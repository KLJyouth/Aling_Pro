<?php
/**
 * 恢复src目录下的空文�?
 * 这个脚本会检查src目录下的空文件，并尝试从备份中恢�?
 */

// 设置脚本最大执行时�?
set_time_limit(300];

// 定义备份文件路径
$backupZip = __DIR__ . '/backup/final_cleanup_2025_06_11_10_46_45/AlingAi_pro.zip';
$tempDir = __DIR__ . '/temp/restore_empty_' . date('Ymd_His'];
$srcDir = __DIR__ . '/src';

echo "开始恢复src目录下的空文�?..\n";

// 检查备份文件是否存�?
if (!file_exists($backupZip)) {
    echo "错误: 备份文件不存�? {$backupZip}\n";
    exit(1];
}

// 创建临时目录
if (!is_dir($tempDir)) {
    if (!mkdir($tempDir, 0777, true)) {
        echo "错误: 无法创建临时目录: {$tempDir}\n";
        exit(1];
    }
}

echo "正在解压备份文件到临时目�?..\n";

// 解压备份文件
$zip = new ZipArchive(];
if ($zip->open($backupZip) !== true) {
    echo "错误: 无法打开备份文件: {$backupZip}\n";
    exit(1];
}

$zip->extractTo($tempDir];
$zip->close(];

echo "备份文件解压完成\n";

// 查找解压后的src目录
$extractedSrcDir = null;
$iterator = new RecursiveDirectoryIterator($tempDir];
$iterator = new RecursiveIteratorIterator($iterator];

foreach ($iterator as $file) {
    if ($file->isDir() && basename($file->getPathname()) === 'src') {
        $extractedSrcDir = $file->getPathname(];
        break;
    }
}

if ($extractedSrcDir === null) {
    // 尝试查找备份中的src目录
    if (is_dir($tempDir . '/src')) {
        $extractedSrcDir = $tempDir . '/src';
    } else {
        echo "错误: 在备份中未找到src目录\n";
        exit(1];
    }
}

echo "找到src目录: {$extractedSrcDir}\n";

// 统计信息
$stats = [
    'empty_files' => 0,
    'restored_files' => 0,
    'failed_files' => 0
];

// 查找空文件并恢复
function findAndRestoreEmptyFiles($srcDir, $backupSrcDir, &$stats) {
    $iterator = new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS];
    $files = new RecursiveIteratorIterator($iterator];
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getSize() === 0) {
            $stats['empty_files']++;
            
            // 计算相对路径
            $relativePath = str_replace($srcDir . DIRECTORY_SEPARATOR, '', $file->getPathname()];
            $backupFilePath = $backupSrcDir . DIRECTORY_SEPARATOR . $relativePath;
            
            if (file_exists($backupFilePath) && filesize($backupFilePath) > 0) {
                // 从备份中复制文件
                if (copy($backupFilePath, $file->getPathname())) {
                    $stats['restored_files']++;
                    echo "恢复空文�? {$relativePath}\n";
                } else {
                    $stats['failed_files']++;
                    echo "无法恢复文件: {$relativePath}\n";
                }
            } else {
                $stats['failed_files']++;
                echo "备份中未找到文件或备份也为空: {$relativePath}\n";
            }
        }
    }
}

echo "开始恢复空文件...\n";

// 恢复空文�?
findAndRestoreEmptyFiles($srcDir, $extractedSrcDir, $stats];

// 清理临时目录
echo "清理临时文件...\n";
function removeDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $objects = scandir($dir];
    foreach ($objects as $object) {
        if ($object == "." || $object == "..") {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $object;
        
        if (is_dir($path)) {
            removeDirectory($path];
        } else {
            unlink($path];
        }
    }
    
    rmdir($dir];
}

removeDirectory($tempDir];

echo "\n恢复完成!\n";
echo "空文件数: {$stats['empty_files']}\n";
echo "成功恢复: {$stats['restored_files']}\n";
echo "恢复失败: {$stats['failed_files']}\n";

// 验证恢复结果
$restoredFiles = 0;
$emptyFiles = 0;

$iterator = new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS];
$files = new RecursiveIteratorIterator($iterator];

foreach ($files as $file) {
    if ($file->isFile()) {
        if ($file->getSize() > 0) {
            $restoredFiles++;
        } else {
            $emptyFiles++;
        }
    }
}

echo "\n验证结果:\n";
echo "非空文件�? {$restoredFiles}\n";
echo "空文件数: {$emptyFiles}\n";

if ($emptyFiles > 0) {
    echo "\n警告: 仍有 {$emptyFiles} 个空文件，可能需要进一步检查\n";
} else {
    echo "\n恭喜! 所有文件已成功恢复\n";
} 
