<?php

declare(strict_types=1);

namespace AlingAi\Pro\Cache;

use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;
use Redis;
use AlingAi\Pro\Core\StructuredLogger;

/**
 * 多层缓存管理器
 * 
 * 实现L1(本地)+L2(Redis)多层缓存架构
 */
class CacheManager implements CacheInterface
{
    private array $localCache = [];
    private int $localCacheMaxSize;
    private Redis $redis;
    private LoggerInterface $logger;
    private array $config;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'l1_hits' => 0,
        'l2_hits' => 0,
        'sets' => 0,
        'deletes' => 0
    ];

    public function __construct(
        Redis $redis,
        LoggerInterface $logger,
        array $config = []
    ) {
        $this->redis = $redis;
        $this->logger = $logger;
        $this->config = array_merge([
            'default_ttl' => 3600,
            'local_cache_max_size' => 1000,
            'key_prefix' => 'alingai:',
            'compression' => true,
            'serialization' => 'php'
        ], $config);
        
        $this->localCacheMaxSize = $this->config['local_cache_max_size'];
    }

    /**
     * 获取缓存值
     */
    public function get(string $key, $default = null)
    {
        $startTime = microtime(true);
        $fullKey = $this->buildKey($key);
        
        // L1缓存检查
        if (isset($this->localCache[$key])) {
            $item = $this->localCache[$key];
            if ($item['expires'] === null || $item['expires'] > time()) {
                $this->stats['hits']++;
                $this->stats['l1_hits']++;
                $this->logCacheOperation('get', $key, true, microtime(true) - $startTime);
                return $item['value'];
            } else {
                unset($this->localCache[$key]);
            }
        }

        // L2缓存检查 (Redis)
        try {
            $data = $this->redis->get($fullKey);
            if ($data !== false) {
                $value = $this->unserialize($data);
                
                // 回填L1缓存
                $this->setLocalCache($key, $value, null);
                
                $this->stats['hits']++;
                $this->stats['l2_hits']++;
                $this->logCacheOperation('get', $key, true, microtime(true) - $startTime);
                return $value;
            }
        } catch (\Exception $e) {
            $this->logger->warning('Redis缓存获取失败', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }

        $this->stats['misses']++;
        $this->logCacheOperation('get', $key, false, microtime(true) - $startTime);
        return $default;
    }

    /**
     * 设置缓存值
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        $startTime = microtime(true);
        $ttl = $ttl ?? $this->config['default_ttl'];
        $fullKey = $this->buildKey($key);
        
        // 设置L1缓存
        $expires = $ttl ? time() + $ttl : null;
        $this->setLocalCache($key, $value, $expires);
        
        // 设置L2缓存 (Redis)
        try {
            $data = $this->serialize($value);
            $result = $ttl > 0 
                ? $this->redis->setex($fullKey, $ttl, $data)
                : $this->redis->set($fullKey, $data);
                
            if ($result) {
                $this->stats['sets']++;
                $this->logCacheOperation('set', $key, null, microtime(true) - $startTime);
                return true;
            }
        } catch (\Exception $e) {
            $this->logger->error('Redis缓存设置失败', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * 删除缓存
     */
    public function delete(string $key): bool
    {
        $startTime = microtime(true);
        $fullKey = $this->buildKey($key);
        
        // 删除L1缓存
        unset($this->localCache[$key]);
        
        // 删除L2缓存
        try {
            $result = $this->redis->del($fullKey) > 0;
            if ($result) {
                $this->stats['deletes']++;
            }
            $this->logCacheOperation('delete', $key, null, microtime(true) - $startTime);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Redis缓存删除失败', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 清空缓存
     */
    public function clear(): bool
    {
        $this->localCache = [];
        
        try {
            $pattern = $this->config['key_prefix'] . '*';
            $keys = $this->redis->keys($pattern);
            if (!empty($keys)) {
                $this->redis->del($keys);
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Redis缓存清空失败', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 获取多个缓存值
     */
    public function getMultiple(iterable $keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * 设置多个缓存值
     */
    public function setMultiple(iterable $values, $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * 删除多个缓存
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * 检查缓存是否存在
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * 获取或设置缓存 (缓存穿透保护)
     */
    public function remember(string $key, callable $callback, $ttl = null)
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * 原子递增
     */
    public function increment(string $key, int $value = 1): int
    {
        $fullKey = $this->buildKey($key);
        
        try {
            $result = $this->redis->incrBy($fullKey, $value);
            
            // 更新L1缓存
            $this->setLocalCache($key, $result, null);
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Redis递增操作失败', [
                'key' => $key,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 原子递减
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }

    /**
     * 带标签的缓存
     */
    public function tags(array $tags): TaggedCache
    {
        return new TaggedCache($this, $tags, $this->logger);
    }

    /**
     * 构建完整的缓存键
     */
    private function buildKey(string $key): string
    {
        return $this->config['key_prefix'] . $key;
    }

    /**
     * 设置本地缓存
     */
    private function setLocalCache(string $key, $value, ?int $expires): void
    {
        // 如果本地缓存超过最大大小，移除最旧的项
        if (count($this->localCache) >= $this->localCacheMaxSize) {
            $oldestKey = array_key_first($this->localCache);
            unset($this->localCache[$oldestKey]);
        }

        $this->localCache[$key] = [
            'value' => $value,
            'expires' => $expires
        ];
    }

    /**
     * 序列化数据
     */
    private function serialize($value): string
    {
        if ($this->config['serialization'] === 'json') {
            $data = json_encode($value);
        } else {
            $data = serialize($value);
        }

        if ($this->config['compression']) {
            $data = gzcompress($data);
        }

        return $data;
    }

    /**
     * 反序列化数据
     */
    private function unserialize(string $data)
    {
        if ($this->config['compression']) {
            $data = gzuncompress($data);
        }

        if ($this->config['serialization'] === 'json') {
            return json_decode($data, true);
        } else {
            return unserialize($data);
        }
    }

    /**
     * 记录缓存操作日志
     */
    private function logCacheOperation(string $operation, string $key, ?bool $hit, float $executionTime): void
    {
        if ($this->logger instanceof StructuredLogger) {
            $this->logger->logCacheOperation($operation, $key, $hit, $executionTime);
        }
    }

    /**
     * 获取缓存统计信息
     */
    public function getStats(): array
    {
        $hitRate = $this->stats['hits'] + $this->stats['misses'] > 0 
            ? round($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses']) * 100, 2)
            : 0;

        return array_merge($this->stats, [
            'hit_rate' => $hitRate,
            'local_cache_size' => count($this->localCache),
            'local_cache_max_size' => $this->localCacheMaxSize
        ]);
    }

    /**
     * 重置统计信息
     */
    public function resetStats(): void
    {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'l1_hits' => 0,
            'l2_hits' => 0,
            'sets' => 0,
            'deletes' => 0
        ];
    }
}

/**
 * 带标签的缓存
 */
class TaggedCache
{
    private CacheManager $cache;
    private array $tags;
    private LoggerInterface $logger;

    public function __construct(CacheManager $cache, array $tags, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->tags = $tags;
        $this->logger = $logger;
    }

    /**
     * 设置带标签的缓存
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        // 为每个标签记录键
        foreach ($this->tags as $tag) {
            $tagKey = "tag:{$tag}";
            $taggedKeys = $this->cache->get($tagKey, []);
            if (!in_array($key, $taggedKeys)) {
                $taggedKeys[] = $key;
                $this->cache->set($tagKey, $taggedKeys, 86400 * 7); // 标签索引保存7天
            }
        }

        return $this->cache->set($key, $value, $ttl);
    }

    /**
     * 获取带标签的缓存
     */
    public function get(string $key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    /**
     * 根据标签清空缓存
     */
    public function flush(): bool
    {
        $keysToDelete = [];
        
        foreach ($this->tags as $tag) {
            $tagKey = "tag:{$tag}";
            $taggedKeys = $this->cache->get($tagKey, []);
            $keysToDelete = array_merge($keysToDelete, $taggedKeys);
            
            // 删除标签索引
            $this->cache->delete($tagKey);
        }

        // 删除所有相关的键
        if (!empty($keysToDelete)) {
            $this->cache->deleteMultiple(array_unique($keysToDelete));
        }

        $this->logger->info('根据标签清空缓存', [
            'tags' => $this->tags,
            'deleted_keys_count' => count($keysToDelete)
        ]);

        return true;
    }
}
