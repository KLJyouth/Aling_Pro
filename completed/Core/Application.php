<?php

declare(strict_types=1];

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
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * AlingAi Pro 核心应用程序�?
 * 负责初始化Slim框架应用和所有中间件
 */
/**
 * Application �?
 *
 * @package AlingAi\Core
 */
class Application implements RequestHandlerInterface
{
    private App $app;
    private Container $container;
    private Logger $logger;
    
    /**
     * 创建应用程序实例的静态工厂方�?
     */
    public static function create(): self
    {
        return new self(];
    }
    
    /**

    
     * __construct 方法

    
     *

    
     * @return void

    
     */

    
    public function __construct()
    {
        // 创建容器
        $this->container = new Container(];
        
        // 初始化日志系�?
        $this->initializeLogger(];
        
        // 注册服务
        $this->registerServices(];
        
        // 创建应用实例
        AppFactory::setContainer($this->container];
        $this->app = AppFactory::create(];
        
        // 配置中间�?
        $this->configureMiddleware(];
        
        // 注册路由
        $this->registerRoutes(];
    }
    
    /**
     * 获取Slim App实例
     */
    /**

     * getApp 方法

     *

     * @return void

     */

    public function getApp(): App
    {
        return $this->app;
    }
    
    /**
     * 运行应用程序
     */
    /**

     * run 方法

     *

     * @return void

     */

    public function run(): void
    {
        try {
            $this->app->run(];
        } catch (\Throwable $e) {
            $this->logger->error('Application runtime error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]];
            
            // 在生产环境显示友好错误页�?
            if (getenv('APP_ENV') !== 'development') {
                http_response_code(500];
                echo json_encode([
                    'error' => 'Internal Server Error',
                    'message' => 'An unexpected error occurred. Please try again later.'
                ]];
            } else {
                throw $e;
            }
        }
    }
    
    /**
     * 初始化日志系�?
     */
    /**

     * initializeLogger 方法

     *

     * @return void

     */

    private function initializeLogger(): void
    {
        $this->logger = new Logger('alingai'];
        
        // 根据环境配置不同的日志处理器
        if (getenv('APP_ENV') === 'development') {
            $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG)];
        }
        
