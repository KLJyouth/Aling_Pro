-- 对话历史表 - 支持AI代理和用户对话记录 v2

-- 创建对话会话表
CREATE TABLE IF NOT EXISTS `conversation_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(100) NOT NULL UNIQUE COMMENT '会话唯一标识',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '用户ID',
  `session_title` varchar(255) DEFAULT NULL COMMENT '会话标题',
  `session_context` json DEFAULT NULL COMMENT '会话上下文',
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后活动时间',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否活跃',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='对话会话表';

-- 创建对话历史表
CREATE TABLE IF NOT EXISTS `conversation_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` varchar(100) NOT NULL COMMENT '对话唯一标识',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '用户ID',
  `agent_id` varchar(100) NOT NULL COMMENT '代理ID',
  `message_type` varchar(20) NOT NULL COMMENT '消息类型 (user, agent, system)',
  `message_content` text NOT NULL COMMENT '消息内容',
  `message_metadata` json DEFAULT NULL COMMENT '消息元数据 (tokens, model, etc.)',
  `parent_message_id` bigint(20) unsigned DEFAULT NULL COMMENT '父消息ID (用于回复链)',
  `context_data` json DEFAULT NULL COMMENT '上下文数据',
  `session_id` varchar(100) DEFAULT NULL COMMENT '会话ID',
  `ip_address` varchar(45) DEFAULT NULL COMMENT '用户IP地址',
  `user_agent` text DEFAULT NULL COMMENT '用户代理字符串',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_conversation_id` (`conversation_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_agent_id` (`agent_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_parent_message_id` (`parent_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='对话历史记录表';
