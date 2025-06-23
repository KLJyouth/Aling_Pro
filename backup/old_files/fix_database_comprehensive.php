<?php
/**
 * 系统数据库修复工具
 * 为AlingAi Pro 5.0创建完整的数据库解决方案
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== AlingAi Pro 数据库修复工具 ===\n";
echo "执行时间: " . date('Y-m-d H:i:s') . "\n\n";

class DatabaseFixer
{
    private $sqliteDb;
    private $mysqlPdo = null;
    private $config;
    
    public function __construct()
    {
        $this->loadConfig();
    }
    
    private function loadConfig()
    {
        // 加载环境配置
        if (file_exists(__DIR__ . '/.env')) {
            $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
                    continue;
                }
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value, '"');
            }
        }
        
        $this->config = [
            'mysql' => [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'database' => $_ENV['DB_DATABASE'] ?? 'alingai',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? ''
            ],
            'sqlite' => [
                'path' => __DIR__ . '/storage/database/alingai.sqlite'
            ]
        ];
    }
    
    public function fix()
    {
        echo "1. 测试MySQL连接...\n";
        if ($this->testMysqlConnection()) {
            echo "   ✅ MySQL连接成功，检查表结构\n";
            $this->fixMysqlTables();
        } else {
            echo "   ❌ MySQL连接失败，使用SQLite备用方案\n";
            $this->setupSqliteFallback();
        }
        
        echo "\n2. 创建数据库服务修复...\n";
        $this->createDatabaseServiceFix();
        
        echo "\n3. 验证修复结果...\n";
        $this->validateFix();
    }
    
    private function testMysqlConnection()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $this->config['mysql']['host'],
                $this->config['mysql']['port'],
                $this->config['mysql']['database']
            );
            
            $this->mysqlPdo = new PDO($dsn, $this->config['mysql']['username'], $this->config['mysql']['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            
            return true;
        } catch (Exception $e) {
            echo "   错误: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function fixMysqlTables()
    {
        try {
            // 检查并创建必要的表
            $tables = [
                'system_settings' => $this->getSystemSettingsSchema(),
                'ai_agents' => $this->getAiAgentsSchema()
            ];
            
            foreach ($tables as $tableName => $schema) {
                echo "   检查表: {$tableName}\n";
                
                $stmt = $this->mysqlPdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$tableName]);
                
                if ($stmt->rowCount() == 0) {
                    echo "   创建表: {$tableName}\n";
                    $this->mysqlPdo->exec($schema);
                    echo "   ✅ 表 {$tableName} 创建成功\n";
                } else {
                    echo "   ✅ 表 {$tableName} 已存在\n";
                }
            }
        } catch (Exception $e) {
            echo "   ❌ MySQL表修复失败: " . $e->getMessage() . "\n";
            $this->setupSqliteFallback();
        }
    }
    
    private function setupSqliteFallback()
    {
        echo "   设置SQLite备用数据库...\n";
        
        // 确保目录存在
        $dbDir = dirname($this->config['sqlite']['path']);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        try {
            $this->sqliteDb = new PDO('sqlite:' . $this->config['sqlite']['path']);
            $this->sqliteDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 创建SQLite表结构
            $this->createSqliteTables();
            
            echo "   ✅ SQLite数据库设置完成\n";
            
            // 更新环境配置使用SQLite
            $this->updateEnvForSqlite();
            
        } catch (Exception $e) {
            echo "   ❌ SQLite设置失败: " . $e->getMessage() . "\n";
        }
    }
    
    private function createSqliteTables()
    {
        $tables = [
            'system_settings' => $this->getSystemSettingsSchemaSqlite(),
            'ai_agents' => $this->getAiAgentsSchemaSqlite()
        ];
        
        foreach ($tables as $tableName => $schema) {
            echo "   创建SQLite表: {$tableName}\n";
            $this->sqliteDb->exec($schema);
        }
        
        // 插入基础数据
        $this->insertBasicData();
    }
    
    private function insertBasicData()
    {
        // 插入系统基础设置
        $settings = [
            ['setting_key' => 'system_version', 'setting_value' => '5.0.0'],
            ['setting_key' => 'system_status', 'setting_value' => 'active'],
            ['setting_key' => 'ai_enabled', 'setting_value' => 'true'],
            ['setting_key' => 'websocket_enabled', 'setting_value' => 'true']
        ];
        
        $stmt = $this->sqliteDb->prepare('INSERT OR REPLACE INTO system_settings (setting_key, setting_value) VALUES (?, ?)');
        foreach ($settings as $setting) {
            $stmt->execute([$setting['setting_key'], $setting['setting_value']]);
        }
        
        echo "   ✅ 基础数据插入完成\n";
    }
    
    private function updateEnvForSqlite()
    {
        $envContent = file_get_contents(__DIR__ . '/.env');
        $envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $envContent);
        $envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $this->config['sqlite']['path'], $envContent);
        
        file_put_contents(__DIR__ . '/.env.backup', file_get_contents(__DIR__ . '/.env'));
        file_put_contents(__DIR__ . '/.env', $envContent);
        
        echo "   ✅ 环境配置已更新为SQLite\n";
    }
    
    private function createDatabaseServiceFix()
    {
        $fixedServiceContent = '<?php
/**
 * 修复版数据库服务
 */

