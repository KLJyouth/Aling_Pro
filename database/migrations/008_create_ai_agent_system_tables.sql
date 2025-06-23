-- AI代理系统数据库表结构
-- 支持智能代理协调平台和自学习框架

-- AI代理表
CREATE TABLE IF NOT EXISTS `ai_agents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(100) NOT NULL UNIQUE COMMENT '代理唯一标识',
  `type` varchar(50) NOT NULL COMMENT '代理类型 (chat, analysis, security, etc.)',
  `status` varchar(20) NOT NULL DEFAULT 'idle' COMMENT '代理状态 (idle, busy, error, maintenance, offline)',
  `config` json DEFAULT NULL COMMENT '代理配置参数',
  `capabilities` json DEFAULT NULL COMMENT '代理能力描述',
  `performance_metrics` json DEFAULT NULL COMMENT '性能指标',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_heartbeat` timestamp NULL DEFAULT NULL COMMENT '最后心跳时间',
  PRIMARY KEY (`id`),
  KEY `idx_agent_id` (`agent_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI智能代理表';

-- AI任务表
CREATE TABLE IF NOT EXISTS `ai_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` varchar(100) NOT NULL UNIQUE COMMENT '任务唯一标识',
  `agent_id` varchar(100) NOT NULL COMMENT '分配的代理ID',
  `task_type` varchar(50) NOT NULL COMMENT '任务类型',
  `task_data` json NOT NULL COMMENT '任务数据',
  `priority` tinyint(1) NOT NULL DEFAULT 2 COMMENT '任务优先级 (1-5)',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '任务状态 (pending, assigned, processing, completed, failed)',
  `result` json DEFAULT NULL COMMENT '任务结果',
  `error_message` text DEFAULT NULL COMMENT '错误信息',
  `retry_count` int(11) NOT NULL DEFAULT 0 COMMENT '重试次数',
  `timeout_seconds` int(11) NOT NULL DEFAULT 300 COMMENT '超时时间(秒)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL COMMENT '开始执行时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_agent_id` (`agent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`agent_id`) REFERENCES `ai_agents`(`agent_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI任务表';

-- AI知识库表
CREATE TABLE IF NOT EXISTS `ai_knowledge_base` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL COMMENT '知识类型 (performance, security, user_behavior, etc.)',
  `pattern_data` json NOT NULL COMMENT '模式数据',
  `confidence_score` decimal(3,2) NOT NULL DEFAULT 0.00 COMMENT '置信度分数 (0.00-1.00)',
  `source` varchar(100) DEFAULT NULL COMMENT '数据来源',
  `tags` json DEFAULT NULL COMMENT '标签',
  `version` int(11) NOT NULL DEFAULT 1 COMMENT '版本号',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_confidence_score` (`confidence_score`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI知识库表';

-- AI学习数据表
CREATE TABLE IF NOT EXISTS `ai_learning_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data_type` varchar(50) NOT NULL COMMENT '数据类型',
  `raw_data` json NOT NULL COMMENT '原始数据',
  `processed_data` json DEFAULT NULL COMMENT '处理后数据',
  `features` json DEFAULT NULL COMMENT '提取的特征',
  `labels` json DEFAULT NULL COMMENT '标签',
  `quality_score` decimal(3,2) NOT NULL DEFAULT 0.00 COMMENT '数据质量分数',
  `source_agent_id` varchar(100) DEFAULT NULL COMMENT '来源代理ID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL COMMENT '处理时间',
  PRIMARY KEY (`id`),
  KEY `idx_data_type` (`data_type`),
  KEY `idx_quality_score` (`quality_score`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_source_agent_id` (`source_agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI学习数据表';

-- AI模型表
CREATE TABLE IF NOT EXISTS `ai_models` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL COMMENT '模型名称',
  `model_type` varchar(50) NOT NULL COMMENT '模型类型 (neural_network, decision_tree, etc.)',
  `model_data` longtext NOT NULL COMMENT '模型数据 (序列化)',
  `accuracy` decimal(5,4) NOT NULL DEFAULT 0.0000 COMMENT '准确率',
  `training_data_size` int(11) NOT NULL DEFAULT 0 COMMENT '训练数据大小',
  `training_iterations` int(11) NOT NULL DEFAULT 0 COMMENT '训练迭代次数',
  `hyperparameters` json DEFAULT NULL COMMENT '超参数',
  `performance_metrics` json DEFAULT NULL COMMENT '性能指标',
  `version` varchar(20) NOT NULL DEFAULT '1.0' COMMENT '模型版本',
  `status` varchar(20) NOT NULL DEFAULT 'training' COMMENT '状态 (training, ready, deprecated)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `trained_at` timestamp NULL DEFAULT NULL COMMENT '训练完成时间',
  PRIMARY KEY (`id`),
  KEY `idx_model_name` (`model_name`),
  KEY `idx_model_type` (`model_type`),
  KEY `idx_accuracy` (`accuracy`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI模型表';

-- AI系统配置表
CREATE TABLE IF NOT EXISTS `ai_system_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL UNIQUE COMMENT '配置键',
  `config_value` json NOT NULL COMMENT '配置值',
  `config_type` varchar(50) NOT NULL COMMENT '配置类型 (coordinator, learning, agent, etc.)',
  `description` text DEFAULT NULL COMMENT '配置描述',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(100) DEFAULT NULL COMMENT '创建者',
  PRIMARY KEY (`id`),
  KEY `idx_config_key` (`config_key`),
  KEY `idx_config_type` (`config_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI系统配置表';

-- AI性能指标表
CREATE TABLE IF NOT EXISTS `ai_performance_metrics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `metric_type` varchar(50) NOT NULL COMMENT '指标类型 (response_time, accuracy, throughput, etc.)',
  `entity_type` varchar(20) NOT NULL COMMENT '实体类型 (agent, task, system)',
  `entity_id` varchar(100) NOT NULL COMMENT '实体ID',
  `metric_value` decimal(10,4) NOT NULL COMMENT '指标值',
  `metric_unit` varchar(20) DEFAULT NULL COMMENT '指标单位',
  `additional_data` json DEFAULT NULL COMMENT '附加数据',
  `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `idx_metric_type` (`metric_type`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_recorded_at` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI性能指标表';

-- AI学习日志表
CREATE TABLE IF NOT EXISTS `ai_learning_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varchar(50) NOT NULL COMMENT '日志类型 (training, adaptation, optimization, etc.)',
  `agent_id` varchar(100) DEFAULT NULL COMMENT '相关代理ID',
  `event_description` text NOT NULL COMMENT '事件描述',
  `input_data` json DEFAULT NULL COMMENT '输入数据',
  `output_data` json DEFAULT NULL COMMENT '输出数据',
  `success` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否成功',
  `error_message` text DEFAULT NULL COMMENT '错误信息',
  `duration_ms` int(11) DEFAULT NULL COMMENT '执行时长(毫秒)',
  `learning_impact` decimal(3,2) DEFAULT NULL COMMENT '学习影响分数',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_type` (`log_type`),
  KEY `idx_agent_id` (`agent_id`),
  KEY `idx_success` (`success`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI学习日志表';

-- AI代理通信表
CREATE TABLE IF NOT EXISTS `ai_agent_communications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_agent_id` varchar(100) NOT NULL COMMENT '发送方代理ID',
  `receiver_agent_id` varchar(100) NOT NULL COMMENT '接收方代理ID',
  `message_type` varchar(50) NOT NULL COMMENT '消息类型 (request, response, notification, etc.)',
  `message_content` json NOT NULL COMMENT '消息内容',
  `priority` tinyint(1) NOT NULL DEFAULT 2 COMMENT '消息优先级',
  `status` varchar(20) NOT NULL DEFAULT 'sent' COMMENT '状态 (sent, delivered, processed, failed)',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `response_data` json DEFAULT NULL COMMENT '响应数据',
  PRIMARY KEY (`id`),
  KEY `idx_sender_agent` (`sender_agent_id`),
  KEY `idx_receiver_agent` (`receiver_agent_id`),
  KEY `idx_message_type` (`message_type`),
  KEY `idx_status` (`status`),
  KEY `idx_sent_at` (`sent_at`),
  FOREIGN KEY (`sender_agent_id`) REFERENCES `ai_agents`(`agent_id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_agent_id`) REFERENCES `ai_agents`(`agent_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI代理通信表';

-- 插入默认系统配置
INSERT INTO `ai_system_config` (`config_key`, `config_value`, `config_type`, `description`, `created_by`) VALUES
('coordinator.max_agents_per_type', '5', 'coordinator', '每种类型代理的最大数量', 'system'),
('coordinator.task_timeout', '300', 'coordinator', '任务超时时间(秒)', 'system'),
('coordinator.health_check_interval', '60', 'coordinator', '健康检查间隔(秒)', 'system'),
('coordinator.load_balancing_algorithm', '"round_robin"', 'coordinator', '负载均衡算法', 'system'),
('coordinator.auto_scaling_enabled', 'true', 'coordinator', '是否启用自动扩缩容', 'system'),
('coordinator.failover_enabled', 'true', 'coordinator', '是否启用故障转移', 'system'),
('learning.learning_interval', '3600', 'learning', '学习循环间隔(秒)', 'system'),
('learning.adaptation_threshold', '0.75', 'learning', '自适应阈值', 'system'),
('learning.min_data_points', '100', 'learning', '最小数据点数量', 'system'),
('learning.max_learning_iterations', '1000', 'learning', '最大学习迭代次数', 'system'),
('learning.confidence_threshold', '0.8', 'learning', '置信度阈值', 'system'),
('learning.auto_repair_enabled', 'true', 'learning', '是否启用自动修复', 'system'),
('agent.max_concurrent_tasks', '3', 'agent', '代理最大并发任务数', 'system'),
('agent.response_timeout', '120', 'agent', '代理响应超时时间(秒)', 'system'),
('agent.retry_attempts', '2', 'agent', '代理任务重试次数', 'system'),
('agent.learning_enabled', 'true', 'agent', '是否启用代理学习', 'system')
ON DUPLICATE KEY UPDATE 
  `config_value` = VALUES(`config_value`),
  `updated_at` = CURRENT_TIMESTAMP;

-- 创建视图：代理性能统计
CREATE OR REPLACE VIEW `ai_agent_performance_stats` AS
SELECT 
    a.agent_id,
    a.type,
    a.status,
    COUNT(t.id) as total_tasks,
    COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks,
    COUNT(CASE WHEN t.status = 'failed' THEN 1 END) as failed_tasks,
    COALESCE(COUNT(CASE WHEN t.status = 'completed' THEN 1 END) / NULLIF(COUNT(t.id), 0) * 100, 0) as success_rate,
    AVG(CASE WHEN t.status = 'completed' THEN TIMESTAMPDIFF(SECOND, t.started_at, t.completed_at) END) as avg_processing_time,
    MAX(t.created_at) as last_task_time,
    a.created_at as agent_created_at,
    a.last_heartbeat
FROM ai_agents a
LEFT JOIN ai_tasks t ON a.agent_id = t.agent_id
GROUP BY a.agent_id, a.type, a.status, a.created_at, a.last_heartbeat;

-- 创建视图：学习效果统计
CREATE OR REPLACE VIEW `ai_learning_effectiveness` AS
SELECT 
    kb.type as knowledge_type,
    COUNT(kb.id) as total_patterns,
    AVG(kb.confidence_score) as avg_confidence,
    COUNT(CASE WHEN kb.confidence_score >= 0.8 THEN 1 END) as high_confidence_patterns,
    COUNT(ld.id) as total_learning_data,
    AVG(ld.quality_score) as avg_data_quality,
    COUNT(m.id) as trained_models,
    AVG(m.accuracy) as avg_model_accuracy,
    MAX(kb.updated_at) as last_learning_update
FROM ai_knowledge_base kb
LEFT JOIN ai_learning_data ld ON kb.type = ld.data_type
LEFT JOIN ai_models m ON kb.type = m.model_type
GROUP BY kb.type;

-- 创建存储过程：清理过期数据
DELIMITER //
CREATE PROCEDURE CleanupExpiredAIData()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- 清理过期的知识库数据
    DELETE FROM ai_knowledge_base 
    WHERE expires_at IS NOT NULL AND expires_at < NOW();
    
    -- 清理超过30天的已完成任务
    DELETE FROM ai_tasks 
    WHERE status IN ('completed', 'failed') 
    AND completed_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- 清理超过7天的性能指标数据
    DELETE FROM ai_performance_metrics 
    WHERE recorded_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    -- 清理超过30天的学习日志
    DELETE FROM ai_learning_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- 清理超过7天的代理通信记录
    DELETE FROM ai_agent_communications 
    WHERE sent_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    COMMIT;
END //
DELIMITER ;

-- 创建事件调度器：自动清理数据（每天凌晨3点执行）
-- 注意：需要确保事件调度器已启用 (SET GLOBAL event_scheduler = ON;)
/*
CREATE EVENT IF NOT EXISTS cleanup_ai_data_event
ON SCHEDULE EVERY 1 DAY
STARTS '2024-01-01 03:00:00'
DO
  CALL CleanupExpiredAIData();
*/
