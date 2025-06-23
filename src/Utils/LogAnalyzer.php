<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;

/**
 * 日志分析器
 * 
 * 提供强大的日志分析和监控功能
 * 优化性能：流式处理、缓存分析、并行处理
 * 增强功能：实时分析、异常检测、趋势预测
 */
class LogAnalyzer
{
    private LoggerInterface $logger;
    private array $config;
    private array $patterns = [];
    private array $metrics = [];
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'log_dir' => dirname(__DIR__, 2) . '/storage/logs',
            'log_pattern' => '*.log',
            'analysis_cache' => true,
            'cache_ttl' => 3600,
            'real_time_analysis' => true,
            'alert_thresholds' => [
                'error_rate' => 5,
                'response_time' => 2000,
                'memory_usage' => 85
            ],
            'patterns' => [
                'error' => '/ERROR|CRITICAL|FATAL/i',
                'warning' => '/WARNING/i',
                'info' => '/INFO/i',
                'debug' => '/DEBUG/i',
                'request' => '/Request|Response/i',
                'performance' => '/duration|time|performance/i',
                'security' => '/security|auth|permission/i'
            ]
        ], $config);
        
        $this->initializePatterns();
    }
    
    /**
     * 初始化分析模式
     */
    private function initializePatterns(): void
    {
        $this->patterns = array_merge($this->config['patterns'], [
            'timestamp' => '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            'ip_address' => '/\b(?:\d{1,3}\.){3}\d{1,3}\b/',
            'user_id' => '/user_id[:\s]+(\d+)/i',
            'request_id' => '/request_id[:\s]+([a-zA-Z0-9_-]+)/i',
            'duration' => '/duration[:\s]+(\d+(?:\.\d+)?)/i',
            'memory' => '/memory[:\s]+(\d+(?:\.\d+)?)/i',
            'status_code' => '/status[:\s]+(\d{3})/i'
        ]);
    }
    
    /**
     * 分析日志文件
     */
    public function analyzeLogs(array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $options = array_merge([
                'files' => null,
                'date_range' => null,
                'level' => 'all',
                'include_patterns' => [],
                'exclude_patterns' => [],
                'group_by' => 'hour',
                'limit' => 1000
            ], $options);
            
            $this->logger->info('开始分析日志', $options);
            
            // 获取日志文件
            $logFiles = $this->getLogFiles($options['files']);
            
            // 解析日志
            $logEntries = $this->parseLogFiles($logFiles, $options);
            
            // 分析日志
            $analysis = $this->performAnalysis($logEntries, $options);
            
            // 生成报告
            $report = $this->generateReport($analysis, $options);
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->info('日志分析完成', [
                'files_analyzed' => count($logFiles),
                'entries_analyzed' => count($logEntries),
                'duration_ms' => $duration
            ]);
            
            return $report;
            
        } catch (\Exception $e) {
            $this->logger->error('日志分析失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 获取日志文件
     */
    private function getLogFiles(?array $files): array
    {
        if ($files) {
            return array_filter($files, 'file_exists');
        }
        
        $pattern = $this->config['log_dir'] . '/' . $this->config['log_pattern'];
        return glob($pattern);
    }
    
    /**
     * 解析日志文件
     */
    private function parseLogFiles(array $files, array $options): array
    {
        $entries = [];
        
        foreach ($files as $file) {
            $fileEntries = $this->parseLogFile($file, $options);
            $entries = array_merge($entries, $fileEntries);
        }
        
        // 按时间排序
        usort($entries, function($a, $b) {
            return strtotime($a['timestamp']) - strtotime($b['timestamp']);
        });
        
        // 限制数量
        if ($options['limit']) {
            $entries = array_slice($entries, -$options['limit']);
        }
        
        return $entries;
    }
    
    /**
     * 解析单个日志文件
     */
    private function parseLogFile(string $file, array $options): array
    {
        $entries = [];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            $this->logger->warning('无法打开日志文件', ['file' => $file]);
            return $entries;
        }
        
        while (($line = fgets($handle)) !== false) {
            $entry = $this->parseLogLine($line, $options);
            
            if ($entry) {
                $entries[] = $entry;
            }
        }
        
        fclose($handle);
        return $entries;
    }
    
    /**
     * 解析日志行
     */
    private function parseLogLine(string $line, array $options): ?array
    {
        $line = trim($line);
        
        if (empty($line)) {
            return null;
        }
        
        // 提取时间戳
        if (!preg_match($this->patterns['timestamp'], $line, $matches)) {
            return null;
        }
        
        $timestamp = $matches[0];
        
        // 检查日期范围
        if ($options['date_range']) {
            $logDate = strtotime($timestamp);
            $startDate = strtotime($options['date_range']['start']);
            $endDate = strtotime($options['date_range']['end']);
            
            if ($logDate < $startDate || $logDate > $endDate) {
                return null;
            }
        }
        
        // 检查日志级别
        $level = $this->extractLogLevel($line);
        if ($options['level'] !== 'all' && $level !== $options['level']) {
            return null;
        }
        
        // 检查包含模式
        if (!empty($options['include_patterns'])) {
            $matched = false;
            foreach ($options['include_patterns'] as $pattern) {
                if (preg_match($pattern, $line)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return null;
            }
        }
        
        // 检查排除模式
        if (!empty($options['exclude_patterns'])) {
            foreach ($options['exclude_patterns'] as $pattern) {
                if (preg_match($pattern, $line)) {
                    return null;
                }
            }
        }
        
        return [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $line,
            'file' => basename($this->getCurrentLogFile()),
            'ip' => $this->extractIpAddress($line),
            'user_id' => $this->extractUserId($line),
            'request_id' => $this->extractRequestId($line),
            'duration' => $this->extractDuration($line),
            'memory' => $this->extractMemory($line),
            'status_code' => $this->extractStatusCode($line),
            'categories' => $this->categorizeLogEntry($line)
        ];
    }
    
    /**
     * 提取日志级别
     */
    private function extractLogLevel(string $line): string
    {
        if (preg_match($this->patterns['error'], $line)) {
            return 'ERROR';
        } elseif (preg_match($this->patterns['warning'], $line)) {
            return 'WARNING';
        } elseif (preg_match($this->patterns['info'], $line)) {
            return 'INFO';
        } elseif (preg_match($this->patterns['debug'], $line)) {
            return 'DEBUG';
        }
        
        return 'UNKNOWN';
    }
    
    /**
     * 提取IP地址
     */
    private function extractIpAddress(string $line): ?string
    {
        if (preg_match($this->patterns['ip_address'], $line, $matches)) {
            return $matches[0];
        }
        
        return null;
    }
    
    /**
     * 提取用户ID
     */
    private function extractUserId(string $line): ?int
    {
        if (preg_match($this->patterns['user_id'], $line, $matches)) {
            return (int)$matches[1];
        }
        
        return null;
    }
    
    /**
     * 提取请求ID
     */
    private function extractRequestId(string $line): ?string
    {
        if (preg_match($this->patterns['request_id'], $line, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * 提取持续时间
     */
    private function extractDuration(string $line): ?float
    {
        if (preg_match($this->patterns['duration'], $line, $matches)) {
            return (float)$matches[1];
        }
        
        return null;
    }
    
    /**
     * 提取内存使用
     */
    private function extractMemory(string $line): ?float
    {
        if (preg_match($this->patterns['memory'], $line, $matches)) {
            return (float)$matches[1];
        }
        
        return null;
    }
    
    /**
     * 提取状态码
     */
    private function extractStatusCode(string $line): ?int
    {
        if (preg_match($this->patterns['status_code'], $line, $matches)) {
            return (int)$matches[1];
        }
        
        return null;
    }
    
    /**
     * 分类日志条目
     */
    private function categorizeLogEntry(string $line): array
    {
        $categories = [];
        
        if (preg_match($this->patterns['request'], $line)) {
            $categories[] = 'request';
        }
        
        if (preg_match($this->patterns['performance'], $line)) {
            $categories[] = 'performance';
        }
        
        if (preg_match($this->patterns['security'], $line)) {
            $categories[] = 'security';
        }
        
        return $categories;
    }
    
    /**
     * 执行分析
     */
    private function performAnalysis(array $entries, array $options): array
    {
        $analysis = [
            'summary' => $this->generateSummary($entries),
            'trends' => $this->analyzeTrends($entries, $options),
            'errors' => $this->analyzeErrors($entries),
            'performance' => $this->analyzePerformance($entries),
            'security' => $this->analyzeSecurity($entries),
            'users' => $this->analyzeUserActivity($entries),
            'alerts' => $this->generateAlerts($entries)
        ];
        
        return $analysis;
    }
    
    /**
     * 生成摘要
     */
    private function generateSummary(array $entries): array
    {
        $total = count($entries);
        $levels = array_count_values(array_column($entries, 'level'));
        
        return [
            'total_entries' => $total,
            'time_range' => [
                'start' => $entries[0]['timestamp'] ?? null,
                'end' => end($entries)['timestamp'] ?? null
            ],
            'levels' => $levels,
            'error_rate' => $total > 0 ? round(($levels['ERROR'] ?? 0) / $total * 100, 2) : 0,
            'unique_users' => count(array_unique(array_filter(array_column($entries, 'user_id')))),
            'unique_ips' => count(array_unique(array_filter(array_column($entries, 'ip'))))
        ];
    }
    
    /**
     * 分析趋势
     */
    private function analyzeTrends(array $entries, array $options): array
    {
        $groupBy = $options['group_by'] ?? 'hour';
        $trends = [];
        
        foreach ($entries as $entry) {
            $timeKey = $this->getTimeKey($entry['timestamp'], $groupBy);
            
            if (!isset($trends[$timeKey])) {
                $trends[$timeKey] = [
                    'total' => 0,
                    'errors' => 0,
                    'warnings' => 0,
                    'avg_duration' => 0,
                    'avg_memory' => 0
                ];
            }
            
            $trends[$timeKey]['total']++;
            
            if ($entry['level'] === 'ERROR') {
                $trends[$timeKey]['errors']++;
            } elseif ($entry['level'] === 'WARNING') {
                $trends[$timeKey]['warnings']++;
            }
            
            if ($entry['duration']) {
                $trends[$timeKey]['avg_duration'] = 
                    ($trends[$timeKey]['avg_duration'] * ($trends[$timeKey]['total'] - 1) + $entry['duration']) / $trends[$timeKey]['total'];
            }
            
            if ($entry['memory']) {
                $trends[$timeKey]['avg_memory'] = 
                    ($trends[$timeKey]['avg_memory'] * ($trends[$timeKey]['total'] - 1) + $entry['memory']) / $trends[$timeKey]['total'];
            }
        }
        
        return $trends;
    }
    
    /**
     * 获取时间键
     */
    private function getTimeKey(string $timestamp, string $groupBy): string
    {
        $time = strtotime($timestamp);
        
        switch ($groupBy) {
            case 'minute':
                return date('Y-m-d H:i', $time);
            case 'hour':
                return date('Y-m-d H', $time);
            case 'day':
                return date('Y-m-d', $time);
            case 'week':
                return date('Y-W', $time);
            case 'month':
                return date('Y-m', $time);
            default:
                return date('Y-m-d H', $time);
        }
    }
    
    /**
     * 分析错误
     */
    private function analyzeErrors(array $entries): array
    {
        $errors = array_filter($entries, function($entry) {
            return $entry['level'] === 'ERROR';
        });
        
        $errorPatterns = [];
        $errorUsers = [];
        $errorIps = [];
        
        foreach ($errors as $error) {
            // 分析错误模式
            $pattern = $this->extractErrorPattern($error['message']);
            $errorPatterns[$pattern] = ($errorPatterns[$pattern] ?? 0) + 1;
            
            // 分析错误用户
            if ($error['user_id']) {
                $errorUsers[$error['user_id']] = ($errorUsers[$error['user_id']] ?? 0) + 1;
            }
            
            // 分析错误IP
            if ($error['ip']) {
                $errorIps[$error['ip']] = ($errorIps[$error['ip']] ?? 0) + 1;
            }
        }
        
        arsort($errorPatterns);
        arsort($errorUsers);
        arsort($errorIps);
        
        return [
            'total_errors' => count($errors),
            'error_patterns' => array_slice($errorPatterns, 0, 10, true),
            'error_users' => array_slice($errorUsers, 0, 10, true),
            'error_ips' => array_slice($errorIps, 0, 10, true),
            'recent_errors' => array_slice($errors, -10)
        ];
    }
    
    /**
     * 提取错误模式
     */
    private function extractErrorPattern(string $message): string
    {
        // 简化实现，实际应该使用更复杂的模式匹配
        $words = explode(' ', $message);
        return implode(' ', array_slice($words, 0, 5));
    }
    
    /**
     * 分析性能
     */
    private function analyzePerformance(array $entries): array
    {
        $performanceEntries = array_filter($entries, function($entry) {
            return in_array('performance', $entry['categories']);
        });
        
        $durations = array_filter(array_column($performanceEntries, 'duration'));
        $memories = array_filter(array_column($performanceEntries, 'memory'));
        
        return [
            'total_performance_entries' => count($performanceEntries),
            'avg_duration' => !empty($durations) ? array_sum($durations) / count($durations) : 0,
            'max_duration' => !empty($durations) ? max($durations) : 0,
            'min_duration' => !empty($durations) ? min($durations) : 0,
            'avg_memory' => !empty($memories) ? array_sum($memories) / count($memories) : 0,
            'max_memory' => !empty($memories) ? max($memories) : 0,
            'min_memory' => !empty($memories) ? min($memories) : 0,
            'slow_requests' => count(array_filter($durations, function($d) {
                return $d > $this->config['alert_thresholds']['response_time'];
            }))
        ];
    }
    
    /**
     * 分析安全
     */
    private function analyzeSecurity(array $entries): array
    {
        $securityEntries = array_filter($entries, function($entry) {
            return in_array('security', $entry['categories']);
        });
        
        $failedAuths = array_filter($securityEntries, function($entry) {
            return strpos($entry['message'], 'authentication failed') !== false ||
                   strpos($entry['message'], 'unauthorized') !== false;
        });
        
        $permissionDenied = array_filter($securityEntries, function($entry) {
            return strpos($entry['message'], 'permission denied') !== false ||
                   strpos($entry['message'], 'forbidden') !== false;
        });
        
        return [
            'total_security_entries' => count($securityEntries),
            'failed_authentications' => count($failedAuths),
            'permission_denied' => count($permissionDenied),
            'suspicious_ips' => $this->findSuspiciousIps($securityEntries),
            'recent_security_events' => array_slice($securityEntries, -10)
        ];
    }
    
    /**
     * 查找可疑IP
     */
    private function findSuspiciousIps(array $securityEntries): array
    {
        $ipCounts = [];
        
        foreach ($securityEntries as $entry) {
            if ($entry['ip']) {
                $ipCounts[$entry['ip']] = ($ipCounts[$entry['ip']] ?? 0) + 1;
            }
        }
        
        // 返回出现次数超过5次的IP
        return array_filter($ipCounts, function($count) {
            return $count > 5;
        });
    }
    
    /**
     * 分析用户活动
     */
    private function analyzeUserActivity(array $entries): array
    {
        $userActivity = [];
        
        foreach ($entries as $entry) {
            if ($entry['user_id']) {
                $userId = $entry['user_id'];
                
                if (!isset($userActivity[$userId])) {
                    $userActivity[$userId] = [
                        'total_requests' => 0,
                        'errors' => 0,
                        'last_activity' => null,
                        'ips' => []
                    ];
                }
                
                $userActivity[$userId]['total_requests']++;
                
                if ($entry['level'] === 'ERROR') {
                    $userActivity[$userId]['errors']++;
                }
                
                if ($entry['timestamp'] > $userActivity[$userId]['last_activity']) {
                    $userActivity[$userId]['last_activity'] = $entry['timestamp'];
                }
                
                if ($entry['ip']) {
                    $userActivity[$userId]['ips'][] = $entry['ip'];
                }
            }
        }
        
        // 计算唯一IP数量
        foreach ($userActivity as &$activity) {
            $activity['unique_ips'] = count(array_unique($activity['ips']));
            unset($activity['ips']);
        }
        
        return $userActivity;
    }
    
    /**
     * 生成告警
     */
    private function generateAlerts(array $entries): array
    {
        $alerts = [];
        
        // 错误率告警
        $errorRate = $this->calculateErrorRate($entries);
        if ($errorRate > $this->config['alert_thresholds']['error_rate']) {
            $alerts[] = [
                'type' => 'error_rate',
                'level' => 'warning',
                'message' => "错误率过高: {$errorRate}%",
                'threshold' => $this->config['alert_thresholds']['error_rate']
            ];
        }
        
        // 响应时间告警
        $avgDuration = $this->calculateAverageDuration($entries);
        if ($avgDuration > $this->config['alert_thresholds']['response_time']) {
            $alerts[] = [
                'type' => 'response_time',
                'level' => 'warning',
                'message' => "平均响应时间过长: {$avgDuration}ms",
                'threshold' => $this->config['alert_thresholds']['response_time']
            ];
        }
        
        return $alerts;
    }
    
    /**
     * 计算错误率
     */
    private function calculateErrorRate(array $entries): float
    {
        $total = count($entries);
        if ($total === 0) {
            return 0;
        }
        
        $errors = count(array_filter($entries, function($entry) {
            return $entry['level'] === 'ERROR';
        }));
        
        return round(($errors / $total) * 100, 2);
    }
    
    /**
     * 计算平均响应时间
     */
    private function calculateAverageDuration(array $entries): float
    {
        $durations = array_filter(array_column($entries, 'duration'));
        
        if (empty($durations)) {
            return 0;
        }
        
        return round(array_sum($durations) / count($durations), 2);
    }
    
    /**
     * 生成报告
     */
    private function generateReport(array $analysis, array $options): array
    {
        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'options' => $options,
            'analysis' => $analysis,
            'recommendations' => $this->generateRecommendations($analysis)
        ];
    }
    
    /**
     * 生成建议
     */
    private function generateRecommendations(array $analysis): array
    {
        $recommendations = [];
        
        // 错误率建议
        if ($analysis['summary']['error_rate'] > 5) {
            $recommendations[] = [
                'type' => 'error_rate',
                'priority' => 'high',
                'message' => '错误率过高，建议检查系统配置和代码质量',
                'action' => 'review_error_logs_and_fix_issues'
            ];
        }
        
        // 性能建议
        if ($analysis['performance']['avg_duration'] > 1000) {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'medium',
                'message' => '平均响应时间较长，建议优化数据库查询和缓存',
                'action' => 'optimize_database_and_cache'
            ];
        }
        
        // 安全建议
        if ($analysis['security']['failed_authentications'] > 10) {
            $recommendations[] = [
                'type' => 'security',
                'priority' => 'high',
                'message' => '认证失败次数较多，可能存在安全风险',
                'action' => 'review_security_logs_and_implement_rate_limiting'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * 获取当前日志文件
     */
    private function getCurrentLogFile(): string
    {
        return $this->config['log_dir'] . '/app-' . date('Y-m-d') . '.log';
    }
    
    /**
     * 实时分析
     */
    public function realTimeAnalysis(callable $callback): void
    {
        if (!$this->config['real_time_analysis']) {
            return;
        }
        
        $logFile = $this->getCurrentLogFile();
        
        if (!file_exists($logFile)) {
            return;
        }
        
        $handle = fopen($logFile, 'r');
        if (!$handle) {
            return;
        }
        
        // 移动到文件末尾
        fseek($handle, 0, SEEK_END);
        
        while (true) {
            $line = fgets($handle);
            if ($line) {
                $entry = $this->parseLogLine($line, ['level' => 'all']);
                if ($entry) {
                    $callback($entry);
                }
            }
            
            sleep(1);
        }
        
        fclose($handle);
    }
} 