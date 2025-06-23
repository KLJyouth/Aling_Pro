<?php
/**
 * AlingAi Pro 权限验证中间件
 * 集成权限管理系统到应用路由中
 */
namespace AlingAi\Middleware;

use AlingAi\Security\PermissionManager;
use AlingAi\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Exception;

class PermissionMiddleware implements MiddlewareInterface
{
    private $permissionManager;
    private $db;
    private $routePermissions;
    
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
        $this->permissionManager = new PermissionManager($db->getPdo());
        $this->initializeRoutePermissions();
    }
    
    /**
     * 处理中间件
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $route = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        // 检查是否需要权限验证
        if (!$this->requiresPermission($route, $method)) {
            return $handler->handle($request);
        }
        
        try {
            // 获取用户信息
            $user = $this->getCurrentUser($request);
            if (!$user) {
                return $this->createUnauthorizedResponse('Authentication required');
            }
            
            // 检查权限
            $requiredPermission = $this->getRequiredPermission($route, $method);
            if (!$this->permissionManager->hasPermission($user['id'], $requiredPermission['module'], $requiredPermission['level'])) {
                return $this->createForbiddenResponse('Insufficient permissions');
            }
            
            // 记录权限验证日志
            $this->logPermissionCheck($user['id'], $route, $method, true);
            
            // 将用户信息添加到请求中
            $request = $request->withAttribute('user', $user);
            $request = $request->withAttribute('permissions', $this->permissionManager->getUserPermissions($user['id']));
            
            return $handler->handle($request);
            
        } catch (Exception $e) {
            $this->logPermissionCheck(null, $route, $method, false, $e->getMessage());
            return $this->createErrorResponse('Permission check failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 初始化路由权限配置
     */
    private function initializeRoutePermissions(): void
    {
        $this->routePermissions = [
            // 管理员路由
            '/api/admin/*' => [
                'module' => PermissionManager::MODULE_USER_MANAGEMENT,
                'level' => PermissionManager::LEVEL_ADMIN,
                'methods' => ['GET', 'POST', 'PUT', 'DELETE']
            ],
            
            // 增强管理员路由
            '/api/enhanced-admin/*' => [
                'module' => PermissionManager::MODULE_SYSTEM_MONITOR,
                'level' => PermissionManager::LEVEL_ADMIN,
                'methods' => ['GET', 'POST', 'PUT', 'DELETE']
            ],
            
            // 系统监控
            '/api/enhanced-admin/monitoring/*' => [
                'module' => PermissionManager::MODULE_SYSTEM_MONITOR,
                'level' => PermissionManager::LEVEL_MODERATOR,
                'methods' => ['GET']
            ],
            
            // 备份管理
            '/api/enhanced-admin/backup/*' => [
                'module' => PermissionManager::MODULE_BACKUP_MANAGE,
                'level' => PermissionManager::LEVEL_ADMIN,
                'methods' => ['GET', 'POST', 'DELETE']
            ],
            
            // 安全扫描
            '/api/enhanced-admin/security/*' => [
                'module' => PermissionManager::MODULE_SECURITY_SCAN,
                'level' => PermissionManager::LEVEL_ADMIN,
                'methods' => ['GET', 'POST']
            ],
            
            // 性能测试
            '/api/enhanced-admin/performance/*' => [
                'module' => PermissionManager::MODULE_PERFORMANCE_TEST,
                'level' => PermissionManager::LEVEL_MODERATOR,
                'methods' => ['GET', 'POST']
            ],
            
            // 系统配置
            '/api/enhanced-admin/config/*' => [
                'module' => PermissionManager::MODULE_SYSTEM_CONFIG,
                'level' => PermissionManager::LEVEL_SUPER_ADMIN,
                'methods' => ['GET', 'POST', 'PUT']
            ],
            
            // 缓存管理
            '/api/cache/*' => [
                'module' => PermissionManager::MODULE_SYSTEM_CONFIG,
                'level' => PermissionManager::LEVEL_ADMIN,
                'methods' => ['GET', 'POST', 'DELETE']
            ],
            
            // 测试系统
            '/api/test/*' => [
                'module' => PermissionManager::MODULE_PERFORMANCE_TEST,
                'level' => PermissionManager::LEVEL_MODERATOR,
                'methods' => ['GET', 'POST']
            ],
            
            // 用户设置 (用户只能访问自己的设置)
            '/api/user/settings' => [
                'module' => PermissionManager::MODULE_USER_MANAGEMENT,
                'level' => PermissionManager::LEVEL_USER,
                'methods' => ['GET', 'POST', 'PUT'],
                'self_only' => true
            ],
            
            // 公共API端点 (无需特殊权限)
            '/api/health' => [
                'module' => null,
                'level' => PermissionManager::LEVEL_GUEST,
                'methods' => ['GET']
            ],
            
            '/api/version' => [
                'module' => null,
                'level' => PermissionManager::LEVEL_GUEST,
                'methods' => ['GET']
            ]
        ];
    }
    
    /**
     * 检查路由是否需要权限验证
     */
    private function requiresPermission(string $route, string $method): bool
    {
        // 公共路由无需验证
        $publicRoutes = [
            '/api/auth/login',
            '/api/auth/register',
            '/api/auth/forgot-password',
            '/api/health',
            '/api/version'
        ];
        
        if (in_array($route, $publicRoutes)) {
            return false;
        }
        
        // 静态资源无需验证
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/', $route)) {
            return false;
        }
        
        // API路由需要验证
        if (strpos($route, '/api/') === 0) {
            return true;
        }
        
        // 管理界面需要验证
        if (strpos($route, '/admin') === 0 || strpos($route, '/profile') === 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取当前用户信息
     */
    private function getCurrentUser(Request $request): ?array
    {
        // 从Authorization头获取token
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            // 尝试从session获取
            session_start();
            if (isset($_SESSION['user_id'])) {
                return $this->getUserById($_SESSION['user_id']);
            }
            return null;
        }
        
        // 解析Bearer token
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            return $this->getUserByToken($token);
        }
        
        return null;
    }
    
    /**
     * 根据ID获取用户
     */
    private function getUserById(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, role FROM users WHERE id = ? AND status = 'active'");
            $stmt->execute([$userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * 根据token获取用户
     */
    private function getUserByToken(string $token): ?array
    {
        try {
            // 这里应该验证JWT token或查询token表
            // 简化实现：假设token是base64编码的用户ID
            $userId = base64_decode($token);
            if (is_numeric($userId)) {
                return $this->getUserById((int) $userId);
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * 获取路由所需权限
     */
    private function getRequiredPermission(string $route, string $method): array
    {
        // 精确匹配
        if (isset($this->routePermissions[$route])) {
            $permission = $this->routePermissions[$route];
            if (in_array($method, $permission['methods'])) {
                return $permission;
            }
        }
        
        // 通配符匹配
        foreach ($this->routePermissions as $pattern => $permission) {
            if (strpos($pattern, '*') !== false) {
                $regex = str_replace('*', '.*', preg_quote($pattern, '/'));
                if (preg_match("/^{$regex}$/", $route) && in_array($method, $permission['methods'])) {
                    return $permission;
                }
            }
        }
        
        // 默认权限
        return [
            'module' => PermissionManager::MODULE_USER_MANAGEMENT,
            'level' => PermissionManager::LEVEL_USER,
            'methods' => ['GET', 'POST', 'PUT', 'DELETE']
        ];
    }
    
    /**
     * 记录权限检查日志
     */
    private function logPermissionCheck(?int $userId, string $route, string $method, bool $success, ?string $error = null): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO system_monitoring (
                    metric_type, metric_name, metric_value, metadata, collected_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            
            $metadata = json_encode([
                'user_id' => $userId,
                'route' => $route,
                'method' => $method,
                'success' => $success,
                'error' => $error,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            $stmt->execute([
                'security',
                'permission_check',
                $success ? 1 : 0,
                $metadata
            ]);
        } catch (Exception $e) {
            error_log("Failed to log permission check: " . $e->getMessage());
        }
    }
    
    /**
     * 创建未授权响应
     */
    private function createUnauthorizedResponse(string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
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
    private function createForbiddenResponse(string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Forbidden',
            'message' => $message,
            'code' => 403
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }
    
    /**
     * 创建错误响应
     */
    private function createErrorResponse(string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Internal Server Error',
            'message' => $message,
            'code' => 500
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
    
    /**
     * 验证用户是否只能访问自己的资源
     */
    private function validateSelfAccess(Request $request, array $user, array $permission): bool
    {
        if (!isset($permission['self_only']) || !$permission['self_only']) {
            return true;
        }
        
        // 从路由参数或查询参数获取目标用户ID
        $routeUserId = $this->extractUserIdFromRoute($request);
        
        return $routeUserId === null || $routeUserId === $user['id'];
    }
    
    /**
     * 从路由中提取用户ID
     */
    private function extractUserIdFromRoute(Request $request): ?int
    {
        // 从路径参数提取
        $path = $request->getUri()->getPath();
        if (preg_match('/\/users\/(\d+)/', $path, $matches)) {
            return (int) $matches[1];
        }
        
        // 从查询参数提取
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['user_id'])) {
            return (int) $queryParams['user_id'];
        }
        
        // 从请求体提取
        $body = $request->getParsedBody();
        if (is_array($body) && isset($body['user_id'])) {
            return (int) $body['user_id'];
        }
        
        return null;
    }
    
    /**
     * 检查IP限制
     */
    private function checkIpRestrictions(Request $request, array $user): bool
    {
        // 如果用户有IP限制设置
        try {
            $stmt = $this->db->prepare("
                SELECT setting_value 
                FROM user_settings 
                WHERE user_id = ? AND category = 'security' AND setting_key = 'allowed_ips'
            ");
            $stmt->execute([$user['id']]);
            $result = $stmt->fetch();
            
            if ($result) {
                $allowedIps = json_decode($result['setting_value'], true);
                $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
                
                if (!empty($allowedIps) && !in_array($clientIp, $allowedIps)) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            // 如果检查失败，默认允许访问
            return true;
        }
    }
    
    /**
     * 检查时间限制
     */
    private function checkTimeRestrictions(array $user): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT setting_value 
                FROM user_settings 
                WHERE user_id = ? AND category = 'security' AND setting_key = 'access_hours'
            ");
            $stmt->execute([$user['id']]);
            $result = $stmt->fetch();
            
            if ($result) {
                $accessHours = json_decode($result['setting_value'], true);
                $currentHour = (int) date('H');
                
                if (!empty($accessHours) && !in_array($currentHour, $accessHours)) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            // 如果检查失败，默认允许访问
            return true;
        }
    }
}
