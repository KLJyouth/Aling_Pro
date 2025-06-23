<?php

namespace AlingAi\Services;

use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use Psr\Log\LoggerInterface;

/**
 * 增强安全服务
 * 提供与UnifiedAdminController兼容的安全功能
 */
class EnhancedSecurityService
{
    private DatabaseServiceInterface $db;
    private CacheService $cache;
    private LoggerInterface $logger;
    private array $config;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->config = $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        return [
            'scan_timeout' => 300, // 5分钟
            'max_scan_files' => 10000,
            'excluded_paths' => [
                '/vendor/',
                '/node_modules/',
                '/.git/',
                '/storage/cache/',
                '/storage/logs/'
            ],
            'security_headers' => [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block',
                'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
            ],
            'file_extensions' => [
                'php', 'html', 'js', 'css', 'json', 'xml', 'sql'
            ]
        ];
    }

    /**
     * 执行安全扫描
     */
    public function performSecurityScan(array $options = []): array
    {
        $scanId = $this->generateScanId();
        $startTime = microtime(true);

        try {
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

            // 执行各种安全检查
            $checks = [
                'file_permissions' => $this->checkFilePermissions(),
                'dangerous_functions' => $this->checkDangerousFunctions(),
                'configuration' => $this->checkConfiguration(),
                'headers' => $this->checkSecurityHeaders(),
                'directory_listing' => $this->checkDirectoryListing(),
                'sensitive_files' => $this->checkSensitiveFiles(),
                'database' => $this->checkDatabaseSecurity(),
                'session' => $this->checkSessionSecurity()
            ];

            $vulnerabilities = [];
            foreach ($checks as $checkType => $checkResult) {
                if (isset($checkResult['vulnerabilities'])) {
                    $vulnerabilities = array_merge($vulnerabilities, $checkResult['vulnerabilities']);
                }
                $results[$checkType] = $checkResult;
            }

            $results['vulnerabilities'] = $vulnerabilities;
            $results['security_score'] = $this->calculateSecurityScore($checks);
            $results['recommendations'] = $this->generateRecommendations($checks);

            $endTime = microtime(true);
            $results['end_time'] = date('Y-m-d H:i:s');
            $results['duration'] = round($endTime - $startTime, 2);
            $results['status'] = 'completed';

            $this->logger->info('安全扫描完成', [
                'scan_id' => $scanId,
                'vulnerabilities_found' => count($vulnerabilities),
                'security_score' => $results['security_score']
            ]);

            return $results;

        } catch (\Exception $e) {
            $this->logger->error('安全扫描失败', [
                'scan_id' => $scanId,
                'error' => $e->getMessage()
            ]);

            return [
                'scan_id' => $scanId,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'start_time' => date('Y-m-d H:i:s'),
                'end_time' => date('Y-m-d H:i:s'),
                'duration' => round(microtime(true) - $startTime, 2)
            ];
        }
    }

    /**
     * 检查文件权限
     */
    private function checkFilePermissions(): array
    {
        $vulnerabilities = [];
        $basePath = __DIR__ . '/../..';
        
        $criticalPaths = [
            'config' => $basePath . '/config',
            'src' => $basePath . '/src',
            'storage' => $basePath . '/storage',
            'public' => $basePath . '/public'
        ];

        foreach ($criticalPaths as $name => $path) {
            if (is_dir($path)) {
                $perms = fileperms($path);
                $octal = substr(sprintf('%o', $perms), -4);
                
                // 检查是否过于宽松的权限
                if ($name === 'config' || $name === 'src') {
                    if (octdec($octal) & 0002) { // 世界可写
                        $vulnerabilities[] = [
                            'type' => 'file_permissions',
                            'severity' => 'high',
                            'message' => "目录 {$name} 具有世界可写权限 ({$octal})",
                            'path' => $path,
                            'recommendation' => '移除世界可写权限'
                        ];
                    }
                }
            }
        }

        // 检查敏感文件权限
        $sensitiveFiles = [
            $basePath . '/.env',
            $basePath . '/composer.json',
            $basePath . '/composer.lock'
        ];

        foreach ($sensitiveFiles as $file) {
            if (is_file($file)) {
                $perms = fileperms($file);
                $octal = substr(sprintf('%o', $perms), -4);
                
                if (octdec($octal) & 0044) { // 世界可读
                    $vulnerabilities[] = [
                        'type' => 'file_permissions',
                        'severity' => 'medium',
                        'message' => "敏感文件具有过宽权限: " . basename($file) . " ({$octal})",
                        'path' => $file,
                        'recommendation' => '限制文件权限为 600 或 640'
                    ];
                }
            }
        }

        return [
            'status' => empty($vulnerabilities) ? 'secure' : 'vulnerable',
            'vulnerabilities' => $vulnerabilities,
            'checked_paths' => count($criticalPaths) + count($sensitiveFiles)
        ];
    }

    /**
     * 检查危险函数
     */
    private function checkDangerousFunctions(): array
    {
        $dangerousFunctions = [
            'eval', 'exec', 'shell_exec', 'system', 'passthru',
            'file_get_contents', 'file_put_contents', 'fopen', 'fwrite',
            'unlink', 'rmdir', 'move_uploaded_file'
        ];

        $vulnerabilities = [];
        $basePath = __DIR__ . '/../..';
        
        $files = $this->scanPhpFiles($basePath);
        
        foreach ($files as $file) {
            if ($this->isExcludedPath($file)) {
                continue;
            }

            $content = file_get_contents($file);
            foreach ($dangerousFunctions as $func) {
                if (preg_match('/\b' . preg_quote($func) . '\s*\(/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $lineNumber = substr_count(substr($content, 0, $matches[0][1]), "\n") + 1;
                    
                    $vulnerabilities[] = [
                        'type' => 'dangerous_function',
                        'severity' => $this->getFunctionSeverity($func),
                        'message' => "检测到危险函数: {$func}",
                        'file' => str_replace($basePath, '', $file),
                        'line' => $lineNumber,
                        'function' => $func,
                        'recommendation' => "审查 {$func} 函数的使用，确保输入验证和安全性"
                    ];
                }
            }
        }

        return [
            'status' => empty($vulnerabilities) ? 'secure' : 'vulnerable',
            'vulnerabilities' => $vulnerabilities,
            'scanned_files' => count($files)
        ];
    }

    /**
     * 检查配置安全
     */
    private function checkConfiguration(): array
    {
        $vulnerabilities = [];

        // 检查PHP配置
        $phpChecks = [
            'display_errors' => [
                'current' => ini_get('display_errors'),
                'secure' => '0',
                'severity' => 'medium'
            ],
            'expose_php' => [
                'current' => ini_get('expose_php'),
                'secure' => '0',
                'severity' => 'low'
            ],
            'allow_url_fopen' => [
                'current' => ini_get('allow_url_fopen'),
                'secure' => '0',
                'severity' => 'medium'
            ],
            'allow_url_include' => [
                'current' => ini_get('allow_url_include'),
                'secure' => '0',
                'severity' => 'high'
            ]
        ];

        foreach ($phpChecks as $directive => $check) {
            if ($check['current'] !== $check['secure']) {
                $vulnerabilities[] = [
                    'type' => 'php_configuration',
                    'severity' => $check['severity'],
                    'message' => "不安全的PHP配置: {$directive} = {$check['current']}",
                    'directive' => $directive,
                    'current_value' => $check['current'],
                    'recommended_value' => $check['secure'],
                    'recommendation' => "设置 {$directive} = {$check['secure']}"
                ];
            }
        }

        // 检查环境变量
        if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
            $vulnerabilities[] = [
                'type' => 'environment_config',
                'severity' => 'medium',
                'message' => '生产环境中启用了调试模式',
                'recommendation' => '在生产环境中设置 APP_DEBUG=false'
            ];
        }

        return [
            'status' => empty($vulnerabilities) ? 'secure' : 'vulnerable',
            'vulnerabilities' => $vulnerabilities,
            'checked_directives' => count($phpChecks)
        ];
    }

    /**
     * 检查安全头
     */
    private function checkSecurityHeaders(): array
    {
        $vulnerabilities = [];
        $requiredHeaders = $this->config['security_headers'];

        // 模拟检查（实际应用中可能需要发送HTTP请求）
        $currentHeaders = $this->getCurrentHeaders();

        foreach ($requiredHeaders as $header => $expectedValue) {
            if (!isset($currentHeaders[$header])) {
                $vulnerabilities[] = [
                    'type' => 'missing_header',
                    'severity' => 'medium',
                    'message' => "缺少安全头: {$header}",
                    'header' => $header,
                    'expected_value' => $expectedValue,
                    'recommendation' => "添加安全头: {$header}: {$expectedValue}"
                ];
            }
        }

        return [
            'status' => empty($vulnerabilities) ? 'secure' : 'vulnerable',
            'vulnerabilities' => $vulnerabilities,
            'checked_headers' => count($requiredHeaders)
        ];
    }

    /**
     * 检查目录列举
     */
    private function checkDirectoryListing(): array
    {
        $vulnerabilities = [];
        
        // 检查是否存在可能的目录列举漏洞
        $directories = [
            '/storage',
            '/storage/logs',
            '/storage/cache',
            '/uploads'
        ];

        foreach ($directories as $dir) {
            $indexFile = __DIR__ . '/../..' . $dir . '/index.html';
            if (!file_exists($indexFile)) {
                $vulnerabilities[] = [
                    'type' => 'directory_listing',
                    'severity' => 'medium',
                    'message' => "目录可能允许列举: {$dir}",
                    'directory' => $dir,
                    'recommendation' => "在 {$dir} 目录中添加 index.html 文件"
                ];
            }
        }

        return [
            'status' => empty($vulnerabilities) ? 'secure' : 'vulnerable',
            'vulnerabilities' => $vulnerabilities,
            'checked_directories' => count($directories)
        ];
    }

    /**
     * 检查敏感文件
     */
    private function checkSensitiveFiles(): array
    {
        $vulnerabilities = [];
        $basePath = __DIR__ . '/../..';
        
        $sensitiveFiles = [
            '.env',
            '.env.backup',
            '.env.example',
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            '.git/config',
            'phpinfo.php',
            'info.php'
        ];

        foreach ($sensitiveFiles as $file) {
            $fullPath = $basePath . '/' . $file;
            if (file_exists($fullPath)) {
                // 检查文件是否在公共目录中
                $publicPath = $basePath . '/public/' . $file;
                if (file_exists($publicPath)) {
                    $vulnerabilities[] = [
                        'type' => 'sensitive_file_exposure',
                        'severity' => 'high',
                        'message' => "敏感文件暴露在公共目录: {$file}",
                        'file' => $file,
                        'path' => $publicPath,
                        'recommendation' => "移除公共目录中的敏感文件"
                    ];
                }
            }
        }

        return [
            'status' => empty($vulnerabilities) ? 'secure' : 'vulnerable',
            'vulnerabilities' => $vulnerabilities,
            'checked_files' => count($sensitiveFiles)
        ];
    }

    /**
     * 检查数据库安全
     */
    private function checkDatabaseSecurity(): array
    {
        $vulnerabilities = [];

        try {
            // 检查数据库连接
            $result = $this->db->query("SELECT VERSION() as version")->fetch();
            
            // 检查默认账户（这里简化处理）
            $vulnerabilities[] = [
                'type' => 'database_info',
                'severity' => 'info',
                'message' => "数据库版本: " . ($result['version'] ?? 'Unknown'),
                'recommendation' => '确保数据库版本是最新的，并应用了所有安全补丁'
            ];

        } catch (\Exception $e) {
            $vulnerabilities[] = [
                'type' => 'database_connection',
                'severity' => 'critical',
                'message' => '数据库连接失败',
                'error' => $e->getMessage(),
                'recommendation' => '检查数据库配置和连接参数'
            ];
        }

        return [
            'status' => empty($vulnerabilities) ? 'secure' : 'vulnerable',
            'vulnerabilities' => $vulnerabilities
        ];
    }

    /**
     * 检查会话安全
     */
    private function checkSessionSecurity(): array
    {
        $vulnerabilities = [];

        $sessionChecks = [
            'session.use_strict_mode' => [
                'current' => ini_get('session.use_strict_mode'),
                'secure' => '1',
                'severity' => 'medium'
            ],
            'session.cookie_httponly' => [
                'current' => ini_get('session.cookie_httponly'),
                'secure' => '1',
                'severity' => 'high'
            ],
            'session.cookie_secure' => [
                'current' => ini_get('session.cookie_secure'),
                'secure' => '1',
                'severity' => 'medium'
            ]
        ];

        foreach ($sessionChecks as $directive => $check) {
            if ($check['current'] !== $check['secure']) {
                $vulnerabilities[] = [
                    'type' => 'session_security',
                    'severity' => $check['severity'],
                    'message' => "不安全的会话配置: {$directive} = {$check['current']}",
                    'directive' => $directive,
                    'current_value' => $check['current'],
                    'recommended_value' => $check['secure'],
                    'recommendation' => "设置 {$directive} = {$check['secure']}"
                ];
            }
        }

        return [
            'status' => empty($vulnerabilities) ? 'secure' : 'vulnerable',
            'vulnerabilities' => $vulnerabilities,
            'checked_directives' => count($sessionChecks)
        ];
    }

    /**
     * 计算安全评分
     */
    private function calculateSecurityScore(array $checks): int
    {
        $totalChecks = 0;
        $secureChecks = 0;
        $criticalIssues = 0;
        $highIssues = 0;
        $mediumIssues = 0;

        foreach ($checks as $check) {
            $totalChecks++;
            if ($check['status'] === 'secure') {
                $secureChecks++;
            } else {
                foreach ($check['vulnerabilities'] as $vuln) {
                    switch ($vuln['severity']) {
                        case 'critical':
                            $criticalIssues++;
                            break;
                        case 'high':
                            $highIssues++;
                            break;
                        case 'medium':
                            $mediumIssues++;
                            break;
                    }
                }
            }
        }

        // 计算基础评分
        $baseScore = $totalChecks > 0 ? ($secureChecks / $totalChecks) * 100 : 0;

        // 根据严重问题调整评分
        $penalty = ($criticalIssues * 20) + ($highIssues * 10) + ($mediumIssues * 5);
        $finalScore = max(0, $baseScore - $penalty);

        return (int) round($finalScore);
    }

    /**
     * 生成安全建议
     */
    private function generateRecommendations(array $checks): array
    {
        $recommendations = [];

        foreach ($checks as $checkType => $check) {
            if ($check['status'] !== 'secure') {
                foreach ($check['vulnerabilities'] as $vuln) {
                    if (isset($vuln['recommendation'])) {
                        $recommendations[] = [
                            'category' => $checkType,
                            'severity' => $vuln['severity'],
                            'recommendation' => $vuln['recommendation'],
                            'issue' => $vuln['message']
                        ];
                    }
                }
            }
        }

        // 按严重程度排序
        usort($recommendations, function($a, $b) {
            $severityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1, 'info' => 0];
            return ($severityOrder[$b['severity']] ?? 0) - ($severityOrder[$a['severity']] ?? 0);
        });

        return $recommendations;
    }

    /**
     * 扫描PHP文件
     */
    private function scanPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                
                // 限制扫描文件数量
                if (count($files) >= $this->config['max_scan_files']) {
                    break;
                }
            }
        }

        return $files;
    }

    /**
     * 检查是否为排除路径
     */
    private function isExcludedPath(string $path): bool
    {
        foreach ($this->config['excluded_paths'] as $excludedPath) {
            if (strpos($path, $excludedPath) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取函数严重性
     */
    private function getFunctionSeverity(string $function): string
    {
        $highRisk = ['eval', 'exec', 'shell_exec', 'system', 'passthru'];
        $mediumRisk = ['file_get_contents', 'file_put_contents', 'unlink', 'rmdir'];
        
        if (in_array($function, $highRisk)) {
            return 'high';
        } elseif (in_array($function, $mediumRisk)) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * 获取当前头信息（模拟）
     */
    private function getCurrentHeaders(): array
    {
        // 在实际实现中，这里应该检查服务器配置或发送HTTP请求
        return [];
    }

    /**
     * 生成扫描ID
     */
    private function generateScanId(): string
    {
        return 'scan_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 8);
    }

    /**
     * 获取安全状态
     */
    public function getSecurityStatus(): array
    {
        try {
            $quickScan = $this->performQuickSecurityCheck();
            
            return [
                'status' => $quickScan['overall_status'],
                'last_scan' => $quickScan['timestamp'],
                'issues_count' => $quickScan['issues_count'],
                'security_score' => $quickScan['security_score'],
                'critical_issues' => $quickScan['critical_issues']
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 执行快速安全检查
     */
    public function performQuickSecurityCheck(): array
    {
        $checks = [
            'permissions' => $this->checkFilePermissions(),
            'configuration' => $this->checkConfiguration(),
            'sensitive_files' => $this->checkSensitiveFiles()
        ];

        $totalIssues = 0;
        $criticalIssues = 0;
        
        foreach ($checks as $check) {
            $totalIssues += count($check['vulnerabilities']);
            foreach ($check['vulnerabilities'] as $vuln) {
                if ($vuln['severity'] === 'critical') {
                    $criticalIssues++;
                }
            }
        }

        $overallStatus = $criticalIssues > 0 ? 'critical' : ($totalIssues > 0 ? 'warning' : 'secure');
        $securityScore = $this->calculateSecurityScore($checks);

        return [
            'overall_status' => $overallStatus,
            'security_score' => $securityScore,
            'issues_count' => $totalIssues,
            'critical_issues' => $criticalIssues,
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * 健康检查方法（API兼容）
     */
    public function healthCheck(): array
    {
        return $this->getSecurityStatus();
    }
}
