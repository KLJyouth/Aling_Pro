<?php

// 直接测试Application.php文件
$appFile = __DIR__ . '/src/Core/Application.php';

if (!file_exists($appFile)) {
    echo "Application.php not found!\n";
    exit(1);
}

echo "File exists: YES\n";
echo "File size: " . filesize($appFile) . " bytes\n";

// 读取文件内容
$content = file_get_contents($appFile);
echo "First 200 characters:\n";
echo substr($content, 0, 200) . "\n\n";

// 检查是否有BOM或隐藏字符
$firstChars = substr($content, 0, 10);
for ($i = 0; $i < strlen($firstChars); $i++) {
    echo "Char $i: " . ord($firstChars[$i]) . " (" . $firstChars[$i] . ")\n";
}
