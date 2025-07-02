<?php
/**
 * AlingAi Pro 配置文件
 * 
 * 包含网站基本设置和配置项
 */

// 基本设置
$config = [
    // 网站信息
    'site' => [
        'name' => 'AlingAi Pro',
        'title' => 'AlingAi Pro - 先进AI解决方案',
        'description' => 'AlingAi Pro提供先进的AI解决方案，包括AI助手、数据分析、自动化工具和系统集成',
        'url' => 'https://alingai.pro',
        'version' => '1.0.0',
        'email' => 'contact@alingai.pro',
        'support_email' => 'support@alingai.pro',
    ],
    
    // 数据库配置
    'database' => [
        'host' => 'localhost',
        'name' => 'alingai_pro',
        'user' => 'alingai_user',
        'password' => 'your_secure_password',
        'port' => 3306,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    // 路径配置
    'paths' => [
        'root' => dirname(dirname(__DIR__)),
        'public' => dirname(__DIR__),
        'templates' => dirname(__DIR__) . '/templates',
        'assets' => '/assets',
        'uploads' => dirname(__DIR__) . '/uploads',
        'logs' => dirname(dirname(__DIR__)) . '/logs',
    ],
    
    // 安全配置
    'security' => [
        'session_name' => 'alingai_session',
        'session_lifetime' => 7200, // 2小时
        'password_min_length' => 8,
        'password_requires_mixed_case' => true,
        'password_requires_number' => true,
        'password_requires_symbol' => true,
        'max_login_attempts' => 5,
        'lockout_time' => 900, // 15分钟
        'csrf_token_lifetime' => 3600, // 1小时
    ],
    
    // API配置
    'api' => [
        'version' => 'v1',
        'rate_limit' => 100, // 每分钟请求次数
        'token_lifetime' => 86400, // 24小时
    ],
    
    // 邮件配置
    'mail' => [
        'from_name' => 'AlingAi Pro',
        'from_email' => 'no-reply@alingai.pro',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_user' => 'smtp_user',
        'smtp_password' => 'smtp_password',
        'smtp_encryption' => 'tls',
    ],
    
    // 缓存配置
    'cache' => [
        'enabled' => true,
        'lifetime' => 3600, // 1小时
        'driver' => 'file', // file, redis, memcached
    ],
    
    // 日志配置
    'logging' => [
        'level' => 'error', // debug, info, notice, warning, error, critical, alert, emergency
        'file' => dirname(dirname(__DIR__)) . '/logs/app.log',
    ],
    
    // 调试模式
    'debug' => true,
];

// 根据环境加载不同配置
$env = getenv('APP_ENV') ?: 'development';
if ($env === 'production') {
    $config['debug'] = false;
    $config['logging']['level'] = 'error';
    
    // 加载生产环境特定配置
    if (file_exists(__DIR__ . '/production.php')) {
        $productionConfig = require __DIR__ . '/production.php';
        $config = array_merge($config, $productionConfig);
    }
}

// 定义全局变量供其他页面使用
$GLOBALS['config'] = $config;

// 设置一些常用的全局变量
$siteName = $config['site']['name'];
$siteTitle = $config['site']['title'];
$siteDescription = $config['site']['description'];
$siteUrl = $config['site']['url'];
$debug = $config['debug'];

// 导出配置
return $config;