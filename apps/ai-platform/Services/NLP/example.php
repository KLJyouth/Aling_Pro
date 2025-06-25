<?php

require_once 'BaseNLPModel.php';
require_once 'TextClassificationModel.php';
require_once 'SentimentAnalysisModel.php';
require_once 'EntityRecognitionModel.php';
require_once 'LanguageDetectionModel.php';
require_once 'TextSummarizationModel.php';
require_once 'TranslationModel.php';
require_once 'fixed_nlp_new.php';

use AlingAi\AIServices\NLP\NaturalLanguageProcessor;

// 创建NLP处理器实�?$nlp = new NaturalLanguageProcessor(];

// 示例文本
$text = "AlingAi Pro is an advanced AI platform developed by our team in Beijing. 
It was launched on 2023-05-15 and has been used by many companies including 
Microsoft and Google. The platform provides natural language processing, 
computer vision, and machine learning capabilities.";

// 进行文本分析
$result = $nlp->analyzeText($text];

// 输出结果
echo "=== NLP分析结果 ===\n\n";
echo "文本: " . $text . "\n\n";

echo "语言: " . $result["data"]["language"]["language_name"] . 
     " (置信�? " . $result["data"]["language"]["confidence"] . ")\n\n";

echo "情感: " . $result["data"]["sentiment"]["main_sentiment"] . 
     " (置信�? " . $result["data"]["sentiment"]["confidence"] . ")\n\n";

echo "识别到的实体:\n";
foreach ($result["data"]["entities"]["entities"] as $entity) {
    echo "- " . $entity["text"] . " (" . $entity["type"] . 
         ", 置信�? " . $entity["confidence"] . ")\n";
}
echo "\n";

echo "主要类别: " . $result["data"]["categories"]["top_category"] . 
     " (置信�? " . $result["data"]["categories"]["confidence"] . ")\n\n";

echo "摘要: " . $result["data"]["summary"]["summary"] . "\n\n";

// 翻译示例
$translationResult = $nlp->translateText($text, ["target_language" => "zh"]];
echo "翻译 (英文 -> 中文): " . $translationResult["data"]["translated_text"] . "\n\n";

// 获取服务状�?$status = $nlp->getStatus(];
echo "=== 服务状�?===\n";
echo "状�? " . $status["status"] . "\n";
echo "已加载模�? " . implode(", ", $status["available_models"]) . "\n";
echo "默认模型: " . $status["default_model"] . "\n";
echo "处理请求�? " . $status["requests_processed"] . "\n";
echo "平均处理时间: " . $status["average_processing_time"] . "\n";
