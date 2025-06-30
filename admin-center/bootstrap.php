<?php
/**
 * 应用引导文件
 * 负责应用的初始化和全局设置
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 设置内部字符编码
mb_internal_encoding('UTF-8');

// 定义常量
define('BASE_PATH', dirname(__FILE__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('ROUTES_PATH', BASE_PATH . '/routes');
define('VIEWS_PATH', BASE_PATH . '/resources/views');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('PUBLIC_PATH', BASE_PATH . '/public');

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
    // 将命名空间转换为文件路径
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
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