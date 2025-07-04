<?php
/**
 * AlingAi Pro 一键安装脚本
 */

// 设置无限执行时间
set_time_limit(0);

// 设置头部，允许流式输出
header('Content-Type: text/plain');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

// 禁用输出缓冲
if (ob_get_level()) ob_end_clean();
ob_implicit_flush(true);

// 输出函数
function output($message) {
    echo $message . PHP_EOL;
    flush();
}

// 定义根目录
define('ROOT_DIR', dirname(dirname(__DIR__)));
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('CONFIG_DIR', ROOT_DIR . '/config');
define('STORAGE_DIR', ROOT_DIR . '/storage');
define('DATABASE_DIR', STORAGE_DIR . '/database');

// 检查PHP版本
output("检查系统环境...");
if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    output("错误: 需要PHP 7.4或更高版本。当前版本: " . PHP_VERSION);
    exit;
}

// 检查必要的PHP扩展
$requiredExtensions = ['pdo', 'pdo_sqlite', 'json', 'mbstring'];
foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        output("错误: 缺少必要的PHP扩展: {$extension}");
        exit;
    }
}
output("系统环境检查通过。");

// 创建必要的目录
output("创建必要的目录...");
$directories = [
    CONFIG_DIR,
    STORAGE_DIR,
    DATABASE_DIR,
    STORAGE_DIR . '/logs',
    STORAGE_DIR . '/cache',
    STORAGE_DIR . '/uploads',
    STORAGE_DIR . '/sessions',
    PUBLIC_DIR . '/uploads',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            output("错误: 无法创建目录: {$dir}");
            exit;
        }
        output("创建目录: {$dir}");
    }
}

// 执行安装步骤
output("\n开始安装 AlingAi Pro...");

// 步骤1：设置数据库
output("\n[1/6] 设置数据库...");
try {
    // 创建SQLite数据库
    $dbPath = DATABASE_DIR . '/alingai.sqlite';
    
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建配置文件
    $configContent = "<?php\n";
    $configContent .= "/**\n";
    $configContent .= " * AlingAi Pro 系统配置文件\n";
    $configContent .= " * 自动生成于" . date('Y-m-d H:i:s') . "\n";
    $configContent .= " */\n\n";
    $configContent .= "return [\n";
    $configContent .= "    'database' => [\n";
    $configContent .= "        'type' => 'sqlite',\n";
    $configContent .= "        'path' => '" . $dbPath . "',\n";
    $configContent .= "    ],\n";
    $configContent .= "    'app' => [\n";
    $configContent .= "        'name' => 'AlingAi Pro',\n";
    $configContent .= "        'version' => '1.0.0',\n";
    $configContent .= "        'debug' => false,\n";
    $configContent .= "        'timezone' => 'Asia/Shanghai',\n";
    $configContent .= "        'locale' => 'zh_CN',\n";
    $configContent .= "    ],\n";
    $configContent .= "];\n";
    
    file_put_contents(CONFIG_DIR . '/config.php', $configContent);
    
    output("数据库配置文件创建成功");
} catch (PDOException $e) {
    output("错误: 数据库设置失败: " . $e->getMessage());
    exit;
}

// 步骤2：创建数据表
output("\n[2/6] 创建数据表...");
try {
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
    output("创建用户表成功");
    
    // 创建API密钥表
    $db->exec("CREATE TABLE IF NOT EXISTS api_keys (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        api_key VARCHAR(64) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    output("创建API密钥表成功");
    
    // 创建API使用日志表
    $db->exec("CREATE TABLE IF NOT EXISTS api_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        api_key_id INTEGER,
        endpoint VARCHAR(255) NOT NULL,
        method VARCHAR(10) NOT NULL,
        status_code INTEGER NOT NULL,
        response_time FLOAT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (api_key_id) REFERENCES api_keys(id)
    )");
    output("创建API使用日志表成功");
    
    // 创建系统设置表
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    output("创建系统设置表成功");
    
    // 创建默认管理员用户
    $username = 'admin';
    $email = 'admin@alingai.pro';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute([$username, $email, $password]);
    output("创建默认管理员用户成功");
    
    // 创建默认系统设置
    $defaultSettings = [
        ['site_name', 'AlingAi Pro', 'general'],
        ['site_description', 'AlingAi Pro 人工智能平台', 'general'],
        ['api_rate_limit', '100', 'api'],
        ['api_enable_logging', '1', 'api'],
        ['security_level', 'high', 'security'],
        ['maintenance_mode', '0', 'system']
    ];
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }
    output("创建默认系统设置成功");
} catch (PDOException $e) {
    output("错误: 创建数据表失败: " . $e->getMessage());
    exit;
}

