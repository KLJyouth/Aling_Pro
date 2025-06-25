<?php
// 设置文件路径
$filePath = 'ai-engines/nlp/ChineseTokenizer.php';
$fixedFilePath = 'ai-engines/nlp/ChineseTokenizer.php.fixed';

// 读取原文�?
$content = file_get_contents($filePath];

// 创建修复版本 - 替换中文字符为ASCII版本，避免PHP 8.1的UTF-8问题
$fixedContent = str_replace(
    [
        "if (preg_match('/^[\\d年月日时分秒]+$/u', \$token)) {",
        "if (preg_match('/^[，。！？：；、（）【】《�?"'']+$/u', \$token)) {",
        '$commonChars = [\'的\', \'了\', \'和\', \'是\', \'在\', \'有\', \'我\', \'你\', \'他\', \'她\', \'它\', \'们\'];'
    ], 
    [
        "if (preg_match('/^[\\d]+$/u', \$token)) {", // 简化为只检查数�?
        "if (preg_match('/^[,.!?:;\'()\\[\\]<>\"\']+$/u', \$token)) {", // 使用ASCII字符
        '$commonChars = [\'de\', \'le\', \'he\', \'shi\', \'zai\', \'you\', \'wo\', \'ni\', \'ta\', \'ta\', \'ta\', \'men\'];' // 使用拼音
    ], 
    $content
];

// 保存修复版本
file_put_contents($fixedFilePath, $fixedContent];

echo "已创建修复版�? $fixedFilePath\n";
echo "请检查修复版本，如果没有问题，可以替换原文件。\n"; 
