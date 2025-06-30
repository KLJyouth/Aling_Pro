<?php
/**
 * 路由配置文件
 * 
 * 定义系统所有路由及其对应的控制器和方法
 */

return [
    // 仪表盘路由
    'GET|/' => 'DashboardController@index',
    'GET|/dashboard' => 'DashboardController@index',
    
    // 认证路由
    'GET|/login' => 'AuthController@showLoginForm',
    'POST|/login' => 'AuthController@login',
    'GET|/logout' => 'AuthController@logout',
    
    // 用户管理路由
    'GET|/users' => 'UserController@index',
    'GET|/users/create' => 'UserController@create',
    'POST|/users/store' => 'UserController@store',
    'GET|/users/:id' => 'UserController@show',
    'GET|/users/:id/edit' => 'UserController@edit',
    'POST|/users/:id/update' => 'UserController@update',
    'POST|/users/:id/delete' => 'UserController@delete',
    
    // 系统设置路由
    'GET|/settings' => 'SettingController@index',
    'POST|/settings/save' => 'SettingController@save',
    'GET|/cache/clear' => 'SettingController@clearCache',
    'GET|/database/optimize' => 'SettingController@optimizeDatabase',
    'GET|/maintenance/toggle' => 'SettingController@toggleMaintenanceMode',
    
    // 系统日志路由
    'GET|/logs' => 'LogController@index',
    'GET|/logs/:id' => 'LogController@show',
    'POST|/logs/clear' => 'LogController@clear',
    'POST|/logs/:id/delete' => 'LogController@delete',
    
    // 备份管理路由
    'GET|/backup' => 'BackupController@index',
    'GET|/backup/create' => 'BackupController@create',
    'GET|/backup/:filename/download' => 'BackupController@download',
    'POST|/backup/:filename/delete' => 'BackupController@delete',
    'POST|/backup/:filename/restore' => 'BackupController@restore',
    
    // 系统工具路由
    'GET|/tools' => 'ToolController@index',
    'GET|/tools/phpinfo' => 'ToolController@phpInfo',
    'GET|/tools/server-info' => 'ToolController@serverInfo',
    'GET|/tools/database-info' => 'ToolController@databaseInfo',
    
    // 404页面
    '404' => 'ErrorController@notFound',
    '500' => 'ErrorController@serverError'
]; 