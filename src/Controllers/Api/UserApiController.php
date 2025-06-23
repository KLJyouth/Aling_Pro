<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\UserService;
use AlingAi\Core\Middleware\AuthMiddleware; // 用于获取用户ID
use Throwable;

/**
 * 用户API控制器 - 已重构
 * 处理用户个人资料等相关请求
 */
class UserApiController extends BaseApiController
{
    private $userService;

    // 通过构造函数注入UserService
    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * 获取当前认证用户的个人资料
     */
    public function getProfile(Request $request, Response $response): Response
    {
        try {
            // 从请求属性中获取由AuthMiddleware设置的用户ID
            $userId = $request->getAttribute(AuthMiddleware::USER_ID_ATTRIBUTE);
            if (!$userId) {
                return $this->sendErrorResponse('认证失败或用户ID缺失', 401);
            }

            $profile = $this->userService->getProfile((int)$userId);
            
            return $this->sendSuccess($response, $profile);

        } catch (Throwable $e) {
            // 如果UserService抛出"用户不存在"等异常
            return $this->sendErrorResponse('获取个人资料失败: ' . $e->getMessage(), 404);
        }
    }

    /**
     * 更新当前认证用户的个人资料
     */
    public function updateProfile(Request $request, Response $response): Response
    {
        try {
            $userId = $request->getAttribute(AuthMiddleware::USER_ID_ATTRIBUTE);
            if (!$userId) {
                return $this->sendErrorResponse('认证失败或用户ID缺失', 401);
            }

            $data = $this->getRequestData($request);

            $success = $this->userService->updateProfile((int)$userId, $data);

            if ($success) {
                // 同时返回更新后的资料
                $updatedProfile = $this->userService->getProfile((int)$userId);
                return $this->sendSuccess($response, $updatedProfile, '个人资料更新成功');
            } else {
                return $this->sendErrorResponse('个人资料更新失败，可能没有提供任何有效字段', 400);
            }

        } catch (Throwable $e) {
            // 如果UserService抛出"用户名已存在"等异常
            return $this->sendErrorResponse('个人资料更新失败: ' . $e->getMessage(), 400);
        }
    }
}
