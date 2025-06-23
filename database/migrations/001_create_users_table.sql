-- 用户表迁移脚本
-- 适用于MySQL 5.7.43+
-- 支持多语言、角色权限、完整用户管理功能

-- 创建用户表
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
    `username` varchar(50) NOT NULL COMMENT '用户名',
    `email` varchar(100) NOT NULL COMMENT '邮箱',
    `password` varchar(255) NOT NULL COMMENT '密码(bcrypt加密)',
    `salt` varchar(32) DEFAULT NULL COMMENT '密码盐值',
    `nickname` varchar(100) DEFAULT NULL COMMENT '昵称',
    `avatar` varchar(500) DEFAULT NULL COMMENT '头像URL',
    `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
    `gender` tinyint(1) DEFAULT 0 COMMENT '性别：0-未知，1-男，2-女',
    `birthday` date DEFAULT NULL COMMENT '生日',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用，1-正常，2-待验证',
    `role` varchar(20) DEFAULT 'user' COMMENT '用户角色：user, admin, super_admin',
    `language` varchar(10) DEFAULT 'zh-cn' COMMENT '首选语言',
    `timezone` varchar(50) DEFAULT 'Asia/Shanghai' COMMENT '时区',
    `email_verified_at` timestamp NULL DEFAULT NULL COMMENT '邮箱验证时间',
    `phone_verified_at` timestamp NULL DEFAULT NULL COMMENT '手机验证时间',
    `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
    `last_login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
    `login_count` int(11) unsigned DEFAULT 0 COMMENT '登录次数',
    `failed_login_count` int(11) unsigned DEFAULT 0 COMMENT '失败登录次数',
    `locked_until` timestamp NULL DEFAULT NULL COMMENT '锁定到期时间',
    `settings` json DEFAULT NULL COMMENT '用户设置(JSON格式)',
    `metadata` json DEFAULT NULL COMMENT '扩展数据(JSON格式)',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`),
    KEY `idx_status` (`status`),
    KEY `idx_role` (`role`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_deleted_at` (`deleted_at`),
    KEY `idx_last_login` (`last_login_at`),
    KEY `idx_email_verified` (`email_verified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- 创建用户会话表
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` varchar(128) NOT NULL COMMENT '会话ID',
    `user_id` int(11) unsigned DEFAULT NULL COMMENT '用户ID',
    `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP地址',
    `user_agent` text DEFAULT NULL COMMENT '用户代理',
    `payload` longtext NOT NULL COMMENT '会话数据',
    `last_activity` int(11) unsigned NOT NULL COMMENT '最后活动时间',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_last_activity` (`last_activity`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户会话表';

-- 创建用户权限表
CREATE TABLE IF NOT EXISTS `user_permissions` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限ID',
    `name` varchar(50) NOT NULL COMMENT '权限名称',
    `slug` varchar(50) NOT NULL COMMENT '权限标识',
    `description` text DEFAULT NULL COMMENT '权限描述',
    `group_name` varchar(50) DEFAULT NULL COMMENT '权限组',
    `is_system` tinyint(1) DEFAULT 0 COMMENT '是否系统权限',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限表';

-- 创建角色权限关联表
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `role` varchar(20) NOT NULL COMMENT '角色名',
    `permission_id` int(11) unsigned NOT NULL COMMENT '权限ID',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_permission` (`role`, `permission_id`),
    KEY `idx_role` (`role`),
    FOREIGN KEY (`permission_id`) REFERENCES `user_permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色权限关联表';

-- 创建用户操作日志表
CREATE TABLE IF NOT EXISTS `user_logs` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
    `user_id` int(11) unsigned DEFAULT NULL COMMENT '用户ID',
    `action` varchar(50) NOT NULL COMMENT '操作类型',
    `resource` varchar(100) DEFAULT NULL COMMENT '操作资源',
    `resource_id` int(11) unsigned DEFAULT NULL COMMENT '资源ID',
    `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP地址',
    `user_agent` text DEFAULT NULL COMMENT '用户代理',
    `request_data` json DEFAULT NULL COMMENT '请求数据',
    `response_data` json DEFAULT NULL COMMENT '响应数据',
    `status` varchar(20) DEFAULT 'success' COMMENT '操作状态：success, failed, error',
    `error_message` text DEFAULT NULL COMMENT '错误信息',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户操作日志表';

-- 插入默认权限数据
INSERT INTO `user_permissions` (`name`, `slug`, `description`, `group_name`, `is_system`) VALUES
('查看用户', 'user.view', '查看用户信息', '用户管理', 1),
('创建用户', 'user.create', '创建新用户', '用户管理', 1),
('编辑用户', 'user.edit', '编辑用户信息', '用户管理', 1),
('删除用户', 'user.delete', '删除用户', '用户管理', 1),
('管理员面板', 'admin.panel', '访问管理员面板', '系统管理', 1),
('系统设置', 'system.settings', '修改系统设置', '系统管理', 1),
('查看日志', 'system.logs', '查看系统日志', '系统管理', 1),
('文件上传', 'file.upload', '上传文件', '文件管理', 0),
('文件删除', 'file.delete', '删除文件', '文件管理', 0);

-- 分配默认角色权限
INSERT INTO `role_permissions` (`role`, `permission_id`) VALUES
-- super_admin 拥有所有权限
('super_admin', 1), ('super_admin', 2), ('super_admin', 3), ('super_admin', 4),
('super_admin', 5), ('super_admin', 6), ('super_admin', 7), ('super_admin', 8), ('super_admin', 9),
-- admin 拥有基础管理权限
('admin', 1), ('admin', 2), ('admin', 3), ('admin', 5), ('admin', 7), ('admin', 8),
-- user 拥有基础权限
('user', 8);
