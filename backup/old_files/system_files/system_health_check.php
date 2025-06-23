<?php
/**
 * AlingAI Pro 5.0 完整系统健康检查
 * 版本: 5.0.0-Final
 * 日期: 2024-12-19
 */

declare(strict_types=1);

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 开始时间
$startTime = microtime(true);

echo "🔍 AlingAI Pro 5.0 系统健康检查\n";
echo str_repeat("=", 50) . "\n";
echo "开始时间: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 50) . "\n\n";

$healthReport = [
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '5.0.0-Final',
    'checks' => [],
    'overall_status' => 'healthy',
    'critical_issues' => 0,
    'warnings' => 0
];

/**
 * 记录检查结果
 */
function recordCheck(string $name, bool $passed, string $message, string $level = 'info'): void {
    global $healthReport;
    
    $status = $passed ? '✅' : '❌';
    $color = $passed ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";
    
    if ($level === 'warning' && !$passed) {
        $color = "\033[33m";
        $status = '⚠️';
        $healthReport['warnings']++;
    } elseif (!$passed) {
        $healthReport['critical_issues']++;
        $healthReport['overall_status'] = 'critical';
    }
    
    echo sprintf("%s[%s] %s: %s%s\n", $color, $status, $name, $message, $reset);
    
    $healthReport['checks'][] = [
        'name' => $name,
        'passed' => $passed,
        'message' => $message,
        'level' => $level,
        'timestamp' => date('H:i:s')
    ];
}

/**
 * 检查PHP环境
 */
function checkPHPEnvironment(): void {
    echo "📋 检查PHP环境...\n";
    
    // PHP版本
    $phpVersion = PHP_VERSION;
    $versionOk = version_compare($phpVersion, '8.1.0', '>=');
    recordCheck(
        'PHP版本', 
        $versionOk, 
        "当前版本: $phpVersion " . ($versionOk ? '(符合要求)' : '(需要8.1+)')
    );
    
    // 必需扩展
    $requiredExtensions = [
        'pdo', 'pdo_sqlite', 'curl', 'json', 'mbstring', 
        'openssl', 'filter', 'hash', 'fileinfo', 'zip'
    ];
    
    foreach ($requiredExtensions as $ext) {
        $loaded = extension_loaded($ext);
        recordCheck(
            "PHP扩展: $ext", 
            $loaded, 
            $loaded ? '已加载' : '未加载',
            $loaded ? 'info' : 'error'
        );
    }
    
    // 可选扩展
    $optionalExtensions = ['redis', 'memcached', 'imagick', 'gd'];
    foreach ($optionalExtensions as $ext) {
        $loaded = extension_loaded($ext);
        recordCheck(
            "可选扩展: $ext", 
            $loaded, 
            $loaded ? '已加载' : '未加载',
            'warning'
        );
    }
    
    // 内存限制
    $memoryLimit = ini_get('memory_limit');
    $memoryOk = $memoryLimit === '-1' || 
                (int)$memoryLimit >= 256 || 
                substr($memoryLimit, -1) === 'G';
    recordCheck(
        'PHP内存限制', 
        $memoryOk, 
        "当前: $memoryLimit " . ($memoryOk ? '(充足)' : '(建议256MB+)')
    );
    
    // 执行时间限制
    $timeLimit = ini_get('max_execution_time');
    recordCheck(
        '最大执行时间', 
        true, 
        $timeLimit == 0 ? '无限制' : "{$timeLimit}秒"
    );
    
    echo "\n";
}

/**
 * 检查文件系统
 */
