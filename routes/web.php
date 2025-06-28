<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Security\QuantumSecurityDashboardController;

/*
|--------------------------------------------------------------------------
| Web路由
|--------------------------------------------------------------------------
|
| 这里定义了所有与Web相关的路由
|
*/

// 默认首页
Route::get('/', function () {
    return redirect('/dashboard');
});

// 仪表盘路由
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// 量子安全仪表盘路由
Route::get('/security/quantum-dashboard', [QuantumSecurityDashboardController::class, 'index'])
    ->name('security.quantum-dashboard');

// 其他安全相关路由
Route::prefix('security')->group(function () {
    // 安全概览
    Route::get('/', function () {
        return view('security.overview');
    })->name('security.overview');
    
    // 威胁管理
    Route::get('/threats', function () {
        return view('security.threats');
    })->name('security.threats');
    
    // 漏洞扫描
    Route::get('/vulnerabilities', function () {
        return view('security.vulnerabilities');
    })->name('security.vulnerabilities');
    
    // 安全测试
    Route::get('/tests', function () {
        return view('security.tests');
    })->name('security.tests');
    
    // 量子加密
    Route::get('/quantum-crypto', function () {
        return view('security.quantum-crypto');
    })->name('security.quantum-crypto');
});

// 安全隔离区路由
Route::prefix('security')->name('security.')->middleware(['auth', 'admin'])->group(function () {
    // 量子安全仪表盘
    Route::get('/quantum-dashboard', 'Security\QuantumSecurityDashboardController@index')->name('quantum-dashboard');
    
    // 隔离区路由
    Route::prefix('quarantine')->name('quarantine.')->group(function () {
        Route::get('/', 'Security\QuarantineController@index')->name('index');
        Route::get('/ip-bans', 'Security\QuarantineController@ipBans')->name('ip-bans');
        Route::get('/{id}', 'Security\QuarantineController@show')->name('show');
        Route::post('/{id}/update-status', 'Security\QuarantineController@updateStatus')->name('update-status');
        Route::post('/ban-ip', 'Security\QuarantineController@banIp')->name('ban-ip');
        Route::post('/revoke-ip-ban/{id}', 'Security\QuarantineController@revokeIpBan')->name('revoke-ip-ban');
    });
});

// 工单系统路由
Route::prefix('tickets')->middleware(['auth'])->group(function () {
    // 工单管理
    Route::get('/', 'Ticket\TicketController@index')->name('tickets.index');
    Route::get('/create', 'Ticket\TicketController@create')->name('tickets.create');
    Route::post('/', 'Ticket\TicketController@store')->name('tickets.store');
    Route::get('/{id}', 'Ticket\TicketController@show')->name('tickets.show');
    Route::put('/{id}', 'Ticket\TicketController@update')->name('tickets.update');
    
    // 工单回复
    Route::post('/{id}/reply', 'Ticket\TicketController@reply')->name('tickets.reply');
    
    // 工单操作
    Route::post('/{id}/assign', 'Ticket\TicketController@assign')->name('tickets.assign');
    Route::post('/{id}/close', 'Ticket\TicketController@close')->name('tickets.close');
    Route::post('/{id}/reopen', 'Ticket\TicketController@reopen')->name('tickets.reopen');
    
    // 附件管理
    Route::delete('/{id}/attachment/{attachmentId}', 'Ticket\TicketController@deleteAttachment')
        ->name('tickets.attachment.delete');
});

// 工单部门和分类管理路由（仅管理员）
Route::prefix('admin/tickets')->middleware(['auth', 'role:admin'])->group(function () {
    // 部门管理
    Route::get('/departments', 'Ticket\TicketDepartmentController@index')->name('ticket.departments.index');
    Route::get('/departments/create', 'Ticket\TicketDepartmentController@create')->name('ticket.departments.create');
    Route::post('/departments', 'Ticket\TicketDepartmentController@store')->name('ticket.departments.store');
    Route::get('/departments/{id}/edit', 'Ticket\TicketDepartmentController@edit')->name('ticket.departments.edit');
    Route::put('/departments/{id}', 'Ticket\TicketDepartmentController@update')->name('ticket.departments.update');
    Route::delete('/departments/{id}', 'Ticket\TicketDepartmentController@destroy')->name('ticket.departments.destroy');
    
    // 分类管理
    Route::get('/categories', 'Ticket\TicketCategoryController@index')->name('ticket.categories.index');
    Route::get('/categories/create', 'Ticket\TicketCategoryController@create')->name('ticket.categories.create');
    Route::post('/categories', 'Ticket\TicketCategoryController@store')->name('ticket.categories.store');
    Route::get('/categories/{id}/edit', 'Ticket\TicketCategoryController@edit')->name('ticket.categories.edit');
    Route::put('/categories/{id}', 'Ticket\TicketCategoryController@update')->name('ticket.categories.update');
    Route::delete('/categories/{id}', 'Ticket\TicketCategoryController@destroy')->name('ticket.categories.destroy');
    Route::get('/categories/by-department', 'Ticket\TicketCategoryController@getByDepartment')
        ->name('ticket.categories.by-department');
});

