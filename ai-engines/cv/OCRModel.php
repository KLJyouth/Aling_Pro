<?php
/**
 * 文件名：OCRModel.php
 * 功能描述：光学字符识别模型 - 识别图像中的文字内容
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
 * OCR模型
 * 
 * 负责在图像中识别文字，支持多语言、结构化文本识别、表格识别等功能
 */
class OCRModel
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
     * @var array 支持的语言
     */
    private array $supportedLanguages = [
        "zh-cn" => "简体中文",
        "zh-tw" => "繁体中文",
        "en" => "英语",
        "ja" => "日语",
        "ko" => "韩语",
        "ru" => "俄语",
        "fr" => "法语",
        "de" => "德语",
        "es" => "西班牙语",
        "pt" => "葡萄牙语",
        "it" => "意大利语",
        "auto" => "自动检测"
    ];
    
    /**
     * @var array 支持的OCR引擎
     */
    private array $supportedEngines = ["general", "dense", "handwriting", "formula", "document", "table"];
    
    /**
     * 构造函数
     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志记录器
     * @param CacheManager|null $cache 缓存管理器
     */
    public function __construct(array $config = [],  ?LoggerInterface $logger = null, ?CacheManager $cache = null)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $this->mergeConfig($config);
        
        $this->initialize();
        
        if ($this->logger) {
            $this->logger->info("OCR模型初始化成功", [
                "engine" => $this->config["engine"], 
                "language" => $this->config["language"], 
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
            "engine" => "general", // OCR引擎
            "language" => "auto", // 识别语言
            "confidence_threshold" => 0.6, // 置信度阈值
            "enable_layout_analysis" => false, // 是否启用布局分析
            "enable_table_recognition" => false, // 是否启用表格识别
            "enable_formula_recognition" => false, // 是否启用公式识别
            "enable_detection" => true, // 是否启用文本检测
            "enable_correction" => false, // 是否启用文本校正
            "cache_enabled" => true, // 是否启用缓存
            "cache_ttl" => 3600, // 缓存有效期(秒)
            "use_gpu" => false, // 是否使用GPU加速
            "max_text_length" => 10000, // 最大文本长度
            "preserve_whitespace" => true, // 是否保留空白
            "preserve_punctuation" => true, // 是否保留标点符号
            "max_detections" => 1000, // 最大检测数量
            "model_path" => null, // 模型文件路径
            "batch_processing" => false, // 是否启用批处理
            "batch_size" => 4 // 批处理大小
        ];
        
        return array_merge($defaultConfig, $config);
    }
    
    /**
     * 初始化模型
     */
    private function initialize(): void
    {
        // 实际项目中这里会加载预训练模型
        // 本实现中使用模拟模型进行演示
    }
    
    /**
     * OCR主方法 - 识别图像中的文字
     *
     * @param mixed $image 图像数据(路径或图像数据)
     * @param array $options 识别选项
     * @return array 识别结果
     * @throws InvalidArgumentException 参数无效时抛出异常
     * @throws RuntimeException 处理失败时抛出异常
     */
    public function recognize($image, array $options = []): array
    {
        // 合并选项
        $options = array_merge($this->config, $options);
        
        try {
            // 检查缓存
            if ($options["cache_enabled"] && $this->cache) {
                $imagePath = is_string($image) ? $image : "";
                if ($imagePath && file_exists($imagePath)) {
                    $cacheKey = "ocr_" . md5_file($imagePath) . "_" . md5(json_encode($options));
                    if ($this->cache->has($cacheKey)) {
                        return $this->cache->get($cacheKey);
                    }
                }
            }
            
            // 获取图像信息
            $imageInfo = $this->getImageInfo($image);
            
            // 预处理图像
            $processedImage = $this->preprocessImage($image, $options);
            
            // 运行OCR模型
            $ocrResults = $this->runOCRModel($processedImage, $options);
            
            // 后处理结果
            $result = $this->postprocessResults($ocrResults, $imageInfo, $options);
            
            // 如果启用了文本校正
            if ($options["enable_correction"]) {
                $result = $this->correctText($result, $options);
            }
            
            // 缓存结果
            if ($options["cache_enabled"] && $this->cache && isset($cacheKey)) {
                $this->cache->set($cacheKey, $result, $options["cache_ttl"]);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("OCR处理失败", [
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]);
            }
            throw new RuntimeException("OCR处理失败: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 批量OCR识别
     *
     * @param array $images 图像路径或数据数组
     * @param array $options 识别选项
     * @return array 识别结果数组
     */
    public function recognizeBatch(array $images, array $options = []): array
    {
        if (!$this->config["batch_processing"]) {
            throw new RuntimeException("批量处理未启用");
        }
        
        $results = [];
        $batchSize = $this->config["batch_size"];
        $startTime = microtime(true);
        
        // 分批处理
        for ($i = 0; $i < count($images); $i += $batchSize) {
            $batch = array_slice($images, $i, $batchSize);
            $batchResults = [];
            
            // 处理当前批次
            foreach ($batch as $index => $image) {
                try {
                    $batchResults[$index] = $this->recognize($image, $options);
                } catch (Exception $e) {
                    $batchResults[$index] = ["error" => $e->getMessage()];
                    
                    if ($this->logger) {
                        $this->logger->error("批量OCR处理失败", [
                            "batch_index" => $i + $index,
                            "error" => $e->getMessage()
                        ]);
                    }
                }
            }
            
            $results = array_merge($results, $batchResults);
        }
        
        $totalTime = microtime(true) - $startTime;
        
        return [
            "results" => $results,
            "total_images" => count($images),
            "total_time" => round($totalTime * 1000), // 转换为毫秒
            "average_time_per_image" => round(($totalTime * 1000) / count($images)),
            "batch_size" => $batchSize,
            "num_batches" => ceil(count($images) / $batchSize)
        ];
    }

    /**
     * 获取图像信息
     * 
     * @param mixed $image 图像数据
     * @return array 图像信息
     */
    private function getImageInfo($image): array
    {
        if (is_string($image) && file_exists($image)) {
            $imageSize = getimagesize($image);
            $fileSize = filesize($image);
            
            return [
                "width" => $imageSize[0] ?? 0,
                "height" => $imageSize[1] ?? 0,
                "type" => $imageSize["mime"] ?? "",
                "file_size" => $fileSize,
                "file_path" => $image,
                "is_file" => true
            ];
        } else {
            // 处理二进制图像数据
            return [
                "is_file" => false,
                "data_size" => is_string($image) ? strlen($image) : 0
            ];
        }
    }
    
    /**
     * 预处理图像
     * 
     * @param mixed $image 图像数据
     * @param array $options 选项
     * @return mixed 预处理后的图像
     */
    private function preprocessImage($image, array $options): array
    {
        // 实际项目中这里会进行图像预处理
        // 如调整大小、灰度化、二值化、去噪等
        
        // 模拟预处理过程
        return [
            "original" => $image,
            "processed" => true,
            "grayscale" => true,
            "denoised" => true,
            "binarized" => false
        ];
    }
    
    /**
     * 运行OCR模型
     * 
     * @param array $processedImage 预处理后的图像
     * @param array $options 选项
     * @return array 原始OCR结果
     */
    private function runOCRModel(array $processedImage, array $options): array
    {
        // 实际项目中这里会调用深度学习框架运行OCR模型
        
        // 模拟OCR结果 - 文本区域和识别结果
        $textRegions = [];
        
        // 模拟一些文本区域
        if ($options["engine"] === "general" || $options["engine"] === "dense") {
            $textRegions = [
                [
                    "bbox" => [100, 50, 400, 80],
                    "text" => "AlingAi人工智能平台",
                    "confidence" => 0.95,
                    "language" => "zh-cn"
                ],
                [
                    "bbox" => [100, 100, 500, 130],
                    "text" => "为企业提供全方位AI解决方案",
                    "confidence" => 0.92,
                    "language" => "zh-cn"
                ],
                [
                    "bbox" => [100, 150, 450, 180],
                    "text" => "包括自然语言处理、计算机视觉和机器学习",
                    "confidence" => 0.89,
                    "language" => "zh-cn"
                ],
                [
                    "bbox" => [100, 200, 400, 230],
                    "text" => "联系我们：contact@alingai.com",
                    "confidence" => 0.94,
                    "language" => "zh-cn"
                ],
                [
                    "bbox" => [100, 250, 350, 280],
                    "text" => "电话：+86-123-4567-8910",
                    "confidence" => 0.93,
                    "language" => "zh-cn"
                ]
            ];
        } elseif ($options["engine"] === "document") {
            $textRegions = [
                [
                    "bbox" => [50, 50, 550, 80],
                    "text" => "服务协议",
                    "confidence" => 0.98,
                    "language" => "zh-cn",
                    "type" => "title"
                ],
                [
                    "bbox" => [50, 100, 550, 150],
                    "text" => "本协议由您与AlingAi平台共同缔结，本协议具有合同效力。您在使用AlingAi平台提供的服务前，请仔细阅读本协议的全部内容。",
                    "confidence" => 0.94,
                    "language" => "zh-cn",
                    "type" => "paragraph"
                ],
                [
                    "bbox" => [50, 170, 550, 200],
                    "text" => "1. 服务内容",
                    "confidence" => 0.96,
                    "language" => "zh-cn",
                    "type" => "heading"
                ],
                [
                    "bbox" => [50, 220, 550, 300],
                    "text" => "AlingAi平台提供基于人工智能的解决方案，包括但不限于自然语言处理、计算机视觉、机器学习等技术服务。具体服务内容以平台实际提供的为准。",
                    "confidence" => 0.91,
                    "language" => "zh-cn",
                    "type" => "paragraph"
                ]
            ];
        } elseif ($options["engine"] === "table") {
            $textRegions = [
                [
                    "bbox" => [50, 50, 550, 300],
                    "text" => "产品名称,价格,库存\nAI基础版,1000元/年,充足\nAI专业版,3000元/年,充足\nAI企业版,10000元/年,有限\nAI定制版,价格面议,需申请",
                    "confidence" => 0.92,
                    "language" => "zh-cn",
                    "type" => "table",
                    "structure" => [
                        "rows" => 5,
                        "columns" => 3,
                        "cells" => [
                            ["row" => 0, "col" => 0, "text" => "产品名称"],
                            ["row" => 0, "col" => 1, "text" => "价格"],
                            ["row" => 0, "col" => 2, "text" => "库存"],
                            ["row" => 1, "col" => 0, "text" => "AI基础版"],
                            ["row" => 1, "col" => 1, "text" => "1000元/年"],
                            ["row" => 1, "col" => 2, "text" => "充足"],
                            ["row" => 2, "col" => 0, "text" => "AI专业版"],
                            ["row" => 2, "col" => 1, "text" => "3000元/年"],
                            ["row" => 2, "col" => 2, "text" => "充足"],
                            ["row" => 3, "col" => 0, "text" => "AI企业版"],
                            ["row" => 3, "col" => 1, "text" => "10000元/年"],
                            ["row" => 3, "col" => 2, "text" => "有限"],
                            ["row" => 4, "col" => 0, "text" => "AI定制版"],
                            ["row" => 4, "col" => 1, "text" => "价格面议"],
                            ["row" => 4, "col" => 2, "text" => "需申请"]
                        ]
                    ]
                ]
            ];
        } elseif ($options["engine"] === "formula") {
            $textRegions = [
                [
                    "bbox" => [100, 100, 500, 150],
                    "text" => "E = mc^2",
                    "confidence" => 0.96,
                    "language" => "en",
                    "type" => "formula",
                    "latex" => "E = mc^2"
                ],
                [
                    "bbox" => [100, 200, 500, 250],
                    "text" => "_0^ e^(-x) dx = 1",
                    "confidence" => 0.92,
                    "language" => "en",
                    "type" => "formula",
                    "latex" => "\\int_0^{\\infty} e^{-x} dx = 1"
                ]
            ];
        } elseif ($options["engine"] === "handwriting") {
            $textRegions = [
                [
                    "bbox" => [50, 50, 550, 100],
                    "text" => "会议记录：2025年1月15日",
                    "confidence" => 0.85,
                    "language" => "zh-cn",
                    "type" => "handwriting"
                ],
                [
                    "bbox" => [50, 120, 550, 170],
                    "text" => "参会人员：张三、李四、王五",
                    "confidence" => 0.82,
                    "language" => "zh-cn",
                    "type" => "handwriting"
                ],
                [
                    "bbox" => [50, 190, 550, 240],
                    "text" => "会议内容：讨论AI平台新功能",
                    "confidence" => 0.80,
                    "language" => "zh-cn",
                    "type" => "handwriting"
                ]
            ];
        }
        
        return [
            "text_regions" => $textRegions,
            "detected_language" => "zh-cn",
            "orientation" => 0, // 0度，表示图像无需旋转
            "has_text" => count($textRegions) > 0
        ];
    }
    
    /**
     * 后处理OCR结果
     * 
     * @param array $ocrResults 原始OCR结果
     * @param array $imageInfo 图像信息
     * @param array $options 选项
     * @return array 处理后的结果
     */
    private function postprocessResults(array $ocrResults, array $imageInfo, array $options): array
    {
        $textRegions = $ocrResults["text_regions"];
        
        // 过滤低置信度结果
        $filteredRegions = [];
        foreach ($textRegions as $region) {
            if ($region["confidence"] >= $options["confidence_threshold"]) {
                $filteredRegions[] = $region;
            }
        }
        
        // 按照从上到下，从左到右排序
        usort($filteredRegions, function($a, $b) {
            // 如果y坐标相差不大，按x坐标排序
            if (abs($a["bbox"][1] - $b["bbox"][1]) < 30) {
                return $a["bbox"][0] - $b["bbox"][0];
            }
            // 否则按y坐标排序
            return $a["bbox"][1] - $b["bbox"][1];
        });
        
        // 合并文本
        $fullText = "";
        foreach ($filteredRegions as $region) {
            $fullText .= $region["text"] . "\n";
        }
        
        // 限制文本长度
        if (mb_strlen($fullText) > $options["max_text_length"]) {
            $fullText = mb_substr($fullText, 0, $options["max_text_length"]) . "...";
        }
        
        // 构建结果
        $result = [
            "text_regions" => $filteredRegions,
            "full_text" => $fullText,
            "detected_language" => $ocrResults["detected_language"],
            "orientation" => $ocrResults["orientation"],
            "image_info" => $imageInfo,
            "processing_time" => rand(50, 500), // 模拟处理时间(毫秒)
            "engine" => $options["engine"],
            "confidence" => array_reduce($filteredRegions, function($carry, $item) {
                return $carry + $item["confidence"];
            }, 0) / (count($filteredRegions) ?: 1) // 平均置信度
        ];
        
        // 如果启用了布局分析
        if ($options["enable_layout_analysis"]) {
            $result["layout"] = $this->analyzeLayout($filteredRegions);
        }
        
        // 如果启用了表格识别
        if ($options["enable_table_recognition"]) {
            $result["tables"] = $this->extractTables($filteredRegions);
        }
        
        return $result;
    }
    
    /**
     * 分析文档布局
     * 
     * @param array $textRegions 文本区域
     * @return array 布局分析结果
     */
    private function analyzeLayout(array $textRegions): array
    {
        // 实际项目中这里会分析文档布局
        
        // 模拟布局分析结果
        return [
            "blocks" => [
                [
                    "type" => "title",
                    "bbox" => [50, 50, 550, 80],
                    "confidence" => 0.95
                ],
                [
                    "type" => "paragraph",
                    "bbox" => [50, 100, 550, 200],
                    "confidence" => 0.92
                ],
                [
                    "type" => "list",
                    "bbox" => [50, 220, 550, 300],
                    "confidence" => 0.90,
                    "items" => 3
                ],
                [
                    "type" => "image",
                    "bbox" => [100, 320, 500, 500],
                    "confidence" => 0.85
                ]
            ],
            "reading_order" => [0, 1, 2, 3],
            "columns" => 1
        ];
    }
    
    /**
     * 提取表格
     * 
     * @param array $textRegions 文本区域
     * @return array 表格数据
     */
    private function extractTables(array $textRegions): array
    {
        // 实际项目中这里会提取表格
        
        // 模拟表格提取结果
        $tables = [];
        
        foreach ($textRegions as $region) {
            if (isset($region["type"]) && $region["type"] === "table" && isset($region["structure"])) {
                $tables[] = [
                    "bbox" => $region["bbox"],
                    "rows" => $region["structure"]["rows"],
                    "columns" => $region["structure"]["columns"],
                    "cells" => $region["structure"]["cells"],
                    "confidence" => $region["confidence"]
                ];
            }
        }
        
        return $tables;
    }
    
    /**
     * 文本校正
     * 
     * @param array $result OCR结果
     * @param array $options 选项
     * @return array 校正后的结果
     */
    private function correctText(array $result, array $options): array
    {
        // 实际项目中这里会进行文本校正
        // 如拼写检查、语法纠正等
        
        // 模拟文本校正
        foreach ($result["text_regions"] as &$region) {
            // 这里只是示例，实际项目中会有真正的校正逻辑
            $region["original_text"] = $region["text"];
            $region["corrected"] = false;
            
            // 模拟一些简单的校正
            if (strpos($region["text"], "AlingAl") !== false) {
                $region["text"] = str_replace("AlingAl", "AlingAi", $region["text"]);
                $region["corrected"] = true;
            }
        }
        
        // 更新全文
        $fullText = "";
        foreach ($result["text_regions"] as $region) {
            $fullText .= $region["text"] . "\n";
        }
        $result["full_text"] = $fullText;
        $result["correction_applied"] = true;
        
        return $result;
    }
    
    /**
     * 提取文本
     * 
     * @param mixed $image 图像数据
     * @param array $options 选项
     * @return string 提取的文本
     */
    public function extract($image, array $options = []): string
    {
        $result = $this->recognize($image, $options);
        return $result["full_text"];
    }
    
    /**
     * 识别表格
     * 
     * @param mixed $image 图像数据
     * @param array $options 选项
     * @return array 表格数据
     */
    public function recognizeTable($image, array $options = []): array
    {
        $options["engine"] = "table";
        $options["enable_table_recognition"] = true;
        
        $result = $this->recognize($image, $options);
        return $result["tables"] ?? [];
    }
    
    /**
     * 识别公式
     * 
     * @param mixed $image 图像数据
     * @param array $options 选项
     * @return array 公式数据
     */
    public function recognizeFormula($image, array $options = []): array
    {
        $options["engine"] = "formula";
        $options["enable_formula_recognition"] = true;
        
        $result = $this->recognize($image, $options);
        
        $formulas = [];
        foreach ($result["text_regions"] as $region) {
            if (isset($region["type"]) && $region["type"] === "formula") {
                $formulas[] = [
                    "text" => $region["text"],
                    "latex" => $region["latex"] ?? "",
                    "bbox" => $region["bbox"],
                    "confidence" => $region["confidence"]
                ];
            }
        }
        
        return $formulas;
    }
    
    /**
     * 获取支持的语言
     * 
     * @return array 支持的语言
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }
    
    /**
     * 获取支持的OCR引擎
     * 
     * @return array 支持的引擎
     */
    public function getSupportedEngines(): array
    {
        return $this->supportedEngines;
    }
}
