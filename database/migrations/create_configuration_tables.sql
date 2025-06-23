-- 系统配置管理表结构
-- 创建时间: 2024-12-19

-- 1. 系统配置主表
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(255) NOT NULL COMMENT '配置键',
    `setting_value` longtext COMMENT '配置值',
    `setting_type` enum('string','integer','boolean','json','float','datetime','url','email','password') DEFAULT 'string' COMMENT '配置类型',
    `category` varchar(100) DEFAULT 'system' COMMENT '配置分类',
    `description` text COMMENT '配置描述',
    `is_sensitive` tinyint(1) DEFAULT '0' COMMENT '是否敏感配置',
    `is_required` tinyint(1) DEFAULT '0' COMMENT '是否必需配置',
    `validation_rule` varchar(500) DEFAULT NULL COMMENT '验证规则',
    `default_value` text COMMENT '默认值',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_setting_key` (`setting_key`),
    KEY `idx_category` (`category`),
    KEY `idx_setting_type` (`setting_type`),
    KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- 2. 配置变更历史表
CREATE TABLE IF NOT EXISTS `system_settings_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(255) NOT NULL COMMENT '配置键',
    `old_value` longtext COMMENT '旧值',
    `new_value` longtext COMMENT '新值',
    `setting_type` varchar(50) DEFAULT 'string' COMMENT '配置类型',
    `category` varchar(100) DEFAULT 'system' COMMENT '配置分类',
    `version` varchar(100) NOT NULL COMMENT '版本标识',
    `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '变更时间',
    `changed_by` varchar(100) DEFAULT 'system' COMMENT '变更人',
    `change_reason` varchar(500) DEFAULT NULL COMMENT '变更原因',
    `client_ip` varchar(45) DEFAULT NULL COMMENT '客户端IP',
    `user_agent` varchar(500) DEFAULT NULL COMMENT '用户代理',
    PRIMARY KEY (`id`),
    KEY `idx_setting_key` (`setting_key`),
    KEY `idx_changed_at` (`changed_at`),
    KEY `idx_changed_by` (`changed_by`),
    KEY `idx_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配置变更历史表';

-- 3. 配置分组表（可选）
CREATE TABLE IF NOT EXISTS `system_setting_groups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `group_key` varchar(100) NOT NULL COMMENT '分组键',
    `group_name` varchar(200) NOT NULL COMMENT '分组名称',
    `description` text COMMENT '分组描述',
    `icon` varchar(100) DEFAULT NULL COMMENT '图标',
    `sort_order` int(11) DEFAULT '0' COMMENT '排序',
    `is_active` tinyint(1) DEFAULT '1' COMMENT '是否启用',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_group_key` (`group_key`),
    KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配置分组表';

-- 4. 插入默认配置分组
INSERT INTO `system_setting_groups` (`group_key`, `group_name`, `description`, `icon`, `sort_order`) VALUES
('system', '系统配置', '基础系统配置选项', 'cpu', 1),
('security', '安全配置', '系统安全相关配置', 'shield-lock', 2),
('database', '数据库配置', '数据库连接和优化配置', 'database', 3),
('cache', '缓存配置', '缓存系统配置', 'lightning', 4),
('email', '邮件配置', '邮件发送服务配置', 'envelope', 5),
('ai', 'AI服务配置', 'AI模型和API配置', 'robot', 6),
('api', 'API配置', 'API接口相关配置', 'code-slash', 7),
('ui', '界面配置', '用户界面和主题配置', 'palette', 8),
('performance', '性能配置', '系统性能优化配置', 'speedometer2', 9),
('backup', '备份配置', '数据备份相关配置', 'archive', 10),
('monitoring', '监控配置', '系统监控和报警配置', 'graph-up', 11),
('logging', '日志配置', '日志记录和管理配置', 'journal-text', 12)
ON DUPLICATE KEY UPDATE 
    `group_name` = VALUES(`group_name`),
    `description` = VALUES(`description`),
    `icon` = VALUES(`icon`),
    `sort_order` = VALUES(`sort_order`);

