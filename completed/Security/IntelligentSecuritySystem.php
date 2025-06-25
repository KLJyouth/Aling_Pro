<?php

declare(strict_types=1];

namespace AlingAi\Security;

use AlingAi\Services\DatabaseServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * 智能安全系统 - 全局威胁监控
 * 
 * 功能特�?
 * - 实时威胁检�?
 * - IP黑白名单管理  
 * - 行为分析
 * - 自动防护响应
 * - 安全日志记录
 * - 威胁情报集成
 */
/**
 * IntelligentSecuritySystem �?
 *
 * @package AlingAi\Security
 */
class IntelligentSecuritySystem
{
    private DatabaseServiceInterface $database;
    private LoggerInterface $logger;
    private array $config;
    private array $threatPatterns;
    private array $securityRules;
    
    // 威胁级别常量
    public const THREAT_LEVEL_LOW = 1;
    public const THREAT_LEVEL_MEDIUM = 2;
    public const THREAT_LEVEL_HIGH = 3;
    public const THREAT_LEVEL_CRITICAL = 4;
    
    // 防护动作类型
    public const ACTION_LOG = 'log';
    public const ACTION_WARN = 'warn';
    public const ACTION_BLOCK = 'block';
    public const ACTION_CAPTCHA = 'captcha';
    public const ACTION_RATE_LIMIT = 'rate_limit';
    
    /**

    
     * __construct 方法

    
     *

    
     * @param DatabaseServiceInterface $database

    
     * @param LoggerInterface $logger

    
     * @param array $config

    
     * @return void

    
     */

    
    public function __construct(
        DatabaseServiceInterface $database,
        LoggerInterface $logger,
        array $config = []
    ) {
        $this->database = $database;
        $this->logger = $logger;
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->initializeSecurityRules(];
        $this->initializeThreatPatterns(];
    }
    
    /**
     * 检查请求安全�?
     */
    /**

     * analyzeRequest 方法

     *

     * @param array $requestData

     * @return void

     */

    public function analyzeRequest(array $requestData): array
    {
        $ip = $requestData['ip'] ?? '';
        $userAgent = $requestData['user_agent'] ?? '';
        $uri = $requestData['uri'] ?? '';
        $method = $requestData['method'] ?? '';
        $headers = $requestData['headers'] ?? [];
        $payload = $requestData['payload'] ?? '';
        
        $threats = [];
        $riskScore = 0;
        
        // IP安全检�?
        $ipAnalysis = $this->analyzeIP($ip];
        if ($ipAnalysis['threat_level'] > 0) {
            $threats[] = $ipAnalysis;
            $riskScore += $ipAnalysis['risk_score'];
        }
        
        // User-Agent分析
        $uaAnalysis = $this->analyzeUserAgent($userAgent];
        if ($uaAnalysis['threat_level'] > 0) {
            $threats[] = $uaAnalysis;
            $riskScore += $uaAnalysis['risk_score'];
        }
        
        // URI路径检�?
        $uriAnalysis = $this->analyzeURI($uri];
        if ($uriAnalysis['threat_level'] > 0) {
            $threats[] = $uriAnalysis;
            $riskScore += $uriAnalysis['risk_score'];
        }
        
        // 请求头分�?
        $headerAnalysis = $this->analyzeHeaders($headers];
        if ($headerAnalysis['threat_level'] > 0) {
            $threats[] = $headerAnalysis;
            $riskScore += $headerAnalysis['risk_score'];
        }
        
        // 负载内容检�?
        $payloadAnalysis = $this->analyzePayload($payload];
        if ($payloadAnalysis['threat_level'] > 0) {
            $threats[] = $payloadAnalysis;
            $riskScore += $payloadAnalysis['risk_score'];
        }
        
        // 行为模式分析
        $behaviorAnalysis = $this->analyzeBehaviorPattern($ip, $requestData];
        if ($behaviorAnalysis['threat_level'] > 0) {
            $threats[] = $behaviorAnalysis;
            $riskScore += $behaviorAnalysis['risk_score'];
        }
        
        // 确定最终威胁级�?
        $threatLevel = $this->calculateThreatLevel($riskScore];
        
        // 记录安全事件
        $this->recordSecurityEvent([
            'ip' => $ip,
            'user_agent' => $userAgent,
            'uri' => $uri,
            'method' => $method,
            'threats' => $threats,
            'risk_score' => $riskScore,
            'threat_level' => $threatLevel,
            'timestamp' => time()
        ]];
        
        return [
            'is_safe' => $threatLevel <= self::THREAT_LEVEL_LOW,
            'threat_level' => $threatLevel,
            'risk_score' => $riskScore,
            'threats' => $threats,
            'recommended_action' => $this->getRecommendedAction($threatLevel],
            'block_request' => $threatLevel >= self::THREAT_LEVEL_HIGH
        ];
    }
    
