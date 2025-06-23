<?php

namespace AlingAi\AI;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use AlingAi\Security\IntelligentSecuritySystem;
use AlingAi\Core\Container;

/**
 * 智能代理系统
 * 
 * 提供高级智能代理管理和协调功能，包括多模态代理、自适应学习和任务分发
 * 优化性能：使用缓存和异步处理提高响应速度
 * 增强安全性：集成安全检查和输入验证
 */
class IntelligentAgentSystem
{
    private $logger;
    private $httpClient;
    private $securitySystem;
    private $container;
    private $agents = [];
    private $config = [];
    private $modelRegistry = [];
    private $performanceStats = [];
    private $lastMaintenanceTime = 0;
    private $maintenanceInterval = 3600; // 1小时
    private $cacheEnabled = true;
    private $cache = [];
    private $cacheLifetime = 300; // 5分钟

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param IntelligentSecuritySystem $securitySystem 安全系统
     * @param Container $container 容器
     * @param Client|null $httpClient HTTP客户端
     */
    public function __construct(
        LoggerInterface $logger, 
        IntelligentSecuritySystem $securitySystem,
        Container $container,
        Client $httpClient = null
    ) {
        $this->logger = $logger;
        $this->securitySystem = $securitySystem;
        $this->container = $container;
        $this->httpClient = $httpClient ?? new Client([
            'timeout' => 30,
            'verify' => true
        ]);
        
        $this->config = $this->loadConfiguration();
        $this->initializeAgents();
        $this->registerModels();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        $config = [
            'default_model' => env('AI_DEFAULT_MODEL', 'gpt-4'),
            'providers' => [
                'openai' => [
                    'api_key' => env('OPENAI_API_KEY', ''),
                    'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
                    'models' => ['gpt-4', 'gpt-3.5-turbo', 'dall-e-3']
                ],
                'deepseek' => [
                    'api_key' => env('DEEPSEEK_API_KEY', ''),
                    'api_url' => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1'),
                    'models' => ['deepseek-chat', 'deepseek-coder']
                ],
                'anthropic' => [
                    'api_key' => env('ANTHROPIC_API_KEY', ''),
                    'api_url' => env('ANTHROPIC_API_URL', 'https://api.anthropic.com'),
                    'models' => ['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku']
                ]
            ],
            'cache_enabled' => env('AI_CACHE_ENABLED', true),
            'cache_lifetime' => env('AI_CACHE_LIFETIME', 300),
            'maintenance_interval' => env('AI_MAINTENANCE_INTERVAL', 3600),
            'security' => [
                'content_filtering' => true,
                'max_tokens_per_request' => 4000,
                'rate_limit_per_minute' => 60
            ]
        ];
        
        // 允许通过config/ai.php文件覆盖设置
        $configPath = base_path('config/ai.php');
        if (file_exists($configPath)) {
            $fileConfig = require $configPath;
            $config = array_merge($config, $fileConfig);
        }
        
        return $config;
    }
    
    /**
     * 初始化代理
     */
    private function initializeAgents(): void
    {
        $this->agents = [
            'content' => [
                'name' => '内容生成代理',
                'capabilities' => ['text_generation', 'translation', 'summarization', 'content_optimization'],
                'models' => ['gpt-4', 'claude-3-opus', 'deepseek-chat'],
                'default_model' => 'gpt-4',
                'params' => [
                    'temperature' => 0.7,
                    'top_p' => 0.9
                ]
            ],
            'code' => [
                'name' => '代码生成代理',
                'capabilities' => ['code_generation', 'code_review', 'debugging', 'documentation'],
                'models' => ['deepseek-coder', 'gpt-4', 'claude-3-opus'],
                'default_model' => 'deepseek-coder',
                'params' => [
                    'temperature' => 0.2,
                    'top_p' => 0.95
                ]
            ],
            'vision' => [
                'name' => '视觉分析代理',
                'capabilities' => ['image_analysis', 'object_detection', 'scene_recognition', 'ocr'],
                'models' => ['gpt-4-vision', 'gemini-pro-vision'],
                'default_model' => 'gpt-4-vision',
                'params' => [
                    'temperature' => 0.5,
                    'detail' => 'high'
                ]
            ],
            'conversation' => [
                'name' => '对话代理',
                'capabilities' => ['chat', 'question_answering', 'role_playing', 'reasoning'],
                'models' => ['claude-3-opus', 'gpt-4', 'deepseek-chat'],
                'default_model' => 'claude-3-opus',
                'params' => [
                    'temperature' => 0.8,
                    'top_p' => 0.9
                ]
            ],
            'creation' => [
                'name' => '创意生成代理',
                'capabilities' => ['image_generation', 'story_writing', 'idea_generation'],
                'models' => ['dall-e-3', 'midjourney', 'stable-diffusion-3'],
                'default_model' => 'dall-e-3',
                'params' => [
                    'quality' => 'hd',
                    'style' => 'vivid'
                ]
            ]
        ];
    }
    
