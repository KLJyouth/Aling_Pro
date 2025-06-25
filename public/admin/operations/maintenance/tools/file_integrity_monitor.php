<?php
/**
 * AlingAi Pro 文件完整性监控工具
 * 
 * 此工具用于监控文件系统中的PHP和HTML文件是否被非法修改
 * 功能：
 * 1. 生成文件哈希基准库
 * 2. 定期扫描文件并与基准库比较，检测文件变化
 * 3. 记录可疑修改并报警
 * 4. 提供文件恢复功能
 */

declare(strict_types=1);

// 设置脚本最大执行时间
set_time_limit(600);

// 配置参数
$config = [
    'baseline_db' => __DIR__ . '/../../../database/file_integrity_baseline.json',
    'backup_dir' => __DIR__ . '/../../../database/file_backups',
    'log_file' => __DIR__ . '/../logs/file_integrity_log.json',
    'alert_threshold' => 5, // 超过此数量的文件变化将触发警报
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
    'file_types' => [
        'php',
        'html',
        'js',
        'css',
    ],
    'max_backup_age' => 30, // 备份保留天数
];

// 确保目录存在
if (!is_dir(dirname($config['baseline_db']))) {
    mkdir(dirname($config['baseline_db']), 0755, true);
}
if (!is_dir($config['backup_dir'])) {
    mkdir($config['backup_dir'], 0755, true);
}
if (!is_dir(dirname($config['log_file']))) {
    mkdir(dirname($config['log_file']), 0755, true);
}

// 命令行参数处理
$action = $_GET['action'] ?? 'scan';
$verbose = isset($_GET['verbose']) ? (bool)$_GET['verbose'] : false;
$path = $_GET['path'] ?? '';
$restore = $_GET['restore'] ?? '';

// 根据操作执行不同功能
switch ($action) {
    case 'create-baseline':
        createBaseline($config, $verbose);
        break;
    case 'scan':
        scanFiles($config, $verbose, $path);
        break;
    case 'restore':
        restoreFile($config, $restore);
        break;
    case 'view-log':
        viewLog($config);
        break;
    case 'cleanup':
        cleanupOldBackups($config);
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
    echo "  ?action=create-baseline - 创建文件基准库\n";
    echo "  ?action=scan - 扫描文件变化\n";
    echo "  ?action=scan&path=/path/to/dir - 扫描指定目录\n";
    echo "  ?action=restore&restore=/path/to/file - 恢复文件\n";
    echo "  ?action=view-log - 查看日志\n";
    echo "  ?action=cleanup - 清理旧备份\n";
    echo "  &verbose=1 - 显示详细信息\n";
}

/**
 * 创建文件基准库
 */
function createBaseline(array $config, bool $verbose = false) {
    echo "<h2>创建文件完整性基准库</h2>";
    
    $baseline = [];
    $fileCount = 0;
    
    foreach ($config['scan_dirs'] as $dir) {
        processDirectory($dir, $config, $baseline, $fileCount, $verbose);
    }
    
    // 保存基准库
    file_put_contents($config['baseline_db'], json_encode($baseline, JSON_PRETTY_PRINT));
    
    echo "<p>基准库已创建，共 $fileCount 个文件</p>";
}

/**
 * 递归处理目录
 */
function processDirectory(string $dir, array $config, array &$baseline, int &$fileCount, bool $verbose = false) {
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
            processDirectory($path, $config, $baseline, $fileCount, $verbose);
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($extension, $config['file_types'])) {
                $fileHash = getFileHash($path);
                $fileSize = filesize($path);
                $fileTime = filemtime($path);
                
                $baseline[$relativePath] = [
                    'hash' => $fileHash,
                    'size' => $fileSize,
                    'time' => $fileTime,
                    'last_verified' => time(),
                ];
                
                $fileCount++;
                
                if ($verbose) {
                    echo "<p>添加文件: $relativePath (SHA256: " . substr($fileHash, 0, 16) . "...)</p>";
                }
                
                // 创建初始备份
                backupFile($path, $config['backup_dir']);
            }
        }
    }
}

/**
 * 扫描文件变化
 */
