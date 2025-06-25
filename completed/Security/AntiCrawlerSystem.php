<?php

namespace AlingAi\Security;

use AlingAi\Core\Logger;
use Exception;

/**
 * 反爬虫和链接保护系统
 * 实现链接加密、防盗链、反爬虫等安全功�?
 */
/**
 * AntiCrawlerSystem �?
 *
 * @package AlingAi\Security
 */
class AntiCrawlerSystem
{
    private $config;
    private $logger;
    private $suspiciousIPs = [];
    private $blockedIPs = [];
    private $encryptionKey;
    
    // 爬虫特征检测规�?
    private $botSignatures = [
        'user_agents' => [
            'bot', 'crawler', 'spider', 'scraper', 'wget', 'curl',
            'python-requests', 'scrapy', 'selenium', 'headless',
            'phantom', 'nightmare', 'puppeteer'
        ], 
        'headers' => [
            'x-scrapy-callback',
            'x-crawler-id',
            'x-bot-id'
        ], 
        'suspicious_patterns' => [
            'rapid_requests',
            'missing_referer',
            'suspicious_user_agent',
            'automated_behavior'
        ]
    ];
    
    // 防护策略配置
    private $protectionStrategies = [
        'rate_limiting' => true,
        'link_encryption' => true,
        'referer_check' => true,
        'captcha_challenge' => true,
        'honeypot_trap' => true,
        'fingerprint_detection' => true
    ];

    /**


     * __construct 方法


     *


     * @param mixed $config


     * @return void


     */


    public function __construct($config = [])
    {
        $this->config = array_merge([
            'max_requests_per_minute' => 60,
            'max_requests_per_hour' => 1000,
            'block_duration' => 3600, // 1小时
            'encryption_algorithm' => 'AES-256-CBC',
            'link_expiry_time' => 3600, // 1小时
            'captcha_threshold' => 10, // 触发验证码的可疑行为次数
            'honeypot_enabled' => true,
            'log_all_requests' => false
        ],  $config];
        
        $this->logger = new Logger('AntiCrawlerSystem'];
        $this->encryptionKey = $this->generateEncryptionKey(];
        $this->initializeProtection(];
    }

    /**
     * 初始化保护系�?
     */
    /**

     * initializeProtection 方法

     *

     * @return void

     */

    private function initializeProtection()
    {
        // 加载已阻止的IP列表
        $this->loadBlockedIPs(];
        
        // 设置蜜罐陷阱
        if ($this->config['honeypot_enabled']) {
            $this->setupHoneypot(];
        }
        
        $this->logger->info('反爬虫系统初始化完成'];
    }

    /**
     * 主要请求检查入�?
     */
    /**

     * checkRequest 方法

     *

     * @param mixed $request

     * @return void

     */

    public function checkRequest($request)
    {
        $clientIP = $this->getClientIP($request];
        $userAgent = $request['HTTP_USER_AGENT'] ?? '';
        $referer = $request['HTTP_REFERER'] ?? '';
        $requestUri = $request['REQUEST_URI'] ?? '';
        
        // 检查是否在黑名单中
        if ($this->isBlocked($clientIP)) {
            $this->handleBlocked($clientIP, 'IP已被阻止'];
            return false;
        }
        
        // 计算风险评分
        $riskScore = $this->calculateRiskScore($request];
        
        // 执行各种检�?
        $checks = [
            'rate_limit' => $this->checkRateLimit($clientIP],
            'bot_detection' => $this->detectBot($userAgent, $request],
            'referer_validation' => $this->validateReferer($referer, $requestUri],
            'behavior_analysis' => $this->analyzeBehavior($clientIP, $request],
            'fingerprint_check' => $this->checkFingerprint($request)
        ];
        
        // 处理检查结�?
        $suspiciousCount = array_sum($checks];
        
        if ($suspiciousCount >= 3) {
            $this->handleSuspicious($clientIP, $checks, $riskScore];
            return false;
        } elseif ($suspiciousCount >= 1) {
            $this->logSuspiciousActivity($clientIP, $checks, $riskScore];
        }
        
        // 记录正常访问
        $this->recordAccess($clientIP, $request];
        
        return true;
    }

    /**
     * 计算风险评分
     */
    /**

     * calculateRiskScore 方法

     *

     * @param mixed $request

     * @return void

     */

