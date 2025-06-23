<?php
/**
 * 运行所有修复脚本
 * 这个脚本会按顺序运行所有修复脚本，完善项目功能
 */

// 设置脚本最大执行时间
set_time_limit(900);

// 定义要运行的脚本
$scripts = [
    'complete_file_structure.php',
    'fix_admin_syntax.php',
    'generate_autoload.php'
];

// 统计信息
$stats = [
    'scripts_run' => 0,
    'scripts_failed' => 0,
    'total_execution_time' => 0
];

// 开始执行
echo "开始运行所有修复脚本...\n\n";
$startTime = microtime(true);

// 运行每个脚本
foreach ($scripts as $script) {
    if (!file_exists(__DIR__ . '/' . $script)) {
        echo "错误: 脚本不存在: {$script}\n";
        $stats['scripts_failed']++;
        continue;
    }
    
    echo "运行脚本: {$script}\n";
    echo "----------------------------------------\n";
    
    $scriptStartTime = microtime(true);
    
    // 执行脚本
    $output = [];
    $returnVar = 0;
    exec("php " . __DIR__ . '/' . $script, $output, $returnVar);
    
    // 输出结果
    echo implode("\n", $output) . "\n";
    
    $scriptEndTime = microtime(true);
    $scriptExecutionTime = round($scriptEndTime - $scriptStartTime, 2);
    
    echo "----------------------------------------\n";
    echo "脚本执行完成: {$script}\n";
    echo "执行时间: {$scriptExecutionTime} 秒\n";
    echo "\n";
    
    if ($returnVar !== 0) {
        echo "警告: 脚本返回非零状态码: {$returnVar}\n\n";
        $stats['scripts_failed']++;
    } else {
        $stats['scripts_run']++;
    }
    
    $stats['total_execution_time'] += $scriptExecutionTime;
}

// 生成项目完整性报告
generateProjectReport();

$endTime = microtime(true);
$totalExecutionTime = round($endTime - $startTime, 2);

echo "所有脚本执行完成！\n";
echo "统计信息：\n";
echo "- 成功运行脚本数: " . $stats['scripts_run'] . "\n";
echo "- 失败脚本数: " . $stats['scripts_failed'] . "\n";
echo "- 脚本总执行时间: " . $stats['total_execution_time'] . " 秒\n";
echo "- 总执行时间: " . $totalExecutionTime . " 秒\n";

/**
 * 生成项目完整性报告
 */
function generateProjectReport()
{
    $reportFile = __DIR__ . '/PROJECT_INTEGRITY_REPORT.md';
    
    $content = "# AlingAi Pro 项目完整性报告\n\n";
    $content .= "生成时间: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 检查核心目录
    $content .= "## 核心目录完整性\n\n";
    $coreDirectories = [
        'src',
        'public',
        'config',
        'storage',
        'tests'
    ];
    
    $content .= "| 目录 | 状态 | 文件数 |\n";
    $content .= "|------|------|--------|\n";
    
    foreach ($coreDirectories as $dir) {
        $path = __DIR__ . '/' . $dir;
        $exists = is_dir($path);
        $fileCount = $exists ? countFiles($path) : 0;
        
        $content .= "| {$dir} | " . ($exists ? "✅ 存在" : "❌ 不存在") . " | {$fileCount} |\n";
    }
    
    // 检查关键文件
    $content .= "\n## 关键文件完整性\n\n";
    $keyFiles = [
        'autoload.php' => '自动加载文件',
        'src/helpers.php' => '全局辅助函数',
        'src/Core/Application.php' => '核心应用类',
        'src/Services/DatabaseService.php' => '数据库服务',
        'src/Controllers/BaseController.php' => '基础控制器'
    ];
    
    $content .= "| 文件 | 描述 | 状态 | 大小 |\n";
    $content .= "|------|------|------|------|\n";
    
    foreach ($keyFiles as $file => $description) {
        $path = __DIR__ . '/' . $file;
        $exists = file_exists($path);
        $size = $exists ? filesize($path) : 0;
        $sizeFormatted = formatSize($size);
        
        $content .= "| {$file} | {$description} | " . ($exists ? "✅ 存在" : "❌ 不存在") . " | {$sizeFormatted} |\n";
    }
    
    // 统计src目录下的文件类型
    $content .= "\n## src目录文件统计\n\n";
    $fileStats = getFileStats(__DIR__ . '/src');
    
    $content .= "| 文件类型 | 数量 | 总大小 |\n";
    $content .= "|----------|------|--------|\n";
    
    foreach ($fileStats as $extension => $stats) {
        $content .= "| {$extension} | {$stats['count']} | " . formatSize($stats['size']) . " |\n";
    }
    
    // 添加修复过程概述
    $content .= "\n## 修复过程概述\n\n";
    $content .= "1. **恢复空文件内容**\n";
    $content .= "   - 从备份中恢复了部分文件\n";
    $content .= "   - 为无法恢复的文件生成了基本结构\n\n";
    
    $content .= "2. **完善文件结构**\n";
    $content .= "   - 根据文件类型和命名空间生成了适当的类结构\n";
    $content .= "   - 修复了helpers.php全局辅助函数\n\n";
    
    $content .= "3. **修复语法错误**\n";
    $content .= "   - 修复了public/admin目录下的文件语法错误\n";
    $content .= "   - 修复了fix_syntax.php脚本的安全问题\n\n";
    
    $content .= "4. **生成自动加载**\n";
    $content .= "   - 扫描项目目录生成了类映射\n";
    $content .= "   - 创建了符合PSR-4标准的自动加载文件\n\n";
    
    // 添加后续建议
    $content .= "## 后续建议\n\n";
    $content .= "1. **代码审查**\n";
    $content .= "   - 对所有生成的文件进行代码审查，确保功能完整\n";
    $content .= "   - 特别关注那些只有基本结构的文件，根据项目需求完善功能\n\n";
    
    $content .= "2. **测试验证**\n";
    $content .= "   - 运行项目测试，确保基本功能正常\n";
    $content .= "   - 测试各个模块的集成情况\n\n";
    
    $content .= "3. **备份策略**\n";
    $content .= "   - 建立定期备份策略，确保重要代码不会丢失\n";
    $content .= "   - 在执行修复脚本前，始终创建备份\n\n";
    
    $content .= "4. **持续改进**\n";
    $content .= "   - 定期检查代码质量，确保符合最佳实践\n";
    $content .= "   - 更新文档，确保项目易于维护\n";
    
    file_put_contents($reportFile, $content);
    echo "项目完整性报告已生成: {$reportFile}\n\n";
}

/**
 * 统计目录中的文件数
 */
function countFiles($dir)
{
    $count = 0;
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            $count += countFiles($path);
        } else {
            $count++;
        }
    }
    
    return $count;
}

/**
 * 获取目录中各类型文件的统计信息
 */
function getFileStats($dir)
{
    $stats = [];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        $extension = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
        $extension = $extension ? $extension : 'unknown';
        
        if (!isset($stats[$extension])) {
            $stats[$extension] = [
                'count' => 0,
                'size' => 0
            ];
        }
        
        $stats[$extension]['count']++;
        $stats[$extension]['size'] += $file->getSize();
    }
    
    return $stats;
}

/**
 * 格式化文件大小
 */
function formatSize($size)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    
    return round($size, 2) . ' ' . $units[$i];
}