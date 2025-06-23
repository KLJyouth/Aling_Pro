<?php
/**
 * AlingAi Pro 监控API控制器
 * 提供系统监控数据的REST API接口
 */
namespace AlingAi\Controllers;

use AlingAi\Monitoring\SystemMonitor;
use AlingAi\Services\DatabaseService;
use AlingAi\Services\CacheService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class MonitoringController
{
    private SystemMonitor $monitor;
    private DatabaseService $db;
    private CacheService $cache;
    
    public function __construct(DatabaseService $db, CacheService $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
        
        // 初始化系统监控器
        $logger = new \Monolog\Logger('monitoring');
        $this->monitor = new SystemMonitor($logger);
    }
    
    /**
     * 获取系统实时状态
     */
    public function getSystemStatus(Request $request, Response $response): Response
    {
        try {
            $status = [
                'timestamp' => date('Y-m-d H:i:s'),
                'system_info' => $this->getSystemInfo(),
                'resource_usage' => $this->getResourceUsage(),
                'service_status' => $this->getServiceStatus(),
                'performance_metrics' => $this->getPerformanceMetrics(),
                'health_check' => $this->performHealthCheck()
            ];
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $status,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**
     * 获取系统信息
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'operating_system' => PHP_OS,
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        ];
    }
    
    /**
     * 获取资源使用情况
     */
    private function getResourceUsage(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        // 转换内存限制为字节
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        
        // 获取磁盘信息
        $diskTotal = disk_total_space(__DIR__);
        $diskFree = disk_free_space(__DIR__);
        $diskUsed = $diskTotal - $diskFree;
        
        return [
            'memory' => [
                'current_usage' => $memoryUsage,
                'current_usage_formatted' => $this->formatBytes($memoryUsage),
                'peak_usage' => $memoryPeak,
                'peak_usage_formatted' => $this->formatBytes($memoryPeak),
                'limit' => $memoryLimitBytes,
                'limit_formatted' => $memoryLimit,
                'usage_percentage' => $memoryLimitBytes > 0 ? round(($memoryUsage / $memoryLimitBytes) * 100, 2) : 0
            ],
            'disk' => [
                'total_space' => $diskTotal,
                'total_space_formatted' => $this->formatBytes($diskTotal),
                'used_space' => $diskUsed,
                'used_space_formatted' => $this->formatBytes($diskUsed),
                'free_space' => $diskFree,
                'free_space_formatted' => $this->formatBytes($diskFree),
                'usage_percentage' => round(($diskUsed / $diskTotal) * 100, 2)
            ],
            'cpu' => [
                'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
                'processor_count' => $this->getProcessorCount()
            ]
        ];
    }
    
    /**
     * 获取服务状态
     */
    private function getServiceStatus(): array
    {
        $services = [
            'database' => $this->checkDatabaseStatus(),
            'cache' => $this->checkCacheStatus(),
            'file_system' => $this->checkFileSystemStatus(),
            'php_extensions' => $this->checkPhpExtensions()
        ];
        
        return $services;
    }
    
    /**
     * 检查数据库状态
     */
    private function checkDatabaseStatus(): array
    {
        try {
            $startTime = microtime(true);
            // 测试数据库连接
            $testQuery = "SELECT 1";
            $this->db->query($testQuery);
            $connected = true;
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => $connected ? 'healthy' : 'error',
                'response_time_ms' => $responseTime,
                'last_check' => date('Y-m-d H:i:s'),
                'details' => $connected ? 'Database connection successful' : 'Database connection failed'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'response_time_ms' => null,
                'last_check' => date('Y-m-d H:i:s'),
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查缓存状态
     */
    private function checkCacheStatus(): array
    {
        try {
            $startTime = microtime(true);
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            // 测试缓存读写
            $this->cache->set($testKey, $testValue, 60);
            $retrieved = $this->cache->get($testKey);
            $this->cache->delete($testKey);
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            $working = ($retrieved === $testValue);
            
            return [
                'status' => $working ? 'healthy' : 'error',
                'response_time_ms' => $responseTime,
                'last_check' => date('Y-m-d H:i:s'),
                'details' => $working ? 'Cache read/write test successful' : 'Cache read/write test failed'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'response_time_ms' => null,
                'last_check' => date('Y-m-d H:i:s'),
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查文件系统状态
     */
    private function checkFileSystemStatus(): array
    {
        $logDir = dirname(__DIR__, 2) . '/logs';
        $cacheDir = dirname(__DIR__, 2) . '/cache';
        $uploadsDir = dirname(__DIR__, 2) . '/uploads';
        
        $checks = [
            'logs_writable' => is_writable($logDir),
            'cache_writable' => is_writable($cacheDir),
            'uploads_writable' => is_writable($uploadsDir)
        ];
        
        $allHealthy = array_reduce($checks, function($carry, $item) {
            return $carry && $item;
        }, true);
        
        return [
            'status' => $allHealthy ? 'healthy' : 'warning',
            'last_check' => date('Y-m-d H:i:s'),
            'details' => $checks,
            'summary' => $allHealthy ? 'All directories are writable' : 'Some directories are not writable'
        ];
    }
    
    /**
     * 检查PHP扩展
     */
    private function checkPhpExtensions(): array
    {
        $requiredExtensions = [
            'pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'curl', 'zip'
        ];
        
        $optionalExtensions = [
            'redis', 'memcached', 'opcache', 'xdebug', 'imagick'
        ];
        
        $extensionStatus = [];
        
        foreach ($requiredExtensions as $ext) {
            $extensionStatus['required'][$ext] = extension_loaded($ext);
        }
        
        foreach ($optionalExtensions as $ext) {
            $extensionStatus['optional'][$ext] = extension_loaded($ext);
        }
        
        $allRequiredLoaded = array_reduce($extensionStatus['required'], function($carry, $item) {
            return $carry && $item;
        }, true);
        
        return [
            'status' => $allRequiredLoaded ? 'healthy' : 'error',
            'last_check' => date('Y-m-d H:i:s'),
            'extensions' => $extensionStatus,
            'summary' => $allRequiredLoaded ? 'All required extensions loaded' : 'Some required extensions missing'
        ];
    }
    
    /**
     * 获取性能指标
     */
    private function getPerformanceMetrics(): array
    {
        $opcacheStatus = null;
        if (function_exists('opcache_get_status')) {
            $opcacheStatus = opcache_get_status(false);
        }
        
        return [
            'request_start_time' => $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true),
            'current_memory_usage' => memory_get_usage(true),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'opcache' => $opcacheStatus ? [
                'enabled' => $opcacheStatus['opcache_enabled'],
                'cache_full' => $opcacheStatus['cache_full'] ?? false,
                'hit_rate' => isset($opcacheStatus['opcache_statistics']) ? 
                    round($opcacheStatus['opcache_statistics']['opcache_hit_rate'], 2) : null,
                'memory_usage' => $opcacheStatus['memory_usage'] ?? null
            ] : null,
            'included_files_count' => count(get_included_files()),
            'declared_classes_count' => count(get_declared_classes()),
            'declared_functions_count' => count(get_defined_functions()['user'])
        ];
    }
    
    /**
     * 执行健康检查
     */
    private function performHealthCheck(): array
    {
        $checks = [
            'system_responsive' => true,
            'database_connected' => $this->checkDatabaseStatus()['status'] === 'healthy',
            'cache_working' => $this->checkCacheStatus()['status'] === 'healthy',
            'filesystem_writable' => $this->checkFileSystemStatus()['status'] === 'healthy',
            'extensions_loaded' => $this->checkPhpExtensions()['status'] === 'healthy'
        ];
        
        $healthyCount = array_sum($checks);
        $totalChecks = count($checks);
        $healthScore = round(($healthyCount / $totalChecks) * 100);
          if ($healthScore >= 90) {
            $overallStatus = 'excellent';
        } elseif ($healthScore >= 70) {
            $overallStatus = 'good';
        } elseif ($healthScore >= 50) {
            $overallStatus = 'warning';
        } else {
            $overallStatus = 'critical';
        }
        
        return [
            'overall_status' => $overallStatus,
            'health_score' => $healthScore,
            'checks_passed' => $healthyCount,
            'total_checks' => $totalChecks,
            'detailed_checks' => $checks,
            'recommendations' => $this->getHealthRecommendations($checks)
        ];
    }
    
    /**
     * 获取健康建议
     */
    private function getHealthRecommendations(array $checks): array
    {
        $recommendations = [];
        
        if (!$checks['database_connected']) {
            $recommendations[] = '检查数据库连接配置和服务状态';
        }
        
        if (!$checks['cache_working']) {
            $recommendations[] = '检查缓存服务配置和权限';
        }
        
        if (!$checks['filesystem_writable']) {
            $recommendations[] = '检查文件系统权限，确保关键目录可写';
        }
        
        if (!$checks['extensions_loaded']) {
            $recommendations[] = '安装或启用缺失的PHP扩展';
        }
        
        return $recommendations;
    }
    
    /**
     * 获取历史监控数据
     */
    public function getHistoricalData(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $days = (int) ($params['days'] ?? 7);
            $metric = $params['metric'] ?? 'all';
            
            // 这里可以从数据库获取历史数据
            $historicalData = [
                'period' => $days . ' days',
                'metric' => $metric,
                'data' => [], // 实际数据将从数据库查询
                'summary' => [
                    'total_records' => 0,
                    'avg_health_score' => 0,
                    'incidents_count' => 0
                ]
            ];
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $historicalData,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**
     * 辅助方法：转换内存大小字符串为字节
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '-1') return PHP_INT_MAX;
        
        $last = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;
        
        switch ($last) {
            case 'g': $number *= 1024;
            case 'm': $number *= 1024;
            case 'k': $number *= 1024;
        }
        
        return $number;
    }
    
    /**
     * 辅助方法：格式化字节大小
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * 获取处理器数量
     */
    private function getProcessorCount(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return (int) ($_SERVER['NUMBER_OF_PROCESSORS'] ?? 1);
        } else {
            $output = shell_exec('nproc 2>/dev/null');
            return $output ? (int) trim($output) : 1;
        }
    }
}
