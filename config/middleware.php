<?php
/**
 * 中间件配置
 * 
 * @package AlingAi\Pro
 * @version 6.0.0
 */

use Slim\App;
use Psr\Container\ContainerInterface;
use AlingAi\Security\Middleware\ApiEncryptionMiddleware;
use AlingAi\Security\Middleware\AuthenticationMiddleware;

return function (App $app, ContainerInterface $container) {
    // 获取配置
    $securityConfig = $container->get('config')['security'] ?? [];
    
    // 注册API加密中间件
    $app->add(function ($request, $handler) use ($container) {
        $middleware = new ApiEncryptionMiddleware(
            $container->get('encryption'),
            $container->get('logger')
        );
        return $middleware->process($request, $handler);
    });
    
    // 注册认证中间件（仅应用于特定路由组）
    $authMiddleware = function ($request, $handler) use ($container) {
        $middleware = new AuthenticationMiddleware(
            $container->get('auth'),
            $container->get('logger')
        );
        return $middleware->process($request, $handler);
    };
    
    // 将认证中间件添加到容器中，以便路由组使用
    $container->set('auth_middleware', $authMiddleware);
    
    // 注册CORS中间件
    $app->add(function ($request, $handler) use ($securityConfig) {
        $response = $handler->handle($request);
        
        $corsConfig = $securityConfig['cors'] ?? [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
            'exposed_headers' => [],
            'max_age' => 3600,
            'supports_credentials' => false
        ];
        
        $origin = $request->getHeaderLine('Origin');
        
        // 检查是否允许该来源
        if (in_array('*', $corsConfig['allowed_origins']) || in_array($origin, $corsConfig['allowed_origins'])) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin ?: '*');
            
            if ($corsConfig['supports_credentials']) {
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
            
            if ($request->getMethod() === 'OPTIONS') {
                $response = $response
                    ->withHeader('Access-Control-Allow-Methods', implode(', ', $corsConfig['allowed_methods']))
                    ->withHeader('Access-Control-Allow-Headers', implode(', ', $corsConfig['allowed_headers']))
                    ->withHeader('Access-Control-Max-Age', (string)$corsConfig['max_age']);
            }
            
            if (!empty($corsConfig['exposed_headers'])) {
                $response = $response->withHeader('Access-Control-Expose-Headers', implode(', ', $corsConfig['exposed_headers']));
            }
        }
        
        return $response;
    });
    
    // 注册内容安全策略中间件
    $app->add(function ($request, $handler) use ($securityConfig) {
        $response = $handler->handle($request);
        
        $cspConfig = $securityConfig['csp'] ?? [
            'enabled' => true,
            'report_only' => false,
            'report_uri' => '/api/v1/security/csp-report'
        ];
        
        if ($cspConfig['enabled']) {
            $cspHeader = $cspConfig['report_only'] ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            
            $cspValue = "default-src 'self'; " .
                        "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                        "style-src 'self' 'unsafe-inline'; " .
                        "img-src 'self' data:; " .
                        "font-src 'self'; " .
                        "connect-src 'self'; " .
                        "frame-src 'self'; " .
                        "report-uri " . $cspConfig['report_uri'];
            
            $response = $response->withHeader($cspHeader, $cspValue);
        }
        
        return $response;
    });
    
    // 注册安全头部中间件
    $app->add(function ($request, $handler) {
        $response = $handler->handle($request);
        
        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    });
};
