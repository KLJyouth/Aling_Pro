<?php
/**
 * AlingAI Pro 5.0 改进的系统健康检查
 * 版本: 5.0.0-Final
 * 日期: 2024-12-19
 */

declare(strict_types=1);

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 开始时间
$startTime = microtime(true);

echo "🔍 AlingAI Pro 5.0 系统健康检查 (改进版)\n";
echo str_repeat("=", 60) . "\n";
echo "开始时间: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 60) . "\n\n";

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
    
    if ($level === 'warning' && !$passed) {
        $status = '⚠️';
        $healthReport['warnings']++;
    } elseif (!$passed) {
        $healthReport['critical_issues']++;
        $healthReport['overall_status'] = 'critical';
    }
    
    echo sprintf("[%s] %s: %s\n", $status, $name, $message);
    
    $healthReport['checks'][] = [
        'name' => $name,
        'passed' => $passed,
        'message' => $message,
        'level' => $level,
        'timestamp' => date('H:i:s')
    ];
}

// 1. 检查PHP环境
echo "📋 检查PHP环境...\n";
$phpVersion = PHP_VERSION;
$versionOk = version_compare($phpVersion, '8.1.0', '>=');
recordCheck('PHP版本', $versionOk, "当前版本: $phpVersion " . ($versionOk ? '(符合要求)' : '(需要8.1+)'));

// 检查关键扩展
$extensions = [
    'curl' => extension_loaded('curl'),
    'json' => extension_loaded('json'),
    'mbstring' => extension_loaded('mbstring'),
    'openssl' => extension_loaded('openssl'),
    'pdo' => extension_loaded('pdo'),
    'sqlite3' => extension_loaded('sqlite3')
];

foreach ($extensions as $ext => $loaded) {
    recordCheck("PHP扩展: $ext", $loaded, $loaded ? '已加载' : '未加载', $loaded ? 'info' : 'warning');
}

// 内存限制
$memoryLimit = ini_get('memory_limit');
$memoryOk = $memoryLimit === '-1' || (int)$memoryLimit >= 256;
recordCheck('PHP内存限制', $memoryOk, "当前: $memoryLimit " . ($memoryOk ? '(充足)' : '(建议256MB+)'));

echo "\n";

// 2. 检查文件系统
echo "📁 检查文件系统...\n";
$requiredDirs = ['logs', 'storage', 'public', 'config', 'src', 'resources'];

foreach ($requiredDirs as $dir) {
    $exists = is_dir($dir);
    $writable = $exists && is_writable($dir);
    recordCheck("目录: $dir", $exists, $exists ? ($writable ? '存在且可写' : '存在但不可写') : '不存在');
}

// 检查关键文件
$requiredFiles = [
    '.env' => '环境配置文件',
    'composer.json' => 'Composer配置',
    'config/routes.php' => '路由配置'
];

foreach ($requiredFiles as $file => $desc) {
    $exists = file_exists($file);
    recordCheck("文件: $desc", $exists, $exists ? "存在" : "缺失");
}

echo "\n";

// 3. 检查数据库 - 使用新的数据库管理器
echo "🗄️ 检查数据库...\n";

try {
    // 确保数据库管理器文件存在
    if (file_exists('src/Database/DatabaseManager.php')) {
        require_once 'src/Database/DatabaseManager.php';
        
        $dbManager = \AlingAI\Database\DatabaseManager::getInstance();
        $connected = $dbManager->testConnection();
        $dbType = $dbManager->getType();
        
        recordCheck('数据库连接', $connected, $connected ? "成功 (使用 {$dbType})" : '失败');
        
        if ($connected) {
            // 获取数据库统计信息
            $stats = $dbManager->getStats();
            recordCheck('数据库统计', true, "类型: {$stats['database_type']}, 表数: " . count($stats['tables']));
            
            // 测试基本操作
            try {
                $testConfig = $dbManager->getConfig('system_name', 'Test');
                recordCheck('数据库读取', true, "配置读取测试成功");
                
                $dbManager->setConfig('health_check_time', date('Y-m-d H:i:s'));
                recordCheck('数据库写入', true, "配置写入测试成功");
                
            } catch (Exception $e) {
                recordCheck('数据库操作', false, "操作测试失败: " . $e->getMessage());
            }
        }
        
    } else {
        recordCheck('数据库管理器', false, '数据库管理器文件不存在');
    }
    
} catch (Exception $e) {
    recordCheck('数据库检查', false, '检查失败: ' . $e->getMessage());
}

echo "\n";

// 4. 检查Composer依赖
echo "📦 检查Composer依赖...\n";
$vendorExists = is_dir('vendor');
recordCheck('Vendor目录', $vendorExists, $vendorExists ? '存在' : '不存在，需要运行 composer install');

if ($vendorExists && file_exists('vendor/autoload.php')) {
    recordCheck('Autoload文件', true, '存在');
    
    try {
        require_once 'vendor/autoload.php';
        recordCheck('Autoload加载', true, '成功');
    } catch (Exception $e) {
        recordCheck('Autoload加载', false, '失败: ' . $e->getMessage());
    }
} else {
    recordCheck('Autoload文件', false, '不存在');
}

