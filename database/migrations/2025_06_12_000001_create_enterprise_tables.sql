-- AlingAi Pro 6.0 企业级数据库迁移脚本
-- 创建时间: 2025-06-12
-- 版本: 6.0.0

-- ================================================
-- 企业服务相关表
-- ================================================

-- 企业工作空间表
CREATE TABLE IF NOT EXISTS enterprise_workspaces (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('startup', 'enterprise', 'government', 'research', 'custom') DEFAULT 'enterprise',
    organization_id VARCHAR(64),
    owner_id VARCHAR(64) NOT NULL,
    settings JSON,
    resource_config JSON,
    security_config JSON,
    collaboration_config JSON,
    status ENUM('active', 'suspended', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_workspace_id (workspace_id),
    INDEX idx_organization_id (organization_id),
    INDEX idx_owner_id (owner_id),
    INDEX idx_status (status)
);

-- 企业项目管理表
CREATE TABLE IF NOT EXISTS enterprise_projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(64) UNIQUE NOT NULL,
    workspace_id VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(100),
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('planning', 'active', 'on_hold', 'completed', 'cancelled') DEFAULT 'planning',
    start_date DATE,
    end_date DATE,
    budget DECIMAL(15,2),
    progress INT DEFAULT 0,
    team_members JSON,
    milestones JSON,
    resources JSON,
    risk_assessment JSON,
    created_by VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_project_id (project_id),
    INDEX idx_workspace_id (workspace_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
);

-- 企业团队管理表
CREATE TABLE IF NOT EXISTS enterprise_teams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id VARCHAR(64) UNIQUE NOT NULL,
    workspace_id VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    team_type VARCHAR(100),
    lead_id VARCHAR(64),
    members JSON,
    permissions JSON,
    settings JSON,
    performance_metrics JSON,
    status ENUM('active', 'inactive', 'disbanded') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_team_id (team_id),
    INDEX idx_workspace_id (workspace_id),
    INDEX idx_lead_id (lead_id),
    INDEX idx_status (status)
);

-- 企业任务自动化表
CREATE TABLE IF NOT EXISTS enterprise_automations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    automation_id VARCHAR(64) UNIQUE NOT NULL,
    workspace_id VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    trigger_type VARCHAR(100),
    trigger_config JSON,
    actions JSON,
    conditions JSON,
    schedule_config JSON,
    execution_history JSON,
    is_active BOOLEAN DEFAULT TRUE,
    last_executed_at TIMESTAMP NULL,
    next_execution_at TIMESTAMP NULL,
    created_by VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_automation_id (automation_id),
    INDEX idx_workspace_id (workspace_id),
    INDEX idx_is_active (is_active),
    INDEX idx_next_execution (next_execution_at)
);

-- 企业文档管理表
CREATE TABLE IF NOT EXISTS enterprise_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id VARCHAR(64) UNIQUE NOT NULL,
    workspace_id VARCHAR(64) NOT NULL,
    project_id VARCHAR(64),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    content LONGTEXT,
    document_type VARCHAR(100),
    file_path VARCHAR(500),
    file_size BIGINT,
    mime_type VARCHAR(100),
    version VARCHAR(20) DEFAULT '1.0',
    version_history JSON,
    tags JSON,
    permissions JSON,
    metadata JSON,
    is_template BOOLEAN DEFAULT FALSE,
    template_category VARCHAR(100),
    owner_id VARCHAR(64),
    created_by VARCHAR(64),
    last_modified_by VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_document_id (document_id),
    INDEX idx_workspace_id (workspace_id),
    INDEX idx_project_id (project_id),
    INDEX idx_owner_id (owner_id),
    INDEX idx_document_type (document_type),
    INDEX idx_is_template (is_template)
);

-- ================================================
-- AI平台相关表
-- ================================================

