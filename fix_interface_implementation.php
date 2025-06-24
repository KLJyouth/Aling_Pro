<?php
/**
 * 接口实现修复工具
 * 
 * 此脚本用于修复项目中的接口实现问题，特别是POSTagger中的tokenize方法签名与TokenizerInterface不匹配的问题
 */

// 设置基础配置
$projectRoot = __DIR__;
$backupDir = $projectRoot . '/backups/interface_fix_' . date('Ymd_His');
$fixCount = 0;
$errorCount = 0;
$backupCount = 0;

// 日志文件
$logFile = "interface_fix_" . date("Ymd_His") . ".log";
$reportFile = "INTERFACE_FIX_REPORT_" . date("Ymd_His") . ".md";

// 要修复的方法签名
$methodSignatureFixes = [
    'ai-engines/nlp/POSTagger.php' => [
        'tokenize' => [
            'old_signature' => 'public function tokenize(string $text): array',
            'new_signature' => 'public function tokenize(string $text, array $options = []): array'
        ]
    ]
];

// 初始化日志
echo "=== 接口实现修复工具 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";
file_put_contents($logFile, "=== 接口实现修复日志 - " . date("Y-m-d H:i:s") . " ===\n\n");

/**
 * 写入日志
 */
function log_message($message) {
    global $logFile;
    echo $message . "\n";
    file_put_contents($logFile, $message . "\n", FILE_APPEND);
}

/**
 * 创建备份目录
 */
function create_backup_dir() {
    global $backupDir;
    
    if (!is_dir($backupDir)) {
        if (mkdir($backupDir, 0777, true)) {
            log_message("已创建备份目录: $backupDir");
            return true;
        } else {
            log_message("无法创建备份目录: $backupDir");
            return false;
        }
    }
    
    return true;
}

/**
 * 创建文件备份
 */
function backup_file($file) {
    global $backupDir, $backupCount;
    
    $relativePath = $file;
    $backupPath = $backupDir . '/' . $relativePath;
    $backupDirPath = dirname($backupPath);
    
    if (!is_dir($backupDirPath)) {
        if (!mkdir($backupDirPath, 0777, true)) {
            log_message("无法创建备份子目录: $backupDirPath");
            return false;
        }
    }
    
    if (copy($file, $backupPath)) {
        log_message("已备份文件: $file -> $backupPath");
        $backupCount++;
        return true;
    } else {
        log_message("无法备份文件: $file");
        return false;
    }
}

/**
 * 修复方法签名
 */
function fix_method_signature($file, $methodName, $oldSignature, $newSignature) {
    global $fixCount, $errorCount;
    
    if (!file_exists($file)) {
        log_message("文件不存在: $file");
        $errorCount++;
        return false;
    }
    
    log_message("处理文件: $file");
    
    // 读取文件内容
    $content = file_get_contents($file);
    if ($content === false) {
        log_message("无法读取文件: $file");
        $errorCount++;
        return false;
    }
    
    // 检查是否包含旧方法签名
    if (strpos($content, $oldSignature) === false) {
        log_message("文件不包含目标方法签名 '$oldSignature': $file");
        return false;
    }
    
    // 备份文件
    if (!backup_file($file)) {
        return false;
    }
    
    // 替换方法签名
    $newContent = str_replace(
        $oldSignature,
        $newSignature,
        $content
    );
    
    // 修改方法体，适应新的参数
    if ($methodName === 'tokenize') {
        // 对于tokenize方法，需要修改方法体以使用options参数
        $pattern = '/public function tokenize\(string \$text(?:, array \$options = \[\])?\): array\s*\{([^}]+)\}/s';
        if (preg_match($pattern, $newContent, $matches)) {
            $methodBody = $matches[1];
            
            // 检查方法体是否已经使用了$options参数
            if (strpos($methodBody, '$options') === false) {
                // 如果没有使用$options参数，添加一个注释说明
                $newMethodBody = "\n        // 注意：添加了\$options参数以符合接口要求，但尚未在方法体中使用\n" . $methodBody;
                $newContent = preg_replace($pattern, "public function tokenize(string \$text, array \$options = []): array\n    {" . $newMethodBody . "}", $newContent);
            }
        }
    }
    
    // 写入修改后的内容
    if (file_put_contents($file, $newContent)) {
        log_message("已修复方法签名: $file");
        log_message("  - 方法: $methodName");
        log_message("  - 从: $oldSignature");
        log_message("  - 到: $newSignature");
        $fixCount++;
        return true;
    } else {
        log_message("无法写入文件: $file");
        $errorCount++;
        return false;
    }
}

/**
 * 生成报告
 */
function generate_report() {
    global $methodSignatureFixes, $fixCount, $errorCount, $backupCount, $reportFile, $backupDir;
    
    $report = "# 接口实现修复报告\n\n";
    $report .= "## 执行摘要\n\n";
    $report .= "- 执行时间: " . date('Y-m-d H:i:s') . "\n";
    $report .= "- 修复的方法数: $fixCount\n";
    $report .= "- 备份的文件数: $backupCount\n";
    $report .= "- 错误数: $errorCount\n";
    $report .= "- 备份目录: $backupDir\n\n";
    
    $report .= "## 修复的方法签名\n\n";
    
    foreach ($methodSignatureFixes as $file => $methods) {
        $report .= "### 文件: `$file`\n\n";
        
        foreach ($methods as $methodName => $signatures) {
            $report .= "#### 方法: `$methodName`\n\n";
            $report .= "- **旧签名**: `{$signatures['old_signature']}`\n";
            $report .= "- **新签名**: `{$signatures['new_signature']}`\n\n";
        }
    }
    
    $report .= "## 后续步骤\n\n";
    $report .= "1. 验证修复后的文件是否正常工作\n";
    $report .= "2. 运行接口实现检查工具，确认所有接口方法都已正确实现\n";
    $report .= "3. 运行PHP语法检查，确保没有引入新的错误\n";
    $report .= "4. 更新方法实现，确保新参数被正确使用\n\n";
    
    $report .= "## 预防措施\n\n";
    $report .= "1. 使用IDE功能自动检测接口实现问题\n";
    $report .= "2. 在CI/CD流程中加入接口实现检查\n";
    $report .= "3. 实施严格的代码审查流程\n";
    $report .= "4. 使用PHPStan或Psalm等静态分析工具\n";
    
    file_put_contents($reportFile, $report);
    log_message("\n报告已生成: $reportFile");
}

// 创建备份目录
if (!create_backup_dir()) {
    log_message("无法继续，退出程序");
    exit(1);
}

// 修复方法签名
log_message("开始修复方法签名...");
foreach ($methodSignatureFixes as $file => $methods) {
    foreach ($methods as $methodName => $signatures) {
        fix_method_signature($file, $methodName, $signatures['old_signature'], $signatures['new_signature']);
    }
}

// 生成报告
generate_report();

// 输出结果摘要
echo "\n=== 修复结果摘要 ===\n";
echo "修复的方法数: $fixCount\n";
echo "备份的文件数: $backupCount\n";
echo "错误数: $errorCount\n";
echo "备份目录: $backupDir\n";
echo "详细报告: $reportFile\n"; 