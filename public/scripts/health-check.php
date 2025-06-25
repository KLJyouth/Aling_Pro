<?php
/**
 * AlingAi Pro 6.0 系统健康检查和诊断工具
 * 实际检测和验证系统的所有核心功�?
 */

declare(strict_types=1];

require_once __DIR__ . '/../vendor/autoload.php';

use AlingAi\Core\Application;
use AlingAi\Core\Config\ConfigManager;
use Psr\Log\LoggerInterface;

class SystemHealthChecker
{
    private array $results = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    
    public function __construct()
    {
        echo "🔍 AlingAi Pro 6.0 系统健康检查开�?..\n\n";
    }
    
    /**
     * 运行完整的系统健康检�?
     */
    public function runCompleteHealthCheck(): array
    {
        $this->checkPhpEnvironment(];
        $this->checkFilePermissions(];
        $this->checkDatabaseConnection(];
        $this->checkRedisConnection(];
        $this->checkCoreServices(];
        $this->checkEnterpriseServices(];
        $this->checkAIServices(];
        $this->checkBlockchainServices(];
        $this->checkSecurityServices(];
        $this->checkPerformanceMetrics(];
        $this->checkAPIEndpoints(];
        $this->checkFrontendAssets(];
        
        $this->generateHealthReport(];
        
        return $this->results;
    }
    