-- AI模型管理表
CREATE TABLE IF NOT EXISTS ai_models (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_id VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    model_type ENUM('nlp', 'cv', 'speech', 'knowledge_graph', 'multimodal') NOT NULL,
    provider VARCHAR(100),
    version VARCHAR(50),
    config JSON,
    parameters JSON,
    capabilities JSON,
    performance_metrics JSON,
    training_data JSON,
    deployment_status ENUM('development', 'testing', 'staging', 'production', 'deprecated') DEFAULT 'development',
    api_endpoint VARCHAR(500),
    access_token_encrypted TEXT,
    usage_statistics JSON,
    cost_per_request DECIMAL(10,6),
    rate_limits JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_by VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_model_id (model_id),
    INDEX idx_model_type (model_type),
    INDEX idx_deployment_status (deployment_status),
    INDEX idx_is_active (is_active)
);

-- AI提示词管理表
CREATE TABLE IF NOT EXISTS ai_prompts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prompt_id VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    template TEXT NOT NULL,
    variables JSON,
    model_compatibility JSON,
    use_cases JSON,
    performance_metrics JSON,
    version VARCHAR(20) DEFAULT '1.0',
    version_history JSON,
    tags JSON,
    is_public BOOLEAN DEFAULT FALSE,
    usage_count INT DEFAULT 0,
    rating DECIMAL(3,2),
    created_by VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_prompt_id (prompt_id),
    INDEX idx_category (category),
    INDEX idx_is_public (is_public),
    INDEX idx_usage_count (usage_count),
    INDEX idx_rating (rating)
);

-- AI任务执行记录表
CREATE TABLE IF NOT EXISTS ai_task_executions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    execution_id VARCHAR(64) UNIQUE NOT NULL,
    task_type VARCHAR(100) NOT NULL,
    model_id VARCHAR(64),
    prompt_id VARCHAR(64),
    input_data JSON,
    output_data JSON,
    parameters JSON,
    execution_time_ms INT,
    tokens_used INT,
    cost DECIMAL(10,6),
    quality_score DECIMAL(3,2),
    error_message TEXT,
    status ENUM('pending', 'running', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    user_id VARCHAR(64),
    workspace_id VARCHAR(64),
    project_id VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_execution_id (execution_id),
    INDEX idx_task_type (task_type),
    INDEX idx_model_id (model_id),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- ================================================
-- 零信任安全相关表
-- ================================================

-- 用户身份认证表
CREATE TABLE IF NOT EXISTS user_authentications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auth_id VARCHAR(64) UNIQUE NOT NULL,
    user_id VARCHAR(64) NOT NULL,
    auth_methods JSON,
    biometric_templates JSON,
    hardware_tokens JSON,
    software_tokens JSON,
    backup_codes JSON,
    security_questions JSON,
    password_hash VARCHAR(255),
    password_salt VARCHAR(100),
    password_history JSON,
    last_password_change TIMESTAMP,
    failed_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    security_preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_auth_id (auth_id),
    INDEX idx_user_id (user_id),
    INDEX idx_locked_until (locked_until)
);

-- 设备管理表
CREATE TABLE IF NOT EXISTS user_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(64) UNIQUE NOT NULL,
    user_id VARCHAR(64) NOT NULL,
    device_name VARCHAR(255),
    device_type VARCHAR(100),
    platform VARCHAR(100),
    browser VARCHAR(100),
    fingerprint_hash VARCHAR(255),
    public_key TEXT,
    device_certificate TEXT,
    trust_level ENUM('untrusted', 'low', 'medium', 'high', 'trusted') DEFAULT 'untrusted',
    security_attributes JSON,
    location_history JSON,
    usage_patterns JSON,
    last_seen_at TIMESTAMP,
    last_ip_address VARCHAR(45),
    is_active BOOLEAN DEFAULT TRUE,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_device_id (device_id),
    INDEX idx_user_id (user_id),
    INDEX idx_trust_level (trust_level),
    INDEX idx_is_active (is_active),
    INDEX idx_last_seen_at (last_seen_at)
);

