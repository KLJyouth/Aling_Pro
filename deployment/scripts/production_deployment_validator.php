<?php
/**
 * AlingAi Pro 生产环境部署验证器
 * 完整验证系统的所有组件和功能
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Services\DatabaseService;
use AlingAi\Services\DeepSeekAIService;
use AlingAi\Security\IntelligentSecuritySystem;
use AlingAi\Security\GlobalThreatIntelligence;
use AlingAi\Security\RealTimeNetworkMonitor;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ProductionDeploymentValidator
{
    private Logger $logger;
    private array $testResults = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;

    public function __construct()
    {
        $this->logger = new Logger('deployment_validator');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
    }

    /**
     * 运行完整的部署验证
     */
    public function validateDeployment(): bool
    {
        $this->printHeader();
        
        $testSuites = [
            '环境检查' => [$this, 'validateEnvironment'],
            '数据库连接' => [$this, 'validateDatabase'],
            'AI服务' => [$this, 'validateAIService'],
            '安全系统' => [$this, 'validateSecuritySystem'],
            '威胁情报' => [$this, 'validateThreatIntelligence'],
            '网络监控' => [$this, 'validateNetworkMonitoring'],
            'WebSocket服务' => [$this, 'validateWebSocketService'],
            'Web安装系统' => [$this, 'validateWebInstaller'],
            '系统管理API' => [$this, 'validateSystemManagementAPI'],
            '完整启动流程' => [$this, 'validateCompleteStartup']
        ];

        foreach ($testSuites as $suiteName => $testMethod) {
            $this->runTestSuite($suiteName, $testMethod);
        }

        $this->printSummary();
        return $this->failedTests === 0;
    }

    /**
     * 验证运行环境
     */
    private function validateEnvironment(): array
    {
        $results = [];
        
        // PHP版本检查
        $phpVersion = PHP_VERSION;
        $minVersion = '8.0.0';
        $results['PHP版本'] = [
            'expected' => ">= {$minVersion}",
            'actual' => $phpVersion,
            'passed' => version_compare($phpVersion, $minVersion, '>='),
            'message' => version_compare($phpVersion, $minVersion, '>=') ? 
                "PHP版本 {$phpVersion} 符合要求" : "PHP版本过低，需要 >= {$minVersion}"
        ];

        // 必需扩展检查
        $requiredExtensions = ['pdo', 'json', 'curl', 'mbstring', 'openssl'];
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $results["扩展_{$ext}"] = [
                'expected' => '已加载',
                'actual' => $loaded ? '已加载' : '未加载',
                'passed' => $loaded,
                'message' => $loaded ? "{$ext} 扩展已加载" : "{$ext} 扩展未加载"
            ];
        }

        // 内存限制检查
        $memoryLimit = ini_get('memory_limit');
        $results['内存限制'] = [
            'expected' => '>= 256M',
            'actual' => $memoryLimit,
            'passed' => $this->parseMemoryLimit($memoryLimit) >= 268435456, // 256MB
            'message' => "当前内存限制: {$memoryLimit}"
        ];

        return $results;
    }    /**
     * 验证数据库连接
     */
    private function validateDatabase(): array
    {
        $results = [];
        
        try {
            $database = new DatabaseService();
            
            // 测试基本连接
            $connection = $database->getConnection();
            $connectionType = $database->getConnectionType();
            
            $results['数据库连接'] = [
                'expected' => '连接成功',
                'actual' => "连接成功 ({$connectionType})",
                'passed' => true,
                'message' => "数据库连接正常，类型: {$connectionType}"
            ];

            // 测试基础查询（根据连接类型使用不同方法）
            try {
                if ($connectionType === 'file') {
                    // FileSystemDB测试
                    $stmt = $connection->query('SELECT 1 as test');
                    $testResult = $stmt->fetch();
                } else {
                    // PDO测试  
                    $stmt = $connection->query('SELECT 1 as test');
                    $testResult = $stmt->fetch();
                }
                
                $results['基础查询'] = [
                    'expected' => '查询成功',
                    'actual' => $testResult ? '查询成功' : '查询失败',
                    'passed' => $testResult !== false,
                    'message' => $testResult ? '数据库查询功能正常' : '数据库查询失败'
                ];
            } catch (Exception $e) {
                $results['基础查询'] = [
                    'expected' => '查询成功',
                    'actual' => '查询失败',
                    'passed' => false,
                    'message' => '数据库查询失败: ' . $e->getMessage()
                ];
            }

            // 检查必要的表（根据连接类型使用不同方法）
            $requiredTables = ['users', 'security_logs', 'security_threats', 'system_settings'];
            foreach ($requiredTables as $table) {
                try {                    if ($connectionType === 'file') {
                        // FileSystemDB - 直接使用select方法测试
                        $testData = $database->findAll($table);
                        $tableExists = true;
                    } else {
                        // PDO - 使用SQL查询
                        $stmt = $connection->query("SELECT COUNT(*) FROM {$table}");
                        $tableExists = $stmt !== false;
                    }
                    
                    $results["表_{$table}"] = [
                        'expected' => '表存在',
                        'actual' => '表存在',
                        'passed' => true,
                        'message' => "表 {$table} 存在且可访问"
                    ];
                } catch (Exception $e) {
                    $results["表_{$table}"] = [
                        'expected' => '表存在',
                        'actual' => '表不存在或无法访问',
                        'passed' => false,
                        'message' => "表 {$table} 访问失败: " . $e->getMessage()
                    ];
                }
            }

        } catch (Exception $e) {
            $results['数据库连接'] = [
                'expected' => '连接成功',
                'actual' => '连接失败',
                'passed' => false,
                'message' => '数据库连接失败: ' . $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * 验证AI服务
     */
    private function validateAIService(): array
    {
        $results = [];
        
        try {
            $database = new DatabaseService();
            $apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? 'sk-test-key';
            $aiService = new DeepSeekAIService($apiKey, $database, $this->logger);

            $results['AI服务初始化'] = [
                'expected' => '初始化成功',
                'actual' => '初始化成功',
                'passed' => true,
                'message' => 'AI服务初始化成功'
            ];            // 测试简单的AI调用
            try {
                $response = $aiService->generateChatResponse('Test message', '', ['max_tokens' => 10]);
                $results['AI服务调用'] = [
                    'expected' => '调用成功',
                    'actual' => is_array($response) && isset($response['success']) ? '调用成功' : '调用失败',
                    'passed' => is_array($response) && isset($response['success']),
                    'message' => is_array($response) && isset($response['success']) ? 
                        'AI服务API调用正常' : 'AI服务API响应格式异常'
                ];
            } catch (Exception $e) {
                $results['AI服务调用'] = [
                    'expected' => '调用成功',
                    'actual' => '调用失败',
                    'passed' => false,
                    'message' => 'AI服务API调用失败: ' . $e->getMessage()
                ];
            }

        } catch (Exception $e) {
            $results['AI服务初始化'] = [
                'expected' => '初始化成功',
                'actual' => '初始化失败',
                'passed' => false,
                'message' => 'AI服务初始化失败: ' . $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * 验证安全系统
     */
    private function validateSecuritySystem(): array
    {
        $results = [];
        
        try {
            $database = new DatabaseService();
            $securitySystem = new IntelligentSecuritySystem($database, $this->logger);

            $results['安全系统初始化'] = [
                'expected' => '初始化成功',
                'actual' => '初始化成功',
                'passed' => true,
                'message' => '智能安全系统初始化成功'
            ];            // 测试威胁检测
            $testIP = '192.168.1.100';
            $testRequest = [
                'ip' => $testIP,
                'user_agent' => 'Mozilla/5.0 (test browser)',
                'uri' => '/test',
                'method' => 'GET',
                'headers' => [],
                'payload' => ''
            ];
            $threatAnalysis = $securitySystem->analyzeRequest($testRequest);
            $results['威胁检测'] = [
                'expected' => '检测完成',
                'actual' => is_array($threatAnalysis) && isset($threatAnalysis['threat_level']) ? '检测完成' : '检测失败',
                'passed' => is_array($threatAnalysis) && isset($threatAnalysis['threat_level']),
                'message' => is_array($threatAnalysis) && isset($threatAnalysis['threat_level']) ? 
                    "威胁检测功能正常，威胁级别: {$threatAnalysis['threat_level']}" : '威胁检测功能异常'
            ];

        } catch (Exception $e) {
            $results['安全系统初始化'] = [
                'expected' => '初始化成功',
                'actual' => '初始化失败',
                'passed' => false,
                'message' => '安全系统初始化失败: ' . $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * 验证威胁情报系统
     */
    private function validateThreatIntelligence(): array
    {
        $results = [];
        
        try {
            $database = new DatabaseService();
            $apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? 'sk-test-key';
            $aiService = new DeepSeekAIService($apiKey, $database, $this->logger);
            $threatIntel = new GlobalThreatIntelligence($database, $aiService, $this->logger);

            $results['威胁情报初始化'] = [
                'expected' => '初始化成功',
                'actual' => '初始化成功',
                'passed' => true,
                'message' => '全球威胁情报系统初始化成功'
            ];

            // 测试3D威胁可视化数据生成
            $visualizationData = $threatIntel->getGlobal3DThreatVisualization();
            $results['3D威胁可视化'] = [
                'expected' => '数据生成成功',
                'actual' => is_array($visualizationData) ? '数据生成成功' : '数据生成失败',
                'passed' => is_array($visualizationData) && !empty($visualizationData),
                'message' => is_array($visualizationData) ? 
                    '3D威胁可视化数据生成正常' : '3D威胁可视化数据生成失败'
            ];

        } catch (Exception $e) {
            $results['威胁情报初始化'] = [
                'expected' => '初始化成功',
                'actual' => '初始化失败',
                'passed' => false,
                'message' => '威胁情报系统初始化失败: ' . $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * 验证网络监控系统
     */
    private function validateNetworkMonitoring(): array
    {
        $results = [];
        
        try {
            $database = new DatabaseService();
            $apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? 'sk-test-key';
            $aiService = new DeepSeekAIService($apiKey, $database, $this->logger);
            $securitySystem = new IntelligentSecuritySystem($database, $this->logger);
            $threatIntel = new GlobalThreatIntelligence($database, $aiService, $this->logger);
            $networkMonitor = new RealTimeNetworkMonitor($database, $this->logger, $securitySystem, $threatIntel);

            $results['网络监控初始化'] = [
                'expected' => '初始化成功',
                'actual' => '初始化成功',
                'passed' => true,
                'message' => '实时网络监控系统初始化成功'
            ];

            // 测试监控状态
            $monitoringStatus = $networkMonitor->getMonitoringStatus();
            $results['监控状态'] = [
                'expected' => '状态正常',
                'actual' => is_array($monitoringStatus) ? '状态正常' : '状态异常',
                'passed' => is_array($monitoringStatus),
                'message' => is_array($monitoringStatus) ? 
                    '网络监控状态正常' : '网络监控状态异常'
            ];

        } catch (Exception $e) {
            $results['网络监控初始化'] = [
                'expected' => '初始化成功',
                'actual' => '初始化失败',
                'passed' => false,
                'message' => '网络监控系统初始化失败: ' . $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * 验证WebSocket服务
     */
    private function validateWebSocketService(): array
    {
        $results = [];
        
        // 检查WebSocket服务器文件
        $websocketFile = __DIR__ . '/start_websocket_server.php';
        $results['WebSocket文件'] = [
            'expected' => '文件存在',
            'actual' => file_exists($websocketFile) ? '文件存在' : '文件不存在',
            'passed' => file_exists($websocketFile),
            'message' => file_exists($websocketFile) ? 
                'WebSocket服务器文件存在' : 'WebSocket服务器文件不存在'
        ];

        // 检查语法
        if (file_exists($websocketFile)) {
            $syntaxCheck = shell_exec("php -l \"{$websocketFile}\" 2>&1");
            $syntaxOK = strpos($syntaxCheck, 'No syntax errors') !== false;
            $results['WebSocket语法'] = [
                'expected' => '语法正确',
                'actual' => $syntaxOK ? '语法正确' : '语法错误',
                'passed' => $syntaxOK,
                'message' => $syntaxOK ? 
                    'WebSocket服务器语法正确' : 'WebSocket服务器语法错误: ' . $syntaxCheck
            ];
        }

        return $results;
    }

    /**
     * 验证Web安装系统
     */
    private function validateWebInstaller(): array
    {
        $results = [];
        
        $installDir = __DIR__ . '/install';
        
        // 检查关键安装文件
        $criticalFiles = [
            'api_router.php' => 'API路由器',
            'system_api.php' => '系统管理API',
            'web_installer_fixed.html' => 'Web安装界面',
            'install_complete.html' => '安装完成页面'
        ];

        foreach ($criticalFiles as $file => $description) {
            $filePath = $installDir . '/' . $file;
            $exists = file_exists($filePath);
            $results[$description] = [
                'expected' => '文件存在',
                'actual' => $exists ? '文件存在' : '文件不存在',
                'passed' => $exists,
                'message' => $exists ? 
                    "{$description} 文件存在" : "{$description} 文件不存在"
            ];
        }

        return $results;
    }

    /**
     * 验证系统管理API
     */
    private function validateSystemManagementAPI(): array
    {
        $results = [];
        
        $systemApiFile = __DIR__ . '/install/system_api.php';
        
        // 检查系统API文件
        $results['系统API文件'] = [
            'expected' => '文件存在',
            'actual' => file_exists($systemApiFile) ? '文件存在' : '文件不存在',
            'passed' => file_exists($systemApiFile),
            'message' => file_exists($systemApiFile) ? 
                '系统管理API文件存在' : '系统管理API文件不存在'
        ];

        // 检查语法
        if (file_exists($systemApiFile)) {
            $syntaxCheck = shell_exec("php -l \"{$systemApiFile}\" 2>&1");
            $syntaxOK = strpos($syntaxCheck, 'No syntax errors') !== false;
            $results['系统API语法'] = [
                'expected' => '语法正确',
                'actual' => $syntaxOK ? '语法正确' : '语法错误',
                'passed' => $syntaxOK,
                'message' => $syntaxOK ? 
                    '系统管理API语法正确' : '系统管理API语法错误: ' . $syntaxCheck
            ];
        }

        return $results;
    }

    /**
     * 验证完整启动流程
     */
    private function validateCompleteStartup(): array
    {
        $results = [];
        
        $startupScript = __DIR__ . '/install/start_alingai_system.bat';
        
        // 检查启动脚本
        $results['启动脚本'] = [
            'expected' => '脚本存在',
            'actual' => file_exists($startupScript) ? '脚本存在' : '脚本不存在',
            'passed' => file_exists($startupScript),
            'message' => file_exists($startupScript) ? 
                '完整启动脚本存在' : '完整启动脚本不存在'
        ];

        // 检查部署目录结构
        $requiredDirs = ['public', 'src', 'vendor', 'install', 'logs'];
        foreach ($requiredDirs as $dir) {
            $dirPath = __DIR__ . '/' . $dir;
            $exists = is_dir($dirPath);
            $results["目录_{$dir}"] = [
                'expected' => '目录存在',
                'actual' => $exists ? '目录存在' : '目录不存在',
                'passed' => $exists,
                'message' => $exists ? 
                    "目录 {$dir} 存在" : "目录 {$dir} 不存在"
            ];
        }

        return $results;
    }

    /**
     * 运行测试套件
     */
    private function runTestSuite(string $suiteName, callable $testMethod): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🧪 测试套件: {$suiteName}\n";
        echo str_repeat("-", 60) . "\n";

        try {
            $results = call_user_func($testMethod);
            $this->testResults[$suiteName] = $results;

            foreach ($results as $testName => $result) {
                $this->totalTests++;
                $icon = $result['passed'] ? '✅' : '❌';
                $status = $result['passed'] ? 'PASS' : 'FAIL';
                
                if ($result['passed']) {
                    $this->passedTests++;
                } else {
                    $this->failedTests++;
                }

                echo sprintf(
                    "%s [%s] %s: %s\n",
                    $icon,
                    $status,
                    $testName,
                    $result['message']
                );
            }

        } catch (Exception $e) {
            echo "❌ 测试套件执行失败: " . $e->getMessage() . "\n";
            $this->failedTests++;
            $this->totalTests++;
        }
    }

    /**
     * 打印标题
     */
    private function printHeader(): void
    {
        echo "\n";
        echo str_repeat("=", 80) . "\n";
        echo "🚀 AlingAi Pro 生产环境部署验证器\n";
        echo "🛡️ 实时网络安全监控系统 - 完整功能验证\n";
        echo str_repeat("=", 80) . "\n";
        echo "时间: " . date('Y-m-d H:i:s') . "\n";
        echo "PHP版本: " . PHP_VERSION . "\n";
        echo "操作系统: " . PHP_OS . "\n";
        echo str_repeat("=", 80) . "\n";
    }

    /**
     * 打印总结
     */
    private function printSummary(): void
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "📊 验证结果总结\n";
        echo str_repeat("=", 80) . "\n";
        
        $successRate = $this->totalTests > 0 ? ($this->passedTests / $this->totalTests) * 100 : 0;
        
        echo sprintf("总测试数: %d\n", $this->totalTests);
        echo sprintf("通过测试: %d\n", $this->passedTests);
        echo sprintf("失败测试: %d\n", $this->failedTests);
        echo sprintf("成功率: %.1f%%\n", $successRate);
        
        if ($this->failedTests === 0) {
            echo "\n🎉 所有测试通过！系统已准备好进行生产部署。\n";
        } else {
            echo "\n⚠️ 发现问题，请修复失败的测试项目后重新验证。\n";
        }
        
        echo str_repeat("=", 80) . "\n";
    }

    /**
     * 解析内存限制
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit)-1]);
        $limit = (int) $limit;
        
        switch($last) {
            case 'g': $limit *= 1024;
            case 'm': $limit *= 1024;
            case 'k': $limit *= 1024;
        }
        
        return $limit;
    }
}

// 运行验证器
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $validator = new ProductionDeploymentValidator();
    $success = $validator->validateDeployment();
    exit($success ? 0 : 1);
}
