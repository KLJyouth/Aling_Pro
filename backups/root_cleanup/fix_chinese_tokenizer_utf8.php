<?php
/**
 * 修复ChineseTokenizer.php文件中的UTF-8编码问题
 * 针对PHP 8.1兼容性
 */

// 设置要修复的文件路径
$filePath = 'ai-engines/nlp/ChineseTokenizer.php';

// 确认文件存在
if (!file_exists($filePath)) {
    echo "错误: 文件 $filePath 不存在\n";
    exit(1);
}

// 读取文件内容
echo "读取文件: $filePath\n";
$content = file_get_contents($filePath);

// 备份原文件
$backupPath = $filePath . '.bak_' . date('YmdHis');
file_put_contents($backupPath, $content);
echo "已创建备份: $backupPath\n";

// 修复中文标点符号正则表达式
$pattern1 = '/\[，。！？：；、（）【】《》""\'\']/';
$replacement1 = '[\x{FF0C}\x{3002}\x{FF01}\x{FF1F}\x{FF1A}\x{FF1B}\x{3001}\x{FF08}\x{FF09}\x{3010}\x{3011}\x{300A}\x{300B}\x{201C}\x{201D}\x{2018}\x{2019}]';

// 修复中文日期正则表达式
$pattern2 = '/\[\\\\d年月日时分秒]/';
$replacement2 = '[\d\x{5E74}\x{6708}\x{65E5}\x{65F6}\x{5206}\x{79D2}]';

// 修复常见字符数组
$pattern3 = '/\[\'的\', \'了\', \'和\', \'是\', \'在\', \'有\', \'我\', \'你\', \'他\', \'她\', \'它\', \'们\'\]/';
$replacement3 = "['\\x{7684}', '\\x{4E86}', '\\x{548C}', '\\x{662F}', '\\x{5728}', '\\x{6709}', '\\x{6211}', '\\x{4F60}', '\\x{4ED6}', '\\x{5979}', '\\x{5B83}', '\\x{4EEC}']";

// 应用所有修复
$newContent = preg_replace($pattern1, $replacement1, $content);
$newContent = preg_replace($pattern2, $replacement2, $newContent);
$newContent = preg_replace($pattern3, $replacement3, $newContent);

// 如果内容被修改，写回文件
if ($newContent !== $content) {
    file_put_contents($filePath, $newContent);
    echo "已修复文件中的UTF-8编码问题\n";
    
    // 查找差异
    $contentLines = explode("\n", $content);
    $newContentLines = explode("\n", $newContent);
    $diffCount = 0;
    
    echo "\n修改的行：\n";
    for ($i = 0; $i < count($contentLines); $i++) {
        if (isset($newContentLines[$i]) && $contentLines[$i] !== $newContentLines[$i]) {
            $lineNumber = $i + 1;
            echo "行 $lineNumber: \n";
            echo "原始: " . $contentLines[$i] . "\n";
            echo "修改: " . $newContentLines[$i] . "\n";
            echo "---\n";
            $diffCount++;
        }
    }
    
    echo "\n总共修改了 $diffCount 行\n";
} else {
    echo "文件中未发现编码问题，或者替换模式匹配失败\n";
}

echo "\n修复完成!\n"; 