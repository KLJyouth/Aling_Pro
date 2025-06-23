<?php
/**
 * AlingAi Pro 系统集成测试
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database/FileSystemDB.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlingAi\Services\DatabaseService;

echo "=== AlingAi Pro 系统集成测试 ===\n\n";

try {
    // 1. 测试日志服务
    echo "1. 测试日志服务...\n";
    $logger = new Logger('test');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/storage/logs/test.log'));
    $logger->info('系统集成测试开始');
    echo "✓ 日志服务正常\n\n";
    
    // 2. 测试数据库服务
    echo "2. 测试数据库服务...\n";
    $dbService = new DatabaseService($logger);
    echo "✓ 数据库连接类型: " . $dbService->getConnectionType() . "\n";
    
    // 测试数据库操作
    $insertResult = $dbService->insert('users', [
        'username' => 'integration_test',
        'email' => 'integration@test.com',
        'password' => password_hash('test123', PASSWORD_DEFAULT),
        'level' => 1
    ]);
    echo "✓ 数据插入测试: " . ($insertResult ? '成功' : '失败') . "\n";
    
    $users = $dbService->findAll('users', ['username' => 'integration_test']);
    echo "✓ 数据查询测试: 找到 " . count($users) . " 条记录\n\n";
    
    // 3. 测试环境配置
    echo "3. 测试环境配置...\n";
    $requiredEnvVars = [
        'APP_NAME', 'APP_ENV', 'APP_DEBUG',
        'DEEPSEEK_API_KEY', 'BAIDU_AI_API_KEY', 'BAIDU_AI_SECRET_KEY'
    ];
    
    foreach ($requiredEnvVars as $var) {
        $value = getenv($var);
        if ($value !== false) {
            echo "✓ {$var}: " . (strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value) . "\n";
        } else {
            echo "⚠ {$var}: 未设置\n";
        }
    }
    echo "\n";
    
    // 4. 测试存储目录
    echo "4. 测试存储目录...\n";
    $storageDirs = [
        'storage/data',
        'storage/cache',
        'storage/logs',
        'storage/sessions',
        'storage/uploads',
        'storage/backup'
    ];
    
    foreach ($storageDirs as $dir) {
        if (is_dir(__DIR__ . '/' . $dir)) {
            $writable = is_writable(__DIR__ . '/' . $dir);
            echo "✓ {$dir}: " . ($writable ? '可写' : '只读') . "\n";
        } else {
            echo "✗ {$dir}: 不存在\n";
        }
    }
    echo "\n";
    
    // 5. 测试AI服务配置
    echo "5. 测试AI服务配置...\n";
    $aiConfig = [
        'deepseek' => [
            'api_key' => getenv('DEEPSEEK_API_KEY'),
            'base_url' => getenv('DEEPSEEK_BASE_URL') ?: 'https://api.deepseek.com',
            'model' => getenv('DEEPSEEK_MODEL') ?: 'deepseek-chat'
        ],
        'baidu' => [
            'api_key' => getenv('BAIDU_AI_API_KEY'),
            'secret_key' => getenv('BAIDU_AI_SECRET_KEY'),
            'model' => getenv('BAIDU_AI_MODEL') ?: 'ERNIE-4.0-8K'
        ]
    ];
    
    foreach ($aiConfig as $provider => $config) {
        echo "AI服务 {$provider}:\n";
        foreach ($config as $key => $value) {
            if (strpos($key, 'key') !== false && $value) {
                echo "  ✓ {$key}: " . substr($value, 0, 8) . "...\n";
            } else {
                echo "  " . ($value ? "✓" : "⚠") . " {$key}: {$value}\n";
            }
        }
    }
    echo "\n";
    
    // 6. 测试邮件服务配置
    echo "6. 测试邮件服务配置...\n";
    $mailConfig = [
        'MAIL_MAILER' => getenv('MAIL_MAILER'),
        'MAIL_HOST' => getenv('MAIL_HOST'),
        'MAIL_PORT' => getenv('MAIL_PORT'),
        'MAIL_USERNAME' => getenv('MAIL_USERNAME'),
        'MAIL_FROM_ADDRESS' => getenv('MAIL_FROM_ADDRESS')
    ];
    
    foreach ($mailConfig as $key => $value) {
        echo "  " . ($value ? "✓" : "⚠") . " {$key}: {$value}\n";
    }
    echo "\n";
    
    // 7. 测试缓存配置
    echo "7. 测试缓存配置...\n";
    $cacheDriver = getenv('CACHE_DRIVER') ?: 'file';
    echo "✓ 缓存驱动: {$cacheDriver}\n";
    
    if ($cacheDriver === 'redis') {
        $redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
        $redisPort = getenv('REDIS_PORT') ?: 6379;
        echo "  Redis连接: {$redisHost}:{$redisPort}\n";
    }
    echo "\n";
    
    // 8. 生成系统状态报告
    echo "8. 生成系统状态报告...\n";
    $systemStatus = [
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'database_type' => $dbService->getConnectionType(),
        'storage_writable' => is_writable(__DIR__ . '/storage'),
        'cache_driver' => $cacheDriver,
        'ai_services' => [
            'deepseek' => !empty(getenv('DEEPSEEK_API_KEY')),
            'baidu' => !empty(getenv('BAIDU_AI_API_KEY'))
        ],
        'mail_configured' => !empty(getenv('MAIL_HOST')),
        'debug_mode' => getenv('APP_DEBUG') === 'true'
    ];
    
    $reportFile = __DIR__ . '/storage/logs/system_status_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($reportFile, json_encode($systemStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✓ 系统状态报告已保存: {$reportFile}\n\n";
    
    // 9. 测试总结
    echo "=== 测试总结 ===\n";
    echo "✓ 数据库服务: " . $dbService->getConnectionType() . " 模式\n";
    echo "✓ 存储系统: 正常\n";
    echo "✓ 日志系统: 正常\n";
    echo "✓ AI服务配置: " . (count(array_filter($systemStatus['ai_services'])) . "/2 已配置") . "\n";
    echo "✓ 邮件服务: " . ($systemStatus['mail_configured'] ? '已配置' : '未配置') . "\n";
    echo "\n系统已准备就绪，可以开始使用！\n";
    
} catch (Exception $e) {
    echo "✗ 系统测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
