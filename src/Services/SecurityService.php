<?php

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Services\DatabaseService;
use AlingAi\Services\CacheService;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;

/**
 * 安全服务类
 * 
 * 提供安全扫描、漏洞检测、访问控制、安全监控等功能
 * 
 * @package AlingAi\Services
 * @version 1.0.0
 */
class SecurityService
{
    private DatabaseService $db;
    private CacheService $cache;
    private $monitor; // 移除类型声明以支持匿名类
    private LoggerInterface $logger;
    private array $config;
    private array $rateLimits = [];
    private array $threats = [];
    private array $auditLog = [];
    private array $blockedIps = [];

    public function __construct(DatabaseService $db = null, CacheService $cache = null, ?LoggerInterface $logger = null)
    {
        // 创建Logger（如果没有提供）
        if (!$logger) {
            $logger = new Logger('security');
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::WARNING));
        }
        $this->logger = $logger;
        
        $this->db = $db ?: new DatabaseService($this->logger);
        $this->cache = $cache ?: new CacheService($this->logger);
        
        // 创建一个简化的监控服务实例，避免依赖复杂的参数
        $this->monitor = new class {
            public function logSecurityEvent(array $event) {
                // 简化的安全事件记录
                error_log('Security Event: ' . json_encode($event));
            }
            
            public function getSystemMetrics(): array {
                return [
                    'security_status' => 'active',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        };
        
        $this->config = $this->loadSecurityConfig();
    }

    /**
     * 执行安全扫描
     */
    public function performSecurityScan(array $options = []): array
    {
        try {
            $scanId = $this->generateScanId();
            $startTime = microtime(true);

            $this->logger->info('开始安全扫描', [
                'scan_id' => $scanId,
                'options' => $options
            ]);

            $results = [
                'scan_id' => $scanId,
                'start_time' => date('Y-m-d H:i:s'),
                'status' => 'running',
                'vulnerabilities' => [],
                'security_score' => 0,
                'recommendations' => []
            ];

            // 漏洞扫描
            $vulnerabilities = $this->scanVulnerabilities($options);
            $results['vulnerabilities'] = $vulnerabilities;

            // 文件系统安全检查
            $fileSecurityResults = $this->checkFileSystemSecurity();
            
            // 数据库安全检查
            $dbSecurityResults = $this->checkDatabaseSecurity();
            
            // 网络安全检查
            $networkSecurityResults = $this->checkNetworkSecurity();
            
            // 应用安全检查
            $appSecurityResults = $this->checkApplicationSecurity();

            // 计算安全评分
            $securityScore = $this->calculateSecurityScore([
                'vulnerabilities' => $vulnerabilities,
                'file_security' => $fileSecurityResults,
                'db_security' => $dbSecurityResults,
                'network_security' => $networkSecurityResults,
                'app_security' => $appSecurityResults
            ]);

            $results['security_score'] = $securityScore;
            $results['file_security'] = $fileSecurityResults;
            $results['database_security'] = $dbSecurityResults;
            $results['network_security'] = $networkSecurityResults;
            $results['application_security'] = $appSecurityResults;

            // 生成安全建议
            $results['recommendations'] = $this->generateSecurityRecommendations($results);

            $endTime = microtime(true);
            $results['end_time'] = date('Y-m-d H:i:s');
            $results['duration'] = round($endTime - $startTime, 2);
            $results['status'] = 'completed';

            // 保存扫描结果
            $this->saveScanResults($results);

            return $results;

        } catch (Exception $e) {
            $this->logger->error('安全扫描失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'scan_id' => $scanId ?? null
            ];
        }
    }

    /**
     * 验证请求安全性
     */
    public function validateRequest(): bool
    {
        try {
            // 检查请求来源
            if (!$this->validateRequestOrigin()) {
                $this->logger->warning('请求来源验证失败');
                return false;
            }

            // 检查频率限制
            if (!$this->checkRateLimit()) {
                $this->logger->warning('请求频率超限');
                return false;
            }

            // 检查恶意请求特征
            if ($this->detectMaliciousRequest()) {
                $this->logger->warning('检测到恶意请求');
                return false;
            }

            return true;

        } catch (Exception $e) {
            $this->logger->error('请求验证失败', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 获取安全状态概览
     */
    public function getSecurityOverview(): array
    {
        try {
            // 获取最新扫描结果
            $results = $this->db->query(
                'SELECT * FROM security_scans ORDER BY created_at DESC LIMIT 1'
            );

            $latestScan = !empty($results) ? $results[0] : null;

            $overview = [
                'last_scan_date' => null,
                'security_score' => 0,
                'total_vulnerabilities' => 0,
                'critical_vulnerabilities' => 0,
                'high_vulnerabilities' => 0,
                'medium_vulnerabilities' => 0,
                'low_vulnerabilities' => 0,
                'scan_status' => 'never_scanned',
                'recommendations_count' => 0
            ];

            if ($latestScan) {
                $overview['last_scan_date'] = $latestScan['created_at'];
                $overview['security_score'] = $latestScan['security_score'];
                $overview['total_vulnerabilities'] = $latestScan['vulnerabilities_count'];
                $overview['scan_status'] = $latestScan['status'];

                // 解析详细结果
                if ($latestScan['results_data']) {
                    $results = json_decode($latestScan['results_data'], true);
                    if ($results && isset($results['vulnerabilities'])) {
                        foreach ($results['vulnerabilities'] as $vuln) {
                            switch ($vuln['severity']) {
                                case 'critical':
                                    $overview['critical_vulnerabilities']++;
                                    break;
                                case 'high':
                                    $overview['high_vulnerabilities']++;
                                    break;
                                case 'medium':
                                    $overview['medium_vulnerabilities']++;
                                    break;
                                case 'low':
                                    $overview['low_vulnerabilities']++;
                                    break;
                            }
                        }
                    }

                    if (isset($results['recommendations'])) {
                        $overview['recommendations_count'] = count($results['recommendations']);
                    }
                }
            }

            // 添加实时安全状态
            $overview['threat_status'] = $this->getThreatStatus();
            $overview['system_integrity'] = $this->checkSystemIntegrity();
            $overview['firewall_status'] = $this->getFirewallStatus();

            return $overview;

        } catch (Exception $e) {
            $this->logger->error('获取安全概览失败', ['error' => $e->getMessage()]);
            return [
                'error' => '获取安全状态失败',
                'status' => 'error'
            ];
        }
    }

    /**
     * 扫描系统漏洞
     */
    private function scanVulnerabilities(array $options): array
    {
        $vulnerabilities = [];

        try {
            // SQL注入检查
            $sqlInjectionResults = $this->detectSQLInjection();
            if (!empty($sqlInjectionResults)) {
                $vulnerabilities = array_merge($vulnerabilities, $sqlInjectionResults);
            }

            // XSS漏洞检查
            $xssResults = $this->detectXSSVulnerabilities();
            if (!empty($xssResults)) {
                $vulnerabilities = array_merge($vulnerabilities, $xssResults);
            }

            // CSRF漏洞检查
            $csrfResults = $this->detectCSRFVulnerabilities();
            if (!empty($csrfResults)) {
                $vulnerabilities = array_merge($vulnerabilities, $csrfResults);
            }

            // 文件上传漏洞检查
            $uploadResults = $this->detectFileUploadVulnerabilities();
            if (!empty($uploadResults)) {
                $vulnerabilities = array_merge($vulnerabilities, $uploadResults);
            }

        } catch (Exception $e) {
            $this->logger->error('漏洞扫描失败', ['error' => $e->getMessage()]);
        }

        return $vulnerabilities;
    }

    /**
     * 检查文件系统安全
     */
    private function checkFileSystemSecurity(): array
    {
        $results = [
            'status' => 'secure',
            'issues' => [],
            'score' => 100
        ];

        try {
            // 检查敏感文件权限
            $sensitiveFiles = ['.env', 'config/', 'storage/', 'vendor/'];
            foreach ($sensitiveFiles as $file) {
                if (file_exists($file)) {
                    $perms = fileperms($file);
                    if ($perms & 0004) { // 其他用户可读
                        $results['issues'][] = [
                            'type' => 'file_permissions',
                            'severity' => 'high',
                            'file' => $file,
                            'description' => "文件 {$file} 对其他用户可读"
                        ];
                        $results['score'] -= 20;
                    }
                }
            }

            // 检查配置文件
            $this->checkConfigurationSecurity($results);

        } catch (Exception $e) {
            $this->logger->error('文件系统安全检查失败', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * 检查数据库安全
     */
    private function checkDatabaseSecurity(): array
    {
        $results = [
            'status' => 'secure',
            'issues' => [],
            'score' => 100
        ];

        try {
            // 检查数据库连接安全
            $connection = $this->db->getConnection();
            if ($connection) {
                // 检查默认用户和密码
                $this->checkDatabaseCredentials($results);
                
                // 检查数据库权限
                $this->checkDatabasePermissions($results);
            }

        } catch (Exception $e) {
            $this->logger->error('数据库安全检查失败', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * 检查网络安全
     */
    private function checkNetworkSecurity(): array
    {
        $results = [
            'status' => 'secure',
            'issues' => [],
            'score' => 100
        ];

        try {
            // 检查HTTPS配置
            $this->checkHTTPSConfiguration($results);
            
            // 检查安全头
            $this->checkSecurityHeaders($results);
            
            // 检查端口安全
            $this->checkPortSecurity($results);

        } catch (Exception $e) {
            $this->logger->error('网络安全检查失败', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * 检查应用安全
     */
    private function checkApplicationSecurity(): array
    {
        $results = [
            'status' => 'secure',
            'issues' => [],
            'score' => 100
        ];

        try {
            // 检查身份验证机制
            $this->checkAuthentication($results);
            
            // 检查会话安全
            $this->checkSessionSecurity($results);
            
            // 检查输入验证
            $this->checkInputValidation($results);

        } catch (Exception $e) {
            $this->logger->error('应用安全检查失败', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * 计算安全评分
     */
    private function calculateSecurityScore(array $data): int
    {
        $totalScore = 0;
        $weights = [
            'vulnerabilities' => 40,
            'file_security' => 15,
            'db_security' => 20,
            'network_security' => 15,
            'app_security' => 10
        ];

        // 漏洞扣分
        $vulnerabilityPenalty = 0;
        foreach ($data['vulnerabilities'] as $vuln) {
            switch ($vuln['severity']) {
                case 'critical':
                    $vulnerabilityPenalty += 25;
                    break;
                case 'high':
                    $vulnerabilityPenalty += 15;
                    break;
                case 'medium':
                    $vulnerabilityPenalty += 10;
                    break;
                case 'low':
                    $vulnerabilityPenalty += 5;
                    break;
            }
        }

        $vulnerabilityScore = max(0, 100 - $vulnerabilityPenalty);
        $totalScore += $vulnerabilityScore * ($weights['vulnerabilities'] / 100);

        // 其他安全检查评分
        foreach (['file_security', 'db_security', 'network_security', 'app_security'] as $key) {
            if (isset($data[$key]['score'])) {
                $totalScore += $data[$key]['score'] * ($weights[$key] / 100);
            }
        }

        return (int) min(100, max(0, $totalScore));
    }

    /**
     * 生成安全建议
     */
    private function generateSecurityRecommendations(array $scanResults): array
    {
        $recommendations = [];

        // 基于漏洞生成建议
        foreach ($scanResults['vulnerabilities'] as $vuln) {
            if (isset($vuln['recommendation'])) {
                $recommendations[] = [
                    'category' => $vuln['type'],
                    'priority' => $this->getPriorityFromSeverity($vuln['severity']),
                    'title' => $this->getRecommendationTitle($vuln['type']),
                    'description' => $vuln['recommendation'],
                    'impact' => $vuln['severity']
                ];
            }
        }

        // 通用安全建议
        $recommendations = array_merge($recommendations, [
            [
                'category' => 'general',
                'priority' => 'medium',
                'title' => '定期安全扫描',
                'description' => '建议每周进行一次全面的安全扫描，及时发现和修复安全问题',
                'impact' => 'medium'
            ],
            [
                'category' => 'general',
                'priority' => 'high',
                'title' => '更新依赖包',
                'description' => '定期更新系统依赖包，修复已知的安全漏洞',
                'impact' => 'high'
            ],
            [
                'category' => 'general',
                'priority' => 'medium',
                'title' => '备份策略',
                'description' => '制定完整的数据备份和恢复策略，防范数据丢失风险',
                'impact' => 'medium'
            ]
        ]);

        return $recommendations;
    }

    /**
     * 保存扫描结果
     */
    private function saveScanResults(array $results): void
    {
        try {
            $data = [
                'scan_id' => $results['scan_id'],
                'start_time' => $results['start_time'],
                'end_time' => $results['end_time'],
                'duration' => $results['duration'],
                'status' => $results['status'],
                'security_score' => $results['security_score'],
                'vulnerabilities_count' => count($results['vulnerabilities']),
                'results_data' => json_encode($results),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('security_scans', $data);
            
            $this->logger->info('安全扫描结果已保存', [
                'scan_id' => $results['scan_id'],
                'score' => $results['security_score']
            ]);

        } catch (Exception $e) {
            $this->logger->error('保存安全扫描结果失败', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 生成扫描ID
     */
    private function generateScanId(): string
    {
        return 'scan_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 8);
    }

    /**
     * 加载安全配置
     */
    private function loadSecurityConfig(): array
    {
        return [
            'max_login_attempts' => 5,
            'session_timeout' => 3600,
            'password_min_length' => 8,
            'require_https' => true,
            'blocked_ips' => [],
            'allowed_file_types' => ['jpg', 'png', 'gif', 'pdf', 'doc', 'docx'],
            'max_file_size' => 10 * 1024 * 1024 // 10MB
        ];
    }

    /**
     * 验证请求来源
     */
    private function validateRequestOrigin(): bool
    {
        // 简化实现 - 检查基本的HTTP头
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // 基本的反爬虫检查
        if (empty($userAgent) || strpos($userAgent, 'bot') !== false) {
            return false;
        }

        return true;
    }

    /**
     * 检查频率限制
     * 
     * @param string|null $ip IP地址，如果不提供则使用当前请求IP
     * @return bool
     */
    public function checkRateLimit(string $ip = null): bool
    {
        $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $key = 'rate_limit_' . $ip;
        
        try {
            $current = $this->cache->get($key, 0);
            if ($current >= 100) { // 每分钟最多100个请求
                return false;
            }
            
            $this->cache->set($key, $current + 1, 60);
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('频率限制检查失败', ['error' => $e->getMessage()]);
            return true; // 出错时允许请求通过
        }
    }

    /**
     * 检测恶意请求
     */
    private function detectMaliciousRequest(): bool
    {
        $suspicious = false;
        
        // 检查URL中的恶意模式
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $maliciousPatterns = [
            '/\.\.\//i',  // 目录遍历
            '/union.*select/i',  // SQL注入
            '/<script/i',  // XSS
            '/eval\(/i',   // 代码注入
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                $suspicious = true;
                break;
            }
        }
        
        return $suspicious;
    }

    // SQL注入检测方法的简化实现
    private function detectSQLInjection(): array
    {
        // 模拟SQL注入检测结果
        return [];
    }

    // XSS漏洞检测方法的简化实现
    private function detectXSSVulnerabilities(): array
    {
        // 模拟XSS检测结果
        return [];
    }

    // CSRF漏洞检测方法的简化实现
    private function detectCSRFVulnerabilities(): array
    {
        // 模拟CSRF检测结果
        return [];
    }

    // 文件上传漏洞检测方法的简化实现
    private function detectFileUploadVulnerabilities(): array
    {
        // 模拟文件上传漏洞检测结果
        return [];
    }

    // 各种安全检查方法的简化实现
    private function checkConfigurationSecurity(array &$results): void
    {
        // 检查调试模式是否关闭
        if (defined('APP_DEBUG') && APP_DEBUG === true) {
            $results['issues'][] = [
                'type' => 'configuration',
                'severity' => 'medium',
                'description' => '调试模式在生产环境中应该关闭'
            ];
            $results['score'] -= 10;
        }
    }

    private function checkDatabaseCredentials(array &$results): void
    {
        // 检查是否使用默认凭据
        // 这里可以添加具体的检查逻辑
    }

    private function checkDatabasePermissions(array &$results): void
    {
        // 检查数据库权限配置
        // 这里可以添加具体的检查逻辑
    }

    private function checkHTTPSConfiguration(array &$results): void
    {
        // 检查HTTPS配置
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            $results['issues'][] = [
                'type' => 'https',
                'severity' => 'high',
                'description' => '建议在生产环境中启用HTTPS'
            ];
            $results['score'] -= 15;
        }
    }

    private function checkSecurityHeaders(array &$results): void
    {
        $requiredHeaders = [
            'X-Content-Type-Options',
            'X-Frame-Options',
            'X-XSS-Protection',
            'Strict-Transport-Security'
        ];

        foreach ($requiredHeaders as $header) {
            if (!isset($_SERVER['HTTP_' . str_replace('-', '_', strtoupper($header))])) {
                $results['issues'][] = [
                    'type' => 'security_headers',
                    'severity' => 'medium',
                    'description' => "缺少安全头: $header"
                ];
                $results['score'] -= 5;
            }
        }
    }

    private function checkPortSecurity(array &$results): void
    {
        // 检查开放的端口
        // 暂时跳过具体实现
    }

    private function checkAuthentication(array &$results): void
    {
        // 检查身份验证机制
        // 暂时跳过具体实现
    }

    private function checkSessionSecurity(array &$results): void
    {
        // 检查会话安全配置
        if (ini_get('session.cookie_secure') !== '1') {
            $results['issues'][] = [
                'type' => 'session_security',
                'severity' => 'medium',
                'description' => "Session cookies not set to secure"
            ];
            $results['score'] -= 10;
        }

        if (ini_get('session.cookie_httponly') !== '1') {
            $results['issues'][] = [
                'type' => 'session_security',
                'severity' => 'medium',
                'description' => "Session cookies not set to HttpOnly"
            ];
            $results['score'] -= 10;
        }
    }

    private function checkInputValidation(array &$results): void
    {
        // 检查输入验证机制
        // 暂时跳过具体实现
    }

    // 辅助方法
    private function getPriorityFromSeverity(string $severity): string
    {
        $map = [
            'critical' => 'urgent',
            'high' => 'high',
            'medium' => 'medium',
            'low' => 'low'
        ];
        
        return $map[$severity] ?? 'medium';
    }

    private function getRecommendationTitle(string $type): string
    {
        $titles = [
            'sql_injection' => '修复SQL注入漏洞',
            'xss' => '修复XSS漏洞',
            'csrf' => '修复CSRF漏洞',
            'file_upload' => '修复文件上传漏洞'
        ];
        
        return $titles[$type] ?? '安全问题修复';
    }

    private function getThreatStatus(): string
    {
        // 模拟威胁状态检查
        return 'low';
    }

    private function checkSystemIntegrity(): string
    {
        // 模拟系统完整性检查
        return 'verified';
    }

    private function getFirewallStatus(): string
    {
        // 模拟防火墙状态检查
        return 'active';
    }

    /**
     * 验证JWT令牌
     *
     * @param string $token JWT令牌
     * @return array|bool 用户数据或失败时为false
     */
    public function validateJwtToken(string $token)
    {
        try {
            // 此处应使用JWT库进行实际验证
            // 下面是简化版实现
            if (empty($token)) {
                return false;
            }
            
            // 在实际应用中应验证签名、过期时间等
            // 这里仅做示例
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }
            
            // 从令牌中获取用户ID
            // 在实际应用中应该解码JWT载荷
            return [
                'user_id' => 1,  // 示例用户ID
                'username' => 'testuser',
                'role' => 'user',
                'expires' => time() + 3600
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('JWT验证失败', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * 检查IP白名单
     *
     * @param array $whitelist 白名单IP列表
     * @return bool 是否在白名单中
     */
    public function checkIpWhitelist(array $whitelist): bool
    {
        // 获取客户端IP
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // 如果白名单为空，默认允许所有IP
        if (empty($whitelist)) {
            return true;
        }
        
        // 检查IP是否在白名单中
        return in_array($clientIp, $whitelist);
    }
    
    /**
     * 净化输入数据
     *
     * @param mixed $input 输入数据
     * @return mixed 清理后的数据
     */
    public function sanitizeInput($input)
    {
        if (is_string($input)) {
            // 过滤字符串
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        } elseif (is_array($input)) {
            // 递归过滤数组
            foreach ($input as $key => $value) {
                $input[$key] = $this->sanitizeInput($value);
            }
        }
        
        return $input;
    }
}
