<?php
/**
 * 数据库结构设置脚本
 * 创建完整的真实数据库表结构
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== AlingAi Pro 数据库结构创建 ===\n\n";

// 使用文件系统数据库替代方案
$dataDir = __DIR__ . '/storage/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

echo "✅ 数据存储目录创建成功: {$dataDir}\n";

// 创建数据表结构定义
$tables = [
    'users' => [
        'id' => 'auto_increment',
        'username' => 'string',
        'email' => 'string',
        'password_hash' => 'string', 
        'full_name' => 'string',
        'avatar' => 'string',
        'role' => 'string',
        'status' => 'string',
        'email_verified' => 'boolean',
        'last_login' => 'timestamp',
        'login_count' => 'integer',
        'preferences' => 'json',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ],
    'conversations' => [
        'id' => 'auto_increment',
        'user_id' => 'integer',
        'title' => 'string',
        'model' => 'string',
        'system_prompt' => 'text',
        'status' => 'string',
        'message_count' => 'integer',
        'tokens_used' => 'integer',
        'last_activity' => 'timestamp',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ],
    'messages' => [
        'id' => 'auto_increment',
        'conversation_id' => 'integer',
        'user_id' => 'integer',
        'role' => 'string',
        'content' => 'text',
        'tokens' => 'integer',
        'model' => 'string',
        'metadata' => 'json',
        'created_at' => 'timestamp'
    ],
    'documents' => [
        'id' => 'auto_increment',
        'user_id' => 'integer',
        'title' => 'string',
        'content' => 'text',
        'type' => 'string',
        'size' => 'integer',
        'format' => 'string',
        'status' => 'string',
        'tags' => 'json',
        'metadata' => 'json',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ],
    'user_logs' => [
        'id' => 'auto_increment',
        'user_id' => 'integer',
        'action' => 'string',
        'resource' => 'string',
        'resource_id' => 'integer',
        'ip_address' => 'string',
        'user_agent' => 'string',
        'metadata' => 'json',
        'created_at' => 'timestamp'
    ],
    'api_tokens' => [
        'id' => 'auto_increment',
        'user_id' => 'integer',
        'name' => 'string',
        'token' => 'string',
        'abilities' => 'json',
        'last_used_at' => 'timestamp',
        'expires_at' => 'timestamp',
        'created_at' => 'timestamp'
    ],
    'system_settings' => [
        'id' => 'auto_increment',
        'key' => 'string',
        'value' => 'text',
        'type' => 'string',
        'description' => 'string',
        'is_public' => 'boolean',
        'updated_at' => 'timestamp'
    ],
    'ai_models' => [
        'id' => 'auto_increment',
        'name' => 'string',
        'provider' => 'string',
        'api_key' => 'string',
        'endpoint' => 'string',
        'max_tokens' => 'integer',
        'temperature' => 'float',
        'status' => 'string',
        'capabilities' => 'json',
        'pricing' => 'json',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ],
    'usage_statistics' => [
        'id' => 'auto_increment',
        'user_id' => 'integer',
        'model' => 'string',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'cost' => 'float',
        'date' => 'date',
        'created_at' => 'timestamp'
    ],
    'feedback' => [
        'id' => 'auto_increment',
        'user_id' => 'integer',
        'type' => 'string',
        'rating' => 'integer',
        'content' => 'text',
        'metadata' => 'json',
        'status' => 'string',
        'created_at' => 'timestamp'
    ]
];

// 创建表结构文件
foreach ($tables as $tableName => $structure) {
    $tableFile = $dataDir . "/{$tableName}.json";
    
    $tableInfo = [
        'name' => $tableName,
        'structure' => $structure,
        'data' => [],
        'indexes' => [],
        'auto_increment' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($tableFile, json_encode($tableInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ 创建表结构: {$tableName}\n";
}

// 创建系统默认数据
$defaultSettings = [
    ['key' => 'app_name', 'value' => 'AlingAi Pro', 'type' => 'string', 'description' => '应用名称', 'is_public' => true],
    ['key' => 'app_version', 'value' => '1.0.0', 'type' => 'string', 'description' => '应用版本', 'is_public' => true],
    ['key' => 'max_tokens', 'value' => '4000', 'type' => 'integer', 'description' => '最大令牌数', 'is_public' => false],
    ['key' => 'default_model', 'value' => 'deepseek-chat', 'type' => 'string', 'description' => '默认AI模型', 'is_public' => true],
    ['key' => 'registration_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => '是否允许注册', 'is_public' => true],
    ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'description' => '维护模式', 'is_public' => true]
];

$settingsData = json_decode(file_get_contents($dataDir . '/system_settings.json'), true);
foreach ($defaultSettings as $setting) {
    $setting['id'] = $settingsData['auto_increment']++;
    $setting['updated_at'] = date('Y-m-d H:i:s');
    $settingsData['data'][] = $setting;
}
$settingsData['updated_at'] = date('Y-m-d H:i:s');
file_put_contents($dataDir . '/system_settings.json', json_encode($settingsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ 创建系统默认设置\n";

// 创建默认AI模型配置
$defaultModels = [
    [
        'name' => 'DeepSeek Chat',
        'provider' => 'deepseek',
        'api_key' => '',
        'endpoint' => 'https://api.deepseek.com/v1/chat/completions',
        'max_tokens' => 4000,
        'temperature' => 0.7,
        'status' => 'active',
        'capabilities' => ['chat', 'completion'],
        'pricing' => ['input' => 0.14, 'output' => 0.28]
    ],
    [
        'name' => 'GPT-4',
        'provider' => 'openai',
        'api_key' => '',
        'endpoint' => 'https://api.openai.com/v1/chat/completions',
        'max_tokens' => 4000,
        'temperature' => 0.7,
        'status' => 'inactive',
        'capabilities' => ['chat', 'completion', 'image'],
        'pricing' => ['input' => 30, 'output' => 60]
    ]
];

$modelsData = json_decode(file_get_contents($dataDir . '/ai_models.json'), true);
foreach ($defaultModels as $model) {
    $model['id'] = $modelsData['auto_increment']++;
    $model['created_at'] = date('Y-m-d H:i:s');
    $model['updated_at'] = date('Y-m-d H:i:s');
    $modelsData['data'][] = $model;
}
$modelsData['updated_at'] = date('Y-m-d H:i:s');
file_put_contents($dataDir . '/ai_models.json', json_encode($modelsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ 创建默认AI模型配置\n";

// 创建数据库操作辅助类配置
$dbConfig = [
    'type' => 'file_system',
    'path' => $dataDir,
    'auto_backup' => true,
    'backup_interval' => 3600, // 1小时
    'max_backups' => 24,
    'created_at' => date('Y-m-d H:i:s')
];

file_put_contents($dataDir . '/database_config.json', json_encode($dbConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ 创建数据库配置文件\n";

// 创建数据访问状态文件
$migrationStatus = [
    'status' => 'completed',
    'version' => '1.0.0',
    'tables_created' => count($tables),
    'migration_date' => date('Y-m-d H:i:s'),
    'last_check' => date('Y-m-d H:i:s')
];

file_put_contents($dataDir . '/migration_status.json', json_encode($migrationStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ 创建迁移状态文件\n";

echo "\n=== 数据库结构创建完成 ===\n";
echo "总共创建 " . count($tables) . " 个数据表\n";
echo "数据存储路径: {$dataDir}\n";
echo "系统已切换为文件系统数据库模式\n\n";

echo "✅ 数据库迁移完成！\n";
echo "现在可以启动应用程序进行测试\n";
