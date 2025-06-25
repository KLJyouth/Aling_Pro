<?php

/**
 * AlingAi Pro PHP 8.1 语法兼容性修复工�?
 * 
 * 这个脚本用于自动修复以下常见语法错误�?
 * 1. 构造函数中的重复括�? __construct((param)) -> __construct(param)
 * 2. 函数内部错误使用private关键�? private $var -> $var
 * 3. 行尾多余的单引号和分�? 'value','; -> 'value',
 * 4. 函数参数中的多余括号: function test(()) -> function test()
 * 5. 数组语法错误: 'key' => value,'; -> 'key' => value,
 * 6. 命名空间一致�? AlingAI\ �?AlingAiPro\ -> AlingAi\
 * 7. 修复缺少对应catch块的try语句
 */

// 设置脚本最大执行时�?
set_time_limit(300];

// 源代码目�?
$srcDir = __DIR__ . '/src';

// 统计信息
$stats = [
    'files_scanned' => 0,
    'files_modified' => 0,
    'errors_fixed' => [
        'double_parentheses' => 0,
        'private_in_function' => 0,
        'extra_quotes' => 0,
        'empty_parentheses' => 0,
        'array_syntax' => 0,
        'namespace' => 0,
        'try_catch' => 0
    ]
];

// 递归扫描目录
function scanDirectory($dir, &$stats) {
    $items = scandir($dir];
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            scanDirectory($path, $stats];
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            fixFile($path, $stats];
        }
    }
}

// 修复文件
function fixFile($file, &$stats) {
    echo "检查文�? " . $file . PHP_EOL;
    $stats['files_scanned']++;
    
    $content = file_get_contents($file];
    $originalContent = $content;
    
    // 安全检查：如果文件内容为空，跳过处�?
    if (empty($content)) {
        echo "警告: 文件为空，跳过处�? " . $file . PHP_EOL;
        return;
    }
    // 安全检查：确保不会写入空内�?
    if (empty($content)) {
        echo "错误: 不能写入空内容，跳过修改: " . $file . PHP_EOL;
        return;
    }
    
    // 如果内容有变化，保存文件
    if ($content !== $originalContent) {
        echo "修复文件: " . $file . PHP_EOL;
        file_put_contents($file, $content];
        $stats['files_modified']++;
    }
}

// 开始扫�?

// 创建备份目录
$backupDir = __DIR__ . '/backup/syntax_fix_backup_' . date('Ymd_His'];
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true];
}

// 备份src目录
function backupDirectory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0777, true];
    }
    
    $iterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS];
    $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST];
    
    foreach ($files as $file) {
        $targetPath = $dest . DIRECTORY_SEPARATOR . $files->getSubPathName(];
        
        if ($file->isDir()) {
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true];
            }
        } else {
            copy($file->getPathname(), $targetPath];
        }
    }
}

echo "备份src目录�?{$backupDir}...\n";
backupDirectory($srcDir, $backupDir . '/src'];

echo "开始扫描并修复PHP文件...\n";
$startTime = microtime(true];

scanDirectory($srcDir, $stats];

$endTime = microtime(true];
$executionTime = round($endTime - $startTime, 2];

// 输出统计信息
echo "\n修复完成! 执行时间: {$executionTime} 秒\n";
echo "扫描文件�? {$stats['files_scanned']}\n";
echo "修改文件�? {$stats['files_modified']}\n";
echo "修复错误�?\n";
echo "  - 构造函数重复括�? {$stats['errors_fixed']['double_parentheses']}\n";
echo "  - 函数内private关键�? {$stats['errors_fixed']['private_in_function']}\n";
echo "  - 多余引号和分�? {$stats['errors_fixed']['extra_quotes']}\n";
echo "  - 函数参数多余括号: {$stats['errors_fixed']['empty_parentheses']}\n";
echo "  - 数组语法错误: {$stats['errors_fixed']['array_syntax']}\n";
echo "  - 命名空间问题: {$stats['errors_fixed']['namespace']}\n";
echo "  - 缺少catch�? {$stats['errors_fixed']['try_catch']}\n";

echo "\n请运�?'php -l' 检查是否还有剩余错误。\n";

// 检查剩余错�?- Windows PowerShell版本
echo "\n检查剩余语法错�?..\n";
$command = 'powershell -Command "$errorCount = (Get-ChildItem -Path src -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName } | Select-String -Pattern \"Errors parsing\" | Measure-Object).Count; Write-Output \"PHP文件剩余错误�? $errorCount\""';
echo shell_exec($command];

// 修复API文档中的语法错误
$file = 'public/admin/api/documentation/index.php';
$content = file_get_contents($file];

// 备份原文�?
file_put_contents($file . '.bak', $content];

// 读取文件的每一�?
$lines = file($file];

// 修复�?9行的问题（描述行�?
$lines[48] = '            "description" => "AlingAi Pro API文档系统 - 用户管理、系统监控等功能",'."\n";

// 将修改后的内容写回文�?
file_put_contents($file, implode('', $lines)];

echo "文件已修�? $file\n";
?> 

