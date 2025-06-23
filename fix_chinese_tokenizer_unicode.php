<?php
/**
 * 修复ChineseTokenizer.php文件中的UTF-8编码问题
 * 使用Unicode编码点替代直接的中文字符
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
$backupPath = $filePath . '.unicode_bak';
file_put_contents($backupPath, $content);
echo "已创建备份: $backupPath\n";

// 中文字符映射到Unicode编码点
$chineseToUnicode = [
    // 常见字符数组
    "'de'" => "mb_chr(0x7684, 'UTF-8')", // 的
    "'le'" => "mb_chr(0x4E86, 'UTF-8')", // 了
    "'he'" => "mb_chr(0x548C, 'UTF-8')", // 和
    "'shi'" => "mb_chr(0x662F, 'UTF-8')", // 是
    "'zai'" => "mb_chr(0x5728, 'UTF-8')", // 在
    "'you'" => "mb_chr(0x6709, 'UTF-8')", // 有
    "'wo'" => "mb_chr(0x6211, 'UTF-8')", // 我
    "'ni'" => "mb_chr(0x4F60, 'UTF-8')", // 你
    "'ta'" => "mb_chr(0x4ED6, 'UTF-8'), mb_chr(0x5979, 'UTF-8'), mb_chr(0x5B83, 'UTF-8')", // 他, 她, 它
    "'men'" => "mb_chr(0x4EEC, 'UTF-8')", // 们
];

// 修改常见字符数组的定义
$pattern = '/private function isCommonChar\(string \$char\): bool\s*\{\s*\$commonChars = \[(.*?)\];/s';
preg_match($pattern, $content, $matches);

if (isset($matches[1])) {
    $originalCommonChars = $matches[1];
    $newCommonChars = "
            mb_chr(0x7684, 'UTF-8'), // 的
            mb_chr(0x4E86, 'UTF-8'), // 了
            mb_chr(0x548C, 'UTF-8'), // 和
            mb_chr(0x662F, 'UTF-8'), // 是
            mb_chr(0x5728, 'UTF-8'), // 在
            mb_chr(0x6709, 'UTF-8'), // 有
            mb_chr(0x6211, 'UTF-8'), // 我
            mb_chr(0x4F60, 'UTF-8'), // 你
            mb_chr(0x4ED6, 'UTF-8'), // 他
            mb_chr(0x5979, 'UTF-8'), // 她
            mb_chr(0x5B83, 'UTF-8'), // 它
            mb_chr(0x4EEC, 'UTF-8')  // 们";
    
    $content = str_replace(
        "private function isCommonChar(string \$char): bool\n    {\n        \$commonChars = [$originalCommonChars];",
        "private function isCommonChar(string \$char): bool\n    {\n        // 使用Unicode编码点替代直接的中文字符\n        \$commonChars = [$newCommonChars]",
        $content
    );
}

// 修改日期正则表达式
$pattern = '/\/\^\\\\[\d\]+\$\/u/';
$replacement = '/^[\d\x{5E74}\x{6708}\x{65E5}\x{65F6}\x{5206}\x{79D2}]+$/u';
$content = preg_replace(
    "/(if \(preg_match\()$pattern(, \\\$token\)\) \{\s*return 'datetime';)/",
    "$1'$replacement'$2",
    $content
);

// 添加导入mb_chr函数的检查
$pattern = '/namespace AlingAi\\\\AI\\\\Engines\\\\NLP;/';
$replacement = "namespace AlingAi\\AI\\Engines\\NLP;\n\n// 如果mb_chr函数不存在，定义一个polyfill\nif (!function_exists('mb_chr')) {\n    function mb_chr(int \$codepoint, string \$encoding = 'UTF-8'): string {\n        return html_entity_decode('&#' . \$codepoint . ';', ENT_QUOTES, \$encoding);\n    }\n}";
$content = preg_replace($pattern, $replacement, $content);

// 写入修改后的文件
file_put_contents($filePath, $content);
echo "已修复文件，使用Unicode编码点替代中文字符\n";

echo "\n修复完成!\n";

// 建议添加单元测试
echo "\n建议添加以下单元测试来验证修复效果:\n";
echo "1. 测试常见字符识别功能\n";
echo "2. 测试日期时间识别功能\n";
echo "3. 测试在PHP 8.1环境下的兼容性\n";
echo "4. 测试在不同编码环境下的性能\n"; 