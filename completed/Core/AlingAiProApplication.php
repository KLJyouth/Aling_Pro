<?php
/**
 * AlingAi Pro - Simplified Application Bootstrap
 * 三完编译 (Three Complete Compilation) Integration
 * 
 * PHP 8.0+ Enterprise Architecture
 * Nginx 1.20+ Compatible
 * MySQL 8.0+ Optimized
 * Production Linux Ready
 * 
 * @package AlingAi\Pro
 * @version 3.0.0
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\Core;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

// Import existing services
use AlingAi\Services\DatabaseService;
use AlingAi\Services\CacheService;
use AlingAi\Services\AuthService;
use AlingAi\Services\SecurityService;
use AlingAi\AI\EnhancedAgentCoordinator;
use AlingAi\Core\CompleteRouterIntegration;

/**
 * Simplified AlingAi Pro Application
 * Works with existing AlingAi namespace structure
 */
/**
 * AlingAiProApplication 类
 *
 * @package AlingAi\Core
 */
class AlingAiProApplication implements RequestHandlerInterface
{    private App $app;
    private Container $container;
    private Logger $logger;
    private ?DatabaseService $database = null;
    private ?CacheService $cache = null;
    private ?AuthService $auth = null;
    private ?SecurityService $security = null;
    private ?EnhancedAgentCoordinator $agentCoordinator = null;
    private ?CompleteRouterIntegration $router = null;
    
    /**
     * Create application instance
     */
    public static function create(): self
    {
        return new self();
    }
    
    /**

    
     * __construct 方法

    
     *

    
     * @return void

    
     */

    
    public function __construct()
    {
        // Initialize core components
        $this->initializeContainer();
        $this->initializeLogger();
        $this->registerServices();
        $this->initializeApplication();
        $this->configureMiddleware();
        $this->setupRouting();
        $this->performSystemInitialization();
    }
    
    /**
     * Initialize DI container
     */
    /**

     * initializeContainer 方法

     *

     * @return void

     */

    private function initializeContainer(): void
    {
        $this->container = new Container();
        
        // Register core services
        $this->container->set('logger', function() {
            return $this->logger;
        });
    }
    
    /**
     * Initialize logging system
     */
    /**

     * initializeLogger 方法

     *

     * @return void

     */

    private function initializeLogger(): void
    {
        $this->logger = new Logger('AlingAiPro');
        
        // Development handler
        if (($_ENV['APP_ENV'] ?? getenv('APP_ENV')) === 'development') {
            $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        }
        
        // Production handlers
        $logPath = APP_ROOT . '/storage/logs';
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $this->logger->pushHandler(new RotatingFileHandler(
            $logPath . '/application.log',
            0,
            Logger::INFO
        ));
        
        $this->logger->pushHandler(new RotatingFileHandler(
            $logPath . '/error.log',
            0,
            Logger::ERROR
        ));
    }    /**
     * Register application services
     */
    /**

     * registerServices 方法

     *

     * @return void

     */

    private function registerServices(): void
    {
        // Database Service
        $this->container->set(DatabaseService::class, function() {
            $this->database = new DatabaseService($this->logger);
            return $this->database;
        });
        
        // Cache Service
        $this->container->set(CacheService::class, function() {
            $this->cache = new CacheService($this->logger);
            return $this->cache;
        });        // Security Service
        $this->container->set(SecurityService::class, function() {
            $this->security = new SecurityService(
                $this->container->get(DatabaseService::class),
                $this->container->get(CacheService::class),
                $this->logger
            );
            return $this->security;
        });
        
        // Auth Service
        $this->container->set(AuthService::class, function() {
            $this->auth = new AuthService(
                $this->container->get(DatabaseService::class),
                $this->container->get(CacheService::class),
                $this->logger
            );
            return $this->auth;
        });
          // Enhanced Agent Coordinator
        $this->container->set(EnhancedAgentCoordinator::class, function() {
            $this->agentCoordinator = new EnhancedAgentCoordinator(
                $this->logger
            );
            return $this->agentCoordinator;
        });
    }
    
