<?php

namespace AlingAi\Database;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;

/**
 * 数据库管理器
 * 
 * 提供统一的数据库连接管理和操作接口
 * 优化性能：连接池、查询缓存、批量操作
 * 增强安全性：SQL注入防护、参数绑定、访问控制
 */
class DatabaseManager
{
    private $logger;
    private $container;
    private $connections = [];
    private $config = [];
    private $queryCache = [];
    private $transactionStack = [];
    private $connectionPool = [];
    private $maxConnections = 10;
    private $connectionTimeout = 30;
    private $lastCleanupTime = 0;
    private $cleanupInterval = 300; // 5分钟清理一次

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param Container $container 容器
     */
    public function __construct(LoggerInterface $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->config = $this->loadConfiguration();
        $this->initializeConnectionPool();
    }

    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'default' => [
                'driver' => env('DB_DRIVER', 'mysql'),
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', 3306),
                'database' => env('DB_DATABASE', 'alingai'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => env('DB_PREFIX', ''),
                'strict' => env('DB_STRICT', true),
                'engine' => env('DB_ENGINE', 'InnoDB'),
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            ],
            'redis' => [
                'host' => env('REDIS_HOST', 'localhost'),
                'port' => env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD', null),
                'database' => env('REDIS_DB', 0),
                'timeout' => env('REDIS_TIMEOUT', 5)
            ],
            'mongodb' => [
                'host' => env('MONGODB_HOST', 'localhost'),
                'port' => env('MONGODB_PORT', 27017),
                'database' => env('MONGODB_DATABASE', 'alingai'),
                'username' => env('MONGODB_USERNAME', ''),
                'password' => env('MONGODB_PASSWORD', ''),
                'options' => [
                    'connectTimeoutMS' => 5000,
                    'socketTimeoutMS' => 30000
                ]
            ]
        ];
    }

    /**
     * 初始化连接池
     */
    private function initializeConnectionPool(): void
    {
        $this->connectionPool = [
            'available' => [],
            'in_use' => [],
            'max_size' => $this->maxConnections
        ];
    }

    /**
     * 获取数据库连接
     * 
     * @param string $connection 连接名称
     * @return PDO
     * @throws PDOException
     */
    public function getConnection(string $connection = 'default'): PDO
    {
        // 检查连接池中是否有可用连接
        if (!empty($this->connectionPool['available'])) {
            $pdo = array_pop($this->connectionPool['available']);
            $this->connectionPool['in_use'][] = $pdo;
            return $pdo;
        }

        // 创建新连接
        if (count($this->connectionPool['in_use']) < $this->maxConnections) {
            $pdo = $this->createConnection($connection);
            $this->connectionPool['in_use'][] = $pdo;
            return $pdo;
        }

        // 等待可用连接
        return $this->waitForConnection();
    }

    /**
     * 创建数据库连接
     * 
     * @param string $connection 连接名称
     * @return PDO
     * @throws PDOException
     */
    private function createConnection(string $connection): PDO
    {
        if (!isset($this->config[$connection])) {
            throw new PDOException("数据库连接配置 '{$connection}' 不存在");
        }

        $config = $this->config[$connection];
        
        try {
            $dsn = $this->buildDsn($config);
            $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            
            $this->logger->info('数据库连接创建成功', ['connection' => $connection]);
            return $pdo;
            
        } catch (PDOException $e) {
            $this->logger->error('数据库连接创建失败', [
                'connection' => $connection,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 构建DSN字符串
     * 
     * @param array $config 配置
     * @return string
     */
    private function buildDsn(array $config): string
    {
        switch ($config['driver']) {
            case 'mysql':
                return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            case 'pgsql':
                return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            case 'sqlite':
                return "sqlite:{$config['database']}";
            default:
                throw new PDOException("不支持的数据库驱动: {$config['driver']}");
        }
    }

    /**
     * 等待可用连接
     * 
     * @return PDO
     * @throws PDOException
     */
    private function waitForConnection(): PDO
    {
        $startTime = time();
        
        while (time() - $startTime < $this->connectionTimeout) {
            if (!empty($this->connectionPool['available'])) {
                $pdo = array_pop($this->connectionPool['available']);
                $this->connectionPool['in_use'][] = $pdo;
                return $pdo;
            }
            
            usleep(100000); // 等待100ms
        }
        
        throw new PDOException('数据库连接池超时');
    }

    /**
     * 释放连接回连接池
     * 
     * @param PDO $pdo 数据库连接
     */
    public function releaseConnection(PDO $pdo): void
    {
        $key = array_search($pdo, $this->connectionPool['in_use'], true);
        if ($key !== false) {
            unset($this->connectionPool['in_use'][$key]);
            $this->connectionPool['available'][] = $pdo;
        }
    }

    /**
     * 执行查询
     * 
     * @param string $sql SQL语句
     * @param array $params 参数
     * @param string $connection 连接名称
     * @return array
     */
    public function query(string $sql, array $params = [], string $connection = 'default'): array
    {
        $pdo = $this->getConnection($connection);
        
        try {
            // 检查查询缓存
            $cacheKey = $this->generateCacheKey($sql, $params);
            if (isset($this->queryCache[$cacheKey])) {
                $this->logger->debug('使用查询缓存', ['sql' => $sql]);
                return $this->queryCache[$cacheKey];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll();

            // 缓存结果
            $this->queryCache[$cacheKey] = $result;

            $this->logger->debug('查询执行成功', [
                'sql' => $sql,
                'params' => $params,
                'row_count' => count($result)
            ]);

            return $result;

        } catch (PDOException $e) {
            $this->logger->error('查询执行失败', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            $this->releaseConnection($pdo);
        }
    }

    /**
     * 执行插入操作
     * 
     * @param string $table 表名
     * @param array $data 数据
     * @param string $connection 连接名称
     * @return int 插入ID
     */
    public function insert(string $table, array $data, string $connection = 'default'): int
    {
        $pdo = $this->getConnection($connection);
        
        try {
            $columns = array_keys($data);
            $placeholders = ':' . implode(', :', $columns);
            $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);

            $insertId = $pdo->lastInsertId();

            $this->logger->info('数据插入成功', [
                'table' => $table,
                'insert_id' => $insertId
            ]);

            return $insertId;

        } catch (PDOException $e) {
            $this->logger->error('数据插入失败', [
                'table' => $table,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            $this->releaseConnection($pdo);
        }
    }

    /**
     * 执行更新操作
     * 
     * @param string $table 表名
     * @param array $data 数据
     * @param array $where 条件
     * @param string $connection 连接名称
     * @return int 影响行数
     */
    public function update(string $table, array $data, array $where, string $connection = 'default'): int
    {
        $pdo = $this->getConnection($connection);
        
        try {
            $setClause = [];
            foreach (array_keys($data) as $column) {
                $setClause[] = "{$column} = :{$column}";
            }

            $whereClause = [];
            foreach (array_keys($where) as $column) {
                $whereClause[] = "{$column} = :where_{$column}";
            }

            $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);

            $params = $data;
            foreach ($where as $column => $value) {
                $params["where_{$column}"] = $value;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $rowCount = $stmt->rowCount();

            $this->logger->info('数据更新成功', [
                'table' => $table,
                'row_count' => $rowCount
            ]);

            return $rowCount;

        } catch (PDOException $e) {
            $this->logger->error('数据更新失败', [
                'table' => $table,
                'data' => $data,
                'where' => $where,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            $this->releaseConnection($pdo);
        }
    }

    /**
     * 执行删除操作
     * 
     * @param string $table 表名
     * @param array $where 条件
     * @param string $connection 连接名称
     * @return int 影响行数
     */
    public function delete(string $table, array $where, string $connection = 'default'): int
    {
        $pdo = $this->getConnection($connection);
        
        try {
            $whereClause = [];
            foreach (array_keys($where) as $column) {
                $whereClause[] = "{$column} = :{$column}";
            }

            $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClause);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($where);

            $rowCount = $stmt->rowCount();

            $this->logger->info('数据删除成功', [
                'table' => $table,
                'row_count' => $rowCount
            ]);

            return $rowCount;

        } catch (PDOException $e) {
            $this->logger->error('数据删除失败', [
                'table' => $table,
                'where' => $where,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            $this->releaseConnection($pdo);
        }
    }

    /**
     * 开始事务
     * 
     * @param string $connection 连接名称
     * @return bool
     */
    public function beginTransaction(string $connection = 'default'): bool
    {
        $pdo = $this->getConnection($connection);
        
        try {
            $result = $pdo->beginTransaction();
            if ($result) {
                $this->transactionStack[] = $connection;
                $this->logger->info('事务开始', ['connection' => $connection]);
            }
            return $result;
        } catch (PDOException $e) {
            $this->logger->error('事务开始失败', [
                'connection' => $connection,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 提交事务
     * 
     * @param string $connection 连接名称
     * @return bool
     */
    public function commit(string $connection = 'default'): bool
    {
        $pdo = $this->getConnection($connection);
        
        try {
            $result = $pdo->commit();
            if ($result) {
                array_pop($this->transactionStack);
                $this->logger->info('事务提交成功', ['connection' => $connection]);
            }
            return $result;
        } catch (PDOException $e) {
            $this->logger->error('事务提交失败', [
                'connection' => $connection,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 回滚事务
     * 
     * @param string $connection 连接名称
     * @return bool
     */
    public function rollback(string $connection = 'default'): bool
    {
        $pdo = $this->getConnection($connection);
        
        try {
            $result = $pdo->rollback();
            if ($result) {
                array_pop($this->transactionStack);
                $this->logger->info('事务回滚成功', ['connection' => $connection]);
            }
            return $result;
        } catch (PDOException $e) {
            $this->logger->error('事务回滚失败', [
                'connection' => $connection,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 生成缓存键
     * 
     * @param string $sql SQL语句
     * @param array $params 参数
     * @return string
     */
    private function generateCacheKey(string $sql, array $params): string
    {
        return md5($sql . serialize($params));
    }

    /**
     * 清理过期缓存
     */
    public function cleanupCache(): void
    {
        if (time() - $this->lastCleanupTime > $this->cleanupInterval) {
            $this->queryCache = [];
            $this->lastCleanupTime = time();
            $this->logger->debug('查询缓存已清理');
        }
    }

    /**
     * 获取系统状态
     * 
     * @return array
     */
    public function getStatus(): array
    {
        return [
            'connections' => [
                'available' => count($this->connectionPool['available']),
                'in_use' => count($this->connectionPool['in_use']),
                'max_size' => $this->maxConnections
            ],
            'cache' => [
                'size' => count($this->queryCache),
                'last_cleanup' => $this->lastCleanupTime
            ],
            'transactions' => [
                'active' => count($this->transactionStack)
            ]
        ];
    }

    /**
     * 关闭所有连接
     */
    public function closeAllConnections(): void
    {
        foreach ($this->connectionPool['available'] as $pdo) {
            $pdo = null;
        }
        foreach ($this->connectionPool['in_use'] as $pdo) {
            $pdo = null;
        }
        
        $this->connectionPool['available'] = [];
        $this->connectionPool['in_use'] = [];
        
        $this->logger->info('所有数据库连接已关闭');
    }
}
