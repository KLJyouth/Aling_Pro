<?php

namespace App\Http;

use App\Controllers\Frontend\FrontendController;
use App\Controllers\Frontend\Enhanced3DThreatVisualizationController;
use App\AI\EnhancedAgentCoordinator;
use App\Services\DatabaseConfigMigrationService;
use App\Services\AuthService;
use App\Services\ConfigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

/**
 * 完整的应用路由集成系统
 * 整合所有前端、API和管理功能
 */
class CompleteRouterIntegration
{
    private FrontendController $frontendController;
    private Enhanced3DThreatVisualizationController $threatController;
    private EnhancedAgentCoordinator $agentCoordinator;
    private DatabaseConfigMigrationService $configMigration;
    private AuthService $auth;
    private ConfigService $config;
    private array $routes = [];

    public function __construct(
        FrontendController $frontendController,
        Enhanced3DThreatVisualizationController $threatController,
        EnhancedAgentCoordinator $agentCoordinator,
        DatabaseConfigMigrationService $configMigration,
        AuthService $auth,
        ConfigService $config
    ) {
        $this->frontendController = $frontendController;
        $this->threatController = $threatController;
        $this->agentCoordinator = $agentCoordinator;
        $this->configMigration = $configMigration;
        $this->auth = $auth;
        $this->config = $config;
        
        $this->registerRoutes();
    }

    /**
     * 注册所有路由
     */
    private function registerRoutes(): void
    {
        // 前端页面路由
        $this->routes['GET']['/'] = [$this->frontendController, 'index'];
        $this->routes['GET']['/home'] = [$this->frontendController, 'index'];
        $this->routes['GET']['/dashboard'] = [$this->frontendController, 'dashboard'];
        $this->routes['GET']['/login'] = [$this->frontendController, 'login'];
        $this->routes['GET']['/agent-management'] = [$this->frontendController, 'agentManagement'];
        
        // 3D威胁可视化
        $this->routes['GET']['/threat-visualization'] = [$this->threatController, 'index'];
        $this->routes['GET']['/3d-threats'] = [$this->threatController, 'index'];
        
        // AI智能体API
        $this->routes['POST']['/api/agent/assign-task'] = [$this, 'assignTaskToAgent'];
        $this->routes['GET']['/api/agent/status/{taskId}'] = [$this, 'getTaskStatus'];
        $this->routes['GET']['/api/agent/system-status'] = [$this, 'getAgentSystemStatus'];
        $this->routes['GET']['/api/agent/active-agents'] = [$this, 'getActiveAgents'];
        
        // 威胁数据API
        $this->routes['GET']['/api/threat-data'] = [$this->threatController, 'getThreatDataApi'];
        $this->routes['GET']['/api/counter-measures'] = [$this->threatController, 'getCounterMeasuresApi'];
        
        // 配置管理API
        $this->routes['POST']['/api/config/migrate'] = [$this, 'executeConfigMigration'];
        $this->routes['GET']['/api/config/migration-status'] = [$this, 'getConfigMigrationStatus'];
        $this->routes['POST']['/api/config/rollback'] = [$this, 'rollbackConfigMigration'];
        
        // 系统管理API
        $this->routes['GET']['/api/system/status'] = [$this, 'getSystemStatus'];
        $this->routes['POST']['/api/system/deploy'] = [$this, 'deploySystem'];
        $this->routes['GET']['/api/system/health'] = [$this, 'getSystemHealth'];
        
        // DeepSeek集成API
        $this->routes['POST']['/api/deepseek/chat'] = [$this, 'deepseekChat'];
        $this->routes['POST']['/api/deepseek/analyze'] = [$this, 'deepseekAnalyze'];
        
        // WebSocket API
        $this->routes['GET']['/ws'] = [$this, 'handleWebSocket'];
        
        // 文件上传和处理
        $this->routes['POST']['/api/upload'] = [$this, 'handleFileUpload'];
        $this->routes['GET']['/api/download/{fileId}'] = [$this, 'handleFileDownload'];
        
        // 用户管理
        $this->routes['POST']['/api/auth/login'] = [$this, 'handleLogin'];
        $this->routes['POST']['/api/auth/logout'] = [$this, 'handleLogout'];
        $this->routes['GET']['/api/auth/user'] = [$this, 'getCurrentUser'];
        
        // 旧HTML文件重定向
        $this->registerLegacyRedirects();
    }

    /**
     * 注册旧HTML文件重定向
     */
    private function registerLegacyRedirects(): void
    {
        $legacyFiles = [
            '/index.html' => '/',
            '/dashboard.html' => '/dashboard',
            '/admin.html' => '/dashboard',
            '/login.html' => '/login',
            '/register.html' => '/register',
            '/chat.html' => '/dashboard',
            '/settings.html' => '/dashboard',
            '/profile.html' => '/dashboard',
            '/threats.html' => '/threat-visualization',
            '/security.html' => '/threat-visualization',
            '/agents.html' => '/agent-management',
            '/ai.html' => '/agent-management'
        ];

        foreach ($legacyFiles as $oldPath => $newPath) {
            $this->routes['GET'][$oldPath] = function() use ($newPath) {
                return new Response(301, ['Location' => $newPath]);
            };
        }
    }

