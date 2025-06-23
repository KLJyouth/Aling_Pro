<?php
/**
 * 文件名：cv-demo.php
 * 功能描述：计算机视觉模块演示程序
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 */

declare(strict_types=1);

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use AlingAi\Engines\CV\ComputerVisionAPI;
use AlingAi\Core\Logger\FileLogger;
use AlingAi\Utils\CacheManager;

// 创建日志记录器
$logger = new FileLogger([
    'log_path' => __DIR__ . '/../logs/cv-demo.log',
    'log_level' => 'debug',
    'max_file_size' => 10 * 1024 * 1024, // 10MB
]);

// 创建缓存管理器
$cache = new CacheManager([
    'cache_path' => __DIR__ . '/../cache/cv',
    'ttl' => 3600, // 默认缓存1小时
]);

// 创建计算机视觉API
try {
    $cvAPI = new ComputerVisionAPI([
        'api_version' => '1.0.0',
        'confidence_threshold' => 0.6,
        'max_detections' => 30,
        'use_gpu' => false
    ], $logger, $cache);

    echo "计算机视觉API初始化成功！\n";
    echo "API版本: {$cvAPI->getConfig()['api_version']}\n";
    echo "支持的图像格式: " . implode(', ', $cvAPI->getSupportedFormats()) . "\n";
    echo "==================================================\n\n";
} catch (Exception $e) {
    echo "初始化失败: " . $e->getMessage() . "\n";
    exit(1);
}

// 测试图像路径
$testImagePath = __DIR__ . '/images/test.jpg';
$faceImagePath = __DIR__ . '/images/face.jpg';
$textImagePath = __DIR__ . '/images/text.jpg';
$image1Path = __DIR__ . '/images/compare1.jpg';
$image2Path = __DIR__ . '/images/compare2.jpg';

// 确保测试图像目录存在
if (!file_exists(__DIR__ . '/images')) {
    mkdir(__DIR__ . '/images', 0755, true);
}

// 如果测试图像不存在，创建测试图像
if (!file_exists($testImagePath)) {
    createSampleImage($testImagePath, 640, 480);
    echo "创建测试图像: $testImagePath\n";
}

if (!file_exists($faceImagePath)) {
    createSampleImage($faceImagePath, 400, 400);
    echo "创建测试图像: $faceImagePath\n";
}

if (!file_exists($textImagePath)) {
    createSampleImage($textImagePath, 800, 600);
    echo "创建测试图像: $textImagePath\n";
}

if (!file_exists($image1Path)) {
    createSampleImage($image1Path, 500, 500);
    echo "创建测试图像: $image1Path\n";
}

if (!file_exists($image2Path)) {
    createSampleImage($image2Path, 500, 500);
    echo "创建测试图像: $image2Path\n";
}

echo "==================================================\n\n";

// 测试1: 图像分析（综合功能）
try {
    echo "测试1: 图像分析 - $testImagePath\n";
    $result = $cvAPI->analyzeImage($testImagePath);
    
    echo "分析结果:\n";
    echo " - 检测到对象数量: " . count($result['objects']) . "\n";
    echo " - 主要场景: {$result['scene']}\n";
    echo " - 主要颜色: " . implode(', ', array_slice($result['colors'], 0, 3)) . "\n";
    echo " - 图像质量评分: {$result['quality_score']}\n";
    echo " - 处理时间: {$result['processing_time']}ms\n";
    echo "==================================================\n\n";
} catch (Exception $e) {
    echo "图像分析失败: " . $e->getMessage() . "\n";
}

// 测试2: 物体检测
try {
    echo "测试2: 物体检测 - $testImagePath\n";
    $result = $cvAPI->detectObjects($testImagePath);
    
    echo "检测结果:\n";
    echo " - 检测到物体数量: " . count($result['objects']) . "\n";
    
    // 显示前5个检测结果
    $count = 0;
    foreach ($result['objects'] as $object) {
        echo " - {$object['label']} (置信度: " . round($object['confidence'] * 100, 2) . "%)\n";
        echo "   位置: x={$object['bbox']['x']}, y={$object['bbox']['y']}, ";
        echo "宽={$object['bbox']['width']}, 高={$object['bbox']['height']}\n";
        
        $count++;
        if ($count >= 5) {
            break;
        }
    }
    echo "==================================================\n\n";
} catch (Exception $e) {
    echo "物体检测失败: " . $e->getMessage() . "\n";
}

