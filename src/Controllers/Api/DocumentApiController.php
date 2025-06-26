<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Models\Document;
use AlingAi\Models\BaseModel;
use AlingAi\Services\AuthService;
use AlingAi\Services\ValidatorService;
use AlingAi\Services\DocumentService;
use AlingAi\Exceptions\ApiException;
use AlingAi\Exceptions\ValidationException;
use AlingAi\Exceptions\UnauthorizedException;
use AlingAi\Exceptions\ForbiddenException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Exception;

/**
 * 文档API控制器
 * 
 * 提供文档相关的RESTful API接口
 * 支持文档的增删改查、导入导出等功能
 *
 * @package AlingAi\Controllers\Api
 * @author AlingAi Team
 * @version 1.0.0
 * @since 2025-06-26
 */
class DocumentApiController extends ModelApiController
{
    /**
     * @var DocumentService 文档服务
     */
    protected $documentService;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化模型
        $this->initModel(Document::class);
        
        // 初始化文档服务
        $this->documentService = new DocumentService();
        
        // 设置权限前缀
        $this->permissionPrefix = 'document';
        
        // 设置允许的过滤字段
        $this->allowedFilters = [
            'title', 'status', 'type', 'user_id', 'created_at'
        ];
        
        // 设置允许的排序字段
        $this->allowedSorts = [
            'id', 'title', 'status', 'type', 'created_at', 'updated_at'
        ];
        
        // 设置允许的包含关联
        $this->allowedIncludes = [
            'user', 'tags', 'versions'
        ];
        
        // 设置允许的字段
        $this->allowedFields = [
            'id', 'title', 'description', 'status', 'type', 'user_id', 
            'created_at', 'updated_at', 'version'
        ];
        
        // 设置创建验证规则
        $this->createRules = [
            'title' => 'required|string|min:1|max:255',
            'description' => 'string|max:1000',
            'content' => 'required|string',
            'type' => 'required|string|in:text,html,markdown,json',
            'status' => 'string|in:draft,published,archived',
            'tags' => 'array'
        ];
        