    /**
     * 注册模型
     */
    private function registerModels(): void
    {
        foreach ($this->config['providers'] as $provider => $providerConfig) {
            foreach ($providerConfig['models'] as $model) {
                $this->modelRegistry[$model] = [
                    'provider' => $provider,
                    'api_key' => $providerConfig['api_key'],
                    'api_url' => $providerConfig['api_url'],
                    'capabilities' => $this->getModelCapabilities($model),
                    'performance' => [
                        'average_response_time' => 0,
                        'success_rate' => 100,
                        'total_requests' => 0
                    ]
                ];
            }
        }
    }
    
    /**
     * 获取模型能力
     * 
     * @param string $model 模型名称
     * @return array 模型能力列表
     */
    private function getModelCapabilities(string $model): array
    {
        $capabilities = [];
        
        // 基于模型名称推断能力
        if (strpos($model, 'gpt-4') !== false) {
            $capabilities = ['text_generation', 'reasoning', 'code_generation', 'chat'];
            
            if (strpos($model, 'vision') !== false) {
                $capabilities[] = 'image_analysis';
            }
        } elseif (strpos($model, 'gpt-3.5') !== false) {
            $capabilities = ['text_generation', 'chat', 'summarization'];
        } elseif (strpos($model, 'claude') !== false) {
            $capabilities = ['text_generation', 'reasoning', 'chat', 'summarization'];
        } elseif (strpos($model, 'deepseek-coder') !== false) {
            $capabilities = ['code_generation', 'code_review', 'debugging'];
        } elseif (strpos($model, 'deepseek-chat') !== false) {
            $capabilities = ['text_generation', 'chat', 'reasoning'];
        } elseif (strpos($model, 'dall-e') !== false || strpos($model, 'midjourney') !== false || strpos($model, 'stable-diffusion') !== false) {
            $capabilities = ['image_generation'];
        }
        
        return $capabilities;
    }
    
    /**
     * 选择最佳代理处理任务
     * 
     * @param string $task 任务描述
     * @param array $context 上下文信息
     * @return string 代理ID
     */
    public function selectAgent(string $task, array $context = []): string
    {
        $this->logger->info('选择代理', ['task' => $task]);
        
        // 任务分析，确定所需能力
        $requiredCapabilities = $this->analyzeTaskCapabilities($task, $context);
        
        $bestAgent = null;
        $highestScore = -1;
        
        foreach ($this->agents as $agentId => $agent) {
            $score = $this->calculateAgentScore($agent, $requiredCapabilities);
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestAgent = $agentId;
            }
        }
        