function checkFileSystem(): void {
    echo "📁 检查文件系统...\n";
    
    // 核心目录
    $requiredDirs = [
        'logs', 'storage', 'public', 'config', 'src', 'resources', 'vendor'
    ];
    
    foreach ($requiredDirs as $dir) {
        $exists = is_dir($dir);
        $writable = $exists && is_writable($dir);
        
        recordCheck(
            "目录: $dir", 
            $exists, 
            $exists ? ($writable ? '存在且可写' : '存在但不可写') : '不存在',
            $exists ? ($writable ? 'info' : 'warning') : 'error'
        );
    }
    
    // 关键文件
    $requiredFiles = [
        '.env' => '环境配置文件',
        'composer.json' => 'Composer配置',
        'config/routes.php' => '路由配置',
        'src/Controllers/Frontend/RealTimeSecurityController.php' => '安全控制器',
        'src/Security/WebSocketSecurityServer.php' => 'WebSocket服务器'
    ];
    
    foreach ($requiredFiles as $file => $desc) {
        $exists = file_exists($file);
        recordCheck(
            "文件: $desc", 
            $exists, 
            $exists ? "存在 ($file)" : "缺失 ($file)",
            $exists ? 'info' : 'error'
        );
    }
    
    // 磁盘空间
    $freeSpace = disk_free_space('.');
    $totalSpace = disk_total_space('.');
    $freeSpaceMB = round($freeSpace / 1024 / 1024, 2);
    $usage = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
    
    recordCheck(
        '磁盘空间', 
        $freeSpaceMB > 100, 
        "可用: {$freeSpaceMB}MB, 使用率: {$usage}%",
        $freeSpaceMB > 100 ? 'info' : 'warning'
    );
    
    echo "\n";
}

/**
 * 检查数据库连接
 */
function checkDatabase(): void {
    echo "🗄️ 检查数据库...\n";
    
    try {
        // 检查SQLite数据库文件
        $dbFile = 'storage/database.sqlite';
        $dbExists = file_exists($dbFile);
        recordCheck(
            'SQLite数据库文件', 
            $dbExists, 
            $dbExists ? "存在 ($dbFile)" : "不存在 ($dbFile)"
        );
        
        if ($dbExists) {
            // 尝试连接数据库
            $pdo = new PDO("sqlite:$dbFile");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            recordCheck('数据库连接', true, 'SQLite连接成功');
            
            // 检查表是否存在
            $tables = [
                'threat_detections' => '威胁检测表',
                'security_events' => '安全事件表',
                'system_settings' => '系统设置表',
                'configuration_settings' => '配置设置表',
                'intelligent_agents' => '智能代理表'
            ];
            
            foreach ($tables as $table => $desc) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                    $count = $stmt->fetchColumn();
                    recordCheck(
                        "数据表: $desc", 
                        true, 
                        "存在，记录数: $count"
                    );
                } catch (PDOException $e) {
                    recordCheck(
                        "数据表: $desc", 
                        false, 
                        "不存在或无法访问",
                        'warning'
                    );
                }
            }
            
        } else {
            recordCheck('数据库连接', false, '数据库文件不存在');
        }
        
    } catch (Exception $e) {
        recordCheck('数据库连接', false, '连接失败: ' . $e->getMessage());
    }
    
    echo "\n";
}

/**
 * 检查Composer依赖
 */
function checkComposerDependencies(): void {
    echo "📦 检查Composer依赖...\n";
    
    // 检查vendor目录
    $vendorExists = is_dir('vendor');
    recordCheck(
        'Vendor目录', 
        $vendorExists, 
        $vendorExists ? '存在' : '不存在，需要运行 composer install'
    );
    
    if ($vendorExists) {
        // 检查autoload文件
        $autoloadExists = file_exists('vendor/autoload.php');
        recordCheck(
            'Autoload文件', 
            $autoloadExists, 
            $autoloadExists ? '存在' : '不存在'
        );
        
        if ($autoloadExists) {
            require_once 'vendor/autoload.php';
            
            // 检查关键依赖包
            $dependencies = [
                'Slim\\App' => 'Slim Framework',
                'Monolog\\Logger' => 'Monolog日志库',
                'GuzzleHttp\\Client' => 'Guzzle HTTP客户端',
                'Psr\\Http\\Message\\ServerRequestInterface' => 'PSR-7 HTTP消息接口'
            ];
            
            foreach ($dependencies as $class => $name) {
                $exists = class_exists($class) || interface_exists($class);
                recordCheck(
                    "依赖: $name", 
                    $exists, 
                    $exists ? '已加载' : '未找到',
                    $exists ? 'info' : 'error'
                );
            }
        }
    }
    
    echo "\n";
}

/**
 * 检查网络和端口
 */
