<?php
/**
 * 文件名：UniversalTokenizer.php
 * 功能描述：通用分词器 - 支持多语言分词处理
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\Engines\NLP;

use InvalidArgumentException;
use RuntimeException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;

/**
 * 通用分词器
 *
 * 提供多语言分词支持，针对不同语言使用不同的分词策略
 */
class UniversalTokenizer implements TokenizerInterface
{
    /**
     * @var array 配置选项
     */
    private array $config;
    
    /**
     * @var LoggerInterface|null 日志器
     */
    private ?LoggerInterface $logger;
    
    /**
     * @var CacheManager|null 缓存管理器
     */
    private ?CacheManager $cache;
    
    /**
     * @var array 停用词列表 [language => [word1, word2, ...]]
     */
    private array $stopwords = [];
    
    /**
     * @var string 当前语言
     */
    private string $currentLanguage;
    
    /**
     * @var array 语言检测模型
     */
    private array $languageDetectionModels = [];
    
    /**
     * @var array 词干提取器
     */
    private array $stemmers = [];
    
    /**
     * @var array 词形还原器
     */
    private array $lemmatizers = [];

    /**
     * 构造函数
     *
     * @param array $config 配置选项
     * @param LoggerInterface|null $logger 日志器
     * @param CacheManager|null $cache 缓存管理器
     */
    public function __construct(array $config = [], ?LoggerInterface $logger = null, ?CacheManager $cache = null)
    {
        $this->config = $this->mergeConfig($config);
        $this->logger = $logger;
        $this->cache = $cache;
        
        $this->currentLanguage = $this->config['default_language'];
        
        $this->initializeStopwords();
        
        if ($this->logger) {
            $this->logger->info('通用分词器初始化成功', [
                'default_language' => $this->currentLanguage,
                'supported_languages' => implode(', ', $this->config['supported_languages'])
            ]);
        }
    }
    
    /**
     * 合并默认配置和用户配置
     *
     * @param array $config 用户配置
     * @return array 合并后的配置
     */
    private function mergeConfig(array $config): array
    {
        // 默认配置
        $defaultConfig = [
            'default_language' => 'zh-CN',
            'supported_languages' => ['zh-CN', 'en-US'],
            'use_cache' => true,
            'cache_ttl' => 3600,
            'max_token_length' => 100,
            'preserve_case' => true,
            'tokenize_punctuation' => true,
            'tokenize_numbers' => true,
            'remove_stopwords' => false
        ];
        
        return array_merge($defaultConfig, $config);
    }
    
    /**
     * 初始化停用词
     */
    private function initializeStopwords(): void
    {
        // 加载默认语言的停用词
        $this->loadStopwords($this->currentLanguage);
        
        // 如果设置了缓存，尝试从缓存加载其他支持语言的停用词
        if ($this->cache && $this->config['use_cache']) {
            foreach ($this->config['supported_languages'] as $language) {
                if ($language === $this->currentLanguage) {
                    continue;
                }
                
                $cacheKey = "stopwords_{$language}";
                if ($this->cache->has($cacheKey)) {
                    $this->stopwords[$language] = $this->cache->get($cacheKey);
                }
            }
        }
    }
    
    /**
     * 加载特定语言的停用词
     *
     * @param string $language 语言代码
     * @return bool 是否加载成功
     */
    private function loadStopwords(string $language): bool
    {
        if (isset($this->stopwords[$language])) {
            return true;
        }
        
        // 尝试从缓存加载
        if ($this->cache && $this->config['use_cache']) {
            $cacheKey = "stopwords_{$language}";
            if ($this->cache->has($cacheKey)) {
                $this->stopwords[$language] = $this->cache->get($cacheKey);
                return true;
            }
        }
        
        // 从文件加载停用词
        $stopwordsFile = __DIR__ . "/resources/stopwords/{$language}.php";
        
        if (file_exists($stopwordsFile)) {
            $stopwords = include $stopwordsFile;
            if (is_array($stopwords)) {
                $this->stopwords[$language] = $stopwords;
                
                // 保存到缓存
                if ($this->cache && $this->config['use_cache']) {
                    $cacheKey = "stopwords_{$language}";
                    $this->cache->set($cacheKey, $stopwords, $this->config['cache_ttl']);
                }
                
                return true;
            }
        }
        
        // 如果无法加载特定语言的停用词，使用硬编码的基本停用词
        $this->stopwords[$language] = $this->getDefaultStopwords($language);
        
        return false;
    }
    
