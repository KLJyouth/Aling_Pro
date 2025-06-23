-- API监控中心数据库表结构
-- 创建于 2025-06-25

-- API调用记录表
CREATE TABLE IF NOT EXISTS `api_calls` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` VARCHAR(64) NOT NULL COMMENT '请求唯一ID',
  `path` VARCHAR(255) NOT NULL COMMENT 'API路径',
  `method` VARCHAR(10) NOT NULL COMMENT '请求方法',
  `status_code` INT NOT NULL DEFAULT 200 COMMENT '状态码',
  `user_id` BIGINT UNSIGNED NULL COMMENT '用户ID，非用户请求为NULL',
  `user_type` VARCHAR(20) NULL COMMENT '用户类型: user/admin/system',
  `ip_address` VARCHAR(45) NOT NULL COMMENT '请求IP地址',
  `user_agent` VARCHAR(255) NULL COMMENT '用户代理',
  `referer` VARCHAR(255) NULL COMMENT '来源页面',
  `request_data` TEXT NULL COMMENT '请求数据(JSON)',
  `response_data` TEXT NULL COMMENT '响应摘要(JSON)',
  `error_message` TEXT NULL COMMENT '错误信息',
  `processing_time` FLOAT(10,6) NOT NULL COMMENT '处理时间(秒)',
  `request_size` INT UNSIGNED NULL COMMENT '请求大小(字节)',
  `response_size` INT UNSIGNED NULL COMMENT '响应大小(字节)',
  `is_encrypted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否加密',
  `encryption_type` VARCHAR(20) NULL COMMENT '加密类型',
  `is_authenticated` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否认证',
  `auth_type` VARCHAR(20) NULL COMMENT '认证类型',
  `session_id` VARCHAR(64) NULL COMMENT '会话ID',
  `device_info` VARCHAR(255) NULL COMMENT '设备信息',
  `device_type` VARCHAR(20) NULL COMMENT '设备类型: desktop/mobile/tablet/api',
  `os_info` VARCHAR(100) NULL COMMENT '操作系统信息',
  `browser_info` VARCHAR(100) NULL COMMENT '浏览器信息',
  `country` VARCHAR(50) NULL COMMENT '国家',
  `region` VARCHAR(50) NULL COMMENT '地区',
  `city` VARCHAR(50) NULL COMMENT '城市',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_api_calls_request_id` (`request_id`),
  INDEX `idx_api_calls_path` (`path`),
  INDEX `idx_api_calls_user_id` (`user_id`),
  INDEX `idx_api_calls_status_code` (`status_code`),
  INDEX `idx_api_calls_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API性能指标表
CREATE TABLE IF NOT EXISTS `api_performance_metrics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_call_id` BIGINT UNSIGNED NOT NULL COMMENT 'API调用ID',
  `cpu_usage` FLOAT NULL COMMENT 'CPU使用率',
  `memory_usage` FLOAT NULL COMMENT '内存使用(MB)',
  `db_query_count` INT UNSIGNED NULL COMMENT '数据库查询次数',
  `db_query_time` FLOAT NULL COMMENT '数据库查询时间(秒)',
  `cache_hit_count` INT UNSIGNED NULL COMMENT '缓存命中次数',
  `cache_miss_count` INT UNSIGNED NULL COMMENT '缓存未命中次数',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_metrics_api_call` FOREIGN KEY (`api_call_id`) REFERENCES `api_calls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API安全事件表
CREATE TABLE IF NOT EXISTS `api_security_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_call_id` BIGINT UNSIGNED NULL COMMENT 'API调用ID',
  `event_type` VARCHAR(50) NOT NULL COMMENT '事件类型',
  `severity` VARCHAR(20) NOT NULL COMMENT '严重性: low/medium/high/critical',
  `description` TEXT NOT NULL COMMENT '事件描述',
  `details` TEXT NULL COMMENT '详细信息(JSON)',
  `is_resolved` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否已解决',
  `resolved_at` TIMESTAMP NULL COMMENT '解决时间',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_security_event_type` (`event_type`),
  INDEX `idx_security_severity` (`severity`),
  INDEX `idx_security_created_at` (`created_at`),
  CONSTRAINT `fk_security_api_call` FOREIGN KEY (`api_call_id`) REFERENCES `api_calls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API调用关系表
