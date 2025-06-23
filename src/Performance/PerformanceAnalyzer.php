<?php

declare(strict_types=1);

namespace AlingAi\Performance;

use AlingAi\Services\DatabaseServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * 性能分析器
 * 负责分析系统性能指标和生成性能报告
 */
class PerformanceAnalyzer
{
    private DatabaseServiceInterface $db;
    private LoggerInterface $logger;
    private array $metrics = [];
    private float $startTime;
    
    public function __construct(DatabaseServiceInterface $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->startTime = microtime(true);
    }
    
    /**
     * 开始性能监控
     */
    public function startMonitoring(string $identifier): void
    {
        $this->metrics[$identifier] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'start_peak_memory' => memory_get_peak_usage(true),
            'cpu_start' => $this->getCpuUsage()
        ];
    }
    
    /**
     * 结束性能监控
     */
    public function endMonitoring(string $identifier): array
    {
        if (!isset($this->metrics[$identifier])) {
            throw new \InvalidArgumentException("Monitoring not started for identifier: {$identifier}");
        }
        
        $startMetrics = $this->metrics[$identifier];
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $endPeakMemory = memory_get_peak_usage(true);
        $cpuEnd = $this->getCpuUsage();
        
        $result = [
            'identifier' => $identifier,
            'execution_time' => round(($endTime - $startMetrics['start_time']) * 1000, 2), // ms
            'memory_usage' => $endMemory - $startMetrics['start_memory'],
            'peak_memory_usage' => $endPeakMemory - $startMetrics['start_peak_memory'],
            'cpu_usage' => $cpuEnd - $startMetrics['cpu_start'],
            'timestamp' => date('Y-m-d H:i:s'),
            'total_memory' => $endMemory,
            'total_peak_memory' => $endPeakMemory
        ];
        
        // 记录到数据库
        $this->saveMetrics($result);
        
        unset($this->metrics[$identifier]);
        
        return $result;
    }
    
    /**
     * 获取系统性能概览
     */
    public function getSystemOverview(): array
    {
        return [
            'memory' => [
                'current_usage' => memory_get_usage(true),
                'peak_usage' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
                'usage_percentage' => $this->getMemoryUsagePercentage()
            ],
            'cpu' => [
                'load' => sys_getloadavg(),
                'usage' => $this->getCpuUsage()
            ],
            'disk' => [
                'free_space' => disk_free_space('.'),
                'total_space' => disk_total_space('.'),
                'usage_percentage' => $this->getDiskUsagePercentage()
            ],
            'php' => [
                'version' => PHP_VERSION,
                'max_execution_time' => ini_get('max_execution_time'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize')
            ],
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'request_time' => $_SERVER['REQUEST_TIME'] ?? time(),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
            ]
        ];
    }
    
    /**
     * 分析数据库性能
     */
    public function analyzeDatabasePerformance(): array
    {
        $this->startMonitoring('db_performance');
        
        try {
            // 执行一些数据库性能测试
            $queries = [
                'SELECT COUNT(*) as total_records FROM information_schema.tables',
                'SHOW STATUS LIKE "Queries"',
                'SHOW STATUS LIKE "Connections"',
                'SHOW STATUS LIKE "Slow_queries"'
            ];
            
            $results = [];
            foreach ($queries as $query) {
                $start = microtime(true);
                $result = $this->db->query($query);
                $end = microtime(true);
                
                $results[] = [
                    'query' => $query,
                    'execution_time' => round(($end - $start) * 1000, 2),
                    'result_count' => is_array($result) ? count($result) : 1
                ];
            }
            
            $performance = $this->endMonitoring('db_performance');
            
            return [
                'overall_performance' => $performance,
                'query_results' => $results,
                'connections' => $this->getDatabaseConnections(),
                'status' => 'success'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Database performance analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 生成性能报告
     */
    public function generateReport(array $filters = []): array
    {
        $since = $filters['since'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));
        $until = $filters['until'] ?? date('Y-m-d H:i:s');
        
        try {
            $metrics = $this->getStoredMetrics($since, $until);
            
            return [
                'period' => [
                    'start' => $since,
                    'end' => $until
                ],
                'summary' => $this->calculateSummary($metrics),
                'trends' => $this->calculateTrends($metrics),
                'recommendations' => $this->generateRecommendations($metrics),
                'detailed_metrics' => $metrics
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Performance report generation failed', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Failed to generate performance report'
            ];
        }
    }
    
    /**
     * 获取CPU使用率
     */
    private function getCpuUsage(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? 0.0;
        }
        return 0.0;
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
     * 保存性能指标到数据库
     */
    private function saveMetrics(array $metrics): void
    {
        try {
            $this->db->insert('performance_metrics', [
                'identifier' => $metrics['identifier'],
                'execution_time' => $metrics['execution_time'],
                'memory_usage' => $metrics['memory_usage'],
                'peak_memory_usage' => $metrics['peak_memory_usage'],
                'cpu_usage' => $metrics['cpu_usage'],
                'total_memory' => $metrics['total_memory'],
                'total_peak_memory' => $metrics['total_peak_memory'],
                'created_at' => $metrics['timestamp']
            ]);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to save performance metrics', [
                'error' => $e->getMessage(),
                'metrics' => $metrics
            ]);
        }
    }
    
    /**
     * 获取存储的性能指标
     */
    private function getStoredMetrics(string $since, string $until): array
    {
        try {
            return $this->db->query(
                "SELECT * FROM performance_metrics WHERE created_at BETWEEN ? AND ? ORDER BY created_at DESC",
                [$since, $until]
            ) ?: [];
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve stored metrics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 计算性能摘要
     */
    private function calculateSummary(array $metrics): array
    {
        if (empty($metrics)) {
            return ['status' => 'no_data'];
        }
        
        $executionTimes = array_column($metrics, 'execution_time');
        $memoryUsages = array_column($metrics, 'memory_usage');
        $cpuUsages = array_column($metrics, 'cpu_usage');
        
        return [
            'total_requests' => count($metrics),
            'avg_execution_time' => round(array_sum($executionTimes) / count($executionTimes), 2),
            'max_execution_time' => max($executionTimes),
            'min_execution_time' => min($executionTimes),
            'avg_memory_usage' => round(array_sum($memoryUsages) / count($memoryUsages)),
            'max_memory_usage' => max($memoryUsages),
            'avg_cpu_usage' => round(array_sum($cpuUsages) / count($cpuUsages), 2),
            'max_cpu_usage' => max($cpuUsages)
        ];
    }
    
    /**
     * 计算性能趋势
     */
    private function calculateTrends(array $metrics): array
    {
        // 简化的趋势计算
        if (count($metrics) < 2) {
            return ['status' => 'insufficient_data'];
        }
        
        $recentMetrics = array_slice($metrics, 0, 10);
        $olderMetrics = array_slice($metrics, -10, 10);
        
        $recentAvgTime = array_sum(array_column($recentMetrics, 'execution_time')) / count($recentMetrics);
        $olderAvgTime = array_sum(array_column($olderMetrics, 'execution_time')) / count($olderMetrics);
        
        return [
            'execution_time_trend' => $recentAvgTime > $olderAvgTime ? 'increasing' : 'decreasing',
            'trend_percentage' => round((($recentAvgTime - $olderAvgTime) / $olderAvgTime) * 100, 2)
        ];
    }
    
    /**
     * 生成性能建议
     */
    private function generateRecommendations(array $metrics): array
    {
        $recommendations = [];
        
        if (empty($metrics)) {
            return $recommendations;
        }
        
        $summary = $this->calculateSummary($metrics);
        
        if ($summary['avg_execution_time'] > 1000) {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'high',
                'message' => 'Average execution time is high. Consider optimizing slow queries and adding caching.'
            ];
        }
        
        if ($summary['max_memory_usage'] > 50 * 1024 * 1024) { // 50MB
            $recommendations[] = [
                'type' => 'memory',
                'priority' => 'medium',
                'message' => 'High memory usage detected. Review memory-intensive operations.'
            ];
        }
        
        if ($summary['avg_cpu_usage'] > 80) {
            $recommendations[] = [
                'type' => 'cpu',
                'priority' => 'high',
                'message' => 'High CPU usage detected. Consider scaling or optimizing CPU-intensive operations.'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * 获取数据库连接信息
     */
    private function getDatabaseConnections(): array
    {
        try {
            $result = $this->db->query("SHOW STATUS LIKE 'Threads_connected'");
            return $result ?: [];
        } catch (\Exception $e) {
            return ['error' => 'Unable to retrieve connection info'];
        }
    }
}
