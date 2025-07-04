<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MCP\MCPController;

/*
|--------------------------------------------------------------------------
| MCP API 路由
|--------------------------------------------------------------------------
|
| 这里是MCP API的路由定义
|
*/

Route::prefix("api/v1/mcp")->middleware(["api", "auth:api", "admin"])->group(function () {
    // 系统状态
    Route::get("system/status", [MCPController::class, "getSystemStatus"]);
    
    // 资源使用情况
    Route::get("system/resources", [MCPController::class, "getResourceUsage"]);
    
    // 用户统计
    Route::get("users/stats", [MCPController::class, "getUserStats"]);
    
    // API统计
    Route::get("api/stats", [MCPController::class, "getApiStats"]);
    
    // 系统维护任务
    Route::post("system/maintenance/{task}", [MCPController::class, "runMaintenanceTask"]);
    
    // 系统配置
    Route::get("system/config/{configGroup?}", [MCPController::class, "getSystemConfig"]);
    Route::put("system/config/{configGroup}", [MCPController::class, "updateSystemConfig"]);
});