CREATE TABLE IF NOT EXISTS `api_call_relationships` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_call_id` BIGINT UNSIGNED NOT NULL COMMENT '父调用ID',
  `child_call_id` BIGINT UNSIGNED NOT NULL COMMENT '子调用ID',
  `relationship_type` VARCHAR(50) NOT NULL COMMENT '关系类型',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_parent_child_call` (`parent_call_id`, `child_call_id`),
  CONSTRAINT `fk_relationship_parent` FOREIGN KEY (`parent_call_id`) REFERENCES `api_calls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_relationship_child` FOREIGN KEY (`child_call_id`) REFERENCES `api_calls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API分析统计表
CREATE TABLE IF NOT EXISTS `api_analytics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` VARCHAR(255) NOT NULL COMMENT 'API路径',
  `date` DATE NOT NULL COMMENT '统计日期',
  `hour` TINYINT UNSIGNED NULL COMMENT '小时(0-23)',
  `total_calls` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '总调用次数',
  `success_calls` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '成功调用次数',
  `error_calls` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '错误调用次数',
  `avg_response_time` FLOAT NOT NULL DEFAULT 0 COMMENT '平均响应时间(秒)',
  `min_response_time` FLOAT NOT NULL DEFAULT 0 COMMENT '最小响应时间(秒)',
  `max_response_time` FLOAT NOT NULL DEFAULT 0 COMMENT '最大响应时间(秒)',
  `total_data_transferred` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '总数据传输量(字节)',
  `unique_users` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '唯一用户数',
  `unique_ips` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '唯一IP数',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_api_analytics_path_date_hour` (`path`, `date`, `hour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API异常警报配置表
CREATE TABLE IF NOT EXISTS `api_alert_configurations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT '警报名称',
  `path` VARCHAR(255) NULL COMMENT 'API路径(NULL表示全部)',
  `condition_type` VARCHAR(50) NOT NULL COMMENT '条件类型',
  `condition_value` VARCHAR(255) NOT NULL COMMENT '条件值',
  `severity` VARCHAR(20) NOT NULL COMMENT '严重性: low/medium/high/critical',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否激活',
  `notification_channels` VARCHAR(255) NOT NULL COMMENT '通知渠道(逗号分隔)',
  `notification_interval` INT NOT NULL DEFAULT 300 COMMENT '通知间隔(秒)',
  `created_by` BIGINT UNSIGNED NOT NULL COMMENT '创建者ID',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API异常警报记录表
CREATE TABLE IF NOT EXISTS `api_alerts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alert_config_id` BIGINT UNSIGNED NOT NULL COMMENT '警报配置ID',
  `api_call_id` BIGINT UNSIGNED NULL COMMENT '触发警报的API调用ID',
  `message` TEXT NOT NULL COMMENT '警报消息',
  `details` TEXT NULL COMMENT '详细信息(JSON)',
  `is_acknowledged` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否已确认',
  `acknowledged_by` BIGINT UNSIGNED NULL COMMENT '确认者ID',
  `acknowledged_at` TIMESTAMP NULL COMMENT '确认时间',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_alert_config_id` (`alert_config_id`),
  CONSTRAINT `fk_alert_config` FOREIGN KEY (`alert_config_id`) REFERENCES `api_alert_configurations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alert_api_call` FOREIGN KEY (`api_call_id`) REFERENCES `api_calls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SQLite版本的表结构
-- 为了兼容开发环境使用的SQLite

CREATE TABLE IF NOT EXISTS "api_calls" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "request_id" TEXT NOT NULL,
  "path" TEXT NOT NULL,
  "method" TEXT NOT NULL,
  "status_code" INTEGER NOT NULL DEFAULT 200,
  "user_id" INTEGER NULL,
  "user_type" TEXT NULL,
  "ip_address" TEXT NOT NULL,
  "user_agent" TEXT NULL,
  "referer" TEXT NULL,
  "request_data" TEXT NULL,
  "response_data" TEXT NULL,
  "error_message" TEXT NULL,
  "processing_time" REAL NOT NULL,
  "request_size" INTEGER NULL,
  "response_size" INTEGER NULL,
  "is_encrypted" INTEGER NOT NULL DEFAULT 0,
  "encryption_type" TEXT NULL,
  "is_authenticated" INTEGER NOT NULL DEFAULT 0,
  "auth_type" TEXT NULL,
  "session_id" TEXT NULL,
  "device_info" TEXT NULL,
  "device_type" TEXT NULL,
  "os_info" TEXT NULL,
  "browser_info" TEXT NULL,
  "country" TEXT NULL,
  "region" TEXT NULL,
  "city" TEXT NULL,
  "created_at" TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS "idx_api_calls_request_id" ON "api_calls" ("request_id");
CREATE INDEX IF NOT EXISTS "idx_api_calls_path" ON "api_calls" ("path");
CREATE INDEX IF NOT EXISTS "idx_api_calls_user_id" ON "api_calls" ("user_id");
CREATE INDEX IF NOT EXISTS "idx_api_calls_status_code" ON "api_calls" ("status_code");
CREATE INDEX IF NOT EXISTS "idx_api_calls_created_at" ON "api_calls" ("created_at");

-- 其余SQLite表结构...（此处省略其他表的SQLite版本，实际使用时应完整实现） 