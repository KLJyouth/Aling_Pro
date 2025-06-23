<?php

namespace AlingAi\Cache;

use AlingAi\Services\DatabaseService;
use Exception;

/**
 * 应用缓存管理器
 * 提供智能缓存策略和多层缓存架构
 */
class ApplicationCacheManager
{
    private AdvancedFileCache $fileCache;
    private array $memoryCache;
    private DatabaseService $db;
    private array $config;
    private array $stats;
    
    public function __construct(DatabaseService $db, array $config = [])
    {
        $this->db = $db;
        $this->config = array_merge([
            'memory_limit' => 100, // 内存缓存项数限制
            'default_ttl' => 3600, // 默认TTL (1小时)
            'file_cache_dir' => sys_get_temp_dir() . '/alingai_cache',
            'compression_enabled' => true,
            'compression_level' => 6,
            'auto_cleanup' => true,
            'cleanup_interval' => 3600,
            'cleanup_probability' => 0.01 // 1% 概率自动清理
        ], $config);
        
        $this->fileCache = new AdvancedFileCache(
            $this->config['file_cache_dir'],
            $this->config['default_ttl'],
            $this->config['compression_enabled']
        );
        
        $this->memoryCache = [];
        $this->stats = [
            'memory_hits' => 0,
            'file_hits' => 0,
            'db_hits' => 0,
            'total_requests' => 0
        ];
        
        // 随机清理过期缓存
        if ($this->config['auto_cleanup'] && rand(1, 100) <= ($this->config['cleanup_probability'] * 100)) {
            $this->cleanup();
        }
    }
    
    /**
     * 获取缓存数据 - 三层缓存策略
     * 1. 内存缓存 (最快)
     * 2. 文件缓存 (中等)
     * 3. 数据库查询 (最慢，但会更新缓存)
     */
    public function get(string $key, callable $callback = null, int $ttl = null)
    {
        $this->stats['total_requests']++;
        
        // 第一层：内存缓存
        if (isset($this->memoryCache[$key])) {
            $item = $this->memoryCache[$key];
            if ($this->isMemoryItemValid($item)) {
                $this->stats['memory_hits']++;
                return $item['value'];
            } else {
                unset($this->memoryCache[$key]);
            }
        }
        
        // 第二层：文件缓存
        $value = $this->fileCache->get($key);
        if ($value !== null) {
            $this->stats['file_hits']++;
            $this->setMemoryCache($key, $value, $ttl);
            return $value;
        }
        
        // 第三层：数据库查询（如果提供了回调）
        if ($callback !== null) {
            $this->stats['db_hits']++;
            $value = $callback();
            if ($value !== null) {
                $this->set($key, $value, $ttl);
            }
            return $value;
        }
        
        return null;
    }
    
    /**
     * 设置缓存数据
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->config['default_ttl'];
        
        // 设置文件缓存
        $fileResult = $this->fileCache->set($key, $value, $ttl);
        
        // 设置内存缓存
        $this->setMemoryCache($key, $value, $ttl);
        
        return $fileResult;
    }
    
    /**
     * 删除缓存
     */
    public function delete(string $key): bool
    {
        // 删除内存缓存
        unset($this->memoryCache[$key]);
        
        // 删除文件缓存
        return $this->fileCache->delete($key);
    }
    
    /**
     * 清空所有缓存
     */
    public function clear(): bool
    {
        $this->memoryCache = [];
        return $this->fileCache->clear();
    }
    
    /**
     * 用户查询缓存
     */
    public function getUserData(int $userId, int $ttl = 1800): ?array
    {
        $key = "user_data_{$userId}";
        
        return $this->get($key, function() use ($userId) {
            try {
                if (method_exists($this->db, 'query')) {
                    $result = $this->db->query("SELECT * FROM users WHERE id = {$userId}");
                    return is_array($result) && !empty($result) ? $result[0] : null;
                } else {
                    // 文件存储模式的处理
                    $result = $this->db->find('users', $userId);
                    return $result;
                }
            } catch (Exception $e) {
                error_log("缓存用户数据查询失败: " . $e->getMessage());
                return null;
            }
        }, $ttl);
    }
    
