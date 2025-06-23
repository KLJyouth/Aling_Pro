-- 实时网络安全监控数据库架构
-- 创建安全监控相关表

-- 安全日志表 (用于存储所有安全事件)
CREATE TABLE IF NOT EXISTS security_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    request_url TEXT,
    request_method VARCHAR(10),
    user_agent TEXT,
    referer TEXT,
    status_code INT,
    response_size BIGINT,
    response_time FLOAT,
    threat_level INT DEFAULT 0,
    threat_type VARCHAR(50),
    threat_score FLOAT DEFAULT 0,
    risk_factors JSON,
    country VARCHAR(100),
    region VARCHAR(100),
    city VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    is_blocked BOOLEAN DEFAULT FALSE,
    blocked_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_ip (ip),
    INDEX idx_threat_level (threat_level),
    INDEX idx_threat_type (threat_type),
    INDEX idx_created_at (created_at),
    INDEX idx_country (country),
    INDEX idx_is_blocked (is_blocked)
);

-- 威胁检测记录表
CREATE TABLE IF NOT EXISTS threat_detections (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    threat_id VARCHAR(100) UNIQUE NOT NULL,
    source_ip VARCHAR(45) NOT NULL,
    target_ip VARCHAR(45),
    threat_type VARCHAR(50) NOT NULL,
    threat_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    attack_vector VARCHAR(100),
    payload_size BIGINT,
    threat_score FLOAT NOT NULL,
    attack_signature TEXT,
    mitigation_action VARCHAR(100),
    is_mitigated BOOLEAN DEFAULT FALSE,
    detection_engine VARCHAR(50),
    confidence_score FLOAT,
    false_positive BOOLEAN DEFAULT FALSE,
    investigation_status ENUM('pending', 'investigating', 'resolved', 'false_positive') DEFAULT 'pending',
    analyst_notes TEXT,
    source_country VARCHAR(100),
    target_country VARCHAR(100),
    attack_duration INT, -- 攻击持续时间(秒)
    packets_count BIGINT,
    bytes_transferred BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    
    INDEX idx_source_ip (source_ip),
    INDEX idx_threat_type (threat_type),
    INDEX idx_threat_level (threat_level),
    INDEX idx_created_at (created_at),
    INDEX idx_investigation_status (investigation_status),
    INDEX idx_source_country (source_country)
);

-- IP黑名单表 (扩展)
CREATE TABLE IF NOT EXISTS security_blacklist (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    ip_range VARCHAR(100), -- CIDR格式的IP段
    blacklist_type ENUM('manual', 'auto', 'intelligence', 'reputation') NOT NULL,
    threat_category VARCHAR(50),
    severity_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    block_reason TEXT NOT NULL,
    source VARCHAR(100), -- 来源(如威胁情报提供商)
    confidence_score FLOAT DEFAULT 0,
    hit_count INT DEFAULT 0,
    last_hit_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_permanent BOOLEAN DEFAULT FALSE,
    whitelist_override BOOLEAN DEFAULT FALSE,
    added_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_ip (ip),
    INDEX idx_expires_at (expires_at),
    INDEX idx_blacklist_type (blacklist_type),
    INDEX idx_severity_level (severity_level),
    INDEX idx_threat_category (threat_category)
);

-- 实时网络流量统计表
CREATE TABLE IF NOT EXISTS network_traffic_stats (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_requests BIGINT DEFAULT 0,
    total_bytes BIGINT DEFAULT 0,
    unique_ips INT DEFAULT 0,
    blocked_requests BIGINT DEFAULT 0,
    threat_requests BIGINT DEFAULT 0,
    average_response_time FLOAT DEFAULT 0,
    peak_requests_per_minute INT DEFAULT 0,
    active_connections INT DEFAULT 0,
    bandwidth_usage_mbps FLOAT DEFAULT 0,
    top_countries JSON, -- 前10个国家的访问统计
    top_threat_types JSON, -- 前10个威胁类型
    attack_vectors_distribution JSON, -- 攻击向量分布
    
    INDEX idx_timestamp (timestamp)
);

-- 地理威胁分布表
CREATE TABLE IF NOT EXISTS geo_threat_distribution (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    country_code VARCHAR(2) NOT NULL,
    country_name VARCHAR(100) NOT NULL,
    region VARCHAR(100),
    city VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    threat_count BIGINT DEFAULT 0,
    high_severity_count BIGINT DEFAULT 0,
    blocked_count BIGINT DEFAULT 0,
    risk_score FLOAT DEFAULT 0,
    threat_density FLOAT DEFAULT 0, -- 威胁密度
    last_threat_at TIMESTAMP NULL,
    reputation_score FLOAT DEFAULT 50, -- 声誉评分 (0-100)
    is_high_risk BOOLEAN DEFAULT FALSE,
    monitoring_level ENUM('normal', 'enhanced', 'strict') DEFAULT 'normal',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_location (country_code, region, city),
    INDEX idx_country_code (country_code),
    INDEX idx_threat_count (threat_count),
    INDEX idx_risk_score (risk_score),
    INDEX idx_is_high_risk (is_high_risk)
);

