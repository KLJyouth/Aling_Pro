<?php
/**
 * 安全系统入口文件
 * @version 1.0.0
 * @author AlingAi Team
 */

// 启动会话
session_start();

// 检查是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 未登录，重定向到登录页面
    header('Location: ../login.php');
    exit;
}

// 自动加载类
spl_autoload_register(function ($class) {
    // 将命名空间转换为文件路径
    $prefix = 'AlingAi\\Admin\\Security\\';
    
    // 如果类不在我们的命名空间中，跳过
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    
    // 获取相对类名
    $relative_class = substr($class, strlen($prefix));
    
    // 将命名空间分隔符替换为目录分隔符
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative_class) . '.php';
    
    // 如果文件存在，加载它
    if (file_exists($file)) {
        require $file;
    }
});

// 加载控制器
require_once __DIR__ . '/controllers/SecurityController.php';

// 加载安全管理器
require_once __DIR__ . '/classes/SecurityManager.php';

// 创建控制器实例
$controller = new \AlingAi\Admin\Security\Controllers\SecurityController();

// 处理请求
$controller->handleRequest(); 