    /**
     * AI模型缓存
     */
    public function getModelResponse(string $prompt, string $model, int $ttl = 3600): ?array
    {
        $key = "ai_response_" . md5($prompt . $model);
        
        return $this->get($key, null, $ttl);
    }
    
    /**
     * 设置AI模型响应缓存
     */
    public function setModelResponse(string $prompt, string $model, array $response, int $ttl = 3600): bool
    {
        $key = "ai_response_" . md5($prompt . $model);
        return $this->set($key, $response, $ttl);
    }
    
    /**
     * 会话缓存
     */
    public function getConversation(string $sessionId, int $ttl = 7200): ?array
    {
        $key = "conversation_{$sessionId}";
        
        return $this->get($key, function() use ($sessionId) {
            try {
                if (method_exists($this->db, 'query')) {
                    $result = $this->db->query("SELECT * FROM conversations WHERE session_id = '{$sessionId}' ORDER BY created_at ASC");
                    return is_array($result) ? $result : [];
                } else {
                    // 文件存储模式的处理
                    $result = $this->db->findAll('conversations', ['session_id' => $sessionId]);
                    return $result ?? [];
                }
            } catch (Exception $e) {
                error_log("缓存会话数据查询失败: " . $e->getMessage());
                return null;
            }
        }, $ttl);
    }
    
    /**
     * 系统配置缓存
     */
    public function getSystemConfig(int $ttl = 86400): ?array
    {
        $key = "system_config";
        
        return $this->get($key, function() {
            try {
                if (method_exists($this->db, 'query')) {
                    $result = $this->db->query("SELECT * FROM settings");
                    $settings = [];
                    if (is_array($result)) {
                        foreach ($result as $row) {
                            $settings[$row['key']] = $row['value'];
                        }
                    }
                    return $settings;
                } else {
                    // 文件存储模式的处理
                    $result = $this->db->findAll('settings');
                    $settings = [];
                    if (is_array($result)) {
                        foreach ($result as $row) {
                            $settings[$row['key']] = $row['value'];
                        }
                    }
                    return $settings;
                }
            } catch (Exception $e) {
                error_log("缓存系统配置查询失败: " . $e->getMessage());
                return null;
            }
        }, $ttl);
    }
    
