<?php
/**
 * AlingAi Pro 安装向导 - 环境检查
 */

// 设置响应头
header("Content-Type: application/json");

// 检查PHP版本
$phpVersion = [
    "status" => version_compare(PHP_VERSION, "8.1.0") >= 0,
    "message" => "当前PHP版本: " . PHP_VERSION
];

// 检查PDO扩展
$pdo = [
    "status" => extension_loaded("pdo"),
    "message" => extension_loaded("pdo") ? null : "PDO扩展未安装"
];

// 检查Mbstring扩展
$mbstring = [
    "status" => extension_loaded("mbstring"),
    "message" => extension_loaded("mbstring") ? null : "Mbstring扩展未安装"
];

// 检查JSON扩展
$json = [
    "status" => extension_loaded("json"),
    "message" => extension_loaded("json") ? null : "JSON扩展未安装"
];

// 检查OpenSSL扩展
$openssl = [
    "status" => extension_loaded("openssl"),
    "message" => extension_loaded("openssl") ? null : "OpenSSL扩展未安装"
];

// 检查存储目录是否可写
$storageDir = __DIR__ . "/../../storage";
$storageWritable = [
    "status" => is_dir($storageDir) && is_writable($storageDir),
    "message" => is_dir($storageDir) && is_writable($storageDir) ? null : "存储目录不存在或不可写"
];

// 检查配置目录是否可写
$configDir = __DIR__ . "/../../config";
$configWritable = [
    "status" => is_dir($configDir) && is_writable($configDir),
    "message" => is_dir($configDir) && is_writable($configDir) ? null : "配置目录不存在或不可写"
];

// 组装结果
$result = [
    "php_version" => $phpVersion,
    "pdo" => $pdo,
    "mbstring" => $mbstring,
    "json" => $json,
    "openssl" => $openssl,
    "storage_writable" => $storageWritable,
    "config_writable" => $configWritable
];

// 输出结果
echo json_encode($result);
