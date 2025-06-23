<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use Exception;
use InvalidArgumentException;
use AlingAi\Services\DeepSeekApiService;

/**
 * 聊天API控制器
 * 
 * 处理AI聊天、对话管理、消息历史等功能
 * 
 * @package AlingAi\Controllers\Api
 * @version 1.0.0
 * @since 2024-12-19
 */
class ChatApiController extends BaseApiController
{
    private array $aiConfig;    public function __construct()
    {
        parent::__construct();
        $this->aiConfig = $this->config['ai'] ?? [];
    }    /**
     * 测试端点
     */
    public function test(): void
    {
        $this->sendSuccessResponse([
            'message' => 'Chat API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }/**
     * 发送聊天消息
     */
    public function sendMessage(): void
    {
        try {
            if (!$this->requireAuth()) {
                return;
            }

            $data = $this->getRequestData();
            $user = $this->getCurrentUser();
            
            // 验证输入数据
            $validated = $this->validateRequestData($data, [
                'message' => ['required' => true, 'max_length' => 4000],
                'conversation_id' => ['type' => 'numeric'],
                'model' => ['max_length' => 50],
                'stream' => ['type' => 'boolean']
            ]);

            $conversationId = $validated['conversation_id'] ?? null;
            $model = $validated['model'] ?? $this->aiConfig['default_model'] ?? 'gpt-3.5-turbo';
            $stream = $validated['stream'] ?? false;            // 消息安全检查
            if (!$this->security->sanitizeInput($validated['message'])) {
                $this->sendErrorResponse('Message contains inappropriate content', 400);
                return;
            }

            $db = $this->getDatabase();            // 创建或验证对话
            if ($conversationId) {
                $conversation = $this->getConversationById($conversationId, $user['user_id']);
                if (!$conversation) {
                    $this->sendErrorResponse('Conversation not found', 404);
                    return;
                }
            } else {
                $conversationId = $this->createConversation($user['user_id'], $validated['message']);
            }

            // 保存用户消息
            $messageId = $this->saveMessage($conversationId, 'user', $validated['message'], $user['user_id']);

            // 获取对话历史
            $history = $this->getConversationHistory($conversationId, 10);            // 调用AI API
            $response = $this->callAiApi($history, $model, $stream);

            if (!$response['success']) {
                $this->sendErrorResponse('AI service error: ' . $response['error'], 500);
                return;
            }

            // 保存AI回复
            $aiMessageId = $this->saveMessage($conversationId, 'assistant', $response['content'], null, [
                'model' => $model,
                'tokens_used' => $response['tokens_used'] ?? 0,
                'response_time' => $response['response_time'] ?? 0
            ]);

            // 更新对话
            $this->updateConversation($conversationId);            // 记录用户活动
            $this->monitor->logUserActivity($user['user_id'], 'chat_message_sent');

            $this->sendSuccessResponse([
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
                'ai_message_id' => $aiMessageId,
                'response' => $response['content'],
                'model' => $model,
                'tokens_used' => $response['tokens_used'] ?? 0
            ]);

        } catch (InvalidArgumentException $e) {
            $this->sendErrorResponse('Validation failed', 400, json_decode($e->getMessage(), true));
        } catch (Exception $e) {
            $this->monitor->logError('Chat message failed', [
                'error' => $e->getMessage(),
                'user_id' => $user['user_id'] ?? 'unknown'
            ]);
            $this->sendErrorResponse('Failed to send message', 500);
        }
    }    /**
     * 获取对话列表
     */
    public function getConversations(): void
    {
        try {
            if (!$this->requireAuth()) {
                return;
            }

            $user = $this->getCurrentUser();
            $pagination = $this->getPaginationParams();

            $db = $this->getDatabase();
            
            // 获取对话总数
            $stmt = $db->prepare("
                SELECT COUNT(*) as total 
                FROM conversations 
                WHERE user_id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$user['user_id']]);
            $total = $stmt->fetch()['total'];

            // 获取对话列表
            $stmt = $db->prepare("
                SELECT c.*, 
                       (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_at,
                       (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id) as message_count
                FROM conversations c
                WHERE c.user_id = ? AND c.deleted_at IS NULL
                ORDER BY c.updated_at DESC
                LIMIT ? OFFSET ?
            ");            $stmt->execute([$user['user_id'], $pagination['limit'], $pagination['offset']]);
            $conversations = $stmt->fetchAll();

            $this->sendSuccessResponse($this->buildPaginatedResponse($conversations, $total, $pagination));

        } catch (Exception $e) {
            $this->monitor->logError('Get conversations failed', [
                'error' => $e->getMessage(),
                'user_id' => $user['user_id'] ?? 'unknown'
            ]);
            $this->sendErrorResponse('Failed to get conversations', 500);
        }
    }    /**
     * 获取单个对话详情
     */
    public function getConversation(): void
    {
        try {
            if (!$this->requireAuth()) {
                return;
            }

            $user = $this->getCurrentUser();
            $conversationId = $_GET['id'] ?? null;

            if (!$conversationId) {
                $this->sendErrorResponse('Conversation ID required', 400);
                return;
            }

            $conversation = $this->getConversationById($conversationId, $user['user_id']);
            if (!$conversation) {
                $this->sendErrorResponse('Conversation not found', 404);
                return;
            }            // 获取消息列表
            $pagination = $this->getPaginationParams();
            $messages = $this->getConversationMessages($conversationId, $pagination);

            $this->sendSuccessResponse([
                'conversation' => $conversation,
                'messages' => $messages
            ]);

        } catch (Exception $e) {
            $this->monitor->logError('Get conversation failed', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId ?? 'unknown'
            ]);
            $this->sendErrorResponse('Failed to get conversation', 500);
        }
    }    /**
     * 删除对话
     */
    public function deleteConversation(): void
    {        try {
            if (!$this->requireAuth()) {
                return;
            }

            $user = $this->getCurrentUser();
            $conversationId = $_GET['id'] ?? null;            if (!$conversationId) {
                $this->sendErrorResponse('Conversation ID required', 400);
                return;
            }

            $conversation = $this->getConversationById($conversationId, $user['user_id']);
            if (!$conversation) {
                $this->sendErrorResponse('Conversation not found', 404);
                return;
            }

            $db = $this->getDatabase();
            
            // 软删除对话
            $stmt = $db->prepare("UPDATE conversations SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$conversationId]);

            // 软删除相关消息
            $stmt = $db->prepare("UPDATE messages SET deleted_at = NOW() WHERE conversation_id = ?");
            $stmt->execute([$conversationId]);            $this->monitor->logUserActivity($user['user_id'], 'conversation_deleted');

            $this->sendSuccessResponse([
                'message' => 'Conversation deleted successfully'
            ]);

        } catch (Exception $e) {
            $this->monitor->logError('Delete conversation failed', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId ?? 'unknown'
            ]);
            $this->sendErrorResponse('Failed to delete conversation', 500);
        }
    }    /**
     * 重新生成AI响应
     */    public function regenerateResponse(): void
    {
        try {
            if (!$this->requireAuth()) {
                return;
            }

            $data = $this->getRequestData();
            $user = $this->getCurrentUser();

            $validated = $this->validateRequestData($data, [
                'message_id' => ['required' => true, 'type' => 'numeric'],
                'model' => ['max_length' => 50]
            ]);

            $db = $this->getDatabase();
            
            // 获取原始消息
            $stmt = $db->prepare("
                SELECT m.*, c.user_id 
                FROM messages m
                JOIN conversations c ON m.conversation_id = c.id
                WHERE m.id = ? AND m.role = 'user' AND m.deleted_at IS NULL
            ");
            $stmt->execute([$validated['message_id']]);
            $message = $stmt->fetch();            if (!$message || $message['user_id'] != $user['user_id']) {
                $this->sendErrorResponse('Message not found', 404);
                return;
            }

            $model = $validated['model'] ?? $this->aiConfig['default_model'] ?? 'gpt-3.5-turbo';

            // 获取对话历史（包括重新生成的消息）
            $history = $this->getConversationHistory($message['conversation_id'], 10, $validated['message_id']);

            // 调用AI API
            $response = $this->callAiApi($history, $model, false);            if (!$response['success']) {
                $this->sendErrorResponse('AI service error: ' . $response['error'], 500);
                return;
            }

            // 保存新的AI回复
            $aiMessageId = $this->saveMessage($message['conversation_id'], 'assistant', $response['content'], null, [
                'model' => $model,
                'tokens_used' => $response['tokens_used'] ?? 0,
                'response_time' => $response['response_time'] ?? 0,
                'regenerated' => true,
                'original_message_id' => $validated['message_id']
            ]);            $this->monitor->logUserActivity($user['user_id'], 'response_regenerated');

            $this->sendSuccessResponse([
                'message_id' => $aiMessageId,
                'response' => $response['content'],
                'model' => $model,
                'tokens_used' => $response['tokens_used'] ?? 0
            ]);

        } catch (InvalidArgumentException $e) {
            $this->sendErrorResponse('Validation failed', 400, json_decode($e->getMessage(), true));
        } catch (Exception $e) {
            $this->monitor->logError('Regenerate response failed', [
                'error' => $e->getMessage(),
                'message_id' => $validated['message_id'] ?? 'unknown'
            ]);
            $this->sendErrorResponse('Failed to regenerate response', 500);
        }
    }    /**
     * 获取可用的AI模型
     */
    public function getModels(): void
    {
        try {
            $this->sendSuccessResponse([
                'models' => $this->aiConfig['available_models'] ?? [
                    'gpt-3.5-turbo' => ['name' => 'GPT-3.5 Turbo', 'max_tokens' => 4096],
                    'gpt-4' => ['name' => 'GPT-4', 'max_tokens' => 8192],
                    'claude-3' => ['name' => 'Claude 3', 'max_tokens' => 8192]
                ],
                'default_model' => $this->aiConfig['default_model'] ?? 'gpt-3.5-turbo'
            ]);

        } catch (Exception $e) {
            $this->monitor->logError('Get models failed', [
                'error' => $e->getMessage()
            ]);
            $this->sendErrorResponse('Failed to get models', 500);
        }
    }

    /**
     * 创建新对话
     */
    private function createConversation(int $userId, string $title): int
    {
        $db = $this->getDatabase();
        
        // 生成对话标题（取消息的前50个字符）
        $conversationTitle = mb_substr($title, 0, 50) . (mb_strlen($title) > 50 ? '...' : '');
        
        $stmt = $db->prepare("
            INSERT INTO conversations (user_id, title, created_at, updated_at) 
            VALUES (?, ?, NOW(), NOW())
        ");        $stmt->execute([$userId, $conversationTitle]);
        
        return (int) $db->lastInsertId();
    }    /**
     * 获取对话信息
     */
    private function getConversationById(int $conversationId, int $userId): ?array
    {
        $db = $this->getDatabase();
        $stmt = $db->prepare("
            SELECT * FROM conversations 
            WHERE id = ? AND user_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$conversationId, $userId]);
        
        return $stmt->fetch() ?: null;
    }

    /**
     * 保存消息
     */
    private function saveMessage(int $conversationId, string $role, string $content, ?int $userId, array $metadata = []): int
    {
        $db = $this->getDatabase();
        $stmt = $db->prepare("
            INSERT INTO messages (conversation_id, user_id, role, content, metadata, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $conversationId,
            $userId,
            $role,
            $content,
            json_encode($metadata)        ]);
        
        return (int) $db->lastInsertId();
    }

    /**
     * 更新对话时间
     */
    private function updateConversation(int $conversationId): void
    {
        $db = $this->getDatabase();
        $stmt = $db->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$conversationId]);
    }

    /**
     * 获取对话历史
     */
    private function getConversationHistory(int $conversationId, int $limit = 10, ?int $upToMessageId = null): array
    {
        $db = $this->getDatabase();
        
        $sql = "
            SELECT role, content 
            FROM messages 
            WHERE conversation_id = ? AND deleted_at IS NULL
        ";
        $params = [$conversationId];
        
        if ($upToMessageId) {
            $sql .= " AND id <= ?";
            $params[] = $upToMessageId;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $messages = $stmt->fetchAll();
        return array_reverse($messages); // 返回正序
    }

    /**
     * 获取对话消息（分页）
     */
    private function getConversationMessages(int $conversationId, array $pagination): array
    {
        $db = $this->getDatabase();
        
        // 获取消息总数
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM messages 
            WHERE conversation_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$conversationId]);
        $total = $stmt->fetch()['total'];

        // 获取消息列表
        $stmt = $db->prepare("
            SELECT * FROM messages 
            WHERE conversation_id = ? AND deleted_at IS NULL
            ORDER BY created_at ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$conversationId, $pagination['limit'], $pagination['offset']]);
        $messages = $stmt->fetchAll();

        return $this->buildPaginatedResponse($messages, $total, $pagination);
    }    /**
     * 调用AI API
     */
    private function callAiApi(array $history, string $model, bool $stream = false): array
    {
        try {
            $startTime = microtime(true);
            
            // 使用DeepSeek API服务
            $deepSeekService = new DeepSeekApiService();
            
            // 将历史消息转换为DeepSeek API需要的格式
            $historyMessages = [];
            foreach ($history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $historyMessages[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            }
            
            // 构建上下文
            $context = [
                'history' => $historyMessages,
                'temperature' => $this->aiConfig['temperature'] ?? 0.7,
                'max_tokens' => $this->aiConfig['max_tokens'] ?? 2000,
                'stream' => $stream
            ];
            
            // 获取最后一条用户消息
            $userMessage = '';
            foreach (array_reverse($history) as $msg) {
                if (isset($msg['role']) && $msg['role'] === 'user' && isset($msg['content'])) {
                    $userMessage = $msg['content'];
                    break;
                }
            }
            
            if (empty($userMessage)) {
                return ['success' => false, 'error' => 'No user message found'];
            }
            
            // 发送消息到DeepSeek API
            $apiResponse = $deepSeekService->sendMessage($userMessage, $context);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            // 处理API响应
            if (isset($apiResponse['choices']) && !empty($apiResponse['choices'])) {
                $content = $apiResponse['choices'][0]['message']['content'] ?? '';
                $tokensUsed = $apiResponse['usage']['total_tokens'] ?? 0;
                
                // 记录API调用（如果方法存在）
                if (method_exists($this->monitor, 'recordApiCall')) {
                    try {
                        $this->monitor->recordApiCall('ai_chat');
                    } catch (Exception $e) {
                        // 忽略监控记录错误
                    }
                }
                
                return [
                    'success' => true,
                    'content' => $content,
                    'tokens_used' => $tokensUsed,
                    'response_time' => $responseTime
                ];
            } else {
                $error = 'Invalid API response format';
                if (isset($apiResponse['error'])) {
                    $error = $apiResponse['error']['message'] ?? $apiResponse['error'];
                }
                
                return [
                    'success' => false,
                    'error' => $error
                ];
            }

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
