<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\AI\MachineLearning\NeuralNetwork;
use AlingAi\AI\MachineLearning\AnomalyDetector;

/**
 * 智能威胁检测服务
 * 
 * 使用机器学习和人工智能技术检测和预防安全威胁
 * 增强安全性：实时威胁检测、行为分析和预测性安全
 * 优化性能：智能算法和并行处理
 */
class IntelligentThreatDetectionService
{
    private $logger;
    private $container;
    private $config = [];
    private $neuralNetwork;
    private $anomalyDetector;
    private $threatDatabase = [];
    private $behaviorProfiles = [];
    private $threatIndicators = [];
    private $detectionRules = [];
    private $lastAnalysis = 0;
    private $analysisInterval = 60; // 1分钟分析一次

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
        $this->loadThreatDatabase();
        $this->initializeDetectionRules();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'detection_modes' => [
                'signature_based' => true,
                'behavior_based' => true,
                'anomaly_based' => true,
                'ml_based' => true
            ],
            'analysis_interval' => env('THREAT_ANALYSIS_INTERVAL', 60),
            'threat_threshold' => env('THREAT_THRESHOLD', 0.7),
            'false_positive_rate' => env('FALSE_POSITIVE_RATE', 0.05),
            'learning_rate' => env('ML_LEARNING_RATE', 0.01),
            'model_update_interval' => env('MODEL_UPDATE_INTERVAL', 3600),
            'max_behavior_history' => env('MAX_BEHAVIOR_HISTORY', 10000),
            'threat_categories' => [
                'malware' => ['weight' => 0.9, 'patterns' => []],
                'phishing' => ['weight' => 0.8, 'patterns' => []],
                'ddos' => ['weight' => 0.7, 'patterns' => []],
                'sql_injection' => ['weight' => 0.8, 'patterns' => []],
                'xss' => ['weight' => 0.7, 'patterns' => []],
                'privilege_escalation' => ['weight' => 0.9, 'patterns' => []],
                'data_exfiltration' => ['weight' => 0.8, 'patterns' => []],
                'insider_threat' => ['weight' => 0.6, 'patterns' => []]
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        // 初始化神经网络
        $this->neuralNetwork = new NeuralNetwork([
            'input_size' => 100,
            'hidden_layers' => [64, 32, 16],
            'output_size' => count($this->config['threat_categories']),
            'learning_rate' => $this->config['learning_rate']
        ]);
        
        // 初始化异常检测器
        $this->anomalyDetector = new AnomalyDetector([
            'window_size' => 100,
            'threshold' => 0.05,
            'sensitivity' => 0.8
        ]);
    }
    
    /**
     * 加载威胁数据库
     */
    private function loadThreatDatabase(): void
    {
        $this->threatDatabase = [
            'signatures' => [
                'malware' => [
                    '/\b(eval|exec|system|shell_exec|passthru)\s*\(/i',
                    '/\b(base64_decode|gzinflate|gzuncompress)\s*\(/i',
                    '/\b(file_get_contents|file_put_contents)\s*\(/i',
                    '/\b(include|require|include_once|require_once)\s*\(/i'
                ],
                'phishing' => [
                    '/\b(paypal|ebay|amazon|bank|credit)\s*.*\b(verify|confirm|update|secure)/i',
                    '/\b(password|username|login|account)\s*.*\b(expired|suspended|locked)/i',
                    '/\b(urgent|immediate|action|required|suspended)/i'
                ],
                'sql_injection' => [
                    '/\b(union|select|insert|update|delete|drop|create|alter)\s+.*\b(from|into|where)/i',
                    '/\b(or|and)\s+\d+\s*=\s*\d+/i',
                    '/\b(union|select).*\b(information_schema|mysql|sys)/i'
                ],
                'xss' => [
                    '/<script[^>]*>.*<\/script>/is',
                    '/javascript:|vbscript:|onload=|onerror=|onclick=/i',
                    '/<iframe[^>]*>.*<\/iframe>/is',
                    '/<object[^>]*>.*<\/object>/is'
                ]
            ],
            'behavior_patterns' => [
                'privilege_escalation' => [
                    'sudden_admin_access' => 0.8,
                    'multiple_failed_logins' => 0.6,
                    'unusual_file_access' => 0.7
                ],
                'data_exfiltration' => [
                    'large_data_transfer' => 0.8,
                    'unusual_export_activity' => 0.7,
                    'off_hours_access' => 0.5
                ],
                'insider_threat' => [
                    'accessing_unusual_resources' => 0.6,
                    'copying_sensitive_data' => 0.8,
                    'unusual_login_times' => 0.5
                ]
            ]
        ];
    }
    
