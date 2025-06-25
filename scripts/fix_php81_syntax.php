<?php
/**
 * PHP 8.1语法修复工具
 * 
 * 此脚本用于批量修复PHP 8.1语法兼容性问题
 */

declare(strict_types=1);

// 设置脚本运行环境
ini_set('display_errors', '1');
error_reporting(E_ALL);

// 项目根目录
$rootDir = dirname(__DIR__);

// 需要扫描的目录
$directories = [
    $rootDir . '/src',
    $rootDir . '/public',
    $rootDir . '/ai-engines',
    $rootDir . '/apps',
    $rootDir . '/scripts',
];

// 需要忽略的目录
$ignoreDirs = [
    'vendor',
    'node_modules',
    'storage/logs',
    'backups',
];

// 统计信息
$stats = [
    'files_scanned' => 0,
    'files_modified' => 0,
    'total_fixes' => 0,
    'fixes_by_type' => [
        'strict_types' => 0,
        'array_merge' => 0,
        'function_call' => 0,
        'is_array' => 0,
        'string_encoding' => 0,
        'quotes' => 0,
        'other' => 0,
    ],
];

/**
 * 修复PHP 8.1语法问题
 */
function fixPhp81Syntax(string $file, array &$stats): bool {
    // 读取文件内容
    $content = file_get_contents($file);
    if ($content === false) {
        return false;
    }
    
    $originalContent = $content;
    
    // 修复常见语法问题
    
    // 1. 修复declare(strict_types=1]
    $pattern = '/declare\s*\(\s*strict_types\s*=\s*1\s*\]/';
    $replacement = 'declare(strict_types=1)';
    $content = preg_replace($pattern, $replacement, $content, -1, $count);
    $stats['fixes_by_type']['strict_types'] += $count;
    
    // 2. 修复数组合并方法 array_merge($array, $array2]
    $pattern = '/array_merge\s*\(\s*([^,\)]+)\s*,\s*([^\)]+)\]/';
    $replacement = 'array_merge($1, $2)';
    $content = preg_replace($pattern, $replacement, $content, -1, $count);
    $stats['fixes_by_type']['array_merge'] += $count;
    
    // 3. 修复其他函数调用中的方括号结束 function_name($param]
    $pattern = '/([a-zA-Z0-9_]+)\s*\(\s*([^\)\]]+)\s*\]/';
    $replacement = '$1($2)';
    $content = preg_replace($pattern, $replacement, $content, -1, $count);
    $stats['fixes_by_type']['function_call'] += $count;
    
    // 4. 修复is_[$var) 变为 is_array($var)
    $pattern = '/is_\s*\[\s*([^\)]+)\s*\)/';
    $replacement = 'is_array($1)';
    $content = preg_replace($pattern, $replacement, $content, -1, $count);
    $stats['fixes_by_type']['is_array'] += $count;
    
    // 5. 修复中文字符编码问题（替换特殊字符）
    // 常见乱码对照表
    $encodingFixes = [
        '?' => '器',
        '?' => '模',
        '?' => '型',
        '?' => '识',
        '?' => '别',
        '?' => '码',
        '?' => '算',
        '?' => '法',
        '?' => '入',
        '?' => '分',
        '?' => '用',
        '?' => '空',
        '?' => '是',
        '?' => '能',
        '?' => '功',
        '?' => '提',
        '?' => '供',
        '?' => '据',
        '?' => '支',
        '?' => '持',
        '?' => '性',
        '?' => '解',
        '?' => '析',
        '?' => '检',
        '?' => '测',
        '?' => '图',
        '?' => '像',
        '?' => '对',
        '?' => '比',
        '?' => '生',
        '?' => '成',
        '?' => '时',
        '?' => '间',
        '?' => '版',
        '?' => '本',
        '?' => '系',
        '?' => '统',
        '?' => '学',
        '?' => '习',
        '?' => '库',
        '?' => '配',
        '?' => '置',
        '?' => '阈',
        '?' => '值',
        '?' => '限',
        '?' => '人',
        '?' => '脸',
        '?' => '情',
        '?' => '结',
        '?' => '构',
        '?' => '向',
        '?' => '量',
        '?' => '记',
        '?' => '录',
        '?' => '特',
        '?' => '征',
        '?' => '匹',
        '?' => '配',
        '?' => '加',
        '?' => '速',
        '?' => '路',
        '?' => '径',
        '?' => '点',
        '?' => '表',
        '?' => '情',
        '?' => '活',
        '?' => '体',
        '?' => '资',
        '?' => '源',
        '?' => '已',
        '?' => '释',
        '?' => '放',
        '?' => '名',
        '?' => '称',
        '?' => '缓',
        '?' => '存',
        '?' => '未',
        '?' => '载',
        '?' => '数',
        '?' => '面',
        '?' => '特',
        '?' => '征',
        '?' => '抛',
        '?' => '异',
        '?' => '常',
        '?' => '超',
        '?' => '标',
        '?' => '大',
        '?' => '小',
        '?' => '字',
        '?' => '符',
        '?' => '串',
        '?' => '类',
        '?' => '型',
        '?' => '期'
    ];
    
    foreach ($encodingFixes as $from => $to) {
        $count = substr_count($content, $from);
        if ($count > 0) {
            $content = str_replace($from, $to, $content);
            $stats['fixes_by_type']['string_encoding'] += $count;
        }
    }
    
    // 6. 修复引号问题 (缺少引号或错误引号)
    $pattern = '/([\'"])(.*?)(?<!\\\)\1\s*\.\s*([^\'";,\s\(\)\[\]{}]+)/';
    $replacement = '$1$2$1 . "$3"';
    $content = preg_replace($pattern, $replacement, $content, -1, $count);
    $stats['fixes_by_type']['quotes'] += $count;
    
    // 检查是否有修改
    if ($content !== $originalContent) {
        // 写回文件
        if (file_put_contents($file, $content) !== false) {
            $stats['files_modified']++;
            $totalFixes = array_sum($stats['fixes_by_type']);
            $stats['total_fixes'] += $totalFixes;
            
            return true;
        }
    }
    
    return false;
}

