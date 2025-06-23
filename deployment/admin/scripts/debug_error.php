<?php
/**
 * 调试错误脚本
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing Application Debug ===\n";

try {
    echo "1. Testing file includes...\n";
    
    // 测试 autoload
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        throw new Exception('Autoload file not found');
    }
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ Autoload included\n";
    
    // 测试环境文件
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        echo "✓ .env loaded\n";
    } else {
        echo "⚠ .env file not found, using defaults\n";
    }
    
    echo "2. Testing application creation...\n";
    
    // 测试应用程序创建
    $app = new AlingAi\Core\Application();
    echo "✓ Application created\n";
    
    echo "3. Testing container services...\n";
    
    // 测试容器
    $container = $app->getContainer();
    echo "✓ Container obtained\n";
    
    // 测试服务
    try {
        $logger = $container->get('logger');
        echo "✓ Logger service works\n";
    } catch (Exception $e) {
        echo "✗ Logger service failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $db = $container->get(AlingAi\Services\DatabaseServiceInterface::class);
        echo "✓ Database service works\n";
    } catch (Exception $e) {
        echo "✗ Database service failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $cache = $container->get(AlingAi\Services\CacheService::class);
        echo "✓ Cache service works\n";
    } catch (Exception $e) {
        echo "✗ Cache service failed: " . $e->getMessage() . "\n";
    }
      echo "4. Testing route loading...\n";
    
    // 测试路由 - 使用内部的Slim App实例
    $routes = require __DIR__ . '/config/routes.php';
    if (is_callable($routes)) {
        $slimApp = $app->getApp(); // 获取内部的Slim App实例
        $routes($slimApp);
        echo "✓ Routes loaded\n";
    } else {
        echo "✗ Routes failed to load\n";
    }
    
    echo "5. Testing HTTP request...\n";
    
    // 创建测试请求
    $serverParams = [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => 8000,
        'HTTP_HOST' => 'localhost:8000'
    ];
    
    $psr17Factory = new Nyholm\Psr7\Factory\Psr17Factory();
    $request = $psr17Factory->createServerRequest('GET', '/', $serverParams);
    
    echo "✓ PSR-7 request created\n";
    
    // 运行应用
    $response = $app->handle($request);
    echo "✓ Application handled request\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response body: " . $response->getBody() . "\n";
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}