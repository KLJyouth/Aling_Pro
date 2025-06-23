<?php

declare(strict_types=1);

namespace AlingAi\Monitoring;

use Psr\Log\LoggerInterface;

/**
 * 系统监控器
 * 负责监控系统状态、资源使用情况和性能指标
 */
class SystemMonitor
{
    private LoggerInterface $logger;
    private array $thresholds;
    private array $monitors = [];
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->initializeThresholds();
    }
    
    /**
     * 获取系统状态概览
     */
    public function getSystemStatus(): array
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'system' => $this->getSystemInfo(),
            'performance' => $this->getPerformanceMetrics(),
            'resources' => $this->getResourceUsage(),
            'services' => $this->getServiceStatus(),
            'health' => $this->getHealthScore()
        ];
    }
    
    /**
     * 获取系统信息
     */
    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'operating_system' => PHP_OS,
            'architecture' => php_uname('m'),
            'hostname' => gethostname(),
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        ];
    }
    
    /**
     * 获取性能指标
     */
    public function getPerformanceMetrics(): array
    {
        $loadAvg = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        
        return [
            'memory' => [
                'current_usage' => memory_get_usage(true),
                'peak_usage' => memory_get_peak_usage(true),
                'limit' => $this->convertToBytes(ini_get('memory_limit')),
                'usage_percentage' => $this->getMemoryUsagePercentage()
            ],
            'cpu' => [
                'load_average_1min' => $loadAvg[0] ?? 0,
                'load_average_5min' => $loadAvg[1] ?? 0,
                'load_average_15min' => $loadAvg[2] ?? 0,
                'process_count' => $this->getProcessCount()
            ],
            'disk' => [
                'free_space' => disk_free_space('.'),
                'total_space' => disk_total_space('.'),
                'usage_percentage' => $this->getDiskUsagePercentage()
            ],
            'network' => $this->getNetworkStats()
        ];
    }
    
    /**
     * 获取资源使用情况
     */
    public function getResourceUsage(): array
    {
        return [
            'files' => [
                'open_files' => $this->getOpenFileCount(),
                'max_files' => $this->getMaxFileLimit()
            ],
            'processes' => [
                'current_processes' => $this->getProcessCount(),
                'max_processes' => $this->getMaxProcessLimit()
            ],
            'connections' => [
                'active_connections' => $this->getActiveConnections(),
                'max_connections' => $this->getMaxConnections()
            ]
        ];
    }
    
    /**
     * 获取服务状态
     */
    public function getServiceStatus(): array
    {
        return [
            'web_server' => $this->checkWebServerStatus(),
            'database' => $this->checkDatabaseStatus(),
            'cache' => $this->checkCacheStatus(),
            'storage' => $this->checkStorageStatus()
        ];
    }
    
    /**
     * 获取健康评分
     */
    public function getHealthScore(): array
    {
        $scores = [];
        $performance = $this->getPerformanceMetrics();
        
        // 内存健康评分
        $memoryUsage = $performance['memory']['usage_percentage'];
        if ($memoryUsage < 70) {
            $scores['memory'] = 100;
        } elseif ($memoryUsage < 85) {
            $scores['memory'] = 75;
        } elseif ($memoryUsage < 95) {
            $scores['memory'] = 50;
        } else {
            $scores['memory'] = 25;
        }
        
        // CPU健康评分
        $cpuLoad = $performance['cpu']['load_average_1min'];
        if ($cpuLoad < 1.0) {
            $scores['cpu'] = 100;
        } elseif ($cpuLoad < 2.0) {
            $scores['cpu'] = 75;
        } elseif ($cpuLoad < 4.0) {
            $scores['cpu'] = 50;
        } else {
            $scores['cpu'] = 25;
        }
        
        // 磁盘健康评分
        $diskUsage = $performance['disk']['usage_percentage'];
        if ($diskUsage < 70) {
            $scores['disk'] = 100;
        } elseif ($diskUsage < 85) {
            $scores['disk'] = 75;
        } elseif ($diskUsage < 95) {
            $scores['disk'] = 50;
        } else {
            $scores['disk'] = 25;
        }
        
        // 整体健康评分
        $overallScore = array_sum($scores) / count($scores);
        
        return [
            'overall_score' => round($overallScore),
            'individual_scores' => $scores,
            'status' => $this->getHealthStatus($overallScore),
            'recommendations' => $this->getHealthRecommendations($scores)
        ];
    }
    
    /**
     * 开始监控特定指标
     */
    public function startMonitoring(string $metric, callable $callback = null): void
    {
        $this->monitors[$metric] = [
            'start_time' => microtime(true),
            'callback' => $callback,
            'status' => 'active'
        ];
        
        $this->logger->info("Started monitoring: {$metric}");
    }
    
    /**
     * 停止监控特定指标
     */
    public function stopMonitoring(string $metric): array
    {
        if (!isset($this->monitors[$metric])) {
            throw new \InvalidArgumentException("Monitoring not started for metric: {$metric}");
        }
        
        $monitor = $this->monitors[$metric];
        $endTime = microtime(true);
        $duration = $endTime - $monitor['start_time'];
        
        $result = [
            'metric' => $metric,
            'duration' => round($duration * 1000, 2), // ms
            'start_time' => $monitor['start_time'],
            'end_time' => $endTime,
            'status' => 'completed'
        ];
        
        if ($monitor['callback']) {
            try {
                $result['callback_result'] = call_user_func($monitor['callback'], $result);
            } catch (\Exception $e) {
                $result['callback_error'] = $e->getMessage();
            }
        }
        
        unset($this->monitors[$metric]);
        $this->logger->info("Stopped monitoring: {$metric}", $result);
        
        return $result;
    }
    
    /**
     * 检查是否有警报
     */
    public function checkAlerts(): array
    {
        $alerts = [];
        $performance = $this->getPerformanceMetrics();
        
        // 内存警报
        if ($performance['memory']['usage_percentage'] > $this->thresholds['memory']['critical']) {
            $alerts[] = [
                'type' => 'memory',
                'level' => 'critical',
                'message' => 'Memory usage is critically high',
                'value' => $performance['memory']['usage_percentage'],
                'threshold' => $this->thresholds['memory']['critical']
            ];
        } elseif ($performance['memory']['usage_percentage'] > $this->thresholds['memory']['warning']) {
            $alerts[] = [
                'type' => 'memory',
                'level' => 'warning',
                'message' => 'Memory usage is high',
                'value' => $performance['memory']['usage_percentage'],
                'threshold' => $this->thresholds['memory']['warning']
            ];
        }
        
        // CPU警报
        if ($performance['cpu']['load_average_1min'] > $this->thresholds['cpu']['critical']) {
            $alerts[] = [
                'type' => 'cpu',
                'level' => 'critical',
                'message' => 'CPU load is critically high',
                'value' => $performance['cpu']['load_average_1min'],
                'threshold' => $this->thresholds['cpu']['critical']
            ];
        } elseif ($performance['cpu']['load_average_1min'] > $this->thresholds['cpu']['warning']) {
            $alerts[] = [
                'type' => 'cpu',
                'level' => 'warning',
                'message' => 'CPU load is high',
                'value' => $performance['cpu']['load_average_1min'],
                'threshold' => $this->thresholds['cpu']['warning']
            ];
        }
        
        // 磁盘警报
        if ($performance['disk']['usage_percentage'] > $this->thresholds['disk']['critical']) {
            $alerts[] = [
                'type' => 'disk',
                'level' => 'critical',
                'message' => 'Disk usage is critically high',
                'value' => $performance['disk']['usage_percentage'],
                'threshold' => $this->thresholds['disk']['critical']
            ];
        } elseif ($performance['disk']['usage_percentage'] > $this->thresholds['disk']['warning']) {
            $alerts[] = [
                'type' => 'disk',
                'level' => 'warning',
                'message' => 'Disk usage is high',
                'value' => $performance['disk']['usage_percentage'],
                'threshold' => $this->thresholds['disk']['warning']
            ];
        }
        
        return $alerts;
    }
    
    /**
     * 获取监控历史
     */
    public function getMonitoringHistory(int $hours = 24): array
    {
        // 这里应该从数据库或日志文件中获取历史数据
        // 为演示目的，返回模拟数据
        return [
            'period_hours' => $hours,
            'data_points' => 0,
            'message' => 'Monitoring history feature requires database integration'
        ];
    }
    
    /**
     * 生成监控报告
     */
    public function generateReport(): array
    {
        $status = $this->getSystemStatus();
        $alerts = $this->checkAlerts();
        
        return [
            'report_time' => date('Y-m-d H:i:s'),
            'system_status' => $status,
            'alerts' => $alerts,
            'alert_count' => count($alerts),
            'critical_alerts' => count(array_filter($alerts, fn($alert) => $alert['level'] === 'critical')),
            'warning_alerts' => count(array_filter($alerts, fn($alert) => $alert['level'] === 'warning')),
            'recommendations' => $this->generateRecommendations($status, $alerts)
        ];
    }
    
    /**
     * 初始化阈值
     */
    private function initializeThresholds(): void
    {
        $this->thresholds = [
            'memory' => [
                'warning' => 80,  // 80%
                'critical' => 95  // 95%
            ],
            'cpu' => [
                'warning' => 2.0,  // Load average 2.0
                'critical' => 4.0  // Load average 4.0
            ],
            'disk' => [
                'warning' => 85,  // 85%
                'critical' => 95  // 95%
            ]
        ];
    }
    
    /**
     * 获取内存使用率百分比
     */
    private function getMemoryUsagePercentage(): float
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return 0.0;
        }
        
        $limitBytes = $this->convertToBytes($limit);
        $currentUsage = memory_get_usage(true);
        
        return round(($currentUsage / $limitBytes) * 100, 2);
    }
    
    /**
     * 获取磁盘使用率百分比
     */
    private function getDiskUsagePercentage(): float
    {
        $freeBytes = disk_free_space('.');
        $totalBytes = disk_total_space('.');
        
        if ($totalBytes === false || $freeBytes === false) {
            return 0.0;
        }
        
        $usedBytes = $totalBytes - $freeBytes;
        return round(($usedBytes / $totalBytes) * 100, 2);
    }
    
    /**
     * 转换内存大小字符串为字节数
     */
    private function convertToBytes(string $size): int
    {
        if ($size === '-1') {
            return PHP_INT_MAX;
        }
        
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $value = (int) $size;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * 获取进程数量
     */
    private function getProcessCount(): int
    {
        if (function_exists('shell_exec')) {
            $output = shell_exec('ps aux | wc -l');
            return $output ? (int) trim($output) : 0;
        }
        return 0;
    }
    
    /**
     * 获取网络统计
     */
    private function getNetworkStats(): array
    {
        return [
            'status' => 'connected',
            'interfaces' => $this->getNetworkInterfaces()
        ];
    }
    
    /**
     * 获取网络接口
     */
    private function getNetworkInterfaces(): array
    {
        // 简化版本，实际应该从系统获取网络接口信息
        return [
            'eth0' => ['status' => 'up', 'ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown']
        ];
    }
    
    /**
     * 获取打开文件数量
     */
    private function getOpenFileCount(): int
    {
        // 简化版本，实际应该从系统获取
        return 0;
    }
    
    /**
     * 获取最大文件限制
     */
    private function getMaxFileLimit(): int
    {
        // 简化版本，实际应该从系统获取
        return 1024;
    }
    
    /**
     * 获取最大进程限制
     */
    private function getMaxProcessLimit(): int
    {
        // 简化版本，实际应该从系统获取
        return 1024;
    }
    
    /**
     * 获取活动连接数
     */
    private function getActiveConnections(): int
    {
        // 简化版本，实际应该从网络统计获取
        return 0;
    }
    
    /**
     * 获取最大连接数
     */
    private function getMaxConnections(): int
    {
        // 简化版本，实际应该从系统配置获取
        return 1000;
    }
    
    /**
     * 检查Web服务器状态
     */
    private function checkWebServerStatus(): array
    {
        return [
            'status' => 'running',
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'port' => $_SERVER['SERVER_PORT'] ?? 80
        ];
    }
    
    /**
     * 检查数据库状态
     */
    private function checkDatabaseStatus(): array
    {
        // 简化版本，实际应该检查数据库连接
        return [
            'status' => 'unknown',
            'type' => 'file_based',
            'message' => 'Database connection check requires integration'
        ];
    }
    
    /**
     * 检查缓存状态
     */
    private function checkCacheStatus(): array
    {
        return [
            'status' => 'active',
            'type' => 'file_cache',
            'memory_usage' => 0
        ];
    }
    
    /**
     * 检查存储状态
     */
    private function checkStorageStatus(): array
    {
        return [
            'status' => 'available',
            'free_space' => disk_free_space('.'),
            'total_space' => disk_total_space('.')
        ];
    }
    
    /**
     * 获取健康状态描述
     */
    private function getHealthStatus(float $score): string
    {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 75) {
            return 'good';
        } elseif ($score >= 50) {
            return 'fair';
        } else {
            return 'poor';
        }
    }
    
    /**
     * 获取健康建议
     */
    private function getHealthRecommendations(array $scores): array
    {
        $recommendations = [];
        
        if ($scores['memory'] < 75) {
            $recommendations[] = [
                'type' => 'memory',
                'message' => 'Consider optimizing memory usage or increasing memory limit'
            ];
        }
        
        if ($scores['cpu'] < 75) {
            $recommendations[] = [
                'type' => 'cpu',
                'message' => 'High CPU load detected. Review CPU-intensive operations'
            ];
        }
        
        if ($scores['disk'] < 75) {
            $recommendations[] = [
                'type' => 'disk',
                'message' => 'Disk space is running low. Consider cleanup or expansion'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * 生成建议
     */
    private function generateRecommendations(array $status, array $alerts): array
    {
        $recommendations = [];
        
        foreach ($alerts as $alert) {
            switch ($alert['type']) {
                case 'memory':
                    $recommendations[] = [
                        'priority' => $alert['level'],
                        'action' => 'Optimize memory usage',
                        'details' => 'Review memory-intensive operations and consider increasing memory limit'
                    ];
                    break;
                case 'cpu':
                    $recommendations[] = [
                        'priority' => $alert['level'],
                        'action' => 'Reduce CPU load',
                        'details' => 'Optimize CPU-intensive operations or scale horizontally'
                    ];
                    break;
                case 'disk':
                    $recommendations[] = [
                        'priority' => $alert['level'],
                        'action' => 'Free up disk space',
                        'details' => 'Clean up temporary files, logs, or expand storage capacity'
                    ];
                    break;
            }
        }
        
        return $recommendations;
    }
}
