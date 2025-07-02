<?php
/**
 * 日志模型类
 * 
 * 处理系统日志的存储和查询
 */

namespace App\Models;

use PDO;
use PDOException;
use App\Core\Database;

class Log
{
    /**
     * 数据库连接
     * 
     * @var PDO
     */
    private $db;
    
    /**
     * 日志级别
     */
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    
    /**
     * 构造函数
     * 
     * @param Database|null $database 数据库对象
     */
    public function __construct($database = null)
    {
        if ($database) {
            $this->db = $database->getConnection();
        } else {
            $this->db = Database::getInstance()->getConnection();
        }
    }
    
    /**
     * 添加日志
     * 
     * @param string $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @param int|null $userId 用户ID
     * @param string|null $ip IP地址
     * @return bool 成功返回true，失败返回false
     */
    public function add($level, $message, $context = [], $userId = null, $ip = null)
    {
        $sql = "INSERT INTO system_logs (level, message, context, user_id, ip_address, created_at) 
                VALUES (:level, :message, :context, :user_id, :ip, NOW())";
        
        $contextJson = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : null;
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':level', $level, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':context', $contextJson, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, $userId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    /**
     * 添加调试级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @param int|null $userId 用户ID
     * @param string|null $ip IP地址
     * @return bool 成功返回true，失败返回false
     */
    public function debug($message, $context = [], $userId = null, $ip = null)
    {
        return $this->add(self::LEVEL_DEBUG, $message, $context, $userId, $ip);
    }
    
    /**
     * 添加信息级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @param int|null $userId 用户ID
     * @param string|null $ip IP地址
     * @return bool 成功返回true，失败返回false
     */
    public function info($message, $context = [], $userId = null, $ip = null)
    {
        return $this->add(self::LEVEL_INFO, $message, $context, $userId, $ip);
    }
    
    /**
     * 添加警告级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @param int|null $userId 用户ID
     * @param string|null $ip IP地址
     * @return bool 成功返回true，失败返回false
     */
    public function warning($message, $context = [], $userId = null, $ip = null)
    {
        return $this->add(self::LEVEL_WARNING, $message, $context, $userId, $ip);
    }
    
    /**
     * 添加错误级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @param int|null $userId 用户ID
     * @param string|null $ip IP地址
     * @return bool 成功返回true，失败返回false
     */
    public function error($message, $context = [], $userId = null, $ip = null)
    {
        return $this->add(self::LEVEL_ERROR, $message, $context, $userId, $ip);
    }
    
    /**
     * 添加严重错误级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @param int|null $userId 用户ID
     * @param string|null $ip IP地址
     * @return bool 成功返回true，失败返回false
     */
    public function critical($message, $context = [], $userId = null, $ip = null)
    {
        return $this->add(self::LEVEL_CRITICAL, $message, $context, $userId, $ip);
    }
    
    /**
     * 获取日志列表
     * 
     * @param int $limit 限制条数
     * @param int $offset 偏移量
     * @param string|null $level 日志级别
     * @param string|null $startDate 开始日期
     * @param string|null $endDate 结束日期
     * @param int|null $userId 用户ID
     * @return array 日志列表
     */
    public function getList($limit = 100, $offset = 0, $level = null, $startDate = null, $endDate = null, $userId = null)
    {
        $sql = "SELECT l.*, u.username, u.display_name 
                FROM system_logs l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE 1=1 ";
        
        $params = [];
        
        if ($level) {
            $sql .= "AND l.level = :level ";
            $params[':level'] = $level;
        }
        
        if ($startDate) {
            $sql .= "AND l.created_at >= :start_date ";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $sql .= "AND l.created_at <= :end_date ";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }
        
        if ($userId !== null) {
            $sql .= "AND l.user_id = :user_id ";
            $params[':user_id'] = $userId;
        }
        
        $sql .= "ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 解析上下文JSON
        foreach ($logs as &$log) {
            if (!empty($log['context'])) {
                $log['context'] = json_decode($log['context'], true);
            } else {
                $log['context'] = [];
            }
        }
        
        return $logs;
    }
}