    /**
     * IP地址安全分析
     */
    /**

     * analyzeIP 方法

     *

     * @param string $ip

     * @return void

     */

    private function analyzeIP(string $ip): array
    {
        $result = [
            'type' => 'ip_analysis',
            'threat_level' => 0,
            'risk_score' => 0,
            'details' => []
        ];
        
        if (empty($ip)) {
            return $result;
        }
        
        // 检查IP黑名�?
        if ($this->isBlacklistedIP($ip)) {
            $result['threat_level'] = self::THREAT_LEVEL_CRITICAL;
            $result['risk_score'] = 100;
            $result['details'][] = 'IP在黑名单�?;
            return $result;
        }
        
        // 检查IP白名�?
        if ($this->isWhitelistedIP($ip)) {
            return $result; // 白名单IP直接通过
        }
        
        // 检查IP地理位置
        $geoData = $this->getIPGeolocation($ip];
        if ($geoData && in_[$geoData['country'],  $this->config['blocked_countries'])) {
            $result['threat_level'] = self::THREAT_LEVEL_MEDIUM;
            $result['risk_score'] = 30;
            $result['details'][] = "来自受限制国�? {$geoData['country']}";
        }
        
        // 检查IP声誉
        $reputation = $this->checkIPReputation($ip];
        if ($reputation['is_malicious']) {
            $result['threat_level'] = max($result['threat_level'],  self::THREAT_LEVEL_HIGH];
            $result['risk_score'] += $reputation['risk_score'];
            $result['details'][] = "恶意IP: {$reputation['reason']}";
        }
        
        // 检查请求频�?
        $frequency = $this->getIPRequestFrequency($ip];
        if ($frequency > $this->config['max_requests_per_minute']) {
            $result['threat_level'] = max($result['threat_level'],  self::THREAT_LEVEL_MEDIUM];
            $result['risk_score'] += 20;
            $result['details'][] = "请求频率过高: {$frequency}/分钟";
        }
        
        return $result;
    }
    
    /**
     * User-Agent分析
     */
    /**

     * analyzeUserAgent 方法

     *

     * @param string $userAgent

     * @return void

     */

    private function analyzeUserAgent(string $userAgent): array
    {
        $result = [
            'type' => 'user_agent_analysis',
            'threat_level' => 0,
            'risk_score' => 0,
            'details' => []
        ];
        
        if (empty($userAgent)) {
            $result['threat_level'] = self::THREAT_LEVEL_LOW;
            $result['risk_score'] = 10;
            $result['details'][] = '缺少User-Agent�?;
            return $result;
        }
        
        // 检查恶意User-Agent模式
        foreach ($this->threatPatterns['user_agents'] as $pattern) {
            if (preg_match($pattern['pattern'],  $userAgent)) {
                $result['threat_level'] = max($result['threat_level'],  $pattern['threat_level']];
                $result['risk_score'] += $pattern['risk_score'];
                $result['details'][] = "匹配恶意模式: {$pattern['description']}";
            }
        }
        
        // 检查异常短User-Agent
        if (strlen($userAgent) < 20) {
            $result['threat_level'] = max($result['threat_level'],  self::THREAT_LEVEL_LOW];
            $result['risk_score'] += 5;
            $result['details'][] = 'User-Agent异常�?;
        }
        
        return $result;
    }
    
