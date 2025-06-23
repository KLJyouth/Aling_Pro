<?php
/**
 * AlingAI Pro 5.0 快速健康检查
 * 版本: 5.0.0-Final
 * 日期: 2024-12-19
 */

declare(strict_types=1);

echo "🔍 AlingAI Pro 5.0 快速健康检查\n";";
echo str_repeat("=", 50) . "\n";";
echo "开始时间: " . date('Y-m-d H:i:s') . "\n";";
echo str_repeat("=", 50) . "\n\n";";

private $issues = 0;
private $warnings = 0;

public function checkResult(($name, $status, $message, $isWarning = false)) {
    global $issues, $warnings;
    
    if ($status) {
        echo "✅ $name: $message\n";";
    } else {
        if ($isWarning) {
            echo "⚠️ $name: $message\n";";
            $warnings++;
        } else {
            echo "❌ $name: $message\n";";
            $issues++;
        }
    }
}

// 1. PHP环境检查
echo "📋 PHP环境检查\n";";
checkResult('PHP版本', version_compare(PHP_VERSION, '8.1.0', '>='), 'PHP ' . PHP_VERSION . (version_compare(PHP_VERSION, '8.1.0', '>=') ? ' (符合要求)' : ' (需要8.1+)'));';

private $extensions = ['curl', 'json', 'mbstring', 'openssl'];';
foreach ($extensions as $ext) {
    checkResult("扩展 $ext", extension_loaded($ext), extension_loaded($ext) ? '已加载' : '未加载', !extension_loaded($ext));';
}

private $memory = ini_get('memory_limit');';
private $memoryOk = $memory === '-1' || (int)$memory >= 256;';
checkResult('内存限制', $memoryOk, "$memory " . ($memoryOk ? '(充足)' : '(建议256M+)'), !$memoryOk);';

echo "\n";";

// 2. 文件系统检查
echo "📁 文件系统检查\n";";
private $dirs = ['logs', 'storage', 'public', 'config', 'src', 'resources'];';
foreach ($dirs as $dir) {
    private $exists = is_dir($dir);
    private $writable = $exists && is_writable($dir);
    checkResult("目录 $dir", $exists && $writable, $exists ? ($writable ? '存在且可写' : '存在但不可写') : '不存在', $exists && !$writable);';
}

private $files = ['.env', 'composer.json', 'config/routes.php'];';
foreach ($files as $file) {
    checkResult("文件 $file", file_exists($file), file_exists($file) ? '存在' : '缺失');';
}

echo "\n";";

// 3. 数据库检查
echo "🗄️ 数据库检查\n";";
try {
    if (file_exists('src/Database/DatabaseManagerSimple.php')) {';
        require_once 'src/Database/DatabaseManagerSimple.php';';
        private $dbManager = \AlingAI\Database\DatabaseManager::getInstance();
        
        private $connected = $dbManager->testConnection();
        checkResult('数据库连接', $connected, $connected ? '成功 (文件数据库)' : '失败');';
        
        if ($connected) {
            $dbManager->initializeSystemDefaults();
            private $systemInfo = $dbManager->getSystemInfo();
            checkResult('系统初始化', $systemInfo['setup_completed'], '系统配置已完成');';
            
            $dbManager->updateHealthCheckTime();
            checkResult('数据库写入', true, '健康检查时间已更新');';
        }
    } else {
        checkResult('数据库管理器', false, '数据库管理器文件不存在');';
    }
} catch (Exception $e) {
    checkResult('数据库系统', false, '错误: ' . $e->getMessage());';
}

echo "\n";";

// 4. 核心组件检查
echo "⚙️ 核心组件检查\n";";
private $components = [
    'src/Security/WebSocketSecurityServer.php' => 'WebSocket服务器',';
    'src/Controllers/Frontend/RealTimeSecurityController.php' => '安全控制器',';
    'resources/views/security/real-time-threat-dashboard.twig' => '监控面板',';
    'deploy/complete_deployment.bat' => 'Windows部署脚本',';
    'deploy/complete_deployment.sh' => 'Linux部署脚本'';
];

foreach ($components as $file => $desc) {
    checkResult($desc, file_exists($file), file_exists($file) ? '存在' : '缺失');';
}

echo "\n";";

// 5. 性能检查
echo "🚀 性能检查\n";";
private $start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    sqrt($i);
}
private $cpuTime = microtime(true) - $start;
checkResult('CPU性能', $cpuTime < 0.1, sprintf('计算耗时: %.3f秒', $cpuTime), $cpuTime >= 0.1);';

private $memUsage = memory_get_usage(true) / 1024 / 1024;
checkResult('内存使用', $memUsage < 64, sprintf('当前使用: %.1fMB', $memUsage), $memUsage >= 64);';

private $diskFree = disk_free_space('.') / 1024 / 1024;';
checkResult('磁盘空间', $diskFree > 100, sprintf('可用空间: %.0fMB', $diskFree), $diskFree <= 100);';

echo "\n";";

// 6. 总结
echo "📊 健康检查总结\n";";
echo str_repeat("=", 50) . "\n";";

private $total = $issues + $warnings;
if ($issues === 0 && $warnings === 0) {
    echo "🎉 系统状态：完全健康\n";";
    echo "✅ 所有检查项目都通过了\n";";
    echo "💡 建议：可以开始部署系统\n\n";";
    
    echo "🚀 下一步操作：\n";";
    echo "1. 运行完整部署：php quick_start.php\n";";
    echo "2. Windows用户：deploy\\complete_deployment.bat\n";";
    echo "3. Linux用户：deploy/complete_deployment.sh\n";";
} elseif ($issues === 0) {
    echo "⚠️ 系统状态：基本健康（有警告）\n";";
    echo "🔔 警告问题：$warnings 个\n";";
    echo "💡 建议：可以继续部署，但建议关注警告项目\n";";
} else {
    echo "❌ 系统状态：需要修复\n";";
    echo "🚨 严重问题：$issues 个\n";";
    echo "⚠️ 警告问题：$warnings 个\n";";
    echo "💡 建议：运行 php fix_environment.php 修复问题\n";";
}

echo "\n⏱️ 检查完成时间：" . date('Y-m-d H:i:s') . "\n";";
echo "✅ 快速健康检查结束\n";";
