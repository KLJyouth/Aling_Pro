<?php
/**
 * 路由测试脚本
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

try {
    echo "正在测试路由配置...\n";
    
    // 加载环境变量
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
    
    // 创建应用程序实例
    $app = new AlingAi\Core\Application();
    $slimApp = $app->getApp();
    
    // 获取路由收集器
    $routeCollector = $slimApp->getRouteCollector();
    $routes = $routeCollector->getRoutes();
    
    echo "发现 " . count($routes) . " 个路由:\n";
    echo str_repeat("-", 60) . "\n";
    
    $duplicateRoutes = [];
    $routePatterns = [];
    
    foreach ($routes as $route) {
        $pattern = $route->getPattern();
        $methods = implode(', ', $route->getMethods());
        $name = $route->getName() ?? '(unnamed)';
        
        echo sprintf("%-8s %-30s %s\n", $methods, $pattern, $name);
        
        // 检查重复路由
        $key = $methods . ':' . $pattern;
        if (isset($routePatterns[$key])) {
            $duplicateRoutes[] = $key;
        } else {
            $routePatterns[$key] = true;
        }
    }
    
    echo str_repeat("-", 60) . "\n";
    
    if (!empty($duplicateRoutes)) {
        echo "❌ 发现重复路由:\n";
        foreach ($duplicateRoutes as $duplicate) {
            echo "   - $duplicate\n";
        }
    } else {
        echo "✅ 没有发现重复路由\n";
    }
    
    // 测试几个关键路由
    echo "\n测试关键路由:\n";
    echo str_repeat("-", 60) . "\n";
    
    $testRoutes = [
        'GET /',
        'GET /dashboard',
        'GET /api/v1/documents/categories',
        'GET /api/v1/documents/{id}',
        'GET /admin'
    ];
    
    foreach ($testRoutes as $testRoute) {
        $found = false;
        foreach ($routes as $route) {
            $pattern = $route->getPattern();
            $methods = $route->getMethods();
            
            list($method, $path) = explode(' ', $testRoute, 2);
            
            if (in_array($method, $methods) && $pattern === $path) {
                echo "✅ $testRoute - 找到\n";
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "❌ $testRoute - 未找到\n";
        }
    }
    
    echo "\n路由测试完成!\n";
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}