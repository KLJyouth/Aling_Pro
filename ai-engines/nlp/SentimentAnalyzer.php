<?php
/**
 * 文件名：SentimentAnalyzer.php
 * 功能描述：情感分析器 - 实现文本情感分析功能
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
 * 情感分析器
 *
 * 实现文本的情感分析功能，支持多种语言和分析方法
 */
class SentimentAnalyzer
{
    /**
     * 配置参数
     */
    private array $config;

    /**
     * 英文情感词典
     */
    private array $englishLexicon;

    /**
     * 中文情感词典
     */
    private array $chineseLexicon;

    /**
     * 情感分析结果缓存
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
        $this->loadResources();
    }

    /**
     * 加载资源
     */
    private function loadResources(): void
    {
        $this->loadEnglishLexicon();
        $this->loadChineseLexicon();
    }

    /**
     * 加载英文情感词典
     */
    private function loadEnglishLexicon(): void
    {
        // 简化版的英文情感词典
        $this->englishLexicon = [
            // 正面情感词
            'positive' => [
                'good' => 0.8,
                'great' => 0.9,
                'excellent' => 1.0,
                'wonderful' => 0.9,
                'amazing' => 0.9,
                'fantastic' => 0.9,
                'terrific' => 0.8,
                'outstanding' => 0.8,
                'superb' => 0.9,
                'awesome' => 0.8,
                'best' => 0.9,
                'better' => 0.7,
                'happy' => 0.8,
                'glad' => 0.7,
                'pleased' => 0.7,
                'satisfied' => 0.7,
                'enjoy' => 0.7,
                'like' => 0.6,
                'love' => 0.9,
                'adore' => 0.8,
                'favorite' => 0.7,
                'perfect' => 0.9,
                'pleasant' => 0.6,
                'impressive' => 0.7,
                'beautiful' => 0.7,
                'delightful' => 0.8,
                'positive' => 0.7,
                'recommended' => 0.7,
                'worth' => 0.6,
                'valuable' => 0.6,
                'correct' => 0.6,
                'right' => 0.6,
                'success' => 0.7,
                'successful' => 0.7,
                'win' => 0.7,
                'winning' => 0.7
            ],
            // 负面情感词
            'negative' => [
                'bad' => -0.8,
                'terrible' => -0.9,
                'horrible' => -0.9,
                'awful' => -0.8,
                'poor' => -0.7,
                'disappointing' => -0.7,
                'disappointed' => -0.7,
                'worst' => -0.9,
                'worse' => -0.7,
                'sad' => -0.7,
                'unhappy' => -0.8,
                'angry' => -0.8,
                'upset' => -0.7,
                'annoyed' => -0.6,
                'annoying' => -0.6,
                'hate' => -0.9,
                'dislike' => -0.7,
                'awful' => -0.8,
                'disgusting' => -0.8,
                'unpleasant' => -0.6,
                'negative' => -0.7,
                'avoid' => -0.6,
                'problem' => -0.6,
                'issue' => -0.5,
                'error' => -0.6,
                'mistake' => -0.6,
                'wrong' => -0.6,
                'fail' => -0.7,
                'failure' => -0.7,
                'lose' => -0.6,
                'lost' => -0.6,
                'boring' => -0.6,
                'stupid' => -0.7,
                'useless' => -0.7,
                'waste' => -0.7,
                'expensive' => -0.5,
                'overpriced' => -0.6
            ],
            // 程度副词
            'intensifiers' => [
                'very' => 1.5,
                'extremely' => 2.0,
                'really' => 1.3,
                'so' => 1.3,
                'too' => 1.2,
                'absolutely' => 1.8,
                'completely' => 1.6,
                'highly' => 1.4,
                'totally' => 1.7,
                'quite' => 1.2,
                'rather' => 1.1,
                'somewhat' => 0.8,
                'slightly' => 0.7
            ],
            // 否定词
            'negations' => [
                'not' => -1.0,
                "don't" => -1.0,
                "doesn't" => -1.0,
                "didn't" => -1.0,
                "won't" => -1.0,
                "wouldn't" => -1.0,
                "can't" => -1.0,
                "couldn't" => -1.0,
                "shouldn't" => -1.0,
                "isn't" => -1.0,
                "aren't" => -1.0,
                "wasn't" => -1.0,
                "weren't" => -1.0,
                "haven't" => -1.0,
                "hasn't" => -1.0,
                "hadn't" => -1.0,
                'never' => -1.0,
                'no' => -1.0,
                'nobody' => -1.0,
                'nothing' => -1.0,
                'nowhere' => -1.0,
                'neither' => -1.0,
                'nor' => -1.0
            ]
        ];
    }

