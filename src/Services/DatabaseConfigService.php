<?php

namespace App\Services;

use App\Services\DatabaseService;
use App\Services\ConfigService;

/**
 * 数据库配置管理服务
 * 将系统配置从.env文件迁移到数据库管理
 * 支持实时配置更新、版本控制和配置加密
 */
class DatabaseConfigService
{
    private DatabaseService $db;
    private ConfigService $config;
    private array $configCache = [];
    private string $encryptionKey;

    public function __construct(DatabaseService $db, ConfigService $config)
    {
        $this->db = $db;
        $this->config = $config;
        $this->encryptionKey = $this->generateEncryptionKey();
        $this->initializeConfigTables();
        $this->migrateFromEnvFile();
    }

    /**
     * 初始化配置数据表
     */
    private function initializeConfigTables(): void
    {
        $this->createConfigTables();
        $this->createConfigVersionTables();
        $this->createConfigAuditTables();
    }

    /**
     * 创建配置相关数据表
     */
    private function createConfigTables(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `system_config` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `config_key` varchar(255) NOT NULL,
            `config_value` text,
            `config_type` enum('string','integer','boolean','json','encrypted') DEFAULT 'string',
            `category` varchar(100) DEFAULT 'general',
            `description` text,
            `is_encrypted` tinyint(1) DEFAULT 0,
            `is_sensitive` tinyint(1) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_by` int(11) DEFAULT NULL,
            `updated_by` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `config_key` (`config_key`),
            KEY `category` (`category`),
            KEY `is_sensitive` (`is_sensitive`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `config_versions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `config_key` varchar(255) NOT NULL,
            `old_value` text,
            `new_value` text,
            `version` int(11) NOT NULL DEFAULT 1,
            `change_reason` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `created_by` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `config_key` (`config_key`),
            KEY `version` (`version`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `config_audit_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `config_key` varchar(255) NOT NULL,
            `action` enum('create','update','delete','read') NOT NULL,
            `old_value` text,
            `new_value` text,
            `user_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45),
            `user_agent` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `config_key` (`config_key`),
            KEY `action` (`action`),
            KEY `user_id` (`user_id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $this->db->executeStatement($sql);
    }

    /**
     * 从.env文件迁移配置到数据库
     */
    private function migrateFromEnvFile(): void
    {
        $envPath = BASE_PATH . '/.env';
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);
        $envLines = explode("\n", $envContent);

        foreach ($envLines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '"\'');

                $this->migrateConfigItem($key, $value);
            }
        }

        // 创建.env备份
        $this->createEnvBackup();
    }

    /**
     * 迁移单个配置项
     */
    private function migrateConfigItem(string $key, string $value): void
    {
        // 检查配置是否已存在
        $existing = $this->db->fetchOne(
            "SELECT id FROM system_config WHERE config_key = ?",
            [$key]
        );

        if ($existing) {
            return; // 已存在，跳过
        }

        $category = $this->categorizeConfigKey($key);
        $isSensitive = $this->isSensitiveConfig($key);
        $configType = $this->determineConfigType($value);

        // 敏感信息加密处理
        if ($isSensitive) {
            $value = $this->encryptValue($value);
        }

        $this->db->executeStatement(
            "INSERT INTO system_config (config_key, config_value, config_type, category, is_encrypted, is_sensitive, description) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $key,
                $value,
                $configType,
                $category,
                $isSensitive ? 1 : 0,
                $isSensitive ? 1 : 0,
                $this->generateConfigDescription($key)
            ]
        );

        $this->logConfigChange($key, 'create', null, $value, 0, $this->getClientInfo());
    }

    /**
     * 获取配置值
     */
    public function get(string $key, $default = null)
    {
        // 检查缓存
        if (isset($this->configCache[$key])) {
            return $this->configCache[$key];
        }

        $config = $this->db->fetchOne(
            "SELECT config_value, config_type, is_encrypted FROM system_config WHERE config_key = ?",
            [$key]
        );

        if (!$config) {
            return $default;
        }

        $value = $config['config_value'];

        // 解密敏感信息
        if ($config['is_encrypted']) {
            $value = $this->decryptValue($value);
        }

        // 类型转换
        $value = $this->convertConfigValue($value, $config['config_type']);

        // 缓存配置
        $this->configCache[$key] = $value;

        // 记录读取日志
        $this->logConfigChange($key, 'read', null, null, 0, $this->getClientInfo());

        return $value;
    }

    /**
     * 设置配置值
     */
    public function set(string $key, $value, int $userId = 0, string $reason = ''): bool
    {
        $oldValue = $this->get($key);
        $category = $this->categorizeConfigKey($key);
        $isSensitive = $this->isSensitiveConfig($key);
        $configType = $this->determineConfigType($value);

        // 敏感信息加密
        $encryptedValue = $isSensitive ? $this->encryptValue($value) : $value;

        // 更新或插入配置
        $existing = $this->db->fetchOne(
            "SELECT id FROM system_config WHERE config_key = ?",
            [$key]
        );

        if ($existing) {
            // 更新现有配置
            $result = $this->db->executeStatement(
                "UPDATE system_config SET 
                 config_value = ?, config_type = ?, updated_by = ?, updated_at = NOW() 
                 WHERE config_key = ?",
                [$encryptedValue, $configType, $userId, $key]
            );
        } else {
            // 插入新配置
            $result = $this->db->executeStatement(
                "INSERT INTO system_config 
                 (config_key, config_value, config_type, category, is_encrypted, is_sensitive, created_by, description) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $key, $encryptedValue, $configType, $category,
                    $isSensitive ? 1 : 0, $isSensitive ? 1 : 0,
                    $userId, $this->generateConfigDescription($key)
                ]
            );
        }

        if ($result) {
            // 记录版本历史
            $this->recordConfigVersion($key, $oldValue, $value, $reason, $userId);
            
            // 记录审计日志
            $this->logConfigChange($key, $existing ? 'update' : 'create', $oldValue, $value, $userId, $this->getClientInfo());
            
            // 清除缓存
            unset($this->configCache[$key]);
        }

        return $result !== false;
    }

    /**
     * 删除配置
     */
    public function delete(string $key, int $userId = 0, string $reason = ''): bool
    {
        $oldValue = $this->get($key);
        
        $result = $this->db->executeStatement(
            "DELETE FROM system_config WHERE config_key = ?",
            [$key]
        );

        if ($result) {
            // 记录版本历史
            $this->recordConfigVersion($key, $oldValue, null, $reason, $userId);
            
            // 记录审计日志
            $this->logConfigChange($key, 'delete', $oldValue, null, $userId, $this->getClientInfo());
            
            // 清除缓存
            unset($this->configCache[$key]);
        }

        return $result !== false;
    }

    /**
     * 获取分类配置
     */
    public function getByCategory(string $category): array
    {
        $configs = $this->db->fetchAll(
            "SELECT config_key, config_value, config_type, is_encrypted, description 
             FROM system_config WHERE category = ? ORDER BY config_key",
            [$category]
        );

        $result = [];
        foreach ($configs as $config) {
            $value = $config['config_value'];
            
            if ($config['is_encrypted']) {
                $value = $this->decryptValue($value);
            }
            
            $result[$config['config_key']] = $this->convertConfigValue($value, $config['config_type']);
        }

        return $result;
    }

    /**
     * 获取所有配置分类
     */
    public function getCategories(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT category, COUNT(*) as count 
             FROM system_config GROUP BY category ORDER BY category"
        );
    }

    /**
     * 获取配置版本历史
     */
    public function getConfigHistory(string $key, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM config_versions 
             WHERE config_key = ? ORDER BY version DESC LIMIT ?",
            [$key, $limit]
        );
    }

    /**
     * 回滚配置到指定版本
     */
    public function rollbackToVersion(string $key, int $version, int $userId = 0): bool
    {
        $versionData = $this->db->fetchOne(
            "SELECT old_value FROM config_versions WHERE config_key = ? AND version = ?",
            [$key, $version]
        );

        if (!$versionData) {
            return false;
        }

        return $this->set($key, $versionData['old_value'], $userId, "回滚到版本 {$version}");
    }

    /**
     * 导出配置到文件
     */
    public function exportToFile(string $filePath = null): string
    {
        $filePath = $filePath ?: BASE_PATH . '/storage/config_backup_' . date('Y-m-d_H-i-s') . '.json';
        
        $configs = $this->db->fetchAll(
            "SELECT config_key, config_value, config_type, category, is_encrypted, description 
             FROM system_config ORDER BY category, config_key"
        );

        $exportData = [
            'export_time' => date('Y-m-d H:i:s'),
            'total_configs' => count($configs),
            'configs' => []
        ];

        foreach ($configs as $config) {
            $value = $config['config_value'];
            
            // 敏感信息不导出实际值
            if ($config['is_encrypted']) {
                $value = '[ENCRYPTED]';
            }
            
            $exportData['configs'][] = [
                'key' => $config['config_key'],
                'value' => $value,
                'type' => $config['config_type'],
                'category' => $config['category'],
                'description' => $config['description']
            ];
        }

        file_put_contents($filePath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $filePath;
    }

    /**
     * 批量更新配置
     */
    public function batchUpdate(array $configs, int $userId = 0): array
    {
        $results = [];
        
        foreach ($configs as $key => $value) {
            $results[$key] = $this->set($key, $value, $userId, '批量更新');
        }
        
        return $results;
    }

    /**
     * 获取配置统计信息
     */
    public function getStatistics(): array
    {
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_configs,
                COUNT(CASE WHEN is_sensitive = 1 THEN 1 END) as sensitive_configs,
                COUNT(CASE WHEN is_encrypted = 1 THEN 1 END) as encrypted_configs,
                COUNT(DISTINCT category) as categories
             FROM system_config"
        );

        $stats['recent_changes'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM config_audit_log 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['count'];

        return $stats;
    }

    // 私有辅助方法

    private function categorizeConfigKey(string $key): string
    {
        $categoryMap = [
            'DB_' => 'database',
            'REDIS_' => 'cache',
            'MAIL_' => 'email',
            'APP_' => 'application',
            'DEEPSEEK_' => 'ai',
            'WEBSOCKET_' => 'websocket',
            'SECURITY_' => 'security',
            'API_' => 'api'
        ];

        foreach ($categoryMap as $prefix => $category) {
            if (strpos($key, $prefix) === 0) {
                return $category;
            }
        }

        return 'general';
    }

    private function isSensitiveConfig(string $key): bool
    {
        $sensitiveKeys = [
            'PASSWORD', 'SECRET', 'KEY', 'TOKEN', 'PRIVATE'
        ];

        foreach ($sensitiveKeys as $sensitive) {
            if (strpos(strtoupper($key), $sensitive) !== false) {
                return true;
            }
        }

        return false;
    }

    private function determineConfigType($value): string
    {
        if (is_bool($value) || in_array(strtolower($value), ['true', 'false'])) {
            return 'boolean';
        }
        
        if (is_numeric($value)) {
            return 'integer';
        }
        
        if (is_array($value) || (is_string($value) && json_decode($value) !== null)) {
            return 'json';
        }
        
        return 'string';
    }

    private function convertConfigValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value
        };
    }

    private function encryptValue(string $value): string
    {
        return base64_encode(openssl_encrypt($value, 'AES-256-CBC', $this->encryptionKey, 0, substr(hash('sha256', $this->encryptionKey), 0, 16)));
    }

    private function decryptValue(string $encryptedValue): string
    {
        return openssl_decrypt(base64_decode($encryptedValue), 'AES-256-CBC', $this->encryptionKey, 0, substr(hash('sha256', $this->encryptionKey), 0, 16));
    }

    private function generateEncryptionKey(): string
    {
        return hash('sha256', $this->config->get('app.key', 'default-key') . 'config-encryption');
    }

    private function recordConfigVersion(string $key, $oldValue, $newValue, string $reason, int $userId): void
    {
        $version = $this->db->fetchOne(
            "SELECT COALESCE(MAX(version), 0) + 1 as next_version FROM config_versions WHERE config_key = ?",
            [$key]
        )['next_version'];

        $this->db->executeStatement(
            "INSERT INTO config_versions (config_key, old_value, new_value, version, change_reason, created_by) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$key, json_encode($oldValue), json_encode($newValue), $version, $reason, $userId]
        );
    }

    private function logConfigChange(string $key, string $action, $oldValue, $newValue, int $userId, array $clientInfo): void
    {
        $this->db->executeStatement(
            "INSERT INTO config_audit_log (config_key, action, old_value, new_value, user_id, ip_address, user_agent) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $key, $action, 
                json_encode($oldValue), json_encode($newValue), 
                $userId, $clientInfo['ip'], $clientInfo['user_agent']
            ]
        );
    }

    private function getClientInfo(): array
    {
        return [
            'ip' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
    }

    private function generateConfigDescription(string $key): string
    {
        $descriptions = [
            'DB_HOST' => '数据库主机地址',
            'DB_DATABASE' => '数据库名称',
            'DB_USERNAME' => '数据库用户名',
            'DB_PASSWORD' => '数据库密码',
            'REDIS_HOST' => 'Redis缓存服务器地址',
            'MAIL_HOST' => '邮件服务器地址',
            'DEEPSEEK_API_KEY' => 'DeepSeek AI API密钥',
            // 添加更多描述...
        ];

        return $descriptions[$key] ?? "系统配置项: {$key}";
    }

    private function createEnvBackup(): void
    {
        $envPath = BASE_PATH . '/.env';
        $backupPath = BASE_PATH . '/.env.backup.' . date('Y-m-d_H-i-s');
        
        if (file_exists($envPath)) {
            copy($envPath, $backupPath);
        }
    }

    private function createConfigVersionTables(): void
    {
        // 已在 createConfigTables 中实现
    }

    private function createConfigAuditTables(): void
    {
        // 已在 createConfigTables 中实现
    }
}
