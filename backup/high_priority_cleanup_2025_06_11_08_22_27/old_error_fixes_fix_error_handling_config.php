<?php
/**
 * 生产环境错误处理配置修复
 */

echo "🔧 修复生产环境错误处理配置\n";
echo "============================\n";

// 1. 修复PHP配置
echo "📋 设置生产环境PHP配置...\n";

// 关闭错误显示（生产环境要求）
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

// 但启用错误日志记录
ini_set('log_errors', '1');

// 设置错误日志文件路径
$errorLogPath = __DIR__ . '/logs/php_errors.log';
if (!is_dir(dirname($errorLogPath))) {
    mkdir(dirname($errorLogPath), 0755, true);
}
ini_set('error_log', $errorLogPath);

// 设置报告所有错误但不显示
error_reporting(E_ALL);

echo "✅ PHP错误配置已设置为生产环境模式\n";
echo "   - display_errors: " . ini_get('display_errors') . "\n";
echo "   - log_errors: " . ini_get('log_errors') . "\n";
echo "   - error_log: " . ini_get('error_log') . "\n";

// 2. 创建生产环境配置文件
echo "📋 创建生产环境配置文件...\n";

$prodConfig = [
    'php' => [
        'display_errors' => 'Off',
        'display_startup_errors' => 'Off',
        'log_errors' => 'On',
        'error_reporting' => E_ALL,
        'memory_limit' => '256M',
        'max_execution_time' => 30,
        'upload_max_filesize' => '10M',
        'post_max_size' => '10M'
    ],
    'application' => [
        'environment' => 'production',
        'debug' => false,
        'cache_enabled' => true,
        'session_secure' => true,
        'csrf_protection' => true
    ]
];

$configDir = __DIR__ . '/config';
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

file_put_contents($configDir . '/production.json', json_encode($prodConfig, JSON_PRETTY_PRINT));
echo "✅ 生产环境配置文件已创建\n";

// 3. 创建错误处理验证脚本
echo "📋 创建错误处理验证脚本...\n";

$validationScript = '<?php
/**
 * 错误处理验证脚本
 */

class ErrorHandlingValidator {
    public function validate(): array {
        $results = [];
        
        // 检查display_errors配置
        $displayErrors = ini_get("display_errors");
        $results["display_errors"] = [
            "value" => $displayErrors,
            "valid" => $displayErrors == "0" || $displayErrors == "",
            "requirement" => "Should be Off in production"
        ];
        
        // 检查log_errors配置
        $logErrors = ini_get("log_errors");
        $results["log_errors"] = [
            "value" => $logErrors,
            "valid" => $logErrors == "1" || $logErrors == "On",
            "requirement" => "Should be On"
        ];
        
        // 检查错误日志文件
        $errorLog = ini_get("error_log");
        $results["error_log"] = [
            "value" => $errorLog,
            "valid" => !empty($errorLog),
            "requirement" => "Should be configured"
        ];
        
        // 检查错误报告级别
        $errorReporting = error_reporting();
        $results["error_reporting"] = [
            "value" => $errorReporting,
            "valid" => $errorReporting == E_ALL,
            "requirement" => "Should report all errors"
        ];
        
        // 总体状态
        $allValid = true;
        foreach ($results as $check) {
            if (!$check["valid"]) {
                $allValid = false;
                break;
            }
        }
        
        return [
            "status" => $allValid ? "valid" : "invalid",
            "timestamp" => date("Y-m-d H:i:s"),
            "checks" => $results
        ];
    }
}

// 如果直接运行此脚本
if (basename(__FILE__) === basename($_SERVER["SCRIPT_NAME"])) {
    $validator = new ErrorHandlingValidator();
    $result = $validator->validate();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
';

file_put_contents(__DIR__ . '/error_handling_validator.php', $validationScript);
echo "✅ 错误处理验证脚本已创建\n";

// 4. 运行验证
echo "📋 运行错误处理验证...\n";
$output = shell_exec('php error_handling_validator.php 2>&1');
echo $output . "\n";

echo "✅ 生产环境错误处理配置修复完成！\n";
