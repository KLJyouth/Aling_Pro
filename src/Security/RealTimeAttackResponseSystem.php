<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\AI\MachineLearning\PredictiveAnalytics;

/**
 * 实时攻击响应系统
 * 
 * 提供毫秒级攻击检测和自动响应，包括智能阻断、威胁隔离和自动恢复
 * 增强安全性：实时威胁响应、智能阻断和自动恢复
 * 优化性能：并行处理和优先级队列
 */
class RealTimeAttackResponseSystem
{
    private $logger;
    private $container;
    private $config = [];
    private $predictiveAnalytics;
    private $responseEngine;
    private $threatQueue = [];
    private $responseHistory = [];
    private $activeThreats = [];
    private $blockedIPs = [];
    private $isolatedResources = [];
    private $lastResponse = 0;
    private $responseInterval = 0.1; // 100毫秒响应一次
    private $frequencyCache = [];
    private $behaviorPatterns = [];
    private $blockedSessions = [];

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
        $this->initializeComponents();
        $this->initializeResponseEngine();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'response_modes' => [
                'automatic' => env('RTARS_AUTO_RESPONSE', true),
                'semi_automatic' => env('RTARS_SEMI_AUTO', false),
                'manual' => env('RTARS_MANUAL', false)
            ],
            'response_levels' => [
                'immediate' => [
                    'threshold' => env('RTARS_IMMEDIATE_THRESHOLD', 0.9),
                    'actions' => ['block_ip', 'isolate_resource', 'alert_admin'],
                    'timeout' => 0.05 // 50毫秒
                ],
                'urgent' => [
                    'threshold' => env('RTARS_URGENT_THRESHOLD', 0.7),
                    'actions' => ['rate_limit', 'monitor_closely', 'alert_admin'],
                    'timeout' => 0.1 // 100毫秒
                ],
                'normal' => [
                    'threshold' => env('RTARS_NORMAL_THRESHOLD', 0.5),
                    'actions' => ['log_event', 'monitor', 'alert_user'],
                    'timeout' => 0.5 // 500毫秒
                ]
            ],
            'blocking' => [
                'ip_blocking' => env('RTARS_IP_BLOCKING', true),
                'session_blocking' => env('RTARS_SESSION_BLOCKING', true),
                'resource_blocking' => env('RTARS_RESOURCE_BLOCKING', true),
                'block_duration' => env('RTARS_BLOCK_DURATION', 3600), // 1小时
                'auto_unblock' => env('RTARS_AUTO_UNBLOCK', true)
            ],
            'isolation' => [
                'enabled' => env('RTARS_ISOLATION_ENABLED', true),
                'isolation_level' => env('RTARS_ISOLATION_LEVEL', 'strict'),
                'auto_recovery' => env('RTARS_AUTO_RECOVERY', true),
                'recovery_timeout' => env('RTARS_RECOVERY_TIMEOUT', 300) // 5分钟
            ],
            'monitoring' => [
                'real_time_monitoring' => env('RTARS_REAL_TIME_MONITORING', true),
                'threat_tracking' => env('RTARS_THREAT_TRACKING', true),
                'behavior_analysis' => env('RTARS_BEHAVIOR_ANALYSIS', true),
                'pattern_detection' => env('RTARS_PATTERN_DETECTION', true)
            ],
            'performance' => [
                'max_concurrent_responses' => env('RTARS_MAX_CONCURRENT', 100),
                'queue_size' => env('RTARS_QUEUE_SIZE', 1000),
                'response_timeout' => env('RTARS_RESPONSE_TIMEOUT', 1.0), // 1秒
                'cleanup_interval' => env('RTARS_CLEANUP_INTERVAL', 60) // 1分钟
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        // 初始化预测分析器
        $this->predictiveAnalytics = new PredictiveAnalytics([
            'real_time_analysis' => true,
            'threat_prediction' => true,
            'response_optimization' => true,
            'confidence_threshold' => 0.8
        ]);
        
        // 初始化威胁队列
        $this->threatQueue = [
            'critical' => [],
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        // 初始化响应历史
        $this->responseHistory = [
            'recent_responses' => [],
            'successful_responses' => [],
            'failed_responses' => [],
            'response_metrics' => [
                'total_responses' => 0,
                'successful_responses' => 0,
                'failed_responses' => 0,
                'average_response_time' => 0.0
            ]
        ];
        
        // 初始化活跃威胁
        $this->activeThreats = [
            'current_threats' => [],
            'threat_sessions' => [],
            'threat_patterns' => []
        ];
        
        // 初始化阻断列表
        $this->blockedIPs = [
            'temporary' => [],
            'permanent' => [],
            'suspicious' => []
        ];
        
        // 初始化隔离资源
        $this->isolatedResources = [
            'files' => [],
            'processes' => [],
            'network_connections' => [],
            'user_sessions' => []
        ];
    }
    
    /**
     * 初始化响应引擎
     */
    private function initializeResponseEngine(): void
    {
        $this->responseEngine = [
            'response_rules' => [
                'sql_injection' => [
                    'detection_pattern' => '/\b(union|select|insert|update|delete|drop|create)\b/i',
                    'response_actions' => ['block_ip', 'isolate_session', 'alert_admin'],
                    'priority' => 'critical'
                ],
                'xss_attack' => [
                    'detection_pattern' => '/<script|javascript:|vbscript:|onload=|onerror=/i',
                    'response_actions' => ['block_session', 'sanitize_input', 'alert_user'],
                    'priority' => 'high'
                ],
                'ddos_attack' => [
                    'detection_pattern' => 'rate_limit_exceeded',
                    'response_actions' => ['rate_limit', 'block_ip', 'scale_resources'],
                    'priority' => 'critical'
                ],
                'privilege_escalation' => [
                    'detection_pattern' => 'unusual_admin_access',
                    'response_actions' => ['block_session', 'revoke_permissions', 'alert_admin'],
                    'priority' => 'critical'
                ],
                'data_exfiltration' => [
                    'detection_pattern' => 'large_data_transfer',
                    'response_actions' => ['block_connection', 'isolate_data', 'alert_admin'],
                    'priority' => 'high'
                ]
            ],
            'response_templates' => [
                'block_ip' => [
                    'action' => 'block_ip',
                    'parameters' => ['ip', 'duration', 'reason'],
                    'timeout' => 0.05
                ],
                'block_session' => [
                    'action' => 'block_session',
                    'parameters' => ['session_id', 'duration', 'reason'],
                    'timeout' => 0.1
                ],
                'isolate_resource' => [
                    'action' => 'isolate_resource',
                    'parameters' => ['resource_id', 'isolation_level', 'duration'],
                    'timeout' => 0.2
                ],
                'rate_limit' => [
                    'action' => 'rate_limit',
                    'parameters' => ['target', 'limit', 'window'],
                    'timeout' => 0.05
                ],
                'alert_admin' => [
                    'action' => 'alert_admin',
                    'parameters' => ['message', 'severity', 'urgency'],
                    'timeout' => 0.1
                ]
            ]
        ];
    }
    
    /**
     * 处理安全事件
     * 
     * @param array $event 安全事件
     * @return array 响应结果
     */
    public function handleSecurityEvent(array $event): array
    {
        $startTime = microtime(true);
        
        $this->logger->info('开始处理安全事件', [
            'event_id' => $event['id'] ?? 'unknown',
            'event_type' => $event['type'] ?? 'unknown',
            'severity' => $event['severity'] ?? 'low'
        ]);
        
        // 分析事件威胁级别
        $threatAnalysis = $this->analyzeThreat($event);
        
        // 确定响应级别
        $responseLevel = $this->determineResponseLevel($threatAnalysis);
        
        // 生成响应计划
        $responsePlan = $this->generateResponsePlan($event, $threatAnalysis, $responseLevel);
        
        // 执行响应
        $responseResult = $this->executeResponse($responsePlan);
        
        // 记录响应历史
        $this->recordResponse($event, $responseResult);
        
        // 更新活跃威胁
        $this->updateActiveThreats($event, $threatAnalysis);
        
        $duration = microtime(true) - $startTime;
        
        $this->logger->info('完成安全事件处理', [
            'event_id' => $event['id'] ?? 'unknown',
            'response_level' => $responseLevel,
            'duration' => $duration,
            'actions_taken' => count($responseResult['actions'])
        ]);
        
        return [
            'event_id' => $event['id'] ?? uniqid('event_', true),
            'threat_level' => $threatAnalysis['threat_level'],
            'response_level' => $responseLevel,
            'response_time' => $duration,
            'actions_taken' => $responseResult['actions'],
            'success' => $responseResult['success']
        ];
    }
    
    /**
     * 分析威胁
     * 
     * @param array $event 安全事件
     * @return array 威胁分析结果
     */
    private function analyzeThreat(array $event): array
    {
        $threatScore = 0.0;
        $threatFactors = [];
        
        // 检查事件类型
        $eventType = $event['type'] ?? 'unknown';
        if (isset($this->responseEngine['response_rules'][$eventType])) {
            $rule = $this->responseEngine['response_rules'][$eventType];
            $threatScore += $this->calculateThreatScore($rule['priority']);
            $threatFactors[] = [
                'factor' => 'event_type',
                'score' => $this->calculateThreatScore($rule['priority']),
                'details' => $eventType
            ];
        }
        
        // 检查事件严重性
        $severity = $event['severity'] ?? 'low';
        $severityScore = $this->calculateSeverityScore($severity);
        $threatScore += $severityScore;
        $threatFactors[] = [
            'factor' => 'severity',
            'score' => $severityScore,
            'details' => $severity
        ];
        
        // 检查事件频率
        $frequencyScore = $this->calculateFrequencyScore($event);
        $threatScore += $frequencyScore;
        if ($frequencyScore > 0) {
            $threatFactors[] = [
                'factor' => 'frequency',
                'score' => $frequencyScore,
                'details' => 'High frequency event'
            ];
        }
        
        // 检查IP信誉
        $ipReputationScore = $this->calculateIPReputationScore($event);
        $threatScore += $ipReputationScore;
        if ($ipReputationScore > 0) {
            $threatFactors[] = [
                'factor' => 'ip_reputation',
                'score' => $ipReputationScore,
                'details' => 'Suspicious IP'
            ];
        }
        
        // 检查行为异常
        $behaviorScore = $this->calculateBehaviorScore($event);
        $threatScore += $behaviorScore;
        if ($behaviorScore > 0) {
            $threatFactors[] = [
                'factor' => 'behavior',
                'score' => $behaviorScore,
                'details' => 'Anomalous behavior'
            ];
        }
        
        // 确定威胁级别
        $threatLevel = $this->determineThreatLevel($threatScore);
        
        return [
            'threat_score' => $threatScore,
            'threat_level' => $threatLevel,
            'threat_factors' => $threatFactors,
            'confidence' => $this->calculateConfidence($threatFactors)
        ];
    }
    
    /**
     * 计算威胁分数
     * 
     * @param string $priority 优先级
     * @return float 威胁分数
     */
    private function calculateThreatScore(string $priority): float
    {
        $scores = [
            'critical' => 0.9,
            'high' => 0.7,
            'medium' => 0.5,
            'low' => 0.3
        ];
        
        return $scores[$priority] ?? 0.3;
    }
    
    /**
     * 计算严重性分数
     * 
     * @param string $severity 严重性
     * @return float 严重性分数
     */
    private function calculateSeverityScore(string $severity): float
    {
        $scores = [
            'critical' => 0.9,
            'high' => 0.7,
            'medium' => 0.5,
            'low' => 0.3,
            'info' => 0.1
        ];
        
        return $scores[$severity] ?? 0.3;
    }
    
    /**
     * 计算频率分数
     * 
     * @param array $event 事件
     * @return float 频率分数
     */
    private function calculateFrequencyScore(array $event): float
    {
        $sourceIP = $event['source_ip'] ?? '';
        $eventType = $event['type'] ?? '';
        $currentTime = time();
        
        // 检查IP频率
        $ipKey = "ip_frequency:{$sourceIP}";
        $ipFrequency = $this->getFrequencyData($ipKey, $currentTime);
        
        // 检查事件类型频率
        $typeKey = "type_frequency:{$eventType}";
        $typeFrequency = $this->getFrequencyData($typeKey, $currentTime);
        
        // 计算综合频率分数
        $ipScore = $this->calculateIPFrequencyScore($ipFrequency);
        $typeScore = $this->calculateTypeFrequencyScore($typeFrequency);
        
        return ($ipScore + $typeScore) / 2.0;
    }

    /**
     * 获取频率数据
     * 
     * @param string $key 键
     * @param int $currentTime 当前时间
     * @return array 频率数据
     */
    private function getFrequencyData(string $key, int $currentTime): array
    {
        $data = $this->frequencyCache[$key] ?? [];
        
        // 清理过期数据（超过1小时）
        $data = array_filter($data, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 3600;
        });
        
        return $data;
    }

