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
    private PDO $pdo;
    private Logger $logger;
    private array $config;
      public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->config = $this->loadConfig();
        $this->initializeDatabase();
    }    private function loadConfig(): array
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
        $connection = $this->config['connection'];
        
        if ($connection === 'sqlite') {
            $dsn = 'sqlite:' . $this->config['database'];
            $username = null;
            $password = null;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30,
            ];
        } else {
            // MySQL DSN
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );
            $username = $this->config['username'];
            $password = $this->config['password'];
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']} COLLATE {$this->config['collation']}",
                PDO::ATTR_TIMEOUT => 30,
            ];
        }
        
        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
            $this->logger->info('Database connection established successfully', ['connection' => $connection]);
        } catch (PDOException $e) {
            $this->logger->error('Database connection failed', ['error' => $e->getMessage(), 'connection' => $connection]);
            throw new \RuntimeException('Unable to connect to database: ' . $e->getMessage());
        }
    }
    
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
    
    public function beginTransaction(): bool
    {
        try {
            return $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            $this->logger->error('Failed to begin transaction', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function commit(): bool
    {
        try {
            return $this->pdo->commit();
        } catch (PDOException $e) {
            $this->logger->error('Failed to commit transaction', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function rollback(): bool
    {
        try {
            return $this->pdo->rollback();
        } catch (PDOException $e) {
            $this->logger->error('Failed to rollback transaction', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error('Query execution failed', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Query execution failed: ' . $e->getMessage());
        }
    }
    
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logger->error('SQL execution failed', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('SQL execution failed: ' . $e->getMessage());
        }
    }
    
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
    
    public function rowCount(): int
    {
        return $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
    }
    
    public function tableExists(string $tableName): bool
    {
        try {
            $sql = "SHOW TABLES LIKE :tableName";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['tableName' => $this->config['prefix'] . $tableName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logger->error('Table existence check failed', [
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public function getTableColumns(string $tableName): array
    {
        try {
            $sql = "DESCRIBE " . $this->config['prefix'] . $tableName;
            return $this->query($sql);
        } catch (PDOException $e) {
            $this->logger->error('Failed to get table columns', [
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    public function createDatabase(): bool
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['charset']
            );
            
            $pdo = new PDO($dsn, $this->config['username'], $this->config['password']);
            $sql = "CREATE DATABASE IF NOT EXISTS `{$this->config['database']}` CHARACTER SET {$this->config['charset']} COLLATE {$this->config['collation']}";
            
            $result = $pdo->exec($sql);
            $this->logger->info('Database created successfully', ['database' => $this->config['database']]);
            
            return $result !== false;
        } catch (PDOException $e) {
            $this->logger->error('Database creation failed', [
                'database' => $this->config['database'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public function runMigrations(): bool
    {
        try {
            $migrationsPath = APP_ROOT . '/database/migrations';
            if (!is_dir($migrationsPath)) {
                $this->logger->warning('Migrations directory not found', ['path' => $migrationsPath]);
                return false;
            }
            
            // 创建迁移表
            $this->createMigrationsTable();
            
            $files = glob($migrationsPath . '/*.php');
            sort($files);
            
            foreach ($files as $file) {
                $migrationName = basename($file, '.php');
                
                if ($this->migrationExists($migrationName)) {
                    continue;
                }
                
                require_once $file;
                $className = $this->getMigrationClassName($migrationName);
                
                if (class_exists($className)) {
                    $migration = new $className($this);
                    $migration->up();
                    $this->recordMigration($migrationName);
                    
                    $this->logger->info('Migration executed', ['migration' => $migrationName]);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Migration execution failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    private function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->config['prefix']}migrations` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `migration` varchar(255) NOT NULL,
                `batch` int(11) NOT NULL,
                `executed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->config['charset']} COLLATE={$this->config['collation']}
        ";
        
        $this->execute($sql);
    }
    
    private function migrationExists(string $migrationName): bool
    {
        $sql = "SELECT COUNT(*) FROM `{$this->config['prefix']}migrations` WHERE migration = :migration";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['migration' => $migrationName]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function recordMigration(string $migrationName): void
    {
        $sql = "INSERT INTO `{$this->config['prefix']}migrations` (migration, batch) VALUES (:migration, 1)";
        $this->execute($sql, ['migration' => $migrationName]);
    }
    
    private function getMigrationClassName(string $migrationName): string
    {
        // 从文件名提取类名，例如: 2024_01_01_000000_create_users_table -> CreateUsersTable
        $parts = explode('_', $migrationName);
        $className = '';
        
        for ($i = 4; $i < count($parts); $i++) {
            $className .= ucfirst($parts[$i]);
        }
        
        return $className;
    }
    
    public function getStats(): array
    {
        try {
            $stats = [];
            
            // 数据库大小
            $sql = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = :database";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['database' => $this->config['database']]);
            $stats['database_size_mb'] = $stmt->fetchColumn() ?: 0;
            
            // 表数量
            $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :database";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['database' => $this->config['database']]);
            $stats['table_count'] = $stmt->fetchColumn();
            
            // 连接信息
            $stats['connection_info'] = [
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'database' => $this->config['database'],
                'charset' => $this->config['charset'],
            ];
            
            return $stats;
        } catch (PDOException $e) {
            $this->logger->error('Failed to get database stats', ['error' => $e->getMessage()]);
            return [];
        }
    }
      /**
     * 获取数据库连接
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * 查找单条记录
     */
    public function find(string $table, $id): ?array
    {
        return $this->selectOne($table, ['id' => $id]);
    }

    /**
     * 查找所有记录
     */
    public function findAll(string $table, array $conditions = []): array
    {
        return $this->select($table, $conditions);
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
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute($data);
        } catch (PDOException $e) {
            $this->logger->error('Insert failed', [
                'table' => $table,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Insert failed: ' . $e->getMessage());
        }
    }
      /**
     * 获取数据库连接
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * 查找单条记录
     */
    public function find(string $table, $id): ?array
    {
        return $this->selectOne($table, ['id' => $id]);
    }

    /**
     * 查找所有记录
     */
    public function findAll(string $table, array $conditions = []): array
    {
        return $this->select($table, $conditions);
    }

    /**
     * 更新数据（接口兼容版本）
     */
    public function update(string $table, $id, array $data): bool
    {
        return $this->updateRecord($table, $data, ['id' => $id]);
    }

    /**
     * 删除数据（接口兼容版本）
     */
    public function delete(string $table, $id): bool
    {
        return $this->deleteRecord($table, ['id' => $id]);
    }

    /**
     * 更新数据（原始版本，重命名）
     */
    public function updateRecord(string $table, array $data, array $where): bool
    {
        try {
            $table = $this->config['prefix'] . $table;
            
            $setClause = [];
            foreach ($data as $key => $value) {
                $setClause[] = "`{$key}` = :{$key}";
            }
            $setClause = implode(', ', $setClause);
            
            $whereClause = [];
            foreach ($where as $key => $value) {
                $whereClause[] = "`{$key}` = :where_{$key}";
            }
            $whereClause = implode(' AND ', $whereClause);
            
            $sql = "UPDATE `{$table}` SET {$setClause} WHERE {$whereClause}";
            $stmt = $this->pdo->prepare($sql);
            
            // 合并参数
            $params = $data;
            foreach ($where as $key => $value) {
                $params['where_' . $key] = $value;
            }
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logger->error('Update failed', [
                'table' => $table,
                'data' => $data,
                'where' => $where,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Update failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 删除数据
     */
    public function delete(string $table, array $where): bool
    {
        try {
            $table = $this->config['prefix'] . $table;
            
            $whereClause = [];
            foreach ($where as $key => $value) {
                $whereClause[] = "`{$key}` = :{$key}";
            }
            $whereClause = implode(' AND ', $whereClause);
            
            $sql = "DELETE FROM `{$table}` WHERE {$whereClause}";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute($where);
        } catch (PDOException $e) {
            $this->logger->error('Delete failed', [
                'table' => $table,
                'where' => $where,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Delete failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 选择多条数据
     */
    public function select(string $table, array $where = [], array $options = []): array
    {
        try {
            $table = $this->config['prefix'] . $table;
            $sql = "SELECT * FROM `{$table}`";
            $params = [];
            
            if (!empty($where)) {
                $whereClause = [];
                foreach ($where as $key => $value) {
                    $whereClause[] = "`{$key}` = :{$key}";
                    $params[$key] = $value;
                }
                $sql .= ' WHERE ' . implode(' AND ', $whereClause);
            }
            
            // 添加排序
            if (isset($options['order'])) {
                $sql .= ' ORDER BY ' . $options['order'];
            }
            
            // 添加分页
            if (isset($options['limit'])) {
                $sql .= ' LIMIT ' . (int)$options['limit'];
                if (isset($options['offset'])) {
                    $sql .= ' OFFSET ' . (int)$options['offset'];
                }
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error('Select failed', [
                'table' => $table,
                'where' => $where,
                'options' => $options,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Select failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 选择单条数据
     */
    public function selectOne(string $table, array $where = []): ?array
    {
        $results = $this->select($table, $where, ['limit' => 1]);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * 计数查询
     */
    public function count(string $table, array $where = []): int
    {
        try {
            $table = $this->config['prefix'] . $table;
            $sql = "SELECT COUNT(*) FROM `{$table}`";
            $params = [];
            
            if (!empty($where)) {
                $whereClause = [];
                foreach ($where as $key => $value) {
                    $whereClause[] = "`{$key}` = :{$key}";
                    $params[$key] = $value;
                }
                $sql .= ' WHERE ' . implode(' AND ', $whereClause);
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logger->error('Count failed', [
                'table' => $table,
                'where' => $where,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Count failed: ' . $e->getMessage());
        }
    }
}
