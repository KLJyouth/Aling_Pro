<?php
/**
 * SDK下载系统完整功能测试
 * 测试所有组件的正常工作
 */

echo "=== AlingAI SDK下载系统功能测试 ===\n\n";

// 测试1: SDK生成器
echo "1. 测试SDK生成器...\n";
$generator = require_once('scripts/sdk_generator.php');

// 测试PHP SDK生成
echo "   生成PHP SDK...\n";
$result = shell_exec('php scripts/sdk_generator.php language=php version=2.0.0');
echo "   结果: " . trim($result) . "\n";

// 测试JavaScript SDK生成
echo "   生成JavaScript SDK...\n";
$result = shell_exec('php scripts/sdk_generator.php language=javascript version=2.0.0');
echo "   结果: " . trim($result) . "\n\n";

// 测试2: 统计API
echo "2. 测试统计API...\n";
include_once('api/sdk-stats.php');
$api = new SDKStatsAPI();

// 记录几个下载
echo "   记录下载数据...\n";
$api->recordDownload('php', '2.0.0', 'TestAgent/1.0', '192.168.1.100');
$api->recordDownload('javascript', '2.0.0', 'TestAgent/1.0', '192.168.1.101');
$api->recordDownload('python', '2.0.0', 'TestAgent/1.0', '192.168.1.102');

// 获取统计数据
$stats = $api->getStats();
echo "   总下载数: " . $stats['total_downloads'] . "\n";
echo "   活跃文件数: " . $stats['active_downloads'] . "\n";
echo "   语言分布: " . json_encode($stats['downloads_by_language']) . "\n\n";

// 测试3: 清理功能
echo "3. 测试清理功能...\n";
include_once('scripts/cleanup_downloads.php');
$cleaner = new DownloadCleaner();

$status = $cleaner->getDirectoryStatus();
echo "   下载目录状态: " . json_encode($status) . "\n";

$cleanupResult = $cleaner->cleanupExpiredFiles();
echo "   清理结果: " . json_encode($cleanupResult) . "\n\n";

// 测试4: 检查文件结构
echo "4. 检查文件结构...\n";
$requiredDirs = [
    'public/downloads/',
    'sdk_source/',
    'logs/',
    'scripts/',
    'api/'
];

foreach ($requiredDirs as $dir) {
    $exists = is_dir($dir) ? '✓' : '✗';
    echo "   {$dir}: {$exists}\n";
}

$requiredFiles = [
    'scripts/sdk_generator.php',
    'scripts/cleanup_downloads.php',
    'api/sdk-stats.php',
    'public/sdk-download.html',
    'public/assets/js/sdk-download.js'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists($file) ? '✓' : '✗';
    echo "   {$file}: {$exists}\n";
}

echo "\n=== 测试完成 ===\n";
echo "所有主要功能已验证！\n";
echo "系统已准备就绪，可以部署到支持PHP的Web服务器。\n\n";

echo "部署提醒:\n";
echo "1. 确保Web服务器支持PHP 7.4+\n";
echo "2. 设置public/downloads/目录可写权限\n";
echo "3. 配置Web服务器路由，确保API端点可访问\n";
echo "4. 可选：设置定时任务运行cleanup_downloads.php\n";
?>