    private function calculateRiskScore($request)
    {
        $score = 0;
        $userAgent = $request['HTTP_USER_AGENT'] ?? '';
        $referer = $request['HTTP_REFERER'] ?? '';
        
        // 用户代理评分
        if (empty($userAgent)) {
            $score += 30;
        } elseif ($this->isSuspiciousUserAgent($userAgent)) {
            $score += 20;
        }
        
        // Referer评分
        if (empty($referer) && !$this->isDirectAccess($request)) {
            $score += 15;
        }
        
        // 请求头评�?
        if ($this->hasSuspiciousHeaders($request)) {
            $score += 25;
        }
        
        // 请求频率评分
        $requestFrequency = $this->getRequestFrequency($this->getClientIP($request)];
        if ($requestFrequency > $this->config['max_requests_per_minute']) {
            $score += 40;
        }
        
        return min($score, 100];
    }

    /**
     * 检查速率限制
     */
    /**

     * checkRateLimit 方法

     *

     * @param mixed $clientIP

     * @return void

     */

    private function checkRateLimit($clientIP)
    {
        $currentMinute = date('Y-m-d H:i'];
        $currentHour = date('Y-m-d H'];
        
        $minuteKey = "rate_limit_minute_{$clientIP}_{$currentMinute}";
        $hourKey = "rate_limit_hour_{$clientIP}_{$currentHour}";
        
        $minuteCount = $this->getCounter($minuteKey];
        $hourCount = $this->getCounter($hourKey];
        
        $this->incrementCounter($minuteKey, 60];
        $this->incrementCounter($hourKey, 3600];
        
        if ($minuteCount > $this->config['max_requests_per_minute'] ||
            $hourCount > $this->config['max_requests_per_hour']) {
            
            $this->logger->warning("速率限制触发: IP {$clientIP}, 分钟请求: {$minuteCount}, 小时请求: {$hourCount}"];
            return true;
        }
        
        return false;
    }

    /**
     * 检测机器人
     */
    /**

     * detectBot 方法

     *

     * @param mixed $userAgent

     * @param mixed $request

     * @return void

     */

    private function detectBot($userAgent, $request)
    {
        // 检查用户代�?
        if ($this->isSuspiciousUserAgent($userAgent)) {
            return true;
        }
        
        // 检查请求头
        if ($this->hasSuspiciousHeaders($request)) {
            return true;
        }
        
        // 检查自动化行为模式
        if ($this->detectAutomatedBehavior($request)) {
            return true;
        }
        
        return false;
    }

    /**
     * 检查可疑用户代�?
     */
    /**

     * isSuspiciousUserAgent 方法

     *

     * @param mixed $userAgent

     * @return void

     */

    private function isSuspiciousUserAgent($userAgent)
    {
        $userAgent = strtolower($userAgent];
        
        foreach ($this->botSignatures['user_agents'] as $signature) {
            if (strpos($userAgent, $signature) !== false) {
                return true;
            }
        }
        
        // 检查是否过于简单或缺失
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return true;
        }
        
        // 检查是否包含常见浏览器标识
        $browserSignatures = ['mozilla', 'chrome', 'safari', 'firefox', 'edge'];
        $hasBrowserSignature = false;
        
        foreach ($browserSignatures as $browser) {
            if (strpos($userAgent, $browser) !== false) {
                $hasBrowserSignature = true;
                break;
            }
        }
        
