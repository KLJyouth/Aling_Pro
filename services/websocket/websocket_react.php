<?php
/**
 * ReactPHP WebSocket服务器
 * 使用ReactPHP提供稳定的WebSocket支持
 */

require_once __DIR__ . '/vendor/autoload.php';

use React\Socket\SocketServer;
use React\Stream\WritableResourceStream;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;

// 读取环境配置
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// WebSocket配置
$host = $_ENV['WEBSOCKET_HOST'] ?? '127.0.0.1';
$port = $_ENV['WEBSOCKET_PORT'] ?? '8080';

echo "==================================================\n";
echo "AlingAi Pro ReactPHP WebSocket服务器\n";
echo "==================================================\n";
echo "启动地址: ws://{$host}:{$port}\n";
echo "启动时间: " . date('Y-m-d H:i:s') . "\n";
echo "支持端点:\n";
echo "  - /ws/chat (聊天消息)\n";
echo "  - /ws/notifications (系统通知)\n";
echo "  - /ws/monitoring (系统监控)\n";
echo "==================================================\n";

// 存储活跃连接
$connections = [];
$lastPing = time();

// 消息处理函数
function processMessage($data, $connectionId, &$connections) {
    $message = json_decode($data, true);
    if (!$message) {
        return ['type' => 'error', 'message' => 'Invalid JSON'];
    }

    $response = ['timestamp' => time()];
    
    switch ($message['type'] ?? '') {
        case 'ping':
            $response['type'] = 'pong';
            $response['message'] = 'Server is alive';
            break;
            
        case 'chat':
            $response['type'] = 'chat_response';
            $response['message'] = '收到聊天消息: ' . ($message['content'] ?? '');
            $response['user'] = $message['user'] ?? 'anonymous';
            
            // 广播给所有连接的客户端
            broadcastToAll($connections, $response, $connectionId);
            return null; // 已经广播，不需要单独回复
            
        case 'notification':
            $response['type'] = 'notification_received';
            $response['message'] = '系统通知已处理';
            break;
            
        case 'quantum_interaction':
            $response['type'] = 'quantum_response';
            $response['message'] = '量子球交互已处理';
            $response['effect'] = $message['effect'] ?? 'glow';
            break;
            
        default:
            $response['type'] = 'echo';
            $response['message'] = 'Echo: ' . ($message['content'] ?? 'No content');
    }
    
    return $response;
}

// 广播消息给所有客户端
function broadcastToAll($connections, $message, $excludeId = null) {
    $jsonMessage = json_encode($message);
    foreach ($connections as $id => $connection) {
        if ($id !== $excludeId && isset($connection['stream'])) {
            try {
                $connection['stream']->write($jsonMessage . "\n");
            } catch (Exception $e) {
                echo "广播失败 (连接 {$id}): " . $e->getMessage() . "\n";
                unset($connections[$id]);
            }
        }
    }
}

// 发送心跳
function sendHeartbeat($connections) {
    $heartbeat = json_encode([
        'type' => 'heartbeat',
        'timestamp' => time(),
        'connections' => count($connections)
    ]);
    
    foreach ($connections as $id => $connection) {
        if (isset($connection['stream'])) {
            try {
                $connection['stream']->write($heartbeat . "\n");
            } catch (Exception $e) {
                echo "心跳发送失败 (连接 {$id}): " . $e->getMessage() . "\n";
                unset($connections[$id]);
            }
        }
    }
}

// WebSocket握手处理
function handleWebSocketUpgrade(ServerRequestInterface $request) {
    $key = '';
    $upgrade = '';
    $connection = '';
    
    foreach ($request->getHeaders() as $name => $values) {
        $lowerName = strtolower($name);
        if ($lowerName === 'sec-websocket-key') {
            $key = $values[0];
        } elseif ($lowerName === 'upgrade') {
            $upgrade = $values[0];
        } elseif ($lowerName === 'connection') {
            $connection = $values[0];
        }
    }
    
    if (empty($key) || strtolower($upgrade) !== 'websocket' || 
        strpos(strtolower($connection), 'upgrade') === false) {
        return new Response(400, [], 'Bad Request - Invalid WebSocket upgrade');
    }
    
    $accept = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    
    return new Response(101, [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade', 
        'Sec-WebSocket-Accept' => $accept,
        'Sec-WebSocket-Version' => '13'
    ], '');
}

