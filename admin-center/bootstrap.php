<?php
/**
 * AlingAi Pro - IT运维中心引导文件
 * 负责初始化系统设置、常量定义和全局函数
 */

// 设置错误报告级别
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 设置内部字符编码
mb_internal_encoding('UTF-8');

// 定义常量（如果尚未定义）
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__FILE__));
if (!defined('APP_PATH')) define('APP_PATH', BASE_PATH . '/app');
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', BASE_PATH . '/config');
if (!defined('ROUTES_PATH')) define('ROUTES_PATH', BASE_PATH . '/routes');
if (!defined('VIEWS_PATH')) define('VIEWS_PATH', BASE_PATH . '/resources/views');
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', BASE_PATH . '/storage');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', BASE_PATH . '/public');
if (!defined('LOGS_PATH')) define('LOGS_PATH', STORAGE_PATH . '/logs');
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', STORAGE_PATH . '/uploads');

// 创建必要的目录
$directories = [
    STORAGE_PATH,
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/temp',
    STORAGE_PATH . '/uploads'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 设置自动加载
spl_autoload_register(function ($class) {
    // 转换命名空间为目录路径
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    
    // 检查类是否使用命名空间前缀
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // 获取相对类名
    $relativeClass = substr($class, $len);
    
    // 将命名空间分隔符替换为目录分隔符，并添加.php后缀
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // 如果文件存在，加载它
    if (file_exists($file)) {
        require $file;
    }
});

// 设置异常处理器
set_exception_handler(function ($exception) {
    // 记录异常信息
    error_log(sprintf(
        "[%s] 未捕获异常: %s in %s:%d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    ));
    
    // 如果已经初始化了Logger类，使用它记录异常
    if (class_exists('\App\Core\Logger')) {
        \App\Core\Logger::error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
    
    // 显示友好的错误页面
    if (getenv('APP_ENV') === 'development') {
        // 开发环境下显示详细错误信息
        http_response_code(500);
        echo "<h1>应用错误</h1>";
        echo "<p><strong>消息:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>文件:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>行号:</strong> " . $exception->getLine() . "</p>";
        echo "<h2>堆栈跟踪:</h2>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    } else {
        // 生产环境下显示友好的错误页面
        http_response_code(500);
        
        $errorPage = VIEWS_PATH . '/errors/500.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "<h1>服务器错误</h1>";
            echo "<p>抱歉，系统遇到了错误。请稍后再试或联系系统管理员。</p>";
        }
    }
    
    exit;
});

// 设置错误处理器
set_error_handler(function ($level, $message, $file, $line) {
    // 将错误转换为异常
    if (error_reporting() & $level) {
        throw new \ErrorException($message, 0, $level, $file, $line);
    }
});

// 注册关闭函数
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && ($error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR))) {
        // 调用异常处理器
        $exception = new \ErrorException(
            $error['message'], 0, $error['type'], $error['file'], $error['line']
        );
        
        // 获取当前设置的异常处理器
        $handler = set_exception_handler(function () {});
        restore_exception_handler();
        
        // 如果有异常处理器，调用它
        if ($handler) {
            call_user_func($handler, $exception);
        }
    }
});

// 启动会话
session_start();

// 设置安全头部
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// 加载环境变量（如果有.env文件）
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // 跳过注释行
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // 如果值包含在引号中，去除引号
        if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
            $value = $matches[2];
        }
        
        putenv("$name=$value");
    }
}

// 加载配置
require_once CONFIG_PATH . '/app.php';

// 注册路由
$router = [
    // 仪表盘
    '/' => ['DashboardController', 'index'],
    '/dashboard' => ['DashboardController', 'index'],
    
    // 认证
    '/login' => ['AuthController', 'login'],
    '/logout' => ['AuthController', 'logout'],
    
    // 用户管理
    '/users' => ['UserController', 'index'],
    '/users/create' => ['UserController', 'create'],
    '/users/store' => ['UserController', 'store'],
    '/users/edit/{id}' => ['UserController', 'edit'],
    '/users/update/{id}' => ['UserController', 'update'],
    '/users/delete/{id}' => ['UserController', 'delete'],
    '/users/show/{id}' => ['UserController', 'show'],
    
    // 系统设置
    '/settings' => ['SettingController', 'index'],
    '/settings/save' => ['SettingController', 'save'],
    
    // 日志管理
    '/logs' => ['LogController', 'index'],
    '/logs/view/{file}' => ['LogController', 'view'],
    '/logs/download/{file}' => ['LogController', 'download'],
    '/logs/clear' => ['LogController', 'clear'],
    
    // 系统监控
    '/monitoring' => ['MonitoringController', 'index'],
    '/monitoring/status' => ['MonitoringController', 'status'],
    
    // 安全管理
    '/security' => ['SecurityController', 'index'],
    '/security/scan' => ['SecurityController', 'scan'],
    
    // 运维工具
    '/tools' => ['ToolController', 'index'],
    '/tools/{name}' => ['ToolController', 'run'],
    
    // 运维报告
    '/reports' => ['ReportController', 'index'],
    '/reports/generate' => ['ReportController', 'generate'],
    '/reports/export/{id}' => ['ReportController', 'export']
];

// 全局函数
/**
 * 获取当前请求的路径
 * 
 * @return string 请求路径
 */
function getRequestPath() {
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    $position = strpos($path, '?');
    if ($position !== false) {
        $path = substr($path, 0, $position);
    }
    $path = rtrim($path, '/');
    return $path ?: '/';
}

/**
 * 获取请求方法
 * 
 * @return string 请求方法（GET, POST 等）
 */
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * 重定向到指定的URL
 * 
 * @param string $url 重定向的目标URL
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * 设置闪存消息，用于在下一个请求中显示
 * 
 * @param string $message 消息内容
 * @param string $type 消息类型（success, info, warning, danger）
 * @return void
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_message_type'] = $type;
}

/**
 * 检查路径是否匹配路由模式
 * 
 * @param string $pattern 路由模式
 * @param string $path 请求路径
 * @return array|false 匹配成功返回参数数组，否则返回false
 */
function matchRoute($pattern, $path) {
    // 将路由模式转换为正则表达式
    $patternRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
    $patternRegex = '#^' . $patternRegex . '$#';
    
    // 尝试匹配
    if (preg_match($patternRegex, $path, $matches)) {
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }
    
    return false;
}

// 返回路由数组
return $router; 