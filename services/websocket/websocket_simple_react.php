<?php
require_once 'vendor/autoload.php';

use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Stream\WritableResourceStream;

class SimpleWebSocketServer
{
    private $loop;
    private $clients = [];
    private $logger;
    
    public function __construct()
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
        $socket = new SocketServer("0.0.0.0:$port", [], $this->loop);
        
        $socket->on('connection', function ($connection) {
            $clientId = uniqid();
            ($this->logger)("新连接: $clientId");
            
            $connection->on('data', function ($data) use ($connection, $clientId) {
                $this->handleData($connection, $data, $clientId);
            });
            
            $connection->on('close', function () use ($clientId) {
                ($this->logger)("连接关闭: $clientId");
                unset($this->clients[$clientId]);
            });
            
            $connection->on('error', function ($error) use ($clientId) {
                ($this->logger)("连接错误 $clientId: " . $error->getMessage());
                unset($this->clients[$clientId]);
            });
        });
        
        ($this->logger)("WebSocket服务器启动在端口 $port");
        
        // 保存进程ID
        $pidFile = __DIR__ . '/storage/websocket.pid';
        file_put_contents($pidFile, getmypid());
        
        $this->loop->run();
    }
    
    private function handleData($connection, $data, $clientId)
    {
        // 检查是否是 HTTP 请求 (WebSocket 握手)
        if (strpos($data, 'GET ') === 0) {
            $this->handleWebSocketHandshake($connection, $data, $clientId);
            return;
        }
        
        // 处理 WebSocket 帧
        $this->handleWebSocketFrame($connection, $data, $clientId);
    }
    
    private function handleWebSocketHandshake($connection, $data, $clientId)
    {
        // 解析 WebSocket 握手
        $lines = explode("\r\n", $data);
        $headers = [];
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim(strtolower($key))] = trim($value);
            }
        }
        
        if (!isset($headers['sec-websocket-key'])) {
            $connection->close();
            return;
        }
        
        $key = $headers['sec-websocket-key'];
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        
        $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: $acceptKey\r\n" .
                   "\r\n";
        
        $connection->write($response);
        
        $this->clients[$clientId] = $connection;
        ($this->logger)("WebSocket握手成功: $clientId");
        
        // 发送欢迎消息
        $this->sendMessage($connection, json_encode([
            'type' => 'system',
            'message' => 'WebSocket连接成功',
            'clientId' => $clientId,
            'timestamp' => time()
        ]));
    }
    
    private function handleWebSocketFrame($connection, $data, $clientId)
    {
        if (strlen($data) < 2) {
            return;
        }
        
        $firstByte = ord($data[0]);
        $secondByte = ord($data[1]);
        
        $fin = ($firstByte >> 7) & 1;
        $opcode = $firstByte & 0x0F;
        $masked = ($secondByte >> 7) & 1;
        $payloadLength = $secondByte & 0x7F;
        
        $offset = 2;
        
        if ($payloadLength == 126) {
            $payloadLength = unpack('n', substr($data, $offset, 2))[1];
            $offset += 2;
        } elseif ($payloadLength == 127) {
            $payloadLength = unpack('J', substr($data, $offset, 8))[1];
            $offset += 8;
        }
        
        if ($masked) {
            $maskingKey = substr($data, $offset, 4);
            $offset += 4;
        }
        
        $payload = substr($data, $offset, $payloadLength);
        
        if ($masked) {
            for ($i = 0; $i < strlen($payload); $i++) {
                $payload[$i] = chr(ord($payload[$i]) ^ ord($maskingKey[$i % 4]));
            }
        }
        
        // 处理不同类型的帧
        switch ($opcode) {
            case 0x1: // 文本帧
                $this->handleTextMessage($connection, $payload, $clientId);
                break;
            case 0x8: // 关闭帧
                $connection->close();
                break;
            case 0x9: // Ping 帧
                $this->sendPong($connection, $payload);
                break;
            case 0xA: // Pong 帧
                ($this->logger)("收到Pong: $clientId");
                break;
        }
    }
    
    private function handleTextMessage($connection, $message, $clientId)
    {
        ($this->logger)("收到消息 ($clientId): $message");
        
        try {
            $data = json_decode($message, true);
            if (!$data) {
                $data = ['type' => 'text', 'message' => $message];
            }
            
            switch ($data['type'] ?? 'text') {
                case 'ping':
                    $this->sendMessage($connection, json_encode([
                        'type' => 'pong',
                        'timestamp' => time()
                    ]));
                    break;
                    
                case 'chat':
                    // 广播聊天消息给所有客户端
                    $this->broadcast(json_encode([
                        'type' => 'chat',
                        'message' => $data['message'] ?? '',
                        'sender' => $clientId,
                        'timestamp' => time()
                    ]), $clientId);
                    break;
                    
                default:
                    // 回显消息
                    $this->sendMessage($connection, json_encode([
                        'type' => 'echo',
                        'originalMessage' => $data,
                        'timestamp' => time()
                    ]));
                    break;
            }
        } catch (Exception $e) {
            ($this->logger)("处理消息错误: " . $e->getMessage());
        }
    }
    
    private function sendMessage($connection, $message)
    {
        $frame = $this->createFrame($message);
        $connection->write($frame);
    }
    
    private function sendPong($connection, $payload)
    {
        $frame = $this->createFrame($payload, 0xA);
        $connection->write($frame);
    }
    
    private function createFrame($payload, $opcode = 0x1)
    {
        $payloadLength = strlen($payload);
        $frame = chr(0x80 | $opcode); // FIN = 1, opcode
        
        if ($payloadLength < 126) {
            $frame .= chr($payloadLength);
        } elseif ($payloadLength < 65536) {
            $frame .= chr(126) . pack('n', $payloadLength);
        } else {
            $frame .= chr(127) . pack('J', $payloadLength);
        }
        
        $frame .= $payload;
        return $frame;
    }
    
    private function broadcast($message, $excludeClientId = null)
    {
        foreach ($this->clients as $clientId => $connection) {
            if ($clientId !== $excludeClientId) {
                $this->sendMessage($connection, $message);
            }
        }
    }
}

// 启动服务器
$server = new SimpleWebSocketServer();
$server->start(8080);
