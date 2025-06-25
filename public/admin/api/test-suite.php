<?php
/**
 * AlingAi Pro 5.0 - Admin系统集成测试套件
 * 验证所有API模块协同工作，确保系统稳定�? */

require_once __DIR__ . '/../../vendor/autoload.php';

class AdminSystemTestSuite
{
    private $testResults = [];
    private $logger;
    private $startTime;
    private $baseUrl;
    private $adminToken;
    
    public function __construct() {
        $this->logger = new \AlingAi\Utils\Logger('AdminTest'];
        $this->baseUrl = 'http://localhost/admin/api';
        $this->startTime = microtime(true];
        
        // 初始化测试环�?        $this->initializeTestEnvironment(];
    }
    
    /**
     * 运行所有测�?     */
    public function runAllTests(): array
    {
        $this->logger->info('Starting Admin System Integration Tests'];
        
        $testSuites = [
            'API网关测试' => [$this, 'testApiGateway'], 
            '用户管理API测试' => [$this, 'testUserManagementApi'], 
            '第三方服务API测试' => [$this, 'testThirdPartyApi'], 
            '监控API测试' => [$this, 'testMonitoringApi'], 
            '风控API测试' => [$this, 'testRiskControlApi'], 
            '邮件API测试' => [$this, 'testEmailApi'], 
            '聊天监控API测试' => [$this, 'testChatMonitoringApi'], 
            'Token管理测试' => [$this, 'testTokenManagement'], 
            'WebSocket测试' => [$this, 'testWebSocket'], 
            '数据库完整性测�? => [$this, 'testDatabaseIntegrity'], 
            '权限系统测试' => [$this, 'testPermissionSystem'], 
            '性能测试' => [$this, 'testPerformance'], 
            '安全测试' => [$this, 'testSecurity']
        ];
        
        foreach ($testSuites as $suiteName => $testMethod) {
            $this->logger->info("Running test suite: {$suiteName}"];
            
            try {
                $result = call_user_func($testMethod];
                $this->testResults[$suiteName] = $result;
            } catch (Exception $e) {
                $this->testResults[$suiteName] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];
            }
        }
        
