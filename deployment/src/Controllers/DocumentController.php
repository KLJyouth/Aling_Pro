<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\Models\Document;
use AlingAi\Models\User;
use AlingAi\Services\CacheService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

/**
 * 文档管理控制器
 * 处理文档相关的所有操作
 */
class DocumentController extends BaseController
{
    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * 获取文档列表
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $search = $params['search'] ?? '';
            $userId = $params['user_id'] ?? '';
            $type = $params['type'] ?? '';
            $status = $params['status'] ?? '';

            // 缓存键
            $cacheKey = "documents_list_" . md5(serialize($params));
            
            // 尝试从缓存获取
            $result = $this->cacheService->get($cacheKey);
            if ($result !== null) {
                return $this->successResponse($this->createResponse(), $result, 'Success');
            }

            // 构建查询
            $documentModel = new Document();
            $query = $documentModel->query();

            if (!empty($search)) {
                $query->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('content', 'LIKE', "%{$search}%");
            }

            if (!empty($userId)) {
                $query->where('user_id', $userId);
            }

            if (!empty($type)) {
                $query->where('type', $type);
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            // 获取分页数据
            $documents = $query->orderBy('created_at', 'DESC')->paginate($page, $limit);

            $result = [
                'data' => $documents['data'] ?? [],
                'pagination' => [
                    'total' => $documents['total'] ?? 0,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil(($documents['total'] ?? 0) / $limit),
                    'from' => (($page - 1) * $limit) + 1,
                    'to' => min($page * $limit, $documents['total'] ?? 0)
                ]
            ];

            // 缓存结果
            $this->cacheService->set($cacheKey, $result, 300);

            return $this->successResponse($this->createResponse(), $result, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '获取文档列表失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取文档详情
     */
    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        try {
            $documentId = (int)$args['id'];
            
            $documentModel = new Document();
            $document = $documentModel->query()->where('id', $documentId)->first();

            if (!$document) {
                return $this->errorResponse($this->createResponse(), '文档不存在', 404);
            }

            return $this->successResponse($this->createResponse(), $document, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '获取文档详情失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 创建文档
     */
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            
            // 验证必要字段
            $requiredFields = ['title', 'content', 'user_id'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->errorResponse($this->createResponse(), "缺少必要字段: {$field}", 400);
                }
            }

            // 验证用户是否存在
            $userModel = new User();
            $user = $userModel->query()->where('id', $data['user_id'])->first();
            if (!$user) {
                return $this->errorResponse($this->createResponse(), '指定的用户不存在', 400);
            }

            $documentModel = new Document();
            $documentData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'user_id' => $data['user_id'],
                'type' => $data['type'] ?? 'document',
                'format' => $data['format'] ?? 'text',
                'status' => $data['status'] ?? 'draft',
                'metadata' => json_encode($data['metadata'] ?? []),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $documentId = $documentModel->query()->insert($documentData);
            $document = $documentModel->query()->where('id', $documentId)->first();

            return $this->successResponse($this->createResponse(), $document, '文档创建成功');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '创建文档失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 更新文档
     */
    public function update(ServerRequestInterface $request, array $args): ResponseInterface
    {
        try {
            $documentId = (int)$args['id'];
            $data = json_decode($request->getBody()->getContents(), true);

            $documentModel = new Document();
            $document = $documentModel->query()->where('id', $documentId)->first();

            if (!$document) {
                return $this->errorResponse($this->createResponse(), '文档不存在', 404);
            }

            // 权限检查（如果需要）
            $currentUserId = $this->getCurrentUserId($request);
            if ($currentUserId && $document->user_id !== $currentUserId) {
                return $this->errorResponse($this->createResponse(), '没有权限修改此文档', 403);
            }

            $updateData = ['updated_at' => date('Y-m-d H:i:s')];
            
            if (isset($data['title'])) $updateData['title'] = $data['title'];
            if (isset($data['content'])) $updateData['content'] = $data['content'];
            if (isset($data['type'])) $updateData['type'] = $data['type'];
            if (isset($data['format'])) $updateData['format'] = $data['format'];
            if (isset($data['status'])) $updateData['status'] = $data['status'];
            if (isset($data['metadata'])) $updateData['metadata'] = json_encode($data['metadata']);

            $documentModel->query()->where('id', $documentId)->update($updateData);
            $updatedDocument = $documentModel->query()->where('id', $documentId)->first();

            return $this->successResponse($this->createResponse(), $updatedDocument, '文档信息更新成功');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '更新文档失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 删除文档
     */
    public function delete(ServerRequestInterface $request, array $args): ResponseInterface
    {
        try {
            $documentId = (int)$args['id'];

            $documentModel = new Document();
            $document = $documentModel->query()->where('id', $documentId)->first();

            if (!$document) {
                return $this->errorResponse($this->createResponse(), '文档不存在', 404);
            }

            // 权限检查（如果需要）
            $currentUserId = $this->getCurrentUserId($request);
            if ($currentUserId && $document->user_id !== $currentUserId) {
                return $this->errorResponse($this->createResponse(), '没有权限删除此文档', 403);
            }

            $documentModel->query()->where('id', $documentId)->delete();

            return $this->successResponse($this->createResponse(), null, '文档删除成功');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '删除文档失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 下载文档
     */
    public function download(ServerRequestInterface $request, array $args): ResponseInterface
    {
        try {
            $documentId = (int)$args['id'];

            $documentModel = new Document();
            $document = $documentModel->query()->where('id', $documentId)->first();

            if (!$document) {
                return $this->errorResponse($this->createResponse(), '文档不存在', 404);
            }

            // 权限检查（如果需要）
            $currentUserId = $this->getCurrentUserId($request);
            if ($currentUserId && $document->user_id !== $currentUserId) {
                return $this->errorResponse($this->createResponse(), '没有权限下载此文档', 403);
            }

            // 生成下载响应
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'application/octet-stream');
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $document->title . '.txt"');
            $response->getBody()->write($document->content);

            return $response;
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '下载文档失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取文档统计
     */
    public function statistics(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $documentModel = new Document();
            
            $totalCount = $documentModel->query()->count();
            
            // 按类型统计
            $typeStats = $documentModel->query()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get();
            
            $typeStatsArray = [];
            foreach ($typeStats as $stat) {
                $typeStatsArray[$stat->type] = $stat->count;
            }

            // 按格式统计
            $formatStats = $documentModel->query()
                ->selectRaw('format, COUNT(*) as count')
                ->groupBy('format')
                ->get();
            
            $formatStatsArray = [];
            foreach ($formatStats as $stat) {
                $formatStatsArray[$stat->format] = $stat->count;
            }

            $stats = [
                'total_documents' => $totalCount,
                'by_type' => $typeStatsArray,
                'by_format' => $formatStatsArray,
                'recent_documents' => $documentModel->query()
                    ->orderBy('created_at', 'DESC')
                    ->limit(10)
                    ->get()
            ];

            return $this->successResponse($this->createResponse(), $stats, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '获取文档统计失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 搜索文档
     */
    public function search(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getQueryParams();
            $keyword = $params['q'] ?? '';
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);

            if (empty($keyword)) {
                return $this->errorResponse($this->createResponse(), '搜索关键词不能为空', 400);
            }

            $documentModel = new Document();
            $documents = $documentModel->query()
                ->where('title', 'LIKE', "%{$keyword}%")
                ->orWhere('content', 'LIKE', "%{$keyword}%")
                ->orderBy('created_at', 'DESC')
                ->paginate($page, $limit);

            $result = [
                'data' => $documents['data'] ?? [],
                'pagination' => [
                    'total' => $documents['total'] ?? 0,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil(($documents['total'] ?? 0) / $limit),
                    'from' => (($page - 1) * $limit) + 1,
                    'to' => min($page * $limit, $documents['total'] ?? 0),
                ]
            ];

            return $this->successResponse($this->createResponse(), $result, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($this->createResponse(), '搜索文档失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取当前用户ID
     */
    private function getCurrentUserId(ServerRequestInterface $request): ?int
    {
        $user = $request->getAttribute('user');
        return $user['id'] ?? null;
    }

    /**
     * 创建响应对象
     */
    private function createResponse(): ResponseInterface
    {
        return new Response();
    }
}
