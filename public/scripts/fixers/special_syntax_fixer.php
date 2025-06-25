<?php
/**
 * 特殊文件修复脚本 - 修复index.php中的语法问题
 */

function fixSpecialSyntaxIssues() {
    $indexFile = 'public/index.php';
    
    if (!file_exists($indexFile)) {
        echo "�?文件不存�? $indexFile\n";
        return false;
    }
    
    $content = file_get_contents($indexFile];
    $originalContent = $content;
    
    // 修复private语句在全局作用域的问题
    $content = preg_replace('/private\s+\$isProduction\s*=\s*\([^;]+\];\s*\';\s*/', '$isProduction = (getenv(\'APP_ENV\') === \'production\'];' . "\n", $content];
    
    // 修复多余的引号和分号
    $content = preg_replace('/\'\s*;\s*\n/', "\n", $content];
    
    // 修复被破坏的字符�?
    $content = preg_replace('/echo\s*"<([^>]+)>"\s*\.\s*([^;]+)\s*\.\s*"<\/([^>]+)>"\s*;/', 'echo "<$1>" . $2 . "</$3>";', $content];
    
    // 修复sprintf格式字符串问�?
    $content = preg_replace('/\[%s\]\s*([^,]+],/', '[%s] $1,', $content];
    
    // 保存修复后的文件
    if ($content !== $originalContent) {
        file_put_contents($indexFile, $content];
        echo "�?修复文件: $indexFile\n";
        return true;
    } else {
        echo "ℹ️ 文件无需修复: $indexFile\n";
        return true;
    }
}

echo "🔧 开始修复特殊语法问�?..\n";
$result = fixSpecialSyntaxIssues(];

if ($result) {
    echo "🎉 特殊语法问题修复完成！\n";
} else {
    echo "�?修复失败！\n";
}
?>
