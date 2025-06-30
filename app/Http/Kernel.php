<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * 应用的全局HTTP中间件栈
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * 应用的路由中间件组
     *
     * @var array
     */
    protected $middlewareGroups = [
        "web" => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        "api" => [
            "throttle:api",
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * 应用的路由中间件
     *
     * @var array
     */
    protected $routeMiddleware = [
        "auth" => \App\Http\Middleware\Authenticate::class,
        "auth.basic" => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        "cache.headers" => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        "can" => \Illuminate\Auth\Middleware\Authorize::class,
        "guest" => \App\Http\Middleware\RedirectIfAuthenticated::class,
        "password.confirm" => \Illuminate\Auth\Middleware\RequirePassword::class,
        "signed" => \Illuminate\Routing\Middleware\ValidateSignature::class,
        "throttle" => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        "verified" => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        "db.security" => \App\Http\Middleware\DatabaseSecurityMiddleware::class,
        
        // 零信任安全中间件
        "zero_trust" => \App\Http\Middleware\ZeroTrustMiddleware::class,
        
        // 多因素认证中间件
        "require_mfa" => \App\Http\Middleware\RequireMfaMiddleware::class,
        
        // 设备验证中间件
        "verify_device" => \App\Http\Middleware\VerifyDeviceMiddleware::class,
        
        // 角色和权限中间件
        "role" => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        "permission" => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        "role_or_permission" => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        
        // 会员额度检查中间件
        "check_quota" => \App\Http\Middleware\CheckQuota::class,
    ];
}
