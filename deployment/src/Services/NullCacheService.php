<?php
/**
 * 空缓存服务实现
 * 当缓存连接失败时使用，提供兼容的接口但不执行实际操作
 * 
 * @package AlingAi\Services
 */

declare(strict_types=1);

namespace AlingAi\Services;

use Monolog\Logger;

class NullCacheService
{
    private Logger $logger;
    
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function get(string $key, $default = null)
    {
        $this->logger->debug('NullCacheService: get operation skipped', ['key' => $key]);
        return $default;
    }
    
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $this->logger->debug('NullCacheService: set operation skipped', ['key' => $key]);
        return false;
    }
    
    public function delete(string $key): bool
    {
        $this->logger->debug('NullCacheService: delete operation skipped', ['key' => $key]);
        return false;
    }
    
    public function clear(): bool
    {
        $this->logger->debug('NullCacheService: clear operation skipped');
        return false;
    }
    
    public function has(string $key): bool
    {
        $this->logger->debug('NullCacheService: has operation skipped', ['key' => $key]);
        return false;
    }
    
    public function getMultiple(array $keys, $default = null): array
    {
        $this->logger->debug('NullCacheService: getMultiple operation skipped');
        return array_fill_keys($keys, $default);
    }
    
    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        $this->logger->debug('NullCacheService: setMultiple operation skipped');
        return false;
    }
    
    public function deleteMultiple(array $keys): bool
    {
        $this->logger->debug('NullCacheService: deleteMultiple operation skipped');
        return false;
    }
    
    public function increment(string $key, int $value = 1): int
    {
        $this->logger->debug('NullCacheService: increment operation skipped', ['key' => $key]);
        return 0;
    }
    
    public function decrement(string $key, int $value = 1): int
    {
        $this->logger->debug('NullCacheService: decrement operation skipped', ['key' => $key]);
        return 0;
    }
}
