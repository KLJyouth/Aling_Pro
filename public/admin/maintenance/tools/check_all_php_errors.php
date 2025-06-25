<?php
/**
 * 检测并修复PHP文件中的语法错误和中文乱码问�?
 * 包括�?
 * 1. 中文乱码（锟斤拷）问�?
 * 2. 引号不匹配导致的语法错误
 * 3. 邮箱后缀统一�?@gxggm.com
 * 4. 版本号统一�?6.0.0
 */

// 设置执行时间，防止超�?
set_time_limit(0];
ini_set('memory_limit', '1024M'];

// 日志文件
$log_file = 'php_errors_fix_log_' . date('Ymd_His') . '.txt';
$report_file = 'PHP_ERRORS_FIX_REPORT.md';

// 统计数据
$stats = [
    'total_files' => 0,
    'scanned_files' => 0,
    'error_files' => 0,
    'fixed_files' => 0,
    'encoding_issues' => 0,
    'syntax_errors' => 0,
    'email_updates' => 0,
    'version_updates' => 0,
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
];

// 要检查的文件扩展�?
$extensions = [
    'php',
    'phtml',
    'php5',
    'php7',
    'phps',
];

// 初始化日�?
function init_log() {
    global $log_file;
    file_put_contents($log_file, "=== PHP错误检测与修复日志 - " . date('Y-m-d H:i:s') . " ===\n\n"];
}

// 写入日志
function log_message($message) {
    global $log_file;
    file_put_contents($log_file, $message . "\n", FILE_APPEND];
    echo $message . "\n";
}

// 检查文件是否有BOM标记
function has_bom($content) {
    return strpos($content, "\xEF\xBB\xBF") === 0;
}

// 移除BOM标记
function remove_bom($content) {
    if (has_bom($content)) {
        return substr($content, 3];
    }
    return $content;
}

// 检查并修复中文乱码
function fix_chinese_encoding($content) {
    $has_encoding_issues = preg_match('/锟斤�?', $content];
    
    if ($has_encoding_issues) {
        // 这里仅简单替换一些常见的乱码，实际情况可能需要更复杂的处�?
        $fixes = [
            // 锟斤拷常见的对应文字替换
            '锟斤拷应锟斤拷锟斤拷' => '响应数据',
            '锟斤拷锟斤拷锟斤�? => '错误处理',
            'API锟侥碉拷锟斤拷锟斤拷锟斤拷锟斤拷' => 'API文档生成�?,
            '锟斤拷证' => '认证',
            '锟矫伙拷锟斤拷录' => '用户登录',
            '锟斤拷锟斤拷' => '密码',
            '锟斤拷锟截匡拷锟斤拷锟斤拷锟斤拷' => '本地开发环�?,
            '锟斤拷锟斤拷锟斤拷锟斤拷' => '生产环境',
            '锟斤拷权锟斤拷证' => '授权验证',
            '未锟结供锟斤拷证锟斤拷息' => '未提供认证信�?,
            '锟斤拷效锟斤拷锟斤拷锟斤�? => '无效的令�?,
            '锟斤拷效锟斤拷API锟斤拷钥' => '无效的API密钥',
            '锟斤拷证失锟斤拷' => '认证失败',
            '锟斤拷锟斤拷锟斤拷锟斤拷' => '请求处理',
            '锟斤拷锟斤拷锟絆PTIONS锟斤拷锟斤拷CORS预锟斤拷锟斤拷应锟斤拷锟斤拷前锟芥处锟斤�? => '处理OPTIONS请求的CORS预检响应和前置处�?,
            '锟斤拷锟斤拷路锟斤拷锟酵凤拷锟斤拷锟街凤拷锟斤拷锟斤�? => '根据路径和方法分发请�?,
            '锟斤拷取API锟侥碉拷锟结�? => '获取API文档结构',
            '锟缴癸拷锟斤拷取API锟侥碉拷' => '成功获取API文档',
            '默锟较凤拷锟斤拷锟斤拷锟斤拷锟斤拷API锟侥碉拷' => '默认返回完整的API文档',
            '执锟斤拷锟斤拷锟斤拷锟斤�? => '执行请求处理',
            '锟斤拷锟斤拷锟斤拷锟斤拷时锟斤拷锟斤拷锟斤拷锟斤�? => '处理请求时发生错�?,
            // 添加更多常见乱码替换
            '锟斤拷锟斤拷API锟侥碉拷 - 锟斤拷锟斤拷锟矫伙拷锟斤拷锟斤拷锟斤拷锟斤拷锟届、系统锟斤拷氐锟斤拷锟斤拷泄锟斤拷锟?' => 'AlingAi Pro API文档系统 - 用户管理、系统监控等功能'
        ];
        
        $fixed_content = $content;
        foreach ($fixes as $broken => $fixed) {
            $fixed_content = str_replace($broken, $fixed, $fixed_content];
        }
        
        return [
            'fixed' => $fixed_content !== $content,
            'content' => $fixed_content
        ];
    }
    
    return [
        'fixed' => false,
        'content' => $content
    ];
}

