<?php
/**
 * 扫描项目中的锟斤拷乱码文本
 */

// 设置执行时间，防止超时
set_time_limit(0);
ini_set("memory_limit", "1024M");

// 日志文件
$log_file = "garbled_text_scan_" . date("Ymd_His") . ".txt";
$report_file = "GARBLED_TEXT_SCAN_REPORT.md";

// 要排除的目录
$exclude_dirs = [
    ".git",
    "vendor",
    "node_modules",
    "backups",
    "backup",
    "tmp",
    "temp",
    "logs",
];

// 要检查的文件扩展名
$extensions = [
    "php",
    "phtml",
    "php5",
    "php7",
    "phps",
];

// 初始化日志
file_put_contents($log_file, "=== 乱码文本扫描日志 - " . date("Y-m-d H:i:s") . " ===\n\n");
echo "开始扫描项目中的乱码文本...\n";

// 扫描文件统计
$stats = [
    "total_files" => 0,
    "scanned_files" => 0,
    "garbled_files" => 0,
];

// 乱码文件列表
$garbled_files = [];

// 检查是否为有效的PHP文件
function is_valid_php_file($file) {
    global $extensions;
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    return in_array(strtolower($ext), $extensions);
}

// 检查是否需要排除该目录
function should_exclude_dir($dir) {
    global $exclude_dirs;
    $base_dir = basename($dir);
    return in_array($base_dir, $exclude_dirs);
}

// 扫描目录
function scan_directory($dir) {
    global $stats, $garbled_files, $log_file;
    
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            if (!should_exclude_dir($path)) {
                scan_directory($path);
            }
        } elseif (is_file($path) && is_valid_php_file($path)) {
            $stats["total_files"]++;
            
            if ($stats["total_files"] % 100 === 0) {
                echo "已扫描 {$stats["total_files"]} 个文件...\n";
            }
            
            check_file($path);
        }
    }
}

// 检查文件中的乱码
function check_file($file) {
    global $stats, $garbled_files, $log_file;
    
    $stats["scanned_files"]++;
    
    $content = file_get_contents($file);
    if ($content === false) {
        file_put_contents($log_file, "无法读取文件: $file\n", FILE_APPEND);
        return;
    }
    
    // 检查是否含有乱码文本
    if (preg_match('/锟斤拷/', $content)) {
        $stats["garbled_files"]++;
        $garbled_files[] = $file;
        
        // 提取包含乱码的行
        $lines = explode("\n", $content);
        $garbled_lines = [];
        
        foreach ($lines as $i => $line) {
            if (preg_match('/锟斤拷/', $line)) {
                $line_num = $i + 1;
                $garbled_lines[] = "行 $line_num: " . trim($line);
            }
        }
        
        // 记录到日志
        $log_message = "发现乱码文件: $file\n";
        $log_message .= "乱码行:\n" . implode("\n", $garbled_lines) . "\n\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        
        echo "发现乱码文件: $file\n";
    }
}

// 生成报告
function generate_report() {
    global $stats, $garbled_files, $report_file;
    
    $report = "# 乱码文本扫描报告\n\n";
    $report .= "## 扫描统计\n\n";
    $report .= "- 总文件数: {$stats["total_files"]}\n";
    $report .= "- 扫描文件数: {$stats["scanned_files"]}\n";
    $report .= "- 包含乱码的文件数: {$stats["garbled_files"]}\n\n";
    
    if ($stats["garbled_files"] > 0) {
        $report .= "## 乱码文件列表\n\n";
        foreach ($garbled_files as $file) {
            $report .= "- $file\n";
        }
    } else {
        $report .= "没有发现乱码文件，太好了！\n";
    }
    
    $report .= "\n## 扫描时间\n\n";
    $report .= date("Y-m-d H:i:s") . "\n";
    
    file_put_contents($report_file, $report);
    echo "\n报告已生成: $report_file\n";
}

// 主函数
function main() {
    global $stats, $log_file;
    
    $start_time = microtime(true);
    
    // 从当前目录开始扫描
    scan_directory(".");
    
    $end_time = microtime(true);
    $duration = round($end_time - $start_time, 2);
    
    // 生成扫描报告
    generate_report();
    
    $summary = "\n扫描完成!\n";
    $summary .= "总文件数: {$stats["total_files"]}\n";
    $summary .= "包含乱码的文件数: {$stats["garbled_files"]}\n";
    $summary .= "耗时: {$duration} 秒\n";
    
    echo $summary;
    file_put_contents($log_file, $summary, FILE_APPEND);
}

// 执行主函数
main(); 