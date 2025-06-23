<?php
/**
 * 文件名：nlp-tokenizer-demo.php
 * 功能描述：NLP分词器模块示例演示
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * 本示例演示了如何使用AlingAi的NLP分词器模块
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AlingAi\Core\Logger\FileLogger;
use AlingAi\Utils\CacheManager;
use AlingAi\Engines\NLP\UniversalTokenizer;

// 创建日志记录器
$logger = new FileLogger([
    'log_dir' => __DIR__ . '/../logs',
    'log_filename' => 'nlp-tokenizer-demo.log',
    'log_level' => 'debug'
]);

// 创建缓存管理器
$cache = new CacheManager([
    'cache_dir' => __DIR__ . '/../cache/nlp',
    'ttl' => 3600
]);

// 显示标题
echo "============================================================\n";
echo "                AlingAi NLP分词器演示程序                     \n";
echo "============================================================\n\n";

try {
    // 创建分词器
    echo "初始化分词器...\n";
    $tokenizer = new UniversalTokenizer([
        'default_language' => 'zh-CN',
        'preserve_case' => true,
        'use_cache' => true,
        'remove_stopwords' => false
    ], $logger, $cache);
    
    // 显示分词器信息
    $info = $tokenizer->getTokenizerInfo();
    echo "分词器信息:\n";
    echo "- 名称: {$info['name']}\n";
    echo "- 版本: {$info['version']}\n";
    echo "- 当前语言: {$info['current_language']}\n";
    echo "- 支持语言: " . implode(', ', $info['supported_languages']) . "\n\n";
    
    // 示例文本
    $chineseText = "AlingAi是一个强大的人工智能系统，它可以理解和处理自然语言。";
    $englishText = "AlingAi is a powerful AI system that can understand and process natural language.";
    $mixedText = "AlingAi支持中英文混合分词，it's very convenient for users.";
    
    // 语言检测演示
    echo "语言检测演示:\n";
    echo "示例1 (中文文本): \"$chineseText\"\n";
    $lang1 = $tokenizer->detectLanguage($chineseText);
    echo "检测结果: $lang1\n\n";
    
    echo "示例2 (英文文本): \"$englishText\"\n";
    $lang2 = $tokenizer->detectLanguage($englishText);
    echo "检测结果: $lang2\n\n";
    
    echo "示例3 (混合文本): \"$mixedText\"\n";
    $lang3 = $tokenizer->detectLanguage($mixedText);
    echo "检测结果: $lang3\n\n";
    
    // 中文分词演示
    echo "中文分词演示:\n";
    $tokenizer->setLanguage('zh-CN');
    $chineseTokens = $tokenizer->tokenize($chineseText);
    
    echo "原文: $chineseText\n";
    echo "分词结果: " . count($chineseTokens) . " 个词元\n";
    
    // 展示分词结果
    echo str_repeat("-", 80) . "\n";
    echo sprintf("%-20s | %-10s | %-15s | %-10s | %-8s\n", "文本", "类型", "位置", "长度", "停用词");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($chineseTokens as $token) {
        echo sprintf("%-20s | %-10s | %4d-%-8d | %-10d | %-8s\n",
            $token['text'],
            $token['type'],
            $token['start'],
            $token['end'],
            $token['length'],
            $token['is_stop_word'] ? '是' : '否'
        );
    }
    echo str_repeat("-", 80) . "\n\n";
    
    // 英文分词演示
    echo "英文分词演示:\n";
    $tokenizer->setLanguage('en-US');
    $englishTokens = $tokenizer->tokenize($englishText);
    
    echo "原文: $englishText\n";
    echo "分词结果: " . count($englishTokens) . " 个词元\n";
    
    // 展示分词结果
    echo str_repeat("-", 80) . "\n";
    echo sprintf("%-15s | %-10s | %-15s | %-10s | %-8s\n", "文本", "类型", "位置", "长度", "停用词");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($englishTokens as $token) {
        echo sprintf("%-15s | %-10s | %4d-%-8d | %-10d | %-8s\n",
            $token['text'],
            $token['type'],
            $token['start'],
            $token['end'],
            $token['length'],
            $token['is_stop_word'] ? '是' : '否'
        );
    }
    echo str_repeat("-", 80) . "\n\n";
    
    // 停用词过滤演示
    echo "停用词过滤演示:\n";
    $filteredTokens = $tokenizer->filterTokens($englishTokens, [
        'remove_stopwords' => true,
        'remove_punctuation' => true
    ]);
    
    echo "过滤前: " . count($englishTokens) . " 个词元\n";
    echo "过滤后: " . count($filteredTokens) . " 个词元\n";
    echo "过滤结果: " . $tokenizer->tokensToString($filteredTokens) . "\n\n";
    
    // 词干提取演示
    echo "词干提取演示:\n";
    $words = ["running", "flies", "argued", "happier", "books"];
    echo "原始词: " . implode(", ", $words) . "\n";
    echo "词干: ";
    
    foreach ($words as $word) {
        echo $word . " -> " . $tokenizer->stem($word, 'en-US') . ", ";
    }
    echo "\n\n";
    
    // 词形还原演示
    echo "词形还原演示:\n";
    $words = ["am", "better", "went", "children", "mice"];
    echo "原始词: " . implode(", ", $words) . "\n";
    echo "词元: ";
    
    foreach ($words as $word) {
        echo $word . " -> " . $tokenizer->lemmatize($word, 'en-US') . ", ";
    }
    echo "\n\n";
    
    // 自定义停用词演示
    echo "自定义停用词演示:\n";
    $customStopwords = ["人工智能", "系统"];
    echo "添加自定义停用词: " . implode(", ", $customStopwords) . "\n";
    
    $tokenizer->setLanguage('zh-CN');
    $tokenizer->addStopwords($customStopwords);
    
    $customTokens = $tokenizer->tokenize($chineseText, ['remove_stopwords' => true]);
    echo "过滤后结果: " . $tokenizer->tokensToString($customTokens) . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . " 行数: " . $e->getLine() . "\n";
    
    // 记录异常
    $logger->error("NLP分词器演示异常", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

echo "\n演示完成!\n";

/**
 * 注意: 本示例假设所有依赖的类都已经实现并可用。
 * 如果您运行这个示例时遇到缺少类的错误，请确保先实现这些依赖类。
 */ 