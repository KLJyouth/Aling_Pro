<?php
/**
 * AlingAi Pro - Enhanced Application Bootstrap
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
use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

// Import our enhanced components
use AlingAi\Controllers\WebController;
use AlingAi\Controllers\AdminController;
use AlingAi\Controllers\SystemController;
use AlingAi\Controllers\Api\SystemApiController;
use AlingAi\Http\ModernRouterSystem;
use AlingAi\Services\DatabaseService;
use AlingAi\Services\CacheService;
use AlingAi\Services\AuthService;

/**
 * Enhanced AlingAi Pro Application
 * Integrates all Three Complete Compilation components
 */
class AlingAiProApplication implements RequestHandlerInterface
{    private App $app;
    private Container $container;
    private Logger $logger;
    private ModernRouterSystem $router;
    private DatabaseService $database;
    private CacheService $cache;
    private AuthService $auth;
    
    /**
     * Create application instance
     */
    public static function create(): self
    {
        return new self();
    }
    
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
    private function initializeLogger(): void
    {
        $this->logger = new Logger('AlingAiPro');
        
        // Development handler
        if (getenv('APP_ENV') === 'development') {
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
    }
    
    /**
     * Register application services
     */
    private function registerServices(): void
    {
        // Database Service
        $this->container->set(DatabaseService::class, function() {
            return new DatabaseService();
        });
        
        // Cache Service
        $this->container->set(CacheService::class, function() {
            return new CacheService();
        });
        
        // Auth Service
        $this->container->set(AuthService::class, function() {
            return new AuthService(
                $this->container->get(DatabaseService::class),
                $this->container->get(CacheService::class)
            );
        });
        
        // Frontend Controller
        $this->container->set(FrontendController::class, function() {
            return new FrontendController(
                $this->container->get(DatabaseService::class),
                $this->container->get(AuthService::class)
            );
        });
        
        // 3D Threat Visualization Controller
        $this->container->set(Enhanced3DThreatVisualizationController::class, function() {
            return new Enhanced3DThreatVisualizationController(
                $this->container->get(DatabaseService::class)
            );
        });
        
        // Enhanced Agent Coordinator
        $this->container->set(EnhancedAgentCoordinator::class, function() {
            $coordinator = new EnhancedAgentCoordinator(
                $this->container->get(DatabaseService::class)
            );
            $this->agentCoordinator = $coordinator;
            return $coordinator;
        });
        
        // Database Config Migration Service
        $this->container->set(DatabaseConfigMigrationService::class, function() {
            $service = new DatabaseConfigMigrationService(
                $this->container->get(DatabaseService::class)
            );
            $this->configMigration = $service;
            return $service;
        });
    }
    
    /**
     * Initialize Slim application
     */
    private function initializeApplication(): void
    {
        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();
        
        // Add routing middleware
        $this->app->addRoutingMiddleware();
        
        // Add error middleware
        $errorMiddleware = $this->app->addErrorMiddleware(
            getenv('APP_ENV') === 'development',
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
    private function setupRouting(): void
    {
        $this->router = new CompleteRouterIntegration(
            $this->container->get(FrontendController::class),
            $this->container->get(Enhanced3DThreatVisualizationController::class),
            $this->container->get(EnhancedAgentCoordinator::class),
            $this->container->get(DatabaseConfigMigrationService::class)
        );
        
        $this->router->registerRoutes($this->app);
        
        $this->logger->info('Complete router integration initialized with all enhanced components');
    }
    
    /**
     * Perform system initialization tasks
     */
    private function performSystemInitialization(): void
    {
        try {
            // Check and migrate configuration if needed
            if ($this->configMigration && file_exists(APP_ROOT . '/.env')) {
                $envVars = $this->parseEnvFile(APP_ROOT . '/.env');
                if (!empty($envVars)) {
                    $this->configMigration->migrateFromEnv($envVars);
                    $this->logger->info('Configuration migration completed successfully');
                }
            }
            
            // Initialize AI agent system
            if ($this->agentCoordinator) {
                $this->agentCoordinator->initializeAgentSystem();
                $this->logger->info('AI Agent System initialized');
            }
            
            // Log system startup
            $this->logger->info('AlingAi Pro Application initialized successfully', [
                'version' => APP_VERSION,
                'environment' => getenv('APP_ENV') ?: 'production',
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('System initialization error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Parse .env file for migration
     */
    private function parseEnvFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }
        
        $envVars = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
                [$key, $value] = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value, '"\'');
            }
        }
        
        return $envVars;
    }
    
    /**
     * Handle 404 errors
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->handle($request);
    }
    
    /**
     * Run the application
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
            if (getenv('APP_ENV') !== 'development') {
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
    public function getApp(): App
    {
        return $this->app;
    }
    
    /**
     * Get the DI container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
    
    /**
     * Get system status for monitoring
     */
    public function getSystemStatus(): array
    {
        return [
            'status' => 'healthy',
            'version' => APP_VERSION,
            'environment' => getenv('APP_ENV') ?: 'production',
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'uptime' => time() - (int)APP_START_TIME,
            'timestamp' => date('Y-m-d H:i:s'),
            'components' => [
                'frontend_controller' => 'active',
                'agent_coordinator' => 'active',
                'threat_visualization' => 'active',
                'config_migration' => 'active',
                'router_integration' => 'active'
            ]
        ];
    }
}
