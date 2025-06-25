<?php
/**
 * 系统化验证和修复脚本
 * 按阶段验证和修复项目中的问题，防止引入新问题
 */

// 包含PHP 8.1兼容性修复函数库
require_once 'php81_compatibility_fixes.php';

// 设置执行时间，防止超时
set_time_limit(0);
ini_set('memory_limit', '1024M');

// 日志和报告文件
$log_file = 'systematic_fix_log_' . date('Ymd_His') . '.log';
$report_file = 'SYSTEMATIC_FIX_REPORT.md';
$backup_dir = 'backups/systematic_fix_' . date('Ymd_His');

// 初始化日志
file_put_contents($log_file, "=== 系统化验证和修复日志 - " . date('Y-m-d H:i:s') . " ===\n\n");
echo "开始系统化验证和修复...\n";

// 创建备份目录
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// 统计数据
$stats = [
    'total_files' => 0,
    'scanned_files' => 0,
    'encoding_issues' => 0,
    'syntax_errors' => 0,
    'php81_issues' => 0,
    'fixed_files' => 0,
    'backup_files' => 0,
    'error_files' => 0
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

// 要处理的文件扩展名
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
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
}

// 备份文件
function backup_file($file) {
    global $backup_dir, $stats;
    
    $relative_path = str_replace('\\', '/', $file);
    $backup_path = $backup_dir . '/' . $relative_path;
    $backup_dir_path = dirname($backup_path);
    
    if (!file_exists($backup_dir_path)) {
        mkdir($backup_dir_path, 0777, true);
    }
    
    if (copy($file, $backup_path)) {
        $stats['backup_files']++;
        return true;
    }
    
    return false;
}

// 检查文件类型
function is_target_file($file) {
    global $file_extensions;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return isset($file_extensions[$ext]);
}

// 检查是否需要排除目录
function should_exclude_dir($dir) {
    global $exclude_dirs;
    $basename = basename($dir);
    return in_array($basename, $exclude_dirs);
}

// 检查并移除BOM标记
function check_and_remove_bom($content) {
    $bom = "\xEF\xBB\xBF";
    if (substr($content, 0, 3) === $bom) {
        return substr($content, 3);
    }
    return $content;
}

// 检查并修复中文乱码
function check_and_fix_chinese_encoding($content) {
    // 检测是否有中文乱码(锟斤拷)
    if (strpos($content, '锟斤拷') !== false) {
        // 获取映射表
        $replacements = get_chinese_encoding_fix_map();
        
        $fixed_content = $content;
        foreach ($replacements as $broken => $fixed) {
            $fixed_content = str_replace($broken, $fixed, $fixed_content);
        }
        
        if ($fixed_content !== $content) {
            return [
                'fixed' => true,
                'content' => $fixed_content
            ];
        }
    }
    
    return [
        'fixed' => false,
        'content' => $content
    ];
}

// 检查并修复语法错误
function check_and_fix_syntax_errors($content) {
    // 1. 修复引号不匹配问题
    $patterns = [
        // 修复字符串中缺少结束引号的情况
        '/"([^"]*),\s*$/' => '"$1",',
        '/\'([^\']*),\s*$/' => "'$1',",
        // 修复数组中键值对缺少分隔符的情况
        '/=>([^,\s\n\]]*?)(\s*[\]\)])/' => '=> $1,$2'
    ];
    
    $fixed = false;
    $fixed_content = $content;
    
    foreach ($patterns as $pattern => $replacement) {
        $new_content = preg_replace($pattern, $replacement, $fixed_content);
        if ($new_content !== $fixed_content) {
            $fixed = true;
            $fixed_content = $new_content;
        }
    }
    
    return [
        'fixed' => $fixed,
        'content' => $fixed_content
    ];
}

// 更新版本号和邮箱
function update_constants($content) {
    $fixed = false;
    $fixed_content = $content;
    
    // 更新邮箱后缀
    $email_pattern = '/"email"\s*=>\s*"([^@"]+)@[^"]+"/';
    $email_replacement = '"email" => "$1@gxggm.com"';
    
    $new_content = preg_replace($email_pattern, $email_replacement, $fixed_content);
    if ($new_content !== $fixed_content) {
        $fixed = true;
        $fixed_content = $new_content;
    }
    
    // 更新版本号
    $version_pattern = '/"version"\s*=>\s*"(\d+\.\d+\.\d+)"/';
    $version_replacement = '"version" => "6.0.0"';
    
    $new_content = preg_replace($version_pattern, $version_replacement, $fixed_content);
    if ($new_content !== $fixed_content) {
        $fixed = true;
        $fixed_content = $new_content;
    }
    
    return [
        'fixed' => $fixed,
        'content' => $fixed_content
    ];
}

// 扫描目录
function scan_directory($dir) {
    global $stats, $log_file;

    try {
        $items = scandir($dir);
    } catch (Exception $e) {
        log_message("无法扫描目录 $dir: " . $e->getMessage());
        return;
    }
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            if (!should_exclude_dir($path)) {
                scan_directory($path);
            }
        } elseif (is_file($path)) {
            $stats['total_files']++;
            
            if (is_target_file($path)) {
                process_file($path);
            }
        }
    }
}

