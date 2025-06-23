<?php
/**
 * 文件名：ImageRecognitionEngine.php
 * 功能描述：图像识别引擎 - 实现图像识别、物体检测等计算机视觉功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 * 
 * @package AlingAi\AI\Engines\CV
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\AI\Engines\CV;

use Exception;
use InvalidArgumentException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;
use AlingAi\Utils\PerformanceMonitor;

/**
 * 图像识别引擎
 * 
 * 提供图像识别、物体检测、人脸识别、图像分类等核心CV功能
 * 支持多种图像格式，具备高性能和可扩展性
 */
class ImageRecognitionEngine
{
    private LoggerInterface $logger;
    private CacheManager $cache;
    private PerformanceMonitor $monitor;
    
    // 配置参数
    private array $config;
    private array $supportedFormats;
    private array $modelConfigs;
    
    // 模型实例
    private $objectDetectionModel;
    private $faceRecognitionModel;
    private $imageClassificationModel;
    private $ocrModel;
    
    public function __construct(
        LoggerInterface $logger,
        CacheManager $cache,
        PerformanceMonitor $monitor,
        array $config = []
    ) {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->monitor = $monitor;
        $this->config = array_merge($this->getDefaultConfig(), $config);
        
        $this->initializeModels();
        $this->loadResources();
    }
    
