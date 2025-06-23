<?php

require_once __DIR__ . '/vendor/autoload.php';

// 加载环境变量
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

echo "=== AlingAi Pro 应用程序启动测试 ===\n";

try {
    echo "1. 创建应用程序实例...\n";
    $app = \AlingAi\Core\Application::create();
    echo "   ✓ 应用程序实例创建成功\n";
    
    echo "2. 检查容器服务...\n";
    $container = $app->getContainer();
    
    echo "   - 测试数据库服务...\n";
    $dbService = $container->get(\AlingAi\Services\DatabaseServiceInterface::class);
    echo "   ✓ 数据库服务类型: " . get_class($dbService) . "\n";
    
    echo "   - 测试缓存服务...\n";
    $cacheService = $container->get(\AlingAi\Services\CacheService::class);
    echo "   ✓ 缓存服务类型: " . get_class($cacheService) . "\n";
    
    echo "3. 测试文件存储功能...\n";
    if ($dbService instanceof \AlingAi\Services\FileStorageService) {
        $testData = [
            'name' => '测试用户',
            'email' => 'test@example.com',
            'status' => 'active'
        ];
        
        echo "   - 插入测试数据...\n";
        $result = $dbService->insert('users', $testData);
        echo "   ✓ 插入结果: " . ($result ? 'success' : 'failed') . "\n";
        
        echo "   - 查询所有数据...\n";
        $allUsers = $dbService->findAll('users');
        echo "   ✓ 查询到 " . count($allUsers) . " 条记录\n";
        
        if (!empty($allUsers)) {
            $user = $allUsers[0];
            echo "   - 查找单条记录...\n";
            $foundUser = $dbService->find('users', $user['id']);
            echo "   ✓ 找到用户: " . ($foundUser ? $foundUser['name'] : 'not found') . "\n";
        }
    }
    
    echo "4. 测试路由配置...\n";
    $slim = $app->getApp();
    $routes = $slim->getRouteCollector()->getRoutes();
    echo "   ✓ 已配置 " . count($routes) . " 个路由\n";
    
    // 显示前几个路由
    $routeCount = 0;
    foreach ($routes as $route) {
        if ($routeCount < 5) {
            $methods = implode('|', $route->getMethods());
            echo "   - {$methods} {$route->getPattern()}\n";
            $routeCount++;
        }
    }
    if (count($routes) > 5) {
        echo "   - ... 还有 " . (count($routes) - 5) . " 个路由\n";
    }
    
    echo "\n✅ 应用程序启动测试成功！\n";
    echo "📝 应用程序已准备就绪，可以通过 Web 服务器运行。\n";
    
} catch (\Exception $e) {
    echo "\n❌ 应用程序启动失败:\n";
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== 测试完成 ===\n";