-- 攻击模式分析表
CREATE TABLE IF NOT EXISTS attack_patterns (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    pattern_id VARCHAR(100) UNIQUE NOT NULL,
    pattern_name VARCHAR(200) NOT NULL,
    pattern_type VARCHAR(50) NOT NULL,
    attack_signature TEXT NOT NULL,
    regex_pattern TEXT,
    threat_indicators JSON,
    severity_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    confidence_threshold FLOAT DEFAULT 0.8,
    false_positive_rate FLOAT DEFAULT 0,
    detection_count BIGINT DEFAULT 0,
    last_detected_at TIMESTAMP NULL,
    effectiveness_score FLOAT DEFAULT 0,
    description TEXT,
    mitigation_strategy TEXT,
    references JSON, -- 相关参考资料
    is_active BOOLEAN DEFAULT TRUE,
    created_by VARCHAR(100),
    updated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_pattern_type (pattern_type),
    INDEX idx_severity_level (severity_level),
    INDEX idx_is_active (is_active),
    INDEX idx_last_detected_at (last_detected_at)
);

-- 安全事件时间线表
CREATE TABLE IF NOT EXISTS security_timeline (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(100) UNIQUE NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_category ENUM('threat', 'attack', 'defense', 'maintenance', 'investigation') NOT NULL,
    severity ENUM('info', 'low', 'medium', 'high', 'critical') NOT NULL,
    title VARCHAR(500) NOT NULL,
    description TEXT,
    source_ip VARCHAR(45),
    target_ip VARCHAR(45),
    affected_systems JSON,
    impact_assessment TEXT,
    response_actions JSON,
    status ENUM('open', 'investigating', 'contained', 'resolved', 'closed') DEFAULT 'open',
    assigned_to VARCHAR(100),
    escalation_level INT DEFAULT 0,
    correlation_id VARCHAR(100), -- 关联事件ID
    external_references JSON,
    evidence_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    
    INDEX idx_event_type (event_type),
    INDEX idx_event_category (event_category),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_correlation_id (correlation_id)
);

-- 威胁情报数据表
CREATE TABLE IF NOT EXISTS threat_intelligence (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    intel_id VARCHAR(100) UNIQUE NOT NULL,
    intel_type ENUM('ioc', 'ttp', 'campaign', 'actor', 'malware') NOT NULL,
    threat_actor VARCHAR(200),
    campaign_name VARCHAR(200),
    malware_family VARCHAR(100),
    ioc_type VARCHAR(50), -- IP, domain, hash, url等
    ioc_value TEXT NOT NULL,
    confidence_level ENUM('low', 'medium', 'high', 'verified') NOT NULL,
    threat_types JSON,
    first_seen TIMESTAMP NULL,
    last_seen TIMESTAMP NULL,
    tlp_marking ENUM('white', 'green', 'amber', 'red') DEFAULT 'white',
    source_reliability ENUM('A', 'B', 'C', 'D', 'E', 'F') DEFAULT 'F',
    intel_source VARCHAR(200),
    description TEXT,
    kill_chain_phases JSON,
    mitre_tactics JSON,
    mitre_techniques JSON,
    tags JSON,
    is_active BOOLEAN DEFAULT TRUE,
    expiry_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_intel_type (intel_type),
    INDEX idx_ioc_type (ioc_type),
    INDEX idx_ioc_value (ioc_value(255)),
    INDEX idx_confidence_level (confidence_level),
    INDEX idx_threat_actor (threat_actor),
    INDEX idx_is_active (is_active),
    INDEX idx_expiry_date (expiry_date)
);

-- 系统性能监控表
CREATE TABLE IF NOT EXISTS system_performance (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cpu_usage FLOAT DEFAULT 0,
    memory_usage FLOAT DEFAULT 0,
    disk_usage FLOAT DEFAULT 0,
    network_io_read BIGINT DEFAULT 0,
    network_io_write BIGINT DEFAULT 0,
    active_processes INT DEFAULT 0,
    load_average FLOAT DEFAULT 0,
    monitoring_status ENUM('healthy', 'warning', 'critical') DEFAULT 'healthy',
    alert_threshold_exceeded JSON,
    system_metrics JSON,
    
    INDEX idx_timestamp (timestamp),
    INDEX idx_monitoring_status (monitoring_status)
);

