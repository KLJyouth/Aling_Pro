<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use AlingAi\Services\SecurityService;
use AlingAi\Services\FileUserService;
use Exception;
use InvalidArgumentException;

/**
 * 用户资料API控制器
 * 
 * 处理用户资料更新、头像上传、偏好设置等功能
 * 
 * @package AlingAi\Controllers\Api
 * @version 1.0.0
 */
class UserProfileApiController extends BaseApiController
{
    private FileUserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new FileUserService();
    }

    /**
     * 获取用户资料
     */
    public function getProfile(): array
    {
        try {
            if (!$this->requireAuth()) {
                return [];
            }

            $currentUser = $this->getCurrentUser();
            $user = $this->userService->getUserById($currentUser['user_id']);

            if (!$user) {
                return $this->sendErrorResponse('User not found', 404);
            }

            // 移除敏感信息
            unset($user['password'], $user['reset_token'], $user['verification_token']);

            return $this->sendSuccessResponse([
                'user' => $user
            ]);

        } catch (Exception $e) {
            $this->monitor->logError('Get profile failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser['user_id'] ?? 'unknown'
            ]);
            return $this->sendErrorResponse('Failed to get profile', 500);
        }
    }

    /**
     * 更新用户资料
     */
    public function updateProfile(): array
    {
        try {
            if (!$this->requireAuth()) {
                return [];
            }

            $currentUser = $this->getCurrentUser();
            $data = $this->getRequestData();

            // 验证输入数据
            $validated = $this->validateRequestData($data, [
                'username' => ['required' => false, 'min_length' => 3, 'max_length' => 50],
                'full_name' => ['required' => false, 'max_length' => 100],
                'bio' => ['required' => false, 'max_length' => 500],
                'location' => ['required' => false, 'max_length' => 100],
                'website' => ['required' => false, 'type' => 'url', 'max_length' => 255],
                'phone' => ['required' => false, 'max_length' => 20],
                'birthday' => ['required' => false, 'type' => 'date']
            ]);

            $user = $this->userService->getUserById($currentUser['user_id']);
            if (!$user) {
                return $this->sendErrorResponse('User not found', 404);
            }

            // 检查用户名是否已被其他用户使用
            if (isset($validated['username']) && $validated['username'] !== $user['username']) {
                $existingUser = $this->userService->getUserByUsername($validated['username']);
                if ($existingUser && $existingUser['id'] !== $user['id']) {
                    return $this->sendErrorResponse('Username already taken', 400);
                }
            }

            // 更新用户资料
            $updateData = array_merge($user, $validated);
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $result = $this->userService->updateUser($user['id'], $updateData);

            if ($result) {
                $this->monitor->logUserActivity($user['id'], 'profile_updated', [
                    'fields' => array_keys($validated)
                ]);

                // 获取更新后的用户信息
                $updatedUser = $this->userService->getUserById($user['id']);
                unset($updatedUser['password'], $updatedUser['reset_token'], $updatedUser['verification_token']);

                return $this->sendSuccessResponse([
                    'message' => 'Profile updated successfully',
                    'user' => $updatedUser
                ]);
            } else {
                return $this->sendErrorResponse('Failed to update profile', 500);
            }

        } catch (InvalidArgumentException $e) {
            return $this->sendErrorResponse('Validation failed', 400, json_decode($e->getMessage(), true));
        } catch (Exception $e) {
            $this->monitor->logError('Update profile failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser['user_id'] ?? 'unknown'
            ]);
            return $this->sendErrorResponse('Failed to update profile', 500);
        }
    }

    /**
     * 更改密码
     */
    public function changePassword(): array
    {
        try {
            if (!$this->requireAuth()) {
                return [];
            }

            $currentUser = $this->getCurrentUser();
            $data = $this->getRequestData();

            // 验证输入数据
            $validated = $this->validateRequestData($data, [
                'current_password' => ['required' => true],
                'new_password' => ['required' => true, 'min_length' => 8],
                'confirm_password' => ['required' => true]
            ]);

            if ($validated['new_password'] !== $validated['confirm_password']) {
                return $this->sendErrorResponse('Password confirmation does not match', 400);
            }

            if (!$this->security->validatePasswordStrength($validated['new_password'])) {
                return $this->sendErrorResponse('Password does not meet security requirements', 400);
            }

            $user = $this->userService->getUserById($currentUser['user_id']);
            if (!$user) {
                return $this->sendErrorResponse('User not found', 404);
            }

            // 验证当前密码
            if (!password_verify($validated['current_password'], $user['password'])) {
                return $this->sendErrorResponse('Current password is incorrect', 400);
            }

            // 更新密码
            $hashedPassword = password_hash($validated['new_password'], PASSWORD_ARGON2ID);
            $result = $this->userService->updateUser($user['id'], [
                'password' => $hashedPassword,
                'password_updated_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                $this->monitor->logUserActivity($user['id'], 'password_changed');

                return $this->sendSuccessResponse([
                    'message' => 'Password changed successfully'
                ]);
            } else {
                return $this->sendErrorResponse('Failed to change password', 500);
            }

        } catch (InvalidArgumentException $e) {
            return $this->sendErrorResponse('Validation failed', 400, json_decode($e->getMessage(), true));
        } catch (Exception $e) {
            $this->monitor->logError('Change password failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser['user_id'] ?? 'unknown'
            ]);
            return $this->sendErrorResponse('Failed to change password', 500);
        }
    }

    /**
     * 上传头像
     */
    public function uploadAvatar(): array
    {
        try {
            if (!$this->requireAuth()) {
                return [];
            }

            $currentUser = $this->getCurrentUser();

            if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                return $this->sendErrorResponse('No file uploaded or upload error', 400);
            }

            $file = $_FILES['avatar'];
            
            // 验证文件类型
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                return $this->sendErrorResponse('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed', 400);
            }

            // 验证文件大小 (最大2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                return $this->sendErrorResponse('File too large. Maximum size is 2MB', 400);
            }

            // 创建上传目录
            $uploadDir = __DIR__ . '/../../../public/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // 生成唯一文件名
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $currentUser['user_id'] . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // 移动上传的文件
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // 更新用户头像路径
                $avatarUrl = '/uploads/avatars/' . $filename;
                $result = $this->userService->updateUser($currentUser['user_id'], [
                    'avatar' => $avatarUrl,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if ($result) {
                    $this->monitor->logUserActivity($currentUser['user_id'], 'avatar_uploaded', [
                        'filename' => $filename,
                        'size' => $file['size']
                    ]);

                    return $this->sendSuccessResponse([
                        'message' => 'Avatar uploaded successfully',
                        'avatar_url' => $avatarUrl
                    ]);
                } else {
                    // 如果数据库更新失败，删除已上传的文件
                    unlink($filepath);
                    return $this->sendErrorResponse('Failed to update avatar', 500);
                }
            } else {
                return $this->sendErrorResponse('Failed to upload file', 500);
            }

        } catch (Exception $e) {
            $this->monitor->logError('Avatar upload failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser['user_id'] ?? 'unknown'
            ]);
            return $this->sendErrorResponse('Failed to upload avatar', 500);
        }
    }

    /**
     * 更新用户偏好设置
     */
    public function updatePreferences(): array
    {
        try {
            if (!$this->requireAuth()) {
                return [];
            }

            $currentUser = $this->getCurrentUser();
            $data = $this->getRequestData();

            // 验证偏好设置
            $validated = $this->validateRequestData($data, [
                'language' => ['required' => false, 'in' => ['zh-CN', 'en-US', 'ja-JP']],
                'theme' => ['required' => false, 'in' => ['light', 'dark', 'auto']],
                'timezone' => ['required' => false, 'max_length' => 50],
                'email_notifications' => ['required' => false, 'type' => 'boolean'],
                'push_notifications' => ['required' => false, 'type' => 'boolean'],
                'marketing_emails' => ['required' => false, 'type' => 'boolean']
            ]);

            $user = $this->userService->getUserById($currentUser['user_id']);
            if (!$user) {
                return $this->sendErrorResponse('User not found', 404);
            }

            // 合并现有偏好设置
            $currentPreferences = json_decode($user['preferences'] ?? '{}', true);
            $newPreferences = array_merge($currentPreferences, $validated);

            $result = $this->userService->updateUser($user['id'], [
                'preferences' => json_encode($newPreferences),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                $this->monitor->logUserActivity($user['id'], 'preferences_updated', [
                    'updated_fields' => array_keys($validated)
                ]);

                return $this->sendSuccessResponse([
                    'message' => 'Preferences updated successfully',
                    'preferences' => $newPreferences
                ]);
            } else {
                return $this->sendErrorResponse('Failed to update preferences', 500);
            }

        } catch (InvalidArgumentException $e) {
            return $this->sendErrorResponse('Validation failed', 400, json_decode($e->getMessage(), true));
        } catch (Exception $e) {
            $this->monitor->logError('Update preferences failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser['user_id'] ?? 'unknown'
            ]);
            return $this->sendErrorResponse('Failed to update preferences', 500);
        }
    }

    /**
     * 删除用户账户
     */
    public function deleteAccount(): array
    {
        try {
            if (!$this->requireAuth()) {
                return [];
            }

            $currentUser = $this->getCurrentUser();
            $data = $this->getRequestData();

            // 验证删除确认
            $validated = $this->validateRequestData($data, [
                'password' => ['required' => true],
                'confirm_deletion' => ['required' => true, 'equals' => 'DELETE_MY_ACCOUNT']
            ]);

            $user = $this->userService->getUserById($currentUser['user_id']);
            if (!$user) {
                return $this->sendErrorResponse('User not found', 404);
            }

            // 验证密码
            if (!password_verify($validated['password'], $user['password'])) {
                return $this->sendErrorResponse('Password is incorrect', 400);
            }

            // 软删除用户（标记为已删除，而不是物理删除）
            $result = $this->userService->updateUser($user['id'], [
                'status' => 'deleted',
                'deleted_at' => date('Y-m-d H:i:s'),
                'email' => $user['email'] . '_deleted_' . time(), // 避免邮箱冲突
                'username' => $user['username'] . '_deleted_' . time()
            ]);

            if ($result) {
                $this->monitor->logUserActivity($user['id'], 'account_deleted');

                return $this->sendSuccessResponse([
                    'message' => 'Account has been deleted successfully'
                ]);
            } else {
                return $this->sendErrorResponse('Failed to delete account', 500);
            }

        } catch (InvalidArgumentException $e) {
            return $this->sendErrorResponse('Validation failed', 400, json_decode($e->getMessage(), true));
        } catch (Exception $e) {
            $this->monitor->logError('Account deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser['user_id'] ?? 'unknown'
            ]);
            return $this->sendErrorResponse('Failed to delete account', 500);
        }
    }

    /**
     * 获取用户活动日志
     */
    public function getActivityLog(): array
    {
        try {
            if (!$this->requireAuth()) {
                return [];
            }

            $currentUser = $this->getCurrentUser();
            
            // 这里应该从日志系统获取用户活动记录
            // 由于我们使用的是文件系统，这里返回模拟数据
            $activities = [
                [
                    'id' => 1,
                    'action' => 'login',
                    'description' => '用户登录',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];

            return $this->sendSuccessResponse([
                'activities' => $activities,
                'total' => count($activities)
            ]);

        } catch (Exception $e) {
            $this->monitor->logError('Get activity log failed', [
                'error' => $e->getMessage(),
                'user_id' => $currentUser['user_id'] ?? 'unknown'
            ]);
            return $this->sendErrorResponse('Failed to get activity log', 500);
        }
    }
}
