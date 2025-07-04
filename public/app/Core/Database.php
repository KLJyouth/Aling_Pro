<?php
/**
 * 数据库类
 * 
 * 负责数据库连接和操作
 * 
 * @package App\Core
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    /**
     * 数据库连接实例
     * 
     * @var PDO|null
     */
    private static $connection = null;
    
    /**
     * 数据库配置
     * 
     * @var array
     */
    private static $config = [];
    
    /**
     * 设置数据库配置
     * 
     * @param array $config 数据库配置
     * @return void
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;
    }
    
    /**
     * 获取数据库连接
     * 
     * @return PDO
     * @throws PDOException 如果连接失败
     */
    public static function getConnection()
    {
        if (self::$connection === null) {
            self::connect();
        }
        
        return self::$connection;
    }
    
    /**
     * 连接数据库
     * 
     * @return void
     * @throws PDOException 如果连接失败
     */
    private static function connect()
    {
        try {
            $type = self::$config["type"] ?? "mysql";
            
            if ($type === "sqlite") {
                $path = self::$config["path"] ?? "database.sqlite";
                self::$connection = new PDO("sqlite:{$path}");
            } else {
                $host = self::$config["host"] ?? "localhost";
                $port = self::$config["port"] ?? 3306;
                $database = self::$config["database"] ?? "";
                $username = self::$config["username"] ?? "root";
                $password = self::$config["password"] ?? "";
                $charset = self::$config["charset"] ?? "utf8mb4";
                
                $dsn = "{$type}:host={$host};port={$port};dbname={$database};charset={$charset}";
                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            }
        } catch (PDOException $e) {
            Logger::error("数据库连接失败: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 执行SQL查询
     * 
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return \PDOStatement
     */
    public static function query($sql, array $params = [])
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error("SQL查询失败: " . $e->getMessage(), [
                "sql" => $sql,
                "params" => $params
            ]);
            throw $e;
        }
    }
    
    /**
     * 获取单行数据
     * 
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return array|null 返回查询结果的第一行，如果没有结果则返回null
     */
    public static function fetchOne($sql, array $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * 获取多行数据
     * 
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return array 返回查询结果的所有行
     */
    public static function fetchAll($sql, array $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * 获取单个值
     * 
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return mixed|null 返回查询结果的第一行第一列的值，如果没有结果则返回null
     */
    public static function fetchValue($sql, array $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchColumn() ?: null;
    }
    
    /**
     * 执行插入、更新或删除操作
     * 
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return int 返回受影响的行数
     */
    public static function execute($sql, array $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * 获取最后插入的ID
     * 
     * @return string
     */
    public static function lastInsertId()
    {
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * 开始事务
     * 
     * @return bool
     */
    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * 提交事务
     * 
     * @return bool
     */
    public static function commit()
    {
        return self::getConnection()->commit();
    }
    
    /**
     * 回滚事务
     * 
     * @return bool
     */
    public static function rollBack()
    {
        return self::getConnection()->rollBack();
    }
}
