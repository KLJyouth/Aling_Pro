<?php
/**
 * 修复版API测试服务器
 * 解决DatabaseServiceInterface实现问题
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Controllers\UnifiedAdminController;
use AlingAi\Services\{DatabaseServiceInterface, CacheService, EmailService, EnhancedUserManagementService};
use AlingAi\Utils\Logger;
use Psr\Http\Message\ServerRequestInterface;

// 设置CORS头
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json');

// 处理OPTIONS请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 创建完整的DatabaseService模拟实现
class MockDatabaseService implements DatabaseServiceInterface {
    public function getConnection() { return null; }
    public function query(string $sql, array $params = []): array { 
        return [
            ['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'],
            ['id' => 2, 'name' => 'Admin User', 'email' => 'admin@example.com']
        ]; 
    }
    public function execute(string $sql, array $params = []): bool { return true; }
    public function insert(string $table, array $data): bool { return true; }
    public function find(string $table, $id): ?array { 
        return ['id' => $id, 'name' => 'Mock Data', 'created_at' => date('Y-m-d H:i:s')]; 
    }
    public function findAll(string $table, array $conditions = []): array { 
        return [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2']
        ]; 
    }
    public function select(string $table, array $conditions = [], array $options = []): array { 
        return $this->findAll($table, $conditions); 
    }
    public function update(string $table, $id, array $data): bool { return true; }
    public function delete(string $table, $id): bool { return true; }
    public function count(string $table, array $conditions = []): int { return 10; }
    public function selectOne(string $table, array $conditions): ?array { 
        return ['id' => 1, 'name' => 'Single Record']; 
    }
    public function lastInsertId() { return '1'; }
    public function beginTransaction(): bool { return true; }
    public function commit(): bool { return true; }
    public function rollback(): bool { return true; }
}

// 创建模拟CacheService
class MockCacheService {
    public function get($key) { return null; }
    public function set($key, $value, $ttl = null) { return true; }
    public function delete($key) { return true; }
    public function clear() { return true; }
    public function has($key) { return false; }
}

// 创建模拟EmailService
class MockEmailService {
    public function send($to, $subject, $body) { return true; }
}

// 创建模拟UserManagementService
class MockUserManagementService {
    public function __construct($db = null, $cache = null, $email = null, $logger = null) {}
    public function getUserStats() { 
        return [
            'total_users' => 1500,
            'active_users' => 1200,
            'new_users_today' => 25
        ];
    }
}

// 简单的ServerRequest实现
class SimpleServerRequest implements ServerRequestInterface {
    private $method;
    private $uri;
    private $headers;
    private $body;
    private $attributes = [];
    
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->headers = getallheaders() ?: [];
        $this->body = file_get_contents('php://input');
        
        // 模拟管理员用户
        $this->attributes['user'] = (object)[
            'id' => 1,
            'role' => 'admin',
            'is_admin' => true,
            'name' => 'Test Admin'
        ];
    }
    
    public function getAttribute($name, $default = null) {
        return $this->attributes[$name] ?? $default;
    }
    
    public function withAttribute($name, $value) {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }
    
    // PSR-7 必需方法（简化实现）
    public function getProtocolVersion() { return '1.1'; }
    public function withProtocolVersion($version) { return $this; }
    public function getHeaders() { return $this->headers; }
    public function hasHeader($name) { return isset($this->headers[$name]); }
    public function getHeader($name) { return $this->headers[$name] ?? []; }
    public function getHeaderLine($name) { return implode(', ', $this->getHeader($name)); }
    public function withHeader($name, $value) { return $this; }
    public function withAddedHeader($name, $value) { return $this; }
    public function withoutHeader($name) { return $this; }
    public function getBody() { return null; }
    public function withBody($body) { return $this; }
    public function getRequestTarget() { return $this->uri; }
    public function withRequestTarget($requestTarget) { return $this; }
    public function getMethod() { return $this->method; }
    public function withMethod($method) { return $this; }
    public function getUri() { return null; }
    public function withUri($uri, $preserveHost = false) { return $this; }
    public function getServerParams() { return $_SERVER; }
    public function getCookieParams() { return $_COOKIE; }
    public function withCookieParams(array $cookies) { return $this; }
    public function getQueryParams() { return $_GET; }
    public function withQueryParams(array $query) { return $this; }
    public function getUploadedFiles() { return []; }
    public function withUploadedFiles(array $uploadedFiles) { return $this; }
    public function getParsedBody() { return json_decode($this->body, true); }
    public function withParsedBody($data) { return $this; }
    public function getAttributes() { return $this->attributes; }
    public function withoutAttribute($name) { return $this; }
}

// 路由处理函数
function handleApiRequest($path, $method) {
    try {
        // 创建服务实例
        $mockDB = new MockDatabaseService();
        $mockCache = new MockCacheService();
        $mockEmail = new MockEmailService();
        $mockUserManagement = new MockUserManagementService();
        
        // 创建控制器
        $controller = new UnifiedAdminController($mockDB, $mockCache, $mockEmail, $mockUserManagement);
        $request = new SimpleServerRequest();
        
        // 处理路由
        switch ($path) {
            case '/api/unified-admin/dashboard':
                if ($method === 'GET') {
                    return $controller->dashboard($request);
                }
                break;
                
            case '/api/unified-admin/diagnostics':
                if ($method === 'GET') {
                    return $controller->getSystemDiagnostics($request);
                }
                break;
                
            case '/api/unified-admin/tests/comprehensive':
                if ($method === 'POST') {
                    return $controller->runComprehensiveTests($request);
                }
                break;
                
            case '/api/unified-admin/health':
                if ($method === 'GET') {
                    return $controller->getSystemHealth($request);
                }
                break;
                
            case '/api/unified-admin/health/check':
                if ($method === 'POST') {
                    return $controller->runHealthCheck($request);
                }
                break;
                
            case '/api/unified-admin/monitoring/current':
                if ($method === 'GET') {
                    return $controller->getCurrentMetrics($request);
                }
                break;
                
            case '/api/unified-admin/monitoring/history':
                if ($method === 'GET') {
                    return $controller->getMonitoringHistory($request);
                }
                break;
                
            case '/api/unified-admin/security/scan':
                if ($method === 'POST') {
                    return $controller->runSecurityScan($request);
                }
                break;
                
            case '/api/unified-admin/testing/status':
                if ($method === 'GET') {
                    return $controller->getTestingSystemStatus($request);
                }
                break;
                
            case '/api/unified-admin/diagnostics/run':
                if ($method === 'POST') {
                    return $controller->runSystemDiagnostics($request);
                }
                break;
                
            default:
                return [
                    'error' => 'API端点未找到',
                    'path' => $path,
                    'method' => $method,
                    'available_endpoints' => [
                        'GET /api/unified-admin/dashboard',
                        'GET /api/unified-admin/diagnostics',
                        'POST /api/unified-admin/tests/comprehensive',
                        'GET /api/unified-admin/health',
                        'POST /api/unified-admin/health/check',
                        'GET /api/unified-admin/monitoring/current',
                        'GET /api/unified-admin/monitoring/history',
                        'POST /api/unified-admin/security/scan',
                        'GET /api/unified-admin/testing/status',
                        'POST /api/unified-admin/diagnostics/run'
                    ],
                    'status_code' => 404
                ];
        }
        
        return [
            'error' => '方法不支持',
            'path' => $path,
            'method' => $method,
            'status_code' => 405
        ];
        
    } catch (Exception $e) {
        return [
            'error' => 'API服务器内部错误',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'status_code' => 500
        ];
    } catch (Error $e) {
        return [
            'error' => 'PHP致命错误',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'status_code' => 500
        ];
    }
}

// 主处理逻辑
try {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    
    $result = handleApiRequest($path, $method);
    
    // 设置HTTP状态码
    if (isset($result['status_code'])) {
        http_response_code($result['status_code']);
    } else {
        http_response_code(200);
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => '服务器错误',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'PHP致命错误',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
