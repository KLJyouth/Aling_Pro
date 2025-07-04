<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Management\AdminRoleController;
use App\Http\Controllers\Admin\Management\AdminUserController;
use App\Http\Controllers\Admin\Management\AdminPermissionController;
use App\Http\Controllers\Admin\Management\AdminLogController;
use App\Http\Controllers\Admin\Security\ApiInterfaceController;
use App\Http\Controllers\Admin\Security\ApiRiskRuleController;
use App\Http\Controllers\Admin\Security\ApiRiskEventController;
use App\Http\Controllers\Admin\Security\ApiBlacklistController;
use App\Http\Controllers\Admin\Security\ApiMonitoringDashboardController;

// 管理员管理路由
Route::prefix('admin-management')->middleware(['auth:admin'])->group(function () {
    // 角色管理
    Route::prefix('roles')->group(function () {
        Route::get('/', [AdminRoleController::class, 'index'])->name('admin.roles.index');
        Route::get('/create', [AdminRoleController::class, 'create'])->name('admin.roles.create');
        Route::post('/', [AdminRoleController::class, 'store'])->name('admin.roles.store');
        Route::get('/{role}', [AdminRoleController::class, 'show'])->name('admin.roles.show');
        Route::get('/{role}/edit', [AdminRoleController::class, 'edit'])->name('admin.roles.edit');
        Route::put('/{role}', [AdminRoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/{role}', [AdminRoleController::class, 'destroy'])->name('admin.roles.destroy');
    });
    
    // 管理员管理
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/{user}/edit-password', [AdminUserController::class, 'editPassword'])->name('admin.users.edit_password');
        Route::put('/{user}/update-password', [AdminUserController::class, 'updatePassword'])->name('admin.users.update_password');
        Route::get('/{user}/login-logs', [AdminUserController::class, 'loginLogs'])->name('admin.users.login_logs');
        Route::get('/{user}/operation-logs', [AdminUserController::class, 'operationLogs'])->name('admin.users.operation_logs');
    });
    
    // 权限管理
    Route::prefix('permissions')->group(function () {
        Route::get('/', [AdminPermissionController::class, 'index'])->name('admin.permissions.index');
        Route::get('/create', [AdminPermissionController::class, 'create'])->name('admin.permissions.create');
        Route::post('/', [AdminPermissionController::class, 'store'])->name('admin.permissions.store');
        Route::get('/{permission}/edit', [AdminPermissionController::class, 'edit'])->name('admin.permissions.edit');
        Route::put('/{permission}', [AdminPermissionController::class, 'update'])->name('admin.permissions.update');
        Route::delete('/{permission}', [AdminPermissionController::class, 'destroy'])->name('admin.permissions.destroy');
        
        // 权限组
        Route::get('/groups', [AdminPermissionController::class, 'groupIndex'])->name('admin.permission.groups');
        Route::get('/groups/create', [AdminPermissionController::class, 'groupCreate'])->name('admin.permission.groups.create');
        Route::post('/groups', [AdminPermissionController::class, 'groupStore'])->name('admin.permission.groups.store');
        Route::get('/groups/{group}/edit', [AdminPermissionController::class, 'groupEdit'])->name('admin.permission.groups.edit');
        Route::put('/groups/{group}', [AdminPermissionController::class, 'groupUpdate'])->name('admin.permission.groups.update');
        Route::delete('/groups/{group}', [AdminPermissionController::class, 'groupDestroy'])->name('admin.permission.groups.destroy');
    });
    
    // 日志管理
    Route::prefix('logs')->group(function () {
        Route::get('/login', [AdminLogController::class, 'loginLogs'])->name('admin.logs.login');
        Route::get('/operation', [AdminLogController::class, 'operationLogs'])->name('admin.logs.operation');
        Route::get('/login/{log}', [AdminLogController::class, 'showLoginLog'])->name('admin.logs.login.show');
        Route::get('/operation/{log}', [AdminLogController::class, 'showOperationLog'])->name('admin.logs.operation.show');
        Route::get('/login/export', [AdminLogController::class, 'exportLoginLogs'])->name('admin.logs.login.export');
        Route::get('/operation/export', [AdminLogController::class, 'exportOperationLogs'])->name('admin.logs.operation.export');
    });
});

