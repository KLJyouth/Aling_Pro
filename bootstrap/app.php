<?php
/**
 * AlingAi Pro 6.0 - Application Bootstrap
 * 企业级生产系统引导文件
 * 
 * @package AlingAi\Pro
 * @version 6.0.0
 * @author AlingAi Team
 * @copyright 2025 AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

use DI\Container;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use AlingAi\Core\Logger\SimpleLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use AlingAi\Core\Application;
use AlingAi\Core\ServiceContainer;
use AlingAi\Core\Config\ConfigManager;
use AlingAi\Core\Database\DatabaseManager;
use AlingAi\Core\Cache\CacheManager;
use AlingAi\Core\Security\SecurityManager;
use AlingAi\Core\Monitoring\PerformanceMonitor;

// 定义应用根目录
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '6.0.0');
}
if (!defined('APP_NAME')) {
    define('APP_NAME', 'AlingAi Pro');
}

// 启动时间记录
if (!defined('APP_START_TIME')) {
    define('APP_START_TIME', microtime(true));
}

// 错误报告设置
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// 内存限制设置
ini_set('memory_limit', '512M');

// 自动加载器
require_once APP_ROOT . '/vendor/autoload.php';

/**
 * 应用程序引导类
 */
class Bootstrap
{
    private Container $container;
    private SimpleLogger $logger;
    private array $config;

    public function __construct() {
        $this->initializeEnvironment();
        $this->initializeLogger();
        $this->initializeContainer();
        $this->initializeConfig();
        $this->registerServices();
    }

    /**
     * 初始化环境变量
     */
    private function initializeEnvironment(): void
    {
        try {
            $dotenv = Dotenv::createImmutable(APP_ROOT);
            $dotenv->load();
            
            // 验证必需的环境变量
            $dotenv->required([
                'APP_ENV',
                'APP_DEBUG',
                'APP_KEY',
                'DB_CONNECTION'
            ]);
            
        } catch (Exception $e) {
            die("Environment configuration error: " . $e->getMessage());
        }
    }

    /**
     * 初始化日志系统
     */
    private function initializeLogger(): void
    {
        $this->logger = new SimpleLogger('alingai');

        // 设置日志文件路径
        $logPath = APP_ROOT . '/storage/logs';
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $this->logger->setLogFile($logPath . '/app-' . date('Y-m-d') . '.log');
    }

    /**
     * 获取日志级别
     */
    private function getLogLevel(): int
    {
        $level = strtoupper(getenv('LOG_LEVEL') ?: 'INFO');

        return match ($level) {
            'DEBUG' => 100,
            'INFO' => 200,
            'WARNING' => 300,
            'ERROR' => 400,
            'CRITICAL' => 500,
            default => 200
        };
    }

    /**
     * 初始化依赖注入容器
     */
    private function initializeContainer(): void
    {
        $builder = new ContainerBuilder();
        
        // 在生产环境启用编译
        if (getenv('APP_ENV') === 'production') {
            $builder->enableCompilation(APP_ROOT . '/cache/container');
        }
        
        // 加载容器配置
        $builder->addDefinitions([
            SimpleLogger::class => $this->logger,
            \Monolog\Logger::class => $this->logger, // 为了兼容性，也注册为Monolog\Logger
            'app.version' => APP_VERSION,
            'app.name' => APP_NAME,
            'app.root' => APP_ROOT,
            'app.start_time' => APP_START_TIME
        ]);
        
        $this->container = $builder->build();
    }

    /**
     * 初始化配置管理器
     */
    private function initializeConfig(): void
    {
        $this->config = [
            'app' => [
                'name' => getenv('APP_NAME') ?: APP_NAME,
                'version' => APP_VERSION,
                'env' => getenv('APP_ENV') ?: 'production',
                'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
                'url' => getenv('APP_URL') ?: 'http://localhost',
                'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Shanghai',
                'locale' => getenv('APP_LOCALE') ?: 'zh_CN',
                'key' => getenv('APP_KEY')
            ],
            'database' => [
                'connection' => getenv('DB_CONNECTION') ?: 'mysql',
                'host' => getenv('DB_HOST') ?: '127.0.0.1',
                'port' => (int)(getenv('DB_PORT') ?: 3306),
                'database' => getenv('DB_DATABASE') ?: 'alingai_pro',
                'username' => getenv('DB_USERNAME') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => getenv('DB_PREFIX') ?: ''
            ],
            'cache' => [
                'driver' => getenv('CACHE_DRIVER') ?: 'file',
                'redis' => [
                    'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
                    'port' => (int)(getenv('REDIS_PORT') ?: 6379),
                    'password' => getenv('REDIS_PASSWORD') ?: null,
                    'database' => (int)(getenv('REDIS_DB') ?: 0)
                ]
            ],
            'security' => [
                'jwt_secret' => getenv('JWT_SECRET'),
                'jwt_ttl' => (int)(getenv('JWT_TTL') ?: 3600),
                'bcrypt_rounds' => (int)(getenv('BCRYPT_ROUNDS') ?: 12)
            ]
        ];
        
        $this->container->set('config', $this->config);
    }

    /**
     * 注册核心服务
     */
    private function registerServices(): void
    {
        // 数据库管理器
        $this->container->set(DatabaseManager::class, function() {
            return new DatabaseManager($this->config['database'], $this->logger);
        });
        
        // 缓存管理器
        $this->container->set(CacheManager::class, function() {
            return new CacheManager($this->config['cache'], $this->logger);
        });
        
        // 安全管理器
        $this->container->set(SecurityManager::class, function() {
            return new SecurityManager($this->config['security'], $this->logger);
        });
        
        // 性能监控器
        $this->container->set(PerformanceMonitor::class, function() {
            return new PerformanceMonitor($this->logger);
        });
    }

    /**
     * 获取容器实例
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * 获取应用实例
     */
    public function createApplication(): Application
    {
        return new Application($this->container);
    }
}

// 创建并返回应用实例
try {
    $bootstrap = new Bootstrap();
    $app = $bootstrap->createApplication();
    
    // 注册全局异常处理器 - 使用闭包来确保正确调用
    set_exception_handler(function($exception) use ($app) {
        $app->handleException($exception);
    });
    
    set_error_handler(function($errno, $errstr, $errfile, $errline) use ($app) {
        return $app->handleError($errno, $errstr, $errfile, $errline);
    });

    return $app;
    
} catch (Exception $e) {
    // 应用引导失败处理
    http_response_code(500);
    
    if (getenv('APP_ENV') === 'development') {
        echo "Application bootstrap failed: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    } else {
        echo "Service temporarily unavailable. Please try again later.";
        error_log("Bootstrap Error: " . $e->getMessage());
    }
    
    exit(1);
}
