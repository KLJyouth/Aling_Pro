<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\Models\Conversation;
use AlingAi\Models\User;
use AlingAi\Services\{DatabaseService, CacheService};
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

/**
 * 对话管理控制器
 * 处理对话相关的所有操作
 */
class ConversationController extends BaseController
{
    private CacheService $cacheService;

    public function __construct(
        DatabaseService $db,
        CacheService $cache
    ) {
        parent::__construct($db, $cache);
        $this->cacheService = $cache;
    }

    /**
     * 获取对话列表
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $userId = $this->getCurrentUserId($request);
            $params = $request->getQueryParams();
            
            $page = (int)($params['page'] ?? 1);
            $limit = (int)($params['limit'] ?? 20);
            $status = $params['status'] ?? null;
            
            $cacheKey = "conversations_list_{$userId}_{$page}_{$limit}_{$status}";
            $result = $this->cacheService->get($cacheKey);
            
            if ($result === null) {
                $conversationModel = new Conversation();
                $query = $conversationModel->query()
                    ->where('user_id', $userId)
                    ->orderBy('updated_at', 'DESC');
                
                if ($status) {
                    $query->where('status', $status);
                }
                
                $result = $query->paginate($page, $limit);
                $this->cacheService->set($cacheKey, $result, 300);
            }
            
            return $this->successResponse($this->createResponse(), $result, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '获取对话列表失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取对话详情
     */
    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        try {
            $conversationId = (int)$args['id'];
            $userId = $this->getCurrentUserId($request);
            
            $conversationModel = new Conversation();
            $conversation = $conversationModel->query()
                ->where('id', $conversationId)
                ->where('user_id', $userId)
                ->first();
            
            if (!$conversation) {
                return $this->errorResponse($this->createResponse(), '对话不存在', 404);
            }
            
            return $this->successResponse($this->createResponse(), $conversation, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '获取对话详情失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 创建新对话
     */
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $userId = $this->getCurrentUserId($request);
            $data = json_decode($request->getBody()->getContents(), true);
            
            $conversationModel = new Conversation();
            $conversationData = [
                'user_id' => $userId,
                'title' => $data['title'] ?? '新对话',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $conversationId = $conversationModel->query()->insert($conversationData);
            
            // 清除缓存
            $this->cacheService->delete("conversations_list_{$userId}_*");
            
            $conversation = $conversationModel->query()->where('id', $conversationId)->first();
            
            return $this->successResponse($this->createResponse(), $conversation, '对话创建成功');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '创建对话失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 更新对话
     */
    public function update(ServerRequestInterface $request, array $args): ResponseInterface
    {
        try {
            $conversationId = (int)$args['id'];
            $userId = $this->getCurrentUserId($request);
            $data = json_decode($request->getBody()->getContents(), true);
            
            $conversationModel = new Conversation();
            
            // 检查对话是否存在且属于当前用户
            $conversation = $conversationModel->query()
                ->where('id', $conversationId)
                ->where('user_id', $userId)
                ->first();
            
            if (!$conversation) {
                return $this->errorResponse($this->createResponse(), '对话不存在', 404);
            }
            
            $updateData = [
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
            }
            
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            
            $conversationModel->query()
                ->where('id', $conversationId)
                ->update($updateData);
            
            // 清除缓存
            $this->cacheService->delete("conversations_list_{$userId}_*");
            
            $updatedConversation = $conversationModel->query()->where('id', $conversationId)->first();
            
            return $this->successResponse($this->createResponse(), $updatedConversation, '对话更新成功');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '更新对话失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 删除对话
     */
    public function delete(ServerRequestInterface $request, array $args): ResponseInterface
    {
        try {
            $conversationId = (int)$args['id'];
            $userId = $this->getCurrentUserId($request);
            
            $conversationModel = new Conversation();
            
            // 检查对话是否存在且属于当前用户
            $conversation = $conversationModel->query()
                ->where('id', $conversationId)
                ->where('user_id', $userId)
                ->first();
            
            if (!$conversation) {
                return $this->errorResponse($this->createResponse(), '对话不存在', 404);
            }
            
            $conversationModel->query()->where('id', $conversationId)->delete();
            
            // 清除缓存
            $this->cacheService->delete("conversations_list_{$userId}_*");
            
            return $this->successResponse($this->createResponse(), null, '对话删除成功');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '删除对话失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取对话统计
     */
    public function statistics(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $userId = $this->getCurrentUserId($request);
            
            $conversationModel = new Conversation();
            
            $stats = [
                'total_conversations' => $conversationModel->query()->where('user_id', $userId)->count(),
                'active_conversations' => $conversationModel->query()->where('user_id', $userId)->where('status', 'active')->count(),
                'completed_conversations' => $conversationModel->query()->where('user_id', $userId)->where('status', 'completed')->count(),
                'today_conversations' => $conversationModel->query()
                    ->where('user_id', $userId)
                    ->whereDate('created_at', date('Y-m-d'))
                    ->count()
            ];
            
            return $this->successResponse($this->createResponse(), $stats, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '获取统计信息失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 批量操作对话
     */
    public function batchAction(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $userId = $this->getCurrentUserId($request);
            $data = json_decode($request->getBody()->getContents(), true);
            
            $action = $data['action'] ?? '';
            $conversationIds = $data['conversation_ids'] ?? [];
            
            if (empty($conversationIds) || !is_array($conversationIds)) {
                return $this->errorResponse($this->createResponse(), '请选择要操作的对话', 400);
            }
            
            $conversationModel = new Conversation();
            $successCount = 0;
            
            foreach ($conversationIds as $conversationId) {
                // 检查对话是否属于当前用户
                $conversation = $conversationModel->query()
                    ->where('id', $conversationId)
                    ->where('user_id', $userId)
                    ->first();
                
                if ($conversation) {
                    switch ($action) {
                        case 'delete':
                            $conversationModel->query()->where('id', $conversationId)->delete();
                            $successCount++;
                            break;
                        case 'archive':
                            $conversationModel->query()
                                ->where('id', $conversationId)
                                ->update(['status' => 'archived', 'updated_at' => date('Y-m-d H:i:s')]);
                            $successCount++;
                            break;
                        case 'activate':
                            $conversationModel->query()
                                ->where('id', $conversationId)
                                ->update(['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')]);
                            $successCount++;
                            break;
                    }
                }
            }
            
            // 清除缓存
            $this->cacheService->delete("conversations_list_{$userId}_*");
            
            return $this->successResponse($this->createResponse(), [
                'success_count' => $successCount,
                'total_count' => count($conversationIds)
            ], "成功{$action}了{$successCount}个对话");
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '批量操作失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 搜索对话
     */
    public function search(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $userId = $this->getCurrentUserId($request);
            $params = $request->getQueryParams();
            
            $keyword = $params['q'] ?? '';
            $page = (int)($params['page'] ?? 1);
            $limit = (int)($params['limit'] ?? 20);
            
            if (empty($keyword)) {
                return $this->errorResponse($this->createResponse(), '搜索关键词不能为空', 400);
            }
            
            $conversationModel = new Conversation();
            $result = $conversationModel->query()
                ->where('user_id', $userId)
                ->where('title', 'LIKE', "%{$keyword}%")
                ->orderBy('updated_at', 'DESC')
                ->paginate($page, $limit);
            
            return $this->successResponse($this->createResponse(), $result, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '搜索对话失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取当前用户ID
     */
    private function getCurrentUserId(ServerRequestInterface $request): int
    {
        // 从请求中获取用户ID，这里假设从JWT token或session中获取
        // 实际实现需要根据认证机制调整
        $user = $request->getAttribute('user');
        return $user['id'] ?? 1; // 临时返回1，实际应该抛出异常
    }

    /**
     * 创建响应对象
     */
    private function createResponse(): ResponseInterface
    {
        return new Response();
    }
}
