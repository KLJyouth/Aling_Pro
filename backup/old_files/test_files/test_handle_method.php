<?php
require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

try {
    $app = new AlingAi\Core\Application();
    echo "Application created successfully\n";
    
    // 检查方法是否存在
    if (method_exists($app, 'handle')) {
        echo "handle() method exists\n";
    } else {
        echo "handle() method NOT found\n";
    }
    
    // 检查接口实现
    if ($app instanceof Psr\Http\Server\RequestHandlerInterface) {
        echo "Application implements RequestHandlerInterface\n";
    } else {
        echo "Application does NOT implement RequestHandlerInterface\n";
    }
    
    // 列出所有方法
    $reflection = new ReflectionClass($app);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    echo "Public methods:\n";
    foreach ($methods as $method) {
        echo "  - " . $method->getName() . "\n";
    }
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
