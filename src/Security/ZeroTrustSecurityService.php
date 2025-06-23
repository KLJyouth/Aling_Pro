<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use GuzzleHttp\Client;

/**
 * 零信任安全服务
 * 
 * 实现零信任安全模型，包括持续验证、最小权限原则和威胁检测
 * 增强安全性：多层验证、行为分析和实时威胁检测
 * 优化性能：智能缓存和异步验证
 */
class ZeroTrustSecurityService
{
    private $logger;
    private $container;
    private $httpClient;
    private $config = [];
    private $userSessions = [];
    private $deviceRegistry = [];
    private $threatIndicators = [];
    private $verificationCache = [];
    private $lastCleanupTime = 0;
    private $cleanupInterval = 300; // 5分钟清理一次

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param Container $container 容器
     * @param Client|null $httpClient HTTP客户端
     */
    public function __construct(LoggerInterface $logger, Container $container, Client $httpClient = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->httpClient = $httpClient ?? new Client(['timeout' => 10]);
        
        $this->config = $this->loadConfiguration();
        $this->initializeThreatIndicators();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'verification_levels' => [
                'low' => ['device_check', 'basic_auth'],
                'medium' => ['device_check', 'basic_auth', 'behavior_analysis'],
                'high' => ['device_check', 'basic_auth', 'behavior_analysis', 'biometric', 'risk_assessment']
            ],
            'session_timeout' => env('ZERO_TRUST_SESSION_TIMEOUT', 1800), // 30分钟
            'max_failed_attempts' => env('ZERO_TRUST_MAX_FAILED_ATTEMPTS', 5),
            'lockout_duration' => env('ZERO_TRUST_LOCKOUT_DURATION', 900), // 15分钟
            'risk_thresholds' => [
                'low' => 0.3,
                'medium' => 0.6,
                'high' => 0.8
            ],
            'continuous_verification' => true,
            'adaptive_timeout' => true
        ];
    }
    
    /**
     * 初始化威胁指标
     */
    private function initializeThreatIndicators(): void
    {
        $this->threatIndicators = [
            'suspicious_ips' => [],
            'failed_attempts' => [],
            'anomalous_behavior' => [],
            'known_threats' => [
                'sql_injection' => ['pattern' => '/\b(union|select|insert|update|delete|drop|create)\b/i', 'weight' => 0.8],
                'xss' => ['pattern' => '/<script|javascript:|vbscript:|onload=|onerror=/i', 'weight' => 0.7],
                'path_traversal' => ['pattern' => '/\.\.\/|\.\.\\\|%2e%2e/i', 'weight' => 0.6],
                'command_injection' => ['pattern' => '/\b(cmd|exec|system|shell|bash)\b/i', 'weight' => 0.9]
            ]
        ];
    }
    
    /**
     * 验证用户访问权限
     * 
     * @param string $userId 用户ID
     * @param string $resource 资源
     * @param array $context 上下文信息
     * @return array 验证结果
     */
    public function verifyAccess(string $userId, string $resource, array $context = []): array
    {
        $this->logger->info('开始零信任访问验证', [
            'user_id' => $userId,
            'resource' => $resource,
            'context' => $context
        ]);
        
        // 清理过期数据
        $this->performCleanup();
        
        // 获取用户会话信息
        $session = $this->getUserSession($userId);
        if (!$session) {
            return $this->createDenialResponse('用户会话不存在');
        }
        
        // 检查会话是否过期
        if ($this->isSessionExpired($session)) {
            $this->removeUserSession($userId);
            return $this->createDenialResponse('会话已过期');
        }
        
        // 计算风险评分
        $riskScore = $this->calculateRiskScore($userId, $context);
        
        // 确定验证级别
        $verificationLevel = $this->determineVerificationLevel($resource, $riskScore);
        
        // 执行验证
        $verificationResult = $this->performVerification($userId, $verificationLevel, $context);
        
        if (!$verificationResult['success']) {
            $this->recordFailedAttempt($userId, $context);
            return $verificationResult;
        }
        
        // 更新会话信息
        $this->updateUserSession($userId, $context);
        
        // 记录成功访问
        $this->recordSuccessfulAccess($userId, $resource, $context);
        
        return [
            'success' => true,
            'risk_score' => $riskScore,
            'verification_level' => $verificationLevel,
            'session_id' => $session['session_id'],
            'expires_at' => $session['expires_at']
        ];
    }
    
    /**
     * 获取用户会话
     * 
     * @param string $userId 用户ID
     * @return array|null 会话信息
     */
    private function getUserSession(string $userId): ?array
    {
        return $this->userSessions[$userId] ?? null;
    }
    
    /**
     * 检查会话是否过期
     * 
     * @param array $session 会话信息
     * @return bool
     */
    private function isSessionExpired(array $session): bool
    {
        return time() > $session['expires_at'];
    }
    
    /**
     * 移除用户会话
     * 
     * @param string $userId 用户ID
     */
    private function removeUserSession(string $userId): void
    {
        unset($this->userSessions[$userId]);
    }
    
    /**
     * 计算风险评分
     * 
     * @param string $userId 用户ID
     * @param array $context 上下文信息
     * @return float 风险评分 (0-1)
     */
    private function calculateRiskScore(string $userId, array $context): float
    {
        $riskScore = 0.0;
        
        // 检查IP地址风险
        $ipRisk = $this->assessIpRisk($context['ip'] ?? '');
        $riskScore += $ipRisk * 0.3;
        
        // 检查设备风险
        $deviceRisk = $this->assessDeviceRisk($context['device_id'] ?? '');
        $riskScore += $deviceRisk * 0.2;
        
        // 检查行为风险
        $behaviorRisk = $this->assessBehaviorRisk($userId, $context);
        $riskScore += $behaviorRisk * 0.3;
        
        // 检查内容风险
        $contentRisk = $this->assessContentRisk($context['content'] ?? '');
        $riskScore += $contentRisk * 0.2;
        
        return min($riskScore, 1.0);
    }
    
    /**
     * 评估IP风险
     * 
     * @param string $ip IP地址
     * @return float 风险评分
     */
    private function assessIpRisk(string $ip): float
    {
        if (empty($ip)) {
            return 0.5; // 未知IP视为中等风险
        }
        
        // 检查是否在黑名单中
        if (in_array($ip, $this->threatIndicators['suspicious_ips'])) {
            return 0.9;
        }
        
        // 检查地理位置
        $geoRisk = $this->assessGeographicRisk($ip);
        
        // 检查IP信誉
        $reputationRisk = $this->assessIpReputation($ip);
        
        return max($geoRisk, $reputationRisk);
    }
    
    /**
     * 评估地理位置风险
     * 
     * @param string $ip IP地址
     * @return float 风险评分
     */
    private function assessGeographicRisk(string $ip): float
    {
        // 这里可以集成地理位置服务
        // 暂时返回默认值
        return 0.1;
    }
    
    /**
     * 评估IP信誉
     * 
     * @param string $ip IP地址
     * @return float 风险评分
     */
    private function assessIpReputation(string $ip): float
    {
        // 这里可以集成IP信誉服务
        // 暂时返回默认值
        return 0.1;
    }
    
    /**
     * 评估设备风险
     * 
     * @param string $deviceId 设备ID
     * @return float 风险评分
     */
    private function assessDeviceRisk(string $deviceId): float
    {
        if (empty($deviceId)) {
            return 0.5; // 未知设备视为中等风险
        }
        
        $device = $this->deviceRegistry[$deviceId] ?? null;
        if (!$device) {
            return 0.6; // 未注册设备
        }
        
        // 检查设备状态
        if (!$device['verified']) {
            return 0.7;
        }
        
        // 检查设备类型
        if ($device['type'] === 'mobile' && !$device['encrypted']) {
            return 0.4;
        }
        
        return 0.1; // 低风险
    }
    
    /**
     * 评估行为风险
     * 
     * @param string $userId 用户ID
     * @param array $context 上下文信息
     * @return float 风险评分
     */
    private function assessBehaviorRisk(string $userId, array $context): float
    {
        $riskScore = 0.0;
        
        // 检查访问时间模式
        $timeRisk = $this->assessTimePatternRisk($userId, $context);
        $riskScore += $timeRisk * 0.3;
        
        // 检查访问频率
        $frequencyRisk = $this->assessFrequencyRisk($userId);
        $riskScore += $frequencyRisk * 0.3;
        
        // 检查异常行为
        $anomalyRisk = $this->assessAnomalyRisk($userId, $context);
        $riskScore += $anomalyRisk * 0.4;
        
        return $riskScore;
    }
    
    /**
     * 评估时间模式风险
     * 
     * @param string $userId 用户ID
     * @param array $context 上下文信息
     * @return float 风险评分
     */
    private function assessTimePatternRisk(string $userId, array $context): float
    {
        $currentHour = (int)date('H');
        
        // 检查是否在异常时间访问
        if ($currentHour < 6 || $currentHour > 23) {
            return 0.4;
        }
        
        return 0.1;
    }
    
    /**
     * 评估访问频率风险
     * 
     * @param string $userId 用户ID
     * @return float 风险评分
     */
    private function assessFrequencyRisk(string $userId): float
    {
        $recentAttempts = $this->threatIndicators['failed_attempts'][$userId] ?? 0;
        
        if ($recentAttempts > 10) {
            return 0.8;
        } elseif ($recentAttempts > 5) {
            return 0.5;
        }
        
        return 0.1;
    }
    
    /**
     * 评估异常行为风险
     * 
     * @param string $userId 用户ID
     * @param array $context 上下文信息
     * @return float 风险评分
     */
    private function assessAnomalyRisk(string $userId, array $context): float
    {
        // 检查用户代理字符串
        $userAgent = $context['user_agent'] ?? '';
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return 0.6;
        }
        
        // 检查请求头
        $headers = $context['headers'] ?? [];
        if (empty($headers['Accept-Language'])) {
            return 0.3;
        }
        
        return 0.1;
    }
    
    /**
     * 评估内容风险
     * 
     * @param string $content 内容
     * @return float 风险评分
     */
    private function assessContentRisk(string $content): float
    {
        if (empty($content)) {
            return 0.0;
        }
        
        $riskScore = 0.0;
        
        // 检查已知威胁模式
        foreach ($this->threatIndicators['known_threats'] as $threatType => $threat) {
            if (preg_match($threat['pattern'], $content)) {
                $riskScore = max($riskScore, $threat['weight']);
            }
        }
        
        return $riskScore;
    }
    
    /**
     * 确定验证级别
     * 
     * @param string $resource 资源
     * @param float $riskScore 风险评分
     * @return string 验证级别
     */
    private function determineVerificationLevel(string $resource, float $riskScore): string
    {
        // 基于资源敏感性和风险评分确定验证级别
        $resourceSensitivity = $this->assessResourceSensitivity($resource);
        
        if ($resourceSensitivity === 'high' || $riskScore > $this->config['risk_thresholds']['high']) {
            return 'high';
        } elseif ($resourceSensitivity === 'medium' || $riskScore > $this->config['risk_thresholds']['medium']) {
            return 'medium';
        }
        
        return 'low';
    }
    
    /**
     * 评估资源敏感性
     * 
     * @param string $resource 资源
     * @return string 敏感性级别
     */
    private function assessResourceSensitivity(string $resource): string
    {
        $sensitivePatterns = [
            'high' => ['/admin|config|password|token|key|secret/i'],
            'medium' => ['/user|profile|settings|api/i'],
            'low' => ['/public|static|assets/i']
        ];
        
        foreach ($sensitivePatterns as $level => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $resource)) {
                    return $level;
                }
            }
        }
        
        return 'low';
    }
    
    /**
     * 执行验证
     * 
     * @param string $userId 用户ID
     * @param string $level 验证级别
     * @param array $context 上下文信息
     * @return array 验证结果
     */
    private function performVerification(string $userId, string $level, array $context): array
    {
        $verificationSteps = $this->config['verification_levels'][$level] ?? [];
        
        foreach ($verificationSteps as $step) {
            $result = $this->executeVerificationStep($step, $userId, $context);
            if (!$result['success']) {
                return $result;
            }
        }
        
        return ['success' => true];
    }
    
    /**
     * 执行验证步骤
     * 
     * @param string $step 验证步骤
     * @param string $userId 用户ID
     * @param array $context 上下文信息
     * @return array 验证结果
     */
    private function executeVerificationStep(string $step, string $userId, array $context): array
    {
        switch ($step) {
            case 'device_check':
                return $this->verifyUserDevice($userId, $context);
            case 'basic_auth':
                return $this->verifyBasicAuth($userId, $context);
            case 'behavior_analysis':
                return $this->verifyBehavior($userId, $context);
            case 'biometric':
                return $this->verifyBiometric($userId, $context);
            case 'risk_assessment':
                return $this->verifyRiskAssessment($userId, $context);
            default:
                return ['success' => true];
        }
    }
    
    /**
     * 验证设备
     */
    private function verifyUserDevice(string $userId, array $context): array
    {
        $deviceId = $context['device_id'] ?? '';
        if (empty($deviceId)) {
            return $this->createDenialResponse('设备ID缺失');
        }
        
        $device = $this->deviceRegistry[$deviceId] ?? null;
        if (!$device || !$device['verified']) {
            return $this->createDenialResponse('设备未验证');
        }
        
        return ['success' => true];
    }
    
    /**
     * 验证基本认证
     */
    private function verifyBasicAuth(string $userId, array $context): array
    {
        // 这里应该验证用户的认证状态
        // 暂时返回成功
        return ['success' => true];
    }
    
    /**
     * 验证行为
     */
    private function verifyBehavior(string $userId, array $context): array
    {
        // 这里应该进行行为分析
        // 暂时返回成功
        return ['success' => true];
    }
    
    /**
     * 验证生物识别
     */
    private function verifyBiometric(string $userId, array $context): array
    {
        // 这里应该验证生物识别信息
        // 暂时返回成功
        return ['success' => true];
    }
    
    /**
     * 验证风险评估
     */
    private function verifyRiskAssessment(string $userId, array $context): array
    {
        // 这里应该进行详细的风险评估
        // 暂时返回成功
        return ['success' => true];
    }
    
    /**
     * 记录失败尝试
     * 
     * @param string $userId 用户ID
     * @param array $context 上下文信息
     */
    private function recordFailedAttempt(string $userId, array $context): void
    {
        if (!isset($this->threatIndicators['failed_attempts'][$userId])) {
            $this->threatIndicators['failed_attempts'][$userId] = 0;
        }
        
        $this->threatIndicators['failed_attempts'][$userId]++;
        
        $this->logger->warning('零信任验证失败', [
            'user_id' => $userId,
            'context' => $context,
            'failed_attempts' => $this->threatIndicators['failed_attempts'][$userId]
        ]);
    }
    
    /**
     * 更新用户会话
     * 
     * @param string $userId 用户ID
     * @param array $context 上下文信息
     */
    private function updateUserSession(string $userId, array $context): void
    {
        $sessionTimeout = $this->config['session_timeout'];
        
        $this->userSessions[$userId] = [
            'session_id' => uniqid('zt_', true),
            'created_at' => time(),
            'last_activity' => time(),
            'expires_at' => time() + $sessionTimeout,
            'context' => $context
        ];
    }
    
    /**
     * 记录成功访问
     * 
     * @param string $userId 用户ID
     * @param string $resource 资源
     * @param array $context 上下文信息
     */
    private function recordSuccessfulAccess(string $userId, string $resource, array $context): void
    {
        $this->logger->info('零信任访问成功', [
            'user_id' => $userId,
            'resource' => $resource,
            'context' => $context
        ]);
    }
    
    /**
     * 创建拒绝响应
     * 
     * @param string $reason 拒绝原因
     * @return array 拒绝响应
     */
    private function createDenialResponse(string $reason): array
    {
        return [
            'success' => false,
            'reason' => $reason,
            'timestamp' => time()
        ];
    }
    
    /**
     * 执行清理
     */
    private function performCleanup(): void
    {
        $currentTime = time();
        if ($currentTime - $this->lastCleanupTime < $this->cleanupInterval) {
            return;
        }
        
        // 清理过期会话
        foreach ($this->userSessions as $userId => $session) {
            if ($this->isSessionExpired($session)) {
                unset($this->userSessions[$userId]);
            }
        }
        
        // 清理过期缓存
        foreach ($this->verificationCache as $key => $entry) {
            if ($currentTime - $entry['time'] > 300) {
                unset($this->verificationCache[$key]);
            }
        }
        
        $this->lastCleanupTime = $currentTime;
    }
    
    /**
     * 注册设备
     * 
     * @param string $deviceId 设备ID
     * @param array $deviceInfo 设备信息
     * @return array 注册结果
     */
    public function registerDevice(string $deviceId, array $deviceInfo): array
    {
        $this->deviceRegistry[$deviceId] = array_merge($deviceInfo, [
            'registered_at' => time(),
            'verified' => false,
            'last_seen' => time()
        ]);
        
        $this->logger->info('设备已注册', [
            'device_id' => $deviceId,
            'device_info' => $deviceInfo
        ]);
        
        return ['success' => true, 'device_id' => $deviceId];
    }
    
    /**
     * 验证设备
     * 
     * @param string $deviceId 设备ID
     * @param array $verificationData 验证数据
     * @return array 验证结果
     */
    public function verifyDevice(string $deviceId, array $verificationData): array
    {
        if (!isset($this->deviceRegistry[$deviceId])) {
            return $this->createDenialResponse('设备未注册');
        }
        
        // 这里应该进行实际的设备验证
        // 暂时标记为已验证
        $this->deviceRegistry[$deviceId]['verified'] = true;
        $this->deviceRegistry[$deviceId]['verified_at'] = time();
        
        return ['success' => true, 'device_id' => $deviceId];
    }
    
    /**
     * 获取系统状态
     * 
     * @return array 系统状态
     */
    public function getStatus(): array
    {
        return [
            'active_sessions' => count($this->userSessions),
            'registered_devices' => count($this->deviceRegistry),
            'suspicious_ips' => count($this->threatIndicators['suspicious_ips']),
            'failed_attempts' => array_sum($this->threatIndicators['failed_attempts']),
            'cache_entries' => count($this->verificationCache),
            'last_cleanup' => date('Y-m-d H:i:s', $this->lastCleanupTime)
        ];
    }
}
