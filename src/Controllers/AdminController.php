<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\Models\{User, Conversation, Document, UserLog};
use AlingAi\Services\{CacheService, DatabaseServiceInterface, EmailService, EnhancedUserManagementService};
use AlingAi\Utils\Logger;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Illuminate\Database\Eloquent\ModelNotFoundException;

// 包含辅助函数
require_once __DIR__ . '/../Utils/Helpers.php';

/**
 * 管理员控制器
 * 处理系统管理相关的所有操作，包括企业用户管理和高级功能
 */
class AdminController extends BaseController
{
    private CacheService $cacheService;
    private DatabaseServiceInterface $databaseService;
    private EmailService $emailService;
    private EnhancedUserManagementService $userManagementService;
    protected Logger $logger;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        EmailService $emailService,
        EnhancedUserManagementService $userManagementService
    ) {
        parent::__construct($db, $cache);
        $this->cacheService = $cache;
        $this->databaseService = $db;
        $this->emailService = $emailService;
        $this->userManagementService = $userManagementService;
        $this->logger = new Logger();
    }    /**
     * 获取管理员面板仪表板数据（增强版）
     */
    public function dashboard(ServerRequestInterface $request): array
    {
        try {
            // 检查管理员权限
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $cacheKey = 'admin_dashboard_enhanced';
            
            // 尝试从缓存获取
            $data = $this->cacheService->get($cacheKey);
            if ($data === null) {
                // 用户统计（增强版）
                $userStats = [
                    'total' => User::count(),
                    'personal' => User::where('user_type', 'personal')->count(),
                    'enterprise' => User::where('user_type', 'enterprise')->count(),
                    'pending_applications' => User::where('application_status', 'pending')->count(),
                    'approved_applications' => User::where('application_status', 'approved')->count(),
                    'rejected_applications' => User::where('application_status', 'rejected')->count(),
                    'active' => User::where('status', 'active')->count(),
                    'inactive' => User::where('status', 'inactive')->count(),
                    'banned' => User::where('status', 'banned')->count(),
                    'new_today' => User::whereDate('created_at', today())->count(),
                    'new_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
                ];

                // API使用统计
                $apiStats = $this->databaseService->select(
                    "SELECT 
                        COUNT(*) as total_requests,
                        SUM(cost) as total_cost,
                        AVG(cost) as avg_cost,
                        COUNT(DISTINCT user_id) as active_users,
                        provider_name,
                        DATE(created_at) as date
                    FROM api_usage_stats 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY provider_name, DATE(created_at)
                    ORDER BY date DESC"
                );

                // 钱包和支付统计
                $walletStats = $this->databaseService->select(
                    "SELECT 
                        COUNT(DISTINCT user_id) as users_with_balance,
                        SUM(CASE WHEN transaction_type = 'recharge' THEN amount ELSE 0 END) as total_recharge,
                        SUM(CASE WHEN transaction_type = 'deduction' THEN amount ELSE 0 END) as total_deduction,
                        COUNT(CASE WHEN transaction_type = 'recharge' AND DATE(created_at) = CURDATE() THEN 1 END) as today_recharge_count,
                        SUM(CASE WHEN transaction_type = 'recharge' AND DATE(created_at) = CURDATE() THEN amount ELSE 0 END) as today_recharge_amount
                    FROM wallet_transactions 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                )[0] ?? [];

                // 企业申请待处理
                $pendingApplications = $this->databaseService->select(
                    "SELECT ua.*, u.username, u.email, u.created_at as user_created_at
                    FROM user_applications ua
                    JOIN users u ON ua.user_id = u.id
                    WHERE ua.status = 'pending'
                    ORDER BY ua.created_at DESC
                    LIMIT 10"
                );

                // AI提供商状态
                $aiProviderStats = $this->databaseService->select(
                    "SELECT 
                        provider_name,
                        is_enabled,
                        daily_quota,
                        used_quota,
                        last_health_check,
                        health_status
                    FROM ai_provider_configs
                    ORDER BY provider_name"
                );

                // 对话统计
                $conversationStats = [
                    'total' => Conversation::count(),
                    'active' => Conversation::where('status', 'active')->count(),
                    'today' => Conversation::whereDate('created_at', today())->count(),
                    'this_week' => Conversation::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'this_month' => Conversation::whereMonth('created_at', now()->month)->count(),
                ];

                // 文档统计
                $documentStats = [
                    'total' => Document::count(),
                    'active' => Document::where('status', 'active')->count(),
                    'public' => Document::where('is_public', true)->count(),
                    'today' => Document::whereDate('created_at', today())->count(),
                    'this_week' => Document::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'this_month' => Document::whereMonth('created_at', now()->month)->count(),
                ];

                // 系统统计
                $systemStats = [
                    'php_version' => PHP_VERSION,
                    'memory_usage' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'disk_space' => disk_free_space('/'),
                    'server_load' => sys_getloadavg(),
                ];

                // 最近活动
                $recentActivity = UserLog::with(['user:id,username,nickname'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(20)
                                        ->get();

                // 热门内容
                $popularConversations = Conversation::with(['user:id,username,nickname'])
                                                   ->orderBy('read_count', 'desc')
                                                   ->limit(10)
                                                   ->get();

                $popularDocuments = Document::with(['user:id,username,nickname'])
                                           ->orderBy('view_count', 'desc')
                                           ->limit(10)
                                           ->get();

                $data = [
                    'users' => $userStats,
                    'api' => $apiStats,
                    'wallet' => $walletStats,
                    'pending_applications' => $pendingApplications,
                    'ai_providers' => $aiProviderStats,
                    'conversations' => $conversationStats,
                    'documents' => $documentStats,
                    'system' => $systemStats,
                    'recent_activity' => $recentActivity,
                    'popular_conversations' => $popularConversations,
                    'popular_documents' => $popularDocuments,
                    'timestamp' => now()
                ];

                // 缓存5分钟
                $this->cacheService->set($cacheKey, $data, 300);
            }

            return $this->jsonResponse([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取管理员仪表板失败', [
                'error' => $e->getMessage()
            ]);

            return $this->jsonResponse([
                'error' => '获取仪表板数据失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取系统配置
     */
    public function getConfig(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $config = [
                'app' => [
                    'name' => $_ENV['APP_NAME'] ?? 'AlingAi',
                    'env' => $_ENV['APP_ENV'] ?? 'production',
                    'debug' => $_ENV['APP_DEBUG'] ?? false,
                    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
                    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
                ],
                'database' => [
                    'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
                    'host' => $_ENV['DB_HOST'] ?? 'localhost',
                    'port' => $_ENV['DB_PORT'] ?? 3306,
                    'database' => $_ENV['DB_DATABASE'] ?? 'alingai',
                ],
                'cache' => [
                    'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
                    'redis_host' => $_ENV['REDIS_HOST'] ?? 'localhost',
                    'redis_port' => $_ENV['REDIS_PORT'] ?? 6379,
                ],
                'email' => [
                    'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
                    'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
                    'port' => $_ENV['MAIL_PORT'] ?? 587,
                    'from' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@alingai.com',
                ],
                'security' => [
                    'jwt_secret' => !empty($_ENV['JWT_SECRET']),
                    'session_lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120,
                    'password_reset_expire' => $_ENV['PASSWORD_RESET_EXPIRE'] ?? 60,
                ],
            ];

            return $this->jsonResponse([
                'data' => $config
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取系统配置失败', [
                'error' => $e->getMessage()
            ]);

            return $this->jsonResponse([
                'error' => '获取系统配置失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新系统配置
     */
    public function updateConfig(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $data = $this->getJsonData($request);
            $envPath = dirname(__DIR__, 2) . '/.env';

            // 读取现有环境变量
            $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
            $envLines = explode("\n", $envContent);
            $envArray = [];

            foreach ($envLines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $envArray[trim($key)] = trim($value);
                }
            }

            // 更新配置
            foreach ($data as $section => $settings) {
                foreach ($settings as $key => $value) {
                    $envKey = strtoupper($section . '_' . $key);
                    $envArray[$envKey] = $value;
                }
            }

            // 生成新的.env内容
            $newEnvContent = '';
            foreach ($envArray as $key => $value) {
                $newEnvContent .= "{$key}={$value}\n";
            }

            // 写入.env文件
            file_put_contents($envPath, $newEnvContent);

            // 记录操作日志
            UserLog::create([
                'user_id' => $request->getAttribute('user_id'),
                'action' => 'config_updated',
                'description' => '系统配置更新',
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'updated_config' => $data
                ]
            ]);

            // 清除相关缓存
            $this->cacheService->delete('admin_dashboard');

            return $this->jsonResponse([
                'message' => '系统配置更新成功'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('更新系统配置失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);

            return $this->jsonResponse([
                'error' => '更新系统配置失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取系统日志
     */
    public function getLogs(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 50), 200);
            $level = $params['level'] ?? '';
            $action = $params['action'] ?? '';
            $userId = $params['user_id'] ?? '';

            $query = UserLog::with(['user:id,username,nickname,email']);

            if (!empty($level)) {
                $query->where('level', $level);
            }

            if (!empty($action)) {
                $query->where('action', 'LIKE', "%{$action}%");
            }

            if (!empty($userId)) {
                $query->where('user_id', $userId);
            }            $logs = $query->orderBy('created_at', 'desc')
                         ->paginate($limit, ['*'], 'page', $page);

            return $this->jsonResponse([
                'data' => $logs['data'],
                'meta' => [
                    'total' => $logs['total'],
                    'per_page' => $logs['per_page'],
                    'current_page' => $logs['current_page'],
                    'last_page' => $logs['total_pages'],
                    'from' => $logs['from'],
                    'to' => $logs['to'],
                    'has_next' => $logs['has_next'],
                    'has_prev' => $logs['has_prev']
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取系统日志失败', [
                'error' => $e->getMessage()
            ]);

            return $this->jsonResponse([
                'error' => '获取系统日志失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 清理系统缓存
     */
    public function clearCache(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $data = $this->getJsonData($request);
            $type = $data['type'] ?? 'all';

            $result = [];

            switch ($type) {
                case 'all':
                    $this->cacheService->flush();
                    $result['message'] = '所有缓存已清理';
                    break;

                case 'users':
                    $this->cacheService->deleteByTag('users_list');
                    $this->cacheService->delete('user_statistics');
                    $result['message'] = '用户缓存已清理';
                    break;

                case 'conversations':
                    $this->cacheService->deleteByTag('conversations_list');
                    $result['message'] = '对话缓存已清理';
                    break;

                case 'documents':
                    $this->cacheService->deleteByTag('documents_list');
                    $result['message'] = '文档缓存已清理';
                    break;

                case 'dashboard':
                    $this->cacheService->delete('admin_dashboard');
                    $result['message'] = '仪表板缓存已清理';
                    break;

                default:
                    return $this->jsonResponse([
                        'error' => '不支持的缓存类型'
                    ], 400);
            }

            // 记录操作日志
            UserLog::create([
                'user_id' => $request->getAttribute('user_id'),
                'action' => 'cache_cleared',
                'description' => "清理缓存: {$type}",
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'cache_type' => $type
                ]
            ]);

            return $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->logger->error('清理缓存失败', [
                'error' => $e->getMessage(),
                'type' => $type ?? null
            ]);

            return $this->jsonResponse([
                'error' => '清理缓存失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 数据库维护
     */
    public function maintenance(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $data = $this->getJsonData($request);
            $action = $data['action'] ?? '';

            $result = [];

            switch ($action) {
                case 'optimize':
                    // 优化数据库表
                    $tables = ['users', 'conversations', 'documents', 'user_logs', 'password_resets', 'api_tokens'];
                    foreach ($tables as $table) {
                        $this->databaseService->execute("OPTIMIZE TABLE {$table}");
                    }
                    $result['message'] = '数据库表优化完成';
                    break;

                case 'analyze':
                    // 分析数据库表
                    $tables = ['users', 'conversations', 'documents', 'user_logs', 'password_resets', 'api_tokens'];
                    foreach ($tables as $table) {
                        $this->databaseService->execute("ANALYZE TABLE {$table}");
                    }
                    $result['message'] = '数据库表分析完成';
                    break;

                case 'backup':
                    // 创建数据库备份
                    $backupFile = storage_path('backups/db_backup_' . date('Y-m-d_H-i-s') . '.sql');
                    $command = sprintf(
                        'mysqldump -h %s -u %s -p%s %s > %s',
                        $_ENV['DB_HOST'],
                        $_ENV['DB_USERNAME'],
                        $_ENV['DB_PASSWORD'],
                        $_ENV['DB_DATABASE'],
                        $backupFile
                    );
                    exec($command);
                    $result['message'] = '数据库备份完成';
                    $result['backup_file'] = basename($backupFile);
                    break;

                case 'cleanup':
                    // 清理过期数据
                    $expiredPasswordResets = $this->databaseService->execute(
                        "DELETE FROM password_resets WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)"
                    );
                    $expiredLogs = $this->databaseService->execute(
                        "DELETE FROM user_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
                    );
                    $result['message'] = '数据清理完成';
                    $result['deleted'] = [
                        'password_resets' => $expiredPasswordResets,
                        'logs' => $expiredLogs
                    ];
                    break;

                default:
                    return $this->jsonResponse([
                        'error' => '不支持的维护操作'
                    ], 400);
            }

            // 记录操作日志
            UserLog::create([
                'user_id' => $request->getAttribute('user_id'),
                'action' => 'database_maintenance',
                'description' => "数据库维护: {$action}",
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'maintenance_action' => $action,
                    'result' => $result
                ]
            ]);

            return $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->logger->error('数据库维护失败', [
                'error' => $e->getMessage(),
                'action' => $action ?? null
            ]);

            return $this->jsonResponse([
                'error' => '数据库维护失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 发送系统通知
     */
    public function sendNotification(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $data = $this->getJsonData($request);
            
            if (empty($data['subject']) || empty($data['message'])) {
                return $this->jsonResponse([
                    'error' => '主题和消息内容不能为空'
                ], 400);
            }

            $recipient = $data['recipient'] ?? 'all';
            $subject = $data['subject'];
            $message = $data['message'];

            $users = [];
            
            switch ($recipient) {
                case 'all':
                    $users = User::where('status', 'active')->get();
                    break;
                    
                case 'admins':
                    $users = User::where('role', 'admin')->where('status', 'active')->get();
                    break;
                    
                case 'users':
                    $users = User::where('role', 'user')->where('status', 'active')->get();
                    break;
                    
                default:
                    if (is_numeric($recipient)) {
                        $users = User::where('id', $recipient)->where('status', 'active')->get();
                    }
            }            if (empty($users)) {
                return $this->jsonResponse([
                    'error' => '没有找到符合条件的用户'
                ], 400);
            }

            $sentCount = 0;
            $failedCount = 0;

            foreach ($users as $user) {
                try {
                    $this->emailService->send(
                        $user->email,
                        $subject,
                        $message,
                        [
                            'username' => $user->username,
                            'nickname' => $user->nickname
                        ]
                    );
                    $sentCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $this->logger->warning('发送通知邮件失败', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // 记录操作日志
            UserLog::create([
                'user_id' => $request->getAttribute('user_id'),
                'action' => 'notification_sent',
                'description' => '发送系统通知',
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'recipient' => $recipient,
                    'subject' => $subject,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount
                ]
            ]);

            return $this->jsonResponse([
                'message' => '通知发送完成',
                'data' => [
                    'sent' => $sentCount,
                    'failed' => $failedCount,
                    'total' => count($users)
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('发送系统通知失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);

            return $this->jsonResponse([
                'error' => '发送系统通知失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取企业用户列表
     */
    public function getEnterpriseUsers(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $status = $params['status'] ?? '';
            $application_status = $params['application_status'] ?? '';

            $query = "SELECT 
                u.id, u.username, u.email, u.user_type, u.status, u.application_status,
                u.company_name, u.business_license, u.contact_person, u.contact_phone,
                u.monthly_quota, u.used_quota, u.wallet_balance,
                u.created_at, u.updated_at,
                ua.application_reason, ua.submitted_at, ua.reviewed_at, ua.reviewer_id,
                ua.review_notes,
                (SELECT SUM(amount) FROM wallet_transactions wt WHERE wt.user_id = u.id AND wt.transaction_type = 'recharge') as total_recharged,
                (SELECT COUNT(*) FROM api_usage_stats aus WHERE aus.user_id = u.id) as api_calls_count
            FROM users u
            LEFT JOIN user_applications ua ON u.id = ua.user_id
            WHERE u.user_type = 'enterprise'";

            $params_array = [];

            if (!empty($status)) {
                $query .= " AND u.status = ?";
                $params_array[] = $status;
            }

            if (!empty($application_status)) {
                $query .= " AND u.application_status = ?";
                $params_array[] = $application_status;
            }

            $query .= " ORDER BY u.created_at DESC";

            // 计算总数
            $countQuery = str_replace("SELECT u.id, u.username, u.email, u.user_type, u.status, u.application_status, u.company_name, u.business_license, u.contact_person, u.contact_phone, u.monthly_quota, u.used_quota, u.wallet_balance, u.created_at, u.updated_at, ua.application_reason, ua.submitted_at, ua.reviewed_at, ua.reviewer_id, ua.review_notes, (SELECT SUM(amount) FROM wallet_transactions wt WHERE wt.user_id = u.id AND wt.transaction_type = 'recharge') as total_recharged, (SELECT COUNT(*) FROM api_usage_stats aus WHERE aus.user_id = u.id) as api_calls_count", "SELECT COUNT(*)", $query);
            $total = $this->databaseService->select($countQuery, $params_array)[0]['COUNT(*)'] ?? 0;

            // 添加分页
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT ? OFFSET ?";
            $params_array[] = $limit;
            $params_array[] = $offset;

            $users = $this->databaseService->select($query, $params_array);

            return $this->jsonResponse([
                'data' => $users,
                'meta' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil($total / $limit),
                    'from' => $offset + 1,
                    'to' => min($offset + $limit, $total)
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取企业用户列表失败', [
                'error' => $e->getMessage()
            ]);

            return $this->jsonResponse([
                'error' => '获取企业用户列表失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 审批企业用户申请
     */
    public function reviewEnterpriseApplication(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $data = $this->getJsonData($request);
            $userId = $data['user_id'] ?? null;
            $action = $data['action'] ?? ''; // 'approve' or 'reject'
            $reviewNotes = $data['review_notes'] ?? '';
            $monthlyQuota = $data['monthly_quota'] ?? 10000; // 默认配额

            if (!$userId || !in_array($action, ['approve', 'reject'])) {
                return $this->jsonResponse([
                    'error' => '用户ID和操作类型不能为空'
                ], 400);
            }

            $result = $this->userManagementService->reviewEnterpriseApplication(
                $userId,
                $action,
                $request->getAttribute('user_id'),
                $reviewNotes,
                $action === 'approve' ? $monthlyQuota : null
            );

            if (!$result['success']) {
                return $this->jsonResponse([
                    'error' => $result['message']
                ], 400);
            }

            // 记录操作日志
            UserLog::create([
                'user_id' => $request->getAttribute('user_id'),
                'action' => 'enterprise_application_reviewed',
                'description' => "企业申请审批: {$action}",
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'reviewed_user_id' => $userId,
                    'action' => $action,
                    'review_notes' => $reviewNotes,
                    'monthly_quota' => $monthlyQuota
                ]
            ]);

            // 清除相关缓存
            $this->cacheService->delete('admin_dashboard_enhanced');

            return $this->jsonResponse([
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            $this->logger->error('审批企业申请失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);

            return $this->jsonResponse([
                'error' => '审批企业申请失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 管理用户API配额
     */
    public function manageUserQuota(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $data = $this->getJsonData($request);
            $userId = $data['user_id'] ?? null;
            $monthlyQuota = $data['monthly_quota'] ?? null;
            $action = $data['action'] ?? ''; // 'set', 'increase', 'decrease', 'reset'

            if (!$userId) {
                return $this->jsonResponse([
                    'error' => '用户ID不能为空'
                ], 400);
            }

            $user = User::find($userId);
            if (!$user) {
                return $this->jsonResponse([
                    'error' => '用户不存在'
                ], 404);
            }

            switch ($action) {
                case 'set':
                    if ($monthlyQuota === null) {
                        return $this->jsonResponse([
                            'error' => '配额值不能为空'
                        ], 400);
                    }
                    $user->monthly_quota = $monthlyQuota;
                    $message = "配额设置为 {$monthlyQuota}";
                    break;

                case 'increase':
                    if ($monthlyQuota === null) {
                        return $this->jsonResponse([
                            'error' => '增加值不能为空'
                        ], 400);
                    }
                    $user->monthly_quota += $monthlyQuota;
                    $message = "配额增加 {$monthlyQuota}";
                    break;

                case 'decrease':
                    if ($monthlyQuota === null) {
                        return $this->jsonResponse([
                            'error' => '减少值不能为空'
                        ], 400);
                    }
                    $user->monthly_quota = max(0, $user->monthly_quota - $monthlyQuota);
                    $message = "配额减少 {$monthlyQuota}";
                    break;

                case 'reset':
                    $user->used_quota = 0;
                    $message = "已用配额重置为0";
                    break;

                default:
                    return $this->jsonResponse([
                        'error' => '不支持的操作类型'
                    ], 400);
            }

            $user->save();

            // 记录操作日志
            UserLog::create([
                'user_id' => $request->getAttribute('user_id'),
                'action' => 'quota_managed',
                'description' => "配额管理: {$message}",
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'data' => [
                    'target_user_id' => $userId,
                    'action' => $action,
                    'monthly_quota' => $monthlyQuota,
                    'new_monthly_quota' => $user->monthly_quota,
                    'new_used_quota' => $user->used_quota
                ]
            ]);

            return $this->jsonResponse([
                'message' => $message,
                'data' => [
                    'monthly_quota' => $user->monthly_quota,
                    'used_quota' => $user->used_quota
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('管理用户配额失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);

            return $this->jsonResponse([
                'error' => '管理用户配额失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取支付系统统计
     */
    public function getPaymentStats(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $params = $request->getQueryParams();
            $period = $params['period'] ?? '7d'; // '1d', '7d', '30d', '90d'

            $periodMap = [
                '1d' => 1,
                '7d' => 7,
                '30d' => 30,
                '90d' => 90
            ];

            $days = $periodMap[$period] ?? 7;

            // 支付统计
            $paymentStats = $this->databaseService->select(
                "SELECT 
                    payment_method,
                    COUNT(*) as transaction_count,
                    SUM(amount) as total_amount,
                    AVG(amount) as avg_amount,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_count,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
                FROM wallet_transactions 
                WHERE transaction_type = 'recharge' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY payment_method",
                [$days]
            );

            // 每日趋势
            $dailyTrend = $this->databaseService->select(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as transaction_count,
                    SUM(amount) as total_amount,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_count
                FROM wallet_transactions 
                WHERE transaction_type = 'recharge' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date",
                [$days]
            );

            // 用户充值排行
            $topRechargeUsers = $this->databaseService->select(
                "SELECT 
                    u.id, u.username, u.user_type,
                    COUNT(*) as recharge_count,
                    SUM(wt.amount) as total_recharged
                FROM wallet_transactions wt
                JOIN users u ON wt.user_id = u.id
                WHERE wt.transaction_type = 'recharge' 
                AND wt.status = 'completed'
                AND wt.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY u.id
                ORDER BY total_recharged DESC
                LIMIT 10",
                [$days]
            );

            // 退款统计
            $refundStats = $this->databaseService->select(
                "SELECT 
                    COUNT(*) as refund_count,
                    SUM(amount) as total_refunded,
                    AVG(amount) as avg_refund
                FROM wallet_transactions 
                WHERE transaction_type = 'refund' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )[0] ?? [];

            return $this->jsonResponse([
                'data' => [
                    'period' => $period,
                    'payment_stats' => $paymentStats,
                    'daily_trend' => $dailyTrend,
                    'top_recharge_users' => $topRechargeUsers,
                    'refund_stats' => $refundStats
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取支付统计失败', [
                'error' => $e->getMessage()
            ]);

            return $this->jsonResponse([
                'error' => '获取支付统计失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 管理AI提供商配置
     */
    public function manageAIProviders(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            $data = $this->getJsonData($request);
            $action = $data['action'] ?? 'list';

            switch ($action) {
                case 'list':
                    $providers = $this->databaseService->select(
                        "SELECT * FROM ai_provider_configs ORDER BY provider_name"
                    );
                    return $this->jsonResponse([
                        'data' => $providers
                    ]);

                case 'update':
                    $providerId = $data['provider_id'] ?? null;
                    $config = $data['config'] ?? [];

                    if (!$providerId) {
                        return $this->jsonResponse([
                            'error' => '提供商ID不能为空'
                        ], 400);
                    }

                    $updateFields = [];
                    $params = [];

                    if (isset($config['is_enabled'])) {
                        $updateFields[] = 'is_enabled = ?';
                        $params[] = $config['is_enabled'] ? 1 : 0;
                    }

                    if (isset($config['daily_quota'])) {
                        $updateFields[] = 'daily_quota = ?';
                        $params[] = $config['daily_quota'];
                    }

                    if (isset($config['rate_limit'])) {
                        $updateFields[] = 'rate_limit = ?';
                        $params[] = $config['rate_limit'];
                    }

                    if (isset($config['price_per_call'])) {
                        $updateFields[] = 'price_per_call = ?';
                        $params[] = $config['price_per_call'];
                    }

                    if (isset($config['max_tokens'])) {
                        $updateFields[] = 'max_tokens = ?';
                        $params[] = $config['max_tokens'];
                    }

                    if (empty($updateFields)) {
                        return $this->jsonResponse([
                            'error' => '没有需要更新的字段'
                        ], 400);
                    }

                    $updateFields[] = 'updated_at = NOW()';
                    $params[] = $providerId;

                    $this->databaseService->execute(
                        "UPDATE ai_provider_configs SET " . implode(', ', $updateFields) . " WHERE id = ?",
                        $params
                    );

                    // 记录操作日志
                    UserLog::create([
                        'user_id' => $request->getAttribute('user_id'),
                        'action' => 'ai_provider_updated',
                        'description' => 'AI提供商配置更新',
                        'ip_address' => $this->getClientIp($request),
                        'user_agent' => $request->getHeaderLine('User-Agent'),
                        'data' => [
                            'provider_id' => $providerId,
                            'config' => $config
                        ]
                    ]);

                    return $this->jsonResponse([
                        'message' => 'AI提供商配置更新成功'
                    ]);

                case 'test':
                    $providerId = $data['provider_id'] ?? null;
                    if (!$providerId) {
                        return $this->jsonResponse([
                            'error' => '提供商ID不能为空'
                        ], 400);
                    }

                    // 执行健康检查
                    $result = $this->userManagementService->testAIProviderHealth($providerId);

                    return $this->jsonResponse([
                        'data' => $result
                    ]);

                default:
                    return $this->jsonResponse([
                        'error' => '不支持的操作类型'
                    ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('管理AI提供商失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);

            return $this->jsonResponse([
                'error' => '管理AI提供商失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 系统监控和警告
     */
    public function getSystemMonitoring(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse([
                    'error' => '需要管理员权限'
                ], 403);
            }

            // 系统健康检查
            $healthChecks = [
                'database' => $this->checkDatabaseHealth(),
                'cache' => $this->checkCacheHealth(),
                'email' => $this->checkEmailHealth(),
                'storage' => $this->checkStorageHealth(),
                'ai_providers' => $this->checkAIProvidersHealth()
            ];

            // 系统警告
            $warnings = [];

            // 检查磁盘空间
            $freeSpace = disk_free_space('/');
            $totalSpace = disk_total_space('/');
            $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
            if ($usagePercent > 90) {
                $warnings[] = [
                    'type' => 'storage',
                    'level' => 'critical',
                    'message' => "磁盘使用率过高: {$usagePercent}%"
                ];
            } elseif ($usagePercent > 80) {
                $warnings[] = [
                    'type' => 'storage',
                    'level' => 'warning',
                    'message' => "磁盘使用率较高: {$usagePercent}%"
                ];
            }

            // 检查内存使用
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = ini_get('memory_limit');
            $memoryLimitBytes = $this->parseSize($memoryLimit);
            $memoryPercent = ($memoryUsage / $memoryLimitBytes) * 100;
            if ($memoryPercent > 90) {
                $warnings[] = [
                    'type' => 'memory',
                    'level' => 'critical',
                    'message' => "内存使用率过高: {$memoryPercent}%"
                ];
            }

            // 检查待处理的企业申请
            $pendingApplications = $this->databaseService->select(
                "SELECT COUNT(*) as count FROM user_applications WHERE status = 'pending'"
            )[0]['count'] ?? 0;

            if ($pendingApplications > 10) {
                $warnings[] = [
                    'type' => 'applications',
                    'level' => 'warning',
                    'message' => "有 {$pendingApplications} 个企业申请待处理"
                ];
            }

            // 检查失败的支付
            $failedPayments = $this->databaseService->select(
                "SELECT COUNT(*) as count FROM wallet_transactions 
                WHERE status = 'failed' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            )[0]['count'] ?? 0;

            if ($failedPayments > 5) {
                $warnings[] = [
                    'type' => 'payments',
                    'level' => 'critical',
                    'message' => "最近1小时有 {$failedPayments} 个支付失败"
                ];
            }

            // 性能指标
            $performanceMetrics = [
                'memory_usage' => $memoryUsage,
                'memory_peak' => memory_get_peak_usage(true),
                'memory_limit' => $memoryLimitBytes,
                'disk_free' => $freeSpace,
                'disk_total' => $totalSpace,
                'disk_usage_percent' => $usagePercent,
                'server_load' => sys_getloadavg(),
                'php_version' => PHP_VERSION,
                'uptime' => $this->getSystemUptime()
            ];

            return $this->jsonResponse([
                'data' => [
                    'health_checks' => $healthChecks,
                    'warnings' => $warnings,
                    'performance_metrics' => $performanceMetrics,
                    'timestamp' => now()
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取系统监控数据失败', [
                'error' => $e->getMessage()
            ]);

            return $this->jsonResponse([
                'error' => '获取系统监控数据失败',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 检查管理员权限
     */
    private function isAdmin(ServerRequestInterface $request): bool
    {
        $userRole = $request->getAttribute('user_role');
        return $userRole === 'admin';
    }
}

// 辅助函数
if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $storagePath = dirname(__DIR__, 2) . '/storage';
        return $path ? $storagePath . '/' . ltrim($path, '/') : $storagePath;
    }
}
