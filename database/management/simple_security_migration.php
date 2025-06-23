<?php
/**
 * 简化版安全监控数据库迁移脚本
 */

class SimpleSecurityMonitoringMigration {
    private PDO $database;
    private string $logFile;

    public function __construct() {
        $this->logFile = __DIR__ . '/logs/migration_' . date('Y-m-d_H-i-s') . '.log';
        
        // 创建logs目录
        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }        // 加载环境变量
        $this->loadEnvFile();
        
        // 数据库配置
        $config = [
            'host' => $_ENV['DB_HOST'] ?? '111.180.205.70',
            'dbname' => $_ENV['DB_DATABASE'] ?? 'alingai',
            'username' => $_ENV['DB_USERNAME'] ?? 'AlingAi',
            'password' => $_ENV['DB_PASSWORD'] ?? 'e5bjzeWCr7k38TrZ',
            'port' => $_ENV['DB_PORT'] ?? 3306
        ];

        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
            $this->database = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $this->log("数据库连接成功");        } catch (Exception $e) {
            $this->log("数据库连接失败: " . $e->getMessage());
            echo "❌ 数据库连接失败: " . $e->getMessage() . "\n";
            echo "请检查数据库配置和连接\n";
            exit(1);
        }
    }

    /**
     * 加载环境文件
     */
    private function loadEnvFile(): void {
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }

    /**
     * 运行安全监控表迁移
     */
    public function runMigration(): bool {
        try {
            $this->log("开始执行安全监控数据库迁移...");
            echo "🚀 开始安全监控系统数据库迁移...\n";
            echo str_repeat("=", 50) . "\n";

            // 创建所有表
            $this->createTables();
            
            // 创建视图
            $this->createViews();
            
            // 插入默认数据
            $this->insertDefaultData();
            
            // 创建索引
            $this->createIndexes();

            echo "\n" . str_repeat("=", 50) . "\n";
            echo "🎉 数据库迁移完成！\n";
            $this->log("数据库迁移完成");
            return true;

        } catch (Exception $e) {
            $this->log("迁移失败: " . $e->getMessage());
            echo "❌ 迁移失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 创建数据库表
     */
    private function createTables(): void {
        $tables = [
            'security_logs' => "
                CREATE TABLE IF NOT EXISTS security_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                    level ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') DEFAULT 'INFO',
                    source VARCHAR(100) NOT NULL,
                    message TEXT NOT NULL,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    session_id VARCHAR(128),
                    additional_data JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'threat_detections' => "
                CREATE TABLE IF NOT EXISTS threat_detections (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    detection_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                    threat_type VARCHAR(50) NOT NULL,
                    threat_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
                    source_ip VARCHAR(45) NOT NULL,
                    target_resource VARCHAR(255),
                    attack_vector VARCHAR(100),
                    risk_score INT DEFAULT 0,
                    geographic_info JSON,
                    blocked BOOLEAN DEFAULT FALSE,
                    response_action VARCHAR(100),
                    raw_data JSON,
                    INDEX idx_detection_time (detection_time),
                    INDEX idx_source_ip (source_ip),
                    INDEX idx_threat_type (threat_type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'security_blacklist' => "
                CREATE TABLE IF NOT EXISTS security_blacklist (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip VARCHAR(45) NOT NULL UNIQUE,
                    reason VARCHAR(255) NOT NULL,
                    threat_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
                    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at DATETIME,
                    is_active BOOLEAN DEFAULT TRUE,
                    detection_count INT DEFAULT 1,
                    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
                    geographic_info JSON,
                    INDEX idx_ip (ip),
                    INDEX idx_expires_at (expires_at),
                    INDEX idx_is_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'network_traffic_stats' => "
                CREATE TABLE IF NOT EXISTS network_traffic_stats (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                    total_requests INT DEFAULT 0,
                    blocked_requests INT DEFAULT 0,
                    bandwidth_in BIGINT DEFAULT 0,
                    bandwidth_out BIGINT DEFAULT 0,
                    active_connections INT DEFAULT 0,
                    unique_visitors INT DEFAULT 0,
                    top_source_ips JSON,
                    protocol_distribution JSON,
                    hourly_stats JSON,
                    INDEX idx_timestamp (timestamp)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'geo_threat_distribution' => "
                CREATE TABLE IF NOT EXISTS geo_threat_distribution (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    country_code VARCHAR(2) NOT NULL,
                    country_name VARCHAR(100) NOT NULL,
                    region VARCHAR(100),
                    city VARCHAR(100),
                    latitude DECIMAL(10, 8),
                    longitude DECIMAL(11, 8),
                    threat_count INT DEFAULT 0,
                    risk_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'LOW',
                    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    threat_types JSON,
                    UNIQUE KEY unique_location (country_code, region, city),
                    INDEX idx_country_code (country_code),
                    INDEX idx_threat_count (threat_count)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'attack_patterns' => "
                CREATE TABLE IF NOT EXISTS attack_patterns (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    pattern_name VARCHAR(100) NOT NULL,
                    pattern_type ENUM('REQUEST', 'USER_AGENT', 'IP_RANGE', 'BEHAVIOR') DEFAULT 'REQUEST',
                    pattern_regex TEXT,
                    description TEXT,
                    severity ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
                    is_active BOOLEAN DEFAULT TRUE,
                    detection_count INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_pattern_type (pattern_type),
                    INDEX idx_is_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'security_timeline' => "
                CREATE TABLE IF NOT EXISTS security_timeline (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    event_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                    event_type VARCHAR(50) NOT NULL,
                    event_category ENUM('THREAT', 'DEFENSE', 'SYSTEM', 'USER') DEFAULT 'SYSTEM',
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    severity ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') DEFAULT 'INFO',
                    source_ip VARCHAR(45),
                    affected_resource VARCHAR(255),
                    action_taken VARCHAR(255),
                    metadata JSON,
                    INDEX idx_event_time (event_time),
                    INDEX idx_event_type (event_type),
                    INDEX idx_severity (severity)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'threat_intelligence' => "
                CREATE TABLE IF NOT EXISTS threat_intelligence (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    feed_name VARCHAR(100) NOT NULL,
                    feed_type ENUM('IP', 'DOMAIN', 'URL', 'HASH', 'PATTERN') DEFAULT 'IP',
                    indicator_value VARCHAR(500) NOT NULL,
                    threat_type VARCHAR(100),
                    confidence_level ENUM('LOW', 'MEDIUM', 'HIGH', 'VERIFIED') DEFAULT 'MEDIUM',
                    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
                    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
                    expiry_date DATETIME,
                    is_active BOOLEAN DEFAULT TRUE,
                    source_reputation INT DEFAULT 50,
                    additional_context JSON,
                    INDEX idx_indicator_value (indicator_value),
                    INDEX idx_feed_type (feed_type),
                    INDEX idx_is_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'system_performance' => "
                CREATE TABLE IF NOT EXISTS system_performance (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                    cpu_usage DECIMAL(5,2) DEFAULT 0.00,
                    memory_usage DECIMAL(10,2) DEFAULT 0.00,
                    disk_usage DECIMAL(5,2) DEFAULT 0.00,
                    network_in BIGINT DEFAULT 0,
                    network_out BIGINT DEFAULT 0,
                    monitoring_status ENUM('healthy', 'warning', 'critical') DEFAULT 'healthy',
                    active_threats INT DEFAULT 0,
                    blocked_attacks INT DEFAULT 0,
                    system_metrics JSON,
                    INDEX idx_timestamp (timestamp),
                    INDEX idx_monitoring_status (monitoring_status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'auto_defense_rules' => "
                CREATE TABLE IF NOT EXISTS auto_defense_rules (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    rule_name VARCHAR(100) NOT NULL UNIQUE,
                    rule_type ENUM('BLOCK_IP', 'RATE_LIMIT', 'CHALLENGE', 'REDIRECT') DEFAULT 'BLOCK_IP',
                    trigger_condition JSON NOT NULL,
                    action_config JSON NOT NULL,
                    is_enabled BOOLEAN DEFAULT TRUE,
                    priority INT DEFAULT 100,
                    execution_count INT DEFAULT 0,
                    success_count INT DEFAULT 0,
                    last_triggered DATETIME,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_rule_type (rule_type),
                    INDEX idx_is_enabled (is_enabled),
                    INDEX idx_priority (priority)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];

        foreach ($tables as $tableName => $sql) {
            try {
                $this->database->exec($sql);
                echo "✅ 创建表: $tableName\n";
                $this->log("成功创建表: $tableName");
            } catch (Exception $e) {
                echo "❌ 创建表失败 $tableName: " . $e->getMessage() . "\n";
                $this->log("创建表失败 $tableName: " . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * 创建视图
     */
    private function createViews(): void {
        $views = [
            'active_threats_view' => "
                CREATE OR REPLACE VIEW active_threats_view AS
                SELECT 
                    td.id,
                    td.detection_time,
                    td.threat_type,
                    td.threat_level,
                    td.source_ip,
                    td.target_resource,
                    td.risk_score,
                    gtd.country_name,
                    gtd.city,
                    bl.is_active as is_blacklisted
                FROM threat_detections td
                LEFT JOIN geo_threat_distribution gtd ON JSON_EXTRACT(td.geographic_info, '$.country_code') = gtd.country_code
                LEFT JOIN security_blacklist bl ON td.source_ip = bl.ip
                WHERE td.detection_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY td.detection_time DESC
            ",
            'security_summary_view' => "
                CREATE OR REPLACE VIEW security_summary_view AS
                SELECT 
                    COUNT(DISTINCT td.id) as total_threats_24h,
                    COUNT(DISTINCT CASE WHEN td.threat_level = 'CRITICAL' THEN td.id END) as critical_threats,
                    COUNT(DISTINCT CASE WHEN td.blocked = TRUE THEN td.id END) as blocked_threats,
                    COUNT(DISTINCT bl.ip) as blacklisted_ips,
                    COUNT(DISTINCT gtd.country_code) as affected_countries,
                    AVG(sp.cpu_usage) as avg_cpu_usage,
                    AVG(sp.memory_usage) as avg_memory_usage,
                    MAX(nts.total_requests) as peak_requests
                FROM threat_detections td
                LEFT JOIN security_blacklist bl ON bl.is_active = TRUE
                LEFT JOIN geo_threat_distribution gtd ON gtd.last_updated >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LEFT JOIN system_performance sp ON sp.timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                LEFT JOIN network_traffic_stats nts ON nts.timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                WHERE td.detection_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            "
        ];

        foreach ($views as $viewName => $sql) {
            try {
                $this->database->exec($sql);
                echo "✅ 创建视图: $viewName\n";
                $this->log("成功创建视图: $viewName");
            } catch (Exception $e) {
                echo "⚠️ 创建视图失败 $viewName: " . $e->getMessage() . "\n";
                $this->log("创建视图失败 $viewName: " . $e->getMessage());
            }
        }
    }

    /**
     * 插入默认数据
     */
    private function insertDefaultData(): void {
        echo "📦 插入默认数据...\n";

        // 插入默认攻击模式
        $defaultPatterns = [
            ['SQL注入检测', 'REQUEST', '(union|select|insert|delete|update|drop|create|alter|exec|script)', 'SQL注入攻击检测模式', 'HIGH'],
            ['XSS检测', 'REQUEST', '(<script|javascript:|onload=|onerror=|onclick=)', 'XSS跨站脚本攻击检测', 'MEDIUM'],
            ['路径遍历', 'REQUEST', '(\.\./|\.\.\\\\|%2e%2e%2f|%2e%2e\\\\)', '路径遍历攻击检测', 'HIGH'],
            ['命令注入', 'REQUEST', '(;|\||&|`|\$\(|\${)', '命令注入攻击检测', 'CRITICAL'],
            ['恶意爬虫', 'USER_AGENT', '(bot|crawler|spider|scraper)', '恶意爬虫检测', 'LOW']
        ];

        foreach ($defaultPatterns as $pattern) {
            try {
                $stmt = $this->database->prepare("
                    INSERT IGNORE INTO attack_patterns 
                    (pattern_name, pattern_type, pattern_regex, description, severity) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute($pattern);
                echo "  ✓ 插入攻击模式: {$pattern[0]}\n";
            } catch (Exception $e) {
                echo "  ❌ 插入攻击模式失败: {$pattern[0]} - " . $e->getMessage() . "\n";
            }
        }

        // 插入默认自动防御规则
        $defaultRules = [
            [
                'name' => 'Critical_Threat_Auto_Block',
                'type' => 'BLOCK_IP',
                'condition' => json_encode(['threat_level' => 'CRITICAL', 'detection_count' => 3]),
                'config' => json_encode(['duration_hours' => 24, 'notify_admin' => true])
            ],
            [
                'name' => 'High_Frequency_Attack_Rate_Limit',
                'type' => 'RATE_LIMIT',
                'condition' => json_encode(['requests_per_minute' => 100, 'threat_score' => 70]),
                'config' => json_encode(['limit_requests' => 10, 'time_window' => 60])
            ]
        ];

        foreach ($defaultRules as $rule) {
            try {
                $stmt = $this->database->prepare("
                    INSERT IGNORE INTO auto_defense_rules 
                    (rule_name, rule_type, trigger_condition, action_config) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$rule['name'], $rule['type'], $rule['condition'], $rule['config']]);
                echo "  ✓ 插入防御规则: {$rule['name']}\n";
            } catch (Exception $e) {
                echo "  ❌ 插入防御规则失败: {$rule['name']} - " . $e->getMessage() . "\n";
            }
        }

        // 插入初始系统性能记录
        try {
            $stmt = $this->database->prepare("
                INSERT INTO system_performance 
                (cpu_usage, memory_usage, disk_usage, monitoring_status, system_metrics) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                5.2, 
                256.0, 
                45.7, 
                'healthy',
                json_encode(['startup_time' => time(), 'initial_state' => 'clean'])
            ]);
            echo "  ✓ 插入初始系统性能记录\n";
        } catch (Exception $e) {
            echo "  ❌ 插入系统性能记录失败: " . $e->getMessage() . "\n";
        }
    }

    /**
     * 创建优化索引
     */
    private function createIndexes(): void {
        echo "🔧 创建优化索引...\n";

        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_security_logs_level_time ON security_logs(level, timestamp)",
            "CREATE INDEX IF NOT EXISTS idx_threat_detections_composite ON threat_detections(threat_level, detection_time, source_ip)",
            "CREATE INDEX IF NOT EXISTS idx_blacklist_active_expires ON security_blacklist(is_active, expires_at)",
            "CREATE INDEX IF NOT EXISTS idx_traffic_stats_hourly ON network_traffic_stats(timestamp, total_requests)",
            "CREATE INDEX IF NOT EXISTS idx_timeline_category_time ON security_timeline(event_category, event_time)"
        ];

        foreach ($indexes as $sql) {
            try {
                $this->database->exec($sql);
                echo "  ✓ 创建索引\n";
            } catch (Exception $e) {
                echo "  ⚠️ 创建索引失败: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * 验证迁移结果
     */
    public function validateMigration(): bool {
        echo "\n🔍 验证迁移结果...\n";
        
        $requiredTables = [
            'security_logs', 'threat_detections', 'security_blacklist',
            'network_traffic_stats', 'geo_threat_distribution', 'attack_patterns',
            'security_timeline', 'threat_intelligence', 'system_performance', 'auto_defense_rules'
        ];

        $allValid = true;
        foreach ($requiredTables as $table) {
            try {
                $stmt = $this->database->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo "  ✅ 表 $table 存在\n";
                } else {
                    echo "  ❌ 表 $table 不存在\n";
                    $allValid = false;
                }
            } catch (Exception $e) {
                echo "  ❌ 检查表 $table 失败: " . $e->getMessage() . "\n";
                $allValid = false;
            }
        }

        // 验证数据
        try {
            $stmt = $this->database->query("SELECT COUNT(*) as count FROM attack_patterns");
            $result = $stmt->fetch();
            echo "  📊 攻击模式数量: {$result['count']}\n";

            $stmt = $this->database->query("SELECT COUNT(*) as count FROM auto_defense_rules");
            $result = $stmt->fetch();
            echo "  🛡️ 防御规则数量: {$result['count']}\n";
        } catch (Exception $e) {
            echo "  ⚠️ 数据验证失败: " . $e->getMessage() . "\n";
        }

        return $allValid;
    }

    /**
     * 记录日志
     */
    private function log(string $message): void {
        $logEntry = date('Y-m-d H:i:s') . " - " . $message . "\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// 运行迁移
try {
    $migration = new SimpleSecurityMonitoringMigration();
    
    if ($migration->runMigration()) {
        if ($migration->validateMigration()) {
            echo "\n🎊 迁移完全成功！安全监控数据库已准备就绪\n";
            echo "📖 下一步: 启动安全监控系统\n";
            echo "   php start_security_monitoring.php\n";
        } else {
            echo "\n⚠️ 迁移完成但验证有问题，请检查日志\n";
        }
    } else {
        echo "\n❌ 迁移失败\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "💥 迁移过程中发生致命错误: " . $e->getMessage() . "\n";
    exit(1);
}
