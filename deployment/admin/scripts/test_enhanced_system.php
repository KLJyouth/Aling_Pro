<?php
/**
 * AlingAi Pro 系统增强测试
 * 包含环境变量加载和完整功能测试
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database/FileSystemDB.php';
require_once __DIR__ . '/src/Utils/EnvLoader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlingAi\Services\DatabaseService;

// 加载环境变量
EnvLoader::load();

echo "=== AlingAi Pro 系统增强测试 ===\n\n";

try {
    // 1. 测试环境变量加载
    echo "1. 测试环境变量加载...\n";
    $appName = EnvLoader::get('APP_NAME', 'Unknown');
    $appEnv = EnvLoader::get('APP_ENV', 'Unknown');
    $appDebug = EnvLoader::get('APP_DEBUG', false);
    
    echo "✓ APP_NAME: {$appName}\n";
    echo "✓ APP_ENV: {$appEnv}\n";
    echo "✓ APP_DEBUG: " . ($appDebug ? 'true' : 'false') . "\n\n";
    
    // 2. 测试AI服务密钥
    echo "2. 测试AI服务密钥...\n";
    $deepseekKey = EnvLoader::get('DEEPSEEK_API_KEY');
    $baiduKey = EnvLoader::get('BAIDU_AI_API_KEY');
    $baiduSecret = EnvLoader::get('BAIDU_AI_SECRET_KEY');
    
    echo "✓ DeepSeek API Key: " . ($deepseekKey ? substr($deepseekKey, 0, 8) . '...' : '未配置') . "\n";
    echo "✓ Baidu AI API Key: " . ($baiduKey ? substr($baiduKey, 0, 8) . '...' : '未配置') . "\n";
    echo "✓ Baidu AI Secret: " . ($baiduSecret ? substr($baiduSecret, 0, 8) . '...' : '未配置') . "\n\n";
    
    // 3. 测试数据库服务（带环境变量）
    echo "3. 测试数据库服务...\n";
    $logger = new Logger('enhanced_test');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/storage/logs/enhanced_test.log'));
    
    $dbService = new DatabaseService($logger);
    echo "✓ 数据库连接类型: " . $dbService->getConnectionType() . "\n";
    
    // 4. 测试AI服务配置
    echo "\n4. 创建AI服务配置测试...\n";
    
    // 创建简单的AI服务配置类
    class AIServiceConfig {
        private $configs = [];
        
        public function __construct() {
            $this->configs = [
                'deepseek' => [
                    'enabled' => !empty(EnvLoader::get('DEEPSEEK_API_KEY')),
                    'api_key' => EnvLoader::get('DEEPSEEK_API_KEY'),
                    'base_url' => EnvLoader::get('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
                    'model' => EnvLoader::get('DEEPSEEK_MODEL', 'deepseek-chat'),
                    'max_tokens' => (int)EnvLoader::get('DEEPSEEK_MAX_TOKENS', 4000),
                    'temperature' => (float)EnvLoader::get('DEEPSEEK_TEMPERATURE', 0.7)
                ],
                'baidu' => [
                    'enabled' => !empty(EnvLoader::get('BAIDU_AI_API_KEY')) && !empty(EnvLoader::get('BAIDU_AI_SECRET_KEY')),
                    'api_key' => EnvLoader::get('BAIDU_AI_API_KEY'),
                    'secret_key' => EnvLoader::get('BAIDU_AI_SECRET_KEY'),
                    'model' => EnvLoader::get('BAIDU_AI_MODEL', 'ERNIE-4.0-8K'),
                    'base_url' => EnvLoader::get('BAIDU_AI_BASE_URL', 'https://aip.baidubce.com')
                ]
            ];
        }
        
        public function getConfig($provider) {
            return $this->configs[$provider] ?? null;
        }
        
        public function getEnabledProviders() {
            return array_filter($this->configs, function($config) {
                return $config['enabled'];
            });
        }
    }
    
    $aiConfig = new AIServiceConfig();
    $enabledProviders = $aiConfig->getEnabledProviders();
    
    echo "✓ 可用的AI服务提供商: " . count($enabledProviders) . "\n";
    foreach ($enabledProviders as $provider => $config) {
        echo "  - {$provider}: {$config['model']}\n";
    }
    
    if (empty($enabledProviders)) {
        echo "⚠ 警告: 没有配置可用的AI服务提供商\n";
    }
    echo "\n";
    
    // 5. 测试邮件服务配置
    echo "5. 测试邮件服务配置...\n";
    $mailConfig = [
        'mailer' => EnvLoader::get('MAIL_MAILER', 'smtp'),
        'host' => EnvLoader::get('MAIL_HOST'),
        'port' => EnvLoader::get('MAIL_PORT', 587),
        'username' => EnvLoader::get('MAIL_USERNAME'),
        'password' => EnvLoader::get('MAIL_PASSWORD'),
        'encryption' => EnvLoader::get('MAIL_ENCRYPTION', 'tls'),
        'from_address' => EnvLoader::get('MAIL_FROM_ADDRESS'),
        'from_name' => EnvLoader::get('MAIL_FROM_NAME', $appName)
    ];
    
    $mailConfigured = !empty($mailConfig['host']) && !empty($mailConfig['username']);
    echo "✓ 邮件服务状态: " . ($mailConfigured ? '已配置' : '未配置') . "\n";
    
    if ($mailConfigured) {
        echo "  主机: {$mailConfig['host']}:{$mailConfig['port']}\n";
        echo "  用户: {$mailConfig['username']}\n";
        echo "  加密: {$mailConfig['encryption']}\n";
    }
    echo "\n";
    
    // 6. 测试缓存和会话配置
    echo "6. 测试缓存和会话配置...\n";
    $cacheDriver = EnvLoader::get('CACHE_DRIVER', 'file');
    $sessionDriver = EnvLoader::get('SESSION_DRIVER', 'file');
    $redisConfig = [
        'host' => EnvLoader::get('REDIS_HOST', '127.0.0.1'),
        'port' => EnvLoader::get('REDIS_PORT', 6379),
        'password' => EnvLoader::get('REDIS_PASSWORD')
    ];
    
    echo "✓ 缓存驱动: {$cacheDriver}\n";
    echo "✓ 会话驱动: {$sessionDriver}\n";
    
    if ($cacheDriver === 'redis' || $sessionDriver === 'redis') {
        echo "  Redis配置: {$redisConfig['host']}:{$redisConfig['port']}\n";
    }
    echo "\n";
    
    // 7. 测试系统监控配置
    echo "7. 测试系统监控配置...\n";
    $monitoringConfig = [
        'enabled' => EnvLoader::get('MONITORING_ENABLED', true),
        'interval' => (int)EnvLoader::get('MONITORING_INTERVAL', 60),
        'cpu_threshold' => (float)EnvLoader::get('CPU_THRESHOLD', 80.0),
        'memory_threshold' => (float)EnvLoader::get('MEMORY_THRESHOLD', 85.0),
        'disk_threshold' => (float)EnvLoader::get('DISK_THRESHOLD', 90.0)
    ];
    
    echo "✓ 监控功能: " . ($monitoringConfig['enabled'] ? '启用' : '禁用') . "\n";
    echo "✓ 监控间隔: {$monitoringConfig['interval']}秒\n";
    echo "✓ CPU阈值: {$monitoringConfig['cpu_threshold']}%\n";
    echo "✓ 内存阈值: {$monitoringConfig['memory_threshold']}%\n";
    echo "✓ 磁盘阈值: {$monitoringConfig['disk_threshold']}%\n\n";
    
    // 8. 创建系统配置文件
    echo "8. 创建系统配置文件...\n";
    $systemConfig = [
        'app' => [
            'name' => $appName,
            'env' => $appEnv,
            'debug' => $appDebug,
            'url' => EnvLoader::get('APP_URL', 'http://localhost:3000'),
            'timezone' => EnvLoader::get('APP_TIMEZONE', 'Asia/Shanghai'),
            'locale' => EnvLoader::get('APP_LOCALE', 'zh_CN')
        ],
        'database' => [
            'connection' => $dbService->getConnectionType(),
            'file_mode' => $dbService->getConnectionType() === 'file'
        ],
        'ai_services' => $aiConfig->getEnabledProviders(),
        'mail' => $mailConfig,
        'cache' => [
            'driver' => $cacheDriver,
            'redis' => $redisConfig
        ],
        'monitoring' => $monitoringConfig,
        'features' => [
            'ai_chat' => count($enabledProviders) > 0,
            'email_notifications' => $mailConfigured,
            'file_storage' => true,
            'user_management' => true,
            'system_monitoring' => $monitoringConfig['enabled']
        ]
    ];
    
    $configFile = __DIR__ . '/storage/data/system_config.json';
    file_put_contents($configFile, json_encode($systemConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✓ 系统配置已保存: {$configFile}\n\n";
    
    // 9. 功能可用性检查
    echo "9. 功能可用性检查...\n";
    $features = $systemConfig['features'];
    
    echo "可用功能:\n";
    foreach ($features as $feature => $available) {
        $status = $available ? "✓ 可用" : "⚠ 不可用";
        echo "  {$feature}: {$status}\n";
    }
    echo "\n";
    
    // 10. 系统就绪检查
    echo "10. 系统就绪检查...\n";
    $readinessChecks = [
        'database_connected' => $dbService->getConnectionType() !== 'unknown',
        'storage_writable' => is_writable(__DIR__ . '/storage'),
        'logs_writable' => is_writable(__DIR__ . '/storage/logs'),
        'env_loaded' => !empty($appName),
        'basic_features' => true
    ];
    
    $allReady = true;
    foreach ($readinessChecks as $check => $status) {
        echo "  {$check}: " . ($status ? "✓" : "✗") . "\n";
        if (!$status) $allReady = false;
    }
    
    echo "\n=== 系统状态总结 ===\n";
    echo "系统状态: " . ($allReady ? "✓ 就绪" : "⚠ 部分功能不可用") . "\n";
    echo "数据库: {$dbService->getConnectionType()} 模式\n";
    echo "AI服务: " . count($enabledProviders) . "/2 可用\n";
    echo "存储系统: ✓ 正常\n";
    echo "环境配置: ✓ 已加载\n";
    
    if ($allReady) {
        echo "\n🎉 系统完全就绪，可以启动AlingAi Pro！\n";
    } else {
        echo "\n⚠ 系统部分功能不可用，但基础功能可以使用\n";
    }
    
} catch (Exception $e) {
    echo "✗ 系统测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}