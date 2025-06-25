<?php
/**
 * PHP语法检查工具
 * 
 * 此脚本遍历项目中的所有PHP文件，检查语法错误
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
    'files_checked' => 0,
    'files_with_errors' => 0,
    'errors' => [],
];

/**
 * 检查PHP文件语法
 */
function checkPhpSyntax(string $file): ?string {
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
    
    if ($return !== 0) {
        return implode("\n", $output);
    }
    
    return null;
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
            $stats['files_checked']++;
            
            echo "检查文件: " . str_replace(dirname(__DIR__) . '/', '', $path) . "\n";
            
            $error = checkPhpSyntax($path);
            if ($error !== null) {
                $stats['files_with_errors']++;
                $stats['errors'][] = [
                    'file' => $path,
                    'error' => $error,
                ];
                
                echo "  发现错误: " . $error . "\n";
            }
        }
    }
}

// 开始扫描
echo "开始PHP语法检查...\n";
echo "----------------------------\n";

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        scanDirectory($dir, $stats, $ignoreDirs);
    }
}

// 打印统计信息
echo "\n===== 检查完成 =====\n";
echo "检查文件数: " . $stats['files_checked'] . "\n";
echo "有错误的文件数: " . $stats['files_with_errors'] . "\n";

// 输出错误详情
if ($stats['files_with_errors'] > 0) {
    echo "\n===== 错误详情 =====\n";
    foreach ($stats['errors'] as $index => $error) {
        $relativePath = str_replace(dirname(__DIR__) . '/', '', $error['file']);
        echo ($index + 1) . ". " . $relativePath . ":\n";
        echo "   " . str_replace("\n", "\n   ", $error['error']) . "\n\n";
    }
    
    exit(1);
} else {
    echo "\n恭喜！所有PHP文件语法检查通过！\n";
    exit(0);
}
