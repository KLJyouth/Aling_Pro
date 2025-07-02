<?php
/**
 * AlingAi Pro 配置加载器
 * 
 * 加载配置文件并定义全局变量
 */

// 确定配置文件的绝对路径
$configPath = __DIR__ . '/config.php';

// 检查文件是否存在
if (!file_exists($configPath)) {
    die('配置文件不存在: ' . $configPath);
}

// 加载配置文件
$config = require_once $configPath;

// 定义全局变量供其他页面使用
$GLOBALS['config'] = $config;

// 设置一些常用的全局变量
$siteName = $config['site']['name'];
$siteTitle = $config['site']['title'];
$siteDescription = $config['site']['description'];
$siteUrl = $config['site']['url'];
$debug = $config['debug'];

// 返回配置
return $config; 