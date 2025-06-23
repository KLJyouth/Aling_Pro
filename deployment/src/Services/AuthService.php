<?php
/**
 * 认证服务类
 * 
 * @package AlingAi\Services
 */

declare(strict_types=1);

namespace AlingAi\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Monolog\Logger;
use AlingAi\Models\User;

class AuthService
{
    private DatabaseServiceInterface $db;
    private CacheService $cache;
    private Logger $logger;
    private array $jwtConfig;
    
    public function __construct(DatabaseServiceInterface $db, CacheService $cache, Logger $logger)
    {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->jwtConfig = $this->loadJwtConfig();
    }    private function loadJwtConfig(): array
    {
        $secret = $_ENV['JWT_SECRET'] ?? '';
        if (empty($secret)) {
            throw new \RuntimeException('JWT_SECRET must be set in environment variables');
        }
        
        return [
            'secret' => $secret,
            'algorithm' => 'HS256',
            'ttl' => (int) ($_ENV['JWT_TTL'] ?? 3600), // 1 hour
            'refresh_ttl' => (int) ($_ENV['JWT_REFRESH_TTL'] ?? 604800), // 1 week
            'leeway' => (int) ($_ENV['JWT_LEEWAY'] ?? 60), // 1 minute
            'issuer' => $_ENV['JWT_ISSUER'] ?? 'alingai-pro',
            'audience' => $_ENV['JWT_AUDIENCE'] ?? 'alingai-pro-users',
        ];
    }
    
