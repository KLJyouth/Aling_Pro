<?php
/**
 * 文件名：advanced-text-analysis-demo.php
 * 功能描述：高级文本分析演示 - 展示关键词提取和文本分类功能
 * 创建时间：2025-02-XX
 * 最后修改：2025-02-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Examples\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

// 引入必要的类
require_once __DIR__ . '/../../vendor/autoload.php';

use AlingAi\AI\Engines\NLP\KeywordExtractor;
use AlingAi\AI\Engines\NLP\TextClassifier;
use AlingAi\AI\Engines\NLP\TextAnalysisEngine;
use AlingAi\Core\Logger\FileLogger;

// 创建日志记录器
$logger = new FileLogger(__DIR__ . '/../../logs/nlp-demo.log');
$logger->info('高级文本分析演示开始');

// 创建文本分析引擎
$textAnalysisEngine = new TextAnalysisEngine([
    'default_language' => 'zh-CN'
], $logger);

// 示例文本
$chineseText = <<<EOT
人工智能（Artificial Intelligence，缩写为AI）是计算机科学的一个分支，它企图了解智能的实质，
并生产出一种新的能以人类智能相似的方式做出反应的智能机器。人工智能的研究包括机器人、语言识别、
图像识别、自然语言处理和专家系统等。人工智能从诞生以来，理论和技术日益成熟，应用领域也不断扩大，
可以设想，未来人工智能带来的科技产品，将会是人类智慧的"容器"。人工智能可以对人的意识、思维的
信息过程的模拟。人工智能不是人的智能，但能像人那样思考、也可能超过人的智能。
EOT;

$englishText = <<<EOT
Artificial intelligence (AI) is intelligence demonstrated by machines, as opposed to natural intelligence
displayed by animals including humans. Leading AI textbooks define the field as the study of "intelligent agents":
any system that perceives its environment and takes actions that maximize its chance of achieving its goals.
Some popular accounts use the term "artificial intelligence" to describe machines that mimic "cognitive" functions
that humans associate with the human mind, such as "learning" and "problem solving", however this definition is
rejected by major AI researchers.
EOT;

// 1. 关键词提取演示
echo "===== 关键词提取演示 =====\n\n";

// 直接使用KeywordExtractor
$keywordExtractor = new KeywordExtractor([
    'default_language' => 'zh-CN',
    'default_algorithm' => 'tfidf',
    'max_keywords' => 10
]);

// 使用TF-IDF算法提取中文关键词
echo "中文文本 - TF-IDF算法提取关键词：\n";
$keywords = $keywordExtractor->extract($chineseText, [
    'algorithm' => 'tfidf'
]);
printKeywords($keywords);

// 使用TextRank算法提取中文关键词
echo "\n中文文本 - TextRank算法提取关键词：\n";
$keywords = $keywordExtractor->extract($chineseText, [
    'algorithm' => 'textrank'
]);
printKeywords($keywords);

// 使用RAKE算法提取英文关键词
echo "\n英文文本 - RAKE算法提取关键词：\n";
$keywords = $keywordExtractor->extract($englishText, [
    'language' => 'en-US',
    'algorithm' => 'rake'
]);
printKeywords($keywords);

// 2. 文本分类演示
echo "\n\n===== 文本分类演示 =====\n\n";

// 直接使用TextClassifier
$textClassifier = new TextClassifier([
    'default_language' => 'zh-CN',
    'default_algorithm' => 'naive_bayes',
    'min_confidence' => 0.2
]);

// 使用朴素贝叶斯算法分类中文文本
echo "中文文本 - 朴素贝叶斯算法分类：\n";
$classification = $textClassifier->classify($chineseText, [
    'algorithm' => 'naive_bayes'
]);
printClassification($classification);

// 使用SVM算法分类中文文本
echo "\n中文文本 - SVM算法分类：\n";
$classification = $textClassifier->classify($chineseText, [
    'algorithm' => 'svm'
]);
printClassification($classification);

// 使用神经网络算法分类英文文本
echo "\n英文文本 - 神经网络算法分类：\n";
$classification = $textClassifier->classify($englishText, [
    'language' => 'en-US',
    'algorithm' => 'neural_network'
]);
printClassification($classification);

// 3. 综合文本分析演示
echo "\n\n===== 综合文本分析演示 =====\n\n";

// 使用TextAnalysisEngine进行综合分析
echo "中文文本 - 综合分析：\n";
$analysis = $textAnalysisEngine->analyze($chineseText, [
    'include_tokens' => false,
    'include_pos' => true,
    'include_entities' => true,
    'include_sentiment' => true,
    'include_keywords' => true,
    'include_summary' => true,
    'include_classification' => true
]);

// 输出分析结果
echo "文本长度: {$analysis['length']} 字符\n";
echo "语言: {$analysis['language']}\n";

if (isset($analysis['sentiment'])) {
    echo "情感分析: {$analysis['sentiment']['label']} (分数: {$analysis['sentiment']['score']})\n";
}

if (isset($analysis['entities']) && !empty($analysis['entities'])) {
    echo "实体识别:\n";
    foreach ($analysis['entities'] as $entity) {
        echo "  - {$entity['text']} ({$entity['type']})\n";
    }
}

if (isset($analysis['keywords']) && !empty($analysis['keywords'])) {
    echo "关键词提取:\n";
    foreach ($analysis['keywords'] as $keyword) {
        echo "  - {$keyword['keyword']} (分数: {$keyword['score']})\n";
    }
}

if (isset($analysis['summary']) && !empty($analysis['summary']['sentences'])) {
    echo "文本摘要:\n";
    foreach ($analysis['summary']['sentences'] as $sentence) {
        echo "  - {$sentence}\n";
    }
}

if (isset($analysis['classification']) && !empty($analysis['classification']['predictions'])) {
    echo "文本分类:\n";
    foreach ($analysis['classification']['predictions'] as $prediction) {
        echo "  - {$prediction['category']} (置信度: {$prediction['confidence']})\n";
    }
}

echo "\n处理时间: " . round($analysis['processing_time'] * 1000, 2) . " 毫秒\n";

$logger->info('高级文本分析演示结束');

/**
 * 打印关键词提取结果
 *
 * @param array $keywords 关键词数组
 */
function printKeywords(array $keywords): void
{
    foreach ($keywords as $keyword) {
        echo "  - {$keyword['keyword']} (分数: {$keyword['score']})\n";
    }
    echo "算法: {$keywords[0]['algorithm']}, 关键词数量: " . count($keywords) . "\n";
}

/**
 * 打印文本分类结果
 *
 * @param array $classification 分类结果
 */
function printClassification(array $classification): void
{
    echo "算法: {$classification['algorithm']}\n";
    echo "预测类别: " . ($classification['top_category'] ?? '无') . "\n";
    echo "置信度: " . ($classification['top_confidence'] ?? 0) . "\n";
    
    if (!empty($classification['predictions'])) {
        echo "所有预测:\n";
        foreach ($classification['predictions'] as $prediction) {
            echo "  - {$prediction['category']} (置信度: {$prediction['confidence']})\n";
        }
    }
} 