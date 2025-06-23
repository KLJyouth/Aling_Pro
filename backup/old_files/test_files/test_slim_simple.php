<?php
/**
 * 简单的测试脚本，直接测试 Slim 应用
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// 定义常量
define('APP_ROOT', __DIR__);
define('APP_PUBLIC', __DIR__ . '/public');

// 自动加载
require_once __DIR__ . '/vendor/autoload.php';

// 加载环境变量
use Dotenv\Dotenv;
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// 创建 Slim 应用
$app = \Slim\Factory\AppFactory::create();

// 简单的测试路由
$app->get('/test', function ($request, $response, $args) {
    $response->getBody()->write('Hello, World!');
    return $response;
});

$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write('AlingAi Pro Test');
    return $response;
});

// 添加错误中间件
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

echo "=== 简单 Slim 测试 ===\n";

try {
    // 模拟请求
    $request = \Slim\Psr7\Factory\ServerRequestFactory::createFromGlobals();
    $response = $app->handle($request);
    
    echo "状态码: " . $response->getStatusCode() . "\n";
    echo "响应体: " . $response->getBody() . "\n";
    
} catch (Throwable $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}

echo "=== 测试完成 ===\n";
