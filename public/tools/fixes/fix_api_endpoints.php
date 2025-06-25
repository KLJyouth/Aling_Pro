<?php
/**
 * API端点修复脚本
 * 
 * 此脚本用于修复AlingAi Pro系统中的API端点问题
 */

// 定义应用程序根目录
define("APP_ROOT", __DIR__];

// 输出标题
echo "=================================================\n";
echo "AlingAi Pro API端点修复工具\n";
echo "=================================================\n\n";

// 1. 创建测试API端点
echo "1. 创建测试API端点...\n";

// 在public目录下创建测试API端点
$testApiDir = APP_ROOT . "/public/api";
if (!is_dir($testApiDir)) {
    mkdir($testApiDir, 0755, true];
    echo "   - 创建测试API目录: {$testApiDir}\n";
}

// 创建v1/system/info.php
$systemInfoDir = $testApiDir . "/v1/system";
if (!is_dir($systemInfoDir)) {
    mkdir($systemInfoDir, 0755, true];
}

$systemInfoFile = $systemInfoDir . "/info.php";
$systemInfoContent = "<?php
header(\"Content-Type: application/json\"];

echo json_encode([
    \"version\" => \"1.0\",
    \"system\" => \"AlingAi Pro\",
    \"api_version\" => \"v1\",
    \"timestamp\" => date(\"Y-m-d H:i:s\"],
    \"features\" => [
        \"security_scanning\" => true,
        \"threat_visualization\" => true,
        \"database_management\" => true,
        \"cache_optimization\" => true
    ]
]];";

file_put_contents($systemInfoFile, $systemInfoContent];
echo "   - 创建测试API端点: /api/v1/system/info\n";

// 创建info.php
$infoFile = $testApiDir . "/info.php";
$infoContent = "<?php
header(\"Content-Type: application/json\"];

echo json_encode([
    \"message\" => \"AlingAi Pro API\",
    \"current_version\" => \"v2\",
    \"available_versions\" => [\"v1\", \"v2\"], 
    \"endpoints\" => [
        \"/api/v1/system/info\" => \"System information (v1)\",
        \"/api/v2/enhanced/dashboard\" => \"Enhanced dashboard (v2)\",
        \"/api/v1/security/overview\" => \"Security overview\",
        \"/api/v2/ai/agents\" => \"AI agents management\"
    ]
]];";

file_put_contents($infoFile, $infoContent];
echo "   - 创建测试API端点: /api/info\n";

// 2. 创建.htaccess文件以处理API请求
echo "\n2. 创建.htaccess文件以处理API请求...\n";

$htaccessFile = APP_ROOT . "/public/.htaccess";
$htaccessContent = "<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # 如果请求的是实际文件或目录，则直接访问
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # 处理API请求
    RewriteRule ^api/v1/system/info/?$ api/v1/system/info.php [L]
    RewriteRule ^api/info/?$ api/info.php [L]

    # 其他请求转发到index.php
    RewriteRule ^ index.php [L]
</IfModule>";

file_put_contents($htaccessFile, $htaccessContent];
echo "   - 创建.htaccess文件\n";

echo "\n=================================================\n";
echo "修复完成!\n";
echo "请运行以下命令启动服务器并测试API端点:\n";
echo "php -c php.ini.local -S localhost:8000 -t public\n";
echo "=================================================\n";
