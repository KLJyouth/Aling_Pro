<?php
/**
 * 验证已修复文件的PHP语法正确性
 */

// 设置执行时间，防止超时
set_time_limit(0);
ini_set("memory_limit", "1024M");

// 日志文件
$log_file = "validate_fixed_files_" . date("Ymd_His") . ".log";
$report_file = "VALIDATION_REPORT_" . date("Ymd_His") . ".md";

// 初始化日志
file_put_contents($log_file, "=== 已修复文件验证日志 - " . date("Y-m-d H:i:s") . " ===\n\n");
echo "开始验证已修复的PHP文件...\n\n";

// 统计数据
$stats = [
    'validated_files' => 0,
    'valid_files' => 0,
    'invalid_files' => 0,
    'errors' => []
];

/**
 * 写入日志
 */
function log_message($message) {
    global $log_file;
    echo $message . "\n";
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
}

/**
 * 验证PHP文件语法
 */
function validate_php_file($file_path) {
    global $stats;
    
    if (!file_exists($file_path)) {
        log_message("文件不存在: $file_path");
        return false;
    }
    
    log_message("验证文件: $file_path");
    $stats['validated_files']++;
    
    // 使用PHP的lint功能检查语法
    $output = [];
    $return_var = 0;
    exec("php -l " . escapeshellarg($file_path), $output, $return_var);
    
    $is_valid = ($return_var === 0);
    
    if ($is_valid) {
        log_message("  ✅ 语法正确: $file_path");
        $stats['valid_files']++;
        return true;
    } else {
        $error_message = implode("\n", $output);
        log_message("  ❌ 语法错误: $file_path");
        log_message("     错误信息: $error_message");
        $stats['invalid_files']++;
        $stats['errors'][$file_path] = $error_message;
        return false;
    }
}

/**
 * 验证已修复的文件
 */
function validate_fixed_files() {
    // 已修复的文件列表
    $files = [
        'apps/ai-platform/Services/CV/ComputerVisionProcessor.php',
        'apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php',
        'apps/ai-platform/Services/Speech/SpeechProcessor.php',
        'apps/blockchain/Services/BlockchainServiceManager.php',
        'apps/blockchain/Services/SmartContractManager.php',
        'apps/blockchain/Services/WalletManager.php',
        'config/database.php',
        'completed/Config/database.php'
    ];
    
    foreach ($files as $file) {
        validate_php_file($file);
    }
}

/**
 * 生成验证报告
 */
function generate_report() {
    global $stats, $report_file;
    
    $report = "# PHP文件验证报告\n\n";
    $report .= "## 验证时间: " . date("Y-m-d H:i:s") . "\n\n";
    
    $report .= "## 验证摘要\n\n";
    $report .= "- 验证文件总数: {$stats['validated_files']}\n";
    $report .= "- 语法正确文件: {$stats['valid_files']}\n";
    $report .= "- 语法错误文件: {$stats['invalid_files']}\n\n";
    
    if ($stats['invalid_files'] > 0) {
        $report .= "## 错误详情\n\n";
        foreach ($stats['errors'] as $file => $error) {
            $report .= "### $file\n\n";
            $report .= "```\n$error\n```\n\n";
        }
    } else {
        $report .= "## 恭喜！\n\n";
        $report .= "所有已修复的文件都通过了PHP语法验证，没有发现语法错误。\n\n";
    }
    
    $report .= "## 建议\n\n";
    
    if ($stats['invalid_files'] > 0) {
        $report .= "1. 请检查并修复上述文件中的语法错误\n";
        $report .= "2. 修复后再次运行此验证脚本确认问题已解决\n";
    } else {
        $report .= "1. 继续使用PHP语法检查作为开发流程的一部分\n";
        $report .= "2. 考虑添加更多静态分析工具如PHPStan或Psalm\n";
        $report .= "3. 确保所有新代码在提交前都通过语法检查\n";
    }
    
    file_put_contents($report_file, $report);
    log_message("\n验证报告已生成: $report_file");
}

// 执行验证
validate_fixed_files();

// 生成报告
generate_report();

// 输出结果摘要
echo "\n=== 验证结果摘要 ===\n";
echo "验证文件总数: {$stats['validated_files']}\n";
echo "语法正确文件: {$stats['valid_files']}\n";
echo "语法错误文件: {$stats['invalid_files']}\n";
echo "详细报告: $report_file\n"; 