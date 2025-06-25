<?php

namespace AlingAi\AI;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * 增强的智能体协调�?
 * 实现企业级AI任务分配和协调功�?
 */
/**
 * EnhancedAgentCoordinator �?
 *
 * @package AlingAi\AI
 */
class EnhancedAgentCoordinator
{
    private $logger;
    private $httpClient;
    private $deepseekApiUrl;
    private $apiKey;
    private $agents = [];
    private $taskQueue = [];
    private $performanceMetrics = [];
    private $coordinatorStatus = 'active';

    /**


     * __construct 方法


     *


     * @param LoggerInterface $logger


     * @param Client $httpClient


     * @return void


     */


    public function __construct(LoggerInterface $logger, Client $httpClient = null)
    {
        $this->logger = $logger;
        $this->httpClient = $httpClient ?? new Client([
            'timeout' => 30,
            'verify' => false // 生产环境中应该设置为true
        ]];
        $this->deepseekApiUrl = $_ENV['DEEPSEEK_API_URL'] ?? 'https://api.deepseek.com/v1/chat/completions';
        $this->apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? '';
        $this->initializeAgents(];
    }

    /**
     * 初始化智能体配置
     */
    /**

     * initializeAgents 方法

     *

     * @return void

     */

    private function initializeAgents(): void
    {
        $this->agents = [
            'content_generator' => [
                'name' => '内容生成智能�?,
                'capabilities' => ['content_creation', 'writing', 'translation'], 
                'load' => 0,
                'max_concurrent_tasks' => 5,
                'performance_score' => 100
            ], 
            'data_analyzer' => [
                'name' => '数据分析智能�?,
                'capabilities' => ['data_analysis', 'statistics', 'reporting'], 
                'load' => 0,
                'max_concurrent_tasks' => 3,
                'performance_score' => 100
            ], 
            'code_assistant' => [
                'name' => '代码助手智能�?,
                'capabilities' => ['coding', 'debugging', 'code_review'], 
                'load' => 0,
                'max_concurrent_tasks' => 4,
                'performance_score' => 100
            ]
        ];
    }

    /**
     * 获取系统状�?
     */
    /**

     * getStatus 方法

     *

     * @return void

     */

    public function getStatus(): array
    {
        return [
            'status' => $this->coordinatorStatus,
            'active_agents' => count($this->agents],
            'queue_length' => count($this->taskQueue],
            'total_processed' => $this->getTotalProcessedTasks(),
            'performance_summary' => $this->getPerformanceSummary()
        ];
    }

    /**
     * 获取已处理任务总数
     */
    /**

     * getTotalProcessedTasks 方法

     *

     * @return void

     */

    private function getTotalProcessedTasks(): int
    {
        return array_sum(array_column($this->performanceMetrics, 'completed_tasks')];
    }

    /**
     * 获取性能摘要
     */
    /**

     * getPerformanceSummary 方法

     *

     * @return void

     */

    private function getPerformanceSummary(): array
    {
        if (empty($this->performanceMetrics)) {
            return [
                'average_response_time' => 0,
                'success_rate' => 100,
                'total_tasks' => 0
            ];
        }

        $totalTasks = $this->getTotalProcessedTasks(];
        $totalResponseTime = array_sum(array_column($this->performanceMetrics, 'avg_response_time')];
        $avgResponseTime = $totalTasks > 0 ? $totalResponseTime / count($this->performanceMetrics) : 0;

        return [
            'average_response_time' => round($avgResponseTime, 2],
            'success_rate' => 95.5,
            'total_tasks' => $totalTasks
        ];
    }

    /**
     * 任务分配核心方法
     */
    /**

     * assignTask 方法

     *

     * @param string $taskDescription

     * @param array $context

     * @return void

     */

