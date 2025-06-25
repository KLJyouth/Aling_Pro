<?php
declare(strict_types=1];

/**
 * �ļ�����KeywordExtractor.php
 * �����������ؼ�����ȡ�� - ʵ���ı��ؼ�����ȡ����
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

/**
 * �ؼ�����ȡ��
 *
 * ʵ���ı��ؼ�����ȡ���ܣ�֧�ֶ�����ȡ�㷨������
 */
class KeywordExtractor
{
    /**
     * ���ò���
     */
    private array $config;

    /**
     * ͣ�ô��б�
     */
    private array $stopwords = [];

    /**
     * �ؼ�����ȡ�������
     */
    private array $cache = [];
    
    /**
     * ���캯��
     *
     * @param array $config ���ò���
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->loadStopwords(];
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
            'default_algorithm' => 'tfidf',
            'max_keywords' => 10,
            'min_word_length' => 2,
            'use_cache' => true,
            'cache_ttl' => 3600
        ];
    }
    
    /**
     * ����ͣ�ô�
     */
    private function loadStopwords(): void
    {
        // Ӣ��ͣ�ô�
        $this->stopwords['en-US'] = [
            'a', 'an', 'the', 'and', 'or', 'but', 'if', 'then', 'else', 'when',
            'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to', 'into', 'with',
            'is', 'am', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing',
            'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them',
            'this', 'that', 'these', 'those', 'my', 'your', 'his', 'her', 'its', 'our', 'their'
        ];
        
        // ����ͣ�ô�
        $this->stopwords['zh-CN'] = [
            '��', '��', '��', '��', '��', '��', '��', '��', '��', '��', '��', '��', '��', '��',
            'Ϊ', '��', 'Ҳ', '��', '֮', '��', '��', '��', '��', '��', '��', '��', '��', '��',
            '��', '��', '��', '��', 'ĳ', '��', 'ÿ', '��', '��', '��', '��', '��', '��', '��'
        ];
    }
    