echo "\n";

// 5. 检查网络和端口
echo "🌐 检查网络和端口...\n";
$ports = [8000 => 'Web服务器', 8081 => 'WebSocket服务器'];

foreach ($ports as $port => $service) {
    $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
    $available = $connection !== false;
    
    if ($connection) {
        fclose($connection);
    }
    
    recordCheck("端口 $port ($service)", true, $available ? '端口被使用/服务运行中' : '端口可用', 'info');
}

echo "\n";

// 6. 检查关键组件
echo "⚙️ 检查关键组件...\n";
$components = [
    'src/Security/WebSocketSecurityServer.php' => 'WebSocket安全服务器',
    'src/Controllers/Frontend/RealTimeSecurityController.php' => '实时安全控制器',
    'resources/views/security/real-time-threat-dashboard.twig' => '威胁监控面板',
    'src/Database/DatabaseManager.php' => '数据库管理器',
    'src/Database/FileDatabase.php' => '文件数据库'
];

foreach ($components as $file => $desc) {
    $exists = file_exists($file);
    recordCheck("组件: $desc", $exists, $exists ? '存在' : '缺失');
}

echo "\n";

// 7. 检查部署脚本
echo "🚀 检查部署脚本...\n";
$deployScripts = [
    'deploy/complete_deployment.sh' => 'Linux部署脚本',
    'deploy/complete_deployment.bat' => 'Windows部署脚本',
    'quick_start.php' => '快速启动脚本',
    'fix_environment.php' => '环境修复脚本'
];

foreach ($deployScripts as $script => $desc) {
    $exists = file_exists($script);
    recordCheck("脚本: $desc", $exists, $exists ? '存在' : '缺失');
}

echo "\n";

// 8. 性能测试
echo "🚀 性能测试...\n";

// CPU测试
$cpuStart = microtime(true);
$sum = 0;
for ($i = 0; $i < 50000; $i++) {
    $sum += sqrt($i);
}
$cpuTime = microtime(true) - $cpuStart;
recordCheck('CPU性能', $cpuTime < 0.5, sprintf("计算耗时: %.3f秒", $cpuTime));

// 内存测试
$memoryUsage = memory_get_usage(true);
$memoryMB = round($memoryUsage / 1024 / 1024, 2);
recordCheck('内存使用', $memoryMB < 32, "当前使用: {$memoryMB}MB");

// 磁盘空间
$freeSpace = disk_free_space('.');
$freeSpaceMB = round($freeSpace / 1024 / 1024, 2);
recordCheck('磁盘空间', $freeSpaceMB > 100, "可用空间: {$freeSpaceMB}MB");

echo "\n";

// 9. 生成总结报告
$endTime = microtime(true);
$duration = round($endTime - $startTime, 3);

echo "📊 健康检查总结\n";
echo str_repeat("=", 60) . "\n";
echo "检查耗时: {$duration}秒\n";
echo "总体状态: " . ($healthReport['overall_status'] === 'healthy' ? '✅ 健康' : '❌ 异常') . "\n";
echo "严重问题: {$healthReport['critical_issues']}个\n";
echo "警告问题: {$healthReport['warnings']}个\n";
echo "检查项目: " . count($healthReport['checks']) . "个\n";

// 计算通过率
$totalChecks = count($healthReport['checks']);
$passedChecks = count(array_filter($healthReport['checks'], function($check) {
    return $check['passed'];
}));
$passRate = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100, 1) : 0;

echo "通过率: {$passRate}%\n";
echo str_repeat("=", 60) . "\n";

// 保存报告
$reportFile = "logs/health_report_" . date('Y_m_d_H_i_s') . ".json";
$healthReport['duration'] = $duration;
$healthReport['end_time'] = date('Y-m-d H:i:s');
$healthReport['pass_rate'] = $passRate;

// 确保logs目录存在
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

file_put_contents($reportFile, json_encode($healthReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "📄 详细报告已保存: $reportFile\n\n";

// 建议
if ($healthReport['overall_status'] === 'healthy') {
    echo "🎉 系统状态良好，准备就绪！\n";
    echo "💡 建议下一步操作：\n";
    echo "   1. 运行 php quick_start.php 开始部署\n";
    echo "   2. 或运行 deploy/complete_deployment.bat (Windows)\n";
    echo "   3. 或运行 deploy/complete_deployment.sh (Linux)\n\n";
} else {
    echo "🚨 发现问题，建议：\n";
    if ($healthReport['critical_issues'] > 0) {
        echo "   • 优先解决严重问题\n";
        echo "   • 运行 php fix_environment.php 修复环境\n";
        echo "   • 确保PHP版本和扩展正确安装\n";
    }
    if ($healthReport['warnings'] > 0) {
        echo "   • 检查警告项目，确保最佳性能\n";
    }
    echo "\n";
}

echo "✅ 系统健康检查完成！\n";