    public function assignTask(string $taskDescription, array $context = []): array
    {
        try {
            $this->logger->info('开始任务分�?, ['task' => $taskDescription]];
            
            // 使用DeepSeek API进行任务分析
            $taskAnalysis = $this->analyzeTaskWithDeepSeek($taskDescription, $context];
            
            // 选择最佳智能体
            $selectedAgent = $this->selectBestAgent($taskAnalysis];
            
            if (!$selectedAgent) {
                throw new \Exception('无法找到合适的智能体处理此任务'];
            }

            // 生成任务ID
            $taskId = $this->generateTaskId(];
            
            // 执行任务分配
            $assignmentResult = $this->executeTaskAssignment($taskId, $selectedAgent, $taskAnalysis];
            
            // 更新性能指标
            $this->updatePerformanceMetrics($selectedAgent, [
                'task_id' => $taskId,
                'completion_time' => microtime(true],
                'success' => true,
                'confidence' => $taskAnalysis['confidence'] ?? 0
            ]];
            
            return [
                'success' => true,
                'task_id' => $taskId,
                'assigned_agent' => $selectedAgent,
                'analysis' => $taskAnalysis,
                'execution_result' => $assignmentResult
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('任务分配失败', ['error' => $e->getMessage()]];
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * DeepSeek API任务分析
     */
    /**

     * analyzeTaskWithDeepSeek 方法

     *

     * @param string $taskDescription

     * @param array $context

     * @return void

     */

    private function analyzeTaskWithDeepSeek(string $taskDescription, array $context): array
    {
        $prompt = $this->buildTaskAnalysisPrompt($taskDescription, $context];
        
        try {
            $response = $this->httpClient->post($this->deepseekApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ], 
                'json' => [
                    'model' => 'deepseek-chat',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ], 
                    'max_tokens' => 500,
                    'temperature' => 0.3
                ], 
                'verify' => false
            ]];

            $data = json_decode($response->getBody()->getContents(), true];
            
            if (isset($data['choices'][0]['message']['content'])) {
                return $this->parseTaskAnalysis($data['choices'][0]['message']['content']];
            }
            
            throw new \Exception('DeepSeek API响应格式错误'];
            
        } catch (RequestException $e) {
            $this->logger->warning('DeepSeek API调用失败，使用本地分�?, ['error' => $e->getMessage()]];
            return $this->fallbackTaskAnalysis($taskDescription, $context];
        }
    }

    /**
     * 构建任务分析提示
     */
    /**

     * buildTaskAnalysisPrompt 方法

     *

     * @param string $taskDescription

     * @param array $context

     * @return void

     */

    private function buildTaskAnalysisPrompt(string $taskDescription, array $context): string
    {
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '�?;
        
        return "请分析以下任务并提供JSON格式的分析结果：

任务描述：{$taskDescription}
上下文：{$contextStr}

请返回JSON格式，包含以下字段：
- task_type: 任务类型
- complexity: 复杂度（1-10�?
- required_capabilities: 所需能力列表
- estimated_time: 预估处理时间（分钟）
- confidence: 分析置信度（0-1�?
- priority: 优先级（low/medium/high�?;
    }

    /**
     * 解析任务分析结果
     */
    /**

     * parseTaskAnalysis 方法

     *

     * @param string $content

     * @return void

     */

    private function parseTaskAnalysis(string $content): array
    {
        // 尝试提取JSON
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $json = json_decode($matches[0],  true];
            if ($json) {
                return array_merge([
                    'task_type' => 'general',
                    'complexity' => 5,
                    'required_capabilities' => ['general'], 
                    'estimated_time' => 10,
                    'confidence' => 0.8,
                    'priority' => 'medium'
                ],  $json];
            }
        }
        
        return $this->fallbackTaskAnalysis('', []];
    }

    /**
     * 备用任务分析
     */
    /**

     * fallbackTaskAnalysis 方法

     *

     * @param string $taskDescription

     * @param array $context

     * @return void

     */

    private function fallbackTaskAnalysis(string $taskDescription, array $context): array
    {
        $keywords = ['代码', '编程', '程序', 'code', 'programming'];
        $isCodeTask = false;
        foreach ($keywords as $keyword) {
            if (stripos($taskDescription, $keyword) !== false) {
                $isCodeTask = true;
                break;
            }
        }

        return [
            'task_type' => $isCodeTask ? 'coding' : 'general',
            'complexity' => 5,
            'required_capabilities' => $isCodeTask ? ['coding'] : ['general'], 
            'estimated_time' => 10,
            'confidence' => 0.7,
            'priority' => 'medium'
        ];
    }

    /**
     * 选择最佳智能体
     */
    /**

     * selectBestAgent 方法

     *

     * @param array $taskAnalysis

     * @return void

     */

    private function selectBestAgent(array $taskAnalysis): ?string
    {
        $bestAgent = null;
        $bestScore = 0;

        foreach ($this->agents as $agentId => $agent) {
            if ($agent['load'] >= $agent['max_concurrent_tasks']) {
                continue; // 智能体已满载
            }

            $score = $this->calculateAgentScore($agent, $taskAnalysis];
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestAgent = $agentId;
            }
        }

        return $bestAgent;
    }

    /**
     * 计算智能体匹配分�?
     */
    /**

     * calculateAgentScore 方法

     *

     * @param array $agent

     * @param array $taskAnalysis

     * @return void

     */

    private function calculateAgentScore(array $agent, array $taskAnalysis): float
    {
        $capabilityScore = 0;
        $requiredCapabilities = $taskAnalysis['required_capabilities'] ?? [];

        foreach ($requiredCapabilities as $capability) {
            if (in_[$capability, $agent['capabilities'])) {
                $capabilityScore += 10;
            }
        }

        $loadScore = (1 - $agent['load'] / $agent['max_concurrent_tasks']) * 5;
        $performanceScore = $agent['performance_score'] / 100 * 5;

        return $capabilityScore + $loadScore + $performanceScore;
    }

    /**
     * 执行任务分配
     */
    /**

     * executeTaskAssignment 方法

     *

     * @param string $taskId

     * @param string $agentId

     * @param array $taskAnalysis

     * @return void

     */

