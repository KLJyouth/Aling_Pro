<?php
/**
 * 缓存测试和性能优化脚本
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Services\CacheService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('cache_test');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// 创建缓存服务
$cache = new CacheService($logger);

echo "=== AlingAi Pro 缓存性能测试 ===\n";

// 测试缓存操作
$startTime = microtime(true);

// 写入测试
$testData = [
    'system_info' => [
        'version' => '6.0.0',
        'timestamp' => time(),
        'features' => ['ai', 'blockchain', 'security']
    ],
    'user_sessions' => range(1, 100),
    'security_logs' => array_fill(0, 50, ['timestamp' => time(), 'event' => 'test'])
];

foreach ($testData as $key => $data) {
    $cache->set($key, $data, 3600);
    echo "✓ 缓存写入: $key\n";
}

// 读取测试
$readStartTime = microtime(true);
foreach (array_keys($testData) as $key) {
    $value = $cache->get($key);
    echo "✓ 缓存读取: $key - " . (is_array($value) ? count($value) . " items" : "simple value") . "\n";
}
$readTime = (microtime(true) - $readStartTime) * 1000;

$totalTime = (microtime(true) - $startTime) * 1000;

echo "\n=== 性能报告 ===\n";
echo "总测试时间: " . round($totalTime, 2) . "ms\n";
echo "读取时间: " . round($readTime, 2) . "ms\n";
echo "缓存驱动: " . (getenv('CACHE_DRIVER') ?: 'file') . "\n";

// 内存使用情况
echo "内存使用: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB\n";
echo "峰值内存: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";

// 清理测试
foreach (array_keys($testData) as $key) {
    $cache->delete($key);
}

echo "\n缓存测试完成！\n";
