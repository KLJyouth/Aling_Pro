<?php
declare(strict_types=1];

/**
 * �ļ�����TextAnalysisEngine.php
 * �����������ı��������� - ���ɶ���NLP���ܵĺ�������
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�2025-01-XX
 * �汾��1.0.0
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
 * �ı���������
 *
 * ���ɶ���NLP���ܵĺ������棬�����ִʡ����Ա�ע������ʵ��ʶ����з������ı����ࡢ�ؼ�����ȡ���ı�ժҪ�ȹ���
 */
class TextAnalysisEngine
{
    /**
     * ���ò���
     */
    private array $config;

    /**
     * �ִ���
     */
    private ?TokenizerInterface $tokenizer = null;

    /**
     * ���Ա�ע��
     */
    private ?POSTagger $posTagger = null;

    /**
     * ����ʵ��ʶ��ģ��
     */
    private ?NERModel $nerModel = null;

    /**
     * ��з�����
     */
    private ?SentimentAnalyzer $sentimentAnalyzer = null;

    /**
     * �ı�������
     */
    private ?TextClassifier $textClassifier = null;

    /**
     * �ؼ�����ȡ��
     */
    private ?KeywordExtractor $keywordExtractor = null;

    /**
     * �ı�ժҪ��
     */
    private ?TextSummarizer $textSummarizer = null;

    /**
     * ��־��¼��
     */
    private ?LoggerInterface $logger = null;
    
    /**
     * ���캯��
     *
     * @param array $config ���ò���
     * @param LoggerInterface|null $logger ��־��¼��
     */
    public function __construct(array $config = [],  ?LoggerInterface $logger = null)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->logger = $logger;
        
