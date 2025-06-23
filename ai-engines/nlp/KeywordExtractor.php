<?php
declare(strict_types=1);

/**
 * 文件名：KeywordExtractor.php
 * 功能描述：关键词提取器 - 实现文本关键词提取功能
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

/**
 * 关键词提取器
 *
 * 实现文本关键词提取功能，支持多种提取算法和语言
 */
class KeywordExtractor
{
    /**
     * 配置参数
     */
    private array $config;

    /**
     * 停用词列表
     */
    private array $stopwords = [];

    /**
     * 关键词提取结果缓存
     */
    private array $cache = [];
    
    /**
     * 构造函数
     *
     * @param array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->loadStopwords();
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
            'default_algorithm' => 'tfidf',
            'max_keywords' => 10,
            'min_word_length' => 2,
            'use_cache' => true,
            'cache_ttl' => 3600
        ];
    }
    
    /**
     * 加载停用词
     */
    private function loadStopwords(): void
    {
        // 英文停用词
        $this->stopwords['en-US'] = [
            'a', 'an', 'the', 'and', 'or', 'but', 'if', 'then', 'else', 'when',
            'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to', 'into', 'with',
            'is', 'am', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing',
            'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them',
            'this', 'that', 'these', 'those', 'my', 'your', 'his', 'her', 'its', 'our', 'their'
        ];
        
        // 中文停用词
        $this->stopwords['zh-CN'] = [
            '的', '了', '和', '是', '就', '都', '而', '及', '与', '这', '那', '有', '在', '中',
            '为', '对', '也', '以', '之', '于', '上', '下', '但', '如', '因', '由', '所', '已',
            '被', '其', '从', '或', '某', '各', '每', '当', '我', '你', '他', '她', '它', '们'
        ];
    }
    
