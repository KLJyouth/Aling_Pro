<?php
/**
 * AlingAi Pro HTML验证工具
 * 
 * 此工具用于检查HTML文件的语法错误和最佳实践
 * 功能：
 * 1. 检测HTML语法错误
 * 2. 检查无效链接
 * 3. 检查可访问性问题
 * 4. 检查SEO最佳实践
 * 5. 检查响应式设计问题
 */

declare(strict_types=1);

// 设置脚本最大执行时间
set_time_limit(300);

// 配置参数
$config = [
    'scan_dirs' => [
        __DIR__ . '/../../../../', // 根目录
    ],
    'exclude_dirs' => [
        'vendor',
        'node_modules',
        'cache',
        'logs',
        'temp',
        'storage/logs',
        'storage/cache',
    ],
    'report_file' => __DIR__ . '/../reports/html_validation_report.json',
    'log_file' => __DIR__ . '/../logs/html_validation.log',
    'max_file_size' => 2 * 1024 * 1024, // 2MB
];

// 确保目录存在
if (!is_dir(dirname($config['report_file']))) {
    mkdir(dirname($config['report_file']), 0755, true);
}
if (!is_dir(dirname($config['log_file']))) {
    mkdir(dirname($config['log_file']), 0755, true);
}

// 验证规则
$validationRules = [
    'syntax' => [
        'name' => 'HTML语法',
        'description' => '检查HTML语法错误',
        'patterns' => [
            // 未闭合标签
            '/<([a-z][a-z0-9]*)[^>]*>(?!.*?<\/\1>)/i',
            // 重复ID
            '/id=[\'"]([^\'"]+)[\'"].*id=[\'"]\\1[\'"]/is',
            // 无效的嵌套
            '/<(p|h[1-6]|div)[^>]*>.*<(table|ul|ol|div)[^>]*>.*?<\/\1>/is',
        ],
        'remediation' => '确保所有HTML标签正确闭合，ID值唯一，遵循正确的标签嵌套规则。'
    ],
    'links' => [
        'name' => '链接有效性',
        'description' => '检查无效链接和锚点',
        'patterns' => [
            // 空链接
            '/<a[^>]*href\s*=\s*[\'"]?\s*[\'"]?[^>]*>/i',
            // JavaScript伪协议
            '/<a[^>]*href\s*=\s*[\'"]?javascript:void\(0\)[\'"]?[^>]*>/i',
        ],
        'remediation' => '避免使用空链接或JavaScript伪协议，使用有效的URL或ID引用。'
    ],
    'accessibility' => [
        'name' => '可访问性',
        'description' => '检查可访问性问题',
        'patterns' => [
            // 缺少alt属性的图片
            '/<img[^>]*(?!alt=)[^>]*>/i',
            // 空的alt属性
            '/<img[^>]*alt\s*=\s*[\'"]?\s*[\'"]?[^>]*>/i',
            // 表单控件缺少标签
            '/<input[^>]*(?!id=)[^>]*>(?!.*<label[^>]*for=)/i',
        ],
        'remediation' => '为所有图片添加有意义的alt属性，确保所有表单控件都有关联的标签。'
    ],
    'seo' => [
        'name' => 'SEO最佳实践',
        'description' => '检查SEO相关问题',
        'patterns' => [
            // 缺少标题
            '/<!DOCTYPE[^>]*>(?:.*?)<head[^>]*>(?!.*?<title[^>]*>.*?<\/title>)/is',
            // 缺少meta描述
            '/<head[^>]*>(?!.*?<meta[^>]*name\s*=\s*[\'"]description[\'"])/is',
            // 多个H1标签
            '/<h1[^>]*>.*<h1[^>]*>/is',
        ],
        'remediation' => '确保每个页面都有唯一的标题和meta描述，并且只有一个H1标签。'
    ],
    'responsive' => [
        'name' => '响应式设计',
        'description' => '检查响应式设计问题',
        'patterns' => [
            // 缺少viewport元标签
            '/<head[^>]*>(?!.*?<meta[^>]*name\s*=\s*[\'"]viewport[\'"])/is',
            // 固定宽度
            '/<[^>]*style\s*=\s*[\'"][^\'">]*width\s*:\s*\d+px[^\'">]*[\'"][^>]*>/i',
        ],
        'remediation' => '添加viewport元标签，避免使用固定像素宽度，使用响应式单位如%、em、rem或vw。'
    ],
    'performance' => [
        'name' => '性能优化',
        'description' => '检查可能影响性能的问题',
        'patterns' => [
            // 未压缩的大型内联脚本
            '/<script[^>]*>(?!.*?src=)[\s\S]{1000,}<\/script>/i',
            // 未压缩的大型内联样式
            '/<style[^>]*>[\s\S]{1000,}<\/style>/i',
            // 大型内联图片
            '/<img[^>]*src\s*=\s*[\'"]data:image\/[^\'">]{10000,}[\'"][^>]*>/i',
        ],
        'remediation' => '避免大型内联脚本和样式，将它们移至外部文件。使用适当大小的图片，避免大型内联图片。'
    ],
];

