<?php
/**
 * 直接测试路由注册和匹配
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Controllers\Api\AuthApiController;
use AlingAi\Controllers\Api\ChatApiController;
use AlingAi\Controllers\Api\UserApiController;
use AlingAi\Controllers\Api\AdminApiController;
use AlingAi\Controllers\Api\SystemApiController;
use AlingAi\Controllers\Api\FileApiController;
use AlingAi\Controllers\Api\MonitorApiController;
use AlingAi\Controllers\Api\HistoryApiController;

// 创建一个简化版本的API路由器
class TestApiRouter {
    private $routes = [];
    private $controllers = [];
    
    public function __construct() {
        echo "初始化API路由器...\n";
        $this->initializeControllers();
        $this->registerRoutes();
        echo "路由注册完成，总计: " . count($this->routes) . " 个路由\n\n";
    }
    
    private function initializeControllers() {
        echo "初始化控制器...\n";
        try {
            $this->controllers = [
                'auth' => new AuthApiController(),
                'chat' => new ChatApiController(),
                'user' => new UserApiController(),
                'admin' => new AdminApiController(),
                'system' => new SystemApiController(),
                'file' => new FileApiController(),
                'monitor' => new MonitorApiController(),
                'history' => new HistoryApiController()
            ];
            echo "✅ 所有控制器初始化成功\n";
        } catch (Exception $e) {
            echo "❌ 控制器初始化失败: " . $e->getMessage() . "\n";
        }
    }
    
    private function registerRoutes() {
        echo "注册认证路由...\n";
        
        // 认证路由
        $this->addRoute('POST', '/api/auth/login', 'auth', 'login');
        $this->addRoute('POST', '/api/auth/register', 'auth', 'register');
        $this->addRoute('GET', '/api/auth/test', 'auth', 'test');
        
        echo "认证路由注册完成\n";
    }
    
    private function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
        echo "  + $method $path -> $controller::$action\n";
    }
    
    public function listRoutes() {
        echo "已注册的路由:\n";
        echo "============\n";
        foreach ($this->routes as $index => $route) {
            echo "[$index] {$route['method']} {$route['path']} -> {$route['controller']}::{$route['action']}\n";
        }
        echo "\n";
    }
    
    public function testRoute($method, $path) {
        echo "测试路由: $method $path\n";
        echo "------------------------\n";
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $routePath = $route['path'];
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';
            
            echo "  检查路由: {$route['path']}\n";
            echo "  模式: $pattern\n";
            
            if (preg_match($pattern, $path)) {
                echo "  ✅ 匹配成功!\n";
                echo "  控制器: {$route['controller']}\n";
                echo "  方法: {$route['action']}\n";
                return $route;
            } else {
                echo "  ❌ 不匹配\n";
            }
        }
        
        echo "  ❌ 未找到匹配的路由\n";
        return null;
    }
}

echo "API路由器测试\n";
echo "=============\n\n";

$router = new TestApiRouter();
$router->listRoutes();

// 测试几个路由
$testCases = [
    ['GET', '/api/auth/test'],
    ['POST', '/api/auth/login'],
    ['POST', '/api/auth/register']
];

foreach ($testCases as $case) {
    $router->testRoute($case[0], $case[1]);
    echo "\n";
}
