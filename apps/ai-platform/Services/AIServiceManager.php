<?php
/**
 * AlingAi Pro 6.0 - AI平台服务管理器
 * AI Platform Service Manager - 多模态AI融合引擎
 * 
 * @package AlingAi\AI\Services
 * @version 6.0.0
 * @author AlingAi Team
 * @copyright 2025 AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\AI\Services;

use DI\Container;
use Psr\Log\LoggerInterface;
use AlingAi\Core\Services\AbstractServiceManager;
use AlingAi\AIServices\NLP\NaturalLanguageProcessor;
use AlingAi\AIServices\CV\ComputerVisionProcessor;
use AlingAi\AIServices\Speech\SpeechProcessor;
use AlingAi\AIServices\KnowledgeGraph\KnowledgeGraphProcessor;

/**
 * AI平台服务管理器
 * 
 * 管理多模态AI能力:
 * - 自然语言处理 (NLP)
 * - 计算机视觉 (CV)
 * - 语音识别与合成
 * - 知识图谱构建
 * - 智能推荐系统
 * - 预测分析引擎
 * - AI模型管理
 * - AI编排服务
 * - 提示词管理
 * - AI伦理监控
 */
class AIServiceManager extends AbstractServiceManager
{
    private NaturalLanguageProcessor $nlpProcessor;
    private ComputerVisionProcessor $visionProcessor;
    private SpeechProcessor $speechProcessor;
    private KnowledgeGraphProcessor $knowledgeProcessor;
    
    private array $activeModels = [];
    private array $modelMetrics = [];
    private array $serviceStatus = [];
    
    public function __construct(Container $container, LoggerInterface $logger) {
        parent::__construct($container, $logger);
        $this->initializeAIServices();
    }
    
