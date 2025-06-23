<?php
/**
 * AlingAi Pro 5.0 - Admin系统SQLite数据库迁移
 * 简化版本，用于演示和开发
 */

try {
    echo "🚀 开始Admin系统SQLite数据库迁移...\n\n";
    
    $dbPath = __DIR__ . '/admin_system.db';
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "📂 使用SQLite数据库: $dbPath\n";
    
    // 创建用户管理相关表
    echo "📊 创建用户管理表...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL UNIQUE,
            display_name VARCHAR(100) NOT NULL,
            permissions TEXT,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20),
            password_hash VARCHAR(255) NOT NULL,
            balance DECIMAL(10,2) DEFAULT 0.00,
            total_tokens INTEGER DEFAULT 0,
            used_tokens INTEGER DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            role_id INTEGER NOT NULL,
            avatar VARCHAR(255),
            last_login_at DATETIME NULL,
            last_login_ip VARCHAR(45),
            login_count INTEGER DEFAULT 0,
            risk_level VARCHAR(20) DEFAULT 'low',
            metadata TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES admin_roles(id)
        )
    ");
    
    // 用户余额日志表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_balance_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            change_type VARCHAR(20) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            balance_before DECIMAL(10,2) NOT NULL,
            balance_after DECIMAL(10,2) NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id)
        )
    ");
    
    // 创建API监控相关表
    echo "📊 创建API监控表...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS api_endpoints (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            endpoint VARCHAR(255) NOT NULL UNIQUE,
            method VARCHAR(10) DEFAULT 'GET',
            total_calls INTEGER DEFAULT 0,
            success_calls INTEGER DEFAULT 0,
            error_calls INTEGER DEFAULT 0,
            avg_response_time DECIMAL(10,3) DEFAULT 0,
            last_called_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS api_call_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            endpoint VARCHAR(255) NOT NULL,
            method VARCHAR(10) NOT NULL,
            status_code INTEGER NOT NULL,
            request_size INTEGER DEFAULT 0,
            response_size INTEGER DEFAULT 0,
            duration DECIMAL(10,3) DEFAULT 0,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id)
        )
    ");
    
    // 创建第三方服务相关表
    echo "📊 创建第三方服务表...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS third_party_services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            service_name VARCHAR(100) NOT NULL,
            service_type VARCHAR(50) NOT NULL,
            config TEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'inactive',
            last_health_check DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS third_party_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            service_id INTEGER NOT NULL,
            action VARCHAR(100) NOT NULL,
            request_data TEXT,
            response_data TEXT,
            success INTEGER DEFAULT 0,
            duration DECIMAL(10,3) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (service_id) REFERENCES third_party_services(id)
        )
    ");
    
    // 创建风控相关表
    echo "📊 创建风控系统表...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS risk_rules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(50) NOT NULL,
            conditions TEXT NOT NULL,
            action VARCHAR(50) NOT NULL,
            enabled INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS risk_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            endpoint VARCHAR(255),
            risk_level VARCHAR(20) NOT NULL,
            risk_score INTEGER NOT NULL,
            risk_factors TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS blacklists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(20) NOT NULL,
            value VARCHAR(255) NOT NULL,
            reason TEXT,
            status VARCHAR(20) DEFAULT 'active',
            expires_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // 创建聊天监控相关表
    echo "📊 创建聊天监控表...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chat_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            session_type VARCHAR(50) DEFAULT 'ai_chat',
            metadata TEXT,
            status VARCHAR(20) DEFAULT 'active',
            last_activity_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chat_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            message_type VARCHAR(50) NOT NULL,
            content TEXT NOT NULL,
            metadata TEXT,
            is_flagged INTEGER DEFAULT 0,
            is_hidden INTEGER DEFAULT 0,
            flag_reason TEXT,
            risk_score INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (session_id) REFERENCES chat_sessions(id),
            FOREIGN KEY (user_id) REFERENCES admin_users(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sensitive_words (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            word VARCHAR(255) NOT NULL,
            category VARCHAR(50) NOT NULL,
            severity INTEGER DEFAULT 1,
            replacement VARCHAR(255),
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // 创建邮件系统相关表
    echo "📊 创建邮件系统表...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS email_configs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            host VARCHAR(255) NOT NULL,
            port INTEGER NOT NULL,
            encryption VARCHAR(10),
            username VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            from_email VARCHAR(255) NOT NULL,
            from_name VARCHAR(255),
            status VARCHAR(20) DEFAULT 'active',
            is_default INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS email_templates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            template_key VARCHAR(100) NOT NULL UNIQUE,
            category VARCHAR(50) NOT NULL,
            subject_template TEXT NOT NULL,
            body_template TEXT NOT NULL,
            variables TEXT,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS email_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            template_key VARCHAR(100),
            to_email VARCHAR(255) NOT NULL,
            subject VARCHAR(500) NOT NULL,
            body TEXT NOT NULL,
            variables TEXT,
            status VARCHAR(20) DEFAULT 'pending',
            error_message TEXT,
            sent_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (config_id) REFERENCES email_configs(id)
        )
    ");
    
    // 创建Token管理相关表
    echo "📊 创建Token管理表...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS jwt_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            status VARCHAR(20) DEFAULT 'active',
            metadata TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS api_keys (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            key_name VARCHAR(100) NOT NULL,
            key_hash VARCHAR(255) NOT NULL,
            permissions TEXT,
            rate_limit INTEGER DEFAULT 1000,
            expires_at DATETIME NULL,
            last_used_at DATETIME NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id)
        )
    ");
    
    // 插入默认数据
    echo "📊 插入默认数据...\n";
    
    // 插入默认角色
    $pdo->exec("
        INSERT OR IGNORE INTO admin_roles (name, display_name, permissions) VALUES 
        ('super_admin', '超级管理员', '{\"permissions\": [\"*\"]}'),
        ('admin', '管理员', '{\"permissions\": [\"user_management\", \"api_monitoring\", \"system_settings\"]}'),
        ('moderator', '监察员', '{\"permissions\": [\"chat_monitoring\", \"risk_control\"]}'),
        ('user', '普通用户', '{\"permissions\": [\"basic_access\"]}')
    ");
    
    // 插入默认管理员账户
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT OR IGNORE INTO admin_users (username, email, password_hash, role_id, status) VALUES 
        ('admin', 'admin@alingai.com', '$adminPassword', 1, 'active')
    ");
    
    // 插入默认敏感词
    $pdo->exec("
        INSERT OR IGNORE INTO sensitive_words (word, category, severity) VALUES 
        ('测试敏感词', '测试', 1),
        ('违规内容', '违规', 3),
        ('危险行为', '安全', 4)
    ");
    
    // 插入默认邮件模板
    $pdo->exec("
        INSERT OR IGNORE INTO email_templates (name, template_key, category, subject_template, body_template, variables) VALUES 
        ('用户注册通知', 'user_register', '用户管理', '欢迎注册AlingAi', '亲爱的{{username}}，欢迎注册AlingAi系统！', '{\"variables\": [\"username\"]}'),
        ('密码重置', 'password_reset', '安全', '密码重置通知', '您的密码重置链接：{{reset_link}}', '{\"variables\": [\"reset_link\"]}')
    ");
    
    echo "✅ Admin系统SQLite数据库迁移完成！\n";
    echo "📍 数据库文件位置: $dbPath\n";
    echo "👤 默认管理员账户: admin / admin123\n\n";
    
    // 显示表统计
    echo "📊 数据库表统计:\n";
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  - $table: $count 条记录\n";
    }
    
} catch (Exception $e) {
    echo "❌ 迁移失败: " . $e->getMessage() . "\n";
    exit(1);
}