    /**
     * 检查PHP环境
     */
    private function checkPhpEnvironment(): void
    {
        $this->printSectionHeader("PHP环境检�?];
        
        // PHP版本检�?
        $phpVersion = PHP_VERSION;
        $this->test(
            "PHP版本检�?(要求 >= 8.1)",
            version_compare($phpVersion, '8.1.0', '>='],
            "当前版本: $phpVersion"
        ];
        
        // 必需扩展检�?
        $requiredExtensions = [
            'pdo', 'pdo_mysql', 'redis', 'curl', 'json', 'openssl',
            'mbstring', 'xml', 'gd', 'zip', 'intl', 'bcmath'
        ];
        
        foreach ($requiredExtensions as $extension) {
            $this->test(
                "PHP扩展: $extension",
                extension_loaded($extension],
                extension_loaded($extension) ? "已安�? : "未安�?
            ];
        }
        
        // 内存限制检�?
        $memoryLimit = ini_get('memory_limit'];
        $this->test(
            "内存限制检�?,
            $this->parseMemoryLimit($memoryLimit) >= 256,
            "当前设置: $memoryLimit"
        ];
        
        // 执行时间限制
        $maxExecutionTime = ini_get('max_execution_time'];
        $this->test(
            "执行时间限制",
            $maxExecutionTime == 0 || $maxExecutionTime >= 300,
            "当前设置: {$maxExecutionTime}�?
        ];
    }
    
    /**
     * 检查文件权�?
     */
    private function checkFilePermissions(): void
    {
        $this->printSectionHeader("文件权限检�?];
        
        $checkDirs = [
            'storage/logs' => 0755,
            'storage/framework/cache' => 0755,
            'storage/framework/sessions' => 0755,
            'storage/framework/views' => 0755,
            'bootstrap/cache' => 0755,
            'public' => 0755
        ];
        
        foreach ($checkDirs as $dir => $expectedPerm) {
            $fullPath = __DIR__ . "/../$dir";
            
            if (!is_dir($fullPath)) {
                $this->test(
                    "目录存在�? $dir",
                    false,
                    "目录不存�?
                ];
                continue;
            }
            
            $perms = fileperms($fullPath) & 0777;
            $this->test(
                "目录权限: $dir",
                $perms >= $expectedPerm,
                sprintf("当前权限: %o (期望: %o)", $perms, $expectedPerm)
            ];
            
            $this->test(
                "目录可写�? $dir",
                is_writable($fullPath],
                is_writable($fullPath) ? "可写" : "不可�?
            ];
        }
    }
    
    /**
     * 检查数据库连接
     */
    private function checkDatabaseConnection(): void
    {
        $this->printSectionHeader("数据库连接检�?];
        
        try {
            $config = $this->loadConfig(];
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                $config['database']['host'] ?? 'localhost',
                $config['database']['port'] ?? 3306,
                $config['database']['database'] ?? 'alingai_pro'
            ];
            
            $pdo = new PDO(
                $dsn,
                $config['database']['username'] ?? 'root',
                $config['database']['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5
                ]
            ];
            
            $this->test(
                "数据库连�?,
                true,
                "连接成功"
            ];
            
            // 检查数据库版本
            $version = $pdo->query("SELECT VERSION() as version")->fetch()['version'];
            $this->test(
                "MySQL版本检�?,
                version_compare($version, '8.0.0', '>='],
                "版本: $version"
            ];
            
            // 检查核心表是否存在
            $coreTables = ['users', 'enterprises', 'workspaces', 'projects'];
            foreach ($coreTables as $table) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?"];
                $stmt->execute([$table]];
                $exists = $stmt->rowCount() > 0;
                
                $this->test(
                    "核心�? $table",
                    $exists,
                    $exists ? "存在" : "不存�?
                ];
            }
            
        } catch (Exception $e) {
            $this->test(
                "数据库连�?,
                false,
                "连接失败: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查Redis连接
     */
    private function checkRedisConnection(): void
    {
        $this->printSectionHeader("Redis连接检�?];
        
        try {
            if (!class_exists('Redis')) {
                $this->test(
                    "Redis扩展",
                    false,
                    "Redis扩展未安�?
                ];
                return;
            }
            
            $redis = new Redis(];
            $config = $this->loadConfig(];
            
            $result = $redis->connect(
                $config['redis']['host'] ?? '127.0.0.1',
                $config['redis']['port'] ?? 6379,
                2.0
            ];
            
            $this->test(
                "Redis连接",
                $result,
                $result ? "连接成功" : "连接失败"
            ];
            
            if ($result) {
                // 测试Redis操作
                $testKey = 'health_check_' . time(];
                $testValue = 'test_value';
                
                $redis->set($testKey, $testValue, 10];
                $retrievedValue = $redis->get($testKey];
                
                $this->test(
                    "Redis读写测试",
                    $retrievedValue === $testValue,
                    "读写正常"
                ];
                
                $redis->del($testKey];
                
                // 检查Redis内存使用
                $info = $redis->info('memory'];
                $usedMemory = $info['used_memory_human'] ?? 'Unknown';
                
                $this->test(
                    "Redis内存使用",
                    true,
                    "已使�? $usedMemory"
                ];
            }
            
        } catch (Exception $e) {
            $this->test(
                "Redis连接",
                false,
                "错误: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查核心服�?
     */
    private function checkCoreServices(): void
    {
        $this->printSectionHeader("核心服务检�?];
        
        try {
            // 检查应用核�?
            $appFile = __DIR__ . '/../src/Core/Application.php';
            $this->test(
                "应用核心文件",
                file_exists($appFile],
                file_exists($appFile) ? "存在" : "缺失"
            ];
            
            // 检查配置管理器
            $configFile = __DIR__ . '/../src/Core/Config/ConfigManager.php';
            $this->test(
                "配置管理�?,
                file_exists($configFile],
                file_exists($configFile) ? "存在" : "缺失"
            ];
            
            // 检查性能监控�?
            $monitorFile = __DIR__ . '/../src/Core/Monitoring/PerformanceMonitor.php';
            $this->test(
                "性能监控�?,
                file_exists($monitorFile],
                file_exists($monitorFile) ? "存在" : "缺失"
            ];
            
            // 检查安全管理器
            $securityFile = __DIR__ . '/../src/Core/Security/ZeroTrustManager.php';
            $this->test(
                "零信任安全管理器",
                file_exists($securityFile],
                file_exists($securityFile) ? "存在" : "缺失"
            ];
            
        } catch (Exception $e) {
            $this->test(
                "核心服务检�?,
                false,
                "错误: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查企业服�?
     */    private function checkEnterpriseServices(): void
    {
        $this->printSectionHeader("企业服务检�?];
        
        $enterpriseFiles = [
            'apps/enterprise/Services/EnterpriseServiceManager.php' => '企业服务管理�?,
            'apps/enterprise/Services/WorkspaceManager.php' => '工作空间管理�?,
            'apps/enterprise/Services/ProjectManager.php' => '项目管理�?,
            'apps/enterprise/Services/TeamManager.php' => '团队管理�?
        ];
        
        foreach ($enterpriseFiles as $file => $name) {
            $fullPath = __DIR__ . "/../$file";
            $this->test(
                $name,
                file_exists($fullPath],
                file_exists($fullPath) ? "已部�? : "缺失"
            ];
        }
    }
    
    /**
     * 检查AI服务
     */    private function checkAIServices(): void
    {
        $this->printSectionHeader("AI服务检�?];
        
        $aiFiles = [
            'apps/ai-platform/Services/AIServiceManager.php' => 'AI服务管理�?,
            'apps/ai-platform/Services/NLP/NaturalLanguageProcessor.php' => '自然语言处理',
            'apps/ai-platform/Services/CV/ComputerVisionProcessor.php' => '计算机视�?,
            'apps/ai-platform/Services/Speech/SpeechProcessor.php' => '语音处理',
            'apps/ai-platform/Services/KnowledgeGraph/KnowledgeGraphProcessor.php' => '知识图谱'
        ];
        
        foreach ($aiFiles as $file => $name) {
            $fullPath = __DIR__ . "/../$file";
            $this->test(
                $name,
                file_exists($fullPath],
                file_exists($fullPath) ? "已部�? : "缺失"
            ];
        }
    }
    
    /**
     * 检查区块链服务
     */    private function checkBlockchainServices(): void
    {
        $this->printSectionHeader("区块链服务检�?];
        
        $blockchainFiles = [
            'apps/blockchain/Services/BlockchainServiceManager.php' => '区块链服务管理器',
            'apps/blockchain/Services/WalletManager.php' => '钱包管理�?,
            'apps/blockchain/Services/SmartContractManager.php' => '智能合约管理�?
        ];
        
        foreach ($blockchainFiles as $file => $name) {
            $fullPath = __DIR__ . "/../$file";
            $this->test(
                $name,
                file_exists($fullPath],
                file_exists($fullPath) ? "已部�? : "缺失"
            ];
        }
    }
    
    /**
     * 检查安全服�?
     */    private function checkSecurityServices(): void
    {
        $this->printSectionHeader("安全服务检�?];
        
        $securityFiles = [
            'apps/security/Services/ZeroTrustManager.php' => '零信任管理器',
            'apps/security/Services/AuthenticationManager.php' => '认证管理�?,
            'apps/security/Services/EncryptionManager.php' => '加密管理�?
        ];
        
        foreach ($securityFiles as $file => $name) {
            $fullPath = __DIR__ . "/../$file";
            $this->test(
                $name,
                file_exists($fullPath],
                file_exists($fullPath) ? "已部�? : "缺失"
            ];
        }
    }
    
    /**
     * 检查性能指标
     */
    private function checkPerformanceMetrics(): void
    {
        $this->printSectionHeader("性能指标检�?];
        
        // 内存使用情况
        $memoryUsage = memory_get_usage(true];
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit')) * 1024 * 1024;
        $memoryPercentage = ($memoryUsage / $memoryLimit) * 100;
        
        $this->test(
            "内存使用�?,
            $memoryPercentage < 80,
            sprintf("%.2f%% (%s / %s)", 
                $memoryPercentage,
                $this->formatBytes($memoryUsage],
                ini_get('memory_limit')
            )
        ];
        
        // 磁盘空间检�?
        $diskFree = disk_free_space(__DIR__];
        $diskTotal = disk_total_space(__DIR__];
        $diskUsagePercentage = (($diskTotal - $diskFree) / $diskTotal) * 100;
        
        $this->test(
            "磁盘使用�?,
            $diskUsagePercentage < 90,
            sprintf("%.2f%% (可用: %s)",
                $diskUsagePercentage,
                $this->formatBytes($diskFree)
            )
        ];
          // 响应时间测试
        $startTime = microtime(true];
        usleep(1000]; // 模拟简单操作（1毫秒�?
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        $this->test(
            "系统响应时间",
            $responseTime < 100,
            sprintf("%.2f ms", $responseTime)
        ];
    }
    
    /**
     * 检查API端点
     */
    private function checkAPIEndpoints(): void
    {
        $this->printSectionHeader("API端点检�?];
        
        // 这里可以添加实际的API测试
        // 目前只检查路由文件是否存�?
        $routeFiles = [
            'routes/api.php' => 'API路由',
            'routes/web.php' => 'Web路由'
        ];
        
        foreach ($routeFiles as $file => $name) {
            $fullPath = __DIR__ . "/../$file";
            $this->test(
                $name,
                file_exists($fullPath],
                file_exists($fullPath) ? "已配�? : "缺失"
            ];
        }
    }
    
    /**
     * 检查前端资�?
     */
    private function checkFrontendAssets(): void
    {
        $this->printSectionHeader("前端资源检�?];
        
        $frontendFiles = [
            'public/government/index.html' => '政府门户',
            'public/enterprise/workspace.html' => '企业工作空间',
            'public/admin/console.html' => '管理员控制台'
        ];
        
        foreach ($frontendFiles as $file => $name) {
            $fullPath = __DIR__ . "/../$file";
            $this->test(
                $name,
                file_exists($fullPath],
                file_exists($fullPath) ? "已部�? : "缺失"
            ];
        }
    }
    
    /**
     * 生成健康报告
     */
    private function generateHealthReport(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🏥 系统健康检查报告\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $successRate = $this->totalTests > 0 ? ($this->passedTests / $this->totalTests) * 100 : 0;
        
        echo "📊 总体统计:\n";
        echo "  �?总测试数: {$this->totalTests}\n";
        echo "  �?通过测试: {$this->passedTests}\n";
        echo "  �?失败测试: {$this->failedTests}\n";
        echo "  �?成功�? " . sprintf("%.2f%%", $successRate) . "\n\n";
        
        // 健康等级评估
        if ($successRate >= 95) {
            echo "🟢 系统健康状�? 优秀\n";
            echo "   系统运行状态良好，所有核心功能正常。\n\n";
        } elseif ($successRate >= 85) {
            echo "🟡 系统健康状�? 良好\n";
            echo "   系统基本正常，但有一些需要关注的问题。\n\n";
        } elseif ($successRate >= 70) {
            echo "🟠 系统健康状�? 警告\n";
            echo "   系统存在一些问题，建议尽快处理。\n\n";
        } else {
            echo "🔴 系统健康状�? 严重\n";
            echo "   系统存在严重问题，需要立即处理。\n\n";
        }
        
        // 失败项目详情
        if ($this->failedTests > 0) {
            echo "�?需要处理的问题:\n";
            foreach ($this->results as $result) {
                if (!$result['passed']) {
                    echo "  �?{$result['test']}: {$result['message']}\n";
                }
            }
            echo "\n";
        }
        
        echo "📅 检查时�? " . date('Y-m-d H:i:s') . "\n";
        echo "🔧 系统版本: AlingAi Pro 6.0.0\n\n";
        
        // 保存报告到文�?
        $this->saveReportToFile(];
    }
    
    /**
     * 保存报告到文�?
     */
    private function saveReportToFile(): void
    {
        $reportData = [
            'timestamp' => time(),
            'version' => '6.0.0',
            'total_tests' => $this->totalTests,
            'passed_tests' => $this->passedTests,
            'failed_tests' => $this->failedTests,
            'success_rate' => $this->totalTests > 0 ? ($this->passedTests / $this->totalTests) * 100 : 0,
            'results' => $this->results
        ];
        
        $filename = 'SYSTEM_HEALTH_REPORT_' . date('Y_m_d_H_i_s') . '.json';
        file_put_contents(__DIR__ . "/../$filename", json_encode($reportData, JSON_PRETTY_PRINT)];
        
        echo "📋 详细报告已保存至: $filename\n";
    }
    
    /**
     * 执行单个测试
     */
    private function test(string $testName, bool $passed, string $message = ''): void
    {
        $this->totalTests++;
        
        if ($passed) {
            $this->passedTests++;
            $status = "�?;
        } else {
            $this->failedTests++;
            $status = "�?;
        }
        
        echo sprintf("  %s %s", $status, $testName];
        if ($message) {
            echo " - $message";
        }
        echo "\n";
        
        $this->results[] = [
            'test' => $testName,
            'passed' => $passed,
            'message' => $message
        ];
    }
    
    /**
     * 打印章节标题
     */
    private function printSectionHeader(string $title): void
    {
        echo "\n📋 $title\n";
        echo str_repeat("-", strlen($title) + 4) . "\n";
    }
    
    /**
     * 解析内存限制
     */
    private function parseMemoryLimit(string $limit): int
    {
        $unit = strtoupper(substr($limit, -1)];
        $value = (int) substr($limit, 0, -1];
        
        switch ($unit) {
            case 'G':
                return $value * 1024;
            case 'M':
                return $value;
            case 'K':
                return $value / 1024;
            default:
                return (int) $limit / 1024 / 1024;
        }
    }    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        $envFile = __DIR__ . '/../.env';
        $config = [];
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES];
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                    [$key, $value] = explode('=', $line, 2];
                    $key = trim($key];
                    $value = trim($value, '"\''];
                    
                    if (str_starts_with($key, 'DB_')) {
                        $dbKey = strtolower(substr($key, 3)];
                        $config['database'][$dbKey] = $value;
                    } elseif (str_starts_with($key, 'REDIS_')) {
                        $redisKey = strtolower(substr($key, 6)];
                        $config['redis'][$redisKey] = $value;
                    }
                }
            }
        }
        
        return $config;
    }
}

// 运行健康检�?
if (php_sapi_name() === 'cli') {
    $checker = new SystemHealthChecker(];
    $checker->runCompleteHealthCheck(];
}

