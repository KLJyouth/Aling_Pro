<?php
/**
 * 直接测试应用程序调用，获取详细错误信息
 */

// 设置错误显示
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 定义应用常量
define('APP_START_TIME', microtime(true));
define('APP_ROOT', __DIR__);
define('APP_PUBLIC', __DIR__ . '/public');
define('APP_VERSION', '2.0.0');
define('APP_NAME', 'AlingAi Pro');

// 自动加载
require_once __DIR__ . '/vendor/autoload.php';

// 加载环境变量
use Dotenv\Dotenv;
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

echo "=== 直接应用程序调用测试 ===\n";

try {
    echo "1. 创建应用程序实例...\n";
    $application = \AlingAi\Core\Application::create();
    echo "   ✓ 应用程序实例创建成功\n";
    
    echo "2. 准备运行应用...\n";
    // 模拟 HTTP 请求环境
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['SERVER_PORT'] = '8080';
    $_SERVER['HTTPS'] = '';
    
    // 运行应用
    echo "3. 运行应用...\n";
    $application->run();
    
} catch (Throwable $e) {
    echo "❌ 发生错误:\n";
    echo "消息: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}

echo "=== 测试完成 ===\n";