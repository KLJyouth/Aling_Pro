<?php
/**
 * AlingAi Pro 5.0 - 高级Token管理系统
 * 提供JWT安全策略、Token生命周期管理、权限控�?
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AdvancedTokenManager
{
    private $secretKey;
    private $algorithm;
    private $issuer;
    private $audience;
    private $accessTokenTtl;
    private $refreshTokenTtl;
    private $logger;
    private $cacheService;
    private $databaseService;
    
    public function __construct() {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'alingai_pro_5.0_secret_key_2024';
        $this->algorithm = 'HS256';
        $this->issuer = 'AlingAi Pro 5.0';
        $this->audience = 'alingai-pro-users';
        $this->accessTokenTtl = 3600; // 1小时
        $this->refreshTokenTtl = 604800; // 7�?
        
        $this->logger = new \AlingAi\Utils\Logger('TokenManager'];
        $this->cacheService = new \AlingAi\Services\CacheService(];
        $this->databaseService = new \AlingAi\Services\DatabaseService(];
        
        $this->initializeTokenStorage(];
    }
    
    /**
     * 初始化Token存储
     */
    private function initializeTokenStorage() {
        // 创建Token存储�?
        $sql = "
            CREATE TABLE IF NOT EXISTS admin_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token_id VARCHAR(255) UNIQUE NOT NULL,
                token_type ENUM('access', 'refresh') NOT NULL,
                token_hash VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                is_revoked BOOLEAN DEFAULT FALSE,
                device_info TEXT,
                ip_address VARCHAR(45],
                user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_used_at DATETIME,
                INDEX idx_user_id (user_id],
                INDEX idx_token_id (token_id],
                INDEX idx_expires_at (expires_at)
            )";
        
        $this->databaseService->execute($sql];
    }
    
    /**
     * 生成访问Token
     */
    public function generateAccessToken(array $user, array $permissions = []): array
    {
        $tokenId = $this->generateTokenId(];
        $issuedAt = time(];
        $expiresAt = $issuedAt + $this->accessTokenTtl;
        
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'jti' => $tokenId,
            'sub' => $user['id'], 
            'user' => [
                'id' => $user['id'], 
                'username' => $user['username'] ?? '',
                'email' => $user['email'] ?? '',
                'role' => $user['role'] ?? 'user',
                'is_admin' => $user['is_admin'] ?? false
            ], 
            'permissions' => $permissions,
            'token_type' => 'access'
        ];
        
        $token = JWT::encode($payload, $this->secretKey, $this->algorithm];
        
        // 存储Token信息
        $this->storeToken([
            'user_id' => $user['id'], 
            'token_id' => $tokenId,
            'token_type' => 'access',
            'token_hash' => hash('sha256', $token],
            'expires_at' => date('Y-m-d H:i:s', $expiresAt],
            'device_info' => $this->getDeviceInfo(),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]];
        
        return [
            'token' => $token,
            'token_id' => $tokenId,
            'expires_at' => $expiresAt,
            'expires_in' => $this->accessTokenTtl
        ];
    }
    
    /**
     * 生成刷新Token
     */
    public function generateRefreshToken(int $userId): array
    {
        $tokenId = $this->generateTokenId(];
        $issuedAt = time(];
        $expiresAt = $issuedAt + $this->refreshTokenTtl;
        
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'jti' => $tokenId,
            'sub' => $userId,
            'token_type' => 'refresh'
        ];
        
        $token = JWT::encode($payload, $this->secretKey, $this->algorithm];
        
        // 存储Token信息
        $this->storeToken([
            'user_id' => $userId,
            'token_id' => $tokenId,
            'token_type' => 'refresh',
            'token_hash' => hash('sha256', $token],
            'expires_at' => date('Y-m-d H:i:s', $expiresAt],
            'device_info' => $this->getDeviceInfo(),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]];
        
        return [
            'token' => $token,
            'token_id' => $tokenId,
            'expires_at' => $expiresAt,
            'expires_in' => $this->refreshTokenTtl
        ];
    }
    
    /**
     * 验证Token
     */
    public function validateToken(string $token): ?array
    {
        try {
            // 解码JWT
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm)];
            $payload = (array) $decoded;
            
            // 检查Token是否被撤销
            if ($this->isTokenRevoked($payload['jti'])) {
                throw new Exception('Token has been revoked'];
            }
            
            // 更新最后使用时�?
            $this->updateLastUsed($payload['jti']];
            
            // 检查权�?
            $permissions = $this->getTokenPermissions($payload['jti']];
            $payload['permissions'] = $permissions;
            
            return $payload;
            
        } catch (ExpiredException $e) {
            $this->logger->warning('Token expired: ' . $e->getMessage()];
            return null;
        } catch (Exception $e) {
            $this->logger->error('Token validation failed: ' . $e->getMessage()];
            return null;
        }
    }
    
    /**
     * 刷新访问Token
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        $payload = $this->validateToken($refreshToken];
        
        if (!$payload || $payload['token_type'] !== 'refresh') {
            return null;
        }
        
        // 获取用户信息
        $user = $this->getUserById($payload['sub']];
        if (!$user) {
            return null;
        }
        
        // 获取用户权限
        $permissions = $this->getUserPermissions($user['id']];
        
        // 生成新的访问Token;
        return $this->generateAccessToken($user, $permissions];
    }
    
    /**
     * 撤销Token
     */
    public function revokeToken(string $tokenId): bool
    {
        $sql = "UPDATE admin_tokens SET is_revoked = TRUE WHERE token_id = ?";
        return $this->databaseService->execute($sql, [$tokenId]];
    }
    
    /**
     * 撤销用户的所有Token
     */
    public function revokeAllUserTokens(int $userId): bool
    {
        $sql = "UPDATE admin_tokens SET is_revoked = TRUE WHERE user_id = ?";
        return $this->databaseService->execute($sql, [$userId]];
    }
    
    /**
     * 获取用户的活跃Token
     */
    public function getUserActiveTokens(int $userId): array
    {
        $sql = "
        SELECT token_id, token_type, expires_at, device_info, ip_address, 
               created_at, last_used_at
        FROM admin_tokens 
        WHERE user_id = ? AND is_revoked = FALSE AND expires_at > NOW()
        ORDER BY created_at DESC
        ";
        
        return $this->databaseService->query($sql, [$userId]];
    }
    
    /**
     * 清理过期Token
     */
    public function cleanupExpiredTokens(): int
    {
        $sql = "DELETE FROM admin_tokens WHERE expires_at < NOW() OR is_revoked = TRUE";
        $this->databaseService->execute($sql];
        
        return $this->databaseService->getAffectedRows(];
    }
    
    /**
     * 获取Token统计信息
     */
    public function getTokenStatistics(): array
    {
        $stats = [];
        
        // 活跃Token�?
        $sql = "SELECT COUNT(*) as count FROM admin_tokens WHERE is_revoked = FALSE AND expires_at > NOW()";
        $result = $this->databaseService->query($sql];
        $stats['active_tokens'] = $result[0]['count'] ?? 0;
        
        // 今日生成Token�?
        $sql = "SELECT COUNT(*) as count FROM admin_tokens WHERE DATE(created_at) = CURDATE()";
        $result = $this->databaseService->query($sql];
        $stats['today_generated'] = $result[0]['count'] ?? 0;
        
        // 过期Token�?
        $sql = "SELECT COUNT(*) as count FROM admin_tokens WHERE expires_at < NOW()";
        $result = $this->databaseService->query($sql];
        $stats['expired_tokens'] = $result[0]['count'] ?? 0;
        
        // 被撤销Token�?
        $sql = "SELECT COUNT(*) as count FROM admin_tokens WHERE is_revoked = TRUE";
        $result = $this->databaseService->query($sql];
        $stats['revoked_tokens'] = $result[0]['count'] ?? 0;
        
        return $stats;
    }
    
    /**
     * 生成Token ID
     */
    private function generateTokenId(): string
    {
        return bin2hex(random_bytes(16)];
    }
    
    /**
     * 存储Token信息
     */
    private function storeToken(array $tokenData): bool
    {
        $sql = "
        INSERT INTO admin_tokens (
            user_id, token_id, token_type, token_hash, expires_at,
            device_info, ip_address, user_agent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        return $this->databaseService->execute($sql, [
            $tokenData['user_id'], 
            $tokenData['token_id'], 
            $tokenData['token_type'], 
            $tokenData['token_hash'], 
            $tokenData['expires_at'], 
            $tokenData['device_info'], 
            $tokenData['ip_address'], 
            $tokenData['user_agent']
        ]];
    }
    
    /**
     * 检查Token是否被撤销
     */
    private function isTokenRevoked(string $tokenId): bool
    {
        $sql = "SELECT is_revoked FROM admin_tokens WHERE token_id = ?";
        $result = $this->databaseService->query($sql, [$tokenId]];
        
        return !empty($result) && $result[0]['is_revoked'];
    }
    
    /**
     * 更新Token最后使用时�?
     */
    private function updateLastUsed(string $tokenId): void
    {
        $sql = "UPDATE admin_tokens SET last_used_at = NOW() WHERE token_id = ?";
        $this->databaseService->execute($sql, [$tokenId]];
    }
    
    /**
     * 获取Token权限
     */
    private function getTokenPermissions(string $tokenId): array
    {
        // 从缓存或数据库获取权�?
        $cacheKey = "token_permissions_{$tokenId}";
        $permissions = $this->cacheService->get($cacheKey];
        
        if ($permissions === null) {
            // 从数据库获取权限
            $sql = "
            SELECT p.permission_name 
            FROM admin_tokens t
            JOIN admin_users u ON t.user_id = u.id
            JOIN admin_user_permissions up ON u.id = up.user_id
            JOIN admin_permissions p ON up.permission_id = p.id
            WHERE t.token_id = ? AND t.is_revoked = FALSE
            ";
            
            $result = $this->databaseService->query($sql, [$tokenId]];
            $permissions = array_column($result, 'permission_name'];
            
            // 缓存权限信息
            $this->cacheService->set($cacheKey, $permissions, 300]; // 5分钟缓存
        }
        
        return $permissions ?: [];
    }
    
    /**
     * 根据ID获取用户
     */
    private function getUserById(int $userId): ?array
    {
        $sql = "SELECT * FROM admin_users WHERE id = ? AND is_active = TRUE";
        $result = $this->databaseService->query($sql, [$userId]];
        
        return $result[0] ?? null;
    }
    
    /**
     * 获取用户权限
     */
    private function getUserPermissions(int $userId): array
    {
        $sql = "
        SELECT p.permission_name 
        FROM admin_user_permissions up
        JOIN admin_permissions p ON up.permission_id = p.id
        WHERE up.user_id = ?
        ";
        
        $result = $this->databaseService->query($sql, [$userId]];
        return array_column($result, 'permission_name'];
    }
    
    /**
     * 获取设备信息
     */
    private function getDeviceInfo(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // 简单的设备检�?
        if (stripos($userAgent, 'mobile') !== false) {
            return 'mobile';
        } elseif (stripos($userAgent, 'tablet') !== false) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]];
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * 获取Token管理器实�?
     */
    public static function getInstance(): AdvancedTokenManager
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new AdvancedTokenManager(];
        }
        return $instance;
    }
}

