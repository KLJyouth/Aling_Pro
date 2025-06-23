-- AlingAi_pro 系统数据库结构
-- 版本: 5.1.0
-- 创建日期: 2023-09-01

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- 表结构 `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `role` enum('admin','manager','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `api_key` varchar(64) DEFAULT NULL,
  `settings` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `user_sessions`
--

CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `api_keys`
--

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `permissions` text,
  `status` enum('active','revoked') NOT NULL DEFAULT 'active',
  `last_used` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `rate_limit` int(11) DEFAULT '100',
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `api_access_log`
--

CREATE TABLE IF NOT EXISTS `api_access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `status_code` int(11) NOT NULL,
  `response_time` float NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `request_data` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `api_key_id` (`api_key_id`),
  KEY `user_id` (`user_id`),
  KEY `endpoint` (`endpoint`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `api_access_log_ibfk_1` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL,
  CONSTRAINT `api_access_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `ai_engines`
--

CREATE TABLE IF NOT EXISTS `ai_engines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `version` varchar(20) NOT NULL,
  `status` enum('active','maintenance','offline') NOT NULL DEFAULT 'active',
  `config` text,
  `api_endpoint` varchar(255),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_version` (`name`,`version`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `ai_engine_usage`
--

CREATE TABLE IF NOT EXISTS `ai_engine_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `engine_id` int(11) NOT NULL,
  `tokens_input` int(11) NOT NULL DEFAULT '0',
  `tokens_output` int(11) NOT NULL DEFAULT '0',
  `processing_time` float NOT NULL,
  `request_id` varchar(36) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('success','failed','timeout') NOT NULL DEFAULT 'success',
  `error_message` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `engine_id` (`engine_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `ai_engine_usage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_engine_usage_ibfk_2` FOREIGN KEY (`engine_id`) REFERENCES `ai_engines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `security_audit_log`
--

CREATE TABLE IF NOT EXISTS `security_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `severity` enum('info','warning','critical') NOT NULL DEFAULT 'info',
  `status` enum('success','failed') NOT NULL DEFAULT 'success',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`),
  KEY `severity` (`severity`),
  CONSTRAINT `security_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `system_settings`
--

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `description` text,
  `data_type` enum('string','integer','boolean','json','float') NOT NULL DEFAULT 'string',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `chat_sessions`
--

CREATE TABLE IF NOT EXISTS `chat_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `engine_id` int(11) NOT NULL,
  `status` enum('active','archived','deleted') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_message_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metadata` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `engine_id` (`engine_id`),
  KEY `status` (`status`),
  KEY `last_message_at` (`last_message_at`),
  CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_sessions_ibfk_2` FOREIGN KEY (`engine_id`) REFERENCES `ai_engines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `chat_messages`
--

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `role` enum('user','system','assistant') NOT NULL,
  `content` text NOT NULL,
  `tokens` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metadata` text,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `billing`
--

CREATE TABLE IF NOT EXISTS `billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan` varchar(50) NOT NULL DEFAULT 'free',
  `status` enum('active','suspended','cancelled') NOT NULL DEFAULT 'active',
  `next_billing_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `plan` (`plan`),
  KEY `status` (`status`),
  CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `user_usage_quota`
--

CREATE TABLE IF NOT EXISTS `user_usage_quota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `quota_type` enum('tokens','requests','api_calls') NOT NULL,
  `limit_value` int(11) NOT NULL DEFAULT '0',
  `used_value` int(11) NOT NULL DEFAULT '0',
  `reset_period` enum('daily','weekly','monthly','never') NOT NULL DEFAULT 'monthly',
  `next_reset` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_quota_type` (`user_id`,`quota_type`),
  CONSTRAINT `user_usage_quota_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `system_jobs`
--

CREATE TABLE IF NOT EXISTS `system_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(100) NOT NULL,
  `job_status` enum('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
  `job_data` text,
  `scheduled_at` datetime NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `error_message` text,
  `retry_count` int(11) NOT NULL DEFAULT '0',
  `max_retries` int(11) NOT NULL DEFAULT '3',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `job_status` (`job_status`),
  KEY `scheduled_at` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表结构 `user_feedback`
--

CREATE TABLE IF NOT EXISTS `user_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `message_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `feedback_text` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `feedback_type` enum('general','message','feature') NOT NULL DEFAULT 'general',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `user_feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_feedback_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 初始数据
--

-- 默认系统设置
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`, `description`, `data_type`) VALUES
('site_name', 'AlingAi Pro', 'general', '站点名称', 'string'),
('site_description', 'AlingAi Pro AI平台', 'general', '站点描述', 'string'),
('maintenance_mode', '0', 'system', '维护模式', 'boolean'),
('default_language', 'zh_CN', 'localization', '默认语言', 'string'),
('allow_registration', '1', 'users', '是否允许用户注册', 'boolean'),
('default_user_quota_tokens', '100000', 'quota', '默认用户Token配额', 'integer'),
('default_user_quota_requests', '1000', 'quota', '默认用户请求数配额', 'integer'),
('system_version', '5.1.0', 'system', '系统版本', 'string'),
('api_rate_limit', '60', 'api', 'API速率限制（每分钟请求数）', 'integer'),
('file_upload_max_size', '10', 'uploads', '最大文件上传大小（MB）', 'integer'),
('allowed_file_extensions', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt', 'uploads', '允许的文件扩展名', 'string'),
('smtp_host', '', 'email', 'SMTP服务器地址', 'string'),
('smtp_port', '587', 'email', 'SMTP端口', 'integer'),
('smtp_user', '', 'email', 'SMTP用户名', 'string'),
('smtp_password', '', 'email', 'SMTP密码', 'string'),
('smtp_from_email', '', 'email', '发信人邮箱', 'string'),
('smtp_from_name', 'AlingAi Pro', 'email', '发信人名称', 'string');

-- 添加默认AI引擎
INSERT INTO `ai_engines` (`name`, `description`, `version`, `status`, `config`) VALUES
('AlingAi-Base', '基础通用AI模型', '1.0.0', 'active', '{"model_type":"gpt","temperature":0.7,"max_tokens":4096}'),
('AlingAi-Pro', '高级专业AI模型', '1.0.0', 'active', '{"model_type":"gpt","temperature":0.5,"max_tokens":8192}'),
('AlingAi-Code', '代码专用AI模型', '1.0.0', 'active', '{"model_type":"codex","temperature":0.2,"max_tokens":8192}'),
('AlingAi-Image', '图像生成AI模型', '1.0.0', 'active', '{"model_type":"diffusion","resolution":"1024x1024"}');

-- 启用外键约束
SET FOREIGN_KEY_CHECKS=1;