        return $this->generateTestReport(];
    }
    
    /**
     * 测试API网关
     */
    private function testApiGateway(): array
    {
        $results = [];
        
        // 测试网关状�?        $response = $this->makeRequest('GET', '/gateway.php?action=status'];
        $results['gateway_status'] = $this->validateResponse($response, ['status', 'version']];
        
        // 测试认证要求
        $response = $this->makeRequest('GET', '/users/', [],  false];
        $results['auth_required'] = $response['status_code'] === 401;
        
        // 测试认证功能
        $response = $this->makeRequest('GET', '/users/', [],  true];
        $results['auth_working'] = $response['status_code'] === 200;
        
        // 测试限流
        $requests = [];
        for ($i = 0; $i < 10; $i++) {
            $requests[] = $this->makeRequest('GET', '/gateway.php?action=status'];
        }
        $results['rate_limiting'] = $this->checkRateLimiting($requests];
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试用户管理API
     */
    private function testUserManagementApi(): array
    {
        $results = [];
        
        // 测试获取用户列表
        $response = $this->makeRequest('GET', '/users/', [],  true];
        $results['get_users'] = $this->validateResponse($response, ['users', 'pagination']];
        
        // 测试创建用户
        $userData = [
            'username' => 'test_user_' . time(),
            'email' => 'test' . time() . '@example.com',
            'password' => 'test123456',
            'role' => 'user'
        ];
        $response = $this->makeRequest('POST', '/users/', $userData, true];
        $results['create_user'] = $this->validateResponse($response, ['user_id', 'success']];
        $testUserId = $response['data']['user_id'] ?? null;
        
        // 测试获取单个用户
        if ($testUserId) {
            $response = $this->makeRequest('GET', "/users/{$testUserId}", [],  true];
            $results['get_user'] = $this->validateResponse($response, ['user']];
            
            // 测试更新用户
            $updateData = ['username' => 'updated_user_' . time()];
            $response = $this->makeRequest('PUT', "/users/{$testUserId}", $updateData, true];
            $results['update_user'] = $this->validateResponse($response, ['success']];
            
            // 测试删除用户
            $response = $this->makeRequest('DELETE', "/users/{$testUserId}", [],  true];
            $results['delete_user'] = $this->validateResponse($response, ['success']];
        }
        
        // 测试批量操作
        $response = $this->makeRequest('POST', '/users/batch', ['action' => 'export'],  true];
        $results['batch_operations'] = $this->validateResponse($response, ['success']];
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试第三方服务API
     */
    private function testThirdPartyApi(): array
    {
        $results = [];
        
        // 测试服务列表
        $response = $this->makeRequest('GET', '/third-party/services', [],  true];
        $results['list_services'] = $this->validateResponse($response, ['services']];
        
        // 测试服务状�?        $response = $this->makeRequest('GET', '/third-party/status', [],  true];
        $results['service_status'] = $this->validateResponse($response, ['status']];
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试监控API
     */
    private function testMonitoringApi(): array
    {
        $results = [];
        
        // 测试系统状�?        $response = $this->makeRequest('GET', '/monitoring/system', [],  true];
        $results['system_status'] = $this->validateResponse($response, ['status', 'metrics']];
        
        // 测试性能指标
        $response = $this->makeRequest('GET', '/monitoring/performance', [],  true];
        $results['performance_metrics'] = $this->validateResponse($response, ['metrics']];
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试风控API
     */
    private function testRiskControlApi(): array
    {
        $results = [];
        
        // 测试风控规则
        $response = $this->makeRequest('GET', '/risk-control/rules', [],  true];
        $results['risk_rules'] = $this->validateResponse($response, ['rules']];
        
        // 测试风控事件
        $response = $this->makeRequest('GET', '/risk-control/events', [],  true];
        $results['risk_events'] = $this->validateResponse($response, ['events']];
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试邮件API
     */
    private function testEmailApi(): array
    {
        $results = [];
        
        // 测试邮件模板
        $response = $this->makeRequest('GET', '/email/templates', [],  true];
        $results['email_templates'] = $this->validateResponse($response, ['templates']];
        
        // 测试发送邮�?        $emailData = [
            'to' => 'test@example.com',
            'subject' => 'Test Email',
            'body' => 'This is a test email'
        ];
        $response = $this->makeRequest('POST', '/email/send', $emailData, true];
        $results['send_email'] = $this->validateResponse($response, ['success']];
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试聊天监控API
     */
    private function testChatMonitoringApi(): array
    {
        $results = [];
        
        // 测试聊天记录
        $response = $this->makeRequest('GET', '/chat-monitoring/records', [],  true];
        $results['chat_records'] = $this->validateResponse($response, ['records']];
        
        // 测试敏感词检�?        $testData = ['content' => '测试内容'];
        $response = $this->makeRequest('POST', '/chat-monitoring/check', $testData, true];
        $results['sensitive_check'] = $this->validateResponse($response, ['is_sensitive']];
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试Token管理
     */
    private function testTokenManagement(): array
    {
        $results = [];
        
        // 测试Token生成
        $response = $this->makeRequest('POST', '/auth/token', [
            'username' => 'admin',
            'password' => 'admin123'
        ],  false];
        $results['token_generation'] = $this->validateResponse($response, ['token']];
        
        // 测试Token验证
        if (isset($response['data']['token'])) {
            $this->adminToken = $response['data']['token'];
            $response = $this->makeRequest('GET', '/auth/verify', [],  true];
            $results['token_verification'] = $this->validateResponse($response, ['valid']];
        }
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试WebSocket
     */
    private function testWebSocket(): array
    {
        $results = [];
        
        // 测试WebSocket连接
        $wsClient = new \WebSocket\Client('ws://localhost:8080'];
        $results['ws_connection'] = $wsClient->isConnected(];
        
        // 测试消息发�?        if ($results['ws_connection']) {
            $wsClient->send(json_encode(['type' => 'test'])];
            $response = $wsClient->receive(];
            $results['ws_message'] = !empty($response];
        }
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试数据库完整�?     */
    private function testDatabaseIntegrity(): array
    {
        $results = [];
        
        // 测试数据库连�?        $db = new \PDO('sqlite:' . __DIR__ . '/../../../database/admin_system.db'];
        $results['db_connection'] = $db !== null;
        
        // 测试表结�?        $tables = ['admin_users', 'admin_tokens', 'admin_permissions'];
        foreach ($tables as $table) {
            $results["table_{$table}"] = $this->checkTableExists($db, $table];
        }
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试权限系统
     */
    private function testPermissionSystem(): array
    {
        $results = [];
        
        // 测试权限列表
        $response = $this->makeRequest('GET', '/permissions', [],  true];
        $results['permission_list'] = $this->validateResponse($response, ['permissions']];
        
        // 测试权限分配
        $permissionData = [
            'user_id' => 1,
            'permission_id' => 1
        ];
        $response = $this->makeRequest('POST', '/permissions/assign', $permissionData, true];
        $results['permission_assignment'] = $this->validateResponse($response, ['success']];
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试性能
     */
    private function testPerformance(): array
    {
        $results = [];
        
        // 测试API响应时间
        $startTime = microtime(true];
        $this->makeRequest('GET', '/gateway.php?action=status'];
        $results['api_response_time'] = microtime(true) - $startTime;
        
        // 测试并发请求
        $concurrentRequests = 10;
        $startTime = microtime(true];
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $this->makeRequest('GET', '/gateway.php?action=status'];
        }
        $results['concurrent_performance'] = microtime(true) - $startTime;
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 测试安全�?     */
    private function testSecurity(): array
    {
        $results = [];
        
        // 测试SQL注入防护
        $response = $this->makeRequest('GET', '/users/?id=1\' OR \'1\'=\'1', [],  true];
        $results['sql_injection'] = $response['status_code'] === 400;
        
        // 测试XSS防护
        $response = $this->makeRequest('POST', '/users/', [
            'username' => '<script>alert(1)</script>'
        ],  true];
        $results['xss_protection'] = $response['status_code'] === 400;
        
        return [
            'success' => $this->allTestsPassed($results],
            'details' => $results
        ];
    }
    
    /**
     * 初始化测试环�?     */
    private function initializeTestEnvironment(): void
    {
        // 设置测试环境变量
        putenv('APP_ENV=testing'];
        
        // 初始化数据库连接
        $this->initializeDatabase(];
        
        // 清理测试数据
        $this->cleanupTestData(];
    }
    
    /**
     * 初始化数据库
     */
    private function initializeDatabase(): void
    {
        $db = new \PDO('sqlite:' . __DIR__ . '/../../../database/admin_system.db'];
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION];
        
        // 创建测试�?        $db->exec("
            CREATE TABLE IF NOT EXISTS test_data (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "];
    }
    
    /**
     * 清理测试数据
     */
    private function cleanupTestData(): void
    {
        $db = new \PDO('sqlite:' . __DIR__ . '/../../../database/admin_system.db'];
        $db->exec("DELETE FROM test_data"];
    }
    
    /**
     * 发送HTTP请求
     */
    private function makeRequest(string $method, string $endpoint, array $data = [],  bool $useAuth = true): array
    {
        $url = $this->baseUrl . $endpoint;
        $headers = ['Content-Type: application/json'];
        
        if ($useAuth && $this->adminToken) {
            $headers[] = 'Authorization: Bearer ' . $this->adminToken;
        }
        
        $ch = curl_init(];
        curl_setopt($ch, CURLOPT_URL, $url];
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method];
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)];
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers];
        
        $response = curl_exec($ch];
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE];
        curl_close($ch];
        
        return [
            'status_code' => $statusCode,
            'data' => json_decode($response, true) ?? []
        ];
    }
    
    /**
     * 验证响应数据
     */
    private function validateResponse(array $response, array $requiredFields): bool
    {
        if ($response['status_code'] !== 200) {
            return false;
        }
        
        foreach ($requiredFields as $field) {
            if (!isset($response['data'][$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 检查限�?     */
    private function checkRateLimiting(array $requests): bool
    {
        $successCount = 0;
        foreach ($requests as $request) {
            if ($request['status_code'] === 200) {
                $successCount++;
            }
        }
        
        return $successCount >= 8; // 允许80%的请求通过
    }
    
    /**
     * 检查表是否存在
     */
    private function checkTableExists(\PDO $db, string $tableName): bool
    {
        try {
            $result = $db->query("SELECT 1 FROM {$tableName} LIMIT 1"];
            return $result !== false;
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * 检查所有测试是否通过
     */
    private function allTestsPassed(array $results): bool
    {
        foreach ($results as $result) {
            if ($result === false) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 生成测试报告
     */
    private function generateTestReport(): array
    {
        $totalTests = count($this->testResults];
        $passedTests = 0;
        $failedTests = [];
        
        foreach ($this->testResults as $suiteName => $result) {
            if ($result['success']) {
                $passedTests++;
            } else {
                $failedTests[$suiteName] = $result['error'];
            }
        }
        
        return [
                'total_tests' => $totalTests,
                'passed_tests' => $passedTests,
            'failed_tests' => $failedTests,
            'success_rate' => ($passedTests / $totalTests) * 100,
            'execution_time' => microtime(true) - $this->startTime
        ];
    }
}

// 运行测试套件
$testSuite = new AdminSystemTestSuite(];
$report = $testSuite->runAllTests(];

// 输出测试报告
echo "\n📊 Test Report:\n";
echo "Total Tests: {$report['total_tests']}\n";
echo "Passed Tests: {$report['passed_tests']}\n";
echo "Success Rate: {$report['success_rate']}%\n";
echo "Execution Time: {$report['execution_time']} seconds\n";

if (!empty($report['failed_tests'])) {
    echo "\n�?Failed Tests:\n";
    foreach ($report['failed_tests'] as $suite => $error) {
        echo "{$suite}: {$error}\n";
    }
}

