<?php
/**
 * AlingAi Pro 系统启动脚本
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/EnvLoader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlingAi\Services\DatabaseService;
use AlingAi\Utils\EnvLoader;

// 加载环境变量
EnvLoader::load(];

echo "=== AlingAi Pro 系统启动 ===\n\n";

function askYesNo($question) {
    echo $question . " (y/n): ";
    $handle = fopen("php://stdin", "r"];
    $line = fgets($handle];
    fclose($handle];
    return trim(strtolower($line)) === 'y';
}

function showMenu() {
    echo "\n选择启动模式:\n";
    echo "1. 快速启�?(使用默认配置)\n";
    echo "2. 完整检查后启动\n";
    echo "3. 仅检查系统状态\n";
    echo "4. 数据库管理\n";
    echo "5. 查看系统信息\n";
    echo "6. 退出\n";
    echo "请选择 (1-6): ";
}

try {
    // 显示菜单
    showMenu(];
    $handle = fopen("php://stdin", "r"];
    $choice = trim(fgets($handle)];
    fclose($handle];
    
    switch ($choice) {
        case '1':
            echo "\n=== 快速启动模�?===\n";
            break;
            
        case '2':
            echo "\n=== 完整检查启动模�?===\n";
            echo "运行系统状态检�?..\n";
            include __DIR__ . '/system_status_check.php';
            
            if (!askYesNo("\n继续启动服务�?")) {
                echo "启动已取消。\n";
                exit(0];
            }
            break;
            
        case '3':
            echo "\n=== 系统状态检�?===\n";
            include __DIR__ . '/system_status_check.php';
            exit(0];
            
        case '4':
            echo "\n=== 数据库管�?===\n";
            include __DIR__ . '/database_management.php';
            exit(0];
            
        case '5':
            echo "\n=== 系统信息 ===\n";
            showSystemInfo(];
            exit(0];
            
        case '6':
            echo "再见！\n";
            exit(0];
            
        default:
            echo "无效选择，使用快速启动模式。\n";
            break;
    }
    
    // 1. 检查系统就绪状�?
    echo "\n1. 检查系统就绪状�?..\n";
    
    $logger = new Logger('startup'];
    $logger->pushHandler(new StreamHandler(__DIR__ . '/storage/logs/startup.log')];
      // 检查数据库连接
    $dbService = new DatabaseService($logger];
    echo "�?数据库连接成�?(类型: " . $dbService->getConnectionType() . ")\n";
    
    // 2. 初始化环�?
    echo "\n2. 初始化运行环�?..\n";
    
    $host = $_ENV['APP_HOST'] ?? '127.0.0.1';
    $port = $_ENV['APP_PORT'] ?? 3000;
    $publicDir = __DIR__ . '/public';
    
    echo "服务器配�?\n";
    echo "  主机: {$host}\n";
    echo "  端口: {$port}\n";
    echo "  文档根目�? {$publicDir}\n";
    echo "  访问地址: http://{$host}:{$port}\n\n";
    
    // 检查端口是否可�?
    $socket = @fsockopen($host, $port, $errno, $errstr, 1];
    if ($socket) {
        fclose($socket];
        echo "�?警告: 端口 {$port} 已被占用\n";
        echo "请检查是否有其他服务正在运行，或修改 APP_PORT 配置\n\n";
    }
    
    // 3. 构建系统配置
    $config = [
        'app' => [
            'name' => $_ENV['APP_NAME'] ?? 'AlingAi Pro',
            'env' => $_ENV['APP_ENV'] ?? 'development',
            'debug' => ($_ENV['APP_DEBUG'] ?? 'true') === 'true',
            'url' => $_ENV['APP_URL'] ?? "http://{$host}:{$port}"
        ], 
        'database' => [
            'connection' => $dbService->getConnectionType()
        ], 
        'ai_services' => [], 
        'features' => [
            'email_notifications' => !empty($_ENV['MAIL_HOST'],
            'system_monitoring' => true
        ]
    ];
    
    // 检查AI服务配置
    if (!empty($_ENV['DEEPSEEK_API_KEY')) {
        $config['ai_services']['deepseek'] = [
            'model' => $_ENV['DEEPSEEK_MODEL'] ?? 'deepseek-chat'
        ];
    }
    
    if (!empty($_ENV['BAIDU_AI_API_KEY')) {
        $config['ai_services']['baidu'] = [
            'model' => 'ERNIE-Bot'
        ];
    }
    
    // 4. 显示系统信息
    echo "3. 系统信息:\n";
    echo "应用名称: {$config['app']['name']}\n";
    echo "运行环境: {$config['app']['env']}\n";
    echo "调试模式: " . ($config['app']['debug'] ? '启用' : '禁用') . "\n";
    echo "数据�? {$config['database']['connection']}\n";
    echo "AI服务: " . count($config['ai_services') . " 个可用\n";
    echo "邮件服务: " . ($config['features']['email_notifications'] ? '已配�? : '未配�?) . "\n";
    echo "监控服务: " . ($config['features']['system_monitoring'] ? '启用' : '禁用') . "\n\n";
    
    // 5. 显示可用的AI模型
    if (!empty($config['ai_services')) {
        echo "4. 可用的AI模型:\n";
        foreach ($config['ai_services'] as $provider => $serviceConfig) {
            echo "  - {$provider}: {$serviceConfig['model']}\n";
        }
        echo "\n";
    }
      // 6. 启动选项
    echo "5. 启动选项:\n";
    echo "  [1] 使用PHP内置服务器启�?(开发模�?\n";
    echo "  [2] 显示Nginx配置 (生产模式)\n";
    echo "  [3] 运行系统检查\n";
    echo "  [4] 查看日志\n";
    echo "  [0] 退出\n\n";
    
    while (true) {
        echo "请选择一个选项 [1-4, 0): ";
        $choice = trim(fgets(STDIN)];
        
        switch ($choice) {
            case '1':
                echo "\n正在启动PHP内置服务�?..\n";
                echo "�?Ctrl+C 停止服务器\n\n";
                
                // 启动PHP内置服务�?
                $command = "php -S {$host}:{$port} -t {$publicDir}";
                echo "执行命令: {$command}\n\n";
                passthru($command];
                break 2;
                
            case '2':
                echo "\nNginx配置示例:\n";
                echo "server {\n";
                echo "    listen 80;\n";
                echo "    server_name your-domain.com;\n";
                echo "    root {$publicDir};\n";
                echo "    index index.php index.html;\n\n";
                echo "    location / {\n";
                echo "        try_files \$uri \$uri/ /index.php?\$query_string;\n";
                echo "    }\n\n";
                echo "    location ~ \\.php\$ {\n";
                echo "        fastcgi_pass 127.0.0.1:9000;\n";
                echo "        fastcgi_index index.php;\n";
                echo "        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n";
                echo "        include fastcgi_params;\n";
                echo "    }\n";
                echo "}\n\n";
                break;
                
            case '3':
                echo "\n正在运行系统检�?..\n";
                include __DIR__ . '/test_enhanced_system.php';
                echo "\n";
                break;
                
            case '4':
                echo "\n最近的日志文件:\n";
                $logDir = __DIR__ . '/storage/logs';
                $logFiles = glob($logDir . '/*.log'];
                rsort($logFiles];
                
                foreach (array_slice($logFiles, 0, 5) as $logFile) {
                    $fileName = basename($logFile];
                    $fileSize = round(filesize($logFile) / 1024, 2];
                    $fileTime = date('Y-m-d H:i:s', filemtime($logFile)];
                    echo "  {$fileName} ({$fileSize}KB, {$fileTime})\n";
                }
                
                if (!empty($logFiles)) {
                    echo "\n查看最新日志内容？(y/n): ";
                    $viewLog = trim(fgets(STDIN)];
                    if (strtolower($viewLog) === 'y') {
                        echo "\n--- " . basename($logFiles[0) . " ---\n";
                        echo tail($logFiles[0],  20];
                        echo "--- 日志结束 ---\n\n";
                    }
                }
                break;
                
            case '0':
                echo "\n感谢使用 AlingAi Pro！\n";
                exit(0];
                
            default:
                echo "无效选择，请重新输入。\n";
        }
    }
    
} catch (Exception $e) {
    echo "�?启动失败: " . $e->getMessage() . "\n";
    echo "请检查系统配置和依赖项\n";
}

/**
 * 获取文件末尾几行内容
 */
