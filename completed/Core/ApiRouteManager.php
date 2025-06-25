<?php
/**
 * API路由管理�?
 * 统一管理所有API端点，提供路由注册、中间件支持和文档生�?
 */

namespace AlingAi\Core;

use AlingAi\Services\SecurityService;
use AlingAi\Services\PerformanceMonitorService;

/**
 * ApiRouteManager �?
 *
 * @package AlingAi\Core
 */
class ApiRouteManager {
    
    private $routes = [];
    private $middleware = [];
    private $securityService;
    private $performanceMonitor;
    private $baseUrl;
    
    /**

    
     * __construct 方法

    
     *

    
     * @return void

    
     */

    
    public function __construct() {
        $this->securityService = new SecurityService(];
        $this->performanceMonitor = new PerformanceMonitorService(];
        $this->baseUrl = '/api';
        $this->registerDefaultRoutes(];
    }
    
    /**
     * 注册默认路由
     */
    /**

     * registerDefaultRoutes 方法

     *

     * @return void

     */

    private function registerDefaultRoutes() {
        // 认证相关路由
        $this->group('/auth', function() {
            $this->post('/login', 'AuthController@login', ['validate', 'csrf'];
            $this->post('/register', 'AuthController@register', ['validate', 'csrf'];
            $this->post('/logout', 'AuthController@logout', ['auth'];
            $this->post('/refresh', 'AuthController@refreshToken', ['auth'];
            $this->post('/forgot-password', 'AuthController@forgotPassword', ['validate', 'csrf'];
            $this->post('/reset-password', 'AuthController@resetPassword', ['validate', 'csrf'];
            $this->get('/profile', 'AuthController@getProfile', ['auth'];
            $this->put('/profile', 'AuthController@updateProfile', ['auth', 'validate', 'csrf'];
        }];
        
        // 聊天相关路由
        $this->group('/chat', function() {
            $this->get('/conversations', 'ChatController@getConversations', ['auth'];
            $this->post('/conversations', 'ChatController@createConversation', ['auth', 'validate', 'csrf'];
            $this->get('/conversations/{id}', 'ChatController@getConversation', ['auth'];
            $this->post('/conversations/{id}/messages', 'ChatController@sendMessage', ['auth', 'validate', 'csrf'];
            $this->delete('/conversations/{id}', 'ChatController@deleteConversation', ['auth', 'csrf'];
        }];
        
        // 文件上传路由
        $this->group('/files', function() {
            $this->post('/upload', 'FileController@upload', ['auth', 'csrf'];
            $this->get('/download/{id}', 'FileController@download', ['auth'];
            $this->delete('/{id}', 'FileController@delete', ['auth', 'csrf'];
            $this->get('/list', 'FileController@list', ['auth'];
        }];
        
        // 系统监控路由
        $this->group('/monitor', function() {
            $this->get('/status', 'MonitorController@getSystemStatus', ['auth', 'admin'];
            $this->get('/performance', 'MonitorController@getPerformanceData', ['auth', 'admin'];
            $this->get('/logs', 'MonitorController@getLogs', ['auth', 'admin'];
            $this->post('/alerts', 'MonitorController@createAlert', ['auth', 'admin', 'csrf'];
        }];
        
        // 数据库管理路�?
        $this->group('/database', function() {
            $this->get('/status', 'DatabaseController@getStatus', ['auth', 'admin'];
            $this->post('/migrate', 'DatabaseController@migrate', ['auth', 'admin', 'csrf'];
            $this->post('/seed', 'DatabaseController@seed', ['auth', 'admin', 'csrf'];
            $this->get('/tables', 'DatabaseController@getTables', ['auth', 'admin'];
            $this->post('/backup', 'DatabaseController@backup', ['auth', 'admin', 'csrf'];
        }];
        
        // 用户管理路由
        $this->group('/users', function() {
            $this->get('/', 'UserController@list', ['auth', 'admin'];
            $this->get('/{id}', 'UserController@get', ['auth'];
            $this->put('/{id}', 'UserController@update', ['auth', 'csrf'];
            $this->delete('/{id}', 'UserController@delete', ['auth', 'admin', 'csrf'];
            $this->post('/{id}/roles', 'UserController@assignRole', ['auth', 'admin', 'csrf'];
        }];
        
        // 系统配置路由
        $this->group('/config', function() {
            $this->get('/', 'ConfigController@getConfig', ['auth', 'admin'];
            $this->put('/', 'ConfigController@updateConfig', ['auth', 'admin', 'csrf'];
            $this->post('/cache/clear', 'ConfigController@clearCache', ['auth', 'admin', 'csrf'];
            $this->get('/info', 'ConfigController@getSystemInfo', ['auth', 'admin'];
        }];
        
        // API文档路由
        $this->get('/docs', 'ApiController@getDocs'];
        $this->get('/docs/openapi', 'ApiController@getOpenApiSpec'];
        $this->get('/health', 'ApiController@healthCheck'];
        $this->get('/version', 'ApiController@getVersion'];
    }
    
    /**
     * 注册GET路由
     */
    /**

     * get 方法

     *

     * @param mixed $path

     * @param mixed $handler

     * @param mixed $middleware

     * @return void

     */

    public function get($path, $handler, $middleware = [) {
        $this->addRoute('GET', $path, $handler, $middleware];
    }
    
    /**
     * 注册POST路由
     */
    /**

     * post 方法

     *

     * @param mixed $path

     * @param mixed $handler

     * @param mixed $middleware

     * @return void

     */

    public function post($path, $handler, $middleware = [) {
        $this->addRoute('POST', $path, $handler, $middleware];
    }
    
    /**
     * 注册PUT路由
     */
    /**

     * put 方法

     *

     * @param mixed $path

     * @param mixed $handler

     * @param mixed $middleware

     * @return void

     */

    public function put($path, $handler, $middleware = [) {
        $this->addRoute('PUT', $path, $handler, $middleware];
    }
    
    /**
     * 注册DELETE路由
     */
    /**

     * delete 方法

     *

     * @param mixed $path

     * @param mixed $handler

     * @param mixed $middleware

     * @return void

     */

    public function delete($path, $handler, $middleware = [) {
        $this->addRoute('DELETE', $path, $handler, $middleware];
    }
    
    /**
     * 注册PATCH路由
     */
    /**

     * patch 方法

     *

     * @param mixed $path

     * @param mixed $handler

     * @param mixed $middleware

     * @return void

     */

    public function patch($path, $handler, $middleware = [) {
        $this->addRoute('PATCH', $path, $handler, $middleware];
    }
    
    /**
     * 路由分组
     */
    /**

     * group 方法

     *

     * @param mixed $prefix

     * @param mixed $callback

     * @return void

     */

    public function group($prefix, $callback) {
        $previousPrefix = $this->currentPrefix ?? '';
        $this->currentPrefix = $previousPrefix . $prefix;
        
        $callback->call($this];
        
        $this->currentPrefix = $previousPrefix;
    }
    
    /**
     * 添加路由
     */
    /**

     * addRoute 方法

     *

     * @param mixed $method

     * @param mixed $path

     * @param mixed $handler

     * @param mixed $middleware

     * @return void

     */

    private function addRoute($method, $path, $handler, $middleware = [) {
        $fullPath = ($this->currentPrefix ?? '') . $path;
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $middleware,
            'pattern' => $this->convertToPattern($fullPath)
        ];
    }
    
    /**
     * 将路径转换为正则表达式模�?
     */
    /**

     * convertToPattern 方法

     *

     * @param mixed $path

     * @return void

     */

    private function convertToPattern($path) {
        // �?{id} 转换�?(?P<id>[^/) +)
        $pattern = preg_replace('/\{(^}) +)\}/', '(?P<$1>[^/) +)', $path];
        return '#^' . $pattern . '$#';
    }
    
    /**
     * 处理请求
     */
    /**

     * handleRequest 方法

     *

     * @return void

     */

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH];
        
        // 移除基础URL前缀
        if (strpos($path, $this->baseUrl) === 0) {
            $path = substr($path, strlen($this->baseUrl)];
        }
        
        $startTime = microtime(true];
        
        try {
            // 安全检�?
            $this->performSecurityChecks(];
            
            // 查找匹配的路�?
            $route = $this->findRoute($method, $path];
            
            if (!$route) {
                $this->sendResponse(404, ['error' => 'Route not found'];
                return;
            }
            
            // 执行中间�?
            $this->executeMiddleware($route['middleware'];
            
            // 执行路由处理�?
            $response = $this->executeHandler($route['handler'],  $route['params'] ?? [];
            
            // 发送响�?
            $this->sendResponse(200, $response];
            
        } catch (\Exception $e) {
            $this->handleException($e];
        } finally {
            // 记录性能指标
            $endTime = microtime(true];
            $this->performanceMonitor->monitorApiRequest(
                $path,
                $method,
                $startTime,
                $endTime,
                http_response_code(),
                isset($e) ? $e->getMessage() : null
            ];
        }
    }
    
    /**
     * 查找匹配的路�?
     */
    /**

     * findRoute 方法

     *

     * @param mixed $method

     * @param mixed $path

     * @return void

     */

    private function findRoute($method, $path) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'],  $path, $matches)) {
                // 提取路径参数
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                
                $route['params'] = $params;
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * 执行安全检�?
     */
    /**

     * performSecurityChecks 方法

     *

     * @return void

     */

    private function performSecurityChecks() {
        $ipAddress = $this->getClientIp(];
        
        // 检查IP是否被封�?
        if (!$this->securityService->checkRateLimit($ipAddress)) {
            throw new \Exception('Rate limit exceeded', 429];
        }
        
        // 验证User-Agent
        if (empty($_SERVER['HTTP_USER_AGENT')) {
            $this->securityService->recordThreat('missing_user_agent', [
                'ip_address' => $ipAddress
            ];
        }
        
        // 检查恶意请求模�?
        $this->detectMaliciousPatterns(];
    }
    
    /**
     * 检测恶意请求模�?
     */
    /**

     * detectMaliciousPatterns 方法

     *

     * @return void

     */

    private function detectMaliciousPatterns() {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // 检查SQL注入模式
        $sqlPatterns = ['/union.*select/i', '/drop.*table/i', '/insert.*into/i'];
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                $this->securityService->recordThreat('sql_injection_attempt', [
                    'uri' => $uri,
                    'pattern' => $pattern
                ];
                throw new \Exception('Malicious request detected', 403];
            }
        }
        
        // 检查恶意User-Agent
        $maliciousAgents = ['sqlmap', 'nikto', 'nmap', 'masscan'];
        foreach ($maliciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                $this->securityService->recordThreat('malicious_user_agent', [
                    'user_agent' => $userAgent
                ];
                throw new \Exception('Malicious user agent detected', 403];
            }
        }
    }
    
    /**
     * 执行中间�?
     */
    /**

     * executeMiddleware 方法

     *

     * @param mixed $middlewareList

     * @return void

     */

    private function executeMiddleware($middlewareList) {
        foreach ($middlewareList as $middleware) {
            switch ($middleware) {
                case 'auth':
                    $this->authenticateUser(];
                    break;
                case 'admin':
                    $this->requireAdminRole(];
                    break;
                case 'csrf':
                    $this->validateCsrfToken(];
                    break;
                case 'validate':
                    $this->validateInput(];
                    break;
            }
        }
    }
    
    /**
     * 用户认证中间�?
     */
    /**

     * authenticateUser 方法

     *

     * @return void

     */

    private function authenticateUser() {
        $token = $this->getBearerToken(];
        
        if (!$token) {
            throw new \Exception('Authentication required', 401];
        }
        
        // 验证JWT令牌
        try {
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(
                $_ENV['JWT_SECRET'] ?? 'default_secret',
                'HS256'
            )];
            
            $_SESSION['user_id'] = $decoded->sub;
            $_SESSION['user_role'] = $decoded->role ?? 'user';
            
        } catch (\Exception $e) {
            throw new \Exception('Invalid token', 401];
        }
    }
    
    /**
     * 管理员权限中间件
     */
    /**

     * requireAdminRole 方法

     *

     * @return void

     */

    private function requireAdminRole() {
        if (empty($_SESSION['user_role') || $_SESSION['user_role'] !== 'admin') {
            throw new \Exception('Admin access required', 403];
        }
    }
    
    /**
     * CSRF令牌验证中间�?
     */
    /**

     * validateCsrfToken 方法

     *

     * @return void

     */

    private function validateCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return; // GET请求不需要CSRF验证
        }
        
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? null;
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        
        if (!$this->securityService->validateCsrfToken($token, $sessionToken)) {
            throw new \Exception('CSRF token validation failed', 403];
        }
    }
    
    /**
     * 输入验证中间�?
     */
    /**

     * validateInput 方法

     *

     * @return void

     */

    private function validateInput() {
        $input = json_decode(file_get_contents('php://input'], true) ?? $_POST;
        
        foreach ($input as $key => $value) {
            $sanitized = $this->securityService->sanitizeInput($value];
            $input[$key] = $sanitized;
        }
        
        // 将清理后的输入存储在全局变量�?
        $GLOBALS['sanitized_input'] = $input;
    }
    
    /**
     * 执行路由处理�?
     */
    /**

     * executeHandler 方法

     *

     * @param mixed $handler

     * @param mixed $params

     * @return void

     */

    private function executeHandler($handler, $params = [) {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler];
            
            $controllerClass = "\\AlingAi\\Controllers\\Api\\{$controller}";
            
            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller {$controllerClass} not found", 500];
            }
            
            $controllerInstance = new $controllerClass(];
            
            if (!method_exists($controllerInstance, $method)) {
                throw new \Exception("Method {$method} not found in {$controllerClass}", 500];
            }
            
            return call_user_func_[$controllerInstance, $method],  [$params];
        }
        
        if (is_callable($handler)) {
            return $handler($params];
        }
        
        throw new \Exception('Invalid handler', 500];
    }
    
    /**
     * 发送响�?
     */
    /**

     * sendResponse 方法

     *

     * @param mixed $statusCode

     * @param mixed $data

     * @return void

     */

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode];
        header('Content-Type: application/json; charset=utf-8'];
        header('X-Powered-By: AlingAi Pro API'];
        header('X-Response-Time: ' . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT') * 1000, 2) . 'ms'];
        
        // 添加安全�?
        header('X-Content-Type-Options: nosniff'];
        header('X-Frame-Options: DENY'];
        header('X-XSS-Protection: 1; mode=block'];
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains'];
        
        // CORS�?
        header('Access-Control-Allow-Origin: *'];
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'];
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token'];
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT];
        exit;
    }
    
    /**
     * 处理异常
     */
    /**

     * handleException 方法

     *

     * @param \Exception $e

     * @return void

     */

    private function handleException(\Exception $e) {
        $statusCode = $e->getCode() ?: 500;
        
        $response = [
            'error' => true,
            'message' => $e->getMessage(),
            'timestamp' => time()
        ];
        
        // 在开发环境中包含堆栈跟踪
        if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
            $response['trace'] = $e->getTraceAsString(];
        }
        
        // 记录错误
        error_log("API Error [{$statusCode}): " . $e->getMessage()];
        
        $this->sendResponse($statusCode, $response];
    }
    
    /**
     * 获取Bearer令牌
     */
    /**

     * getBearerToken 方法

     *

     * @return void

     */

    private function getBearerToken() {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * 获取客户端IP
     */
    /**

     * getClientIp 方法

     *

     * @return void

     */

    private function getClientIp() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key)) {
                $ips = explode(',', $_SERVER[$key];
                return trim($ips[0];
            }
        }
        
        return 'unknown';
    }
    
    /**
     * 生成API文档
     */
    /**

     * generateApiDocs 方法

     *

     * @return void

     */

    public function generateApiDocs() {
        $docs = [
            'info' => [
                'title' => 'AlingAi Pro API',
                'version' => '1.0.0',
                'description' => 'AlingAi Pro系统REST API文档'
            ], 
            'baseUrl' => $this->baseUrl,
            'endpoints' => []
        ];
        
        foreach ($this->routes as $route) {
            $docs['endpoints'][] = [
                'method' => $route['method'], 
                'path' => $route['path'], 
                'handler' => $route['handler'], 
                'middleware' => $route['middleware'], 
                'description' => $this->getEndpointDescription($route)
            ];
        }
        
        return $docs;
    }
    
    /**
     * 获取端点描述
     */
    /**

     * getEndpointDescription 方法

     *

     * @param mixed $route

     * @return void

     */

    private function getEndpointDescription($route) {
        $descriptions = [
            '/auth/login' => '用户登录',
            '/auth/register' => '用户注册',
            '/auth/logout' => '用户登出',
            '/chat/conversations' => '获取或创建聊天对�?,
            '/monitor/status' => '获取系统状�?,
            '/database/status' => '获取数据库状�?,
            // 添加更多描述...
        ];
        
        return $descriptions[$route['path']] ?? '暂无描述';
    }
    
    /**
     * 获取所有路�?
     */
    /**

     * getRoutes 方法

     *

     * @return void

     */

    public function getRoutes() {
        return $this->routes;
    }
}

