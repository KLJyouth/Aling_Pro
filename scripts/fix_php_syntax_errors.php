<?php
/**
 * PHP 8.1语法修复工具
 * 
 * 主要修复以下PHP 8.1语法错误:
 * 1. 将错误的方括号 [] 改为正确的圆括号 () 作为函数调用结束符
 * 2. 修复字符串连接问题和引号不匹配问题
 * 3. 修复数组语法问题
 * 4. 修复其他PHP 8.1兼容性问题
 */

// 检查命令行参数
if ($argc < 2) {
    echo "用法：php fix_php_syntax_errors.php <目标文件或目录>\n";
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
    
    // 应用修复
    $content = fixBracketSyntax($content);
    $content = fixStringQuotes($content);
    $content = fixArraySyntax($content);
    $content = fixClassReferences($content);
    $content = fixCharacterEncoding($content);
    
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
 * 修复方括号语法错误
 */
function fixBracketSyntax($content) {
    // 修复函数调用中使用方括号作为结束符的问题
    $patterns = [
        // 简单函数调用
        '/(\b[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\s*\(([^)\]]*)\]/' => '$1($2)',
        
        // 方法调用 
        '/->(\b[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\s*\(([^)\]]*)\]/' => '->$1($2)',
        
        // 静态方法调用
        '/::(\b[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\s*\(([^)\]]*)\]/' => '::$1($2)',
        
        // declare语句
        '/declare\s*\(\s*strict_types\s*=\s*1\s*\]/' => 'declare(strict_types=1)',
        
        // 闭包调用
        '/\}\s*\(([^)\]]*)\]/' => '}($1)',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    return $content;
}

/**
 * 修复字符串引号问题
 */
function fixStringQuotes($content) {
    // 修复字符串引号不匹配的问题
    $patterns = [
        // 以单引号开始，双引号结束的字符串
        '/\'([^\'"]*)"/s' => '\'$1\'',
        
        // 以双引号开始，单引号结束的字符串
        '/"([^\'"]*)\'/' => '"$1"',
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
    // 修复数组访问中的错误
    $patterns = [
        // in_array函数错误调用
        '/\bin_\[([^,]+),\s*([^\]]+)\]/' => 'in_array($1, $2)',
        
        // array_key_exists函数错误调用
        '/\barray_key_exists\s*\[([^,]+),\s*([^\]]+)\]/' => 'array_key_exists($1, $2)',
        
        // is_[] 改为 is_array()
        '/is_\s*\[\s*([^\)]+)\s*\)/' => 'is_array($1)',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    return $content;
}

/**
 * 修复类引用操作符问题
 */
function fixClassReferences($content) {
    // 修复类常量和静态属性访问中的错误
    $patterns = [
        // 错误的类常量访问
        '/([a-zA-Z0-9_\\\\]+)::\[([a-zA-Z0-9_]+)\]/' => '$1::$2',
        
        // 错误的静态属性访问
        '/([a-zA-Z0-9_\\\\]+)::\$([a-zA-Z0-9_]+)\[/' => '$1::$$2[',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    return $content;
}

/**
 * 修复字符编码问题（中文字符）
 */
function fixCharacterEncoding($content) {
    // 常见中文乱码字符映射
    $encodingFixes = [
        '?' => '器',
        '?' => '模',
        '?' => '型',
        '?' => '识',
        '?' => '别',
        '?' => '码',
        '?' => '算',
        '?' => '法',
        '?' => '入',
        '?' => '分',
        '?' => '用',
        '?' => '空',
        '?' => '是',
        '?' => '能',
        '?' => '功',
        '?' => '提',
        '?' => '供',
        '?' => '据',
        '?' => '支',
        '?' => '持',
        '?' => '性',
        '?' => '解',
        '?' => '析',
        '?' => '检',
        '?' => '测',
        '?' => '图',
        '?' => '像',
        '?' => '对',
        '?' => '比',
        '?' => '生',
        '?' => '成',
        '?' => '时',
        '?' => '间',
        '?' => '版',
        '?' => '本',
        '?' => '系',
        '?' => '统',
        '?' => '学',
        '?' => '习',
        '?' => '库',
        '?' => '配',
        '?' => '置',
        '?' => '阈',
        '?' => '值',
        '?' => '限',
        '?' => '人',
        '?' => '脸',
        '?' => '情',
        '?' => '结',
        '?' => '构',
        '?' => '向',
        '?' => '量',
        '?' => '记',
        '?' => '录',
        '?' => '特',
        '?' => '征',
        '?' => '匹',
        '?' => '配',
        '?' => '加',
        '?' => '速',
        '?' => '路',
        '?' => '径',
        '?' => '点',
        '?' => '表',
        '?' => '情',
        '?' => '活',
        '?' => '体',
        '?' => '资',
        '?' => '源',
        '?' => '已',
        '?' => '释',
        '?' => '放',
        '?' => '名',
        '?' => '称',
        '?' => '缓',
        '?' => '存',
        '?' => '未',
        '?' => '载',
        '?' => '数',
        '?' => '面',
        '?' => '特',
        '?' => '征',
        '?' => '抛',
        '?' => '异',
        '?' => '常',
        '?' => '超',
        '?' => '标',
        '?' => '大',
        '?' => '小',
        '?' => '字',
        '?' => '符',
        '?' => '串',
        '?' => '类',
        '?' => '型',
        '?' => '期'
    ];

    foreach ($encodingFixes as $from => $to) {
        $content = str_replace($from, $to, $content);
    }

    return $content;
} 