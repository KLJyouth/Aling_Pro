<?php
/**
 * AlingAi Pro 5.0 - 简化WebSocket服务器
 * 原生PHP实现的WebSocket服务器，用于Admin系统实时数据推送
 */

class SimpleWebSocketServer
{
    private $socket;
    private $clients = [];
    private $adminClients = [];
    private $host;
    private $port;
    private $running = false;
    private $pdo;
    
    public function __construct(string $host = '127.0.0.1', int $port = 8080)
    {
        $this->host = $host;
        $this->port = $port;
        $this->connectDatabase();
    }
    
    /**
     * 连接数据库
     */
    private function connectDatabase(): void
    {
        try {
            $dsn = "sqlite:" . dirname(__DIR__) . "/database/admin.db";
            $this->pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            $this->log("❌ Database connection error: " . $e->getMessage());
            $this->pdo = null;
        }
    }
    
    /**
     * 启动WebSocket服务器
     */
    public function start(): bool
    {
        // 创建socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if (!$this->socket) {
            $this->log("❌ Failed to create socket: " . socket_strerror(socket_last_error()));
            return false;
        }
        
        // 设置socket选项
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        // 绑定地址和端口
        if (!socket_bind($this->socket, $this->host, $this->port)) {
            $this->log("❌ Failed to bind socket: " . socket_strerror(socket_last_error()));
            return false;
        }
        
        // 开始监听
        if (!socket_listen($this->socket, 5)) {
            $this->log("❌ Failed to listen on socket: " . socket_strerror(socket_last_error()));
            return false;
        }
        
        $this->running = true;
        $this->log("🚀 WebSocket Server started on {$this->host}:{$this->port}");
        
        // 主循环
        while ($this->running) {
            $this->mainLoop();
            usleep(100000); // 0.1秒
        }
        
