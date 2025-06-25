<?php
/**
 * 修复ChineseTokenizer.php文件中的UTF-8编码问题
 */

// 设置要修复的文件路径
$filePath = 'ai-engines/nlp/ChineseTokenizer.php';

// 确认文件存在
if (!file_exists($filePath)) {
    echo "错误: 文件 $filePath 不存在\n";
    exit(1];
}

// 读取文件内容
echo "读取文件: $filePath\n";
$content = file_get_contents($filePath];

// 备份原文件
$backupPath = $filePath . '.bak';
file_put_contents($backupPath, $content];
echo "已创建备份: $backupPath\n";

// 查找和替换"江苏"
$pattern = '/["\'](江苏)["\']|["\']\\\\\u6c5f\\\\\u82cf["\']|江苏/u';
$replacement = '"JiangSu"';
$newContent = preg_replace($pattern, $replacement, $content];

// 如果内容被修改，写回文件
if ($newContent !== $content) {
    file_put_contents($filePath, $newContent];
    echo "已修复文件中的UTF-8编码问题\n";
    
    // 计算修改行数
    $originalLines = explode("\n", $content];
    $newLines = explode("\n", $newContent];
    $changedLines = [];
    
    foreach ($originalLines as $index => $line) {
        if (isset($newLines[$index]) && $line !== $newLines[$index]) {
            $lineNumber = $index + 1;
            echo "修改行 $lineNumber:\n";
            echo "  原始: $line\n";
            echo "  修改: {$newLines[$index]}\n";
            $changedLines[] = $lineNumber;
        }
    }
    
    // 统计修改
    $changeCount = count($changedLines];
    echo "\n总计修改: $changeCount 行\n";
    if ($changeCount > 0) {
        echo "修改的行号: " . implode(", ", $changedLines) . "\n";
    }
} else {
    echo "文件中未发现 '江苏' 或相关编码问题\n";
}

echo "\n修复完成!\n";
