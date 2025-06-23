<?php
/**
 * AlingAi Pro 生产环境部署准备检查
 */

echo "=== AlingAi Pro 生产环境部署准备检查 ===\n";
echo "检查时间: " . date('Y-m-d H:i:s') . "\n\n";

// 加载环境变量
function loadEnvFile($envFile) {
    if (!file_exists($envFile)) {
        return false;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, ' "\'');
            
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
    return true;
}

// 尝试加载.env文件
$envLoaded = loadEnvFile(__DIR__ . '/.env');

$checkResults = [];

function checkItem($name, $condition, $description = '') {
    global $checkResults;
    $status = $condition ? '✓' : '✗';
    $checkResults[$name] = $condition;
    echo sprintf("%-30s %s %s\n", $name . ':', $status, $description);
    return $condition;
}

echo "1. 系统核心组件检查\n";
echo "===================\n";

// 检查PHP版本
$phpVersion = PHP_VERSION;
checkItem('PHP版本', version_compare($phpVersion, '8.0.0', '>='), "当前: {$phpVersion}");

// 检查必要扩展
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'curl'];
foreach ($requiredExtensions as $ext) {
    checkItem("PHP扩展: {$ext}", extension_loaded($ext));
}

// 检查Composer
$composerExists = file_exists(__DIR__ . '/vendor/autoload.php');
checkItem('Composer依赖', $composerExists);

echo "\n2. 数据库连接检查\n";
echo "================\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    $config = require __DIR__ . '/config/database_local.php';
    
    // 使用生产环境配置进行测试
    $dbConfig = $config['production'];
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset={$dbConfig['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
    
    checkItem('数据库连接', true, '连接成功');
      // 检查数据库是否存在
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbConfig['database']}'");
    $dbExists = $stmt->rowCount() > 0;
    checkItem('数据库存在', $dbExists, $dbConfig['database']);
    
    if ($dbExists) {
        $pdo->exec("USE {$dbConfig['database']}");
        
        // 检查必要的表
        $requiredTables = ['users', 'system_settings', 'chat_conversations', 'chat_messages'];
        foreach ($requiredTables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            $tableExists = $stmt->rowCount() > 0;
            checkItem("数据表: {$table}", $tableExists);
        }
    }
    
} catch (Exception $e) {
    checkItem('数据库连接', false, '连接失败: ' . $e->getMessage());
}

echo "\n3. 文件系统权限检查\n";
echo "==================\n";

$directories = [
    'storage' => __DIR__ . '/storage',
    'storage/logs' => __DIR__ . '/storage/logs',
    'storage/cache' => __DIR__ . '/storage/cache',
    'storage/uploads' => __DIR__ . '/storage/uploads',
    'public/uploads' => __DIR__ . '/public/uploads'
];

foreach ($directories as $name => $path) {
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    checkItem("目录: {$name}", $exists, $writable ? '可写' : ($exists ? '只读' : '不存在'));
}

echo "\n4. 配置文件检查\n";
echo "===============\n";

$configFiles = [
    'app.php' => __DIR__ . '/config/app.php',
    'database.php' => __DIR__ . '/config/database_local.php',
    'routes.php' => __DIR__ . '/config/routes.php'
];

foreach ($configFiles as $name => $path) {
    checkItem("配置: {$name}", file_exists($path));
}

// 检查环境变量
$envVars = ['APP_ENV', 'APP_DEBUG', 'APP_KEY'];
foreach ($envVars as $var) {
    $exists = isset($_ENV[$var]) || getenv($var);
    checkItem("环境变量: {$var}", $exists);
}

echo "\n5. 安全检查\n";
echo "==========\n";

// 检查调试模式
$debugMode = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? true;
checkItem('生产模式', !$debugMode, $debugMode ? '调试模式开启' : '生产模式');

// 检查默认密码
$hasDefaultSecrets = false;
if (file_exists(__DIR__ . '/config/app.php')) {
    $appConfig = require __DIR__ . '/config/app.php';
    $appKey = $appConfig['app']['key'] ?? '';
    $hasDefaultSecrets = empty($appKey) || strpos($appKey, 'your-secret-key-here') !== false;
}
checkItem('应用密钥配置', !$hasDefaultSecrets, $hasDefaultSecrets ? '使用默认密钥' : '已配置');

