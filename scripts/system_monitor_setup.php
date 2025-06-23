<?php
/**
 * AlingAi Pro 系统监控和告警配置脚本
 * 实施系统监控、日志管理、告警机制
 */

class SystemMonitor
{
    private $config = [];
    private $thresholds = [];
    private $alerts = [];
    
    public function __construct()
    {
        echo "📊 AlingAi Pro 系统监控配置开始...\n";
        echo "配置时间: " . date('Y-m-d H:i:s') . "\n\n";
        $this->initializeThresholds();
    }
    
    public function setupMonitoring()
    {
        $this->setupSystemMetrics();
        $this->setupDatabaseMonitoring();
        $this->setupApplicationMonitoring();
        $this->setupLogManagement();
        $this->setupAlertSystem();
        $this->generateMonitoringConfigs();
        $this->generateMonitoringReport();
    }
    
    private function initializeThresholds()
    {
        $this->thresholds = [
            'cpu_usage' => 80,          // CPU 使用率警告阈值
            'memory_usage' => 85,       // 内存使用率警告阈值
            'disk_usage' => 90,         // 磁盘使用率警告阈值
            'response_time' => 2000,    // 响应时间警告阈值 (毫秒)
            'error_rate' => 5,          // 错误率警告阈值 (%)
            'connection_count' => 400,  // 数据库连接数警告阈值
            'cache_hit_rate' => 85      // 缓存命中率最低阈值 (%)
        ];
    }
    
    private function setupSystemMetrics()
    {
        echo "🖥️ 配置系统指标监控...\n";
        
        $systemMetrics = [
            'cpu' => [
                'metrics' => ['usage_percent', 'load_average'],
                'interval' => 60,
                'threshold' => $this->thresholds['cpu_usage']
            ],
            'memory' => [
                'metrics' => ['total', 'used', 'free', 'cached'],
                'interval' => 60,
                'threshold' => $this->thresholds['memory_usage']
            ],
            'disk' => [
                'metrics' => ['total', 'used', 'free', 'io_read', 'io_write'],
                'interval' => 300,
                'threshold' => $this->thresholds['disk_usage']
            ],
            'network' => [
                'metrics' => ['bytes_in', 'bytes_out', 'packets_in', 'packets_out'],
                'interval' => 60,
                'threshold' => null
            ]
        ];
        
        $this->config['system_metrics'] = $systemMetrics;
        echo "  ✓ 系统指标监控配置完成\n\n";
    }
    
    private function setupDatabaseMonitoring()
    {
        echo "🗄️ 配置数据库监控...\n";
        
        $dbMetrics = [
            'connections' => [
                'metrics' => ['active_connections', 'max_connections', 'connection_errors'],
                'interval' => 120,
                'threshold' => $this->thresholds['connection_count']
            ],
            'performance' => [
                'metrics' => ['query_time', 'slow_queries', 'innodb_buffer_pool_hit_rate'],
                'interval' => 300,
                'threshold' => null
            ],
            'replication' => [
                'metrics' => ['slave_lag', 'slave_status'],
                'interval' => 60,
                'threshold' => null
            ],
            'storage' => [
                'metrics' => ['table_size', 'index_size', 'data_free'],
                'interval' => 3600,
                'threshold' => null
            ]
        ];
        
        $this->config['database_metrics'] = $dbMetrics;
        echo "  ✓ 数据库监控配置完成\n\n";
    }
    
