<?php

declare(strict_types=1);

namespace AlingAi\Core;

use AlingAi\Controllers\Api\{
    AuthApiController,
    ChatApiController,
    UserApiController,
    AdminApiController,
    SystemApiController,
    FileApiController,
    MonitorApiController
};
use AlingAi\Services\SecurityService;
use AlingAi\Services\PerformanceMonitorService;
use Exception;

/**
 * API 路由处理器
 * 
 * 统一处理所有API请求，连接路由管理器与实际控制器
 * 
 * @package AlingAi\Core
 * @version 1.0.0
 * @since 2024-12-19
 */
/**
 * ApiHandler 类
 *
 * @package AlingAi\Core
 */
class ApiHandler
{
    private ApiRouteManager $routeManager;
    private SecurityService $security;
    private PerformanceMonitorService $monitor;
    private array $controllerInstances = [];

    /**


     * __construct 方法


     *


     * @return void


     */


    public function __construct()
    {
        $this->routeManager = new ApiRouteManager();
        $this->security = new SecurityService();
        $this->monitor = new PerformanceMonitorService();
    }

    /**
     * 处理API请求
     */
    /**

     * handleRequest 方法

     *

     * @return void

     */

    public function handleRequest(): void
    {
        try {
            // 预检OPTIONS请求处理
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                $this->handleCorsOptions();
                return;
            }

            // 获取请求信息
            $method = $_SERVER['REQUEST_METHOD'];
            $path = $this->getRequestPath();
            
            // 记录请求开始
            $requestId = $this->monitor->startRequest($method, $path);
            
            // 查找路由
            $route = $this->routeManager->match($method, $path);
            
            if (!$route) {
                $this->sendNotFound();
                return;
            }

            // 执行中间件
            $this->executeMiddleware($route['middleware'] ?? []);
            
            // 执行控制器方法
            $response = $this->executeController($route);
            
            // 记录请求完成
            $this->monitor->endRequest($requestId, 200);
            
            // 发送响应
            $this->sendJsonResponse($response);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * 获取请求路径
     */
    /**

     * getRequestPath 方法

     *

     * @return void

     */

    private function getRequestPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // 移除查询参数
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // 标准化路径
        $path = '/' . trim($path, '/');
        
        return $path;
    }

    /**
     * 执行中间件
     */
    /**

     * executeMiddleware 方法

     *

     * @param array $middleware

     * @return void

     */

    private function executeMiddleware(array $middleware): void
    {
        foreach ($middleware as $middlewareName) {
            switch ($middlewareName) {
                case 'auth':
                    $this->requireAuthentication();
                    break;
                case 'admin':
                    $this->requireAdminRole();
                    break;
                case 'csrf':
                    $this->validateCsrfToken();
                    break;
                case 'rate_limit':
                    $this->checkRateLimit();
                    break;
                default:
                    // 自定义中间件处理
                    $this->executeCustomMiddleware($middlewareName);
            }
        }
    }

    /**
     * 执行控制器方法
     */
    /**

     * executeController 方法

     *

     * @param array $route

     * @return void

     */

    private function executeController(array $route): array
    {
        $controllerClass = $route['controller'];
        $method = $route['method'];
        $params = $route['params'] ?? [];
        
        // 获取控制器实例
        $controller = $this->getControllerInstance($controllerClass);
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Method {$method} not found in {$controllerClass}");
        }
        
