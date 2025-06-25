<?php
/**
 * PHP语法错误修复工具
 * 
 * 此脚本用于修复常见的PHP语法错误:
 * 1. 移除UTF-8 BOM标记
 * 2. 修复行末多余的引号和分号 ('";)
 * 3. 修复不正确的PHP开头标记
 */

// 配置
$config = [
    'backup' => true,
    'backup_dir' => './backups/php_syntax_fix_' . date('Y-m-d_H-i-s'),
    'log_file' => './PHP_SYNTAX_FIX_LOG_' . date('Y-m-d_H-i-s') . '.md',
    'extensions' => ['php'],
    'exclude_dirs' => ['vendor', 'node_modules', '.git'],
    'fix_bom' => true,
    'fix_php_tags' => true,
    'fix_trailing_quotes_semicolons' => true,
];

// 日志函数
function log_message($message, $type = 'INFO') {
    global $config;
    $log_entry = "[" . date('Y-m-d H:i:s') . "] [$type] $message\n";
    echo $log_entry;
    file_put_contents($config['log_file'], $log_entry, FILE_APPEND);
}

// 初始化
if (!file_exists($config['log_file'])) {
    file_put_contents($config['log_file'], "# PHP语法错误修复日志\n\n");
}

// 如果需要备份，创建备份目录
if ($config['backup'] && !file_exists($config['backup_dir'])) {
    mkdir($config['backup_dir'], 0755, true);
    log_message("创建备份目录: {$config['backup_dir']}");
}

// 处理文件
function process_file($file_path) {
    global $config;
    
    // 检查文件是否存在
    if (!file_exists($file_path)) {
        log_message("文件不存在: $file_path", 'ERROR');
        return false;
    }
    
    // 读取文件内容
    $content = file_get_contents($file_path);
    if ($content === false) {
        log_message("无法读取文件: $file_path", 'ERROR');
        return false;
    }
    
    $original_content = $content;
    $modified = false;
    
    // 1. 移除UTF-8 BOM标记
    if ($config['fix_bom'] && substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
        log_message("移除BOM标记: $file_path", 'FIX');
        $modified = true;
    }
    
    // 2. 修复PHP开头标记
    if ($config['fix_php_tags']) {
        // 修复错误的PHP开头标记
        $patterns = [
            '/^<\?(?!php)/i', // 匹配 <? 但不是 <?php
            '/^<\?hp/i',      // 匹配 <?hp
            '/^<\?php;/i',    // 匹配 <?php;
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '<?php', $content);
                log_message("修复PHP标签: $file_path", 'FIX');
                $modified = true;
            }
        }
    }
    
    // 3. 修复行末多余的引号和分号
    if ($config['fix_trailing_quotes_semicolons']) {
        // 匹配行末的 '; 或 "; 模式
        $pattern = '/(["\']);\s*$/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, ',', $content);
            log_message("修复行末多余的引号和分号: $file_path", 'FIX');
            $modified = true;
        }
        
        // 修复数组定义中的问题
        $pattern = '/([\'"])\s*=>\s*([^,\s\n\r\]]+)([\'"]);\s*$/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '$1 => $2$3,', $content);
            log_message("修复数组定义中的问题: $file_path", 'FIX');
            $modified = true;
        }
    }
    
    // 如果内容已修改，则保存
    if ($modified) {
        // 如果需要备份，先创建备份
        if ($config['backup']) {
            $backup_path = $config['backup_dir'] . '/' . str_replace('/', '_', $file_path);
            $backup_dir = dirname($backup_path);
            if (!file_exists($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
            file_put_contents($backup_path, $original_content);
            log_message("创建备份: $backup_path");
        }
        
        // 保存修改后的内容
        if (file_put_contents($file_path, $content) !== false) {
            log_message("成功修复文件: $file_path", 'SUCCESS');
            return true;
        } else {
            log_message("无法写入文件: $file_path", 'ERROR');
            return false;
        }
    } else {
        log_message("文件无需修复: $file_path");
        return true;
    }
}

// 递归处理目录
function process_directory($dir) {
    global $config;
    
    if (!is_dir($dir)) {
        log_message("目录不存在: $dir", 'ERROR');
        return;
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        // 如果是目录，检查是否应该排除
        if (is_dir($path)) {
            if (in_array($item, $config['exclude_dirs'])) {
                log_message("跳过排除目录: $path");
                continue;
            }
            process_directory($path);
        }
        // 如果是文件，检查扩展名
        else if (is_file($path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, $config['extensions'])) {
                process_file($path);
            }
        }
    }
}

// 处理特定文件
function process_specific_file($file_path) {
    global $config;
    
    if (!file_exists($file_path)) {
        log_message("文件不存在: $file_path", 'ERROR');
        return;
    }
    
    $ext = pathinfo($file_path, PATHINFO_EXTENSION);
    if (in_array($ext, $config['extensions'])) {
        process_file($file_path);
    } else {
        log_message("跳过非PHP文件: $file_path");
    }
}

// 主函数
function main($args) {
    if (count($args) < 2) {
        echo "用法: php fix_php_syntax_errors.php [目录|文件路径]\n";
        return;
    }
    
    $target = $args[1];
    
    if (is_dir($target)) {
        log_message("开始处理目录: $target");
        process_directory($target);
    } else if (is_file($target)) {
        log_message("开始处理文件: $target");
        process_specific_file($target);
    } else {
        log_message("目标不存在: $target", 'ERROR');
    }
    
    log_message("处理完成");
}

// 运行脚本
main($argv);
