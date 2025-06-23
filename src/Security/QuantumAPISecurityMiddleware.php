<?php

namespace AlingAi\Security\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\Security\QuantumCryptographyService;
use AlingAi\Security\GlobalSituationAwarenessSystem;

/**
 * 量子API安全中间件
 * 
 * 提供最高级别的API安全保护，包括量子加密、高级威胁检测和自适应安全响应
 * 增强安全性：量子级别加密、实时威胁分析和自动防御
 * 优化性能：智能缓存和选择性保护
 */
class QuantumAPISecurityMiddleware implements MiddlewareInterface
{
    private $logger;
    private $container;
    private $quantumCryptoService;
    private $situationAwareness;
    private $config = [];
    private $threatCache = [];
    private $requestPatterns = [];
    private $lastCleanup = 0;
    private $cleanupInterval = 300; // 5分钟清理一次

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param Container $container 容器
     * @param QuantumCryptographyService $quantumCryptoService 量子加密服务
     * @param GlobalSituationAwarenessSystem $situationAwareness 全局态势感知系统
     */
    public function __construct(
        LoggerInterface $logger,
        Container $container,
        QuantumCryptographyService $quantumCryptoService,
        GlobalSituationAwarenessSystem $situationAwareness
    ) {
        $this->logger = $logger;
        $this->container = $container;
        $this->quantumCryptoService = $quantumCryptoService;
        $this->situationAwareness = $situationAwareness;
        
        $this->config = $this->loadConfiguration();
        $this->initializeThreatCache();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'protection_level' => env('QUANTUM_API_PROTECTION_LEVEL', 'high'),
            'encryption_enabled' => env('QUANTUM_API_ENCRYPTION_ENABLED', true),
            'threat_detection_enabled' => env('QUANTUM_API_THREAT_DETECTION', true),
            'auto_defense_enabled' => env('QUANTUM_API_AUTO_DEFENSE', true),
            'rate_limiting' => [
                'enabled' => env('QUANTUM_API_RATE_LIMIT_ENABLED', true),
                'limit' => env('QUANTUM_API_RATE_LIMIT', 100),
                'window' => env('QUANTUM_API_RATE_WINDOW', 60)
            ],
            'excluded_paths' => explode(',', env('QUANTUM_API_EXCLUDED_PATHS', '/public,/health')),
            'threat_thresholds' => [
                'low' => env('QUANTUM_API_THREAT_LOW', 0.3),
                'medium' => env('QUANTUM_API_THREAT_MEDIUM', 0.6),
                'high' => env('QUANTUM_API_THREAT_HIGH', 0.8)
            ]
        ];
    }
    
    /**
     * 初始化威胁缓存
     */
    private function initializeThreatCache(): void
    {
        $this->threatCache = [
            'known_threats' => [],
            'suspicious_ips' => [],
            'blocked_patterns' => [
                '/\b(union|select|insert|update|delete|drop|create)\b/i',
                '/<script|javascript:|vbscript:|onload=|onerror=/i',
                '/\.\.\/|\.\.\\\|%2e%2e/i',
                '/\b(cmd|exec|system|shell|bash)\b/i'
            ]
        ];
        
        $this->requestPatterns = [
            'ip_addresses' => [],
            'user_agents' => [],
            'request_rates' => []
        ];
    }
    
    /**
     * 处理请求
     * 
     * @param ServerRequestInterface $request 请求
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface 响应
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 检查是否需要跳过保护
        if ($this->shouldSkipProtection($request)) {
            return $handler->handle($request);
        }
        
        // 清理过期数据
        $this->performCleanup();
        
        // 记录请求模式
        $this->recordRequestPattern($request);
        
        // 威胁检测
        $threatAnalysis = $this->analyzeThreat($request);
        
        // 如果威胁级别过高，直接拒绝请求
        if ($threatAnalysis['threat_level'] === 'high' && $this->config['auto_defense_enabled']) {
            $this->logger->warning('量子API安全中间件拦截高威胁请求', [
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                'path' => $request->getUri()->getPath(),
                'threat_score' => $threatAnalysis['threat_score']
            ]);
            
            return $this->createSecurityResponse($request, 403, '安全限制：请求被拒绝');
        }
        
        // 应用量子加密
        if ($this->config['encryption_enabled']) {
            $request = $this->applyQuantumEncryption($request);
        }
        
        // 速率限制
        if ($this->config['rate_limiting']['enabled'] && !$this->checkRateLimit($request)) {
            $this->logger->warning('量子API安全中间件速率限制触发', [
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                'path' => $request->getUri()->getPath()
            ]);
            
            return $this->createSecurityResponse($request, 429, '请求频率过高，请稍后再试');
        }
        
        // 处理请求
        $response = $handler->handle($request);
        
        // 更新全局态势感知
        $this->updateSituationAwareness($request, $response, $threatAnalysis);
        
        // 应用响应安全措施
        return $this->secureResponse($response, $request, $threatAnalysis);
    }
    
    /**
     * 检查是否应该跳过保护
     * 
     * @param ServerRequestInterface $request 请求
     * @return bool
     */
    private function shouldSkipProtection(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        
        foreach ($this->config['excluded_paths'] as $excludedPath) {
            if (strpos($path, trim($excludedPath)) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 记录请求模式
     * 
     * @param ServerRequestInterface $request 请求
     */
    private function recordRequestPattern(ServerRequestInterface $request): void
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $request->getHeaderLine('User-Agent');
        $timestamp = time();
        
        // 记录IP地址
        if (!isset($this->requestPatterns['ip_addresses'][$ip])) {
            $this->requestPatterns['ip_addresses'][$ip] = [
                'count' => 0,
                'first_seen' => $timestamp,
                'last_seen' => $timestamp,
                'paths' => []
            ];
        }
        
        $this->requestPatterns['ip_addresses'][$ip]['count']++;
        $this->requestPatterns['ip_addresses'][$ip]['last_seen'] = $timestamp;
        $this->requestPatterns['ip_addresses'][$ip]['paths'][$request->getUri()->getPath()] = 
            ($this->requestPatterns['ip_addresses'][$ip]['paths'][$request->getUri()->getPath()] ?? 0) + 1;
        
        // 记录User-Agent
        if (!empty($userAgent)) {
            if (!isset($this->requestPatterns['user_agents'][$userAgent])) {
                $this->requestPatterns['user_agents'][$userAgent] = [
                    'count' => 0,
                    'first_seen' => $timestamp,
                    'last_seen' => $timestamp,
                    'ips' => []
                ];
            }
            
            $this->requestPatterns['user_agents'][$userAgent]['count']++;
            $this->requestPatterns['user_agents'][$userAgent]['last_seen'] = $timestamp;
            $this->requestPatterns['user_agents'][$userAgent]['ips'][$ip] = 
                ($this->requestPatterns['user_agents'][$userAgent]['ips'][$ip] ?? 0) + 1;
        }
        
        // 记录请求速率
        if (!isset($this->requestPatterns['request_rates'][$ip])) {
            $this->requestPatterns['request_rates'][$ip] = [];
        }
        
        $this->requestPatterns['request_rates'][$ip][] = $timestamp;
    }
    
    /**
     * 分析威胁
     * 
     * @param ServerRequestInterface $request 请求
     * @return array 威胁分析结果
     */
    private function analyzeThreat(ServerRequestInterface $request): array
    {
        if (!$this->config['threat_detection_enabled']) {
            return [
                'threat_level' => 'none',
                'threat_score' => 0.0,
                'threat_factors' => []
            ];
        }
        
        $threatScore = 0.0;
        $threatFactors = [];
        
        // 检查IP信誉
        $ipScore = $this->checkIpReputation($request);
        if ($ipScore > 0) {
            $threatScore += $ipScore * 0.3;
            $threatFactors[] = [
                'type' => 'ip_reputation',
                'score' => $ipScore,
                'details' => '可疑IP地址'
            ];
        }
        
        // 检查内容威胁
        $contentScore = $this->checkContentThreats($request);
        if ($contentScore > 0) {
            $threatScore += $contentScore * 0.4;
            $threatFactors[] = [
                'type' => 'content_threat',
                'score' => $contentScore,
                'details' => '请求内容包含潜在威胁'
            ];
        }
        
        // 检查行为异常
        $behaviorScore = $this->checkBehaviorAnomalies($request);
        if ($behaviorScore > 0) {
            $threatScore += $behaviorScore * 0.3;
            $threatFactors[] = [
                'type' => 'behavior_anomaly',
                'score' => $behaviorScore,
                'details' => '请求行为异常'
            ];
        }
        
        // 确定威胁级别
        $threatLevel = 'none';
        if ($threatScore >= $this->config['threat_thresholds']['high']) {
            $threatLevel = 'high';
        } elseif ($threatScore >= $this->config['threat_thresholds']['medium']) {
            $threatLevel = 'medium';
        } elseif ($threatScore >= $this->config['threat_thresholds']['low']) {
            $threatLevel = 'low';
        }
        
        return [
            'threat_level' => $threatLevel,
            'threat_score' => $threatScore,
            'threat_factors' => $threatFactors
        ];
    }
    
    /**
     * 检查IP信誉
     * 
     * @param ServerRequestInterface $request 请求
     * @return float 威胁分数
     */
    private function checkIpReputation(ServerRequestInterface $request): float
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        
        // 检查已知的可疑IP
        if (isset($this->threatCache['suspicious_ips'][$ip])) {
            return $this->threatCache['suspicious_ips'][$ip];
        }
        
        // 检查请求模式
        if (isset($this->requestPatterns['ip_addresses'][$ip])) {
            $ipPattern = $this->requestPatterns['ip_addresses'][$ip];
            
            // 检查请求频率
            if ($ipPattern['count'] > 100 && 
                ($ipPattern['last_seen'] - $ipPattern['first_seen']) < 60) {
                $this->threatCache['suspicious_ips'][$ip] = 0.7;
                return 0.7;
            }
            
            // 检查路径多样性
            if (count($ipPattern['paths']) > 20) {
                $this->threatCache['suspicious_ips'][$ip] = 0.5;
                return 0.5;
            }
        }
        
        return 0.0;
    }
    
    /**
     * 检查内容威胁
     * 
     * @param ServerRequestInterface $request 请求
     * @return float 威胁分数
     */
    private function checkContentThreats(ServerRequestInterface $request): float
    {
        $content = (string) $request->getBody();
        $queryParams = $request->getQueryParams();
        $parsedBody = $request->getParsedBody() ?? [];
        
        $threatScore = 0.0;
        
        // 检查请求体
        if (!empty($content)) {
            foreach ($this->threatCache['blocked_patterns'] as $pattern) {
                if (preg_match($pattern, $content)) {
                    $threatScore = max($threatScore, 0.8);
                    break;
                }
            }
        }
        
        // 检查查询参数
        foreach ($queryParams as $param => $value) {
            if (is_string($value)) {
                foreach ($this->threatCache['blocked_patterns'] as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $threatScore = max($threatScore, 0.7);
                        break 2;
                    }
                }
            }
        }
        
        // 检查表单数据
        foreach ($parsedBody as $param => $value) {
            if (is_string($value)) {
                foreach ($this->threatCache['blocked_patterns'] as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $threatScore = max($threatScore, 0.7);
                        break 2;
                    }
                }
            }
        }
        
        return $threatScore;
    }
    
    /**
     * 检查行为异常
     * 
     * @param ServerRequestInterface $request 请求
     * @return float 威胁分数
     */
    private function checkBehaviorAnomalies(ServerRequestInterface $request): float
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $request->getHeaderLine('User-Agent');
        
        $threatScore = 0.0;
        
        // 检查请求速率
        if (isset($this->requestPatterns['request_rates'][$ip])) {
            $recentRequests = array_filter($this->requestPatterns['request_rates'][$ip], function($timestamp) {
                return (time() - $timestamp) <= $this->config['rate_limiting']['window'];
            });
            
            if (count($recentRequests) > $this->config['rate_limiting']['limit']) {
                $threatScore = max($threatScore, 0.6);
            }
        }
        
        // 检查User-Agent异常
        if (empty($userAgent)) {
            $threatScore = max($threatScore, 0.4);
        } elseif (strlen($userAgent) < 10 || strlen($userAgent) > 500) {
            $threatScore = max($threatScore, 0.3);
        }
        
        return $threatScore;
    }
    
    /**
     * 检查速率限制
     * 
     * @param ServerRequestInterface $request 请求
     * @return bool 是否通过速率限制
     */
    private function checkRateLimit(ServerRequestInterface $request): bool
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        
        if (!isset($this->requestPatterns['request_rates'][$ip])) {
            return true;
        }
        
        $recentRequests = array_filter($this->requestPatterns['request_rates'][$ip], function($timestamp) {
            return (time() - $timestamp) <= $this->config['rate_limiting']['window'];
        });
        
        return count($recentRequests) <= $this->config['rate_limiting']['limit'];
    }
    
    /**
     * 应用量子加密
     * 
     * @param ServerRequestInterface $request 请求
     * @return ServerRequestInterface 加密后的请求
     */
    private function applyQuantumEncryption(ServerRequestInterface $request): ServerRequestInterface
    {
        // 检查请求是否已经加密
        if ($request->hasHeader('X-Quantum-Encrypted') && $request->getHeaderLine('X-Quantum-Encrypted') === 'true') {
            return $request;
        }
        
        // 获取加密密钥
        $encryptionKey = $this->quantumCryptoService->generateTemporaryKey();
        
        // 将密钥添加到请求属性中，以便后续处理
        return $request->withAttribute('quantum_encryption_key', $encryptionKey)
                      ->withHeader('X-Quantum-Encrypted', 'true')
                      ->withHeader('X-Quantum-Key-ID', $encryptionKey['id']);
    }
    
    /**
     * 保护响应
     * 
     * @param ResponseInterface $response 响应
     * @param ServerRequestInterface $request 请求
     * @param array $threatAnalysis 威胁分析
     * @return ResponseInterface 安全的响应
     */
    private function secureResponse(ResponseInterface $response, ServerRequestInterface $request, array $threatAnalysis): ResponseInterface
    {
        // 添加安全头
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff')
                           ->withHeader('X-Frame-Options', 'DENY')
                           ->withHeader('X-XSS-Protection', '1; mode=block')
                           ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                           ->withHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // 如果启用了量子加密，加密响应内容
        if ($this->config['encryption_enabled'] && $request->getAttribute('quantum_encryption_key')) {
            $encryptionKey = $request->getAttribute('quantum_encryption_key');
            $encryptedContent = $this->quantumCryptoService->encrypt((string) $response->getBody(), $encryptionKey);
            
            $stream = $response->getBody();
            $stream->rewind();
            $stream->write($encryptedContent);
            
            $response = $response->withHeader('X-Quantum-Encrypted', 'true')
                               ->withHeader('X-Quantum-Key-ID', $encryptionKey['id']);
        }
        
        return $response;
    }
    
    /**
     * 创建安全响应
     * 
     * @param ServerRequestInterface $request 请求
     * @param int $statusCode 状态码
     * @param string $message 消息
     * @return ResponseInterface 响应
     */
    private function createSecurityResponse(ServerRequestInterface $request, int $statusCode, string $message): ResponseInterface
    {
        $response = new \GuzzleHttp\Psr7\Response(
            $statusCode,
            [
                'Content-Type' => 'application/json',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block'
            ],
            json_encode(['error' => $message])
        );
        
        return $response;
    }
    
    /**
     * 更新全局态势感知
     * 
     * @param ServerRequestInterface $request 请求
     * @param ResponseInterface $response 响应
     * @param array $threatAnalysis 威胁分析
     */
    private function updateSituationAwareness(ServerRequestInterface $request, ResponseInterface $response, array $threatAnalysis): void
    {
        if ($threatAnalysis['threat_score'] > 0.3) {
            $this->situationAwareness->updateSituation([
                'type' => 'api_security_event',
                'severity' => $threatAnalysis['threat_level'],
                'source' => 'quantum_api_security',
                'details' => [
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                    'path' => $request->getUri()->getPath(),
                    'method' => $request->getMethod(),
                    'threat_score' => $threatAnalysis['threat_score'],
                    'threat_factors' => $threatAnalysis['threat_factors'],
                    'status_code' => $response->getStatusCode()
                ]
            ]);
        }
    }
    
    /**
     * 执行清理
     */
    private function performCleanup(): void
    {
        $currentTime = time();
        
        if (($currentTime - $this->lastCleanup) < $this->cleanupInterval) {
            return;
        }
        
        $this->lastCleanup = $currentTime;
        
        // 清理请求模式
        foreach ($this->requestPatterns['ip_addresses'] as $ip => $data) {
            if (($currentTime - $data['last_seen']) > 3600) { // 1小时
                unset($this->requestPatterns['ip_addresses'][$ip]);
            }
        }
        
        foreach ($this->requestPatterns['user_agents'] as $userAgent => $data) {
            if (($currentTime - $data['last_seen']) > 3600) { // 1小时
                unset($this->requestPatterns['user_agents'][$userAgent]);
            }
        }
        
        foreach ($this->requestPatterns['request_rates'] as $ip => $timestamps) {
            $this->requestPatterns['request_rates'][$ip] = array_filter($timestamps, function($timestamp) use ($currentTime) {
                return ($currentTime - $timestamp) <= 3600; // 1小时
            });
            
            if (empty($this->requestPatterns['request_rates'][$ip])) {
                unset($this->requestPatterns['request_rates'][$ip]);
            }
        }
    }
    
    /**
     * 获取中间件状态
     * 
     * @return array 状态信息
     */
    public function getStatus(): array
    {
        return [
            'protection_level' => $this->config['protection_level'],
            'encryption_enabled' => $this->config['encryption_enabled'],
            'threat_detection_enabled' => $this->config['threat_detection_enabled'],
            'auto_defense_enabled' => $this->config['auto_defense_enabled'],
            'tracked_ips' => count($this->requestPatterns['ip_addresses']),
            'tracked_user_agents' => count($this->requestPatterns['user_agents']),
            'suspicious_ips' => count($this->threatCache['suspicious_ips']),
            'last_cleanup' => $this->lastCleanup
        ];
    }
} 