function scanFiles(array $config, bool $verbose = false, string $specificPath = '') {
    echo "<h2>扫描文件完整性</h2>";
    
    // 加载基准库
    if (!file_exists($config['baseline_db'])) {
        echo "<p class='error'>错误: 基准库不存在，请先创建基准库</p>";
        return;
    }
    
    $baseline = json_decode(file_get_contents($config['baseline_db']), true);
    
    $changedFiles = [];
    $newFiles = [];
    $missingFiles = [];
    $scannedFiles = 0;
    
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
    
    // 扫描文件系统
    foreach ($scanDirs as $dir) {
        scanDirectory($dir, $config, $baseline, $changedFiles, $newFiles, $scannedFiles, $verbose);
    }
    
    // 检查丢失的文件
    foreach ($baseline as $relativePath => $fileInfo) {
        $fullPath = $config['scan_dirs'][0] . '/' . $relativePath;
        if (!file_exists($fullPath)) {
            $missingFiles[] = $relativePath;
        }
    }
    
    // 输出结果
    echo "<p>扫描完成，共扫描 $scannedFiles 个文件</p>";
    
    if (count($changedFiles) > 0) {
        echo "<h3>发现 " . count($changedFiles) . " 个文件被修改:</h3>";
        echo "<ul>";
        foreach ($changedFiles as $file) {
            echo "<li>$file[path] - <a href='?action=restore&restore=" . urlencode($file['path']) . "'>恢复</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>未发现文件被修改</p>";
    }
    
    if (count($newFiles) > 0) {
        echo "<h3>发现 " . count($newFiles) . " 个新文件:</h3>";
        echo "<ul>";
        foreach ($newFiles as $file) {
            echo "<li>$file</li>";
        }
        echo "</ul>";
    }
    
    if (count($missingFiles) > 0) {
        echo "<h3>发现 " . count($missingFiles) . " 个文件丢失:</h3>";
        echo "<ul>";
        foreach ($missingFiles as $file) {
            echo "<li>$file</li>";
        }
        echo "</ul>";
    }
    
    // 记录到日志
    logResults($config, $changedFiles, $newFiles, $missingFiles);
    
    // 如果变化超过阈值，触发警报
    $totalChanges = count($changedFiles) + count($newFiles) + count($missingFiles);
    if ($totalChanges >= $config['alert_threshold']) {
        triggerAlert($config, $totalChanges, $changedFiles, $newFiles, $missingFiles);
    }
}

/**
 * 递归扫描目录并检查文件变化
 */
function scanDirectory(string $dir, array $config, array &$baseline, array &$changedFiles, array &$newFiles, int &$scannedFiles, bool $verbose = false) {
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
            scanDirectory($path, $config, $baseline, $changedFiles, $newFiles, $scannedFiles, $verbose);
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($extension, $config['file_types'])) {
                $scannedFiles++;
                
                // 检查文件是否在基准库中
                if (isset($baseline[$relativePath])) {
                    $fileHash = getFileHash($path);
                    
                    // 检查文件是否被修改
                    if ($fileHash !== $baseline[$relativePath]['hash']) {
                        $changedFiles[] = [
                            'path' => $relativePath,
                            'old_hash' => $baseline[$relativePath]['hash'],
                            'new_hash' => $fileHash,
                            'time' => time(),
                        ];
                        
                        if ($verbose) {
                            echo "<p class='warning'>文件已修改: $relativePath</p>";
                        }
                        
                        // 备份被修改的文件
                        backupFile($path, $config['backup_dir']);
                    } else {
                        // 更新最后验证时间
                        $baseline[$relativePath]['last_verified'] = time();
                        
                        if ($verbose) {
                            echo "<p>文件未变化: $relativePath</p>";
                        }
                    }
                } else {
                    // 新文件
                    $newFiles[] = $relativePath;
                    
                    if ($verbose) {
                        echo "<p class='info'>发现新文件: $relativePath</p>";
                    }
                }
            }
        }
    }
    
    // 更新基准库
    file_put_contents($config['baseline_db'], json_encode($baseline, JSON_PRETTY_PRINT));
}

/**
 * 恢复文件
 */
