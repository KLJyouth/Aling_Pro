<?php
namespace App\Core;

/**
 * 安全处理类
 * 负责处理安全相关功能
 */
class Security
{
    /**
     * 安全配置
     * @var array
     */
    private static $config = [];
    
    /**
     * 初始化安全系统
     * @param array $config 安全配置
     * @return void
     */
    public static function init(array $config)
    {
        self::$config = $config;
        
        // 设置会话安全选项
        if (isset($config['session'])) {
            self::setupSessionSecurity($config['session']);
        }
    }
    
    /**
     * 设置会话安全选项
     * @param array $config 会话安全配置
     * @return void
     */
    private static function setupSessionSecurity(array $config = [])
    {
        // 检查会话状态
        if (session_status() === PHP_SESSION_ACTIVE) {
            // 会话已经启动，不能更改会话名称和cookie参数
            if (class_exists('\App\Core\Logger')) {
                \App\Core\Logger::warning('尝试在会话已激活时更改会话安全设置，这将被忽略');
            } else {
                error_log('尝试在会话已激活时更改会话安全设置，这将被忽略');
            }
            return; // 直接返回，不做任何更改
        }
        
        // 会话尚未启动，可以设置会话名称和cookie参数
        if (!empty($config['name'])) {
            session_name($config['name']);
        }
        
        // 设置会话cookie参数
        $params = session_get_cookie_params();
        
        session_set_cookie_params([
            'lifetime' => $config['lifetime'] ?? $params['lifetime'],
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $config['secure'] ?? $params['secure'],
            'httponly' => $config['httponly'] ?? $params['httponly'],
            'samesite' => $config['samesite'] ?? 'Lax'
        ]);
    }
    
