<?php
/**
 * 调试 Slim 应用程序路由解析
 */

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo "=== Slim Route Resolution Debug ===\n";
    
    // 1. 包含依赖
    require_once __DIR__ . '/vendor/autoload.php';
    
    // 2. 加载环境变量
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    // 3. 创建应用程序
    $app = \AlingAi\Core\Application::create();
    echo "✓ Application created\n";
    
    // 4. 获取 Slim 应用实例
    $slimApp = $app->getApp();
    echo "✓ Slim app obtained\n";
    
    // 5. 检查路由收集器
    $routeCollector = $slimApp->getRouteCollector();
    $routes = $routeCollector->getRoutes();
    echo "✓ Routes collected: " . count($routes) . " routes found\n";
    
    // 列出所有路由
    echo "\nRegistered routes:\n";
    foreach ($routes as $route) {
        $methods = implode('|', $route->getMethods());
        $pattern = $route->getPattern();
        echo "  $methods $pattern\n";
    }
    
    // 6. 创建请求进行路由解析测试
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    $request = $psr17Factory->createServerRequest('GET', 'http://localhost/', [
        'HTTP_HOST' => 'localhost',
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
        'SERVER_NAME' => 'localhost'
    ]);
    echo "\n✓ Test request created for '/'\n";    // 7. 直接测试 Slim 应用处理，跳过单独的中间件测试
    echo "\n=== Testing Slim app with full middleware stack ===\n";    
    // 8. 直接测试 Slim 应用处理
    try {
        $slimResponse = $slimApp->handle($request);
        echo "Slim app response status: " . $slimResponse->getStatusCode() . "\n";
        
        $slimResponse->getBody()->rewind();
        $slimBody = $slimResponse->getBody()->getContents();
        echo "Slim app response body length: " . strlen($slimBody) . "\n";
        
        if (strlen($slimBody) > 0) {
            echo "Slim app response preview: " . substr($slimBody, 0, 200) . "...\n";
        } else {
            echo "Slim app response body is empty\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Slim app handle error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}