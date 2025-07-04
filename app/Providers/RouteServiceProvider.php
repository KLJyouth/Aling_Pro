<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * 应用的路由中间件组
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * 定义应用的路由模型绑定、模式过滤器等
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix("api")
                ->middleware("api")
                ->namespace($this->namespace)
                ->group(base_path("routes/api.php"));

            Route::middleware("web")
                ->namespace($this->namespace)
                ->group(base_path("routes/web.php"));

            // 用户管理路由
            Route::middleware("web")
                ->namespace($this->namespace)
                ->group(base_path("routes/user-management.php"));
                
            // 管理员管理和API风控监管路由
            Route::middleware("web")
                ->namespace($this->namespace)
                ->group(base_path("routes/admin-management.php"));
                
            // MCP管理控制平台路由
            Route::middleware("api")
                ->namespace($this->namespace)
                ->group(base_path("routes/mcp.php"));
        });
    }

    /**
     * 配置路由请求频率限制
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for("api", function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
