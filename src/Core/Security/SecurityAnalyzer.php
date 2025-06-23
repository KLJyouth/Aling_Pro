<?php

namespace AlingAi\Core\Security;

/**
 * 安全分析器
 * 
 * 分析API请求并检测潜在的安全风险
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */
class SecurityAnalyzer
{
    /**
     * @var array 安全风险定义
     */
    private $riskDefinitions;
    
    /**
     * @var array API请求参数敏感字段列表
     */
    private $sensitiveFields = [
        'password', 
        'token', 
        'secret', 
        'api_key', 
        'credit_card',
        'ssn',
        'id_card',
        'private_key'
    ];
    
    /**
     * @var array 已知恶意IP地址段(示例)
     */
    private $knownBadIps = [];
    
    /**
     * @var array SQL注入攻击模式
     */
    private $sqlInjectionPatterns = [
        '/SELECT.*FROM/i',
        '/INSERT.*INTO/i',
        '/UPDATE.*SET/i',
        '/DELETE.*FROM/i',
        '/DROP.*TABLE/i',
        '/ALTER.*TABLE/i',
        '/UNION.*SELECT/i',
        '/EXEC.*sp_/i',
        '/DECLARE.*@/i',
        '/xp_cmdshell/i',
        "/'.*;--/i",
        "/\\s+or\\s+[0-9]=[0-9]/i",
        "/\\s+or\\s+'[^']+'='[^']+'/i"
    ];
    
