<?php
/**
 * AlingAi Pro 6.0 - 主入口文件
 * Enhanced Multi-AI Integration Platform
 * 
 * @version 6.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

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

// 直接包含路由器
include_once __DIR__ . '/router_standalone.php';

// 输出执行时间
$executionTime = microtime(true) - APP_START_TIME;
echo "<!-- 应用程序执行完成，耗时 " . round($executionTime, 4) . " 秒 -->";
?>