    /**
     * 加载中文情感词典
     */
    private function loadChineseLexicon(): void
    {
        // 简化版的中文情感词典
        $this->chineseLexicon = [
            // 正面情感词
            'positive' => [
                '好' => 0.8,
                '棒' => 0.8,
                '优秀' => 0.9,
                '优质' => 0.8,
                '精彩' => 0.8,
                '出色' => 0.8,
                '卓越' => 0.9,
                '杰出' => 0.9,
                '完美' => 0.9,
                '绝佳' => 0.9,
                '满意' => 0.7,
                '喜欢' => 0.7,
                '爱' => 0.9,
                '赞' => 0.8,
                '赞赏' => 0.8,
                '赞美' => 0.8,
                '表扬' => 0.7,
                '推荐' => 0.7,
                '值得' => 0.6,
                '高兴' => 0.7,
                '快乐' => 0.8,
                '开心' => 0.8,
                '愉快' => 0.7,
                '舒适' => 0.6,
                '舒服' => 0.6,
                '舒心' => 0.7,
                '顺利' => 0.6,
                '成功' => 0.7,
                '胜利' => 0.7,
                '优惠' => 0.6,
                '便宜' => 0.6,
                '实惠' => 0.6,
                '划算' => 0.6,
                '漂亮' => 0.7,
                '美' => 0.7,
                '美丽' => 0.7,
                '美好' => 0.8,
                '华丽' => 0.7,
                '精致' => 0.7
            ],
            // 负面情感词
            'negative' => [
                '差' => -0.8,
                '糟' => -0.8,
                '糟糕' => -0.8,
                '差劲' => -0.8,
                '坏' => -0.7,
                '不好' => -0.7,
                '劣质' => -0.8,
                '低劣' => -0.8,
                '失望' => -0.7,
                '不满' => -0.7,
                '不满意' => -0.7,
                '不喜欢' => -0.7,
                '讨厌' => -0.8,
                '厌恶' => -0.8,
                '憎恨' => -0.9,
                '恨' => -0.9,
                '批评' => -0.6,
                '责备' => -0.7,
                '抱怨' => -0.7,
                '投诉' => -0.7,
                '难过' => -0.7,
                '伤心' => -0.8,
                '痛苦' => -0.8,
                '悲伤' => -0.8,
                '悲痛' => -0.9,
                '生气' => -0.8,
                '愤怒' => -0.9,
                '恼火' => -0.8,
                '烦' => -0.6,
                '烦人' => -0.7,
                '烦恼' => -0.7,
                '麻烦' => -0.6,
                '困难' => -0.6,
                '问题' => -0.5,
                '缺点' => -0.6,
                '缺陷' => -0.7,
                '错误' => -0.6,
                '失败' => -0.7,
                '失利' => -0.7,
                '贵' => -0.5,
                '昂贵' => -0.6,
                '不值' => -0.7,
                '不值得' => -0.7,
                '浪费' => -0.7
            ],
            // 程度副词
            'intensifiers' => [
                '很' => 1.5,
                '非常' => 1.8,
                '特别' => 1.6,
                '格外' => 1.6,
                '极' => 1.9,
                '极其' => 2.0,
                '极度' => 2.0,
                '极为' => 1.9,
                '十分' => 1.7,
                '分外' => 1.5,
                '更' => 1.4,
                '更加' => 1.5,
                '越发' => 1.5,
                '愈发' => 1.5,
                '愈加' => 1.5,
                '尤其' => 1.6,
                '相当' => 1.4,
                '颇' => 1.3,
                '颇为' => 1.4,
                '太' => 1.5,
                '挺' => 1.3,
                '蛮' => 1.2,
                '相当' => 1.4,
                '稍' => 0.8,
                '稍微' => 0.7,
                '有点' => 0.8,
                '有些' => 0.8,
                '略' => 0.7,
                '略微' => 0.7
            ],
            // 否定词
            'negations' => [
                '不' => -1.0,
                '没' => -1.0,
                '没有' => -1.0,
                '不是' => -1.0,
                '不会' => -1.0,
                '不能' => -1.0,
                '不可' => -1.0,
                '不可能' => -1.0,
                '不行' => -1.0,
                '别' => -1.0,
                '莫' => -1.0,
                '勿' => -1.0,
                '未' => -1.0,
                '无' => -1.0,
                '非' => -1.0,
                '无法' => -1.0,
                '绝不' => -1.0,
                '决不' => -1.0,
                '永不' => -1.0,
                '永远不' => -1.0,
                '从不' => -1.0,
                '从未' => -1.0,
                '从来不' => -1.0
            ]
        ];
    }

