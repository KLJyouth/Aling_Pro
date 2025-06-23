<?php
require 'vendor/autoload.php';

$loader = require 'vendor/autoload.php';
$prefixes = $loader->getPrefixesPsr4();

echo "PSR-4 prefixes:\n";
foreach ($prefixes as $prefix => $paths) {
    if (strpos($prefix, 'AlingAi') !== false) {
        echo "  $prefix => " . implode(', ', $paths) . "\n";
    }
}

// 测试文件路径计算
$expectedPath = __DIR__ . '/src/Core/Application.php';
echo "\nExpected path: $expectedPath\n";
echo "File exists: " . (file_exists($expectedPath) ? 'YES' : 'NO') . "\n";

// 手动包含测试
echo "\nTrying to include manually...\n";
try {
    include_once $expectedPath;
    echo "Manual include successful\n";
    echo "Class exists after manual include: " . (class_exists('AlingAi\\Core\\Application', false) ? 'YES' : 'NO') . "\n";
} catch (Throwable $e) {
    echo "Manual include failed: " . $e->getMessage() . "\n";
}
