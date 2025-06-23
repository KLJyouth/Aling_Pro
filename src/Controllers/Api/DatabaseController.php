<?php
/**
 * AlingAi Pro Database API Controller
 * 数据库管理API控制器
 * 
 * @package AlingAi\Controllers\Api
 * @author AlingAi Team
 * @version 1.0.0
 */

namespace AlingAi\Controllers\Api;

use PDO;
use PDOException;
use Exception;

class DatabaseController
{
    private $config;
    private $pdo;
    
    public function __construct()
    {
        $this->config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'alingai_pro',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ];
    }
    
    /**
     * 测试数据库连接
     */
    public function testConnection()
    {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};charset={$this->config['charset']}";
            $pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']} COLLATE {$this->config['collation']}"
            ]);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => '数据库连接成功',
                'server_info' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO),
                'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
            ]);
        } catch (PDOException $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => '数据库连接失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 初始化数据库
     */
    public function initializeDatabase()
    {
        try {
            $this->connect();
            
            // 创建数据库
            $this->createDatabase();
            
            // 创建表结构
            $this->createTables();
            
            // 插入初始数据
            $this->seedDatabase();
            
            return $this->jsonResponse([
                'success' => true,
                'message' => '数据库初始化成功',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => '数据库初始化失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取数据库状态
     */
    public function getStatus()
    {
        try {
            $this->connect();
            
            $status = [
                'connected' => true,
                'database' => $this->config['database'],
                'tables' => [],
                'version' => $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
                'charset' => $this->config['charset']
            ];
            
            // 检查表是否存在
            $requiredTables = ['users', 'conversations', 'documents', 'user_logs', 'password_resets', 'api_tokens'];
            
            foreach ($requiredTables as $table) {
                $exists = $this->tableExists($table);
                $status['tables'][$table] = [
                    'exists' => $exists,
                    'count' => $exists ? $this->getTableRowCount($table) : 0
                ];
            }
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $status
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => '获取数据库状态失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 连接数据库
     */
    private function connect()
    {
        if ($this->pdo) {
            return;
        }
        
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};charset={$this->config['charset']}";
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']} COLLATE {$this->config['collation']}"
            ]);
        } catch (PDOException $e) {
            throw new Exception('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 创建数据库
     */
    private function createDatabase()
    {
        $sql = "CREATE DATABASE IF NOT EXISTS `{$this->config['database']}` 
                CHARACTER SET {$this->config['charset']} 
                COLLATE {$this->config['collation']}";
        
        $this->pdo->exec($sql);
        $this->pdo->exec("USE `{$this->config['database']}`");
    }
    
    /**
     * 创建表结构
     */
    private function createTables()
    {
        $tables = [
            'users' => $this->getUsersTableSQL(),
            'conversations' => $this->getConversationsTableSQL(),
            'documents' => $this->getDocumentsTableSQL(),
            'user_logs' => $this->getUserLogsTableSQL(),
            'password_resets' => $this->getPasswordResetsTableSQL(),
            'api_tokens' => $this->getApiTokensTableSQL()
        ];
        
        foreach ($tables as $tableName => $sql) {
            $this->pdo->exec($sql);
        }
    }
    
    /**
     * 填充初始数据
     */
    private function seedDatabase()
    {
        // 创建默认管理员用户
        $adminUser = [
            'username' => 'admin',
            'email' => 'admin@alingai.pro',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'is_active' => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $sql = "INSERT IGNORE INTO users (username, email, password, role, is_active, email_verified_at, created_at, updated_at) 
                VALUES (:username, :email, :password, :role, :is_active, :email_verified_at, :created_at, :updated_at)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($adminUser);
    }
    
    /**
     * 检查表是否存在
     */
    private function tableExists($tableName)
    {
        $sql = "SELECT COUNT(*) FROM information_schema.tables 
                WHERE table_schema = :database AND table_name = :table";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'database' => $this->config['database'],
            'table' => $tableName
        ]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * 获取表行数
     */
    private function getTableRowCount($tableName)
    {
        try {
            $sql = "SELECT COUNT(*) FROM `{$tableName}`";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * 用户表结构
     */
    private function getUsersTableSQL()
    {
        return "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `email` varchar(100) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `first_name` varchar(50) DEFAULT NULL,
            `last_name` varchar(50) DEFAULT NULL,
            `avatar` varchar(255) DEFAULT NULL,
            `role` enum('admin', 'user', 'moderator') DEFAULT 'user',
            `is_active` tinyint(1) DEFAULT 1,
            `email_verified_at` timestamp NULL DEFAULT NULL,
            `last_login_at` timestamp NULL DEFAULT NULL,
            `last_login_ip` varchar(45) DEFAULT NULL,
            `login_count` int(11) DEFAULT 0,
            `preferences` json DEFAULT NULL,
            `metadata` json DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_username` (`username`),
            KEY `idx_email` (`email`),
            KEY `idx_role` (`role`),
            KEY `idx_is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * 对话表结构
     */
    private function getConversationsTableSQL()
    {
        return "CREATE TABLE IF NOT EXISTS `conversations` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(11) unsigned NOT NULL,
            `title` varchar(255) DEFAULT '新对话',
            `description` text DEFAULT NULL,
            `type` enum('chat', 'document', 'code', 'creative', 'analysis') DEFAULT 'chat',
            `status` enum('active', 'archived', 'paused', 'completed') DEFAULT 'active',
            `context` json DEFAULT NULL,
            `messages` json DEFAULT NULL,
            `metadata` json DEFAULT NULL,
            `settings` json DEFAULT NULL,
            `tags` json DEFAULT NULL,
            `is_favorite` tinyint(1) DEFAULT 0,
            `is_public` tinyint(1) DEFAULT 0,
            `share_token` varchar(64) DEFAULT NULL UNIQUE,
            `view_count` int(11) DEFAULT 0,
            `rating` tinyint(4) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_type` (`type`),
            KEY `idx_status` (`status`),
            KEY `idx_is_public` (`is_public`),
            KEY `idx_share_token` (`share_token`),
            KEY `idx_created_at` (`created_at`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * 文档表结构
     */
    private function getDocumentsTableSQL()
    {
        return "CREATE TABLE IF NOT EXISTS `documents` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(11) unsigned NOT NULL,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `filename` varchar(255) DEFAULT NULL,
            `original_filename` varchar(255) DEFAULT NULL,
            `mime_type` varchar(100) DEFAULT NULL,
            `file_size` bigint(20) DEFAULT NULL,
            `file_path` varchar(500) DEFAULT NULL,
            `type` enum('text', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'image', 'video', 'audio', 'other') DEFAULT 'text',
            `status` enum('uploading', 'processing', 'active', 'archived', 'failed') DEFAULT 'active',
            `content` longtext DEFAULT NULL,
            `metadata` json DEFAULT NULL,
            `tags` json DEFAULT NULL,
            `analysis_result` json DEFAULT NULL,
            `is_public` tinyint(1) DEFAULT 0,
            `share_token` varchar(64) DEFAULT NULL UNIQUE,
            `view_count` int(11) DEFAULT 0,
            `download_count` int(11) DEFAULT 0,
            `rating` tinyint(4) DEFAULT NULL,
            `processed_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_type` (`type`),
            KEY `idx_status` (`status`),
            KEY `idx_is_public` (`is_public`),
            KEY `idx_share_token` (`share_token`),
            KEY `idx_created_at` (`created_at`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * 用户日志表结构
     */
    private function getUserLogsTableSQL()
    {
        return "CREATE TABLE IF NOT EXISTS `user_logs` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(11) unsigned DEFAULT NULL,
            `action` varchar(100) NOT NULL,
            `description` text DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` varchar(500) DEFAULT NULL,
            `data` json DEFAULT NULL,
            `level` enum('debug', 'info', 'warning', 'error', 'critical') DEFAULT 'info',
            `module` varchar(50) DEFAULT NULL,
            `method` varchar(10) DEFAULT NULL,
            `url` varchar(500) DEFAULT NULL,
            `response_code` int(11) DEFAULT NULL,
            `response_time` float DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_action` (`action`),
            KEY `idx_level` (`level`),
            KEY `idx_ip_address` (`ip_address`),
            KEY `idx_created_at` (`created_at`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * 密码重置表结构
     */
    private function getPasswordResetsTableSQL()
    {
        return "CREATE TABLE IF NOT EXISTS `password_resets` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `email` varchar(100) NOT NULL,
            `token` varchar(255) NOT NULL,
            `expires_at` timestamp NOT NULL,
            `used` tinyint(1) DEFAULT 0,
            `used_at` timestamp NULL DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_email` (`email`),
            KEY `idx_token` (`token`),
            KEY `idx_expires_at` (`expires_at`),
            KEY `idx_email_token` (`email`, `token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * API令牌表结构
     */
    private function getApiTokensTableSQL()
    {
        return "CREATE TABLE IF NOT EXISTS `api_tokens` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(11) unsigned NOT NULL,
            `name` varchar(100) NOT NULL,
            `token` varchar(64) NOT NULL UNIQUE,
            `abilities` json DEFAULT NULL,
            `last_used_at` timestamp NULL DEFAULT NULL,
            `expires_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_token` (`token`),
            KEY `idx_expires_at` (`expires_at`),
            KEY `idx_user_name` (`user_id`, `name`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    
    /**
     * 返回JSON响应
     */
    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = array_merge([
            'timestamp' => date('c'),
            'status' => $status
        ], $data);
        
        return json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * 处理API路由
     */
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        
        // 移除 'api' 和 'database' 前缀
        if (count($pathParts) >= 3 && $pathParts[0] === 'api' && $pathParts[1] === 'database') {
            $action = $pathParts[2] ?? '';
        } else {
            $action = $pathParts[0] ?? '';
        }
        
        try {
            switch ($action) {
                case 'test':
                    return $this->testConnection();
                case 'init':
                    return $this->initializeDatabase();
                case 'status':
                    return $this->getStatus();
                default:
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => '无效的API端点'
                    ], 404);
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

// 如果直接访问此文件，处理API请求
if (basename($_SERVER['SCRIPT_NAME']) === 'DatabaseController.php') {
    $controller = new DatabaseController();
    echo $controller->handleRequest();
}
