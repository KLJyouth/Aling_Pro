<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use AlingAi\Services\CacheService;

/**
 * 缓存优化器
 * 
 * 提供智能缓存优化和管理功能
 * 优化性能：智能缓存、缓存预热、批量操作、缓存分层
 * 增强功能：缓存策略、自动清理、性能监控、缓存分析
 */
class CacheOptimizer
{
    private LoggerInterface $logger;
    private CacheService $cacheService;
    private array $config;
    private array $cacheStats = [];
    private array $cachePolicies = [];
    
    public function __construct(
        LoggerInterface $logger,
        CacheService $cacheService,
        array $config = []
    ) {
        $this->logger = $logger;
        $this->cacheService = $cacheService;
        $this->config = array_merge([
            'enabled' => true,
            'auto_optimization' => true,
            'preload_enabled' => true,
            'compression_enabled' => true,
            'compression_threshold' => 1024, // 1KB
            'compression_level' => 6,
            'max_memory_usage' => 0.8, // 80%
            'cleanup_interval' => 3600, // 1小时
            'policies' => [
                'frequently_accessed' => [
                    'ttl' => 3600,
                    'priority' => 'high',
                    'compression' => true
                ],
                'rarely_accessed' => [
                    'ttl' => 86400,
                    'priority' => 'low',
                    'compression' => true
                ],
                'temporary' => [
                    'ttl' => 300,
                    'priority' => 'medium',
                    'compression' => false
                ]
            ],
            'patterns' => [
                'user_data' => '/^user_/',
                'session_data' => '/^session_/',
                'api_response' => '/^api_/',
                'query_result' => '/^query_/',
                'config_data' => '/^config_/'
            ]
        ], $config);
        
        $this->initializeCachePolicies();
    }
    
    /**
     * 初始化缓存策略
     */
    private function initializeCachePolicies(): void
    {
        $this->cachePolicies = $this->config['policies'];
        
        // 添加默认策略
        $this->cachePolicies['default'] = [
            'ttl' => 1800,
            'priority' => 'medium',
            'compression' => false
        ];
    }
    
