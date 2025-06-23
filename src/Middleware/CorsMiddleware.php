<?php
/**
 * CORS中间件
 * 
 * @package AlingAi\Middleware
 */

declare(strict_types=1);

namespace AlingAi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    private array $settings;
    
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'origin' => ['*'],
            'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
            'headers.allow' => ['X-Requested-With', 'Content-Type', 'Accept', 'Origin', 'Authorization'],
            'headers.expose' => ['Authorization'],
            'credentials' => true,
            'cache' => 86400, // 24 hours
        ], $settings);
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        
        return $this->addCorsHeaders($request, $response);
    }
    
    private function addCorsHeaders(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        
        // 设置 Access-Control-Allow-Origin
        if ($this->isOriginAllowed($origin)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
        } elseif (in_array('*', $this->settings['origin'])) {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        }
        
        // 设置允许的方法
        $response = $response->withHeader(
            'Access-Control-Allow-Methods',
            implode(', ', $this->settings['methods'])
        );
        
        // 设置允许的头部
        $response = $response->withHeader(
            'Access-Control-Allow-Headers',
            implode(', ', $this->settings['headers.allow'])
        );
        
        // 设置暴露的头部
        if (!empty($this->settings['headers.expose'])) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $this->settings['headers.expose'])
            );
        }
        
        // 设置是否允许凭据
        if ($this->settings['credentials']) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }
        
        // 设置预检请求缓存时间
        if ($request->getMethod() === 'OPTIONS') {
            $response = $response->withHeader('Access-Control-Max-Age', (string) $this->settings['cache']);
        }
        
        return $response;
    }
    
    private function isOriginAllowed(string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }
        
        foreach ($this->settings['origin'] as $allowedOrigin) {
            if ($allowedOrigin === '*' || $allowedOrigin === $origin) {
                return true;
            }
            
            // 支持通配符匹配
            if (strpos($allowedOrigin, '*') !== false) {
                $pattern = str_replace('*', '.*', preg_quote($allowedOrigin, '/'));
                if (preg_match('/^' . $pattern . '$/', $origin)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}
