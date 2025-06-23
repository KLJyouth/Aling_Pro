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

use AlingAi\Services\Interfaces\AIServiceInterface;
use AlingAi\Core\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use PDO;
use Throwable;

/**
 * 聊天服务
 * 处理聊天会话管理、消息存储和AI交互
 */
class ChatService
{
    private AIServiceInterface $aiService;
    private DatabaseManager $dbManager;
    private LoggerInterface $logger;

    public function __construct(
        AIServiceInterface $aiService,
        DatabaseManager $dbManager,
        ?LoggerInterface $logger = null
    ) {
        $this->aiService = $aiService;
        $this->dbManager = $dbManager;
        $this->logger = $logger ?? $this->createDefaultLogger();
    }

    /**
     * 发送消息并获取AI响应
     */
    public function sendMessage(int $userId, string $message, ?int $conversationId = null, array $options = []): array
    {
        try {
            $this->logger->info('开始处理用户消息', [
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'message_length' => strlen($message)
            ]);

            // 获取或创建会话
            if ($conversationId === null) {
                $conversationId = $this->createConversation($userId);
            } else {
                $this->validateConversationAccess($userId, $conversationId);
            }

            // 保存用户消息
            $userMessageId = $this->saveMessage($conversationId, $userId, 'user', $message);

            // 获取会话历史
            $history = $this->getConversationHistory($conversationId, $options['max_history'] ?? 10);

            // 调用AI服务
            $aiResponse = $this->aiService->getCompletion($history, $options);

            if (!$aiResponse['success']) {
                throw new \Exception($aiResponse['error'] ?? 'AI服务调用失败');
            }

            // 保存AI响应
            $aiMessageId = $this->saveMessage($conversationId, 0, 'assistant', $aiResponse['data']['content']);

            // 更新使用统计
            $this->updateUsageStats($conversationId, $aiResponse['usage'] ?? []);

            $this->logger->info('消息处理完成', [
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'user_message_id' => $userMessageId,
                'ai_message_id' => $aiMessageId
            ]);

            return [
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'message_id' => $aiMessageId,
                    'content' => $aiResponse['data']['content'],
                    'model' => $aiResponse['data']['model'] ?? '',
                    'usage' => $aiResponse['usage'] ?? []
                ]
            ];

        } catch (\Exception $e) {
            $this->logger->error('消息处理失败', [
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 创建新会话
     */
    public function createConversation(int $userId, string $title = ''): int
    {
        $sql = "INSERT INTO conversations (user_id, title, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
        $this->dbManager->execute($sql, [$userId, $title]);
        return (int) $this->dbManager->lastInsertId();
    }

    /**
     * 获取用户会话列表
     */
    public function getUserConversations(int $userId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT c.*, 
                       COUNT(m.id) as message_count,
                       MAX(m.created_at) as last_message_time
                FROM conversations c
                LEFT JOIN messages m ON c.id = m.conversation_id
                WHERE c.user_id = ?
                GROUP BY c.id
                ORDER BY c.updated_at DESC
                LIMIT ? OFFSET ?";
        
        $conversations = $this->dbManager->query($sql, [$userId, $limit, $offset])->fetchAll();
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM conversations WHERE user_id = ?";
        $total = $this->dbManager->query($countSql, [$userId])->fetch()['total'];

        return [
            'conversations' => $conversations,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * 获取会话消息历史
     */
    public function getConversationHistory(int $conversationId, int $limit = 50): array
    {
        $this->validateConversationAccess(0, $conversationId); // 0表示系统访问

        $sql = "SELECT role, content 
                FROM messages 
                WHERE conversation_id = ? 
                ORDER BY created_at ASC 
                LIMIT ?";
        
        $messages = $this->dbManager->query($sql, [$conversationId, $limit])->fetchAll();
        
        return array_map(function($msg) {
            return [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }, $messages);
    }

    /**
     * 删除会话
     */
    public function deleteConversation(int $userId, int $conversationId): bool
    {
        $this->validateConversationAccess($userId, $conversationId);

        $this->dbManager->beginTransaction();
        try {
            // 删除消息
            $this->dbManager->execute(
                "DELETE FROM messages WHERE conversation_id = ?", 
                [$conversationId]
            );
            
            // 删除使用统计
            $this->dbManager->execute(
                "DELETE FROM usage_stats WHERE conversation_id = ?", 
                [$conversationId]
            );
            
            // 删除会话
            $this->dbManager->execute(
                "DELETE FROM conversations WHERE id = ? AND user_id = ?", 
                [$conversationId, $userId]
            );

            $this->dbManager->commit();
            return true;

        } catch (\Exception $e) {
            $this->dbManager->rollback();
            throw $e;
        }
    }

    /**
     * 获取AI服务健康状态
     */
    public function getAIHealthStatus(): array
    {
        return $this->aiService->healthCheck();
    }

    /**
     * 保存消息到数据库
     */
    private function saveMessage(int $conversationId, int $userId, string $role, string $content): int
    {
        $sql = "INSERT INTO messages (conversation_id, user_id, role, content, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->dbManager->execute($sql, [$conversationId, $userId, $role, $content]);
        return (int) $this->dbManager->lastInsertId();
    }

    /**
     * 验证会话访问权限
     */
    private function validateConversationAccess(int $userId, int $conversationId): void
    {
        $sql = "SELECT user_id FROM conversations WHERE id = ?";
        $result = $this->dbManager->query($sql, [$conversationId])->fetch();
        
        if (!$result) {
            throw new \Exception('会话不存在');
        }
        
        if ($userId !== 0 && $result['user_id'] != $userId) {
            throw new \Exception('无权访问此会话');
        }
    }

    /**
     * 更新使用统计
     */
    private function updateUsageStats(int $conversationId, array $usage): void
    {
        if (empty($usage)) {
            return;
        }

        $sql = "INSERT INTO usage_stats (conversation_id, prompt_tokens, completion_tokens, total_tokens, created_at) 
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                prompt_tokens = prompt_tokens + VALUES(prompt_tokens),
                completion_tokens = completion_tokens + VALUES(completion_tokens),
                total_tokens = total_tokens + VALUES(total_tokens),
                updated_at = NOW()";

        $this->dbManager->execute($sql, [
            $conversationId,
            $usage['prompt_tokens'] ?? 0,
            $usage['completion_tokens'] ?? 0,
            $usage['total_tokens'] ?? 0
        ]);
    }

    /**
     * 创建默认日志记录器
     */
    private function createDefaultLogger(): LoggerInterface
    {
        return new class implements LoggerInterface {
            public function emergency($message, array $context = []): void {}
            public function alert($message, array $context = []): void {}
            public function critical($message, array $context = []): void {}
            public function error($message, array $context = []): void {}
            public function warning($message, array $context = []): void {}
            public function notice($message, array $context = []): void {}
            public function info($message, array $context = []): void {}
            public function debug($message, array $context = []): void {}
            public function log($level, $message, array $context = []): void {}
        };
    }
}