    /**
     * 批量获取缓存
     */
    public function getMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }
    
    /**
     * 批量设置缓存
     */
    public function setMultiple(array $items, int $ttl = null): bool
    {
        $success = true;
        foreach ($items as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * 预热缓存
     */
    public function warmup(): array
    {
        $warmed = [];
        
        try {
            // 预热系统配置
            $config = $this->getSystemConfig();
            if ($config !== null) {
                $warmed[] = 'system_config';
            }
            
            // 预热热门用户数据
            if (method_exists($this->db, 'query')) {
                try {
                    $result = $this->db->query("SELECT id FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 7 DAY) LIMIT 50");
                    if (is_array($result)) {
                        foreach ($result as $row) {
                            $userData = $this->getUserData($row['id']);
                            if ($userData !== null) {
                                $warmed[] = "user_data_{$row['id']}";
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("预热用户数据失败: " . $e->getMessage());
                }
            }
            
        } catch (Exception $e) {
            error_log("缓存预热失败: " . $e->getMessage());
        }
        
        return $warmed;
    }
    
    /**
     * 清理过期缓存
     */
    public function cleanup(): int
    {
        // 清理内存缓存中的过期项
        $cleaned = 0;
        foreach ($this->memoryCache as $key => $item) {
            if (!$this->isMemoryItemValid($item)) {
                unset($this->memoryCache[$key]);
                $cleaned++;
            }
        }
        
        // 清理文件缓存中的过期项
        $cleaned += $this->fileCache->cleanup();
        
        return $cleaned;
    }
    
    /**
     * 获取缓存统计信息
     */
    public function getStats(): array
    {
        $fileStats = $this->fileCache->getStats();
        $memoryCount = count($this->memoryCache);
        $memorySize = strlen(serialize($this->memoryCache));
        
        $totalRequests = $this->stats['total_requests'];
        $hitRate = $totalRequests > 0 ? 
            (($this->stats['memory_hits'] + $this->stats['file_hits']) / $totalRequests) * 100 : 0;
        
        return [
            'requests' => $this->stats,
            'hit_rate' => round($hitRate, 2),
            'memory_cache' => [
                'count' => $memoryCount,
                'size' => $memorySize,
                'limit' => $this->config['memory_limit']
            ],
            'file_cache' => $fileStats,
            'performance' => [
                'memory_hit_ratio' => $totalRequests > 0 ? round(($this->stats['memory_hits'] / $totalRequests) * 100, 2) : 0,
                'file_hit_ratio' => $totalRequests > 0 ? round(($this->stats['file_hits'] / $totalRequests) * 100, 2) : 0,
                'db_hit_ratio' => $totalRequests > 0 ? round(($this->stats['db_hits'] / $totalRequests) * 100, 2) : 0
            ]
        ];
    }
    
    /**
     * 获取缓存文件信息（API兼容方法）
     */
    public function getCacheFileInfo(): array
    {
        try {
            $fileStats = $this->fileCache->getStats();
            $cacheDir = $this->config['file_cache_dir'] ?? '/tmp/cache';
            
            return [
                'cache_directory' => $cacheDir,
                'total_files' => $fileStats['total_files'] ?? 0,
                'total_size' => $fileStats['total_size'] ?? 0,
                'disk_usage' => [
                    'used_space' => $fileStats['total_size'] ?? 0,
                    'available_space' => disk_free_space($cacheDir),
                    'total_space' => disk_total_space($cacheDir)
                ],
                'file_details' => $fileStats['file_list'] ?? [],
                'last_cleanup' => $fileStats['last_cleanup'] ?? null,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * 检查是否启用压缩（API兼容方法）
     */
    public function isCompressionEnabled(): bool
    {
        return $this->config['compression_enabled'] ?? false;
    }

    /**
     * 获取缓存配置信息
     */
    public function getCacheConfig(): array
    {
        return [
            'memory_limit' => $this->config['memory_limit'],
            'default_ttl' => $this->config['default_ttl'],
            'file_cache_dir' => $this->config['file_cache_dir'],
            'compression_enabled' => $this->isCompressionEnabled(),
            'compression_level' => $this->config['compression_level'] ?? 6,
            'auto_cleanup_enabled' => $this->config['auto_cleanup'] ?? true,
            'cleanup_interval' => $this->config['cleanup_interval'] ?? 3600
        ];
    }

    /**
     * 强制刷新特定缓存
     */
    public function refresh(string $key, callable $callback = null, int $ttl = null): bool
    {
        // 先删除现有缓存
        $this->delete($key);
        
        // 如果提供了回调，重新获取数据
        if ($callback !== null) {
            $value = $callback();
            if ($value !== null) {
                return $this->set($key, $value, $ttl);
            }
        }
        
        return false;
    }

    /**
     * 缓存是否存在
     */
    public function has(string $key): bool
    {
        // 检查内存缓存
        if (isset($this->memoryCache[$key]) && $this->isMemoryItemValid($this->memoryCache[$key])) {
            return true;
        }
        
        // 检查文件缓存
        return $this->fileCache->has($key);
    }

    /**
     * 获取缓存键的TTL
     */
    public function getTtl(string $key): ?int
    {
        // 检查内存缓存
        if (isset($this->memoryCache[$key])) {
            $item = $this->memoryCache[$key];
            if ($this->isMemoryItemValid($item)) {
                return $item['expires'] - time();
            }
        }
        
        // 检查文件缓存
        return $this->fileCache->getTtl($key);
    }
    
    /**
     * 设置内存缓存
     */
    private function setMemoryCache(string $key, $value, int $ttl = null): void
    {
        $ttl = $ttl ?? $this->config['default_ttl'];
        
        // 检查内存缓存限制
        if (count($this->memoryCache) >= $this->config['memory_limit']) {
            // 删除最旧的项（FIFO策略）
            $oldestKey = array_key_first($this->memoryCache);
            unset($this->memoryCache[$oldestKey]);
        }
        
        $this->memoryCache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
    }
    
    /**
     * 检查内存缓存项是否有效
     */
    private function isMemoryItemValid(array $item): bool
    {
        return $item['expires'] > time();
    }
}