    /**
     * 计算IP频率分数
     * 
     * @param array $frequency 频率数据
     * @return float 分数
     */
    private function calculateIPFrequencyScore(array $frequency): float
    {
        $count = count($frequency);
        
        if ($count == 0) return 0.0;
        if ($count <= 5) return 0.1;
        if ($count <= 10) return 0.3;
        if ($count <= 20) return 0.5;
        if ($count <= 50) return 0.7;
        return 1.0;
    }

    /**
     * 计算事件类型频率分数
     * 
     * @param array $frequency 频率数据
     * @return float 分数
     */
    private function calculateTypeFrequencyScore(array $frequency): float
    {
        $count = count($frequency);
        
        if ($count == 0) return 0.0;
        if ($count <= 3) return 0.2;
        if ($count <= 8) return 0.4;
        if ($count <= 15) return 0.6;
        if ($count <= 30) return 0.8;
        return 1.0;
    }

    /**
     * 计算IP信誉分数
     * 
     * @param array $event 事件
     * @return float IP信誉分数
     */
    private function calculateIPReputationScore(array $event): float
    {
        $ip = $event['source_ip'] ?? '';
        
        // 检查是否在阻断列表中
        if (isset($this->blockedIPs['temporary'][$ip]) || 
            isset($this->blockedIPs['permanent'][$ip])) {
            return 0.8;
        }
        
        // 检查是否在可疑列表中
        if (isset($this->blockedIPs['suspicious'][$ip])) {
            return 0.5;
        }
        
        return 0.0;
    }
    