// 创建HTTP服务器处理WebSocket升级
$http = new HttpServer(function (ServerRequestInterface $request) use (&$connections) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    
    // 检查是否是WebSocket升级请求
    $upgrade = $request->getHeaderLine('Upgrade');
    $connection = $request->getHeaderLine('Connection');
    
    if (strtolower($upgrade) === 'websocket' && 
        strpos(strtolower($connection), 'upgrade') !== false) {
        
        echo "WebSocket连接请求: {$path}\n";
        return handleWebSocketUpgrade($request);
    }
    
    // 普通HTTP请求 - 返回状态页面
    if ($path === '/' || $path === '/status') {
        $status = [
            'server' => 'AlingAi Pro WebSocket Server',
            'status' => 'running',
            'connections' => count($connections),
            'uptime' => time() - $GLOBALS['startTime'],
            'endpoints' => [
                '/ws/chat',
                '/ws/notifications', 
                '/ws/monitoring'
            ]
        ];
        
        return new Response(200, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ], json_encode($status, JSON_PRETTY_PRINT));
    }
    
    return new Response(404, [], 'Not Found');
});

// 创建socket服务器
try {
    $socket = new SocketServer("{$host}:{$port}");
    $http->listen($socket);
    
    echo "✓ WebSocket服务器启动成功！\n";
    echo "连接地址: ws://{$host}:{$port}/ws\n";
    echo "状态页面: http://{$host}:{$port}/status\n";
    echo "按 Ctrl+C 停止服务器\n\n";
    
    $GLOBALS['startTime'] = time();
    
    // 处理连接
    $socket->on('connection', function ($stream) use (&$connections) {
        $connectionId = uniqid();
        $remoteAddress = $stream->getRemoteAddress();
        
        echo "新连接: {$connectionId} 来自 {$remoteAddress}\n";
        
        $connections[$connectionId] = [
            'stream' => $stream,
            'address' => $remoteAddress,
            'connected_at' => time()
        ];
        
        // 发送欢迎消息
        $welcome = json_encode([
            'type' => 'welcome',
            'message' => 'WebSocket连接已建立',
            'connection_id' => $connectionId,
            'server_time' => date('Y-m-d H:i:s')
        ]);
        $stream->write($welcome . "\n");
        
        // 处理接收到的数据
        $stream->on('data', function ($data) use ($connectionId, &$connections) {
            $data = trim($data);
            if (empty($data)) return;
            
            echo "收到消息 ({$connectionId}): {$data}\n";
            
            $response = processMessage($data, $connectionId, $connections);
            if ($response && isset($connections[$connectionId]['stream'])) {
                $connections[$connectionId]['stream']->write(json_encode($response) . "\n");
            }
        });
        
        // 处理连接关闭
        $stream->on('close', function () use ($connectionId, &$connections) {
            echo "连接关闭: {$connectionId}\n";
            unset($connections[$connectionId]);
        });
        
        $stream->on('error', function ($error) use ($connectionId, &$connections) {
            echo "连接错误 ({$connectionId}): {$error->getMessage()}\n";
            unset($connections[$connectionId]);
        });
    });
    
    // 心跳定时器
    $loop = \React\EventLoop\Factory::create();
    $loop->addPeriodicTimer(30, function () use (&$connections, &$lastPing) {
        echo "发送心跳包 (活跃连接: " . count($connections) . ")\n";
        sendHeartbeat($connections);
        $lastPing = time();
    });
    
    // 保存PID
    $pidFile = __DIR__ . '/storage/websocket.pid';
    file_put_contents($pidFile, getmypid());
    
    echo "WebSocket服务器运行中... (PID: " . getmypid() . ")\n\n";
    
} catch (Exception $e) {
    echo "❌ 服务器启动失败: " . $e->getMessage() . "\n";
    exit(1);
}
