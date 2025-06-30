<?php
/**
 * AlingAi Pro - IT运维中心入口文件
 * 
 * 处理所有到达后台管理中心的请求
 */

// 加载引导文件
$router = require_once __DIR__ . '/bootstrap.php';

// 创建App实例并运行
$app = new \App\Core\App($router);
$app->run();
