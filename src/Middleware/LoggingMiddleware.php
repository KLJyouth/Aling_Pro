<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    private array $sensitiveFields;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'access_token',
            'refresh_token',
            'api_key',
            'secret',
            'private_key'
        ];
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        $requestId = uniqid('req_', true);
        
        // 记录请求信息
        $this->logRequest($request, $requestId);
        
        try {
            $response = $handler->handle($request);
            
            // 记录响应信息
            $this->logResponse($request, $response, $requestId, $startTime);
            
            return $response;
        } catch (\Throwable $e) {
            // 记录异常
            $this->logException($request, $e, $requestId, $startTime);
            throw $e;
        }
    }
    
    private function logRequest(ServerRequestInterface $request, string $requestId): void
    {
        $uri = $request->getUri();
        $method = $request->getMethod();
        $userAgent = $request->getHeaderLine('User-Agent');
        $clientIp = $this->getClientIp($request);
        
        $body = (string) $request->getBody();
        $parsedBody = $request->getParsedBody();
        
        // 过滤敏感信息
        $filteredBody = $this->filterSensitiveData($parsedBody ?: $body);
        
        $logData = [
            'request_id' => $requestId,
            'method' => $method,
            'uri' => (string) $uri,
            'client_ip' => $clientIp,
            'user_agent' => $userAgent,
            'headers' => $this->filterHeaders($request->getHeaders()),
            'body' => $filteredBody,
            'query_params' => $request->getQueryParams(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logger->info('HTTP Request', $logData);
    }
    
    private function logResponse(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $requestId,
        float $startTime
    ): void {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $statusCode = $response->getStatusCode();
        $contentLength = $response->getHeaderLine('Content-Length') ?: 0;
        
        $logData = [
            'request_id' => $requestId,
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'content_length' => $contentLength,
            'headers' => $response->getHeaders(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $logLevel = $statusCode >= 500 ? 'error' : ($statusCode >= 400 ? 'warning' : 'info');
        $this->logger->log($logLevel, 'HTTP Response', $logData);
    }
    
    private function logException(
        ServerRequestInterface $request,
        \Throwable $exception,
        string $requestId,
        float $startTime
    ): void {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $logData = [
            'request_id' => $requestId,
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'duration_ms' => $duration,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logger->error('HTTP Exception', $logData);
    }
    
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        // 检查常见的代理头
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // 代理
            'HTTP_X_FORWARDED_FOR',      // 负载均衡器
            'HTTP_X_FORWARDED',          // 代理
            'HTTP_X_CLUSTER_CLIENT_IP',  // 集群
            'HTTP_FORWARDED_FOR',        // 代理
            'HTTP_FORWARDED',            // RFC 7239
            'REMOTE_ADDR'                // 标准
        ];
        
        foreach ($headers as $header) {
            if (!empty($serverParams[$header])) {
                $ip = $serverParams[$header];
                // 处理多个IP的情况（逗号分隔）
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }
    
    private function filterSensitiveData($data): array|string
    {
        if (is_string($data)) {
            return strlen($data) > 1000 ? substr($data, 0, 1000) . '...' : $data;
        }
        
        if (!is_array($data)) {
            return $data;
        }
        
        $filtered = [];
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $this->sensitiveFields)) {
                $filtered[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $filtered[$key] = $this->filterSensitiveData($value);
            } else {
                $filtered[$key] = $value;
            }
        }
        
        return $filtered;
    }
    
    private function filterHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
            'x-access-token'
        ];
        
        $filtered = [];
        foreach ($headers as $name => $values) {
            if (in_array(strtolower($name), $sensitiveHeaders)) {
                $filtered[$name] = ['[REDACTED]'];
            } else {
                $filtered[$name] = $values;
            }
        }
        
        return $filtered;
    }
}
