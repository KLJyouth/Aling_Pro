<?php

/**
 * ðï¸?ç®åæ°æ®åºåå§åèæ?
 * åå»ºJSONæä»¶æ°æ®åºè§£å³æ¹æ¡?
 */

$dataPath = __DIR__ . '/../database/filedb';

// åå»ºæ°æ®ç®å½
if (!is_dir($dataPath)) {
    mkdir($dataPath, 0755, true];
    echo "â?åå»ºæ°æ®ç®å½: $dataPath\n";
}

// åå§ååºç¡æ°æ®è¡?
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
    echo "â?åå»ºè¡? $tableName (" . count($data) . " æ¡è®°å½?\n";
}

// æ´æ°æ°æ®åºéç½?
$configFile = __DIR__ . '/../config/database.php';
$newConfig = "<?php\n\n/**\n * AlingAi Pro 5.0 - Database Configuration\n * Updated with File Database fallback\n * Modified: " . date('Y-m-d H:i:s') . "\n */\n\nreturn [\n    'default' => 'file',\n    'connections' => [\n        'file' => [\n            'driver' => 'file',\n            'path' => __DIR__ . '/../database/filedb',\n        ], \n        'mysql' => [\n            'driver' => 'mysql',\n            'host' => '127.0.0.1',\n            'port' => '3306',\n            'database' => 'alingai_pro',\n            'username' => 'root',\n            'password' => '',\n            'charset' => 'utf8mb4',\n            'collation' => 'utf8mb4_unicode_ci',\n        ]\n    ]\n];\n";

file_put_contents($configFile, $newConfig];
echo "â?æ°æ®åºéç½®å·²æ´æ°\n";

echo "\nð æä»¶æ°æ®åºåå§åå®æï¼\n";
echo "ð æ°æ®è·¯å¾: $dataPath\n";
echo "ð¤ é»è®¤ç®¡çå? admin / admin123456\n";

?>
