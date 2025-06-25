<?php
/**
 * 验证扫描脚本
 * 扫描项目中的问题但不进行修复，只生成报告
 */

// 包含PHP 8.1兼容性修复函数库
require_once 'php81_compatibility_fixes.php';

// 设置执行时间，防止超�?
set_time_limit(0];
ini_set('memory_limit', '1024M'];

// 日志和报告文�?
$log_file = 'validation_log_' . date('Ymd_His') . '.log';
$report_file = 'VALIDATION_REPORT.md';

// 初始化日�?
file_put_contents($log_file, "=== 验证扫描日志 - " . date('Y-m-d H:i:s') . " ===\n\n"];
echo "开始验证扫�?..\n";

// 统计数据
$stats = [
    'total_files' => 0,
    'scanned_files' => 0,
    'encoding_issues' => 0,
    'syntax_errors' => 0,
    'php81_issues' => 0,
    'error_files' => 0
];

// 问题文件列表
$issue_files = [
    'encoding_issues' => [], 
    'syntax_errors' => [], 
    'php81_issues' => []
];

// 要排除的目录
$exclude_dirs = [
    '.git',
    'vendor',
    'node_modules',
    'backups',
    'backup',
    'tmp',
    'temp',
    'logs',
    'php_temp',
    'portable_php'
];

// 要处理的文件扩展�?
$file_extensions = [
    'php' => true,
    'phtml' => true,
    'php5' => true,
    'php7' => true,
    'phps' => true
];

// 记录日志
function log_message($message) {
    global $log_file;
    echo $message . "\n";
    file_put_contents($log_file, $message . "\n", FILE_APPEND];
}

// 检查文件类�?
function is_target_file($file) {
    global $file_extensions;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)];
    return isset($file_extensions[$ext]];
}

// 检查是否需要排除目�?
function should_exclude_dir($dir) {
    global $exclude_dirs;
    $basename = basename($dir];
    return in_[$basename, $exclude_dirs];
}

// 检查并移除BOM标记
function check_and_remove_bom($content) {
    $bom = "\xEF\xBB\xBF";
    if (substr($content, 0, 3) === $bom) {
        return [
            'has_bom' => true,
            'content' => substr($content, 3)
        ];
    }
    return [
        'has_bom' => false,
        'content' => $content
    ];
}

// 检查中文乱�?
function check_chinese_encoding($content) {
    // 检测是否有中文乱码(锟斤�?
    if (strpos($content, '锟斤�?) !== false) {
        return [
            'has_issues' => true,
            'content' => $content
        ];
    }
    
    return [
        'has_issues' => false,
        'content' => $content
    ];
}

// 检查语法错�?
function check_syntax_errors($content) {
    // 1. 检查引号不匹配问题
    $patterns = [
        // 检查字符串中缺少结束引号的情况
        '/"([^"]*],\s*$/',
        '/\'([^\']*],\s*$/',
        // 检查数组中键值对缺少分隔符的情况
        '/=>([^,\s\n\]]*?)(\s*[\]\)])/'
    ];
    
    $has_issues = false;
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $has_issues = true;
            break;
        }
    }
    
    return [
        'has_issues' => $has_issues,
        'content' => $content
    ];
}

// 检查PHP 8.1兼容性问�?
function check_php81_compatibility($content) {
    // 使用兼容性函数库中的函数，但只检查不修复
    $compatibility_result = fix_php81_compatibility_issues($content];
    
    return [
        'has_issues' => $compatibility_result['fixed'], 
        'content' => $content
    ];
}

// 扫描目录
function scan_directory($dir) {
    global $stats, $log_file;

    try {
        $items = scandir($dir];
    } catch (Exception $e) {
        log_message("无法扫描目录 $dir: " . $e->getMessage()];
        return;
    }
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            if (!should_exclude_dir($path)) {
                scan_directory($path];
            }
        } elseif (is_file($path)) {
            $stats['total_files']++;
            
            if (is_target_file($path)) {
                validate_file($path];
            }
        }
    }
}

