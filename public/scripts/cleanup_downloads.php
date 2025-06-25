<?php
/**
 * 清理过期下载文件脚本
 * 自动删除超过指定时间的SDK下载文件
 */

class DownloadCleaner {
    private $downloadPath;
    private $logPath;
    private $maxAge; // 文件最大存活时间（秒）
    
    public function __construct($maxAge = 300) { // 默认5分钟
        $this->downloadPath = __DIR__ . '/../public/downloads/';
        $this->logPath = __DIR__ . '/../logs/';
        $this->maxAge = $maxAge;
        
        // 确保目录存在
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true];
        }
    }
    
    /**
     * 清理过期文件
     */
    public function cleanupExpiredFiles() {
        $cleanedFiles = 0;
        $totalSize = 0;
        $errors = [];
        
        try {
            if (!is_dir($this->downloadPath)) {
                return [
                    'success' => true,
                    'message' => '下载目录不存�?,
                    'cleaned_files' => 0,
                    'freed_space' => 0
                ];
            }
            
            $files = glob($this->downloadPath . 'alingai_sdk_*.zip'];
            $currentTime = time(];
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileAge = $currentTime - filemtime($file];
                    
                    if ($fileAge > $this->maxAge) {
                        $fileSize = filesize($file];
                        
                        if (unlink($file)) {
                            $cleanedFiles++;
                            $totalSize += $fileSize;
                            $this->log("已删除过期文�? " . basename($file) . " (大小: " . $this->formatFileSize($fileSize) . ")"];
                        } else {
                            $errors[] = "无法删除文件: " . basename($file];
                        }
                    }
                }
            }
            
            $result = [
                'success' => true,
                'cleaned_files' => $cleanedFiles,
                'freed_space' => $this->formatFileSize($totalSize],
                'freed_bytes' => $totalSize,
                'errors' => $errors,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if ($cleanedFiles > 0) {
                $this->log("清理完成: 删除�?{$cleanedFiles} 个文件，释放空间 " . $this->formatFileSize($totalSize)];
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log("清理过程中发生错�? " . $e->getMessage()];
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * 获取下载目录状�?
     */
    public function getDirectoryStatus() {
        $files = glob($this->downloadPath . 'alingai_sdk_*.zip'];
        $totalFiles = count($files];
        $totalSize = 0;
        $oldestFile = null;
        $newestFile = null;
        $currentTime = time(];
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileSize = filesize($file];
                $fileTime = filemtime($file];
                
                $totalSize += $fileSize;
                
                if ($oldestFile === null || $fileTime < filemtime($oldestFile)) {
                    $oldestFile = $file;
                }
                
                if ($newestFile === null || $fileTime > filemtime($newestFile)) {
                    $newestFile = $file;
                }
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $this->formatFileSize($totalSize],
            'total_bytes' => $totalSize,
            'oldest_file' => $oldestFile ? [
                'name' => basename($oldestFile],
                'age_seconds' => $currentTime - filemtime($oldestFile],
                'age_readable' => $this->formatAge($currentTime - filemtime($oldestFile))
            ] : null,
            'newest_file' => $newestFile ? [
                'name' => basename($newestFile],
                'age_seconds' => $currentTime - filemtime($newestFile],
                'age_readable' => $this->formatAge($currentTime - filemtime($newestFile))
            ] : null,
            'max_age_seconds' => $this->maxAge,
            'max_age_readable' => $this->formatAge($this->maxAge)
        ];
    }
    
    /**
     * 立即清理所有下载文�?
     */
    public function cleanupAllFiles() {
        $cleanedFiles = 0;
        $totalSize = 0;
        $errors = [];
        
        try {
            $files = glob($this->downloadPath . 'alingai_sdk_*.zip'];
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileSize = filesize($file];
                    
                    if (unlink($file)) {
                        $cleanedFiles++;
                        $totalSize += $fileSize;
                    } else {
                        $errors[] = "无法删除文件: " . basename($file];
                    }
                }
            }
            
            $this->log("强制清理完成: 删除�?{$cleanedFiles} 个文�?];
            
            return [
                'success' => true,
                'cleaned_files' => $cleanedFiles,
                'freed_space' => $this->formatFileSize($totalSize],
                'errors' => $errors,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * 格式化文件大�?
     */
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes > 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * 格式化时间年�?
     */
    private function formatAge($seconds) {
        if ($seconds < 60) {
            return $seconds . ' �?;
        } elseif ($seconds < 3600) {
            return floor($seconds / 60) . ' 分钟';
        } elseif ($seconds < 86400) {
            return floor($seconds / 3600) . ' 小时';
        } else {
            return floor($seconds / 86400) . ' �?;
        }
    }
    
    /**
     * 记录日志
     */
    private function log($message) {
        $logFile = $this->logPath . 'download_cleanup.log';
        $logEntry = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX];
    }
}

// 处理HTTP请求
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cleaner = new DownloadCleaner(];
    
    $action = $_POST['action'] ?? 'cleanup';
    
    switch ($action) {
        case 'cleanup':
            $result = $cleaner->cleanupExpiredFiles(];
            break;
        case 'status':
            $result = $cleaner->getDirectoryStatus(];
            break;
        case 'cleanup_all':
            $result = $cleaner->cleanupAllFiles(];
            break;
        default:
            $result = [
                'success' => false,
                'error' => '未知的操作类�?
            ];
    }
    
    header('Content-Type: application/json'];
    echo json_encode($result];
    
} elseif (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $cleaner = new DownloadCleaner(];
    $status = $cleaner->getDirectoryStatus(];
    
    header('Content-Type: application/json'];
    echo json_encode($status];
    
} else {
    // 如果是命令行调用
    if (php_sapi_name() === 'cli') {
        $cleaner = new DownloadCleaner(];
        $result = $cleaner->cleanupExpiredFiles(];
        
        echo "SDK下载文件清理结果:\n";
        echo "- 清理文件�? " . $result['cleaned_files'] . "\n";
        echo "- 释放空间: " . $result['freed_space'] . "\n";
        
        if (!empty($result['errors'])) {
            echo "- 错误: " . implode(', ', $result['errors']) . "\n";
        }
    } else {
        echo "SDK下载文件清理器已就绪";
    }
}
?>

