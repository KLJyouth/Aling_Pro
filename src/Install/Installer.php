<?php

namespace AlingAi\Install;

use Psr\Log\LoggerInterface;
use PDO;
use PDOException;

/**
 * 安装程序类
 * 
 * 实现具体的安装步骤
 * 
 * @package AlingAi\Install
 * @version 6.0.0
 */
class Installer
{
    private LoggerInterface $logger;
    private ?PDO $pdo = null;
    
    /**
     * 构造函数
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 执行安装步骤
     * 
     * @param string $step 步骤名称
     * @param array $data 安装数据
     * @return array 执行结果
     * @throws \Exception 执行失败时抛出异常
     */
    public function executeStep(string $step, array $data): array
    {
        $method = 'execute' . str_replace('_', '', ucwords($step, '_'));
        
        if (!method_exists($this, $method)) {
            throw new \Exception("未知的安装步骤: {$step}");
        }
        
        return $this->$method($data);
    }
    
    /**
     * 执行系统环境检查
     * 
     * @param array $data 安装数据
     * @return array 执行结果
     */
    private function executeSystemCheck(array $data): array
    {
        // 检查PHP版本
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            throw new \Exception('PHP版本不满足要求，需要PHP 8.0.0或更高版本');
        }
        
        // 检查必要扩展
        $requiredExtensions = ['pdo', 'mbstring', 'json', 'openssl', 'curl'];
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                throw new \Exception("缺少必要的PHP扩展: {$extension}");
            }
        }
        
        // 检查数据库扩展
        if ($data['database']['type'] === 'mysql' && !extension_loaded('pdo_mysql')) {
            throw new \Exception('缺少PDO MySQL扩展');
        }
        
        if ($data['database']['type'] === 'sqlite' && !extension_loaded('pdo_sqlite')) {
            throw new \Exception('缺少PDO SQLite扩展');
        }
        
        // 检查目录权限
        $requiredPaths = [
            'storage',
            'storage/logs',
            'storage/data',
            'storage/cache',
            'storage/sessions',
            'storage/uploads'
        ];
        
        foreach ($requiredPaths as $path) {
            $fullPath = dirname(__DIR__, 2) . '/' . $path;
            
            if (!file_exists($fullPath)) {
                if (!mkdir($fullPath, 0755, true)) {
                    throw new \Exception("无法创建目录: {$path}");
                }
            } elseif (!is_writable($fullPath)) {
                throw new \Exception("目录不可写: {$path}");
            }
        }
        
        return [
            'message' => '系统环境检查通过',
            'details' => [
                'php_version' => PHP_VERSION,
                'extensions' => get_loaded_extensions()
            ]
        ];
    }
    
    /**
     * 创建配置文件
     * 
     * @param array $data 安装数据
     * @return array 执行结果
     */
    private function executeCreateConfig(array $data): array
    {
        // 生成环境配置
        $envContent = $this->generateEnvConfig($data);
        $envPath = dirname(__DIR__, 2) . '/.env';
        
        // 写入.env文件
        if (file_put_contents($envPath, $envContent) === false) {
            throw new \Exception('无法写入.env配置文件');
        }
        
        // 生成加密密钥
        $encryptionKey = bin2hex(random_bytes(32));
        $encryptionIv = bin2hex(random_bytes(16));
        
        // 更新.env文件添加加密密钥
        $additionalConfig = "\n# 加密配置\nSYSTEM_ENCRYPTION_KEY={$encryptionKey}\nSYSTEM_ENCRYPTION_IV={$encryptionIv}\n";
        file_put_contents($envPath, $additionalConfig, FILE_APPEND);
        
        return [
            'message' => '配置文件创建成功',
            'details' => [
                'env_path' => $envPath
            ]
        ];
    }
    
    /**
     * 设置数据库
     * 
     * @param array $data 安装数据
     * @return array 执行结果
     */
    private function executeSetupDatabase(array $data): array
    {
        $dbConfig = $data['database'];
        
        try {
            // 连接数据库
            if ($dbConfig['type'] === 'mysql') {
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'] ?? '');
                
                // 创建数据库（如果不存在）
                $this->pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // 选择数据库
                $this->pdo->exec("USE `{$dbConfig['database']}`");
            } else {
                // SQLite
                $dbPath = dirname(__DIR__, 2) . '/storage/data/' . $dbConfig['database'];
                $dsn = "sqlite:{$dbPath}";
                $this->pdo = new PDO($dsn);
            }
            
            // 设置PDO属性
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            return [
                'message' => '数据库设置成功',
                'details' => [
                    'type' => $dbConfig['type'],
                    'database' => $dbConfig['database']
                ]
            ];
            
        } catch (PDOException $e) {
            throw new \Exception('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 创建数据表
     * 
     * @param array $data 安装数据
     * @return array 执行结果
     */
    private function executeCreateTables(array $data): array
    {
        if (!$this->pdo) {
            throw new \Exception('数据库未连接');
        }
        
        $dbType = $data['database']['type'];
        $tablePrefix = $data['database']['prefix'] ?? '';
        
        try {
            // 执行数据库迁移
            $migrations = $this->getMigrations($dbType, $tablePrefix);
            
            foreach ($migrations as $tableName => $migration) {
                $this->pdo->exec($migration);
                $this->logger->info("创建数据表: {$tableName}");
            }
            
            return [
                'message' => '数据表创建成功',
                'details' => [
                    'tables_created' => array_keys($migrations)
                ]
            ];
            
        } catch (PDOException $e) {
            throw new \Exception('创建数据表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 创建管理员账户
     * 
     * @param array $data 安装数据
     * @return array 执行结果
     */
    private function executeCreateAdmin(array $data): array
    {
        if (!$this->pdo) {
            throw new \Exception('数据库未连接');
        }
        
        $admin = $data['admin'];
        $tablePrefix = $data['database']['prefix'] ?? '';
        
        try {
            // 生成密码哈希
            $passwordHash = password_hash($admin['password'], PASSWORD_DEFAULT);
            
            // 插入管理员账户
            $stmt = $this->pdo->prepare("
                INSERT INTO {$tablePrefix}users 
                (username, email, password, role, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, 'admin', 1, ?, ?)
            ");
            
            $now = date('Y-m-d H:i:s');
            $stmt->execute([
                $admin['username'],
                $admin['email'],
                $passwordHash,
                $now,
                $now
            ]);
            
            $userId = $this->pdo->lastInsertId();
            
            // 创建管理员配置
            $stmt = $this->pdo->prepare("
                INSERT INTO {$tablePrefix}settings 
                (user_id, key_name, value, created_at, updated_at) 
                VALUES (?, 'admin_preferences', ?, ?, ?)
            ");
            
            $preferences = json_encode([
                'theme' => 'light',
                'language' => 'zh_CN',
                'dashboard_widgets' => ['security', 'analytics', 'users', 'system']
            ]);
            
            $stmt->execute([$userId, $preferences, $now, $now]);
            
            return [
                'message' => '管理员账户创建成功',
                'details' => [
                    'username' => $admin['username'],
                    'email' => $admin['email'],
                    'user_id' => $userId
                ]
            ];
            
        } catch (PDOException $e) {
            throw new \Exception('创建管理员账户失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 设置加密系统
     * 
     * @param array $data 安装数据
     * @return array 执行结果
     */
    private function executeSetupEncryption(array $data): array
    {
        // 生成随机JWT密钥
        $jwtSecret = bin2hex(random_bytes(32));
        
        // 更新.env文件添加JWT密钥
        $envPath = dirname(__DIR__, 2) . '/.env';
        $jwtConfig = "\n# JWT配置\nJWT_SECRET={$jwtSecret}\nJWT_TTL=3600\nJWT_REFRESH_TTL=604800\n";
        
        if (file_put_contents($envPath, $jwtConfig, FILE_APPEND) === false) {
            throw new \Exception('无法更新.env配置文件');
        }
        
        // 启用API加密
        $apiEncryptionConfig = "\n# API加密配置\nAPI_ENCRYPTION_ENABLED=true\nAPI_ENCRYPTION_ALGORITHM=AES-256-CBC\n";
        file_put_contents($envPath, $apiEncryptionConfig, FILE_APPEND);
        
        return [
            'message' => '加密系统设置成功',
            'details' => [
                'jwt_configured' => true,
                'api_encryption' => true
            ]
        ];
    }
    
    /**
     * 完成安装
     * 
     * @param array $data 安装数据
     * @return array 执行结果
     */
    private function executeFinalize(array $data): array
    {
        // 创建默认目录（如果不存在）
        $directories = [
            'storage/uploads/images',
            'storage/uploads/documents',
            'storage/uploads/temp',
            'storage/cache/views',
            'storage/logs/audit'
        ];
        
        foreach ($directories as $dir) {
            $fullPath = dirname(__DIR__, 2) . '/' . $dir;
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
        }
        
        // 创建.htaccess文件保护敏感目录
        $htaccessContent = "Order deny,allow\nDeny from all\n";
        $protectedDirs = [
            'storage',
            'src',
            'vendor',
            'bootstrap'
        ];
        
        foreach ($protectedDirs as $dir) {
            $htaccessPath = dirname(__DIR__, 2) . '/' . $dir . '/.htaccess';
            if (!file_exists($htaccessPath)) {
                file_put_contents($htaccessPath, $htaccessContent);
            }
        }
        
        return [
            'message' => '安装完成',
            'details' => [
                'app_url' => $data['app']['url'],
                'admin_url' => $data['app']['url'] . '/admin',
                'api_url' => $data['app']['url'] . '/api'
            ]
        ];
    }
    
    /**
     * 生成环境配置
     * 
     * @param array $data 安装数据
     * @return string 环境配置内容
     */
    private function generateEnvConfig(array $data): string
    {
        $app = $data['app'];
        $db = $data['database'];
        
        $config = [
            '# 应用配置',
            'APP_NAME="' . $app['name'] . '"',
            'APP_ENV=' . $app['environment'],
            'APP_DEBUG=' . ($app['environment'] === 'production' ? 'false' : 'true'),
            'APP_URL=' . $app['url'],
            'APP_VERSION=6.0.0',
            '',
            '# 数据库配置',
            'DB_CONNECTION=' . $db['type'],
        ];
        
        if ($db['type'] === 'mysql') {
            $config = array_merge($config, [
                'DB_HOST=' . $db['host'],
                'DB_PORT=' . $db['port'],
                'DB_DATABASE=' . $db['database'],
                'DB_USERNAME=' . $db['username'],
                'DB_PASSWORD=' . ($db['password'] ?? ''),
                'DB_PREFIX=' . ($db['prefix'] ?? ''),
            ]);
        } else {
            $config = array_merge($config, [
                'DB_DATABASE=' . $db['database'],
                'DB_PREFIX=' . ($db['prefix'] ?? ''),
            ]);
        }
        
        $config = array_merge($config, [
            '',
            '# 缓存配置',
            'CACHE_DRIVER=file',
            'CACHE_PREFIX=alingai_',
            '',
            '# 日志配置',
            'LOG_CHANNEL=file',
            'LOG_LEVEL=warning',
            '',
            '# 会话配置',
            'SESSION_DRIVER=file',
            'SESSION_LIFETIME=120',
            '',
            '# 邮件配置',
            'MAIL_MAILER=smtp',
            'MAIL_HOST=smtp.example.com',
            'MAIL_PORT=587',
            'MAIL_USERNAME=null',
            'MAIL_PASSWORD=null',
            'MAIL_ENCRYPTION=tls',
            'MAIL_FROM_ADDRESS=null',
            'MAIL_FROM_NAME="${APP_NAME}"',
        ]);
        
        return implode("\n", $config);
    }
    
    /**
     * 获取数据库迁移
     * 
     * @param string $dbType 数据库类型
     * @param string $prefix 表前缀
     * @return array 迁移SQL语句
     */
    private function getMigrations(string $dbType, string $prefix): array
    {
        $migrations = [];
        
        // 用户表
        $migrations["{$prefix}users"] = $dbType === 'mysql' 
            ? $this->getMysqlUsersMigration($prefix)
            : $this->getSqliteUsersMigration($prefix);
        
        // 设置表
        $migrations["{$prefix}settings"] = $dbType === 'mysql'
            ? $this->getMysqlSettingsMigration($prefix)
            : $this->getSqliteSettingsMigration($prefix);
        
        // 会话表
        $migrations["{$prefix}sessions"] = $dbType === 'mysql'
            ? $this->getMysqlSessionsMigration($prefix)
            : $this->getSqliteSessionsMigration($prefix);
        
        // API令牌表
        $migrations["{$prefix}api_tokens"] = $dbType === 'mysql'
            ? $this->getMysqlApiTokensMigration($prefix)
            : $this->getSqliteApiTokensMigration($prefix);
        
        // 日志表
        $migrations["{$prefix}logs"] = $dbType === 'mysql'
            ? $this->getMysqlLogsMigration($prefix)
            : $this->getSqliteLogsMigration($prefix);
        
        // 钱包表
        $migrations["{$prefix}wallets"] = $dbType === 'mysql'
            ? $this->getMysqlWalletsMigration($prefix)
            : $this->getSqliteWalletsMigration($prefix);
        
        // 交易表
        $migrations["{$prefix}transactions"] = $dbType === 'mysql'
            ? $this->getMysqlTransactionsMigration($prefix)
            : $this->getSqliteTransactionsMigration($prefix);
        
        return $migrations;
    }
    
    /**
     * MySQL用户表迁移
     */
    private function getMysqlUsersMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}users` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `username` varchar(100) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `role` varchar(50) NOT NULL DEFAULT 'user',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `email_verified_at` datetime DEFAULT NULL,
            `last_login_at` datetime DEFAULT NULL,
            `2fa_secret` varchar(255) DEFAULT NULL,
            `2fa_enabled` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `users_email_unique` (`email`),
            UNIQUE KEY `users_username_unique` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * SQLite用户表迁移
     */
    private function getSqliteUsersMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}users` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `username` TEXT NOT NULL,
            `email` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `role` TEXT NOT NULL DEFAULT 'user',
            `is_active` INTEGER NOT NULL DEFAULT 1,
            `email_verified_at` TEXT DEFAULT NULL,
            `last_login_at` TEXT DEFAULT NULL,
            `2fa_secret` TEXT DEFAULT NULL,
            `2fa_enabled` INTEGER NOT NULL DEFAULT 0,
            `created_at` TEXT NOT NULL,
            `updated_at` TEXT NOT NULL,
            UNIQUE(`email`),
            UNIQUE(`username`)
        )";
    }
    
    /**
     * MySQL设置表迁移
     */
    private function getMysqlSettingsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}settings` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(10) UNSIGNED DEFAULT NULL,
            `key_name` varchar(100) NOT NULL,
            `value` text,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `settings_user_id_foreign` (`user_id`),
            KEY `settings_key_name_index` (`key_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * SQLite设置表迁移
     */
    private function getSqliteSettingsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}settings` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `user_id` INTEGER DEFAULT NULL,
            `key_name` TEXT NOT NULL,
            `value` TEXT,
            `created_at` TEXT NOT NULL,
            `updated_at` TEXT NOT NULL
        )";
    }
    
    /**
     * MySQL会话表迁移
     */
    private function getMysqlSessionsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}sessions` (
            `id` varchar(100) NOT NULL,
            `user_id` int(10) UNSIGNED NOT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text,
            `payload` text,
            `last_activity` datetime NOT NULL,
            `created_at` datetime NOT NULL,
            `expires_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `sessions_user_id_foreign` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * SQLite会话表迁移
     */
    private function getSqliteSessionsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}sessions` (
            `id` TEXT NOT NULL PRIMARY KEY,
            `user_id` INTEGER NOT NULL,
            `ip_address` TEXT DEFAULT NULL,
            `user_agent` TEXT,
            `payload` TEXT,
            `last_activity` TEXT NOT NULL,
            `created_at` TEXT NOT NULL,
            `expires_at` TEXT NOT NULL
        )";
    }
    
    /**
     * MySQL API令牌表迁移
     */
    private function getMysqlApiTokensMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}api_tokens` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(10) UNSIGNED NOT NULL,
            `name` varchar(255) NOT NULL,
            `token` varchar(100) NOT NULL,
            `abilities` text,
            `last_used_at` datetime DEFAULT NULL,
            `expires_at` datetime DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `api_tokens_token_unique` (`token`),
            KEY `api_tokens_user_id_foreign` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * SQLite API令牌表迁移
     */
    private function getSqliteApiTokensMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}api_tokens` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `user_id` INTEGER NOT NULL,
            `name` TEXT NOT NULL,
            `token` TEXT NOT NULL,
            `abilities` TEXT,
            `last_used_at` TEXT DEFAULT NULL,
            `expires_at` TEXT DEFAULT NULL,
            `created_at` TEXT NOT NULL,
            `updated_at` TEXT NOT NULL,
            UNIQUE(`token`)
        )";
    }
    
    /**
     * MySQL日志表迁移
     */
    private function getMysqlLogsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}logs` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(10) UNSIGNED DEFAULT NULL,
            `level` varchar(20) NOT NULL,
            `message` text NOT NULL,
            `context` text,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `logs_user_id_foreign` (`user_id`),
            KEY `logs_level_index` (`level`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * SQLite日志表迁移
     */
    private function getSqliteLogsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}logs` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `user_id` INTEGER DEFAULT NULL,
            `level` TEXT NOT NULL,
            `message` TEXT NOT NULL,
            `context` TEXT,
            `ip_address` TEXT DEFAULT NULL,
            `user_agent` TEXT,
            `created_at` TEXT NOT NULL
        )";
    }
    
    /**
     * MySQL钱包表迁移
     */
    private function getMysqlWalletsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}wallets` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(10) UNSIGNED NOT NULL,
            `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
            `currency` varchar(10) NOT NULL DEFAULT 'CNY',
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `wallets_user_id_foreign` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * SQLite钱包表迁移
     */
    private function getSqliteWalletsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}wallets` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `user_id` INTEGER NOT NULL,
            `balance` REAL NOT NULL DEFAULT 0.00,
            `currency` TEXT NOT NULL DEFAULT 'CNY',
            `created_at` TEXT NOT NULL,
            `updated_at` TEXT NOT NULL
        )";
    }
    
    /**
     * MySQL交易表迁移
     */
    private function getMysqlTransactionsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}transactions` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `wallet_id` int(10) UNSIGNED NOT NULL,
            `user_id` int(10) UNSIGNED NOT NULL,
            `type` varchar(50) NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `description` text,
            `status` varchar(50) NOT NULL DEFAULT 'completed',
            `reference` varchar(100) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `transactions_wallet_id_foreign` (`wallet_id`),
            KEY `transactions_user_id_foreign` (`user_id`),
            KEY `transactions_type_index` (`type`),
            KEY `transactions_status_index` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * SQLite交易表迁移
     */
    private function getSqliteTransactionsMigration(string $prefix): string
    {
        return "CREATE TABLE IF NOT EXISTS `{$prefix}transactions` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `wallet_id` INTEGER NOT NULL,
            `user_id` INTEGER NOT NULL,
            `type` TEXT NOT NULL,
            `amount` REAL NOT NULL,
            `description` TEXT,
            `status` TEXT NOT NULL DEFAULT 'completed',
            `reference` TEXT DEFAULT NULL,
            `created_at` TEXT NOT NULL,
            `updated_at` TEXT NOT NULL
        )";
    }
} 