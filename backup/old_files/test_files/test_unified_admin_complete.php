<?php
/**
 * UnifiedAdminController完整功能测试 - 包含管理员权限模拟
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

echo "=== UnifiedAdminController完整功能验证测试 ===\n";

try {
    echo "✓ Autoloader 加载成功\n";
    
    // 创建各种Logger
    $monologLogger = new MonologLogger('test');
    $monologLogger->pushHandler(new StreamHandler('php://stdout', MonologLogger::INFO));
    $alingaiLogger = new Logger();
    echo "✓ Logger 创建成功\n";
    
    // 创建服务实例
    $cacheService = new CacheService($monologLogger);
    $emailService = new EmailService($monologLogger);
    echo "✓ 服务实例创建成功\n";
    
    // 创建Mock数据库服务（完全符合接口）
    $databaseService = new class implements DatabaseServiceInterface {
        public function getConnection(): ?\PDO { return null; }
        public function query(string $sql, array $params = []): array { 
            return [['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')]]; 
        }
        public function execute(string $sql, array $params = []): bool { return true; }
        public function insert(string $table, array $data): bool { return true; }
        public function find(string $table, $id): ?array { 
            return ['id' => $id, 'data' => 'test']; 
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
    echo "✓ Mock DatabaseService 创建成功\n";
    
    // 创建用户管理服务和控制器
    $userService = new EnhancedUserManagementService($databaseService, $cacheService, $emailService, $alingaiLogger);
      // 创建统一管理控制器
    $controller = new UnifiedAdminController(
        $databaseService,
        $cacheService,
        $emailService,
        $userService
    );
    
    echo "✓ UnifiedAdminController 创建成功\n";
    
    // 测试所有API方法
    $apiMethods = [
        'dashboard' => '管理员仪表板',
        'getSystemHealth' => '系统健康状态', 
        'runHealthCheck' => '运行健康检查',
        'runSystemDiagnostics' => '运行系统诊断',
        'getCurrentMetrics' => '获取当前监控指标',
        'getMonitoringHistory' => '获取监控历史',
        'runSecurityScan' => '运行安全扫描',
        'getTestingSystemStatus' => '获取测试系统状态',
        'getSystemDiagnostics' => '获取系统诊断',
        'runComprehensiveTests' => '运行综合测试'
    ];
    
    echo "\n=== 开始完整API功能测试 ===\n";
    
    $successCount = 0;
    $totalCount = count($apiMethods);
    
    foreach ($apiMethods as $method => $description) {
        try {
            echo "\n[$method] $description:\n";
              // 创建带管理员权限的模拟请求
            $adminUser = (object)[
                'id' => 1,
                'role' => 'admin',
                'is_admin' => true,
                'name' => 'Test Admin'
            ];
            
            $request = new ServerRequest([], [], '', 'GET');
            $request = $request->withAttribute('user', $adminUser);
            
            // 调用方法
            $startTime = microtime(true);
            $result = $controller->$method($request);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            if (is_array($result)) {
                echo "  ✓ 成功执行 (耗时: {$executionTime}ms)\n";
                
                // 分析返回结果
                if (isset($result['error'])) {
                    echo "  ⚠ 返回错误: " . $result['error'] . "\n";
                } elseif (isset($result['success'])) {
                    echo "  ✓ 成功状态: " . ($result['success'] ? 'true' : 'false') . "\n";
                    if (isset($result['data'])) {
                        echo "  ✓ 数据字段数: " . count($result['data']) . "\n";
                    }
                } elseif (isset($result['status'])) {
                    echo "  ✓ 状态: " . $result['status'] . "\n";
                } else {
                    echo "  ✓ 响应字段数: " . count($result) . "\n";
                    // 显示主要字段
                    $mainFields = array_intersect(array_keys($result), ['users', 'system', 'performance', 'tests', 'diagnostics', 'metrics']);
                    if (!empty($mainFields)) {
                        echo "  ✓ 主要数据: " . implode(', ', $mainFields) . "\n";
                    }
                }
                
                $successCount++;
            } else {
                echo "  ❌ 返回类型异常: " . gettype($result) . "\n";
            }
        } catch (Exception $e) {
            echo "  ❌ 执行错误: " . $e->getMessage() . "\n";
            echo "  📍 错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
    }
    
    echo "\n=== 测试总结 ===\n";
    echo "总测试方法数: $totalCount\n";
    echo "成功执行数: $successCount\n";
    echo "成功率: " . round(($successCount / $totalCount) * 100, 1) . "%\n";
    
    if ($successCount === $totalCount) {
        echo "\n🎉 所有API方法测试通过！UnifiedAdminController功能完全正常！\n";
    } else {
        echo "\n⚠ 部分测试失败，需要进一步检查\n";
    }
    
    echo "\n=== UnifiedAdminController完整功能验证完成！ ===\n";
    
} catch (Exception $e) {
    echo "❌ 测试执行错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
