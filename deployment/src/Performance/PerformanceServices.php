<?php

namespace AlingAi\Performance;

use AlingAi\Services\EnhancedConfigService;
use Predis\Client as RedisClient;

/**
 * 缓存管理服务
 */
class CacheManager
{
    private static $instance = null;
    private $redisClient = null;
    private $fileCache = null;
    private $config;
    
    private function __construct()
    {
        $this->config = EnhancedConfigService::getInstance();
        $this->initializeRedis();
        $this->initializeFileCache();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeRedis(): void
    {
        try {
            $redisConfig = [
                'scheme' => 'tcp',
                'host' => $this->config->get('REDIS_HOST', '127.0.0.1'),
                'port' => $this->config->getInt('REDIS_PORT', 6379),
                'database' => $this->config->getInt('REDIS_DATABASE', 0),
            ];
            
            if ($password = $this->config->get('REDIS_PASSWORD')) {
                $redisConfig['password'] = $password;
            }
            
            $this->redisClient = new RedisClient($redisConfig);
            $this->redisClient->ping();
        } catch (\Exception $e) {
            error_log("Redis连接失败: " . $e->getMessage());
            $this->redisClient = null;
        }
    }
    
    private function initializeFileCache(): void
    {
        $cacheDir = $this->config->get('CACHE_PATH', '/var/cache/alingai');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $this->fileCache = $cacheDir;
    }
    
    /**
     * 获取缓存
     */
    public function get(string $key, $default = null)
    {
        // 首先尝试Redis
        if ($this->redisClient) {
            try {
                $value = $this->redisClient->get($key);
                if ($value !== null) {
                    return json_decode($value, true);
                }
            } catch (\Exception $e) {
                error_log("Redis读取失败: " . $e->getMessage());
            }
        }
        
        // 回退到文件缓存
        return $this->getFromFile($key, $default);
    }
    
    /**
     * 设置缓存
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $jsonValue = json_encode($value);
        
        // 写入Redis
        if ($this->redisClient) {
            try {
                $this->redisClient->setex($key, $ttl, $jsonValue);
            } catch (\Exception $e) {
                error_log("Redis写入失败: " . $e->getMessage());
            }
        }
        
        // 写入文件缓存
        return $this->setToFile($key, $value, $ttl);
    }
    
    /**
     * 删除缓存
     */
    public function delete(string $key): bool
    {
        $success = true;
        
        // 从Redis删除
        if ($this->redisClient) {
            try {
                $this->redisClient->del($key);
            } catch (\Exception $e) {
                error_log("Redis删除失败: " . $e->getMessage());
                $success = false;
            }
        }
        
        // 从文件缓存删除
        return $this->deleteFromFile($key) && $success;
    }
    
    /**
     * 清空所有缓存
     */
    public function flush(): bool
    {
        $success = true;
        
        // 清空Redis
        if ($this->redisClient) {
            try {
                $this->redisClient->flushdb();
            } catch (\Exception $e) {
                error_log("Redis清空失败: " . $e->getMessage());
                $success = false;
            }
        }
        
        // 清空文件缓存
        return $this->flushFileCache() && $success;
    }
    
    /**
     * 获取或设置缓存（回调模式）
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * 文件缓存操作
     */
    private function getFromFile(string $key, $default = null)
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $content = file_get_contents($filename);
        $data = json_decode($content, true);
        
        if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
            unlink($filename);
            return $default;
        }
        
