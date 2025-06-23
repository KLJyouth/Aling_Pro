<?php

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Utils\Logger;
use Illuminate\Database\Connection;

/**
 * 监控服务
 * 提供系统监控、性能跟踪和健康检查功能
 */
class MonitoringService
{
    private Connection $db;
    private Logger $logger;
    private CacheService $cache;

    public function __construct(Connection $db, Logger $logger, CacheService $cache)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * 获取实时监控指标
     */
    public function getRealTimeMetrics(): array
    {
        $cacheKey = 'realtime_metrics';
        $cached = $this->cache->get($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        $metrics = [
            'system' => $this->getSystemMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'application' => $this->getApplicationMetrics(),
            'performance' => $this->getPerformanceMetrics(),
            'security' => $this->getSecurityMetrics()
        ];

        $this->cache->set($cacheKey, $metrics, 30); // 缓存30秒
        return $metrics;
    }

    /**
     * 记录监控数据
     */
    public function recordMetric(string $type, string $name, $value, string $unit = null, array $metadata = []): void
    {
        try {
            $this->db->table('system_monitoring')->insert([
                'metric_type' => $type,
                'metric_name' => $name,
                'metric_value' => is_numeric($value) ? $value : null,
                'metric_unit' => $unit,
                'metric_data' => json_encode(array_merge(['raw_value' => $value], $metadata)),
                'severity' => $this->determineSeverity($type, $name, $value),
                'status' => $this->determineStatus($type, $name, $value),
                'collected_at' => now(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to record metric: ' . $e->getMessage(), [
                'type' => $type,
                'name' => $name,
                'value' => $value
            ]);
        }
    }

    /**
     * 获取系统指标
     */
    private function getSystemMetrics(): array
    {
        $metrics = [];

        // CPU使用率
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $metrics['cpu_load_1min'] = $load[0] ?? 0;
            $metrics['cpu_load_5min'] = $load[1] ?? 0;
            $metrics['cpu_load_15min'] = $load[2] ?? 0;
        }

        // 内存使用
        $metrics['memory_usage'] = memory_get_usage(true);
        $metrics['memory_peak'] = memory_get_peak_usage(true);
        $metrics['memory_limit'] = ini_get('memory_limit');

        // 磁盘空间
        $path = __DIR__ . '/../../storage';
        $metrics['disk_total'] = disk_total_space($path);
        $metrics['disk_free'] = disk_free_space($path);
        $metrics['disk_used'] = $metrics['disk_total'] - $metrics['disk_free'];
        $metrics['disk_usage_percent'] = ($metrics['disk_used'] / $metrics['disk_total']) * 100;

        // 网络连接
        $metrics['active_connections'] = $this->getActiveConnections();

        return $metrics;
    }

    /**
     * 获取数据库指标
     */
    private function getDatabaseMetrics(): array
    {
        $metrics = [];

        try {
            // 连接数
            $result = $this->db->select("SHOW STATUS LIKE 'Threads_connected'");
            $metrics['connections'] = $result[0]->Value ?? 0;

            // 查询统计
            $result = $this->db->select("SHOW STATUS LIKE 'Questions'");
            $metrics['total_queries'] = $result[0]->Value ?? 0;

            // 慢查询
            $result = $this->db->select("SHOW STATUS LIKE 'Slow_queries'");
            $metrics['slow_queries'] = $result[0]->Value ?? 0;

            // 表锁定
            $result = $this->db->select("SHOW STATUS LIKE 'Table_locks_waited'");
            $metrics['table_locks_waited'] = $result[0]->Value ?? 0;

            // 缓冲池命中率
            $innodb_stats = $this->db->select("
                SHOW STATUS WHERE Variable_name IN (
                    'Innodb_buffer_pool_read_requests',
                    'Innodb_buffer_pool_reads'
                )
            ");
            
            $buffer_pool_reads = 0;
            $buffer_pool_read_requests = 0;
            
            foreach ($innodb_stats as $stat) {
                if ($stat->Variable_name === 'Innodb_buffer_pool_reads') {
                    $buffer_pool_reads = $stat->Value;
                } elseif ($stat->Variable_name === 'Innodb_buffer_pool_read_requests') {
                    $buffer_pool_read_requests = $stat->Value;
                }
            }

            if ($buffer_pool_read_requests > 0) {
                $metrics['buffer_pool_hit_rate'] = (1 - ($buffer_pool_reads / $buffer_pool_read_requests)) * 100;
            }

            // 数据库大小
            $dbName = config('database.connections.mysql.database');
            $result = $this->db->select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size_mb
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [$dbName]);
            
            $metrics['database_size_mb'] = $result[0]->db_size_mb ?? 0;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get database metrics: ' . $e->getMessage());
        }

        return $metrics;
    }

    /**
     * 获取应用指标
     */
    private function getApplicationMetrics(): array
    {
        $metrics = [];

        try {
            // 今日活跃用户
            $metrics['active_users_today'] = $this->db->table('users')
                ->where('last_login_at', '>=', date('Y-m-d 00:00:00'))
                ->count();

            // 今日会话数
            $metrics['conversations_today'] = $this->db->table('conversations')
                ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                ->count();

            // 今日消息数
            $metrics['messages_today'] = $this->db->table('messages')
                ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                ->count();

            // 错误率（过去1小时）
            $totalLogs = $this->db->table('system_logs')
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                ->count();

            $errorLogs = $this->db->table('system_logs')
                ->whereIn('level', ['error', 'critical', 'alert', 'emergency'])
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                ->count();

            $metrics['error_rate'] = $totalLogs > 0 ? ($errorLogs / $totalLogs) * 100 : 0;

            // 缓存命中率
            $metrics['cache_hit_rate'] = $this->getCacheHitRate();

        } catch (\Exception $e) {
            $this->logger->error('Failed to get application metrics: ' . $e->getMessage());
        }

        return $metrics;
    }

    /**
     * 获取性能指标
     */
    private function getPerformanceMetrics(): array
    {
        $metrics = [];

        try {
            // 平均响应时间（过去1小时）
            $avgResponseTime = $this->db->table('system_monitoring')
                ->where('metric_name', 'response_time')
                ->where('collected_at', '>=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                ->avg('metric_value');

            $metrics['avg_response_time'] = round($avgResponseTime ?? 0, 2);

            // 吞吐量（每分钟请求数）
            $requestsLastMinute = $this->db->table('system_logs')
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-1 minute')))
                ->count();

            $metrics['requests_per_minute'] = $requestsLastMinute;

            // 队列任务统计
            $metrics['pending_jobs'] = $this->db->table('jobs')->count();
            $metrics['failed_jobs'] = $this->db->table('failed_jobs')
                ->where('failed_at', '>=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->count();

        } catch (\Exception $e) {
            $this->logger->error('Failed to get performance metrics: ' . $e->getMessage());
        }

        return $metrics;
    }

    /**
     * 获取安全指标
     */
    private function getSecurityMetrics(): array
    {
        $metrics = [];

        try {
            // 失败登录尝试（过去1小时）
            $failedLogins = $this->db->table('system_logs')
                ->where('message', 'LIKE', '%login failed%')
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                ->count();

            $metrics['failed_logins_1h'] = $failedLogins;

            // 可疑活动
            $suspiciousActivity = $this->db->table('system_logs')
                ->where('level', 'warning')
                ->where('message', 'LIKE', '%suspicious%')
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->count();

            $metrics['suspicious_activity_24h'] = $suspiciousActivity;

            // 最近安全扫描
            $latestScan = $this->db->table('security_scans')
                ->orderBy('start_time', 'desc')
                ->first();

            if ($latestScan) {
                $metrics['latest_scan_date'] = $latestScan->start_time;
                $metrics['latest_scan_status'] = $latestScan->status;
                $metrics['latest_scan_critical_issues'] = $latestScan->critical_issues ?? 0;
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to get security metrics: ' . $e->getMessage());
        }

        return $metrics;
    }

    /**
     * 启动后台监控任务
     */
    public function startBackgroundMonitoring(): void
    {
        // 使用队列系统启动后台监控
        $monitoringJobs = [
            'system_metrics' => 60,    // 每分钟
            'database_metrics' => 300, // 每5分钟
            'performance_metrics' => 60, // 每分钟
            'security_metrics' => 1800   // 每30分钟
        ];

        foreach ($monitoringJobs as $job => $interval) {
            $this->scheduleMonitoringJob($job, $interval);
        }
    }

    /**
     * 获取监控历史数据
     */
    public function getHistoricalData(string $metricType, string $metricName, string $period = '24h'): array
    {
        $timeMap = [
            '1h' => '-1 hour',
            '24h' => '-24 hours',
            '7d' => '-7 days',
            '30d' => '-30 days'
        ];

        $startTime = date('Y-m-d H:i:s', strtotime($timeMap[$period] ?? '-24 hours'));

        return $this->db->table('system_monitoring')
            ->select('metric_value', 'collected_at')
            ->where('metric_type', $metricType)
            ->where('metric_name', $metricName)
            ->where('collected_at', '>=', $startTime)
            ->orderBy('collected_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * 生成监控报告
     */
    public function generateMonitoringReport(string $period = '24h'): array
    {
        return [
            'period' => $period,
            'generated_at' => now(),
            'summary' => $this->getMonitoringSummary($period),
            'system_health' => $this->getSystemHealthSummary($period),
            'performance_analysis' => $this->getPerformanceAnalysis($period),
            'security_summary' => $this->getSecuritySummary($period),
            'recommendations' => $this->generateRecommendations($period)
        ];
    }

    // 辅助方法

    private function determineSeverity(string $type, string $name, $value): string
    {
        // 根据不同的指标类型和值确定严重程度
        $thresholds = [
            'performance' => [
                'response_time' => ['warning' => 1000, 'critical' => 5000],
                'cpu_load' => ['warning' => 2.0, 'critical' => 4.0],
                'memory_usage_percent' => ['warning' => 80, 'critical' => 95]
            ],
            'database' => [
                'slow_queries' => ['warning' => 10, 'critical' => 50],
                'connections' => ['warning' => 100, 'critical' => 150]
            ]
        ];

        if (!is_numeric($value)) {
            return 'info';
        }

        $metricThresholds = $thresholds[$type][$name] ?? null;
        if (!$metricThresholds) {
            return 'info';
        }

        if ($value >= ($metricThresholds['critical'] ?? PHP_FLOAT_MAX)) {
            return 'critical';
        } elseif ($value >= ($metricThresholds['warning'] ?? PHP_FLOAT_MAX)) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    private function determineStatus(string $type, string $name, $value): string
    {
        $severity = $this->determineSeverity($type, $name, $value);
        
        switch ($severity) {
            case 'critical':
                return 'critical';
            case 'warning':
                return 'warning';
            default:
                return 'normal';
        }
    }

    private function getActiveConnections(): int
    {
        try {
            $result = $this->db->select("SHOW STATUS LIKE 'Threads_connected'");
            return (int)($result[0]->Value ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getCacheHitRate(): float
    {
        // 这里需要根据实际使用的缓存系统实现
        // 暂时返回模拟数据
        return 85.5;
    }

    private function scheduleMonitoringJob(string $jobType, int $interval): void
    {
        // 实现队列任务调度
        // 这里需要与现有的队列系统集成
    }

    private function getMonitoringSummary(string $period): array
    {
        // 实现监控摘要逻辑
        return [];
    }

    private function getSystemHealthSummary(string $period): array
    {
        // 实现系统健康摘要逻辑
        return [];
    }

    private function getPerformanceAnalysis(string $period): array
    {
        // 实现性能分析逻辑
        return [];
    }

    private function getSecuritySummary(string $period): array
    {
        // 实现安全摘要逻辑
        return [];
    }

    private function generateRecommendations(string $period): array
    {
        // 实现推荐建议生成逻辑
        return [];
    }
}
