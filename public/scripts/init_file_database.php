<?php

/**
 * 🗃�?简化数据库初始化脚�?
 * 创建JSON文件数据库解决方�?
 */

$dataPath = __DIR__ . '/../database/filedb';

// 创建数据目录
if (!is_dir($dataPath)) {
    mkdir($dataPath, 0755, true];
    echo "�?创建数据目录: $dataPath\n";
}

// 初始化基础数据�?
$tables = [
    'users' => [
        [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@alingai.pro',
            'password_hash' => password_hash('admin123456', PASSWORD_DEFAULT],
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'],
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ], 
    'sessions' => [], 
    'ai_conversations' => [], 
    'system_logs' => [
        [
            'id' => 1,
            'level' => 'info',
            'message' => 'Database initialized with file storage',
            'context' => json_encode(['system' => 'filedb']],
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]
];

foreach ($tables as $tableName => $data) {
    $tableFile = $dataPath . "/{$tableName}.json";
    file_put_contents($tableFile, json_encode($data, JSON_PRETTY_PRINT)];
    echo "�?创建�? $tableName (" . count($data) . " 条记�?\n";
}

// 更新数据库配�?
$configFile = __DIR__ . '/../config/database.php';
$newConfig = "<?php\n\n/**\n * AlingAi Pro 5.0 - Database Configuration\n * Updated with File Database fallback\n * Modified: " . date('Y-m-d H:i:s') . "\n */\n\nreturn [\n    'default' => 'file',\n    'connections' => [\n        'file' => [\n            'driver' => 'file',\n            'path' => __DIR__ . '/../database/filedb',\n        ], \n        'mysql' => [\n            'driver' => 'mysql',\n            'host' => '127.0.0.1',\n            'port' => '3306',\n            'database' => 'alingai_pro',\n            'username' => 'root',\n            'password' => '',\n            'charset' => 'utf8mb4',\n            'collation' => 'utf8mb4_unicode_ci',\n        ]\n    ]\n];\n";

file_put_contents($configFile, $newConfig];
echo "�?数据库配置已更新\n";

echo "\n🎉 文件数据库初始化完成！\n";
echo "📁 数据路径: $dataPath\n";
echo "👤 默认管理�? admin / admin123456\n";

?>
