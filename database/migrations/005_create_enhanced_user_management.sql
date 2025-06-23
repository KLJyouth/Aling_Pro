-- 增强用户管理系统数据库迁移
-- 创建时间: 2025-06-06
-- 版本: 2.0.0
-- 功能: 企业级用户管理、API配额、支付集成、第三方AI集成

-- 1. 扩展用户表，添加企业用户支持
ALTER TABLE `users` 
ADD COLUMN `user_type` ENUM('personal', 'enterprise') DEFAULT 'personal' COMMENT '用户类型',
ADD COLUMN `company_name` VARCHAR(255) DEFAULT NULL COMMENT '公司名称',
ADD COLUMN `company_size` ENUM('1-10', '11-50', '51-200', '201-1000', '1000+') DEFAULT NULL COMMENT '公司规模',
ADD COLUMN `industry` VARCHAR(100) DEFAULT NULL COMMENT '所属行业',
ADD COLUMN `application_status` ENUM('pending', 'approved', 'rejected', 'under_review') DEFAULT 'pending' COMMENT '申请状态',
ADD COLUMN `application_notes` TEXT DEFAULT NULL COMMENT '申请备注',
ADD COLUMN `approved_by` INT(11) UNSIGNED DEFAULT NULL COMMENT '审批人ID',
ADD COLUMN `approved_at` TIMESTAMP NULL DEFAULT NULL COMMENT '审批时间',
ADD COLUMN `api_quota_daily` INT(11) DEFAULT 100 COMMENT '每日API配额',
ADD COLUMN `api_quota_monthly` INT(11) DEFAULT 3000 COMMENT '每月API配额',
ADD COLUMN `api_usage_daily` INT(11) DEFAULT 0 COMMENT '每日API使用量',
ADD COLUMN `api_usage_monthly` INT(11) DEFAULT 0 COMMENT '每月API使用量',
ADD COLUMN `wallet_balance` DECIMAL(10,2) DEFAULT 0.00 COMMENT '钱包余额',
ADD COLUMN `anti_bot_verified` TINYINT(1) DEFAULT 0 COMMENT '是否通过反机器人验证',
ADD COLUMN `verification_code` VARCHAR(10) DEFAULT NULL COMMENT '验证码',
ADD COLUMN `verification_expires` TIMESTAMP NULL DEFAULT NULL COMMENT '验证码过期时间',
ADD INDEX `idx_user_type` (`user_type`),
ADD INDEX `idx_application_status` (`application_status`),
ADD INDEX `idx_approved_by` (`approved_by`);

