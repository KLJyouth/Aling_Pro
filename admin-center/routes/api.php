<?php
/**
 * 定义API路由
 * 
 * 格式: $router->get('路由路径', '控制器@方法');
 */

// 获取路由实例
$router = new \App\Core\Router();

// API路由使用前缀/api/v1
$router->group('/api/v1', function($router) {
    // 认证API
    $router->post('/auth/login', 'AuthController@apiLogin');
    $router->post('/auth/refresh', 'AuthController@apiRefreshToken');
    $router->post('/auth/logout', 'AuthController@apiLogout');
    
    // 用户API
    $router->get('/users', 'UserController@apiGetAll');
    $router->get('/users/{id}', 'UserController@apiGet');
    $router->post('/users', 'UserController@apiCreate');
    $router->put('/users/{id}', 'UserController@apiUpdate');
    $router->delete('/users/{id}', 'UserController@apiDelete');
    
    // 系统信息API
    $router->get('/system/info', 'SystemController@apiInfo');
    $router->get('/system/status', 'SystemController@apiStatus');
    $router->get('/system/stats', 'SystemController@apiStats');
    
    // 日志API
    $router->get('/logs', 'LogController@apiGetAll');
    $router->get('/logs/{type}', 'LogController@apiGet');
    
    // 监控API
    $router->get('/monitoring/status', 'MonitoringController@apiStatus');
    $router->get('/monitoring/resources', 'MonitoringController@apiResources');
    
    // 数据库API
    $router->get('/database/tables', 'DatabaseController@apiTables');
    $router->get('/database/tables/{table}', 'DatabaseController@apiTableInfo');
    
    // 报告API
    $router->get('/reports', 'ReportController@apiGetAll');
    $router->get('/reports/{id}', 'ReportController@apiGet');
    $router->post('/reports', 'ReportController@apiGenerate');
});

return $router; 