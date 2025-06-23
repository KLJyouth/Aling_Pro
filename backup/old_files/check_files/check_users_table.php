<?php
/**
 * 检查用户表结构
 */

// 加载环境变量
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"');
        }
    }
}

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'alingai';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 检查users表结构...\n";
    echo "数据库: {$host}/{$dbname}\n\n";
    
    $result = $db->query('DESCRIBE users');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "users表当前字段:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']}) " . 
             ($column['Null'] === 'NO' ? "NOT NULL" : "NULL") . 
             ($column['Default'] ? " DEFAULT {$column['Default']}" : "") . "\n";
    }
    
    // 检查是否存在role字段
    $hasRole = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'role') {
            $hasRole = true;
            break;
        }
    }
    
    echo "\n🎯 检查结果:\n";
    echo "  role字段存在: " . ($hasRole ? "✅" : "❌") . "\n";
    
    if (!$hasRole) {
        echo "\n🔧 添加role字段到users表...\n";
        
        // 添加role字段
        $db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'user'");
        echo "  ✅ role字段已添加\n";
        
        // 再次验证
        $result = $db->query('DESCRIBE users');
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n更新后的users表字段:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']}) " . 
                 ($column['Null'] === 'NO' ? "NOT NULL" : "NULL") . 
                 ($column['Default'] ? " DEFAULT {$column['Default']}" : "") . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "请检查数据库连接配置\n";
}
