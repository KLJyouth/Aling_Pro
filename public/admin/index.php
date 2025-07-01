<?php
/**
 * AlingAi Pro - 管理中心入口点文件
 * 
 * 此文件作为前端入口点，将请求重定向到实际的admin-center应用
 * 遵循入口点(Entry Point)模式，增强安全性
 */

// 定义一个标识，表明这是一个有效的入口点
define('ADMIN_ENTRY_POINT', true);

// 设置相对路径
$adminCenterPath = dirname(dirname(__DIR__)) . '/admin-center';

// 包含admin-center的引导文件
require_once $adminCenterPath . '/bootstrap.php';

// 创建App实例并运行
$app = new \App\Core\App($router);
$app->run(); 
