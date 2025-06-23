<?php

declare(strict_types=1);

namespace AlingAi\Database;

use PDO;
use PDOException;
use Exception;
use AlingAi\Services\PerformanceMonitorService;

/**
 * 数据库迁移管理器
 * 
 * 管理数据库架构变更、版本控制和迁移执行
 * 
 * @package AlingAi\Database
 * @version 1.0.0
 * @since 2024-12-19
 */
class MigrationManager
{
    private PDO $db;
    private PerformanceMonitorService $monitor;
    private array $config;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->monitor = new PerformanceMonitorService();
        $this->migrationsPath = __DIR__ . '/Migrations';
        $this->initializeDatabase();
        $this->ensureMigrationsTable();
    }

    /**
     * 初始化数据库连接
     */
    private function initializeDatabase(): void
    {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
            $this->db = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            // 设置 SQL 模式
            $this->db->exec("SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * 确保迁移表存在
     */
    private function ensureMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_batch (batch)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $this->db->exec($sql);
    }

    /**
     * 运行所有待执行的迁移
     */
    public function migrate(): array
    {
        try {
            $startTime = microtime(true);
            $pendingMigrations = $this->getPendingMigrations();
            
            if (empty($pendingMigrations)) {
                return [
                    'status' => 'success',
                    'message' => 'No pending migrations',
                    'executed' => [],
                    'execution_time' => 0
                ];
            }

            $this->db->beginTransaction();
            
            try {
                $batch = $this->getNextBatch();
                $executed = [];
                
                foreach ($pendingMigrations as $migration) {
                    $this->executeMigration($migration, $batch);
                    $executed[] = $migration;
                    
                    $this->monitor->logEvent('migration_executed', [
                        'migration' => $migration,
                        'batch' => $batch
                    ]);
                }
                
                $this->db->commit();
                
                $executionTime = (microtime(true) - $startTime) * 1000;
                
                $this->monitor->logEvent('migrations_completed', [
                    'count' => count($executed),
                    'batch' => $batch,
                    'execution_time' => $executionTime
                ]);
                
                return [
                    'status' => 'success',
                    'message' => 'Migrations executed successfully',
                    'executed' => $executed,
                    'batch' => $batch,
                    'execution_time' => round($executionTime, 2)
                ];
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->monitor->logError('Migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Migration failed: ' . $e->getMessage(),
                'executed' => $executed ?? [],
                'execution_time' => 0
            ];
        }
    }

    /**
     * 获取迁移状态
     */
    public function getStatus(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        $pendingMigrations = $this->getPendingMigrations();
        
        return [
            'total_migrations' => count($allMigrations),
            'executed_migrations' => count($executedMigrations),
            'pending_migrations' => count($pendingMigrations),
            'last_batch' => $this->getLastBatch(),
            'pending' => $pendingMigrations,
            'executed' => array_map(function($m) {
                return [
                    'migration' => $m['migration'],
                    'batch' => $m['batch'],
                    'executed_at' => $m['executed_at']
                ];
            }, $executedMigrations)
        ];
    }

    /**
     * 获取待执行的迁移
     */
    private function getPendingMigrations(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = array_column($this->getExecutedMigrations(), 'migration');
        
        return array_diff($allMigrations, $executedMigrations);
    }

    /**
     * 获取所有迁移文件
     */
    private function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }
        
        $files = scandir($this->migrationsPath);
        $migrations = [];
        
        foreach ($files as $file) {
            if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_.*\.php$/', $file)) {
                $migrations[] = basename($file, '.php');
            }
        }
        
        sort($migrations);
        return $migrations;
    }

    /**
     * 获取已执行的迁移
     */
    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->migrationsTable} ORDER BY id");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 执行单个迁移
     */
    private function executeMigration(string $migration, int $batch): void
    {
        $migrationFile = $this->migrationsPath . '/' . $migration . '.php';
        
        if (!file_exists($migrationFile)) {
            throw new Exception("Migration file not found: {$migration}");
        }
        
        require_once $migrationFile;
        
        $className = $this->getMigrationClassName($migration);
        
        if (!class_exists($className)) {
            throw new Exception("Migration class not found: {$className}");
        }
        
        $migrationInstance = new $className($this->db);
        
        if (!method_exists($migrationInstance, 'up')) {
            throw new Exception("Migration {$className} does not have an 'up' method");
        }
        
        // 执行迁移
        $migrationInstance->up();
        
        // 记录迁移
        $stmt = $this->db->prepare("
            INSERT INTO {$this->migrationsTable} (migration, batch) 
            VALUES (?, ?)
        ");
        $stmt->execute([$migration, $batch]);
    }

    /**
     * 获取下一个批次号
     */
    private function getNextBatch(): int
    {
        $stmt = $this->db->prepare("SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return ($result['max_batch'] ?? 0) + 1;
    }

    /**
     * 获取最后批次号
     */
    private function getLastBatch(): int
    {
        $stmt = $this->db->prepare("SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['max_batch'] ?? 0;
    }

    /**
     * 获取迁移类名
     */
    private function getMigrationClassName(string $migration): string
    {
        $parts = explode('_', $migration);
        if (count($parts) >= 4) {
            // 移除前4个部分（日期时间）
            $nameParts = array_slice($parts, 4);
            return 'Migration_' . implode('_', array_slice($parts, 0, 4)) . '_' . $this->toCamelCase(implode('_', $nameParts));
        }
        
        return 'Migration_' . $migration;
    }

    /**
     * 转换为驼峰命名
     */
    private function toCamelCase(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $str)));
    }

    /**
     * 检查数据库连接
     */
    public function checkConnection(): bool
    {
        try {
            $this->db->query('SELECT 1');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取数据库信息
     */
    public function getDatabaseInfo(): array
    {
        try {
            $version = $this->db->query('SELECT VERSION() as version')->fetch()['version'];
            $charset = $this->db->query('SELECT @@character_set_database as charset')->fetch()['charset'];
            $collation = $this->db->query('SELECT @@collation_database as collation')->fetch()['collation'];
            
            return [
                'version' => $version,
                'charset' => $charset,
                'collation' => $collation,
                'connection_status' => 'connected'
            ];
        } catch (Exception $e) {
            return [
                'connection_status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
}
