<?php
/**
 * �ļ�����UniversalTokenizer.php
 * ����������ͨ�÷ִ��� - ֧�ֶ����Էִʴ���
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�2025-01-XX
 * �汾��1.0.0
 *
 * @package AlingAi\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\Engines\NLP;

use InvalidArgumentException;
use RuntimeException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;

/**
 * ͨ�÷ִ���
 *
 * �ṩ�����Էִ�֧�֣���Բ�ͬ����ʹ�ò�ͬ�ķִʲ���
 */
class UniversalTokenizer implements TokenizerInterface
{
    /**
     * @var array ����ѡ��
     */
    private array $config;
    
    /**
     * @var LoggerInterface|null ��־��
     */
    private ?LoggerInterface $logger;
    
    /**
     * @var CacheManager|null ���������
     */
    private ?CacheManager $cache;
    
    /**
     * @var array ͣ�ô��б� [language => [word1, word2, ...]]
     */
    private array $stopwords = [];
    
    /**
     * @var string ��ǰ����
     */
    private string $currentLanguage;
    
    /**
     * @var array ���Լ��ģ��
     */
    private array $languageDetectionModels = [];
    
    /**
     * @var array �ʸ���ȡ��
     */
    private array $stemmers = [];
    
    /**
     * @var array ���λ�ԭ��
     */
    private array $lemmatizers = [];

    /**
     * ���캯��
     *
     * @param array $config ����ѡ��
     * @param LoggerInterface|null $logger ��־��
     * @param CacheManager|null $cache ���������
     */
    public function __construct(array $config = [],  ?LoggerInterface $logger = null, ?CacheManager $cache = null)
    {
        $this->config = $this->mergeConfig($config];
        $this->logger = $logger;
        $this->cache = $cache;
        
        $this->currentLanguage = $this->config['default_language'];
        
        $this->initializeStopwords(];
        
        if ($this->logger) {
            $this->logger->info('ͨ�÷ִ�����ʼ���ɹ�', [
                'default_language' => $this->currentLanguage,
                'supported_languages' => implode(', ', $this->config['supported_languages'])
            ]];
        }
    }
    
    /**
     * �ϲ�Ĭ�����ú��û�����
     *
     * @param array $config �û�����
     * @return array �ϲ��������
     */
    private function mergeConfig(array $config): array
    {
        // Ĭ������
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
        
        return array_merge($defaultConfig, $config];
    }
    
    /**
     * ��ʼ��ͣ�ô�
     */
    private function initializeStopwords(): void
    {
        // ����Ĭ�����Ե�ͣ�ô�
        $this->loadStopwords($this->currentLanguage];
        
        // ��������˻��棬���Դӻ����������֧�����Ե�ͣ�ô�
        if ($this->cache && $this->config['use_cache']) {
            foreach ($this->config['supported_languages'] as $language) {
                if ($language === $this->currentLanguage) {
                    continue;
                }
                
                $cacheKey = "stopwords_{$language}";
                if ($this->cache->has($cacheKey)) {
                    $this->stopwords[$language] = $this->cache->get($cacheKey];
                }
            }
        }
    }
    
    /**
     * �����ض����Ե�ͣ�ô�
     *
     * @param string $language ���Դ���
     * @return bool �Ƿ���سɹ�
     */
    private function loadStopwords(string $language): bool
    {
        if (isset($this->stopwords[$language])) {
            return true;
        }
        
        // ���Դӻ������
        if ($this->cache && $this->config['use_cache']) {
            $cacheKey = "stopwords_{$language}";
            if ($this->cache->has($cacheKey)) {
                $this->stopwords[$language] = $this->cache->get($cacheKey];
                return true;
            }
        }
        
        // ���ļ�����ͣ�ô�
        $stopwordsFile = __DIR__ . "/resources/stopwords/{$language}.php";
        
        if (file_exists($stopwordsFile)) {
            $stopwords = include $stopwordsFile;
            if (is_[$stopwords)) {
                $this->stopwords[$language] = $stopwords;
                
                // ���浽����
                if ($this->cache && $this->config['use_cache']) {
                    $cacheKey = "stopwords_{$language}";
                    $this->cache->set($cacheKey, $stopwords, $this->config['cache_ttl']];
                }
                
                return true;
            }
        }
        
        // ����޷������ض����Ե�ͣ�ôʣ�ʹ��Ӳ����Ļ���ͣ�ô�
        $this->stopwords[$language] = $this->getDefaultStopwords($language];
        
        return false;
    }
    
