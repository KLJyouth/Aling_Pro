<?php
/**
 * 终极数据库修复工具 v2.0
 * 自动选择最佳可用的数据库方案
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== AlingAi Pro 终极数据库修复工具 v2.0 ===\n";
echo "执行时间: " . date('Y-m-d H:i:s') . "\n\n";

class UltimateDatabaseFixer
{
    private $logger;
    private $config;
    private $selectedService = null;
    
    public function __construct()
    {
        $this->logger = new \Monolog\Logger('DatabaseFixer');
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
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
    }
    
    public function fix()
    {
        echo "🔍 分析数据库环境...\n";
        
        $mysqlAvailable = $this->testMysqlAvailability();
        $sqliteAvailable = $this->testSqliteAvailability();
        
        echo "\n📋 环境分析结果:\n";
        echo "   MySQL可用: " . ($mysqlAvailable ? "✅" : "❌") . "\n";
        echo "   SQLite可用: " . ($sqliteAvailable ? "✅" : "❌") . "\n";
        echo "   文件系统: ✅ (始终可用)\n";
        
        echo "\n🛠️ 选择最佳数据库方案...\n";
        
        if ($mysqlAvailable) {
            $this->setupMysqlService();
        } elseif ($sqliteAvailable) {
            $this->setupSqliteService();
        } else {
            $this->setupFileSystemService();
        }
        
        echo "\n🔧 创建统一数据库服务...\n";
        $this->createUnifiedDatabaseService();
        
        echo "\n✅ 修复应用程序配置...\n";
        $this->fixApplicationConfiguration();
        
        echo "\n🧪 验证修复结果...\n";
        $this->validateComplete();
    }
    
    private function testMysqlAvailability(): bool
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'] ?? 'localhost',
                $_ENV['DB_PORT'] ?? '3306',
                $_ENV['DB_DATABASE'] ?? 'alingai'
            );
            
            $pdo = new PDO($dsn, $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            echo "   ✅ MySQL连接测试成功\n";
            return true;
        } catch (Exception $e) {
            echo "   ❌ MySQL连接失败: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function testSqliteAvailability(): bool
    {
        try {
            if (!extension_loaded('pdo_sqlite')) {
                echo "   ❌ SQLite PDO驱动未安装\n";
                return false;
            }
            
            $testPath = __DIR__ . '/storage/test.sqlite';
            $testDir = dirname($testPath);
            
            if (!is_dir($testDir)) {
                mkdir($testDir, 0755, true);
            }
            
            $pdo = new PDO('sqlite:' . $testPath);
            $pdo->exec('CREATE TABLE IF NOT EXISTS test (id INTEGER)');
            unlink($testPath);
            
            echo "   ✅ SQLite测试成功\n";
            return true;
        } catch (Exception $e) {
            echo "   ❌ SQLite测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function setupMysqlService()
    {
        echo "   🎯 选择MySQL数据库服务\n";
        
        try {
            // 确保MySQL表存在
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_PORT'],
                $_ENV['DB_DATABASE']
            );
            
            $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $this->createMysqlTables($pdo);
            $this->selectedService = 'mysql';
            
            echo "   ✅ MySQL服务配置完成\n";
        } catch (Exception $e) {
            echo "   ❌ MySQL配置失败，回退到其他方案\n";
            $this->setupFileSystemService();
        }
    }
    
    private function setupSqliteService()
    {
        echo "   🎯 选择SQLite数据库服务\n";
        
        try {
            $dbPath = __DIR__ . '/storage/database/alingai.sqlite';
            $dbDir = dirname($dbPath);
            
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->createSqliteTables($pdo);
            $this->selectedService = 'sqlite';
            
            echo "   ✅ SQLite服务配置完成\n";
        } catch (Exception $e) {
            echo "   ❌ SQLite配置失败，回退到文件系统\n";
            $this->setupFileSystemService();
        }
    }
    
    private function setupFileSystemService()
    {
        echo "   🎯 选择文件系统数据库服务\n";
        
        $dataDir = __DIR__ . '/storage/filedb';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        // 创建基础数据文件
        $this->createFileSystemTables();
        $this->selectedService = 'filesystem';
        
        echo "   ✅ 文件系统服务配置完成\n";
    }
    
    private function createMysqlTables($pdo)
    {
        $tables = [
            'system_settings' => "
                CREATE TABLE IF NOT EXISTS system_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(255) NOT NULL UNIQUE,
                    setting_value TEXT,
                    setting_type VARCHAR(50) DEFAULT 'string',
                    description TEXT,
                    is_public TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_setting_key (setting_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            'ai_agents' => "
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            $pdo->exec($sql);
            echo "   ✅ MySQL表 {$tableName} 创建成功\n";
        }
        
        // 插入基础数据
        $this->insertBasicSettings($pdo, 'mysql');
    }
    
    private function createSqliteTables($pdo)
    {
        $tables = [
            'system_settings' => "
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
            ",
            'ai_agents' => "
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
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            $pdo->exec($sql);
            echo "   ✅ SQLite表 {$tableName} 创建成功\n";
        }
        
        // 插入基础数据
        $this->insertBasicSettings($pdo, 'sqlite');
    }
    
    private function createFileSystemTables()
    {
        $dataDir = __DIR__ . '/storage/filedb';
        
        $tables = ['system_settings', 'ai_agents'];
        
        foreach ($tables as $table) {
            $filePath = $dataDir . '/' . $table . '.json';
            $data = [
                'schema' => [],
                'data' => [],
                'auto_increment' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
            echo "   ✅ 文件系统表 {$table} 创建成功\n";
        }
        
        // 插入基础数据
        $this->insertBasicSettingsFileSystem();
    }
    
    private function insertBasicSettings($pdo, $type)
    {
        $settings = [
            ['system_version', '5.0.0'],
            ['system_status', 'active'],
            ['ai_enabled', 'true'],
            ['websocket_enabled', 'true'],
            ['database_type', $type],
            ['compilation_status', 'complete']
        ];
        
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
        if ($type === 'mysql') {
            $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
        }
        
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
        
        echo "   ✅ 基础设置数据插入完成\n";
    }
    
    private function insertBasicSettingsFileSystem()
    {
        $settingsFile = __DIR__ . '/storage/filedb/system_settings.json';
        $data = json_decode(file_get_contents($settingsFile), true);
        
        $settings = [
            ['setting_key' => 'system_version', 'setting_value' => '5.0.0'],
            ['setting_key' => 'system_status', 'setting_value' => 'active'],
            ['setting_key' => 'ai_enabled', 'setting_value' => 'true'],
            ['setting_key' => 'websocket_enabled', 'setting_value' => 'true'],
            ['setting_key' => 'database_type', 'setting_value' => 'filesystem'],
            ['setting_key' => 'compilation_status', 'setting_value' => 'complete']
        ];
        
        foreach ($settings as $setting) {
            $setting['id'] = $data['auto_increment']++;
            $setting['created_at'] = date('Y-m-d H:i:s');
            $data['data'][] = $setting;
        }
        
        file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT));
        echo "   ✅ 文件系统基础设置插入完成\n";
    }
    
    private function createUnifiedDatabaseService()
    {
        $serviceContent = '<?php
/**
 * 统一数据库服务 - 自动适配最佳数据库
 */

