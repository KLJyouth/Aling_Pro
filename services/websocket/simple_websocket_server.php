<?php
/**
 * 简化的WebSocket服务器启动脚本
 * 使用React/Socket启动WebSocket服务器
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\WebSocket\SimpleWebSocketServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

// 配置
$host = '127.0.0.1';
$port = 8080;

echo "=== AlingAi Pro 简化WebSocket服务器 ===\n";
echo "正在启动服务器...\n";
echo "地址: {$host}:{$port}\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n";
echo "进程ID: " . getmypid() . "\n";
echo "=====================================\n";

try {
    // 创建事件循环
    $loop = Loop::get();
    
    // 创建WebSocket应用
    $app = new SimpleWebSocketServer();
    
    // 创建Socket服务器
    $socket = new SocketServer("{$host}:{$port}", $loop);
    
    echo "✅ Socket服务器创建成功\n";
    echo "WebSocket endpoints:\n";
    echo "  - ws://{$host}:{$port}/\n";
    echo "=====================================\n";
    echo "服务器运行中... 按 Ctrl+C 停止\n";
    echo "=====================================\n";
    
    // 处理连接
    $socket->on('connection', function($conn) use ($app) {
        echo "新原始连接: " . $conn->getRemoteAddress() . "\n";
        
        // 这里应该实现WebSocket握手协议
        // 为了简单起见，我们直接处理数据
        $conn->on('data', function($data) use ($app, $conn) {
            echo "收到原始数据: {$data}\n";
            
            // 简单回应
            $response = "Echo: {$data}";
            $conn->write($response);
        });
        
        $conn->on('close', function() {
            echo "连接关闭\n";
        });
    });
    
    // 启动事件循环
    $loop->run();
    
} catch (\Exception $e) {
    echo "❌ 服务器启动失败: " . $e->getMessage() . "\n";
    echo "错误详情:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
