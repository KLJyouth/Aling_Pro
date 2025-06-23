<?php
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
