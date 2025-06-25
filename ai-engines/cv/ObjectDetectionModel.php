<?php
/**
 * 文件名：ObjectDetectionModel.php
 * 功能描述：物体检测模�?- 识别图像中的多个物体及其位置
 * 创建时间�?025-01-XX
 * 最后修改：2025-01-XX
 * 版本�?.0.0
 * 
 * @package AlingAi\Engines\CV
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\Engines\CV;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;

/**
 * 物体检测模�? * 
 * 负责在图像中检测和定位多个物体，支持边界框(bbox)、像素分�?mask)和关键点检测等多种输出形式
 */
class ObjectDetectionModel
{
    /**
     * @var array 配置参数
     */
    private array $config;
    
    /**
     * @var LoggerInterface|null 日志记录�?     */
    private ?LoggerInterface $logger;
    
    /**
     * @var CacheManager|null 缓存管理�?     */
    private ?CacheManager $cache;
    
    /**
     * @var array 模型实例缓存
     */
    private array $models = [];
    
    /**
     * @var array 物体类别集合
     */
    private array $objectCategories = [];
    
    /**
     * @var array 支持的检测模型架�?     */
    private array $supportedArchitectures = ['yolo', 'ssd', 'faster_rcnn', 'mask_rcnn', 'detr'];
    
    /**
     * @var array|null 物体跟踪状�?     */
    private ?array $trackingState = null;
    
    /**
     * 构造函�?     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志记录�?     * @param CacheManager|null $cache 缓存管理�?     */
    public function __construct(array $config = [],  ?LoggerInterface $logger = null, ?CacheManager $cache = null)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $this->mergeConfig($config];
        
        $this->initialize(];
        
        if ($this->logger) {
            $this->logger->info('物体检测模型初始化成功', [
                'model_architecture' => $this->config['model_architecture'], 
                'confidence_threshold' => $this->config['confidence_threshold'], 
                'iou_threshold' => $this->config['iou_threshold']
            ]];
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
            'model_architecture' => 'yolo', // 模型架构
            'model_version' => 'v5', // 模型版本
            'confidence_threshold' => 0.4, // 置信度阈�?            'iou_threshold' => 0.5, // IOU阈值，用于非极大值抑�?            'max_detections' => 100, // 最大检测数�?            'enable_batch_processing' => false, // 是否启用批处�?            'batch_size' => 8, // 批处理大�?            'enable_tracking' => false, // 是否启用物体跟踪
            'cache_enabled' => true, // 是否启用缓存
            'cache_ttl' => 3600, // 缓存有效�?�?
            'use_gpu' => false, // 是否使用GPU加�?            'input_size' => [640, 640],  // 输入尺寸 [高度, 宽度]
            'pixel_mean' => [0.485, 0.456, 0.406],  // 像素均值，用于标准�?            'pixel_std' => [0.229, 0.224, 0.225],  // 像素标准差，用于标准�?            'model_path' => null, // 模型文件路径
            'classes_path' => null, // 类别文件路径
            'enable_mask' => false, // 是否启用像素级分�?            'enable_keypoints' => false, // 是否启用关键点检�?        ];
        
        return array_merge($defaultConfig, $config];
    }
    
    /**
     * 初始化模�?     */
    private function initialize(): void
    {
        $this->loadObjectCategories(];
        
        // 实际项目中这里会加载预训练模�?        // 本实现中使用模拟模型进行演示
    }
    