    /**
     * 生成CSRF令牌
     * @return string CSRF令牌
     */
    public static function generateCsrfToken()
    {
        if (!self::isCsrfEnabled()) {
            return '';
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * 验证CSRF令牌
     * @param string $token 要验证的令牌
     * @return bool 是否有效
     */
    public static function validateCsrfToken($token)
    {
        if (!self::isCsrfEnabled()) {
            return true;
        }
        
        // 检查令牌是否存在
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // 检查令牌是否一致
        if ($_SESSION['csrf_token'] !== $token) {
            return false;
        }
        
        // 检查令牌是否过期
        $lifetime = self::$config['csrf']['token_lifetime'] ?? 7200;
        if (time() - $_SESSION['csrf_token_time'] > $lifetime) {
            // 令牌已过期，生成新令牌
            self::generateCsrfToken();
            return false;
        }
        
        return true;
    }
    
    /**
     * 检查CSRF保护是否开启
     * @return bool 是否开启
     */
    public static function isCsrfEnabled()
    {
        return isset(self::$config['csrf']) && !empty(self::$config['csrf']['enabled']);
    }
    
    /**
     * 生成CSRF表单字段
     * @return string HTML表单字段
     */
    public static function csrfField()
    {
        if (!self::isCsrfEnabled()) {
            return '';
        }
        
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * 过滤输入，防止XSS攻击
     * @param string $input 输入字符串
     * @return string 过滤后的字符串
     */
    public static function xssFilter($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        
        // 检查XSS过滤是否开启
        if (isset(self::$config['xss']) && !empty(self::$config['xss']['enabled'])) {
            // 基本HTML过滤
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        
        return $input;
    }
    
    /**
     * 过滤数组输入，防止XSS攻击
     * @param array $input 输入数组
     * @return array 过滤后的数组
     */
    public static function xssFilterArray(array $input)
    {
        $result = [];
        
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::xssFilterArray($value);
            } else {
                $result[$key] = self::xssFilter($value);
            }
        }
        
        return $result;
    }
    
    /**
     * 验证密码强度
     * @param string $password 密码
     * @return array 验证结果和错误信息
     */
    public static function validatePassword($password)
    {
        $result = [
            'valid' => true,
            'errors' => []
        ];
        
        // 获取密码策略
        $policy = self::$config['password'] ?? [];
        
        // 检查密码长度
        $minLength = $policy['min_length'] ?? 8;
        if (strlen($password) < $minLength) {
            $result['valid'] = false;
            $result['errors'][] = "密码长度不能少于{$minLength}个字符";
        }
        
        // 检查是否包含特殊字符
        if (!empty($policy['require_special_chars'])) {
            if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
                $result['valid'] = false;
                $result['errors'][] = "密码必须包含至少一个特殊字符";
            }
        }
        
        // 检查是否包含数字
        if (!empty($policy['require_numbers'])) {
            if (!preg_match('/[0-9]/', $password)) {
                $result['valid'] = false;
                $result['errors'][] = "密码必须包含至少一个数字";
            }
        }
        
        // 检查是否包含大写字母
        if (!empty($policy['require_uppercase'])) {
            if (!preg_match('/[A-Z]/', $password)) {
                $result['valid'] = false;
                $result['errors'][] = "密码必须包含至少一个大写字母";
            }
        }
        
        // 检查是否包含小写字母
        if (!empty($policy['require_lowercase'])) {
            if (!preg_match('/[a-z]/', $password)) {
                $result['valid'] = false;
                $result['errors'][] = "密码必须包含至少一个小写字母";
            }
        }
        
        return $result;
    }
    
    /**
     * 生成安全的随机字符串
     * @param int $length 字符串长度
     * @return string 随机字符串
     */
    public static function generateRandomString($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * 安全地哈希密码
     * @param string $password 原始密码
     * @return string 哈希后的密码
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
    
    /**
     * 验证密码哈希
     * @param string $password 原始密码
     * @param string $hash 哈希值
     * @return bool 是否匹配
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * 检查密码是否需要重新哈希
     * @param string $hash 哈希值
     * @return bool 是否需要重新哈希
     */
    public static function passwordNeedsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    /**
     * 生成API令牌
     * @param int $userId 用户ID
     * @param array $permissions 权限数组
     * @param int $expiry 过期时间（秒）
     * @return string 生成的JWT令牌
     */
    public static function generateApiToken($userId, array $permissions = [], $expiry = null)
    {
        if ($expiry === null) {
            $expiry = self::$config['token']['lifetime'] ?? 86400; // 默认24小时
        }
        
        // 创建有效载荷
        $payload = [
            'sub' => $userId,               // subject (用户ID)
            'iat' => time(),                // issued at (签发时间)
            'exp' => time() + $expiry,      // expiration time (过期时间)
            'jti' => self::generateRandomString(16),  // JWT ID (唯一标识)
            'perm' => $permissions          // 权限
        ];
        
        // 加密令牌
        return self::encodeJwt($payload);
    }

    /**
     * 验证API令牌
     * @param string $token JWT令牌
     * @return array|false 验证成功返回解码后的载荷，失败返回false
     */
    public static function validateApiToken($token)
    {
        // 解码令牌
        $payload = self::decodeJwt($token);
        
        if ($payload === false) {
            return false;
        }
        
        // 检查令牌是否过期
        if ($payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }

    /**
     * 刷新API令牌
     * @param string $token 现有JWT令牌
     * @return string|false 刷新的JWT令牌，失败返回false
     */
    public static function refreshApiToken($token)
    {
        $payload = self::validateApiToken($token);
        
        if ($payload === false) {
            return false;
        }
        
        // 获取刷新时间
        $refreshTime = self::$config['token']['refresh_time'] ?? 3600; // 默认1小时
        
        // 创建新的有效载荷
        $newPayload = [
            'sub' => $payload['sub'],
            'iat' => time(),
            'exp' => time() + ($payload['exp'] - $payload['iat']), // 保持原有的有效期
            'jti' => self::generateRandomString(16),
            'perm' => $payload['perm'] ?? []
        ];
        
        // 加密新令牌
        return self::encodeJwt($newPayload);
    }

    /**
     * 编码JWT
     * @param array $payload 有效载荷
     * @return string JWT令牌
     */
    private static function encodeJwt(array $payload)
    {
        // 获取密钥
        $secret = self::getJwtSecret();
        
        // 创建头部
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        // 编码头部和载荷
        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        
        // 创建签名
        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        // 组合JWT
        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }

    /**
     * 解码JWT
     * @param string $token JWT令牌
     * @return array|false 解码后的载荷，失败返回false
     */
    private static function decodeJwt($token)
    {
        // 分割令牌
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        // 解码载荷
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        
        if ($payload === null) {
            return false;
        }
        
        // 验证签名
        $secret = self::getJwtSecret();
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $secret, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        return $payload;
    }

    /**
     * 获取JWT密钥
     * @return string JWT密钥
     */
    private static function getJwtSecret()
    {
        // 从配置获取密钥，如果不存在则使用一个默认值
        // 注意：在生产环境中，应该使用一个安全的密钥存储机制
        $secret = self::$config['token']['secret'] ?? getenv('JWT_SECRET');
        
        if (empty($secret)) {
            // 如果没有设置密钥，使用应用密钥（如果有的话）
            $secret = getenv('APP_KEY');
            
            if (empty($secret)) {
                // 警告：这不是一个安全的做法，仅用于开发环境
                Logger::warning('没有配置JWT密钥，使用默认密钥。这在生产环境中是不安全的。');
                $secret = 'default_insecure_jwt_secret_please_change_this';
            }
        }
        
        return $secret;
    }

    /**
     * Base64 URL编码
     * @param string $data 要编码的数据
     * @return string 编码后的字符串
     */
    private static function base64UrlEncode($data)
    {
        $base64 = base64_encode($data);
        return str_replace(['+', '/', '='], ['-', '_', ''], $base64);
    }

    /**
     * Base64 URL解码
     * @param string $data 要解码的数据
     * @return string 解码后的字符串
     */
    private static function base64UrlDecode($data)
    {
        $base64 = str_replace(['-', '_'], ['+', '/'], $data);
        $padLength = strlen($base64) % 4;
        if ($padLength > 0) {
            $base64 .= str_repeat('=', 4 - $padLength);
        }
        return base64_decode($base64);
    }
} 