<?php

namespace AlingAi\Services;

use Psr\Log\LoggerInterface;
use AlingAi\Security\EncryptionService;
use AlingAi\Models\User;
use AlingAi\Security\JWT;
use AlingAi\Security\JWTManager;

/**
 * 增强认证服务类
 * 
 * 提供完整的认证功能，包括双因素认证、会话管理和安全令牌处理
 * 
 * @package AlingAi\Services
 * @version 6.0.0
 */
class EnhancedAuthService extends AuthService
{
    private EncryptionService $encryption;
    private array $config;
    private array $sessions = [];
    
    /**
     * 构造函数
     */
    public function __construct(
        DatabaseServiceInterface $db, 
        CacheService $cache, 
        LoggerInterface $logger, 
        EncryptionService $encryption
    ) {
        parent::__construct($db, $cache, $logger);
        $this->encryption = $encryption;
        $this->config = $this->loadAuthConfig();
    }
    
    /**
     * 加载认证配置
     */
    private function loadAuthConfig(): array
    {
        return [
            'enable_2fa' => (bool)($_ENV['ENABLE_2FA'] ?? false),
            'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 3600), // 默认1小时
            'max_login_attempts' => (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5),
            'lockout_time' => (int)($_ENV['LOCKOUT_TIME'] ?? 900), // 默认15分钟
            'remember_me_lifetime' => (int)($_ENV['REMEMBER_ME_LIFETIME'] ?? 2592000), // 默认30天
            'secure_cookies' => (bool)($_ENV['SECURE_COOKIES'] ?? true),
            'api_rate_limit' => (int)($_ENV['API_RATE_LIMIT'] ?? 60), // 每分钟请求数
        ];
    }
    
    /**
     * 增强登录方法
     * 
     * @param string $email 邮箱
     * @param string $password 密码
     * @param bool $remember 是否记住登录
     * @param string $ip 客户端IP
     * @param string $userAgent 用户代理
     * @return array 登录结果
     */
    public function login(string $email, string $password, bool $remember = false, string $ip = '', string $userAgent = ''): array
    {
        // 检查登录尝试次数
        if ($this->isLoginThrottled($email, $ip)) {
            $this->logger->warning('登录尝试次数过多', ['email' => $email, 'ip' => $ip]);
            return ['success' => false, 'message' => '登录尝试次数过多，请稍后再试', 'error_code' => 'throttled'];
        }
        
        // 调用父类登录方法
        $result = parent::login($email, $password);
        
        // 登录失败，记录失败次数
        if (!$result['success']) {
            $this->recordFailedLogin($email, $ip);
            return $result;
        }
        
        // 检查是否需要双因素认证
        if ($this->config['enable_2fa'] && $this->is2faEnabled($result['user']['id'])) {
            // 生成并发送2FA验证码
            $code = $this->generate2faCode($result['user']['id']);
            $this->send2faCode($result['user']['email'], $code);
            
            return [
                'success' => true,
                'requires_2fa' => true,
                'message' => '请输入发送到您邮箱的验证码',
                'user_id' => $result['user']['id'],
                'temp_token' => $this->generateTempToken($result['user']['id'])
            ];
        }
        
        // 创建会话
        $session = $this->createSession($result['user']['id'], $ip, $userAgent, $remember);
        
        // 重置失败登录计数
        $this->resetFailedLoginCount($email, $ip);
        
        // 返回结果
        return [
            'success' => true,
            'message' => $result['message'],
            'user' => $result['user'],
            'tokens' => $result['tokens'],
            'session' => $session
        ];
    }
    