        // 添加文件日志处理�?
        $logPath = __DIR__ . '/../../storage/logs/app.log';
        $logDir = dirname($logPath];
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true];
        }
        $this->logger->pushHandler(new RotatingFileHandler($logPath, 30, Logger::INFO)];
    }
    
    /**
     * 注册应用服务到容�?
     */
    /**

     * registerServices 方法

     *

     * @return void

     */

    private function registerServices(): void
    {
        // 注册日志服务
        $this->container->set('logger', function () {
            return $this->logger;
        }];
        
        // 注册数据库服务接�?
        $this->container->set(DatabaseServiceInterface::class, function () {
            try {
                return new DatabaseService($this->logger];
            } catch (\Exception $e) {
                $this->logger->warning('Database service initialization failed, using file storage service', [
                    'error' => $e->getMessage()
                ]];
                // 使用文件存储服务作为数据库替代方�?
                return new \AlingAi\Services\FileStorageService($this->logger];
            }
        }];
        
        // 注册数据库服务（向后兼容�?
        $this->container->set(DatabaseService::class, function () {
            return $this->container->get(DatabaseServiceInterface::class];
        }];
          // 注册缓存服务
        $this->container->set(CacheService::class, function () {
            return new CacheService($this->logger];
        }];
        
        // 注册高级缓存管理�?
        $this->container->set(\AlingAi\Cache\ApplicationCacheManager::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class];
            return new \AlingAi\Cache\ApplicationCacheManager($db, [
                'memory_limit' => 100,
                'default_ttl' => 3600,
                'file_cache_dir' => __DIR__ . '/../../storage/cache/advanced',
                'compression' => true,
                'auto_cleanup' => true,
                'cleanup_probability' => 0.01
            ]];
        }];
          // 注册认证服务
        $this->container->set(AuthService::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class];
            $cache = $this->container->get(CacheService::class];
            return new AuthService($db, $cache, $this->logger];
        }];
        
        // 注册测试系统集成服务
        $this->container->set(\AlingAi\Services\TestSystemIntegrationService::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class];
            $cache = $this->container->get(CacheService::class];
            return new \AlingAi\Services\TestSystemIntegrationService($db, $cache, $this->logger];
        }];
        
        // 注册权限管理�?
        $this->container->set(\AlingAi\Security\PermissionManager::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class];
            $cache = $this->container->get(CacheService::class];
            return new \AlingAi\Security\PermissionManager($db, $cache, $this->logger];
        }];
        
        // 注册权限集成中间�?
        $this->container->set(\AlingAi\Middleware\PermissionIntegrationMiddleware::class, function () {
            $permissionManager = $this->container->get(\AlingAi\Security\PermissionManager::class];
            return new \AlingAi\Middleware\PermissionIntegrationMiddleware($permissionManager, $this->logger];
        }];
        
        // 注册系统监控服务
        $this->container->set(\AlingAi\Monitoring\SystemMonitor::class, function () {
            return new \AlingAi\Monitoring\SystemMonitor($this->logger];
        }];
        
        // 注册性能分析�?
        $this->container->set(\AlingAi\Performance\PerformanceAnalyzer::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class];
            return new \AlingAi\Performance\PerformanceAnalyzer($db, $this->logger];
        }];
        
        // 注册监控控制�?
        $this->container->set(\AlingAi\Controllers\MonitoringController::class, function () {
            $db = $this->container->get(DatabaseServiceInterface::class];
            $cache = $this->container->get(CacheService::class];
            return new \AlingAi\Controllers\MonitoringController($db, $cache];
        }];
        
        // 注册性能优化�?
        $this->container->set(\AlingAi\Performance\PerformanceOptimizer::class, function () {
            $cache = $this->container->get(CacheService::class];
            $advancedCache = $this->container->get(\AlingAi\Cache\ApplicationCacheManager::class];
            return new \AlingAi\Performance\PerformanceOptimizer($cache, $advancedCache, $this->logger];
        }];
    }
    
    /**
     * 配置应用中间�?
     */
    /**

     * configureMiddleware 方法

     *

     * @return void

     */

    private function configureMiddleware(): void
    {
        // 添加CORS中间�?
        $this->app->add(new CorsMiddleware()];
        
        // 添加body解析中间�?
        $this->app->addBodyParsingMiddleware(];
        
        // 添加路由中间�?
        $routingMiddleware = $this->app->addRoutingMiddleware(];
          // 添加错误处理中间�?
        $displayErrorDetails = $_ENV['APP_ENV'] === 'development';
        $logErrors = true;
        $logErrorDetails = true;
        
        $errorMiddleware = $this->app->addErrorMiddleware(
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails,
            $this->logger
        ];
        
        // 不强制设�?JSON 内容类型，让控制器决定响应格�?
        // $errorHandler = $errorMiddleware->getDefaultErrorHandler(];
        // $errorHandler->forceContentType('application/json'];
    }
    
    /**
     * 注册应用路由
     */
    /**

     * registerRoutes 方法

     *

     * @return void

     */

    private function registerRoutes(): void
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        if (file_exists($routesFile)) {
            $routeSetup = require $routesFile;
            if (is_callable($routeSetup)) {
                $routeSetup($this->app];
            }
        } else {
            $this->logger->warning('Routes file not found: ' . $routesFile];
        }
    }
    
    /**
     * 获取容器实例
     */
    /**

     * getContainer 方法

     *

     * @return void

     */

    public function getContainer(): Container
    {
        return $this->container;
    }
    
    /**
     * 获取日志实例
     */
    /**

     * getLogger 方法

     *

     * @return void

     */

    public function getLogger(): Logger
    {
        return $this->logger;
    }
    
    /**
     * 处理PSR-7请求 (RequestHandlerInterface)
     */
    /**

     * handle 方法

     *

     * @param ServerRequestInterface $request

     * @return void

     */

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->handle($request];
    }
}

