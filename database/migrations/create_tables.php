<?php

/**
 * AlingAi Pro 数据库迁移脚本
 * 创建基本表结构
 */

// 连接到数据库
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "数据库连接成功，开始创建表...\n";
    
    // 创建用户表
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'user',
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "- 用户表创建成功\n";
    
    // 创建设置表
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        key VARCHAR(100) NOT NULL,
        value TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "- 设置表创建成功\n";
    
    // 创建会话表
    $db->exec("CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) PRIMARY KEY,
        user_id INTEGER,
        ip_address VARCHAR(45),
        user_agent TEXT,
        payload TEXT,
        last_activity INTEGER,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "- 会话表创建成功\n";
    
    // 创建API令牌表
    $db->exec("CREATE TABLE IF NOT EXISTS api_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name VARCHAR(255) NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        abilities TEXT,
        last_used_at DATETIME,
        expires_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "- API令牌表创建成功\n";
    
    // 创建日志表
    $db->exec("CREATE TABLE IF NOT EXISTS logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        type VARCHAR(20) NOT NULL,
        message TEXT NOT NULL,
        context TEXT,
        ip_address VARCHAR(45),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "- 日志表创建成功\n";
    
    // 创建钱包表
    $db->exec("CREATE TABLE IF NOT EXISTS wallets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "- 钱包表创建成功\n";
    
    // 创建交易表
    $db->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        wallet_id INTEGER NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        type VARCHAR(20) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'completed',
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE
    )");
    echo "- 交易表创建成功\n";
    
    // 创建系统配置表
    $db->exec("CREATE TABLE IF NOT EXISTS system_configs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        key VARCHAR(100) NOT NULL UNIQUE,
        value TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "- 系统配置表创建成功\n";
    
    // 插入默认管理员用户
    $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@alingai.com', $hashedPassword, 'admin']);
    echo "- 默认管理员用户创建成功\n";
    
    // 插入系统默认配置
    $configs = [
        ['app_name', 'AlingAi Pro'],
        ['app_version', '6.0.0'],
        ['maintenance_mode', '0'],
        ['registration_enabled', '1'],
        ['default_role', 'user'],
        ['default_theme', 'light'],
        ['api_rate_limit', '60'],
        ['file_upload_max_size', '10485760'] // 10MB
    ];
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO system_configs (key, value) VALUES (?, ?)");
    foreach ($configs as $config) {
        $stmt->execute($config);
    }
    echo "- 系统默认配置创建成功\n";
    
    echo "数据库迁移完成！\n";
    
} catch (PDOException $e) {
    die("数据库迁移失败: " . $e->getMessage() . "\n");
} 