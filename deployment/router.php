<?php
/**
 * PHP内置服务器路由器
 * 处理所有请求并转发到index.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 静态文件直接返回
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// 所有其他请求转发到index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/public/index.php';