// 命令行参数处理
$action = $_GET['action'] ?? 'scan';
$path = $_GET['path'] ?? '';
$verbose = isset($_GET['verbose']) ? (bool)$_GET['verbose'] : false;
$category = $_GET['category'] ?? 'all';

// 根据操作执行不同功能
switch ($action) {
    case 'scan':
        scanHtmlFiles($config, $validationRules, $path, $verbose, $category);
        break;
    case 'report':
        viewReport($config);
        break;
    default:
        echo "未知操作: $action\n";
        showHelp();
        break;
}

/**
 * 显示帮助信息
 */
function showHelp() {
    echo "用法:\n";
    echo "  ?action=scan - 扫描HTML文件\n";
    echo "  ?action=scan&path=/path/to/dir - 扫描指定目录\n";
    echo "  ?action=scan&category=accessibility - 只检查可访问性问题\n";
    echo "  ?action=report - 查看验证报告\n";
    echo "  &verbose=1 - 显示详细信息\n";
}

/**
 * 扫描HTML文件
 */
function scanHtmlFiles(array $config, array $validationRules, string $specificPath = '', bool $verbose = false, string $categoryFilter = 'all') {
    echo "<h2>HTML文件验证</h2>";
    
    $startTime = microtime(true);
    $issues = [];
    $fileCount = 0;
    $issueCount = 0;
    
    // 如果指定了特定路径
    if (!empty($specificPath)) {
        $fullPath = realpath($specificPath);
        if (!$fullPath) {
            echo "<p class='error'>错误: 指定的路径不存在: $specificPath</p>";
            return;
        }
        
        $scanDirs = [$fullPath];
    } else {
        $scanDirs = $config['scan_dirs'];
    }
    
    // 根据分类过滤规则
    if ($categoryFilter !== 'all') {
        if (!isset($validationRules[$categoryFilter])) {
            echo "<p class='error'>错误: 未知的验证分类: $categoryFilter</p>";
            return;
        }
        
        $filteredRules = [$categoryFilter => $validationRules[$categoryFilter]];
        $validationRules = $filteredRules;
    }
    
    // 扫描目录
    foreach ($scanDirs as $dir) {
        scanDirectory($dir, $config, $validationRules, $issues, $fileCount, $verbose);
    }
    
    // 统计问题数量
    foreach ($issues as $file => $fileIssues) {
        $issueCount += count($fileIssues);
    }
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    // 保存报告
    saveReport($config, $issues, $fileCount, $issueCount, $executionTime);
    
    // 输出结果
    echo "<p>扫描完成，耗时 $executionTime 秒，共扫描 $fileCount 个文件，发现 $issueCount 个问题</p>";
    
    // 按问题类型分组
    $issuesByCategory = [];
    foreach ($issues as $file => $fileIssues) {
        foreach ($fileIssues as $issue) {
            $category = $issue['category'];
            if (!isset($issuesByCategory[$category])) {
                $issuesByCategory[$category] = [
                    'name' => $validationRules[$category]['name'],
                    'count' => 0,
                    'issues' => [],
                ];
            }
            $issuesByCategory[$category]['count']++;
            $issuesByCategory[$category]['issues'][] = [
                'file' => $file,
                'line' => $issue['line'],
                'code' => $issue['code'],
            ];
        }
    }
    
    // 输出问题摘要
    if ($issueCount > 0) {
        echo "<h3>问题摘要:</h3>";
        echo "<table>";
        echo "<tr><th>问题类型</th><th>数量</th></tr>";
        
        foreach ($issuesByCategory as $category => $info) {
            echo "<tr>";
            echo "<td>{$info['name']}</td>";
            echo "<td>{$info['count']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // 输出详细问题信息
        echo "<h3>详细问题信息:</h3>";
        
        foreach ($issuesByCategory as $category => $info) {
            $remediation = $validationRules[$category]['remediation'];
            
            echo "<div class='issue-category'>";
            echo "<h4>{$info['name']}</h4>";
            echo "<p><strong>修复建议:</strong> $remediation</p>";
            
            echo "<table>";
            echo "<tr><th>文件</th><th>行号</th><th>代码</th></tr>";
            
            foreach ($info['issues'] as $issue) {
                $file = $issue['file'];
                $line = $issue['line'];
                $code = htmlspecialchars($issue['code']);
                
                echo "<tr>";
                echo "<td>$file</td>";
                echo "<td>$line</td>";
                echo "<td><code>$code</code></td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
    } else {
        echo "<p class='success'>未发现问题</p>";
    }
}

/**
 * 递归扫描目录
 */
function scanDirectory(string $dir, array $config, array $validationRules, array &$issues, int &$fileCount, bool $verbose = false) {
    if (!is_dir($dir)) {
        return;
    }
    
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        $relativePath = getRelativePath($path, $config['scan_dirs'][0]);
        
        // 检查是否为排除目录
        foreach ($config['exclude_dirs'] as $excludeDir) {
            if (strpos($relativePath, $excludeDir) === 0) {
                if ($verbose) {
                    echo "<p>跳过排除目录: $relativePath</p>";
                }
                continue 2;
            }
        }
        
        if (is_dir($path)) {
            scanDirectory($path, $config, $validationRules, $issues, $fileCount, $verbose);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'html' || pathinfo($path, PATHINFO_EXTENSION) === 'htm') {
            // 检查文件大小
            if (filesize($path) > $config['max_file_size']) {
                if ($verbose) {
                    echo "<p class='warning'>跳过过大文件: $relativePath</p>";
                }
                continue;
            }
            
            $fileCount++;
            
            if ($verbose) {
                echo "<p>验证文件: $relativePath</p>";
            }
            
            // 验证HTML文件
            $fileIssues = validateHtmlFile($path, $validationRules);
            
            if (!empty($fileIssues)) {
                $issues[$relativePath] = $fileIssues;
                
                if ($verbose) {
                    echo "<p class='warning'>发现 " . count($fileIssues) . " 个问题: $relativePath</p>";
                }
            }
        }
    }
}

/**
 * 验证HTML文件
 */
function validateHtmlFile(string $filePath, array $validationRules) {
    $content = file_get_contents($filePath);
    if ($content === false) {
        return [];
    }
    
    $lines = explode("\n", $content);
    $issues = [];
    
    // 检查每个验证规则
    foreach ($validationRules as $category => $rule) {
        foreach ($rule['patterns'] as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $matchText = $match[0];
                    $position = $match[1];
                    
                    // 计算行号
                    $lineNumber = 1;
                    $pos = 0;
                    while ($pos < $position && $pos !== false) {
                        $pos = strpos($content, "\n", $pos);
                        if ($pos !== false) {
                            $lineNumber++;
                            $pos++;
                        }
                    }
                    
                    // 获取包含上下文的代码片段
                    $contextLines = [];
                    $startLine = max(1, $lineNumber - 2);
                    $endLine = min(count($lines), $lineNumber + 2);
                    
                    for ($i = $startLine - 1; $i < $endLine; $i++) {
                        $contextLines[] = ($i + 1 === $lineNumber ? '> ' : '  ') . $lines[$i];
                    }
                    
                    $issues[] = [
                        'category' => $category,
                        'line' => $lineNumber,
                        'code' => implode("\n", $contextLines),
                        'match' => $matchText,
                    ];
                }
            }
        }
    }
    
    // 进行额外的DOM验证
    $domIssues = validateWithDOM($filePath, $content);
    if (!empty($domIssues)) {
        $issues = array_merge($issues, $domIssues);
    }
    
    return $issues;
}

