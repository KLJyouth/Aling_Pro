<?php
// 检查PHP文件语法
$file = 'public/admin/api/documentation/index.php';
$output = [];
$return_var = 0;

// 使用exec执行php -l命令
exec("php -l $file 2>&1", $output, $return_var);

// 输出结果
echo implode("\n", $output) . "\n";
echo "返回值: $return_var\n";

// 手动检查文件内容
$content = file_get_contents($file);
echo "文件大小: " . strlen($content) . " 字节\n";

// 检查特定行
$lines = explode("\n", $content);
echo "第49行: " . $lines[48] . "\n";
echo "第50行: " . $lines[49] . "\n";
echo "第51行: " . $lines[50] . "\n";

?> 