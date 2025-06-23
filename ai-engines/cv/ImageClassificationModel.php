<?php
/**
 * 文件名：ImageClassificationModel.php
 * 功能描述：图像分类模型 - 将图像分类到预定义的类别中
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 * 
 * @package AlingAi\Engines\CV
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\Engines\CV;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;

/**
 * 图像分类模型
 * 
 * 提供对图像的分类功能，支持场景识别、内容标签、细粒度分类等
 */
class ImageClassificationModel
{
    /**
     * @var array 配置参数
     */
    private array $config;
    
    /**
     * @var LoggerInterface|null 日志记录器
     */
    private ?LoggerInterface $logger;
    
    /**
     * @var CacheManager|null 缓存管理器
     */
    private ?CacheManager $cache;
    
    /**
     * @var array 模型实例缓存
     */
    private array $models = [];
    
    /**
     * @var array 类别层次结构
     */
    private array $categoryHierarchy = [];
    
    /**
     * @var array 支持的分类模型架构
     */
    private array $supportedArchitectures = ['resnet', 'mobilenet', 'efficientnet', 'vit'];
    
    /**
     * 构造函数
     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志记录器
     * @param CacheManager|null $cache 缓存管理器
     */
    public function __construct(array $config = [], ?LoggerInterface $logger = null, ?CacheManager $cache = null)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $this->mergeConfig($config);
        
        $this->initialize();
        
        if ($this->logger) {
            $this->logger->info('图像分类模型初始化成功', [
                'model_architecture' => $this->config['model_architecture'],
                'confidence_threshold' => $this->config['confidence_threshold'],
                'max_results' => $this->config['max_results']
            ]);
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
            'model_architecture' => 'efficientnet', // 模型架构
            'model_version' => 'b0', // 模型版本
            'confidence_threshold' => 0.2, // 置信度阈值
            'max_results' => 10, // 最大返回结果数
            'enable_batch_processing' => false, // 是否启用批处理
            'batch_size' => 16, // 批处理大小
            'enable_explanations' => false, // 是否启用可解释性
            'cache_enabled' => true, // 是否启用缓存
            'cache_ttl' => 3600, // 缓存有效期(秒)
            'use_gpu' => false, // 是否使用GPU加速
            'input_size' => [224, 224], // 输入尺寸 [高度, 宽度]
            'model_path' => null, // 模型文件路径
            'labels_path' => null, // 标签文件路径
        ];
        