    /**
     * 获取默认的停用词
     *
     * @param string $language 语言代码
     * @return array 默认停用词列表
     */
    private function getDefaultStopwords(string $language): array
    {
        switch ($language) {
            case 'zh-CN':
                return ['的', '了', '和', '是', '就', '都', '而', '及', '与', '这', '那', '有', '在', '中', '为', '对', '也'];
            case 'en-US':
                return ['the', 'a', 'an', 'and', 'or', 'but', 'if', 'then', 'else', 'when', 'at', 'from', 'by', 'on', 'for', 'to', 'in', 'of'];
            default:
                return [];
        }
    }
    
    /**
     * 分词方法
     *
     * @param string $text 要分词的文本
     * @param array $options 分词选项
     * @return array 分词结果
     */
    public function tokenize(string $text, array $options = []): array
    {
        // 合并选项
        $options = array_merge($this->config, $options);
        
        // 检查缓存
        $cacheKey = null;
        if ($this->cache && $options['use_cache']) {
            $cacheKey = "tokenize_" . md5($text . json_encode($options));
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }
        
        // 检测语言（如果没有指定）
        $language = $options['language'] ?? null;
        if (!$language) {
            $language = $this->detectLanguage($text) ?? $this->currentLanguage;
        }
        
        // 根据语言选择分词策略
        $tokens = $this->tokenizeByLanguage($text, $language, $options);
        
        // 如果需要过滤停用词
        if ($options['remove_stopwords'] ?? false) {
            $tokens = $this->filterTokens($tokens, ['remove_stopwords' => true]);
        }
        
        // 缓存结果
        if ($this->cache && $options['use_cache'] && $cacheKey) {
            $this->cache->set($cacheKey, $tokens, $options['cache_ttl']);
        }
        
        return $tokens;
    }
    
    /**
     * 根据语言调用不同的分词方法
     *
     * @param string $text 文本
     * @param string $language 语言代码
     * @param array $options 选项
     * @return array 分词结果
     */
    private function tokenizeByLanguage(string $text, string $language, array $options): array
    {
        switch ($language) {
            case 'zh-CN':
                return $this->tokenizeChineseText($text, $options);
            case 'en-US':
            default:
                return $this->tokenizeEnglishText($text, $options);
        }
    }
    
    /**
     * 中文分词
     *
     * @param string $text 中文文本
     * @param array $options 选项
     * @return array 分词结果
     */
    private function tokenizeChineseText(string $text, array $options): array
    {
        // 这里应该使用专业的中文分词库
        // 例如 jieba-php, phpanalysis 等
        // 简单起见，这里使用字符分割方式，实际应用应使用专业算法
        $tokens = [];
        $text = trim($text);
        $position = 0;
        
        // 使用正则识别不同类型的单元
        $pattern = '/([a-zA-Z0-9]+|[\x{4e00}-\x{9fa5}]|[^\s\x{4e00}-\x{9fa5}a-zA-Z0-9])/u';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
        
        foreach ($matches[0] as $match) {
            $tokenText = $match[0];
            $start = $match[1];
            $length = mb_strlen($tokenText);
            
            // 确定token类型
            $type = $this->determineTokenType($tokenText);
            
            // 检查是否为停用词
            $isStopWord = in_array($tokenText, $this->stopwords[$this->currentLanguage] ?? []);
            
            $token = [
                'text' => $tokenText,
                'start' => $start,
                'end' => $start + strlen($tokenText),
                'length' => $length,
                'type' => $type,
                'is_stop_word' => $isStopWord,
            ];
            
            $tokens[] = $token;
        }
        
        return $tokens;
    }
    