function tail($file, $lines = 10) {
    $handle = fopen($file, "r"];
    $linecounter = 0;
    $pos = -2;
    $beginning = false;
    $text = [];
    
    while ($linecounter < $lines) {
        $t = " ";
        while ($t != "\n") {
            if (fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true;
                break;
            }
            $t = fgetc($handle];
            $pos--;
        }
        $linecounter++;
        if ($beginning) {
            rewind($handle];
        }
        $text[$lines - $linecounter] = fgets($handle];
        if ($beginning) break;
    }
    fclose($handle];
    return implode("", array_reverse($text)];
}

function showSystemInfo() {
    echo "=== AlingAi Pro 系统信息 ===\n\n";
    
    // PHP 信息
    echo "PHP 版本: " . PHP_VERSION . "\n";
    echo "内存限制: " . ini_get('memory_limit') . "\n";
    echo "执行时间限制: " . ini_get('max_execution_time') . "s\n";
    
    // 环境信息
    echo "\n环境配置:\n";
    echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? '未设�?) . "\n";
    echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? '未设�?) . "\n";
    echo "APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? '未设�?) . "\n";
    
    // 扩展检�?
    echo "\n已安装的PHP扩展:\n";
    $requiredExts = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring', 'openssl'];
    foreach ($requiredExts as $ext) {
        $status = extension_loaded($ext) ? '�? : '�?;
        echo "  {$status} {$ext}\n";
    }
    
    // 磁盘空间
    echo "\n磁盘使用情况:\n";
    $totalSpace = disk_total_space('.'];
    $freeSpace = disk_free_space('.'];
    $usedSpace = $totalSpace - $freeSpace;
    
    echo "  总空�? " . formatBytes($totalSpace) . "\n";
    echo "  已使�? " . formatBytes($usedSpace) . "\n";
    echo "  可用空间: " . formatBytes($freeSpace) . "\n";
}

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

