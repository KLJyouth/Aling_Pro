<?php
/**
 * AlingAi Pro - 聊天服务
 * 处理AI聊天、消息管理、会话管理等核心功能
 * 
 * @package AlingAi\Pro\Services
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Utils\{Logger, HttpClient, TokenCounter};
use AlingAi\Models\{Conversation, Message, User};
use AlingAi\Exceptions\{UnauthorizedAccessException, ValidationException};

class ChatService
{
    private DatabaseService $db;
    private CacheService $cache;
    private HttpClient $httpClient;
    private TokenCounter $tokenCounter;
    
    // AI 模型配置
    private array $models = [
        'deepseek-chat' => [
            'endpoint' => 'https://api.deepseek.com/v1/chat/completions',
            'max_tokens' => 4000,
            'supports_streaming' => true
        ],
        'gpt-4' => [
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'max_tokens' => 8000,
            'supports_streaming' => true
        ],
        'claude-3' => [
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'max_tokens' => 4000,
            'supports_streaming' => false
        ]
    ];

    public function __construct(
        DatabaseService $db,
        CacheService $cache,
        HttpClient $httpClient,
        TokenCounter $tokenCounter
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->tokenCounter = $tokenCounter;
    }

    /**
     * 发送消息并获取AI响应
     */
    public function sendMessage(array $params): array
    {
        try {
            $userId = $params['user_id'];
            $message = $params['message'];
            $conversationId = $params['conversation_id'];
            $modelType = $params['model_type'] ?? 'deepseek-chat';
            $temperature = $params['temperature'] ?? 0.7;
            $maxTokens = $params['max_tokens'] ?? 1024;

            // 验证模型类型
            if (!isset($this->models[$modelType])) {
                throw new \InvalidArgumentException('不支持的模型类型: ' . $modelType);
            }

            // 获取或创建会话
            if (!$conversationId) {
                $conversationId = $this->createConversation($userId, $modelType);
            } else {
                $conversation = $this->getConversation($conversationId);
                if (!$conversation || $conversation['user_id'] !== $userId) {
                    throw new UnauthorizedAccessException('无权访问此会话');
                }
            }

            // 保存用户消息
            $userMessageId = $this->saveMessage([
                'conversation_id' => $conversationId,
                'role' => 'user',
                'content' => $message,
                'tokens' => $this->tokenCounter->countTokens($message),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 获取会话历史
            $history = $this->getConversationHistory($conversationId, 10);
            
            // 构建消息上下文
            $messages = $this->buildMessageContext($history, $message);

            // 调用AI API
            $aiResponse = $this->callAiApi($modelType, $messages, $temperature, $maxTokens);

            // 保存AI响应
            $aiMessageId = $this->saveMessage([
                'conversation_id' => $conversationId,
                'role' => 'assistant',
                'content' => $aiResponse['content'],
                'tokens' => $aiResponse['tokens'],
                'model' => $modelType,
                'finish_reason' => $aiResponse['finish_reason'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 更新会话信息
            $this->updateConversation($conversationId, [
                'last_message_at' => date('Y-m-d H:i:s'),
                'message_count' => $this->getMessageCount($conversationId),
                'total_tokens' => $this->getTotalTokens($conversationId)
            ]);

            // 记录使用统计
            $this->recordUsageStats($userId, $modelType, $aiResponse['tokens']);

            return [
                'message_id' => $aiMessageId,
                'conversation_id' => $conversationId,
                'ai_response' => $aiResponse['content'],
                'tokens_used' => $aiResponse['tokens'],
                'model' => $modelType,
                'finish_reason' => $aiResponse['finish_reason']
            ];

        } catch (\Exception $e) {
            Logger::error('发送消息失败', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 调用AI API
     */
    private function callAiApi(string $modelType, array $messages, float $temperature, int $maxTokens): array
    {
        $model = $this->models[$modelType];
        $apiKey = $this->getApiKey($modelType);

        if (!$apiKey) {
            throw new \RuntimeException("未配置 {$modelType} API密钥");
        }

        $requestData = [
            'model' => $modelType,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => min($maxTokens, $model['max_tokens']),
            'stream' => false
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$apiKey}"
        ];

        // 根据不同模型调整请求格式
        switch ($modelType) {
            case 'claude-3':
                $headers['x-api-key'] = $apiKey;
                $headers['anthropic-version'] = '2023-06-01';
                unset($headers['Authorization']);
                $requestData['max_tokens'] = $maxTokens;
                break;
        }

        $startTime = microtime(true);
          try {
            $options = [
                'headers' => $headers,
                'timeout' => 30
            ];
            $response = $this->httpClient->post($model['endpoint'], $requestData, $options);
            $responseTime = (microtime(true) - $startTime) * 1000;

            // 解析响应
            $result = $this->parseAiResponse($modelType, $response);
            
            // 记录API调用日志
            Logger::info('AI API调用成功', [
                'model' => $modelType,
                'tokens' => $result['tokens'],
                'response_time_ms' => round($responseTime, 2)
            ]);

            return $result;

        } catch (\Exception $e) {
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            Logger::error('AI API调用失败', [
                'model' => $modelType,
                'error' => $e->getMessage(),
                'response_time_ms' => round($responseTime, 2)
            ]);
            
            throw new \RuntimeException("AI API调用失败: " . $e->getMessage());
        }
    }

    /**
     * 解析AI响应
     */
    private function parseAiResponse(string $modelType, array $response): array
    {
        switch ($modelType) {
            case 'deepseek-chat':
            case 'gpt-4':
                if (!isset($response['choices'][0]['message']['content'])) {
                    throw new \RuntimeException('无效的API响应格式');
                }
                
                return [
                    'content' => $response['choices'][0]['message']['content'],
                    'tokens' => $response['usage']['total_tokens'] ?? 0,
                    'finish_reason' => $response['choices'][0]['finish_reason'] ?? 'stop'
                ];
                
            case 'claude-3':
                if (!isset($response['content'][0]['text'])) {
                    throw new \RuntimeException('无效的API响应格式');
                }
                
                return [
                    'content' => $response['content'][0]['text'],
                    'tokens' => $response['usage']['output_tokens'] ?? 0,
                    'finish_reason' => $response['stop_reason'] ?? 'end_turn'
                ];
                
            default:
                throw new \RuntimeException('不支持的模型类型');
        }
    }

    /**
     * 构建消息上下文
     */
    private function buildMessageContext(array $history, string $newMessage): array
    {
        $messages = [];
        
        // 添加系统提示
        $messages[] = [
            'role' => 'system',
            'content' => $this->getSystemPrompt()
        ];
        
        // 添加历史消息
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }
        
        // 添加当前消息
        $messages[] = [
            'role' => 'user',
            'content' => $newMessage
        ];
        
        return $messages;
    }

    /**
     * 获取系统提示
     */
    private function getSystemPrompt(): string
    {
        return "你是AlingAi Pro的智能助手，一个专业、友好、有帮助的AI助手。请用中文回答用户问题，提供准确、有用的信息。保持回答简洁明了，同时确保信息的完整性。";
    }

    /**
     * 创建新会话
     */
    public function createConversation(int $userId, string $modelType = 'deepseek-chat'): string
    {
        $conversationId = $this->generateUuid();
        
        $this->db->insert('conversations', [
            'id' => $conversationId,
            'user_id' => $userId,
            'title' => '新对话',
            'model' => $modelType,
            'message_count' => 0,
            'total_tokens' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        Logger::info('创建新会话', [
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'model' => $modelType
        ]);
        
        return $conversationId;
    }

    /**
     * 获取会话信息
     */
    public function getConversation(string $conversationId): ?array
    {
        $cacheKey = "conversation:{$conversationId}";
        
        $conversation = $this->cache->get($cacheKey);
        if ($conversation) {
            return $conversation;
        }
        
        $conversation = $this->db->selectOne('conversations', ['id' => $conversationId]);
        
        if ($conversation) {
            $this->cache->set($cacheKey, $conversation, 300); // 缓存5分钟
        }
        
        return $conversation;
    }

    /**
     * 更新会话信息
     */
    private function updateConversation(string $conversationId, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->update('conversations', $data, ['id' => $conversationId]);
        
        // 清除缓存
        $this->cache->delete("conversation:{$conversationId}");
    }

    /**
     * 保存消息
     */
    private function saveMessage(array $messageData): string
    {
        $messageId = $this->generateUuid();
        $messageData['id'] = $messageId;
        
        $this->db->insert('messages', $messageData);
        
        return $messageId;
    }

    /**
     * 获取会话历史
     */
    public function getConversationHistory(string $conversationId, int $limit = 20): array
    {
        return $this->db->select('messages', [
            'conversation_id' => $conversationId
        ], [
            'order' => ['created_at' => 'DESC'],
            'limit' => $limit
        ]);
    }

    /**
     * 获取聊天历史
     */
    public function getChatHistory(int $userId, ?string $conversationId = null, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        if ($conversationId) {
            // 获取特定会话的消息
            $messages = $this->db->select('messages', [
                'conversation_id' => $conversationId
            ], [
                'order' => ['created_at' => 'ASC'],
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $total = $this->db->count('messages', ['conversation_id' => $conversationId]);
            
            return [
                'conversations' => [],
                'messages' => $messages,
                'total' => $total
            ];
        } else {
            // 获取用户的所有会话
            $conversations = $this->db->select('conversations', [
                'user_id' => $userId
            ], [
                'order' => ['updated_at' => 'DESC'],
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $total = $this->db->count('conversations', ['user_id' => $userId]);
            
            return [
                'conversations' => $conversations,
                'messages' => [],
                'total' => $total
            ];
        }
    }

    /**
     * 删除会话
     */
    public function deleteConversation(string $conversationId, int $userId): bool
    {
        // 验证权限
        $conversation = $this->getConversation($conversationId);
        if (!$conversation || $conversation['user_id'] !== $userId) {
            return false;
        }
        
        // 删除消息
        $this->db->delete('messages', ['conversation_id' => $conversationId]);
        
        // 删除会话
        $this->db->delete('conversations', ['id' => $conversationId]);
        
        // 清除缓存
        $this->cache->delete("conversation:{$conversationId}");
        
        Logger::info('删除会话', [
            'conversation_id' => $conversationId,
            'user_id' => $userId
        ]);
        
        return true;
    }

    /**
     * 健康检查
     */
    public function healthCheck(): array
    {
        $startTime = microtime(true);
        
        try {
            // 检查数据库连接
            $this->db->query('SELECT 1');
            $dbTime = (microtime(true) - $startTime) * 1000;
            
            // 检查缓存连接
            $cacheStart = microtime(true);
            $this->cache->get('health_check');
            $cacheTime = (microtime(true) - $cacheStart) * 1000;
            
            return [
                'status' => 'healthy',
                'response_time' => round((microtime(true) - $startTime) * 1000, 2),
                'connections' => $this->getActiveConnections(),
                'queue_size' => 0 // 如果有消息队列的话
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'response_time' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
    }

    /**
     * 获取API密钥
     */
    private function getApiKey(string $modelType): ?string
    {
        $envMap = [
            'deepseek-chat' => 'DEEPSEEK_API_KEY',
            'gpt-4' => 'OPENAI_API_KEY',
            'claude-3' => 'ANTHROPIC_API_KEY'
        ];
        
        return $_ENV[$envMap[$modelType]] ?? null;
    }

    /**
     * 生成UUID
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * 记录使用统计
     */
    private function recordUsageStats(int $userId, string $model, int $tokens): void
    {
        // 实现使用统计记录
        $this->db->insert('usage_stats', [
            'user_id' => $userId,
            'model' => $model,
            'tokens' => $tokens,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取消息数量
     */
    private function getMessageCount(string $conversationId): int
    {
        return $this->db->count('messages', ['conversation_id' => $conversationId]);
    }

    /**
     * 获取总token数
     */
    private function getTotalTokens(string $conversationId): int
    {
        $result = $this->db->query(
            'SELECT SUM(tokens) as total FROM messages WHERE conversation_id = ?',
            [$conversationId]
        );
        
        return (int)($result[0]['total'] ?? 0);
    }

    /**
     * 获取活跃连接数
     */
    private function getActiveConnections(): int
    {
        // 这里可以实现获取当前活跃连接数的逻辑
        return 1;
    }

    /**
     * 获取统计数据
     */
    public function getTotalConversations(): int
    {
        return $this->db->count('conversations');
    }

    public function getConversationsToday(): int
    {
        return $this->db->count('conversations', [
            'created_at' => ['>=', date('Y-m-d 00:00:00')]
        ]);
    }

    public function getTotalMessages(): int
    {
        return $this->db->count('messages');
    }

    public function getMessagesToday(): int
    {
        return $this->db->count('messages', [
            'created_at' => ['>=', date('Y-m-d 00:00:00')]
        ]);
    }

    /**
     * 导出数据
     */
    public function exportData(string $type, string $format, ?string $startDate = null, ?string $endDate = null): string
    {
        $conditions = [];
        
        if ($startDate) {
            $conditions['created_at'] = ['>=', $startDate];
        }
        
        if ($endDate) {
            $conditions['created_at'] = ['<=', $endDate];
        }
        
        switch ($type) {
            case 'conversations':
                $data = $this->db->select('conversations', $conditions);
                break;
            case 'messages':
                $data = $this->db->select('messages', $conditions);
                break;
            default:
                throw new \InvalidArgumentException('不支持的导出类型');
        }
        
        if ($format === 'csv') {
            return $this->convertToCsv($data);
        } else {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 转换为CSV格式
     */
    private function convertToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }
        
        $output = fopen('php://temp', 'r+');
        
        // 写入表头
        fputcsv($output, array_keys($data[0]));
        
        // 写入数据
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
