-- 登录尝试表
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `ip_address` (`ip_address`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 双因素认证码表
CREATE TABLE IF NOT EXISTS `two_factor_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `two_factor_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 安全日志表
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `event_data` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 设备表
CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `device_id` varchar(64) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_used_at` datetime NOT NULL,
  `is_trusted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_device_unique` (`user_id`, `device_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 密码历史表（用于防止重复使用旧密码）
CREATE TABLE IF NOT EXISTS `password_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 安全通知设置表
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `email` tinyint(1) NOT NULL DEFAULT 1,
  `sms` tinyint(1) NOT NULL DEFAULT 0,
  `push` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_event_unique` (`user_id`, `event_type`),
  CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 用户表添加安全相关字段
ALTER TABLE `users` 
  ADD COLUMN IF NOT EXISTS `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `two_factor_method` varchar(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `two_factor_secret` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `password_expires_at` datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `login_attempts` int(11) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `last_login_at` datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `last_password_change` datetime DEFAULT NULL; 