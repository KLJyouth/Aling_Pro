<?php

namespace AlingAi\Services;

use AlingAi\Config\EnhancedConfig;
use PDO;
use PDOException;
use MongoDB\Client as MongoClient;
use Redis;

/**
 * 增强数据库服务
 * 支持MySQL、MongoDB和Redis连接管理
 */
class EnhancedDatabaseService
{
    private static $instance = null;
    private $config;
    private $connections = [];
    private $redis = null;
    private $mongodb = null;

    private function __construct()
    {
        $this->config = EnhancedConfig::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取MySQL连接
     */
    public function getMysqlConnection(): PDO
    {
        if (!isset($this->connections['mysql'])) {
            $config = $this->config->getDatabaseConfig('mysql');
            
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                
                // 设置时区
                $pdo->exec("SET time_zone = '+08:00'");
                
                // 设置字符集
                $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $this->connections['mysql'] = $pdo;
                
                $this->logConnection('MySQL', $config['host'], $config['port'], $config['database']);
                
            } catch (PDOException $e) {
                $this->logError('MySQL连接失败', $e->getMessage());
                throw new \Exception('MySQL数据库连接失败: ' . $e->getMessage());
            }
        }

        return $this->connections['mysql'];
    }

    /**
     * 获取MongoDB连接
     */
    public function getMongoConnection(): MongoClient
    {
        if ($this->mongodb === null) {
            $uri = $this->config->get('database.mongodb.uri');
            
            try {
                $this->mongodb = new MongoClient($uri);
                
                // 测试连接
                $this->mongodb->selectDatabase('admin')->command(['ping' => 1]);
                
                $this->logConnection('MongoDB', parse_url($uri, PHP_URL_HOST), parse_url($uri, PHP_URL_PORT), parse_url($uri, PHP_URL_PATH));
                
            } catch (\Exception $e) {
                $this->logError('MongoDB连接失败', $e->getMessage());
                throw new \Exception('MongoDB数据库连接失败: ' . $e->getMessage());
            }
        }

        return $this->mongodb;
    }

    /**
     * 获取Redis连接
     */
    public function getRedisConnection(): Redis
    {
        if ($this->redis === null) {
            $config = $this->config->get('redis');
            
            try {
                $this->redis = new Redis();
                $this->redis->connect($config['host'], $config['port']);
                
                if (!empty($config['password'])) {
                    $this->redis->auth($config['password']);
                }
                
                $this->redis->select($config['database']);
                
                // 设置前缀
                if (!empty($config['prefix'])) {
                    $this->redis->setOption(Redis::OPT_PREFIX, $config['prefix']);
                }
                
                $this->logConnection('Redis', $config['host'], $config['port'], $config['database']);
                
            } catch (\Exception $e) {
                $this->logError('Redis连接失败', $e->getMessage());
                throw new \Exception('Redis连接失败: ' . $e->getMessage());
            }
        }

        return $this->redis;
    }

