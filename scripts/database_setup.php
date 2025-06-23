<?php
/**
 * 数据库设置和测试脚本
 * 检测数据库连接并执行初始化
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AlingAi\Database\MigrationManager;

class DatabaseSetup {
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/database_local.php';
    }
    
    /**
     * 测试数据库连接
     */
    public function testConnections() {
        echo "🔍 Testing database connections...\n\n";
        
        // 测试 MySQL 连接
        echo "Testing MySQL connection...\n";
        $mysqlResult = $this->testMysqlConnection();
        
        // 测试 SQLite 连接
        echo "Testing SQLite connection...\n";
        $sqliteResult = $this->testSqliteConnection();
        
        return [
            'mysql' => $mysqlResult,
            'sqlite' => $sqliteResult
        ];
    }
    
    /**
     * 测试 MySQL 连接
     */
    private function testMysqlConnection() {
        try {
            $config = $this->config['production'];
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            // 测试查询
            $stmt = $pdo->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            
            echo "✅ MySQL connection successful!\n";
            echo "   Version: {$result['version']}\n";
            echo "   Host: {$config['host']}:{$config['port']}\n";
            echo "   Database: {$config['database']}\n\n";
            
            return [
                'success' => true,
                'version' => $result['version'],
                'pdo' => $pdo
            ];
            
        } catch (Exception $e) {
            echo "❌ MySQL connection failed: " . $e->getMessage() . "\n\n";
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 测试 SQLite 连接
     */
    private function testSqliteConnection() {
        try {
            $config = $this->config['local'];
            
            // 确保目录存在
            $dbDir = dirname($config['database']);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
                echo "📁 Created database directory: $dbDir\n";
            }
            
            $pdo = new PDO("sqlite:" . $config['database'], null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // 测试查询
            $stmt = $pdo->query("SELECT sqlite_version() as version");
            $result = $stmt->fetch();
            
            echo "✅ SQLite connection successful!\n";
            echo "   Version: {$result['version']}\n";
            echo "   Database: {$config['database']}\n\n";
            
            return [
                'success' => true,
                'version' => $result['version'],
                'pdo' => $pdo
            ];
            
        } catch (Exception $e) {
            echo "❌ SQLite connection failed: " . $e->getMessage() . "\n\n";
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 运行数据库迁移
     */
    public function runMigrations($useConnection = 'auto') {
        echo "🚀 Running database migrations...\n\n";
        
        $connections = $this->testConnections();
        
        // 选择连接
        $pdo = null;
        if ($useConnection === 'auto') {
            if ($connections['mysql']['success']) {
                $pdo = $connections['mysql']['pdo'];
                echo "Using MySQL connection for migrations.\n";
            } elseif ($connections['sqlite']['success']) {
                $pdo = $connections['sqlite']['pdo'];
                echo "Using SQLite connection for migrations.\n";
            } else {
                throw new Exception("No database connection available!");
            }
        } elseif ($useConnection === 'mysql' && $connections['mysql']['success']) {
            $pdo = $connections['mysql']['pdo'];
        } elseif ($useConnection === 'sqlite' && $connections['sqlite']['success']) {
            $pdo = $connections['sqlite']['pdo'];
        } else {
            throw new Exception("Requested database connection not available!");
        }
        
        // 执行迁移
        $migrationManager = new MigrationManager($pdo);
        $result = $migrationManager->migrate();
        
        if ($result['success']) {
            echo "✅ Migrations completed successfully!\n";
            echo "   Executed: " . count($result['executed']) . " migrations\n";
            foreach ($result['executed'] as $migration) {
                echo "   - {$migration}\n";
            }
        } else {
            echo "❌ Migration failed: " . $result['error'] . "\n";
        }
        
        return $result;
    }
    
    /**
     * 创建基础表结构
     */
    public function createBasicTables($pdo) {
        echo "🏗️  Creating basic table structure...\n\n";
        
        $tables = [
            // 用户表
            'users' => "
                CREATE TABLE IF NOT EXISTS users (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    full_name VARCHAR(100),
                    avatar_url VARCHAR(255),
                    role ENUM('user', 'admin', 'super_admin') DEFAULT 'user',
                    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
                    email_verified_at TIMESTAMP NULL,
                    last_login_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    
                    INDEX idx_username (username),
                    INDEX idx_email (email),
                    INDEX idx_role (role),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            // 聊天会话表
            'chat_sessions' => "
                CREATE TABLE IF NOT EXISTS chat_sessions (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    model VARCHAR(50) DEFAULT 'gpt-3.5-turbo',
                    system_prompt TEXT,
                    status ENUM('active', 'archived', 'deleted') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user_id (user_id),
                    INDEX idx_status (status),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            // 聊天消息表
            'chat_messages' => "
                CREATE TABLE IF NOT EXISTS chat_messages (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    session_id INT NOT NULL,
                    role ENUM('user', 'assistant', 'system') NOT NULL,
                    content TEXT NOT NULL,
                    metadata JSON,
                    tokens_used INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    
                    FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
                    INDEX idx_session_id (session_id),
                    INDEX idx_role (role),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            // 系统日志表
            'system_logs' => "
                CREATE TABLE IF NOT EXISTS system_logs (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    level VARCHAR(20) NOT NULL,
                    message TEXT NOT NULL,
                    context JSON,
                    user_id INT NULL,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_level (level),
                    INDEX idx_user_id (user_id),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            try {
                // 对于 SQLite，需要调整 SQL
                if (strpos($pdo->getAttribute(PDO::ATTR_DRIVER_NAME), 'sqlite') !== false) {
                    $sql = $this->convertToSqlite($sql, $tableName);
                }
                
                $pdo->exec($sql);
                echo "✅ Created table: $tableName\n";
            } catch (Exception $e) {
                echo "❌ Failed to create table $tableName: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * 转换 MySQL SQL 为 SQLite
     */
    private function convertToSqlite($sql, $tableName) {
        // 基本的 MySQL 到 SQLite 转换
        $sql = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $sql);
        $sql = str_replace('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci', '', $sql);
        $sql = preg_replace('/VARCHAR\((\d+)\)/', 'TEXT', $sql);
        $sql = str_replace('ENUM(', 'TEXT CHECK (', $sql);
        $sql = str_replace('ON UPDATE CURRENT_TIMESTAMP', '', $sql);
        
        return $sql;
    }
    
    /**
     * 插入测试数据
     */
    public function insertTestData($pdo) {
        echo "📊 Inserting test data...\n\n";
        
        try {
            // 创建测试用户
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO users (username, email, password_hash, full_name, role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $testUsers = [
                ['admin', 'admin@alingai.com', password_hash('admin123', PASSWORD_DEFAULT), 'System Administrator', 'super_admin'],
                ['testuser', 'test@alingai.com', password_hash('test123', PASSWORD_DEFAULT), 'Test User', 'user']
            ];
            
            foreach ($testUsers as $user) {
                $stmt->execute($user);
                echo "✅ Created user: {$user[0]}\n";
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Failed to insert test data: " . $e->getMessage() . "\n\n";
        }
    }
}

// 主执行逻辑
try {
    $setup = new DatabaseSetup();
    
    echo "🎯 AlingAi Pro Database Setup\n";
    echo "============================\n\n";
    
    // 测试连接
    $connections = $setup->testConnections();
    
    // 选择可用的连接
    $pdo = null;
    if ($connections['mysql']['success']) {
        $pdo = $connections['mysql']['pdo'];
        echo "Using MySQL for setup...\n\n";
    } elseif ($connections['sqlite']['success']) {
        $pdo = $connections['sqlite']['pdo'];
        echo "Using SQLite for setup...\n\n";
    } else {
        throw new Exception("No database connection available!");
    }
    
    // 创建基础表
    $setup->createBasicTables($pdo);
    
    // 插入测试数据
    $setup->insertTestData($pdo);
    
    echo "🎉 Database setup completed successfully!\n";
    echo "You can now start using the AlingAi Pro application.\n\n";
    
    // 显示登录信息
    echo "Test accounts:\n";
    echo "- Admin: admin@alingai.com / admin123\n";
    echo "- User:  test@alingai.com / test123\n";
    
} catch (Exception $e) {
    echo "💥 Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
