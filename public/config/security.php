<?php
/**
 * AlingAi Pro - 零信任安全配置
 * 
 * 配置系统的安全策略，包括密码策略、会话安全、IP限制等
 */

return [
    // 零信任策略
    'zero_trust' => [
        'enabled' => true,
        
        // 持续验证 - 即使在会话中也要定期验证用户身份
        'continuous_verification' => [
            'enabled' => true,
            'verify_interval' => 30, // 分钟
            'sensitive_actions_require_reauth' => true,
        ],
        
        // 最小权限原则
        'least_privilege' => [
            'enabled' => true,
            'default_role' => 'user',
            'role_expiry' => [
                'admin' => 24, // 小时
                'editor' => 72, // 小时
            ],
        ],
        
        // 设备信任
        'device_trust' => [
            'enabled' => true,
            'require_device_verification' => true,
            'max_devices_per_user' => 5,
            'unknown_device_action' => 'verify', // verify, block, notify
        ],
    ],
    
    // 密码策略
    'password_policy' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_number' => true,
        'require_special_char' => true,
        'max_age_days' => 90, // 密码过期时间
        'prevent_common_passwords' => true,
        'prevent_reuse_count' => 5, // 防止重复使用最近的5个密码
    ],
    
    // 会话安全
    'session' => [
        'secure' => true,
        'http_only' => true,
        'same_site' => 'Lax', // None, Lax, Strict
        'lifetime' => 120, // 分钟
        'idle_timeout' => 30, // 分钟
        'regenerate_id' => true,
        'single_device' => false, // 是否限制单设备登录
    ],
    
    // 双因素认证
    'two_factor' => [
        'enabled' => true,
        'methods' => ['totp', 'email', 'sms'],
        'default_method' => 'totp',
        'enforce_for_roles' => ['admin', 'editor'],
        'remember_device_days' => 30,
    ],
    
    // IP限制
    'ip_restrictions' => [
        'enabled' => false,
        'whitelist' => [],
        'blacklist' => [],
        'geo_restrictions' => [
            'enabled' => false,
            'allowed_countries' => ['CN', 'US', 'JP'],
        ],
    ],
    
    // 请求限制
    'rate_limiting' => [
        'enabled' => true,
        'login_attempts' => [
            'max_attempts' => 5,
            'decay_minutes' => 10,
            'lockout_minutes' => 30,
        ],
        'api_requests' => [
            'max_requests' => 60,
            'decay_minutes' => 1,
        ],
    ],
    
    // CSRF保护
    'csrf' => [
        'enabled' => true,
        'token_lifetime' => 120, // 分钟
    ],
    
    // XSS保护
    'xss' => [
        'enabled' => true,
        'filter_input' => true,
        'filter_output' => true,
        'content_security_policy' => true,
    ],
    
    // SQL注入保护
    'sql_injection' => [
        'enabled' => true,
        'prepared_statements' => true,
        'input_validation' => true,
    ],
    
    // 日志记录
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error
        'log_login_attempts' => true,
        'log_admin_actions' => true,
        'log_api_requests' => true,
        'log_sensitive_data_access' => true,
    ],
    
    // 通知
    'notifications' => [
        'security_events' => [
            'admin_login' => true,
            'failed_login_attempts' => true,
            'password_changed' => true,
            'two_factor_disabled' => true,
            'new_device_login' => true,
        ],
        'channels' => ['email', 'sms', 'push'],
        'default_channel' => 'email',
    ],
];
