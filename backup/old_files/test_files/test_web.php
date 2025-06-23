<?php

error_reporting(E_ALL & ~E_WARNING); // 忽略Redis警告

require_once __DIR__ . '/vendor/autoload.php';

try {
    echo "Starting AlingAi Web Application Test...\n";
    
    // 模拟HTTP请求环境
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['HTTPS'] = '';
    
    // 创建Application实例
    $app = \AlingAi\Core\Application::create();
    echo "✓ Application created successfully\n";
    
    // 获取Slim App
    $slimApp = $app->getApp();
    echo "✓ Slim App retrieved\n";
    
    // 检查路由是否注册
    $routeCollector = $slimApp->getRouteCollector();
    $routes = $routeCollector->getRoutes();
    echo "✓ Found " . count($routes) . " routes registered\n";
    
    // 列出前几个路由
    $count = 0;
    foreach ($routes as $route) {
        if ($count < 5) {
            echo "  - " . implode('|', $route->getMethods()) . " " . $route->getPattern() . "\n";
            $count++;
        }
    }
    
    echo "✓ Web application is ready to serve requests!\n";
    
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
