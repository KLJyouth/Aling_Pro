<?php
/**
 * 检查数据库表结构
 */

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $config = require __DIR__ . '/../config/database_local.php';
    $mysql = $config['production'];
    
    $pdo = new PDO(
        "mysql:host={$mysql['host']};port={$mysql['port']};dbname={$mysql['database']};charset=utf8mb4",
        $mysql['username'],
        $mysql['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "📋 Database Tables and Structure:\n\n";
    
    // 获取所有表
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Available tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\n";
    
    // 检查 users 表结构
    if (in_array('users', $tables)) {
        echo "👤 Users table structure:\n";
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        
        foreach ($columns as $column) {
            echo sprintf("- %-20s %s\n", $column['Field'], $column['Type']);
        }
        
        echo "\n";
        
        // 检查是否存在数据
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "Users count: $count\n\n";
    }
    
    // 修复密码字段名称问题
    echo "🔧 Fixing password field name...\n";
    
    // 检查字段是否存在
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'");
    $passwordField = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
    $passwordHashField = $stmt->fetch();
    
    if ($passwordField && !$passwordHashField) {
        // 重命名字段
        $pdo->exec("ALTER TABLE users CHANGE password password_hash VARCHAR(255) NOT NULL");
        echo "✅ Renamed 'password' field to 'password_hash'\n";
    } elseif (!$passwordField && !$passwordHashField) {
        // 添加字段
        $pdo->exec("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NOT NULL AFTER email");
        echo "✅ Added 'password_hash' field\n";
    } else {
        echo "ℹ️  Password field already exists correctly\n";
    }
    
    // 现在尝试插入测试数据
    echo "\n📊 Inserting test data...\n";
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (username, email, password_hash, full_name, role) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $testUsers = [
        ['admin', 'admin@alingai.com', password_hash('admin123', PASSWORD_DEFAULT), 'System Administrator', 'super_admin'],
        ['testuser', 'test@alingai.com', password_hash('test123', PASSWORD_DEFAULT), 'Test User', 'user']
    ];
    
    foreach ($testUsers as $user) {
        $result = $stmt->execute($user);
        if ($result) {
            echo "✅ Created user: {$user[0]} ({$user[1]})\n";
        }
    }
    
    echo "\n🎉 Database setup completed successfully!\n\n";
    
    echo "Test accounts:\n";
    echo "- Admin: admin@alingai.com / admin123\n";
    echo "- User:  test@alingai.com / test123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
