<?php
/**
 * 数据库服务类
 * 
 * @package AlingAi\Services
 */

declare(strict_types=1);

namespace AlingAi\Services;

use PDO;
use PDOException;
use Monolog\Logger;

class DatabaseService implements DatabaseServiceInterface
{
    private ?PDO $pdo = null;
    private Logger $logger;
    private array $config;
    
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->config = $this->loadConfig();
        $this->initializeDatabase();
    }
    
    private function loadConfig(): array
    {
        $connection = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: 'mysql';
        
        $config = [
            'connection' => $connection,
            'prefix' => $_ENV['DB_PREFIX'] ?? getenv('DB_PREFIX') ?: '',
        ];
        
        if ($connection === 'sqlite') {
            $config['database'] = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: __DIR__ . '/../../storage/database.sqlite';
        } else {
            // MySQL 配置
            $config = array_merge($config, [
                'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1',
                'port' => (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306),
                'database' => $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'alingai_pro',
                'username' => $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
        }
        
        return $config;
    }
    
    private function initializeDatabase(): void
    {
        try {
            if ($this->config['connection'] === 'sqlite') {
                $this->initializeSQLite();
            } else {
                $this->initializeMySQL();
            }
        } catch (PDOException $e) {
            $this->logger->error('Database initialization failed', [
                'connection' => $this->config['connection'],
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Database initialization failed: ' . $e->getMessage());
        }
    }
    
    private function initializeSQLite(): void
    {
        $databasePath = $this->config['database'];
        $databaseDir = dirname($databasePath);
        
        if (!is_dir($databaseDir)) {
            mkdir($databaseDir, 0755, true);
        }
        
        $dsn = 'sqlite:' . $databasePath;
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        $this->logger->info('SQLite database connected', ['path' => $databasePath]);
    }
    
    private function initializeMySQL(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['charset']
        );
        
        $this->pdo = new PDO(
            $dsn,
            $this->config['username'],
            $this->config['password']
        );
        
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        $this->logger->info('MySQL database connected', [
            'host' => $this->config['host'],
            'database' => $this->config['database']
        ]);
    }
    
    /**
     * 获取数据库连接
     */
    public function getConnection()
    {
        return $this->pdo;
    }
    
    /**
     * 执行查询
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error('Query failed', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Query failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 执行 SQL 语句
     */
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logger->error('Execute failed', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 插入数据
     */
    public function insert(string $table, array $data): bool
    {
        try {
            $table = $this->config['prefix'] . $table;
            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
            return $this->execute($sql, $data);
        } catch (\Exception $e) {
            $this->logger->error('Insert failed', [
                'table' => $table,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 查找单条记录
     */
    public function find(string $table, $id): ?array
    {
        try {
            $table = $this->config['prefix'] . $table;
            $sql = "SELECT * FROM `{$table}` WHERE id = :id LIMIT 1";
            $results = $this->query($sql, ['id' => $id]);
            return !empty($results) ? $results[0] : null;
        } catch (\Exception $e) {
            $this->logger->error('Find failed', [
                'table' => $table,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 查找所有记录
     */
    public function findAll(string $table, array $conditions = []): array
    {
        try {
            $table = $this->config['prefix'] . $table;
            $sql = "SELECT * FROM `{$table}`";
            $params = [];
            
            if (!empty($conditions)) {
                $whereClause = [];
                foreach ($conditions as $key => $value) {
                    $whereClause[] = "`{$key}` = :{$key}";
                    $params[$key] = $value;
                }
                $sql .= ' WHERE ' . implode(' AND ', $whereClause);
            }
            
            return $this->query($sql, $params);
        } catch (\Exception $e) {
            $this->logger->error('FindAll failed', [
                'table' => $table,
                'conditions' => $conditions,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 更新数据
     */
    public function update(string $table, $id, array $data): bool
    {
        try {
            $table = $this->config['prefix'] . $table;
            
            $setClause = [];
            foreach ($data as $key => $value) {
                $setClause[] = "`{$key}` = :{$key}";
            }
            $setClause = implode(', ', $setClause);
            
            $sql = "UPDATE `{$table}` SET {$setClause} WHERE id = :id";
            $params = array_merge($data, ['id' => $id]);
            
            return $this->execute($sql, $params);
        } catch (\Exception $e) {
            $this->logger->error('Update failed', [
                'table' => $table,
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 删除数据
     */
    public function delete(string $table, $id): bool
    {
        try {
            $table = $this->config['prefix'] . $table;
            $sql = "DELETE FROM `{$table}` WHERE id = :id";
            return $this->execute($sql, ['id' => $id]);
        } catch (\Exception $e) {
            $this->logger->error('Delete failed', [
                'table' => $table,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 开始事务
     */
    public function beginTransaction(): bool
    {
        try {
            return $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            $this->logger->error('Begin transaction failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * 提交事务
     */
    public function commit(): bool
    {
        try {
            return $this->pdo->commit();
        } catch (PDOException $e) {
            $this->logger->error('Commit failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * 回滚事务
     */
    public function rollback(): bool
    {
        try {
            return $this->pdo->rollBack();
        } catch (PDOException $e) {
            $this->logger->error('Rollback failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * 获取数据库统计信息
     */
    public function getStats(): array
    {
        try {
            if ($this->config['connection'] === 'sqlite') {
                return $this->getSQLiteStats();
            } else {
                return $this->getMySQLStats();
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to get database stats', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    private function getSQLiteStats(): array
    {
        $stats = [
            'connection_info' => [
                'type' => 'sqlite',
                'database' => $this->config['database'],
            ]
        ];
        
        try {
            $stats['database_size_bytes'] = filesize($this->config['database']);
            
            $tables = $this->query("SELECT name FROM sqlite_master WHERE type='table'");
            $stats['table_count'] = count($tables);
            $stats['tables'] = array_column($tables, 'name');
        } catch (\Exception $e) {
            $this->logger->warning('Could not get SQLite stats', ['error' => $e->getMessage()]);
        }
        
        return $stats;
    }
    
    private function getMySQLStats(): array
    {
        $stats = [
            'connection_info' => [
                'type' => 'mysql',
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'database' => $this->config['database'],
                'charset' => $this->config['charset'],
            ]
        ];
        
        try {
            // 数据库大小
            $sql = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = :database";
            $result = $this->query($sql, ['database' => $this->config['database']]);
            $stats['database_size_mb'] = $result[0]['size_mb'] ?? 0;
            
            // 表数量
            $sql = "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = :database";
            $result = $this->query($sql, ['database' => $this->config['database']]);
            $stats['table_count'] = $result[0]['count'] ?? 0;
        } catch (\Exception $e) {
            $this->logger->warning('Could not get MySQL stats', ['error' => $e->getMessage()]);
        }
        
        return $stats;
    }
}
