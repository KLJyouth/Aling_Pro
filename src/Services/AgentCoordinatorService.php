<?php

namespace AlingAi\Services;

use AlingAi\Core\Database;
use AlingAi\Core\Logger;
use AlingAi\Core\Config;

/**
 * 增强智能体协调服务
 * Enhanced Agent Coordinator Service
 * 
 * 负责多智能体系统的协调、调度和监控
 */
class AgentCoordinatorService
{
    private Database $database;
    private array $agents = [];
    private array $activeConnections = [];
    private int $maxConcurrentAgents = 10;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->initializeAgents();
    }
    
    /**
     * 初始化智能体系统
     */
    private function initializeAgents(): void
    {
        $this->agents = [
            'chat_agent' => [
                'type' => 'chat',
                'model' => 'deepseek-chat',
                'max_tokens' => 4000,
                'temperature' => 0.7,
                'status' => 'ready'
            ],
            'analysis_agent' => [
                'type' => 'analysis',
                'model' => 'deepseek-coder',
                'max_tokens' => 8000,
                'temperature' => 0.3,
                'status' => 'ready'
            ],
            'threat_detection_agent' => [
                'type' => 'security',
                'model' => 'deepseek-chat',
                'max_tokens' => 2000,
                'temperature' => 0.1,
                'status' => 'monitoring'
            ]
        ];
        
        Logger::info('AgentCoordinator initialized', [
            'agent_count' => count($this->agents),
            'max_concurrent' => $this->maxConcurrentAgents
        ]);
    }
    
    /**
     * 协调智能体处理请求
     */
    public function coordinateRequest(array $request): array
    {
        $requestId = $this->generateRequestId();
        $agent = $this->selectOptimalAgent($request);
        
        if (!$agent) {
            throw new \Exception('No available agent for request type: ' . $request['type']);
        }
        
        try {
            // 记录请求开始
            $this->logRequestStart($requestId, $agent, $request);
            
            // 执行智能体任务
            $response = $this->executeAgentTask($agent, $request);
            
            // 记录请求完成
            $this->logRequestComplete($requestId, $response);
            
            return [
                'request_id' => $requestId,
                'agent' => $agent,
                'response' => $response,
                'status' => 'success',
                'timestamp' => time()
            ];
            
        } catch (\Exception $e) {
            $this->logRequestError($requestId, $e);
            throw $e;
        }
    }
    
    /**
     * 选择最优智能体
     */
    private function selectOptimalAgent(array $request): ?string
    {
        $requestType = $request['type'] ?? 'chat';
        
        // 根据请求类型选择合适的智能体
        switch ($requestType) {
            case 'chat':
            case 'conversation':
                return 'chat_agent';
                
            case 'analysis':
            case 'code_review':
            case 'data_analysis':
                return 'analysis_agent';
                
            case 'security':
            case 'threat_detection':
                return 'threat_detection_agent';
                
            default:
                return 'chat_agent'; // 默认使用聊天智能体
        }
    }
    
    /**
     * 执行智能体任务
     */
    private function executeAgentTask(string $agentId, array $request): array
    {
        $agent = $this->agents[$agentId];
        
        // 模拟智能体处理
        $processingTime = rand(500, 2000); // 500ms到2s的处理时间
        usleep($processingTime * 1000);
        
        // 构建响应
        $response = [
            'agent_id' => $agentId,
            'model' => $agent['model'],
            'content' => $this->generateAgentResponse($agent, $request),
            'tokens_used' => rand(50, $agent['max_tokens']),
            'processing_time' => $processingTime,
            'confidence' => rand(85, 99) / 100
        ];
        
        return $response;
    }
    
    /**
     * 生成智能体响应
     */
    private function generateAgentResponse(array $agent, array $request): string
    {
        $message = $request['message'] ?? '';
        
        switch ($agent['type']) {
            case 'chat':
                return "智能聊天助手已处理您的消息: " . substr($message, 0, 50) . "...";
                
            case 'analysis':
                return "代码分析智能体已完成分析，发现了潜在的优化点和安全建议。";
                
            case 'security':
                return "安全监控智能体检测到正常活动，系统安全状态良好。";
                
            default:
                return "任务已完成处理。";
        }
    }
    
    /**
     * 获取智能体状态
     */
    public function getAgentStatus(): array
    {
        $status = [];
        
        foreach ($this->agents as $agentId => $agent) {
            $status[$agentId] = [
                'type' => $agent['type'],
                'model' => $agent['model'],
                'status' => $agent['status'],
                'active_connections' => $this->getActiveConnections($agentId),
                'last_activity' => $this->getLastActivity($agentId)
            ];
        }
        
        return $status;
    }
    
    /**
     * 获取活跃连接数
     */
    private function getActiveConnections(string $agentId): int
    {
        return $this->activeConnections[$agentId] ?? 0;
    }
    
    /**
     * 获取最后活动时间
     */
    private function getLastActivity(string $agentId): ?string
    {
        try {
            $stmt = $this->database->prepare("
                SELECT MAX(created_at) as last_activity 
                FROM logs 
                WHERE context->>'$.agent_id' = ?
            ");
            $stmt->execute([$agentId]);
            $result = $stmt->fetch();
            
            return $result['last_activity'] ?? null;
        } catch (\Exception $e) {
            Logger::error('Failed to get last activity', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * 监控智能体健康状态
     */
    public function monitorAgentHealth(): array
    {
        $healthReport = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_status' => 'healthy',
            'agents' => []
        ];
        
        foreach ($this->agents as $agentId => $agent) {
            $health = $this->checkAgentHealth($agentId);
            $healthReport['agents'][$agentId] = $health;
            
            if ($health['status'] !== 'healthy') {
                $healthReport['overall_status'] = 'warning';
            }
        }
        
        // 记录健康检查结果
        Logger::info('Agent health check completed', $healthReport);
        
        return $healthReport;
    }
    
    /**
     * 检查单个智能体健康状态
     */
    private function checkAgentHealth(string $agentId): array
    {
        $agent = $this->agents[$agentId];
        
        // 模拟健康检查
        $memoryUsage = rand(20, 80); // 内存使用率
        $responseTime = rand(100, 1000); // 响应时间（毫秒）
        $errorRate = rand(0, 5) / 100; // 错误率
        
        $status = 'healthy';
        $issues = [];
        
        if ($memoryUsage > 90) {
            $status = 'warning';
            $issues[] = 'High memory usage';
        }
        
        if ($responseTime > 800) {
            $status = 'warning';
            $issues[] = 'Slow response time';
        }
        
        if ($errorRate > 0.1) {
            $status = 'error';
            $issues[] = 'High error rate';
        }
        
        return [
            'agent_id' => $agentId,
            'type' => $agent['type'],
            'status' => $status,
            'metrics' => [
                'memory_usage' => $memoryUsage,
                'response_time' => $responseTime,
                'error_rate' => $errorRate
            ],
            'issues' => $issues,
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 生成请求ID
     */
    private function generateRequestId(): string
    {
        return 'req_' . uniqid() . '_' . rand(1000, 9999);
    }
    
    /**
     * 记录请求开始
     */
    private function logRequestStart(string $requestId, string $agent, array $request): void
    {
        try {
            $stmt = $this->database->prepare("
                INSERT INTO logs (level, message, context, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                'info',
                'Agent request started',
                json_encode([
                    'request_id' => $requestId,
                    'agent_id' => $agent,
                    'request_type' => $request['type'] ?? 'unknown',
                    'message_length' => strlen($request['message'] ?? '')
                ])
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to log request start', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * 记录请求完成
     */
    private function logRequestComplete(string $requestId, array $response): void
    {
        try {
            $stmt = $this->database->prepare("
                INSERT INTO logs (level, message, context, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                'info',
                'Agent request completed',
                json_encode([
                    'request_id' => $requestId,
                    'agent_id' => $response['agent_id'],
                    'tokens_used' => $response['tokens_used'],
                    'processing_time' => $response['processing_time'],
                    'confidence' => $response['confidence']
                ])
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to log request complete', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * 记录请求错误
     */
    private function logRequestError(string $requestId, \Exception $error): void
    {
        try {
            $stmt = $this->database->prepare("
                INSERT INTO logs (level, message, context, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                'error',
                'Agent request failed',
                json_encode([
                    'request_id' => $requestId,
                    'error_message' => $error->getMessage(),
                    'error_code' => $error->getCode(),
                    'error_file' => $error->getFile(),
                    'error_line' => $error->getLine()
                ])
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to log request error', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * 获取协调服务统计信息
     */
    public function getStatistics(): array
    {
        try {
            // 获取今日请求统计
            $stmt = $this->database->prepare("
                SELECT 
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN level = 'error' THEN 1 END) as error_count,
                    AVG(CAST(JSON_EXTRACT(context, '$.processing_time') AS UNSIGNED)) as avg_processing_time
                FROM logs 
                WHERE DATE(created_at) = CURDATE() 
                AND message LIKE '%Agent request%'
            ");
            $stmt->execute();
            $todayStats = $stmt->fetch();
            
            return [
                'today' => $todayStats,
                'agents' => $this->getAgentStatus(),
                'system' => [
                    'max_concurrent_agents' => $this->maxConcurrentAgents,
                    'active_agents' => count(array_filter($this->agents, fn($a) => $a['status'] === 'ready')),
                    'total_agents' => count($this->agents)
                ]
            ];
        } catch (\Exception $e) {
            Logger::error('Failed to get statistics', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to get statistics'];
        }
    }
}
