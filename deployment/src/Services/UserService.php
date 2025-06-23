<?php
/**
 * AlingAi Pro - 用户服务
 * 处理用户管理、权限、设置等功能
 * 
 * @package AlingAi\Pro\Services
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Utils\{Logger, PasswordHasher, FileUploader};
use AlingAi\Models\User;

class UserService
{    private DatabaseServiceInterface $db;
    private CacheService $cache;
    private PasswordHasher $hasher;
    private FileUploader $uploader;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        PasswordHasher $hasher,
        FileUploader $uploader
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->hasher = $hasher;
        $this->uploader = $uploader;
    }

    /**
     * 获取用户资料
     */
    public function getProfile(int $userId): array
    {
        $cacheKey = "user_profile:{$userId}";
        
        $profile = $this->cache->get($cacheKey);
        if ($profile) {
            return $profile;
        }

        $user = $this->db->selectOne('users', ['id' => $userId]);
        
        if (!$user) {
            throw new \RuntimeException('用户不存在');
        }

        // 移除敏感信息
        unset($user['password'], $user['api_key']);
        
        // 获取用户设置
        $settings = $this->getSettings($userId);
        $user['settings'] = $settings;
        
        // 获取用户统计
        $stats = $this->getUserStats($userId);
        $user['stats'] = $stats;

        $this->cache->set($cacheKey, $user, 300); // 缓存5分钟
        
        return $user;
    }

    /**
     * 更新用户资料
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $allowedFields = ['username', 'email', 'first_name', 'last_name', 'bio', 'timezone'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updateData)) {
            return false;
        }

        // 验证用户名和邮箱唯一性
        if (isset($updateData['username'])) {
            $existing = $this->db->selectOne('users', [
                'username' => $updateData['username'],
                'id' => ['!=', $userId]
            ]);
            
            if ($existing) {
                throw new \RuntimeException('用户名已被使用');
            }
        }

        if (isset($updateData['email'])) {
            $existing = $this->db->selectOne('users', [
                'email' => $updateData['email'],
                'id' => ['!=', $userId]
            ]);
            
            if ($existing) {
                throw new \RuntimeException('邮箱已被使用');
            }
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        $result = $this->db->update('users', $updateData, ['id' => $userId]);
        
        if ($result) {
            // 清除缓存
            $this->cache->delete("user_profile:{$userId}");
            $this->cache->delete("user:{$userId}");
            
            Logger::info('用户资料更新', [
                'user_id' => $userId,
                'fields' => array_keys($updateData)
            ]);
        }
        
        return $result;
    }

    /**
     * 获取用户设置
     */
    public function getSettings(int $userId): array
    {
        $settings = $this->db->selectOne('user_settings', ['user_id' => $userId]);
        
        if (!$settings) {
            // 创建默认设置
            $defaultSettings = $this->getDefaultSettings();
            $defaultSettings['user_id'] = $userId;
            $defaultSettings['created_at'] = date('Y-m-d H:i:s');
            
            $this->db->insert('user_settings', $defaultSettings);
            return $defaultSettings;
        }
        
        return $settings;
    }

    /**
     * 更新用户设置
     */
    public function updateSettings(int $userId, array $settings): bool
    {
        $allowedSettings = [
            'theme', 'language', 'timezone', 'notifications',
            'auto_save', 'privacy_level', 'ai_model_preference',
            'default_temperature', 'max_tokens_per_request'
        ];
        
        $updateData = array_intersect_key($settings, array_flip($allowedSettings));
        
        if (empty($updateData)) {
            return false;
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        $existing = $this->db->selectOne('user_settings', ['user_id' => $userId]);
        
        if ($existing) {
            $result = $this->db->update('user_settings', $updateData, ['user_id' => $userId]);
        } else {
            $updateData['user_id'] = $userId;
            $updateData['created_at'] = date('Y-m-d H:i:s');
            $result = $this->db->insert('user_settings', $updateData);
        }
        
        if ($result) {
            // 清除缓存
            $this->cache->delete("user_profile:{$userId}");
            
            Logger::info('用户设置更新', [
                'user_id' => $userId,
                'settings' => array_keys($updateData)
            ]);
        }
        
        return $result;
    }

    /**
     * 上传用户头像
     */
    public function uploadAvatar(int $userId, array $file): array
    {
        try {
            // 验证文件类型
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new \RuntimeException('不支持的文件类型');
            }

            // 验证文件大小 (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new \RuntimeException('文件大小超过限制');
            }            // 上传文件
            $uploadResult = $this->uploader->uploadFile($file, 'avatars');

            // 更新用户头像路径
            $this->db->update('users', [
                'avatar' => $uploadResult['path'],
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $userId]);

            // 清除缓存
            $this->cache->delete("user_profile:{$userId}");
            $this->cache->delete("user:{$userId}");

            Logger::info('用户头像上传', [
                'user_id' => $userId,
                'file_path' => $uploadResult['path']
            ]);

            return $uploadResult;

        } catch (\Exception $e) {
            Logger::error('头像上传失败', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 更改密码
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->db->selectOne('users', ['id' => $userId]);
        
        if (!$user) {
            throw new \RuntimeException('用户不存在');
        }

        // 验证当前密码
        if (!$this->hasher->verify($currentPassword, $user['password'])) {
            throw new \RuntimeException('当前密码不正确');
        }

        // 验证新密码强度
        if (!$this->validatePasswordStrength($newPassword)) {
            throw new \RuntimeException('新密码强度不足');
        }

        // 更新密码
        $hashedPassword = $this->hasher->hash($newPassword);
        
        $result = $this->db->update('users', [
            'password' => $hashedPassword,
            'password_changed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);

        if ($result) {
            Logger::info('用户密码更改', ['user_id' => $userId]);
        }

        return $result;
    }

    /**
     * 删除用户账户
     */
    public function deleteAccount(int $userId, string $password): bool
    {
        $user = $this->db->selectOne('users', ['id' => $userId]);
        
        if (!$user) {
            throw new \RuntimeException('用户不存在');
        }

        // 验证密码
        if (!$this->hasher->verify($password, $user['password'])) {
            throw new \RuntimeException('密码不正确');
        }

        try {
            $this->db->beginTransaction();

            // 删除相关数据
            $this->db->delete('user_settings', ['user_id' => $userId]);
            $this->db->delete('messages', ['user_id' => $userId]);
            $this->db->delete('conversations', ['user_id' => $userId]);
            $this->db->delete('usage_stats', ['user_id' => $userId]);
            $this->db->delete('user_logs', ['user_id' => $userId]);
            
            // 删除用户
            $this->db->delete('users', ['id' => $userId]);

            $this->db->commit();

            // 清除缓存
            $this->cache->delete("user_profile:{$userId}");
            $this->cache->delete("user:{$userId}");

            Logger::info('用户账户删除', ['user_id' => $userId]);

            return true;

        } catch (\Exception $e) {
            $this->db->rollback();
            Logger::error('删除账户失败', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 获取用户权限
     */
    public function getUserPermissions(int $userId): array
    {
        $user = $this->db->selectOne('users', ['id' => $userId]);
        
        if (!$user) {
            return [];
        }

        $permissions = [];
        
        switch ($user['role']) {
            case 'admin':
                $permissions = [
                    'users.view', 'users.create', 'users.update', 'users.delete',
                    'system.stats', 'system.logs', 'system.maintenance',
                    'chat.unlimited', 'export.data'
                ];
                break;
                
            case 'premium':
                $permissions = [
                    'chat.premium_models', 'chat.high_limits',
                    'export.personal', 'priority_support'
                ];
                break;
                
            case 'user':
            default:
                $permissions = [
                    'chat.basic', 'profile.update', 'settings.update'
                ];
                break;
        }

        return $permissions;
    }

    /**
     * 检查用户权限
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permission, $permissions);
    }

    /**
     * 获取用户统计
     */
    public function getUserStats(int $userId): array
    {
        $cacheKey = "user_stats:{$userId}";
        
        $stats = $this->cache->get($cacheKey);
        if ($stats) {
            return $stats;
        }

        $stats = [
            'conversations_count' => $this->db->count('conversations', ['user_id' => $userId]),
            'messages_count' => $this->db->count('messages', ['user_id' => $userId]),
            'tokens_used' => $this->getTotalTokensUsed($userId),
            'login_count' => $this->db->count('user_logs', [
                'user_id' => $userId,
                'action' => 'login'
            ]),
            'last_active' => $this->getLastActiveTime($userId),
            'member_since' => $this->db->selectOne('users', ['id' => $userId])['created_at'] ?? null
        ];

        $this->cache->set($cacheKey, $stats, 600); // 缓存10分钟
        
        return $stats;
    }

    /**
     * 获取总使用token数
     */
    private function getTotalTokensUsed(int $userId): int
    {
        $result = $this->db->query(
            'SELECT SUM(tokens) as total FROM usage_stats WHERE user_id = ?',
            [$userId]
        );
        
        return (int)($result[0]['total'] ?? 0);
    }    /**
     * 获取最后活跃时间
     */
    private function getLastActiveTime(int $userId): ?string
    {
        $logs = $this->db->select('user_logs', [
            'user_id' => $userId
        ], [
            'order' => 'created_at DESC',
            'limit' => 1
        ]);
        
        return !empty($logs) ? $logs[0]['created_at'] : null;
    }

    /**
     * 记录用户活动
     */
    public function logActivity(int $userId, string $action, array $details = []): void
    {
        $this->db->insert('user_logs', [
            'user_id' => $userId,
            'action' => $action,
            'details' => json_encode($details),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取默认设置
     */
    private function getDefaultSettings(): array
    {
        return [
            'theme' => 'quantum',
            'language' => 'zh_CN',
            'timezone' => 'Asia/Shanghai',
            'notifications' => true,
            'auto_save' => true,
            'privacy_level' => 2,
            'ai_model_preference' => 'deepseek-chat',
            'default_temperature' => 0.7,
            'max_tokens_per_request' => 1024
        ];
    }

    /**
     * 验证密码强度
     */
    private function validatePasswordStrength(string $password): bool
    {
        // 至少8位，包含大小写字母、数字
        return strlen($password) >= 8 &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }

    /**
     * 管理员统计方法
     */
    public function getTotalUsers(): int
    {
        return $this->db->count('users');
    }

    public function getActiveUsersToday(): int
    {
        return $this->db->count('user_logs', [
            'action' => 'login',
            'created_at' => ['>=', date('Y-m-d 00:00:00')]
        ]);
    }

    public function getNewUsersThisWeek(): int
    {
        return $this->db->count('users', [
            'created_at' => ['>=', date('Y-m-d 00:00:00', strtotime('-7 days'))]
        ]);
    }

    /**
     * 获取用户列表（管理员）
     */
    public function getUserList(int $page = 1, int $limit = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $conditions = [];
        
        if (!empty($filters['role'])) {
            $conditions['role'] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $conditions['status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $conditions['OR'] = [
                ['username', 'LIKE', "%{$search}%"],
                ['email', 'LIKE', "%{$search}%"],
                ['first_name', 'LIKE', "%{$search}%"],
                ['last_name', 'LIKE', "%{$search}%"]
            ];
        }

        $users = $this->db->select('users', $conditions, [
            'order' => ['created_at' => 'DESC'],
            'limit' => $limit,
            'offset' => $offset,
            'exclude' => ['password'] // 排除敏感字段
        ]);
        
        $total = $this->db->count('users', $conditions);
        
        return [
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * 更新用户状态（管理员）
     */
    public function updateUserStatus(int $userId, string $status): bool
    {
        $allowedStatuses = ['active', 'inactive', 'suspended', 'banned'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new \InvalidArgumentException('无效的用户状态');
        }

        $result = $this->db->update('users', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);

        if ($result) {
            // 清除缓存
            $this->cache->delete("user_profile:{$userId}");
            $this->cache->delete("user:{$userId}");
            
            Logger::info('用户状态更新', [
                'user_id' => $userId,
                'status' => $status
            ]);
        }

        return $result;
    }
}
