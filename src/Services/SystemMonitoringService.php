<?php

namespace AlingAi\Services;

use AlingAi\Config\EnhancedConfig;

/**
 * 系统监控服务
 * 监控系统资源使用情况并发送告警
 */
class SystemMonitoringService
{
    private static $instance = null;
    private $config;
    private $dbService;
    private $emailService;
    private $lastAlertTimes = [];

    private function __construct()
    {
        $this->config = EnhancedConfig::getInstance();
        $this->dbService = EnhancedDatabaseService::getInstance();
        $this->emailService = EnhancedEmailService::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 开始监控
     */
    public function startMonitoring(): void
    {
        $this->log('系统监控服务启动');

        // 设置监控间隔
        $resourceInterval = $this->config->get('monitoring.resource_check_interval', 60000) / 1000; // 转换为秒
        $healthInterval = $this->config->get('monitoring.health_check_frequency', 300000) / 1000;
        $dbInterval = $this->config->get('monitoring.db_monitor_interval', 60000) / 1000;

        // 启动监控循环
        while (true) {
            try {
                // 系统资源监控
                $this->checkSystemResources();

                // 数据库监控
                if (time() % $dbInterval === 0) {
                    $this->checkDatabaseHealth();
                }

                // 健康检查
                if (time() % $healthInterval === 0) {
                    $this->performHealthCheck();
                }

                // 清理过期数据
                if (date('H:i') === '02:00') {
                    $this->cleanupOldData();
                }

                sleep($resourceInterval);

            } catch (\Exception $e) {
                $this->logError('监控循环异常', $e->getMessage());
                sleep(60); // 异常时等待1分钟再继续
            }
        }
    }

    /**
     * 检查系统资源
     */
    public function checkSystemResources(): array
    {
        $metrics = $this->collectSystemMetrics();
        
        // 记录指标到数据库
        $this->saveMetrics($metrics);

        // 检查告警阈值
        $this->checkResourceAlerts($metrics);

        return $metrics;
    }

    /**
     * 收集系统指标
     */
    public function collectSystemMetrics(): array
    {
        $metrics = [
            'timestamp' => date('Y-m-d H:i:s'),
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
            'process_count' => $this->getProcessCount(),
            'network_stats' => $this->getNetworkStats(),
        ];

        return $metrics;
    }

    /**
     * 获取CPU使用率
     */
    private function getCpuUsage(): float
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows系统
            $cmd = 'wmic cpu get loadpercentage /value';
            $output = shell_exec($cmd);
            if (preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                return (float) $matches[1];
            }
        } else {
            // Linux/Unix系统
            $load = sys_getloadavg();
            $cpuCores = $this->getCpuCores();
            return ($load[0] / $cpuCores) * 100;
        }