    /**
     * 执行MySQL查询
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $pdo = $this->getMysqlConnection();
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('SQL查询失败', $e->getMessage(), ['sql' => $sql, 'params' => $params]);
            throw new \Exception('数据库查询失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取单条记录
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * 获取多条记录
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 插入记录
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        
        return (int) $this->getMysqlConnection()->lastInsertId();
    }

    /**
     * 更新记录
     */
    public function update(string $table, array $data, array $where): int
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        
        $whereClause = [];
        foreach (array_keys($where) as $column) {
            $whereClause[] = "{$column} = :where_{$column}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . 
               " WHERE " . implode(' AND ', $whereClause);
        
        // 合并参数
        $params = $data;
        foreach ($where as $key => $value) {
            $params["where_{$key}"] = $value;
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * 删除记录
     */
    public function delete(string $table, array $where): int
    {
        $whereClause = [];
        foreach (array_keys($where) as $column) {
            $whereClause[] = "{$column} = :{$column}";
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->query($sql, $where);
        return $stmt->rowCount();
    }

    /**
     * 开始事务
     */
    public function beginTransaction(): bool
    {
        return $this->getMysqlConnection()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit(): bool
    {
        return $this->getMysqlConnection()->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback(): bool
    {
        return $this->getMysqlConnection()->rollback();
    }

    /**
     * Redis缓存操作
     */
    public function cacheGet(string $key)
    {
        try {
            $redis = $this->getRedisConnection();
            $value = $redis->get($key);
            
            if ($value === false) {
                return null;
            }
            
            // 尝试解析JSON
            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $value;
            
        } catch (\Exception $e) {
            $this->logError('Redis读取失败', $e->getMessage(), ['key' => $key]);
            return null;
        }
    }

    /**
     * Redis缓存设置
     */
    public function cacheSet(string $key, $value, int $ttl = 3600): bool
    {
        try {
            $redis = $this->getRedisConnection();
            
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            
            return $redis->setex($key, $ttl, $value);
            
        } catch (\Exception $e) {
            $this->logError('Redis写入失败', $e->getMessage(), ['key' => $key]);
            return false;
        }
    }

    /**
     * Redis缓存删除
     */
    public function cacheDelete(string $key): bool
    {
        try {
            $redis = $this->getRedisConnection();
            return $redis->del($key) > 0;
        } catch (\Exception $e) {
            $this->logError('Redis删除失败', $e->getMessage(), ['key' => $key]);
            return false;
        }
    }

    /**
     * 获取MongoDB集合
     */
    public function getMongoCollection(string $database, string $collection)
    {
        $client = $this->getMongoConnection();
        return $client->selectDatabase($database)->selectCollection($collection);
    }

    /**
     * 健康检查
     */
    public function healthCheck(): array
    {
        $status = [
            'mysql' => false,
            'redis' => false,
            'mongodb' => false,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // MySQL健康检查
        try {
            $pdo = $this->getMysqlConnection();
            $stmt = $pdo->query('SELECT 1');
            $status['mysql'] = $stmt !== false;
        } catch (\Exception $e) {
            $this->logError('MySQL健康检查失败', $e->getMessage());
        }

        // Redis健康检查
        try {
            $redis = $this->getRedisConnection();
            $status['redis'] = $redis->ping() === '+PONG';
        } catch (\Exception $e) {
            $this->logError('Redis健康检查失败', $e->getMessage());
        }

        // MongoDB健康检查
        try {
            $mongo = $this->getMongoConnection();
            $result = $mongo->selectDatabase('admin')->command(['ping' => 1]);
            $status['mongodb'] = isset($result->toArray()[0]['ok']) && $result->toArray()[0]['ok'] == 1;
        } catch (\Exception $e) {
            $this->logError('MongoDB健康检查失败', $e->getMessage());
        }

        return $status;
    }

    /**
     * 记录连接日志
     */
    private function logConnection(string $type, string $host, int $port, string $database): void
    {
        error_log(sprintf(
            '[%s] %s连接成功: %s:%d/%s',
            date('Y-m-d H:i:s'),
            $type,
            $host,
            $port,
            $database
        ));
    }

    /**
     * 记录错误日志
     */
    private function logError(string $message, string $error, array $context = []): void
    {
        $logMessage = sprintf(
            '[%s] %s: %s',
            date('Y-m-d H:i:s'),
            $message,
            $error
        );
        
        if (!empty($context)) {
            $logMessage .= ' Context: ' . json_encode($context);
        }
        
        error_log($logMessage);
    }

    /**
     * 关闭所有连接
     */
    public function closeConnections(): void
    {
        foreach ($this->connections as $connection) {
            $connection = null;
        }
        
        if ($this->redis) {
            $this->redis->close();
            $this->redis = null;
        }
        
        $this->mongodb = null;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->closeConnections();
    }

    /**
     * 获取健康状态（API兼容方法）
     */
    public function getHealthStatus(): array
    {
        return $this->healthCheck();
    }

    /**
     * 获取MySQL状态
     */
    public function getMySQLStatus(): array
    {
        try {
            $pdo = $this->getMysqlConnection();
            $status = $pdo->query('SHOW STATUS')->fetchAll(PDO::FETCH_KEY_PAIR);
            
            return [
                'connected' => true,
                'threads_connected' => $status['Threads_connected'] ?? 0,
                'threads_running' => $status['Threads_running'] ?? 0,
                'uptime' => $status['Uptime'] ?? 0,
                'queries' => $status['Queries'] ?? 0,
                'slow_queries' => $status['Slow_queries'] ?? 0,
                'version' => $pdo->query('SELECT VERSION()')->fetchColumn()
            ];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 获取Redis状态
     */
    public function getRedisStatus(): array
    {
        try {
            $redis = $this->getRedisConnection();
            $info = $redis->info();
            
            return [
                'connected' => true,
                'used_memory' => $info['used_memory'] ?? 0,
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'version' => $info['redis_version'] ?? 'unknown'
            ];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 获取MongoDB状态
     */
    public function getMongoDBStatus(): array
    {
        try {
            $mongo = $this->getMongoConnection();
            $admin = $mongo->selectDatabase('admin');
            $status = $admin->command(['serverStatus' => 1])->toArray()[0];
            
            return [
                'connected' => true,
                'version' => $status['version'] ?? 'unknown',
                'uptime' => $status['uptime'] ?? 0,
                'connections_current' => $status['connections']['current'] ?? 0,
                'connections_available' => $status['connections']['available'] ?? 0,
                'operations' => $status['opcounters'] ?? []
            ];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 清除过期缓存
     */
    public function clearExpiredCache(): array
    {
        try {
            $redis = $this->getRedisConnection();
            $keys = $redis->keys('*');
            $expiredCount = 0;
            
            foreach ($keys as $key) {
                $ttl = $redis->ttl($key);
                if ($ttl === -2) { // 已过期
                    $expiredCount++;
                }
            }
            
            return [
                'total_keys' => count($keys),
                'expired_removed' => $expiredCount,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            $this->logError('清除过期缓存失败', $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取配置
     */
    public function getConfiguration(string $section = null): array
    {
        try {
            if ($section) {
                return $this->config->get($section, []);
            }
            return $this->config->all();
        } catch (\Exception $e) {
            $this->logError('获取配置失败', $e->getMessage());
            return [];
        }
    }

    /**
     * 更新配置
     */
    public function updateConfiguration(string $key, $value, string $type = 'string'): bool
    {
        try {
            // 类型转换
            switch ($type) {
                case 'int':
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'bool':
                case 'boolean':
                    $value = (bool) $value;
                    break;
                case 'float':
                    $value = (float) $value;
                    break;
                case 'array':
                    if (is_string($value)) {
                        $value = json_decode($value, true) ?? [];
                    }
                    break;
                default:
                    $value = (string) $value;
            }

            // 更新配置到数据库
            $sql = "INSERT INTO system_config (config_key, config_value, config_type) 
                    VALUES (:key, :value, :type) 
                    ON DUPLICATE KEY UPDATE 
                    config_value = :value, config_type = :type, updated_at = NOW()";
            
            $this->query($sql, [
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError('更新配置失败', $e->getMessage());
            return false;
        }
    }
}
