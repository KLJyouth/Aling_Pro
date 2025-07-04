<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MCP\MCPService;

class MCPServiceProvider extends ServiceProvider
{
    /**
     * 注册应用程序服务
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MCPService::class, function ($app) {
            return new MCPService();
        });
    }

    /**
     * 引导应用程序服务
     *
     * @return void
     */
    public function boot()
    {
        // 加载MCP配置
        $this->mergeConfigFrom(
            __DIR__."/../../config/mcp.php", "mcp"
        );
        
        // 发布配置文件
        $this->publishes([
            __DIR__."/../../config/mcp.php" => config_path("mcp.php"),
        ], "mcp-config");
        
        // 加载迁移文件
        $this->loadMigrationsFrom(__DIR__."/../../database/migrations");
    }
}
