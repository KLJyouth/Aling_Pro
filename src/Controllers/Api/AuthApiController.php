<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\AuthService;
use InvalidArgumentException;
use Throwable;

/**
 * 认证API控制器
 * 
 * 处理用户登录、注册、密码重置等认证相关的API请求
 * 
 * @package AlingAi\Controllers\Api
 * @version 1.1.0
 * @since 2024-12-19
 */
class AuthApiController extends BaseApiController
{
    private AuthService $authService;

    // 通过构造函数注入AuthService和Logger
    public function __construct(AuthService $authService, ?\Psr\Log\LoggerInterface $logger = null)
    {
        parent::__construct($logger); // 调用父类构造函数
        $this->authService = $authService;
    }

    /**
     * 测试端点
     */
    public function test(Request $request, Response $response): Response
    {
        return $this->sendSuccess($response, [
            'message' => 'Auth API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.1.0'
        ]);
    }

    /**
     * 用户登录
     */
    public function login(Request $request, Response $response): Response
    {
        try {
            $data = $this->getRequestData($request);
            
            $result = $this->authService->login(
                $data['email'] ?? '',
                $data['password'] ?? ''
            );
            
            return $this->sendSuccess($response, $result);

        } catch (InvalidArgumentException $e) {
            $this->sendErrorResponse($e->getMessage(), 401); // 401 for invalid credentials
            return $response->withStatus(401);
        } catch (Throwable $e) {
            $this->logApiError($request, 'login', $e);
            $this->sendErrorResponse('登录时发生未知错误', 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 用户注册
     */
    public function register(Request $request, Response $response): Response
    {
        try {
            $data = $this->getRequestData($request);

            // 简单的验证
            $this->validateRequiredParams($data, ['username', 'email', 'password']);
            
            $result = $this->authService->register(
                $data['username'],
                $data['email'],
                $data['password']
            );

            return $this->sendSuccess($response, $result, '注册成功');

        } catch (InvalidArgumentException $e) {
            $this->sendErrorResponse($e->getMessage(), 400); // 400 for bad request
            return $response->withStatus(400);
        } catch (Throwable $e) {
            $this->logApiError($request, 'register', $e);
            $this->sendErrorResponse('注册时发生未知错误', 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 刷新令牌
     */
    public function refresh(Request $request, Response $response): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequiredParams($data, ['refresh_token']);

            $result = $this->authService->refreshToken($data['refresh_token']);
            
            return $this->sendSuccess($response, $result, '令牌刷新成功');
            
        } catch (InvalidArgumentException $e) {
            $this->sendErrorResponse($e->getMessage(), 401);
            return $response->withStatus(401);
        } catch (Throwable $e) {
            $this->logApiError($request, 'refresh_token', $e);
            $this->sendErrorResponse('令牌刷新失败', 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 获取当前用户信息
     */
    public function getUser(Request $request, Response $response): Response
    {
        try {
            $userId = $this->validateAuth($request);
            
            $userData = $this->authService->getUserById($userId);
            
            return $this->sendSuccess($response, $userData, '获取用户信息成功');
            
        } catch (InvalidArgumentException $e) {
            $this->sendErrorResponse($e->getMessage(), 404); // User not found
            return $response->withStatus(404);
        } catch (Throwable $e) {
             $this->logApiError($request, 'get_user', $e);
            $this->sendErrorResponse('获取用户信息失败', 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 忘记密码
     */
    public function forgotPassword(Request $request, Response $response): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequiredParams($data, ['email']);
            
            $this->authService->forgotPassword($data['email']);
            
            $message = '如果邮箱存在，密码重置链接已发送';
            return $this->sendSuccess($response, ['message' => $message], $message);
            
        } catch (InvalidArgumentException $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
            return $response->withStatus(400);
        } catch (Throwable $e) {
            $this->logApiError($request, 'forgot_password', $e);
            $this->sendErrorResponse('请求处理失败', 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 重置密码
     */
    public function resetPassword(Request $request, Response $response): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequiredParams($data, ['token', 'password']);

            $this->authService->resetPassword(
                $data['token'],
                $data['password']
            );

            return $this->sendSuccess($response, null, '密码重置成功');

        } catch (InvalidArgumentException $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
            return $response->withStatus(400);
        } catch (Throwable $e) {
            $this->logApiError($request, 'reset_password', $e);
            $this->sendErrorResponse('密码重置失败', 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 退出登录
     */
    public function logout(Request $request, Response $response): Response
    {
        try {
            $userId = $this->validateAuth($request);
            $this->authService->logout($userId);
            return $this->sendSuccess($response, null, '退出成功');
        } catch (Throwable $e) {
            $this->logApiError($request, 'logout', $e);
            $this->sendErrorResponse('退出失败', 500);
            return $response->withStatus(500);
        }
    }
    
    /**
     * 验证必需的参数
     *
     * @param array $data 请求数据
     * @param array $params 必需的参数列表
     * @throws InvalidArgumentException 如果缺少必需参数
     */
    protected function validateRequiredParams(array $data, array $params): void
    {
        $missing = [];
        
        foreach ($params as $param) {
            if (!isset($data[$param]) || (is_string($data[$param]) && trim($data[$param]) === '')) {
                $missing[] = $param;
            }
        }
        
        if (!empty($missing)) {
            throw new InvalidArgumentException('缺少必需参数: ' . implode(', ', $missing));
        }
    }
    
    /**
     * 验证用户认证状态
     *
     * @param Request $request 请求对象
     * @return int 用户ID
     * @throws InvalidArgumentException 如果认证失败
     */
    protected function validateAuth(Request $request): int
    {
        // 从请求中获取令牌
        $token = $this->getBearerToken() ?? $request->getQueryParams()['token'] ?? null;
        
        if (!$token) {
            throw new InvalidArgumentException('缺少认证令牌');
        }
        
        // 验证令牌
        $validation = $this->security->validateJwtToken($token);
        
        if (!$validation || !isset($validation['user_id'])) {
            throw new InvalidArgumentException('无效的认证令牌');
        }
        
        return (int)$validation['user_id'];
    }
}
