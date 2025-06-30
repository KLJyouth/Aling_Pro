<?php
/**
 * AlingAi Pro - 简化版应用程序类
 * 不依赖Slim框架和PSR接口
 */

declare(strict_types=1);

namespace AlingAi\Core;

/**
 * SimpleApplication - 简单的应用程序类
 * 
 * 不依赖任何框架的简单应用程序类
 */
class SimpleApplication
{
    /**
     * 应用程序根目录
     * 
     * @var string
     */
    protected $appRoot;
    
    /**
     * 应用程序配置
     * 
     * @var array
     */
    protected $config = [];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->appRoot = dirname(dirname(__DIR__));
        
        // 初始化配置
        $this->initializeConfiguration();
        
        // 设置错误处理
        $this->setupErrorHandling();
    }
    
    /**
     * 初始化配置
     */
    protected function initializeConfiguration()
    {
        // 加载基本配置
        $this->config = [
            'app' => [
                'name' => 'AlingAi Pro',
                'version' => '6.0.0',
                'env' => getenv('APP_ENV') ?: 'development',
                'debug' => true,
                'url' => 'http://localhost:8000',
                'timezone' => 'Asia/Shanghai',
                'locale' => 'zh_CN',
            ],
            'security' => [
                'session_lifetime' => 120,
                'cookie_secure' => false,
                'cookie_httponly' => true,
                'csrf_protection' => true,
            ],
            'database' => [
                'driver' => 'mysql',
                'host' => 'localhost',
                'database' => 'alingai_pro',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
        ];
    }
    
    /**
     * 设置错误处理
     */
    protected function setupErrorHandling()
    {
        // 根据环境设置错误报告
        if ($this->config['app']['env'] === 'production') {
            error_reporting(E_ERROR | E_PARSE);
            ini_set('display_errors', 0);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
        
        // 设置错误处理函数
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }
    
    /**
     * 处理PHP错误
     * 
     * @param int $errno 错误级别
     * @param string $errstr 错误消息
     * @param string $errfile 错误文件
     * @param int $errline 错误行号
     * @return bool
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        $error = date('[Y-m-d H:i:s]') . " ERROR: $errstr in $errfile:$errline";
        error_log($error . PHP_EOL, 3, $this->appRoot . '/logs/error.log');
        
        if ($this->config['app']['env'] !== 'production') {
            echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px 0;border-radius:5px;'>";
            echo "<strong>错误:</strong> $errstr in $errfile:$errline";
            echo "</div>";
        }
        
        return true;
    }
    
    /**
     * 处理未捕获的异常
     * 
     * @param \Throwable $exception 异常对象
     * @return void
     */
    public function handleException($exception)
    {
        $error = date('[Y-m-d H:i:s]') . " FATAL ERROR: " . $exception->getMessage() . PHP_EOL;
        $error .= "Stack trace:" . PHP_EOL;
        $error .= $exception->getTraceAsString();
        error_log($error . PHP_EOL, 3, $this->appRoot . '/logs/error.log');
        
        if ($this->config['app']['env'] !== 'production') {
            echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px 0;border-radius:5px;'>";
            echo "<strong>异常:</strong> " . $exception->getMessage() . "<br>";
            echo "<strong>文件:</strong> " . $exception->getFile() . ":" . $exception->getLine() . "<br>";
            echo "<strong>堆栈跟踪:</strong> <pre>" . $exception->getTraceAsString() . "</pre>";
            echo "</div>";
        } else {
            // 在生产环境中显示友好的错误页面
            header('HTTP/1.1 500 Internal Server Error');
            echo file_get_contents($this->appRoot . '/public/500.html');
        }
    }
    
    /**
     * 运行应用程序
     * 
     * @return void
     */
    public function run()
    {
        // 记录启动时间
        $startTime = microtime(true);
        
        try {
            // 处理请求
            $this->handleRequest();
        } catch (\Throwable $e) {
            // 捕获所有异常
            $this->handleException($e);
        }
        
        // 计算执行时间
        $executionTime = microtime(true) - $startTime;
        
        // 记录执行时间
        echo "<!-- 应用程序执行完成，耗时 " . round($executionTime, 4) . " 秒 -->";
    }
    
    /**
     * 处理请求
     * 
     * @return void
     */
    protected function handleRequest()
    {
        // 获取请求URI
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        $path = ltrim($path, '/');
        
        // 默认页面是index
        if (empty($path)) {
            $path = 'index';
        }
        
        // 简单的路由映射
        $routes = [
            'index' => 'index.html',
            'login' => 'login_standalone.php',
            'dashboard' => 'dashboard.html',
            'profile' => 'profile.html',
        ];
        
        // 处理路由
        if (isset($routes[$path])) {
            $filePath = $this->appRoot . '/public/' . $routes[$path];
            if (file_exists($filePath)) {
                // 如果是PHP文件，则包含它
                if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
                    include $filePath;
                } else {
                    // 否则输出文件内容
                    header('Content-Type: ' . $this->getMimeType($filePath));
                    readfile($filePath);
                }
            } else {
                $this->show404($path);
            }
        } else {
            $this->show404($path);
        }
    }
    
    /**
     * 获取文件的MIME类型
     * 
     * @param string $filePath 文件路径
     * @return string MIME类型
     */
    protected function getMimeType($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'ico' => 'image/x-icon',
        ];
        
        return $mimeTypes[$extension] ?? 'text/plain';
    }
    
    /**
     * 显示404页面
     * 
     * @param string $path 请求的路径
     * @return void
     */
    protected function show404($path)
    {
        header('HTTP/1.0 404 Not Found');
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>404 - 页面未找到</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        h1 {
            color: #e74c3c;
            margin-top: 0;
        }
        p {
            color: #555;
            margin-bottom: 20px;
        }
        .path {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            margin: 20px 0;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404 - 页面未找到</h1>
        <p>您请求的页面不存在。</p>
        <div class="path">' . htmlspecialchars($path) . '</div>
        <p><a href="/">返回首页</a></p>
    </div>
</body>
</html>';
    }
    
    /**
     * 获取配置值
     * 
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function config($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
} 