// 步骤3：设置安全系统
output("\n[3/6] 设置安全系统...");
try {
    // 创建安全事件表
    $db->exec("CREATE TABLE IF NOT EXISTS security_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_type VARCHAR(50) NOT NULL,
        severity VARCHAR(20) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    output("创建安全事件表成功");
    
    // 创建IP黑名单表
    $db->exec("CREATE TABLE IF NOT EXISTS ip_blacklist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        reason TEXT,
        added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME,
        active BOOLEAN DEFAULT 1
    )");
    output("创建IP黑名单表成功");
    
    // 创建量子加密状态表
    $db->exec("CREATE TABLE IF NOT EXISTS quantum_encryption_status (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        component VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL,
        details TEXT,
        last_check DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    output("创建量子加密状态表成功");
    
    // 创建API安全表
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
    output("创建API端点表成功");
    
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
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO quantum_encryption_status (component, status, details) VALUES (?, ?, ?)");
    foreach ($components as $component) {
        $stmt->execute($component);
    }
    output("初始化量子加密组件状态成功");
} catch (PDOException $e) {
    output("错误: 设置安全系统失败: " . $e->getMessage());
    exit;
}

// 步骤4：设置API系统
output("\n[4/6] 设置API系统...");
try {
    // 创建API配置文件
    $apiConfigContent = "<?php\n";
    $apiConfigContent .= "/**\n";
    $apiConfigContent .= " * AlingAi Pro API配置文件\n";
    $apiConfigContent .= " * 自动生成于" . date('Y-m-d H:i:s') . "\n";
    $apiConfigContent .= " */\n\n";
    $apiConfigContent .= "return [\n";
    $apiConfigContent .= "    'version' => '1.0.0',\n";
    $apiConfigContent .= "    'rate_limit' => 100,\n";
    $apiConfigContent .= "    'enable_logging' => true,\n";
    $apiConfigContent .= "    'token_expiration' => 3600,\n";
    $apiConfigContent .= "    'allowed_origins' => ['*'],\n";
    $apiConfigContent .= "    'endpoints' => [\n";
    $apiConfigContent .= "        'auth' => [\n";
    $apiConfigContent .= "            'login' => '/api/v1/auth/login',\n";
    $apiConfigContent .= "            'logout' => '/api/v1/auth/logout',\n";
    $apiConfigContent .= "            'refresh' => '/api/v1/auth/refresh',\n";
    $apiConfigContent .= "        ],\n";
    $apiConfigContent .= "        'user' => [\n";
    $apiConfigContent .= "            'profile' => '/api/v1/user/profile',\n";
    $apiConfigContent .= "            'update' => '/api/v1/user/update',\n";
    $apiConfigContent .= "        ],\n";
    $apiConfigContent .= "        'ai' => [\n";
    $apiConfigContent .= "            'text' => '/api/v1/ai/text',\n";
    $apiConfigContent .= "            'image' => '/api/v1/ai/image',\n";
    $apiConfigContent .= "            'speech' => '/api/v1/ai/speech',\n";
    $apiConfigContent .= "        ],\n";
    $apiConfigContent .= "    ],\n";
    $apiConfigContent .= "];\n";
    
    file_put_contents(CONFIG_DIR . '/api.php', $apiConfigContent);
    output("API配置文件创建成功");
    
    // 创建API安全配置文件
    $apiSecurityConfigContent = "<?php\n";
    $apiSecurityConfigContent .= "/**\n";
    $apiSecurityConfigContent .= " * AlingAi Pro API安全配置文件\n";
    $apiSecurityConfigContent .= " * 自动生成于" . date('Y-m-d H:i:s') . "\n";
    $apiSecurityConfigContent .= " */\n\n";
    $apiSecurityConfigContent .= "return [\n";
    $apiSecurityConfigContent .= "    'security_level' => 'high',\n";
    $apiSecurityConfigContent .= "    'rate_limiting' => [\n";
    $apiSecurityConfigContent .= "        'enabled' => true,\n";
    $apiSecurityConfigContent .= "        'max_requests' => 100,\n";
    $apiSecurityConfigContent .= "        'period' => 60,\n";
    $apiSecurityConfigContent .= "    ],\n";
    $apiSecurityConfigContent .= "    'ip_whitelist' => [],\n";
    $apiSecurityConfigContent .= "    'ip_blacklist' => [],\n";
    $apiSecurityConfigContent .= "    'cors' => [\n";
    $apiSecurityConfigContent .= "        'allowed_origins' => ['*'],\n";
    $apiSecurityConfigContent .= "        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],\n";
    $apiSecurityConfigContent .= "        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],\n";
    $apiSecurityConfigContent .= "    ],\n";
    $apiSecurityConfigContent .= "];\n";
    
    file_put_contents(CONFIG_DIR . '/api_security.php', $apiSecurityConfigContent);
    output("API安全配置文件创建成功");
    
    // 创建量子加密配置文件
    $quantumConfigContent = "<?php\n";
    $quantumConfigContent .= "/**\n";
    $quantumConfigContent .= " * AlingAi Pro 量子加密配置文件\n";
    $quantumConfigContent .= " * 自动生成于" . date('Y-m-d H:i:s') . "\n";
    $quantumConfigContent .= " */\n\n";
    $quantumConfigContent .= "return [\n";
    $quantumConfigContent .= "    'enabled' => true,\n";
    $quantumConfigContent .= "    'default_algorithm' => 'sm4',\n";
    $quantumConfigContent .= "    'key_distribution' => [\n";
    $quantumConfigContent .= "        'method' => 'qkd',\n";
    $quantumConfigContent .= "        'refresh_interval' => 3600,\n";
    $quantumConfigContent .= "    ],\n";
    $quantumConfigContent .= "    'random_generator' => [\n";
    $quantumConfigContent .= "        'source' => 'quantum',\n";
    $quantumConfigContent .= "        'fallback' => 'pseudo',\n";
    $quantumConfigContent .= "    ],\n";
    $quantumConfigContent .= "    'algorithms' => [\n";
    $quantumConfigContent .= "        'sm2' => [\n";
    $quantumConfigContent .= "            'enabled' => true,\n";
    $quantumConfigContent .= "            'key_length' => 256,\n";
    $quantumConfigContent .= "        ],\n";
    $quantumConfigContent .= "        'sm3' => [\n";
    $quantumConfigContent .= "            'enabled' => true,\n";
    $quantumConfigContent .= "        ],\n";
    $quantumConfigContent .= "        'sm4' => [\n";
    $quantumConfigContent .= "            'enabled' => true,\n";
    $quantumConfigContent .= "            'key_length' => 128,\n";
    $quantumConfigContent .= "            'mode' => 'cbc',\n";
    $quantumConfigContent .= "        ],\n";
    $quantumConfigContent .= "    ],\n";
    $quantumConfigContent .= "];\n";
    
    file_put_contents(CONFIG_DIR . '/quantum_config.php', $quantumConfigContent);
    output("量子加密配置文件创建成功");
} catch (Exception $e) {
    output("错误: 设置API系统失败: " . $e->getMessage());
    exit;
}

// 步骤5：设置管理系统
output("\n[5/6] 设置管理系统...");
try {
    // 创建管理系统配置文件
    $adminConfigContent = "<?php\n";
    $adminConfigContent .= "/**\n";
    $adminConfigContent .= " * AlingAi Pro 管理系统配置文件\n";
    $adminConfigContent .= " * 自动生成于" . date('Y-m-d H:i:s') . "\n";
    $adminConfigContent .= " */\n\n";
    $adminConfigContent .= "return [\n";
    $adminConfigContent .= "    'title' => 'AlingAi Pro 管理系统',\n";
    $adminConfigContent .= "    'logo' => '/admin/images/logo.png',\n";
    $adminConfigContent .= "    'favicon' => '/admin/images/favicon.ico',\n";
    $adminConfigContent .= "    'theme' => 'default',\n";
    $adminConfigContent .= "    'session' => [\n";
    $adminConfigContent .= "        'lifetime' => 7200,\n";
    $adminConfigContent .= "        'secure' => false,\n";
    $adminConfigContent .= "        'http_only' => true,\n";
    $adminConfigContent .= "    ],\n";
    $adminConfigContent .= "    'login' => [\n";
    $adminConfigContent .= "        'max_attempts' => 5,\n";
    $adminConfigContent .= "        'lockout_time' => 15,\n";
    $adminConfigContent .= "    ],\n";
    $adminConfigContent .= "    'menu' => [\n";
    $adminConfigContent .= "        [\n";
    $adminConfigContent .= "            'title' => '仪表盘',\n";
    $adminConfigContent .= "            'icon' => 'dashboard',\n";
    $adminConfigContent .= "            'url' => '/admin/index.php',\n";
    $adminConfigContent .= "        ],\n";
    $adminConfigContent .= "        [\n";
    $adminConfigContent .= "            'title' => '用户管理',\n";
    $adminConfigContent .= "            'icon' => 'users',\n";
    $adminConfigContent .= "            'url' => '/admin/users.php',\n";
    $adminConfigContent .= "        ],\n";
    $adminConfigContent .= "        [\n";
    $adminConfigContent .= "            'title' => 'API管理',\n";
    $adminConfigContent .= "            'icon' => 'api',\n";
    $adminConfigContent .= "            'url' => '/admin/api/index.php',\n";
    $adminConfigContent .= "        ],\n";
    $adminConfigContent .= "        [\n";
    $adminConfigContent .= "            'title' => '安全中心',\n";
    $adminConfigContent .= "            'icon' => 'security',\n";
    $adminConfigContent .= "            'url' => '/admin/security.php',\n";
    $adminConfigContent .= "        ],\n";
    $adminConfigContent .= "        [\n";
    $adminConfigContent .= "            'title' => '系统设置',\n";
    $adminConfigContent .= "            'icon' => 'settings',\n";
    $adminConfigContent .= "            'url' => '/admin/config_manager.php',\n";
    $adminConfigContent .= "        ],\n";
    $adminConfigContent .= "    ],\n";
    $adminConfigContent .= "];\n";
    
    file_put_contents(CONFIG_DIR . '/admin.php', $adminConfigContent);
    output("管理系统配置文件创建成功");
} catch (Exception $e) {
    output("错误: 设置管理系统失败: " . $e->getMessage());
    exit;
}

// 步骤6：完成安装
output("\n[6/6] 完成安装...");
try {
    // 创建安装标记文件
    $installInfo = [
        'version' => '1.0.0',
        'installed_at' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    ];
    
    file_put_contents(ROOT_DIR . '/.installed', json_encode($installInfo, JSON_PRETTY_PRINT));
    output("创建安装标记文件成功");
    
    // 创建启动脚本
    $startScript = "<?php\n";
    $startScript .= "/**\n";
    $startScript .= " * AlingAi Pro 启动脚本\n";
    $startScript .= " */\n\n";
    $startScript .= "echo \"正在启动 AlingAi Pro 系统...\n\";\n\n";
    $startScript .= "// 启动内置服务器\n";
    $startScript .= "echo \"启动内置 PHP 服务器...\n\";\n";
    $startScript .= "echo \"访问地址: http://localhost:8000\n\";\n";
    $startScript .= "echo \"按 Ctrl+C 停止服务器\n\";\n";
    $startScript .= "passthru('php -S localhost:8000 -t public');\n";
    
    file_put_contents(ROOT_DIR . '/start.php', $startScript);
    output("创建启动脚本成功");
    
    // 创建Windows批处理启动脚本
    $batchScript = "@echo off\n";
    $batchScript .= "echo 正在启动 AlingAi Pro 系统...\n";
    $batchScript .= "echo.\n";
    $batchScript .= "php start.php\n";
    $batchScript .= "pause\n";
    
    file_put_contents(ROOT_DIR . '/start.bat', $batchScript);
    output("创建Windows批处理启动脚本成功");
} catch (Exception $e) {
    output("错误: 完成安装失败: " . $e->getMessage());
    exit;
}

// 安装完成
output("\n安装完成！您现在可以访问系统了。");
output("管理员账户: admin");
output("默认密码: admin123");
output("管理员面板: /admin/");
output("前端页面: /");
output("\n请确保在生产环境中更改默认密码！");