/**
 * 递归扫描目录
 */
function scanDirectory(string $dir, array &$stats, array $ignoreDirs): void {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        // 检查是否为忽略目录
        $shouldIgnore = false;
        foreach ($ignoreDirs as $ignoreDir) {
            if (strpos($path, '/' . $ignoreDir . '/') !== false || 
                strpos($path, '\\' . $ignoreDir . '\\') !== false) {
                $shouldIgnore = true;
                break;
            }
        }
        
        if ($shouldIgnore) {
            continue;
        }
        
        if (is_dir($path)) {
            scanDirectory($path, $stats, $ignoreDirs);
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $stats['files_scanned']++;
            
            $relativePath = str_replace(dirname(__DIR__) . '/', '', $path);
            echo "处理文件: " . $relativePath;
            
            if (fixPhp81Syntax($path, $stats)) {
                echo " - 已修复\n";
            } else {
                echo " - 无需修复\n";
            }
        }
    }
}

// 开始扫描
echo "开始PHP 8.1语法修复...\n";
echo "----------------------------\n";

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        scanDirectory($dir, $stats, $ignoreDirs);
    }
}

// 打印统计信息
echo "\n===== 修复完成 =====\n";
echo "扫描文件数: " . $stats['files_scanned'] . "\n";
echo "修改文件数: " . $stats['files_modified'] . "\n";
echo "总修复数: " . $stats['total_fixes'] . "\n";
echo "\n修复类型统计:\n";
echo "- declare(strict_types): " . $stats['fixes_by_type']['strict_types'] . "\n";
echo "- array_merge调用: " . $stats['fixes_by_type']['array_merge'] . "\n";
echo "- 其他函数调用: " . $stats['fixes_by_type']['function_call'] . "\n";
echo "- is_array修复: " . $stats['fixes_by_type']['is_array'] . "\n";
echo "- 字符编码问题: " . $stats['fixes_by_type']['string_encoding'] . "\n";
echo "- 引号和连接修复: " . $stats['fixes_by_type']['quotes'] . "\n";
echo "- 其他修复: " . $stats['fixes_by_type']['other'] . "\n";

if ($stats['files_modified'] > 0) {
    echo "\n已成功修复PHP 8.1语法问题！\n";
} else {
    echo "\n未发现需要修复的PHP 8.1语法问题。\n";
} 