<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\AI\IntelligentAgentCoordinator;
use AlingAi\AI\SelfLearningFramework;
use AlingAi\Services\DeepSeekAIService;
use AlingAi\Services\DatabaseServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * AI代理管理控制器
 * 
 * 提供AI代理系统的REST API接口
 */
class AIAgentController
{
    private IntelligentAgentCoordinator $coordinator;
    private SelfLearningFramework $learningFramework;
    private DeepSeekAIService $aiService;
    private DatabaseServiceInterface $database;
    private LoggerInterface $logger;
    
    public function __construct(
        IntelligentAgentCoordinator $coordinator,
        SelfLearningFramework $learningFramework,
        DeepSeekAIService $aiService,
        DatabaseServiceInterface $database,
        LoggerInterface $logger
    ) {
        $this->coordinator = $coordinator;
        $this->learningFramework = $learningFramework;
        $this->aiService = $aiService;
        $this->database = $database;
        $this->logger = $logger;
    }
    
    /**
     * 获取代理系统状态
     */
    public function getSystemStatus(): array
    {
        try {
            $coordinatorStatus = $this->coordinator->getStatus();
            $learningStatus = $this->learningFramework->getLearningStatus();
            
            return [
                'success' => true,
                'data' => [
                    'coordinator' => $coordinatorStatus,
                    'learning_framework' => $learningStatus,
                    'system_health' => $this->calculateSystemHealth($coordinatorStatus, $learningStatus),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('获取系统状态失败', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 创建新代理
     */
    public function createAgent(): array
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $type = $input['type'] ?? IntelligentAgentCoordinator::AGENT_TYPE_CHAT;
            $config = $input['config'] ?? [];
            
            $agentId = $this->coordinator->createAgent($type, $config);
            
            return [
                'success' => true,
                'data' => [
                    'agent_id' => $agentId,
                    'type' => $type,
                    'config' => $config,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('创建代理失败', [
                'error' => $e->getMessage(),
                'input' => $input ?? null
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 分配任务
     */
    public function assignTask(): array
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $task = [
                'type' => $input['type'] ?? IntelligentAgentCoordinator::AGENT_TYPE_CHAT,
                'data' => $input['data'] ?? [],
                'priority' => $input['priority'] ?? IntelligentAgentCoordinator::PRIORITY_NORMAL,
                'timeout' => $input['timeout'] ?? 300,
                'context' => $input['context'] ?? []
            ];
            
            $taskId = $this->coordinator->assignTask($task);
            
            return [
                'success' => true,
                'data' => [
                    'task_id' => $taskId,
                    'status' => 'assigned',
                    'estimated_completion' => date('Y-m-d H:i:s', time() + $task['timeout'])
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('分配任务失败', [
                'error' => $e->getMessage(),
                'input' => $input ?? null
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 处理任务
     */
    public function processTask(): array
    {
        try {
            $taskId = $_GET['task_id'] ?? null;
            
            if (!$taskId) {
                throw new \InvalidArgumentException('缺少任务ID');
            }
            
            $result = $this->coordinator->processTask($taskId);
            
            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('处理任务失败', [
                'error' => $e->getMessage(),
                'task_id' => $taskId ?? null
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 执行智能聊天
     */
    public function intelligentChat(): array
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $message = $input['message'] ?? '';
            $context = $input['context'] ?? [];
            $userId = $input['user_id'] ?? '';
            
            if (empty($message)) {
                throw new \InvalidArgumentException('消息内容不能为空');
            }
            
            // 创建聊天任务
            $task = [
                'type' => IntelligentAgentCoordinator::AGENT_TYPE_CHAT,
                'message' => $message,
                'context' => $context,
                'user_id' => $userId,
                'priority' => IntelligentAgentCoordinator::PRIORITY_HIGH
            ];
            
            $taskId = $this->coordinator->assignTask($task);
            $result = $this->coordinator->processTask($taskId);
            
            return [
                'success' => true,
                'data' => [
                    'response' => $result['result']['content'] ?? '',
                    'task_id' => $taskId,
                    'agent_id' => $result['agent_id'] ?? '',
                    'processing_time' => $result['duration'] ?? 0,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('智能聊天失败', [
                'error' => $e->getMessage(),
                'input' => $input ?? null
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 执行智能分析
     */
    public function intelligentAnalysis(): array
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $data = $input['data'] ?? [];
            $analysisType = $input['analysis_type'] ?? 'general';
            
            if (empty($data)) {
                throw new \InvalidArgumentException('分析数据不能为空');
            }
            
            // 创建分析任务
            $task = [
                'type' => IntelligentAgentCoordinator::AGENT_TYPE_ANALYSIS,
                'data' => $data,
                'analysis_type' => $analysisType,
                'priority' => IntelligentAgentCoordinator::PRIORITY_NORMAL
            ];
            
            $taskId = $this->coordinator->assignTask($task);
            $result = $this->coordinator->processTask($taskId);
            
            return [
                'success' => true,
                'data' => [
                    'analysis_result' => $result['result']['analysis'] ?? '',
                    'data_points' => $result['result']['data_points'] ?? 0,
                    'task_id' => $taskId,
                    'agent_id' => $result['agent_id'] ?? '',
                    'processing_time' => $result['duration'] ?? 0,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('智能分析失败', [
                'error' => $e->getMessage(),
                'input' => $input ?? null
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 安全威胁分析
     */
    public function securityAnalysis(): array
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $securityData = $input['security_data'] ?? [];
            $threatType = $input['threat_type'] ?? 'general';
            
            if (empty($securityData)) {
                throw new \InvalidArgumentException('安全数据不能为空');
            }
            
            // 创建安全分析任务
            $task = [
                'type' => IntelligentAgentCoordinator::AGENT_TYPE_SECURITY,
                'security_data' => $securityData,
                'threat_type' => $threatType,
                'priority' => IntelligentAgentCoordinator::PRIORITY_HIGH
            ];
            
            $taskId = $this->coordinator->assignTask($task);
            $result = $this->coordinator->processTask($taskId);
            
            return [
                'success' => true,
                'data' => [
                    'threats_detected' => $result['result']['threats_detected'] ?? '',
                    'risk_level' => $result['result']['risk_level'] ?? 'unknown',
                    'task_id' => $taskId,
                    'agent_id' => $result['agent_id'] ?? '',
                    'processing_time' => $result['duration'] ?? 0,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('安全分析失败', [
                'error' => $e->getMessage(),
                'input' => $input ?? null
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 系统优化建议
     */
    public function optimizationRecommendations(): array
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $targetData = $input['target_data'] ?? [];
            $optimizationType = $input['optimization_type'] ?? 'performance';
            
            if (empty($targetData)) {
                throw new \InvalidArgumentException('优化目标数据不能为空');
            }
            
            // 创建优化任务
            $task = [
                'type' => IntelligentAgentCoordinator::AGENT_TYPE_OPTIMIZATION,
                'target_data' => $targetData,
                'optimization_type' => $optimizationType,
                'priority' => IntelligentAgentCoordinator::PRIORITY_NORMAL
            ];
            
            $taskId = $this->coordinator->assignTask($task);
            $result = $this->coordinator->processTask($taskId);
            
            return [
                'success' => true,
                'data' => [
                    'recommendations' => $result['result']['recommendations'] ?? '',
                    'optimization_type' => $result['result']['optimization_type'] ?? '',
                    'task_id' => $taskId,
                    'agent_id' => $result['agent_id'] ?? '',
                    'processing_time' => $result['duration'] ?? 0,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('优化建议生成失败', [
                'error' => $e->getMessage(),
                'input' => $input ?? null
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取学习框架状态
     */
    public function getLearningStatus(): array
    {
        try {
            $status = $this->learningFramework->getLearningStatus();
            
            return [
                'success' => true,
                'data' => $status
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('获取学习状态失败', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 生成学习报告
     */
    public function generateLearningReport(): array
    {
        try {
            $report = $this->learningFramework->generateLearningReport();
            
            return [
                'success' => true,
                'data' => $report
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('生成学习报告失败', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 生成协调报告
     */
    public function generateCoordinationReport(): array
    {
        try {
            $report = $this->coordinator->generateCoordinationReport();
            
            return [
                'success' => true,
                'data' => $report
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('生成协调报告失败', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 健康检查
     */
    public function healthCheck(): array
    {
        try {
            $coordinatorStatus = $this->coordinator->getStatus();
            $learningStatus = $this->learningFramework->getLearningStatus();
            
            $health = [
                'coordinator_healthy' => $coordinatorStatus['coordinator_status'] === 'running',
                'learning_framework_healthy' => $learningStatus['framework_status'] === 'active',
                'total_agents' => $coordinatorStatus['total_agents'],
                'active_tasks' => $coordinatorStatus['active_tasks'],
                'system_load' => $this->calculateSystemLoad($coordinatorStatus),
                'uptime' => $this->getSystemUptime(),
                'last_check' => date('Y-m-d H:i:s')
            ];
            
            return [
                'success' => true,
                'data' => $health,
                'status' => $health['coordinator_healthy'] && $health['learning_framework_healthy'] ? 'healthy' : 'unhealthy'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('健康检查失败', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'unhealthy'
            ];
        }
    }
    
    /**
     * 计算系统健康度
     */
    private function calculateSystemHealth(array $coordinatorStatus, array $learningStatus): array
    {
        $totalAgents = $coordinatorStatus['total_agents'] ?? 0;
        $activeAgents = 0;
        
        foreach ($coordinatorStatus['agents'] ?? [] as $agent) {
            if (in_array($agent['status'], ['idle', 'busy'])) {
                $activeAgents++;
            }
        }
        
        $agentHealthScore = $totalAgents > 0 ? ($activeAgents / $totalAgents) * 100 : 0;
        $taskSuccessRate = $this->calculateTaskSuccessRate($coordinatorStatus);
        $learningEfficiency = $this->calculateLearningEfficiency($learningStatus);
        
        $overallHealth = ($agentHealthScore + $taskSuccessRate + $learningEfficiency) / 3;
        
        return [
            'overall_score' => round($overallHealth, 2),
            'agent_health' => round($agentHealthScore, 2),
            'task_success_rate' => round($taskSuccessRate, 2),
            'learning_efficiency' => round($learningEfficiency, 2),
            'status' => $overallHealth >= 80 ? 'excellent' : ($overallHealth >= 60 ? 'good' : 'needs_attention')
        ];
    }
    
    /**
     * 计算任务成功率
     */
    private function calculateTaskSuccessRate(array $status): float
    {
        $completedTasks = $status['completed_tasks'] ?? 0;
        $failedTasks = $status['failed_tasks'] ?? 0;
        $totalTasks = $completedTasks + $failedTasks;
        
        if ($totalTasks === 0) {
            return 100.0;
        }
        
        return ($completedTasks / $totalTasks) * 100;
    }
    
    /**
     * 计算学习效率
     */
    private function calculateLearningEfficiency(array $status): float
    {
        $knowledgeBaseSize = $status['knowledge_base_size'] ?? 0;
        $learningModules = count($status['learning_modules'] ?? []);
        
        if ($learningModules === 0) {
            return 0.0;
        }
        
        // 简单的效率计算：知识库大小 / 学习模块数
        return min(($knowledgeBaseSize / $learningModules) * 10, 100);
    }
    
    /**
     * 计算系统负载
     */
    private function calculateSystemLoad(array $status): float
    {
        $totalAgents = $status['total_agents'] ?? 0;
        $activeTasks = $status['active_tasks'] ?? 0;
        
        if ($totalAgents === 0) {
            return 0.0;
        }
        
        return min(($activeTasks / $totalAgents) * 100, 100);
    }
    
    /**
     * 获取系统运行时间
     */
    private function getSystemUptime(): int
    {
        // 简化实现，返回固定值
        return time() - strtotime('2024-01-01 00:00:00');
    }
}
