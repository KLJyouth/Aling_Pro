<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

/**
 * 简化的认证API控制器
 * 专注于基本的登录功能
 */
class SimpleAuthApiController
{
    /**
     * 测试端点
     */
    public function test(): array
    {
        return [
            'success' => true,
            'message' => 'Simple Auth API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ];
    }

    /**
     * 用户登录
     */
    public function login(): array
    {
        try {
            // 获取请求数据
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            // 基本验证
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'error' => 'Email and password are required',
                    'timestamp' => date('c')
                ];
            }
            
            // 使用文件用户服务进行认证
            require_once __DIR__ . '/../../Services/FileUserService.php';
            require_once __DIR__ . '/../../Services/SimpleJwtService.php';
            
            $userService = new \AlingAi\Services\FileUserService();
            $jwtService = new \AlingAi\Services\SimpleJwtService();
            
            $user = $userService->verifyPassword($email, $password);
            
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Invalid credentials',
                    'timestamp' => date('c')
                ];
            }
            
            // 检查账户状态
            if ($user['status'] !== 'active') {
                return [
                    'success' => false,
                    'error' => 'Account is not active',
                    'timestamp' => date('c')
                ];
            }
            
            // 生成JWT令牌
            $tokenData = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'permissions' => $user['permissions'] ?? []
            ];
            
            $token = $jwtService->generateToken($tokenData);
            $refreshToken = $jwtService->generateRefreshToken($user['id']);
            
            // 记录登录
            $userService->recordLogin($user['id']);
            
            return [
                'success' => true,
                'data' => [
                    'token' => $token,
                    'refresh_token' => $refreshToken,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'username' => $user['username'],
                        'role' => $user['role'],
                        'avatar' => $user['avatar'],
                        'created_at' => $user['created_at']
                    ],
                    'expires_in' => 3600 // 1小时
                ],
                'timestamp' => date('c')
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Login failed: ' . $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
    
    /**
     * 验证令牌
     */
    public function verify(): array
    {
        try {
            require_once __DIR__ . '/../../Services/SimpleJwtService.php';
            $jwtService = new \AlingAi\Services\SimpleJwtService();
            
            $token = $jwtService->extractTokenFromHeader();
            
            if (!$token) {
                return [
                    'success' => false,
                    'error' => 'No token provided',
                    'timestamp' => date('c')
                ];
            }
            
            $payload = $jwtService->verifyToken($token);
            
            if (!$payload) {
                return [
                    'success' => false,
                    'error' => 'Invalid token',
                    'timestamp' => date('c')
                ];
            }
            
            return [
                'success' => true,
                'data' => [
                    'user_id' => $payload['user_id'],
                    'email' => $payload['email'],
                    'role' => $payload['role'],
                    'permissions' => $payload['permissions'] ?? []
                ],
                'timestamp' => date('c')
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Token verification failed: ' . $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
}
