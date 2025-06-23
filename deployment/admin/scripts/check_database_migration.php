<?php
/**
 * 数据库迁移检查脚本
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/EnvLoader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlingAi\Services\DatabaseService;

// 加载环境变量
EnvLoader::load();

echo "=== 数据库迁移检查 ===\n\n";

try {
    $logger = new Logger('migration_check');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/storage/logs/migration.log'));
    
    $dbService = new DatabaseService($logger);
    echo "✓ 数据库连接成功 (类型: " . $dbService->getConnectionType() . ")\n\n";
    
    if ($dbService->getConnectionType() === 'mysql') {
        echo "检查MySQL数据库表结构...\n";
        
        // 检查必需的表
        $requiredTables = [
            'users' => 'CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL UNIQUE,
                `email` varchar(100) NOT NULL UNIQUE,
                `password` varchar(255) NOT NULL,
                `avatar` varchar(255) DEFAULT NULL,
                `level` tinyint(4) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_username` (`username`),
                KEY `idx_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            
            'chat_sessions' => 'CREATE TABLE IF NOT EXISTS `chat_sessions` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(11) unsigned NOT NULL,
                `title` varchar(255) NOT NULL DEFAULT "新对话",
                `model` varchar(50) NOT NULL DEFAULT "deepseek-chat",
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            
            'chat_messages' => 'CREATE TABLE IF NOT EXISTS `chat_messages` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `session_id` int(11) unsigned NOT NULL,
                `user_id` int(11) unsigned NOT NULL,
                `role` enum("user","assistant","system") NOT NULL,
                `content` text NOT NULL,
                `model` varchar(50) DEFAULT NULL,
                `tokens_used` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_session_id` (`session_id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            
            'system_settings' => 'CREATE TABLE IF NOT EXISTS `system_settings` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `key` varchar(100) NOT NULL UNIQUE,
                `value` text,
                `type` enum("string","int","float","bool","json") NOT NULL DEFAULT "string",
                `description` varchar(255) DEFAULT NULL,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_key` (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            
            'system_metrics' => 'CREATE TABLE IF NOT EXISTS `system_metrics` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `metric_type` enum("cpu","memory","disk","network","database") NOT NULL,
                `metric_name` varchar(100) NOT NULL,
                `metric_value` decimal(10,2) NOT NULL,
                `metric_unit` varchar(20) DEFAULT "%",
                `threshold_warning` decimal(10,2) DEFAULT NULL,
                `threshold_critical` decimal(10,2) DEFAULT NULL,
                `status` enum("normal","warning","critical") DEFAULT "normal",
                `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_metric_type_time` (`metric_type`, `recorded_at`),
                KEY `idx_status_time` (`status`, `recorded_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            
            'ai_conversations' => 'CREATE TABLE IF NOT EXISTS `ai_conversations` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(11) unsigned NOT NULL,
                `model_name` varchar(50) NOT NULL,
                `prompt` text NOT NULL,
                `response` text NOT NULL,
                `tokens_used` int(11) DEFAULT NULL,
                `response_time` decimal(8,3) DEFAULT NULL COMMENT "响应时间(秒)",
                `status` enum("success","error","timeout") DEFAULT "success",
                `error_message` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_model_name` (`model_name`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            
            'email_logs' => 'CREATE TABLE IF NOT EXISTS `email_logs` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `to_email` varchar(255) NOT NULL,
                `subject` varchar(255) NOT NULL,
                `body` text NOT NULL,
                `status` enum("pending","sent","failed") DEFAULT "pending",
                `error_message` text DEFAULT NULL,
                `sent_at` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_to_email` (`to_email`),
                KEY `idx_status` (`status`),
                KEY `idx_sent_at` (`sent_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        ];
        
        $connection = $dbService->getConnection();
        
        foreach ($requiredTables as $tableName => $createSql) {
            try {
                $connection->exec($createSql);
                echo "✓ 表 {$tableName} 检查/创建完成\n";
            } catch (Exception $e) {
                echo "✗ 表 {$tableName} 创建失败: " . $e->getMessage() . "\n";
            }
        }
        
        // 插入默认系统设置
        echo "\n检查系统设置...\n";
        $defaultSettings = [
            ['key' => 'system_name', 'value' => 'AlingAi Pro', 'type' => 'string', 'description' => '系统名称'],
            ['key' => 'system_version', 'value' => '2.0.0', 'type' => 'string', 'description' => '系统版本'],
            ['key' => 'default_ai_model', 'value' => 'deepseek-chat', 'type' => 'string', 'description' => '默认AI模型'],
            ['key' => 'max_tokens_per_request', 'value' => '4000', 'type' => 'int', 'description' => '单次请求最大令牌数'],
            ['key' => 'enable_monitoring', 'value' => 'true', 'type' => 'bool', 'description' => '启用系统监控'],
            ['key' => 'monitoring_interval', 'value' => '60', 'type' => 'int', 'description' => '监控间隔(秒)']
        ];
        
        foreach ($defaultSettings as $setting) {
            try {
                $stmt = $connection->prepare("INSERT IGNORE INTO system_settings (`key`, `value`, `type`, `description`) VALUES (?, ?, ?, ?)");
                $stmt->execute([$setting['key'], $setting['value'], $setting['type'], $setting['description']]);
                echo "✓ 系统设置 {$setting['key']} 已配置\n";
            } catch (Exception $e) {
                echo "⚠ 系统设置 {$setting['key']} 配置失败: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n✓ MySQL数据库迁移检查完成\n";
        
    } else {
        echo "使用文件系统数据库，无需迁移\n";
    }
    
    // 检查数据库连通性
    echo "\n数据库连通性测试...\n";
    try {
        $testData = [
            'username' => 'migration_test_' . time(),
            'email' => 'test@migration.local',
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'level' => 1
        ];
        
        $insertResult = $dbService->insert('users', $testData);
        if ($insertResult) {
            echo "✓ 数据插入测试成功\n";
            
            // 查询测试
            $user = $dbService->findAll('users', ['username' => $testData['username']]);
            if (!empty($user)) {
                echo "✓ 数据查询测试成功\n";
                
                // 清理测试数据
                if ($dbService->getConnectionType() === 'mysql') {
                    $connection = $dbService->getConnection();
                    $stmt = $connection->prepare("DELETE FROM users WHERE username = ?");
                    $stmt->execute([$testData['username']]);
                    echo "✓ 测试数据清理完成\n";
                }
            } else {
                echo "⚠ 数据查询测试失败\n";
            }
        } else {
            echo "⚠ 数据插入测试失败\n";
        }
    } catch (Exception $e) {
        echo "⚠ 数据库连通性测试失败: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== 数据库迁移检查完成 ===\n";
    
} catch (Exception $e) {
    echo "✗ 数据库迁移检查失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}