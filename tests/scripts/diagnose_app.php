<?php
/**
 * 应用程序诊断脚本
 * 检查应用初始化和路由注册状态
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Core\AlingAiProApplication;

echo "=== AlingAi Pro 应用诊断 ===\n\n";

try {
    // 检查环境变量
    echo "1. 环境变量检查:\n";
    echo "APP_ENV: " . (getenv('APP_ENV') ?: '未设置') . "\n";
    echo "CACHE_DRIVER: " . (getenv('CACHE_DRIVER') ?: '未设置') . "\n";
    echo "\n";
    
    // 检查数据库连接
    echo "2. 数据库配置检查:\n";
    if (file_exists('.env')) {
        $envContent = file_get_contents('.env');
        if (preg_match('/DB_HOST=(.+)/', $envContent, $matches)) {
            echo "DB_HOST: " . trim($matches[1]) . "\n";
        }
        if (preg_match('/DB_DATABASE=(.+)/', $envContent, $matches)) {
            echo "DB_DATABASE: " . trim($matches[1]) . "\n";
        }
    }
    echo "\n";
    
    // 检查核心类文件
    echo "3. 核心类文件检查:\n";
    $coreFiles = [
        'src/Core/AlingAiProApplication.php',
        'src/Core/CompleteAPIRouter.php',
        'src/Core/CompleteRouterIntegration.php'
    ];
    
    foreach ($coreFiles as $file) {
        echo $file . ": " . (file_exists($file) ? "✓" : "✗") . "\n";
    }
    echo "\n";
      // 尝试创建应用实例
    echo "4. 应用实例创建测试:\n";
    
    echo "正在创建应用实例...\n";
    $app = \AlingAi\Core\AlingAiProApplication::create();
    echo "✓ 应用实例创建成功\n";
    
    echo "✓ 诊断完成\n";
    
} catch (Throwable $e) {
    echo "✗ 诊断失败: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
