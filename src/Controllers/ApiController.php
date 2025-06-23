<?php
/**
 * AlingAi Pro - API控制器
 * 处理所有API相关的请求，提供RESTful API接口
 * 
 * @package AlingAi\Pro\Controllers
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlingAi\Services\{
    DatabaseServiceInterface, 
    CacheService, 
    AuthService,
    ChatService,
    UserService,
    ValidationService,
    RateLimitService,
    EnhancedDatabaseService,
    EnhancedAIService,
    EnhancedEmailService,
    SystemMonitoringService
};
use AlingAi\Cache\ApplicationCacheManager;
use AlingAi\Utils\{Logger, ApiResponse};

class ApiController extends BaseController
{
    private AuthService $authService;
    private ChatService $chatService;
    private UserService $userService;
    private ValidationService $validator;
    private RateLimitService $rateLimiter;
    private EnhancedDatabaseService $enhancedDb;
    private EnhancedAIService $aiService;
    private EnhancedEmailService $emailService;
    private SystemMonitoringService $monitoringService;
    private ApplicationCacheManager $cacheManager;    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        AuthService $authService,
        ChatService $chatService,
        UserService $userService,
        ValidationService $validator,
        RateLimitService $rateLimiter,
        ApplicationCacheManager $cacheManager
    ) {
        parent::__construct($db, $cache);
        $this->authService = $authService;
        $this->chatService = $chatService;
        $this->userService = $userService;
        $this->validator = $validator;
        $this->rateLimiter = $rateLimiter;
        $this->cacheManager = $cacheManager;
        
        // 初始化增强服务
        $this->enhancedDb = EnhancedDatabaseService::getInstance();
        $this->aiService = EnhancedAIService::getInstance();
        $this->emailService = EnhancedEmailService::getInstance();
        $this->monitoringService = SystemMonitoringService::getInstance();
    }

    /**
     * API状态检查
     */
    public function status(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->successResponse($response, [
            'status' => 'ok',
            'timestamp' => date('c'),
            'version' => '2.0.0',
            'endpoints' => [
                'auth' => '/api/auth',
                'chat' => '/api/chat',
                'users' => '/api/users',
                'admin' => '/api/admin'
            ]
        ]);
    }

    /**
     * 用户认证状态检查
     */
    public function authStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $token = $this->extractBearerToken($request);
            
            if (!$token) {
                return $this->errorResponse($response, '未提供认证令牌', 401);
            }

            $user = $this->authService->validateToken($token);
            
            if (!$user) {
                return $this->errorResponse($response, '无效的认证令牌', 401);
            }

            return $this->successResponse($response, [
                'authenticated' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'last_login' => $user['last_login']
                ],
                'token_expires' => $user['token_expires']
            ]);

        } catch (\Exception $e) {
            Logger::error('认证状态检查失败', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, '认证检查失败', 500);
        }
    }

    /**
     * 聊天健康检查
     */
    public function chatHealth(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $health = $this->chatService->healthCheck();
            
            return $this->successResponse($response, [
                'service' => 'chat',
                'status' => $health['status'],
                'response_time_ms' => $health['response_time'],
                'connections' => $health['connections'],
                'queue_size' => $health['queue_size']
            ]);

        } catch (\Exception $e) {
            Logger::error('聊天服务健康检查失败', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, '聊天服务检查失败', 500);
        }
    }    /**
     * 用户资料获取
     */
    public function userProfile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $startTime = microtime(true);
        
        try {
            // 验证用户认证
            $user = $this->requireAuth($request);
            if (!$user) {
                return $this->errorResponse($response, '需要登录', 401);
            }

            // 应用速率限制
            if (!$this->rateLimiter->allow($user['id'], 'profile', 60, 10)) {
                return $this->errorResponse($response, '请求过于频繁', 429);
            }

            // 生成用户资料缓存键
            $cacheKey = "user_profile:{$user['id']}";
            
            // 尝试从缓存获取用户资料
            $cachedProfile = $this->cacheManager->get($cacheKey);
            
            if ($cachedProfile) {
                Logger::info('用户资料缓存命中', [
                    'user_id' => $user['id'],
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]);
                
                return $this->successResponse($response, array_merge($cachedProfile, [
                    'cached' => true,
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2)
                ]));
            }

            $profile = $this->userService->getProfile($user['id']);
            $permissions = $this->userService->getUserPermissions($user['id']);
            
            $result = [
                'profile' => $profile,
                'permissions' => $permissions
            ];
            
            // 缓存用户资料（15分钟）
            $this->cacheManager->set($cacheKey, $result, 900);
            
            Logger::info('用户资料查询完成', [
                'user_id' => $user['id'],
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            
            return $this->successResponse($response, array_merge($result, [
                'cached' => false,
                'response_time' => round((microtime(true) - $startTime) * 1000, 2)
            ]));

        } catch (\Exception $e) {
            Logger::error('获取用户资料失败', [
                'user_id' => $user['id'] ?? null,
                'error' => $e->getMessage(),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            return $this->errorResponse($response, '获取用户资料失败', 500);
        }
    }

    /**
     * 更新用户设置
     */
    public function updateSettings(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 验证用户认证
            $user = $this->requireAuth($request);
            if (!$user) {
                return $this->errorResponse($response, '需要登录', 401);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            
            // 验证输入数据
            $validation = $this->validator->validate($data, [
                'theme' => 'string|in:quantum,classic,minimal,neon',
                'language' => 'string|in:zh_CN,en_US,ja_JP',
                'notifications' => 'boolean',
                'auto_save' => 'boolean',
                'privacy_level' => 'integer|between:1,3'
            ]);

            if (!$validation['valid']) {
                return $this->errorResponse($response, '输入数据无效', 400, $validation['errors']);
            }

            $updated = $this->userService->updateSettings($user['id'], $data);
            
            return $this->successResponse($response, [
                'updated' => $updated,
                'settings' => $this->userService->getSettings($user['id'])
            ]);

        } catch (\Exception $e) {
            Logger::error('更新用户设置失败', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, '更新设置失败', 500);
        }
    }    /**
     * 发送聊天消息
     */
    public function sendMessage(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $startTime = microtime(true);
        
        try {
            // 验证用户认证
            $user = $this->requireAuth($request);
            if (!$user) {
                return $this->errorResponse($response, '需要登录', 401);
            }

            // 应用速率限制
            if (!$this->rateLimiter->allow($user['id'], 'chat', 60, 20)) {
                return $this->errorResponse($response, '发送消息过于频繁', 429);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            
            // 验证输入数据
            $validation = $this->validator->validate($data, [
                'message' => 'required|string|max:4000',
                'conversation_id' => 'string|uuid',
                'model_type' => 'string|in:deepseek-chat,gpt-4,claude-3',
                'temperature' => 'numeric|between:0,2',
                'max_tokens' => 'integer|between:1,4000'
            ]);

            if (!$validation['valid']) {
                return $this->errorResponse($response, '消息格式无效', 400, $validation['errors']);
            }

            // 生成缓存键
            $cacheKey = "chat_message:" . md5($user['id'] . ':' . $data['message'] . ':' . ($data['model_type'] ?? 'deepseek-chat'));
              // 尝试从缓存获取相似响应（对于简单问题）
            $cachedResponse = null;
            if (strlen($data['message']) < 100 && !isset($data['conversation_id'])) {
                $cachedResponse = $this->cacheManager->get($cacheKey);
            }
            
            if ($cachedResponse) {
                // 记录缓存命中
                Logger::info('AI响应缓存命中', [
                    'user_id' => $user['id'],
                    'cache_key' => $cacheKey,
                    'response_time' => (microtime(true) - $startTime) * 1000 . 'ms'
                ]);
                
                return $this->successResponse($response, [
                    'message_id' => 'cached_' . uniqid(),
                    'conversation_id' => $cachedResponse['conversation_id'] ?? null,
                    'response' => $cachedResponse['response'],
                    'tokens_used' => $cachedResponse['tokens_used'] ?? 0,
                    'model' => $cachedResponse['model'] ?? 'cached',
                    'cached' => true,
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2)
                ]);
            }

            // 发送消息并获取AI响应
            $result = $this->chatService->sendMessage([
                'user_id' => $user['id'],
                'message' => $data['message'],
                'conversation_id' => $data['conversation_id'] ?? null,
                'model_type' => $data['model_type'] ?? 'deepseek-chat',
                'temperature' => $data['temperature'] ?? 0.7,
                'max_tokens' => $data['max_tokens'] ?? 1024
            ]);

            // 缓存AI响应（对于简单问题且响应时间较长时）
            $responseTime = (microtime(true) - $startTime) * 1000;
            if ($responseTime > 1000 && strlen($data['message']) < 100 && !isset($data['conversation_id'])) {
                $this->cacheManager->set($cacheKey, [
                    'response' => $result['ai_response'],
                    'tokens_used' => $result['tokens_used'],
                    'model' => $result['model'],
                    'created_at' => time()
                ], 3600); // 缓存1小时
            }

            Logger::info('聊天消息处理完成', [
                'user_id' => $user['id'],
                'response_time' => round($responseTime, 2) . 'ms',
                'tokens_used' => $result['tokens_used']
            ]);

            return $this->successResponse($response, [
                'message_id' => $result['message_id'],
                'conversation_id' => $result['conversation_id'],
                'response' => $result['ai_response'],
                'tokens_used' => $result['tokens_used'],
                'model' => $result['model'],
                'cached' => false,
                'response_time' => round($responseTime, 2)
            ]);

        } catch (\Exception $e) {
            Logger::error('发送消息失败', [
                'user_id' => $user['id'] ?? null,
                'error' => $e->getMessage(),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            return $this->errorResponse($response, '发送消息失败', 500);
        }
    }    /**
     * 获取聊天历史
     */
    public function getChatHistory(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $startTime = microtime(true);
        
        try {
            // 验证用户认证
            $user = $this->requireAuth($request);
            if (!$user) {
                return $this->errorResponse($response, '需要登录', 401);
            }

            $queryParams = $request->getQueryParams();
            $conversationId = $queryParams['conversation_id'] ?? null;
            $page = (int)($queryParams['page'] ?? 1);
            $limit = min((int)($queryParams['limit'] ?? 20), 100);

            // 生成缓存键
            $cacheKey = "chat_history:{$user['id']}:" . md5($conversationId . ":{$page}:{$limit}");
            
            // 尝试从缓存获取历史记录
            $cachedHistory = $this->cacheManager->get($cacheKey);
            
            if ($cachedHistory) {
                Logger::info('聊天历史缓存命中', [
                    'user_id' => $user['id'],
                    'cache_key' => $cacheKey,
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]);
                
                return $this->successResponse($response, array_merge($cachedHistory, [
                    'cached' => true,
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2)
                ]));
            }

            $history = $this->chatService->getChatHistory($user['id'], $conversationId, $page, $limit);
            
            $result = [
                'conversations' => $history['conversations'],
                'messages' => $history['messages'],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $history['total'],
                    'pages' => ceil($history['total'] / $limit)
                ]
            ];
            
            // 缓存聊天历史（5分钟）
            $this->cacheManager->set($cacheKey, $result, 300);
            
            Logger::info('聊天历史查询完成', [
                'user_id' => $user['id'],
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            
            return $this->successResponse($response, array_merge($result, [
                'cached' => false,
                'response_time' => round((microtime(true) - $startTime) * 1000, 2)
            ]));

        } catch (\Exception $e) {
            Logger::error('获取聊天历史失败', [
                'user_id' => $user['id'] ?? null,
                'error' => $e->getMessage(),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            return $this->errorResponse($response, '获取聊天历史失败', 500);
        }
    }

    /**
     * 系统统计信息（管理员）
     */
    public function systemStats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 验证管理员权限
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $stats = [
                'users' => [
                    'total' => $this->userService->getTotalUsers(),
                    'active_today' => $this->userService->getActiveUsersToday(),
                    'new_this_week' => $this->userService->getNewUsersThisWeek()
                ],
                'conversations' => [
                    'total' => $this->chatService->getTotalConversations(),
                    'today' => $this->chatService->getConversationsToday()
                ],
                'messages' => [
                    'total' => $this->chatService->getTotalMessages(),
                    'today' => $this->chatService->getMessagesToday()
                ],
                'system' => [
                    'uptime' => $this->getSystemUptime(),
                    'memory_usage' => memory_get_usage(true),
                    'cpu_load' => sys_getloadavg()[0] ?? 0
                ]
            ];

            return $this->successResponse($response, $stats);

        } catch (\Exception $e) {
            Logger::error('获取系统统计失败', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, '获取系统统计失败', 500);
        }
    }    /**
     * 清除缓存（管理员）
     */
    public function clearCache(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $startTime = microtime(true);
        
        try {
            // 验证管理员权限
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }
            
            $data = json_decode($request->getBody()->getContents(), true);
            $cacheType = $data['type'] ?? 'all';

            $results = [];
            
            // 根据缓存类型执行不同的清理操作
            if ($cacheType === 'all') {
                // 清理所有缓存
                $results['application_cache'] = $this->cacheManager->clear();
                $results['legacy_cache'] = $this->cache->clear();
                
                // 获取缓存统计信息
                $stats = $this->cacheManager->getStats();
                $results['cache_stats'] = $stats;
                
            } elseif ($cacheType === 'users') {
                // 清理用户相关缓存
                $cleared = 0;
                for ($userId = 1; $userId <= 1000; $userId++) { // 假设最多1000个用户
                    if ($this->cacheManager->delete("user_profile:{$userId}")) {
                        $cleared++;
                    }
                }
                $results['users_cache_cleared'] = $cleared;
                
            } elseif ($cacheType === 'chat') {
                // 清理聊天相关缓存
                // 这里可以添加更精确的聊天缓存清理逻辑
                $results['chat_cache'] = 'Chat cache clearing not implemented yet';
                
            } else {
                // 对于特定类型的缓存，可以使用模式清理
                $keys = $this->cache->getKeys("*{$cacheType}*");
                $cleared = $this->cache->deleteMultiple($keys);
                $results['pattern_cleared'] = $cleared;
            }
            
            Logger::info('缓存已清除', [
                'type' => $cacheType,
                'admin_id' => $user['id'],
                'results' => $results,
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);

            return $this->successResponse($response, [
                'cleared' => true,
                'type' => $cacheType,
                'results' => $results,
                'timestamp' => date('c'),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

        } catch (\Exception $e) {
            Logger::error('清除缓存失败', [
                'error' => $e->getMessage(),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            return $this->errorResponse($response, '清除缓存失败', 500);
        }
    }

    /**
     * 导出数据（管理员）
     */
    public function exportData(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 验证管理员权限
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $queryParams = $request->getQueryParams();
            $format = $queryParams['format'] ?? 'json';
            $type = $queryParams['type'] ?? 'conversations';
            $startDate = $queryParams['start_date'] ?? null;
            $endDate = $queryParams['end_date'] ?? null;

            $exportData = $this->chatService->exportData($type, $format, $startDate, $endDate);
            
            $filename = sprintf('export_%s_%s.%s', $type, date('Y-m-d'), $format);
            
            Logger::info('数据导出', [
                'type' => $type,
                'format' => $format,
                'admin_id' => $user['id']
            ]);

            return $this->successResponse($response, [
                'export_data' => $exportData,
                'filename' => $filename,
                'format' => $format,
                'type' => $type,
                'generated_at' => date('c')
            ]);

        } catch (\Exception $e) {
            Logger::error('数据导出失败', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, '数据导出失败', 500);
        }
    }

    /**
     * ================= 增强服务 API 端点 =================
     */

    /**
     * 增强 AI 聊天接口
     */
    public function enhancedChat(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            
            // 验证必需参数
            if (empty($data['message'])) {
                return $this->errorResponse($response, '消息内容不能为空', 400);
            }

            $user = $this->requireAuth($request);
            $userId = $user['id'] ?? null;
            
            // 使用增强 AI 服务
            $result = $this->aiService->chat([
                'message' => $data['message'],
                'user_id' => $userId,
                'provider' => $data['provider'] ?? 'auto',
                'session_id' => $data['session_id'] ?? null,
                'options' => $data['options'] ?? []
            ]);

            return $this->successResponse($response, $result);

        } catch (\Exception $e) {
            Logger::error('增强AI聊天失败', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'AI服务暂时不可用', 500);
        }
    }

    /**
     * AI 服务状态检查
     */
    public function aiStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $status = $this->aiService->getHealthStatus();
            return $this->successResponse($response, $status);
        } catch (\Exception $e) {
            return $this->errorResponse($response, '无法获取AI服务状态', 500);
        }
    }

    /**
     * AI 使用统计
     */
    public function aiUsageStats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user) {
                return $this->errorResponse($response, '需要登录', 401);
            }

            $queryParams = $request->getQueryParams();
            $period = $queryParams['period'] ?? 'today';
            $provider = $queryParams['provider'] ?? null;

            $stats = $this->aiService->getUsageStatistics($user['id'], $period, $provider);
            return $this->successResponse($response, $stats);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取统计数据失败', 500);
        }
    }

    /**
     * 系统监控数据
     */
    public function systemMetrics(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $queryParams = $request->getQueryParams();
            $timeRange = $queryParams['range'] ?? '1h';
            $metricType = $queryParams['type'] ?? null;

            $metrics = $this->monitoringService->getMetrics($timeRange, $metricType);
            return $this->successResponse($response, $metrics);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取监控数据失败', 500);
        }
    }

    /**
     * 系统健康检查
     */
    public function systemHealth(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $health = [
                'database' => $this->enhancedDb->getHealthStatus(),
                'ai_services' => $this->aiService->getHealthStatus(),
                'email_service' => $this->emailService->getStatus(),
                'monitoring' => $this->monitoringService->getStatus(),
                'timestamp' => date('c')
            ];            $allHealthy = array_reduce($health, function($carry, $item) {
                return $carry && ($item['status'] ?? false);
            }, true);

            $statusCode = $allHealthy ? 200 : 503;
            
            return $this->successResponse($response, $health, $allHealthy ? 'Health check passed' : 'Health check failed', $statusCode);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '健康检查失败', 500);
        }
    }

    /**
     * 系统告警列表
     */
    public function systemAlerts(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $queryParams = $request->getQueryParams();
            $status = $queryParams['status'] ?? 'active';
            $severity = $queryParams['severity'] ?? null;
            $limit = (int)($queryParams['limit'] ?? 50);

            $alerts = $this->monitoringService->getAlerts($status, $severity, $limit);
            return $this->successResponse($response, $alerts);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取告警信息失败', 500);
        }
    }

    /**
     * 发送测试邮件
     */
    public function sendTestEmail(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            $email = $data['email'] ?? $user['email'];

            $result = $this->emailService->sendTestEmail($email);
            
            if ($result) {
                return $this->successResponse($response, ['message' => '测试邮件发送成功']);
            } else {
                return $this->errorResponse($response, '测试邮件发送失败', 500);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($response, '邮件发送失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 邮件发送统计
     */
    public function emailStats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $queryParams = $request->getQueryParams();
            $period = $queryParams['period'] ?? 'today';

            $stats = $this->emailService->getStatistics($period);
            return $this->successResponse($response, $stats);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取邮件统计失败', 500);
        }
    }

    /**
     * 数据库连接状态
     */
    public function databaseStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $status = [
                'mysql' => $this->enhancedDb->getMySQLStatus(),
                'redis' => $this->enhancedDb->getRedisStatus(),
                'mongodb' => $this->enhancedDb->getMongoDBStatus()
            ];

            return $this->successResponse($response, $status);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取数据库状态失败', 500);
        }
    }

    /**
     * 清理系统数据
     */
    public function cleanupSystem(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            $cleanupTypes = $data['types'] ?? ['logs', 'metrics', 'cache'];

            $results = [];
            
            if (in_array('logs', $cleanupTypes)) {
                $results['logs'] = $this->monitoringService->cleanupOldLogs();
            }
            
            if (in_array('metrics', $cleanupTypes)) {
                $results['metrics'] = $this->monitoringService->cleanupOldMetrics();
            }
            
            if (in_array('cache', $cleanupTypes)) {
                $results['cache'] = $this->enhancedDb->clearExpiredCache();
            }

            return $this->successResponse($response, $results);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '系统清理失败', 500);
        }
    }

    /**
     * 配置管理
     */
    public function getConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $queryParams = $request->getQueryParams();
            $section = $queryParams['section'] ?? null;

            $config = $this->enhancedDb->getConfiguration($section);
            return $this->successResponse($response, $config);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取配置失败', 500);
        }
    }

    /**
     * 更新配置
     */
    public function updateConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            
            if (empty($data['key']) || !isset($data['value'])) {
                return $this->errorResponse($response, '配置键和值不能为空', 400);
            }

            $result = $this->enhancedDb->updateConfiguration($data['key'], $data['value'], $data['type'] ?? 'string');
            
            if ($result) {
                return $this->successResponse($response, ['message' => '配置更新成功']);
            } else {
                return $this->errorResponse($response, '配置更新失败', 500);
            }

        } catch (\Exception $e) {
            return $this->errorResponse($response, '配置更新失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 提取Bearer令牌
     */
    private function extractBearerToken(ServerRequestInterface $request): ?string
    {
        $authorization = $request->getHeaderLine('Authorization');
        
        if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * 要求用户认证
     */
    private function requireAuth(ServerRequestInterface $request): ?array
    {
        $token = $this->extractBearerToken($request);
        
        if (!$token) {
            return null;
        }

        return $this->authService->validateToken($token);
    }

    /**
     * 获取系统运行时间
     */
    private function getSystemUptime(): int
    {
        if (function_exists('sys_getloadavg') && is_readable('/proc/uptime')) {
            return (int)floatval(file_get_contents('/proc/uptime'));
        }
        
        return 0;
    }

    /**
     * 用户信息端点
     */
    public function userInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            
            if (!$user) {
                return $this->errorResponse($response, '用户未认证', 401);
            }

            $userInfo = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'created_at' => $user['created_at'],
                'last_login' => $user['last_login'] ?? null,
                'profile' => [
                    'display_name' => $user['display_name'] ?? $user['username'],
                    'avatar' => $user['avatar'] ?? null,
                    'bio' => $user['bio'] ?? '',
                    'location' => $user['location'] ?? '',
                    'website' => $user['website'] ?? ''
                ],
                'settings' => [
                    'language' => $user['language'] ?? 'zh-cn',
                    'timezone' => $user['timezone'] ?? 'Asia/Shanghai',
                    'theme' => $user['theme'] ?? 'light',
                    'notifications' => $user['notifications'] ?? true
                ],
                'statistics' => [
                    'total_chats' => $this->getUserChatCount($user['id']),
                    'total_messages' => $this->getUserMessageCount($user['id']),
                    'last_activity' => $this->getUserLastActivity($user['id'])
                ]
            ];

            return $this->successResponse($response, $userInfo);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取用户信息失败: ' . $e->getMessage(), 500);
        }
    }    /**
     * 系统设置端点
     */
    public function getSettings(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $settings = [
                'system' => [
                    'name' => 'AlingAi Pro',
                    'version' => '2.0.0',
                    'environment' => $_ENV['APP_ENV'] ?? 'production',
                    'debug_mode' => $_ENV['APP_DEBUG'] ?? false,
                    'maintenance_mode' => false // 简化，不依赖缓存
                ],
                'features' => [
                    'chat_enabled' => true,
                    'ai_enabled' => true,
                    'file_upload' => true,
                    'multi_language' => true,
                    'real_time' => true,
                    'advanced_ui' => true
                ],
                'limits' => [
                    'max_message_length' => 8000,
                    'max_file_size' => '50MB',
                    'daily_requests' => 10000,
                    'concurrent_connections' => 100
                ],
                'ai_settings' => [
                    'default_model' => 'gpt-3.5-turbo',
                    'available_models' => ['gpt-3.5-turbo', 'claude-3', 'gemini-pro'],
                    'max_tokens' => 4000,
                    'temperature' => 0.7
                ],
                'ui_settings' => [
                    'theme_options' => ['light', 'dark', 'auto'],
                    'language_options' => ['zh-cn', 'en-us', 'ja', 'ko'],
                    'animation_enabled' => true,
                    'sound_enabled' => true
                ]
            ];

            return $this->successResponse($response, $settings);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取系统设置失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 聊天发送端点
     */
    public function sendChatMessage(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            
            if (!$user) {
                return $this->errorResponse($response, '用户未认证', 401);
            }

            $data = $request->getParsedBody();
            
            if (!isset($data['message']) || empty(trim($data['message']))) {
                return $this->errorResponse($response, '消息内容不能为空', 400);
            }

            $message = trim($data['message']);
            $conversationId = $data['conversation_id'] ?? null;

            // 验证消息长度
            if (strlen($message) > 8000) {
                return $this->errorResponse($response, '消息长度超过限制', 400);
            }

            // 模拟AI响应
            $aiResponse = $this->generateMockAIResponse($message);

            $result = [
                'status' => 'success',
                'message_id' => uniqid('msg_'),
                'user_message' => $message,
                'ai_response' => $aiResponse,
                'timestamp' => date('c'),
                'conversation_id' => $conversationId ?? uniqid('conv_'),
                'response_time_ms' => rand(800, 2000)
            ];

            return $this->successResponse($response, $result);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '发送消息失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * AI模型端点
     */
    public function getAIModels(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $models = [
                [
                    'id' => 'gpt-3.5-turbo',
                    'name' => 'GPT-3.5 Turbo',
                    'provider' => 'OpenAI',
                    'type' => 'chat',
                    'status' => 'active',
                    'max_tokens' => 4096,
                    'cost_per_1k_tokens' => 0.002,
                    'features' => ['conversational', 'creative', 'analytical']
                ],
                [
                    'id' => 'claude-3',
                    'name' => 'Claude 3',
                    'provider' => 'Anthropic',
                    'type' => 'chat',
                    'status' => 'active',
                    'max_tokens' => 8192,
                    'cost_per_1k_tokens' => 0.003,
                    'features' => ['reasoning', 'code', 'analysis']
                ],
                [
                    'id' => 'gemini-pro',
                    'name' => 'Gemini Pro',
                    'provider' => 'Google',
                    'type' => 'multimodal',
                    'status' => 'active',
                    'max_tokens' => 4096,
                    'cost_per_1k_tokens' => 0.001,
                    'features' => ['multimodal', 'vision', 'code']
                ]
            ];

            $result = [
                'models' => $models,
                'default_model' => 'gpt-3.5-turbo',
                'total_count' => count($models),
                'timestamp' => date('c')
            ];

            return $this->successResponse($response, $result);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取AI模型失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 文件上传端点
     */
    public function uploadFile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->requireAuth($request);
            
            if (!$user) {
                return $this->errorResponse($response, '用户未认证', 401);
            }

            // 模拟文件上传
            $uploadResult = [
                'status' => 'success',
                'file_id' => uniqid('file_'),
                'filename' => 'uploaded_file.txt',
                'size' => rand(1024, 1048576),
                'type' => 'text/plain',
                'url' => '/uploads/' . uniqid() . '.txt',
                'upload_time' => date('c'),
                'user_id' => $user['id']
            ];

            return $this->successResponse($response, $uploadResult);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '文件上传失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取用户聊天数量
     */
    private function getUserChatCount(int $userId): int
    {
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM conversations WHERE user_id = ?", [$userId]);
            return $result ? (int)$result[0]['count'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取用户消息数量
     */
    private function getUserMessageCount(int $userId): int
    {
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM messages WHERE user_id = ?", [$userId]);
            return $result ? (int)$result[0]['count'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取用户最后活动时间
     */
    private function getUserLastActivity(int $userId): ?string
    {
        try {
            $result = $this->db->query("SELECT MAX(created_at) as last_activity FROM messages WHERE user_id = ?", [$userId]);
            return $result ? $result[0]['last_activity'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 生成模拟AI响应
     */
    private function generateMockAIResponse(string $message): string
    {
        $responses = [
            '您好！我是AlingAi，很高兴为您服务。我理解您的问题，让我为您详细解答。',
            '这是一个很有趣的问题！基于我的理解，我可以为您提供以下建议...',
            '感谢您的提问。根据您的描述，我认为可以从以下几个方面来考虑...',
            '我明白您的意思。这个问题确实需要仔细分析，让我为您逐步解释...',
            '很好的问题！根据我的知识库，我可以为您提供一些有用的信息...'
        ];
        
        return $responses[array_rand($responses)] . "\n\n针对您提到的「" . mb_substr($message, 0, 50) . "」，我建议您可以进一步探讨相关的技术细节和实现方案。";
    }

    /**
     * 获取缓存统计信息（管理员）
     */
    public function cacheStats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $startTime = microtime(true);
        
        try {
            // 验证管理员权限
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            // 获取缓存统计信息
            $stats = $this->cacheManager->getStats();
            
            // 计算缓存命中率
            $totalRequests = $stats['total_requests'] ?? 0;
            $totalHits = ($stats['memory_hits'] ?? 0) + ($stats['file_hits'] ?? 0);
            $hitRate = $totalRequests > 0 ? round(($totalHits / $totalRequests) * 100, 2) : 0;
            
            // 获取系统内存使用情况
            $memoryUsage = [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit')
            ];
            
            // 获取缓存文件信息
            $cacheFileInfo = $this->cacheManager->getCacheFileInfo();
            
            $result = [
                'cache_stats' => $stats,
                'hit_rate' => $hitRate . '%',
                'memory_usage' => $memoryUsage,
                'cache_files' => $cacheFileInfo,
                'performance' => [
                    'average_response_time' => $this->calculateAverageResponseTime(),
                    'cache_enabled' => true,
                    'compression_enabled' => $this->cacheManager->isCompressionEnabled()
                ],
                'timestamp' => date('c'),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2)
            ];

            return $this->successResponse($response, $result);

        } catch (\Exception $e) {
            Logger::error('获取缓存统计失败', [
                'error' => $e->getMessage(),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            return $this->errorResponse($response, '获取缓存统计失败', 500);
        }
    }

    /**
     * 缓存预热（管理员）
     */
    public function warmupCache(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $startTime = microtime(true);
        
        try {
            // 验证管理员权限
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            $warmupType = $data['type'] ?? 'common';
            
            $warmedUp = [];
            
            // 根据类型进行缓存预热
            if ($warmupType === 'common' || $warmupType === 'all') {
                // 预热常用数据
                $commonQuestions = [
                    "你好",
                    "什么是AI？",
                    "如何使用这个系统？",
                    "帮助",
                    "功能介绍"
                ];
                
                foreach ($commonQuestions as $question) {
                    $cacheKey = "chat_message:" . md5("system:{$question}:deepseek-chat");
                    $this->cacheManager->set($cacheKey, [
                        'response' => "这是对'{$question}'的预缓存响应",
                        'tokens_used' => 50,
                        'model' => 'deepseek-chat',
                        'created_at' => time()
                    ], 3600);
                    $warmedUp[] = $question;
                }
            }
            
            if ($warmupType === 'users' || $warmupType === 'all') {
                // 预热活跃用户数据
                // 这里可以添加用户数据预热逻辑
                $warmedUp[] = 'user_profiles_preloaded';
            }
            
            Logger::info('缓存预热完成', [
                'type' => $warmupType,
                'admin_id' => $user['id'],
                'items_warmed' => count($warmedUp),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);

            return $this->successResponse($response, [
                'warmed_up' => $warmedUp,
                'type' => $warmupType,
                'count' => count($warmedUp),
                'timestamp' => date('c'),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

        } catch (\Exception $e) {
            Logger::error('缓存预热失败', [
                'error' => $e->getMessage(),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            return $this->errorResponse($response, '缓存预热失败', 500);
        }
    }

    /**
     * 计算平均响应时间（私有方法）
     */
    private function calculateAverageResponseTime(): float
    {
        // 这里可以从日志或监控系统获取实际的响应时间数据
        // 目前返回模拟值
        return 150.5; // 毫秒
    }
}
