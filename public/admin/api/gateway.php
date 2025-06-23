<?php
/**
 * AlingAi Pro 5.0 - Admin系统API网关
 * 统一处理所有Admin相关的API请求
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/Auth/AdminAuthServiceDemo.php';

use AlingAi\Auth\AdminAuthServiceDemo;

class AdminApiGateway
{
    private $requestStartTime;
    private $authService;
    
    public function __construct() {
        $this->requestStartTime = microtime(true);
        $this->authService = new AdminAuthServiceDemo();
    }
    
    public function handleRequest() {
        try {
            // 获取请求信息
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $path = str_replace('/admin/api', '', $path);
            
            // 验证管理员权限
            if (!$this->authService->verifyAdminAccess()) {
                $this->sendError('需要管理员权限', 403);
                return;
            }
            
            // 路由处理
            $result = $this->routeRequest($path, $method);
            
            // 记录API调用
            $responseTime = (microtime(true) - $this->requestStartTime) * 1000;
            $this->logApiCall($path, $method, $responseTime, 200);
            
            // 发送响应
            $this->sendResponse($result);
            
        } catch (Exception $e) {
            $responseTime = (microtime(true) - $this->requestStartTime) * 1000;
            $this->logApiCall($path ?? '', $method ?? 'UNKNOWN', $responseTime, $e->getCode() ?: 500);
            $this->sendError($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    private function routeRequest($path, $method) {
        // 路由映射表
        $routes = [
            // 主要模块路由 - 直接代理到对应模块
            '/users' => 'users',
            '/third-party' => 'third-party',
            '/monitoring' => 'monitoring',
            '/risk-control' => 'risk-control',
            '/email' => 'email',
            '/chat-monitoring' => 'chat-monitoring',
            '/documentation' => 'documentation',
            
            // 内置API端点
            '/dashboard' => 'dashboard',
            '/tokens' => 'tokens',
            '/health' => 'health',
            '/system' => 'system'
        ];
        
        // 匹配路由
        $matchedModule = null;
        $subPath = '';
        
        foreach ($routes as $pattern => $module) {
            if (strpos($path, $pattern) === 0) {
                $matchedModule = $module;
                $subPath = substr($path, strlen($pattern));
                break;
            }
        }
        
        if (!$matchedModule) {
            throw new Exception('API端点未找到: ' . $path, 404);
        }
        
        // 处理内置端点
        if (in_array($matchedModule, ['dashboard', 'tokens', 'health', 'system'])) {
            return $this->handleBuiltinApi($matchedModule, $subPath, $method);
        }
        
        // 代理到模块
        return $this->proxyToModule($matchedModule, $subPath, $method);
    }
    
    private function proxyToModule($module, $subPath, $method) {
        $moduleFile = __DIR__ . '/' . $module . '/index.php';
        
        if (!file_exists($moduleFile)) {
            throw new Exception("模块 {$module} 不存在", 404);
        }
        
        // 设置环境变量
        $_SERVER['ADMIN_MODULE'] = $module;
        $_SERVER['ADMIN_SUB_PATH'] = $subPath;
        $_SERVER['ORIGINAL_REQUEST_METHOD'] = $method;
        
        // 重写请求路径
        $_SERVER['REQUEST_URI'] = '/admin/api/' . $module . $subPath;
        
        // 捕获输出
        ob_start();
        include $moduleFile;
        $output = ob_get_clean();
        
        // 清理环境
        unset($_SERVER['ADMIN_MODULE'], $_SERVER['ADMIN_SUB_PATH'], $_SERVER['ORIGINAL_REQUEST_METHOD']);
        
        // 如果输出是JSON，解析后返回，否则直接返回
        $decoded = json_decode($output, true);
        return $decoded !== null ? $decoded : $output;
    }
    
    private function handleBuiltinApi($module, $subPath, $method) {
        switch ($module) {
            case 'dashboard':
                return $this->handleDashboard($subPath, $method);
                return $this->handleTokens($subPath, $method);
                return $this->handleHealth($subPath, $method);
                return $this->handleSystem($subPath, $method);
                throw new Exception('未知的内置模块: ' . $module, 404);
        }
    }
    
    private function handleDashboard($subPath, $method) {
        if ($method !== 'GET') {
            throw new Exception('不支持的HTTP方法', 405);
        }
        
        switch ($subPath) {
            case '':
                return $this->getDashboardOverview();
                return $this->getDashboardStats();
                return $this->getDashboardCharts();
                throw new Exception('未知的仪表板端点: ' . $subPath, 404);
        }
    }
    
    private function handleTokens($subPath, $method) {
        switch ($subPath) {
            case '':
                return $this->handleTokensRoot($method);
                return $this->handleJwtTokens($method);
                return $this->handleApiKeys($method);
                throw new Exception('未知的Token端点: ' . $subPath, 404);
        }
    }
    
    private function handleHealth($subPath, $method) {
        if ($method !== 'GET') {
            throw new Exception('不支持的HTTP方法', 405);
        }
        
        return $this->getSystemHealth();
    }
    
    private function handleSystem($subPath, $method) {
        switch ($subPath) {
            case '/logs':
                return $this->getSystemLogs($method);
                return $this->handleSystemConfig($method);
                throw new Exception('未知的系统端点: ' . $subPath, 404);
        }
    }
    
    // ============ 仪表板API实现 ============
    
    private function getDashboardOverview() {
        return [
            'data' => [
                'overview' => [
                    'total_users' => 1250,
                    'active_users' => 890,
                    'total_apis' => 45,
                    'api_calls_today' => 12580,
                    'system_health' => 'healthy'
                ],
                'modules_status' => [
                    'users' => ['status' => 'active', 'endpoints' => 8],
                    'third_party' => ['status' => 'active', 'endpoints' => 12],
                    'monitoring' => ['status' => 'active', 'endpoints' => 6],
                    'risk_control' => ['status' => 'active', 'endpoints' => 10],
                    'email' => ['status' => 'active', 'endpoints' => 9],
                    'chat_monitoring' => ['status' => 'active', 'endpoints' => 7],
                    'documentation' => ['status' => 'active', 'endpoints' => 4]
                ],
                'recent_activities' => [
                    [
                        'type' => 'api_call',
                        'description' => '用户管理API调用',
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-2 minutes'))
                    ],
                    [
                        'type' => 'system_alert',
                        'description' => 'CPU使用率达到80%',
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
                    ],
                    [
                        'type' => 'user_action',
                        'description' => '新用户注册',
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
                    ]
                ]
            ],
            'message' => '仪表板数据获取成功'
        ];
    }
    
    private function getDashboardStats() {
        return [
            'data' => [
                'user_stats' => [
                    'total' => 1250,
                    'active_today' => 290,
                    'new_today' => 15,
                    'growth_rate' => 12.5
                ],
                'api_stats' => [
                    'total_calls_today' => 12580,
                    'success_rate' => 99.2,
                    'average_response_time' => 145,
                    'error_rate' => 0.8
                ],
                'system_stats' => [
                    'cpu_usage' => 35.2,
                    'memory_usage' => 68.5,
                    'disk_usage' => 45.8,
                    'uptime' => '15 days, 8 hours'
                ],
                'security_stats' => [
                    'blocked_attempts' => 23,
                    'flagged_sessions' => 5,
                    'risk_events' => 12,
                    'security_score' => 95.5
                ]
            ],
            'message' => '统计数据获取成功'
        ];
    }
    
    private function getDashboardCharts() {
        $type = $_GET['type'] ?? 'api_calls';
        
        $charts = [
            'api_calls' => [
                'title' => 'API调用趋势',
                'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                'datasets' => [
                    [
                        'label' => 'API调用次数',
                        'data' => [120, 95, 180, 250, 220, 160],
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)'
                    ]
                ]
            ],
            'user_activity' => [
                'title' => '用户活跃度',
                'labels' => ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
                'datasets' => [
                    [
                        'label' => '活跃用户数',
                        'data' => [850, 920, 890, 980, 1100, 650, 450],
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)'
                    ]
                ]
            ],
            'system_performance' => [
                'title' => '系统性能',
                'labels' => ['CPU', '内存', '磁盘', '网络'],
                'datasets' => [
                    [
                        'label' => '使用率 (%)',
                        'data' => [35.2, 68.5, 45.8, 23.1],
                        'backgroundColor' => [
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ]
                    ]
                ]
            ]
        ];
        
        return [
            'data' => $charts[$type] ?? $charts['api_calls'],
            'message' => '图表数据获取成功'
        ];
    }
    
    // ============ Token管理API实现 ============
    
    private function handleTokensRoot($method) {
        switch ($method) {
            case 'GET':
                return $this->getAllTokens();
                return $this->createToken();
                return $this->revokeToken();
                throw new Exception('不支持的HTTP方法', 405);
        }
    }
    
    private function handleJwtTokens($method) {
        switch ($method) {
            case 'GET':
                return $this->getJwtTokens();
                return $this->revokeJwtToken();
                throw new Exception('不支持的HTTP方法', 405);
        }
    }
    
    private function handleApiKeys($method) {
        switch ($method) {
            case 'GET':
                return $this->getApiKeys();
                return $this->createApiKey();
                return $this->deleteApiKey();
                throw new Exception('不支持的HTTP方法', 405);
        }
    }
    
    private function getAllTokens() {
        return [
            'data' => [
                'summary' => [
                    'total_tokens' => 156,
                    'active_tokens' => 89,
                    'expired_tokens' => 45,
                    'revoked_tokens' => 22
                ],
                'recent_tokens' => [
                    [
                        'id' => 'token_' . uniqid(),
                        'user_id' => 'user_123',
                        'type' => 'jwt',
                        'created_at' => date('Y-m-d H:i:s'),
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                        'status' => 'active',
                        'last_used' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                    ],
                    [
                        'id' => 'token_' . uniqid(),
                        'user_id' => 'user_456',
                        'type' => 'api_key',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                        'status' => 'active',
                        'last_used' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
                    ]
                ]
            ],
            'message' => 'Token列表获取成功'
        ];
    }
    
    private function createToken() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        return [
            'data' => [
                'token_id' => 'token_' . uniqid(),
                'token' => 'tok_' . bin2hex(random_bytes(20)),
                'type' => $data['type'] ?? 'jwt',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
            ],
            'message' => 'Token创建成功'
        ];
    }
    
    private function revokeToken() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        return [
            'message' => 'Token已撤销: ' . ($data['token_id'] ?? 'unknown')
        ];
    }
    
    private function getJwtTokens() {
        return [
            'data' => [
                'jwt_tokens' => [
                    [
                        'id' => 'jwt_' . uniqid(),
                        'user_id' => 'user_123',
                        'issued_at' => date('Y-m-d H:i:s'),
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                        'algorithm' => 'HS256',
                        'status' => 'active'
                    ]
                ],
                'statistics' => [
                    'total_issued' => 1250,
                    'active_tokens' => 89,
                    'expired_today' => 23
                ]
            ],
            'message' => 'JWT Token列表获取成功'
        ];
    }
    
    private function revokeJwtToken() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        return [
            'message' => 'JWT Token已撤销: ' . ($data['token_id'] ?? 'unknown')
        ];
    }
    
    private function getApiKeys() {
        return [
            'data' => [
                'api_keys' => [
                    [
                        'id' => 'key_' . uniqid(),
                        'name' => 'Production API Key',
                        'key_prefix' => 'ak_' . substr(md5(uniqid()), 0, 8) . '...',
                        'created_at' => date('Y-m-d H:i:s'),
                        'last_used' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                        'permissions' => ['read', 'write'],
                        'status' => 'active'
                    ],
                    [
                        'id' => 'key_' . uniqid(),
                        'name' => 'Development API Key',
                        'key_prefix' => 'ak_' . substr(md5(uniqid()), 0, 8) . '...',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                        'last_used' => date('Y-m-d H:i:s', strtotime('-1 day')),
                        'permissions' => ['read'],
                        'status' => 'active'
                    ]
                ],
                'usage_stats' => [
                    'total_requests' => 15620,
                    'requests_today' => 892,
                    'unique_keys_used' => 12
                ]
            ],
            'message' => 'API Keys列表获取成功'
        ];
    }
    
    private function createApiKey() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        return [
            'data' => [
                'api_key_id' => 'key_' . uniqid(),
                'api_key' => 'ak_' . bin2hex(random_bytes(20)),
                'name' => $data['name'] ?? 'New API Key',
                'permissions' => $data['permissions'] ?? ['read']
            ],
            'message' => 'API Key创建成功'
        ];
    }
    
    private function deleteApiKey() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        return [
            'message' => 'API Key已删除: ' . ($data['key_id'] ?? 'unknown')
        ];
    }
    
    // ============ 系统健康检查API实现 ============
    
    private function getSystemHealth() {
        return [
            'data' => [
                'overall_status' => 'healthy',
                'uptime' => '15 days, 8 hours, 32 minutes',
                'version' => '5.0.0',
                'services' => [
                    'database' => [
                        'status' => 'healthy',
                        'response_time' => '12ms',
                        'connections' => 45
                    ],
                    'redis' => [
                        'status' => 'healthy',
                        'response_time' => '2ms',
                        'memory_usage' => '245MB'
                    ],
                    'email_service' => [
                        'status' => 'healthy',
                        'queue_size' => 23,
                        'success_rate' => '99.5%'
                    ],
                    'third_party_apis' => [
                        'status' => 'warning',
                        'available_services' => 8,
                        'failing_services' => 1
                    ]
                ],
                'system_metrics' => [
                    'cpu_usage' => 35.2,
                    'memory_usage' => 68.5,
                    'disk_usage' => 45.8,
                    'network_io' => 12.3,
                    'load_average' => [1.2, 1.1, 1.0]
                ],
                'api_health' => [
                    'total_endpoints' => 67,
                    'healthy_endpoints' => 65,
                    'degraded_endpoints' => 2,
                    'failed_endpoints' => 0
                ]
            ],
            'message' => '系统健康状况获取成功'
        ];
    }
    
    // ============ 系统API实现 ============
    
    private function getSystemLogs($method) {
        if ($method !== 'GET') {
            throw new Exception('不支持的HTTP方法', 405);
        }
        
        $level = $_GET['level'] ?? 'all';
        $limit = intval($_GET['limit'] ?? 50);
        $page = intval($_GET['page'] ?? 1);
        
        return [
            'data' => [
                'logs' => [
                    [
                        'id' => 'log_' . uniqid(),
                        'level' => 'info',
                        'message' => '管理员登录成功',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'context' => [
                            'user_id' => 'admin_123',
                            'ip' => '192.168.1.100'
                        ]
                    ],
                    [
                        'id' => 'log_' . uniqid(),
                        'level' => 'warning',
                        'message' => 'API调用频率过高',
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
                        'context' => [
                            'endpoint' => '/api/chat/send',
                            'rate' => '150 req/min'
                        ]
                    ],
                    [
                        'id' => 'log_' . uniqid(),
                        'level' => 'error',
                        'message' => '第三方服务连接失败',
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
                        'context' => [
                            'service' => 'payment_gateway',
                            'error_code' => 'CONNECTION_TIMEOUT'
                        ]
                    ]
                ],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => 1250,
                    'total_pages' => ceil(1250 / $limit)
                ],
                'filters' => [
                    'level' => $level,
                    'available_levels' => ['all', 'debug', 'info', 'warning', 'error', 'critical']
                ]
            ],
            'message' => '系统日志获取成功'
        ];
    }
    
    private function handleSystemConfig($method) {
        switch ($method) {
            case 'GET':
                return $this->getSystemConfig();
                return $this->updateSystemConfig();
                throw new Exception('不支持的HTTP方法', 405);
        }
    }
    
    private function getSystemConfig() {
        return [
            'data' => [
                'application' => [
                    'name' => 'AlingAi Pro',
                    'version' => '5.0.0',
                    'environment' => 'production',
                    'debug_mode' => false,
                    'maintenance_mode' => false
                ],
                'security' => [
                    'jwt_secret_set' => true,
                    'api_key_required' => true,
                    'rate_limiting_enabled' => true,
                    'two_factor_auth' => false,
                    'password_policy' => [
                        'min_length' => 8,
                        'require_uppercase' => true,
                        'require_numbers' => true,
                        'require_symbols' => false
                    ]
                ],
                'features' => [
                    'user_registration' => true,
                    'email_verification' => true,
                    'password_reset' => true,
                    'social_login' => true,
                    'file_upload' => true,
                    'realtime_chat' => true
                ],
                'limits' => [
                    'max_users' => 10000,
                    'api_rate_limit' => 1000,
                    'file_upload_size' => '10MB',
                    'session_timeout' => 3600,
                    'password_reset_timeout' => 1800
                ],
                'integrations' => [
                    'email_service' => 'configured',
                    'payment_gateway' => 'configured',
                    'social_auth' => 'configured',
                    'monitoring' => 'enabled',
                    'logging' => 'enabled'
                ]
            ],
            'message' => '系统配置获取成功'
        ];
    }
    
    private function updateSystemConfig() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // 这里应该有实际的配置更新逻辑
        // 为了演示，我们只是返回成功响应
        
        return [
            'data' => [
                'updated_fields' => array_keys($data),
                'timestamp' => date('Y-m-d H:i:s')
            ],
            'message' => '系统配置更新成功'
        ];
    }
    
    // ============ 工具方法 ============
    
    private function sendResponse($data) {
        if (is_string($data)) {
            echo $data;
        } else {
            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }
    
    private function sendError($message, $code = 500) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    }
    
    private function logApiCall($path, $method, $responseTime, $statusCode) {
        $logData = [
            'path' => $path,
            'method' => $method,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'response_time' => round($responseTime, 2) . 'ms',
            'status_code' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $logFile = __DIR__ . '/../../../logs/admin_api_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }
}

// 启动API网关
try {
    $gateway = new AdminApiGateway();
    $gateway->handleRequest();
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode() ?: 500,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
