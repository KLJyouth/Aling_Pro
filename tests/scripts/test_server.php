<?php
// 简单测试脚本
echo "Server is working!\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// 测试基本功能
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "Autoload: OK\n";
} catch (Exception $e) {
    echo "Autoload Error: " . $e->getMessage() . "\n";
}

// 测试应用程序初始化
try {
    $appClass = 'AlingAi\\Core\\AlingAiProApplication';
    echo "Application class: OK\n";
    
    $app = $appClass::create();
    echo "Application created: OK\n";
    
} catch (Exception $e) {
    echo "Application Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