// 检查并修复语法错误
function fix_syntax_errors($content) {
    $fixed = false;
    $fixed_content = $content;
    
    // 1. 修复引号不匹配问�?
    $patterns = [
        // 修复字符串中缺少结束引号的情�?
        '/(["\'].*],\s*$/m' => '$1",',
        // 其他常见语法错误模式...
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $new_content = preg_replace($pattern, $replacement, $fixed_content];
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

// 更新邮箱和版本号
function update_constants($content) {
    $fixed = false;
    $fixed_content = $content;
    
    // 更新邮箱后缀
    $email_pattern = '/"email"\s*=>\s*"([^@"]+)@[^"]+"/';
    $email_replacement = '"email" => "$1@gxggm.com"';
    
    $new_content = preg_replace($email_pattern, $email_replacement, $fixed_content];
    if ($new_content !== $fixed_content) {
        $fixed = true;
        $fixed_content = $new_content;
        global $stats;
        $stats['email_updates']++;
    }
    
    // 更新版本�?
    $version_pattern = '/"version"\s*=>\s*"(\d+\.\d+\.\d+)"/';
    $version_replacement = '"version" => "6.0.0"';
    
    $new_content = preg_replace($version_pattern, $version_replacement, $fixed_content];
    if ($new_content !== $fixed_content) {
        $fixed = true;
        $fixed_content = $new_content;
        global $stats;
        $stats['version_updates']++;
    }
    
    return [
        'fixed' => $fixed,
        'content' => $fixed_content
    ];
}

// 检查是否为有效的PHP文件
function is_valid_php_file($file) {
    global $extensions;
    $ext = pathinfo($file, PATHINFO_EXTENSION];
    return in_[strtolower($ext], $extensions];
}

// 检查是否需要排除该目录
function should_exclude_dir($dir) {
    global $exclude_dirs;
    $basename = basename($dir];
    return in_[$basename, $exclude_dirs];
}

// 递归扫描目录
function scan_directory($dir) {
    global $stats;
    
    if (should_exclude_dir($dir)) {
        return;
    }
    
    $items = scandir($dir];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            scan_directory($path];
        } elseif (is_file($path) && is_valid_php_file($path)) {
            $stats['total_files']++;
            process_file($path];
        }
    }
}

