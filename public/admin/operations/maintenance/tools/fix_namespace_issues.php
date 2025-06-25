<?php
/**
 * 命名空间修复工具
 * 
 * 此脚本用于修复项目中的命名空间不一致问题，特别是接口和实现类之间的命名空间不一�?
 */

// 设置基础配置
$projectRoot = __DIR__;
$fixCount = 0;
$fileCount = 0;
$errorCount = 0;
$backupMode = true;

// 日志文件
$logFile = "namespace_fix_" . date("Ymd_His") . ".log";
$reportFile = "NAMESPACE_FIX_REPORT_" . date("Ymd_His") . ".md";

// 命名空间映射配置
$namespaceMapping = [
    // 源命名空�?=> 目标命名空间
    'AlingAi\AI\Engines\NLP' => 'AlingAi\Engines\NLP',
    // 添加其他需要修复的命名空间映射
];

// 特定文件的命名空间映�?
$fileNamespaceMapping = [
    // 文件路径 => 目标命名空间
    'ai-engines/nlp/ChineseTokenizer.php' => 'AlingAi\Engines\NLP',
    'ai-engines/nlp/EnglishTokenizer.php' => 'AlingAi\Engines\NLP',
    'ai-engines/nlp/POSTagger.php' => 'AlingAi\Engines\NLP',
    // 添加其他需要修复的文件
];

// 排除目录
$excludeDirs = [
    'vendor', 
    'backups', 
    'backup', 
    'tmp',
    'logs',
    'tests/fixtures'
];

echo "=== AlingAi Pro 命名空间修复工具 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";

// 初始化日�?
file_put_contents($logFile, "=== 命名空间修复日志 - " . date("Y-m-d H:i:s") . " ===\n\n"];

/**
 * 写入日志
 */
function log_message($message) {
    global $logFile;
    echo $message . "\n";
    file_put_contents($logFile, $message . "\n", FILE_APPEND];
}

/**
 * 扫描目录查找PHP文件
 */
function findPhpFiles($dir, $excludeDirs = []) {
    $files = [];
    if (is_dir($dir)) {
        $scan = scandir($dir];
        foreach ($scan as $file) {
            if ($file != "." && $file != "..") {
                $path = "$dir/$file";
                
                // 检查是否在排除目录�?
                $excluded = false;
                foreach ($excludeDirs as $excludeDir) {
                    if (strpos($path, "/$excludeDir/") !== false || basename($dir) == $excludeDir) {
                        $excluded = true;
                        break;
                    }
                }
                
                if ($excluded) {
                    continue;
                }
                
                if (is_dir($path)) {
                    $files = array_merge($files, findPhpFiles($path, $excludeDirs)];
                } else if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
                    $files[] = $path;
                }
            }
        }
    }
    return $files;
}

/**
 * 获取文件中的命名空间
 */
function getFileNamespace($file) {
    $content = file_get_contents($file];
    if (preg_match('/namespace\s+([^;]+];/', $content, $matches)) {
        return trim($matches[1]];
    }
    return null;
}

/**
 * 修复文件的命名空�?
 */
function fixFileNamespace($file, $targetNamespace) {
    global $backupMode, $fixCount;
    
    $content = file_get_contents($file];
    $originalContent = $content;
    
    // 检查当前命名空�?
    $currentNamespace = getFileNamespace($file];
    if (!$currentNamespace) {
        log_message("  ⚠️ 文件没有命名空间声明: $file"];
        return false;
    }
    
    if ($currentNamespace === $targetNamespace) {
        log_message("  �?命名空间已经正确: $file"];
        return false;
    }
    
    // 创建备份
    if ($backupMode) {
        $backupFile = $file . '.namespace.bak.' . date('YmdHis'];
        if (!copy($file, $backupFile)) {
            log_message("  ⚠️ 无法创建备份: $backupFile"];
            return false;
        }
        log_message("  已创建备�? $backupFile"];
    }
    
    // 替换命名空间
    $newContent = preg_replace('/namespace\s+' . preg_quote($currentNamespace, '/') . ';/', "namespace $targetNamespace;", $content];
    
    if ($newContent !== $content) {
        if (file_put_contents($file, $newContent)) {
            log_message("  �?已修复命名空�? $file"];
            log_message("    - �? $currentNamespace"];
            log_message("    - �? $targetNamespace"];
            $fixCount++;
            return true;
        } else {
            log_message("  �?无法写入文件: $file"];
            return false;
        }
    } else {
        log_message("  ⚠️ 无法替换命名空间: $file"];
        return false;
    }
}

/**
 * 修复命名空间引用
 */
