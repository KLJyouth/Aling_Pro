<?php
/**
 * PHP内置服务器路由器
 * 处理所有请求并转发到相应的文件
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 处理API请求 - 优先使用快速API
if (strpos($uri, '/api/') === 0) {
    // 使用优化的快速API入口
    require_once __DIR__ . '/public/api/fast_index.php';
    return true;
}

// 静态文件直接返回
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// 根目录请求转发到index.html
if ($uri === '/') {
    require_once __DIR__ . '/public/index.html';
    return true;
}

// 其他请求尝试在public目录中查找
$publicFile = __DIR__ . '/public' . $uri;
if (file_exists($publicFile)) {
    return false;
}

// 对于不存在的文件，尝试转发到index.php（SPA路由）
if (pathinfo($uri, PATHINFO_EXTENSION) === '' || pathinfo($uri, PATHINFO_EXTENSION) === 'html') {
    require_once __DIR__ . '/public/index.html';
    return true;
}

// 返回404
http_response_code(404);
echo '404 Not Found';
return true;
