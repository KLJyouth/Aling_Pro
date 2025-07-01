<?php
/**
 * AlingAi Pro - IT运维中心入口文件
 * 
 * 处理所有到达后台管理中心的请求
 */

// 引入引导文件
require_once __DIR__ . '/bootstrap.php';

try {
    // 获取请求URI和方法
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // 解析URL，去掉/admin或/admin-center前缀
    if (strpos($requestUri, '/admin/') === 0) {
        $requestUri = substr($requestUri, 6); // 去掉'/admin'
    } elseif (strpos($requestUri, '/admin-center/') === 0) {
        $requestUri = substr($requestUri, 13); // 去掉'/admin-center'
    }
    
    // 创建应用实例
    $app = new \App\Core\App();
    
    // 运行应用
    $app->run();
    
} catch (Exception $e) {
    // 处理异常
    if (class_exists('\App\Core\Logger')) {
        \App\Core\Logger::error('应用启动失败: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        error_log('应用启动失败: ' . $e->getMessage());
    }
    
    // 显示错误页面
    http_response_code(500);
    
    if (file_exists(__DIR__ . '/resources/views/errors/500.php')) {
        // 设置错误页面需要的变量
        $isDebug = getenv('APP_ENV') === 'development';
        $exception = $e;
        include __DIR__ . '/resources/views/errors/500.php';
    } else {
        echo "<h1>服务器错误</h1>";
        echo "<p>抱歉，系统遇到了错误。请稍后再试或联系系统管理员。</p>";
    }
}
