<?php

namespace AlingAi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;

/**
 * JSON响应中间件
 * 
 * 统一处理API响应的JSON格式化和错误处理
 * 优化性能：响应缓存、压缩、状态码优化
 * 增强安全性：响应头安全设置、数据过滤
 */
class JsonResponseMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    private array $config;
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'pretty_print' => false,
            'include_timestamp' => true,
            'include_request_id' => true,
            'error_details' => false,
            'max_response_size' => 1048576, // 1MB
            'compress' => true,
            'cache_headers' => true
        ], $config);
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        $requestId = uniqid('req_', true);
        
        try {
            // 处理请求
            $response = $handler->handle($request);
            
            // 格式化JSON响应
            $response = $this->formatJsonResponse($response, $requestId, $startTime);
            
            // 添加响应头
            $response = $this->addResponseHeaders($response, $request);
            
            // 记录响应日志
            $this->logResponse($request, $response, $requestId, $startTime);
            
            return $response;
            
        } catch (\Throwable $e) {
            // 处理异常并返回JSON错误响应
            return $this->handleException($e, $requestId, $startTime);
        }
    }
    
    /**
     * 格式化JSON响应
     */
    private function formatJsonResponse(ResponseInterface $response, string $requestId, float $startTime): ResponseInterface
    {
        $contentType = $response->getHeaderLine('Content-Type');
        
        // 如果不是JSON响应，直接返回
        if (strpos($contentType, 'application/json') === false) {
            return $response;
        }
        
        $body = (string) $response->getBody();
        
        // 如果响应体为空，返回标准格式
        if (empty($body)) {
            $data = $this->createStandardResponse($response, $requestId, $startTime);
            $response->getBody()->write($this->encodeJson($data));
            return $response;
        }
        
        // 解析现有JSON
        $data = json_decode($body, true);
        
        // 如果解析失败，返回错误响应
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('JSON解析失败', [
                'error' => json_last_error_msg(),
                'body' => substr($body, 0, 1000)
            ]);
            
            $data = $this->createErrorResponse('JSON格式错误', 500, $requestId, $startTime);
            $response->getBody()->write($this->encodeJson($data));
            return $response->withStatus(500);
        }
        
        // 标准化响应格式
        $data = $this->standardizeResponse($data, $response, $requestId, $startTime);
        
        // 检查响应大小
        $encodedData = $this->encodeJson($data);
        if (strlen($encodedData) > $this->config['max_response_size']) {
            $this->logger->warning('响应大小超出限制', [
                'size' => strlen($encodedData),
                'limit' => $this->config['max_response_size']
            ]);
            
            $data = $this->createErrorResponse('响应数据过大', 413, $requestId, $startTime);
            $encodedData = $this->encodeJson($data);
            $response = $response->withStatus(413);
        }
        
        $response->getBody()->write($encodedData);
        return $response;
    }
    
    /**
     * 创建标准响应格式
     */
    private function createStandardResponse(ResponseInterface $response, string $requestId, float $startTime): array
    {
        $statusCode = $response->getStatusCode();
        $isSuccess = $statusCode >= 200 && $statusCode < 300;
        
        $data = [
            'success' => $isSuccess,
            'status_code' => $statusCode,
            'message' => $this->getStatusMessage($statusCode)
        ];
        
        if ($this->config['include_timestamp']) {
            $data['timestamp'] = date('c');
        }
        
        if ($this->config['include_request_id']) {
            $data['request_id'] = $requestId;
        }
        
        $data['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
        
        return $data;
    }
    
    /**
     * 标准化响应数据
     */
    private function standardizeResponse(array $data, ResponseInterface $response, string $requestId, float $startTime): array
    {
        $statusCode = $response->getStatusCode();
        $isSuccess = $statusCode >= 200 && $statusCode < 300;
        
        // 如果已经是标准格式，直接返回
        if (isset($data['success']) && isset($data['status_code'])) {
            return $data;
        }
        
        $standardized = [
            'success' => $isSuccess,
            'status_code' => $statusCode,
            'data' => $data
        ];
        
        if ($this->config['include_timestamp']) {
            $standardized['timestamp'] = date('c');
        }
        
        if ($this->config['include_request_id']) {
            $standardized['request_id'] = $requestId;
        }
        
        $standardized['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
        
        return $standardized;
    }
    
    /**
     * 创建错误响应
     */
    private function createErrorResponse(string $message, int $statusCode, string $requestId, float $startTime): array
    {
        $data = [
            'success' => false,
            'error' => $message,
            'status_code' => $statusCode
        ];
        
        if ($this->config['include_timestamp']) {
            $data['timestamp'] = date('c');
        }
        
        if ($this->config['include_request_id']) {
            $data['request_id'] = $requestId;
        }
        
        $data['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
        
        return $data;
    }
    
    /**
     * 处理异常
     */
    private function handleException(\Throwable $e, string $requestId, float $startTime): ResponseInterface
    {
        $this->logger->error('请求处理异常', [
            'request_id' => $requestId,
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        $statusCode = $e->getCode() ?: 500;
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }
        
        $data = $this->createErrorResponse($e->getMessage(), $statusCode, $requestId, $startTime);
        
        if ($this->config['error_details'] && $statusCode >= 500) {
            $data['details'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }
        
        $response = new Response();
        $response->getBody()->write($this->encodeJson($data));
        
        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
    
    /**
     * 添加响应头
     */
    private function addResponseHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        // 设置JSON内容类型
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        
        // 添加缓存头
        if ($this->config['cache_headers']) {
            $response = $response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response = $response->withHeader('Pragma', 'no-cache');
            $response = $response->withHeader('Expires', '0');
        }
        
        // 添加安全头
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
        $response = $response->withHeader('X-Frame-Options', 'DENY');
        $response = $response->withHeader('X-XSS-Protection', '1; mode=block');
        
        // 添加CORS头（如果需要）
        $origin = $request->getHeaderLine('Origin');
        if (!empty($origin)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }
        
        return $response;
    }
    
    /**
     * 编码JSON
     */
    private function encodeJson(array $data): string
    {
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        
        if ($this->config['pretty_print']) {
            $options |= JSON_PRETTY_PRINT;
        }
        
        $json = json_encode($data, $options);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('JSON编码失败', [
                'error' => json_last_error_msg(),
                'data' => $data
            ]);
            
            // 返回错误响应
            return json_encode([
                'success' => false,
                'error' => 'JSON编码失败',
                'status_code' => 500
            ], JSON_UNESCAPED_UNICODE);
        }
        
        return $json;
    }
    
    /**
     * 获取状态码对应的消息
     */
    private function getStatusMessage(int $statusCode): string
    {
        $messages = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable'
        ];
        
        return $messages[$statusCode] ?? 'Unknown Status';
    }
    
    /**
     * 记录响应日志
     */
    private function logResponse(ServerRequestInterface $request, ResponseInterface $response, string $requestId, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $statusCode = $response->getStatusCode();
        
        $logData = [
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'content_length' => $response->getHeaderLine('Content-Length') ?: 0
        ];
        
        $logLevel = $statusCode >= 500 ? 'error' : ($statusCode >= 400 ? 'warning' : 'info');
        $this->logger->log($logLevel, 'JSON Response', $logData);
    }
}