        $this->logger->info('已选择代理', ['agent' => $bestAgent, 'score' => $highestScore]);
        return $bestAgent;
    }
    
    /**
     * 分析任务所需能力
     * 
     * @param string $task 任务描述
     * @param array $context 上下文信息
     * @return array 所需能力列表
     */
    private function analyzeTaskCapabilities(string $task, array $context = []): array
    {
        $task = strtolower($task);
        $capabilities = [];
        
        // 基于关键词分析任务类型
        if (preg_match('/(编写|生成|创建|写).*代码|code|program/i', $task)) {
            $capabilities[] = 'code_generation';
        }
        
        if (preg_match('/(debug|修复|解决|错误|bug)/i', $task)) {
            $capabilities[] = 'debugging';
        }
        
        if (preg_match('/(图像|图片|照片|识别|分析|查看)/i', $task)) {
            $capabilities[] = 'image_analysis';
        }
        
        if (preg_match('/(生成|创建|画|设计).*(图像|图片|照片|艺术)/i', $task)) {
            $capabilities[] = 'image_generation';
        }
        
        if (preg_match('/(翻译|translate)/i', $task)) {
            $capabilities[] = 'translation';
        }
        
        if (preg_match('/(总结|摘要|概括|summarize)/i', $task)) {
            $capabilities[] = 'summarization';
        }
        
        if (preg_match('/(聊天|对话|交谈|chat)/i', $task)) {
            $capabilities[] = 'chat';
        }
        
        // 如果没有明确匹配，默认为通用文本生成和推理
        if (empty($capabilities)) {
            $capabilities = ['text_generation', 'reasoning'];
        }
        
        return $capabilities;
    }
    
    /**
     * 计算代理评分
     * 
     * @param array $agent 代理信息
     * @param array $requiredCapabilities 所需能力
     * @return float 评分
     */
    private function calculateAgentScore(array $agent, array $requiredCapabilities): float
    {
        $score = 0;
        $capabilityMatch = 0;
        
        // 计算能力匹配度
        foreach ($requiredCapabilities as $capability) {
            if (in_array($capability, $agent['capabilities'])) {
                $capabilityMatch++;
            }
        }
        
        if (count($requiredCapabilities) > 0) {
            $score = $capabilityMatch / count($requiredCapabilities) * 10;
        }
        
        return $score;
    }
    
    /**
     * 执行代理任务
     * 
     * @param string $agentId 代理ID
     * @param string $task 任务
     * @param array $params 参数
     * @return array 执行结果
     */
    public function executeTask(string $agentId, string $task, array $params = []): array
    {
        $this->logger->info('执行代理任务', ['agent' => $agentId, 'task' => $task]);
        
        // 安全检查
        $securityCheck = $this->securitySystem->validateContent($task);
        if (!$securityCheck['valid']) {
            $this->logger->warning('任务未通过安全检查', ['reason' => $securityCheck['reason']]);
        return [
                'success' => false,
                'error' => '内容未通过安全检查: ' . $securityCheck['reason']
            ];
        }
        
        // 检查缓存
        $cacheKey = md5($agentId . $task . json_encode($params));
        if ($this->cacheEnabled && isset($this->cache[$cacheKey])) {
            $cacheEntry = $this->cache[$cacheKey];
            if (time() - $cacheEntry['time'] < $this->cacheLifetime) {
                $this->logger->info('使用缓存结果', ['cache_key' => $cacheKey]);
                return $cacheEntry['result'];
            }
        }
        
        // 获取代理信息
        if (!isset($this->agents[$agentId])) {
            $this->logger->error('未找到代理', ['agent_id' => $agentId]);
            return [
                'success' => false,
                'error' => '未找到指定的代理'
            ];
        }
        
        $agent = $this->agents[$agentId];
        
        // 确定使用的模型
        $model = $params['model'] ?? $agent['default_model'];
        if (!in_array($model, $agent['models'])) {
            $model = $agent['default_model'];
        }
        
        // 合并参数
        $modelParams = array_merge($agent['params'], $params);
        
        try {
            // 执行任务
            $startTime = microtime(true);
            $result = $this->callModel($model, $task, $modelParams);
            $endTime = microtime(true);
            
            // 更新性能统计
            $this->updatePerformanceStats($model, $endTime - $startTime, true);
            
            // 缓存结果
            if ($this->cacheEnabled) {
                $this->cache[$cacheKey] = [
                    'time' => time(),
                    'result' => $result
                ];
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('执行任务失败', [
                'agent' => $agentId,
                'model' => $model,
                'error' => $e->getMessage()
            ]);
            
            // 更新性能统计
            $this->updatePerformanceStats($model, 0, false);
            
            return [
                'success' => false,
                'error' => '执行任务失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 调用AI模型
     * 
     * @param string $model 模型名称
     * @param string $prompt 提示词
     * @param array $params 参数
     * @return array 模型响应
     */
    private function callModel(string $model, string $prompt, array $params = []): array
    {
        if (!isset($this->modelRegistry[$model])) {
            throw new \Exception("未知的模型: {$model}");
        }
        
        $modelInfo = $this->modelRegistry[$model];
        $provider = $modelInfo['provider'];
        $apiKey = $modelInfo['api_key'];
        $apiUrl = $modelInfo['api_url'];
        
        if (empty($apiKey)) {
            throw new \Exception("模型 {$model} 的API密钥未配置");
        }
        
        switch ($provider) {
            case 'openai':
                return $this->callOpenAI($apiUrl, $apiKey, $model, $prompt, $params);
            case 'deepseek':
                return $this->callDeepSeek($apiUrl, $apiKey, $model, $prompt, $params);
            case 'anthropic':
                return $this->callAnthropic($apiUrl, $apiKey, $model, $prompt, $params);
            default:
                throw new \Exception("不支持的提供商: {$provider}");
        }
    }
    
    /**
     * 调用OpenAI API
     */
    private function callOpenAI(string $apiUrl, string $apiKey, string $model, string $prompt, array $params): array
    {
        $endpoint = "{$apiUrl}/chat/completions";
        
        $messages = [];
        if (isset($params['messages']) && is_array($params['messages'])) {
            $messages = $params['messages'];
        } else {
            $messages[] = [
                'role' => 'user',
                'content' => $prompt
            ];
        }
        
        $requestData = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $params['temperature'] ?? 0.7,
            'max_tokens' => $params['max_tokens'] ?? 2000,
            'top_p' => $params['top_p'] ?? 1.0,
            'frequency_penalty' => $params['frequency_penalty'] ?? 0,
            'presence_penalty' => $params['presence_penalty'] ?? 0
        ];
        
        $response = $this->httpClient->post($endpoint, [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json'
            ],
            'json' => $requestData
        ]);
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        return [
            'success' => true,
            'model' => $model,
            'content' => $responseData['choices'][0]['message']['content'] ?? '',
            'usage' => $responseData['usage'] ?? null,
            'raw_response' => $responseData
        ];
    }
    
    /**
     * 调用DeepSeek API
     */
    private function callDeepSeek(string $apiUrl, string $apiKey, string $model, string $prompt, array $params): array
    {
        $endpoint = "{$apiUrl}/chat/completions";
        
        $messages = [];
        if (isset($params['messages']) && is_array($params['messages'])) {
            $messages = $params['messages'];
        } else {
            $messages[] = [
                'role' => 'user',
                'content' => $prompt
            ];
        }
        
        $requestData = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $params['temperature'] ?? 0.7,
            'max_tokens' => $params['max_tokens'] ?? 2000,
            'top_p' => $params['top_p'] ?? 1.0
        ];
        
        $response = $this->httpClient->post($endpoint, [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json'
            ],
            'json' => $requestData
        ]);
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        return [
            'success' => true,
            'model' => $model,
            'content' => $responseData['choices'][0]['message']['content'] ?? '',
            'usage' => $responseData['usage'] ?? null,
            'raw_response' => $responseData
        ];
    }
    
    /**
     * 调用Anthropic API
     */
    private function callAnthropic(string $apiUrl, string $apiKey, string $model, string $prompt, array $params): array
    {
        $endpoint = "{$apiUrl}/v1/messages";
        
        $messages = [];
        if (isset($params['messages']) && is_array($params['messages'])) {
            // 转换消息格式
            foreach ($params['messages'] as $message) {
                if ($message['role'] === 'user') {
                    $messages[] = [
                        'role' => 'user',
                        'content' => $message['content']
                    ];
                } elseif ($message['role'] === 'assistant') {
                    $messages[] = [
                        'role' => 'assistant',
                        'content' => $message['content']
                    ];
                }
            }
        } else {
            $messages[] = [
                'role' => 'user',
                'content' => $prompt
            ];
        }
        
        $requestData = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $params['max_tokens'] ?? 2000,
            'temperature' => $params['temperature'] ?? 0.7,
            'top_p' => $params['top_p'] ?? 1.0
        ];
        
        $response = $this->httpClient->post($endpoint, [
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ],
            'json' => $requestData
        ]);
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        return [
            'success' => true,
            'model' => $model,
            'content' => $responseData['content'][0]['text'] ?? '',
            'usage' => [
                'input_tokens' => $responseData['usage']['input_tokens'] ?? 0,
                'output_tokens' => $responseData['usage']['output_tokens'] ?? 0
            ],
            'raw_response' => $responseData
        ];
    }
    
    /**
     * 更新性能统计
     * 
     * @param string $model 模型名称
     * @param float $responseTime 响应时间
     * @param bool $success 是否成功
     */
    private function updatePerformanceStats(string $model, float $responseTime, bool $success): void
    {
        if (!isset($this->modelRegistry[$model])) {
            return;
        }
        
        $performance = &$this->modelRegistry[$model]['performance'];
        $totalRequests = $performance['total_requests'];
        
        // 更新平均响应时间
        if ($success && $responseTime > 0) {
            if ($totalRequests > 0) {
                $performance['average_response_time'] = 
                    ($performance['average_response_time'] * $totalRequests + $responseTime) / ($totalRequests + 1);
            } else {
                $performance['average_response_time'] = $responseTime;
            }
        }
        
        // 更新成功率
        $performance['total_requests']++;
        if (!$success) {
            $successCount = ($performance['success_rate'] / 100) * $totalRequests;
            $performance['success_rate'] = ($successCount / $performance['total_requests']) * 100;
        }
        
        // 维护
        $this->performMaintenance();
    }
    
    /**
     * 执行维护
     */
    private function performMaintenance(): void
    {
        $currentTime = time();
        if ($currentTime - $this->lastMaintenanceTime < $this->maintenanceInterval) {
            return;
        }
        
        $this->logger->info('执行系统维护');
        
        // 清理过期缓存
        if ($this->cacheEnabled) {
            foreach ($this->cache as $key => $entry) {
                if ($currentTime - $entry['time'] > $this->cacheLifetime) {
                    unset($this->cache[$key]);
                }
            }
        }
        
        // 更新最后维护时间
        $this->lastMaintenanceTime = $currentTime;
    }
    
    /**
     * 获取系统状态
     * 
     * @return array 系统状态
     */
    public function getStatus(): array
    {
            return [
            'agents' => count($this->agents),
            'models' => count($this->modelRegistry),
            'cache_entries' => count($this->cache),
            'cache_enabled' => $this->cacheEnabled,
            'performance' => $this->getPerformanceSummary(),
            'last_maintenance' => date('Y-m-d H:i:s', $this->lastMaintenanceTime)
        ];
    }
    
    /**
     * 获取性能摘要
     * 
     * @return array 性能摘要
     */
    private function getPerformanceSummary(): array
    {
        $totalResponseTime = 0;
        $totalSuccessRate = 0;
        $totalRequests = 0;
        $modelCount = 0;
        
        foreach ($this->modelRegistry as $model => $info) {
            if ($info['performance']['total_requests'] > 0) {
                $totalResponseTime += $info['performance']['average_response_time'];
                $totalSuccessRate += $info['performance']['success_rate'];
                $totalRequests += $info['performance']['total_requests'];
                $modelCount++;
            }
        }

            return [
            'average_response_time' => $modelCount > 0 ? $totalResponseTime / $modelCount : 0,
            'average_success_rate' => $modelCount > 0 ? $totalSuccessRate / $modelCount : 100,
            'total_requests' => $totalRequests
        ];
    }
    
    /**
     * 获取可用代理列表
     * 
     * @return array 代理列表
     */
    public function getAvailableAgents(): array
    {
        $agents = [];
        foreach ($this->agents as $id => $agent) {
            $agents[$id] = [
                'name' => $agent['name'],
                'capabilities' => $agent['capabilities'],
                'models' => $agent['models']
            ];
        }
        return $agents;
    }
    
    /**
     * 获取可用模型列表
     * 
     * @return array 模型列表
     */
    public function getAvailableModels(): array
    {
        $models = [];
        foreach ($this->modelRegistry as $id => $model) {
            $models[$id] = [
                'provider' => $model['provider'],
                'capabilities' => $model['capabilities'],
                'performance' => [
                    'average_response_time' => $model['performance']['average_response_time'],
                    'success_rate' => $model['performance']['success_rate']
                ]
            ];
        }
        return $models;
    }
}
