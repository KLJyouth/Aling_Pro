<?php
/**
 * 修复fix_syntax.php脚本中的正则表达式错�?
 */

$fixSyntaxFile = __DIR__ . '/fix_syntax.php';

if (!file_exists($fixSyntaxFile)) {
    echo "错误: fix_syntax.php文件不存在\n";
    exit(1];
}

echo "开始修复fix_syntax.php中的正则表达式错�?..\n";

$content = file_get_contents($fixSyntaxFile];
$originalContent = $content;

// 修复正则表达式中缺少结束分隔符的问题
$fixedPatterns = [
    // 1. 修复构造函数中的重复括�?
    '/function\s+__construct\s*\(\(([^)]*)\)\)/' => '/function\s+__construct\s*\(\(([^)]*)\)\)/s',
    
    // 2. 修复函数内部错误使用private关键�?
    '/(function\s+[a-zA-Z0-9_]+\s*\([^)]*\)\s*{[^}]*?)private\s+(\$[a-zA-Z0-9_]+)/s' => '/(function\s+[a-zA-Z0-9_]+\s*\([^)]*\)\s*{[^}]*?)private\s+(\$[a-zA-Z0-9_]+)/s',
    
    // 3. 修复行尾多余的单引号和分�?
    '/\';\s*$/m' => '/\';\s*$/m',
    '/";$/m' => '/";$/m',
    
    // 4. 修复函数参数中的多余括号
    '/function\s+([a-zA-Z0-9_]+)\s*\(\(\)\)/' => '/function\s+([a-zA-Z0-9_]+)\s*\(\(\)\)/s',
    
    // 5. 修复函数参数中的多余括号 - 带参数版�?
    '/function\s+([a-zA-Z0-9_]+)\s*\(\(([^)]+)\)\)/' => '/function\s+([a-zA-Z0-9_]+)\s*\(\(([^)]+)\)\)/s',
    
    // 6. 修复数组语法错误 - 行尾多余的引号和分号
    '/=>([^,\n\r\]]*],\';/' => '/=>([^,\n\r\]]*],\';/s',
    
    // 7. 修复命名空间一致性问�?
    '/namespace\s+AlingAI\\/' => '/namespace\s+AlingAI\\\\/s',
    '/namespace\s+AlingAiPro\\/' => '/namespace\s+AlingAiPro\\\\/s',
    '/use\s+AlingAI\\/' => '/use\s+AlingAI\\\\/s',
    '/use\s+AlingAiPro\\/' => '/use\s+AlingAiPro\\\\/s',
];

foreach ($fixedPatterns as $oldPattern => $newPattern) {
    $content = str_replace($oldPattern, $newPattern, $content];
}

// 修复try-catch模式
$tryPattern = '/try\s*{[^}]*}\s*(?!catch|finally)/s';
$content = str_replace($tryPattern, '/try\s*{[^}]*}\s*(?!catch|finally)/s', $content];

// 修复魔术方法中的重复括号
$magicMethodPattern1 = '/function\s+__([a-zA-Z0-9_]+)\s*\(\(\)\)/';
$content = str_replace($magicMethodPattern1, '/function\s+__([a-zA-Z0-9_]+)\s*\(\(\)\)/s', $content];

$magicMethodPattern2 = '/function\s+__([a-zA-Z0-9_]+)\s*\(\(([^)]+)\)\)/';
$content = str_replace($magicMethodPattern2, '/function\s+__([a-zA-Z0-9_]+)\s*\(\(([^)]+)\)\)/s', $content];

// 保存修复后的文件
if ($content !== $originalContent) {
    file_put_contents($fixSyntaxFile, $content];
    echo "�?成功修复fix_syntax.php中的正则表达式错误\n";
} else {
    echo "⚠️ 未发现需要修复的正则表达式错误\n";
}

echo "\n修复完成，请检查fix_syntax.php文件\n"; 
