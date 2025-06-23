<?php
/**
 * AlingAi Pro 5.0 - Admin系统数据库迁移
 * 创建完整的Admin管理系统数据库结构
 */

class AdminSystemMigration
{
    private $pdo;
    
    public function __construct($pdo = null)
    {
        if ($pdo === null) {
            // 使用文件数据库或MySQL
            $this->pdo = $this->createDatabaseConnection();
        } else {
            $this->pdo = $pdo;
        }
    }
      private function createDatabaseConnection()
    {
        // 优先使用SQLite，确保兼容性
        $dbPath = __DIR__ . '/admin_system.db';
        
        try {
            echo "📂 使用SQLite数据库: $dbPath\n";
            return new PDO("sqlite:$dbPath", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            echo "❌ 数据库连接失败: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    public function runMigration()
    {
        echo "🚀 开始Admin系统数据库迁移...\n\n";
        
        try {
            $this->createUserTables();
            $this->createApiTables();
            $this->createThirdPartyTables();
            $this->createRiskControlTables();
            $this->createChatTables();
            $this->createTokenTables();
            $this->createEmailTables();
            $this->insertDefaultData();
            
            echo "✅ Admin系统数据库迁移完成！\n";
            return true;
        } catch (Exception $e) {
            echo "❌ 迁移失败: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function createUserTables()
    {
        echo "📊 创建用户管理表...\n";
          // 用户角色表
        $this->pdo->exec("
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
          // 用户基础信息表
        $this->pdo->exec("
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
          // 用户余额变动记录表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_balance_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                balance_before DECIMAL(10,2) NOT NULL,
                balance_after DECIMAL(10,2) NOT NULL,
                type VARCHAR(20) NOT NULL,
                source VARCHAR(20) NOT NULL,
                description TEXT,
                reference_id VARCHAR(100),
                operator_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id)
            )
        ");
        
        // 为用户余额变动记录表创建索引
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_time ON user_balance_logs (user_id, created_at)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_type ON user_balance_logs (type)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_reference ON user_balance_logs (reference_id)");
          // Token使用记录表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_token_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                tokens_used INTEGER NOT NULL,
                tokens_before INTEGER NOT NULL,
                tokens_after INTEGER NOT NULL,
                service_type VARCHAR(50) NOT NULL,
                model_name VARCHAR(50),
                prompt_tokens INTEGER DEFAULT 0,
                completion_tokens INTEGER DEFAULT 0,
                cost_amount DECIMAL(8,4) DEFAULT 0,
                session_id VARCHAR(100),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id)
            )
        ");
        
        // 为Token使用记录表创建索引
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_time_tokens ON user_token_logs (user_id, created_at)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_service ON user_token_logs (service_type)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session_tokens ON user_token_logs (session_id)");
        
        echo "   ✅ 用户管理表创建完成\n";
    }
    
    private function createApiTables()
    {
        echo "📡 创建API监管表...\n";
          // API接口注册表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS api_endpoints (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                path VARCHAR(255) NOT NULL,
                method VARCHAR(10) NOT NULL,
                description TEXT,
                category VARCHAR(50) DEFAULT 'general',
                rate_limit INTEGER DEFAULT 100,
                rate_window INTEGER DEFAULT 60,
                requires_auth BOOLEAN DEFAULT 1,
                required_role VARCHAR(50),
                status VARCHAR(20) DEFAULT 'active',
                version VARCHAR(10) DEFAULT 'v1',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // 创建唯一索引
        $this->pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS unique_endpoint ON api_endpoints (path, method)");
          // API调用日志表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS api_call_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                endpoint_id INTEGER,
                user_id INTEGER,
                ip_address VARCHAR(45),
                user_agent TEXT,
                request_method VARCHAR(10),
                request_path VARCHAR(255),
                request_params TEXT,
                request_body TEXT,
                response_code INTEGER,
                response_size INTEGER,
                response_time FLOAT,
                memory_usage INTEGER,
                error_message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (endpoint_id) REFERENCES api_endpoints(id),
                FOREIGN KEY (user_id) REFERENCES admin_users(id)
            )
        ");
        
        // 创建API调用日志表的索引
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_endpoint_time ON api_call_logs (endpoint_id, created_at)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_time_api ON api_call_logs (user_id, created_at)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_ip_time ON api_call_logs (ip_address, created_at)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_response_code ON api_call_logs (response_code)");
        
        // API限流记录表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS api_rate_limits (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                identifier VARCHAR(100) NOT NULL,
                endpoint_id INT,
                request_count INT DEFAULT 1,
                window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_rate_limit (identifier, endpoint_id),
                INDEX idx_expires (expires_at)
            )
        ");
        
        echo "   ✅ API监管表创建完成\n";
    }
    
    private function createThirdPartyTables()
    {
        echo "🔌 创建第三方服务表...\n";
        
        // 第三方服务配置表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS third_party_services (
                id INT PRIMARY KEY AUTO_INCREMENT,
                service_type ENUM('payment', 'oauth', 'sms', 'email', 'ai') NOT NULL,
                service_name VARCHAR(50) NOT NULL,
                provider VARCHAR(50) NOT NULL,
                config JSON NOT NULL,
                credentials JSON,
                webhook_url VARCHAR(255),
                status ENUM('active', 'disabled', 'testing') DEFAULT 'active',
                last_health_check TIMESTAMP NULL,
                health_status ENUM('healthy', 'warning', 'error') DEFAULT 'healthy',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_service (service_type, service_name)
            )
        ");
        
        // 第三方服务调用记录表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS third_party_logs (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                service_id INT NOT NULL,
                user_id BIGINT,
                action VARCHAR(50) NOT NULL,
                request_id VARCHAR(100),
                request_data JSON,
                response_data JSON,
                response_time FLOAT,
                status ENUM('success', 'failed', 'pending') NOT NULL,
                error_code VARCHAR(50),
                error_message TEXT,
                cost_amount DECIMAL(8,4) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (service_id) REFERENCES third_party_services(id),
                FOREIGN KEY (user_id) REFERENCES admin_users(id),
                INDEX idx_service_time (service_id, created_at),
                INDEX idx_user_time (user_id, created_at),
                INDEX idx_status (status),
                INDEX idx_request_id (request_id)
            )
        ");
        