    /**
     * 加载物体类别
     */
    private function loadObjectCategories(): void
    {
        // 在实际项目中，这里会从文件加载完整的物体类别
        // 本实现中使用COCO数据集的常见类别
        
        $this->objectCategories = [
            1 => ['id' => 1, 'name' => 'person', 'label' => '�?, 'supercategory' => 'person'], 
            2 => ['id' => 2, 'name' => 'bicycle', 'label' => '自行�?, 'supercategory' => 'vehicle'], 
            3 => ['id' => 3, 'name' => 'car', 'label' => '汽车', 'supercategory' => 'vehicle'], 
            4 => ['id' => 4, 'name' => 'motorcycle', 'label' => '摩托�?, 'supercategory' => 'vehicle'], 
            5 => ['id' => 5, 'name' => 'airplane', 'label' => '飞机', 'supercategory' => 'vehicle'], 
            6 => ['id' => 6, 'name' => 'bus', 'label' => '公交�?, 'supercategory' => 'vehicle'], 
            7 => ['id' => 7, 'name' => 'train', 'label' => '火车', 'supercategory' => 'vehicle'], 
            8 => ['id' => 8, 'name' => 'truck', 'label' => '卡车', 'supercategory' => 'vehicle'], 
            9 => ['id' => 9, 'name' => 'boat', 'label' => '�?, 'supercategory' => 'vehicle'], 
            10 => ['id' => 10, 'name' => 'traffic light', 'label' => '交通灯', 'supercategory' => 'outdoor'], 
            11 => ['id' => 11, 'name' => 'fire hydrant', 'label' => '消防�?, 'supercategory' => 'outdoor'], 
            12 => ['id' => 12, 'name' => 'stop sign', 'label' => '停止标志', 'supercategory' => 'outdoor'], 
            13 => ['id' => 13, 'name' => 'parking meter', 'label' => '停车计时�?, 'supercategory' => 'outdoor'], 
            14 => ['id' => 14, 'name' => 'bench', 'label' => '长凳', 'supercategory' => 'outdoor'], 
            15 => ['id' => 15, 'name' => 'bird', 'label' => '�?, 'supercategory' => 'animal'], 
            16 => ['id' => 16, 'name' => 'cat', 'label' => '�?, 'supercategory' => 'animal'], 
            17 => ['id' => 17, 'name' => 'dog', 'label' => '�?, 'supercategory' => 'animal'], 
            18 => ['id' => 18, 'name' => 'horse', 'label' => '�?, 'supercategory' => 'animal'], 
            19 => ['id' => 19, 'name' => 'sheep', 'label' => '�?, 'supercategory' => 'animal'], 
            20 => ['id' => 20, 'name' => 'cow', 'label' => '�?, 'supercategory' => 'animal'], 
        ];
    }
    
    /**
     * 物体检测主方法
     *
     * @param mixed $image 图像数据(路径或图像数�?
     * @param array $options 检测选项
     * @return array 检测结�?     * @throws InvalidArgumentException 参数无效时抛出异�?     * @throws RuntimeException 处理失败时抛出异�?     */
    public function detect($image, array $options = []): array
    {
        // 合并选项
        $options = array_merge($this->config, $options];
        
        try {
            // 检查缓�?            if ($options['cache_enabled'] && $this->cache) {
                $imagePath = is_string($image) ? $image : '';
                if ($imagePath && file_exists($imagePath)) {
                    $cacheKey = 'obj_detection_' . md5_file($imagePath) . '_' . md5(json_encode($options)];
                    if ($this->cache->has($cacheKey)) {
                        return $this->cache->get($cacheKey];
                    }
                }
            }
            
            // 获取图像信息
            $imageInfo = $this->getImageInfo($image];
            
            // 预处理图�?            $processedImage = $this->preprocessImage($image, $options];
            
            // 运行检测模�?            $detectionResults = $this->runDetectionModel($processedImage, $options];
            
            // 后处理结�?            $result = $this->postprocessResults($detectionResults, $imageInfo, $options];
            
            // 如果启用了跟踪功�?            if ($options['enable_tracking'] && isset($this->trackingState)) {
                $result = $this->trackObjects($result, $imageInfo['timestamp'] ?? time()];
            }
            
            // 缓存结果
            if ($options['cache_enabled'] && $this->cache && isset($cacheKey)) {
                $this->cache->set($cacheKey, $result, $options['cache_ttl']];
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('物体检测失�?, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]];
            }
            throw new RuntimeException('物体检测失�? ' . $e->getMessage(), 0, $e];
        }
    }
    
    /**
     * 批量物体检�?     *
     * @param array $images 图像路径或数据数�?     * @param array $options 检测选项
     * @return array 检测结果数�?     */
    public function detectBatch(array $images, array $options = []): array
    {
        if (!$this->config['enable_batch_processing']) {
            throw new RuntimeException('批量处理未启�?];
        }
        
        $results = [];
        $batchSize = $this->config['batch_size'];
        $startTime = microtime(true];
        
        // 分批处理
        for ($i = 0; $i < count($images]; $i += $batchSize) {
            $batch = array_slice($images, $i, $batchSize];
            $batchResults = [];
            
            // 处理当前批次
            foreach ($batch as $index => $image) {
                try {
                    $batchResults[$index] = $this->detect($image, $options];
                } catch (Exception $e) {
                    $batchResults[$index] = ['error' => $e->getMessage()];
                    
                    if ($this->logger) {
                        $this->logger->error('批量物体检测失�?, [
                            'batch_index' => $i + $index,
                            'error' => $e->getMessage()
                        ]];
                    }
                }
            }
            
            $results = array_merge($results, $batchResults];
        }
        
        $totalTime = microtime(true) - $startTime;
        
        return [
            'results' => $results,
            'total_images' => count($images],
            'total_time' => round($totalTime * 1000], // 转换为毫�?            'average_time_per_image' => round(($totalTime * 1000) / count($images)],
            'batch_size' => $batchSize,
            'num_batches' => ceil(count($images) / $batchSize)
        ];
    }
    
    /**
     * 计算目标检测的评价指标
     *
     * @param array $detections 检测结�?     * @param array $groundTruth 真实标注
     * @return array 评价指标
     */
    public function evaluateDetection(array $detections, array $groundTruth): array
    {
        $metrics = [
            'precision' => 0,
            'recall' => 0,
            'f1_score' => 0,
            'ap' => [],  // 各类别的平均精度
            'map' => 0, // 平均精度的均�?        ];
        
        $totalTruePositives = 0;
        $totalFalsePositives = 0;
        $totalGroundTruth = count($groundTruth];
        
        // 计算各个类别的AP
        foreach ($this->objectCategories as $categoryId => $category) {
            // 筛选当前类别的检测结果和真实标注
            $categoryDetections = array_filter($detections, function($detection) use ($categoryId) {
                return $detection['category_id'] === $categoryId;
            }];
            
            $categoryGroundTruth = array_filter($groundTruth, function($gt) use ($categoryId) {
                return $gt['category_id'] === $categoryId;
            }];
            
            // 计算当前类别的AP
            $ap = $this->calculateAP($categoryDetections, $categoryGroundTruth];
            $metrics['ap'][$categoryId] = $ap;
            
            // 更新总计�?            $truePositives = $ap['true_positives'];
            $falsePositives = $ap['false_positives'];
            
            $totalTruePositives += $truePositives;
            $totalFalsePositives += $falsePositives;
        }
        
        // 计算总体指标
        if ($totalTruePositives + $totalFalsePositives > 0) {
            $metrics['precision'] = $totalTruePositives / ($totalTruePositives + $totalFalsePositives];
        }
        
        if ($totalGroundTruth > 0) {
            $metrics['recall'] = $totalTruePositives / $totalGroundTruth;
        }
        
        if ($metrics['precision'] + $metrics['recall'] > 0) {
            $metrics['f1_score'] = 2 * $metrics['precision'] * $metrics['recall'] / 
                                  ($metrics['precision'] + $metrics['recall']];
        }
        
        // 计算mAP
        $metrics['map'] = count($metrics['ap']) > 0 ? 
                         array_sum(array_column($metrics['ap'],  'ap')) / count($metrics['ap']) : 0;
        
        return $metrics;
    }
    
    /**
     * 获取图像信息
     * 
     * @param mixed $image 图像数据(路径或图像数�?
     * @return array 图像信息
     */
    private function getImageInfo($image): array
    {
        if (is_string($image) && file_exists($image)) {
            // 如果是真实图像，获取实际尺寸
            $imageSize = getimagesize($image];
            if ($imageSize) {
                return [
                    'width' => $imageSize[0], 
                    'height' => $imageSize[1], 
                    'type' => $imageSize['mime'] ?? 'unknown',
                    'path' => $image,
                    'timestamp' => filemtime($image) ?: time()
                ];
            }
        }
        
        // 如果无法获取，返回默认�?        return [
            'width' => 640,
            'height' => 480,
            'type' => 'unknown',
            'timestamp' => time()
        ];
    }
    
    /**
     * 预处理图�?     *
     * @param mixed $image 图像数据
     * @param array $options 处理选项
     * @return array 预处理后的图像数�?     */
    private function preprocessImage($image, array $options): array
    {
        // 在实际项目中，这里会进行真实的图像预处理
        // 本实现中使用模拟数据
        
        $imageInfo = $this->getImageInfo($image];
        
        // 模拟预处理结�?        return [
            'processed_data' => [
                'width' => $options['input_size'][1], 
                'height' => $options['input_size'][0], 
                'channels' => 3,
                'original_size' => [$imageInfo['width'],  $imageInfo['height']], 
                'scale_x' => $imageInfo['width'] / $options['input_size'][1], 
                'scale_y' => $imageInfo['height'] / $options['input_size'][0]
            ], 
            'path' => $imageInfo['path'] ?? null
        ];
    }
    
    /**
     * 运行检测模�?     *
     * @param array $processedImage 预处理后的图�?     * @param array $options 处理选项
     * @return array 检测结�?     */
    private function runDetectionModel(array $processedImage, array $options): array
    {
        // 在实际项目中，这里会运行真实的检测模�?        // 本实现中使用模拟数据生成检测结�?        
        $architecture = $options['model_architecture'];
        $detections = [];
        
        // 根据不同架构生成不同的检测结�?        switch ($architecture) {
            case 'yolo':
                $detections = $this->simulateYoloDetections($options];
                break;
            case 'ssd':
                $detections = $this->simulateSSDDetections($options];
                break;
            case 'faster_rcnn':
                $detections = $this->simulateFasterRCNNDetections($options];
                break;
            case 'mask_rcnn':
                $detections = $this->simulateMaskRCNNDetections($options];
                break;
            case 'detr':
                $detections = $this->simulateDETRDetections($options];
                break;
            default:
                $detections = $this->simulateYoloDetections($options];
        }
        
        return [
            'raw_detections' => $detections,
            'model_info' => [
                'architecture' => $architecture,
                'version' => $options['model_version']
            ]
        ];
    }
    
    /**
     * 模拟YOLO检测结�?     *
     * @param array $options 处理选项
     * @return array 检测结�?     */
    private function simulateYoloDetections(array $options): array
    {
        // 随机生成5-15个检测框
        $numDetections = rand(5, 15];
        $detections = [];
        
        for ($i = 0; $i < $numDetections; $i++) {
            // 随机选择一个类�?            $categoryId = array_rand($this->objectCategories];
            $category = $this->objectCategories[$categoryId];
            
            // 生成随机边界�?[x1, y1, x2, y2]，值在0-1之间
            $x1 = rand(0, 800) / 1000;
            $y1 = rand(0, 800) / 1000;
            $width = rand(50, 300) / 1000;
            $height = rand(50, 300) / 1000;
            $x2 = min(1.0, $x1 + $width];
            $y2 = min(1.0, $y1 + $height];
            
            // 生成随机置信度，偏向高�?            $confidence = (rand(650, 990) / 1000) * (1 - ($i / $numDetections / 3)];
            
            $detection = [
                'bbox' => [$x1, $y1, $x2, $y2], 
                'category_id' => $categoryId,
                'confidence' => $confidence
            ];
            
            // 如果启用了掩码，添加掩码数据
            if ($options['enable_mask']) {
                $maskHeight = 28;
                $maskWidth = 28;
                $mask = [];
                
                // 简单的圆形掩码
                $centerX = $maskWidth / 2;
                $centerY = $maskHeight / 2;
                $radius = min($maskWidth, $maskHeight) / 2 * 0.8;
                
                for ($y = 0; $y < $maskHeight; $y++) {
                    $row = [];
                    for ($x = 0; $x < $maskWidth; $x++) {
                        $distance = sqrt(pow($x - $centerX, 2) + pow($y - $centerY, 2)];
                        $row[] = $distance <= $radius ? 1.0 : 0.0;
                    }
                    $mask[] = $row;
                }
                
                $detection['mask'] = $mask;
            }
            
            // 如果启用了关键点，添加关键点数据
            if ($options['enable_keypoints'] && $category['name'] === 'person') {
                $keypoints = [];
                
                // 人体�?7个关键点 (COCO格式)
                $numKeypoints = 17;
                for ($k = 0; $k < $numKeypoints; $k++) {
                    $kx = $x1 + ($x2 - $x1) * rand(100, 900) / 1000;
                    $ky = $y1 + ($y2 - $y1) * rand(100, 900) / 1000;
                    $visibility = rand(0, 10) > 2 ? 2 : rand(0, 1]; // 0: 不可�? 1: 被遮�? 2: 可见
                    
                    $keypoints[] = [
                        'x' => $kx,
                        'y' => $ky,
                        'visibility' => $visibility
                    ];
                }
                
                $detection['keypoints'] = $keypoints;
            }
            
            $detections[] = $detection;
        }
        
        return $detections;
    }
    
    /**
     * 模拟SSD检测结�?     *
     * @param array $options 处理选项
     * @return array 检测结�?     */
    private function simulateSSDDetections(array $options): array
    {
        // SSD模拟结果与YOLO类似，但数量和置信度分布不同
        return $this->simulateYoloDetections($options];
    }
    
    /**
     * 模拟Faster R-CNN检测结�?     *
     * @param array $options 处理选项
     * @return array 检测结�?     */
    private function simulateFasterRCNNDetections(array $options): array
    {
        // Faster R-CNN模拟结果与YOLO类似，但数量和置信度分布不同
        $detections = $this->simulateYoloDetections($options];
        
        // Faster R-CNN通常有更高的置信�?        foreach ($detections as &$detection) {
            $detection['confidence'] = min(0.99, $detection['confidence'] * 1.1];
        }
        
        return $detections;
    }
    
    /**
     * 模拟Mask R-CNN检测结�?     *
     * @param array $options 处理选项
     * @return array 检测结�?     */
    private function simulateMaskRCNNDetections(array $options): array
    {
        // 强制启用掩码
        $options['enable_mask'] = true;
        
        // 获取基础检测结�?        $detections = $this->simulateFasterRCNNDetections($options];
        
        // 确保所有检测都有掩�?        foreach ($detections as &$detection) {
            if (!isset($detection['mask'])) {
                $maskHeight = 28;
                $maskWidth = 28;
                $mask = [];
                
                // 简单的椭圆掩码
                $centerX = $maskWidth / 2;
                $centerY = $maskHeight / 2;
                $radiusX = $maskWidth / 2 * 0.8;
                $radiusY = $maskHeight / 2 * 0.8;
                
                for ($y = 0; $y < $maskHeight; $y++) {
                    $row = [];
                    for ($x = 0; $x < $maskWidth; $x++) {
                        $value = pow(($x - $centerX) / $radiusX, 2) + pow(($y - $centerY) / $radiusY, 2];
                        $row[] = $value <= 1.0 ? 1.0 : 0.0;
                    }
                    $mask[] = $row;
                }
                
                $detection['mask'] = $mask;
            }
        }
        
        return $detections;
    }
    
    /**
     * 模拟DETR检测结�?     *
     * @param array $options 处理选项
     * @return array 检测结�?     */
    private function simulateDETRDetections(array $options): array
    {
        // DETR模拟结果与YOLO类似，但置信度分布不�?        $detections = $this->simulateYoloDetections($options];
        
        // DETR通常对小目标效果较好
        $numSmallObjects = rand(2, 5];
        for ($i = 0; $i < $numSmallObjects; $i++) {
            // 随机选择一个类�?            $categoryId = array_rand($this->objectCategories];
            
            // 生成随机小边界框
            $x1 = rand(0, 900) / 1000;
            $y1 = rand(0, 900) / 1000;
            $width = rand(10, 80) / 1000;
            $height = rand(10, 80) / 1000;
            $x2 = min(1.0, $x1 + $width];
            $y2 = min(1.0, $y1 + $height];
            
            // 生成随机置信�?            $confidence = rand(700, 950) / 1000;
            
            $detections[] = [
                'bbox' => [$x1, $y1, $x2, $y2], 
                'category_id' => $categoryId,
                'confidence' => $confidence
            ];
        }
        
        return $detections;
    }
    
    /**
     * 后处理检测结�?     *
     * @param array $detectionResults 模型输出的检测结�?     * @param array $imageInfo 图像信息
     * @param array $options 处理选项
     * @return array 后处理后的结�?     */
    private function postprocessResults(array $detectionResults, array $imageInfo, array $options): array
    {
        $rawDetections = $detectionResults['raw_detections'];
        $processedDetections = [];
        
        foreach ($rawDetections as $detection) {
            // 获取置信�?            $confidence = $detection['confidence'];
            
            // 过滤低置信度检�?            if ($confidence < $options['confidence_threshold']) {
                continue;
            }
            
            // 获取类别信息
            $categoryId = $detection['category_id'];
            $category = $this->objectCategories[$categoryId] ?? [
                'id' => $categoryId,
                'name' => 'unknown',
                'label' => '未知',
                'supercategory' => 'other'
            ];
            
            // 获取边界框并转换到原始图像坐�?            $bbox = $detection['bbox'];
            $x1 = $bbox[0] * $imageInfo['width'];
            $y1 = $bbox[1] * $imageInfo['height'];
            $x2 = $bbox[2] * $imageInfo['width'];
            $y2 = $bbox[3] * $imageInfo['height'];
            
            $processedDetection = [
                'category_id' => $categoryId,
                'name' => $category['name'], 
                'label' => $category['label'], 
                'supercategory' => $category['supercategory'], 
                'confidence' => $confidence,
                'bbox' => [
                    'x1' => $x1,
                    'y1' => $y1,
                    'x2' => $x2,
                    'y2' => $y2,
                    'width' => $x2 - $x1,
                    'height' => $y2 - $y1,
                    'center_x' => ($x1 + $x2) / 2,
                    'center_y' => ($y1 + $y2) / 2,
                    'area' => ($x2 - $x1) * ($y2 - $y1)
                ], 
                'normalized_bbox' => $bbox
            ];
            
            // 如果有掩码，转换掩码
            if (isset($detection['mask'])) {
                $processedDetection['mask'] = $detection['mask'];
            }
            
            // 如果有关键点，转换关键点
            if (isset($detection['keypoints'])) {
                $processedKeypoints = [];
                foreach ($detection['keypoints'] as $keypoint) {
                    $processedKeypoints[] = [
                        'x' => $keypoint['x'] * $imageInfo['width'], 
                        'y' => $keypoint['y'] * $imageInfo['height'], 
                        'visibility' => $keypoint['visibility']
                    ];
                }
                $processedDetection['keypoints'] = $processedKeypoints;
            }
            
            $processedDetections[] = $processedDetection;
        }
        
        // 非极大值抑�?        $processedDetections = $this->nonMaxSuppression($processedDetections, $options['iou_threshold']];
        
        // 限制最大检测数�?        $processedDetections = array_slice($processedDetections, 0, $options['max_detections']];
        
        return [
            'detections' => $processedDetections,
            'image_info' => $imageInfo,
            'model_info' => $detectionResults['model_info'], 
            'count' => count($processedDetections],
            'processing_time' => rand(10, 150) // 模拟处理时间(毫秒)
        ];
    }
    
    /**
     * 非极大值抑�?     *
     * @param array $detections 检测结�?     * @param float $iouThreshold IOU阈�?     * @return array 过滤后的检测结�?     */
    private function nonMaxSuppression(array $detections, float $iouThreshold): array
    {
        if (empty($detections)) {
            return [];
        }
        
        // 按置信度排序
        usort($detections, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        }];
        
        $selected = [];
        $indexes = range(0, count($detections) - 1];
        
        while (!empty($indexes)) {
            // 取置信度最高的检�?            $current = array_shift($indexes];
            $selected[] = $detections[$current];
            
            // 更新剩余索引，移除与当前检测重叠的索引
            $indexes = array_filter($indexes, function($index) use ($detections, $current, $iouThreshold) {
                $iou = $this->calculateIoU(
                    $detections[$current]['bbox'], 
                    $detections[$index]['bbox']
                ];
                
                // 如果IoU大于阈值，则移�?                return $iou <= $iouThreshold;
            }];
            
            // 重新索引
            $indexes = array_values($indexes];
        }
        
        return $selected;
    }
    
    /**
     * 计算两个边界框的IoU（交并比�?     *
     * @param array $box1 第一个边界框
     * @param array $box2 第二个边界框
     * @return float IoU�?     */
    private function calculateIoU(array $box1, array $box2): float
    {
        // 获取坐标
        $x1_1 = $box1['x1'] ?? $box1[0];
        $y1_1 = $box1['y1'] ?? $box1[1];
        $x2_1 = $box1['x2'] ?? $box1[2];
        $y2_1 = $box1['y2'] ?? $box1[3];
        
        $x1_2 = $box2['x1'] ?? $box2[0];
        $y1_2 = $box2['y1'] ?? $box2[1];
        $x2_2 = $box2['x2'] ?? $box2[2];
        $y2_2 = $box2['y2'] ?? $box2[3];
        
        // 计算交集坐标
        $x1_i = max($x1_1, $x1_2];
        $y1_i = max($y1_1, $y1_2];
        $x2_i = min($x2_1, $x2_2];
        $y2_i = min($y2_1, $y2_2];
        
        // 如果没有交集，返�?
        if ($x2_i <= $x1_i || $y2_i <= $y1_i) {
            return 0.0;
        }
        
        // 计算交集面积
        $intersection = ($x2_i - $x1_i) * ($y2_i - $y1_i];
        
        // 计算各自面积
        $area1 = ($x2_1 - $x1_1) * ($y2_1 - $y1_1];
        $area2 = ($x2_2 - $x1_2) * ($y2_2 - $y1_2];
        
        // 计算并集面积
        $union = $area1 + $area2 - $intersection;
        
        // 计算IoU
        return $intersection / $union;
    }
    
    /**
     * 计算平均精度(AP)
     *
     * @param array $detections 检测结�?     * @param array $groundTruth 真实标注
     * @return array AP相关指标
     */
    private function calculateAP(array $detections, array $groundTruth): array
    {
        // 如果没有标注，无法计算AP
        if (empty($groundTruth)) {
            return [
                'ap' => 0,
                'true_positives' => 0,
                'false_positives' => count($detections],
                'false_negatives' => 0,
                'precision' => empty($detections) ? 1.0 : 0.0,
                'recall' => 1.0
            ];
        }
        
        // 如果没有检测，返回0 AP
        if (empty($detections)) {
            return [
                'ap' => 0,
                'true_positives' => 0,
                'false_positives' => 0,
                'false_negatives' => count($groundTruth],
                'precision' => 1.0,
                'recall' => 0.0
            ];
        }
        
        // 按置信度排序
        usort($detections, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        }];
        
        $numGroundTruth = count($groundTruth];
        $truePositives = array_fill(0, count($detections], 0];
        $falsePositives = array_fill(0, count($detections], 0];
        
        // 标记已匹配的真实标注
        $gtMatched = array_fill(0, $numGroundTruth, false];
        
        // 对每个检�?        foreach ($detections as $i => $detection) {
            $maxIoU = 0;
            $maxIndex = -1;
            
            // 找到最佳匹配的真实标注
            foreach ($groundTruth as $j => $gt) {
                if ($gtMatched[$j]) {
                    continue;
                }
                
                $iou = $this->calculateIoU($detection['bbox'],  $gt['bbox']];
                
                if ($iou > $maxIoU) {
                    $maxIoU = $iou;
                    $maxIndex = $j;
                }
            }
            
            // 如果IoU大于阈值，标记为真阳�?            if ($maxIoU >= 0.5 && $maxIndex >= 0) {
                $truePositives[$i] = 1;
                $gtMatched[$maxIndex] = true;
            } else {
                $falsePositives[$i] = 1;
            }
        }
        
        // 计算累积TP和FP
        $cumulativeTP = array_fill(0, count($detections], 0];
        $cumulativeFP = array_fill(0, count($detections], 0];
        
        $cumulativeTP[0] = $truePositives[0];
        $cumulativeFP[0] = $falsePositives[0];
        
        for ($i = 1; $i < count($detections]; $i++) {
            $cumulativeTP[$i] = $cumulativeTP[$i - 1] + $truePositives[$i];
            $cumulativeFP[$i] = $cumulativeFP[$i - 1] + $falsePositives[$i];
        }
        
        // 计算精度和召回率
        $precision = [];
        $recall = [];
        
        for ($i = 0; $i < count($detections]; $i++) {
            $precision[$i] = $cumulativeTP[$i] / ($cumulativeTP[$i] + $cumulativeFP[$i]];
            $recall[$i] = $cumulativeTP[$i] / $numGroundTruth;
        }
        
        // 计算11点插值的AP
        $ap = 0;
        $recallLevels = [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0];
        
        foreach ($recallLevels as $r) {
            $maxPrecision = 0;
            
            for ($i = 0; $i < count($precision]; $i++) {
                if ($recall[$i] >= $r) {
                    $maxPrecision = max($maxPrecision, $precision[$i]];
                }
            }
            
            $ap += $maxPrecision / 11;
        }
        
        // 计算总体TP、FP和FN
        $totalTP = end($cumulativeTP];
        $totalFP = end($cumulativeFP];
        $totalFN = $numGroundTruth - $totalTP;
        
        // 计算最终的精度和召回率
        $finalPrecision = $totalTP / ($totalTP + $totalFP];
        $finalRecall = $totalTP / $numGroundTruth;
        
        return [
            'ap' => $ap,
            'true_positives' => $totalTP,
            'false_positives' => $totalFP,
            'false_negatives' => $totalFN,
            'precision' => $finalPrecision,
            'recall' => $finalRecall
        ];
    }
    
    /**
     * 根据类别名称检测物�?     *
     * @param mixed $image 图像数据
     * @param string $categoryName 类别名称
     * @param array $options 检测选项
     * @return array 检测结�?     */
    public function detectByCategory($image, string $categoryName, array $options = []): array
    {
        $result = $this->detect($image, $options];
        
        // 筛选指定类别的检测结�?        $filteredDetections = array_filter($result['detections'],  function($detection) use ($categoryName) {
            return $detection['name'] === $categoryName || $detection['label'] === $categoryName;
        }];
        
        $result['detections'] = array_values($filteredDetections];
        $result['count'] = count($filteredDetections];
        
        return $result;
    }
    
    /**
     * 根据超类别检测物�?     *
     * @param mixed $image 图像数据
     * @param string $supercategory 超类别名�?     * @param array $options 检测选项
     * @return array 检测结�?     */
    public function detectBySupercategory($image, string $supercategory, array $options = []): array
    {
        $result = $this->detect($image, $options];
        
        // 筛选指定超类别的检测结�?        $filteredDetections = array_filter($result['detections'],  function($detection) use ($supercategory) {
            return $detection['supercategory'] === $supercategory;
        }];
        