/**
 * 使用DOM进行额外验证
 */
function validateWithDOM(string $filePath, string $content) {
    $issues = [];
    
    // 使用libxml错误处理
    libxml_use_internal_errors(true);
    
    $dom = new DOMDocument();
    $dom->loadHTML($content, LIBXML_NOWARNING | LIBXML_NOERROR);
    
    $errors = libxml_get_errors();
    libxml_clear_errors();
    
    // 处理libxml错误
    $lines = explode("\n", $content);
    foreach ($errors as $error) {
        // 过滤一些常见的无关紧要的警告
        if ($error->level === LIBXML_ERR_WARNING) {
            continue;
        }
        
        $lineNumber = $error->line;
        
        // 获取包含上下文的代码片段
        $contextLines = [];
        $startLine = max(1, $lineNumber - 2);
        $endLine = min(count($lines), $lineNumber + 2);
        
        for ($i = $startLine - 1; $i < $endLine; $i++) {
            if (isset($lines[$i])) {
                $contextLines[] = ($i + 1 === $lineNumber ? '> ' : '  ') . $lines[$i];
            }
        }
        
        $issues[] = [
            'category' => 'syntax',
            'line' => $lineNumber,
            'code' => implode("\n", $contextLines),
            'match' => $error->message,
        ];
    }
    
    return $issues;
}

