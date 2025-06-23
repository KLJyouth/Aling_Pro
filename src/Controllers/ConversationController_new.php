<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\Models\Conversation;
use AlingAi\Models\User;
use AlingAi\Services\{DatabaseService, CacheService};
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * 对话管理控制器
 * 处理对话相关的所有操作
 */
class ConversationController extends BaseController
{
    private CacheService $cacheService;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        DatabaseService $db,
        CacheService $cache,
        ResponseFactoryInterface $responseFactory
    ) {
        parent::__construct($db, $cache);
        $this->cacheService = $cache;
        $this->responseFactory = $responseFactory;
    }

    /**
     * 获取对话列表
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $search = $params['search'] ?? '';
            $userId = $params['user_id'] ?? '';
            $status = $params['status'] ?? '';
            $type = $params['type'] ?? '';

            // 缓存键
            $cacheKey = "conversations_list_" . md5(serialize($params));
            
            // 尝试从缓存获取
            $result = $this->cacheService->get($cacheKey);
            if ($result !== null) {
                return $this->successResponse($response, $result);
            }

            // 构建查询条件
            $conditions = [];
            if (!empty($userId)) {
                $conditions['user_id'] = $userId;
            }
            if (!empty($status)) {
                $conditions['status'] = $status;
            }
            if (!empty($type)) {
                $conditions['type'] = $type;
            }

            // 获取对话列表
            $conversationModel = new Conversation();
            $offset = ($page - 1) * $limit;
            
            $conversations = $conversationModel->select($conditions, '*', $limit, $offset, 'updated_at DESC');
            $total = $conversationModel->count($conditions);
            
            // 计算分页信息
            $lastPage = ceil($total / $limit);
            $from = $total > 0 ? $offset + 1 : 0;
            $to = min($offset + $limit, $total);

            $result = [
                'data' => $conversations,
                'meta' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to
                ]
            ];

            // 缓存结果5分钟
            $this->cacheService->set($cacheKey, $result, 300);

            return $this->successResponse($response, $result);
        } catch (\Exception $e) {
            $this->logger->error('获取对话列表失败', [
                'error' => $e->getMessage(),
                'params' => $params ?? []
            ]);

            return $this->errorResponse($response, '获取对话列表失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取单个对话详情
     */
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        try {
            $conversationId = (int)$request->getAttribute('id');
            
            // 缓存键
            $cacheKey = "conversation_detail_{$conversationId}";
            
            // 尝试从缓存获取
            $conversation = $this->cacheService->get($cacheKey);
            if ($conversation === null) {
                $conversationModel = new Conversation();
                $conversation = $conversationModel->find($conversationId);

                if (!$conversation) {
                    return $this->errorResponse($response, '对话不存在', 404);
                }

                // 缓存对话信息10分钟
                $this->cacheService->set($cacheKey, $conversation, 600);
            }

            // 更新阅读次数
            $conversationModel = new Conversation();
            $readCount = ($conversation['read_count'] ?? 0) + 1;
            $conversationModel->update(['read_count' => $readCount, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $conversationId]);

            return $this->successResponse($response, $conversation);
        } catch (\Exception $e) {
            $this->logger->error('获取对话详情失败', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId ?? null
            ]);

            return $this->errorResponse($response, '获取对话详情失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 创建新对话
     */
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        try {
            $data = $this->getJsonData($request);
            $userId = $request->getAttribute('user_id');

            // 验证必要字段
            if (empty($data['content'])) {
                return $this->errorResponse($response, '对话内容不能为空', 400);
            }

            // 验证用户是否存在
            $userModel = new User();
            if (!$userModel->find($data['user_id'] ?? $userId)) {
                return $this->errorResponse($response, '指定的用户不存在', 400);
            }

            // 设置默认值
            if (empty($data['user_id'])) {
                $data['user_id'] = $userId;
            }
            if (empty($data['title'])) {
                $data['title'] = mb_substr(strip_tags($data['content']), 0, 50) . '...';
            }
            if (empty($data['type'])) {
                $data['type'] = 'chat';
            }
            if (empty($data['status'])) {
                $data['status'] = 'active';
            }

            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            // 创建对话
            $conversationModel = new Conversation();
            $conversationId = $conversationModel->insert($data);

            // 获取创建的对话
            $conversation = $conversationModel->find($conversationId);

            // 清除相关缓存
            $this->cacheService->deletePattern('conversations_list_*');

            return $this->successResponse($response, $conversation, '对话创建成功', 201);
        } catch (\Exception $e) {
            $this->logger->error('创建对话失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);

            return $this->errorResponse($response, '创建对话失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 更新对话信息
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        try {
            $conversationId = (int)$request->getAttribute('id');
            $data = $this->getJsonData($request);
            $userId = $request->getAttribute('user_id');

            $conversationModel = new Conversation();
            $conversation = $conversationModel->find($conversationId);

            if (!$conversation) {
                return $this->errorResponse($response, '对话不存在', 404);
            }

            // 权限检查：只有对话的创建者或管理员可以修改
            $userRole = $request->getAttribute('user_role');
            if ($conversation['user_id'] !== $userId && $userRole !== 'admin') {
                return $this->errorResponse($response, '没有权限修改此对话', 403);
            }

            // 可更新的字段
            $updateableFields = [
                'title', 'content', 'type', 'status', 'summary', 
                'tags', 'metadata', 'settings', 'rating'
            ];

            $updateData = [];
            foreach ($updateableFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $conversationModel->update($updateData, ['id' => $conversationId]);

                // 清除相关缓存
                $this->cacheService->delete("conversation_detail_{$conversationId}");
                $this->cacheService->deletePattern('conversations_list_*');
            }

            // 获取更新后的对话
            $conversation = $conversationModel->find($conversationId);

            return $this->successResponse($response, $conversation, '对话信息更新成功');
        } catch (\Exception $e) {
            $this->logger->error('更新对话失败', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId ?? null,
                'data' => $data ?? []
            ]);

            return $this->errorResponse($response, '更新对话失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 删除对话
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        try {
            $conversationId = (int)$request->getAttribute('id');
            $userId = $request->getAttribute('user_id');

            $conversationModel = new Conversation();
            $conversation = $conversationModel->find($conversationId);

            if (!$conversation) {
                return $this->errorResponse($response, '对话不存在', 404);
            }

            // 权限检查：只有对话的创建者或管理员可以删除
            $userRole = $request->getAttribute('user_role');
            if ($conversation['user_id'] !== $userId && $userRole !== 'admin') {
                return $this->errorResponse($response, '没有权限删除此对话', 403);
            }

            $conversationModel->delete(['id' => $conversationId]);

            // 清除相关缓存
            $this->cacheService->delete("conversation_detail_{$conversationId}");
            $this->cacheService->deletePattern('conversations_list_*');

            return $this->successResponse($response, null, '对话删除成功');
        } catch (\Exception $e) {
            $this->logger->error('删除对话失败', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId ?? null
            ]);

            return $this->errorResponse($response, '删除对话失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取对话统计信息
     */
    public function statistics(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        try {
            $params = $request->getQueryParams();
            $userId = $params['user_id'] ?? null;
            
            $cacheKey = "conversation_stats_" . ($userId ? "user_{$userId}" : 'all');
            
            // 尝试从缓存获取
            $stats = $this->cacheService->get($cacheKey);
            if ($stats === null) {
                $conversationModel = new Conversation();
                
                // 基础统计
                $conditions = $userId ? ['user_id' => $userId] : [];
                
                $stats = [
                    'total_conversations' => $conversationModel->count($conditions),
                    'active_conversations' => $conversationModel->count(array_merge($conditions, ['status' => 'active'])),
                    'completed_conversations' => $conversationModel->count(array_merge($conditions, ['status' => 'completed'])),
                ];

                // 缓存10分钟
                $this->cacheService->set($cacheKey, $stats, 600);
            }

            return $this->successResponse($response, $stats);
        } catch (\Exception $e) {
            $this->logger->error('获取对话统计失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId ?? null
            ]);

            return $this->errorResponse($response, '获取对话统计失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 批量操作对话
     */
    public function batchOperation(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        try {
            $data = $this->getJsonData($request);
            $userId = $request->getAttribute('user_id');
            $userRole = $request->getAttribute('user_role');

            if (empty($data['conversation_ids']) || !is_array($data['conversation_ids'])) {
                return $this->errorResponse($response, '请选择要操作的对话', 400);
            }

            if (empty($data['action'])) {
                return $this->errorResponse($response, '请指定操作类型', 400);
            }

            $conversationIds = $data['conversation_ids'];
            $action = $data['action'];

            $conversationModel = new Conversation();
            $results = [];

            foreach ($conversationIds as $conversationId) {
                try {
                    $conversation = $conversationModel->find($conversationId);
                    if (!$conversation) {
                        $results[$conversationId] = ['success' => false, 'message' => '对话不存在'];
                        continue;
                    }

                    // 权限检查
                    if ($conversation['user_id'] !== $userId && $userRole !== 'admin') {
                        $results[$conversationId] = ['success' => false, 'message' => '没有权限操作此对话'];
                        continue;
                    }

                    switch ($action) {
                        case 'delete':
                            $conversationModel->delete(['id' => $conversationId]);
                            $results[$conversationId] = ['success' => true, 'message' => '删除成功'];
                            break;
                        case 'archive':
                            $conversationModel->update(['status' => 'archived'], ['id' => $conversationId]);
                            $results[$conversationId] = ['success' => true, 'message' => '归档成功'];
                            break;
                        case 'restore':
                            $conversationModel->update(['status' => 'active'], ['id' => $conversationId]);
                            $results[$conversationId] = ['success' => true, 'message' => '恢复成功'];
                            break;
                        default:
                            $results[$conversationId] = ['success' => false, 'message' => '不支持的操作类型'];
                    }
                } catch (\Exception $e) {
                    $results[$conversationId] = ['success' => false, 'message' => $e->getMessage()];
                }
            }

            // 清除相关缓存
            $this->cacheService->deletePattern('conversations_list_*');

            return $this->successResponse($response, $results, '批量操作完成');
        } catch (\Exception $e) {
            $this->logger->error('批量操作失败', [
                'error' => $e->getMessage(),
                'data' => $data ?? []
            ]);

            return $this->errorResponse($response, '批量操作失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 搜索对话
     */
    public function search(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        try {
            $params = $request->getQueryParams();
            $query = trim($params['query'] ?? '');
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $userId = $params['user_id'] ?? null;
            $type = $params['type'] ?? null;

            if (empty($query)) {
                return $this->errorResponse($response, '搜索关键词不能为空', 400);
            }

            $cacheKey = "conversation_search_" . md5(serialize($params));
            
            // 尝试从缓存获取
            $result = $this->cacheService->get($cacheKey);
            if ($result !== null) {
                return $this->successResponse($response, $result);
            }

            // 构建搜索条件
            $conditions = [];
            if ($userId) {
                $conditions['user_id'] = $userId;
            }
            if ($type) {
                $conditions['type'] = $type;
            }

            $conversationModel = new Conversation();
            $offset = ($page - 1) * $limit;

            // 简化的搜索：使用LIKE查询
            $searchConditions = array_merge($conditions, [
                'title LIKE' => "%{$query}%",
                'content LIKE' => "%{$query}%"
            ]);

            $conversations = $conversationModel->select($searchConditions, '*', $limit, $offset, 'updated_at DESC');
            $total = $conversationModel->count($searchConditions);

            // 计算分页信息
            $lastPage = ceil($total / $limit);
            $from = $total > 0 ? $offset + 1 : 0;
            $to = min($offset + $limit, $total);

            $result = [
                'data' => $conversations,
                'meta' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'query' => $query
                ]
            ];

            // 缓存搜索结果5分钟
            $this->cacheService->set($cacheKey, $result, 300);

            return $this->successResponse($response, $result);
        } catch (\Exception $e) {
            $this->logger->error('搜索对话失败', [
                'error' => $e->getMessage(),
                'params' => $params ?? []
            ]);

            return $this->errorResponse($response, '搜索对话失败: ' . $e->getMessage(), 500);
        }
    }
}
