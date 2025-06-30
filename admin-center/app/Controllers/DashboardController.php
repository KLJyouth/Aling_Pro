<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Core\Logger;

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
        $recentLogs = $this->getRecentLogs(5);
        
        // 获取工具统计信息
        $toolsStats = $this->getToolsStats();
        
        // 获取最近登录用户
        $recentUsers = $this->getRecentUsers();
        
        // 系统状态指标
        $metrics = $this->getSystemMetrics();
        
        // 渲染视图
        View::display('dashboard.index', [
            'pageTitle' => 'IT运维中心 - 系统仪表盘',
            'pageHeader' => '系统仪表盘',
            'currentPage' => 'dashboard',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/dashboard' => '仪表盘'
            ],
            'systemInfo' => $systemInfo,
            'recentLogs' => $recentLogs,
            'toolsStats' => $toolsStats,
            'recentUsers' => $recentUsers,
            'metrics' => $metrics
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
            'timeZone' => date_default_timezone_get(),
            'mysqlVersion' => $this->getMySQLVersion()
        ];
    }
    
    /**
     * 获取系统性能指标
     * @return array 性能指标
     */
    private function getSystemMetrics()
    {
        // CPU使用率 - 在Windows上我们不能轻易获取，这里模拟数据
        $cpuUsage = rand(20, 75);
        
        // 内存使用率
        $memoryTotal = $this->getMemoryTotal();
        $memoryUsed = $this->getMemoryUsed();
        $memoryUsagePercent = $memoryTotal > 0 ? round(($memoryUsed / $memoryTotal) * 100) : 0;
        
        // 磁盘使用率
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0;
        
        // 数据库连接数 - 这里模拟数据
        $dbConnections = rand(5, 30);
        
        return [
            'cpu' => [
                'usage' => $cpuUsage,
                'status' => $this->getStatusByPercent($cpuUsage)
            ],
            'memory' => [
                'total' => $this->formatBytes($memoryTotal),
                'used' => $this->formatBytes($memoryUsed),
                'free' => $this->formatBytes($memoryTotal - $memoryUsed),
                'usage' => $memoryUsagePercent,
                'status' => $this->getStatusByPercent($memoryUsagePercent)
            ],
            'disk' => [
                'total' => $this->formatBytes($diskTotal),
                'used' => $this->formatBytes($diskUsed),
                'free' => $this->formatBytes($diskFree),
                'usage' => $diskUsagePercent,
                'status' => $this->getStatusByPercent($diskUsagePercent)
            ],
            'database' => [
                'connections' => $dbConnections,
                'status' => $this->getStatusByPercent($dbConnections / 100 * 100)
            ]
        ];
    }
    
    /**
     * 根据百分比获取状态
     * @param int $percent 百分比
     * @return string 状态
     */
    private function getStatusByPercent($percent)
    {
        if ($percent < 60) {
            return 'good'; // 绿色
        } elseif ($percent < 80) {
            return 'warning'; // 黄色
        } else {
            return 'critical'; // 红色
        }
    }
    
    /**
     * 获取系统总内存
     * @return int 总内存（字节）
     */
    private function getMemoryTotal()
    {
        // 在Windows上获取内存信息
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // 模拟数据
            return 8 * 1024 * 1024 * 1024; // 假设8GB
        } else {
            // Linux系统
            if (is_readable('/proc/meminfo')) {
                $memInfo = file_get_contents('/proc/meminfo');
                preg_match('/MemTotal:\s+(\d+)\skB/i', $memInfo, $matches);
                if (isset($matches[1])) {
                    return $matches[1] * 1024; // 转换为字节
                }
            }
            return 0;
        }
    }
    
    /**
     * 获取系统已用内存
     * @return int 已用内存（字节）
     */
    private function getMemoryUsed()
    {
        // 在Windows上获取内存信息
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // 模拟数据
            return 4 * 1024 * 1024 * 1024; // 假设使用4GB
        } else {
            // Linux系统
            if (is_readable('/proc/meminfo')) {
                $memInfo = file_get_contents('/proc/meminfo');
                preg_match('/MemTotal:\s+(\d+)\skB/i', $memInfo, $matches);
                $memTotal = isset($matches[1]) ? $matches[1] * 1024 : 0;
                
                preg_match('/MemFree:\s+(\d+)\skB/i', $memInfo, $matches);
                $memFree = isset($matches[1]) ? $matches[1] * 1024 : 0;
                
                preg_match('/Buffers:\s+(\d+)\skB/i', $memInfo, $matches);
                $buffers = isset($matches[1]) ? $matches[1] * 1024 : 0;
                
                preg_match('/Cached:\s+(\d+)\skB/i', $memInfo, $matches);
                $cached = isset($matches[1]) ? $matches[1] * 1024 : 0;
                
                return $memTotal - $memFree - $buffers - $cached;
            }
            return 0;
        }
    }
    
    /**
     * 获取MySQL版本
     * @return string MySQL版本
     */
    private function getMySQLVersion()
    {
        try {
            $db = Database::getInstance();
            $result = $db->query("SELECT VERSION() as version");
            $row = $result->fetch(\PDO::FETCH_ASSOC);
            return $row['version'] ?? 'Unknown';
        } catch (\Exception $e) {
            Logger::error('获取MySQL版本失败: ' . $e->getMessage());
            return 'Unknown';
        }
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
            
            // 日志类型
            $logType = '';
            if (strpos($fileName, 'error') !== false) {
                $logType = 'error';
            } elseif (strpos($fileName, 'warning') !== false) {
                $logType = 'warning';
            } elseif (strpos($fileName, 'info') !== false) {
                $logType = 'info';
            } else {
                $logType = 'debug';
            }
            
            $logs[] = [
                'name' => $fileName,
                'size' => $this->formatBytes($fileSize),
                'modified' => date('Y-m-d H:i:s', $modifiedTime),
                'content' => $content,
                'type' => $logType
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
     * 获取最近登录用户
     * @param int $limit 限制数量
     * @return array 用户列表
     */
    private function getRecentUsers($limit = 5)
    {
        try {
            $db = Database::getInstance();
            $sql = "
                SELECT u.id, u.username, u.name, u.email, u.role, u.last_login, 
                       h.action, h.ip_address, h.created_at
                FROM admin_users u
                LEFT JOIN admin_login_history h ON u.id = h.user_id
                WHERE h.action = 'login'
                ORDER BY h.created_at DESC
                LIMIT ?
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Logger::error('获取最近登录用户失败: ' . $e->getMessage());
            return [];
        }
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