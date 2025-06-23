<?php
echo "=== Manual Application Test ===\n";

// 手动加载所需的类
require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

echo "1. Loading Application class manually...\n";
$appFile = __DIR__ . '/src/Core/Application.php';
if (file_exists($appFile)) {
    require_once $appFile;
    echo "✓ Application.php loaded\n";
} else {
    echo "✗ Application.php not found\n";
    exit(1);
}

echo "2. Checking class existence...\n";
if (class_exists('AlingAi\Core\Application')) {
    echo "✓ Application class found\n";
} else {
    echo "✗ Application class not found\n";
    exit(1);
}

echo "3. Creating Application instance...\n";
try {
    $app = new AlingAi\Core\Application();
    echo "✓ Application instance created\n";
} catch (Throwable $e) {
    echo "✗ Failed to create Application instance: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "4. Testing methods...\n";
if (method_exists($app, 'handle')) {
    echo "✓ handle() method exists\n";
} else {
    echo "✗ handle() method missing\n";
}

if ($app instanceof Psr\Http\Server\RequestHandlerInterface) {
    echo "✓ Implements RequestHandlerInterface\n";
} else {
    echo "✗ Does not implement RequestHandlerInterface\n";
}

echo "5. Testing basic functionality...\n";
try {
    $slimApp = $app->getApp();
    echo "✓ Got Slim App instance\n";
    
    $container = $app->getContainer();
    echo "✓ Got container instance\n";
    
    $logger = $app->getLogger();
    echo "✓ Got logger instance\n";
    
} catch (Throwable $e) {
    echo "✗ Error testing functionality: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n";