    /**
     * 计算行为分数
     * 
     * @param array $event 事件
     * @return float 行为分数
     */
    private function calculateBehaviorScore(array $event): float
    {
        $behaviorScore = 0.0;
        $sourceIP = $event['source_ip'] ?? '';
        $userAgent = $event['user_agent'] ?? '';
        $requestPath = $event['request_path'] ?? '';
        $requestMethod = $event['request_method'] ?? '';
        
        // 检查异常用户代理
        if ($this->isSuspiciousUserAgent($userAgent)) {
            $behaviorScore += 0.3;
        }
        
        // 检查异常请求路径
        if ($this->isSuspiciousPath($requestPath)) {
            $behaviorScore += 0.4;
        }
        
        // 检查异常请求方法
        if ($this->isSuspiciousMethod($requestMethod)) {
            $behaviorScore += 0.2;
        }
        
        // 检查行为模式
        $patternScore = $this->analyzeBehaviorPattern($sourceIP);
        $behaviorScore += $patternScore;
        
        return min(1.0, $behaviorScore);
    }

    /**
     * 检查可疑用户代理
     * 
     * @param string $userAgent 用户代理
     * @return bool 是否可疑
     */
    private function isSuspiciousUserAgent(string $userAgent): bool
    {
        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scanner/i',
            '/sqlmap/i',
            '/nikto/i',
            '/nmap/i',
            '/curl/i',
            '/wget/i',
            '/python/i',
            '/perl/i',
            '/java/i',
            '/go-http-client/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检查可疑路径
     * 
     * @param string $path 请求路径
     * @return bool 是否可疑
     */
    private function isSuspiciousPath(string $path): bool
    {
        $suspiciousPaths = [
            '/admin',
            '/wp-admin',
            '/phpmyadmin',
            '/config',
            '/.env',
            '/.git',
            '/backup',
            '/sql',
            '/database',
            '/shell',
            '/cmd',
            '/exec',
            '/eval',
            '/system',
            '/passwd',
            '/shadow',
            '/etc',
            '/proc',
            '/tmp',
            '/var'
        ];
        
        foreach ($suspiciousPaths as $suspiciousPath) {
            if (stripos($path, $suspiciousPath) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检查可疑请求方法
     * 
     * @param string $method 请求方法
     * @return bool 是否可疑
     */
    private function isSuspiciousMethod(string $method): bool
    {
        $suspiciousMethods = ['PUT', 'DELETE', 'PATCH', 'TRACE', 'OPTIONS'];
        return in_array(strtoupper($method), $suspiciousMethods);
    }

    /**
     * 分析行为模式
     * 
     * @param string $sourceIP 源IP
     * @return float 模式分数
     */
    private function analyzeBehaviorPattern(string $sourceIP): float
    {
        $patternKey = "behavior_pattern:{$sourceIP}";
        $patterns = $this->behaviorPatterns[$patternKey] ?? [];
        
        $score = 0.0;
        
        // 检查快速连续请求
        if ($this->hasRapidRequests($patterns)) {
            $score += 0.3;
        }
        
        // 检查异常时间模式
        if ($this->hasAbnormalTimePattern($patterns)) {
            $score += 0.2;
        }
        
        // 检查资源扫描模式
        if ($this->hasResourceScanningPattern($patterns)) {
            $score += 0.4;
        }
        
        // 检查参数攻击模式
        if ($this->hasParameterAttackPattern($patterns)) {
            $score += 0.5;
        }
        
        return min(1.0, $score);
    }

    /**
     * 检查快速连续请求
     * 
     * @param array $patterns 模式数据
     * @return bool 是否有快速请求
     */
    private function hasRapidRequests(array $patterns): bool
    {
        if (count($patterns) < 5) return false;
        
        $recentPatterns = array_slice($patterns, -5);
        $timeSpan = end($recentPatterns) - reset($recentPatterns);
        
        return $timeSpan < 10; // 5个请求在10秒内
    }

    /**
     * 检查异常时间模式
     * 
     * @param array $patterns 模式数据
     * @return bool 是否有异常时间模式
     */
    private function hasAbnormalTimePattern(array $patterns): bool
    {
        if (count($patterns) < 10) return false;
        
        $hourlyDistribution = [];
        foreach ($patterns as $timestamp) {
            $hour = date('H', $timestamp);
            $hourlyDistribution[$hour] = ($hourlyDistribution[$hour] ?? 0) + 1;
        }
        
        // 检查是否在非正常时间有大量请求
        $nightHours = ['00', '01', '02', '03', '04', '05'];
        $nightRequests = 0;
        foreach ($nightHours as $hour) {
            $nightRequests += $hourlyDistribution[$hour] ?? 0;
        }
        
        return $nightRequests > count($patterns) * 0.3; // 30%的请求在夜间
    }

    /**
     * 检查资源扫描模式
     * 
     * @param array $patterns 模式数据
     * @return bool 是否有扫描模式
     */
    private function hasResourceScanningPattern(array $patterns): bool
    {
        if (count($patterns) < 20) return false;
        
        $recentPatterns = array_slice($patterns, -20);
        $uniquePaths = [];
        
        foreach ($recentPatterns as $pattern) {
            if (isset($pattern['path'])) {
                $uniquePaths[] = $pattern['path'];
            }
        }
        
        $uniquePathCount = count(array_unique($uniquePaths));
        return $uniquePathCount > 15; // 短时间内访问超过15个不同路径
    }

    /**
     * 检查参数攻击模式
     * 
     * @param array $patterns 模式数据
     * @return bool 是否有参数攻击
     */
    private function hasParameterAttackPattern(array $patterns): bool
    {
        $attackPatterns = [
            '/\b(union|select|insert|update|delete|drop|create|alter)\b/i',
            '/\b(script|javascript|vbscript|onload|onerror)\b/i',
            '/\b(exec|eval|system|shell|cmd)\b/i',
            '/\b(admin|root|test|guest)\b/i',
            '/[<>"\']/', // 特殊字符
            '/\b(or|and)\s+\d+\s*=\s*\d+/i', // SQL注入模式
            '/\b(alert|confirm|prompt)\s*\(/i' // XSS模式
        ];
        
        foreach ($patterns as $pattern) {
            if (isset($pattern['params'])) {
                foreach ($attackPatterns as $attackPattern) {
                    if (preg_match($attackPattern, $pattern['params'])) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * 确定威胁级别
     * 
     * @param float $threatScore 威胁分数
     * @return string 威胁级别
     */
    private function determineThreatLevel(float $threatScore): string
    {
        if ($threatScore >= 0.8) {
            return 'critical';
        } elseif ($threatScore >= 0.6) {
            return 'high';
        } elseif ($threatScore >= 0.4) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * 计算置信度
     * 
     * @param array $threatFactors 威胁因素
     * @return float 置信度
     */
    private function calculateConfidence(array $threatFactors): float
    {
        if (empty($threatFactors)) {
            return 0.0;
        }
        
        $totalScore = array_sum(array_column($threatFactors, 'score'));
        $factorCount = count($threatFactors);
        
        return min(1.0, $totalScore / $factorCount);
    }
    
    /**
     * 确定响应级别
     * 
     * @param array $threatAnalysis 威胁分析
     * @return string 响应级别
     */
    private function determineResponseLevel(array $threatAnalysis): string
    {
        $threatScore = $threatAnalysis['threat_score'];
        
        foreach ($this->config['response_levels'] as $level => $config) {
            if ($threatScore >= $config['threshold']) {
                return $level;
            }
        }
        
        return 'normal';
    }
    
    /**
     * 生成响应计划
     * 
     * @param array $event 事件
     * @param array $threatAnalysis 威胁分析
     * @param string $responseLevel 响应级别
     * @return array 响应计划
     */
    private function generateResponsePlan(array $event, array $threatAnalysis, string $responseLevel): array
    {
        $responsePlan = [
            'event_id' => $event['id'] ?? uniqid('event_', true),
            'threat_level' => $threatAnalysis['threat_level'],
            'response_level' => $responseLevel,
            'actions' => [],
            'timeout' => $this->config['response_levels'][$responseLevel]['timeout'] ?? 0.5
        ];
        
        // 获取响应级别的动作
        $levelActions = $this->config['response_levels'][$responseLevel]['actions'] ?? [];
        
        // 根据事件类型添加特定动作
        $eventType = $event['type'] ?? 'unknown';
        if (isset($this->responseEngine['response_rules'][$eventType])) {
            $ruleActions = $this->responseEngine['response_rules'][$eventType]['response_actions'];
            $levelActions = array_merge($levelActions, $ruleActions);
        }
        
        // 去重并生成动作计划
        $uniqueActions = array_unique($levelActions);
        foreach ($uniqueActions as $action) {
            if (isset($this->responseEngine['response_templates'][$action])) {
                $template = $this->responseEngine['response_templates'][$action];
                $responsePlan['actions'][] = [
                    'action' => $action,
                    'template' => $template,
                    'parameters' => $this->generateActionParameters($action, $event, $threatAnalysis)
                ];
            }
        }
        
        return $responsePlan;
    }
    
    /**
     * 生成动作参数
     * 
     * @param string $action 动作
     * @param array $event 事件
     * @param array $threatAnalysis 威胁分析
     * @return array 参数
     */
    private function generateActionParameters(string $action, array $event, array $threatAnalysis): array
    {
        $parameters = [];
        
        switch ($action) {
            case 'block_ip':
                $parameters = [
                    'ip' => $event['source_ip'] ?? '',
                    'duration' => $this->config['blocking']['block_duration'],
                    'reason' => "Threat level: {$threatAnalysis['threat_level']}"
                ];
                break;
                
            case 'block_session':
                $parameters = [
                    'session_id' => $event['session_id'] ?? '',
                    'duration' => $this->config['blocking']['block_duration'],
                    'reason' => "Threat level: {$threatAnalysis['threat_level']}"
                ];
                break;
                
            case 'isolate_resource':
                $parameters = [
                    'resource_id' => $event['resource_id'] ?? '',
                    'isolation_level' => $this->config['isolation']['isolation_level'],
                    'duration' => $this->config['isolation']['recovery_timeout']
                ];
                break;
                
            case 'rate_limit':
                $parameters = [
                    'target' => $event['source_ip'] ?? $event['session_id'] ?? '',
                    'limit' => 10,
                    'window' => 60
                ];
                break;
                
            case 'alert_admin':
                $parameters = [
                    'message' => "Security threat detected: {$event['type']}",
                    'severity' => $threatAnalysis['threat_level'],
                    'urgency' => $threatAnalysis['threat_score'] > 0.7 ? 'high' : 'normal'
                ];
                break;
        }
        
        return $parameters;
    }
    
    /**
     * 执行响应
     * 
     * @param array $responsePlan 响应计划
     * @return array 响应结果
     */
    private function executeResponse(array $responsePlan): array
    {
        $startTime = microtime(true);
        $results = [
            'success' => true,
            'actions' => [],
            'errors' => []
        ];
        
        foreach ($responsePlan['actions'] as $action) {
            $actionStartTime = microtime(true);
            
            try {
                $actionResult = $this->executeAction($action);
                $results['actions'][] = [
                    'action' => $action['action'],
                    'success' => $actionResult['success'],
                    'duration' => microtime(true) - $actionStartTime,
                    'details' => $actionResult['details']
                ];
                
                if (!$actionResult['success']) {
                    $results['success'] = false;
                    $results['errors'][] = $actionResult['error'] ?? 'Unknown error';
                }
            } catch (\Exception $e) {
                $results['actions'][] = [
                    'action' => $action['action'],
                    'success' => false,
                    'duration' => microtime(true) - $actionStartTime,
                    'details' => 'Exception occurred'
                ];
                
                $results['success'] = false;
                $results['errors'][] = $e->getMessage();
            }
        }
        
        $results['total_duration'] = microtime(true) - $startTime;
        
        return $results;
    }
    
    /**
     * 执行动作
     * 
     * @param array $action 动作
     * @return array 执行结果
     */
    private function executeAction(array $action): array
    {
        $actionName = $action['action'];
        $parameters = $action['parameters'];
        
        switch ($actionName) {
            case 'block_ip':
                return $this->blockIP($parameters);
                
            case 'block_session':
                return $this->blockSession($parameters);
                
            case 'isolate_resource':
                return $this->isolateResource($parameters);
                
            case 'rate_limit':
                return $this->rateLimit($parameters);
                
            case 'alert_admin':
                return $this->alertAdmin($parameters);
                
            default:
                return [
                    'success' => false,
                    'error' => "Unknown action: {$actionName}"
                ];
        }
    }
    
    /**
     * 阻断IP
     * 
     * @param array $parameters 参数
     * @return array 结果
     */
    private function blockIP(array $parameters): array
    {
        $ip = $parameters['ip'];
        $duration = $parameters['duration'];
        $reason = $parameters['reason'];
        
        if (empty($ip)) {
            return [
                'success' => false,
                'error' => 'IP address is required'
            ];
        }
        
        $this->blockedIPs['temporary'][$ip] = [
            'blocked_at' => time(),
            'expires_at' => time() + $duration,
            'reason' => $reason
        ];
        
        return [
            'success' => true,
            'details' => "IP {$ip} blocked for {$duration} seconds"
        ];
    }
    
    /**
     * 阻断会话
     * 
     * @param array $parameters 参数
     * @return array 结果
     */
    private function blockSession(array $parameters): array
    {
        $sessionId = $parameters['session_id'];
        $duration = $parameters['duration'];
        $reason = $parameters['reason'];
        
        if (empty($sessionId)) {
            return [
                'success' => false,
                'error' => 'Session ID is required'
            ];
        }
        
        try {
            // 实际实现：阻断会话
            $this->blockedSessions[$sessionId] = [
                'blocked_at' => time(),
                'expires_at' => time() + $duration,
                'reason' => $reason,
                'source_ip' => $parameters['source_ip'] ?? '',
                'user_agent' => $parameters['user_agent'] ?? ''
            ];
            
            // 记录到日志
            $this->logger->warning('Session blocked', [
                'session_id' => $sessionId,
                'duration' => $duration,
                'reason' => $reason,
                'source_ip' => $parameters['source_ip'] ?? ''
            ]);
            
            // 发送实时通知
            $this->sendRealTimeNotification('session_blocked', [
                'session_id' => $sessionId,
                'reason' => $reason,
                'duration' => $duration
            ]);
            
            return [
                'success' => true,
                'details' => "Session {$sessionId} blocked for {$duration} seconds",
                'block_info' => $this->blockedSessions[$sessionId]
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to block session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to block session: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 隔离资源
     * 
     * @param array $parameters 参数
     * @return array 结果
     */
    private function isolateResource(array $parameters): array
    {
        $resourceId = $parameters['resource_id'];
        $isolationLevel = $parameters['isolation_level'];
        $duration = $parameters['duration'];
        $resourceType = $parameters['resource_type'] ?? 'file';
        
        if (empty($resourceId)) {
            return [
                'success' => false,
                'error' => 'Resource ID is required'
            ];
        }
        
        try {
            $isolationInfo = [
                'isolated_at' => time(),
                'expires_at' => time() + $duration,
                'level' => $isolationLevel,
                'type' => $resourceType,
                'reason' => $parameters['reason'] ?? 'Security threat detected'
            ];
            
            // 根据资源类型进行隔离
            switch ($resourceType) {
                case 'file':
                    $this->isolatedResources['files'][$resourceId] = $isolationInfo;
                    $this->isolateFile($resourceId, $isolationLevel);
                    break;
                case 'process':
                    $this->isolatedResources['processes'][$resourceId] = $isolationInfo;
                    $this->isolateProcess($resourceId, $isolationLevel);
                    break;
                case 'network':
                    $this->isolatedResources['network_connections'][$resourceId] = $isolationInfo;
                    $this->isolateNetworkConnection($resourceId, $isolationLevel);
                    break;
                default:
                    $this->isolatedResources['files'][$resourceId] = $isolationInfo;
                    $this->isolateFile($resourceId, $isolationLevel);
            }
            
            $this->logger->warning('Resource isolated', [
                'resource_id' => $resourceId,
                'type' => $resourceType,
                'level' => $isolationLevel,
                'duration' => $duration
            ]);
            
            return [
                'success' => true,
                'details' => "Resource {$resourceId} isolated with level {$isolationLevel}",
                'isolation_info' => $isolationInfo
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to isolate resource', [
                'resource_id' => $resourceId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to isolate resource: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 速率限制
     * 
     * @param array $parameters 参数
     * @return array 结果
     */
    private function rateLimit(array $parameters): array
    {
        $target = $parameters['target'];
        $limit = $parameters['limit'];
        $window = $parameters['window'];
        $action = $parameters['action'] ?? 'block';
        
        if (empty($target)) {
            return [
                'success' => false,
                'error' => 'Target is required'
            ];
        }
        
        try {
            // 实际实现：应用速率限制
            $rateLimitKey = "rate_limit:{$target}";
            $currentTime = time();
            
            // 获取当前请求计数
            $currentCount = $this->getRateLimitCount($rateLimitKey, $currentTime, $window);
            
            if ($currentCount >= $limit) {
                // 超过限制，执行相应动作
                switch ($action) {
                    case 'block':
                        $this->blockIP(['ip' => $target, 'duration' => $window, 'reason' => 'Rate limit exceeded']);
                        break;
                    case 'delay':
                        $this->applyDelay($target, $window);
                        break;
                    case 'challenge':
                        $this->applyChallenge($target);
                        break;
                    default:
                        $this->blockIP(['ip' => $target, 'duration' => $window, 'reason' => 'Rate limit exceeded']);
                }
                
                $this->logger->warning('Rate limit exceeded', [
                    'target' => $target,
                    'limit' => $limit,
                    'window' => $window,
                    'action' => $action
                ]);
                
                return [
                    'success' => true,
                    'details' => "Rate limit exceeded for {$target}, action: {$action}",
                    'limit_info' => [
                        'target' => $target,
                        'limit' => $limit,
                        'window' => $window,
                        'current_count' => $currentCount,
                        'action' => $action
                    ]
                ];
            } else {
                // 增加计数
                $this->incrementRateLimitCount($rateLimitKey, $currentTime);
                
                return [
                    'success' => true,
                    'details' => "Rate limit applied to {$target}: {$limit} requests per {$window} seconds",
                    'current_count' => $currentCount + 1
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to apply rate limit', [
                'target' => $target,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to apply rate limit: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 告警管理员
     * 
     * @param array $parameters 参数
     * @return array 结果
     */
    private function alertAdmin(array $parameters): array
    {
        $message = $parameters['message'];
        $severity = $parameters['severity'];
        $urgency = $parameters['urgency'];
        $channel = $parameters['channel'] ?? 'all';
        
        try {
            // 实际实现：发送告警
            $alertData = [
                'message' => $message,
                'severity' => $severity,
                'urgency' => $urgency,
                'timestamp' => time(),
                'source' => 'RealTimeAttackResponseSystem',
                'alert_id' => uniqid('alert_', true)
            ];
            
            // 根据严重程度和紧急程度确定通知方式
            $notificationMethods = $this->determineNotificationMethods($severity, $urgency);
            
            foreach ($notificationMethods as $method) {
                switch ($method) {
                    case 'email':
                        $this->sendEmailAlert($alertData);
                        break;
                    case 'sms':
                        $this->sendSMSAlert($alertData);
                        break;
                    case 'webhook':
                        $this->sendWebhookAlert($alertData);
                        break;
                    case 'dashboard':
                        $this->sendDashboardAlert($alertData);
                        break;
                    case 'slack':
                        $this->sendSlackAlert($alertData);
                        break;
                }
            }
            
            // 记录告警
            $this->logAlert($alertData);
            
            $this->logger->warning('Admin alert sent', [
                'message' => $message,
                'severity' => $severity,
                'urgency' => $urgency,
                'methods' => $notificationMethods
            ]);
            
            return [
                'success' => true,
                'details' => "Admin alert sent: {$message} (Severity: {$severity}, Urgency: {$urgency})",
                'alert_id' => $alertData['alert_id'],
                'notification_methods' => $notificationMethods
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to send admin alert', [
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to send admin alert: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 记录响应
     * 
     * @param array $event 事件
     * @param array $responseResult 响应结果
     */
    private function recordResponse(array $event, array $responseResult): void
    {
        $response = [
            'event_id' => $event['id'] ?? uniqid('event_', true),
            'timestamp' => time(),
            'success' => $responseResult['success'],
            'duration' => $responseResult['total_duration'] ?? 0,
            'actions' => $responseResult['actions'] ?? []
        ];
        
        $this->responseHistory['recent_responses'][] = $response;
        
        // 限制最近响应数量
        if (count($this->responseHistory['recent_responses']) > 100) {
            array_shift($this->responseHistory['recent_responses']);
        }
        
        // 更新指标
        $this->responseHistory['response_metrics']['total_responses']++;
        if ($responseResult['success']) {
            $this->responseHistory['response_metrics']['successful_responses']++;
        } else {
            $this->responseHistory['response_metrics']['failed_responses']++;
        }
        
        // 更新平均响应时间
        $totalResponses = $this->responseHistory['response_metrics']['total_responses'];
        $currentAvg = $this->responseHistory['response_metrics']['average_response_time'];
        $newDuration = $responseResult['total_duration'] ?? 0;
        
        $this->responseHistory['response_metrics']['average_response_time'] = 
            (($currentAvg * ($totalResponses - 1)) + $newDuration) / $totalResponses;
    }
    
    /**
     * 更新活跃威胁
     * 
     * @param array $event 事件
     * @param array $threatAnalysis 威胁分析
     */
    private function updateActiveThreats(array $event, array $threatAnalysis): void
    {
        if ($threatAnalysis['threat_level'] === 'critical' || $threatAnalysis['threat_level'] === 'high') {
            $threatId = uniqid('threat_', true);
            
            $this->activeThreats['current_threats'][$threatId] = [
                'event_id' => $event['id'] ?? uniqid('event_', true),
                'threat_level' => $threatAnalysis['threat_level'],
                'threat_score' => $threatAnalysis['threat_score'],
                'detected_at' => time(),
                'source_ip' => $event['source_ip'] ?? '',
                'event_type' => $event['type'] ?? 'unknown'
            ];
        }
    }
    
    /**
     * 获取系统状态
     * 
     * @return array 系统状态
     */
    public function getSystemStatus(): array
    {
        return [
            'active_threats' => count($this->activeThreats['current_threats']),
            'blocked_ips' => count($this->blockedIPs['temporary']) + count($this->blockedIPs['permanent']),
            'isolated_resources' => count($this->isolatedResources['files']) + 
                                  count($this->isolatedResources['processes']) +
                                  count($this->isolatedResources['network_connections']),
            'response_metrics' => $this->responseHistory['response_metrics'],
            'queue_size' => array_sum(array_map('count', $this->threatQueue))
        ];
    }
    
    /**
     * 清理过期数据
     */
    public function cleanupExpiredData(): void
    {
        $now = time();
        
        // 清理过期的IP阻断
        foreach ($this->blockedIPs['temporary'] as $ip => $blockInfo) {
            if ($blockInfo['expires_at'] < $now) {
                unset($this->blockedIPs['temporary'][$ip]);
            }
        }
        
        // 清理过期的隔离资源
        foreach ($this->isolatedResources['files'] as $resourceId => $isolationInfo) {
            if ($isolationInfo['expires_at'] < $now) {
                unset($this->isolatedResources['files'][$resourceId]);
            }
        }
        
        // 清理过期的活跃威胁
        foreach ($this->activeThreats['current_threats'] as $threatId => $threatInfo) {
            if (($now - $threatInfo['detected_at']) > 3600) { // 1小时
                unset($this->activeThreats['current_threats'][$threatId]);
            }
        }
    }
} 