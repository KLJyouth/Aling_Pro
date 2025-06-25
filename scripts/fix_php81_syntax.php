<?php
/**
 * PHP 8.1语法修复工具
 * 
 * 主要修复:
 * 1. 将错误的方括号 [] 改为正确的圆括号 () 作为函数调用结束符
 * 2. 修复常见的PHP 8.1兼容性问题
 */

// 检查命令行参数
if ($argc < 2) {
    echo "用法：php fix_php81_syntax.php <目标文件或目录>\n";
    exit(1);
}

$targetPath = $argv[1];

// 检查目标路径是否存在
if (!file_exists($targetPath)) {
    echo "错误：指定的路径不存在: {$targetPath}\n";
    exit(1);
}

// 计数器
$totalFiles = 0;
$fixedFiles = 0;

// 如果是目录，则递归处理
if (is_dir($targetPath)) {
    processDirectory($targetPath);
} else {
    // 如果是文件，直接处理
    $result = processFile($targetPath);
    $totalFiles++;
    if ($result) {
        $fixedFiles++;
    }
}

echo "完成! 共处理 {$totalFiles} 个文件，修复了 {$fixedFiles} 个文件。\n";

/**
 * 处理目录
 */
function processDirectory($dir) {
    global $totalFiles, $fixedFiles;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $result = processFile($file->getPathname());
            $totalFiles++;
            if ($result) {
                $fixedFiles++;
            }
        }
    }
}

/**
 * 处理单个PHP文件
 */
function processFile($filePath) {
    echo "处理文件: {$filePath}\n";
    
    // 读取文件内容
    $content = file_get_contents($filePath);
    if ($content === false) {
        echo "  错误：无法读取文件\n";
        return false;
    }
    
    // 保存原始内容用于比较
    $originalContent = $content;
    
    // 执行各种修复
    $content = fixBracketsAsFunctionTerminator($content);
    $content = fixVariableSyntax($content);
    $content = fixArraySyntax($content);
    $content = fixClassReferenceOperator($content);
    
    // 检查是否有修改
    if ($content === $originalContent) {
        echo "  无需修改\n";
        return false;
    }
    
    // 备份原文件
    $backupPath = $filePath . '.bak.' . date('YmdHis');
    if (!copy($filePath, $backupPath)) {
        echo "  警告：无法创建备份文件，跳过此文件\n";
        return false;
    }
    
    // 写入修复后的内容
    if (file_put_contents($filePath, $content) === false) {
        echo "  错误：无法写入文件\n";
        return false;
    }
    
    echo "  已修复并保存\n";
    return true;
}

/**
 * 修复将方括号 [] 用作函数调用结束符的问题
 */
function fixBracketsAsFunctionTerminator($content) {
    // 替换函数调用中的方括号结束符
    // 例如: someFunction($param1, $param2] -> someFunction($param1, $param2)
    $patterns = [
        '/(\w+)\s*\(([^)\]]*)\]/' => '$1($2)',  // 函数调用
        '/\}\s*\(([^)\]]*)\]/' => '}($1)',      // 闭包调用
        '/\)\s*\->\s*(\w+)\s*\(([^)\]]*)\]/' => ')->$1($2)', // 方法调用
        '/\$\w+\s*\->\s*(\w+)\s*\(([^)\]]*)\]/' => '$0', // 标记方法调用但不修改，由下面的模式处理
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // 修复对象方法调用，这需要多次迭代
    $methodCallPattern = '/(\$\w+(?:\[\w+\])*)\s*\->\s*(\w+)\s*\(([^)\]]*)\]/';
    $methodCallReplacement = '$1->$2($3)';
    
    while (preg_match($methodCallPattern, $content)) {
        $content = preg_replace($methodCallPattern, $methodCallReplacement, $content);
    }
    
    // 修复静态方法调用
    $staticMethodCallPattern = '/(\w+(?:\\\\w+)*)::\s*(\w+)\s*\(([^)\]]*)\]/';
    $staticMethodCallReplacement = '$1::$2($3)';
    
    while (preg_match($staticMethodCallPattern, $content)) {
        $content = preg_replace($staticMethodCallPattern, $staticMethodCallReplacement, $content);
    }
    
    // 修复declare语句
    $content = preg_replace('/declare\s*\(\s*strict_types\s*=\s*1\s*\]/', 'declare(strict_types=1)', $content);
    
    return $content;
}

/**
 * 修复变量语法问题
 */
function fixVariableSyntax($content) {
    // 修复缺少$符号的变量
    $patterns = [
        '/foreach\s*\(\s*(\w+)\s+as\s+(\w+)\s*\)/' => 'foreach ($1 as $$2)', // foreach中缺少$的变量
        '/foreach\s*\(\s*(\w+)\s+as\s+(\w+)\s*=>\s*(\w+)\s*\)/' => 'foreach ($1 as $2 => $$3)', // foreach键值对中缺少$的变量
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    return $content;
}

/**
 * 修复数组语法问题
 */
function fixArraySyntax($content) {
    // 修复旧式数组语法为短数组语法
    // 例如: array(1, 2, 3) -> [1, 2, 3]
    $content = preg_replace('/array\s*\(\s*\)/', '[]', $content);
    
    // 修复in_array和array_key_exists等函数的括号错误
    $content = preg_replace('/in_\[([^]]+), ([^]]+)\]/', 'in_array($1, $2)', $content);
    $content = preg_replace('/array_key_exists\s*\[([^]]+), ([^]]+)\]/', 'array_key_exists($1, $2)', $content);
    
    return $content;
}

/**
 * 修复类引用操作符问题
 */
function fixClassReferenceOperator($content) {
    // 修复静态属性访问
    $content = preg_replace('/(\w+(?:\\\\w+)*)::\$(\w+)\[/', '$1::$$2[', $content);
    
    // 修复类常量访问
    $content = preg_replace('/(\w+(?:\\\\w+)*)::\[(\w+)\]/', '$1::$2', $content);
    
    return $content;
} 