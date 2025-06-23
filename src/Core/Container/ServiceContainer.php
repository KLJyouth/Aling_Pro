<?php

declare(strict_types=1);

namespace AlingAi\Core\Container;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as HttpClient;
use AlingAi\Services\DeepSeekAIService;
use AlingAi\Services\ChatService;
use AlingAi\Services\UserService;
use AlingAi\Core\Database\DatabaseManager;
use AlingAi\Core\Logger\LoggerFactory;
use AlingAi\Controllers\Api\EnhancedChatApiController;
use AlingAi\Controllers\Api\UserApiController;
use AlingAi\Controllers\Api\AuthApiController;

/**
 * 服务容器
 * 管理应用程序的依赖注入
 */
class ServiceContainer implements ContainerInterface
{
    private array $services = [];
    private array $factories = [];

    public function __construct()
    {
        $this->registerServices();
    }

    /**
     * 注册所有服务
     */
    private function registerServices(): void
    {
        // 基础服务
        $this->factories[LoggerInterface::class] = function() {
            return LoggerFactory::createLogger();
        };

        $this->factories[HttpClient::class] = function() {
            return new HttpClient([
                'timeout' => 30,
                'connect_timeout' => 10,
                'verify' => true
            ]);
        };

        $this->factories[DatabaseManager::class] = function() {
            return new DatabaseManager();
        };

        // AI服务
        $this->factories[DeepSeekAIService::class] = function() {
            $logger = $this->get(LoggerInterface::class);
            $httpClient = $this->get(HttpClient::class);
            
            return new DeepSeekAIService(
                $logger,
                $httpClient,
                [
                    'api_key' => getenv('DEEPSEEK_API_KEY'),
                    'api_url' => getenv('DEEPSEEK_API_URL') ?: 'https://api.deepseek.com'
                ]
            );
        };

        // 业务服务
        $this->factories[ChatService::class] = function() {
            $dbManager = $this->get(DatabaseManager::class);
            $logger = $this->get(LoggerInterface::class);
            $aiService = $this->get(DeepSeekAIService::class);
            
            return new ChatService($dbManager, $logger, $aiService);
        };

        $this->factories[UserService::class] = function() {
            $dbManager = $this->get(DatabaseManager::class);
            $logger = $this->get(LoggerInterface::class);
            
            return new UserService($dbManager, $logger);
        };

        // API控制器
        $this->factories[EnhancedChatApiController::class] = function() {
            $chatService = $this->get(ChatService::class);
            return new EnhancedChatApiController($chatService);
        };

        $this->factories[UserApiController::class] = function() {
            $userService = $this->get(UserService::class);
            return new UserApiController($userService);
        };

        $this->factories[AuthApiController::class] = function() {
            return new AuthApiController();
        };
    }

    /**
     * 获取服务实例
     */
    public function get(string $id)
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->factories[$id])) {
            $this->services[$id] = $this->factories[$id]($this);
            return $this->services[$id];
        }

        throw new \InvalidArgumentException("Service not found: {$id}");
    }

    /**
     * 检查服务是否存在
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }

    /**
     * 注册自定义服务
     */
    public function register(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * 注册单例服务
     */
    public function registerSingleton(string $id, $instance): void
    {
        $this->services[$id] = $instance;
    }
} 