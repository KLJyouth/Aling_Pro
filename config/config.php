<?php
/**
 * AlingAi Pro 系统配置文件
 * 
 * 此文件包含系统的基本配置
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

return [
    /*
    |--------------------------------------------------------------------------
    | 应用配置
    |--------------------------------------------------------------------------
    |
    | 这些是应用程序的基本配置
    |
    */
    "app" => [
        // 应用名称
        "name" => env("APP_NAME", "AlingAi Pro"),
        
        // 应用环境
        "env" => env("APP_ENV", "production"),
        
        // 调试模式
        "debug" => (bool) env("APP_DEBUG", false),
        
        // 应用URL
        "url" => env("APP_URL", "http://localhost"),
        
        // 应用时区
        "timezone" => env("APP_TIMEZONE", "Asia/Shanghai"),
        
        // 应用语言
        "locale" => env("APP_LOCALE", "zh_CN"),
        
        // 应用回退语言
        "fallback_locale" => env("APP_FALLBACK_LOCALE", "en"),
        
        // 应用密钥
        "key" => env("APP_KEY"),
        
        // 加密算法
        "cipher" => "AES-256-CBC",
    ],
    
    /*
    |--------------------------------------------------------------------------
    | 数据库配置
    |--------------------------------------------------------------------------
    |
    | 这些是数据库连接的配置
    |
    */
    "database" => [
        // 默认连接
        "default" => env("DB_CONNECTION", "mysql"),
        
        // 连接配置
        "connections" => [
            "sqlite" => [
                "driver" => "sqlite",
                "database" => env("DB_DATABASE", database_path("database.sqlite")),
                "prefix" => "",
                "foreign_key_constraints" => env("DB_FOREIGN_KEYS", true),
            ],
            
            "mysql" => [
                "driver" => "mysql",
                "host" => env("DB_HOST", "127.0.0.1"),
                "port" => env("DB_PORT", "3306"),
                "database" => env("DB_DATABASE", "alingai"),
                "username" => env("DB_USERNAME", "root"),
                "password" => env("DB_PASSWORD", ""),
                "unix_socket" => env("DB_SOCKET", ""),
                "charset" => "utf8mb4",
                "collation" => "utf8mb4_unicode_ci",
                "prefix" => "",
                "prefix_indexes" => true,
                "strict" => true,
                "engine" => null,
                "options" => extension_loaded("pdo_mysql") ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env("MYSQL_ATTR_SSL_CA"),
                ]) : [],
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | 缓存配置
    |--------------------------------------------------------------------------
    |
    | 这些是缓存系统的配置
    |
    */
    "cache" => [
        // 默认缓存存储
        "default" => env("CACHE_DRIVER", "file"),
        
        // 缓存存储配置
        "stores" => [
            "file" => [
                "driver" => "file",
                "path" => storage_path("framework/cache"),
            ],
            
            "redis" => [
                "driver" => "redis",
                "connection" => "cache",
            ],
        ],
        
        // 缓存前缀
        "prefix" => env("CACHE_PREFIX", "alingai_cache"),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | 会话配置
    |--------------------------------------------------------------------------
    |
    | 这些是会话系统的配置
    |
    */
    "session" => [
        // 会话驱动
        "driver" => env("SESSION_DRIVER", "file"),
        
        // 会话生命周期
        "lifetime" => env("SESSION_LIFETIME", 120),
        
        // 会话加密
        "encrypt" => false,
        
        // 会话文件位置
        "files" => storage_path("framework/sessions"),
        
        // 会话数据库连接
        "connection" => env("SESSION_CONNECTION", null),
        
        // 会话数据库表
        "table" => "sessions",
        
        // 会话Cookie名称
        "cookie" => env("SESSION_COOKIE", "alingai_session"),
        
        // 会话Cookie路径
        "path" => "/",
        
        // 会话Cookie域
        "domain" => env("SESSION_DOMAIN", null),
        
        // 会话Cookie安全传输
        "secure" => env("SESSION_SECURE_COOKIE", false),
        
        // 会话Cookie仅HTTP访问
        "http_only" => true,
        
        // 会话Cookie SameSite属性
        "same_site" => "lax",
    ],
    
    /*
    |--------------------------------------------------------------------------
    | 日志配置
    |--------------------------------------------------------------------------
    |
    | 这些是日志系统的配置
    |
    */
    "logging" => [
        // 默认日志通道
        "default" => env("LOG_CHANNEL", "stack"),
        
        // 弃用API日志通道
        "deprecations" => env("LOG_DEPRECATIONS_CHANNEL", "null"),
        
        // 日志通道配置
        "channels" => [
            "stack" => [
                "driver" => "stack",
                "channels" => ["single"],
                "ignore_exceptions" => false,
            ],
            
            "single" => [
                "driver" => "single",
                "path" => storage_path("logs/alingai.log"),
                "level" => env("LOG_LEVEL", "debug"),
            ],
            
            "daily" => [
                "driver" => "daily",
                "path" => storage_path("logs/alingai.log"),
                "level" => env("LOG_LEVEL", "debug"),
                "days" => 14,
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | 邮件配置
    |--------------------------------------------------------------------------
    |
    | 这些是邮件系统的配置
    |
    */
    "mail" => [
        // 默认邮件驱动
        "default" => env("MAIL_MAILER", "smtp"),
        
        // 邮件驱动配置
        "mailers" => [
            "smtp" => [
                "transport" => "smtp",
                "host" => env("MAIL_HOST", "smtp.mailgun.org"),
                "port" => env("MAIL_PORT", 587),
                "encryption" => env("MAIL_ENCRYPTION", "tls"),
                "username" => env("MAIL_USERNAME"),
                "password" => env("MAIL_PASSWORD"),
                "timeout" => null,
                "auth_mode" => null,
            ],
        ],
        
        // 全局发件人地址
        "from" => [
            "address" => env("MAIL_FROM_ADDRESS", "hello@example.com"),
            "name" => env("MAIL_FROM_NAME", "AlingAi Pro"),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | 文件存储配置
    |--------------------------------------------------------------------------
    |
    | 这些是文件存储系统的配置
    |
    */
    "filesystems" => [
        // 默认磁盘
        "default" => env("FILESYSTEM_DRIVER", "local"),
        
        // 磁盘配置
        "disks" => [
            "local" => [
                "driver" => "local",
                "root" => storage_path("app"),
            ],
            
            "public" => [
                "driver" => "local",
                "root" => storage_path("app/public"),
                "url" => env("APP_URL")."/storage",
                "visibility" => "public",
            ],
        ],
    ],
];
