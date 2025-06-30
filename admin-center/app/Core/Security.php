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
    private static function setupSessionSecurity(array $config)
    {
        // 设置会话名称
        if (!empty($config['name'])) {
            session_name($config['name']);
        }
        
        // 设置会话cookie参数
        if (session_status() === PHP_SESSION_ACTIVE) {
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
} 