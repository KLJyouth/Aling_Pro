<?php
/**
 * Token计数器工具类
 * 用于计算和管理AI模型的token使用量
 * 
 * @package AlingAi\Utils
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Utils;

use Monolog\Logger;

class TokenCounter
{
    private Logger $logger;
    
    // 不同模型的token估算规则
    private array $modelTokenRates = [
        'gpt-3.5-turbo' => [
            'chars_per_token' => 4,
            'input_cost_per_1k' => 0.0015,
            'output_cost_per_1k' => 0.002
        ],
        'gpt-4' => [
            'chars_per_token' => 4,
            'input_cost_per_1k' => 0.03,
            'output_cost_per_1k' => 0.06
        ],
        'claude-3' => [
            'chars_per_token' => 4,
            'input_cost_per_1k' => 0.015,
            'output_cost_per_1k' => 0.075
        ]
    ];
    
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 估算文本的token数量
     */
    public function countTokens(string $text, string $model = 'gpt-3.5-turbo'): int
    {
        if (empty($text)) {
            return 0;
        }
        
        $modelConfig = $this->modelTokenRates[$model] ?? $this->modelTokenRates['gpt-3.5-turbo'];
        
        // 基础字符计数方法
        $charCount = mb_strlen($text, 'UTF-8');
        $estimatedTokens = (int) ceil($charCount / $modelConfig['chars_per_token']);
        
        // 对于中文文本，调整系数
        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
            $estimatedTokens = (int) ceil($estimatedTokens * 1.2); // 中文通常需要更多token
        }
        
        $this->logger->debug('Token计数完成', [
            'model' => $model,
            'text_length' => $charCount,
            'estimated_tokens' => $estimatedTokens
        ]);
        
        return $estimatedTokens;
    }
    
    /**
     * 计算消息数组的总token数
     */
    public function countMessagesTokens(array $messages, string $model = 'gpt-3.5-turbo'): int
    {
        $totalTokens = 0;
        
        foreach ($messages as $message) {
            if (isset($message['content'])) {
                $totalTokens += $this->countTokens($message['content'], $model);
            }
            
            // 为消息结构添加额外的token开销
            $totalTokens += 4; // 每条消息的元数据开销
        }
        
        // 为对话添加系统级开销
        $totalTokens += 2;
        
        return $totalTokens;
    }
    
    /**
     * 计算token使用成本
     */
    public function calculateCost(int $inputTokens, int $outputTokens, string $model = 'gpt-3.5-turbo'): float
    {
        $modelConfig = $this->modelTokenRates[$model] ?? $this->modelTokenRates['gpt-3.5-turbo'];
        
        $inputCost = ($inputTokens / 1000) * $modelConfig['input_cost_per_1k'];
        $outputCost = ($outputTokens / 1000) * $modelConfig['output_cost_per_1k'];
        
        $totalCost = $inputCost + $outputCost;
        
        $this->logger->debug('Token成本计算', [
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $totalCost
        ]);
        
        return round($totalCost, 6);
    }
    
    /**
     * 检查token限制
     */
    public function checkTokenLimit(array $messages, string $model = 'gpt-3.5-turbo', int $maxTokens = null): array
    {
        $modelLimits = [
            'gpt-3.5-turbo' => 4096,
            'gpt-4' => 8192,
            'claude-3' => 100000
        ];
        
        $limit = $maxTokens ?? ($modelLimits[$model] ?? 4096);
        $currentTokens = $this->countMessagesTokens($messages, $model);
        
        $result = [
            'within_limit' => $currentTokens <= $limit,
            'current_tokens' => $currentTokens,
            'token_limit' => $limit,
            'remaining_tokens' => max(0, $limit - $currentTokens),
            'usage_percentage' => round(($currentTokens / $limit) * 100, 2)
        ];
        
        if (!$result['within_limit']) {
            $this->logger->warning('Token限制超出', $result);
        }
        
        return $result;
    }
    
    /**
     * 截断消息以适应token限制
     */
    public function truncateMessages(array $messages, string $model = 'gpt-3.5-turbo', int $maxTokens = null): array
    {
        $modelLimits = [
            'gpt-3.5-turbo' => 4096,
            'gpt-4' => 8192,
            'claude-3' => 100000
        ];
        
        $limit = $maxTokens ?? ($modelLimits[$model] ?? 4096);
        $truncatedMessages = [];
        $currentTokens = 0;
        
        // 保留系统消息（如果存在）
        if (!empty($messages) && isset($messages[0]['role']) && $messages[0]['role'] === 'system') {
            $systemMessage = $messages[0];
            $systemTokens = $this->countTokens($systemMessage['content'], $model) + 4;
            
            if ($systemTokens < $limit * 0.3) { // 系统消息不超过30%的限制
                $truncatedMessages[] = $systemMessage;
                $currentTokens += $systemTokens;
                $messages = array_slice($messages, 1);
            }
        }
        
        // 从最新消息开始向前添加
        $messages = array_reverse($messages);
        
        foreach ($messages as $message) {
            $messageTokens = $this->countTokens($message['content'], $model) + 4;
            
            if ($currentTokens + $messageTokens <= $limit) {
                array_unshift($truncatedMessages, $message);
                $currentTokens += $messageTokens;
            } else {
                break;
            }
        }
        
        $this->logger->info('消息截断完成', [
            'original_count' => count($messages) + count($truncatedMessages),
            'truncated_count' => count($truncatedMessages),
            'total_tokens' => $currentTokens,
            'token_limit' => $limit
        ]);
        
        return $truncatedMessages;
    }
    
    /**
     * 获取模型信息
     */
    public function getModelInfo(string $model): array
    {
        return $this->modelTokenRates[$model] ?? [
            'chars_per_token' => 4,
            'input_cost_per_1k' => 0,
            'output_cost_per_1k' => 0
        ];
    }
    
    /**
     * 添加自定义模型配置
     */
    public function addModelConfig(string $model, array $config): void
    {
        $this->modelTokenRates[$model] = array_merge([
            'chars_per_token' => 4,
            'input_cost_per_1k' => 0,
            'output_cost_per_1k' => 0
        ], $config);
        
        $this->logger->info('添加模型配置', [
            'model' => $model,
            'config' => $this->modelTokenRates[$model]
        ]);
    }
    
    /**
     * 获取支持的模型列表
     */
    public function getSupportedModels(): array
    {
        return array_keys($this->modelTokenRates);
    }
    
    /**
     * 生成token使用报告
     */
    public function generateUsageReport(array $usage_data): array
    {
        $report = [
            'total_input_tokens' => 0,
            'total_output_tokens' => 0,
            'total_cost' => 0,
            'models_used' => [],
            'daily_usage' => []
        ];
        
        foreach ($usage_data as $record) {
            $model = $record['model'] ?? 'gpt-3.5-turbo';
            $inputTokens = $record['input_tokens'] ?? 0;
            $outputTokens = $record['output_tokens'] ?? 0;
            $date = $record['date'] ?? date('Y-m-d');
            
            $report['total_input_tokens'] += $inputTokens;
            $report['total_output_tokens'] += $outputTokens;
            
            $cost = $this->calculateCost($inputTokens, $outputTokens, $model);
            $report['total_cost'] += $cost;
            
            // 按模型统计
            if (!isset($report['models_used'][$model])) {
                $report['models_used'][$model] = [
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'cost' => 0
                ];
            }
            $report['models_used'][$model]['input_tokens'] += $inputTokens;
            $report['models_used'][$model]['output_tokens'] += $outputTokens;
            $report['models_used'][$model]['cost'] += $cost;
            
            // 按日期统计
            if (!isset($report['daily_usage'][$date])) {
                $report['daily_usage'][$date] = [
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'cost' => 0
                ];
            }
            $report['daily_usage'][$date]['input_tokens'] += $inputTokens;
            $report['daily_usage'][$date]['output_tokens'] += $outputTokens;
            $report['daily_usage'][$date]['cost'] += $cost;
        }
        
        $report['total_cost'] = round($report['total_cost'], 6);
        
        return $report;
    }
}