    /**
     * 智能缓存设置
     */
    public function smartSet(string $key, $value, array $options = []): bool
    {
        try {
            $options = array_merge([
                'policy' => 'default',
                'compress' => null,
                'ttl' => null,
                'tags' => []
            ], $options);
            
            // 确定缓存策略
            $policy = $this->determineCachePolicy($key, $options);
            
            // 压缩数据（如果需要）
            if ($this->shouldCompress($value, $policy)) {
                $value = $this->compressData($value);
                $key = $key . '_compressed';
            }
            
            // 设置缓存
            $ttl = $options['ttl'] ?? $policy['ttl'];
            $result = $this->cacheService->set($key, $value, $ttl);
            
            // 更新统计信息
            $this->updateCacheStats($key, 'set', strlen(serialize($value)));
            
            // 记录缓存操作
            $this->logger->debug('智能缓存设置', [
                'key' => $key,
                'policy' => $options['policy'],
                'ttl' => $ttl,
                'compressed' => $this->shouldCompress($value, $policy)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('智能缓存设置失败', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * 智能缓存获取
     */
    public function smartGet(string $key, $default = null)
    {
        try {
            // 尝试获取原始数据
            $value = $this->cacheService->get($key);
            
            if ($value !== null) {
                $this->updateCacheStats($key, 'hit', strlen(serialize($value)));
                return $value;
            }
            
            // 尝试获取压缩数据
            $compressedKey = $key . '_compressed';
            $value = $this->cacheService->get($compressedKey);
            
            if ($value !== null) {
                $value = $this->decompressData($value);
                $this->updateCacheStats($key, 'hit_compressed', strlen(serialize($value)));
                return $value;
            }
            
            // 缓存未命中
            $this->updateCacheStats($key, 'miss', 0);
            
            return $default;
            
        } catch (\Exception $e) {
            $this->logger->error('智能缓存获取失败', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return $default;
        }
    }
    
    /**
     * 确定缓存策略
     */
    private function determineCachePolicy(string $key, array $options): array
    {
        // 如果明确指定了策略，使用指定的策略
        if (isset($options['policy']) && isset($this->cachePolicies[$options['policy']])) {
            return $this->cachePolicies[$options['policy']];
        }
        
        // 根据键模式确定策略
        foreach ($this->config['patterns'] as $pattern => $regex) {
            if (preg_match($regex, $key)) {
                $policyName = $this->getPolicyForPattern($pattern);
                if (isset($this->cachePolicies[$policyName])) {
                    return $this->cachePolicies[$policyName];
                }
            }
        }
        
        // 使用默认策略
        return $this->cachePolicies['default'];
    }
    
    /**
     * 根据模式获取策略
     */
    private function getPolicyForPattern(string $pattern): string
    {
        $policyMap = [
            'user_data' => 'frequently_accessed',
            'session_data' => 'temporary',
            'api_response' => 'frequently_accessed',
            'query_result' => 'rarely_accessed',
            'config_data' => 'frequently_accessed'
        ];
        
        return $policyMap[$pattern] ?? 'default';
    }
    
    /**
     * 判断是否应该压缩
     */
    private function shouldCompress($value, array $policy): bool
    {
        if (!$this->config['compression_enabled']) {
            return false;
        }
        
        if (isset($policy['compression'])) {
            return $policy['compression'];
        }
        
        $size = strlen(serialize($value));
        return $size > $this->config['compression_threshold'];
    }
    
    /**
     * 压缩数据
     */
    private function compressData($data): string
    {
        $serialized = serialize($data);
        return gzencode($serialized, $this->config['compression_level']);
    }
    
    /**
     * 解压数据
     */
    private function decompressData(string $compressedData)
    {
        $serialized = gzdecode($compressedData);
        return unserialize($serialized);
    }
    
    /**
     * 更新缓存统计
     */
    private function updateCacheStats(string $key, string $operation, int $size): void
    {
        $pattern = $this->getKeyPattern($key);
        
        if (!isset($this->cacheStats[$pattern])) {
            $this->cacheStats[$pattern] = [
                'hits' => 0,
                'misses' => 0,
                'sets' => 0,
                'total_size' => 0,
                'operations' => []
            ];
        }
        
        $stats = &$this->cacheStats[$pattern];
        
        switch ($operation) {
            case 'hit':
            case 'hit_compressed':
                $stats['hits']++;
                break;
            case 'miss':
                $stats['misses']++;
                break;
            case 'set':
                $stats['sets']++;
                $stats['total_size'] += $size;
                break;
        }
        
        $stats['operations'][] = [
            'operation' => $operation,
            'key' => $key,
            'size' => $size,
            'timestamp' => time()
        ];
        
        // 保持操作历史在合理范围内
        if (count($stats['operations']) > 1000) {
            $stats['operations'] = array_slice($stats['operations'], -500);
        }
    }
    
    /**
     * 获取键模式
     */
    private function getKeyPattern(string $key): string
    {
        foreach ($this->config['patterns'] as $pattern => $regex) {
            if (preg_match($regex, $key)) {
                return $pattern;
            }
        }
        
        return 'other';
    }
    
    /**
     * 缓存预热
     */
    public function preloadCache(array $keys, array $options = []): array
    {
        if (!$this->config['preload_enabled']) {
            return ['success' => false, 'message' => '缓存预热已禁用'];
        }
        
        $options = array_merge([
            'parallel' => true,
            'max_concurrent' => 10,
            'timeout' => 30
        ], $options);
        
        $startTime = microtime(true);
        $results = [];
        
        try {
            $this->logger->info('开始缓存预热', [
                'keys_count' => count($keys)
            ]);
            
            if ($options['parallel']) {
                $results = $this->preloadParallel($keys, $options);
            } else {
                $results = $this->preloadSequential($keys, $options);
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            $successCount = count(array_filter($results, function($r) { return $r['success']; }));
            
            $this->logger->info('缓存预热完成', [
                'total' => count($keys),
                'success' => $successCount,
                'duration' => $duration
            ]);
            
            return [
                'success' => $successCount === count($keys),
                'total' => count($keys),
                'success_count' => $successCount,
                'results' => $results,
                'duration' => $duration
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('缓存预热失败', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 并行预热
     */
    private function preloadParallel(array $keys, array $options): array
    {
        $results = [];
        $chunks = array_chunk($keys, $options['max_concurrent']);
        
        foreach ($chunks as $chunk) {
            $chunkResults = $this->preloadSequential($chunk, $options);
            $results = array_merge($results, $chunkResults);
        }
        
        return $results;
    }
    
    /**
     * 顺序预热
     */
    private function preloadSequential(array $keys, array $options): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            $startTime = microtime(true);
            
            try {
                // 这里应该实现实际的数据加载逻辑
                $data = $this->loadDataForKey($key);
                
                if ($data !== null) {
                    $this->smartSet($key, $data);
                    $results[] = [
                        'success' => true,
                        'key' => $key,
                        'duration' => round((microtime(true) - $startTime) * 1000, 2)
                    ];
                } else {
                    $results[] = [
                        'success' => false,
                        'key' => $key,
                        'error' => '数据加载失败',
                        'duration' => round((microtime(true) - $startTime) * 1000, 2)
                    ];
                }
                
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'key' => $key,
                    'error' => $e->getMessage(),
                    'duration' => round((microtime(true) - $startTime) * 1000, 2)
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * 为键加载数据
     */
    private function loadDataForKey(string $key): mixed
    {
        // 这里应该实现实际的数据加载逻辑
        // 简化实现，返回模拟数据
        $pattern = $this->getKeyPattern($key);
        
        $mockData = [
            'user_data' => ['id' => 1, 'name' => 'Test User'],
            'session_data' => ['session_id' => 'test_session'],
            'api_response' => ['status' => 'success', 'data' => []],
            'query_result' => ['results' => []],
            'config_data' => ['setting' => 'value']
        ];
        
        return $mockData[$pattern] ?? null;
    }
    
    /**
     * 智能缓存清理
     */
    public function smartCleanup(array $options = []): array
    {
        $options = array_merge([
            'pattern' => null,
            'older_than' => null,
            'max_memory_usage' => $this->config['max_memory_usage'],
            'dry_run' => false
        ], $options);
        
        try {
            $this->logger->info('开始智能缓存清理', $options);
            
            $stats = $this->getCacheStats();
            $currentMemoryUsage = $this->getCurrentMemoryUsage();
            
            $cleanupPlan = $this->createCleanupPlan($stats, $currentMemoryUsage, $options);
            
            if ($options['dry_run']) {
                return [
                    'success' => true,
                    'dry_run' => true,
                    'plan' => $cleanupPlan
                ];
            }
            
            $results = $this->executeCleanupPlan($cleanupPlan);
            
            $this->logger->info('智能缓存清理完成', [
                'cleaned_keys' => count($results),
                'memory_freed' => $this->calculateMemoryFreed($results)
            ]);
            
            return [
                'success' => true,
                'cleaned_keys' => count($results),
                'results' => $results
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('智能缓存清理失败', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 创建清理计划
     */
    private function createCleanupPlan(array $stats, float $memoryUsage, array $options): array
    {
        $plan = [];
        
        // 如果内存使用率超过阈值，清理低优先级缓存
        if ($memoryUsage > $options['max_memory_usage']) {
            $plan['memory_pressure'] = [
                'reason' => 'memory_usage_high',
                'patterns' => ['rarely_accessed', 'temporary'],
                'priority' => 'high'
            ];
        }
        
        // 清理过期缓存
        $plan['expired'] = [
            'reason' => 'expired_cache',
            'patterns' => ['all'],
            'priority' => 'medium'
        ];
        
        // 根据模式清理
        if ($options['pattern']) {
            $plan['pattern'] = [
                'reason' => 'pattern_cleanup',
                'patterns' => [$options['pattern']],
                'priority' => 'medium'
            ];
        }
        
        return $plan;
    }
    
    /**
     * 执行清理计划
     */
    private function executeCleanupPlan(array $plan): array
    {
        $results = [];
        
        foreach ($plan as $type => $cleanup) {
            $typeResults = $this->executeCleanupType($cleanup);
            $results = array_merge($results, $typeResults);
        }
        
        return $results;
    }
    
    /**
     * 执行清理类型
     */
    private function executeCleanupType(array $cleanup): array
    {
        $results = [];
        
        foreach ($cleanup['patterns'] as $pattern) {
            $patternResults = $this->cleanupPattern($pattern);
            $results = array_merge($results, $patternResults);
        }
        
        return $results;
    }
    
    /**
     * 清理模式
     */
    private function cleanupPattern(string $pattern): array
    {
        // 这里应该实现实际的缓存清理逻辑
        // 简化实现
        return [
            [
                'pattern' => $pattern,
                'keys_cleaned' => 10,
                'memory_freed' => 1024
            ]
        ];
    }
    
    /**
     * 获取缓存统计
     */
    public function getCacheStats(): array
    {
        return $this->cacheStats;
    }
    
    /**
     * 获取当前内存使用率
     */
    private function getCurrentMemoryUsage(): float
    {
        // 这里应该实现实际的内存使用率计算
        // 简化实现
        return 0.5; // 50%
    }
    
    /**
     * 计算释放的内存
     */
    private function calculateMemoryFreed(array $results): int
    {
        $totalFreed = 0;
        
        foreach ($results as $result) {
            $totalFreed += $result['memory_freed'] ?? 0;
        }
        
        return $totalFreed;
    }
    
    /**
     * 获取缓存性能报告
     */
    public function getPerformanceReport(): array
    {
        $stats = $this->getCacheStats();
        $report = [
            'overall' => [
                'total_hits' => 0,
                'total_misses' => 0,
                'total_sets' => 0,
                'hit_rate' => 0,
                'total_size' => 0
            ],
            'patterns' => [],
            'recommendations' => []
        ];
        
        foreach ($stats as $pattern => $patternStats) {
            $total = $patternStats['hits'] + $patternStats['misses'];
            $hitRate = $total > 0 ? ($patternStats['hits'] / $total) * 100 : 0;
            
            $report['patterns'][$pattern] = [
                'hits' => $patternStats['hits'],
                'misses' => $patternStats['misses'],
                'sets' => $patternStats['sets'],
                'hit_rate' => round($hitRate, 2),
                'total_size' => $patternStats['total_size']
            ];
            
            $report['overall']['total_hits'] += $patternStats['hits'];
            $report['overall']['total_misses'] += $patternStats['misses'];
            $report['overall']['total_sets'] += $patternStats['sets'];
            $report['overall']['total_size'] += $patternStats['total_size'];
        }
        
        $totalRequests = $report['overall']['total_hits'] + $report['overall']['total_misses'];
        $report['overall']['hit_rate'] = $totalRequests > 0 ? 
            round(($report['overall']['total_hits'] / $totalRequests) * 100, 2) : 0;
        
        // 生成建议
        $report['recommendations'] = $this->generateOptimizationRecommendations($report);
        
        return $report;
    }
    
    /**
     * 生成优化建议
     */
    private function generateOptimizationRecommendations(array $report): array
    {
        $recommendations = [];
        
        // 命中率建议
        if ($report['overall']['hit_rate'] < 70) {
            $recommendations[] = [
                'type' => 'hit_rate',
                'priority' => 'high',
                'message' => '缓存命中率较低，建议优化缓存策略',
                'action' => 'review_cache_keys_and_ttl'
            ];
        }
        
        // 模式建议
        foreach ($report['patterns'] as $pattern => $stats) {
            if ($stats['hit_rate'] < 50) {
                $recommendations[] = [
                    'type' => 'pattern_optimization',
                    'priority' => 'medium',
                    'message' => "模式 {$pattern} 命中率较低，建议调整缓存策略",
                    'action' => "optimize_cache_for_pattern_{$pattern}"
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * 重置统计信息
     */
    public function resetStats(): void
    {
        $this->cacheStats = [];
    }
    
    /**
     * 批量操作
     */
    public function batchOperation(array $operations, array $options = []): array
    {
        $options = array_merge([
            'parallel' => true,
            'max_concurrent' => 10
        ], $options);
        
        $results = [];
        
        if ($options['parallel']) {
            $chunks = array_chunk($operations, $options['max_concurrent']);
            
            foreach ($chunks as $chunk) {
                $chunkResults = $this->executeBatchChunk($chunk);
                $results = array_merge($results, $chunkResults);
            }
        } else {
            $results = $this->executeBatchChunk($operations);
        }
        
        return $results;
    }
    
    /**
     * 执行批量操作块
     */
    private function executeBatchChunk(array $operations): array
    {
        $results = [];
        
        foreach ($operations as $operation) {
            $type = $operation['type'] ?? 'get';
            $key = $operation['key'] ?? '';
            $value = $operation['value'] ?? null;
            $options = $operation['options'] ?? [];
            
            try {
                switch ($type) {
                    case 'get':
                        $result = $this->smartGet($key, $operation['default'] ?? null);
                        $results[] = [
                            'success' => true,
                            'type' => $type,
                            'key' => $key,
                            'result' => $result
                        ];
                        break;
                        
                    case 'set':
                        $result = $this->smartSet($key, $value, $options);
                        $results[] = [
                            'success' => $result,
                            'type' => $type,
                            'key' => $key
                        ];
                        break;
                        
                    case 'delete':
                        $result = $this->cacheService->delete($key);
                        $results[] = [
                            'success' => $result,
                            'type' => $type,
                            'key' => $key
                        ];
                        break;
                        
                    default:
                        $results[] = [
                            'success' => false,
                            'type' => $type,
                            'key' => $key,
                            'error' => '未知操作类型'
                        ];
                }
                
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'type' => $type,
                    'key' => $key,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
} 