    /**
     * @var array XSS攻击模式
     */
    private $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript:/i',
        '/on[a-z]+\s*=\s*(["\']*).*?\1/i',
        '/<[^>]*[^a-z]style\s*=[^>]*expression[^>]*>/i',
        '/<[^>]*[^a-z]style\s*=[^>]*behavior[^>]*>/i',
        '/<[^>]*[^a-z]style\s*=[^>]*url[^>]*script:/i',
        '/<[^>]*[^a-z]style\s*=[^>]*url[^>]*data:/i'
    ];
    
    /**
     * @var array 命令注入模式
     */
    private $cmdInjectionPatterns = [
        '/;.*?(?:\||\$\(|`)/i', 
        '/\$\([^\)]*\)/', 
        '/`[^`]*`/', 
        '/\|.+/', 
        '/&.+/'
    ];
    
    /**
     * @var array 敏感文件访问模式
     */
    private $fileAccessPatterns = [
        '/\.\.\//',
        '/\.\.\\\\/',
        '/etc\/passwd/',
        '/config\.php/',
        '/wp-config\.php/',
        '/\.htaccess/',
        '/boot\.ini/',
        '/web\.config/'
    ];
    
    /**
     * @var array API滥用阈值
     */
    private $apiAbuseThresholds = [
        'high_frequency' => 100, // 10秒内请求次数
        'large_payload' => 1048576, // 1MB
        'bulk_operations' => 50
    ];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->loadRiskDefinitions();
        $this->loadBlockLists();
    }
    
    /**
     * 加载风险定义
     */
    private function loadRiskDefinitions(): void
    {
        // 从配置文件加载风险定义
        $riskDefinitionsFile = __DIR__ . '/../../../config/security/risk_definitions.php';
        
        if (file_exists($riskDefinitionsFile)) {
            $this->riskDefinitions = require $riskDefinitionsFile;
        } else {
            // 默认风险定义
            $this->riskDefinitions = [
                'injection' => [
                    'severity' => 'high',
                    'description' => '检测到潜在的SQL/命令/代码注入尝试',
                    'action' => 'block'
                ],
                'xss' => [
                    'severity' => 'high',
                    'description' => '检测到潜在的跨站脚本(XSS)攻击',
                    'action' => 'block'
                ],
                'auth_bypass' => [
                    'severity' => 'critical',
                    'description' => '检测到潜在的认证绕过尝试',
                    'action' => 'block'
                ],
                'path_traversal' => [
                    'severity' => 'high',
                    'description' => '检测到潜在的路径遍历攻击',
                    'action' => 'block'
                ],
                'api_abuse' => [
                    'severity' => 'medium',
                    'description' => '检测到API滥用行为',
                    'action' => 'rate_limit'
                ],
                'data_leakage' => [
                    'severity' => 'high',
                    'description' => '检测到可能的敏感数据泄露',
                    'action' => 'alert'
                ],
                'unusual_behavior' => [
                    'severity' => 'low',
                    'description' => '检测到异常行为模式',
                    'action' => 'monitor'
                ],
                'suspicious_origin' => [
                    'severity' => 'medium',
                    'description' => '检测到来自可疑来源的请求',
                    'action' => 'challenge'
                ],
                'brute_force' => [
                    'severity' => 'high',
                    'description' => '检测到潜在的暴力破解尝试',
                    'action' => 'block_temp'
                ],
                'outdated_client' => [
                    'severity' => 'low',
                    'description' => '检测到过时的客户端软件',
                    'action' => 'warn'
                ]
            ];
        }
    }
    
    /**
     * 加载黑名单
     */
    private function loadBlockLists(): void
    {
        // 从配置文件加载黑名单IP地址
        $blockListFile = __DIR__ . '/../../../config/security/blocklists.php';
        
        if (file_exists($blockListFile)) {
            $blockLists = require $blockListFile;
            $this->knownBadIps = $blockLists['ip_addresses'] ?? [];
        }
    }
    
    /**
     * 分析请求并检测潜在的安全风险
     * 
     * @param array $requestData 请求数据
     * @param array $requestInfo 请求信息
     * @return array 检测到的安全问题列表
     */
    public function analyze(array $requestData, array $requestInfo): array
    {
        $issues = [];
        
        // 检测SQL注入
        $sqlInjectionIssues = $this->detectSqlInjection($requestData);
        $issues = array_merge($issues, $sqlInjectionIssues);
        
        // 检测XSS
        $xssIssues = $this->detectXss($requestData);
        $issues = array_merge($issues, $xssIssues);
        
        // 检测命令注入
        $cmdInjectionIssues = $this->detectCommandInjection($requestData);
        $issues = array_merge($issues, $cmdInjectionIssues);
        
        // 检测路径遍历
        $fileAccessIssues = $this->detectFileAccess($requestData);
        $issues = array_merge($issues, $fileAccessIssues);
        
        // 检测可疑IP地址
        if ($ipIssue = $this->detectSuspiciousIp($requestInfo['ip_address'])) {
            $issues[] = $ipIssue;
        }
        
        // 检查未加密的敏感数据
        if (!$requestInfo['is_encrypted']) {
            $dataLeakageIssues = $this->detectDataLeakage($requestData);
            $issues = array_merge($issues, $dataLeakageIssues);
        }
        
        return $issues;
    }
    
    /**
     * 检测SQL注入尝试
     */
    private function detectSqlInjection(array $requestData): array
    {
        $issues = [];
        $flatData = $this->flattenArray($requestData);
        
        foreach ($flatData as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($this->sqlInjectionPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $issues[] = [
                        'type' => 'injection',
                        'subtype' => 'sql_injection',
                        'severity' => 'high',
                        'description' => '检测到潜在的SQL注入尝试',
                        'details' => [
                            'parameter' => $key,
                            'pattern_matched' => $pattern,
                            'sanitized_value' => $this->sanitizeOutput($value)
                        ]
                    ];
                    break; // 对该值只报告一次
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * 检测XSS尝试
     */
    private function detectXss(array $requestData): array
    {
        $issues = [];
        $flatData = $this->flattenArray($requestData);
        
        foreach ($flatData as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($this->xssPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $issues[] = [
                        'type' => 'xss',
                        'subtype' => 'reflected_xss',
                        'severity' => 'high',
                        'description' => '检测到潜在的跨站脚本(XSS)攻击',
                        'details' => [
                            'parameter' => $key,
                            'pattern_matched' => $pattern,
                            'sanitized_value' => $this->sanitizeOutput($value)
                        ]
                    ];
                    break; // 对该值只报告一次
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * 检测命令注入尝试
     */
    private function detectCommandInjection(array $requestData): array
    {
        $issues = [];
        $flatData = $this->flattenArray($requestData);
        
        foreach ($flatData as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($this->cmdInjectionPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $issues[] = [
                        'type' => 'injection',
                        'subtype' => 'command_injection',
                        'severity' => 'critical',
                        'description' => '检测到潜在的命令注入尝试',
                        'details' => [
                            'parameter' => $key,
                            'pattern_matched' => $pattern,
                            'sanitized_value' => $this->sanitizeOutput($value)
                        ]
                    ];
                    break; // 对该值只报告一次
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * 检测恶意文件访问尝试(如路径遍历)
     */
    private function detectFileAccess(array $requestData): array
    {
        $issues = [];
        $flatData = $this->flattenArray($requestData);
        
        foreach ($flatData as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($this->fileAccessPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $issues[] = [
                        'type' => 'path_traversal',
                        'subtype' => 'file_access',
                        'severity' => 'high',
                        'description' => '检测到潜在的路径遍历攻击',
                        'details' => [
                            'parameter' => $key,
                            'pattern_matched' => $pattern,
                            'sanitized_value' => $this->sanitizeOutput($value)
                        ]
                    ];
                    break; // 对该值只报告一次
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * 检测可疑IP地址
     */
    private function detectSuspiciousIp(string $ipAddress): ?array
    {
        // 检查已知恶意IP列表
        if (in_array($ipAddress, $this->knownBadIps)) {
            return [
                'type' => 'suspicious_origin',
                'subtype' => 'known_bad_ip',
                'severity' => 'high',
                'description' => '请求源自已知恶意IP地址',
                'details' => [
                    'ip_address' => $ipAddress
                ]
            ];
        }
        
        // 在这里可以添加更多IP检查逻辑
        // 如检查Tor出口节点、已知代理等
        
        return null;
    }
    
    /**
     * 检测未加密敏感数据
     */
    private function detectDataLeakage(array $requestData): array
    {
        $issues = [];
        $flatData = $this->flattenArray($requestData);
        
        foreach ($flatData as $key => $value) {
            // 检查键名是否包含敏感词
            foreach ($this->sensitiveFields as $field) {
                if (stripos($key, $field) !== false && !empty($value)) {
                    $issues[] = [
                        'type' => 'data_leakage',
                        'subtype' => 'unencrypted_sensitive_data',
                        'severity' => 'medium',
                        'description' => '检测到未加密传输的敏感数据',
                        'details' => [
                            'parameter' => $key
                        ]
                    ];
                    break; // 对该字段只报告一次
                }
            }
            
            // 检查值是否包含常见的敏感数据模式
            if (is_string($value)) {
                // 检测信用卡
                if (preg_match('/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|6(?:011|5[0-9]{2})[0-9]{12}|(?:2131|1800|35\d{3})\d{11})$/', $value)) {
                    $issues[] = [
                        'type' => 'data_leakage',
                        'subtype' => 'credit_card',
                        'severity' => 'high',
                        'description' => '检测到未加密的信用卡号码',
                        'details' => [
                            'parameter' => $key
                        ]
                    ];
                }
                
                // 检测中国身份证号
                if (preg_match('/(^[1-9]\d{5}(18|19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])\d{3}[0-9xX]$)/', $value)) {
                    $issues[] = [
                        'type' => 'data_leakage',
                        'subtype' => 'id_card',
                        'severity' => 'high',
                        'description' => '检测到未加密的身份证号码',
                        'details' => [
                            'parameter' => $key
                        ]
                    ];
                }
                
                // 检测API密钥格式
                if (preg_match('/^[A-Za-z0-9_-]{20,64}$/', $value)) {
                    // 检测更具体的API密钥模式
                    if (preg_match('/^(sk_|pk_|api_|key_|token_)/', $value)) {
                        $issues[] = [
                            'type' => 'data_leakage',
                            'subtype' => 'api_key',
                            'severity' => 'high',
                            'description' => '检测到未加密的API密钥',
                            'details' => [
                                'parameter' => $key
                            ]
                        ];
                    }
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * 将多维数组展平为一维数组
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value) && !empty($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * 清理输出中的敏感内容
     */
    private function sanitizeOutput(string $value): string
    {
        if (strlen($value) > 100) {
            return substr($value, 0, 97) . '...';
        }
        
        return $value;
    }
} 