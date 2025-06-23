<?php

declare(strict_types=1);

namespace AlingAi\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use AlingAi\Services\SecurityService;
use AlingAi\Services\UserService;

/**
 * Auth middleware - handles authentication for API routes
 */
class AuthMiddleware implements MiddlewareInterface
{
    // 用户ID属性名称，用于在请求中存储已验证的用户ID
    public const USER_ID_ATTRIBUTE = 'userId';

    private $securityService;
    private $userService;

    /**
     * Constructor
     *
     * @param SecurityService $securityService
     * @param UserService $userService
     */
    public function __construct(SecurityService $securityService, UserService $userService)
    {
        $this->securityService = $securityService;
        $this->userService = $userService;
    }

    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 获取认证令牌
        $token = $this->extractToken($request);

        // 如果没有提供令牌，则继续处理请求（将由控制器检查认证情况）
        if (empty($token)) {
            return $handler->handle($request);
        }

        // 验证令牌并获取用户ID
        $validation = $this->securityService->validateJwtToken($token);

        if ($validation && isset($validation['user_id'])) {
            // 将用户ID添加到请求属性中
            $request = $request->withAttribute(self::USER_ID_ATTRIBUTE, $validation['user_id']);

            // 可能还需要加载用户信息
            if (isset($validation['load_user']) && $validation['load_user']) {
                $user = $this->userService->getUserById($validation['user_id']);
                if ($user) {
                    $request = $request->withAttribute('user', $user);
                }
            }
        }

        // 继续处理请求
        return $handler->handle($request);
    }

    /**
     * Extract token from request
     *
     * @param ServerRequestInterface $request
     * @return string|null
     */
    private function extractToken(ServerRequestInterface $request): ?string
    {
        // 从 Authorization 头部提取令牌
        $authHeader = $request->getHeaderLine('Authorization');
        if (strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }

        // 如果没有在头部中找到，则从查询参数中查找
        $params = $request->getQueryParams();
        if (isset($params['token'])) {
            return $params['token'];
        }

        // 如果也没有在查询参数中找到，则从 Cookie 中查找
        $cookies = $request->getCookieParams();
        if (isset($cookies['token'])) {
            return $cookies['token'];
        }

        return null;
    }
}