/**
 * Token权限管理�?
 */
class TokenPermissionManager
{
    private $databaseService;
    private $cacheService;
    
    public function __construct() {
        $this->databaseService = new \AlingAi\Services\DatabaseService(];
        $this->cacheService = new \AlingAi\Services\CacheService(];
        
        $this->initializePermissionTables(];
    }
    
    /**
     * 初始化权限表
     */
    private function initializePermissionTables() {
        // 权限�?
        $sql = "
        CREATE TABLE IF NOT EXISTS admin_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            permission_name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            category VARCHAR(50],
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->databaseService->execute($sql];
        
        // 用户权限关联�?
        $sql = "
        CREATE TABLE IF NOT EXISTS admin_user_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            permission_id INTEGER NOT NULL,
            granted_by INTEGER,
            granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME,
            UNIQUE(user_id, permission_id],
            FOREIGN KEY (user_id) REFERENCES admin_users(id],
            FOREIGN KEY (permission_id) REFERENCES admin_permissions(id],
            FOREIGN KEY (granted_by) REFERENCES admin_users(id)
        )";
        $this->databaseService->execute($sql];
        
        // 初始化基础权限
        $this->initializeDefaultPermissions(];
    }
    
    /**
     * 初始化默认权�?
     */
    private function initializeDefaultPermissions() {
        $permissions = [
            ['admin.dashboard.view', '查看管理仪表�?, 'dashboard'], 
            ['admin.users.view', '查看用户列表', 'users'], 
            ['admin.users.create', '创建用户', 'users'], 
            ['admin.users.edit', '编辑用户', 'users'], 
            ['admin.users.delete', '删除用户', 'users'], 
            ['admin.system.view', '查看系统信息', 'system'], 
            ['admin.system.config', '系统配置', 'system'], 
            ['admin.logs.view', '查看系统日志', 'system'], 
            ['admin.api.manage', 'API管理', 'api'], 
            ['admin.third_party.manage', '第三方服务管�?, 'third_party'], 
            ['admin.monitoring.view', '监控查看', 'monitoring'], 
            ['admin.risk_control.manage', '风控管理', 'risk_control'], 
            ['admin.email.manage', '邮件管理', 'email'], 
            ['admin.chat.monitor', '聊天监控', 'chat']
        ];
        
        foreach ($permissions as $permission) {
            $sql = "INSERT OR IGNORE INTO admin_permissions (permission_name, description, category) VALUES (?, ?, ?)";
            $this->databaseService->execute($sql, $permission];
        }
    }
    
    /**
     * 检查权�?
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        $cacheKey = "user_permission_{$userId}_{$permission}";
        $hasPermission = $this->cacheService->get($cacheKey];
        
        if ($hasPermission === null) {
            $sql = "
            SELECT COUNT(*) as count
            FROM admin_user_permissions up
            JOIN admin_permissions p ON up.permission_id = p.id
            WHERE up.user_id = ? AND p.permission_name = ? 
            AND p.is_active = TRUE
            AND (up.expires_at IS NULL OR up.expires_at > NOW())
            ";
            
            $result = $this->databaseService->query($sql, [$userId, $permission]];
            $hasPermission = ($result[0]['count'] ?? 0) > 0;
            
            // 缓存结果
            $this->cacheService->set($cacheKey, $hasPermission, 300];
        }
        
        return (bool) $hasPermission;
    }
    
    /**
     * 授予权限
     */
    public function grantPermission(int $userId, string $permission, int $grantedBy, ?string $expiresAt = null): bool
    {
        // 获取权限ID
        $sql = "SELECT id FROM admin_permissions WHERE permission_name = ? AND is_active = TRUE";
        $result = $this->databaseService->query($sql, [$permission]];
        
        if (empty($result)) {
            return false;
        }
        
        $permissionId = $result[0]['id'];
        
        // 授予权限
        $sql = "
        INSERT OR REPLACE INTO admin_user_permissions 
        (user_id, permission_id, granted_by, expires_at) 
        VALUES (?, ?, ?, ?)
        ";
        
        $success = $this->databaseService->execute($sql, [$userId, $permissionId, $grantedBy, $expiresAt]];
        
        if ($success) {
            // 清除缓存
            $this->clearUserPermissionCache($userId];
        }
        
        return $success;
    }
    
    /**
     * 撤销权限
     */
    public function revokePermission(int $userId, string $permission): bool
    {
        $sql = "
        DELETE FROM admin_user_permissions 
        WHERE user_id = ? AND permission_id = (
            SELECT id FROM admin_permissions WHERE permission_name = ?
        )
        ";
        
        $success = $this->databaseService->execute($sql, [$userId, $permission]];
        
        if ($success) {
            // 清除缓存
            $this->clearUserPermissionCache($userId];
        }
        
        return $success;
    }
    
    /**
     * 清除用户权限缓存
     */
    private function clearUserPermissionCache(int $userId): void
    {
        // 简单的缓存清除策略
        $pattern = "user_permission_{$userId}_*";
        $this->cacheService->deletePattern($pattern];
    }
    
    /**
     * 获取权限管理器实�?
     */
    public static function getInstance(): TokenPermissionManager
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new TokenPermissionManager(];
        }
        return $instance;
    }
}

