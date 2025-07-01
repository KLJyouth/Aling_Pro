<?php
namespace App\Core;

/**
 * 应用核心类
 * 负责初始化和管理应用
 */
class App
{
    /**
     * 应用实例
     * @var App
     */
    private static $instance = null;
    
    /**
     * 应用配置
     * @var array
     */
    private $config = [];
    
    /**
     * 路由配置
     * @var array
     */
    private $routes = [];
    
    /**
     * 构造函数，接收路由参数
     * @param array $router 路由配置
     */
    public function __construct(array $router = [])
    {
        self::$instance = $this;
        $this->routes = $router;
        $this->init();
    }
    
    /**
     * 获取应用实例
     * @return App 应用实例
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * 初始化应用
     * @return void
     */
    public function init()
    {
        // 加载所有配置
        Config::loadAll();
        $this->config = Config::get();
        
        // 初始化数据库
        if (isset($this->config['database'])) {
            Database::setConfig($this->config['database']);
        }
        
        // 初始化日志系统
        if (isset($this->config['logging'])) {
            $logConfig = $this->config['logging'];
            $logConfig['path'] = $this->config['paths']['logs'] ?? BASE_PATH . '/storage/logs';
            Logger::init($logConfig);
        }
        
        // 初始化安全系统
        if (isset($this->config['security'])) {
            Security::init($this->config['security']);
        }
        
        // 设置时区
        if (isset($this->config['app']['timezone'])) {
            date_default_timezone_set($this->config['app']['timezone']);
        }
    }
    
    /**
     * 运行应用
     * @return void
     */
    public function run()
    {
        try {
            // 获取请求URI和方法
            $requestUri = $_SERVER['REQUEST_URI'];
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            
            // 解析URL,去掉/admin前缀
            if (strpos($requestUri, '/admin') === 0) {
                $requestUri = substr($requestUri, 6); // 去掉'/admin'
                if (empty($requestUri)) {
                    $requestUri = '/';
                }
            }
            
            // 检查是否是登录相关路由
            $isLoginRoute = ($requestUri === '/login' || $requestUri === '/');
            
            // 会话检查 - 如果未登录且不是登录页面，则重定向到登录页面
            if (!isset($_SESSION['admin_user_id']) && !$isLoginRoute) {
                header('Location: /admin/login');
                exit;
            }
            
            // 如果已登录且访问登录页，则重定向到仪表盘
            if (isset($_SESSION['admin_user_id']) && $isLoginRoute) {
                header('Location: /admin/dashboard');
                exit;
            }
            
            // 初始化路由器
            $router = new Router();
            
            // 使用构造函数传入的路由配置
            if (!empty($this->routes)) {
                foreach ($this->routes as $route => $handler) {
                    $router->addRoute($route, $handler);
                }
            } else {
                // 如果没有传入路由配置，则从文件加载
                require_once ROUTES_PATH . '/web.php';
            }
            
            // 分发请求
            $router->dispatch($requestUri, $requestMethod);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * 处理异常
     * @param \Exception $e 异常对象
     * @return void
     */
    private function handleException(\Exception $e)
    {
        // 设置HTTP状态码
        http_response_code(500);
        
        // 记录错误日志
        Logger::error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // 显示错误页面
        if ($this->isDebugMode()) {
            // 调试模式下显示详细错误信息
            echo "<h1>系统错误</h1>";
            echo "<p><strong>错误信息:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>文件:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
            echo "<p><strong>行号:</strong> " . $e->getLine() . "</p>";
            echo "<h2>堆栈跟踪:</h2>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            // 生产环境下显示友好的错误页面
            $this->showErrorPage(500);
        }
    }
    
    /**
     * 显示错误页面
     * @param int $code HTTP状态码
     * @return void
     */
    public function showErrorPage($code)
    {
        // 设置HTTP状态码
        http_response_code($code);
        
        // 构建错误页面路径
        $errorPagePath = VIEWS_PATH . '/errors/' . $code . '.php';
        
        // 如果错误页面文件不存在，尝试使用通用错误页面
        if (!file_exists($errorPagePath)) {
            $errorPagePath = VIEWS_PATH . '/errors/error.php';
            
            // 如果通用错误页面也不存在，显示简单错误信息
            if (!file_exists($errorPagePath)) {
                echo "<h1>错误 $code</h1>";
                echo "<p>很抱歉，系统发生了错误。</p>";
                return;
            }
        }
        
        // 包含错误页面文件
        include $errorPagePath;
    }
    
    /**
     * 检查是否为调试模式
     * @return bool 是否为调试模式
     */
    private function isDebugMode()
    {
        return isset($this->config['app']['debug']) && $this->config['app']['debug'] === true;
    }
    
    /**
     * 获取配置项
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed 配置值或默认值
     */
    public function config($key = null, $default = null)
    {
        return Config::get($key, $default);
    }
} 