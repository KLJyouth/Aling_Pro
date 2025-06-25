<?php
/**
 * AlingAi Pro 5.0 - 系统监控API
 * 系统健康状态、性能指标、实时监控等
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../src/Auth/AdminAuthServiceDemo.php';

use AlingAi\Auth\AdminAuthServiceDemo;

// 响应函数
function sendResponse($success, $data = null, $message = '', $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

function handleError($message, $code = 500) {
    error_log("Monitor API Error: $message");
    sendResponse(false, null, $message, $code);
}

// 获取请求信息
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));

try {
    // 验证管理员权限
    $authService = new AdminAuthServiceDemo();
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (strpos($token, 'Bearer ') === 0) {
        $token = substr($token, 7);
    }
    
    if (!$token) {
        sendResponse(false, null, '缺少授权令牌', 401);
    }
    
    $user = $authService->validateToken($token);
    if (!$user || !$authService->hasPermission($user['id'], 'system.monitor')) {
        sendResponse(false, null, '权限不足', 403);
    }
    
    // 解析路由
    $action = $pathSegments[3] ?? '';
    
    switch ($action) {
        case 'dashboard':
            handleMonitoringDashboard();
            break;
            
        case 'health':
            handleSystemHealth();
            break;
            
        case 'performance':
            handlePerformanceMetrics();
            break;
            
        case 'resources':
            handleResourceUsage();
            break;
            
        case 'logs':
            handleSystemLogs();
            break;
            
        case 'alerts':
            handleSystemAlerts();
            break;
            
        case 'api-status':
            handleAPIStatus();
            break;
            
        case 'database':
            handleDatabaseStatus();
            break;
            
        case 'cache':
            handleCacheStatus();
            break;
            
        case 'queue':
            handleQueueStatus();
            break;
            
        default:
            handleOverview();
    }
    
} catch (Exception $e) {
    handleError($e->getMessage());
}

/**
 * 系统监控总览
 */
function handleOverview() {
    try {
        $overview = [
            'system_status' => getSystemStatus(),
            'quick_stats' => getQuickStats(),
            'alerts' => getActiveAlerts(),
            'recent_events' => getRecentEvents()
        ];
        
        sendResponse(true, $overview, '获取监控总览成功');
        
    } catch (Exception $e) {
        handleError('获取监控总览失败: ' . $e->getMessage());
    }
}

/**
 * 监控仪表板数据
 */
function handleMonitoringDashboard() {
    try {
        $dashboard = [
            'system_health' => getSystemHealthMetrics(),
            'performance' => getPerformanceData(),
            'traffic' => getTrafficData(),
            'errors' => getErrorMetrics(),
            'uptime' => getUptimeData(),
            'alerts' => getActiveAlerts()
        ];
        
        sendResponse(true, $dashboard, '获取监控仪表板数据成功');
        
    } catch (Exception $e) {
        handleError('获取监控仪表板失败: ' . $e->getMessage());
    }
}

/**
 * 系统健康检查
 */
function handleSystemHealth() {
    try {
        $health = [
            'overall_status' => 'healthy',
            'components' => [
                'web_server' => checkWebServer(),
                'database' => checkDatabase(),
                'cache' => checkCache(),
                'file_system' => checkFileSystem(),
                'network' => checkNetwork(),
                'third_party_services' => checkThirdPartyServices()
            ],
            'last_check' => date('Y-m-d H:i:s'),
            'uptime' => getSystemUptime()
        ];
        
        // 计算总体状态
        $healthy = 0;
        $total = count($health['components']);
        foreach ($health['components'] as $component) {
            if ($component['status'] === 'healthy') {
                $healthy++;
            }
        }
        
        if ($healthy === $total) {
            $health['overall_status'] = 'healthy';
        } elseif ($healthy > $total / 2) {
            $health['overall_status'] = 'warning';
        } else {
            $health['overall_status'] = 'critical';
        }
        
        sendResponse(true, $health, '系统健康检查完成');
        
    } catch (Exception $e) {
        handleError('系统健康检查失败: ' . $e->getMessage());
    }
}

