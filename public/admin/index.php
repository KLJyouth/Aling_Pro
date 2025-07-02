<?php
/**
 * AlingAi Pro 后台管理入口文件
 * 
 * 处理所有后台管理请求
 */

// 定义应用根目录
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('ADMIN_PATH', ROOT_PATH . '/admin-center');

// 引入配置文件
require_once ROOT_PATH . '/public/config/config_loader.php';

// 引入后台引导文件
require_once ADMIN_PATH . '/bootstrap.php';

// 获取请求URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// 移除基础路径
$basePath = '/admin';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath)) ?: '/';
}

// 移除查询字符串
$position = strpos($requestUri, '?');
if ($position !== false) {
    $requestUri = substr($requestUri, 0, $position);
}

// 获取请求方法
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    // 初始化应用
    $app = new \App\Core\App($router);
    
    // 运行应用
    $app->run();
} catch (Exception $e) {
    // 记录错误
    error_log("后台错误: " . $e->getMessage());
    
    // 显示错误页面
    if ($config['debug']) {
        echo "<h1>系统错误</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        // 重定向到错误页面
        header('Location: /admin/error');
        exit;
    }
}
?> 
