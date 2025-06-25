<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * 系统监控控制�?
 * 负责处理系统监控相关请求
 */
class MonitoringController extends Controller
{
    /**
     * 显示监控仪表盘页�?
     * @return void
     */
    public function index()
    {
        // 获取系统信息
        $systemInfo = $this->getSystemInfo(];
        
        // 获取PHP信息
        $phpInfo = $this->getPhpInfo(];
        
        // 获取磁盘使用情况
        $diskUsage = $this->getDiskUsage(];
        
        // 获取内存使用情况
        $memoryUsage = $this->getMemoryUsage(];
        
        // 获取CPU使用情况
        $cpuUsage = $this->getCpuUsage(];
        
        // 获取最近的错误日志
        $errorLogs = $this->getRecentErrorLogs(];
        
        // 渲染视图
        $this->view('monitoring.index', [
            'systemInfo' => $systemInfo,
            'phpInfo' => $phpInfo,
            'diskUsage' => $diskUsage,
            'memoryUsage' => $memoryUsage,
            'cpuUsage' => $cpuUsage,
            'errorLogs' => $errorLogs,
            'pageTitle' => 'IT运维中心 - 系统监控'
        ]];
    }
    
    /**
     * 显示PHP信息页面
     * @return void
     */
    public function phpInfo()
    {
        // 获取PHP信息
        $phpInfo = $this->getPhpInfo(];
        
        // 获取已安装的PHP扩展
        $extensions = $this->getPhpExtensions(];
        
        // 渲染视图
        $this->view('monitoring.php-info', [
            'phpInfo' => $phpInfo,
            'extensions' => $extensions,
            'pageTitle' => 'IT运维中心 - PHP信息'
        ]];
    }
    
    /**
     * 显示系统日志页面
     * @return void
     */
    public function logs()
    {
        // 获取日志类型
        $logType = $this->input('type', 'error'];
        
        // 获取日志行数
        $lines = (int)$this->input('lines', 100];
        
        // 获取日志内容
        $logs = $this->getSystemLogs($logType, $lines];
        
        // 渲染视图
        $this->view('monitoring.logs', [
            'logs' => $logs,
            'logType' => $logType,
            'lines' => $lines,
            'pageTitle' => 'IT运维中心 - 系统日志'
        ]];
    }
    
    /**
     * 显示实时监控页面
     * @return void
     */
    public function realtime()
    {
        // 渲染视图
        $this->view('monitoring.realtime', [
            'pageTitle' => 'IT运维中心 - 实时监控'
        ]];
    }
    
    /**
     * 获取实时系统数据
     * @return void
     */
    public function getRealtimeData()
    {
        // 获取实时系统数据
        $data = [
            'timestamp' => date('Y-m-d H:i:s'],
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'network' => $this->getNetworkUsage()
        ];
        
        // 返回JSON数据
        $this->json($data];
    }
    
    /**
     * 获取系统信息
     * @return array 系统信息
     */
    private function getSystemInfo()
    {
        $info = [
            'os' => PHP_OS,
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'hostname' => gethostname(),
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'server_protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
            'request_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()],
            'timezone' => date_default_timezone_get()
        ];
        
        return $info;
    }
    
    /**
     * 获取PHP信息
     * @return array PHP信息
     */
    private function getPhpInfo()
    {
        $info = [
            'version' => PHP_VERSION,
            'sapi' => php_sapi_name(),
            'os' => PHP_OS,
            'max_execution_time' => ini_get('max_execution_time'],
            'memory_limit' => ini_get('memory_limit'],
            'post_max_size' => ini_get('post_max_size'],
            'upload_max_filesize' => ini_get('upload_max_filesize'],
            'max_input_time' => ini_get('max_input_time'],
            'default_socket_timeout' => ini_get('default_socket_timeout'],
            'display_errors' => ini_get('display_errors'],
            'error_reporting' => ini_get('error_reporting'],
            'log_errors' => ini_get('log_errors'],
            'error_log' => ini_get('error_log'],
            'date.timezone' => ini_get('date.timezone'],
            'disable_functions' => ini_get('disable_functions'],
            'loaded_extensions' => implode(', ', get_loaded_extensions())
        ];
        
        return $info;
    }
    
    /**
     * 获取PHP扩展信息
     * @return array PHP扩展信息
     */
    private function getPhpExtensions()
    {
        $extensions = get_loaded_extensions(];
        $result = [];
        
        foreach ($extensions as $extension) {
            $reflection = new \ReflectionExtension($extension];
            $version = $reflection->getVersion(];
            
            $result[] = [
                'name' => $extension,
                'version' => $version ?: 'Unknown',
            ];
        }
        
        return $result;
    }
    
    /**
     * 获取磁盘使用情况
     * @return array 磁盘使用情况
     */
    private function getDiskUsage()
    {
        $diskTotal = disk_total_space('/'];
        $diskFree = disk_free_space('/'];
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = ($diskUsed / $diskTotal) * 100;
        
        return [
            'total' => $this->formatBytes($diskTotal],
            'free' => $this->formatBytes($diskFree],
            'used' => $this->formatBytes($diskUsed],
            'percent' => round($diskPercent, 2)
        ];
    }
    
