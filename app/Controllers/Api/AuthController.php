<?php
namespace App\Controllers\Api;

/**
 * API认证控制器
 * 
 * 处理用户认证相关的API请求
 */
class AuthController
{
    /**
     * 用户登录
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function login($requestData = [], $params = [])
    {
        // 验证请求数据
        if (empty($requestData['email']) || empty($requestData['password'])) {
            return [
                'error' => true,
                'message' => '邮箱和密码不能为空',
                'code' => 400
            ];
        }
        
        $email = $requestData['email'];
        $password = $requestData['password'];
        
        // TODO: 实现实际的用户认证逻辑
        // 这里仅作为演示，返回模拟数据
        
        // 模拟成功的身份验证
        if ($email === 'test@example.com' && $password === 'password') {
            // 生成访问令牌
            $token = $this->generateJwtToken([
                'sub' => 1, // 用户ID
                'name' => 'Test User',
                'email' => $email,
                'iat' => time(),
                'exp' => time() + 3600 // 1小时过期
            ]);
            
            return [
                'status' => 'success',
                'message' => '登录成功',
                'user' => [
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => $email
                ],
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600)
            ];
        } else {
            return [
                'error' => true,
                'message' => '邮箱或密码错误',
                'code' => 401
            ];
        }
    }
    
    /**
     * 用户注册
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function register($requestData = [], $params = [])
    {
        // 验证请求数据
        $requiredFields = ['name', 'email', 'password', 'password_confirmation'];
        foreach ($requiredFields as $field) {
            if (empty($requestData[$field])) {
                return [
                    'error' => true,
                    'message' => '缺少必要的字段: ' . $field,
                    'code' => 400
                ];
            }
        }
        
        // 检查密码确认
        if ($requestData['password'] !== $requestData['password_confirmation']) {
            return [
                'error' => true,
                'message' => '密码和确认密码不匹配',
                'code' => 400
            ];
        }
        
        // TODO: 实现实际的用户注册逻辑
        // 这里仅作为演示，返回模拟数据
        
        return [
            'status' => 'success',
            'message' => '注册成功，请检查邮箱激活账户',
            'user' => [
                'id' => 2,
                'name' => $requestData['name'],
                'email' => $requestData['email']
            ]
        ];
    }
    
    /**
     * 用户登出
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function logout($requestData = [], $params = [])
    {
        // TODO: 实现令牌失效逻辑
        
        return [
            'status' => 'success',
            'message' => '成功登出'
        ];
    }
    
    /**
     * 刷新令牌
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function refreshToken($requestData = [], $params = [])
    {
        // 验证当前令牌
        $token = $this->getBearerToken();
        
        if (!$token) {
            return [
                'error' => true,
                'message' => '未提供令牌',
                'code' => 401
            ];
        }
        
        // TODO: 验证令牌并刷新
        // 这里仅作为演示，返回模拟数据
        
        // 生成新令牌
        $newToken = $this->generateJwtToken([
            'sub' => 1, // 用户ID
            'name' => 'Test User',
            'email' => 'test@example.com',
            'iat' => time(),
            'exp' => time() + 3600 // 1小时过期
        ]);
        
        return [
            'status' => 'success',
            'message' => '令牌已刷新',
            'token' => $newToken,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600)
        ];
    }
    
    /**
     * 忘记密码
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function forgotPassword($requestData = [], $params = [])
    {
        // 验证请求数据
        if (empty($requestData['email'])) {
            return [
                'error' => true,
                'message' => '邮箱不能为空',
                'code' => 400
            ];
        }
        
        $email = $requestData['email'];
        
        // TODO: 发送密码重置邮件
        // 这里仅作为演示，返回模拟数据
        
        return [
            'status' => 'success',
            'message' => '密码重置链接已发送到您的邮箱'
        ];
    }
    
    /**
     * 重置密码
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function resetPassword($requestData = [], $params = [])
    {
        // 验证请求数据
        $requiredFields = ['token', 'email', 'password', 'password_confirmation'];
        foreach ($requiredFields as $field) {
            if (empty($requestData[$field])) {
                return [
                    'error' => true,
                    'message' => '缺少必要的字段: ' . $field,
                    'code' => 400
                ];
            }
        }
        
        // 检查密码确认
        if ($requestData['password'] !== $requestData['password_confirmation']) {
            return [
                'error' => true,
                'message' => '密码和确认密码不匹配',
                'code' => 400
            ];
        }
        
        // TODO: 验证令牌并重置密码
        // 这里仅作为演示，返回模拟数据
        
        return [
            'status' => 'success',
            'message' => '密码已成功重置，请使用新密码登录'
        ];
    }
    
    /**
     * 验证邮箱
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function verifyEmail($requestData = [], $params = [])
    {
        // 验证令牌
        if (empty($params['token'])) {
            return [
                'error' => true,
                'message' => '无效的验证令牌',
                'code' => 400
            ];
        }
        
        $token = $params['token'];
        
        // TODO: 验证令牌并激活邮箱
        // 这里仅作为演示，返回模拟数据
        
        return [
            'status' => 'success',
            'message' => '邮箱验证成功，您的账户已激活'
        ];
    }
    
    /**
     * 获取请求中的Bearer令牌
     * 
     * @return string|null 令牌或null
     */
    private function getBearerToken()
    {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * 生成JWT令牌
     * 
     * @param array $payload 令牌载荷
     * @return string 生成的JWT令牌
     */
    private function generateJwtToken($payload)
    {
        // 获取JWT密钥
        $secret = getenv('JWT_SECRET') ?: 'default_secret_key_change_in_production';
        
        // 创建JWT头部
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        
        // 编码头部和载荷
        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        
        // 创建签名
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        // 组合为JWT令牌
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * Base64 URL编码
     * 
     * @param string $data 要编码的数据
     * @return string 编码后的字符串
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
} 