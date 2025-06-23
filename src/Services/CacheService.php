<?php
/**
 * 缓存服务类
 * 
 * @package AlingAi\Services
 */

declare(strict_types=1);

namespace AlingAi\Services;

use Predis\Client as RedisClient;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CacheService
{
    private ?RedisClient $redis = null;
    private LoggerInterface $logger;
    private string $driver;
    private string $fileStoragePath;    private string $prefix;
    private array $tags = [];
    
    public function __construct(?LoggerInterface $logger = null)
    {
        // 创建Logger（如果没有提供）
        if (!$logger) {
            $logger = new Logger('cache');
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::WARNING));
        }
        $this->logger = $logger;
        $this->driver = getenv('CACHE_DRIVER') ?: 'redis';
        $this->fileStoragePath = dirname(__DIR__, 2) . '/storage/framework/cache';
        $this->prefix = getenv('CACHE_PREFIX') ?: 'alingai_pro:';
        
        $this->initializeCache();
    }
    
    private function initializeCache(): void
    {
        if ($this->driver === 'redis') {
            $this->initializeRedis();
        } else {
            $this->initializeFileCache();
        }
    }
    
    private function initializeRedis(): void
    {
        try {
            $this->redis = new RedisClient([
                'scheme' => 'tcp',
                'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
                'port' => (int) (getenv('REDIS_PORT') ?: 6379),
                'password' => getenv('REDIS_PASSWORD') ?: null,
                'database' => (int) (getenv('REDIS_DB') ?: 0),
                'prefix' => $this->prefix,
                'read_write_timeout' => 0,
                'persistent' => true,
            ]);
            
            // 测试连接
            $this->redis->ping();
            $this->logger->info('Redis cache initialized successfully');
            
        } catch (\Exception $e) {
            $this->logger->warning('Redis connection failed, falling back to file cache', [
                'error' => $e->getMessage()
            ]);
            $this->driver = 'file';
            $this->redis = null;
            $this->initializeFileCache();
        }
    }
    
    private function initializeFileCache(): void
    {
        if (!is_dir($this->fileStoragePath)) {
            mkdir($this->fileStoragePath, 0755, true);
        }
        $this->logger->info('File cache initialized successfully');
    }
    
    public function get(string $key, $default = null)
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                $value = $this->redis->get($key);
                if ($value === null) {
                    return $default;
                }
                
                $data = json_decode($value, true);
                if ($data === null) {
                    return $default;
                }
                
                // 检查是否过期
                if (isset($data['expires_at']) && time() > $data['expires_at']) {
                    $this->delete($key);
                    return $default;
                }
                
                return $data['value'];
            } else {
                return $this->getFromFile($key, $default);
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache get failed', ['key' => $key, 'error' => $e->getMessage()]);
            return $default;
        }
    }
    
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            $data = [
                'value' => $value,
                'created_at' => time(),
                'expires_at' => $ttl > 0 ? time() + $ttl : null
            ];
            
            if ($this->driver === 'redis' && $this->redis) {
                if ($ttl > 0) {
                    return $this->redis->setex($key, $ttl, json_encode($data)) === 'OK';
                } else {
                    return $this->redis->set($key, json_encode($data)) === 'OK';
                }
            } else {
                return $this->setToFile($key, $data);
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache set failed', ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function has(string $key): bool
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                return $this->redis->exists($key) > 0;
            } else {
                return $this->hasInFile($key);
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache has check failed', ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function delete(string $key): bool
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                return $this->redis->del($key) > 0;
            } else {
                return $this->deleteFromFile($key);
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache delete failed', ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function clear(): bool
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                $keys = $this->redis->keys($this->prefix . '*');
                if (!empty($keys)) {
                    return $this->redis->del($keys) > 0;
                }
                return true;
            } else {
                return $this->clearFileCache();
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache clear failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function increment(string $key, int $value = 1): int
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                return $this->redis->incrby($key, $value);
            } else {
                $current = (int) $this->get($key, 0);
                $new = $current + $value;
                $this->set($key, $new);
                return $new;
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache increment failed', ['key' => $key, 'error' => $e->getMessage()]);
            return 0;
        }
    }
    
    public function decrement(string $key, int $value = 1): int
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                return $this->redis->decrby($key, $value);
            } else {
                $current = (int) $this->get($key, 0);
                $new = $current - $value;
                $this->set($key, $new);
                return $new;
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache decrement failed', ['key' => $key, 'error' => $e->getMessage()]);
            return 0;
        }
    }
    
    public function remember(string $key, callable $callback, int $ttl = 3600)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    public function tags(array $tags): self
    {
        // 简单的标签实现，为每个标签创建一个键列表
        $taggedCache = clone $this;
        $taggedCache->tags = $tags;
        return $taggedCache;
    }
    
    public function flush(): bool
    {
        if (isset($this->tags)) {
            // 删除标签相关的缓存
            foreach ($this->tags as $tag) {
                $keys = $this->get("tag:{$tag}:keys", []);
                foreach ($keys as $key) {
                    $this->delete($key);
                }
                $this->delete("tag:{$tag}:keys");
            }
            return true;
        }
        
        return $this->clear();
    }
    
    public function getMultiple(array $keys, $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }
    
    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
      public function deleteMultiple(array $keys): bool
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
     * 获取键的TTL（剩余生存时间）
     */
    public function getTtl(string $key): ?int
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                $ttl = $this->redis->ttl($key);
                return $ttl > 0 ? $ttl : null;
            } else {
                $filename = $this->getFilename($key);
                if (!file_exists($filename)) {
                    return null;
                }
                
                $content = file_get_contents($filename);
                if ($content === false) {
                    return null;
                }
                
                $data = json_decode($content, true);
                if ($data === null || !isset($data['expires_at'])) {
                    return null;
                }
                
                $ttl = $data['expires_at'] - time();
                return $ttl > 0 ? $ttl : null;
            }
        } catch (\Exception $e) {
            $this->logger->error('Get TTL failed', ['key' => $key, 'error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * 获取匹配模式的所有键
     */
    public function getKeys(string $pattern = '*'): array
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                return $this->redis->keys($pattern);
            } else {
                $keys = [];
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($this->fileStoragePath, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'cache') {
                        // 从文件名推导键名（这是一个简化的实现）
                        $filename = $file->getBasename('.cache');
                        $keys[] = $filename;
                    }
                }
                
                return $keys;
            }
        } catch (\Exception $e) {
            $this->logger->error('Get keys failed', ['pattern' => $pattern, 'error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * 带TTL的increment方法
     */
    public function incrementWithTtl(string $key, int $value = 1, int $ttl = 3600): int
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                $result = $this->redis->incrby($key, $value);
                if ($ttl > 0) {
                    $this->redis->expire($key, $ttl);
                }
                return $result;
            } else {
                $current = (int) $this->get($key, 0);
                $new = $current + $value;
                $this->set($key, $new, $ttl);
                return $new;
            }
        } catch (\Exception $e) {
            $this->logger->error('Increment with TTL failed', ['key' => $key, 'error' => $e->getMessage()]);
            return 0;
        }
    }
    
    private function getFromFile(string $key, $default = null)
    {
        $filename = $this->getFilename($key);
        if (!file_exists($filename)) {
            return $default;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            return $default;
        }
        
        $data = json_decode($content, true);
        if ($data === null) {
            return $default;
        }
        
        // 检查是否过期
        if (isset($data['expires_at']) && time() > $data['expires_at']) {
            unlink($filename);
            return $default;
        }
        
        return $data['value'];
    }
    
    private function setToFile(string $key, array $data): bool
    {
        $filename = $this->getFilename($key);
        $dir = dirname($filename);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return file_put_contents($filename, json_encode($data), LOCK_EX) !== false;
    }
    
    private function hasInFile(string $key): bool
    {
        $filename = $this->getFilename($key);
        if (!file_exists($filename)) {
            return false;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            return false;
        }
        
        $data = json_decode($content, true);
        if ($data === null) {
            return false;
        }
        
        // 检查是否过期
        if (isset($data['expires_at']) && time() > $data['expires_at']) {
            unlink($filename);
            return false;
        }
        
        return true;
    }
    
    private function deleteFromFile(string $key): bool
    {
        $filename = $this->getFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }
    
    private function clearFileCache(): bool
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->fileStoragePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                unlink($file->getPathname());
            } elseif ($file->isDir()) {
                rmdir($file->getPathname());
            }
        }
        
        return true;
    }
    
    private function getFilename(string $key): string
    {
        $hash = hash('sha256', $this->prefix . $key);
        $dir = substr($hash, 0, 2);
        return $this->fileStoragePath . '/' . $dir . '/' . $hash . '.cache';
    }
    
    public function getStats(): array
    {
        $stats = [
            'driver' => $this->driver,
            'prefix' => $this->prefix,
        ];
        
        if ($this->driver === 'redis' && $this->redis) {
            try {
                $info = $this->redis->info();
                $stats['redis'] = [
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'used_memory' => $info['used_memory_human'] ?? '0B',
                    'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                    'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                ];
            } catch (\Exception $e) {
                $stats['redis'] = ['error' => $e->getMessage()];
            }
        } else {
            $stats['file_cache'] = [
                'storage_path' => $this->fileStoragePath,
                'total_files' => $this->countCacheFiles(),
                'total_size' => $this->getCacheSize(),
            ];
        }
        
        return $stats;
    }
    
    private function countCacheFiles(): int
    {
        if (!is_dir($this->fileStoragePath)) {
            return 0;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->fileStoragePath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        $count = 0;
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $count++;
            }
        }
        
        return $count;
    }
    
    private function getCacheSize(): string
    {
        if (!is_dir($this->fileStoragePath)) {
            return '0B';
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->fileStoragePath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        $size = 0;
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $size += $file->getSize();
            }
        }
        
        return $this->formatBytes($size);
    }
    
    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($size, 1024));
        return round($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }
    
    /**
     * 根据标签删除缓存
     */
    public function deleteByTag(string $tag): bool
    {
        try {
            if ($this->driver === 'redis' && $this->redis) {
                // Redis实现：使用模式匹配删除
                $pattern = $this->prefix . $tag . ':*';
                $keys = $this->redis->keys($pattern);
                
                if (!empty($keys)) {
                    $this->redis->del($keys);
                }
                
                $this->logger->info("缓存标签删除成功", ['tag' => $tag, 'keys_count' => count($keys)]);
                return true;
            } else {
                // 文件缓存实现：删除包含标签的文件
                if (!is_dir($this->fileStoragePath)) {
                    return true;
                }
                
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($this->fileStoragePath, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                $deleted = 0;
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'cache') {
                        $filename = $file->getBasename('.cache');
                        if (strpos($filename, $tag . '_') === 0) {
                            unlink($file->getPathname());
                            $deleted++;
                        }
                    }
                }
                
                $this->logger->info("文件缓存标签删除成功", ['tag' => $tag, 'files_deleted' => $deleted]);
                return true;
            }
        } catch (\Exception $e) {
            $this->logger->error("删除缓存标签失败", [
                'tag' => $tag,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
