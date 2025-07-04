<?php
/**
 * AlingAi Pro 一键部署脚本
 * 自动安装所有系统组件和数据库
 * @version 1.0.0
 * @author AlingAi Team
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 定义根目录
define('ROOT_DIR', __DIR__);
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('CONFIG_DIR', ROOT_DIR . '/config');
define('STORAGE_DIR', ROOT_DIR . '/storage');
define('DATABASE_DIR', STORAGE_DIR . '/database');

// 检查PHP版本
if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    die('错误: 需要PHP 7.4或更高版本。当前版本: ' . PHP_VERSION);
}

// 检查必要的PHP扩展
$requiredExtensions = ['pdo', 'pdo_sqlite', 'json', 'mbstring'];
foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        die('错误: 缺少必要的PHP扩展: ' . $extension);
    }
}

// 创建必要的目录
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
            die('错误: 无法创建目录: ' . $dir);
        }
        echo '创建目录: ' . $dir . PHP_EOL;
    }
}

// 安装步骤
$installSteps = [
    'setup_database' => '设置数据库',
    'create_tables' => '创建数据表',
    'setup_security' => '设置安全系统',
    'setup_api' => '设置API系统',
    'setup_admin' => '设置管理系统',
    'finalize' => '完成安装'
];

// 执行安装步骤
foreach ($installSteps as $step => $description) {
    echo PHP_EOL . '正在执行: ' . $description . PHP_EOL;
    
    try {
        call_user_func($step);
        echo $description . ' - 完成' . PHP_EOL;
    } catch (Exception $e) {
        die('错误: ' . $description . ' 失败: ' . $e->getMessage());
    }
}

echo PHP_EOL . '安装完成！您现在可以访问系统了。' . PHP_EOL;
echo '管理员面板: ' . '/public/admin/' . PHP_EOL;
echo '前端页面: ' . '/public/' . PHP_EOL;

/**
 * 设置数据库
 */
function setup_database() {
    global $CONFIG_DIR, $DATABASE_DIR;
    
    // 创建SQLite数据库
    $dbPath = $DATABASE_DIR . '/alingai.sqlite';
    
    try {
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
        
        file_put_contents($CONFIG_DIR . '/config.php', $configContent);
        
        echo "数据库配置文件创建成功" . PHP_EOL;
        return true;
    } catch (PDOException $e) {
        throw new Exception('数据库设置失败: ' . $e->getMessage());
    }
}

/**
 * 创建数据表
 */