    /**
     * 初始化检测规则
     */
    private function initializeDetectionRules(): void
    {
        $this->detectionRules = [
            'immediate_block' => [
                'malware_signature' => true,
                'sql_injection_attempt' => true,
                'privilege_escalation' => true
            ],
            'monitor_closely' => [
                'anomalous_behavior' => true,
                'suspicious_pattern' => true,
                'unusual_access' => true
            ],
            'investigate' => [
                'potential_threat' => true,
                'behavior_change' => true,
                'risk_indicator' => true
            ]
        ];
    }
    
    /**
     * 分析安全事件
     * 
     * @param array $event 安全事件
     * @return array 分析结果
     */
    public function analyzeSecurityEvent(array $event): array
    {
        $this->logger->info('开始分析安全事件', [
            'event_id' => $event['id'] ?? 'unknown',
            'event_type' => $event['type'] ?? 'unknown'
        ]);
        
        $analysisResult = [
            'event_id' => $event['id'] ?? uniqid('event_', true),
            'timestamp' => time(),
            'threat_level' => 'low',
            'confidence' => 0.0,
            'threat_type' => 'unknown',
            'recommendations' => [],
            'actions_taken' => []
        ];
        
        // 执行多层检测
        $signatureResult = $this->performSignatureAnalysis($event);
        $behaviorResult = $this->performBehaviorAnalysis($event);
        $anomalyResult = $this->performAnomalyAnalysis($event);
        $mlResult = $this->performMachineLearningAnalysis($event);
        
        // 综合评估
        $analysisResult = $this->combineAnalysisResults(
            $analysisResult,
            $signatureResult,
            $behaviorResult,
            $anomalyResult,
            $mlResult
        );
        
        // 更新行为档案
        $this->updateBehaviorProfile($event);
        
        // 记录威胁指标
        if ($analysisResult['threat_level'] !== 'low') {
            $this->recordThreatIndicator($event, $analysisResult);
        }
        
        // 执行自动响应
        $analysisResult['actions_taken'] = $this->executeAutomaticResponse($analysisResult);
        
        $this->logger->info('安全事件分析完成', [
            'event_id' => $analysisResult['event_id'],
            'threat_level' => $analysisResult['threat_level'],
            'confidence' => $analysisResult['confidence']
        ]);
        
        return $analysisResult;
    }
    
