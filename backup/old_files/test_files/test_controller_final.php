<?php
/**
 * 最终的UnifiedAdminController测试 - 修正版
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
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;

echo "=== 开始最终完整API测试 ===\n";

try {
    echo "✓ Autoloader 加载成功\n";
    
    // 创建Monolog Logger（用于CacheService）
    $monologLogger = new MonologLogger('test');
    $monologLogger->pushHandler(new StreamHandler('php://stdout', MonologLogger::INFO));
    echo "✓ Monolog Logger 创建成功\n";
    
    // 创建AlingAi自定义Logger（用于UserManagementService）
    $alingaiLogger = new Logger();
    echo "✓ AlingAi Logger 创建成功\n";
    
    // 创建CacheService实例（需要Monolog Logger）
    $cacheService = new CacheService($monologLogger);
    echo "✓ CacheService 创建成功\n";
    
    // 创建EmailService实例（需要PSR Logger）
    $emailService = new EmailService($monologLogger);
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
          public function select(string $table, array $conditions = [], array $options = []): array {
            return [['id' => 1, 'name' => 'test']];
        }
          public function update(string $table, $id, array $data): bool {
            return true;
        }
          public function delete(string $table, $id): bool {
            return true;
        }
        
        public function count(string $table, array $conditions = []): int {
            return 10;
        }
          public function selectOne(string $table, array $conditions): ?array {
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
    
    // 创建用户管理服务（需要AlingAi Logger）
    $userService = new EnhancedUserManagementService($databaseService, $cacheService, $emailService, $alingaiLogger);
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
        'dashboard',
        'getSystemHealth', 
        'runHealthCheck',
        'runSystemDiagnostics',
        'getCurrentMetrics',
        'getMonitoringHistory',
        'runSecurityScan',
        'getTestingSystemStatus',
        'getSystemDiagnostics',
        'runComprehensiveTests'
    ];
    
    echo "\n=== 开始API方法测试 ===\n";
    
    foreach ($testMethods as $method) {
        try {
            // 创建模拟请求
            $request = new ServerRequest([], [], '', 'GET');
            
            // 调用方法
            $result = $controller->$method($request);
            
            if (is_array($result)) {
                echo "✓ $method() - 成功 (返回数组)\n";
                
                // 输出响应内容的简要信息
                if (isset($result['status'])) {
                    echo "  - 响应状态: " . $result['status'] . "\n";
                } elseif (isset($result['success'])) {
                    echo "  - 成功状态: " . ($result['success'] ? 'true' : 'false') . "\n";
                } elseif (isset($result['error'])) {
                    echo "  - 错误信息: " . $result['error'] . "\n";
                } else {
                    echo "  - 响应包含 " . count($result) . " 个字段\n";
                }
            } else {
                echo "⚠ $method() - 返回类型异常: " . gettype($result) . "\n";
            }
        } catch (Exception $e) {
            echo "❌ $method() - 错误: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== 测试统计 ===\n";
    echo "测试方法总数: " . count($testMethods) . "\n";
    echo "测试完成！\n";
    echo "\n=== UnifiedAdminController 功能验证完成！ ===\n";
    
} catch (Exception $e) {
    echo "❌ PHP错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