// 测试3: 人脸识别
try {
    echo "测试3: 人脸识别 - $faceImagePath\n";
    $result = $cvAPI->recognizeFaces($faceImagePath);
    
    echo "识别结果:\n";
    echo " - 检测到人脸数量: " . count($result['faces']) . "\n";
    
    // 显示前3个人脸结果
    $count = 0;
    foreach ($result['faces'] as $face) {
        echo " - 人脸 #" . ($count + 1) . " (置信度: " . round($face['confidence'] * 100, 2) . "%)\n";
        
        if (isset($face['recognition']) && isset($face['recognition']['person_id'])) {
            echo "   身份: {$face['recognition']['person_name']} (ID: {$face['recognition']['person_id']})\n";
        } else {
            echo "   身份: 未知\n";
        }
        
        if (isset($face['demographics'])) {
            echo "   年龄: ~{$face['demographics']['age']}岁\n";
            echo "   性别: {$face['demographics']['gender']}\n";
        }
        
        if (isset($face['emotion'])) {
            echo "   情绪: {$face['emotion']['dominant']}\n";
        }
        
        $count++;
        if ($count >= 3) {
            break;
        }
    }
    echo "==================================================\n\n";
} catch (Exception $e) {
    echo "人脸识别失败: " . $e->getMessage() . "\n";
}

// 测试4: 图像分类
try {
    echo "测试4: 图像分类 - $testImagePath\n";
    $result = $cvAPI->classifyImage($testImagePath);
    
    echo "分类结果:\n";
    
    // 显示前5个分类结果
    $count = 0;
    foreach ($result['categories'] as $category) {
        echo " - {$category['name']} (置信度: " . round($category['confidence'] * 100, 2) . "%)\n";
        
        $count++;
        if ($count >= 5) {
            break;
        }
    }
    echo "==================================================\n\n";
} catch (Exception $e) {
    echo "图像分类失败: " . $e->getMessage() . "\n";
}

// 测试5: 文本识别(OCR)
try {
    echo "测试5: 文本识别(OCR) - $textImagePath\n";
    $result = $cvAPI->recognizeText($textImagePath);
    
    echo "识别结果:\n";
    echo " - 检测到文本行数: " . count($result['lines']) . "\n";
    echo " - 提取的文本:\n";
    
    foreach ($result['lines'] as $index => $line) {
        echo "   " . ($index + 1) . ": {$line['text']} (置信度: " . round($line['confidence'] * 100, 2) . "%)\n";
    }
    
    echo " - 处理时间: {$result['processing_time']}ms\n";
    echo "==================================================\n\n";
} catch (Exception $e) {
    echo "文本识别失败: " . $e->getMessage() . "\n";
}

// 测试6: 图像比较
try {
    echo "测试6: 图像比较\n";
    echo " - 图像1: $image1Path\n";
    echo " - 图像2: $image2Path\n";
    
    $result = $cvAPI->compareImages($image1Path, $image2Path);
    
    echo "比较结果:\n";
    echo " - 相似度: " . round($result['similarity'] * 100, 2) . "%\n";
    echo " - 判定: " . ($result['is_similar'] ? '相似' : '不相似') . "\n";
    echo " - 比较方法: {$result['comparison_details']['method']}\n";
    echo " - 阈值: " . round($result['comparison_details']['threshold'] * 100, 2) . "%\n";
    echo "==================================================\n\n";
} catch (Exception $e) {
    echo "图像比较失败: " . $e->getMessage() . "\n";
}

// 测试7: 获取性能统计
echo "测试7: 性能统计\n";
$stats = $cvAPI->getPerformanceStats();

echo "性能统计:\n";
echo " - 总处理图像数: {$stats['total_processed_images']}\n";
echo " - 平均处理时间: {$stats['average_processing_time']}ms\n";
echo " - 缓存命中率: " . round($stats['cache_hit_ratio'] * 100, 2) . "%\n";
echo " - 内存使用峰值: " . formatBytes($stats['peak_memory_usage']) . "\n";
echo "==================================================\n\n";

// 清理资源
$cvAPI->cleanup();
echo "资源已清理完毕!\n";

/**
 * 创建示例图像
 *
 * @param string $path 图像路径
 * @param int $width 宽度
 * @param int $height 高度
 * @return bool 是否成功
 */
function createSampleImage(string $path, int $width, int $height): bool
{
    // 创建空白图像
    $image = imagecreatetruecolor($width, $height);
    
    // 定义颜色
    $bgColor = imagecolorallocate($image, 230, 230, 230);
    $textColor = imagecolorallocate($image, 50, 50, 50);
    $boxColor = imagecolorallocate($image, 100, 150, 200);
    
    // 填充背景
    imagefill($image, 0, 0, $bgColor);
    
    // 绘制一些形状
    imagefilledrectangle($image, $width/4, $height/4, $width*3/4, $height*3/4, $boxColor);
    
    // 添加文本
    $text = "AlingAi CV Test";
    $fontSize = 5;
    $textWidth = imagefontwidth($fontSize) * strlen($text);
    $textHeight = imagefontheight($fontSize);
    $textX = ($width - $textWidth) / 2;
    $textY = ($height - $textHeight) / 2;
    
    imagestring($image, $fontSize, $textX, $textY, $text, $textColor);
    
    // 保存图像
    $result = imagejpeg($image, $path, 90);
    
    // 释放资源
    imagedestroy($image);
    
    return $result;
}

/**
 * 格式化字节大小
 *
 * @param int $bytes 字节数
 * @param int $precision 精度
 * @return string 格式化后的字符串
 */
function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
} 