-- 5. 插入默认系统配置
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_required`) VALUES
-- 系统基础配置
('system.app_name', 'AlingAi Pro', 'string', 'system', '应用程序名称', 1),
('system.app_version', '5.0.0', 'string', 'system', '应用程序版本', 1),
('system.app_url', 'https://alingai.com', 'url', 'system', '应用程序URL', 1),
('system.timezone', 'Asia/Shanghai', 'string', 'system', '系统时区', 1),
('system.locale', 'zh_CN', 'string', 'system', '系统语言', 1),
('system.debug_mode', 'false', 'boolean', 'system', '调试模式', 0),
('system.maintenance_mode', 'false', 'boolean', 'system', '维护模式', 0),
('system.max_upload_size', '50', 'integer', 'system', '最大上传文件大小(MB)', 1),

-- 安全配置
('security.password_min_length', '8', 'integer', 'security', '密码最小长度', 1),
('security.password_require_special', 'true', 'boolean', 'security', '密码需要特殊字符', 1),
('security.session_timeout', '3600', 'integer', 'security', '会话超时时间(秒)', 1),
('security.max_login_attempts', '5', 'integer', 'security', '最大登录尝试次数', 1),
('security.lockout_duration', '900', 'integer', 'security', '账户锁定持续时间(秒)', 1),
('security.two_factor_auth', 'false', 'boolean', 'security', '启用双因素认证', 0),
('security.ip_whitelist', '[]', 'json', 'security', 'IP白名单', 0),

-- 数据库配置
('database.query_log', 'false', 'boolean', 'database', '启用查询日志', 0),
('database.slow_query_threshold', '2', 'float', 'database', '慢查询阈值(秒)', 0),
('database.connection_pool_size', '10', 'integer', 'database', '连接池大小', 0),
('database.backup_retention_days', '30', 'integer', 'database', '备份保留天数', 0),

-- 缓存配置
('cache.driver', 'redis', 'string', 'cache', '缓存驱动', 1),
('cache.default_ttl', '3600', 'integer', 'cache', '默认缓存时间(秒)', 1),
('cache.prefix', 'alingai:', 'string', 'cache', '缓存键前缀', 1),
('cache.compression', 'true', 'boolean', 'cache', '启用压缩', 0),

-- 邮件配置
('email.driver', 'smtp', 'string', 'email', '邮件驱动', 1),
('email.from_address', 'noreply@alingai.com', 'email', 'email', '发件人邮箱', 1),
('email.from_name', 'AlingAi Pro', 'string', 'email', '发件人名称', 1),
('email.queue_enabled', 'true', 'boolean', 'email', '启用邮件队列', 0),

-- AI服务配置
('ai.default_model', 'gpt-3.5-turbo', 'string', 'ai', '默认AI模型', 1),
('ai.max_tokens', '2048', 'integer', 'ai', '最大token数', 1),
('ai.temperature', '0.7', 'float', 'ai', '温度参数', 0),
('ai.timeout', '30', 'integer', 'ai', 'API超时时间(秒)', 1),
('ai.retry_attempts', '3', 'integer', 'ai', '重试次数', 0),

-- API配置
('api.rate_limit', '100', 'integer', 'api', 'API速率限制(每分钟)', 1),
('api.cors_enabled', 'true', 'boolean', 'api', '启用CORS', 1),
('api.versioning', 'true', 'boolean', 'api', '启用API版本控制', 1),
('api.documentation', 'true', 'boolean', 'api', '启用API文档', 1),

-- 界面配置
('ui.theme', 'auto', 'string', 'ui', '默认主题', 1),
('ui.sidebar_collapsed', 'false', 'boolean', 'ui', '侧边栏默认折叠', 0),
('ui.show_breadcrumbs', 'true', 'boolean', 'ui', '显示面包屑导航', 0),
('ui.items_per_page', '20', 'integer', 'ui', '每页显示条数', 1),

-- 性能配置
('performance.enable_opcache', 'true', 'boolean', 'performance', '启用OPCache', 0),
('performance.enable_gzip', 'true', 'boolean', 'performance', '启用Gzip压缩', 0),
('performance.cdn_enabled', 'false', 'boolean', 'performance', '启用CDN', 0),
('performance.image_optimization', 'true', 'boolean', 'performance', '启用图片优化', 0),

-- 备份配置
('backup.auto_backup', 'true', 'boolean', 'backup', '启用自动备份', 1),
('backup.backup_schedule', '0 2 * * *', 'string', 'backup', '备份计划(Cron)', 1),
('backup.backup_retention', '7', 'integer', 'backup', '备份保留天数', 1),
('backup.include_uploads', 'true', 'boolean', 'backup', '包含上传文件', 0),

-- 监控配置
('monitoring.enable_alerts', 'true', 'boolean', 'monitoring', '启用监控报警', 1),
('monitoring.cpu_threshold', '80', 'integer', 'monitoring', 'CPU使用率阈值(%)', 1),
('monitoring.memory_threshold', '85', 'integer', 'monitoring', '内存使用率阈值(%)', 1),
('monitoring.disk_threshold', '90', 'integer', 'monitoring', '磁盘使用率阈值(%)', 1),
('monitoring.check_interval', '300', 'integer', 'monitoring', '检查间隔(秒)', 1),

-- 日志配置
('logging.level', 'info', 'string', 'logging', '日志级别', 1),
('logging.max_files', '30', 'integer', 'logging', '最大日志文件数', 1),
('logging.max_size', '100', 'integer', 'logging', '单个日志文件最大大小(MB)', 1),
('logging.enable_query_log', 'false', 'boolean', 'logging', '启用查询日志', 0)

ON DUPLICATE KEY UPDATE 
    `setting_value` = CASE 
        WHEN `is_required` = 1 AND `setting_value` IS NULL THEN VALUES(`setting_value`)
        ELSE `setting_value`
    END,
    `description` = VALUES(`description`),
    `is_required` = VALUES(`is_required`);

-- 6. 创建配置权限表（可选）
CREATE TABLE IF NOT EXISTS `system_setting_permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(255) NOT NULL,
    `role` varchar(100) NOT NULL COMMENT '角色',
    `permission` enum('read','write','admin') DEFAULT 'read' COMMENT '权限类型',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_setting_role` (`setting_key`, `role`),
    KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配置权限表';

-- 7. 创建配置模板表（可选）
CREATE TABLE IF NOT EXISTS `system_setting_templates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `template_name` varchar(200) NOT NULL COMMENT '模板名称',
    `template_key` varchar(100) NOT NULL COMMENT '模板键',
    `description` text COMMENT '模板描述',
    `settings` longtext COMMENT '配置数据(JSON)',
    `is_system` tinyint(1) DEFAULT '0' COMMENT '是否系统模板',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_template_key` (`template_key`),
    KEY `idx_is_system` (`is_system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配置模板表';