    /**
     * 获取默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'max_image_size' => 10 * 1024 * 1024, // 10MB
            'supported_formats' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'enable_object_detection' => true,
            'enable_face_recognition' => true,
            'enable_image_classification' => true,
            'enable_ocr' => true,
            'confidence_threshold' => 0.5,
            'max_detections' => 100,
            'performance_monitoring' => true
        ];
    }
    
    /**
     * 初始化模型
     */
    private function initializeModels(): void
    {
        try {
            $this->objectDetectionModel = $this->createObjectDetectionModel();
            $this->faceRecognitionModel = $this->createFaceRecognitionModel();
            $this->imageClassificationModel = $this->createImageClassificationModel();
            $this->ocrModel = $this->createOCRModel();
            
            $this->logger->info('ImageRecognitionEngine models initialized successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to initialize ImageRecognitionEngine models: ' . $e->getMessage());
            throw new Exception('模型初始化失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 加载资源
     */
    private function loadResources(): void
    {
        $this->supportedFormats = $this->config['supported_formats'];
        $this->modelConfigs = $this->loadModelConfigs();
    }
    
    /**
     * 创建物体检测模型
     */
    private function createObjectDetectionModel()
    {
        return new ObjectDetectionModel($this->config);
    }
    
    /**
     * 创建人脸识别模型
     */
    private function createFaceRecognitionModel()
    {
        return new FaceRecognitionModel($this->config);
    }
    
    /**
     * 创建图像分类模型
     */
    private function createImageClassificationModel()
    {
        return new ImageClassificationModel($this->config);
    }
    
    /**
     * 创建OCR模型
     */
    private function createOCRModel()
    {
        return new OCRModel($this->config);
    }
    
    /**
     * 加载模型配置
     */
    private function loadModelConfigs(): array
    {
        $configFile = __DIR__ . '/config/models.json';
        
        if (!file_exists($configFile)) {
            return $this->getDefaultModelConfigs();
        }
        
        $content = file_get_contents($configFile);
        return json_decode($content, true) ?: $this->getDefaultModelConfigs();
    }
    
    /**
     * 获取默认模型配置
     */
    private function getDefaultModelConfigs(): array
    {
        return [
            'object_detection' => [
                'model_path' => __DIR__ . '/models/object_detection.onnx',
                'labels_path' => __DIR__ . '/models/coco_labels.txt',
                'input_size' => [640, 640],
                'confidence_threshold' => 0.5,
                'nms_threshold' => 0.4
            ],
            'face_recognition' => [
                'model_path' => __DIR__ . '/models/face_recognition.onnx',
                'input_size' => [112, 112],
                'confidence_threshold' => 0.6
            ],
            'image_classification' => [
                'model_path' => __DIR__ . '/models/image_classification.onnx',
                'labels_path' => __DIR__ . '/models/imagenet_labels.txt',
                'input_size' => [224, 224],
                'top_k' => 5
            ],
            'ocr' => [
                'model_path' => __DIR__ . '/models/ocr.onnx',
                'input_size' => [640, 640],
                'confidence_threshold' => 0.5
            ]
        ];
    }
    
    /**
     * 图像预处理
     * 
     * @param string $imagePath 图像路径
     * @return array 预处理结果
     * @throws InvalidArgumentException
     */
    public function preprocess(string $imagePath): array
    {
        $this->monitor->start('image_preprocessing');
        
        try {
            // 验证图像文件
            $this->validateImage($imagePath);
            
            // 生成缓存键
            $cacheKey = 'preprocess_' . md5_file($imagePath);
            
            // 检查缓存
            if ($this->config['cache_enabled']) {
                $cached = $this->cache->get($cacheKey);
                if ($cached !== null) {
                    $this->monitor->end('image_preprocessing');
                    return $cached;
                }
            }
            
            // 获取图像信息
            $imageInfo = $this->getImageInfo($imagePath);
            
            // 图像预处理
            $processedImage = $this->processImage($imagePath, $imageInfo);
            
            $result = [
                'original_path' => $imagePath,
                'image_info' => $imageInfo,
                'processed_image' => $processedImage,
                'processing_time' => 0
            ];
            
            // 缓存结果
            if ($this->config['cache_enabled']) {
                $this->cache->set($cacheKey, $result, $this->config['cache_ttl']);
            }
            
            $this->monitor->end('image_preprocessing');
            $result['processing_time'] = $this->monitor->getDuration('image_preprocessing');
            
            $this->logger->info('Image preprocessing completed', [
                'image_path' => $imagePath,
                'processing_time' => $result['processing_time']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end('image_preprocessing');
            $this->logger->error('Image preprocessing failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 验证图像文件
     */
    private function validateImage(string $imagePath): void
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException('图像文件不存在: ' . $imagePath);
        }
        
        if (!is_readable($imagePath)) {
            throw new InvalidArgumentException('图像文件不可读: ' . $imagePath);
        }
        
        $fileSize = filesize($imagePath);
        if ($fileSize > $this->config['max_image_size']) {
            throw new InvalidArgumentException('图像文件过大: ' . $fileSize . ' bytes');
        }
        
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->supportedFormats)) {
            throw new InvalidArgumentException('不支持的图像格式: ' . $extension);
        }
        
        // 验证图像文件完整性
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            throw new InvalidArgumentException('无效的图像文件: ' . $imagePath);
        }
    }
    
    /**
     * 获取图像信息
     */
    private function getImageInfo(string $imagePath): array
    {
        $imageInfo = getimagesize($imagePath);
        
        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => $imageInfo[2],
            'mime' => $imageInfo['mime'],
            'file_size' => filesize($imagePath),
            'format' => strtolower(pathinfo($imagePath, PATHINFO_EXTENSION))
        ];
    }
    
    /**
     * 处理图像
     */
    private function processImage(string $imagePath, array $imageInfo): array
    {
        // 读取图像
        $image = $this->loadImage($imagePath, $imageInfo['type']);
        
        // 图像增强
        $enhancedImage = $this->enhanceImage($image);
        
        // 图像标准化
        $normalizedImage = $this->normalizeImage($enhancedImage);
        
        return [
            'original' => $image,
            'enhanced' => $enhancedImage,
            'normalized' => $normalizedImage
        ];
    }
    
    /**
     * 加载图像
     */
    private function loadImage(string $imagePath, int $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($imagePath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($imagePath);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($imagePath);
            case IMAGETYPE_BMP:
                return imagecreatefromwbmp($imagePath);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($imagePath);
            default:
                throw new Exception('不支持的图像类型: ' . $type);
        }
    }
    
    /**
     * 图像增强
     */
    private function enhanceImage($image)
    {
        // 自动对比度调整
        $image = $this->autoContrast($image);
        
        // 噪声减少
        $image = $this->reduceNoise($image);
        
        // 锐化
        $image = $this->sharpen($image);
        
        return $image;
    }
    
    /**
     * 自动对比度调整
     */
    private function autoContrast($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        
        // 计算直方图
        $histogram = [];
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $gray = ($rgb >> 16) * 0.299 + (($rgb >> 8) & 255) * 0.587 + ($rgb & 255) * 0.114;
                $histogram[(int)$gray]++;
            }
        }
        
        // 计算累积分布
        $total = $width * $height;
        $cumulative = 0;
        $lookup = [];
        
        for ($i = 0; $i < 256; $i++) {
            $cumulative += $histogram[$i] ?? 0;
            $lookup[$i] = (int)(($cumulative / $total) * 255);
        }
        
        // 应用查找表
        $enhancedImage = imagecreatetruecolor($width, $height);
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 255;
                $g = ($rgb >> 8) & 255;
                $b = $rgb & 255;
                
                $newR = $lookup[$r];
                $newG = $lookup[$g];
                $newB = $lookup[$b];
                
                $color = imagecolorallocate($enhancedImage, $newR, $newG, $newB);
                imagesetpixel($enhancedImage, $x, $y, $color);
            }
        }
        
        return $enhancedImage;
    }
    
    /**
     * 噪声减少
     */
    private function reduceNoise($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        
        $filteredImage = imagecreatetruecolor($width, $height);
        
        // 中值滤波
        for ($x = 1; $x < $width - 1; $x++) {
            for ($y = 1; $y < $height - 1; $y++) {
                $pixels = [];
                
                // 收集3x3邻域像素
                for ($i = -1; $i <= 1; $i++) {
                    for ($j = -1; $j <= 1; $j++) {
                        $pixels[] = imagecolorat($image, $x + $i, $y + $j);
                    }
                }
                
                // 计算中值
                sort($pixels);
                $median = $pixels[4]; // 9个像素的中值是第5个
                
                imagesetpixel($filteredImage, $x, $y, $median);
            }
        }
        
        return $filteredImage;
    }
    
    /**
     * 图像锐化
     */
    private function sharpen($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        
        $sharpenedImage = imagecreatetruecolor($width, $height);
        
        // 锐化核
        $kernel = [
            [0, -1, 0],
            [-1, 5, -1],
            [0, -1, 0]
        ];
        
        for ($x = 1; $x < $width - 1; $x++) {
            for ($y = 1; $y < $height - 1; $y++) {
                $r = $g = $b = 0;
                
                // 应用卷积核
                for ($i = -1; $i <= 1; $i++) {
                    for ($j = -1; $j <= 1; $j++) {
                        $rgb = imagecolorat($image, $x + $i, $y + $j);
                        $weight = $kernel[$i + 1][$j + 1];
                        
                        $r += (($rgb >> 16) & 255) * $weight;
                        $g += (($rgb >> 8) & 255) * $weight;
                        $b += ($rgb & 255) * $weight;
                    }
                }
                
                // 限制值范围
                $r = max(0, min(255, $r));
                $g = max(0, min(255, $g));
                $b = max(0, min(255, $b));
                
                $color = imagecolorallocate($sharpenedImage, $r, $g, $b);
                imagesetpixel($sharpenedImage, $x, $y, $color);
            }
        }
        
        return $sharpenedImage;
    }
    
    /**
     * 图像标准化
     */
    private function normalizeImage($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        
        $normalizedImage = imagecreatetruecolor($width, $height);
        
        // 计算均值和标准差
        $sum = 0;
        $sumSq = 0;
        $count = 0;
        
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $gray = ($rgb >> 16) * 0.299 + (($rgb >> 8) & 255) * 0.587 + ($rgb & 255) * 0.114;
                $sum += $gray;
                $sumSq += $gray * $gray;
                $count++;
            }
        }
        
        $mean = $sum / $count;
        $std = sqrt(($sumSq / $count) - ($mean * $mean));
        
        // 标准化
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 255;
                $g = ($rgb >> 8) & 255;
                $b = $rgb & 255;
                
                $newR = (int)((($r - $mean) / $std) * 255 + 128);
                $newG = (int)((($g - $mean) / $std) * 255 + 128);
                $newB = (int)((($b - $mean) / $std) * 255 + 128);
                
                $newR = max(0, min(255, $newR));
                $newG = max(0, min(255, $newG));
                $newB = max(0, min(255, $newB));
                
                $color = imagecolorallocate($normalizedImage, $newR, $newG, $newB);
                imagesetpixel($normalizedImage, $x, $y, $color);
            }
        }
        
        return $normalizedImage;
    }
    
    /**
     * 物体检测
     * 
     * @param string $imagePath 图像路径
     * @return array 检测结果
     */
    public function detectObjects(string $imagePath): array
    {
        if (!$this->config['enable_object_detection']) {
            throw new Exception('物体检测功能未启用');
        }
        
        $this->monitor->start('object_detection');
        
        try {
            $preprocessed = $this->preprocess($imagePath);
            $detections = $this->objectDetectionModel->detect($preprocessed['processed_image']['normalized']);
            
            $result = [
                'detections' => $detections,
                'detection_count' => count($detections),
                'processing_time' => 0
            ];
            
            $this->monitor->end('object_detection');
            $result['processing_time'] = $this->monitor->getDuration('object_detection');
            
            $this->logger->info('Object detection completed', [
                'detection_count' => $result['detection_count'],
                'processing_time' => $result['processing_time']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end('object_detection');
            $this->logger->error('Object detection failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 人脸识别
     * 
     * @param string $imagePath 图像路径
     * @return array 识别结果
     */
    public function recognizeFaces(string $imagePath): array
    {
        if (!$this->config['enable_face_recognition']) {
            throw new Exception('人脸识别功能未启用');
        }
        
        $this->monitor->start('face_recognition');
        
        try {
            $preprocessed = $this->preprocess($imagePath);
            $faces = $this->faceRecognitionModel->recognize($preprocessed['processed_image']['normalized']);
            
            $result = [
                'faces' => $faces,
                'face_count' => count($faces),
                'processing_time' => 0
            ];
            
            $this->monitor->end('face_recognition');
            $result['processing_time'] = $this->monitor->getDuration('face_recognition');
            
            $this->logger->info('Face recognition completed', [
                'face_count' => $result['face_count'],
                'processing_time' => $result['processing_time']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end('face_recognition');
            $this->logger->error('Face recognition failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 图像分类
     * 
     * @param string $imagePath 图像路径
     * @return array 分类结果
     */
    public function classifyImage(string $imagePath): array
    {
        if (!$this->config['enable_image_classification']) {
            throw new Exception('图像分类功能未启用');
        }
        
        $this->monitor->start('image_classification');
        
        try {
            $preprocessed = $this->preprocess($imagePath);
            $classifications = $this->imageClassificationModel->classify($preprocessed['processed_image']['normalized']);
            
            $result = [
                'classifications' => $classifications,
                'top_class' => $classifications[0] ?? null,
                'processing_time' => 0
            ];
            
            $this->monitor->end('image_classification');
            $result['processing_time'] = $this->monitor->getDuration('image_classification');
            
            $this->logger->info('Image classification completed', [
                'top_class' => $result['top_class']['label'] ?? 'unknown',
                'processing_time' => $result['processing_time']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end('image_classification');
            $this->logger->error('Image classification failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * OCR文字识别
     * 
     * @param string $imagePath 图像路径
     * @return array 识别结果
     */
    public function extractText(string $imagePath): array
    {
        if (!$this->config['enable_ocr']) {
            throw new Exception('OCR功能未启用');
        }
        
        $this->monitor->start('ocr');
        
        try {
            $preprocessed = $this->preprocess($imagePath);
            $text = $this->ocrModel->extract($preprocessed['processed_image']['normalized']);
            
            $result = [
                'text' => $text,
                'text_length' => strlen($text),
                'processing_time' => 0
            ];
            
            $this->monitor->end('ocr');
            $result['processing_time'] = $this->monitor->getDuration('ocr');
            
            $this->logger->info('OCR completed', [
                'text_length' => $result['text_length'],
                'processing_time' => $result['processing_time']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end('ocr');
            $this->logger->error('OCR failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 完整图像分析
     * 
     * @param string $imagePath 图像路径
     * @return array 完整分析结果
     */
    public function analyze(string $imagePath): array
    {
        $this->monitor->start('full_analysis');
        
        try {
            $result = [
                'preprocessing' => $this->preprocess($imagePath),
                'analysis_time' => 0
            ];
            
            // 添加物体检测
            if ($this->config['enable_object_detection']) {
                $result['object_detection'] = $this->detectObjects($imagePath);
            }
            
            // 添加人脸识别
            if ($this->config['enable_face_recognition']) {
                $result['face_recognition'] = $this->recognizeFaces($imagePath);
            }
            
            // 添加图像分类
            if ($this->config['enable_image_classification']) {
                $result['image_classification'] = $this->classifyImage($imagePath);
            }
            
            // 添加OCR
            if ($this->config['enable_ocr']) {
                $result['ocr'] = $this->extractText($imagePath);
            }
            
            $this->monitor->end('full_analysis');
            $result['analysis_time'] = $this->monitor->getDuration('full_analysis');
            
            $this->logger->info('Full image analysis completed', [
                'analysis_time' => $result['analysis_time']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end('full_analysis');
            $this->logger->error('Full image analysis failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 获取性能统计
     */
    public function getPerformanceStats(): array
    {
        return $this->monitor->getStats();
    }
    
    /**
     * 清理缓存
     */
    public function clearCache(): void
    {
        if ($this->config['cache_enabled']) {
            $this->cache->clear();
            $this->logger->info('ImageRecognitionEngine cache cleared');
        }
    }
}
