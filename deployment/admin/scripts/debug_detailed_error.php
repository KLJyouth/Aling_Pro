<?php
/**
 * 详细错误调试脚本
 */

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// 设置错误处理器
set_error_handler(function($severity, $message, $file, $line) {
    echo "PHP Error: $message in $file:$line\n";
});

set_exception_handler(function($exception) {
    echo "Uncaught Exception: " . $exception->getMessage() . "\n";
    echo "File: " . $exception->getFile() . ":" . $exception->getLine() . "\n";
    echo "Stack trace:\n" . $exception->getTraceAsString() . "\n";
});

try {
    echo "=== Detailed Error Debug ===\n";
    
    // 1. 包含自动加载
    echo "1. Including autoload...\n";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ Autoload included\n";
    
    // 2. 加载环境变量
    echo "2. Loading environment...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✓ Environment loaded\n";
    
    // 3. 创建应用程序
    echo "3. Creating application...\n";
    $app = \AlingAi\Core\Application::create();
    echo "✓ Application created\n";
    
    // 4. 测试容器服务
    echo "4. Testing container services...\n";
    $container = $app->getContainer();
    echo "✓ Container obtained\n";
    
    // 测试各个服务
    $logger = $container->get('logger');
    echo "✓ Logger service works\n";
    
    $database = $container->get(\AlingAi\Services\DatabaseServiceInterface::class);
    echo "✓ Database service works\n";
    
    $cache = $container->get(\AlingAi\Services\CacheService::class);
    echo "✓ Cache service works\n";
    
    // 5. 测试控制器创建
    echo "5. Testing controller creation...\n";
    $webController = $container->get(\AlingAi\Controllers\WebController::class);
    echo "✓ WebController created\n";    // 6. 测试路由
    echo "6. Testing route loading...\n";
    $routeConfig = require __DIR__ . '/config/routes.php';
    $slimApp = $app->getApp(); // 获取 Slim App 实例
    $routeConfig($slimApp);
    echo "✓ Routes loaded\n";
    
    // 7. 创建详细的请求对象
    echo "7. Creating detailed request...\n";
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    $request = $psr17Factory->createServerRequest('GET', 'http://localhost/', [
        'HTTP_HOST' => 'localhost',
        'HTTP_USER_AGENT' => 'Test/1.0',
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => '80'
    ]);
    echo "✓ PSR-7 request created\n";
      // 8. 直接测试控制器方法
    echo "8. Testing controller method directly...\n";
    $response = $psr17Factory->createResponse();
    
    try {
        $controllerResponse = $webController->index($request, $response);
        echo "✓ Controller method executed\n";
        echo "Response status: " . $controllerResponse->getStatusCode() . "\n";
        
        // 重要：在测试中需要 rewind 流
        $controllerResponse->getBody()->rewind();
        $body = $controllerResponse->getBody()->getContents();
        echo "Response body length: " . strlen($body) . " characters\n";
        
        // 显示前200个字符
        if (strlen($body) > 0) {
            echo "Response body preview: " . substr($body, 0, 200) . "...\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Controller method failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // 9. 测试应用程序处理
    echo "9. Testing application handling...\n";
    try {
        $response2 = $psr17Factory->createResponse();
        $appResponse = $app->handle($request);
        echo "✓ Application handled request\n";
        echo "App Response status: " . $appResponse->getStatusCode() . "\n";
        $appBody = $appResponse->getBody()->getContents();
        echo "App Response body: " . $appBody . "\n";
        
    } catch (\Exception $e) {
        echo "✗ Application handling failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}