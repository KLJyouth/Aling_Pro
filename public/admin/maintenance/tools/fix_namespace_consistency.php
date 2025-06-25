<?php
/**
 * 命名空间一致性修复工�?
 * 
 * 此脚本用于修复项目中的命名空间一致性问题，特别是TokenizerInterface和其实现类之间的命名空间不一�?
 */

// 设置基础配置
$projectRoot = __DIR__;
$backupDir = $projectRoot . '/backups/namespace_fix_' . date('Ymd_His'];
$fixCount = 0;
$errorCount = 0;
$backupCount = 0;

// 日志文件
$logFile = "namespace_fix_" . date("Ymd_His") . ".log";
$reportFile = "NAMESPACE_FIX_REPORT_" . date("Ymd_His") . ".md";

// 要修复的文件列表
$filesToFix = [
    'ai-engines/nlp/ChineseTokenizer.php' => [
        'old_namespace' => 'AlingAi\AI\Engines\NLP',
        'new_namespace' => 'AlingAi\Engines\NLP'
    ], 
    'ai-engines/nlp/EnglishTokenizer.php' => [
        'old_namespace' => 'AlingAi\AI\Engines\NLP',
        'new_namespace' => 'AlingAi\Engines\NLP'
    ], 
    'ai-engines/nlp/POSTagger.php' => [
        'old_namespace' => 'AlingAi\AI\Engines\NLP',
        'new_namespace' => 'AlingAi\Engines\NLP'
    ]
];

// 初始化日�?
echo "=== 命名空间一致性修复工�?===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";
file_put_contents($logFile, "=== 命名空间一致性修复日�?- " . date("Y-m-d H:i:s") . " ===\n\n"];

/**
 * 写入日志
 */
function log_message($message) {
    global $logFile;
    echo $message . "\n";
    file_put_contents($logFile, $message . "\n", FILE_APPEND];
}

/**
 * 创建备份目录
 */
function create_backup_dir() {
    global $backupDir;
    
    if (!is_dir($backupDir)) {
        if (mkdir($backupDir, 0777, true)) {
            log_message("已创建备份目�? $backupDir"];
            return true;
        } else {
            log_message("无法创建备份目录: $backupDir"];
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
    $backupDirPath = dirname($backupPath];
    
    if (!is_dir($backupDirPath)) {
        if (!mkdir($backupDirPath, 0777, true)) {
            log_message("无法创建备份子目�? $backupDirPath"];
            return false;
        }
    }
    
    if (copy($file, $backupPath)) {
        log_message("已备份文�? $file -> $backupPath"];
        $backupCount++;
        return true;
    } else {
        log_message("无法备份文件: $file"];
        return false;
    }
}

/**
 * 修复文件命名空间
 */
function fix_file_namespace($file, $oldNamespace, $newNamespace) {
    global $fixCount, $errorCount;
    
    if (!file_exists($file)) {
        log_message("文件不存�? $file"];
        $errorCount++;
        return false;
    }
    
    log_message("处理文件: $file"];
    
    // 读取文件内容
    $content = file_get_contents($file];
    if ($content === false) {
        log_message("无法读取文件: $file"];
        $errorCount++;
        return false;
    }
    
    // 检查是否包含旧命名空间
    if (strpos($content, "namespace $oldNamespace;") === false) {
        log_message("文件不包含目标命名空�?'$oldNamespace': $file"];
        return false;
    }
    
    // 备份文件
    if (!backup_file($file)) {
        return false;
    }
    
    // 替换命名空间
    $newContent = str_replace(
        "namespace $oldNamespace;",
        "namespace $newNamespace;",
        $content
    ];
    
    // 写入修改后的内容
    if (file_put_contents($file, $newContent)) {
        log_message("已修复命名空�? $file"];
        log_message("  - �? $oldNamespace"];
        log_message("  - �? $newNamespace"];
        $fixCount++;
        return true;
    } else {
        log_message("无法写入文件: $file"];
        $errorCount++;
        return false;
    }
}

/**
 * 修复使用旧命名空间的引用
 */
function fix_namespace_references($directory, $oldNamespace, $newNamespace) {
    global $fixCount, $errorCount;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    ];
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname(];
            
            // 读取文件内容
            $content = file_get_contents($filePath];
            if ($content === false) {
                continue;
            }
            
            // 检查是否包含旧命名空间的引�?
            $pattern = '/use\s+' . preg_quote($oldNamespace, '/') . '\\\\([^;]+];/';
            if (preg_match($pattern, $content)) {
                // 备份文件
                if (!backup_file($filePath)) {
                    continue;
                }
                
                // 替换命名空间引用
                $newContent = preg_replace(
                    $pattern,
                    'use ' . $newNamespace . '\\\\$1;',
                    $content
                ];
                
                // 写入修改后的内容
                if (file_put_contents($filePath, $newContent)) {
                    log_message("已修复命名空间引�? $filePath"];
                    log_message("  - �? $oldNamespace"];
                    log_message("  - �? $newNamespace"];
                    $fixCount++;
                } else {
                    log_message("无法写入文件: $filePath"];
                    $errorCount++;
                }
            }
        }
    }
}