    /**
     * ��ȡ�ؼ���
     *
     * @param string $text �ı�����
     * @param array $options ѡ��
     * @return array �ؼ�������
     */
    public function extract(string $text, array $options = []): array
    {
        // �ϲ�ѡ��
        $options = array_merge([
            'language' => $this->config['default_language'], 
            'algorithm' => $this->config['default_algorithm'], 
            'max_keywords' => $this->config['max_keywords'], 
            'min_word_length' => $this->config['min_word_length']
        ],  $options];
        
        // ��黺��
        $cacheKey = md5($text . json_encode($options)];
        if ($this->config['use_cache'] && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // �������
        if ($options['language'] === 'auto') {
            $options['language'] = $this->detectLanguage($text];
        }
        
        // �����㷨��ȡ�ؼ���
        $keywords = [];
        switch ($options['algorithm']) {
            case 'tfidf':
                $keywords = $this->extractByTfIdf($text, $options];
                break;
            case 'textrank':
                $keywords = $this->extractByTextRank($text, $options];
                break;
            case 'rake':
                $keywords = $this->extractByRake($text, $options];
                break;
            default:
                throw new InvalidArgumentException("��֧�ֵĹؼ�����ȡ�㷨: {$options['algorithm']}"];
        }
        
        // ������
        if ($this->config['use_cache']) {
            $this->cache[$cacheKey] = $keywords;
        }
        
        return $keywords;
    }
    
    /**
     * ʹ��TF-IDF�㷨��ȡ�ؼ���
     *
     * @param string $text �ı�����
     * @param array $options ѡ��
     * @return array �ؼ�������
     */
    private function extractByTfIdf(string $text, array $options): array
    {
        // �ִ�
        $tokens = $this->tokenize($text, $options['language']];
        
        // ����ͣ�ôʺͶ̴�
        $filteredTokens = $this->filterTokens($tokens, $options];
        
        // �����Ƶ
        $termFrequency = array_count_values($filteredTokens];
        
        // ����TF-IDFֵ
        $tfidfScores = [];
        $documentCount = 1; // ��ʵ��Ӧ���У���Ӧ�������Ͽ��е��ĵ�����
        
        foreach ($termFrequency as $term => $frequency) {
            // �򻯰�TF-IDF���㣬��ʵ��Ӧ����Ӧ��ʹ�ø����ӵĹ�ʽ
            $tf = $frequency / count($filteredTokens];
            $idf = log($documentCount / 1]; // �򻯰�IDF
            $tfidfScores[$term] = $tf * $idf;
        }
        
        // ����������
        arsort($tfidfScores];
        
        // ��ȡǰN���ؼ���
        $keywords = [];
        $count = 0;
        foreach ($tfidfScores as $term => $score) {
            if ($count >= $options['max_keywords']) {
                break;
            }
            
            $keywords[] = [
                'keyword' => $term,
                'score' => round($score, 4],
                'algorithm' => 'tfidf'
            ];
            
            $count++;
        }
        
        return $keywords;
    }
    
    /**
     * ʹ��TextRank�㷨��ȡ�ؼ���
     *
     * @param string $text �ı�����
     * @param array $options ѡ��
     * @return array �ؼ�������
     */
    private function extractByTextRank(string $text, array $options): array
    {
        // �ִ�
        $tokens = $this->tokenize($text, $options['language']];
        
        // ����ͣ�ôʺͶ̴�
        $filteredTokens = $this->filterTokens($tokens, $options];
        
        // �����ʹ��־���
        $cooccurrenceMatrix = [];
        $window = 5; // ���ִ��ڴ�С
        
        for ($i = 0; $i < count($filteredTokens]; $i++) {
            $word = $filteredTokens[$i];
            
            // ��ʼ������
            if (!isset($cooccurrenceMatrix[$word])) {
                $cooccurrenceMatrix[$word] = [];
            }
            
            // ��鴰���ڵĴ�
            for ($j = max(0, $i - $window]; $j <= min(count($filteredTokens) - 1, $i + $window]; $j++) {
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
        
        // ��ʼ��TextRank����
        $scores = [];
        $uniqueTokens = array_unique($filteredTokens];
        
        foreach ($uniqueTokens as $token) {
            $scores[$token] = 1.0;
        }
        
        // TextRank����
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
                        $outSum = array_sum($cooccurrenceMatrix[$coword]];
                        $newScore += $damping * $weight * $scores[$coword] / $outSum;
                    }
                }
                
                $change = abs($newScore - $scores[$token]];
                $maxChange = max($maxChange, $change];
                $newScores[$token] = $newScore;
            }
            
            $scores = $newScores;
            
            // ����仯С����ֵ����ǰ��������
            if ($maxChange < $threshold) {
                break;
            }
        }
        
        // ����������
        arsort($scores];
        
        // ��ȡǰN���ؼ���
        $keywords = [];
        $count = 0;
        foreach ($scores as $term => $score) {
            if ($count >= $options['max_keywords']) {
                break;
            }
            
            $keywords[] = [
                'keyword' => $term,
                'score' => round($score, 4],
                'algorithm' => 'textrank'
            ];
            
            $count++;
        }
        
