<?php
/**
 * API安全监控类 - 监控各类API的安全状态
 * @version 1.0.0
 * @author AlingAi Team
 */

namespace AlingAi\Admin\Security;

use PDO;
use Exception;
use DateTime;

class ApiSecurityMonitor
{
    private $db;
    private $logger;
    private $config;
    private $apiEndpoints = [];
    private $apiThreats = [];
    private $apiVulnerabilities = [];
    private $lastScanTime = null;
    
    /**
     * 构造函数
     */
    public function __construct($db = null, $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->loadConfig();
        $this->initializeMonitor();
    }
    
    /**
     * 加载配置
     */
    private function loadConfig(): void
    {
        try {
            $configFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/config/api_security_config.php';
            if (file_exists($configFile)) {
                $this->config = require $configFile;
            } else {
                // 使用默认配置
                $this->config = [
                    'scan_interval' => 60, // 分钟
                    'alert_threshold' => 'medium',
                    'api_rate_limiting' => true,
                    'api_authentication_required' => true,
                    'api_logging_level' => 'detailed',
                    'api_monitoring_enabled' => true,
                    'api_categories' => [
                        'system' => true,   // 系统API
                        'local' => true,    // 本地API
                        'user' => true,     // 用户API
                        'external' => true  // 外部API
                    ],
                    'vulnerability_scan_enabled' => true,
                    'threat_detection_enabled' => true,
                    'auto_block_threats' => false
                ];
            }
        } catch (Exception $e) {
            $this->logError('加载API安全配置失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 初始化监控系统
     */
    private function initializeMonitor(): void
    {
        try {
            // 创建必要的数据库表
            $this->createMonitoringTables();
            
            // 加载API端点
            $this->loadApiEndpoints();
            
            // 加载API威胁
            $this->loadApiThreats();
            
            // 加载API漏洞
            $this->loadApiVulnerabilities();
        } catch (Exception $e) {
            $this->logError('初始化API安全监控系统失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 创建监控相关数据库表
     */
    private function createMonitoringTables(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            // API端点表
            $this->db->exec("CREATE TABLE IF NOT EXISTS api_endpoints (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                endpoint VARCHAR(255) NOT NULL,
                method VARCHAR(10) NOT NULL,
                category VARCHAR(20) NOT NULL,
                description TEXT,
                authentication_required BOOLEAN DEFAULT 1,
                rate_limited BOOLEAN DEFAULT 1,
                active BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_checked DATETIME
            )");
            
            // API访问日志表
            $this->db->exec("CREATE TABLE IF NOT EXISTS api_access_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                endpoint_id INTEGER,
                ip_address VARCHAR(45),
                user_agent TEXT,
                user_id INTEGER,
                response_code INTEGER,
                response_time FLOAT,
                request_size INTEGER,
                response_size INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // API威胁日志表
            $this->db->exec("CREATE TABLE IF NOT EXISTS api_threats (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                endpoint_id INTEGER,
                threat_type VARCHAR(50) NOT NULL,
                severity VARCHAR(20) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                user_id INTEGER,
                request_data TEXT,
                blocked BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // API漏洞表
            $this->db->exec("CREATE TABLE IF NOT EXISTS api_vulnerabilities (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                endpoint_id INTEGER,
                vulnerability_type VARCHAR(50) NOT NULL,
                severity VARCHAR(20) NOT NULL,
                description TEXT,
                remediation TEXT,
                status VARCHAR(20) DEFAULT 'open',
                discovered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                fixed_at DATETIME
            )");
        } catch (Exception $e) {
            $this->logError('创建API安全监控数据库表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 加载API端点
     */
    private function loadApiEndpoints(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->query("
                SELECT * FROM api_endpoints
                WHERE active = 1
                ORDER BY category, endpoint
            ");
            
            $this->apiEndpoints = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 如果没有记录，初始化一些默认端点
            if (empty($this->apiEndpoints)) {
                $this->initializeDefaultEndpoints();
            }
        } catch (Exception $e) {
            $this->logError('加载API端点失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 初始化默认API端点
     */
    private function initializeDefaultEndpoints(): void
    {
        if (!$this->db) {
            return;
        }
        
        $defaultEndpoints = [
            // 系统API
            ['endpoint' => '/api/v1/auth/login', 'method' => 'POST', 'category' => 'system', 'description' => '用户登录API', 'authentication_required' => 0],
            ['endpoint' => '/api/v1/auth/logout', 'method' => 'POST', 'category' => 'system', 'description' => '用户登出API', 'authentication_required' => 1],
            ['endpoint' => '/api/v1/users/profile', 'method' => 'GET', 'category' => 'system', 'description' => '获取用户资料', 'authentication_required' => 1],
            ['endpoint' => '/api/v1/system/status', 'method' => 'GET', 'category' => 'system', 'description' => '系统状态API', 'authentication_required' => 1],
            
            // 本地API
            ['endpoint' => '/api/v1/local/data', 'method' => 'GET', 'category' => 'local', 'description' => '本地数据API', 'authentication_required' => 1],
            ['endpoint' => '/api/v1/local/files', 'method' => 'GET', 'category' => 'local', 'description' => '本地文件API', 'authentication_required' => 1],
            
            // 量子加密API
            ['endpoint' => '/api/v2/quantum/status', 'method' => 'GET', 'category' => 'system', 'description' => '量子加密状态API', 'authentication_required' => 1],
            ['endpoint' => '/api/v2/quantum/encrypt', 'method' => 'POST', 'category' => 'system', 'description' => '量子加密API', 'authentication_required' => 1],
            ['endpoint' => '/api/v2/quantum/decrypt', 'method' => 'POST', 'category' => 'system', 'description' => '量子解密API', 'authentication_required' => 1],
            
            // 用户API
            ['endpoint' => '/api/v1/user-api/endpoint1', 'method' => 'GET', 'category' => 'user', 'description' => '用户API示例1', 'authentication_required' => 1],
            ['endpoint' => '/api/v1/user-api/endpoint2', 'method' => 'POST', 'category' => 'user', 'description' => '用户API示例2', 'authentication_required' => 1]
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO api_endpoints 
                (endpoint, method, category, description, authentication_required)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($defaultEndpoints as $endpoint) {
                $stmt->execute([
                    $endpoint['endpoint'],
                    $endpoint['method'],
                    $endpoint['category'],
                    $endpoint['description'],
                    $endpoint['authentication_required']
                ]);
            }
            
            // 重新加载端点
            $this->loadApiEndpoints();
        } catch (Exception $e) {
            $this->logError('初始化默认API端点失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 加载API威胁
     */
    private function loadApiThreats(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->query("
                SELECT t.*, e.endpoint, e.method, e.category
                FROM api_threats t
                LEFT JOIN api_endpoints e ON t.endpoint_id = e.id
                WHERE t.created_at > datetime('now', '-7 days')
                ORDER BY t.created_at DESC
                LIMIT 50
            ");
            
            $this->apiThreats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError('加载API威胁失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 加载API漏洞
     */
    private function loadApiVulnerabilities(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->query("
                SELECT v.*, e.endpoint, e.method, e.category
                FROM api_vulnerabilities v
                LEFT JOIN api_endpoints e ON v.endpoint_id = e.id
                WHERE v.status = 'open'
                ORDER BY v.severity DESC, v.discovered_at DESC
            ");
            
            $this->apiVulnerabilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError('加载API漏洞失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 扫描API安全状态
     */
    public function scanApiSecurity(): array
    {
        $results = [
            'status' => '正常',
            'endpoints_scanned' => 0,
            'threats_detected' => 0,
            'vulnerabilities_found' => 0,
            'categories' => [
                'system' => ['scanned' => 0, 'issues' => 0],
                'local' => ['scanned' => 0, 'issues' => 0],
                'user' => ['scanned' => 0, 'issues' => 0],
                'external' => ['scanned' => 0, 'issues' => 0]
            ],
            'details' => [],
            'scan_time' => date('Y-m-d H:i:s')
        ];
        
        try {
            // 检查是否启用了相应类别的API监控
            $categories = [];
            foreach ($this->config['api_categories'] as $category => $enabled) {
                if ($enabled) {
                    $categories[] = $category;
                }
            }
            
            if (empty($categories)) {
                return $results;
            }
            
            // 扫描所有活跃的API端点
            foreach ($this->apiEndpoints as $endpoint) {
                if (!in_array($endpoint['category'], $categories)) {
                    continue;
                }
                
                $results['endpoints_scanned']++;
                $results['categories'][$endpoint['category']]['scanned']++;
                
                // 扫描端点安全性
                $endpointResult = $this->scanApiEndpoint($endpoint);
                
                if (!empty($endpointResult['threats'])) {
                    $results['threats_detected'] += count($endpointResult['threats']);
                    $results['categories'][$endpoint['category']]['issues'] += count($endpointResult['threats']);
                }
                
                if (!empty($endpointResult['vulnerabilities'])) {
                    $results['vulnerabilities_found'] += count($endpointResult['vulnerabilities']);
                    $results['categories'][$endpoint['category']]['issues'] += count($endpointResult['vulnerabilities']);
                }
                
                if (!empty($endpointResult['threats']) || !empty($endpointResult['vulnerabilities'])) {
                    $results['details'][] = [
                        'endpoint' => $endpoint['endpoint'],
                        'method' => $endpoint['method'],
                        'category' => $endpoint['category'],
                        'threats' => $endpointResult['threats'],
                        'vulnerabilities' => $endpointResult['vulnerabilities']
                    ];
                }
            }
            
            // 确定整体状态
            if ($results['threats_detected'] > 0 || $results['vulnerabilities_found'] > 0) {
                $hasCritical = false;
                
                foreach ($results['details'] as $detail) {
                    foreach ($detail['threats'] as $threat) {
                        if ($threat['severity'] === '高') {
                            $hasCritical = true;
                            break 2;
                        }
                    }
                    
                    foreach ($detail['vulnerabilities'] as $vulnerability) {
                        if ($vulnerability['severity'] === '高') {
                            $hasCritical = true;
                            break 2;
                        }
                    }
                }
                
                $results['status'] = $hasCritical ? '危险' : '警告';
            }
            
            // 更新最后扫描时间
            $this->lastScanTime = new DateTime();
            
            // 记录扫描结果
            $this->logScanResults($results);
        } catch (Exception $e) {
            $this->logError('扫描API安全失败: ' . $e->getMessage());
            $results['status'] = '错误';
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * 扫描单个API端点
     */
    private function scanApiEndpoint(array $endpoint): array
    {
        $result = [
            'threats' => [],
            'vulnerabilities' => []
        ];
        
        // 在实际实现中，这里应该进行真实的API安全检查
        // 例如，尝试发送各种测试请求来检测漏洞
        
        // 这里使用模拟数据进行演示
        $endpointId = $endpoint['id'];
        
        // 检查是否有最近的威胁记录
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM api_threats
                    WHERE endpoint_id = ?
                    AND created_at > datetime('now', '-24 hours')
                    ORDER BY created_at DESC
                ");
                
                $stmt->execute([$endpointId]);
                $threats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($threats)) {
                    $result['threats'] = $threats;
                }
            } catch (Exception $e) {
                $this->logError('检查API威胁记录失败: ' . $e->getMessage());
            }
        }
        
        // 检查是否有未修复的漏洞
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM api_vulnerabilities
                    WHERE endpoint_id = ?
                    AND status = 'open'
                    ORDER BY severity DESC, discovered_at DESC
                ");
                
                $stmt->execute([$endpointId]);
                $vulnerabilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($vulnerabilities)) {
                    $result['vulnerabilities'] = $vulnerabilities;
                }
            } catch (Exception $e) {
                $this->logError('检查API漏洞记录失败: ' . $e->getMessage());
            }
        }
        
        // 模拟漏洞扫描
        // 在实际实现中，这里应该进行真实的漏洞扫描
        if ($this->config['vulnerability_scan_enabled'] && rand(1, 20) === 1) {
            $vulnTypes = [
                'sql_injection' => '可能存在SQL注入漏洞',
                'xss' => '可能存在跨站脚本攻击漏洞',
                'csrf' => '缺少CSRF保护',
                'auth_bypass' => '可能存在认证绕过漏洞',
                'rate_limit_missing' => '缺少速率限制'
            ];
            
            $vulnType = array_rand($vulnTypes);
            $severity = ($vulnType === 'sql_injection' || $vulnType === 'auth_bypass') ? '高' : '中';
            
            $newVulnerability = [
                'endpoint_id' => $endpointId,
                'vulnerability_type' => $vulnType,
                'severity' => $severity,
                'description' => $vulnTypes[$vulnType],
                'remediation' => $this->getRemediationForVulnerability($vulnType),
                'status' => 'open',
                'discovered_at' => date('Y-m-d H:i:s')
            ];
            
            // 添加到数据库
            if ($this->db) {
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO api_vulnerabilities
                        (endpoint_id, vulnerability_type, severity, description, remediation, status)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $newVulnerability['endpoint_id'],
                        $newVulnerability['vulnerability_type'],
                        $newVulnerability['severity'],
                        $newVulnerability['description'],
                        $newVulnerability['remediation'],
                        $newVulnerability['status']
                    ]);
                    
                    $newVulnerability['id'] = $this->db->lastInsertId();
                    $result['vulnerabilities'][] = $newVulnerability;
                } catch (Exception $e) {
                    $this->logError('添加API漏洞记录失败: ' . $e->getMessage());
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 获取漏洞修复建议
     */
    private function getRemediationForVulnerability(string $vulnType): string
    {
        $remediations = [
            'sql_injection' => '使用参数化查询，避免直接拼接SQL语句。确保使用ORM或预处理语句。',
            'xss' => '对所有用户输入进行过滤和转义。使用内容安全策略(CSP)。',
            'csrf' => '实现CSRF令牌验证机制，确保所有状态更改操作都需要有效的CSRF令牌。',
            'auth_bypass' => '检查所有授权逻辑，确保每个请求都进行适当的认证和授权检查。',
            'rate_limit_missing' => '实现API速率限制，防止暴力攻击和DoS攻击。'
        ];
        
        return $remediations[$vulnType] ?? '请联系安全团队进行评估和修复。';
    }
    
    /**
     * 记录API访问
     */
    public function logApiAccess(array $accessData): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO api_access_log
                (endpoint_id, ip_address, user_agent, user_id, response_code, response_time, request_size, response_size)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $accessData['endpoint_id'],
                $accessData['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
                $accessData['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
                $accessData['user_id'] ?? null,
                $accessData['response_code'],
                $accessData['response_time'],
                $accessData['request_size'] ?? 0,
                $accessData['response_size'] ?? 0
            ]);
        } catch (Exception $e) {
            $this->logError('记录API访问失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 记录API威胁
     */
    public function logApiThreat(array $threatData): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO api_threats
                (endpoint_id, threat_type, severity, description, ip_address, user_agent, user_id, request_data, blocked)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $threatData['endpoint_id'],
                $threatData['threat_type'],
                $threatData['severity'],
                $threatData['description'],
                $threatData['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
                $threatData['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
                $threatData['user_id'] ?? null,
                $threatData['request_data'] ?? null,
                $threatData['blocked'] ?? 0
            ]);
        } catch (Exception $e) {
            $this->logError('记录API威胁失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 记录扫描结果
     */
    private function logScanResults(array $results): void
    {
        // 在实际实现中，可以将扫描结果记录到日志文件或数据库中
        // 这里简单记录到错误日志
        if ($results['threats_detected'] > 0 || $results['vulnerabilities_found'] > 0) {
            $message = "API安全扫描发现 {$results['threats_detected']} 个威胁和 {$results['vulnerabilities_found']} 个漏洞";
            $this->logError($message);
        }
    }
    
    /**
     * 获取API端点列表
     */
    public function getApiEndpoints(string $category = null): array
    {
        if ($category) {
            return array_filter($this->apiEndpoints, function($endpoint) use ($category) {
                return $endpoint['category'] === $category;
            });
        }
        
        return $this->apiEndpoints;
    }
    
    /**
     * 获取API威胁列表
     */
    public function getApiThreats(int $limit = 50): array
    {
        if (!$this->db) {
            return $this->apiThreats;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, e.endpoint, e.method, e.category
                FROM api_threats t
                LEFT JOIN api_endpoints e ON t.endpoint_id = e.id
                ORDER BY t.created_at DESC
                LIMIT ?
            ");
            
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError('获取API威胁列表失败: ' . $e->getMessage());
            return $this->apiThreats;
        }
    }
    
    /**
     * 获取API漏洞列表
     */
    public function getApiVulnerabilities(string $status = 'open'): array
    {
        if (!$this->db) {
            return $this->apiVulnerabilities;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT v.*, e.endpoint, e.method, e.category
                FROM api_vulnerabilities v
                LEFT JOIN api_endpoints e ON v.endpoint_id = e.id
                WHERE v.status = ?
                ORDER BY v.severity DESC, v.discovered_at DESC
            ");
            
            $stmt->execute([$status]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError('获取API漏洞列表失败: ' . $e->getMessage());
            return $this->apiVulnerabilities;
        }
    }
    
    /**
     * 获取API访问统计
     */
    public function getApiAccessStatistics(string $period = 'day'): array
    {
        if (!$this->db) {
            return [];
        }
        
        try {
            $timeFilter = '';
            switch ($period) {
                case 'day':
                    $timeFilter = "WHERE a.created_at > datetime('now', '-1 day')";
                    break;
                case 'week':
                    $timeFilter = "WHERE a.created_at > datetime('now', '-7 days')";
                    break;
                case 'month':
                    $timeFilter = "WHERE a.created_at > datetime('now', '-30 days')";
                    break;
                default:
                    $timeFilter = "";
            }
            
            $stmt = $this->db->query("
                SELECT 
                    e.endpoint,
                    e.method,
                    e.category,
                    COUNT(*) as access_count,
                    AVG(a.response_time) as avg_response_time,
                    SUM(CASE WHEN a.response_code >= 200 AND a.response_code < 300 THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN a.response_code >= 400 THEN 1 ELSE 0 END) as error_count
                FROM api_access_log a
                LEFT JOIN api_endpoints e ON a.endpoint_id = e.id
                $timeFilter
                GROUP BY e.endpoint, e.method
                ORDER BY access_count DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError('获取API访问统计失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 添加新的API端点
     */
    public function addApiEndpoint(array $endpointData): bool
    {
        if (!$this->db) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO api_endpoints
                (endpoint, method, category, description, authentication_required, rate_limited)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $endpointData['endpoint'],
                $endpointData['method'],
                $endpointData['category'],
                $endpointData['description'] ?? '',
                $endpointData['authentication_required'] ?? 1,
                $endpointData['rate_limited'] ?? 1
            ]);
            
            if ($result) {
                // 重新加载端点
                $this->loadApiEndpoints();
            }
            
            return $result;
        } catch (Exception $e) {
            $this->logError('添加API端点失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 修复API漏洞
     */
    public function fixApiVulnerability(int $vulnerabilityId): bool
    {
        if (!$this->db) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE api_vulnerabilities
                SET status = 'fixed', fixed_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$vulnerabilityId]);
            
            if ($result) {
                // 重新加载漏洞
                $this->loadApiVulnerabilities();
            }
            
            return $result;
        } catch (Exception $e) {
            $this->logError('修复API漏洞失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 记录错误
     */
    private function logError(string $message): void
    {
        if ($this->logger) {
            $this->logger->error($message);
        } else {
            error_log($message);
        }
    }
} 