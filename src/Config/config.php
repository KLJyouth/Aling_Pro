<?php
/**
 * 基本配置文件
 */

return [
    // 数据库配置
    'database' => [
        'host' => 'localhost',
        'name' => 'alingai_pro',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'engine' => 'sqlite', // 使用SQLite进行测试
        'path' => __DIR__ . '/../../storage/database/alingai.db'
    ],
    
    // JWT配置
    'jwt' => [
        'secret' => 'your-jwt-secret-key-change-this-in-production',
        'issuer' => 'alingai-pro',
        'audience' => 'alingai-users',
        'expiration' => 3600, // 1小时
        'algorithm' => 'HS256'
    ],
    
    // 安全配置
    'security' => [
        'ip_whitelist' => null, // null表示允许所有IP
        'rate_limit' => [
            'enabled' => false, // 测试时禁用速率限制
            'max_requests' => 100,
            'window' => 3600
        ]
    ],
    
    // CORS配置
    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With']
    ],
    
    // 应用配置
    'app' => [
        'name' => 'AlingAi Pro',
        'version' => '1.0.0',
        'environment' => 'development',
        'debug' => true
    ]
];
