<?php
/**
 * 应用程序启动调试脚本
 */

declare(strict_types=1);

// 错误报告设置
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "调试应用程序启动...\n";
echo str_repeat("=", 50) . "\n";

try {
    echo "1. 检查自动加载...\n";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✅ 自动加载成功\n";

    echo "2. 检查环境变量...\n";
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        echo "   ✅ 环境变量加载成功\n";
        echo "   APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "\n";
    } else {
        echo "   ⚠️  .env 文件不存在\n";
    }

    echo "3. 检查应用程序类...\n";
    if (class_exists('AlingAi\Core\Application')) {
        echo "   ✅ Application 类存在\n";
    } else {
        echo "   ❌ Application 类不存在\n";
        exit(1);
    }

    echo "4. 尝试创建应用程序实例...\n";
    $application = AlingAi\Core\Application::create();
    echo "   ✅ 应用程序实例创建成功\n";

    echo "5. 检查 Slim App 实例...\n";
    $app = $application->getApp();
    if ($app instanceof Slim\App) {
        echo "   ✅ Slim App 实例正常\n";
    } else {
        echo "   ❌ Slim App 实例异常\n";
    }

    echo "6. 检查路由注册...\n";
    $routes = $app->getRouteCollector()->getRoutes();
    echo "   ✅ 注册了 " . count($routes) . " 个路由\n";    echo "7. 尝试处理简单请求...\n";
    
    // 创建一个简单的请求
    $request = \Slim\Psr7\Factory\ServerRequestFactory::createFromGlobals();
    $uri = new \Slim\Psr7\Uri('http', 'localhost', 8000, '/');
    $request = $request->withUri($uri);
    $request = $request->withMethod('GET');
    
    echo "   请求 URI: " . $request->getUri() . "\n";
    echo "   请求方法: " . $request->getMethod() . "\n";
    
    $response = $app->handle($request);
    echo "   ✅ 请求处理成功\n";
    echo "   响应状态码: " . $response->getStatusCode() . "\n";
    
    $body = (string) $response->getBody();
    echo "   响应体长度: " . strlen($body) . " 字符\n";
    
    if (strlen($body) > 0) {
        echo "   响应体预览: " . substr($body, 0, 100) . "...\n";
    }
    
    echo "\n✅ 应用程序启动调试完成，应用程序似乎工作正常！\n";

} catch (Throwable $e) {
    echo "\n❌ 发生错误:\n";
    echo "错误信息: " . $e->getMessage() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行号: " . $e->getLine() . "\n";
    echo "\n堆栈跟踪:\n";
    echo $e->getTraceAsString() . "\n";
}