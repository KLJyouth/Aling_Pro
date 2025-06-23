<?php
/**
 * 性能监控服务
 * 实时监控系统性能、资源使用情况和用户行为
 */

namespace AlingAi\Services;

class PerformanceMonitorService {
    
    private $metrics = [];
    private $thresholds = [];
    private $alerts = [];
    private $logFile;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../../logs/performance.log';
        $this->initializeThresholds();
        $this->startMonitoring();
    }
    
    /**
     * 初始化性能阈值
     */
    private function initializeThresholds() {
        $this->thresholds = [
            'memory_usage' => 85, // 内存使用率超过85%警告
            'response_time' => 2000, // 响应时间超过2秒警告
            'error_rate' => 5, // 错误率超过5%警告
            'cpu_usage' => 80, // CPU使用率超过80%警告
            'concurrent_users' => 1000, // 并发用户超过1000警告
            'database_connections' => 90 // 数据库连接数超过90%警告
        ];
    }
    
    /**
     * 开始监控
     */
    public function startMonitoring() {
        $this->recordMetric('monitoring_start', [
            'timestamp' => time(),
            'server_info' => $this->getServerInfo()
        ]);
    }
    
    /**
     * 记录性能指标
     */
    public function recordMetric($type, $data) {
        $metric = [
            'timestamp' => microtime(true),
            'type' => $type,
            'data' => $data,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        $this->metrics[] = $metric;
        $this->checkThresholds($metric);
        $this->writeToLog($metric);
        
        // 保持最近1000条记录
        if (count($this->metrics) > 1000) {
            array_shift($this->metrics);        }
    }
    
    /**
     * 记录API请求
     */
    public function recordApiRequest($requestData) {
        $this->recordMetric('api_request_start', $requestData);
    }
    
    /**
     * 监控API请求性能
     */
    public function monitorApiRequest($endpoint, $method, $startTime, $endTime, $responseCode, $error = null) {
        $duration = ($endTime - $startTime) * 1000; // 转换为毫秒
        
        $this->recordMetric('api_request', [
            'endpoint' => $endpoint,
            'method' => $method,
            'duration_ms' => $duration,
            'response_code' => $responseCode,
            'error' => $error,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip_address' => $this->getClientIp()
        ]);
        
        // 检查慢查询
        if ($duration > $this->thresholds['response_time']) {
            $this->triggerAlert('slow_request', [
                'endpoint' => $endpoint,
                'duration' => $duration
            ]);
        }
    }
    
    /**
     * 监控数据库查询性能
     */
    public function monitorDatabaseQuery($query, $startTime, $endTime, $error = null) {
        $duration = ($endTime - $startTime) * 1000;
        
        $this->recordMetric('database_query', [
            'query_hash' => md5($query),
            'duration_ms' => $duration,
            'error' => $error,
            'query_type' => $this->getQueryType($query)
        ]);
    }
    
    /**
     * 监控用户活动
     */
    public function monitorUserActivity($userId, $action, $details = []) {
        $this->recordMetric('user_activity', [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'session_id' => session_id(),
            'ip_address' => $this->getClientIp()
        ]);
    }
    
    /**
     * 获取系统状态
     */
    public function getSystemStatus() {
        $status = [
            'timestamp' => time(),
            'memory' => $this->getMemoryStatus(),
            'disk' => $this->getDiskStatus(),
            'database' => $this->getDatabaseStatus(),
            'cache' => $this->getCacheStatus(),
            'websocket' => $this->getWebSocketStatus(),
            'recent_metrics' => array_slice($this->metrics, -50),
            'active_alerts' => $this->getActiveAlerts()
        ];
        
        return $status;
    }
    
    /**
     * 获取内存状态
     */
    private function getMemoryStatus() {
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseBytes($memoryLimit);
        $currentUsage = memory_get_usage(true);
        $peakUsage = memory_get_peak_usage(true);
        
        return [
            'limit' => $memoryLimit,
            'limit_bytes' => $memoryLimitBytes,
            'current_usage' => $currentUsage,
            'current_usage_mb' => round($currentUsage / 1024 / 1024, 2),
            'peak_usage' => $peakUsage,
            'peak_usage_mb' => round($peakUsage / 1024 / 1024, 2),
            'usage_percentage' => $memoryLimitBytes > 0 ? round(($currentUsage / $memoryLimitBytes) * 100, 2) : 0,
            'available' => $memoryLimitBytes - $currentUsage,
            'available_mb' => round(($memoryLimitBytes - $currentUsage) / 1024 / 1024, 2)
        ];
    }
    
    /**
     * 获取磁盘状态
     */
    private function getDiskStatus() {
        $rootPath = __DIR__ . '/../../';
        $totalSpace = disk_total_space($rootPath);
        $freeSpace = disk_free_space($rootPath);
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'total_space' => $totalSpace,
            'total_space_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
            'free_space' => $freeSpace,
            'free_space_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
            'used_space' => $usedSpace,
            'used_space_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2)
        ];
    }
    
    /**
     * 获取数据库状态
     */
    private function getDatabaseStatus() {
        try {
            $pdo = $this->getDatabaseConnection();
            if (!$pdo) {
                return ['status' => 'disconnected', 'error' => 'Unable to connect'];
            }
            
            // 获取数据库统计信息
            $stats = [
                'status' => 'connected',
                'connections' => $this->getDatabaseConnections($pdo),
                'queries' => $this->getDatabaseQueries($pdo),
                'size' => $this->getDatabaseSize($pdo)
            ];
            
            return $stats;
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取缓存状态
     */
    private function getCacheStatus() {
        // 检查不同缓存系统的状态
        $status = [
            'opcache' => $this->getOpcacheStatus(),
            'redis' => $this->getRedisStatus(),
            'file_cache' => $this->getFileCacheStatus()
        ];
        
        return $status;
    }
    
    /**
     * 获取WebSocket状态
     */
    private function getWebSocketStatus() {
        // 检查WebSocket服务器是否运行
        $websocketPort = 8080;
        $connection = @fsockopen('127.0.0.1', $websocketPort, $errno, $errstr, 1);
        
        if ($connection) {
            fclose($connection);
            return [
                'status' => 'running',
                'port' => $websocketPort,
                'connections' => $this->getWebSocketConnections()
            ];
        } else {
            return [
                'status' => 'stopped',
                'port' => $websocketPort,
                'error' => $errstr
            ];
        }
    }
    
    /**
     * 检查阈值并触发警告
     */
    private function checkThresholds($metric) {
        $memoryStatus = $this->getMemoryStatus();
        
        // 检查内存使用率
        if ($memoryStatus['usage_percentage'] > $this->thresholds['memory_usage']) {
            $this->triggerAlert('high_memory_usage', [
                'usage_percentage' => $memoryStatus['usage_percentage'],
                'threshold' => $this->thresholds['memory_usage']
            ]);
        }
        
        // 检查API响应时间
        if ($metric['type'] === 'api_request' && 
            isset($metric['data']['duration_ms']) && 
            $metric['data']['duration_ms'] > $this->thresholds['response_time']) {
            
            $this->triggerAlert('slow_response', [
                'endpoint' => $metric['data']['endpoint'],
                'duration' => $metric['data']['duration_ms'],
                'threshold' => $this->thresholds['response_time']
            ]);
        }
    }
    
    /**
     * 触发警告
     */
    private function triggerAlert($type, $data) {
        $alert = [
            'type' => $type,
            'timestamp' => time(),
            'data' => $data,
            'severity' => $this->getAlertSeverity($type)
        ];
        
        $this->alerts[] = $alert;
        $this->writeAlertToLog($alert);
        $this->notifyAdministrators($alert);
        
        // 保持最近100条警告
        if (count($this->alerts) > 100) {
            array_shift($this->alerts);
        }
    }
    
    /**
     * 获取活跃警告
     */
    private function getActiveAlerts() {
        $activeAlerts = [];
        $currentTime = time();
        
        foreach ($this->alerts as $alert) {
            // 显示最近1小时的警告
            if ($currentTime - $alert['timestamp'] < 3600) {
                $activeAlerts[] = $alert;
            }
        }
        
        return $activeAlerts;
    }
    
    /**
     * 获取警告严重性
     */
    private function getAlertSeverity($type) {
        $severityMap = [
            'high_memory_usage' => 'warning',
            'slow_response' => 'warning',
            'database_error' => 'critical',
            'websocket_disconnect' => 'info',
            'security_threat' => 'critical'
        ];
        
        return $severityMap[$type] ?? 'info';
    }
    
    /**
     * 写入日志文件
     */    private function writeToLog($metric) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s', (int)$metric['timestamp']),
            'type' => $metric['type'],
            'data' => $metric['data'],
            'memory_mb' => round($metric['memory_usage'] / 1024 / 1024, 2)
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 写入警告日志
     */
    private function writeAlertToLog($alert) {
        $alertLogFile = __DIR__ . '/../../logs/alerts.log';
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s', $alert['timestamp']),
            'type' => $alert['type'],
            'severity' => $alert['severity'],
            'data' => $alert['data']
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($alertLogFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 通知管理员
     */
    private function notifyAdministrators($alert) {
        // 发送邮件通知（如果配置了SMTP）
        // 推送到WebSocket连接的管理员
        // 记录到系统日志
        
        error_log("ALERT [{$alert['severity']}] {$alert['type']}: " . json_encode($alert['data']));
    }
    
    /**
     * 获取服务器信息
     */
    private function getServerInfo() {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'system' => php_uname(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIp() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return 'unknown';
    }
    
    /**
     * 解析字节大小
     */
    private function parseBytes($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }
    
    /**
     * 获取查询类型
     */
    private function getQueryType($query) {
        $query = trim(strtoupper($query));
        
        if (strpos($query, 'SELECT') === 0) return 'SELECT';
        if (strpos($query, 'INSERT') === 0) return 'INSERT';
        if (strpos($query, 'UPDATE') === 0) return 'UPDATE';
        if (strpos($query, 'DELETE') === 0) return 'DELETE';
        if (strpos($query, 'CREATE') === 0) return 'CREATE';
        if (strpos($query, 'DROP') === 0) return 'DROP';
        if (strpos($query, 'ALTER') === 0) return 'ALTER';
        
        return 'OTHER';
    }
    
    /**
     * 获取数据库连接
     */
    private function getDatabaseConnection() {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'alingai';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';
            
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            return new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 获取数据库连接数
     */
    private function getDatabaseConnections($pdo) {
        try {
            $stmt = $pdo->query("SHOW STATUS LIKE 'Threads_connected'");
            $result = $stmt->fetch();
            return (int)$result['Value'];
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取数据库查询统计
     */
    private function getDatabaseQueries($pdo) {
        try {
            $stmt = $pdo->query("SHOW STATUS LIKE 'Queries'");
            $result = $stmt->fetch();
            return (int)$result['Value'];
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取数据库大小
     */
    private function getDatabaseSize($pdo) {
        try {
            $dbname = $_ENV['DB_NAME'] ?? 'alingai';
            $stmt = $pdo->prepare("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                FROM information_schema.tables 
                WHERE table_schema = ?
            ");
            $stmt->execute([$dbname]);
            $result = $stmt->fetch();
            return $result['size_mb'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取OPcache状态
     */
    private function getOpcacheStatus() {
        if (!function_exists('opcache_get_status')) {
            return ['enabled' => false];
        }
        
        $status = opcache_get_status();
        if (!$status) {
            return ['enabled' => false];
        }
        
        return [
            'enabled' => true,
            'cache_full' => $status['cache_full'],
            'restart_pending' => $status['restart_pending'],
            'memory_usage' => $status['memory_usage'],
            'opcache_statistics' => $status['opcache_statistics']
        ];
    }
    
    /**
     * 获取Redis状态
     */
    private function getRedisStatus() {
        try {
            if (!class_exists('Redis')) {
                return ['enabled' => false, 'error' => 'Redis extension not installed'];
            }
            
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            $info = $redis->info();
            $redis->close();
            
            return [
                'enabled' => true,
                'version' => $info['redis_version'] ?? 'unknown',
                'memory_usage' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'enabled' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取文件缓存状态
     */
    private function getFileCacheStatus() {
        $cacheDir = __DIR__ . '/../../cache';
        
        if (!is_dir($cacheDir)) {
            return ['enabled' => false, 'error' => 'Cache directory not found'];
        }
        
        $files = glob($cacheDir . '/*');
        $totalSize = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $totalSize += filesize($file);
            }
        }
        
        return [
            'enabled' => true,
            'files_count' => count($files),
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'cache_dir' => $cacheDir
        ];
    }
    
    /**
     * 获取WebSocket连接数
     */
    private function getWebSocketConnections() {
        // 这里需要与WebSocket服务器通信获取连接数
        // 暂时返回模拟数据
        return [
            'total' => 0,
            'authenticated' => 0,
            'rooms' => []
        ];
    }
    
    /**
     * 生成性能报告
     */
    public function generateReport($timeframe = '1hour') {
        $endTime = time();
        $startTime = $endTime - $this->getTimeframeSeconds($timeframe);
        
        $filteredMetrics = array_filter($this->metrics, function($metric) use ($startTime, $endTime) {
            return $metric['timestamp'] >= $startTime && $metric['timestamp'] <= $endTime;
        });
        
        $report = [
            'timeframe' => $timeframe,
            'start_time' => date('Y-m-d H:i:s', $startTime),
            'end_time' => date('Y-m-d H:i:s', $endTime),
            'total_metrics' => count($filteredMetrics),
            'api_requests' => $this->analyzeApiRequests($filteredMetrics),
            'database_queries' => $this->analyzeDatabaseQueries($filteredMetrics),
            'user_activities' => $this->analyzeUserActivities($filteredMetrics),
            'system_performance' => $this->analyzeSystemPerformance($filteredMetrics),
            'alerts' => $this->getAlertsInTimeframe($startTime, $endTime)
        ];
        
        return $report;
    }
    
    /**
     * 获取时间范围秒数
     */
    private function getTimeframeSeconds($timeframe) {
        $timeframes = [
            '15min' => 900,
            '1hour' => 3600,
            '6hours' => 21600,
            '24hours' => 86400,
            '7days' => 604800
        ];
        
        return $timeframes[$timeframe] ?? 3600;
    }
    
    /**
     * 分析API请求
     */
    private function analyzeApiRequests($metrics) {
        $apiMetrics = array_filter($metrics, function($metric) {
            return $metric['type'] === 'api_request';
        });
        
        if (empty($apiMetrics)) {
            return ['total' => 0];
        }
        
        $totalRequests = count($apiMetrics);
        $durations = array_column(array_column($apiMetrics, 'data'), 'duration_ms');
        $responseCodes = array_column(array_column($apiMetrics, 'data'), 'response_code');
        
        return [
            'total' => $totalRequests,
            'avg_duration_ms' => round(array_sum($durations) / count($durations), 2),
            'max_duration_ms' => max($durations),
            'min_duration_ms' => min($durations),
            'success_rate' => round((count(array_filter($responseCodes, function($code) {
                return $code >= 200 && $code < 300;
            })) / $totalRequests) * 100, 2),
            'error_rate' => round((count(array_filter($responseCodes, function($code) {
                return $code >= 400;
            })) / $totalRequests) * 100, 2)
        ];
    }
    
    /**
     * 分析数据库查询
     */
    private function analyzeDatabaseQueries($metrics) {
        $dbMetrics = array_filter($metrics, function($metric) {
            return $metric['type'] === 'database_query';
        });
        
        if (empty($dbMetrics)) {
            return ['total' => 0];
        }
        
        $totalQueries = count($dbMetrics);
        $durations = array_column(array_column($dbMetrics, 'data'), 'duration_ms');
        
        return [
            'total' => $totalQueries,
            'avg_duration_ms' => round(array_sum($durations) / count($durations), 2),
            'max_duration_ms' => max($durations),
            'slow_queries' => count(array_filter($durations, function($duration) {
                return $duration > 1000; // 超过1秒的查询
            }))
        ];
    }
    
    /**
     * 分析用户活动
     */
    private function analyzeUserActivities($metrics) {
        $userMetrics = array_filter($metrics, function($metric) {
            return $metric['type'] === 'user_activity';
        });
        
        if (empty($userMetrics)) {
            return ['total' => 0];
        }
        
        $userActivities = array_column($userMetrics, 'data');
        $uniqueUsers = array_unique(array_column($userActivities, 'user_id'));
        $actions = array_column($userActivities, 'action');
        
        return [
            'total_activities' => count($userMetrics),
            'unique_users' => count($uniqueUsers),
            'most_common_actions' => array_count_values($actions)
        ];
    }
    
    /**
     * 分析系统性能
     */
    private function analyzeSystemPerformance($metrics) {
        $memoryUsages = array_column($metrics, 'memory_usage');
        
        if (empty($memoryUsages)) {
            return [];
        }
        
        return [
            'avg_memory_usage_mb' => round(array_sum($memoryUsages) / count($memoryUsages) / 1024 / 1024, 2),
            'max_memory_usage_mb' => round(max($memoryUsages) / 1024 / 1024, 2),
            'min_memory_usage_mb' => round(min($memoryUsages) / 1024 / 1024, 2)
        ];
    }
    
    /**
     * 获取时间范围内的警告
     */
    private function getAlertsInTimeframe($startTime, $endTime) {
        return array_filter($this->alerts, function($alert) use ($startTime, $endTime) {
            return $alert['timestamp'] >= $startTime && $alert['timestamp'] <= $endTime;
        });    }
    
    /**
     * 记录错误日志
     */
    public function logError($message, $context = []) {
        $this->recordMetric('error', [
            'message' => $message,
            'context' => $context,
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ]);
    }
    
    /**
     * 记录API调用
     */
    public function recordApiCall($data) {
        $this->recordMetric('api_call', $data);
    }
    
    /**
     * 记录用户活动
     */
    public function logUserActivity($activity, $data = []) {
        $this->recordMetric('user_activity', array_merge([
            'activity' => $activity
        ], $data));
    }
    
    /**
     * 记录API响应
     */
    public function recordApiResponse($data) {
        $this->recordMetric('api_response', $data);
    }
    
    /**
     * 记录请求完成
     */
    public function recordRequestCompletion(float $executionTime): void
    {
        try {
            error_log("Request completed in {$executionTime}s");
        } catch (Exception $e) {
            // 静默忽略错误
        }
    }
}
