<?php
/**
 * 路由器类
 * 处理HTTP路由和中间件执行
 */

namespace AlingAi\Core;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use AlingAi\Config\Routes;

class Router {
    
    private $routes = [];
    private $middleware = [];
    private $currentRoute;
    
    public function __construct() {
        $this->loadRoutes();
        $this->loadMiddleware();
    }
    
    /**
     * 加载路由配置
     */
    private function loadRoutes() {
        // Web路由
        $webRoutes = Routes::getWebRoutes();
        foreach ($webRoutes as $path => $config) {
            $this->routes['GET'][$path] = $config;
        }
        
        // API路由
        $apiRoutes = Routes::getApiRoutes();
        foreach ($apiRoutes as $route => $config) {
            list($method, $path) = explode(' ', $route, 2);
            $this->routes[$method][$path] = $config;
        }
    }
    
    /**
     * 加载中间件配置
     */
    private function loadMiddleware() {
        $this->middleware = Routes::getMiddleware();
    }
    
    /**
     * 路由分发
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // 清理路径
        $path = rtrim($path, '/') ?: '/';
        
        // 查找匹配的路由
        $route = $this->findRoute($method, $path);
        
        if (!$route) {
            return $this->handleNotFound($response);
        }
        
        $this->currentRoute = $route;
        
        try {
            // 执行中间件链
            $response = $this->executeMiddleware($request, $response, $route);
            
            // 执行控制器
            return $this->executeController($request, $response, $route);
            
        } catch (\Exception $e) {
            return $this->handleError($response, $e);
        }
    }
    
    /**
     * 查找路由
     */
    private function findRoute(string $method, string $path): ?array {
        // 精确匹配
        if (isset($this->routes[$method][$path])) {
            return array_merge($this->routes[$method][$path], ['path' => $path, 'params' => []]);
        }
        
        // 参数路由匹配
        foreach ($this->routes[$method] ?? [] as $routePath => $config) {
            $params = $this->matchParameterRoute($routePath, $path);
            if ($params !== false) {
                return array_merge($config, ['path' => $routePath, 'params' => $params]);
            }
        }
        
        return null;
    }
    
    /**
     * 匹配参数路由
     */
    private function matchParameterRoute(string $routePath, string $requestPath): array|false {
        // 将路由路径转换为正则表达式
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches); // 移除完整匹配
            
            // 提取参数名
            preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
            $paramNames = $paramNames[1];
            
            // 组合参数
            $params = [];
            foreach ($paramNames as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
            
            return $params;
        }
        
        return false;
    }
    
    /**
     * 执行中间件
     */
    private function executeMiddleware(ServerRequestInterface $request, ResponseInterface $response, array $route): ResponseInterface {
        $middlewareGroups = [];
        
        // 全局中间件
        $middlewareGroups[] = $this->middleware['global'] ?? [];
        
        // API中间件
        if (strpos($route['path'], '/api/') === 0) {
            $middlewareGroups[] = $this->middleware['api'] ?? [];
        }
        
        // 认证中间件
        if ($this->requiresAuth($route)) {
            $middlewareGroups[] = $this->middleware['auth'] ?? [];
        }
        
        // 管理员中间件
        if ($this->requiresAdmin($route)) {
            $middlewareGroups[] = $this->middleware['admin'] ?? [];
        }
          // 展平中间件数组
        $allMiddleware = [];
        foreach ($middlewareGroups as $group) {
            if (is_array($group)) {
                $allMiddleware = array_merge($allMiddleware, $group);
            } else {
                $allMiddleware[] = $group;
            }
        }
        
        // 执行中间件
        foreach ($allMiddleware as $middlewareClass) {
            if (is_string($middlewareClass)) {
                $middleware = $this->createMiddleware($middlewareClass);
                $response = $middleware->process($request, $response);
            }
        }
        
        return $response;
    }
    
    /**
     * 执行控制器
     */
    private function executeController(ServerRequestInterface $request, ResponseInterface $response, array $route): ResponseInterface {
        $controllerClass = 'AlingAi\\Controllers\\' . $route['controller'];
        $method = $route['method'];
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller not found: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \Exception("Method not found: {$controllerClass}::{$method}");
        }
        
        // 将路由参数添加到请求
        if (!empty($route['params'])) {
            $request = $request->withAttribute('routeParams', $route['params']);
        }
        
        return $controller->$method($request, $response);
    }
    
    /**
     * 创建中间件实例
     */
    private function createMiddleware(string $middlewareClass) {
        $fullClass = 'AlingAi\\Middleware\\' . $middlewareClass;
        
        if (!class_exists($fullClass)) {
            throw new \Exception("Middleware not found: {$fullClass}");
        }
        
        return new $fullClass();
    }
    
    /**
     * 检查是否需要认证
     */
    private function requiresAuth(array $route): bool {
        $authRequiredPaths = [
            '/dashboard',
            '/profile',
            '/chat',
            '/api/chat/',
            '/api/user/',
        ];
        
        foreach ($authRequiredPaths as $path) {
            if (strpos($route['path'], $path) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 检查是否需要管理员权限
     */
    private function requiresAdmin(array $route): bool {
        $adminRequiredPaths = [
            '/admin',
            '/api/admin/',
        ];
        
        foreach ($adminRequiredPaths as $path) {
            if (strpos($route['path'], $path) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 处理404错误
     */
    private function handleNotFound(ResponseInterface $response): ResponseInterface {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>页面未找到 - AlingAi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
        <p class="text-xl text-gray-600 mb-8">页面未找到</p>
        <a href="/" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            返回首页
        </a>
    </div>
</body>
</html>
HTML;
        
        $response->getBody()->write($html);
        return $response->withStatus(404)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
    
    /**
     * 处理错误
     */
    private function handleError(ResponseInterface $response, \Exception $e): ResponseInterface {
        error_log("Router Error: " . $e->getMessage());
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>服务器错误 - AlingAi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800 mb-4">500</h1>
        <p class="text-xl text-gray-600 mb-8">服务器内部错误</p>
        <a href="/" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            返回首页
        </a>
    </div>
</body>
</html>
HTML;
        
        $response->getBody()->write($html);
        return $response->withStatus(500)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
    
    /**
     * 获取当前路由信息
     */
    public function getCurrentRoute(): ?array {
        return $this->currentRoute;
    }
}
