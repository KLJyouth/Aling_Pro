<?php
/**
 * 项目代码分析脚本
 * 用于分析项目状态，识别需要优先完善的部分
 */

// 设置脚本最大执行时间
set_time_limit(300);

// 源代码目录
$srcDir = __DIR__ . '/src';
$testDir = __DIR__ . '/tests';

// 统计信息
$stats = [
    'total_files' => 0,
    'empty_files' => 0,
    'basic_structure_files' => 0,
    'complete_files' => 0,
    'test_files' => 0,
    'test_coverage' => 0,
    'directories' => []
];

// 代码完整性阈值（行数）
$basicStructureThreshold = 50;
$completeFileThreshold = 200;

// 递归扫描目录
function scanDirectory($dir, &$stats, $isTest = false)
{
    if (!is_dir($dir)) {
        return;
    }
    
    $dirName = basename($dir);
    if (!isset($stats['directories'][$dirName])) {
        $stats['directories'][$dirName] = [
            'total_files' => 0,
            'empty_files' => 0,
            'basic_structure_files' => 0,
            'complete_files' => 0,
            'priority_score' => 0
        ];
    }
    
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            scanDirectory($path, $stats, $isTest);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            if ($isTest) {
                $stats['test_files']++;
            } else {
                analyzeFile($path, $stats, $dirName);
            }
        }
    }
}

// 分析文件
function analyzeFile($filePath, &$stats, $dirName)
{
    global $basicStructureThreshold, $completeFileThreshold;
    
    $stats['total_files']++;
    $stats['directories'][$dirName]['total_files']++;
    
    $content = file_get_contents($filePath);
    $lineCount = substr_count($content, "\n") + 1;
    
    if (empty(trim($content)) || $lineCount < 10) {
        $stats['empty_files']++;
        $stats['directories'][$dirName]['empty_files']++;
    } elseif ($lineCount < $basicStructureThreshold) {
        $stats['basic_structure_files']++;
        $stats['directories'][$dirName]['basic_structure_files']++;
    } else {
        $stats['complete_files']++;
        $stats['directories'][$dirName]['complete_files']++;
    }
}

// 计算目录优先级
function calculatePriorities(&$stats)
{
    foreach ($stats['directories'] as $dirName => &$dirStats) {
        if ($dirStats['total_files'] === 0) {
            continue;
        }
        
        // 计算优先级得分 (空文件比例 * 2 + 基本结构文件比例)
        $emptyRatio = $dirStats['empty_files'] / $dirStats['total_files'];
        $basicRatio = $dirStats['basic_structure_files'] / $dirStats['total_files'];
        $dirStats['priority_score'] = ($emptyRatio * 2 + $basicRatio) * 100;
    }
}

// 分析测试覆盖率
function analyzeTestCoverage(&$stats)
{
    if ($stats['total_files'] > 0) {
        $stats['test_coverage'] = ($stats['test_files'] / $stats['total_files']) * 100;
    }
}

// 生成优先级报告
function generatePriorityReport($stats)
{
    $report = "# 代码完善优先级报告\n\n";
    $report .= "生成时间: " . date('Y-m-d H:i:s') . "\n\n";
    
    $report .= "## 项目概览\n\n";
    $report .= "- 总文件数: " . $stats['total_files'] . "\n";
    $report .= "- 空文件数: " . $stats['empty_files'] . " (" . round(($stats['empty_files'] / $stats['total_files']) * 100, 2) . "%)\n";
    $report .= "- 基本结构文件数: " . $stats['basic_structure_files'] . " (" . round(($stats['basic_structure_files'] / $stats['total_files']) * 100, 2) . "%)\n";
    $report .= "- 完整文件数: " . $stats['complete_files'] . " (" . round(($stats['complete_files'] / $stats['total_files']) * 100, 2) . "%)\n";
    $report .= "- 测试文件数: " . $stats['test_files'] . "\n";
    $report .= "- 测试覆盖率: " . round($stats['test_coverage'], 2) . "%\n\n";
    
    $report .= "## 目录优先级\n\n";
    $report .= "| 目录 | 总文件数 | 空文件 | 基本结构 | 完整文件 | 优先级得分 |\n";
    $report .= "|------|----------|--------|----------|----------|------------|\n";
    
    // 按优先级排序
    uasort($stats['directories'], function($a, $b) {
        return $b['priority_score'] <=> $a['priority_score'];
    });
    
    foreach ($stats['directories'] as $dirName => $dirStats) {
        if ($dirStats['total_files'] === 0) {
            continue;
        }
        
        $report .= "| {$dirName} | {$dirStats['total_files']} | {$dirStats['empty_files']} | {$dirStats['basic_structure_files']} | {$dirStats['complete_files']} | " . round($dirStats['priority_score'], 2) . " |\n";
    }
    
    $report .= "\n## 完善建议\n\n";
    
    // 获取前5个优先级最高的目录
    $topPriorities = array_slice($stats['directories'], 0, 5, true);
    
    $report .= "### 优先完善以下目录:\n\n";
    foreach ($topPriorities as $dirName => $dirStats) {
        $report .= "1. **{$dirName}** - 优先级得分: " . round($dirStats['priority_score'], 2) . "\n";
        $report .= "   - 空文件: {$dirStats['empty_files']}, 基本结构: {$dirStats['basic_structure_files']}, 完整文件: {$dirStats['complete_files']}\n";
    }
    
    $report .= "\n### 测试覆盖率\n\n";
    if ($stats['test_coverage'] < 30) {
        $report .= "测试覆盖率较低，建议优先添加单元测试和集成测试。\n";
    } else if ($stats['test_coverage'] < 70) {
        $report .= "测试覆盖率中等，建议继续增加测试用例，特别是核心功能模块。\n";
    } else {
        $report .= "测试覆盖率良好，建议维护现有测试并为新功能添加测试。\n";
    }
    
    return $report;
}

// 开始执行
echo "开始分析项目代码...\n";
$startTime = microtime(true);

// 扫描源代码目录
scanDirectory($srcDir, $stats);

// 扫描测试目录
if (is_dir($testDir)) {
    scanDirectory($testDir, $stats, true);
}

// 计算优先级
calculatePriorities($stats);

// 分析测试覆盖率
analyzeTestCoverage($stats);

// 生成报告
$report = generatePriorityReport($stats);
file_put_contents(__DIR__ . '/CODE_PRIORITY_REPORT.md', $report);

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "完成！\n";
echo "统计信息：\n";
echo "- 总文件数: " . $stats['total_files'] . "\n";
echo "- 空文件数: " . $stats['empty_files'] . "\n";
echo "- 基本结构文件数: " . $stats['basic_structure_files'] . "\n";
echo "- 完整文件数: " . $stats['complete_files'] . "\n";
echo "- 测试文件数: " . $stats['test_files'] . "\n";
echo "- 测试覆盖率: " . round($stats['test_coverage'], 2) . "%\n";
echo "- 执行时间: " . $executionTime . " 秒\n";
echo "优先级报告已生成: " . __DIR__ . '/CODE_PRIORITY_REPORT.md' . "\n"; 