        $result['detections'] = array_values($filteredDetections];
        $result['count'] = count($filteredDetections];
        
        return $result;
    }
    
    /**
     * 检测人�?     * 
     * @param mixed $image 图像数据
     * @param array $options 检测选项
     * @return array 检测结�?     */
    public function detectPeople($image, array $options = []): array
    {
        return $this->detectByCategory($image, 'person', $options];
    }
    
    /**
     * 检测车�?     * 
     * @param mixed $image 图像数据
     * @param array $options 检测选项
     * @return array 检测结�?     */
    public function detectVehicles($image, array $options = []): array
    {
        return $this->detectBySupercategory($image, 'vehicle', $options];
    }
    
    /**
     * 检测动�?     *
     * @param mixed $image 图像数据
     * @param array $options 检测选项
     * @return array 检测结�?     */
    public function detectAnimals($image, array $options = []): array
    {
        return $this->detectBySupercategory($image, 'animal', $options];
    }
    
    /**
     * 获取支持的类�?     *
     * @return array 支持的类�?     */
    public function getSupportedCategories(): array
    {
        return $this->objectCategories;
    }
    
    /**
     * 获取配置信息
     * 
     * @return array 配置信息
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * 更新配置
     * 
     * @param array $config 新配�?     * @return void
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config];
        
        if ($this->logger) {
            $this->logger->info('更新物体检测模型配�?, [
                'new_config' => $config
            ]];
        }
    }
    
    /**
     * 获取支持的模型架�?     * 
     * @return array 支持的模型架�?     */
    public function getSupportedArchitectures(): array
    {
        return $this->supportedArchitectures;
    }
    
    /**
     * 启用物体跟踪
     * 
     * @param bool $enable 是否启用
     * @return void
     */
    public function enableTracking(bool $enable = true): void
    {
        $this->config['enable_tracking'] = $enable;
        
        if ($enable && !isset($this->trackingState)) {
            $this->trackingState = [
                'objects' => [], 
                'next_id' => 1,
                'last_timestamp' => time()
            ];
        }
        
        if ($this->logger) {
            $this->logger->info($enable ? '启用物体跟踪' : '禁用物体跟踪'];
        }
    }
    
    /**
     * 清理资源
     * 
     * @return void
     */
    public function cleanup(): void
    {
        // 清理模型和缓存资�?        $this->models = [];
        
        if (isset($this->trackingState)) {
            unset($this->trackingState];
        }
        
        if ($this->logger) {
            $this->logger->debug('物体检测模型资源已释放'];
        }
    }
    
    /**
     * 物体跟踪
     *
     * @param array $result 当前帧的检测结�?     * @param int $timestamp 当前时间�?     * @return array 带跟踪ID的检测结�?     */
    private function trackObjects(array $result, int $timestamp): array
    {
        if (!isset($this->trackingState)) {
            $this->trackingState = [
                'objects' => [], 
                'next_id' => 1,
                'last_timestamp' => $timestamp
            ];
        }
        
        $currentDetections = $result['detections'];
        $trackedObjects = $this->trackingState['objects'];
        $deltaTime = max(0.001, $timestamp - $this->trackingState['last_timestamp']]; // 防止除零
        
        // 计算检测与已跟踪物体的匹配
        $matches = [];
        $unmatched_detections = [];
        $unmatched_tracks = array_keys($trackedObjects];
        
        // 对每个当前检�?        foreach ($currentDetections as $detIndex => $detection) {
            $best_iou = 0;
            $best_track_idx = -1;
            
            // 查找最佳匹配的已跟踪物�?            foreach ($unmatched_tracks as $trackIndex) {
                $tracked = $trackedObjects[$trackIndex];
                
                // 检查类别是否匹�?                if ($tracked['category_id'] !== $detection['category_id']) {
                    continue;
                }
                
                // 如果跟踪物体已经匹配，跳�?                if (in_[$trackIndex, array_column($matches, 1))) {
                    continue;
                }
                
                // 计算IoU
                $iou = $this->calculateIoU($detection['bbox'],  $tracked['bbox']];
                
                // 如果IoU大于阈值且大于当前最佳IoU
                if ($iou > 0.3 && $iou > $best_iou) {
                    $best_iou = $iou;
                    $best_track_idx = $trackIndex;
                }
            }
            
            // 如果找到匹配
            if ($best_track_idx >= 0) {
                $matches[] = [$detIndex, $best_track_idx];
                
                // 从未匹配跟踪列表中移�?                $key = array_search($best_track_idx, $unmatched_tracks];
                if ($key !== false) {
                    unset($unmatched_tracks[$key]];
                }
            } else {
                $unmatched_detections[] = $detIndex;
            }
        }
        
        // 更新匹配的跟�?        foreach ($matches as $match) {
            [$det_idx, $track_idx] = $match;
            $detection = $currentDetections[$det_idx];
            $tracked = $trackedObjects[$track_idx];
            
            // 更新位置和速度
            $dx = $detection['bbox']['center_x'] - $tracked['bbox']['center_x'];
            $dy = $detection['bbox']['center_y'] - $tracked['bbox']['center_y'];
            $velocity_x = $dx / $deltaTime;
            $velocity_y = $dy / $deltaTime;
            
            // 更新跟踪状�?            $trackedObjects[$track_idx] = [
                'tracking_id' => $tracked['tracking_id'], 
                'category_id' => $detection['category_id'], 
                'name' => $detection['name'], 
                'label' => $detection['label'], 
                'confidence' => $detection['confidence'], 
                'bbox' => $detection['bbox'], 
                'velocity' => [
                    'x' => $velocity_x,
                    'y' => $velocity_y,
                    'magnitude' => sqrt($velocity_x * $velocity_x + $velocity_y * $velocity_y)
                ], 
                'age' => $tracked['age'] + 1,
                'time_since_update' => 0,
                'last_timestamp' => $timestamp
            ];
            
            // 添加跟踪ID到当前检�?            $currentDetections[$det_idx]['tracking_id'] = $tracked['tracking_id'];
            $currentDetections[$det_idx]['velocity'] = $trackedObjects[$track_idx]['velocity'];
            $currentDetections[$det_idx]['age'] = $trackedObjects[$track_idx]['age'];
        }
        
        // 对未匹配的检测创建新跟踪
        foreach ($unmatched_detections as $det_idx) {
            $detection = $currentDetections[$det_idx];
            $track_id = $this->trackingState['next_id']++;
            
            $trackedObjects[$track_id] = [
                'tracking_id' => $track_id,
                'category_id' => $detection['category_id'], 
                'name' => $detection['name'], 
                'label' => $detection['label'], 
                'confidence' => $detection['confidence'], 
                'bbox' => $detection['bbox'], 
                'velocity' => [
                    'x' => 0,
                    'y' => 0,
                    'magnitude' => 0
                ], 
                'age' => 1,
                'time_since_update' => 0,
                'last_timestamp' => $timestamp
            ];
            
            // 添加跟踪ID到当前检�?            $currentDetections[$det_idx]['tracking_id'] = $track_id;
            $currentDetections[$det_idx]['velocity'] = $trackedObjects[$track_id]['velocity'];
            $currentDetections[$det_idx]['age'] = 1;
        }
        
        // 更新未匹配的跟踪
        foreach ($unmatched_tracks as $track_idx) {
            $trackedObjects[$track_idx]['time_since_update']++;
            
            // 如果跟踪丢失太久，移除它
            if ($trackedObjects[$track_idx]['time_since_update'] > 10) {
                unset($trackedObjects[$track_idx]];
            }
        }
        
        // 更新跟踪状�?        $this->trackingState['objects'] = $trackedObjects;
        $this->trackingState['last_timestamp'] = $timestamp;
        
        // 更新结果
        $result['detections'] = $currentDetections;
        $result['tracking_info'] = [
            'tracked_objects_count' => count($trackedObjects],
            'new_tracks' => count($unmatched_detections],
            'matched_tracks' => count($matches],
            'lost_tracks' => count($unmatched_tracks)
        ];
        
        return $result;
    }
}

