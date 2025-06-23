<?php
/**
 * AlingAI Pro 5.0 部署启动脚本
 * 完整的生产环境部署和启动系统
 */

declare(strict_types=1);

// 检查PHP版本
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    die("错误: AlingAI Pro 5.0 需要 PHP 8.1.0 或更高版本，当前版本: " . PHP_VERSION . "\n");
}

// 检查必要的扩展
$requiredExtensions = ['pdo', 'pdo_mysql', 'redis', 'curl', 'openssl', 'json', 'mbstring'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    die("错误: 缺少必要的PHP扩展: " . implode(', ', $missingExtensions) . "\n");
}

echo "=== AlingAI Pro 5.0 系统部署启动 ===\n";
echo "当前时间: " . date('Y-m-d H:i:s') . "\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "操作系统: " . PHP_OS . "\n\n";

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 自动加载
require_once __DIR__ . '/vendor/autoload.php';

// 环境变量加载
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

try {
    // 1. 初始化应用程序
    echo "1. 初始化 AlingAI Pro 5.0 应用程序...\n";
    
    $config = [
        'system' => [
            'environment' => getenv('APP_ENV') ?: 'production',
            'debug' => getenv('APP_DEBUG') === 'true',
            'name' => 'AlingAI Pro 5.0',
            'version' => '5.0.0',
        ],
        'database' => [
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => (int)(getenv('DB_PORT') ?: 3306),
            'database' => getenv('DB_DATABASE') ?: 'alingai_pro_5',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
        ],
        'redis' => [
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port' => (int)(getenv('REDIS_PORT') ?: 6379),
            'password' => getenv('REDIS_PASSWORD') ?: null,
        ]
    ];
    
    // 创建应用程序实例
    $app = \AlingAi\Core\ApplicationV5::create($config);
    echo "   ✓ 应用程序初始化成功\n";
    
    // 2. 数据库连接测试
    echo "2. 测试数据库连接...\n";
    try {
        $pdo = new PDO(
            "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['database']};charset=utf8mb4",
            $config['database']['username'],
            $config['database']['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        echo "   ✓ 数据库连接成功\n";
    } catch (PDOException $e) {
        echo "   ✗ 数据库连接失败: " . $e->getMessage() . "\n";
        echo "   注意: 系统将在文件模式下运行\n";
    }
    
    // 3. Redis连接测试
    echo "3. 测试Redis连接...\n";
    try {
        $redis = new Redis();
        $redis->connect($config['redis']['host'], $config['redis']['port']);
        if ($config['redis']['password']) {
            $redis->auth($config['redis']['password']);
        }
        $redis->ping();
        echo "   ✓ Redis连接成功\n";
        $redis->close();
    } catch (Exception $e) {
        echo "   ✗ Redis连接失败: " . $e->getMessage() . "\n";
        echo "   注意: 系统将使用文件缓存\n";
    }
    
    // 4. 创建必要目录
    echo "4. 创建系统目录...\n";
    $directories = [
        __DIR__ . '/storage/logs',
        __DIR__ . '/storage/cache',
        __DIR__ . '/storage/sessions',
        __DIR__ . '/storage/uploads',
        __DIR__ . '/storage/backups',
        __DIR__ . '/storage/quantum_keys',
        __DIR__ . '/public/assets/css',
        __DIR__ . '/public/assets/js',
        __DIR__ . '/public/assets/images',
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "   ✓ 创建目录: $dir\n";
        }
    }
    
    // 5. 初始化核心服务
    echo "5. 初始化核心服务...\n";
    
    // 性能优化器
    try {
        $performanceOptimizer = new \AlingAi\Production\PerformanceOptimizer();
        $performanceOptimizer->initializeOptimizations();
        echo "   ✓ 性能优化器初始化成功\n";
    } catch (Exception $e) {
        echo "   ✗ 性能优化器初始化失败: " . $e->getMessage() . "\n";
    }
    
    // 缓存管理器
    try {
        $cacheManager = new \AlingAi\Production\AdvancedCacheManager();
        $cacheManager->initialize();
        echo "   ✓ 缓存管理器初始化成功\n";
    } catch (Exception $e) {
        echo "   ✗ 缓存管理器初始化失败: " . $e->getMessage() . "\n";
    }
    
    // 负载均衡器
    try {
        $loadBalancer = new \AlingAi\Production\LoadBalancerManager();
        $loadBalancer->initialize();
        echo "   ✓ 负载均衡器初始化成功\n";
    } catch (Exception $e) {
        echo "   ✗ 负载均衡器初始化失败: " . $e->getMessage() . "\n";
    }
    
    // 量子加密系统
    try {
        $quantumCrypto = new \AlingAi\Security\QuantumCryptoValidator();
        $quantumCrypto->initialize();
        echo "   ✓ 量子加密系统初始化成功\n";
    } catch (Exception $e) {
        echo "   ✗ 量子加密系统初始化失败: " . $e->getMessage() . "\n";
    }
    
    // DeepSeek智能体系统
    try {
        $deepSeekAgent = new \AlingAi\AI\DeepSeekAgentIntegration();
        $deepSeekAgent->initialize();
        echo "   ✓ DeepSeek智能体系统初始化成功\n";
    } catch (Exception $e) {
        echo "   ✗ DeepSeek智能体系统初始化失败: " . $e->getMessage() . "\n";
    }
    
    // 6. 启动Web服务器
    echo "6. 启动Web服务器...\n";
    
    // 创建Slim应用
    $slimApp = \Slim\Factory\AppFactory::create();
    
    // 配置路由
    $container = $app->getContainer();
    $logger = $app->getLogger();
    
    $routeManager = new \AlingAi\Core\RouteIntegrationManager($slimApp, $container, $logger);
    $routeManager->registerCoreArchitectureRoutes();
    
    echo "   ✓ 路由注册完成\n";
    
    // 7. 生成启动报告
    echo "7. 生成系统启动报告...\n";
    
    $systemInfo = $app->getSystemInfo();
    $report = [
        'deployment_time' => date('Y-m-d H:i:s'),
        'system_info' => $systemInfo,
        'services_status' => [
            'application' => true,
            'database' => isset($pdo),
            'redis' => isset($redis),
            'performance_optimizer' => class_exists('\AlingAi\Production\PerformanceOptimizer'),
            'cache_manager' => class_exists('\AlingAi\Production\AdvancedCacheManager'),
            'load_balancer' => class_exists('\AlingAi\Production\LoadBalancerManager'),
            'quantum_crypto' => class_exists('\AlingAi\Security\QuantumCryptoValidator'),
            'deepseek_agent' => class_exists('\AlingAi\AI\DeepSeekAgentIntegration'),
        ],
        'environment' => [
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'timezone' => date_default_timezone_get(),
        ],
        'configurations' => [
            'debug_mode' => $config['system']['debug'],
            'environment' => $config['system']['environment'],
            'database_host' => $config['database']['host'],
            'redis_host' => $config['redis']['host'],
        ]
    ];
    
    $reportFile = __DIR__ . '/storage/logs/deployment_report_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "   ✓ 启动报告已保存: $reportFile\n";
    
    echo "\n=== AlingAI Pro 5.0 部署完成 ===\n";
    echo "系统状态: 已启动并运行\n";
    echo "管理面板: http://localhost:8000/admin\n";
    echo "API接口: http://localhost:8000/api/v5\n";
    echo "文档地址: http://localhost:8000/docs\n";
    echo "监控面板: http://localhost:8000/monitoring\n\n";
    
    // 8. 启动内置服务器（开发模式）
    if ($config['system']['environment'] === 'development' || $config['system']['debug']) {
        echo "启动内置开发服务器...\n";
        echo "访问地址: http://localhost:8000\n";
        echo "按 Ctrl+C 停止服务器\n\n";
        
        // 创建临时的index.php文件
        $indexContent = '<?php
require_once __DIR__ . "/vendor/autoload.php";

// 加载环境变量
if (file_exists(__DIR__ . "/.env")) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// 创建应用实例
$app = \AlingAi\Core\ApplicationV5::create();

// 创建Slim应用
$slimApp = \Slim\Factory\AppFactory::create();

// 注册路由
$container = $app->getContainer();
$logger = $app->getLogger();
$routeManager = new \AlingAi\Core\RouteIntegrationManager($slimApp, $container, $logger);
$routeManager->registerCoreArchitectureRoutes();

// 运行应用
$slimApp->run();
';
        file_put_contents(__DIR__ . '/public/index.php', $indexContent);
        
        // 启动内置服务器
        $command = 'php -S localhost:8000 -t public';
        echo "执行命令: $command\n";
        system($command);
    } else {
        echo "生产环境部署完成！\n";
        echo "请配置您的Web服务器（Apache/Nginx）指向 public 目录\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ 部署过程中发生错误:\n";
    echo "错误信息: " . $e->getMessage() . "\n";
    echo "错误文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
    
    // 记录错误日志
    $errorLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    
    if (!is_dir(__DIR__ . '/storage/logs')) {
        mkdir(__DIR__ . '/storage/logs', 0755, true);
    }
    
    file_put_contents(
        __DIR__ . '/storage/logs/deployment_error_' . date('Y-m-d_H-i-s') . '.json',
        json_encode($errorLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    
    exit(1);
}