        // 执行控制器方法
        return call_user_func_array([$controller, $method], $params);
    }

    /**
     * 获取控制器实例
     */
    /**

     * getControllerInstance 方法

     *

     * @param string $controllerClass

     * @return void

     */

    private function getControllerInstance(string $controllerClass): object
    {
        if (!isset($this->controllerInstances[$controllerClass])) {
            $fullClassName = $this->resolveControllerClass($controllerClass);
            
            if (!class_exists($fullClassName)) {
                throw new Exception("Controller class {$fullClassName} not found");
            }
            
            $this->controllerInstances[$controllerClass] = new $fullClassName();
        }
        
        return $this->controllerInstances[$controllerClass];
    }

    /**
     * 解析控制器类名
     */
    /**

     * resolveControllerClass 方法

     *

     * @param string $controllerClass

     * @return void

     */

    private function resolveControllerClass(string $controllerClass): string
    {
        // 如果已经是完整类名，直接返回
        if (strpos($controllerClass, '\\') !== false) {
            return $controllerClass;
        }
        
        // 根据控制器名称映射到实际类
        $controllerMap = [
            'AuthController' => AuthApiController::class,
            'ChatController' => ChatApiController::class,
            'UserController' => UserApiController::class,
            'AdminController' => AdminApiController::class,
            'SystemController' => SystemApiController::class,
            'FileController' => FileApiController::class,
            'MonitorController' => MonitorApiController::class,
        ];
        
        return $controllerMap[$controllerClass] ?? 
               "AlingAi\\Controllers\\Api\\{$controllerClass}";
    }

    /**
     * 处理CORS预检请求
     */
    /**

     * handleCorsOptions 方法

     *

     * @return void

     */

    private function handleCorsOptions(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        header('Access-Control-Max-Age: 86400');
        http_response_code(200);
        exit;
    }

    /**
     * 要求认证
     */
    /**

     * requireAuthentication 方法

     *

     * @return void

     */

    private function requireAuthentication(): void
    {
        $token = $this->getBearerToken();
        
        if (!$token) {
            $this->sendError('Authentication required', 401);
        }
        
        $user = $this->security->validateJwtToken($token);
        if (!$user) {
            $this->sendError('Invalid or expired token', 401);
        }
        
        // 将用户信息存储到全局变量中，供控制器使用
        $GLOBALS['current_user'] = $user;
    }

    /**
     * 要求管理员角色
     */
    /**

     * requireAdminRole 方法

     *

     * @return void

     */

    private function requireAdminRole(): void
    {
        $user = $GLOBALS['current_user'] ?? null;
        
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            $this->sendError('Admin privileges required', 403);
        }
    }

    /**
     * 验证CSRF令牌
     */
    /**

     * validateCsrfToken 方法

     *

     * @return void

     */

    private function validateCsrfToken(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                $_POST['_token'] ?? 
                $_GET['_token'] ?? null;
        
        if (!$token || !$this->security->validateCsrfToken($token)) {
            $this->sendError('Invalid CSRF token', 403);
        }
    }

    /**
     * 检查速率限制
     */
    /**

     * checkRateLimit 方法

     *

     * @return void

     */

    private function checkRateLimit(): void
    {
        $clientId = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (!$this->security->checkRateLimit($clientId)) {
            $this->sendError('Rate limit exceeded', 429);
        }
    }

    /**
     * 执行自定义中间件
     */
    /**

     * executeCustomMiddleware 方法

     *

     * @param string $middlewareName

     * @return void

     */

    private function executeCustomMiddleware(string $middlewareName): void
    {
        $middlewareClass = "AlingAi\\Middleware\\{$middlewareName}";
        
        if (class_exists($middlewareClass)) {
            $middleware = new $middlewareClass();
            if (method_exists($middleware, 'handle')) {
                $middleware->handle();
            }
        }
    }

    /**
     * 获取Bearer令牌
     */
    /**

     * getBearerToken 方法

     *

     * @return void

     */

    private function getBearerToken(): ?string
    {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') === 0) {
                return substr($authHeader, 7);
            }
        }
        
        return null;
    }

    /**
     * 发送404错误
     */
    /**

     * sendNotFound 方法

     *

     * @return void

     */

    private function sendNotFound(): void
    {
        $this->sendError('Endpoint not found', 404);
    }

    /**
     * 处理异常
     */
    /**

     * handleException 方法

     *

     * @param Exception $e

     * @return void

     */

    private function handleException(Exception $e): void
    {
        // 记录错误
        $this->monitor->logError('API Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // 根据异常类型发送不同的错误响应
        if ($e instanceof \InvalidArgumentException) {
            $this->sendError($e->getMessage(), 400);
        } elseif ($e instanceof \UnauthorizedAccessException) {
            $this->sendError($e->getMessage(), 401);
        } elseif ($e instanceof \ForbiddenAccessException) {
            $this->sendError($e->getMessage(), 403);
        } else {
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * 发送JSON响应
     */
    /**

     * sendJsonResponse 方法

     *

     * @param array $data

     * @param int $statusCode

     * @return void

     */

    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        // CORS头
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * 发送错误响应
     */
    /**

     * sendError 方法

     *

     * @param string $message

     * @param int $statusCode

     * @return void

     */

    private function sendError(string $message, int $statusCode = 400): void
    {
        $this->sendJsonResponse([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ], $statusCode);
    }

    /**
     * 注册所有API路由
     */
    public static function registerRoutes(): void
    {
        $routeManager = new ApiRouteManager();
        
        // 认证相关路由
        $routeManager->post('/api/auth/login', 'AuthController', 'login');
        $routeManager->post('/api/auth/register', 'AuthController', 'register');
        $routeManager->post('/api/auth/refresh', 'AuthController', 'refresh', ['auth']);
        $routeManager->post('/api/auth/logout', 'AuthController', 'logout', ['auth']);
        $routeManager->get('/api/auth/user', 'AuthController', 'getUser', ['auth']);
        $routeManager->post('/api/auth/forgot-password', 'AuthController', 'forgotPassword');
        $routeManager->post('/api/auth/reset-password', 'AuthController', 'resetPassword');
        
        // 聊天相关路由
        $routeManager->post('/api/chat/send', 'ChatController', 'sendMessage', ['auth']);
        $routeManager->get('/api/chat/conversations', 'ChatController', 'getConversations', ['auth']);
        $routeManager->get('/api/chat/conversations/{id}', 'ChatController', 'getConversation', ['auth']);
        $routeManager->delete('/api/chat/conversations/{id}', 'ChatController', 'deleteConversation', ['auth']);
        $routeManager->post('/api/chat/regenerate', 'ChatController', 'regenerateResponse', ['auth']);
        
        // 用户相关路由
        $routeManager->get('/api/user/profile', 'UserController', 'getProfile', ['auth']);
        $routeManager->put('/api/user/profile', 'UserController', 'updateProfile', ['auth']);
        $routeManager->post('/api/user/avatar', 'UserController', 'uploadAvatar', ['auth']);
        $routeManager->get('/api/user/settings', 'UserController', 'getSettings', ['auth']);
        $routeManager->put('/api/user/settings', 'UserController', 'updateSettings', ['auth']);
        
        // 管理员路由
        $routeManager->get('/api/admin/users', 'AdminController', 'getUsers', ['auth', 'admin']);
        $routeManager->post('/api/admin/users', 'AdminController', 'createUser', ['auth', 'admin']);
        $routeManager->get('/api/admin/users/{id}', 'AdminController', 'getUser', ['auth', 'admin']);
        $routeManager->put('/api/admin/users/{id}', 'AdminController', 'updateUser', ['auth', 'admin']);
        $routeManager->delete('/api/admin/users/{id}', 'AdminController', 'deleteUser', ['auth', 'admin']);
        $routeManager->get('/api/admin/statistics', 'AdminController', 'getStatistics', ['auth', 'admin']);
        
        // 系统路由
        $routeManager->get('/api/system/status', 'SystemController', 'getStatus');
        $routeManager->get('/api/system/health', 'SystemController', 'getHealth');
        $routeManager->get('/api/system/version', 'SystemController', 'getVersion');
        
        // 文件上传路由
        $routeManager->post('/api/files/upload', 'FileController', 'upload', ['auth']);
        $routeManager->delete('/api/files/{id}', 'FileController', 'delete', ['auth']);
        
        // 监控路由
        $routeManager->get('/api/monitor/metrics', 'MonitorController', 'getMetrics', ['auth', 'admin']);
        $routeManager->get('/api/monitor/logs', 'MonitorController', 'getLogs', ['auth', 'admin']);
    }
}
