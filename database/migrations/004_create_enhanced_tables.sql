-- 增强服务数据库表结构
-- 创建日期: 2025年6月3日
-- 版本: 2.0.0

-- 系统监控指标表
CREATE TABLE IF NOT EXISTS system_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_type ENUM('cpu', 'memory', 'disk', 'network', 'database') NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,2) NOT NULL,
    metric_unit VARCHAR(20) DEFAULT '%',
    threshold_warning DECIMAL(10,2) DEFAULT NULL,
    threshold_critical DECIMAL(10,2) DEFAULT NULL,
    status ENUM('normal', 'warning', 'critical') DEFAULT 'normal',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_type_time (metric_type, recorded_at),
    INDEX idx_status_time (status, recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 数据库健康监控表
CREATE TABLE IF NOT EXISTS database_health (
    id INT AUTO_INCREMENT PRIMARY KEY,
    connection_type ENUM('mysql', 'mongodb', 'redis') NOT NULL,
    host VARCHAR(255) NOT NULL,
    port INT NOT NULL,
    database_name VARCHAR(100) DEFAULT NULL,
    connection_status ENUM('connected', 'disconnected', 'error') NOT NULL,
    response_time DECIMAL(8,3) DEFAULT NULL COMMENT '响应时间(ms)',
    error_message TEXT DEFAULT NULL,
    last_check TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_status (connection_type, connection_status),
    INDEX idx_last_check (last_check)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI对话记录表
CREATE TABLE IF NOT EXISTS ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    user_id INT DEFAULT NULL,
    provider ENUM('deepseek', 'baidu', 'openai') NOT NULL,
    model VARCHAR(100) DEFAULT NULL,
    prompt TEXT NOT NULL,
    response TEXT DEFAULT NULL,
    tokens_used INT DEFAULT 0,
    response_time DECIMAL(8,3) DEFAULT NULL COMMENT '响应时间(ms)',
    status ENUM('pending', 'completed', 'error', 'timeout') DEFAULT 'pending',
    error_message TEXT DEFAULT NULL,
    metadata JSON DEFAULT NULL COMMENT '额外元数据',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_user_provider (user_id, provider),
    INDEX idx_status_created (status, created_at),
    INDEX idx_provider_created (provider, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI使用统计表
CREATE TABLE IF NOT EXISTS ai_usage_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider ENUM('deepseek', 'baidu', 'openai') NOT NULL,
    model VARCHAR(100) DEFAULT NULL,
    user_id INT DEFAULT NULL,
    date DATE NOT NULL,
    request_count INT DEFAULT 0,
    token_count INT DEFAULT 0,
    error_count INT DEFAULT 0,
    total_response_time DECIMAL(12,3) DEFAULT 0 COMMENT '总响应时间(ms)',
    avg_response_time DECIMAL(8,3) DEFAULT 0 COMMENT '平均响应时间(ms)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_provider_user_date (provider, user_id, date),
    INDEX idx_provider_date (provider, date),
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 邮件发送日志表
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    cc_emails JSON DEFAULT NULL,
    bcc_emails JSON DEFAULT NULL,
    subject VARCHAR(500) NOT NULL,
    template VARCHAR(100) DEFAULT NULL,
    email_type ENUM('notification', 'alert', 'welcome', 'reset', 'verification', 'marketing') DEFAULT 'notification',
    status ENUM('queued', 'sent', 'failed', 'bounced') DEFAULT 'queued',
    error_message TEXT DEFAULT NULL,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    opened_at TIMESTAMP NULL DEFAULT NULL,
    clicked_at TIMESTAMP NULL DEFAULT NULL,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_to_email (to_email),
    INDEX idx_status_created (status, created_at),
    INDEX idx_type_created (email_type, created_at),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 邮件模板表
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    subject VARCHAR(500) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT DEFAULT NULL,
    variables JSON DEFAULT NULL COMMENT '模板变量定义',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name_active (name, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 系统告警记录表
CREATE TABLE IF NOT EXISTS system_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('cpu', 'memory', 'disk', 'database', 'service', 'security') NOT NULL,
    severity ENUM('info', 'warning', 'critical') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    details JSON DEFAULT NULL,
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    resolved_by INT DEFAULT NULL,
    notified_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_severity (alert_type, severity),
    INDEX idx_resolved_created (is_resolved, created_at),
    INDEX idx_severity_created (severity, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 配置缓存表
CREATE TABLE IF NOT EXISTS config_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(255) NOT NULL UNIQUE,
    config_value LONGTEXT NOT NULL,
    config_type ENUM('string', 'integer', 'float', 'boolean', 'json', 'array') DEFAULT 'string',
    is_encrypted BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key_expires (config_key, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Redis连接池状态表
CREATE TABLE IF NOT EXISTS redis_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    connection_name VARCHAR(100) NOT NULL,
    host VARCHAR(255) NOT NULL,
    port INT NOT NULL,
    database_num INT DEFAULT 0,
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    last_ping TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    error_count INT DEFAULT 0,
    total_commands INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name_status (connection_name, status),
    INDEX idx_last_ping (last_ping)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入默认邮件模板
INSERT INTO email_templates (name, subject, body_html, body_text, variables) VALUES
('system_alert', '系统告警 - {{alert_type}}', 
'<html><body><h2>AlingAi Pro 系统告警</h2><p><strong>告警类型:</strong> {{alert_type}}</p><p><strong>严重程度:</strong> {{severity}}</p><p><strong>消息:</strong> {{message}}</p><p><strong>时间:</strong> {{created_at}}</p></body></html>',
'AlingAi Pro 系统告警\n\n告警类型: {{alert_type}}\n严重程度: {{severity}}\n消息: {{message}}\n时间: {{created_at}}',
'["alert_type", "severity", "message", "created_at"]'),

('welcome_email', '欢迎使用 AlingAi Pro', 
'<html><body><h2>欢迎使用 AlingAi Pro!</h2><p>亲爱的 {{username}},</p><p>感谢您注册 AlingAi Pro。您的账户已成功创建。</p><p>开始您的AI智能对话之旅吧！</p></body></html>',
'欢迎使用 AlingAi Pro!\n\n亲爱的 {{username}},\n\n感谢您注册 AlingAi Pro。您的账户已成功创建。\n\n开始您的AI智能对话之旅吧！',
'["username"]'),

('password_reset', '密码重置 - AlingAi Pro', 
'<html><body><h2>密码重置请求</h2><p>您请求重置 AlingAi Pro 账户的密码。</p><p><a href="{{reset_link}}">点击这里重置密码</a></p><p>链接将在 {{expires_in}} 分钟后失效。</p></body></html>',
'密码重置请求\n\n您请求重置 AlingAi Pro 账户的密码。\n\n重置链接: {{reset_link}}\n\n链接将在 {{expires_in}} 分钟后失效。',
'["reset_link", "expires_in"]');

-- 插入默认配置
INSERT INTO config_cache (config_key, config_value, config_type) VALUES
('monitoring.enabled', 'true', 'boolean'),
('monitoring.check_interval', '60', 'integer'),
('email.daily_limit', '1000', 'integer'),
('ai.default_provider', 'deepseek', 'string'),
('ai.max_tokens_per_request', '2048', 'integer'),
('cache.default_ttl', '3600', 'integer');