-- 8. 插入默认配置模板
INSERT INTO `system_setting_templates` (`template_name`, `template_key`, `description`, `settings`, `is_system`) VALUES
('开发环境', 'development', '适用于开发环境的配置模板', '{"system.debug_mode": "true", "cache.default_ttl": "60", "logging.level": "debug"}', 1),
('生产环境', 'production', '适用于生产环境的配置模板', '{"system.debug_mode": "false", "cache.default_ttl": "3600", "logging.level": "error"}', 1),
('高性能', 'high_performance', '高性能优化配置模板', '{"performance.enable_opcache": "true", "performance.enable_gzip": "true", "cache.compression": "true"}', 1)
ON DUPLICATE KEY UPDATE 
    `template_name` = VALUES(`template_name`),
    `description` = VALUES(`description`),
    `settings` = VALUES(`settings`);

-- 9. 创建索引优化
ALTER TABLE `system_settings` 
ADD INDEX `idx_category_type` (`category`, `setting_type`),
ADD INDEX `idx_created_updated` (`created_at`, `updated_at`);

ALTER TABLE `system_settings_history` 
ADD INDEX `idx_key_time` (`setting_key`, `changed_at`),
ADD INDEX `idx_changed_by_time` (`changed_by`, `changed_at`);

-- 10. 创建视图（可选）
CREATE OR REPLACE VIEW `v_system_settings_summary` AS
SELECT 
    `category`,
    COUNT(*) as `setting_count`,
    COUNT(CASE WHEN `is_required` = 1 THEN 1 END) as `required_count`,
    COUNT(CASE WHEN `is_sensitive` = 1 THEN 1 END) as `sensitive_count`,
    MAX(`updated_at`) as `last_updated`
FROM `system_settings` 
GROUP BY `category`
ORDER BY `category`;

-- 完成
SELECT 'Configuration management tables created successfully!' as status;
