<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 聊天控制器
 * 处理聊天和对话相关功能
 */
class ChatController extends BaseController
{
    /**
     * 聊天界面
     */    public function chatInterface(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            
            // 返回JSON响应而不是模板渲染（稍后可以实现模板引擎）
            return $this->successResponse($response, [
                'user' => $user,
                'title' => '智能对话',
                'page' => 'chat'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('渲染聊天界面失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return $this->errorResponse($response, '加载聊天界面失败', 500);
        }
    }

    /**
     * 获取对话列表
     */
    public function getConversations(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);

            $conversations = $user->conversations()
                ->with('messages')
                ->orderBy('updated_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($response, [
                'conversations' => $conversations->items(),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'total_pages' => $conversations->lastPage(),
                    'total_items' => $conversations->total(),
                    'per_page' => $conversations->perPage()
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取对话列表失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return $this->errorResponse($response, '获取对话列表失败', 500);
        }
    }

    /**
     * 创建新对话
     */
    public function createConversation(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            $data = json_decode($request->getBody()->getContents(), true);

            $conversation = $user->conversations()->create([
                'title' => $data['title'] ?? '新对话',
                'context' => $data['context'] ?? [],
                'settings' => $data['settings'] ?? []
            ]);

            return $this->successResponse($response, $conversation, '对话创建成功', 201);
        } catch (\Exception $e) {
            $this->logger->error('创建对话失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'data' => $data ?? []
            ]);

            return $this->errorResponse($response, '创建对话失败', 500);
        }
    }

    /**
     * 获取单个对话
     */
    public function getConversation(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            $conversationId = $request->getAttribute('id');

            $conversation = $user->conversations()
                ->with('messages')
                ->findOrFail($conversationId);

            return $this->successResponse($response, $conversation);
        } catch (\Exception $e) {
            $this->logger->error('获取对话失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'conversation_id' => $conversationId ?? null
            ]);

            return $this->errorResponse($response, '对话不存在', 404);
        }
    }

    /**
     * 更新对话
     */
    public function updateConversation(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            $conversationId = $request->getAttribute('id');
            $data = json_decode($request->getBody()->getContents(), true);

            $conversation = $user->conversations()->findOrFail($conversationId);
            
            $conversation->update([
                'title' => $data['title'] ?? $conversation->title,
                'context' => $data['context'] ?? $conversation->context,
                'settings' => $data['settings'] ?? $conversation->settings
            ]);

            return $this->successResponse($response, $conversation, '对话更新成功');
        } catch (\Exception $e) {
            $this->logger->error('更新对话失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'conversation_id' => $conversationId ?? null,
                'data' => $data ?? []
            ]);

            return $this->errorResponse($response, '更新对话失败', 500);
        }
    }

    /**
     * 删除对话
     */
    public function deleteConversation(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            $conversationId = $request->getAttribute('id');

            $conversation = $user->conversations()->findOrFail($conversationId);
            $conversation->delete();

            return $this->successResponse($response, null, '对话删除成功');
        } catch (\Exception $e) {
            $this->logger->error('删除对话失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'conversation_id' => $conversationId ?? null
            ]);

            return $this->errorResponse($response, '删除对话失败', 500);
        }
    }

    /**
     * 发送消息
     */
    public function sendMessage(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            $conversationId = $request->getAttribute('id');
            $data = json_decode($request->getBody()->getContents(), true);

            $conversation = $user->conversations()->findOrFail($conversationId);
            
            // 创建用户消息
            $userMessage = $conversation->messages()->create([
                'user_id' => $user->id,
                'content' => $data['content'],
                'type' => 'user',
                'metadata' => $data['metadata'] ?? []
            ]);

            // 这里应该调用AI服务处理消息并生成回复
            // 暂时返回模拟回复
            $aiResponse = $this->generateAIResponse($data['content']);
            
            $aiMessage = $conversation->messages()->create([
                'user_id' => null, // AI消息
                'content' => $aiResponse,
                'type' => 'assistant',
                'metadata' => [
                    'model' => 'gpt-3.5-turbo',
                    'timestamp' => time()
                ]
            ]);

            // 更新对话的最后活动时间
            $conversation->touch();

            return $this->successResponse($response, [
                'user_message' => $userMessage,
                'ai_message' => $aiMessage
            ], '消息发送成功');
        } catch (\Exception $e) {
            $this->logger->error('发送消息失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'conversation_id' => $conversationId ?? null,
                'data' => $data ?? []
            ]);

            return $this->errorResponse($response, '发送消息失败', 500);
        }
    }

    /**
     * 获取对话消息
     */
    public function getMessages(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            $conversationId = $request->getAttribute('id');
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 50), 100);

            $conversation = $user->conversations()->findOrFail($conversationId);
            
            $messages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($response, [
                'messages' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'total_pages' => $messages->lastPage(),
                    'total_items' => $messages->total(),
                    'per_page' => $messages->perPage()
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('获取消息失败', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'conversation_id' => $conversationId ?? null
            ]);

            return $this->errorResponse($response, '获取消息失败', 500);
        }
    }

    /**
     * 生成AI回复（模拟）
     */
    private function generateAIResponse(string $userMessage): string
    {
        // 这里应该集成真实的AI服务
        // 暂时返回模拟回复
        $responses = [
            '我理解您的问题，让我来帮助您解决。',
            '这是一个很好的问题，我会尽力为您提供准确的答案。',
            '根据您的描述，我建议您考虑以下几个方面...',
            '感谢您的提问，我正在为您分析相关信息。',
            '这个问题很有意思，让我详细为您分析一下。'
        ];

        return $responses[array_rand($responses)] . "\n\n您的问题：「" . mb_substr($userMessage, 0, 50) . "」\n\n我正在持续学习中，如果回答不够准确，请多多包涵。";
    }
}
