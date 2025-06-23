<?php
/**
 * 修复fix_syntax.php脚本的安全问题
 * 这个脚本会修改fix_syntax.php，添加安全检查，确保它不会清空文件
 */

$fixSyntaxFile = __DIR__ . '/fix_syntax.php';

if (!file_exists($fixSyntaxFile)) {
    echo "错误: fix_syntax.php文件不存在\n";
    exit(1);
}

echo "开始修复fix_syntax.php脚本的安全问题...\n";

$content = file_get_contents($fixSyntaxFile);
$originalContent = $content;

// 在fixFile函数中添加安全检查，防止清空文件
$fixFileFunction = 'function fixFile($file, &$stats) {
    echo "检查文件: " . $file . PHP_EOL;
    $stats[\'files_scanned\']++;
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // 安全检查：如果文件内容为空，跳过处理
    if (empty($content)) {
        echo "警告: 文件为空，跳过处理: " . $file . PHP_EOL;
        return;
    }';

// 在file_put_contents之前添加安全检查
$safeFilePut = '    // 安全检查：确保不会写入空内容
    if (empty($content)) {
        echo "错误: 不能写入空内容，跳过修改: " . $file . PHP_EOL;
        return;
    }
    
    // 如果内容有变化，保存文件
    if ($content !== $originalContent) {
        echo "修复文件: " . $file . PHP_EOL;
        file_put_contents($file, $content);
        $stats[\'files_modified\']++;
    }
}';

// 替换原始的fixFile函数定义
$pattern = '/function fixFile\(\$file, &\$stats\) \{.*?if \(\$content !== \$originalContent\) \{.*?file_put_contents\(\$file, \$content\);.*?\$stats\[\'files_modified\'\]\+\+;.*?\}.*?\}/s';
$content = preg_replace($pattern, $fixFileFunction . "\n" . $safeFilePut, $content);

// 添加备份功能
$backupCode = '
// 创建备份目录
$backupDir = __DIR__ . \'/backup/syntax_fix_backup_\' . date(\'Ymd_His\');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// 备份src目录
function backupDirectory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0777, true);
    }
    
    $iterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
    
    foreach ($files as $file) {
        $targetPath = $dest . DIRECTORY_SEPARATOR . $files->getSubPathName();
        
        if ($file->isDir()) {
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }
        } else {
            copy($file->getPathname(), $targetPath);
        }
    }
}

echo "备份src目录到 {$backupDir}...\n";
backupDirectory($srcDir, $backupDir . \'/src\');
';

// 在开始扫描之前添加备份代码
$pattern = '/echo "开始扫描并修复PHP文件...\\\n";/';
$content = preg_replace($pattern, $backupCode . "\necho \"开始扫描并修复PHP文件...\\n\";", $content);

// 保存修改后的文件
if ($content !== $originalContent) {
    // 先备份原始文件
    $backupFile = $fixSyntaxFile . '.bak.' . date('Ymd_His');
    copy($fixSyntaxFile, $backupFile);
    echo "已备份原始文件到: {$backupFile}\n";
    
    file_put_contents($fixSyntaxFile, $content);
    echo "✅ 成功修复fix_syntax.php脚本的安全问题\n";
} else {
    echo "⚠️ 未能修改fix_syntax.php脚本\n";
}

echo "\n修复完成，fix_syntax.php现在更安全了\n";