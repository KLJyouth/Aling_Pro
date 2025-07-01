<?php
/**
 * 安全配置文件
 */

return [
    // 会话安全配置
    'session' => [
        'name' => 'admin_session',
        'lifetime' => 7200, // 2小时
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    
    // 密码策略
    'password' => [
        'min_length' => 8,
        'require_special_chars' => true,
        'require_numbers' => true,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'max_attempts' => 5,
        'lockout_time' => 300, // 5分钟
    ],
    
    // API令牌配置
    'token' => [
        'lifetime' => 86400, // 24小时
        'refresh_time' => 3600, // 1小时
        'secret' => getenv('JWT_SECRET') ?: null,
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
    
    // 内容安全策略
    'csp' => [
        'enabled' => true,
        'policy' => [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", "cdn.jsdelivr.net", "cdn.datatables.net"],
            'style-src' => ["'self'", "'unsafe-inline'", "cdn.jsdelivr.net", "cdn.datatables.net"],
            'img-src' => ["'self'", "data:", "cdn.jsdelivr.net"],
            'font-src' => ["'self'", "cdn.jsdelivr.net"],
            'connect-src' => ["'self'"],
            'frame-src' => ["'none'"],
            'object-src' => ["'none'"],
        ]
    ],
    
    // 安全头部
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
    ],
    
    // 登录安全
    'login' => [
        'throttle' => [
            'enabled' => true,
            'max_attempts' => 5,
            'decay_minutes' => 5,
        ],
        'two_factor' => [
            'enabled' => false,
        ]
    ],
    
    // 加密配置
    'encryption' => [
        'algo' => 'AES-256-CBC',
    ]
]; 