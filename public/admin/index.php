<?php
/**
 * AlingAi Pro - 管理中心入口点文件
 * 
 * 此文件作为前端入口点，将请求重定向到实际的admin-center应用
 * 遵循入口点(Entry Point)模式，增强安全性
 */

// 定义一个标识，表明这是一个有效的入口点
define('ADMIN_ENTRY_POINT', true);

// 设置相对路径
$adminCenterPath = dirname(dirname(__DIR__)) . '/admin-center';

// 检查必要的目录是否存在
$storagePaths = [
    $adminCenterPath . '/storage',
    $adminCenterPath . '/storage/logs',
    $adminCenterPath . '/storage/cache',
    $adminCenterPath . '/storage/temp',
    $adminCenterPath . '/storage/uploads',
    $adminCenterPath . '/storage/backups'
];

foreach ($storagePaths as $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

// 包含admin-center的引导文件
try {
    // 加载引导文件
    require_once $adminCenterPath . '/bootstrap.php';
    
    // 获取路由实例
    $router = new \App\Core\Router();
    
    // 加载路由配置
    if (file_exists($adminCenterPath . '/routes/web.php')) {
        require_once $adminCenterPath . '/routes/web.php';
    }
    
    if (file_exists($adminCenterPath . '/routes/api.php')) {
        require_once $adminCenterPath . '/routes/api.php';
    }

    // 创建App实例并运行
    $app = new \App\Core\App($router);
    $app->run();
} catch (Exception $e) {
    // 捕获并显示错误
    header('HTTP/1.1 500 Internal Server Error');
    echo '<h1>系统错误</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    
    // 记录错误日志
    error_log('管理中心启动失败: ' . $e->getMessage() . ' 在 ' . $e->getFile() . ' 第 ' . $e->getLine() . ' 行');
} 