/**
 * 保存验证报告
 */
function saveReport(array $config, array $issues, int $fileCount, int $issueCount, float $executionTime) {
    $report = [
        'timestamp' => time(),
        'scan_date' => date('Y-m-d H:i:s'),
        'execution_time' => $executionTime,
        'files_scanned' => $fileCount,
        'issue_count' => $issueCount,
        'issues' => $issues,
    ];
    
    file_put_contents($config['report_file'], json_encode($report, JSON_PRETTY_PRINT));
    
    // 记录日志
    $log = "[$report[scan_date]] 验证完成: 扫描 $fileCount 个文件，发现 $issueCount 个问题，耗时 $executionTime 秒\n";
    file_put_contents($config['log_file'], $log, FILE_APPEND);
}

/**
 * 查看验证报告
 */
function viewReport(array $config) {
    echo "<h2>HTML验证报告</h2>";
    
    if (!file_exists($config['report_file'])) {
        echo "<p class='error'>报告文件不存在</p>";
        return;
    }
    
    $report = json_decode(file_get_contents($config['report_file']), true);
    
    if (!$report) {
        echo "<p class='error'>无法解析报告文件</p>";
        return;
    }
    
    echo "<p>扫描日期: {$report['scan_date']}</p>";
    echo "<p>扫描文件数: {$report['files_scanned']}</p>";
    echo "<p>问题数量: {$report['issue_count']}</p>";
    echo "<p>执行时间: {$report['execution_time']} 秒</p>";
    
    if ($report['issue_count'] > 0) {
        echo "<h3>问题详情:</h3>";
        
        foreach ($report['issues'] as $file => $fileIssues) {
            echo "<div class='file-issues'>";
            echo "<h4>$file</h4>";
            echo "<ul>";
            
            foreach ($fileIssues as $issue) {
                echo "<li>";
                echo "<strong>{$issue['category']}</strong> (第 {$issue['line']} 行)<br>";
                echo "<pre>{$issue['code']}</pre>";
                echo "</li>";
            }
            
            echo "</ul>";
            echo "</div>";
        }
    } else {
        echo "<p class='success'>未发现问题</p>";
    }
}

/**
 * 获取相对路径
 */
function getRelativePath(string $path, string $basePath) {
    return ltrim(str_replace($basePath, '', $path), '/\\');
}

// 添加HTML头部
function outputHeader() {
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML验证工具</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        h1, h2, h3, h4 {
            color: #2c3e50;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .info {
            color: #3498db;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        pre, code {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 10px;
            overflow: auto;
            font-family: monospace;
            font-size: 14px;
        }
        .issue-category {
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
            padding-left: 15px;
        }
        .file-issues {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .menu {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .menu a {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <h1>AlingAi Pro HTML验证工具</h1>
    <div class="menu">
        <a href="?action=scan">验证全部</a>
        <a href="?action=scan&category=syntax">检查语法</a>
        <a href="?action=scan&category=accessibility">检查可访问性</a>
        <a href="?action=scan&category=seo">检查SEO</a>
        <a href="?action=scan&category=responsive">检查响应式</a>
        <a href="?action=report">查看报告</a>
    </div>';
}

// 添加HTML尾部
function outputFooter() {
    echo '
</body>
</html>';
}

// 输出HTML头部
outputHeader();

// 输出HTML尾部
outputFooter();
?> 