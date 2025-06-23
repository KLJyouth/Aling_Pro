<?php
/**
 * 安全中间件
 * 
 * @package AlingAi\Middleware
 */

declare(strict_types=1);

namespace AlingAi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SecurityMiddleware implements MiddlewareInterface
{
    private array $settings;
    
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge([
            'xss_protection' => true,
            'content_type_nosniff' => true,
            'frame_deny' => true,
            'https_redirect' => false,
            'hsts_max_age' => 31536000, // 1 year
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'feature_policy' => [
                'camera' => "'none'",
                'microphone' => "'none'",
                'geolocation' => "'none'",
                'payment' => "'none'",
            ],
        ], $settings);
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // HTTPS重定向
        if ($this->settings['https_redirect'] && !$this->isHttps($request)) {
            return $this->redirectToHttps($request);
        }
        
        $response = $handler->handle($request);
        
        return $this->addSecurityHeaders($response);
    }
    
    private function addSecurityHeaders(ResponseInterface $response): ResponseInterface
    {
        // X-XSS-Protection
        if ($this->settings['xss_protection']) {
            $response = $response->withHeader('X-XSS-Protection', '1; mode=block');
        }
        
        // X-Content-Type-Options
        if ($this->settings['content_type_nosniff']) {
            $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
        }
        
        // X-Frame-Options
        if ($this->settings['frame_deny']) {
            $response = $response->withHeader('X-Frame-Options', 'DENY');
        }
        
        // Strict-Transport-Security (仅在HTTPS下)
        if ($this->isHttps() && $this->settings['hsts_max_age'] > 0) {
            $response = $response->withHeader(
                'Strict-Transport-Security',
                'max-age=' . $this->settings['hsts_max_age'] . '; includeSubDomains; preload'
            );
        }
        
        // Referrer-Policy
        if (!empty($this->settings['referrer_policy'])) {
            $response = $response->withHeader('Referrer-Policy', $this->settings['referrer_policy']);
        }
        
        // Feature-Policy / Permissions-Policy
        if (!empty($this->settings['feature_policy'])) {
            $policies = [];
            foreach ($this->settings['feature_policy'] as $feature => $value) {
                $policies[] = $feature . '=' . $value;
            }
            $response = $response->withHeader('Permissions-Policy', implode(', ', $policies));
        }
        
        // Content-Security-Policy (基础策略)
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https: wss: ws:",
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "upgrade-insecure-requests"
        ];
        
        $response = $response->withHeader('Content-Security-Policy', implode('; ', $csp));
        
        // X-Powered-By头部移除
        $response = $response->withoutHeader('X-Powered-By');
        
        // Server头部隐藏
        $response = $response->withHeader('Server', 'AlingAi Pro');
        
        return $response;
    }
    
    private function isHttps(?ServerRequestInterface $request = null): bool
    {
        if ($request) {
            $uri = $request->getUri();
            return $uri->getScheme() === 'https';
        }
        
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
            (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        );
    }
    
    private function redirectToHttps(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();
        $httpsUri = $uri->withScheme('https')->withPort(443);
        
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'HTTPS required',
            'redirect_url' => (string) $httpsUri,
            'timestamp' => date('c')
        ]));
        
        return $response
            ->withStatus(301)
            ->withHeader('Location', (string) $httpsUri)
            ->withHeader('Content-Type', 'application/json');
    }
}
