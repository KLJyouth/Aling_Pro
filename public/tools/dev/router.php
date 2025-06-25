<?php
/**
 * AlingAi Pro - PHP内置服务器路由脚本
 * 
 * 此文件用于PHP内置服务器的路由处理
 * 静态文件直接提供服务，动态请求转发到index.php
 */

// 获取请求URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH)];

// 检查请求的文件是否存在
$requested_file = __DIR__ . '/public' . $uri;

// 如果请求的是文件且该文件存在，直接提供服务
if ($uri !== '/' && file_exists($requested_file) && !is_dir($requested_file)) {
    // 获取文件扩展名
    $extension = pathinfo($requested_file, PATHINFO_EXTENSION];
    
    // 设置适当的Content-Type
    switch ($extension) {
        case 'css':
            header('Content-Type: text/css'];
            break;
        case 'js':
            header('Content-Type: application/javascript'];
            break;
        case 'json':
            header('Content-Type: application/json'];
            break;
        case 'png':
            header('Content-Type: image/png'];
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg'];
            break;
        case 'svg':
            header('Content-Type: image/svg+xml'];
            break;
    }
    
    // 输出文件内容
    readfile($requested_file];
    return true;
}

// 否则，将请求转发到index.php
require_once __DIR__ . '/public/index.php';
