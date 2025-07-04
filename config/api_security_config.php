<?php
/**
 * API安全配置文件
 * @version 1.0.0
 * @author AlingAi Team
 */

return [
    // 扫描配置
    'scan_interval' => 60, // 扫描间隔（分钟）
    'alert_threshold' => 'medium', // 警报阈值（low, medium, high）
    
    // API安全设置
    'api_rate_limiting' => true, // 是否启用速率限制
    'api_authentication_required' => true, // 是否要求认证
    'api_logging_level' => 'detailed', // 日志级别 (basic, standard, detailed)
    'api_monitoring_enabled' => true, // 是否启用监控
    
    // API类别配置
    'api_categories' => [
        'system' => true,   // 系统API
        'local' => true,    // 本地API
        'user' => true,     // 用户API
        'external' => true  // 外部API
    ],
    
    // 安全扫描配置
    'vulnerability_scan_enabled' => true, // 是否启用漏洞扫描
    'threat_detection_enabled' => true, // 是否启用威胁检测
    'auto_block_threats' => false, // 是否自动阻止威胁
    
    // 高级设置
    'advanced' => [
        'jwt_validation' => true, // JWT验证
        'oauth_validation' => true, // OAuth验证
        'api_key_rotation' => true, // API密钥轮换
        'request_validation' => true, // 请求验证
        'response_validation' => true, // 响应验证
        'payload_encryption' => true // 负载加密
    ],
    
    // 量子加密集成
    'quantum_integration' => [
        'enabled' => true, // 是否启用量子加密集成
        'encryption_level' => 'high', // 加密级别 (standard, high, ultra)
        'endpoints' => [ // 需要量子加密的端点
            '/api/v2/quantum/*',
            '/api/v3/secure/*',
            '/api/admin/*'
        ]
    ]
];
