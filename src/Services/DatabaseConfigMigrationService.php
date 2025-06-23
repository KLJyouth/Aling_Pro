<?php

namespace App\Services;

use App\Services\DatabaseService;
use App\Services\LogService;
use App\Services\ConfigService;

/**
 * 数据库配置迁移服务
 * 将.env配置迁移到数据库统一管理
 */
class DatabaseConfigMigrationService
{
    private DatabaseService $db;
    private LogService $logger;
    private ConfigService $config;
    private string $envFilePath;
    private array $migrationMap = [];

    public function __construct(
        DatabaseService $db,
        LogService $logger,
        ConfigService $config
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = $config;
        $this->envFilePath = dirname(__DIR__, 2) . '/.env';
        
        $this->initializeMigrationMap();
        $this->ensureConfigTableExists();
    }

    /**
     * 执行完整的配置迁移
     */
    public function executeMigration(): array
    {
        $this->logger->info('开始执行数据库配置迁移');
        
        $results = [
            'success' => false,
            'migrated_configs' => 0,
            'skipped_configs' => 0,
            'errors' => [],
            'backup_created' => false,
            'migration_time' => date('Y-m-d H:i:s')
        ];

        try {
            // 1. 创建.env备份
            $backupResult = $this->createEnvBackup();
            $results['backup_created'] = $backupResult['success'];
            
            if (!$backupResult['success']) {
                throw new \Exception('创建.env备份失败: ' . $backupResult['error']);
            }

            // 2. 读取.env配置
            $envConfigs = $this->parseEnvFile();
            
            // 3. 迁移配置到数据库
            foreach ($envConfigs as $key => $value) {
                $migrationResult = $this->migrateConfig($key, $value);
                
                if ($migrationResult['success']) {
                    $results['migrated_configs']++;
                } else {
                    $results['skipped_configs']++;
                    $results['errors'][] = $migrationResult['error'];
                }
            }
            
            // 4. 验证迁移结果
            $validationResult = $this->validateMigration($envConfigs);
            
            if (!$validationResult['success']) {
                throw new \Exception('迁移验证失败: ' . $validationResult['error']);
            }
            
            // 5. 创建新的.env文件（仅保留数据库连接信息）
            $this->createNewEnvFile();
            
            $results['success'] = true;
            $this->logger->info('数据库配置迁移完成', $results);
            
        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->logger->error('数据库配置迁移失败', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * 创建.env备份
     */
    private function createEnvBackup(): array
    {
        try {
            if (!file_exists($this->envFilePath)) {
                return ['success' => false, 'error' => '.env文件不存在'];
            }

            $backupPath = $this->envFilePath . '.backup.' . date('Y-m-d_H-i-s');
            $copyResult = copy($this->envFilePath, $backupPath);
            
            if (!$copyResult) {
                return ['success' => false, 'error' => '无法创建备份文件'];
            }

            $this->logger->info('已创建.env备份', ['backup_path' => $backupPath]);
            return ['success' => true, 'backup_path' => $backupPath];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 解析.env文件
     */
    private function parseEnvFile(): array
    {
        $configs = [];
        
        if (!file_exists($this->envFilePath)) {
            return $configs;
        }

        $lines = file($this->envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 跳过注释和空行
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // 解析键值对
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '"\'');
                
                $configs[$key] = $value;
            }
        }

        return $configs;
    }

    /**
     * 迁移单个配置项
     */
    private function migrateConfig(string $key, string $value): array
    {
        try {
            // 检查是否应该迁移此配置
            if (!$this->shouldMigrateConfig($key)) {
                return ['success' => false, 'error' => "配置项 {$key} 不在迁移范围内"];
            }

            // 获取配置分类和描述
            $category = $this->getConfigCategory($key);
            $description = $this->getConfigDescription($key);
            
            // 加密敏感配置
            $encryptedValue = $this->encryptConfigValue($key, $value);
            
            // 检查配置是否已存在
            $existingConfig = $this->db->queryRow(
                "SELECT id FROM system_configs WHERE config_key = ?",
                [$key]
            );

            if ($existingConfig) {
                // 更新现有配置
                $this->db->update('system_configs', [
                    'config_value' => $encryptedValue,
                    'category' => $category,
                    'description' => $description,
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['config_key' => $key]);
            } else {
                // 插入新配置
                $this->db->insert('system_configs', [
                    'config_key' => $key,
                    'config_value' => $encryptedValue,
                    'category' => $category,
                    'description' => $description,
                    'is_encrypted' => $this->isEncryptedConfig($key) ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 验证迁移结果
     */
    private function validateMigration(array $originalConfigs): array
    {
        try {
            $migratedCount = 0;
            $validationErrors = [];

            foreach ($originalConfigs as $key => $originalValue) {
                if (!$this->shouldMigrateConfig($key)) {
                    continue;
                }

                // 从数据库读取配置
                $dbConfig = $this->db->queryRow(
                    "SELECT config_value, is_encrypted FROM system_configs WHERE config_key = ?",
                    [$key]
                );

                if (!$dbConfig) {
                    $validationErrors[] = "配置项 {$key} 未在数据库中找到";
                    continue;
                }

                // 解密并验证值
                $dbValue = $dbConfig['is_encrypted'] ? 
                    $this->decryptConfigValue($dbConfig['config_value']) : 
                    $dbConfig['config_value'];

                if ($dbValue !== $originalValue) {
                    $validationErrors[] = "配置项 {$key} 值不匹配";
                    continue;
                }

                $migratedCount++;
            }

            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'error' => '验证失败: ' . implode(', ', $validationErrors)
                ];
            }

            return [
                'success' => true,
                'migrated_count' => $migratedCount
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 创建新的.env文件（仅保留数据库连接）
     */
    private function createNewEnvFile(): void
    {
        $essentialConfigs = [
            'DB_HOST' => $this->config->get('database.host', 'localhost'),
            'DB_PORT' => $this->config->get('database.port', '3306'),
            'DB_DATABASE' => $this->config->get('database.database', 'alingai_pro'),
            'DB_USERNAME' => $this->config->get('database.username', 'root'),
            'DB_PASSWORD' => $this->config->get('database.password', ''),
            'DB_CHARSET' => $this->config->get('database.charset', 'utf8mb4')
        ];

        $newEnvContent = "# AlingAi Pro 数据库连接配置\n";
        $newEnvContent .= "# 其他配置已迁移到数据库统一管理\n\n";

        foreach ($essentialConfigs as $key => $value) {
            $newEnvContent .= "{$key}={$value}\n";
        }

        $newEnvContent .= "\n# 配置管理模式\n";
        $newEnvContent .= "CONFIG_MODE=database\n";
        $newEnvContent .= "CONFIG_MIGRATION_DATE=" . date('Y-m-d H:i:s') . "\n";

        file_put_contents($this->envFilePath, $newEnvContent);
        
        $this->logger->info('已创建新的.env文件', ['content_length' => strlen($newEnvContent)]);
    }

    /**
     * 确保配置表存在
     */
    private function ensureConfigTableExists(): void
    {
        $createTableSql = "
            CREATE TABLE IF NOT EXISTS system_configs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_key VARCHAR(255) NOT NULL UNIQUE,
                config_value TEXT,
                category VARCHAR(100) DEFAULT 'general',
                description TEXT,
                is_encrypted TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category (category),
                INDEX idx_key (config_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        try {
            $this->db->execute($createTableSql);
        } catch (\Exception $e) {
            $this->logger->error('创建配置表失败', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 初始化迁移映射
     */
    private function initializeMigrationMap(): void
    {
        $this->migrationMap = [
            // 应用配置
            'APP_NAME' => ['category' => 'application', 'description' => '应用名称', 'encrypt' => false],
            'APP_ENV' => ['category' => 'application', 'description' => '应用环境', 'encrypt' => false],
            'APP_DEBUG' => ['category' => 'application', 'description' => '调试模式', 'encrypt' => false],
            'APP_URL' => ['category' => 'application', 'description' => '应用URL', 'encrypt' => false],
            
            // API配置
            'DEEPSEEK_API_KEY' => ['category' => 'api', 'description' => 'DeepSeek API密钥', 'encrypt' => true],
            'DEEPSEEK_API_URL' => ['category' => 'api', 'description' => 'DeepSeek API地址', 'encrypt' => false],
            'OPENAI_API_KEY' => ['category' => 'api', 'description' => 'OpenAI API密钥', 'encrypt' => true],
            
            // 缓存配置
            'CACHE_DRIVER' => ['category' => 'cache', 'description' => '缓存驱动', 'encrypt' => false],
            'REDIS_HOST' => ['category' => 'cache', 'description' => 'Redis主机', 'encrypt' => false],
            'REDIS_PORT' => ['category' => 'cache', 'description' => 'Redis端口', 'encrypt' => false],
            'REDIS_PASSWORD' => ['category' => 'cache', 'description' => 'Redis密码', 'encrypt' => true],
            
            // 邮件配置
            'MAIL_MAILER' => ['category' => 'mail', 'description' => '邮件驱动', 'encrypt' => false],
            'MAIL_HOST' => ['category' => 'mail', 'description' => '邮件服务器', 'encrypt' => false],
            'MAIL_PORT' => ['category' => 'mail', 'description' => '邮件端口', 'encrypt' => false],
            'MAIL_USERNAME' => ['category' => 'mail', 'description' => '邮件用户名', 'encrypt' => true],
            'MAIL_PASSWORD' => ['category' => 'mail', 'description' => '邮件密码', 'encrypt' => true],
            
            // 安全配置
            'JWT_SECRET' => ['category' => 'security', 'description' => 'JWT密钥', 'encrypt' => true],
            'ENCRYPTION_KEY' => ['category' => 'security', 'description' => '加密密钥', 'encrypt' => true],
            'SESSION_DRIVER' => ['category' => 'security', 'description' => '会话驱动', 'encrypt' => false],
            
            // WebSocket配置
            'WEBSOCKET_HOST' => ['category' => 'websocket', 'description' => 'WebSocket主机', 'encrypt' => false],
            'WEBSOCKET_PORT' => ['category' => 'websocket', 'description' => 'WebSocket端口', 'encrypt' => false],
            
            // 日志配置
            'LOG_CHANNEL' => ['category' => 'logging', 'description' => '日志通道', 'encrypt' => false],
            'LOG_LEVEL' => ['category' => 'logging', 'description' => '日志级别', 'encrypt' => false],
        ];
    }

    /**
     * 检查是否应该迁移配置
     */
    private function shouldMigrateConfig(string $key): bool
    {
        // 数据库连接配置不迁移
        $databaseKeys = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_CHARSET'];
        
        if (in_array($key, $databaseKeys)) {
            return false;
        }

        return isset($this->migrationMap[$key]);
    }

    /**
     * 获取配置分类
     */
    private function getConfigCategory(string $key): string
    {
        return $this->migrationMap[$key]['category'] ?? 'general';
    }

    /**
     * 获取配置描述
     */
    private function getConfigDescription(string $key): string
    {
        return $this->migrationMap[$key]['description'] ?? $key;
    }

    /**
     * 检查是否为加密配置
     */
    private function isEncryptedConfig(string $key): bool
    {
        return $this->migrationMap[$key]['encrypt'] ?? false;
    }

    /**
     * 加密配置值
     */
    private function encryptConfigValue(string $key, string $value): string
    {
        if (!$this->isEncryptedConfig($key)) {
            return $value;
        }

        // 使用AES-256-GCM加密
        $method = 'AES-256-GCM';
        $encryptionKey = $this->getEncryptionKey();
        $iv = random_bytes(16);
        $tag = '';
        
        $encrypted = openssl_encrypt($value, $method, $encryptionKey, 0, $iv, $tag);
        
        if ($encrypted === false) {
            throw new \Exception('配置值加密失败');
        }

        // 返回base64编码的加密数据
        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * 解密配置值
     */
    private function decryptConfigValue(string $encryptedValue): string
    {
        $method = 'AES-256-GCM';
        $encryptionKey = $this->getEncryptionKey();
        
        $data = base64_decode($encryptedValue);
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        
        $decrypted = openssl_decrypt($encrypted, $method, $encryptionKey, 0, $iv, $tag);
        
        if ($decrypted === false) {
            throw new \Exception('配置值解密失败');
        }

        return $decrypted;
    }

    /**
     * 获取加密密钥
     */
    private function getEncryptionKey(): string
    {
        // 从环境变量或生成默认密钥
        $key = $this->config->get('encryption.key');
        
        if (empty($key)) {
            // 基于应用特定信息生成密钥
            $key = hash('sha256', 'AlingAi_Pro_Config_Encryption_' . $_SERVER['SERVER_NAME'] ?? 'localhost');
        }

        return $key;
    }

    /**
     * 获取迁移状态
     */
    public function getMigrationStatus(): array
    {
        try {
            $totalConfigs = $this->db->queryRow("SELECT COUNT(*) as count FROM system_configs");
            $configsByCategory = $this->db->query("
                SELECT category, COUNT(*) as count 
                FROM system_configs 
                GROUP BY category
            ");
            
            $encryptedConfigs = $this->db->queryRow("
                SELECT COUNT(*) as count 
                FROM system_configs 
                WHERE is_encrypted = 1
            ");

            return [
                'total_configs' => $totalConfigs['count'] ?? 0,
                'configs_by_category' => $configsByCategory,
                'encrypted_configs' => $encryptedConfigs['count'] ?? 0,
                'last_update' => $this->db->queryRow("
                    SELECT MAX(updated_at) as last_update 
                    FROM system_configs
                ")['last_update'] ?? null
            ];
            
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 回滚迁移
     */
    public function rollbackMigration(string $backupPath): array
    {
        try {
            if (!file_exists($backupPath)) {
                return ['success' => false, 'error' => '备份文件不存在'];
            }

            // 恢复.env文件
            copy($backupPath, $this->envFilePath);
            
            // 清除数据库配置（可选）
            // $this->db->execute("DELETE FROM system_configs");
            
            $this->logger->info('配置迁移已回滚', ['backup_path' => $backupPath]);
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