-- 访问会话表
CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) UNIQUE NOT NULL,
    user_id VARCHAR(64) NOT NULL,
    device_id VARCHAR(64),
    ip_address VARCHAR(45),
    user_agent TEXT,
    geo_location JSON,
    auth_methods JSON,
    trust_level ENUM('untrusted', 'low', 'medium', 'high', 'trusted') DEFAULT 'untrusted',
    risk_score INT DEFAULT 0,
    permissions JSON,
    restrictions JSON,
    activities JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    last_activity_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    end_reason VARCHAR(100),
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_device_id (device_id),
    INDEX idx_expires_at (expires_at),
    INDEX idx_last_activity_at (last_activity_at)
);

-- 安全策略表
CREATE TABLE IF NOT EXISTS security_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    policy_id VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    policy_type VARCHAR(100),
    scope JSON,
    conditions JSON,
    actions JSON,
    priority INT DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    effective_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    effective_until TIMESTAMP NULL,
    created_by VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_policy_id (policy_id),
    INDEX idx_policy_type (policy_type),
    INDEX idx_is_active (is_active),
    INDEX idx_priority (priority)
);

-- 安全审计日志表
CREATE TABLE IF NOT EXISTS security_audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    log_id VARCHAR(64) UNIQUE NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_category VARCHAR(100),
    severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    user_id VARCHAR(64),
    device_id VARCHAR(64),
    session_id VARCHAR(64),
    ip_address VARCHAR(45),
    resource VARCHAR(500),
    action VARCHAR(100),
    result ENUM('success', 'failure', 'blocked', 'pending') NOT NULL,
    risk_score INT,
    context_data JSON,
    threat_indicators JSON,
    response_actions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_id (log_id),
    INDEX idx_event_type (event_type),
    INDEX idx_severity (severity),
    INDEX idx_user_id (user_id),
    INDEX idx_result (result),
    INDEX idx_created_at (created_at)
);

-- ================================================
-- 区块链相关表
-- ================================================

-- 区块链网络配置表
CREATE TABLE IF NOT EXISTS blockchain_networks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    network_id VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    chain_id VARCHAR(100),
    network_type ENUM('mainnet', 'testnet', 'devnet') DEFAULT 'mainnet',
    rpc_endpoint VARCHAR(500),
    ws_endpoint VARCHAR(500),
    explorer_url VARCHAR(500),
    native_currency JSON,
    block_time INT,
    consensus_mechanism VARCHAR(100),
    configuration JSON,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_network_id (network_id),
    INDEX idx_chain_id (chain_id),
    INDEX idx_status (status)
);

-- 智能合约表
CREATE TABLE IF NOT EXISTS smart_contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id VARCHAR(64) UNIQUE NOT NULL,
    network_id VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    contract_address VARCHAR(100),
    source_code LONGTEXT,
    bytecode LONGTEXT,
    abi JSON,
    compiler_version VARCHAR(50),
    deployment_transaction VARCHAR(100),
    deployment_block BIGINT,
    verification_status ENUM('unverified', 'verified', 'failed') DEFAULT 'unverified',
    audit_reports JSON,
    usage_statistics JSON,
    gas_optimization JSON,
    status ENUM('deployed', 'paused', 'upgraded', 'deprecated') DEFAULT 'deployed',
    owner_address VARCHAR(100),
    created_by VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contract_id (contract_id),
    INDEX idx_network_id (network_id),
    INDEX idx_contract_address (contract_address),
    INDEX idx_status (status)
);

-- 数字钱包表
CREATE TABLE IF NOT EXISTS digital_wallets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wallet_id VARCHAR(64) UNIQUE NOT NULL,
    user_id VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    wallet_type ENUM('hd', 'simple', 'multisig', 'smart') DEFAULT 'hd',
    supported_networks JSON,
    addresses JSON,
    public_keys JSON,
    encrypted_private_keys LONGTEXT,
    security_config JSON,
    backup_data JSON,
    transaction_history JSON,
    balance_cache JSON,
    last_sync_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_wallet_id (wallet_id),
    INDEX idx_user_id (user_id),
    INDEX idx_wallet_type (wallet_type),
    INDEX idx_is_active (is_active)
);

