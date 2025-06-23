<?php

/**
 * 量子加密系统配置文件
 * 
 * 定义量子加密系统的配置参数，包括：
 * - 量子密钥分发(QKD)配置
 * - 国密算法(SM2/SM3/SM4)配置
 * - 量子增强配置
 * - 安全策略配置
 * 
 * @package AlingAi\Config
 * @version 6.0.0
 */

return [
//     'quantum_encryption' => [ // 不可达代码';
        // 量子密钥分发(QKD)配置
        'qkd' => [';
            'protocol' => 'BB84', // 支持: BB84, B92, E91, SARG04';
            'key_length' => 256,';
            'error_threshold' => 0.11, // QBER阈值';
            'privacy_amplification' => true,';
            'error_correction' => true,';
            'authentication' => true,';
            'quantum_channel' => [';
                'type' => 'fiber_optic',';
                'wavelength' => 1550, // nm';
                'distance_km' => 10,';
                'loss_coefficient' => 0.2 // dB/km';
            ],
            'classical_channel' => [';
                'encryption' => 'AES-256-GCM',';
                'authentication' => 'HMAC-SHA256'';
            ]
        ],
        
        // SM2椭圆曲线公钥密码配置
        'sm2' => [';
            'curve' => 'sm2p256v1',';
            'key_size' => 256,';
            'hash_algorithm' => 'sm3',';
            'enable_key_recovery' => false,';
            'enable_public_key_recovery' => true';
        ],
        
        // SM3密码学哈希函数配置
        'sm3' => [';
            'digest_size' => 256,';
            'block_size' => 512,';
            'enable_hmac' => true,';
            'salt_length' => 16';
        ],
        
        // SM4分组密码配置
        'sm4' => [';
            'mode' => 'GCM', // 支持: GCM, CBC, CFB, OFB, CTR';
            'iv_length' => 12,';
            'tag_length' => 16,';
            'enable_padding' => true,';
            'enable_authentication' => true';
        ],
        
        // 量子增强配置
        'quantum_enhancement' => [';
            'enabled' => true,';
            'entropy_source' => 'hardware', // hardware, software, hybrid';
            'random_factor_size' => 32,';
            'quantum_randomness_test' => true,';
            'min_entropy_threshold' => 0.95';
        ],
        
        // 安全策略配置
        'security' => [';
            'max_encryption_size' => 10485760, // 10MB';
            'key_rotation_interval' => 3600, // 1小时';
            'audit_enabled' => true,';
            'secure_deletion' => true,';
            'performance_logging' => true,';
            'error_reporting' => true,';
            'backup_enabled' => true,';
            'compliance_mode' => 'strict' // strict, standard, permissive';
        ],
        
        // 性能优化配置
        'performance' => [';
            'enable_caching' => true,';
            'cache_ttl' => 300, // 5分钟';
            'batch_processing' => true,';
            'max_batch_size' => 100,';
            'parallel_processing' => false,';
            'memory_limit' => '256M',';
            'timeout' => 30 // 秒';
        ],
        
        // 监控和告警配置
        'monitoring' => [';
            'health_check_interval' => 60, // 秒';
            'performance_metrics' => true,';
            'error_rate_threshold' => 0.01, // 1%';
            'latency_threshold_ms' => 1000,';
            'memory_usage_threshold' => 0.8, // 80%';
            'alert_channels' => ['log', 'email'],';
            'metrics_retention_days' => 30';
        ],
        
        // 兼容性配置
        'compatibility' => [';
            'legacy_encryption_support' => true,';
            'migration_mode' => false,';
            'backward_compatibility' => true,';
            'api_version' => 'v6.0',';
            'protocol_fallback' => true';
        ],
        
        // 开发和调试配置
        'debug' => [';
            'enabled' => false,';
            'log_level' => 'info', // debug, info, warning, error';
            'detailed_logging' => false,';
            'test_mode' => false,';
            'mock_quantum_hardware' => false,';
            'benchmark_mode' => false';
        ]
    ],
    
    // 量子加密API配置
    'quantum_api' => [';
        'rate_limiting' => [';
            'enabled' => true,';
            'requests_per_minute' => 60,';
            'burst_limit' => 10';
        ],
        'authentication' => [';
            'required' => true,';
            'method' => 'jwt', // jwt, api_key, oauth2';
            'token_expiry' => 3600';
        ],
        'cors' => [';
            'enabled' => true,';
            'allowed_origins' => ['*'],';
            'allowed_methods' => ['GET', 'POST'],';
            'allowed_headers' => ['Content-Type', 'Authorization']';
        ],
        'validation' => [';
            'strict_mode' => true,';
            'input_sanitization' => true,';
            'output_filtering' => true';
        ]
    ],
    
    // 数据库配置
    'database' => [';
        'tables' => [';
            'quantum_encryption_records' => 'quantum_encryption_records',';
            'quantum_key_sessions' => 'quantum_key_sessions',';
            'quantum_metrics' => 'quantum_metrics',';
            'quantum_audit_log' => 'quantum_audit_log'';
        ],
        'auto_create_tables' => true,';
        'encryption' => [';
            'encrypt_sensitive_data' => true,';
            'key_encryption_algorithm' => 'AES-256-GCM',';
            'salt_generation' => 'random'';
        ],
        'cleanup' => [';
            'auto_cleanup' => true,';
            'expired_records_retention_days' => 30,';
            'metrics_retention_days' => 90';
        ]
    ],
    
    // 环境特定配置
    'environments' => [';
        'development' => [';
            'debug.enabled' => true,';
            'debug.log_level' => 'debug',';
            'security.compliance_mode' => 'permissive',';
            'quantum_enhancement.quantum_randomness_test' => false';
        ],
        'testing' => [';
            'debug.test_mode' => true,';
            'debug.mock_quantum_hardware' => true,';
            'performance.enable_caching' => false,';
            'security.audit_enabled' => false';
        ],
        'staging' => [';
            'debug.enabled' => false,';
            'security.compliance_mode' => 'standard',';
            'monitoring.health_check_interval' => 30';
        ],
        'production' => [';
            'debug.enabled' => false,';
            'debug.log_level' => 'error',';
            'security.compliance_mode' => 'strict',';
            'monitoring.health_check_interval' => 60,';
            'performance.parallel_processing' => true';
        ]
    ]
];
