<?php // Logs Controller

namespace App\Controllers;

use App\Core\Controller;

/**
 * 日志管理控制器
 * 负责处理日志管理相关请求
 */
class LogsController extends Controller
{
    /**
     * 日志目录路径
     * @var string
     */
    private $logsPath;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct(];
        $this->logsPath = BASE_PATH . '/storage/logs';
    }
    
    /**
     * 显示日志管理首页
     * @return void
     */
    public function index()
    {
        // 获取日志概览数据
        $logsOverview = $this->getLogsOverview(];
        
        // 获取最近的日志文件
        $recentLogs = $this->getRecentLogFiles(];
        
        // 渲染视图
        $this->view('logs.index', [
            'logsOverview' => $logsOverview,
            'recentLogs' => $recentLogs,
            'pageTitle' => 'IT运维中心 - 日志管理'
        ]];
    }
    
    /**
     * 显示系统日志页面
     * @return void
     */
    public function system()
    {
        // 获取系统日志
        $logFile = $this->input('file', 'system.log'];
        $page = $this->input('page', 1];
        $perPage = $this->input('per_page', 100];
        
        // 读取日志内容
        $logContent = $this->readLogFile($logFile, $page, $perPage];
        
        // 渲染视图
        $this->view('logs.system', [
            'logFile' => $logFile,
            'logContent' => $logContent,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $logContent['totalPages'], 
            'pageTitle' => 'IT运维中心 - 系统日志'
        ]];
    }
    
    /**
     * 显示错误日志页面
     * @return void
     */
    public function errors()
    {
        // 获取错误日志
        $logFile = $this->input('file', 'errors.log'];
        $page = $this->input('page', 1];
        $perPage = $this->input('per_page', 100];
        
        // 读取日志内容
        $logContent = $this->readLogFile($logFile, $page, $perPage];
        
        // 渲染视图
        $this->view('logs.errors', [
            'logFile' => $logFile,
            'logContent' => $logContent,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $logContent['totalPages'], 
            'pageTitle' => 'IT运维中心 - 错误日志'
        ]];
    }
    
    /**
     * 显示访问日志页面
     * @return void
     */
    public function access()
    {
        // 获取访问日志
        $logFile = $this->input('file', 'access.log'];
        $page = $this->input('page', 1];
        $perPage = $this->input('per_page', 100];
        
        // 读取日志内容
        $logContent = $this->readLogFile($logFile, $page, $perPage];
        
        // 渲染视图
        $this->view('logs.access', [
            'logFile' => $logFile,
            'logContent' => $logContent,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $logContent['totalPages'], 
            'pageTitle' => 'IT运维中心 - 访问日志'
        ]];
    }
    
    /**
     * 显示安全日志页面
     * @return void
     */
    public function security()
    {
        // 获取安全日志
        $logFile = $this->input('file', 'security_events.log'];
        $page = $this->input('page', 1];
        $perPage = $this->input('per_page', 100];
        
        // 读取日志内容
        $logContent = $this->readLogFile($logFile, $page, $perPage];
        
        // 渲染视图
        $this->view('logs.security', [
            'logFile' => $logFile,
            'logContent' => $logContent,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $logContent['totalPages'], 
            'pageTitle' => 'IT运维中心 - 安全日志'
        ]];
    }
    
    /**
     * 搜索日志
     * @return void
     */
    public function search()
    {
        // 获取搜索参数
        $keyword = $this->input('keyword'];
        $logType = $this->input('log_type', 'all'];
        $startDate = $this->input('start_date'];
        $endDate = $this->input('end_date'];
        
        // 执行搜索
        $searchResults = $this->searchLogs($keyword, $logType, $startDate, $endDate];
        
        // 返回JSON结果
        $this->json($searchResults];
    }
    
    /**
     * 下载日志文件
     * @return void
     */
    public function download()
    {
        // 获取文件名
        $fileName = $this->input('file'];
        
        // 验证参数
        if (empty($fileName)) {
            $this->json([
                'success' => false,
                'message' => '文件名不能为空'
            ],  400];
            return;
        }
        
        // 检查文件是否存在
        $filePath = $this->logsPath . '/' . $fileName;
        if (!file_exists($filePath)) {
            $this->json([
                'success' => false,
                'message' => '文件不存在'
            ],  404];
            return;
        }
        
        // 设置下载头
        header('Content-Description: File Transfer'];
        header('Content-Type: application/octet-stream'];
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"'];
        header('Expires: 0'];
        header('Cache-Control: must-revalidate'];
        header('Pragma: public'];
        header('Content-Length: ' . filesize($filePath)];
        
        // 清除输出缓冲
        ob_clean(];
        flush(];
        
        // 输出文件内容
        readfile($filePath];
        exit;
    }
    
    /**
     * 清空日志文件
     * @return void
     */
    public function clear()
    {
        // 获取文件名
        $fileName = $this->input('file'];
        
        // 验证参数
        if (empty($fileName)) {
            $this->json([
                'success' => false,
                'message' => '文件名不能为空'
            ],  400];
            return;
        }
        
        // 检查文件是否存在
        $filePath = $this->logsPath . '/' . $fileName;
        if (!file_exists($filePath)) {
            $this->json([
                'success' => false,
                'message' => '文件不存在'
            ],  404];
            return;
        }
        
        // 清空文件内容
        if (file_put_contents($filePath, '') !== false) {
            $this->json([
                'success' => true,
                'message' => '日志文件已清空'
            ]];
        } else {
            $this->json([
                'success' => false,
                'message' => '清空日志文件失败'
            ],  500];
        }
    }
    
    /**
     * 获取日志概览数据
     * @return array 日志概览数据
     */
    private function getLogsOverview()
    {
        // 检查日志目录是否存在
        if (!is_dir($this->logsPath)) {
            mkdir($this->logsPath, 0755, true];
        }
        
        // 获取日志文件列表
        $logFiles = glob($this->logsPath . '/*.log'];
        
        // 统计日志文件数量
        $totalLogFiles = count($logFiles];
        
        // 统计日志文件总大小
        $totalSize = 0;
        foreach ($logFiles as $file) {
            $totalSize += filesize($file];
        }
        
        // 统计今日日志数量
        $todayLogs = 0;
        $today = date('Y-m-d'];
        foreach ($logFiles as $file) {
            if (date('Y-m-d', filemtime($file)) === $today) {
                $todayLogs++;
            }
        }
        
        // 统计错误日志数量
        $errorLogs = count(glob($this->logsPath . '/*error*.log')];
        
        return [
            'totalLogFiles' => $totalLogFiles,
            'totalSize' => $this->formatBytes($totalSize],
            'todayLogs' => $todayLogs,
            'errorLogs' => $errorLogs
        ];
    }
    
    /**
     * 获取最近的日志文件
     * @param int $limit 文件数量限制
     * @return array 日志文件列表
     */
    private function getRecentLogFiles($limit = 10)
    {
        // 检查日志目录是否存在
        if (!is_dir($this->logsPath)) {
            mkdir($this->logsPath, 0755, true];
        }
        
        // 获取日志文件列表
        $logFiles = glob($this->logsPath . '/*.log'];
        
        // 按修改时间排序
        usort($logFiles, function($a, $b) {
            return filemtime($b) - filemtime($a];
        }];
        
        // 限制数量
        $logFiles = array_slice($logFiles, 0, $limit];
        
        // 格式化结果
        $result = [];
        foreach ($logFiles as $file) {
            $fileName = basename($file];
            $fileSize = filesize($file];
            $modifiedTime = filemtime($file];
            $lineCount = $this->countFileLines($file];
            
            // 确定日志类型
            $type = 'other';
            if (strpos($fileName, 'system') !== false) {
                $type = 'system';
            } elseif (strpos($fileName, 'error') !== false) {
                $type = 'error';
            } elseif (strpos($fileName, 'access') !== false) {
                $type = 'access';
            } elseif (strpos($fileName, 'security') !== false) {
                $type = 'security';
            }
            
            $result[] = [
                'name' => $fileName,
                'path' => $file,
                'size' => $this->formatBytes($fileSize],
                'modifiedTime' => date('Y-m-d H:i:s', $modifiedTime],
                'lineCount' => $lineCount,
                'type' => $type
            ];
        }
        
        return $result;
    }
    
    /**
     * 读取日志文件内容
     * @param string $fileName 文件名
     * @param int $page 页码
     * @param int $perPage 每页行数
     * @return array 日志内容
     */
    private function readLogFile($fileName, $page = 1, $perPage = 100)
    {
        // 构建文件路径
        $filePath = $this->logsPath . '/' . $fileName;
        
        // 检查文件是否存在
        if (!file_exists($filePath)) {
            return [
                'lines' => [], 
                'totalLines' => 0,
                'totalPages' => 0,
                'error' => '文件不存在'
            ];
        }
        
        // 统计文件总行数
        $totalLines = $this->countFileLines($filePath];
        
        // 计算总页数
        $totalPages = ceil($totalLines / $perPage];
        
        // 计算起始行和结束行
        $startLine = ($page - 1) * $perPage;
        $endLine = $startLine + $perPage - 1;
        
        // 读取指定范围的行
        $lines = [];
        $lineNumber = 0;
        $file = fopen($filePath, 'r'];
        
        if ($file) {
            while (($line = fgets($file)) !== false) {
                if ($lineNumber >= $startLine && $lineNumber <= $endLine) {
                    $lines[] = [
                        'number' => $lineNumber + 1,
                        'content' => trim($line],
                        'timestamp' => $this->extractTimestamp($line)
                    ];
                }
                
                $lineNumber++;
                
                // 如果已经读取到了结束行，就可以停止了
                if ($lineNumber > $endLine) {
                    break;
                }
            }
            
            fclose($file];
        }
        
        return [
            'lines' => $lines,
            'totalLines' => $totalLines,
            'totalPages' => $totalPages,
            'error' => null
        ];
    }
    
    /**
     * 搜索日志
     * @param string $keyword 关键词
     * @param string $logType 日志类型
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @return array 搜索结果
     */
    private function searchLogs($keyword, $logType = 'all', $startDate = null, $endDate = null)
    {
        // 检查日志目录是否存在
        if (!is_dir($this->logsPath)) {
            mkdir($this->logsPath, 0755, true];
        }
        
        // 确定要搜索的文件
        $filesToSearch = [];
        if ($logType === 'all') {
            $filesToSearch = glob($this->logsPath . '/*.log'];
        } else {
            switch ($logType) {
                case 'system':
                    $filesToSearch = glob($this->logsPath . '/*system*.log'];
                    break;
                case 'error':
                    $filesToSearch = glob($this->logsPath . '/*error*.log'];
                    break;
                case 'access':
                    $filesToSearch = glob($this->logsPath . '/*access*.log'];
                    break;
                case 'security':
                    $filesToSearch = glob($this->logsPath . '/*security*.log'];
                    break;
            }
        }
        
        // 搜索结果
        $results = [];
        
        // 遍历文件
        foreach ($filesToSearch as $file) {
            $fileName = basename($file];
            $fileHandle = fopen($file, 'r'];
            
            if ($fileHandle) {
                $lineNumber = 0;
                
                while (($line = fgets($fileHandle)) !== false) {
                    $lineNumber++;
                    
                    // 检查是否包含关键词
                    if (!empty($keyword) && stripos($line, $keyword) === false) {
                        continue;
                    }
                    
                    // 提取时间戳
                    $timestamp = $this->extractTimestamp($line];
                    
                    // 检查日期范围
                    if (!empty($timestamp)) {
                        $date = date('Y-m-d', strtotime($timestamp)];
                        
                        if (!empty($startDate) && $date < $startDate) {
                            continue;
                        }
                        
                        if (!empty($endDate) && $date > $endDate) {
                            continue;
                        }
                    }
                    
                    // 添加到结果中
                    $results[] = [
                        'file' => $fileName,
                        'line' => $lineNumber,
                        'content' => trim($line],
                        'timestamp' => $timestamp
                    ];
                    
                    // 限制结果数量
                    if (count($results) >= 1000) {
                        break 2; // 跳出两层循环
                    }
                }
                
                fclose($fileHandle];
            }
        }
        
        // 按时间戳排序
        usort($results, function($a, $b) {
            if (empty($a['timestamp']) && empty($b['timestamp'])) {
                return 0;
            }
            
            if (empty($a['timestamp'])) {
                return 1;
            }
            
            if (empty($b['timestamp'])) {
                return -1;
            }
            
            return strtotime($b['timestamp']) - strtotime($a['timestamp']];
        }];
        
        return [
            'success' => true,
            'count' => count($results],
            'results' => $results
        ];
    }
    
    /**
     * 统计文件行数
     * @param string $file 文件路径
     * @return int 行数
     */
    private function countFileLines($file)
    {
        $lineCount = 0;
        $handle = fopen($file, 'r'];
        
        if ($handle) {
            while (fgets($handle) !== false) {
                $lineCount++;
            }
            
            fclose($handle];
        }
        
        return $lineCount;
    }
    
    /**
     * 格式化字节数为可读形式
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化后的字符串
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)];
        $pow = min($pow, count($units) - 1];
        
        $bytes /= pow(1024, $pow];
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * 从日志行中提取时间戳
     * @param string $line 日志行
     * @return string|null 时间戳
     */
    private function extractTimestamp($line)
    {
        // 尝试匹配常见的时间戳格式
        $patterns = [
            // [2023-06-25 10:30:45]
            '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]/',
            // 2023-06-25T10:30:45+00:00
            '/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[\+\-]\d{2}:\d{2})/',
            // 2023-06-25 10:30:45
            '/(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})/',
            // 25/Jun/2023:10:30:45 +0000
            '/(\d{2}\/[A-Za-z) {3}\/\d{4}:\d{2}:\d{2}:\d{2}\s[\+\-]\d{4})/',
            // Jun 25 10:30:45
            '/([A-Za-z) {3}\s+\d{1,2}\s\d{2}:\d{2}:\d{2})/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}

