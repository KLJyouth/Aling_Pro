<?php
/**
 * 安全监控系统数据库初始化脚本
 * @version 1.0.0
 * @author AlingAi Team
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 数据库路径
$dbPath = __DIR__ . '/admin.sqlite';

try {
    // 创建数据库连接
    $db = new PDO("sqlite:{$dbPath}");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "数据库连接成功。\n";
    
    // 创建基本安全表
    
    // 安全事件表
    $db->exec("CREATE TABLE IF NOT EXISTS security_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_type VARCHAR(50) NOT NULL,
        severity VARCHAR(20) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "创建安全事件表成功。\n";
    
    // IP黑名单表
    $db->exec("CREATE TABLE IF NOT EXISTS ip_blacklist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        reason TEXT,
        added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME,
        active BOOLEAN DEFAULT 1
    )");
    echo "创建IP黑名单表成功。\n";
    
    // 安全配置表
    $db->exec("CREATE TABLE IF NOT EXISTS security_config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        config_key VARCHAR(50) NOT NULL,
        config_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "创建安全配置表成功。\n";
    
    // 漏洞表
    $db->exec("CREATE TABLE IF NOT EXISTS vulnerabilities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        vulnerability_type VARCHAR(50) NOT NULL,
        severity VARCHAR(20) NOT NULL,
        description TEXT,
        affected_component VARCHAR(50),
        status VARCHAR(20) DEFAULT 'open',
        discovered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        fixed_at DATETIME
    )");
    echo "创建漏洞表成功。\n";
    
    // 创建量子加密监控表
    
    // 量子加密状态表
    $db->exec("CREATE TABLE IF NOT EXISTS quantum_encryption_status (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        component VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL,
        details TEXT,
        last_check DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "创建量子加密状态表成功。\n";
    
    // 量子密钥分发日志表
    $db->exec("CREATE TABLE IF NOT EXISTS quantum_key_distribution_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id VARCHAR(64) NOT NULL,
        key_size INTEGER NOT NULL,
        protocol VARCHAR(20) NOT NULL,
        status VARCHAR(20) NOT NULL,
        error_rate FLOAT,
        intrusion_detected BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "创建量子密钥分发日志表成功。\n";
    
    // 量子加密使用日志表
    $db->exec("CREATE TABLE IF NOT EXISTS quantum_encryption_usage (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        service VARCHAR(50) NOT NULL,
        operation VARCHAR(50) NOT NULL,
        algorithm VARCHAR(20) NOT NULL,
        data_size INTEGER NOT NULL,
        execution_time FLOAT,
        status VARCHAR(20) NOT NULL,
        user_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "创建量子加密使用日志表成功。\n";
    
    // 量子加密警报表
    $db->exec("CREATE TABLE IF NOT EXISTS quantum_encryption_alerts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        alert_type VARCHAR(50) NOT NULL,
        severity VARCHAR(20) NOT NULL,
        description TEXT,
        component VARCHAR(50),
        resolved BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        resolved_at DATETIME
    )");
    echo "创建量子加密警报表成功。\n";
    
    // 创建API安全监控表
    
    // API端点表
    $db->exec("CREATE TABLE IF NOT EXISTS api_endpoints (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint VARCHAR(255) NOT NULL,
        method VARCHAR(10) NOT NULL,
        category VARCHAR(20) NOT NULL,
        description TEXT,
        authentication_required BOOLEAN DEFAULT 1,
        rate_limited BOOLEAN DEFAULT 1,
        active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_checked DATETIME
    )");
    echo "创建API端点表成功。\n";
    
    // API访问日志表
    $db->exec("CREATE TABLE IF NOT EXISTS api_access_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint_id INTEGER,
        ip_address VARCHAR(45),
        user_agent TEXT,
        user_id INTEGER,
        response_code INTEGER,
        response_time FLOAT,
        request_size INTEGER,
        response_size INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "创建API访问日志表成功。\n";
    
    // API威胁日志表
    $db->exec("CREATE TABLE IF NOT EXISTS api_threats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint_id INTEGER,
        threat_type VARCHAR(50) NOT NULL,
        severity VARCHAR(20) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        user_id INTEGER,
        request_data TEXT,
        blocked BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "创建API威胁日志表成功。\n";
    
    // API漏洞表
    $db->exec("CREATE TABLE IF NOT EXISTS api_vulnerabilities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint_id INTEGER,
        vulnerability_type VARCHAR(50) NOT NULL,
        severity VARCHAR(20) NOT NULL,
        description TEXT,
        remediation TEXT,
        status VARCHAR(20) DEFAULT 'open',
        discovered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        fixed_at DATETIME
    )");
    echo "创建API漏洞表成功。\n";
    
    // 初始化一些默认数据
    
    // 初始化默认安全配置
    $defaultConfigs = [
        ['security_level', 'high'],
        ['max_login_attempts', '5'],
        ['lockout_time', '15'],
        ['session_timeout', '30'],
        ['password_min_length', '12'],
        ['password_require_uppercase', '1'],
        ['password_require_lowercase', '1'],
        ['password_require_number', '1'],
        ['password_require_special', '1'],
        ['csrf_protection', '1'],
        ['xss_protection', '1'],
        ['sql_injection_protection', '1'],
        ['rate_limiting_enabled', '1'],
        ['requests_per_minute', '60']
    ];
    
    $stmt = $db->prepare("INSERT INTO security_config (config_key, config_value) VALUES (?, ?)");
    foreach ($defaultConfigs as $config) {
        try {
            $stmt->execute($config);
        } catch (PDOException $e) {
            // 配置可能已经存在，忽略错误
        }
    }
    echo "初始化默认安全配置成功。\n";
    
    // 初始化默认API端点
    $defaultEndpoints = [
        // 系统API
        ['endpoint' => '/api/v1/auth/login', 'method' => 'POST', 'category' => 'system', 'description' => '用户登录API', 'authentication_required' => 0],
        ['endpoint' => '/api/v1/auth/logout', 'method' => 'POST', 'category' => 'system', 'description' => '用户登出API', 'authentication_required' => 1],
        ['endpoint' => '/api/v1/users/profile', 'method' => 'GET', 'category' => 'system', 'description' => '获取用户资料', 'authentication_required' => 1],
        ['endpoint' => '/api/v1/system/status', 'method' => 'GET', 'category' => 'system', 'description' => '系统状态API', 'authentication_required' => 1],
        
        // 本地API
        ['endpoint' => '/api/v1/local/data', 'method' => 'GET', 'category' => 'local', 'description' => '本地数据API', 'authentication_required' => 1],
        ['endpoint' => '/api/v1/local/files', 'method' => 'GET', 'category' => 'local', 'description' => '本地文件API', 'authentication_required' => 1],
        
        // 量子加密API
        ['endpoint' => '/api/v2/quantum/status', 'method' => 'GET', 'category' => 'system', 'description' => '量子加密状态API', 'authentication_required' => 1],
        ['endpoint' => '/api/v2/quantum/encrypt', 'method' => 'POST', 'category' => 'system', 'description' => '量子加密API', 'authentication_required' => 1],
        ['endpoint' => '/api/v2/quantum/decrypt', 'method' => 'POST', 'category' => 'system', 'description' => '量子解密API', 'authentication_required' => 1],
        
        // 用户API
        ['endpoint' => '/api/v1/user-api/endpoint1', 'method' => 'GET', 'category' => 'user', 'description' => '用户API示例1', 'authentication_required' => 1],
        ['endpoint' => '/api/v1/user-api/endpoint2', 'method' => 'POST', 'category' => 'user', 'description' => '用户API示例2', 'authentication_required' => 1]
    ];
    
    $stmt = $db->prepare("INSERT INTO api_endpoints (endpoint, method, category, description, authentication_required) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultEndpoints as $endpoint) {
        try {
            $stmt->execute([
                $endpoint['endpoint'],
                $endpoint['method'],
                $endpoint['category'],
                $endpoint['description'],
                $endpoint['authentication_required']
            ]);
        } catch (PDOException $e) {
            // 端点可能已经存在，忽略错误
        }
    }
    echo "初始化默认API端点成功。\n";
    
    // 初始化量子加密组件状态
    $components = [
        ['quantum_random_generator', '正常', '量子随机数生成器运行正常'],
        ['key_distribution', '正常', '量子密钥分发系统运行正常'],
        ['sm2_engine', '正常', 'SM2加密引擎运行正常'],
        ['sm3_engine', '正常', 'SM3加密引擎运行正常'],
        ['sm4_engine', '正常', 'SM4加密引擎运行正常'],
        ['key_storage', '正常', '量子密钥存储系统运行正常'],
        ['encryption_api', '正常', '量子加密API运行正常']
    ];
    
    $stmt = $db->prepare("INSERT INTO quantum_encryption_status (component, status, details) VALUES (?, ?, ?)");
    foreach ($components as $component) {
        try {
            $stmt->execute($component);
        } catch (PDOException $e) {
            // 组件状态可能已经存在，忽略错误
        }
    }
    echo "初始化量子加密组件状态成功。\n";
    
    echo "数据库初始化完成！\n";
    
} catch (PDOException $e) {
    die("数据库初始化失败: " . $e->getMessage());
} 