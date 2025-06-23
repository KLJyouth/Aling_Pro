-- 增强版AI代理任务管理表
-- 三完编译 (Three Complete Compilation) - 企业级AI任务管理

CREATE TABLE IF NOT EXISTS `ai_enhanced_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` varchar(100) NOT NULL COMMENT '任务ID',
  `description` text NOT NULL COMMENT '任务描述',
  `assigned_agent_id` varchar(100) NOT NULL COMMENT '分配的智能体ID',
  `analysis_data` json DEFAULT NULL COMMENT '任务分析数据',
  `status` varchar(50) NOT NULL DEFAULT 'assigned' COMMENT '任务状态',
  `priority` varchar(20) NOT NULL DEFAULT 'medium' COMMENT '任务优先级',
  `execution_result` json DEFAULT NULL COMMENT '执行结果',
  `performance_metrics` json DEFAULT NULL COMMENT '性能指标',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_task_id` (`task_id`),
  KEY `idx_assigned_agent` (`assigned_agent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='增强版AI代理任务表';

-- 智能体性能历史记录表
CREATE TABLE IF NOT EXISTS `ai_agent_performance_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(100) NOT NULL COMMENT '智能体ID',
  `task_id` varchar(100) NOT NULL COMMENT '任务ID',
  `execution_time` int(11) NOT NULL DEFAULT 0 COMMENT '执行时间(秒)',
  `quality_score` decimal(3,2) DEFAULT NULL COMMENT '质量评分(0-1)',
  `success` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否成功',
  `error_message` text DEFAULT NULL COMMENT '错误信息',
  `metrics_data` json DEFAULT NULL COMMENT '详细指标数据',
  `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_agent_id` (`agent_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_recorded_at` (`recorded_at`),
  FOREIGN KEY (`task_id`) REFERENCES `ai_enhanced_tasks`(`task_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='智能体性能历史记录表';

-- 对话历史记录表
CREATE TABLE IF NOT EXISTS `ai_conversation_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` varchar(100) NOT NULL COMMENT '任务ID',
  `user_message` text NOT NULL COMMENT '用户消息',
  `ai_response` text NOT NULL COMMENT 'AI响应',
  `tokens_used` int(11) DEFAULT NULL COMMENT '使用的token数量',
  `response_time` int(11) DEFAULT NULL COMMENT '响应时间(毫秒)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`task_id`) REFERENCES `ai_enhanced_tasks`(`task_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI对话历史记录表';

-- 插入初始数据
INSERT INTO `ai_system_config` (`config_key`, `config_value`, `config_type`, `description`, `created_by`) VALUES
('enhanced_coordinator.deepseek_api_key', '', 'enhanced', 'DeepSeek API密钥', 'system'),
('enhanced_coordinator.max_concurrent_tasks', '10', 'enhanced', '最大并发任务数', 'system'),
('enhanced_coordinator.task_timeout', '300', 'enhanced', '任务超时时间(秒)', 'system'),
('enhanced_coordinator.agent_selection_strategy', 'ai_optimized', 'enhanced', '智能体选择策略', 'system'),
('enhanced_coordinator.load_balancing_enabled', 'true', 'enhanced', '是否启用负载均衡', 'system'),
('enhanced_coordinator.performance_monitoring', 'true', 'enhanced', '是否启用性能监控', 'system'),
('enhanced_coordinator.auto_scaling', 'true', 'enhanced', '是否启用自动扩展', 'system')
ON DUPLICATE KEY UPDATE 
  `config_value` = VALUES(`config_value`),
  `updated_at` = CURRENT_TIMESTAMP;

-- 添加示例智能体记录
INSERT INTO `ai_agents` (`agent_id`, `type`, `name`, `description`, `config`, `status`, `created_at`) VALUES
('enhanced_ppt_generator', 'ppt_generator', 'PPT生成专家', '基于AI的PPT自动生成智能体', '{"capabilities": ["ppt_creation", "slide_design", "content_organization"], "performance_score": 0.92}', 'active', NOW()),
('enhanced_data_analyst', 'data_analyst', '数据分析师', '智能数据分析和可视化专家', '{"capabilities": ["data_analysis", "statistical_modeling", "visualization"], "performance_score": 0.88}', 'active', NOW()),
('enhanced_security_scanner', 'security_scanner', '安全扫描专家', '系统安全扫描和威胁检测专家', '{"capabilities": ["vulnerability_scan", "threat_detection", "security_audit"], "performance_score": 0.94}', 'active', NOW()),
('enhanced_chat_assistant', 'chat_assistant', '智能聊天助手', 'DeepSeek驱动的对话助手', '{"capabilities": ["conversation", "question_answering", "task_assistance"], "performance_score": 0.90}', 'active', NOW())
ON DUPLICATE KEY UPDATE 
  `status` = VALUES(`status`),
  `updated_at` = CURRENT_TIMESTAMP;