    /**
     * 提取关键词
     *
     * @param string $text 文本内容
     * @param array $options 选项
     * @return array 关键词数组
     */
    public function extract(string $text, array $options = []): array
    {
        // 合并选项
        $options = array_merge([
            'language' => $this->config['default_language'],
            'algorithm' => $this->config['default_algorithm'],
            'max_keywords' => $this->config['max_keywords'],
            'min_word_length' => $this->config['min_word_length']
        ], $options);
        
        // 检查缓存
        $cacheKey = md5($text . json_encode($options));
        if ($this->config['use_cache'] && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // 检测语言
        if ($options['language'] === 'auto') {
            $options['language'] = $this->detectLanguage($text);
        }
        
        // 根据算法提取关键词
        $keywords = [];
        switch ($options['algorithm']) {
            case 'tfidf':
                $keywords = $this->extractByTfIdf($text, $options);
                break;
            case 'textrank':
                $keywords = $this->extractByTextRank($text, $options);
                break;
            case 'rake':
                $keywords = $this->extractByRake($text, $options);
                break;
            default:
                throw new InvalidArgumentException("不支持的关键词提取算法: {$options['algorithm']}");
        }
        
        // 缓存结果
        if ($this->config['use_cache']) {
            $this->cache[$cacheKey] = $keywords;
        }
        
        return $keywords;
    }
    
    /**
     * 使用TF-IDF算法提取关键词
     *
     * @param string $text 文本内容
     * @param array $options 选项
     * @return array 关键词数组
     */
    private function extractByTfIdf(string $text, array $options): array
    {
        // 分词
        $tokens = $this->tokenize($text, $options['language']);
        
        // 过滤停用词和短词
        $filteredTokens = $this->filterTokens($tokens, $options);
        
        // 计算词频
        $termFrequency = array_count_values($filteredTokens);
        
        // 计算TF-IDF值
        $tfidfScores = [];
        $documentCount = 1; // 在实际应用中，这应该是语料库中的文档数量
        
        foreach ($termFrequency as $term => $frequency) {
            // 简化版TF-IDF计算，在实际应用中应该使用更复杂的公式
            $tf = $frequency / count($filteredTokens);
            $idf = log($documentCount / 1); // 简化版IDF
            $tfidfScores[$term] = $tf * $idf;
        }
        
        // 按分数排序
        arsort($tfidfScores);
        
        // 提取前N个关键词
        $keywords = [];
        $count = 0;
        foreach ($tfidfScores as $term => $score) {
            if ($count >= $options['max_keywords']) {
                break;
            }
            
            $keywords[] = [
                'keyword' => $term,
                'score' => round($score, 4),
                'algorithm' => 'tfidf'
            ];
            
            $count++;
        }
        
        return $keywords;
    }
    
    /**
     * 使用TextRank算法提取关键词
     *
     * @param string $text 文本内容
     * @param array $options 选项
     * @return array 关键词数组
     */
    private function extractByTextRank(string $text, array $options): array
    {
        // 分词
        $tokens = $this->tokenize($text, $options['language']);
        
        // 过滤停用词和短词
        $filteredTokens = $this->filterTokens($tokens, $options);
        
        // 构建词共现矩阵
        $cooccurrenceMatrix = [];
        $window = 5; // 共现窗口大小
        
        for ($i = 0; $i < count($filteredTokens); $i++) {
            $word = $filteredTokens[$i];
            
            // 初始化矩阵
            if (!isset($cooccurrenceMatrix[$word])) {
                $cooccurrenceMatrix[$word] = [];
            }
            
            // 检查窗口内的词
            for ($j = max(0, $i - $window); $j <= min(count($filteredTokens) - 1, $i + $window); $j++) {
                if ($i == $j) {
                    continue;
                }
                
                $coword = $filteredTokens[$j];
                
                if (!isset($cooccurrenceMatrix[$word][$coword])) {
                    $cooccurrenceMatrix[$word][$coword] = 0;
                }
                
                $cooccurrenceMatrix[$word][$coword]++;
            }
        }
        
        // 初始化TextRank分数
        $scores = [];
        $uniqueTokens = array_unique($filteredTokens);
        
        foreach ($uniqueTokens as $token) {
            $scores[$token] = 1.0;
        }
        
        // TextRank迭代
        $damping = 0.85;
        $iterations = 10;
        $threshold = 0.0001;
        
        for ($iter = 0; $iter < $iterations; $iter++) {
            $newScores = [];
            $maxChange = 0;
            
            foreach ($uniqueTokens as $token) {
                $newScore = 1 - $damping;
                
                foreach ($cooccurrenceMatrix as $coword => $edges) {
                    if (isset($edges[$token])) {
                        $weight = $edges[$token];
                        $outSum = array_sum($cooccurrenceMatrix[$coword]);
                        $newScore += $damping * $weight * $scores[$coword] / $outSum;
                    }
                }
                
                $change = abs($newScore - $scores[$token]);
                $maxChange = max($maxChange, $change);
                $newScores[$token] = $newScore;
            }
            
            $scores = $newScores;
            
            // 如果变化小于阈值，提前结束迭代
            if ($maxChange < $threshold) {
                break;
            }
        }
        
        // 按分数排序
        arsort($scores);
        
        // 提取前N个关键词
        $keywords = [];
        $count = 0;
        foreach ($scores as $term => $score) {
            if ($count >= $options['max_keywords']) {
                break;
            }
            
            $keywords[] = [
                'keyword' => $term,
                'score' => round($score, 4),
                'algorithm' => 'textrank'
            ];
            
            $count++;
        }
        
        return $keywords;
    }
    
    /**
     * 使用RAKE算法提取关键词
     *
     * @param string $text 文本内容
     * @param array $options 选项
     * @return array 关键词数组
     */
    private function extractByRake(string $text, array $options): array
    {
        // 分句
        $sentences = $this->splitSentences($text, $options['language']);
        
        // 提取候选短语
        $candidatePhrases = [];
        
        foreach ($sentences as $sentence) {
            // 使用停用词和标点符号作为分隔符
            $phrases = preg_split('/[' . $this->getPunctuationPattern() . ']+/u', $sentence);
            
            foreach ($phrases as $phrase) {
                $phrase = trim($phrase);
                if (empty($phrase)) {
                    continue;
                }
                
                $words = explode(' ', $phrase);
                $filteredWords = [];
                
                // 过滤停用词和短词
                foreach ($words as $word) {
                    $word = trim($word);
                    if (!empty($word) && 
                        !in_array(mb_strtolower($word), $this->stopwords[$options['language']]) && 
                        mb_strlen($word) >= $options['min_word_length']) {
                        $filteredWords[] = $word;
                    }
                }
                
                if (!empty($filteredWords)) {
                    $candidatePhrases[] = implode(' ', $filteredWords);
                }
            }
        }
        
        // 计算词频和词共现度
        $wordFrequency = [];
        $wordDegree = [];
        
        foreach ($candidatePhrases as $phrase) {
            $words = explode(' ', $phrase);
            $wordCount = count($words);
            
            foreach ($words as $word) {
                if (!isset($wordFrequency[$word])) {
                    $wordFrequency[$word] = 0;
                    $wordDegree[$word] = 0;
                }
                
                $wordFrequency[$word]++;
                $wordDegree[$word] += $wordCount;
            }
        }
        
        // 计算每个词的分数
        $wordScores = [];
        foreach ($wordFrequency as $word => $freq) {
            $wordScores[$word] = $wordDegree[$word] / $freq;
        }
        
        // 计算短语分数
        $phraseScores = [];
        foreach ($candidatePhrases as $phrase) {
            $words = explode(' ', $phrase);
            $score = 0;
            
            foreach ($words as $word) {
                $score += $wordScores[$word];
            }
            
            $phraseScores[$phrase] = $score;
        }
        
        // 按分数排序
        arsort($phraseScores);
        
        // 提取前N个关键短语
        $keywords = [];
        $count = 0;
        foreach ($phraseScores as $phrase => $score) {
            if ($count >= $options['max_keywords']) {
                break;
            }
            
            $keywords[] = [
                'keyword' => $phrase,
                'score' => round($score, 4),
                'algorithm' => 'rake'
            ];
            
            $count++;
        }
        
        return $keywords;
    }
    
    /**
     * 分词
     *
     * @param string $text 文本内容
     * @param string $language 语言
     * @return array 词元数组
     */
    private function tokenize(string $text, string $language): array
    {
        // 简单分词实现，实际应用中应该使用专业的分词器
        if ($language === 'zh-CN') {
            // 中文分词（简化版）
            $text = preg_replace('/[^\p{Han}]/u', ' ', $text);
            $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
            return array_filter($chars);
        } else {
            // 英文分词
            $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
            return array_filter(explode(' ', $text));
        }
    }
    
    /**
     * 过滤词元
     *
     * @param array $tokens 词元数组
     * @param array $options 选项
     * @return array 过滤后的词元数组
     */
    private function filterTokens(array $tokens, array $options): array
    {
        $language = $options['language'];
        $minWordLength = $options['min_word_length'];
        
        return array_filter($tokens, function($token) use ($language, $minWordLength) {
            return mb_strlen($token) >= $minWordLength && 
                   !in_array(mb_strtolower($token), $this->stopwords[$language]);
        });
    }
    
    /**
     * 分句
     *
     * @param string $text 文本内容
     * @param string $language 语言
     * @return array 句子数组
     */
    private function splitSentences(string $text, string $language): array
    {
        if ($language === 'zh-CN') {
            // 中文分句
            return preg_split('/[。！？]/u', $text);
        } else {
            // 英文分句
            return preg_split('/[.!?]/u', $text);
        }
    }
    
    /**
     * 获取标点符号正则表达式
     *
     * @return string 正则表达式
     */
    private function getPunctuationPattern(): string
    {
        return '\p{P}\s';
    }
    
    /**
     * 检测语言
     *
     * @param string $text 文本内容
     * @return string 语言代码
     */
    private function detectLanguage(string $text): string
    {
        // 简单语言检测：计算中文字符比例
        $totalChars = mb_strlen($text);
        if ($totalChars === 0) {
            return $this->config['default_language'];
        }
        
        $chineseChars = preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $text);
        $chineseRatio = $chineseChars / $totalChars;
        
        return $chineseRatio > 0.1 ? 'zh-CN' : 'en-US';
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
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * 清除缓存
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
