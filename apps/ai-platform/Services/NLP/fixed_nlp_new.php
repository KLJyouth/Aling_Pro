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
        ], $config);
        
        $this->initializeModels();
    }
    
    /**
     * 初始化NLP模型
     */
    private function initializeModels(): void
    {
        $this->models = [
            'text_classification' => new TextClassificationModel($this->config),
            'sentiment_analysis' => new SentimentAnalysisModel($this->config),
            'entity_recognition' => new EntityRecognitionModel($this->config),
            'language_detection' => new LanguageDetectionModel($this->config),
            'text_summarization' => new TextSummarizationModel($this->config),
            'translation' => new TranslationModel($this->config)
        ];
    }
}