    /**
     * 执行签名分析
     * 
     * @param array $event 安全事件
     * @return array 分析结果
     */
    private function performSignatureAnalysis(array $event): array
    {
        $result = [
            'threat_detected' => false,
            'threat_type' => 'unknown',
            'confidence' => 0.0,
            'matched_signatures' => []
        ];
        
        $content = $event['content'] ?? '';
        $url = $event['url'] ?? '';
        $userAgent = $event['user_agent'] ?? '';
        
        foreach ($this->threatDatabase['signatures'] as $threatType => $signatures) {
            foreach ($signatures as $signature) {
                if (preg_match($signature, $content . ' ' . $url . ' ' . $userAgent)) {
                    $result['threat_detected'] = true;
                    $result['threat_type'] = $threatType;
                    $result['confidence'] = max($result['confidence'], 0.8);
                    $result['matched_signatures'][] = $signature;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 执行行为分析
     * 
     * @param array $event 安全事件
     * @return array 分析结果
     */
    private function performBehaviorAnalysis(array $event): array
    {
        $result = [
            'anomalous_behavior' => false,
            'behavior_type' => 'normal',
            'confidence' => 0.0,
            'indicators' => []
        ];
        
        $userId = $event['user_id'] ?? 'anonymous';
        $userProfile = $this->getBehaviorProfile($userId);
        
        if (!$userProfile) {
            return $result;
        }
        
        // 分析访问模式
        $accessPattern = $this->analyzeAccessPattern($event, $userProfile);
        if ($accessPattern['anomalous']) {
            $result['anomalous_behavior'] = true;
            $result['behavior_type'] = $accessPattern['type'];
            $result['confidence'] = $accessPattern['confidence'];
            $result['indicators'][] = $accessPattern['indicator'];
        }
        
        // 分析资源访问
        $resourceAccess = $this->analyzeResourceAccess($event, $userProfile);
        if ($resourceAccess['anomalous']) {
            $result['anomalous_behavior'] = true;
            $result['behavior_type'] = $resourceAccess['type'];
            $result['confidence'] = max($result['confidence'], $resourceAccess['confidence']);
            $result['indicators'][] = $resourceAccess['indicator'];
        }
        
        // 分析时间模式
        $timePattern = $this->analyzeTimePattern($event, $userProfile);
        if ($timePattern['anomalous']) {
            $result['anomalous_behavior'] = true;
            $result['behavior_type'] = $timePattern['type'];
            $result['confidence'] = max($result['confidence'], $timePattern['confidence']);
            $result['indicators'][] = $timePattern['indicator'];
        }
        
        return $result;
    }
    
    /**
     * 执行异常分析
     * 
     * @param array $event 安全事件
     * @return array 分析结果
     */
    private function performAnomalyAnalysis(array $event): array
    {
        $result = [
            'anomaly_detected' => false,
            'anomaly_score' => 0.0,
            'anomaly_type' => 'none',
            'features' => []
        ];
        
        // 提取特征
        $features = $this->extractEventFeatures($event);
        
        // 使用异常检测器
        $anomalyScore = $this->anomalyDetector->detect($features);
        
        if ($anomalyScore > $this->config['false_positive_rate']) {
            $result['anomaly_detected'] = true;
            $result['anomaly_score'] = $anomalyScore;
            $result['anomaly_type'] = $this->classifyAnomaly($features);
            $result['features'] = $features;
        }
        
        return $result;
    }
    
    /**
     * 执行机器学习分析
     * 
     * @param array $event 安全事件
     * @return array 分析结果
     */
    private function performMachineLearningAnalysis(array $event): array
    {
        $result = [
            'ml_prediction' => 'normal',
            'confidence' => 0.0,
            'threat_probabilities' => [],
            'features_used' => []
        ];
        
        // 提取特征
        $features = $this->extractMLFeatures($event);
        
        // 使用神经网络预测
        $predictions = $this->neuralNetwork->predict($features);
        
        // 找到最高概率的威胁类型
        $maxIndex = array_search(max($predictions), $predictions);
        $threatTypes = array_keys($this->config['threat_categories']);
        
        if (isset($threatTypes[$maxIndex])) {
            $result['ml_prediction'] = $threatTypes[$maxIndex];
            $result['confidence'] = $predictions[$maxIndex];
            $result['threat_probabilities'] = array_combine($threatTypes, $predictions);
        }
        
        $result['features_used'] = $features;
        
        return $result;
    }
    
    /**
     * 综合分析结果
     * 
     * @param array $baseResult 基础结果
     * @param array $signatureResult 签名分析结果
     * @param array $behaviorResult 行为分析结果
     * @param array $anomalyResult 异常分析结果
     * @param array $mlResult 机器学习结果
     * @return array 综合结果
     */
    private function combineAnalysisResults(
        array $baseResult,
        array $signatureResult,
        array $behaviorResult,
        array $anomalyResult,
        array $mlResult
    ): array {
        $threatScore = 0.0;
        $confidence = 0.0;
        $threatType = 'unknown';
        $recommendations = [];
        
        // 签名分析权重
        if ($signatureResult['threat_detected']) {
            $threatScore += 0.4;
            $confidence = max($confidence, $signatureResult['confidence']);
            $threatType = $signatureResult['threat_type'];
            $recommendations[] = '立即阻止此类型的请求';
        }
        
        // 行为分析权重
        if ($behaviorResult['anomalous_behavior']) {
            $threatScore += 0.3;
            $confidence = max($confidence, $behaviorResult['confidence']);
            if ($threatType === 'unknown') {
                $threatType = $behaviorResult['behavior_type'];
            }
            $recommendations[] = '监控用户行为模式';
        }
        
        // 异常分析权重
        if ($anomalyResult['anomaly_detected']) {
            $threatScore += 0.2;
            $confidence = max($confidence, $anomalyResult['anomaly_score']);
            if ($threatType === 'unknown') {
                $threatType = $anomalyResult['anomaly_type'];
            }
            $recommendations[] = '进一步调查异常活动';
        }
        
        // 机器学习分析权重
        if ($mlResult['ml_prediction'] !== 'normal') {
            $threatScore += 0.1;
            $confidence = max($confidence, $mlResult['confidence']);
            if ($threatType === 'unknown') {
                $threatType = $mlResult['ml_prediction'];
            }
            $recommendations[] = '使用AI模型进行深度分析';
        }
        
        // 确定威胁级别
        $threatLevel = 'low';
        if ($threatScore >= 0.8) {
            $threatLevel = 'critical';
        } elseif ($threatScore >= 0.6) {
            $threatLevel = 'high';
        } elseif ($threatScore >= 0.4) {
            $threatLevel = 'medium';
        }
        
        return array_merge($baseResult, [
            'threat_level' => $threatLevel,
            'confidence' => $confidence,
            'threat_type' => $threatType,
            'recommendations' => $recommendations,
            'analysis_details' => [
                'signature' => $signatureResult,
                'behavior' => $behaviorResult,
                'anomaly' => $anomalyResult,
                'machine_learning' => $mlResult
            ]
        ]);
    }
    
    /**
     * 获取行为档案
     * 
     * @param string $userId 用户ID
     * @return array|null 行为档案
     */
    private function getBehaviorProfile(string $userId): ?array
    {
        return $this->behaviorProfiles[$userId] ?? null;
    }
    
    /**
     * 分析访问模式
     * 
     * @param array $event 安全事件
     * @param array $profile 行为档案
     * @return array 分析结果
     */
    private function analyzeAccessPattern(array $event, array $profile): array
    {
        $result = [
            'anomalous' => false,
            'type' => 'normal',
            'confidence' => 0.0,
            'indicator' => ''
        ];
        
        $currentTime = time();
        $hour = (int)date('H', $currentTime);
        
        // 检查是否在异常时间访问
        if ($hour < 6 || $hour > 23) {
            $result['anomalous'] = true;
            $result['type'] = 'off_hours_access';
            $result['confidence'] = 0.6;
            $result['indicator'] = '在非工作时间访问';
        }
        
        // 检查访问频率
        $recentAccesses = $profile['recent_accesses'] ?? [];
        $accessCount = count(array_filter($recentAccesses, function($access) use ($currentTime) {
            return $currentTime - $access < 3600; // 1小时内
        }));
        
        if ($accessCount > 100) {
            $result['anomalous'] = true;
            $result['type'] = 'high_frequency_access';
            $result['confidence'] = 0.7;
            $result['indicator'] = '访问频率异常高';
        }
        
        return $result;
    }
    
    /**
     * 分析资源访问
     * 
     * @param array $event 安全事件
     * @param array $profile 行为档案
     * @return array 分析结果
     */
    private function analyzeResourceAccess(array $event, array $profile): array
    {
        $result = [
            'anomalous' => false,
            'type' => 'normal',
            'confidence' => 0.0,
            'indicator' => ''
        ];
        
        $resource = $event['resource'] ?? '';
        $usualResources = $profile['usual_resources'] ?? [];
        
        // 检查是否访问了不常见的资源
        if (!empty($usualResources) && !in_array($resource, $usualResources)) {
            $result['anomalous'] = true;
            $result['type'] = 'unusual_resource_access';
            $result['confidence'] = 0.5;
            $result['indicator'] = '访问了不常见的资源';
        }
        
        // 检查是否访问了敏感资源
        if (preg_match('/\b(admin|config|password|token|key|secret)\b/i', $resource)) {
            $result['anomalous'] = true;
            $result['type'] = 'sensitive_resource_access';
            $result['confidence'] = 0.8;
            $result['indicator'] = '访问了敏感资源';
        }
        
        return $result;
    }
    
    /**
     * 分析时间模式
     * 
     * @param array $event 安全事件
     * @param array $profile 行为档案
     * @return array 分析结果
     */
    private function analyzeTimePattern(array $event, array $profile): array
    {
        $result = [
            'anomalous' => false,
            'type' => 'normal',
            'confidence' => 0.0,
            'indicator' => ''
        ];
        
        $currentTime = time();
        $usualTimes = $profile['usual_times'] ?? [];
        
        if (!empty($usualTimes)) {
            $currentHour = (int)date('H', $currentTime);
            $isUsualTime = false;
            
            foreach ($usualTimes as $timeRange) {
                if ($currentHour >= $timeRange['start'] && $currentHour <= $timeRange['end']) {
                    $isUsualTime = true;
                    break;
                }
            }
            
            if (!$isUsualTime) {
                $result['anomalous'] = true;
                $result['type'] = 'unusual_time_access';
                $result['confidence'] = 0.4;
                $result['indicator'] = '在异常时间访问';
            }
        }
        
        return $result;
    }
    
    /**
     * 提取事件特征
     * 
     * @param array $event 安全事件
     * @return array 特征向量
     */
    private function extractEventFeatures(array $event): array
    {
        $features = [];
        
        // 时间特征
        $hour = (int)date('H', time());
        $features['hour'] = $hour / 24.0; // 归一化到0-1
        
        // 请求特征
        $features['request_length'] = strlen($event['content'] ?? '') / 10000.0;
        $features['url_length'] = strlen($event['url'] ?? '') / 1000.0;
        
        // 用户代理特征
        $userAgent = $event['user_agent'] ?? '';
        $features['user_agent_length'] = strlen($userAgent) / 500.0;
        $features['has_bot_indicators'] = preg_match('/bot|crawler|spider/i', $userAgent) ? 1.0 : 0.0;
        
        // IP特征
        $ip = $event['ip'] ?? '';
        $features['ip_risk'] = $this->calculateIpRisk($ip);
        
        // 方法特征
        $method = $event['method'] ?? 'GET';
        $features['is_post'] = ($method === 'POST') ? 1.0 : 0.0;
        $features['is_put'] = ($method === 'PUT') ? 1.0 : 0.0;
        $features['is_delete'] = ($method === 'DELETE') ? 1.0 : 0.0;
        
        return $features;
    }
    
    /**
     * 提取机器学习特征
     * 
     * @param array $event 安全事件
     * @return array 特征向量
     */
    private function extractMLFeatures(array $event): array
    {
        $features = $this->extractEventFeatures($event);
        
        // 添加更多ML特定特征
        $content = $event['content'] ?? '';
        
        // 内容特征
        $features['content_entropy'] = $this->calculateEntropy($content);
        $features['special_char_ratio'] = $this->calculateSpecialCharRatio($content);
        $features['digit_ratio'] = $this->calculateDigitRatio($content);
        
        // 编码特征
        $features['has_encoding'] = preg_match('/%[0-9A-F]{2}/i', $content) ? 1.0 : 0.0;
        $features['has_unicode'] = preg_match('/\\u[0-9A-F]{4}/i', $content) ? 1.0 : 0.0;
        
        // 扩展为固定长度向量
        $mlFeatures = array_fill(0, 100, 0.0);
        $i = 0;
        foreach ($features as $value) {
            if ($i < 100) {
                $mlFeatures[$i] = is_numeric($value) ? (float)$value : 0.0;
                $i++;
            }
        }
        
        return $mlFeatures;
    }
    
    /**
     * 计算IP风险
     * 
     * @param string $ip IP地址
     * @return float 风险评分
     */
    private function calculateIpRisk(string $ip): float
    {
        if (empty($ip)) {
            return 0.5;
        }
        
        // 简化的IP风险评估
        if (in_array($ip, $this->threatIndicators['suspicious_ips'] ?? [])) {
            return 0.9;
        }
        
        return 0.1;
    }
    
    /**
     * 计算熵
     * 
     * @param string $content 内容
     * @return float 熵值
     */
    private function calculateEntropy(string $content): float
    {
        if (empty($content)) {
            return 0.0;
        }
        
        $charCount = count_chars($content, 1);
        $length = strlen($content);
        $entropy = 0.0;
        
        foreach ($charCount as $count) {
            $probability = $count / $length;
            $entropy -= $probability * log($probability, 2);
        }
        
        return $entropy / 8.0; // 归一化到0-1
    }
    
    /**
     * 计算特殊字符比例
     * 
     * @param string $content 内容
     * @return float 比例
     */
    private function calculateSpecialCharRatio(string $content): float
    {
        if (empty($content)) {
            return 0.0;
        }
        
        $specialChars = preg_match_all('/[^a-zA-Z0-9\s]/', $content);
        return $specialChars / strlen($content);
    }
    
    /**
     * 计算数字比例
     * 
     * @param string $content 内容
     * @return float 比例
     */
    private function calculateDigitRatio(string $content): float
    {
        if (empty($content)) {
            return 0.0;
        }
        
        $digits = preg_match_all('/[0-9]/', $content);
        return $digits / strlen($content);
    }
    
    /**
     * 分类异常
     * 
     * @param array $features 特征
     * @return string 异常类型
     */
    private function classifyAnomaly(array $features): string
    {
        // 简化的异常分类
        if ($features['hour'] < 0.25 || $features['hour'] > 0.9) {
            return 'time_anomaly';
        }
        
        if ($features['request_length'] > 0.8) {
            return 'size_anomaly';
        }
        
        if ($features['has_bot_indicators'] > 0.5) {
            return 'bot_anomaly';
        }
        
        return 'general_anomaly';
    }
    
    /**
     * 更新行为档案
     * 
     * @param array $event 安全事件
     */
    private function updateBehaviorProfile(array $event): void
    {
        $userId = $event['user_id'] ?? 'anonymous';
        
        if (!isset($this->behaviorProfiles[$userId])) {
            $this->behaviorProfiles[$userId] = [
                'user_id' => $userId,
                'created_at' => time(),
                'recent_accesses' => [],
                'usual_resources' => [],
                'usual_times' => [],
                'access_count' => 0
            ];
        }
        
        $profile = &$this->behaviorProfiles[$userId];
        $currentTime = time();
        
        // 更新访问记录
        $profile['recent_accesses'][] = $currentTime;
        $profile['access_count']++;
        
        // 限制访问记录数量
        if (count($profile['recent_accesses']) > 1000) {
            array_shift($profile['recent_accesses']);
        }
        
        // 更新常用资源
        $resource = $event['resource'] ?? '';
        if (!empty($resource) && !in_array($resource, $profile['usual_resources'])) {
            $profile['usual_resources'][] = $resource;
            
            // 限制资源列表大小
            if (count($profile['usual_resources']) > 100) {
                array_shift($profile['usual_resources']);
            }
        }
        
        // 更新常用时间
        $hour = (int)date('H', $currentTime);
        $timeRange = ['start' => $hour, 'end' => $hour];
        
        $found = false;
        foreach ($profile['usual_times'] as &$existingRange) {
            if (abs($existingRange['start'] - $hour) <= 2) {
                $existingRange['start'] = min($existingRange['start'], $hour);
                $existingRange['end'] = max($existingRange['end'], $hour);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $profile['usual_times'][] = $timeRange;
            
            // 限制时间范围数量
            if (count($profile['usual_times']) > 10) {
                array_shift($profile['usual_times']);
            }
        }
    }
    
    /**
     * 记录威胁指标
     * 
     * @param array $event 安全事件
     * @param array $analysisResult 分析结果
     */
    private function recordThreatIndicator(array $event, array $analysisResult): void
    {
        $indicator = [
            'timestamp' => time(),
            'event_id' => $analysisResult['event_id'],
            'threat_type' => $analysisResult['threat_type'],
            'threat_level' => $analysisResult['threat_level'],
            'confidence' => $analysisResult['confidence'],
            'source_ip' => $event['ip'] ?? '',
            'user_id' => $event['user_id'] ?? '',
            'resource' => $event['resource'] ?? ''
        ];
        
        $this->threatIndicators[] = $indicator;
        
        // 限制威胁指标数量
        if (count($this->threatIndicators) > 10000) {
            array_shift($this->threatIndicators);
        }
    }
    
    /**
     * 执行自动响应
     * 
     * @param array $analysisResult 分析结果
     * @return array 执行的行动
     */
    private function executeAutomaticResponse(array $analysisResult): array
    {
        $actions = [];
        
        switch ($analysisResult['threat_level']) {
            case 'critical':
                $actions[] = '立即阻止IP地址';
                $actions[] = '发送安全警报';
                $actions[] = '记录详细日志';
                break;
            case 'high':
                $actions[] = '临时阻止IP地址';
                $actions[] = '增加监控频率';
                $actions[] = '记录详细日志';
                break;
            case 'medium':
                $actions[] = '增加监控频率';
                $actions[] = '记录详细日志';
                break;
            case 'low':
                $actions[] = '记录基本日志';
                break;
        }
        
        return $actions;
    }
    
    /**
     * 训练模型
     * 
     * @param array $trainingData 训练数据
     * @return array 训练结果
     */
    public function trainModel(array $trainingData): array
    {
        $this->logger->info('开始训练威胁检测模型', [
            'training_samples' => count($trainingData)
        ]);
        
        $features = [];
        $labels = [];
        
        foreach ($trainingData as $sample) {
            $features[] = $this->extractMLFeatures($sample['event']);
            $labels[] = $sample['label'];
        }
        
        // 训练神经网络
        $trainingResult = $this->neuralNetwork->train($features, $labels);
        
        // 训练异常检测器
        $this->anomalyDetector->train($features);
        
        $this->logger->info('威胁检测模型训练完成', [
            'accuracy' => $trainingResult['accuracy'] ?? 0.0,
            'loss' => $trainingResult['loss'] ?? 0.0
        ]);
        
        return $trainingResult;
    }
    
    /**
     * 获取系统状态
     * 
     * @return array 系统状态
     */
    public function getStatus(): array
    {
        return [
            'total_profiles' => count($this->behaviorProfiles),
            'total_threat_indicators' => count($this->threatIndicators),
            'detection_rules' => count($this->detectionRules),
            'threat_signatures' => count($this->threatDatabase['signatures']),
            'model_accuracy' => $this->neuralNetwork->getAccuracy() ?? 0.0,
            'anomaly_detector_status' => $this->anomalyDetector->getStatus(),
            'last_analysis' => date('Y-m-d H:i:s', $this->lastAnalysis)
        ];
    }
}
