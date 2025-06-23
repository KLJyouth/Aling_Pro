<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\Security\Exceptions\NetworkMonitorException;

/**
 * 实时网络监控服务
 * 
 * 实时监控网络流量、连接状态和安全事件
 * 增强安全性：实时威胁检测、流量分析和异常监控
 * 优化性能：高效的数据处理和智能过滤
 */
class RealTimeNetworkMonitor
{
    private $logger;
    private $container;
    private $config = [];
    private $networkStats = [];
    private $connectionPool = [];
    private $trafficAnalyzer;
    private $threatDetector;
    private $alertManager;
    private $lastCleanup = 0;
    private $cleanupInterval = 300; // 5分钟清理一次

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param Container $container 容器
     */
    public function __construct(LoggerInterface $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
        
        $this->config = $this->loadConfiguration();
        $this->initializeComponents();
        $this->initializeNetworkStats();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'monitoring_enabled' => env('NETWORK_MONITORING_ENABLED', true),
            'traffic_analysis' => env('TRAFFIC_ANALYSIS_ENABLED', true),
            'connection_tracking' => env('CONNECTION_TRACKING_ENABLED', true),
            'threat_detection' => env('THREAT_DETECTION_ENABLED', true),
            'alert_thresholds' => [
                'high_bandwidth' => env('HIGH_BANDWIDTH_THRESHOLD', 1000000), // 1MB/s
                'connection_flood' => env('CONNECTION_FLOOD_THRESHOLD', 100), // 100连接/秒
                'suspicious_patterns' => env('SUSPICIOUS_PATTERNS_THRESHOLD', 10), // 10个模式/分钟
                'anomalous_traffic' => env('ANOMALOUS_TRAFFIC_THRESHOLD', 0.8) // 80%异常流量
            ],
            'monitoring_intervals' => [
                'traffic_stats' => env('TRAFFIC_STATS_INTERVAL', 60), // 1分钟
                'connection_analysis' => env('CONNECTION_ANALYSIS_INTERVAL', 30), // 30秒
                'threat_scan' => env('THREAT_SCAN_INTERVAL', 10), // 10秒
                'alert_check' => env('ALERT_CHECK_INTERVAL', 5) // 5秒
            ],
            'data_retention' => [
                'traffic_logs' => env('TRAFFIC_LOGS_RETENTION', 86400), // 24小时
                'connection_logs' => env('CONNECTION_LOGS_RETENTION', 3600), // 1小时
                'alert_logs' => env('ALERT_LOGS_RETENTION', 604800) // 7天
            ],
            'filter_rules' => [
                'whitelist_ips' => explode(',', env('WHITELIST_IPS', '')),
                'blacklist_ips' => explode(',', env('BLACKLIST_IPS', '')),
                'monitored_ports' => explode(',', env('MONITORED_PORTS', '80,443,22,21')),
                'ignored_protocols' => explode(',', env('IGNORED_PROTOCOLS', 'ICMP'))
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        // 初始化流量分析器
        $this->trafficAnalyzer = [
            'packet_counter' => 0,
            'byte_counter' => 0,
            'protocol_stats' => [],
            'port_stats' => [],
            'ip_stats' => [],
            'last_reset' => time()
        ];
        
        // 初始化威胁检测器
        $this->threatDetector = [
            'suspicious_patterns' => [],
            'attack_signatures' => [],
            'anomaly_detector' => null,
            'last_scan' => 0
        ];
        
        // 初始化告警管理器
        $this->alertManager = [
            'active_alerts' => [],
            'alert_history' => [],
            'alert_rules' => [],
            'last_alert_check' => 0
        ];
    }
    
    /**
     * 初始化网络统计
     */
    private function initializeNetworkStats(): void
    {
        $this->networkStats = [
            'total_packets' => 0,
            'total_bytes' => 0,
            'active_connections' => 0,
            'peak_connections' => 0,
            'dropped_packets' => 0,
            'error_packets' => 0,
            'start_time' => time(),
            'last_update' => time()
        ];
    }
    
    /**
     * 开始监控
     * 
     * @return array 监控状态
     */
    public function startMonitoring(): array
    {
        if (!$this->config['monitoring_enabled']) {
            throw new NetworkMonitorException('网络监控已禁用');
        }
        
        $this->logger->info('开始实时网络监控');
        
        // 初始化监控线程
        $this->initializeMonitoringThreads();
        
        // 启动流量分析
        if ($this->config['traffic_analysis']) {
            $this->startTrafficAnalysis();
        }
        
        // 启动连接跟踪
        if ($this->config['connection_tracking']) {
            $this->startConnectionTracking();
        }
        
        // 启动威胁检测
        if ($this->config['threat_detection']) {
            $this->startThreatDetection();
        }
        
        return [
            'status' => 'monitoring_started',
            'timestamp' => time(),
            'components' => [
                'traffic_analysis' => $this->config['traffic_analysis'],
                'connection_tracking' => $this->config['connection_tracking'],
                'threat_detection' => $this->config['threat_detection']
            ]
        ];
    }
    
    /**
     * 停止监控
     * 
     * @return array 停止状态
     */
    public function stopMonitoring(): array
    {
        $this->logger->info('停止实时网络监控');
        
        // 停止所有监控线程
        $this->stopMonitoringThreads();
        
        // 保存最终统计
        $this->saveFinalStats();
        
        return [
            'status' => 'monitoring_stopped',
            'timestamp' => time(),
            'final_stats' => $this->getNetworkStats()
        ];
    }
    
    /**
     * 处理网络数据包
     * 
     * @param array $packet 数据包信息
     * @return array 处理结果
     */
    public function processPacket(array $packet): array
    {
        $result = [
            'processed' => false,
            'filtered' => false,
            'threat_detected' => false,
            'alerts_generated' => []
        ];
        
        // 检查数据包是否应该被过滤
        if ($this->shouldFilterPacket($packet)) {
            $result['filtered'] = true;
            return $result;
        }
        
        // 更新网络统计
        $this->updateNetworkStats($packet);
        
        // 分析流量
        if ($this->config['traffic_analysis']) {
            $this->analyzeTraffic($packet);
        }
        
        // 跟踪连接
        if ($this->config['connection_tracking']) {
            $this->trackConnection($packet);
        }
        
        // 检测威胁
        if ($this->config['threat_detection']) {
            $threatResult = $this->detectThreats($packet);
            if ($threatResult['threat_detected']) {
                $result['threat_detected'] = true;
                $result['alerts_generated'] = $threatResult['alerts'];
            }
        }
        
        $result['processed'] = true;
        
        return $result;
    }
    
    /**
     * 检查是否应该过滤数据包
     * 
     * @param array $packet 数据包信息
     * @return bool 是否过滤
     */
    private function shouldFilterPacket(array $packet): bool
    {
        $sourceIp = $packet['source_ip'] ?? '';
        $destIp = $packet['dest_ip'] ?? '';
        $protocol = $packet['protocol'] ?? '';
        $port = $packet['port'] ?? 0;
        
        // 检查黑名单IP
        if (in_array($sourceIp, $this->config['filter_rules']['blacklist_ips'])) {
            return true;
        }
        
        // 检查白名单IP（如果设置了白名单，只允许白名单内的IP）
        if (!empty($this->config['filter_rules']['whitelist_ips']) && 
            !in_array($sourceIp, $this->config['filter_rules']['whitelist_ips'])) {
            return true;
        }
        
        // 检查忽略的协议
        if (in_array($protocol, $this->config['filter_rules']['ignored_protocols'])) {
            return true;
        }
        
        // 检查监控端口
        if (!in_array($port, $this->config['filter_rules']['monitored_ports'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 更新网络统计
     * 
     * @param array $packet 数据包信息
     */
    private function updateNetworkStats(array $packet): void
    {
        $this->networkStats['total_packets']++;
        $this->networkStats['total_bytes'] += $packet['size'] ?? 0;
        $this->networkStats['last_update'] = time();
        
        // 更新协议统计
        $protocol = $packet['protocol'] ?? 'unknown';
        if (!isset($this->trafficAnalyzer['protocol_stats'][$protocol])) {
            $this->trafficAnalyzer['protocol_stats'][$protocol] = 0;
        }
        $this->trafficAnalyzer['protocol_stats'][$protocol]++;
        
        // 更新端口统计
        $port = $packet['port'] ?? 0;
        if (!isset($this->trafficAnalyzer['port_stats'][$port])) {
            $this->trafficAnalyzer['port_stats'][$port] = 0;
        }
        $this->trafficAnalyzer['port_stats'][$port]++;
        
        // 更新IP统计
        $sourceIp = $packet['source_ip'] ?? '';
        if (!isset($this->trafficAnalyzer['ip_stats'][$sourceIp])) {
            $this->trafficAnalyzer['ip_stats'][$sourceIp] = 0;
        }
        $this->trafficAnalyzer['ip_stats'][$sourceIp]++;
    }
    
    /**
     * 分析流量
     * 
     * @param array $packet 数据包信息
     */
    private function analyzeTraffic(array $packet): void
    {
        $this->trafficAnalyzer['packet_counter']++;
        $this->trafficAnalyzer['byte_counter'] += $packet['size'] ?? 0;
        
        // 检查高带宽使用
        $currentTime = time();
        if ($currentTime - $this->trafficAnalyzer['last_reset'] >= 60) {
            $bytesPerSecond = $this->trafficAnalyzer['byte_counter'] / 60;
            
            if ($bytesPerSecond > $this->config['alert_thresholds']['high_bandwidth']) {
                $this->generateAlert('high_bandwidth', [
                    'bytes_per_second' => $bytesPerSecond,
                    'threshold' => $this->config['alert_thresholds']['high_bandwidth']
                ]);
            }
            
            // 重置计数器
            $this->trafficAnalyzer['packet_counter'] = 0;
            $this->trafficAnalyzer['byte_counter'] = 0;
            $this->trafficAnalyzer['last_reset'] = $currentTime;
        }
    }
    
    /**
     * 跟踪连接
     * 
     * @param array $packet 数据包信息
     */
    private function trackConnection(array $packet): void
    {
        $connectionId = $this->generateConnectionId($packet);
        
        if (!isset($this->connectionPool[$connectionId])) {
            $this->connectionPool[$connectionId] = [
                'id' => $connectionId,
                'source_ip' => $packet['source_ip'] ?? '',
                'dest_ip' => $packet['dest_ip'] ?? '',
                'source_port' => $packet['source_port'] ?? 0,
                'dest_port' => $packet['dest_port'] ?? 0,
                'protocol' => $packet['protocol'] ?? '',
                'start_time' => time(),
                'last_activity' => time(),
                'packet_count' => 0,
                'byte_count' => 0,
                'status' => 'active'
            ];
            
            $this->networkStats['active_connections']++;
            
            if ($this->networkStats['active_connections'] > $this->networkStats['peak_connections']) {
                $this->networkStats['peak_connections'] = $this->networkStats['active_connections'];
            }
        }
        
        $connection = &$this->connectionPool[$connectionId];
        $connection['last_activity'] = time();
        $connection['packet_count']++;
        $connection['byte_count'] += $packet['size'] ?? 0;
        
        // 检查连接洪水攻击
        $this->checkConnectionFlood($packet);
    }
    
    /**
     * 生成连接ID
     * 
     * @param array $packet 数据包信息
     * @return string 连接ID
     */
    private function generateConnectionId(array $packet): string
    {
        $sourceIp = $packet['source_ip'] ?? '';
        $destIp = $packet['dest_ip'] ?? '';
        $sourcePort = $packet['source_port'] ?? 0;
        $destPort = $packet['dest_port'] ?? 0;
        $protocol = $packet['protocol'] ?? '';
        
        return md5("{$sourceIp}:{$sourcePort}-{$destIp}:{$destPort}-{$protocol}");
    }
    
    /**
     * 检查连接洪水攻击
     * 
     * @param array $packet 数据包信息
     */
    private function checkConnectionFlood(array $packet): void
    {
        $sourceIp = $packet['source_ip'] ?? '';
        $currentTime = time();
        
        // 统计该IP的连接数
        $connectionCount = 0;
        foreach ($this->connectionPool as $connection) {
            if ($connection['source_ip'] === $sourceIp && 
                $currentTime - $connection['start_time'] < 60) {
                $connectionCount++;
            }
        }
        
        if ($connectionCount > $this->config['alert_thresholds']['connection_flood']) {
            $this->generateAlert('connection_flood', [
                'source_ip' => $sourceIp,
                'connection_count' => $connectionCount,
                'threshold' => $this->config['alert_thresholds']['connection_flood']
            ]);
        }
    }
    
    /**
     * 检测威胁
     * 
     * @param array $packet 数据包信息
     * @return array 检测结果
     */
    private function detectThreats(array $packet): array
    {
        $result = [
            'threat_detected' => false,
            'threat_type' => 'none',
            'confidence' => 0.0,
            'alerts' => []
        ];
        
        // 检查可疑模式
        $suspiciousPatterns = $this->detectSuspiciousPatterns($packet);
        if (!empty($suspiciousPatterns)) {
            $result['threat_detected'] = true;
            $result['threat_type'] = 'suspicious_pattern';
            $result['confidence'] = 0.7;
            $result['alerts'][] = $this->generateAlert('suspicious_pattern', [
                'patterns' => $suspiciousPatterns,
                'packet_info' => $packet
            ]);
        }
        
        // 检查攻击签名
        $attackSignatures = $this->detectAttackSignatures($packet);
        if (!empty($attackSignatures)) {
            $result['threat_detected'] = true;
            $result['threat_type'] = 'attack_signature';
            $result['confidence'] = 0.9;
            $result['alerts'][] = $this->generateAlert('attack_signature', [
                'signatures' => $attackSignatures,
                'packet_info' => $packet
            ]);
        }
        
        // 检查异常流量
        $anomalyScore = $this->detectAnomalousTraffic($packet);
        if ($anomalyScore > $this->config['alert_thresholds']['anomalous_traffic']) {
            $result['threat_detected'] = true;
            $result['threat_type'] = 'anomalous_traffic';
            $result['confidence'] = $anomalyScore;
            $result['alerts'][] = $this->generateAlert('anomalous_traffic', [
                'anomaly_score' => $anomalyScore,
                'packet_info' => $packet
            ]);
        }
        
        return $result;
    }
    
    /**
     * 检测可疑模式
     * 
     * @param array $packet 数据包信息
     * @return array 可疑模式列表
     */
    private function detectSuspiciousPatterns(array $packet): array
    {
        $patterns = [];
        $content = $packet['content'] ?? '';
        
        // 检查SQL注入模式
        if (preg_match('/\b(union|select|insert|update|delete|drop|create)\s+.*\b(from|into|where)/i', $content)) {
            $patterns[] = 'sql_injection';
        }
        
        // 检查XSS模式
        if (preg_match('/<script[^>]*>.*<\/script>/is', $content)) {
            $patterns[] = 'xss_attack';
        }
        
        // 检查命令注入模式
        if (preg_match('/\b(eval|exec|system|shell_exec|passthru)\s*\(/i', $content)) {
            $patterns[] = 'command_injection';
        }
        
        // 检查路径遍历模式
        if (preg_match('/\.\.\/|\.\.\\\|%2e%2e/i', $content)) {
            $patterns[] = 'path_traversal';
        }
        
        return $patterns;
    }
    
    /**
     * 检测攻击签名
     * 
     * @param array $packet 数据包信息
     * @return array 攻击签名列表
     */
    private function detectAttackSignatures(array $packet): array
    {
        $signatures = [];
        $content = $packet['content'] ?? '';
        
        // 检查DDoS攻击签名
        if (preg_match('/\b(slowloris|slowread|slowpost)\b/i', $content)) {
            $signatures[] = 'ddos_attack';
        }
        
        // 检查暴力破解签名
        if (preg_match('/\b(brute|force|password|login)\b.*\b(attempt|trial|guess)\b/i', $content)) {
            $signatures[] = 'brute_force_attack';
        }
        
        // 检查扫描攻击签名
        if (preg_match('/\b(nmap|scan|probe|reconnaissance)\b/i', $content)) {
            $signatures[] = 'port_scanning';
        }
        
        return $signatures;
    }
    
    /**
     * 检测异常流量
     * 
     * @param array $packet 数据包信息
     * @return float 异常评分
     */
    private function detectAnomalousTraffic(array $packet): float
    {
        $anomalyScore = 0.0;
        
        // 检查数据包大小异常
        $packetSize = $packet['size'] ?? 0;
        if ($packetSize > 1500 || $packetSize < 64) {
            $anomalyScore += 0.3;
        }
        
        // 检查协议异常
        $protocol = $packet['protocol'] ?? '';
        if (!in_array($protocol, ['TCP', 'UDP', 'HTTP', 'HTTPS'])) {
            $anomalyScore += 0.2;
        }
        
        // 检查端口异常
        $port = $packet['port'] ?? 0;
        if ($port < 1 || $port > 65535) {
            $anomalyScore += 0.4;
        }
        
        // 检查IP地址异常
        $sourceIp = $packet['source_ip'] ?? '';
        if (!filter_var($sourceIp, FILTER_VALIDATE_IP)) {
            $anomalyScore += 0.5;
        }
        
        return min($anomalyScore, 1.0);
    }
    
    /**
     * 生成告警
     * 
     * @param string $alertType 告警类型
     * @param array $alertData 告警数据
     * @return array 告警信息
     */
    private function generateAlert(string $alertType, array $alertData): array
    {
        $alert = [
            'id' => uniqid('alert_', true),
            'type' => $alertType,
            'timestamp' => time(),
            'severity' => $this->determineAlertSeverity($alertType),
            'data' => $alertData,
            'status' => 'active'
        ];
        
        $this->alertManager['active_alerts'][] = $alert;
        $this->alertManager['alert_history'][] = $alert;
        
        $this->logger->warning('网络监控告警', [
            'alert_type' => $alertType,
            'severity' => $alert['severity'],
            'data' => $alertData
        ]);
        
        return $alert;
    }
    
    /**
     * 确定告警严重性
     * 
     * @param string $alertType 告警类型
     * @return string 严重性级别
     */
    private function determineAlertSeverity(string $alertType): string
    {
        $severityMap = [
            'high_bandwidth' => 'medium',
            'connection_flood' => 'high',
            'suspicious_pattern' => 'medium',
            'attack_signature' => 'critical',
            'anomalous_traffic' => 'low'
        ];
        
        return $severityMap[$alertType] ?? 'low';
    }
    
    /**
     * 初始化监控线程
     */
    private function initializeMonitoringThreads(): void
    {
        // 这里应该启动实际的监控线程
        // 由于PHP的限制，这里只是模拟
        $this->logger->info('监控线程已初始化');
    }
    
    /**
     * 启动流量分析
     */
    private function startTrafficAnalysis(): void
    {
        $this->logger->info('流量分析已启动');
    }
    
    /**
     * 启动连接跟踪
     */
    private function startConnectionTracking(): void
    {
        $this->logger->info('连接跟踪已启动');
    }
    
    /**
     * 启动威胁检测
     */
    private function startThreatDetection(): void
    {
        $this->logger->info('威胁检测已启动');
    }
    
    /**
     * 停止监控线程
     */
    private function stopMonitoringThreads(): void
    {
        $this->logger->info('监控线程已停止');
    }
    
    /**
     * 保存最终统计
     */
    private function saveFinalStats(): void
    {
        $this->logger->info('保存最终网络统计');
    }
    
    /**
     * 获取网络统计
     * 
     * @return array 网络统计信息
     */
    public function getNetworkStats(): array
    {
        $this->performCleanup();
        
        return array_merge($this->networkStats, [
            'traffic_analysis' => [
                'protocol_stats' => $this->trafficAnalyzer['protocol_stats'],
                'port_stats' => array_slice($this->trafficAnalyzer['port_stats'], 0, 10, true),
                'ip_stats' => array_slice($this->trafficAnalyzer['ip_stats'], 0, 10, true)
            ],
            'connection_pool' => [
                'active_connections' => count(array_filter($this->connectionPool, function($conn) {
                    return $conn['status'] === 'active';
                })),
                'total_connections' => count($this->connectionPool)
            ],
            'alerts' => [
                'active_alerts' => count($this->alertManager['active_alerts']),
                'total_alerts' => count($this->alertManager['alert_history'])
            ]
        ]);
    }
    
    /**
     * 执行清理
     */
    private function performCleanup(): void
    {
        $currentTime = time();
        if ($currentTime - $this->lastCleanup < $this->cleanupInterval) {
            return;
        }
        
        // 清理过期连接
        foreach ($this->connectionPool as $connectionId => $connection) {
            if ($currentTime - $connection['last_activity'] > 300) { // 5分钟无活动
                unset($this->connectionPool[$connectionId]);
                $this->networkStats['active_connections']--;
            }
        }
        
        // 清理过期告警
        $this->alertManager['active_alerts'] = array_filter(
            $this->alertManager['active_alerts'],
            function($alert) use ($currentTime) {
                return $currentTime - $alert['timestamp'] < 3600; // 1小时内的告警
            }
        );
        
        $this->lastCleanup = $currentTime;
    }
    
    /**
     * 获取活跃告警
     * 
     * @return array 活跃告警列表
     */
    public function getActiveAlerts(): array
    {
        return $this->alertManager['active_alerts'];
    }
    
    /**
     * 获取告警历史
     * 
     * @param int $limit 限制数量
     * @return array 告警历史
     */
    public function getAlertHistory(int $limit = 100): array
    {
        return array_slice($this->alertManager['alert_history'], -$limit);
    }
    
    /**
     * 清除告警
     * 
     * @param string $alertId 告警ID
     * @return bool 是否成功
     */
    public function clearAlert(string $alertId): bool
    {
        foreach ($this->alertManager['active_alerts'] as $key => $alert) {
            if ($alert['id'] === $alertId) {
                $this->alertManager['active_alerts'][$key]['status'] = 'cleared';
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取连接详情
     * 
     * @param string $connectionId 连接ID
     * @return array|null 连接详情
     */
    public function getConnectionDetails(string $connectionId): ?array
    {
        return $this->connectionPool[$connectionId] ?? null;
    }
    
    /**
     * 获取所有活跃连接
     * 
     * @return array 活跃连接列表
     */
    public function getActiveConnections(): array
    {
        return array_filter($this->connectionPool, function($connection) {
            return $connection['status'] === 'active';
        });
    }
}
