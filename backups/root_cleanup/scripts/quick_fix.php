<?php
/**
 * 快速数据库修复和设置
 */

try {
    $config = require __DIR__ . '/../config/database_local.php';
    $mysql = $config['production'];
    
    $pdo = new PDO(
        "mysql:host={$mysql['host']};port={$mysql['port']};dbname={$mysql['database']};charset=utf8mb4",
        $mysql['username'],
        $mysql['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully!\n";
    
    // 检查 users 表的列
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }
    
    echo "Current columns: " . implode(', ', $columns) . "\n";
    
    // 如果没有 password_hash 字段，添加它
    if (!in_array('password_hash', $columns)) {
        if (in_array('password', $columns)) {
            $pdo->exec("ALTER TABLE users CHANGE password password_hash VARCHAR(255) NOT NULL");
            echo "Renamed password to password_hash\n";
        } else {
            $pdo->exec("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NOT NULL AFTER email");
            echo "Added password_hash column\n";
        }
    }
    
    // 插入测试用户
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
    
    $users = [
        ['admin', 'admin@alingai.com', password_hash('admin123', PASSWORD_DEFAULT), 'Administrator', 'super_admin'],
        ['testuser', 'test@alingai.com', password_hash('test123', PASSWORD_DEFAULT), 'Test User', 'user']
    ];
    
    foreach ($users as $user) {
        $stmt->execute($user);
        echo "Inserted user: {$user[0]}\n";
    }
    
    echo "Setup complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
