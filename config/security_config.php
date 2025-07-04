<?php
/**
 * 安全系统配置文件
 * @version 1.0.0
 * @author AlingAi Team
 */

return [
    'security_level' => 'high',
    'max_login_attempts' => 5,
    'lockout_time' => 15, // 分钟
    'password_policy' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_number' => true,
        'require_special' => true
    ],
    'session_timeout' => 30, // 分钟
    'ip_whitelist' => [],
    'ip_blacklist' => [],
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
    'max_file_size' => 10 * 1024 * 1024, // 10MB
    'csrf_protection' => true,
    'xss_protection' => true,
    'sql_injection_protection' => true,
    'rate_limiting' => [
        'enabled' => true,
        'requests_per_minute' => 60
    ]
];
