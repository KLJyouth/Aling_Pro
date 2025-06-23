<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\AI\MachineLearning\BehaviorAnalyzer;

/**
 * 超级反爬虫系统
 * 
 * 使用先进的AI技术和行为分析来检测和阻止爬虫攻击
 * 增强安全性：智能爬虫检测、行为分析和动态防护
 * 优化性能：高效检测算法和智能缓存
 */
class SuperAntiCrawlerSystem
{
    private $logger;
    private $container;
    private $config = [];
    private $behaviorAnalyzer;
    private $crawlerDatabase = [];
    private $userProfiles = [];
    private $blockedIPs = [];
    private $whitelistIPs = [];
    private $detectionRules = [];
    private $lastCleanup = 0;
    private $cleanupInterval = 600; // 10分钟清理一次

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
        $this->loadCrawlerDatabase();
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
                'ai_based' => true,
                'honeypot_based' => true
            ],
            'blocking_strategies' => [
                'immediate_block' => true,
                'progressive_block' => true,
                'rate_limiting' => true,
                'challenge_response' => true
            ],
            'thresholds' => [
                'crawler_confidence' => env('CRAWLER_CONFIDENCE_THRESHOLD', 0.7),
                'behavior_anomaly' => env('BEHAVIOR_ANOMALY_THRESHOLD', 0.6),
                'request_frequency' => env('REQUEST_FREQUENCY_THRESHOLD', 100), // 请求/分钟
                'session_duration' => env('SESSION_DURATION_THRESHOLD', 3600), // 1小时
                'page_depth' => env('PAGE_DEPTH_THRESHOLD', 50) // 页面深度
            ],
            'ai_settings' => [
                'learning_enabled' => env('AI_LEARNING_ENABLED', true),
                'model_update_interval' => env('AI_MODEL_UPDATE_INTERVAL', 3600),
                'feature_extraction' => env('AI_FEATURE_EXTRACTION', true),
                'anomaly_detection' => env('AI_ANOMALY_DETECTION', true)
            ],
            'honeypot' => [
                'enabled' => env('HONEYPOT_ENABLED', true),
                'trap_urls' => explode(',', env('HONEYPOT_TRAP_URLS', '/admin/hidden,/api/internal,/debug')),
                'fake_data' => env('HONEYPOT_FAKE_DATA', true),
                'monitoring_duration' => env('HONEYPOT_MONITORING_DURATION', 300) // 5分钟
            ],
            'rate_limiting' => [
                'requests_per_minute' => env('RATE_LIMIT_REQUESTS_PER_MINUTE', 60),
                'burst_limit' => env('RATE_LIMIT_BURST_LIMIT', 10),
                'window_size' => env('RATE_LIMIT_WINDOW_SIZE', 60)
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        // 初始化行为分析器
        $this->behaviorAnalyzer = new BehaviorAnalyzer([
            'window_size' => 100,
            'sensitivity' => 0.8,
            'learning_rate' => 0.01
        ]);
        
        // 初始化爬虫数据库
        $this->crawlerDatabase = [
            'known_crawlers' => [],
            'suspicious_patterns' => [],
            'blocked_signatures' => [],
            'behavior_profiles' => []
        ];
    }
    
    /**
     * 加载爬虫数据库
     */
    private function loadCrawlerDatabase(): void
    {
        $this->crawlerDatabase['known_crawlers'] = [
            'googlebot' => [
                'user_agent' => '/Googlebot/i',
                'behavior_pattern' => 'legitimate',
                'whitelist' => true
            ],
            'bingbot' => [
                'user_agent' => '/bingbot/i',
                'behavior_pattern' => 'legitimate',
                'whitelist' => true
            ],
            'baiduspider' => [
                'user_agent' => '/baiduspider/i',
                'behavior_pattern' => 'legitimate',
                'whitelist' => true
            ],
            'scrapy' => [
                'user_agent' => '/scrapy/i',
                'behavior_pattern' => 'suspicious',
                'whitelist' => false
            ],
            'phantomjs' => [
                'user_agent' => '/phantomjs/i',
                'behavior_pattern' => 'suspicious',
                'whitelist' => false
            ],
            'selenium' => [
                'user_agent' => '/selenium/i',
                'behavior_pattern' => 'suspicious',
                'whitelist' => false
            ]
        ];
        
        $this->crawlerDatabase['suspicious_patterns'] = [
            'rapid_requests' => [
                'pattern' => 'requests_per_second > 10',
                'weight' => 0.8
            ],
            'no_javascript' => [
                'pattern' => 'javascript_disabled = true',
                'weight' => 0.6
            ],
            'no_cookies' => [
                'pattern' => 'cookies_disabled = true',
                'weight' => 0.5
            ],
            'no_images' => [
                'pattern' => 'images_disabled = true',
                'weight' => 0.4
            ],
            'systematic_navigation' => [
                'pattern' => 'navigation_pattern = systematic',
                'weight' => 0.7
            ],
            'no_mouse_movement' => [
                'pattern' => 'mouse_movement = false',
                'weight' => 0.6
            ],
            'no_keyboard_events' => [
                'pattern' => 'keyboard_events = false',
                'weight' => 0.5
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
                'known_malicious_crawler' => true,
                'honeypot_triggered' => true,
                'ai_high_confidence' => true
            ],
            'progressive_block' => [
                'suspicious_behavior' => true,
                'rate_limit_exceeded' => true,
                'anomaly_detected' => true
            ],
            'challenge_response' => [
                'uncertain_crawler' => true,
                'new_behavior_pattern' => true,
                'low_confidence_detection' => true
            ]
        ];
    }
    
    /**
     * 检测爬虫
     * 
     * @param array $requestData 请求数据
     * @return array 检测结果
     */
    public function detectCrawler(array $requestData): array
    {
        $this->logger->debug('开始爬虫检测', [
            'ip' => $requestData['ip'] ?? 'unknown',
            'user_agent' => $requestData['user_agent'] ?? 'unknown'
        ]);
        
        $detectionResult = [
            'is_crawler' => false,
            'confidence' => 0.0,
            'crawler_type' => 'unknown',
            'threat_level' => 'low',
            'block_recommended' => false,
            'action_taken' => 'none',
            'detection_methods' => []
        ];
        
        // 检查IP白名单
        if ($this->isIPWhitelisted($requestData['ip'] ?? '')) {
            return array_merge($detectionResult, [
                'is_crawler' => false,
                'confidence' => 1.0,
                'crawler_type' => 'whitelisted',
                'action_taken' => 'allow'
            ]);
        }
        
        // 检查IP黑名单
        if ($this->isIPBlocked($requestData['ip'] ?? '')) {
            return array_merge($detectionResult, [
                'is_crawler' => true,
                'confidence' => 1.0,
                'crawler_type' => 'blocked',
                'threat_level' => 'high',
                'block_recommended' => true,
                'action_taken' => 'block'
            ]);
        }
        
        // 执行多层检测
        $signatureResult = $this->performSignatureDetection($requestData);
        $behaviorResult = $this->performBehaviorDetection($requestData);
        $aiResult = $this->performAIDetection($requestData);
        $honeypotResult = $this->performHoneypotDetection($requestData);
        
        // 综合分析结果
        $detectionResult = $this->combineDetectionResults(
            $detectionResult,
            $signatureResult,
            $behaviorResult,
            $aiResult,
            $honeypotResult
        );
        
        // 更新用户档案
        $this->updateUserProfile($requestData, $detectionResult);
        
        // 执行相应行动
        $detectionResult['action_taken'] = $this->executeAction($detectionResult);
        
        $this->logger->info('爬虫检测完成', [
            'ip' => $requestData['ip'] ?? 'unknown',
            'is_crawler' => $detectionResult['is_crawler'],
            'confidence' => $detectionResult['confidence'],
            'action_taken' => $detectionResult['action_taken']
        ]);
        
        return $detectionResult;
    }
    
    /**
     * 检查IP是否在白名单中
     * 
     * @param string $ip IP地址
     * @return bool 是否在白名单中
     */
    private function isIPWhitelisted(string $ip): bool
    {
        return in_array($ip, $this->whitelistIPs);
    }
    
    /**
     * 检查IP是否在黑名单中
     * 
     * @param string $ip IP地址
     * @return bool 是否在黑名单中
     */
    private function isIPBlocked(string $ip): bool
    {
        return in_array($ip, $this->blockedIPs);
    }
    
    /**
     * 执行签名检测
     * 
     * @param array $requestData 请求数据
     * @return array 检测结果
     */
    private function performSignatureDetection(array $requestData): array
    {
        $result = [
            'detected' => false,
            'confidence' => 0.0,
            'crawler_type' => 'unknown',
            'signatures_matched' => []
        ];
        
        $userAgent = $requestData['user_agent'] ?? '';
        $ip = $requestData['ip'] ?? '';
        
        // 检查已知爬虫签名
        foreach ($this->crawlerDatabase['known_crawlers'] as $crawlerName => $crawlerInfo) {
            if (preg_match($crawlerInfo['user_agent'], $userAgent)) {
                $result['detected'] = true;
                $result['crawler_type'] = $crawlerName;
                $result['signatures_matched'][] = $crawlerName;
                
                if ($crawlerInfo['whitelist']) {
                    $result['confidence'] = 0.1; // 低置信度，因为是白名单
                } else {
                    $result['confidence'] = 0.9; // 高置信度，恶意爬虫
                }
                break;
            }
        }
        
        // 检查可疑模式
        foreach ($this->crawlerDatabase['suspicious_patterns'] as $patternName => $patternInfo) {
            if ($this->matchesSuspiciousPattern($requestData, $patternInfo)) {
                $result['detected'] = true;
                $result['confidence'] = max($result['confidence'], $patternInfo['weight']);
                $result['signatures_matched'][] = $patternName;
            }
        }
        
        return $result;
    }
    
    /**
     * 检查是否匹配可疑模式
     * 
     * @param array $requestData 请求数据
     * @param array $patternInfo 模式信息
     * @return bool 是否匹配
     */
    private function matchesSuspiciousPattern(array $requestData, array $patternInfo): bool
    {
        $pattern = $patternInfo['pattern'];
        
        switch ($pattern) {
            case 'requests_per_second > 10':
                return $this->checkRequestFrequency($requestData, 10);
            case 'javascript_disabled = true':
                return !($requestData['javascript_enabled'] ?? true);
            case 'cookies_disabled = true':
                return !($requestData['cookies_enabled'] ?? true);
            case 'images_disabled = true':
                return !($requestData['images_enabled'] ?? true);
            case 'navigation_pattern = systematic':
                return $this->checkSystematicNavigation($requestData);
            case 'mouse_movement = false':
                return !($requestData['mouse_movement'] ?? true);
            case 'keyboard_events = false':
                return !($requestData['keyboard_events'] ?? true);
            default:
                return false;
        }
    }
    
    /**
     * 检查请求频率
     * 
     * @param array $requestData 请求数据
     * @param int $threshold 阈值
     * @return bool 是否超过阈值
     */
    private function checkRequestFrequency(array $requestData, int $threshold): bool
    {
        $ip = $requestData['ip'] ?? '';
        $currentTime = time();
        
        if (!isset($this->userProfiles[$ip])) {
            return false;
        }
        
        $profile = $this->userProfiles[$ip];
        $recentRequests = array_filter($profile['requests'] ?? [], function($timestamp) use ($currentTime) {
            return $currentTime - $timestamp < 60; // 1分钟内
        });
        
        return count($recentRequests) > $threshold;
    }
    
    /**
     * 检查系统性导航
     * 
     * @param array $requestData 请求数据
     * @return bool 是否是系统性导航
     */
    private function checkSystematicNavigation(array $requestData): bool
    {
        $ip = $requestData['ip'] ?? '';
        
        if (!isset($this->userProfiles[$ip])) {
            return false;
        }
        
        $profile = $this->userProfiles[$ip];
        $recentUrls = array_slice($profile['recent_urls'] ?? [], -10);
        
        if (count($recentUrls) < 5) {
            return false;
        }
        
        // 检查是否有规律的模式
        $patterns = [];
        for ($i = 1; $i < count($recentUrls); $i++) {
            $patterns[] = $this->extractUrlPattern($recentUrls[$i - 1], $recentUrls[$i]);
        }
        
        // 如果大部分模式相同，认为是系统性导航
        $patternCounts = array_count_values($patterns);
        $maxCount = max($patternCounts);
        
        return $maxCount >= count($patterns) * 0.7;
    }
    
    /**
     * 提取URL模式
     * 
     * @param string $url1 第一个URL
     * @param string $url2 第二个URL
     * @return string URL模式
     */
    private function extractUrlPattern(string $url1, string $url2): string
    {
        // 简化的URL模式提取
        $path1 = parse_url($url1, PHP_URL_PATH);
        $path2 = parse_url($url2, PHP_URL_PATH);
        
        if ($path1 && $path2) {
            $segments1 = explode('/', trim($path1, '/'));
            $segments2 = explode('/', trim($path2, '/'));
            
            if (count($segments1) === count($segments2)) {
                $pattern = [];
                for ($i = 0; $i < count($segments1); $i++) {
                    if ($segments1[$i] === $segments2[$i]) {
                        $pattern[] = $segments1[$i];
                    } else {
                        $pattern[] = '*';
                    }
                }
                return implode('/', $pattern);
            }
        }
        
        return 'unknown';
    }
    
    /**
     * 执行行为检测
     * 
     * @param array $requestData 请求数据
     * @return array 检测结果
     */
    private function performBehaviorDetection(array $requestData): array
    {
        $result = [
            'anomalous_behavior' => false,
            'confidence' => 0.0,
            'behavior_type' => 'normal',
            'indicators' => []
        ];
        
        $ip = $requestData['ip'] ?? '';
        $profile = $this->getUserProfile($ip);
        
        if (!$profile) {
            return $result;
        }
        
        // 分析访问模式
        $accessPattern = $this->analyzeAccessPattern($requestData, $profile);
        if ($accessPattern['anomalous']) {
            $result['anomalous_behavior'] = true;
            $result['confidence'] = $accessPattern['confidence'];
            $result['behavior_type'] = $accessPattern['type'];
            $result['indicators'][] = $accessPattern['indicator'];
        }
        
        // 分析时间模式
        $timePattern = $this->analyzeTimePattern($requestData, $profile);
        if ($timePattern['anomalous']) {
            $result['anomalous_behavior'] = true;
            $result['confidence'] = max($result['confidence'], $timePattern['confidence']);
            $result['behavior_type'] = $timePattern['type'];
            $result['indicators'][] = $timePattern['indicator'];
        }
        
        // 分析内容模式
        $contentPattern = $this->analyzeContentPattern($requestData, $profile);
        if ($contentPattern['anomalous']) {
            $result['anomalous_behavior'] = true;
            $result['confidence'] = max($result['confidence'], $contentPattern['confidence']);
            $result['behavior_type'] = $contentPattern['type'];
            $result['indicators'][] = $contentPattern['indicator'];
        }
        
        return $result;
    }
    
    /**
     * 获取用户档案
     * 
     * @param string $ip IP地址
     * @return array|null 用户档案
     */
    private function getUserProfile(string $ip): ?array
    {
        return $this->userProfiles[$ip] ?? null;
    }
    
    /**
     * 分析访问模式
     * 
     * @param array $requestData 请求数据
     * @param array $profile 用户档案
     * @return array 分析结果
     */
    private function analyzeAccessPattern(array $requestData, array $profile): array
    {
        $result = [
            'anomalous' => false,
            'confidence' => 0.0,
            'type' => 'normal',
            'indicator' => ''
        ];
        
        $currentUrl = $requestData['url'] ?? '';
        $usualUrls = $profile['usual_urls'] ?? [];
        
        // 检查是否访问了不常见的URL
        if (!empty($usualUrls) && !in_array($currentUrl, $usualUrls)) {
            $result['anomalous'] = true;
            $result['confidence'] = 0.5;
            $result['type'] = 'unusual_url_access';
            $result['indicator'] = '访问了不常见的URL';
        }
        
        // 检查页面深度
        $pageDepth = $this->calculatePageDepth($currentUrl);
        if ($pageDepth > $this->config['thresholds']['page_depth']) {
            $result['anomalous'] = true;
            $result['confidence'] = 0.6;
            $result['type'] = 'deep_navigation';
            $result['indicator'] = '页面深度异常';
        }
        
        return $result;
    }
    
    /**
     * 分析时间模式
     * 
     * @param array $requestData 请求数据
     * @param array $profile 用户档案
     * @return array 分析结果
     */
    private function analyzeTimePattern(array $requestData, array $profile): array
    {
        $result = [
            'anomalous' => false,
            'confidence' => 0.0,
            'type' => 'normal',
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
                $result['confidence'] = 0.4;
                $result['type'] = 'unusual_time_access';
                $result['indicator'] = '在异常时间访问';
            }
        }
        
        return $result;
    }
    
    /**
     * 分析内容模式
     * 
     * @param array $requestData 请求数据
     * @param array $profile 用户档案
     * @return array 分析结果
     */
    private function analyzeContentPattern(array $requestData, array $profile): array
    {
        $result = [
            'anomalous' => false,
            'confidence' => 0.0,
            'type' => 'normal',
            'indicator' => ''
        ];
        
        $content = $requestData['content'] ?? '';
        
        // 检查内容长度
        if (strlen($content) > 10000) {
            $result['anomalous'] = true;
            $result['confidence'] = 0.3;
            $result['type'] = 'large_content';
            $result['indicator'] = '内容长度异常';
        }
        
        // 检查内容类型
        if (preg_match('/\b(admin|config|password|token|key|secret)\b/i', $content)) {
            $result['anomalous'] = true;
            $result['confidence'] = 0.7;
            $result['type'] = 'sensitive_content';
            $result['indicator'] = '访问敏感内容';
        }
        
        return $result;
    }
    
    /**
     * 计算页面深度
     * 
     * @param string $url URL
     * @return int 页面深度
     */
    private function calculatePageDepth(string $url): int
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return 0;
        }
        
        $segments = explode('/', trim($path, '/'));
        return count($segments);
    }
    
    /**
     * 执行AI检测
     * 
     * @param array $requestData 请求数据
     * @return array 检测结果
     */
    private function performAIDetection(array $requestData): array
    {
        $result = [
            'ai_detected' => false,
            'confidence' => 0.0,
            'features' => [],
            'prediction' => 'human'
        ];
        
        if (!$this->config['ai_settings']['ai_based']) {
            return $result;
        }
        
        // 提取特征
        $features = $this->extractAIFeatures($requestData);
        $result['features'] = $features;
        
        // 使用AI模型预测
        $prediction = $this->behaviorAnalyzer->predict($features);
        $result['prediction'] = $prediction['class'] ?? 'human';
        $result['confidence'] = $prediction['confidence'] ?? 0.0;
        
        if ($result['prediction'] === 'crawler' && $result['confidence'] > $this->config['thresholds']['crawler_confidence']) {
            $result['ai_detected'] = true;
        }
        
        return $result;
    }
    
    /**
     * 提取AI特征
     * 
     * @param array $requestData 请求数据
     * @return array 特征向量
     */
    private function extractAIFeatures(array $requestData): array
    {
        $features = [];
        
        // 基本特征
        $features['request_frequency'] = $this->calculateRequestFrequency($requestData);
        $features['session_duration'] = $this->calculateSessionDuration($requestData);
        $features['page_depth'] = $this->calculatePageDepth($requestData['url'] ?? '');
        
        // 行为特征
        $features['javascript_enabled'] = $requestData['javascript_enabled'] ?? true ? 1.0 : 0.0;
        $features['cookies_enabled'] = $requestData['cookies_enabled'] ?? true ? 1.0 : 0.0;
        $features['images_enabled'] = $requestData['images_enabled'] ?? true ? 1.0 : 0.0;
        $features['mouse_movement'] = $requestData['mouse_movement'] ?? true ? 1.0 : 0.0;
        $features['keyboard_events'] = $requestData['keyboard_events'] ?? true ? 1.0 : 0.0;
        
        // 时间特征
        $hour = (int)date('H', time());
        $features['hour'] = $hour / 24.0; // 归一化到0-1
        
        // 内容特征
        $content = $requestData['content'] ?? '';
        $features['content_length'] = strlen($content) / 10000.0; // 归一化
        $features['content_entropy'] = $this->calculateContentEntropy($content);
        
        return $features;
    }
    
    /**
     * 计算请求频率
     * 
     * @param array $requestData 请求数据
     * @return float 请求频率
     */
    private function calculateRequestFrequency(array $requestData): float
    {
        $ip = $requestData['ip'] ?? '';
        $currentTime = time();
        
        if (!isset($this->userProfiles[$ip])) {
            return 0.0;
        }
        
        $profile = $this->userProfiles[$ip];
        $recentRequests = array_filter($profile['requests'] ?? [], function($timestamp) use ($currentTime) {
            return $currentTime - $timestamp < 60; // 1分钟内
        });
        
        return count($recentRequests) / 60.0; // 每秒请求数
    }
    
    /**
     * 计算会话持续时间
     * 
     * @param array $requestData 请求数据
     * @return float 会话持续时间
     */
    private function calculateSessionDuration(array $requestData): float
    {
        $ip = $requestData['ip'] ?? '';
        
        if (!isset($this->userProfiles[$ip])) {
            return 0.0;
        }
        
        $profile = $this->userProfiles[$ip];
        $firstRequest = min($profile['requests'] ?? [time()]);
        $currentTime = time();
        
        return ($currentTime - $firstRequest) / 3600.0; // 小时
    }
    
    /**
     * 计算内容熵
     * 
     * @param string $content 内容
     * @return float 熵值
     */
    private function calculateContentEntropy(string $content): float
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
     * 执行蜜罐检测
     * 
     * @param array $requestData 请求数据
     * @return array 检测结果
     */
    private function performHoneypotDetection(array $requestData): array
    {
        $result = [
            'honeypot_triggered' => false,
            'confidence' => 0.0,
            'trap_type' => 'none'
        ];
        
        if (!$this->config['honeypot']['enabled']) {
            return $result;
        }
        
        $url = $requestData['url'] ?? '';
        
        // 检查是否访问了蜜罐URL
        foreach ($this->config['honeypot']['trap_urls'] as $trapUrl) {
            if (strpos($url, $trapUrl) !== false) {
                $result['honeypot_triggered'] = true;
                $result['confidence'] = 0.9;
                $result['trap_type'] = 'url_trap';
                break;
            }
        }
        
        return $result;
    }
    
    /**
     * 综合检测结果
     * 
     * @param array $baseResult 基础结果
     * @param array $signatureResult 签名检测结果
     * @param array $behaviorResult 行为检测结果
     * @param array $aiResult AI检测结果
     * @param array $honeypotResult 蜜罐检测结果
     * @return array 综合结果
     */
    private function combineDetectionResults(
        array $baseResult,
        array $signatureResult,
        array $behaviorResult,
        array $aiResult,
        array $honeypotResult
    ): array {
        $crawlerScore = 0.0;
        $confidence = 0.0;
        $detectionMethods = [];
        
        // 签名检测权重
        if ($signatureResult['detected']) {
            $crawlerScore += 0.4;
            $confidence = max($confidence, $signatureResult['confidence']);
            $detectionMethods[] = 'signature';
        }
        
        // 行为检测权重
        if ($behaviorResult['anomalous_behavior']) {
            $crawlerScore += 0.3;
            $confidence = max($confidence, $behaviorResult['confidence']);
            $detectionMethods[] = 'behavior';
        }
        
        // AI检测权重
        if ($aiResult['ai_detected']) {
            $crawlerScore += 0.2;
            $confidence = max($confidence, $aiResult['confidence']);
            $detectionMethods[] = 'ai';
        }
        
        // 蜜罐检测权重
        if ($honeypotResult['honeypot_triggered']) {
            $crawlerScore += 0.1;
            $confidence = max($confidence, $honeypotResult['confidence']);
            $detectionMethods[] = 'honeypot';
        }
        
        // 确定是否为爬虫
        $isCrawler = $crawlerScore >= 0.5;
        
        // 确定威胁级别
        $threatLevel = 'low';
        if ($crawlerScore >= 0.8) {
            $threatLevel = 'critical';
        } elseif ($crawlerScore >= 0.6) {
            $threatLevel = 'high';
        } elseif ($crawlerScore >= 0.4) {
            $threatLevel = 'medium';
        }
        
        // 确定是否建议阻止
        $blockRecommended = $crawlerScore >= 0.7 || $honeypotResult['honeypot_triggered'];
        
        return array_merge($baseResult, [
            'is_crawler' => $isCrawler,
            'confidence' => $confidence,
            'threat_level' => $threatLevel,
            'block_recommended' => $blockRecommended,
            'detection_methods' => $detectionMethods
        ]);
    }
    
    /**
     * 更新用户档案
     * 
     * @param array $requestData 请求数据
     * @param array $detectionResult 检测结果
     */
    private function updateUserProfile(array $requestData, array $detectionResult): void
    {
        $ip = $requestData['ip'] ?? '';
        $currentTime = time();
        
        if (!isset($this->userProfiles[$ip])) {
            $this->userProfiles[$ip] = [
                'ip' => $ip,
                'first_seen' => $currentTime,
                'last_seen' => $currentTime,
                'requests' => [],
                'recent_urls' => [],
                'usual_urls' => [],
                'usual_times' => [],
                'crawler_detections' => 0,
                'total_requests' => 0
            ];
        }
        
        $profile = &$this->userProfiles[$ip];
        $profile['last_seen'] = $currentTime;
        $profile['requests'][] = $currentTime;
        $profile['total_requests']++;
        
        // 更新最近访问的URL
        $url = $requestData['url'] ?? '';
        if (!empty($url)) {
            $profile['recent_urls'][] = $url;
            if (!in_array($url, $profile['usual_urls'])) {
                $profile['usual_urls'][] = $url;
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
        }
        
        // 更新爬虫检测计数
        if ($detectionResult['is_crawler']) {
            $profile['crawler_detections']++;
        }
        
        // 限制历史数据大小
        if (count($profile['requests']) > 1000) {
            array_shift($profile['requests']);
        }
        
        if (count($profile['recent_urls']) > 100) {
            array_shift($profile['recent_urls']);
        }
        
        if (count($profile['usual_urls']) > 50) {
            array_shift($profile['usual_urls']);
        }
        
        if (count($profile['usual_times']) > 10) {
            array_shift($profile['usual_times']);
        }
    }
    
    /**
     * 执行相应行动
     * 
     * @param array $detectionResult 检测结果
     * @return string 执行的行动
     */
    private function executeAction(array $detectionResult): string
    {
        if (!$detectionResult['block_recommended']) {
            return 'allow';
        }
        
        $ip = $detectionResult['ip'] ?? '';
        
        if ($detectionResult['threat_level'] === 'critical') {
            $this->blockedIPs[] = $ip;
            $this->logger->warning('阻止恶意爬虫IP', ['ip' => $ip]);
            return 'block';
        }
        
        if ($detectionResult['threat_level'] === 'high') {
            $this->blockedIPs[] = $ip;
            $this->logger->warning('阻止可疑爬虫IP', ['ip' => $ip]);
            return 'block';
        }
        
        if ($detectionResult['threat_level'] === 'medium') {
            $this->logger->info('增加对可疑IP的监控', ['ip' => $ip]);
            return 'monitor';
        }
        
        return 'allow';
    }
    
    /**
     * 添加IP到白名单
     * 
     * @param string $ip IP地址
     * @return bool 是否成功
     */
    public function addToWhitelist(string $ip): bool
    {
        if (!in_array($ip, $this->whitelistIPs)) {
            $this->whitelistIPs[] = $ip;
            $this->logger->info('添加IP到白名单', ['ip' => $ip]);
            return true;
        }
        
        return false;
    }
    
    /**
     * 从白名单移除IP
     * 
     * @param string $ip IP地址
     * @return bool 是否成功
     */
    public function removeFromWhitelist(string $ip): bool
    {
        $key = array_search($ip, $this->whitelistIPs);
        if ($key !== false) {
            unset($this->whitelistIPs[$key]);
            $this->whitelistIPs = array_values($this->whitelistIPs);
            $this->logger->info('从白名单移除IP', ['ip' => $ip]);
            return true;
        }
        
        return false;
    }
    
    /**
     * 添加IP到黑名单
     * 
     * @param string $ip IP地址
     * @return bool 是否成功
     */
    public function addToBlacklist(string $ip): bool
    {
        if (!in_array($ip, $this->blockedIPs)) {
            $this->blockedIPs[] = $ip;
            $this->logger->info('添加IP到黑名单', ['ip' => $ip]);
            return true;
        }
        
        return false;
    }
    
    /**
     * 从黑名单移除IP
     * 
     * @param string $ip IP地址
     * @return bool 是否成功
     */
    public function removeFromBlacklist(string $ip): bool
    {
        $key = array_search($ip, $this->blockedIPs);
        if ($key !== false) {
            unset($this->blockedIPs[$key]);
            $this->blockedIPs = array_values($this->blockedIPs);
            $this->logger->info('从黑名单移除IP', ['ip' => $ip]);
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取系统状态
     * 
     * @return array 系统状态
     */
    public function getStatus(): array
    {
        $this->performCleanup();
        
        $crawlerDetections = 0;
        $totalRequests = 0;
        
        foreach ($this->userProfiles as $profile) {
            $crawlerDetections += $profile['crawler_detections'];
            $totalRequests += $profile['total_requests'];
        }
        
        return [
            'total_profiles' => count($this->userProfiles),
            'blocked_ips' => count($this->blockedIPs),
            'whitelist_ips' => count($this->whitelistIPs),
            'crawler_detections' => $crawlerDetections,
            'total_requests' => $totalRequests,
            'detection_rate' => $totalRequests > 0 ? ($crawlerDetections / $totalRequests) * 100 : 0,
            'known_crawlers' => count($this->crawlerDatabase['known_crawlers']),
            'suspicious_patterns' => count($this->crawlerDatabase['suspicious_patterns'])
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
        
        // 清理过期的用户档案
        foreach ($this->userProfiles as $ip => $profile) {
            if ($currentTime - $profile['last_seen'] > 86400) { // 24小时无活动
                unset($this->userProfiles[$ip]);
            }
        }
        
        $this->lastCleanup = $currentTime;
    }
}
