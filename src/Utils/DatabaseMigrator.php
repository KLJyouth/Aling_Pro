<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use PDO;
use PDOException;

/**
 * 数据库迁移器
 * 
 * 提供完整的数据库迁移和版本管理功能
 * 优化性能：批量迁移、事务处理、并行迁移
 * 增强功能：版本控制、回滚、种子数据、迁移验证
 */
class DatabaseMigrator
{
    private LoggerInterface $logger;
    private PDO $pdo;
    private array $config;
    private string $migrationsTable = 'migrations';
    private string $migrationsPath;
    
    public function __construct(
        LoggerInterface $logger,
        PDO $pdo,
        array $config = []
    ) {
        $this->logger = $logger;
        $this->pdo = $pdo;
        $this->config = array_merge([
            'migrations_path' => dirname(__DIR__, 2) . '/database/migrations',
            'seeds_path' => dirname(__DIR__, 2) . '/database/seeds',
            'batch_size' => 10,
            'transaction_mode' => true,
            'safe_mode' => true,
            'backup_before_migrate' => true,
            'migration_timeout' => 300
        ], $config);
        
        $this->migrationsPath = $this->config['migrations_path'];
        $this->ensureMigrationsTable();
    }
    
    /**
     * 确保迁移表存在
     */
    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            execution_time FLOAT DEFAULT 0,
            status ENUM('success', 'failed', 'rolled_back') DEFAULT 'success',
            error_message TEXT NULL
        )";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->logger->error('创建迁移表失败', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * 运行迁移
     */
    public function migrate(array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $options = array_merge([
                'target' => null, // 目标版本，null表示最新
                'force' => false,
                'pretend' => false,
                'step' => null
            ], $options);
            
            $this->logger->info('开始运行数据库迁移', $options);
            
            // 获取待运行的迁移
            $pendingMigrations = $this->getPendingMigrations();
            
            if (empty($pendingMigrations)) {
                $this->logger->info('没有待运行的迁移');
                return [
                    'success' => true,
                    'migrations_run' => 0,
                    'message' => '没有待运行的迁移'
                ];
            }
            
            // 应用目标限制
            if ($options['target']) {
                $pendingMigrations = $this->filterMigrationsByTarget($pendingMigrations, $options['target']);
            }
            
            // 应用步数限制
            if ($options['step']) {
                $pendingMigrations = array_slice($pendingMigrations, 0, $options['step']);
            }
            
            $results = [];
            $batch = $this->getNextBatchNumber();
            
            foreach ($pendingMigrations as $migration) {
                $result = $this->runMigration($migration, $batch, $options);
                $results[] = $result;
                
                if (!$result['success'] && !$options['force']) {
                    break;
                }
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $successCount = count(array_filter($results, function($r) { return $r['success']; }));
            $failedCount = count($results) - $successCount;
            
            $this->logger->info('迁移完成', [
                'total' => count($results),
                'success' => $successCount,
                'failed' => $failedCount,
                'duration' => $duration
            ]);
            
            return [
                'success' => $failedCount === 0,
                'migrations_run' => count($results),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results,
                'duration' => $duration
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('迁移失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 获取待运行的迁移
     */
    private function getPendingMigrations(): array
    {
        $migrations = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        return array_filter($migrations, function($migration) use ($executedMigrations) {
            return !in_array($migration['name'], $executedMigrations);
        });
    }
    
    /**
     * 获取迁移文件
     */
    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $migration = $this->parseMigrationFile($file);
            if ($migration) {
                $migrations[] = $migration;
            }
        }
        
        // 按版本排序
        usort($migrations, function($a, $b) {
            return strcmp($a['version'], $b['version']);
        });
        
        return $migrations;
    }
    
    /**
     * 解析迁移文件
     */
    private function parseMigrationFile(string $file): ?array
    {
        $filename = basename($file, '.php');
        
        // 解析文件名格式: 2024_01_01_000001_create_users_table.php
        if (!preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)$/', $filename, $matches)) {
            return null;
        }
        
        $version = $matches[1];
        $name = $matches[2];
        
        // 读取文件内容
        $content = file_get_contents($file);
        
        // 提取类名
        if (preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            $className = $classMatches[1];
        } else {
            $className = str_replace('_', '', ucwords($name, '_'));
        }
        
        return [
            'file' => $file,
            'name' => $filename,
            'version' => $version,
            'class_name' => $className,
            'content' => $content
        ];
    }
    
    /**
     * 获取已执行的迁移
     */
    private function getExecutedMigrations(): array
    {
        $sql = "SELECT migration FROM {$this->migrationsTable} WHERE status = 'success' ORDER BY id";
        $stmt = $this->pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * 按目标过滤迁移
     */
    private function filterMigrationsByTarget(array $migrations, string $target): array
    {
        return array_filter($migrations, function($migration) use ($target) {
            return $migration['version'] <= $target;
        });
    }
    
    /**
     * 获取下一个批次号
     */
    private function getNextBatchNumber(): int
    {
        $sql = "SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($result['max_batch'] ?? 0) + 1;
    }
    
    /**
     * 运行单个迁移
     */
    private function runMigration(array $migration, int $batch, array $options): array
    {
        $startTime = microtime(true);
        
        try {
            $this->logger->info('运行迁移', [
                'migration' => $migration['name'],
                'batch' => $batch
            ]);
            
            // 如果是预演模式，只记录不执行
            if ($options['pretend']) {
                return [
                    'success' => true,
                    'migration' => $migration['name'],
                    'pretend' => true,
                    'message' => '预演模式：迁移将被执行'
                ];
            }
            
            // 加载迁移类
            $migrationInstance = $this->loadMigrationClass($migration);
            
            // 开始事务
            if ($this->config['transaction_mode']) {
                $this->pdo->beginTransaction();
            }
            
            try {
                // 执行迁移
                $migrationInstance->up();
                
                // 记录迁移
                $this->recordMigration($migration['name'], $batch, 'success');
                
                // 提交事务
                if ($this->config['transaction_mode']) {
                    $this->pdo->commit();
                }
                
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                $this->logger->info('迁移成功', [
                    'migration' => $migration['name'],
                    'duration_ms' => $duration
                ]);
                
                return [
                    'success' => true,
                    'migration' => $migration['name'],
                    'duration_ms' => $duration
                ];
                
            } catch (\Exception $e) {
                // 回滚事务
                if ($this->config['transaction_mode']) {
                    $this->pdo->rollBack();
                }
                
                // 记录失败
                $this->recordMigration($migration['name'], $batch, 'failed', $e->getMessage());
                
                throw $e;
            }
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->error('迁移失败', [
                'migration' => $migration['name'],
                'error' => $e->getMessage(),
                'duration_ms' => $duration
            ]);
            
            return [
                'success' => false,
                'migration' => $migration['name'],
                'error' => $e->getMessage(),
                'duration_ms' => $duration
            ];
        }
    }
    
    /**
     * 加载迁移类
     */
    private function loadMigrationClass(array $migration): object
    {
        // 包含文件
        require_once $migration['file'];
        
        $className = $migration['class_name'];
        
        if (!class_exists($className)) {
            throw new \RuntimeException("迁移类 {$className} 不存在");
        }
        
        return new $className($this->pdo);
    }
    
    /**
     * 记录迁移
     */
    private function recordMigration(string $migration, int $batch, string $status, string $errorMessage = null): void
    {
        $sql = "INSERT INTO {$this->migrationsTable} (migration, batch, status, error_message) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$migration, $batch, $status, $errorMessage]);
    }
    
    /**
     * 回滚迁移
     */
    public function rollback(array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $options = array_merge([
                'steps' => 1,
                'target' => null,
                'pretend' => false
            ], $options);
            
            $this->logger->info('开始回滚迁移', $options);
            
            // 获取要回滚的迁移
            $migrationsToRollback = $this->getMigrationsToRollback($options);
            
            if (empty($migrationsToRollback)) {
                $this->logger->info('没有可回滚的迁移');
                return [
                    'success' => true,
                    'migrations_rolled_back' => 0,
                    'message' => '没有可回滚的迁移'
                ];
            }
            
            $results = [];
            
            foreach ($migrationsToRollback as $migration) {
                $result = $this->rollbackMigration($migration, $options);
                $results[] = $result;
                
                if (!$result['success']) {
                    break;
                }
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $successCount = count(array_filter($results, function($r) { return $r['success']; }));
            
            $this->logger->info('回滚完成', [
                'total' => count($results),
                'success' => $successCount,
                'duration' => $duration
            ]);
            
            return [
                'success' => $successCount === count($results),
                'migrations_rolled_back' => count($results),
                'success_count' => $successCount,
                'results' => $results,
                'duration' => $duration
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('回滚失败', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 获取要回滚的迁移
     */
    private function getMigrationsToRollback(array $options): array
    {
        $sql = "SELECT migration, batch FROM {$this->migrationsTable} WHERE status = 'success' ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        $executedMigrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($executedMigrations)) {
            return [];
        }
        
        // 按批次分组
        $batches = [];
        foreach ($executedMigrations as $migration) {
            $batches[$migration['batch']][] = $migration;
        }
        
        $migrationsToRollback = [];
        $steps = $options['steps'] ?? 1;
        $batchCount = 0;
        
        foreach (array_reverse($batches) as $batch => $migrations) {
            if ($batchCount >= $steps) {
                break;
            }
            
            $migrationsToRollback = array_merge($migrationsToRollback, $migrations);
            $batchCount++;
        }
        
        return $migrationsToRollback;
    }
    
    /**
     * 回滚单个迁移
     */
    private function rollbackMigration(array $migration, array $options): array
    {
        $startTime = microtime(true);
        
        try {
            $this->logger->info('回滚迁移', [
                'migration' => $migration['migration']
            ]);
            
            // 如果是预演模式，只记录不执行
            if ($options['pretend']) {
                return [
                    'success' => true,
                    'migration' => $migration['migration'],
                    'pretend' => true,
                    'message' => '预演模式：迁移将被回滚'
                ];
            }
            
            // 加载迁移类
            $migrationFile = $this->migrationsPath . '/' . $migration['migration'] . '.php';
            $migrationData = $this->parseMigrationFile($migrationFile);
            $migrationInstance = $this->loadMigrationClass($migrationData);
            
            // 开始事务
            if ($this->config['transaction_mode']) {
                $this->pdo->beginTransaction();
            }
            
            try {
                // 执行回滚
                $migrationInstance->down();
                
                // 更新迁移状态
                $this->updateMigrationStatus($migration['migration'], 'rolled_back');
                
                // 提交事务
                if ($this->config['transaction_mode']) {
                    $this->pdo->commit();
                }
                
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                $this->logger->info('回滚成功', [
                    'migration' => $migration['migration'],
                    'duration_ms' => $duration
                ]);
                
                return [
                    'success' => true,
                    'migration' => $migration['migration'],
                    'duration_ms' => $duration
                ];
                
            } catch (\Exception $e) {
                // 回滚事务
                if ($this->config['transaction_mode']) {
                    $this->pdo->rollBack();
                }
                
                throw $e;
            }
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->error('回滚失败', [
                'migration' => $migration['migration'],
                'error' => $e->getMessage(),
                'duration_ms' => $duration
            ]);
            
            return [
                'success' => false,
                'migration' => $migration['migration'],
                'error' => $e->getMessage(),
                'duration_ms' => $duration
            ];
        }
    }
    
    /**
     * 更新迁移状态
     */
    private function updateMigrationStatus(string $migration, string $status): void
    {
        $sql = "UPDATE {$this->migrationsTable} SET status = ? WHERE migration = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status, $migration]);
    }
    
    /**
     * 重置迁移
     */
    public function reset(): array
    {
        $this->logger->info('开始重置所有迁移');
        
        // 获取所有已执行的迁移
        $sql = "SELECT migration FROM {$this->migrationsTable} WHERE status = 'success' ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($migrations)) {
            return [
                'success' => true,
                'message' => '没有可重置的迁移'
            ];
        }
        
        // 回滚所有迁移
        return $this->rollback(['steps' => count($migrations)]);
    }
    
    /**
     * 刷新迁移
     */
    public function refresh(): array
    {
        $this->logger->info('开始刷新迁移');
        
        // 重置所有迁移
        $resetResult = $this->reset();
        
        if (!$resetResult['success']) {
            return $resetResult;
        }
        
        // 重新运行所有迁移
        return $this->migrate();
    }
    
    /**
     * 获取迁移状态
     */
    public function status(): array
    {
        $migrations = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        $status = [];
        
        foreach ($migrations as $migration) {
            $status[] = [
                'migration' => $migration['name'],
                'version' => $migration['version'],
                'status' => in_array($migration['name'], $executedMigrations) ? 'Ran' : 'Pending',
                'batch' => $this->getMigrationBatch($migration['name'])
            ];
        }
        
        return $status;
    }
    
    /**
     * 获取迁移批次
     */
    private function getMigrationBatch(string $migration): ?int
    {
        $sql = "SELECT batch FROM {$this->migrationsTable} WHERE migration = ? AND status = 'success'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$migration]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['batch'] : null;
    }
    
    /**
     * 创建迁移文件
     */
    public function createMigration(string $name, string $table = null): string
    {
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $filepath = $this->migrationsPath . '/' . $filename;
        
        $className = str_replace('_', '', ucwords($name, '_'));
        
        $content = $this->generateMigrationContent($className, $table);
        
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
        }
        
        file_put_contents($filepath, $content);
        
        $this->logger->info('创建迁移文件', [
            'file' => $filename,
            'class' => $className
        ]);
        
        return $filepath;
    }
    
    /**
     * 生成迁移内容
     */
    private function generateMigrationContent(string $className, ?string $table): string
    {
        $tableName = $table ?: 'table_name';
        
        return "<?php

use PDO;

class {$className}
{
    private PDO \$pdo;
    
    public function __construct(PDO \$pdo)
    {
        \$this->pdo = \$pdo;
    }
    
    public function up(): void
    {
        // 创建表
        \$sql = \"CREATE TABLE IF NOT EXISTS {$tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )\";
        
        \$this->pdo->exec(\$sql);
    }
    
    public function down(): void
    {
        // 删除表
        \$sql = \"DROP TABLE IF EXISTS {$tableName}\";
        
        \$this->pdo->exec(\$sql);
    }
}";
    }
    
    /**
     * 运行种子数据
     */
    public function seed(array $options = []): array
    {
        $options = array_merge([
            'class' => null,
            'force' => false
        ], $options);
        
        $seedsPath = $this->config['seeds_path'];
        $files = glob($seedsPath . '/*.php');
        
        if (empty($files)) {
            return [
                'success' => true,
                'message' => '没有种子文件'
            ];
        }
        
        $results = [];
        
        foreach ($files as $file) {
            if ($options['class'] && basename($file, '.php') !== $options['class']) {
                continue;
            }
            
            $result = $this->runSeed($file);
            $results[] = $result;
        }
        
        return [
            'success' => count(array_filter($results, function($r) { return $r['success']; })) === count($results),
            'results' => $results
        ];
    }
    
    /**
     * 运行单个种子文件
     */
    private function runSeed(string $file): array
    {
        try {
            require_once $file;
            
            $className = basename($file, '.php');
            $className = str_replace('_', '', ucwords($className, '_'));
            
            if (!class_exists($className)) {
                throw new \RuntimeException("种子类 {$className} 不存在");
            }
            
            $seed = new $className($this->pdo);
            $seed->run();
            
            return [
                'success' => true,
                'seed' => $className
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'seed' => basename($file, '.php'),
                'error' => $e->getMessage()
            ];
        }
    }
} 