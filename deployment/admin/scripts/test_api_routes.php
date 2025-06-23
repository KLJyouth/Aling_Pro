<?php
/**
 * 测试API路由是否正确注册
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Core\Application;

try {
    echo "=== API路由测试 ===\n";
      // 创建应用实例
    $application = Application::create();
    $app = $application->getApp();
    
    echo "1. 应用创建成功\n";
    
    // 获取路由收集器
    $routeCollector = $app->getRouteCollector();
    $routes = $routeCollector->getRoutes();
    
    echo "2. 路由总数: " . count($routes) . "\n";
    
    // 列出所有路由
    echo "3. 所有注册的路由:\n";
    foreach ($routes as $route) {
        $methods = implode(', ', $route->getMethods());
        $pattern = $route->getPattern();
        echo "   [$methods] $pattern\n";
    }
    
    // 检查特定的API路由
    $apiRoutes = array_filter($routes, function($route) {
        return strpos($route->getPattern(), '/api/') === 0;
    });
    
    echo "4. API路由数量: " . count($apiRoutes) . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
}