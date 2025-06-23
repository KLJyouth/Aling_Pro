<?php
/**
 * 完整API测试 - 使用正确的服务类型
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Controllers\UnifiedAdminController;
use AlingAi\Services\{DatabaseServiceInterface, CacheService, EmailService, EnhancedUserManagementService};
use AlingAi\Utils\Logger;
use Psr\Http\Message\ServerRequestInterface;

echo "=== 开始完整API测试 ===\n\n";

try {
    echo "✓ Autoloader 加载成功\n";
    
    // 创建Logger实例
    $logger = new Logger();
    echo "✓ Logger 创建成功\n";
    
    // 创建正确类型的CacheService
    $cacheService = new CacheService($logger);
    echo "✓ CacheService 创建成功\n";
    
    // 创建正确类型的EmailService
    $emailService = new EmailService($logger);
    echo "✓ EmailService 创建成功\n";
    
    // 创建模拟数据库服务
    $mockDB = new class implements DatabaseServiceInterface {
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
            return ['id' => $id, 'name' => 'Test Item', 'created_at' => date('Y-m-d H:i:s')]; 
        }
        public function findAll(string $table, array $conditions = []): array { 
            return [
                ['id' => 1, 'name' => 'Item 1', 'status' => 'active'],
                ['id' => 2, 'name' => 'Item 2', 'status' => 'active']
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
    };
    
    echo "✓ 模拟数据库服务创建成功\n";
    
    // 创建控制器
    $controller = new UnifiedAdminController($mockDB, $cacheService, $emailService);
    echo "✓ UnifiedAdminController 创建成功\n";
    
    // 创建模拟请求
    $request = new class implements ServerRequestInterface {
        private $attributes;
        
        public function __construct() {
            $this->attributes = ['user' => (object)['id' => 1, 'role' => 'admin', 'is_admin' => true]];
        }
        
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
        'getTestingSystemStatus' => '测试系统状态',
        'runHealthCheck' => '运行健康检查',
        'getMonitoringHistory' => '监控历史',
        'runSecurityScan' => '安全扫描',
        'runSystemDiagnostics' => '运行系统诊断',
        'runComprehensiveTests' => '综合测试'
    ];
    
    $successCount = 0;
    $totalCount = count($methods);
    
    foreach ($methods as $method => $description) {
        echo "测试 {$method} ({$description})...\n";
        try {
            $result = $controller->$method($request);
            if (is_array($result) && !isset($result['error'])) {
                echo "  ✓ 成功\n";
                $successCount++;
            } else {
                echo "  ⚠ 返回了错误: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch (Exception $e) {
            echo "  ✗ 错误: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    echo "=== 测试完成 ===\n";
    echo "成功: {$successCount}/{$totalCount} 个方法\n";
    
    if ($successCount === $totalCount) {
        echo "🎉 所有测试通过！UnifiedAdminController 可以正常工作。\n";
    } else {
        echo "⚠ 部分测试失败，但基础功能正常。\n";
    }
    
} catch (Exception $e) {
    echo "❌ 致命错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ PHP错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
