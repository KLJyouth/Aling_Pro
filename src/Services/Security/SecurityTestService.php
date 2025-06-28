<?php

namespace AlingAi\Services\Security;

use AlingAi\Utils\Logger;
use AlingAi\Utils\Database;

/**
 * 安全测试服务
 * 提供系统安全测试、漏洞扫描和安全状态评估功能
 *
 * @package AlingAi\Services\Security
 */
class SecurityTestService
{
    /**
     * 数据库连接
     *
     * @var Database
     */
    protected $db;
    
    /**
     * 日志记录器
     *
     * @var Logger
     */
    protected $logger;
    
    /**
     * 最后扫描时间
     *
     * @var int|null
     */
    protected $lastScanTime = null;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->logger = new Logger('security_test');
        $this->lastScanTime = $this->getLastScanTimeFromDb();
    }
    
    /**
     * 运行安全测试
     * 
     * @param string $testType 测试类型
     * @param array $options 测试选项
     * @return array 测试结果
     */
    public function runTests($testType = 'all', $options = [])
    {
        $this->logger->info('开始运行安全测试', [
            'test_type' => $testType,
            'options' => $options
        ]);
        
        $results = [];
        
        // 根据测试类型运行不同的测试
        switch ($testType) {
            case 'all':
                $results = array_merge(
                    $results,
                    $this->runConfigurationTests(),
                    $this->runVulnerabilityTests(),
                    $this->runAccessControlTests(),
                    $this->runEncryptionTests(),
                    $this->runNetworkTests()
                );
                break;
            case 'configuration':
                $results = $this->runConfigurationTests();
                break;
            case 'vulnerability':
                $results = $this->runVulnerabilityTests();
                break;
            case 'access_control':
                $results = $this->runAccessControlTests();
                break;
            case 'encryption':
                $results = $this->runEncryptionTests();
                break;
            case 'network':
                $results = $this->runNetworkTests();
                break;
            default:
                throw new \Exception('不支持的测试类型: ' . $testType);
        }
        
        // 更新最后扫描时间
        $this->updateLastScanTime();
        
        // 保存测试结果
        $this->saveTestResults($results, $testType);
        
        $this->logger->info('安全测试完成', [
            'test_type' => $testType,
            'result_count' => count($results)
        ]);
        
        return $results;
    }
    
    /**
     * 获取系统安全状态
     * 
     * @return array 安全状态信息
     */
    public function getSecurityStatus()
    {
        // 获取最近的测试结果
        $recentTests = $this->getRecentTestResults();
        
        // 计算整体安全评分
        $score = $this->calculateSecurityScore($recentTests);
        
        // 确定安全状态级别
        $level = $this->determineSecurityLevel($score);
        
        // 获取未解决的问题
        $issues = $this->getUnresolvedIssues();
        
        return [
            'score' => $score,
            'level' => $level,
            'last_scan' => $this->lastScanTime,
            'issues_count' => count($issues),
            'critical_issues' => $this->countIssuesBySeverity($issues, 'critical'),
            'high_issues' => $this->countIssuesBySeverity($issues, 'high'),
            'medium_issues' => $this->countIssuesBySeverity($issues, 'medium'),
            'low_issues' => $this->countIssuesBySeverity($issues, 'low'),
            'recommendations' => $this->getTopRecommendations($issues)
        ];
    }
    
    /**
     * 获取最后扫描时间
     * 
     * @return int|null 最后扫描时间戳
     */
    public function getLastScanTime()
    {
        return $this->lastScanTime;
    }
    
    /**
     * 运行配置测试
     * 
     * @return array 测试结果
     */
    protected function runConfigurationTests()
    {
        $this->logger->info('运行配置安全测试');
        $results = [];
        
        // 检查PHP配置
        $results[] = $this->checkPhpConfiguration();
        
        // 检查文件权限
        $results[] = $this->checkFilePermissions();
        
        // 检查环境变量
        $results[] = $this->checkEnvironmentVariables();
        
        // 检查数据库配置
        $results[] = $this->checkDatabaseConfiguration();
        
        // 检查Web服务器配置
        $results[] = $this->checkWebServerConfiguration();
        
        return array_filter($results);
    }
    
    /**
     * 运行漏洞测试
     * 
     * @return array 测试结果
     */
    protected function runVulnerabilityTests()
    {
        $this->logger->info('运行漏洞安全测试');
        $results = [];
        
        // 检查SQL注入漏洞
        $results[] = $this->checkSqlInjection();
        
        // 检查XSS漏洞
        $results[] = $this->checkXssVulnerabilities();
        
        // 检查CSRF漏洞
        $results[] = $this->checkCsrfVulnerabilities();
        
        // 检查文件包含漏洞
        $results[] = $this->checkFileInclusionVulnerabilities();
        
        // 检查命令注入漏洞
        $results[] = $this->checkCommandInjectionVulnerabilities();
        
        return array_filter($results);
    }
    
    /**
     * 运行访问控制测试
     * 
     * @return array 测试结果
     */
    protected function runAccessControlTests()
    {
        $this->logger->info('运行访问控制安全测试');
        $results = [];
        
        // 检查权限设置
        $results[] = $this->checkPermissionSettings();
        
        // 检查认证机制
        $results[] = $this->checkAuthenticationMechanisms();
        
        // 检查会话管理
        $results[] = $this->checkSessionManagement();
        
        // 检查API访问控制
        $results[] = $this->checkApiAccessControl();
        
        return array_filter($results);
    }
    
    /**
     * 运行加密测试
     * 
     * @return array 测试结果
     */
    protected function runEncryptionTests()
    {
        $this->logger->info('运行加密安全测试');
        $results = [];
        
        // 检查SSL/TLS配置
        $results[] = $this->checkSslTlsConfiguration();
        
        // 检查密钥管理
        $results[] = $this->checkKeyManagement();
        
        // 检查密码哈希
        $results[] = $this->checkPasswordHashing();
        
        // 检查数据加密
        $results[] = $this->checkDataEncryption();
        
        return array_filter($results);
    }
    
    /**
     * 运行网络测试
     * 
     * @return array 测试结果
     */
    protected function runNetworkTests()
    {
        $this->logger->info('运行网络安全测试');
        $results = [];
        
        // 检查开放端口
        $results[] = $this->checkOpenPorts();
        
        // 检查防火墙配置
        $results[] = $this->checkFirewallConfiguration();
        
        // 检查网络流量
        $results[] = $this->checkNetworkTraffic();
        
        // 检查DDoS防护
        $results[] = $this->checkDdosProtection();
        
        return array_filter($results);
    }
    
    /**
     * 检查PHP配置
     * 
     * @return array|null 测试结果
     */
    protected function checkPhpConfiguration()
    {
        $issues = [];
        
        // 检查PHP版本
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.1', '<')) {
            $issues[] = [
                'id' => 'php-version',
                'title' => 'PHP版本过低',
                'description' => '当前PHP版本(' . $phpVersion . ')低于推荐版本(8.1)，可能存在安全风险。',
                'severity' => 'medium',
                'recommendation' => '升级PHP到8.1或更高版本。'
            ];
        }
        
        // 检查危险函数
        $dangerousFunctions = [
            'exec', 'shell_exec', 'system', 'passthru', 'eval', 'popen', 'proc_open'
        ];
        
        $enabledDangerousFunctions = [];
        foreach ($dangerousFunctions as $function) {
            if (function_exists($function) && !in_array($function, explode(',', ini_get('disable_functions')))) {
                $enabledDangerousFunctions[] = $function;
            }
        }
        
        if (!empty($enabledDangerousFunctions)) {
            $issues[] = [
                'id' => 'dangerous-functions',
                'title' => '危险PHP函数未禁用',
                'description' => '以下危险函数未被禁用: ' . implode(', ', $enabledDangerousFunctions),
                'severity' => 'high',
                'recommendation' => '在php.ini中通过disable_functions禁用这些函数。'
            ];
        }
        
        // 检查显示错误设置
        if (ini_get('display_errors') == '1' && !in_array(strtolower(ini_get('environment')), ['development', 'dev'])) {
            $issues[] = [
                'id' => 'display-errors',
                'title' => '错误显示已启用',
                'description' => '在生产环境中启用错误显示可能泄露敏感信息。',
                'severity' => 'medium',
                'recommendation' => '在php.ini中设置display_errors=Off。'
            ];
        }
        
        // 检查文件上传设置
        if (ini_get('file_uploads') == '1' && ini_get('upload_max_filesize') > '10M') {
            $issues[] = [
                'id' => 'file-upload-size',
                'title' => '文件上传大小限制过大',
                'description' => '当前允许上传的最大文件大小为' . ini_get('upload_max_filesize') . '，可能导致DoS风险。',
                'severity' => 'low',
                'recommendation' => '减小upload_max_filesize和post_max_size的值。'
            ];
        }
        
        return !empty($issues) ? [
            'test_id' => 'php-configuration',
            'test_name' => 'PHP配置检查',
            'status' => 'failed',
            'issues' => $issues
        ] : null;
    }
    
    /**
     * 检查文件权限
     * 
     * @return array|null 测试结果
     */
    protected function checkFilePermissions()
    {
        $issues = [];
        $basePath = realpath(__DIR__ . '/../../../');
        
        // 检查敏感文件权限
        $sensitivePaths = [
            $basePath . '/.env',
            $basePath . '/config/database.php',
            $basePath . '/config/app.php',
            $basePath . '/storage/logs'
        ];
        
        foreach ($sensitivePaths as $path) {
            if (file_exists($path)) {
                $perms = fileperms($path);
                $worldWritable = ($perms & 0x0002) > 0;
                
                if ($worldWritable) {
                    $issues[] = [
                        'id' => 'world-writable-' . md5($path),
                        'title' => '敏感文件/目录可被任何用户写入',
                        'description' => '文件/目录 "' . $path . '" 对所有用户可写，这是一个严重的安全风险。',
                        'severity' => 'critical',
                        'recommendation' => '修改文件权限，移除全局写入权限。'
                    ];
                }
            }
        }
        
        // 检查上传目录权限
        $uploadDirs = [
            $basePath . '/public/uploads',
            $basePath . '/storage/app/public'
        ];
        
        foreach ($uploadDirs as $dir) {
            if (is_dir($dir)) {
                $perms = fileperms($dir);
                $worldExecutable = ($perms & 0x0001) > 0;
                
                if ($worldExecutable) {
                    $issues[] = [
                        'id' => 'world-executable-' . md5($dir),
                        'title' => '上传目录可执行',
                        'description' => '上传目录 "' . $dir . '" 对所有用户可执行，可能导致上传的文件被执行。',
                        'severity' => 'high',
                        'recommendation' => '修改目录权限，移除全局执行权限。'
                    ];
                }
            }
        }
        
        return !empty($issues) ? [
            'test_id' => 'file-permissions',
            'test_name' => '文件权限检查',
            'status' => 'failed',
            'issues' => $issues
        ] : null;
    }
    
    /**
     * 获取最近的测试结果
     * 
     * @param int $days 天数
     * @return array 测试结果
     */
    protected function getRecentTestResults($days = 30)
    {
        $cutoffTime = time() - ($days * 86400);
        
        try {
            $results = $this->db->query(
                "SELECT * FROM security_test_results WHERE created_at > ? ORDER BY created_at DESC",
                [$cutoffTime]
            )->fetchAll();
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('获取最近测试结果失败', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * 获取未解决的问题
     * 
     * @return array 未解决的问题
     */
    protected function getUnresolvedIssues()
    {
        try {
            $issues = $this->db->query(
                "SELECT * FROM security_issues WHERE status = 'open' ORDER BY severity DESC, created_at DESC"
            )->fetchAll();
            
            return $issues;
        } catch (\Exception $e) {
            $this->logger->error('获取未解决问题失败', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * 计算安全评分
     * 
     * @param array $testResults 测试结果
     * @return int 安全评分(0-100)
     */
    protected function calculateSecurityScore($testResults)
    {
        if (empty($testResults)) {
            return 0;
        }
        
        $baseScore = 100;
        $issueWeights = [
            'critical' => 25,
            'high' => 15,
            'medium' => 8,
            'low' => 3
        ];
        
        $issues = [];
        foreach ($testResults as $result) {
            if (isset($result['issues']) && is_array($result['issues'])) {
                $issues = array_merge($issues, $result['issues']);
            }
        }
        
        $deductions = 0;
        foreach ($issues as $issue) {
            if (isset($issue['severity']) && isset($issueWeights[$issue['severity']])) {
                $deductions += $issueWeights[$issue['severity']];
            }
        }
        
        $score = max(0, $baseScore - $deductions);
        return $score;
    }
    
    /**
     * 确定安全级别
     * 
     * @param int $score 安全评分
     * @return string 安全级别
     */
    protected function determineSecurityLevel($score)
    {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 75) {
            return 'good';
        } elseif ($score >= 60) {
            return 'fair';
        } elseif ($score >= 40) {
            return 'poor';
        } else {
            return 'critical';
        }
    }
    
    /**
     * 按严重程度计数问题
     * 
     * @param array $issues 问题列表
     * @param string $severity 严重程度
     * @return int 计数
     */
    protected function countIssuesBySeverity($issues, $severity)
    {
        $count = 0;
        foreach ($issues as $issue) {
            if (isset($issue['severity']) && $issue['severity'] === $severity) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * 获取顶级建议
     * 
     * @param array $issues 问题列表
     * @param int $limit 限制数量
     * @return array 建议列表
     */
    protected function getTopRecommendations($issues, $limit = 5)
    {
        // 按严重程度排序问题
        usort($issues, function($a, $b) {
            $severityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
            $severityA = isset($a['severity']) && isset($severityOrder[$a['severity']]) ? $severityOrder[$a['severity']] : 4;
            $severityB = isset($b['severity']) && isset($severityOrder[$b['severity']]) ? $severityOrder[$b['severity']] : 4;
            
            return $severityA - $severityB;
        });
        
        $recommendations = [];
        foreach (array_slice($issues, 0, $limit) as $issue) {
            if (isset($issue['recommendation'])) {
                $recommendations[] = [
                    'title' => isset($issue['title']) ? $issue['title'] : '未知问题',
                    'recommendation' => $issue['recommendation'],
                    'severity' => isset($issue['severity']) ? $issue['severity'] : 'medium'
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * 从数据库获取最后扫描时间
     * 
     * @return int|null 最后扫描时间戳
     */
    protected function getLastScanTimeFromDb()
    {
        try {
            $result = $this->db->query(
                "SELECT MAX(created_at) as last_scan FROM security_test_results"
            )->fetch();
            
            return $result && isset($result['last_scan']) ? $result['last_scan'] : null;
        } catch (\Exception $e) {
            $this->logger->error('获取最后扫描时间失败', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * 更新最后扫描时间
     */
    protected function updateLastScanTime()
    {
        $this->lastScanTime = time();
    }
    
    /**
     * 保存测试结果
     * 
     * @param array $results 测试结果
     * @param string $testType 测试类型
     */
    protected function saveTestResults($results, $testType)
    {
        try {
            $data = [
                'test_type' => $testType,
                'results' => json_encode($results),
                'created_at' => time(),
                'status' => $this->getOverallTestStatus($results)
            ];
            
            $this->db->insert('security_test_results', $data);
            
            // 保存发现的问题
            $this->saveIssues($results);
            
            $this->logger->info('测试结果已保存', [
                'test_type' => $testType,
                'status' => $data['status']
            ]);
        } catch (\Exception $e) {
            $this->logger->error('保存测试结果失败', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * 保存发现的问题
     * 
     * @param array $results 测试结果
     */
    protected function saveIssues($results)
    {
        foreach ($results as $result) {
            if (isset($result['issues']) && is_array($result['issues'])) {
                foreach ($result['issues'] as $issue) {
                    if (!isset($issue['id'])) {
                        continue;
                    }
                    
                    try {
                        // 检查问题是否已存在
                        $existingIssue = $this->db->query(
                            "SELECT * FROM security_issues WHERE issue_id = ?",
                            [$issue['id']]
                        )->fetch();
                        
                        if ($existingIssue) {
                            // 更新现有问题
                            $this->db->update('security_issues', [
                                'last_detected' => time(),
                                'detection_count' => $existingIssue['detection_count'] + 1
                            ], ['issue_id' => $issue['id']]);
                        } else {
                            // 插入新问题
                            $this->db->insert('security_issues', [
                                'issue_id' => $issue['id'],
                                'title' => $issue['title'] ?? '未知问题',
                                'description' => $issue['description'] ?? '',
                                'severity' => $issue['severity'] ?? 'medium',
                                'recommendation' => $issue['recommendation'] ?? '',
                                'status' => 'open',
                                'first_detected' => time(),
                                'last_detected' => time(),
                                'detection_count' => 1
                            ]);
                        }
                    } catch (\Exception $e) {
                        $this->logger->error('保存问题失败', [
                            'issue_id' => $issue['id'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * 获取测试结果的整体状态
     * 
     * @param array $results 测试结果
     * @return string 整体状态
     */
    protected function getOverallTestStatus($results)
    {
        foreach ($results as $result) {
            if (isset($result['status']) && $result['status'] === 'failed') {
                return 'failed';
            }
        }
        
        return 'passed';
    }
    
    /**
     * 生成安全报告
     * 
     * @param string $reportType 报告类型
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @return array 安全报告
     */
    public function generateReport($reportType = 'summary', $startDate = null, $endDate = null)
    {
        // 设置默认日期范围
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        // 转换为时间戳
        $startTimestamp = strtotime($startDate . ' 00:00:00');
        $endTimestamp = strtotime($endDate . ' 23:59:59');
        
        // 获取时间范围内的测试结果
        $results = $this->getTestResultsByDateRange($startTimestamp, $endTimestamp);
        
        // 获取时间范围内的问题
        $issues = $this->getIssuesByDateRange($startTimestamp, $endTimestamp);
        
        // 按报告类型生成报告
        switch ($reportType) {
            case 'summary':
                return $this->generateSummaryReport($results, $issues, $startDate, $endDate);
            case 'detailed':
                return $this->generateDetailedReport($results, $issues, $startDate, $endDate);
            case 'executive':
                return $this->generateExecutiveReport($results, $issues, $startDate, $endDate);
            default:
                throw new \Exception('不支持的报告类型: ' . $reportType);
        }
    }
    
    /**
     * 获取安全建议
     * 
     * @return array 安全建议
     */
    public function getSecurityRecommendations()
    {
        // 获取未解决的问题
        $issues = $this->getUnresolvedIssues();
        
        // 获取顶级建议
        $recommendations = $this->getTopRecommendations($issues, 10);
        
        // 添加一般安全建议
        $generalRecommendations = [
            [
                'title' => '定期更新系统和依赖',
                'recommendation' => '确保系统和所有依赖包保持最新，以修复已知的安全漏洞。',
                'severity' => 'medium'
            ],
            [
                'title' => '实施多因素认证',
                'recommendation' => '为所有用户账户启用多因素认证，特别是管理员账户。',
                'severity' => 'high'
            ],
            [
                'title' => '定期备份数据',
                'recommendation' => '实施定期备份策略，并测试恢复过程以确保数据安全。',
                'severity' => 'high'
            ],
            [
                'title' => '加密敏感数据',
                'recommendation' => '确保所有敏感数据在存储和传输过程中都使用强加密算法进行加密。',
                'severity' => 'high'
            ],
            [
                'title' => '实施最小权限原则',
                'recommendation' => '为用户和系统组件分配最小必要的权限，以减少潜在的攻击面。',
                'severity' => 'medium'
            ]
        ];
        
        // 合并建议
        $allRecommendations = array_merge($recommendations, $generalRecommendations);
        
        // 移除重复项
        $uniqueRecommendations = [];
        $titles = [];
        
        foreach ($allRecommendations as $recommendation) {
            if (!in_array($recommendation['title'], $titles)) {
                $titles[] = $recommendation['title'];
                $uniqueRecommendations[] = $recommendation;
            }
        }
        
        return $uniqueRecommendations;
    }
} 