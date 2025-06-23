<?php
/**
 * 文件名：object-detection-demo.php
 * 功能描述：物体检测模型演示程序
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Examples
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

// 引入自动加载器
require_once __DIR__ . '/../vendor/autoload.php';

// 引入配置
$config = require_once __DIR__ . '/../config/config.php';

use AlingAi\Engines\CV\ObjectDetectionModel;
use AlingAi\Core\Logger\SimpleLogger;
use AlingAi\Utils\CacheManager;

// 创建缓存管理器
$cache = new CacheManager([
    'driver' => 'file',
    'path' => __DIR__ . '/../storage/cache',
    'ttl' => 3600
]);

// 创建日志记录器
$logger = new SimpleLogger([
    'log_level' => 'info',
    'log_path' => __DIR__ . '/../storage/logs',
    'log_file' => 'object-detection.log'
]);

/**
 * 格式化输出函数
 *
 * @param mixed $data 要输出的数据
 * @param string $title 标题
 * @return void
 */
function displayOutput($data, string $title = ''): void
{
    echo PHP_EOL . "\033[1;36m" . str_repeat('=', 80) . "\033[0m" . PHP_EOL;
    
    if (!empty($title)) {
        echo "\033[1;33m" . $title . "\033[0m" . PHP_EOL;
        echo "\033[1;36m" . str_repeat('-', 80) . "\033[0m" . PHP_EOL;
    }
    
    if (is_array($data) || is_object($data)) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        echo $data . PHP_EOL;
    }
    
    echo "\033[1;36m" . str_repeat('=', 80) . "\033[0m" . PHP_EOL;
}

/**
 * 可视化检测结果（ASCII艺术风格）
 *
 * @param array $detections 检测结果
 * @param array $imageInfo 图像信息
 * @return void
 */
function visualizeDetections(array $detections, array $imageInfo): void
{
    $width = $imageInfo['width'] ?? 100;
    $height = $imageInfo['height'] ?? 100;
    
    // 缩放到适合终端显示的大小
    $scale = min(60 / $width, 30 / $height);
    $displayWidth = (int)($width * $scale);
    $displayHeight = (int)($height * $scale);
    
    // 创建空白画布
    $canvas = array_fill(0, $displayHeight, array_fill(0, $displayWidth, ' '));
    
    // 绘制边界
    for ($i = 0; $i < $displayWidth; $i++) {
        $canvas[0][$i] = '-';
        $canvas[$displayHeight - 1][$i] = '-';
    }
    
    for ($i = 0; $i < $displayHeight; $i++) {
        $canvas[$i][0] = '|';
        $canvas[$i][$displayWidth - 1] = '|';
    }
    
    // 绘制检测框
    foreach ($detections as $detection) {
        if (!isset($detection['bbox'])) continue;
        
        $bbox = $detection['bbox'];
        $x1 = (int)(($bbox['x1'] ?? $bbox[0]) * $scale);
        $y1 = (int)(($bbox['y1'] ?? $bbox[1]) * $scale);
        $x2 = (int)(($bbox['x2'] ?? $bbox[2]) * $scale);
        $y2 = (int)(($bbox['y2'] ?? $bbox[3]) * $scale);
        
        // 确保坐标在范围内
        $x1 = max(0, min($displayWidth - 1, $x1));
        $y1 = max(0, min($displayHeight - 1, $y1));
        $x2 = max(0, min($displayWidth - 1, $x2));
        $y2 = max(0, min($displayHeight - 1, $y2));
        
        // 绘制边界框
        for ($i = $x1; $i <= $x2; $i++) {
            $canvas[$y1][$i] = '*';
            $canvas[$y2][$i] = '*';
        }
        
        for ($i = $y1; $i <= $y2; $i++) {
            $canvas[$i][$x1] = '*';
            $canvas[$i][$x2] = '*';
        }
        
        // 添加类别标签
        $label = substr($detection['label'] ?? $detection['name'] ?? 'obj', 0, 8);
        $labelLen = mb_strlen($label);
        for ($i = 0; $i < $labelLen && $x1 + $i < $displayWidth - 1; $i++) {
            $canvas[$y1][$x1 + $i] = mb_substr($label, $i, 1);
        }
    }
    
    // 显示画布
    echo "\033[1;33m物体检测可视化（ASCII）:\033[0m" . PHP_EOL;
    for ($i = 0; $i < $displayHeight; $i++) {
        echo implode('', $canvas[$i]) . PHP_EOL;
    }
}

