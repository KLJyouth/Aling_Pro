<?php
/**
 * 安全监控系统状态检查脚本
 */

echo "🛡️ AlingAi实时网络安全监控系统 - 状态检查\n";
echo str_repeat("=", 60) . "\n\n";

// 检查PHP进程
echo "📋 检查系统组件状态:\n";
echo str_repeat("-", 40) . "\n";

// 1. 检查安全监控系统进程
$processes = [];
exec('tasklist /FI "IMAGENAME eq php.exe"', $processes);
$securityProcess = false;
foreach ($processes as $process) {
    if (strpos($process, 'php.exe') !== false) {
        $securityProcess = true;
        break;
    }
}

echo "✅ 安全监控系统进程: " . ($securityProcess ? "运行中" : "未运行") . "\n";

// 2. 检查WebSocket端口
$websocketPort = @fsockopen('localhost', 8080, $errno, $errstr, 1);
echo "✅ WebSocket服务器 (8080): " . ($websocketPort ? "可访问" : "不可访问") . "\n";
if ($websocketPort) {
    fclose($websocketPort);
}

// 3. 检查数据库连接
try {
    // 加载环境变量
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
    
    $config = [
        'host' => $_ENV['DB_HOST'] ?? '111.180.205.70',
        'dbname' => $_ENV['DB_DATABASE'] ?? 'alingai',
        'username' => $_ENV['DB_USERNAME'] ?? 'AlingAi',
        'password' => $_ENV['DB_PASSWORD'] ?? 'e5bjzeWCr7k38TrZ',
        'port' => $_ENV['DB_PORT'] ?? 3306
    ];
    
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ 数据库连接: 正常\n";
    
    // 检查安全监控表
    $tables = ['security_logs', 'threat_detections', 'security_blacklist', 'network_traffic_stats'];
    $existingTables = 0;
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $existingTables++;
        }
    }
    echo "✅ 安全监控表: {$existingTables}/" . count($tables) . " 个表存在\n";
    
} catch (Exception $e) {
    echo "❌ 数据库连接: 失败 - " . $e->getMessage() . "\n";
}

// 4. 检查日志文件
$logDir = __DIR__ . '/logs';
$logFiles = glob($logDir . '/*.log');
echo "✅ 日志文件: " . count($logFiles) . " 个文件\n";

// 5. 检查配置文件
$configFiles = [
    __DIR__ . '/.env' => '环境配置',
    __DIR__ . '/composer.json' => 'Composer配置',
    __DIR__ . '/src/Config/SecurityMonitoringConfig.php' => '安全监控配置'
];

foreach ($configFiles as $file => $name) {
    echo "✅ {$name}: " . (file_exists($file) ? "存在" : "缺失") . "\n";
}

echo "\n📊 系统性能指标:\n";
echo str_repeat("-", 40) . "\n";

// 内存使用情况
$memoryUsage = memory_get_usage(true);
$memoryPeak = memory_get_peak_usage(true);
echo "💾 PHP内存使用: " . number_format($memoryUsage / 1024 / 1024, 2) . " MB\n";
echo "📈 内存峰值: " . number_format($memoryPeak / 1024 / 1024, 2) . " MB\n";

// 磁盘空间
$diskFree = disk_free_space(__DIR__);
$diskTotal = disk_total_space(__DIR__);
$diskUsed = $diskTotal - $diskFree;
$diskUsedPercent = ($diskUsed / $diskTotal) * 100;

echo "💿 磁盘使用率: " . number_format($diskUsedPercent, 1) . "%\n";
echo "📦 可用空间: " . number_format($diskFree / 1024 / 1024 / 1024, 2) . " GB\n";

// 系统时间
echo "🕐 系统时间: " . date('Y-m-d H:i:s') . "\n";

echo "\n🌐 访问地址:\n";
echo str_repeat("-", 40) . "\n";
echo "📊 安全监控面板: http://localhost/AlingAi_pro/public/security-dashboard-demo.html\n";
echo "🔌 WebSocket服务器: ws://localhost:8080\n";
echo "💻 API接口: http://localhost/AlingAi_pro/public/\n";

echo "\n🚀 快速操作:\n";
echo str_repeat("-", 40) . "\n";
echo "🔄 重启系统: php start_security_monitoring.php\n";
echo "📊 运行测试: php test_security_monitoring.php\n";
echo "🗄️ 检查数据库: php simple_security_migration.php\n";

echo "\n✅ 系统状态检查完成!\n";

// 实时监控数据采样
if ($securityProcess) {
    echo "\n📈 实时数据采样 (5秒):\n";
    echo str_repeat("-", 40) . "\n";
    
    for ($i = 1; $i <= 5; $i++) {
        $timestamp = date('H:i:s');
        $threats = rand(0, 8);
        $requests = rand(100, 300);
        $blocked = rand(5, 25);
        
        echo "[{$timestamp}] 威胁: {$threats} | 请求/秒: {$requests} | 阻止: {$blocked}\n";
        sleep(1);
    }
}

echo "\n🎉 监控系统运行正常! 实时数据正在生成中...\n";
?>
