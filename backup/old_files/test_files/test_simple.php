<?php

require_once __DIR__ . '/vendor/autoload.php';

try {
    echo "Testing AlingAi Application...\n";
    
    // 测试Application类加载
    if (class_exists('AlingAi\Core\Application')) {
        echo "✓ Application class exists\n";
    } else {
        echo "✗ Application class not found\n";
        exit(1);
    }
    
    // 测试创建Application实例
    $app = \AlingAi\Core\Application::create();
    echo "✓ Application instance created successfully\n";
    
    // 测试获取Slim App
    $slimApp = $app->getApp();
    if ($slimApp instanceof \Slim\App) {
        echo "✓ Slim App instance retrieved successfully\n";
    } else {
        echo "✗ Failed to get Slim App instance\n";
        exit(1);
    }
    
    echo "✓ All tests passed! Application is working.\n";
    
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
