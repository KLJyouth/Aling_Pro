<?php
/**
 * 数据库连接池
 */
class DatabaseConnectionPool 
{
    private static $pool = [];
    private static $maxConnections = 10;
    private static $activeConnections = 0;
    
    public static function getConnection() 
    {
        if (!empty(self::$pool)) {
            return array_pop(self::$pool];
        }
        
        if (self::$activeConnections < self::$maxConnections) {
            self::$activeConnections++;
            return self::createConnection(];
        }
        
        // 等待可用连接
        sleep(1];
        return self::getConnection(];
    }
    
    public static function releaseConnection($connection) 
    {
        if ($connection && count(self::$pool) < self::$maxConnections) {
            self::$pool[] = $connection;
        } else {
            self::$activeConnections--;
        }
    }
    
    private static function createConnection() 
    {
        $host = $_ENV["DB_HOST"] ?? "127.0.0.1";
        $db = $_ENV["DB_DATABASE"] ?? "alingai";
        $user = $_ENV["DB_USERNAME"] ?? "root";
        $pass = $_ENV["DB_PASSWORD"] ?? "";
        
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true
            ]];
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("数据库连接失�? " . $e->getMessage()];
        }
    }
}
