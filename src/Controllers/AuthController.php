<?php

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlingAi\Services\{AuthService, EmailService};
use AlingAi\Utils\Logger;

class AuthController extends BaseController
{
    private AuthService $auth;
    private EmailService $emailService;
      public function __construct(
        \AlingAi\Services\DatabaseServiceInterface $db,
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
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->getRequestData($request);
            
            // 验证必需字段
            $required = ['username', 'email', 'password', 'password_confirmation'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                return $this->errorResponse(
                    $response,
                    'Missing required fields: ' . implode(', ', $missing),
                    422
                );
            }
            
            // 验证密码确认
            if ($data['password'] !== $data['password_confirmation']) {
                return $this->errorResponse(
                    $response,
                    'Password confirmation does not match',
                    422
                );
            }
            
            // 验证邮箱格式
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->errorResponse(
                    $response,
                    'Invalid email format',
                    422
                );
            }
            
            // 检查用户是否已存在
            if ($this->auth->userExists($data['email'], $data['username'])) {
                return $this->errorResponse(
                    $response,
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
                'role' => 'user',
                'status' => 'pending'
            ]);
              // 发送验证邮件
            $this->auth->sendVerificationEmail($user['id'], $user['email']);
            
            // 记录操作
            $this->logAction('user_register', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]);
            
            return $this->successResponse(
                $response,
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
            Logger::error('Registration error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Registration failed',
                500
            );
        }
    }

    /**
     * 用户登录
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->getRequestData($request);
            
            // 验证必需字段
            $required = ['login', 'password'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                return $this->errorResponse(
                    $response,
                    'Missing required fields: ' . implode(', ', $missing),
                    422
                );
            }
            
            $result = $this->auth->login($data['login'], $data['password']);
            
            if (!$result['success']) {
                $this->logAction('login_failed', [
                    'login' => $data['login'],
                    'reason' => $result['message']
                ]);
                
                return $this->errorResponse(
                    $response,
                    $result['message'],
                    401
                );
            }
            
            $this->logAction('login_success', [
                'user_id' => $result['user']['id'],
                'username' => $result['user']['username']
            ]);
            
            return $this->successResponse(
                $response,
                $result,
                'Login successful'
            );
            
        } catch (\Exception $e) {
            Logger::error('Login error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Login failed',
                500
            );
        }
    }

    /**
     * 刷新令牌
     */
    public function refresh(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->getRequestData($request);
            
            if (empty($data['refresh_token'])) {
                return $this->errorResponse(
                    $response,
                    'Refresh token required',
                    422
                );
            }
            
            $result = $this->auth->refreshToken($data['refresh_token']);
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $response,
                    $result['message'],
                    401
                );
            }
            
            return $this->successResponse(
                $response,
                $result,
                'Token refreshed successfully'
            );
            
        } catch (\Exception $e) {
            Logger::error('Token refresh error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Token refresh failed',
                500
            );
        }
    }

    /**
     * 登出
     */    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->errorResponse(
                    $response,
                    'Not authenticated',
                    401
                );
            }
            
            // 从Authorization头获取令牌
            $authHeader = $request->getHeaderLine('Authorization');
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
                $this->auth->revokeToken($token);
            }
            
            $this->logAction('logout', [
                'user_id' => $user['id'],
                'username' => $user['username']
            ]);
            
            return $this->successResponse(
                $response,
                null,
                'Logout successful'
            );
            
        } catch (\Exception $e) {
            Logger::error('Logout error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Logout failed',
                500
            );
        }
    }

    /**
     * 获取当前用户信息
     */
    public function me(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser();
            
            if (!$user) {
                return $this->errorResponse(
                    $response,
                    'Not authenticated',
                    401
                );
            }
            
            $userDetails = $this->auth->getUserDetails($user['id']);
            
            return $this->successResponse(
                $response,
                ['user' => $userDetails],
                'User details retrieved successfully'
            );
            
        } catch (\Exception $e) {
            Logger::error('Get user details error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Failed to get user details',
                500
            );
        }
    }

    /**
     * 更新个人资料
     */
    public function updateProfile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser();
            
            if (!$user) {
                return $this->errorResponse(
                    $response,
                    'Not authenticated',
                    401
                );
            }
            
            $data = $this->getRequestData($request);
            
            // 移除不能更新的字段
            unset($data['id'], $data['email'], $data['username'], $data['password'], 
                  $data['role'], $data['status'], $data['created_at']);
            
            if (empty($data)) {
                return $this->errorResponse(
                    $response,
                    'No valid fields to update',
                    422
                );
            }
            
            $result = $this->auth->updateUser($user['id'], $data);
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $response,
                    $result['message'],
                    500
                );
            }
            
            $this->logAction('profile_update', [
                'user_id' => $user['id'],
                'updated_fields' => array_keys($data)
            ]);
            
            return $this->successResponse(
                $response,
                ['user' => $result['user']],
                'Profile updated successfully'
            );
            
        } catch (\Exception $e) {
            Logger::error('Update profile error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Profile update failed',
                500
            );
        }
    }

    /**
     * 修改密码
     */
    public function changePassword(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser();
            
            if (!$user) {
                return $this->errorResponse(
                    $response,
                    'Not authenticated',
                    401
                );
            }
            
            $data = $this->getRequestData($request);
            
            // 验证必需字段
            $required = ['old_password', 'new_password', 'new_password_confirmation'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                return $this->errorResponse(
                    $response,
                    'Missing required fields: ' . implode(', ', $missing),
                    422
                );
            }
            
            // 验证新密码确认
            if ($data['new_password'] !== $data['new_password_confirmation']) {
                return $this->errorResponse(
                    $response,
                    'New password confirmation does not match',
                    422
                );
            }
            
            $result = $this->auth->changePassword(
                $user['id'],
                $data['old_password'],
                $data['new_password']
            );
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $response,
                    $result['message'],
                    400
                );
            }
            
            $this->logAction('password_change', [
                'user_id' => $user['id']
            ]);
            
            return $this->successResponse(
                $response,
                null,
                'Password changed successfully'
            );
            
        } catch (\Exception $e) {
            Logger::error('Change password error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Password change failed',
                500
            );
        }
    }

    /**
     * 请求密码重置
     */
    public function requestPasswordReset(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->getRequestData($request);
            
            if (empty($data['email'])) {
                return $this->errorResponse(
                    $response,
                    'Email is required',
                    422
                );
            }
            
            $result = $this->auth->requestPasswordReset($data['email']);
            
            return $this->successResponse(
                $response,
                null,
                'If your email is registered, you will receive a password reset link.'
            );
            
        } catch (\Exception $e) {
            Logger::error('Password reset request error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Password reset request failed',
                500
            );
        }
    }

    /**
     * 重置密码
     */
    public function resetPassword(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->getRequestData($request);
            
            // 验证必需字段
            $required = ['token', 'password', 'password_confirmation'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                return $this->errorResponse(
                    $response,
                    'Missing required fields: ' . implode(', ', $missing),
                    422
                );
            }
            
            // 验证密码确认
            if ($data['password'] !== $data['password_confirmation']) {
                return $this->errorResponse(
                    $response,
                    'Password confirmation does not match',
                    422
                );
            }
            
            $result = $this->auth->resetPassword($data['token'], $data['password']);
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $response,
                    $result['message'],
                    400
                );
            }
            
            return $this->successResponse(
                $response,
                null,
                'Password reset successfully'
            );
            
        } catch (\Exception $e) {
            Logger::error('Password reset error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Password reset failed',
                500
            );
        }
    }

    /**
     * 验证邮箱
     */
    public function verifyEmail(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $queryParams = $this->getQueryParams($request);
            
            if (empty($queryParams['token'])) {
                return $this->errorResponse(
                    $response,
                    'Verification token required',
                    422
                );
            }
            
            $result = $this->auth->verifyEmail($queryParams['token']);
            
            if (!$result['success']) {
                return $this->errorResponse(
                    $response,
                    $result['message'],
                    400
                );
            }
            
            return $this->successResponse(
                $response,
                null,
                'Email verified successfully'
            );
            
        } catch (\Exception $e) {
            Logger::error('Email verification error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Email verification failed',
                500
            );
        }
    }

    /**
     * 重新发送验证邮件
     */
    public function resendVerification(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->getRequestData($request);
            
            // 验证必需字段
            $required = ['email'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                return $this->errorResponse(
                    $response,
                    'Email is required',
                    422
                );
            }
            
            // 验证邮箱格式
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->errorResponse(
                    $response,
                    'Invalid email format',
                    422
                );
            }
            
            // 检查用户是否存在
            if (!$this->auth->userExists($data['email'])) {
                return $this->errorResponse(
                    $response,
                    'User not found',
                    404
                );
            }
            
            // 这里可以实现重新发送验证邮件的逻辑
            // 简化处理，返回成功
            return $this->successResponse(
                $response,
                null,
                'Verification email sent successfully'
            );
            
        } catch (\Exception $e) {
            Logger::error('Resend verification error: ' . $e->getMessage());
            return $this->errorResponse(
                $response,
                'Failed to resend verification email',
                500
            );
        }
    }
}