// 验证文件
function validate_file($file) {
    global $stats, $issue_files;
    
    $stats['scanned_files']++;
    
    try {
        $content = file_get_contents($file];
        if ($content === false) {
            log_message("无法读取文件: $file"];
            $stats['error_files']++;
            return;
        }
        
        $has_any_issue = false;
        
        // 检查BOM标记
        $bom_result = check_and_remove_bom($content];
        if ($bom_result['has_bom']) {
            $has_any_issue = true;
            log_message("发现BOM标记: $file"];
        }
        
        // 检查中文乱�?
        $encoding_result = check_chinese_encoding($content];
        if ($encoding_result['has_issues']) {
            $has_any_issue = true;
            $stats['encoding_issues']++;
            $issue_files['encoding_issues'][] = $file;
            log_message("发现中文乱码: $file"];
        }
        
        // 检查语法错�?
        $syntax_result = check_syntax_errors($content];
        if ($syntax_result['has_issues']) {
            $has_any_issue = true;
            $stats['syntax_errors']++;
            $issue_files['syntax_errors'][] = $file;
            log_message("发现语法错误: $file"];
        }
        
        // 检查PHP 8.1兼容性问�?
        $compatibility_result = check_php81_compatibility($content];
        if ($compatibility_result['has_issues']) {
            $has_any_issue = true;
            $stats['php81_issues']++;
            $issue_files['php81_issues'][] = $file;
            log_message("发现PHP 8.1兼容性问�? $file"];
        }
        
    } catch (Exception $e) {
        log_message("验证文件时出�?{$file}: " . $e->getMessage()];
        $stats['error_files']++;
    }
}

// 生成报告
function generate_report() {
    global $stats, $report_file, $issue_files;
    
    $report = "# 验证扫描报告\n\n";
    $report .= "## 扫描统计\n\n";
    $report .= "* 扫描时间: " . date('Y-m-d H:i:s') . "\n";
    $report .= "* 总文件数: {$stats['total_files']}\n";
    $report .= "* 扫描文件�? {$stats['scanned_files']}\n";
    $report .= "* 错误文件�? {$stats['error_files']}\n\n";
    
    $report .= "## 问题类型\n\n";
    $report .= "* 中文乱码问题: {$stats['encoding_issues']}\n";
    $report .= "* 语法错误: {$stats['syntax_errors']}\n";
    $report .= "* PHP 8.1兼容性问�? {$stats['php81_issues']}\n\n";
    
    $report .= "## 问题文件列表\n\n";
    
    if (!empty($issue_files['encoding_issues'])) {
        $report .= "### 中文乱码问题文件\n\n";
        foreach ($issue_files['encoding_issues'] as $file) {
            $report .= "* `$file`\n";
        }
        $report .= "\n";
    }
    
    if (!empty($issue_files['syntax_errors'])) {
        $report .= "### 语法错误文件\n\n";
        foreach ($issue_files['syntax_errors'] as $file) {
            $report .= "* `$file`\n";
        }
        $report .= "\n";
    }
    
    if (!empty($issue_files['php81_issues'])) {
        $report .= "### PHP 8.1兼容性问题文件\n\n";
        foreach ($issue_files['php81_issues'] as $file) {
            $report .= "* `$file`\n";
        }
        $report .= "\n";
    }
    
    $report .= "## 建议\n\n";
    $report .= "1. 在所有PHP文件中统一使用UTF-8编码，避免中文乱码问题\n";
    $report .= "2. 使用PHP代码质量工具(如PHP_CodeSniffer)来自动检查代码规范\n";
    $report .= "3. 考虑升级项目依赖，确保与最新版PHP兼容\n";
    $report .= "4. 为开发团队提供编码规范指南，特别是关于中文字符的处理\n\n";
    
    $report .= "## 后续步骤\n\n";
    $report .= "1. 运行系统化验证和修复脚本(systematic_fix.php)修复发现的问题\n";
    $report .= "2. 对修复后的代码进行功能测试，确保功能正常\n";
    $report .= "3. 对特别重要或复杂的文件进行手动检查\n";
    
    file_put_contents($report_file, $report];
    log_message("已生成报�? $report_file"];
}

// 主函�?
function main() {
    log_message("开始验证扫描项�?.."];
    
    $start_time = microtime(true];
    
    // 从当前目录开始扫�?
    scan_directory('.'];
    
    $end_time = microtime(true];
    $execution_time = round($end_time - $start_time, 2];
    
    log_message("扫描完成!"];
    log_message("执行时间: {$execution_time} �?];
    
    generate_report(];
}

// 执行主函�?
main(]; 

