<?php
/**
 * AlingAi Pro 5.0 - 实时数据API服务
 * 基于HTTP的实时数据推送服务，替代WebSocket
 */

header('Content-Type: application/json'];
header('Access-Control-Allow-Origin: *'];
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'];
header('Access-Control-Allow-Headers: Content-Type, Authorization'];

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0];
}

class AdminRealtimeDataService
{
    private $dbConfig;
    
    public function __construct() {
        // 数据库配�?
        $this->dbConfig = [
            'host' => $_ENV['DB_HOST'] ?? '111.180.205.70',
            'dbname' => $_ENV['DB_NAME'] ?? 'alingai',
            'username' => $_ENV['DB_USER'] ?? 'AlingAi',
            'password' => $_ENV['DB_PASS'] ?? 'e5bjzeWCr7k38TrZ'
        ];
    }
    
    /**
     * 获取数据库连�?
     */
    private function getConnection() {
        try {
            $dsn = "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['dbname']};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->dbConfig['username'],  $this->dbConfig['password']];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            return $pdo;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * 获取系统统计数据
     */
    public function getSystemStats() {
        $pdo = $this->getConnection(];
        
        $stats = [
            'system' => [
                'server_time' => date('Y-m-d H:i:s'],
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2],
                'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0,
                'disk_free' => round(disk_free_space('.') / 1024 / 1024 / 1024, 2],
                'php_version' => PHP_VERSION,
                'timestamp' => time()
            ], 
            'database' => [
                'status' => $pdo ? 'connected' : 'disconnected',
                'tables' => $pdo ? $this->getTableCount($pdo) : 0
            ]
        ];
        
        if ($pdo) {
            $stats['admin_users'] = $this->getAdminUserStats($pdo];
            $stats['system_logs'] = $this->getSystemLogStats($pdo];
            $stats['api_calls'] = $this->getApiCallStats($pdo];
        }
        
        return $stats;
    }
    
    /**
     * 获取用户统计数据
     */
    public function getUserStats() {
        $pdo = $this->getConnection(];
        
        if (!$pdo) {
            return ['error' => 'Database connection failed'];
        }
        
        try {
            // 管理员用户统�?
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_users"];
            $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as active FROM admin_users WHERE is_active = 1"];
            $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as recent FROM admin_users WHERE last_login_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"];
            $recentLogins = $stmt->fetch(PDO::FETCH_ASSOC)['recent'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as today FROM admin_users WHERE DATE(created_at) = CURDATE()"];
            $newToday = $stmt->fetch(PDO::FETCH_ASSOC)['today'];
            
            return [
                'total_users' => (int)$totalUsers,
                'active_users' => (int)$activeUsers,
                'recent_logins' => (int)$recentLogins,
                'new_today' => (int)$newToday,
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * 获取API统计数据
     */
    public function getApiStats() {
        $pdo = $this->getConnection(];
        
        if (!$pdo) {
            return ['error' => 'Database connection failed'];
        }
        
        try {
            // 模拟API统计数据，因为还没有API调用日志�?
            $stats = [
                'total_requests' => rand(10000, 50000],
                'success_rate' => round(rand(95, 99) + rand(0, 100) / 100, 2],
                'avg_response_time' => rand(50, 200],
                'errors_today' => rand(0, 10],
                'top_endpoints' => [
                    '/api/users' => rand(1000, 5000],
                    '/api/monitoring' => rand(500, 2000],
                    '/api/third-party' => rand(300, 1000],
                    '/api/risk-control' => rand(100, 500)
                ], 
                'timestamp' => time()
            ];
            
            return $stats;
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * 获取监控数据
     */
    public function getMonitoringData() {
        $pdo = $this->getConnection(];
        
        if (!$pdo) {
            return ['error' => 'Database connection failed'];
        }
        
        try {
            // 获取最近的监控指标
            $stmt = $pdo->query("
                SELECT metric_name, metric_value, metric_unit, timestamp 
                FROM admin_monitoring_metrics 
                ORDER BY timestamp DESC 
                LIMIT 50
            "];
            
            $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC];
            
            return [
                'summary' => [
                    'total_metrics' => count($metrics],
                    'last_update' => $metrics[0]['timestamp'] ?? null
                ], 
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * 获取风控事件数据
     */
    public function getRiskControlData() {
        $pdo = $this->getConnection(];
        
        if (!$pdo) {
            return ['error' => 'Database connection failed'];
        }
        
        try {
            // 获取最近的风控事件
            $stmt = $pdo->query("
                SELECT event_type, risk_level, COUNT(*) as count
                FROM admin_risk_control_events 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY event_type, risk_level
                ORDER BY count DESC
            "];
            
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC];
            
            // 获取未处理的事件数量
            $stmt = $pdo->query("
                SELECT COUNT(*) as pending 
                FROM admin_risk_control_events 
                WHERE resolved = 0
            "];
            
            $pendingCount = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
            
            return [
                'pending_events' => (int)$pendingCount,
                'risk_levels' => [
                    'low' => array_sum(array_column(array_filter($events, function($e) { return $e['risk_level'] === 'low'; }], 'count')],
                    'medium' => array_sum(array_column(array_filter($events, function($e) { return $e['risk_level'] === 'medium'; }], 'count')],
                    'high' => array_sum(array_column(array_filter($events, function($e) { return $e['risk_level'] === 'high'; }], 'count')],
                    'critical' => array_sum(array_column(array_filter($events, function($e) { return $e['risk_level'] === 'critical'; }], 'count'))
                ], 
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * 获取第三方服务状�?
     */
    public function getThirdPartyStatus() {
        $pdo = $this->getConnection(];
        
        if (!$pdo) {
            return ['error' => 'Database connection failed'];
        }
        
        try {
            $stmt = $pdo->query("
                SELECT service_name, type, is_enabled, last_test_at, last_test_result, response_time
                FROM admin_third_party_services
                ORDER BY service_name
            "];
            
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC];
            
            $summary = [
                'total' => count($services],
                'enabled' => count(array_filter($services, function($s) { return $s['is_enabled']; })],
                'avg_response_time' => 0
            ];
            
            if (!empty($services)) {
                $responseTimes = array_filter(array_column($services, 'response_time')];
                $summary['avg_response_time'] = !empty($responseTimes) ? round(array_sum($responseTimes) / count($responseTimes], 2) : 0;
            }
            
            return [
                'summary' => $summary,
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * 获取表数�?
     */
    private function getTableCount($pdo) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'admin_%'"];
            return $stmt->rowCount(];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取管理员用户统�?
     */
    private function getAdminUserStats($pdo) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users"];
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取系统日志统计
     */
    private function getSystemLogStats($pdo) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_system_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"];
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取API调用统计
     */
    private function getApiCallStats($pdo) {
        // 暂时返回模拟数据，因为API调用日志表可能还没有创建
        return rand(100, 1000];
    }
}

// 处理请求
$service = new AdminRealtimeDataService(];
$action = $_GET['action'] ?? 'system_stats';

try {
    switch ($action) {
        case 'system_stats':
            $data = $service->getSystemStats(];
            break;
            
        case 'user_stats':
            $data = $service->getUserStats(];
            break;
            
        case 'api_stats':
            $data = $service->getApiStats(];
            break;
            
        case 'monitoring':
            $data = $service->getMonitoringData(];
            break;
            
        case 'risk_control':
            $data = $service->getRiskControlData(];
            break;
            
        case 'third_party':
            $data = $service->getThirdPartyStatus(];
            break;
            
        case 'all':
            $data = [
                'system' => $service->getSystemStats(),
                'users' => $service->getUserStats(),
                'api' => $service->getApiStats(),
                'monitoring' => $service->getMonitoringData(),
                'risk_control' => $service->getRiskControlData(),
                'third_party' => $service->getThirdPartyStatus()
            ];
            break;
            
        default:
            $data = ['error' => 'Unknown action: ' . $action];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => time(),
        'server_time' => date('Y-m-d H:i:s')
    ],  JSON_PRETTY_PRINT];
    
} catch (Exception $e) {
    http_response_code(500];
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ],  JSON_PRETTY_PRINT];
}

