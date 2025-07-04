<?php
/**
 * 提交前代码检查脚本
 * 
 * 用于在提交代码前检查代码质量，包括语法错误、编码标准等
 * 
 * 使用方法：
 * 1. 将此文件复制到 .git/hooks/pre-commit
 * 2. 确保文件有执行权限 (chmod +x .git/hooks/pre-commit)
 */

// 获取暂存区中的PHP文件
$output = [];
exec("git diff --cached --name-only --diff-filter=ACMR | grep -E \"\.php$\"", $output);

if (empty($output)) {
    echo "没有PHP文件需要检查。\n";
    exit(0);
}

$files = array_filter($output, function($file) {
    return file_exists($file) && is_file($file);
});

if (empty($files)) {
    echo "没有有效的PHP文件需要检查。\n";
    exit(0);
}

// 检查PHP语法错误
$hasErrors = false;
foreach ($files as $file) {
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg($file), $output, $return);
    
    if ($return !== 0) {
        echo implode("\n", $output) . "\n";
        $hasErrors = true;
    }
}

if ($hasErrors) {
    echo "检测到PHP语法错误，请修复后再提交。\n";
    exit(1);
}

// 如果安装了PHP_CodeSniffer，则检查代码风格
if (file_exists("vendor/bin/phpcs")) {
    $fileList = escapeshellarg(implode(" ", $files));
    $output = [];
    $return = 0;
    
    exec("vendor/bin/phpcs --standard=phpcs.xml " . $fileList, $output, $return);
    
    if ($return !== 0) {
        echo "代码风格检查失败：\n";
        echo implode("\n", $output) . "\n";
        echo "请修复代码风格问题后再提交。\n";
        exit(1);
    }
}

// 如果安装了PHPStan，则进行静态分析
if (file_exists("vendor/bin/phpstan")) {
    $fileList = escapeshellarg(implode(" ", $files));
    $output = [];
    $return = 0;
    
    exec("vendor/bin/phpstan analyse --configuration=phpstan.neon " . $fileList, $output, $return);
    
    if ($return !== 0) {
        echo "静态分析检查失败：\n";
        echo implode("\n", $output) . "\n";
        echo "请修复静态分析问题后再提交。\n";
        exit(1);
    }
}

echo "代码检查通过！\n";
exit(0);