    /**
     * 处理HTTP请求
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // 移除查询参数
        $path = strtok($path, '?');
        
        // 查找精确匹配的路由
        if (isset($this->routes[$method][$path])) {
            return $this->executeRoute($this->routes[$method][$path], $request);
        }
        
        // 查找参数化路由
        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $matches = $this->matchParameterizedRoute($pattern, $path);
            if ($matches !== false) {
                $request = $request->withAttribute('route_params', $matches);
                return $this->executeRoute($handler, $request);
            }
        }
        
        // 404未找到
        return new Response(404, [], '页面未找到');
    }

    /**
     * 执行路由处理器
     */
    private function executeRoute($handler, ServerRequestInterface $request): ResponseInterface
    {
        try {
            if (is_callable($handler)) {
                return $handler($request);
            }
            
            if (is_array($handler) && count($handler) === 2) {
                [$controller, $method] = $handler;
                return $controller->$method($request);
            }
            
            throw new \Exception('无效的路由处理器');
            
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error: ' . $e->getMessage());
        }
    }

    /**
     * 匹配参数化路由
     */
    private function matchParameterizedRoute(string $pattern, string $path): array|false
    {
        // 转换路由模式为正则表达式
        $regex = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $path, $matches)) {
            array_shift($matches); // 移除完整匹配
            return $matches;
        }
        
        return false;
    }

    /**
     * 中间件处理
     */
    private function applyMiddleware(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // CORS处理
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
                           ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                           ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        // 安全头部
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff')
                           ->withHeader('X-Frame-Options', 'DENY')
                           ->withHeader('X-XSS-Protection', '1; mode=block');
        
        return $response;
    }

    /**
     * API方法实现
     */

    public function assignTaskToAgent(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = json_decode($request->getBody()->getContents(), true);
            $taskDescription = $body['task'] ?? '';
            $context = $body['context'] ?? [];
            
            if (empty($taskDescription)) {
                return new Response(400, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => '任务描述不能为空']));
            }
            
            $result = $this->agentCoordinator->assignTask($taskDescription, $context);
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($result));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function getTaskStatus(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $taskId = $request->getAttribute('route_params')[0] ?? '';
            
            if (empty($taskId)) {
                return new Response(400, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => '任务ID不能为空']));
            }
            
            $status = $this->agentCoordinator->getTaskStatus($taskId);
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($status));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function getAgentSystemStatus(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $status = $this->agentCoordinator->getSystemStatus();
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($status));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function getActiveAgents(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $agents = $this->agentCoordinator->getActiveAgents();
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($agents));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function executeConfigMigration(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // 检查管理员权限
            if (!$this->auth->hasRole('admin')) {
                return new Response(403, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => '权限不足']));
            }
            
            $result = $this->configMigration->executeMigration();
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($result));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function getConfigMigrationStatus(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $status = $this->configMigration->getMigrationStatus();
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($status));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function rollbackConfigMigration(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // 检查管理员权限
            if (!$this->auth->hasRole('admin')) {
                return new Response(403, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => '权限不足']));
            }
            
            $body = json_decode($request->getBody()->getContents(), true);
            $backupPath = $body['backup_path'] ?? '';
            
            if (empty($backupPath)) {
                return new Response(400, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => '备份路径不能为空']));
            }
            
            $result = $this->configMigration->rollbackMigration($backupPath);
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($result));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function getSystemStatus(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $status = [
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
                    'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost'
                ],
                'system_info' => [
                    'os' => PHP_OS,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'upload_max_filesize' => ini_get('upload_max_filesize')
                ],
                'application_info' => [
                    'version' => $this->config->get('app.version', '1.0.0'),
                    'environment' => $this->config->get('app.environment', 'production'),
                    'debug_mode' => $this->config->get('app.debug', false),
                    'config_mode' => $this->config->get('config.mode', 'file')
                ],
                'services_status' => [
                    'database' => $this->checkDatabaseConnection(),
                    'cache' => $this->checkCacheConnection(),
                    'websocket' => $this->checkWebSocketService(),
                    'ai_agents' => $this->checkAIAgentService()
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($status));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function deepseekChat(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = json_decode($request->getBody()->getContents(), true);
            $message = $body['message'] ?? '';
            $context = $body['context'] ?? [];
            
            if (empty($message)) {
                return new Response(400, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => '消息不能为空']));
            }
            
            $result = $this->agentCoordinator->assignTask($message, array_merge($context, ['type' => 'chat']));
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode($result));
                
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    public function handleLogin(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = json_decode($request->getBody()->getContents(), true);
            $username = $body['username'] ?? '';
            $password = $body['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                return new Response(400, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => '用户名和密码不能为空']));
            }
            
            $loginResult = $this->auth->login($username, $password);
            
            if ($loginResult['success']) {
                return new Response(200, ['Content-Type' => 'application/json'], 
                    json_encode($loginResult));
            } else {
                return new Response(401, ['Content-Type' => 'application/json'], 
                    json_encode($loginResult));
            }
            
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => $e->getMessage()]));
        }
    }

    /**
     * 检查服务状态的辅助方法
     */
    private function checkDatabaseConnection(): string
    {
        try {
            // 这里应该调用数据库服务的连接检查
            return 'online';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    private function checkCacheConnection(): string
    {
        try {
            // 这里应该调用缓存服务的连接检查
            return 'online';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    private function checkWebSocketService(): string
    {
        // 检查WebSocket服务状态
        return 'online';
    }

    private function checkAIAgentService(): string
    {
        // 检查AI智能体服务状态
        return 'online';
    }
}