    private function executeTaskAssignment(string $taskId, string $agentId, array $taskAnalysis): array
    {
        // 增加智能体负�?
        $this->agents[$agentId]['load']++;
        
        // 将任务添加到队列
        $this->taskQueue[$taskId] = [
            'id' => $taskId,
            'agent_id' => $agentId,
            'analysis' => $taskAnalysis,
            'status' => 'assigned',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return [
            'task_id' => $taskId,
            'agent' => $this->agents[$agentId]['name'], 
            'status' => 'assigned',
            'estimated_completion' => date('Y-m-d H:i:s', strtotime('+' . $taskAnalysis['estimated_time'] . ' minutes'))
        ];
    }

    /**
     * 更新性能指标
     */
    /**

     * updatePerformanceMetrics 方法

     *

     * @param string $agentId

     * @param array $metrics

     * @return void

     */

    private function updatePerformanceMetrics(string $agentId, array $metrics): void
    {
        if (!isset($this->performanceMetrics[$agentId])) {
            $this->performanceMetrics[$agentId] = [
                'completed_tasks' => 0,
                'total_response_time' => 0,
                'avg_response_time' => 0,
                'success_rate' => 100
            ];
        }

        $this->performanceMetrics[$agentId]['completed_tasks']++;
        
        if (isset($metrics['completion_time'])) {
            $responseTime = $metrics['completion_time'] - (microtime(true) - 1];
            $this->performanceMetrics[$agentId]['total_response_time'] += $responseTime;
            $this->performanceMetrics[$agentId]['avg_response_time'] = 
                $this->performanceMetrics[$agentId]['total_response_time'] / 
                $this->performanceMetrics[$agentId]['completed_tasks'];
        }
    }

    /**
     * 生成任务ID
     */
    /**

     * generateTaskId 方法

     *

     * @return void

     */

    private function generateTaskId(): string
    {
        return 'task_' . uniqid() . '_' . time(];
    }

    /**
     * 获取智能体性能报告
     */
    /**

     * getAgentPerformanceReport 方法

     *

     * @return void

     */

    public function getAgentPerformanceReport(): array
    {
        $report = [
            'total_agents' => count($this->agents],
            'active_tasks' => count($this->taskQueue],
            'total_tasks' => 0,
            'agents' => []
        ];

        foreach ($this->agents as $agentId => $agent) {
            $metrics = $this->performanceMetrics[$agentId] ?? [
                'completed_tasks' => 0,
                'avg_response_time' => 0,
                'success_rate' => 100
            ];

            $report['agents'][$agentId] = [
                'name' => $agent['name'], 
                'status' => $agent['load'] > 0 ? 'busy' : 'idle',
                'current_load' => $agent['load'], 
                'max_capacity' => $agent['max_concurrent_tasks'], 
                'performance' => $metrics
            ];

            $report['total_tasks'] += $metrics['completed_tasks'];
        }

        return $report;
    }

    /**
     * 获取任务队列状�?
     */
    /**

     * getTaskQueueStatus 方法

     *

     * @return void

     */

    public function getTaskQueueStatus(): array
    {
        return [
            'total_tasks' => count($this->taskQueue],
            'pending_tasks' => count(array_filter($this->taskQueue, function($task) {
                return $task['status'] === 'assigned';
            })],
            'tasks' => array_values($this->taskQueue)
        ];
    }

    /**
     * 处理任务完成
     */
    /**

     * completeTask 方法

     *

     * @param string $taskId

     * @param array $result

     * @return void

     */

    public function completeTask(string $taskId, array $result = []): bool
    {
        if (!isset($this->taskQueue[$taskId])) {
            return false;
        }

        $task = $this->taskQueue[$taskId];
        $agentId = $task['agent_id'];

        // 减少智能体负�?
        if ($this->agents[$agentId]['load'] > 0) {
            $this->agents[$agentId]['load']--;
        }

        // 更新任务状�?
        $this->taskQueue[$taskId]['status'] = 'completed';
        $this->taskQueue[$taskId]['completed_at'] = date('Y-m-d H:i:s'];
        $this->taskQueue[$taskId]['result'] = $result;

        $this->logger->info('任务完成', ['task_id' => $taskId, 'agent' => $agentId]];

        return true;
    }

    /**
     * 获取系统健康状况
     */
    /**

     * getHealthStatus 方法

     *

     * @return void

     */

    public function getHealthStatus(): array
    {
        $totalLoad = array_sum(array_column($this->agents, 'load')];
        $maxCapacity = array_sum(array_column($this->agents, 'max_concurrent_tasks')];
        $utilization = $maxCapacity > 0 ? ($totalLoad / $maxCapacity) * 100 : 0;

        return [
            'status' => $utilization < 80 ? 'healthy' : ($utilization < 95 ? 'warning' : 'critical'],
            'utilization_rate' => round($utilization, 2],
            'active_agents' => count(array_filter($this->agents, function($agent) {
                return $agent['load'] > 0;
            })],
            'queue_health' => count($this->taskQueue) < 100 ? 'good' : 'high_load'
        ];
    }
}

