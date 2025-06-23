<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use Exception;

/**
 * 简化的用户API控制器
 */
class UserApiController extends BaseApiController
{
    private $userService;
    private $validationService;

    public function __construct()
    {
        parent::__construct();
        // 延迟初始化服务，避免构造函数错误
    }

    /**
     * 测试端点
     */
    public function test(): array
    {
        return $this->sendSuccessResponse([
            'message' => 'User API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }

    /**
     * 获取用户资料
     */
    public function getProfile(): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('Unauthorized', 401);
            }

            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendErrorResponse('User not found', 404);
            }

            // 移除敏感信息
            unset($user['password']);

            return $this->sendSuccessResponse($user, 'Profile retrieved successfully');

        } catch (Exception $e) {
            return $this->sendErrorResponse('Failed to get profile: ' . $e->getMessage(), 500);
        }
    }
}
