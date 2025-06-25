<?php
/**
 * Debug Web Server Entry Point
 * 用于调试web服务�?00错误
 * 
 * @version 2.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

declare(strict_types=1];

// IP白名单限�?
$allowedIPs = [
    '127.0.0.1',
    '::1',
    // 添加您的办公室IP或开发团队IP
    '192.168.1.0/24'
];

// 检查IP是否在白名单�?
function isIPAllowed(string $ip, array $allowedIPs): bool {
    foreach ($allowedIPs as $allowedIP) {
        // 检查是否是CIDR格式
        if (strpos($allowedIP, '/') !== false) {
            if (isIPInRange($ip, $allowedIP)) {
                return true;
            }
        } else {
            if ($ip === $allowedIP) {
                return true;
            }
        }
    }
    return false;
}

// 检查IP是否在CIDR范围�?
function isIPInRange(string $ip, string $cidr): bool {
    list($subnet, $mask) = explode('/', $cidr];
    
    $ipLong = ip2long($ip];
    $subnetLong = ip2long($subnet];
    $maskLong = ~((1 << (32 - $mask)) - 1];
    
    return ($ipLong & $maskLong) === ($subnetLong & $maskLong];
}

// 获取客户端IP
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// 如果IP不在白名单中，拒绝访�?
if (!isIPAllowed($clientIP, $allowedIPs)) {
    header('HTTP/1.1 403 Forbidden'];
    echo "Access denied. Your IP ($clientIP) is not allowed to access this page.";
    exit;
}

// 开启详细错误报�?
error_reporting(E_ALL];
ini_set('display_errors', '1'];
ini_set('log_errors', '1'];
ini_set('error_log', __DIR__ . '/../storage/logs/debug.log'];

// 开始计�?
$debugStartTime = microtime(true];

// 性能分析函数
function measurePerformance(string $label, callable $callback) {
    $start = microtime(true];
    $result = $callback(];
    $end = microtime(true];
    $duration = ($end - $start) * 1000; // 转换为毫�?
    
    echo "<div class='performance-block'>";
    echo "<h3>$label</h3>";
    echo "<p>执行时间: <strong>" . number_format($duration, 2) . " ms</strong></p>";
    echo "</div>";
    
    return $result;
}

// 获取内存使用情况
function getMemoryUsage(): string {
    $currentUsage = memory_get_usage(true];
    $peakUsage = memory_get_peak_usage(true];
    
    return "当前: " . formatBytes($currentUsage) . ", 峰�? " . formatBytes($peakUsage];
}

// 格式化字节数
function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0];
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)];
    $pow = min($pow, count($units) - 1];
    
    $bytes /= (1 << (10 * $pow)];
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// 获取PHP扩展列表
function getLoadedExtensions(): array {
    return get_loaded_extensions(];
}

// 获取PHP INI设置
function getImportantIniSettings(): array {
    $settings = [
        'memory_limit',
        'max_execution_time',
        'upload_max_filesize',
        'post_max_size',
        'display_errors',
        'error_reporting',
        'date.timezone',
        'session.gc_maxlifetime',
        'opcache.enable',
        'opcache.memory_consumption'
    ];
    
    $result = [];
    foreach ($settings as $setting) {
        $result[$setting] = ini_get($setting];
    }
    
    return $result;
}

// HTML头部
echo "<!DOCTYPE html>
<html lang='zh-CN'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>AlingAi Pro 调试信息</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background-color: #f5f7fa; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1]; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0; }
        h2 { color: #2980b9; margin-top: 30px; border-left: 4px solid #3498db; padding-left: 10px; }
        h3 { color: #16a085; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
        .warning { color: #f39c12; }
        .performance-block { background: #e8f4f8; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .tab-container { margin-top: 20px; }
        .tab-buttons { overflow: hidden; border: 1px solid #ccc; background-color: #f1f1f1; }
        .tab-buttons button { background-color: inherit; float: left; border: none; outline: none; cursor: pointer; padding: 14px 16px; transition: 0.3s; }
        .tab-buttons button:hover { background-color: #ddd; }
        .tab-buttons button.active { background-color: #3498db; color: white; }
        .tab-content { display: none; padding: 20px; border: 1px solid #ccc; border-top: none; }
        .active-tab { display: block; }
    </style>
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName('tab-content'];
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = 'none';
            }
            tablinks = document.getElementsByClassName('tab-button'];
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(' active', ''];
            }
            document.getElementById(tabName).style.display = 'block';
            evt.currentTarget.className += ' active';
        }
    </script>
</head>
<body>
    <div class='container'>";

// 标题
echo "<h1>AlingAi Pro 调试信息</h1>";
echo "<p>当前时间: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>客户端IP: " . $clientIP . "</p>";

// 选项卡导�?
echo "<div class='tab-container'>
    <div class='tab-buttons'>
        <button class='tab-button active' onclick='openTab(event, \"tab-system\")'>系统信息</button>
        <button class='tab-button' onclick='openTab(event, \"tab-php\")'>PHP环境</button>
        <button class='tab-button' onclick='openTab(event, \"tab-files\")'>文件系统</button>
        <button class='tab-button' onclick='openTab(event, \"tab-database\")'>数据库测�?/button>
        <button class='tab-button' onclick='openTab(event, \"tab-performance\")'>性能分析</button>
        <button class='tab-button' onclick='openTab(event, \"tab-routes\")'>路由测试</button>
    </div>";

// 系统信息选项�?
echo "<div id='tab-system' class='tab-content active-tab'>";
echo "<h2>系统信息</h2>";
echo "<table>
        <tr><th>项目</th><th>�?/th></tr>
        <tr><td>操作系统</td><td>" . PHP_OS . "</td></tr>
        <tr><td>服务器软�?/td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>
        <tr><td>服务器名�?/td><td>" . $_SERVER['SERVER_NAME'] . "</td></tr>
        <tr><td>服务器地址</td><td>" . $_SERVER['SERVER_ADDR'] . "</td></tr>
        <tr><td>服务器端�?/td><td>" . $_SERVER['SERVER_PORT'] . "</td></tr>
        <tr><td>请求方法</td><td>" . $_SERVER['REQUEST_METHOD'] . "</td></tr>
        <tr><td>请求URI</td><td>" . $_SERVER['REQUEST_URI'] . "</td></tr>
        <tr><td>内存使用</td><td>" . getMemoryUsage() . "</td></tr>
        <tr><td>磁盘剩余空间</td><td>" . formatBytes(disk_free_space('/')) . "</td></tr>
        <tr><td>当前用户</td><td>" . get_current_user() . "</td></tr>
      </table>";
echo "</div>";

// PHP环境选项�?
echo "<div id='tab-php' class='tab-content'>";
echo "<h2>PHP环境</h2>";
echo "<table>
        <tr><th>项目</th><th>�?/th></tr>
        <tr><td>PHP版本</td><td>" . PHP_VERSION . "</td></tr>
        <tr><td>Zend引擎版本</td><td>" . zend_version() . "</td></tr>
        <tr><td>SAPI接口</td><td>" . php_sapi_name() . "</td></tr>
      </table>";

echo "<h3>PHP INI设置</h3>";
echo "<table><tr><th>设置</th><th>�?/th></tr>";
foreach (getImportantIniSettings() as $setting => $value) {
    echo "<tr><td>$setting</td><td>$value</td></tr>";
}
echo "</table>";

echo "<h3>已加载扩�?(" . count(getLoadedExtensions()) . ")</h3>";
echo "<pre>" . implode(", ", getLoadedExtensions()) . "</pre>";
echo "</div>";

// 文件系统选项�?
echo "<div id='tab-files' class='tab-content'>";
echo "<h2>文件系统检�?/h2>";

$basePath = dirname(__DIR__];
echo "<p>基础路径: $basePath</p>";

// 检查关键文件和目录
$filesToCheck = [
    '/vendor/autoload.php' => '自动加载�?,
    '/.env' => '环境配置文件',
    '/src/Core/AlingAiProApplication.php' => '核心应用文件',
    '/storage/logs' => '日志目录',
    '/storage/cache' => '缓存目录',
    '/public/index.php' => '公共入口文件',
    '/public/index.html' => '主页HTML文件',
    '/public/.htaccess' => 'Apache配置文件'
];

echo "<table>
        <tr><th>文件/目录</th><th>状�?/th><th>权限</th><th>大小</th><th>修改时间</th></tr>";

foreach ($filesToCheck as $file => $description) {
    $fullPath = $basePath . $file;
    $exists = file_exists($fullPath];
    $status = $exists ? '<span class="success">�?存在</span>' : '<span class="error">�?不存�?/span>';
    
    $perms = $exists ? substr(sprintf('%o', fileperms($fullPath)], -4) : 'N/A';
    $size = $exists ? (is_dir($fullPath) ? '目录' : formatBytes(filesize($fullPath))) : 'N/A';
    $mtime = $exists ? date('Y-m-d H:i:s', filemtime($fullPath)) : 'N/A';
    
    echo "<tr>
            <td>$description ($file)</td>
            <td>$status</td>
            <td>$perms</td>
            <td>$size</td>
            <td>$mtime</td>
          </tr>";
}

echo "</table>";
echo "</div>";

// 数据库测试选项�?
echo "<div id='tab-database' class='tab-content'>";
echo "<h2>数据库连接测�?/h2>";

try {
    require_once $basePath . '/vendor/autoload.php';
    
    // 加载环境变量
    if (file_exists($basePath . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable($basePath];
        $dotenv->load(];
    }
    
    // 测试数据库连�?
    echo "<h3>数据库配�?/h3>";
    echo "<table>
            <tr><th>配置�?/th><th>�?/th></tr>
            <tr><td>DB_HOST</td><td>" . (getenv('DB_HOST') ?: '<span class="error">未设�?/span>') . "</td></tr>
            <tr><td>DB_NAME</td><td>" . (getenv('DB_NAME') ?: '<span class="error">未设�?/span>') . "</td></tr>
            <tr><td>DB_USER</td><td>" . (getenv('DB_USER') ?: '<span class="error">未设�?/span>') . "</td></tr>
            <tr><td>DB_PASSWORD</td><td>" . (getenv('DB_PASSWORD') ? '<span class="success">已设�?/span>' : '<span class="error">未设�?/span>') . "</td></tr>
          </table>";
    
    // 尝试连接数据�?
    if (class_exists('\AlingAi\Services\DatabaseService')) {
        echo "<h3>数据库连接测�?/h3>";
        $result = measurePerformance('数据库连�?, function() {
            try {
                $db = new \AlingAi\Services\DatabaseService(];
                $connection = $db->getConnection(];
                echo "<p class='success'>�?数据库连接成�?/p>";
                return true;
            } catch (Exception $e) {
                echo "<p class='error'>�?数据库连接失�? " . $e->getMessage() . "</p>";
                return false;
            }
        }];
        
        // 如果连接成功，尝试执行简单查�?
        if ($result) {
            measurePerformance('简单查询测�?, function() use ($db) {
                try {
                    $query = "SELECT 1 as test";
                    $stmt = $db->getConnection()->query($query];
                    $result = $stmt->fetch(];
                    echo "<p class='success'>�?查询测试成功: " . json_encode($result) . "</p>";
                    return true;
                } catch (Exception $e) {
                    echo "<p class='error'>�?查询测试失败: " . $e->getMessage() . "</p>";
                    return false;
                }
            }];
        }
    } else {
        echo "<p class='warning'>! DatabaseService类不存在，无法测试数据库连接</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>�?测试过程中发生错�? " . $e->getMessage() . "</p>";
}

echo "</div>";

// 性能分析选项�?
echo "<div id='tab-performance' class='tab-content'>";
echo "<h2>性能分析</h2>";

// 测试文件I/O性能
measurePerformance('文件I/O测试', function() {
    $testFile = dirname(__DIR__) . '/storage/cache/perf_test.txt';
    $content = str_repeat('A', 1024 * 1024]; // 1MB数据
    
    // 写入测试
    $start = microtime(true];
    file_put_contents($testFile, $content];
    $writeTime = microtime(true) - $start;
    
    // 读取测试
    $start = microtime(true];
    $data = file_get_contents($testFile];
    $readTime = microtime(true) - $start;
    
    // 删除测试文件
    @unlink($testFile];
    
    echo "<p>写入1MB数据: " . number_format($writeTime * 1000, 2) . " ms</p>";
    echo "<p>读取1MB数据: " . number_format($readTime * 1000, 2) . " ms</p>";
    
    return true;
}];

// 测试CPU性能
measurePerformance('CPU性能测试', function() {
    $start = microtime(true];
    $result = 0;
    
    // 执行一些CPU密集型操�?
    for ($i = 0; $i < 1000000; $i++) {
        $result += sin($i) * cos($i];
    }
    
    $end = microtime(true];
    echo "<p>执行100万次三角函数计算</p>";
    
    return true;
}];

// 测试内存分配性能
measurePerformance('内存分配测试', function() {
    $start = microtime(true];
    
    // 分配和释放大量小对象
    for ($i = 0; $i < 10000; $i++) {
        $obj = new stdClass(];
        $obj->data = str_repeat('A', 100];
        unset($obj];
    }
    
    $end = microtime(true];
    echo "<p>分配和释�?0000个小对象</p>";
    
    return true;
}];

echo "</div>";

// 路由测试选项�?
echo "<div id='tab-routes' class='tab-content'>";
echo "<h2>路由测试</h2>";

if (isset($_GET['route'])) {
    echo "<p>测试路由: " . htmlspecialchars($_GET['route']) . "</p>";
    
    // 模拟不同的路由测�?
    switch ($_GET['route']) {
        case 'api':
            echo "<pre>" . json_encode(['status' => 'ok', 'time' => time(), 'message' => 'API测试成功'],  JSON_PRETTY_PRINT) . "</pre>";
            break;
        case 'database':
            try {
                $db = new \AlingAi\Services\DatabaseService(];
                echo "<p class='success'>�?数据库服务创建成�?/p>";
                $connection = $db->getConnection(];
                echo "<p class='success'>�?数据库连�? 成功</p>";
            } catch (Exception $e) {
                echo "<p class='error'>�?数据库测试失�? " . $e->getMessage() . "</p>";
            }
            break;
        case 'session':
            session_start(];
            $_SESSION['test'] = time(];
            echo "<p>会话ID: " . session_id() . "</p>";
            echo "<p>会话数据: " . json_encode($_SESSION) . "</p>";
            break;
        case 'error':
            // 故意触发错误以测试错误处�?
            trigger_error("这是一个测试错�?, E_USER_WARNING];
            echo "<p>检查日志以查看错误记录</p>";
            break;
        default:
            echo "<p>可用路由: ?route=api, ?route=database, ?route=session, ?route=error</p>";
    }
}

echo "<h3>快速路由测�?/h3>";
echo "<p>
        <a href='?route=api' class='button'>测试API</a> | 
        <a href='?route=database' class='button'>测试数据�?/a> | 
        <a href='?route=session' class='button'>测试会话</a> | 
        <a href='?route=error' class='button'>测试错误处理</a>
      </p>";

echo "</div>";

// 关闭选项卡容�?
echo "</div>";

// 底部链接
echo "<h2>快速链�?/h2>";
echo "<p><a href='/'>返回主页</a> | <a href='/phpinfo_debug.php'>查看PHP信息</a></p>";

// 计算总执行时�?
$debugEndTime = microtime(true];
$totalExecutionTime = ($debugEndTime - $debugStartTime) * 1000;

echo "<p>调试页面生成时间: " . number_format($totalExecutionTime, 2) . " ms</p>";

// 关闭容器和HTML
echo "</div>
</body>
</html>";

