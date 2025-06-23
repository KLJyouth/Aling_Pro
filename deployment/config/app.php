<?php
/**
 * 应用程序配置文件
 * 
 * @package AlingAi\Pro
 */

return [
    // 应用基本配置
    'app' => [
        'name' => getenv('APP_NAME') ?: 'AlingAi Pro',
        'version' => '2.0.0',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Shanghai',
        'locale' => getenv('APP_LOCALE') ?: 'zh_CN',
        'fallback_locale' => 'en_US',
        'key' => getenv('APP_KEY') ?: 'base64:' . base64_encode(random_bytes(32)),
    ],    // 数据库配置
    'database' => [
        'default' => getenv('DB_CONNECTION') ?: 'mysql',
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => getenv('DB_HOST') ?: '127.0.0.1',
                'port' => (int) (getenv('DB_PORT') ?: 3306),
                'database' => getenv('DB_DATABASE') ?: 'alingai_pro',
                'username' => getenv('DB_USERNAME') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => getenv('DB_PREFIX') ?: '',
                'strict' => true,
                'engine' => 'InnoDB',
                'options' => [
                    PDO::ATTR_TIMEOUT => 30,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
                ],
            ],
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => getenv('DB_DATABASE') ?: __DIR__ . '/../storage/database.sqlite',
                'prefix' => getenv('DB_PREFIX') ?: '',
                'foreign_key_constraints' => true,
                'options' => [
                    PDO::ATTR_TIMEOUT => 30,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ],
            ],
        ],
    ],

    // 缓存配置
    'cache' => [
        'default' => getenv('CACHE_DRIVER') ?: 'redis',
        'stores' => [
            'redis' => [
                'driver' => 'redis',
                'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
                'port' => (int) (getenv('REDIS_PORT') ?: 6379),
                'password' => getenv('REDIS_PASSWORD') ?: null,
                'database' => (int) (getenv('REDIS_DB') ?: 0),
                'prefix' => getenv('REDIS_PREFIX') ?: 'alingai_pro:',
            ],
            'file' => [
                'driver' => 'file',
                'path' => '/storage/framework/cache',
            ],
        ],
    ],

    // 会话配置
    'session' => [
        'driver' => getenv('SESSION_DRIVER') ?: 'redis',
        'lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 7200), // 2 hours
        'expire_on_close' => false,
        'encrypt' => true,
        'files' => '/storage/framework/sessions',
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'alingai_session',
        'path' => '/',
        'domain' => getenv('SESSION_DOMAIN'),
        'secure' => filter_var(getenv('SESSION_SECURE_COOKIE'), FILTER_VALIDATE_BOOLEAN),
        'http_only' => true,
        'same_site' => 'lax',
    ],

    // JWT配置
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'your-jwt-secret-key',
        'algorithm' => 'HS256',
        'ttl' => (int) (getenv('JWT_TTL') ?: 3600), // 1 hour
        'refresh_ttl' => (int) (getenv('JWT_REFRESH_TTL') ?: 604800), // 1 week
        'leeway' => (int) (getenv('JWT_LEEWAY') ?: 60), // 1 minute
        'issuer' => getenv('JWT_ISSUER') ?: 'alingai-pro',
        'audience' => getenv('JWT_AUDIENCE') ?: 'alingai-pro-users',
    ],

    // 邮件配置
    'mail' => [
        'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => (int) (getenv('MAIL_PORT') ?: 587),
        'username' => getenv('MAIL_USERNAME'),
        'password' => getenv('MAIL_PASSWORD'),
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'from' => [
            'address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@alingai.com',
            'name' => getenv('MAIL_FROM_NAME') ?: 'AlingAi Pro',
        ],
        'reply_to' => [
            'address' => getenv('MAIL_REPLY_TO_ADDRESS') ?: 'support@alingai.com',
            'name' => getenv('MAIL_REPLY_TO_NAME') ?: 'AlingAi Support',
        ],
    ],

    // 文件存储配置
    'filesystems' => [
        'default' => getenv('FILESYSTEM_DRIVER') ?: 'local',
        'disks' => [
            'local' => [
                'driver' => 'local',
                'root' => '/storage/app',
            ],
            'public' => [
                'driver' => 'local',
                'root' => '/storage/app/public',
                'url' => getenv('APP_URL') . '/storage',
                'visibility' => 'public',
            ],
            'uploads' => [
                'driver' => 'local',
                'root' => '/public/uploads',
                'url' => getenv('APP_URL') . '/uploads',
                'visibility' => 'public',
            ],
        ],
    ],

    // 日志配置
    'logging' => [
        'default' => getenv('LOG_CHANNEL') ?: 'stack',
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                'channels' => ['daily'],
            ],
            'daily' => [
                'driver' => 'daily',
                'path' => '/storage/logs/alingai.log',
                'level' => getenv('LOG_LEVEL') ?: 'info',
                'days' => 14,
            ],
            'syslog' => [
                'driver' => 'syslog',
                'level' => 'debug',
            ],
        ],
    ],

    // 安全配置
    'security' => [
        'csrf_protection' => true,
        'xss_protection' => true,
        'content_type_nosniff' => true,
        'frame_deny' => true,
        'https_redirect' => filter_var(getenv('FORCE_HTTPS'), FILTER_VALIDATE_BOOLEAN),
        'hsts_max_age' => 31536000, // 1 year
        'rate_limiting' => [
            'enabled' => true,
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000,
        ],
    ],

    // 上传文件配置
    'upload' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'document' => ['pdf', 'doc', 'docx', 'txt', 'md'],
            'archive' => ['zip', 'rar', '7z'],
        ],
        'image_max_width' => 2048,
        'image_max_height' => 2048,
        'thumbnail_size' => 300,
    ],

    // API配置
    'api' => [
        'version' => 'v1',
        'rate_limit' => [
            'requests_per_minute' => 100,
            'requests_per_hour' => 2000,
        ],
        'pagination' => [
            'default_limit' => 20,
            'max_limit' => 100,
        ],
    ],

    // 第三方服务配置
    'services' => [
        'openai' => [
            'api_key' => getenv('OPENAI_API_KEY'),
            'api_url' => getenv('OPENAI_API_URL') ?: 'https://api.openai.com/v1',
            'model' => getenv('OPENAI_MODEL') ?: 'gpt-3.5-turbo',
            'max_tokens' => (int) (getenv('OPENAI_MAX_TOKENS') ?: 2048),
            'temperature' => (float) (getenv('OPENAI_TEMPERATURE') ?: 0.7),
        ],
        'google' => [
            'analytics_id' => getenv('GOOGLE_ANALYTICS_ID'),
            'recaptcha_site_key' => getenv('GOOGLE_RECAPTCHA_SITE_KEY'),
            'recaptcha_secret_key' => getenv('GOOGLE_RECAPTCHA_SECRET_KEY'),
        ],
    ],

    // 功能开关
    'features' => [
        'registration' => filter_var(getenv('FEATURE_REGISTRATION'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'email_verification' => filter_var(getenv('FEATURE_EMAIL_VERIFICATION'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'password_reset' => filter_var(getenv('FEATURE_PASSWORD_RESET'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'admin_panel' => filter_var(getenv('FEATURE_ADMIN_PANEL'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'chat' => filter_var(getenv('FEATURE_CHAT'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'documents' => filter_var(getenv('FEATURE_DOCUMENTS'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
    ],
];
