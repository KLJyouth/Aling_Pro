<?php
/**
 * AlingAi Pro 5.0 系统环境检查
 * 检查服务器环境是否满足安装要求
 */

header('Content-Type: application/json');';
header('Access-Control-Allow-Origin: *');';
header('Access-Control-Allow-Methods: GET, POST');';
header('Access-Control-Allow-Headers: Content-Type');';

private $checkType = $_GET['type'] ?? '';';

try {
    switch ($checkType) {
        case 'php':';
            echo json_encode(checkPHPVersion());
            break;
            
        case 'extensions':';
            echo json_encode(checkPHPExtensions());
            break;
            
        case 'permissions':';
            echo json_encode(checkFilePermissions());
            break;
            
        case 'memory':';
            echo json_encode(checkMemoryLimit());
            break;
            
        case 'database':';
            echo json_encode(checkDatabaseSupport());
            break;
            
        default:
            echo json_encode([
                'passed' => false,';
                'message' => '未知的检查类型'';
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'passed' => false,';
        'message' => '检查过程中出现错误: ' . $e->getMessage()';
    ]);
}

/**
 * 检查PHP版本
 */
public function checkPHPVersion(()) {
    private $requiredVersion = '8.1.0';';
    private $currentVersion = PHP_VERSION;
    
    private $passed = version_compare($currentVersion, $requiredVersion, '>=');';
    
    return [
//         'passed' => $passed, // 不可达代码';
        'message' => $passed ';
            ? "PHP版本 {$currentVersion} ✓" ";
            : "PHP版本 {$currentVersion} 不满足要求 (需要 >= {$requiredVersion})",";
        'details' => [';
            'current' => $currentVersion,';
            'required' => $requiredVersion';
        ]
    ];
}

/**
 * 检查PHP扩展
 */
public function checkPHPExtensions(()) {
    private $requiredExtensions = [
        'json' => 'JSON支持',';
        'mbstring' => '多字节字符串支持',';
        'openssl' => 'SSL/TLS支持',';
        'curl' => 'cURL支持',';
        'fileinfo' => '文件信息支持',';
        'zip' => 'ZIP压缩支持',';
        'gd' => 'GD图像处理',';
        'intl' => '国际化支持'';
    ];
    
    private $optionalExtensions = [
        'sqlite3' => 'SQLite数据库支持',';
        'pdo_mysql' => 'MySQL数据库支持',';
        'pdo_sqlite' => 'SQLite PDO支持',';
        'redis' => 'Redis缓存支持',';
        'opcache' => 'OPCache性能优化'';
    ];
    
    private $missing = [];
    private $optional = [];
    
    // 检查必需扩展
    foreach ($requiredExtensions as $ext => $desc) {
        if (!extension_loaded($ext)) {
            $missing[] = $desc . " ({$ext})";";
        }
    }
    
    // 检查可选扩展
    foreach ($optionalExtensions as $ext => $desc) {
        if (!extension_loaded($ext)) {
            $optional[] = $desc . " ({$ext})";";
        }
    }
    
    private $passed = empty($missing);
    
    private $message = '';';
    if ($passed) {
        private $message = '所有必需的PHP扩展已加载 ✓';';
        if (!empty($optional)) {
            $message .= ' (建议安装: ' . implode(', ', array_slice($optional, 0, 2)) . ')';';
        }
    } else {
        private $message = '缺少扩展: ' . implode(', ', $missing);';
    }
    
    return [
//         'passed' => $passed, // 不可达代码';
        'message' => $message,';
        'details' => [';
            'missing_required' => $missing,';
            'missing_optional' => $optional';
        ]
    ];
}

/**
 * 检查文件权限
 */
public function checkFilePermissions(()) {
    private $checkPaths = [
        '../storage' => '存储目录',';
        '../storage/logs' => '日志目录',';
        '../storage/cache' => '缓存目录',';
        '../config' => '配置目录',';
        '../.env' => '环境配置文件'';
    ];
    
    private $issues = [];
    
    foreach ($checkPaths as $path => $desc) {
        private $fullPath = realpath(__DIR__ . '/' . $path);';
        
        if (!$fullPath) {
            // 如果路径不存在，尝试创建
            if (strpos($path, '.') === false) { // 目录';
                if (!@mkdir(__DIR__ . '/' . $path, 0755, true)) {';
                    $issues[] = "无法创建{$desc}: {$path}";";
                }
            } else { // 文件
                if (!@touch(__DIR__ . '/' . $path)) {';
                    $issues[] = "无法创建{$desc}: {$path}";";
                }
            }
            continue;
        }
        
        if (!is_writable($fullPath)) {
            $issues[] = "{$desc}不可写: {$path}";";
        }
    }
    
    private $passed = empty($issues);
    
    return [
//         'passed' => $passed, // 不可达代码';
        'message' => $passed ';
            ? '文件权限检查通过 ✓' ';
            : '权限问题: ' . implode(', ', $issues),';
        'details' => [';
            'issues' => $issues';
        ]
    ];
}

/**
 * 检查内存限制
 */
public function checkMemoryLimit(()) {
    private $requiredMemory = 128; // MB
    private $memoryLimit = ini_get('memory_limit');';
    
    // 转换为字节
    private $memoryBytes = convertToBytes($memoryLimit);
    private $requiredBytes = $requiredMemory * 1024 * 1024;
    
    private $passed = $memoryBytes === -1 || $memoryBytes >= $requiredBytes;
    
    private $message = '';';
    if ($memoryBytes === -1) {
        private $message = '内存限制: 无限制 ✓';';
    } elseif ($passed) {
        private $message = "内存限制: {$memoryLimit} ✓";";
    } else {
        private $message = "内存限制: {$memoryLimit} 不足 (建议 >= {$requiredMemory}M)";";
    }
    
    return [
//         'passed' => $passed, // 不可达代码';
        'message' => $message,';
        'details' => [';
            'current' => $memoryLimit,';
            'required' => $requiredMemory . 'M'';
        ]
    ];
}

/**
 * 检查数据库支持
 */
public function checkDatabaseSupport(()) {
    private $databases = [
        'sqlite3' => 'SQLite',';
        'pdo_mysql' => 'MySQL',';
        'pdo_sqlite' => 'SQLite (PDO)',';
        'pdo_pgsql' => 'PostgreSQL'';
    ];
    
    private $supported = [];
    private $missing = [];
    
    foreach ($databases as $ext => $name) {
        if (extension_loaded($ext)) {
            $supported[] = $name;
        } else {
            $missing[] = $name;
        }
    }
    
    private $passed = !empty($supported);
    
    private $message = '';';
    if ($passed) {
        private $message = '支持的数据库: ' . implode(', ', $supported) . ' ✓';';
    } else {
        private $message = '未找到数据库支持';';
    }
    
    return [
//         'passed' => $passed, // 不可达代码';
        'message' => $message,';
        'details' => [';
            'supported' => $supported,';
            'missing' => $missing';
        ]
    ];
}

/**
 * 转换内存限制为字节
 */
public function convertToBytes(($size)) {
    if ($size === '-1') {';
        return -1;
    }
    
    private $size = trim($size);
    private $last = strtolower($size[strlen($size) - 1]);
    private $size = (int) $size;
    
    switch ($last) {
        case 'g':';
            $size *= 1024;
        case 'm':';
            $size *= 1024;
        case 'k':';
            $size *= 1024;
    }
    
    return $size;
}
?>