/**
 * 性能指标
 */
function handlePerformanceMetrics() {
    try {
        $period = $_GET['period'] ?? 'hour'; // hour, day, week, month
        
        $metrics = [
            'cpu' => getCPUMetrics($period),
            'memory' => getMemoryMetrics($period),
            'disk' => getDiskMetrics($period),
            'network' => getNetworkMetrics($period),
            'response_time' => getResponseTimeMetrics($period),
            'throughput' => getThroughputMetrics($period)
        ];
        
        sendResponse(true, $metrics, '获取性能指标成功');
        
    } catch (Exception $e) {
        handleError('获取性能指标失败: ' . $e->getMessage());
    }
}

/**
 * 资源使用情况
 */
function handleResourceUsage() {
    try {
        $resources = [
            'cpu' => getCurrentCPUUsage(),
            'memory' => getCurrentMemoryUsage(),
            'disk' => getCurrentDiskUsage(),
            'network' => getCurrentNetworkUsage(),
            'processes' => getProcessInfo(),
            'connections' => getConnectionInfo()
        ];
        
        sendResponse(true, $resources, '获取资源使用情况成功');
        
    } catch (Exception $e) {
        handleError('获取资源使用情况失败: ' . $e->getMessage());
    }
}

/**
 * 系统日志
 */
function handleSystemLogs() {
    try {
        $level = $_GET['level'] ?? 'all'; // error, warning, info, debug, all
        $limit = min((int)($_GET['limit'] ?? 100), 1000);
        $page = (int)($_GET['page'] ?? 1);
        
        $logs = getSystemLogs($level, $limit, $page);
        
        sendResponse(true, $logs, '获取系统日志成功');
        
    } catch (Exception $e) {
        handleError('获取系统日志失败: ' . $e->getMessage());
    }
}

/**
 * 系统告警
 */
function handleSystemAlerts() {
    try {
        $status = $_GET['status'] ?? 'active'; // active, resolved, all
        $severity = $_GET['severity'] ?? 'all'; // critical, warning, info, all
        
        $alerts = getSystemAlerts($status, $severity);
        
        sendResponse(true, $alerts, '获取系统告警成功');
        
    } catch (Exception $e) {
        handleError('获取系统告警失败: ' . $e->getMessage());
    }
}

/**
 * API状态监控
 */
function handleAPIStatus() {
    try {
        $apiStatus = [
            'endpoints' => getAPIEndpointStatus(),
            'response_times' => getAPIResponseTimes(),
            'error_rates' => getAPIErrorRates(),
            'throughput' => getAPIThroughput()
        ];
        
        sendResponse(true, $apiStatus, '获取API状态成功');
        
    } catch (Exception $e) {
        handleError('获取API状态失败: ' . $e->getMessage());
    }
}

/**
 * 数据库状态
 */
function handleDatabaseStatus() {
    try {
        $dbStatus = [
            'connection_status' => checkDatabaseConnection(),
            'performance' => getDatabasePerformance(),
            'slow_queries' => getSlowQueries(),
            'connections' => getDatabaseConnections(),
            'storage' => getDatabaseStorage()
        ];
        
        sendResponse(true, $dbStatus, '获取数据库状态成功');
        
    } catch (Exception $e) {
        handleError('获取数据库状态失败: ' . $e->getMessage());
    }
}

/**
 * 缓存状态
 */
function handleCacheStatus() {
    try {
        $cacheStatus = [
            'redis' => getRedisStatus(),
            'file_cache' => getFileCacheStatus(),
            'hit_rate' => getCacheHitRate(),
            'memory_usage' => getCacheMemoryUsage()
        ];
        
        sendResponse(true, $cacheStatus, '获取缓存状态成功');
        
    } catch (Exception $e) {
        handleError('获取缓存状态失败: ' . $e->getMessage());
    }
}

