<?php
// 测试PSR接口是否可用

// 启用错误显示
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "PSR接口测试<br>";

// 检查是否已安装react/promise
if (file_exists(__DIR__ . '/../vendor/react/promise/src/functions_include.php')) {
    echo "react/promise 已安装<br>";
} else {
    echo "react/promise 未安装<br>";
}

// 检查PSR接口
if (interface_exists('Psr\Http\Server\RequestHandlerInterface')) {
    echo "Psr\Http\Server\RequestHandlerInterface 接口存在<br>";
} else {
    echo "Psr\Http\Server\RequestHandlerInterface 接口不存在<br>";
}

// 检查composer自动加载
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "composer自动加载文件存在<br>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "已加载composer自动加载文件<br>";
    
    // 再次检查PSR接口
    if (interface_exists('Psr\Http\Server\RequestHandlerInterface')) {
        echo "加载自动加载文件后，Psr\Http\Server\RequestHandlerInterface 接口存在<br>";
    } else {
        echo "加载自动加载文件后，Psr\Http\Server\RequestHandlerInterface 接口仍不存在<br>";
    }
} else {
    echo "composer自动加载文件不存在<br>";
}

// 显示已加载的扩展
echo "<br>已加载的扩展：<br>";
$extensions = get_loaded_extensions();
echo implode(", ", $extensions);
?> 