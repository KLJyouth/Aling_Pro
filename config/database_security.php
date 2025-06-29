<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 数据库安全配置
    |--------------------------------------------------------------------------
    |
    | 这个配置文件包含了数据库安全相关的设置，包括防爆破、SQL注入防御、
    | 连接限制、审计日志等功能的配置。
    |
    */

    // 防爆破攻击配置
    'brute_force' => [
        'enabled' => true,
        'max_failed_attempts' => 5,          // 最大失败尝试次数
        'lockout_time' => 30,                // 锁定时间（分钟）
        'detection_window' => 5,             // 检测窗口（分钟）
        'ip_blacklist_threshold' => 10,      // IP黑名单阈值
        'blacklist_duration' => 1440,        // 黑名单持续时间（分钟）
    ],
    
    // SQL注入防御配置
    'sql_injection' => [
        'enabled' => true,
        'log_suspicious_queries' => true,    // 记录可疑查询
        'block_suspicious_queries' => true,  // 阻止可疑查询
        'alert_threshold' => 3,              // 警报阈值
        'patterns' => [
            '/\s*SELECT\s+.*\s+FROM\s+information_schema\./i',
            '/\s*SELECT\s+.*\s+FROM\s+mysql\./i',
            '/\s*UNION\s+SELECT\s+/i',
            '/\s*OR\s+1\s*=\s*1\s*/i',
            '/\s*OR\s+\'1\'\s*=\s*\'1\'\s*/i',
            '/\s*DROP\s+TABLE\s+/i',
            '/\s*DROP\s+DATABASE\s+/i',
            '/\s*DELETE\s+FROM\s+/i',
            '/\s*INSERT\s+INTO\s+.*\s+SELECT\s+/i',
            '/\s*SLEEP\s*\(/i',
            '/\s*BENCHMARK\s*\(/i',
            '/\s*LOAD_FILE\s*\(/i',
            '/\s*INTO\s+OUTFILE\s*/i',
            '/\s*INTO\s+DUMPFILE\s*/i',
        ],
    ],
    
    // 连接限制配置
    'connection_limits' => [
        'enabled' => true,
        'max_connections_per_ip' => 20,      // 每IP最大连接数
        'max_connections_total' => 100,      // 总最大连接数
        'connection_timeout' => 300,         // 连接超时（秒）
    ],
    
    // 审计日志配置
    'audit' => [
        'enabled' => true,
        'log_all_queries' => false,          // 记录所有查询（警告：会产生大量日志）
        'log_write_queries' => true,         // 记录写入查询（INSERT, UPDATE, DELETE）
        'log_schema_changes' => true,        // 记录架构变更（CREATE, ALTER, DROP）
        'log_admin_queries' => true,         // 记录管理员查询
        'retention_days' => 90,              // 日志保留天数
    ],
    
    // 防火墙配置
    'firewall' => [
        'enabled' => true,
        'default_rules' => [
            [
                'rule_type' => 'QUERY_PATTERN',
                'rule_value' => 'DROP DATABASE',
                'action' => 'DENY',
                'description' => '阻止删除数据库操作'
            ],
            [
                'rule_type' => 'QUERY_PATTERN',
                'rule_value' => 'DROP TABLE',
                'action' => 'DENY',
                'description' => '阻止删除表操作'
            ],
            [
                'rule_type' => 'QUERY_PATTERN',
                'rule_value' => 'TRUNCATE TABLE',
                'action' => 'DENY',
                'description' => '阻止清空表操作'
            ],
            [
                'rule_type' => 'QUERY_PATTERN',
                'rule_value' => 'GRANT ALL',
                'action' => 'DENY',
                'description' => '阻止授予所有权限'
            ]
        ],
    ],
    
    // 监控配置
    'monitoring' => [
        'enabled' => true,
        'check_interval' => 5,               // 检查间隔（分钟）
        'kill_long_queries' => true,         // 终止长时间运行的查询
        'monitor_changes' => true,           // 监控数据库结构变化
        'performance_logging' => true,       // 性能日志记录
    ],
    
    // 备份配置
    'backup' => [
        'enabled' => true,
        'auto_backup' => true,               // 自动备份
        'backup_interval' => 1440,           // 备份间隔（分钟）
        'backup_retention' => 7,             // 备份保留天数
        'compression' => true,               // 启用压缩
        'encryption' => false,               // 启用加密
    ],
    
    // 漏洞扫描配置
    'vulnerability_scan' => [
        'enabled' => true,
        'scan_interval' => 10080,            // 扫描间隔（分钟，默认7天）
        'scan_types' => [
            'privilege_escalation',
            'sql_injection',
            'weak_passwords',
            'missing_patches',
            'configuration_issues',
        ],
    ],
];
