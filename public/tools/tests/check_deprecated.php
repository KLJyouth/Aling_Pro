<?php

/**
 * AlingAi Pro PHP 8.1 废弃特性检查工�?
 * 
 * 这个脚本用于检查代码中使用的PHP 8.1中废弃的函数和特�?
 */

// 设置脚本最大执行时�?
set_time_limit(300];

// 源代码目�?
$srcDir = __DIR__ . '/src';

// 统计信息
$stats = [
    'files_scanned' => 0,
    'files_with_issues' => 0,
    'issues' => [
        'deprecated_functions' => 0,
        'deprecated_features' => 0
    ]
];

// PHP 8.1中废弃的函数列表
$deprecatedFunctions = [
    'strstr' => 'str_contains',
    'strpos' => 'str_contains (如果检查是否包�?',
    'strrpos' => 'str_contains (如果检查是否包�?',
    'strncmp' => 'str_starts_with (如果检查开�?',
    'substr_compare' => 'str_starts_with/str_ends_with',
    'substr' => 'str_starts_with/str_ends_with (如果检查开头或结尾)',
    'each' => '使用 foreach 或其他迭代方�?,
    'is_resource' => 'is_object (对于已转换为对象的资�?',
    'mb_ereg_replace' => 'mb_ereg_replace_callback',
    'mb_eregi_replace' => 'mb_eregi_replace_callback',
    'create_function' => '匿名函数',
    'parse_str' => '带第二个参数�?parse_str',
    'assert' => '其他验证方法',
    'utf8_encode' => 'mb_convert_encoding',
    'utf8_decode' => 'mb_convert_encoding',
    'get_magic_quotes_gpc' => '移除，魔术引号已被废�?,
    'get_magic_quotes_runtime' => '移除，魔术引号已被废�?,
];

// 递归扫描目录
function scanDirectory($dir, &$stats, $deprecatedFunctions) {
    $items = scandir($dir];
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            scanDirectory($path, $stats, $deprecatedFunctions];
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            checkFile($path, $stats, $deprecatedFunctions];
        }
    }
}

// 检查文�?
function checkFile($file, &$stats, $deprecatedFunctions) {
    echo "检查文�? " . $file . PHP_EOL;
    $stats['files_scanned']++;
    
    $content = file_get_contents($file];
    $hasIssues = false;
    
    // 检查废弃的函数
    foreach ($deprecatedFunctions as $function => $alternative) {
        $pattern = '/\b' . preg_quote($function, '/') . '\s*\(/';
        if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            if (!$hasIssues) {
                echo "  文件 {$file} 中发现问�?" . PHP_EOL;
                $hasIssues = true;
                $stats['files_with_issues']++;
            }
            
            $count = count($matches[0]];
            $stats['issues']['deprecated_functions'] += $count;
            
            echo "    - 使用了废弃函�?{$function}() {$count} 次，建议使用 {$alternative}" . PHP_EOL;
        }
    }
    
    // 检查废弃的特�?
    
    // 1. 检�?FILTER_SANITIZE_STRING
    if (strpos($content, 'FILTER_SANITIZE_STRING') !== false) {
        if (!$hasIssues) {
            echo "  文件 {$file} 中发现问�?" . PHP_EOL;
            $hasIssues = true;
            $stats['files_with_issues']++;
        }
        
        $stats['issues']['deprecated_features']++;
        echo "    - 使用了废弃的过滤�?FILTER_SANITIZE_STRING，建议使�?htmlspecialchars() 或其他替代方�? . PHP_EOL;
    }
    
    // 2. 检查隐式函数声�?
    if (preg_match('/function\s+[a-zA-Z0-9_]+\s*\([^)]*\)\s*(?!:)(?!{)/m', $content)) {
        if (!$hasIssues) {
            echo "  文件 {$file} 中发现问�?" . PHP_EOL;
            $hasIssues = true;
            $stats['files_with_issues']++;
        }
        
        $stats['issues']['deprecated_features']++;
        echo "    - 使用了隐式函数声明（没有返回类型和函数体），在PHP 8.1中可能会有警�? . PHP_EOL;
    }
    
    // 3. 检�?serialize 未实�?Serializable 接口的对�?
    if (preg_match('/serialize\s*\(/', $content) && !strpos($content, 'implements\s+Serializable')) {
        if (!$hasIssues) {
            echo "  文件 {$file} 中发现问�?" . PHP_EOL;
            $hasIssues = true;
            $stats['files_with_issues']++;
        }
        
        $stats['issues']['deprecated_features']++;
        echo "    - 使用�?serialize() 但类可能没有实现 Serializable 接口，在PHP 8.1中可能会有警�? . PHP_EOL;
    }
}

// 开始扫�?
echo "开始扫描PHP文件，检查PHP 8.1中废弃的函数和特�?..\n";
$startTime = microtime(true];

scanDirectory($srcDir, $stats, $deprecatedFunctions];

$endTime = microtime(true];
$executionTime = round($endTime - $startTime, 2];

// 输出统计信息
echo "\n检查完�? 执行时间: {$executionTime} 秒\n";
echo "扫描文件�? {$stats['files_scanned']}\n";
echo "有问题的文件�? {$stats['files_with_issues']}\n";
echo "发现问题�?\n";
echo "  - 废弃函数: {$stats['issues']['deprecated_functions']}\n";
echo "  - 废弃特�? {$stats['issues']['deprecated_features']}\n";

echo "\n建议修复这些问题以确保代码在PHP 8.1中正常运行。\n"; 