    /**
     * 验证双因素认证码
     * 
     * @param string $tempToken 临时令牌
     * @param string $code 验证码
     * @param string $ip 客户端IP
     * @param string $userAgent 用户代理
     * @param bool $remember 是否记住登录
     * @return array 验证结果
     */
    public function verify2fa(string $tempToken, string $code, string $ip = '', string $userAgent = '', bool $remember = false): array
    {
        try {
            // 解析临时令牌
            $decoded = $this->decodeTempToken($tempToken);
            if (!$decoded || !isset($decoded['user_id'])) {
                throw new \Exception('无效的临时令牌');
            }
            
            $userId = $decoded['user_id'];
            
            // 验证2FA码
            if (!$this->validate2faCode($userId, $code)) {
                $this->logger->warning('2FA验证失败', ['user_id' => $userId]);
                return ['success' => false, 'message' => '验证码无效或已过期'];
            }
            
            // 获取用户信息
            $user = $this->getUserDetails($userId);
            if (!$user) {
                throw new \Exception('找不到用户');
            }
            
            // 创建会话
            $session = $this->createSession($userId, $ip, $userAgent, $remember);
            
            // 生成令牌
            $tokens = $this->generateTokens($user);
            
            // 更新最后登录时间
            $this->updateLastLogin($userId);
            
            // 记录登录日志
            $this->logUserAction($userId, 'login_2fa', '用户通过双因素认证成功登录');
            
            return [
                'success' => true,
                'message' => '双因素认证成功',
                'user' => $this->sanitizeUser($user),
                'tokens' => $tokens,
                'session' => $session
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('2FA验证错误', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => '验证失败: ' . $e->getMessage()];
        }
    }
    
    /**
     * 启用双因素认证
     * 
     * @param int $userId 用户ID
     * @return array 结果
     */
    public function enable2fa(int $userId): array
    {
        try {
            // 检查用户是否存在
            $user = $this->getUserDetails($userId);
            if (!$user) {
                throw new \Exception('用户不存在');
            }
            
            // 生成2FA密钥
            $secret = $this->generateSecret();
            
            // 保存到用户记录
            $this->db->update('users', $userId, [
                '2fa_secret' => $this->encryption->encrypt($secret, 'data')['data'],
                '2fa_enabled' => true,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->logger->info('用户启用了双因素认证', ['user_id' => $userId]);
            
            return [
                'success' => true,
                'message' => '双因素认证已启用',
                'secret' => $secret
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('启用2FA失败', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => '启用双因素认证失败: ' . $e->getMessage()];
        }
    }
    
    /**
     * 禁用双因素认证
     * 
     * @param int $userId 用户ID
     * @param string $password 密码（用于验证）
     * @return array 结果
     */
    public function disable2fa(int $userId, string $password): array
    {
        try {
            // 检查用户是否存在
            $user = $this->getUserDetails($userId);
            if (!$user) {
                throw new \Exception('用户不存在');
            }
            
            // 验证密码
            if (!$this->verifyPassword($password, $user['password'])) {
                $this->logger->warning('禁用2FA密码验证失败', ['user_id' => $userId]);
                return ['success' => false, 'message' => '密码验证失败'];
            }
            
            // 更新用户记录
            $this->db->update('users', $userId, [
                '2fa_secret' => null,
                '2fa_enabled' => false,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->logger->info('用户禁用了双因素认证', ['user_id' => $userId]);
            
            return [
                'success' => true,
                'message' => '双因素认证已禁用'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('禁用2FA失败', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => '禁用双因素认证失败: ' . $e->getMessage()];
        }
    }
    
    /**
     * 创建会话
     * 
     * @param int $userId 用户ID
     * @param string $ip 客户端IP
     * @param string $userAgent 用户代理
     * @param bool $remember 是否记住登录
     * @return array 会话信息
     */
    public function createSession(int $userId, string $ip = '', string $userAgent = '', bool $remember = false): array
    {
        $sessionId = bin2hex(random_bytes(32));
        $expiresAt = time() + ($remember ? $this->config['remember_me_lifetime'] : $this->config['session_lifetime']);
        
        $sessionData = [
            'id' => $sessionId,
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            'last_activity' => date('Y-m-d H:i:s')
        ];
        
        // 保存会话到数据库
        $this->db->insert('sessions', $sessionData);
        
        // 缓存会话数据
        $this->cache->set('session:' . $sessionId, $sessionData, $expiresAt - time());
        
        return [
            'session_id' => $sessionId,
            'expires_at' => $expiresAt,
            'remember' => $remember
        ];
    }
    
    /**
     * 验证会话
     * 
     * @param string $sessionId 会话ID
     * @param string $ip 客户端IP
     * @param string $userAgent 用户代理
     * @return array|null 会话信息或null（无效会话）
     */
    public function validateSession(string $sessionId, string $ip = '', string $userAgent = ''): ?array
    {
        // 从缓存获取会话
        $session = $this->cache->get('session:' . $sessionId);
        
        // 如果缓存中没有，从数据库获取
        if (!$session) {
            $session = $this->db->selectOne('sessions', ['id' => $sessionId]);
            if ($session) {
                // 更新缓存
                $expiresAt = strtotime($session['expires_at']);
                $this->cache->set('session:' . $sessionId, $session, max(0, $expiresAt - time()));
            }
        }
        
        // 检查会话是否存在且有效
        if (!$session || strtotime($session['expires_at']) < time()) {
            return null;
        }
        
        // 可选：验证IP和用户代理（防止会话劫持）
        if ($ip && $session['ip_address'] && $ip !== $session['ip_address']) {
            $this->logger->warning('会话IP不匹配', ['session_id' => $sessionId, 'expected' => $session['ip_address'], 'actual' => $ip]);
            return null;
        }
        
        // 更新最后活动时间
        $this->updateSessionActivity($sessionId);
        
        return $session;
    }
    
    /**
     * 更新会话活动时间
     * 
     * @param string $sessionId 会话ID
     */
    public function updateSessionActivity(string $sessionId): void
    {
        $now = date('Y-m-d H:i:s');
        
        // 更新数据库
        $this->db->update('sessions', ['id' => $sessionId], ['last_activity' => $now]);
        
        // 更新缓存
        $session = $this->cache->get('session:' . $sessionId);
        if ($session) {
            $session['last_activity'] = $now;
            $expiresAt = strtotime($session['expires_at']);
            $this->cache->set('session:' . $sessionId, $session, max(0, $expiresAt - time()));
        }
    }
    
    /**
     * 销毁会话
     * 
     * @param string $sessionId 会话ID
     * @return bool 成功与否
     */
    public function destroySession(string $sessionId): bool
    {
        // 从数据库删除
        $this->db->delete('sessions', ['id' => $sessionId]);
        
        // 从缓存删除
        $this->cache->delete('session:' . $sessionId);
        
        return true;
    }
    
    /**
     * 销毁用户所有会话
     * 
     * @param int $userId 用户ID
     * @param string $exceptSessionId 排除的会话ID（当前会话）
     * @return int 销毁的会话数
     */
    public function destroyUserSessions(int $userId, string $exceptSessionId = ''): int
    {
        // 查询用户的所有会话
        $sessions = $this->db->select('sessions', ['user_id' => $userId]);
        
        $count = 0;
        foreach ($sessions as $session) {
            if ($exceptSessionId && $session['id'] === $exceptSessionId) {
                continue; // 跳过当前会话
            }
            
            // 销毁会话
            $this->destroySession($session['id']);
            $count++;
        }
        
        return $count;
    }
    
    /**
     * 检查登录尝试次数是否超限
     * 
     * @param string $email 邮箱
     * @param string $ip 客户端IP
     * @return bool 是否超限
     */
    private function isLoginThrottled(string $email, string $ip): bool
    {
        $emailKey = 'login_attempts:email:' . md5($email);
        $ipKey = 'login_attempts:ip:' . md5($ip);
        
        $emailAttempts = (int)$this->cache->get($emailKey, 0);
        $ipAttempts = (int)$this->cache->get($ipKey, 0);
        
        return $emailAttempts >= $this->config['max_login_attempts'] || 
               ($ip && $ipAttempts >= $this->config['max_login_attempts'] * 2);
    }
    
    /**
     * 记录失败的登录尝试
     * 
     * @param string $email 邮箱
     * @param string $ip 客户端IP
     */
    private function recordFailedLogin(string $email, string $ip): void
    {
        $emailKey = 'login_attempts:email:' . md5($email);
        $ipKey = 'login_attempts:ip:' . md5($ip);
        
        $emailAttempts = (int)$this->cache->get($emailKey, 0) + 1;
        $this->cache->set($emailKey, $emailAttempts, $this->config['lockout_time']);
        
        if ($ip) {
            $ipAttempts = (int)$this->cache->get($ipKey, 0) + 1;
            $this->cache->set($ipKey, $ipAttempts, $this->config['lockout_time']);
        }
        
        // 记录日志
        $this->logger->warning('登录失败', [
            'email' => $email,
            'ip' => $ip,
            'attempts' => $emailAttempts
        ]);
    }
    
    /**
     * 重置失败登录计数
     * 
     * @param string $email 邮箱
     * @param string $ip 客户端IP
     */
    private function resetFailedLoginCount(string $email, string $ip): void
    {
        $emailKey = 'login_attempts:email:' . md5($email);
        $ipKey = 'login_attempts:ip:' . md5($ip);
        
        $this->cache->delete($emailKey);
        if ($ip) {
            $this->cache->delete($ipKey);
        }
    }
    
    /**
     * 检查用户是否启用了双因素认证
     * 
     * @param int $userId 用户ID
     * @return bool 是否启用
     */
    private function is2faEnabled(int $userId): bool
    {
        $user = $this->getUserDetails($userId);
        return $user && isset($user['2fa_enabled']) && $user['2fa_enabled'];
    }
    
    /**
     * 生成双因素认证码
     * 
     * @param int $userId 用户ID
     * @return string 验证码
     */
    private function generate2faCode(int $userId): string
    {
        // 生成6位数字验证码
        $code = sprintf('%06d', mt_rand(0, 999999));
        
        // 存储到缓存，有效期10分钟
        $this->cache->set('2fa_code:' . $userId, [
            'code' => $code,
            'created_at' => time()
        ], 600);
        
        return $code;
    }
    
    /**
     * 发送双因素认证码
     * 
     * @param string $email 邮箱
     * @param string $code 验证码
     * @return bool 发送结果
     */
    private function send2faCode(string $email, string $code): bool
    {
        // 在实际应用中，这里应该调用邮件服务发送验证码
        // 此处仅记录日志
        $this->logger->info('发送2FA验证码', [
            'email' => $email,
            'code' => $code
        ]);
        
        return true;
    }
    
    /**
     * 验证双因素认证码
     * 
     * @param int $userId 用户ID
     * @param string $code 验证码
     * @return bool 验证结果
     */
    private function validate2faCode(int $userId, string $code): bool
    {
        $cacheKey = '2fa_code:' . $userId;
        $storedData = $this->cache->get($cacheKey);
        
        if (!$storedData || !isset($storedData['code'])) {
            return false;
        }
        
        // 验证码有效期检查（10分钟）
        if (time() - $storedData['created_at'] > 600) {
            $this->cache->delete($cacheKey);
            return false;
        }
        
        // 验证码匹配检查
        $result = $storedData['code'] === $code;
        
        // 无论成功与否，都删除缓存中的验证码（一次性使用）
        $this->cache->delete($cacheKey);
        
        return $result;
    }
    
    /**
     * 生成临时令牌（用于2FA验证过程）
     * 
     * @param int $userId 用户ID
     * @return string 临时令牌
     */
    private function generateTempToken(int $userId): string
    {
        $payload = [
            'user_id' => $userId,
            'purpose' => '2fa_verification',
            'exp' => time() + 600 // 10分钟有效期
        ];
        
        // 使用JWT生成令牌
        $jwtManager = new JWTManager($_ENV['JWT_SECRET'] ?? 'default_secret');
        return $jwtManager->generateToken($payload);
    }
    
    /**
     * 解析临时令牌
     * 
     * @param string $token 令牌
     * @return array|null 解析结果
     */
    private function decodeTempToken(string $token): ?array
    {
        try {
            $jwtManager = new JWTManager($_ENV['JWT_SECRET'] ?? 'default_secret');
            $decoded = $jwtManager->decodeToken($token);
            
            // 验证令牌用途
            if (!isset($decoded->purpose) || $decoded->purpose !== '2fa_verification') {
                return null;
            }
            
            return (array)$decoded;
        } catch (\Exception $e) {
            $this->logger->error('临时令牌解析失败', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * 生成密钥
     * 
     * @return string 密钥
     */
    private function generateSecret(): string
    {
        return bin2hex(random_bytes(16));
    }
} 