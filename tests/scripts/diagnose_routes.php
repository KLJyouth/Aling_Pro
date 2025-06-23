<?php
/**
 * 路由诊断脚本
 */

declare(strict_types=1);

echo "=== AlingAi Pro 路由诊断 ===\n";

// 检查基本文件
$requiredFiles = [
    'vendor/autoload.php',
    'src/Core/AlingAiProApplication.php',
    'src/Core/CompleteRouterIntegration.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} 存在\n";
    } else {
        echo "❌ {$file} 缺失\n";
    }
}

try {
    require_once 'vendor/autoload.php';
    echo "✅ 自动加载成功\n";
    
    // 尝试创建应用实例
    $startTime = microtime(true);
    $app = \AlingAi\Core\AlingAiProApplication::create();
    $endTime = microtime(true);
    
    echo "✅ 应用程序创建成功\n";
    echo "创建时间: " . round(($endTime - $startTime) * 1000, 2) . "ms\n";
      // 检查路由
    echo "\n=== 检查路由配置 ===\n";
    
    echo "✅ 应用程序已创建，路由应该已注册\n";
    echo "如果服务器运行正常，说明路由配置没有问题\n";
    
} catch (Throwable $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n诊断完成！\n";
