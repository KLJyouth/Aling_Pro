<?php
/**
 * AlingAi Pro 系统检查脚本
 * 
 * 此脚本用于检查系统配置、PHP扩展和目录权限
 */

// 定义应用根目录
define('APP_ROOT', dirname(__DIR__));

echo "===========================================\n";
echo "       AlingAi Pro 系统检查工具\n";
echo "===========================================\n\n";

// 检查PHP版本
$requiredPhpVersion = '7.4.0';
$currentPhpVersion = PHP_VERSION;
echo "检查PHP版本...\n";
echo "  当前版本: {$currentPhpVersion}\n";
echo "  要求版本: >= {$requiredPhpVersion}\n";

if (version_compare($currentPhpVersion, $requiredPhpVersion, '>=')) {
    echo "  [通过] PHP版本符合要求\n\n";
} else {
    echo "  [警告] PHP版本低于建议版本\n\n";
}

// 检查必需的PHP扩展
$requiredExtensions = [
    'pdo',
    'pdo_sqlite',
    'openssl',
    'mbstring',
    'json',
    'xml',
];

$recommendedExtensions = [
    'gd',
    'redis',
    'opcache',
    'curl',
    'zip',
];

echo "检查必需的PHP扩展...\n";
$missingRequired = [];
foreach ($requiredExtensions as $extension) {
    if (extension_loaded($extension)) {
        echo "  [通过] {$extension}\n";
    } else {
        echo "  [失败] {$extension} - 未安装或未启用\n";
        $missingRequired[] = $extension;
    }
}

if (empty($missingRequired)) {
    echo "  所有必需扩展已安装\n\n";
} else {
    echo "  缺少必需的扩展，请在php.ini中启用这些扩展\n\n";
}

echo "检查推荐的PHP扩展...\n";
$missingRecommended = [];
foreach ($recommendedExtensions as $extension) {
    if (extension_loaded($extension)) {
        echo "  [通过] {$extension}\n";
    } else {
        echo "  [提示] {$extension} - 建议安装\n";
        $missingRecommended[] = $extension;
    }
}
echo "\n";

// 检查PHP配置
echo "检查PHP配置...\n";
$configs = [
    'memory_limit' => [
        'current' => ini_get('memory_limit'),
        'recommended' => '256M',
        'compare' => function($current, $recommended) {
            return (int)$current >= (int)$recommended || $current === '-1';
        }
    ],
    'max_execution_time' => [
        'current' => ini_get('max_execution_time'),
        'recommended' => '120',
        'compare' => function($current, $recommended) {
            return (int)$current >= (int)$recommended || $current === '0';
        }
    ],
    'upload_max_filesize' => [
        'current' => ini_get('upload_max_filesize'),
        'recommended' => '64M',
        'compare' => function($current, $recommended) {
            return (int)$current >= (int)$recommended;
        }
    ],
    'post_max_size' => [
        'current' => ini_get('post_max_size'),
        'recommended' => '64M',
        'compare' => function($current, $recommended) {
            return (int)$current >= (int)$recommended;
        }
    ],
    'display_errors' => [
        'current' => ini_get('display_errors'),
        'recommended' => 'Off',
        'compare' => function($current, $recommended) {
            return $current === $recommended;
        }
    ],
];

foreach ($configs as $name => $config) {
    echo "  {$name}:\n";
    echo "    当前值: {$config['current']}\n";
    echo "    建议值: {$config['recommended']}\n";
    
    if ($config['compare']($config['current'], $config['recommended'])) {
        echo "    [通过] 配置符合建议\n";
    } else {
        echo "    [提示] 建议优化此配置\n";
    }
}
echo "\n";

// 检查目录权限
echo "检查目录权限...\n";
$directories = [
    'storage/logs',
    'storage/cache',
    'database',
    'public/uploads',
];

foreach ($directories as $dir) {
    $path = APP_ROOT . '/' . $dir;
    
    if (!file_exists($path)) {
        echo "  {$dir}: 目录不存在\n";
        continue;
    }
    
    if (is_writable($path)) {
        echo "  [通过] {$dir} 目录可写\n";
    } else {
        echo "  [失败] {$dir} 目录不可写\n";
    }
}
echo "\n";

// 检查数据库连接
echo "检查数据库连接...\n";
$dbFile = APP_ROOT . '/database/database.sqlite';

if (!file_exists($dbFile)) {
    echo "  [提示] SQLite数据库文件不存在\n";
} else {
    try {
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "  [通过] 成功连接到SQLite数据库\n";
        
        // 检查表是否存在
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "  [信息] 数据库包含以下表: " . implode(', ', $tables) . "\n";
        } else {
            echo "  [提示] 数据库中没有表，需要运行迁移脚本\n";
        }
        
    } catch (PDOException $e) {
        echo "  [失败] 无法连接到SQLite数据库: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// 总结
echo "===========================================\n";
echo "系统检查摘要:\n";
if (empty($missingRequired)) {
    echo "  - 所有必需的PHP扩展已安装\n";
} else {
    echo "  - 缺少必需的PHP扩展: " . implode(', ', $missingRequired) . "\n";
}

if (!empty($missingRecommended)) {
    echo "  - 建议安装的PHP扩展: " . implode(', ', $missingRecommended) . "\n";
}

echo "  - PHP版本: " . (version_compare($currentPhpVersion, $requiredPhpVersion, '>=') ? "符合要求" : "低于建议版本") . "\n";
echo "===========================================\n"; 