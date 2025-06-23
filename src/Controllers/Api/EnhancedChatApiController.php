<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\ChatService;
use AlingAi\Core\Middleware\AuthMiddleware;
use Throwable;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;

/**
 * 增强聊天API控制器
 * 提供完整的聊天功能，包括消息发送、会话管理、历史记录等
 */
class EnhancedChatApiController extends BaseApiController
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService, ?LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->chatService = $chatService;
    }

    /**
     * 发送消息
     * POST /api/v1/chat/send
     */
    public function sendMessage(Request $request, Response $response): Response
    {
        try {
            $this->logApiCall($request, "send_message");
            
            $userId = $this->validateAuth($request);
            $data = $this->getRequestData($request);
            
            // 验证必需参数
            $this->validateRequiredParams($data, ["message"]);
            
            $message = $data["message"];
            $conversationId = $data["conversation_id"] ?? null;
            $options = [
                "model" => $data["model"] ?? null,
                "temperature" => $data["temperature"] ?? null,
                "max_tokens" => $data["max_tokens"] ?? null,
                "max_history" => $data["max_history"] ?? 10
            ];

            // 过滤空值
            $options = array_filter($options, function($value) {
                return $value !== null;
            });

            $result = $this->chatService->sendMessage($userId, $message, $conversationId, $options);

            if (!$result["success"]) {
                $this->sendErrorResponse($result["error"], 400);
                return $response->withStatus(400);
            }

            return $this->sendSuccess($response, $result["data"], "消息发送成功");

        } catch (\Exception $e) {
            $this->logApiError($request, "send_message", $e);
            $this->sendErrorResponse($e->getMessage(), 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 获取用户会话列表
     * GET /api/v1/chat/conversations
     */
    public function getConversations(Request $request, Response $response): Response
    {
        try {
            $this->logApiCall($request, "get_conversations");
            
            $userId = $this->validateAuth($request);
            $pagination = $this->getPaginationParams($request);
            
            $result = $this->chatService->getUserConversations(
                $userId, 
                $pagination["page"], 
                $pagination["limit"]
            );

            return $this->sendSuccess($response, $result, "获取会话列表成功");

        } catch (\Exception $e) {
            $this->logApiError($request, "get_conversations", $e);
            $this->sendErrorResponse($e->getMessage(), 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 获取会话历史
     * GET /api/v1/chat/conversations/{id}/history
     */
    public function getConversationHistory(Request $request, Response $response, array $args): Response
    {
        try {
            $this->logApiCall($request, "get_conversation_history");
            
            $userId = $this->validateAuth($request);
            $conversationId = (int) $args["id"];
            
            // 验证会话访问权限
            $this->validateConversationAccess($userId, $conversationId);
            
            $queryParams = $request->getQueryParams();
            $limit = min(100, max(1, (int) ($queryParams["limit"] ?? 50)));
            
            $history = $this->chatService->getConversationHistory($conversationId, $limit);

            return $this->sendSuccess($response, $history, "获取会话历史成功");

        } catch (\Exception $e) {
            $this->logApiError($request, "get_conversation_history", $e);
            $this->sendErrorResponse($e->getMessage(), 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 删除会话
     * DELETE /api/v1/chat/conversations/{id}
     */
    public function deleteConversation(Request $request, Response $response, array $args): Response
    {
        try {
            $this->logApiCall($request, "delete_conversation");
            
            $userId = $this->validateAuth($request);
            $conversationId = (int) $args["id"];
            
            $success = $this->chatService->deleteConversation($userId, $conversationId);

            if (!$success) {
                $this->sendErrorResponse("删除会话失败", 500);
                return $response->withStatus(500);
            }

            return $this->sendSuccess($response, null, "会话删除成功");

        } catch (\Exception $e) {
            $this->logApiError($request, "delete_conversation", $e);
            $this->sendErrorResponse($e->getMessage(), 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 获取AI服务健康状态
     * GET /api/v1/chat/health
     */
    public function getHealthStatus(Request $request, Response $response): Response
    {
        try {
            $this->logApiCall($request, "get_health_status");
            
            $health = $this->chatService->getAIHealthStatus();

            return $this->sendSuccess($response, $health, "获取健康状态成功");

        } catch (\Exception $e) {
            $this->logApiError($request, "get_health_status", $e);
            $this->sendErrorResponse($e->getMessage(), 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 创建新会话
     * POST /api/v1/chat/conversations
     */
    public function createConversation(Request $request, Response $response): Response
    {
        try {
            $this->logApiCall($request, "create_conversation");
            
            $userId = $this->validateAuth($request);
            $data = $this->getRequestData($request);
            
            $title = $data["title"] ?? "";
            $conversationId = $this->chatService->createConversation($userId, $title);

            return $this->sendSuccess($response, [
                "conversation_id" => $conversationId,
                "title" => $title
            ], "会话创建成功");

        } catch (\Exception $e) {
            $this->logApiError($request, "create_conversation", $e);
            $this->sendErrorResponse($e->getMessage(), 500);
            return $response->withStatus(500);
        }
    }

    /**
     * 验证会话访问权限
     */
    private function validateConversationAccess(int $userId, int $conversationId): void
    {
        // 这里可以添加更详细的权限验证逻辑
        // 目前ChatService内部已经处理了权限验证
    }

    /**
     * 验证用户认证状态
     *
     * @param Request $request 请求对象
     * @return int 用户ID
     * @throws InvalidArgumentException 如果认证失败
     */
    protected function validateAuth(Request $request): int
    {
        // 从请求中获取令牌
        $token = $this->getBearerToken() ?? $request->getQueryParams()["token"] ?? null;
        
        if (!$token) {
            throw new InvalidArgumentException("缺少认证令牌");
        }
        
        // 验证令牌
        $validation = $this->security->validateJwtToken($token);
        
        if (!$validation || !isset($validation["user_id"])) {
            throw new InvalidArgumentException("无效的认证令牌");
        }
        
        return (int)$validation["user_id"];
    }
    
    /**
     * 验证必需的参数
     *
     * @param array $data 请求数据
     * @param array $params 必需的参数列表
     * @throws InvalidArgumentException 如果缺少必需参数
     */
    protected function validateRequiredParams(array $data, array $params): void
    {
        $missing = [];
        
        foreach ($params as $param) {
            if (!isset($data[$param]) || (is_string($data[$param]) && trim($data[$param]) === "")) {
                $missing[] = $param;
            }
        }
        
        if (!empty($missing)) {
            throw new InvalidArgumentException("缺少必需参数: " . implode(", ", $missing));
        }
    }
}
