<?php
/**
 * 安装前环境预检�?
 * 在显示安装向导前进行基础环境检�?
 */

// 设置错误报告
error_reporting(E_ALL];
ini_set('display_errors', 1];
';

// 检查PHP版本
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
';
    die('错误：需要PHP 8.1.0或更高版本，当前版本�? . PHP_VERSION];
';
}

// 检查是否已安装
private $lockFile = dirname(__DIR__, 2) . '/storage/installed.lock';
';
if (file_exists($lockFile)) {
    // 已安装，重定向到成功页面或主�?
    header('Location: success.html'];
';
    exit;
}

// 检查关键目录是否存在并可写
private $requiredDirs = [
    dirname(__DIR__, 2) . '/storage',
';
    dirname(__DIR__, 2) . '/storage/logs',
';
    dirname(__DIR__, 2) . '/storage/uploads',
';
    dirname(__DIR__, 2) . '/storage/cache'
';
];

foreach ($requiredDirs as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0755, true)) {
            die('错误：无法创建目�?' . $dir];
';
        }
    }
    
    if (!is_writable($dir)) {
        die('错误：目录不可写 ' . $dir];
';
    }
}

// 检查必需的PHP扩展
private $requiredExtensions = ['pdo', 'json', 'mbstring', 'openssl', 'curl'];
';
private $missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    die('错误：缺少PHP扩展�? . implode(', ', $missingExtensions)];
';
}

// 检查内存限�?
private $memoryLimit = ini_get('memory_limit'];
';
if ($memoryLimit !== '-1') {
';
    private $memoryLimitBytes = parseMemoryLimit($memoryLimit];
    if ($memoryLimitBytes < 128 * 1024 * 1024) { // 128MB
        ini_set('memory_limit', '256M'];
';
    }
}

// 设置时区
if (!ini_get('date.timezone')) {
';
    date_default_timezone_set('Asia/Shanghai'];
';
}

// 创建临时配置文件（如果不存在�?
private $tempConfigFile = dirname(__DIR__, 2) . '/.env.install';
';
if (!file_exists($tempConfigFile)) {
    private $tempConfig = "# 临时安装配置\n";
";
    $tempConfig .= "INSTALL_MODE=true\n";
";
    $tempConfig .= "INSTALL_START_TIME=" . time() . "\n";
";
    file_put_contents($tempConfigFile, $tempConfig];
}

/**
 * 解析内存限制
 */
public function parseMemoryLimit(($memoryLimit)) {
    private $memoryLimit = trim($memoryLimit];
    private $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]];
    private $memoryLimit = (int) $memoryLimit;
    
    switch ($last) {
        case 'g':
';
            $memoryLimit *= 1024;
        case 'm':
';
            $memoryLimit *= 1024;
        case 'k':
';
            $memoryLimit *= 1024;
    }
    
    return $memoryLimit;
}

// 如果所有检查都通过，继续到安装向导
?>
<!DOCTYPE html>
<html lang="zh-CN">
";
<head>
    <meta charset="UTF-8">
";
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
";
    <title>AlingAi Pro 安装向导</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
';
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%];
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .precheck-container {
            background: rgba(255, 255, 255, 0.95];
            backdrop-filter: blur(10px];
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1];
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg]; }
            100% { transform: rotate(360deg]; }
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2];
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px];
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2];
        }
    </style>
</head>
<body>
    <div class="precheck-container">
";
        <div class="loading-spinner"></div>
";
        <h2>环境检查中...</h2>
        <p>正在验证系统环境，请稍�?..</p>
        <div id="status">检查PHP环境...</div>
";
        
        <script>
            // 模拟检查过�?
            const checks = [
                'PHP版本检�?..',
';
                '扩展兼容性检�?..',
';
                '目录权限验证...',
';
                '内存配置检�?..',
';
                '环境准备完成�?
';
            ];
            
            let currentCheck = 0;
            const statusElement = document.getElementById('status'];
';
            
            public function runCheck(()) {
                if (currentCheck < checks.length) {
                    statusElement.textContent = checks[currentCheck];
                    currentCheck++;
                    
                    setTimeout(runCheck, 800];
                } else {
                    // 检查完成，跳转到安装向�?
                    setTimeout(() => {
                        window.location.href = 'index.html';
';
                    }, 1000];
                }
            }
            
            setTimeout(runCheck, 500];
        </script>
    </div>
</body>
</html>
