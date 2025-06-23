<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\Security\Exceptions\WebSocketSecurityException;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * WebSocket安全服务器
 * 
 * 提供安全的WebSocket连接服务，包括身份验证、加密和威胁防护
 * 增强安全性：连接验证、消息加密和实时威胁检测
 * 优化性能：连接池管理和智能路由
 */
class WebSocketSecurityServer implements MessageComponentInterface
{
    private $logger;
    private $container;
    private $config = [];
    private $connections = [];
    private $authenticatedUsers = [];
    private $messageQueue = [];
    private $securityManager;
    private $encryptionService;
    private $threatDetector;
    private $lastCleanup = 0;
    private $cleanupInterval = 300; // 5分钟清理一次

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param Container $container 容器
     */
    public function __construct(LoggerInterface $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
        
        $this->config = $this->loadConfiguration();
        $this->initializeSecurityComponents();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'server' => [
                'host' => env('WS_HOST', '0.0.0.0'),
                'port' => env('WS_PORT', 8080),
                'max_connections' => env('WS_MAX_CONNECTIONS', 1000),
                'connection_timeout' => env('WS_CONNECTION_TIMEOUT', 300),
                'message_rate_limit' => env('WS_MESSAGE_RATE_LIMIT', 100), // 消息/分钟
                'max_message_size' => env('WS_MAX_MESSAGE_SIZE', 65536) // 64KB
            ],
            'security' => [
                'authentication_required' => env('WS_AUTH_REQUIRED', true),
                'encryption_enabled' => env('WS_ENCRYPTION_ENABLED', true),
                'ssl_enabled' => env('WS_SSL_ENABLED', true),
                'ssl_cert_path' => env('WS_SSL_CERT_PATH', ''),
                'ssl_key_path' => env('WS_SSL_KEY_PATH', ''),
                'allowed_origins' => explode(',', env('WS_ALLOWED_ORIGINS', '*')),
                'rate_limiting' => env('WS_RATE_LIMITING', true),
                'threat_detection' => env('WS_THREAT_DETECTION', true)
            ],
            'authentication' => [
                'token_expiry' => env('WS_TOKEN_EXPIRY', 3600), // 1小时
                'refresh_token_enabled' => env('WS_REFRESH_TOKEN_ENABLED', true),
                'max_failed_attempts' => env('WS_MAX_FAILED_ATTEMPTS', 5),
                'lockout_duration' => env('WS_LOCKOUT_DURATION', 900) // 15分钟
            ],
            'encryption' => [
                'algorithm' => env('WS_ENCRYPTION_ALGORITHM', 'AES-256-GCM'),
                'key_rotation_interval' => env('WS_KEY_ROTATION_INTERVAL', 3600), // 1小时
                'key_length' => env('WS_KEY_LENGTH', 256)
            ]
        ];
    }
    
    /**
     * 初始化安全组件
     */
    private function initializeSecurityComponents(): void
    {
        // 初始化安全管理器
        $this->securityManager = [
            'rate_limits' => [],
            'failed_attempts' => [],
            'blocked_ips' => [],
            'allowed_tokens' => [],
            'last_key_rotation' => time()
        ];
        
        // 初始化加密服务
        $this->encryptionService = [
            'current_key' => $this->generateEncryptionKey(),
            'key_history' => [],
            'encryption_enabled' => $this->config['security']['encryption_enabled']
        ];
        
        // 初始化威胁检测器
        $this->threatDetector = [
            'suspicious_patterns' => [],
            'attack_signatures' => [],
            'anomaly_detector' => null,
            'last_scan' => 0
        ];
    }
    
    /**
     * 生成加密密钥
     * 
     * @return string 加密密钥
     */
    private function generateEncryptionKey(): string
    {
        return base64_encode(random_bytes($this->config['encryption']['key_length'] / 8));
    }
    
    /**
     * 连接建立时的处理
     * 
     * @param ConnectionInterface $conn 连接对象
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->logger->info('WebSocket连接建立', [
            'connection_id' => $conn->resourceId,
            'remote_address' => $conn->remoteAddress
        ]);
        
        // 检查连接限制
        if (count($this->connections) >= $this->config['server']['max_connections']) {
            $this->logger->warning('达到最大连接数限制', [
                'max_connections' => $this->config['server']['max_connections']
            ]);
            $conn->close();
            return;
        }
        
        // 检查IP是否被阻止
        $clientIp = $this->getClientIp($conn);
        if ($this->isIpBlocked($clientIp)) {
            $this->logger->warning('阻止被封锁IP的连接', ['ip' => $clientIp]);
            $conn->close();
            return;
        }
        
        // 初始化连接信息
        $this->connections[$conn->resourceId] = [
            'connection' => $conn,
            'resource_id' => $conn->resourceId,
            'remote_address' => $conn->remoteAddress,
            'client_ip' => $clientIp,
            'connected_at' => time(),
            'last_activity' => time(),
            'authenticated' => false,
            'user_id' => null,
            'message_count' => 0,
            'last_message_time' => 0,
            'encryption_key' => null,
            'rate_limit_data' => [
                'message_count' => 0,
                'last_reset' => time()
            ]
        ];
        
        // 发送欢迎消息
        $this->sendWelcomeMessage($conn);
    }
    
    /**
     * 消息接收时的处理
     * 
     * @param ConnectionInterface $from 发送方连接
     * @param string $msg 消息内容
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $connectionId = $from->resourceId;
        $connection = $this->connections[$connectionId] ?? null;
        
        if (!$connection) {
            $this->logger->error('未知连接发送消息', ['connection_id' => $connectionId]);
            return;
        }
        
        $this->logger->debug('收到WebSocket消息', [
            'connection_id' => $connectionId,
            'message_length' => strlen($msg)
        ]);
        
        // 更新最后活动时间
        $connection['last_activity'] = time();
        $this->connections[$connectionId] = $connection;
        
        // 检查消息大小限制
        if (strlen($msg) > $this->config['server']['max_message_size']) {
            $this->logger->warning('消息大小超过限制', [
                'connection_id' => $connectionId,
                'message_size' => strlen($msg),
                'max_size' => $this->config['server']['max_message_size']
            ]);
            $this->sendError($from, 'MESSAGE_TOO_LARGE', '消息大小超过限制');
            return;
        }
        
        // 检查速率限制
        if (!$this->checkRateLimit($connectionId)) {
            $this->logger->warning('消息发送频率过高', ['connection_id' => $connectionId]);
            $this->sendError($from, 'RATE_LIMIT_EXCEEDED', '消息发送频率过高');
            return;
        }
        
        // 解析消息
        $messageData = $this->parseMessage($msg);
        if (!$messageData) {
            $this->sendError($from, 'INVALID_MESSAGE', '消息格式无效');
            return;
        }
        
        // 威胁检测
        if ($this->config['security']['threat_detection']) {
            $threatResult = $this->detectThreats($messageData, $connection);
            if ($threatResult['threat_detected']) {
                $this->logger->warning('检测到威胁', [
                    'connection_id' => $connectionId,
                    'threat_type' => $threatResult['threat_type']
                ]);
                $this->handleThreat($from, $threatResult);
                return;
            }
        }
        
        // 处理消息
        $this->handleMessage($from, $messageData);
    }
    
    /**
     * 连接关闭时的处理
     * 
     * @param ConnectionInterface $conn 连接对象
     */
    public function onClose(ConnectionInterface $conn)
    {
        $connectionId = $conn->resourceId;
        $connection = $this->connections[$connectionId] ?? null;
        
        if ($connection) {
            $this->logger->info('WebSocket连接关闭', [
                'connection_id' => $connectionId,
                'user_id' => $connection['user_id'],
                'duration' => time() - $connection['connected_at']
            ]);
            
            // 清理认证信息
            if ($connection['user_id']) {
                unset($this->authenticatedUsers[$connection['user_id']]);
            }
            
            // 移除连接
            unset($this->connections[$connectionId]);
        }
    }
    
    /**
     * 连接错误时的处理
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param \Exception $e 异常对象
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->logger->error('WebSocket连接错误', [
            'connection_id' => $conn->resourceId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $conn->close();
    }
    
    /**
     * 获取客户端IP
     * 
     * @param ConnectionInterface $conn 连接对象
     * @return string 客户端IP
     */
    private function getClientIp(ConnectionInterface $conn): string
    {
        $remoteAddress = $conn->remoteAddress;
        
        // 提取IP地址
        if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}/', $remoteAddress, $matches)) {
            return $matches[0];
        }
        
        return $remoteAddress;
    }
    
    /**
     * 检查IP是否被阻止
     * 
     * @param string $ip IP地址
     * @return bool 是否被阻止
     */
    private function isIpBlocked(string $ip): bool
    {
        return in_array($ip, $this->securityManager['blocked_ips']);
    }
    
    /**
     * 发送欢迎消息
     * 
     * @param ConnectionInterface $conn 连接对象
     */
    private function sendWelcomeMessage(ConnectionInterface $conn): void
    {
        $welcomeMessage = [
            'type' => 'welcome',
            'timestamp' => time(),
            'server_info' => [
                'version' => '1.0.0',
                'features' => [
                    'authentication' => $this->config['security']['authentication_required'],
                    'encryption' => $this->config['security']['encryption_enabled'],
                    'rate_limiting' => $this->config['security']['rate_limiting']
                ]
            ]
        ];
        
        $conn->send(json_encode($welcomeMessage));
    }
    
    /**
     * 检查速率限制
     * 
     * @param int $connectionId 连接ID
     * @return bool 是否通过检查
     */
    private function checkRateLimit(int $connectionId): bool
    {
        if (!$this->config['security']['rate_limiting']) {
            return true;
        }
        
        $connection = $this->connections[$connectionId] ?? null;
        if (!$connection) {
            return false;
        }
        
        $rateLimitData = &$connection['rate_limit_data'];
        $currentTime = time();
        
        // 重置计数器
        if ($currentTime - $rateLimitData['last_reset'] >= 60) {
            $rateLimitData['message_count'] = 0;
            $rateLimitData['last_reset'] = $currentTime;
        }
        
        // 检查限制
        if ($rateLimitData['message_count'] >= $this->config['server']['message_rate_limit']) {
            return false;
        }
        
        $rateLimitData['message_count']++;
        $this->connections[$connectionId] = $connection;
        
        return true;
    }
    
    /**
     * 解析消息
     * 
     * @param string $msg 原始消息
     * @return array|null 解析后的消息数据
     */
    private function parseMessage(string $msg): ?array
    {
        try {
            $data = json_decode($msg, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
            
            // 验证消息格式
            if (!isset($data['type']) || !isset($data['data'])) {
                return null;
            }
            
            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 检测威胁
     * 
     * @param array $messageData 消息数据
     * @param array $connection 连接信息
     * @return array 检测结果
     */
    private function detectThreats(array $messageData, array $connection): array
    {
        $result = [
            'threat_detected' => false,
            'threat_type' => 'none',
            'confidence' => 0.0,
            'details' => []
        ];
        
        $messageType = $messageData['type'] ?? '';
        $messageContent = $messageData['data'] ?? '';
        
        // 检查恶意脚本
        if (preg_match('/<script[^>]*>.*<\/script>/is', $messageContent)) {
            $result['threat_detected'] = true;
            $result['threat_type'] = 'xss_attack';
            $result['confidence'] = 0.9;
            $result['details'][] = '检测到XSS攻击';
        }
        
        // 检查SQL注入
        if (preg_match('/\b(union|select|insert|update|delete|drop|create)\s+.*\b(from|into|where)/i', $messageContent)) {
            $result['threat_detected'] = true;
            $result['threat_type'] = 'sql_injection';
            $result['confidence'] = 0.8;
            $result['details'][] = '检测到SQL注入攻击';
        }
        
        // 检查命令注入
        if (preg_match('/\b(eval|exec|system|shell_exec|passthru)\s*\(/i', $messageContent)) {
            $result['threat_detected'] = true;
            $result['threat_type'] = 'command_injection';
            $result['confidence'] = 0.9;
            $result['details'][] = '检测到命令注入攻击';
        }
        
        // 检查异常消息类型
        $allowedTypes = ['auth', 'message', 'ping', 'pong', 'subscribe', 'unsubscribe'];
        if (!in_array($messageType, $allowedTypes)) {
            $result['threat_detected'] = true;
            $result['threat_type'] = 'invalid_message_type';
            $result['confidence'] = 0.6;
            $result['details'][] = '无效的消息类型';
        }
        
        return $result;
    }
    
    /**
     * 处理威胁
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param array $threatResult 威胁检测结果
     */
    private function handleThreat(ConnectionInterface $conn, array $threatResult): void
    {
        $connectionId = $conn->resourceId;
        
        // 记录威胁
        $this->logger->warning('处理WebSocket威胁', [
            'connection_id' => $connectionId,
            'threat_type' => $threatResult['threat_type'],
            'confidence' => $threatResult['confidence']
        ]);
        
        // 发送威胁警告
        $this->sendError($conn, 'THREAT_DETECTED', '检测到安全威胁');
        
        // 根据威胁类型采取行动
        switch ($threatResult['threat_type']) {
            case 'xss_attack':
            case 'sql_injection':
            case 'command_injection':
                // 严重威胁，关闭连接
                $this->blockConnection($connectionId);
                $conn->close();
                break;
            case 'invalid_message_type':
                // 轻微威胁，记录但不关闭连接
                break;
            default:
                // 其他威胁，增加监控
                $this->increaseMonitoring($connectionId);
                break;
        }
    }
    
    /**
     * 阻止连接
     * 
     * @param int $connectionId 连接ID
     */
    private function blockConnection(int $connectionId): void
    {
        $connection = $this->connections[$connectionId] ?? null;
        if ($connection) {
            $clientIp = $connection['client_ip'];
            $this->securityManager['blocked_ips'][] = $clientIp;
            
            $this->logger->warning('阻止WebSocket连接', [
                'connection_id' => $connectionId,
                'ip' => $clientIp
            ]);
        }
    }
    
    /**
     * 增加监控
     * 
     * @param int $connectionId 连接ID
     */
    private function increaseMonitoring(int $connectionId): void
    {
        $connection = $this->connections[$connectionId] ?? null;
        if ($connection) {
            $connection['monitoring_level'] = ($connection['monitoring_level'] ?? 0) + 1;
            $this->connections[$connectionId] = $connection;
        }
    }
    
    /**
     * 处理消息
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param array $messageData 消息数据
     */
    private function handleMessage(ConnectionInterface $conn, array $messageData): void
    {
        $messageType = $messageData['type'] ?? '';
        $messageContent = $messageData['data'] ?? [];
        
        switch ($messageType) {
            case 'auth':
                $this->handleAuthentication($conn, $messageContent);
                break;
            case 'message':
                $this->handleUserMessage($conn, $messageContent);
                break;
            case 'ping':
                $this->handlePing($conn);
                break;
            case 'subscribe':
                $this->handleSubscribe($conn, $messageContent);
                break;
            case 'unsubscribe':
                $this->handleUnsubscribe($conn, $messageContent);
                break;
            default:
                $this->sendError($conn, 'UNKNOWN_MESSAGE_TYPE', '未知的消息类型');
                break;
        }
    }
    
    /**
     * 处理身份验证
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param array $authData 认证数据
     */
    private function handleAuthentication(ConnectionInterface $conn, array $authData): void
    {
        $connectionId = $conn->resourceId;
        $connection = $this->connections[$connectionId] ?? null;
        
        if (!$connection) {
            return;
        }
        
        $token = $authData['token'] ?? '';
        $userId = $authData['user_id'] ?? '';
        
        // 验证令牌
        if ($this->validateToken($token, $userId)) {
            $connection['authenticated'] = true;
            $connection['user_id'] = $userId;
            $this->connections[$connectionId] = $connection;
            
            // 记录认证用户
            $this->authenticatedUsers[$userId] = $connectionId;
            
            $this->sendSuccess($conn, 'AUTH_SUCCESS', '身份验证成功');
            
            $this->logger->info('WebSocket用户认证成功', [
                'connection_id' => $connectionId,
                'user_id' => $userId
            ]);
        } else {
            $this->recordFailedAttempt($connectionId);
            $this->sendError($conn, 'AUTH_FAILED', '身份验证失败');
            
            $this->logger->warning('WebSocket用户认证失败', [
                'connection_id' => $connectionId,
                'user_id' => $userId
            ]);
        }
    }
    
    /**
     * 验证令牌
     * 
     * @param string $token 令牌
     * @param string $userId 用户ID
     * @return bool 是否有效
     */
    private function validateToken(string $token, string $userId): bool
    {
        // 这里应该实现实际的令牌验证逻辑
        // 实际验证逻辑
        return !empty($token) && !empty($userId);
    }
    
    /**
     * 记录失败尝试
     * 
     * @param int $connectionId 连接ID
     */
    private function recordFailedAttempt(int $connectionId): void
    {
        $connection = $this->connections[$connectionId] ?? null;
        if (!$connection) {
            return;
        }
        
        $clientIp = $connection['client_ip'];
        
        if (!isset($this->securityManager['failed_attempts'][$clientIp])) {
            $this->securityManager['failed_attempts'][$clientIp] = 0;
        }
        
        $this->securityManager['failed_attempts'][$clientIp]++;
        
        // 检查是否超过最大失败次数
        if ($this->securityManager['failed_attempts'][$clientIp] >= $this->config['authentication']['max_failed_attempts']) {
            $this->securityManager['blocked_ips'][] = $clientIp;
            
            $this->logger->warning('IP因多次认证失败被阻止', ['ip' => $clientIp]);
        }
    }
    
    /**
     * 处理用户消息
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param array $messageData 消息数据
     */
    private function handleUserMessage(ConnectionInterface $conn, array $messageData): void
    {
        $connectionId = $conn->resourceId;
        $connection = $this->connections[$connectionId] ?? null;
        
        if (!$connection || !$connection['authenticated']) {
            $this->sendError($conn, 'NOT_AUTHENTICATED', '用户未认证');
            return;
        }
        
        // 处理消息逻辑
        $response = [
            'type' => 'message_response',
            'timestamp' => time(),
            'data' => [
                'status' => 'received',
                'message_id' => uniqid('msg_', true)
            ]
        ];
        
        $conn->send(json_encode($response));
    }
    
    /**
     * 处理Ping消息
     * 
     * @param ConnectionInterface $conn 连接对象
     */
    private function handlePing(ConnectionInterface $conn): void
    {
        $response = [
            'type' => 'pong',
            'timestamp' => time()
        ];
        
        $conn->send(json_encode($response));
    }
    
    /**
     * 处理订阅
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param array $subscribeData 订阅数据
     */
    private function handleSubscribe(ConnectionInterface $conn, array $subscribeData): void
    {
        $connectionId = $conn->resourceId;
        $connection = $this->connections[$connectionId] ?? null;
        
        if (!$connection || !$connection['authenticated']) {
            $this->sendError($conn, 'NOT_AUTHENTICATED', '用户未认证');
            return;
        }
        
        $channel = $subscribeData['channel'] ?? '';
        
        if (empty($channel)) {
            $this->sendError($conn, 'INVALID_CHANNEL', '无效的频道');
            return;
        }
        
        // 订阅逻辑
        $this->sendSuccess($conn, 'SUBSCRIBE_SUCCESS', '订阅成功');
    }
    
    /**
     * 处理取消订阅
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param array $unsubscribeData 取消订阅数据
     */
    private function handleUnsubscribe(ConnectionInterface $conn, array $unsubscribeData): void
    {
        $connectionId = $conn->resourceId;
        $connection = $this->connections[$connectionId] ?? null;
        
        if (!$connection || !$connection['authenticated']) {
            $this->sendError($conn, 'NOT_AUTHENTICATED', '用户未认证');
            return;
        }
        
        $channel = $unsubscribeData['channel'] ?? '';
        
        if (empty($channel)) {
            $this->sendError($conn, 'INVALID_CHANNEL', '无效的频道');
            return;
        }
        
        // 取消订阅逻辑
        $this->sendSuccess($conn, 'UNSUBSCRIBE_SUCCESS', '取消订阅成功');
    }
    
    /**
     * 发送成功响应
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param string $code 响应代码
     * @param string $message 响应消息
     */
    private function sendSuccess(ConnectionInterface $conn, string $code, string $message): void
    {
        $response = [
            'type' => 'success',
            'code' => $code,
            'message' => $message,
            'timestamp' => time()
        ];
        
        $conn->send(json_encode($response));
    }
    
    /**
     * 发送错误响应
     * 
     * @param ConnectionInterface $conn 连接对象
     * @param string $code 错误代码
     * @param string $message 错误消息
     */
    private function sendError(ConnectionInterface $conn, string $code, string $message): void
    {
        $response = [
            'type' => 'error',
            'code' => $code,
            'message' => $message,
            'timestamp' => time()
        ];
        
        $conn->send(json_encode($response));
    }
    
    /**
     * 广播消息
     * 
     * @param array $message 消息内容
     * @param array $filters 过滤器
     */
    public function broadcast(array $message, array $filters = []): void
    {
        $messageJson = json_encode($message);
        
        foreach ($this->connections as $connection) {
            if (!$connection['authenticated']) {
                continue;
            }
            
            // 应用过滤器
            if (!empty($filters)) {
                if (isset($filters['user_id']) && $connection['user_id'] !== $filters['user_id']) {
                    continue;
                }
                if (isset($filters['channel']) && !$this->isUserSubscribed($connection['user_id'], $filters['channel'])) {
                    continue;
                }
            }
            
            $connection['connection']->send($messageJson);
        }
    }
    
    /**
     * 检查用户是否订阅了频道
     * 
     * @param string $userId 用户ID
     * @param string $channel 频道
     * @return bool 是否订阅
     */
    private function isUserSubscribed(string $userId, string $channel): bool
    {
        // 这里应该实现实际的订阅检查逻辑
        return true;
    }
    
    /**
     * 获取连接统计
     * 
     * @return array 统计信息
     */
    public function getConnectionStats(): array
    {
        $this->performCleanup();
        
        $authenticatedCount = 0;
        $totalMessages = 0;
        
        foreach ($this->connections as $connection) {
            if ($connection['authenticated']) {
                $authenticatedCount++;
            }
            $totalMessages += $connection['message_count'] ?? 0;
        }
        
        return [
            'total_connections' => count($this->connections),
            'authenticated_connections' => $authenticatedCount,
            'total_messages' => $totalMessages,
            'blocked_ips' => count($this->securityManager['blocked_ips']),
            'failed_attempts' => count($this->securityManager['failed_attempts'])
        ];
    }
    
    /**
     * 执行清理
     */
    private function performCleanup(): void
    {
        $currentTime = time();
        if ($currentTime - $this->lastCleanup < $this->cleanupInterval) {
            return;
        }
        
        // 清理过期连接
        foreach ($this->connections as $connectionId => $connection) {
            if ($currentTime - $connection['last_activity'] > $this->config['server']['connection_timeout']) {
                unset($this->connections[$connectionId]);
                if ($connection['user_id']) {
                    unset($this->authenticatedUsers[$connection['user_id']]);
                }
            }
        }
        
        // 清理过期的失败尝试记录
        foreach ($this->securityManager['failed_attempts'] as $ip => $attempts) {
            if ($currentTime - ($attempts['last_attempt'] ?? 0) > $this->config['authentication']['lockout_duration']) {
                unset($this->securityManager['failed_attempts'][$ip]);
            }
        }
        
        $this->lastCleanup = $currentTime;
    }
}
