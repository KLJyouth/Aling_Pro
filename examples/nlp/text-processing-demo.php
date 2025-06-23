<?php
/**
 * 文件名：text-processing-demo.php
 * 功能描述：NLP文本处理演示 - 展示文本摘要、语言检测、关键词提取和文本分类功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-02-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Examples\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use AlingAi\AI\Engines\NLP\TextSummarizer;
use AlingAi\AI\Engines\NLP\LanguageDetector;
use AlingAi\AI\Engines\NLP\UniversalTokenizer;
use AlingAi\AI\Engines\NLP\KeywordExtractor;
use AlingAi\AI\Engines\NLP\TextClassifier;
use AlingAi\Core\Logger\FileLogger;

// 创建日志器
$logger = new FileLogger([
    'log_file' => __DIR__ . '/../../logs/nlp-demo.log',
    'log_level' => 'debug'
]);

// 创建分词器
$tokenizer = new UniversalTokenizer([
    'default_language' => 'zh-CN',
    'supported_languages' => ['zh-CN', 'en-US'],
    'preserve_case' => true
], $logger);

// 创建文本摘要器
$summarizer = new TextSummarizer([
    'max_summary_length' => 300,
    'compression_ratio' => 0.3
], $tokenizer);

// 创建语言检测器
$languageDetector = new LanguageDetector([
    'confidence_threshold' => 0.6
], $logger);

// 创建关键词提取器
$keywordExtractor = new KeywordExtractor([
    'default_language' => 'zh-CN',
    'default_algorithm' => 'tfidf',
    'max_keywords' => 10
]);

// 创建文本分类器
$textClassifier = new TextClassifier([
    'default_language' => 'zh-CN',
    'default_algorithm' => 'naive_bayes',
    'min_confidence' => 0.2
]);

// 测试文本
$chineseText = <<<TEXT
人工智能（Artificial Intelligence，简称AI）是计算机科学的一个分支，它企图了解智能的实质，并生产出一种新的能以人类智能相似的方式做出反应的智能机器。人工智能的研究包括机器人、语言识别、图像识别、自然语言处理和专家系统等。随着计算机技术的发展，人工智能已经不再是一个纯粹的概念，而是已经深入到了我们生活的方方面面。

人工智能的发展历程可以追溯到20世纪50年代。1956年，在达特茅斯会议上，"人工智能"这一术语被首次提出。此后，人工智能的研究经历了几次起伏。20世纪70年代到80年代，由于技术限制和资金问题，人工智能研究进入了低谷，这段时期被称为"人工智能的冬天"。

进入21世纪，随着计算能力的提升和大数据的出现，人工智能迎来了新的春天。深度学习、机器学习等技术的突破，使得人工智能在图像识别、语音识别、自然语言处理等领域取得了显著进展。如今，人工智能已经在医疗、金融、教育、交通等多个领域展现出巨大的应用潜力。

然而，人工智能的发展也带来了一系列的伦理和安全问题。如何确保人工智能的决策过程是透明的，如何防止人工智能被滥用，如何处理人工智能可能带来的就业问题，这些都是我们需要面对的挑战。

未来，随着技术的不断进步，人工智能将会更加智能化、个性化和人性化，为人类社会带来更多的便利和可能性。但同时，我们也需要审慎地对待人工智能的发展，确保它朝着有益于人类社会的方向前进。
TEXT;

$englishText = <<<TEXT
Artificial Intelligence (AI) is a branch of computer science that aims to understand the essence of intelligence and produce a new type of intelligent machine that can react in a way similar to human intelligence. Research in artificial intelligence includes robotics, speech recognition, image recognition, natural language processing, and expert systems. With the development of computer technology, artificial intelligence is no longer a pure concept, but has penetrated into all aspects of our lives.

The development of artificial intelligence can be traced back to the 1950s. In 1956, at the Dartmouth Conference, the term "artificial intelligence" was first proposed. Since then, research in artificial intelligence has experienced several ups and downs. From the 1970s to the 1980s, due to technical limitations and funding issues, artificial intelligence research entered a trough, a period known as the "AI winter".

Entering the 21st century, with the improvement of computing power and the emergence of big data, artificial intelligence has ushered in a new spring. Breakthroughs in technologies such as deep learning and machine learning have led to significant advances in image recognition, speech recognition, natural language processing, and other fields. Today, artificial intelligence has shown great application potential in many fields such as healthcare, finance, education, and transportation.

However, the development of artificial intelligence has also brought a series of ethical and security issues. How to ensure that the decision-making process of artificial intelligence is transparent, how to prevent the misuse of artificial intelligence, and how to deal with the employment issues that artificial intelligence may bring, these are all challenges we need to face.

In the future, with the continuous advancement of technology, artificial intelligence will become more intelligent, personalized, and humanized, bringing more convenience and possibilities to human society. But at the same time, we also need to treat the development of artificial intelligence with caution to ensure that it moves in a direction that is beneficial to human society.
TEXT;

$mixedText = <<<TEXT
这是一段混合了中文和English的文本。This is a text that mixes Chinese and English. 
人工智能技术正在迅速发展，Artificial Intelligence is developing rapidly. 
我们需要理解这些技术的影响，We need to understand the impact of these technologies.
TEXT;

// 测试语言检测
echo "========== 语言检测测试 ==========\n";
echo "检测中文文本语言：\n";
$chineseResult = $languageDetector->detect($chineseText, ['detailed' => true]);
echo "检测结果：" . $chineseResult['language'] . "（置信度：" . $chineseResult['confidence'] . "）\n\n";

echo "检测英文文本语言：\n";
$englishResult = $languageDetector->detect($englishText, ['detailed' => true]);
echo "检测结果：" . $englishResult['language'] . "（置信度：" . $englishResult['confidence'] . "）\n\n";

echo "检测混合文本语言：\n";
$mixedResult = $languageDetector->detect($mixedText, ['detailed' => true]);
echo "检测结果：" . $mixedResult['language'] . "（置信度：" . $mixedResult['confidence'] . "）\n";
if (isset($mixedResult['details'])) {
    echo "所有语言相似度：\n";
    foreach ($mixedResult['details']['all_languages'] as $lang => $sim) {
        echo "  - $lang: $sim\n";
    }
}
echo "\n";

// 测试文本摘要
echo "========== 文本摘要测试 ==========\n";
echo "中文文本提取式摘要：\n";
$chineseSummary = $summarizer->summarize($chineseText, "人工智能发展", [
    'algorithm' => 'extractive',
    'language' => 'zh-CN'
]);
echo $chineseSummary['summary'] . "\n\n";

echo "英文文本提取式摘要：\n";
$englishSummary = $summarizer->summarize($englishText, "Artificial Intelligence Development", [
    'algorithm' => 'extractive',
    'language' => 'en-US'
]);
echo $englishSummary['summary'] . "\n\n";

echo "中文文本关键词摘要：\n";
$chineseKeywordSummary = $summarizer->summarize($chineseText, null, [
    'algorithm' => 'keyword',
    'language' => 'zh-CN'
]);
echo "关键词：" . implode(", ", array_slice($chineseKeywordSummary['keywords'], 0, 10)) . "\n";
echo $chineseKeywordSummary['summary'] . "\n\n";

echo "英文文本生成式摘要：\n";
$englishAbstractiveSummary = $summarizer->summarize($englishText, null, [
    'algorithm' => 'abstractive',
    'language' => 'en-US',
    'max_length' => 200
]);
echo $englishAbstractiveSummary['summary'] . "\n\n";

// 摘要统计信息
echo "========== 摘要统计信息 ==========\n";
echo "中文原文长度：" . $chineseSummary['original_length'] . " 字符\n";
echo "中文摘要长度：" . $chineseSummary['summary_length'] . " 字符\n";
echo "中文压缩比例：" . round($chineseSummary['compression_ratio'] * 100, 2) . "%\n\n";

echo "英文原文长度：" . $englishSummary['original_length'] . " 字符\n";
echo "英文摘要长度：" . $englishSummary['summary_length'] . " 字符\n";
echo "英文压缩比例：" . round($englishSummary['compression_ratio'] * 100, 2) . "%\n\n";

// 测试关键词提取
echo "========== 关键词提取测试 ==========\n";

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

// 测试文本分类
echo "\n========== 文本分类测试 ==========\n";

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

echo "\n完成演示。\n";

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