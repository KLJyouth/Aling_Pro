<?php
// 检查PHP文件语法
$file = 'public/admin/api/documentation/index.php';
$output = [];
$return_var = 0;

// 使用exec执行php -l命令
exec("php -l $file 2>&1", $output, $return_var];

// 输出结果
echo implode("\n", $output) . "\n";
echo "返回�? $return_var\n";

// 手动检查文件内�?
$content = file_get_contents($file];
echo "文件大小: " . strlen($content) . " 字节\n";

// 检查特定行
$lines = explode("\n", $content];
echo "�?9�? " . $lines[48] . "\n";
echo "�?0�? " . $lines[49] . "\n";
echo "�?1�? " . $lines[50] . "\n";

?> 
