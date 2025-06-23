<?php
/**
 * AlingAi Pro 权限验证增强服务
 * 提供完整的权限管理和验证功能
 */

namespace AlingAi\Security;

use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use Psr\Log\LoggerInterface;

class PermissionManager
{
    private DatabaseServiceInterface $db;
    private CacheService $cache;
    private LoggerInterface $logger;
    private array $permissionCache = [];
    
    // 权限级别定义
    const LEVEL_GUEST = 0;
    const LEVEL_USER = 1;
    const LEVEL_MODERATOR = 2;
    const LEVEL_ADMIN = 3;
    const LEVEL_SUPER_ADMIN = 4;
    
    // 权限模块定义
    const MODULE_USER_MANAGEMENT = 'user_management';
    const MODULE_SYSTEM_MONITOR = 'system_monitor';
    const MODULE_BACKUP_MANAGE = 'backup_manage';
    const MODULE_SECURITY_SCAN = 'security_scan';
    const MODULE_PERFORMANCE_TEST = 'performance_test';
    const MODULE_SYSTEM_CONFIG = 'system_config';
    
    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->initializePermissions();
    }
    
    /**
     * 初始化权限系统
     */
    private function initializePermissions(): void
    {
        $this->createPermissionTables();
        $this->seedDefaultPermissions();
    }
    
    /**
     * 创建权限表
     */
    private function createPermissionTables(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS user_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            module VARCHAR(50) NOT NULL,
            permission_level INT DEFAULT 0,
            granted_by INT,
            granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            UNIQUE KEY unique_user_module (user_id, module)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        try {
            $this->db->execute($sql);
            $this->logger->info("权限表创建成功");
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'already exists') === false) {
                $this->logger->error("权限表创建失败: " . $e->getMessage());
            }
        }
    }
    
    /**
     * 初始化默认权限
     */
    private function seedDefaultPermissions(): void
    {
        $defaultPermissions = [
            [
                'user_id' => 1, // 假设第一个用户是超级管理员
                'module' => self::MODULE_USER_MANAGEMENT,
                'permission_level' => self::LEVEL_SUPER_ADMIN
            ],
            [
                'user_id' => 1,
                'module' => self::MODULE_SYSTEM_MONITOR,
                'permission_level' => self::LEVEL_SUPER_ADMIN
            ],
            [
                'user_id' => 1,
                'module' => self::MODULE_BACKUP_MANAGE,
                'permission_level' => self::LEVEL_SUPER_ADMIN
            ],
            [
                'user_id' => 1,
                'module' => self::MODULE_SECURITY_SCAN,
                'permission_level' => self::LEVEL_SUPER_ADMIN
            ],
            [
                'user_id' => 1,
                'module' => self::MODULE_PERFORMANCE_TEST,
                'permission_level' => self::LEVEL_SUPER_ADMIN
            ],
            [
                'user_id' => 1,
                'module' => self::MODULE_SYSTEM_CONFIG,
                'permission_level' => self::LEVEL_SUPER_ADMIN
            ]
        ];
        
        foreach ($defaultPermissions as $permission) {
            $this->grantPermission(
                $permission['user_id'],
                $permission['module'],
                $permission['permission_level']
            );
        }
    }
    
    /**
     * 授予权限
     */
    public function grantPermission(int $userId, string $module, int $level, int $grantedBy = null): bool
    {
        try {
            // 检查是否已存在权限记录
            $existing = $this->db->query(
                "SELECT id FROM user_permissions WHERE user_id = ? AND module = ?",
                [$userId, $module]
            );
            
            if ($existing) {
                // 更新现有权限
                $result = $this->db->update('user_permissions', [
                    'permission_level' => $level,
                    'granted_by' => $grantedBy,
                    'granted_at' => date('Y-m-d H:i:s'),
                    'is_active' => 1
                ], [
                    'user_id' => $userId,
                    'module' => $module
                ]);
            } else {
                // 创建新权限记录
                $result = $this->db->insert('user_permissions', [
                    'user_id' => $userId,
                    'module' => $module,
                    'permission_level' => $level,
                    'granted_by' => $grantedBy,
                    'granted_at' => date('Y-m-d H:i:s'),
                    'is_active' => 1
                ]);
            }
            
            if ($result) {
                // 清除用户权限缓存
                $cacheKey = "user_permissions_{$userId}";
                $this->cache->delete($cacheKey);
                $this->logger->info("权限授予成功", [
                    'user_id' => $userId,
                    'module' => $module,
                    'level' => $level
                ]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error("权限授予失败: " . $e->getMessage(), [
                'user_id' => $userId,
                'module' => $module,
                'level' => $level
            ]);
            return false;
        }
    }
    
    /**
     * 撤销权限
     */
    public function revokePermission(int $userId, string $module): bool
    {
        try {
            $result = $this->db->update('user_permissions', [
                'is_active' => 0
            ], [
                'user_id' => $userId,
                'module' => $module
            ]);
            
            if ($result) {
                // 清除缓存
                $cacheKey = "user_permissions_{$userId}";
                $this->cache->delete($cacheKey);
                $this->logger->info("权限撤销成功", [
                    'user_id' => $userId,
                    'module' => $module
                ]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error("权限撤销失败: " . $e->getMessage(), [
                'user_id' => $userId,
                'module' => $module
            ]);
            return false;
        }
    }
    
    /**
     * 检查用户权限
     */
    public function hasPermission(int $userId, string $module, int $requiredLevel = self::LEVEL_USER): bool
    {
        try {
            // 检查缓存
            $cacheKey = "user_permissions_{$userId}";
            $permissions = $this->cache->get($cacheKey);
            
            if (!$permissions) {
                // 从数据库获取权限
                $permissions = $this->db->query(
                    "SELECT module, permission_level FROM user_permissions WHERE user_id = ? AND is_active = 1",
                    [$userId]
                ) ?: [];
                
                // 缓存结果
                $this->cache->set($cacheKey, $permissions, 3600); // 缓存1小时
            }
            
            foreach ($permissions as $permission) {
                if ($permission['module'] === $module) {
                    return (int)$permission['permission_level'] >= $requiredLevel;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error("权限检查失败: " . $e->getMessage(), [
                'user_id' => $userId,
                'module' => $module,
                'required_level' => $requiredLevel
            ]);
            return false;
        }
    }
    
    /**
     * 获取用户所有权限
     */
    public function getUserPermissions(int $userId): array
    {
        try {
            $cacheKey = "user_permissions_{$userId}";
            $permissions = $this->cache->get($cacheKey);
            
            if (!$permissions) {
                $permissions = $this->db->query(
                    "SELECT * FROM user_permissions WHERE user_id = ? AND is_active = 1 ORDER BY module",
                    [$userId]
                ) ?: [];
                
                $this->cache->set($cacheKey, $permissions, 3600);
            }
            
            return $permissions;
        } catch (\Exception $e) {
            $this->logger->error("获取用户权限失败: " . $e->getMessage(), [
                'user_id' => $userId
            ]);
            return [];
        }
    }
    
    /**
     * 权限中间件验证
     */
    public function validatePermission(int $userId, string $module, int $requiredLevel): array
    {
        $hasPermission = $this->hasPermission($userId, $module, $requiredLevel);
        
        if (!$hasPermission) {
            return [
                'success' => false,
                'error' => 'Insufficient permissions',
                'data' => [
                    'user_id' => $userId,
                    'module' => $module,
                    'required_level' => $requiredLevel,
                    'user_level' => $this->getUserPermissionLevel($userId, $module)
                ]
            ];
        }
        
        return [
            'success' => true,
            'message' => '权限验证通过',
            'data' => [
                'user_id' => $userId,
                'module' => $module,
                'required_level' => $requiredLevel
            ]
        ];
    }
    
    /**
     * 获取用户在特定模块的权限级别
     */
    public function getUserPermissionLevel(int $userId, string $module): int
    {
        $permissions = $this->getUserPermissions($userId);
        
        foreach ($permissions as $permission) {
            if ($permission['module'] === $module) {
                return (int)$permission['permission_level'];
            }
        }
        
        return self::LEVEL_GUEST;
    }
    
    /**
     * 验证权限令牌
     */
    public function validateToken(string $token): array
    {
        // 这里应该实现JWT或其他令牌验证逻辑
        // 为简化，返回基本验证结果
        if (empty($token)) {
            return [
                'valid' => false,
                'user_id' => null,
                'error' => 'Token is required'
            ];
        }
        
        // 简化的令牌验证（实际应该使用JWT等）
        if ($token === 'admin_token') {
            return [
                'valid' => true,
                'user_id' => 1,
                'permissions' => $this->getUserPermissions(1)
            ];
        }
        
        return [
            'valid' => false,
            'user_id' => null,
            'error' => 'Invalid token'
        ];
    }
    
    /**
     * 获取权限级别名称
     */
    public function getLevelName(int $level): string
    {
        $levels = [
            self::LEVEL_GUEST => 'Guest',
            self::LEVEL_USER => 'User',
            self::LEVEL_MODERATOR => 'Moderator',
            self::LEVEL_ADMIN => 'Admin',
            self::LEVEL_SUPER_ADMIN => 'Super Admin'
        ];
        
        return $levels[$level] ?? 'Unknown';
    }
    
    /**
     * 获取模块名称
     */
    public function getModuleName(string $module): string
    {
        $modules = [
            self::MODULE_USER_MANAGEMENT => '用户管理',
            self::MODULE_SYSTEM_MONITOR => '系统监控',
            self::MODULE_BACKUP_MANAGE => '备份管理',
            self::MODULE_SECURITY_SCAN => '安全扫描',
            self::MODULE_PERFORMANCE_TEST => '性能测试',
            self::MODULE_SYSTEM_CONFIG => '系统配置'
        ];
        
        return $modules[$module] ?? $module;
    }
    
    /**
     * 获取权限统计
     */
    public function getPermissionStats(): array
    {
        try {
            $totalUsers = $this->db->query("SELECT COUNT(DISTINCT user_id) as count FROM user_permissions")[0]['count'] ?? 0;
            $totalPermissions = $this->db->query("SELECT COUNT(*) as count FROM user_permissions WHERE is_active = 1")[0]['count'] ?? 0;
            $activeCount = $this->db->query("SELECT COUNT(*) as count FROM user_permissions WHERE is_active = 1")[0]['count'] ?? 0;
            
            return [
                'success' => true,
                'data' => [
                    'total_users_with_permissions' => $totalUsers,
                    'total_permissions' => $totalPermissions,
                    'active_permissions' => $activeCount,
                    'permission_modules' => [
                        self::MODULE_USER_MANAGEMENT,
                        self::MODULE_SYSTEM_MONITOR,
                        self::MODULE_BACKUP_MANAGE,
                        self::MODULE_SECURITY_SCAN,
                        self::MODULE_PERFORMANCE_TEST,
                        self::MODULE_SYSTEM_CONFIG
                    ]
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error("获取权限统计失败: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 清理过期权限
     */
    public function cleanupExpiredPermissions(): int
    {
        try {
            $result = $this->db->execute(
                "UPDATE user_permissions SET is_active = 0 WHERE expires_at IS NOT NULL AND expires_at < NOW()"
            );
            
            $this->logger->info("清理过期权限完成", ['affected_rows' => $result]);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("清理过期权限失败: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * 获取所有权限列表
     */
    public function getAllPermissions(): array
    {
        $cacheKey = 'permissions_all_list';
        
        // 尝试从缓存获取
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
          try {
            $permissions = $this->db->query("
                SELECT 
                    up.*,
                    u.username,
                    u.email
                FROM user_permissions up
                LEFT JOIN users u ON up.user_id = u.id
                WHERE up.is_active = 1
                ORDER BY up.user_id, up.module
            ");
              // 格式化权限数据
            $formattedPermissions = [];
            if (is_array($permissions)) {
                foreach ($permissions as $perm) {
                    $formattedPermissions[] = [
                        'id' => $perm['id'],
                        'user_id' => $perm['user_id'],
                        'username' => $perm['username'] ?? 'Unknown',
                        'email' => $perm['email'] ?? '',
                        'module' => $perm['module'],
                        'module_name' => $this->getModuleName($perm['module']),
                        'permission_level' => $perm['permission_level'],
                        'level_name' => $this->getLevelName($perm['permission_level']),
                        'granted_at' => $perm['granted_at'],
                        'granted_by' => $perm['granted_by'],
                        'expires_at' => $perm['expires_at']
                    ];
                }
            }
            
            // 缓存结果
            $this->cache->set($cacheKey, $formattedPermissions, 300);
            
            return $formattedPermissions;
            
        } catch (\Exception $e) {
            $this->logger->error("获取所有权限失败: " . $e->getMessage());
            return [];
        }
    }
}
