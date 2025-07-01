<?php
/**
 * 日志配置文件
 */

return [
    // 日志级别: debug, info, notice, warning, error, critical, alert, emergency
    'level' => getenv('LOG_LEVEL') ?: 'debug',
    
    // 日志保存路径
    'path' => BASE_PATH . '/storage/logs',
    
    // 日志文件最大数量
    'max_files' => 30,
    
    // 单个日志文件最大大小（字节）
    'max_size' => 10485760, // 10MB
    
    // 日志格式
    'format' => '[%datetime%] %level_name%: %message% %context% %extra%',
    'date_format' => 'Y-m-d H:i:s',
    
    // 是否在控制台输出日志
    'stdout' => getenv('APP_ENV') === 'development',
    
    // 是否记录异常详情
    'trace_exceptions' => true,
    
    // 日志分类
    'channels' => [
        // 应用日志
        'app' => [
            'file' => 'app.log',
            'level' => 'debug',
        ],
        
        // 系统日志
        'system' => [
            'file' => 'system.log',
            'level' => 'info',
        ],
        
        // 访问日志
        'access' => [
            'file' => 'access.log',
            'level' => 'info',
        ],
        
        // 错误日志
        'error' => [
            'file' => 'error.log',
            'level' => 'error',
        ],
        
        // 数据库日志
        'database' => [
            'file' => 'database.log',
            'level' => 'info',
        ],
        
        // 安全日志
        'security' => [
            'file' => 'security.log',
            'level' => 'info',
        ],
    ],
]; 