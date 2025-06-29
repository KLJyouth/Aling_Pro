<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ���ݿⰲȫ����
    |--------------------------------------------------------------------------
    |
    | ��������ļ����������ݿⰲȫ��ص����ã����������ơ�SQLע�������
    | �������ơ������־�ȹ��ܵ����á�
    |
    */

    // �����ƹ�������
    'brute_force' => [
        'enabled' => true,
        'max_failed_attempts' => 5,          // ���ʧ�ܳ��Դ���
        'lockout_time' => 30,                // ����ʱ�䣨���ӣ�
        'detection_window' => 5,             // ��ⴰ�ڣ����ӣ�
        'ip_blacklist_threshold' => 10,      // IP��������ֵ
        'blacklist_duration' => 1440,        // ����������ʱ�䣨���ӣ�
    ],
    
    // SQLע���������
    'sql_injection' => [
        'enabled' => true,
        'log_suspicious_queries' => true,    // ��¼���ɲ�ѯ
        'block_suspicious_queries' => true,  // ��ֹ���ɲ�ѯ
        'alert_threshold' => 3,              // ������ֵ
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
    
    // ������������
    'connection_limits' => [
        'enabled' => true,
        'max_connections_per_ip' => 20,      // ÿIP���������
        'max_connections_total' => 100,      // �����������
        'connection_timeout' => 300,         // ���ӳ�ʱ���룩
    ],
    
    // �����־����
    'audit' => [
        'enabled' => true,
        'log_all_queries' => false,          // ��¼���в�ѯ�����棺�����������־��
        'log_write_queries' => true,         // ��¼д���ѯ��INSERT, UPDATE, DELETE��
        'log_schema_changes' => true,        // ��¼�ܹ������CREATE, ALTER, DROP��
        'log_admin_queries' => true,         // ��¼����Ա��ѯ
        'retention_days' => 90,              // ��־��������
    ],
    
    // ����ǽ����
    'firewall' => [
        'enabled' => true,
        'default_rules' => [
            [
                'rule_type' => 'QUERY_PATTERN',
                'rule_value' => 'DROP DATABASE',
                'action' => 'DENY',
                'description' => '��ֹɾ�����ݿ����'
            ],
            [
                'rule_type' => 'QUERY_PATTERN',
                'rule_value' => 'DROP TABLE',
                'action' => 'DENY',
                'description' => '��ֹɾ�������'
            ],
            [
                'rule_type' => 'QUERY_PATTERN',
                'rule_value' => 'TRUNCATE TABLE',
                'action' => 'DENY',
                'description' => '��ֹ��ձ����'
            ],
            [
                'rule_type' => 'QUERY_PATTERN',
                'rule_value' => 'GRANT ALL',
                'action' => 'DENY',
                'description' => '��ֹ��������Ȩ��'
            ]
        ],
    ],
    
    // �������
    'monitoring' => [
        'enabled' => true,
        'check_interval' => 5,               // ����������ӣ�
        'kill_long_queries' => true,         // ��ֹ��ʱ�����еĲ�ѯ
        'monitor_changes' => true,           // ������ݿ�ṹ�仯
        'performance_logging' => true,       // ������־��¼
    ],
    
    // ��������
    'backup' => [
        'enabled' => true,
        'auto_backup' => true,               // �Զ�����
        'backup_interval' => 1440,           // ���ݼ�������ӣ�
        'backup_retention' => 7,             // ���ݱ�������
        'compression' => true,               // ����ѹ��
        'encryption' => false,               // ���ü���
    ],
    
    // ©��ɨ������
    'vulnerability_scan' => [
        'enabled' => true,
        'scan_interval' => 10080,            // ɨ���������ӣ�Ĭ��7�죩
        'scan_types' => [
            'privilege_escalation',
            'sql_injection',
            'weak_passwords',
            'missing_patches',
            'configuration_issues',
        ],
    ],
];
