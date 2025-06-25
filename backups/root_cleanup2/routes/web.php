<?php
/**
 * 定义Web路由
 * 
 * 格式: $router->get('路由路径', '控制器@方法');
 */

// 获取路由实例
$router = new \App\Core\Router();

// 仪表盘路由
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// 维护工具路由
$router->get('/tools', 'ToolsController@index');
$router->get('/tools/php-fix', 'ToolsController@phpFix');
$router->get('/tools/namespace-check', 'ToolsController@namespaceCheck');
$router->get('/tools/encoding-fix', 'ToolsController@encodingFix');
$router->post('/tools/run-fix', 'ToolsController@runFix');
$router->get('/tools/server-status', 'MonitoringController@serverStatus');
$router->get('/tools/database', 'MonitoringController@database');

// 系统监控路由
$router->get('/monitoring', 'MonitoringController@index');
$router->get('/monitoring/php-info', 'MonitoringController@phpInfo');
$router->get('/monitoring/logs', 'MonitoringController@logs');
$router->get('/monitoring/realtime', 'MonitoringController@realtime');
$router->get('/monitoring/realtime-data', 'MonitoringController@getRealtimeData');

// 安全管理路由
$router->get('/security', 'SecurityController@index');
$router->get('/security/permissions', 'SecurityController@permissions');
$router->get('/security/backups', 'SecurityController@backups');
$router->get('/security/users', 'SecurityController@users');
$router->get('/security/roles', 'SecurityController@roles');

// 运维报告路由
$router->get('/reports', 'ReportsController@index');
$router->get('/reports/php-errors', 'ReportsController@phpErrors');
$router->get('/reports/performance', 'ReportsController@performance');
$router->get('/reports/generate', 'ReportsController@generate');
$router->get('/reports/view/{id}', 'ReportsController@view');
$router->get('/reports/download/{id}', 'ReportsController@download');

// 日志管理路由
$router->get('/logs', 'LogsController@index');
$router->get('/logs/view', 'LogsController@view');
$router->get('/logs/download', 'LogsController@download');
$router->post('/logs/clear/{type}', 'LogsController@clear');

// 认证路由
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout'); 