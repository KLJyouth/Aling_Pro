<?php
/**
 * 安全系统入口文件
 * @version 1.1.0
 * @author AlingAi Team
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set("display_errors", 1);

// 设置时区
date_default_timezone_set("Asia/Shanghai");

// 自动加载类
spl_autoload_register(function ($class) {
    // 将命名空间转换为文件路径
    $prefix = "AlingAi\\Admin\\Security\\";
    
    // 检查类是否使用该命名空间前缀
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // 获取相对类名
    $relative_class = substr($class, $len);
    
    // 将命名空间分隔符替换为目录分隔符，添加.php后缀
    $file = __DIR__ . "/security/classes/" . str_replace("\\", "/", $relative_class) . ".php";
    
    // 如果文件存在，则加载它
    if (file_exists($file)) {
        require $file;
        return;
    }
    
    // 检查控制器目录
    $controller_file = __DIR__ . "/security/controllers/" . str_replace("\\", "/", $relative_class) . ".php";
    if (file_exists($controller_file)) {
        require $controller_file;
    }
});

// 定义常量
define("SECURITY_ROOT", __DIR__ . "/security");
define("SECURITY_VERSION", "1.1.0");
define("SECURITY_SYSTEM", true);

// 检查是否已安装
$dbPath = dirname(dirname(__DIR__)) . '/storage/database/admin.sqlite';
if (!file_exists($dbPath)) {
    header("Location: /admin/security/install.php");
    exit;
}

// 检查用户是否已登录
session_start();
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    // 保存当前URL以便登录后重定向回来
    $_SESSION["redirect_after_login"] = $_SERVER["REQUEST_URI"];
    header("Location: /admin/login.php");
    exit;
}

// 处理请求
$module = $_GET["module"] ?? "security";

switch ($module) {
    case "quantum":
        // 量子加密监控
        $controller = new AlingAi\Admin\Security\Controllers\QuantumMonitorController();
        $controller->handleRequest();
        break;
        
    case "api":
        // API安全监控
        $controller = new AlingAi\Admin\Security\Controllers\ApiMonitorController();
        $controller->handleRequest();
        break;
        
    case "security":
    default:
        // 基本安全管理
        $controller = new AlingAi\Admin\Security\Controllers\SecurityController();
        $controller->handleRequest();
        break;
}
