<?php

namespace AlingAi\Services;

use AlingAi\Utils\Logger;
use Psr\Log\LogLevel;

/**
 * 日志服务类
 * 提供结构化日志记录和管理功能
 */
class LoggingService
{
    private const DEFAULT_LOG_LEVEL = LogLevel::INFO;
    private const MAX_LOG_FILE_SIZE = 50 * 1024 * 1024; // 50MB
    private const MAX_LOG_FILES = 10;
    
    private string $logDir;
    private string $logLevel;
    private array $context;
    
    public function __construct(string $logDir = 'logs/', string $logLevel = self::DEFAULT_LOG_LEVEL)
    {
        $this->logDir = rtrim($logDir, '/') . '/';
        $this->logLevel = $logLevel;
        $this->context = [];
        
        $this->ensureLogDirectory();
    }
    
    /**
     * 记录紧急日志
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    
    /**
     * 记录警报日志
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    
    /**
     * 记录严重错误日志
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    
    /**
     * 记录错误日志
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    
    /**
     * 记录警告日志
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    
    /**
     * 记录通知日志
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    
    /**
     * 记录信息日志
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    
    /**
     * 记录调试日志
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
    
    /**
     * 记录日志
     */
    public function log(string $level, string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }
        
        $logEntry = $this->formatLogEntry($level, $message, $context);
        $this->writeLog($level, $logEntry);
          // 同时使用简单Logger记录
        Logger::info($message, $context);
    }
    
    /**
     * 设置全局上下文
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }
    
    /**
     * 添加全局上下文
     */
    public function addContext(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }
    
    /**
     * 记录API请求日志
     */
    public function logApiRequest(array $requestData): void
    {
        $context = [
            'type' => 'api_request',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip' => $this->getClientIp(),
            'request_time' => microtime(true),
            'request_data' => $requestData
        ];
        
        $this->info('API请求', $context);
    }
    
    /**
     * 记录API响应日志
     */
    public function logApiResponse(array $responseData, float $startTime): void
    {
        $context = [
            'type' => 'api_response',
            'response_time' => round((microtime(true) - $startTime) * 1000, 2),
            'memory_usage' => memory_get_peak_usage(true),
            'response_data' => $responseData
        ];
        
        $this->info('API响应', $context);
    }
    
    /**
     * 记录数据库查询日志
     */
    public function logDatabaseQuery(string $query, array $params = [], float $executionTime = 0): void
    {
        $context = [
            'type' => 'database_query',
            'query' => $query,
            'params' => $params,
            'execution_time' => round($executionTime * 1000, 2) . 'ms'
        ];
        
        $this->debug('数据库查询', $context);
    }
    
    /**
     * 记录缓存操作日志
     */
    public function logCacheOperation(string $operation, string $key, $value = null): void
    {
        $context = [
            'type' => 'cache_operation',
            'operation' => $operation,
            'key' => $key
        ];
        
        if ($value !== null && $operation === 'set') {
            $context['value_size'] = strlen(serialize($value));
        }
        
        $this->debug('缓存操作', $context);
    }
    
    /**
     * 记录用户操作日志
     */
    public function logUserAction(int $userId, string $action, array $details = []): void
    {
        $context = [
            'type' => 'user_action',
            'user_id' => $userId,
            'action' => $action,
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'details' => $details
        ];
        
        $this->info('用户操作', $context);
    }
    
    /**
     * 记录安全事件日志
     */
    public function logSecurityEvent(string $event, array $details = []): void
    {
        $context = [
            'type' => 'security_event',
            'event' => $event,
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'details' => $details
        ];
        
        $this->warning('安全事件', $context);
    }
    
    /**
     * 记录性能日志
     */
    public function logPerformance(string $operation, float $duration, array $metrics = []): void
    {
        $context = [
            'type' => 'performance',
            'operation' => $operation,
            'duration' => round($duration * 1000, 2) . 'ms',
            'memory_usage' => memory_get_peak_usage(true),
            'metrics' => $metrics
        ];
        
        if ($duration > 1.0) {
            $this->warning('性能警告', $context);
        } else {
            $this->info('性能监控', $context);
        }
    }
    
    /**
     * 获取日志统计
     */
    public function getLogStats(string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $logFile = $this->getLogFilePath('application', $date);
        
        if (!file_exists($logFile)) {
            return ['error' => '日志文件不存在'];
        }
        
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        
        $stats = [
            'total_entries' => 0,
            'by_level' => [],
            'by_hour' => [],
            'errors' => [],
            'performance_issues' => []
        ];
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $entry = json_decode($line, true);
            if (!$entry) continue;
            
            $stats['total_entries']++;
            
            // 按级别统计
            $level = $entry['level'] ?? 'unknown';
            $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;
            
            // 按小时统计
            $hour = date('H', strtotime($entry['timestamp'] ?? ''));
            $stats['by_hour'][$hour] = ($stats['by_hour'][$hour] ?? 0) + 1;
            
            // 收集错误
            if (in_array($level, [LogLevel::ERROR, LogLevel::CRITICAL, LogLevel::EMERGENCY])) {
                $stats['errors'][] = [
                    'timestamp' => $entry['timestamp'],
                    'level' => $level,
                    'message' => $entry['message']
                ];
            }
            
            // 收集性能问题
            if (isset($entry['context']['type']) && $entry['context']['type'] === 'performance') {
                $duration = floatval(str_replace('ms', '', $entry['context']['duration'] ?? '0'));
                if ($duration > 1000) {
                    $stats['performance_issues'][] = $entry;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * 清理过期日志
     */
    public function cleanupOldLogs(int $daysToKeep = 30): int
    {
        $deletedCount = 0;
        $cutoffDate = date('Y-m-d', strtotime("-{$daysToKeep} days"));
        
        $files = glob($this->logDir . '*.log');
        foreach ($files as $file) {
            $basename = basename($file, '.log');
            $parts = explode('-', $basename);
            
            if (count($parts) >= 4) {
                $fileDate = implode('-', array_slice($parts, -3));
                if ($fileDate < $cutoffDate) {
                    if (unlink($file)) {
                        $deletedCount++;
                    }
                }
            }
        }
        
        $this->info('清理过期日志', ['deleted_count' => $deletedCount, 'days_to_keep' => $daysToKeep]);
        
        return $deletedCount;
    }
    
    /**
     * 检查是否应该记录日志
     */
    private function shouldLog(string $level): bool
    {
        $levels = [
            LogLevel::EMERGENCY => 0,
            LogLevel::ALERT => 1,
            LogLevel::CRITICAL => 2,
            LogLevel::ERROR => 3,
            LogLevel::WARNING => 4,
            LogLevel::NOTICE => 5,
            LogLevel::INFO => 6,
            LogLevel::DEBUG => 7
        ];
        
        $currentLevelValue = $levels[$this->logLevel] ?? 6;
        $logLevelValue = $levels[$level] ?? 6;
        
        return $logLevelValue <= $currentLevelValue;
    }
    
    /**
     * 格式化日志条目
     */
    private function formatLogEntry(string $level, string $message, array $context): string
    {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => array_merge($this->context, $context),
            'memory_usage' => memory_get_usage(true),
            'process_id' => getmypid()
        ];
        
        // 添加请求信息
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $entry['request'] = [
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'ip' => $this->getClientIp()
            ];
        }
        
        return json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * 写入日志
     */
    private function writeLog(string $level, string $logEntry): void
    {
        $logFile = $this->getLogFilePath($this->getLogCategory($level));
        
        // 检查日志文件大小并轮转
        if (file_exists($logFile) && filesize($logFile) > self::MAX_LOG_FILE_SIZE) {
            $this->rotateLogFile($logFile);
        }
        
        file_put_contents($logFile, $logEntry . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 获取日志类别
     */
    private function getLogCategory(string $level): string
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
                return 'error';
            case LogLevel::WARNING:
                return 'warning';
            case LogLevel::DEBUG:
                return 'debug';
            default:
                return 'application';
        }
    }
    
    /**
     * 获取日志文件路径
     */
    private function getLogFilePath(string $category, string $date = null): string
    {
        $date = $date ?? date('Y-m-d');
        return $this->logDir . "{$category}-{$date}.log";
    }
    
    /**
     * 轮转日志文件
     */
    private function rotateLogFile(string $logFile): void
    {
        $pathInfo = pathinfo($logFile);
        $baseName = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'log';
        $directory = $pathInfo['dirname'];
        
        // 查找现有的轮转文件
        $rotatedFiles = glob("{$directory}/{$baseName}.*.{$extension}");
        $maxNumber = 0;
        
        foreach ($rotatedFiles as $file) {
            if (preg_match('/\.(\d+)\.' . $extension . '$/', $file, $matches)) {
                $maxNumber = max($maxNumber, intval($matches[1]));
            }
        }
        
        // 删除过旧的文件
        if ($maxNumber >= self::MAX_LOG_FILES) {
            $oldFile = "{$directory}/{$baseName}." . ($maxNumber - self::MAX_LOG_FILES + 1) . ".{$extension}";
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // 重命名当前文件
        $newFileName = "{$directory}/{$baseName}." . ($maxNumber + 1) . ".{$extension}";
        rename($logFile, $newFileName);
    }
    
    /**
     * 确保日志目录存在
     */
    private function ensureLogDirectory(): void
    {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