    /**
     * 英文分词
     *
     * @param string $text 英文文本
     * @param array $options 选项
     * @return array 分词结果
     */
    private function tokenizeEnglishText(string $text, array $options): array
    {
        $tokens = [];
        $preserve_case = $options['preserve_case'] ?? true;
        
        // 简单的基于空格和标点的分词
        $pattern = '/\b\w+\b|[^\w\s]/u';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
        
        foreach ($matches[0] as $match) {
            $tokenText = $match[0];
            $start = $match[1];
            
            // 如果不保留大小写，则转为小写
            if (!$preserve_case) {
                $tokenText = strtolower($tokenText);
            }
            
            // 确定token类型
            $type = $this->determineTokenType($tokenText);
            
            // 检查是否为停用词
            $isStopWord = in_array(strtolower($tokenText), $this->stopwords['en-US'] ?? []);
            
            $token = [
                'text' => $tokenText,
                'start' => $start,
                'end' => $start + strlen($tokenText),
                'length' => mb_strlen($tokenText),
                'type' => $type,
                'is_stop_word' => $isStopWord,
            ];
            
            $tokens[] = $token;
        }
        
        return $tokens;
    }
    
    /**
     * 确定token类型
     *
     * @param string $token token文本
     * @return string token类型
     */
    private function determineTokenType(string $token): string
    {
        if (preg_match('/^\d+$/', $token)) {
            return 'NUMBER';
        } elseif (preg_match('/^\d+\.\d+$/', $token)) {
            return 'FLOAT';
        } elseif (preg_match('/^[a-zA-Z]+$/', $token)) {
            return 'WORD';
        } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $token)) {
            return 'CJK';
        } elseif (preg_match('/^[^\w\s]$/u', $token)) {
            return 'PUNCTUATION';
        } else {
            return 'OTHER';
        }
    }
    
    /**
     * 获取停用词列表
     *
     * @param string|null $language 语言代码
     * @return array 停用词列表
     */
    public function getStopwords(?string $language = null): array
    {
        $language = $language ?? $this->currentLanguage;
        
        // 确保语言的停用词已加载
        if (!isset($this->stopwords[$language])) {
            $this->loadStopwords($language);
        }
        
        return $this->stopwords[$language] ?? [];
    }
    
    /**
     * 添加自定义停用词
     *
     * @param array $words 要添加的停用词
     * @param string|null $language 语言代码
     * @return bool 是否添加成功
     */
    public function addStopwords(array $words, ?string $language = null): bool
    {
        $language = $language ?? $this->currentLanguage;
        
        // 确保语言的停用词已加载
        if (!isset($this->stopwords[$language])) {
            $this->loadStopwords($language);
        }
        
        // 合并并去重
        $this->stopwords[$language] = array_unique(array_merge($this->stopwords[$language] ?? [], $words));
        
        // 更新缓存
        if ($this->cache && $this->config['use_cache']) {
            $cacheKey = "stopwords_{$language}";
            $this->cache->set($cacheKey, $this->stopwords[$language], $this->config['cache_ttl']);
        }
        
        return true;
    }
    
    /**
     * 移除停用词
     *
     * @param array $words 要移除的停用词
     * @param string|null $language 语言代码
     * @return bool 是否移除成功
     */
    public function removeStopwords(array $words, ?string $language = null): bool
    {
        $language = $language ?? $this->currentLanguage;
        
        // 确保语言的停用词已加载
        if (!isset($this->stopwords[$language])) {
            $this->loadStopwords($language);
        }
        
        // 移除指定的停用词
        if (isset($this->stopwords[$language])) {
            $this->stopwords[$language] = array_diff($this->stopwords[$language], $words);
            
            // 更新缓存
            if ($this->cache && $this->config['use_cache']) {
                $cacheKey = "stopwords_{$language}";
                $this->cache->set($cacheKey, $this->stopwords[$language], $this->config['cache_ttl']);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 将分词结果转换为字符串
     *
     * @param array $tokens 分词结果
     * @param string $delimiter 分隔符
     * @return string 转换后的字符串
     */
    public function tokensToString(array $tokens, string $delimiter = ' '): string
    {
        $textArray = array_column($tokens, 'text');
        return implode($delimiter, $textArray);
    }
    
    /**
     * 过滤分词结果
     *
     * @param array $tokens 原始分词结果
     * @param array $options 过滤选项
     * @return array 过滤后的分词结果
     */
    public function filterTokens(array $tokens, array $options = []): array
    {
        $result = [];
        
        // 合并默认选项和用户选项
        $options = array_merge([
            'remove_stopwords' => false,
            'remove_punctuation' => false,
            'min_length' => 0,
            'max_length' => PHP_INT_MAX,
            'types' => null, // 指定要保留的类型
        ], $options);
        
        foreach ($tokens as $token) {
            // 跳过停用词
            if ($options['remove_stopwords'] && ($token['is_stop_word'] ?? false)) {
                continue;
            }
            
            // 跳过标点
            if ($options['remove_punctuation'] && $token['type'] === 'PUNCTUATION') {
                continue;
            }
            
            // 检查长度
            $length = mb_strlen($token['text']);
            if ($length < $options['min_length'] || $length > $options['max_length']) {
                continue;
            }
            
            // 检查类型
            if ($options['types'] !== null && !in_array($token['type'], (array)$options['types'])) {
                continue;
            }
            
            $result[] = $token;
        }
        
        return $result;
    }
    
    /**
     * 获取分词器信息
     *
     * @return array 分词器信息
     */
    public function getTokenizerInfo(): array
    {
        return [
            'name' => 'UniversalTokenizer',
            'version' => '1.0.0',
            'supported_languages' => $this->config['supported_languages'],
            'current_language' => $this->currentLanguage,
            'features' => [
                'stemming' => true,
                'lemmatization' => true,
                'language_detection' => true,
                'stopwords_filtering' => true,
            ]
        ];
    }
    
    /**
     * 检测语言
     *
     * @param string $text 要检测的文本
     * @return string|null 检测到的语言代码
     */
    public function detectLanguage(string $text): ?string
    {
        // 简单的语言检测逻辑
        // 在实际应用中，应该使用更复杂的语言检测算法
        
        // 检查缓存
        if ($this->cache && $this->config['use_cache']) {
            $cacheKey = "lang_detect_" . md5($text);
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }
        
        // 计算中文字符的比例
        $totalLength = mb_strlen($text);
        if ($totalLength === 0) {
            return null;
        }
        
        // 计算中文字符数量
        $chineseCount = preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $text);
        
        // 如果中文字符占比超过15%，认为是中文
        $chineseRatio = $totalLength > 0 ? $chineseCount / $totalLength : 0;
        $detectedLanguage = $chineseRatio > 0.15 ? 'zh-CN' : 'en-US';
        
        // 缓存结果
        if ($this->cache && $this->config['use_cache']) {
            $cacheKey = "lang_detect_" . md5($text);
            $this->cache->set($cacheKey, $detectedLanguage, $this->config['cache_ttl']);
        }
        
        return $detectedLanguage;
    }
    
    /**
     * 获取词干
     *
     * @param string $word 要提取词干的单词
     * @param string|null $language 语言代码
     * @return string 提取的词干
     */
    public function stem(string $word, ?string $language = null): string
    {
        $language = $language ?? $this->currentLanguage;
        
        // 针对不同语言使用不同的词干提取算法
        switch ($language) {
            case 'en-US':
                // 这里应该使用专业的英文词干提取库，如 porter stemming algorithm
                // 简单示例，真实实现需要一个完整的词干提取器
                return $this->porterStem($word);
            case 'zh-CN':
                // 中文通常不需要词干提取
                return $word;
            default:
                return $word;
        }
    }
    
    /**
     * 简单的Porter词干提取算法实现
     *
     * @param string $word 英文单词
     * @return string 提取的词干
     */
    private function porterStem(string $word): string
    {
        // 转为小写
        $word = strtolower($word);
        
        // 简化的Porter词干提取规则
        // 注意：这是一个非常简化的版本，实际使用时应该使用完整的Porter算法
        
        // 处理复数形式
        $word = preg_replace('/(s|es)$/', '', $word);
        
        // 处理ing结尾
        if (preg_match('/ing$/', $word)) {
            $stem = preg_replace('/ing$/', '', $word);
            // 如果去掉ing后至少有3个字符，则认为是有效的词干
            if (strlen($stem) >= 3) {
                $word = $stem;
            }
        }
        
        // 处理ed结尾
        if (preg_match('/ed$/', $word)) {
            $stem = preg_replace('/ed$/', '', $word);
            // 如果去掉ed后至少有3个字符，则认为是有效的词干
            if (strlen($stem) >= 3) {
                $word = $stem;
            }
        }
        
        // 处理ly结尾
        $word = preg_replace('/ly$/', '', $word);
        
        return $word;
    }
    
    /**
     * 词形还原
     *
     * @param string $word 要还原的单词
     * @param string|null $language 语言代码
     * @return string 还原后的词形
     */
    public function lemmatize(string $word, ?string $language = null): string
    {
        $language = $language ?? $this->currentLanguage;
        
        // 针对不同语言使用不同的词形还原算法
        switch ($language) {
            case 'en-US':
                // 这里应使用词形还原库
                // 简单示例，真实实现需要完整的词形还原库和词典
                return $this->simpleLemmatize($word);
            case 'zh-CN':
                // 中文通常不需要词形还原
                return $word;
            default:
                return $word;
        }
    }
    
    /**
     * 简单的词形还原逻辑
     *
     * @param string $word 单词
     * @return string 还原后的词形
     */
    private function simpleLemmatize(string $word): string
    {
        // 转为小写
        $word = strtolower($word);
        
        // 简单的英文词形还原词典
        $lemmaDict = [
            'am' => 'be',
            'is' => 'be',
            'are' => 'be',
            'was' => 'be',
            'were' => 'be',
            'been' => 'be',
            'being' => 'be',
            'has' => 'have',
            'had' => 'have',
            'having' => 'have',
            'does' => 'do',
            'did' => 'do',
            'doing' => 'do',
            'goes' => 'go',
            'went' => 'go',
            'gone' => 'go',
            'going' => 'go',
            'better' => 'good',
            'best' => 'good',
            'worse' => 'bad',
            'worst' => 'bad',
            'children' => 'child',
            'men' => 'man',
            'women' => 'woman',
            'people' => 'person',
            'mice' => 'mouse',
            'leaves' => 'leaf',
        ];
        
        // 查找词典
        if (isset($lemmaDict[$word])) {
            return $lemmaDict[$word];
        }
        
        // 处理常见的词尾变化
        
        // 处理复数
        if (preg_match('/s$/', $word) && !preg_match('/(ss|us|is|os|xis)$/', $word)) {
            $singular = preg_replace('/s$/', '', $word);
            // 确保单数形式是有效词
            if (strlen($singular) >= 2) {
                return $singular;
            }
        }
        
        // 处理特殊的ies结尾（如stories -> story）
        if (preg_match('/ies$/', $word)) {
            return preg_replace('/ies$/', 'y', $word);
        }
        
        // 处理过去时和过去分词
        if (preg_match('/ed$/', $word) && !preg_match('/(eed|ied)$/', $word)) {
            // 去掉ed
            $lemma = preg_replace('/ed$/', '', $word);
            
            // 如果倒数第二个字符是重复的，去掉一个（如：stopped -> stop）
            $lemma = preg_replace('/([^aeiou])\1$/', '$1', $lemma);
            
            return $lemma;
        }
        
        // 处理进行时
        if (preg_match('/ing$/', $word)) {
            // 去掉ing
            $lemma = preg_replace('/ing$/', '', $word);
            
            // 如果结尾是重复的辅音字母，去掉一个
            $lemma = preg_replace('/([^aeiou])\1$/', '$1', $lemma);
            
            // 如果词长度足够，认为是有效的词根
            if (strlen($lemma) >= 2) {
                return $lemma;
            }
        }
        
        return $word;
    }
    
    /**
     * 设置当前语言
     *
     * @param string $language 语言代码
     * @return void
     */
    public function setLanguage(string $language): void
    {
        if (in_array($language, $this->config['supported_languages'])) {
            $this->currentLanguage = $language;
            
            // 确保该语言的停用词已加载
            if (!isset($this->stopwords[$language])) {
                $this->loadStopwords($language);
            }
        } else {
            throw new InvalidArgumentException("不支持的语言: {$language}");
        }
    }
    
    /**
     * 获取当前设置的语言
     *
     * @return string 当前语言代码
     */
    public function getLanguage(): string
    {
        return $this->currentLanguage;
    }
    }
