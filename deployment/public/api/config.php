<?php
/**
 * 环境配置加载器
 * 确保所有API端点都能正确访问环境变量
 */

// 加载 composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// 加载环境变量
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
}

// 确保基本配置可用
if (!function_exists('env')) {
    function env($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}

// 邮件配置常量
if (!defined('MAIL_CONFIG')) {
    define('MAIL_CONFIG', [
        'host' => env('MAIL_HOST', 'localhost'),
        'port' => (int) env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@alingai.com'),
        'from_name' => env('MAIL_FROM_NAME', 'AlingAi Pro'),
        'reply_to_address' => env('MAIL_REPLY_TO_ADDRESS', ''),
        'reply_to_name' => env('MAIL_REPLY_TO_NAME', ''),
    ]);
}

// 应用配置常量
if (!defined('APP_CONFIG')) {
    define('APP_CONFIG', [
        'name' => env('APP_NAME', 'AlingAi Pro'),
        'env' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', 'false') === 'true',
        'url' => env('APP_URL', 'http://localhost'),
        'timezone' => env('APP_TIMEZONE', 'Asia/Shanghai'),
    ]);
}
?>
