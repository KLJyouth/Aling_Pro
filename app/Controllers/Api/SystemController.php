<?php
namespace App\Controllers\Api;

/**
 * 系统控制器
 * 
 * 处理系统状态和信息相关的API请求
 */
class SystemController
{
    /**
     * 检查API是否正常运行
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function ping($requestData = [], $params = [])
    {
        return [
            'status' => 'success',
            'message' => 'pong',
            'timestamp' => time()
        ];
    }
    
    /**
     * 获取系统版本信息
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function getVersion($requestData = [], $params = [])
    {
        return [
            'status' => 'success',
            'version' => '1.0.0',
            'phpVersion' => PHP_VERSION,
            'environment' => getenv('APP_ENV') ?: 'production'
        ];
    }
    
    /**
     * 获取系统当前状态
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function getStatus($requestData = [], $params = [])
    {
        // 检查数据库连接
        $dbStatus = $this->checkDatabaseConnection();
        
        // 检查文件系统
        $fsStatus = $this->checkFileSystem();
        
        // 获取服务器负载
        $load = sys_getloadavg();
        
        return [
            'status' => 'success',
            'system' => [
                'uptime' => $this->getSystemUptime(),
                'memory' => $this->getMemoryUsage(),
                'load' => [
                    'now' => $load[0],
                    '5min' => $load[1],
                    '15min' => $load[2]
                ]
            ],
            'database' => $dbStatus,
            'filesystem' => $fsStatus,
            'timestamp' => time()
        ];
    }
    
    /**
     * 获取系统统计信息
     * 
     * @param array $requestData 请求数据
     * @param array $params URL参数
     * @return array 响应数据
     */
    public function getStatistics($requestData = [], $params = [])
    {
        // 这里通常会从数据库或缓存中获取统计信息
        // 示例数据
        return [
            'status' => 'success',
            'users' => [
                'total' => 1250,
                'active' => 827,
                'new_today' => 15
            ],
            'system' => [
                'requests_today' => 12500,
                'average_response_time' => 0.238, // 秒
                'errors_today' => 23
            ],
            'resources' => [
                'disk_usage' => 78, // 百分比
                'memory_usage' => 45, // 百分比
                'cpu_usage' => 32 // 百分比
            ],
            'timestamp' => time()
        ];
    }
    
    /**
     * 检查数据库连接状态
     * 
     * @return array 数据库状态
     */
    private function checkDatabaseConnection()
    {
        try {
            // 尝试连接到数据库
            // 通常会使用现有的数据库连接类
            $dbConfig = include CONFIG_PATH . '/database.php';
            $status = [
                'status' => true,
                'message' => '数据库连接正常',
                'type' => $dbConfig['default'] ?? 'unknown'
            ];
        } catch (\Exception $e) {
            $status = [
                'status' => false,
                'message' => '数据库连接失败: ' . $e->getMessage(),
                'type' => $dbConfig['default'] ?? 'unknown'
            ];
        }
        
        return $status;
    }
    
    /**
     * 检查文件系统状态
     * 
     * @return array 文件系统状态
     */
    private function checkFileSystem()
    {
        $storagePath = STORAGE_PATH;
        $totalSpace = disk_total_space($storagePath);
        $freeSpace = disk_free_space($storagePath);
        $usedSpace = $totalSpace - $freeSpace;
        $usedPercent = ($usedSpace / $totalSpace) * 100;
        
        $status = [
            'status' => ($freeSpace > 1073741824), // 至少1GB可用空间
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percent_used' => round($usedPercent, 2)
        ];
        
        // 检查写权限
        $testFile = $storagePath . '/test_write_' . time() . '.tmp';
        try {
            $handle = fopen($testFile, 'w');
            if ($handle) {
                fwrite($handle, 'test');
                fclose($handle);
                unlink($testFile);
                $status['writable'] = true;
            } else {
                $status['writable'] = false;
            }
        } catch (\Exception $e) {
            $status['writable'] = false;
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    /**
     * 获取系统运行时间
     * 
     * @return string 格式化的运行时间
     */
    private function getSystemUptime()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows系统
            return 'N/A'; // Windows不容易获取uptime
        } else {
            // Linux/Unix系统
            $uptime = shell_exec('uptime -p');
            return trim($uptime ?: 'unknown');
        }
    }
    
    /**
     * 获取内存使用情况
     * 
     * @return array 内存使用情况
     */
    private function getMemoryUsage()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows系统
            return [
                'total' => 'N/A',
                'used' => 'N/A',
                'free' => 'N/A',
                'percent_used' => 'N/A'
            ];
        } else {
            // Linux/Unix系统
            $free = shell_exec('free -b');
            if ($free) {
                $lines = explode("\n", trim($free));
                $memory = explode(" ", preg_replace('/\s+/', ' ', $lines[1]));
                
                $total = $memory[1];
                $used = $memory[2];
                $free = $memory[3];
                $percentUsed = ($used / $total) * 100;
                
                return [
                    'total' => $this->formatBytes($total),
                    'used' => $this->formatBytes($used),
                    'free' => $this->formatBytes($free),
                    'percent_used' => round($percentUsed, 2)
                ];
            }
            
            return [
                'total' => 'unknown',
                'used' => 'unknown',
                'free' => 'unknown',
                'percent_used' => 0
            ];
        }
    }
    
    /**
     * 格式化字节数为人类可读的格式
     * 
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化的字节数
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