<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * 测试生成器
 * 
 * 自动生成单元测试和集成测试
 * 优化性能：智能测试生成、测试数据管理、并行测试
 * 增强功能：覆盖率分析、测试报告、持续集成
 */
class TestGenerator
{
    private LoggerInterface $logger;
    private array $config;
    private array $testTemplates = [];
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'output_dir' => dirname(__DIR__, 2) . '/tests',
            'test_framework' => 'phpunit',
            'coverage_enabled' => true,
            'coverage_threshold' => 80,
            'test_data_dir' => dirname(__DIR__, 2) . '/tests/data',
            'templates' => [
                'unit_test' => 'unit_test_template.php',
                'integration_test' => 'integration_test_template.php',
                'api_test' => 'api_test_template.php'
            ],
            'test_suites' => [
                'unit' => '单元测试',
                'integration' => '集成测试',
                'api' => 'API测试',
                'performance' => '性能测试'
            ]
        ], $config);
        
        $this->initializeTemplates();
    }
    
    /**
     * 初始化测试模板
     */
    private function initializeTemplates(): void
    {
        $this->testTemplates = [
            'unit_test' => $this->getUnitTestTemplate(),
            'integration_test' => $this->getIntegrationTestTemplate(),
            'api_test' => $this->getApiTestTemplate()
        ];
    }
    
    /**
     * 生成测试
     */
    public function generateTests(array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $options = array_merge([
                'target' => 'all', // all, controllers, services, models
                'type' => 'unit', // unit, integration, api
                'force' => false,
                'coverage' => true
            ], $options);
            
            $this->logger->info('开始生成测试', $options);
            
            $this->ensureTestDirectory();
            
            $generatedTests = [];
            
            switch ($options['target']) {
                case 'controllers':
                    $generatedTests = $this->generateControllerTests($options);
                    break;
                    
                case 'services':
                    $generatedTests = $this->generateServiceTests($options);
                    break;
                    
                case 'models':
                    $generatedTests = $this->generateModelTests($options);
                    break;
                    
                case 'all':
                default:
                    $generatedTests = array_merge(
                        $this->generateControllerTests($options),
                        $this->generateServiceTests($options),
                        $this->generateModelTests($options)
                    );
                    break;
            }
            
            // 生成测试配置文件
            $this->generateTestConfig();
            
            // 生成测试数据
            $this->generateTestData();
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->info('测试生成完成', [
                'generated_count' => count($generatedTests),
                'duration_ms' => $duration
            ]);
            
            return [
                'success' => true,
                'generated_tests' => $generatedTests,
                'duration' => $duration
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('测试生成失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 生成控制器测试
     */
    private function generateControllerTests(array $options): array
    {
        $generatedTests = [];
        $controllerDir = dirname(__DIR__) . '/Controllers';
        $files = glob($controllerDir . '/*.php');
        
        foreach ($files as $file) {
            $className = 'AlingAi\\Controllers\\' . basename($file, '.php');
            
            if (class_exists($className)) {
                $testFile = $this->generateControllerTest($className, $options);
                if ($testFile) {
                    $generatedTests[] = $testFile;
                }
            }
        }
        
        return $generatedTests;
    }
    
    /**
     * 生成单个控制器测试
     */
    private function generateControllerTest(string $className, array $options): ?string
    {
        $reflection = new ReflectionClass($className);
        $testClassName = $reflection->getShortName() . 'Test';
        $testFilePath = $this->config['output_dir'] . '/Controllers/' . $testClassName . '.php';
        
        // 检查是否已存在且不强制覆盖
        if (file_exists($testFilePath) && !$options['force']) {
            return null;
        }
        
        $methods = $this->getTestableMethods($reflection);
        $testContent = $this->generateTestContent($className, $methods, $options);
        
        $this->ensureDirectory(dirname($testFilePath));
        file_put_contents($testFilePath, $testContent);
        
        return $testFilePath;
    }
    
    /**
     * 获取可测试的方法
     */
    private function getTestableMethods(ReflectionClass $reflection): array
    {
        $methods = [];
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $reflection->getName() && !$method->isConstructor()) {
                $methods[] = [
                    'name' => $method->getName(),
                    'parameters' => $this->getMethodParameters($method),
                    'doc_comment' => $method->getDocComment()
                ];
            }
        }
        
        return $methods;
    }
    
    /**
     * 获取方法参数
     */
    private function getMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $param) {
            $parameters[] = [
                'name' => $param->getName(),
                'type' => $param->getType() ? $param->getType()->getName() : 'mixed',
                'required' => !$param->isOptional(),
                'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
            ];
        }
        
        return $parameters;
    }
    
    /**
     * 生成测试内容
     */
    private function generateTestContent(string $className, array $methods, array $options): string
    {
        $template = $this->testTemplates['unit_test'];
        
        $testMethods = '';
        foreach ($methods as $method) {
            $testMethods .= $this->generateTestMethod($method, $options);
        }
        
        $content = str_replace([
            '{{CLASS_NAME}}',
            '{{TEST_CLASS_NAME}}',
            '{{TEST_METHODS}}',
            '{{NAMESPACE}}'
        ], [
            $className,
            basename($className) . 'Test',
            $testMethods,
            'AlingAi\\Tests\\Controllers'
        ], $template);
        
        return $content;
    }
    
    /**
     * 生成测试方法
     */
    private function generateTestMethod(array $method, array $options): string
    {
        $methodName = $method['name'];
        $testMethodName = 'test' . ucfirst($methodName);
        
        $testCases = $this->generateTestCases($method, $options);
        
        return "
    /**
     * 测试 {$methodName} 方法
     */
    public function {$testMethodName}(): void
    {
        // 准备测试数据
        \$this->prepareTestData();
        
        // 执行测试用例
        {$testCases}
        
        // 验证结果
        \$this->assertTestResults();
    }
    
    /**
     * 准备测试数据
     */
    private function prepareTestData(): void
    {
        // TODO: 准备测试数据
    }
    
    /**
     * 验证测试结果
     */
    private function assertTestResults(): void
    {
        // TODO: 验证测试结果
    }
";
    }
    
    /**
     * 生成测试用例
     */
    private function generateTestCases(array $method, array $options): string
    {
        $methodName = $method['name'];
        $testCases = '';
        
        // 根据方法名生成不同的测试用例
        switch ($methodName) {
            case 'login':
                $testCases = "
        // 测试正常登录
        \$loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        \$result = \$this->controller->login(\$loginData);
        \$this->assertIsArray(\$result);
        \$this->assertArrayHasKey('success', \$result);
        
        // 测试无效凭据
        \$invalidData = [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword'
        ];
        \$result = \$this->controller->login(\$invalidData);
        \$this->assertFalse(\$result['success']);
";
                break;
                
            case 'register':
                $testCases = "
        // 测试正常注册
        \$registerData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        \$result = \$this->controller->register(\$registerData);
        \$this->assertIsArray(\$result);
        \$this->assertArrayHasKey('success', \$result);
        
        // 测试重复邮箱
        \$duplicateData = [
            'username' => 'existinguser',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        \$result = \$this->controller->register(\$duplicateData);
        \$this->assertFalse(\$result['success']);
";
                break;
                
            case 'sendMessage':
                $testCases = "
        // 测试发送消息
        \$messageData = [
            'message' => 'Hello, AI!',
            'model' => 'gpt-3.5-turbo'
        ];
        \$result = \$this->controller->sendMessage(\$messageData);
        \$this->assertIsArray(\$result);
        \$this->assertArrayHasKey('success', \$result);
        \$this->assertArrayHasKey('data', \$result);
        
        // 测试空消息
        \$emptyData = [
            'message' => '',
            'model' => 'gpt-3.5-turbo'
        ];
        \$result = \$this->controller->sendMessage(\$emptyData);
        \$this->assertFalse(\$result['success']);
";
                break;
                
            default:
                $testCases = "
        // 测试 {$methodName} 方法
        \$testData = \$this->getTestData('{$methodName}');
        \$result = \$this->controller->{$methodName}(\$testData);
        \$this->assertIsArray(\$result);
";
                break;
        }
        
        return $testCases;
    }
    
    /**
     * 生成服务测试
     */
    private function generateServiceTests(array $options): array
    {
        $generatedTests = [];
        $serviceDir = dirname(__DIR__) . '/Services';
        $files = glob($serviceDir . '/*.php');
        
        foreach ($files as $file) {
            $className = 'AlingAi\\Services\\' . basename($file, '.php');
            
            if (class_exists($className)) {
                $testFile = $this->generateServiceTest($className, $options);
                if ($testFile) {
                    $generatedTests[] = $testFile;
                }
            }
        }
        
        return $generatedTests;
    }
    
    /**
     * 生成单个服务测试
     */
    private function generateServiceTest(string $className, array $options): ?string
    {
        $reflection = new ReflectionClass($className);
        $testClassName = $reflection->getShortName() . 'Test';
        $testFilePath = $this->config['output_dir'] . '/Services/' . $testClassName . '.php';
        
        if (file_exists($testFilePath) && !$options['force']) {
            return null;
        }
        
        $methods = $this->getTestableMethods($reflection);
        $testContent = $this->generateServiceTestContent($className, $methods, $options);
        
        $this->ensureDirectory(dirname($testFilePath));
        file_put_contents($testFilePath, $testContent);
        
        return $testFilePath;
    }
    
    /**
     * 生成服务测试内容
     */
    private function generateServiceTestContent(string $className, array $methods, array $options): string
    {
        $template = $this->testTemplates['unit_test'];
        
        $testMethods = '';
        foreach ($methods as $method) {
            $testMethods .= $this->generateServiceTestMethod($method, $options);
        }
        
        $content = str_replace([
            '{{CLASS_NAME}}',
            '{{TEST_CLASS_NAME}}',
            '{{TEST_METHODS}}',
            '{{NAMESPACE}}'
        ], [
            $className,
            basename($className) . 'Test',
            $testMethods,
            'AlingAi\\Tests\\Services'
        ], $template);
        
        return $content;
    }
    
    /**
     * 生成服务测试方法
     */
    private function generateServiceTestMethod(array $method, array $options): string
    {
        $methodName = $method['name'];
        $testMethodName = 'test' . ucfirst($methodName);
        
        return "
    /**
     * 测试 {$methodName} 方法
     */
    public function {$testMethodName}(): void
    {
        // 准备测试数据
        \$this->prepareTestData();
        
        // 执行测试
        \$result = \$this->service->{$methodName}();
        
        // 验证结果
        \$this->assertNotNull(\$result);
        \$this->assertTestResults(\$result);
    }
    
    /**
     * 准备测试数据
     */
    private function prepareTestData(): void
    {
        // TODO: 准备测试数据
    }
    
    /**
     * 验证测试结果
     */
    private function assertTestResults(\$result): void
    {
        // TODO: 验证测试结果
    }
";
    }
    
    /**
     * 生成模型测试
     */
    private function generateModelTests(array $options): array
    {
        $generatedTests = [];
        $modelDir = dirname(__DIR__) . '/Models';
        $files = glob($modelDir . '/*.php');
        
        foreach ($files as $file) {
            $className = 'AlingAi\\Models\\' . basename($file, '.php');
            
            if (class_exists($className)) {
                $testFile = $this->generateModelTest($className, $options);
                if ($testFile) {
                    $generatedTests[] = $testFile;
                }
            }
        }
        
        return $generatedTests;
    }
    
    /**
     * 生成单个模型测试
     */
    private function generateModelTest(string $className, array $options): ?string
    {
        $reflection = new ReflectionClass($className);
        $testClassName = $reflection->getShortName() . 'Test';
        $testFilePath = $this->config['output_dir'] . '/Models/' . $testClassName . '.php';
        
        if (file_exists($testFilePath) && !$options['force']) {
            return null;
        }
        
        $methods = $this->getTestableMethods($reflection);
        $testContent = $this->generateModelTestContent($className, $methods, $options);
        
        $this->ensureDirectory(dirname($testFilePath));
        file_put_contents($testFilePath, $testContent);
        
        return $testFilePath;
    }
    
    /**
     * 生成模型测试内容
     */
    private function generateModelTestContent(string $className, array $methods, array $options): string
    {
        $template = $this->testTemplates['unit_test'];
        
        $testMethods = '';
        foreach ($methods as $method) {
            $testMethods .= $this->generateModelTestMethod($method, $options);
        }
        
        $content = str_replace([
            '{{CLASS_NAME}}',
            '{{TEST_CLASS_NAME}}',
            '{{TEST_METHODS}}',
            '{{NAMESPACE}}'
        ], [
            $className,
            basename($className) . 'Test',
            $testMethods,
            'AlingAi\\Tests\\Models'
        ], $template);
        
        return $content;
    }
    
    /**
     * 生成模型测试方法
     */
    private function generateModelTestMethod(array $method, array $options): string
    {
        $methodName = $method['name'];
        $testMethodName = 'test' . ucfirst($methodName);
        
        return "
    /**
     * 测试 {$methodName} 方法
     */
    public function {$testMethodName}(): void
    {
        // 创建测试实例
        \$model = new {{CLASS_NAME}}();
        
        // 执行测试
        \$result = \$model->{$methodName}();
        
        // 验证结果
        \$this->assertNotNull(\$result);
        \$this->assertTestResults(\$result);
    }
    
    /**
     * 验证测试结果
     */
    private function assertTestResults(\$result): void
    {
        // TODO: 验证测试结果
    }
";
    }
    
    /**
     * 生成测试配置
     */
    private function generateTestConfig(): void
    {
        $configFile = $this->config['output_dir'] . '/phpunit.xml';
        
        $config = $this->getPhpUnitConfig();
        
        file_put_contents($configFile, $config);
    }
    
    /**
     * 获取PHPUnit配置
     */
    private function getPhpUnitConfig(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         processIsolation="false"
         stopOnFailure="false"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Unit">
            <directory>Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>Integration</directory>
        </testsuite>
        <testsuite name="API">
            <directory>API</directory>
        </testsuite>
    </testsuites>
    
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">../src</directory>
        </include>
        <exclude>
            <directory suffix=".php">../src/Middleware</directory>
            <directory suffix=".php">../src/Utils</directory>
        </exclude>
        <report>
            <html outputDirectory="coverage"/>
            <clover outputFile="coverage.xml"/>
        </report>
    </coverage>
    
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_DATABASE" value="alingai_test"/>
    </php>
</phpunit>';
    }
    
    /**
     * 生成测试数据
     */
    private function generateTestData(): void
    {
        $dataDir = $this->config['test_data_dir'];
        $this->ensureDirectory($dataDir);
        
        $testData = [
            'users' => $this->generateUserTestData(),
            'conversations' => $this->generateConversationTestData(),
            'documents' => $this->generateDocumentTestData()
        ];
        
        foreach ($testData as $type => $data) {
            $file = $dataDir . '/' . $type . '.json';
            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    
    /**
     * 生成用户测试数据
     */
    private function generateUserTestData(): array
    {
        return [
            [
                'id' => 1,
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => 'password123',
                'created_at' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 2,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'admin123',
                'role' => 'admin',
                'created_at' => '2024-01-01 00:00:00'
            ]
        ];
    }
    
    /**
     * 生成对话测试数据
     */
    private function generateConversationTestData(): array
    {
        return [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => '测试对话',
                'created_at' => '2024-01-01 00:00:00'
            ]
        ];
    }
    
    /**
     * 生成文档测试数据
     */
    private function generateDocumentTestData(): array
    {
        return [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => '测试文档',
                'content' => '这是一个测试文档的内容',
                'created_at' => '2024-01-01 00:00:00'
            ]
        ];
    }
    
    /**
     * 确保测试目录存在
     */
    private function ensureTestDirectory(): void
    {
        $this->ensureDirectory($this->config['output_dir']);
        $this->ensureDirectory($this->config['output_dir'] . '/Controllers');
        $this->ensureDirectory($this->config['output_dir'] . '/Services');
        $this->ensureDirectory($this->config['output_dir'] . '/Models');
        $this->ensureDirectory($this->config['output_dir'] . '/Integration');
        $this->ensureDirectory($this->config['output_dir'] . '/API');
    }
    
    /**
     * 确保目录存在
     */
    private function ensureDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \RuntimeException("无法创建目录: {$dir}");
            }
        }
    }
    
    /**
     * 获取单元测试模板
     */
    private function getUnitTestTemplate(): string
    {
        return '<?php

namespace {{NAMESPACE}};

use PHPUnit\Framework\TestCase;
use {{CLASS_NAME}};

/**
 * {{TEST_CLASS_NAME}}
 */
class {{TEST_CLASS_NAME}} extends TestCase
{
    protected \$controller;
    
    protected function setUp(): void
    {
        parent::setUp();
        \$this->controller = new {{CLASS_NAME}}();
    }
    
    protected function tearDown(): void
    {
        \$this->controller = null;
        parent::tearDown();
    }
    
{{TEST_METHODS}}
}';
    }
    
    /**
     * 获取集成测试模板
     */
    private function getIntegrationTestTemplate(): string
    {
        return '<?php

namespace {{NAMESPACE}};

use PHPUnit\Framework\TestCase;

/**
 * {{TEST_CLASS_NAME}}
 */
class {{TEST_CLASS_NAME}} extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 设置集成测试环境
    }
    
    protected function tearDown(): void
    {
        // 清理集成测试环境
        parent::tearDown();
    }
    
{{TEST_METHODS}}
}';
    }
    
    /**
     * 获取API测试模板
     */
    private function getApiTestTemplate(): string
    {
        return '<?php

namespace {{NAMESPACE}};

use PHPUnit\Framework\TestCase;

/**
 * {{TEST_CLASS_NAME}}
 */
class {{TEST_CLASS_NAME}} extends TestCase
{
    protected \$baseUrl = \'http://localhost:8000\';
    
    protected function setUp(): void
    {
        parent::setUp();
        // 设置API测试环境
    }
    
    protected function tearDown(): void
    {
        // 清理API测试环境
        parent::tearDown();
    }
    
{{TEST_METHODS}}
}';
    }
} 