<?php
/**
 * 为空文件生成基本结构
 * 这个脚本会为src目录下的空文件生成基本的PHP类结构
 */

// 设置脚本最大执行时间
set_time_limit(300);

// 源代码目录
$srcDir = __DIR__ . '/src';

// 统计信息
$stats = [
    'empty_files' => 0,
    'generated_files' => 0,
    'failed_files' => 0
];

// 递归扫描目录
function scanDirectory($dir, &$stats) {
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            scanDirectory($path, $stats);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php' && filesize($path) === 0) {
            generateFileStructure($path, $stats);
        }
    }
}

// 根据文件路径生成命名空间
function generateNamespace($filePath) {
    $relativePath = str_replace(__DIR__ . '/src/', '', dirname($filePath));
    $namespace = str_replace('/', '\\', $relativePath);
    
    if (!empty($namespace)) {
        return "AlingAi\\{$namespace}";
    } else {
        return "AlingAi";
    }
}

// 根据文件名生成类名
function generateClassName($filePath) {
    return pathinfo(basename($filePath), PATHINFO_FILENAME);
}

// 生成文件结构
function generateFileStructure($filePath, &$stats) {
    $stats['empty_files']++;
    
    $namespace = generateNamespace($filePath);
    $className = generateClassName($filePath);
    
    // 检测是否是接口
    $isInterface = (strpos($className, 'Interface') !== false);
    
    // 生成文件内容
    $content = "<?php\n\n";
    $content .= "namespace {$namespace};\n\n";
    
    // 添加类注释
    $content .= "/**\n";
    $content .= " * {$className}\n";
    $content .= " *\n";
    $content .= " * @package {$namespace}\n";
    $content .= " */\n";
    
    if ($isInterface) {
        $content .= "interface {$className}\n{\n";
        $content .= "    // 接口方法定义\n";
        $content .= "}\n";
    } else {
        $content .= "class {$className}\n{\n";
        $content .= "    // 类属性和方法\n";
        $content .= "    \n";
        $content .= "    /**\n";
        $content .= "     * 构造函数\n";
        $content .= "     */\n";
        $content .= "    public function __construct()\n";
        $content .= "    {\n";
        $content .= "        // 初始化代码\n";
        $content .= "    }\n";
        $content .= "}\n";
    }
    
    try {
        file_put_contents($filePath, $content);
        $stats['generated_files']++;
        echo "生成文件结构: " . str_replace(__DIR__ . '/', '', $filePath) . "\n";
    } catch (Exception $e) {
        $stats['failed_files']++;
        echo "无法生成文件结构: " . str_replace(__DIR__ . '/', '', $filePath) . " - " . $e->getMessage() . "\n";
    }
}

echo "开始为空文件生成基本结构...\n";
$startTime = microtime(true);

scanDirectory($srcDir, $stats);

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "\n生成完成! 执行时间: {$executionTime} 秒\n";
echo "空文件数: {$stats['empty_files']}\n";
echo "成功生成: {$stats['generated_files']}\n";
echo "生成失败: {$stats['failed_files']}\n";

// 验证结果
$emptyFiles = 0;
$iterator = new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($iterator);

foreach ($files as $file) {
    if ($file->isFile() && $file->getSize() === 0) {
        $emptyFiles++;
    }
}

echo "\n验证结果:\n";
echo "仍有空文件: {$emptyFiles}\n";

if ($emptyFiles > 0) {
    echo "\n警告: 仍有 {$emptyFiles} 个空文件，可能需要进一步检查\n";
} else {
    echo "\n恭喜! 所有文件都有基本结构\n";
} 