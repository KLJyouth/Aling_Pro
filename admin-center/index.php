<?php
/**
 * AlingAi_pro 后台IT技术运维中心
 * 入口文件
 */

// 加载引导文件
require_once __DIR__ . '/bootstrap.php';

// 设置内容安全策略
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self';");

// 创建并运行应用
try {
    // 获取应用实例
    $app = \App\Core\App::getInstance();
    
    // 初始化应用
    $app->init();
    
    // 运行应用
    $app->run();
} catch (\Exception $e) {
    // 让异常处理器处理异常
    throw $e;
}
