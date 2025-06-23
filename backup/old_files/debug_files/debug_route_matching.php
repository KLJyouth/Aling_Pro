<?php
/**
 * 调试路由匹配问题
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Controllers\Api\AuthApiController;

// 创建一个简化的路由测试
class RouteDebugger {
    private $routes = [];
    
    public function __construct() {
        // 手动添加登录路由
        $this->addRoute('POST', '/api/auth/login', 'auth', 'login');
        $this->addRoute('GET', '/api/auth/test', 'auth', 'test');
    }
    
    private function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
        echo "添加路由: $method $path -> $controller::$action\n";
    }
    
    public function findRoute($method, $path) {
        echo "\n查找路由: $method $path\n";
        echo "可用路由:\n";
        
        foreach ($this->routes as $index => $route) {
            echo "  [$index] {$route['method']} {$route['path']}\n";
            
            if ($route['method'] !== $method) {
                echo "    ❌ 方法不匹配 ({$route['method']} != $method)\n";
                continue;
            }
            
            // 处理路径参数
            $routePath = $route['path'];
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';
            
            echo "    路径模式: $pattern\n";
            echo "    请求路径: $path\n";
            
            if (preg_match($pattern, $path, $matches)) {
                echo "    ✅ 路径匹配!\n";
                return $route;
            } else {
                echo "    ❌ 路径不匹配\n";
            }
        }
        
        return null;
    }
    
    public function testController() {
        echo "\n测试控制器实例化:\n";
        try {
            $controller = new AuthApiController();
            echo "✅ AuthApiController 实例化成功\n";
            
            if (method_exists($controller, 'login')) {
                echo "✅ login 方法存在\n";
            } else {
                echo "❌ login 方法不存在\n";
            }
            
        } catch (Exception $e) {
            echo "❌ 控制器实例化失败: " . $e->getMessage() . "\n";
        }
    }
}

echo "路由调试器\n";
echo "==========\n\n";

$debugger = new RouteDebugger();

// 测试路由匹配
echo "\n测试路由匹配:\n";
echo "--------------\n";

$testCases = [
    ['GET', '/api/auth/test'],
    ['POST', '/api/auth/login'],
    ['POST', '/api/auth/register']
];

foreach ($testCases as $case) {
    $result = $debugger->findRoute($case[0], $case[1]);
    if ($result) {
        echo "✅ 找到路由: {$result['controller']}::{$result['action']}\n";
    } else {
        echo "❌ 未找到路由\n";
    }
    echo "\n";
}

// 测试控制器
$debugger->testController();
