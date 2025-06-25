<?php
/**
 * 应用程序主配置文�?
 */

return [
    // 应用基本信息
    'app' => [
        'name' => 'AlingAi_pro IT运维中心',
        'version' => '1.0.0',
        'debug' => true,
        'timezone' => 'Asia/Shanghai',
        'locale' => 'zh_CN',
    ], 
    
    // 数据库配�?
    'database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'aling_admin',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ], 
    
    // 路径配置
    'paths' => [
        'base' => BASE_PATH,
        'app' => APP_PATH,
        'config' => CONFIG_PATH,
        'routes' => ROUTES_PATH,
        'views' => VIEWS_PATH,
        'public' => BASE_PATH . '/public',
        'storage' => BASE_PATH . '/storage',
        'logs' => BASE_PATH . '/storage/logs',
    ], 
    
    // 功能模块
    'modules' => [
        'tools' => true,       // 维护工具
        'monitoring' => true,  // 系统监控
        'security' => true,    // 安全管理
        'reports' => true,     // 运维报告
        'logs' => true,        // 日志管理
    ], 
]; 