function createDatabaseManagement() {
    $content = '<?php
/**
 * 数据库管理脚�?
 */

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/src/Utils/EnvLoader.php";

use AlingAi\Services\DatabaseService;
use AlingAi\Utils\EnvLoader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

EnvLoader::load(];

echo "=== 数据库管�?===\n\n";

$logger = new Logger("db_mgmt"];
$logger->pushHandler(new StreamHandler(__DIR__ . "/storage/logs/db_mgmt.log")];

try {
    $dbService = new DatabaseService($logger];
    
    echo "选择操作:\n";
    echo "1. 查看数据库状态\n";
    echo "2. 备份数据库\n";
    echo "3. 清理日志表\n";
    echo "4. 重建索引\n";
    echo "5. 返回主菜单\n";
    echo "请选择 (1-5): ";
    
    $handle = fopen("php://stdin", "r"];
    $choice = trim(fgets($handle)];
    fclose($handle];
    
    switch ($choice) {
        case "1":
            $stats = $dbService->getStats(];
            echo "\n数据库统计信�?\n";
            print_r($stats];
            break;
            
        case "2":
            echo "\n开始备份数据库...\n";
            // 备份逻辑
            echo "备份功能暂未实现\n";
            break;
            
        case "3":
            echo "\n清理日志�?..\n";
            // 清理逻辑
            echo "清理功能暂未实现\n";
            break;
            
        case "4":
            echo "\n重建索引...\n";
            // 索引重建逻辑
            echo "索引重建功能暂未实现\n";
            break;
            
        default:
            echo "返回主菜单\n";
            break;
    }
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}';
    
    file_put_contents(__DIR__ . '/database_management.php', $content];
}

