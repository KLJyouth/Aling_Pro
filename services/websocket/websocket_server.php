<?php
/**
 * WebSocket服务器启动脚本
 * 使用ReactPHP为AlingAi Pro系统提供实时通信支持
 */

require_once __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Psr\Http\Message\ServerRequestInterface;

// 读取环境配置
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// 配置
$websocketHost = '127.0.0.1';
$websocketPort = 8081; // 使用不同的端口避免冲突
$httpPort = 8080;

// 创建事件循环
$loop = Loop::get();

// 存储WebSocket连接
$connections = new SplObjectStorage();
$userConnections = [];

// 创建HTTP服务器处理WebSocket升级
$httpServer = new HttpServer($loop, function (ServerRequestInterface $request) use (&$connections, &$userConnections) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    
    // 处理WebSocket升级请求
    if ($request->hasHeader('Upgrade') && $request->getHeaderLine('Upgrade') === 'websocket') {
        return handleWebSocketUpgrade($request, $connections, $userConnections);
    }
    
    // 处理普通HTTP请求
    switch ($path) {
        case '/health':
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'status' => 'healthy',
                'timestamp' => time(),
                'connections' => count($connections),
                'uptime' => time() - $_SERVER['REQUEST_TIME']
            ]));
            
        case '/stats':
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'total_connections' => count($connections),
                'authenticated_users' => count($userConnections),
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ]));
            
        default:
            return new Response(404, [], 'Not Found');
    }
});

// 创建Socket服务器
$socket = new SocketServer("{$websocketHost}:{$websocketPort}", [], $loop);
$httpServer->listen($socket);

echo "==================================================\n";
echo "AlingAi Pro WebSocket服务器启动\n";
echo "==================================================\n";
echo "HTTP/WebSocket地址: {$websocketHost}:{$websocketPort}\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n";
echo "进程ID: " . getmypid() . "\n";
echo "==================================================\n";
echo "WebSocket endpoints:\n";
echo "  - ws://{$websocketHost}:{$websocketPort}/chat\n";
echo "  - ws://{$websocketHost}:{$websocketPort}/notifications\n";
echo "  - ws://{$websocketHost}:{$websocketPort}/monitoring\n";
echo "==================================================\n";
echo "HTTP endpoints:\n";
echo "  - http://{$websocketHost}:{$websocketPort}/health\n";
echo "  - http://{$websocketHost}:{$websocketPort}/stats\n";
echo "==================================================\n";
echo "按 Ctrl+C 停止服务器\n";
echo "==================================================\n";

// 处理WebSocket升级
function handleWebSocketUpgrade($request, &$connections, &$userConnections) {
    // 简化的WebSocket握手
    $key = $request->getHeaderLine('Sec-WebSocket-Key');
    $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    
    return new Response(101, [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $acceptKey,
        'Sec-WebSocket-Protocol' => 'chat'
    ]);
}

// 广播消息给所有连接
function broadcastMessage($message, $connections) {
    $data = json_encode([
        'type' => 'broadcast',
        'message' => $message,
        'timestamp' => time()
    ]);
    
    foreach ($connections as $connection) {
        try {
            $connection->write($data);
        } catch (Exception $e) {
            // 移除断开的连接
            $connections->detach($connection);
        }
    }
}

// 定时广播系统状态
$loop->addPeriodicTimer(30, function() use (&$connections) {
    $status = [
        'type' => 'system_status',
        'data' => [
            'memory_usage' => memory_get_usage(true),
            'connection_count' => count($connections),
            'timestamp' => time()
        ]
    ];
    
    broadcastMessage($status, $connections);
    echo "[" . date('Y-m-d H:i:s') . "] 系统状态广播发送给 " . count($connections) . " 个连接\n";
});

// 启动服务器
echo "服务器正在运行...\n";
$loop->run();
