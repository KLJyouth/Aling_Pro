<?php
/**
 * 路由配置文件
 * 
 * 定义系统所有路由及其对应的控制器和方法
 */

return [
    // 主页
    '/' => ['HomeController', 'index'],
    '/dashboard' => ['HomeController', 'index'],
    
    // 用户管理
    '/users' => ['UserController', 'index'],
    '/users/add' => ['UserController', 'add'],
    '/users/edit/([0-9]+)' => ['UserController', 'edit'],
    '/users/delete/([0-9]+)' => ['UserController', 'delete'],
    
    // 系统设置
    '/settings' => ['SettingController', 'index'],
    '/settings/save' => ['SettingController', 'save'],
    
    // 日志管理
    '/logs' => ['LogController', 'index'],
    '/logs/([0-9]+)' => ['LogController', 'show'],
    '/logs/clear' => ['LogController', 'clear'],
    
    // 备份管理
    '/backup' => ['BackupController', 'index'],
    '/backup/create' => ['BackupController', 'create'],
    '/backup/([^/]+)/download' => ['BackupController', 'download'],
    '/backup/([^/]+)/restore' => ['BackupController', 'restore'],
    '/backup/([^/]+)/delete' => ['BackupController', 'delete'],
    
    // 系统工具
    '/tools' => ['ToolController', 'index'],
    '/tools/phpinfo' => ['ToolController', 'phpInfo'],
    '/tools/server-info' => ['ToolController', 'serverInfo'],
    '/tools/database-info' => ['ToolController', 'databaseInfo'],
    
    // 缓存管理
    '/cache/clear' => ['CacheController', 'clear'],
    
    // 数据库优化
    '/database/optimize' => ['DatabaseController', 'optimize'],
    
    // 错误页面
    '/error/404' => ['ErrorController', 'notFound'],
    '/error/500' => ['ErrorController', 'serverError'],
    '/error/403' => ['ErrorController', 'forbidden'],
    '/error/maintenance' => ['ErrorController', 'maintenance'],
]; 