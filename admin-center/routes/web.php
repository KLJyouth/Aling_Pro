<?php
/**
 * 定义Web路由
 * 
 * 格式: $router->get('路由路径', '控制器@方法');
 */

// 获取路由实例
$router = new \App\Core\Router();

// 主页/仪表盘
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// 认证相关路由
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPasswordForm');
$router->post('/forgot-password', 'AuthController@sendResetLink');
$router->get('/reset-password/{token}', 'AuthController@resetPasswordForm');
$router->post('/reset-password', 'AuthController@resetPassword');

// 用户管理路由
$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users/store', 'UserController@store');
$router->get('/users/edit/{id}', 'UserController@edit');
$router->post('/users/update/{id}', 'UserController@update');
$router->post('/users/delete/{id}', 'UserController@delete');
$router->get('/users/show/{id}', 'UserController@show');

// 系统设置路由
$router->get('/settings', 'SettingController@index');
$router->post('/settings/save', 'SettingController@save');

// 日志管理路由
$router->get('/logs', 'LogController@index');
$router->get('/logs/view/{file}', 'LogController@view');
$router->get('/logs/download/{file}', 'LogController@download');
$router->post('/logs/clear', 'LogController@clear');

// 系统监控路由
$router->get('/monitoring', 'MonitoringController@index');
$router->get('/monitoring/status', 'MonitoringController@status');
$router->get('/monitoring/resources', 'MonitoringController@resources');

// 安全管理路由
$router->get('/security', 'SecurityController@index');
$router->post('/security/scan', 'SecurityController@scan');
$router->post('/security/fix', 'SecurityController@fix');

// 运维工具路由
$router->get('/tools', 'ToolController@index');
$router->get('/tools/system-info', 'ToolController@systemInfo');
$router->get('/tools/phpinfo', 'ToolController@phpInfo');
$router->get('/tools/server-status', 'ToolController@serverStatus');
$router->get('/tools/database-info', 'ToolController@databaseInfo');
$router->get('/tools/database-management', 'ToolController@databaseManagement');
$router->get('/tools/cache-optimizer', 'ToolController@cacheOptimizer');
$router->get('/tools/security-checker', 'ToolController@securityChecker');
$router->get('/tools/logs-viewer', 'ToolController@logsViewer');

// 运维报告路由
$router->get('/reports', 'ReportController@index');
$router->post('/reports/generate', 'ReportController@generate');
$router->get('/reports/view/{id}', 'ReportController@view');
$router->get('/reports/export/{id}', 'ReportController@export');
$router->post('/reports/delete/{id}', 'ReportController@delete');

// 404页面
$router->get('/404', 'ErrorController@notFound');
$router->get('/error', 'ErrorController@error');

// 将路由实例返回给引导文件
return $router; 