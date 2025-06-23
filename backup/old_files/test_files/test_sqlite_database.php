<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 本地开发环境数据库测试\n";
echo "===========================\n";

try {
    // 首先尝试本地SQLite数据库用于开发
    $sqliteDb = __DIR__ . '/storage/database/alingai.db';
    $sqliteDir = dirname($sqliteDb);
    
    // 确保目录存在
    if (!is_dir($sqliteDir)) {
        mkdir($sqliteDir, 0755, true);
        echo "📁 创建SQLite数据库目录\n";
    }
    
    echo "📱 使用SQLite数据库进行开发测试: $sqliteDb\n";
    
    $pdo = new PDO("sqlite:$sqliteDb", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ SQLite数据库连接成功\n";
    
    // 创建用户表
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        username VARCHAR(255),
        name VARCHAR(255),
        role VARCHAR(50) DEFAULT 'user',
        status VARCHAR(20) DEFAULT 'active',
        permissions TEXT DEFAULT '[]',
        avatar VARCHAR(500),
        phone VARCHAR(20),
        last_login_at TIMESTAMP NULL,
        login_count INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL
    );
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ 用户表创建/验证成功\n";
    
    // 检查用户数量
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "📊 用户数量: $userCount\n";
    
    if ($userCount == 0) {
        echo "📝 创建测试用户...\n";
        $hashedPassword = password_hash('test123456', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (email, password, username, role, status, created_at) VALUES (?, ?, ?, ?, ?, datetime('now'))");
        $result = $stmt->execute([
            'test@example.com',
            $hashedPassword,
            'testuser',
            'user',
            'active'
        ]);
        
        if ($result) {
            echo "✅ 测试用户创建成功\n";
            echo "   邮箱: test@example.com\n";
            echo "   密码: test123456\n";
        }
    } else {
        echo "📋 现有用户列表:\n";
        $users = $pdo->query("SELECT id, email, username, role, status, created_at FROM users LIMIT 5")->fetchAll();
        foreach ($users as $user) {
            echo "   ID:{$user['id']} | {$user['email']} | {$user['username']} | {$user['role']} | {$user['status']} | {$user['created_at']}\n";
        }
    }
    
    // 测试密码验证
    echo "\n🔐 测试密码验证:\n";
    $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    $user = $stmt->fetch();
    
    if ($user) {
        $isValid = password_verify('test123456', $user['password']);
        echo "   用户ID: {$user['id']}\n";
        echo "   邮箱: {$user['email']}\n";
        echo "   密码验证结果: " . ($isValid ? "✅ 正确" : "❌ 错误") . "\n";
        
        if ($isValid) {
            echo "\n🎯 数据库准备完成，可以进行JWT认证测试\n";
        }
    } else {
        echo "   ⚠️ 找不到测试用户\n";
    }
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "   文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
