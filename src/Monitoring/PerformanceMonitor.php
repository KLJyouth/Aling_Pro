<?php

declare(strict_types=1);

namespace AlingAi\Pro\Monitoring;

use Psr\Log\LoggerInterface;
use AlingAi\Pro\Core\StructuredLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * 性能监控系统
 * 
 * 监控系统性能指标、错误追踪和实时告警
 * 
 * @package AlingAi\Pro\Monitoring
 */
class PerformanceMonitor
{
    private LoggerInterface $logger;
    private array $config;
    private array $metrics = [];
    private float $startTime;

    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'enabled' => true,
            'sample_rate' => 1.0,
            'slow_query_threshold' => 1000, // 毫秒
            'memory_threshold' => 128 * 1024 * 1024, // 128MB
            'response_time_threshold' => 5000, // 5秒
            'error_rate_threshold' => 0.05, // 5%
            'alert_channels' => ['log', 'email'],
        ], $config);
        
        $this->startTime = microtime(true);
        
        if ($this->config['enabled']) {
            $this->startMonitoring();
        }
    }

    /**
     * 开始监控
     */
    private function startMonitoring(): void
    {
        // 注册关闭时的性能统计
        register_shutdown_function([$this, 'recordPerformanceMetrics']);
        
        // 监控数据库查询
        if (class_exists('Illuminate\Support\Facades\DB')) {
            DB::listen([$this, 'logSlowQuery']);
        }
    }

    /**
     * 记录性能指标
     */
    public function recordPerformanceMetrics(): void
    {
        $endTime = microtime(true);
        $responseTime = ($endTime - $this->startTime) * 1000; // 毫秒
        $memoryUsage = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        $metrics = [
            'response_time' => $responseTime,
            'memory_usage' => $memoryUsage,
            'memory_limit' => $this->parseMemoryLimit($memoryLimit),
            'cpu_usage' => $this->getCpuUsage(),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'cli',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'timestamp' => time(),
        ];

        // 检查性能阈值
        $this->checkPerformanceThresholds($metrics);
        
        // 记录到日志
        $this->logger->info('Performance metrics recorded', $metrics);
        
        // 存储到缓存用于实时监控
        $this->storeMetricsToCache($metrics);
    }

    /**
     * 记录慢查询
     */
    public function logSlowQuery($query, $bindings = null, $time = null): void
    {
        if ($time && $time > $this->config['slow_query_threshold']) {
            $slowQuery = [
                'sql' => $query,
                'bindings' => $bindings,
                'time' => $time,
                'timestamp' => time(),
            ];

            $this->logger->warning('Slow query detected', $slowQuery);
            
            // 发送告警
            $this->sendAlert('slow_query', $slowQuery);
        }
    }

    /**
     * 记录错误
     */
    public function recordError(Exception $exception, array $context = []): void
    {
        $errorData = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context,
            'timestamp' => time(),
        ];

        $this->logger->error('Exception occurred', $errorData);
        
        // 更新错误率统计
        $this->updateErrorRate();
        
        // 检查错误率阈值
        $this->checkErrorRateThreshold();
    }

    /**
     * 获取实时性能数据
     */
    public function getRealTimeMetrics(): array
    {
        $cacheKey = 'performance_metrics_' . date('YmdH');
        $metrics = Cache::get($cacheKey, []);
        
        if (empty($metrics)) {
            return [
                'avg_response_time' => 0,
                'max_response_time' => 0,
                'avg_memory_usage' => 0,
                'max_memory_usage' => 0,
                'request_count' => 0,
                'error_count' => 0,
                'error_rate' => 0,
            ];
        }

        return [
            'avg_response_time' => array_sum(array_column($metrics, 'response_time')) / count($metrics),
            'max_response_time' => max(array_column($metrics, 'response_time')),
            'avg_memory_usage' => array_sum(array_column($metrics, 'memory_usage')) / count($metrics),
            'max_memory_usage' => max(array_column($metrics, 'memory_usage')),
            'request_count' => count($metrics),
            'error_count' => Cache::get('error_count_' . date('YmdH'), 0),
            'error_rate' => $this->calculateErrorRate(),
        ];
    }

    /**
     * 获取系统健康状态
     */
    public function getHealthStatus(): array
    {
        $metrics = $this->getRealTimeMetrics();
        $issues = [];

        // 检查响应时间
        if ($metrics['avg_response_time'] > $this->config['response_time_threshold']) {
            $issues[] = 'High response time: ' . round($metrics['avg_response_time'], 2) . 'ms';
        }

        // 检查内存使用
        if ($metrics['avg_memory_usage'] > $this->config['memory_threshold']) {
            $issues[] = 'High memory usage: ' . $this->formatBytes($metrics['avg_memory_usage']);
        }

        // 检查错误率
        if ($metrics['error_rate'] > $this->config['error_rate_threshold']) {
            $issues[] = 'High error rate: ' . round($metrics['error_rate'] * 100, 2) . '%';
        }

        // 检查数据库连接
        try {
            DB::select('SELECT 1');
        } catch (Exception $e) {
            $issues[] = 'Database connection failed: ' . $e->getMessage();
        }

        // 检查缓存连接
        try {
            Cache::put('health_check', true, 10);
            if (!Cache::get('health_check')) {
                $issues[] = 'Cache system not working';
            }
        } catch (Exception $e) {
            $issues[] = 'Cache connection failed: ' . $e->getMessage();
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'unhealthy',
            'issues' => $issues,
            'metrics' => $metrics,
            'timestamp' => time(),
        ];
    }

    /**
     * 检查性能阈值
     */
    private function checkPerformanceThresholds(array $metrics): void
    {
        // 检查响应时间
        if ($metrics['response_time'] > $this->config['response_time_threshold']) {
            $this->sendAlert('high_response_time', $metrics);
        }

        // 检查内存使用
        if ($metrics['memory_usage'] > $this->config['memory_threshold']) {
            $this->sendAlert('high_memory_usage', $metrics);
        }
    }

    /**
     * 更新错误率统计
     */
    private function updateErrorRate(): void
    {
        $cacheKey = 'error_count_' . date('YmdH');
        Cache::increment($cacheKey, 1, 3600);
    }

    /**
     * 检查错误率阈值
     */
    private function checkErrorRateThreshold(): void
    {
        $errorRate = $this->calculateErrorRate();
        
        if ($errorRate > $this->config['error_rate_threshold']) {
            $this->sendAlert('high_error_rate', [
                'error_rate' => $errorRate,
                'threshold' => $this->config['error_rate_threshold'],
            ]);
        }
    }

    /**
     * 计算错误率
     */
    private function calculateErrorRate(): float
    {
        $requestCount = Cache::get('performance_metrics_' . date('YmdH'), []);
        $errorCount = Cache::get('error_count_' . date('YmdH'), 0);
        
        if (count($requestCount) === 0) {
            return 0.0;
        }

        return $errorCount / count($requestCount);
    }

    /**
     * 存储指标到缓存
     */
    private function storeMetricsToCache(array $metrics): void
    {
        $cacheKey = 'performance_metrics_' . date('YmdH');
        $existingMetrics = Cache::get($cacheKey, []);
        $existingMetrics[] = $metrics;
        
        // 只保留最近1000条记录
        if (count($existingMetrics) > 1000) {
            $existingMetrics = array_slice($existingMetrics, -1000);
        }
        
        Cache::put($cacheKey, $existingMetrics, 3600);
    }

    /**
     * 发送告警
     */
    private function sendAlert(string $type, array $data): void
    {
        $alertData = [
            'type' => $type,
            'data' => $data,
            'timestamp' => time(),
            'server' => gethostname(),
        ];

        foreach ($this->config['alert_channels'] as $channel) {
            switch ($channel) {
                case 'log':
                    $this->logger->critical('Performance alert', $alertData);
                    break;
                case 'email':
                    // 集成邮件发送
                    break;
                case 'webhook':
                    // 集成 Webhook 通知
                    break;
            }
        }
    }

    /**
     * 获取 CPU 使用率
     */
    private function getCpuUsage(): float
    {
        if (!function_exists('sys_getloadavg')) {
            return 0.0;
        }

        $load = sys_getloadavg();
        return $load[0] ?? 0.0;
    }

    /**
     * 解析内存限制
     */
    private function parseMemoryLimit(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }

    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
