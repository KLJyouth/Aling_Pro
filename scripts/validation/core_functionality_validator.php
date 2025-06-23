<?php
/**
 * AlingAi Pro 5.0 - 核心功能综合验证脚本
 * Comprehensive Core Functionality Verification
 */

class CoreFunctionalityValidator {
    private $rootDir;
    private $testResults = [];
    private $serverProcess = null;
    private $serverPort = 8001; // 使用不同端口避免冲突
      public function __construct() {
        $this->rootDir = dirname(dirname(__DIR__)); // 回到项目根目录
        echo "🔍 AlingAi Pro 5.0 - 核心功能综合验证\n";
        echo str_repeat("=", 80) . "\n";
    }
    
    public function runAllTests() {
        try {
            $this->testEnvironment();
            $this->testFileStructure();
            $this->testDependencies();
            $this->startTestServer();
            $this->testWebAccess();
            $this->testAPIEndpoints();
            $this->testStaticResources();
            $this->stopTestServer();
            $this->generateReport();
        } catch (Exception $e) {
            echo "❌ 测试执行失败: " . $e->getMessage() . "\n";
            $this->stopTestServer();
        }
    }
    
    private function testEnvironment() {
        echo "🌍 测试系统环境...\n";
        echo str_repeat("-", 60) . "\n";
        
        // PHP版本检查
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.0.0', '>=')) {
            echo "✅ PHP版本: $phpVersion (兼容)\n";
            $this->testResults['php_version'] = true;
        } else {
            echo "❌ PHP版本: $phpVersion (需要 >= 8.0)\n";
            $this->testResults['php_version'] = false;
        }
        
