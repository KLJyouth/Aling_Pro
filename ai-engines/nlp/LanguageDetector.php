<?php
/**
 * 文件名：LanguageDetector.php
 * 功能描述：语言检测器 - 实现文本语言自动检测功�?
 * 创建时间�?025-01-XX
 * 最后修改：2025-01-XX
 * 版本�?.0.0
 *
 * @package AlingAi\AI\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\AI\Engines\NLP;

use Exception;
use InvalidArgumentException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;

/**
 * 语言检测器
 *
 * 实现文本语言自动检测功能，支持多种语言识别
 */
class LanguageDetector
{
    /**
     * 配置参数
     */
    private array $config;
    
    /**
     * 语言特征�?
     */
    private array $languageProfiles = [];
    
    /**
     * 检测结果缓�?
     */
    private array $cache = [];
    
    /**
     * 日志�?
     */
    private ?LoggerInterface $logger;
    
    /**
     * 缓存管理�?
     */
    private ?CacheManager $cacheManager;
    
    /**
     * 构造函�?
     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志�?
     * @param CacheManager|null $cacheManager 缓存管理�?
     */
    public function __construct(array $config = [],  ?LoggerInterface $logger = null, ?CacheManager $cacheManager = null)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->logger = $logger;
        $this->cacheManager = $cacheManager;
        
        $this->loadLanguageProfiles(];
        
        if ($this->logger) {
            $this->logger->info('语言检测器初始化成�?, [
                'supported_languages' => implode(', ', array_keys($this->languageProfiles))
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
            'min_text_length' => 10,
            'max_text_length' => 10000,
            'sample_size' => 1000,
            'use_cache' => true,
            'cache_ttl' => 3600,
            'confidence_threshold' => 0.5,
            'n_gram_size' => 3,
            'profile_size' => 1000,
            'supported_languages' => [
                'zh-CN', 'en-US', 'ja-JP', 'ko-KR', 'fr-FR', 
                'de-DE', 'es-ES', 'it-IT', 'pt-PT', 'ru-RU'
            ]
        ];
    }
    
    /**
     * 加载语言特征�?
     */
    private function loadLanguageProfiles(): void
    {
        // 尝试从缓存加�?
        if ($this->cacheManager && $this->config['use_cache']) {
            $cacheKey = 'language_profiles';
            if ($this->cacheManager->has($cacheKey)) {
                $this->languageProfiles = $this->cacheManager->get($cacheKey];
                return;
            }
        }
        
        // 从文件加�?
        foreach ($this->config['supported_languages'] as $language) {
            $profilePath = __DIR__ . "/resources/language_profiles/{$language}.php";
            if (file_exists($profilePath)) {
                $profile = include $profilePath;
                if (is_[$profile)) {
                    $this->languageProfiles[$language] = $profile;
                }
            }
        }
        
        // 如果没有找到任何配置文件，使用内置的简化特征库
        if (empty($this->languageProfiles)) {
            $this->loadBuiltinProfiles(];
        }
        
        // 保存到缓�?
        if ($this->cacheManager && $this->config['use_cache']) {
            $cacheKey = 'language_profiles';
            $this->cacheManager->set($cacheKey, $this->languageProfiles, $this->config['cache_ttl']];
        }
    }
    
