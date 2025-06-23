<?php
/**
 * AlingAi Pro 6.0 - 数据库迁移脚本
 * Database Migration Script - 6.0版本数据库结构初始化
 * 
 * @package AlingAi\Database\Migrations
 * @version 6.0.0
 * @author AlingAi Team
 * @copyright 2025 AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use AlingAi\Core\Application;
use AlingAi\Core\Services\DatabaseServiceInterface;

/**
 * AlingAi Pro 6.0 数据库迁移
 * 
 * 创建所有6.0版本所需的数据库表:
 * - 企业工作空间表
 * - 政府服务表
 * - AI模型管理表
 * - 安全审计表
 * - 区块链服务表
 * - 性能监控表
 */
class DatabaseMigration_v6_0
{
    private DatabaseServiceInterface $database;
    private array $migrations = [];
    
    public function __construct(DatabaseServiceInterface $database)
    {
        $this->database = $database;
        $this->defineMigrations();
    }
    
    /**
     * 定义所有迁移
     */
    private function defineMigrations(): void
    {
        $this->migrations = [
            'enterprise_workspaces' => $this->getEnterpriseWorkspacesSchema(),
            'workspace_users' => $this->getWorkspaceUsersSchema(),
            'workspace_projects' => $this->getWorkspaceProjectsSchema(),
            'workspace_documents' => $this->getWorkspaceDocumentsSchema(),
            'workspace_folders' => $this->getWorkspaceFoldersSchema(),
            'workspace_roles' => $this->getWorkspaceRolesSchema(),
            'workspace_ai_assistants' => $this->getWorkspaceAIAssistantsSchema(),
            'government_services' => $this->getGovernmentServicesSchema(),
            'government_applications' => $this->getGovernmentApplicationsSchema(),
            'government_documents' => $this->getGovernmentDocumentsSchema(),
            'ai_models' => $this->getAIModelsSchema(),
            'ai_model_versions' => $this->getAIModelVersionsSchema(),
            'ai_training_jobs' => $this->getAITrainingJobsSchema(),
            'ai_inference_logs' => $this->getAIInferenceLogsSchema(),
            'security_audit_log' => $this->getSecurityAuditLogSchema(),
            'security_sessions' => $this->getSecuritySessionsSchema(),
            'security_threats' => $this->getSecurityThreatsSchema(),
            'blockchain_transactions' => $this->getBlockchainTransactionsSchema(),
            'blockchain_contracts' => $this->getBlockchainContractsSchema(),
            'performance_metrics' => $this->getPerformanceMetricsSchema(),
            'system_health' => $this->getSystemHealthSchema(),
            'user_preferences' => $this->getUserPreferencesSchema(),
            'api_keys' => $this->getApiKeysSchema(),
            'feature_flags' => $this->getFeatureFlagsSchema(),
            'notifications' => $this->getNotificationsSchema()
        ];
    }
    
    /**
     * 执行迁移
     */
    public function migrate(): array
    {
        $results = [];
        
        echo "Starting AlingAi Pro 6.0 Database Migration...\n";
        echo "====================================================\n";
        
        foreach ($this->migrations as $tableName => $schema) {
            echo "Creating table: {$tableName}... ";
            
            try {
                if ($this->tableExists($tableName)) {
                    echo "SKIPPED (already exists)\n";
                    $results[$tableName] = 'skipped';
                    continue;
                }
                
                $this->database->execute($schema);
                echo "SUCCESS\n";
                $results[$tableName] = 'created';
                
            } catch (\Exception $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
                $results[$tableName] = 'failed: ' . $e->getMessage();
            }
        }
        
        echo "\n====================================================\n";
        echo "Migration completed!\n";
        echo "Tables created: " . count(array_filter($results, fn($r) => $r === 'created')) . "\n";
        echo "Tables skipped: " . count(array_filter($results, fn($r) => $r === 'skipped')) . "\n";
        echo "Tables failed: " . count(array_filter($results, fn($r) => strpos($r, 'failed') === 0)) . "\n";
        
        return $results;
    }
    
