<?php
/**
 * 文件名：EnglishTokenizer.php
 * 功能描述：英文分词器 - 实现英文文本分词功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\AI\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\AI\Engines\NLP;

use Exception;
use InvalidArgumentException;

/**
 * 英文分词器
 *
 * 实现英文文本的分词功能
 */
class EnglishTokenizer implements TokenizerInterface
{
    private array $config;
    private array $dictionary;
    private array $stopWords;

    /**
     * 构造函数
     *
     * @param array $config 分词器配置
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->loadResources();
    }
    
    /**
     * 加载资源文件
     */
    private function loadResources(): void
    {
        $this->loadDictionary();
        $this->loadStopWords();
    }

    /**
     * 加载词典
     */
    private function loadDictionary(): void
    {
        // 在实际应用中，这里应该从外部文件或数据库加载词典
        // 为简化演示，这里直接使用内置词典
        $this->dictionary = $this->getBasicDictionary();
        
        // 添加自定义词典
        if (isset($this->config['custom_dictionary']) && is_array($this->config['custom_dictionary'])) {
            $this->dictionary = array_merge($this->dictionary, $this->config['custom_dictionary']);
        }
    }

    /**
     * 获取基础词典
     */
    private function getBasicDictionary(): array
    {
        // 返回一个简单的英文词典作为示例
        return [
            'artificial', 'intelligence', 'machine', 'learning', 'natural', 'language', 'processing',
            'computer', 'vision', 'deep', 'neural', 'network', 'big', 'data', 'cloud', 'computing',
            'blockchain', 'data', 'mining', 'pattern', 'recognition', 'speech', 'recognition',
            'image', 'processing', 'knowledge', 'graph', 'semantic', 'analysis', 'sentiment', 'analysis'
        ];
    }

    /**
     * 加载停用词
     */
    private function loadStopWords(): void
    {
        // 在实际应用中，这里应该从外部文件或数据库加载停用词
        // 为简化演示，这里直接使用内置停用词列表
        $this->stopWords = ['a', 'an', 'the', 'and', 'or', 'but', 'if', 'because', 'as', 'what', 'when', 'where', 'how', 'which', 'who', 'whom'];
        
        // 添加自定义停用词
        if (isset($this->config['custom_stopwords']) && is_array($this->config['custom_stopwords'])) {
            $this->stopWords = array_merge($this->stopWords, $this->config['custom_stopwords']);
        }
    }

    /**
     * 获取默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'algorithm' => 'word_boundary', // 默认使用词边界分词算法
            'remove_stop_words' => false,   // 是否去除停用词
            'return_original' => true,      // 是否返回原始文本
            'preprocess' => true,           // 是否进行预处理
            'postprocess' => true,          // 是否进行后处理
            'lowercase' => true,            // 是否转为小写
        ];
    }

    /**
     * 分词主函数
     *
     * @param string $text 输入文本
     * @return array 分词结果
     */
    public function tokenize(string $text): array
    {
        // 预处理
        if ($this->config['preprocess']) {
            $text = $this->preprocessText($text);
        }
        
        // 根据配置选择分词算法
        $tokens = $this->wordBoundaryTokenize($text);
        
        // 后处理
        if ($this->config['postprocess']) {
            $tokens = $this->postprocessTokens($tokens);
        }
        
        return $tokens;
    }

    /**
     * 文本预处理
     *
     * @param string $text 原始文本
     * @return string 预处理后的文本
     */
    private function preprocessText(string $text): string
    {
        // 转换为小写（可选）
        if ($this->config['lowercase']) {
            $text = mb_strtolower($text, 'UTF-8');
        }
        
        // 去除多余空白字符
        $text = preg_replace('/\s+/u', ' ', $text);
        
        return trim($text);
    }

    /**
     * 词边界分词算法
     *
     * @param string $text 预处理后的文本
     * @return array 分词结果
     */
    private function wordBoundaryTokenize(string $text): array
    {
        $tokens = [];
        
        // 使用正则表达式按词边界分词
        preg_match_all('/\b\w+\b/u', $text, $matches, PREG_OFFSET_CAPTURE);
        
        foreach ($matches[0] as $i => $match) {
            $word = $match[0];
            $position = $match[1];
            
            // 跳过停用词
            if ($this->config['remove_stop_words'] && in_array(mb_strtolower($word, 'UTF-8'), $this->stopWords)) {
                continue;
            }
            
            // 添加到结果
            $tokens[] = [
                'text' => $word,
                'start' => $position,
                'end' => $position + mb_strlen($word, 'UTF-8') - 1,
                'length' => mb_strlen($word, 'UTF-8'),
                'type' => $this->getTokenType($word),
                'is_stop_word' => in_array(mb_strtolower($word, 'UTF-8'), $this->stopWords)
            ];
        }
        
        return $tokens;
    }

    /**
     * 后处理tokens
     *
     * @param array $tokens 分词结果
     * @return array 后处理后的分词结果
     */
    private function postprocessTokens(array $tokens): array
    {
        // 添加token索引
        foreach ($tokens as $i => &$token) {
            $token['index'] = $i;
        }

        return $tokens;
    }

    /**
     * 获取token类型
     *
     * @param string $token token文本
     * @return string token类型
     */
    private function getTokenType(string $token): string
    {
        // 检查是否为数字
        if (preg_match('/^\d+$/u', $token)) {
            return 'number';
        }
        
        // 检查是否为标点符号
        if (preg_match('/^[,.!?:;\'()[\]<>"\']+$/u', $token)) {
            return 'punctuation';
        }
        
        // 检查是否为特殊标记
        if (preg_match('/^[#@]+\w+$/u', $token)) {
            return 'tag';
        }
        
        // 默认为单词
        return 'word';
    }

    /**
     * 获取分词器配置
     *
     * @return array 分词器配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 设置分词器配置
     *
     * @param array $config 分词器配置
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取词典
     *
     * @return array 词典
     */
    public function getDictionary(): array
    {
        return $this->dictionary;
    }

    /**
     * 添加词到词典
     *
     * @param string $word 词
     * @return void
     */
    public function addWord(string $word): void
    {
        if (!in_array($word, $this->dictionary)) {
            $this->dictionary[] = $word;
        }
    }

    /**
     * 获取停用词列表
     *
     * @return array 停用词列表
     */
    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    /**
     * 添加停用词
     *
     * @param string $word 停用词
     * @return void
     */
    public function addStopWord(string $word): void
    {
        if (!in_array($word, $this->stopWords)) {
            $this->stopWords[] = $word;
        }
    }
}