function fixNamespaceReferences($file, $sourceNamespace, $targetNamespace) {
    global $backupMode, $fixCount;
    
    $content = file_get_contents($file];
    $originalContent = $content;
    
    // 创建备份
    if ($backupMode && $content !== $originalContent) {
        $backupFile = $file . '.ref.bak.' . date('YmdHis'];
        if (!copy($file, $backupFile)) {
            log_message("  ⚠️ 无法创建备份: $backupFile"];
            return false;
        }
        log_message("  已创建备�? $backupFile"];
    }
    
    // 替换use语句
    $newContent = preg_replace('/use\s+' . preg_quote($sourceNamespace, '/') . '\\\\([^;]+];/', "use $targetNamespace\\\\$1;", $content];
    
    // 替换完全限定类名引用
    $newContent = preg_replace('/' . preg_quote($sourceNamespace, '/') . '\\\\([a-zA-Z0-9_]+)/', "$targetNamespace\\\\$1", $newContent];
    
    if ($newContent !== $content) {
        if (file_put_contents($file, $newContent)) {
            log_message("  �?已修复命名空间引�? $file"];
            $fixCount++;
            return true;
        } else {
            log_message("  �?无法写入文件: $file"];
            return false;
        }
    }
    
    return false;
}

/**
 * 主函数：修复命名空间
 */
function fixNamespaces() {
    global $projectRoot, $excludeDirs, $namespaceMapping, $fileNamespaceMapping, $fileCount, $errorCount;
    
    // 查找所有PHP文件
    log_message("扫描项目中的PHP文件..."];
    $files = findPhpFiles($projectRoot, $excludeDirs];
    log_message("找到 " . count($files) . " 个PHP文件"];
    
    // 首先修复特定文件的命名空�?
    log_message("\n修复特定文件的命名空�?.."];
    foreach ($fileNamespaceMapping as $filePath => $targetNamespace) {
        $fullPath = $projectRoot . '/' . $filePath;
        if (file_exists($fullPath)) {
            $fileCount++;
            log_message("处理文件: $fullPath"];
            if (!fixFileNamespace($fullPath, $targetNamespace)) {
                $errorCount++;
            }
        } else {
            log_message("⚠️ 文件不存�? $fullPath"];
        }
    }
    
    // 然后修复所有文件中的命名空间引�?
    log_message("\n修复命名空间引用..."];
    foreach ($files as $file) {
        $fileCount++;
        log_message("检查文件中的命名空间引�? $file"];
        
        foreach ($namespaceMapping as $sourceNamespace => $targetNamespace) {
            fixNamespaceReferences($file, $sourceNamespace, $targetNamespace];
        }
    }
}

/**
 * 生成报告
 */
function generateReport() {
    global $fixCount, $fileCount, $errorCount, $reportFile, $namespaceMapping, $fileNamespaceMapping;
    
    $report = "# 命名空间修复报告\n\n";
    $report .= "## 摘要\n\n";
    $report .= "- 执行时间: " . date('Y-m-d H:i:s') . "\n";
    $report .= "- 处理的文�? $fileCount\n";
    $report .= "- 修复的命名空�? $fixCount\n";
    $report .= "- 错误: $errorCount\n\n";
    
    $report .= "## 命名空间映射\n\n";
    $report .= "| 源命名空�?| 目标命名空间 |\n";
    $report .= "|------------|------------|\n";
    
    foreach ($namespaceMapping as $source => $target) {
        $report .= "| `$source` | `$target` |\n";
    }
    
    $report .= "\n## 特定文件命名空间修复\n\n";
    $report .= "| 文件 | 目标命名空间 |\n";
    $report .= "|------|------------|\n";
    
    foreach ($fileNamespaceMapping as $file => $namespace) {
        $report .= "| `$file` | `$namespace` |\n";
    }
    
    $report .= "\n## 建议\n\n";
    $report .= "1. 检查修复后的文件，确保功能正常\n";
    $report .= "2. 运行PHP语法检查，确保没有引入新的错误\n";
    $report .= "3. 统一项目中的命名空间规范，避免未来出现类似问题\n";
    $report .= "4. 考虑使用自动加载器，减少命名空间相关的问题\n";
    
    file_put_contents($reportFile, $report];
    log_message("\n报告已生�? $reportFile"];
}

// 执行命名空间修复
fixNamespaces(];

// 生成报告
generateReport(];

// 输出结果摘要
echo "\n=== 修复结果摘要 ===\n";
echo "处理的文�? $fileCount\n";
echo "修复的命名空�? $fixCount\n";
echo "错误: $errorCount\n";
echo "详细报告: $reportFile\n"; 
