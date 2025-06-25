<?php
/**
 * AlingAi Pro 5.0 - 简化版数据库迁移执行器
 * 独立执行，不依赖外部依赖
 */

class SimpleAdminDatabaseMigrator
{
    private $dbPath;
    private $pdo;
    
    public function __construct() {
        $this->dbPath = __DIR__ . '/../../../database/admin_system.db';
        $this->initializeDatabase(];
    }
    
    /**
     * 初始化数据库连接
     */
    private function initializeDatabase(): void {
        try {
            // 确保数据库目录存�?            $dbDir = dirname($this->dbPath];
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true];
                echo "�?Created database directory: {$dbDir}\n";
            }
            
            $this->pdo = new PDO("sqlite:{$this->dbPath}"];
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            $this->pdo->exec('PRAGMA foreign_keys = ON'];
            
            echo "�?Database connection established\n";
        } catch (Exception $e) {
            echo "�?Database connection failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * 执行所有迁�?     */
    public function runMigrations(): array
    {
        echo "🚀 Starting Admin Database Migrations...\n\n";
        
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
        
        // 创建迁移记录�?        $this->createMigrationsTable(];
        
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
     * 创建迁移记录�?     */
    private function createMigrationsTable(): void {
        $sql = "
        CREATE TABLE IF NOT EXISTS admin_migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) UNIQUE NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
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
    private function recordMigration(string $migrationName): void {
        $stmt = $this->pdo->prepare("INSERT INTO admin_migrations (migration) VALUES (?)"];
        $stmt->execute([$migrationName]];
    }
    
    /**
     * 迁移001：创建管理员用户�?     */
    private function createAdminUsersTable(): void {
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
            last_login_ip VARCHAR(45],
            login_attempts INTEGER DEFAULT 0,
            locked_until DATETIME,
            profile_data TEXT,
            settings TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_users_username ON admin_users(username)"];
        $this->pdo->exec("CREATE INDEX idx_admin_users_email ON admin_users(email)"];
        $this->pdo->exec("CREATE INDEX idx_admin_users_role ON admin_users(role)"];
    }
    
    /**
     * 迁移002：创建管理员Token�?     */
    private function createAdminTokensTable(): void {
        $sql = "
        CREATE TABLE admin_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token_id VARCHAR(255) UNIQUE NOT NULL,
            token_type VARCHAR(20) NOT NULL CHECK(token_type IN ('access', 'refresh')],
            token_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            is_revoked BOOLEAN DEFAULT FALSE,
            device_info TEXT,
            ip_address VARCHAR(45],
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_used_at DATETIME
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_tokens_user_id ON admin_tokens(user_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_tokens_token_id ON admin_tokens(token_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_tokens_expires_at ON admin_tokens(expires_at)"];
    }
    
    /**
     * 迁移003：创建权限表
     */
    private function createAdminPermissionsTable(): void {
        $sql = "
        CREATE TABLE admin_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            permission_name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            category VARCHAR(50],
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_permissions_name ON admin_permissions(permission_name)"];
        $this->pdo->exec("CREATE INDEX idx_admin_permissions_category ON admin_permissions(category)"];
    }
    
    /**
     * 迁移004：创建用户权限关联表
     */
    private function createAdminUserPermissionsTable(): void {
        $sql = "
        CREATE TABLE admin_user_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            permission_id INTEGER NOT NULL,
                granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            granted_by INTEGER,
                FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE,
                FOREIGN KEY (granted_by) REFERENCES admin_users(id) ON DELETE SET NULL
            )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_user_permissions_user_id ON admin_user_permissions(user_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_user_permissions_permission_id ON admin_user_permissions(permission_id)"];
    }
    
    /**
     * 迁移005：创建API密钥�?     */
    private function createAdminApiKeysTable(): void {
        $sql = "
        CREATE TABLE admin_api_keys (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                api_key VARCHAR(64) UNIQUE NOT NULL,
                api_secret VARCHAR(128) NOT NULL,
                name VARCHAR(100],
            description TEXT,
            permissions TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            last_used_at DATETIME,
            expires_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_api_keys_user_id ON admin_api_keys(user_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_api_keys_api_key ON admin_api_keys(api_key)"];
    }
    
    /**
     * 迁移006：创建第三方服务�?     */
    private function createAdminThirdPartyServicesTable(): void {
        $sql = "
        CREATE TABLE admin_third_party_services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
                service_name VARCHAR(100) UNIQUE NOT NULL,
                service_type VARCHAR(50) NOT NULL,
                api_key TEXT,
                api_secret TEXT,
                config_data TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                last_check_at DATETIME,
                status VARCHAR(20) DEFAULT 'unknown',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_third_party_services_name ON admin_third_party_services(service_name)"];
        $this->pdo->exec("CREATE INDEX idx_admin_third_party_services_type ON admin_third_party_services(service_type)"];
    }
    
    /**
     * 迁移007：创建系统日志表
     */
    private function createAdminSystemLogsTable(): void {
        $sql = "
        CREATE TABLE admin_system_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
                action VARCHAR(100) NOT NULL,
                entity_type VARCHAR(50],
                entity_id INTEGER,
                details TEXT,
            ip_address VARCHAR(45],
            user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_system_logs_user_id ON admin_system_logs(user_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_system_logs_action ON admin_system_logs(action)"];
        $this->pdo->exec("CREATE INDEX idx_admin_system_logs_created_at ON admin_system_logs(created_at)"];
    }
    
    /**
     * 迁移008：创建监控指标表
     */
    private function createAdminMonitoringMetricsTable(): void {
        $sql = "
        CREATE TABLE admin_monitoring_metrics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            metric_name VARCHAR(100) NOT NULL,
                metric_value FLOAT NOT NULL,
                metric_type VARCHAR(50) NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_monitoring_metrics_name ON admin_monitoring_metrics(metric_name)"];
        $this->pdo->exec("CREATE INDEX idx_admin_monitoring_metrics_timestamp ON admin_monitoring_metrics(timestamp)"];
    }
    
    /**
     * 迁移009：创建风控规则表
     */
    private function createAdminRiskControlRulesTable(): void {
        $sql = "
        CREATE TABLE admin_risk_control_rules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
                rule_name VARCHAR(100) NOT NULL,
            rule_type VARCHAR(50) NOT NULL,
                rule_condition TEXT NOT NULL,
                rule_action TEXT NOT NULL,
                priority INTEGER DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_rules_name ON admin_risk_control_rules(rule_name)"];
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_rules_type ON admin_risk_control_rules(rule_type)"];
    }
    
    /**
     * 迁移010：创建风控事件表
     */
    private function createAdminRiskControlEventsTable(): void {
        $sql = "
        CREATE TABLE admin_risk_control_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
                rule_id INTEGER,
            event_type VARCHAR(50) NOT NULL,
                event_data TEXT NOT NULL,
            risk_level VARCHAR(20) NOT NULL,
                status VARCHAR(20) DEFAULT 'pending',
                handled_by INTEGER,
                handled_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (rule_id) REFERENCES admin_risk_control_rules(id) ON DELETE SET NULL,
                FOREIGN KEY (handled_by) REFERENCES admin_users(id) ON DELETE SET NULL
            )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_events_rule_id ON admin_risk_control_events(rule_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_events_type ON admin_risk_control_events(event_type)"];
        $this->pdo->exec("CREATE INDEX idx_admin_risk_control_events_created_at ON admin_risk_control_events(created_at)"];
    }
    
    /**
     * 迁移011：创建邮件模板表
     */
    private function createAdminEmailTemplatesTable(): void {
        $sql = "
        CREATE TABLE admin_email_templates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
                template_name VARCHAR(100) UNIQUE NOT NULL,
            subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
            variables TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_email_templates_name ON admin_email_templates(template_name)"];
    }
    
    /**
     * 迁移012：创建邮件队列表
     */
    private function createAdminEmailQueueTable(): void {
        $sql = "
        CREATE TABLE admin_email_queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
                template_id INTEGER,
                recipient_email VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                variables TEXT,
            status VARCHAR(20) DEFAULT 'pending',
            attempts INTEGER DEFAULT 0,
            last_attempt_at DATETIME,
            error_message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                scheduled_at DATETIME,
            sent_at DATETIME,
                FOREIGN KEY (template_id) REFERENCES admin_email_templates(id) ON DELETE SET NULL
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_email_queue_template_id ON admin_email_queue(template_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_email_queue_status ON admin_email_queue(status)"];
        $this->pdo->exec("CREATE INDEX idx_admin_email_queue_created_at ON admin_email_queue(created_at)"];
    }
    
    /**
     * 迁移013：创建聊天监控表
     */
    private function createAdminChatMonitoringTable(): void {
        $sql = "
        CREATE TABLE admin_chat_monitoring (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
                chat_id VARCHAR(100) NOT NULL,
            user_id INTEGER,
                message TEXT NOT NULL,
                message_type VARCHAR(20) NOT NULL,
                risk_level VARCHAR(20],
                is_flagged BOOLEAN DEFAULT FALSE,
                flagged_reason TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
            )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_chat_monitoring_chat_id ON admin_chat_monitoring(chat_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_chat_monitoring_user_id ON admin_chat_monitoring(user_id)"];
        $this->pdo->exec("CREATE INDEX idx_admin_chat_monitoring_created_at ON admin_chat_monitoring(created_at)"];
    }
    
    /**
     * 迁移014：创建敏感词�?     */
    private function createAdminSensitiveWordsTable(): void {
        $sql = "
        CREATE TABLE admin_sensitive_words (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
                word VARCHAR(100) UNIQUE NOT NULL,
            category VARCHAR(50],
                risk_level VARCHAR(20) DEFAULT 'medium',
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql];
        
        // 创建索引
        $this->pdo->exec("CREATE INDEX idx_admin_sensitive_words_word ON admin_sensitive_words(word)"];
        $this->pdo->exec("CREATE INDEX idx_admin_sensitive_words_category ON admin_sensitive_words(category)"];
    }
    
    /**
     * 迁移015：插入默认数�?     */
    private function insertDefaultData(): void {
        // 插入默认管理员用�?        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT];
        $salt = bin2hex(random_bytes(16)];
        
        $sql = "INSERT INTO admin_users (username, email, password_hash, salt, role, is_admin) 
                VALUES ('admin', 'admin@example.com', ?, ?, 'super_admin', 1)";
        
        $stmt = $this->pdo->prepare($sql];
        $stmt->execute([$defaultPassword, $salt]];
        
        // 插入默认权限
        $permissions = [
            ['user_management', '用户管理权限', 'system'], 
            ['role_management', '角色管理权限', 'system'], 
            ['permission_management', '权限管理权限', 'system'], 
            ['system_settings', '系统设置权限', 'system'], 
            ['api_management', 'API管理权限', 'system'], 
            ['log_view', '日志查看权限', 'system'], 
            ['monitoring', '系统监控权限', 'system'], 
            ['risk_control', '风控管理权限', 'system']
        ];
        
        $stmt = $this->pdo->prepare("INSERT INTO admin_permissions (permission_name, description, category) VALUES (?, ?, ?)"];
        
        foreach ($permissions as $permission) {
            $stmt->execute($permission];
        }
        
        // 为超级管理员分配所有权�?        $adminId = $this->pdo->lastInsertId(];
        $permissionIds = $this->pdo->query("SELECT id FROM admin_permissions")->fetchAll(PDO::FETCH_COLUMN];
        
        $stmt = $this->pdo->prepare("INSERT INTO admin_user_permissions (user_id, permission_id, granted_by) VALUES (?, ?, ?)"];
        
        foreach ($permissionIds as $permissionId) {
                $stmt->execute([$adminId, $permissionId, $adminId]];
        }
        
        // 插入默认邮件模板
        $templates = [
            [
                'welcome_email',
                '欢迎使用 AlingAi Pro 管理系统',
                '亲爱�?{username}，\n\n欢迎使用 AlingAi Pro 管理系统。您的账号已成功创建。\n\n请使用以下凭据登录：\n用户名：{username}\n密码：{password}\n\n请及时修改您的密码。\n\n祝您使用愉快�?,
                '["username", "password"]'
            ], 
            [
                'password_reset',
                '重置密码 - AlingAi Pro 管理系统',
                '亲爱�?{username}，\n\n您请求重置密码。请使用以下临时密码登录：\n\n{temp_password}\n\n请在登录后立即修改密码。\n\n如果这不是您发起的请求，请忽略此邮件�?,
                '["username", "temp_password"]'
            ]
        ];
        
        $stmt = $this->pdo->prepare("INSERT INTO admin_email_templates (template_name, subject, body, variables) VALUES (?, ?, ?, ?)"];
        
        foreach ($templates as $template) {
            $stmt->execute($template];
        }
        
        // 插入默认风控规则
        $rules = [
            [
                'login_attempts',
                'login',
                '{"max_attempts": 5, "time_window": 300}',
                '{"action": "lock_account", "duration": 1800}',
                1
            ], 
            [
                'sensitive_words',
                'content',
                '{"words": ["敏感�?", "敏感�?"]}',
                '{"action": "flag", "notify": true}',
                2
            ]
        ];
        
        $stmt = $this->pdo->prepare("INSERT INTO admin_risk_control_rules (rule_name, rule_type, rule_condition, rule_action, priority) VALUES (?, ?, ?, ?, ?)"];
        
        foreach ($rules as $rule) {
            $stmt->execute($rule];
        }
    }
    
    /**
     * 验证数据库结�?     */
    public function validateDatabase(): array
    {
        $results = [];
        $tables = [
            'admin_users',
            'admin_tokens',
            'admin_permissions',
            'admin_user_permissions',
            'admin_api_keys',
            'admin_third_party_services',
            'admin_system_logs',
            'admin_monitoring_metrics',
            'admin_risk_control_rules',
            'admin_risk_control_events',
            'admin_email_templates',
            'admin_email_queue',
            'admin_chat_monitoring',
            'admin_sensitive_words'
        ];
        
        foreach ($tables as $table) {
            try {
                $this->pdo->query("SELECT 1 FROM {$table} LIMIT 1"];
                $results[$table] = ['status' => 'success', 'message' => 'Table exists and is accessible'];
            } catch (Exception $e) {
                $results[$table] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
        
        return $results;
    }
    
    /**
     * 获取数据库统计信�?     */
    public function getDatabaseStats(): array
    {
        $stats = [];
        
        foreach ($this->pdo->query("SELECT name FROM sqlite_master WHERE type='table'") as $table) {
            $tableName = $table['name'];
            $count = $this->pdo->query("SELECT COUNT(*) FROM {$tableName}")->fetchColumn(];
            $stats[$tableName] = $count;
        }
        
        return $stats;
    }
}

// 创建并运行迁移器
$migrator = new SimpleAdminDatabaseMigrator(];
$results = $migrator->runMigrations(];

// 输出迁移结果
    echo "\n📊 Migration Results:\n";
    foreach ($results as $migration => $result) {
    $status = $result['status'];
    $message = $result['message'];
    echo "{$migration}: {$status} - {$message}\n";
}

// 验证数据�?echo "\n🔍 Validating Database Structure:\n";
$validation = $migrator->validateDatabase(];
foreach ($validation as $table => $result) {
    $status = $result['status'];
    $message = $result['message'];
    echo "{$table}: {$status} - {$message}\n";
}

// 获取数据库统计信�?    echo "\n📈 Database Statistics:\n";
$stats = $migrator->getDatabaseStats(];
    foreach ($stats as $table => $count) {
    echo "{$table}: {$count} records\n";
}
