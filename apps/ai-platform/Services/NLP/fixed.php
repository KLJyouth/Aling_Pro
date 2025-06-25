<?php

namespace AlingAi\AIServices\NLP;

/**
 * 自然语言处理服务
 */
class NaturalLanguageProcessor
{
    private array $config;
    private array $models;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'default_model' => 'gpt-4o-mini',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'timeout' => 30
        ],  $config];
        
        $this->initializeModels(];
    }

    /**
     * 初始化NLP模型
     */
    private function initializeModels(): void
    {
        $this->models = [
            'text_analysis' => new TextAnalysisModel($this->config],
            'sentiment_analysis' => new SentimentAnalysisModel($this->config],
            'entity_extraction' => new EntityExtractionModel($this->config],
            'text_classification' => new TextClassificationModel($this->config],
            'language_detection' => new LanguageDetectionModel($this->config],
            'text_summarization' => new TextSummarizationModel($this->config],
            'question_answering' => new QuestionAnsweringModel($this->config],
            'text_generation' => new TextGenerationModel($this->config)
        ];
    }

    /**
     * 文本分析
     */
    public function analyzeText(string $text, array $options = []): array
    {
        try {
            $results = [
                'text' => $text,
                'length' => strlen($text],
                'word_count' => str_word_count($text],
                'sentiment' => $this->models['sentiment_analysis']->analyze($text],
                'entities' => $this->models['entity_extraction']->extract($text],
                'language' => $this->models['language_detection']->detect($text],
                'classification' => $this->models['text_classification']->classify($text],
                'keywords' => $this->extractKeywords($text],
                'readability' => $this->calculateReadability($text],
                'analysis_time' => date('Y-m-d H:i:s')
            ];

            // 如果需要详细分�?
            if ($options['detailed'] ?? false) {
                $results['detailed_analysis'] = [
                    'sentence_count' => substr_count($text, '.') + substr_count($text, '!') + substr_count($text, '?'],
                    'paragraph_count' => substr_count($text, "\n\n") + 1,
                    'complexity_score' => $this->calculateComplexity($text],
                    'tone_analysis' => $this->analyzeTone($text],
                    'topics' => $this->extractTopics($text)
                ];
            }

            return $results;
        } catch (\Exception $e) {
            throw new \RuntimeException("文本分析失败: " . $e->getMessage()];
        }
    }
} 
