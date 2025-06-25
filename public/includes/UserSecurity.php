<?php
/**
 * AlingAi Pro 用户安全�?
 * 提供用户数据保护、加密、访问控制等安全功能
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

namespace AlingAi\Security;

class UserSecurity {
    /**
     * 加密常量
     */
    const CIPHER_ALGO = 'aes-256-gcm';
    const HASH_ALGO = 'sha256';
    const KEY_LENGTH = 32; // 256位密�?
    const TAG_LENGTH = 16; // GCM认证标签长度
    const PBKDF2_ITERATIONS = 10000;

    /**
     * 加密用户敏感数据
     * 
     * @param string $data 要加密的数据
     * @param string $masterKey 主密�?
     * @return array 包含密文和相关元数据的数�?
     */
    public static function encryptData($data, $masterKey) {
        if (empty($data) || empty($masterKey)) {
            throw new \InvalidArgumentException('数据和主密钥不能为空'];
        }

        // 生成随机盐和IV
        $salt = random_bytes(16];
        $iv = random_bytes(12]; // GCM模式推荐IV长度�?2字节

        // 使用PBKDF2从主密钥派生加密密钥
        $key = hash_pbkdf2(
            self::HASH_ALGO,
            $masterKey,
            $salt,
            self::PBKDF2_ITERATIONS,
            self::KEY_LENGTH,
            true
        ];

        // 使用GCM模式加密
        $tag = '';
        $encrypted = openssl_encrypt(
            $data,
            self::CIPHER_ALGO,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '', // 附加数据，用于认�?
            self::TAG_LENGTH
        ];

        if ($encrypted === false) {
            throw new \RuntimeException('加密失败: ' . openssl_error_string()];
        }

        // 将所有二进制数据转换为Base64
        return [
            'ciphertext' => base64_encode($encrypted],
            'iv' => base64_encode($iv],
            'salt' => base64_encode($salt],
            'tag' => base64_encode($tag],
            'algo' => self::CIPHER_ALGO,
            'version' => 1, // 加密版本，便于未来升级加密算�?
        ];
    }

    /**
     * 解密用户敏感数据
     * 
     * @param array $encryptedData 加密数据数组
     * @param string $masterKey 主密�?
     * @return string 解密后的数据
     */
    public static function decryptData($encryptedData, $masterKey) {
        if (empty($encryptedData) || empty($masterKey)) {
            throw new \InvalidArgumentException('加密数据和主密钥不能为空'];
        }

        // 检查必要的加密元素
        $requiredKeys = ['ciphertext', 'iv', 'salt', 'tag', 'algo', 'version'];
        foreach ($requiredKeys as $key) {
            if (!isset($encryptedData[$key])) {
                throw new \InvalidArgumentException("缺少加密元素: {$key}"];
            }
        }

        // 检查加密算法版�?
        if ($encryptedData['version'] != 1) {
            throw new \RuntimeException("不支持的加密版本: {$encryptedData['version']}"];
        }

        // 检查加密算�?
        if ($encryptedData['algo'] != self::CIPHER_ALGO) {
            throw new \RuntimeException("不支持的加密算法: {$encryptedData['algo']}"];
        }

        // 解码所有Base64数据
        $ciphertext = base64_decode($encryptedData['ciphertext']];
        $iv = base64_decode($encryptedData['iv']];
        $salt = base64_decode($encryptedData['salt']];
        $tag = base64_decode($encryptedData['tag']];

        // 使用PBKDF2从主密钥派生加密密钥
        $key = hash_pbkdf2(
            self::HASH_ALGO,
            $masterKey,
            $salt,
            self::PBKDF2_ITERATIONS,
            self::KEY_LENGTH,
            true
        ];

        // 使用GCM模式解密
        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER_ALGO,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        ];

        if ($decrypted === false) {
            throw new \RuntimeException('解密失败: ' . openssl_error_string()];
        }

        return $decrypted;
    }

    /**
     * 生成安全密码哈希
     * 
     * @param string $password 原始密码
     * @return string 密码哈希
     */
    public static function hashPassword($password) {
        if (empty($password)) {
            throw new \InvalidArgumentException('密码不能为空'];
        }
        
        // 使用Argon2id算法进行密码哈希（PHP 7.3+�?
        $options = [
            'memory_cost' => 65536, // 64MB
            'time_cost' => 4,       // 4次迭�?
            'threads' => 3          // 3线程
        ];
        
        return password_hash($password, PASSWORD_ARGON2ID, $options];
    }

    /**
     * 验证密码
     * 
     * @param string $password 原始密码
     * @param string $hash 密码哈希
     * @return bool 验证结果
     */
    public static function verifyPassword($password, $hash) {
        if (empty($password) || empty($hash)) {
            return false;
        }
        
        return password_verify($password, $hash];
    }

    /**
     * 检查密码强�?
     * 
     * @param string $password 密码
     * @return array 包含强度评分和建议的数组
     */
    public static function checkPasswordStrength($password) {
        $score = 0;
        $feedback = [];

        // 长度检�?
        $length = strlen($password];
        if ($length < 8) {
            $feedback[] = '密码长度应至少为8个字�?;
        } elseif ($length >= 12) {
            $score += 2;
        } else {
            $score += 1;
        }

        // 复杂性检�?
        if (preg_match('/[A-Z]/', $password)) {
            $score++;
        } else {
            $feedback[] = '应包含至少一个大写字�?;
        }

        if (preg_match('/[a-z]/', $password)) {
            $score++;
        } else {
            $feedback[] = '应包含至少一个小写字�?;
        }

        if (preg_match('/[0-9]/', $password)) {
            $score++;
        } else {
            $feedback[] = '应包含至少一个数�?;
        }

        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score++;
        } else {
            $feedback[] = '应包含至少一个特殊字�?;
        }

        // 常见密码模式检�?
        $commonPatterns = [
            '/^123456/',
            '/^password/i',
            '/^qwerty/i',
            '/^admin/i',
            '/^welcome/i',
            '/^12345678/'
        ];
        
        foreach ($commonPatterns as $pattern) {
            if (preg_match($pattern, $password)) {
                $score = 0;
                $feedback[] = '密码包含常见的不安全模式';
                break;
            }
        }

        // 评分结果
        $result = [
            'score' => $score,
            'strength' => 'weak',
            'feedback' => $feedback
        ];

        if ($score >= 5) {
            $result['strength'] = 'strong';
        } elseif ($score >= 3) {
            $result['strength'] = 'medium';
        }

        return $result;
    }

    /**
     * 生成防CSRF令牌
     * 
     * @param string $formId 表单ID
     * @return string CSRF令牌
     */
    public static function generateCsrfToken($formId = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(];
        }
        
        $tokenId = $formId ? "csrf_token_{$formId}" : 'csrf_token';
        
        // 如果令牌不存在或过期，生成新令牌
        if (!isset($_SESSION[$tokenId]) || 
            (isset($_SESSION["{$tokenId}_time"]) && time() - $_SESSION["{$tokenId}_time"] > 3600)) {
            $_SESSION[$tokenId] = bin2hex(random_bytes(32)];
            $_SESSION["{$tokenId}_time"] = time(];
        }
        
        return $_SESSION[$tokenId];
    }

    /**
     * 验证CSRF令牌
     * 
     * @param string $token 提交的令�?
     * @param string $formId 表单ID
     * @return bool 验证结果
     */
    public static function validateCsrfToken($token, $formId = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(];
        }
        
        $tokenId = $formId ? "csrf_token_{$formId}" : 'csrf_token';
        
        if (!isset($_SESSION[$tokenId]) || !hash_equals($_SESSION[$tokenId],  $token)) {
            return false;
        }
        
        return true;
    }

    /**
     * 生成随机令牌
     * 
     * @param int $length 令牌长度
     * @return string 随机令牌
     */
    public static function generateRandomToken($length = 32) {
        return bin2hex(random_bytes($length / 2)];
    }

    /**
     * 安全地执行重定向
     * 
     * @param string $url 目标URL
     * @param array $allowedDomains 允许的域名列�?
     * @return void
     */
    public static function safeRedirect($url, $allowedDomains = []) {
        // 验证URL格式
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = '/'; // 如无效则重定向到首页
        }

        // 解析URL
        $parsedUrl = parse_url($url];
        
        // 检查是否是相对URL
        if (empty($parsedUrl['host'])) {
            header("Location: {$url}"];
            exit;
        }
        
        // 检查域名是否在允许列表�?
        $isAllowedDomain = false;
        foreach ($allowedDomains as $domain) {
            if (strcasecmp(substr($parsedUrl['host'],  -strlen($domain)], $domain) === 0) {
                $isAllowedDomain = true;
                break;
            }
        }
        
        if (!$isAllowedDomain) {
            $url = '/'; // 如域名不在允许列表中，重定向到首�?
        }
        
        header("Location: {$url}"];
        exit;
    }

    /**
     * IP地址风险评估
     * 
     * @param string $ipAddress IP地址
     * @return array 风险评估结果
     */
    public static function assessIpRisk($ipAddress) {
        $risk = [
            'score' => 0, // 0-100，分数越高风险越�?
            'factors' => [], 
            'level' => 'low',
        ];
        
        // 检查是否是私有IP
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $risk['factors'][] = '私有或保留IP地址';
            $risk['score'] += 50;
        }
        
        // 检查是否是代理IP（示例实现）
        // 真实实现可能需要使用专业的代理检测服�?
        $headers = [
            'HTTP_VIA',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR_IP',
            'X_FORWARDED_FOR',
            'FORWARDED_FOR',
            'X_FORWARDED',
            'FORWARDED',
            'CLIENT_IP',
            'HTTP_PROXY_CONNECTION'
        ];
        
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $risk['factors'][] = '检测到代理标头';
                $risk['score'] += 30;
                break;
            }
        }
        
        // 设置风险级别
        if ($risk['score'] >= 70) {
            $risk['level'] = 'high';
        } elseif ($risk['score'] >= 40) {
            $risk['level'] = 'medium';
        }
        
        return $risk;
    }

    /**
     * 验证用户会话状�?
     * 
     * @param bool $requireAdmin 是否要求管理员权�?
     * @param string $redirectUrl 未登录时重定向URL
     * @return array|bool 用户数据或false
     */
    public static function validateSession($requireAdmin = false, $redirectUrl = '/login.php') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(];
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            // 未登录，重定�?
            if ($redirectUrl !== null) {
                header("Location: {$redirectUrl}"];
                exit;
            }
            return false;
        }
        
        // 检查会话活动超时（30分钟�?
        if (time() - $_SESSION['last_activity'] > 1800) {
            // 会话过期，清理会�?
            self::destroySession(];
            
            if ($redirectUrl !== null) {
                header("Location: {$redirectUrl}?expired=1"];
                exit;
            }
            return false;
        }
        
        // 更新最后活动时�?
        $_SESSION['last_activity'] = time(];
        
        // 如果要求管理员权限，检查用户是否是管理�?
        if ($requireAdmin && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin')) {
            if ($redirectUrl !== null) {
                header("Location: /access-denied.php"];
                exit;
            }
            return false;
        }
        
        // 返回用户数据
        $userData = [
            'id' => $_SESSION['user_id'], 
            'username' => $_SESSION['username'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'role' => $_SESSION['user_role'] ?? '',
            'last_activity' => $_SESSION['last_activity']
        ];
        
        return $userData;
    }

    /**
     * 安全销毁用户会�?
     * 
     * @return void
     */
    public static function destroySession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(];
        }
        
        // 清除所有会话变�?
        $_SESSION = [];
        
        // 删除会话Cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params(];
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'], 
                $params['domain'], 
                $params['secure'], 
                $params['httponly']
            ];
        }
        
        // 销毁会�?
        session_destroy(];
    }

    /**
     * 记录安全审计日志
     * 
     * @param int $userId 用户ID
     * @param string $action 操作
     * @param string $description 描述
     * @param string $severity 严重程度（info/warning/critical�?
     * @param string $status 状态（success/failed�?
     * @return bool 是否记录成功
     */
    public static function logSecurityEvent($userId, $action, $description, $severity = 'info', $status = 'success') {
        try {
            // 加载配置文件
            $configFile = dirname(dirname(__DIR__)) . '/config/config.php';
            if (!file_exists($configFile)) {
                throw new \Exception('配置文件不存�?];
            }
            
            $config = require $configFile;
            
            // 连接数据�?
            if ($config['database']['type'] === 'sqlite') {
                $dbPath = dirname(dirname(__DIR__)) . '/' . $config['database']['path'];
                $pdo = new \PDO("sqlite:{$dbPath}"];
            } else {
                $host = $config['database']['host'];
                $port = $config['database']['port'] ?? 3306;
                $dbname = $config['database']['database'];
                $dbuser = $config['database']['username'];
                $dbpass = $config['database']['password'];
                
                $pdo = new \PDO("mysql:host={$host};port={$port};dbname={$dbname}", $dbuser, $dbpass];
            }
            
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION];
            
            // 插入日志记录
            $stmt = $pdo->prepare("INSERT INTO security_audit_log 
                (user_id, action, description, ip_address, user_agent, severity, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"];
                
            $stmt->execute([
                $userId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'], 
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $severity,
                $status
            ]];
            
            return true;
        } catch (\Exception $e) {
            // 记录错误到系统日�?
            error_log("Security audit log error: " . $e->getMessage()];
            return false;
        }
    }
} 