/**
 * 队列状态
 */
function handleQueueStatus() {
    try {
        $queueStatus = [
            'pending_jobs' => getPendingJobs(),
            'failed_jobs' => getFailedJobs(),
            'processed_jobs' => getProcessedJobs(),
            'queue_workers' => getQueueWorkers()
        ];
        
        sendResponse(true, $queueStatus, '获取队列状态成功');
        
    } catch (Exception $e) {
        handleError('获取队列状态失败: ' . $e->getMessage());
    }
}

// ================================
// 辅助函数
// ================================

function getSystemStatus(): array
{
    return [
        'uptime' => getSystemUptime(),
        'load_average' => getLoadAverage(),
        'version' => 'AlingAi Pro 5.0',
        'last_restart' => date('Y-m-d H:i:s', time() - rand(3600, 86400))
    ];
}

function getQuickStats(): array
{
    return [
        'total_requests_today' => rand(1000, 5000),
        'average_response_time' => rand(50, 300) . 'ms',
        'error_rate' => round(rand(0, 50) / 10, 1) . '%',
        'cpu_usage' => rand(10, 80) . '%',
        'memory_usage' => rand(30, 90) . '%'
    ];
}

function getActiveAlerts(): array
{
    $alerts = [];
    
    // 模拟一些告警
    if (rand(0, 10) > 7) {
        $alerts[] = [
            'id' => 1,
            'severity' => 'warning',
            'title' => 'CPU使用率偏高',
            'message' => 'CPU使用率持续5分钟超过80%',
            'created_at' => date('Y-m-d H:i:s', time() - rand(300, 3600))
        ];
    }
    
    if (rand(0, 10) > 8) {
        $alerts[] = [
            'id' => 2,
            'severity' => 'critical',
            'title' => '数据库连接异常',
            'message' => '数据库连接超时，请检查网络状态',
            'created_at' => date('Y-m-d H:i:s', time() - rand(60, 600))
        ];
    }
    
    return $alerts;
}

function getRecentEvents(): array
{
    return [
        'type' => 'system',
        'message' => '系统自动备份完成',
        'timestamp' => date('Y-m-d H:i:s', time() - 3600)
    ];
}

function getSystemHealthMetrics(): array
{
    return [
        'components' => [
            'web_server' => ['score' => rand(90, 100), 'status' => 'healthy'],
            'database' => ['score' => rand(85, 95), 'status' => 'healthy'],
            'cache' => ['score' => rand(80, 100), 'status' => 'healthy'],
            'storage' => ['score' => rand(75, 90), 'status' => 'warning']
        ]
    ];
}

function getPerformanceData(): array
{
    $times = [];
    for ($i = 23; $i >= 0; $i--) {
        $times[] = [
            'time' => date('H:i', time() - $i * 3600),
            'cpu' => rand(10, 80),
            'memory' => rand(30, 90),
            'response_time' => rand(50, 300)
        ];
    }
    
    return $times;
}

function getTrafficData(): array
{
    $traffic = [];
    for ($i = 23; $i >= 0; $i--) {
        $traffic[] = [
            'time' => date('H:i', time() - $i * 3600),
            'requests' => rand(100, 1000),
            'bandwidth' => rand(10, 100) . 'MB'
        ];
    }
    
    return $traffic;
}

function getErrorMetrics(): array
{
    return [
        'error_rate' => round(rand(0, 50) / 10, 1),
        'critical_errors' => rand(0, 5),
        'recent_errors' => [
            [
                'message' => 'Database connection timeout',
                'count' => rand(1, 10),
                'last_seen' => date('Y-m-d H:i:s', time() - rand(300, 3600))
            ]
        ]
    ];
}

function getUptimeData(): array
{
    return [
        'uptime_percentage' => 99.9,
        'last_downtime' => date('Y-m-d H:i:s', time() - rand(86400, 604800)),
        'downtime_duration' => '5 minutes'
    ];
}