    /**
     * 检查表是否存在
     */
    private function tableExists(string $tableName): bool
    {
        try {
            $result = $this->database->query("SHOW TABLES LIKE '{$tableName}'");
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 企业工作空间表结构
     */
    private function getEnterpriseWorkspacesSchema(): string
    {
        return "
            CREATE TABLE enterprise_workspaces (
                id VARCHAR(50) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                template VARCHAR(50) NOT NULL DEFAULT 'startup',
                config JSON,
                max_users INT DEFAULT 50,
                security_level ENUM('standard', 'high', 'enterprise', 'government') DEFAULT 'standard',
                storage_limit VARCHAR(20) DEFAULT '100GB',
                status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                created_by VARCHAR(50),
                INDEX idx_template (template),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 工作空间用户表结构
     */
    private function getWorkspaceUsersSchema(): string
    {
        return "
            CREATE TABLE workspace_users (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                workspace_id VARCHAR(50) NOT NULL,
                user_id VARCHAR(50) NOT NULL,
                role VARCHAR(50) NOT NULL,
                permissions JSON,
                status ENUM('active', 'suspended', 'removed') DEFAULT 'active',
                joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_active TIMESTAMP NULL,
                FOREIGN KEY (workspace_id) REFERENCES enterprise_workspaces(id) ON DELETE CASCADE,
                UNIQUE KEY unique_workspace_user (workspace_id, user_id),
                INDEX idx_user_id (user_id),
                INDEX idx_role (role),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 工作空间项目表结构
     */
    private function getWorkspaceProjectsSchema(): string
    {
        return "
            CREATE TABLE workspace_projects (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                workspace_id VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                status ENUM('planning', 'active', 'on_hold', 'completed', 'cancelled') DEFAULT 'planning',
                priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                start_date DATE,
                end_date DATE,
                budget DECIMAL(15,2),
                manager_id VARCHAR(50),
                metadata JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (workspace_id) REFERENCES enterprise_workspaces(id) ON DELETE CASCADE,
                INDEX idx_workspace_id (workspace_id),
                INDEX idx_status (status),
                INDEX idx_priority (priority),
                INDEX idx_manager_id (manager_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 工作空间文档表结构
     */
    private function getWorkspaceDocumentsSchema(): string
    {
        return "
            CREATE TABLE workspace_documents (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                workspace_id VARCHAR(50) NOT NULL,
                folder_id BIGINT,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(50),
                size BIGINT,
                mime_type VARCHAR(100),
                file_path VARCHAR(500),
                version INT DEFAULT 1,
                checksum VARCHAR(64),
                tags JSON,
                metadata JSON,
                uploaded_by VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (workspace_id) REFERENCES enterprise_workspaces(id) ON DELETE CASCADE,
                INDEX idx_workspace_id (workspace_id),
                INDEX idx_folder_id (folder_id),
                INDEX idx_type (type),
                INDEX idx_uploaded_by (uploaded_by),
                FULLTEXT idx_name_content (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 工作空间文件夹表结构
     */
    private function getWorkspaceFoldersSchema(): string
    {
        return "
            CREATE TABLE workspace_folders (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                workspace_id VARCHAR(50) NOT NULL,
                parent_id BIGINT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                permissions JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (workspace_id) REFERENCES enterprise_workspaces(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES workspace_folders(id) ON DELETE CASCADE,
                INDEX idx_workspace_id (workspace_id),
                INDEX idx_parent_id (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 工作空间角色表结构
     */
    private function getWorkspaceRolesSchema(): string
    {
        return "
            CREATE TABLE workspace_roles (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                workspace_id VARCHAR(50) NOT NULL,
                role_name VARCHAR(50) NOT NULL,
                permissions JSON,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (workspace_id) REFERENCES enterprise_workspaces(id) ON DELETE CASCADE,
                UNIQUE KEY unique_workspace_role (workspace_id, role_name),
                INDEX idx_workspace_id (workspace_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 工作空间AI助手表结构
     */
    private function getWorkspaceAIAssistantsSchema(): string
    {
        return "
            CREATE TABLE workspace_ai_assistants (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                workspace_id VARCHAR(50) NOT NULL,
                name VARCHAR(255) DEFAULT 'AI Assistant',
                config JSON,
                status ENUM('active', 'inactive', 'training') DEFAULT 'active',
                model_version VARCHAR(50),
                last_training TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (workspace_id) REFERENCES enterprise_workspaces(id) ON DELETE CASCADE,
                INDEX idx_workspace_id (workspace_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 政府服务表结构
     */
    private function getGovernmentServicesSchema(): string
    {
        return "
            CREATE TABLE government_services (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                service_code VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                category VARCHAR(100),
                department VARCHAR(100),
                processing_time VARCHAR(100),
                required_documents JSON,
                fees JSON,
                digital_form_url VARCHAR(500),
                ai_enabled BOOLEAN DEFAULT FALSE,
                status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_service_code (service_code),
                INDEX idx_category (category),
                INDEX idx_department (department),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 政府申请表结构
     */
    private function getGovernmentApplicationsSchema(): string
    {
        return "
            CREATE TABLE government_applications (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                application_number VARCHAR(50) UNIQUE NOT NULL,
                service_id BIGINT NOT NULL,
                applicant_id VARCHAR(50) NOT NULL,
                form_data JSON,
                documents JSON,
                status ENUM('submitted', 'under_review', 'additional_info_required', 'approved', 'rejected') DEFAULT 'submitted',
                ai_analysis JSON,
                processing_notes TEXT,
                assigned_officer VARCHAR(50),
                submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                FOREIGN KEY (service_id) REFERENCES government_services(id),
                INDEX idx_application_number (application_number),
                INDEX idx_service_id (service_id),
                INDEX idx_applicant_id (applicant_id),
                INDEX idx_status (status),
                INDEX idx_submitted_at (submitted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 政府文档表结构
     */
    private function getGovernmentDocumentsSchema(): string
    {
        return "
            CREATE TABLE government_documents (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                application_id BIGINT,
                document_type VARCHAR(100) NOT NULL,
                original_name VARCHAR(255),
                stored_name VARCHAR(255),
                file_path VARCHAR(500),
                mime_type VARCHAR(100),
                size BIGINT,
                checksum VARCHAR(64),
                ai_extracted_data JSON,
                verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (application_id) REFERENCES government_applications(id) ON DELETE CASCADE,
                INDEX idx_application_id (application_id),
                INDEX idx_document_type (document_type),
                INDEX idx_verification_status (verification_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * AI模型表结构
     */
    private function getAIModelsSchema(): string
    {
        return "
            CREATE TABLE ai_models (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                model_name VARCHAR(100) UNIQUE NOT NULL,
                model_type ENUM('nlp', 'cv', 'speech', 'multimodal') NOT NULL,
                framework VARCHAR(50),
                description TEXT,
                capabilities JSON,
                requirements JSON,
                performance_metrics JSON,
                status ENUM('active', 'inactive', 'deprecated') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_model_name (model_name),
                INDEX idx_model_type (model_type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * AI模型版本表结构
     */
    private function getAIModelVersionsSchema(): string
    {
        return "
            CREATE TABLE ai_model_versions (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                model_id BIGINT NOT NULL,
                version VARCHAR(20) NOT NULL,
                file_path VARCHAR(500),
                file_size BIGINT,
                checksum VARCHAR(64),
                training_data_info JSON,
                accuracy_metrics JSON,
                release_notes TEXT,
                is_active BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (model_id) REFERENCES ai_models(id) ON DELETE CASCADE,
                UNIQUE KEY unique_model_version (model_id, version),
                INDEX idx_model_id (model_id),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * AI训练任务表结构
     */
    private function getAITrainingJobsSchema(): string
    {
        return "
            CREATE TABLE ai_training_jobs (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                job_name VARCHAR(255) NOT NULL,
                model_id BIGINT NOT NULL,
                training_config JSON,
                dataset_info JSON,
                status ENUM('queued', 'running', 'completed', 'failed', 'cancelled') DEFAULT 'queued',
                progress INT DEFAULT 0,
                metrics JSON,
                logs TEXT,
                started_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (model_id) REFERENCES ai_models(id) ON DELETE CASCADE,
                INDEX idx_model_id (model_id),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * AI推理日志表结构
     */
    private function getAIInferenceLogsSchema(): string
    {
        return "
            CREATE TABLE ai_inference_logs (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                model_id BIGINT NOT NULL,
                input_type VARCHAR(50),
                input_size INT,
                output_type VARCHAR(50),
                processing_time DECIMAL(10,4),
                success BOOLEAN DEFAULT TRUE,
                error_message TEXT,
                metadata JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (model_id) REFERENCES ai_models(id) ON DELETE CASCADE,
                INDEX idx_model_id (model_id),
                INDEX idx_created_at (created_at),
                INDEX idx_success (success)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 安全审计日志表结构
     */
    private function getSecurityAuditLogSchema(): string
    {
        return "
            CREATE TABLE security_audit_log (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                user_id VARCHAR(50),
                ip VARCHAR(45),
                user_agent TEXT,
                risk_score DECIMAL(3,2),
                allowed BOOLEAN,
                threats_detected JSON,
                security_actions JSON,
                request_data JSON,
                response_data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_type (event_type),
                INDEX idx_user_id (user_id),
                INDEX idx_ip (ip),
                INDEX idx_created_at (created_at),
                INDEX idx_risk_score (risk_score)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 安全会话表结构
     */
    private function getSecuritySessionsSchema(): string
    {
        return "
            CREATE TABLE security_sessions (
                id VARCHAR(64) PRIMARY KEY,
                user_id VARCHAR(50),
                ip VARCHAR(45),
                user_agent TEXT,
                security_level ENUM('standard', 'restricted', 'high_security'),
                data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_expires_at (expires_at),
                INDEX idx_last_activity (last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 安全威胁表结构
     */
    private function getSecurityThreatsSchema(): string
    {
        return "
            CREATE TABLE security_threats (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                threat_type VARCHAR(50) NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
                source_ip VARCHAR(45),
                description TEXT,
                details JSON,
                status ENUM('detected', 'investigating', 'mitigated', 'resolved') DEFAULT 'detected',
                detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                resolved_at TIMESTAMP NULL,
                INDEX idx_threat_type (threat_type),
                INDEX idx_severity (severity),
                INDEX idx_source_ip (source_ip),
                INDEX idx_status (status),
                INDEX idx_detected_at (detected_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 区块链交易表结构
     */
    private function getBlockchainTransactionsSchema(): string
    {
        return "
            CREATE TABLE blockchain_transactions (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                transaction_hash VARCHAR(66) UNIQUE NOT NULL,
                blockchain VARCHAR(50) NOT NULL,
                block_number BIGINT,
                from_address VARCHAR(42),
                to_address VARCHAR(42),
                value VARCHAR(32),
                gas_used BIGINT,
                gas_price VARCHAR(32),
                status ENUM('pending', 'confirmed', 'failed') DEFAULT 'pending',
                transaction_data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                confirmed_at TIMESTAMP NULL,
                INDEX idx_transaction_hash (transaction_hash),
                INDEX idx_blockchain (blockchain),
                INDEX idx_from_address (from_address),
                INDEX idx_to_address (to_address),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 区块链智能合约表结构
     */
    private function getBlockchainContractsSchema(): string
    {
        return "
            CREATE TABLE blockchain_contracts (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                contract_address VARCHAR(42) UNIQUE NOT NULL,
                blockchain VARCHAR(50) NOT NULL,
                contract_name VARCHAR(255),
                abi JSON,
                bytecode TEXT,
                source_code TEXT,
                compiler_version VARCHAR(50),
                status ENUM('deployed', 'verified', 'deprecated') DEFAULT 'deployed',
                deployment_transaction VARCHAR(66),
                deployed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_contract_address (contract_address),
                INDEX idx_blockchain (blockchain),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 性能指标表结构
     */
    private function getPerformanceMetricsSchema(): string
    {
        return "
            CREATE TABLE performance_metrics (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                metric_type VARCHAR(50) NOT NULL,
                metric_name VARCHAR(100) NOT NULL,
                value DECIMAL(15,4),
                unit VARCHAR(20),
                tags JSON,
                metadata JSON,
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric_type (metric_type),
                INDEX idx_metric_name (metric_name),
                INDEX idx_recorded_at (recorded_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 系统健康表结构
     */
    private function getSystemHealthSchema(): string
    {
        return "
            CREATE TABLE system_health (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                component VARCHAR(100) NOT NULL,
                status ENUM('healthy', 'warning', 'critical', 'unknown') NOT NULL,
                message TEXT,
                details JSON,
                checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_component (component),
                INDEX idx_status (status),
                INDEX idx_checked_at (checked_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 用户偏好表结构
     */
    private function getUserPreferencesSchema(): string
    {
        return "
            CREATE TABLE user_preferences (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(50) NOT NULL,
                workspace_id VARCHAR(50),
                preferences JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_workspace (user_id, workspace_id),
                INDEX idx_user_id (user_id),
                INDEX idx_workspace_id (workspace_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * API密钥表结构
     */
    private function getApiKeysSchema(): string
    {
        return "
            CREATE TABLE api_keys (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                key_hash VARCHAR(64) UNIQUE NOT NULL,
                user_id VARCHAR(50) NOT NULL,
                name VARCHAR(255),
                permissions JSON,
                rate_limit INT DEFAULT 1000,
                expires_at TIMESTAMP NULL,
                last_used TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_key_hash (key_hash),
                INDEX idx_user_id (user_id),
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 功能标志表结构
     */
    private function getFeatureFlagsSchema(): string
    {
        return "
            CREATE TABLE feature_flags (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                flag_name VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                enabled BOOLEAN DEFAULT FALSE,
                conditions JSON,
                rollout_percentage INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_flag_name (flag_name),
                INDEX idx_enabled (enabled)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
    
    /**
     * 通知表结构
     */
    private function getNotificationsSchema(): string
    {
        return "
            CREATE TABLE notifications (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(50) NOT NULL,
                workspace_id VARCHAR(50),
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                data JSON,
                read_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_workspace_id (workspace_id),
                INDEX idx_type (type),
                INDEX idx_read_at (read_at),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
    }
}

// 如果直接运行此脚本
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        echo "Initializing AlingAi Pro 6.0...\n";
        
        $app = Application::create();
        $container = $app->getContainer();
        $database = $container->get(DatabaseServiceInterface::class);
        
        $migration = new DatabaseMigration_v6_0($database);
        $results = $migration->migrate();
        
        echo "\nMigration Results:\n";
        foreach ($results as $table => $result) {
            echo "  {$table}: {$result}\n";
        }
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
