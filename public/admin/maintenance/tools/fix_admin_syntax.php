<?php
/**
 * 修复public/admin目录中的语法错误文件
 * 这个脚本会修复SystemManagerClean.php和SystemManager_Fixed.php文件中的语法错误
 */

// 设置脚本最大执行时�?
set_time_limit(300];

// 定义文件路径
$files = [
    __DIR__ . '/public/admin/SystemManagerClean.php',
    __DIR__ . '/public/admin/SystemManager_Fixed.php'
];

// 统计信息
$stats = [
    'files_scanned' => 0,
    'files_fixed' => 0,
    'errors' => 0
];

// 开始执�?
echo "开始修复public/admin目录中的语法错误文件...\n";
$startTime = microtime(true];

// 创建备份目录
$backupDir = __DIR__ . '/backup/admin_syntax_fix_' . date('Ymd_His'];
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true];
}

// 处理每个文件
foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "文件不存�? {$file}\n";
        continue;
    }
    
    $stats['files_scanned']++;
    
    // 创建备份
    $backupFile = $backupDir . '/' . basename($file];
    copy($file, $backupFile];
    
    echo "处理文件: {$file}\n";
    
    try {
        // 读取文件内容
        $content = file_get_contents($file];
        $originalContent = $content;
        
        // 1. 修复构造函数中的重复括�?
        $pattern = '/function\s+__construct\s*\(\(([^)]*)\)\)/s';
        $replacement = 'function __construct($1)';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 2. 修复函数内部错误使用private关键�?
        $pattern = '/(function\s+[a-zA-Z0-9_]+\s*\([^)]*\)\s*{[^}]*?)private\s+(\$[a-zA-Z0-9_]+)/s';
        $replacement = '$1$2';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 3. 修复行尾多余的单引号和分�?
        $pattern = '/\';\s*$/m';
        $replacement = '\'';
        $content = preg_replace($pattern, $replacement, $content];
        
        $pattern = '/";$/m';
        $replacement = '"';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 4. 修复函数参数中的多余括号
        $pattern = '/function\s+([a-zA-Z0-9_]+)\s*\(\(\)\)/s';
        $replacement = 'function $1()';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 5. 修复函数参数中的多余括号 - 带参数版�?
        $pattern = '/function\s+([a-zA-Z0-9_]+)\s*\(\(([^)]+)\)\)/s';
        $replacement = 'function $1($2)';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 6. 修复数组语法错误 - 行尾多余的引号和分号
        $pattern = '/=>([^,\n\r\]]*],\';/s';
        $replacement = '=>$1,';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 7. 修复命名空间一致性问�?
        $pattern = '/namespace\s+AlingAI\\\\/s';
        $replacement = 'namespace AlingAi\\\\';
        $content = preg_replace($pattern, $replacement, $content];
        
        $pattern = '/namespace\s+AlingAiPro\\\\/s';
        $replacement = 'namespace AlingAi\\\\';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 8. 修复缺少对应catch块的try语句
        $tryPattern = '/try\s*{[^}]*}\s*(?!catch|finally)/s';
        if (preg_match($tryPattern, $content)) {
            $content = preg_replace($tryPattern, '$0 catch (\Exception $e) { /* 自动添加的catch�?*/ }', $content];
        }
        
        // 9. 修复魔术方法中的重复括号
        $pattern = '/function\s+__([a-zA-Z0-9_]+)\s*\(\(\)\)/s';
        $replacement = 'function __$1()';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 10. 修复魔术方法中的重复括号 - 带参数版�?
        $pattern = '/function\s+__([a-zA-Z0-9_]+)\s*\(\(([^)]+)\)\)/s';
        $replacement = 'function __$1($2)';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 11. 修复public function声明为function
        $pattern = '/public\s+function\s+([a-zA-Z0-9_]+)/s';
        $replacement = 'function $1';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 12. 修复private/protected变量声明
        $pattern = '/private\s+(\$[a-zA-Z0-9_]+)/s';
        $replacement = '$1';
        $content = preg_replace($pattern, $replacement, $content];
        
        $pattern = '/protected\s+(\$[a-zA-Z0-9_]+)/s';
        $replacement = '$1';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 13. 修复多余的分�?
        $pattern = '/;\s*;/';
        $replacement = ';';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 14. 修复错误的数组语�?
        $pattern = '/array\s*\(\s*\[\s*/';
        $replacement = '[';
        $content = preg_replace($pattern, $replacement, $content];
        
        $pattern = '/\s*\]\s*\)/';
        $replacement = ')';
        $content = preg_replace($pattern, $replacement, $content];
        
        // 15. 修复未闭合的引号
        $content = preg_replace_callback('/(["\'])(?:(?=(\\\\?))\2.)*?\1/', function($matches) {
            return $matches[0];
        }, $content];
        
        // 如果内容有变化，保存文件
        if ($content !== $originalContent) {
            file_put_contents($file, $content];
            $stats['files_fixed']++;
            echo "已修复文�? {$file}\n";
            
            // 检查语法错�?
            $output = [];
            $returnVar = 0;
            exec("php -l {$file}", $output, $returnVar];
            
            if ($returnVar !== 0) {
                echo "警告: 文件仍存在语法错�? {$file}\n";
                echo implode("\n", $output) . "\n";
                
                // 尝试进一步修�?
                echo "尝试进一步修�?..\n";
                
                // 读取文件内容
                $content = file_get_contents($file];
                
                // 16. 修复PHP标签
                if (strpos($content, '<?php') === false) {
                    $content = "<?php\n" . $content;
                }
                
                // 17. 确保文件以PHP标签开�?
                $content = preg_replace('/^[^<]*(<\?php)/s', '$1', $content];
                
                // 18. 移除所有HTML标签
                $content = preg_replace('/<[^?][^>]*>/', '', $content];
                
                // 19. 修复未闭合的大括�?
                $openBraces = substr_count($content, '{'];
                $closeBraces = substr_count($content, '}'];
                
                if ($openBraces > $closeBraces) {
                    $diff = $openBraces - $closeBraces;
                    $content .= str_repeat("\n}", $diff];
                }
                
                // 保存修复后的文件
                file_put_contents($file, $content];
                
                // 再次检查语法错�?
                $output = [];
                $returnVar = 0;
                exec("php -l {$file}", $output, $returnVar];
                
                if ($returnVar === 0) {
                    echo "成功修复文件: {$file}\n";
                } else {
                    echo "警告: 文件仍存在语法错误，可能需要手动修�? {$file}\n";
                    echo implode("\n", $output) . "\n";
                }
            } else {
                echo "文件语法检查通过: {$file}\n";
            }
        } else {
            echo "文件没有需要修复的语法错误: {$file}\n";
        }
    } catch (\Exception $e) {
        $stats['errors']++;
        echo "错误: " . $e->getMessage() . " - " . $file . "\n";
    }
}

$endTime = microtime(true];
$executionTime = round($endTime - $startTime, 2];

echo "\n完成！\n";
echo "统计信息：\n";
echo "- 扫描文件�? " . $stats['files_scanned'] . "\n";
echo "- 修复文件�? " . $stats['files_fixed'] . "\n";
echo "- 错误�? " . $stats['errors'] . "\n";
echo "- 执行时间: " . $executionTime . " 秒\n";

