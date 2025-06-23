<?php
namespace AlingAi\Monitoring\Metrics;

use AlingAi\Monitoring\Storage\MetricsStorageInterface;
use AlingAi\Monitoring\Alert\AlertManager;
use Psr\Log\LoggerInterface;

/**
 * 指标收集器 - 收集和处理API监控指标
 */
class MetricsCollector
{
    /**
     * @var MetricsStorageInterface
     */
    private $storage;
    
    /**
     * @var AlertManager
     */
    private $alertManager;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var array 阈值配置
     */
    private $thresholds;

    /**
     * 构造函数
     */
    public function __construct(
        MetricsStorageInterface $storage, 
        AlertManager $alertManager, 
        LoggerInterface $logger,
        array $thresholds = []
    ) {
        $this->storage = $storage;
        $this->alertManager = $alertManager;
        $this->logger = $logger;
        $this->thresholds = $thresholds;
    }

    /**
     * 记录API调用指标
     *
     * @param string $apiName API名称
     * @param string $type API类型 (internal/external/incoming)
     * @param float $duration 调用耗时(秒)
     * @param bool $success 是否成功
     * @param string|null $errorMessage 错误信息
     * @param int|null $statusCode HTTP状态码
     * @param array $tags 额外标签
     * @return void
     */
    public function recordApiCall(
        string $apiName, 
        string $type, 
        float $duration, 
        bool $success, 
        ?string $errorMessage = null,
        ?int $statusCode = null,
        array $tags = []
    ): void {
        // 构建指标数据
        $timestamp = time();
        $metric = [
            'timestamp' => $timestamp,
            'api_name' => $apiName,
            'type' => $type,
            'duration' => $duration,
            'success' => $success,
            'error_message' => $errorMessage,
            'status_code' => $statusCode,
            'tags' => $tags,
        ];
        
        // 保存指标
        try {
            $this->storage->storeMetric($metric);
            
            // 检查是否需要触发告警
            $this->checkAlerts($metric);
            
            $this->logger->debug("已记录API指标", $metric);
        } catch (\Exception $e) {
            $this->logger->error("保存API指标失败", [
                'error' => $e->getMessage(),
                'metric' => $metric,
            ]);
        }
    }

    /**
     * 记录API可用性指标
     */
    public function recordApiAvailability(string $apiName, string $type, bool $isAvailable, ?string $reason = null): void
    {
        $timestamp = time();
        $metric = [
            'timestamp' => $timestamp,
            'api_name' => $apiName,
            'type' => $type,
            'available' => $isAvailable,
            'reason' => $reason,
        ];
        
        try {
            $this->storage->storeAvailabilityMetric($metric);
            
            // 如果API不可用，触发告警
            if (!$isAvailable) {
                $this->alertManager->triggerAlert(
                    AlertManager::SEVERITY_HIGH,
                    "API不可用: $apiName",
                    "API $apiName 当前不可用. 原因: " . ($reason ?? '未知'),
                    ['api_name' => $apiName, 'type' => $type]
                );
            }
            
            $this->logger->debug("已记录API可用性", $metric);
        } catch (\Exception $e) {
            $this->logger->error("保存API可用性指标失败", [
                'error' => $e->getMessage(),
                'metric' => $metric,
            ]);
        }
    }

    /**
     * 检查是否需要触发告警
     */
    private function checkAlerts(array $metric): void
    {
        // 检查响应时间阈值
        $this->checkDurationThreshold($metric);
        
        // 检查错误率阈值
        $this->checkErrorRateThreshold($metric);
        
        // 检查HTTP状态码
        $this->checkStatusCodeAlert($metric);
    }

    /**
     * 检查响应时间阈值
     */
    private function checkDurationThreshold(array $metric): void
    {
        $apiName = $metric['api_name'];
        $duration = $metric['duration'];
        
        // 获取API的响应时间阈值
        $threshold = $this->getThreshold($apiName, 'duration') ?? 
                     $this->getThreshold($metric['type'], 'duration') ?? 
                     $this->getThreshold('default', 'duration') ??
                     5.0; // 默认5秒
        
        if ($duration > $threshold) {
            $this->alertManager->triggerAlert(
                AlertManager::SEVERITY_MEDIUM,
                "API响应时间过长: $apiName",
                "API $apiName 响应时间为 {$duration}秒, 超过阈值 {$threshold}秒",
                [
                    'api_name' => $apiName,
                    'duration' => $duration,
                    'threshold' => $threshold,
                    'type' => $metric['type'],
                ]
            );
        }
    }

    /**
     * 检查错误率阈值
     */
    private function checkErrorRateThreshold(array $metric): void
    {
        if (!$metric['success']) {
            $apiName = $metric['api_name'];
            
            // 获取最近一段时间内的调用次数和失败次数
            $timeWindow = 300; // 5分钟
            $startTime = time() - $timeWindow;
            
            $recentMetrics = $this->storage->getMetricsByTimeRange($apiName, $startTime);
            
            if (empty($recentMetrics)) {
                return;
            }
            
            $totalCalls = count($recentMetrics);
            $failedCalls = 0;
            
            foreach ($recentMetrics as $m) {
                if (!$m['success']) {
                    $failedCalls++;
                }
            }
            
            $errorRate = $failedCalls / $totalCalls;
            
            // 获取API的错误率阈值
            $threshold = $this->getThreshold($apiName, 'error_rate') ?? 
                         $this->getThreshold($metric['type'], 'error_rate') ?? 
                         $this->getThreshold('default', 'error_rate') ??
                         0.1; // 默认10%
            
            if ($errorRate > $threshold) {
                $this->alertManager->triggerAlert(
                    AlertManager::SEVERITY_HIGH,
                    "API错误率过高: $apiName",
                    "API $apiName 在过去 {$timeWindow} 秒内错误率为 " . round($errorRate * 100, 2) . "%, 超过阈值 " . round($threshold * 100, 2) . "%",
                    [
                        'api_name' => $apiName,
                        'error_rate' => $errorRate,
                        'threshold' => $threshold,
                        'time_window' => $timeWindow,
                        'total_calls' => $totalCalls,
                        'failed_calls' => $failedCalls,
                        'type' => $metric['type'],
                    ]
                );
            }
        }
    }

    /**
     * 检查HTTP状态码告警
     */
    private function checkStatusCodeAlert(array $metric): void
    {
        if (isset($metric['status_code']) && $metric['status_code'] >= 500) {
            $apiName = $metric['api_name'];
            $statusCode = $metric['status_code'];
            
            $this->alertManager->triggerAlert(
                AlertManager::SEVERITY_HIGH,
                "API服务器错误: $apiName",
                "API $apiName 返回服务器错误状态码: $statusCode",
                [
                    'api_name' => $apiName,
                    'status_code' => $statusCode,
                    'type' => $metric['type'],
                ]
            );
        }
    }

    /**
     * 获取指定API和指标类型的阈值
     */
    private function getThreshold(string $key, string $metricType): ?float
    {
        if (isset($this->thresholds[$key][$metricType])) {
            return (float) $this->thresholds[$key][$metricType];
        }
        
        return null;
    }
} 