    private function setupApplicationMonitoring()
    {
        echo "🔧 配置应用程序监控...\n";
        
        $appMetrics = [
            'api_performance' => [
                'metrics' => ['response_time', 'throughput', 'error_rate'],
                'interval' => 60,
                'endpoints' => ['/api/chat', '/api/auth', '/api/user'],
                'threshold_response_time' => $this->thresholds['response_time'],
                'threshold_error_rate' => $this->thresholds['error_rate']
            ],
            'cache_performance' => [
                'metrics' => ['hit_rate', 'miss_rate', 'memory_usage', 'evicted_keys'],
                'interval' => 120,
                'threshold' => $this->thresholds['cache_hit_rate']
            ],
            'user_activity' => [
                'metrics' => ['active_users', 'new_registrations', 'login_attempts'],
                'interval' => 300,
                'threshold' => null
            ],
            'security_events' => [
                'metrics' => ['failed_logins', 'suspicious_requests', 'blocked_ips'],
                'interval' => 60,
                'threshold' => 10
            ]
        ];
        
        $this->config['application_metrics'] = $appMetrics;
        echo "  ✓ 应用程序监控配置完成\n\n";
    }
    
    private function setupLogManagement()
    {
        echo "📝 配置日志管理...\n";
        
        $logConfig = [
            'log_levels' => ['ERROR', 'WARNING', 'INFO'],
            'log_rotation' => [
                'max_size' => '100MB',
                'max_files' => 7,
                'compress' => true
            ],
            'log_sources' => [
                'application' => [
                    'path' => './logs/app.log',
                    'level' => 'INFO',
                    'format' => 'json'
                ],
                'error' => [
                    'path' => './logs/error.log',
                    'level' => 'ERROR',
                    'format' => 'json'
                ],
                'access' => [
                    'path' => './logs/access.log',
                    'level' => 'INFO',
                    'format' => 'combined'
                ],
                'security' => [
                    'path' => './logs/security.log',
                    'level' => 'WARNING',
                    'format' => 'json'
                ]
            ],
            'log_analysis' => [
                'patterns' => [
                    'error_patterns' => ['ERROR', 'FATAL', 'Exception'],
                    'security_patterns' => ['SQL injection', 'XSS', 'CSRF', 'Unauthorized'],
                    'performance_patterns' => ['slow query', 'timeout', 'memory limit']
                ],
                'alert_threshold' => 5
            ]
        ];
        
        $this->config['log_management'] = $logConfig;
        echo "  ✓ 日志管理配置完成\n\n";
    }
    
    private function setupAlertSystem()
    {
        echo "🚨 配置告警系统...\n";
        
        $alertConfig = [
            'notification_channels' => [
                'email' => [
                    'enabled' => true,
                    'smtp_host' => 'smtp.gmail.com',
                    'smtp_port' => 587,
                    'username' => 'aoteman2024@gmail.com',
                    'recipients' => ['admin@alingai.com', 'ops@alingai.com']
                ],
                'webhook' => [
                    'enabled' => true,
                    'url' => 'https://your-domain.com/webhook/alerts',
                    'timeout' => 10
                ],
                'log' => [
                    'enabled' => true,
                    'path' => './logs/alerts.log'
                ]
            ],
            'alert_rules' => [
                'critical' => [
                    'conditions' => [
                        'cpu_usage > 90',
                        'memory_usage > 95',
                        'disk_usage > 95',
                        'database_connections > 450',
                        'error_rate > 10'
                    ],
                    'notification_delay' => 0,
                    'channels' => ['email', 'webhook', 'log']
                ],
                'warning' => [
                    'conditions' => [
                        'cpu_usage > 80',
                        'memory_usage > 85',
                        'response_time > 2000',
                        'cache_hit_rate < 85'
                    ],
                    'notification_delay' => 300,
                    'channels' => ['log', 'webhook']
                ],
                'info' => [
                    'conditions' => [
                        'new_user_registration',
                        'system_startup',
                        'backup_completed'
                    ],
                    'notification_delay' => 0,
                    'channels' => ['log']
                ]
            ],
            'escalation' => [
                'warning_to_critical' => 1800,  // 30分钟后升级
                'max_notifications' => 5,       // 最大通知次数
                'cooldown_period' => 3600      // 冷却期1小时
            ]
        ];
        
        $this->config['alert_system'] = $alertConfig;
        echo "  ✓ 告警系统配置完成\n\n";
    }
    