function restoreFile(array $config, string $filePath) {
    echo "<h2>恢复文件</h2>";
    
    if (empty($filePath)) {
        echo "<p class='error'>错误: 未指定文件路径</p>";
        return;
    }
    
    $fullPath = $config['scan_dirs'][0] . '/' . $filePath;
    $backupPath = $config['backup_dir'] . '/' . $filePath;
    
    if (!file_exists($backupPath)) {
        echo "<p class='error'>错误: 备份文件不存在: $backupPath</p>";
        return;
    }
    
    // 备份当前文件
    if (file_exists($fullPath)) {
        $tempBackup = $fullPath . '.before_restore.' . date('YmdHis');
        copy($fullPath, $tempBackup);
        echo "<p>已创建临时备份: $tempBackup</p>";
    }
    
    // 恢复文件
    if (copy($backupPath, $fullPath)) {
        echo "<p class='success'>文件恢复成功: $filePath</p>";
        
        // 更新基准库
        $baseline = json_decode(file_get_contents($config['baseline_db']), true);
        $baseline[$filePath]['hash'] = getFileHash($fullPath);
        $baseline[$filePath]['size'] = filesize($fullPath);
        $baseline[$filePath]['time'] = filemtime($fullPath);
        $baseline[$filePath]['last_verified'] = time();
        file_put_contents($config['baseline_db'], json_encode($baseline, JSON_PRETTY_PRINT));
        
        // 记录恢复操作
        $log = loadLog($config);
        $log[] = [
            'type' => 'restore',
            'file' => $filePath,
            'time' => time(),
            'user' => $_SERVER['PHP_AUTH_USER'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];
        saveLog($config, $log);
    } else {
        echo "<p class='error'>文件恢复失败: $filePath</p>";
    }
}

/**
 * 查看日志
 */
function viewLog(array $config) {
    echo "<h2>文件完整性监控日志</h2>";
    
    if (!file_exists($config['log_file'])) {
        echo "<p>日志文件不存在</p>";
        return;
    }
    
    $log = loadLog($config);
    
    if (empty($log)) {
        echo "<p>日志为空</p>";
        return;
    }
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>时间</th><th>类型</th><th>文件</th><th>详情</th><th>用户</th><th>IP</th></tr>";
    
    // 按时间倒序排列
    usort($log, function($a, $b) {
        return $b['time'] - $a['time'];
    });
    
    foreach ($log as $entry) {
        $time = date('Y-m-d H:i:s', $entry['time']);
        $type = $entry['type'];
        $file = $entry['file'] ?? '';
        $details = '';
        
        if ($type === 'scan') {
            $details = "变更: {$entry['changed']}，新增: {$entry['new']}，丢失: {$entry['missing']}";
        } elseif ($type === 'alert') {
            $details = "共 {$entry['total_changes']} 个变化";
        }
        
        $user = $entry['user'] ?? 'unknown';
        $ip = $entry['ip'] ?? 'unknown';
        
        echo "<tr>";
        echo "<td>$time</td>";
        echo "<td>$type</td>";
        echo "<td>$file</td>";
        echo "<td>$details</td>";
        echo "<td>$user</td>";
        echo "<td>$ip</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

/**
 * 清理旧备份
 */
function cleanupOldBackups(array $config) {
    echo "<h2>清理旧备份</h2>";
    
    $cutoffTime = time() - ($config['max_backup_age'] * 86400);
    $count = 0;
    
    cleanupDirectory($config['backup_dir'], $cutoffTime, $count);
    
    echo "<p>清理完成，共删除 $count 个过期备份文件</p>";
}

/**
 * 递归清理目录中的旧备份
 */
function cleanupDirectory(string $dir, int $cutoffTime, int &$count) {
    if (!is_dir($dir)) {
        return;
    }
    
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            cleanupDirectory($path, $cutoffTime, $count);
            
            // 如果目录为空，删除目录
            $dirItems = scandir($path);
            if (count($dirItems) <= 2) { // 只有 . 和 ..
                rmdir($path);
            }
        } else {
            // 检查文件修改时间
            if (filemtime($path) < $cutoffTime) {
                if (unlink($path)) {
                    $count++;
                }
            }
        }
    }
}

/**
 * 备份文件
 */
function backupFile(string $filePath, string $backupDir) {
    $relativePath = getRelativePath($filePath, dirname(dirname(dirname(dirname(dirname(__DIR__))))));
    $backupPath = $backupDir . '/' . $relativePath;
    
    // 确保备份目录存在
    $backupDirPath = dirname($backupPath);
    if (!is_dir($backupDirPath)) {
        mkdir($backupDirPath, 0755, true);
    }
    
    // 创建备份
    copy($filePath, $backupPath);
}

/**
 * 获取文件哈希值
 */
function getFileHash(string $filePath) {
    return hash_file('sha256', $filePath);
}

/**
 * 获取相对路径
 */
function getRelativePath(string $path, string $basePath) {
    return ltrim(str_replace($basePath, '', $path), '/\\');
}

/**
 * 记录扫描结果
 */
function logResults(array $config, array $changedFiles, array $newFiles, array $missingFiles) {
    $log = loadLog($config);
    
    $log[] = [
        'type' => 'scan',
        'time' => time(),
        'changed' => count($changedFiles),
        'new' => count($newFiles),
        'missing' => count($missingFiles),
        'user' => $_SERVER['PHP_AUTH_USER'] ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ];
    
    saveLog($config, $log);
}

/**
 * 触发警报
 */
function triggerAlert(array $config, int $totalChanges, array $changedFiles, array $newFiles, array $missingFiles) {
    $log = loadLog($config);
    
    $log[] = [
        'type' => 'alert',
        'time' => time(),
        'total_changes' => $totalChanges,
        'changed_files' => array_column($changedFiles, 'path'),
        'new_files' => $newFiles,
        'missing_files' => $missingFiles,
        'user' => $_SERVER['PHP_AUTH_USER'] ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ];
    
    saveLog($config, $log);
    
    // 这里可以添加发送邮件、短信等警报方式
    // sendAlertEmail($config, $totalChanges, $changedFiles, $newFiles, $missingFiles);
}

/**
 * 加载日志
 */
function loadLog(array $config) {
    if (file_exists($config['log_file'])) {
        return json_decode(file_get_contents($config['log_file']), true) ?? [];
    }
    return [];
}

/**
 * 保存日志
 */
function saveLog(array $config, array $log) {
    file_put_contents($config['log_file'], json_encode($log, JSON_PRETTY_PRINT));
}

// 添加HTML头部
function outputHeader() {
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文件完整性监控工具</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        h1, h2, h3 {
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
        }
        th, td {
            text-align: left;
            padding: 8px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        th {
            background-color: #3498db;
            color: white;
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
    <h1>AlingAi Pro 文件完整性监控工具</h1>
    <div class="menu">
        <a href="?action=create-baseline">创建基准库</a>
        <a href="?action=scan">扫描文件</a>
        <a href="?action=view-log">查看日志</a>
        <a href="?action=cleanup">清理备份</a>
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