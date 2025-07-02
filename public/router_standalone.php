<?php
/**
 * AlingAi Pro - 独立路由处理器
 * 
 * 用于PHP内置服务器的路由处理
 */

// 定义基础路径
if (!defined('BASE_PATH')) {
    define("BASE_PATH", __DIR__);
}

// 获取请求URI
$requestUri = $_SERVER["REQUEST_URI"];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = ltrim($path, "/");

// 记录请求信息到控制台
file_put_contents("php://stdout", "\n请求路径: " . $path);

// 处理静态资源
if (preg_match('/\.(?:css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
    // 直接提供静态资源
    return false;
}

// 处理管理后台请求
if ($path === 'admin' || strpos($path, 'admin/') === 0) {
    $adminFile = BASE_PATH . '/admin/index.php';
    if (file_exists($adminFile)) {
        include_once($adminFile);
        exit;
    } else {
        file_put_contents("php://stdout", "\n管理后台文件不存在: " . $adminFile);
    }
}

// 处理解决方案路径
if (strpos($path, 'solutions/') === 0) {
    $solutionPath = BASE_PATH . '/' . $path . '.php';
    $solutionPathWithoutExt = BASE_PATH . '/' . $path;
    
    if (file_exists($solutionPath)) {
        include_once($solutionPath);
        exit;
    } else if (file_exists($solutionPathWithoutExt)) {
        include_once($solutionPathWithoutExt);
        exit;
    } else if (file_exists($solutionPathWithoutExt . '.html')) {
        include_once($solutionPathWithoutExt . '.html');
        exit;
    }
}

// 处理API请求
if (strpos($path, 'api/') === 0) {
    include_once(BASE_PATH . '/api.php');
    exit;
}

// 处理PHP文件
if (file_exists(BASE_PATH . '/' . $path . '.php')) {
    include_once(BASE_PATH . '/' . $path . '.php');
    exit;
}

// 处理HTML文件
if (file_exists(BASE_PATH . '/' . $path . '.html')) {
    return false;
}

// 处理目录索引文件
if (is_dir(BASE_PATH . '/' . $path)) {
    $dirPath = rtrim(BASE_PATH . '/' . $path, '/') . '/';
    if (file_exists($dirPath . 'index.php')) {
        include_once($dirPath . 'index.php');
        exit;
    } else if (file_exists($dirPath . 'index.html')) {
        return false;
    }
}

// 处理根路径
if (empty($path)) {
    include_once(BASE_PATH . '/index.php');
    exit;
}

// 如果所有条件都不满足，返回404页面
include_once(BASE_PATH . '/404.php');
exit; 