/**
 * 生成报告
 */
function generate_report() {
    global $filesToFix, $fixCount, $errorCount, $backupCount, $reportFile, $backupDir;
    
    $report = "# 命名空间一致性修复报告\n\n";
    $report .= "## 执行摘要\n\n";
    $report .= "- 执行时间: " . date('Y-m-d H:i:s') . "\n";
    $report .= "- 修复的文件数: $fixCount\n";
    $report .= "- 备份的文件数: $backupCount\n";
    $report .= "- 错误�? $errorCount\n";
    $report .= "- 备份目录: $backupDir\n\n";
    
    $report .= "## 修复的命名空间\n\n";
    $report .= "| 文件 | 旧命名空�?| 新命名空�?|\n";
    $report .= "|------|------------|------------|\n";
    
    foreach ($filesToFix as $file => $namespaces) {
        $report .= "| `$file` | `{$namespaces['old_namespace']}` | `{$namespaces['new_namespace']}` |\n";
    }
    
    $report .= "\n## 后续步骤\n\n";
    $report .= "1. 验证修复后的文件是否正常工作\n";
    $report .= "2. 运行接口实现检查工具，确认接口实现正确\n";
    $report .= "3. 运行PHP语法检查，确保没有引入新的错误\n";
    $report .= "4. 更新项目文档，明确命名空间规范\n\n";
    
    $report .= "## 预防措施\n\n";
    $report .= "1. 制定明确的命名空间规范文档\n";
    $report .= "2. 使用IDE功能自动检测接口实现问题\n";
    $report .= "3. 在CI/CD流程中加入命名空间一致性检查\n";
    $report .= "4. 实施严格的代码审查流程\n";
    
    file_put_contents($reportFile, $report];
    log_message("\n报告已生�? $reportFile"];
}

// 创建备份目录
if (!create_backup_dir()) {
    log_message("无法继续，退出程�?];
    exit(1];
}

// 修复文件命名空间
log_message("开始修复文件命名空�?.."];
foreach ($filesToFix as $file => $namespaces) {
    fix_file_namespace($file, $namespaces['old_namespace'],  $namespaces['new_namespace']];
}

// 修复命名空间引用
log_message("\n开始修复命名空间引�?.."];
foreach ($filesToFix as $file => $namespaces) {
    fix_namespace_references('ai-engines', $namespaces['old_namespace'],  $namespaces['new_namespace']];
    fix_namespace_references('apps', $namespaces['old_namespace'],  $namespaces['new_namespace']];
}

// 生成报告
generate_report(];

// 输出结果摘要
echo "\n=== 修复结果摘要 ===\n";
echo "修复的文件数: $fixCount\n";
echo "备份的文件数: $backupCount\n";
echo "错误�? $errorCount\n";
echo "备份目录: $backupDir\n";
echo "详细报告: $reportFile\n"; 