        return 0.0;
    }

    /**
     * 获取内存使用率
     */
    private function getMemoryUsage(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows系统
            $output = shell_exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /value');
            $total = 0;
            $free = 0;
            
            if (preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $matches)) {
                $total = (int) $matches[1] * 1024; // 转换为字节
            }
            if (preg_match('/FreePhysicalMemory=(\d+)/', $output, $matches)) {
                $free = (int) $matches[1] * 1024;
            }
            
            $used = $total - $free;
            $usage = $total > 0 ? ($used / $total) * 100 : 0;
            
        } else {
            // Linux/Unix系统
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+) kB/', $meminfo, $totalMatches);
            preg_match('/MemAvailable:\s+(\d+) kB/', $meminfo, $availableMatches);
            
            $total = (int) $totalMatches[1] * 1024;
            $available = (int) $availableMatches[1] * 1024;
            $used = $total - $available;
            $usage = $total > 0 ? ($used / $total) * 100 : 0;
        }

        return [
            'total' => $total,
            'used' => $used,
            'free' => $total - $used,
            'usage_percent' => round($usage, 2)
        ];
    }

    /**
     * 获取磁盘使用率
     */
    private function getDiskUsage(): array
    {
        $disk = [];
        
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows系统 - 检查C盘
            $total = disk_total_space('C:');
            $free = disk_free_space('C:');
        } else {
            // Linux/Unix系统 - 检查根目录
            $total = disk_total_space('/');
            $free = disk_free_space('/');
        }

        $used = $total - $free;
        $usage = $total > 0 ? ($used / $total) * 100 : 0;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'usage_percent' => round($usage, 2)
        ];
    }

    /**
     * 获取系统负载
     */
    private function getLoadAverage(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0],
                '5min' => $load[1],
                '15min' => $load[2]
            ];
        }

        return ['1min' => 0, '5min' => 0, '15min' => 0];
    }

    /**
     * 获取进程数量
     */
    private function getProcessCount(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('tasklist /fo csv | find /c /v ""');
            return (int) trim($output) - 1; // 减去标题行
        } else {
            $output = shell_exec('ps aux | wc -l');
            return (int) trim($output) - 1; // 减去标题行
        }
    }

    /**
     * 获取网络统计
     */
    private function getNetworkStats(): array
    {
        // 简化的网络统计
        return [
            'connections' => $this->getActiveConnections(),
            'bandwidth_in' => 0,
            'bandwidth_out' => 0
        ];
    }

    /**
     * 获取活跃连接数
     */
    private function getActiveConnections(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('netstat -an | find "ESTABLISHED" /c');
            return (int) trim($output);
        } else {
            $output = shell_exec('netstat -an | grep ESTABLISHED | wc -l');
            return (int) trim($output);
        }
    }

    /**
     * 获取CPU核心数
     */
    private function getCpuCores(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return (int) shell_exec('wmic cpu get NumberOfCores /value | findstr NumberOfCores');
        } else {
            return (int) shell_exec('nproc');
        }
    }

    /**
     * 保存指标到数据库
     */
    private function saveMetrics(array $metrics): void
    {
        try {
            $this->dbService->insert('system_metrics', [
                'timestamp' => $metrics['timestamp'],
                'cpu_usage' => $metrics['cpu_usage'],
                'memory_total' => $metrics['memory_usage']['total'],
                'memory_used' => $metrics['memory_usage']['used'],
                'memory_usage_percent' => $metrics['memory_usage']['usage_percent'],
                'disk_total' => $metrics['disk_usage']['total'],
                'disk_used' => $metrics['disk_usage']['used'],
                'disk_usage_percent' => $metrics['disk_usage']['usage_percent'],
                'load_1min' => $metrics['load_average']['1min'],
                'load_5min' => $metrics['load_average']['5min'],
                'load_15min' => $metrics['load_average']['15min'],
                'process_count' => $metrics['process_count'],
                'active_connections' => $metrics['network_stats']['connections'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logError('保存系统指标失败', $e->getMessage());
        }
    }

    /**
     * 检查资源告警
     */
    private function checkResourceAlerts(array $metrics): void
    {
        $alerts = $this->config->get('alerts');

        // CPU告警
        $cpuUsage = $metrics['cpu_usage'];
        if ($cpuUsage >= $alerts['cpu_critical']) {
            $this->sendAlert('critical', 'CPU使用率过高', "CPU使用率达到 {$cpuUsage}%，超过临界阈值 {$alerts['cpu_critical']}%");
        } elseif ($cpuUsage >= $alerts['cpu_warning']) {
            $this->sendAlert('warning', 'CPU使用率告警', "CPU使用率达到 {$cpuUsage}%，超过警告阈值 {$alerts['cpu_warning']}%");
        }

        // 内存告警
        $memoryUsage = $metrics['memory_usage']['usage_percent'];
        if ($memoryUsage >= $alerts['memory_critical']) {
            $this->sendAlert('critical', '内存使用率过高', "内存使用率达到 {$memoryUsage}%，超过临界阈值 {$alerts['memory_critical']}%");
        } elseif ($memoryUsage >= $alerts['memory_warning']) {
            $this->sendAlert('warning', '内存使用率告警', "内存使用率达到 {$memoryUsage}%，超过警告阈值 {$alerts['memory_warning']}%");
        }

        // 磁盘告警
        $diskUsage = $metrics['disk_usage']['usage_percent'];
        if ($diskUsage >= $alerts['disk_critical']) {
            $this->sendAlert('critical', '磁盘空间不足', "磁盘使用率达到 {$diskUsage}%，超过临界阈值 {$alerts['disk_critical']}%");
        } elseif ($diskUsage >= $alerts['disk_warning']) {
            $this->sendAlert('warning', '磁盘空间告警', "磁盘使用率达到 {$diskUsage}%，超过警告阈值 {$alerts['disk_warning']}%");
        }
    }

    /**
     * 检查数据库健康状态
     */
    public function checkDatabaseHealth(): array
    {
        $dbHealth = $this->dbService->healthCheck();
        
        // 记录数据库健康状态
        try {
            $this->dbService->insert('database_health', [
                'timestamp' => date('Y-m-d H:i:s'),
                'mysql_status' => $dbHealth['mysql'] ? 1 : 0,
                'redis_status' => $dbHealth['redis'] ? 1 : 0,
                'mongodb_status' => $dbHealth['mongodb'] ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logError('记录数据库健康状态失败', $e->getMessage());
        }

        // 检查数据库告警
        foreach ($dbHealth as $service => $status) {
            if ($service !== 'timestamp' && !$status) {
                $this->sendAlert('critical', '数据库服务异常', "数据库服务 {$service} 连接失败");
            }
        }

        return $dbHealth;
    }

    /**
     * 执行健康检查
     */
    public function performHealthCheck(): array
    {
        $health = [
            'timestamp' => date('Y-m-d H:i:s'),
            'system' => $this->checkSystemHealth(),
            'database' => $this->checkDatabaseHealth(),
            'ai_services' => $this->checkAIServicesHealth(),
            'overall_status' => 'healthy'
        ];

        // 判断整体状态
        foreach ($health as $key => $status) {
            if ($key !== 'timestamp' && $key !== 'overall_status') {
                if (is_array($status)) {
                    foreach ($status as $subStatus) {
                        if (is_bool($subStatus) && !$subStatus) {
                            $health['overall_status'] = 'unhealthy';
                            break 2;
                        }
                    }
                }
            }
        }

        return $health;
    }

    /**
     * 检查系统健康状态
     */
    private function checkSystemHealth(): array
    {
        $metrics = $this->collectSystemMetrics();
        $alerts = $this->config->get('alerts');

        return [
            'cpu_healthy' => $metrics['cpu_usage'] < $alerts['cpu_critical'],
            'memory_healthy' => $metrics['memory_usage']['usage_percent'] < $alerts['memory_critical'],
            'disk_healthy' => $metrics['disk_usage']['usage_percent'] < $alerts['disk_critical'],
        ];
    }

    /**
     * 检查AI服务健康状态
     */
    private function checkAIServicesHealth(): array
    {
        try {
            $aiService = EnhancedAIService::getInstance();
            return $aiService->healthCheck();
        } catch (\Exception $e) {
            $this->logError('AI服务健康检查失败', $e->getMessage());
            return ['deepseek' => false, 'baidu' => false];
        }
    }

    /**
     * 发送告警
     */
    private function sendAlert(string $level, string $subject, string $message): void
    {
        $alertKey = md5($subject . $message);
        $throttleInterval = $this->config->get('mail.throttle_interval', 300000) / 1000; // 转换为秒
        
        // 检查是否在限流期内
        if (isset($this->lastAlertTimes[$alertKey])) {
            if (time() - $this->lastAlertTimes[$alertKey] < $throttleInterval) {
                return; // 在限流期内，不发送告警
            }
        }

        try {
            $fullMessage = "
告警级别: " . strtoupper($level) . "
告警时间: " . date('Y-m-d H:i:s') . "
服务器: " . gethostname() . "
详细信息: {$message}

请及时处理此告警。

---
AlingAi Pro 监控系统
            ";

            $this->emailService->sendAlert($subject, $fullMessage);
            
            // 记录告警发送时间
            $this->lastAlertTimes[$alertKey] = time();
            
            $this->log("已发送 {$level} 级别告警: {$subject}");

        } catch (\Exception $e) {
            $this->logError('发送告警邮件失败', $e->getMessage());
        }
    }

    /**
     * 清理过期数据
     */
    private function cleanupOldData(): void
    {
        $retentionDays = $this->config->get('monitoring.metrics_retention_days', 30);
        
        try {
            // 清理过期的系统指标
            $this->dbService->query(
                "DELETE FROM system_metrics WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$retentionDays]
            );

            // 清理过期的数据库健康记录
            $this->dbService->query(
                "DELETE FROM database_health WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$retentionDays]
            );

            $this->log("已清理 {$retentionDays} 天前的监控数据");

        } catch (\Exception $e) {
            $this->logError('清理过期数据失败', $e->getMessage());
        }
    }

    /**
     * 获取监控报告
     */
    public function getMonitoringReport(string $period = 'today'): array
    {
        try {
            $whereClause = '';
            switch ($period) {
                case 'today':
                    $whereClause = 'WHERE DATE(created_at) = CURDATE()';
                    break;
                case 'week':
                    $whereClause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'month':
                    $whereClause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
            }

            $systemStats = $this->dbService->fetchOne("
                SELECT 
                    AVG(cpu_usage) as avg_cpu,
                    MAX(cpu_usage) as max_cpu,
                    AVG(memory_usage_percent) as avg_memory,
                    MAX(memory_usage_percent) as max_memory,
                    AVG(disk_usage_percent) as avg_disk,
                    MAX(disk_usage_percent) as max_disk,
                    COUNT(*) as data_points
                FROM system_metrics 
                {$whereClause}
            ");

            return [
                'period' => $period,
                'system_stats' => $systemStats,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logError('获取监控报告失败', $e->getMessage());
            return [];
        }
    }

    /**
     * 记录日志
     */
    private function log(string $message): void
    {
        $logMessage = sprintf('[%s] [MONITOR] %s', date('Y-m-d H:i:s'), $message);
        error_log($logMessage);
    }

    /**
     * 记录错误日志
     */
    private function logError(string $message, string $error): void
    {
        $logMessage = sprintf('[%s] [MONITOR ERROR] %s: %s', date('Y-m-d H:i:s'), $message, $error);
        error_log($logMessage);
    }

    /**
     * 获取指标数据（API兼容方法）
     */
    public function getMetrics(string $timeRange = 'today', string $metricType = 'all'): array
    {
        try {
            $whereClause = '';
            $params = [];

            // 时间范围过滤
            switch ($timeRange) {
                case 'today':
                    $whereClause = 'WHERE DATE(created_at) = CURDATE()';
                    break;
                case '1h':
                    $whereClause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)';
                    break;
                case '24h':
                    $whereClause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)';
                    break;
                case 'week':
                    $whereClause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'month':
                    $whereClause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
            }

            // 指标类型过滤
            $selectFields = '*';
            if ($metricType !== 'all') {
                switch ($metricType) {
                    case 'cpu':
                        $selectFields = 'timestamp, cpu_usage, created_at';
                        break;
                    case 'memory':
                        $selectFields = 'timestamp, memory_usage_percent, memory_total, memory_used, created_at';
                        break;
                    case 'disk':
                        $selectFields = 'timestamp, disk_usage_percent, disk_total, disk_used, created_at';
                        break;
                }
            }

            $metrics = $this->dbService->fetchAll("
                SELECT {$selectFields}
                FROM system_metrics 
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT 1000
            ", $params);

            return [
                'time_range' => $timeRange,
                'metric_type' => $metricType,
                'data' => $metrics,
                'count' => count($metrics),
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logError('获取指标数据失败', $e->getMessage());
            return [];
        }
    }

    /**
     * 获取服务状态（API兼容方法）
     */
    public function getStatus(): array
    {
        try {
            $currentMetrics = $this->collectSystemMetrics();
            $alerts = $this->config->get('alerts');

            return [
                'monitoring_active' => true,
                'system_health' => [
                    'cpu' => [
                        'usage' => $currentMetrics['cpu_usage'],
                        'status' => $currentMetrics['cpu_usage'] < $alerts['cpu_critical'] ? 'healthy' : 'critical'
                    ],
                    'memory' => [
                        'usage_percent' => $currentMetrics['memory_usage']['usage_percent'],
                        'status' => $currentMetrics['memory_usage']['usage_percent'] < $alerts['memory_critical'] ? 'healthy' : 'critical'
                    ],
                    'disk' => [
                        'usage_percent' => $currentMetrics['disk_usage']['usage_percent'],
                        'status' => $currentMetrics['disk_usage']['usage_percent'] < $alerts['disk_critical'] ? 'healthy' : 'critical'
                    ]
                ],
                'database_health' => $this->checkDatabaseHealth(),
                'ai_services_health' => $this->checkAIServicesHealth(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'monitoring_active' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * 获取告警列表（API兼容方法）
     */
    public function getAlerts(string $status = 'all', string $severity = 'all', int $limit = 50): array
    {
        try {
            $whereClause = '';
            $params = [];

            // 构建查询条件
            $conditions = [];
            
            if ($status !== 'all') {
                $conditions[] = 'status = :status';
                $params['status'] = $status;
            }

            if ($severity !== 'all') {
                $conditions[] = 'severity = :severity';
                $params['severity'] = $severity;
            }

            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }

            $alerts = $this->dbService->fetchAll("
                SELECT * FROM system_alerts 
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT :limit
            ", array_merge($params, ['limit' => $limit]));

            return [
                'alerts' => $alerts,
                'count' => count($alerts),
                'filters' => [
                    'status' => $status,
                    'severity' => $severity,
                    'limit' => $limit
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logError('获取告警列表失败', $e->getMessage());
            return [];
        }
    }

    /**
     * 清理旧日志（API兼容方法）
     */
    public function cleanupOldLogs(): array
    {
        try {
            $retentionDays = $this->config->get('monitoring.log_retention_days', 30);
            
            // 清理系统日志
            $logsDeleted = $this->dbService->query(
                "DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$retentionDays]
            )->rowCount();

            // 清理错误日志
            $errorLogsDeleted = $this->dbService->query(
                "DELETE FROM error_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$retentionDays]
            )->rowCount();

            return [
                'logs_deleted' => $logsDeleted,
                'error_logs_deleted' => $errorLogsDeleted,
                'retention_days' => $retentionDays,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logError('清理旧日志失败', $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 清理旧指标（API兼容方法）
     */
    public function cleanupOldMetrics(): array
    {
        try {
            $retentionDays = $this->config->get('monitoring.metrics_retention_days', 30);
            
            // 清理系统指标
            $metricsDeleted = $this->dbService->query(
                "DELETE FROM system_metrics WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$retentionDays]
            )->rowCount();

            // 清理数据库健康记录
            $healthRecordsDeleted = $this->dbService->query(
                "DELETE FROM database_health WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$retentionDays]
            )->rowCount();

            return [
                'metrics_deleted' => $metricsDeleted,
                'health_records_deleted' => $healthRecordsDeleted,
                'retention_days' => $retentionDays,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logError('清理旧指标失败', $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