    /**
     * 获取默认配置
     *
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'model' => 'lexicon', // lexicon, ml
            'default_language' => 'en',
            'use_cache' => true,
            'cache_size' => 1000,
            'negation_window' => 3, // 否定词影响范围
            'min_confidence' => 0.6,
            'neutral_threshold' => 0.3, // 中性情感阈值
            'output_format' => 'simple', // simple, detailed
            'sentiment_classes' => [
                'positive' => 1,
                'neutral' => 0,
                'negative' => -1
            ]
        ];
    }

    /**
     * 分析文本情感
     *
     * @param string $text 输入文本
     * @param array|null $tokens 分词结果，如果为null则自动分词
     * @param string|null $language 语言代码，如果为null则自动检测
     * @return array 情感分析结果
     */
    public function analyze(string $text, ?array $tokens = null, ?string $language = null): array
    {
        if (empty($text)) {
            return $this->formatOutput(0, 'neutral', 0.0);
        }

        // 使用缓存
        if ($this->config['use_cache']) {
            $cacheKey = md5($text . ($language ?? ''));
            if (isset($this->cache[$cacheKey])) {
                return $this->cache[$cacheKey];
            }
        }

        // 检测语言
        if ($language === null) {
            $language = $this->detectLanguage($text);
        }

        // 分词
        if ($tokens === null) {
            $tokenizer = $this->getTokenizer($language);
            $tokens = $tokenizer->tokenize($text);
        }

        // 根据语言选择分析方法
        switch ($language) {
            case 'en':
                $result = $this->analyzeEnglish($tokens);
                break;
            case 'zh':
                $result = $this->analyzeChinese($tokens);
                break;
            default:
                $result = $this->analyzeEnglish($tokens); // 默认使用英文分析
        }

        // 缓存结果
        if ($this->config['use_cache']) {
            if (count($this->cache) >= $this->config['cache_size']) {
                // 简单的缓存清理策略：清除最早的一半缓存
                $this->cache = array_slice($this->cache, intval($this->config['cache_size'] / 2), null, true);
            }
            $this->cache[$cacheKey] = $result;
        }

        return $result;
    }

    /**
     * 获取适合语言的分词器
     *
     * @param string $language 语言代码
     * @return TokenizerInterface 分词器
     */
    private function getTokenizer(string $language): TokenizerInterface
    {
        switch ($language) {
            case 'en':
                return new EnglishTokenizer();
            case 'zh':
                return new ChineseTokenizer();
            default:
                return new UniversalTokenizer();
        }
    }

    /**
     * 检测语言
     *
     * @param string $text 文本
     * @return string 语言代码
     */
    private function detectLanguage(string $text): string
    {
        // 简单的语言检测：检查是否包含中文字符
        if (preg_match('/\p{Han}+/u', $text)) {
            return 'zh';
        }
        return 'en'; // 默认为英文
    }

