<?php

namespace AlingAi\Services;

/**
 * 文件用户存储服务
 * 用于开发环境的简单用户管理
 */
class FileUserService
{
    private string $storageDir;
    private string $userFile;
    
    public function __construct()
    {
        $this->storageDir = __DIR__ . '/../../storage/users';
        $this->userFile = $this->storageDir . '/users.json';
        
        // 确保目录存在
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
        
        // 初始化用户文件
        if (!file_exists($this->userFile)) {
            $this->initializeUsers();
        }
    }
    
    /**
     * 初始化默认用户
     */
    private function initializeUsers(): void
    {
        $defaultUsers = [
            [
                'id' => 1,
                'email' => 'test@example.com',
                'password' => password_hash('test123456', PASSWORD_DEFAULT),
                'username' => 'testuser',
                'name' => 'Test User',
                'role' => 'user',
                'status' => 'active',
                'permissions' => [],
                'avatar' => null,
                'phone' => null,
                'last_login_at' => null,
                'login_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null
            ],
            [
                'id' => 2,
                'email' => 'admin@example.com',
                'password' => password_hash('admin123456', PASSWORD_DEFAULT),
                'username' => 'admin',
                'name' => 'Administrator',
                'role' => 'admin',
                'status' => 'active',
                'permissions' => ['*'],
                'avatar' => null,
                'phone' => null,
                'last_login_at' => null,
                'login_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null
            ]
        ];
        
        file_put_contents($this->userFile, json_encode($defaultUsers, JSON_PRETTY_PRINT));
    }
    
    /**
     * 获取所有用户
     */
    public function getAllUsers(): array
    {
        $content = file_get_contents($this->userFile);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * 根据邮箱查找用户
     */
    public function findByEmail(string $email): ?array
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email && $user['deleted_at'] === null) {
                return $user;
            }
        }
        return null;
    }
    
    /**
     * 根据ID查找用户
     */
    public function findById(int $id): ?array
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['id'] === $id && $user['deleted_at'] === null) {
                return $user;
            }
        }
        return null;
    }
    
    /**
     * 更新用户
     */
    public function updateUser(int $id, array $data): bool
    {
        $users = $this->getAllUsers();
        $updated = false;
        
        foreach ($users as &$user) {
            if ($user['id'] === $id) {
                foreach ($data as $key => $value) {
                    if ($key !== 'id') {
                        $user[$key] = $value;
                    }
                }
                $user['updated_at'] = date('Y-m-d H:i:s');
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            file_put_contents($this->userFile, json_encode($users, JSON_PRETTY_PRINT));
        }
        
        return $updated;
    }
    
    /**
     * 创建新用户
     */
    public function createUser(array $userData): array
    {
        $users = $this->getAllUsers();
        
        // 生成新ID
        $maxId = 0;
        foreach ($users as $user) {
            if ($user['id'] > $maxId) {
                $maxId = $user['id'];
            }
        }
        
        $newUser = array_merge([
            'id' => $maxId + 1,
            'role' => 'user',
            'status' => 'active',
            'permissions' => [],
            'avatar' => null,
            'phone' => null,
            'last_login_at' => null,
            'login_count' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null
        ], $userData);
        
        $users[] = $newUser;
        file_put_contents($this->userFile, json_encode($users, JSON_PRETTY_PRINT));
        
        return $newUser;
    }
    
    /**
     * 验证用户密码
     */
    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }
    
    /**
     * 记录登录
     */
    public function recordLogin(int $userId): void
    {
        $this->updateUser($userId, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'login_count' => $this->findById($userId)['login_count'] + 1
        ]);
    }
}