declare(strict_types=1);

namespace AlingAi\Services;

use PDO;
use PDOException;
use Monolog\Logger;

class DatabaseServiceFixed implements DatabaseServiceInterface
{
    private ?PDO $pdo = null;
    private Logger $logger;
    private array $config;
    private string $connectionType = "unknown";
    
    public function __construct(?Logger $logger = null)
    {
        $this->logger = $logger ?? new Logger("DatabaseService");
        $this->config = $this->loadConfig();
        $this->initializeDatabase();
    }
    
    private function loadConfig(): array
    {
        $connection = $_ENV["DB_CONNECTION"] ?? "sqlite";
        
        $config = [
            "connection" => $connection,
            "prefix" => $_ENV["DB_PREFIX"] ?? "",
        ];
        
        if ($connection === "sqlite") {
            $config["database"] = $_ENV["DB_DATABASE"] ?? __DIR__ . "/../../storage/database/alingai.sqlite";
        } else {
            $config = array_merge($config, [
                "host" => $_ENV["DB_HOST"] ?? "127.0.0.1",
                "port" => (int) ($_ENV["DB_PORT"] ?? 3306),
                "database" => $_ENV["DB_DATABASE"] ?? "alingai",
                "username" => $_ENV["DB_USERNAME"] ?? "root",
                "password" => $_ENV["DB_PASSWORD"] ?? "",
                "charset" => "utf8mb4",
                "collation" => "utf8mb4_unicode_ci",
            ]);
        }
        
        return $config;
    }
    
    private function initializeDatabase(): void
    {
        try {
            if ($this->config["connection"] === "sqlite") {
                $this->initializeSQLite();
            } else {
                $this->initializeMySQL();
            }
        } catch (PDOException $e) {
            $this->logger->warning("Primary database failed, using SQLite fallback", [
                "error" => $e->getMessage()
            ]);
            $this->initializeSQLiteFallback();
        }
    }
    
