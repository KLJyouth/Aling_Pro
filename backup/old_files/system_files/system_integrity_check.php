<?php
/**
 * 系统完整性检查
 * 检查 AlingAI Pro 5.0 系统的关键组件是否正常工作
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

echo "=== AlingAI Pro 5.0 系统完整性检查 ===\n\n";

try {
    // 1. 检查核心类是否可以加载
    echo "1. 检查核心类加载...\n";
    
    $coreClasses = [
        'AlingAi\\Utils\\ApiResponse',
        'AlingAi\\AI\\AgentScheduler\\IntelligentAgentScheduler',
        'AlingAi\\Controllers\\AI\\AgentSchedulerController',
        'AlingAi\\Services\\DatabaseServiceInterface',
        'AlingAi\\Services\\CacheService',
        'AlingAi\\Microservices\\ServiceRegistry\\ServiceRegistryCenter'
    ];
    
    foreach ($coreClasses as $class) {
        if (class_exists($class) || interface_exists($class)) {
            echo "  ✅ {$class}\n";
        } else {
            echo "  ❌ {$class} - 不存在\n";
        }
    }
    
    // 2. 检查 ApiResponse 类的方法
    echo "\n2. 检查 ApiResponse 方法...\n";
    if (class_exists('AlingAi\\Utils\\ApiResponse')) {
        $reflection = new ReflectionClass('AlingAi\\Utils\\ApiResponse');
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC);
        
        $expectedMethods = ['success', 'error', 'paginated', 'validationError'];
        foreach ($expectedMethods as $method) {
            $hasMethod = $reflection->hasMethod($method);
            echo "  " . ($hasMethod ? "✅" : "❌") . " {$method}()\n";
        }
    }
    
    // 3. 检查数据库接口方法
    echo "\n3. 检查 DatabaseServiceInterface 方法...\n";
    if (interface_exists('AlingAi\\Services\\DatabaseServiceInterface')) {
        $reflection = new ReflectionClass('AlingAi\\Services\\DatabaseServiceInterface');
        $methods = $reflection->getMethods();
        
        $expectedMethods = ['query', 'execute', 'insert', 'find', 'update', 'delete'];
        foreach ($expectedMethods as $method) {
            $hasMethod = $reflection->hasMethod($method);
            echo "  " . ($hasMethod ? "✅" : "❌") . " {$method}()\n";
        }
        
        // 检查是否有 queryOne 方法（不应该有）
        $hasQueryOne = $reflection->hasMethod('queryOne');
        echo "  " . ($hasQueryOne ? "❌" : "✅") . " 不应该有 queryOne() 方法\n";
    }
    
    // 4. 检查智能体调度器方法
    echo "\n4. 检查 IntelligentAgentScheduler 方法...\n";
    if (class_exists('AlingAi\\AI\\AgentScheduler\\IntelligentAgentScheduler')) {
        $reflection = new ReflectionClass('AlingAi\\AI\\AgentScheduler\\IntelligentAgentScheduler');
        
        $expectedMethods = [
            'getSchedulerStatus',
            'assignTask', 
            'getTaskStatus',
            'getAvailableAgents',
            'generatePerformanceReport',
            'optimizeSchedulingParameters',
            'getSchedulingStatistics',
            'updateAgentStatus',
            'cancelTask',
            'getAvailableStrategies'
        ];
        
        foreach ($expectedMethods as $method) {
            $hasMethod = $reflection->hasMethod($method);
            echo "  " . ($hasMethod ? "✅" : "❌") . " {$method}()\n";
        }
    }
    
    // 5. 创建简单测试实例
    echo "\n5. 测试组件实例化...\n";
    
    try {
        // 创建模拟数据库服务
        $mockDB = new class implements AlingAi\Services\DatabaseServiceInterface {
            public function getConnection() { return null; }
            public function query(string $sql, array $params = []): array { return [['count' => 0]]; }
            public function execute(string $sql, array $params = []): bool { return true; }
            public function insert(string $table, array $data): bool { return true; }
            public function find(string $table, $id): ?array { return null; }
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
        echo "  ✅ 模拟数据库服务创建成功\n";
          // 创建服务注册中心
        $mockLogger = new class implements Psr\Log\LoggerInterface {
            public function emergency(\Stringable|string $message, array $context = []): void {}
            public function alert(\Stringable|string $message, array $context = []): void {}
            public function critical(\Stringable|string $message, array $context = []): void {}
            public function error(\Stringable|string $message, array $context = []): void {}
            public function warning(\Stringable|string $message, array $context = []): void {}
            public function notice(\Stringable|string $message, array $context = []): void {}
            public function info(\Stringable|string $message, array $context = []): void {}
            public function debug(\Stringable|string $message, array $context = []): void {}
            public function log($level, \Stringable|string $message, array $context = []): void {}
        };
        echo "  ✅ 模拟日志服务创建成功\n";
        
        $cacheService = new AlingAi\Services\CacheService($mockLogger);
        echo "  ✅ 缓存服务创建成功\n";
        
        $serviceRegistry = new AlingAi\Microservices\ServiceRegistry\ServiceRegistryCenter(
            $mockDB, $cacheService, $mockLogger
        );
        echo "  ✅ 服务注册中心创建成功\n";
        
        $scheduler = new AlingAi\AI\AgentScheduler\IntelligentAgentScheduler(
            $mockDB, $serviceRegistry, $mockLogger
        );
        echo "  ✅ 智能体调度器创建成功\n";
        
        // 测试调度器方法
        $status = $scheduler->getSchedulerStatus();
        echo "  ✅ 调度器状态获取成功: " . json_encode($status) . "\n";
        
        $agents = $scheduler->getAvailableAgents();
        echo "  ✅ 获取可用代理成功，数量: " . count($agents) . "\n";
        
    } catch (Exception $e) {
        echo "  ❌ 组件实例化失败: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== 检查完成 ===\n";
    
} catch (Exception $e) {
    echo "❌ 检查过程中发生错误: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
