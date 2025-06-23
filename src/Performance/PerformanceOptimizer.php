<?php

declare(strict_types=1);

namespace AlingAi\Performance;

use AlingAi\Services\CacheService;
use AlingAi\Cache\ApplicationCacheManager;
use Psr\Log\LoggerInterface;

/**
 * 性能优化器
 * 负责自动优化系统性能，包括缓存优化、资源清理等
 */
class PerformanceOptimizer
{
    private CacheService $cache;
    private ApplicationCacheManager $advancedCache;
    private LoggerInterface $logger;
    private array $optimizationRules = [];
    
    public function __construct(
        CacheService $cache,
        ApplicationCacheManager $advancedCache,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->advancedCache = $advancedCache;
        $this->logger = $logger;
        $this->initializeOptimizationRules();
    }
    
    /**
     * 执行全面的性能优化
     */
    public function optimize(array $options = []): array
    {
        $results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'optimizations' => [],
            'errors' => [],
            'summary' => []
        ];
        
        $optimizations = $options['optimizations'] ?? ['cache', 'memory', 'disk', 'database'];
        
        foreach ($optimizations as $optimization) {
            try {
                $result = $this->runOptimization($optimization);
                $results['optimizations'][$optimization] = $result;
            } catch (\Exception $e) {
                $results['errors'][$optimization] = $e->getMessage();
                $this->logger->error("Optimization failed: {$optimization}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        $results['summary'] = $this->generateOptimizationSummary($results);
        
        return $results;
    }
    
    /**
     * 缓存优化
     */
    public function optimizeCache(): array
    {
        $results = [
            'cache_cleanup' => $this->cleanupCache(),
            'cache_warming' => $this->warmupCache(),
            'cache_analysis' => $this->analyzeCacheUsage()
        ];
        
        return $results;
    }
    
    /**
     * 内存优化
     */
    public function optimizeMemory(): array
    {
        $beforeMemory = memory_get_usage(true);
        $beforePeakMemory = memory_get_peak_usage(true);
        
        // 清理未使用的变量
        gc_collect_cycles();
        
        // 清理临时缓存
        $this->clearTemporaryData();
        
        $afterMemory = memory_get_usage(true);
        $afterPeakMemory = memory_get_peak_usage(true);
        
        return [
            'memory_freed' => $beforeMemory - $afterMemory,
            'peak_memory_freed' => $beforePeakMemory - $afterPeakMemory,
            'gc_cycles_collected' => gc_collect_cycles(),
            'before_memory' => $beforeMemory,
            'after_memory' => $afterMemory,
            'memory_limit' => ini_get('memory_limit')
        ];
    }
    
    /**
     * 磁盘空间优化
     */
    public function optimizeDisk(): array
    {
        $results = [
            'log_cleanup' => $this->cleanupLogs(),
            'temp_cleanup' => $this->cleanupTempFiles(),
            'cache_cleanup' => $this->cleanupCacheFiles()
        ];
        
        $results['total_freed'] = array_sum(array_column($results, 'freed_bytes'));
        
        return $results;
    }
    
    /**
     * 数据库优化
     */
    public function optimizeDatabase(): array
    {
        // 由于我们使用的是接口，这里提供基本的优化建议
        return [
            'status' => 'analyzed',
            'recommendations' => [
                'Consider adding database indexes for frequently queried columns',
                'Review and optimize slow queries',
                'Clean up old log entries and temporary data',
                'Update table statistics for better query planning'
            ],
            'suggested_actions' => [
                'Run ANALYZE TABLE on main tables',
                'Consider partitioning large tables',
                'Review connection pooling settings'
            ]
        ];
    }
    
    /**
     * 自动清理系统资源
     */
    public function autoCleanup(): array
    {
        $results = [];
        
        // 清理过期缓存
        $results['cache_cleanup'] = $this->cleanupExpiredCache();
        
        // 清理临时文件
        $results['temp_cleanup'] = $this->cleanupTempFiles();
        
        // 清理老旧日志
        $results['log_cleanup'] = $this->cleanupOldLogs();
        
        // 内存清理
        $results['memory_cleanup'] = $this->optimizeMemory();
        
        return $results;
    }
    
    /**
     * 预热缓存
     */
    public function warmupCache(): array
    {
        $warmedUp = 0;
        $errors = 0;
        
        $criticalCacheKeys = [
            'system_config',
            'user_permissions',
            'application_settings',
            'performance_metrics'
        ];
        
        foreach ($criticalCacheKeys as $key) {
            try {
                if (!$this->cache->has($key)) {
                    // 这里应该根据实际业务逻辑预热缓存
                    $this->cache->set($key, $this->generateCacheData($key), 3600);
                    $warmedUp++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->logger->warning("Failed to warm up cache for key: {$key}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'warmed_up' => $warmedUp,
            'errors' => $errors,
            'total_keys' => count($criticalCacheKeys)
        ];
    }
    
    /**
     * 分析缓存使用情况
     */
    public function analyzeCacheUsage(): array
    {
        try {
            $stats = $this->advancedCache->getStats();
            
            return [
                'cache_stats' => $stats,
                'hit_rate' => $this->calculateHitRate($stats),
                'memory_usage' => $stats['memory_usage'] ?? 0,
                'total_items' => $stats['total_items'] ?? 0,
                'recommendations' => $this->generateCacheRecommendations($stats)
            ];
        } catch (\Exception $e) {
            $this->logger->error('Cache analysis failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Cache analysis failed'
            ];
        }
    }
    
    /**
     * 运行特定的优化
     */
    private function runOptimization(string $type): array
    {
        switch ($type) {
            case 'cache':
                return $this->optimizeCache();
            case 'memory':
                return $this->optimizeMemory();
            case 'disk':
                return $this->optimizeDisk();
            case 'database':
                return $this->optimizeDatabase();
            default:
                throw new \InvalidArgumentException("Unknown optimization type: {$type}");
        }
    }
    
    /**
     * 清理缓存
     */
    private function cleanupCache(): array
    {
        $before = $this->getCacheSize();
        
        try {
            $this->cache->clear();
            $this->advancedCache->clear();
            
            $after = $this->getCacheSize();
            
            return [
                'status' => 'success',
                'before_size' => $before,
                'after_size' => $after,
                'freed_bytes' => $before - $after
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 清理过期缓存
     */
    private function cleanupExpiredCache(): array
    {
        try {
            $cleaned = $this->advancedCache->cleanup();
            
            return [
                'status' => 'success',
                'items_cleaned' => $cleaned,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 清理临时数据
     */
    private function clearTemporaryData(): void
    {
        // 清理PHP OPcache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }        // 清理用户缓存
        if (function_exists('apcu_clear')) {
            \apcu_clear();
        }
    }
    
    /**
     * 清理日志文件
     */
    private function cleanupLogs(): array
    {
        $logDir = __DIR__ . '/../../storage/logs';
        $freedBytes = 0;
        $cleanedFiles = 0;
        
        if (!is_dir($logDir)) {
            return [
                'status' => 'no_logs_dir',
                'freed_bytes' => 0,
                'cleaned_files' => 0
            ];
        }
        
        try {
            $files = glob($logDir . '/*.log');
            $cutoffTime = strtotime('-30 days'); // 保留30天内的日志
            
            foreach ($files as $file) {
                if (filemtime($file) < $cutoffTime) {
                    $size = filesize($file);
                    if (unlink($file)) {
                        $freedBytes += $size;
                        $cleanedFiles++;
                    }
                }
            }
            
            return [
                'status' => 'success',
                'freed_bytes' => $freedBytes,
                'cleaned_files' => $cleanedFiles
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'freed_bytes' => $freedBytes,
                'cleaned_files' => $cleanedFiles
            ];
        }
    }
    
    /**
     * 清理临时文件
     */
    private function cleanupTempFiles(): array
    {
        $tempDirs = [
            sys_get_temp_dir(),
            __DIR__ . '/../../storage/temp'
        ];
        
        $freedBytes = 0;
        $cleanedFiles = 0;
        
        foreach ($tempDirs as $dir) {
            if (!is_dir($dir)) continue;
            
            try {
                $files = glob($dir . '/alingai_*');
                $cutoffTime = strtotime('-1 day');
                
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < $cutoffTime) {
                        $size = filesize($file);
                        if (unlink($file)) {
                            $freedBytes += $size;
                            $cleanedFiles++;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->warning("Failed to clean temp directory: {$dir}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'status' => 'success',
            'freed_bytes' => $freedBytes,
            'cleaned_files' => $cleanedFiles
        ];
    }
    
    /**
     * 清理缓存文件
     */
    private function cleanupCacheFiles(): array
    {
        $cacheDir = __DIR__ . '/../../storage/cache';
        $freedBytes = 0;
        $cleanedFiles = 0;
        
        if (!is_dir($cacheDir)) {
            return [
                'status' => 'no_cache_dir',
                'freed_bytes' => 0,
                'cleaned_files' => 0
            ];
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir)
            );
            
            $cutoffTime = strtotime('-7 days');
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getMTime() < $cutoffTime) {
                    $size = $file->getSize();
                    if (unlink($file->getPathname())) {
                        $freedBytes += $size;
                        $cleanedFiles++;
                    }
                }
            }
            
            return [
                'status' => 'success',
                'freed_bytes' => $freedBytes,
                'cleaned_files' => $cleanedFiles
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'freed_bytes' => $freedBytes,
                'cleaned_files' => $cleanedFiles
            ];
        }
    }
    
    /**
     * 清理老旧日志
     */
    private function cleanupOldLogs(): array
    {
        return $this->cleanupLogs(); // 重用日志清理逻辑
    }
    
    /**
     * 获取缓存大小
     */
    private function getCacheSize(): int
    {
        try {
            $stats = $this->advancedCache->getStats();
            return $stats['memory_usage'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 生成缓存数据
     */
    private function generateCacheData(string $key): array
    {
        // 根据键生成相应的缓存数据
        switch ($key) {
            case 'system_config':
                return ['status' => 'active', 'version' => '1.0.0'];
            case 'user_permissions':
                return ['default_role' => 'user', 'max_sessions' => 5];
            case 'application_settings':
                return ['theme' => 'default', 'language' => 'zh-CN'];
            case 'performance_metrics':
                return ['last_check' => time(), 'status' => 'optimal'];
            default:
                return ['cached_at' => time()];
        }
    }
    
    /**
     * 计算缓存命中率
     */
    private function calculateHitRate(array $stats): float
    {
        $hits = $stats['hits'] ?? 0;
        $misses = $stats['misses'] ?? 0;
        $total = $hits + $misses;
        
        if ($total === 0) {
            return 0.0;
        }
        
        return round(($hits / $total) * 100, 2);
    }
    
    /**
     * 生成缓存建议
     */
    private function generateCacheRecommendations(array $stats): array
    {
        $recommendations = [];
        
        $hitRate = $this->calculateHitRate($stats);
        
        if ($hitRate < 80) {
            $recommendations[] = [
                'type' => 'hit_rate',
                'priority' => 'medium',
                'message' => 'Cache hit rate is below 80%. Consider reviewing cache strategies.'
            ];
        }
        
        $memoryUsage = $stats['memory_usage'] ?? 0;
        if ($memoryUsage > 100 * 1024 * 1024) { // 100MB
            $recommendations[] = [
                'type' => 'memory',
                'priority' => 'high',
                'message' => 'Cache memory usage is high. Consider implementing cache eviction policies.'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * 生成优化摘要
     */
    private function generateOptimizationSummary(array $results): array
    {
        $totalOptimizations = count($results['optimizations']);
        $successfulOptimizations = count(array_filter($results['optimizations'], function($result) {
            return !isset($result['status']) || $result['status'] !== 'error';
        }));
        
        $totalFreedBytes = 0;
        foreach ($results['optimizations'] as $optimization) {
            if (isset($optimization['freed_bytes'])) {
                $totalFreedBytes += $optimization['freed_bytes'];
            } elseif (isset($optimization['cache_cleanup']['freed_bytes'])) {
                $totalFreedBytes += $optimization['cache_cleanup']['freed_bytes'];
            }
        }
        
        return [
            'total_optimizations' => $totalOptimizations,
            'successful_optimizations' => $successfulOptimizations,
            'failed_optimizations' => count($results['errors']),
            'success_rate' => round(($successfulOptimizations / $totalOptimizations) * 100, 2),
            'total_freed_bytes' => $totalFreedBytes,
            'total_freed_mb' => round($totalFreedBytes / (1024 * 1024), 2)
        ];
    }
    
    /**
     * 初始化优化规则
     */
    private function initializeOptimizationRules(): void
    {
        $this->optimizationRules = [
            'cache' => [
                'enabled' => true,
                'frequency' => 'hourly',
                'priority' => 'high'
            ],
            'memory' => [
                'enabled' => true,
                'frequency' => 'every_30_minutes',
                'priority' => 'medium'
            ],
            'disk' => [
                'enabled' => true,
                'frequency' => 'daily',
                'priority' => 'low'
            ],
            'database' => [
                'enabled' => true,
                'frequency' => 'daily',
                'priority' => 'medium'
            ]
        ];
    }
}
