<?php
declare(strict_types=1);

/**
 * 文件名：ComputerVisionAPI.php
 * 功能描述：计算机视觉API - 提供CV模块的统一接口
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Engines\CV
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\Engines\CV;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;

/**
 * 计算机视觉API
 *
 * 作为计算机视觉模块的统一接口，提供图像识别、人脸检测、OCR等功能
 */
class ComputerVisionAPI
{
    /**
     * @var LoggerInterface|null 日志记录器
     */
    private ?LoggerInterface $logger;

    /**
     * @var CacheManager|null 缓存管理器
     */
    private ?CacheManager $cache;

    /**
     * @var ImageRecognitionEngine 图像识别引擎
     */
    private ImageRecognitionEngine $imageEngine;

    /**
     * @var array 配置参数
     */
    private array $config;

    /**
     * @var array 支持的图像格式
     */
    private array $supportedFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    /**
     * 构造函数
     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志记录器
     * @param CacheManager|null $cache 缓存管理器
     * @throws Exception 初始化失败时抛出异常
     */
    public function __construct(array $config = [], ?LoggerInterface $logger = null, ?CacheManager $cache = null)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $this->mergeConfig($config);

        try {
            $this->initializeEngine();

            if ($this->logger) {
                $this->logger->info('计算机视觉API初始化成功', [
                    'config' => [
                        'api_version' => $this->config['api_version'],
                        'cache_enabled' => $this->config['cache_enabled']
                    ]
                ]);
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('计算机视觉API初始化失败', ['error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * 合并配置
     *
     * @param array $config 用户配置
     * @return array 合并后的配置
     */
    private function mergeConfig(array $config): array
    {
        // 默认配置
        $defaultConfig = [
            'api_version' => '1.0.0',
            'max_image_size' => 10 * 1024 * 1024, // 10MB
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'confidence_threshold' => 0.5,
            'max_detections' => 50,
            'use_gpu' => false,
            'batch_processing' => false
        ];

        return array_merge($defaultConfig, $config);
    }

    /**
     * 初始化图像识别引擎
     */
    private function initializeEngine(): void
    {
        // 创建性能监控器（假设已实现）
        $performanceMonitor = new \AlingAi\Utils\PerformanceMonitor();

        // 创建图像识别引擎
        $this->imageEngine = new ImageRecognitionEngine(
            $this->logger,
            $this->cache,
            $performanceMonitor,
            $this->config
        );
    }

    /**
     * 图像分析
     *
     * @param string $imagePath 图像路径
     * @param array $options 分析选项
     * @return array 分析结果
     * @throws InvalidArgumentException 参数无效时抛出异常
     * @throws RuntimeException 处理失败时抛出异常
     */
    public function analyzeImage(string $imagePath, array $options = []): array
    {
        $this->validateImage($imagePath);

        // 合并选项
        $mergedOptions = array_merge($this->config, $options);

        // 检查缓存
        if ($mergedOptions['cache_enabled']) {
            $cacheKey = 'cv_analyze_' . md5_file($imagePath) . '_' . md5(json_encode($mergedOptions));
            if ($this->cache && $this->cache->has($cacheKey)) {
                if ($this->logger) {
                    $this->logger->debug('从缓存获取图像分析结果', ['image_path' => $imagePath]);
                }
                return $this->cache->get($cacheKey);
            }
        }

        try {
            if ($this->logger) {
                $this->logger->info('开始分析图像', ['image_path' => $imagePath]);
            }

            // 调用图像识别引擎进行分析
            $analysisResult = $this->imageEngine->analyze($imagePath);

            // 缓存结果
            if ($mergedOptions['cache_enabled'] && $this->cache) {
                $this->cache->set($cacheKey, $analysisResult, $mergedOptions['cache_ttl']);
            }

            return $analysisResult;

        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('图像分析失败', [
                    'image_path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
            }
            throw new RuntimeException('图像分析失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 物体检测
     *
     * @param string $imagePath 图像路径
     * @param array $options 检测选项
     * @return array 检测结果
     */
    public function detectObjects(string $imagePath, array $options = []): array
    {
        $this->validateImage($imagePath);
        
        // 合并选项
        $mergedOptions = array_merge($this->config, $options);

        // 检查缓存
        if ($mergedOptions['cache_enabled']) {
            $cacheKey = 'cv_objects_' . md5_file($imagePath) . '_' . md5(json_encode($mergedOptions));
            if ($this->cache && $this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }

        try {
            // 调用物体检测
            $result = $this->imageEngine->detectObjects($imagePath);
            
            // 缓存结果
            if ($mergedOptions['cache_enabled'] && $this->cache) {
                $this->cache->set($cacheKey, $result, $mergedOptions['cache_ttl']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('物体检测失败', [
                    'image_path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
            }
            throw new RuntimeException('物体检测失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 人脸识别
     *
     * @param string $imagePath 图像路径
     * @param array $options 识别选项
     * @return array 识别结果
     */
    public function recognizeFaces(string $imagePath, array $options = []): array
    {
        $this->validateImage($imagePath);
        
        // 合并选项
        $mergedOptions = array_merge($this->config, $options);

        // 检查缓存
        if ($mergedOptions['cache_enabled']) {
            $cacheKey = 'cv_faces_' . md5_file($imagePath) . '_' . md5(json_encode($mergedOptions));
            if ($this->cache && $this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }

        try {
            // 调用人脸识别
            $result = $this->imageEngine->recognizeFaces($imagePath);
            
            // 缓存结果
            if ($mergedOptions['cache_enabled'] && $this->cache) {
                $this->cache->set($cacheKey, $result, $mergedOptions['cache_ttl']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('人脸识别失败', [
                    'image_path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
            }
            throw new RuntimeException('人脸识别失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 图像分类
     *
     * @param string $imagePath 图像路径
     * @param array $options 分类选项
     * @return array 分类结果
     */
    public function classifyImage(string $imagePath, array $options = []): array
    {
        $this->validateImage($imagePath);
        
        // 合并选项
        $mergedOptions = array_merge($this->config, $options);

        // 检查缓存
        if ($mergedOptions['cache_enabled']) {
            $cacheKey = 'cv_classify_' . md5_file($imagePath) . '_' . md5(json_encode($mergedOptions));
            if ($this->cache && $this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }

        try {
            // 调用图像分类
            $result = $this->imageEngine->classifyImage($imagePath);
            
            // 缓存结果
            if ($mergedOptions['cache_enabled'] && $this->cache) {
                $this->cache->set($cacheKey, $result, $mergedOptions['cache_ttl']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('图像分类失败', [
                    'image_path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
            }
            throw new RuntimeException('图像分类失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 光学字符识别(OCR)
     *
     * @param string $imagePath 图像路径
     * @param array $options OCR选项
     * @return array OCR结果
     */
    public function recognizeText(string $imagePath, array $options = []): array
    {
        $this->validateImage($imagePath);
        
        // 合并选项
        $mergedOptions = array_merge($this->config, $options);

        // 检查缓存
        if ($mergedOptions['cache_enabled']) {
            $cacheKey = 'cv_ocr_' . md5_file($imagePath) . '_' . md5(json_encode($mergedOptions));
            if ($this->cache && $this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }

        try {
            // 调用OCR
            $result = $this->imageEngine->extractText($imagePath);
            
            // 缓存结果
            if ($mergedOptions['cache_enabled'] && $this->cache) {
                $this->cache->set($cacheKey, $result, $mergedOptions['cache_ttl']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('文本识别失败', [
                    'image_path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
            }
            throw new RuntimeException('文本识别失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 图像比较
     *
     * @param string $image1Path 第一张图像路径
     * @param string $image2Path 第二张图像路径
     * @param array $options 比较选项
     * @return array 比较结果
     */
    public function compareImages(string $image1Path, string $image2Path, array $options = []): array
    {
        $this->validateImage($image1Path);
        $this->validateImage($image2Path);
        
        // 合并选项
        $mergedOptions = array_merge($this->config, $options);

        // 检查缓存
        if ($mergedOptions['cache_enabled']) {
            $cacheKey = 'cv_compare_' . md5_file($image1Path) . '_' . md5_file($image2Path) . '_' . md5(json_encode($mergedOptions));
            if ($this->cache && $this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }

        try {
            // 分别获取两张图像的特征
            $features1 = $this->extractImageFeatures($image1Path);
            $features2 = $this->extractImageFeatures($image2Path);
            
            // 计算相似度
            $similarity = $this->calculateImageSimilarity($features1, $features2);
            
            $result = [
                'similarity' => $similarity,
                'is_similar' => $similarity > $mergedOptions['confidence_threshold'],
                'comparison_details' => [
                    'method' => 'feature_based',
                    'feature_dimension' => count($features1),
                    'threshold' => $mergedOptions['confidence_threshold']
                ]
            ];
            
            // 缓存结果
            if ($mergedOptions['cache_enabled'] && $this->cache) {
                $this->cache->set($cacheKey, $result, $mergedOptions['cache_ttl']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('图像比较失败', [
                    'image1_path' => $image1Path,
                    'image2_path' => $image2Path,
                    'error' => $e->getMessage()
                ]);
            }
            throw new RuntimeException('图像比较失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 提取图像特征
     *
     * @param string $imagePath 图像路径
     * @return array 图像特征
     */
    private function extractImageFeatures(string $imagePath): array
    {
        // 这里应该调用模型提取特征
        // 简单模拟特征提取
        $featureSize = 512;
        $features = [];
        
        // 生成随机特征向量（实际应该使用模型）
        for ($i = 0; $i < $featureSize; $i++) {
            $features[] = mt_rand() / mt_getrandmax();
        }
        
        return $features;
    }

    /**
     * 计算图像相似度
     *
     * @param array $features1 第一个特征向量
     * @param array $features2 第二个特征向量
     * @return float 相似度（0-1之间）
     */
    private function calculateImageSimilarity(array $features1, array $features2): float
    {
        // 计算余弦相似度
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        
        $count = min(count($features1), count($features2));
        
        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $features1[$i] * $features2[$i];
            $magnitude1 += $features1[$i] * $features1[$i];
            $magnitude2 += $features2[$i] * $features2[$i];
        }
        
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }
        
        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * 验证图像
     *
     * @param string $imagePath 图像路径
     * @throws InvalidArgumentException 图像无效时抛出异常
     */
    private function validateImage(string $imagePath): void
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("图像文件不存在: {$imagePath}");
        }
        
        $fileInfo = pathinfo($imagePath);
        if (!isset($fileInfo['extension']) || !in_array(strtolower($fileInfo['extension']), $this->supportedFormats)) {
            throw new InvalidArgumentException("不支持的图像格式: {$fileInfo['extension']}");
        }
        
        if (filesize($imagePath) > $this->config['max_image_size']) {
            throw new InvalidArgumentException("图像文件过大: " . filesize($imagePath) . " 字节 (最大: {$this->config['max_image_size']} 字节)");
        }
    }

    /**
     * 获取API配置
     *
     * @return array 配置参数
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 更新API配置
     *
     * @param array $config 新的配置参数
     * @return void
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取支持的图像格式
     *
     * @return array 支持的图像格式
     */
    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }

    /**
     * 获取性能统计信息
     *
     * @return array 性能统计信息
     */
    public function getPerformanceStats(): array
    {
        return $this->imageEngine->getPerformanceStats();
    }

    /**
     * 清理API资源
     *
     * @return void
     */
    public function cleanup(): void
    {
        if ($this->cache) {
            $this->cache->clear('cv_*');
        }
        
        if ($this->logger) {
            $this->logger->info('计算机视觉API资源已释放');
        }
    }
} 