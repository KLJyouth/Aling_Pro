<?php
/**
 * 文件名：nlp-integration-test.php
 * 功能描述：NLP模块集成测试 - 测试各个NLP组件之间的协作
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

use AlingAi\AI\Engines\NLP\TextAnalysisEngine;
use AlingAi\AI\Engines\NLP\UniversalTokenizer;
use AlingAi\AI\Engines\NLP\KeywordExtractor;
use AlingAi\AI\Engines\NLP\TextSummarizer;
use AlingAi\AI\Engines\NLP\TextClassifier;
use AlingAi\AI\Engines\NLP\LanguageDetector;
use AlingAi\AI\Engines\NLP\NERModel;
use AlingAi\AI\Engines\NLP\POSTagger;
use AlingAi\AI\Engines\NLP\SentimentAnalyzer;
use AlingAi\Core\Logger\FileLogger;
use AlingAi\Utils\PerformanceMonitor;

// 创建日志记录器
$logger = new FileLogger(__DIR__ . '/../../logs/nlp-integration-test.log');
$logger->info('NLP模块集成测试开始');

// 创建性能监控器
$performanceMonitor = new PerformanceMonitor($logger);
$performanceMonitor->start('nlp_integration_test');

// 创建文本分析引擎
$textAnalysisEngine = new TextAnalysisEngine([
    'default_language' => 'zh-CN'
], $logger);

// 测试文本
$chineseNews = <<<TEXT
中国科技创新取得重大突破，量子计算研究领域再创佳绩。
昨日，中国科学院宣布，其研究团队成功研发出新一代量子计算芯片，
该芯片在计算能力和稳定性方面均有显著提升，将为解决复杂科学问题提供强大工具。
专家表示，这一突破标志着中国在量子科技领域已跻身世界前列，
未来有望在密码破解、新材料开发、药物研发等领域带来革命性变化。
国家科技部表示将继续加大对量子计算研究的投入，
推动量子信息科学与传统产业深度融合，加速科技成果转化。
TEXT;

$englishNews = <<<TEXT
Breaking: Major Breakthrough in Renewable Energy Storage
Scientists at Cambridge University have announced a revolutionary new battery technology 
that could solve one of renewable energy's biggest challenges: efficient energy storage.
The new battery, developed using sustainable materials, can store energy for up to 30 days 
with minimal loss and costs significantly less than current lithium-ion alternatives.
"This could be the missing piece in the renewable energy puzzle," said Professor Sarah Johnson, 
who led the research team. "It addresses the intermittency issue that has long plagued solar and wind power."
Industry experts predict this technology could accelerate the global transition to renewable energy 
by making it more reliable and economically competitive with fossil fuels.
The research has received $50 million in funding for commercial development, with the first 
large-scale units expected to be deployed within two years.
TEXT;

// 1. 语言检测和基本分析
echo "===== 语言检测和基本分析 =====\n\n";

$performanceMonitor->start('language_detection');
$chineseLanguage = $textAnalysisEngine->getLanguageDetector()->detect($chineseNews);
$englishLanguage = $textAnalysisEngine->getLanguageDetector()->detect($englishNews);
$performanceMonitor->end('language_detection');

echo "中文新闻语言检测结果: {$chineseLanguage}\n";
echo "英文新闻语言检测结果: {$englishLanguage}\n\n";

// 2. 分词和词性标注
echo "===== 分词和词性标注 =====\n\n";

$performanceMonitor->start('tokenization_and_pos');
$chineseTokens = $textAnalysisEngine->tokenize($chineseNews);
$chinesePosTags = $textAnalysisEngine->tagPOS($chineseNews);
$performanceMonitor->end('tokenization_and_pos');

echo "中文新闻分词结果 (前10个词): " . implode(', ', array_slice($chineseTokens, 0, 10)) . "...\n";
echo "中文新闻词性标注结果 (前5个): \n";
$count = 0;
foreach ($chinesePosTags as $token => $tag) {
    if ($count >= 5) break;
    echo "  {$token}: {$tag}\n";
    $count++;
}
echo "\n";

// 3. 命名实体识别
echo "===== 命名实体识别 =====\n\n";

$performanceMonitor->start('ner');
$chineseEntities = $textAnalysisEngine->recognizeEntities($chineseNews);
$englishEntities = $textAnalysisEngine->recognizeEntities($englishNews, ['language' => 'en-US']);
$performanceMonitor->end('ner');

echo "中文新闻实体识别结果:\n";
foreach ($chineseEntities as $entity) {
    echo "  {$entity['text']} ({$entity['type']})\n";
}

echo "\n英文新闻实体识别结果:\n";
foreach ($englishEntities as $entity) {
    echo "  {$entity['text']} ({$entity['type']})\n";
}
echo "\n";

// 4. 情感分析
echo "===== 情感分析 =====\n\n";

$performanceMonitor->start('sentiment_analysis');
$chineseSentiment = $textAnalysisEngine->analyzeSentiment($chineseNews);
$englishSentiment = $textAnalysisEngine->analyzeSentiment($englishNews, ['language' => 'en-US']);
$performanceMonitor->end('sentiment_analysis');

echo "中文新闻情感分析结果: {$chineseSentiment['label']} (分数: {$chineseSentiment['score']})\n";
echo "英文新闻情感分析结果: {$englishSentiment['label']} (分数: {$englishSentiment['score']})\n\n";

// 5. 关键词提取
echo "===== 关键词提取 =====\n\n";

$performanceMonitor->start('keyword_extraction');
$chineseKeywords = $textAnalysisEngine->extractKeywords($chineseNews);
$englishKeywords = $textAnalysisEngine->extractKeywords($englishNews, ['language' => 'en-US']);
$performanceMonitor->end('keyword_extraction');

echo "中文新闻关键词提取结果:\n";
foreach ($chineseKeywords as $keyword) {
    echo "  {$keyword['keyword']} (分数: {$keyword['score']})\n";
}

echo "\n英文新闻关键词提取结果:\n";
foreach ($englishKeywords as $keyword) {
    echo "  {$keyword['keyword']} (分数: {$keyword['score']})\n";
}
echo "\n";

// 6. 文本摘要
echo "===== 文本摘要 =====\n\n";

$performanceMonitor->start('text_summarization');
$chineseSummary = $textAnalysisEngine->summarizeText($chineseNews, "中国量子计算突破");
$englishSummary = $textAnalysisEngine->summarizeText($englishNews, "Renewable Energy Storage Breakthrough", ['language' => 'en-US']);
$performanceMonitor->end('text_summarization');

echo "中文新闻摘要:\n{$chineseSummary['summary']}\n\n";
echo "英文新闻摘要:\n{$englishSummary['summary']}\n\n";

// 7. 文本分类
echo "===== 文本分类 =====\n\n";

$performanceMonitor->start('text_classification');
$chineseClassification = $textAnalysisEngine->classifyText($chineseNews);
$englishClassification = $textAnalysisEngine->classifyText($englishNews, ['language' => 'en-US']);
$performanceMonitor->end('text_classification');

echo "中文新闻分类结果:\n";
foreach ($chineseClassification['predictions'] as $prediction) {
    echo "  {$prediction['category']} (置信度: {$prediction['confidence']})\n";
}

echo "\n英文新闻分类结果:\n";
foreach ($englishClassification['predictions'] as $prediction) {
    echo "  {$prediction['category']} (置信度: {$prediction['confidence']})\n";
}
echo "\n";

// 8. 综合分析
echo "===== 综合分析 =====\n\n";

$performanceMonitor->start('comprehensive_analysis');
$chineseAnalysis = $textAnalysisEngine->analyze($chineseNews);
$englishAnalysis = $textAnalysisEngine->analyze($englishNews, ['language' => 'en-US']);
$performanceMonitor->end('comprehensive_analysis');

echo "中文新闻综合分析结果:\n";
echo "  语言: {$chineseAnalysis['language']}\n";
echo "  文本长度: {$chineseAnalysis['length']} 字符\n";
echo "  情感: {$chineseAnalysis['sentiment']['label']} (分数: {$chineseAnalysis['sentiment']['score']})\n";
echo "  实体数量: " . count($chineseAnalysis['entities']) . "\n";
echo "  关键词数量: " . count($chineseAnalysis['keywords']) . "\n";
echo "  处理时间: " . round($chineseAnalysis['processing_time'] * 1000, 2) . " 毫秒\n\n";

echo "英文新闻综合分析结果:\n";
echo "  语言: {$englishAnalysis['language']}\n";
echo "  文本长度: {$englishAnalysis['length']} 字符\n";
echo "  情感: {$englishAnalysis['sentiment']['label']} (分数: {$englishAnalysis['sentiment']['score']})\n";
echo "  实体数量: " . count($englishAnalysis['entities']) . "\n";
echo "  关键词数量: " . count($englishAnalysis['keywords']) . "\n";
echo "  处理时间: " . round($englishAnalysis['processing_time'] * 1000, 2) . " 毫秒\n\n";

// 9. 性能统计
$performanceMonitor->end('nlp_integration_test');
$stats = $performanceMonitor->getStats();

echo "===== 性能统计 =====\n\n";
echo "总执行时间: " . round($stats['nlp_integration_test']['total_duration'], 4) . " 秒\n\n";
echo "各模块执行时间:\n";
foreach ($stats as $name => $stat) {
    if ($name !== 'nlp_integration_test') {
        echo "  {$name}: " . round($stat['total_duration'], 4) . " 秒\n";
    }
}

$logger->info('NLP模块集成测试结束', [
    'total_duration' => $stats['nlp_integration_test']['total_duration'],
    'peak_memory' => $stats['nlp_integration_test']['peak_memory']
]);

echo "\n测试完成。\n"; 