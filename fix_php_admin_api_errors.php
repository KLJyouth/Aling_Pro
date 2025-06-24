<?php
/**
 * 修复Admin API文件中的语法错误
 * 
 * 主要修复以下问题：
 * 1. 行末多余的单引号和分号 (';)
 * 2. 全局范围内错误使用的public/private关键字
 * 3. 函数参数定义中的多余括号
 * 4. 注释中的"不可达代码"
 */

// 要处理的文件列表
$files = [
    'public/admin/api/users/index.php',
    'public/admin/api/monitoring/index.php',
    'public/admin/api/risk-control/index.php',
    'public/admin/api/third-party/index.php'
];

// 处理每个文件
foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "文件不存在: $file\n";
        continue;
    }

    echo "正在处理: $file\n";
    
    // 读取文件内容
    $content = file_get_contents($file);
    if ($content === false) {
        echo "无法读取文件: $file\n";
        continue;
    }
    
    // 创建备份
    $backupFile = $file . '.bak';
    if (!file_exists($backupFile)) {
        file_put_contents($backupFile, $content);
        echo "已创建备份: $backupFile\n";
    }
    
    // 1. 修复行末多余的单引号和分号 (';)
    $content = str_replace("';\n", "\n", $content);
    
    // 2. 移除全局范围内的public/private关键字
    $content = preg_replace('/^(public|private)\s+(\$[a-zA-Z0-9_]+\s*=)/m', '$2', $content);
    
    // 3. 修复函数参数定义中的多余括号
    $content = preg_replace('/function\s+([a-zA-Z0-9_]+)\s*\(\(([^)]*)\)\)/m', 'function $1($2)', $content);
    
    // 4. 修复注释中的"不可达代码"
    $content = preg_replace('/\/\/\s*\}\);[\s\n]*\/\/\s*不可达代码/m', '});', $content);
    
    // 保存修复后的内容
    if (file_put_contents($file, $content)) {
        echo "已修复: $file\n";
    } else {
        echo "无法写入文件: $file\n";
    }
}

echo "修复完成\n"; 