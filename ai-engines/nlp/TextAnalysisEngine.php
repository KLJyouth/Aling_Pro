<?php
declare(strict_types=1];

/**
 * 文件名：TextAnalysisEngine.php
 * 功能描述：文本分析引擎 - 集成多种NLP功能的核心引擎
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\AI\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\AI\Engines\NLP;

use Exception;
use InvalidArgumentException;
use AlingAi\Core\Logger\LoggerInterface;

/**
 * 文本分析引擎
 *
 * 集成多种NLP功能的核心引擎，包括分词、词性标注、命名实体识别、情感分析、文本分类、关键词提取和文本摘要等功能
 */
class TextAnalysisEngine
{
    /**
     * 配置参数
     */
    private array $config;

    /**
     * 分词器
     */
    private ?TokenizerInterface $tokenizer = null;

    /**
     * 词性标注器
     */
    private ?POSTagger $posTagger = null;

    /**
     * 命名实体识别模型
     */
    private ?NERModel $nerModel = null;

    /**
     * 情感分析器
     */
    private ?SentimentAnalyzer $sentimentAnalyzer = null;

    /**
     * 文本分类器
     */
    private ?TextClassifier $textClassifier = null;

    /**
     * 关键词提取器
     */
    private ?KeywordExtractor $keywordExtractor = null;

    /**
     * 文本摘要器
     */
    private ?TextSummarizer $textSummarizer = null;

    /**
     * 日志记录器
     */
    private ?LoggerInterface $logger = null;
    
    /**
     * 构造函数
     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(array $config = [],  ?LoggerInterface $logger = null)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->logger = $logger;
        
        if ($this->logger) {
            $this->logger->info('文本分析引擎初始化成功', [
                'default_language' => $this->config['default_language']
            ]];
        }
    }
    
    /**
     * 获取默认配置
     *
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'default_language' => 'zh-CN',
            'supported_languages' => ['zh-CN', 'en-US'], 
            'use_cache' => true,
            'cache_ttl' => 3600,
            'tokenizer' => 'universal',
            'pos_tagger' => 'default',
            'ner_model' => 'default',
            'sentiment_analyzer' => 'default',
            'text_classifier' => 'default',
            'keyword_extractor' => 'default',
            'text_summarizer' => 'default'
        ];
    }
    
    /**
     * 获取分词器
     *
     * @return TokenizerInterface 分词器实例
     */
    public function getTokenizer(): TokenizerInterface
    {
        if ($this->tokenizer === null) {
            $tokenizerType = $this->config['tokenizer'];
            
            switch ($tokenizerType) {
                case 'chinese':
                    $this->tokenizer = new ChineseTokenizer(];
                    break;
                case 'english':
                    $this->tokenizer = new EnglishTokenizer(];
                    break;
                case 'universal':
                default:
                    $this->tokenizer = new UniversalTokenizer([
                        'default_language' => $this->config['default_language']
                    ]];
                    break;
            }
            
            if ($this->logger) {
                $this->logger->debug('初始化分词器', ['type' => $tokenizerType]];
            }
        }
        
        return $this->tokenizer;
    }
    
