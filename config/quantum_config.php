<?php
/**
 * 量子加密系统配置文件
 * @version 1.0.0
 * @author AlingAi Team
 */

return [
    // 量子加密系统基本配置
    'quantum_encryption_enabled' => true,
    'key_rotation_interval' => 24, // 密钥轮换间隔（小时）
    'quantum_algorithms' => ['SM2', 'SM3', 'SM4'], // 使用的量子算法
    'qkd_protocol' => 'BB84', // 量子密钥分发协议
    
    // 监控配置
    'monitoring_interval' => 15, // 监控间隔（分钟）
    'alert_threshold' => 'medium', // 警报阈值（low, medium, high）
    
    // 量子随机数生成
    'quantum_random_source' => 'hybrid', // 量子随机源 (hardware, simulated, hybrid)
    
    // 密钥安全
    'key_storage_protection' => 'hardware', // 密钥存储保护 (software, hardware)
    
    // 入侵检测
    'intrusion_detection' => true, // 是否启用入侵检测
    
    // 日志记录
    'logging_level' => 'detailed', // 日志级别 (basic, standard, detailed)
    
    // 高级设置
    'advanced' => [
        'error_correction' => true, // 是否启用错误校正
        'privacy_amplification' => true, // 是否启用隐私放大
        'authentication_method' => 'quantum', // 认证方法 (classical, quantum, hybrid)
        'entropy_source' => 'quantum', // 熵源 (classical, quantum, hybrid)
        'post_quantum_algorithms' => [ // 后量子算法
            'lattice_based' => true,
            'hash_based' => true,
            'multivariate' => false,
            'code_based' => false
        ]
    ],
    
    // 系统集成
    'integration' => [
        'api_encryption' => true, // API加密
        'database_encryption' => true, // 数据库加密
        'file_encryption' => true, // 文件加密
        'communication_encryption' => true // 通信加密
    ],
    
    // 性能优化
    'performance' => [
        'cache_encrypted_data' => false, // 是否缓存加密数据
        'parallel_processing' => true, // 是否启用并行处理
        'hardware_acceleration' => false // 是否启用硬件加速
    ]
];
