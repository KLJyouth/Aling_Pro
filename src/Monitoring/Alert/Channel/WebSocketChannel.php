<?php
namespace AlingAi\Monitoring\Alert\Channel;

use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

/**
 * WebSocket告警通道 - 向前端实时推送告警信息
 */
class WebSocketChannel implements AlertChannelInterface, MessageComponentInterface
{
    /**
     * @var array 配置
     */
    private $config;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var \SplObjectStorage 已连接的客户端
     */
    private $clients;
    
    /**
     * @var IoServer WebSocket服务器
     */
    private $server;
    
    /**
     * @var bool 服务器是否运行
     */
    private $isRunning = false;

    /**
     * 构造函数
     */
    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->clients = new \SplObjectStorage();
    }

    /**
     * 启动WebSocket服务器
     */
    public function startServer(): void
    {
        if ($this->isRunning) {
            return;
        }
        
        $port = $this->config['port'] ?? 8080;
        $host = $this->config['host'] ?? '0.0.0.0';
        
        try {
            $this->server = IoServer::factory(
                new HttpServer(
                    new WsServer($this)
                ),
                $port,
                $host
            );
            
            $this->logger->info("WebSocket告警服务器已启动", [
                'host' => $host,
                'port' => $port,
            ]);
            
            $this->isRunning = true;
            
            // 在后台线程中运行服务器
            if (function_exists('pcntl_fork')) {
                $pid = pcntl_fork();
                
                if ($pid == -1) {
                    $this->logger->error("无法派生WebSocket服务器进程");
                } elseif ($pid) {
                    // 父进程
                    $this->logger->info("WebSocket服务器已在后台启动，PID: $pid");
                } else {
                    // 子进程
                    posix_setsid();
                    $this->server->run();
                    exit(0);
                }
            } else {
                // 不支持pcntl，直接运行(会阻塞当前进程)
                $this->logger->warning("pcntl扩展未启用，WebSocket服务器将在主线程中运行");
                $this->server->run();
            }
        } catch (\Exception $e) {
            $this->logger->error("启动WebSocket服务器失败", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 停止WebSocket服务器
     */
    public function stopServer(): void
    {
        if (!$this->isRunning || !$this->server) {
            return;
        }
        
        try {
            $this->server->socket->close();
            $this->isRunning = false;
            $this->logger->info("WebSocket告警服务器已停止");
        } catch (\Exception $e) {
            $this->logger->error("停止WebSocket服务器失败", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send(array $alert): bool
    {
        if (!$this->isRunning) {
            $this->logger->warning("WebSocket服务器未运行，无法发送告警");
            return false;
        }
        
        try {
            $message = json_encode([
                'type' => 'alert',
                'data' => $alert,
            ]);
            
            $this->broadcast($message);
            
            $this->logger->info("WebSocket告警已发送", [
                'alert_id' => $alert['id'],
                'clients_count' => count($this->clients),
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error("WebSocket发送告警失败", [
                'alert_id' => $alert['id'],
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendResolution(array $alert): bool
    {
        if (!$this->isRunning) {
            return false;
        }
        
        try {
            $message = json_encode([
                'type' => 'alert_resolved',
                'data' => $alert,
            ]);
            
            $this->broadcast($message);
            
            $this->logger->info("WebSocket告警解决通知已发送", [
                'alert_id' => $alert['id'],
                'clients_count' => count($this->clients),
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error("WebSocket发送告警解决通知失败", [
                'alert_id' => $alert['id'],
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * 广播消息给所有连接的客户端
     */
    private function broadcast(string $message): void
    {
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    /**
     * 当WebSocket客户端连接时调用
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        
        $this->logger->info("新WebSocket客户端已连接", [
            'connection_id' => $conn->resourceId,
            'clients_count' => count($this->clients),
        ]);
    }

    /**
     * 当WebSocket客户端发送消息时调用
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $this->logger->debug("收到WebSocket消息", [
            'connection_id' => $from->resourceId,
            'message' => $msg,
        ]);
        
        // 处理客户端消息
        // 例如，客户端可以订阅特定类型的告警
        try {
            $data = json_decode($msg, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => '无效的JSON格式',
                ]));
                return;
            }
            
            switch ($data['type'] ?? '') {
                case 'subscribe':
                    // 处理订阅请求
                    $from->send(json_encode([
                        'type' => 'subscribed',
                        'message' => '订阅成功',
                        'data' => $data['data'] ?? [],
                    ]));
                    break;
                    
                case 'ping':
                    // 心跳检测
                    $from->send(json_encode([
                        'type' => 'pong',
                        'time' => time(),
                    ]));
                    break;
                    
                default:
                    $from->send(json_encode([
                        'type' => 'error',
                        'message' => '未知的消息类型',
                    ]));
                    break;
            }
        } catch (\Exception $e) {
            $this->logger->error("处理WebSocket消息失败", [
                'connection_id' => $from->resourceId,
                'error' => $e->getMessage(),
            ]);
            
            $from->send(json_encode([
                'type' => 'error',
                'message' => '处理消息时发生错误',
            ]));
        }
    }

    /**
     * 当WebSocket客户端关闭连接时调用
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        
        $this->logger->info("WebSocket客户端已断开连接", [
            'connection_id' => $conn->resourceId,
            'clients_count' => count($this->clients),
        ]);
    }

    /**
     * 当WebSocket连接发生错误时调用
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $this->logger->error("WebSocket连接发生错误", [
            'connection_id' => $conn->resourceId,
            'error' => $e->getMessage(),
        ]);
        
        $conn->close();
    }
} 