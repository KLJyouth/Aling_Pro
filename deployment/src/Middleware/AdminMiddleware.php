<?php

namespace AlingAi\Middleware;

use AlingAi\Services\AuthService;
use AlingAi\Middleware\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * 管理员权限中间件
 * 验证用户是否具有管理员权限
 */
class AdminMiddleware implements MiddlewareInterface
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 获取用户信息（由认证中间件添加）
        // 假设 request 实现了 ServerRequestInterface，提供 getAttribute 方法
        $user = null;
        if ($request instanceof ServerRequestInterface) {
            $user = $request->getAttribute('user');
        }
        
        if (!$user) {
            return $this->createForbiddenResponse('用户认证信息缺失');
        }

        // 检查管理员权限
        if (!$this->isAdmin($user)) {
            return $this->createForbiddenResponse('需要管理员权限');
        }

        return $handler->handle($request);
    }

    /**
     * 检查用户是否为管理员
     */
    private function isAdmin(array $user): bool
    {
        return isset($user['role']) && $user['role'] === 'admin';
    }

    /**
     * 创建禁止访问响应
     */
    private function createForbiddenResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message,
            'code' => 403
        ]));
        
        return $response
            ->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }
}
