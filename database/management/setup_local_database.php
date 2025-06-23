<?php

echo "=== 本地SQLite数据库初始化 ===\n\n";

$dbPath = __DIR__ . '/storage/database/alingai_local.sqlite';
$dbDir = dirname($dbPath);

// 创建数据库目录
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
    echo "✓ 创建数据库存储目录: $dbDir\n";
}

try {
    // 连接SQLite数据库
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ SQLite数据库连接成功\n";
    echo "  数据库文件: $dbPath\n\n";
    
    // 创建用户表
    echo "创建数据库表...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            user_type ENUM('regular', 'enterprise', 'admin') DEFAULT 'regular',
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "  ✓ users 表创建完成\n";
    
    // 创建企业用户申请表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_applications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            application_type VARCHAR(50) DEFAULT 'enterprise',
            company_name VARCHAR(255),
            business_license VARCHAR(255),
            contact_person VARCHAR(255),
            contact_phone VARCHAR(50),
            business_description TEXT,
            application_data TEXT,
            status ENUM('pending', 'approved', 'rejected', 'under_review') DEFAULT 'pending',
            admin_notes TEXT,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            reviewed_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "  ✓ user_applications 表创建完成\n";
    
    // 创建用户配额表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_quota (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            api_quota_daily INTEGER DEFAULT 1000,
            api_quota_monthly INTEGER DEFAULT 30000,
            api_calls_today INTEGER DEFAULT 0,
            api_calls_month INTEGER DEFAULT 0,
            token_quota_daily INTEGER DEFAULT 50000,
            token_quota_monthly INTEGER DEFAULT 1500000,
            tokens_used_today INTEGER DEFAULT 0,
            tokens_used_month INTEGER DEFAULT 0,
            last_reset_date DATE DEFAULT (DATE('now')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "  ✓ user_quota 表创建完成\n";
    
    // 创建企业用户配置表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_enterprise_config (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            ai_providers TEXT DEFAULT '[]',
            custom_models TEXT DEFAULT '[]',
            webhook_url VARCHAR(255),
            priority_support BOOLEAN DEFAULT 0,
            custom_branding BOOLEAN DEFAULT 0,
            advanced_analytics BOOLEAN DEFAULT 0,
            dedicated_support_contact VARCHAR(255),
            enterprise_features TEXT DEFAULT '[]',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "  ✓ user_enterprise_config 表创建完成\n";
    
    // 创建测试数据
    echo "\n插入测试数据...\n";
    
    // 插入管理员用户
    $adminData = [
        'username' => 'admin',
        'email' => 'admin@alingai.com',
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'user_type' => 'admin',
        'status' => 'active'
    ];
    
    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO users (username, email, password_hash, user_type, status)
        VALUES (:username, :email, :password_hash, :user_type, :status)
    ");
    $stmt->execute($adminData);
    echo "  ✓ 管理员用户创建完成\n";
    
    // 插入测试普通用户
    $regularUserData = [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
        'user_type' => 'regular',
        'status' => 'active'
    ];
    
    $stmt->execute($regularUserData);
    echo "  ✓ 测试普通用户创建完成\n";
    
    // 插入企业用户申请
    $applicationData = [
        'user_id' => 2, // testuser的ID
        'company_name' => '测试科技有限公司',
        'business_license' => '91000000000000000X',
        'contact_person' => '张三',
        'contact_phone' => '13800138000',
        'business_description' => '专注于AI技术开发和应用的科技公司',
        'application_data' => json_encode([
            'expected_api_calls' => 100000,
            'business_scale' => 'medium',
            'use_cases' => ['文档生成', '智能客服', '数据分析']
        ]),
        'status' => 'pending'
    ];
    
    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO user_applications 
        (user_id, company_name, business_license, contact_person, contact_phone, business_description, application_data, status)
        VALUES (:user_id, :company_name, :business_license, :contact_person, :contact_phone, :business_description, :application_data, :status)
    ");
    $stmt->execute($applicationData);
    echo "  ✓ 企业用户申请创建完成\n";
    
    // 插入用户配额记录
    $quotaData = [
        'user_id' => 2,
        'api_quota_daily' => 1000,
        'api_quota_monthly' => 30000,
        'api_calls_today' => 150,
        'api_calls_month' => 4500,
        'token_quota_daily' => 50000,
        'token_quota_monthly' => 1500000,
        'tokens_used_today' => 7500,
        'tokens_used_month' => 225000
    ];
    
    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO user_quota 
        (user_id, api_quota_daily, api_quota_monthly, api_calls_today, api_calls_month, 
         token_quota_daily, token_quota_monthly, tokens_used_today, tokens_used_month)
        VALUES (:user_id, :api_quota_daily, :api_quota_monthly, :api_calls_today, :api_calls_month,
                :token_quota_daily, :token_quota_monthly, :tokens_used_today, :tokens_used_month)
    ");
    $stmt->execute($quotaData);
    echo "  ✓ 用户配额记录创建完成\n";
    
    // 验证数据
    echo "\n验证数据库数据...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "  用户总数: $userCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_applications");
    $appCount = $stmt->fetch()['count'];
    echo "  申请总数: $appCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_quota");
    $quotaCount = $stmt->fetch()['count'];
    echo "  配额记录总数: $quotaCount\n";
    
    echo "\n✓ 本地SQLite数据库初始化完成!\n";
    echo "数据库文件位置: $dbPath\n";
    
} catch (PDOException $e) {
    echo "✗ 数据库操作失败: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ 发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