    private function generateMonitoringConfigs()
    {
        echo "📄 生成监控配置文件...\n";
        
        // 生成 Prometheus 配置
        $this->generatePrometheusConfig();
        
        // 生成 Grafana 仪表板配置
        $this->generateGrafanaConfig();
        
        // 生成监控脚本
        $this->generateMonitoringScripts();
        
        // 生成告警脚本
        $this->generateAlertScripts();
        
        echo "  ✓ 监控配置文件生成完成\n\n";
    }
    
    private function generatePrometheusConfig()
    {
        $prometheusConfig = <<<EOF
# Prometheus 配置 - AlingAi Pro
# 生成时间: {date('Y-m-d H:i:s')}

global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  - "alert_rules.yml"

alerting:
  alertmanagers:
    - static_configs:
        - targets:
          - alertmanager:9093

scrape_configs:
  # 系统指标
  - job_name: 'node-exporter'
    static_configs:
      - targets: ['localhost:9100']
    scrape_interval: 60s
    
  # 应用指标
  - job_name: 'alingai-app'
    static_configs:
      - targets: ['localhost:8080']
    scrape_interval: 30s
    metrics_path: '/metrics'
    
  # 数据库指标
  - job_name: 'mysql-exporter'
    static_configs:
      - targets: ['localhost:9104']
    scrape_interval: 60s
    
  # Redis 指标
  - job_name: 'redis-exporter'
    static_configs:
      - targets: ['localhost:9121']
    scrape_interval: 60s
    
  # Nginx 指标
  - job_name: 'nginx-exporter'
    static_configs:
      - targets: ['localhost:9113']
    scrape_interval: 30s

EOF;
        
        file_put_contents('prometheus.monitoring.yml', $prometheusConfig);
    }
    
