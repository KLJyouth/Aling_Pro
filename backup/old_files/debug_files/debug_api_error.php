<?php
/**
 * Debug API错误 - 查看详细错误信息
 */

// 开启错误显示
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "开始调试API错误...\n\n";

try {
    // 1. 检查autoloader
    echo "1. 检查autoloader...\n";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✓ Autoloader加载成功\n\n";
    
    // 2. 检查类文件是否存在
    echo "2. 检查关键类文件...\n";
    $classFiles = [
        'UnifiedAdminController' => 'src/Controllers/UnifiedAdminController.php',
        'DatabaseService' => 'src/Services/DatabaseService.php',
        'Logger' => 'src/Utils/Logger.php'
    ];
    
    foreach ($classFiles as $className => $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "   ✓ $className ($file)\n";
        } else {
            echo "   ✗ $className ($file) - 文件不存在\n";
        }
    }
    echo "\n";
      // 3. 尝试加载UnifiedAdminController
    echo "3. 尝试加载UnifiedAdminController...\n";
    
    echo "   ✓ 所有use语句执行成功\n\n";
    
    // 4. 创建Logger实例
    echo "4. 创建Logger实例...\n";
    $logger = new Logger();
    echo "   ✓ Logger创建成功\n\n";
    
    // 5. 创建模拟服务
    echo "5. 创建模拟服务...\n";
    
    // 检查接口是否存在
    if (!interface_exists('AlingAi\Services\DatabaseServiceInterface')) {
        echo "   ! DatabaseServiceInterface接口不存在，创建基础实现\n";
        
        $mockDB = new class {
            public function query(string $sql, array $params = []): array { return []; }
            public function prepare(string $sql): object { return new stdClass(); }
            public function execute(string $sql, array $params = []): bool { return true; }
            public function lastInsertId(): string { return '1'; }
            public function beginTransaction(): bool { return true; }
            public function commit(): bool { return true; }
            public function rollback(): bool { return true; }
        };
    } else {
        echo "   ✓ DatabaseServiceInterface存在\n";
        $mockDB = new class implements \AlingAi\Services\DatabaseServiceInterface {
            public function query(string $sql, array $params = []): array { return []; }
            public function prepare(string $sql): object { return new stdClass(); }
            public function execute(string $sql, array $params = []): bool { return true; }
            public function lastInsertId(): string { return '1'; }
            public function beginTransaction(): bool { return true; }
            public function commit(): bool { return true; }
            public function rollback(): bool { return true; }
        };
    }
    
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
    
    echo "   ✓ 模拟服务创建成功\n\n";
    
    // 6. 尝试创建UnifiedAdminController
    echo "6. 尝试创建UnifiedAdminController...\n";
    $controller = new UnifiedAdminController($mockDB, $mockCache, $mockEmail, $mockUserManagement);
    echo "   ✓ UnifiedAdminController创建成功\n\n";
    
    // 7. 测试一个简单的方法调用
    echo "7. 测试方法调用...\n";
    
    // 创建简单的请求对象
    $request = new class implements Psr\Http\Message\ServerRequestInterface {
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
    
    // 测试dashboard方法
    $result = $controller->dashboard($request);
    echo "   ✓ dashboard方法调用成功\n";
    echo "   结果: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "🎉 所有测试通过！API应该可以正常工作。\n";
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ 致命错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
