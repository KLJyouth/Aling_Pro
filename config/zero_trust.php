<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 零信任安全框架配置
    |--------------------------------------------------------------------------
    |
    | 此配置文件包含零信任安全框架的所有配置选项。
    |
    */

    // 是否启用零信任安全框架
    'enabled' => env('ZERO_TRUST_ENABLED', true),
    
    // 风险评分阈值
    'thresholds' => [
        'suspicious' => env('ZERO_TRUST_SUSPICIOUS_THRESHOLD', 50), // 可疑活动阈值
        'dangerous' => env('ZERO_TRUST_DANGEROUS_THRESHOLD', 80),   // 危险活动阈值
    ],
    
    // 设备绑定配置
    'device_binding' => [
        'enabled' => env('DEVICE_BINDING_ENABLED', true),
        'max_devices' => env('MAX_DEVICES_PER_USER', 5),
        'require_verification' => env('REQUIRE_DEVICE_VERIFICATION', true),
    ],
    
    // 多因素认证配置
    'mfa' => [
        'enabled' => env('MFA_ENABLED', true),
        'enforce' => env('ENFORCE_MFA', false),
        'session_lifetime' => env('MFA_SESSION_LIFETIME', 60 * 24), // 分钟
        'methods' => [
            'app' => env('MFA_APP_ENABLED', true),
            'sms' => env('MFA_SMS_ENABLED', true),
            'email' => env('MFA_EMAIL_ENABLED', true),
            'fingerprint' => env('MFA_FINGERPRINT_ENABLED', true),
        ],
    ],
    
    // 地理位置安全配置
    'geolocation' => [
        'enabled' => env('GEO_SECURITY_ENABLED', true),
        'high_risk_countries' => explode(',', env('HIGH_RISK_COUNTRIES', 'North Korea,Iran,Syria,Sudan,Cuba')),
        'vpn_detection' => env('VPN_DETECTION_ENABLED', true),
    ],
    
    // 网络安全配置
    'network' => [
        'enforce_https' => env('ENFORCE_HTTPS', true),
        'min_tls_version' => env('MIN_TLS_VERSION', 'TLSv1.2'),
    ],
    
    // 行为分析配置
    'behavior' => [
        'enabled' => env('BEHAVIOR_ANALYSIS_ENABLED', true),
        'rate_limit' => [
            'enabled' => env('RATE_LIMIT_ENABLED', true),
            'max_requests' => env('RATE_LIMIT_MAX_REQUESTS', 60),
            'window_minutes' => env('RATE_LIMIT_WINDOW_MINUTES', 1),
        ],
    ],
    
    // API安全配置
    'api' => [
        'key_rotation_days' => env('API_KEY_ROTATION_DAYS', 90),
        'enforce_key_rotation' => env('ENFORCE_API_KEY_ROTATION', true),
    ],
    
    // 支付安全配置
    'payment' => [
        'require_mfa' => env('PAYMENT_REQUIRE_MFA', true),
        'require_device_verification' => env('PAYMENT_REQUIRE_DEVICE_VERIFICATION', true),
        'risk_threshold' => env('PAYMENT_RISK_THRESHOLD', 60),
    ],
    
    // 日志配置
    'logging' => [
        'enabled' => env('SECURITY_LOGGING_ENABLED', true),
        'retention_days' => env('SECURITY_LOG_RETENTION_DAYS', 90),
    ],
    
    // 警报配置
    'alerts' => [
        'enabled' => env('SECURITY_ALERTS_ENABLED', true),
        'email_notifications' => env('ALERT_EMAIL_NOTIFICATIONS', true),
        'admin_emails' => explode(',', env('SECURITY_ADMIN_EMAILS', 'admin@example.com')),
    ],
];
