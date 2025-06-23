<?php
namespace AlingAi\Security\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use AlingAi\Services\EnhancedAuthService;

/**
 * 认证中间件
 * 
 * 验证API请求的身份认证
 * 
 * @package AlingAi\Security\Middleware
 * @version 6.0.0
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    private EnhancedAuthService $authService;
    private LoggerInterface $logger;
    private array $config;
    
    /**
     * 构造函数
     */
    public function __construct(EnhancedAuthService $authService, LoggerInterface $logger)
    {
        $this->authService = $authService;
        $this->logger = $logger;
        $this->config = $this->loadConfig();
    }
    
    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        return [
            "public_paths" => explode(",", $_ENV["AUTH_PUBLIC_PATHS"] ?? "/api/v1/system/info,/api/health,/api/auth/login,/api/auth/register"),
            "token_header" => $_ENV["AUTH_TOKEN_HEADER"] ?? "Authorization",
            "token_prefix" => $_ENV["AUTH_TOKEN_PREFIX"] ?? "Bearer",
            "session_header" => $_ENV["AUTH_SESSION_HEADER"] ?? "X-Session-ID",
        ];
    }
    
    /**
     * 处理请求
     * 
     * @param ServerRequestInterface $request 请求
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface 响应
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 检查是否为公开路径
        $path = $request->getUri()->getPath();
        if ($this->isPublicPath($path)) {
            return $handler->handle($request);
        }
        
        // 尝试从请求中提取身份验证信息
        $authResult = $this->authenticate($request);
        
        // 如果认证失败，返回401响应
        if (!$authResult["authenticated"]) {
            return $this->createErrorResponse(
                401,
                "Unauthorized",
                $authResult["message"] ?? "身份验证失败"
            );
        }
        
        // 将用户信息添加到请求属性中
        $request = $request->withAttribute("user", $authResult["user"]);
        $request = $request->withAttribute("token", $authResult["token"] ?? null);
        $request = $request->withAttribute("session", $authResult["session"] ?? null);
        
        // 处理请求
        return $handler->handle($request);
    }
    
    /**
     * 检查是否为公开路径
     * 
     * @param string $path 请求路径
     * @return bool 是否公开
     */
    private function isPublicPath(string $path): bool
    {
        foreach ($this->config["public_paths"] as $publicPath) {
            if (strpos($path, trim($publicPath)) === 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 认证请求
     * 
     * @param ServerRequestInterface $request 请求
     * @return array 认证结果
     */
    private function authenticate(ServerRequestInterface $request): array
    {
        // 尝试从会话认证
        $sessionResult = $this->authenticateBySession($request);
        if ($sessionResult["authenticated"]) {
            return $sessionResult;
        }
        
        // 尝试从令牌认证
        $tokenResult = $this->authenticateByToken($request);
        if ($tokenResult["authenticated"]) {
            return $tokenResult;
        }
        
        // 认证失败
        return [
            "authenticated" => false,
            "message" => "未提供有效的身份验证信息"
        ];
    }
    
    /**
     * 通过会话认证
     * 
     * @param ServerRequestInterface $request 请求
     * @return array 认证结果
     */
    private function authenticateBySession(ServerRequestInterface $request): array
    {
        // 从请求头获取会话ID
        $sessionId = $request->getHeaderLine($this->config["session_header"]);
        
        if (empty($sessionId)) {
            return ["authenticated" => false];
        }
        
        try {
            // 获取客户端信息
            $ip = $request->getServerParams()["REMOTE_ADDR"] ?? "";
            $userAgent = $request->getHeaderLine("User-Agent");
            
            // 验证会话
            $session = $this->authService->validateSession($sessionId, $ip, $userAgent);
            
            if (!$session) {
                $this->logger->warning("会话验证失败", [
                    "session_id" => $sessionId,
                    "ip" => $ip
                ]);
                
                return [
                    "authenticated" => false,
                    "message" => "无效或过期的会话"
                ];
            }
            
            // 获取用户信息
            $user = $this->authService->getUserDetails($session["user_id"]);
            
            if (!$user) {
                return [
                    "authenticated" => false,
                    "message" => "找不到会话关联的用户"
                ];
            }
            
            return [
                "authenticated" => true,
                "user" => $user,
                "session" => $session
            ];
            
        } catch (\Exception $e) {
            $this->logger->error("会话认证异常", [
                "error" => $e->getMessage(),
                "session_id" => $sessionId
            ]);
            
            return [
                "authenticated" => false,
                "message" => "会话验证失败"
            ];
        }
    }
    
    /**
     * 通过令牌认证
     * 
     * @param ServerRequestInterface $request 请求
     * @return array 认证结果
     */
    private function authenticateByToken(ServerRequestInterface $request): array
    {
        // 从请求头获取令牌
        $authHeader = $request->getHeaderLine($this->config["token_header"]);
        
        if (empty($authHeader)) {
            return ["authenticated" => false];
        }
        
        // 提取令牌
        $prefix = $this->config["token_prefix"] . " ";
        if (strpos($authHeader, $prefix) !== 0) {
            return [
                "authenticated" => false,
                "message" => "无效的令牌格式"
            ];
        }
        
        $token = substr($authHeader, strlen($prefix));
        
        try {
            // 验证令牌
            $tokenData = $this->authService->validateToken($token);
            
            if (!$tokenData) {
                $this->logger->warning("令牌验证失败", [
                    "token" => substr($token, 0, 10) . "..."
                ]);
                
                return [
                    "authenticated" => false,
                    "message" => "无效或过期的令牌"
                ];
            }
            
            // 获取用户信息
            $user = $this->authService->getUserDetails($tokenData["user_id"]);
            
            if (!$user) {
                return [
                    "authenticated" => false,
                    "message" => "找不到令牌关联的用户"
                ];
            }
            
            return [
                "authenticated" => true,
                "user" => $user,
                "token" => $tokenData
            ];
            
        } catch (\Exception $e) {
            $this->logger->error("令牌认证异常", [
                "error" => $e->getMessage()
            ]);
            
            return [
                "authenticated" => false,
                "message" => "令牌验证失败"
            ];
        }
    }
    
    /**
     * 创建错误响应
     * 
     * @param int $status 状态码
     * @param string $error 错误类型
     * @param string $message 错误消息
     * @return ResponseInterface 响应
     */
    private function createErrorResponse(int $status, string $error, string $message): ResponseInterface
    {
        $response = new \GuzzleHttp\Psr7\Response(
            $status,
            ["Content-Type" => "application/json"],
            json_encode([
                "success" => false,
                "error" => $error,
                "message" => $message
            ])
        );
        
        return $response;
    }
}
