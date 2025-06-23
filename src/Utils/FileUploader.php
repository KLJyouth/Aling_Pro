<?php

namespace AlingAi\Utils;

use AlingAi\Exceptions\FileException;
use AlingAi\Utils\Logger;

/**
 * 文件上传工具类
 * 提供安全的文件上传、处理和管理功能
 */
class FileUploader
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const UPLOAD_DIR = 'uploads/';
    
    // 允许的文件类型
    private const ALLOWED_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'text/plain', 'text/csv',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    // 文件扩展名映射
    private const EXTENSION_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'text/plain' => 'txt',
        'text/csv' => 'csv',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
    ];
    
    private string $uploadDir;
    private int $maxFileSize;
    private array $allowedTypes;
    
    public function __construct(
        string $uploadDir = null,
        int $maxFileSize = null,
        array $allowedTypes = null
    ) {
        $this->uploadDir = $uploadDir ?? self::UPLOAD_DIR;
        $this->maxFileSize = $maxFileSize ?? self::MAX_FILE_SIZE;
        $this->allowedTypes = $allowedTypes ?? self::ALLOWED_TYPES;
        
        $this->ensureUploadDirectory();
    }
    
    /**
     * 上传单个文件
     */
    public function uploadFile(array $file, string $subDir = ''): array
    {
        $this->validateFile($file);
        
        $targetDir = $this->uploadDir . $subDir;
        if (!empty($subDir)) {
            $this->ensureDirectory($targetDir);
        }
        
        $fileName = $this->generateFileName($file);
        $targetPath = $targetDir . '/' . $fileName;
        $fullPath = rtrim($targetDir, '/') . '/' . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new FileException('文件上传失败');
        }
        
        $fileInfo = [
            'original_name' => $file['name'],
            'file_name' => $fileName,
            'file_path' => $fullPath,
            'relative_path' => $targetPath,
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'upload_time' => date('Y-m-d H:i:s')
        ];
        
        Logger::info('文件上传成功', $fileInfo);
        
        return $fileInfo;
    }
    
    /**
     * 上传多个文件
     */
    public function uploadMultipleFiles(array $files, string $subDir = ''): array
    {
        $results = [];
        
        foreach ($files as $index => $file) {
            try {
                $results[] = $this->uploadFile($file, $subDir);
            } catch (FileException $e) {
                Logger::error("文件上传失败 (索引: $index)", ['error' => $e->getMessage()]);
                $results[] = [
                    'error' => $e->getMessage(),
                    'original_name' => $file['name'] ?? 'unknown'
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * 通过Base64上传文件
     */
    public function uploadFromBase64(string $base64Data, string $fileName, string $subDir = ''): array
    {
        // 解析Base64数据
        if (preg_match('/^data:([^;]+);base64,(.+)$/', $base64Data, $matches)) {
            $mimeType = $matches[1];
            $data = base64_decode($matches[2]);
        } else {
            throw new FileException('无效的Base64数据格式');
        }
        
        if ($data === false) {
            throw new FileException('Base64解码失败');
        }
        
        // 验证MIME类型
        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new FileException('不支持的文件类型: ' . $mimeType);
        }
        
        // 验证文件大小
        if (strlen($data) > $this->maxFileSize) {
            throw new FileException('文件大小超过限制');
        }
        
        $targetDir = $this->uploadDir . $subDir;
        if (!empty($subDir)) {
            $this->ensureDirectory($targetDir);
        }
        
        $extension = self::EXTENSION_MAP[$mimeType] ?? 'bin';
        $generatedFileName = $this->generateUniqueFileName($fileName, $extension);
        $fullPath = rtrim($targetDir, '/') . '/' . $generatedFileName;
        
        if (file_put_contents($fullPath, $data) === false) {
            throw new FileException('文件写入失败');
        }
        
        $fileInfo = [
            'original_name' => $fileName,
            'file_name' => $generatedFileName,
            'file_path' => $fullPath,
            'relative_path' => $targetDir . '/' . $generatedFileName,
            'file_size' => strlen($data),
            'mime_type' => $mimeType,
            'upload_time' => date('Y-m-d H:i:s')
        ];
        
        Logger::info('Base64文件上传成功', $fileInfo);
        
        return $fileInfo;
    }
    
    /**
     * 删除文件
     */
    public function deleteFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        if (unlink($filePath)) {
            Logger::info('文件删除成功', ['file_path' => $filePath]);
            return true;
        }
        
        Logger::error('文件删除失败', ['file_path' => $filePath]);
        return false;
    }
    
    /**
     * 获取文件信息
     */
    public function getFileInfo(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new FileException('文件不存在');
        }
        
        $fileInfo = [
            'file_path' => $filePath,
            'file_name' => basename($filePath),
            'file_size' => filesize($filePath),
            'mime_type' => mime_content_type($filePath),
            'created_time' => date('Y-m-d H:i:s', filectime($filePath)),
            'modified_time' => date('Y-m-d H:i:s', filemtime($filePath)),
            'is_readable' => is_readable($filePath),
            'is_writable' => is_writable($filePath)
        ];
        
        return $fileInfo;
    }
    
    /**
     * 生成缩略图 (仅支持图片)
     */
    public function generateThumbnail(string $imagePath, int $width = 150, int $height = 150): string
    {
        if (!extension_loaded('gd')) {
            throw new FileException('GD扩展未安装');
        }
        
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            throw new FileException('无效的图片文件');
        }
        
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $imageType = $imageInfo[2];
        
        // 创建原始图片资源
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($imagePath);
                break;
            default:
                throw new FileException('不支持的图片格式');
        }
        
        // 计算缩略图尺寸
        $ratio = min($width / $originalWidth, $height / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        // 创建缩略图
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $thumbnail, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );
        
        // 生成缩略图文件名
        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        // 保存缩略图
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $thumbnailPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $thumbnailPath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $thumbnailPath);
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        
        return $thumbnailPath;
    }
    
    /**
     * 验证上传文件
     */
    private function validateFile(array $file): void
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new FileException('无效的文件参数');
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new FileException('没有选择文件');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new FileException('文件大小超过限制');
            default:
                throw new FileException('文件上传失败');
        }
        
        if ($file['size'] > $this->maxFileSize) {
            throw new FileException('文件大小超过限制: ' . $this->formatFileSize($this->maxFileSize));
        }
        
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new FileException('不支持的文件类型: ' . $file['type']);
        }
        
        // 验证文件内容
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($mimeType !== $file['type']) {
            throw new FileException('文件类型不匹配');
        }
    }
    
    /**
     * 生成文件名
     */
    private function generateFileName(array $file): string
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        
        // 清理文件名
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        $baseName = substr($baseName, 0, 50);
        
        return $baseName . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
    }
    
    /**
     * 生成唯一文件名
     */
    private function generateUniqueFileName(string $originalName, string $extension): string
    {
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        $baseName = substr($baseName, 0, 50);
        
        return $baseName . '_' . time() . '_' . uniqid() . '.' . $extension;
    }
    
    /**
     * 确保上传目录存在
     */
    private function ensureUploadDirectory(): void
    {
        $this->ensureDirectory($this->uploadDir);
    }
    
    /**
     * 确保目录存在
     */
    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new FileException('无法创建上传目录: ' . $directory);
            }
        }
        
        if (!is_writable($directory)) {
            throw new FileException('上传目录不可写: ' . $directory);
        }
    }
    
    /**
     * 格式化文件大小
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen((string)$bytes) - 1) / 3);
        
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
    
    /**
     * 清理过期文件
     */
    public function cleanupOldFiles(int $daysOld = 30): int
    {
        $deletedCount = 0;
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->uploadDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoffTime) {
                if (unlink($file->getPathname())) {
                    $deletedCount++;
                }
            }
        }
        
        Logger::info("清理过期文件完成", ['deleted_count' => $deletedCount, 'days_old' => $daysOld]);
        
        return $deletedCount;
    }
}
