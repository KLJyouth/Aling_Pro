<?php

namespace AlingAi\AIServices\NLP;

/**
 * 实体识别模型
 */
class EntityRecognitionModel extends BaseNLPModel
{
    /**
     * 识别文本中的实体
     */
    public function recognizeEntities(string $text, array $options = []): array
    {
        // 简单的实体识别示例
        $entities = [];
        
        // 模拟人名识别
        if (preg_match_all('/[A-Z][a-z]+ [A-Z][a-z]+/', $text, $matches)) {
            foreach ($matches[0] as $name) {
                $entities[] = [
                    'text' => $name,
                    'type' => 'PERSON',
                    'confidence' => round(rand(75, 98) / 100, 2),
                    'start_pos' => strpos($text, $name),
                    'end_pos' => strpos($text, $name) + strlen($name)
                ];
            }
        }
        
        // 模拟日期识别
        if (preg_match_all('/\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4}/', $text, $matches)) {
            foreach ($matches[0] as $date) {
                $entities[] = [
                    'text' => $date,
                    'type' => 'DATE',
                    'confidence' => round(rand(85, 99) / 100, 2),
                    'start_pos' => strpos($text, $date),
                    'end_pos' => strpos($text, $date) + strlen($date)
                ];
            }
        }
        
        // 模拟地点识别 (简化版)
        $locations = ['Beijing', 'Shanghai', 'New York', 'London', 'Tokyo'];
        foreach ($locations as $location) {
            if (stripos($text, $location) !== false) {
                $entities[] = [
                    'text' => $location,
                    'type' => 'LOCATION',
                    'confidence' => round(rand(80, 95) / 100, 2),
                    'start_pos' => stripos($text, $location),
                    'end_pos' => stripos($text, $location) + strlen($location)
                ];
            }
        }
        
        return [
            'success' => true,
            'message' => '实体识别完成',
            'data' => [
                'entities' => $entities,
                'entity_count' => count($entities),
                'processing_time' => rand(40, 180) . 'ms'
            ]
        ];
    }

    /**
     * 处理文本
     */
    public function process(string $text, array $options = []): array
    {
        return $this->recognizeEntities($text, $options);
    }
}