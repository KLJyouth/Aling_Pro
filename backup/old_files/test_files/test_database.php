<?php
/**
 * 数据库连接测试脚本
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

echo "数据库连接测试...\n";
echo str_repeat("=", 50) . "\n";

// 加载环境变量
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✅ 环境变量已加载\n";
} else {
    echo "❌ .env 文件不存在\n";
    exit(1);
}

echo "\n环境变量检查:\n";
echo "DB_CONNECTION: " . (getenv('DB_CONNECTION') ?: 'not set') . "\n";
echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: 'not set') . "\n";

try {
    echo "\n尝试直接连接数据库...\n";
    
    $connection = getenv('DB_CONNECTION') ?: 'mysql';
    echo "连接类型: $connection\n";
    
    if ($connection === 'sqlite') {
        $database = getenv('DB_DATABASE') ?: __DIR__ . '/storage/database.sqlite';
        echo "数据库文件: $database\n";
        echo "文件是否存在: " . (file_exists($database) ? '是' : '否') . "\n";
        
        $dsn = 'sqlite:' . $database;
        echo "DSN: $dsn\n";
        
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        echo "✅ SQLite 连接成功!\n";
        
        // 测试一个简单查询
        $result = $pdo->query("SELECT sqlite_version() as version");
        $version = $result->fetch();
        echo "SQLite 版本: " . $version['version'] . "\n";
        
    } else {
        echo "MySQL 连接配置 - 跳过测试\n";
    }
    
} catch (Exception $e) {
    echo "❌ 数据库连接失败: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
}