    private function generateGrafanaConfig()
    {
        $grafanaConfig = [
            "dashboard" => [
                "id" => null,
                "title" => "AlingAi Pro 系统监控",
                "tags" => ["alingai", "monitoring"],
                "timezone" => "browser",
                "panels" => [
                    [
                        "id" => 1,
                        "title" => "系统 CPU 使用率",
                        "type" => "graph",
                        "targets" => [
                            [
                                "expr" => "100 - (avg(rate(node_cpu_seconds_total{mode=\"idle\"}[5m])) * 100)",
                                "legendFormat" => "CPU Usage %"
                            ]
                        ],
                        "alert" => [
                            "conditions" => [
                                [
                                    "query" => ["A", "5m", "now"],
                                    "reducer" => ["type" => "avg"],
                                    "evaluator" => ["params" => [80]]
                                ]
                            ]
                        ]
                    ],
                    [
                        "id" => 2,
                        "title" => "内存使用率",
                        "type" => "graph",
                        "targets" => [
                            [
                                "expr" => "(1 - (node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes)) * 100",
                                "legendFormat" => "Memory Usage %"
                            ]
                        ]
                    ],
                    [
                        "id" => 3,
                        "title" => "API 响应时间",
                        "type" => "graph",
                        "targets" => [
                            [
                                "expr" => "avg(http_request_duration_seconds{job=\"alingai-app\"})",
                                "legendFormat" => "Response Time"
                            ]
                        ]
                    ],
                    [
                        "id" => 4,
                        "title" => "数据库连接数",
                        "type" => "singlestat",
                        "targets" => [
                            [
                                "expr" => "mysql_global_status_threads_connected",
                                "legendFormat" => "Connections"
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        file_put_contents('grafana.dashboard.json', json_encode($grafanaConfig, JSON_PRETTY_PRINT));
    }
    
    private function generateMonitoringScripts()
    {
        // 系统监控脚本
        $systemMonitorScript = <<<'PHP'
<?php
/**
 * 系统监控脚本
 */
class SystemMetricsCollector
{
    public function collectMetrics()
    {
        return [
            'timestamp' => time(),
            'cpu' => $this->getCPUUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'network' => $this->getNetworkStats()
        ];
    }
    
    private function getCPUUsage()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic cpu get loadpercentage /value');
            if (preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                return (int)$matches[1];
            }
        } else {
            $load = sys_getloadavg();
            return $load[0];
        }
        return 0;
    }
    
    private function getMemoryUsage()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /value');
            if (preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $total) &&
                preg_match('/FreePhysicalMemory=(\d+)/', $output, $free)) {
                $used = ($total[1] - $free[1]) / $total[1] * 100;
                return round($used, 2);
            }
        }
        return 0;
    }
    
    private function getDiskUsage()
    {
        $total = disk_total_space('.');
        $free = disk_free_space('.');
        return round(($total - $free) / $total * 100, 2);
    }
    
    private function getNetworkStats()
    {
        // 网络统计实现
        return ['bytes_in' => 0, 'bytes_out' => 0];
    }
}

// 定期收集指标
$collector = new SystemMetricsCollector();
$metrics = $collector->collectMetrics();

// 保存到日志文件
file_put_contents('./logs/system_metrics.log', 
    json_encode($metrics) . "\n", FILE_APPEND | LOCK_EX);

// 检查告警阈值
$alerts = [];
if ($metrics['cpu'] > 80) {
    $alerts[] = ['type' => 'WARNING', 'message' => "CPU usage high: {$metrics['cpu']}%"];
}
if ($metrics['memory'] > 85) {
    $alerts[] = ['type' => 'WARNING', 'message' => "Memory usage high: {$metrics['memory']}%"];
}
if ($metrics['disk'] > 90) {
    $alerts[] = ['type' => 'CRITICAL', 'message' => "Disk usage critical: {$metrics['disk']}%"];
}

// 发送告警
foreach ($alerts as $alert) {
    file_put_contents('./logs/alerts.log', 
        json_encode($alert + ['timestamp' => time()]) . "\n", FILE_APPEND | LOCK_EX);
}
?>
PHP;
        
        file_put_contents('system_monitor.php', $systemMonitorScript);
    }
    
    private function generateAlertScripts()
    {
        // 告警处理脚本
        $alertScript = <<<'PHP'
<?php
/**
 * 告警处理脚本
 */
class AlertManager
{
    private $config;
    
    public function __construct()
    {
        $this->config = [
            'email_smtp' => 'smtp.gmail.com',
            'email_port' => 587,
            'email_user' => 'aoteman2024@gmail.com',
            'email_pass' => 'your_app_password',
            'recipients' => ['admin@alingai.com']
        ];
    }
    
    public function processAlerts()
    {
        $alertsFile = './logs/alerts.log';
        if (!file_exists($alertsFile)) return;
        
        $alerts = file($alertsFile, FILE_IGNORE_NEW_LINES);
        $unprocessedAlerts = [];
        
        foreach ($alerts as $alertLine) {
            $alert = json_decode($alertLine, true);
            if ($alert && !isset($alert['processed'])) {
                $this->sendAlert($alert);
                $alert['processed'] = true;
                $unprocessedAlerts[] = json_encode($alert);
            }
        }
        
        // 更新已处理的告警
        if (!empty($unprocessedAlerts)) {
            file_put_contents($alertsFile, implode("\n", $unprocessedAlerts) . "\n");
        }
    }
    
    private function sendAlert($alert)
    {
        $subject = "AlingAi Pro 系统告警 - " . $alert['type'];
        $message = "告警时间: " . date('Y-m-d H:i:s', $alert['timestamp']) . "\n";
        $message .= "告警级别: " . $alert['type'] . "\n";
        $message .= "告警信息: " . $alert['message'] . "\n";
        
        // 发送邮件告警
        $this->sendEmailAlert($subject, $message);
        
        // 记录告警发送日志
        error_log("Alert sent: " . json_encode($alert));
    }
    
    private function sendEmailAlert($subject, $message)
    {
        // 简单的邮件发送实现
        $headers = "From: {$this->config['email_user']}\r\n";
        $headers .= "Reply-To: {$this->config['email_user']}\r\n";
        
        foreach ($this->config['recipients'] as $recipient) {
            mail($recipient, $subject, $message, $headers);
        }
    }
}

// 执行告警处理
$alertManager = new AlertManager();
$alertManager->processAlerts();
?>
PHP;
        
        file_put_contents('alert_manager.php', $alertScript);
        
        // Windows 计划任务脚本
        $taskScript = <<<BAT
@echo off
REM AlingAi Pro 监控任务调度脚本

REM 每分钟执行系统监控
schtasks /create /tn "AlingAi_SystemMonitor" /tr "php E:\Code\AlingAi\AlingAi_pro\scripts\system_monitor.php" /sc minute /mo 1 /f

REM 每5分钟处理告警
schtasks /create /tn "AlingAi_AlertManager" /tr "php E:\Code\AlingAi\AlingAi_pro\scripts\alert_manager.php" /sc minute /mo 5 /f

echo 监控任务已配置完成
pause
BAT;
        
        file_put_contents('setup_monitoring_tasks.bat', $taskScript);
    }
    
    private function generateMonitoringReport()
    {
        echo "📊 生成监控配置报告...\n";
        echo str_repeat("=", 60) . "\n";
        echo "📊 AlingAi Pro 系统监控配置报告\n";
        echo str_repeat("=", 60) . "\n";
        echo "配置时间: " . date('Y-m-d H:i:s') . "\n\n";
        
        echo "🎯 监控指标阈值:\n";
        echo str_repeat("-", 40) . "\n";
        foreach ($this->thresholds as $metric => $threshold) {
            echo "  {$metric}: {$threshold}" . ($metric === 'response_time' ? 'ms' : '%') . "\n";
        }
        echo "\n";
        
        echo "📊 监控范围:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  ✓ 系统资源监控 (CPU, 内存, 磁盘, 网络)\n";
        echo "  ✓ 数据库性能监控 (连接数, 查询性能)\n";
        echo "  ✓ 应用程序监控 (API 性能, 缓存效率)\n";
        echo "  ✓ 安全事件监控 (登录失败, 异常请求)\n\n";
        
        echo "🔔 告警配置:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  ✓ 邮件告警 - 严重告警立即发送\n";
        echo "  ✓ 日志告警 - 所有告警记录到日志\n";
        echo "  ✓ Webhook 告警 - 集成第三方系统\n";
        echo "  ✓ 告警升级机制 - 30分钟后升级\n\n";
        
        echo "📁 生成的配置文件:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  ✓ prometheus.monitoring.yml - Prometheus 配置\n";
        echo "  ✓ grafana.dashboard.json - Grafana 仪表板\n";
        echo "  ✓ system_monitor.php - 系统监控脚本\n";
        echo "  ✓ alert_manager.php - 告警处理脚本\n";
        echo "  ✓ setup_monitoring_tasks.bat - 任务调度脚本\n\n";
        
        echo "🚀 部署建议:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  1. 安装 Prometheus + Grafana 监控栈\n";
        echo "  2. 配置 Node Exporter 收集系统指标\n";
        echo "  3. 运行 setup_monitoring_tasks.bat 配置调度\n";
        echo "  4. 验证告警通知渠道\n";
        echo "  5. 根据实际情况调整阈值\n\n";
        
        echo str_repeat("=", 60) . "\n";
        
        // 保存详细配置
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'thresholds' => $this->thresholds,
            'configuration' => $this->config,
            'config_files' => [
                'prometheus.monitoring.yml',
                'grafana.dashboard.json',
                'system_monitor.php',
                'alert_manager.php',
                'setup_monitoring_tasks.bat'
            ]
        ];
        
        file_put_contents('monitoring_configuration_report_' . date('Y_m_d_H_i_s') . '.json',
                         json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "📄 详细配置已保存到: monitoring_configuration_report_" . date('Y_m_d_H_i_s') . ".json\n";
    }
}

// 执行监控配置
$monitor = new SystemMonitor();
$monitor->setupMonitoring();
