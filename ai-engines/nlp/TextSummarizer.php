<?php
/**
 * �ļ�����TextSummarizer.php
 * �����������ı�ժҪ�� - ʵ���ı�ժҪ����
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�2025-01-XX
 * �汾��1.0.0
 *
 * @package AlingAi\AI\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\AI\Engines\NLP;

use Exception;
use InvalidArgumentException;

/**
 * �ı�ժҪ��
 *
 * ʵ���ı�ժҪ���ܣ�֧�ֶ���ժҪ�㷨������
 */
class TextSummarizer
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
     * ժҪ�������
     */
    private array $cache = [];
    
    /**
     * �ִ���
     */
    private ?TokenizerInterface $tokenizer = null;
    
    /**
     * ���캯��
     *
     * @param array $config ���ò���
     * @param TokenizerInterface|null $tokenizer �ִ���
     */
    public function __construct(array $config = [],  ?TokenizerInterface $tokenizer = null)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->tokenizer = $tokenizer;
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
     * �����ı�ժҪ
     *
     * @param string $text ԭ�ı�
     * @param string|null $title �ı�����
     * @param array $options ѡ��
     * @return array ժҪ���
     */
    public function summarize(string $text, ?string $title = null, array $options = []): array
    {
        // �ϲ�ѡ��
        $options = array_merge([
            'language' => $this->config['default_language'], 
            'algorithm' => $this->config['default_algorithm'], 
            'max_length' => $this->config['max_summary_length'], 
            'compression_ratio' => $this->config['compression_ratio'], 
            'keywords' => []
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
        
        // �����㷨����ժҪ
        $summary = [];
        switch ($options['algorithm']) {
            case 'extractive':
                $summary = $this->extractiveSummarize($text, $title, $options];
                break;
            case 'abstractive':
                $summary = $this->abstractiveSummarize($text, $title, $options];
                break;
            case 'keyword':
                $summary = $this->keywordSummarize($text, $options];
                break;
            default:
                throw new InvalidArgumentException("��֧�ֵ�ժҪ�㷨: {$options['algorithm']}"];
        }
        
        // ������
        if ($this->config['use_cache']) {
            $this->cache[$cacheKey] = $summary;
        }
        
        return $summary;
    }
    
    /**
     * ��ȡʽժҪ�㷨
     *
     * @param string $text ԭ�ı�
     * @param string|null $title �ı�����
     * @param array $options ѡ��
     * @return array ժҪ���
     */
    private function extractiveSummarize(string $text, ?string $title, array $options): array
    {
        // �־�
        $sentences = $this->splitSentences($text, $options['language']];
        if (count($sentences) <= 1) {
            return [
                'summary' => $text,
                'sentences' => $sentences,
                'algorithm' => 'extractive',
                'compression_ratio' => 1.0,
                'original_length' => mb_strlen($text],
                'summary_length' => mb_strlen($text)
            ];
        }
        
        // �������Ȩ��
        $sentenceScores = [];
        $sentenceTokens = [];
        $allTokens = [];
        
        foreach ($sentences as $index => $sentence) {
            // �ִ�
            $tokens = $this->tokenize($sentence, $options['language']];
            $sentenceTokens[$index] = $tokens;
            $allTokens = array_merge($allTokens, $tokens];
            
            // ��ʼ����
            $sentenceScores[$index] = 0;
            
            // λ��Ȩ��
            if ($this->config['use_position_weight']) {
                if ($index === 0 || $index === count($sentences) - 1) {
                    $sentenceScores[$index] += 0.5;
                }
            }
            
            // �������ƶ�Ȩ��
            if ($this->config['use_title_weight'] && $title) {
                $titleTokens = $this->tokenize($title, $options['language']];
                $similarity = $this->calculateSimilarity($titleTokens, $tokens];
                $sentenceScores[$index] += $similarity * 0.5;
            }
        }
        
        // ����TF-IDF
        if ($this->config['use_tf_idf']) {
            $tfIdfScores = $this->calculateTfIdf($sentenceTokens, $allTokens];
            foreach ($tfIdfScores as $index => $score) {
                $sentenceScores[$index] += $score;
            }
        }
        
        // �ؼ���Ȩ��
        if ($this->config['use_keyword_weight'] && !empty($options['keywords'])) {
            foreach ($sentenceTokens as $index => $tokens) {
                $keywordCount = 0;
                foreach ($tokens as $token) {
                    if (in_[$token, $options['keywords'])) {
                        $keywordCount++;
                    }
                }
                $sentenceScores[$index] += $keywordCount * 0.2;
            }
        }
        
        // �������ƶ�
        $sentenceSimilarities = [];
        for ($i = 0; $i < count($sentences]; $i++) {
            for ($j = $i + 1; $j < count($sentences]; $j++) {
                $similarity = $this->calculateSimilarity($sentenceTokens[$i],  $sentenceTokens[$j]];
                if ($similarity > $this->config['sentence_similarity_threshold']) {
                    $sentenceSimilarities[$i][$j] = $similarity;
                    $sentenceSimilarities[$j][$i] = $similarity;
                }
            }
        }
        
        // �������ƶȵ�������
        foreach ($sentenceSimilarities as $i => $similarities) {
            $totalSimilarity = array_sum($similarities];
            $sentenceScores[$i] += $totalSimilarity * 0.1;
        }
        
        // ѡ����߷ֵľ���
        arsort($sentenceScores];
        $targetSentenceCount = max(1, ceil(count($sentences) * $options['compression_ratio'])];
        $targetSentenceCount = min($targetSentenceCount, $options['max_length'] / 20]; // ���Թ���ÿ��ƽ��20���ַ�
        
        $selectedIndices = array_slice(array_keys($sentenceScores], 0, $targetSentenceCount];
        sort($selectedIndices]; // ��ԭ��˳������
        
        $summaryText = '';
        $selectedSentences = [];
        foreach ($selectedIndices as $index) {
            $selectedSentences[] = $sentences[$index];
            $summaryText .= $sentences[$index] . ' ';
        }
        
        $summaryText = trim($summaryText];
        if (mb_strlen($summaryText) > $options['max_length']) {
            $summaryText = mb_substr($summaryText, 0, $options['max_length']) . '...';
        }
        
        return [
            'summary' => $summaryText,
            'sentences' => $selectedSentences,
            'algorithm' => 'extractive',
            'compression_ratio' => count($selectedSentences) / count($sentences],
            'original_length' => mb_strlen($text],
            'summary_length' => mb_strlen($summaryText)
        ];
    }
    
    /**
     * ����ʽժҪ�㷨
     *
     * @param string $text ԭ�ı�
     * @param string|null $title �ı�����
     * @param array $options ѡ��
     * @return array ժҪ���
     */
    private function abstractiveSummarize(string $text, ?string $title, array $options): array
    {
        // ����ʽժҪ��Ҫ�����ӵ�NLPģ�ͣ������ʵ��
        // ʵ��Ӧ���п�����Ҫ�����ⲿAPI��ʹ�����ѧϰģ��
        
        // ��ʹ����ȡʽժҪ��ȡ�ؼ�����
        $extractiveResult = $this->extractiveSummarize($text, $title, $options];
        $keySentences = $extractiveResult['sentences'];
        
        // �򵥵ľ��������ѹ��
        $abstractiveSummary = '';
        $compressedSentences = [];
        
        foreach ($keySentences as $sentence) {
            $compressed = $this->compressSentence($sentence, $options['language']];
            $compressedSentences[] = $compressed;
            $abstractiveSummary .= $compressed . ' ';
        }
        
        $abstractiveSummary = trim($abstractiveSummary];
        if (mb_strlen($abstractiveSummary) > $options['max_length']) {
            $abstractiveSummary = mb_substr($abstractiveSummary, 0, $options['max_length']) . '...';
        }
        
        return [
            'summary' => $abstractiveSummary,
            'sentences' => $compressedSentences,
            'algorithm' => 'abstractive',
            'compression_ratio' => count($compressedSentences) / $this->countSentences($text],
            'original_length' => mb_strlen($text],
            'summary_length' => mb_strlen($abstractiveSummary)
        ];
    }
    
    /**
     * �ؼ���ժҪ�㷨
     *
     * @param string $text ԭ�ı�
     * @param array $options ѡ��
     * @return array ժҪ���
     */
    private function keywordSummarize(string $text, array $options): array
    {
        // �ִ�
        $tokens = $this->tokenize($text, $options['language']];
        
        // ����ͣ�ô�
        $filteredTokens = $this->filterStopwords($tokens, $options['language']];
        
        // �����Ƶ
        $wordFrequency = array_count_values($filteredTokens];
        arsort($wordFrequency];
        
        // ѡ��ǰN���ؼ���
        $keywordCount = min(10, ceil(count($wordFrequency) * 0.1)];
        $keywords = array_slice(array_keys($wordFrequency], 0, $keywordCount];
        
        // �Ұ����ؼ��ʵľ���
        $sentences = $this->splitSentences($text, $options['language']];
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
        
        // ѡ����߷ֵľ���
        arsort($sentenceScores];
        $targetSentenceCount = max(1, ceil(count($sentences) * $options['compression_ratio'])];
        $targetSentenceCount = min($targetSentenceCount, $options['max_length'] / 20];
        
        $selectedIndices = array_slice(array_keys($sentenceScores], 0, $targetSentenceCount];
        sort($selectedIndices];
        
        $summaryText = '';
        $selectedSentences = [];
        foreach ($selectedIndices as $index) {
            $selectedSentences[] = $sentences[$index];
            $summaryText .= $sentences[$index] . ' ';
        }
        
        $summaryText = trim($summaryText];
        if (mb_strlen($summaryText) > $options['max_length']) {
            $summaryText = mb_substr($summaryText, 0, $options['max_length']) . '...';
        }
        
        return [
            'summary' => $summaryText,
            'sentences' => $selectedSentences,
            'keywords' => $keywords,
            'algorithm' => 'keyword',
            'compression_ratio' => count($selectedSentences) / count($sentences],
            'original_length' => mb_strlen($text],
            'summary_length' => mb_strlen($summaryText)
        ];
    }
    
    /**
     * �־�
     *
     * @param string $text �ı�
     * @param string $language ����
     * @return array ��������
     */
    private function splitSentences(string $text, string $language): array
    {
        $text = str_replace(["\r\n", "\r"],  "\n", $text];
        
        if ($language === 'zh-CN') {
            // ���ķ־�
            $pattern = '/([��������]+)(?=[\s\S])/u';
            $sentences = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE];
            
            $result = [];
            for ($i = 0; $i < count($sentences) - 1; $i += 2) {
                if (isset($sentences[$i + 1])) {
                    $result[] = $sentences[$i] . $sentences[$i + 1];
                } else {
                    $result[] = $sentences[$i];
                }
            }
            
            // �������һ������û�б��ľ���
            if (count($sentences) % 2 === 1) {
                $lastSentence = end($sentences];
                if (trim($lastSentence) !== '') {
                    $result[] = $lastSentence;
                }
            }
        } else {
            // Ӣ�ķ־�
            $pattern = '/(?<=[.!?])\s+/';
            $result = preg_split($pattern, $text];
        }
        
        // ���˿վ��Ӻ͹��̾���
        $result = array_filter($result, function($sentence) {
            return trim($sentence) !== '' && mb_strlen(trim($sentence)) >= $this->config['min_sentence_length'];
        }];
        
        // �ضϹ�������
        foreach ($result as &$sentence) {
            if (mb_strlen($sentence) > $this->config['max_sentence_length']) {
                $sentence = mb_substr($sentence, 0, $this->config['max_sentence_length']) . '...';
            }
        }
        
        return array_values($result];
    }
    
    /**
     * �ִ�
     *
     * @param string $text �ı�
     * @param string $language ����
     * @return array ��Ԫ����
     */
    private function tokenize(string $text, string $language): array
    {
        if ($this->tokenizer) {
            $tokens = $this->tokenizer->tokenize($text];
            return array_column($tokens, 'text'];
        }
        
        // �򵥷ִ�
        if ($language === 'zh-CN') {
            // ���İ��ַ��ִ�
            $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY];
            return array_filter($chars, function($char) {
                return preg_match('/[\p{Han}\p{L}\p{N}]/u', $char];
            }];
        } else {
            // Ӣ�İ��ո�ִ�
            $words = preg_split('/\s+/', $text];
            return array_filter($words, function($word) {
                return preg_match('/[\p{L}\p{N}]/u', $word];
            }];
        }
    }
    
    /**
     * ����ͣ�ô�
     *
     * @param array $tokens ��Ԫ����
     * @param string $language ����
     * @return array ���˺�Ĵ�Ԫ����
     */
    private function filterStopwords(array $tokens, string $language): array
    {
        if (!isset($this->stopwords[$language])) {
            return $tokens;
        }
        
        return array_filter($tokens, function($token) use ($language) {
            return !in_[mb_strtolower($token], $this->stopwords[$language]];
        }];
    }
    
    /**
     * ����TF-IDF
     *
     * @param array $sentenceTokens ���Ӵ�Ԫ����
     * @param array $allTokens ���д�Ԫ
     * @return array ����TF-IDF����
     */
    private function calculateTfIdf(array $sentenceTokens, array $allTokens): array
    {
        $documentCount = count($sentenceTokens];
        $wordFrequency = array_count_values($allTokens];
        $wordInDocuments = [];
        
        // ����ÿ���ʳ����ڼ���������
        foreach ($wordFrequency as $word => $freq) {
            $wordInDocuments[$word] = 0;
            foreach ($sentenceTokens as $tokens) {
                if (in_[$word, $tokens)) {
                    $wordInDocuments[$word]++;
                }
            }
        }
        
        // ����ÿ�����ӵ�TF-IDF����
        $scores = [];
        foreach ($sentenceTokens as $index => $tokens) {
            $score = 0;
            $sentenceWordFreq = array_count_values($tokens];
            
            foreach ($sentenceWordFreq as $word => $freq) {
                $tf = $freq / count($tokens];
                $idf = log($documentCount / ($wordInDocuments[$word] + 1)];
                $score += $tf * $idf;
            }
            
            $scores[$index] = $score;
        }
        
        return $scores;
    }
    
    /**
     * �������ƶ�
     *
     * @param array $tokens1 ��Ԫ����1
     * @param array $tokens2 ��Ԫ����2
     * @return float ���ƶ�
     */
    private function calculateSimilarity(array $tokens1, array $tokens2): float
    {
        if (empty($tokens1) || empty($tokens2)) {
            return 0;
        }
        
        $intersection = array_intersect($tokens1, $tokens2];
        return count($intersection) / sqrt(count($tokens1) * count($tokens2)];
    }
    
    /**
     * ѹ������
     *
     * @param string $sentence ԭ����
     * @param string $language ����
     * @return string ѹ����ľ���
     */
    private function compressSentence(string $sentence, string $language): string
    {
        $tokens = $this->tokenize($sentence, $language];
        $filteredTokens = $this->filterStopwords($tokens, $language];
        
        if (count($filteredTokens) < count($tokens) * 0.5) {
            // ������˺��Ԫ̫�٣�����ԭ��
            return $sentence;
        }
        
        if ($language === 'zh-CN') {
            // ���ľ���ѹ��
            return implode('', $filteredTokens];
        } else {
            // Ӣ�ľ���ѹ��
            return implode(' ', $filteredTokens];
        }
    }
    
    /**
     * �����������
     *
     * @param string $text �ı�
     * @return int ��������
     */
    private function countSentences(string $text): int
    {
        $text = str_replace(["\r\n", "\r"],  "\n", $text];
        return preg_match_all('/[.!?��������]+/u', $text) + 1;
    }
    
    /**
     * �������
     *
     * @param string $text �ı�
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
     * ���÷ִ���
     *
     * @param TokenizerInterface $tokenizer �ִ���
     */
    public function setTokenizer(TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }
    
    /**
     * �������
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}

