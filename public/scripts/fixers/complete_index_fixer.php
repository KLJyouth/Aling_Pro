<?php
/**
 * 完整的index.php修复脚本
 */

function fixIndexPhpCompletely() {
    $indexFile = 'public/index.php';
    
    if (!file_exists($indexFile)) {
        echo "�?文件不存�? $indexFile\n";
        return false;
    }
    
    $content = file_get_contents($indexFile];
    $originalContent = $content;
    
    // 1. 修复require语句缺少分号
    $content = preg_replace('/require_once\s+APP_ROOT\s*\.\s*\'\/vendor\/autoload\.php\s*$/', "require_once APP_ROOT . '/vendor/autoload.php';", $content];
    
    // 2. 修复private在全局作用域的问题
    $content = preg_replace('/private\s+\$([a-zA-Z0-9_]+)\s*=/', '$\1 =', $content];
    
    // 3. 修复注释中的分号
    $content = preg_replace('/\/\/\s*([^)]+)\];/', '// $1', $content];
    
    // 4. 修复多余的引号和分号模式
    $content = preg_replace('/\'\s*;\s*\n/', "\n", $content];
    $content = preg_replace('/;\s*\'\s*;\s*/', ';', $content];
    
    // 5. 修复break语句
    $content = preg_replace('/echo\s+([^;]+];\s*break\s*;\s*\'\s*;/', 'echo $1; break;', $content];
    
    // 6. 修复if条件语句
    $content = preg_replace('/if\s*\(\s*([^)]+)\)\s*\{\s*\'\s*;/', 'if ($1) {', $content];
    
    // 7. 修复echo语句
    $content = preg_replace('/echo\s+"([^"]+)"\s*\.\s*([^;]+)\s*\.\s*"([^"]+)"\s*;\s*\'\s*;/', 'echo "$1" . $2 . "$3";', $content];
    
    // 8. 清理多余的空�?
    $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content];
    
    // 保存修复后的文件
    if ($content !== $originalContent) {
        file_put_contents($indexFile, $content];
        echo "�?修复文件: $indexFile\n";
        
        // 显示修复的部分内�?
        echo "📝 修复了以下问题：\n";
        echo "   - require语句缺少分号\n";
        echo "   - private在全局作用域\n";
        echo "   - 多余的引号和分号\n";
        echo "   - 破损的字符串\n";
        
        return true;
    } else {
        echo "ℹ️ 文件无需修复: $indexFile\n";
        return true;
    }
}

echo "🔧 开始完整修复index.php...\n";
$result = fixIndexPhpCompletely(];

if ($result) {
    echo "🎉 index.php完整修复完成！\n";
    
    // 进行语法检�?
    echo "🔍 进行语法验证...\n";
    $output = [];
    $returnCode = 0;
    exec('php -l public/index.php 2>&1', $output, $returnCode];
    
    if ($returnCode === 0) {
        echo "�?语法验证通过！\n";
    } else {
        echo "�?语法验证失败：\n";
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "�?修复失败！\n";
}
?>
