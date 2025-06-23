<?php
/**
 * 简单数据库测试和初始化
 */

echo "=== AlingAi Pro 数据库初始化 ===\n";

// 创建必要的目录
$dirs = [
    __DIR__ . '/storage/database',
    __DIR__ . '/storage/data',
    __DIR__ . '/storage/logs'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ 创建目录: $dir\n";
    } else {
        echo "✅ 目录已存在: $dir\n";
    }
}

// 使用文件系统存储创建用户数据
$usersFile = __DIR__ . '/storage/data/users.json';
$sessionsFile = __DIR__ . '/storage/data/sessions.json';

if (!file_exists($usersFile)) {
    $defaultUsers = [
        [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@alingai.pro',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    file_put_contents($usersFile, json_encode($defaultUsers, JSON_PRETTY_PRINT));
    echo "✅ 用户数据文件创建成功\n";
} else {
    echo "✅ 用户数据文件已存在\n";
}

if (!file_exists($sessionsFile)) {
    $defaultSessions = [];
    file_put_contents($sessionsFile, json_encode($defaultSessions, JSON_PRETTY_PRINT));
    echo "✅ 会话数据文件创建成功\n";
} else {
    echo "✅ 会话数据文件已存在\n";
}

// 检查API配置
echo "\n=== API配置检查 ===\n";
$apiIndex = __DIR__ . '/public/api/index.php';
if (file_exists($apiIndex)) {
    echo "✅ API入口文件存在\n";
} else {
    echo "❌ API入口文件不存在\n";
}

// 创建简单的状态检查文件
$statusFile = __DIR__ . '/storage/data/system_status.json';
$status = [
    'database_initialized' => true,
    'file_system_ready' => true,
    'last_check' => date('Y-m-d H:i:s'),
    'system_ready' => true
];

file_put_contents($statusFile, json_encode($status, JSON_PRETTY_PRINT));
echo "✅ 系统状态文件创建成功\n";

echo "\n=== 系统初始化完成 ===\n";
echo "管理员账户: admin / admin123\n";
echo "数据存储: 文件系统模式\n";
echo "系统状态: 就绪\n";
?>
