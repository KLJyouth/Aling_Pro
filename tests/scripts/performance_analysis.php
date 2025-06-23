<?php
/**
 * AlingAi Pro 性能分析脚本
 * 分析应用程序启动时间和资源使用
 */

declare(strict_types=1);

// 开始计时
$startTime = microtime(true);
$startMemory = memory_get_usage(true);

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Core\AlingAiProApplication;

echo "=== AlingAi Pro 性能分析 ===\n";

// 记录自动加载完成时间
$autoloadTime = microtime(true);
echo "自动加载时间: " . round(($autoloadTime - $startTime) * 1000, 2) . "ms\n";

// 分析应用程序初始化
$initStartTime = microtime(true);

try {
    // 创建应用程序实例
    $application = AlingAiProApplication::create();
    
    $initEndTime = microtime(true);
    echo "应用程序初始化时间: " . round(($initEndTime - $initStartTime) * 1000, 2) . "ms\n";
    
    // 分析内存使用
    $currentMemory = memory_get_usage(true);
    echo "初始化后内存使用: " . round($currentMemory / 1024 / 1024, 2) . " MB\n";
    echo "内存增长: " . round(($currentMemory - $startMemory) / 1024 / 1024, 2) . " MB\n";
    
    // 测试路由性能
    $routeStartTime = microtime(true);
    
    // 模拟一些路由调用
    for ($i = 0; $i < 10; $i++) {
        // 这里可以添加实际的路由性能测试
    }
    
    $routeEndTime = microtime(true);
    echo "路由处理时间: " . round(($routeEndTime - $routeStartTime) * 1000, 2) . "ms\n";
    
    // 总体性能
    $totalTime = microtime(true) - $startTime;
    $peakMemory = memory_get_peak_usage(true);
    
    echo "\n=== 性能摘要 ===\n";
    echo "总启动时间: " . round($totalTime * 1000, 2) . "ms\n";
    echo "峰值内存使用: " . round($peakMemory / 1024 / 1024, 2) . " MB\n";
    
    // 性能建议
    echo "\n=== 性能建议 ===\n";
    
    if ($totalTime > 0.5) {
        echo "⚠️  启动时间较长，建议优化自动加载和依赖注入\n";
    } else {
        echo "✅ 启动时间良好\n";
    }
    
    if ($peakMemory > 64 * 1024 * 1024) {
        echo "⚠️  内存使用较高，建议优化内存使用\n";
    } else {
        echo "✅ 内存使用正常\n";
    }
    
    // 检查 Composer 优化
    if (!file_exists(__DIR__ . '/vendor/composer/autoload_classmap.php') || 
        filesize(__DIR__ . '/vendor/composer/autoload_classmap.php') < 1000) {
        echo "💡 建议运行 'composer dump-autoload --optimize' 优化自动加载\n";
    }
    
    // 检查缓存
    if (!is_dir(__DIR__ . '/storage/framework/cache')) {
        echo "💡 缓存目录不存在，建议创建并配置缓存\n";
    }
    
} catch (Throwable $e) {
    echo "❌ 应用程序初始化失败: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n性能分析完成！\n";