// 检查敏感文件
$sensitiveFiles = [
    '.env',
    'config/database.php',
    'composer.json'
];

foreach ($sensitiveFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    checkItem("敏感文件: {$file}", $exists, '需要保护');
}

echo "\n6. 服务状态检查\n";
echo "===============\n";

// 检查WebSocket服务
$wsRunning = false;
$socket = @fsockopen('localhost', 8080, $errno, $errstr, 1);
if ($socket) {
    $wsRunning = true;
    fclose($socket);
}
checkItem('WebSocket服务', $wsRunning, $wsRunning ? '端口8080运行中' : '未运行');

// 检查Web服务器
$webRunning = false;
$socket = @fsockopen('localhost', 3000, $errno, $errstr, 1);
if ($socket) {
    $webRunning = true;
    fclose($socket);
}
checkItem('Web服务器', $webRunning, $webRunning ? '端口3000运行中' : '未运行');

echo "\n7. 性能配置检查\n";
echo "===============\n";

// PHP配置检查
$memoryLimit = ini_get('memory_limit');
$maxExecutionTime = ini_get('max_execution_time');
$uploadMaxFilesize = ini_get('upload_max_filesize');

checkItem('内存限制', $memoryLimit !== '-1', "当前: {$memoryLimit}");
checkItem('执行时间限制', is_numeric($maxExecutionTime), "当前: {$maxExecutionTime}秒");
checkItem('上传文件大小限制', !empty($uploadMaxFilesize), "当前: {$uploadMaxFilesize}");

echo "\n=== 部署准备总结 ===\n";

$totalChecks = count($checkResults);
$passedChecks = array_sum($checkResults);
$readinessScore = ($passedChecks / $totalChecks) * 100;

printf("检查项目总数: %d\n", $totalChecks);
printf("通过项目数: %d\n", $passedChecks);
printf("失败项目数: %d\n", $totalChecks - $passedChecks);
printf("部署准备度: %.1f%%\n", $readinessScore);

echo "\n部署建议:\n";

if ($readinessScore >= 95) {
    echo "🚀 系统已准备好生产部署！\n";
    echo "  - 所有核心组件运行正常\n";
    echo "  - 可以立即部署到生产环境\n";
    echo "  - 建议设置监控和日志\n";
} elseif ($readinessScore >= 85) {
    echo "✅ 系统基本准备就绪，有少量问题需要修复\n";
    echo "  - 修复失败的检查项目\n";
    echo "  - 进行最终测试\n";
    echo "  - 然后可以部署\n";
} elseif ($readinessScore >= 70) {
    echo "⚠️ 系统需要进一步配置才能部署\n";
    echo "  - 修复所有失败的检查项目\n";
    echo "  - 完善安全配置\n";
    echo "  - 进行完整测试\n";
} else {
    echo "❌ 系统尚未准备好部署\n";
    echo "  - 存在重大配置问题\n";
    echo "  - 需要完整的系统配置\n";
    echo "  - 不建议部署到生产环境\n";
}

echo "\n关键行动项:\n";

// 识别关键失败项目
$criticalChecks = [
    'PHP版本', 'Composer依赖', '数据库连接', '应用密钥配置'
];

foreach ($criticalChecks as $check) {
    if (!$checkResults[$check]) {
        echo "  🔴 关键问题: {$check} 失败\n";
    }
}

if ($readinessScore >= 85) {
    echo "\n下一步操作:\n";
    echo "  1. 备份数据库和文件\n";
    echo "  2. 配置生产环境服务器\n";
    echo "  3. 设置SSL证书\n";
    echo "  4. 配置域名和DNS\n";
    echo "  5. 设置监控和日志\n";
    echo "  6. 执行生产环境测试\n";
}

echo "\n检查完成时间: " . date('Y-m-d H:i:s') . "\n";
echo "=== 部署准备检查完成 ===\n";
