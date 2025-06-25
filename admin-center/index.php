<?php
/**
 * AlingAi_pro 后台IT技术运维中心
 * 入口文件
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 定义常量
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('ROUTES_PATH', BASE_PATH . '/routes');
define('VIEWS_PATH', BASE_PATH . '/resources/views');

// 加载配置
require_once CONFIG_PATH . '/app.php';

// 自动加载类
spl_autoload_register(function ($class) {
    // 将命名空间转换为文件路径
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
});

// 启动会话
session_start();

// 路由处理
require_once ROUTES_PATH . '/web.php';

// 处理请求
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// 简单的路由分发
$router = new \App\Core\Router();
$router->dispatch($requestUri, $requestMethod);