    /**
     * 英文情感分析
     *
     * @param array $tokens 分词结果
     * @return array 情感分析结果
     */
    private function analyzeEnglish(array $tokens): array
    {
        $score = 0.0;
        $sentimentWords = [];
        $totalWords = count($tokens);
        
        if ($totalWords === 0) {
            return $this->formatOutput(0, 'neutral', 0.0);
        }
        
        // 查找否定词位置
        $negationPositions = [];
        foreach ($tokens as $i => $token) {
            if (isset($token['text'])) {
                $text = strtolower($token['text']);
                if (isset($this->englishLexicon['negations'][$text])) {
                    $negationPositions[] = $i;
                }
            }
        }
        
        // 计算情感分数
        for ($i = 0; $i < $totalWords; $i++) {
            if (!isset($tokens[$i]['text'])) {
                continue;
            }
            
            $text = strtolower($tokens[$i]['text']);
            $sentimentValue = 0.0;
            $isIntensifier = false;
            $intensifierValue = 1.0;
            
            // 检查是否为情感词
            if (isset($this->englishLexicon['positive'][$text])) {
                $sentimentValue = $this->englishLexicon['positive'][$text];
                $sentimentType = 'positive';
            } elseif (isset($this->englishLexicon['negative'][$text])) {
                $sentimentValue = $this->englishLexicon['negative'][$text];
                $sentimentType = 'negative';
            } else {
                continue; // 不是情感词，跳过
            }
            
            // 检查前面的词是否为程度副词
            if ($i > 0 && isset($tokens[$i-1]['text'])) {
                $prevText = strtolower($tokens[$i-1]['text']);
                if (isset($this->englishLexicon['intensifiers'][$prevText])) {
                    $intensifierValue = $this->englishLexicon['intensifiers'][$prevText];
                    $isIntensifier = true;
                }
            }
            
            // 检查是否在否定词的影响范围内
            $isNegated = false;
            foreach ($negationPositions as $negPos) {
                if ($i > $negPos && $i <= $negPos + $this->config['negation_window']) {
                    $isNegated = true;
                    break;
                }
            }
            
            // 应用否定和程度修饰
            if ($isNegated) {
                $sentimentValue = -$sentimentValue;
                $sentimentType = $sentimentType === 'positive' ? 'negative' : 'positive';
            }
            
            if ($isIntensifier) {
                $sentimentValue *= $intensifierValue;
            }
            
            // 累加情感分数
            $score += $sentimentValue;
            
            // 记录情感词
            $sentimentWords[] = [
                'word' => $tokens[$i]['text'],
                'type' => $sentimentType,
                'value' => $sentimentValue,
                'position' => $i,
                'intensified' => $isIntensifier,
                'negated' => $isNegated
            ];
        }
        
        // 归一化情感分数
        if (!empty($sentimentWords)) {
            $score = $score / sqrt(count($sentimentWords));
        }
        
        // 确定情感极性
        if ($score > $this->config['neutral_threshold']) {
            $sentiment = 'positive';
            $sentimentValue = 1;
        } elseif ($score < -$this->config['neutral_threshold']) {
            $sentiment = 'negative';
            $sentimentValue = -1;
        } else {
            $sentiment = 'neutral';
            $sentimentValue = 0;
        }
        
        // 计算置信度
        $confidence = min(abs($score), 1.0);
        
        return $this->formatOutput($sentimentValue, $sentiment, $confidence, $score, $sentimentWords);
    }

