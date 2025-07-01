<?php
namespace App\Core;

/**
 * 数据库连接管理类
 * 单例模式，负责创建和管理数据库连接
 */
class Database
{
    /**
     * 数据库连接实例集合
     * @var array
     */
    private static $connections = [];
    
    /**
     * 当前使用的连接名称
     * @var string
     */
    private static $currentConnection = 'default';
    
    /**
     * 数据库配置
     * @var array
     */
    private static $config = [];
    
    /**
     * 私有构造函数，防止直接创建对象
     */
    private function __construct() {}
    
    /**
     * 私有克隆方法，防止克隆对象
     */
    private function __clone() {}
    
    /**
     * 获取数据库连接实例
     * @param string|null $connection 连接名称，null表示使用当前连接
     * @return \PDO 数据库连接
     */
    public static function getInstance($connection = null)
    {
        // 确定连接名称
        $connection = $connection ?: self::$currentConnection;
        
        // 如果连接不存在，创建连接
        if (!isset(self::$connections[$connection])) {
            self::connect($connection);
        }
        
        return self::$connections[$connection];
    }
    
    /**
     * 切换当前连接
     * @param string $connection 连接名称
     * @return void
     */
    public static function connection($connection)
    {
        self::$currentConnection = $connection;
        
        // 如果连接不存在，创建连接
        if (!isset(self::$connections[$connection])) {
            self::connect($connection);
        }
        
        return new static();
    }
    