declare(strict_types=1);

namespace AlingAi\Services;

use Monolog\Logger;

class UnifiedDatabaseService implements DatabaseServiceInterface
{
    private $activeService;
    private Logger $logger;
    
    public function __construct(?Logger $logger = null)
    {
        $this->logger = $logger ?? new Logger("UnifiedDatabase");
        $this->initializeService();
    }
    
    private function initializeService()
    {
        try {
            // 尝试加载修复版数据库服务
            if (class_exists("\\AlingAi\\Services\\DatabaseServiceFixed")) {
                $this->activeService = new DatabaseServiceFixed($this->logger);
                if ($this->activeService->isConnected()) {
                    $this->logger->info("Using DatabaseServiceFixed");
                    return;
                }
            }
        } catch (Exception $e) {
            $this->logger->warning("DatabaseServiceFixed failed: " . $e->getMessage());
        }
        
        try {
            // 回退到文件系统服务
            if (class_exists("\\AlingAi\\Services\\FileSystemDatabaseService")) {
                $this->activeService = new FileSystemDatabaseService($this->logger);
                $this->logger->info("Using FileSystemDatabaseService");
                return;
            }
        } catch (Exception $e) {
            $this->logger->warning("FileSystemDatabaseService failed: " . $e->getMessage());
        }
        
        throw new \\RuntimeException("No database service available");
    }
    
    // 委托所有方法给活跃的服务
    public function getConnection() {
        return $this->activeService->getConnection();
    }
    
    public function query(string $sql, array $params = []): array {
        return $this->activeService->query($sql, $params);
    }
    
