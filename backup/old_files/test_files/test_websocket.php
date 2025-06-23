<?php
/**
 * WebSocket 客户端测试脚本
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;

echo "=== WebSocket 连接测试 ===\n";

try {
    // 创建WebSocket连接器
    $connector = new Connector();
    
    echo "正在连接到 WebSocket 服务器 (ws://localhost:8080)...\n";
    
    $connector('ws://localhost:8080')
        ->then(function (WebSocket $conn) {
            echo "✓ WebSocket 连接成功建立！\n";
            
            // 发送测试消息
            $testMessage = json_encode([
                'type' => 'test',
                'message' => 'WebSocket 连接测试',
                'timestamp' => date('c'),
                'client_id' => 'test_client_' . uniqid()
            ]);
            
            echo "发送测试消息: {$testMessage}\n";
            $conn->send($testMessage);
            
            // 监听消息响应
            $conn->on('message', function ($msg) {
                echo "收到服务器响应: {$msg->getPayload()}\n";
                
                // 解析响应
                $response = json_decode($msg->getPayload(), true);
                if ($response) {
                    echo "✓ 消息格式正确\n";
                    echo "  - 类型: " . ($response['type'] ?? 'unknown') . "\n";
                    echo "  - 状态: " . ($response['status'] ?? 'unknown') . "\n";
                    echo "  - 时间戳: " . ($response['timestamp'] ?? 'unknown') . "\n";
                } else {
                    echo "✗ 响应格式错误\n";
                }
                
                echo "=== WebSocket 测试完成 ===\n";
                exit(0);
            });
            
            // 连接关闭处理
            $conn->on('close', function ($code = null, $reason = null) {
                echo "WebSocket 连接已关闭 (Code: {$code}, Reason: {$reason})\n";
            });
            
            // 设置5秒超时
            \React\EventLoop\Loop::get()->addTimer(5, function() use ($conn) {
                echo "⏱ 测试超时，关闭连接\n";
                $conn->close();
                exit(1);
            });
            
        }, function (\Exception $e) {
            echo "✗ WebSocket 连接失败: " . $e->getMessage() . "\n";
            exit(1);
        });
    
    // 启动事件循环
    \React\EventLoop\Loop::get()->run();
    
} catch (\Exception $e) {
    echo "✗ WebSocket 测试异常: " . $e->getMessage() . "\n";
    exit(1);
}
