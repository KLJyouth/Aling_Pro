<?php
/**
 * 深度性能分析和优化建议
 */

declare(strict_types=1);

// 记录详细的时间戳
function profileTime($label) {
    static $times = [];
    $times[$label] = microtime(true);
    if (count($times) > 1) {
        $keys = array_keys($times);
        $lastKey = $keys[count($keys) - 2];
        $duration = ($times[$label] - $times[$lastKey]) * 1000;
        echo sprintf("⏱️  %s -> %s: %.2fms\n", $lastKey, $label, $duration);
    }
    return $times[$label];
}

profileTime('START');

// 检查OPcache
echo "=== PHP OPcache 状态 ===\n";
if (function_exists('opcache_get_status')) {
    $opcache = opcache_get_status();
    if ($opcache['opcache_enabled']) {
        echo "✅ OPcache 已启用\n";
        echo "命中率: " . round($opcache['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
        echo "缓存文件数: " . $opcache['opcache_statistics']['num_cached_scripts'] . "\n";
    } else {
        echo "❌ OPcache 未启用 - 这是主要性能瓶颈!\n";
    }
} else {
    echo "❌ OPcache 不可用\n";
}

profileTime('OPcache_check');

require_once __DIR__ . '/vendor/autoload.php';
profileTime('autoload');

use AlingAi\Core\AlingAiProApplication;

// 分析自动加载的类
$loadedClasses = get_declared_classes();
$alingAiClasses = array_filter($loadedClasses, function($class) {
    return strpos($class, 'AlingAi') === 0 || strpos($class, 'AlingAI') === 0;
});

echo "\n=== 自动加载分析 ===\n";
echo "总加载类数: " . count($loadedClasses) . "\n";
echo "AlingAi 相关类: " . count($alingAiClasses) . "\n";

profileTime('class_analysis');

// 测试最小化的应用初始化
echo "\n=== 尝试优化的初始化 ===\n";

try {
    // 禁用一些非必要的服务进行测试
    $startOptimized = microtime(true);
    
    // 创建应用（这里会触发所有的依赖注入）
    $application = AlingAiProApplication::create();
    
    $endOptimized = microtime(true);
    $optimizedTime = ($endOptimized - $startOptimized) * 1000;
    
    echo "优化后初始化时间: " . round($optimizedTime, 2) . "ms\n";
    
    profileTime('optimized_init');
    
} catch (Throwable $e) {
    echo "❌ 优化测试失败: " . $e->getMessage() . "\n";
}

echo "\n=== 具体优化建议 ===\n";

// 检查环境配置
echo "1. OPcache 优化:\n";
if (!function_exists('opcache_get_status') || !opcache_get_status()['opcache_enabled']) {
    echo "   - 在 php.ini 中启用 opcache.enable=1\n";
    echo "   - 设置 opcache.memory_consumption=128\n";
    echo "   - 设置 opcache.max_accelerated_files=4000\n";
    echo "   - 设置 opcache.validate_timestamps=0 (生产环境)\n";
}

echo "\n2. 自动加载优化:\n";
echo "   - 已执行 composer dump-autoload --optimize\n";
echo "   - 考虑使用 composer dump-autoload --classmap-authoritative (生产环境)\n";

echo "\n3. 依赖注入优化:\n";
echo "   - 延迟初始化不必要的服务\n";
echo "   - 使用单例模式减少重复实例化\n";
echo "   - 考虑使用缓存存储已编译的依赖容器\n";

echo "\n4. 数据库连接优化:\n";
echo "   - 使用连接池\n";
echo "   - 延迟数据库连接到真正需要时\n";
echo "   - 使用持久连接\n";

echo "\n5. 中间件优化:\n";
echo "   - 减少不必要的中间件\n";
echo "   - 优化中间件执行顺序\n";
echo "   - 使用条件中间件\n";

// 检查文件系统性能
$tempFile = tempnam(sys_get_temp_dir(), 'perf_test');
$startIO = microtime(true);
for ($i = 0; $i < 100; $i++) {
    file_put_contents($tempFile . $i, str_repeat('test', 100));
    unlink($tempFile . $i);
}
$endIO = microtime(true);
$ioTime = ($endIO - $startIO) * 1000;
unlink($tempFile);

echo "\n6. 文件系统性能:\n";
echo "   100 次文件操作耗时: " . round($ioTime, 2) . "ms\n";
if ($ioTime > 100) {
    echo "   ⚠️  文件系统较慢，考虑使用 SSD 或优化文件缓存\n";
}

profileTime('END');

echo "\n=== 推荐的 php.ini 优化设置 ===\n";
echo "; OPcache 设置\n";
echo "opcache.enable=1\n";
echo "opcache.memory_consumption=128\n";
echo "opcache.interned_strings_buffer=8\n";
echo "opcache.max_accelerated_files=4000\n";
echo "opcache.revalidate_freq=2\n";
echo "opcache.fast_shutdown=1\n";
echo "opcache.enable_cli=1\n";
echo "\n; 内存和执行时间\n";
echo "memory_limit=256M\n";
echo "max_execution_time=30\n";
echo "max_input_time=60\n";
echo "\n; 文件上传\n";
echo "upload_max_filesize=32M\n";
echo "post_max_size=32M\n";

echo "\n性能分析完成！\n";