        // 必需扩展检查
        $requiredExtensions = ['pdo', 'json', 'mbstring', 'curl', 'openssl'];
        $missingExtensions = [];
        
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo "✅ PHP扩展: $ext\n";
            } else {
                echo "❌ 缺失扩展: $ext\n";
                $missingExtensions[] = $ext;
            }
        }
        
        $this->testResults['php_extensions'] = empty($missingExtensions);
        echo "\n";
    }
    
    private function testFileStructure() {
        echo "📁 测试文件结构...\n";
        echo str_repeat("-", 60) . "\n";
        
        $criticalPaths = [
            'public/index.php' => 'Web应用入口',
            'router.php' => '路由脚本',
            'vendor/autoload.php' => '自动加载',
            'src/Core/AlingAiProApplication.php' => '核心应用',
            '.env' => '环境配置',
            'composer.json' => 'Composer配置'
        ];
        
        $missingFiles = [];
        foreach ($criticalPaths as $path => $desc) {
            $fullPath = $this->rootDir . '/' . $path;
            if (file_exists($fullPath)) {
                echo "✅ $desc: $path\n";
            } else {
                echo "❌ 缺失文件: $path ($desc)\n";
                $missingFiles[] = $path;
            }
        }
        
        $this->testResults['file_structure'] = empty($missingFiles);
        echo "\n";
    }
    
    private function testDependencies() {
        echo "📦 测试依赖加载...\n";
        echo str_repeat("-", 60) . "\n";
        
        try {
            require_once $this->rootDir . '/vendor/autoload.php';
            echo "✅ Composer自动加载成功\n";
            
            // 测试核心类加载
            $coreClasses = [
                'AlingAi\\Core\\AlingAiProApplication',
                'Slim\\App',
                'Monolog\\Logger',
                'DI\\Container'
            ];
            
            $failedClasses = [];
            foreach ($coreClasses as $class) {
                if (class_exists($class)) {
                    echo "✅ 核心类: $class\n";
                } else {
                    echo "❌ 类不存在: $class\n";
                    $failedClasses[] = $class;
                }
            }
            
            $this->testResults['dependencies'] = empty($failedClasses);
        } catch (Exception $e) {
            echo "❌ 依赖加载失败: " . $e->getMessage() . "\n";
            $this->testResults['dependencies'] = false;
        }
        
        echo "\n";
    }
    
    private function startTestServer() {
        echo "🚀 启动测试服务器...\n";
        echo str_repeat("-", 60) . "\n";
        
        $command = "php -S localhost:{$this->serverPort} -t public/ router.php";
        
        if (PHP_OS_FAMILY === 'Windows') {
            $this->serverProcess = popen("start /B $command", 'r');
        } else {
            $this->serverProcess = popen("$command > /dev/null 2>&1 &", 'r');
        }
        
        // 等待服务器启动
        sleep(2);
        
        // 测试服务器是否响应
        $testUrl = "http://localhost:{$this->serverPort}/";
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        
        if (@file_get_contents($testUrl, false, $context)) {
            echo "✅ 测试服务器启动成功: http://localhost:{$this->serverPort}\n";
            $this->testResults['server_start'] = true;
        } else {
            echo "❌ 测试服务器启动失败\n";
            $this->testResults['server_start'] = false;
        }
        
        echo "\n";
    }
    
    private function testWebAccess() {
        echo "🌐 测试Web访问...\n";
        echo str_repeat("-", 60) . "\n";
        
        if (!$this->testResults['server_start']) {
            echo "⏸️ 跳过Web访问测试（服务器未启动）\n\n";
            return;
        }
        
        $testUrls = [
            '/' => '主页',
            '/admin/' => '管理界面',
            '/test/' => '测试工具'
        ];
        
        $failedUrls = [];
        foreach ($testUrls as $path => $desc) {
            $url = "http://localhost:{$this->serverPort}$path";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET'
                ]
            ]);
            
            $result = @file_get_contents($url, false, $context);
            if ($result !== false) {
                $size = strlen($result);
                echo "✅ $desc: $path (${size}字节)\n";
            } else {
                echo "❌ 访问失败: $path ($desc)\n";
                $failedUrls[] = $path;
            }
        }
        
        $this->testResults['web_access'] = empty($failedUrls);
        echo "\n";
    }
    
    private function testAPIEndpoints() {
        echo "📡 测试API端点...\n";
        echo str_repeat("-", 60) . "\n";
        
        if (!$this->testResults['server_start']) {
            echo "⏸️ 跳过API测试（服务器未启动）\n\n";
            return;
        }
        
        $apiEndpoints = [
            '/api/' => 'API首页',
            '/api/system/status' => '系统状态'
        ];
        
        $failedAPIs = [];
        foreach ($apiEndpoints as $path => $desc) {
            $url = "http://localhost:{$this->serverPort}$path";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 15, // API可能加载较慢
                    'method' => 'GET',
                    'header' => "Accept: application/json\r\n"
                ]
            ]);
            
            echo "   测试 $desc: $path ... ";
            $start = microtime(true);
            $result = @file_get_contents($url, false, $context);
            $end = microtime(true);
            $duration = round(($end - $start) * 1000, 2);
            
            if ($result !== false) {
                echo "✅ (${duration}ms)\n";
            } else {
                echo "❌ 超时或失败\n";
                $failedAPIs[] = $path;
            }
        }
        
        $this->testResults['api_endpoints'] = empty($failedAPIs);
        echo "\n";
    }
    
    private function testStaticResources() {
        echo "🎨 测试静态资源...\n";
        echo str_repeat("-", 60) . "\n";
        
        if (!$this->testResults['server_start']) {
            echo "⏸️ 跳过静态资源测试（服务器未启动）\n\n";
            return;
        }
        
        $staticResources = [
            '/assets/css/style.css' => 'CSS样式',
            '/assets/js/app.js' => 'JavaScript文件'
        ];
        
        $failedResources = [];
        foreach ($staticResources as $path => $desc) {
            $url = "http://localhost:{$this->serverPort}$path";
            $context = stream_context_create(['http' => ['timeout' => 5]]);
            
            $result = @file_get_contents($url, false, $context);
            if ($result !== false) {
                $size = strlen($result);
                echo "✅ $desc: $path (${size}字节)\n";
            } else {
                echo "❌ 资源不可访问: $path ($desc)\n";
                $failedResources[] = $path;
            }
        }
        
        $this->testResults['static_resources'] = empty($failedResources);
        echo "\n";
    }
    
    private function stopTestServer() {
        if ($this->serverProcess) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /IM php.exe 2>nul", $output, $return);
            } else {
                pclose($this->serverProcess);
            }
            echo "🛑 测试服务器已停止\n\n";
        }
    }
    
    private function generateReport() {
        echo "📋 测试结果汇总\n";
        echo str_repeat("=", 80) . "\n";
        
        $totalTests = count($this->testResults);
        $passedTests = array_sum($this->testResults);
        $failedTests = $totalTests - $passedTests;
        $successRate = round(($passedTests / $totalTests) * 100, 2);
        
        echo "📊 测试统计:\n";
        echo "   • 总测试项目: $totalTests\n";
        echo "   • 通过测试: $passedTests\n";
        echo "   • 失败测试: $failedTests\n";
        echo "   • 成功率: $successRate%\n\n";
        
        echo "📝 详细结果:\n";
        foreach ($this->testResults as $test => $result) {
            $status = $result ? '✅ 通过' : '❌ 失败';
            $testName = $this->getTestName($test);
            echo "   $status $testName\n";
        }
        
        echo "\n🎯 总体评估:\n";
        if ($successRate >= 90) {
            echo "🎉 优秀！系统运行良好，所有核心功能正常。\n";
        } elseif ($successRate >= 70) {
            echo "⚠️ 良好！大部分功能正常，少数问题需要关注。\n";
        } else {
            echo "❌ 需要改进！存在多个问题，建议排查解决。\n";
        }
        
        echo "\n💡 建议:\n";
        if ($this->testResults['server_start'] && $successRate >= 80) {
            echo "   🚀 系统准备就绪，可以正常使用\n";
            echo "   📖 推荐启动命令: php -S localhost:8000 -t public/ router.php\n";
        } else {
            echo "   🔧 请检查失败的测试项目并进行修复\n";
            echo "   📋 参考文档进行问题排查和配置调整\n";
        }
        
        echo "\n" . str_repeat("=", 80) . "\n";
    }
    
    private function getTestName($test) {
        $names = [
            'php_version' => 'PHP版本兼容性',
            'php_extensions' => 'PHP扩展完整性',
            'file_structure' => '文件结构完整性',
            'dependencies' => '依赖包加载',
            'server_start' => '服务器启动',
            'web_access' => 'Web页面访问',
            'api_endpoints' => 'API端点响应',
            'static_resources' => '静态资源访问'
        ];
        
        return $names[$test] ?? $test;
    }
}

// 执行综合验证
$validator = new CoreFunctionalityValidator();
$validator->runAllTests();
?>
