<?php
/**
 * AlingAi Pro 5.0 - MySQL数据库迁移执行器
 * 为管理系统创建MySQL数据库结�?
 */

class MySQLAdminDatabaseMigrator
{
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $pdo;
    
    public function __construct() {
        // 使用环境变量或默认�?
        $this->host = $_ENV['DB_HOST'] ?? '111.180.205.70';
        $this->dbname = $_ENV['DB_NAME'] ?? 'alingai';
        $this->username = $_ENV['DB_USER'] ?? 'AlingAi';
        $this->password = $_ENV['DB_PASS'] ?? 'e5bjzeWCr7k38TrZ';
        
        $this->initializeDatabase(];
    }
    
    /**
     * 初始化数据库连接
     */
    private function initializeDatabase() {
        try {
            $dsn = "mysql:host={$this->host};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->username, $this->password];
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            // 创建数据库（如果不存在）
            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"];
            $this->pdo->exec("USE `{$this->dbname}`"];
            
            echo "�?Database connection established and database '{$this->dbname}' ready\n";
        } catch (Exception $e) {
            echo "�?Database connection failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * 执行所有迁�?
     */
    public function runMigrations(): array
    {
        echo "🚀 Starting Admin MySQL Database Migrations...\n\n";
        
        $migrations = [
            '001_create_admin_users_table' => [$this, 'createAdminUsersTable'], 
            '002_create_admin_tokens_table' => [$this, 'createAdminTokensTable'], 
            '003_create_admin_permissions_table' => [$this, 'createAdminPermissionsTable'], 
            '004_create_admin_user_permissions_table' => [$this, 'createAdminUserPermissionsTable'], 
            '005_create_admin_api_keys_table' => [$this, 'createAdminApiKeysTable'], 
            '006_create_admin_third_party_services_table' => [$this, 'createAdminThirdPartyServicesTable'], 
            '007_create_admin_system_logs_table' => [$this, 'createAdminSystemLogsTable'], 
            '008_create_admin_monitoring_metrics_table' => [$this, 'createAdminMonitoringMetricsTable'], 
            '009_create_admin_risk_control_rules_table' => [$this, 'createAdminRiskControlRulesTable'], 
            '010_create_admin_risk_control_events_table' => [$this, 'createAdminRiskControlEventsTable'], 
            '011_create_admin_email_templates_table' => [$this, 'createAdminEmailTemplatesTable'], 
            '012_create_admin_email_queue_table' => [$this, 'createAdminEmailQueueTable'], 
            '013_create_admin_chat_monitoring_table' => [$this, 'createAdminChatMonitoringTable'], 
            '014_create_admin_sensitive_words_table' => [$this, 'createAdminSensitiveWordsTable'], 
            '015_insert_default_data' => [$this, 'insertDefaultData']
        ];
        
        $results = [];
        
        // 创建迁移记录�?
        $this->createMigrationsTable(];
        
        foreach ($migrations as $migrationName => $migrationMethod) {
            if ($this->isMigrationExecuted($migrationName)) {
                echo "⏭️  Skipping {$migrationName} (already executed)\n";
                $results[$migrationName] = ['status' => 'skipped', 'message' => 'Already executed'];
                continue;
            }
            
            try {
                echo "🔄 Executing migration: {$migrationName}\n";
                call_user_func($migrationMethod];
                $this->recordMigration($migrationName];
                echo "�?Migration completed: {$migrationName}\n";
                $results[$migrationName] = ['status' => 'success', 'message' => 'Migration completed'];
            } catch (Exception $e) {
                $error = "Migration failed: {$e->getMessage()}";
                echo "�?{$error}\n";
                $results[$migrationName] = ['status' => 'failed', 'message' => $error];
            }
        }
        
        return $results;
    }
    
    /**
     * 创建迁移记录�?
     */
    private function createMigrationsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS admin_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) UNIQUE NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 检查迁移是否已执行
     */
    private function isMigrationExecuted(string $migrationName): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM admin_migrations WHERE migration = ?"];
        $stmt->execute([$migrationName]];
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * 记录迁移执行
     */
    private function recordMigration(string $migrationName) {
        $stmt = $this->pdo->prepare("INSERT INTO admin_migrations (migration) VALUES (?)"];
        $stmt->execute([$migrationName]];
    }
    
    /**
     * 迁移001：创建管理员用户�?
     */
    private function createAdminUsersTable() {
        $sql = "
        CREATE TABLE admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            salt VARCHAR(32) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin',
            is_admin BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            last_login_at TIMESTAMP NULL,
            last_login_ip VARCHAR(45],
            login_attempts INT DEFAULT 0,
            locked_until TIMESTAMP NULL,
            profile_data TEXT,
            settings TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username],
            INDEX idx_email (email],
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移002：创建管理员Token�?
     */
    private function createAdminTokensTable() {
        $sql = "
        CREATE TABLE admin_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_id VARCHAR(255) UNIQUE NOT NULL,
            token_type ENUM('access', 'refresh') NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            is_revoked BOOLEAN DEFAULT FALSE,
            device_info TEXT,
            ip_address VARCHAR(45],
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_used_at TIMESTAMP NULL,
            INDEX idx_user_id (user_id],
            INDEX idx_token_id (token_id],
            INDEX idx_expires_at (expires_at],
            FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移003：创建权限表
     */
    private function createAdminPermissionsTable() {
        $sql = "
        CREATE TABLE admin_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            permission_name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            category VARCHAR(50],
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_permission_name (permission_name],
            INDEX idx_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移004：创建用户权限关联表
     */
    private function createAdminUserPermissionsTable() {
        $sql = "
        CREATE TABLE admin_user_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            permission_id INT NOT NULL,
            granted_by INT,
            granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            UNIQUE KEY unique_user_permission (user_id, permission_id],
            INDEX idx_user_id (user_id],
            INDEX idx_permission_id (permission_id],
            FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE,
            FOREIGN KEY (granted_by) REFERENCES admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移005：创建API密钥�?
     */
    private function createAdminApiKeysTable() {
        $sql = "
        CREATE TABLE admin_api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            key_id VARCHAR(255) UNIQUE NOT NULL,
            key_hash VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            permissions TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            last_used_at TIMESTAMP NULL,
            usage_count INT DEFAULT 0,
            rate_limit INT DEFAULT 1000,
            expires_at TIMESTAMP NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_key_id (key_id],
            INDEX idx_created_by (created_by],
            FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移006：创建第三方服务�?
     */
    private function createAdminThirdPartyServicesTable() {
        $sql = "
        CREATE TABLE admin_third_party_services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(50) NOT NULL,
            config TEXT NOT NULL,
            is_enabled BOOLEAN DEFAULT TRUE,
            last_test_at TIMESTAMP NULL,
            last_test_result TEXT,
            response_time DECIMAL(8,2],
            success_rate DECIMAL(5,2],
            error_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_type (type],
            INDEX idx_enabled (is_enabled)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移007：创建系统日志表
     */
    private function createAdminSystemLogsTable() {
        $sql = "
        CREATE TABLE admin_system_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            level VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            context TEXT,
            user_id INT,
            ip_address VARCHAR(45],
            user_agent TEXT,
            request_uri TEXT,
            method VARCHAR(10],
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_level (level],
            INDEX idx_user_id (user_id],
            INDEX idx_created_at (created_at],
            FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移008：创建监控指标表
     */
    private function createAdminMonitoringMetricsTable() {
        $sql = "
        CREATE TABLE admin_monitoring_metrics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            metric_name VARCHAR(100) NOT NULL,
            metric_value DECIMAL(15,4) NOT NULL,
            metric_unit VARCHAR(20],
            tags TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_metric_name (metric_name],
            INDEX idx_timestamp (timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移009：创建风控规则表
     */
    private function createAdminRiskControlRulesTable() {
        $sql = "
        CREATE TABLE admin_risk_control_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            rule_type VARCHAR(50) NOT NULL,
            conditions TEXT NOT NULL,
            actions TEXT NOT NULL,
            priority INT DEFAULT 100,
            is_enabled BOOLEAN DEFAULT TRUE,
            trigger_count INT DEFAULT 0,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_rule_type (rule_type],
            INDEX idx_enabled (is_enabled],
            FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移010：创建风控事件表
     */
    private function createAdminRiskControlEventsTable() {
        $sql = "
        CREATE TABLE admin_risk_control_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50) NOT NULL,
            risk_level VARCHAR(20) NOT NULL,
            user_id INT,
            ip_address VARCHAR(45],
            user_agent TEXT,
            event_data TEXT,
            rule_id INT,
            action_taken TEXT,
            resolved BOOLEAN DEFAULT FALSE,
            resolved_by INT,
            resolved_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_type (event_type],
            INDEX idx_risk_level (risk_level],
            INDEX idx_user_id (user_id],
            FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
            FOREIGN KEY (rule_id) REFERENCES admin_risk_control_rules(id) ON DELETE SET NULL,
            FOREIGN KEY (resolved_by) REFERENCES admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移011：创建邮件模板表
     */
    private function createAdminEmailTemplatesTable() {
        $sql = "
        CREATE TABLE admin_email_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body_html TEXT NOT NULL,
            body_text TEXT,
            variables TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name],
            INDEX idx_active (is_active],
            FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移012：创建邮件队列表
     */
    private function createAdminEmailQueueTable() {
        $sql = "
        CREATE TABLE admin_email_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            to_email VARCHAR(255) NOT NULL,
            to_name VARCHAR(100],
            subject VARCHAR(255) NOT NULL,
            body_html TEXT NOT NULL,
            body_text TEXT,
            template_id INT,
            template_data TEXT,
            priority INT DEFAULT 100,
            status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
            attempts INT DEFAULT 0,
            max_attempts INT DEFAULT 3,
            last_attempt_at TIMESTAMP NULL,
            error_message TEXT,
            sent_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status],
            INDEX idx_priority (priority],
            INDEX idx_created_at (created_at],
            FOREIGN KEY (template_id) REFERENCES admin_email_templates(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移013：创建聊天监控表
     */
    private function createAdminChatMonitoringTable() {
        $sql = "
        CREATE TABLE admin_chat_monitoring (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            conversation_id VARCHAR(255],
            message_id VARCHAR(255],
            message_content TEXT NOT NULL,
            risk_level ENUM('safe', 'low', 'medium', 'high', 'critical') DEFAULT 'safe',
            risk_score DECIMAL(3,2) DEFAULT 0.00,
            flags TEXT,
            action_taken VARCHAR(50],
            reviewed BOOLEAN DEFAULT FALSE,
            reviewed_by INT,
            reviewed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id],
            INDEX idx_risk_level (risk_level],
            INDEX idx_reviewed (reviewed],
            FOREIGN KEY (reviewed_by) REFERENCES admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移014：创建敏感词�?
     */
    private function createAdminSensitiveWordsTable() {
        $sql = "
        CREATE TABLE admin_sensitive_words (
            id INT AUTO_INCREMENT PRIMARY KEY,
            word VARCHAR(255) NOT NULL,
            category VARCHAR(50],
            severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            is_regex BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            hit_count INT DEFAULT 0,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_word (word],
            INDEX idx_category (category],
            INDEX idx_active (is_active],
            FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql];
    }
    
    /**
     * 迁移015：插入默认数�?
     */
    private function insertDefaultData() {
        // 插入默认权限
        $permissions = [
            ['admin.dashboard.view', '查看管理仪表�?, 'dashboard'], 
            ['admin.users.view', '查看用户列表', 'users'], 
            ['admin.users.create', '创建用户', 'users'], 
            ['admin.users.edit', '编辑用户', 'users'], 
            ['admin.users.delete', '删除用户', 'users'], 
            ['admin.system.view', '查看系统信息', 'system'], 
            ['admin.system.config', '系统配置', 'system'], 
            ['admin.logs.view', '查看系统日志', 'system'], 
            ['admin.api.manage', 'API管理', 'api'], 
            ['admin.third_party.manage', '第三方服务管�?, 'third_party'], 
            ['admin.monitoring.view', '监控查看', 'monitoring'], 
            ['admin.risk_control.manage', '风控管理', 'risk_control'], 
            ['admin.email.manage', '邮件管理', 'email'], 
            ['admin.chat.monitor', '聊天监控', 'chat']
        ];
        
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO admin_permissions (permission_name, description, category) VALUES (?, ?, ?)"];
        
        foreach ($permissions as $permission) {
            $stmt->execute($permission];
        }
        
        // 插入默认管理员用�?
        $defaultAdminExists = $this->pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'")->fetchColumn(];
        
        if (!$defaultAdminExists) {
            $salt = bin2hex(random_bytes(16)];
            $password = 'admin123'; // 默认密码，生产环境应该修�?
            $passwordHash = hash('sha256', $password . $salt];
            
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_users (username, email, password_hash, salt, role, is_admin, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            "];
            
            $stmt->execute([
                'admin',
                'admin@alingai.com',
                $passwordHash,
                $salt,
                'super_admin',
                true,
                true
            ]];
            
            // 为默认管理员授予所有权�?
            $adminId = $this->pdo->lastInsertId(];
            $permissions = $this->pdo->query("SELECT id FROM admin_permissions WHERE is_active = TRUE")->fetchAll(PDO::FETCH_COLUMN];
            
            $stmt = $this->pdo->prepare("INSERT INTO admin_user_permissions (user_id, permission_id, granted_by) VALUES (?, ?, ?)"];
            
            foreach ($permissions as $permissionId) {
                $stmt->execute([$adminId, $permissionId, $adminId]];
            }
            
            echo "�?Created default admin user: admin / admin123\n";
        }
        
        // 插入默认邮件模板
        $templates = [
            [
                'welcome',
                '欢迎使用AlingAi Pro',
                '<h1>欢迎，{{name}}�?/h1><p>感谢您使用AlingAi Pro系统�?/p>',
                '欢迎，{{name}}！感谢您使用AlingAi Pro系统�?,
                '["name"]'
            ], 
            [
                'password_reset',
                '密码重置请求',
                '<h1>密码重置</h1><p>请点击以下链接重置您的密码：<a href="{{reset_link}}">重置密码</a></p>',
                '密码重置请求，请访问：{{reset_link}}',
                '["reset_link"]'
            ]
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO admin_email_templates (name, subject, body_html, body_text, variables) 
            VALUES (?, ?, ?, ?, ?)
        "];
        
        foreach ($templates as $template) {
            $stmt->execute($template];
        }
        
        // 插入默认敏感�?
        $sensitiveWords = [
            ['垃圾', 'inappropriate', 'low'], 
            ['诈骗', 'fraud', 'high'], 
            ['色情', 'adult', 'high'], 
            ['暴力', 'violence', 'medium']
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO admin_sensitive_words (word, category, severity) 
            VALUES (?, ?, ?)
        "];
        
        foreach ($sensitiveWords as $word) {
            $stmt->execute($word];
        }
        
        // 插入默认风控规则
        $riskRules = [
            [
                '频繁登录检�?,
                '检测短时间内频繁登录尝�?,
                'login_frequency',
                '{"max_attempts": 5, "time_window": 300}',
                '{"action": "block", "duration": 1800}'
            ], 
            [
                '异常IP检�?,
                '检测来自异常地理位置的访问',
                'ip_location',
                '{"check_geolocation": true, "whitelist": []}',
                '{"action": "alert", "notify": true}'
            ]
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO admin_risk_control_rules (name, description, rule_type, conditions, actions) 
            VALUES (?, ?, ?, ?, ?)
        "];
        
        foreach ($riskRules as $rule) {
            $stmt->execute($rule];
        }
    }
    
    /**
     * 验证数据库结�?
     */
    public function validateDatabase(): array
    {
        $validation = [];
        
        $requiredTables = [
            'admin_users', 'admin_tokens', 'admin_permissions',
            'admin_user_permissions', 'admin_api_keys',
            'admin_third_party_services', 'admin_system_logs',
            'admin_monitoring_metrics', 'admin_risk_control_rules',
            'admin_risk_control_events', 'admin_email_templates',
            'admin_email_queue', 'admin_chat_monitoring',
            'admin_sensitive_words', 'admin_migrations'
        ];
        
        foreach ($requiredTables as $table) {
            $result = $this->pdo->query("SHOW TABLES LIKE '{$table}'")->fetch(];
            $validation[$table] = !empty($result];
        }
        
        return $validation;
    }
    
    /**
     * 获取数据库统计信�?
     */
    public function getDatabaseStats(): array
    {
        $stats = [];
        
        $tables = [
            'admin_users', 'admin_tokens', 'admin_permissions',
            'admin_user_permissions', 'admin_third_party_services',
            'admin_system_logs', 'admin_email_queue'
        ];
        
        foreach ($tables as $table) {
            try {
                $count = $this->pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn(];
                $stats[$table] = $count;
            } catch (Exception $e) {
                $stats[$table] = 0;
            }
        }
        
        return $stats;
    }
}

// 执行迁移
try {
    echo "🗄�? Admin Database Migration for MySQL\n";
    echo "====================================\n";
    echo "Host: " . ($_ENV['DB_HOST'] ?? 'localhost') . "\n";
    echo "Database: " . ($_ENV['DB_NAME'] ?? 'alingai_admin') . "\n";
    echo "User: " . ($_ENV['DB_USER'] ?? 'root') . "\n\n";
    
    $migrator = new MySQLAdminDatabaseMigrator(];
    
    $results = $migrator->runMigrations(];
    
    echo "\n📊 Migration Results:\n";
    echo "==================\n";
    foreach ($results as $migration => $result) {
        $status = $result['status'] === 'success' ? '�? : ($result['status'] === 'skipped' ? '⏭️' : '�?];
        echo "{$status} {$migration}: {$result['status']} - {$result['message']}\n";
    }
    
    echo "\n🔍 Database Structure Validation:\n";
    echo "===============================\n";
    $validation = $migrator->validateDatabase(];
    foreach ($validation as $table => $exists) {
        $status = $exists ? '�? : '�?;
        echo "{$status} {$table}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    echo "\n📈 Database Statistics:\n";
    echo "=====================\n";
    $stats = $migrator->getDatabaseStats(];
    foreach ($stats as $table => $count) {
        echo "📊 {$table}: {$count} records\n";
    }
    
    echo "\n🎉 Admin Database Migration Completed Successfully!\n";
    echo "🔑 Default admin credentials: admin / admin123\n";
    echo "🔧 Please change the default password after first login!\n";
    
} catch (Exception $e) {
    echo "�?Migration failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
