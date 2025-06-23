<?php

namespace AlingAi\Security;

/**
 * CSRF防护服务
 */
class CSRFProtection
{
    private static $instance = null;
    private $tokenName = '_csrf_token';
    private $sessionKey = 'csrf_tokens';
    
    /**

    
     * __construct 方法

    
     *

    
     * @return void

    
     */

    
    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 生成CSRF令牌
     */
    /**

     * generateToken 方法

     *

     * @return void

     */

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
        
        // 只保留最近的10个令牌
        if (count($_SESSION[$this->sessionKey]) >= 10) {
            array_shift($_SESSION[$this->sessionKey]);
        }
        
        $_SESSION[$this->sessionKey][] = $token;
        
        return $token;
    }
    
    /**
     * 验证CSRF令牌
     */
    /**

     * validateToken 方法

     *

     * @param string $token

     * @return void

     */

    public function validateToken(string $token): bool
    {
        if (!isset($_SESSION[$this->sessionKey]) || empty($_SESSION[$this->sessionKey])) {
            return false;
        }
        
        $index = array_search($token, $_SESSION[$this->sessionKey]);
        if ($index !== false) {
            // 使用后删除令牌
            unset($_SESSION[$this->sessionKey][$index]);
            $_SESSION[$this->sessionKey] = array_values($_SESSION[$this->sessionKey]);
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取隐藏的CSRF输入字段HTML
     */
    /**

     * getHiddenInput 方法

     *

     * @return void

     */

    public function getHiddenInput(): string
    {
        $token = $this->generateToken();
        return '<input type="hidden" name="' . $this->tokenName . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * 获取CSRF令牌（用于AJAX请求）
     */
    /**

     * getToken 方法

     *

     * @return void

     */

    public function getToken(): string
    {
        return $this->generateToken();
    }
    
    /**
     * 验证请求中的CSRF令牌
     */
    /**

     * validateRequest 方法

     *

     * @return void

     */

    public function validateRequest(): bool
    {
        $token = $_POST[$this->tokenName] ?? $_GET[$this->tokenName] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token) {
            return false;
        }
        
        return $this->validateToken($token);
    }
    
    /**
     * 中间件：验证CSRF令牌
     */
    /**

     * middleware 方法

     *

     * @return void

     */

    public function middleware(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if (!$this->validateRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'CSRF token validation failed',
                    'error_code' => 'CSRF_VALIDATION_FAILED'
                ]);
                exit;
            }
        }
    }
}

/**
 * XSS防护服务
 */
class XSSProtection
{
    /**
     * 清理用户输入，防止XSS攻击
     */
    public static function clean($input, $allowedTags = [])
    {
        if (is_array($input)) {
            return array_map(function($item) use ($allowedTags) {
                return self::clean($item, $allowedTags);
            }, $input);
        }
        
        if (!is_string($input)) {
            return $input;
        }
        
        // 移除危险的标签和属性
        $dangerousTags = [
            'script', 'iframe', 'object', 'embed', 'link', 'style', 'meta',
            'form', 'input', 'textarea', 'button', 'select', 'option'
        ];
        
        $dangerousAttributes = [
            'onload', 'onerror', 'onclick', 'onmouseover', 'onmouseout',
            'onfocus', 'onblur', 'onchange', 'onsubmit', 'onkeydown',
            'onkeyup', 'onkeypress', 'javascript:', 'vbscript:', 'data:'
        ];
        
        // 如果没有指定允许的标签，则清理所有HTML
        if (empty($allowedTags)) {
            return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // 使用HTMLPurifier或自定义清理
        $input = strip_tags($input, '<' . implode('><', $allowedTags) . '>');
        
        // 移除危险属性
        foreach ($dangerousAttributes as $attr) {
            $input = preg_replace('/\s*' . $attr . '\s*=\s*["\'][^"\']*["\']?/i', '', $input);
        }
        
        return $input;
    }
    
    /**
     * 安全输出到HTML
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * 安全输出到JavaScript
     */
    public static function escapeJS($string)
    {
        return json_encode($string, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
    
    /**
     * 安全输出到CSS
     */
    public static function escapeCSS($string)
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $string);
    }
    
    /**
     * 验证和清理URL
     */
    public static function cleanURL($url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $parsed = parse_url($url);
        if (!$parsed || !in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }
        
        return $url;
    }
}

/**
 * SQL注入防护服务
 */
class SQLInjectionProtection
{
    /**
     * 准备安全的SQL语句
     */
    public static function prepare(\PDO $pdo, string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $paramType = PDO::PARAM_STR;
                
                if (is_int($value)) {
                    $paramType = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $paramType = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $paramType = PDO::PARAM_NULL;
                }
                
                if (is_string($key)) {
                    $stmt->bindValue($key, $value, $paramType);
                } else {
                    $stmt->bindValue($key + 1, $value, $paramType);
                }
            }
            
            return $stmt;
        } catch (\PDOException $e) {
            error_log("SQL Preparation Error: " . $e->getMessage());
            throw new \Exception("Database query preparation failed");
        }
    }
    
    /**
     * 验证表名和列名（防止SQL注入）
     */
    public static function validateIdentifier(string $identifier): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier);
    }
    
    /**
     * 安全地构建WHERE子句
     */
    public static function buildWhereClause(array $conditions): array
    {
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            if (!self::validateIdentifier($field)) {
                throw new \InvalidArgumentException("Invalid field name: $field");
            }
            
            if (is_array($value)) {
                $placeholders = array_fill(0, count($value), '?');
                $whereParts[] = "`$field` IN (" . implode(',', $placeholders) . ")";
                $params = array_merge($params, $value);
            } else {
                $whereParts[] = "`$field` = ?";
                $params[] = $value;
            }
        }
        
        return [
            'where' => implode(' AND ', $whereParts),
            'params' => $params
        ];
    }
}

/**
 * 输入验证服务
 */
class InputValidation
{
    /**
     * 验证邮箱
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * 验证密码强度
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = '密码长度至少8位';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = '密码必须包含大写字母';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = '密码必须包含小写字母';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = '密码必须包含数字';
        }
        
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = '密码必须包含特殊字符';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * 验证用户名
     */
    public static function validateUsername(string $username): array
    {
        $errors = [];
        
        if (strlen($username) < 3) {
            $errors[] = '用户名长度至少3位';
        }
        
        if (strlen($username) > 20) {
            $errors[] = '用户名长度不能超过20位';
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = '用户名只能包含字母、数字和下划线';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * 验证手机号
     */
    public static function validatePhone(string $phone): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone);
    }
    
    /**
     * 通用数据验证
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $errors[$field][] = '此字段为必填项';
                    continue;
                }
                
                if (empty($value)) {
                    continue; // 如果值为空且不是必填，跳过其他验证
                }
                
                switch ($rule) {
                    case 'email':
                        if (!self::validateEmail($value)) {
                            $errors[$field][] = '邮箱格式不正确';
                        }
                        break;
                        
                    case 'phone':
                        if (!self::validatePhone($value)) {
                            $errors[$field][] = '手机号格式不正确';
                        }
                        break;
                        
                    default:
                        if (strpos($rule, 'min:') === 0) {
                            $min = (int)substr($rule, 4);
                            if (strlen($value) < $min) {
                                $errors[$field][] = "最少需要{$min}个字符";
                            }
                        } elseif (strpos($rule, 'max:') === 0) {
                            $max = (int)substr($rule, 4);
                            if (strlen($value) > $max) {
                                $errors[$field][] = "最多只能{$max}个字符";
                            }
                        }
                        break;
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
