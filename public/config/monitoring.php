<?php
/**
 * AlingAi API监控系统配置
 */
return [
    /**
     * 数据库配�?
     */
    'database' => [
        'driver' => 'pgsql', // 使用TimescaleDB (PostgreSQL扩展)
        'host' => env('MONITORING_DB_HOST', 'localhost'],
        'port' => env('MONITORING_DB_PORT', '5432'],
        'database' => env('MONITORING_DB_DATABASE', 'alingai_monitoring'],
        'username' => env('MONITORING_DB_USERNAME', 'postgres'],
        'password' => env('MONITORING_DB_PASSWORD', ''],
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
    ], 
    
    /**
     * 告警配置
     */
    'alerts' => [
        'email' => [
            'enabled' => env('MONITORING_EMAIL_ALERTS_ENABLED', false],
            'from_email' => env('MONITORING_EMAIL_FROM', 'monitoring@alingai.com'],
            'subject_prefix' => env('MONITORING_EMAIL_SUBJECT_PREFIX', '[AlingAi监控告警]'],
            'smtp_host' => env('MONITORING_SMTP_HOST', 'smtp.example.com'],
            'smtp_port' => env('MONITORING_SMTP_PORT', 587],
            'smtp_username' => env('MONITORING_SMTP_USERNAME', ''],
            'smtp_password' => env('MONITORING_SMTP_PASSWORD', ''],
            'smtp_encryption' => env('MONITORING_SMTP_ENCRYPTION', 'tls'],
            'recipients' => explode(',', env('MONITORING_EMAIL_RECIPIENTS', '')],
            'severity_recipients' => [
                'critical' => explode(',', env('MONITORING_EMAIL_CRITICAL_RECIPIENTS', '')],
                'high' => explode(',', env('MONITORING_EMAIL_HIGH_RECIPIENTS', '')],
            ], 
        ], 
        'sms' => [
            'enabled' => env('MONITORING_SMS_ALERTS_ENABLED', false],
            'provider' => env('MONITORING_SMS_PROVIDER', 'aliyun'],
            'api_key' => env('MONITORING_SMS_API_KEY', ''],
            'api_secret' => env('MONITORING_SMS_API_SECRET', ''],
            'sign_name' => env('MONITORING_SMS_SIGN_NAME', ''],
            'template_code' => env('MONITORING_SMS_TEMPLATE_CODE', ''],
            'recipients' => explode(',', env('MONITORING_SMS_RECIPIENTS', '')],
            'severity_recipients' => [
                'critical' => explode(',', env('MONITORING_SMS_CRITICAL_RECIPIENTS', '')],
                'high' => explode(',', env('MONITORING_SMS_HIGH_RECIPIENTS', '')],
            ], 
        ], 
        'webhook' => [
            'enabled' => env('MONITORING_WEBHOOK_ALERTS_ENABLED', false],
            'url' => env('MONITORING_WEBHOOK_URL', ''],
            'method' => env('MONITORING_WEBHOOK_METHOD', 'POST'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => env('MONITORING_WEBHOOK_AUTH', ''],
            ], 
        ], 
        'websocket' => [
            'enabled' => env('MONITORING_WEBSOCKET_ALERTS_ENABLED', true],
            'host' => env('MONITORING_WEBSOCKET_HOST', '0.0.0.0'],
            'port' => env('MONITORING_WEBSOCKET_PORT', 8080],
        ], 
        'thresholds' => [
            'low' => [
                'channels' => ['log'], 
                'throttle_interval' => 3600, // 1小时
            ], 
            'medium' => [
                'channels' => ['log', 'email'], 
                'throttle_interval' => 1800, // 30分钟
            ], 
            'high' => [
                'channels' => ['log', 'email', 'sms'], 
                'throttle_interval' => 600, // 10分钟
            ], 
            'critical' => [
                'channels' => ['log', 'email', 'sms', 'webhook', 'websocket'], 
                'throttle_interval' => 300, // 5分钟
            ], 
        ], 
    ], 
    
    /**
     * 健康检查配�?
     */
    'health_checks' => [
        'enabled' => true,
        'interval' => 60, // 默认检查间�?�?
        'timeout' => 5, // 默认超时时间(�?
        'retries' => 3, // 默认重试次数
    ], 
    
    /**
     * 调度器配�?
     */
    'scheduler' => [
        'autostart' => false, // 应用启动时是否自动启动调度器
        'pid_file' => storage_path('logs/monitor.pid'],
        'log_file' => storage_path('logs/monitor.log'],
    ], 
    
    /**
     * 服务设置
     */
    'service' => [
        'providers' => [
            'aliyun' => [
                'base_url' => 'https://api.aliyun.com/',
                'timeout' => 30,
                'auth_type' => 'api_key',
                'auth' => [
                    'key_name' => 'AccessKeyId',
                    'key_value' => env('ALIYUN_ACCESS_KEY_ID', ''],
                    'key_in' => 'header',
                ], 
            ], 
            'tencent' => [
                'base_url' => 'https://api.tencent.com/',
                'timeout' => 30,
                'auth_type' => 'api_key',
                'auth' => [
                    'key_name' => 'SecretId',
                    'key_value' => env('TENCENT_SECRET_ID', ''],
                    'key_in' => 'header',
                ], 
            ], 
            // 添加更多预配置的API提供�?
        ], 
    ], 
    
    /**
     * 数据保留策略
     */
    'data_retention' => [
        'metrics' => 30, // 保留指标数据的天�?
        'availability' => 90, // 保留可用性数据的天数
        'alerts' => 90, // 保留告警历史的天�?
    ], 
    
    /**
     * 界面配置
     */
    'ui' => [
        'theme' => 'light', // light, dark
        'refresh_interval' => 30, // 仪表盘自动刷新间�?�?
        'chart_data_points' => 100, // 图表中显示的数据点数�?
    ], 
]; 