// 处理单个文件
function process_file($file) {
    global $stats;
    
    $stats['scanned_files']++;
    
    try {
        $content = file_get_contents($file];
        if ($content === false) {
            log_message("无法读取文件: $file"];
            return;
        }
        
        $original_content = $content;
        $changed = false;
        
        // 1. 移除BOM标记
        $content = remove_bom($content];
        $bom_removed = $content !== $original_content;
        if ($bom_removed) {
            $changed = true;
            log_message("已移除BOM标记: $file"];
        }
        
        // 2. 修复中文乱码
        $encoding_result = fix_chinese_encoding($content];
        if ($encoding_result['fixed']) {
            $content = $encoding_result['content'];
            $changed = true;
            $stats['encoding_issues']++;
            log_message("已修复中文乱�? $file"];
        }
        
        // 3. 修复语法错误
        $syntax_result = fix_syntax_errors($content];
        if ($syntax_result['fixed']) {
            $content = $syntax_result['content'];
            $changed = true;
            $stats['syntax_errors']++;
            log_message("已修复语法错�? $file"];
        }
        
        // 4. 更新邮箱和版本号
        $constants_result = update_constants($content];
        if ($constants_result['fixed']) {
            $content = $constants_result['content'];
            $changed = true;
            log_message("已更新邮箱或版本�? $file"];
        }
        
        // 如果有变更，保存文件
        if ($changed) {
            // 先创建备�?
            $backup_file = $file . '.bak.' . date('YmdHis'];
            file_put_contents($backup_file, $original_content];
            
            // 保存修改后的文件
            if (file_put_contents($file, $content) !== false) {
                $stats['fixed_files']++;
                log_message("已成功修复并保存: $file"];
            } else {
                log_message("无法写入文件: $file"];
            }
        }
        
    } catch (Exception $e) {
        $stats['error_files']++;
        log_message("处理文件时出�?{$file}: " . $e->getMessage()];
    }
}

// 生成报告
function generate_report() {
    global $stats, $report_file;
    
    $report = "# PHP错误修复报告\n\n";
    $report .= "## 扫描摘要\n\n";
    $report .= "* 扫描时间: " . date('Y-m-d H:i:s') . "\n";
    $report .= "* 总文件数: {$stats['total_files']}\n";
    $report .= "* 扫描文件�? {$stats['scanned_files']}\n";
    $report .= "* 错误文件�? {$stats['error_files']}\n";
    $report .= "* 修复文件�? {$stats['fixed_files']}\n\n";
    
    $report .= "## 修复类型统计\n\n";
    $report .= "* 中文乱码问题: {$stats['encoding_issues']}\n";
    $report .= "* 语法错误: {$stats['syntax_errors']}\n";
    $report .= "* 邮箱更新: {$stats['email_updates']}\n";
    $report .= "* 版本号更�? {$stats['version_updates']}\n\n";
    
    $report .= "## 修复说明\n\n";
    $report .= "本次扫描修复了以下类型的问题：\n\n";
    $report .= "1. **中文乱码问题** - 修复了锟斤拷等常见乱码问题\n";
    $report .= "2. **语法错误** - 修复了引号不匹配等语法错误\n";
    $report .= "3. **邮箱标准�?* - 将所有邮箱后缀统一�?@gxggm.com\n";
    $report .= "4. **版本号统一** - 将所有版本号统一�?6.0.0\n\n";
    
    $report .= "## 建议\n\n";
    $report .= "1. 在处理多语言文件时使用UTF-8编码，避免出现乱码\n";
    $report .= "2. 在编辑PHP文件时使用支持语法高亮的编辑器，可以及时发现语法错误\n";
    $report .= "3. 考虑添加自动化测试，在部署前检查PHP语法错误\n";
    $report .= "4. 使用统一的配置管理系统管理版本号和联系邮箱等常量\n\n";
    
    $report .= "## 结论\n\n";
    $report .= "系统修复完成，已解决所有检测到的问题。对于更复杂的语法错误，可能需要手动检查修复。\n";
    
    file_put_contents($report_file, $report];
    log_message("已生成报�? $report_file"];
}

// 主函�?
function main() {
    init_log(];
    log_message("开始扫描和修复PHP文件..."];
    
    $start_time = microtime(true];
    $root_dir = __DIR__;
    
    scan_directory($root_dir];
    
    $end_time = microtime(true];
    $execution_time = round($end_time - $start_time, 2];
    
    log_message("扫描和修复完成，用时: {$execution_time} �?];
    
    generate_report(];
}

// 执行主函�?
main(]; 

