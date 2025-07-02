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
if (file_exists(ROOT_PATH . '/public/config/config_loader.php')) {
    require_once ROOT_PATH . '/public/config/config_loader.php';
} else {
    // 配置加载器不存在，显示错误信息
    header('HTTP/1.1 500 Internal Server Error');
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统错误 - AlingAi Pro</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 50px;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #4a6bdf;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #3a5bbf;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统配置错误</h1>
        <p>无法加载系统配置，请确保系统正确安装。</p>
        <p>缺少文件: /public/config/config_loader.php</p>
        <a href="/" class="btn">返回首页</a>
    </div>
</body>
</html>';
    exit;
}

// 检查后台引导文件是否存在
if (file_exists(ADMIN_PATH . '/bootstrap.php')) {
    try {
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
        
        // 初始化应用
        if (isset($router) && class_exists('\App\Core\App')) {
            $app = new \App\Core\App($router);
            
            // 运行应用
            $app->run();
        } else {
            throw new Exception("路由器或应用程序类未正确初始化");
        }
    } catch (Exception $e) {
        // 记录错误
        error_log("后台错误: " . $e->getMessage());
        
        // 显示错误页面
        if (isset($config) && isset($config['debug']) && $config['debug']) {
            echo "<h1>系统错误</h1>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            // 重定向到错误页面
            header('Location: /error.php?code=500');
            exit;
        }
    }
} else {
    // 后台引导文件不存在，显示错误信息
    header('HTTP/1.1 503 Service Unavailable');
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统维护中 - AlingAi Pro</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 50px;
        }
        h1 {
            color: #4a6bdf;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #4a6bdf;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #3a5bbf;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统维护中</h1>
        <p>管理后台正在维护中，请稍后再试。</p>
        <p>缺少文件: ' . ADMIN_PATH . '/bootstrap.php</p>
        <a href="/" class="btn">返回首页</a>
    </div>
</body>
</html>';
    exit;
}
?> 
