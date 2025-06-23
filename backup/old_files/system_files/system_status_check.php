<?php
/**
 * AlingAi Pro 系统完整状态检查
 */

require_once 'vendor/autoload.php';
require_once 'src/Utils/EnvLoader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlingAi\Services\DatabaseService;
use AlingAi\Utils\EnvLoader;

// 加载环境变量
EnvLoader::load(__DIR__ . '/.env');

echo "=== AlingAi Pro 系统状态检查 ===\n\n";

// 创建日志记录器
$logger = new Logger('system_check');
$logger->pushHandler(new StreamHandler(__DIR__ . '/storage/logs/system_check.log'));

$systemStatus = [
    'database' => false,
    'ai_services' => false,
    'mail_service' => false,
    'storage' => false,
    'configuration' => false
];

// 1. 数据库连接检查
echo "1. 检查数据库连接...\n";
try {
    $dbService = new DatabaseService($logger);
    $connectionType = $dbService->getConnectionType();
    echo "✓ 数据库连接成功 (类型: {$connectionType})\n";
    
    // 测试基本操作
    $stats = $dbService->getStats();
    if (!empty($stats)) {
        echo "✓ 数据库统计信息获取成功\n";
        if (isset($stats['table_count'])) {
            echo "  - 表数量: {$stats['table_count']}\n";
        }
        if (isset($stats['database_size_mb'])) {
            echo "  - 数据库大小: {$stats['database_size_mb']} MB\n";
        }
    }
    
    // 测试系统设置表
    $settings = $dbService->findAll('system_settings', [], ['limit' => 5]);
    echo "✓ 系统设置表访问成功，找到 " . count($settings) . " 条记录\n";
    
    $systemStatus['database'] = true;
} catch (Exception $e) {
    echo "✗ 数据库连接失败: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. AI 服务配置检查
echo "2. 检查 AI 服务配置...\n";
$aiServices = [
    'DEEPSEEK_API_KEY' => 'DeepSeek API',
    'BAIDU_AI_API_KEY' => 'Baidu AI API',
    'BAIDU_AI_SECRET_KEY' => 'Baidu AI Secret'
];

$aiConfigured = 0;
foreach ($aiServices as $key => $name) {
    $value = $_ENV[$key] ?? getenv($key);
    if (!empty($value)) {
        echo "✓ {$name}: 已配置\n";
        $aiConfigured++;
    } else {
        echo "✗ {$name}: 未配置\n";
    }
}

if ($aiConfigured > 0) {
    echo "✓ AI 服务配置完成 ({$aiConfigured}/{" . count($aiServices) . "})\n";
    $systemStatus['ai_services'] = true;
} else {
    echo "✗ 没有配置任何 AI 服务\n";
}

echo "\n";

// 3. 邮件服务检查
echo "3. 检查邮件服务配置...\n";
$mailConfig = [
    'MAIL_DRIVER' => 'smtp',
    'MAIL_HOST' => $_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST'),
    'MAIL_PORT' => $_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT'),
    'MAIL_USERNAME' => $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME'),
    'MAIL_FROM_ADDRESS' => $_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS')
];

$mailConfigured = true;
foreach ($mailConfig as $key => $value) {
    if (empty($value)) {
        $mailConfigured = false;
        break;
    }
}

if ($mailConfigured) {
    echo "✓ 邮件服务配置完成\n";
    echo "  - SMTP 主机: {$mailConfig['MAIL_HOST']}\n";
    echo "  - SMTP 端口: {$mailConfig['MAIL_PORT']}\n";
    echo "  - 发送邮箱: {$mailConfig['MAIL_FROM_ADDRESS']}\n";
    $systemStatus['mail_service'] = true;
} else {
    echo "✗ 邮件服务配置不完整\n";
}

echo "\n";

// 4. 存储目录检查
echo "4. 检查存储目录...\n";
$requiredDirs = [
    'storage',
    'storage/data',
    'storage/logs',
    'storage/cache',
    'storage/uploads',
    'public/uploads'
];

$storageOk = true;
foreach ($requiredDirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "✓ {$dir}: 存在且可写\n";
    } else {
        echo "✗ {$dir}: 不存在或不可写\n";
        $storageOk = false;
    }
}

if ($storageOk) {
    $systemStatus['storage'] = true;
}

echo "\n";

// 5. 应用配置检查
echo "5. 检查应用配置...\n";
$appConfig = [
    'APP_NAME' => $_ENV['APP_NAME'] ?? getenv('APP_NAME'),
    'APP_ENV' => $_ENV['APP_ENV'] ?? getenv('APP_ENV'),
    'APP_DEBUG' => $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG'),
    'APP_KEY' => $_ENV['APP_KEY'] ?? getenv('APP_KEY'),
    'APP_URL' => $_ENV['APP_URL'] ?? getenv('APP_URL')
];

$configOk = true;
foreach ($appConfig as $key => $value) {
    if (!empty($value)) {
        echo "✓ {$key}: {$value}\n";
    } else {
        echo "✗ {$key}: 未设置\n";
        $configOk = false;
    }
}

if ($configOk) {
    $systemStatus['configuration'] = true;
}

echo "\n";

// 6. 系统整体状态评估
echo "=== 系统状态总结 ===\n";
$totalChecks = count($systemStatus);
$passedChecks = array_sum($systemStatus);

foreach ($systemStatus as $component => $status) {
    $statusText = $status ? '✓ 正常' : '✗ 异常';
    echo "{$component}: {$statusText}\n";
}

echo "\n";
echo "系统就绪度: {$passedChecks}/{$totalChecks} (" . round(($passedChecks / $totalChecks) * 100) . "%)\n";

if ($passedChecks === $totalChecks) {
    echo "\n🎉 系统完全就绪，可以启动！\n";
    echo "建议运行: php start_system.php\n";
} else {
    echo "\n⚠️  系统存在问题，请修复后再启动\n";
    
    // 提供修复建议
    echo "\n修复建议:\n";
    if (!$systemStatus['database']) {
        echo "- 检查数据库连接配置\n";
    }
    if (!$systemStatus['ai_services']) {
        echo "- 配置 AI 服务 API 密钥\n";
    }
    if (!$systemStatus['mail_service']) {
        echo "- 配置邮件服务参数\n";
    }
    if (!$systemStatus['storage']) {
        echo "- 确保存储目录存在且可写\n";
    }
    if (!$systemStatus['configuration']) {
        echo "- 完善应用基础配置\n";
    }
}

echo "\n";
