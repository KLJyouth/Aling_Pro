<?php

namespace AlingAi\Services;

use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * 增强日志服务
 * 提供与UnifiedAdminController兼容的日志功能
 */
class EnhancedLoggingService
{
    private DatabaseServiceInterface $db;
    private CacheService $cache;
    private LoggerInterface $logger;
    private array $config;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->config = $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        return [
            'log_dir' => __DIR__ . '/../../storage/logs',
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'max_files' => 10,
            'retention_days' => 30,
            'log_levels' => [
                LogLevel::EMERGENCY,
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::NOTICE,
                LogLevel::INFO,
                LogLevel::DEBUG
            ],
            'file_names' => [
                'application' => 'app.log',
                'error' => 'error.log',
                'security' => 'security.log',
                'performance' => 'performance.log',
                'api' => 'api.log'
            ]
        ];
    }

    /**
     * 记录日志
     */
    public function log(string $level, string $message, array $context = [], string $channel = 'application'): void
    {
        try {
            $logEntry = $this->formatLogEntry($level, $message, $context);
            $this->writeToFile($logEntry, $channel);
            
            // 同时使用系统日志记录器
            $this->logger->log($level, $message, $context);
            
            // 缓存最近的日志条目
            $this->cacheRecentLogs($level, $message, $context, $channel);
            
        } catch (\Exception $e) {
            // 使用系统错误日志记录失败
            error_log("日志记录失败: " . $e->getMessage());
        }
    }

    /**
     * 紧急日志
     */
    public function emergency(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context, $channel);
    }

    /**
     * 警报日志
     */
    public function alert(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log(LogLevel::ALERT, $message, $context, $channel);
    }

    /**
     * 严重错误日志
     */
    public function critical(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context, $channel);
    }

    /**
     * 错误日志
     */
    public function error(string $message, array $context = [], string $channel = 'error'): void
    {
        $this->log(LogLevel::ERROR, $message, $context, $channel);
    }

    /**
     * 警告日志
     */
    public function warning(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log(LogLevel::WARNING, $message, $context, $channel);
    }

    /**
     * 通知日志
     */
    public function notice(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log(LogLevel::NOTICE, $message, $context, $channel);
    }

    /**
     * 信息日志
     */
    public function info(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log(LogLevel::INFO, $message, $context, $channel);
    }

    /**
     * 调试日志
     */
    public function debug(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log(LogLevel::DEBUG, $message, $context, $channel);
    }

    /**
     * 安全日志
     */
    public function security(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context, 'security');
    }

    /**
     * 性能日志
     */
    public function performance(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context, 'performance');
    }

    /**
     * API日志
     */
    public function api(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context, 'api');
    }

    /**
     * 获取日志列表
     */
    public function getLogs(array $filters = []): array
    {
        $level = $filters['level'] ?? 'all';
        $channel = $filters['channel'] ?? 'all';
        $date = $filters['date'] ?? date('Y-m-d');
        $limit = (int) ($filters['limit'] ?? 100);

        try {
            // 首先尝试从缓存获取最近的日志
            $cacheKey = "recent_logs_{$level}_{$channel}_{$date}";
            $cachedLogs = $this->cache->get($cacheKey);
            
            if ($cachedLogs && is_array($cachedLogs)) {
                return array_slice($cachedLogs, 0, $limit);
            }

            // 从文件读取日志
            $logs = $this->readLogsFromFiles($level, $channel, $date, $limit);
            
            // 缓存结果
            $this->cache->set($cacheKey, $logs, 300); // 5分钟缓存
            
            return $logs;

        } catch (\Exception $e) {
            $this->logger->error('获取日志失败', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 搜索日志
     */
    public function searchLogs(string $query, array $filters = []): array
    {
        $level = $filters['level'] ?? 'all';
        $channel = $filters['channel'] ?? 'all';
        $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $limit = (int) ($filters['limit'] ?? 100);

        try {
            $results = [];
            $logDir = $this->config['log_dir'];
            
            if (!is_dir($logDir)) {
                return $results;
            }

            // 获取要搜索的文件列表
            $filesToSearch = $this->getLogFilesInDateRange($startDate, $endDate, $channel);
            
            foreach ($filesToSearch as $file) {
                $fileResults = $this->searchInFile($file, $query, $level);
                $results = array_merge($results, $fileResults);
                
                if (count($results) >= $limit) {
                    break;
                }
            }

            // 按时间倒序排序
            usort($results, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return array_slice($results, 0, $limit);

        } catch (\Exception $e) {
            $this->logger->error('搜索日志失败', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 获取日志统计
     */
    public function getLogStatistics(string $period = 'today'): array
    {
        try {
            $stats = [
                'period' => $period,
                'timestamp' => date('Y-m-d H:i:s'),
                'total_logs' => 0,
                'by_level' => [],
                'by_channel' => [],
                'errors_count' => 0,
                'warnings_count' => 0
            ];

            // 从缓存获取统计信息
            $cacheKey = "log_stats_{$period}";
            $cachedStats = $this->cache->get($cacheKey);
            
            if ($cachedStats && is_array($cachedStats)) {
                return $cachedStats;
            }

            // 计算统计信息
            $dateRange = $this->getDateRangeForPeriod($period);
            $logs = $this->getLogsInDateRange($dateRange['start'], $dateRange['end']);

            foreach ($logs as $log) {
                $stats['total_logs']++;
                
                // 按级别统计
                $level = $log['level'] ?? 'unknown';
                $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;
                
                // 按通道统计
                $channel = $log['channel'] ?? 'unknown';
                $stats['by_channel'][$channel] = ($stats['by_channel'][$channel] ?? 0) + 1;
                
                // 错误和警告计数
                if (in_array($level, [LogLevel::ERROR, LogLevel::CRITICAL, LogLevel::EMERGENCY, LogLevel::ALERT])) {
                    $stats['errors_count']++;
                } elseif ($level === LogLevel::WARNING) {
                    $stats['warnings_count']++;
                }
            }

            // 缓存统计结果
            $this->cache->set($cacheKey, $stats, 1800); // 30分钟缓存

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error('获取日志统计失败', ['error' => $e->getMessage()]);
            return [
                'period' => $period,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * 清理旧日志
     */
    public function cleanupOldLogs(): array
    {
        $cleaned = 0;
        $errors = [];
        $retentionDays = $this->config['retention_days'];

        try {
            $logDir = $this->config['log_dir'];
            
            if (!is_dir($logDir)) {
                return [
                    'cleaned_files' => 0,
                    'errors' => ['日志目录不存在']
                ];
            }

            $cutoffTime = time() - ($retentionDays * 24 * 3600);
            $files = scandir($logDir);

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $logDir . '/' . $file;
                
                if (is_file($filePath) && filemtime($filePath) < $cutoffTime) {
                    try {
                        if (unlink($filePath)) {
                            $cleaned++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "无法删除 {$file}: " . $e->getMessage();
                    }
                }
            }

            // 清理日志相关的缓存
            $this->clearLogCache();

            return [
                'cleaned_files' => $cleaned,
                'retention_days' => $retentionDays,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            return [
                'cleaned_files' => $cleaned,
                'error' => $e->getMessage(),
                'errors' => $errors
            ];
        }
    }

    /**
     * 导出日志
     */
    public function exportLogs(array $filters = []): array
    {
        $format = $filters['format'] ?? 'json';
        $level = $filters['level'] ?? 'all';
        $channel = $filters['channel'] ?? 'all';
        $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $filters['end_date'] ?? date('Y-m-d');

        try {
            $logs = $this->getLogsInDateRange($startDate, $endDate, $level, $channel);
            
            $exportData = [
                'export_date' => date('Y-m-d H:i:s'),
                'filters' => [
                    'level' => $level,
                    'channel' => $channel,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'total_logs' => count($logs),
                'logs' => $logs
            ];

            $filename = $this->generateExportFilename($format, $startDate, $endDate);
            $exportPath = $this->config['log_dir'] . '/exports';
            
            if (!is_dir($exportPath)) {
                mkdir($exportPath, 0755, true);
            }

            $fullPath = $exportPath . '/' . $filename;

            switch ($format) {
                case 'csv':
                    $this->exportToCsv($logs, $fullPath);
                    break;
                case 'json':
                default:
                    file_put_contents($fullPath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    break;
            }

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $fullPath,
                'size' => filesize($fullPath),
                'total_logs' => count($logs)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 格式化日志条目
     */
    private function formatLogEntry(string $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        return "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
    }

    /**
     * 写入文件
     */
    private function writeToFile(string $logEntry, string $channel): void
    {
        $logDir = $this->config['log_dir'];
        
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                throw new \RuntimeException("无法创建日志目录: {$logDir}");
            }
        }

        $filename = $this->config['file_names'][$channel] ?? 'app.log';
        $filePath = $logDir . '/' . $filename;

        // 检查文件大小，如果需要则轮转
        if (file_exists($filePath) && filesize($filePath) > $this->config['max_file_size']) {
            $this->rotateLogFile($filePath);
        }

        if (file_put_contents($filePath, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            throw new \RuntimeException("无法写入日志文件: {$filePath}");
        }
    }

    /**
     * 轮转日志文件
     */
    private function rotateLogFile(string $filePath): void
    {
        $maxFiles = $this->config['max_files'];
        $pathInfo = pathinfo($filePath);
        $baseName = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';
        $directory = $pathInfo['dirname'];

        // 删除最旧的文件
        $oldestFile = $directory . '/' . $baseName . '.' . $maxFiles . ($extension ? '.' . $extension : '');
        if (file_exists($oldestFile)) {
            unlink($oldestFile);
        }

        // 重命名现有文件
        for ($i = $maxFiles - 1; $i >= 1; $i--) {
            $oldFile = $directory . '/' . $baseName . '.' . $i . ($extension ? '.' . $extension : '');
            $newFile = $directory . '/' . $baseName . '.' . ($i + 1) . ($extension ? '.' . $extension : '');
            
            if (file_exists($oldFile)) {
                rename($oldFile, $newFile);
            }
        }

        // 重命名当前文件
        $newFile = $directory . '/' . $baseName . '.1' . ($extension ? '.' . $extension : '');
        rename($filePath, $newFile);
    }

    /**
     * 缓存最近的日志
     */
    private function cacheRecentLogs(string $level, string $message, array $context, string $channel): void
    {
        $cacheKey = 'recent_logs_all';
        $recentLogs = $this->cache->get($cacheKey) ?: [];
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'channel' => $channel
        ];

        array_unshift($recentLogs, $logEntry);
        
        // 只保留最近的100条日志
        if (count($recentLogs) > 100) {
            $recentLogs = array_slice($recentLogs, 0, 100);
        }

        $this->cache->set($cacheKey, $recentLogs, 3600); // 1小时缓存
    }

    /**
     * 从文件读取日志
     */
    private function readLogsFromFiles(string $level, string $channel, string $date, int $limit): array
    {
        $logs = [];
        $logDir = $this->config['log_dir'];

        if (!is_dir($logDir)) {
            return $logs;
        }

        $files = $this->getLogFiles($channel);
        
        foreach ($files as $file) {
            $filePath = $logDir . '/' . $file;
            if (file_exists($filePath)) {
                $fileLogs = $this->parseLogFile($filePath, $level, $date);
                $logs = array_merge($logs, $fileLogs);
            }
        }

        // 按时间倒序排序
        usort($logs, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($logs, 0, $limit);
    }

    /**
     * 解析日志文件
     */
    private function parseLogFile(string $filePath, string $level, string $date): array
    {
        $logs = [];
        $handle = fopen($filePath, 'r');
        
        if (!$handle) {
            return $logs;
        }

        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^\[([^\]]+)\]\s+(\w+):\s+(.+)$/', trim($line), $matches)) {
                $timestamp = $matches[1];
                $logLevel = $matches[2];
                $message = $matches[3];

                // 过滤日期
                if ($date !== 'all' && strpos($timestamp, $date) !== 0) {
                    continue;
                }

                // 过滤级别
                if ($level !== 'all' && strtolower($logLevel) !== strtolower($level)) {
                    continue;
                }

                $logs[] = [
                    'timestamp' => $timestamp,
                    'level' => $logLevel,
                    'message' => $message,
                    'file' => basename($filePath)
                ];
            }
        }

        fclose($handle);
        return $logs;
    }

    /**
     * 获取日志文件列表
     */
    private function getLogFiles(string $channel): array
    {
        if ($channel === 'all') {
            return array_values($this->config['file_names']);
        }

        return [$this->config['file_names'][$channel] ?? 'app.log'];
    }

    /**
     * 其他辅助方法...
     */
    private function getDateRangeForPeriod(string $period): array
    {
        switch ($period) {
            case 'today':
                return [
                    'start' => date('Y-m-d'),
                    'end' => date('Y-m-d')
                ];
            case 'week':
                return [
                    'start' => date('Y-m-d', strtotime('-6 days')),
                    'end' => date('Y-m-d')
                ];
            case 'month':
                return [
                    'start' => date('Y-m-d', strtotime('-29 days')),
                    'end' => date('Y-m-d')
                ];
            default:
                return [
                    'start' => date('Y-m-d'),
                    'end' => date('Y-m-d')
                ];
        }
    }

    private function getLogsInDateRange(string $startDate, string $endDate, string $level = 'all', string $channel = 'all'): array
    {
        // 简化实现，返回空数组
        return [];
    }

    private function getLogFilesInDateRange(string $startDate, string $endDate, string $channel): array
    {
        return $this->getLogFiles($channel);
    }

    private function searchInFile(string $file, string $query, string $level): array
    {
        return [];
    }

    private function clearLogCache(): void
    {
        $keys = ['recent_logs_all', 'log_stats_today', 'log_stats_week', 'log_stats_month'];
        foreach ($keys as $key) {
            $this->cache->delete($key);
        }
    }

    private function generateExportFilename(string $format, string $startDate, string $endDate): string
    {
        return "logs_export_{$startDate}_to_{$endDate}." . $format;
    }

    private function exportToCsv(array $logs, string $filePath): void
    {
        $handle = fopen($filePath, 'w');
        
        // 写入CSV头
        fputcsv($handle, ['Timestamp', 'Level', 'Channel', 'Message']);
        
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log['timestamp'] ?? '',
                $log['level'] ?? '',
                $log['channel'] ?? '',
                $log['message'] ?? ''
            ]);
        }
        
        fclose($handle);
    }

    /**
     * 健康检查方法（API兼容）
     */
    public function healthCheck(): array
    {
        try {
            $logDir = $this->config['log_dir'];
            $isWritable = is_dir($logDir) && is_writable($logDir);
            
            return [
                'status' => $isWritable ? 'healthy' : 'critical',
                'log_directory' => $logDir,
                'writable' => $isWritable,
                'disk_usage' => $this->getLogDirectorySize()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 获取日志目录大小
     */
    private function getLogDirectorySize(): int
    {
        $size = 0;
        $logDir = $this->config['log_dir'];
        
        if (is_dir($logDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($logDir)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return $size;
    }
}
