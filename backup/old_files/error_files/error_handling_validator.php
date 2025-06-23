<?php
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
