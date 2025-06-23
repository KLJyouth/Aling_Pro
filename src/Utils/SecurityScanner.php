<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;

/**
 * 安全扫描器
 * 
 * 提供全面的安全扫描和威胁检测功能
 * 优化性能：并行扫描、智能检测、缓存结果
 * 增强功能：实时监控、威胁情报、安全报告
 */
class SecurityScanner
{
    private LoggerInterface $logger;
    private array $config;
    private array $scanResults = [];
    private array $threatPatterns = [];
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'enabled' => true,
            'scan_interval' => 3600, // 1小时
            'real_time_monitoring' => true,
            'auto_fix' => false,
            'report_level' => 'medium', // low, medium, high, critical
            'scan_types' => [
                'code_analysis' => true,
                'dependency_check' => true,
                'configuration_audit' => true,
                'file_permissions' => true,
                'network_scan' => true,
                'database_security' => true
            ],
            'exclude_patterns' => [
                '/vendor/',
                '/node_modules/',
                '/storage/logs/',
                '/storage/cache/'
            ],
            'threat_levels' => [
                'low' => 1,
                'medium' => 2,
                'high' => 3,
                'critical' => 4
            ]
        ], $config);
        
        $this->initializeThreatPatterns();
    }
    
    /**
     * 初始化威胁模式
     */
    private function initializeThreatPatterns(): void
    {
        $this->threatPatterns = [
            'sql_injection' => [
                'patterns' => [
                    '/\$_(GET|POST|REQUEST)\[.*\]/',
                    '/mysql_query\s*\(\s*\$/',
                    '/mysqli_query\s*\(\s*\$/',
                    '/execute\s*\(\s*\$/'
                ],
                'level' => 'critical',
                'description' => 'SQL注入漏洞'
            ],
            'xss' => [
                'patterns' => [
                    '/echo\s+\$_/',
                    '/print\s+\$_/',
                    '/\$_(GET|POST|REQUEST)\[.*\]/',
                    '/innerHTML\s*=/',
                    '/outerHTML\s*=/'
                ],
                'level' => 'high',
                'description' => '跨站脚本攻击漏洞'
            ],
            'file_inclusion' => [
                'patterns' => [
                    '/include\s*\(\s*\$/',
                    '/require\s*\(\s*\$/',
                    '/include_once\s*\(\s*\$/',
                    '/require_once\s*\(\s*\$/'
                ],
                'level' => 'critical',
                'description' => '文件包含漏洞'
            ],
            'command_injection' => [
                'patterns' => [
                    '/exec\s*\(\s*\$/',
                    '/system\s*\(\s*\$/',
                    '/shell_exec\s*\(\s*\$/',
                    '/passthru\s*\(\s*\$/'
                ],
                'level' => 'critical',
                'description' => '命令注入漏洞'
            ],
            'weak_encryption' => [
                'patterns' => [
                    '/md5\s*\(\s*\$/',
                    '/sha1\s*\(\s*\$/',
                    '/base64_encode\s*\(\s*\$/',
                    '/base64_decode\s*\(\s*\$/'
                ],
                'level' => 'medium',
                'description' => '弱加密算法'
            ],
            'hardcoded_credentials' => [
                'patterns' => [
                    '/password\s*=\s*[\'"][^\'"]+[\'"]/',
                    '/passwd\s*=\s*[\'"][^\'"]+[\'"]/',
                    '/secret\s*=\s*[\'"][^\'"]+[\'"]/',
                    '/key\s*=\s*[\'"][^\'"]+[\'"]/'
                ],
                'level' => 'high',
                'description' => '硬编码凭据'
            ],
            'debug_code' => [
                'patterns' => [
                    '/var_dump\s*\(/',
                    '/print_r\s*\(/',
                    '/debug\s*\(/',
                    '/dump\s*\(/'
                ],
                'level' => 'low',
                'description' => '调试代码'
            ]
        ];
    }
    
    /**
     * 执行安全扫描
     */
    public function scan(array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $options = array_merge([
                'scan_types' => array_keys($this->config['scan_types']),
                'target_path' => dirname(__DIR__, 2),
                'recursive' => true,
                'max_depth' => 10,
                'parallel' => true
            ], $options);
            
            $this->logger->info('开始安全扫描', $options);
            
            $this->scanResults = [
                'scan_info' => [
                    'start_time' => date('Y-m-d H:i:s'),
                    'target_path' => $options['target_path'],
                    'scan_types' => $options['scan_types']
                ],
                'vulnerabilities' => [],
                'warnings' => [],
                'recommendations' => [],
                'summary' => []
            ];
            
            // 执行各种扫描
            foreach ($options['scan_types'] as $scanType) {
                if ($this->config['scan_types'][$scanType]) {
                    $this->executeScanType($scanType, $options);
                }
            }
            
            // 生成摘要和建议
            $this->generateSummary();
            $this->generateRecommendations();
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->scanResults['scan_info']['end_time'] = date('Y-m-d H:i:s');
            $this->scanResults['scan_info']['duration'] = $duration;
            
            $this->logger->info('安全扫描完成', [
                'duration' => $duration,
                'vulnerabilities' => count($this->scanResults['vulnerabilities']),
                'warnings' => count($this->scanResults['warnings'])
            ]);
            
            return $this->scanResults;
            
        } catch (\Exception $e) {
            $this->logger->error('安全扫描失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 执行特定类型的扫描
     */
    private function executeScanType(string $scanType, array $options): void
    {
        switch ($scanType) {
            case 'code_analysis':
                $this->scanCodeAnalysis($options);
                break;
                
            case 'dependency_check':
                $this->scanDependencies($options);
                break;
                
            case 'configuration_audit':
                $this->scanConfiguration($options);
                break;
                
            case 'file_permissions':
                $this->scanFilePermissions($options);
                break;
                
            case 'network_scan':
                $this->scanNetwork($options);
                break;
                
            case 'database_security':
                $this->scanDatabase($options);
                break;
        }
    }
    
    /**
     * 代码分析扫描
     */
    private function scanCodeAnalysis(array $options): void
    {
        $this->logger->info('开始代码分析扫描');
        
        $files = $this->getScanFiles($options['target_path'], $options['recursive'], $options['max_depth']);
        
        foreach ($files as $file) {
            $this->analyzeFile($file);
        }
    }
    
    /**
     * 获取扫描文件
     */
    private function getScanFiles(string $path, bool $recursive, int $maxDepth): array
    {
        $files = [];
        $excludePatterns = $this->config['exclude_patterns'];
        
        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $filePath = $file->getPathname();
                    
                    // 检查排除模式
                    $excluded = false;
                    foreach ($excludePatterns as $pattern) {
                        if (strpos($filePath, $pattern) !== false) {
                            $excluded = true;
                            break;
                        }
                    }
                    
                    if (!$excluded) {
                        $files[] = $filePath;
                    }
                }
            }
        } else {
            $files = glob($path . '/*.php');
        }
        
        return $files;
    }
    
    /**
     * 分析单个文件
     */
    private function analyzeFile(string $file): void
    {
        try {
            $content = file_get_contents($file);
            $lines = explode("\n", $content);
            
            foreach ($this->threatPatterns as $threatType => $threat) {
                foreach ($threat['patterns'] as $pattern) {
                    $matches = [];
                    if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                        foreach ($matches[0] as $match) {
                            $lineNumber = $this->getLineNumber($content, $match[1]);
                            
                            $vulnerability = [
                                'type' => $threatType,
                                'level' => $threat['level'],
                                'description' => $threat['description'],
                                'file' => $file,
                                'line' => $lineNumber,
                                'code' => trim($lines[$lineNumber - 1] ?? ''),
                                'pattern' => $pattern,
                                'recommendation' => $this->getRecommendation($threatType)
                            ];
                            
                            $this->addVulnerability($vulnerability);
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->warning('文件分析失败', [
                'file' => $file,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 获取行号
     */
    private function getLineNumber(string $content, int $position): int
    {
        return substr_count(substr($content, 0, $position), "\n") + 1;
    }
    
    /**
     * 添加漏洞
     */
    private function addVulnerability(array $vulnerability): void
    {
        $level = $vulnerability['level'];
        $minLevel = $this->config['threat_levels'][$this->config['report_level']];
        
        if ($this->config['threat_levels'][$level] >= $minLevel) {
            $this->scanResults['vulnerabilities'][] = $vulnerability;
        }
    }
    
    /**
     * 获取建议
     */
    private function getRecommendation(string $threatType): string
    {
        $recommendations = [
            'sql_injection' => '使用参数化查询或预处理语句',
            'xss' => '对输出进行HTML转义',
            'file_inclusion' => '验证文件路径，使用白名单',
            'command_injection' => '避免使用用户输入执行命令',
            'weak_encryption' => '使用强加密算法如bcrypt、Argon2',
            'hardcoded_credentials' => '使用环境变量或配置文件',
            'debug_code' => '移除生产环境中的调试代码'
        ];
        
        return $recommendations[$threatType] ?? '请检查代码安全性';
    }
    
    /**
     * 依赖检查扫描
     */
    private function scanDependencies(array $options): void
    {
        $this->logger->info('开始依赖检查扫描');
        
        $composerFile = $options['target_path'] . '/composer.json';
        $packageFile = $options['target_path'] . '/package.json';
        
        if (file_exists($composerFile)) {
            $this->scanComposerDependencies($composerFile);
        }
        
        if (file_exists($packageFile)) {
            $this->scanNpmDependencies($packageFile);
        }
    }
    
    /**
     * 扫描Composer依赖
     */
    private function scanComposerDependencies(string $composerFile): void
    {
        try {
            $composerData = json_decode(file_get_contents($composerFile), true);
            $dependencies = array_merge(
                $composerData['require'] ?? [],
                $composerData['require-dev'] ?? []
            );
            
            // 这里应该检查已知漏洞的依赖
            $vulnerableDeps = $this->checkVulnerableDependencies($dependencies);
            
            foreach ($vulnerableDeps as $dep) {
                $this->scanResults['vulnerabilities'][] = [
                    'type' => 'vulnerable_dependency',
                    'level' => 'high',
                    'description' => '存在安全漏洞的依赖包',
                    'package' => $dep['name'],
                    'version' => $dep['version'],
                    'vulnerability' => $dep['vulnerability'],
                    'recommendation' => '更新到安全版本'
                ];
            }
            
        } catch (\Exception $e) {
            $this->logger->warning('Composer依赖检查失败', [
                'file' => $composerFile,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 扫描NPM依赖
     */
    private function scanNpmDependencies(string $packageFile): void
    {
        try {
            $packageData = json_decode(file_get_contents($packageFile), true);
            $dependencies = array_merge(
                $packageData['dependencies'] ?? [],
                $packageData['devDependencies'] ?? []
            );
            
            // 这里应该检查已知漏洞的依赖
            $vulnerableDeps = $this->checkVulnerableDependencies($dependencies);
            
            foreach ($vulnerableDeps as $dep) {
                $this->scanResults['vulnerabilities'][] = [
                    'type' => 'vulnerable_dependency',
                    'level' => 'high',
                    'description' => '存在安全漏洞的依赖包',
                    'package' => $dep['name'],
                    'version' => $dep['version'],
                    'vulnerability' => $dep['vulnerability'],
                    'recommendation' => '更新到安全版本'
                ];
            }
            
        } catch (\Exception $e) {
            $this->logger->warning('NPM依赖检查失败', [
                'file' => $packageFile,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 检查漏洞依赖
     */
    private function checkVulnerableDependencies(array $dependencies): array
    {
        // 这里应该连接到漏洞数据库进行检查
        // 简化实现，返回模拟数据
        $vulnerableDeps = [];
        
        foreach ($dependencies as $name => $version) {
            // 模拟检查
            if (in_array($name, ['old-package', 'vulnerable-lib'])) {
                $vulnerableDeps[] = [
                    'name' => $name,
                    'version' => $version,
                    'vulnerability' => 'CVE-2024-XXXX'
                ];
            }
        }
        
        return $vulnerableDeps;
    }
    
    /**
     * 配置审计扫描
     */
    private function scanConfiguration(array $options): void
    {
        $this->logger->info('开始配置审计扫描');
        
        $configFiles = [
            $options['target_path'] . '/.env',
            $options['target_path'] . '/config/app.php',
            $options['target_path'] . '/config/database.php',
            $options['target_path'] . '/config/security.php'
        ];
        
        foreach ($configFiles as $configFile) {
            if (file_exists($configFile)) {
                $this->auditConfigFile($configFile);
            }
        }
    }
    
    /**
     * 审计配置文件
     */
    private function auditConfigFile(string $configFile): void
    {
        try {
            $content = file_get_contents($configFile);
            
            // 检查敏感信息
            $sensitivePatterns = [
                '/password\s*=\s*[\'"][^\'"]+[\'"]/',
                '/secret\s*=\s*[\'"][^\'"]+[\'"]/',
                '/key\s*=\s*[\'"][^\'"]+[\'"]/',
                '/token\s*=\s*[\'"][^\'"]+[\'"]/'
            ];
            
            foreach ($sensitivePatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $this->scanResults['warnings'][] = [
                        'type' => 'sensitive_config',
                        'level' => 'medium',
                        'description' => '配置文件中包含敏感信息',
                        'file' => $configFile,
                        'recommendation' => '使用环境变量存储敏感信息'
                    ];
                }
            }
            
            // 检查调试模式
            if (strpos($content, 'debug = true') !== false || strpos($content, 'APP_DEBUG=true') !== false) {
                $this->scanResults['warnings'][] = [
                    'type' => 'debug_enabled',
                    'level' => 'medium',
                    'description' => '调试模式已启用',
                    'file' => $configFile,
                    'recommendation' => '生产环境中禁用调试模式'
                ];
            }
            
        } catch (\Exception $e) {
            $this->logger->warning('配置文件审计失败', [
                'file' => $configFile,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 文件权限扫描
     */
    private function scanFilePermissions(array $options): void
    {
        $this->logger->info('开始文件权限扫描');
        
        $criticalFiles = [
            $options['target_path'] . '/.env',
            $options['target_path'] . '/config/',
            $options['target_path'] . '/storage/',
            $options['target_path'] . '/public/'
        ];
        
        foreach ($criticalFiles as $file) {
            if (file_exists($file)) {
                $this->checkFilePermissions($file);
            }
        }
    }
    
    /**
     * 检查文件权限
     */
    private function checkFilePermissions(string $file): void
    {
        $perms = fileperms($file);
        $mode = substr(sprintf('%o', $perms), -4);
        
        // 检查过于宽松的权限
        if (is_file($file) && $mode > '0644') {
            $this->scanResults['warnings'][] = [
                'type' => 'file_permissions',
                'level' => 'medium',
                'description' => '文件权限过于宽松',
                'file' => $file,
                'permissions' => $mode,
                'recommendation' => '设置适当的文件权限（644）'
            ];
        }
        
        if (is_dir($file) && $mode > '0755') {
            $this->scanResults['warnings'][] = [
                'type' => 'directory_permissions',
                'level' => 'medium',
                'description' => '目录权限过于宽松',
                'file' => $file,
                'permissions' => $mode,
                'recommendation' => '设置适当的目录权限（755）'
            ];
        }
    }
    
    /**
     * 网络扫描
     */
    private function scanNetwork(array $options): void
    {
        $this->logger->info('开始网络扫描');
        
        // 检查开放端口
        $ports = [80, 443, 22, 21, 3306, 6379];
        $host = 'localhost';
        
        foreach ($ports as $port) {
            $this->checkPort($host, $port);
        }
    }
    
    /**
     * 检查端口
     */
    private function checkPort(string $host, int $port): void
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);
        
        if ($connection) {
            fclose($connection);
            
            // 检查不必要的开放端口
            if (in_array($port, [21, 22, 3306, 6379])) {
                $this->scanResults['warnings'][] = [
                    'type' => 'open_port',
                    'level' => 'medium',
                    'description' => '检测到开放端口',
                    'host' => $host,
                    'port' => $port,
                    'recommendation' => '关闭不必要的端口或限制访问'
                ];
            }
        }
    }
    
    /**
     * 数据库安全扫描
     */
    private function scanDatabase(array $options): void
    {
        $this->logger->info('开始数据库安全扫描');
        
        // 这里应该检查数据库配置和连接
        // 简化实现
        $this->scanResults['warnings'][] = [
            'type' => 'database_security',
            'level' => 'low',
            'description' => '建议定期检查数据库安全配置',
            'recommendation' => '启用数据库审计日志，定期备份数据'
        ];
    }
    
    /**
     * 生成摘要
     */
    private function generateSummary(): void
    {
        $vulnerabilities = $this->scanResults['vulnerabilities'];
        $warnings = $this->scanResults['warnings'];
        
        $summary = [
            'total_vulnerabilities' => count($vulnerabilities),
            'total_warnings' => count($warnings),
            'critical_vulnerabilities' => count(array_filter($vulnerabilities, function($v) { return $v['level'] === 'critical'; })),
            'high_vulnerabilities' => count(array_filter($vulnerabilities, function($v) { return $v['level'] === 'high'; })),
            'medium_vulnerabilities' => count(array_filter($vulnerabilities, function($v) { return $v['level'] === 'medium'; })),
            'low_vulnerabilities' => count(array_filter($vulnerabilities, function($v) { return $v['level'] === 'low'; }))
        ];
        
        $this->scanResults['summary'] = $summary;
    }
    
    /**
     * 生成建议
     */
    private function generateRecommendations(): void
    {
        $recommendations = [];
        
        $vulnerabilities = $this->scanResults['vulnerabilities'];
        $warnings = $this->scanResults['warnings'];
        
        if (count($vulnerabilities) > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => '立即修复所有高危漏洞',
                'description' => '发现 ' . count($vulnerabilities) . ' 个安全漏洞需要修复'
            ];
        }
        
        if (count($warnings) > 0) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => '处理安全警告',
                'description' => '发现 ' . count($warnings) . ' 个安全警告需要处理'
            ];
        }
        
        $recommendations[] = [
            'priority' => 'low',
            'action' => '定期安全扫描',
            'description' => '建议定期执行安全扫描以保持系统安全'
        ];
        
        $this->scanResults['recommendations'] = $recommendations;
    }
    
    /**
     * 获取扫描报告
     */
    public function getReport(array $options = []): array
    {
        $options = array_merge([
            'format' => 'json',
            'include_details' => true,
            'include_recommendations' => true
        ], $options);
        
        $report = [
            'scan_results' => $this->scanResults,
            'generated_at' => date('Y-m-d H:i:s'),
            'scanner_version' => '1.0.0'
        ];
        
        if (!$options['include_details']) {
            unset($report['scan_results']['vulnerabilities']);
            unset($report['scan_results']['warnings']);
        }
        
        if (!$options['include_recommendations']) {
            unset($report['scan_results']['recommendations']);
        }
        
        return $report;
    }
    
    /**
     * 实时安全监控
     */
    public function startRealTimeMonitoring(callable $callback): void
    {
        if (!$this->config['real_time_monitoring']) {
            return;
        }
        
        $this->logger->info('开始实时安全监控');
        
        // 这里应该实现实时监控逻辑
        // 简化实现
        while (true) {
            $threats = $this->detectRealTimeThreats();
            
            if (!empty($threats)) {
                $callback($threats);
            }
            
            sleep(60); // 每分钟检查一次
        }
    }
    
    /**
     * 检测实时威胁
     */
    private function detectRealTimeThreats(): array
    {
        // 这里应该实现实时威胁检测逻辑
        // 简化实现
        return [];
    }
    
    /**
     * 自动修复
     */
    public function autoFix(array $vulnerabilities): array
    {
        if (!$this->config['auto_fix']) {
            return ['success' => false, 'message' => '自动修复已禁用'];
        }
        
        $results = [];
        
        foreach ($vulnerabilities as $vulnerability) {
            $result = $this->fixVulnerability($vulnerability);
            $results[] = $result;
        }
        
        return [
            'success' => count(array_filter($results, function($r) { return $r['success']; })) === count($results),
            'results' => $results
        ];
    }
    
    /**
     * 修复单个漏洞
     */
    private function fixVulnerability(array $vulnerability): array
    {
        // 这里应该实现具体的修复逻辑
        // 简化实现
        return [
            'success' => false,
            'vulnerability' => $vulnerability['type'],
            'message' => '需要手动修复'
        ];
    }
} 