function checkNetworkAndPorts(): void {
    echo "🌐 检查网络和端口...\n";
    
    // 检查关键端口
    $ports = [
        8000 => 'Web服务器',
        8080 => 'WebSocket服务器'
    ];
    
    foreach ($ports as $port => $service) {
        $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
        $available = $connection !== false;
        
        if ($connection) {
            fclose($connection);
        }
        
        recordCheck(
            "端口 $port ($service)", 
            $available, 
            $available ? '可用/被占用' : '不可用',
            'info' // 端口被占用实际上是好的，说明服务在运行
        );
    }
    
    // 检查网络连接
    $internetConnection = @file_get_contents('http://www.baidu.com', false, stream_context_create([
        'http' => ['timeout' => 5]
    ]));
    
    recordCheck(
        '互联网连接', 
        $internetConnection !== false, 
        $internetConnection !== false ? '正常' : '无法连接到互联网',
        'warning'
    );
    
    echo "\n";
}

/**
 * 检查系统进程
 */
function checkSystemProcesses(): void {
    echo "⚙️ 检查系统进程...\n";
    
    // 检查是否有PHP进程在运行
    $phpProcesses = [];
    
    if (PHP_OS_FAMILY === 'Windows') {
        $output = shell_exec('tasklist /fi "imagename eq php.exe" /fo csv 2>nul');
        if ($output && strpos($output, 'php.exe') !== false) {
            $lines = explode("\n", trim($output));
            $phpProcesses = array_slice($lines, 1); // 跳过标题行
        }
    } else {
        $output = shell_exec('ps aux | grep php | grep -v grep');
        if ($output) {
            $phpProcesses = explode("\n", trim($output));
        }
    }
    
    $phpRunning = !empty($phpProcesses) && $phpProcesses[0] !== '';
    recordCheck(
        'PHP进程', 
        $phpRunning, 
        $phpRunning ? '运行中 (' . count($phpProcesses) . '个进程)' : '未运行'
    );
    
    // 检查重要日志文件
    $logFiles = [
        'logs/system/webserver.log' => 'Web服务器日志',
        'logs/websocket/websocket.log' => 'WebSocket日志',
        'logs/security/monitoring.log' => '安全监控日志'
    ];
    
    foreach ($logFiles as $file => $desc) {
        $exists = file_exists($file);
        $size = $exists ? filesize($file) : 0;
        $sizeKB = round($size / 1024, 2);
        
        recordCheck(
            "日志: $desc", 
            $exists, 
            $exists ? "存在 ({$sizeKB}KB)" : '不存在',
            $exists ? 'info' : 'warning'
        );
    }
    
    echo "\n";
}

/**
 * 检查安全配置
 */
function checkSecurityConfiguration(): void {
    echo "🔒 检查安全配置...\n";
    
    // 检查.env文件中的安全配置
    if (file_exists('.env')) {
        $envContent = file_get_contents('.env');
        
        // 检查JWT密钥
        $hasJwtSecret = strpos($envContent, 'JWT_SECRET=') !== false && 
                       strpos($envContent, 'your_jwt_secret_here') === false;
        recordCheck(
            'JWT密钥配置', 
            $hasJwtSecret, 
            $hasJwtSecret ? '已配置' : '使用默认值（不安全）'
        );
        
        // 检查加密密钥
        $hasEncKey = strpos($envContent, 'ENCRYPTION_KEY=') !== false && 
                    strpos($envContent, 'your_encryption_key_here') === false;
        recordCheck(
            '加密密钥配置', 
            $hasEncKey, 
            $hasEncKey ? '已配置' : '使用默认值（不安全）'
        );
        
        // 检查调试模式
        $debugOff = strpos($envContent, 'APP_DEBUG=false') !== false;
        recordCheck(
            '调试模式', 
            $debugOff, 
            $debugOff ? '已关闭（安全）' : '开启中（开发模式）',
            $debugOff ? 'info' : 'warning'
        );
        
        // 检查监控配置
        $monitoringEnabled = strpos($envContent, 'MONITORING_ENABLED=true') !== false;
        recordCheck(
            '安全监控', 
            $monitoringEnabled, 
            $monitoringEnabled ? '已启用' : '未启用',
            $monitoringEnabled ? 'info' : 'warning'
        );
        
    } else {
        recordCheck('环境配置', false, '.env文件不存在');
    }
    
    echo "\n";
}

