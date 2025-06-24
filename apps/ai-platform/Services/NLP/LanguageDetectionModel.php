<?php

namespace AlingAi\AIServices\NLP;

/**
 * 语言检测模型
 */
class LanguageDetectionModel extends BaseNLPModel
{
    /**
     * 检测文本语言
     */
    public function detectLanguage(string $text, array $options = []): array
    {
        // 模拟语言检测结果
        $languages = [
            'en' => ['name' => 'English', 'confidence' => 0],
            'zh' => ['name' => 'Chinese', 'confidence' => 0],
            'es' => ['name' => 'Spanish', 'confidence' => 0],
            'fr' => ['name' => 'French', 'confidence' => 0],
            'de' => ['name' => 'German', 'confidence' => 0],
            'ja' => ['name' => 'Japanese', 'confidence' => 0],
            'ru' => ['name' => 'Russian', 'confidence' => 0]
        ];
        
        // 基于字符集的简单检测
        $hasChineseChars = preg_match('/[\x{4e00}-\x{9fa5}]/u', $text);
        $hasJapaneseChars = preg_match('/[\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $text);
        $hasCyrillicChars = preg_match('/[\x{0400}-\x{04FF}]/u', $text);
        
        if ($hasChineseChars) {
            $languages['zh']['confidence'] = 0.9;
        } elseif ($hasJapaneseChars) {
            $languages['ja']['confidence'] = 0.85;
        } elseif ($hasCyrillicChars) {
            $languages['ru']['confidence'] = 0.85;
        } else {
            // 简单的英文/西班牙语/法语/德语检测
            $languages['en']['confidence'] = 0.4;
            $languages['es']['confidence'] = 0.2;
            $languages['fr']['confidence'] = 0.2;
            $languages['de']['confidence'] = 0.2;
            
            // 检测一些常见的西班牙语词汇
            if (preg_match('/\b(hola|gracias|buenos|días|señor)\b/i', $text)) {
                $languages['es']['confidence'] += 0.4;
            }
            
            // 检测一些常见的法语词汇
            if (preg_match('/\b(bonjour|merci|monsieur|madame|oui)\b/i', $text)) {
                $languages['fr']['confidence'] += 0.4;
            }
            
            // 检测一些常见的德语词汇
            if (preg_match('/\b(guten|danke|bitte|schön|herr|frau)\b/i', $text)) {
                $languages['de']['confidence'] += 0.4;
            }
            
            // 检测一些常见的英语词汇
            if (preg_match('/\b(the|and|is|in|to|of|that|for)\b/i', $text)) {
                $languages['en']['confidence'] += 0.3;
            }
        }
        
        // 排序并找出最可能的语言
        uasort($languages, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        $detectedLanguage = array_key_first($languages);
        
        return [
            'success' => true,
            'message' => '语言检测完成',
            'data' => [
                'detected_language' => $detectedLanguage,
                'language_name' => $languages[$detectedLanguage]['name'],
                'confidence' => $languages[$detectedLanguage]['confidence'],
                'all_languages' => $languages,
                'processing_time' => rand(20, 100) . 'ms'
            ]
        ];
    }

    /**
     * 处理文本
     */
    public function process(string $text, array $options = []): array
    {
        return $this->detectLanguage($text, $options);
    }
}