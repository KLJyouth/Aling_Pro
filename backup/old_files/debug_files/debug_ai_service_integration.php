<?php
/**
 * AI服务集成调试脚本
 */

require_once __DIR__ . '/vendor/autoload.php';

// 设置环境变量
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// 确保必要的常量和环境
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', '3.0.0');
}

try {
    echo "🔍 调试AI服务集成...\n";
    echo "====================\n";
    
    // 创建应用实例
    $app = \AlingAi\Core\AlingAiProApplication::create();
    echo "✅ 应用实例创建成功\n";
    
    // 获取容器
    $container = $app->getContainer();
    echo "✅ 容器获取成功\n";
    
    // 获取EnhancedAgentCoordinator
    $coordinator = $container->get(\AlingAi\AI\EnhancedAgentCoordinator::class);
    echo "✅ EnhancedAgentCoordinator获取成功\n";
    
    // 获取状态
    $status = $coordinator->getStatus();
    echo "✅ 状态获取成功\n";
    
    // 检查状态内容
    echo "📊 状态内容:\n";
    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    // 检查关键字段
    echo "🔍 关键字段检查:\n";
    echo "   - 是否有'status'键: " . (isset($status['status']) ? '✅ 是' : '❌ 否') . "\n";
    if (isset($status['status'])) {
        echo "   - status值: " . $status['status'] . "\n";
    }
    
    // 模拟验证器检查
    $aiServiceIntegrationValid = isset($status['status']);
    echo "🎯 AI服务集成验证结果: " . ($aiServiceIntegrationValid ? '✅ 通过' : '❌ 失败') . "\n";
    
} catch (Exception $e) {
    echo "❌ 调试过程中出错: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}
