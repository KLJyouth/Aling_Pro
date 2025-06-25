<?php
/**
 * 最终清理修复脚�?- 修复残留的语法问�?
 */

function finalCleanupFix() {
    $files = [
        'bootstrap/app.php',
        'public/index.php'
    ];
    
    $fixedCount = 0;
    
    foreach ($files as $file) {
        if (!file_exists($file)) {
            echo "�?文件不存�? $file\n";
            continue;
        }
        
        $content = file_get_contents($file];
        $originalContent = $content;
        
        // 修复所有残留的 '; 模式
        $content = preg_replace('/\'\s*;\s*\n/', "\n", $content];
        $content = preg_replace('/\'\s*;\s*/', '', $content];
        
        // 修复多余的空�?
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content];
        
        // 修复缺少的分�?
        $content = preg_replace('/\)\s*\n\s*}/', "];\n}", $content];
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content];
            echo "�?修复文件: $file\n";
            $fixedCount++;
            
            // 验证语法
            $output = [];
            $returnCode = 0;
            exec("php -l \"$file\" 2>&1", $output, $returnCode];
            
            if ($returnCode === 0) {
                echo "   �?语法验证通过\n";
            } else {
                echo "   �?语法验证失败: " . implode("\n", $output) . "\n";
            }
        } else {
            echo "ℹ️ 文件无需修复: $file\n";
        }
    }
    
    return $fixedCount;
}

echo "🧹 开始最终清理修�?..\n";
$fixedCount = finalCleanupFix(];

echo "\n🎉 最终清理修复完成！修复�?$fixedCount 个文件\n";

// 验证核心文件语法
echo "\n🔍 验证核心文件语法...\n";
$coreFiles = [
    'public/index.php',
    'bootstrap/app.php'
];

$allPassed = true;
foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        $output = [];
        $returnCode = 0;
        exec("php -l \"$file\" 2>&1", $output, $returnCode];
        
        if ($returnCode === 0) {
            echo "�?$file - 语法正确\n";
        } else {
            echo "�?$file - 语法错误: " . implode("\n", $output) . "\n";
            $allPassed = false;
        }
    }
}

if ($allPassed) {
    echo "\n🎉 所有核心文件语法验证通过！\n";
} else {
    echo "\n⚠️ 仍有文件存在语法问题，需要手动检查\n";
}
?>
