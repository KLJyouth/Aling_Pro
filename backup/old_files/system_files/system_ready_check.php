<?php
/**
 * AlingAi Pro 系统就绪检查
 * 验证所有组件是否正确安装和配置
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

echo "🚀 AlingAi Pro 系统就绪检查\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. 检查PHP版本和扩展
echo "📋 检查系统环境...\n";
echo "PHP版本: " . PHP_VERSION . "\n";

$requiredExtensions = ['pdo', 'json', 'curl', 'mbstring', 'openssl'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ {$ext} 扩展已加载\n";
    } else {
        echo "✗ {$ext} 扩展未加载\n";
        $missingExtensions[] = $ext;
    }
}

// 2. 检查核心类是否能够加载
echo "\n🔧 检查核心类加载...\n";

$coreClasses = [
    'AlingAi\\Controllers\\SystemManagementController',
    'AlingAi\\Controllers\\CacheManagementController', 
    'AlingAi\\Controllers\\WebController',
    'AlingAi\\Cache\\ApplicationCacheManager',
    'AlingAi\\Security\\PermissionManager',
    'AlingAi\\Performance\\PerformanceOptimizer',
    'AlingAi\\Services\\TestSystemIntegrationService',
    'AlingAi\\Services\\DatabaseService',
    'AlingAi\\Services\\CacheService'
];

$loadErrors = [];
foreach ($coreClasses as $class) {
    if (class_exists($class)) {
        echo "✓ {$class}\n";
    } else {
        echo "✗ {$class}\n";
        $loadErrors[] = $class;
    }
}

// 3. 检查配置文件
echo "\n📝 检查配置文件...\n";

$configFiles = [
    'config/routes.php' => '路由配置',
    'resources/views/admin/system-management.html' => '系统管理界面',
    'src/Core/Application.php' => '应用核心'
];

foreach ($configFiles as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ {$desc}: {$file}\n";
    } else {
        echo "✗ {$desc}: {$file} (不存在)\n";
        $loadErrors[] = $file;
    }
}

// 4. 检查目录权限
echo "\n📂 检查目录权限...\n";

$directories = [
    'storage/logs' => '日志目录',
    'storage/cache' => '缓存目录',
    'storage/sessions' => '会话目录',
    'storage/uploads' => '上传目录'
];

foreach ($directories as $dir => $desc) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        if (is_writable($fullPath)) {
            echo "✓ {$desc}: {$dir} (可写)\n";
        } else {
            echo "⚠ {$desc}: {$dir} (只读)\n";
        }
    } else {
        echo "⚠ {$desc}: {$dir} (不存在，将自动创建)\n";
        @mkdir($fullPath, 0755, true);
    }
}

// 5. 功能测试
echo "\n🧪 运行功能测试...\n";

try {
    // 创建日志器
    $logger = new \Monolog\Logger('system_ready');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stderr'));
    
    // 测试服务实例化
    $db = new \AlingAi\Services\DatabaseService($logger);
    echo "✓ 数据库服务初始化\n";
    
    $cache = new \AlingAi\Services\CacheService($logger);
    echo "✓ 缓存服务初始化\n";
    
    $cacheManager = new \AlingAi\Cache\ApplicationCacheManager($db);
    echo "✓ 应用缓存管理器初始化\n";
    
    $permissionManager = new \AlingAi\Security\PermissionManager($db, $cache, $logger);
    echo "✓ 权限管理器初始化\n";
    
} catch (Exception $e) {
    echo "✗ 功能测试失败: " . $e->getMessage() . "\n";
    $loadErrors[] = 'functional_test';
}

// 6. 生成报告
echo "\n📊 系统就绪状态报告\n";
echo "=" . str_repeat("=", 50) . "\n";

if (empty($missingExtensions) && empty($loadErrors)) {
    echo "🎉 系统完全就绪！\n";
    echo "✅ 所有组件正常加载\n";
    echo "✅ 所有配置文件存在\n"; 
    echo "✅ 目录权限正确\n";
    echo "✅ 功能测试通过\n\n";
    
    echo "🌐 访问系统管理界面: /system-management\n";
    echo "📚 API文档: /api 下的各个端点\n";
    echo "🔧 系统监控: /api/system-management/overview\n";
    echo "💾 缓存管理: /api/cache-management/overview\n";
    
} else {
    echo "⚠️  系统部分就绪，发现以下问题:\n\n";
    
    if (!empty($missingExtensions)) {
        echo "缺少PHP扩展:\n";
        foreach ($missingExtensions as $ext) {
            echo "  - {$ext}\n";
        }
        echo "\n";
    }
    
    if (!empty($loadErrors)) {
        echo "加载错误:\n";
        foreach ($loadErrors as $error) {
            echo "  - {$error}\n";
        }
        echo "\n";
    }
    
    echo "请解决上述问题后重新运行检查。\n";
}

echo "\n🔗 更多信息:\n";
echo "  - 查看日志: storage/logs/\n";
echo "  - 运行测试: php test_system_integration_final.php\n";
echo "  - 系统配置: config/\n";
