<?php

declare(strict_types=1);

namespace AlingAi\Core;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\RoutingMiddleware;
use DI\Container;
use AlingAi\Services\DatabaseService;
use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use AlingAi\Services\AuthService;
use AlingAi\Controllers\MonitoringController;
use AlingAi\Middleware\CorsMiddleware;
use AlingAi\Core\Logger\SimpleLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * AlingAi Pro 核心应用程序类
 * 负责初始化Slim框架应用和所有中间件
 */
class Application implements RequestHandlerInterface
{
    private App $app;
    private Container $container;
    private SimpleLogger $logger;
    
    /**
     * 创建应用程序实例的静态工厂方法
     */
    public static function create(): self
    {
        return new self();
    }
    
    public function __construct()
    {
        // 创建容器
        $this->container = new Container();
        
        // 初始化日志系统
        $this->initializeLogger();
        
        // 注册服务
        $this->registerServices();
        
        // 创建应用实例
        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();
        
        // 配置中间件
        $this->configureMiddleware();
        
        // 注册路由
        $this->registerRoutes();
    }
    
    /**
     * 获取Slim App实例
     */
    public function getApp(): App
    {
        return $this->app;
    }
    
    /**
     * 运行应用程序
     */
    public function run(): void
    {
        try {
            $this->app->run();
        } catch (\Throwable $e) {
            $this->logger->error('Application runtime error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // 在生产环境显示友好错误页面
            if (getenv('APP_ENV') !== 'development') {
                http_response_code(500);
                echo json_encode([
                    'error' => 'Internal Server Error',
                    'message' => 'An unexpected error occurred. Please try again later.'
                ]);
            } else {
                throw $e;
            }
        }
    }
    
    /**
     * 初始化日志系统
     */
    private function initializeLogger(): void
    {
        $this->logger = new SimpleLogger('alingai');
        
        // 添加文件日志处理器
        $logPath = __DIR__ . '/../../storage/logs';
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logger->setLogFile($logPath . '/app-' . date('Y-m-d') . '.log');
    }
    
    /**
     * 注册应用服务到容器
     */
    private function registerServices(): void
    {
        // 注册日志服务
        $this->container->set('logger', function () {
            return $this->logger;
        });
        
        // 注册SimpleLogger服务
        $this->container->set(SimpleLogger::class, function () {
            return $this->logger;
        });
        
        // 为了兼容性，也注册为Monolog\Logger
        $this->container->set(\Monolog\Logger::class, function () {
            return $this->logger;
        });
        
        // 注册数据库服务接口
        $this->container->set(DatabaseServiceInterface::class, function () {
            try {
                return new DatabaseService($this->logger);
            } catch (\Exception $e) {
                $this->logger->warning('Database service initialization failed, using file storage service', [
                    'error' => $e->getMessage()
                ]);
                // 使用文件存储服务作为数据库替代方案
                return new \AlingAi\Services\FileStorageService($this->logger);
            }
        });
        
        // 注册数据库服务（向后兼容）
        $this->container->set(DatabaseService::class, function () {
            return $this->container->get(DatabaseServiceInterface::class);
        });
          // 注册缓存服务
        $this->container->set(CacheService::class, function () {
            return new CacheService($this->logger);
        });
        
        // 注册高级缓存管理器
        $this->container->set(\AlingAi\Cache\ApplicationCacheManager::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class);
            return new \AlingAi\Cache\ApplicationCacheManager($db, [
                'memory_limit' => 100,
                'default_ttl' => 3600,
                'file_cache_dir' => __DIR__ . '/../../storage/cache/advanced',
                'compression' => true,
                'auto_cleanup' => true,
                'cleanup_probability' => 0.01
            ]);
        });
          // 注册认证服务
        $this->container->set(AuthService::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class);
            $cache = $this->container->get(CacheService::class);
            return new AuthService($db, $cache, $this->logger);
        });
        
        // 注册测试系统集成服务
        $this->container->set(\AlingAi\Services\TestSystemIntegrationService::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class);
            $cache = $this->container->get(CacheService::class);
            return new \AlingAi\Services\TestSystemIntegrationService($db, $cache, $this->logger);
        });
        
        // 注册权限管理器
        $this->container->set(\AlingAi\Security\PermissionManager::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class);
            $cache = $this->container->get(CacheService::class);
            return new \AlingAi\Security\PermissionManager($db, $cache, $this->logger);
        });
        
        // 注册权限集成中间件
        $this->container->set(\AlingAi\Middleware\PermissionIntegrationMiddleware::class, function () {
            $permissionManager = $this->container->get(\AlingAi\Security\PermissionManager::class);
            return new \AlingAi\Middleware\PermissionIntegrationMiddleware($permissionManager, $this->logger);
        });
        
        // 注册系统监控服务
        $this->container->set(\AlingAi\Monitoring\SystemMonitor::class, function () {
            return new \AlingAi\Monitoring\SystemMonitor($this->logger);
        });
        
        // 注册性能分析器
        $this->container->set(\AlingAi\Performance\PerformanceAnalyzer::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class);
            return new \AlingAi\Performance\PerformanceAnalyzer($db, $this->logger);
        });
        
        // 注册性能优化器
        $this->container->set(\AlingAi\Performance\PerformanceOptimizer::class, function () {
            $cache = $this->container->get(CacheService::class);
            $advancedCache = $this->container->get(\AlingAi\Cache\ApplicationCacheManager::class);
            return new \AlingAi\Performance\PerformanceOptimizer($cache, $advancedCache, $this->logger);
        });
    }
    
    /**
     * 配置应用中间件
     */
    private function configureMiddleware(): void
    {
        // 添加CORS中间件
        $this->app->add(new CorsMiddleware());
        
        // 添加body解析中间件
        $this->app->addBodyParsingMiddleware();
        
        // 添加路由中间件
        $routingMiddleware = $this->app->addRoutingMiddleware();
          // 添加错误处理中间件
        $displayErrorDetails = $_ENV['APP_ENV'] === 'development';
        $logErrors = true;
        $logErrorDetails = true;
        
        $errorMiddleware = $this->app->addErrorMiddleware(
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails,
            $this->logger
        );
        
        // 不强制设置 JSON 内容类型，让控制器决定响应格式
        // $errorHandler = $errorMiddleware->getDefaultErrorHandler();
        // $errorHandler->forceContentType('application/json');
    }
    
    /**
     * 注册应用路由
     */
    private function registerRoutes(): void
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        if (file_exists($routesFile)) {
            $routeSetup = require $routesFile;
            if (is_callable($routeSetup)) {
                $routeSetup($this->app);
            }
        } else {
            $this->logger->warning('Routes file not found: ' . $routesFile);
        }
    }
    
    /**
     * 获取容器实例
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
    
    /**
     * 获取日志实例
     */
    public function getLogger(): SimpleLogger
    {
        return $this->logger;
    }
    
    /**
     * 处理PSR-7请求 (RequestHandlerInterface)
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->handle($request);
    }
    
    /**
     * 处理未捕获的异常
     * 
     * @param \Throwable $exception 异常对象
     * @return void
     */
    public function handleException(\Throwable $exception): void
    {
        $this->logger->error('Uncaught exception: ' . $exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // 设置HTTP状态码
        http_response_code(500);
        
        // 根据环境显示不同的错误信息
        if (getenv('APP_ENV') === 'development') {
            echo "<h1>Application Error</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
            echo "<h2>Stack Trace:</h2>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            echo "<h1>服务器错误</h1>";
            echo "<p>服务器遇到了一个错误，请稍后再试。</p>";
            echo "<p>如果问题持续存在，请联系系统管理员。</p>";
        }
    }
    
    /**
     * 处理PHP错误
     * 
     * @param int $errno 错误级别
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行号
     * @return bool
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // 如果错误已经被PHP的@ 操作符抑制，则不处理
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        // 将PHP错误转换为异常
        $exception = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        $this->handleException($exception);
        
        // 不再执行PHP标准错误处理
        return true;
    }
}
