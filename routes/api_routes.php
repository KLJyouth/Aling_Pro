<?php
/**
 * API路由配置文件
 * 
 * 定义所有的API路由规则，格式为：
 * 'HTTP方法:API版本:路径' => '控制器@方法'
 */

return [
    // 认证相关API
    'POST:1:/auth/login' => 'AuthController@login',
    'POST:1:/auth/register' => 'AuthController@register',
    'POST:1:/auth/logout' => 'AuthController@logout',
    'POST:1:/auth/refresh-token' => 'AuthController@refreshToken',
    'POST:1:/auth/forgot-password' => 'AuthController@forgotPassword',
    'POST:1:/auth/reset-password' => 'AuthController@resetPassword',
    'GET:1:/auth/verify-email/{token}' => 'AuthController@verifyEmail',
    
    // 用户相关API
    'GET:1:/user/profile' => 'UserController@getProfile',
    'PUT:1:/user/profile' => 'UserController@updateProfile',
    'PUT:1:/user/password' => 'UserController@changePassword',
    'GET:1:/user/settings' => 'UserController@getSettings',
    'PUT:1:/user/settings' => 'UserController@updateSettings',
    
    // 管理员API
    'GET:1:/admin/users' => 'AdminController@listUsers',
    'GET:1:/admin/users/{id}' => 'AdminController@getUser',
    'POST:1:/admin/users' => 'AdminController@createUser',
    'PUT:1:/admin/users/{id}' => 'AdminController@updateUser',
    'DELETE:1:/admin/users/{id}' => 'AdminController@deleteUser',
    'GET:1:/admin/logs' => 'AdminController@getLogs',
    'GET:1:/admin/statistics' => 'AdminController@getStatistics',
    
    // 系统状态API
    'GET:1:/system/status' => 'SystemController@getStatus',
    'GET:1:/system/stats' => 'SystemController@getStatistics',
    
    // 通用API
    'GET:1:/ping' => 'SystemController@ping',
    'GET:1:/version' => 'SystemController@getVersion',
    
    // 示例API端点
    'GET:1:/examples' => 'ExampleController@index',
    'GET:1:/examples/{id}' => 'ExampleController@show',
    'POST:1:/examples' => 'ExampleController@store',
    'PUT:1:/examples/{id}' => 'ExampleController@update',
    'DELETE:1:/examples/{id}' => 'ExampleController@delete',
]; 