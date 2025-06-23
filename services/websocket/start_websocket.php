<?php
/**
 * WebSocket服务器启动测试
 * 版本: 5.0.0-Final
 */

declare(strict_types=1);

echo "🔌 WebSocket服务器启动测试\n";
echo str_repeat("=", 50) . "\n";

// 检查WebSocket服务器文件
if (!file_exists('src/Security/WebSocketSecurityServer.php')) {
    echo "❌ WebSocket服务器文件不存在\n";
    exit(1);
}

echo "✅ WebSocket服务器文件存在\n";

// 检查端口8081是否可用
$connection = @fsockopen('localhost', 8081, $errno, $errstr, 1);
if ($connection) {
    fclose($connection);
    echo "⚠️ 端口8081已被占用，WebSocket服务器可能已在运行\n";
} else {
    echo "✅ 端口8081可用\n";
}

// 加载WebSocket服务器
require_once 'src/Security/WebSocketSecurityServer.php';

echo "✅ WebSocket服务器类加载成功\n";

try {
    echo "🚀 准备启动WebSocket安全监控服务器...\n";
    echo "监听地址: 0.0.0.0:8081\n";
    echo "按 Ctrl+C 停止服务器\n";
    echo str_repeat("-", 50) . "\n";
    
    // 创建WebSocket服务器实例
    $server = new \AlingAI\Security\WebSocketSecurityServer('0.0.0.0', 8081);
    
    echo "✅ WebSocket服务器实例创建成功\n";
    echo "🎯 启动服务器监听...\n\n";
    
    // 启动服务器
    $server->run();
    
} catch (Exception $e) {
    echo "❌ WebSocket服务器启动失败: " . $e->getMessage() . "\n";
    echo "💡 可能的原因:\n";
    echo "  • 端口8081被占用\n";
    echo "  • 缺少必要的PHP扩展\n";
    echo "  • 权限不足\n";
    exit(1);
}