    /**
     * ��ȡĬ�ϵ�ͣ�ô�
     *
     * @param string $language ���Դ���
     * @return array Ĭ��ͣ�ô��б�
     */
    private function getDefaultStopwords(string $language): array
    {
        switch ($language) {
            case 'zh-CN':
                return ['��', '��', '��', '��', '��', '��', '��', '��', '��', '��', '��', '��', '��', '��', 'Ϊ', '��', 'Ҳ'];
            case 'en-US':
                return ['the', 'a', 'an', 'and', 'or', 'but', 'if', 'then', 'else', 'when', 'at', 'from', 'by', 'on', 'for', 'to', 'in', 'of'];
            default:
                return [];
        }
    }
    
    /**
     * �ִʷ���
     *
     * @param string $text Ҫ�ִʵ��ı�
     * @param array $options �ִ�ѡ��
     * @return array �ִʽ��
     */
    public function tokenize(string $text, array $options = []): array
    {
        // �ϲ�ѡ��
        $options = array_merge($this->config, $options];
        
        // ��黺��
        $cacheKey = null;
        if ($this->cache && $options['use_cache']) {
            $cacheKey = "tokenize_" . md5($text . json_encode($options)];
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey];
            }
        }
        
        // ������ԣ����û��ָ����
        $language = $options['language'] ?? null;
        if (!$language) {
            $language = $this->detectLanguage($text) ?? $this->currentLanguage;
        }
        
        // ��������ѡ��ִʲ���
        $tokens = $this->tokenizeByLanguage($text, $language, $options];
        
        // �����Ҫ����ͣ�ô�
        if ($options['remove_stopwords'] ?? false) {
            $tokens = $this->filterTokens($tokens, ['remove_stopwords' => true]];
        }
        
        // ������
        if ($this->cache && $options['use_cache'] && $cacheKey) {
            $this->cache->set($cacheKey, $tokens, $options['cache_ttl']];
        }
        
        return $tokens;
    }
    
    /**
     * �������Ե��ò�ͬ�ķִʷ���
     *
     * @param string $text �ı�
     * @param string $language ���Դ���
     * @param array $options ѡ��
     * @return array �ִʽ��
     */
    private function tokenizeByLanguage(string $text, string $language, array $options): array
    {
        switch ($language) {
            case 'zh-CN':
                return $this->tokenizeChineseText($text, $options];
            case 'en-US':
            default:
                return $this->tokenizeEnglishText($text, $options];
        }
    }
    
    /**
     * ���ķִ�
     *
     * @param string $text �����ı�
     * @param array $options ѡ��
     * @return array �ִʽ��
     */
    private function tokenizeChineseText(string $text, array $options): array
    {
        // ����Ӧ��ʹ��רҵ�����ķִʿ�
        // ���� jieba-php, phpanalysis ��
        // �����������ʹ���ַ��ָʽ��ʵ��Ӧ��Ӧʹ��רҵ�㷨
        $tokens = [];
        $text = trim($text];
        $position = 0;
        
        // ʹ������ʶ��ͬ���͵ĵ�Ԫ
        $pattern = '/([a-zA-Z0-9]+|[\x{4e00}-\x{9fa5}]|[^\s\x{4e00}-\x{9fa5}a-zA-Z0-9])/u';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE];
        
        foreach ($matches[0] as $match) {
            $tokenText = $match[0];
            $start = $match[1];
            $length = mb_strlen($tokenText];
            
            // ȷ��token����
            $type = $this->determineTokenType($tokenText];
            
            // ����Ƿ�Ϊͣ�ô�
            $isStopWord = in_[$tokenText, $this->stopwords[$this->currentLanguage] ?? []];
            
            $token = [
                'text' => $tokenText,
                'start' => $start,
                'end' => $start + strlen($tokenText],
                'length' => $length,
                'type' => $type,
                'is_stop_word' => $isStopWord,
            ];
            
            $tokens[] = $token;
        }
        
        return $tokens;
    }
    
    /**
     * Ӣ�ķִ�
     *
     * @param string $text Ӣ���ı�
     * @param array $options ѡ��
     * @return array �ִʽ��
     */
    private function tokenizeEnglishText(string $text, array $options): array
    {
        $tokens = [];
        $preserve_case = $options['preserve_case'] ?? true;
        
        // �򵥵Ļ��ڿո�ͱ��ķִ�
        $pattern = '/\b\w+\b|[^\w\s]/u';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE];
        
        foreach ($matches[0] as $match) {
            $tokenText = $match[0];
            $start = $match[1];
            
            // �����������Сд����תΪСд
            if (!$preserve_case) {
                $tokenText = strtolower($tokenText];
            }
            
            // ȷ��token����
            $type = $this->determineTokenType($tokenText];
            
            // ����Ƿ�Ϊͣ�ô�
            $isStopWord = in_[strtolower($tokenText], $this->stopwords['en-US'] ?? []];
            
            $token = [
                'text' => $tokenText,
                'start' => $start,
                'end' => $start + strlen($tokenText],
                'length' => mb_strlen($tokenText],
                'type' => $type,
                'is_stop_word' => $isStopWord,
            ];
            
            $tokens[] = $token;
        }
        
        return $tokens;
    }
    
    /**
     * ȷ��token����
     *
     * @param string $token token�ı�
     * @return string token����
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
     * ��ȡͣ�ô��б�
     *
     * @param string|null $language ���Դ���
     * @return array ͣ�ô��б�
     */
    public function getStopwords(?string $language = null): array
    {
        $language = $language ?? $this->currentLanguage;
        
        // ȷ�����Ե�ͣ�ô��Ѽ���
        if (!isset($this->stopwords[$language])) {
            $this->loadStopwords($language];
        }
        
        return $this->stopwords[$language] ?? [];
    }
    
    /**
     * ����Զ���ͣ�ô�
     *
     * @param array $words Ҫ��ӵ�ͣ�ô�
     * @param string|null $language ���Դ���
     * @return bool �Ƿ���ӳɹ�
     */
    public function addStopwords(array $words, ?string $language = null): bool
    {
        $language = $language ?? $this->currentLanguage;
        
        // ȷ�����Ե�ͣ�ô��Ѽ���
        if (!isset($this->stopwords[$language])) {
            $this->loadStopwords($language];
        }
        
        // �ϲ���ȥ��
        $this->stopwords[$language] = array_unique(array_merge($this->stopwords[$language] ?? [],  $words)];
        
        // ���»���
        if ($this->cache && $this->config['use_cache']) {
            $cacheKey = "stopwords_{$language}";
            $this->cache->set($cacheKey, $this->stopwords[$language],  $this->config['cache_ttl']];
        }
        
        return true;
    }
    
    /**
     * �Ƴ�ͣ�ô�
     *
     * @param array $words Ҫ�Ƴ���ͣ�ô�
     * @param string|null $language ���Դ���
     * @return bool �Ƿ��Ƴ��ɹ�
     */
    public function removeStopwords(array $words, ?string $language = null): bool
    {
        $language = $language ?? $this->currentLanguage;
        
        // ȷ�����Ե�ͣ�ô��Ѽ���
        if (!isset($this->stopwords[$language])) {
            $this->loadStopwords($language];
        }
        
        // �Ƴ�ָ����ͣ�ô�
        if (isset($this->stopwords[$language])) {
            $this->stopwords[$language] = array_diff($this->stopwords[$language],  $words];
            
            // ���»���
            if ($this->cache && $this->config['use_cache']) {
                $cacheKey = "stopwords_{$language}";
                $this->cache->set($cacheKey, $this->stopwords[$language],  $this->config['cache_ttl']];
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * ���ִʽ��ת��Ϊ�ַ���
     *
     * @param array $tokens �ִʽ��
     * @param string $delimiter �ָ���
     * @return string ת������ַ���
     */
    public function tokensToString(array $tokens, string $delimiter = ' '): string
    {
        $textArray = array_column($tokens, 'text'];
        return implode($delimiter, $textArray];
    }
    
    /**
     * ���˷ִʽ��
     *
     * @param array $tokens ԭʼ�ִʽ��
     * @param array $options ����ѡ��
     * @return array ���˺�ķִʽ��
     */
    public function filterTokens(array $tokens, array $options = []): array
    {
        $result = [];
        
        // �ϲ�Ĭ��ѡ����û�ѡ��
        $options = array_merge([
            'remove_stopwords' => false,
            'remove_punctuation' => false,
            'min_length' => 0,
            'max_length' => PHP_INT_MAX,
            'types' => null, // ָ��Ҫ����������
        ],  $options];
        
        foreach ($tokens as $token) {
            // ����ͣ�ô�
            if ($options['remove_stopwords'] && ($token['is_stop_word'] ?? false)) {
                continue;
            }
            
            // �������
            if ($options['remove_punctuation'] && $token['type'] === 'PUNCTUATION') {
                continue;
            }
            
            // ��鳤��
            $length = mb_strlen($token['text']];
            if ($length < $options['min_length'] || $length > $options['max_length']) {
                continue;
            }
            
            // �������
            if ($options['types'] !== null && !in_[$token['type'],  (array)$options['types'])) {
                continue;
            }
            
            $result[] = $token;
        }
        
        return $result;
    }
    
    /**
     * ��ȡ�ִ�����Ϣ
     *
     * @return array �ִ�����Ϣ
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
     * �������
     *
     * @param string $text Ҫ�����ı�
     * @return string|null ��⵽�����Դ���
     */
    public function detectLanguage(string $text): ?string
    {
        // �򵥵����Լ���߼�
        // ��ʵ��Ӧ���У�Ӧ��ʹ�ø����ӵ����Լ���㷨
        
        // ��黺��
        if ($this->cache && $this->config['use_cache']) {
            $cacheKey = "lang_detect_" . md5($text];
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey];
            }
        }
        
        // ���������ַ��ı���
        $totalLength = mb_strlen($text];
        if ($totalLength === 0) {
            return null;
        }
        
        // ���������ַ�����
        $chineseCount = preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $text];
        
        // ��������ַ�ռ�ȳ���15%����Ϊ������
        $chineseRatio = $totalLength > 0 ? $chineseCount / $totalLength : 0;
        $detectedLanguage = $chineseRatio > 0.15 ? 'zh-CN' : 'en-US';
        
        // ������
        if ($this->cache && $this->config['use_cache']) {
            $cacheKey = "lang_detect_" . md5($text];
            $this->cache->set($cacheKey, $detectedLanguage, $this->config['cache_ttl']];
        }
        
        return $detectedLanguage;
    }
    
    /**
     * ��ȡ�ʸ�
     *
     * @param string $word Ҫ��ȡ�ʸɵĵ���
     * @param string|null $language ���Դ���
     * @return string ��ȡ�Ĵʸ�
     */
    public function stem(string $word, ?string $language = null): string
    {
        $language = $language ?? $this->currentLanguage;
        
        // ��Բ�ͬ����ʹ�ò�ͬ�Ĵʸ���ȡ�㷨
        switch ($language) {
            case 'en-US':
                // ����Ӧ��ʹ��רҵ��Ӣ�Ĵʸ���ȡ�⣬�� porter stemming algorithm
                // ��ʾ������ʵʵ����Ҫһ�������Ĵʸ���ȡ��
                return $this->porterStem($word];
            case 'zh-CN':
                // ����ͨ������Ҫ�ʸ���ȡ
                return $word;
            default:
                return $word;
        }
    }
    
    /**
     * �򵥵�Porter�ʸ���ȡ�㷨ʵ��
     *
     * @param string $word Ӣ�ĵ���
     * @return string ��ȡ�Ĵʸ�
     */
    private function porterStem(string $word): string
    {
        // תΪСд
        $word = strtolower($word];
        
        // �򻯵�Porter�ʸ���ȡ����
        // ע�⣺����һ���ǳ��򻯵İ汾��ʵ��ʹ��ʱӦ��ʹ��������Porter�㷨
        
        // ��������ʽ
        $word = preg_replace('/(s|es)$/', '', $word];
        
        // ����ing��β
        if (preg_match('/ing$/', $word)) {
            $stem = preg_replace('/ing$/', '', $word];
            // ���ȥ��ing��������3���ַ�������Ϊ����Ч�Ĵʸ�
            if (strlen($stem) >= 3) {
                $word = $stem;
            }
        }
        
        // ����ed��β
        if (preg_match('/ed$/', $word)) {
            $stem = preg_replace('/ed$/', '', $word];
            // ���ȥ��ed��������3���ַ�������Ϊ����Ч�Ĵʸ�
            if (strlen($stem) >= 3) {
                $word = $stem;
            }
        }
        
        // ����ly��β
        $word = preg_replace('/ly$/', '', $word];
        
        return $word;
    }
    
    /**
     * ���λ�ԭ
     *
     * @param string $word Ҫ��ԭ�ĵ���
     * @param string|null $language ���Դ���
     * @return string ��ԭ��Ĵ���
     */
    public function lemmatize(string $word, ?string $language = null): string
    {
        $language = $language ?? $this->currentLanguage;
        
        // ��Բ�ͬ����ʹ�ò�ͬ�Ĵ��λ�ԭ�㷨
        switch ($language) {
            case 'en-US':
                // ����Ӧʹ�ô��λ�ԭ��
                // ��ʾ������ʵʵ����Ҫ�����Ĵ��λ�ԭ��ʹʵ�
                return $this->simpleLemmatize($word];
            case 'zh-CN':
                // ����ͨ������Ҫ���λ�ԭ
                return $word;
            default:
                return $word;
        }
    }
    
    /**
     * �򵥵Ĵ��λ�ԭ�߼�
     *
     * @param string $word ����
     * @return string ��ԭ��Ĵ���
     */
    private function simpleLemmatize(string $word): string
    {
        // תΪСд
        $word = strtolower($word];
        
        // �򵥵�Ӣ�Ĵ��λ�ԭ�ʵ�
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
        
        // ���Ҵʵ�
        if (isset($lemmaDict[$word])) {
            return $lemmaDict[$word];
        }
        
        // �������Ĵ�β�仯
        
        // ������
        if (preg_match('/s$/', $word) && !preg_match('/(ss|us|is|os|xis)$/', $word)) {
            $singular = preg_replace('/s$/', '', $word];
            // ȷ��������ʽ����Ч��
            if (strlen($singular) >= 2) {
                return $singular;
            }
        }
        
        // ���������ies��β����stories -> story��
        if (preg_match('/ies$/', $word)) {
            return preg_replace('/ies$/', 'y', $word];
        }
        
        // �����ȥʱ�͹�ȥ�ִ�
        if (preg_match('/ed$/', $word) && !preg_match('/(eed|ied)$/', $word)) {
            // ȥ��ed
            $lemma = preg_replace('/ed$/', '', $word];
            
            // ��������ڶ����ַ����ظ��ģ�ȥ��һ�����磺stopped -> stop��
            $lemma = preg_replace('/([^aeiou])\1$/', '$1', $lemma];
            
            return $lemma;
        }
        
        // �������ʱ
        if (preg_match('/ing$/', $word)) {
            // ȥ��ing
            $lemma = preg_replace('/ing$/', '', $word];
            
            // �����β���ظ��ĸ�����ĸ��ȥ��һ��
            $lemma = preg_replace('/([^aeiou])\1$/', '$1', $lemma];
            
            // ����ʳ����㹻����Ϊ����Ч�Ĵʸ�
            if (strlen($lemma) >= 2) {
                return $lemma;
            }
        }
        
        return $word;
    }
    
    /**
     * ���õ�ǰ����
     *
     * @param string $language ���Դ���
     * @return void
     */
    public function setLanguage(string $language): void
    {
        if (in_[$language, $this->config['supported_languages'])) {
            $this->currentLanguage = $language;
            
            // ȷ�������Ե�ͣ�ô��Ѽ���
            if (!isset($this->stopwords[$language])) {
                $this->loadStopwords($language];
            }
        } else {
            throw new InvalidArgumentException("��֧�ֵ�����: {$language}"];
        }
    }
    
    /**
     * ��ȡ��ǰ���õ�����
     *
     * @return string ��ǰ���Դ���
     */
    public function getLanguage(): string
    {
        return $this->currentLanguage;
    }
    }

