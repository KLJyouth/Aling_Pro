<?php
/**
 * AlingAi Pro - 简单入口文件
 * 
 * 使用SimpleApplication类处理请求
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 定义应用常量
define('APP_START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));
define('APP_PUBLIC', __DIR__);
define('APP_VERSION', '6.0.0');
define('APP_NAME', 'AlingAi Pro - Simple');

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

// 自动加载
require_once APP_ROOT . '/vendor/autoload.php';

// 创建应用程序实例
$app = new \AlingAi\Core\SimpleApplication();

// 运行应用程序
$app->run();

// 输出执行时间
$executionTime = microtime(true) - APP_START_TIME;
echo "<!-- 应用程序执行完成，耗时 " . round($executionTime, 4) . " 秒 -->"; 