        $this->cleanup();
        return true;
    }
    
    /**
     * 主循环处理连接
     */
    private function mainLoop(): void
    {
        $read = array_merge([$this->socket], $this->clients);
        $write = null;
        $except = null;
        
        $num = socket_select($read, $write, $except, 0, 10000);
        
        if ($num === false) {
            $this->log("❌ Socket select failed");
            return;
        }
        
        if ($num > 0) {
            // 检查新连接
            if (in_array($this->socket, $read)) {
                $this->acceptNewConnection();
                $key = array_search($this->socket, $read);
                unset($read[$key]);
            }
            
            // 处理现有连接的消息
            foreach ($read as $client) {
                $this->handleClientMessage($client);
            }
        }
        
        // 定期发送系统状态更新
        static $lastUpdate = 0;
        if (time() - $lastUpdate > 5) { // 每5秒更新一次
            $this->broadcastSystemUpdate();
            $lastUpdate = time();
        }
    }
    
    /**
     * 接受新连接
     */
    private function acceptNewConnection(): void
    {
        $newClient = socket_accept($this->socket);
        
        if ($newClient === false) {
            $this->log("❌ Failed to accept connection");
            return;
        }
        
        // 执行WebSocket握手
        if ($this->performHandshake($newClient)) {
            $this->clients[] = $newClient;
            $this->log("✅ New client connected. Total clients: " . count($this->clients));
            
            // 发送欢迎消息
            $this->sendMessage($newClient, [
                'type' => 'connection',
                'message' => 'Connected to AlingAi Pro Admin WebSocket Server',
                'timestamp' => time(),
                'client_count' => count($this->clients)
            ]);
        } else {
            socket_close($newClient);
        }
    }
    
    /**
     * WebSocket握手
     */
    private function performHandshake($client): bool
    {
        $request = socket_read($client, 5000);
        
        if (empty($request)) {
            return false;
        }
        
        // 解析WebSocket握手请求
        preg_match('/Sec-WebSocket-Key: (.*)\r\n/', $request, $matches);
        
        if (empty($matches[1])) {
            return false;
        }
        
        $key = trim($matches[1]);
        $acceptKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        
        $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
        
        socket_write($client, $response, strlen($response));
        
        return true;
    }
    
    /**
     * 处理客户端消息
     */
    private function handleClientMessage($client): void
    {
        $data = socket_read($client, 1024, PHP_NORMAL_READ);
        
        if ($data === false || $data === '') {
            $this->disconnectClient($client);
            return;
        }
        
        // 解码WebSocket帧
        $message = $this->decodeFrame($data);
        
        if ($message === false) {
            return;
        }
        
        $this->log("📨 Received message: " . $message);
        
        // 解析JSON消息
        $messageData = json_decode($message, true);
        
        if ($messageData) {
            $this->processMessage($client, $messageData);
        }
    }
    
    /**
     * 处理消息
     */
    private function processMessage($client, array $messageData): void
    {
        switch ($messageData['type'] ?? '') {
            case 'ping':
                $this->sendMessage($client, [
                    'type' => 'pong',
                    'timestamp' => time()
                ]);
                break;
                
            case 'subscribe':
                $this->handleSubscription($client, $messageData);
                break;
                
            case 'request_data':
                $this->handleDataRequest($client, $messageData);
                break;
                
            default:
                $this->sendMessage($client, [
                    'type' => 'error',
                    'message' => 'Unknown message type',
                    'timestamp' => time()
                ]);
                break;
        }
    }
    
    /**
     * 处理订阅请求
     */
    private function handleSubscription($client, array $messageData): void
    {
        $channel = $messageData['channel'] ?? '';
        
        if (empty($channel)) {
            $this->sendMessage($client, [
                'type' => 'error',
                'message' => 'Channel not specified',
                'timestamp' => time()
            ]);
            return;
        }
        
        // 将客户端添加到订阅列表
        if (!isset($this->adminClients[$channel])) {
            $this->adminClients[$channel] = [];
        }
        
        if (!in_array($client, $this->adminClients[$channel])) {
            $this->adminClients[$channel][] = $client;
        }
        
        $this->sendMessage($client, [
            'type' => 'subscribed',
            'channel' => $channel,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 处理数据请求
     */
    private function handleDataRequest($client, array $messageData): void
    {
        $type = $messageData['data_type'] ?? '';
        
        switch ($type) {
            case 'system_stats':
                $this->sendSystemStats($client);
                break;
                
            case 'user_stats':
                $this->sendUserStats($client);
                break;
                
            case 'api_stats':
                $this->sendApiStats($client);
                break;
                
            default:
                $this->sendMessage($client, [
                    'type' => 'error',
                    'message' => 'Unknown data type',
                    'timestamp' => time()
                ]);
                break;
        }
    }
    
    /**
     * 发送系统统计信息
     */
    private function sendSystemStats($client): void
    {
        $stats = $this->getSystemStats();
        
        $this->sendMessage($client, [
            'type' => 'system_stats',
            'data' => $stats,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 发送用户统计信息
     */
    private function sendUserStats($client): void
    {
        $stats = $this->getUserStats();
        
        $this->sendMessage($client, [
            'type' => 'user_stats',
            'data' => $stats,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 发送API统计信息
     */
    private function sendApiStats($client): void
    {
        $stats = $this->getApiStats();
        
        $this->sendMessage($client, [
            'type' => 'api_stats',
            'data' => $stats,
            'timestamp' => time()
        ]);
    }
    
    /**
     * 获取系统统计信息
     */
    private function getSystemStats(): array
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM system_stats ORDER BY id DESC LIMIT 1");
                if ($row = $stmt->fetch()) {
                    return $row;
                }
            } catch (PDOException $e) {
                $this->log("❌ Error getting system stats: " . $e->getMessage());
            }
        }
        
        // 返回模拟数据
        return [
            'cpu_usage' => rand(10, 90),
            'memory_usage' => rand(20, 80),
            'disk_usage' => rand(30, 70),
            'network_traffic' => rand(1000, 5000)
        ];
    }
    
    /**
     * 获取用户统计信息
     */
    private function getUserStats(): array
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM user_stats ORDER BY id DESC LIMIT 1");
                if ($row = $stmt->fetch()) {
                    return $row;
                }
            } catch (PDOException $e) {
                $this->log("❌ Error getting user stats: " . $e->getMessage());
            }
        }
        
        // 返回模拟数据
        return [
            'total_users' => rand(100, 1000),
            'active_users' => rand(50, 200),
            'new_users_today' => rand(5, 50)
        ];
    }
    
    /**
     * 获取API统计信息
     */
    private function getApiStats(): array
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM api_stats ORDER BY id DESC LIMIT 1");
                if ($row = $stmt->fetch()) {
                    return $row;
                }
            } catch (PDOException $e) {
                $this->log("❌ Error getting API stats: " . $e->getMessage());
            }
        }
        
        // 返回模拟数据
        return [
            'total_requests' => rand(1000, 10000),
            'success_rate' => rand(95, 99),
            'average_response_time' => rand(50, 200)
        ];
    }
    
    /**
     * 广播系统更新
     */
    private function broadcastSystemUpdate(): void
    {
        $update = [
            'type' => 'system_update',
            'data' => [
                'system' => $this->getSystemStats(),
                'users' => $this->getUserStats(),
                'api' => $this->getApiStats()
            ],
            'timestamp' => time()
        ];
        
        $this->broadcast($update);
    }
    
    /**
     * 广播消息给所有客户端
     */
    private function broadcast(array $message): void
    {
        foreach ($this->clients as $client) {
            $this->sendMessage($client, $message);
        }
    }
    
    /**
     * 发送消息给指定客户端
     */
    private function sendMessage($client, array $message): void
    {
        $frame = $this->encodeFrame(json_encode($message));
        socket_write($client, $frame, strlen($frame));
    }
    
    /**
     * 编码WebSocket帧
     */
    private function encodeFrame(string $message): string
    {
        $length = strlen($message);
        
        if ($length <= 125) {
            $header = chr(129) . chr($length);
        } elseif ($length <= 65535) {
            $header = chr(129) . chr(126) . pack('n', $length);
        } else {
            $header = chr(129) . chr(127) . pack('J', $length);
        }
        
        return $header . $message;
    }
    
    /**
     * 解码WebSocket帧
     */
    private function decodeFrame(string $data): string|false
    {
        if (strlen($data) < 2) {
            return false;
        }
        
        $firstByte = ord($data[0]);
        $secondByte = ord($data[1]);
        
        $fin = ($firstByte & 0x80) != 0;
        $opcode = $firstByte & 0x0F;
        $masked = ($secondByte & 0x80) != 0;
        $length = $secondByte & 0x7F;
        
        $headerLength = 2;
        
        if ($length == 126) {
            if (strlen($data) < 4) {
                return false;
            }
            $length = unpack('n', substr($data, 2, 2))[1];
            $headerLength = 4;
        } elseif ($length == 127) {
            if (strlen($data) < 10) {
                return false;
            }
            $length = unpack('J', substr($data, 2, 8))[1];
            $headerLength = 10;
        }
        
        if ($masked) {
            if (strlen($data) < $headerLength + 4) {
                return false;
            }
            $mask = substr($data, $headerLength, 4);
            $headerLength += 4;
        }
        
        if (strlen($data) < $headerLength + $length) {
            return false;
        }
        
        $payload = substr($data, $headerLength, $length);
        
        if ($masked) {
            $unmasked = '';
            for ($i = 0; $i < $length; $i++) {
                $unmasked .= chr(ord($payload[$i]) ^ ord($mask[$i % 4]));
            }
            $payload = $unmasked;
        }
        
        return $payload;
    }
    
    /**
     * 断开客户端连接
     */
    private function disconnectClient($client): void
    {
        $key = array_search($client, $this->clients);
        if ($key !== false) {
            unset($this->clients[$key]);
        }
        
        foreach ($this->adminClients as $channel => $clients) {
            $key = array_search($client, $clients);
        if ($key !== false) {
                unset($this->adminClients[$channel][$key]);
            }
        }
        
        socket_close($client);
        $this->log("👋 Client disconnected. Total clients: " . count($this->clients));
    }
    
    /**
     * 清理资源
     */
    private function cleanup(): void
    {
        foreach ($this->clients as $client) {
            socket_close($client);
        }
        
        if ($this->socket) {
            socket_close($this->socket);
        }
        
        $this->clients = [];
        $this->adminClients = [];
        $this->running = false;
        
        $this->log("🧹 Server cleaned up");
    }
    
    /**
     * 停止服务器
     */
    public function stop(): void
    {
        $this->running = false;
        $this->log("🛑 Server stopping...");
    }
    
    /**
     * 记录日志
     */
    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[$timestamp] $message\n";
    }
}

// 注册信号处理器
pcntl_signal(SIGINT, function($signal) {
    global $server;
    if ($server) {
        $server->stop();
    }
});

// 创建并启动服务器
$server = new SimpleWebSocketServer();
$server->start();
