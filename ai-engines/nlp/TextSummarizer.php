<?php
/**
 * 文件名：TextSummarizer.php
 * 功能描述：文本摘要器 - 实现文本摘要功能
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
 * 文本摘要器
 *
 * 实现文本摘要功能，支持多种摘要算法和语言
 */
class TextSummarizer
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
     * 摘要结果缓存
     */
    private array $cache = [];
    
    /**
     * 分词器
     */
    private ?TokenizerInterface $tokenizer = null;
    
    /**
     * 构造函数
     *
     * @param array $config 配置参数
     * @param TokenizerInterface|null $tokenizer 分词器
     */
    public function __construct(array $config = [], ?TokenizerInterface $tokenizer = null)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->tokenizer = $tokenizer;
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
            'default_algorithm' => 'extractive',
            'max_summary_length' => 200,
            'compression_ratio' => 0.3,
            'use_cache' => true,
            'cache_ttl' => 3600,
            'min_sentence_length' => 10,
            'max_sentence_length' => 200,
            'sentence_similarity_threshold' => 0.5,
            'use_tf_idf' => true,
            'use_position_weight' => true,
            'use_title_weight' => true,
            'use_keyword_weight' => true
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
     * 生成文本摘要
     *
     * @param string $text 原文本
     * @param string|null $title 文本标题
     * @param array $options 选项
     * @return array 摘要结果
     */
    public function summarize(string $text, ?string $title = null, array $options = []): array
    {
        // 合并选项
        $options = array_merge([
            'language' => $this->config['default_language'],
            'algorithm' => $this->config['default_algorithm'],
            'max_length' => $this->config['max_summary_length'],
            'compression_ratio' => $this->config['compression_ratio'],
            'keywords' => []
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
        
        // 根据算法生成摘要
        $summary = [];
        switch ($options['algorithm']) {
            case 'extractive':
                $summary = $this->extractiveSummarize($text, $title, $options);
                break;
            case 'abstractive':
                $summary = $this->abstractiveSummarize($text, $title, $options);
                break;
            case 'keyword':
                $summary = $this->keywordSummarize($text, $options);
                break;
            default:
                throw new InvalidArgumentException("不支持的摘要算法: {$options['algorithm']}");
        }
        
        // 缓存结果
        if ($this->config['use_cache']) {
            $this->cache[$cacheKey] = $summary;
        }
        
        return $summary;
    }
    
    /**
     * 提取式摘要算法
     *
     * @param string $text 原文本
     * @param string|null $title 文本标题
     * @param array $options 选项
     * @return array 摘要结果
     */
    private function extractiveSummarize(string $text, ?string $title, array $options): array
    {
        // 分句
        $sentences = $this->splitSentences($text, $options['language']);
        if (count($sentences) <= 1) {
            return [
                'summary' => $text,
                'sentences' => $sentences,
                'algorithm' => 'extractive',
                'compression_ratio' => 1.0,
                'original_length' => mb_strlen($text),
                'summary_length' => mb_strlen($text)
            ];
        }
        
        // 计算句子权重
        $sentenceScores = [];
        $sentenceTokens = [];
        $allTokens = [];
        
        foreach ($sentences as $index => $sentence) {
            // 分词
            $tokens = $this->tokenize($sentence, $options['language']);
            $sentenceTokens[$index] = $tokens;
            $allTokens = array_merge($allTokens, $tokens);
            
            // 初始分数
            $sentenceScores[$index] = 0;
            
            // 位置权重
            if ($this->config['use_position_weight']) {
                if ($index === 0 || $index === count($sentences) - 1) {
                    $sentenceScores[$index] += 0.5;
                }
            }
            
            // 标题相似度权重
            if ($this->config['use_title_weight'] && $title) {
                $titleTokens = $this->tokenize($title, $options['language']);
                $similarity = $this->calculateSimilarity($titleTokens, $tokens);
                $sentenceScores[$index] += $similarity * 0.5;
            }
        }
        
        // 计算TF-IDF
        if ($this->config['use_tf_idf']) {
            $tfIdfScores = $this->calculateTfIdf($sentenceTokens, $allTokens);
            foreach ($tfIdfScores as $index => $score) {
                $sentenceScores[$index] += $score;
            }
        }
        
        // 关键词权重
        if ($this->config['use_keyword_weight'] && !empty($options['keywords'])) {
            foreach ($sentenceTokens as $index => $tokens) {
                $keywordCount = 0;
                foreach ($tokens as $token) {
                    if (in_array($token, $options['keywords'])) {
                        $keywordCount++;
                    }
                }
                $sentenceScores[$index] += $keywordCount * 0.2;
            }
        }
        
        // 句子相似度
        $sentenceSimilarities = [];
        for ($i = 0; $i < count($sentences); $i++) {
            for ($j = $i + 1; $j < count($sentences); $j++) {
                $similarity = $this->calculateSimilarity($sentenceTokens[$i], $sentenceTokens[$j]);
                if ($similarity > $this->config['sentence_similarity_threshold']) {
                    $sentenceSimilarities[$i][$j] = $similarity;
                    $sentenceSimilarities[$j][$i] = $similarity;
                }
            }
        }
        
        // 根据相似度调整分数
        foreach ($sentenceSimilarities as $i => $similarities) {
            $totalSimilarity = array_sum($similarities);
            $sentenceScores[$i] += $totalSimilarity * 0.1;
        }
        
        // 选择最高分的句子
        arsort($sentenceScores);
        $targetSentenceCount = max(1, ceil(count($sentences) * $options['compression_ratio']));
        $targetSentenceCount = min($targetSentenceCount, $options['max_length'] / 20); // 粗略估计每句平均20个字符
        
        $selectedIndices = array_slice(array_keys($sentenceScores), 0, $targetSentenceCount);
        sort($selectedIndices); // 按原文顺序排序
        
        $summaryText = '';
        $selectedSentences = [];
        foreach ($selectedIndices as $index) {
            $selectedSentences[] = $sentences[$index];
            $summaryText .= $sentences[$index] . ' ';
        }
        
        $summaryText = trim($summaryText);
        if (mb_strlen($summaryText) > $options['max_length']) {
            $summaryText = mb_substr($summaryText, 0, $options['max_length']) . '...';
        }
        
        return [
            'summary' => $summaryText,
            'sentences' => $selectedSentences,
            'algorithm' => 'extractive',
            'compression_ratio' => count($selectedSentences) / count($sentences),
            'original_length' => mb_strlen($text),
            'summary_length' => mb_strlen($summaryText)
        ];
    }
    
    /**
     * 生成式摘要算法
     *
     * @param string $text 原文本
     * @param string|null $title 文本标题
     * @param array $options 选项
     * @return array 摘要结果
     */
    private function abstractiveSummarize(string $text, ?string $title, array $options): array
    {
        // 生成式摘要需要更复杂的NLP模型，这里简化实现
        // 实际应用中可能需要调用外部API或使用深度学习模型
        
        // 先使用提取式摘要获取关键句子
        $extractiveResult = $this->extractiveSummarize($text, $title, $options);
        $keySentences = $extractiveResult['sentences'];
        
        // 简单的句子重组和压缩
        $abstractiveSummary = '';
        $compressedSentences = [];
        
        foreach ($keySentences as $sentence) {
            $compressed = $this->compressSentence($sentence, $options['language']);
            $compressedSentences[] = $compressed;
            $abstractiveSummary .= $compressed . ' ';
        }
        
        $abstractiveSummary = trim($abstractiveSummary);
        if (mb_strlen($abstractiveSummary) > $options['max_length']) {
            $abstractiveSummary = mb_substr($abstractiveSummary, 0, $options['max_length']) . '...';
        }
        
        return [
            'summary' => $abstractiveSummary,
            'sentences' => $compressedSentences,
            'algorithm' => 'abstractive',
            'compression_ratio' => count($compressedSentences) / $this->countSentences($text),
            'original_length' => mb_strlen($text),
            'summary_length' => mb_strlen($abstractiveSummary)
        ];
    }
    
    /**
     * 关键词摘要算法
     *
     * @param string $text 原文本
     * @param array $options 选项
     * @return array 摘要结果
     */
    private function keywordSummarize(string $text, array $options): array
    {
        // 分词
        $tokens = $this->tokenize($text, $options['language']);
        
        // 过滤停用词
        $filteredTokens = $this->filterStopwords($tokens, $options['language']);
        
        // 计算词频
        $wordFrequency = array_count_values($filteredTokens);
        arsort($wordFrequency);
        
        // 选择前N个关键词
        $keywordCount = min(10, ceil(count($wordFrequency) * 0.1));
        $keywords = array_slice(array_keys($wordFrequency), 0, $keywordCount);
        
        // 找包含关键词的句子
        $sentences = $this->splitSentences($text, $options['language']);
        $sentenceScores = [];
        
        foreach ($sentences as $index => $sentence) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (mb_stripos($sentence, $keyword) !== false) {
                    $score += $wordFrequency[$keyword];
                }
            }
            $sentenceScores[$index] = $score;
        }
        
        // 选择最高分的句子
        arsort($sentenceScores);
        $targetSentenceCount = max(1, ceil(count($sentences) * $options['compression_ratio']));
        $targetSentenceCount = min($targetSentenceCount, $options['max_length'] / 20);
        
        $selectedIndices = array_slice(array_keys($sentenceScores), 0, $targetSentenceCount);
        sort($selectedIndices);
        
        $summaryText = '';
        $selectedSentences = [];
        foreach ($selectedIndices as $index) {
            $selectedSentences[] = $sentences[$index];
            $summaryText .= $sentences[$index] . ' ';
        }
        
        $summaryText = trim($summaryText);
        if (mb_strlen($summaryText) > $options['max_length']) {
            $summaryText = mb_substr($summaryText, 0, $options['max_length']) . '...';
        }
        
        return [
            'summary' => $summaryText,
            'sentences' => $selectedSentences,
            'keywords' => $keywords,
            'algorithm' => 'keyword',
            'compression_ratio' => count($selectedSentences) / count($sentences),
            'original_length' => mb_strlen($text),
            'summary_length' => mb_strlen($summaryText)
        ];
    }
    
    /**
     * 分句
     *
     * @param string $text 文本
     * @param string $language 语言
     * @return array 句子数组
     */
    private function splitSentences(string $text, string $language): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        if ($language === 'zh-CN') {
            // 中文分句
            $pattern = '/([。！？…]+)(?=[\s\S])/u';
            $sentences = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            $result = [];
            for ($i = 0; $i < count($sentences) - 1; $i += 2) {
                if (isset($sentences[$i + 1])) {
                    $result[] = $sentences[$i] . $sentences[$i + 1];
                } else {
                    $result[] = $sentences[$i];
                }
            }
            
            // 处理最后一个可能没有标点的句子
            if (count($sentences) % 2 === 1) {
                $lastSentence = end($sentences);
                if (trim($lastSentence) !== '') {
                    $result[] = $lastSentence;
                }
            }
        } else {
            // 英文分句
            $pattern = '/(?<=[.!?])\s+/';
            $result = preg_split($pattern, $text);
        }
        
        // 过滤空句子和过短句子
        $result = array_filter($result, function($sentence) {
            return trim($sentence) !== '' && mb_strlen(trim($sentence)) >= $this->config['min_sentence_length'];
        });
        
        // 截断过长句子
        foreach ($result as &$sentence) {
            if (mb_strlen($sentence) > $this->config['max_sentence_length']) {
                $sentence = mb_substr($sentence, 0, $this->config['max_sentence_length']) . '...';
            }
        }
        
        return array_values($result);
    }
    
    /**
     * 分词
     *
     * @param string $text 文本
     * @param string $language 语言
     * @return array 词元数组
     */
    private function tokenize(string $text, string $language): array
    {
        if ($this->tokenizer) {
            $tokens = $this->tokenizer->tokenize($text);
            return array_column($tokens, 'text');
        }
        
        // 简单分词
        if ($language === 'zh-CN') {
            // 中文按字符分词
            $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
            return array_filter($chars, function($char) {
                return preg_match('/[\p{Han}\p{L}\p{N}]/u', $char);
            });
        } else {
            // 英文按空格分词
            $words = preg_split('/\s+/', $text);
            return array_filter($words, function($word) {
                return preg_match('/[\p{L}\p{N}]/u', $word);
            });
        }
    }
    
    /**
     * 过滤停用词
     *
     * @param array $tokens 词元数组
     * @param string $language 语言
     * @return array 过滤后的词元数组
     */
    private function filterStopwords(array $tokens, string $language): array
    {
        if (!isset($this->stopwords[$language])) {
            return $tokens;
        }
        
        return array_filter($tokens, function($token) use ($language) {
            return !in_array(mb_strtolower($token), $this->stopwords[$language]);
        });
    }
    
    /**
     * 计算TF-IDF
     *
     * @param array $sentenceTokens 句子词元数组
     * @param array $allTokens 所有词元
     * @return array 句子TF-IDF分数
     */
    private function calculateTfIdf(array $sentenceTokens, array $allTokens): array
    {
        $documentCount = count($sentenceTokens);
        $wordFrequency = array_count_values($allTokens);
        $wordInDocuments = [];
        
        // 计算每个词出现在几个句子中
        foreach ($wordFrequency as $word => $freq) {
            $wordInDocuments[$word] = 0;
            foreach ($sentenceTokens as $tokens) {
                if (in_array($word, $tokens)) {
                    $wordInDocuments[$word]++;
                }
            }
        }
        
        // 计算每个句子的TF-IDF分数
        $scores = [];
        foreach ($sentenceTokens as $index => $tokens) {
            $score = 0;
            $sentenceWordFreq = array_count_values($tokens);
            
            foreach ($sentenceWordFreq as $word => $freq) {
                $tf = $freq / count($tokens);
                $idf = log($documentCount / ($wordInDocuments[$word] + 1));
                $score += $tf * $idf;
            }
            
            $scores[$index] = $score;
        }
        
        return $scores;
    }
    
    /**
     * 计算相似度
     *
     * @param array $tokens1 词元数组1
     * @param array $tokens2 词元数组2
     * @return float 相似度
     */
    private function calculateSimilarity(array $tokens1, array $tokens2): float
    {
        if (empty($tokens1) || empty($tokens2)) {
            return 0;
        }
        
        $intersection = array_intersect($tokens1, $tokens2);
        return count($intersection) / sqrt(count($tokens1) * count($tokens2));
    }
    
    /**
     * 压缩句子
     *
     * @param string $sentence 原句子
     * @param string $language 语言
     * @return string 压缩后的句子
     */
    private function compressSentence(string $sentence, string $language): string
    {
        $tokens = $this->tokenize($sentence, $language);
        $filteredTokens = $this->filterStopwords($tokens, $language);
        
        if (count($filteredTokens) < count($tokens) * 0.5) {
            // 如果过滤后词元太少，保留原句
            return $sentence;
        }
        
        if ($language === 'zh-CN') {
            // 中文句子压缩
            return implode('', $filteredTokens);
        } else {
            // 英文句子压缩
            return implode(' ', $filteredTokens);
        }
    }
    
    /**
     * 计算句子数量
     *
     * @param string $text 文本
     * @return int 句子数量
     */
    private function countSentences(string $text): int
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        return preg_match_all('/[.!?。！？…]+/u', $text) + 1;
    }
    
    /**
     * 检测语言
     *
     * @param string $text 文本
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
     * 设置分词器
     *
     * @param TokenizerInterface $tokenizer 分词器
     */
    public function setTokenizer(TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }
    
    /**
     * 清除缓存
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
