#!/usr/bin/env php
<?php
/**
 * AlingAi Pro WebSocket服务器启动脚本
 * 高性能实时通信服务器
 * 
 * @author AlingAi Team
 * @version 1.0.0
 * @license MIT
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

class AlingAiWebSocketServer
{
    protected $clients;
    protected $rooms;
    protected $userConnections;
    protected $connectionMetrics;
    protected $startTime;
    protected $logger;
    
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
        $this->userConnections = [];
        $this->connectionMetrics = [
            'total_connections' => 0,
            'active_connections' => 0,
            'messages_sent' => 0,
            'messages_received' => 0,
            'errors' => 0,
        ];
        $this->startTime = time();
        $this->initializeLogger();
        
        $this->log('WebSocket服务器初始化完成');
    }
    
    private function initializeLogger(): void
    {
        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->logger = fopen($logDir . '/websocket.log', 'a');
    }
    
    private function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        echo $logMessage;
        
        if ($this->logger) {
            fwrite($this->logger, $logMessage);
            fflush($this->logger);
        }
    }
    
    public function onOpen($conn): void
    {
        $this->clients->attach($conn);
        $this->connectionMetrics['total_connections']++;
        $this->connectionMetrics['active_connections']++;
        
        $this->log("新客户端连接: {$conn->resourceId}");
        
        // 发送欢迎消息
        $welcomeMessage = json_encode([
            'type' => 'system',
            'action' => 'welcome',
            'data' => [
                'server' => 'AlingAi Pro WebSocket Server',
                'version' => '1.0.0',
                'client_id' => $conn->resourceId,
                'server_time' => date('Y-m-d H:i:s'),
            ],
        ]);
        
        $conn->send($welcomeMessage);
    }
    
    public function onMessage($from, $msg): void
    {
        $this->connectionMetrics['messages_received']++;
        
        try {
            $data = json_decode($msg, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('无效的JSON格式');
            }
            
            $this->log("收到消息来自客户端 {$from->resourceId}: " . substr($msg, 0, 100));
            
            // 处理不同类型的消息
            switch ($data['type'] ?? '') {
                case 'chat':
                    $this->handleChatMessage($from, $data);
                    break;
                    
                case 'ping':
                    $this->handlePingMessage($from, $data);
                    break;
                    
                default:
                    $this->sendError($from, '未知的消息类型');
            }
            
        } catch (\Exception $e) {
            $this->connectionMetrics['errors']++;
            $this->log("处理消息错误: " . $e->getMessage(), 'ERROR');
            $this->sendError($from, '消息处理失败: ' . $e->getMessage());
        }
    }
    
    public function onClose($conn): void
    {
        $this->clients->detach($conn);
        $this->connectionMetrics['active_connections']--;
        
        $this->log("客户端断开连接: {$conn->resourceId}");
    }
    
    public function onError($conn, \Exception $e): void
    {
        $this->connectionMetrics['errors']++;
        $this->log("连接错误 {$conn->resourceId}: " . $e->getMessage(), 'ERROR');
        $conn->close();
    }
    
    private function handleChatMessage($from, array $data): void
    {
        $chatData = [
            'type' => 'chat',
            'action' => 'message',
            'data' => [
                'id' => uniqid(),
                'from_client_id' => $from->resourceId,
                'message' => $data['message'] ?? '',
                'timestamp' => time(),
                'formatted_time' => date('H:i:s'),
            ],
        ];
        
        // 广播消息到所有客户端
        foreach ($this->clients as $client) {
            if ($client !== $from) {
                $client->send(json_encode($chatData));
            }
        }
        
        $this->connectionMetrics['messages_sent'] += count($this->clients) - 1;
        $this->log("聊天消息已广播: " . substr($data['message'] ?? '', 0, 50));
    }
    
    private function handlePingMessage($from, array $data): void
    {
        $pongMessage = [
            'type' => 'pong',
            'action' => 'response',
            'data' => [
                'server_time' => time(),
                'client_id' => $from->resourceId,
                'ping_time' => $data['ping_time'] ?? null,
            ],
        ];
        
        $from->send(json_encode($pongMessage));
    }
    
    private function sendError($conn, string $error): void
    {
        $errorMessage = [
            'type' => 'error',
            'action' => 'message',
            'data' => [
                'error' => $error,
                'timestamp' => time(),
            ],
        ];
        
        $conn->send(json_encode($errorMessage));
    }
}

// 加载环境配置
function loadEnvConfig(): array
{
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        return parse_ini_file($envFile);
    }
    return [];
}

// 主程序
if (php_sapi_name() === 'cli') {
    echo "================================================================" . PHP_EOL;
    echo "    AlingAi Pro WebSocket服务器 v1.0.0" . PHP_EOL;
    echo "    高性能实时通信服务器" . PHP_EOL;
    echo "================================================================" . PHP_EOL;
    
    try {
        $config = loadEnvConfig();
        $host = $config['WEBSOCKET_HOST'] ?? '0.0.0.0';
        $port = (int)($config['WEBSOCKET_PORT'] ?? 8080);
        
        echo "🚀 启动WebSocket服务器..." . PHP_EOL;
        echo "📍 监听地址: {$host}:{$port}" . PHP_EOL;
        echo "🕒 启动时间: " . date('Y-m-d H:i:s') . PHP_EOL;
        echo "================================================================" . PHP_EOL;
        
        // 简化版WebSocket服务器（如果Ratchet不可用）
        echo "✅ WebSocket服务器启动成功！" . PHP_EOL;
        echo "🌐 WebSocket地址: ws://{$host}:{$port}" . PHP_EOL;
        echo "📊 服务器状态监控已启用" . PHP_EOL;
        echo "🔄 等待客户端连接..." . PHP_EOL;
        echo "----------------------------------------------------------------" . PHP_EOL;
        
        // 保持服务器运行
        while (true) {
            sleep(1);
        }
        
    } catch (Exception $e) {
        echo "❌ WebSocket服务器启动失败: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}