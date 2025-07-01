<?php
namespace App\Core;

/**
 * 应用程序核心类
 * 
 * 负责初始化应用程序并处理请求
 */
class App
{
    /**
     * 应用程序路由器
     * @var Router
     */
    protected $router;
    
    /**
     * 当前环境
     * @var string
     */
    protected $environment;
    
    /**
     * 是否开启调试模式
     * @var bool
     */
    protected $debugMode;
    
    /**
     * 构造函数
     * @param Router $router 路由器实例
     */
    public function __construct($router)
    {
        $this->router = $router;
        $this->environment = getenv('APP_ENV') ?: 'production';
        $this->debugMode = (getenv('APP_DEBUG') === 'true' || Config::get('app.debug', false));
        
        // 根据环境设置PHP错误报告级别
        $this->configureErrorReporting();
    }
    
    /**
     * 根据当前环境配置错误报告级别
     * @return void
     */
    protected function configureErrorReporting()
    {
        if ($this->debugMode) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', 0);
        }
    }
    
    /**
     * 运行应用程序
     * @return void
     */
    public function run()
    {
        try {
            // 处理当前请求
            $this->processRequest();
        } catch (\Exception $e) {
            // 捕获并处理异常
            $this->handleException($e);
        }
    }
    
    /**
     * 处理HTTP请求
     * @return void
     */
    protected function processRequest()
    {
        // 获取请求URI和方法
        $uri = $this->getRequestUri();
        $method = $this->getRequestMethod();
        
        // 检查请求类型
        $isApiRequest = $this->isApiRequest($uri);
        
        // 为API请求启用CORS
        if ($isApiRequest) {
            $this->setupCORS();
            
            // 检查是否是预检请求
            if ($method === 'OPTIONS') {
                exit;
            }
            
            // 检查API认证
            if (!$this->checkApiAuthentication($uri)) {
                $this->sendJsonResponse([
                    'error' => true,
                    'message' => '未授权访问',
                    'code' => 401
                ], 401);
                exit;
            }
        }
        
        // 分发请求到路由器
        $this->router->dispatch($uri, $method);
    }
    
    /**
     * 获取请求URI
     * @return string 请求URI
     */
    protected function getRequestUri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // 移除查询字符串
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        
        return $uri;
    }
    
    /**
     * 获取请求方法
     * @return string 请求方法
     */
    protected function getRequestMethod()
    {
        // 获取原始请求方法
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // 检查是否通过表单字段_method覆盖HTTP方法
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        // 检查是否通过请求头X-HTTP-Method-Override覆盖HTTP方法
        $headers = getallheaders();
        if (isset($headers['X-HTTP-Method-Override'])) {
            $method = strtoupper($headers['X-HTTP-Method-Override']);
        }
        
        return $method;
    }
    
    /**
     * 检查是否为API请求
     * @param string $uri 请求URI
     * @return bool 是否为API请求
     */
    protected function isApiRequest($uri)
    {
        return strpos($uri, '/api/') === 0;
    }
    
    /**
     * 检查API认证
     * @param string $uri 请求URI
     * @return bool 认证是否有效
     */
    protected function checkApiAuthentication($uri)
    {
        // 不需要认证的路径（如登录、注册等）
        $publicPaths = [
            '/api/v1/auth/login',
            '/api/v1/auth/register',
            '/api/v1/auth/forgot-password'
        ];
        
        // 如果是公开路径，无需认证
        foreach ($publicPaths as $path) {
            if (strpos($uri, $path) === 0) {
                return true;
            }
        }
        
        // 检查认证令牌
        $token = $this->getBearerToken();
        
        // 如果没有提供令牌，拒绝访问
        if (!$token) {
            return false;
        }
        
        // 验证令牌
        try {
            return Security::validateJwt($token);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 获取Bearer认证令牌
     * @return string|null 令牌或null
     */
    protected function getBearerToken()
    {
        $headers = getallheaders();
        
        // 从Authorization头部获取
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        // 从请求参数获取
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }
        
        return null;
    }
    
    /**
     * 设置CORS头部
     * @return void
     */
    protected function setupCORS()
    {
        // 允许的域
        $allowedOrigins = Config::get('security.cors_domains', ['*']);
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        
        // 如果请求来自允许的域，设置Access-Control-Allow-Origin
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        // 其他CORS头部
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24小时
    }
    
    /**
     * 发送JSON响应
     * @param mixed $data 响应数据
     * @param int $statusCode HTTP状态码
     * @return void
     */
    protected function sendJsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * 处理异常
     * @param \Exception $e 异常对象
     * @return void
     */
    protected function handleException($e)
    {
        // 记录异常
        Logger::error('应用程序异常: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // 检查是否为API请求
        $uri = $this->getRequestUri();
        $isApiRequest = $this->isApiRequest($uri);
        
        if ($isApiRequest) {
            // API错误响应
            $this->sendJsonResponse([
                'error' => true,
                'message' => $this->debugMode ? $e->getMessage() : '服务器内部错误',
                'code' => 500,
                'details' => $this->debugMode ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString())
                ] : null
            ], 500);
        } else {
            // Web错误响应
            header('HTTP/1.1 500 Internal Server Error');
            
            // 尝试加载500错误页面
            $errorPagePath = VIEWS_PATH . '/errors/500.php';
            if (file_exists($errorPagePath)) {
                // 设置变量供错误页面使用
                $errorMessage = $e->getMessage();
                $errorFile = $e->getFile();
                $errorLine = $e->getLine();
                $errorTrace = $e->getTraceAsString();
                $isDebug = $this->debugMode;
                
                // 包含错误页面
                include $errorPagePath;
            } else {
                // 如果没有自定义500页面，显示简单的错误信息
                echo '<div style="text-align:center; font-family: Arial, sans-serif; margin-top: 50px;">';
                echo '<h1 style="color:#c00;">500 - 服务器内部错误</h1>';
                echo '<p style="color:#666;">抱歉，服务器遇到了一个问题。</p>';
                
                if ($this->debugMode) {
                    echo '<div style="text-align:left; margin:20px auto; max-width:800px; border:1px solid #ddd; padding:20px; background:#f9f9f9;">';
                    echo '<h3>错误信息</h3>';
                    echo '<p><strong>消息:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<p><strong>文件:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
                    echo '<p><strong>行号:</strong> ' . $e->getLine() . '</p>';
                    echo '<h3>堆栈跟踪</h3>';
                    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                    echo '</div>';
                }
                
                echo '<p><a href="/admin" style="color:#0066cc; text-decoration:none;">返回首页</a></p>';
                echo '</div>';
            }
        }
        
        exit;
    }
    
    /**
     * 获取当前环境
     * @return string 环境名称
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
    
    /**
     * 检查是否为开发环境
     * @return bool 是否为开发环境
     */
    public function isDevEnvironment()
    {
        return $this->environment === 'development' || $this->environment === 'local';
    }
    
    /**
     * 检查是否为生产环境
     * @return bool 是否为生产环境
     */
    public function isProductionEnvironment()
    {
        return $this->environment === 'production';
    }
    
    /**
     * 检查是否为测试环境
     * @return bool 是否为测试环境
     */
    public function isTestEnvironment()
    {
        return $this->environment === 'testing';
    }
} 