<?php
require_once 'vendor/autoload.php';

use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\RFC6455\Handshake\ServerNegotiator;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\CloseFrameChecker;
use React\Stream\WritableResourceStream;

class WebSocketServer
{
    private $loop;
    private $clients = [];
    private $logger;
    
    public function __init()
    {
        $this->loop = Loop::get();
        $this->logger = $this->createLogger();
    }
    
    private function createLogger()
    {
        $logFile = __DIR__ . '/storage/websocket.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        return function($message) use ($logFile) {
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
            echo "[$timestamp] $message\n";
        };
    }
    
    public function start($port = 8080)
    {
        $this->__init();
        
        $socket = new SocketServer("0.0.0.0:$port", [], $this->loop);
        
        $http = new HttpServer($this->loop, function (ServerRequestInterface $request) {
            $path = $request->getUri()->getPath();
            
            // WebSocket 升级处理
            if ($path === '/ws' || $path === '/websocket') {
                return $this->handleWebSocketUpgrade($request);
            }
            
            // HTTP 状态页面
            if ($path === '/status') {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'status' => 'running',
                    'clients' => count($this->clients),
                    'timestamp' => date('c'),
                    'server' => 'ReactPHP WebSocket Server'
                ]));
            }
            
            return new Response(404, [], 'Not Found');
        });
        
        $http->listen($socket);
        
        ($this->logger)("WebSocket服务器启动在端口 $port");
        ($this->logger)("访问 http://127.0.0.1:$port/status 查看状态");
        ($this->logger)("WebSocket连接地址: ws://127.0.0.1:$port/ws");
        
        // 保存进程ID
        $pidFile = __DIR__ . '/storage/websocket.pid';
        file_put_contents($pidFile, getmypid());
        
        $this->loop->run();
    }
    
    private function handleWebSocketUpgrade(ServerRequestInterface $request)
    {
        $negotiator = new ServerNegotiator();
        
        try {
            $response = $negotiator->handshake($request);
            
            // 如果握手失败
            if ($response->getStatusCode() !== 101) {
                ($this->logger)("WebSocket握手失败: " . $response->getStatusCode());
                return $response;
            }
            
            ($this->logger)("WebSocket握手成功");
            
            // 这里需要处理连接升级
            // 由于ReactPHP HTTP服务器的限制，我们需要使用不同的方法
            return $response;
            
        } catch (Exception $e) {
            ($this->logger)("WebSocket握手异常: " . $e->getMessage());
            return new Response(400, [], 'Bad Request');
        }
    }
}

// 启动服务器
$server = new WebSocketServer();
$server->start(8080);