// API风控监管路由
Route::prefix('api-security')->middleware(['auth:admin'])->group(function () {
    // 仪表板
    Route::get('/dashboard', [ApiMonitoringDashboardController::class, 'index'])->name('admin.api.dashboard');
    Route::get('/realtime', [ApiMonitoringDashboardController::class, 'realtime'])->name('admin.api.realtime');
    Route::get('/realtime-data', [ApiMonitoringDashboardController::class, 'realtimeData'])->name('admin.api.realtime.data');
    
    // API接口管理
    Route::prefix('interfaces')->group(function () {
        Route::get('/', [ApiInterfaceController::class, 'index'])->name('admin.api.interfaces.index');
        Route::get('/create', [ApiInterfaceController::class, 'create'])->name('admin.api.interfaces.create');
        Route::post('/', [ApiInterfaceController::class, 'store'])->name('admin.api.interfaces.store');
        Route::get('/{interface}', [ApiInterfaceController::class, 'show'])->name('admin.api.interfaces.show');
        Route::get('/{interface}/edit', [ApiInterfaceController::class, 'edit'])->name('admin.api.interfaces.edit');
        Route::put('/{interface}', [ApiInterfaceController::class, 'update'])->name('admin.api.interfaces.update');
        Route::delete('/{interface}', [ApiInterfaceController::class, 'destroy'])->name('admin.api.interfaces.destroy');
        Route::post('/import', [ApiInterfaceController::class, 'import'])->name('admin.api.interfaces.import');
        Route::get('/export', [ApiInterfaceController::class, 'export'])->name('admin.api.interfaces.export');
    });
    
    // 风控规则管理
    Route::prefix('risk-rules')->group(function () {
        Route::get('/', [ApiRiskRuleController::class, 'index'])->name('admin.api.risk.rules.index');
        Route::get('/create', [ApiRiskRuleController::class, 'create'])->name('admin.api.risk.rules.create');
        Route::post('/', [ApiRiskRuleController::class, 'store'])->name('admin.api.risk.rules.store');
        Route::get('/{rule}', [ApiRiskRuleController::class, 'show'])->name('admin.api.risk.rules.show');
        Route::get('/{rule}/edit', [ApiRiskRuleController::class, 'edit'])->name('admin.api.risk.rules.edit');
        Route::put('/{rule}', [ApiRiskRuleController::class, 'update'])->name('admin.api.risk.rules.update');
        Route::delete('/{rule}', [ApiRiskRuleController::class, 'destroy'])->name('admin.api.risk.rules.destroy');
        Route::put('/{rule}/toggle-status', [ApiRiskRuleController::class, 'toggleStatus'])->name('admin.api.risk.rules.toggle_status');
        Route::get('/{rule}/test', [ApiRiskRuleController::class, 'showTestForm'])->name('admin.api.risk.rules.test_form');
        Route::post('/{rule}/test', [ApiRiskRuleController::class, 'testRule'])->name('admin.api.risk.rules.test');
    });
    
    // 风险事件管理
    Route::prefix('risk-events')->group(function () {
        Route::get('/', [ApiRiskEventController::class, 'index'])->name('admin.api.risk.events.index');
        Route::get('/{event}', [ApiRiskEventController::class, 'show'])->name('admin.api.risk.events.show');
        Route::put('/{event}/process', [ApiRiskEventController::class, 'process'])->name('admin.api.risk.events.process');
        Route::post('/batch-process', [ApiRiskEventController::class, 'batchProcess'])->name('admin.api.risk.events.batch_process');
        Route::get('/export', [ApiRiskEventController::class, 'export'])->name('admin.api.risk.events.export');
        Route::get('/statistics', [ApiRiskEventController::class, 'statistics'])->name('admin.api.risk.events.statistics');
    });
    
    // 黑名单管理
    Route::prefix('blacklists')->group(function () {
        Route::get('/', [ApiBlacklistController::class, 'index'])->name('admin.api.blacklists.index');
        Route::get('/create', [ApiBlacklistController::class, 'create'])->name('admin.api.blacklists.create');
        Route::post('/', [ApiBlacklistController::class, 'store'])->name('admin.api.blacklists.store');
        Route::get('/{blacklist}/edit', [ApiBlacklistController::class, 'edit'])->name('admin.api.blacklists.edit');
        Route::put('/{blacklist}', [ApiBlacklistController::class, 'update'])->name('admin.api.blacklists.update');
        Route::delete('/{blacklist}', [ApiBlacklistController::class, 'destroy'])->name('admin.api.blacklists.destroy');
        Route::post('/import', [ApiBlacklistController::class, 'import'])->name('admin.api.blacklists.import');
        Route::get('/export', [ApiBlacklistController::class, 'export'])->name('admin.api.blacklists.export');
    });
});
