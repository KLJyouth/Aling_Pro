<?php

return [
    /*
    |--------------------------------------------------------------------------
    | �����ΰ�ȫ�������
    |--------------------------------------------------------------------------
    |
    | �������ļ����������ΰ�ȫ��ܵ���������ѡ�
    |
    */

    // �Ƿ����������ΰ�ȫ���
    'enabled' => env('ZERO_TRUST_ENABLED', true),
    
    // ����������ֵ
    'thresholds' => [
        'suspicious' => env('ZERO_TRUST_SUSPICIOUS_THRESHOLD', 50), // ���ɻ��ֵ
        'dangerous' => env('ZERO_TRUST_DANGEROUS_THRESHOLD', 80),   // Σ�ջ��ֵ
    ],
    
    // �豸������
    'device_binding' => [
        'enabled' => env('DEVICE_BINDING_ENABLED', true),
        'max_devices' => env('MAX_DEVICES_PER_USER', 5),
        'require_verification' => env('REQUIRE_DEVICE_VERIFICATION', true),
    ],
    
    // ��������֤����
    'mfa' => [
        'enabled' => env('MFA_ENABLED', true),
        'enforce' => env('ENFORCE_MFA', false),
        'session_lifetime' => env('MFA_SESSION_LIFETIME', 60 * 24), // ����
        'methods' => [
            'app' => env('MFA_APP_ENABLED', true),
            'sms' => env('MFA_SMS_ENABLED', true),
            'email' => env('MFA_EMAIL_ENABLED', true),
            'fingerprint' => env('MFA_FINGERPRINT_ENABLED', true),
        ],
    ],
    
    // ����λ�ð�ȫ����
    'geolocation' => [
        'enabled' => env('GEO_SECURITY_ENABLED', true),
        'high_risk_countries' => explode(',', env('HIGH_RISK_COUNTRIES', 'North Korea,Iran,Syria,Sudan,Cuba')),
        'vpn_detection' => env('VPN_DETECTION_ENABLED', true),
    ],
    
    // ���簲ȫ����
    'network' => [
        'enforce_https' => env('ENFORCE_HTTPS', true),
        'min_tls_version' => env('MIN_TLS_VERSION', 'TLSv1.2'),
    ],
    
    // ��Ϊ��������
    'behavior' => [
        'enabled' => env('BEHAVIOR_ANALYSIS_ENABLED', true),
        'rate_limit' => [
            'enabled' => env('RATE_LIMIT_ENABLED', true),
            'max_requests' => env('RATE_LIMIT_MAX_REQUESTS', 60),
            'window_minutes' => env('RATE_LIMIT_WINDOW_MINUTES', 1),
        ],
    ],
    
    // API��ȫ����
    'api' => [
        'key_rotation_days' => env('API_KEY_ROTATION_DAYS', 90),
        'enforce_key_rotation' => env('ENFORCE_API_KEY_ROTATION', true),
    ],
    
    // ֧����ȫ����
    'payment' => [
        'require_mfa' => env('PAYMENT_REQUIRE_MFA', true),
        'require_device_verification' => env('PAYMENT_REQUIRE_DEVICE_VERIFICATION', true),
        'risk_threshold' => env('PAYMENT_RISK_THRESHOLD', 60),
    ],
    
    // ��־����
    'logging' => [
        'enabled' => env('SECURITY_LOGGING_ENABLED', true),
        'retention_days' => env('SECURITY_LOG_RETENTION_DAYS', 90),
    ],
    
    // ��������
    'alerts' => [
        'enabled' => env('SECURITY_ALERTS_ENABLED', true),
        'email_notifications' => env('ALERT_EMAIL_NOTIFICATIONS', true),
        'admin_emails' => explode(',', env('SECURITY_ADMIN_EMAILS', 'admin@example.com')),
    ],
];
