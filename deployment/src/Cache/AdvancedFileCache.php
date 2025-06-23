<?php

namespace AlingAi\Cache;

use Exception;

/**
 * 高级文件缓存系统
 * 支持多级缓存、自动过期、压缩存储
 */
class AdvancedFileCache
{
    private string $cacheDir;
    private int $defaultTtl;
    private bool $compression;
    private array $stats;
    
    public function __construct(string $cacheDir = null, int $defaultTtl = 3600, bool $compression = true)
    {
        $this->cacheDir = $cacheDir ?: sys_get_temp_dir() . '/alingai_cache';
        $this->defaultTtl = $defaultTtl;
        $this->compression = $compression;
        $this->stats = ['hits' => 0, 'misses' => 0, 'writes' => 0];
        
        $this->ensureCacheDir();
    }
    
    /**
     * 获取缓存项
     */
    public function get(string $key, $default = null)
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            $this->stats['misses']++;
            return $default;
        }
        
        $data = file_get_contents($file);
        if ($data === false) {
            $this->stats['misses']++;
            return $default;
        }
        
        if ($this->compression) {
            $data = gzuncompress($data);
        }
        
        $item = unserialize($data);
        
        if (!$this->isValid($item)) {
            $this->delete($key);
            $this->stats['misses']++;
            return $default;
        }
        
        $this->stats['hits']++;
        return $item['value'];
    }
    
    /**
     * 设置缓存项
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $expiry = time() + $ttl;
        
        $item = [
            'value' => $value,
            'expiry' => $expiry,
            'created' => time(),
            'key' => $key
        ];
        
        $data = serialize($item);
        
        if ($this->compression) {
            $data = gzcompress($data, 6);
        }
        
        $file = $this->getFilePath($key);
        $this->ensureDir(dirname($file));
        
        $result = file_put_contents($file, $data, LOCK_EX) !== false;
        
        if ($result) {
            $this->stats['writes']++;
        }
        
        return $result;
    }
    
    /**
     * 删除缓存项
     */
    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        return file_exists($file) ? unlink($file) : true;
    }
    
    /**
     * 清空所有缓存
     */
    public function clear(): bool
    {
        $this->deleteDirectory($this->cacheDir);
        $this->ensureCacheDir();
        return true;
    }
    
    /**
     * 检查缓存项是否存在且有效
     */
    public function has(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = file_get_contents($file);
        if ($data === false) {
            return false;
        }
        
        if ($this->compression) {
            $data = gzuncompress($data);
        }
        
        $item = unserialize($data);
        return $this->isValid($item);
    }
    
    /**
     * 获取多个缓存项
     */
    public function getMultiple(array $keys, $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }
    
    /**
     * 设置多个缓存项
     */
    public function setMultiple(array $values, int $ttl = null): bool
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
     * 删除多个缓存项
     */
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
     * 获取缓存统计信息
     */
    public function getStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? ($this->stats['hits'] / $total) * 100 : 0;
        
        return [
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'writes' => $this->stats['writes'],
            'hit_rate' => round($hitRate, 2),
            'cache_size' => $this->getCacheSize(),
            'cache_count' => $this->getCacheCount()
        ];
    }
    
    /**
     * 清理过期缓存
     */
    public function cleanup(): int
    {
        $cleaned = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $data = file_get_contents($file->getPathname());
                if ($data !== false) {
                    if ($this->compression) {
                        $data = gzuncompress($data);
                    }
                    
                    $item = unserialize($data);
                    if (!$this->isValid($item)) {
                        unlink($file->getPathname());
                        $cleaned++;
                    }
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * 获取缓存项的剩余TTL
     */
    public function getTtl(string $key): ?int
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = file_get_contents($file);
        if ($data === false) {
            return null;
        }
        
        if ($this->compression) {
            $data = gzuncompress($data);
        }
        
        $item = unserialize($data);
        
        if (!$this->isValid($item)) {
            return null;
        }
        
        $remainingTtl = $item['expires_at'] - time();
        return $remainingTtl > 0 ? $remainingTtl : null;
    }

    /**
     * 获取缓存文件路径
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        $subDir = substr($hash, 0, 2);
        return $this->cacheDir . '/' . $subDir . '/' . $hash . '.cache';
    }
    
    /**
     * 检查缓存项是否有效
     */
    private function isValid(array $item): bool
    {
        return isset($item['expiry']) && $item['expiry'] > time();
    }
    
    /**
     * 确保缓存目录存在
     */
    private function ensureCacheDir(): void
    {
        $this->ensureDir($this->cacheDir);
    }
    
    /**
     * 确保目录存在
     */
    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    /**
     * 递归删除目录
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * 获取缓存总大小
     */
    private function getCacheSize(): int
    {
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    /**
     * 获取缓存文件数量
     */
    private function getCacheCount(): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        
        return $count;
    }
}
