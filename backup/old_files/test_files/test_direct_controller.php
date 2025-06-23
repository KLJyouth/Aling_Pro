<?php
/**
 * 简单API测试 - 直接测试UnifiedAdminController
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Controllers\UnifiedAdminController;
use AlingAi\Services\{DatabaseServiceInterface, CacheService, EmailService, EnhancedUserManagementService};
use AlingAi\Utils\Logger;
use Psr\Http\Message\ServerRequestInterface;

echo "=== 开始API测试 ===\n\n";

try {
    // 检查autoloader
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        throw new Exception('Autoloader not found');
    }
    
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ Autoloader 加载成功\n";
    
    // 检查UnifiedAdminController是否存在
    if (!class_exists('AlingAi\Controllers\UnifiedAdminController')) {
        throw new Exception('UnifiedAdminController class not found');
    }
    echo "✓ UnifiedAdminController 类存在\n";
    
    // 检查接口和服务类
    if (!interface_exists('AlingAi\Services\DatabaseServiceInterface')) {
        throw new Exception('DatabaseServiceInterface interface not found');    }
    echo "✓ DatabaseServiceInterface 接口存在\n";
    
    // 导入必需的类
    use AlingAi\Controllers\UnifiedAdminController;
    use AlingAi\Services\{DatabaseServiceInterface, CacheService, EmailService, EnhancedUserManagementService};
    use AlingAi\Utils\Logger;
    use Psr\Http\Message\ServerRequestInterface;
    
    // 创建模拟服务
    $mockDB = new class implements DatabaseServiceInterface {
        public function getConnection() { return null; }
        public function query(string $sql, array $params = []): array { return []; }
        public function execute(string $sql, array $params = []): bool { return true; }
        public function insert(string $table, array $data): bool { return true; }
        public function find(string $table, $id): ?array { return ['id' => $id]; }
        public function findAll(string $table, array $conditions = []): array { return []; }
        public function select(string $table, array $conditions = [], array $options = []): array { return []; }
        public function update(string $table, $id, array $data): bool { return true; }
        public function delete(string $table, $id): bool { return true; }
        public function count(string $table, array $conditions = []): int { return 0; }
        public function selectOne(string $table, array $conditions): ?array { return null; }
        public function lastInsertId() { return '1'; }
        public function beginTransaction(): bool { return true; }
        public function commit(): bool { return true; }
        public function rollback(): bool { return true; }
    };
    
    $mockCache = new class {
        public function get($key) { return null; }
        public function set($key, $value, $ttl = null) { return true; }
        public function delete($key) { return true; }
        public function clear() { return true; }
        public function has($key) { return false; }
    };
    
    $mockEmail = new class {
        public function send($to, $subject, $body) { return true; }
    };
    
    $mockUserManagement = new class {
        public function __construct($db = null, $cache = null, $email = null, $logger = null) {}
    };
    
    echo "✓ 模拟服务创建成功\n";
    
    // 创建控制器
    $controller = new UnifiedAdminController($mockDB, $mockCache, $mockEmail, $mockUserManagement);
    echo "✓ UnifiedAdminController 创建成功\n";
    
    // 创建模拟请求
    $request = new class implements ServerRequestInterface {
        private $attributes = ['user' => (object)['id' => 1, 'role' => 'admin', 'is_admin' => true]];
        
        public function getAttribute($name, $default = null) {
            return $this->attributes[$name] ?? $default;
        }
        public function withAttribute($name, $value) { return $this; }
        public function getProtocolVersion() { return '1.1'; }
        public function withProtocolVersion($v) { return $this; }
        public function getHeaders() { return []; }
        public function hasHeader($name) { return false; }
        public function getHeader($name) { return []; }
        public function getHeaderLine($name) { return ''; }
        public function withHeader($name, $value) { return $this; }
        public function withAddedHeader($name, $value) { return $this; }
        public function withoutHeader($name) { return $this; }
        public function getBody() { return null; }
        public function withBody($body) { return $this; }
        public function getRequestTarget() { return '/'; }
        public function withRequestTarget($target) { return $this; }
        public function getMethod() { return 'GET'; }
        public function withMethod($method) { return $this; }
        public function getUri() { return null; }
        public function withUri($uri, $preserveHost = false) { return $this; }
        public function getServerParams() { return []; }
        public function getCookieParams() { return []; }
        public function withCookieParams(array $cookies) { return $this; }
        public function getQueryParams() { return []; }
        public function withQueryParams(array $query) { return $this; }
        public function getUploadedFiles() { return []; }
        public function withUploadedFiles(array $files) { return $this; }
        public function getParsedBody() { return null; }
        public function withParsedBody($data) { return $this; }
        public function getAttributes() { return $this->attributes; }
        public function withoutAttribute($name) { return $this; }
    };
    
    echo "✓ 模拟请求创建成功\n\n";
    
    // 测试各个方法
    $methods = [
        'dashboard' => '仪表板',
        'getSystemDiagnostics' => '系统诊断',
        'getSystemHealth' => '系统健康',
        'getCurrentMetrics' => '当前指标',
        'getTestingSystemStatus' => '测试系统状态'
    ];
    
    foreach ($methods as $method => $description) {
        echo "测试 {$method} ({$description})...\n";
        try {
            $result = $controller->$method($request);
            echo "  ✓ 成功: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
        } catch (Exception $e) {
            echo "  ✗ 错误: " . $e->getMessage() . "\n";
            echo "     文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
        echo "\n";
    }
    
    echo "=== 测试完成 ===\n";
    
} catch (Exception $e) {
    echo "❌ 致命错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ PHP错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
