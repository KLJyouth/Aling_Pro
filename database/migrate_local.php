<?php
/**
 * 本地数据库迁移脚本
 * 支持本地 SQLite 数据库的创建和迁移
 */

declare(strict_types=1);

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 定义常量
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

// 加载环境变量
if (file_exists(APP_ROOT . '/.env')) {
    $lines = file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value, '"\'');
        }
    }
}

class LocalDatabaseMigrator
{
    private PDO $pdo;
    private string $dbPath;
    
    public function __construct()
    {
        $this->dbPath = APP_ROOT . '/storage/database/alingai_local.db';
        $this->createDirectories();
        $this->initializeDatabase();
    }
    
    private function createDirectories(): void
    {
        $dbDir = dirname($this->dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
            echo "✓ 创建数据库目录: $dbDir\n";
        }
    }
    
    private function initializeDatabase(): void
    {
        try {
            $this->pdo = new PDO("sqlite:{$this->dbPath}");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // 启用外键约束
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            
            echo "✓ SQLite 数据库连接成功: {$this->dbPath}\n";
        } catch (PDOException $e) {
            echo "✗ 数据库连接失败: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function migrate(): void
    {
        echo "开始数据库迁移...\n\n";
        
        $this->createMigrationsTable();
        $this->runMigrations();
        
        echo "\n✓ 所有迁移完成!\n";
    }
    
    private function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $this->pdo->exec($sql);
        echo "✓ 迁移表已创建\n";
    }
    
    private function runMigrations(): void
    {
        $migrations = [
            '001_create_users_table' => $this->getUsersTableSQL(),
            '002_create_chat_sessions_table' => $this->getChatSessionsTableSQL(),
            '003_create_chat_messages_table' => $this->getChatMessagesTableSQL(),
            '004_create_system_settings_table' => $this->getSystemSettingsTableSQL(),
            '005_create_enhanced_tables' => $this->getEnhancedTablesSQL(),
        ];
        
        foreach ($migrations as $name => $sql) {
            if (!$this->isMigrationExecuted($name)) {
                try {
                    $this->pdo->exec($sql);
                    $this->markMigrationAsExecuted($name);
                    echo "✓ 执行迁移: $name\n";
                } catch (PDOException $e) {
                    echo "✗ 迁移失败 $name: " . $e->getMessage() . "\n";
                }
            } else {
                echo "- 跳过已执行的迁移: $name\n";
            }
        }
    }
    
    private function isMigrationExecuted(string $migration): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function markMigrationAsExecuted(string $migration): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$migration]);
    }
    
    private function getUsersTableSQL(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(255) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                avatar_url VARCHAR(500),
                is_active BOOLEAN DEFAULT 1,
                email_verified_at DATETIME,
                last_login_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
    }
    
    private function getChatSessionsTableSQL(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS chat_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) DEFAULT 'New Chat',
                context TEXT,
                model VARCHAR(100) DEFAULT 'deepseek-chat',
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
    }
    
    private function getChatMessagesTableSQL(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS chat_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                role VARCHAR(20) NOT NULL CHECK (role IN ('user', 'assistant', 'system')),
                content TEXT NOT NULL,
                model VARCHAR(100),
                tokens_used INTEGER DEFAULT 0,
                response_time FLOAT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
    }
    
    private function getSystemSettingsTableSQL(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS system_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key VARCHAR(255) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type VARCHAR(50) DEFAULT 'string',
                description TEXT,
                is_public BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
    }
    
    private function getEnhancedTablesSQL(): string
    {
        return "
            -- 系统监控表
            CREATE TABLE IF NOT EXISTS system_metrics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                metric_type VARCHAR(50) NOT NULL,
                metric_name VARCHAR(100) NOT NULL,
                metric_value FLOAT NOT NULL,
                threshold_warning FLOAT,
                threshold_critical FLOAT,
                status VARCHAR(20) DEFAULT 'normal',
                metadata TEXT,
                recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            -- 数据库健康监控表
            CREATE TABLE IF NOT EXISTS database_health (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                connection_type VARCHAR(20) NOT NULL,
                host VARCHAR(255),
                database_name VARCHAR(100),
                status VARCHAR(20) NOT NULL,
                response_time FLOAT,
                error_message TEXT,
                last_check DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            -- AI 对话记录表
            CREATE TABLE IF NOT EXISTS ai_conversations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER,
                provider VARCHAR(50) NOT NULL,
                model VARCHAR(100) NOT NULL,
                user_message TEXT NOT NULL,
                ai_response TEXT NOT NULL,
                tokens_used INTEGER DEFAULT 0,
                response_time FLOAT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'success',
                error_message TEXT,
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE SET NULL
            );
            
            -- 邮件日志表
            CREATE TABLE IF NOT EXISTS email_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                recipient VARCHAR(255) NOT NULL,
                sender VARCHAR(255) NOT NULL,
                subject VARCHAR(500) NOT NULL,
                email_type VARCHAR(50) NOT NULL,
                template_used VARCHAR(100),
                status VARCHAR(20) NOT NULL,
                error_message TEXT,
                sent_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            -- 创建索引
            CREATE INDEX IF NOT EXISTS idx_system_metrics_type_time ON system_metrics(metric_type, recorded_at);
            CREATE INDEX IF NOT EXISTS idx_database_health_status ON database_health(status, last_check);
            CREATE INDEX IF NOT EXISTS idx_ai_conversations_provider ON ai_conversations(provider, created_at);
            CREATE INDEX IF NOT EXISTS idx_email_logs_status ON email_logs(status, created_at);
        ";
    }
    
    public function seed(): void
    {
        echo "\n开始数据种子...\n";
        
        // 创建管理员用户
        $this->createAdminUser();
        
        // 插入系统设置
        $this->insertSystemSettings();
        
        echo "✓ 数据种子完成!\n";
    }
    
    private function createAdminUser(): void
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        
        if ($stmt->fetchColumn() == 0) {
            $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password_hash, role, is_active, email_verified_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'admin',
                'admin@alingai.local',
                $passwordHash,
                'admin',
                1,
                date('Y-m-d H:i:s')
            ]);
            echo "✓ 创建管理员用户: admin / admin123\n";
        } else {
            echo "- 管理员用户已存在\n";
        }
    }
    
    private function insertSystemSettings(): void
    {
        $settings = [
            ['app_name', 'AlingAi Pro', 'string', '应用程序名称', 1],
            ['app_version', '2.0.0', 'string', '应用程序版本', 1],
            ['max_chat_history', '1000', 'integer', '最大聊天历史记录数', 0],
            ['enable_registration', 'true', 'boolean', '是否允许用户注册', 0],
            ['maintenance_mode', 'false', 'boolean', '维护模式', 0],
            ['default_ai_model', 'deepseek-chat', 'string', '默认AI模型', 0],
            ['system_initialized', 'true', 'boolean', '系统已初始化', 0],
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO system_settings 
            (setting_key, setting_value, setting_type, description, is_public)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
        
        echo "✓ 插入系统设置\n";
    }
    
    public function getStatus(): void
    {
        echo "\n=== 数据库状态 ===\n";
        echo "数据库文件: {$this->dbPath}\n";
        echo "文件大小: " . $this->formatBytes(filesize($this->dbPath)) . "\n";
        
        // 统计表信息
        $tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
        echo "表数量: " . count($tables) . "\n";
        
        foreach ($tables as $table) {
            $tableName = $table['name'];
            if ($tableName === 'sqlite_sequence') continue;
            
            $count = $this->pdo->query("SELECT COUNT(*) FROM {$tableName}")->fetchColumn();
            echo "  - {$tableName}: {$count} 条记录\n";
        }
        
        echo "\n";
    }
    
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

// 执行迁移
try {
    $migrator = new LocalDatabaseMigrator();
    $migrator->migrate();
    $migrator->seed();
    $migrator->getStatus();
    
    echo "\n🎉 本地数据库设置完成！\n";
    echo "现在可以使用本地 SQLite 数据库运行应用程序。\n";
    echo "如需切换到生产数据库，请修改 .env 文件中的数据库配置。\n";
    
} catch (Exception $e) {
    echo "✗ 迁移失败: " . $e->getMessage() . "\n";
    exit(1);
}