-- 区块链交易表
CREATE TABLE IF NOT EXISTS blockchain_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(64) UNIQUE NOT NULL,
    network_id VARCHAR(64) NOT NULL,
    wallet_id VARCHAR(64),
    transaction_hash VARCHAR(100),
    block_number BIGINT,
    block_hash VARCHAR(100),
    transaction_index INT,
    from_address VARCHAR(100),
    to_address VARCHAR(100),
    value VARCHAR(50),
    currency VARCHAR(20),
    gas_limit BIGINT,
    gas_used BIGINT,
    gas_price VARCHAR(50),
    transaction_fee VARCHAR(50),
    input_data LONGTEXT,
    transaction_type VARCHAR(100),
    status ENUM('pending', 'confirmed', 'failed', 'cancelled') DEFAULT 'pending',
    confirmations INT DEFAULT 0,
    error_message TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_network_id (network_id),
    INDEX idx_transaction_hash (transaction_hash),
    INDEX idx_wallet_id (wallet_id),
    INDEX idx_status (status),
    INDEX idx_block_number (block_number)
);

-- NFT资产表
CREATE TABLE IF NOT EXISTS nft_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nft_id VARCHAR(64) UNIQUE NOT NULL,
    network_id VARCHAR(64) NOT NULL,
    contract_address VARCHAR(100),
    token_id VARCHAR(100),
    name VARCHAR(255),
    description TEXT,
    image_url TEXT,
    metadata_uri TEXT,
    metadata JSON,
    collection_id VARCHAR(64),
    creator_address VARCHAR(100),
    owner_address VARCHAR(100),
    minting_transaction VARCHAR(100),
    royalty_percentage DECIMAL(5,2),
    rarity_score DECIMAL(10,2),
    attributes JSON,
    transfer_history JSON,
    price_history JSON,
    status ENUM('minted', 'listed', 'sold', 'burned') DEFAULT 'minted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nft_id (nft_id),
    INDEX idx_network_id (network_id),
    INDEX idx_contract_address (contract_address),
    INDEX idx_token_id (token_id),
    INDEX idx_owner_address (owner_address),
    INDEX idx_collection_id (collection_id)
);

-- ================================================
-- 政务服务相关表
-- ================================================

-- 政务服务目录表
CREATE TABLE IF NOT EXISTS government_services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    department VARCHAR(255),
    category VARCHAR(100),
    service_type ENUM('consultation', 'application', 'approval', 'payment', 'certification') NOT NULL,
    requirements JSON,
    process_steps JSON,
    documents_required JSON,
    fees JSON,
    processing_time VARCHAR(100),
    online_available BOOLEAN DEFAULT TRUE,
    appointment_required BOOLEAN DEFAULT FALSE,
    ai_assistance_available BOOLEAN DEFAULT FALSE,
    service_level ENUM('basic', 'standard', 'premium') DEFAULT 'standard',
    status ENUM('active', 'suspended', 'deprecated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service_id (service_id),
    INDEX idx_department (department),
    INDEX idx_category (category),
    INDEX idx_service_type (service_type),
    INDEX idx_status (status)
);

-- 政务服务申请表
CREATE TABLE IF NOT EXISTS service_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id VARCHAR(64) UNIQUE NOT NULL,
    service_id VARCHAR(64) NOT NULL,
    applicant_id VARCHAR(64) NOT NULL,
    application_data JSON,
    submitted_documents JSON,
    current_status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected', 'completed') DEFAULT 'draft',
    status_history JSON,
    assigned_officer VARCHAR(64),
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    estimated_completion DATE,
    actual_completion DATE,
    fees_paid DECIMAL(10,2) DEFAULT 0.00,
    payment_status ENUM('pending', 'paid', 'refunded', 'waived') DEFAULT 'pending',
    notes TEXT,
    ai_analysis JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_application_id (application_id),
    INDEX idx_service_id (service_id),
    INDEX idx_applicant_id (applicant_id),
    INDEX idx_current_status (current_status),
    INDEX idx_assigned_officer (assigned_officer)
);

