<?php
/**
 * AlingAi Pro 5.0 - 数据库迁移执行器
 * 自动执行所有管理系统数据库迁移
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

class AdminDatabaseMigrator
{
    private $logger;
    private $dbPath;
    private $pdo;
    
    public function __construct() {
        $this->logger = new \AlingAi\Utils\Logger('DatabaseMigrator');
        $this->dbPath = __DIR__ . '/../../../database/admin_system.db';
        $this->initializeDatabase();
    }
    
    /**
     * 初始化数据库连接
     */
    private function initializeDatabase() {
        try {
            // 确保数据库目录存在
            $dbDir = dirname($this->dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $this->pdo = new PDO("sqlite:{$this->dbPath}");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            
            $this->logger->info('Database connection established');
        } catch (Exception $e) {
            $this->logger->error('Database connection failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 执行所有迁移
     */
    public function runMigrations(): array
    {
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
        
        // 创建迁移记录表
        $this->createMigrationsTable();
        
        foreach ($migrations as $migrationName => $migrationMethod) {
            if ($this->isMigrationExecuted($migrationName)) {
                $results[$migrationName] = ['status' => 'skipped', 'message' => 'Already executed'];
                continue;
            }
            
            try {
                $this->logger->info("Executing migration: {$migrationName}");
                call_user_func($migrationMethod);
                $this->recordMigration($migrationName);
                $results[$migrationName] = ['status' => 'success', 'message' => 'Migration completed'];
            } catch (Exception $e) {
                $error = "Migration failed: {$e->getMessage()}";
                $this->logger->error($error);
                $results[$migrationName] = ['status' => 'failed', 'message' => $error];
            }
        }
        
        return $results;
    }
    
    /**
     * 创建迁移记录表
     */
    private function createMigrationsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS admin_migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) UNIQUE NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }
    
    /**
     * 检查迁移是否已执行
     */
    private function isMigrationExecuted(string $migrationName): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM admin_migrations WHERE migration = ?");
        $stmt->execute([$migrationName]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * 记录迁移执行
     */
    private function recordMigration(string $migrationName) {
        $stmt = $this->pdo->prepare("INSERT INTO admin_migrations (migration) VALUES (?)");
        $stmt->execute([$migrationName]);
    }
    
    /**
     * 迁移001：创建管理员用户表
     */
    private function createAdminUsersTable() {
        $sql = "
        CREATE TABLE admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            salt VARCHAR(32) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin',
            is_admin BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            last_login_at DATETIME,
            last_login_ip VARCHAR(45),
            login_attempts INTEGER DEFAULT 0,
            locked_until DATETIME,
            profile_data TEXT,
            settings TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_users_username ON admin_users(username)");
        $this->pdo->exec("CREATE INDEX idx_admin_users_email ON admin_users(email)");
        $this->pdo->exec("CREATE INDEX idx_admin_users_role ON admin_users(role)");
    }
    
    /**
     * 迁移002：创建管理员Token表
     */
    private function createAdminTokensTable() {
        $sql = "
        CREATE TABLE admin_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token_id VARCHAR(255) UNIQUE NOT NULL,
            token_type VARCHAR(20) NOT NULL CHECK(token_type IN ('access', 'refresh')),
            token_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            is_revoked BOOLEAN DEFAULT FALSE,
            device_info TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_used_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
        )";
        
        $this->pdo->exec($sql);
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_tokens_user_id ON admin_tokens(user_id)");
        $this->pdo->exec("CREATE INDEX idx_admin_tokens_token_id ON admin_tokens(token_id)");
        $this->pdo->exec("CREATE INDEX idx_admin_tokens_expires_at ON admin_tokens(expires_at)");
    }
    
    /**
     * 迁移003：创建权限表
     */
    private function createAdminPermissionsTable() {
        $sql = "
        CREATE TABLE admin_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            permission_name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            category VARCHAR(50),
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_permissions_name ON admin_permissions(permission_name)");
        $this->pdo->exec("CREATE INDEX idx_admin_permissions_category ON admin_permissions(category)");
    }
    
    /**
     * 迁移004：创建用户权限关联表
     */
    private function createAdminUserPermissionsTable() {
        $sql = "
        CREATE TABLE admin_user_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            permission_id INTEGER NOT NULL,
            granted_by INTEGER,
            granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME,
            UNIQUE(user_id, permission_id),
            FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE,
            FOREIGN KEY (granted_by) REFERENCES admin_users(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_user_permissions_user_id ON admin_user_permissions(user_id)");
        $this->pdo->exec("CREATE INDEX idx_admin_user_permissions_permission_id ON admin_user_permissions(permission_id)");
    }
    
    /**
     * 迁移005：创建API密钥表
     */
    private function createAdminApiKeysTable() {
        $sql = "
        CREATE TABLE admin_api_keys (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key_id VARCHAR(255) UNIQUE NOT NULL,
            key_hash VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            permissions TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            last_used_at DATETIME,
            usage_count INTEGER DEFAULT 0,
            rate_limit INTEGER DEFAULT 1000,
            expires_at DATETIME,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES admin_users(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_api_keys_key_id ON admin_api_keys(key_id)");
        $this->pdo->exec("CREATE INDEX idx_admin_api_keys_created_by ON admin_api_keys(created_by)");
    }
    
    /**
     * 迁移006：创建第三方服务表
     */
    private function createAdminThirdPartyServicesTable() {
        $sql = "
        CREATE TABLE admin_third_party_services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(50) NOT NULL,
            config TEXT NOT NULL,
            is_enabled BOOLEAN DEFAULT TRUE,
            last_test_at DATETIME,
            last_test_result TEXT,
            response_time REAL,
            success_rate REAL,
            error_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_third_party_services_type ON admin_third_party_services(type)");
        $this->pdo->exec("CREATE INDEX idx_admin_third_party_services_enabled ON admin_third_party_services(is_enabled)");
    }
    
    /**
     * 迁移007：创建系统日志表
     */
    private function createAdminSystemLogsTable() {
        $sql = "
        CREATE TABLE admin_system_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            level VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            context TEXT,
            user_id INTEGER,
            ip_address VARCHAR(45),
            user_agent TEXT,
            request_uri TEXT,
            method VARCHAR(10),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_system_logs_level ON admin_system_logs(level)");
        $this->pdo->exec("CREATE INDEX idx_admin_system_logs_user_id ON admin_system_logs(user_id)");
        $this->pdo->exec("CREATE INDEX idx_admin_system_logs_created_at ON admin_system_logs(created_at)");
    }
    
    /**
     * 迁移008：创建监控指标表
     */
    private function createAdminMonitoringMetricsTable() {
        $sql = "
        CREATE TABLE admin_monitoring_metrics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            metric_name VARCHAR(100) NOT NULL,
            metric_value REAL NOT NULL,
            metric_unit VARCHAR(20),
            tags TEXT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_monitoring_metrics_name ON admin_monitoring_metrics(metric_name)");
        $this->pdo->exec("CREATE INDEX idx_admin_monitoring_metrics_timestamp ON admin_monitoring_metrics(timestamp)");
    }
    
    /**
     * 迁移009：创建风控规则表
     */
    private function createAdminRiskControlRulesTable() {
        $sql = "
        CREATE TABLE admin_risk_control_rules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            rule_type VARCHAR(50) NOT NULL,
            conditions TEXT NOT NULL,
            actions TEXT NOT NULL,
            priority INTEGER DEFAULT 100,
            is_enabled BOOLEAN DEFAULT TRUE,
            trigger_count INTEGER DEFAULT 0,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES admin_users(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_rules_type ON admin_risk_control_rules(rule_type)");
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_rules_enabled ON admin_risk_control_rules(is_enabled)");
    }
    
    /**
     * 迁移010：创建风控事件表
     */
    private function createAdminRiskControlEventsTable() {
        $sql = "
        CREATE TABLE admin_risk_control_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_type VARCHAR(50) NOT NULL,
            risk_level VARCHAR(20) NOT NULL,
            user_id INTEGER,
            ip_address VARCHAR(45),
            user_agent TEXT,
            event_data TEXT,
            rule_id INTEGER,
            action_taken TEXT,
            resolved BOOLEAN DEFAULT FALSE,
            resolved_by INTEGER,
            resolved_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id),
            FOREIGN KEY (rule_id) REFERENCES admin_risk_control_rules(id),
            FOREIGN KEY (resolved_by) REFERENCES admin_users(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_events_type ON admin_risk_control_events(event_type)");
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_events_level ON admin_risk_control_events(risk_level)");
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_events_user_id ON admin_risk_control_events(user_id)");
    }
    
    /**
     * 迁移011：创建邮件模板表
     */
    private function createAdminEmailTemplatesTable() {
        $sql = "
        CREATE TABLE admin_email_templates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) UNIQUE NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body_html TEXT NOT NULL,
            body_text TEXT,
            variables TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES admin_users(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_email_templates_name ON admin_email_templates(name)");
        $this->pdo->exec("CREATE INDEX idx_admin_email_templates_active ON admin_email_templates(is_active)");
    }
    
    /**
     * 迁移012：创建邮件队列表
     */
    private function createAdminEmailQueueTable() {
        $sql = "
        CREATE TABLE admin_email_queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            to_email VARCHAR(255) NOT NULL,
            to_name VARCHAR(100),
            subject VARCHAR(255) NOT NULL,
            body_html TEXT NOT NULL,
            body_text TEXT,
            template_id INTEGER,
            template_data TEXT,
            priority INTEGER DEFAULT 100,
            status VARCHAR(20) DEFAULT 'pending',
            attempts INTEGER DEFAULT 0,
            max_attempts INTEGER DEFAULT 3,
            last_attempt_at DATETIME,
            error_message TEXT,
            sent_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (template_id) REFERENCES admin_email_templates(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_email_queue_status ON admin_email_queue(status)");
        $this->pdo->exec("CREATE INDEX idx_admin_email_queue_priority ON admin_email_queue(priority)");
        $this->pdo->exec("CREATE INDEX idx_admin_email_queue_created_at ON admin_email_queue(created_at)");
    }
    
    /**
     * 迁移013：创建聊天监控表
     */
    private function createAdminChatMonitoringTable() {
        $sql = "
        CREATE TABLE admin_chat_monitoring (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            conversation_id VARCHAR(255),
            message_id VARCHAR(255),
            message_content TEXT NOT NULL,
            risk_level VARCHAR(20) DEFAULT 'safe',
            risk_score REAL DEFAULT 0.0,
            flags TEXT,
            action_taken VARCHAR(50),
            reviewed BOOLEAN DEFAULT FALSE,
            reviewed_by INTEGER,
            reviewed_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (reviewed_by) REFERENCES admin_users(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_chat_monitoring_user_id ON admin_chat_monitoring(user_id)");
        $this->pdo->exec("CREATE INDEX idx_admin_chat_monitoring_risk_level ON admin_chat_monitoring(risk_level)");
        $this->pdo->exec("CREATE INDEX idx_admin_chat_monitoring_reviewed ON admin_chat_monitoring(reviewed)");
    }
    
    /**
     * 迁移014：创建敏感词表
     */
    private function createAdminSensitiveWordsTable() {
        $sql = "
        CREATE TABLE admin_sensitive_words (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            word VARCHAR(255) NOT NULL,
            category VARCHAR(50),
            severity VARCHAR(20) DEFAULT 'medium',
            is_regex BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            hit_count INTEGER DEFAULT 0,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES admin_users(id)
        )";
        
        $this->pdo->exec($sql);
        
        $this->pdo->exec("CREATE INDEX idx_admin_sensitive_words_word ON admin_sensitive_words(word)");
        $this->pdo->exec("CREATE INDEX idx_admin_sensitive_words_category ON admin_sensitive_words(category)");
        $this->pdo->exec("CREATE INDEX idx_admin_sensitive_words_active ON admin_sensitive_words(is_active)");
    }
    
    /**
     * 迁移015：插入默认数据
     */
    private function insertDefaultData() {
        // 插入默认权限
        $permissions = [
            ['admin.dashboard.view', '查看管理仪表板', 'dashboard'],
            ['admin.users.view', '查看用户列表', 'users'],
            ['admin.users.create', '创建用户', 'users'],
            ['admin.users.edit', '编辑用户', 'users'],
            ['admin.users.delete', '删除用户', 'users'],
            ['admin.system.view', '查看系统信息', 'system'],
            ['admin.system.config', '系统配置', 'system'],
            ['admin.logs.view', '查看系统日志', 'system'],
            ['admin.api.manage', 'API管理', 'api'],
            ['admin.third_party.manage', '第三方服务管理', 'third_party'],
            ['admin.monitoring.view', '监控查看', 'monitoring'],
            ['admin.risk_control.manage', '风控管理', 'risk_control'],
            ['admin.email.manage', '邮件管理', 'email'],
            ['admin.chat.monitor', '聊天监控', 'chat']
        ];
        
        $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO admin_permissions (permission_name, description, category) VALUES (?, ?, ?)");
        
        foreach ($permissions as $permission) {
            $stmt->execute($permission);
        }
        
        // 插入默认管理员用户
        $defaultAdminExists = $this->pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'")->fetchColumn();
        
        if (!$defaultAdminExists) {
            $salt = bin2hex(random_bytes(16));
            $password = 'admin123'; // 默认密码，生产环境应该修改
            $passwordHash = hash('sha256', $password . $salt);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_users (username, email, password_hash, salt, role, is_admin, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                'admin',
                'admin@alingai.com',
                $passwordHash,
                $salt,
                'super_admin',
                true,
                true
            ]);
            
            // 为默认管理员授予所有权限
            $adminId = $this->pdo->lastInsertId();
            $permissions = $this->pdo->query("SELECT id FROM admin_permissions WHERE is_active = TRUE")->fetchAll(PDO::FETCH_COLUMN);
            
            $stmt = $this->pdo->prepare("INSERT INTO admin_user_permissions (user_id, permission_id, granted_by) VALUES (?, ?, ?)");
            
            foreach ($permissions as $permissionId) {
                $stmt->execute([$adminId, $permissionId, $adminId]);
            }
        }
        
        // 插入默认邮件模板
        $templates = [
            [
                'welcome',
                '欢迎使用AlingAi Pro',
                '<h1>欢迎，{{name}}！</h1><p>感谢您使用AlingAi Pro系统。</p>',
                '欢迎，{{name}}！感谢您使用AlingAi Pro系统。',
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
            INSERT OR IGNORE INTO admin_email_templates (name, subject, body_html, body_text, variables) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($templates as $template) {
            $stmt->execute($template);
        }
        
        // 插入默认敏感词
        $sensitiveWords = [
            ['垃圾', 'inappropriate', 'low'],
            ['诈骗', 'fraud', 'high'],
            ['色情', 'adult', 'high'],
            ['暴力', 'violence', 'medium']
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO admin_sensitive_words (word, category, severity) 
            VALUES (?, ?, ?)
        ");
        
        foreach ($sensitiveWords as $word) {
            $stmt->execute($word);
        }
        
        // 插入默认风控规则
        $riskRules = [
            [
                '频繁登录检测',
                '检测短时间内频繁登录尝试',
                'login_frequency',
                '{"max_attempts": 5, "time_window": 300}',
                '{"action": "block", "duration": 1800}'
            ],
            [
                '异常IP检测',
                '检测来自异常地理位置的访问',
                'ip_location',
                '{"check_geolocation": true, "whitelist": []}',
                '{"action": "alert", "notify": true}'
            ]
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO admin_risk_control_rules (name, description, rule_type, conditions, actions) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($riskRules as $rule) {
            $stmt->execute($rule);
        }
    }
    
    /**
     * 验证数据库结构
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
            $result = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'")->fetch();
            $validation[$table] = !empty($result);
        }
        
        return $validation;
    }
    
    /**
     * 获取数据库统计信息
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
                $count = $this->pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                $stats[$table] = $count;
            } catch (Exception $e) {
                $stats[$table] = 0;
            }
        }
        
        return $stats;
    }
}

// 如果直接访问，执行迁移
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    header('Content-Type: application/json');
    
    try {
        $migrator = new AdminDatabaseMigrator();
        
        echo "开始执行数据库迁移...\n\n";
        
        $results = $migrator->runMigrations();
        
        echo "迁移结果：\n";
        foreach ($results as $migration => $result) {
            echo "- {$migration}: {$result['status']} - {$result['message']}\n";
        }
        
        echo "\n验证数据库结构：\n";
        $validation = $migrator->validateDatabase();
        foreach ($validation as $table => $exists) {
            echo "- {$table}: " . ($exists ? '✅' : '❌') . "\n";
        }
        
        echo "\n数据库统计：\n";
        $stats = $migrator->getDatabaseStats();
        foreach ($stats as $table => $count) {
            echo "- {$table}: {$count} 条记录\n";
        }
        
        echo "\n迁移完成！\n";
        
    } catch (Exception $e) {
        echo "迁移失败：" . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
}
