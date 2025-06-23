<?php
/**
 * SQLite版安全监控数据库迁移脚本
 */

class SQLiteSecurityMonitoringMigration {
    private PDO $database;
    private string $logFile;
    private string $dbPath;

    public function __construct() {
        $this->logFile = __DIR__ . '/logs/migration_' . date('Y-m-d_H-i-s') . '.log';
        $this->dbPath = __DIR__ . '/data/security_monitoring.db';
        
        // 创建必要目录
        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }
        if (!is_dir(__DIR__ . '/data')) {
            mkdir(__DIR__ . '/data', 0755, true);
        }

        try {
            $this->database = new PDO("sqlite:{$this->dbPath}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $this->log("SQLite数据库连接成功: {$this->dbPath}");
        } catch (Exception $e) {
            $this->log("数据库连接失败: " . $e->getMessage());
            echo "❌ 数据库连接失败: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * 运行安全监控表迁移
     */
    public function runMigration(): bool {
        try {
            $this->log("开始执行安全监控数据库迁移...");
            echo "🚀 开始安全监控系统数据库迁移 (SQLite)...\n";
            echo str_repeat("=", 60) . "\n";

            // 创建所有表
            $this->createTables();
            
            // 插入默认数据
            $this->insertDefaultData();
            
            // 创建索引
            $this->createIndexes();

            echo "\n" . str_repeat("=", 60) . "\n";
            echo "🎉 数据库迁移完成！\n";
            echo "📁 数据库文件: {$this->dbPath}\n";
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
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                    level TEXT CHECK(level IN ('INFO', 'WARNING', 'ERROR', 'CRITICAL')) DEFAULT 'INFO',
                    source VARCHAR(100) NOT NULL,
                    message TEXT NOT NULL,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    session_id VARCHAR(128),
                    additional_data TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'threat_detections' => "
                CREATE TABLE IF NOT EXISTS threat_detections (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    detection_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                    threat_type VARCHAR(50) NOT NULL,
                    threat_level TEXT CHECK(threat_level IN ('LOW', 'MEDIUM', 'HIGH', 'CRITICAL')) DEFAULT 'MEDIUM',
                    source_ip VARCHAR(45) NOT NULL,
                    target_resource VARCHAR(255),
                    attack_vector VARCHAR(100),
                    risk_score INTEGER DEFAULT 0,
                    geographic_info TEXT,
                    blocked BOOLEAN DEFAULT 0,
                    response_action VARCHAR(100),
                    raw_data TEXT
                )
            ",
            'security_blacklist' => "
                CREATE TABLE IF NOT EXISTS security_blacklist (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ip VARCHAR(45) NOT NULL UNIQUE,
                    reason VARCHAR(255) NOT NULL,
                    threat_level TEXT CHECK(threat_level IN ('LOW', 'MEDIUM', 'HIGH', 'CRITICAL')) DEFAULT 'MEDIUM',
                    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at DATETIME,
                    is_active BOOLEAN DEFAULT 1,
                    detection_count INTEGER DEFAULT 1,
                    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
                    geographic_info TEXT
                )
            ",
            'network_traffic_stats' => "
                CREATE TABLE IF NOT EXISTS network_traffic_stats (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                    total_requests INTEGER DEFAULT 0,
                    blocked_requests INTEGER DEFAULT 0,
                    bandwidth_in INTEGER DEFAULT 0,
                    bandwidth_out INTEGER DEFAULT 0,
                    active_connections INTEGER DEFAULT 0,
                    unique_visitors INTEGER DEFAULT 0,
                    top_source_ips TEXT,
                    protocol_distribution TEXT,
                    hourly_stats TEXT
                )
            ",
            'geo_threat_distribution' => "
                CREATE TABLE IF NOT EXISTS geo_threat_distribution (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    country_code VARCHAR(2) NOT NULL,
                    country_name VARCHAR(100) NOT NULL,
                    region VARCHAR(100),
                    city VARCHAR(100),
                    latitude REAL,
                    longitude REAL,
                    threat_count INTEGER DEFAULT 0,
                    risk_level TEXT CHECK(risk_level IN ('LOW', 'MEDIUM', 'HIGH', 'CRITICAL')) DEFAULT 'LOW',
                    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    threat_types TEXT
                )
            ",
            'attack_patterns' => "
                CREATE TABLE IF NOT EXISTS attack_patterns (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    pattern_name VARCHAR(100) NOT NULL,
                    pattern_type TEXT CHECK(pattern_type IN ('REQUEST', 'USER_AGENT', 'IP_RANGE', 'BEHAVIOR')) DEFAULT 'REQUEST',
                    pattern_regex TEXT,
                    description TEXT,
                    severity TEXT CHECK(severity IN ('LOW', 'MEDIUM', 'HIGH', 'CRITICAL')) DEFAULT 'MEDIUM',
                    is_active BOOLEAN DEFAULT 1,
                    detection_count INTEGER DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'security_timeline' => "
                CREATE TABLE IF NOT EXISTS security_timeline (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    event_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                    event_type VARCHAR(50) NOT NULL,
                    event_category TEXT CHECK(event_category IN ('THREAT', 'DEFENSE', 'SYSTEM', 'USER')) DEFAULT 'SYSTEM',
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    severity TEXT CHECK(severity IN ('INFO', 'WARNING', 'ERROR', 'CRITICAL')) DEFAULT 'INFO',
                    source_ip VARCHAR(45),
                    affected_resource VARCHAR(255),
                    action_taken VARCHAR(255),
                    metadata TEXT
                )
            ",
            'threat_intelligence' => "
                CREATE TABLE IF NOT EXISTS threat_intelligence (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    feed_name VARCHAR(100) NOT NULL,
                    feed_type TEXT CHECK(feed_type IN ('IP', 'DOMAIN', 'URL', 'HASH', 'PATTERN')) DEFAULT 'IP',
                    indicator_value VARCHAR(500) NOT NULL,
                    threat_type VARCHAR(100),
                    confidence_level TEXT CHECK(confidence_level IN ('LOW', 'MEDIUM', 'HIGH', 'VERIFIED')) DEFAULT 'MEDIUM',
                    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
                    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
                    expiry_date DATETIME,
                    is_active BOOLEAN DEFAULT 1,
                    source_reputation INTEGER DEFAULT 50,
                    additional_context TEXT
                )
            ",
            'system_performance' => "
                CREATE TABLE IF NOT EXISTS system_performance (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                    cpu_usage REAL DEFAULT 0.00,
                    memory_usage REAL DEFAULT 0.00,
                    disk_usage REAL DEFAULT 0.00,
                    network_in INTEGER DEFAULT 0,
                    network_out INTEGER DEFAULT 0,
                    monitoring_status TEXT CHECK(monitoring_status IN ('healthy', 'warning', 'critical')) DEFAULT 'healthy',
                    active_threats INTEGER DEFAULT 0,
                    blocked_attacks INTEGER DEFAULT 0,
                    system_metrics TEXT
                )
            ",
            'auto_defense_rules' => "
                CREATE TABLE IF NOT EXISTS auto_defense_rules (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    rule_name VARCHAR(100) NOT NULL UNIQUE,
                    rule_type TEXT CHECK(rule_type IN ('BLOCK_IP', 'RATE_LIMIT', 'CHALLENGE', 'REDIRECT')) DEFAULT 'BLOCK_IP',
                    trigger_condition TEXT NOT NULL,
                    action_config TEXT NOT NULL,
                    is_enabled BOOLEAN DEFAULT 1,
                    priority INTEGER DEFAULT 100,
                    execution_count INTEGER DEFAULT 0,
                    success_count INTEGER DEFAULT 0,
                    last_triggered DATETIME,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
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
            ['恶意爬虫', 'USER_AGENT', '(bot|crawler|spider|scraper)', '恶意爬虫检测', 'LOW'],
            ['暴力破解', 'BEHAVIOR', 'rapid_login_attempts', '暴力破解登录检测', 'HIGH'],
            ['DDoS攻击', 'BEHAVIOR', 'high_frequency_requests', 'DDoS攻击检测', 'CRITICAL'],
            ['文件包含', 'REQUEST', '(include|require|file_get_contents)', '文件包含攻击检测', 'HIGH']
        ];

        foreach ($defaultPatterns as $pattern) {
            try {
                $stmt = $this->database->prepare("
                    INSERT OR IGNORE INTO attack_patterns 
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
                'Critical_Threat_Auto_Block',
                'BLOCK_IP',
                json_encode(['threat_level' => 'CRITICAL', 'detection_count' => 3]),
                json_encode(['duration_hours' => 24, 'notify_admin' => true])
            ],
            [
                'High_Frequency_Attack_Rate_Limit',
                'RATE_LIMIT',
                json_encode(['requests_per_minute' => 100, 'threat_score' => 70]),
                json_encode(['limit_requests' => 10, 'time_window' => 60])
            ],
            [
                'SQL_Injection_Auto_Block',
                'BLOCK_IP',
                json_encode(['threat_type' => 'SQL注入', 'detection_count' => 1]),
                json_encode(['duration_hours' => 6, 'immediate_block' => true])
            ],
            [
                'DDoS_Challenge_Response',
                'CHALLENGE',
                json_encode(['requests_per_second' => 50, 'unique_sources' => false]),
                json_encode(['challenge_type' => 'captcha', 'duration_minutes' => 30])
            ]
        ];

        foreach ($defaultRules as $rule) {
            try {
                $stmt = $this->database->prepare("
                    INSERT OR IGNORE INTO auto_defense_rules 
                    (rule_name, rule_type, trigger_condition, action_config) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute($rule);
                echo "  ✓ 插入防御规则: {$rule[0]}\n";
            } catch (Exception $e) {
                echo "  ❌ 插入防御规则失败: {$rule[0]} - " . $e->getMessage() . "\n";
            }
        }

        // 插入示例威胁情报数据
        $threatIntelData = [
            ['恶意IP黑名单', 'IP', '192.168.1.100', 'SQL注入攻击源', 'HIGH'],
            ['恶意IP黑名单', 'IP', '10.0.0.50', 'DDoS攻击源', 'CRITICAL'],
            ['恶意域名', 'DOMAIN', 'malicious-site.example', '恶意软件分发', 'HIGH'],
            ['钓鱼URL', 'URL', 'http://phishing-site.example/login', '钓鱼攻击', 'MEDIUM']
        ];

        foreach ($threatIntelData as $intel) {
            try {
                $stmt = $this->database->prepare("
                    INSERT OR IGNORE INTO threat_intelligence 
                    (feed_name, feed_type, indicator_value, threat_type, confidence_level) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute($intel);
                echo "  ✓ 插入威胁情报: {$intel[2]}\n";
            } catch (Exception $e) {
                echo "  ❌ 插入威胁情报失败: {$intel[2]} - " . $e->getMessage() . "\n";
            }
        }

        // 插入地理威胁分布示例数据
        $geoData = [
            ['CN', '中国', '北京', '北京', 39.9042, 116.4074, 15, 'MEDIUM'],
            ['US', '美国', '加利福尼亚', '洛杉矶', 34.0522, -118.2437, 8, 'LOW'],
            ['RU', '俄罗斯', '莫斯科', '莫斯科', 55.7558, 37.6176, 25, 'HIGH'],
            ['KR', '韩国', '首尔', '首尔', 37.5665, 126.9780, 12, 'MEDIUM']
        ];

        foreach ($geoData as $geo) {
            try {
                $stmt = $this->database->prepare("
                    INSERT OR IGNORE INTO geo_threat_distribution 
                    (country_code, country_name, region, city, latitude, longitude, threat_count, risk_level) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute($geo);
                echo "  ✓ 插入地理数据: {$geo[1]} - {$geo[2]}\n";
            } catch (Exception $e) {
                echo "  ❌ 插入地理数据失败: {$geo[1]} - " . $e->getMessage() . "\n";
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
                json_encode(['startup_time' => time(), 'initial_state' => 'clean', 'version' => '1.0.0'])
            ]);
            echo "  ✓ 插入初始系统性能记录\n";
        } catch (Exception $e) {
            echo "  ❌ 插入系统性能记录失败: " . $e->getMessage() . "\n";
        }

        // 插入安全时间线示例事件
        $timelineEvents = [
            ['系统启动', 'SYSTEM', 'INFO', '安全监控系统初始化', '系统已成功启动并开始监控'],
            ['防火墙更新', 'DEFENSE', 'INFO', '防火墙规则更新', '自动防御规则已更新'],
            ['威胁检测', 'THREAT', 'WARNING', '检测到可疑活动', '来自192.168.1.100的SQL注入尝试']
        ];

        foreach ($timelineEvents as $event) {
            try {
                $stmt = $this->database->prepare("
                    INSERT INTO security_timeline 
                    (event_type, event_category, severity, title, description) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute($event);
                echo "  ✓ 插入时间线事件: {$event[3]}\n";
            } catch (Exception $e) {
                echo "  ❌ 插入时间线事件失败: {$event[3]} - " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * 创建优化索引
     */
    private function createIndexes(): void {
        echo "🔧 创建优化索引...\n";

        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_security_logs_level_time ON security_logs(level, timestamp)",
            "CREATE INDEX IF NOT EXISTS idx_threat_detections_ip ON threat_detections(source_ip)",
            "CREATE INDEX IF NOT EXISTS idx_threat_detections_time ON threat_detections(detection_time)",
            "CREATE INDEX IF NOT EXISTS idx_threat_detections_level ON threat_detections(threat_level)",
            "CREATE INDEX IF NOT EXISTS idx_blacklist_ip ON security_blacklist(ip)",
            "CREATE INDEX IF NOT EXISTS idx_blacklist_active ON security_blacklist(is_active)",
            "CREATE INDEX IF NOT EXISTS idx_traffic_stats_time ON network_traffic_stats(timestamp)",
            "CREATE INDEX IF NOT EXISTS idx_geo_country ON geo_threat_distribution(country_code)",
            "CREATE INDEX IF NOT EXISTS idx_attack_patterns_type ON attack_patterns(pattern_type)",
            "CREATE INDEX IF NOT EXISTS idx_timeline_time ON security_timeline(event_time)",
            "CREATE INDEX IF NOT EXISTS idx_timeline_category ON security_timeline(event_category)",
            "CREATE INDEX IF NOT EXISTS idx_intel_type ON threat_intelligence(feed_type)",
            "CREATE INDEX IF NOT EXISTS idx_intel_value ON threat_intelligence(indicator_value)",
            "CREATE INDEX IF NOT EXISTS idx_performance_time ON system_performance(timestamp)",
            "CREATE INDEX IF NOT EXISTS idx_defense_rules_enabled ON auto_defense_rules(is_enabled)"
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
                $stmt = $this->database->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
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

            $stmt = $this->database->query("SELECT COUNT(*) as count FROM threat_intelligence");
            $result = $stmt->fetch();
            echo "  🧠 威胁情报数量: {$result['count']}\n";

            $stmt = $this->database->query("SELECT COUNT(*) as count FROM geo_threat_distribution");
            $result = $stmt->fetch();
            echo "  🌍 地理分布数据: {$result['count']}\n";

            $stmt = $this->database->query("SELECT COUNT(*) as count FROM security_timeline");
            $result = $stmt->fetch();
            echo "  📅 时间线事件: {$result['count']}\n";

        } catch (Exception $e) {
            echo "  ⚠️ 数据验证失败: " . $e->getMessage() . "\n";
        }

        return $allValid;
    }

    /**
     * 生成配置文件
     */
    public function generateConfig(): void {
        echo "\n⚙️ 生成配置文件...\n";
        
        $config = [
            'database' => [
                'type' => 'sqlite',
                'path' => $this->dbPath,
                'created_at' => date('Y-m-d H:i:s')
            ],
            'monitoring' => [
                'enabled' => true,
                'interval_seconds' => 1,
                'log_retention_days' => 30,
                'max_threat_records' => 10000
            ],
            'websocket' => [
                'enabled' => true,
                'host' => '127.0.0.1',
                'port' => 8080
            ],
            'alerts' => [
                'email_enabled' => false,
                'webhook_enabled' => false,
                'sound_alerts' => true
            ]
        ];

        $configPath = __DIR__ . '/config/security_monitoring.json';
        if (!is_dir(dirname($configPath))) {
            mkdir(dirname($configPath), 0755, true);
        }

        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
        echo "  ✓ 配置文件已生成: $configPath\n";
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
    echo "🛡️ AlingAi实时网络安全监控系统 - 数据库初始化\n";
    echo str_repeat("=", 70) . "\n\n";
    
    $migration = new SQLiteSecurityMonitoringMigration();
    
    if ($migration->runMigration()) {
        if ($migration->validateMigration()) {
            $migration->generateConfig();
            
            echo "\n" . str_repeat("=", 70) . "\n";
            echo "🎊 迁移完全成功！安全监控数据库已准备就绪\n";
            echo "\n📋 下一步操作:\n";
            echo "1. 🔄 启动监控系统: php start_security_monitoring.php\n";
            echo "2. 🌐 访问控制面板: http://localhost/security-dashboard.html\n";
            echo "3. 📊 查看实时数据: WebSocket连接到 ws://localhost:8080\n";
            echo "4. 🧪 运行系统测试: php test_security_monitoring.php\n";
            echo "\n📁 重要文件:\n";
            echo "   • 数据库: data/security_monitoring.db\n";
            echo "   • 配置: config/security_monitoring.json\n";
            echo "   • 日志: logs/migration_*.log\n";
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
