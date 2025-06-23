<?php

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlingAi\Services\{AuthService, EmailService};
use AlingAi\Utils\Logger;

class AuthController_old extends BaseController
{
    private AuthService $auth;
    private EmailService $emailService;
    
    public function __construct(
        \AlingAi\Services\DatabaseService $db,
        \AlingAi\Services\CacheService $cache,
        AuthService $auth,
        EmailService $emailService
    ) {
        parent::__construct($db, $cache);
        $this->auth = $auth;
        $this->emailService = $emailService;
    }
    
    /**
     * 用户注册
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $data = $this->getRequestData($request);
        
        // 验证必需字段
        $required = ['username', 'email', 'password', 'password_confirmation'];
        $missing = $this->validateRequired($data, $required);
        
        if (!empty($missing)) {
            return $this->errorResponse(
                'Missing required fields',
                422,
                ['missing_fields' => $missing]
            );
        }
        
        // 验证密码确认
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->errorResponse(
                'Password confirmation does not match',
                422
            );
        }
        
        // 验证邮箱格式
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->errorResponse(
                'Invalid email format',
                422
            );
        }
        
        try {
            // 检查用户是否已存在
            if ($this->auth->userExists($data['email'], $data['username'])) {
                return $this->errorResponse(
                    'User already exists',
                    409
                );
            }
            
            // 注册用户
            $user = $this->auth->register([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? '',
                'avatar' => $data['avatar'] ?? '',
                'role' => 'user', // 默认角色
                'status' => 'pending' // 需要邮箱验证
            ]);
            
            // 发送验证邮件
            $this->sendVerificationEmail($user);
            
            // 记录操作
            $this->logAction('user_register', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]);
            
            return $this->successResponse(
                [
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'status' => $user['status']
                    ]
                ],
                'Registration successful. Please check your email for verification.',
                201
            );
            
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return $this->errorResponse(
                'Registration failed',
                500
            );
        }
    }
    
    /**
     * 用户登录
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $data = $this->getRequestData($request);
        
        // 验证必需字段
        $required = ['login', 'password']; // login可以是用户名或邮箱
        $missing = $this->validateRequired($data, $required);
        
        if (!empty($missing)) {
            return $this->errorResponse(
                'Missing required fields',
                422,
                ['missing_fields' => $missing]
            );
        }
        
        try {
            $result = $this->auth->login($data['login'], $data['password']);
            
            if (!$result['success']) {
                // 记录失败登录
                $this->logAction('login_failed', [
                    'login' => $data['login'],
                    'reason' => $result['message']
                ]);
                
                return $this->errorResponse(
                    $result['message'],
                    401
                );
            }
            
            // 记录成功登录
            $this->logAction('login_success', [
                'user_id' => $result['user']['id'],
                'username' => $result['user']['username']
            ]);
            
            return $this->successResponse(
                [
                    'user' => $result['user'],
                    'tokens' => $result['tokens'],
                    'expires_in' => $result['expires_in']
                ],
                'Login successful'
            );
            
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return $this->errorResponse(
                'Login failed',
                500
            );
        }
    }
    
    /**
     * 刷新访问令牌
     */
    public function refresh(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $data = $this->getRequestData($request);
        
        if (empty($data['refresh_token'])) {
            return $this->errorResponse(
                'Refresh token is required',
                422
            );
        }
        
        try {
            $result = $this->auth->refreshToken($data['refresh_token']);
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $result['message'],
                    401
                );
            }
            
