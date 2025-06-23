<?php

declare(strict_types=1);

namespace AlingAi\Middleware;

use AlingAi\Security\PermissionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;

/**
 * 权限集成中间件
 * 负责验证用户权限和API访问控制
 */
class PermissionIntegrationMiddleware implements MiddlewareInterface
{
    private PermissionManager $permissionManager;
    private LoggerInterface $logger;
    
    // 权限配置映射
    private array $routePermissions = [
        '/api/system-management' => [
            'module' => PermissionManager::MODULE_SYSTEM_CONFIG,
            'level' => PermissionManager::LEVEL_ADMIN
        ],
        '/api/cache-management' => [
            'module' => PermissionManager::MODULE_SYSTEM_MONITOR,
            'level' => PermissionManager::LEVEL_MODERATOR
        ],
        '/api/user-management' => [
            'module' => PermissionManager::MODULE_USER_MANAGEMENT,
            'level' => PermissionManager::LEVEL_ADMIN
        ],
        '/api/backup' => [
            'module' => PermissionManager::MODULE_BACKUP_MANAGE,
            'level' => PermissionManager::LEVEL_ADMIN
        ],
        '/api/security' => [
            'module' => PermissionManager::MODULE_SECURITY_SCAN,
            'level' => PermissionManager::LEVEL_ADMIN
        ],
        '/api/performance' => [
            'module' => PermissionManager::MODULE_PERFORMANCE_TEST,
            'level' => PermissionManager::LEVEL_MODERATOR
        ]
    ];
    
    // 公开路由（无需权限验证）
    private array $publicRoutes = [
        '/api/health',
        '/api/status',
        '/api/auth/login',
        '/api/auth/register'
    ];
    
    public function __construct(PermissionManager $permissionManager, LoggerInterface $logger)
    {
        $this->permissionManager = $permissionManager;
        $this->logger = $logger;
    }
    
    /**
     * 处理中间件逻辑
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        // 记录请求信息
        $this->logger->info('权限中间件处理请求', [
            'uri' => $uri,
            'method' => $method,
            'ip' => $this->getClientIp($request)
        ]);
        
        // 检查是否为公开路由
        if ($this->isPublicRoute($uri)) {
            return $handler->handle($request);
        }
        
        // 提取用户信息
        $user = $this->extractUser($request);
        if (!$user) {
            return $this->createUnauthorizedResponse('Authentication required');
        }
        
        // 检查权限
        $permissionCheck = $this->checkPermission($uri, $user);
        if (!$permissionCheck['success']) {
            return $this->createForbiddenResponse($permissionCheck['message']);
        }
        
        // 将用户信息添加到请求中
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('permissions', $permissionCheck['permissions']);
        
        return $handler->handle($request);
    }
    
    /**
     * 检查是否为公开路由
     */
    private function isPublicRoute(string $uri): bool
    {
        foreach ($this->publicRoutes as $publicRoute) {
            if (str_starts_with($uri, $publicRoute)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 从请求中提取用户信息
     */
    private function extractUser(ServerRequestInterface $request): ?array
    {
        // 从Authorization头中获取token
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            return null;
        }
        
        // 支持Bearer token格式
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            $token = $authHeader;
        }
        
        // 验证token
        $tokenValidation = $this->permissionManager->validateToken($token);
        if (!$tokenValidation['valid']) {
            $this->logger->warning('无效的访问token', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $tokenValidation['error'] ?? 'Unknown error'
            ]);
            return null;
        }
        
        return [
            'id' => $tokenValidation['user_id'],
            'token' => $token,
            'permissions' => $tokenValidation['permissions'] ?? []
        ];
    }
    
    /**
     * 检查用户权限
     */
    private function checkPermission(string $uri, array $user): array
    {
        // 获取路由对应的权限要求
        $requiredPermission = $this->getRequiredPermission($uri);
        
        if (!$requiredPermission) {
            // 没有特定权限要求，允许已认证用户访问
            return [
                'success' => true,
                'message' => 'No specific permission required',
                'permissions' => $user['permissions']
            ];
        }
        
        // 验证用户权限
        $hasPermission = $this->permissionManager->hasPermission(
            $user['id'],
            $requiredPermission['module'],
            $requiredPermission['level']
        );
        
        if (!$hasPermission) {
            $this->logger->warning('权限不足', [
                'user_id' => $user['id'],
                'uri' => $uri,
                'required_module' => $requiredPermission['module'],
                'required_level' => $requiredPermission['level']
            ]);
            
            return [
                'success' => false,
                'message' => 'Insufficient permissions',
                'required' => $requiredPermission,
                'user_level' => $this->permissionManager->getUserPermissionLevel(
                    $user['id'],
                    $requiredPermission['module']
                )
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Permission granted',
            'permissions' => $user['permissions'],
            'required' => $requiredPermission
        ];
    }
    
    /**
     * 获取路由要求的权限
     */
    private function getRequiredPermission(string $uri): ?array
    {
        // 精确匹配
        if (isset($this->routePermissions[$uri])) {
            return $this->routePermissions[$uri];
        }
        
        // 前缀匹配
        foreach ($this->routePermissions as $route => $permission) {
            if (str_starts_with($uri, $route)) {
                return $permission;
            }
        }
        
        return null;
    }
    
    /**
     * 创建未授权响应
     */
    private function createUnauthorizedResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => 'Unauthorized',
            'message' => $message,
            'code' => 401
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
    
    /**
     * 创建禁止访问响应
     */
    private function createForbiddenResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => 'Forbidden',
            'message' => $message,
            'code' => 403
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }
    
    /**
     * 获取客户端IP地址
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        // 检查各种可能的IP头
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipHeaders as $header) {
            if (!empty($serverParams[$header])) {
                $ip = $serverParams[$header];
                // 处理多个IP的情况（取第一个）
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }
        
        return 'unknown';
    }
    
    /**
     * 添加路由权限配置
     */
    public function addRoutePermission(string $route, string $module, int $level): void
    {
        $this->routePermissions[$route] = [
            'module' => $module,
            'level' => $level
        ];
    }
    
    /**
     * 移除路由权限配置
     */
    public function removeRoutePermission(string $route): void
    {
        unset($this->routePermissions[$route]);
    }
    
    /**
     * 添加公开路由
     */
    public function addPublicRoute(string $route): void
    {
        if (!in_array($route, $this->publicRoutes)) {
            $this->publicRoutes[] = $route;
        }
    }
    
    /**
     * 移除公开路由
     */
    public function removePublicRoute(string $route): void
    {
        $this->publicRoutes = array_filter($this->publicRoutes, fn($r) => $r !== $route);
    }
    
    /**
     * 获取权限配置
     */
    public function getPermissionConfig(): array
    {
        return [
            'route_permissions' => $this->routePermissions,
            'public_routes' => $this->publicRoutes
        ];
    }
    
    /**
     * 验证特定权限
     */
    public function validateSpecificPermission(
        ServerRequestInterface $request,
        string $module,
        int $level
    ): array {
        $user = $this->extractUser($request);
        
        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not authenticated'
            ];
        }
        
        $hasPermission = $this->permissionManager->hasPermission($user['id'], $module, $level);
        
        return [
            'success' => $hasPermission,
            'user_id' => $user['id'],
            'module' => $module,
            'required_level' => $level,
            'user_level' => $this->permissionManager->getUserPermissionLevel($user['id'], $module),
            'message' => $hasPermission ? 'Permission granted' : 'Permission denied'
        ];
    }
}
