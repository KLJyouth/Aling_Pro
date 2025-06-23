<?php

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Core\Database\DatabaseManager;
use PDO;

class UserService
{
    private $db;

    public function __construct(DatabaseManager $dbManager)
    {
        $this->db = $dbManager->getConnection();
    }

    /**
     * 通过邮箱查找用户
     *
     * @param string $email
     * @return array|false
     */
    public function findUserByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 通过ID查找用户
     *
     * @param int $id
     * @return array|false
     */
    public function findUserById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 获取用户的公开个人资料
     *
     * @param int $userId
     * @return array
     * @throws \RuntimeException
     */
    public function getProfile(int $userId): array
    {
        $user = $this->findUserById($userId);
        
        if (!$user) {
            throw new \RuntimeException('用户不存在');
        }

        // 出于安全考虑，绝不返回敏感信息
        unset($user['password'], $user['api_key'], $user['reset_token'], $user['reset_expires']);
        
        return $user;
    }

    /**
     * 创建一个新用户
     *
     * @param string $username
     * @param string $email
     * @param string $hashedPassword
     * @return string|false The ID of the new user or false on failure.
     */
    public function createUser(string $username, string $email, string $hashedPassword)
    {
        $verificationToken = bin2hex(random_bytes(32));
            
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, verification_token, status, role) 
            VALUES (?, ?, ?, ?, 'pending', 'user')
        ");
        
        $success = $stmt->execute([
            $username,
            $email,
            $hashedPassword,
            $verificationToken
        ]);

        if ($success) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * 更新用户个人资料
     *
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateProfile(int $userId, array $data): bool
    {
        // 定义允许前端更新的字段白名单
        $allowedFields = ['username', 'full_name', 'bio', 'timezone', 'avatar'];
        
        $updateData = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            // 没有提供任何可更新的字段
            return false;
        }

        // 检查用户名或邮箱是否已存在 (如果提供了这些字段)
        if (isset($updateData['username'])) {
            $existing = $this->findUserByUsername($updateData['username']);
            if ($existing && $existing['id'] !== $userId) {
                throw new \RuntimeException('用户名已被使用');
            }
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $setClauses = [];
        $params = [];
        foreach ($updateData as $key => $value) {
            $setClauses[] = "`$key` = ?";
            $params[] = $value;
        }
        $params[] = $userId;

        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * 按用户名查找用户 (用于更新时的唯一性检查)
     *
     * @param string $username
     * @return array|false
     */
    public function findUserByUsername(string $username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 更新用户最后登录时间
     *
     * @param int $userId
     * @return void
     */
    public function recordLogin(int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW(), login_count = login_count + 1 WHERE id = ?");
        $stmt->execute([$userId]);
    }
}