// 演示步骤
echo "\033[1;32m物体检测模型演示\033[0m" . PHP_EOL;
echo PHP_EOL . "初始化物体检测模型..." . PHP_EOL;

try {
    // 1. 创建物体检测模型
    $detector = new ObjectDetectionModel([
        'model_architecture' => 'yolo',
        'model_version' => 'v5',
        'confidence_threshold' => 0.4,
        'iou_threshold' => 0.5,
    ], $logger, $cache);
    
    echo "物体检测模型初始化成功！" . PHP_EOL;
    
    // 2. 显示当前配置
    displayOutput($detector->getConfig(), "物体检测模型当前配置");
    
    // 假设这里是我们的示例图像路径
    $sampleImages = [
        'street' => __DIR__ . '/../resources/samples/street.jpg',
        'living_room' => __DIR__ . '/../resources/samples/living_room.jpg',
        'crowd' => __DIR__ . '/../resources/samples/crowd.jpg',
        'traffic' => __DIR__ . '/../resources/samples/traffic.jpg',
        'animals' => __DIR__ . '/../resources/samples/animals.jpg'
    ];
    
    // 检查示例图像是否存在
    $sampleImageExists = false;
    $availableImage = '';
    foreach ($sampleImages as $key => $path) {
        if (file_exists($path)) {
            $sampleImageExists = true;
            $availableImage = $path;
            echo "找到示例图像: $key ($path)" . PHP_EOL;
            break;
        }
    }
    
    // 如果没有找到图像，使用示例路径进行演示
    if (!$sampleImageExists) {
        $availableImage = $sampleImages['street'];
        echo "警告: 示例图像不存在，将使用模拟数据进行演示" . PHP_EOL;
    }
    
    // 3. 执行单图像物体检测
    echo PHP_EOL . "执行物体检测..." . PHP_EOL;
    $result = $detector->detect($availableImage);
    
    // 显示检测结果
    displayOutput($result, "物体检测结果");
    
    // 可视化检测结果
    if (isset($result['detections']) && !empty($result['detections'])) {
        visualizeDetections($result['detections'], $result['image_info'] ?? ['width' => 640, 'height' => 480]);
    }
    
    // 4. 测试特定类别的检测
    echo PHP_EOL . "检测特定类别的物体（人）..." . PHP_EOL;
    $personDetections = array_filter($result['detections'] ?? [], function($detection) {
        return ($detection['name'] ?? '') === 'person' || ($detection['label'] ?? '') === '人';
    });
    displayOutput($personDetections, "人物检测结果");
    
    // 5. 使用不同的配置选项
    echo PHP_EOL . "使用低置信度阈值进行检测..." . PHP_EOL;
    $lowConfidenceResult = $detector->detect($availableImage, [
        'confidence_threshold' => 0.1
    ]);
    
    // 比较检测数量
    $originalCount = count($result['detections'] ?? []);
    $lowThresholdCount = count($lowConfidenceResult['detections'] ?? []);
    displayOutput([
        'original_threshold' => $detector->getConfig()['confidence_threshold'],
        'low_threshold' => 0.1,
        'original_detection_count' => $originalCount,
        'low_threshold_detection_count' => $lowThresholdCount,
        'difference' => $lowThresholdCount - $originalCount
    ], "置信度阈值对比结果");
    
    // 6. 启用像素分割
    echo PHP_EOL . "启用像素分割进行检测..." . PHP_EOL;
    $maskResult = $detector->detect($availableImage, [
        'enable_mask' => true
    ]);
    
    // 仅显示前三个结果的掩码简要信息
    $maskSummary = [];
    if (isset($maskResult['detections'])) {
        foreach (array_slice($maskResult['detections'], 0, 3) as $detection) {
            if (isset($detection['mask'])) {
                $maskShape = is_array($detection['mask']) ? count($detection['mask']) . 'x' . count($detection['mask'][0] ?? []) : 'N/A';
                $maskSummary[] = [
                    'label' => $detection['label'] ?? $detection['name'] ?? 'unknown',
                    'confidence' => $detection['confidence'] ?? 0,
                    'mask_shape' => $maskShape,
                    'mask_type' => gettype($detection['mask'])
                ];
            }
        }
    }
    displayOutput($maskSummary, "像素分割结果摘要");
    
    // 7. 批量检测
    echo PHP_EOL . "启用批处理并检测多个图像..." . PHP_EOL;
    
    // 更新配置启用批处理
    $detector = new ObjectDetectionModel([
        'enable_batch_processing' => true,
        'batch_size' => 2
    ], $logger, $cache);
    
    // 准备多个图像进行批量处理
    $batchImages = [];
    foreach ($sampleImages as $path) {
        if (file_exists($path)) {
            $batchImages[] = $path;
        }
    }
    
    // 如果找不到实际图像，创建模拟图像路径
    if (empty($batchImages)) {
        $batchImages = array_values($sampleImages);
        echo "警告: 批处理将使用模拟数据" . PHP_EOL;
    }
    
    // 执行批量处理(最多3个图像)
    $batchResults = $detector->detectBatch(array_slice($batchImages, 0, 3));
    
    // 显示批处理统计信息
    $batchSummary = [
        'total_images' => $batchResults['total_images'],
        'total_time' => $batchResults['total_time'] . ' ms',
        'average_time_per_image' => $batchResults['average_time_per_image'] . ' ms',
        'batch_size' => $batchResults['batch_size'],
        'num_batches' => $batchResults['num_batches']
    ];
    
    // 为每个图像显示检测到的物体数量
    if (isset($batchResults['results']) && is_array($batchResults['results'])) {
        $imageSummaries = [];
        foreach ($batchResults['results'] as $index => $result) {
            $objectCounts = [];
            if (isset($result['detections'])) {
                foreach ($result['detections'] as $detection) {
                    $label = $detection['label'] ?? $detection['name'] ?? 'unknown';
                    $objectCounts[$label] = ($objectCounts[$label] ?? 0) + 1;
                }
            }
            
            $imageSummaries[] = [
                'image_index' => $index,
                'total_objects' => count($result['detections'] ?? []),
                'object_counts' => $objectCounts
            ];
        }
        $batchSummary['image_summaries'] = $imageSummaries;
    }
    
    displayOutput($batchSummary, "批量物体检测结果统计");
    
    // 8. 模型评估示例
    echo PHP_EOL . "模型评估示例..." . PHP_EOL;
    
    // 创建模拟的groundtruth数据
    $groundTruth = [];
    if (isset($result['detections'])) {
        // 使用我们的检测结果作为"真实"标注，但略微修改
        foreach ($result['detections'] as $index => $detection) {
            if ($index % 4 == 0) continue; // 模拟部分误差，删除一些物体
            
            $gt = $detection;
            // 略微修改边界框，模拟标注误差
            if (isset($gt['bbox'])) {
                $bbox = $gt['bbox'];
                if (is_array($bbox)) {
                    if (isset($bbox['x1'])) {
                        $gt['bbox']['x1'] += rand(-5, 5);
                        $gt['bbox']['y1'] += rand(-5, 5);
                        $gt['bbox']['x2'] += rand(-5, 5);
                        $gt['bbox']['y2'] += rand(-5, 5);
                    } else {
                        $gt['bbox'][0] += rand(-5, 5) / 100;
                        $gt['bbox'][1] += rand(-5, 5) / 100;
                        $gt['bbox'][2] += rand(-5, 5) / 100;
                        $gt['bbox'][3] += rand(-5, 5) / 100;
                    }
                }
            }
            $groundTruth[] = $gt;
        }
        
        // 添加一个额外的"真实"物体，模拟漏检
        $groundTruth[] = [
            'category_id' => 1,
            'name' => 'person',
            'label' => '人',
            'confidence' => 1.0,
            'bbox' => [0.1, 0.1, 0.2, 0.3]
        ];
    }
    
    // 评估模型性能
    $metrics = $detector->evaluateDetection($result['detections'] ?? [], $groundTruth);
    displayOutput($metrics, "模型评估指标");
    
    // 9. 清理资源
    echo PHP_EOL . "资源已清理" . PHP_EOL;
    
    echo PHP_EOL . "\033[1;32m演示完成！\033[0m" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "\033[1;31m错误: " . $e->getMessage() . "\033[0m" . PHP_EOL;
    echo "堆栈跟踪: " . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
} 