-- 2. 创建API使用统计表
CREATE TABLE IF NOT EXISTS `api_usage_stats` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '统计ID',
    `user_id` INT(11) UNSIGNED NOT NULL COMMENT '用户ID',
    `api_endpoint` VARCHAR(255) NOT NULL COMMENT 'API端点',
    `ai_provider` VARCHAR(50) DEFAULT NULL COMMENT 'AI提供商',
    `model_name` VARCHAR(100) DEFAULT NULL COMMENT '模型名称',
    `tokens_used` INT(11) DEFAULT 0 COMMENT '使用的令牌数量',
    `cost` DECIMAL(10,4) DEFAULT 0.0000 COMMENT '成本',
    `response_time` DECIMAL(8,3) DEFAULT 0.000 COMMENT '响应时间(秒)',
    `status_code` INT(3) DEFAULT 200 COMMENT 'HTTP状态码',
    `error_message` TEXT DEFAULT NULL COMMENT '错误信息',
    `request_data` JSON DEFAULT NULL COMMENT '请求数据',
    `response_data` JSON DEFAULT NULL COMMENT '响应数据摘要',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP地址',
    `user_agent` TEXT DEFAULT NULL COMMENT '用户代理',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_api_endpoint` (`api_endpoint`),
    KEY `idx_ai_provider` (`ai_provider`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_user_date` (`user_id`, `created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API使用统计表';

-- 3. 创建钱包交易记录表
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '交易ID',
    `user_id` INT(11) UNSIGNED NOT NULL COMMENT '用户ID',
    `transaction_type` ENUM('recharge', 'consumption', 'refund', 'bonus') NOT NULL COMMENT '交易类型',
    `amount` DECIMAL(10,2) NOT NULL COMMENT '金额',
    `balance_before` DECIMAL(10,2) NOT NULL COMMENT '交易前余额',
    `balance_after` DECIMAL(10,2) NOT NULL COMMENT '交易后余额',
    `payment_method` ENUM('alipay', 'wechat', 'stripe', 'paypal', 'admin') DEFAULT NULL COMMENT '支付方式',
    `payment_order_id` VARCHAR(100) DEFAULT NULL COMMENT '支付订单号',
    `third_party_order_id` VARCHAR(100) DEFAULT NULL COMMENT '第三方订单号',
    `status` ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending' COMMENT '交易状态',
    `description` VARCHAR(255) DEFAULT NULL COMMENT '交易描述',
    `metadata` JSON DEFAULT NULL COMMENT '扩展数据',
    `processed_at` TIMESTAMP NULL DEFAULT NULL COMMENT '处理时间',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_transaction_type` (`transaction_type`),
    KEY `idx_status` (`status`),
    KEY `idx_payment_order_id` (`payment_order_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='钱包交易记录表';

-- 4. 创建第三方AI集成配置表
CREATE TABLE IF NOT EXISTS `ai_provider_configs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
    `provider_name` VARCHAR(50) NOT NULL COMMENT '提供商名称',
    `display_name` VARCHAR(100) NOT NULL COMMENT '显示名称',
    `api_base_url` VARCHAR(255) NOT NULL COMMENT 'API基础URL',
    `api_key` TEXT DEFAULT NULL COMMENT 'API密钥(加密存储)',
    `models` JSON DEFAULT NULL COMMENT '支持的模型列表',
    `rate_limits` JSON DEFAULT NULL COMMENT '速率限制配置',
    `pricing` JSON DEFAULT NULL COMMENT '定价配置',
    `status` ENUM('active', 'inactive', 'maintenance') DEFAULT 'active' COMMENT '状态',
    `priority` INT(3) DEFAULT 100 COMMENT '优先级',
    `config_data` JSON DEFAULT NULL COMMENT '额外配置数据',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_provider_name` (`provider_name`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='第三方AI集成配置表';

-- 5. 创建用户申请审核记录表
CREATE TABLE IF NOT EXISTS `user_applications` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '申请ID',
    `user_id` INT(11) UNSIGNED NOT NULL COMMENT '用户ID',
    `application_type` ENUM('account_upgrade', 'quota_increase', 'enterprise_access') NOT NULL COMMENT '申请类型',
    `current_data` JSON DEFAULT NULL COMMENT '当前数据',
    `requested_data` JSON NOT NULL COMMENT '申请的数据',
    `reason` TEXT DEFAULT NULL COMMENT '申请理由',
    `attachments` JSON DEFAULT NULL COMMENT '附件列表',
    `status` ENUM('pending', 'approved', 'rejected', 'under_review') DEFAULT 'pending' COMMENT '申请状态',
    `reviewer_id` INT(11) UNSIGNED DEFAULT NULL COMMENT '审核人ID',
    `review_notes` TEXT DEFAULT NULL COMMENT '审核备注',
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL COMMENT '审核时间',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_application_type` (`application_type`),
    KEY `idx_status` (`status`),
    KEY `idx_reviewer_id` (`reviewer_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户申请审核记录表';

-- 6. 创建API配额重置记录表
CREATE TABLE IF NOT EXISTS `api_quota_resets` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '重置ID',
    `user_id` INT(11) UNSIGNED NOT NULL COMMENT '用户ID',
    `reset_type` ENUM('daily', 'monthly', 'manual') NOT NULL COMMENT '重置类型',
    `previous_usage` INT(11) DEFAULT 0 COMMENT '重置前使用量',
    `reset_quota` INT(11) DEFAULT 0 COMMENT '重置后配额',
    `reset_reason` VARCHAR(255) DEFAULT NULL COMMENT '重置原因',
    `reset_by` INT(11) UNSIGNED DEFAULT NULL COMMENT '重置操作人',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_reset_type` (`reset_type`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API配额重置记录表';

-- 7. 创建系统通知表
CREATE TABLE IF NOT EXISTS `system_notifications` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通知ID',
    `user_id` INT(11) UNSIGNED DEFAULT NULL COMMENT '用户ID(NULL表示系统通知)',
    `title` VARCHAR(255) NOT NULL COMMENT '通知标题',
    `content` TEXT NOT NULL COMMENT '通知内容',
    `type` ENUM('info', 'success', 'warning', 'error', 'system') DEFAULT 'info' COMMENT '通知类型',
    `category` VARCHAR(50) DEFAULT NULL COMMENT '通知分类',
    `is_read` TINYINT(1) DEFAULT 0 COMMENT '是否已读',
    `is_important` TINYINT(1) DEFAULT 0 COMMENT '是否重要',
    `action_url` VARCHAR(255) DEFAULT NULL COMMENT '操作链接',
    `action_text` VARCHAR(50) DEFAULT NULL COMMENT '操作按钮文本',
    `data` JSON DEFAULT NULL COMMENT '扩展数据',
    `read_at` TIMESTAMP NULL DEFAULT NULL COMMENT '阅读时间',
    `expires_at` TIMESTAMP NULL DEFAULT NULL COMMENT '过期时间',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_type` (`type`),
    KEY `idx_is_read` (`is_read`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_expires_at` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统通知表';

-- 8. 插入默认的AI提供商配置
INSERT INTO `ai_provider_configs` (`provider_name`, `display_name`, `api_base_url`, `models`, `rate_limits`, `pricing`, `status`, `priority`) VALUES
('baidu', '百度文心一言', 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat', 
 '["ernie-bot", "ernie-bot-turbo", "ernie-bot-4"]', 
 '{"requests_per_minute": 100, "tokens_per_minute": 100000}',
 '{"ernie-bot": {"input": 0.008, "output": 0.016}, "ernie-bot-turbo": {"input": 0.004, "output": 0.008}}',
 'active', 90),
('deepseek', 'DeepSeek', 'https://api.deepseek.com/v1/chat/completions',
 '["deepseek-chat", "deepseek-coder"]',
 '{"requests_per_minute": 60, "tokens_per_minute": 60000}', 
 '{"deepseek-chat": {"input": 0.001, "output": 0.002}, "deepseek-coder": {"input": 0.001, "output": 0.002}}',
 'active', 95),
('coze', 'Coze智能助手', 'https://www.coze.cn/api/conversation/chat',
 '["coze-chat"]',
 '{"requests_per_minute": 30, "tokens_per_minute": 30000}',
 '{"coze-chat": {"input": 0.002, "output": 0.004}}',
 'active', 85);

-- 9. 创建管理员配置表
CREATE TABLE IF NOT EXISTS `admin_configs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
    `config_key` VARCHAR(100) NOT NULL COMMENT '配置键',
    `config_value` TEXT DEFAULT NULL COMMENT '配置值',
    `config_type` ENUM('string', 'number', 'boolean', 'json', 'text') DEFAULT 'string' COMMENT '配置类型',
    `category` VARCHAR(50) DEFAULT 'general' COMMENT '配置分类',
    `description` VARCHAR(255) DEFAULT NULL COMMENT '配置描述',
    `is_encrypted` TINYINT(1) DEFAULT 0 COMMENT '是否加密存储',
    `updated_by` INT(11) UNSIGNED DEFAULT NULL COMMENT '更新人',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_config_key` (`config_key`),
    KEY `idx_category` (`category`),
    KEY `idx_updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员配置表';

-- 10. 插入默认管理员配置
INSERT INTO `admin_configs` (`config_key`, `config_value`, `config_type`, `category`, `description`) VALUES
('payment.alipay.enabled', 'true', 'boolean', 'payment', '启用支付宝支付'),
('payment.wechat.enabled', 'true', 'boolean', 'payment', '启用微信支付'),
('payment.min_recharge', '10.00', 'number', 'payment', '最小充值金额'),
('payment.max_recharge', '10000.00', 'number', 'payment', '最大充值金额'),
('api.default_daily_quota', '100', 'number', 'api', '默认每日API配额'),
('api.default_monthly_quota', '3000', 'number', 'api', '默认每月API配额'),
('user.registration_approval', 'false', 'boolean', 'user', '用户注册需要审批'),
('user.enterprise_approval', 'true', 'boolean', 'user', '企业用户需要审批'),
('email.verification_required', 'true', 'boolean', 'email', '邮箱验证必需'),
('security.anti_bot_enabled', 'true', 'boolean', 'security', '启用反机器人验证'),
('ai.auto_failover', 'true', 'boolean', 'ai', '启用AI服务自动故障转移'),
('ai.load_balancing', 'round_robin', 'string', 'ai', 'AI服务负载均衡策略');
