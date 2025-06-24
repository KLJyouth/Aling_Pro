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
            'max_image_size' => 10 * 1024 * 1024, // 10MB
            'supported_formats' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
            'default_quality' => 85,
            'timeout' => 60
        ], $config);
        
        $this->initializeModels();
    }

    /**
     * 初始化CV模型
     */
    private function initializeModels(): void
    {
        $this->models = [
            'image_analysis' => new ImageAnalysisModel($this->config),
            'object_detection' => new ObjectDetectionModel($this->config),
            'face_recognition' => new FaceRecognitionModel($this->config),
            'text_recognition' => new TextRecognitionModel($this->config),
            'image_classification' => new ImageClassificationModel($this->config),
            'image_enhancement' => new ImageEnhancementModel($this->config),
            'scene_analysis' => new SceneAnalysisModel($this->config),
            'content_moderation' => new ContentModerationModel($this->config)
        ];
    }

    /**
     * 图像分析
     */
    public function analyzeImage(string $imagePath, array $options = []): array
    {
        try {
            if (!$this->validateImage($imagePath)) {
                throw new \InvalidArgumentException("无效的图像文件");
            }

            $imageInfo = $this->getImageInfo($imagePath);
            
            $results = [
                'file_info' => $imageInfo,
                'basic_analysis' => $this->models['image_analysis']->analyze($imagePath),
                'objects' => $this->models['object_detection']->detect($imagePath),
                'faces' => $this->models['face_recognition']->detectFaces($imagePath),
                'text' => $this->models['text_recognition']->extractText($imagePath),
                'classification' => $this->models['image_classification']->classify($imagePath),
                'scene' => $this->models['scene_analysis']->analyzeScene($imagePath),
                'analysis_time' => date('Y-m-d H:i:s')
            ];

            // 如果需要详细分析
            if ($options['detailed'] ?? false) {
                $results['detailed_analysis'] = [
                    'color_analysis' => $this->analyzeColors($imagePath),
                    'composition' => $this->analyzeComposition($imagePath),
                    'quality_metrics' => $this->assessImageQuality($imagePath),
                    'metadata' => $this->extractMetadata($imagePath)
                ];
            }

            // 内容审核
            if ($options['content_moderation'] ?? false) {
                $results['content_moderation'] = $this->models['content_moderation']->moderate($imagePath);
            }

            return $results;
        } catch (\Exception $e) {
            throw new \RuntimeException("图像分析失败: " . $e->getMessage());
        }
    }
} 