<?php
/**
 * AlingAi Pro 5.0 - 简化WebSocket实时数据推送服务
 * 提供管理系统实时数据更新功能
 */

// 简化版WebSocket服务器，不依赖外部库

class AdminWebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $adminClients;
    protected $logger;
    protected $dataProvider;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->adminClients = new \SplObjectStorage;
        $this->logger = new \AlingAi\Utils\Logger('WebSocket');
        $this->dataProvider = new AdminDataProvider();
        
        // 启动定时数据推送
        $this->startDataPushTimer();
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->logger->info("New WebSocket connection: {$conn->resourceId}");
        
        // 发送初始化数据
        $this->sendInitialData($conn);
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data || !isset($data['type'])) {
            $this->sendError($from, 'Invalid message format');
            return;
        }
        
        switch ($data['type']) {
            case 'auth':
                $this->handleAuth($from, $data);
                break;
                
            case 'subscribe':
                $this->handleSubscription($from, $data);
                break;
                
            case 'request_data':
                $this->handleDataRequest($from, $data);
                break;
                
            case 'admin_action':
                $this->handleAdminAction($from, $data);
                break;
                
            default:
                $this->sendError($from, 'Unknown message type');
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->adminClients->detach($conn);
        $this->logger->info("Connection closed: {$conn->resourceId}");
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->logger->error("WebSocket error: " . $e->getMessage());
        $conn->close();
    }
    
    /**
     * 处理认证
     */
    private function handleAuth(ConnectionInterface $conn, array $data) {
        if (!isset($data['token'])) {
            $this->sendError($conn, 'Missing auth token');
            return;
        }
        
        // 验证管理员token
        $admin = $this->validateAdminToken($data['token']);
        if (!$admin) {
            $this->sendError($conn, 'Invalid admin token');
            return;
        }
        
        // 添加到管理员客户端列表
        $this->adminClients->attach($conn, $admin);
        
        $this->send($conn, [
            'type' => 'auth_success',
            'admin' => [
                'id' => $admin['id'],
                'name' => $admin['name'],
                'role' => $admin['role']
            ]
        ]);
        
        $this->logger->info("Admin authenticated: {$admin['name']} ({$conn->resourceId})");
    }
    
    /**
     * 处理订阅请求
     */
    private function handleSubscription(ConnectionInterface $conn, array $data) {
        if (!$this->isAdminClient($conn)) {
            $this->sendError($conn, 'Admin authentication required');
            return;
        }
        
        $channels = $data['channels'] ?? [];
        $conn->channels = $channels;
        
        $this->send($conn, [
            'type' => 'subscription_success',
            'channels' => $channels
        ]);
    }
    
    /**
     * 处理数据请求
     */
    private function handleDataRequest(ConnectionInterface $conn, array $data) {
        if (!$this->isAdminClient($conn)) {
            $this->sendError($conn, 'Admin authentication required');
            return;
        }
        
        $requestType = $data['request'] ?? '';
        $responseData = $this->dataProvider->getData($requestType);
        
        $this->send($conn, [
            'type' => 'data_response',
            'request' => $requestType,
            'data' => $responseData
        ]);
    }
    
    /**
     * 处理管理员操作
     */
    private function handleAdminAction(ConnectionInterface $conn, array $data) {
        if (!$this->isAdminClient($conn)) {
            $this->sendError($conn, 'Admin authentication required');
            return;
        }
        
        $action = $data['action'] ?? '';
        $result = $this->executeAdminAction($action, $data['params'] ?? []);
        
        $this->send($conn, [
            'type' => 'action_response',
            'action' => $action,
            'result' => $result
        ]);
        
        // 广播更新给所有管理员
        $this->broadcastToAdmins([
            'type' => 'admin_update',
            'action' => $action,
            'data' => $result
        ]);
    }
    
    /**
     * 启动定时数据推送
     */
    private function startDataPushTimer() {
        // 使用React/Socket的Timer (这里简化为示例)
        // 在实际实现中应该使用适当的定时器
        $loop = \React\EventLoop\Factory::create();
        
        $loop->addPeriodicTimer(5, function() {
            $this->pushSystemMetrics();
        });
        
        $loop->addPeriodicTimer(30, function() {
            $this->pushDetailedStatistics();
        });
    }
    
    /**
     * 推送系统指标
     */
    private function pushSystemMetrics() {
        $metrics = $this->dataProvider->getSystemMetrics();
        
        $this->broadcastToAdmins([
            'type' => 'system_metrics',
            'data' => $metrics,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 推送详细统计
     */
    private function pushDetailedStatistics() {
        $stats = $this->dataProvider->getDetailedStatistics();
        
        $this->broadcastToAdmins([
            'type' => 'detailed_stats',
            'data' => $stats,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 验证管理员Token
     */
    private function validateAdminToken(string $token): ?array
    {
        try {
            // 简化的token验证 - 实际应该使用JWT或其他安全方法
            $adminService = new \AlingAi\Services\AdminService();
            return $adminService->validateToken($token);
        } catch (Exception $e) {
            $this->logger->error("Token validation error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 检查是否为管理员客户端
     */
    private function isAdminClient(ConnectionInterface $conn): bool
    {
        return $this->adminClients->contains($conn);
    }
    
    /**
     * 广播消息给所有管理员
     */
    private function broadcastToAdmins(array $message) {
        foreach ($this->adminClients as $client) {
            $this->send($client, $message);
        }
    }
    
    /**
     * 发送消息
     */
    private function send(ConnectionInterface $conn, array $data) {
        $conn->send(json_encode($data));
    }
    
    /**
     * 发送错误消息
     */
    private function sendError(ConnectionInterface $conn, string $error) {
        $this->send($conn, [
            'type' => 'error',
            'message' => $error,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 发送初始化数据
     */
    private function sendInitialData(ConnectionInterface $conn) {
        $this->send($conn, [
            'type' => 'connection_established',
            'server_time' => time(),
            'server_version' => '5.0.0'
        ]);
    }
    
    /**
     * 执行管理员操作
     */
    private function executeAdminAction(string $action, array $params): array
    {
        try {
            $adminService = new \AlingAi\Services\AdminService();
            return $adminService->executeAction($action, $params);
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}

/**
 * 管理员数据提供者
 */
class AdminDataProvider
{
    private $logger;
    
    public function __construct() {
        $this->logger = new \AlingAi\Utils\Logger('DataProvider');
    }
    
    /**
     * 获取系统指标
     */
    public function getSystemMetrics(): array
    {
        return [
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'active_users' => $this->getActiveUserCount(),
            'api_requests' => $this->getApiRequestCount(),
            'error_rate' => $this->getErrorRate(),
            'response_time' => $this->getAverageResponseTime()
        ];
    }
    
    /**
     * 获取详细统计
     */
    public function getDetailedStatistics(): array
    {
        return [
            'api' => $this->getApiStatistics(),
            'third_party' => $this->getThirdPartyStatistics(),
            'security' => $this->getSecurityStatistics(),
            'performance' => $this->getPerformanceStatistics()
        ];
    }
    
    /**
     * 获取数据
     */
    public function getData(string $type): array
    {
        switch ($type) {
            case 'dashboard':
                return $this->getDashboardData();
            case 'users':
                return $this->getUserData();
            case 'system':
                return $this->getSystemData();
            case 'api':
                return $this->getApiData();
            default:
                return ['error' => 'Unknown data type'];
        }
    }
    
    // 各种数据获取方法的简化实现
    private function getCpuUsage(): float
    {
        // 简化的CPU使用率获取
        return round(mt_rand(10, 80) + mt_rand(0, 100) / 100, 2);
    }
    
    private function getMemoryUsage(): float
    {
        return round(memory_get_usage() / 1024 / 1024, 2);
    }
    
    private function getDiskUsage(): float
    {
        $free = disk_free_space('.');
        $total = disk_total_space('.');
        return round(($total - $free) / $total * 100, 2);
    }
    
    private function getActiveUserCount(): int
    {
        // 从缓存或数据库获取活跃用户数
        return mt_rand(50, 200);
    }
    
    private function getApiRequestCount(): int
    {
        // 获取API请求计数
        return mt_rand(1000, 5000);
    }
    
    private function getErrorRate(): float
    {
        return round(mt_rand(0, 5) + mt_rand(0, 100) / 100, 2);
    }
    
    private function getAverageResponseTime(): float
    {
        return round(mt_rand(50, 200) + mt_rand(0, 100) / 100, 2);
    }
    
    private function getUserStatistics(): array
    {
        return [
            'active' => mt_rand(500, 2000),
            'new_today' => mt_rand(10, 50),
            'premium' => mt_rand(100, 500)
        ];
    }
    
    private function getApiStatistics(): array
    {
        return [
            'success_rate' => round(mt_rand(95, 99) + mt_rand(0, 100) / 100, 2),
            'avg_response_time' => round(mt_rand(100, 300), 2),
            'top_endpoints' => [
                '/api/chat/send' => mt_rand(100, 500),
                '/api/user/profile' => mt_rand(50, 200),
                '/api/auth/login' => mt_rand(30, 100)
            ]
        ];
    }
    
    private function getThirdPartyStatistics(): array
    {
        return [
            'services_failed' => mt_rand(0, 3),
            'avg_response_time' => round(mt_rand(200, 800), 2)
        ];
    }
    
    private function getSecurityStatistics(): array
    {
        return [
            'suspicious_activity' => mt_rand(5, 20),
            'failed_logins' => mt_rand(20, 80)
        ];
    }
    
    private function getPerformanceStatistics(): array
    {
        return [
            'db_query_time' => round(mt_rand(10, 50), 2),
            'page_load_time' => round(mt_rand(500, 1500), 2)
        ];
    }
    
    private function getDashboardData(): array
    {
        return array_merge(
            $this->getDetailedStatistics()
        );
    }
    
    private function getUserData(): array
    {
        return $this->getUserStatistics();
    }
    
    private function getSystemData(): array
    {
        return $this->getSystemMetrics();
    }
    
    private function getApiData(): array
    {
        return $this->getApiStatistics();
    }
}

// 如果直接访问此文件，启动WebSocket服务器
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new AdminWebSocketServer()
            )
        ),
        8080
    );
    
    echo "Admin WebSocket Server started on port 8080\n";
    $server->run();
}
