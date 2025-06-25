<?php

namespace AlingAi\AIServices\CV;

/**
 * 计算机视觉处理服务
 */
class ComputerVisionProcessor
{
    private array $config;
    private array $models;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            "max_image_size" => 10 * 1024 * 1024, // 10MB
            "supported_formats" => ["jpg", "jpeg", "png", "gif", "bmp", "webp"], 
            "default_quality" => 85,
            "timeout" => 60
        ],  $config];
        
        $this->initializeModels(];
    }
}
