<?php
/**
 * MySQL数据库初始化和配置脚本
 * AlingAi Pro - "三完编译" 生产环境部署工具
 * 
 * 功能：
 * - 检测MySQL服务状态
 * - 创建数据库和用户
 * - 执行数据库迁移
 * - 初始化系统数据
 */

require_once __DIR__ . '/../vendor/autoload.php';

class MySQLSetup {
    private $config;
    private $rootConnection;
    private $appConnection;
    
    public function __construct() {
        $this->loadEnvironmentConfig();
    }
    
    /**
     * 加载环境配置
     */
    private function loadEnvironmentConfig() {
        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            $this->error("环境配置文件 .env 不存在！");
        }
        
        $this->config = $this->parseEnvFile($envFile);
        $this->log("✅ 环境配置加载完成");
    }
    
    /**
     * 解析.env文件
     */
    private function parseEnvFile(string $file): array {
        $config = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $config[trim($key)] = trim($value);
            }
        }
        
        return $config;
    }
    
    /**
     * 主要设置流程
     */
    public function setup() {
        $this->printHeader();
        
        try {
            $this->step1_CheckMySQLService();
            $this->step2_CreateDatabase();
            $this->step3_CreateUser();
            $this->step4_RunMigrations();
            $this->step5_SeedInitialData();
            $this->step6_VerifySetup();
            
            $this->success("🎉 MySQL数据库设置完成！系统已准备就绪。");
            
        } catch (Exception $e) {
            $this->error("❌ 设置失败: " . $e->getMessage());
        }
    }
    
    /**
     * 步骤1: 检查MySQL服务状态
     */
    private function step1_CheckMySQLService() {
        $this->log("=== 步骤1: 检查MySQL服务 ===");
        
        // 尝试连接到MySQL根用户
        try {
            $host = $this->config['DB_HOST'] ?? 'localhost';
            $port = $this->config['DB_PORT'] ?? '3306';
            
            $this->rootConnection = new PDO(
                "mysql:host={$host};port={$port}",
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            
            $this->log("✅ MySQL服务运行正常");
            
            // 检查MySQL版本
            $stmt = $this->rootConnection->query("SELECT VERSION() as version");
            $version = $stmt->fetch()['version'];
            $this->log("📋 MySQL版本: {$version}");
            
        } catch (PDOException $e) {
            throw new Exception("MySQL连接失败，请确保MySQL服务已启动: " . $e->getMessage());
        }
    }
    
    /**
     * 步骤2: 创建数据库
     */
    private function step2_CreateDatabase() {
        $this->log("=== 步骤2: 创建数据库 ===");
        
        $dbName = $this->config['DB_DATABASE'] ?? 'alingai_pro';
        $charset = $this->config['DB_CHARSET'] ?? 'utf8mb4';
        $collation = $this->config['DB_COLLATION'] ?? 'utf8mb4_unicode_ci';
        
        // 检查数据库是否存在
        $stmt = $this->rootConnection->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$dbName]);
        
        if ($stmt->rowCount() > 0) {
            $this->log("⚠️ 数据库 '{$dbName}' 已存在，跳过创建");
        } else {
            $sql = "CREATE DATABASE `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}";
            $this->rootConnection->exec($sql);
            $this->log("✅ 数据库 '{$dbName}' 创建成功");
        }
    }
    
    /**
     * 步骤3: 创建用户（如果需要）
     */
    private function step3_CreateUser() {
        $this->log("=== 步骤3: 用户权限配置 ===");
        
        $username = $this->config['DB_USERNAME'] ?? 'root';
        $password = $this->config['DB_PASSWORD'] ?? '';
        $dbName = $this->config['DB_DATABASE'] ?? 'alingai_pro';
        
        if ($username === 'root') {
            $this->log("✅ 使用root用户，跳过用户创建");
            return;
        }
        
        // 检查用户是否存在
        $stmt = $this->rootConnection->prepare("SELECT User FROM mysql.user WHERE User = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            $this->log("⚠️ 用户 '{$username}' 已存在，跳过创建");
        } else {
            // 创建用户
            $this->rootConnection->exec("CREATE USER '{$username}'@'localhost' IDENTIFIED BY '{$password}'");
            $this->log("✅ 用户 '{$username}' 创建成功");
        }
        
        // 授权
        $this->rootConnection->exec("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$username}'@'localhost'");
        $this->rootConnection->exec("FLUSH PRIVILEGES");
        $this->log("✅ 用户权限配置完成");
    }
    
    /**
     * 步骤4: 运行数据库迁移
     */
    private function step4_RunMigrations() {
        $this->log("=== 步骤4: 数据库迁移 ===");
        
        // 连接到应用数据库
        $this->connectToAppDatabase();
        
        $migrationsDir = __DIR__ . '/../database/migrations';
        if (!is_dir($migrationsDir)) {
            $this->log("⚠️ 迁移目录不存在，跳过迁移");
            return;
        }
        
        $migrations = glob($migrationsDir . '/*.sql');
        if (empty($migrations)) {
            $this->log("⚠️ 没有找到迁移文件，跳过迁移");
            return;
        }
        
        foreach ($migrations as $migration) {
            $filename = basename($migration);
            $sql = file_get_contents($migration);
            
            try {
                $this->appConnection->exec($sql);
                $this->log("✅ 迁移完成: {$filename}");
            } catch (PDOException $e) {
                $this->log("⚠️ 迁移跳过: {$filename} - " . $e->getMessage());
            }
        }
    }
    
    /**
     * 步骤5: 初始化数据
     */
    private function step5_SeedInitialData() {
        $this->log("=== 步骤5: 初始化数据 ===");
        
        if (!$this->appConnection) {
            $this->connectToAppDatabase();
        }
        
        // 创建管理员用户
        $this->createAdminUser();
        
        // 初始化系统设置
        $this->initializeSystemSettings();
        
        $this->log("✅ 初始数据创建完成");
    }
    
    /**
     * 创建管理员用户
     */
    private function createAdminUser() {
        try {
            // 检查用户表是否存在
            $stmt = $this->appConnection->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() === 0) {
                $this->log("⚠️ 用户表不存在，跳过管理员创建");
                return;
            }
            
            // 检查是否已有管理员
            $stmt = $this->appConnection->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->log("⚠️ 管理员用户已存在，跳过创建");
                return;
            }
            
            // 创建默认管理员
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $this->appConnection->prepare("
                INSERT INTO users (username, email, password, role, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                'admin',
                'admin@alingai.com',
                $hashedPassword,
                'admin',
                'active'
            ]);
            
            $this->log("✅ 默认管理员创建成功 (用户名: admin, 密码: admin123)");
            
        } catch (PDOException $e) {
            $this->log("⚠️ 管理员创建失败: " . $e->getMessage());
        }
    }
    
    /**
     * 初始化系统设置
     */
    private function initializeSystemSettings() {
        try {
            // 检查设置表是否存在
            $stmt = $this->appConnection->query("SHOW TABLES LIKE 'system_settings'");
            if ($stmt->rowCount() === 0) {
                $this->log("⚠️ 系统设置表不存在，跳过设置初始化");
                return;
            }
            
            $defaultSettings = [
                ['key' => 'app_name', 'value' => 'AlingAi Pro'],
                ['key' => 'app_version', 'value' => '1.0.0'],
                ['key' => 'max_chat_history', 'value' => '1000'],
                ['key' => 'websocket_enabled', 'value' => 'true'],
                ['key' => 'registration_enabled', 'value' => 'true'],
                ['key' => 'quantum_animations_enabled', 'value' => 'true'],
            ];
            
            foreach ($defaultSettings as $setting) {
                $stmt = $this->appConnection->prepare("
                    INSERT IGNORE INTO system_settings (`key`, `value`, created_at, updated_at) 
                    VALUES (?, ?, NOW(), NOW())
                ");
                $stmt->execute([$setting['key'], $setting['value']]);
            }
            
            $this->log("✅ 系统设置初始化完成");
            
        } catch (PDOException $e) {
            $this->log("⚠️ 系统设置初始化失败: " . $e->getMessage());
        }
    }
    
    /**
     * 步骤6: 验证设置
     */
    private function step6_VerifySetup() {
        $this->log("=== 步骤6: 验证数据库设置 ===");
        
        if (!$this->appConnection) {
            $this->connectToAppDatabase();
        }
        
        // 测试数据库连接
        $stmt = $this->appConnection->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = DATABASE()");
        $tableCount = $stmt->fetch()['table_count'];
        $this->log("📊 数据库表数量: {$tableCount}");
        
        // 检查关键表
        $requiredTables = ['users', 'chat_sessions', 'chat_messages'];
        foreach ($requiredTables as $table) {
            $stmt = $this->appConnection->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                $this->log("✅ 表存在: {$table}");
            } else {
                $this->log("⚠️ 表缺失: {$table}");
            }
        }
        
        $this->log("✅ 数据库设置验证完成");
    }
    
    /**
     * 连接到应用数据库
     */
    private function connectToAppDatabase() {
        $host = $this->config['DB_HOST'] ?? 'localhost';
        $port = $this->config['DB_PORT'] ?? '3306';
        $database = $this->config['DB_DATABASE'] ?? 'alingai_pro';
        $username = $this->config['DB_USERNAME'] ?? 'root';
        $password = $this->config['DB_PASSWORD'] ?? '';
        $charset = $this->config['DB_CHARSET'] ?? 'utf8mb4';
        
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
        
        $this->appConnection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    
    /**
     * 打印标题
     */
    private function printHeader() {
        echo "\n";
        echo "====================================================\n";
        echo "    AlingAi Pro MySQL数据库初始化工具 v1.0.0\n";
        echo "    \"三完编译\" 生产环境部署助手\n";
        echo "====================================================\n";
        echo "\n";
    }
    
    /**
     * 日志输出
     */
    private function log(string $message) {
        echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    }
    
    /**
     * 成功消息
     */
    private function success(string $message) {
        echo "\n" . $message . "\n\n";
    }
    
    /**
     * 错误消息并退出
     */
    private function error(string $message) {
        echo "\n❌ 错误: " . $message . "\n\n";
        exit(1);
    }
}

// 运行设置
if (php_sapi_name() === 'cli') {
    $setup = new MySQLSetup();
    $setup->setup();
} else {
    echo "此脚本只能在命令行中运行。\n";
}
