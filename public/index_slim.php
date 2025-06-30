<?php
/**
 * AlingAi Pro 6.0 - Slim框架入口文件
 * 
 * @version 6.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 定义应用常量
define('APP_START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));
define('APP_PUBLIC', __DIR__);
define('APP_VERSION', '6.0.0');
define('APP_NAME', 'AlingAi Pro - Enhanced');

// 错误报告设置
$isProduction = (getenv('APP_ENV') === 'production');

if ($isProduction) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
}

// 性能优化设置
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('post_max_size', '128M');
ini_set('upload_max_filesize', '64M');

// 安全设置
ini_set('expose_php', 'Off');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', $isProduction ? '1' : '0');
ini_set('session.use_strict_mode', '1');

// 自动加载
require_once APP_ROOT . '/vendor/autoload.php';

// 创建Slim应用
$app = \Slim\Factory\AppFactory::create();

// 添加路由中间件
$app->addRoutingMiddleware();

// 添加错误中间件
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// 设置基本路由
$app->get('/', function ($request, $response) {
    $response->getBody()->write(file_get_contents(APP_PUBLIC . '/index.html'));
    return $response;
});

$app->get('/login', function ($request, $response) {
    include APP_PUBLIC . '/login_slim.php';
    return $response;
});

$app->post('/login', function ($request, $response) {
    include APP_PUBLIC . '/login_slim.php';
    return $response;
});

$app->get('/dashboard', function ($request, $response) {
    // 检查是否已登录
    session_start();
    if (!isset($_SESSION['user_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    
    $response->getBody()->write(file_get_contents(APP_PUBLIC . '/dashboard.html'));
    return $response;
});

// 运行应用
$app->run(); 