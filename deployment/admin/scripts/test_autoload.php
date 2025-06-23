<?php
/**
 * 测试 autoload 问题
 */

declare(strict_types=1);

echo "Testing autoload...\n";

// 加载 composer autoload
require_once __DIR__ . '/vendor/autoload.php';

echo "Autoload loaded\n";

// 测试各个类是否可用
$classes = [
    'Slim\\App',
    'Slim\\Factory\\AppFactory', 
    'Dotenv\\Dotenv',
    'Monolog\\Logger',
    'AlingAi\\Core\\Application'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✓ $class - EXISTS\n";
    } else {
        echo "✗ $class - NOT FOUND\n";
    }
}

// 尝试手动加载 Application
try {
    $app = \AlingAi\Core\Application::create();
    echo "✓ Application created successfully\n";
} catch (Throwable $e) {
    echo "✗ Application creation failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}