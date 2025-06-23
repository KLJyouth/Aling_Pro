<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP服务器诊断信息</h1>";
echo "<h2>基本信息</h2>";
echo "PHP版本: " . phpversion() . "<br>";
echo "当前工作目录: " . getcwd() . "<br>";
echo "脚本所在目录: " . __DIR__ . "<br>";
echo "服务器时间: " . date('Y-m-d H:i:s') . "<br>";

echo "<h2>请求信息</h2>";
echo "请求URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "请求方法: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "服务器名称: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "服务器端口: " . $_SERVER['SERVER_PORT'] . "<br>";

echo "<h2>目录结构检查</h2>";
$currentDir = __DIR__;
echo "<strong>当前目录 ($currentDir) 内容:</strong><br>";
$files = scandir($currentDir);
foreach($files as $file) {
    if($file != '.' && $file != '..') {
        $fullPath = $currentDir . DIRECTORY_SEPARATOR . $file;
        $type = is_dir($fullPath) ? '[目录]' : '[文件]';
        $size = is_file($fullPath) ? ' (' . filesize($fullPath) . ' bytes)' : '';
        echo "&nbsp;&nbsp;$type, $file$size<br>";
    }
}

echo "<h2>Admin目录检查</h2>";
$adminDir = $currentDir . DIRECTORY_SEPARATOR . 'admin';
if (is_dir($adminDir)) {
    echo "<strong>Admin目录 ($adminDir) 内容:</strong><br>";
    $adminFiles = scandir($adminDir);
    foreach($adminFiles as $file) {
        if($file != '.' && $file != '..') {
            $fullPath = $adminDir . DIRECTORY_SEPARATOR . $file;
            $type = is_dir($fullPath) ? '[目录]' : '[文件]';
            $size = is_file($fullPath) ? ' (' . filesize($fullPath) . ' bytes)' : '';
            $readable = is_readable($fullPath) ? '[可读]' : '[不可读]';
            echo "&nbsp;&nbsp;$type, $file$size, $readable<br>";
        }
    }
} else {
    echo "❌ Admin目录不存在！<br>";
}

echo "<h2>关键文件检查</h2>";
$keyFiles = [
    'index.html',
    'admin/tools_manager.php',
    'admin/login.php',
    'router.php'
];

foreach($keyFiles as $file) {
    $fullPath = $currentDir . DIRECTORY_SEPARATOR . $file;
    if (file_exists($fullPath)) {
        $readable = is_readable($fullPath) ? '✅' : '❌';
        $size = filesize($fullPath);
        echo "$readable, $file (存在, $size bytes)<br>";
    } else {
        echo "❌ $file (不存在)<br>";
    }
}

echo "<h2>PHP配置</h2>";
echo "错误报告级别: " . error_reporting() . "<br>";
echo "显示错误: " . (ini_get('display_errors') ? '是' : '否') . "<br>";
echo "内存限制: " . ini_get('memory_limit') . "<br>";
echo "执行时间限制: " . ini_get('max_execution_time') . "<br>";

echo "<h2>环境变量</h2>";
foreach($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0 || in_array($key, ['REQUEST_URI', 'REQUEST_METHOD', 'SCRIPT_NAME', 'QUERY_STRING'])) {
        echo "$key: $value<br>";
    }
}
?>
