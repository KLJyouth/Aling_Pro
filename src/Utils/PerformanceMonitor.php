<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use AlingAi\Services\CacheService;

/**
 * 性能监控器
 * 
 * 提供全面的系统和应用性能监控
 * 优化性能：智能采样、缓存优化、异步处理
 * 增强功能：实时监控、性能分析、告警系统
 */
class PerformanceMonitor
{
    private LoggerInterface $logger;
    private CacheService $cacheService;
    private array $config;
    private array $metrics = [];
    private array $startTimes = [];
    
    public function __construct(
        LoggerInterface $logger,
        CacheService $cacheService,
        array $config = []
    ) {
        $this->logger = $logger;
        $this->cacheService = $cacheService;
        $this->config = array_merge([
            'enabled' => true,
            'sampling_rate' => 0.1, // 10%采样率
            'metrics_retention' => 86400, // 24小时
            'alert_thresholds' => [
                'cpu_usage' => 80,
                'memory_usage' => 85,
                'disk_usage' => 90,
                'response_time' => 2000,
                'error_rate' => 5
            ],
            'monitoring' => [
                'system' => true,
                'application' => true,
                'database' => true,
                'cache' => true,
                'network' => true
            ]
        ], $config);
    }
    
    /**
     * 开始监控
     */
    public function startMonitoring(string $context = 'default'): void
    {
        if (!$this->config['enabled']) {
            return;
        }
        
        $this->startTimes[$context] = [
            'start_time' => microtime(true),
            'memory_start' => memory_get_usage(),
            'peak_memory_start' => memory_get_peak_usage()
        ];
        
        $this->logger->debug('开始性能监控', ['context' => $context]);
    }
    
    /**
     * 结束监控
     */
    public function endMonitoring(string $context = 'default', array $additionalData = []): array
    {
        if (!$this->config['enabled'] || !isset($this->startTimes[$context])) {
            return [];
        }
        
        $startData = $this->startTimes[$context];
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $endPeakMemory = memory_get_peak_usage();
        
        $metrics = [
            'context' => $context,
            'duration' => round(($endTime - $startData['start_time']) * 1000, 2),
            'memory_usage' => $endMemory - $startData['memory_start'],
            'peak_memory_usage' => $endPeakMemory - $startData['peak_memory_start'],
            'memory_peak' => $endPeakMemory,
            'timestamp' => date('Y-m-d H:i:s'),
            'additional_data' => $additionalData
        ];
        
        // 记录指标
        $this->recordMetrics($metrics);
        
        // 检查告警阈值
        $this->checkAlerts($metrics);
        
        // 清理开始时间
        unset($this->startTimes[$context]);
        
        $this->logger->debug('性能监控结束', $metrics);
        
        return $metrics;
    }
    
    /**
     * 记录指标
     */
    private function recordMetrics(array $metrics): void
    {
        $key = 'performance_metrics_' . date('Y-m-d-H');
        $existingMetrics = $this->cacheService->get($key) ?: [];
        
        $existingMetrics[] = $metrics;
        
        // 限制指标数量
        if (count($existingMetrics) > 1000) {
            $existingMetrics = array_slice($existingMetrics, -500);
        }
        
        $this->cacheService->set($key, $existingMetrics, $this->config['metrics_retention']);
    }
    
    /**
     * 检查告警
     */
    private function checkAlerts(array $metrics): void
    {
        $alerts = [];
        
        // 检查响应时间
        if ($metrics['duration'] > $this->config['alert_thresholds']['response_time']) {
            $alerts[] = [
                'type' => 'response_time',
                'value' => $metrics['duration'],
                'threshold' => $this->config['alert_thresholds']['response_time'],
                'context' => $metrics['context']
            ];
        }
        
        // 检查内存使用
        $memoryUsagePercent = ($metrics['memory_peak'] / $this->getMemoryLimit()) * 100;
        if ($memoryUsagePercent > $this->config['alert_thresholds']['memory_usage']) {
            $alerts[] = [
                'type' => 'memory_usage',
                'value' => $memoryUsagePercent,
                'threshold' => $this->config['alert_thresholds']['memory_usage'],
                'context' => $metrics['context']
            ];
        }
        
        if (!empty($alerts)) {
            $this->sendAlerts($alerts);
        }
    }
    
    /**
     * 发送告警
     */
    private function sendAlerts(array $alerts): void
    {
        foreach ($alerts as $alert) {
            $this->logger->warning('性能告警', $alert);
        }
        
        // 这里应该实现具体的告警逻辑（邮件、短信、webhook等）
    }
    
