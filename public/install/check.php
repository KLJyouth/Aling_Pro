<?php
/**
 * AlingAi Pro 系统要求检查脚本
 */

header('Content-Type: application/json');

// 检查PHP版本
$requiredPhpVersion = '7.4.0';
$phpVersionStatus = version_compare(PHP_VERSION, $requiredPhpVersion, '>=');

// 检查必要的PHP扩展
$pdo = extension_loaded('pdo');
$pdoSqlite = extension_loaded('pdo_sqlite');
$json = extension_loaded('json');
$mbstring = extension_loaded('mbstring');
$curl = extension_loaded('curl');

// 检查目录权限
$rootDir = dirname(dirname(__DIR__));
$storageDir = $rootDir . '/storage';
$configDir = $rootDir . '/config';

// 如果目录不存在，尝试创建
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0755, true);
}

if (!is_dir($configDir)) {
    @mkdir($configDir, 0755, true);
}

$storageWritable = is_dir($storageDir) && is_writable($storageDir);
$configWritable = is_dir($configDir) && is_writable($configDir);

// 检查可选的PHP扩展
$opcache = extension_loaded('opcache');
$gd = extension_loaded('gd');
$fileinfo = extension_loaded('fileinfo');

// 检查PHP内存限制
$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = return_bytes($memoryLimit);
$recommendedMemory = 128 * 1024 * 1024; // 128MB
$memoryOk = $memoryLimitBytes >= $recommendedMemory || $memoryLimitBytes <= 0; // -1 表示无限制

// 返回结果
echo json_encode([
    'php_version' => [
        'status' => $phpVersionStatus,
        'version' => PHP_VERSION,
        'required' => $requiredPhpVersion
    ],
    'pdo' => $pdo,
    'pdo_sqlite' => $pdoSqlite,
    'json' => $json,
    'mbstring' => $mbstring,
    'curl' => $curl,
    'storage_writable' => $storageWritable,
    'config_writable' => $configWritable,
    'opcache' => $opcache,
    'gd' => $gd,
    'fileinfo' => $fileinfo,
    'memory_limit' => [
        'value' => $memoryLimit,
        'bytes' => $memoryLimitBytes,
        'recommended' => $recommendedMemory,
        'status' => $memoryOk
    ],
    'all_requirements_met' => $phpVersionStatus && $pdo && $pdoSqlite && $json && $storageWritable && $configWritable
]);

/**
 * 将内存限制字符串转换为字节数
 */
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int) $val;
    
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}