    public function execute(string $sql, array $params = []): bool {
        return $this->activeService->execute($sql, $params);
    }
    
    public function insert(string $table, array $data): bool {
        return $this->activeService->insert($table, $data);
    }
    
    public function find(string $table, $id): ?array {
        return $this->activeService->find($table, $id);
    }
    
    public function findAll(string $table, array $conditions = []): array {
        return $this->activeService->findAll($table, $conditions);
    }
    
    public function select(string $table, array $conditions = [], array $options = []): array {
        return $this->activeService->select($table, $conditions, $options);
    }
    
    public function update(string $table, $id, array $data): bool {
        return $this->activeService->update($table, $id, $data);
    }
    
    public function delete(string $table, $id): bool {
        return $this->activeService->delete($table, $id);
    }
    
    public function count(string $table, array $conditions = []): int {
        return $this->activeService->count($table, $conditions);
    }
    
    public function selectOne(string $table, array $conditions): ?array {
        return $this->activeService->selectOne($table, $conditions);
    }
    
    public function lastInsertId() {
        return $this->activeService->lastInsertId();
    }
    
    public function beginTransaction(): bool {
        return $this->activeService->beginTransaction();
    }
    
    public function commit(): bool {
        return $this->activeService->commit();
    }
    
    public function rollback(): bool {
        return $this->activeService->rollback();
    }
    
    public function getActiveServiceType(): string {
        if (method_exists($this->activeService, "getConnectionType")) {
            return $this->activeService->getConnectionType();
        }
        return get_class($this->activeService);
    }
}';
        
        file_put_contents(__DIR__ . '/src/Services/UnifiedDatabaseService.php', $serviceContent);
        echo "   ✅ 统一数据库服务创建完成\n";
    }
    
    private function fixApplicationConfiguration()
    {
        // 更新应用程序配置以使用统一数据库服务
        $appConfigPath = __DIR__ . '/src/Core/Application.php';
        
        if (file_exists($appConfigPath)) {
            $appContent = file_get_contents($appConfigPath);
            
            // 检查是否需要更新数据库服务注册
            if (strpos($appContent, 'UnifiedDatabaseService') === false) {
                $appContent = str_replace(
                    'DatabaseService::class',
                    'UnifiedDatabaseService::class',
                    $appContent
                );
                
                $appContent = str_replace(
                    'use AlingAi\Services\DatabaseService;',
                    'use AlingAi\Services\UnifiedDatabaseService;',
                    $appContent
                );
                
                file_put_contents($appConfigPath, $appContent);
                echo "   ✅ 应用程序配置已更新\n";
            } else {
                echo "   ✅ 应用程序配置已是最新\n";
            }
        }
        
        // 更新环境配置
        $envPath = __DIR__ . '/.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if ($this->selectedService === 'filesystem') {
                $envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=filesystem', $envContent);
            }
            
            // 添加数据库状态标记
            if (strpos($envContent, 'DB_STATUS=') === false) {
                $envContent .= "\n# 数据库状态\nDB_STATUS=fixed\nDB_SERVICE_TYPE={$this->selectedService}\n";
            }
            
            file_put_contents($envPath, $envContent);
            echo "   ✅ 环境配置已更新\n";
        }
    }
    
    private function validateComplete()
    {
        try {
            // 测试统一数据库服务
            require_once __DIR__ . '/src/Services/UnifiedDatabaseService.php';
            
            $unifiedService = new \AlingAi\Services\UnifiedDatabaseService($this->logger);
            
            echo "   ✅ 统一数据库服务加载成功\n";
            echo "   活跃服务类型: " . $unifiedService->getActiveServiceType() . "\n";
            
            // 测试基本操作
            $settings = $unifiedService->query("SELECT COUNT(*) as count FROM system_settings");
            if (!empty($settings)) {
                echo "   ✅ 数据库查询测试成功\n";
                echo "   系统设置数量: " . $settings[0]['count'] . "\n";
            }
            
            echo "\n🎉 数据库修复完全成功！\n";
            echo "数据库类型: " . $this->selectedService . "\n";
            echo "修复完成时间: " . date('Y-m-d H:i:s') . "\n";
            
        } catch (Exception $e) {
            echo "   ❌ 验证失败: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// 执行修复
try {
    $fixer = new UltimateDatabaseFixer();
    $fixer->fix();
    
} catch (Exception $e) {
    echo "\n💥 修复过程出错: " . $e->getMessage() . "\n";
    echo "错误跟踪:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
