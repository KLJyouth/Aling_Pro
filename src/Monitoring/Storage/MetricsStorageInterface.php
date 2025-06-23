<?php
namespace AlingAi\Monitoring\Storage;

/**
 * 指标存储接口 - 定义存储和检索API监控指标的方法
 */
interface MetricsStorageInterface
{
    /**
     * 存储API调用指标
     *
     * @param array $metric 指标数据
     * @return bool 是否成功
     */
    public function storeMetric(array $metric): bool;
    
    /**
     * 存储API可用性指标
     *
     * @param array $metric 可用性指标数据
     * @return bool 是否成功
     */
    public function storeAvailabilityMetric(array $metric): bool;
    
    /**
     * 获取指定时间范围内的API指标
     *
     * @param string $apiName API名称，如果为null则获取所有API的指标
     * @param int $startTime 开始时间戳
     * @param int|null $endTime 结束时间戳，如果为null则为当前时间
     * @return array 指标数据列表
     */
    public function getMetricsByTimeRange(?string $apiName = null, int $startTime = 0, ?int $endTime = null): array;
    
    /**
     * 获取指定API的最近指标
     *
     * @param string $apiName API名称
     * @param int $limit 限制返回的记录数
     * @return array 指标数据列表
     */
    public function getRecentMetrics(string $apiName, int $limit = 100): array;
    
    /**
     * 获取指定API的平均响应时间
     *
     * @param string $apiName API名称
     * @param int $startTime 开始时间戳
     * @param int|null $endTime 结束时间戳，如果为null则为当前时间
     * @return float|null 平均响应时间，如果没有数据则返回null
     */
    public function getAverageResponseTime(string $apiName, int $startTime = 0, ?int $endTime = null): ?float;
    
    /**
     * 获取指定API的错误率
     *
     * @param string $apiName API名称
     * @param int $startTime 开始时间戳
     * @param int|null $endTime 结束时间戳，如果为null则为当前时间
     * @return float|null 错误率(0-1)，如果没有数据则返回null
     */
    public function getErrorRate(string $apiName, int $startTime = 0, ?int $endTime = null): ?float;
    
    /**
     * 获取指定API的可用性百分比
     *
     * @param string $apiName API名称
     * @param int $startTime 开始时间戳
     * @param int|null $endTime 结束时间戳，如果为null则为当前时间
     * @return float|null 可用性百分比(0-100)，如果没有数据则返回null
     */
    public function getAvailabilityPercentage(string $apiName, int $startTime = 0, ?int $endTime = null): ?float;
    
    /**
     * 获取所有API的列表
     *
     * @return array API名称列表
     */
    public function getAllApiNames(): array;
    
    /**
     * 清理旧数据
     *
     * @param int $maxAge 最大保留时间(秒)
     * @return bool 是否成功
     */
    public function cleanupOldData(int $maxAge): bool;
} 