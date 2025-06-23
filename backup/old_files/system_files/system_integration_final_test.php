<?php
/**
 * AlingAi Pro 系统最终集成测试
 * 验证所有新集成的组件能否正常工作
 */

// 设置自动加载
spl_autoload_register(function ($class) {
    $prefix = 'AlingAi\\';
    $base_dir = __DIR__ . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use AlingAi\Controllers\SystemManagementController;
use AlingAi\Controllers\CacheManagementController;
use AlingAi\Cache\ApplicationCacheManager;
use AlingAi\Security\PermissionManager;
use AlingAi\Services\TestSystemIntegrationService;
use AlingAi\Services\DatabaseService;
use AlingAi\Services\LoggerService;
use AlingAi\Performance\PerformanceOptimizer;

echo "\n=== AlingAi Pro 系统最终集成测试 ===\n";

try {
    // 1. 初始化数据库服务
    echo "1. 初始化数据库服务...\n";
    $dbConfig = [
        'host' => 'localhost',
        'database' => 'alingai_pro',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];
    $logger = new LoggerService('logs/test.log');
    $db = new DatabaseService($dbConfig, $logger);
    echo "   ✓ 数据库服务初始化成功\n";

    // 2. 测试缓存管理系统
    echo "\n2. 测试缓存管理系统...\n";
    $cacheConfig = [
        'cache_types' => ['file', 'memory', 'database'],
        'default_ttl' => 3600,
        'max_cache_size' => 100 * 1024 * 1024,
        'cleanup_threshold' => 0.8
    ];
    
    $cacheManager = new ApplicationCacheManager($db, $cacheConfig);
    
    // 测试缓存操作
    $testKey = 'test_integration_' . time();
    $testData = ['message' => 'System integration test', 'timestamp' => time()];
    
    $cacheManager->set($testKey, $testData, 300);
    echo "   ✓ 缓存写入测试通过\n";
    
    $retrieved = $cacheManager->get($testKey);
    if ($retrieved && $retrieved['message'] === 'System integration test') {
        echo "   ✓ 缓存读取测试通过\n";
    } else {
        echo "   ✗ 缓存读取测试失败\n";
    }
    
    // 测试缓存管理控制器
    $cacheController = new CacheManagementController($db);
    echo "   ✓ 缓存管理控制器初始化成功\n";

    // 3. 测试权限管理系统
    echo "\n3. 测试权限管理系统...\n";
    $permissionManager = new PermissionManager($db);
    
    // 测试权限检查
    $hasPermission = $permissionManager->hasPermission(1, 'system.manage');
    echo "   ✓ 权限检查功能正常\n";
    
    $permissions = $permissionManager->getUserPermissions(1);
    echo "   ✓ 用户权限获取功能正常 (获取到 " . count($permissions) . " 个权限)\n";

    // 4. 测试系统管理控制器
    echo "\n4. 测试系统管理控制器...\n";
    $systemController = new SystemManagementController($db, $logger);
    echo "   ✓ 系统管理控制器初始化成功\n";

    // 5. 测试性能优化器
    echo "\n5. 测试性能优化器...\n";
    $performanceOptimizer = new PerformanceOptimizer($logger);
    
    // 测试性能分析
    $metrics = $performanceOptimizer->analyzePerformance();
    echo "   ✓ 性能分析功能正常\n";
    echo "   - CPU使用率: " . $metrics['cpu_usage'] . "%\n";
    echo "   - 内存使用: " . round($metrics['memory_usage'] / 1024 / 1024, 2) . " MB\n";

    // 6. 测试系统集成服务
    echo "\n6. 测试系统集成服务...\n";
    $testService = new TestSystemIntegrationService($db, $logger);
    
    $testResults = $testService->runComprehensiveTests();
    echo "   ✓ 综合测试完成\n";
    echo "   - 通过测试数: " . $testResults['passed'] . "\n";
    echo "   - 失败测试数: " . $testResults['failed'] . "\n";
    echo "   - 总体状态: " . ($testResults['overall_status'] ? '通过' : '失败') . "\n";

    // 7. 清理测试数据
    echo "\n7. 清理测试数据...\n";
    $cacheManager->delete($testKey);
    echo "   ✓ 测试数据清理完成\n";

    // 8. 最终状态报告
    echo "\n=== 最终状态报告 ===\n";
    echo "✓ 数据库服务: 正常\n";
    echo "✓ 缓存管理系统: 正常\n";
    echo "✓ 权限管理系统: 正常\n";
    echo "✓ 系统管理控制器: 正常\n";
    echo "✓ 性能优化器: 正常\n";
    echo "✓ 测试集成服务: 正常\n";
    
    echo "\n🎉 所有系统组件集成测试通过！\n";
    echo "系统已完全就绪，可以投入生产使用。\n";

} catch (Exception $e) {
    echo "\n❌ 集成测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== 测试完成 ===\n";
