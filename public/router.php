<?php
/**
 * AlingAi Pro - 主路由器
 * 
 * 根据URL路径将请求路由到适当的HTML文件
 */

// 定义HTML文件的基础路径
define("BASE_PATH", __DIR__);
define("START_TIME", microtime(true));

// 设置增强的安全头部
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Get the request URI
$requestUri = $_SERVER["REQUEST_URI"];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = ltrim($path, "/");

// Default page is index
if (empty($path)) {
    $path = "index";
}

// 处理管理后台请求
if ($path === 'admin' || strpos($path, 'admin/') === 0) {
    $adminFile = BASE_PATH . '/admin/index.php';
    if (file_exists($adminFile)) {
        include_once($adminFile);
        exit;
    }
}

// Map routes to HTML files
$routes = [
    // Core pages
    "index" => "index.html",
    "login" => "login.php",  // 使用PHP处理登录
    "register" => "register.php", // 使用PHP处理注册
    "dashboard" => "dashboard.html",
    "profile" => "profile.html",
    "chat" => "chat.html",
    
    // 社交登录路由
    "login/google" => "login.php?provider=google",
    "login/github" => "login.php?provider=github",
    "login/google/callback" => "login.php?provider=google&callback=1",
    "login/github/callback" => "login.php?provider=github&callback=1",
    
    // 邮箱验证路由
    "email/verify" => "email_verify.php",
    "email/verification-notification" => "email_verification_notification.php",
    
    // 管理后台路由
    "admin" => "admin/index.php",
    "admin/dashboard" => "admin/index.php",
    "admin/members" => "admin/index.php",
    "admin/orders" => "admin/index.php",
    "admin/settings" => "admin/index.php",
    "admin/logs" => "admin/index.php",
    "admin/tools" => "admin/index.php",
    "admin/security" => "admin/index.php",
    "admin/reports" => "admin/index.php",
    
    // Information & Support pages
    "security" => "security.php",
    "docs" => "docs.php",
    "api-docs" => "api-docs.php",
    "terms" => "terms.php",
    "privacy" => "privacy.php",
    "support" => "support.php",
    "help" => "help.php",
    "system-status" => "system-status.php",
    "partners" => "partners.php",
    "contact" => "contact.php",
    
    // Feature pages
    "platform" => "platform.php",
    "ecosystem" => "ecosystem.php",
    "portal" => "portal.php",
    "zero-trust" => "zero-trust.php",
    "ai-assistant" => "ai-assistant.php"
];

// Handle the request
if (strpos($path, "api/") === 0) {
    // API requests are handled by the API router
    include_once(BASE_PATH . "/api/index.php");
} else if (isset($routes[$path])) {
    // Known routes map to specific HTML files
    $filePath = BASE_PATH . "/" . $routes[$path];
    serveFile($filePath);
} else {
    // Check if the path exists as a direct file
    $filePath = BASE_PATH . "/" . $path;
    if (file_exists($filePath) && !is_dir($filePath)) {
        // Check if it's a PHP file that should be executed, not served
        if (pathinfo($filePath, PATHINFO_EXTENSION) === "php") {
            // Include the PHP file to execute it
            include_once($filePath);
            exit;
        } else {
            serveFile($filePath);
        }
    } else {
        // Try appending .html
        $htmlPath = $filePath . ".html";
        if (file_exists($htmlPath)) {
            serveFile($htmlPath);
        } 
        // Try appending .php
        else if (file_exists($filePath . ".php")) {
            include_once($filePath . ".php");
            exit;
        } else {
            // Check if this is a directory path without a trailing slash
            if (file_exists($filePath) && is_dir($filePath)) {
                // Check for index.html in the directory
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
 * Serve a file with appropriate content type
 * 
 * @param string $filePath Path to the file to serve
 * @return void
 */
function serveFile($filePath) {
    if (!file_exists($filePath)) {
        showNotFound(basename($filePath));
        return;
    }

    // Determine content type based on file extension
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
    
    // Basic caching for static assets
    if (in_array($extension, ["css", "js", "png", "jpg", "jpeg", "gif", "svg", "webp", "woff", "woff2", "ttf", "eot"])) {
        $maxAge = 604800; // 1 week
        header("Cache-Control: public, max-age=$maxAge");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $maxAge) . " GMT");
    } else {
        // No caching for HTML and dynamic content
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
    }
    
    // Output the file
    readfile($filePath);
    
    // Add execution time comment for HTML files in development
    if ($extension === "html" && getenv("APP_ENV") !== "production") {
        $execTime = round((microtime(true) - START_TIME) * 1000, 2);
        echo "\n<!-- Page served in {$execTime}ms -->";
    }
}

/**
 * Show 404 Not Found page
 * 
 * @param string $path The path that was not found
 * @return void
 */
function showNotFound($path) {
    http_response_code(404);
    header("Content-Type: text/html; charset=UTF-8");
    
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | AlingAi Pro</title>
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
        <h1>404 - Page Not Found</h1>
        <p>The page you are looking for does not exist.</p>
        <div class="path">' . htmlspecialchars($path) . '</div>
        <a href="/">Return to Home</a>
    </div>
</body>
</html>';
}