        // OAuth授权记录表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS oauth_authorizations (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                user_id BIGINT NOT NULL,
                service_id INT NOT NULL,
                provider_user_id VARCHAR(100) NOT NULL,
                access_token TEXT,
                refresh_token TEXT,
                token_expires_at TIMESTAMP,
                scope TEXT,
                status ENUM('active', 'revoked', 'expired') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id),
                FOREIGN KEY (service_id) REFERENCES third_party_services(id),
                UNIQUE KEY unique_oauth (user_id, service_id)
            )
        ");
        
        echo "   ✅ 第三方服务表创建完成\n";
    }
    
    private function createRiskControlTables()
    {
        echo "🛡️ 创建风控系统表...\n";
        
        // 风控规则表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS risk_rules (
                id INT PRIMARY KEY AUTO_INCREMENT,
                rule_name VARCHAR(100) NOT NULL,
                rule_type ENUM('frequency', 'amount', 'behavior', 'ip', 'device', 'geo') NOT NULL,
                conditions JSON NOT NULL,
                threshold_value DECIMAL(10,2),
                time_window INT,
                action ENUM('warn', 'block', 'suspend', 'review') NOT NULL,
                priority INT DEFAULT 5,
                status ENUM('active', 'disabled', 'testing') DEFAULT 'active',
                description TEXT,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES admin_users(id)
            )
        ");
        
        // 风控事件表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS risk_events (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                user_id BIGINT NOT NULL,
                rule_id INT NOT NULL,
                event_type VARCHAR(50) NOT NULL,
                risk_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
                risk_score INT DEFAULT 0,
                description TEXT,
                trigger_data JSON,
                action_taken VARCHAR(50),
                status ENUM('pending', 'processed', 'ignored', 'false_positive') DEFAULT 'pending',
                processed_by BIGINT,
                processed_at TIMESTAMP NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id),
                FOREIGN KEY (rule_id) REFERENCES risk_rules(id),
                FOREIGN KEY (processed_by) REFERENCES admin_users(id),
                INDEX idx_user_time (user_id, created_at),
                INDEX idx_status (status),
                INDEX idx_risk_level (risk_level)
            )
        ");
        
        // 黑名单表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS blacklists (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                list_type ENUM('ip', 'email', 'phone', 'device', 'keyword') NOT NULL,
                value VARCHAR(255) NOT NULL,
                reason TEXT,
                source ENUM('manual', 'auto', 'import') DEFAULT 'manual',
                expires_at TIMESTAMP NULL,
                status ENUM('active', 'expired', 'removed') DEFAULT 'active',
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES admin_users(id),
                UNIQUE KEY unique_blacklist (list_type, value),
                INDEX idx_type_value (list_type, value),
                INDEX idx_expires (expires_at)
            )
        ");
        
        echo "   ✅ 风控系统表创建完成\n";
    }
    
    private function createChatTables()
    {
        echo "💬 创建聊天监管表...\n";
        
        // 聊天会话表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS chat_sessions (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                user_id BIGINT NOT NULL,
                session_id VARCHAR(100) NOT NULL UNIQUE,
                title VARCHAR(255),
                ai_model VARCHAR(50),
                total_messages INT DEFAULT 0,
                total_tokens INT DEFAULT 0,
                total_cost DECIMAL(8,4) DEFAULT 0,
                status ENUM('active', 'archived', 'deleted') DEFAULT 'active',
                risk_flags JSON,
                last_message_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id),
                INDEX idx_user_time (user_id, created_at),
                INDEX idx_session (session_id),
                INDEX idx_status (status)
            )
        ");
        
        // 聊天消息表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS chat_messages (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                session_id BIGINT NOT NULL,
                user_id BIGINT NOT NULL,
                message_id VARCHAR(100) NOT NULL,
                role ENUM('user', 'assistant', 'system') NOT NULL,
                content TEXT NOT NULL,
                content_hash VARCHAR(64),
                tokens_used INT DEFAULT 0,
                model_name VARCHAR(50),
                cost_amount DECIMAL(8,4) DEFAULT 0,
                is_flagged BOOLEAN DEFAULT FALSE,
                flag_reasons JSON,
                sentiment_score DECIMAL(3,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES chat_sessions(id),
                FOREIGN KEY (user_id) REFERENCES admin_users(id),
                INDEX idx_session_time (session_id, created_at),
                INDEX idx_user_time (user_id, created_at),
                INDEX idx_flagged (is_flagged),
                INDEX idx_content_hash (content_hash)
            )
        ");
        
        // 敏感词表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS sensitive_words (
                id INT PRIMARY KEY AUTO_INCREMENT,
                word VARCHAR(100) NOT NULL,
                category VARCHAR(50) NOT NULL,
                severity ENUM('low', 'medium', 'high') DEFAULT 'medium',
                action ENUM('flag', 'block', 'replace') DEFAULT 'flag',
                replacement VARCHAR(100),
                status ENUM('active', 'disabled') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_word (word),
                INDEX idx_category (category)
            )
        ");
        
        echo "   ✅ 聊天监管表创建完成\n";
    }
    
    private function createTokenTables()
    {
        echo "🎟️ 创建Token管理表...\n";
        
        // JWT Token表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS jwt_tokens (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                user_id BIGINT NOT NULL,
                token_id VARCHAR(100) NOT NULL UNIQUE,
                token_hash VARCHAR(255) NOT NULL,
                token_type ENUM('access', 'refresh', 'api') DEFAULT 'access',
                scope TEXT,
                expires_at TIMESTAMP NOT NULL,
                last_used_at TIMESTAMP NULL,
                usage_count INT DEFAULT 0,
                ip_address VARCHAR(45),
                user_agent TEXT,
                status ENUM('active', 'revoked', 'expired') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id),
                INDEX idx_user_type (user_id, token_type),
                INDEX idx_expires (expires_at),
                INDEX idx_status (status)
            )
        ");
        
        // API Key表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS api_keys (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                user_id BIGINT NOT NULL,
                key_name VARCHAR(100) NOT NULL,
                api_key VARCHAR(100) NOT NULL UNIQUE,
                key_hash VARCHAR(255) NOT NULL,
                permissions JSON,
                rate_limit INT DEFAULT 1000,
                expires_at TIMESTAMP NULL,
                last_used_at TIMESTAMP NULL,
                usage_count BIGINT DEFAULT 0,
                status ENUM('active', 'disabled', 'expired') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id),
                INDEX idx_user (user_id),
                INDEX idx_key (api_key),
                INDEX idx_status (status)
            )
        ");
        
        echo "   ✅ Token管理表创建完成\n";
    }
    
    private function createEmailTables()
    {
        echo "📧 创建邮箱系统表...\n";
        
        // 邮件配置表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS email_configs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                config_name VARCHAR(50) NOT NULL,
                smtp_host VARCHAR(100) NOT NULL,
                smtp_port INT NOT NULL,
                smtp_user VARCHAR(100) NOT NULL,
                smtp_password VARCHAR(255) NOT NULL,
                encryption ENUM('none', 'ssl', 'tls') DEFAULT 'tls',
                from_email VARCHAR(100) NOT NULL,
                from_name VARCHAR(100) NOT NULL,
                daily_limit INT DEFAULT 1000,
                is_default BOOLEAN DEFAULT FALSE,
                status ENUM('active', 'disabled') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // 邮件模板表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS email_templates (
                id INT PRIMARY KEY AUTO_INCREMENT,
                template_name VARCHAR(100) NOT NULL UNIQUE,
                template_type VARCHAR(50) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                variables JSON,
                status ENUM('active', 'disabled') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // 邮件发送记录表
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS email_logs (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                config_id INT NOT NULL,
                template_id INT,
                to_email VARCHAR(100) NOT NULL,
                to_name VARCHAR(100),
                subject VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
                error_message TEXT,
                sent_at TIMESTAMP NULL,
                opened_at TIMESTAMP NULL,
                clicked_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (config_id) REFERENCES email_configs(id),
                FOREIGN KEY (template_id) REFERENCES email_templates(id),
                INDEX idx_to_email (to_email),
                INDEX idx_status (status),
                INDEX idx_sent_time (sent_at)
            )
        ");
        
        echo "   ✅ 邮箱系统表创建完成\n";
    }
    
    private function insertDefaultData()
    {
        echo "📝 插入默认数据...\n";
        
        // 插入默认角色
        $this->pdo->exec("
            INSERT IGNORE INTO admin_roles (id, name, display_name, permissions) VALUES
            (1, 'super_admin', '超级管理员', '[\"*\"]'),
            (2, 'admin', '管理员', '[\"users.*\", \"api.*\", \"third_party.*\"]'),
            (3, 'operator', '运营', '[\"users.view\", \"users.edit\", \"chat.*\"]'),
            (4, 'user', '普通用户', '[\"chat.*\", \"profile.*\"]')
        ");
        
        // 插入默认超级管理员
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $this->pdo->exec("
            INSERT IGNORE INTO admin_users (id, username, email, password_hash, role_id, balance, total_tokens) VALUES
            (1, 'admin', 'admin@alingai.com', '$passwordHash', 1, 10000.00, 1000000)
        ");
        
        // 插入默认API端点
        $this->pdo->exec("
            INSERT IGNORE INTO api_endpoints (name, path, method, description, category) VALUES
            ('用户列表', '/api/v1/users', 'GET', '获取用户列表', 'user'),
            ('创建用户', '/api/v1/users', 'POST', '创建新用户', 'user'),
            ('用户详情', '/api/v1/users/{id}', 'GET', '获取用户详情', 'user'),
            ('更新用户', '/api/v1/users/{id}', 'PUT', '更新用户信息', 'user'),
            ('删除用户', '/api/v1/users/{id}', 'DELETE', '删除用户', 'user'),
            ('聊天会话', '/api/v1/chat', 'POST', '发起聊天会话', 'chat'),
            ('聊天历史', '/api/v1/chat/history', 'GET', '获取聊天历史', 'chat'),
            ('系统状态', '/api/v1/system/status', 'GET', '获取系统状态', 'system')
        ");
        
        // 插入默认敏感词
        $this->pdo->exec("
            INSERT IGNORE INTO sensitive_words (word, category, severity, action) VALUES
            ('政治敏感', 'politics', 'high', 'block'),
            ('色情内容', 'adult', 'high', 'block'),
            ('暴力内容', 'violence', 'medium', 'flag'),
            ('广告推广', 'spam', 'low', 'flag')
        ");
        
        // 插入默认邮件模板
        $this->pdo->exec("
            INSERT IGNORE INTO email_templates (template_name, template_type, subject, content, variables) VALUES
            ('welcome', 'user', '欢迎加入AlingAi Pro', '欢迎您加入AlingAi Pro！您的账户已创建成功。', '{\"username\": \"用户名\"}'),
            ('password_reset', 'user', '密码重置', '您的密码重置链接：{{reset_link}}', '{\"reset_link\": \"重置链接\"}'),
            ('balance_change', 'user', '余额变动通知', '您的账户余额发生变动，当前余额：{{balance}}', '{\"balance\": \"余额\", \"amount\": \"变动金额\"}')
        ");
        
        echo "   ✅ 默认数据插入完成\n";
    }
}

// 命令行执行
if (php_sapi_name() === 'cli') {
    $migration = new AdminSystemMigration();
    $result = $migration->runMigration();
    exit($result ? 0 : 1);
}

return AdminSystemMigration::class;
