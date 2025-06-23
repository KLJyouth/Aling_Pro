<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Services\CacheService;

class RateLimitMiddleware implements MiddlewareInterface
{
    private CacheService $cache;
    private array $config;
    
    public function __construct(CacheService $cache, array $config = [])
    {
        $this->cache = $cache;
        $this->config = array_merge([
            'max_requests' => 100,      // 最大请求数
            'window_seconds' => 3600,   // 时间窗口（秒）
            'block_duration' => 3600,   // 阻止时长（秒）
            'whitelist' => [],          // IP白名单
            'blacklist' => [],          // IP黑名单
            'skip_successful' => false, // 是否跳过成功请求的计数
            'headers' => true,          // 是否添加限流头信息
            'by_user' => true,          // 是否按用户限流
            'by_ip' => true,            // 是否按IP限流
        ], $config);
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIp = $this->getClientIp($request);
        $userId = $this->getUserId($request);
        
        // 检查黑名单
        if (in_array($clientIp, $this->config['blacklist'])) {
            return $this->createBlockedResponse('IP blocked');
        }
        
        // 检查白名单
        if (in_array($clientIp, $this->config['whitelist'])) {
            return $handler->handle($request);
        }
        
        // 检查限流
        $limitResults = $this->checkLimits($clientIp, $userId);
        
        foreach ($limitResults as $result) {
            if ($result['blocked']) {
                return $this->createBlockedResponse($result['reason'], $result);
            }
        }
        
        // 处理请求
        $response = $handler->handle($request);
        
        // 更新计数器（仅在非成功状态码或配置要求时）
        $statusCode = $response->getStatusCode();
        if (!$this->config['skip_successful'] || $statusCode >= 400) {
            $this->updateCounters($clientIp, $userId, $statusCode);
        }
        
        // 添加限流头信息
        if ($this->config['headers']) {
            $response = $this->addRateLimitHeaders($response, $limitResults);
        }
        
        return $response;
    }
    
    private function checkLimits(string $clientIp, ?string $userId): array
    {
        $results = [];
        
        // IP限流检查
        if ($this->config['by_ip']) {
            $results['ip'] = $this->checkLimit('ip', $clientIp);
        }
        
        // 用户限流检查
        if ($this->config['by_user'] && $userId) {
            $results['user'] = $this->checkLimit('user', $userId);
        }
        
        return $results;
    }
    
    private function checkLimit(string $type, string $identifier): array
    {
        $key = "rate_limit:{$type}:{$identifier}";
        $blockKey = "rate_limit_block:{$type}:{$identifier}";
        
        // 检查是否已被阻止
        if ($this->cache->has($blockKey)) {
            return [
                'blocked' => true,
                'reason' => "Rate limit exceeded for {$type}",
                'reset_time' => $this->cache->get($blockKey),
                'remaining' => 0
            ];
        }
        
        // 获取当前计数
        $current = (int) $this->cache->get($key, 0);
        $maxRequests = $this->getMaxRequests($type, $identifier);
        
        if ($current >= $maxRequests) {
            // 超出限制，设置阻止时间
            $resetTime = time() + $this->config['block_duration'];
            $this->cache->set($blockKey, $resetTime, $this->config['block_duration']);
            
            return [
                'blocked' => true,
                'reason' => "Rate limit exceeded for {$type}",
                'reset_time' => $resetTime,
                'remaining' => 0
            ];
        }
        
        return [
            'blocked' => false,
            'current' => $current,
            'limit' => $maxRequests,
            'remaining' => $maxRequests - $current,
            'reset_time' => $this->getResetTime($key)
        ];
    }
    
    private function updateCounters(string $clientIp, ?string $userId, int $statusCode): void
    {
        // 更新IP计数器
        if ($this->config['by_ip']) {
            $this->incrementCounter('ip', $clientIp);
        }
        
        // 更新用户计数器
        if ($this->config['by_user'] && $userId) {
            $this->incrementCounter('user', $userId);
        }
        
        // 记录异常请求
        if ($statusCode >= 400) {
            $this->recordErrorRequest($clientIp, $userId, $statusCode);
        }
    }
    
    private function incrementCounter(string $type, string $identifier): void
    {
        $key = "rate_limit:{$type}:{$identifier}";
        
        if ($this->cache->has($key)) {
            $this->cache->increment($key);
        } else {
            $this->cache->set($key, 1, $this->config['window_seconds']);
        }
    }
    
    private function recordErrorRequest(string $clientIp, ?string $userId, int $statusCode): void
    {
        $errorKey = "error_requests:{$clientIp}:" . date('Y-m-d-H');
        $this->cache->increment($errorKey, 1, 3600); // 记录1小时
        
        if ($userId) {
            $userErrorKey = "user_error_requests:{$userId}:" . date('Y-m-d-H');
            $this->cache->increment($userErrorKey, 1, 3600);
        }
    }
    
    private function getMaxRequests(string $type, string $identifier): int
    {
        // 可以根据类型和标识符返回不同的限制
        $configKey = "max_requests_{$type}";
        return $this->config[$configKey] ?? $this->config['max_requests'];
    }
    
    private function getResetTime(string $key): int
    {
        $ttl = $this->cache->getTtl($key);
        return time() + ($ttl > 0 ? $ttl : $this->config['window_seconds']);
    }
    
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($serverParams[$header])) {
                $ip = $serverParams[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }
    
    private function getUserId(ServerRequestInterface $request): ?string
    {
        // 尝试从JWT token中获取用户ID
        $auth = $request->getAttribute('auth_user');
        if ($auth && isset($auth['id'])) {
            return (string) $auth['id'];
        }
        
        // 尝试从请求头获取
        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            // 这里可以解析JWT获取用户ID
            // 为简化，暂时返回null
        }
        
        return null;
    }
    
    private function createBlockedResponse(string $reason, array $limitInfo = []): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        
        $body = json_encode([
            'error' => 'Rate limit exceeded',
            'message' => $reason,
            'reset_time' => $limitInfo['reset_time'] ?? null,
            'retry_after' => isset($limitInfo['reset_time']) ? 
                ($limitInfo['reset_time'] - time()) : $this->config['block_duration']
        ]);
        
        $response->getBody()->write($body);
        
        return $response
            ->withStatus(429)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Retry-After', (string) ($limitInfo['reset_time'] ?? $this->config['block_duration']));
    }
    
    private function addRateLimitHeaders(ResponseInterface $response, array $limitResults): ResponseInterface
    {
        foreach ($limitResults as $type => $result) {
            if (!$result['blocked']) {
                $response = $response
                    ->withHeader("X-RateLimit-{$type}-Limit", (string) $result['limit'])
                    ->withHeader("X-RateLimit-{$type}-Remaining", (string) $result['remaining'])
                    ->withHeader("X-RateLimit-{$type}-Reset", (string) $result['reset_time']);
            }
        }
        
        return $response;
    }
}