    /**
     * URI路径分析
     */
    /**

     * analyzeURI 方法

     *

     * @param string $uri

     * @return void

     */

    private function analyzeURI(string $uri): array
    {
        $result = [
            'type' => 'uri_analysis',
            'threat_level' => 0,
            'risk_score' => 0,
            'details' => []
        ];
        
        // 检查恶意URI模式
        foreach ($this->threatPatterns['uris'] as $pattern) {
            if (preg_match($pattern['pattern'],  $uri)) {
                $result['threat_level'] = max($result['threat_level'],  $pattern['threat_level']];
                $result['risk_score'] += $pattern['risk_score'];
                $result['details'][] = "匹配攻击模式: {$pattern['description']}";
            }
        }
        
        // 检查路径遍历攻�?
        if (preg_match('/\.\.\/|\.\.\\\\/', $uri)) {
            $result['threat_level'] = self::THREAT_LEVEL_HIGH;
            $result['risk_score'] += 50;
            $result['details'][] = '可能的路径遍历攻�?;
        }
        
        // 检查异常长URI
        if (strlen($uri) > 2000) {
            $result['threat_level'] = max($result['threat_level'],  self::THREAT_LEVEL_MEDIUM];
            $result['risk_score'] += 15;
            $result['details'][] = 'URI长度异常';
        }
        
        return $result;
    }
    
    /**
     * 请求头分�?
     */
    /**

     * analyzeHeaders 方法

     *

     * @param array $headers

     * @return void

     */

