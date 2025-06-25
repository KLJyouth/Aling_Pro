<?php
/**
 * AlingAi Pro 6.0 - 主入口文�?
 * Enhanced Multi-AI Integration Platform
 * 
 * @version 6.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__];
}

// 设置时区
date_default_timezone_set('Asia/Shanghai'];

// 定义应用常量
define('APP_START_TIME', microtime(true)];
define('APP_ROOT', dirname(__DIR__)];
define('APP_PUBLIC', __DIR__];
define('APP_VERSION', '6.0.0'];
define('APP_NAME', 'AlingAi Pro - Enhanced'];

// 错误报告设置
$isProduction = (getenv('APP_ENV') === 'production'];

if ($isProduction) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE];
    ini_set('display_errors', '0'];
    ini_set('log_errors', '1'];
} else {
    error_reporting(E_ALL];
    ini_set('display_errors', '1'];
    ini_set('log_errors', '1'];
}

// 性能优化设置
ini_set('memory_limit', '1024M'];
ini_set('max_execution_time', '300'];
ini_set('max_input_time', '300'];
ini_set('post_max_size', '128M'];
ini_set('upload_max_filesize', '64M'];

// 安全设置
ini_set('expose_php', 'Off'];
ini_set('session.cookie_httponly', '1'];
ini_set('session.cookie_secure', $isProduction ? '1' : '0'];
ini_set('session.use_strict_mode', '1'];

// 自动加载
require_once APP_ROOT . '/vendor/autoload.php';

// 加载环境变量
if (file_exists(APP_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT];
    $dotenv->load(];
}

try {
    // 调试信息
    error_log("=== Starting AlingAi Pro Application ==="];
    error_log("APP_ROOT: " . APP_ROOT];
    error_log("APP_ENV: " . getenv('APP_ENV')];
    
    // 创建应用实例
    error_log("Creating AlingAiProApplication instance..."];
    
    // 检查核心文�?
    $appFile = APP_ROOT . '/src/Core/AlingAiProApplication.php';
    if (!file_exists($appFile)) {
        throw new Exception("核心应用文件不存�? " . $appFile];
    }
    
    // 加载应用核心
    require_once $appFile;
    
    // 启动应用
    $app = new AlingAi\Core\AlingAiProApplication(];
    $app->run(];
    
} catch (Throwable $e) {
    // 错误处理
    http_response_code(500];
    
    // 记录错误日志
    error_log(sprintf(
        "[%s] FATAL ERROR: %s in %s:%d\nStack trace:\n%s",
        date('Y-m-d H:i:s'],
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    )];
    
    // 显示错误信息
    if (getenv('APP_ENV') === 'development') {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>服务器错�?/h1>";
        echo "<p>服务器遇到了一个错误，请稍后再试�?/p>";
        echo "<p>如果问题持续存在，请联系系统管理员�?/p>";
    }
}

// 记录执行时间
$executionTime = microtime(true) - APP_START_TIME;
error_log("Application execution completed in " . number_format($executionTime, 4) . " seconds"];
?>