    /**
     * 加载内置的简化特征库
     */
    private function loadBuiltinProfiles(): void
    {
        // 简化的语言特征库，基于常用字符和词�?
        $this->languageProfiles = [
            'zh-CN' => [
                'chars' => [
                    '�? => 0.0950, '一' => 0.0350, '�? => 0.0320, '�? => 0.0280,
                    '�? => 0.0260, '�? => 0.0240, '�? => 0.0210, '�? => 0.0190,
                    '�? => 0.0180, '�? => 0.0170, '�? => 0.0160, '�? => 0.0150,
                    '�? => 0.0140, '�? => 0.0130, '�? => 0.0120, '�? => 0.0110,
                    '�? => 0.0100, '�? => 0.0095, '�? => 0.0090, '�? => 0.0085
                ], 
                'script' => 'Han'
            ], 
            'en-US' => [
                'chars' => [
                    'e' => 0.1200, 't' => 0.0900, 'a' => 0.0800, 'o' => 0.0750,
                    'i' => 0.0700, 'n' => 0.0650, 's' => 0.0630, 'h' => 0.0600,
                    'r' => 0.0550, 'd' => 0.0400, 'l' => 0.0350, 'u' => 0.0280,
                    'c' => 0.0270, 'm' => 0.0250, 'f' => 0.0220, 'w' => 0.0200,
                    'g' => 0.0170, 'y' => 0.0150, 'p' => 0.0140, 'b' => 0.0130
                ], 
                'script' => 'Latin'
            ], 
            'ja-JP' => [
                'chars' => [
                    '�? => 0.0950, '�? => 0.0850, '�? => 0.0750, '�? => 0.0650,
                    '�? => 0.0600, '�? => 0.0550, '�? => 0.0500, '�? => 0.0450,
                    '�? => 0.0400, '�? => 0.0350, '�? => 0.0300, '�? => 0.0280,
                    '�? => 0.0260, '�? => 0.0240, '�? => 0.0220, '�? => 0.0200
                ], 
                'script' => 'Hiragana'
            ], 
            'ko-KR' => [
                'chars' => [
                    '�? => 0.0900, '�? => 0.0850, '�? => 0.0750, '�? => 0.0700,
                    '가' => 0.0650, '�? => 0.0600, '�? => 0.0550, '�? => 0.0500,
                    '�? => 0.0450, '�? => 0.0400, '와' => 0.0350, '�? => 0.0300,
                    '지' => 0.0280, '�? => 0.0260, '�? => 0.0240, '�? => 0.0220
                ], 
                'script' => 'Hangul'
            ], 
            'ru-RU' => [
                'chars' => [
                    'о' => 0.1100, 'е' => 0.0850, 'а' => 0.0800, 'и' => 0.0750,
                    'н' => 0.0670, 'т' => 0.0650, 'с' => 0.0550, 'р' => 0.0500,
                    'в' => 0.0480, 'л' => 0.0450, 'к' => 0.0350, 'м' => 0.0330,
                    'д' => 0.0300, 'п' => 0.0280, 'у' => 0.0260, 'я' => 0.0200
                ], 
                'script' => 'Cyrillic'
            ]
        ];
    }
    
    /**
     * 检测文本语言
     *
     * @param string $text 要检测的文本
     * @param array $options 选项
     * @return array 检测结果，包含语言代码和置信度
     */
    public function detect(string $text, array $options = []): array
    {
        // 合并选项
        $options = array_merge([
            'detailed' => false,
            'threshold' => $this->config['confidence_threshold']
        ],  $options];
        
        // 文本预处�?
        $text = $this->preprocessText($text];
        
        // 检查文本长�?
        if (mb_strlen($text) < $this->config['min_text_length']) {
            return $this->formatResult('unknown', 0, $options['detailed']];
        }
        
        // 检查缓�?
        $cacheKey = md5($text];
        if ($this->config['use_cache'] && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // 快速脚本检�?
        $scriptResult = $this->detectByScript($text];
        if ($scriptResult['confidence'] > 0.9) {
            $result = $this->formatResult($scriptResult['language'],  $scriptResult['confidence'],  $options['detailed']];
            
            // 缓存结果
            if ($this->config['use_cache']) {
                $this->cache[$cacheKey] = $result;
            }
            
            return $result;
        }
        
        // 提取文本特征
        $textProfile = $this->extractTextProfile($text];
        
        // 计算与各语言特征库的相似�?
        $similarities = [];
        foreach ($this->languageProfiles as $language => $profile) {
            $similarities[$language] = $this->calculateSimilarity($textProfile, $profile];
        }
        
        // 找出最匹配的语言
        arsort($similarities];
        $bestLanguage = key($similarities];
        $confidence = current($similarities];
        
        // 如果置信度低于阈值，返回未知
        if ($confidence < $options['threshold']) {
            return $this->formatResult('unknown', $confidence, $options['detailed']];
        }
        
        $result = $this->formatResult($bestLanguage, $confidence, $options['detailed'],  $similarities];
        
        // 缓存结果
        if ($this->config['use_cache']) {
            $this->cache[$cacheKey] = $result;
        }
        
        return $result;
    }
    
    /**
     * 通过脚本类型快速检测语言
     *
     * @param string $text 文本
     * @return array 检测结�?
     */
    private function detectByScript(string $text): array
    {
        $scripts = [
            'Han' => '/[\x{4e00}-\x{9fff}]/u',  // 中文
            'Hiragana' => '/[\x{3040}-\x{309f}]/u',  // 日文平假�?
            'Katakana' => '/[\x{30a0}-\x{30ff}]/u',  // 日文片假�?
            'Hangul' => '/[\x{ac00}-\x{d7af}]/u',  // 韩文
            'Cyrillic' => '/[\x{0400}-\x{04ff}]/u',  // 西里尔字�?
            'Latin' => '/[a-zA-Z]/u'  // 拉丁字母
        ];
        
        $counts = [];
        $totalChars = mb_strlen($text];
        
        foreach ($scripts as $script => $pattern) {
            $counts[$script] = preg_match_all($pattern, $text];
        }
        
        // 找出最多的脚本
        arsort($counts];
        $dominantScript = key($counts];
        $scriptRatio = $counts[$dominantScript] / $totalChars;
        
        // 根据脚本映射到语言
        $scriptToLanguage = [
            'Han' => 'zh-CN',
            'Hiragana' => 'ja-JP',
            'Katakana' => 'ja-JP',
            'Hangul' => 'ko-KR',
            'Cyrillic' => 'ru-RU',
            'Latin' => 'en-US'  // 默认英语，但拉丁字母有多种语言
        ];
        
        // 如果是拉丁字母，需要进一步分�?
        $language = $scriptToLanguage[$dominantScript] ?? 'unknown';
        
        return [
            'language' => $language,
            'confidence' => $scriptRatio,
            'script' => $dominantScript
        ];
    }
    
    /**
     * 提取文本特征
     *
     * @param string $text 文本
     * @return array 文本特征
     */
    private function extractTextProfile(string $text): array
    {
        // 对于过长的文本，取样�?
        if (mb_strlen($text) > $this->config['sample_size']) {
            $text = mb_substr($text, 0, $this->config['sample_size']];
        }
        
        // 提取字符频率
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY];
        $charFrequency = array_count_values($chars];
        $totalChars = count($chars];
        
        $profile = ['chars' => []];
        foreach ($charFrequency as $char => $count) {
            $profile['chars'][$char] = $count / $totalChars;
        }
        
        // 提取n-gram特征
        if ($this->config['n_gram_size'] > 1) {
            $profile['ngrams'] = $this->extractNgrams($text, $this->config['n_gram_size']];
        }
        
        return $profile;
    }
    