    public function login(string $email, string $password): array
    {
        try {
            $user = $this->findUserByEmail($email);
            
            if (!$user) {
                $this->logger->warning('Login attempt with non-existent email', ['email' => $email]);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            if (!$this->verifyPassword($password, $user['password'])) {
                $this->logger->warning('Login attempt with wrong password', ['email' => $email]);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            if (!$user['is_active']) {
                $this->logger->warning('Login attempt with inactive account', ['email' => $email]);
                return ['success' => false, 'message' => 'Account is inactive'];
            }
            
            if ($user['email_verified_at'] === null && getenv('FEATURE_EMAIL_VERIFICATION') === 'true') {
                return ['success' => false, 'message' => 'Please verify your email address'];
            }
            
            // 生成JWT令牌
            $tokens = $this->generateTokens($user);
            
            // 更新最后登录时间
            $this->updateLastLogin($user['id']);
            
            // 记录登录日志
            $this->logUserAction($user['id'], 'login', 'User logged in successfully');
            
            $this->logger->info('User logged in successfully', ['user_id' => $user['id'], 'email' => $email]);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $this->sanitizeUser($user),
                'tokens' => $tokens
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Login error', ['email' => $email, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Login failed'];
        }
    }
    
    /**
     * 检查用户是否存在
     */    public function userExists(string $email, string $username = null): bool
    {
        if ($username) {
            // Use the query method for complex WHERE conditions
            $result = $this->db->query(
                'SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1',
                ['email' => $email, 'username' => $username]
            );
            return !empty($result);
        } else {
            $user = $this->db->selectOne('users', ['email' => $email]);
        }
        
        return $user !== null;
    }
    
    /**
     * 注册新用户
     */
    public function register(array $userData): array
    {
        try {
            // 检查用户是否已存在
            if ($this->userExists($userData['email'], $userData['username'] ?? null)) {
                throw new \Exception('用户已存在');
            }
            
            // 加密密码
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // 设置默认值
            $userData['status'] = $userData['status'] ?? 'pending';
            $userData['role'] = $userData['role'] ?? 'user';
            $userData['created_at'] = date('Y-m-d H:i:s');
            $userData['updated_at'] = date('Y-m-d H:i:s');
            
            // 生成邮箱验证令牌
            $userData['email_verification_token'] = bin2hex(random_bytes(32));
            
            // 插入用户
            $userId = $this->db->insert('users', $userData);
              // 获取创建的用户
            $user = $this->db->selectOne('users', ['id' => $userId]);
            
            // 移除敏感信息
            unset($user['password']);
            
            return $user;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * 撤销令牌（登出）
     */
    public function revokeToken(string $token): bool
    {
        try {
            // 将令牌加入黑名单（使用缓存）
            $cacheKey = "blacklisted_token:" . md5($token);
            $this->cache->set($cacheKey, true, 3600); // 1小时过期
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function registerOld(array $userData): array
    {
        try {
            // 验证必填字段
            $required = ['email', 'password', 'name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => "Field {$field} is required"];
                }
            }
            
            // 检查邮箱是否已存在
            if ($this->findUserByEmail($userData['email'])) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // 验证密码强度
            if (!$this->validatePassword($userData['password'])) {
                return ['success' => false, 'message' => 'Password does not meet requirements'];
            }
            
            // 创建用户
            $userId = $this->createUser([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $this->hashPassword($userData['password']),
                'role' => 'user',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$userId) {
                return ['success' => false, 'message' => 'Failed to create user'];
            }
            
            // 发送验证邮件
            if (getenv('FEATURE_EMAIL_VERIFICATION') === 'true') {
                $this->sendVerificationEmail($userId, $userData['email']);
            }
            
            // 记录注册日志
            $this->logUserAction($userId, 'register', 'User registered successfully');
            
            $this->logger->info('User registered successfully', ['user_id' => $userId, 'email' => $userData['email']]);
            
            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $userId
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Registration error', ['email' => $userData['email'] ?? '', 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
      public function verifyToken(string $token): ?array
    {
        try {
            JWT::$leeway = $this->jwtConfig['leeway'];
            
            $decoded = JWT::decode($token, new Key($this->jwtConfig['secret'], $this->jwtConfig['algorithm']));
            $payload = (array) $decoded;
            
            // 验证令牌是否在黑名单中
            if ($this->isTokenBlacklisted($token)) {
                return null;
            }
            
            // 获取用户信息
            $user = $this->findUserById($payload['user_id']);
            if (!$user || !$user['is_active']) {
                return null;
            }
            
            return [
                'user_id' => $payload['user_id'],
                'email' => $payload['email'],
                'role' => $payload['role'],
                'exp' => $payload['exp'],
                'user' => $this->sanitizeUser($user)
            ];
            
        } catch (ExpiredException $e) {
            $this->logger->info('Token expired', ['token' => substr($token, 0, 20) . '...']);
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Token verification failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * 验证令牌（validateToken方法，verifyToken的别名）
     * 
     * @param string $token JWT令牌
     * @return array|null 用户信息或null
     */
    public function validateToken(string $token): ?array
    {
        return $this->verifyToken($token);
    }
    
    public function refreshToken(string $refreshToken): ?array
    {
        try {
            // 验证刷新令牌
            $payload = $this->verifyRefreshToken($refreshToken);
            if (!$payload) {
                return null;
            }
            
            // 获取用户信息
            $user = $this->findUserById($payload['user_id']);
            if (!$user || !$user['is_active']) {
                return null;
            }
            
            // 生成新的令牌对
            $tokens = $this->generateTokens($user);
            
            // 将旧的刷新令牌加入黑名单
            $this->blacklistToken($refreshToken);
            
            $this->logger->info('Token refreshed successfully', ['user_id' => $user['id']]);
            
            return $tokens;
            
        } catch (\Exception $e) {
            $this->logger->error('Token refresh failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    public function logout(string $token): bool
    {
        try {
            // 将令牌加入黑名单
            $this->blacklistToken($token);
            
            // 获取用户信息并记录登出日志
            $payload = $this->verifyToken($token);
            if ($payload) {
                $this->logUserAction($payload['user_id'], 'logout', 'User logged out successfully');
                $this->logger->info('User logged out successfully', ['user_id' => $payload['user_id']]);
            }
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Logout failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function forgotPassword(string $email): array
    {
        try {
            $user = $this->findUserByEmail($email);
            if (!$user) {
                // 为了安全，即使用户不存在也返回成功消息
                return ['success' => true, 'message' => 'Password reset email sent'];
            }
            
            // 生成重置令牌
            $resetToken = $this->generateResetToken();
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1小时后过期
            
            // 保存重置令牌
            $this->savePasswordResetToken($user['id'], $resetToken, $expiresAt);
            
            // 发送重置邮件
            $this->sendPasswordResetEmail($user['email'], $resetToken);
            
            $this->logger->info('Password reset requested', ['user_id' => $user['id'], 'email' => $email]);
            
            return ['success' => true, 'message' => 'Password reset email sent'];
            
        } catch (\Exception $e) {
            $this->logger->error('Password reset request failed', ['email' => $email, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to send reset email'];
        }
    }
    
    public function resetPassword(string $token, string $newPassword): array
    {
        try {
            // 验证重置令牌
            $resetData = $this->findPasswordResetToken($token);
            if (!$resetData || strtotime($resetData['expires_at']) < time()) {
                return ['success' => false, 'message' => 'Invalid or expired reset token'];
            }
            
            // 验证新密码
            if (!$this->validatePassword($newPassword)) {
                return ['success' => false, 'message' => 'Password does not meet requirements'];
            }
            
            // 更新密码
            $hashedPassword = $this->hashPassword($newPassword);
            $this->updateUserPassword($resetData['user_id'], $hashedPassword);
            
            // 删除重置令牌
            $this->deletePasswordResetToken($token);
            
            // 记录密码重置日志
            $this->logUserAction($resetData['user_id'], 'password_reset', 'Password reset successfully');
            
            $this->logger->info('Password reset successfully', ['user_id' => $resetData['user_id']]);
            
            return ['success' => true, 'message' => 'Password reset successfully'];
            
        } catch (\Exception $e) {
            $this->logger->error('Password reset failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Password reset failed'];
        }
    }
    
    private function generateTokens(array $user): array
    {
        $now = time();
        
        // 访问令牌
        $accessPayload = [
            'iss' => $this->jwtConfig['issuer'],
            'aud' => $this->jwtConfig['audience'],
            'iat' => $now,
            'exp' => $now + $this->jwtConfig['ttl'],
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'type' => 'access'
        ];
        
        // 刷新令牌
        $refreshPayload = [
            'iss' => $this->jwtConfig['issuer'],
            'aud' => $this->jwtConfig['audience'],
            'iat' => $now,
            'exp' => $now + $this->jwtConfig['refresh_ttl'],
            'user_id' => $user['id'],
            'type' => 'refresh'
        ];
        
        return [
            'access_token' => JWT::encode($accessPayload, $this->jwtConfig['secret'], $this->jwtConfig['algorithm']),
            'refresh_token' => JWT::encode($refreshPayload, $this->jwtConfig['secret'], $this->jwtConfig['algorithm']),
            'token_type' => 'Bearer',
            'expires_in' => $this->jwtConfig['ttl']
        ];
    }
    
    private function verifyRefreshToken(string $token): ?array
    {
        try {
            JWT::$leeway = $this->jwtConfig['leeway'];
            $decoded = JWT::decode($token, new Key($this->jwtConfig['secret'], $this->jwtConfig['algorithm']));
            $payload = (array) $decoded;
            
            if ($payload['type'] !== 'refresh') {
                return null;
            }
            
            if ($this->isTokenBlacklisted($token)) {
                return null;
            }
            
            return $payload;
            
        } catch (\Exception $e) {
            $this->logger->error('Refresh token verification failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    private function findUserByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $result = $this->db->query($sql, ['email' => $email]);
        return $result[0] ?? null;
    }
    
    private function findUserById(int $userId): ?array
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $result = $this->db->query($sql, ['id' => $userId]);
        return $result[0] ?? null;
    }
    
    private function createUser(array $userData): ?int
    {
        $sql = "INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) 
                VALUES (:name, :email, :password, :role, :is_active, :created_at, :updated_at)";
        
        if ($this->db->execute($sql, $userData)) {
            return (int) $this->db->lastInsertId();
        }
        
        return null;
    }
    
    private function updateLastLogin(int $userId): void
    {
        $sql = "UPDATE users SET last_login_at = :last_login_at WHERE id = :id";
        $this->db->execute($sql, [
            'id' => $userId,
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function updateUserPassword(int $userId, string $hashedPassword): void
    {
        $sql = "UPDATE users SET password = :password, updated_at = :updated_at WHERE id = :id";
        $this->db->execute($sql, [
            'id' => $userId,
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    private function validatePassword(string $password): bool
    {
        // 密码至少8位，包含大小写字母和数字
        return strlen($password) >= 8 && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
    
    private function sanitizeUser(array $user): array
    {
        unset($user['password']);
        return $user;
    }
    
    private function isTokenBlacklisted(string $token): bool
    {
        return $this->cache->has("blacklist:token:" . hash('sha256', $token));
    }
    
    private function blacklistToken(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        $this->cache->set("blacklist:token:" . $tokenHash, true, $this->jwtConfig['ttl']);
    }
    
    private function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    private function savePasswordResetToken(int $userId, string $token, string $expiresAt): void
    {
        $sql = "INSERT INTO password_resets (user_id, token, expires_at, created_at) 
                VALUES (:user_id, :token, :expires_at, :created_at)
                ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires_at, created_at = :created_at";
        
        $this->db->execute($sql, [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function findPasswordResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM password_resets WHERE token = :token LIMIT 1";
        $result = $this->db->query($sql, ['token' => $token]);
        return $result[0] ?? null;
    }
    
    private function deletePasswordResetToken(string $token): void
    {
        $sql = "DELETE FROM password_resets WHERE token = :token";
        $this->db->execute($sql, ['token' => $token]);
    }
    
    private function logUserAction(int $userId, string $action, string $description): void
    {
        $sql = "INSERT INTO user_logs (user_id, action, description, ip_address, user_agent, created_at) 
                VALUES (:user_id, :action, :description, :ip_address, :user_agent, :created_at)";
        
        $this->db->execute($sql, [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function sendVerificationEmail(int $userId, string $email): void
    {
        // 这里应该实现邮件发送逻辑
        // 暂时记录日志
        $this->logger->info('Verification email should be sent', ['user_id' => $userId, 'email' => $email]);
    }
      private function sendPasswordResetEmail(string $email, string $token): void
    {
        // 这里应该实现邮件发送逻辑
        // 暂时记录日志
        $this->logger->info('Password reset email should be sent', ['email' => $email, 'token' => $token]);
    }
    
    /**
     * 验证邮箱
     */    public function verifyEmail(string $token): array
    {
        try {
            // 查找用户通过验证令牌
            $result = $this->db->query(
                'SELECT * FROM users WHERE email_verification_token = :token AND email_verified_at IS NULL LIMIT 1',
                ['token' => $token]
            );
            
            if (empty($result)) {
                return ['success' => false, 'message' => 'Invalid or expired verification token'];
            }
            
            $user = $result[0];
            
            // 更新用户邮箱验证状态
            $this->db->update('users', [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'email_verification_token' => null,
                'status' => 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $user['id']]);
            
            // 记录验证日志
            $this->logUserAction($user['id'], 'email_verified', 'Email verified successfully');
            
            $this->logger->info('Email verified successfully', ['user_id' => $user['id'], 'email' => $user['email']]);
            
            // 返回用户信息（移除敏感数据）
            $verifiedUser = $this->sanitizeUser($user);
            $verifiedUser['status'] = 'active';
            $verifiedUser['email_verified_at'] = date('Y-m-d H:i:s');
            
            return ['success' => true, 'user' => $verifiedUser];
            
        } catch (\Exception $e) {
            $this->logger->error('Email verification failed', ['token' => substr($token, 0, 10) . '...', 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Email verification failed'];
        }
    }
    
    /**
     * 获取用户详情
     */    public function getUserDetails(int $userId): ?array
    {
        $user = $this->db->selectOne('users', ['id' => $userId]);
        
        if ($user) {
            unset($user['password']);
        }
        
        return $user;
    }
    
    /**
     * 更新用户信息
     */
    public function updateUser(int $userId, array $data): array
    {
        try {
            // 移除不能更新的字段
            unset($data['id'], $data['created_at']);
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // 如果包含密码，需要加密
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $this->db->update('users', $data, ['id' => $userId]);
            
            $user = $this->getUserDetails($userId);
            
            return ['success' => true, 'user' => $user];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '更新失败'];
        }
    }
    
    /**
     * 修改密码
     */    public function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        try {
            $user = $this->db->selectOne('users', ['id' => $userId]);
            
            if (!$user) {
                return ['success' => false, 'message' => '用户不存在'];
            }
            
            // 验证旧密码
            if (!password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => '旧密码错误'];
            }
            
            // 更新密码
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update('users', 
                ['password' => $hashedNewPassword, 'updated_at' => date('Y-m-d H:i:s')],
                ['id' => $userId]
            );
            
            return ['success' => true, 'message' => '密码修改成功'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '修改密码失败'];
        }
    }
      /**
     * 请求密码重置
     */
    public function requestPasswordReset(string $email): bool
    {
        try {
            $user = $this->findUserByEmail($email);
            
            if (!$user) {
                // 为了安全，即使用户不存在也返回成功
                return true;
            }
            
            // 生成重置令牌
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1小时有效
            
            // 保存重置令牌
            $this->savePasswordResetToken($user['id'], $resetToken, $expiresAt);
              // 发送重置邮件
            $this->sendPasswordResetEmail($user['email'], $resetToken);
            
            return true;
              
        } catch (\Exception $e) {
            return false;
        }
    }
}
