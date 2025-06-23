-- 系统配置和多语言支持表迁移脚本
-- 适用于MySQL 5.7.43+
-- 支持系统配置管理、多语言本地化、主题管理等功能

-- 创建系统配置表
CREATE TABLE IF NOT EXISTS `system_configs` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
    `config_key` varchar(100) NOT NULL COMMENT '配置键名',
    `config_value` longtext DEFAULT NULL COMMENT '配置值',
    `config_type` varchar(20) DEFAULT 'string' COMMENT '配置类型：string, int, float, bool, json, array',
    `group_name` varchar(50) DEFAULT 'general' COMMENT '配置分组',
    `description` text DEFAULT NULL COMMENT '配置描述',
    `is_public` tinyint(1) DEFAULT 0 COMMENT '是否公开配置(前端可读)',
    `is_editable` tinyint(1) DEFAULT 1 COMMENT '是否可编辑',
    `validation_rules` json DEFAULT NULL COMMENT '验证规则',
    `default_value` text DEFAULT NULL COMMENT '默认值',
    `sort_order` int(11) DEFAULT 0 COMMENT '排序权重',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_config_key` (`config_key`),
    KEY `idx_group_name` (`group_name`),
    KEY `idx_is_public` (`is_public`),
    KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- 创建多语言翻译表
CREATE TABLE IF NOT EXISTS `translations` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '翻译ID',
    `language` varchar(10) NOT NULL COMMENT '语言代码',
    `namespace` varchar(50) DEFAULT 'default' COMMENT '命名空间',
    `group_name` varchar(50) DEFAULT 'default' COMMENT '分组名称',
    `translation_key` varchar(255) NOT NULL COMMENT '翻译键名',
    `translation_value` text NOT NULL COMMENT '翻译内容',
    `is_system` tinyint(1) DEFAULT 0 COMMENT '是否系统翻译',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lang_namespace_group_key` (`language`, `namespace`, `group_name`, `translation_key`),
    KEY `idx_language` (`language`),
    KEY `idx_namespace` (`namespace`),
    KEY `idx_group_name` (`group_name`),
    KEY `idx_is_system` (`is_system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='多语言翻译表';

-- 创建主题管理表
CREATE TABLE IF NOT EXISTS `themes` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主题ID',
    `name` varchar(100) NOT NULL COMMENT '主题名称',
    `slug` varchar(100) NOT NULL COMMENT '主题标识',
    `description` text DEFAULT NULL COMMENT '主题描述',
    `version` varchar(20) DEFAULT '1.0.0' COMMENT '主题版本',
    `author` varchar(100) DEFAULT NULL COMMENT '作者',
    `author_url` varchar(255) DEFAULT NULL COMMENT '作者网址',
    `preview_image` varchar(500) DEFAULT NULL COMMENT '预览图',
    `css_variables` json DEFAULT NULL COMMENT 'CSS变量配置',
    `js_config` json DEFAULT NULL COMMENT 'JS配置',
    `is_active` tinyint(1) DEFAULT 0 COMMENT '是否启用',
    `is_default` tinyint(1) DEFAULT 0 COMMENT '是否默认主题',
    `supports` json DEFAULT NULL COMMENT '支持的功能',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='主题管理表';

-- 创建文件管理表
CREATE TABLE IF NOT EXISTS `files` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
    `user_id` int(11) unsigned DEFAULT NULL COMMENT '上传用户ID',
    `filename` varchar(255) NOT NULL COMMENT '文件名',
    `original_name` varchar(255) NOT NULL COMMENT '原始文件名',
    `file_path` varchar(500) NOT NULL COMMENT '文件路径',
    `file_url` varchar(500) DEFAULT NULL COMMENT '文件URL',
    `file_size` bigint(20) unsigned DEFAULT 0 COMMENT '文件大小(字节)',
    `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME类型',
    `file_extension` varchar(10) DEFAULT NULL COMMENT '文件扩展名',
    `file_hash` varchar(64) DEFAULT NULL COMMENT '文件哈希值(SHA256)',
    `storage_type` varchar(20) DEFAULT 'local' COMMENT '存储类型：local, s3, oss, etc.',
    `storage_config` json DEFAULT NULL COMMENT '存储配置',
    `is_public` tinyint(1) DEFAULT 1 COMMENT '是否公开访问',
    `download_count` int(11) unsigned DEFAULT 0 COMMENT '下载次数',
    `metadata` json DEFAULT NULL COMMENT '文件元数据',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_file_hash` (`file_hash`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_mime_type` (`mime_type`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_deleted_at` (`deleted_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文件管理表';

-- 创建通知消息表
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '通知ID',
    `user_id` int(11) unsigned DEFAULT NULL COMMENT '接收用户ID(NULL表示系统通知)',
    `type` varchar(50) NOT NULL COMMENT '通知类型：info, success, warning, error, system',
    `title` varchar(255) NOT NULL COMMENT '通知标题',
    `content` text DEFAULT NULL COMMENT '通知内容',
    `action_url` varchar(500) DEFAULT NULL COMMENT '操作链接',
    `action_text` varchar(100) DEFAULT NULL COMMENT '操作按钮文本',
    `is_read` tinyint(1) DEFAULT 0 COMMENT '是否已读',
    `is_sent` tinyint(1) DEFAULT 0 COMMENT '是否已发送',
    `send_methods` json DEFAULT NULL COMMENT '发送方式：email, sms, push, websocket',
    `priority` tinyint(1) DEFAULT 5 COMMENT '优先级(1-10)',
    `expires_at` timestamp NULL DEFAULT NULL COMMENT '过期时间',
    `read_at` timestamp NULL DEFAULT NULL COMMENT '已读时间',
    `sent_at` timestamp NULL DEFAULT NULL COMMENT '发送时间',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_type` (`type`),
    KEY `idx_is_read` (`is_read`),
    KEY `idx_is_sent` (`is_sent`),
    KEY `idx_priority` (`priority`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_expires_at` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通知消息表';

-- 插入默认系统配置
INSERT INTO `system_configs` (`config_key`, `config_value`, `config_type`, `group_name`, `description`, `is_public`, `sort_order`) VALUES
-- 基础配置
('app.name', 'AlingAi', 'string', 'basic', '应用名称', 1, 1),
('app.version', '2.0.0', 'string', 'basic', '应用版本', 1, 2),
('app.description', '智能AI内容管理平台', 'string', 'basic', '应用描述', 1, 3),
('app.url', 'https://your-domain.com', 'string', 'basic', '应用URL', 1, 4),
('app.timezone', 'Asia/Shanghai', 'string', 'basic', '默认时区', 1, 5),
('app.language', 'zh-cn', 'string', 'basic', '默认语言', 1, 6),

-- 用户配置
('user.registration_enabled', '1', 'bool', 'user', '是否允许用户注册', 1, 10),
('user.email_verification', '1', 'bool', 'user', '是否需要邮箱验证', 0, 11),
('user.default_role', 'user', 'string', 'user', '默认用户角色', 0, 12),
('user.login_max_attempts', '5', 'int', 'user', '最大登录尝试次数', 0, 13),
('user.login_lockout_time', '900', 'int', 'user', '登录锁定时间(秒)', 0, 14),

-- AI配置
('ai.default_model', 'gpt-3.5-turbo', 'string', 'ai', '默认AI模型', 0, 20),
('ai.max_tokens', '2048', 'int', 'ai', '最大token数', 0, 21),
('ai.temperature', '0.7', 'float', 'ai', '生成温度', 0, 22),
('ai.rate_limit_per_hour', '100', 'int', 'ai', '每小时请求限制', 0, 23),

-- 文件配置
('file.max_upload_size', '10485760', 'int', 'file', '最大上传文件大小(字节)', 0, 30),
('file.allowed_extensions', '["jpg","jpeg","png","gif","pdf","doc","docx","txt"]', 'json', 'file', '允许的文件扩展名', 0, 31),
('file.storage_driver', 'local', 'string', 'file', '存储驱动', 0, 32),

-- 邮件配置
('mail.driver', 'smtp', 'string', 'mail', '邮件驱动', 0, 40),
('mail.host', 'smtp.example.com', 'string', 'mail', 'SMTP主机', 0, 41),
('mail.port', '587', 'int', 'mail', 'SMTP端口', 0, 42),
('mail.from_address', 'noreply@example.com', 'string', 'mail', '发件人邮箱', 0, 43),
('mail.from_name', 'AlingAi', 'string', 'mail', '发件人姓名', 0, 44);

-- 插入中文翻译
INSERT INTO `translations` (`language`, `namespace`, `group_name`, `translation_key`, `translation_value`, `is_system`) VALUES
-- 基础翻译
('zh-cn', 'default', 'common', 'home', '首页', 1),
('zh-cn', 'default', 'common', 'about', '关于', 1),
('zh-cn', 'default', 'common', 'contact', '联系', 1),
('zh-cn', 'default', 'common', 'login', '登录', 1),
('zh-cn', 'default', 'common', 'logout', '退出', 1),
('zh-cn', 'default', 'common', 'register', '注册', 1),
('zh-cn', 'default', 'common', 'profile', '个人资料', 1),
('zh-cn', 'default', 'common', 'settings', '设置', 1),
('zh-cn', 'default', 'common', 'admin', '管理', 1),
('zh-cn', 'default', 'common', 'dashboard', '仪表板', 1),

-- 用户相关
('zh-cn', 'default', 'user', 'username', '用户名', 1),
('zh-cn', 'default', 'user', 'email', '邮箱', 1),
('zh-cn', 'default', 'user', 'password', '密码', 1),
('zh-cn', 'default', 'user', 'confirm_password', '确认密码', 1),
('zh-cn', 'default', 'user', 'remember_me', '记住我', 1),
('zh-cn', 'default', 'user', 'forgot_password', '忘记密码', 1),

-- AI相关
('zh-cn', 'default', 'ai', 'generate_content', '生成内容', 1),
('zh-cn', 'default', 'ai', 'analyze_content', '分析内容', 1),
('zh-cn', 'default', 'ai', 'translate_text', '翻译文本', 1),
('zh-cn', 'default', 'ai', 'summarize_text', '总结文本', 1),
('zh-cn', 'default', 'ai', 'ai_model', 'AI模型', 1),
('zh-cn', 'default', 'ai', 'prompt', '提示词', 1),

-- 操作相关
('zh-cn', 'default', 'action', 'create', '创建', 1),
('zh-cn', 'default', 'action', 'edit', '编辑', 1),
('zh-cn', 'default', 'action', 'delete', '删除', 1),
('zh-cn', 'default', 'action', 'save', '保存', 1),
('zh-cn', 'default', 'action', 'cancel', '取消', 1),
('zh-cn', 'default', 'action', 'submit', '提交', 1),
('zh-cn', 'default', 'action', 'search', '搜索', 1),
('zh-cn', 'default', 'action', 'filter', '过滤', 1),
('zh-cn', 'default', 'action', 'export', '导出', 1),
('zh-cn', 'default', 'action', 'import', '导入', 1);

-- 插入英文翻译
INSERT INTO `translations` (`language`, `namespace`, `group_name`, `translation_key`, `translation_value`, `is_system`) VALUES
-- 基础翻译
('en', 'default', 'common', 'home', 'Home', 1),
('en', 'default', 'common', 'about', 'About', 1),
('en', 'default', 'common', 'contact', 'Contact', 1),
('en', 'default', 'common', 'login', 'Login', 1),
('en', 'default', 'common', 'logout', 'Logout', 1),
('en', 'default', 'common', 'register', 'Register', 1),
('en', 'default', 'common', 'profile', 'Profile', 1),
('en', 'default', 'common', 'settings', 'Settings', 1),
('en', 'default', 'common', 'admin', 'Admin', 1),
('en', 'default', 'common', 'dashboard', 'Dashboard', 1),

-- 用户相关
('en', 'default', 'user', 'username', 'Username', 1),
('en', 'default', 'user', 'email', 'Email', 1),
('en', 'default', 'user', 'password', 'Password', 1),
('en', 'default', 'user', 'confirm_password', 'Confirm Password', 1),
('en', 'default', 'user', 'remember_me', 'Remember Me', 1),
('en', 'default', 'user', 'forgot_password', 'Forgot Password', 1),

-- AI相关
('en', 'default', 'ai', 'generate_content', 'Generate Content', 1),
('en', 'default', 'ai', 'analyze_content', 'Analyze Content', 1),
('en', 'default', 'ai', 'translate_text', 'Translate Text', 1),
('en', 'default', 'ai', 'summarize_text', 'Summarize Text', 1),
('en', 'default', 'ai', 'ai_model', 'AI Model', 1),
('en', 'default', 'ai', 'prompt', 'Prompt', 1),

-- 操作相关
('en', 'default', 'action', 'create', 'Create', 1),
('en', 'default', 'action', 'edit', 'Edit', 1),
('en', 'default', 'action', 'delete', 'Delete', 1),
('en', 'default', 'action', 'save', 'Save', 1),
('en', 'default', 'action', 'cancel', 'Cancel', 1),
('en', 'default', 'action', 'submit', 'Submit', 1),
('en', 'default', 'action', 'search', 'Search', 1),
('en', 'default', 'action', 'filter', 'Filter', 1),
('en', 'default', 'action', 'export', 'Export', 1),
('en', 'default', 'action', 'import', 'Import', 1);

-- 插入默认主题
INSERT INTO `themes` (`name`, `slug`, `description`, `css_variables`, `is_active`, `is_default`) VALUES
('默认主题', 'default', 'AlingAi默认主题', JSON_OBJECT(
    'primary-color', '#2563eb',
    'secondary-color', '#64748b',
    'success-color', '#10b981',
    'warning-color', '#f59e0b',
    'error-color', '#ef4444',
    'background-color', '#ffffff',
    'surface-color', '#f8fafc',
    'text-color', '#1e293b',
    'border-color', '#e2e8f0'
), 1, 1),
('暗黑主题', 'dark', 'AlingAi暗黑主题', JSON_OBJECT(
    'primary-color', '#3b82f6',
    'secondary-color', '#6b7280',
    'success-color', '#22c55e',
    'warning-color', '#eab308',
    'error-color', '#f87171',
    'background-color', '#0f172a',
    'surface-color', '#1e293b',
    'text-color', '#f1f5f9',
    'border-color', '#334155'
), 0, 0),
('量子主题', 'quantum', 'AlingAi量子主题', JSON_OBJECT(
    'primary-color', '#8b5cf6',
    'secondary-color', '#a855f7',
    'success-color', '#06d6a0',
    'warning-color', '#ffd166',
    'error-color', '#f72585',
    'background-color', '#0a0a0f',
    'surface-color', '#1a1a2e',
    'text-color', '#eee6ff',
    'border-color', '#16213e'
), 0, 0);