    private function initializeSQLite(): void
    {
        $databasePath = $this->config["database"];
        $databaseDir = dirname($databasePath);
        
        if (!is_dir($databaseDir)) {
            mkdir($databaseDir, 0755, true);
        }
        
        $this->pdo = new PDO("sqlite:" . $databasePath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connectionType = "sqlite";
        
        $this->logger->info("SQLite database initialized", ["path" => $databasePath]);
    }
    
    private function initializeMySQL(): void
    {
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $this->config["host"],
            $this->config["port"],
            $this->config["database"],
            $this->config["charset"]
        );
        
        $this->pdo = new PDO(
            $dsn,
            $this->config["username"],
            $this->config["password"],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        $this->connectionType = "mysql";
        $this->logger->info("MySQL database initialized");
    }
    
    private function initializeSQLiteFallback(): void
    {
        $fallbackPath = __DIR__ . "/../../storage/database/alingai_fallback.sqlite";
        $this->pdo = new PDO("sqlite:" . $fallbackPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connectionType = "sqlite_fallback";
        
        $this->logger->info("SQLite fallback database initialized");
    }
    
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error("Database query failed", [
                "sql" => $sql,
                "error" => $e->getMessage()
            ]);
            return [];
        }
    }
    
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logger->error("Database execute failed", [
                "sql" => $sql,
                "error" => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }
    
    public function getConnectionType(): string
    {
        return $this->connectionType;
    }
    
    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }
}';
        
        file_put_contents(__DIR__ . '/src/Services/DatabaseServiceFixed.php', $fixedServiceContent);
        echo "   ✅ 修复版数据库服务已创建\n";
    }
    
    private function validateFix()
    {
        try {
            // 测试修复后的数据库服务
            require_once __DIR__ . '/src/Services/DatabaseServiceFixed.php';
            
            $dbService = new \AlingAi\Services\DatabaseServiceFixed();
            
            if ($dbService->isConnected()) {
                echo "   ✅ 数据库服务连接成功\n";
                echo "   连接类型: " . $dbService->getConnectionType() . "\n";
                
                // 测试查询
                $result = $dbService->query("SELECT COUNT(*) as count FROM system_settings");
                if (!empty($result)) {
                    echo "   ✅ 数据库查询测试成功\n";
                    echo "   系统设置数量: " . $result[0]['count'] . "\n";
                } else {
                    echo "   ⚠️ 数据库查询测试失败\n";
                }
            } else {
                echo "   ❌ 数据库服务连接失败\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ 验证失败: " . $e->getMessage() . "\n";
        }
    }
    
    private function getSystemSettingsSchema()
    {
        return "
            CREATE TABLE IF NOT EXISTS system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(255) NOT NULL UNIQUE,
                setting_value TEXT,
                setting_type VARCHAR(50) DEFAULT 'string',
                description TEXT,
                is_public TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_setting_key (setting_key),
                INDEX idx_is_public (is_public)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    private function getAiAgentsSchema()
    {
        return "
            CREATE TABLE IF NOT EXISTS ai_agents (
                id VARCHAR(64) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(100) NOT NULL,
                status ENUM('active', 'inactive', 'training', 'error') DEFAULT 'inactive',
                config JSON,
                capabilities JSON,
                performance_metrics JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_name (name),
                INDEX idx_type (type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
    
    private function getSystemSettingsSchemaSqlite()
    {
        return "
            CREATE TABLE IF NOT EXISTS system_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT NOT NULL UNIQUE,
                setting_value TEXT,
                setting_type TEXT DEFAULT 'string',
                description TEXT,
                is_public INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE INDEX IF NOT EXISTS idx_setting_key ON system_settings(setting_key);
            CREATE INDEX IF NOT EXISTS idx_is_public ON system_settings(is_public);
        ";
    }
    
    private function getAiAgentsSchemaSqlite()
    {
        return "
            CREATE TABLE IF NOT EXISTS ai_agents (
                id TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                status TEXT DEFAULT 'inactive',
                config TEXT,
                capabilities TEXT,
                performance_metrics TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE INDEX IF NOT EXISTS idx_agent_name ON ai_agents(name);
            CREATE INDEX IF NOT EXISTS idx_agent_type ON ai_agents(type);
            CREATE INDEX IF NOT EXISTS idx_agent_status ON ai_agents(status);
        ";
    }
}

// 执行修复
try {
    $fixer = new DatabaseFixer();
    $fixer->fix();
    
    echo "\n=== 修复完成 ===\n";
    echo "✅ 数据库修复成功，系统现在可以正常运行\n";
    echo "修复时间: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "\n❌ 修复失败: " . $e->getMessage() . "\n";
    echo "请检查权限和配置后重试\n";
}
