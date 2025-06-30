<?php
namespace App\Core;

/**
 * 数据库连接管理类
 * 单例模式，负责创建和管理数据库连接
 */
class Database
{
    /**
     * 数据库连接实例
     * @var \PDO|null
     */
    private static $instance = null;
    
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
     * @return \PDO 数据库连接
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::connect();
        }
        
        return self::$instance;
    }
    
    /**
     * 设置数据库配置
     * @param array $config 数据库配置
     * @return void
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;
    }
    
    /**
     * 创建数据库连接
     * @throws \Exception 连接失败时抛出异常
     * @return void
     */
    private static function connect()
    {
        try {
            // 检查配置是否完整
            if (empty(self::$config['host']) || 
                empty(self::$config['database']) || 
                !isset(self::$config['username'])) {
                throw new \Exception('数据库配置不完整');
            }
            
            // 构建DSN
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                self::$config['driver'] ?? 'mysql',
                self::$config['host'],
                self::$config['port'] ?? 3306,
                self::$config['database'],
                self::$config['charset'] ?? 'utf8mb4'
            );
            
            // 创建PDO实例
            $options = self::$config['options'] ?? [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            self::$instance = new \PDO(
                $dsn,
                self::$config['username'],
                self::$config['password'] ?? '',
                $options
            );
            
        } catch (\PDOException $e) {
            // 记录错误日志
            error_log('数据库连接失败: ' . $e->getMessage());
            
            // 抛出自定义异常
            throw new \Exception('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 执行查询并返回一条结果
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return array|false 查询结果或失败时返回false
     */
    public static function fetchOne($sql, array $params = [])
    {
        $stmt = self::prepareAndExecute($sql, $params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 执行查询并返回所有结果
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return array 查询结果数组
     */
    public static function fetchAll($sql, array $params = [])
    {
        $stmt = self::prepareAndExecute($sql, $params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 执行SQL语句
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return int 受影响的行数
     */
    public static function execute($sql, array $params = [])
    {
        $stmt = self::prepareAndExecute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * 获取最后插入的ID
     * @return string 最后插入的ID
     */
    public static function lastInsertId()
    {
        return self::getInstance()->lastInsertId();
    }
    
    /**
     * 开始事务
     * @return bool 是否成功
     */
    public static function beginTransaction()
    {
        return self::getInstance()->beginTransaction();
    }
    
    /**
     * 提交事务
     * @return bool 是否成功
     */
    public static function commit()
    {
        return self::getInstance()->commit();
    }
    
    /**
     * 回滚事务
     * @return bool 是否成功
     */
    public static function rollback()
    {
        return self::getInstance()->rollBack();
    }
    
    /**
     * 准备并执行SQL语句
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return \PDOStatement 执行结果
     */
    private static function prepareAndExecute($sql, array $params = [])
    {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            // 记录错误日志
            error_log('SQL执行错误: ' . $e->getMessage() . ' SQL: ' . $sql);
            
            // 抛出自定义异常
            throw new \Exception('SQL执行错误: ' . $e->getMessage());
        }
    }
} 