    /**
     * 获取系统性能指标
     */
    public function getSystemMetrics(): array
    {
        if (!$this->config['monitoring']['system']) {
            return [];
        }
        
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
            'uptime' => $this->getUptime(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 获取CPU使用率
     */
    private function getCpuUsage(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                'load_1min' => $load[0],
                'load_5min' => $load[1],
                'load_15min' => $load[2]
            ];
        }
        
        // 使用proc文件系统（Linux）
        if (file_exists('/proc/stat')) {
            $stat = file_get_contents('/proc/stat');
            $lines = explode("\n", $stat);
            $cpu = explode(' ', preg_replace('/\s+/', ' ', trim($lines[0])));
            
            $total = array_sum(array_slice($cpu, 1));
            $idle = $cpu[4];
            $usage = (($total - $idle) / $total) * 100;
            
            return [
                'usage_percent' => round($usage, 2),
                'cores' => $this->getCpuCores()
            ];
        }
        
        return ['usage_percent' => 0, 'cores' => 1];
    }
    
    /**
     * 获取内存使用情况
     */
    private function getMemoryUsage(): array
    {
        $memoryLimit = $this->getMemoryLimit();
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        
        return [
            'limit' => $memoryLimit,
            'usage' => $memoryUsage,
            'peak' => $peakMemory,
            'usage_percent' => round(($memoryUsage / $memoryLimit) * 100, 2),
            'peak_percent' => round(($peakMemory / $memoryLimit) * 100, 2),
            'free' => $memoryLimit - $memoryUsage
        ];
    }
    
