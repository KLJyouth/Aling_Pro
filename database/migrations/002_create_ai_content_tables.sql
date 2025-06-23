-- AI内容管理相关表迁移脚本
-- 适用于MySQL 5.7.43+
-- 支持多语言AI内容管理、智能分析、内容生成等功能

-- 创建AI内容表
CREATE TABLE IF NOT EXISTS `ai_contents` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '内容ID',
    `user_id` int(11) unsigned NOT NULL COMMENT '创建用户ID',
    `title` varchar(255) NOT NULL COMMENT '内容标题',
    `content` longtext DEFAULT NULL COMMENT '内容正文',
    `content_type` varchar(50) DEFAULT 'text' COMMENT '内容类型：text, image, video, audio, mixed',
    `ai_model` varchar(100) DEFAULT NULL COMMENT 'AI模型名称',
    `prompt` text DEFAULT NULL COMMENT '生成提示词',
    `language` varchar(10) DEFAULT 'zh-cn' COMMENT '内容语言',
    `category_id` int(11) unsigned DEFAULT NULL COMMENT '分类ID',
    `tags` json DEFAULT NULL COMMENT '标签数组',
    `status` tinyint(1) DEFAULT 1 COMMENT '状态：0-草稿，1-已发布，2-已删除，3-审核中',
    `is_featured` tinyint(1) DEFAULT 0 COMMENT '是否推荐',
    `view_count` int(11) unsigned DEFAULT 0 COMMENT '查看次数',
    `like_count` int(11) unsigned DEFAULT 0 COMMENT '点赞次数',
    `share_count` int(11) unsigned DEFAULT 0 COMMENT '分享次数',
    `comment_count` int(11) unsigned DEFAULT 0 COMMENT '评论次数',
    `quality_score` decimal(3,2) DEFAULT 0.00 COMMENT '质量评分(0-10)',
    `ai_analysis` json DEFAULT NULL COMMENT 'AI分析结果',
    `metadata` json DEFAULT NULL COMMENT '扩展元数据',
    `thumbnail` varchar(500) DEFAULT NULL COMMENT '缩略图URL',
    `published_at` timestamp NULL DEFAULT NULL COMMENT '发布时间',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_status` (`status`),
    KEY `idx_language` (`language`),
    KEY `idx_content_type` (`content_type`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_published_at` (`published_at`),
    KEY `idx_deleted_at` (`deleted_at`),
    KEY `idx_quality_score` (`quality_score`),
    FULLTEXT KEY `ft_title_content` (`title`, `content`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI内容表';

-- 创建内容分类表
CREATE TABLE IF NOT EXISTS `content_categories` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
    `parent_id` int(11) unsigned DEFAULT NULL COMMENT '父分类ID',
    `name` varchar(100) NOT NULL COMMENT '分类名称',
    `slug` varchar(100) NOT NULL COMMENT '分类别名',
    `description` text DEFAULT NULL COMMENT '分类描述',
    `icon` varchar(100) DEFAULT NULL COMMENT '分类图标',
    `color` varchar(7) DEFAULT NULL COMMENT '分类颜色',
    `sort_order` int(11) DEFAULT 0 COMMENT '排序权重',
    `is_active` tinyint(1) DEFAULT 1 COMMENT '是否启用',
    `content_count` int(11) unsigned DEFAULT 0 COMMENT '内容数量',
    `language` varchar(10) DEFAULT 'zh-cn' COMMENT '分类语言',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug_language` (`slug`, `language`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_sort_order` (`sort_order`),
    KEY `idx_language` (`language`),
    FOREIGN KEY (`parent_id`) REFERENCES `content_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='内容分类表';

-- 为内容表添加外键约束
ALTER TABLE `ai_contents` ADD FOREIGN KEY (`category_id`) REFERENCES `content_categories` (`id`) ON DELETE SET NULL;

-- 创建AI任务表
CREATE TABLE IF NOT EXISTS `ai_tasks` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
    `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
    `task_type` varchar(50) NOT NULL COMMENT '任务类型：generate, analyze, translate, summarize, etc.',
    `task_name` varchar(255) NOT NULL COMMENT '任务名称',
    `input_data` json DEFAULT NULL COMMENT '输入数据',
    `output_data` json DEFAULT NULL COMMENT '输出数据',
    `ai_model` varchar(100) DEFAULT NULL COMMENT 'AI模型',
    `parameters` json DEFAULT NULL COMMENT '任务参数',
    `status` varchar(20) DEFAULT 'pending' COMMENT '任务状态：pending, running, completed, failed, cancelled',
    `progress` tinyint(3) unsigned DEFAULT 0 COMMENT '进度百分比(0-100)',
    `priority` tinyint(1) DEFAULT 5 COMMENT '优先级(1-10)',
    `estimated_time` int(11) unsigned DEFAULT NULL COMMENT '预计耗时(秒)',
    `actual_time` int(11) unsigned DEFAULT NULL COMMENT '实际耗时(秒)',
    `error_message` text DEFAULT NULL COMMENT '错误信息',
    `retry_count` tinyint(3) unsigned DEFAULT 0 COMMENT '重试次数',
    `max_retries` tinyint(3) unsigned DEFAULT 3 COMMENT '最大重试次数',
    `started_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
    `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_task_type` (`task_type`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI任务表';

-- 创建AI模型配置表
CREATE TABLE IF NOT EXISTS `ai_models` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '模型ID',
    `name` varchar(100) NOT NULL COMMENT '模型名称',
    `provider` varchar(50) NOT NULL COMMENT '提供商：openai, claude, gemini, etc.',
    `model_id` varchar(100) NOT NULL COMMENT '模型标识',
    `description` text DEFAULT NULL COMMENT '模型描述',
    `capabilities` json DEFAULT NULL COMMENT '模型能力',
    `pricing` json DEFAULT NULL COMMENT '价格信息',
    `limits` json DEFAULT NULL COMMENT '使用限制',
    `api_config` json DEFAULT NULL COMMENT 'API配置',
    `is_active` tinyint(1) DEFAULT 1 COMMENT '是否启用',
    `is_default` tinyint(1) DEFAULT 0 COMMENT '是否默认模型',
    `version` varchar(20) DEFAULT NULL COMMENT '模型版本',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_provider_model` (`provider`, `model_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI模型配置表';

-- 创建用户AI使用统计表
CREATE TABLE IF NOT EXISTS `user_ai_usage` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '统计ID',
    `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
    `model_id` int(11) unsigned NOT NULL COMMENT '模型ID',
    `usage_date` date NOT NULL COMMENT '使用日期',
    `request_count` int(11) unsigned DEFAULT 0 COMMENT '请求次数',
    `token_used` int(11) unsigned DEFAULT 0 COMMENT '消耗token数',
    `cost` decimal(10,4) DEFAULT 0.0000 COMMENT '费用',
    `success_count` int(11) unsigned DEFAULT 0 COMMENT '成功次数',
    `error_count` int(11) unsigned DEFAULT 0 COMMENT '错误次数',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_model_date` (`user_id`, `model_id`, `usage_date`),
    KEY `idx_usage_date` (`usage_date`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户AI使用统计表';

-- 插入默认分类数据
INSERT INTO `content_categories` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`, `language`) VALUES
('文本生成', 'text-generation', 'AI文本内容生成', 'edit', '#4CAF50', 1, 'zh-cn'),
('图像生成', 'image-generation', 'AI图像内容生成', 'image', '#2196F3', 2, 'zh-cn'),
('内容分析', 'content-analysis', 'AI内容智能分析', 'analytics', '#FF9800', 3, 'zh-cn'),
('语言翻译', 'translation', '多语言智能翻译', 'translate', '#9C27B0', 4, 'zh-cn'),
('智能总结', 'summarization', '内容智能总结', 'summarize', '#F44336', 5, 'zh-cn'),
('问答对话', 'qa-dialogue', 'AI问答对话', 'chat', '#00BCD4', 6, 'zh-cn');

-- 插入默认AI模型配置
INSERT INTO `ai_models` (`name`, `provider`, `model_id`, `description`, `capabilities`, `is_active`, `is_default`) VALUES
('GPT-4', 'openai', 'gpt-4', '最先进的大语言模型', JSON_ARRAY('text_generation', 'analysis', 'translation', 'qa'), 1, 1),
('GPT-3.5 Turbo', 'openai', 'gpt-3.5-turbo', '高效的对话模型', JSON_ARRAY('text_generation', 'translation', 'qa'), 1, 0),
('Claude-3', 'anthropic', 'claude-3-opus', '强大的推理能力模型', JSON_ARRAY('text_generation', 'analysis', 'translation'), 1, 0),
('Gemini Pro', 'google', 'gemini-pro', '谷歌最新大语言模型', JSON_ARRAY('text_generation', 'analysis', 'multimodal'), 1, 0);