    private function analyzeHeaders(array $headers): array
    {
        $result = [
            'type' => 'headers_analysis',
            'threat_level' => 0,
            'risk_score' => 0,
            'details' => []
        ];
        
        // 检查恶意头�?
        foreach ($headers as $name => $value) {
            $headerName = strtolower($name];
            
            // 检查注入攻�?
            if (preg_match('/[<>"\'\(\];]/', $value)) {
                $result['threat_level'] = max($result['threat_level'],  self::THREAT_LEVEL_MEDIUM];
                $result['risk_score'] += 20;
                $result['details'][] = "可疑头部内容: {$headerName}";
            }
            
            // 检查特定攻击模�?
            foreach ($this->threatPatterns['headers'] as $pattern) {
                if ($headerName === $pattern['header'] && preg_match($pattern['pattern'],  $value)) {
                    $result['threat_level'] = max($result['threat_level'],  $pattern['threat_level']];
                    $result['risk_score'] += $pattern['risk_score'];
                    $result['details'][] = "恶意头部: {$pattern['description']}";
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 请求负载分析
     */
    /**

     * analyzePayload 方法

     *

     * @param string $payload

     * @return void

     */

    private function analyzePayload(string $payload): array
    {
        $result = [
            'type' => 'payload_analysis',
            'threat_level' => 0,
            'risk_score' => 0,
            'details' => []
        ];
        
        if (empty($payload)) {
            return $result;
        }
        
        // 检查SQL注入
        if (preg_match('/\b(union|select|insert|update|delete|drop|create|alter)\b/i', $payload)) {
            $result['threat_level'] = self::THREAT_LEVEL_HIGH;
            $result['risk_score'] += 60;
            $result['details'][] = '可能的SQL注入攻击';
        }
        
        // 检查XSS攻击
        if (preg_match('/<script|javascript:|onload=|onerror=/i', $payload)) {
            $result['threat_level'] = self::THREAT_LEVEL_HIGH;
            $result['risk_score'] += 50;
            $result['details'][] = '可能的XSS攻击';
        }
        
        // 检查命令注�?
        if (preg_match('/\b(exec|system|shell_exec|passthru)\b/i', $payload)) {
            $result['threat_level'] = self::THREAT_LEVEL_CRITICAL;
            $result['risk_score'] += 80;
            $result['details'][] = '可能的命令注入攻�?;
        }
        
        return $result;
    }
    
    /**
     * 行为模式分析
     */
    /**

     * analyzeBehaviorPattern 方法

     *

     * @param string $ip

     * @param array $requestData

     * @return void

     */

    private function analyzeBehaviorPattern(string $ip, array $requestData): array
    {
        $result = [
            'type' => 'behavior_analysis',
            'threat_level' => 0,
            'risk_score' => 0,
            'details' => []
        ];
        
        // 获取最近的请求历史
        $recentRequests = $this->getRecentRequests($ip, 300]; // 5分钟�?
        
        // 检查请求频率异�?
        if (count($recentRequests) > $this->config['max_requests_per_5min']) {
            $result['threat_level'] = self::THREAT_LEVEL_MEDIUM;
            $result['risk_score'] += 25;
            $result['details'][] = '请求频率异常�?;
        }
        
        // 检查访问模式异�?
        $uniqueUris = array_unique(array_column($recentRequests, 'uri')];
        if (count($uniqueUris) > 50) {
            $result['threat_level'] = max($result['threat_level'],  self::THREAT_LEVEL_MEDIUM];
            $result['risk_score'] += 20;
            $result['details'][] = '扫描行为检�?;
        }
        
        // 检查错误请求比�?
        $errorRequests = array_filter($recentRequests, function($req) {
            return $req['status_code'] >= 400;
        }];
        
        if (count($recentRequests) > 10 && count($errorRequests) / count($recentRequests) > 0.5) {
            $result['threat_level'] = max($result['threat_level'],  self::THREAT_LEVEL_MEDIUM];
            $result['risk_score'] += 15;
            $result['details'][] = '高错误率请求';
        }
        
        return $result;
    }
    
    /**
     * 计算威胁级别
     */
    /**

     * calculateThreatLevel 方法

     *

     * @param int $riskScore

     * @return void

     */

    private function calculateThreatLevel(int $riskScore): int
    {
        if ($riskScore >= 80) {
            return self::THREAT_LEVEL_CRITICAL;
        } elseif ($riskScore >= 50) {
            return self::THREAT_LEVEL_HIGH;
        } elseif ($riskScore >= 20) {
            return self::THREAT_LEVEL_MEDIUM;
        } else {
            return self::THREAT_LEVEL_LOW;
        }
    }
    
    /**
     * 获取推荐防护动作
     */
    /**

     * getRecommendedAction 方法

     *

     * @param int $threatLevel

     * @return void

     */

    private function getRecommendedAction(int $threatLevel): string
    {
        switch ($threatLevel) {
            case self::THREAT_LEVEL_CRITICAL:
                return self::ACTION_BLOCK;
            case self::THREAT_LEVEL_HIGH:
                return self::ACTION_CAPTCHA;
            case self::THREAT_LEVEL_MEDIUM:
                return self::ACTION_RATE_LIMIT;
            case self::THREAT_LEVEL_LOW:
                return self::ACTION_WARN;
            default:
                return self::ACTION_LOG;
        }
    }
    
    /**
     * IP黑名单管�?
     */
    /**

     * addToBlacklist 方法

     *

     * @param string $ip

     * @param string $reason

     * @return void

     */

    public function addToBlacklist(string $ip, string $reason = ''): bool
    {
        try {
            $this->database->insert('security_blacklist', [
                'ip' => $ip,
                'reason' => $reason,
                'created_at' => date('Y-m-d H:i:s'],
                'expires_at' => date('Y-m-d H:i:s', time() + $this->config['blacklist_duration'])
            ]];
            
            $this->logger->info("IP {$ip} 已添加到黑名�?, ['reason' => $reason]];
            return true;
        } catch (\Exception $e) {
            $this->logger->error("添加IP到黑名单失败: " . $e->getMessage()];
            return false;
        }
    }
    
    /**
     * 检查IP是否在黑名单�?
     */
    /**

     * isBlacklistedIP 方法

     *

     * @param string $ip

     * @return void

     */

    private function isBlacklistedIP(string $ip): bool
    {
        try {
            $result = $this->database->find('security_blacklist', [
                'ip' => $ip,
                'expires_at' => ['>', date('Y-m-d H:i:s')]
            ]];
            
            return !empty($result];
        } catch (\Exception $e) {
            $this->logger->error("检查IP黑名单失�? " . $e->getMessage()];
            return false;
        }
    }
    
    /**
     * 检查IP是否在白名单�?
     */
    /**

     * isWhitelistedIP 方法

     *

     * @param string $ip

     * @return void

     */

    private function isWhitelistedIP(string $ip): bool
    {
        return in_[$ip, $this->config['whitelist_ips']];
    }
    
    /**
     * 获取IP地理位置信息
     */
    /**

     * getIPGeolocation 方法

     *

     * @param string $ip

     * @return void

     */

    private function getIPGeolocation(string $ip): ?array
    {
        // 这里可以集成第三方IP地理位置服务
        // 如MaxMind GeoIP、IPinfo�?
        return null;
    }
    
    /**
     * 检查IP声誉
     */
    /**

     * checkIPReputation 方法

     *

     * @param string $ip

     * @return void

     */

    private function checkIPReputation(string $ip): array
    {
        // 这里可以集成威胁情报服务
        // 如AbuseIPDB、VirusTotal�?
        return ['is_malicious' => false, 'risk_score' => 0, 'reason' => ''];
    }
    
    /**
     * 获取IP请求频率
     */
    /**

     * getIPRequestFrequency 方法

     *

     * @param string $ip

     * @return void

     */

    private function getIPRequestFrequency(string $ip): int
    {
        try {
            $oneMinuteAgo = date('Y-m-d H:i:s', time() - 60];
            $requests = $this->database->find('security_logs', [
                'ip' => $ip,
                'created_at' => ['>', $oneMinuteAgo]
            ]];
            
            return count($requests];
        } catch (\Exception $e) {
            $this->logger->error("获取IP请求频率失败: " . $e->getMessage()];
            return 0;
        }
    }
    
    /**
     * 获取最近请求记�?
     */
    /**

     * getRecentRequests 方法

     *

     * @param string $ip

     * @param int $seconds

     * @return void

     */

    private function getRecentRequests(string $ip, int $seconds): array
    {
        try {
            $timeAgo = date('Y-m-d H:i:s', time() - $seconds];
            return $this->database->find('security_logs', [
                'ip' => $ip,
                'created_at' => ['>', $timeAgo]
            ]];
        } catch (\Exception $e) {
            $this->logger->error("获取最近请求记录失�? " . $e->getMessage()];
            return [];
        }
    }
    
    /**
     * 记录安全事件
     */
    /**

     * recordSecurityEvent 方法

     *

     * @param array $eventData

     * @return void

     */

    private function recordSecurityEvent(array $eventData): void
    {
        try {
            $this->database->insert('security_logs', [
                'ip' => $eventData['ip'], 
                'user_agent' => $eventData['user_agent'], 
                'uri' => $eventData['uri'], 
                'method' => $eventData['method'], 
                'threat_level' => $eventData['threat_level'], 
                'risk_score' => $eventData['risk_score'], 
                'threats' => json_encode($eventData['threats']],
                'created_at' => date('Y-m-d H:i:s', $eventData['timestamp'])
            ]];
        } catch (\Exception $e) {
            $this->logger->error("记录安全事件失败: " . $e->getMessage()];
        }
    }
    
    /**
     * 获取默认配置
     */
    /**

     * getDefaultConfig 方法

     *

     * @return void

     */

    private function getDefaultConfig(): array
    {
        return [
            'max_requests_per_minute' => 60,
            'max_requests_per_5min' => 300,
            'blacklist_duration' => 3600, // 1小时
            'blocked_countries' => ['CN', 'RU', 'KP'],  // 可根据需要调�?
            'whitelist_ips' => ['127.0.0.1', '::1'], 
            'enable_geo_blocking' => false,
            'enable_reputation_check' => true,
            'auto_blacklist_threshold' => 80
        ];
    }
    
    /**
     * 初始化威胁模�?
     */
    /**

     * initializeThreatPatterns 方法

     *

     * @return void

     */

    private function initializeThreatPatterns(): void
    {
        $this->threatPatterns = [
            'user_agents' => [
                [
                    'pattern' => '/sqlmap|nikto|nmap|masscan|zap|burp/i',
                    'threat_level' => self::THREAT_LEVEL_HIGH,
                    'risk_score' => 50,
                    'description' => '扫描工具'
                ], 
                [
                    'pattern' => '/bot|crawler|spider/i',
                    'threat_level' => self::THREAT_LEVEL_LOW,
                    'risk_score' => 5,
                    'description' => '机器�?爬虫'
                ]
            ], 
            'uris' => [
                [
                    'pattern' => '/\.(php|asp|jsp|cgi)\?.*(\||;|&|`)/i',
                    'threat_level' => self::THREAT_LEVEL_HIGH,
                    'risk_score' => 60,
                    'description' => '命令注入尝试'
                ], 
                [
                    'pattern' => '/\/wp-admin|\/admin|\/phpmyadmin/i',
                    'threat_level' => self::THREAT_LEVEL_MEDIUM,
                    'risk_score' => 20,
                    'description' => '管理页面扫描'
                ]
            ], 
            'headers' => [
                [
                    'header' => 'x-forwarded-for',
                    'pattern' => '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b.*,.*,/',
                    'threat_level' => self::THREAT_LEVEL_MEDIUM,
                    'risk_score' => 15,
                    'description' => '可能的IP伪�?
                ]
            ]
        ];
    }
    
    /**
     * 初始化安全规�?
     */
    /**

     * initializeSecurityRules 方法

     *

     * @return void

     */

    private function initializeSecurityRules(): void
    {
        $this->securityRules = [
            'rate_limit' => [
                'requests_per_minute' => 60,
                'requests_per_hour' => 1000,
                'burst_allowance' => 10
            ], 
            'auto_ban' => [
                'enabled' => true,
                'threshold_score' => 80,
                'ban_duration' => 3600
            ], 
            'geo_filtering' => [
                'enabled' => false,
                'allowed_countries' => [], 
                'blocked_countries' => []
            ]
        ];
    }
    
    /**
     * 获取安全统计信息
     */
    /**

     * getSecurityStats 方法

     *

     * @return void

     */

    public function getSecurityStats(): array
    {
        try {
            $today = date('Y-m-d'];
            $thisWeek = date('Y-m-d', strtotime('-7 days')];
            
            return [
                'today_threats' => $this->database->count('security_logs', [
                    'created_at' => ['>=', $today . ' 00:00:00'], 
                    'threat_level' => ['>', self::THREAT_LEVEL_LOW]
                ]],
                'week_threats' => $this->database->count('security_logs', [
                    'created_at' => ['>=', $thisWeek . ' 00:00:00'], 
                    'threat_level' => ['>', self::THREAT_LEVEL_LOW]
                ]],
                'blacklisted_ips' => $this->database->count('security_blacklist', [
                    'expires_at' => ['>', date('Y-m-d H:i:s')]
                ]],
                'top_threat_ips' => $this->getTopThreatIPs(),
                'threat_types' => $this->getThreatTypeStats()
            ];
        } catch (\Exception $e) {
            $this->logger->error("获取安全统计失败: " . $e->getMessage()];
            return [];
        }
    }
    
    /**
     * 获取主要威胁IP
     */
    /**

     * getTopThreatIPs 方法

     *

     * @return void

     */

    private function getTopThreatIPs(): array
    {
        try {
            // 这里需要实现SQL查询逻辑
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取威胁类型统计
     */
    /**

     * getThreatTypeStats 方法

     *

     * @return void

     */

    private function getThreatTypeStats(): array
    {
        try {
            // 这里需要实现威胁类型统计逻辑
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
}