-- 创建视图：用户活动概览
CREATE VIEW user_activity_overview AS
SELECT 
    u.user_id,
    COUNT(DISTINCT s.session_id) as session_count,
    MAX(s.last_activity_at) as last_activity,
    COUNT(DISTINCT d.device_id) as device_count,
    AVG(s.risk_score) as avg_risk_score
FROM user_authentications u
LEFT JOIN user_sessions s ON u.user_id = s.user_id
LEFT JOIN user_devices d ON u.user_id = d.user_id
WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY u.user_id;

-- 创建视图：企业工作空间统计
CREATE VIEW workspace_statistics AS
SELECT 
    w.workspace_id,
    w.name,
    COUNT(DISTINCT p.project_id) as project_count,
    COUNT(DISTINCT t.team_id) as team_count,
    COUNT(DISTINCT d.document_id) as document_count,
    COUNT(DISTINCT a.automation_id) as automation_count
FROM enterprise_workspaces w
LEFT JOIN enterprise_projects p ON w.workspace_id = p.workspace_id
LEFT JOIN enterprise_teams t ON w.workspace_id = t.workspace_id
LEFT JOIN enterprise_documents d ON w.workspace_id = d.workspace_id
LEFT JOIN enterprise_automations a ON w.workspace_id = a.workspace_id
WHERE w.status = 'active'
GROUP BY w.workspace_id, w.name;

-- 创建存储过程：清理过期会话
DELIMITER //
CREATE PROCEDURE CleanExpiredSessions()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE session_count INT DEFAULT 0;
    
    -- 清理过期会话
    UPDATE user_sessions 
    SET ended_at = NOW(), end_reason = 'expired'
    WHERE expires_at < NOW() AND ended_at IS NULL;
    
    SELECT ROW_COUNT() INTO session_count;
    
    -- 记录清理日志
    INSERT INTO security_audit_logs (
        log_id, event_type, event_category, severity, 
        context_data, created_at
    ) VALUES (
        CONCAT('cleanup_', UNIX_TIMESTAMP()), 
        'session_cleanup', 
        'maintenance', 
        'info',
        JSON_OBJECT('cleaned_sessions', session_count),
        NOW()
    );
END //
DELIMITER ;

-- 创建触发器：记录重要操作
DELIMITER //
CREATE TRIGGER audit_user_auth_changes
AFTER UPDATE ON user_authentications
FOR EACH ROW
BEGIN
    IF OLD.password_hash != NEW.password_hash THEN
        INSERT INTO security_audit_logs (
            log_id, event_type, event_category, severity,
            user_id, context_data, created_at
        ) VALUES (
            CONCAT('auth_', UNIX_TIMESTAMP(), '_', NEW.user_id),
            'password_change',
            'authentication',
            'info',
            NEW.user_id,
            JSON_OBJECT('auth_id', NEW.auth_id),
            NOW()
        );
    END IF;
END //
DELIMITER ;

-- 创建事件调度器：定期清理
CREATE EVENT IF NOT EXISTS cleanup_expired_sessions
ON SCHEDULE EVERY 1 HOUR
DO CALL CleanExpiredSessions();

-- 设置权限和索引优化
ALTER TABLE user_sessions ADD INDEX idx_composite_user_device (user_id, device_id, created_at);
ALTER TABLE security_audit_logs ADD INDEX idx_composite_user_event (user_id, event_type, created_at);
ALTER TABLE blockchain_transactions ADD INDEX idx_composite_address_status (from_address, to_address, status);

-- 完成迁移
INSERT INTO security_audit_logs (
    log_id, event_type, event_category, severity,
    context_data, created_at
) VALUES (
    CONCAT('migration_', UNIX_TIMESTAMP()),
    'database_migration',
    'system',
    'info',
    JSON_OBJECT('version', '6.0.0', 'migration_file', '2025_06_12_000001_create_enterprise_tables.sql'),
    NOW()
);

COMMIT;