    /**
     * 设置数据库配置
     * @param array $config 数据库配置
     * @return void
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;
        
        // 设置默认连接
        if (isset($config['default'])) {
            self::$currentConnection = $config['default'];
        }
    }
    
    /**
     * 创建数据库连接
     * @param string $connection 连接名称
     * @throws \Exception 连接失败时抛出异常
     * @return void
     */
    private static function connect($connection)
    {
        try {
            // 获取指定连接的配置
            $connectionConfig = self::getConnectionConfig($connection);
            
            // 检查配置是否完整
            if (!isset($connectionConfig['driver'])) {
                throw new \Exception("数据库连接 '{$connection}' 的配置不完整: 缺少 'driver'");
            }
            
            // 根据不同的驱动创建连接
            switch ($connectionConfig['driver']) {
                case 'mysql':
                    self::createMySQLConnection($connection, $connectionConfig);
                    break;
                case 'sqlite':
                    self::createSQLiteConnection($connection, $connectionConfig);
                    break;
                default:
                    throw new \Exception("不支持的数据库驱动: {$connectionConfig['driver']}");
            }
            
        } catch (\PDOException $e) {
            // 记录错误日志
            Logger::error('数据库连接失败', [
                'connection' => $connection,
                'error' => $e->getMessage()
            ]);
            
            // 抛出自定义异常
            throw new \Exception('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 创建MySQL连接
     * @param string $connection 连接名称
     * @param array $config 连接配置
     * @return void
     */
    private static function createMySQLConnection($connection, array $config)
    {
        // 检查必要的配置参数
        if (empty($config['host']) || !isset($config['username']) || !isset($config['database'])) {
            throw new \Exception("MySQL连接配置不完整");
        }
        
        // 构建DSN
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'] ?? 3306,
            $config['database'],
            $config['charset'] ?? 'utf8mb4'
        );
        
        // 设置默认选项
        $options = $config['options'] ?? [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        // 创建PDO实例
        self::$connections[$connection] = new \PDO(
            $dsn,
            $config['username'],
            $config['password'] ?? '',
            $options
        );
    }
    
    /**
     * 创建SQLite连接
     * @param string $connection 连接名称
     * @param array $config 连接配置
     * @return void
     */
    private static function createSQLiteConnection($connection, array $config)
    {
        // 检查必要的配置参数
        if (empty($config['database'])) {
            throw new \Exception("SQLite连接配置不完整: 缺少 'database'");
        }
        
        // 构建DSN
        $dsn = 'sqlite:' . $config['database'];
        
        // 设置默认选项
        $options = $config['options'] ?? [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ];
        
        // 创建PDO实例
        self::$connections[$connection] = new \PDO($dsn, null, null, $options);
    }
    
    /**
     * 获取指定连接的配置
     * @param string $connection 连接名称
     * @return array 连接配置
     */
    private static function getConnectionConfig($connection)
    {
        // 处理默认连接
        if ($connection === 'default') {
            if (isset(self::$config['default']) && isset(self::$config['connections'][self::$config['default']])) {
                return self::$config['connections'][self::$config['default']];
            }
            throw new \Exception('未定义默认数据库连接');
        }
        
        // 处理指定连接
        if (isset(self::$config['connections'][$connection])) {
            return self::$config['connections'][$connection];
        }
        
        throw new \Exception("未定义数据库连接: {$connection}");
    }
    
    /**
     * 准备SQL语句
     * @param string $sql SQL语句
     * @param string|null $connection 连接名称
     * @return \PDOStatement 预处理语句
     */
    public static function prepare($sql, $connection = null)
    {
        return self::getInstance($connection)->prepare($sql);
    }
    
    /**
     * 执行查询并返回一条结果
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @param string|null $connection 连接名称
     * @return array|false 查询结果或失败时返回false
     */
    public static function fetchOne($sql, array $params = [], $connection = null)
    {
        $stmt = self::prepareAndExecute($sql, $params, $connection);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 执行查询并返回所有结果
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @param string|null $connection 连接名称
     * @return array 查询结果数组
     */
    public static function fetchAll($sql, array $params = [], $connection = null)
    {
        $stmt = self::prepareAndExecute($sql, $params, $connection);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 执行SQL语句
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @param string|null $connection 连接名称
     * @return int 受影响的行数
     */
    public static function execute($sql, array $params = [], $connection = null)
    {
        $stmt = self::prepareAndExecute($sql, $params, $connection);
        return $stmt->rowCount();
    }
    
    /**
     * 获取最后插入的ID
     * @param string|null $connection 连接名称
     * @return string 最后插入的ID
     */
    public static function lastInsertId($connection = null)
    {
        return self::getInstance($connection)->lastInsertId();
    }
    
    /**
     * 开始事务
     * @param string|null $connection 连接名称
     * @return bool 是否成功
     */
    public static function beginTransaction($connection = null)
    {
        return self::getInstance($connection)->beginTransaction();
    }
    
    /**
     * 提交事务
     * @param string|null $connection 连接名称
     * @return bool 是否成功
     */
    public static function commit($connection = null)
    {
        return self::getInstance($connection)->commit();
    }
    
    /**
     * 回滚事务
     * @param string|null $connection 连接名称
     * @return bool 是否成功
     */
    public static function rollback($connection = null)
    {
        return self::getInstance($connection)->rollBack();
    }
    
    /**
     * 检查表是否存在
     * @param string $table 表名
     * @param string|null $connection 连接名称
     * @return bool 表是否存在
     */
    public static function tableExists($table, $connection = null)
    {
        try {
            $conn = self::getInstance($connection);
            $driver = $conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
            
            if ($driver === 'mysql') {
                $stmt = $conn->query("SHOW TABLES LIKE '{$table}'");
                return $stmt->rowCount() > 0;
            } elseif ($driver === 'sqlite') {
                $stmt = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'");
                return $stmt->rowCount() > 0;
            }
            
            return false;
        } catch (\Exception $e) {
            Logger::error('检查表是否存在时出错', [
                'table' => $table,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 准备并执行SQL语句
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @param string|null $connection 连接名称
     * @return \PDOStatement 执行结果
     */
    private static function prepareAndExecute($sql, array $params = [], $connection = null)
    {
        try {
            $stmt = self::getInstance($connection)->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            // 记录错误日志
            Logger::error('SQL执行错误', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            
            // 抛出自定义异常
            throw new \Exception('SQL执行错误: ' . $e->getMessage());
        }
    }
} 