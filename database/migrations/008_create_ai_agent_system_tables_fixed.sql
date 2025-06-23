-- 修复版本的AI代理系统数据库表结构
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
  KEY `idx_created_at` (`created_at`)
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_model_version` (`model_name`, `version`),
  KEY `idx_model_name` (`model_name`),
  KEY `idx_model_type` (`model_type`),
  KEY `idx_status` (`status`),
  KEY `idx_accuracy` (`accuracy`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI模型表';

-- AI执行历史表
CREATE TABLE IF NOT EXISTS `ai_execution_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `execution_id` varchar(100) NOT NULL UNIQUE COMMENT '执行唯一标识',
  `agent_id` varchar(100) NOT NULL COMMENT '代理ID',
  `task_id` varchar(100) DEFAULT NULL COMMENT '任务ID',
  `execution_type` varchar(50) NOT NULL COMMENT '执行类型',
  `input_data` json DEFAULT NULL COMMENT '输入数据',
  `output_data` json DEFAULT NULL COMMENT '输出数据',
  `execution_time` int(11) NOT NULL DEFAULT 0 COMMENT '执行时间(毫秒)',
  `memory_usage` int(11) DEFAULT NULL COMMENT '内存使用(字节)',
  `cpu_usage` decimal(5,2) DEFAULT NULL COMMENT 'CPU使用率',
  `status` varchar(20) NOT NULL DEFAULT 'completed' COMMENT '执行状态',
  `error_details` text DEFAULT NULL COMMENT '错误详情',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_execution_id` (`execution_id`),
  KEY `idx_agent_id` (`agent_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_execution_type` (`execution_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI执行历史表';

-- 威胁情报表
CREATE TABLE IF NOT EXISTS `threat_intelligence` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `threat_id` varchar(100) NOT NULL UNIQUE COMMENT '威胁唯一标识',
  `threat_type` varchar(50) NOT NULL COMMENT '威胁类型',
  `source_ip` varchar(45) NOT NULL COMMENT '来源IP',
  `target_ip` varchar(45) DEFAULT NULL COMMENT '目标IP',
  `country_code` varchar(2) DEFAULT NULL COMMENT '国家代码',
  `latitude` decimal(10,7) DEFAULT NULL COMMENT '纬度',
  `longitude` decimal(10,7) DEFAULT NULL COMMENT '经度',
  `severity_level` tinyint(1) NOT NULL DEFAULT 1 COMMENT '严重级别 (1-5)',
  `attack_vector` varchar(100) DEFAULT NULL COMMENT '攻击向量',
  `threat_data` json DEFAULT NULL COMMENT '威胁详细数据',
  `detection_method` varchar(50) DEFAULT NULL COMMENT '检测方法',
  `confidence_level` decimal(3,2) NOT NULL DEFAULT 0.50 COMMENT '置信度',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态 (active, mitigated, false_positive)',
  `first_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '首次发现时间',
  `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后发现时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_threat_id` (`threat_id`),
  KEY `idx_threat_type` (`threat_type`),
  KEY `idx_source_ip` (`source_ip`),
  KEY `idx_severity_level` (`severity_level`),
  KEY `idx_status` (`status`),
  KEY `idx_first_seen` (`first_seen`),
  KEY `idx_geo_location` (`latitude`, `longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='威胁情报表';

-- 防御措施表  
CREATE TABLE IF NOT EXISTS `defense_measures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `measure_id` varchar(100) NOT NULL UNIQUE COMMENT '措施唯一标识',
  `threat_id` varchar(100) NOT NULL COMMENT '关联威胁ID',
  `measure_type` varchar(50) NOT NULL COMMENT '防御措施类型',
  `measure_data` json NOT NULL COMMENT '防御措施详细数据',
  `effectiveness_score` decimal(3,2) NOT NULL DEFAULT 0.00 COMMENT '有效性评分',
  `implementation_status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '实施状态',
  `auto_generated` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否自动生成',
  `ai_recommendation` text DEFAULT NULL COMMENT 'AI推荐说明',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `implemented_at` timestamp NULL DEFAULT NULL COMMENT '实施时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_measure_id` (`measure_id`),
  KEY `idx_threat_id` (`threat_id`),
  KEY `idx_measure_type` (`measure_type`),
  KEY `idx_implementation_status` (`implementation_status`),
  KEY `idx_effectiveness_score` (`effectiveness_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='防御措施表';

-- 添加一些初始数据
INSERT IGNORE INTO `ai_agents` (`agent_id`, `type`, `status`, `capabilities`) VALUES
('deepseek_chat', 'chat', 'active', '["conversation", "analysis", "task_coordination"]'),
('security_scanner', 'security', 'active', '["vulnerability_scan", "threat_detection", "log_analysis"]'),
('data_analyzer', 'analysis', 'active', '["data_processing", "pattern_recognition", "reporting"]'),
('ppt_generator', 'content_generation', 'active', '["ppt_creation", "slide_design", "content_structuring"]');

-- 创建索引优化
CREATE INDEX `idx_ai_tasks_status_priority` ON `ai_tasks` (`status`, `priority`, `created_at`);
CREATE INDEX `idx_threat_geo_severity` ON `threat_intelligence` (`latitude`, `longitude`, `severity_level`);
CREATE INDEX `idx_defense_threat_status` ON `defense_measures` (`threat_id`, `implementation_status`);
