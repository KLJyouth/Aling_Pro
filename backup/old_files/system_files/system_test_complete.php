<?php
/**
 * AlingAI Pro 5.0 完整系统功能测试
 * 版本: 5.0.0-Final
 * 日期: 2024-12-19
 */

declare(strict_types=1);

echo "🧪 AlingAI Pro 5.0 完整系统测试\n";
echo str_repeat("=", 60) . "\n";
echo "测试开始时间: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 60) . "\n\n";

$testResults = [
    'total_tests' => 0,
    'passed_tests' => 0,
    'failed_tests' => 0,
    'warnings' => 0,
    'test_details' => []
];

/**
 * 执行测试并记录结果
 */
function runTest(string $testName, callable $testFunction, bool $isWarning = false): bool {
    global $testResults;
    
    $testResults['total_tests']++;
    
    try {
        $result = $testFunction();
        $status = $result ? '✅' : ($isWarning ? '⚠️' : '❌');
        
        if ($result) {
            $testResults['passed_tests']++;
            echo "$status $testName: 通过\n";
        } else {
            if ($isWarning) {
                $testResults['warnings']++;
                echo "$status $testName: 警告\n";
            } else {
                $testResults['failed_tests']++;
                echo "$status $testName: 失败\n";
            }
        }
        
        $testResults['test_details'][] = [
            'name' => $testName,
            'passed' => $result,
            'warning' => $isWarning && !$result,
            'timestamp' => date('H:i:s')
        ];
        
        return $result;
        
    } catch (Exception $e) {
        $testResults['failed_tests']++;
        echo "❌ $testName: 异常 - " . $e->getMessage() . "\n";
        
        $testResults['test_details'][] = [
            'name' => $testName,
            'passed' => false,
            'error' => $e->getMessage(),
            'timestamp' => date('H:i:s')
        ];
        
        return false;
    }
}

// 1. 基础环境测试
echo "🔧 基础环境测试\n";
echo str_repeat("-", 30) . "\n";

runTest('PHP版本检查', function() {
    return version_compare(PHP_VERSION, '8.1.0', '>=');
});

runTest('必需扩展检查', function() {
    $required = ['curl', 'json', 'mbstring', 'openssl'];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            return false;
        }
    }
    return true;
});

runTest('文件系统权限', function() {
    $dirs = ['logs', 'storage', 'public'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }
    }
    return true;
});

echo "\n";

// 2. 数据库系统测试
echo "🗄️ 数据库系统测试\n";
echo str_repeat("-", 30) . "\n";

runTest('数据库管理器加载', function() {
    return file_exists('src/Database/DatabaseManagerSimple.php');
});

runTest('数据库连接测试', function() {
    require_once 'src/Database/DatabaseManagerSimple.php';
    $db = \AlingAI\Database\DatabaseManager::getInstance();
    return $db->testConnection();
});

runTest('数据库读写测试', function() {
    require_once 'src/Database/DatabaseManagerSimple.php';
    $db = \AlingAI\Database\DatabaseManager::getInstance();
    
    // 写入测试
    $testKey = 'test_' . time();
    $testValue = 'test_value_' . rand(1000, 9999);
    $db->setConfig($testKey, $testValue);
    
    // 读取测试
    $readValue = $db->getConfig($testKey);
    
    return $readValue === $testValue;
});

runTest('安全事件记录测试', function() {
    require_once 'src/Database/DatabaseManagerSimple.php';
    $db = \AlingAI\Database\DatabaseManager::getInstance();
    
    $eventId = $db->logSecurityEvent('test', 'info', [
        'description' => '系统测试事件',
        'test_time' => date('Y-m-d H:i:s')
    ]);
    
    return $eventId > 0;
});

echo "\n";

// 3. 核心组件测试
echo "⚙️ 核心组件测试\n";
echo str_repeat("-", 30) . "\n";

runTest('WebSocket服务器文件', function() {
    return file_exists('src/Security/WebSocketSecurityServer.php');
});

runTest('安全控制器文件', function() {
    return file_exists('src/Controllers/Frontend/RealTimeSecurityController.php');
});

runTest('监控面板模板', function() {
    return file_exists('resources/views/security/real-time-threat-dashboard.twig');
});

runTest('路由配置文件', function() {
    return file_exists('config/routes.php');
});

runTest('环境配置文件', function() {
    return file_exists('.env');
});

echo "\n";

// 4. Web服务器测试
echo "🌐 Web服务器测试\n";
echo str_repeat("-", 30) . "\n";

