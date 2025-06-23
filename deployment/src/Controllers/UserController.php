<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use DateTime;
use AlingAi\Models\User;
use AlingAi\Models\UserLog;
use AlingAi\Models\Conversation;
use AlingAi\Models\Document;
use AlingAi\Services\CacheService;
use AlingAi\Services\EmailService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 用户管理控制器
 * 处理用户相关的所有操作
 */
class UserController extends BaseController
{
    private CacheService $cacheService;
    private EmailService $emailService;

    public function __construct(CacheService $cacheService, EmailService $emailService)
    {
        $this->cacheService = $cacheService;
        $this->emailService = $emailService;
    }

    /**
     * 获取用户列表
     */
    public function index(ServerRequestInterface $request): array
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $search = $params['search'] ?? '';
            $status = $params['status'] ?? '';
            $role = $params['role'] ?? '';

            // 缓存键
            $cacheKey = "users_list_" . md5(serialize($params));
            
            // 尝试从缓存获取
            $result = $this->cacheService->get($cacheKey);
            if ($result !== null) {
                return $this->jsonResponse($result);
            }            // 构建查询  
            $query = User::query();

            // 搜索条件
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('username', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('nickname', 'LIKE', "%{$search}%");
                });
            }

            // 状态过滤
            if (!empty($status)) {
                $query->where('status', $status);
            }

            // 角色过滤
            if (!empty($role)) {
                $query->where('role', $role);
            }

            // 分页
            $users = $query->orderBy('created_at', 'desc')
                          ->paginate($limit, ['*'], 'page', $page);

            $result = [
                'data' => $users['data'],
                'meta' => [
                    'total' => $users['total'],
                    'per_page' => $users['per_page'],
                    'current_page' => $users['current_page'],
                    'last_page' => $users['total_pages'],
                    'from' => $users['from'],
                    'to' => $users['to'],
                    'has_next' => $users['has_next'],
                    'has_prev' => $users['has_prev']
                ]
            ];

            // 缓存结果5分钟
            $this->cacheService->set($cacheKey, $result, 300);

            return $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->logger->error('获取用户列表失败', [
                'error' => $e->getMessage(),
                'params' => $params ?? []
            ]);

            return $this->jsonResponse([
                'error' => '获取用户列表失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取单个用户详情
     */
    public function show(ServerRequestInterface $request): array
    {
        try {
            $userId = $request->getAttribute('id');
            
            // 缓存键
            $cacheKey = "user_detail_{$userId}";
            
            // 尝试从缓存获取
            $user = $this->cacheService->get($cacheKey);
            if ($user === null) {
                $user = User::with([
                    'conversations' => function($q) {
                        $q->latest()->limit(10);
                    },
                    'userLogs' => function($q) {
                        $q->latest()->limit(20);
                    }
                ])->findOrFail($userId);

                // 缓存用户信息10分钟
                $this->cacheService->set($cacheKey, $user, 600);
            }

            return $this->jsonResponse([
                'data' => $user
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->jsonResponse([
                'error' => '用户不存在'
            ], 404);
        } catch (\Exception $e) {
            $this->logger->error('获取用户详情失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId ?? null
            ]);

            return $this->jsonResponse([
                'error' => '获取用户详情失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建新用户
     */
    public function store(ServerRequestInterface $request): array
    {
        try {
            $data = $this->getJsonData($request);
            
            // 验证必要字段
            $requiredFields = ['username', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->jsonResponse([
                        'error' => "缺少必要字段: {$field}"
                    ], 400);
                }
            }

            // 验证邮箱格式
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->jsonResponse([
                    'error' => '邮箱格式不正确'
                ], 400);
            }

            // 检查用户名和邮箱是否已存在
            if (User::where('username', $data['username'])->exists()) {
                return $this->jsonResponse([
                    'error' => '用户名已存在'
                ], 409);
            }

            if (User::where('email', $data['email'])->exists()) {
                return $this->jsonResponse([
                    'error' => '邮箱已存在'
                ], 409);
            }

            // 创建用户
            $user = new User([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_ARGON2ID),
                'nickname' => $data['nickname'] ?? $data['username'],
                'role' => $data['role'] ?? 'user',
                'status' => $data['status'] ?? 'active',
                'avatar' => $data['avatar'] ?? null,
                'phone' => $data['phone'] ?? null,
                'bio' => $data['bio'] ?? null,
                'settings' => $data['settings'] ?? [],
                'preferences' => $data['preferences'] ?? [],
                'last_login_at' => null,
                'last_login_ip' => null,
                'email_verified_at' => ($data['email_verified'] ?? false) ? new DateTime() : null
            ]);

            $user->save();            // 记录操作日志
            UserLog::create([
                'user_id' => $user->id,
                'action' => 'user_created',
                'description' => '用户账户创建',
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'created_by' => $request->getAttribute('user_id')
                ]
            ]);

            // 发送欢迎邮件
            if (!empty($data['send_welcome_email'])) {
                $this->emailService->sendTemplate(
                    $user->email,
                    'welcome',
                    [
                        'username' => $user->username,
                        'nickname' => $user->nickname
                    ],
                    [
                        'subject' => '欢迎加入AlingAi'
                    ]
                );
            }

            // 清除用户列表缓存
            $this->cacheService->deleteByTag('users_list');

            return $this->jsonResponse([
                'data' => $user,
                'message' => '用户创建成功'
            ], 201);
        } catch (\Exception $e) {
            $this->logger->error('创建用户失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);

            return $this->jsonResponse([
                'error' => '创建用户失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新用户信息
     */
    public function update(ServerRequestInterface $request): array
    {
        try {
            $userId = $request->getAttribute('id');
            $data = $this->getJsonData($request);
            
            $user = User::findOrFail($userId);
            $originalData = $user->toArray();

            // 可更新的字段
            $updateableFields = [
                'username', 'email', 'nickname', 'role', 'status', 
                'avatar', 'phone', 'bio', 'settings', 'preferences'
            ];

            foreach ($updateableFields as $field) {
                if (array_key_exists($field, $data)) {
                    if ($field === 'username' && $data[$field] !== $user->username) {
                        // 检查用户名是否已存在
                        if (User::where('username', $data[$field])->where('id', '!=', $userId)->exists()) {
                            return $this->jsonResponse([
                                'error' => '用户名已存在'
                            ], 409);
                        }
                    }

                    if ($field === 'email' && $data[$field] !== $user->email) {
                        // 检查邮箱是否已存在
                        if (User::where('email', $data[$field])->where('id', '!=', $userId)->exists()) {
                            return $this->jsonResponse([
                                'error' => '邮箱已存在'
                            ], 409);
                        }

                        // 验证邮箱格式
                        if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            return $this->jsonResponse([
                                'error' => '邮箱格式不正确'
                            ], 400);
                        }

                        // 重置邮箱验证状态
                        $user->email_verified_at = null;
                    }

                    $user->$field = $data[$field];
                }
            }

            // 如果有密码更新
            if (!empty($data['password'])) {
                $user->password = password_hash($data['password'], PASSWORD_ARGON2ID);
            }

            $user->save();

            // 记录操作日志
            UserLog::create([
                'user_id' => $user->id,
                'action' => 'user_updated',
                'description' => '用户信息更新',
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'updated_by' => $request->getAttribute('user_id'),
                    'changes' => array_diff_assoc($user->toArray(), $originalData)
                ]
            ]);

            // 清除相关缓存
            $this->cacheService->delete("user_detail_{$userId}");
            $this->cacheService->deleteByTag('users_list');

            return $this->jsonResponse([
                'data' => $user,
                'message' => '用户信息更新成功'
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->jsonResponse([
                'error' => '用户不存在'
            ], 404);
        } catch (\Exception $e) {
            $this->logger->error('更新用户失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId ?? null,
                'data' => $data ?? []
            ]);

            return $this->jsonResponse([
                'error' => '更新用户失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除用户
     */
    public function delete(ServerRequestInterface $request): array
    {
        try {
            $userId = $request->getAttribute('id');
            $user = User::findOrFail($userId);

            // 检查是否为管理员
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return $this->jsonResponse([
                        'error' => '不能删除最后一个管理员账户'
                    ], 403);
                }
            }

            // 软删除
            $user->delete();

            // 记录操作日志
            UserLog::create([
                'user_id' => $user->id,
                'action' => 'user_deleted',
                'description' => '用户账户删除',
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'deleted_by' => $request->getAttribute('user_id')
                ]
            ]);

            // 清除相关缓存
            $this->cacheService->delete("user_detail_{$userId}");
            $this->cacheService->deleteByTag('users_list');

            return $this->jsonResponse([
                'message' => '用户删除成功'
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->jsonResponse([
                'error' => '用户不存在'
            ], 404);
        } catch (\Exception $e) {
            $this->logger->error('删除用户失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId ?? null
            ]);

            return $this->jsonResponse([
                'error' => '删除用户失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取用户统计信息
     */
    public function statistics(ServerRequestInterface $request): array
    {
        try {
            $cacheKey = 'user_statistics';
            
            // 尝试从缓存获取
            $stats = $this->cacheService->get($cacheKey);
            if ($stats === null) {
                $stats = [
                    'total_users' => User::count(),
                    'active_users' => User::where('status', 'active')->count(),
                    'inactive_users' => User::where('status', 'inactive')->count(),
                    'banned_users' => User::where('status', 'banned')->count(),
                    'verified_users' => User::whereNotNull('email_verified_at')->count(),
                    'unverified_users' => User::whereNull('email_verified_at')->count(),
                    'admin_users' => User::where('role', 'admin')->count(),
                    'regular_users' => User::where('role', 'user')->count(),
                    'new_users_today' => User::whereDate('created_at', today())->count(),
                    'new_users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
                    'login_today' => User::whereDate('last_login_at', today())->count(),
                    'login_this_week' => User::whereBetween('last_login_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
                ];

                // 缓存10分钟
                $this->cacheService->set($cacheKey, $stats, 600);
            }

            return $this->jsonResponse([
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取用户统计失败', [
                'error' => $e->getMessage()
            ]);

            return $this->jsonResponse([
                'error' => '获取用户统计失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 批量操作用户
     */
    public function batchOperation(ServerRequestInterface $request): array
    {
        try {
            $data = $this->getJsonData($request);
            
            if (empty($data['user_ids']) || !is_array($data['user_ids'])) {
                return $this->jsonResponse([
                    'error' => '请选择要操作的用户'
                ], 400);
            }

            if (empty($data['action'])) {
                return $this->jsonResponse([
                    'error' => '请指定操作类型'
                ], 400);
            }

            $userIds = $data['user_ids'];
            $action = $data['action'];
            $operatorId = $request->getAttribute('user_id');

            $results = [];
            
            foreach ($userIds as $userId) {
                try {
                    $user = User::findOrFail($userId);
                    
                    switch ($action) {
                        case 'activate':
                            $user->status = 'active';
                            $user->save();
                            $results[] = ['user_id' => $userId, 'success' => true, 'message' => '激活成功'];
                            break;
                            
                        case 'deactivate':
                            $user->status = 'inactive';
                            $user->save();
                            $results[] = ['user_id' => $userId, 'success' => true, 'message' => '停用成功'];
                            break;
                            
                        case 'ban':
                            $user->status = 'banned';
                            $user->save();
                            $results[] = ['user_id' => $userId, 'success' => true, 'message' => '封禁成功'];
                            break;
                            
                        case 'delete':
                            if ($user->role === 'admin') {
                                $adminCount = User::where('role', 'admin')->count();
                                if ($adminCount <= 1) {
                                    $results[] = ['user_id' => $userId, 'success' => false, 'message' => '不能删除最后一个管理员'];
                                    continue 2;
                                }
                            }
                            $user->delete();
                            $results[] = ['user_id' => $userId, 'success' => true, 'message' => '删除成功'];
                            break;
                            
                        default:
                            $results[] = ['user_id' => $userId, 'success' => false, 'message' => '不支持的操作'];
                            continue 2;
                    }

                    // 记录操作日志
                    UserLog::create([
                        'user_id' => $user->id,
                        'action' => "batch_{$action}",
                        'description' => "批量操作: {$action}",
                        'ip_address' => $this->getClientIp($request),
                        'user_agent' => $request->getHeaderLine('User-Agent'),
                        'data' => [
                            'operator_id' => $operatorId,
                            'batch_operation' => true
                        ]
                    ]);

                } catch (ModelNotFoundException $e) {
                    $results[] = ['user_id' => $userId, 'success' => false, 'message' => '用户不存在'];
                } catch (\Exception $e) {
                    $results[] = ['user_id' => $userId, 'success' => false, 'message' => $e->getMessage()];
                }
            }

            // 清除相关缓存
            $this->cacheService->deleteByTag('users_list');
            $this->cacheService->delete('user_statistics');

            return $this->jsonResponse([
                'data' => $results,
                'message' => '批量操作完成'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('批量操作用户失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);            return $this->jsonResponse([
                'error' => '批量操作失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 用户仪表板页面
     */
    public function dashboard(ServerRequestInterface $request, ResponseInterface $response): array    {
        try {
            $user = $request->getAttribute('user');
            
            // 获取仪表板数据
            $dashboardData = $this->getDashboardData($user->id);
            
            return $this->jsonResponse([
                'data' => [
                    'user' => $user,
                    'stats' => $dashboardData['stats'],
                    'recent_activities' => $dashboardData['recent_activities'],
                    'recent_documents' => $dashboardData['recent_documents'],
                    'notifications' => $dashboardData['notifications'],
                    'unread_notifications_count' => $dashboardData['unread_notifications_count'],
                    'chart_labels' => $dashboardData['chart_labels'],
                    'chart_conversations' => $dashboardData['chart_conversations'],
                    'chart_documents' => $dashboardData['chart_documents']
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('渲染仪表板失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return $this->jsonResponse([
                'error' => '加载仪表板失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }    /**
     * API: 刷新仪表板数据
     */
    public function refreshDashboard(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            
            // 清除缓存
            $this->cacheService->delete("dashboard_data_{$user->id}");
            
            // 获取最新数据
            $dashboardData = $this->getDashboardData($user->id);
            
            return $this->successResponse($response, $dashboardData, '仪表板数据已刷新');
        } catch (\Exception $e) {
            $this->logger->error('刷新仪表板数据失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return $this->errorResponse($response, '刷新数据失败', 500);
        }
    }

    /**
     * 用户个人资料页面
     */    public function showProfile(ServerRequestInterface $request, ResponseInterface $response): array
    {
        try {
            $user = $request->getAttribute('user');
            
            return [
                'success' => true,
                'data' => [
                    'user' => $user->toDetailedArray(),
                    'title' => '个人资料'
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error('渲染个人资料页面失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return [
                'success' => false,
                'error' => '加载个人资料失败'
            ];
        }    }

    /**
     * 用户设置页面
     */
    public function showSettings(ServerRequestInterface $request, ResponseInterface $response): array
    {
        try {
            $user = $request->getAttribute('user');
            
            return [
                'success' => true,
                'data' => [
                    'user' => $user->toDetailedArray(),
                    'title' => '用户设置'
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error('渲染设置页面失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return [
                'success' => false,
                'error' => '加载设置页面失败'
            ];
        }
    }

    /**
     * 获取仪表板数据
     */
    private function getDashboardData(int $userId): array
    {
        // 缓存键
        $cacheKey = "dashboard_data_{$userId}";
          // 尝试从缓存获取
        $data = $this->cacheService->get($cacheKey);
        if ($data !== null) {
            return $data;
        }

        try {
            $user = User::findOrFail($userId);

            // 基础统计 - 直接使用模型查询
            $conversationCount = (new Conversation())->where('user_id', $userId)->count();
            $documentCount = (new Document())->where('user_id', $userId)->count();
            
            $stats = [
                'conversations' => $conversationCount,
                'documents' => $documentCount,
                'monthly_usage' => $this->calculateMonthlyUsage($userId),
                'storage_used' => $this->calculateStorageUsed($userId),
            ];            // 最近活动 - 直接使用模型查询
            $userLogs = (new UserLog())->where('user_id', $userId)->orderBy('created_at', 'desc')->limit(5)->get();
            $recent_activities = [];
            foreach ($userLogs as $log) {
                $recent_activities[] = [
                    'title' => $this->getActivityTitle($log['action']),
                    'icon' => $this->getActivityIcon($log['action']),
                    'created_at' => $log['created_at']
                ];
            }

            // 最近文档 - 直接使用模型查询
            $documents = (new Document())->where('user_id', $userId)->orderBy('created_at', 'desc')->limit(5)->get();
            $recent_documents = [];
            foreach ($documents as $doc) {
                $recent_documents[] = [
                    'name' => $doc['name'],
                    'size' => $this->formatFileSize($doc['size']),
                    'icon' => $this->getFileIcon($doc['type']),
                    'updated_at' => $doc['updated_at']
                ];
            }

            // 系统通知（模拟数据）
            $notifications = $this->getSystemNotifications($userId);
            $unread_notifications_count = count(array_filter($notifications, function($n) {
                return !$n['is_read'];
            }));

            // 图表数据（最近7天）
            $chartData = $this->getChartData($userId);

            $data = [
                'stats' => $stats,
                'recent_activities' => $recent_activities,
                'recent_documents' => $recent_documents,
                'notifications' => $notifications,
                'unread_notifications_count' => $unread_notifications_count,
                'chart_labels' => $chartData['labels'],
                'chart_conversations' => $chartData['conversations'],
                'chart_documents' => $chartData['documents']
            ];

            // 缓存5分钟
            $this->cacheService->set($cacheKey, $data, 300);

            return $data;
        } catch (\Exception $e) {
            $this->logger->error('获取仪表板数据失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            // 返回默认数据
            return [
                'stats' => [
                    'conversations' => 0,
                    'documents' => 0,
                    'monthly_usage' => 0,
                    'storage_used' => 0
                ],
                'recent_activities' => [],
                'recent_documents' => [],
                'notifications' => [],
                'unread_notifications_count' => 0,
                'chart_labels' => [],
                'chart_conversations' => [],
                'chart_documents' => []
            ];
        }
    }    /**
     * 计算月度使用量百分比
     */
    private function calculateMonthlyUsage(int $userId): int
    {
        try {
            $startOfMonth = date('Y-m-01 00:00:00'); // 使用标准PHP日期函数
            $conversationsThisMonth = (new Conversation())
                ->where('user_id', $userId)
                ->where('created_at', '>=', $startOfMonth)
                ->count();

            // 假设月度限制为100次对话
            $monthlyLimit = 100;
            return min(100, intval(($conversationsThisMonth / $monthlyLimit) * 100));
        } catch (\Exception $e) {
            return 0;
        }
    }    /**
     * 计算存储使用量
     */
    private function calculateStorageUsed(int $userId): string
    {
        try {
            $documents = (new Document())
                ->where('user_id', $userId)
                ->get();
                
            $totalSize = 0;
            foreach ($documents as $doc) {
                $totalSize += $doc['size'] ?? 0;
            }

            return $this->formatFileSize($totalSize);
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    /**
     * 获取活动标题
     */
    private function getActivityTitle(string $action): string
    {
        $titles = [
            'login' => '用户登录',
            'logout' => '用户登出',
            'conversation_create' => '创建新对话',
            'document_upload' => '上传文档',
            'profile_update' => '更新个人资料',
            'settings_update' => '更新设置'
        ];

        return $titles[$action] ?? '未知操作';
    }

    /**
     * 获取活动图标
     */
    private function getActivityIcon(string $action): string
    {
        $icons = [
            'login' => 'box-arrow-in-right',
            'logout' => 'box-arrow-left',
            'conversation_create' => 'chat-dots',
            'document_upload' => 'cloud-upload',
            'profile_update' => 'person-gear',
            'settings_update' => 'gear'
        ];

        return $icons[$action] ?? 'circle';
    }

    /**
     * 获取文件图标
     */
    private function getFileIcon(string $type): string
    {
        $icons = [
            'pdf' => 'file-pdf',
            'doc' => 'file-word',
            'docx' => 'file-word',
            'xls' => 'file-excel',
            'xlsx' => 'file-excel',
            'ppt' => 'file-ppt',
            'pptx' => 'file-ppt',
            'txt' => 'file-text',
            'jpg' => 'file-image',
            'jpeg' => 'file-image',
            'png' => 'file-image',
            'gif' => 'file-image'
        ];

        return $icons[$type] ?? 'file-earmark';
    }

    /**
     * 格式化文件大小
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } elseif ($bytes < 1073741824) {
            return round($bytes / 1048576, 2) . ' MB';
        } else {
            return round($bytes / 1073741824, 2) . ' GB';
        }
    }    /**
     * 获取系统通知
     */
    private function getSystemNotifications(int $userId): array
    {
        // 这里可以从数据库获取真实通知
        // 暂时返回模拟数据
        return [
            [
                'title' => '系统更新',
                'content' => '系统已更新到最新版本，新增了多项功能和性能优化。',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],
            [
                'title' => '存储空间提醒',
                'content' => '您的存储空间使用率已达到80%，建议清理不必要的文件。',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                'title' => '安全提醒',
                'content' => '检测到新的登录设备，如果不是您本人操作，请及时修改密码。',
                'is_read' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ]
        ];
    }

    /**
     * 获取图表数据
     */
    private function getChartData(int $userId): array
    {
        try {
            $labels = [];
            $conversations = [];
            $documents = [];

            // 最近7天的数据
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days")); // 使用标准PHP日期函数
                $labels[] = date('m-d', strtotime($date));
                
                $conversationCount = (new Conversation())
                    ->where('user_id', $userId)
                    ->whereDate('created_at', $date)
                    ->count();
                $conversations[] = $conversationCount;

                $documentCount = (new Document())
                    ->where('user_id', $userId)
                    ->whereDate('created_at', $date)
                    ->count();
                $documents[] = $documentCount;
            }

            return [
                'labels' => $labels,
                'conversations' => $conversations,
                'documents' => $documents
            ];
        } catch (\Exception $e) {
            // 返回默认数据
            return [
                'labels' => ['05-26', '05-27', '05-28', '05-29', '05-30', '05-31', '06-01'],
                'conversations' => [2, 5, 3, 8, 6, 4, 7],
                'documents' => [1, 2, 0, 3, 2, 1, 2]
            ];
        }
    }
}