        // 设置更新验证规则
        $this->updateRules = [
            'title' => 'string|min:1|max:255',
            'description' => 'string|max:1000',
            'content' => 'string',
            'type' => 'string|in:text,html,markdown,json',
            'status' => 'string|in:draft,published,archived',
            'tags' => 'array'
        ];
    }
    
    /**
     * 重写创建方法，自动关联当前用户
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            // 权限检查
            $this->checkPermission("{$this->permissionPrefix}.create");
            
            // 获取当前用户
            $user = $this->getCurrentUser();
            
            // 如果未登录则返回错误
            if (!$user) {
                throw new UnauthorizedException('未登录或会话已过期');
            }
            
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 添加用户ID
            $data['user_id'] = $user->id;
            
            // 验证数据
            $validator = new ValidatorService();
            $validatedData = $validator->validate($data, $this->createRules);
            
            // 创建记录
            $record = $this->documentService->createDocument($validatedData);
            
            // 构建响应
            $responseData = [
                'data' => $record->toArray(),
                'message' => '文档创建成功'
            ];
            
            return $this->respondWithJson($response, $responseData, 201);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (Exception $e) {
            return $this->respondWithError($response, '创建文档失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取文档内容
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @param array $args 路径参数
     * @return Response
     */
    public function getContent(Request $request, Response $response, array $args): Response
    {
        try {
            // 获取文档ID
            $id = $args['id'] ?? null;
            if (!$id) {
                throw new ApiException('未提供文档ID', 400);
            }
            
            // 获取版本参数
            $params = $request->getQueryParams();
            $version = isset($params['version']) ? (int)$params['version'] : null;
            
            // 查找文档
            $document = $this->model->findOrFail($id);
            
            // 检查用户是否有权限查看该文档
            if (!$this->canViewRecord($document)) {
                throw new ForbiddenException('无权查看该文档');
            }
            
            // 获取文档内容
            $content = $this->documentService->getDocumentContent($id, $version);
            
            // 构建响应
            $data = [
                'data' => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'description' => $document->description,
                    'type' => $document->type,
                    'version' => $version ?? $document->version,
                    'content' => $content
                ]
            ];
            
            return $this->respondWithJson($response, $data);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->respondWithError($response, '文档不存在', 404);
        } catch (ApiException $e) {
            return $this->respondWithError($response, $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->respondWithError($response, '获取文档内容失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取文档版本历史
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @param array $args 路径参数
     * @return Response
     */
    public function getVersions(Request $request, Response $response, array $args): Response
    {
        try {
            // 获取文档ID
            $id = $args['id'] ?? null;
            if (!$id) {
                throw new ApiException('未提供文档ID', 400);
            }
            
            // 查找文档
            $document = $this->model->findOrFail($id);
            
            // 检查用户是否有权限查看该文档
            if (!$this->canViewRecord($document)) {
                throw new ForbiddenException('无权查看该文档');
            }
            
            // 获取文档版本历史
            $versions = $this->documentService->getDocumentVersions($id);
            
            // 构建响应
            $data = [
                'data' => $versions
            ];
            
            return $this->respondWithJson($response, $data);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->respondWithError($response, '文档不存在', 404);
        } catch (ApiException $e) {
            return $this->respondWithError($response, $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->respondWithError($response, '获取文档版本历史失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 检查当前用户是否有权限查看记录
     *
     * @param BaseModel $record 记录
     * @return bool
     */
    protected function canViewRecord(BaseModel $record): bool
    {
        $auth = AuthService::getInstance();
        $currentUser = $auth->getUser();
        
        if (!$currentUser) {
            return false;
        }
        
        // 管理员可以查看所有文档
        if ($currentUser->isAdmin()) {
            return true;
        }
        
        // 用户可以查看自己的文档
        if ($currentUser->id == $record->user_id) {
            return true;
        }
        
        // 发布状态的文档可以被所有人查看
        return $record->status === 'published';
    }
    
    /**
     * 检查当前用户是否有权限更新记录
     *
     * @param BaseModel $record 记录
     * @return bool
     */
    protected function canUpdateRecord(BaseModel $record): bool
    {
        $auth = AuthService::getInstance();
        $currentUser = $auth->getUser();
        
        if (!$currentUser) {
            return false;
        }
        
        // 管理员可以更新所有文档
        if ($currentUser->isAdmin()) {
            return true;
        }
        
        // 用户只能更新自己的文档
        return $currentUser->id == $record->user_id;
    }
    
    /**
     * 检查当前用户是否有权限删除记录
     *
     * @param BaseModel $record 记录
     * @return bool
     */
    protected function canDeleteRecord(BaseModel $record): bool
    {
        $auth = AuthService::getInstance();
        $currentUser = $auth->getUser();
        
        if (!$currentUser) {
            return false;
        }
        
        // 管理员可以删除所有文档
        if ($currentUser->isAdmin()) {
            return true;
        }
        
        // 用户只能删除自己的文档
        return $currentUser->id == $record->user_id;
    }
    
    /**
     * 上传文档
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function upload(Request $request, Response $response): Response
    {
        try {
            // 权限检查
            $this->checkPermission("{$this->permissionPrefix}.create");
            
            // 获取当前用户
            $user = $this->getCurrentUser();
            
            // 如果未登录则返回错误
            if (!$user) {
                throw new UnauthorizedException('未登录或会话已过期');
            }
            
            // 获取上传文件
            $uploadedFiles = $request->getUploadedFiles();
            
            if (empty($uploadedFiles['file'])) {
                throw new ApiException('未提供上传文件', 400);
            }
            
            $file = $uploadedFiles['file'];
            
            // 验证文件
            if ($file->getError() !== UPLOAD_ERR_OK) {
                throw new ApiException('文件上传失败: ' . $file->getError(), 400);
            }
            
            // 获取文件信息
            $fileSize = $file->getSize();
            $fileName = $file->getClientFilename();
            $fileType = $file->getClientMediaType();
            
            // 验证文件大小
            $maxFileSize = 10 * 1024 * 1024; // 10MB
            if ($fileSize > $maxFileSize) {
                throw new ApiException('文件大小超过限制', 400);
            }
            
            // 验证文件类型
            $allowedTypes = ['application/pdf', 'text/plain', 'text/html', 'text/markdown', 'application/json'];
            if (!in_array($fileType, $allowedTypes)) {
                throw new ApiException('不支持的文件类型', 400);
            }
            
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 设置文档数据
            $documentData = [
                'title' => $data['title'] ?? pathinfo($fileName, PATHINFO_FILENAME),
                'description' => $data['description'] ?? '',
                'type' => $this->getDocumentTypeFromMimeType($fileType),
                'status' => $data['status'] ?? 'draft',
                'user_id' => $user['id'],
                'content' => ''
            ];
            
            // 读取文件内容
            $documentData['content'] = $file->getStream()->getContents();
            
            // 创建文档
            $document = $this->documentService->createDocument($documentData);
            
            // 构建响应
            $responseData = [
                'data' => $document->toArray(),
                'message' => '文档上传成功'
            ];
            
            return $this->respondWithJson($response, $responseData, 201);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (ApiException $e) {
            return $this->respondWithError($response, $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->respondWithError($response, '文档上传失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 批量导入文档
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return Response
     */
    public function batchImport(Request $request, Response $response): Response
    {
        try {
            // 权限检查
            $this->checkPermission("{$this->permissionPrefix}.create");
            
            // 获取当前用户
            $user = $this->getCurrentUser();
            
            // 如果未登录则返回错误
            if (!$user) {
                throw new UnauthorizedException('未登录或会话已过期');
            }
            
            // 获取请求数据
            $data = $request->getParsedBody();
            
            // 验证数据
            if (empty($data['documents']) || !is_array($data['documents'])) {
                throw new ValidationException(['documents' => ['必须提供文档数组']]);
            }
            
            // 处理导入
            $results = [
                'total' => count($data['documents']),
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];
            
            foreach ($data['documents'] as $index => $documentData) {
                try {
                    // 添加用户ID
                    $documentData['user_id'] = $user['id'];
                    
                    // 验证数据
                    $validator = new ValidatorService();
                    $validatedData = $validator->validate($documentData, $this->createRules);
                    
                    // 创建文档
                    $this->documentService->createDocument($validatedData);
                    
                    $results['success']++;
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'message' => $e->getMessage()
                    ];
                }
            }
            
            // 构建响应
            $responseData = [
                'data' => $results,
                'message' => "文档批量导入完成: {$results['success']}个成功, {$results['failed']}个失败"
            ];
            
            return $this->respondWithJson($response, $responseData);
        } catch (UnauthorizedException $e) {
            return $this->respondWithError($response, $e->getMessage(), 401);
        } catch (ForbiddenException $e) {
            return $this->respondWithError($response, $e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->respondWithValidationError($response, $e->getErrors());
        } catch (Exception $e) {
            return $this->respondWithError($response, '批量导入失败: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 根据MIME类型获取文档类型
     *
     * @param string $mimeType MIME类型
     * @return string
     */
    private function getDocumentTypeFromMimeType(string $mimeType): string
    {
        $typeMap = [
            'application/pdf' => 'pdf',
            'text/plain' => 'text',
            'text/html' => 'html',
            'text/markdown' => 'markdown',
            'application/json' => 'json'
        ];
        
        return $typeMap[$mimeType] ?? 'text';
    }
} 