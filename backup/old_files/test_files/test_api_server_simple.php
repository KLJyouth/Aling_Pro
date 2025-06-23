<?php
/**
 * 简化的API测试服务器 - 避免复杂的服务依赖
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Controllers\UnifiedAdminController;
use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use AlingAi\Services\EmailService;
use AlingAi\Services\EnhancedUserManagementService;
use AlingAi\Utils\Logger;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\ServerRequest;

// 启动简单的HTTP服务器
$port = 8082;
echo "启动简化API测试服务器于端口 $port...\n";

// 创建依赖服务
$monologLogger = new MonologLogger('api');
$monologLogger->pushHandler(new StreamHandler('php://stdout', MonologLogger::WARNING));

$alingaiLogger = new Logger();
$cacheService = new CacheService($monologLogger);
$emailService = new EmailService($monologLogger);

// Mock数据库服务
$databaseService = new class implements DatabaseServiceInterface {
    public function getConnection(): ?\PDO { return null; }
    public function query(string $sql, array $params = []): array { 
        return [['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')]]; 
    }
    public function execute(string $sql, array $params = []): bool { return true; }
    public function insert(string $table, array $data): bool { return true; }
    public function find(string $table, $id): ?array { 
        return ['id' => $id, 'data' => 'mock_data']; 
    }
    public function findAll(string $table, array $conditions = []): array { 
        return [['id' => 1, 'data' => 'test1'], ['id' => 2, 'data' => 'test2']]; 
    }
    public function select(string $table, array $conditions = [], array $options = []): array { 
        return [['id' => 1, 'name' => 'test']]; 
    }
    public function update(string $table, $id, array $data): bool { return true; }
    public function delete(string $table, $id): bool { return true; }
    public function count(string $table, array $conditions = []): int { return 10; }
    public function selectOne(string $table, array $conditions): ?array { 
        return ['id' => 1, 'data' => 'test']; 
    }
    public function lastInsertId(): ?string { return '123'; }
    public function beginTransaction(): bool { return true; }
    public function commit(): bool { return true; }
    public function rollback(): bool { return true; }
};

// 创建控制器
$userService = new EnhancedUserManagementService($databaseService, $cacheService, $emailService, $alingaiLogger);
$controller = new UnifiedAdminController($databaseService, $cacheService, $emailService, $userService);

// 定义路由处理
function handleRequest($path, $controller) {
    // 创建带管理员权限的请求
    $adminUser = (object)[
        'id' => 1,
        'role' => 'admin',
        'is_admin' => true,
        'name' => 'Test Admin'
    ];
    
    $request = new ServerRequest([], [], '', 'GET');
    $request = $request->withAttribute('user', $adminUser);
    
    // 路由到对应的方法
    $routes = [
        '/api/unified-admin/dashboard' => 'dashboard',
        '/api/unified-admin/system/health' => 'getSystemHealth',
        '/api/unified-admin/system/health-check' => 'runHealthCheck',
        '/api/unified-admin/system/diagnostics' => 'runSystemDiagnostics',
        '/api/unified-admin/monitoring/current' => 'getCurrentMetrics',
        '/api/unified-admin/monitoring/history' => 'getMonitoringHistory',
        '/api/unified-admin/security/scan' => 'runSecurityScan',
        '/api/unified-admin/testing/status' => 'getTestingSystemStatus',
        '/api/unified-admin/system/diagnostics-detail' => 'getSystemDiagnostics',
        '/api/unified-admin/testing/comprehensive' => 'runComprehensiveTests'
    ];
    
    if (isset($routes[$path])) {
        $method = $routes[$path];
        try {
            $startTime = microtime(true);
            
            // 对于复杂的方法，返回简化的模拟数据
            if (in_array($method, ['dashboard', 'getSystemDiagnostics', 'runComprehensiveTests'])) {
                $result = [
                    'success' => true,
                    'method' => $method,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data' => [
                        'status' => 'ok',
                        'message' => '模拟数据 - ' . $method,
                        'mock' => true
                    ]
                ];
            } else {
                // 调用实际方法
                $result = $controller->$method($request);
            }
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            if (!isset($result['execution_time'])) {
                $result['execution_time'] = $executionTime . 'ms';
            }
            
            return $result;
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'method' => $method,
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ];
        }
    }
    
    return ['error' => 'Route not found', 'path' => $path];
}

// 启动HTTP服务器
$address = "127.0.0.1:$port";
$context = stream_context_create([
    'http' => [
        'header' => "Content-Type: application/json\r\n"
    ]
]);

echo "🚀 简化API测试服务器已启动！\n";
echo "访问地址: http://$address\n";
echo "可用端点:\n";
echo "  - GET /api/unified-admin/dashboard\n";
echo "  - GET /api/unified-admin/system/health\n";
echo "  - GET /api/unified-admin/system/health-check\n";
echo "  - GET /api/unified-admin/system/diagnostics\n";
echo "  - GET /api/unified-admin/monitoring/current\n";
echo "  - GET /api/unified-admin/monitoring/history\n";
echo "  - GET /api/unified-admin/security/scan\n";
echo "  - GET /api/unified-admin/testing/status\n";
echo "  - GET /api/unified-admin/system/diagnostics-detail\n";
echo "  - GET /api/unified-admin/testing/comprehensive\n";
echo "\n按 Ctrl+C 停止服务器\n\n";

// 简单的HTTP服务器循环
while (true) {
    $socket = stream_socket_server("tcp://$address", $errno, $errstr);
    if (!$socket) {
        die("无法创建socket: $errstr ($errno)\n");
    }
    
    while ($client = stream_socket_accept($socket)) {
        $request = fread($client, 4096);
        
        // 解析请求
        $lines = explode("\n", $request);
        $requestLine = $lines[0];
        $parts = explode(' ', $requestLine);
        $method = $parts[0] ?? '';
        $path = $parts[1] ?? '';
        
        echo "[" . date('Y-m-d H:i:s') . "] $method $path\n";
        
        // 处理请求
        $response = handleRequest($path, $controller);
        $jsonResponse = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // 发送响应
        $httpResponse = "HTTP/1.1 200 OK\r\n";
        $httpResponse .= "Content-Type: application/json; charset=utf-8\r\n";
        $httpResponse .= "Access-Control-Allow-Origin: *\r\n";
        $httpResponse .= "Content-Length: " . strlen($jsonResponse) . "\r\n";
        $httpResponse .= "Connection: close\r\n\r\n";
        $httpResponse .= $jsonResponse;
        
        fwrite($client, $httpResponse);
        fclose($client);
    }
    
    fclose($socket);
    usleep(100000); // 0.1秒延迟
}
