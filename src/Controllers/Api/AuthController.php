<?php
/**
 * 用户认证API控制器
 * 处理登录、注册、密码重置等认证相关功能
 */

namespace AlingAi\Controllers\Api;

use AlingAi\Core\Database;
use AlingAi\Core\Response;
use AlingAi\Core\Validator;
use AlingAi\Models\User;
use AlingAi\Services\AuthService;
use AlingAi\Services\EmailService;
use Exception;
use PDO;

class AuthController
{
    private $db;
    private $authService;
    private $emailService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->authService = new AuthService();
        $this->emailService = new EmailService();
    }

    /**
     * 用户登录
     */
    public function login()
    {
        try {
            $input = $this->getJsonInput();
            
            // 验证输入
            $validator = new Validator($input, [
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);

            if (!$validator->validate()) {
                return Response::error('输入验证失败', $validator->getErrors(), 422);
            }

            $email = $input['email'];
            $password = $input['password'];

            // 查找用户
            $user = User::findByEmail($email);
            if (!$user || !password_verify($password, $user['password_hash'])) {
                return Response::error('邮箱或密码错误');
            }

            // 检查用户状态
            if ($user['status'] !== 'active') {
                return Response::error('账户已被停用，请联系管理员');
            }

            // 生成JWT令牌
            $token = $this->authService->generateToken($user);

            // 更新最后登录时间
            User::updateLastLogin($user['id']);

            // 记录登录日志
            $this->logUserActivity($user['id'], 'login', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

            return Response::success('登录成功', [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'uuid' => $user['uuid'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'avatar_url' => $user['avatar_url'],
                    'role' => $user['role']
                ]
            ]);

        } catch (Exception $e) {
            error_log("登录API错误: " . $e->getMessage());
            return Response::error('登录失败，请稍后重试');
        }
    }

    /**
     * 用户注册
     */
    public function register()
    {
        try {
            $input = $this->getJsonInput();
            
            // 验证输入
            $validator = new Validator($input, [
                'username' => 'required|min:3|max:50',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'first_name' => 'required|max:50',
                'last_name' => 'required|max:50'
            ]);

            if (!$validator->validate()) {
                return Response::error('输入验证失败', $validator->getErrors(), 422);
            }

            // 检查用户名和邮箱是否已存在
            if (User::findByUsername($input['username'])) {
                return Response::error('用户名已存在');
            }

            if (User::findByEmail($input['email'])) {
                return Response::error('邮箱已被注册');
            }

            // 创建用户
            $userData = [
                'uuid' => $this->generateUuid(),
                'username' => $input['username'],
                'email' => $input['email'],
                'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'role' => 'user',
                'status' => 'active'
            ];

            $userId = User::create($userData);
            
            if (!$userId) {
                return Response::error('注册失败，请稍后重试');
            }

            // 发送欢迎邮件
            $this->emailService->sendWelcomeEmail($input['email'], $input['first_name']);

            // 记录注册日志
            $this->logUserActivity($userId, 'register', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

            return Response::success('注册成功', [
                'message' => '账户创建成功，欢迎加入珑凌科技！'
            ]);

        } catch (Exception $e) {
            error_log("注册API错误: " . $e->getMessage());
            return Response::error('注册失败，请稍后重试');
        }
    }

    /**
     * 密码重置请求
     */
    public function requestPasswordReset()
    {
        try {
            $input = $this->getJsonInput();
            
            // 验证输入
            $validator = new Validator($input, [
                'email' => 'required|email'
            ]);

            if (!$validator->validate()) {
                return Response::error('请输入有效的邮箱地址', $validator->getErrors(), 422);
            }

            $email = $input['email'];
            $user = User::findByEmail($email);

            // 为了安全，无论用户是否存在都返回成功消息
            if ($user) {
                // 生成重置令牌
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // 存储重置令牌
                $this->storePasswordResetToken($user['id'], $token, $expiresAt);

                // 发送重置邮件
                $this->emailService->sendPasswordResetEmail($email, $token, $user['first_name']);
            }

            return Response::success('密码重置邮件已发送，请检查您的邮箱');

        } catch (Exception $e) {
            error_log("密码重置请求API错误: " . $e->getMessage());
            return Response::error('请求失败，请稍后重试');
        }
    }

    /**
     * 重置密码
     */
    public function resetPassword()
    {
        try {
            $input = $this->getJsonInput();
            
            // 验证输入
            $validator = new Validator($input, [
                'token' => 'required',
                'password' => 'required|min:8'
            ]);

            if (!$validator->validate()) {
                return Response::error('输入验证失败', $validator->getErrors(), 422);
            }

            $token = $input['token'];
            $newPassword = $input['password'];

            // 验证重置令牌
            $resetRecord = $this->getPasswordResetToken($token);
            if (!$resetRecord || $resetRecord['expires_at'] < date('Y-m-d H:i:s')) {
                return Response::error('重置令牌无效或已过期');
            }

            // 更新密码
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            User::updatePassword($resetRecord['user_id'], $passwordHash);

            // 删除重置令牌
            $this->deletePasswordResetToken($token);

            // 记录密码重置日志
            $this->logUserActivity($resetRecord['user_id'], 'password_reset', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);

            return Response::success('密码重置成功，请使用新密码登录');

        } catch (Exception $e) {
            error_log("密码重置API错误: " . $e->getMessage());
            return Response::error('密码重置失败，请稍后重试');
        }
    }

    /**
     * 获取当前用户信息
     */
    public function me()
    {
        try {
            $user = $this->authService->getCurrentUser();
            if (!$user) {
                return Response::error('用户未认证', null, 401);
            }

            return Response::success('获取用户信息成功', [
                'user' => [
                    'id' => $user['id'],
                    'uuid' => $user['uuid'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'avatar_url' => $user['avatar_url'],
                    'role' => $user['role'],
                    'created_at' => $user['created_at']
                ]
            ]);

        } catch (Exception $e) {
            error_log("获取用户信息API错误: " . $e->getMessage());
            return Response::error('获取用户信息失败');
        }
    }

    /**
     * 用户登出
     */
    public function logout()
    {
        try {
            $user = $this->authService->getCurrentUser();
            if ($user) {
                // 记录登出日志
                $this->logUserActivity($user['id'], 'logout', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]);
            }

            return Response::success('登出成功');

        } catch (Exception $e) {
            error_log("登出API错误: " . $e->getMessage());
            return Response::error('登出失败');
        }
    }

    // 私有辅助方法

    private function getJsonInput()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('无效的JSON数据');
        }
        return $input ?: [];
    }

    private function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private function storePasswordResetToken($userId, $token, $expiresAt)
    {
        $stmt = $this->db->prepare("
            INSERT INTO password_resets (user_id, token, expires_at, created_at) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            token = VALUES(token), 
            expires_at = VALUES(expires_at), 
            created_at = NOW()
        ");
        $stmt->execute([$userId, $token, $expiresAt]);
    }

    private function getPasswordResetToken($token)
    {
        $stmt = $this->db->prepare("
            SELECT user_id, expires_at 
            FROM password_resets 
            WHERE token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function deletePasswordResetToken($token)
    {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
    }

    private function logUserActivity($userId, $action, $data = [])
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_logs (user_id, action, data, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $action,
                json_encode($data),
                $data['ip'] ?? '',
                $data['user_agent'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("记录用户活动失败: " . $e->getMessage());
        }
    }
}
