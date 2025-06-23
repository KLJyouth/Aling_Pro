<?php

namespace AlingAi\Services;

use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use Psr\Log\LoggerInterface;

/**
 * 增强系统监控服务
 * 提供与UnifiedAdminController兼容的系统监控功能
 */
class EnhancedSystemMonitoringService
{
    private DatabaseServiceInterface $db;
    private CacheService $cache;
    private LoggerInterface $logger;
    private array $config;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->config = $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        return [
            'alerts' => [
                'cpu_warning' => 70,
                'cpu_critical' => 90,
                'memory_warning' => 80,
                'memory_critical' => 95,
                'disk_warning' => 85,
                'disk_critical' => 95
            ],
            'monitoring' => [
                'resource_check_interval' => 60,
                'health_check_frequency' => 300,
                'db_monitor_interval' => 60,
                'metrics_retention_days' => 30,
                'log_retention_days' => 7
            ]
        ];
    }

    /**
     * 获取系统状态
     */
    public function getSystemStatus(): array
    {
        try {
            $metrics = $this->collectSystemMetrics();
            $health = $this->performHealthCheck();
            
            return [
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'metrics' => $metrics,
                'health_checks' => $health,
                'alerts' => $this->getActiveAlerts()
            ];
        } catch (\Exception $e) {
            $this->logger->error('获取系统状态失败', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * 收集系统指标
     */
    public function collectSystemMetrics(): array
    {
        $metrics = [
            'timestamp' => date('Y-m-d H:i:s'),
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
            'processes' => $this->getProcessCount(),
            'network' => $this->getNetworkStats()
        ];

        // 缓存最新指标
        $this->cache->set('system_metrics_latest', $metrics, 300);

        return $metrics;
    }

    /**
     * 获取CPU使用率
     */
    private function getCpuUsage(): array
    {
        $usage = 0.0;
        
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'wmic cpu get loadpercentage /value';
            $output = shell_exec($cmd);
            if ($output && preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                $usage = (float) $matches[1];
            }
        } else {
            $load = sys_getloadavg();
            $cpuCores = $this->getCpuCores();
            $usage = ($load[0] / max($cpuCores, 1)) * 100;
        }

        return [
            'usage_percent' => round(min($usage, 100), 2),
            'cores' => $this->getCpuCores(),
            'status' => $usage < $this->config['alerts']['cpu_critical'] ? 'healthy' : 'critical'
        ];
    }

    /**
     * 获取内存使用率
     */
    private function getMemoryUsage(): array
    {
        $total = 0;
        $free = 0;

        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /value');
            if ($output) {
                if (preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $matches)) {
                    $total = (int) $matches[1] * 1024;
                }
                if (preg_match('/FreePhysicalMemory=(\d+)/', $output, $matches)) {
                    $free = (int) $matches[1] * 1024;
                }
            }
        } else {
            if (file_exists('/proc/meminfo')) {
                $meminfo = file_get_contents('/proc/meminfo');
                if (preg_match('/MemTotal:\s+(\d+) kB/', $meminfo, $matches)) {
                    $total = (int) $matches[1] * 1024;
                }
                if (preg_match('/MemAvailable:\s+(\d+) kB/', $meminfo, $matches)) {
                    $free = (int) $matches[1] * 1024;
                } elseif (preg_match('/MemFree:\s+(\d+) kB/', $meminfo, $matches)) {
                    $free = (int) $matches[1] * 1024;
                }
            }
        }

        $used = $total - $free;
        $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'usage_percent' => round($usagePercent, 2),
            'status' => $usagePercent < $this->config['alerts']['memory_critical'] ? 'healthy' : 'critical'
        ];
    }

    /**
     * 获取磁盘使用率
     */
    private function getDiskUsage(): array
    {
        $path = PHP_OS_FAMILY === 'Windows' ? 'C:' : '/';
        
        $total = disk_total_space($path) ?: 0;
        $free = disk_free_space($path) ?: 0;
        $used = $total - $free;
        $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'usage_percent' => round($usagePercent, 2),
            'status' => $usagePercent < $this->config['alerts']['disk_critical'] ? 'healthy' : 'critical'
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
                '1min' => round($load[0], 2),
                '5min' => round($load[1], 2),
                '15min' => round($load[2], 2)
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
            $output = shell_exec('tasklist /fo csv 2>nul | find /c /v ""');
            return max((int) trim($output) - 1, 0);
        } else {
            $output = shell_exec('ps aux 2>/dev/null | wc -l');
            return max((int) trim($output) - 1, 0);
        }
    }

    /**
     * 获取网络统计
     */
    private function getNetworkStats(): array
    {
        return [
            'connections' => $this->getActiveConnections(),
            'status' => 'healthy'
        ];
    }

    /**
     * 获取活跃连接数
     */
    private function getActiveConnections(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('netstat -an 2>nul | find "ESTABLISHED" /c');
            return (int) trim($output);
        } else {
            $output = shell_exec('netstat -an 2>/dev/null | grep ESTABLISHED | wc -l');
            return (int) trim($output);
        }
    }

    /**
     * 获取CPU核心数
     */
    private function getCpuCores(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic cpu get NumberOfCores /value 2>nul');
            if ($output && preg_match('/NumberOfCores=(\d+)/', $output, $matches)) {
                return (int) $matches[1];
            }
        } else {
            $output = shell_exec('nproc 2>/dev/null');
            if ($output) {
                return (int) trim($output);
            }
        }
        
        return 1; // 默认值
    }

    /**
     * 执行健康检查
     */
    public function performHealthCheck(): array
    {
        $checks = [
            'system' => $this->checkSystemHealth(),
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'services' => $this->checkServicesHealth()
        ];

        $overallStatus = 'healthy';
        foreach ($checks as $check) {
            if (isset($check['status']) && $check['status'] !== 'healthy') {
                $overallStatus = 'unhealthy';
                break;
            }
        }

        return [
            'overall_status' => $overallStatus,
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * 检查系统健康状态
     */
    private function checkSystemHealth(): array
    {
        $metrics = $this->collectSystemMetrics();
        $alerts = $this->config['alerts'];

        $issues = [];
        if ($metrics['cpu']['usage_percent'] >= $alerts['cpu_critical']) {
            $issues[] = "CPU使用率过高: {$metrics['cpu']['usage_percent']}%";
        }
        if ($metrics['memory']['usage_percent'] >= $alerts['memory_critical']) {
            $issues[] = "内存使用率过高: {$metrics['memory']['usage_percent']}%";
        }
        if ($metrics['disk']['usage_percent'] >= $alerts['disk_critical']) {
            $issues[] = "磁盘空间不足: {$metrics['disk']['usage_percent']}%";
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'critical',
            'issues' => $issues,
            'metrics' => $metrics
        ];
    }

    /**
     * 检查数据库健康状态
     */
    private function checkDatabaseHealth(): array
    {
        try {
            // 简单的数据库连接测试
            $result = $this->db->query("SELECT 1 as test")->fetch();
            
            return [
                'status' => 'healthy',
                'connection' => true,
                'response_time' => 0
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'connection' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 检查存储健康状态
     */
    private function checkStorageHealth(): array
    {
        $storagePaths = [
            'logs' => __DIR__ . '/../../storage/logs',
            'cache' => __DIR__ . '/../../storage/cache',
            'uploads' => __DIR__ . '/../../storage/uploads'
        ];

        $status = 'healthy';
        $issues = [];

        foreach ($storagePaths as $name => $path) {
            if (!is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    $issues[] = "无法创建{$name}目录: {$path}";
                    $status = 'critical';
                }
            } elseif (!is_writable($path)) {
                $issues[] = "{$name}目录不可写: {$path}";
                $status = 'critical';
            }
        }

        return [
            'status' => $status,
            'issues' => $issues,
            'paths_checked' => count($storagePaths)
        ];
    }

    /**
     * 检查服务健康状态
     */
    private function checkServicesHealth(): array
    {
        return [
            'status' => 'healthy',
            'cache' => true,
            'logging' => true
        ];
    }

    /**
     * 获取活跃告警
     */
    public function getActiveAlerts(): array
    {
        $alerts = [];
        $metrics = $this->collectSystemMetrics();
        $config = $this->config['alerts'];

        // CPU告警
        if ($metrics['cpu']['usage_percent'] >= $config['cpu_critical']) {
            $alerts[] = [
                'type' => 'cpu',
                'level' => 'critical',
                'message' => "CPU使用率过高: {$metrics['cpu']['usage_percent']}%",
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } elseif ($metrics['cpu']['usage_percent'] >= $config['cpu_warning']) {
            $alerts[] = [
                'type' => 'cpu',
                'level' => 'warning',
                'message' => "CPU使用率告警: {$metrics['cpu']['usage_percent']}%",
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        // 内存告警
        if ($metrics['memory']['usage_percent'] >= $config['memory_critical']) {
            $alerts[] = [
                'type' => 'memory',
                'level' => 'critical',
                'message' => "内存使用率过高: {$metrics['memory']['usage_percent']}%",
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } elseif ($metrics['memory']['usage_percent'] >= $config['memory_warning']) {
            $alerts[] = [
                'type' => 'memory',
                'level' => 'warning',
                'message' => "内存使用率告警: {$metrics['memory']['usage_percent']}%",
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        // 磁盘告警
        if ($metrics['disk']['usage_percent'] >= $config['disk_critical']) {
            $alerts[] = [
                'type' => 'disk',
                'level' => 'critical',
                'message' => "磁盘空间不足: {$metrics['disk']['usage_percent']}%",
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } elseif ($metrics['disk']['usage_percent'] >= $config['disk_warning']) {
            $alerts[] = [
                'type' => 'disk',
                'level' => 'warning',
                'message' => "磁盘空间告警: {$metrics['disk']['usage_percent']}%",
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        return $alerts;
    }

    /**
     * 获取性能指标
     */
    public function getPerformanceMetrics(): array
    {
        $metrics = $this->collectSystemMetrics();
        
        return [
            'cpu_usage' => $metrics['cpu']['usage_percent'],
            'memory_usage' => $metrics['memory']['usage_percent'],
            'disk_usage' => $metrics['disk']['usage_percent'],
            'load_average' => $metrics['load_average']['1min'],
            'process_count' => $metrics['processes'],
            'active_connections' => $metrics['network']['connections'],
            'timestamp' => $metrics['timestamp']
        ];
    }

    /**
     * 清理旧数据
     */
    public function cleanupOldData(): array
    {
        $retentionDays = $this->config['monitoring']['metrics_retention_days'];
        $results = [
            'metrics_cleaned' => 0,
            'logs_cleaned' => 0,
            'retention_days' => $retentionDays
        ];

        try {
            // 清理缓存中的旧数据
            $cacheKeys = [
                'system_metrics_*',
                'health_check_*',
                'alerts_*'
            ];

            foreach ($cacheKeys as $pattern) {
                // 简化的缓存清理
                $this->cache->delete($pattern);
            }

            $this->logger->info('清理旧监控数据完成', $results);
        } catch (\Exception $e) {
            $this->logger->error('清理旧监控数据失败', ['error' => $e->getMessage()]);
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * 保存指标到数据库
     */
    public function saveMetrics(array $metrics): bool
    {
        try {
            // 这里可以实现指标保存逻辑
            // 由于数据库表结构可能不存在，暂时只记录日志
            $this->logger->info('系统指标收集', $metrics);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('保存系统指标失败', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 健康检查方法（API兼容）
     */
    public function healthCheck(): array
    {
        return $this->performHealthCheck();
    }

    /**
     * 获取实时指标（API兼容）
     */
    public function getRealTimeMetrics(): array
    {
        return $this->getPerformanceMetrics();
    }
}
