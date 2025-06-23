<?php

echo "=== 数据库连接测试 ===\n\n";

// 加载环境配置
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"\'');
        }
    }
    echo "✓ 环境配置已加载\n";
} else {
    echo "⚠ 未找到 .env 文件\n";
    exit(1);
}

// 数据库配置
$dbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_DATABASE'] ?? 'alingai_pro',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4'
];

echo "数据库配置:\n";
echo "  Host: {$dbConfig['host']}\n";
echo "  Database: {$dbConfig['database']}\n";
echo "  Username: {$dbConfig['username']}\n";
echo "  Password: " . str_repeat('*', strlen($dbConfig['password'])) . "\n\n";

try {
    echo "正在连接数据库...\n";
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
    
    echo "✓ 数据库连接成功!\n\n";
    
    // 测试查询
    echo "测试查询...\n";
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as database, NOW() as current_time");
    $result = $stmt->fetch();
    
    echo "  MySQL版本: {$result['version']}\n";
    echo "  当前数据库: {$result['database']}\n";
    echo "  服务器时间: {$result['current_time']}\n\n";
    
    // 检查表结构
    echo "检查相关表结构...\n";
    $tables = ['users', 'user_applications', 'user_quota', 'user_enterprise_config'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "  ✓ 表 $table 存在\n";
            
            // 显示表结构
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll();
            echo "    字段: " . implode(', ', array_column($columns, 'Field')) . "\n";
        } else {
            echo "  ⚠ 表 $table 不存在\n";
        }
    }
    
    echo "\n✓ 数据库测试完成!\n";
    
} catch (PDOException $e) {
    echo "✗ 数据库连接失败: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    
    if ($e->getCode() == 2002) {
        echo "提示: 无法连接到数据库服务器，请检查网络连接和服务器状态\n";
    } elseif ($e->getCode() == 1045) {
        echo "提示: 用户名或密码错误\n";
    } elseif ($e->getCode() == 1049) {
        echo "提示: 数据库不存在\n";
    }
    
    exit(1);
} catch (Exception $e) {
    echo "✗ 发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