        return !$hasBrowserSignature;
    }

    /**
     * 检查可疑请求头
     */
    /**

     * hasSuspiciousHeaders 方法

     *

     * @param mixed $request

     * @return void

     */

    private function hasSuspiciousHeaders($request)
    {
        foreach ($this->botSignatures['headers'] as $header) {
            if (isset($request[strtoupper(str_replace('-', '_', $header))])) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检测自动化行为
     */
    /**

     * detectAutomatedBehavior 方法

     *

     * @param mixed $request

     * @return void

     */

    private function detectAutomatedBehavior($request)
    {
        $clientIP = $this->getClientIP($request];
        
        // 检查请求时间间隔过于规�?
        $lastRequestTimes = $this->getLastRequestTimes($clientIP, 10];
        if (count($lastRequestTimes) >= 5) {
            $intervals = [];
            for ($i = 1; $i < count($lastRequestTimes]; $i++) {
                $intervals[] = $lastRequestTimes[$i] - $lastRequestTimes[$i-1];
            }
            
            // 如果时间间隔过于规律（方差很小），可能是自动�?
            $avgInterval = array_sum($intervals) / count($intervals];
            $variance = 0;
            foreach ($intervals as $interval) {
                $variance += pow($interval - $avgInterval, 2];
            }
            $variance /= count($intervals];
            
            if ($variance < 0.1 && $avgInterval < 5) { // 方差小于0.1秒且平均间隔小于5�?
                return true;
            }
        }
        
        return false;
    }

    /**
     * 验证Referer
     */
    /**

     * validateReferer 方法

     *

     * @param mixed $referer

     * @param mixed $requestUri

     * @return void

     */

    private function validateReferer($referer, $requestUri)
    {
        // 如果是直接访问主页，允许空referer
        if ($this->isDirectAccess(['REQUEST_URI' => $requestUri])) {
            return false;
        }
        
        // 如果referer为空，可�?
        if (empty($referer)) {
            return true;
        }
        
        // 检查referer域名是否合法
        $allowedDomains = $this->config['allowed_referer_domains'] ?? [];
        if (!empty($allowedDomains)) {
            $refererDomain = parse_url($referer, PHP_URL_HOST];
            if (!in_[$refererDomain, $allowedDomains)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 分析用户行为
     */
    /**

     * analyzeBehavior 方法

     *

     * @param mixed $clientIP

     * @param mixed $request

     * @return void

     */

    private function analyzeBehavior($clientIP, $request)
    {
        $behaviors = [
            'page_depth' => $this->checkPageDepth($clientIP],
            'session_duration' => $this->checkSessionDuration($clientIP],
            'mouse_movement' => $this->checkMouseMovement($clientIP],
            'javascript_enabled' => $this->checkJavaScriptEnabled($request)
        ];
        
        $suspiciousCount = 0;
        foreach ($behaviors as $behavior => $isSuspicious) {
            if ($isSuspicious) {
                $suspiciousCount++;
            }
        }
        
        return $suspiciousCount >= 2;
    }

    /**
     * 检查访问深�?
     */
    /**

     * checkPageDepth 方法

     *

     * @param mixed $clientIP

     * @return void

     */

    private function checkPageDepth($clientIP)
    {
        $pageViews = $this->getPageViews($clientIP];
        
        // 如果只访问特定页面且数量很大，可�?
        if (count(array_unique($pageViews)) == 1 && count($pageViews) > 20) {
            return true;
        }
        
        // 如果访问深度过浅但频率很高，可疑
        if (count(array_unique($pageViews)) < 3 && count($pageViews) > 50) {
            return true;
        }
        
        return false;
    }

    /**
     * 检查会话持续时�?
     */
    /**

     * checkSessionDuration 方法

     *

     * @param mixed $clientIP

     * @return void

     */

    private function checkSessionDuration($clientIP)
    {
        $sessionStart = $this->getSessionStart($clientIP];
        $currentTime = time(];
        
        if ($sessionStart) {
            $duration = $currentTime - $sessionStart;
            
            // 会话时间过短但请求很多，可疑
            if ($duration < 60 && $this->getRequestCount($clientIP) > 30) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检查鼠标移�?
     */
    /**

     * checkMouseMovement 方法

     *

     * @param mixed $clientIP

     * @return void

     */

    private function checkMouseMovement($clientIP)
    {
        // 这个需要前端JavaScript配合记录鼠标活动
        $mouseActivity = $this->getMouseActivity($clientIP];
        
        // 如果完全没有鼠标活动记录，可�?
        if (empty($mouseActivity) && $this->getRequestCount($clientIP) > 10) {
            return true;
        }
        
        return false;
    }

    /**
     * 检查JavaScript启用状�?
     */
    /**

     * checkJavaScriptEnabled 方法

     *

     * @param mixed $request

     * @return void

     */

    private function checkJavaScriptEnabled($request)
    {
        // 通过检查特定的JavaScript设置的cookie或header
        return !isset($request['HTTP_X_JAVASCRIPT_ENABLED']];
    }

    /**
     * 检查浏览器指纹
     */
    /**

     * checkFingerprint 方法

     *

     * @param mixed $request

     * @return void

     */

    private function checkFingerprint($request)
    {
        $fingerprint = $this->generateFingerprint($request];
        
        // 检查指纹是否已知为可疑
        if ($this->isSuspiciousFingerprint($fingerprint)) {
            return true;
        }
        
        // 检查指纹变化频�?
        $clientIP = $this->getClientIP($request];
        $previousFingerprints = $this->getPreviousFingerprints($clientIP];
        
        if (count($previousFingerprints) > 5 && 
            count(array_unique($previousFingerprints)) == count($previousFingerprints)) {
            // 指纹变化过于频繁，可�?
            return true;
        }
        
        $this->recordFingerprint($clientIP, $fingerprint];
        return false;
    }

    /**
     * 生成浏览器指�?
     */
    /**

     * generateFingerprint 方法

     *

     * @param mixed $request

     * @return void

     */

    private function generateFingerprint($request)
    {
        $components = [
            $request['HTTP_USER_AGENT'] ?? '',
            $request['HTTP_ACCEPT'] ?? '',
            $request['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $request['HTTP_ACCEPT_ENCODING'] ?? '',
            $request['HTTP_CONNECTION'] ?? ''
        ];
        
        return hash('sha256', implode('|', $components)];
    }

    /**
     * 链接加密
     */
    /**

     * encryptLink 方法

     *

     * @param mixed $url

     * @param mixed $expiryTime

     * @return void

     */

    public function encryptLink($url, $expiryTime = null)
    {
        if (!$expiryTime) {
            $expiryTime = time() + $this->config['link_expiry_time'];
        }
        
        $data = [
            'url' => $url,
            'expires' => $expiryTime,
            'timestamp' => time()
        ];
        
        $serialized = serialize($data];
        $encrypted = openssl_encrypt(
            $serialized,
            $this->config['encryption_algorithm'], 
            $this->encryptionKey,
            0,
            substr(hash('sha256', $this->encryptionKey], 0, 16)
        ];
        
        return base64_encode($encrypted];
    }

    /**
     * 解密链接
     */
    /**

     * decryptLink 方法

     *

     * @param mixed $encryptedLink

     * @return void

     */

    public function decryptLink($encryptedLink)
    {
        try {
            $encrypted = base64_decode($encryptedLink];
            $decrypted = openssl_decrypt(
                $encrypted,
                $this->config['encryption_algorithm'], 
                $this->encryptionKey,
                0,
                substr(hash('sha256', $this->encryptionKey], 0, 16)
            ];
            
            if ($decrypted === false) {
                return null;
            }
            
            $data = unserialize($decrypted];
            
            // 检查链接是否过�?
            if ($data['expires'] < time()) {
                $this->logger->info('链接已过�?];
                return null;
            }
            
            return $data['url'];
            
        } catch (Exception $e) {
            $this->logger->error('链接解密失败: ' . $e->getMessage()];
            return null;
        }
    }

    /**
     * 设置蜜罐陷阱
     */
    /**

     * setupHoneypot 方法

     *

     * @return void

     */

    private function setupHoneypot()
    {
        // 创建隐藏的蜜罐链�?
        $honeypotLinks = [
            '/robots.txt.bak',
            '/admin.php',
            '/login.php',
            '/config.php.bak',
            '/database.sql'
        ];
        
        foreach ($honeypotLinks as $link) {
            $this->createHoneypotTrap($link];
        }
    }

    /**
     * 创建蜜罐陷阱
     */
    /**

     * createHoneypotTrap 方法

     *

     * @param mixed $path

     * @return void

     */

    private function createHoneypotTrap($path)
    {
        // 记录蜜罐路径
        $honeypotFile = 'security/honeypot_paths.json';
        
        if (!is_dir(dirname($honeypotFile))) {
            mkdir(dirname($honeypotFile], 0755, true];
        }
        
        $honeypots = [];
        if (file_exists($honeypotFile)) {
            $honeypots = json_decode(file_get_contents($honeypotFile], true) ?: [];
        }
        
        if (!in_[$path, $honeypots)) {
            $honeypots[] = $path;
            file_put_contents($honeypotFile, json_encode($honeypots, JSON_PRETTY_PRINT)];
        }
    }

    /**
     * 检查是否访问了蜜罐
     */
    /**

     * checkHoneypotAccess 方法

     *

     * @param mixed $requestUri

     * @return void

     */

    public function checkHoneypotAccess($requestUri)
    {
        $honeypotFile = 'security/honeypot_paths.json';
        
        if (file_exists($honeypotFile)) {
            $honeypots = json_decode(file_get_contents($honeypotFile], true) ?: [];
            
            if (in_[$requestUri, $honeypots)) {
                $this->logger->warning("蜜罐陷阱触发: {$requestUri}"];
                return true;
            }
        }
        
        return false;
    }

    /**
     * 处理可疑行为
     */
    /**

     * handleSuspicious 方法

     *

     * @param mixed $clientIP

     * @param mixed $checks

     * @param mixed $riskScore

     * @return void

     */

    private function handleSuspicious($clientIP, $checks, $riskScore)
    {
        $this->addToSuspiciousList($clientIP, $checks, $riskScore];
        
        if ($riskScore >= 70) {
            $this->blockIP($clientIP, '高风险评�?];
        } elseif ($riskScore >= 50) {
            $this->requireCaptcha($clientIP];
        } else {
            $this->increaseSurveillance($clientIP];
        }
    }

    /**
     * 阻止IP
     */
    /**

     * blockIP 方法

     *

     * @param mixed $ip

     * @param mixed $reason

     * @return void

     */

    private function blockIP($ip, $reason)
    {
        $this->blockedIPs[$ip] = [
            'reason' => $reason,
            'blocked_at' => time(),
            'expires_at' => time() + $this->config['block_duration']
        ];
        
        $this->saveBlockedIPs(];
        $this->logger->warning("IP已被阻止: {$ip}, 原因: {$reason}"];
    }

    /**
     * 要求验证�?
     */
    /**

     * requireCaptcha 方法

     *

     * @param mixed $ip

     * @return void

     */

    private function requireCaptcha($ip)
    {
        $_SESSION['require_captcha'][$ip] = time() + 3600; // 1小时内需要验证码
        $this->logger->info("要求验证码验�? {$ip}"];
    }

    /**
     * 增加监控
     */
    /**

     * increaseSurveillance 方法

     *

     * @param mixed $ip

     * @return void

     */

    private function increaseSurveillance($ip)
    {
        $_SESSION['high_surveillance'][$ip] = time() + 1800; // 30分钟高度监控
        $this->logger->info("增加监控级别: {$ip}"];
    }

    /**
     * 获取客户端IP
     */
    /**

     * getClientIP 方法

     *

     * @param mixed $request

     * @return void

     */

    private function getClientIP($request)
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (isset($request[$key]) && !empty($request[$key])) {
                $ips = explode(',', $request[$key]];
                $ip = trim($ips[0]];
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $request['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * 生成加密密钥
     */
    /**

     * generateEncryptionKey 方法

     *

     * @return void

     */

    private function generateEncryptionKey()
    {
        $keyFile = 'security/encryption.key';
        
        if (!is_dir(dirname($keyFile))) {
            mkdir(dirname($keyFile], 0755, true];
        }
        
        if (file_exists($keyFile)) {
            return file_get_contents($keyFile];
        }
        
        $key = random_bytes(32];
        file_put_contents($keyFile, $key];
        chmod($keyFile, 0600];
        
        return $key;
    }

    /**
     * 辅助方法 - 以下方法需要根据实际存储系统实�?
     */
    /**

     * getCounter 方法

     *

     * @param mixed $key

     * @return void

     */

    private function getCounter($key) { return $_SESSION['counters'][$key] ?? 0; }
    /**

     * incrementCounter 方法

     *

     * @param mixed $key

     * @param mixed $ttl

     * @return void

     */

    private function incrementCounter($key, $ttl) { $_SESSION['counters'][$key] = ($_SESSION['counters'][$key] ?? 0) + 1; }
    /**

     * getLastRequestTimes 方法

     *

     * @param mixed $ip

     * @param mixed $limit

     * @return void

     */

    private function getLastRequestTimes($ip, $limit) { return $_SESSION['request_times'][$ip] ?? []; }
    /**

     * getPageViews 方法

     *

     * @param mixed $ip

     * @return void

     */

    private function getPageViews($ip) { return $_SESSION['page_views'][$ip] ?? []; }
    /**

     * getSessionStart 方法

     *

     * @param mixed $ip

     * @return void

     */

    private function getSessionStart($ip) { return $_SESSION['session_start'][$ip] ?? null; }
    /**

     * getRequestCount 方法

     *

     * @param mixed $ip

     * @return void

     */

    private function getRequestCount($ip) { return $_SESSION['request_count'][$ip] ?? 0; }
    /**

     * getMouseActivity 方法

     *

     * @param mixed $ip

     * @return void

     */

    private function getMouseActivity($ip) { return $_SESSION['mouse_activity'][$ip] ?? []; }
    /**

     * getPreviousFingerprints 方法

     *

     * @param mixed $ip

     * @return void

     */

    private function getPreviousFingerprints($ip) { return $_SESSION['fingerprints'][$ip] ?? []; }
    /**

     * recordFingerprint 方法

     *

     * @param mixed $ip

     * @param mixed $fingerprint

     * @return void

     */

    private function recordFingerprint($ip, $fingerprint) { $_SESSION['fingerprints'][$ip][] = $fingerprint; }
    /**

     * isSuspiciousFingerprint 方法

     *

     * @param mixed $fingerprint

     * @return void

     */

    private function isSuspiciousFingerprint($fingerprint) { return false; } // 实现指纹黑名单检�?
    /**

     * isDirectAccess 方法

     *

     * @param mixed $request

     * @return void

     */

    private function isDirectAccess($request) { return in_[$request['REQUEST_URI'],  ['/', '/index.php', '/home']]; }
    /**

     * recordAccess 方法

     *

     * @param mixed $ip

     * @param mixed $request

     * @return void

     */

    private function recordAccess($ip, $request) { $_SESSION['access_log'][$ip][] = time(]; }
    /**

     * addToSuspiciousList 方法

     *

     * @param mixed $ip

     * @param mixed $checks

     * @param mixed $score

     * @return void

     */

    private function addToSuspiciousList($ip, $checks, $score) { $_SESSION['suspicious'][$ip] = ['checks' => $checks, 'score' => $score, 'time' => time()]; }
    /**

     * isBlocked 方法

     *

     * @param mixed $ip

     * @return void

     */

    private function isBlocked($ip) { return isset($this->blockedIPs[$ip]) && $this->blockedIPs[$ip]['expires_at'] > time(]; }
    /**

     * handleBlocked 方法

     *

     * @param mixed $ip

     * @param mixed $reason

     * @return void

     */

    private function handleBlocked($ip, $reason) { http_response_code(403]; exit('Access Denied']; }
    /**

     * logSuspiciousActivity 方法

     *

     * @param mixed $ip

     * @param mixed $checks

     * @param mixed $score

     * @return void

     */

    private function logSuspiciousActivity($ip, $checks, $score) { $this->logger->warning("可疑活动: IP {$ip}, 检查结�? " . json_encode($checks) . ", 评分: {$score}"]; }
    
    /**

    
     * loadBlockedIPs 方法

    
     *

    
     * @return void

    
     */

    
    private function loadBlockedIPs()
    {
        $file = 'security/blocked_ips.json';
        if (file_exists($file)) {
            $this->blockedIPs = json_decode(file_get_contents($file], true) ?: [];
        }
    }
    
    /**

    
     * saveBlockedIPs 方法

    
     *

    
     * @return void

    
     */

    
    private function saveBlockedIPs()
    {
        $file = 'security/blocked_ips.json';
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file], 0755, true];
        }
        file_put_contents($file, json_encode($this->blockedIPs, JSON_PRETTY_PRINT)];
    }

    /**
     * 生成防护报告
     */
    /**

     * generateProtectionReport 方法

     *

     * @return void

     */

    public function generateProtectionReport()
    {
        return [
            'blocked_ips' => count($this->blockedIPs],
            'suspicious_activities' => count($_SESSION['suspicious'] ?? []],
            'honeypot_triggers' => $this->getHoneypotTriggers(),
            'protection_strategies' => $this->protectionStrategies,
            'last_update' => date('Y-m-d H:i:s')
        ];
    }

    /**


     * getHoneypotTriggers 方法


     *


     * @return void


     */


    private function getHoneypotTriggers()
    {
        // 返回蜜罐触发统计
        return $_SESSION['honeypot_triggers'] ?? 0;
    }
}