    /**
     * 中文情感分析
     *
     * @param array $tokens 分词结果
     * @return array 情感分析结果
     */
    private function analyzeChinese(array $tokens): array
    {
        $score = 0.0;
        $sentimentWords = [];
        $totalWords = count($tokens);
        
        if ($totalWords === 0) {
            return $this->formatOutput(0, 'neutral', 0.0);
        }
        
        // 查找否定词位置
        $negationPositions = [];
        foreach ($tokens as $i => $token) {
            if (isset($token['text'])) {
                $text = $token['text'];
                if (isset($this->chineseLexicon['negations'][$text])) {
                    $negationPositions[] = $i;
                }
            }
        }
        
        // 计算情感分数
        for ($i = 0; $i < $totalWords; $i++) {
            if (!isset($tokens[$i]['text'])) {
                continue;
            }
            
            $text = $tokens[$i]['text'];
            $sentimentValue = 0.0;
            $isIntensifier = false;
            $intensifierValue = 1.0;
            
            // 检查是否为情感词
            if (isset($this->chineseLexicon['positive'][$text])) {
                $sentimentValue = $this->chineseLexicon['positive'][$text];
                $sentimentType = 'positive';
            } elseif (isset($this->chineseLexicon['negative'][$text])) {
                $sentimentValue = $this->chineseLexicon['negative'][$text];
                $sentimentType = 'negative';
            } else {
                continue; // 不是情感词，跳过
            }
            
            // 检查前面的词是否为程度副词
            if ($i > 0 && isset($tokens[$i-1]['text'])) {
                $prevText = $tokens[$i-1]['text'];
                if (isset($this->chineseLexicon['intensifiers'][$prevText])) {
                    $intensifierValue = $this->chineseLexicon['intensifiers'][$prevText];
                    $isIntensifier = true;
                }
            }
            
            // 检查是否在否定词的影响范围内
            $isNegated = false;
            foreach ($negationPositions as $negPos) {
                // 中文否定词通常在情感词前面
                if ($i > $negPos && $i <= $negPos + $this->config['negation_window']) {
                    $isNegated = true;
                    break;
                }
            }
            
            // 应用否定和程度修饰
            if ($isNegated) {
                $sentimentValue = -$sentimentValue;
                $sentimentType = $sentimentType === 'positive' ? 'negative' : 'positive';
            }
            
            if ($isIntensifier) {
                $sentimentValue *= $intensifierValue;
            }
            
            // 累加情感分数
            $score += $sentimentValue;
            
            // 记录情感词
            $sentimentWords[] = [
                'word' => $tokens[$i]['text'],
                'type' => $sentimentType,
                'value' => $sentimentValue,
                'position' => $i,
                'intensified' => $isIntensifier,
                'negated' => $isNegated
            ];
        }
        
        // 归一化情感分数
        if (!empty($sentimentWords)) {
            $score = $score / sqrt(count($sentimentWords));
        }
        
        // 确定情感极性
        if ($score > $this->config['neutral_threshold']) {
            $sentiment = 'positive';
            $sentimentValue = 1;
        } elseif ($score < -$this->config['neutral_threshold']) {
            $sentiment = 'negative';
            $sentimentValue = -1;
        } else {
            $sentiment = 'neutral';
            $sentimentValue = 0;
        }
        
        // 计算置信度
        $confidence = min(abs($score), 1.0);
        
        return $this->formatOutput($sentimentValue, $sentiment, $confidence, $score, $sentimentWords);
    }

    /**
     * 格式化输出
     *
     * @param int $sentimentValue 情感值
     * @param string $sentiment 情感类型
     * @param float $confidence 置信度
     * @param float|null $score 原始分数
     * @param array|null $sentimentWords 情感词列表
     * @return array 格式化后的输出
     */
    private function formatOutput(int $sentimentValue, string $sentiment, float $confidence, ?float $score = null, ?array $sentimentWords = null): array
    {
        if ($this->config['output_format'] === 'simple') {
            return [
                'sentiment' => $sentiment,
                'value' => $sentimentValue,
                'confidence' => $confidence
            ];
        } else {
            return [
                'sentiment' => $sentiment,
                'value' => $sentimentValue,
                'confidence' => $confidence,
                'score' => $score ?? 0.0,
                'sentiment_words' => $sentimentWords ?? []
            ];
        }
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
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取英文情感词典
     *
     * @return array 英文情感词典
     */
    public function getEnglishLexicon(): array
    {
        return $this->englishLexicon;
    }

    /**
     * 添加英文情感词
     *
     * @param string $word 单词
     * @param float $value 情感值
     * @param string $type 类型 (positive, negative, intensifier, negation)
     * @return void
     */
    public function addEnglishWord(string $word, float $value, string $type): void
    {
        $word = strtolower($word);
        if (!in_array($type, ['positive', 'negative', 'intensifiers', 'negations'])) {
            throw new InvalidArgumentException("Invalid sentiment word type: {$type}");
        }
        
        $this->englishLexicon[$type][$word] = $value;
    }

    /**
     * 获取中文情感词典
     *
     * @return array 中文情感词典
     */
    public function getChineseLexicon(): array
    {
        return $this->chineseLexicon;
    }

    /**
     * 添加中文情感词
     *
     * @param string $word 单词
     * @param float $value 情感值
     * @param string $type 类型 (positive, negative, intensifier, negation)
     * @return void
     */
    public function addChineseWord(string $word, float $value, string $type): void
    {
        if (!in_array($type, ['positive', 'negative', 'intensifiers', 'negations'])) {
            throw new InvalidArgumentException("Invalid sentiment word type: {$type}");
        }
        
        $this->chineseLexicon[$type][$word] = $value;
    }

    /**
     * 清除缓存
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}