// 处理文件
function process_file($file) {
    global $stats;
    
    $stats['scanned_files']++;
    
    try {
        $content = file_get_contents($file);
        if ($content === false) {
            log_message("无法读取文件: $file");
            $stats['error_files']++;
            return;
        }
        
        $original_content = $content;
        $changed = false;
        
        // 第1阶段: 移除BOM标记
        $content = check_and_remove_bom($content);
        if ($content !== $original_content) {
            $changed = true;
            log_message("移除BOM标记: $file");
        }
        
        // 第2阶段: 修复中文乱码
        $encoding_result = check_and_fix_chinese_encoding($content);
        if ($encoding_result['fixed']) {
            $content = $encoding_result['content'];
            $changed = true;
            $stats['encoding_issues']++;
            log_message("修复中文乱码: $file");
        }
        
        // 第3阶段: 修复语法错误
        $syntax_result = check_and_fix_syntax_errors($content);
        if ($syntax_result['fixed']) {
            $content = $syntax_result['content'];
            $changed = true;
            $stats['syntax_errors']++;
            log_message("修复语法错误: $file");
        }
        
        // 第4阶段: 更新常量值
        $constants_result = update_constants($content);
        if ($constants_result['fixed']) {
            $content = $constants_result['content'];
            $changed = true;
            log_message("更新常量值: $file");
        }
        
        // 第5阶段: PHP 8.1 兼容性修复
        $compatibility_result = fix_php81_compatibility_issues($content);
        if ($compatibility_result['fixed']) {
            $content = $compatibility_result['content'];
            $changed = true;
            $stats['php81_issues']++;
            log_message("修复PHP 8.1兼容性问题: $file");
        }
        
        // 如果有修改，备份并保存文件
        if ($changed) {
            // 备份原始文件
            if (backup_file($file)) {
                // 保存修改后的文件
                if (file_put_contents($file, $content) !== false) {
                    $stats['fixed_files']++;
                    log_message("成功修复文件: $file");
                } else {
                    log_message("无法写入文件: $file");
                    $stats['error_files']++;
                }
            } else {
                log_message("无法备份文件: $file");
                $stats['error_files']++;
            }
        }
    } catch (Exception $e) {
        log_message("处理文件时出错 {$file}: " . $e->getMessage());
        $stats['error_files']++;
    }
}

// 生成报告
function generate_report() {
    global $stats, $report_file, $backup_dir;
    
    $report = "# 系统化验证与修复报告\n\n";
    $report .= "## 扫描统计\n\n";
    $report .= "* 扫描时间: " . date('Y-m-d H:i:s') . "\n";
    $report .= "* 总文件数: {$stats['total_files']}\n";
    $report .= "* 扫描文件数: {$stats['scanned_files']}\n";
    $report .= "* 修复文件数: {$stats['fixed_files']}\n";
    $report .= "* 备份文件数: {$stats['backup_files']}\n";
    $report .= "* 错误文件数: {$stats['error_files']}\n\n";
    
    $report .= "## 问题类型\n\n";
    $report .= "* 中文乱码问题: {$stats['encoding_issues']}\n";
    $report .= "* 语法错误: {$stats['syntax_errors']}\n";
    $report .= "* PHP 8.1兼容性问题: {$stats['php81_issues']}\n\n";
    
    $report .= "## 修复方法\n\n";
    $report .= "本次修复采用系统化分阶段方法，每个文件分别经过以下阶段的检查与修复：\n\n";
    $report .= "1. **BOM标记移除** - 移除文件开头的UTF-8 BOM标记\n";
    $report .= "2. **中文乱码修复** - 识别并修复常见的中文乱码(锟斤拷)问题\n";
    $report .= "3. **语法错误修复** - 修复引号不匹配、数组语法等常见问题\n";
    $report .= "4. **常量值更新** - 统一邮箱后缀为@gxggm.com、版本号为6.0.0\n";
    $report .= "5. **PHP 8.1兼容性修复** - 修复PHP 8.1中字符串作为数组索引缺少引号的问题\n\n";
    
    $report .= "## 备份信息\n\n";
    $report .= "所有修改的文件都已备份到以下位置：\n";
    $report .= "`$backup_dir`\n\n";
    
    $report .= "## 建议\n\n";
    $report .= "1. 在所有PHP文件中统一使用UTF-8编码，避免中文乱码问题\n";
    $report .= "2. 使用PHP代码质量工具(如PHP_CodeSniffer)来自动检查代码规范\n";
    $report .= "3. 考虑升级项目依赖，确保与最新版PHP兼容\n";
    $report .= "4. 为开发团队提供编码规范指南，特别是关于中文字符的处理\n\n";
    
    $report .= "## 后续步骤\n\n";
    $report .= "1. 对修复后的代码进行功能测试，确保功能正常\n";
    $report .= "2. 对特别重要或复杂的文件进行手动检查\n";
    $report .= "3. 设置自动化测试流程，避免类似问题再次发生\n";
    
    file_put_contents($report_file, $report);
    log_message("已生成报告: $report_file");
}

// 主函数
function main() {
    log_message("开始系统化验证和修复项目...");
    
    $start_time = microtime(true);
    
    // 从当前目录开始扫描
    scan_directory('.');
    
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    log_message("扫描和修复完成!");
    log_message("执行时间: {$execution_time} 秒");
    
    generate_report();
}

// 执行主函数
main();
