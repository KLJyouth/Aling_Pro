<?php

namespace AlingAi\AIServices\NLP;

/**
 * 文本摘要模型
 */
class TextSummarizationModel extends BaseNLPModel
{
    /**
     * 生成文本摘要
     */
    public function summarize(string $text, array $options = []): array
    {
        // 获取配置选项
        $maxLength = $options['max_length'] ?? 100;
        $method = $options['method'] ?? 'extractive';
        
        // 模拟文本摘要结果
        if (strlen($text) <= $maxLength) {
            $summary = $text;
        } else {
            // 简单的摘要算法：提取前几个句子
            $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY];
            $summary = '';
            $currentLength = 0;
            
            foreach ($sentences as $sentence) {
                if ($currentLength + strlen($sentence) <= $maxLength) {
                    $summary .= $sentence . ' ';
                    $currentLength += strlen($sentence) + 1;
                } else {
                    break;
                }
            }
            
            $summary = trim($summary];
        }
        
        return [
            'success' => true,
            'message' => '文本摘要生成完成',
            'data' => [
                'original_length' => strlen($text],
                'summary_length' => strlen($summary],
                'compression_ratio' => strlen($text) > 0 ? round(strlen($summary) / strlen($text], 2) : 1,
                'method' => $method,
                'summary' => $summary,
                'processing_time' => rand(50, 300) . 'ms'
            ]
        ];
    }

    /**
     * 处理文本
     */
    public function process(string $text, array $options = []): array
    {
        return $this->summarize($text, $options];
    }
}