        return $keywords;
    }
    
    /**
     * ʹ��RAKE�㷨��ȡ�ؼ���
     *
     * @param string $text �ı�����
     * @param array $options ѡ��
     * @return array �ؼ�������
     */
    private function extractByRake(string $text, array $options): array
    {
        // �־�
        $sentences = $this->splitSentences($text, $options['language']];
        
        // ��ȡ��ѡ����
        $candidatePhrases = [];
        
        foreach ($sentences as $sentence) {
            // ʹ��ͣ�ôʺͱ�������Ϊ�ָ���
            $phrases = preg_split('/[' . $this->getPunctuationPattern() . ']+/u', $sentence];
            
            foreach ($phrases as $phrase) {
                $phrase = trim($phrase];
                if (empty($phrase)) {
                    continue;
                }
                
                $words = explode(' ', $phrase];
                $filteredWords = [];
                
                // ����ͣ�ôʺͶ̴�
                foreach ($words as $word) {
                    $word = trim($word];
                    if (!empty($word) && 
                        !in_[mb_strtolower($word], $this->stopwords[$options['language']]) && 
                        mb_strlen($word) >= $options['min_word_length']) {
                        $filteredWords[] = $word;
                    }
                }
                
                if (!empty($filteredWords)) {
                    $candidatePhrases[] = implode(' ', $filteredWords];
                }
            }
        }
        
        // �����Ƶ�ʹʹ��ֶ�
        $wordFrequency = [];
        $wordDegree = [];
        
        foreach ($candidatePhrases as $phrase) {
            $words = explode(' ', $phrase];
            $wordCount = count($words];
            
            foreach ($words as $word) {
                if (!isset($wordFrequency[$word])) {
                    $wordFrequency[$word] = 0;
                    $wordDegree[$word] = 0;
                }
                
                $wordFrequency[$word]++;
                $wordDegree[$word] += $wordCount;
            }
        }
        
        // ����ÿ���ʵķ���
        $wordScores = [];
        foreach ($wordFrequency as $word => $freq) {
            $wordScores[$word] = $wordDegree[$word] / $freq;
        }
        
        // ����������
        $phraseScores = [];
        foreach ($candidatePhrases as $phrase) {
            $words = explode(' ', $phrase];
            $score = 0;
            
            foreach ($words as $word) {
                $score += $wordScores[$word];
            }
            
            $phraseScores[$phrase] = $score;
        }
        
        // ����������
        arsort($phraseScores];
        
        // ��ȡǰN���ؼ�����
        $keywords = [];
        $count = 0;
        foreach ($phraseScores as $phrase => $score) {
            if ($count >= $options['max_keywords']) {
                break;
            }
            
            $keywords[] = [
                'keyword' => $phrase,
                'score' => round($score, 4],
                'algorithm' => 'rake'
            ];
            
            $count++;
        }
        
        return $keywords;
    }
    
    /**
     * �ִ�
     *
     * @param string $text �ı�����
     * @param string $language ����
     * @return array ��Ԫ����
     */
    private function tokenize(string $text, string $language): array
    {
        // �򵥷ִ�ʵ�֣�ʵ��Ӧ����Ӧ��ʹ��רҵ�ķִ���
        if ($language === 'zh-CN') {
            // ���ķִʣ��򻯰棩
            $text = preg_replace('/[^\p{Han}]/u', ' ', $text];
            $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY];
            return array_filter($chars];
        } else {
            // Ӣ�ķִ�
            $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text];
            return array_filter(explode(' ', $text)];
        }
    }
    
    /**
     * ���˴�Ԫ
     *
     * @param array $tokens ��Ԫ����
     * @param array $options ѡ��
     * @return array ���˺�Ĵ�Ԫ����
     */
    private function filterTokens(array $tokens, array $options): array
    {
        $language = $options['language'];
        $minWordLength = $options['min_word_length'];
        
        return array_filter($tokens, function($token) use ($language, $minWordLength) {
            return mb_strlen($token) >= $minWordLength && 
                   !in_[mb_strtolower($token], $this->stopwords[$language]];
        }];
    }
    
    /**
     * �־�
     *
     * @param string $text �ı�����
     * @param string $language ����
     * @return array ��������
     */
    private function splitSentences(string $text, string $language): array
    {
        if ($language === 'zh-CN') {
            // ���ķ־�
            return preg_split('/[������]/u', $text];
        } else {
            // Ӣ�ķ־�
            return preg_split('/[.!?]/u', $text];
        }
    }
    
    /**
     * ��ȡ������������ʽ
     *
     * @return string ������ʽ
     */
    private function getPunctuationPattern(): string
    {
        return '\p{P}\s';
    }
    
    /**
     * �������
     *
     * @param string $text �ı�����
     * @return string ���Դ���
     */
    private function detectLanguage(string $text): string
    {
        // �����Լ�⣺���������ַ�����
        $totalChars = mb_strlen($text];
        if ($totalChars === 0) {
            return $this->config['default_language'];
        }
        
        $chineseChars = preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $text];
        $chineseRatio = $chineseChars / $totalChars;
        
        return $chineseRatio > 0.1 ? 'zh-CN' : 'en-US';
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
     * �������
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}

