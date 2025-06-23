<?php
namespace AlingAi\Security\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use AlingAi\Security\EncryptionService;
use AlingAi\Security\Exceptions\EncryptionException;

/**
 * API加密中间件
 * 
 * 处理API请求和响应的加密和解密
 * 
 * @package AlingAi\Security\Middleware
 * @version 6.0.0
 */
class ApiEncryptionMiddleware implements MiddlewareInterface
{
    private EncryptionService $encryption;
    private LoggerInterface $logger;
    private array $config;
    
    /**
     * 构造函数
     */
    public function __construct(EncryptionService $encryption, LoggerInterface $logger)
    {
        $this->encryption = $encryption;
        $this->logger = $logger;
        $this->config = $this->loadConfig();
    }
    
    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        return [
            "enabled" => (bool)($_ENV["API_ENCRYPTION_ENABLED"] ?? true),
            "excluded_paths" => explode(",", $_ENV["API_ENCRYPTION_EXCLUDED_PATHS"] ?? "/api/v1/system/info,/api/health"),
            "algorithm" => $_ENV["API_ENCRYPTION_ALGORITHM"] ?? "AES-256-CBC",
            "debug_mode" => (bool)($_ENV["API_ENCRYPTION_DEBUG"] ?? false),
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
        // 如果加密功能被禁用，直接传递请求
        if (!$this->config["enabled"]) {
            return $handler->handle($request);
        }
        
        // 检查是否为排除路径
        $path = $request->getUri()->getPath();
        if ($this->isExcludedPath($path)) {
            return $handler->handle($request);
        }
        
        // 处理加密的请求数据
        $request = $this->decryptRequest($request);
        
        // 处理响应
        $response = $handler->handle($request);
        
        // 加密响应数据
        return $this->encryptResponse($response);
    }
    
    /**
     * 检查是否为排除路径
     * 
     * @param string $path 请求路径
     * @return bool 是否排除
     */
    private function isExcludedPath(string $path): bool
    {
        foreach ($this->config["excluded_paths"] as $excludedPath) {
            if (strpos($path, trim($excludedPath)) === 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 解密请求
     * 
     * @param ServerRequestInterface $request 请求
     * @return ServerRequestInterface 解密后的请求
     */
    private function decryptRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $contentType = $request->getHeaderLine("Content-Type");
        
        // 只处理JSON请求
        if (strpos($contentType, "application/json") === false) {
            return $request;
        }
        
        $body = $request->getBody();
        $contents = $body->getContents();
        $body->rewind();
        
        if (empty($contents)) {
            return $request;
        }
        
        try {
            $data = json_decode($contents, true);
            
            if (!$data || !isset($data["encrypted"]) || $data["encrypted"] !== true) {
                return $request;
            }
            
            // 解密数据
            $decrypted = $this->encryption->decryptApiData($data);
            
            // 创建新的请求体
            $stream = fopen("php://temp", "r+");
            fwrite($stream, json_encode($decrypted));
            rewind($stream);
            
            return $request->withBody(new \GuzzleHttp\Psr7\Stream($stream));
            
        } catch (EncryptionException $e) {
            $this->logger->error("API请求解密失败", [
                "error" => $e->getMessage(),
                "path" => $request->getUri()->getPath()
            ]);
            
            // 如果解密失败，返回原始请求
            return $request;
        }
    }
    
    /**
     * 加密响应
     * 
     * @param ResponseInterface $response 响应
     * @return ResponseInterface 加密后的响应
     */
    private function encryptResponse(ResponseInterface $response): ResponseInterface
    {
        $contentType = $response->getHeaderLine("Content-Type");
        
        // 只处理JSON响应
        if (strpos($contentType, "application/json") === false) {
            return $response;
        }
        
        $body = $response->getBody();
        $contents = $body->getContents();
        $body->rewind();
        
        if (empty($contents)) {
            return $response;
        }
        
        try {
            $data = json_decode($contents, true);
            
            if (!$data) {
                return $response;
            }
            
            // 加密数据
            $encrypted = $this->encryption->encryptApiData($data);
            
            // 创建新的响应体
            $stream = fopen("php://temp", "r+");
            fwrite($stream, json_encode($encrypted));
            rewind($stream);
            
            return $response->withBody(new \GuzzleHttp\Psr7\Stream($stream));
            
        } catch (EncryptionException $e) {
            $this->logger->error("API响应加密失败", [
                "error" => $e->getMessage()
            ]);
            
            // 如果加密失败，返回原始响应
            return $response;
        }
    }
}
