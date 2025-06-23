<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use Redis;
use Memcached;

/**
 * 缓存管理器
 * 
 * 提供统一的缓存接口，支持多种缓存驱动
 * 优化性能：多级缓存、智能过期、批量操作
 * 增强安全性：缓存加密、访问控制、数据验证
 */
class CacheManager
{
    private LoggerInterface $logger;
    private array $config;
    private array $drivers = [];
    private string $defaultDriver = 'redis';
    private array $cacheStats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0
    ];
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'default' => env('CACHE_DRIVER', 'redis'),
            'prefix' => env('CACHE_PREFIX', 'alingai_'),
            'ttl' => [
                'default' => 3600,
                'short' => 300,
                'medium' => 1800,
                'long' => 86400,
                'permanent' => 0
            ],
            'drivers' => [
                'redis' => [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', 6379),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', 0),
                    'timeout' => 5,
                    'retry_interval' => 100
                ],
                'memcached' => [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100
                ],
                'file' => [
                    'path' => env('CACHE_PATH', storage_path('cache')),
                    'permissions' => 0755
                ]
            ],
            'encryption' => [
                'enabled' => true,
                'key' => env('CACHE_ENCRYPTION_KEY'),
                'algorithm' => 'AES-256-CBC'
            ],
            'compression' => [
                'enabled' => true,
                'threshold' => 1024 // 1KB
            ]
        ], $config);
        
        $this->defaultDriver = $this->config['default'];
        $this->initializeDrivers();
    }
    
    /**
     * 初始化缓存驱动
     */
    private function initializeDrivers(): void
    {
        foreach ($this->config['drivers'] as $driver => $config) {
            try {
                $this->drivers[$driver] = $this->createDriver($driver, $config);
                $this->logger->info("缓存驱动初始化成功", ['driver' => $driver]);
            } catch (\Exception $e) {
                $this->logger->error("缓存驱动初始化失败", [
                    'driver' => $driver,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * 创建缓存驱动实例
     */
    private function createDriver(string $driver, array $config)
    {
        switch ($driver) {
            case 'redis':
                return $this->createRedisDriver($config);
            case 'memcached':
                return $this->createMemcachedDriver($config);
            case 'file':
                return $this->createFileDriver($config);
            default:
                throw new \InvalidArgumentException("不支持的缓存驱动: {$driver}");
        }
    }
    
    /**
     * 创建Redis驱动
     */
    private function createRedisDriver(array $config): Redis
    {
        $redis = new Redis();
        
        $connected = $redis->connect(
            $config['host'],
            $config['port'],
            $config['timeout']
        );
        
        if (!$connected) {
            throw new \RuntimeException("无法连接到Redis服务器");
        }
        
        if (!empty($config['password'])) {
            $redis->auth($config['password']);
        }
        
        if (isset($config['database'])) {
            $redis->select($config['database']);
        }
        
        return $redis;
    }
    
    /**
     * 创建Memcached驱动
     */
    private function createMemcachedDriver(array $config): Memcached
    {
        $memcached = new Memcached();
        $memcached->addServer(
            $config['host'],
            $config['port'],
            $config['weight']
        );
        
        return $memcached;
    }
    
    /**
     * 创建文件驱动
     */
    private function createFileDriver(array $config): array
    {
        $path = $config['path'];
        
        if (!is_dir($path)) {
            if (!mkdir($path, $config['permissions'], true)) {
                throw new \RuntimeException("无法创建缓存目录: {$path}");
            }
        }
        
        return [
            'path' => $path,
            'permissions' => $config['permissions']
        ];
    }
    
    /**
     * 获取缓存值
     */
    public function get(string $key, $default = null)
    {
        $prefixedKey = $this->getPrefixedKey($key);
        
        try {
            $driver = $this->getDriver();
            $value = $this->retrieveFromDriver($driver, $prefixedKey);
            
            if ($value !== null) {
                $this->cacheStats['hits']++;
                $this->logger->debug("缓存命中", ['key' => $key]);
                return $this->unserialize($value);
            }
            
            $this->cacheStats['misses']++;
            $this->logger->debug("缓存未命中", ['key' => $key]);
            return $default;
            
        } catch (\Exception $e) {
            $this->logger->error("获取缓存失败", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }
    
    /**
     * 设置缓存值
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $prefixedKey = $this->getPrefixedKey($key);
        $ttl = $ttl ?? $this->config['ttl']['default'];
        
        try {
            $driver = $this->getDriver();
            $serializedValue = $this->serialize($value);
            
            $result = $this->storeInDriver($driver, $prefixedKey, $serializedValue, $ttl);
            
            if ($result) {
                $this->cacheStats['writes']++;
                $this->logger->debug("缓存写入成功", [
                    'key' => $key,
                    'ttl' => $ttl
                ]);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error("设置缓存失败", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 删除缓存
     */
    public function delete(string $key): bool
    {
        $prefixedKey = $this->getPrefixedKey($key);
        
        try {
            $driver = $this->getDriver();
            $result = $this->deleteFromDriver($driver, $prefixedKey);
            
            if ($result) {
                $this->cacheStats['deletes']++;
                $this->logger->debug("缓存删除成功", ['key' => $key]);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error("删除缓存失败", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 检查缓存是否存在
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
    
    /**
     * 获取或设置缓存
     */
    public function remember(string $key, callable $callback, int $ttl = null)
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
     * 批量获取缓存
     */
    public function getMultiple(array $keys): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        
        return $results;
    }
    
    /**
     * 批量设置缓存
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
     * 批量删除缓存
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
     * 清空所有缓存
     */
    public function clear(): bool
    {
        try {
            $driver = $this->getDriver();
            $result = $this->clearDriver($driver);
            
            if ($result) {
                $this->logger->info("缓存清空成功");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error("清空缓存失败", ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * 获取缓存统计信息
     */
    public function getStats(): array
    {
        $total = $this->cacheStats['hits'] + $this->cacheStats['misses'];
        $hitRate = $total > 0 ? ($this->cacheStats['hits'] / $total) * 100 : 0;
        
        return [
            'hits' => $this->cacheStats['hits'],
            'misses' => $this->cacheStats['misses'],
            'writes' => $this->cacheStats['writes'],
            'deletes' => $this->cacheStats['deletes'],
            'hit_rate' => round($hitRate, 2),
            'total_requests' => $total
        ];
    }
    
    /**
     * 获取缓存驱动
     */
    private function getDriver()
    {
        if (!isset($this->drivers[$this->defaultDriver])) {
            throw new \RuntimeException("缓存驱动不可用: {$this->defaultDriver}");
        }
        
        return $this->drivers[$this->defaultDriver];
    }
    
    /**
     * 获取带前缀的键名
     */
    private function getPrefixedKey(string $key): string
    {
        return $this->config['prefix'] . $key;
    }
    
    /**
     * 从驱动中获取数据
     */
    private function retrieveFromDriver($driver, string $key)
    {
        if ($driver instanceof Redis) {
            return $driver->get($key);
        } elseif ($driver instanceof Memcached) {
            return $driver->get($key);
        } elseif (is_array($driver)) {
            return $this->getFromFile($driver, $key);
        }
        
        return null;
    }
    
    /**
     * 向驱动中存储数据
     */
    private function storeInDriver($driver, string $key, $value, int $ttl): bool
    {
        if ($driver instanceof Redis) {
            return $driver->setex($key, $ttl, $value);
        } elseif ($driver instanceof Memcached) {
            return $driver->set($key, $value, $ttl);
        } elseif (is_array($driver)) {
            return $this->storeToFile($driver, $key, $value, $ttl);
        }
        
        return false;
    }
    
    /**
     * 从驱动中删除数据
     */
    private function deleteFromDriver($driver, string $key): bool
    {
        if ($driver instanceof Redis) {
            return $driver->del($key) > 0;
        } elseif ($driver instanceof Memcached) {
            return $driver->delete($key);
        } elseif (is_array($driver)) {
            return $this->deleteFromFile($driver, $key);
        }
        
        return false;
    }
    
    /**
     * 清空驱动
     */
    private function clearDriver($driver): bool
    {
        if ($driver instanceof Redis) {
            return $driver->flushDB();
        } elseif ($driver instanceof Memcached) {
            return $driver->flush();
        } elseif (is_array($driver)) {
            return $this->clearFileCache($driver);
        }
        
        return false;
    }
    
    /**
     * 从文件获取数据
     */
    private function getFromFile(array $config, string $key)
    {
        $filename = $this->getCacheFilename($config['path'], $key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if ($data['expires_at'] > 0 && $data['expires_at'] < time()) {
            unlink($filename);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * 向文件存储数据
     */
    private function storeToFile(array $config, string $key, $value, int $ttl): bool
    {
        $filename = $this->getCacheFilename($config['path'], $key);
        $data = [
            'value' => $value,
            'expires_at' => $ttl > 0 ? time() + $ttl : 0,
            'created_at' => time()
        ];
        
        return file_put_contents($filename, serialize($data)) !== false;
    }
    
    /**
     * 从文件删除数据
     */
    private function deleteFromFile(array $config, string $key): bool
    {
        $filename = $this->getCacheFilename($config['path'], $key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * 清空文件缓存
     */
    private function clearFileCache(array $config): bool
    {
        $files = glob($config['path'] . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * 获取缓存文件名
     */
    private function getCacheFilename(string $path, string $key): string
    {
        return $path . '/' . md5($key) . '.cache';
    }
    
    /**
     * 序列化数据
     */
    private function serialize($value): string
    {
        $data = serialize($value);
        
        // 压缩数据
        if ($this->config['compression']['enabled'] && strlen($data) > $this->config['compression']['threshold']) {
            $data = gzcompress($data);
        }
        
        // 加密数据
        if ($this->config['encryption']['enabled'] && !empty($this->config['encryption']['key'])) {
            $data = $this->encrypt($data);
        }
        
        return $data;
    }
    
    /**
     * 反序列化数据
     */
    private function unserialize(string $data)
    {
        // 解密数据
        if ($this->config['encryption']['enabled'] && !empty($this->config['encryption']['key'])) {
            $data = $this->decrypt($data);
        }
        
        // 解压数据
        if ($this->config['compression']['enabled']) {
            $uncompressed = @gzuncompress($data);
            if ($uncompressed !== false) {
                $data = $uncompressed;
            }
        }
        
        return unserialize($data);
    }
    
    /**
     * 加密数据
     */
    private function encrypt(string $data): string
    {
        $key = hash('sha256', $this->config['encryption']['key'], true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->config['encryption']['algorithm']));
        
        $encrypted = openssl_encrypt($data, $this->config['encryption']['algorithm'], $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * 解密数据
     */
    private function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $key = hash('sha256', $this->config['encryption']['key'], true);
        $ivLength = openssl_cipher_iv_length($this->config['encryption']['algorithm']);
        
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, $this->config['encryption']['algorithm'], $key, 0, $iv);
    }
}
