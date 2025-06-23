<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use AlingAi\Models\Document;
use AlingAi\Services\FileService;
use AlingAi\Services\ValidationService;
use AlingAi\Utils\FileUploader;
use AlingAi\Utils\PasswordHasher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * 文件API控制器
 * 
 * 提供完整的文件管理功能
 * 优化性能：文件压缩、缓存、批量操作
 * 增强安全性：文件验证、权限控制、病毒扫描
 */
class FileApiController extends BaseApiController
{
    private FileService $fileService;
    private ValidationService $validationService;
    private FileUploader $fileUploader;
    private LoggerInterface $logger;
    
    public function __construct(
        FileService $fileService,
        ValidationService $validationService,
        FileUploader $fileUploader,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->fileService = $fileService;
        $this->validationService = $validationService;
        $this->fileUploader = $fileUploader;
        $this->logger = $logger;
    }

    /**
     * 测试端点
     */
    public function test(): array
    {
        return $this->sendSuccessResponse([
            'message' => 'File API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }

    /**
     * 上传文件
     */
    public function uploadFile(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $uploadedFiles = $request->getUploadedFiles();
            
            if (empty($uploadedFiles)) {
                return $this->sendErrorResponse('未选择文件', 400);
            }

            $results = [];
            $errors = [];

            foreach ($uploadedFiles as $fieldName => $uploadedFile) {
                try {
                    // 验证文件
                    $validationResult = $this->validationService->validateFile($uploadedFile);
                    if (!$validationResult['valid']) {
                        $errors[] = [
                            'field' => $fieldName,
                            'error' => $validationResult['message']
                        ];
                        continue;
                    }

                    // 上传文件
                    $uploadResult = $this->fileUploader->upload($uploadedFile, [
                        'user_id' => $userId,
                        'category' => $request->getParsedBody()['category'] ?? 'general',
                        'description' => $request->getParsedBody()['description'] ?? '',
                        'tags' => $request->getParsedBody()['tags'] ?? []
                    ]);

                    if ($uploadResult['success']) {
                        $results[] = [
                            'field' => $fieldName,
                            'file' => $uploadResult['file'],
                            'url' => $uploadResult['url']
                        ];
                    } else {
                        $errors[] = [
                            'field' => $fieldName,
                            'error' => $uploadResult['message']
                        ];
                    }

                } catch (\Exception $e) {
                    $this->logger->error('文件上传失败', [
                        'user_id' => $userId,
                        'field' => $fieldName,
                        'error' => $e->getMessage()
                    ]);
                    
                    $errors[] = [
                        'field' => $fieldName,
                        'error' => '文件上传失败: ' . $e->getMessage()
                    ];
                }
            }

            $response = [
                'success_count' => count($results),
                'error_count' => count($errors),
                'results' => $results
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            return $this->sendSuccessResponse($response, '文件上传完成');

        } catch (\Exception $e) {
            $this->logger->error('文件上传异常', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendErrorResponse('文件上传失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取用户文件列表
     */
    public function getUserFiles(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $params = $request->getQueryParams();
            
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $category = $params['category'] ?? '';
            $search = $params['search'] ?? '';
            $sortBy = $params['sort_by'] ?? 'created_at';
            $sortOrder = $params['sort_order'] ?? 'desc';

            $files = $this->fileService->getUserFiles($userId, [
                'page' => $page,
                'limit' => $limit,
                'category' => $category,
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]);

            return $this->sendSuccessResponse($files, '文件列表获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取文件列表失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取文件列表失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取文件详情
     */
    public function getFileDetail(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $fileId = $request->getAttribute('id');

            $file = $this->fileService->getFileDetail($fileId, $userId);
            
            if (!$file) {
                return $this->sendErrorResponse('文件不存在或无权限访问', 404);
            }

            return $this->sendSuccessResponse($file, '文件详情获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取文件详情失败', [
                'file_id' => $fileId ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取文件详情失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 下载文件
     */
    public function downloadFile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $fileId = $request->getAttribute('id');

            $downloadResult = $this->fileService->downloadFile($fileId, $userId);
            
            if (!$downloadResult['success']) {
                return $this->sendErrorResponse($downloadResult['message'], 404);
            }

            $file = $downloadResult['file'];
            $filePath = $downloadResult['file_path'];

            // 检查文件是否存在
            if (!file_exists($filePath)) {
                return $this->sendErrorResponse('文件不存在', 404);
            }

            // 设置响应头
            $response = $response
                ->withHeader('Content-Type', $file['mime_type'])
                ->withHeader('Content-Disposition', 'attachment; filename="' . $file['original_name'] . '"')
                ->withHeader('Content-Length', filesize($filePath))
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');

            // 读取文件内容
            $fileContent = file_get_contents($filePath);
            $response->getBody()->write($fileContent);

            // 记录下载日志
            $this->logger->info('文件下载', [
                'user_id' => $userId,
                'file_id' => $fileId,
                'file_name' => $file['original_name']
            ]);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error('文件下载失败', [
                'file_id' => $fileId ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('文件下载失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 删除文件
     */
    public function deleteFile(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $fileId = $request->getAttribute('id');

            $deleteResult = $this->fileService->deleteFile($fileId, $userId);
            
            if (!$deleteResult['success']) {
                return $this->sendErrorResponse($deleteResult['message'], 400);
            }

            $this->logger->info('文件删除成功', [
                'user_id' => $userId,
                'file_id' => $fileId
            ]);

            return $this->sendSuccessResponse(null, '文件删除成功');

        } catch (\Exception $e) {
            $this->logger->error('文件删除失败', [
                'file_id' => $fileId ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('文件删除失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 更新文件信息
     */
    public function updateFile(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $fileId = $request->getAttribute('id');
            $data = $this->getJsonData($request);

            $updateResult = $this->fileService->updateFile($fileId, $userId, $data);
            
            if (!$updateResult['success']) {
                return $this->sendErrorResponse($updateResult['message'], 400);
            }

            return $this->sendSuccessResponse($updateResult['file'], '文件信息更新成功');

        } catch (\Exception $e) {
            $this->logger->error('文件信息更新失败', [
                'file_id' => $fileId ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('文件信息更新失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 批量删除文件
     */
    public function batchDeleteFiles(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $data = $this->getJsonData($request);
            
            if (empty($data['file_ids']) || !is_array($data['file_ids'])) {
                return $this->sendErrorResponse('请选择要删除的文件', 400);
            }

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($data['file_ids'] as $fileId) {
                $deleteResult = $this->fileService->deleteFile($fileId, $userId);
                
                if ($deleteResult['success']) {
                    $successCount++;
                    $results[] = [
                        'file_id' => $fileId,
                        'status' => 'success'
                    ];
                } else {
                    $errorCount++;
                    $results[] = [
                        'file_id' => $fileId,
                        'status' => 'error',
                        'message' => $deleteResult['message']
                    ];
                }
            }

            $response = [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'results' => $results
            ];

            return $this->sendSuccessResponse($response, '批量删除完成');

        } catch (\Exception $e) {
            $this->logger->error('批量删除文件失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('批量删除文件失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取文件统计信息
     */
    public function getFileStats(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $stats = $this->fileService->getUserFileStats($userId);

            return $this->sendSuccessResponse($stats, '文件统计信息获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取文件统计信息失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取文件统计信息失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 搜索文件
     */
    public function searchFiles(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $params = $request->getQueryParams();
            
            $query = $params['q'] ?? '';
            $category = $params['category'] ?? '';
            $fileType = $params['file_type'] ?? '';
            $dateFrom = $params['date_from'] ?? '';
            $dateTo = $params['date_to'] ?? '';
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);

            if (empty($query)) {
                return $this->sendErrorResponse('搜索关键词不能为空', 400);
            }

            $searchResults = $this->fileService->searchFiles($userId, [
                'query' => $query,
                'category' => $category,
                'file_type' => $fileType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'page' => $page,
                'limit' => $limit
            ]);

            return $this->sendSuccessResponse($searchResults, '文件搜索完成');

        } catch (\Exception $e) {
            $this->logger->error('文件搜索失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('文件搜索失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取文件预览
     */
    public function getFilePreview(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $fileId = $request->getAttribute('id');

            $previewResult = $this->fileService->getFilePreview($fileId, $userId);
            
            if (!$previewResult['success']) {
                return $this->sendErrorResponse($previewResult['message'], 404);
            }

            return $this->sendSuccessResponse($previewResult['preview'], '文件预览获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取文件预览失败', [
                'file_id' => $fileId ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取文件预览失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 分享文件
     */
    public function shareFile(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            $fileId = $request->getAttribute('id');
            $data = $this->getJsonData($request);

            $shareResult = $this->fileService->shareFile($fileId, $userId, $data);
            
            if (!$shareResult['success']) {
                return $this->sendErrorResponse($shareResult['message'], 400);
            }

            return $this->sendSuccessResponse($shareResult['share_info'], '文件分享成功');

        } catch (\Exception $e) {
            $this->logger->error('文件分享失败', [
                'file_id' => $fileId ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('文件分享失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取分享的文件
     */
    public function getSharedFile(ServerRequestInterface $request): array
    {
        try {
            $shareToken = $request->getAttribute('token');
            
            if (empty($shareToken)) {
                return $this->sendErrorResponse('分享链接无效', 400);
            }

            $fileResult = $this->fileService->getSharedFile($shareToken);
            
            if (!$fileResult['success']) {
                return $this->sendErrorResponse($fileResult['message'], 404);
            }

            return $this->sendSuccessResponse($fileResult['file'], '分享文件获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取分享文件失败', [
                'share_token' => $shareToken ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取分享文件失败: ' . $e->getMessage(), 500);
        }
    }
}
