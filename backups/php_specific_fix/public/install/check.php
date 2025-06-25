<?php
/**
 * AlingAi Pro 安装向导 - 系统检查脚本
 * 检查系统环境是否满足安装要求
 */

header('Content-Type: application/json'];

// 定义检查结果数组
$result = [
    'success' => true,
    'required' => [],
    'recommended' => []
];

// 检查PHP版本
$phpVersion = phpversion(];
$requiredPhpVersion = '7.4.0';
$result['required'][] = [
    'name' => 'PHP版本',
    'passed' => version_compare($phpVersion, $requiredPhpVersion, '>='],
    'message' => '当前版本: ' . $phpVersion . ' (需要 ' . $requiredPhpVersion . ' 或更高]'
];

// 检查PDO扩展
$pdoInstalled = extension_loaded('pdo'];
$result['required'][] = [
    'name' => 'PDO扩展',
    'passed' => $pdoInstalled,
    'message' => $pdoInstalled ? '已安装' : '未安装'
];

// 检查PDO MySQL扩展
$pdoMysqlInstalled = extension_loaded('pdo_mysql'];
$result['required'][] = [
    'name' => 'PDO MySQL扩展',
    'passed' => $pdoMysqlInstalled,
    'message' => $pdoMysqlInstalled ? '已安装' : '未安装'
];

// 检查JSON扩展
$jsonInstalled = extension_loaded('json'];
$result['required'][] = [
    'name' => 'JSON扩展',
    'passed' => $jsonInstalled,
    'message' => $jsonInstalled ? '已安装' : '未安装'
];

// 检查cURL扩展
$curlInstalled = extension_loaded('curl'];
$result['required'][] = [
    'name' => 'cURL扩展',
    'passed' => $curlInstalled,
    'message' => $curlInstalled ? '已安装' : '未安装'
];

// 检查目录权限
$baseDir = dirname(dirname(__DIR__]];
$dirsToCheck = [
    $baseDir . '/config' => '配置目录',
    $baseDir . '/storage' => '存储目录',
    $baseDir . '/public/uploads' => '上传目录'
];

foreach ($dirsToCheck as $dir => $name] {
    $exists = file_exists($dir];
    $writable = $exists && is_writable($dir];
    
    if (!$exists] {
        // 尝试创建目录
        $created = @mkdir($dir, 0755, true];
        $writable = $created && is_writable($dir];
    }
    
    $result['required'][] = [
        'name' => $name . ' 权限',
        'passed' => $writable,
        'message' => $writable ? '可写' : ($exists ? '存在但不可写' : '不存在且无法创建']
    ];
}

// 检查OPcache扩展
$opcacheInstalled = extension_loaded('opcache'];
$result['recommended'][] = [
    'name' => 'OPcache扩展',
    'passed' => $opcacheInstalled,
    'message' => $opcacheInstalled ? '已安装' : '未安装 (建议安装以提高性能]'
];

// 检查Mbstring扩展
$mbstringInstalled = extension_loaded('mbstring'];
$result['recommended'][] = [
    'name' => 'Mbstring扩展',
    'passed' => $mbstringInstalled,
    'message' => $mbstringInstalled ? '已安装' : '未安装 (建议安装以支持多字节字符]'
];

// 检查GD扩展
$gdInstalled = extension_loaded('gd'];
$result['recommended'][] = [
    'name' => 'GD扩展',
    'passed' => $gdInstalled,
    'message' => $gdInstalled ? '已安装' : '未安装 (建议安装以支持图像处理]'
];

// 检查内存限制
$memoryLimit = ini_get('memory_limit'];
$memoryLimitBytes = return_bytes($memoryLimit];
$recommendedMemory = 128 * 1024 * 1024;// 128MB
$result['recommended'][] = [
    'name' => '内存限制',
    'passed' => $memoryLimitBytes >= $recommendedMemory,
    'message' => '当前设置: ' . $memoryLimit . ' (建议至少 128M]'
];

// 检查是否所有必需条件都通过
foreach ($result['required'] as $check] {
    if (!$check['passed']] {
        $result['success'] = false;
        break;
    }
}

// 将结果以JSON格式返回
echo json_encode($result, JSON_PRETTY_PRINT];

/**
 * 将内存限制字符串转换为字节数
 */
function return_bytes($val] {
    $val = trim($val];
    $last = strtolower($val[strlen($val]-1]];
    $val = (int] $val;
    
    switch($last] {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}

