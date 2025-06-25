<?php

namespace AlingAi\AIServices\NLP;

/**
 * NLP模型基类
 */
abstract class BaseNLPModel
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    abstract public function process(string $text, array $options = []): array;
}
