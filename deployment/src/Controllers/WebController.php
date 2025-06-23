<?php
/**
 * Web控制器
 * 处理前端页面路由和渲染
 */

namespace AlingAi\Controllers;

use AlingAi\Services\AuthService;
use AlingAi\Services\UserService;
use AlingAi\Utils\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WebController extends BaseController {
    
    private $authService;
    private $userService;
    protected $logger;
      public function __construct(
        \AlingAi\Services\DatabaseServiceInterface $db,
        \AlingAi\Services\CacheService $cache
    ) {
        parent::__construct($db, $cache);
          // 创建必需的依赖项
        $logger = new \Monolog\Logger('webcontroller');
        $logger->pushHandler(new \Monolog\Handler\NullHandler());
        $hasher = new \AlingAi\Utils\PasswordHasher();
        $uploader = new \AlingAi\Utils\FileUploader();
        
        // 初始化服务
        $this->authService = new AuthService($db, $cache, $logger);
        $this->userService = new UserService($db, $cache, $hasher, $uploader);
        $this->logger = new Logger();
    }
    
    /**
     * 获取公共配置
     */
    private function getPublicConfig(): array
    {
        return [
            'app_name' => 'AlingAi Pro',
            'version' => '2.0.0',
            'api_base_url' => '/api',
            'websocket_url' => 'ws://localhost:8080',
            'features' => [
                'registration' => true,
                'chat' => true,
                'documents' => true
            ]
        ];
    }
    
    /**
     * 首页
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $user = $this->getCurrentUser($request);
            $config = $this->getPublicConfig();
            
            $data = [
                'title' => '珑凌科技 | 量子安全·智能未来',
                'user' => $user,
                'config' => $config,
                'quantumEnabled' => true,
                'features' => [
                    'quantumSecurity' => true,
                    'aiChat' => true,
                    'realtime' => true,
                    'multiLanguage' => true
                ]
            ];
            
            return $this->renderPage($response, 'index.html', $data);
            
        } catch (\Exception $e) {
            $this->logger->error('Index page error: ' . $e->getMessage());
            return $this->renderErrorPage($response, 500, '服务器内部错误');
        }
    }
    
    /**
     * 登录页面
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $user = $this->getCurrentUser($request);
            
            // 已登录用户跳转到仪表板
            if ($user) {
                $redirect = $request->getQueryParams()['redirect'] ?? '/dashboard';
                return $this->redirect($response, $redirect);
            }
            
            $data = [
                'title' => '登录 - AlingAi',
                'redirect' => $request->getQueryParams()['redirect'] ?? '/dashboard',
                'enableRegister' => true,
                'enableSocialLogin' => false
            ];
            
            return $this->renderPage($response, 'login.html', $data);
            
        } catch (\Exception $e) {
            $this->logger->error('Login page error: ' . $e->getMessage());
            return $this->renderErrorPage($response, 500, '登录页面加载失败');
        }
    }
    
    /**
     * 注册页面
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $user = $this->getCurrentUser($request);
            
            // 已登录用户跳转到仪表板
            if ($user) {
                return $this->redirect($response, '/dashboard');
            }
            
            $data = [
                'title' => '注册 - AlingAi',
                'termsUrl' => '/terms',
                'privacyUrl' => '/privacy',
                'enableSocialLogin' => false
            ];
            
            return $this->renderPage($response, 'register.html', $data);
            
        } catch (\Exception $e) {
            $this->logger->error('Register page error: ' . $e->getMessage());
            return $this->renderErrorPage($response, 500, '注册页面加载失败');
        }
    }
    
    /**
     * 仪表板页面
     */
    public function dashboard(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->redirect($response, '/login?redirect=/dashboard');
            }
            
            // 简化的统计数据
            $stats = [
                'totalConversations' => 0,
                'totalMessages' => 0,
                'lastLogin' => $user['last_login_at'] ?? null,
                'memberSince' => $user['created_at'] ?? date('Y-m-d'),
                'usageThisMonth' => 0
            ];
            
            $data = [
                'title' => '仪表板 - AlingAi',
                'user' => $user,
                'stats' => $stats,
                'recentConversations' => [],
                'systemStatus' => $this->getSystemStatus()
            ];
            
            return $this->renderPage($response, 'dashboard.html', $data);
            
        } catch (\Exception $e) {
            $this->logger->error('Dashboard page error: ' . $e->getMessage());
            return $this->renderErrorPage($response, 500, '仪表板加载失败');
        }
    }
    
    /**
     * 用户资料页面
     */
    public function profile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user) {
                return $this->redirect($response, '/login?redirect=/profile');
            }
            
            $data = [
                'title' => '个人资料 - AlingAi',
                'user' => $user,
                'settings' => [],
                'uploadConfig' => [
                    'maxFileSize' => 5242880, // 5MB
                    'allowedTypes' => ['image/jpeg', 'image/png', 'image/gif']
                ]
            ];
            
            return $this->renderPage($response, 'profile.html', $data);
            
        } catch (\Exception $e) {
            $this->logger->error('Profile page error: ' . $e->getMessage());
            return $this->renderErrorPage($response, 500, '个人资料页面加载失败');
        }
    }
    
    /**
     * 管理页面
     */
    public function admin(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $user = $this->getCurrentUser($request);
            
            if (!$user || $user['role'] !== 'admin') {
                return $this->renderErrorPage($response, 403, '访问被拒绝');
            }
            
            $data = [
                'title' => '管理控制台 - AlingAi',
                'user' => $user,
                'systemStats' => $this->getAdminStats(),
                'serverInfo' => $this->getServerInfo()
            ];
            
            return $this->renderPage($response, 'admin.html', $data);
            
        } catch (\Exception $e) {
            $this->logger->error('Admin page error: ' . $e->getMessage());
            return $this->renderErrorPage($response, 500, '管理页面加载失败');
        }
    }
      // 注意：renderPage, renderErrorPage, redirect 方法已在 BaseController 中定义
    
    /**
     * 获取当前用户
     */
    protected function getCurrentUser($request = null): ?array {
        try {
            $token = $this->extractToken($request);
            if (!$token) {
                return null;
            }
            
            return $this->authService->validateToken($token);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 提取token
     */
    private function extractToken(ServerRequestInterface $request): ?string {
        // 从Authorization header获取
        $authHeader = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        // 从Cookie获取
        $cookies = $request->getCookieParams();
        return $cookies['token'] ?? null;
    }
    
    /**
     * 获取基础URL
     */
    private function getBaseUrl(): string {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
    
    /**
     * 获取系统状态
     */
    private function getSystemStatus(): array {
        return [
            'status' => 'online',
            'version' => '1.0.0',
            'uptime' => $this->getUptime(),
            'memoryUsage' => $this->getMemoryUsage()
        ];
    }
      /**
     * 获取管理员统计数据
     */
    private function getAdminStats(): array {
        return [
            'totalUsers' => $this->userService->getTotalUsers(),
            'activeUsers' => $this->userService->getActiveUsersToday(),
            'totalConversations' => $this->db->count('conversations'),
            'totalMessages' => $this->db->count('messages'),
            'systemLoad' => sys_getloadavg()[0] ?? 0,
            'diskUsage' => $this->getDiskUsage()
        ];
    }
    
    /**
     * 获取服务器信息
     */
    private function getServerInfo(): array {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'operating_system' => PHP_OS,
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        ];
    }
    
    /**
     * 获取系统运行时间
     */
    private function getUptime(): string {
        $uptime = time() - filemtime(__DIR__ . '/../../public/index.php');
        return gmdate('H:i:s', $uptime);
    }
    
    /**
     * 获取内存使用情况
     */
    private function getMemoryUsage(): array {
        return [
            'used' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        ];
    }
    
    /**
     * 获取磁盘使用情况
     */
    private function getDiskUsage(): array {
        $total = disk_total_space(__DIR__);
        $free = disk_free_space(__DIR__);
        $used = $total - $free;
        
        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }
}
