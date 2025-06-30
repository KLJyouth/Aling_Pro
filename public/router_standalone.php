<?php
/**
 * AlingAi Pro - 独立路由器
 * 
 * 不依赖任何框架的简单路由器
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 定义应用常量（如果尚未定义）
if (!defined('APP_START_TIME')) define('APP_START_TIME', microtime(true));
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
if (!defined('APP_PUBLIC')) define('APP_PUBLIC', __DIR__);
if (!defined('APP_VERSION')) define('APP_VERSION', '6.0.0');
if (!defined('APP_NAME')) define('APP_NAME', 'AlingAi Pro - Standalone');

// 错误报告设置
$isProduction = (getenv('APP_ENV') === 'production');

if ($isProduction) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
}

// 设置增强的安全头部
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// 获取请求URI
$requestUri = $_SERVER["REQUEST_URI"];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = ltrim($path, "/");

// 默认页面是index
if (empty($path)) {
    $path = "index";
}

// 路由映射表
$routes = [
    // 核心页面
    "index" => "index.html",
    "login" => "login_standalone.php",
    "dashboard" => "dashboard.html",
    
    // 其他页面
    "register" => "register.html",
    "forgot-password" => "forgot-password.html",
    "profile" => "profile.html",
    "settings" => "settings.html",
    "chat" => "chat.html",
    "docs" => "docs.html",
    "help" => "help.html",
    "contact" => "contact.html",
    
    // 错误页面
    "404" => "404.html",
    "500" => "500.html"
];

// 处理请求
if (isset($routes[$path])) {
    // 已知路由映射到特定文件
    $filePath = APP_PUBLIC . "/" . $routes[$path];
    serveFile($filePath);
} else {
    // 检查路径是否存在为直接文件
    $filePath = APP_PUBLIC . "/" . $path;
    if (file_exists($filePath) && !is_dir($filePath)) {
        // 检查是否为PHP文件，应该执行而不是提供
        if (pathinfo($filePath, PATHINFO_EXTENSION) === "php") {
            // 包含PHP文件以执行它
            include_once($filePath);
            exit;
        } else {
            serveFile($filePath);
        }
    } else {
        // 尝试附加.html
        $htmlPath = $filePath . ".html";
        if (file_exists($htmlPath)) {
            serveFile($htmlPath);
        } 
        // 尝试附加.php
        else if (file_exists($filePath . ".php")) {
            include_once($filePath . ".php");
            exit;
        } else {
            // 检查这是否是没有尾部斜杠的目录路径
            if (file_exists($filePath) && is_dir($filePath)) {
                // 检查目录中的index.html
                $indexPath = rtrim($filePath, "/") . "/index.html";
                $indexPhpPath = rtrim($filePath, "/") . "/index.php";
                
                if (file_exists($indexPath)) {
                    serveFile($indexPath);
                } else if (file_exists($indexPhpPath)) {
                    include_once($indexPhpPath);
                    exit;
                } else {
                    showNotFound($path);
                }
            } else {
                showNotFound($path);
            }
        }
    }
}

/**
 * 提供文件并设置适当的内容类型
 * 
 * @param string $filePath 要提供的文件路径
 * @return void
 */
function serveFile($filePath) {
    if (!file_exists($filePath)) {
        showNotFound(basename($filePath));
        return;
    }

    // 根据文件扩展名确定内容类型
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $contentTypes = [
        "html" => "text/html; charset=UTF-8",
        "css" => "text/css",
        "js" => "application/javascript",
        "json" => "application/json",
        "png" => "image/png",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "gif" => "image/gif",
        "svg" => "image/svg+xml",
        "pdf" => "application/pdf",
        "ico" => "image/x-icon",
        "webp" => "image/webp",
        "woff" => "font/woff",
        "woff2" => "font/woff2",
        "ttf" => "font/ttf",
        "eot" => "application/vnd.ms-fontobject"
    ];
    
    $contentType = $contentTypes[$extension] ?? "text/plain";
    header("Content-Type: $contentType");
    
    // 静态资源的基本缓存
    if (in_array($extension, ["css", "js", "png", "jpg", "jpeg", "gif", "svg", "webp", "woff", "woff2", "ttf", "eot"])) {
        $maxAge = 604800; // 1周
        header("Cache-Control: public, max-age=$maxAge");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $maxAge) . " GMT");
    } else {
        // HTML和动态内容不缓存
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
    }
    
    // 输出文件
    readfile($filePath);
    
    // 为HTML文件添加执行时间注释（在开发环境中）
    if ($extension === "html" && getenv("APP_ENV") !== "production") {
        $execTime = round((microtime(true) - APP_START_TIME) * 1000, 2);
        echo "\n<!-- 页面在 {$execTime}ms 内提供 -->";
    }
}

/**
 * 显示404未找到页面
 * 
 * @param string $path 未找到的路径
 * @return void
 */
function showNotFound($path) {
    http_response_code(404);
    header("Content-Type: text/html; charset=UTF-8");
    
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - 页面未找到 | AlingAi Pro</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        h1 {
            font-size: 36px;
            margin-bottom: 10px;
            color: #1a73e8;
        }
        p {
            font-size: 18px;
            margin-bottom: 25px;
            color: #555;
        }
        a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1a73e8;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        a:hover {
            background-color: #155db1;
        }
        .path {
            font-family: monospace;
            background: #f5f5f5;
            padding: 6px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404 - 页面未找到</h1>
        <p>您正在寻找的页面不存在。</p>
        <div class="path">' . htmlspecialchars($path) . '</div>
        <a href="/">返回首页</a>
    </div>
</body>
</html>';
} 