    /**
     * 获取磁盘使用情况
     */
    private function getDiskUsage(): array
    {
        $path = dirname(__DIR__, 2);
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;
        
        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'usage_percent' => round(($used / $total) * 100, 2)
        ];
    }
    
    /**
     * 获取负载平均值
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
     * 获取系统运行时间
     */
    private function getUptime(): int
    {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            return (int)explode(' ', $uptime)[0];
        }
        
        return time();
    }
    
    /**
     * 获取CPU核心数
     */
    private function getCpuCores(): int
    {
        if (file_exists('/proc/cpuinfo')) {
            $cpuInfo = file_get_contents('/proc/cpuinfo');
            return substr_count($cpuInfo, 'processor');
        }
        
        return 1;
    }
    
    /**
     * 获取内存限制
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        
        $unit = strtolower(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
    
    /**
     * 获取应用性能指标
     */
    public function getApplicationMetrics(): array
    {
        if (!$this->config['monitoring']['application']) {
            return [];
        }
        
        return [
            'php_version' => PHP_VERSION,
            'extensions' => $this->getLoadedExtensions(),
            'opcache' => $this->getOpcacheStatus(),
            'sessions' => $this->getSessionInfo(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 获取已加载的扩展
     */
    private function getLoadedExtensions(): array
    {
        return get_loaded_extensions();
    }
    
    /**
     * 获取OPcache状态
     */
    private function getOpcacheStatus(): array
    {
        if (function_exists('opcache_get_status')) {
            $status = opcache_get_status();
            return [
                'enabled' => $status['opcache_enabled'] ?? false,
                'memory_usage' => $status['memory_usage'] ?? [],
                'statistics' => $status['opcache_statistics'] ?? []
            ];
        }
        
        return ['enabled' => false];
    }
    
    /**
     * 获取会话信息
     */
    private function getSessionInfo(): array
    {
        return [
            'active' => session_status() === PHP_SESSION_ACTIVE,
            'name' => session_name(),
            'save_path' => session_save_path()
        ];
    }
    
    /**
     * 获取数据库性能指标
     */
    public function getDatabaseMetrics(): array
    {
        if (!$this->config['monitoring']['database']) {
            return [];
        }
        
        // 这里应该实现数据库性能监控
        // 简化实现
        return [
            'connections' => 0,
            'queries' => 0,
            'slow_queries' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 获取缓存性能指标
     */
    public function getCacheMetrics(): array
    {
        if (!$this->config['monitoring']['cache']) {
            return [];
        }
        
        return [
            'hits' => $this->cacheService->getStats()['hits'] ?? 0,
            'misses' => $this->cacheService->getStats()['misses'] ?? 0,
            'hit_rate' => $this->calculateHitRate(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 计算缓存命中率
     */
    private function calculateHitRate(): float
    {
        $stats = $this->cacheService->getStats();
        $hits = $stats['hits'] ?? 0;
        $misses = $stats['misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }
    
    /**
     * 获取网络性能指标
     */
    public function getNetworkMetrics(): array
    {
        if (!$this->config['monitoring']['network']) {
            return [];
        }
        
        return [
            'connections' => $this->getActiveConnections(),
            'bandwidth' => $this->getBandwidthUsage(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 获取活动连接数
     */
    private function getActiveConnections(): int
    {
        // 简化实现，实际应该查询系统
        return 0;
    }
    
    /**
     * 获取带宽使用情况
     */
    private function getBandwidthUsage(): array
    {
        // 简化实现，实际应该查询系统
        return [
            'in' => 0,
            'out' => 0
        ];
    }
    
    /**
     * 获取性能报告
     */
    public function getPerformanceReport(array $options = []): array
    {
        $options = array_merge([
            'include_system' => true,
            'include_application' => true,
            'include_database' => true,
            'include_cache' => true,
            'include_network' => true,
            'time_range' => '1h'
        ], $options);
        
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => []
        ];
        
        if ($options['include_system']) {
            $report['system'] = $this->getSystemMetrics();
        }
        
        if ($options['include_application']) {
            $report['application'] = $this->getApplicationMetrics();
        }
        
        if ($options['include_database']) {
            $report['database'] = $this->getDatabaseMetrics();
        }
        
        if ($options['include_cache']) {
            $report['cache'] = $this->getCacheMetrics();
        }
        
        if ($options['include_network']) {
            $report['network'] = $this->getNetworkMetrics();
        }
        
        // 生成摘要
        $report['summary'] = $this->generateSummary($report);
        
        return $report;
    }
    
    /**
     * 生成性能摘要
     */
    private function generateSummary(array $report): array
    {
        $summary = [
            'status' => 'healthy',
            'alerts' => 0,
            'warnings' => 0
        ];
        
        // 检查系统状态
        if (isset($report['system'])) {
            $cpuUsage = $report['system']['cpu']['usage_percent'] ?? 0;
            $memoryUsage = $report['system']['memory']['usage_percent'] ?? 0;
            $diskUsage = $report['system']['disk']['usage_percent'] ?? 0;
            
            if ($cpuUsage > $this->config['alert_thresholds']['cpu_usage']) {
                $summary['alerts']++;
                $summary['status'] = 'critical';
            } elseif ($cpuUsage > $this->config['alert_thresholds']['cpu_usage'] * 0.8) {
                $summary['warnings']++;
                $summary['status'] = 'warning';
            }
            
            if ($memoryUsage > $this->config['alert_thresholds']['memory_usage']) {
                $summary['alerts']++;
                $summary['status'] = 'critical';
            } elseif ($memoryUsage > $this->config['alert_thresholds']['memory_usage'] * 0.8) {
                $summary['warnings']++;
                $summary['status'] = 'warning';
            }
            
            if ($diskUsage > $this->config['alert_thresholds']['disk_usage']) {
                $summary['alerts']++;
                $summary['status'] = 'critical';
            } elseif ($diskUsage > $this->config['alert_thresholds']['disk_usage'] * 0.8) {
                $summary['warnings']++;
                $summary['status'] = 'warning';
            }
        }
        
        return $summary;
    }
    
    /**
     * 清理旧指标
     */
    public function cleanupOldMetrics(): int
    {
        $deletedCount = 0;
        $keys = $this->cacheService->getKeys('performance_metrics_*');
        
        foreach ($keys as $key) {
            $metrics = $this->cacheService->get($key);
            if ($metrics) {
                $oldMetrics = array_filter($metrics, function($metric) {
                    $metricTime = strtotime($metric['timestamp']);
                    return $metricTime < (time() - $this->config['metrics_retention']);
                });
                
                if (count($oldMetrics) > 0) {
                    $this->cacheService->set($key, array_values($oldMetrics), $this->config['metrics_retention']);
                    $deletedCount += count($oldMetrics);
                }
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * 重置监控数据
     */
    public function resetMetrics(): bool
    {
        $keys = $this->cacheService->getKeys('performance_metrics_*');
        
        foreach ($keys as $key) {
            $this->cacheService->delete($key);
        }
        
        $this->metrics = [];
        $this->startTimes = [];
        
        return true;
    }
} 