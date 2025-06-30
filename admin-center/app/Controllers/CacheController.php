<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;

/**
 * 缓存控制器
 * 负责处理缓存相关请求
 */
class CacheController extends Controller
{
    /**
     * 清除缓存
     */
    public function clear()
    {
        try {
            // 清除视图缓存
            $this->clearDirectory(dirname(dirname(__DIR__)) . '/storage/cache/views');
            
            // 清除数据缓存
            $this->clearDirectory(dirname(dirname(__DIR__)) . '/storage/cache/data');
            
            // 清除临时文件
            $this->clearDirectory(dirname(dirname(__DIR__)) . '/storage/temp');
            
            // 记录日志
            Logger::info('缓存已清除', [
                'user_id' => $_SESSION['user_id'] ?? 0,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            $_SESSION['flash_message'] = '缓存已成功清除';
            $_SESSION['flash_message_type'] = 'success';
        } catch (\Exception $e) {
            // 记录错误
            Logger::error('清除缓存失败: ' . $e->getMessage());
            
            $_SESSION['flash_message'] = '清除缓存失败: ' . $e->getMessage();
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // 重定向回上一页或工具页
        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/tools';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * 清除目录中的所有文件
     * 
     * @param string $directory 目录路径
     * @return void
     */
    private function clearDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            // 保留.gitkeep文件
            if ($file->getFilename() === '.gitkeep') {
                continue;
            }
            
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
    }
} 