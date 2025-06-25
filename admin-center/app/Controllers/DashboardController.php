<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * 仪表盘控制器
 * 负责处理仪表盘相关请求
 */
class DashboardController extends Controller
{
    /**
     * 显示仪表盘首页
     * @return void
     */
    public function index()
    {
        // 获取系统状态信息
        $systemInfo = $this->getSystemInfo();
        
        // 获取最近的日志
        $recentLogs = $this->getRecentLogs();
        
        // 获取工具统计信息
        $toolsStats = $this->getToolsStats();
        
        // 渲染视图
        $this->view('dashboard.index', [
            'systemInfo' => $systemInfo,
            'recentLogs' => $recentLogs,
            'toolsStats' => $toolsStats,
            'pageTitle' => 'IT运维中心 - 仪表盘'
        ]);
    }
    
    /**
     * 获取系统状态信息
     * @return array 系统状态信息
     */
    private function getSystemInfo()
    {
        return [
            'phpVersion' => PHP_VERSION,
            'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'operatingSystem' => PHP_OS,
            'memoryUsage' => $this->formatBytes(memory_get_usage(true)),
            'diskFreeSpace' => $this->formatBytes(disk_free_space('/')),
            'diskTotalSpace' => $this->formatBytes(disk_total_space('/')),
            'serverTime' => date('Y-m-d H:i:s'),
            'timeZone' => date_default_timezone_get()
        ];
    }
    
    /**
     * 获取最近的日志
     * @param int $limit 限制数量
     * @return array 日志列表
     */
    private function getRecentLogs($limit = 5)
    {
        $logsPath = BASE_PATH . '/storage/logs';
        $logs = [];
        
        // 检查日志目录是否存在
        if (!is_dir($logsPath)) {
            return $logs;
        }
        
        // 获取所有日志文件
        $logFiles = glob($logsPath . '/*.log');
        
        // 按修改时间排序
        usort($logFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // 限制数量
        $logFiles = array_slice($logFiles, 0, $limit);
        
        // 读取日志内容
        foreach ($logFiles as $logFile) {
            $fileName = basename($logFile);
            $fileSize = filesize($logFile);
            $modifiedTime = filemtime($logFile);
            
            // 读取最后几行
            $content = '';
            $file = fopen($logFile, 'r');
            if ($file) {
                $maxLines = 5;
                $lines = [];
                
                // 从文件末尾开始读取
                fseek($file, -1, SEEK_END);
                $pos = ftell($file);
                $lineCount = 0;
                
                // 向前读取直到找到足够的行数
                while ($pos > 0 && $lineCount < $maxLines) {
                    $char = fgetc($file);
                    if ($char === "\n" && $pos > 0) {
                        $lineCount++;
                    }
                    $pos--;
                    fseek($file, $pos);
                }
                
                // 读取找到的行
                while (!feof($file)) {
                    $line = fgets($file);
                    if ($line !== false) {
                        $lines[] = $line;
                    }
                }
                
                fclose($file);
                $content = implode('', $lines);
            }
            
            $logs[] = [
                'name' => $fileName,
                'size' => $this->formatBytes($fileSize),
                'modified' => date('Y-m-d H:i:s', $modifiedTime),
                'content' => $content
            ];
        }
        
        return $logs;
    }
    
    /**
     * 获取工具统计信息
     * @return array 工具统计信息
     */
    private function getToolsStats()
    {
        $toolsPath = BASE_PATH . '/tools';
        $stats = [
            'totalTools' => 0,
            'recentlyUsed' => [],
            'categories' => []
        ];
        
        // 检查工具目录是否存在
        if (!is_dir($toolsPath)) {
            return $stats;
        }
        
        // 获取所有PHP工具文件
        $toolFiles = glob($toolsPath . '/*.php');
        $stats['totalTools'] = count($toolFiles);
        
        // 按修改时间排序获取最近使用的工具
        usort($toolFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // 获取最近使用的5个工具
        $recentTools = array_slice($toolFiles, 0, 5);
        foreach ($recentTools as $tool) {
            $stats['recentlyUsed'][] = [
                'name' => basename($tool, '.php'),
                'lastUsed' => date('Y-m-d H:i:s', filemtime($tool))
            ];
        }
        
        // 统计工具类别
        $categories = [
            'fix' => 0,
            'check' => 0,
            'validate' => 0,
            'other' => 0
        ];
        
        foreach ($toolFiles as $tool) {
            $fileName = basename($tool, '.php');
            if (strpos($fileName, 'fix_') === 0) {
                $categories['fix']++;
            } elseif (strpos($fileName, 'check_') === 0) {
                $categories['check']++;
            } elseif (strpos($fileName, 'validate_') === 0) {
                $categories['validate']++;
            } else {
                $categories['other']++;
            }
        }
        
        $stats['categories'] = $categories;
        
        return $stats;
    }
    
    /**
     * 格式化字节数为人类可读格式
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化后的字符串
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
} 