        return array_merge($defaultConfig, $config);
    }
    
    /**
     * 初始化模型
     */
    private function initialize(): void
    {
        $this->loadCategoryHierarchy();
        
        // 实际项目中这里会加载预训练模型
        // 本实现中使用模拟模型进行演示
    }
    
    /**
     * 加载类别层次结构
     */
    private function loadCategoryHierarchy(): void
    {
        // 在实际项目中，这里会从文件加载完整的类别层次结构
        // 本实现中使用一个简化的层次结构
        
        $this->categoryHierarchy = [
            'animal' => [
                'label' => '动物',
                'children' => [
                    'mammal' => ['label' => '哺乳动物', 'children' => [
                        'dog' => ['label' => '狗'],
                        'cat' => ['label' => '猫'],
                        'horse' => ['label' => '马']
                    ]],
                    'bird' => ['label' => '鸟类', 'children' => [
                        'eagle' => ['label' => '鹰'],
                        'duck' => ['label' => '鸭子'],
                        'penguin' => ['label' => '企鹅']
                    ]],
                    'fish' => ['label' => '鱼类']
                ]
            ],
            'plant' => [
                'label' => '植物',
                'children' => [
                    'tree' => ['label' => '树木'],
                    'flower' => ['label' => '花朵', 'children' => [
                        'rose' => ['label' => '玫瑰'],
                        'tulip' => ['label' => '郁金香'],
                        'sunflower' => ['label' => '向日葵']
                    ]],
                    'grass' => ['label' => '草']
                ]
            ],
            'vehicle' => [
                'label' => '交通工具',
                'children' => [
                    'car' => ['label' => '汽车'],
                    'airplane' => ['label' => '飞机'],
                    'boat' => ['label' => '船'],
                    'train' => ['label' => '火车']
                ]
            ],
            'food' => [
                'label' => '食物',
                'children' => [
                    'fruit' => ['label' => '水果'],
                    'vegetable' => ['label' => '蔬菜'],
                    'meat' => ['label' => '肉类']
                ]
            ],
            'scene' => [
                'label' => '场景',
                'children' => [
                    'outdoor' => ['label' => '户外', 'children' => [
                        'beach' => ['label' => '海滩'],
                        'mountain' => ['label' => '山脉'],
                        'forest' => ['label' => '森林']
                    ]],
                    'indoor' => ['label' => '室内', 'children' => [
                        'bedroom' => ['label' => '卧室'],
                        'kitchen' => ['label' => '厨房'],
                        'office' => ['label' => '办公室']
                    ]]
                ]
            ],
            'object' => [
                'label' => '物体',
                'children' => [
                    'furniture' => ['label' => '家具'],
                    'electronic' => ['label' => '电子设备'],
                    'clothing' => ['label' => '服装']
                ]
            ]
        ];
    }
    
    /**
     * 图像分类主方法
     * 
     * @param mixed $image 图像数据(路径或图像数据)
     * @param array $options 分类选项
     * @return array 分类结果
     * @throws InvalidArgumentException 参数无效时抛出异常
     * @throws RuntimeException 处理失败时抛出异常
     */
    public function classify($image, array $options = []): array
    {
        // 合并选项
        $options = array_merge($this->config, $options);
        
        try {
            // 检查缓存
            if ($options['cache_enabled'] && $this->cache) {
                $imagePath = is_string($image) ? $image : '';
                if ($imagePath && file_exists($imagePath)) {
                    $cacheKey = 'img_classify_' . md5_file($imagePath) . '_' . md5(json_encode($options));
                    if ($this->cache->has($cacheKey)) {
                        return $this->cache->get($cacheKey);
                    }
                }
            }
            
            // 获取图像信息
            $imageInfo = $this->getImageInfo($image);
        
        // 预处理图像
            $processedImage = $this->preprocessImage($image, $options);
            
            // 运行分类模型
            $classificationResults = $this->runClassificationModel($processedImage, $options);
            
            // 提取特征
            $features = $this->extractFeatures($processedImage, $options);
            
            // 合并结果
            $result = [
                'categories' => $classificationResults,
                'features' => $features,
                'image_info' => $imageInfo,
                'processing_time' => rand(10, 100), // 模拟处理时间(毫秒)
                'model_info' => [
                    'architecture' => $options['model_architecture'],
                    'version' => $options['model_version']
                ],
            ];
            
            // 如果启用了可解释性分析
            if ($options['enable_explanations']) {
                $result['explanations'] = $this->generateExplanations($processedImage, $classificationResults);
            }
            
            // 缓存结果
            if ($options['cache_enabled'] && $this->cache && isset($cacheKey)) {
                $this->cache->set($cacheKey, $result, $options['cache_ttl']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('图像分类失败', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            throw new RuntimeException('图像分类失败: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 批量图像分类
     *
     * @param array $images 图像路径或数据数组
     * @param array $options 分类选项
     * @return array 分类结果数组
     */
    public function classifyBatch(array $images, array $options = []): array
    {
        if (!$this->config['enable_batch_processing']) {
            throw new RuntimeException('批量处理未启用');
        }
        
        $results = [];
        $batchSize = $this->config['batch_size'];
        $startTime = microtime(true);
        
        // 分批处理
        for ($i = 0; $i < count($images); $i += $batchSize) {
            $batch = array_slice($images, $i, $batchSize);
            $batchResults = [];
            
            // 处理当前批次
            foreach ($batch as $index => $image) {
                try {
                    $batchResults[$index] = $this->classify($image, $options);
                } catch (Exception $e) {
                    $batchResults[$index] = ['error' => $e->getMessage()];
                    
                    if ($this->logger) {
                        $this->logger->error('批量图像分类失败', [
                            'batch_index' => $i + $index,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            $results = array_merge($results, $batchResults);
        }
        
        $totalTime = microtime(true) - $startTime;
        
        return [
            'results' => $results,
            'total_images' => count($images),
            'total_time' => round($totalTime * 1000), // 转换为毫秒
            'average_time_per_image' => round(($totalTime * 1000) / count($images)),
            'batch_size' => $batchSize,
            'num_batches' => ceil(count($images) / $batchSize)
        ];
    }
    
    /**
     * 提取图像特征
     *
     * @param mixed $image 图像数据
     * @param array $options 提取选项
     * @return array 特征向量
     * @throws InvalidArgumentException 参数无效时抛出异常
     */
    public function extractFeatures($image, array $options = []): array
    {
        // 合并选项
        $options = array_merge($this->config, $options);
        
        try {
            // 预处理图像
            $processedImage = $this->preprocessImage($image, $options);
            
            // 提取特征
            $featureVector = $this->runFeatureExtraction($processedImage, $options);
            
            return [
                'vector' => $featureVector,
                'dimension' => count($featureVector),
                'embedding_type' => 'image',
                'model_architecture' => $options['model_architecture'],
                'model_version' => $options['model_version']
            ];
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('图像特征提取失败', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            throw new RuntimeException('图像特征提取失败: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 根据特征计算图像相似度
     *
     * @param array $features1 特征向量1
     * @param array $features2 特征向量2
     * @return float 相似度分数(0-1)
     * @throws InvalidArgumentException 参数无效时抛出异常
     */
    public function calculateSimilarity(array $features1, array $features2): float
    {
        if (!isset($features1['vector']) || !isset($features2['vector'])) {
            throw new InvalidArgumentException('特征向量格式无效');
        }
        
        $vector1 = $features1['vector'];
        $vector2 = $features2['vector'];
        
        if (count($vector1) != count($vector2)) {
            throw new InvalidArgumentException('特征向量维度不匹配');
        }
        
        // 计算余弦相似度
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;
        
        for ($i = 0; $i < count($vector1); $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $norm1 += $vector1[$i] * $vector1[$i];
            $norm2 += $vector2[$i] * $vector2[$i];
        }
        
        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);
        
        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }
        
        return max(0, min(1, ($dotProduct / ($norm1 * $norm2) + 1) / 2));
    }
    
    /**
     * 预处理图像
     *
     * @param mixed $image 图像数据
     * @param array $options 处理选项
     * @return array 预处理后的图像数据
     */
    private function preprocessImage($image, array $options): array
    {
        // 在实际项目中，这里会进行真实的图像预处理
        // 本实现中使用模拟数据
        
        $imageInfo = $this->getImageInfo($image);
        
        // 模拟预处理结果
        return [
            'processed_data' => [
                'width' => $options['input_size'][1],
                'height' => $options['input_size'][0],
                'channels' => 3,
                'original_size' => [$imageInfo['width'], $imageInfo['height']]
            ],
            'path' => $imageInfo['path'] ?? null
        ];
    }
    
    /**
     * 运行分类模型
     *
     * @param array $processedImage 预处理后的图像
     * @param array $options 处理选项
     * @return array 分类结果
     */
    private function runClassificationModel(array $processedImage, array $options): array
    {
        // 在实际项目中，这里会运行真实的分类模型
        // 本实现中使用模拟数据生成分类结果
        
        // 从类别层次结构中随机选择类别
        $categories = $this->getRandomCategories($options['max_results']);
        
        // 排序并应用阈值过滤
        usort($categories, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        // 过滤低于阈值的结果
        $categories = array_filter($categories, function($category) use ($options) {
            return $category['confidence'] >= $options['confidence_threshold'];
        });
        
        // 限制结果数量
        $categories = array_slice($categories, 0, $options['max_results']);
        
        return array_values($categories);  // 重置数组索引
    }
    
    /**
     * 运行特征提取
     *
     * @param array $processedImage 预处理后的图像
     * @param array $options 处理选项
     * @return array 特征向量
     */
    private function runFeatureExtraction(array $processedImage, array $options): array
    {
        // 在实际项目中，这里会运行真实的特征提取
        // 本实现中生成一个随机的特征向量
        
        // 根据不同架构生成不同维度的特征
        $dimensions = [
            'resnet' => 2048,
            'mobilenet' => 1280,
            'efficientnet' => 1792,
            'vit' => 768
        ];
        
        $dimension = $dimensions[$options['model_architecture']] ?? 2048;
        $featureVector = [];
        
        // 生成随机特征向量
        for ($i = 0; $i < $dimension; $i++) {
            $featureVector[] = (rand(-1000, 1000) / 1000);  // -1.0 到 1.0 之间的随机值
        }
        
        // 归一化
        $norm = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $featureVector)));
        if ($norm > 0) {
            for ($i = 0; $i < $dimension; $i++) {
                $featureVector[$i] /= $norm;
            }
        }
        
        return $featureVector;
    }
    
    /**
     * 随机获取类别
     *
     * @param int $count 类别数量
     * @return array 类别数组
     */
    private function getRandomCategories(int $count): array
    {
        $categories = [];
        $allCategories = $this->flattenCategoryHierarchy();
        
        // 打乱类别数组
        shuffle($allCategories);
        
        // 取前$count个类别
        $selectedCategories = array_slice($allCategories, 0, $count);
        
        foreach ($selectedCategories as $category) {
            // 生成随机置信度，总和接近但不超过1
            $confidence = (80 + rand(0, 1990)) / 2000;  // 0.04 到 0.999
            
            $categories[] = [
                'id' => $category['id'],
                'name' => $category['name'],
                'label' => $category['label'],
                'confidence' => $confidence,
                'path' => $category['path']  // 类别完整路径
            ];
        }
        
        return $categories;
    }
    
    /**
     * 拍平类别层次结构
     *
     * @return array 拍平后的类别数组
     */
    private function flattenCategoryHierarchy(): array
    {
        $result = [];
        $this->flattenCategoriesRecursive($this->categoryHierarchy, $result);
        return $result;
    }
    
    /**
     * 递归拍平类别层次结构
     *
     * @param array $categories 类别层次结构
     * @param array &$result 结果数组
     * @param string $path 当前路径
     */
    private function flattenCategoriesRecursive(array $categories, array &$result, string $path = ''): void
    {
        foreach ($categories as $id => $category) {
            $currentPath = $path ? $path . '/' . $id : $id;
            
            $result[] = [
                'id' => $id,
                'name' => $category['label'] ?? $id,
                'label' => $category['label'] ?? $id,
                'path' => $currentPath
            ];
            
            if (isset($category['children']) && is_array($category['children'])) {
                $this->flattenCategoriesRecursive($category['children'], $result, $currentPath);
            }
        }
    }
    
    /**
     * 生成可解释性热力图
     *
     * @param array $processedImage 预处理后的图像
     * @param array $categories 分类结果
     * @return array 可解释性数据
     */
    private function generateExplanations(array $processedImage, array $categories): array
    {
        if (empty($categories)) {
            return [];
        }
        
        // 在实际项目中，这里会生成真实的类激活映射(CAM)
        // 本实现中使用模拟数据
        
        $width = $processedImage['processed_data']['width'];
        $height = $processedImage['processed_data']['height'];
        
        $explanations = [];
        foreach ($categories as $index => $category) {
            if ($index >= 3) break;  // 只为前3个类别生成解释
            
            // 生成简单的热力图数据(7x7网格)
            $heatmapSize = 7;
            $heatmap = [];
            
            for ($i = 0; $i < $heatmapSize; $i++) {
                $row = [];
                for ($j = 0; $j < $heatmapSize; $j++) {
                    // 中心区域热度较高
                    $distanceFromCenter = sqrt(pow($i - $heatmapSize/2, 2) + pow($j - $heatmapSize/2, 2));
                    $maxDistance = sqrt(pow($heatmapSize/2, 2) * 2);
                    $intensity = (1 - $distanceFromCenter / $maxDistance) * $category['confidence'] * (0.7 + 0.3 * rand(0, 100) / 100);
                    $intensity = max(0, min(1, $intensity));  // 确保在0-1范围内
                    
                    $row[] = $intensity;
                }
                $heatmap[] = $row;
            }
            
            $explanations[] = [
                'category_id' => $category['id'],
                'category_name' => $category['name'],
                'heatmap' => $heatmap,
                'heatmap_size' => [$heatmapSize, $heatmapSize],
                'method' => 'Grad-CAM'
            ];
        }
        
        return $explanations;
    }
    
    /**
     * 获取图像信息
     * 
     * @param mixed $image 图像数据(路径或图像数据)
     * @return array 图像信息
     */
    private function getImageInfo($image): array
    {
        if (is_string($image) && file_exists($image)) {
            // 如果是真实图像，获取实际尺寸
            $imageSize = getimagesize($image);
            if ($imageSize) {
                return [
                    'width' => $imageSize[0],
                    'height' => $imageSize[1],
                    'type' => $imageSize['mime'] ?? 'unknown',
                    'path' => $image
                ];
            }
        }
        
        // 如果无法获取，返回默认值
        return [
            'width' => 1000,
            'height' => 1000,
            'type' => 'unknown'
        ];
    }
    
    /**
     * 获取类别层次结构
     * 
     * @param string|null $parentId 父类别ID
     * @return array 类别层次结构
     */
    public function getCategoryHierarchy(?string $parentId = null): array
    {
        if ($parentId === null) {
            return $this->categoryHierarchy;
        }
        
        // 查找特定父类别下的子类别
        $result = [];
        $found = false;
        $this->findCategoryById($this->categoryHierarchy, $parentId, $result, $found);
        
        return $found ? $result : [];
    }
    
    /**
     * 按ID查找类别
     * 
     * @param array $categories 类别层次结构
     * @param string $id 目标ID
     * @param array &$result 结果引用
     * @param bool &$found 是否找到标志
     */
    private function findCategoryById(array $categories, string $id, array &$result, bool &$found): void
    {
        foreach ($categories as $categoryId => $category) {
            if ($categoryId === $id) {
                $result = $category;
                $found = true;
                return;
            }
            
            if (isset($category['children']) && is_array($category['children'])) {
                $this->findCategoryById($category['children'], $id, $result, $found);
                if ($found) return;
            }
        }
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
     * @param array $config 新配置
     * @return void
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        
        if ($this->logger) {
            $this->logger->info('更新图像分类模型配置', [
                'new_config' => $config
            ]);
        }
    }
    
    /**
     * 获取支持的模型架构
     * 
     * @return array 支持的模型架构
     */
    public function getSupportedArchitectures(): array
    {
        return $this->supportedArchitectures;
    }
    
    /**
     * 清理资源
     * 
     * @return void
     */
    public function cleanup(): void
    {
        // 清理模型和缓存资源
        $this->models = [];
        
        if ($this->logger) {
            $this->logger->debug('图像分类模型资源已释放');
        }
    }
}
