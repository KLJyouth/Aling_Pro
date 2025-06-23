<?php
/**
 * 简化测试入口文件
 */

// 基本设置
define('APP_ROOT', __DIR__);
define('APP_VERSION', '6.0.0');
define('APP_START_TIME', microtime(true));

// 错误显示
error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    // 加载 Composer autoload
    require_once APP_ROOT . '/vendor/autoload.php';
    echo "✅ Composer autoload 成功\n";
    
    // 加载环境变量
    if (file_exists(APP_ROOT . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
        $dotenv->load();
        echo "✅ 环境变量加载成功\n";
    }
    
    // 简单测试输出
    echo "✅ PHP Version: " . PHP_VERSION . "\n";
    echo "✅ 应用启动成功\n";
    echo "✅ 时间: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Throwable $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "❌ 文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "❌ 调用栈:\n" . $e->getTraceAsString() . "\n";
}
