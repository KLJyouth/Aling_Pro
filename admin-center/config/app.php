<?php
/**
 * 应用程序主配置文件
 */

return [
    // 应用基本信息
    'app' => [
        'name' => 'AlingAi_pro IT运维中心',
        'version' => '2.0.0',
        'debug' => getenv('APP_ENV') === 'development',
        'timezone' => 'Asia/Shanghai',
        'locale' => 'zh_CN',
        'env' => getenv('APP_ENV') ?: 'production',
        'url' => 'http://localhost'
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
        'temp' => BASE_PATH . '/storage/temp',
        'cache' => BASE_PATH . '/storage/cache',
    ],
    
    // 功能模块
    'modules' => [
        'tools' => true,       // 维护工具
        'monitoring' => true,  // 系统监控
        'security' => true,    // 安全管理
        'reports' => true,     // 运维报告
        'logs' => true,        // 日志管理
    ],
    
    // 安全配置
    'security' => [
        'session' => [
            'name' => 'admin_session',
            'lifetime' => 7200, // 2小时
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ],
        'password' => [
            'min_length' => 8,
            'require_special_chars' => true,
            'require_numbers' => true,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'max_attempts' => 5,
            'lockout_time' => 300, // 5分钟
        ],
        'token' => [
            'lifetime' => 86400, // 24小时
            'refresh_time' => 3600, // 1小时
        ],
        // CSRF保护
        'csrf' => [
            'enabled' => true,
            'token_lifetime' => 7200, // 2小时
        ],
        // XSS过滤
        'xss' => [
            'enabled' => true,
        ],
    ],
    
    // 日志配置
    'logging' => [
        'level' => 'debug', // debug, info, notice, warning, error, critical, alert, emergency
        'max_files' => 30,   // 保留天数
        'format' => '[%datetime%] %level_name%: %message% %context% %extra%',
        'date_format' => 'Y-m-d H:i:s',
    ],
    
    // 缓存配置
    'cache' => [
        'driver' => 'file',
        'lifetime' => 86400, // 24小时
        'prefix' => 'admin_',
    ],
    
    // 监控配置
    'monitoring' => [
        'max_log_size' => 10485760, // 10MB
        'cpu_warning' => 80, // CPU使用率预警阈值(%)
        'memory_warning' => 80, // 内存使用率预警阈值(%)
        'disk_warning' => 85, // 磁盘使用率预警阈值(%)
        'log_check_interval' => 3600, // 日志检查间隔(秒)
    ],
]; 