    /**
     * Initialize Slim application
     */
    /**

     * initializeApplication 方法

     *

     * @return void

     */

    private function initializeApplication(): void
    {
        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();
        
        // Add routing middleware
        $this->app->addRoutingMiddleware();
        
        // Add error middleware
        $errorMiddleware = $this->app->addErrorMiddleware(
            ($_ENV['APP_ENV'] ?? getenv('APP_ENV')) === 'development',
            true,
            true
        );
        
        $errorMiddleware->setErrorHandler(
            \Slim\Exception\HttpNotFoundException::class,
            [$this, 'handleNotFound']
        );
    }
    
    /**
     * Configure middleware stack
     */
    /**

     * configureMiddleware 方法

     *

     * @return void

     */

    private function configureMiddleware(): void
    {
        // CORS middleware
        $this->app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
            $response = $handler->handle($request);
            
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        });
        
        // Security headers middleware
        $this->app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
            $response = $handler->handle($request);
            
            return $response
                ->withHeader('X-Content-Type-Options', 'nosniff')
                ->withHeader('X-Frame-Options', 'DENY')
                ->withHeader('X-XSS-Protection', '1; mode=block')
                ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->withHeader('Content-Security-Policy', "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: *");
        });
        
        // Body parsing middleware
        $this->app->addBodyParsingMiddleware();
    }
      /**
     * Setup routing system
     */
    /**

     * setupRouting 方法

     *

     * @return void

     */

    private function setupRouting(): void
    {
        try {
            // Initialize CompleteRouterIntegration for advanced routing
            $this->router = new CompleteRouterIntegration(
                $this->app,
                $this->container->get(DatabaseService::class),
                $this->container->get(SecurityService::class),
                $this->container->get(CacheService::class),
                $this->logger
            );
            
            // Add enhanced AI agent coordination routes
            $this->setupEnhancedAgentRoutes();
            
            // Load existing routes configuration as fallback
            if (file_exists(APP_ROOT . '/config/routes_simple.php')) {
                $routesConfig = require APP_ROOT . '/config/routes_simple.php';
                $routesConfig($this->app);
            }
            
            $this->logger->info('Complete Router Integration initialized successfully');
            
        } catch (\Exception $e) {
            $this->logger->error('Router setup error: ' . $e->getMessage());
            
            // Fallback to simple routing
            if (file_exists(APP_ROOT . '/config/routes_simple.php')) {
                $routesConfig = require APP_ROOT . '/config/routes_simple.php';
                $routesConfig($this->app);
                $this->logger->info('Fallback to simple routes loaded');
            }
        }
    }
      /**
     * Perform system initialization tasks
     */
    /**

     * performSystemInitialization 方法

     *

     * @return void

     */

    private function performSystemInitialization(): void
    {
        try {
            // Test database connection
            $dbService = $this->container->get(DatabaseService::class);
            $dbService->getPdo();
            $this->logger->info('Database connection verified');
            
            // Log system startup
            $this->logger->info('AlingAi Pro Application initialized successfully', [
                'version' => APP_VERSION,
                'environment' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production',
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('System initialization error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }    }
      /**
     * Setup enhanced AI agent coordination routes
     */
    /**

     * setupEnhancedAgentRoutes 方法

     *

     * @return void

     */

    private function setupEnhancedAgentRoutes(): void
    {
        // Enhanced AI Agent API routes
        $this->app->group('/api/v2/agents', function (RouteCollectorProxy $group) {
            // Task management endpoints
            $group->post('/task/assign', [$this, 'assignAgentTask']);
            $group->get('/task/{taskId}/status', [$this, 'getTaskStatus']);
            $group->get('/system/status', [$this, 'getAgentSystemStatus']);
            $group->get('/performance/report', [$this, 'getPerformanceReport']);
        });
        
        $this->logger->info('Enhanced Agent Coordination routes registered');
    }
    
    // Enhanced Agent API Handlers
    
    /**

    
     * assignAgentTask 方法

    
     *

    
     * @param ServerRequestInterface $request

    
     * @param ResponseInterface $response

    
     * @return void

    
     */

    
    public function assignAgentTask(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $agentCoordinator = $this->container->get(EnhancedAgentCoordinator::class);
            
            $taskResult = $agentCoordinator->assignTask($data['task'] ?? '', $data['context'] ?? []);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'task_id' => $taskResult['task_id'],
                'assigned_agent' => $taskResult['agent_id'],
                'status' => 'assigned',
                'message' => 'Task assigned successfully'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $this->logger->error('Task assignment error: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**

    
     * getTaskStatus 方法

    
     *

    
     * @param ServerRequestInterface $request

    
     * @param ResponseInterface $response

    
     * @param array $args

    
     * @return void

    
     */

    
    public function getTaskStatus(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $taskId = $args['taskId'] ?? '';
            $agentCoordinator = $this->container->get(EnhancedAgentCoordinator::class);
            
            $status = $agentCoordinator->getTaskStatus($taskId);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'task_id' => $taskId,
                'status' => $status
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }
      /**

       * getPerformanceReport 方法

       *

       * @param ServerRequestInterface $request

       * @param ResponseInterface $response

       * @return void

       */

      public function getPerformanceReport(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $agentCoordinator = $this->container->get(EnhancedAgentCoordinator::class);
            $report = $agentCoordinator->getAgentPerformanceReport();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'performance_report' => $report,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**

    
     * getAgentSystemStatus 方法

    
     *

    
     * @param ServerRequestInterface $request

    
     * @param ResponseInterface $response

    
     * @return void

    
     */

    
    public function getAgentSystemStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $agentCoordinator = $this->container->get(EnhancedAgentCoordinator::class);
            $status = $agentCoordinator->getStatus();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'system_status' => $status,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**
     * Handle 404 errors
     */
    /**

     * handleNotFound 方法

     *

     * @param ServerRequestInterface $request

     * @param \Throwable $exception

     * @param bool $displayErrorDetails

     * @return void

     */

    public function handleNotFound(ServerRequestInterface $request, \Throwable $exception, bool $displayErrorDetails): ResponseInterface
    {
        $response = $this->app->getResponseFactory()->createResponse(404);
        
        // Check if this is an API request
        $path = $request->getUri()->getPath();
        if (str_starts_with($path, '/api/')) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'The requested API endpoint was not found',
                'path' => $path
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        // For web requests, redirect to main page
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }
    
    /**
     * PSR-15 RequestHandlerInterface implementation
     */
    /**

     * handle 方法

     *

     * @param ServerRequestInterface $request

     * @return void

     */

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->handle($request);
    }
    
    /**
     * Run the application
     */
    /**

     * run 方法

     *

     * @return void

     */

    public function run(): void
    {
        try {
            $this->app->run();
        } catch (\Throwable $e) {
            $this->logger->error('Application runtime error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
            ]);
            
            // Show friendly error page in production
            if (($_ENV['APP_ENV'] ?? getenv('APP_ENV')) !== 'development') {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Internal Server Error',
                    'message' => 'An unexpected error occurred. Please try again later.',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'support' => 'Contact system administrator if the problem persists'
                ]);
            } else {
                throw $e;
            }
        }
    }
    
    /**
     * Get the Slim app instance
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
     * Get the DI container
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
     * Get system status for monitoring
     */
    /**

     * getSystemStatus 方法

     *

     * @return void

     */

    public function getSystemStatus(): array
    {
        return [
            'status' => 'healthy',
            'version' => APP_VERSION,
            'environment' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production',
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'timestamp' => date('Y-m-d H:i:s'),
            'components' => [
                'database' => 'active',
                'cache' => 'active',
                'auth' => 'active',
                'routing' => 'active'
            ]
        ];
    }
}
