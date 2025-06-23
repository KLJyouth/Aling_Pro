<?php
/**
 * 认证中间件
 * 
 * @package AlingAi\Middleware
 */

declare(strict_types=1);

namespace AlingAi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use AlingAi\Services\AuthService;
use Monolog\Logger;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private AuthService $authService;
    private Logger $logger;
    private string $role;
    
    public function __construct(AuthService $authService, Logger $logger, string $role = 'user')
    {
        $this->authService = $authService;
        $this->logger = $logger;
        $this->role = $role;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 从请求头获取认证令牌
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $this->unauthorizedResponse('Authentication token required');
        }
        
        // 验证令牌
        $payload = $this->authService->verifyToken($token);
        
        if (!$payload) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }
        
        // 检查角色权限
        if ($this->role === 'admin' && $payload['role'] !== 'admin') {
            return $this->forbiddenResponse('Admin access required');
        }
        
        // 将用户信息添加到请求属性中
        $request = $request->withAttribute('user', $payload['user']);
        $request = $request->withAttribute('user_id', $payload['user_id']);
        $request = $request->withAttribute('user_role', $payload['role']);
        $request = $request->withAttribute('token_payload', $payload);
        
        $this->logger->debug('User authenticated successfully', [
            'user_id' => $payload['user_id'],
            'role' => $payload['role'],
            'route' => $request->getUri()->getPath()
        ]);
        
        return $handler->handle($request);
    }
    
    private function extractToken(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        
        if (empty($header)) {
            // 尝试从查询参数获取令牌
            $queryParams = $request->getQueryParams();
            return $queryParams['token'] ?? null;
        }
        
        // Bearer token格式: "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    private function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message,
            'code' => 'UNAUTHORIZED',
            'timestamp' => date('c')
        ]));
        
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
    
    private function forbiddenResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message,
            'code' => 'FORBIDDEN',
            'timestamp' => date('c')
        ]));
        
        return $response
            ->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * 创建中间件实例的工厂方法
     */
    public static function create(string $role = 'user'): callable
    {
        return function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($role) {
            // 从容器获取服务
            $container = $GLOBALS['app']->getContainer();
            $authService = $container->get(AuthService::class);
            $logger = $container->get(Logger::class);
            
            $middleware = new self($authService, $logger, $role);
            return $middleware->process($request, $handler);
        };
    }
}
