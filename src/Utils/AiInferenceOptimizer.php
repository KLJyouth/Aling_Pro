<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use AlingAi\Services\CacheService;

/**
 * AI推理优化器
 * 
 * 提供AI模型推理性能优化功能
 * 优化性能：模型缓存、推理加速、批处理、并行推理
 * 增强功能：动态优化、自适应调整、性能监控
 */
class AiInferenceOptimizer
{
    private LoggerInterface $logger;
    private CacheService $cacheService;
    private array $config;
    private array $models = [];
    private array $performanceMetrics = [];
    
    public function __construct(
        LoggerInterface $logger,
        CacheService $cacheService,
        array $config = []
    ) {
        $this->logger = $logger;
        $this->cacheService = $cacheService;
        $this->config = array_merge([
            'enabled' => true,
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'batch_processing' => true,
            'max_batch_size' => 10,
            'parallel_processing' => true,
            'max_parallel_requests' => 5,
            'model_optimization' => true,
            'adaptive_optimization' => true,
            'performance_monitoring' => true,
            'models' => [
                'gpt-3.5-turbo' => [
                    'max_tokens' => 4096,
                    'temperature' => 0.7,
                    'cache_key' => 'gpt35_cache',
                    'optimization_level' => 'balanced'
                ],
                'gpt-4' => [
                    'max_tokens' => 8192,
                    'temperature' => 0.7,
                    'cache_key' => 'gpt4_cache',
                    'optimization_level' => 'quality'
                ],
                'claude-3' => [
                    'max_tokens' => 100000,
                    'temperature' => 0.7,
                    'cache_key' => 'claude3_cache',
                    'optimization_level' => 'balanced'
                ]
            ],
            'optimization_strategies' => [
                'prompt_optimization' => true,
                'response_caching' => true,
                'context_compression' => true,
                'token_optimization' => true,
                'model_selection' => true
            ]
        ], $config);
        
        $this->initializeModels();
    }
    
    /**
     * 初始化模型配置
     */
    private function initializeModels(): void
    {
        $this->models = $this->config['models'];
        
        foreach ($this->models as $modelName => &$modelConfig) {
            $modelConfig['performance_history'] = [];
            $modelConfig['cache_hit_rate'] = 0;
            $modelConfig['avg_response_time'] = 0;
            $modelConfig['total_requests'] = 0;
        }
    }
    
