<?php
/**
 * AlingAi Pro - Advanced PHP Application Entry Point
 * 
 * PHP 7.4+ Advanced Architecture
 * Nginx 1.20.2+ Compatible
 * MySQL 5.7.43+ Optimized
 * CentOS 8.0+ x64 Production Ready
 * 
 * @package AlingAi\Pro
 * @version 2.0.0
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

use AlingAi\Core\Application;
use Dotenv\Dotenv;

// 定义应用常量
define('APP_START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));
define('APP_PUBLIC', __DIR__);
define('APP_VERSION', '2.0.0');
define('APP_NAME', 'AlingAi Pro');

// 错误报告设置 - 强制开发模式显示错误
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

// 内存和时间限制
ini_set('memory_limit', '512M');
set_time_limit(300);

// 自动加载
require_once APP_ROOT . '/vendor/autoload.php';

// 加载环境变量
if (file_exists(APP_ROOT . '/.env')) {
    $dotenv = Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
}

try {
    // 创建应用实例
    $application = Application::create();
    
    // 运行应用
    $application->run();
    
} catch (Throwable $e) {
    // 致命错误处理
    http_response_code(500);
    
    if (getenv('APP_ENV') === 'development') {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";    } else {
        echo "<h1>服务器错误</h1>";
        echo "<p>服务器遇到了一个错误，请稍后再试。</p>";
        echo "<p>如果问题持续存在，请联系管理员。</p>";
    }
    
    // 记录错误日志
    error_log(sprintf(
        "[%s] FATAL ERROR: %s in %s:%d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
}
