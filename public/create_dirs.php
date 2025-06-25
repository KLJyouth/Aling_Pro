<?php
// 创建必要的目�?

// 定义需要创建的目录
$directories = [
    '../storage',
    '../storage/logs',
    '../storage/cache',
    '../storage/cache/rate_limit',
    '../storage/app',
    '../storage/framework',
    '../storage/framework/views',
    '../storage/framework/cache',
    '../storage/framework/sessions'
];

// 创建目录
foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "创建目录成功: $dir<br>";
        } else {
            echo "创建目录失败: $dir<br>";
        }
    } else {
        echo "目录已存�? $dir<br>";
    }
}

// 创建测试文件
$testFile = __DIR__ . '/../storage/logs/test.log';
file_put_contents($testFile, date('Y-m-d H:i:s') . " - Test log entry\n", FILE_APPEND];
echo "创建测试日志文件: ../storage/logs/test.log<br>";

echo "<p>目录创建完成�?/p>";
?> 