        if ($this->logger) {
            $this->logger->info('�ı����������ʼ���ɹ�', [
                'default_language' => $this->config['default_language']
            ]];
        }
    }
    
    /**
     * ��ȡĬ������
     *
     * @return array Ĭ������
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
     * ��ȡ�ִ���
     *
     * @return TokenizerInterface �ִ���ʵ��
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
                $this->logger->debug('��ʼ���ִ���', ['type' => $tokenizerType]];
            }
        }
        
        return $this->tokenizer;
    }
    
    /**
     * ��ȡ���Ա�ע��
     *
     * @return POSTagger ���Ա�ע��ʵ��
     */
    public function getPosTagger(): POSTagger
    {
        if ($this->posTagger === null) {
            $this->posTagger = new POSTagger([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('��ʼ�����Ա�ע��'];
            }
        }
        
        return $this->posTagger;
    }
    
    /**
     * ��ȡ����ʵ��ʶ��ģ��
     *
     * @return NERModel ����ʵ��ʶ��ģ��ʵ��
     */
    public function getNerModel(): NERModel
    {
        if ($this->nerModel === null) {
            $this->nerModel = new NERModel([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('��ʼ������ʵ��ʶ��ģ��'];
            }
        }
        
        return $this->nerModel;
    }
    
    /**
     * ��ȡ��з�����
     *
     * @return SentimentAnalyzer ��з�����ʵ��
     */
    public function getSentimentAnalyzer(): SentimentAnalyzer
    {
        if ($this->sentimentAnalyzer === null) {
            $this->sentimentAnalyzer = new SentimentAnalyzer([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('��ʼ����з�����'];
            }
        }
        
        return $this->sentimentAnalyzer;
    }
    
    /**
     * ��ȡ�ı�������
     *
     * @return TextClassifier �ı�������ʵ��
     */
    public function getTextClassifier(): TextClassifier
    {
        if ($this->textClassifier === null) {
            $this->textClassifier = new TextClassifier([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('��ʼ���ı�������'];
            }
        }
        
        return $this->textClassifier;
    }
    
    /**
     * ��ȡ�ؼ�����ȡ��
     *
     * @return KeywordExtractor �ؼ�����ȡ��ʵ��
     */
    public function getKeywordExtractor(): KeywordExtractor
    {
        if ($this->keywordExtractor === null) {
            $this->keywordExtractor = new KeywordExtractor([
                'default_language' => $this->config['default_language']
            ]];
            
            if ($this->logger) {
                $this->logger->debug('��ʼ���ؼ�����ȡ��'];
            }
        }
        
        return $this->keywordExtractor;
    }
    
    /**
     * ��ȡ�ı�ժҪ��
     *
     * @return TextSummarizer �ı�ժҪ��ʵ��
     */
    public function getTextSummarizer(): TextSummarizer
    {
        if ($this->textSummarizer === null) {
            $this->textSummarizer = new TextSummarizer([
                'default_language' => $this->config['default_language']
            ],  $this->getTokenizer()];
            
            if ($this->logger) {
                $this->logger->debug('��ʼ���ı�ժҪ��'];
            }
        }
        
        return $this->textSummarizer;
    }
    
    /**
     * �ִ�
     *
     * @param string $text �ı�
     * @param array $options ѡ��
     * @return array �ִʽ��
     */
    public function tokenize(string $text, array $options = []): array
    {
        $tokenizer = $this->getTokenizer(];
        return $tokenizer->tokenize($text, $options];
    }
    
    /**
     * ���Ա�ע
     *
     * @param string $text �ı�
     * @param array $options ѡ��
     * @return array ���Ա�ע���
     */
    public function tagPOS(string $text, array $options = []): array
    {
        $tokens = $this->tokenize($text, $options];
        $posTagger = $this->getPosTagger(];
        
        $language = $options['language'] ?? $this->config['default_language'];
        return $posTagger->tag($tokens, $language];
    }
    
    /**
     * ����ʵ��ʶ��
     *
     * @param string $text �ı�
     * @param array $options ѡ��
     * @return array ����ʵ��ʶ����
     */
    public function recognizeEntities(string $text, array $options = []): array
    {
        $tokens = $this->tokenize($text, $options];
        $nerModel = $this->getNerModel(];
        
        $language = $options['language'] ?? $this->config['default_language'];
        return $nerModel->recognize($tokens, $language];
    }
    
    /**
     * ��з���
     *
     * @param string $text �ı�
     * @param array $options ѡ��
     * @return array ��з������
     */
    public function analyzeSentiment(string $text, array $options = []): array
    {
        $tokens = $this->tokenize($text, $options];
        $sentimentAnalyzer = $this->getSentimentAnalyzer(];
        
        $language = $options['language'] ?? $this->config['default_language'];
        return $sentimentAnalyzer->analyze($text, $tokens, $language];
    }
    
    /**
     * �ı�����
     *
     * @param string $text �ı�
     * @param array $options ѡ��
     * @return array �ı�������
     */
    public function classifyText(string $text, array $options = []): array
    {
        $textClassifier = $this->getTextClassifier(];
        return $textClassifier->classify($text, $options];
    }
    
    /**
     * �ؼ�����ȡ
     *
     * @param string $text �ı�
     * @param array $options ѡ��
     * @return array �ؼ�����ȡ���
     */
    public function extractKeywords(string $text, array $options = []): array
    {
        $keywordExtractor = $this->getKeywordExtractor(];
        return $keywordExtractor->extract($text, $options];
    }
    
    /**
     * �ı�ժҪ
     *
     * @param string $text �ı�
     * @param string|null $title ����
     * @param array $options ѡ��
     * @return array �ı�ժҪ���
     */
    public function summarizeText(string $text, ?string $title = null, array $options = []): array
    {
        $textSummarizer = $this->getTextSummarizer(];
        return $textSummarizer->summarize($text, $title, $options];
    }
    
    /**
     * �ۺϷ���
     *
     * @param string $text �ı�
     * @param array $options ѡ��
     * @return array �ۺϷ������
     */
    public function analyze(string $text, array $options = []): array
    {
        $startTime = microtime(true];
        
        // �ϲ�ѡ��
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
        
        // �ִ�
        $tokens = $this->tokenize($text, $options];
        if ($options['include_tokens']) {
            $result['tokens'] = $tokens;
        }
        
        // ���Ա�ע
        if ($options['include_pos']) {
            $result['pos_tags'] = $this->getPosTagger()->tag($tokens, $options['language']];
        }
        
        // ����ʵ��ʶ��
        if ($options['include_entities']) {
            $result['entities'] = $this->getNerModel()->recognize($tokens, $options['language']];
        }
        
        // ��з���
        if ($options['include_sentiment']) {
            $result['sentiment'] = $this->getSentimentAnalyzer()->analyze($text, $tokens, $options['language']];
        }
        
        // �ؼ�����ȡ
        if ($options['include_keywords']) {
            $result['keywords'] = $this->getKeywordExtractor()->extract($text, [
                'language' => $options['language']
            ]];
        }
        
        // �ı�ժҪ
        if ($options['include_summary']) {
            $result['summary'] = $this->getTextSummarizer()->summarize($text, null, [
                'language' => $options['language']
            ]];
        }
        
        // �ı�����
        if ($options['include_classification']) {
            $result['classification'] = $this->getTextClassifier()->classify($text, [
                'language' => $options['language']
            ]];
        }
        
        $result['processing_time'] = microtime(true) - $startTime;
        
        return $result;
    }
    
    /**
     * ��ȡ����
     *
     * @return array ����
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * ��������
     *
     * @param array $config ����
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config];
    }
    
    /**
     * ���÷ִ���
     *
     * @param TokenizerInterface $tokenizer �ִ���
     */
    public function setTokenizer(TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }
    
    /**
     * ���ô��Ա�ע��
     *
     * @param POSTagger $posTagger ���Ա�ע��
     */
    public function setPosTagger(POSTagger $posTagger): void
    {
        $this->posTagger = $posTagger;
    }
    
    /**
     * ��������ʵ��ʶ��ģ��
     *
     * @param NERModel $nerModel ����ʵ��ʶ��ģ��
     */
    public function setNerModel(NERModel $nerModel): void
    {
        $this->nerModel = $nerModel;
    }
    
    /**
     * ������з�����
     *
     * @param SentimentAnalyzer $sentimentAnalyzer ��з�����
     */
    public function setSentimentAnalyzer(SentimentAnalyzer $sentimentAnalyzer): void
    {
        $this->sentimentAnalyzer = $sentimentAnalyzer;
    }
    
    /**
     * �����ı�������
     *
     * @param TextClassifier $textClassifier �ı�������
     */
    public function setTextClassifier(TextClassifier $textClassifier): void
    {
        $this->textClassifier = $textClassifier;
    }
    
    /**
     * ���ùؼ�����ȡ��
     *
     * @param KeywordExtractor $keywordExtractor �ؼ�����ȡ��
     */
    public function setKeywordExtractor(KeywordExtractor $keywordExtractor): void
    {
        $this->keywordExtractor = $keywordExtractor;
    }
    
    /**
     * �����ı�ժҪ��
     *
     * @param TextSummarizer $textSummarizer �ı�ժҪ��
     */
    public function setTextSummarizer(TextSummarizer $textSummarizer): void
    {
        $this->textSummarizer = $textSummarizer;
    }
}

