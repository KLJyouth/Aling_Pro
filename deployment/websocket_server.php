<?php
/**
 * WebSocket服务器启动脚本
 * 为AlingAi Pro系统提供实时通信支持
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/WebSocket/WebSocketServer.php';

use AlingAi\WebSocket\WebSocketServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

// 读取环境配置
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// WebSocket配置
$websocketHost = '127.0.0.1';
$websocketPort = 8080;

// 创建WebSocket服务器
$webSocketServer = new WebSocketServer();
$wsServer = new WsServer($webSocketServer);
$httpServer = new HttpServer($wsServer);
$server = IoServer::factory($httpServer, $websocketPort, $websocketHost);

echo "==================================================\n";
echo "AlingAi Pro WebSocket服务器启动\n";
echo "==================================================\n";
echo "地址: {$websocketHost}:{$websocketPort}\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n";
echo "进程ID: " . getmypid() . "\n";
echo "==================================================\n";
echo "WebSocket endpoints:\n";
echo "  - ws://{$websocketHost}:{$websocketPort}/chat\n";
echo "  - ws://{$websocketHost}:{$websocketPort}/notifications\n";
echo "  - ws://{$websocketHost}:{$websocketPort}/monitoring\n";
echo "==================================================\n";
echo "按 Ctrl+C 停止服务器\n";
echo "==================================================\n";

// 启动服务器
$server->run();
