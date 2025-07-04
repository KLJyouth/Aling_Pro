<?php
/**
 * AlingAi Pro 管理中心入口点
 * 
 * 负责处理管理中心的请求
 */

// 定义根路径
define('BASE_PATH', dirname(dirname(__DIR__)));
define('ADMIN_PATH', BASE_PATH . '/admin-center');

// 包含管理中心引导文件
require_once ADMIN_PATH . '/bootstrap.php';

// 创建App实例并运行
$app = new \App\Core\App($router);
$app->run();