    /**
     * 获取词性标注器
     *
     * @return POSTagger 词性标注器实例
     */
    public function getPosTagger(): POSTagger
    {
        if ($this->posTagger === null) {
            $this->posTagger = new POSTagger([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('初始化词性标注器'];
            }
        }
        
        return $this->posTagger;
    }
    
    /**
     * 获取命名实体识别模型
     *
     * @return NERModel 命名实体识别模型实例
     */
    public function getNerModel(): NERModel
    {
        if ($this->nerModel === null) {
            $this->nerModel = new NERModel([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('初始化命名实体识别模型'];
            }
        }
        
        return $this->nerModel;
    }
    
    /**
     * 获取情感分析器
     *
     * @return SentimentAnalyzer 情感分析器实例
     */
    public function getSentimentAnalyzer(): SentimentAnalyzer
    {
        if ($this->sentimentAnalyzer === null) {
            $this->sentimentAnalyzer = new SentimentAnalyzer([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('初始化情感分析器'];
            }
        }
        
        return $this->sentimentAnalyzer;
    }
    
    /**
     * 获取文本分类器
     *
     * @return TextClassifier 文本分类器实例
     */
    public function getTextClassifier(): TextClassifier
    {
        if ($this->textClassifier === null) {
            $this->textClassifier = new TextClassifier([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('初始化文本分类器'];
            }
        }
        
        return $this->textClassifier;
    }
    
    /**
     * 获取关键词提取器
     *
     * @return KeywordExtractor 关键词提取器实例
     */
    public function getKeywordExtractor(): KeywordExtractor
    {
        if ($this->keywordExtractor === null) {
            $this->keywordExtractor = new KeywordExtractor([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('初始化关键词提取器'];
            }
        }
        
        return $this->keywordExtractor;
    }
    
    /**
     * 获取文本摘要器
     *
     * @return TextSummarizer 文本摘要器实例
     */
    public function getTextSummarizer(): TextSummarizer
    {
        if ($this->textSummarizer === null) {
            $this->textSummarizer = new TextSummarizer([
                'default_language' => $this->config['default_language']
            ],  $this->getTokenizer()];
            
            if ($this->logger) {
                $this->logger->debug('初始化文本摘要器'];
            }
        }
        
        return $this->textSummarizer;
    }
    
    /**
     * 分词
     *
     * @param string $text 文本
     * @param array $options 选项
     * @return array 分词结果
     */
    public function tokenize(string $text, array $options = []): array
    {
        $tokenizer = $this->getTokenizer(];
        return $tokenizer->tokenize($text, $options];
    }
    
    /**
     * 词性标注
     *
     * @param string $text 文本
     * @param array $options 选项
     * @return array 词性标注结果
     */
    public function tagPOS(string $text, array $options = []): array
    {
        $tokens = $this->tokenize($text, $options];
        $posTagger = $this->getPosTagger(];
        
        $language = $options['language'] ?? $this->config['default_language'];
        return $posTagger->tag($tokens, $language];
    }
    
    /**
     * 命名实体识别
     *
     * @param string $text 文本
     * @param array $options 选项
     * @return array 命名实体识别结果
     */
    public function recognizeEntities(string $text, array $options = []): array
    {
        $tokens = $this->tokenize($text, $options];
        $nerModel = $this->getNerModel(];
        
        $language = $options['language'] ?? $this->config['default_language'];
        return $nerModel->recognize($tokens, $language];
    }
    
    /**
     * 情感分析
     *
     * @param string $text 文本
     * @param array $options 选项
     * @return array 情感分析结果
     */
    public function analyzeSentiment(string $text, array $options = []): array
    {
        $tokens = $this->tokenize($text, $options];
        $sentimentAnalyzer = $this->getSentimentAnalyzer(];
        
        $language = $options['language'] ?? $this->config['default_language'];
        return $sentimentAnalyzer->analyze($text, $tokens, $language];
    }
    
    /**
     * 文本分类
     *
     * @param string $text 文本
     * @param array $options 选项
     * @return array 文本分类结果
     */
    public function classifyText(string $text, array $options = []): array
    {
        $textClassifier = $this->getTextClassifier(];
        return $textClassifier->classify($text, $options];
    }
    
    /**
     * 关键词提取
     *
     * @param string $text 文本
     * @param array $options 选项
     * @return array 关键词提取结果
     */
    public function extractKeywords(string $text, array $options = []): array
    {
        $keywordExtractor = $this->getKeywordExtractor(];
        return $keywordExtractor->extract($text, $options];
    }
    
    /**
     * 文本摘要
     *
     * @param string $text 文本
     * @param string|null $title 标题
     * @param array $options 选项
     * @return array 文本摘要结果
     */
    public function summarizeText(string $text, ?string $title = null, array $options = []): array
    {
        $textSummarizer = $this->getTextSummarizer(];
        return $textSummarizer->summarize($text, $title, $options];
    }
    
    /**
     * 综合分析
     *
     * @param string $text 文本
     * @param array $options 选项
     * @return array 综合分析结果
     */
    public function analyze(string $text, array $options = []): array
    {
        $startTime = microtime(true];
        
        // 合并选项
        $options = array_merge([
            'language' => $this->config['default_language'], 
            'include_tokens' => false,
            'include_pos' => true,
            'include_entities' => true,
            'include_sentiment' => true,
            'include_keywords' => true,
            'include_summary' => true,
            'include_classification' => false
        ],  $options];
        
        $result = [
            'text' => $text,
            'language' => $options['language'], 
            'length' => mb_strlen($text)
        ];
        
        // 分词
        $tokens = $this->tokenize($text, $options];
        if ($options['include_tokens']) {
            $result['tokens'] = $tokens;
        }
        
        // 词性标注
        if ($options['include_pos']) {
            $result['pos_tags'] = $this->getPosTagger()->tag($tokens, $options['language']];
        }
        
        // 命名实体识别
        if ($options['include_entities']) {
            $result['entities'] = $this->getNerModel()->recognize($tokens, $options['language']];
        }
        
        // 情感分析
        if ($options['include_sentiment']) {
            $result['sentiment'] = $this->getSentimentAnalyzer()->analyze($text, $tokens, $options['language']];
        }
        
        // 关键词提取
        if ($options['include_keywords']) {
            $result['keywords'] = $this->getKeywordExtractor()->extract($text, [
                'language' => $options['language']
            ]];
        }
        
        // 文本摘要
        if ($options['include_summary']) {
            $result['summary'] = $this->getTextSummarizer()->summarize($text, null, [
                'language' => $options['language']
            ]];
        }
        
        // 文本分类
        if ($options['include_classification']) {
            $result['classification'] = $this->getTextClassifier()->classify($text, [
                'language' => $options['language']
            ]];
        }
        
        $result['processing_time'] = microtime(true) - $startTime;
        
        return $result;
    }
    
    /**
     * 获取配置
     *
     * @return array 配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * 设置配置
     *
     * @param array $config 配置
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config];
    }
    
    /**
     * 设置分词器
     *
     * @param TokenizerInterface $tokenizer 分词器
     */
    public function setTokenizer(TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }
    
    /**
     * 设置词性标注器
     *
     * @param POSTagger $posTagger 词性标注器
     */
    public function setPosTagger(POSTagger $posTagger): void
    {
        $this->posTagger = $posTagger;
    }
    
    /**
     * 设置命名实体识别模型
     *
     * @param NERModel $nerModel 命名实体识别模型
     */
    public function setNerModel(NERModel $nerModel): void
    {
        $this->nerModel = $nerModel;
    }
    
    /**
     * 设置情感分析器
     *
     * @param SentimentAnalyzer $sentimentAnalyzer 情感分析器
     */
    public function setSentimentAnalyzer(SentimentAnalyzer $sentimentAnalyzer): void
    {
        $this->sentimentAnalyzer = $sentimentAnalyzer;
    }
    
    /**
     * 设置文本分类器
     *
     * @param TextClassifier $textClassifier 文本分类器
     */
    public function setTextClassifier(TextClassifier $textClassifier): void
    {
        $this->textClassifier = $textClassifier;
    }
    
    /**
     * 设置关键词提取器
     *
     * @param KeywordExtractor $keywordExtractor 关键词提取器
     */
    public function setKeywordExtractor(KeywordExtractor $keywordExtractor): void
    {
        $this->keywordExtractor = $keywordExtractor;
    }
    
    /**
     * 设置文本摘要器
     *
     * @param TextSummarizer $textSummarizer 文本摘要器
     */
    public function setTextSummarizer(TextSummarizer $textSummarizer): void
    {
        $this->textSummarizer = $textSummarizer;
    }
}