    /**
     * 提取n-gram特征
     *
     * @param string $text 文本
     * @param int $n n-gram大小
     * @return array n-gram特征
     */
    private function extractNgrams(string $text, int $n): array
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY];
        $ngrams = [];
        $totalNgrams = 0;
        
        for ($i = 0; $i <= count($chars) - $n; $i++) {
            $ngram = '';
            for ($j = 0; $j < $n; $j++) {
                $ngram .= $chars[$i + $j];
            }
            
            if (!isset($ngrams[$ngram])) {
                $ngrams[$ngram] = 0;
            }
            $ngrams[$ngram]++;
            $totalNgrams++;
        }
        
        // 计算频率
        $ngramFrequency = [];
        foreach ($ngrams as $ngram => $count) {
            $ngramFrequency[$ngram] = $count / $totalNgrams;
        }
        
        // 只保留最常见的n-gram
        arsort($ngramFrequency];
        return array_slice($ngramFrequency, 0, $this->config['profile_size'],  true];
    }
    
    /**
     * 计算相似�?
     *
     * @param array $textProfile 文本特征
     * @param array $languageProfile 语言特征
     * @return float 相似�?
     */
    private function calculateSimilarity(array $textProfile, array $languageProfile): float
    {
        // 计算字符频率的余弦相似度
        $similarity = 0;
        $textChars = $textProfile['chars'];
        $langChars = $languageProfile['chars'];
        
        $dotProduct = 0;
        $textMagnitude = 0;
        $langMagnitude = 0;
        
        // 计算点积和向量大�?
        foreach ($textChars as $char => $freq) {
            $textMagnitude += $freq * $freq;
            if (isset($langChars[$char])) {
                $dotProduct += $freq * $langChars[$char];
            }
        }
        
        foreach ($langChars as $freq) {
            $langMagnitude += $freq * $freq;
        }
        
        // 计算余弦相似�?
        $textMagnitude = sqrt($textMagnitude];
        $langMagnitude = sqrt($langMagnitude];
        
        if ($textMagnitude > 0 && $langMagnitude > 0) {
            $similarity = $dotProduct / ($textMagnitude * $langMagnitude];
        }
        
        // 如果有n-gram特征，也计算n-gram相似�?
        if (isset($textProfile['ngrams']) && isset($languageProfile['ngrams'])) {
            $ngramSimilarity = $this->calculateNgramSimilarity(
                $textProfile['ngrams'], 
                $languageProfile['ngrams']
            ];
            
            // 综合字符和n-gram相似�?
            $similarity = ($similarity + $ngramSimilarity) / 2;
        }
        
        // 脚本匹配加分
        if (isset($languageProfile['script'])) {
            $scriptPattern = $this->getScriptPattern($languageProfile['script']];
            $scriptMatches = preg_match_all($scriptPattern, $text];
            $scriptRatio = $scriptMatches / mb_strlen($text];
            
            // 脚本匹配度高，增加相似度
            $similarity += $scriptRatio * 0.2;
        }
        
        return min(1.0, max(0.0, $similarity)];
    }
    
    /**
     * 计算n-gram相似�?
     *
     * @param array $textNgrams 文本n-gram
     * @param array $langNgrams 语言n-gram
     * @return float 相似�?
     */
    private function calculateNgramSimilarity(array $textNgrams, array $langNgrams): float
    {
        $dotProduct = 0;
        $textMagnitude = 0;
        $langMagnitude = 0;
        
        // 计算点积和向量大�?
        foreach ($textNgrams as $ngram => $freq) {
            $textMagnitude += $freq * $freq;
            if (isset($langNgrams[$ngram])) {
                $dotProduct += $freq * $langNgrams[$ngram];
            }
        }
        
        foreach ($langNgrams as $freq) {
            $langMagnitude += $freq * $freq;
        }
        
        // 计算余弦相似�?
        $textMagnitude = sqrt($textMagnitude];
        $langMagnitude = sqrt($langMagnitude];
        
        if ($textMagnitude > 0 && $langMagnitude > 0) {
            return $dotProduct / ($textMagnitude * $langMagnitude];
        }
        
        return 0;
    }
    
    /**
     * 获取脚本正则表达�?
     *
     * @param string $script 脚本名称
     * @return string 正则表达�?
     */
    private function getScriptPattern(string $script): string
    {
        $patterns = [
            'Han' => '/[\x{4e00}-\x{9fff}]/u',
            'Hiragana' => '/[\x{3040}-\x{309f}]/u',
            'Katakana' => '/[\x{30a0}-\x{30ff}]/u',
            'Hangul' => '/[\x{ac00}-\x{d7af}]/u',
            'Cyrillic' => '/[\x{0400}-\x{04ff}]/u',
            'Latin' => '/[a-zA-Z]/u'
        ];
        
        return $patterns[$script] ?? '/./u';
    }
    
    /**
     * 文本预处�?
     *
     * @param string $text 原文�?
     * @return string 处理后的文本
     */
    private function preprocessText(string $text): string
    {
        // 转换为小�?
        $text = mb_strtolower($text];
        
        // 移除多余空白
        $text = preg_replace('/\s+/u', ' ', $text];
        
        // 移除标点和数�?
        $text = preg_replace('/[\p{P}\p{N}]/u', '', $text];
        
        return trim($text];
    }
    
    /**
     * 格式化结�?
     *
     * @param string $language 语言代码
     * @param float $confidence 置信�?
     * @param bool $detailed 是否返回详细信息
     * @param array|null $allSimilarities 所有语言的相似度
     * @return array 格式化的结果
     */
    private function formatResult(string $language, float $confidence, bool $detailed, ?array $allSimilarities = null): array
    {
        $result = [
            'language' => $language,
            'confidence' => round($confidence, 4)
        ];
        
        if ($detailed) {
            $result['details'] = [
                'all_languages' => []
            ];
            
            if ($allSimilarities) {
                foreach ($allSimilarities as $lang => $sim) {
                    $result['details']['all_languages'][$lang] = round($sim, 4];
                }
            }
        }
        
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
     * 获取支持的语言
     *
     * @return array 支持的语言
     */
    public function getSupportedLanguages(): array
    {
        return array_keys($this->languageProfiles];
    }
    
    /**
     * 清除缓存
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
    
    /**
     * 添加语言特征
     *
     * @param string $language 语言代码
     * @param array $profile 语言特征
     */
    public function addLanguageProfile(string $language, array $profile): void
    {
        $this->languageProfiles[$language] = $profile;
    }
} 

