<?php
/**
 * 用户模型类
 * 
 * 处理用户数据的查询、验证和操作
 */

namespace App\Models;

use PDO;
use PDOException;
use App\Core\Database;

class User
{
    /**
     * 数据库连接
     * 
     * @var PDO
     */
    private $db;
    
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
     * 获取所有用户
     * 
     * @param int $limit 限制条数
     * @param int $offset 偏移量
     * @param string $orderBy 排序字段
     * @param string $direction 排序方向
     * @return array 用户列表
     */
    public function getAll($limit = 20, $offset = 0, $orderBy = 'id', $direction = 'ASC')
    {
        $sql = "SELECT id, username, email, display_name, role, status, created_at, updated_at 
                FROM users 
                ORDER BY {$orderBy} {$direction} 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取用户总数
     * 
     * @return int 用户总数
     */
    public function count()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * 按ID获取用户
     * 
     * @param int $id 用户ID
     * @return array|null 用户信息
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * 按邮箱获取用户
     * 
     * @param string $email 用户邮箱
     * @return array|null 用户信息
     */
    public function getByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * 按用户名获取用户
     * 
     * @param string $username 用户名
     * @return array|null 用户信息
     */
    public function getByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * 创建用户
     * 
     * @param array $userData 用户数据
     * @return int|false 成功返回用户ID，失败返回false
     */
    public function create($userData)
    {
        $requiredFields = ['username', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                return false;
            }
        }
        
        // 检查用户名和邮箱是否已存在
        if ($this->getByUsername($userData['username']) || $this->getByEmail($userData['email'])) {
            return false;
        }
        
        $sql = "INSERT INTO users (username, email, password, display_name, role, status, created_at, updated_at) 
                VALUES (:username, :email, :password, :display_name, :role, :status, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':username', $userData['username'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $userData['email'], PDO::PARAM_STR);
        $stmt->bindParam(':password', password_hash($userData['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
        $stmt->bindParam(':display_name', $userData['display_name'] ?? $userData['username'], PDO::PARAM_STR);
        $stmt->bindParam(':role', $userData['role'] ?? 'user', PDO::PARAM_STR);
        $stmt->bindParam(':status', $userData['status'] ?? 'active', PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * 更新用户
     * 
     * @param int $id 用户ID
     * @param array $userData 用户数据
     * @return bool 成功返回true，失败返回false
     */
    public function update($id, $userData)
    {
        $user = $this->getById($id);
        if (!$user) {
            return false;
        }
        
        $fields = [];
        $params = [':id' => $id];
        
        foreach ($userData as $key => $value) {
            // 排除不允许直接更新的字段
            if (in_array($key, ['id', 'created_at'])) {
                continue;
            }
            
            // 特殊处理密码字段
            if ($key === 'password' && !empty($value)) {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            
            $fields[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => &$value) {
            $stmt->bindParam($key, $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * 删除用户
     * 
     * @param int $id 用户ID
     * @return bool 成功返回true，失败返回false
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * 验证用户凭据
     * 
     * @param string $usernameOrEmail 用户名或邮箱
     * @param string $password 密码
     * @return array|false 成功返回用户信息，失败返回false
     */
    public function authenticate($usernameOrEmail, $password)
    {
        $sql = "SELECT * FROM users WHERE username = :identifier OR email = :identifier";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':identifier', $usernameOrEmail, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * 搜索用户
     * 
     * @param string $query 搜索关键词
     * @param int $limit 限制条数
     * @param int $offset 偏移量
     * @return array 用户列表
     */
    public function search($query, $limit = 20, $offset = 0)
    {
        $search = "%{$query}%";
        
        $sql = "SELECT id, username, email, display_name, role, status, created_at, updated_at 
                FROM users 
                WHERE username LIKE :search OR email LIKE :search OR display_name LIKE :search
                ORDER BY id DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 搜索结果总数
     * 
     * @param string $query 搜索关键词
     * @return int 结果总数
     */
    public function searchCount($query)
    {
        $search = "%{$query}%";
        
        $sql = "SELECT COUNT(*) FROM users 
                WHERE username LIKE :search OR email LIKE :search OR display_name LIKE :search";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
} 