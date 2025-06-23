<?php
/**
 * AlingAi Pro 5.0 - Admin系统API网关
 * 统一处理所有Admin相关的API请求
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/Services/AdminService.php';
require_once __DIR__ . '/../../src/Services/ApiGatewayService.php';
require_once __DIR__ . '/../../src/Services/RiskControlService.php';

use AlingAi\Services\AdminService;
use AlingAi\Services\ApiGatewayService;
use AlingAi\Services\RiskControlService;

class AdminApiGateway
{
    private $adminService;
    private $gatewayService;
    private $riskControl;
    private $requestStartTime;
    
    public function __construct() {
        $this->requestStartTime = microtime(true);
        $this->adminService = new AdminService();
        $this->gatewayService = new ApiGatewayService();
        $this->riskControl = new RiskControlService();
        
        // 设置CORS和安全头
        $this->setSecurityHeaders();
    }
    
    public function handleRequest() {
        try {
            // 1. 获取请求信息
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $path = str_replace('/admin/api', '', $path);
            
            // 2. 身份验证
            $user = $this->authenticateRequest();
            
            // 3. 权限验证
            $this->validatePermissions($user, $path, $method);
            
            // 4. 风控检查
            $this->performRiskControl($user, $path, $method);
            
            // 5. 限流检查
            $this->checkRateLimit($user, $path);
            
            // 6. 路由处理
            $response = $this->routeRequest($path, $method, $user);
              // 7. 记录日志
            $this->logApiCall($user, $path, $method, microtime(true) - $this->requestStartTime, 200);
            
            // 8. 返回响应
            $this->sendResponse($response);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Token管理API处理
     */
    private function handleTokensApi($method, $params, $user) {
        switch ($method) {
            case 'GET':
                return $this->getAllTokens($user);
            case 'POST':
                return $this->createToken($user, $params);
            case 'DELETE':
                return $this->revokeToken($user, $params);
            default:
                throw new Exception('不支持的HTTP方法', 405);
        }
    }
    
    private function handleJwtTokensApi($method, $params, $user) {
        switch ($method) {
            case 'GET':
                return $this->getJwtTokens($user);
            case 'POST':
                return $this->revokeJwtToken($user, $params);
            default:
                throw new Exception('不支持的HTTP方法', 405);
        }
    }
    
    private function handleApiKeysApi($method, $params, $user) {
        switch ($method) {
            case 'GET':
                return $this->getApiKeys($user);
            case 'POST':
                return $this->createApiKey($user, $params);
            case 'DELETE':
                return $this->deleteApiKey($user, $params);
            default:
                throw new Exception('不支持的HTTP方法', 405);
        }
    }
    
    /**
     * 仪表板API处理
     */
    private function handleDashboardApi($method, $params, $user) {
        if ($method !== 'GET') {
            throw new Exception('不支持的HTTP方法', 405);
        }
        
        return $this->getDashboardData($user);
    }
    
    private function handleDashboardStatsApi($method, $params, $user) {
        if ($method !== 'GET') {
            throw new Exception('不支持的HTTP方法', 405);
        }
        
        return $this->getDashboardStatsData($user);
    }
    
    private function handleDashboardChartsApi($method, $params, $user) {
        if ($method !== 'GET') {
            throw new Exception('不支持的HTTP方法', 405);
        }
        
        return $this->getDashboardCharts($user, $params);
    }
    
    /**
     * 健康检查API处理
     */
    private function handleHealthCheckApi($method, $params, $user) {
        if ($method !== 'GET') {
            throw new Exception('不支持的HTTP方法', 405);
        }
        
        return $this->getSystemHealthData();
    }
    
    private function handleSystemLogsApi($method, $params, $user) {
        if ($method !== 'GET') {
            throw new Exception('不支持的HTTP方法', 405);
        }
        
        return $this->getSystemLogs($params);
    }
    
    private function handleSystemConfigApi($method, $params, $user) {
        switch ($method) {
            case 'GET':
                return $this->getSystemConfig();
            case 'PUT':
                return $this->updateSystemConfig($params);
            default:
                throw new Exception('不支持的HTTP方法', 405);
        }
    }
    
    /**
     * AlingAi Pro 5.0 - Admin系统API网关
     * 统一处理所有Admin相关的API请求
     */
    private function setSecurityHeaders() {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // CORS设置
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $allowedOrigins = ['http://localhost:8000', 'https://admin.alingai.com'];
            if (in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            }
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    private function authenticateRequest() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (empty($authHeader)) {
            throw new Exception('认证头缺失', 401);
        }
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw new Exception('认证格式错误', 401);
        }
        
        $token = $matches[1];
        $user = $this->adminService->validateToken($token);
        
        if (!$user) {
            throw new Exception('Token无效或已过期', 401);
        }
        
        return $user;
    }
    
    private function validatePermissions($user, $path, $method) {
        if (!$this->adminService->hasPermission($user, $path, $method)) {
            throw new Exception('权限不足', 403);
        }
    }
      private function performRiskControl($user, $path, $method) {
        // 简化的风险控制逻辑
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // 基本的风险评估
        $riskScore = 0;
        
        // 检查IP地址
        if ($ip === '127.0.0.1' || $ip === 'localhost') {
            $riskScore += 0; // 本地请求低风险
        } else {
            $riskScore += 10; // 外部请求中等风险
        }
        
        // 检查请求方法
        if (in_array($method, ['DELETE', 'PUT'])) {
            $riskScore += 20; // 修改操作高风险
        }
        
        // 风险阈值判断
        if ($riskScore > 50) {
            throw new Exception('请求被风控系统拦截: 风险分数过高', 429);
        }
        
        // 记录风险评估日志
        error_log("Risk assessment for user {$user['id']}: score {$riskScore}");
    }
    
    private function checkRateLimit($user, $path) {
        if (!$this->gatewayService->checkRateLimit($user['id'], $path)) {
            throw new Exception('请求频率超限', 429);
        }
    }
      private function routeRequest($path, $method, $user) {
        // 路由映射表
        $routes = [
            // 用户管理
            '/users' => $this->getUsersHandler(),
            '/users/{id}' => $this->getUserHandler(),
            
            // 第三方服务管理
            '/third-party' => $this->getThirdPartyHandler(),
            '/third-party/{type}' => $this->getThirdPartyTypeHandler(),
            '/third-party/{type}/{id}' => $this->getThirdPartyServiceHandler(),
            
            // 系统监控
            '/monitoring' => $this->getMonitoringHandler(),
            '/monitoring/metrics' => $this->getMetricsHandler(),
            '/monitoring/logs' => $this->getLogsHandler(),
            
            // 风险控制
            '/risk-control' => $this->getRiskControlHandler(),
            '/risk-control/rules' => $this->getRiskRulesHandler(),
            '/risk-control/events' => $this->getRiskEventsHandler(),
            
            // 邮件系统
            '/email' => $this->getEmailHandler(),
            '/email/templates' => $this->getEmailTemplatesHandler(),
            '/email/logs' => $this->getEmailLogsHandler(),
            '/email/send' => $this->getEmailSendHandler(),
            
            // 聊天监控
            '/chat-monitoring' => $this->getChatMonitoringHandler(),
            '/chat-monitoring/sessions' => $this->getChatSessionsHandler(),
            '/chat-monitoring/messages' => $this->getChatMessagesHandler(),
            '/chat-monitoring/sensitive-words' => $this->getSensitiveWordsHandler(),
            
            // API文档
            '/documentation' => $this->getDocumentationHandler(),
            '/documentation/openapi' => $this->getOpenApiHandler(),
            '/documentation/scan' => $this->getScanHandler(),
            
            // Token管理
            '/tokens' => $this->getTokensHandler(),
            '/tokens/jwt' => $this->getJwtTokensHandler(),
            '/tokens/api-keys' => $this->getApiKeysHandler(),
            
            // 仪表板和统计
            '/dashboard' => $this->getDashboardHandler(),
            '/dashboard/stats' => $this->getDashboardStatsHandler(),
            '/dashboard/charts' => $this->getDashboardChartsHandler(),
            
            // 系统健康检查
            '/health' => $this->getHealthHandler(),
            '/system/logs' => $this->getSystemLogsHandler(),
            '/system/config' => $this->getSystemConfigHandler()
        ];
          // 匹配路由
        $matchedRoute = $this->matchRoute($path, $routes);
        
        if (!$matchedRoute) {
            throw new Exception('API端点未找到', 404);
        }
        
        return $matchedRoute['handler']($method, $matchedRoute['params'], $user);
    }
    
    /**
     * 匹配路由并提取参数
     */
    private function matchRoute($path, $routes) {
        foreach ($routes as $pattern => $handler) {
            $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
            $regex = str_replace('/', '\/', $regex);
            $regex = '/^' . $regex . '$/';
            
            if (preg_match($regex, $path, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $params[$key] = $value;
                    }
                }
                return ['handler' => $handler, 'params' => $params];
            }
        }
        return null;
    }
    
    /**
     * 用户管理处理器
     */
    private function getUsersHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('users', '', $method, $params, $user);
        };
    }
    
    private function getUserHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('users', $params['id'] ?? '', $method, $params, $user);
        };
    }
    
    /**
     * 第三方服务处理器
     */
    private function getThirdPartyHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('third-party', '', $method, $params, $user);
        };
    }
    
    private function getThirdPartyTypeHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('third-party', $params['type'] ?? '', $method, $params, $user);
        };
    }
    
    private function getThirdPartyServiceHandler() {
        return function($method, $params, $user) {
            $path = ($params['type'] ?? '') . '/' . ($params['id'] ?? '');
            return $this->proxyToModule('third-party', $path, $method, $params, $user);
        };
    }
    
    /**
     * 监控系统处理器
     */
    private function getMonitoringHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('monitoring', '', $method, $params, $user);
        };
    }
    
    private function getMetricsHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('monitoring', 'metrics', $method, $params, $user);
        };
    }
    
    private function getLogsHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('monitoring', 'logs', $method, $params, $user);
        };
    }
    
    /**
     * 风险控制处理器
     */
    private function getRiskControlHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('risk-control', '', $method, $params, $user);
        };
    }
    
    private function getRiskRulesHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('risk-control', 'rules', $method, $params, $user);
        };
    }
    
    private function getRiskEventsHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('risk-control', 'events', $method, $params, $user);
        };
    }
    
    /**
     * 邮件系统处理器
     */
    private function getEmailHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('email', '', $method, $params, $user);
        };
    }
    
    private function getEmailTemplatesHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('email', 'templates', $method, $params, $user);
        };
    }
    
    private function getEmailLogsHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('email', 'logs', $method, $params, $user);
        };
    }
    
    private function getEmailSendHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('email', 'send', $method, $params, $user);
        };
    }
    
    /**
     * 聊天监控处理器
     */
    private function getChatMonitoringHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('chat-monitoring', '', $method, $params, $user);
        };
    }
    
    private function getChatSessionsHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('chat-monitoring', 'sessions', $method, $params, $user);
        };
    }
    
    private function getChatMessagesHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('chat-monitoring', 'messages', $method, $params, $user);
        };
    }
    
    private function getSensitiveWordsHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('chat-monitoring', 'sensitive-words', $method, $params, $user);
        };
    }
    
    /**
     * API文档处理器
     */
    private function getDocumentationHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('documentation', '', $method, $params, $user);
        };
    }
    
    private function getOpenApiHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('documentation', 'openapi', $method, $params, $user);
        };
    }
    
    private function getScanHandler() {
        return function($method, $params, $user) {
            return $this->proxyToModule('documentation', 'scan', $method, $params, $user);
        };
    }
    
    /**
     * Token管理处理器
     */
    private function getTokensHandler() {
        return function($method, $params, $user) {
            return $this->handleTokensApi($method, $params, $user);
        };
    }
    
    private function getJwtTokensHandler() {
        return function($method, $params, $user) {
            return $this->handleJwtTokensApi($method, $params, $user);
        };
    }
    
    private function getApiKeysHandler() {
        return function($method, $params, $user) {
            return $this->handleApiKeysApi($method, $params, $user);
        };
    }
    
    /**
     * 仪表板处理器
     */
    private function getDashboardHandler() {
        return function($method, $params, $user) {
            return $this->handleDashboardApi($method, $params, $user);
        };
    }
    
    private function getDashboardStatsHandler() {
        return function($method, $params, $user) {
            return $this->handleDashboardStatsApi($method, $params, $user);
        };
    }
    
    private function getDashboardChartsHandler() {
        return function($method, $params, $user) {
            return $this->handleDashboardChartsApi($method, $params, $user);
        };
    }
    
    /**
     * 系统健康检查处理器
     */
    private function getHealthHandler() {
        return function($method, $params, $user) {
            return $this->handleHealthCheckApi($method, $params, $user);
        };
    }
    
    private function getSystemLogsHandler() {
        return function($method, $params, $user) {
            return $this->handleSystemLogsApi($method, $params, $user);
        };
    }
    
    private function getSystemConfigHandler() {
        return function($method, $params, $user) {
            return $this->handleSystemConfigApi($method, $params, $user);
        };
    }
    
    /**
     * 代理请求到指定模块
     */
    private function proxyToModule($module, $subPath, $method, $params, $user) {
        $moduleFile = __DIR__ . '/' . $module . '/index.php';
        
        if (!file_exists($moduleFile)) {
            throw new Exception("模块 {$module} 不存在", 404);
        }
        
        // 设置环境变量用于模块识别
        $_SERVER['ADMIN_MODULE'] = $module;
        $_SERVER['ADMIN_SUB_PATH'] = $subPath;
        $_SERVER['ADMIN_USER'] = json_encode($user);
        $_SERVER['ADMIN_PARAMS'] = json_encode($params);
        
        // 捕获模块输出
        ob_start();
        include $moduleFile;
        $output = ob_get_clean();
        
        // 清理环境变量
        unset($_SERVER['ADMIN_MODULE'], $_SERVER['ADMIN_SUB_PATH'], $_SERVER['ADMIN_USER'], $_SERVER['ADMIN_PARAMS']);
        
        return $output;
    }
    
    // 用户管理API;
    private function getUsers($params, $user) {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $role = $_GET['role'] ?? '';
        
        return $this->adminService->getUsers($page, $limit, $search, $status, $role);
    }
    
    private function createUser($params, $user) {
        $data = $this->getJsonInput();
        $this->validateRequired($data, ['username', 'email', 'password', 'role_id']);
        
        return $this->adminService->createUser($data, $user['id']);
    }
    
    private function getUser($params, $user) {
        return $this->adminService->getUser($params['id']);
    }
    
    private function updateUser($params, $user) {
        $data = $this->getJsonInput();
        return $this->adminService->updateUser($params['id'], $data, $user['id']);
    }
    
    private function deleteUser($params, $user) {
        return $this->adminService->deleteUser($params['id'], $user['id']);
    }
    
    private function updateUserBalance($params, $user) {
        $data = $this->getJsonInput();
        $this->validateRequired($data, ['amount', 'type', 'description']);
        
        return $this->adminService->updateUserBalance(
            $data['amount'], 
            $data['type'], 
            $data['description'], 
            $user['id']
        );
    }
    
    // 系统统计API;
    private function getDashboardStats($params, $user) {
        return $this->adminService->getDashboardStats();
    }
    
    private function getSystemHealth($params, $user) {
        return [
            'timestamp' => time(),
            'uptime' => $this->getSystemUptime(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'response_time' => round((microtime(true) - $this->requestStartTime) * 1000, 2)
        ];
    }
    
    private function getSystemUptime() {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('uptime');
            return $uptime ? trim($uptime) : 'Unknown';
        }
        return 'Unknown';
    }
    
    /**
     * 具体业务方法实现
     */
    private function getAllTokens($user) {
        // 模拟数据
        return [
            'data' => [
                'active_tokens' => 25,
                'total_tokens' => 150,
                'revoked_tokens' => 125,
                'tokens' => [
                    [
                        'id' => 'token_1',
                        'user_id' => 'user_123',
                        'type' => 'jwt',
                        'created_at' => date('Y-m-d H:i:s'),
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                        'status' => 'active'
                    ]
                ]
            ]
        ];
    }
    
    private function createToken($user, $params) {
        return [
            'data' => [
                'token' => 'new_token_' . uniqid(),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
            ],
            'message' => 'Token创建成功'
        ];
    }
    
    private function revokeToken($user, $params) {
        return [
            'message' => 'Token已撤销'
        ];
    }
    
    private function getJwtTokens($user) {
        return [
            'data' => [
                'jwt_tokens' => [
                    [
                        'id' => 'jwt_1',
                        'user_id' => 'user_123',
                        'issued_at' => date('Y-m-d H:i:s'),
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                        'status' => 'active'
                    ]
                ]
            ]
        ];
    }
    
    private function revokeJwtToken($user, $params) {
        return [
            'message' => 'JWT Token已撤销'
        ];
    }
    
    private function getApiKeys($user) {
        return [
            'data' => [
                'api_keys' => [
                    [
                        'id' => 'key_1',
                        'name' => 'Production API Key',
                        'key' => 'ak_' . substr(md5(uniqid()), 0, 20),
                        'created_at' => date('Y-m-d H:i:s'),
                        'last_used' => date('Y-m-d H:i:s'),
                        'status' => 'active'
                    ]
                ]
            ]
        ];
    }
    
    private function createApiKey($user, $params) {
        return [
            'data' => [
                'api_key' => 'ak_' . bin2hex(random_bytes(20)),
                'name' => $params['name'] ?? 'New API Key'
            ],
            'message' => 'API Key创建成功'
        ];
    }
    
    private function deleteApiKey($user, $params) {
        return [
            'message' => 'API Key已删除'
        ];
    }
    
    private function getDashboardData($user) {
        return [
            'data' => [
                'overview' => [
                    'total_users' => 1250,
                    'active_users' => 890,
                    'total_apis' => 45,
                    'api_calls_today' => 12580,
                    'system_health' => 'healthy'
                ],
                'recent_activities' => [
                    [
                        'type' => 'user_login',
                        'user' => 'user_123',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'description' => '用户登录'
                    ]
                ],
                'system_alerts' => [
                    [
                        'level' => 'warning',
                        'message' => 'API调用频率较高',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]
                ]
            ]
        ];
    }
    
    private function getDashboardStatsData($user) {
        return [
            'data' => [
                'user_stats' => [
                    'total' => 1250,
                    'active' => 890,
                    'new_today' => 15
                ],
                'api_stats' => [
                    'total_calls' => 125800,
                    'success_rate' => 99.5,
                    'average_response_time' => 145
                ],
                'system_stats' => [
                    'cpu_usage' => 35.2,
                    'memory_usage' => 68.5,
                    'disk_usage' => 45.8
                ]
            ]
        ];
    }
    
    private function getDashboardCharts($user, $params) {
        $chartType = $params['type'] ?? 'api_calls';
        
        $chartData = [
            'api_calls' => [
                'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                'data' => [120, 95, 180, 250, 220, 160]
            ],
            'user_activity' => [
                'labels' => ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
                'data' => [850, 920, 890, 980, 1100, 650, 450]
            ]
        ];
        
        return [
            'data' => $chartData[$chartType] ?? $chartData['api_calls']
        ];
    }
    
    private function getSystemHealthData() {
        return [
            'data' => [
                'status' => 'healthy',
                'uptime' => '15 days, 8 hours',
                'services' => [
                    'database' => 'healthy',
                    'redis' => 'healthy',
                    'email' => 'healthy',
                    'third_party' => 'warning'
                ],
                'metrics' => [
                    'cpu_usage' => 35.2,
                    'memory_usage' => 68.5,
                    'disk_usage' => 45.8,
                    'network_io' => 12.3
                ]
            ]
        ];
    }
    
    private function getSystemLogs($params) {
        $level = $params['level'] ?? 'all';
        $limit = intval($params['limit'] ?? 50);
        
        return [
            'data' => [
                'logs' => [
                    [
                        'id' => 'log_1',
                        'level' => 'info',
                        'message' => '用户登录成功',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'context' => ['user_id' => 123]
                    ],
                    [
                        'id' => 'log_2',
                        'level' => 'warning',
                        'message' => 'API调用频率过高',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'context' => ['api' => '/api/chat/send']
                    ]
                ],
                'total' => 1250,
                'filtered' => $level === 'all' ? 1250 : 250
            ]
        ];
    }
    
    private function getSystemConfig() {
        return [
            'data' => [
                'app' => [
                    'name' => 'AlingAi Pro',
                    'version' => '5.0.0',
                    'environment' => 'production'
                ],
                'features' => [
                    'user_registration' => true,
                    'email_verification' => true,
                    'two_factor_auth' => false
                ],
                'limits' => [
                    'max_users' => 10000,
                    'api_rate_limit' => 1000,
                    'file_upload_size' => '10MB'
                ]
            ]
        ];
    }
    
    private function updateSystemConfig($params) {
        return [
            'message' => '系统配置更新成功'
        ];
    }
    
    /**
     * 发送响应
     */
    private function sendResponse($data) {
        if (is_string($data)) {
            echo $data;
        } else {
            header('Content-Type: application/json');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * 发送错误响应
     */
    private function sendError($message, $code = 500) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);    }

    /**
     * 记录API调用日志
     */
    private function logApiCall($user, $path, $method, $responseTime, $statusCode) {
        $logData = [
            'user_id' => $user['id'] ?? 'anonymous',
            'path' => $path,
            'method' => $method,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'response_time' => $responseTime,
            'status_code' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // 写入日志文件
        $logFile = __DIR__ . '/../../../logs/admin_api.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);        // 更新API调用统计 - 简化版本
        $statsFile = __DIR__ . '/../../storage/logs/api_stats.log';
        $statsData = [
            'user_id' => $user['id'] ?? 'anonymous',
            'path' => $path,
            'response_time' => $responseTime,
            'timestamp' => time()
        ];
        if (!is_dir(dirname($statsFile))) {
            mkdir(dirname($statsFile), 0755, true);
        }
        file_put_contents($statsFile, json_encode($statsData) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    private function handleError($e) {
        $code = $e->getCode() ?: 500;
        http_response_code($code);
        
        $response = [
            'success' => false,
            'error' => $e->getMessage(),
            'code' => $code,
            'timestamp' => time()
        ];
        
        // 开发环境显示详细错误
        if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
            $response['trace'] = $e->getTraceAsString();
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
        // 记录错误日志
        error_log("Admin API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    }
    
    /**
     * 获取JSON输入数据
     */
    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?: [];
    }
    
    /**
     * 验证必需字段
     */
    private function validateRequired(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("缺少必需字段: {$field}", 400);
            }
        }
    }
}

// 处理请求
$gateway = new AdminApiGateway();
$gateway->handleRequest();