function checkWebServer(): array
{
    return [
        'response_time' => rand(10, 50) . 'ms',
        'active_connections' => rand(50, 200),
        'last_check' => date('Y-m-d H:i:s')
    ];
}

function checkDatabase(): array
{
    return [
        'connection_count' => rand(5, 20),
        'query_time' => rand(1, 10) . 'ms',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

function checkCache(): array
{
    return [
        'hit_rate' => rand(80, 99) . '%',
        'memory_usage' => rand(30, 80) . '%',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

function checkFileSystem(): array
{
    return [
        'disk_usage' => rand(40, 85) . '%',
        'free_space' => rand(10, 50) . 'GB',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

function checkNetwork(): array
{
    return [
        'latency' => rand(1, 10) . 'ms',
        'packet_loss' => '0%',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

function checkThirdPartyServices(): array
{
    return [
        'active_services' => rand(5, 10),
        'failed_services' => rand(0, 2),
        'last_check' => date('Y-m-d H:i:s')
    ];
}

function getSystemUptime(): string
{
    $uptime = rand(3600, 2592000); // 1 hour to 30 days
    $days = floor($uptime / 86400);
    $hours = floor(($uptime % 86400) / 3600);
    $minutes = floor(($uptime % 3600) / 60);
    
    return "{$days}天 {$hours}小时 {$minutes}分钟";
}

function getLoadAverage(): array
{
    return [
        '5min' => round(rand(40, 280) / 100, 2),
        '15min' => round(rand(30, 250) / 100, 2)
    ];
}

function getCPUMetrics($period): array
{
    $points = getPeriodPoints($period);
    $data = [];
    
    for ($i = 0; $i < $points; $i++) {
        $data[] = [
            'timestamp' => date('Y-m-d H:i:s', time() - ($points - $i) * getPeriodInterval($period)),
            'usage' => rand(10, 80)
        ];
    }
    
    return $data;
}

function getMemoryMetrics($period): array
{
    $points = getPeriodPoints($period);
    $data = [];
    
    for ($i = 0; $i < $points; $i++) {
        $data[] = [
            'timestamp' => date('Y-m-d H:i:s', time() - ($points - $i) * getPeriodInterval($period)),
            'usage' => rand(30, 90),
            'total' => '16GB',
            'available' => rand(2, 8) . 'GB'
        ];
    }
    
    return $data;
}

function getDiskMetrics($period): array
{
    $points = getPeriodPoints($period);
    $data = [];
    
    for ($i = 0; $i < $points; $i++) {
        $data[] = [
            'timestamp' => date('Y-m-d H:i:s', time() - ($points - $i) * getPeriodInterval($period)),
            'usage' => rand(40, 85),
            'read_speed' => rand(50, 200) . 'MB/s',
            'write_speed' => rand(30, 150) . 'MB/s'
        ];
    }
    
    return $data;
}

function getNetworkMetrics($period): array
{
    $points = getPeriodPoints($period);
    $data = [];
    
    for ($i = 0; $i < $points; $i++) {
        $data[] = [
            'timestamp' => date('Y-m-d H:i:s', time() - ($points - $i) * getPeriodInterval($period)),
            'incoming' => rand(10, 100) . 'Mbps',
            'outgoing' => rand(5, 50) . 'Mbps',
            'packets_in' => rand(100, 1000),
            'packets_out' => rand(50, 500)
        ];
    }
    
    return $data;
}

function getResponseTimeMetrics($period): array
{
    $points = getPeriodPoints($period);
    $data = [];
    
    for ($i = 0; $i < $points; $i++) {
        $data[] = [
            'timestamp' => date('Y-m-d H:i:s', time() - ($points - $i) * getPeriodInterval($period)),
            'avg_response_time' => rand(50, 300),
            'min_response_time' => rand(10, 50),
            'max_response_time' => rand(300, 1000)
        ];
    }
    
    return $data;
}

function getThroughputMetrics($period): array
{
    $points = getPeriodPoints($period);
    $data = [];
    
    for ($i = 0; $i < $points; $i++) {
        $data[] = [
            'timestamp' => date('Y-m-d H:i:s', time() - ($points - $i) * getPeriodInterval($period)),
            'requests_per_second' => rand(10, 100),
            'total_requests' => rand(1000, 10000)
        ];
    }
    
    return $data;
}

function getPeriodPoints($period): int
{
    switch ($period) {
        case 'hour': return 60;
        case 'day': return 24;
        case 'week': return 7;
        case 'month': return 30;
        default: return 24;
    }
}

function getPeriodInterval($period): int
{
    switch ($period) {
        case 'hour': return 60; // 1 minute
        case 'day': return 3600; // 1 hour
        case 'week': return 86400; // 1 day
        case 'month': return 86400; // 1 day
        default: return 3600;
    }
}

function getCurrentCPUUsage(): array
{
    return [
        'cores' => [
            ['id' => 1, 'usage' => rand(5, 85)],
            ['id' => 2, 'usage' => rand(5, 85)],
            ['id' => 3, 'usage' => rand(5, 85)],
            ['id' => 4, 'usage' => rand(5, 85)]
        ],
        'load_average' => getLoadAverage()
    ];
}

function getCurrentMemoryUsage(): array
{
    $total = 16 * 1024 * 1024 * 1024; // 16GB
    $used = rand(4, 12) * 1024 * 1024 * 1024;
    
    return [
        'used' => $used,
        'free' => $total - $used,
        'usage_percentage' => round(($used / $total) * 100, 1),
        'swap' => [
            'total' => 4 * 1024 * 1024 * 1024,
            'used' => rand(0, 1) * 1024 * 1024 * 1024,
            'free' => (4 - rand(0, 1)) * 1024 * 1024 * 1024
        ]
    ];
}

function getCurrentDiskUsage(): array
{
    return [
        [
            'name' => '/',
            'total' => 500 * 1024 * 1024 * 1024,
            'used' => rand(200, 400) * 1024 * 1024 * 1024,
            'free' => rand(100, 300) * 1024 * 1024 * 1024,
            'usage_percentage' => rand(40, 80)
        ],
        [
            'name' => '/data',
            'total' => 1000 * 1024 * 1024 * 1024,
            'used' => rand(300, 800) * 1024 * 1024 * 1024,
            'free' => rand(200, 700) * 1024 * 1024 * 1024,
            'usage_percentage' => rand(30, 80)
        ]
    ];
}

function getCurrentNetworkUsage(): array
{
    return [
        [
            'name' => 'eth0',
            'rx_bytes' => rand(1000000, 10000000),
            'tx_bytes' => rand(500000, 5000000),
            'rx_packets' => rand(10000, 100000),
            'tx_packets' => rand(5000, 50000)
        ]
    ];
}

function getProcessInfo(): array
{
    return [
        'running_processes' => rand(5, 20),
        'top_processes' => [
            ['name' => 'php-fpm', 'cpu' => rand(5, 25), 'memory' => rand(50, 200) . 'MB'],
            ['name' => 'nginx', 'cpu' => rand(1, 10), 'memory' => rand(20, 100) . 'MB'],
            ['name' => 'mysql', 'cpu' => rand(10, 30), 'memory' => rand(100, 500) . 'MB'],
            ['name' => 'redis', 'cpu' => rand(1, 5), 'memory' => rand(50, 150) . 'MB']
        ]
    ];
}

function getConnectionInfo(): array
{
    return [
        'active_connections' => rand(20, 100),
        'waiting_connections' => rand(0, 10),
        'by_port' => [
            ['port' => 80, 'connections' => rand(20, 100)],
            ['port' => 443, 'connections' => rand(30, 150)],
            ['port' => 3306, 'connections' => rand(5, 20)],
            ['port' => 6379, 'connections' => rand(2, 10)]
        ]
    ];
}

function getSystemLogs($level, $limit, $page): array
{
    $logs = [];
    $total = rand(500, 2000);
    
    for ($i = 0; $i < min($limit, 100); $i++) {
        $levels = ['error', 'warning', 'info', 'debug'];
        $logLevel = $level === 'all' ? $levels[array_rand($levels)] : $level;
        
        $logs[] = [
            'id' => $i + 1,
            'level' => $logLevel,
            'message' => getRandomLogMessage($logLevel),
            'timestamp' => date('Y-m-d H:i:s', time() - rand(0, 86400)),
            'source' => ['system', 'application', 'security'][array_rand(['system', 'application', 'security'])]
        ];
    }
    
    return [
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ];
}

function getRandomLogMessage($level): string
{
    $messages = [
        'error' => [
            'Database connection failed',
            'Memory limit exceeded',
            'File not found: /path/to/file',
            'API request timeout'
        ],
        'warning' => [
            'High CPU usage detected',
            'Slow query detected',
            'Cache miss rate high',
            'Disk space running low'
        ],
        'info' => [
            'User logged in successfully',
            'Backup completed',
            'Cache cleared',
            'System maintenance completed'
        ],
        'debug' => [
            'Function executed in 0.5s',
            'Cache hit for key: user_123',
            'SQL query executed',
            'API response received'
        ]
    ];
    
    return $messages[$level][array_rand($messages[$level])];
}

function getSystemAlerts($status, $severity): array
{
    $alerts = [];
    $count = rand(0, 10);
    
    for ($i = 0; $i < $count; $i++) {
        $severities = ['critical', 'warning', 'info'];
        $alertSeverity = $severity === 'all' ? $severities[array_rand($severities)] : $severity;
        
        $alerts[] = [
            'id' => $i + 1,
            'title' => getRandomAlertTitle($alertSeverity),
            'severity' => $alertSeverity,
            'status' => $status === 'all' ? ['active', 'resolved'][array_rand(['active', 'resolved'])] : $status,
            'message' => getRandomAlertMessage($alertSeverity),
            'created_at' => date('Y-m-d H:i:s', time() - rand(0, 86400)),
            'resolved_at' => rand(0, 1) ? date('Y-m-d H:i:s', time() - rand(0, 3600)) : null
        ];
    }
    
    return $alerts;
}

function getRandomAlertTitle($severity): string
{
    $titles = [
        'critical' => ['System Down', 'Database Failure', 'Security Breach'],
        'warning' => ['High CPU Usage', 'Memory Warning', 'Slow Response'],
        'info' => ['Scheduled Maintenance', 'Update Available', 'Backup Completed']
    ];
    
    return $titles[$severity][array_rand($titles[$severity])];
}

function getRandomAlertMessage($severity): string
{
    $messages = [
        'critical' => [
            'System is completely unavailable',
            'Database connection lost',
            'Security threat detected'
        ],
        'warning' => [
            'CPU usage above 80% for 5 minutes',
            'Memory usage above 90%',
            'Response time above 2 seconds'
        ],
        'info' => [
            'Scheduled maintenance will begin in 1 hour',
            'System update available',
            'Daily backup completed successfully'
        ]
    ];
    
    return $messages[$severity][array_rand($messages[$severity])];
}

function getAPIEndpointStatus(): array
{
    $endpoints = [
        '/api/auth/login',
        '/api/users',
        '/api/chat',
        '/api/system/health',
        '/api/admin/dashboard'
    ];
    
    $status = [];
    foreach ($endpoints as $endpoint) {
        $status[] = [
            'endpoint' => $endpoint,
            'status' => rand(0, 10) > 1 ? 'healthy' : 'error',
            'response_time' => rand(50, 500) . 'ms',
            'success_rate' => rand(90, 100) . '%'
        ];
    }
    
    return $status;
}

public function getAPIResponseTimes(): array
{
    $data = [];
    for ($i = 23; $i >= 0; $i--) {
        $data[] = [
            'hour' => date('H:i', time() - $i * 3600),
            'avg_response_time' => rand(50, 300),
            'p95_response_time' => rand(200, 800),
            'p99_response_time' => rand(500, 1500)
        ];
    }
    
    return $data;
}

public function getAPIErrorRates(): array
{
    $data = [];
    for ($i = 23; $i >= 0; $i--) {
        $data[] = [
            'hour' => date('H:i', time() - $i * 3600),
            'error_rate' => round(rand(0, 50) / 10, 1),
            'total_requests' => rand(100, 1000),
            'error_count' => rand(0, 50)
        ];
    }
    
    return $data;
}

public function getAPIThroughput(): array
{
    $data = [];
    for ($i = 23; $i >= 0; $i--) {
        $data[] = [
            'hour' => date('H:i', time() - $i * 3600),
            'requests_per_second' => rand(10, 100),
            'total_requests' => rand(1000, 10000)
        ];
    }
    
    return $data;
}

public function checkDatabaseConnection(): array
{
    return [
        'response_time' => rand(1, 20) . 'ms',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

public function getDatabasePerformance(): array
{
    return [
        'average_query_time' => rand(1, 50) . 'ms',
        'slow_queries' => rand(0, 10),
        'cache_hit_rate' => rand(80, 99) . '%'
    ];
}

public function getSlowQueries(): array
{
    return [
    ];
}

public function getDatabaseConnections(): array
{
    return [
        'max_connections' => 100,
        'connection_usage' => rand(5, 80) . '%'
    ];
}

public function getDatabaseStorage(): array
{
    return [
        'data_size' => rand(500, 8000) . 'MB',
        'index_size' => rand(100, 2000) . 'MB',
        'growth_rate' => rand(1, 10) . 'MB/day'
    ];
}

public function getRedisStatus(): array
{
    return [
        'memory_usage' => rand(10, 80) . '%',
        'hit_rate' => rand(80, 99) . '%',
        'connected_clients' => rand(1, 20)
    ];
}

public function getFileCacheStatus(): array
{
    return [
        'cache_files' => rand(1000, 10000),
        'hit_rate' => rand(70, 95) . '%'
    ];
}

public function getCacheHitRate(): array
{
    $data = [];
    for ($i = 23; $i >= 0; $i--) {
        $data[] = [
            'hour' => date('H:i', time() - $i * 3600),
            'hit_rate' => rand(70, 99)
        ];
    }
    
    return $data;
}

public function getCacheMemoryUsage(): array
{
    return [
        'file_cache' => rand(5, 30) . '%',
        'application_cache' => rand(15, 50) . '%'
    ];
}

public function getPendingJobs(): array
{
    return [
        'by_queue' => [
            'emails' => rand(0, 20),
            'notifications' => rand(0, 30),
            'reports' => rand(0, 10),
            'maintenance' => rand(0, 5)
        ]
    ];
}

public function getFailedJobs(): array
{
    return [
        'last_24h' => rand(0, 10),
        'recent_failures' => [
            [
                'job' => 'SendEmailJob',
                'error' => 'SMTP connection failed',
                'failed_at' => date('Y-m-d H:i:s', time() - rand(300, 3600))
            ]
        ]
    ];
}

public function getProcessedJobs(): array
{
    return [
        'per_hour' => rand(20, 200),
        'success_rate' => rand(90, 99) . '%'
    ];
}

public function getQueueWorkers(): array
{
    return [
        'max_workers' => 20,
        'worker_status' => [
            ['id' => 1, 'status' => 'running', 'current_job' => 'SendEmailJob'],
            ['id' => 2, 'status' => 'idle', 'current_job' => null],
            ['id' => 3, 'status' => 'running', 'current_job' => 'GenerateReportJob']
        ]
    ];
}
