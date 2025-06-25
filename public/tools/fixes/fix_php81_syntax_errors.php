<?php
/**
 * PHP 8.1语法错误修复脚本
 * 
 * 此脚本修复常见的PHP 8.1语法错误，特别是图片中显示的问题
 */

echo "PHP 8.1语法错误修复脚本\n";
echo "============================\n\n";

// 定义要处理的目录
$directories = [
    'ai-engines',
    'apps',
    'completed/config',
    'config',
    'public',
    'src',
    'tests'
];

// 错误类型及修复策�?
$errorPatterns = [
    // 私有属性缺少变量名
    [
        'pattern' => '/private\s+([a-zA-Z_\\\\\[\]]+)(?!\s*\$)/',
        'replacement' => 'private $1 $var',
        'description' => '私有属性缺少变量名'
    ], 
    // 对象方法调用缺少->操作�?
    [
        'pattern' => '/(\$[a-zA-Z0-9_]+)(?!\s*->|\s*=|\s*\()([a-zA-Z0-9_]+)/',
        'replacement' => '$1->$2',
        'description' => '对象方法调用缺少->操作�?
    ], 
    // 配置值缺少引�?
    [
        'pattern' => '/([\'"][a-zA-Z_]+[\'"]\s*=>\s*)(?![\'"\[])([a-zA-Z0-9_.]+)/',
        'replacement' => '$1\'$2\'',
        'description' => '配置值缺少引�?
    ], 
    // 类引用缺少命名空�?
    [
        'pattern' => '/([^\\\\])([A-Z][a-zA-Z0-9_]+)::class/',
        'replacement' => '$1\\\\$2::class',
        'description' => '类引用缺少命名空�?
    ], 
    // 方法参数类型缺少变量�?
    [
        'pattern' => '/function\s+[a-zA-Z0-9_]+\s*\(\s*([a-zA-Z_\\\\\[\]]+)\s+(?!\$)/',
        'replacement' => 'function $1($2 $param',
        'description' => '方法参数类型缺少变量�?
    ], 
    // 命名空间格式问题
    [
        'pattern' => '/namespace\s+(?![a-zA-Z\\\\])/',
        'replacement' => 'namespace \\',
        'description' => '命名空间格式问题'
    ], 
    // 引号问题
    [
        'pattern' => '/([\'"]).*((?<!\\\\)\1)/',
        'replacement' => '$1$2',
        'description' => '字符串引号不匹配'
    ]
];

// 修复文件中的语法错误
function fixPhpFile($filePath, $errorPatterns) {
    if (!file_exists($filePath)) {
        return ['status' => false, 'message' => "文件不存�? {$filePath}"];
    }
    
    $content = file_get_contents($filePath];
    if ($content === false) {
        return ['status' => false, 'message' => "无法读取文件: {$filePath}"];
    }
    
    $modified = false;
    $fixes = [];
    
    // 按行处理文件
    $lines = explode("\n", $content];
    foreach ($lines as $lineNumber => $line) {
        $originalLine = $line;
        
        // 应用所有错误模�?
        foreach ($errorPatterns as $pattern) {
            if (preg_match($pattern['pattern'],  $line)) {
                $newLine = preg_replace($pattern['pattern'],  $pattern['replacement'],  $line];
                if ($newLine !== $line) {
                    $lines[$lineNumber] = $newLine;
                    $fixes[] = [
                        'line' => $lineNumber + 1,
                        'description' => $pattern['description'], 
                        'before' => $line,
                        'after' => $newLine
                    ];
                    $modified = true;
                    $line = $newLine; // 更新当前行以便应用下一个模�?
                }
            }
        }
        
        // 特殊处理: ChineseTokenizer.php中的UTF-8字符问题
        if (basename($filePath) === 'ChineseTokenizer.php' && preg_match('/["\'](江苏)["\']/', $line)) {
            $newLine = preg_replace('/["\'](江苏)["\']/', '"JiangSu"', $line];
            if ($newLine !== $line) {
                $lines[$lineNumber] = $newLine;
                $fixes[] = [
                    'line' => $lineNumber + 1,
                    'description' => 'UTF-8字符编码问题',
                    'before' => $line,
                    'after' => $newLine
                ];
                $modified = true;
            }
        }
    }
    
    // 如果有修改，写回文件
    if ($modified) {
        file_put_contents($filePath, implode("\n", $lines)];
        return ['status' => true, 'fixes' => $fixes];
    }
    
    return ['status' => false, 'message' => "文件无需修改: {$filePath}"];
}

