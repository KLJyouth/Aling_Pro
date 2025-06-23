<?php
/**
 * AlingAi Pro 5.0 - Admin系统演示API
 * 使用文件存储模拟数据库操作
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

class AdminDemoAPI
{
    private $dataDir;
    
    public function __construct() {
        $this->dataDir = __DIR__ . '/demo_data';
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
        $this->initializeDemoData();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/admin/api', '', $path);
        
        try {
            switch ($path) {
                case '/dashboard':
                    return $this->getDashboard();
                case '/api-stats':
                    return $this->getApiStats();
                case '/api-endpoints':
                    return $this->getApiEndpoints();
                case '/users':
                    return $this->getUsers();
                case '/system-health':
                    return $this->getSystemHealth();
                case '/recent-activities':
                    return $this->getRecentActivities();
                case '/alerts':
                    return $this->getAlerts();
                default:
                    return $this->notFound();
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    private function getDashboard() {
        return $this->success([
            'apiCalls' => 56789,
            'riskEvents' => 23,
            'systemStatus' => 'healthy',
            'timestamp' => date('c')
        ]);
    }
    
    private function getApiStats() {
        return $this->success([
            'success_calls' => 55234,
            'error_calls' => 1555,
            'success_rate' => 97.3,
            'avg_response_time' => 234.5,
            'calls_per_hour' => [
                ['hour' => '00:00', 'calls' => 1200],
                ['hour' => '01:00', 'calls' => 800],
                ['hour' => '02:00', 'calls' => 600],
                ['hour' => '03:00', 'calls' => 500],
                ['hour' => '04:00', 'calls' => 700],
                ['hour' => '05:00', 'calls' => 900]
            ]
        ]);
    }
    
    private function getApiEndpoints() {
        return $this->success([
            [
                'endpoint' => '/api/chat/send',
                'calls' => 12543,
                'success_rate' => 99.2,
                'avg_response_time' => 245,
                'status' => 'healthy'
            ],
            [
                'endpoint' => '/api/user/profile',
                'calls' => 8765,
                'success_rate' => 98.8,
                'avg_response_time' => 156,
                'status' => 'healthy'
            ],
            [
                'endpoint' => '/api/auth/login',
                'calls' => 4532,
                'success_rate' => 97.5,
                'avg_response_time' => 389,
                'status' => 'warning'
            ],
            [
                'endpoint' => '/api/system/health',
                'calls' => 3421,
                'success_rate' => 99.9,
                'avg_response_time' => 123,
                'status' => 'healthy'
            ]
        ]);
    }
    
    private function getUsers() {
        return $this->success([
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@alingai.com',
                'role' => 'super_admin',
                'status' => 'active',
                'last_login' => '2025-06-12 10:30:00',
                'login_count' => 156
            ],
            [
                'id' => 2,
                'username' => 'user001',
                'email' => 'user001@example.com',
                'role' => 'user',
                'status' => 'active',
                'last_login' => '2025-06-12 09:15:00',
                'login_count' => 23
            ],
            [
                'id' => 3,
                'username' => 'moderator',
                'email' => 'mod@alingai.com',
                'role' => 'moderator',
                'status' => 'active',
                'last_login' => '2025-06-12 08:45:00',
                'login_count' => 89
            ]
        ]);
    }
    
    private function getSystemHealth() {
        return $this->success([
            'components' => [
                [
                    'name' => 'Web服务器',
                    'status' => 'healthy',
                    'response_time' => '12ms',
                    'uptime' => '99.9%'
                ],
                [
                    'name' => 'API网关',
                    'status' => 'healthy',
                    'response_time' => '45ms',
                    'uptime' => '99.8%'
                ],
                [
                    'name' => 'Redis缓存',
                    'status' => 'warning',
                    'response_time' => '156ms',
                    'uptime' => '98.5%'
                ],
                [
                    'name' => '邮件服务',
                    'status' => 'healthy',
                    'response_time' => '234ms',
                    'uptime' => '99.2%'
                ]
            ],
            'metrics' => [
                'cpu_usage' => 45.6,
                'memory_usage' => 68.2,
                'disk_usage' => 34.8,
                'network_io' => 23.4
            ]
        ]);
    }
    
    private function getRecentActivities() {
        return $this->success([
            [
                'time' => '2分钟前',
                'action' => '用户登录',
                'user' => 'admin@alingai.com',
                'type' => 'info',
                'details' => '管理员用户登录系统'
            ],
            [
                'time' => '5分钟前',
                'action' => 'API调用异常',
                'user' => 'system',
                'type' => 'warning',
                'details' => '/api/chat/send 响应时间过长'
            ],
            [
                'time' => '10分钟前',
                'action' => '新用户注册',
                'user' => 'user123@example.com',
                'type' => 'success',
                'details' => '新用户完成注册'
            ],
            [
                'time' => '15分钟前',
                'action' => '权限修改',
                'user' => 'admin@alingai.com',
                'type' => 'info',
                'details' => '修改用户权限设置'
            ],
            [
                'time' => '20分钟前',
                'action' => '风险事件',
                'user' => 'user456@example.com',
                'type' => 'danger',
                'details' => '检测到异常登录行为'
            ]
        ]);
    }
    
    private function getAlerts() {
        return $this->success([
            [
                'id' => 1,
                'level' => 'warning',
                'title' => 'API响应时间警告',
                'message' => '/api/chat/send 端点平均响应时间超过500ms',
                'time' => '刚刚',
                'status' => 'unread'
            ],
            [
                'id' => 2,
                'level' => 'info',
                'title' => '系统备份完成',
                'message' => '每日自动备份已成功完成',
                'time' => '1小时前',
                'status' => 'read'
            ],
            [
                'id' => 3,
                'level' => 'success',
                'title' => '服务恢复正常',
                'message' => 'Redis缓存服务已恢复正常运行',
                'time' => '2小时前',
                'status' => 'read'
            ],
            [
                'id' => 4,
                'level' => 'danger',
                'title' => '安全警报',
                'message' => '检测到来自IP 192.168.1.100的异常访问',
                'time' => '3小时前',
                'status' => 'unread'
            ]
        ]);
    }
    
    private function initializeDemoData() {
        // 创建一些演示数据文件
        $demoFiles = [
            'users.json' => [
                ['id' => 1, 'username' => 'admin', 'role' => 'admin'],
                ['id' => 2, 'username' => 'user1', 'role' => 'user']
            ],
            'stats.json' => [
                'total_users' => 1234,
                'api_calls' => 56789,
                'last_updated' => date('c')
            ]
        ];
        
        foreach ($demoFiles as $filename => $data) {
            $filepath = $this->dataDir . '/' . $filename;
            if (!file_exists($filepath)) {
                file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
            }
        }
    }
    
    private function success($data) {
        return [
            'data' => $data,
            'timestamp' => date('c')
        ];
    }
    
    private function error($message, $code = 500) {
        http_response_code($code);
        return [
            'error' => $message,
            'timestamp' => date('c')
        ];
    }
    
    private function notFound() {
        return $this->error('API端点未找到', 404);
    }
}

// 执行API请求
$api = new AdminDemoAPI();
$result = $api->handleRequest();

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
