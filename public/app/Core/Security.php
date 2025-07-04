<?php
/**
 * 安全类
 * 
 * 负责处理安全相关功能，如身份验证、授权和数据验证
 * 
 * @package App\Core
 */

namespace App\Core;

class Security
{
    /**
     * 安全配置
     * 
     * @var array
     */
    private static $config = [];
    
    /**
     * 初始化安全系统
     * 
     * @param array $config 安全配置
     * @return void
     */
    public static function init(array $config)
    {
        self::$config = $config;
    }
    
    /**
     * 生成密码哈希
     * 
     * @param string $password 明文密码
     * @return string 密码哈希
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            "cost" => self::$config["password_cost"] ?? 12
        ]);
    }
    
    /**
     * 验证密码
     * 
     * @param string $password 明文密码
     * @param string $hash 密码哈希
     * @return bool
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * 生成随机令牌
     * 
     * @param int $length 令牌长度
     * @return string
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * 防止XSS攻击，过滤输入
     * 
     * @param string $input 输入字符串
     * @return string
     */
    public static function sanitizeInput($input)
    {
        return htmlspecialchars($input, ENT_QUOTES, "UTF-8");
    }
    
    /**
     * CSRF令牌生成
     * 
     * @param string $formId 表单ID
     * @return string
     */
    public static function generateCsrfToken($formId = "global")
    {
        if (!isset($_SESSION["csrf_tokens"])) {
            $_SESSION["csrf_tokens"] = [];
        }
        
        $token = self::generateToken();
        $_SESSION["csrf_tokens"][$formId] = [
            "token" => $token,
            "time" => time()
        ];
        
        return $token;
    }
    
    /**
     * 验证CSRF令牌
     * 
     * @param string $token 令牌
     * @param string $formId 表单ID
     * @return bool
     */
    public static function validateCsrfToken($token, $formId = "global")
    {
        if (!isset($_SESSION["csrf_tokens"][$formId])) {
            return false;
        }
        
        $storedToken = $_SESSION["csrf_tokens"][$formId]["token"];
        $tokenTime = $_SESSION["csrf_tokens"][$formId]["time"];
        $maxAge = self::$config["csrf_token_lifetime"] ?? 3600; // 默认1小时
        
        // 检查令牌是否过期
        if (time() - $tokenTime > $maxAge) {
            unset($_SESSION["csrf_tokens"][$formId]);
            return false;
        }
        
        // 验证令牌
        $valid = hash_equals($storedToken, $token);
        
        // 使用后立即删除令牌（一次性使用）
        if ($valid) {
            unset($_SESSION["csrf_tokens"][$formId]);
        }
        
        return $valid;
    }
    
    /**
     * 验证用户会话
     * 
     * @param bool $requireAdmin 是否需要管理员权限
     * @param string $redirectUrl 未登录时重定向的URL
     * @return array|null 用户数据或null
     */
    public static function validateSession($requireAdmin = false, $redirectUrl = "/login.php")
    {
        // 检查会话是否已启动
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // 检查用户是否已登录
        if (!isset($_SESSION["user_id"])) {
            if ($redirectUrl) {
                header("Location: {$redirectUrl}");
                exit;
            }
            return null;
        }
        
        // 获取用户数据
        $userId = $_SESSION["user_id"];
        
        try {
            $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                // 用户不存在，清除会话
                self::logout();
                if ($redirectUrl) {
                    header("Location: {$redirectUrl}");
                    exit;
                }
                return null;
            }
            
            // 检查用户状态
            if ($user["status"] !== "active") {
                self::logout();
                if ($redirectUrl) {
                    header("Location: {$redirectUrl}?error=account_inactive");
                    exit;
                }
                return null;
            }
            
            // 检查管理员权限
            if ($requireAdmin && $user["role"] !== "admin") {
                if ($redirectUrl) {
                    header("Location: /");
                    exit;
                }
                return null;
            }
            
            return $user;
        } catch (\Exception $e) {
            Logger::error("验证会话失败: " . $e->getMessage());
            if ($redirectUrl) {
                header("Location: {$redirectUrl}?error=system");
                exit;
            }
            return null;
        }
    }
    
    /**
     * 用户登出
     * 
     * @return void
     */
    public static function logout()
    {
        // 检查会话是否已启动
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // 清除会话数据
        $_SESSION = [];
        
        // 销毁会话Cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // 销毁会话
        session_destroy();
    }
}
