<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\Models\{User, Conversation, Document, UserLog};
use AlingAi\Services\{CacheService, DatabaseServiceInterface, EmailService};
use AlingAi\Utils\Logger;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Illuminate\Database\Eloquent\ModelNotFoundException;

// 包含辅助函数
require_once __DIR__ . '/../Utils/Helpers.php';

/**
 * 管理员控制器
 * 处理系统管理相关的所有操作
 */
class AdminController extends BaseController
{
    private CacheService $cacheService;
    private DatabaseServiceInterface $databaseService;
    private EmailService $emailService;
    protected Logger $logger;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        EmailService $emailService
    ) {
        parent::__construct($db, $cache);
        $this->cacheService = $cache;
        $this->databaseService = $db;
        $this->emailService = $emailService;
        $this->logger = new Logger();
    }    /**
     * 获取管理员面板仪表板数据
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

            $cacheKey = 'admin_dashboard';
            
            // 尝试从缓存获取
            $data = $this->cacheService->get($cacheKey);
            if ($data === null) {
                // 用户统计
                $userStats = [
                    'total' => User::count(),
                    'active' => User::where('status', 'active')->count(),
                    'inactive' => User::where('status', 'inactive')->count(),
                    'banned' => User::where('status', 'banned')->count(),
                    'new_today' => User::whereDate('created_at', today())->count(),
                    'new_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
                ];

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
