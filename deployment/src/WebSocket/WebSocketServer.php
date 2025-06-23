<?php
/**
 * WebSocket服务器
 * 处理实时通信和量子动画同步
 */

namespace AlingAi\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use AlingAi\Services\AuthService;
use AlingAi\Services\ChatService;
use AlingAi\Services\CacheService;

class WebSocketServer implements MessageComponentInterface {
    
    private $connections;
    private $users;
    private $rooms;
    private $authService;
    private $chatService;
    private $cacheService;
    
    public function __construct() {
        $this->connections = new \SplObjectStorage();
        $this->users = [];
        $this->rooms = [];
        $this->authService = new AuthService();
        $this->chatService = new ChatService();
        $this->cacheService = new CacheService();
        
        echo "WebSocket服务器已启动...\n";
    }
    
    /**
     * 新连接建立
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->connections->attach($conn);
        
        // 从查询参数获取token
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $params);
        $token = $params['token'] ?? null;
        
        if ($token) {
            try {
                $user = $this->authService->validateToken($token);
                if ($user) {
                    $conn->user = $user;
                    $this->users[$user['id']] = $conn;
                    
                    $this->sendToConnection($conn, [
                        'type' => 'auth_success',
                        'user' => $user
                    ]);
                    
                    echo "用户 {$user['username']} 已连接\n";
                } else {
                    $this->sendToConnection($conn, [
                        'type' => 'auth_failed',
                        'message' => 'Token无效'
                    ]);
                }
            } catch (\Exception $e) {
                $this->sendToConnection($conn, [
                    'type' => 'auth_failed',
                    'message' => 'Token验证失败'
                ]);
            }
        }
        
        echo "新连接建立 ({$conn->resourceId})\n";
    }
    
    /**
     * 接收消息
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if (!$data || !isset($data['type'])) {
                $this->sendError($from, '消息格式错误');
                return;
            }
            
            $this->handleMessage($from, $data);
            
        } catch (\Exception $e) {
            $this->sendError($from, '消息处理失败: ' . $e->getMessage());
            error_log("WebSocket消息处理错误: " . $e->getMessage());
        }
    }
    
    /**
     * 处理消息
     */
    private function handleMessage(ConnectionInterface $from, array $data) {
        $type = $data['type'];
        
        switch ($type) {
            case 'chat_message':
                $this->handleChatMessage($from, $data);
                break;
                
            case 'quantum_animation':
                $this->handleQuantumAnimation($from, $data);
                break;
                
            case 'join_room':
                $this->handleJoinRoom($from, $data);
                break;
                
            case 'leave_room':
                $this->handleLeaveRoom($from, $data);
                break;
                
            case 'ping':
                $this->sendToConnection($from, ['type' => 'pong']);
                break;
                
            case 'typing':
                $this->handleTyping($from, $data);
                break;
                
            default:
                $this->sendError($from, '未知消息类型: ' . $type);
        }
    }
    