/**
 * 性能基准测试
 */
function performanceBenchmark(): void {
    echo "🚀 性能基准测试...\n";
    
    // CPU基准测试
    $cpuStart = microtime(true);
    $sum = 0;
    for ($i = 0; $i < 100000; $i++) {
        $sum += sqrt($i);
    }
    $cpuTime = microtime(true) - $cpuStart;
    
    recordCheck(
        'CPU性能', 
        $cpuTime < 1.0, 
        sprintf("计算耗时: %.3f秒", $cpuTime),
        $cpuTime < 1.0 ? 'info' : 'warning'
    );
    
    // 内存使用情况
    $memoryUsage = memory_get_usage(true);
    $memoryPeak = memory_get_peak_usage(true);
    $memoryMB = round($memoryUsage / 1024 / 1024, 2);
    $peakMB = round($memoryPeak / 1024 / 1024, 2);
    
    recordCheck(
        '内存使用', 
        $memoryMB < 64, 
        "当前: {$memoryMB}MB, 峰值: {$peakMB}MB",
        $memoryMB < 64 ? 'info' : 'warning'
    );
    
    // 磁盘I/O测试
    $ioStart = microtime(true);
    $testFile = 'tmp/io_test.tmp';
    $testData = str_repeat('A', 1024 * 100); // 100KB
    
    file_put_contents($testFile, $testData);
    $readData = file_get_contents($testFile);
    unlink($testFile);
    
    $ioTime = microtime(true) - $ioStart;
    $ioOk = $ioTime < 0.1 && $readData === $testData;
    
    recordCheck(
        '磁盘I/O性能', 
        $ioOk, 
        sprintf("100KB读写耗时: %.3f秒", $ioTime),
        $ioOk ? 'info' : 'warning'
    );
    
    echo "\n";
}

/**
 * 生成健康报告
 */
function generateHealthReport(): void {
    global $healthReport, $startTime;
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 3);
    
    echo "📊 健康检查总结\n";
    echo str_repeat("=", 50) . "\n";
    echo "检查耗时: {$duration}秒\n";
    echo "总体状态: " . ($healthReport['overall_status'] === 'healthy' ? '✅ 健康' : '❌ 异常') . "\n";
    echo "严重问题: {$healthReport['critical_issues']}个\n";
    echo "警告问题: {$healthReport['warnings']}个\n";
    echo "检查项目: " . count($healthReport['checks']) . "个\n";
    echo str_repeat("=", 50) . "\n";
    
    // 保存报告到文件
    $reportFile = "health_report_" . date('Y_m_d_H_i_s') . ".json";
    $healthReport['duration'] = $duration;
    $healthReport['end_time'] = date('Y-m-d H:i:s');
    
    file_put_contents($reportFile, json_encode($healthReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "详细报告已保存: $reportFile\n\n";
    
    // 如果有严重问题，提供建议
    if ($healthReport['critical_issues'] > 0) {
        echo "🚨 发现严重问题，建议：\n";
        echo "1. 检查PHP版本和扩展\n";
        echo "2. 运行 composer install 安装依赖\n";
        echo "3. 检查文件权限设置\n";
        echo "4. 确保数据库文件存在且可访问\n";
        echo "5. 配置环境变量文件\n\n";
    }
    
    if ($healthReport['warnings'] > 0) {
        echo "⚠️ 发现警告问题，建议检查相关配置\n\n";
    }
    
    if ($healthReport['overall_status'] === 'healthy') {
        echo "🎉 系统状态良好，可以正常运行！\n\n";
    }
}

// 执行所有检查
try {
    checkPHPEnvironment();
    checkFileSystem();
    checkComposerDependencies();
    checkDatabase();
    checkNetworkAndPorts();
    checkSystemProcesses();
    checkSecurityConfiguration();
    performanceBenchmark();
    generateHealthReport();
    
    echo "✅ 系统健康检查完成！\n";
    
} catch (Exception $e) {
    echo "❌ 健康检查过程中发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
