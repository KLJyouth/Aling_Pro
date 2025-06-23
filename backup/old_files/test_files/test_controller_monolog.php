<?php
/**
 * 完整的UnifiedAdminController测试 - 使用Monolog Logger
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Controllers\UnifiedAdminController;
use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use AlingAi\Services\EmailService;
use AlingAi\Services\EnhancedUserManagementService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;

echo "=== 开始完整API测试 - 使用Monolog Logger ===\n";

try {
    echo "✓ Autoloader 加载成功\n";
    
    // 创建Monolog Logger
    $monologLogger = new Logger('test');
    $monologLogger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
    echo "✓ Monolog Logger 创建成功\n";
    
    // 创建PSR-3 Logger接口（使用Monolog）
    $psrLogger = $monologLogger;
    echo "✓ PSR Logger 创建成功\n";
    
    // 创建CacheService实例（需要Monolog Logger）
    $cacheService = new CacheService($monologLogger);
    echo "✓ CacheService 创建成功\n";
    
    // 创建EmailService实例（需要PSR Logger）
    $emailService = new EmailService($psrLogger);
    echo "✓ EmailService 创建成功\n";
    
    // 创建Mock数据库服务
    $databaseService = new class implements DatabaseServiceInterface {
        public function getConnection(): ?\PDO {
            return null;
        }
        
        public function query(string $sql, array $params = []): array {
            return [['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')]];
        }
        
        public function execute(string $sql, array $params = []): bool {
            return true;
        }
        
        public function insert(string $table, array $data): bool {
            return true;
        }
          public function find(string $table, $id): ?array {
            return ['id' => $id, 'data' => 'test'];
        }
        
        public function findAll(string $table, array $conditions = []): array {
            return [['id' => 1, 'data' => 'test1'], ['id' => 2, 'data' => 'test2']];
        }
        
        public function select(string $table, array $conditions = [], string $orderBy = '', int $limit = 0): array {
            return [['id' => 1, 'name' => 'test']];
        }
        
        public function update(string $table, array $data, array $conditions): bool {
            return true;
        }
        
        public function delete(string $table, array $conditions): bool {
            return true;
        }
        
        public function count(string $table, array $conditions = []): int {
            return 10;
        }
        
        public function selectOne(string $table, array $conditions = []): ?array {
            return ['id' => 1, 'data' => 'test'];
        }
        
        public function lastInsertId(): ?string {
            return '123';
        }
        
        public function beginTransaction(): bool {
            return true;
        }
        
        public function commit(): bool {
            return true;
        }
        
        public function rollback(): bool {
            return true;
        }
    };
    echo "✓ Mock DatabaseService 创建成功\n";
      // 创建用户管理服务
    $userService = new EnhancedUserManagementService($databaseService, $cacheService, $emailService, $psrLogger);
    echo "✓ UserManagementService 创建成功\n";
    
    // 创建统一管理控制器
    $controller = new UnifiedAdminController(
        $databaseService,
        $cacheService,
        $emailService,
        $userService
    );
    echo "✓ UnifiedAdminController 创建成功\n";
    
    // 测试各个API方法
    $testMethods = [
        'getSystemStatus',
        'getSystemHealth', 
        'runHealthCheck',
        'runSystemDiagnostics',
        'getCurrentMetrics',
        'getMonitoringHistory',
        'runSecurityScan',
        'getTestingSystemStatus'
    ];
    
    foreach ($testMethods as $method) {
        try {
            // 创建模拟请求
            $request = new ServerRequest([], [], '', 'GET');
            
            // 调用方法
            $response = $controller->$method($request);
            
            if ($response->getStatusCode() === 200) {
                echo "✓ $method() - 成功\n";
            } else {
                echo "⚠ $method() - 状态码: " . $response->getStatusCode() . "\n";
            }
        } catch (Exception $e) {
            echo "❌ $method() - 错误: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== 测试完成 ===\n";
    echo "UnifiedAdminController 功能验证完成！\n";
    
} catch (Exception $e) {
    echo "❌ PHP错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