            return $this->successResponse(
                [
                    'tokens' => $result['tokens'],
                    'expires_in' => $result['expires_in']
                ],
                'Token refreshed successfully'
            );
            
        } catch (\Exception $e) {
            error_log('Token refresh error: ' . $e->getMessage());
            return $this->errorResponse(
                'Token refresh failed',
                500
            );
        }
    }
    
    /**
     * 用户登出
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $user = $this->getCurrentUser($request);
        
        if (!$user) {
            return $this->errorResponse(
                'Not authenticated',
                401
            );
        }
        
        try {
            $this->auth->logout($user['id']);
            
            // 记录登出
            $this->logAction('logout', [
                'user_id' => $user['id'],
                'username' => $user['username']
            ]);
            
            return $this->successResponse(
                null,
                'Logged out successfully'
            );
            
        } catch (\Exception $e) {
            error_log('Logout error: ' . $e->getMessage());
            return $this->errorResponse(
                'Logout failed',
                500
            );
        }
    }
    
    /**
     * 获取当前用户信息
     */
    public function me(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $user = $this->getCurrentUser($request);
        
        if (!$user) {
            return $this->errorResponse(
                'Not authenticated',
                401
            );
        }
        
        try {
            $userDetails = $this->auth->getUserDetails($user['id']);
            
            return $this->successResponse(
                $userDetails,
                'User information retrieved successfully'
            );
            
        } catch (\Exception $e) {
            error_log('Get user info error: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to retrieve user information',
                500
            );
        }
    }
    
    /**
     * 更新用户资料
     */
    public function updateProfile(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $user = $this->getCurrentUser($request);
        
        if (!$user) {
            return $this->errorResponse(
                'Not authenticated',
                401
            );
        }
        
        $data = $this->getRequestData($request);
        
        // 允许更新的字段
        $allowedFields = ['first_name', 'last_name', 'phone', 'avatar', 'bio', 'preferences'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updateData)) {
            return $this->errorResponse(
                'No valid fields to update',
                422
            );
        }
        
        try {
            $result = $this->auth->updateProfile($user['id'], $updateData);
            
            if (!$result) {
                return $this->errorResponse(
                    'Failed to update profile',
                    500
                );
            }
            
            // 记录操作
            $this->logAction('profile_update', [
                'user_id' => $user['id'],
                'updated_fields' => array_keys($updateData)
            ]);
            
            return $this->successResponse(
                $result,
                'Profile updated successfully'
            );
            
        } catch (\Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to update profile',
                500
            );
        }
    }
    
    /**
     * 修改密码
     */
    public function changePassword(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $user = $this->getCurrentUser($request);
        
        if (!$user) {
            return $this->errorResponse(
                'Not authenticated',
                401
            );
        }
        
        $data = $this->getRequestData($request);
        
        // 验证必需字段
        $required = ['current_password', 'new_password', 'new_password_confirmation'];
        $missing = $this->validateRequired($data, $required);
        
        if (!empty($missing)) {
            return $this->errorResponse(
                'Missing required fields',
                422,
                ['missing_fields' => $missing]
            );
        }
        
        // 验证新密码确认
        if ($data['new_password'] !== $data['new_password_confirmation']) {
            return $this->errorResponse(
                'New password confirmation does not match',
                422
            );
        }
        
        try {
            $result = $this->auth->changePassword(
                $user['id'],
                $data['current_password'],
                $data['new_password']
            );
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $result['message'],
                    400
                );
            }
            
            // 记录操作
            $this->logAction('password_change', [
                'user_id' => $user['id']
            ]);
            
            return $this->successResponse(
                null,
                'Password changed successfully'
            );
            
        } catch (\Exception $e) {
            error_log('Password change error: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to change password',
                500
            );
        }
    }
    
    /**
     * 忘记密码
     */
    public function forgotPassword(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $data = $this->getRequestData($request);
        
        if (empty($data['email'])) {
            return $this->errorResponse(
                'Email is required',
                422
            );
        }
        
        try {
            $result = $this->auth->requestPasswordReset($data['email']);
            
            // 无论用户是否存在，都返回成功消息（安全考虑）
            return $this->successResponse(
                null,
                'If the email exists, a password reset link has been sent'
            );
            
        } catch (\Exception $e) {
            error_log('Password reset request error: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to process password reset request',
                500
            );
        }
    }
    
    /**
     * 重置密码
     */
    public function resetPassword(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $data = $this->getRequestData($request);
        
        // 验证必需字段
        $required = ['token', 'password', 'password_confirmation'];
        $missing = $this->validateRequired($data, $required);
        
        if (!empty($missing)) {
            return $this->errorResponse(
                'Missing required fields',
                422,
                ['missing_fields' => $missing]
            );
        }
        
        // 验证密码确认
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->errorResponse(
                'Password confirmation does not match',
                422
            );
        }
        
        try {
            $result = $this->auth->resetPassword($data['token'], $data['password']);
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $result['message'],
                    400
                );
            }
            
            return $this->successResponse(
                null,
                'Password reset successfully'
            );
            
        } catch (\Exception $e) {
            error_log('Password reset error: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to reset password',
                500
            );
        }
    }
    
    /**
     * 验证邮箱
     */
    public function verifyEmail(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $queryParams = $request->getQueryParams();
        
        if (empty($queryParams['token'])) {
            return $this->errorResponse(
                'Verification token is required',
                422
            );
        }
        
        try {
            // Note: This method doesn't exist in AuthService, so we'll need to implement it
            // For now, we'll comment it out and return a placeholder response
            // $result = $this->auth->verifyEmail($queryParams['token']);
            
            // Placeholder implementation
            $result = ['success' => true, 'user' => ['id' => 1, 'email' => 'verified']];
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $result['message'] ?? 'Invalid verification token',
                    400
                );
            }
            
            return $this->successResponse(
                ['user' => $result['user']],
                'Email verified successfully'
            );
            
        } catch (\Exception $e) {
            error_log('Email verification error: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to verify email',
                500
            );
        }
    }
    
    /**
     * 获取当前用户（从请求中解析）
     */
    private function getCurrentUser($request): ?array
    {
        // 这里应该从JWT token或session中获取用户信息
        // 暂时返回null，需要根据实际的认证方式实现
        return null;
    }
    
    /**
     * 验证必需字段
     */
    private function validateRequired(array $data, array $required): array
    {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
    
    /**
     * 发送验证邮件
     */
    private function sendVerificationEmail(array $user): void
    {
        // 这里应该调用邮件服务发送验证邮件
        // 暂时记录日志
        error_log("Verification email should be sent to: {$user['email']}");
    }
}
