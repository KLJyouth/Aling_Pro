<?php
/**
 * 生成项目的自动加载文件
 * 这个脚本会扫描src目录，并生成自动加载类的配置
 */

// 设置脚本最大执行时间
set_time_limit(300);

// 源代码目录
$srcDir = __DIR__ . '/src';
$autoloadFile = __DIR__ . '/autoload.php';

// 统计信息
$stats = [
    'files_scanned' => 0,
    'classes_found' => 0,
    'directories_processed' => 0
];

// 类映射数组
$classMap = [];

// 递归扫描目录
function scanDirectory($dir, &$stats, &$classMap)
{
    $items = scandir($dir);
    $stats['directories_processed']++;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            scanDirectory($path, $stats, $classMap);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            scanFile($path, $stats, $classMap);
        }
    }
}

// 扫描文件，提取类名和命名空间
function scanFile($filePath, &$stats, &$classMap)
{
    $stats['files_scanned']++;
    
    $content = file_get_contents($filePath);
    
    // 提取命名空间
    preg_match('/namespace\s+([\w\\\\]+)/', $content, $nsMatches);
    $namespace = isset($nsMatches[1]) ? $nsMatches[1] : '';
    
    // 提取类名
    preg_match('/class\s+(\w+)/', $content, $classMatches);
    
    if (isset($classMatches[1])) {
        $className = $classMatches[1];
        $stats['classes_found']++;
        
        // 生成完全限定类名
        $fullyQualifiedClassName = $namespace ? $namespace . '\\' . $className : $className;
        
        // 添加到类映射
        $classMap[$fullyQualifiedClassName] = $filePath;
    }
}

// 生成自动加载文件
function generateAutoloadFile($classMap, $autoloadFile)
{
    $content = "<?php\n\n/**\n * AlingAi Pro 自动加载文件\n * 由generate_autoload.php脚本自动生成\n * 生成时间: " . date('Y-m-d H:i:s') . "\n */\n\n";
    
    $content .= "spl_autoload_register(function (\$class) {\n";
    $content .= "    // 类映射\n";
    $content .= "    \$classMap = [\n";
    
    foreach ($classMap as $class => $file) {
        $relativePath = str_replace(__DIR__ . '/', '', $file);
        $content .= "        '" . addslashes($class) . "' => __DIR__ . '/" . addslashes($relativePath) . "',\n";
    }
    
    $content .= "    ];\n\n";
    $content .= "    if (isset(\$classMap[\$class])) {\n";
    $content .= "        require \$classMap[\$class];\n";
    $content .= "        return true;\n";
    $content .= "    }\n\n";
    
    $content .= "    // PSR-4自动加载\n";
    $content .= "    \$prefix = 'AlingAi\\\\';\n";
    $content .= "    \$base_dir = __DIR__ . '/src/';\n\n";
    
    $content .= "    // 检查类是否使用前缀\n";
    $content .= "    \$len = strlen(\$prefix);\n";
    $content .= "    if (strncmp(\$prefix, \$class, \$len) !== 0) {\n";
    $content .= "        return false;\n";
    $content .= "    }\n\n";
    
    $content .= "    // 获取相对类名\n";
    $content .= "    \$relative_class = substr(\$class, \$len);\n\n";
    
    $content .= "    // 将命名空间前缀替换为基础目录，用目录分隔符替换命名空间分隔符，\n";
    $content .= "    // 附加.php\n";
    $content .= "    \$file = \$base_dir . str_replace('\\\\', '/', \$relative_class) . '.php';\n\n";
    
    $content .= "    // 如果文件存在，加载它\n";
    $content .= "    if (file_exists(\$file)) {\n";
    $content .= "        require \$file;\n";
    $content .= "        return true;\n";
    $content .= "    }\n\n";
    
    $content .= "    return false;\n";
    $content .= "});\n\n";
    
    $content .= "// 加载全局辅助函数\n";
    $content .= "require_once __DIR__ . '/src/helpers.php';\n";
    
    file_put_contents($autoloadFile, $content);
}

// 开始执行
echo "开始生成自动加载文件...\n";
$startTime = microtime(true);

// 扫描目录
scanDirectory($srcDir, $stats, $classMap);

// 生成自动加载文件
generateAutoloadFile($classMap, $autoloadFile);

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "完成！\n";
echo "统计信息：\n";
echo "- 扫描文件数: " . $stats['files_scanned'] . "\n";
echo "- 发现类数: " . $stats['classes_found'] . "\n";
echo "- 处理目录数: " . $stats['directories_processed'] . "\n";
echo "- 执行时间: " . $executionTime . " 秒\n";
echo "自动加载文件已生成: " . $autoloadFile . "\n";