// 网站管理设置路由
Route::prefix('admin/settings')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', 'Admin\SettingController@index')->name('admin.settings.index');
    Route::get('/group/{group}', 'Admin\SettingController@showGroup')->name('admin.settings.group');
    Route::post('/group/{group}', 'Admin\SettingController@saveGroup')->name('admin.settings.group.save');
    
    Route::get('/create', 'Admin\SettingController@create')->name('admin.settings.create');
    Route::post('/', 'Admin\SettingController@store')->name('admin.settings.store');
    Route::get('/{id}/edit', 'Admin\SettingController@edit')->name('admin.settings.edit');
    Route::put('/{id}', 'Admin\SettingController@update')->name('admin.settings.update');
    Route::delete('/{id}', 'Admin\SettingController@destroy')->name('admin.settings.destroy');
    
    Route::post('/clear-cache', 'Admin\SettingController@clearCache')->name('admin.settings.clear-cache');
    Route::post('/init-system', 'Admin\SettingController@initSystemSettings')->name('admin.settings.init-system');
});

// 前台新闻路由
Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', 'News\NewsController@index')->name('index');
    Route::get('/{slug}', 'News\NewsController@show')->name('show');
    Route::get('/category/{slug}', 'News\NewsController@category')->name('category');
    Route::get('/tag/{slug}', 'News\NewsController@tag')->name('tag');
    Route::post('/{slug}/comment', 'News\NewsController@comment')->name('comment');
});

// 后台新闻管理路由
Route::prefix('admin/news')->name('admin.news.')->middleware(['auth', 'role:admin,editor'])->group(function () {
    // 新闻管理
    Route::get('/', 'Admin\News\NewsController@index')->name('index');
    Route::get('/create', 'Admin\News\NewsController@create')->name('create');
    Route::post('/', 'Admin\News\NewsController@store')->name('store');
    Route::get('/{id}/edit', 'Admin\News\NewsController@edit')->name('edit');
    Route::put('/{id}', 'Admin\News\NewsController@update')->name('update');
    Route::delete('/{id}', 'Admin\News\NewsController@destroy')->name('destroy');
    
    // 新闻操作
    Route::post('/{id}/toggle-featured', 'Admin\News\NewsController@toggleFeatured')->name('toggle-featured');
    Route::post('/{id}/publish', 'Admin\News\NewsController@publish')->name('publish');
    Route::post('/{id}/draft', 'Admin\News\NewsController@draft')->name('draft');
    Route::post('/{id}/archive', 'Admin\News\NewsController@archive')->name('archive');
    
    // 分类管理
    Route::get('/categories', 'Admin\News\NewsCategoryController@index')->name('categories.index');
    Route::get('/categories/create', 'Admin\News\NewsCategoryController@create')->name('categories.create');
    Route::post('/categories', 'Admin\News\NewsCategoryController@store')->name('categories.store');
    Route::get('/categories/{id}/edit', 'Admin\News\NewsCategoryController@edit')->name('categories.edit');
    Route::put('/categories/{id}', 'Admin\News\NewsCategoryController@update')->name('categories.update');
    Route::delete('/categories/{id}', 'Admin\News\NewsCategoryController@destroy')->name('categories.destroy');
    Route::post('/categories/{id}/toggle-status', 'Admin\News\NewsCategoryController@toggleStatus')->name('categories.toggle-status');
    
    // 标签管理
    Route::get('/tags', 'Admin\News\NewsTagController@index')->name('tags.index');
    Route::get('/tags/create', 'Admin\News\NewsTagController@create')->name('tags.create');
    Route::post('/tags', 'Admin\News\NewsTagController@store')->name('tags.store');
    Route::get('/tags/{id}/edit', 'Admin\News\NewsTagController@edit')->name('tags.edit');
    Route::put('/tags/{id}', 'Admin\News\NewsTagController@update')->name('tags.update');
    Route::delete('/tags/{id}', 'Admin\News\NewsTagController@destroy')->name('tags.destroy');
    Route::post('/tags/{id}/toggle-status', 'Admin\News\NewsTagController@toggleStatus')->name('tags.toggle-status');
    
    // 评论管理
    Route::get('/comments', 'Admin\News\NewsCommentController@index')->name('comments.index');
    Route::get('/comments/{id}', 'Admin\News\NewsCommentController@show')->name('comments.show');
    Route::post('/comments/{id}/approve', 'Admin\News\NewsCommentController@approve')->name('comments.approve');
    Route::post('/comments/{id}/reject', 'Admin\News\NewsCommentController@reject')->name('comments.reject');
    Route::post('/comments/{id}/reply', 'Admin\News\NewsCommentController@reply')->name('comments.reply');
    Route::delete('/comments/{id}', 'Admin\News\NewsCommentController@destroy')->name('comments.destroy');
    Route::post('/comments/batch-action', 'Admin\News\NewsCommentController@batchAction')->name('comments.batch-action');
}); 