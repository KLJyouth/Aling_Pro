<?php


namespace AlingAi\AIServices\NLP;


/**
 * ��Ȼ���Դ������
 */
class NaturalLanguageProcessor
{
    private array $config;
    private array $models;

    public function __construct(array $config = []]
    {
        $this->config = array_merge([
            'default_model' => 'gpt-4o-mini',
            'max_tokens' => 4096,
            'temperature' => 0.7,
            'timeout' => 30
        ], $config];
        
        $this->initializeModels(];
    }
}

