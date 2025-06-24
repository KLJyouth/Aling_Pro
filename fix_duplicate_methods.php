<?php
/**
 * 重复方法修复工具
 * 
 * 此脚本用于修复项目中的重复方法问题，特别是BaseKGEngine类中同时存在抽象方法和具体实现的问题
 */

// 设置基础配置
$projectRoot = __DIR__;
$backupDir = $projectRoot . '/backups/duplicate_methods_fix_' . date('Ymd_His');
$fixCount = 0;
$errorCount = 0;
$backupCount = 0;

// 日志文件
$logFile = "duplicate_methods_fix_" . date("Ymd_His") . ".log";
$reportFile = "DUPLICATE_METHODS_FIX_REPORT_" . date("Ymd_His") . ".md";

// 要修复的重复方法
$duplicateMethodFixes = [
    'apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php' => [
        'class' => 'BaseKGEngine',
        'method' => 'process',
        'keep' => 'abstract', // 保留抽象方法，删除具体实现
        'abstract_signature' => 'abstract public function process(mixed $input, array $options = []): array;',
        'implementation_pattern' => '/public function process\(\) \{[^}]*\}/s'
    ]
];

// 初始化日志
echo "=== 重复方法修复工具 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";
file_put_contents($logFile, "=== 重复方法修复日志 - " . date("Y-m-d H:i:s") . " ===\n\n");

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
 * 修复重复方法
 */
function fix_duplicate_method($file, $className, $methodName, $keep, $abstractSignature, $implementationPattern) {
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
    
    // 检查是否包含目标类
    if (strpos($content, "class $className") === false) {
        log_message("文件不包含目标类 '$className': $file");
        return false;
    }
    
    // 备份文件
    if (!backup_file($file)) {
        return false;
    }
    
    // 根据保留选项修复重复方法
    $newContent = $content;
    
    if ($keep === 'abstract') {
        // 保留抽象方法，删除具体实现
        if (preg_match($implementationPattern, $newContent)) {
            $newContent = preg_replace($implementationPattern, '', $newContent);
            log_message("已删除方法实现: $className::$methodName");
        } else {
            log_message("未找到方法实现: $className::$methodName");
            return false;
        }
    } else if ($keep === 'implementation') {
        // 保留具体实现，删除抽象方法
        if (strpos($newContent, $abstractSignature) !== false) {
            $newContent = str_replace($abstractSignature, '', $newContent);
            log_message("已删除抽象方法: $className::$methodName");
        } else {
            log_message("未找到抽象方法: $className::$methodName");
            return false;
        }
    }
    
    // 写入修改后的内容
    if ($newContent !== $content) {
        if (file_put_contents($file, $newContent)) {
            log_message("已修复重复方法: $file");
            log_message("  - 类: $className");
            log_message("  - 方法: $methodName");
            log_message("  - 保留: " . ($keep === 'abstract' ? '抽象方法' : '具体实现'));
            $fixCount++;
            return true;
        } else {
            log_message("无法写入文件: $file");
            $errorCount++;
            return false;
        }
    } else {
        log_message("文件内容未变化: $file");
        return false;
    }
}

/**
 * 生成报告
 */
function generate_report() {
    global $duplicateMethodFixes, $fixCount, $errorCount, $backupCount, $reportFile, $backupDir;
    
    $report = "# 重复方法修复报告\n\n";
    $report .= "## 执行摘要\n\n";
    $report .= "- 执行时间: " . date('Y-m-d H:i:s') . "\n";
    $report .= "- 修复的方法数: $fixCount\n";
    $report .= "- 备份的文件数: $backupCount\n";
    $report .= "- 错误数: $errorCount\n";
    $report .= "- 备份目录: $backupDir\n\n";
    
    $report .= "## 修复的重复方法\n\n";
    
    foreach ($duplicateMethodFixes as $file => $fix) {
        $report .= "### 文件: `$file`\n\n";
        $report .= "- **类**: `{$fix['class']}`\n";
        $report .= "- **方法**: `{$fix['method']}`\n";
        $report .= "- **保留**: " . ($fix['keep'] === 'abstract' ? '抽象方法' : '具体实现') . "\n\n";
        
        if ($fix['keep'] === 'abstract') {
            $report .= "**保留的抽象方法**:\n```php\n{$fix['abstract_signature']}\n```\n\n";
        } else {
            $report .= "**保留的具体实现**:\n```php\n// 具体实现内容\n```\n\n";
        }
    }
    
    $report .= "## 后续步骤\n\n";
    $report .= "1. 验证修复后的文件是否正常工作\n";
    $report .= "2. 确保所有子类都正确实现了抽象方法\n";
    $report .= "3. 运行PHP语法检查，确保没有引入新的错误\n\n";
    
    $report .= "## 预防措施\n\n";
    $report .= "1. 使用IDE功能自动检测方法重复问题\n";
    $report .= "2. 在CI/CD流程中加入代码质量检查\n";
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

// 修复重复方法
log_message("开始修复重复方法...");
foreach ($duplicateMethodFixes as $file => $fix) {
    fix_duplicate_method(
        $file,
        $fix['class'],
        $fix['method'],
        $fix['keep'],
        $fix['abstract_signature'],
        $fix['implementation_pattern']
    );
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