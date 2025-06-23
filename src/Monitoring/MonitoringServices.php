<?php

namespace AlingAi\Monitoring;

use AlingAi\Database\DatabaseManager;
use AlingAi\Performance\CacheManager;
use AlingAi\Services\EnhancedConfigService;

/**
 * 系统监控服务
 */
class SystemMonitor
{
    private static $instance = null;
    private $db;
    private $cache;
    private $config;
    
    private function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->cache = CacheManager::getInstance();
        $this->config = EnhancedConfigService::getInstance();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 收集系统指标
     */
    public function collectMetrics(): array
    {
        return [
            'system' => $this->getSystemMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'application' => $this->getApplicationMetrics(),
            'timestamp' => time()
        ];
    }
    
    /**
     * 记录指标到数据库
     */
    public function recordMetrics(array $metrics): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO system_metrics (metric_type, metric_data, created_at) VALUES (?, ?, NOW())"
            );
            
            foreach ($metrics as $type => $data) {
                if ($type !== 'timestamp') {
                    $stmt->execute([$type, json_encode($data)]);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("记录系统指标失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取历史指标
     */
    public function getHistoricalMetrics(string $type, int $hours = 24): array
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT metric_data, created_at FROM system_metrics 
             WHERE metric_type = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
             ORDER BY created_at DESC"
        );
        $stmt->execute([$type, $hours]);
        
        $results = $stmt->fetchAll();
        
        return array_map(function($row) {
            return [
                'data' => json_decode($row['metric_data'], true),
                'timestamp' => $row['created_at']
            ];
        }, $results);
    }
    
    /**
     * 检查系统健康状态
     */
    public function checkHealth(): array
    {
        $checks = [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'disk_space' => $this->checkDiskSpace(),
            'memory' => $this->checkMemoryUsage(),
            'services' => $this->checkServices()
        ];
        
        $overallHealth = array_reduce($checks, function($carry, $check) {
            return $carry && $check['status'] === 'healthy';
        }, true);
        
        return [
            'overall_status' => $overallHealth ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 获取系统指标
     */
    private function getSystemMetrics(): array
    {
        $loadAvg = sys_getloadavg();
        
        return [
            'cpu_load' => [
                '1min' => $loadAvg[0] ?? 0,
                '5min' => $loadAvg[1] ?? 0,
                '15min' => $loadAvg[2] ?? 0
            ],
            'memory' => [
                'used' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => $this->parseBytes(ini_get('memory_limit'))
            ],
            'disk_space' => [
                'total' => disk_total_space('.'),
                'free' => disk_free_space('.'),
                'used' => disk_total_space('.') - disk_free_space('.')
            ],
            'php' => [
                'version' => PHP_VERSION,
                'max_execution_time' => ini_get('max_execution_time'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize')
            ]
        ];
    }
    
    /**
     * 获取数据库指标
     */
    private function getDatabaseMetrics(): array
    {
        try {
            // 连接状态
            $connectionStatus = $this->db->getConnection() ? 'connected' : 'disconnected';
            
            // 查询统计
            $stmt = $this->db->getConnection()->query("SHOW GLOBAL STATUS LIKE 'Queries'");
            $queries = $stmt->fetch();
            
            $stmt = $this->db->getConnection()->query("SHOW GLOBAL STATUS LIKE 'Slow_queries'");
            $slowQueries = $stmt->fetch();
            
            $stmt = $this->db->getConnection()->query("SHOW GLOBAL STATUS LIKE 'Connections'");
            $connections = $stmt->fetch();
            
            return [
                'status' => $connectionStatus,
                'queries' => (int)($queries['Value'] ?? 0),
                'slow_queries' => (int)($slowQueries['Value'] ?? 0),
                'connections' => (int)($connections['Value'] ?? 0)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取缓存指标
     */
    private function getCacheMetrics(): array
    {
        try {
            // 这里可以根据实际缓存系统获取指标
            return [
                'status' => 'active',
                'memory_usage' => 0, // Redis/Memcached memory usage
                'hit_rate' => 0, // Cache hit rate
                'connections' => 0 // Active connections
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取应用指标
     */
    private function getApplicationMetrics(): array
    {
        return [
            'version' => $this->config->get('APP_VERSION', '1.0.0'),
            'environment' => $this->config->get('APP_ENV', 'production'),
            'debug_mode' => $this->config->getBool('APP_DEBUG', false),
            'uptime' => $this->getUptime()
        ];
    }
    
    /**
     * 检查数据库健康状态
     */
    private function checkDatabaseHealth(): array
    {
        try {
            $start = microtime(true);
            $this->db->getConnection()->query("SELECT 1");
            $responseTime = (microtime(true) - $start) * 1000;
            
            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查缓存健康状态
     */
    private function checkCacheHealth(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test';
            
            $this->cache->set($testKey, $testValue, 60);
            $retrieved = $this->cache->get($testKey);
            $this->cache->delete($testKey);
            
            if ($retrieved === $testValue) {
                return ['status' => 'healthy'];
            } else {
                return [
                    'status' => 'unhealthy',
                    'error' => 'Cache write/read test failed'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查磁盘空间
     */
    private function checkDiskSpace(): array
    {
        $freeSpace = disk_free_space('.');
        $totalSpace = disk_total_space('.');
        $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        
        $status = 'healthy';
        if ($usagePercent > 90) {
            $status = 'critical';
        } elseif ($usagePercent > 80) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'usage_percent' => round($usagePercent, 2),
            'free_space' => $freeSpace,
            'total_space' => $totalSpace
        ];
    }
    
    /**
     * 检查内存使用
     */
    private function checkMemoryUsage(): array
    {
        $used = memory_get_usage(true);
        $limit = $this->parseBytes(ini_get('memory_limit'));
        $usagePercent = ($used / $limit) * 100;
        
        $status = 'healthy';
        if ($usagePercent > 90) {
            $status = 'critical';
        } elseif ($usagePercent > 80) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'usage_percent' => round($usagePercent, 2),
            'used' => $used,
            'limit' => $limit
        ];
    }
    
    /**
     * 检查服务状态
     */
    private function checkServices(): array
    {
        $services = [];
        
        // 检查Nginx
        $services['nginx'] = $this->checkServiceStatus('nginx');
        
        // 检查PHP-FPM
        $services['php-fpm'] = $this->checkServiceStatus('php-fpm');
        
        // 检查MySQL
        $services['mysql'] = $this->checkServiceStatus('mysql');
        
        return $services;
    }
    
    /**
     * 检查单个服务状态
     */
    private function checkServiceStatus(string $service): array
    {
        $command = "systemctl is-active $service 2>/dev/null";
        $output = shell_exec($command);
        $isActive = trim($output) === 'active';
        
        return [
            'status' => $isActive ? 'active' : 'inactive',
            'service' => $service
        ];
    }
    
    /**
     * 获取系统运行时间
     */
    private function getUptime(): int
    {
        $uptime = shell_exec('cat /proc/uptime 2>/dev/null');
        if ($uptime) {
            return (int)floatval(trim($uptime));
        }
        return 0;
    }
    
    /**
     * 解析字节大小
     */
    private function parseBytes(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int)$size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }
}

/**
 * 日志管理服务
 */
class LogManager
{
    private static $instance = null;
    private $logPath;
    private $config;
    
    private function __construct()
    {
        $this->config = EnhancedConfigService::getInstance();
        $this->logPath = $this->config->get('LOG_PATH', '/var/log/alingai');
        
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 记录日志
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'request_id' => $this->getRequestId()
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 写入到对应级别的日志文件
        $filename = $this->logPath . '/' . $level . '-' . date('Y-m-d') . '.log';
        file_put_contents($filename, $logLine, FILE_APPEND | LOCK_EX);
        
        // 同时写入到总日志文件
        $generalFilename = $this->logPath . '/app-' . date('Y-m-d') . '.log';
        file_put_contents($generalFilename, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 记录错误
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }
    
    /**
     * 记录警告
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }
    
    /**
     * 记录信息
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
    
    /**
     * 记录调试信息
     */
    public function debug(string $message, array $context = []): void
    {
        if ($this->config->getBool('APP_DEBUG', false)) {
            $this->log('debug', $message, $context);
        }
    }
    
    /**
     * 获取日志文件列表
     */
    public function getLogFiles(): array
    {
        $files = glob($this->logPath . '/*.log');
        return array_map('basename', $files);
    }
    
    /**
     * 读取日志文件内容
     */
    public function readLogFile(string $filename, int $lines = 100): array
    {
        $filepath = $this->logPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $content = shell_exec("tail -n $lines " . escapeshellarg($filepath));
        $lines = explode("\n", trim($content));
        
        $logs = [];
        foreach ($lines as $line) {
            if (trim($line)) {
                $decoded = json_decode($line, true);
                if ($decoded) {
                    $logs[] = $decoded;
                }
            }
        }
        
        return array_reverse($logs);
    }
    
    /**
     * 清理旧日志文件
     */
    public function cleanupOldLogs(int $daysToKeep = 30): int
    {
        $cutoffDate = time() - ($daysToKeep * 24 * 60 * 60);
        $files = glob($this->logPath . '/*.log');
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffDate) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * 获取请求ID（用于追踪）
     */
    private function getRequestId(): string
    {
        static $requestId = null;
        
        if ($requestId === null) {
            $requestId = substr(md5(uniqid(rand(), true)), 0, 8);
        }
        
        return $requestId;
    }
}
