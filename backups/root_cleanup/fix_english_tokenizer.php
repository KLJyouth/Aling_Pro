<?php
/**
 * 修复EnglishTokenizer.php文件中的私有属性问题
 */

// 设置文件路径
$filePath = 'ai-engines/nlp/EnglishTokenizer.php';

// 检查文件是否存在
if (!file_exists($filePath)) {
    echo "错误: 文件 {$filePath} 不存在\n";
    exit(1);
}

// 读取文件内容
$content = file_get_contents($filePath);
echo "已读取文件: {$filePath}\n";

// 创建备份
$backupPath = $filePath . '.bak';
file_put_contents($backupPath, $content);
echo "已创建备份: {$backupPath}\n";

// 修复私有属性缺少变量名的问题
$pattern = '/private\s+([a-zA-Z_\\\\\[\]]+)(?!\s*\$)/';
$replacement = 'private $1 $var';
$newContent = preg_replace($pattern, $replacement, $content);

// 应用修复
if ($newContent !== $content) {
    file_put_contents($filePath, $newContent);
    
    // 显示修改
    $originalLines = explode("\n", $content);
    $newLines = explode("\n", $newContent);
    $changedLines = [];
    
    foreach ($originalLines as $index => $line) {
        if (isset($newLines[$index]) && $line !== $newLines[$index]) {
            $lineNumber = $index + 1;
            echo "修改行 {$lineNumber}:\n";
            echo "  原始: {$line}\n";
            echo "  修改: {$newLines[$index]}\n";
            $changedLines[] = $lineNumber;
        }
    }
    
    echo "\n已修复 " . count($changedLines) . " 处私有属性声明\n";
} else {
    echo "文件无需修改\n";
}

echo "\n修复完成!\n"; 