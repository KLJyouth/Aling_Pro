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
    DatabaseService, 
    CacheService, 
    AuthService,
    ChatService,
    UserService,
    ValidationService,
    RateLimitService
};
use AlingAi\Utils\{Logger, ApiResponse};

class ApiController extends BaseController
{
    private AuthService $authService;
    private ChatService $chatService;
    private UserService $userService;
    private ValidationService $validator;
    private RateLimitService $rateLimiter;

    public function __construct(
        DatabaseService $db,
        CacheService $cache,
        AuthService $authService,
        ChatService $chatService,
        UserService $userService,
        ValidationService $validator,
        RateLimitService $rateLimiter
    ) {
        parent::__construct($db, $cache);
        $this->authService = $authService;
        $this->chatService = $chatService;
        $this->userService = $userService;
        $this->validator = $validator;
        $this->rateLimiter = $rateLimiter;
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
    }

    /**
     * 用户资料获取
     */
    public function userProfile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
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

            $profile = $this->userService->getProfile($user['id']);
            
            return $this->successResponse($response, [
                'profile' => $profile,
                'permissions' => $this->userService->getUserPermissions($user['id'])
            ]);

        } catch (\Exception $e) {
            Logger::error('获取用户资料失败', ['error' => $e->getMessage()]);
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
    }

    /**
     * 发送聊天消息
     */
    public function sendMessage(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
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

            // 发送消息并获取AI响应
            $result = $this->chatService->sendMessage([
                'user_id' => $user['id'],
                'message' => $data['message'],
                'conversation_id' => $data['conversation_id'] ?? null,
                'model_type' => $data['model_type'] ?? 'deepseek-chat',
                'temperature' => $data['temperature'] ?? 0.7,
                'max_tokens' => $data['max_tokens'] ?? 1024
            ]);

            return $this->successResponse($response, [
                'message_id' => $result['message_id'],
                'conversation_id' => $result['conversation_id'],
                'response' => $result['ai_response'],
                'tokens_used' => $result['tokens_used'],
                'model' => $result['model']
            ]);

        } catch (\Exception $e) {
            Logger::error('发送消息失败', [
                'user_id' => $user['id'] ?? null,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse($response, '发送消息失败', 500);
        }
    }

    /**
     * 获取聊天历史
     */
    public function getChatHistory(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
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

            $history = $this->chatService->getChatHistory($user['id'], $conversationId, $page, $limit);
            
            return $this->successResponse($response, [
                'conversations' => $history['conversations'],
                'messages' => $history['messages'],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $history['total'],
                    'pages' => ceil($history['total'] / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            Logger::error('获取聊天历史失败', ['error' => $e->getMessage()]);
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
    }

    /**
     * 清除缓存（管理员）
     */
    public function clearCache(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 验证管理员权限
            $user = $this->requireAuth($request);
            if (!$user || $user['role'] !== 'admin') {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }
            
            $data = json_decode($request->getBody()->getContents(), true);
            $cacheType = $data['type'] ?? 'all';

            // 根据缓存类型执行不同的清理操作
            if ($cacheType === 'all') {
                $cleared = $this->cache->clear();
            } else {
                // 对于特定类型的缓存，可以使用标签或模式清理
                $keys = $this->cache->getKeys("*{$cacheType}*");
                $cleared = $this->cache->deleteMultiple($keys);
            }
            
            Logger::info('缓存已清除', [
                'type' => $cacheType,
                'admin_id' => $user['id']
            ]);

            return $this->successResponse($response, [
                'cleared' => $cleared,
                'type' => $cacheType,
                'timestamp' => date('c')
            ]);

        } catch (\Exception $e) {
            Logger::error('清除缓存失败', ['error' => $e->getMessage()]);
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
}