// 查找PHP文件并修复语法错�?
function findAndFixPhpFiles($directories, $errorPatterns) {
    $stats = [
        'processed' => 0,
        'fixed' => 0,
        'fixes' => []
    ];
    
    foreach ($directories as $dir) {
        $dir = rtrim($dir, '/\\'];
        
        if (!is_dir($dir)) {
            echo "目录不存�? {$dir}\n";
            continue;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS],
            RecursiveIteratorIterator::SELF_FIRST
        ];
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $stats['processed']++;
                $filePath = $file->getPathname(];
                
                echo "处理文件: {$filePath} ... ";
                $result = fixPhpFile($filePath, $errorPatterns];
                
                if ($result['status']) {
                    $stats['fixed']++;
                    $stats['fixes'][$filePath] = $result['fixes'];
                    $fixCount = count($result['fixes']];
                    echo "已修�?{$fixCount} 个问题\n";
                    
                    // 输出详细修复信息
                    foreach ($result['fixes'] as $fix) {
                        echo "  - �?{$fix['line']}: {$fix['description']}\n";
                        echo "    �? {$fix['before']}\n";
                        echo "    �? {$fix['after']}\n";
                    }
                } else {
                    echo "{$result['message']}\n";
                }
            }
        }
    }
    
    return $stats;
}

// 生成修复报告
function generateReport($stats) {
    $totalFixes = 0;
    foreach ($stats['fixes'] as $fileFixes) {
        $totalFixes += count($fileFixes];
    }
    
    $report = <<<REPORT
# PHP 8.1语法错误修复报告

## 修复概要
- 处理文件�? {$stats['processed']}
- 修复文件�? {$stats['fixed']}
- 修复问题�? {$totalFixes}

## 修复的问题类�?
1. 私有属性缺少变量名
2. 对象方法调用缺少->操作�?
3. 配置值缺少引�?
4. 类引用缺少命名空间前缀
5. 方法参数类型缺少变量�?
6. 命名空间格式问题
7. 字符串引号不匹配
8. UTF-8字符编码问题

## 修复详情

REPORT;

    foreach ($stats['fixes'] as $file => $fixes) {
        $report .= "### " . basename($file) . "\n";
        $report .= "文件路径: {$file}\n\n";
        
        foreach ($fixes as $fix) {
            $report .= "- �?{$fix['line']}: {$fix['description']}\n";
            $report .= "  - 修改�? `" . htmlspecialchars($fix['before']) . "`\n";
            $report .= "  - 修改�? `" . htmlspecialchars($fix['after']) . "`\n\n";
        }
    }
    
    $report .= <<<REPORT

## PHP 8.1语法注意事项
- 类型声明必须明确指定变量�?
- 访问对象属�?方法必须使用 -> 操作�?
- 字符串常量应使用引号包围
- 类引用应包含完整命名空间路径

## 后续建议
- 使用PHP代码静态分析工具（如PHPStan�?
- 配置IDE自动检查PHP语法错误
- 建立代码审查流程以确保代码符合PHP 8.1语法规则
REPORT;

    file_put_contents('PHP_81_SYNTAX_FIX_REPORT.md', $report];
    echo "\n已生成修复报�? PHP_81_SYNTAX_FIX_REPORT.md\n";
}

// 执行修复
$startTime = microtime(true];
$stats = findAndFixPhpFiles($directories, $errorPatterns];
$endTime = microtime(true];
$executionTime = round($endTime - $startTime, 2];

echo "\n完成修复!\n";
echo "处理文件�? {$stats['processed']}\n";
echo "修复文件�? {$stats['fixed']}\n";
echo "执行时间: {$executionTime} 秒\n";

generateReport($stats]; 
