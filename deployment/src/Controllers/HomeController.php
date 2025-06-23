<?php

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends BaseController
{
    /**
     * 首页
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [
            'title' => 'AlingAi Pro - 智能AI助手系统',
            'version' => '2.0.0',
            'description' => '基于PHP 7.4+的现代化AI助手系统',
            'features' => [
                '智能对话系统',
                '文档管理',
                '用户管理',
                '权限控制',
                '实时通讯',
                '数据分析',
                'API接口',
                '管理后台'
            ],
            'status' => 'running',
            'timestamp' => date('c')
        ];
        
        return $this->successResponse($response, $data, 'Welcome to AlingAi Pro');
    }
    
    /**
     * 系统状态检查
     */
    public function status(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $status = [
            'system' => [
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'uptime' => $this->getSystemUptime()
            ],
            'database' => $this->checkDatabaseStatus(),
            'cache' => $this->checkCacheStatus(),
            'services' => [
                'auth' => $this->checkAuthService(),
                'storage' => $this->checkStorageService(),
                'queue' => $this->checkQueueService()
            ],
            'timestamp' => date('c')
        ];
        
        return $this->successResponse($response, $status, 'System status');
    }
    
    /**
     * API信息
     */
    public function apiInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $apiInfo = [
            'name' => 'AlingAi Pro API',
            'version' => '2.0.0',
            'description' => '智能AI助手系统API接口',
            'base_url' => $this->getBaseUrl($request),
            'endpoints' => [
                'auth' => '/api/auth',
                'users' => '/api/users',
                'conversations' => '/api/conversations',
                'documents' => '/api/documents',
                'admin' => '/api/admin',
                'websocket' => '/ws'
            ],
            'authentication' => [
                'type' => 'JWT Bearer Token',
                'header' => 'Authorization: Bearer {token}',
                'endpoints' => [
                    'login' => 'POST /api/auth/login',
                    'register' => 'POST /api/auth/register',
                    'refresh' => 'POST /api/auth/refresh'
                ]
            ],
            'rate_limiting' => [
                'requests_per_hour' => 1000,
                'burst_limit' => 100
            ],
            'documentation' => '/docs',
            'timestamp' => date('c')
        ];
        
        return $this->successResponse($response, $apiInfo, 'API Information');
    }
    
    /**
     * 健康检查
     */
    public function health(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $checks = [
            'database' => $this->healthCheckDatabase(),
            'cache' => $this->healthCheckCache(),
            'storage' => $this->healthCheckStorage(),
            'memory' => $this->healthCheckMemory(),
            'disk' => $this->healthCheckDisk()
        ];
        
        $healthy = true;
        $issues = [];
        
        foreach ($checks as $service => $check) {
            if (!$check['healthy']) {
                $healthy = false;
                $issues[] = $service . ': ' . $check['message'];
            }
        }
        
        $healthStatus = [
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'issues' => $issues,
            'timestamp' => date('c')
        ];
        
        $statusCode = $healthy ? 200 : 503;
        
        return $this->successResponse($response, $healthStatus, $healthy ? 'System is healthy' : 'System has issues', $statusCode);
    }
    
    /**
     * 系统信息
     */
    public function info(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $info = [
            'application' => [
                'name' => 'AlingAi Pro',
                'version' => '2.0.0',
                'environment' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true'
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'operating_system' => PHP_OS,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'timezone' => date_default_timezone_get()
            ],
            'features' => [
                'websockets' => extension_loaded('swoole') || extension_loaded('uv'),
                'redis' => extension_loaded('redis'),
                'opcache' => extension_loaded('Zend OPcache'),
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
                'zip' => extension_loaded('zip'),
                'json' => extension_loaded('json')
            ],
            'timestamp' => date('c')
        ];
        
        return $this->successResponse($response, $info, 'System information');
    }
    
    private function getSystemUptime(): int
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = file_get_contents('/proc/uptime');
            return (int) floatval(explode(' ', $uptime)[0]);
        }
        return 0;
    }
      private function checkDatabaseStatus(): array
    {
        try {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->query('SELECT 1');
            return [
                'status' => 'connected',
                'healthy' => true,
                'response_time' => 0 // 可以测量实际响应时间
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'healthy' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function checkCacheStatus(): array
    {
        try {
            $this->cache->set('health_check', 'ok', 60);
            $value = $this->cache->get('health_check');
            return [
                'status' => $value === 'ok' ? 'connected' : 'error',
                'healthy' => $value === 'ok',
                'driver' => 'redis' // 或从配置获取
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'healthy' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function checkAuthService(): bool
    {
        return true; // 简化检查
    }
    
    private function checkStorageService(): bool
    {
        return is_writable(__DIR__ . '/../../storage');
    }
    
    private function checkQueueService(): bool
    {
        return true; // 如果有队列服务的话
    }
      private function healthCheckDatabase(): array
    {
        try {
            $start = microtime(true);
            $pdo = $this->db->getPdo();
            $pdo->query('SELECT 1');
            $duration = microtime(true) - $start;
            
            return [
                'healthy' => true,
                'response_time' => round($duration * 1000, 2),
                'message' => 'Database connection OK'
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    private function healthCheckCache(): array
    {
        try {
            $start = microtime(true);
            $this->cache->set('health_check_' . time(), 'test', 10);
            $duration = microtime(true) - $start;
            
            return [
                'healthy' => true,
                'response_time' => round($duration * 1000, 2),
                'message' => 'Cache service OK'
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Cache service failed: ' . $e->getMessage()
            ];
        }
    }
    
    private function healthCheckStorage(): array
    {
        $storagePath = __DIR__ . '/../../storage';
        
        if (!is_dir($storagePath)) {
            return [
                'healthy' => false,
                'message' => 'Storage directory does not exist'
            ];
        }
        
        if (!is_writable($storagePath)) {
            return [
                'healthy' => false,
                'message' => 'Storage directory is not writable'
            ];
        }
        
        $freeSpace = disk_free_space($storagePath);
        $totalSpace = disk_total_space($storagePath);
        $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
        
        return [
            'healthy' => $usedPercent < 90,
            'free_space' => $freeSpace,
            'used_percent' => $usedPercent,
            'message' => $usedPercent >= 90 ? 'Low disk space' : 'Storage OK'
        ];
    }
    
    private function healthCheckMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $usedPercent = round(($memoryUsage / $memoryLimit) * 100, 2);
        
        return [
            'healthy' => $usedPercent < 80,
            'usage' => $memoryUsage,
            'limit' => $memoryLimit,
            'used_percent' => $usedPercent,
            'message' => $usedPercent >= 80 ? 'High memory usage' : 'Memory usage OK'
        ];
    }
    
    private function healthCheckDisk(): array
    {
        $rootPath = __DIR__ . '/../../';
        $freeSpace = disk_free_space($rootPath);
        $totalSpace = disk_total_space($rootPath);
        $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
        
        return [
            'healthy' => $usedPercent < 85,
            'free_space' => $freeSpace,
            'total_space' => $totalSpace,
            'used_percent' => $usedPercent,
            'message' => $usedPercent >= 85 ? 'Low disk space' : 'Disk space OK'
        ];
    }
    
    private function parseMemoryLimit(string $limit): int
    {
        $limit = strtoupper($limit);
        $bytes = (int) $limit;
        
        if (strpos($limit, 'K') !== false) {
            $bytes *= 1024;
        } elseif (strpos($limit, 'M') !== false) {
            $bytes *= 1024 * 1024;
        } elseif (strpos($limit, 'G') !== false) {
            $bytes *= 1024 * 1024 * 1024;
        }
        
        return $bytes;
    }
    
    private function getBaseUrl(ServerRequestInterface $request): string
    {
        $uri = $request->getUri();
        return $uri->getScheme() . '://' . $uri->getAuthority();
    }
}