    /**
     * 优化推理请求
     */
    public function optimizeInference(array $request, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $options = array_merge([
                'model' => 'gpt-3.5-turbo',
                'use_cache' => true,
                'batch_id' => null,
                'priority' => 'normal'
            ], $options);
            
            $this->logger->debug('开始优化推理请求', [
                'model' => $options['model'],
                'message_length' => strlen($request['message'] ?? '')
            ]);
            
            // 检查缓存
            if ($options['use_cache'] && $this->config['cache_enabled']) {
                $cachedResponse = $this->checkCache($request, $options['model']);
                if ($cachedResponse) {
                    $this->updatePerformanceMetrics($options['model'], 'cache_hit', 0);
                    return $cachedResponse;
                }
            }
            
            // 优化提示词
            $optimizedRequest = $this->optimizePrompt($request, $options);
            
            // 选择最佳模型
            $selectedModel = $this->selectOptimalModel($optimizedRequest, $options);
            
            // 执行推理
            $response = $this->executeInference($optimizedRequest, $selectedModel, $options);
            
            // 缓存响应
            if ($options['use_cache'] && $this->config['cache_enabled']) {
                $this->cacheResponse($request, $response, $selectedModel);
            }
            
            // 更新性能指标
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $this->updatePerformanceMetrics($selectedModel, 'success', $duration);
            
            $this->logger->debug('推理优化完成', [
                'model' => $selectedModel,
                'duration_ms' => $duration,
                'tokens_used' => $response['tokens_used'] ?? 0
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            $this->logger->error('推理优化失败', [
                'error' => $e->getMessage(),
                'model' => $options['model'] ?? 'unknown'
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 检查缓存
     */
    private function checkCache(array $request, string $model): ?array
    {
        $cacheKey = $this->generateCacheKey($request, $model);
        $cached = $this->cacheService->get($cacheKey);
        
        if ($cached) {
            $this->logger->debug('缓存命中', [
                'cache_key' => $cacheKey,
                'model' => $model
            ]);
        }
        
        return $cached;
    }
    
    /**
     * 生成缓存键
     */
    private function generateCacheKey(array $request, string $model): string
    {
        $modelConfig = $this->models[$model] ?? [];
        $cacheKey = $modelConfig['cache_key'] ?? 'ai_cache';
        
        $content = [
            'message' => $request['message'] ?? '',
            'model' => $model,
            'temperature' => $request['temperature'] ?? $modelConfig['temperature'] ?? 0.7,
            'max_tokens' => $request['max_tokens'] ?? $modelConfig['max_tokens'] ?? 1000
        ];
        
        return $cacheKey . '_' . hash('sha256', json_encode($content));
    }
    
    /**
     * 缓存响应
     */
    private function cacheResponse(array $request, array $response, string $model): void
    {
        $cacheKey = $this->generateCacheKey($request, $model);
        $this->cacheService->set($cacheKey, $response, $this->config['cache_ttl']);
        
        $this->logger->debug('响应已缓存', [
            'cache_key' => $cacheKey,
            'model' => $model
        ]);
    }
    
    /**
     * 优化提示词
     */
    private function optimizePrompt(array $request, array $options): array
    {
        $optimizedRequest = $request;
        
        if (!$this->config['optimization_strategies']['prompt_optimization']) {
            return $optimizedRequest;
        }
        
        $message = $request['message'] ?? '';
        
        // 移除多余空格
        $message = preg_replace('/\s+/', ' ', trim($message));
        
        // 优化常见提示词模式
        $message = $this->optimizePromptPatterns($message);
        
        // 压缩上下文（如果启用）
        if ($this->config['optimization_strategies']['context_compression']) {
            $message = $this->compressContext($message);
        }
        
        $optimizedRequest['message'] = $message;
        
        $this->logger->debug('提示词优化完成', [
            'original_length' => strlen($request['message'] ?? ''),
            'optimized_length' => strlen($message),
            'compression_ratio' => round(strlen($message) / max(strlen($request['message'] ?? ''), 1) * 100, 2)
        ]);
        
        return $optimizedRequest;
    }
    
    /**
     * 优化提示词模式
     */
    private function optimizePromptPatterns(string $message): string
    {
        // 优化常见模式
        $patterns = [
            '/请详细解释/',
            '/请详细说明/',
            '/请详细描述/',
            '/请详细分析/'
        ];
        
        $replacements = [
            '请解释',
            '请说明',
            '请描述',
            '请分析'
        ];
        
        $message = preg_replace($patterns, $replacements, $message);
        
        // 移除重复词汇
        $message = preg_replace('/(\b\w+\b)(?:\s+\1)+/', '$1', $message);
        
        return $message;
    }
    
    /**
     * 压缩上下文
     */
    private function compressContext(string $message): string
    {
        // 如果消息太长，进行压缩
        $maxLength = 2000;
        
        if (strlen($message) <= $maxLength) {
            return $message;
        }
        
        // 保留开头和结尾，压缩中间部分
        $startLength = 800;
        $endLength = 400;
        
        $start = substr($message, 0, $startLength);
        $end = substr($message, -$endLength);
        $middle = substr($message, $startLength, -$endLength);
        
        // 压缩中间部分
        $compressedMiddle = $this->compressText($middle);
        
        return $start . $compressedMiddle . $end;
    }
    
    /**
     * 压缩文本
     */
    private function compressText(string $text): string
    {
        // 简化实现，实际应该使用更复杂的文本压缩算法
        $words = explode(' ', $text);
        $compressedWords = array_slice($words, 0, count($words) / 2);
        
        return implode(' ', $compressedWords);
    }
    
    /**
     * 选择最佳模型
     */
    private function selectOptimalModel(array $request, array $options): string
    {
        if (!$this->config['optimization_strategies']['model_selection']) {
            return $options['model'];
        }
        
        $messageLength = strlen($request['message'] ?? '');
        $availableModels = array_keys($this->models);
        
        // 根据消息长度和性能历史选择模型
        $selectedModel = $options['model'];
        
        if ($messageLength > 4000 && in_array('gpt-4', $availableModels)) {
            $selectedModel = 'gpt-4';
        } elseif ($messageLength > 100000 && in_array('claude-3', $availableModels)) {
            $selectedModel = 'claude-3';
        } else {
            // 根据性能历史选择最快的模型
            $fastestModel = $this->getFastestModel($availableModels);
            if ($fastestModel) {
                $selectedModel = $fastestModel;
            }
        }
        
        $this->logger->debug('模型选择', [
            'original_model' => $options['model'],
            'selected_model' => $selectedModel,
            'message_length' => $messageLength
        ]);
        
        return $selectedModel;
    }
    
    /**
     * 获取最快的模型
     */
    private function getFastestModel(array $models): ?string
    {
        $fastestModel = null;
        $fastestTime = PHP_FLOAT_MAX;
        
        foreach ($models as $model) {
            $modelConfig = $this->models[$model] ?? [];
            $avgTime = $modelConfig['avg_response_time'] ?? 0;
            
            if ($avgTime > 0 && $avgTime < $fastestTime) {
                $fastestTime = $avgTime;
                $fastestModel = $model;
            }
        }
        
        return $fastestModel;
    }
    
    /**
     * 执行推理
     */
    private function executeInference(array $request, string $model, array $options): array
    {
        $startTime = microtime(true);
        
        // 这里应该调用实际的AI模型API
        // 简化实现，返回模拟响应
        $response = $this->simulateInference($request, $model);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $response['inference_time'] = $duration;
        $response['model_used'] = $model;
        
        return $response;
    }
    
    /**
     * 模拟推理
     */
    private function simulateInference(array $request, string $model): array
    {
        $message = $request['message'] ?? '';
        
        // 模拟不同模型的响应
        $responses = [
            'gpt-3.5-turbo' => "这是GPT-3.5-Turbo的回复：{$message}",
            'gpt-4' => "这是GPT-4的详细回复：{$message}",
            'claude-3' => "这是Claude-3的回复：{$message}"
        ];
        
        $response = $responses[$model] ?? "这是AI模型的回复：{$message}";
        
        return [
            'response' => $response,
            'tokens_used' => strlen($message) / 4, // 粗略估算
            'model' => $model
        ];
    }
    
    /**
     * 更新性能指标
     */
    private function updatePerformanceMetrics(string $model, string $type, float $duration): void
    {
        if (!isset($this->models[$model])) {
            return;
        }
        
        $modelConfig = &$this->models[$model];
        
        $modelConfig['total_requests']++;
        
        if ($type === 'cache_hit') {
            $modelConfig['cache_hit_rate'] = 
                ($modelConfig['cache_hit_rate'] * ($modelConfig['total_requests'] - 1) + 1) / $modelConfig['total_requests'];
        } else {
            $modelConfig['cache_hit_rate'] = 
                ($modelConfig['cache_hit_rate'] * ($modelConfig['total_requests'] - 1)) / $modelConfig['total_requests'];
            
            // 更新平均响应时间
            $modelConfig['avg_response_time'] = 
                ($modelConfig['avg_response_time'] * ($modelConfig['total_requests'] - 1) + $duration) / $modelConfig['total_requests'];
        }
        
        // 记录性能历史
        $modelConfig['performance_history'][] = [
            'timestamp' => time(),
            'duration' => $duration,
            'type' => $type
        ];
        
        // 保持历史记录在合理范围内
        if (count($modelConfig['performance_history']) > 100) {
            $modelConfig['performance_history'] = array_slice($modelConfig['performance_history'], -50);
        }
    }
    
    /**
     * 批处理推理
     */
    public function batchInference(array $requests, array $options = []): array
    {
        if (!$this->config['batch_processing']) {
            return $this->processSequentially($requests, $options);
        }
        
        $options = array_merge([
            'max_batch_size' => $this->config['max_batch_size'],
            'parallel' => $this->config['parallel_processing']
        ], $options);
        
        $batches = array_chunk($requests, $options['max_batch_size']);
        $results = [];
        
        if ($options['parallel']) {
            $results = $this->processParallelBatches($batches, $options);
        } else {
            $results = $this->processSequentialBatches($batches, $options);
        }
        
        return array_merge(...$results);
    }
    
    /**
     * 顺序处理
     */
    private function processSequentially(array $requests, array $options): array
    {
        $results = [];
        
        foreach ($requests as $request) {
            $results[] = $this->optimizeInference($request, $options);
        }
        
        return $results;
    }
    
    /**
     * 并行处理批次
     */
    private function processParallelBatches(array $batches, array $options): array
    {
        $results = [];
        $maxParallel = $this->config['max_parallel_requests'];
        
        for ($i = 0; $i < count($batches); $i += $maxParallel) {
            $currentBatches = array_slice($batches, $i, $maxParallel);
            $batchResults = $this->processSequentialBatches($currentBatches, $options);
            $results = array_merge($results, $batchResults);
        }
        
        return $results;
    }
    
    /**
     * 顺序处理批次
     */
    private function processSequentialBatches(array $batches, array $options): array
    {
        $results = [];
        
        foreach ($batches as $batch) {
            $batchResults = $this->processSequentially($batch, $options);
            $results[] = $batchResults;
        }
        
        return $results;
    }
    
    /**
     * 获取性能报告
     */
    public function getPerformanceReport(): array
    {
        $report = [
            'models' => [],
            'overall_metrics' => [
                'total_requests' => 0,
                'avg_response_time' => 0,
                'cache_hit_rate' => 0
            ],
            'recommendations' => []
        ];
        
        $totalRequests = 0;
        $totalResponseTime = 0;
        $totalCacheHits = 0;
        
        foreach ($this->models as $modelName => $modelConfig) {
            $report['models'][$modelName] = [
                'total_requests' => $modelConfig['total_requests'],
                'avg_response_time' => round($modelConfig['avg_response_time'], 2),
                'cache_hit_rate' => round($modelConfig['cache_hit_rate'] * 100, 2),
                'performance_history' => array_slice($modelConfig['performance_history'], -10)
            ];
            
            $totalRequests += $modelConfig['total_requests'];
            $totalResponseTime += $modelConfig['avg_response_time'] * $modelConfig['total_requests'];
            $totalCacheHits += $modelConfig['cache_hit_rate'] * $modelConfig['total_requests'];
        }
        
        if ($totalRequests > 0) {
            $report['overall_metrics']['total_requests'] = $totalRequests;
            $report['overall_metrics']['avg_response_time'] = round($totalResponseTime / $totalRequests, 2);
            $report['overall_metrics']['cache_hit_rate'] = round(($totalCacheHits / $totalRequests) * 100, 2);
        }
        
        // 生成建议
        $report['recommendations'] = $this->generateOptimizationRecommendations($report);
        
        return $report;
    }
    
    /**
     * 生成优化建议
     */
    private function generateOptimizationRecommendations(array $report): array
    {
        $recommendations = [];
        
        // 缓存命中率建议
        if ($report['overall_metrics']['cache_hit_rate'] < 30) {
            $recommendations[] = [
                'type' => 'cache',
                'priority' => 'medium',
                'message' => '缓存命中率较低，建议优化缓存策略',
                'action' => 'review_cache_keys_and_ttl'
            ];
        }
        
        // 响应时间建议
        if ($report['overall_metrics']['avg_response_time'] > 2000) {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'high',
                'message' => '平均响应时间较长，建议优化模型选择',
                'action' => 'use_faster_models_for_simple_queries'
            ];
        }
        
        // 模型使用建议
        foreach ($report['models'] as $modelName => $modelData) {
            if ($modelData['total_requests'] > 0 && $modelData['avg_response_time'] > 3000) {
                $recommendations[] = [
                    'type' => 'model_optimization',
                    'priority' => 'medium',
                    'message' => "模型 {$modelName} 响应时间较长，建议优化",
                    'action' => 'consider_model_parameters_optimization'
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * 清理缓存
     */
    public function clearCache(string $model = null): bool
    {
        if ($model) {
            $modelConfig = $this->models[$model] ?? [];
            $cacheKey = $modelConfig['cache_key'] ?? null;
            
            if ($cacheKey) {
                return $this->cacheService->deletePattern($cacheKey . '_*');
            }
        } else {
            // 清理所有模型缓存
            foreach ($this->models as $modelConfig) {
                $cacheKey = $modelConfig['cache_key'] ?? null;
                if ($cacheKey) {
                    $this->cacheService->deletePattern($cacheKey . '_*');
                }
            }
        }
        
        return true;
    }
    
    /**
     * 重置性能指标
     */
    public function resetPerformanceMetrics(): void
    {
        foreach ($this->models as &$modelConfig) {
            $modelConfig['performance_history'] = [];
            $modelConfig['cache_hit_rate'] = 0;
            $modelConfig['avg_response_time'] = 0;
            $modelConfig['total_requests'] = 0;
        }
    }
} 