    /**
     * 初始化AI服务
     */
    private function initializeAIServices(): void
    {
        $this->logger->info('Initializing AI Platform Services...');
        
        try {
            // 初始化NLP处理器
            $this->nlpProcessor = new NaturalLanguageProcessor([
                'default_model' => 'gpt-4o-mini',
                'max_tokens' => 4096,
                'temperature' => 0.7
            ]);
            
            // 初始化计算机视觉处理器
            $this->visionProcessor = new ComputerVisionProcessor([
                'max_image_size' => 10 * 1024 * 1024,
                'supported_formats' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
                'default_quality' => 85
            ]);
            
            // 初始化语音处理器
            $this->speechProcessor = new SpeechProcessor([
                'max_audio_size' => 50 * 1024 * 1024,
                'supported_formats' => ['mp3', 'wav', 'flac', 'm4a', 'ogg'],
                'default_language' => 'zh-CN'
            ]);
            
            // 初始化知识图谱处理器
            $this->knowledgeProcessor = new KnowledgeGraphProcessor([
                'max_nodes' => 10000,
                'max_relationships' => 50000,
                'cache_enabled' => true,
                'inference_enabled' => true
            ]);
            
            // 更新服务状态
            $this->updateServiceStatus();
            
            $this->logger->info('AI Platform Services initialized successfully');
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to initialize AI Platform Services: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 文本分析服务
     */
    public function analyzeText(string $text, array $options = []): array
    {
        try {
            $this->logger->info('Processing text analysis request', ['text_length' => strlen($text)]);
            
            $result = $this->nlpProcessor->analyzeText($text, $options);
            
            // 记录使用指标
            $this->recordUsageMetrics('text_analysis', [
                'text_length' => strlen($text),
                'processing_time' => microtime(true)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Text analysis failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 图像分析服务
     */
    public function analyzeImage(string $imagePath, array $options = []): array
    {
        try {
            $this->logger->info('Processing image analysis request', ['image_path' => $imagePath]);
            
            $result = $this->visionProcessor->analyzeImage($imagePath, $options);
            
            // 记录使用指标
            $this->recordUsageMetrics('image_analysis', [
                'image_path' => $imagePath,
                'processing_time' => microtime(true)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Image analysis failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 语音转文字服务
     */
    public function speechToText(string $audioPath, array $options = []): array
    {
        try {
            $this->logger->info('Processing speech to text request', ['audio_path' => $audioPath]);
            
            $result = $this->speechProcessor->speechToText($audioPath, $options);
            
            // 记录使用指标
            $this->recordUsageMetrics('speech_to_text', [
                'audio_path' => $audioPath,
                'processing_time' => microtime(true)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Speech to text failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 文字转语音服务
     */
    public function textToSpeech(string $text, array $options = []): array
    {
        try {
            $this->logger->info('Processing text to speech request', ['text_length' => strlen($text)]);
            
            $result = $this->speechProcessor->textToSpeech($text, $options);
            
            // 记录使用指标
            $this->recordUsageMetrics('text_to_speech', [
                'text_length' => strlen($text),
                'processing_time' => microtime(true)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Text to speech failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 知识图谱构建服务
     */
    public function buildKnowledgeGraph(string $text, array $options = []): array
    {
        try {
            $this->logger->info('Processing knowledge graph building request', ['text_length' => strlen($text)]);
            
            $result = $this->knowledgeProcessor->buildGraphFromText($text, $options);
            
            // 记录使用指标
            $this->recordUsageMetrics('knowledge_graph_build', [
                'text_length' => strlen($text),
                'processing_time' => microtime(true)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Knowledge graph building failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 知识图谱查询服务
     */
    public function queryKnowledgeGraph(string $query, array $options = []): array
    {
        try {
            $this->logger->info('Processing knowledge graph query', ['query' => $query]);
            
            private $result = $this->knowledgeProcessor->queryGraph($query, $options);
            
            // 记录使用指标
            $this->recordUsageMetrics('knowledge_graph_query', [
                'query' => $query,
                'processing_time' => microtime(true)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Knowledge graph query failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * AI问答服务
     */
    public function answerQuestion(string $question, string $context = '', array $options = []): array
    {
        try {
            $this->logger->info('Processing AI Q&A request', ['question' => $question]);
            
            // 首先尝试使用知识图谱问答
            private $kgResult = $this->knowledgeProcessor->answerQuestion($question, $options);
            
            // 如果需要，结合NLP问答
            private $nlpResult = $this->nlpProcessor->answerQuestion($question, $context, $options);
            
            // 融合结果
            private $result = [
                'question' => $question,
                'knowledge_graph_answer' => $kgResult,
                'nlp_answer' => $nlpResult,
                'combined_confidence' => ($kgResult['confidence'] + $nlpResult['confidence']) / 2,
                'answer_sources' => ['knowledge_graph', 'nlp_model'],
                'processing_time' => date('Y-m-d H:i:s')
            ];
            
            // 记录使用指标
            $this->recordUsageMetrics('ai_qa', [
                'question' => $question,
                'processing_time' => microtime(true)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('AI Q&A failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 多模态AI分析服务
     */
    public function multiModalAnalysis(array $inputs, array $options = []): array
    {
        try {
            $this->logger->info('Processing multi-modal analysis request');
            
            private $results = [];
            
            // 处理文本输入
            if (isset($inputs['text'])) {
                $results['text_analysis'] = $this->analyzeText($inputs['text'], $options);
            }
            
            // 处理图像输入
            if (isset($inputs['image'])) {
                $results['image_analysis'] = $this->analyzeImage($inputs['image'], $options);
            }
            
            // 处理音频输入
            if (isset($inputs['audio'])) {
                $results['speech_analysis'] = $this->speechProcessor->analyzeVoice($inputs['audio'], $options);
                
                // 如果需要转录
                if ($options['transcribe'] ?? false) {
                    $results['transcription'] = $this->speechToText($inputs['audio'], $options);
                }
            }
            
            // 跨模态融合分析
            $results['cross_modal_insights'] = $this->performCrossModalAnalysis($results);
            
            // 记录使用指标
            $this->recordUsageMetrics('multi_modal_analysis', [
                'input_types' => array_keys($inputs),
                'processing_time' => microtime(true)
            ]);
            
            return [
                'results' => $results,
                'analysis_time' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Multi-modal analysis failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 批量AI处理服务
     */
    public function batchProcess(array $tasks, array $options = []): array
    {
        try {
            $this->logger->info('Processing batch AI tasks', ['task_count' => count($tasks)]);
            
            private $results = [];
            private $concurrency = $options['concurrency'] ?? 3;
            
            // 分批处理任务
            private $batches = array_chunk($tasks, $concurrency);
            
            foreach ($batches as $batchIndex => $batch) {
                $this->logger->info("Processing batch {$batchIndex}", ['tasks_in_batch' => count($batch)]);
                
                private $batchResults = [];
                
                foreach ($batch as $taskIndex => $task) {
                    try {
                        private $taskResult = $this->processTask($task);
                        $batchResults[$taskIndex] = $taskResult;
                    } catch (\Exception $e) {
                        $batchResults[$taskIndex] = [
                            'error' => $e->getMessage(),
                            'task' => $task
                        ];
                    }
                }
                
                private $results = array_merge($results, $batchResults);
            }
            
            // 记录使用指标
            $this->recordUsageMetrics('batch_processing', [
                'task_count' => count($tasks),
                'processing_time' => microtime(true)
            ]);
            
            return [
                'successful_tasks' => count(array_filter($results, fn($r) => !isset($r['error']))),
                'failed_tasks' => count(array_filter($results, fn($r) => isset($r['error']))),
                'results' => $results,
                'processing_time' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Batch processing failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 处理单个任务
     */
    private function processTask(array $task): array
    {
        private $taskType = $task['type'] ?? 'unknown';
        private $data = $task['data'] ?? [];
        private $options = $task['options'] ?? [];
        
        switch ($taskType) {
            case 'text_analysis':
                return $this->analyzeText($data['text'], $options);
            case 'image_analysis':
                return $this->analyzeImage($data['image_path'], $options);
            case 'speech_to_text':
                return $this->speechToText($data['audio_path'], $options);
            case 'text_to_speech':
                return $this->textToSpeech($data['text'], $options);
            case 'knowledge_graph_build':
                return $this->buildKnowledgeGraph($data['text'], $options);
            case 'knowledge_graph_query':
                return $this->queryKnowledgeGraph($data['query'], $options);
            default:
                throw new \InvalidArgumentException("Unsupported task type: {$taskType}");
        }
    }

    /**
     * 跨模态融合分析
     */
    private function performCrossModalAnalysis(array $modalResults): array
    {
        private $insights = [];
        
        // 文本和图像的关联分析
        if (isset($modalResults['text_analysis']) && isset($modalResults['image_analysis'])) {
            private $textSentiment = $modalResults['text_analysis']['sentiment']['sentiment'] ?? 'neutral';
            private $imageClassification = $modalResults['image_analysis']['classification']['primary_category'] ?? 'unknown';
            
            $insights['text_image_correlation'] = [
                'text_sentiment' => $textSentiment,
                'image_category' => $imageClassification,
                'consistency_score' => $this->calculateConsistencyScore($textSentiment, $imageClassification)
            ];
        }
        
        // 语音和文本的情感一致性分析
        if (isset($modalResults['speech_analysis']) && isset($modalResults['text_analysis'])) {
            private $speechEmotion = $modalResults['speech_analysis']['emotion_analysis']['primary_emotion'] ?? 'neutral';
            private $textSentiment = $modalResults['text_analysis']['sentiment']['sentiment'] ?? 'neutral';
            
            $insights['speech_text_emotion_consistency'] = [
                'speech_emotion' => $speechEmotion,
                'text_sentiment' => $textSentiment,
                'emotional_alignment' => $this->calculateEmotionalAlignment($speechEmotion, $textSentiment)
            ];
        }
        
        return $insights;
    }

    /**
     * 计算一致性评分
     */
    private function calculateConsistencyScore(string $textSentiment, string $imageCategory): float
    {
        // 简化的一致性评分算法
        private $consistencyMap = [
            'positive' => ['landscape', 'nature', 'portrait'],
            'negative' => ['architecture', 'technology'],
            'neutral' => ['architecture', 'landscape', 'technology']
        ];
        
        private $expectedCategories = $consistencyMap[$textSentiment] ?? [];
        return in_array($imageCategory, $expectedCategories) ? 0.8 : 0.3;
    }

    /**
     * 计算情感对齐度
     */
    private function calculateEmotionalAlignment(string $speechEmotion, string $textSentiment): float
    {
        private $alignmentMap = [
            'happy' => ['positive'],
            'sad' => ['negative'],
            'angry' => ['negative'],
            'neutral' => ['neutral'],
            'excited' => ['positive'],
            'calm' => ['neutral', 'positive']
        ];
        
        private $expectedSentiments = $alignmentMap[$speechEmotion] ?? [];
        return in_array($textSentiment, $expectedSentiments) ? 0.9 : 0.4;
    }

    /**
     * 更新服务状态
     */
    private function updateServiceStatus(): void
    {
        $this->serviceStatus = [
            'nlp' => $this->nlpProcessor->getStatus(),
            'computer_vision' => $this->visionProcessor->getStatus(),
            'speech' => $this->speechProcessor->getStatus(),
            'knowledge_graph' => $this->knowledgeProcessor->getStatus(),
            'last_update' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * 记录使用指标
     */
    private function recordUsageMetrics(string $operation, array $metrics): void
    {
        if (!isset($this->modelMetrics[$operation])) {
            $this->modelMetrics[$operation] = [
                'total_requests' => 0,
                'total_processing_time' => 0,
                'average_processing_time' => 0,
                'last_request' => null
            ];
        }
        
        $this->modelMetrics[$operation]['total_requests']++;
        $this->modelMetrics[$operation]['last_request'] = date('Y-m-d H:i:s');
        
        if (isset($metrics['processing_time'])) {
            private $processingTime = $metrics['processing_time'];
            $this->modelMetrics[$operation]['total_processing_time'] += $processingTime;
            $this->modelMetrics[$operation]['average_processing_time'] = 
                $this->modelMetrics[$operation]['total_processing_time'] / 
                $this->modelMetrics[$operation]['total_requests'];
        }
    }

    /**
     * 获取AI服务状态
     */
    public function getServiceStatus(): array
    {
        $this->updateServiceStatus();
        
        return [
            'status' => 'active',
            'version' => '6.0.0',
            'services' => $this->serviceStatus,
            'usage_metrics' => $this->modelMetrics,
            'capabilities' => [
                'text_analysis',
                'image_analysis', 
                'speech_processing',
                'knowledge_graph',
                'question_answering',
                'multi_modal_analysis',
                'batch_processing'
            ],
            'last_check' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * 初始化服务（继承自AbstractServiceManager）
     */
    protected function doInitialize(): void
    {
        $this->initializeAIServices();
    }

    /**
     * 注册服务（继承自AbstractServiceManager）
     */
    public function registerServices(Container $container): void
    {
        // 在容器中注册AI服务
        $container->set('nlp_processor', $this->nlpProcessor);
        $container->set('vision_processor', $this->visionProcessor);
        $container->set('speech_processor', $this->speechProcessor);
        $container->set('knowledge_processor', $this->knowledgeProcessor);
    }

    /**
     * 健康检查（覆盖父类方法）
     */
    public function healthCheck(): bool
    {
        try {
            private $health = $this->getHealthStatus();
            return $health['overall_status'] === 'healthy';
        } catch (\Exception $e) {
            $this->logger->error('Health check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取详细健康状态
     */
    public function getHealthStatus(): array
    {
        private $health = [
            'overall_status' => 'healthy',
            'services' => [],
            'issues' => []
        ];
        
        // 检查各个服务状态
        try {
            private $nlpStatus = $this->nlpProcessor->getStatus();
            $health['services']['nlp'] = $nlpStatus['status'];
        } catch (\Exception $e) {
            $health['services']['nlp'] = 'error';
            $health['issues'][] = 'NLP service error: ' . $e->getMessage();
        }
        
        try {
            private $visionStatus = $this->visionProcessor->getStatus();
            $health['services']['computer_vision'] = $visionStatus['status'];
        } catch (\Exception $e) {
            $health['services']['computer_vision'] = 'error';
            $health['issues'][] = 'Computer Vision service error: ' . $e->getMessage();
        }
        
        try {
            private $speechStatus = $this->speechProcessor->getStatus();
            $health['services']['speech'] = $speechStatus['status'];
        } catch (\Exception $e) {
            $health['services']['speech'] = 'error';
            $health['issues'][] = 'Speech service error: ' . $e->getMessage();
        }
        
        try {
            private $kgStatus = $this->knowledgeProcessor->getStatus();
            $health['services']['knowledge_graph'] = $kgStatus['status'];
        } catch (\Exception $e) {
            $health['services']['knowledge_graph'] = 'error';
            $health['issues'][] = 'Knowledge Graph service error: ' . $e->getMessage();
        }
        
        // 如果有任何服务出错，标记整体状态为不健康
        if (!empty($health['issues'])) {
            $health['overall_status'] = 'unhealthy';
        }
        
        return $health;
    }
}
