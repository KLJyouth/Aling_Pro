<?php
/**
 * 简单的应用程序测试
 */

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo "=== Simple Application Test ===\n";
    
    // 1. 包含自动加载
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ Autoload included\n";
    
    // 2. 加载环境变量
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✓ Environment loaded\n";
    
    // 3. 创建应用程序
    $app = \AlingAi\Core\Application::create();
    echo "✓ Application created\n";
    
    // 4. 创建请求
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    $request = $psr17Factory->createServerRequest('GET', 'http://localhost/', [
        'HTTP_HOST' => 'localhost',
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
        'SERVER_NAME' => 'localhost'
    ]);
    echo "✓ Request created\n";
    
    // 5. 处理请求
    echo "5. Processing request...\n";
    $response = $app->handle($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    $body = $response->getBody()->getContents();
    echo "Response body length: " . strlen($body) . " characters\n";
    
    // 显示响应的前500个字符
    if (strlen($body) > 0) {
        echo "Response preview:\n";
        echo substr($body, 0, 500) . "\n";
        if (strlen($body) > 500) {
            echo "...[truncated]\n";
        }
    } else {
        echo "Response body is empty\n";
    }
    
    // 6. 直接启动内置服务器进行测试
    echo "\n=== Starting Built-in Server for Manual Testing ===\n";
    echo "Starting PHP built-in server on http://localhost:8000\n";
    echo "Visit the URL to test manually\n";
    echo "Press Ctrl+C to stop the server\n";
    
    // 切换到 public 目录并启动服务器
    chdir(__DIR__ . '/public');
    passthru('php -S localhost:8000 index.php');
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
