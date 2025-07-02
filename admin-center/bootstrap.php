<?php
/**
 * 应用程序引导文件
 * 
 * 负责初始化应用程序环境和加载核心组件
 */

// 定义基本路径常量
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('ROUTES_PATH', BASE_PATH . '/routes');
define('VIEWS_PATH', BASE_PATH . '/resources/views');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');

// 设置错误报告级别
error_reporting(E_ALL);
ini_set('display_errors', true);

// 加载自动加载器
require_once __DIR__ . '/vendor/autoload.php';

// 加载环境变量
if (file_exists(BASE_PATH . '/.env')) {
    try {
        $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
        $dotenv->load();
    } catch (\Exception $e) {
        // 记录环境变量加载错误，但继续执行
        error_log('无法加载.env文件: ' . $e->getMessage());
    }
}

// 创建必要的目录结构
$directories = [
    STORAGE_PATH,
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/temp',
    STORAGE_PATH . '/uploads',
    STORAGE_PATH . '/backups'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 检查核心类文件是否存在
$appCorePath = APP_PATH . '/Core';
if (!is_dir($appCorePath)) {
    // 如果目录不存在，创建它
    mkdir($appCorePath, 0755, true);
    
    // 记录错误
    error_log("警告: 核心类目录不存在，已自动创建: {$appCorePath}");
    
    // 显示错误信息并退出
    echo "<h1>系统初始化错误</h1>";
    echo "<p>核心类目录不存在，请确保应用程序正确安装。</p>";
    echo "<p>系统已尝试创建目录: {$appCorePath}</p>";
    echo "<p>请联系管理员完成系统设置。</p>";
    exit;
}

// 检查核心类文件
$coreFiles = [
    'Config.php',
    'Logger.php',
    'Database.php',
    'Security.php',
    'Controller.php',
    'Model.php',
    'View.php',
    'Router.php',
    'App.php'
];

$missingFiles = [];
foreach ($coreFiles as $file) {
    if (!file_exists($appCorePath . '/' . $file)) {
        $missingFiles[] = $file;
    }
}

if (!empty($missingFiles)) {
    // 记录错误
    error_log("错误: 缺少核心类文件: " . implode(', ', $missingFiles));
    
    // 显示错误信息并退出
    echo "<h1>系统初始化错误</h1>";
    echo "<p>缺少以下核心类文件:</p>";
    echo "<ul>";
    foreach ($missingFiles as $file) {
        echo "<li>{$file}</li>";
    }
    echo "</ul>";
    echo "<p>请联系管理员完成系统设置。</p>";
    exit;
}

// 加载核心类
require_once APP_PATH . '/Core/Config.php';
require_once APP_PATH . '/Core/Logger.php';
require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/Security.php';
require_once APP_PATH . '/Core/Controller.php';
require_once APP_PATH . '/Core/Model.php';
require_once APP_PATH . '/Core/View.php';
require_once APP_PATH . '/Core/Router.php';
require_once APP_PATH . '/Core/App.php';

// 初始化配置
\App\Core\Config::loadAll();
$config = \App\Core\Config::get();

// 初始化日志系统（优先初始化，以便记录后续可能的错误）
if (\App\Core\Config::has('logging')) {
    $logConfig = \App\Core\Config::get('logging');
    \App\Core\Logger::init($logConfig);
} else {
    // 使用默认配置初始化日志
    \App\Core\Logger::init(['path' => STORAGE_PATH . '/logs']);
}

// 初始化数据库
if (\App\Core\Config::has('database')) {
    \App\Core\Database::setConfig(\App\Core\Config::get('database'));
}

// 初始化安全系统
if (\App\Core\Config::has('security')) {
    \App\Core\Security::init(\App\Core\Config::get('security'));
}

// 设置时区
if (\App\Core\Config::has('app.timezone')) {
    date_default_timezone_set(\App\Core\Config::get('app.timezone'));
} else {
    date_default_timezone_set('Asia/Shanghai'); // 默认时区
}

// 设置区域信息
if (\App\Core\Config::has('app.locale')) {
    setlocale(LC_ALL, \App\Core\Config::get('app.locale'));
} else {
    setlocale(LC_ALL, 'zh_CN.UTF-8'); // 默认区域
}

// 启动会话（对于非静态资源请求）
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$extension = pathinfo($requestPath, PATHINFO_EXTENSION);
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map'];

if (!in_array(strtolower($extension), $staticExtensions)) {
    // 检查会话是否已经启动
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // 启动会话（非静态资源）
        session_start();
    }
}

// 设置安全头部
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// 注册错误处理器
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (class_exists('\App\Core\Logger')) {
        \App\Core\Logger::error("PHP错误：[$errno] $errstr", [
            'file' => $errfile,
            'line' => $errline
        ]);
    }
    
    // 显示错误页面（仅对致命错误）
    if ($errno == E_ERROR || $errno == E_CORE_ERROR || $errno == E_USER_ERROR) {
        $isDebug = \App\Core\Config::get('app.debug', false);
        if (file_exists(VIEWS_PATH . '/errors/500.php')) {
            include VIEWS_PATH . '/errors/500.php';
        } else {
            echo "<h1>系统错误</h1>";
            if ($isDebug) {
                echo "<p>错误：{$errstr}</p>";
                echo "<p>文件：{$errfile}:{$errline}</p>";
            } else {
                echo "<p>系统遇到错误，请稍后再试。</p>";
            }
        }
        exit;
    }
    
    return false; // 继续执行PHP标准错误处理
});

// 初始化路由器
$router = new \App\Core\Router();

// 加载路由配置
if (file_exists(ROUTES_PATH . '/web.php')) {
    require_once ROUTES_PATH . '/web.php';
}

if (file_exists(ROUTES_PATH . '/api.php')) {
    require_once ROUTES_PATH . '/api.php';
}

return $router; 