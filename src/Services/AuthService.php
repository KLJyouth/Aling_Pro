<?php

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Services\UserService;
use AlingAi\Core\Security\Security;
use AlingAi\Core\Logging\ApiLogger;
use InvalidArgumentException;

class AuthService
{
    private $userService;
    private $security;
    private $logger;

    public function __construct(UserService $userService, Security $security, ApiLogger $logger)
    {
        $this->userService = $userService;
        $this->security = $security;
        $this->logger = $logger;
    }

    /**
     * 处理用户登录
     *
     * @param string $email
     * @param string $password
     * @return array An array containing tokens and user data.
     * @throws InvalidArgumentException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userService->findUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->logger->warning('登录失败：无效的凭证', ['email' => $email]);
            throw new InvalidArgumentException('无效的凭证');
        }

        if ($user['status'] !== 'active') {
            $this->logger->warning('登录失败：账户未激活', ['email' => $email, 'status' => $user['status']]);
            throw new InvalidArgumentException('账户未激活或被禁用');
        }

        // 生成令牌
        $tokenData = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        $token = $this->security->generateJwtToken($tokenData);
        $refreshToken = $this->security->generateRefreshToken((string)$user['id']);

        // 记录登录
        $this->userService->recordLogin((int)$user['id']);
        $this->logger->info('用户成功登录', ['user_id' => $user['id'], 'email' => $email]);
        
        return [
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ];
    }

    /**
     * 处理用户注册
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return array
     * @throws InvalidArgumentException
     */
    public function register(string $username, string $email, string $password): array
    {
        if ($this->userService->findUserByEmail($email)) {
            throw new InvalidArgumentException('该邮箱已被注册');
        }

        if (!$this->security->validatePasswordStrength($password)) {
            throw new InvalidArgumentException('密码强度不足');
        }

        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        $userId = $this->userService->createUser($username, $email, $hashedPassword);

        if (!$userId) {
            throw new \RuntimeException('创建用户时发生未知错误');
        }
        
        $this->logger->info('新用户注册成功', ['user_id' => $userId, 'email' => $email]);
        
        // @todo 在这里添加发送验证邮件的逻辑

        return [
            'user_id' => $userId,
            'message' => '注册成功，请检查您的邮箱以完成验证。',
        ];
    }
    
    /**
     * 根据用户ID获取用户信息
     *
     * @param int $userId 用户ID
     * @return array 用户信息
     * @throws InvalidArgumentException 如果用户不存在
     */
    public function getUserById(int $userId): array
    {
        $user = $this->userService->getUserById($userId);
        
        if (!$user) {
            $this->logger->warning('获取用户信息失败：用户不存在', ['user_id' => $userId]);
            throw new InvalidArgumentException("用户不存在");
        }
        
        // 移除敏感信息
        if (isset($user['password'])) {
            unset($user['password']);
        }
        
        return $user;
    }
    
    /**
     * 刷新访问令牌
     *
     * @param string $refreshToken 刷新令牌
     * @return array 新的令牌信息
     * @throws InvalidArgumentException 如果刷新令牌无效
     */
    public function refreshToken(string $refreshToken): array
    {
        $userId = $this->security->validateRefreshToken($refreshToken);
        
        if (!$userId) {
            $this->logger->warning('刷新令牌无效');
            throw new InvalidArgumentException('无效或过期的刷新令牌');
        }
        
        $user = $this->getUserById((int)$userId);
        
        // 生成新令牌
        $tokenData = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        
        $newToken = $this->security->generateJwtToken($tokenData);
        $newRefreshToken = $this->security->generateRefreshToken($userId);
        
        $this->logger->info('成功刷新令牌', ['user_id' => $userId]);
        
        return [
            'token' => $newToken,
            'refresh_token' => $newRefreshToken,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ];
    }
    
    /**
     * 处理忘记密码请求
     *
     * @param string $email 用户邮箱
     * @return void
     */
    public function forgotPassword(string $email): void
    {
        $user = $this->userService->findUserByEmail($email);
        
        // 即使用户不存在也不返回错误，防止用户枚举
        if (!$user) {
            $this->logger->info('请求密码重置：邮箱不存在', ['email' => $email]);
            return;
        }
        
        // 生成重置令牌
        $resetToken = $this->security->generatePasswordResetToken((string)$user['id']);
        
        // 存储重置令牌（通常会存储到数据库中）
        $this->userService->savePasswordResetToken($user['id'], $resetToken);
        
        // @todo 发送包含重置链接的邮件
        
        $this->logger->info('已发送密码重置链接', ['user_id' => $user['id'], 'email' => $email]);
    }
    
    /**
     * 处理密码重置
     *
     * @param string $token 重置令牌
     * @param string $password 新密码
     * @throws InvalidArgumentException 如果令牌无效或密码不符合要求
     */
    public function resetPassword(string $token, string $password): void
    {
        // 验证令牌
        $userId = $this->security->validatePasswordResetToken($token);
        
        if (!$userId) {
            $this->logger->warning('密码重置失败：令牌无效');
            throw new InvalidArgumentException('无效或过期的重置令牌');
        }
        
        // 验证密码强度
        if (!$this->security->validatePasswordStrength($password)) {
            throw new InvalidArgumentException('密码强度不足');
        }
        
        // 更新密码
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        $this->userService->updatePassword((int)$userId, $hashedPassword);
        
        // 移除重置令牌
        $this->userService->clearPasswordResetToken((int)$userId);
        
        $this->logger->info('密码重置成功', ['user_id' => $userId]);
    }
    
    /**
     * 用户退出登录
     *
     * @param int $userId 用户ID
     */
    public function logout(int $userId): void
    {
        // 这里可以实现令牌黑名单等退出逻辑
        $this->logger->info('用户退出登录', ['user_id' => $userId]);
    }
}
