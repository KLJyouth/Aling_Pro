<?php
/**
 * 最终错误处理完整修复脚本
 * 确保三完编译的错误处理配置100%正确
 */

echo "🔧 开始最终错误处理完整修复...\n";

// 1. 设置运行时PHP错误处理配置
echo "📋 设置运行时PHP错误处理配置...\n";

// 立即应用错误处理设置
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_reporting', E_ALL);

echo "   ✅ display_errors 设置为: " . ini_get('display_errors') . "\n";
echo "   ✅ log_errors 设置为: " . ini_get('log_errors') . "\n";
echo "   ✅ error_reporting 设置为: " . ini_get('error_reporting') . "\n";

// 2. 创建PHP ini配置文件片段
echo "📝 创建PHP配置文件片段...\n";

$phpIniContent = <<<INI
; AlingAi Pro 生产环境错误处理配置
; 生成时间: {date('Y-m-d H:i:s')}
display_errors = Off
log_errors = On
error_reporting = E_ALL
error_log = {__DIR__}/logs/error.log

; 性能优化
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000

; 安全配置
expose_php = Off
session.cookie_httponly = 1
session.use_strict_mode = 1
INI;

file_put_contents(__DIR__ . '/config/production.ini', $phpIniContent);
echo "   ✅ 创建了 config/production.ini\n";

// 3. 确保日志目录存在且可写
echo "📁 确保日志目录配置...\n";

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    echo "   ✅ 创建日志目录: $logDir\n";
}

$errorLogFile = $logDir . '/error.log';
if (!file_exists($errorLogFile)) {
    file_put_contents($errorLogFile, "# AlingAi Pro Error Log - Created: " . date('Y-m-d H:i:s') . "\n");
    chmod($errorLogFile, 0644);
    echo "   ✅ 创建错误日志文件: $errorLogFile\n";
}

// 检查权限
if (is_writable($logDir) && is_writable($errorLogFile)) {
    echo "   ✅ 日志目录和文件权限正确\n";
} else {
    echo "   ❌ 日志目录或文件权限有问题\n";
}

// 4. 更新.env文件中的环境变量
echo "🔧 更新环境配置...\n";

$envFile = __DIR__ . '/.env';
$envContent = file_exists($envFile) ? file_get_contents($envFile) : "";

// 添加或更新错误处理相关的环境变量
$errorHandlingVars = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'PHP_DISPLAY_ERRORS' => '0',
    'PHP_LOG_ERRORS' => '1',
    'PHP_ERROR_REPORTING' => 'E_ALL'
];

foreach ($errorHandlingVars as $key => $value) {
    if (strpos($envContent, $key) !== false) {
        $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
    } else {
        $envContent .= "\n{$key}={$value}";
    }
}

file_put_contents($envFile, $envContent);
echo "   ✅ 更新了 .env 文件中的错误处理配置\n";

// 5. 创建错误处理验证函数
echo "🧪 创建错误处理验证函数...\n";

$validationContent = '<?php
/**
 * 错误处理配置验证
 */

function validateErrorHandlingConfiguration(): array {
    $results = [
        "display_errors" => ini_get("display_errors") == "0",
        "log_errors" => ini_get("log_errors") == "1", 
        "error_reporting" => ini_get("error_reporting") == E_ALL,
        "log_directory_exists" => is_dir(__DIR__ . "/logs"),
        "log_directory_writable" => is_writable(__DIR__ . "/logs"),
        "error_log_exists" => file_exists(__DIR__ . "/logs/error.log"),
        "error_log_writable" => is_writable(__DIR__ . "/logs/error.log"),
        "app_env" => ($_ENV["APP_ENV"] ?? "development") === "production"
    ];
    
    $allPassed = array_reduce($results, function($carry, $result) {
        return $carry && $result;
    }, true);
    
    return [
        "all_passed" => $allPassed,
        "details" => $results,
        "summary" => array_sum($results) . "/" . count($results) . " 检查通过",
        "timestamp" => date("Y-m-d H:i:s")
    ];
}

// 如果直接调用，输出验证结果
if (basename(__FILE__) === basename($_SERVER["SCRIPT_NAME"])) {
    $validation = validateErrorHandlingConfiguration();
    echo "错误处理配置验证结果:\n";
    echo "总体状态: " . ($validation["all_passed"] ? "✅ 通过" : "❌ 失败") . "\n";
    echo "详细结果: " . $validation["summary"] . "\n";
    foreach ($validation["details"] as $check => $passed) {
        echo "  " . ($passed ? "✅" : "❌") . " {$check}\n";
    }
    return $validation;
}
';

file_put_contents(__DIR__ . '/error_handling_validation.php', $validationContent);
echo "   ✅ 创建了错误处理验证脚本\n";

// 6. 运行验证测试
echo "🧪 运行错误处理验证测试...\n";

include __DIR__ . '/error_handling_validation.php';
$validation = validateErrorHandlingConfiguration();

echo "验证结果:\n";
echo "   总体状态: " . ($validation['all_passed'] ? "✅ 通过" : "❌ 失败") . "\n";
echo "   通过检查: " . $validation['summary'] . "\n";

foreach ($validation['details'] as $check => $passed) {
    echo "   " . ($passed ? "✅" : "❌") . " {$check}\n";
}

// 7. 保存验证结果到JSON文件
$resultFile = __DIR__ . '/error_handling_final_validation.json';
file_put_contents($resultFile, json_encode($validation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "   ✅ 验证结果已保存到: $resultFile\n";

echo "\n🎉 最终错误处理完整修复完成！\n";
echo "📊 配置状态: " . ($validation['all_passed'] ? "✅ 完全就绪" : "⚠️ 需要进一步检查") . "\n";

return $validation;