    /**
     * 处理聊天消息
     */
    private function handleChatMessage(ConnectionInterface $from, array $data) {
        if (!isset($from->user)) {
            $this->sendError($from, '未认证用户');
            return;
        }
        
        $message = $data['message'] ?? '';
        $conversationId = $data['conversation_id'] ?? null;
        $model = $data['model'] ?? 'gpt-4';
        
        if (empty($message)) {
            $this->sendError($from, '消息不能为空');
            return;
        }
        
        try {
            // 保存用户消息
            $userMessage = $this->chatService->saveMessage([
                'user_id' => $from->user['id'],
                'conversation_id' => $conversationId,
                'content' => $message,
                'role' => 'user'
            ]);
            
            // 广播用户消息
            $this->broadcastToRoom($from->user['id'], [
                'type' => 'user_message',
                'message' => $userMessage,
                'quantum_animation' => [
                    'type' => 'userMessageSent',
                    'colors' => ['#0ea5e9', '#3b82f6'],
                    'duration' => 1000
                ]
            ]);
            
            // 发送思考状态
            $this->sendToConnection($from, [
                'type' => 'ai_thinking',
                'quantum_animation' => [
                    'type' => 'aiThinking',
                    'colors' => ['#8b5cf6', '#a855f7'],
                    'duration' => 2000
                ]
            ]);
            
            // 异步获取AI响应
            $this->processAIResponse($from, $userMessage, $model);
            
        } catch (\Exception $e) {
            $this->sendError($from, 'AI消息处理失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 处理AI响应 (异步)
     */
    private function processAIResponse(ConnectionInterface $from, array $userMessage, string $model) {
        // 这里应该异步处理，简化版本直接处理
        try {
            $aiResponse = $this->chatService->getAIResponse(
                $userMessage['content'],
                $userMessage['conversation_id'],
                $model
            );
            
            // 保存AI响应
            $aiMessage = $this->chatService->saveMessage([
                'user_id' => $from->user['id'],
                'conversation_id' => $userMessage['conversation_id'],
                'content' => $aiResponse,
                'role' => 'assistant'
            ]);
            
            // 发送AI响应
            $this->sendToConnection($from, [
                'type' => 'ai_response',
                'message' => $aiMessage,
                'quantum_animation' => [
                    'type' => 'aiResponseReceived',
                    'colors' => ['#10b981', '#059669'],
                    'duration' => 1500
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->sendError($from, 'AI响应生成失败: ' . $e->getMessage());
            
            // 发送错误动画
            $this->sendToConnection($from, [
                'type' => 'ai_error',
                'quantum_animation' => [
                    'type' => 'chatError',
                    'colors' => ['#ef4444', '#dc2626'],
                    'duration' => 1000
                ]
            ]);
        }
    }
    
    /**
     * 处理量子动画
     */
    private function handleQuantumAnimation(ConnectionInterface $from, array $data) {
        $animation = $data['animation'] ?? [];
        $targetUsers = $data['target_users'] ?? [];
        
        // 广播动画到指定用户或房间
        if (empty($targetUsers)) {
            // 广播到所有连接
            $this->broadcast([
                'type' => 'quantum_animation',
                'animation' => $animation,
                'from_user' => $from->user['username'] ?? 'Anonymous'
            ]);
        } else {
            // 发送到指定用户
            foreach ($targetUsers as $userId) {
                if (isset($this->users[$userId])) {
                    $this->sendToConnection($this->users[$userId], [
                        'type' => 'quantum_animation',
                        'animation' => $animation,
                        'from_user' => $from->user['username'] ?? 'Anonymous'
                    ]);
                }
            }
        }
    }
    
    /**
     * 处理加入房间
     */
    private function handleJoinRoom(ConnectionInterface $from, array $data) {
        $roomId = $data['room_id'] ?? null;
        
        if (!$roomId || !isset($from->user)) {
            $this->sendError($from, '加入房间失败');
            return;
        }
        
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = [];
        }
        
        $this->rooms[$roomId][$from->user['id']] = $from;
        
        $this->sendToConnection($from, [
            'type' => 'room_joined',
            'room_id' => $roomId
        ]);
        
        // 通知房间其他用户
        $this->broadcastToRoom($roomId, [
            'type' => 'user_joined',
            'user' => $from->user,
            'room_id' => $roomId
        ], $from);
    }
    
    /**
     * 处理离开房间
     */
    private function handleLeaveRoom(ConnectionInterface $from, array $data) {
        $roomId = $data['room_id'] ?? null;
        
        if (!$roomId || !isset($from->user)) {
            return;
        }
        
        if (isset($this->rooms[$roomId][$from->user['id']])) {
            unset($this->rooms[$roomId][$from->user['id']]);
            
            // 通知房间其他用户
            $this->broadcastToRoom($roomId, [
                'type' => 'user_left',
                'user' => $from->user,
                'room_id' => $roomId
            ]);
            
            // 如果房间为空，删除房间
            if (empty($this->rooms[$roomId])) {
                unset($this->rooms[$roomId]);
            }
        }
    }
    
    /**
     * 处理打字状态
     */
    private function handleTyping(ConnectionInterface $from, array $data) {
        if (!isset($from->user)) {
            return;
        }
        
        $isTyping = $data['is_typing'] ?? false;
        $conversationId = $data['conversation_id'] ?? null;
        
        // 缓存打字状态
        $key = "typing:{$conversationId}:{$from->user['id']}";
        
        if ($isTyping) {
            $this->cacheService->set($key, true, 10); // 10秒过期
        } else {
            $this->cacheService->delete($key);
        }
        
        // 广播打字状态到相关用户
        $this->broadcastToRoom($conversationId, [
            'type' => 'typing_status',
            'user' => $from->user,
            'is_typing' => $isTyping,
            'conversation_id' => $conversationId
        ], $from);
    }
    
    /**
     * 连接关闭
     */
    public function onClose(ConnectionInterface $conn) {
        $this->connections->detach($conn);
        
        if (isset($conn->user)) {
            $userId = $conn->user['id'];
            unset($this->users[$userId]);
            
            // 从所有房间移除用户
            foreach ($this->rooms as $roomId => &$room) {
                if (isset($room[$userId])) {
                    unset($room[$userId]);
                    
                    // 通知房间其他用户
                    $this->broadcastToRoom($roomId, [
                        'type' => 'user_disconnected',
                        'user' => $conn->user
                    ]);
                    
                    // 如果房间为空，删除房间
                    if (empty($room)) {
                        unset($this->rooms[$roomId]);
                    }
                }
            }
            
            echo "用户 {$conn->user['username']} 已断开连接\n";
        }
        
        echo "连接关闭 ({$conn->resourceId})\n";
    }
    
    /**
     * 连接错误
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "连接错误: {$e->getMessage()}\n";
        $conn->close();
    }
    
    /**
     * 发送消息到指定连接
     */
    private function sendToConnection(ConnectionInterface $conn, array $data) {
        $conn->send(json_encode($data));
    }
    
    /**
     * 广播消息到所有连接
     */
    private function broadcast(array $data, ConnectionInterface $exclude = null) {
        foreach ($this->connections as $conn) {
            if ($conn !== $exclude) {
                $this->sendToConnection($conn, $data);
            }
        }
    }
    
    /**
     * 广播消息到房间
     */
    private function broadcastToRoom(string $roomId, array $data, ConnectionInterface $exclude = null) {
        if (!isset($this->rooms[$roomId])) {
            return;
        }
        
        foreach ($this->rooms[$roomId] as $conn) {
            if ($conn !== $exclude) {
                $this->sendToConnection($conn, $data);
            }
        }
    }
    
    /**
     * 发送错误消息
     */
    private function sendError(ConnectionInterface $conn, string $message) {
        $this->sendToConnection($conn, [
            'type' => 'error',
            'message' => $message
        ]);
    }
    
    /**
     * 启动WebSocket服务器
     */
    public static function start(int $port = 8080) {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new self()
                )
            ),
            $port
        );
        
        echo "WebSocket服务器运行在端口 {$port}\n";
        $server->run();
    }
}