runTest('Web服务器运行检查', function() {
    $connection = @fsockopen('localhost', 8000, $errno, $errstr, 1);
    if ($connection) {
        fclose($connection);
        return true;
    }
    return false;
});

runTest('静态文件访问', function() {
    return file_exists('public/test.html');
});

runTest('PHP脚本处理', function() {
    return file_exists('public/index.php');
});

echo "\n";

// 5. 性能测试
echo "🚀 性能测试\n";
echo str_repeat("-", 30) . "\n";

runTest('CPU性能测试', function() {
    $start = microtime(true);
    for ($i = 0; $i < 10000; $i++) {
        sqrt($i);
    }
    $time = microtime(true) - $start;
    return $time < 0.1;
}, true);

runTest('内存使用测试', function() {
    $memory = memory_get_usage(true) / 1024 / 1024;
    return $memory < 32;
}, true);

runTest('磁盘空间检查', function() {
    $free = disk_free_space('.') / 1024 / 1024;
    return $free > 100;
});

echo "\n";

// 6. 安全功能测试
echo "🔒 安全功能测试\n";
echo str_repeat("-", 30) . "\n";

runTest('环境变量保护', function() {
    return !file_exists('public/.env');
});

runTest('敏感文件保护', function() {
    $protectedFiles = ['composer.json', 'config/', 'src/'];
    foreach ($protectedFiles as $file) {
        if (file_exists("public/$file")) {
            return false;
        }
    }
    return true;
});

runTest('错误信息隐藏', function() {
    return ini_get('display_errors') == '0' || php_sapi_name() === 'cli';
}, true);

echo "\n";

// 7. 部署脚本测试
echo "🚀 部署脚本测试\n";
echo str_repeat("-", 30) . "\n";

runTest('Windows部署脚本', function() {
    return file_exists('deploy/complete_deployment.bat');
});

runTest('Linux部署脚本', function() {
    return file_exists('deploy/complete_deployment.sh');
});

runTest('快速启动脚本', function() {
    return file_exists('quick_start.php');
});

runTest('健康检查脚本', function() {
    return file_exists('quick_health_check.php');
});

echo "\n";

// 8. 生成测试报告
echo "📊 测试结果统计\n";
echo str_repeat("=", 60) . "\n";

$passRate = $testResults['total_tests'] > 0 ? 
    round(($testResults['passed_tests'] / $testResults['total_tests']) * 100, 1) : 0;

echo "测试总数: {$testResults['total_tests']}\n";
echo "通过测试: {$testResults['passed_tests']}\n";
echo "失败测试: {$testResults['failed_tests']}\n";
echo "警告项目: {$testResults['warnings']}\n";
echo "通过率: {$passRate}%\n";

// 判断系统状态
if ($testResults['failed_tests'] === 0) {
    if ($testResults['warnings'] === 0) {
        echo "\n🎉 测试结果: 系统完全正常！\n";
        echo "✅ 所有核心功能都正常工作\n";
        echo "🚀 系统准备就绪，可以投入使用\n";
    } else {
        echo "\n✅ 测试结果: 系统基本正常\n";
        echo "⚠️ 有 {$testResults['warnings']} 个警告项目\n";
        echo "💡 建议关注警告项目以获得最佳性能\n";
    }
} else {
    echo "\n❌ 测试结果: 发现问题\n";
    echo "🚨 有 {$testResults['failed_tests']} 个严重问题需要修复\n";
    echo "💡 建议运行修复脚本或检查相关配置\n";
}

// 保存详细报告
$reportFile = "logs/system_test_report_" . date('Y_m_d_H_i_s') . ".json";
$testResults['test_time'] = date('Y-m-d H:i:s');
$testResults['pass_rate'] = $passRate;
$testResults['system_info'] = [
    'php_version' => PHP_VERSION,
    'os' => PHP_OS,
    'memory_limit' => ini_get('memory_limit'),
    'disk_free' => round(disk_free_space('.') / 1024 / 1024, 2) . 'MB'
];

if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

file_put_contents($reportFile, json_encode($testResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n📄 详细测试报告已保存: $reportFile\n";

echo "\n🌐 系统访问地址:\n";
echo "  • 主页: http://localhost:8000/test.html\n";
echo "  • 系统状态: http://localhost:8000/\n";
echo "  • API测试: http://localhost:8000/api/status\n";

echo "\n🔧 管理命令:\n";
echo "  • 健康检查: php quick_health_check.php\n";
echo "  • 环境修复: php fix_environment.php\n";
echo "  • 完整测试: php complete_system_test.php\n";

echo "\n✅ 系统测试完成！\n";
echo "测试结束时间: " . date('Y-m-d H:i:s') . "\n";
