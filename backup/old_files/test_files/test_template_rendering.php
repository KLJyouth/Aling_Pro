<?php
/**
 * 测试模板渲染
 */

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo "=== Template Rendering Test ===\n";
    
    // 1. 检查模板文件
    $templatePath = __DIR__ . '/public/index.html';
    echo "Template path: $templatePath\n";
    
    if (file_exists($templatePath)) {
        echo "✓ Template file exists\n";
        $content = file_get_contents($templatePath);
        echo "Template size: " . strlen($content) . " characters\n";
        echo "Template preview: " . substr($content, 0, 200) . "...\n";
    } else {
        echo "✗ Template file does not exist\n";
        exit(1);
    }
    
    // 2. 包含自动加载
    require_once __DIR__ . '/vendor/autoload.php';
    
    // 3. 加载环境变量
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    // 4. 创建控制器来测试渲染
    echo "\n=== Testing Controller Rendering ===\n";
    
    // 创建必要的依赖项
    $container = new \DI\Container();
    
    // 设置日志服务
    $logger = new \Monolog\Logger('test');
    $logger->pushHandler(new \Monolog\Handler\NullHandler());
    $container->set('logger', $logger);    // 设置数据库服务
    $container->set(\AlingAi\Services\DatabaseServiceInterface::class, function () use ($logger) {
        return new \AlingAi\Services\FileStorageService($logger);
    });
    
    // 设置缓存服务
    $container->set(\AlingAi\Services\CacheService::class, function () use ($logger) {
        return new \AlingAi\Services\CacheService($logger);
    });
    
    // 创建控制器
    $webController = $container->get(\AlingAi\Controllers\WebController::class);
    echo "✓ WebController created\n";
    
    // 创建请求和响应对象
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    $request = $psr17Factory->createServerRequest('GET', 'http://localhost/');
    $response = $psr17Factory->createResponse();
    
    echo "\n=== Testing Index Method ===\n";
    try {
        $result = $webController->index($request, $response);
        echo "✓ Index method executed successfully\n";
        echo "Response status: " . $result->getStatusCode() . "\n";
        $body = $result->getBody()->getContents();
        echo "Response body length: " . strlen($body) . " characters\n";
        
        if (strlen($body) > 0) {
            echo "Response preview: " . substr($body, 0, 300) . "...\n";
        } else {
            echo "Response body is empty - investigating...\n";
            
            // 检查模板路径在控制器中是否正确
            echo "\nDebugging template path calculation:\n";
            $baseControllerPath = __DIR__ . '/src/Controllers';
            echo "Base controller path: $baseControllerPath\n";
            $expectedTemplatePath = $baseControllerPath . '/../../public/index.html';
            echo "Expected template path from controller: $expectedTemplatePath\n";
            $realTemplatePath = realpath($expectedTemplatePath);
            echo "Real template path: $realTemplatePath\n";
            
            if ($realTemplatePath && file_exists($realTemplatePath)) {
                echo "✓ Template is accessible from controller path\n";
            } else {
                echo "✗ Template is NOT accessible from controller path\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "✗ Index method failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
