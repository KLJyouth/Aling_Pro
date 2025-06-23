<?php

namespace AlingAi\Middleware;

use AlingAi\Services\AuthService;
use AlingAi\Services\PermissionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;

/**
 * 权限控制中间件
 * 
 * 提供细粒度的权限控制功能
 * 优化性能：权限缓存、批量检查、智能路由
 * 增强安全性：动态权限验证、访问审计、权限继承
 */
class PermissionControlMiddleware implements MiddlewareInterface
{
    private AuthService $authService;
    private PermissionService $permissionService;
    private LoggerInterface $logger;
    private array $config;
    
    public function __construct(
        AuthService $authService,
        PermissionService $permissionService,
        LoggerInterface $logger,
        array $config = []
    ) {
        $this->authService = $authService;
        $this->permissionService = $permissionService;
        $this->logger = $logger;
        $this->config = array_merge([
            'cache_permissions' => true,
            'cache_ttl' => 300,
            'audit_log' => true,
            'strict_mode' => true,
            'default_permission' => 'deny',
            'exempt_routes' => [
                '/health',
                '/api/health',
                '/docs',
                '/api/docs'
            ]
        ], $config);
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        $requestId = uniqid('perm_', true);
        
        try {
            // 检查是否为豁免路由
            if ($this->isExemptRoute($request)) {
                return $handler->handle($request);
            }
            
            // 获取用户信息
            $user = $this->getCurrentUser($request);
            if (!$user) {
                return $this->createUnauthorizedResponse('用户未认证');
            }
            
            // 获取请求信息
            $resource = $this->getResourceFromRequest($request);
            $action = $this->getActionFromRequest($request);
            
            // 检查权限
            $hasPermission = $this->checkPermission($user, $resource, $action);
            
            if (!$hasPermission) {
                $this->logAccessDenied($user, $resource, $action, $request);
                return $this->createForbiddenResponse('权限不足');
            }
            
            // 记录访问日志
            if ($this->config['audit_log']) {
                $this->logAccessGranted($user, $resource, $action, $request, $startTime);
            }
            
            return $handler->handle($request);
            
        } catch (\Exception $e) {
            $this->logger->error('权限检查异常', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($this->config['strict_mode']) {
                return $this->createForbiddenResponse('权限检查失败');
            }
            
            return $handler->handle($request);
        }
    }
    
    /**
     * 检查是否为豁免路由
     */
    private function isExemptRoute(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        
        foreach ($this->config['exempt_routes'] as $exemptRoute) {
            if (strpos($path, $exemptRoute) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取当前用户
     */
    private function getCurrentUser(ServerRequestInterface $request): ?array
    {
        $user = $request->getAttribute('user');
        
        if (!$user) {
            // 尝试从认证服务获取用户
            $token = $this->extractToken($request);
            if ($token) {
                $user = $this->authService->validateToken($token);
            }
        }
        
        return $user;
    }
    
    /**
     * 从请求中提取令牌
     */
    private function extractToken(ServerRequestInterface $request): ?string
    {
        // 从Authorization头提取
        $authHeader = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        // 从查询参数提取
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['token'])) {
            return $queryParams['token'];
        }
        
        // 从Cookie提取
        $cookies = $request->getCookieParams();
        if (isset($cookies['auth_token'])) {
            return $cookies['auth_token'];
        }
        
        return null;
    }
    
    /**
     * 从请求中获取资源标识
     */
    private function getResourceFromRequest(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        // 解析路径获取资源类型
        $pathParts = explode('/', trim($path, '/'));
        
        if (count($pathParts) >= 2 && $pathParts[0] === 'api') {
            return $pathParts[1]; // 例如: /api/users -> users
        }
        
        if (count($pathParts) >= 1) {
            return $pathParts[0]; // 例如: /users -> users
        }
        
        return 'default';
    }
    
    /**
     * 从请求中获取操作类型
     */
    private function getActionFromRequest(ServerRequestInterface $request): string
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // 根据HTTP方法和路径确定操作
        switch ($method) {
            case 'GET':
                if (preg_match('/\/\d+$/', $path)) {
                    return 'read'; // 获取单个资源
                }
                return 'list'; // 获取资源列表
                
            case 'POST':
                return 'create';
                
            case 'PUT':
            case 'PATCH':
                return 'update';
                
            case 'DELETE':
                return 'delete';
                
            default:
                return 'unknown';
        }
    }
    
    /**
     * 检查用户权限
     */
    private function checkPermission(array $user, string $resource, string $action): bool
    {
        $userId = $user['id'] ?? $user['user_id'];
        
        // 检查是否为超级管理员
        if ($this->isSuperAdmin($user)) {
            return true;
        }
        
        // 构建权限键
        $permissionKey = "{$resource}:{$action}";
        
        // 检查缓存
        if ($this->config['cache_permissions']) {
            $cacheKey = "user_permissions_{$userId}";
            $cachedPermissions = $this->permissionService->getCachedPermissions($cacheKey);
            
            if ($cachedPermissions !== null) {
                return in_array($permissionKey, $cachedPermissions) || 
                       in_array('*', $cachedPermissions) ||
                       in_array("{$resource}:*", $cachedPermissions);
            }
        }
        
        // 实时检查权限
        $hasPermission = $this->permissionService->checkPermission($userId, $resource, $action);
        
        // 缓存权限结果
        if ($this->config['cache_permissions'] && $hasPermission) {
            $this->permissionService->cacheUserPermissions($userId, $permissionKey);
        }
        
        return $hasPermission;
    }
    
    /**
     * 检查是否为超级管理员
     */
    private function isSuperAdmin(array $user): bool
    {
        $role = $user['role'] ?? '';
        $permissions = $user['permissions'] ?? [];
        
        return $role === 'super_admin' || 
               in_array('*', $permissions) ||
               in_array('super_admin', $permissions);
    }
    
    /**
     * 记录访问被拒绝
     */
    private function logAccessDenied(array $user, string $resource, string $action, ServerRequestInterface $request): void
    {
        $this->logger->warning('访问被拒绝', [
            'user_id' => $user['id'] ?? $user['user_id'],
            'username' => $user['username'] ?? 'unknown',
            'resource' => $resource,
            'action' => $action,
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'ip' => $this->getClientIp($request),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 记录访问被允许
     */
    private function logAccessGranted(array $user, string $resource, string $action, ServerRequestInterface $request, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->logger->info('访问被允许', [
            'user_id' => $user['id'] ?? $user['user_id'],
            'username' => $user['username'] ?? 'unknown',
            'resource' => $resource,
            'action' => $action,
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'ip' => $this->getClientIp($request),
            'duration_ms' => $duration,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($serverParams[$header])) {
                $ip = $serverParams[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * 创建未授权响应
     */
    private function createUnauthorizedResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message,
            'code' => 401,
            'timestamp' => date('c')
        ]));
        
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('WWW-Authenticate', 'Bearer');
    }
    
    /**
     * 创建禁止访问响应
     */
    private function createForbiddenResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message,
            'code' => 403,
            'timestamp' => date('c')
        ]));
        
        return $response
            ->withStatus(403)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
    
    /**
     * 批量检查权限
     */
    public function checkMultiplePermissions(array $user, array $permissions): array
    {
        $results = [];
        
        foreach ($permissions as $permission) {
            $resource = $permission['resource'] ?? '';
            $action = $permission['action'] ?? '';
            
            $results[] = [
                'resource' => $resource,
                'action' => $action,
                'allowed' => $this->checkPermission($user, $resource, $action)
            ];
        }
        
        return $results;
    }
    
    /**
     * 获取用户权限列表
     */
    public function getUserPermissions(array $user): array
    {
        $userId = $user['id'] ?? $user['user_id'];
        return $this->permissionService->getUserPermissions($userId);
    }
    
    /**
     * 清除用户权限缓存
     */
    public function clearUserPermissionCache(array $user): bool
    {
        $userId = $user['id'] ?? $user['user_id'];
        return $this->permissionService->clearUserPermissionCache($userId);
    }
}