-- 自动防御规则表
CREATE TABLE IF NOT EXISTS auto_defense_rules (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    rule_id VARCHAR(100) UNIQUE NOT NULL,
    rule_name VARCHAR(200) NOT NULL,
    rule_type ENUM('rate_limiting', 'ip_blocking', 'geo_blocking', 'pattern_blocking', 'behavioral') NOT NULL,
    trigger_conditions JSON NOT NULL,
    action_type ENUM('block', 'rate_limit', 'captcha', 'redirect', 'log_only') NOT NULL,
    action_parameters JSON,
    priority INT DEFAULT 100,
    cooldown_period INT DEFAULT 300,
    max_triggers_per_hour INT DEFAULT 100,
    effectiveness_score FLOAT DEFAULT 0,
    false_positive_count INT DEFAULT 0,
    trigger_count INT DEFAULT 0,
    last_triggered_at TIMESTAMP NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    auto_disable_threshold INT DEFAULT 50, -- 误报阈值
    created_by VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_rule_type (rule_type),
    INDEX idx_action_type (action_type),
    INDEX idx_priority (priority),
    INDEX idx_is_enabled (is_enabled),
    INDEX idx_last_triggered_at (last_triggered_at)
);

-- 创建视图：实时威胁概览
CREATE OR REPLACE VIEW real_time_threat_overview AS
SELECT 
    DATE(created_at) as threat_date,
    threat_type,
    threat_level,
    COUNT(*) as threat_count,
    AVG(threat_score) as avg_threat_score,
    COUNT(DISTINCT source_ip) as unique_source_ips,
    COUNT(DISTINCT source_country) as affected_countries,
    SUM(CASE WHEN is_mitigated = 1 THEN 1 ELSE 0 END) as mitigated_count
FROM threat_detections 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE(created_at), threat_type, threat_level
ORDER BY threat_date DESC, threat_count DESC;

-- 创建视图：地理威胁热图数据
CREATE OR REPLACE VIEW geo_threat_heatmap AS
SELECT 
    g.country_code,
    g.country_name,
    g.latitude,
    g.longitude,
    g.threat_count,
    g.risk_score,
    COUNT(t.id) as recent_threats,
    MAX(t.created_at) as last_threat_time
FROM geo_threat_distribution g
LEFT JOIN threat_detections t ON g.country_name = t.source_country 
    AND t.created_at >= DATE_SUB(NOW(), INTERVAL 6 HOUR)
WHERE g.threat_count > 0
GROUP BY g.id
ORDER BY g.risk_score DESC, recent_threats DESC;

-- 插入默认的攻击模式
INSERT INTO attack_patterns (pattern_id, pattern_name, pattern_type, attack_signature, severity_level, description) VALUES
('sql_inject_001', 'SQL注入 - UNION查询', 'sql_injection', '(union|UNION).*(select|SELECT)', 'high', '检测基于UNION的SQL注入攻击'),
('xss_001', 'XSS - Script标签', 'xss', '<script[^>]*>.*?</script>', 'medium', '检测script标签XSS攻击'),
('path_traversal_001', '路径遍历 - 目录穿越', 'path_traversal', '\.\./|\.\.\\\', 'medium', '检测目录遍历攻击'),
('ddos_001', 'DDoS - 高频请求', 'ddos', 'REQUEST_RATE_LIMIT_EXCEEDED', 'critical', '检测DDoS攻击模式'),
('brute_force_001', '暴力破解 - 登录尝试', 'brute_force', 'FAILED_LOGIN_ATTEMPTS_EXCEEDED', 'high', '检测暴力破解攻击'),
('malware_001', '恶意软件 - 可疑User-Agent', 'malware', '.*(bot|crawler|spider|scanner).*', 'medium', '检测恶意爬虫和扫描器'),
('cmd_inject_001', '命令注入 - 系统命令', 'command_injection', '(;|&&|\\||\\|\\||`).*(rm|cat|ls|ps|wget|curl)', 'critical', '检测命令注入攻击');

-- 插入默认的自动防御规则
INSERT INTO auto_defense_rules (rule_id, rule_name, rule_type, trigger_conditions, action_type, action_parameters, description) VALUES
('rate_limit_001', 'IP访问频率限制', 'rate_limiting', '{"requests_per_minute": 60, "window_size": 60}', 'rate_limit', '{"limit": 30, "window": 60}', '限制单个IP每分钟请求数'),
('geo_block_001', '高风险国家阻断', 'geo_blocking', '{"risk_countries": ["CN", "RU", "KP"]}', 'block', '{"duration": 3600}', '阻断来自高风险国家的访问'),
('sql_inject_block', 'SQL注入自动阻断', 'pattern_blocking', '{"pattern_ids": ["sql_inject_001"]}', 'block', '{"duration": 1800}', '检测到SQL注入时自动阻断IP'),
('ddos_mitigation', 'DDoS攻击缓解', 'behavioral', '{"requests_per_second": 10, "concurrent_connections": 50}', 'rate_limit', '{"limit": 5, "window": 60}', 'DDoS攻击检测和缓解');
