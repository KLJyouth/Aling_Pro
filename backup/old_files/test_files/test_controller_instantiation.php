<?php
/**
 * 测试控制器实例化
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// 定义常量
define('APP_ROOT', __DIR__);
define('APP_PUBLIC', __DIR__ . '/public');

// 自动加载
require_once __DIR__ . '/vendor/autoload.php';

// 加载环境变量
use Dotenv\Dotenv;
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// 手动设置JWT密钥以防万一
if (!getenv('JWT_SECRET')) {
    putenv('JWT_SECRET=your-super-secret-jwt-key-change-this-in-production');
}

echo "=== 测试控制器实例化 ===\n";

try {
    // 创建日志记录器
    $logger = new \Monolog\Logger('test');
    $logger->pushHandler(new \Monolog\Handler\NullHandler());
    
    // 尝试创建数据库服务
    try {
        $db = new \AlingAi\Services\DatabaseService($logger);
        echo "✓ DatabaseService 创建成功\n";
    } catch (\Exception $e) {
        echo "❌ DatabaseService 创建失败: " . $e->getMessage() . "\n";
        echo "尝试使用 FileStorageService...\n";
        $db = new \AlingAi\Services\FileStorageService($logger);
        echo "✓ FileStorageService 创建成功\n";
    }
    
    // 创建缓存服务
    $cache = new \AlingAi\Services\CacheService($logger);
    echo "✓ CacheService 创建成功\n";
    
    // 尝试创建 WebController
    $webController = new \AlingAi\Controllers\WebController($db, $cache);
    echo "✓ WebController 创建成功\n";
    
    echo "控制器类型: " . get_class($webController) . "\n";
    
} catch (\Throwable $e) {
    echo "❌ 控制器实例化失败:\n";
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}

echo "=== 测试完成 ===\n";
