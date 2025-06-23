<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Core/Application.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

try {
    $app = new AlingAi\Core\Application();
    echo "Application created successfully\n";
    
    // 检查方法是否存在
    if (method_exists($app, 'handle')) {
        echo "✓ handle() method exists\n";
    } else {
        echo "✗ handle() method NOT found\n";
    }
    
    // 检查接口实现
    if ($app instanceof Psr\Http\Server\RequestHandlerInterface) {
        echo "✓ Application implements RequestHandlerInterface\n";
    } else {
        echo "✗ Application does NOT implement RequestHandlerInterface\n";
    }
    
    // 测试PSR-7请求处理
    $psr17Factory = new Nyholm\Psr7\Factory\Psr17Factory();
    $request = $psr17Factory->createServerRequest('GET', '/');
    
    echo "Testing PSR-7 request handling...\n";
    $response = $app->handle($request);
    echo "✓ Application handled PSR-7 request successfully\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    
    $responseBody = (string) $response->getBody();
    echo "Response length: " . strlen($responseBody) . " bytes\n";
    
    if (strlen($responseBody) > 100) {
        echo "Response preview: " . substr($responseBody, 0, 100) . "...\n";
    } else {
        echo "Response body: " . $responseBody . "\n";
    }
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
