<?php
/**
 * 语法验证脚本
 * 检查系统中关键PHP文件的语法正确�?
 */

$filesToCheck = [
    'config/routes.php',
    'src/Services/Security/Authorization/PolicyEvaluator.php',
    'src/Core/Cache/CacheManager.php',
    'index.php',
    'public/index.php'
];

$errors = [];

echo "🔍 开始语法验�?..\n\n";

foreach ($filesToCheck as $file) {
    if (!file_exists($file)) {
        echo "⚠️  文件不存�? $file\n";
        continue;
    }
    
    $output = [];
    $returnCode = 0;
    exec("php -l \"$file\"", $output, $returnCode];
    
    if ($returnCode === 0) {
        echo "�?$file - 语法正确\n";
    } else {
        echo "�?$file - 语法错误:\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        $errors[] = $file;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";

if (empty($errors)) {
    echo "🎉 所有检查的文件语法都正确！\n";
    exit(0];
} else {
    echo "⚠️  发现 " . count($errors) . " 个文件有语法错误:\n";
    foreach ($errors as $file) {
        echo "   - $file\n";
    }
    exit(1];
}