    /**
     * 获取内存使用情况
     * @return array 内存使用情况
     */
    private function getMemoryUsage()
    {
        // 在Windows系统上，我们可能无法获取准确的内存信�?
        // 这里提供一个简单的PHP内存使用情况
        $memoryLimit = $this->getBytes(ini_get('memory_limit')];
        $memoryUsage = memory_get_usage(true];
        $memoryPeakUsage = memory_get_peak_usage(true];
        $memoryPercent = ($memoryUsage / $memoryLimit) * 100;
        
        return [
            'limit' => $this->formatBytes($memoryLimit],
            'usage' => $this->formatBytes($memoryUsage],
            'peak' => $this->formatBytes($memoryPeakUsage],
            'percent' => round($memoryPercent, 2)
        ];
    }
    
    /**
     * 获取CPU使用情况
     * @return array CPU使用情况
     */
    private function getCpuUsage()
    {
        // 在Windows系统上，我们可能无法获取准确的CPU信息
        // 这里提供一个模拟的CPU使用情况
        return [
            'usage' => rand(10, 90], // 模拟的CPU使用�?
            'cores' => $this->getCpuCores(),
            'model' => $this->getCpuModel()
        ];
    }
    
    /**
     * 获取CPU核心�?
     * @return int CPU核心�?
     */
    private function getCpuCores()
    {
        $cores = 1;
        
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo'];
            preg_match_all('/^processor/m', $cpuinfo, $matches];
            $cores = count($matches[0]];
        } else if (PHP_OS == 'WINNT') {
            $process = @popen('wmic cpu get NumberOfCores', 'rb'];
            if ($process) {
                fgets($process];
                $cores = intval(fgets($process)];
                pclose($process];
            }
        }
        
        return $cores ?: 1;
    }
    
    /**
     * 获取CPU型号
     * @return string CPU型号
     */
    private function getCpuModel()
    {
        $model = 'Unknown';
        
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo'];
            if (preg_match('/model name\s+:\s+(.+?)$/m', $cpuinfo, $matches)) {
                $model = $matches[1];
            }
        } else if (PHP_OS == 'WINNT') {
            $process = @popen('wmic cpu get Name', 'rb'];
            if ($process) {
                fgets($process];
                $model = trim(fgets($process)];
                pclose($process];
            }
        }
        
        return $model;
    }
    
    /**
     * 获取网络使用情况
     * @return array 网络使用情况
     */
    private function getNetworkUsage()
    {
        // 在Windows系统上，我们可能无法获取准确的网络信�?
        // 这里提供一个模拟的网络使用情况
        return [
            'in' => rand(100, 1000], // 模拟的入站流�?(KB/s)
            'out' => rand(50, 500)   // 模拟的出站流�?(KB/s)
        ];
    }
    
    /**
     * 获取最近的错误日志
     * @param int $lines 日志行数
     * @return array 错误日志
     */
    private function getRecentErrorLogs($lines = 50)
    {
        $logs = [];
        $errorLog = ini_get('error_log'];
        
        if ($errorLog && file_exists($errorLog) && is_readable($errorLog)) {
            $logContent = file($errorLog];
            $logContent = array_slice($logContent, -$lines];
            
            foreach ($logContent as $line) {
                $logs[] = trim($line];
            }
        }
        
        return $logs;
    }
    
    /**
     * 获取系统日志
     * @param string $type 日志类型
     * @param int $lines 日志行数
     * @return array 系统日志
     */
    private function getSystemLogs($type = 'error', $lines = 100)
    {
        $logs = [];
        $logFile = '';
        
        switch ($type) {
            case 'error':
                $logFile = ini_get('error_log'];
                break;
            case 'access':
                // 尝试查找访问日志
                $possiblePaths = [
                    '/var/log/apache2/access.log',
                    '/var/log/httpd/access_log',
                    '/var/log/nginx/access.log'
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path) && is_readable($path)) {
                        $logFile = $path;
                        break;
                    }
                }
                break;
            case 'system':
                // 尝试查找系统日志
                $possiblePaths = [
                    '/var/log/syslog',
                    '/var/log/messages'
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path) && is_readable($path)) {
                        $logFile = $path;
                        break;
                    }
                }
                break;
            case 'app':
                // 应用日志
                $logFile = BASE_PATH . '/storage/logs/app.log';
                break;
        }
        
        if ($logFile && file_exists($logFile) && is_readable($logFile)) {
            // 使用shell命令获取最后的行数，以避免读取大文�?
            if (PHP_OS !== 'WINNT') {
                $command = "tail -n {$lines} " . escapeshellarg($logFile];
                exec($command, $logs];
            } else {
                // Windows不支持tail命令，使用PHP读取
                $file = new \SplFileObject($logFile, 'r'];
                $file->seek(PHP_INT_MAX]; // 移动到文件末�?
                $totalLines = $file->key(]; // 获取总行�?
                
                $startLine = max(0, $totalLines - $lines];
                $file->seek($startLine];
                
                while (!$file->eof()) {
                    $logs[] = trim($file->fgets()];
                }
            }
        }
        
        return $logs;
    }
    
    /**
     * 格式化字节数为人类可读的格式
     * @param int $bytes 字节�?
     * @param int $precision 精度
     * @return string 格式化后的字符串
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        $bytes = max($bytes, 0];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)];
        $pow = min($pow, count($units) - 1];
        
        $bytes /= pow(1024, $pow];
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * 将内存限制字符串转换为字节数
     * @param string $val 内存限制字符�?
     * @return int 字节�?
     */
    private function getBytes($val)
    {
        $val = trim($val];
        $last = strtolower($val[strlen($val)-1]];
        $val = (int)$val;
        
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
} 