        return $data['value'];
    }
    
    private function setToFile(string $key, $value, int $ttl): bool
    {
        $filename = $this->getCacheFilename($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($filename, json_encode($data)) !== false;
    }
    
    private function deleteFromFile(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        return !file_exists($filename) || unlink($filename);
    }
    
    private function flushFileCache(): bool
    {
        $files = glob($this->fileCache . '/cache_*');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    private function getCacheFilename(string $key): string
    {
        return $this->fileCache . '/cache_' . md5($key) . '.json';
    }
}

/**
 * 性能监控服务
 */
class PerformanceMonitor
{
    private static $startTime;
    private static $queries = [];
    private static $memoryUsage = [];
    
    public static function start(): void
    {
        self::$startTime = microtime(true);
        self::$memoryUsage['start'] = memory_get_usage(true);
    }
    
    public static function addQuery(string $sql, float $time): void
    {
        self::$queries[] = [
            'sql' => $sql,
            'time' => $time,
            'memory' => memory_get_usage(true)
        ];
    }
    
    public static function getStats(): array
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        return [
            'execution_time' => round(($endTime - self::$startTime) * 1000, 2),
            'memory_usage' => [
                'start' => self::formatBytes(self::$memoryUsage['start']),
                'end' => self::formatBytes($endMemory),
                'peak' => self::formatBytes(memory_get_peak_usage(true))
            ],
            'queries' => [
                'count' => count(self::$queries),
                'total_time' => round(array_sum(array_column(self::$queries, 'time')) * 1000, 2),
                'details' => self::$queries
            ]
        ];
    }
    
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

/**
 * 图片优化服务
 */
class ImageOptimizer
{
    private $config;
    
    public function __construct()
    {
        $this->config = EnhancedConfigService::getInstance();
    }
    
    /**
     * 优化图片
     */
    public function optimize(string $imagePath, array $options = []): bool
    {
        if (!file_exists($imagePath)) {
            return false;
        }
        
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // 默认选项
        $options = array_merge([
            'max_width' => 1920,
            'max_height' => 1080,
            'quality' => 85,
            'format' => null
        ], $options);
        
        // 检查是否需要调整大小
        if ($width <= $options['max_width'] && $height <= $options['max_height']) {
            return true; // 不需要优化
        }
        
        // 计算新尺寸
        $ratio = min($options['max_width'] / $width, $options['max_height'] / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        // 创建源图像
        $sourceImage = $this->createImageFromFile($imagePath, $mimeType);
        if (!$sourceImage) {
            return false;
        }
        
        // 创建目标图像
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // 保持透明度
        if ($mimeType === 'image/png') {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // 调整大小
        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // 保存图像
        $success = $this->saveImage($targetImage, $imagePath, $mimeType, $options['quality']);
        
        // 清理内存
        imagedestroy($sourceImage);
        imagedestroy($targetImage);
        
        return $success;
    }
    
    /**
     * 生成缩略图
     */
    public function generateThumbnail(string $imagePath, string $thumbnailPath, int $width = 150, int $height = 150): bool
    {
        if (!file_exists($imagePath)) {
            return false;
        }
        
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        $sourceImage = $this->createImageFromFile($imagePath, $imageInfo['mime']);
        if (!$sourceImage) {
            return false;
        }
        
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        
        // 计算裁剪区域（居中裁剪）
        $ratio = max($width / $sourceWidth, $height / $sourceHeight);
        $cropWidth = (int)($width / $ratio);
        $cropHeight = (int)($height / $ratio);
        $cropX = (int)(($sourceWidth - $cropWidth) / 2);
        $cropY = (int)(($sourceHeight - $cropHeight) / 2);
        
        // 创建缩略图
        $thumbnail = imagecreatetruecolor($width, $height);
        
        if ($imageInfo['mime'] === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $width, $height, $transparent);
        }
        
        imagecopyresampled($thumbnail, $sourceImage, 0, 0, $cropX, $cropY, $width, $height, $cropWidth, $cropHeight);
        
        // 保存缩略图
        $success = $this->saveImage($thumbnail, $thumbnailPath, $imageInfo['mime'], 85);
        
        // 清理内存
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        
        return $success;
    }
    
    private function createImageFromFile(string $path, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                return imagecreatefromwebp($path);
            default:
                return false;
        }
    }
    
    private function saveImage($image, string $path, string $mimeType, int $quality): bool
    {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagejpeg($image, $path, $quality);
            case 'image/png':
                return imagepng($image, $path, (int)(9 - ($quality / 10)));
            case 'image/gif':
                return imagegif($image, $path);
            case 'image/webp':
                return imagewebp($image, $path, $quality);
            default:
                return false;
        }
    }
}
