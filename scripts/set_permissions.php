<?php
/**
 * AlingAi Pro 目录权限设置脚本
 * 
 * 此脚本用于检查并设置系统所需的目录权限
 */

// 定义应用根目录
define('APP_ROOT', dirname(__DIR__));

echo "===========================================\n";
echo "    AlingAi Pro 目录权限检查与设置工具\n";
echo "===========================================\n\n";

// 需要可写权限的目录列表
$writableDirs = [
    'storage',
    'storage/logs',
    'storage/cache',
    'storage/uploads',
    'storage/app',
    'storage/framework',
    'database',
    'public/uploads',
    'public/cache',
];

// 需要可执行权限的目录列表
$executableDirs = [
    'scripts',
];

// 检查并创建目录
foreach ($writableDirs as $dir) {
    $path = APP_ROOT . '/' . $dir;
    
    // 检查目录是否存在，不存在则创建
    if (!file_exists($path)) {
        echo "创建目录: {$dir}\n";
        if (!mkdir($path, 0755, true)) {
            echo "  [失败] 无法创建目录 {$dir}\n";
            continue;
        }
    }
    
    // 检查目录是否可写
    if (!is_writable($path)) {
        echo "设置目录权限: {$dir}\n";
        
        if (chmod($path, 0755)) {
            echo "  [成功] 已设置目录 {$dir} 权限为 0755\n";
        } else {
            echo "  [失败] 无法设置目录 {$dir} 权限\n";
        }
    } else {
        echo "目录 {$dir} 已具有正确权限\n";
    }
}

// 设置可执行目录权限
foreach ($executableDirs as $dir) {
    $path = APP_ROOT . '/' . $dir;
    
    // 检查目录是否存在，不存在则创建
    if (!file_exists($path)) {
        echo "创建目录: {$dir}\n";
        if (!mkdir($path, 0755, true)) {
            echo "  [失败] 无法创建目录 {$dir}\n";
            continue;
        }
    }
    
    // 设置可执行权限
    echo "设置可执行目录权限: {$dir}\n";
    if (chmod($path, 0755)) {
        echo "  [成功] 已设置目录 {$dir} 权限为 0755\n";
    } else {
        echo "  [失败] 无法设置目录 {$dir} 权限\n";
    }
}

// 检查特定文件权限
$files = [
    'database/database.sqlite',
];

foreach ($files as $file) {
    $path = APP_ROOT . '/' . $file;
    
    if (file_exists($path)) {
        echo "设置文件权限: {$file}\n";
        
        if (chmod($path, 0644)) {
            echo "  [成功] 已设置文件 {$file} 权限为 0644\n";
        } else {
            echo "  [失败] 无法设置文件 {$file} 权限\n";
        }
    } else {
        echo "文件 {$file} 不存在，跳过权限设置\n";
    }
}

echo "\n权限设置完成！\n";
echo "===========================================\n"; 