function create_tables() {
    global $DATABASE_DIR;
    
    $dbPath = $DATABASE_DIR . '/alingai.sqlite';
    
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建用户表
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'user',
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            referral_code VARCHAR(20) UNIQUE,
            total_referrals INTEGER DEFAULT 0,
            total_referral_points INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        echo "创建用户表成功" . PHP_EOL;
        
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
        echo "创建API密钥表成功" . PHP_EOL;
        
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
        echo "创建API使用日志表成功" . PHP_EOL;
        
        // 创建系统设置表
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        echo "创建系统设置表成功" . PHP_EOL;
        
        // 创建会员等级表
        $db->exec("CREATE TABLE IF NOT EXISTS membership_levels (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL,
            code VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            price_monthly DECIMAL(10,2) NOT NULL DEFAULT 0,
            price_yearly DECIMAL(10,2) NOT NULL DEFAULT 0,
            duration_days INTEGER NOT NULL DEFAULT 30,
            icon VARCHAR(255),
            color VARCHAR(20),
            benefits TEXT,
            api_quota INTEGER DEFAULT 0,
            ai_quota INTEGER DEFAULT 0,
            storage_quota INTEGER DEFAULT 0,
            bandwidth_quota INTEGER DEFAULT 0,
            discount_percent INTEGER DEFAULT 0,
            priority_support BOOLEAN DEFAULT 0,
            is_featured BOOLEAN DEFAULT 0,
            sort_order INTEGER DEFAULT 0,
            upgrade_points INTEGER DEFAULT 0,
            upgrade_spending DECIMAL(10,2) DEFAULT 0,
            upgrade_months INTEGER DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME
        )");
        echo "创建会员等级表成功" . PHP_EOL;
        
        // 创建会员订阅表
        $db->exec("CREATE TABLE IF NOT EXISTS membership_subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            membership_level_id INTEGER NOT NULL,
            order_id INTEGER,
            subscription_no VARCHAR(50) NOT NULL,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            price_paid DECIMAL(10,2) NOT NULL,
            subscription_type VARCHAR(20) NOT NULL DEFAULT 'monthly',
            auto_renew BOOLEAN DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            cancelled_at DATETIME,
            cancellation_reason TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (membership_level_id) REFERENCES membership_levels(id)
        )");
        echo "创建会员订阅表成功" . PHP_EOL;
        
        // 创建会员积分表
        $db->exec("CREATE TABLE IF NOT EXISTS member_points (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            points INTEGER NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            reference_id VARCHAR(50),
            reference_type VARCHAR(50),
            expires_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
        echo "创建会员积分表成功" . PHP_EOL;
        
        // 创建会员特权表
        $db->exec("CREATE TABLE IF NOT EXISTS member_privileges (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            icon VARCHAR(255),
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            is_featured BOOLEAN DEFAULT 0,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        echo "创建会员特权表成功" . PHP_EOL;
        
        // 创建会员特权与等级关联表
        $db->exec("CREATE TABLE IF NOT EXISTS member_privilege_level (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            privilege_id INTEGER NOT NULL,
            level_id INTEGER NOT NULL,
            value VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (privilege_id) REFERENCES member_privileges(id),
            FOREIGN KEY (level_id) REFERENCES membership_levels(id),
            UNIQUE(privilege_id, level_id)
        )");
        echo "创建会员特权与等级关联表成功" . PHP_EOL;
        
        // 创建会员推荐表
        $db->exec("CREATE TABLE IF NOT EXISTS member_referrals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            referrer_id INTEGER NOT NULL,
            referred_id INTEGER NOT NULL,
            code VARCHAR(20) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            points_awarded INTEGER DEFAULT 0,
            reward_type VARCHAR(50),
            reward_amount DECIMAL(10,2) DEFAULT 0,
            reward_description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (referrer_id) REFERENCES users(id),
            FOREIGN KEY (referred_id) REFERENCES users(id)
        )");
        echo "创建会员推荐表成功" . PHP_EOL;
        
        // 创建配额使用记录表
        $db->exec("CREATE TABLE IF NOT EXISTS quota_usages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            quota_type VARCHAR(50) NOT NULL,
            amount INTEGER NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
        echo "创建配额使用记录表成功" . PHP_EOL;
        
        // 创建订单表
        $db->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            order_type VARCHAR(50) NOT NULL,
            subtotal_amount DECIMAL(10,2) NOT NULL,
            discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            total_amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            payment_method VARCHAR(50),
            payment_status VARCHAR(20) DEFAULT 'pending',
            metadata TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
        echo "创建订单表成功" . PHP_EOL;
        
        // 创建默认管理员用户
        $username = 'admin';
        $email = 'admin@alingai.pro';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$username, $email, $password]);
        echo "创建默认管理员用户成功" . PHP_EOL;
        
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
        echo "创建默认系统设置成功" . PHP_EOL;
        
        // 创建默认会员等级
        $defaultLevels = [
            [
                'name' => '基础会员',
                'code' => 'basic',
                'description' => '基础会员享有基本的AI功能和API访问权限',
                'price_monthly' => 29.99,
                'price_yearly' => 299.99,
                'duration_days' => 30,
                'icon' => 'fa-star',
                'color' => '#3498db',
                'benefits' => json_encode(['基础AI模型访问', '每日100次API调用', '5GB存储空间']),
                'api_quota' => 100,
                'ai_quota' => 100,
                'storage_quota' => 5120,
                'bandwidth_quota' => 10240,
                'sort_order' => 1,
                'status' => 'active'
            ],
            [
                'name' => '高级会员',
                'code' => 'premium',
                'description' => '高级会员享有更多AI功能和更高的API访问限制',
                'price_monthly' => 59.99,
                'price_yearly' => 599.99,
                'duration_days' => 30,
                'icon' => 'fa-crown',
                'color' => '#f1c40f',
                'benefits' => json_encode(['高级AI模型访问', '每日500次API调用', '20GB存储空间', '优先技术支持']),
                'api_quota' => 500,
                'ai_quota' => 500,
                'storage_quota' => 20480,
                'bandwidth_quota' => 51200,
                'priority_support' => 1,
                'sort_order' => 2,
                'status' => 'active'
            ],
            [
                'name' => '专业会员',
                'code' => 'professional',
                'description' => '专业会员享有全部AI功能和无限的API访问',
                'price_monthly' => 99.99,
                'price_yearly' => 999.99,
                'duration_days' => 30,
                'icon' => 'fa-gem',
                'color' => '#9b59b6',
                'benefits' => json_encode(['所有AI模型访问', '无限API调用', '100GB存储空间', '专属客服支持', '自定义模型训练']),
                'api_quota' => -1,
                'ai_quota' => -1,
                'storage_quota' => 102400,
                'bandwidth_quota' => 204800,
                'priority_support' => 1,
                'is_featured' => 1,
                'sort_order' => 3,
                'status' => 'active'
            ]
        ];
        
        $stmt = $db->prepare("INSERT OR IGNORE INTO membership_levels 
            (name, code, description, price_monthly, price_yearly, duration_days, icon, color, benefits, 
            api_quota, ai_quota, storage_quota, bandwidth_quota, priority_support, is_featured, sort_order, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        foreach ($defaultLevels as $level) {
            $stmt->execute([
                $level['name'], $level['code'], $level['description'], $level['price_monthly'], $level['price_yearly'],
                $level['duration_days'], $level['icon'], $level['color'], $level['benefits'], $level['api_quota'],
                $level['ai_quota'], $level['storage_quota'], $level['bandwidth_quota'], 
                $level['priority_support'] ?? 0, $level['is_featured'] ?? 0, $level['sort_order'], $level['status']
            ]);
        }
        echo "创建默认会员等级成功" . PHP_EOL;
        
        // 创建默认会员特权
        $defaultPrivileges = [
            [
                'name' => 'API访问配额',
                'code' => 'api_quota',
                'description' => '每日可使用的API调用次数',
                'icon' => 'fa-code',
                'is_featured' => 1,
                'sort_order' => 1
            ],
            [
                'name' => 'AI模型访问',
                'code' => 'ai_models',
                'description' => '可访问的AI模型类型',
                'icon' => 'fa-robot',
                'is_featured' => 1,
                'sort_order' => 2
            ],
            [
                'name' => '存储空间',
                'code' => 'storage',
                'description' => '可用的存储空间大小',
                'icon' => 'fa-hdd',
                'is_featured' => 0,
                'sort_order' => 3
            ],
            [
                'name' => '优先技术支持',
                'code' => 'priority_support',
                'description' => '获得优先的技术支持响应',
                'icon' => 'fa-headset',
                'is_featured' => 1,
                'sort_order' => 4
            ],
            [
                'name' => '专属功能',
                'code' => 'exclusive_features',
                'description' => '专属于高级会员的特殊功能',
                'icon' => 'fa-star',
                'is_featured' => 1,
                'sort_order' => 5
            ]
        ];
        
        $stmt = $db->prepare("INSERT OR IGNORE INTO member_privileges 
            (name, code, description, icon, is_featured, sort_order, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')");
            
        foreach ($defaultPrivileges as $privilege) {
            $stmt->execute([
                $privilege['name'], $privilege['code'], $privilege['description'], 
                $privilege['icon'], $privilege['is_featured'], $privilege['sort_order']
            ]);
        }
        echo "创建默认会员特权成功" . PHP_EOL;
        
        // 关联会员特权与等级
        $privilegeLevelMappings = [
            // 基础会员特权
            ['api_quota', 'basic', '100次/天'],
            ['ai_models', 'basic', '基础模型'],
            ['storage', 'basic', '5GB'],
            
            // 高级会员特权
            ['api_quota', 'premium', '500次/天'],
            ['ai_models', 'premium', '高级模型'],
            ['storage', 'premium', '20GB'],
            ['priority_support', 'premium', '是'],
            
            // 专业会员特权
            ['api_quota', 'professional', '无限制'],
            ['ai_models', 'professional', '所有模型'],
            ['storage', 'professional', '100GB'],
            ['priority_support', 'professional', '是'],
            ['exclusive_features', 'professional', '是']
        ];
        
        foreach ($privilegeLevelMappings as $mapping) {
            // 获取特权ID
            $privilegeStmt = $db->prepare("SELECT id FROM member_privileges WHERE code = ?");
            $privilegeStmt->execute([$mapping[0]]);
            $privilegeId = $privilegeStmt->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
            
            // 获取等级ID
            $levelStmt = $db->prepare("SELECT id FROM membership_levels WHERE code = ?");
            $levelStmt->execute([$mapping[1]]);
            $levelId = $levelStmt->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
            
            if ($privilegeId && $levelId) {
                $mappingStmt = $db->prepare("INSERT OR IGNORE INTO member_privilege_level 
                    (privilege_id, level_id, value) VALUES (?, ?, ?)");
                $mappingStmt->execute([$privilegeId, $levelId, $mapping[2]]);
            }
        }
        echo "关联会员特权与等级成功" . PHP_EOL;
        
        return true;
    } catch (PDOException $e) {
        throw new Exception('创建数据表失败: ' . $e->getMessage());
    }
}

/**
 * 设置安全系统
 */
function setup_security() {
    global $DATABASE_DIR;
    
    $dbPath = $DATABASE_DIR . '/alingai.sqlite';
    
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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
        echo "创建安全事件表成功" . PHP_EOL;
        
        // 创建IP黑名单表
        $db->exec("CREATE TABLE IF NOT EXISTS ip_blacklist (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address VARCHAR(45) NOT NULL,
            reason TEXT,
            added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME,
            active BOOLEAN DEFAULT 1
        )");
        echo "创建IP黑名单表成功" . PHP_EOL;
        
        // 创建量子加密状态表
        $db->exec("CREATE TABLE IF NOT EXISTS quantum_encryption_status (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            component VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL,
            details TEXT,
            last_check DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        echo "创建量子加密状态表成功" . PHP_EOL;
        
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
        echo "创建API端点表成功" . PHP_EOL;
        
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
        echo "初始化量子加密组件状态成功" . PHP_EOL;
        
        return true;
    } catch (PDOException $e) {
        throw new Exception('设置安全系统失败: ' . $e->getMessage());
    }
}

/**
 * 设置API系统
 */
function setup_api() {
    global $CONFIG_DIR;
    
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
    
    file_put_contents($CONFIG_DIR . '/api.php', $apiConfigContent);
    echo "API配置文件创建成功" . PHP_EOL;
    
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
    
    file_put_contents($CONFIG_DIR . '/api_security.php', $apiSecurityConfigContent);
    echo "API安全配置文件创建成功" . PHP_EOL;
    
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
    
    file_put_contents($CONFIG_DIR . '/quantum_config.php', $quantumConfigContent);
    echo "量子加密配置文件创建成功" . PHP_EOL;
    
    return true;
}

/**
 * 设置管理系统
 */
function setup_admin() {
    global $CONFIG_DIR;
    
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
    
    file_put_contents($CONFIG_DIR . '/admin.php', $adminConfigContent);
    echo "管理系统配置文件创建成功" . PHP_EOL;
    
    return true;
}

/**
 * 完成安装
 */
function finalize() {
    global $ROOT_DIR;
    
    // 创建安装标记文件
    $installInfo = [
        'version' => '1.0.0',
        'installed_at' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    ];
    
    file_put_contents($ROOT_DIR . '/.installed', json_encode($installInfo, JSON_PRETTY_PRINT));
    echo "创建安装标记文件成功" . PHP_EOL;
    
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
    
    file_put_contents($ROOT_DIR . '/start.php', $startScript);
    echo "创建启动脚本成功" . PHP_EOL;
    
    // 创建Windows批处理启动脚本
    $batchScript = "@echo off\n";
    $batchScript .= "echo 正在启动 AlingAi Pro 系统...\n";
    $batchScript .= "echo.\n";
    $batchScript .= "php start.php\n";
    $batchScript .= "pause\n";
    
    file_put_contents($ROOT_DIR . '/start.bat', $batchScript);
    echo "创建Windows批处理启动脚本成功" . PHP_EOL;
    
    return true;
}

/**
 * 设置MCP管理控制平台
 */
function setup_mcp() {
    global $DATABASE_DIR;
    
    $dbPath = $DATABASE_DIR . "/alingai.sqlite";
    
    try {
        $db = new PDO("sqlite:" . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建MCP接口表
        $db->exec("CREATE TABLE IF NOT EXISTS mcp_interfaces (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            endpoint VARCHAR(255) NOT NULL,
            description TEXT,
            method VARCHAR(10) NOT NULL,
            parameters TEXT,
            response_format TEXT,
            is_active INTEGER DEFAULT 1,
            requires_auth INTEGER DEFAULT 1,
            rate_limit INTEGER DEFAULT 60,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        echo "创建MCP接口表成功" . PHP_EOL;
        
        // 创建MCP日志表
        $db->exec("CREATE TABLE IF NOT EXISTS mcp_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            interface_id INTEGER,
            method VARCHAR(10) NOT NULL,
            endpoint VARCHAR(255) NOT NULL,
            request_data TEXT,
            status_code INTEGER NOT NULL,
            response_data TEXT,
            response_time FLOAT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            user_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (interface_id) REFERENCES mcp_interfaces(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
        echo "创建MCP日志表成功" . PHP_EOL;
        
        // 创建MCP配置表
        $db->exec("CREATE TABLE IF NOT EXISTS mcp_configs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key VARCHAR(100) NOT NULL UNIQUE,
            value TEXT,
            group_name VARCHAR(50) DEFAULT \"general\",
            description TEXT,
            is_system INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        echo "创建MCP配置表成功" . PHP_EOL;
        
        // 创建默认MCP接口
        $defaultInterfaces = [
            [
                "name" => "系统状态",
                "endpoint" => "system/status",
                "description" => "获取系统状态信息",
                "method" => "GET",
                "parameters" => "{}",
                "response_format" => "{\"status\":\"string\",\"message\":\"string\"}",
                "is_active" => 1,
                "requires_auth" => 1,
                "rate_limit" => 60
            ],
            [
                "name" => "系统资源",
                "endpoint" => "system/resources",
                "description" => "获取系统资源使用情况",
                "method" => "GET",
                "parameters" => "{}",
                "response_format" => "{\"cpu_usage\":\"number\",\"memory_usage\":\"number\",\"disk_usage\":\"number\"}",
                "is_active" => 1,
                "requires_auth" => 1,
                "rate_limit" => 60
            ],
            [
                "name" => "用户统计",
                "endpoint" => "users/stats",
                "description" => "获取用户统计数据",
                "method" => "GET",
                "parameters" => "{}",
                "response_format" => "{\"dates\":[\"string\"],\"new_users\":[\"number\"],\"active_users\":[\"number\"]}",
                "is_active" => 1,
                "requires_auth" => 1,
                "rate_limit" => 60
            ],
            [
                "name" => "API统计",
                "endpoint" => "api/stats",
                "description" => "获取API使用统计数据",
                "method" => "GET",
                "parameters" => "{}",
                "response_format" => "{\"endpoints\":[\"string\"],\"calls\":[\"number\"]}",
                "is_active" => 1,
                "requires_auth" => 1,
                "rate_limit" => 60
            ]
        ];
        
        $stmt = $db->prepare("INSERT INTO mcp_interfaces (name, endpoint, description, method, parameters, response_format, is_active, requires_auth, rate_limit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($defaultInterfaces as $interface) {
            $stmt->execute([
                $interface["name"],
                $interface["endpoint"],
                $interface["description"],
                $interface["method"],
                $interface["parameters"],
                $interface["response_format"],
                $interface["is_active"],
                $interface["requires_auth"],
                $interface["rate_limit"]
            ]);
        }
        echo "创建默认MCP接口成功" . PHP_EOL;
        
        // 创建默认MCP配置
        $defaultConfigs = [
            ["base_url", "https://mcp.alingai.pro/api/v1", "general", "MCP API基础URL", 1],
            ["api_key", md5(uniqid(mt_rand(), true)), "general", "MCP API密钥", 1],
            ["api_secret", bin2hex(random_bytes(32)), "general", "MCP API密钥", 1],
            ["log_all_calls", "1", "general", "是否记录所有API调用", 1],
            ["enabled", "1", "general", "是否启用MCP功能", 1],
            ["monitoring_enabled", "1", "monitoring", "是否启用系统监控", 1],
            ["report_interval", "300", "monitoring", "监控数据上报间隔（秒）", 1],
            ["allow_remote_maintenance", "0", "maintenance", "是否允许远程维护", 1],
            ["report_security_events", "1", "security", "安全事件上报", 1]
        ];
        
        $stmt = $db->prepare("INSERT INTO mcp_configs (key, value, group_name, description, is_system) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($defaultConfigs as $config) {
            $stmt->execute($config);
        }
        echo "创建默认MCP配置成功" . PHP_EOL;
        
        return true;
    } catch (PDOException $e) {
        throw new Exception("设置MCP管理控制平台失败: " . $e->getMessage());
    }
}
