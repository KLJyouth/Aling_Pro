<?php
/**
 * 创建缺少的数据库表 - logs 和 user_preferences
 */

require_once __DIR__ . '/vendor/autoload.php';

// 加载环境配置
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $envLines = explode("\n", $envContent);
    foreach ($envLines as $line) {
        if (trim($line) && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// 数据库连接配置
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'alingai_pro',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? ''
];

try {
    // 连接数据库
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "=== AlingAi Pro - 创建缺少的数据库表 ===\n";
    echo "连接数据库成功: {$config['database']}\n\n";
    
    // 1. 创建 logs 表
    echo "=== 创建 logs 表 ===\n";
    $createLogsSQL = "
        CREATE TABLE IF NOT EXISTS logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            level VARCHAR(20) NOT NULL DEFAULT 'info',
            message TEXT NOT NULL,
            context JSON NULL,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            request_method VARCHAR(10) NULL,
            request_url VARCHAR(500) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_logs_level (level),
            INDEX idx_logs_user_id (user_id),
            INDEX idx_logs_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统日志表'
    ";
    
    $pdo->exec($createLogsSQL);
    echo "✅ logs 表创建成功\n";
    
    // 2. 创建 user_preferences 表
    echo "\n=== 创建 user_preferences 表 ===\n";
    $createUserPreferencesSQL = "
        CREATE TABLE IF NOT EXISTS user_preferences (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            theme VARCHAR(20) DEFAULT 'light',
            language VARCHAR(10) DEFAULT 'zh',
            timezone VARCHAR(50) DEFAULT 'Asia/Shanghai',
            notifications_enabled BOOLEAN DEFAULT TRUE,
            email_notifications BOOLEAN DEFAULT TRUE,
            sound_enabled BOOLEAN DEFAULT TRUE,
            auto_save BOOLEAN DEFAULT TRUE,
            sidebar_collapsed BOOLEAN DEFAULT FALSE,
            chat_history_limit INT DEFAULT 100,
            ai_model_preference VARCHAR(50) DEFAULT 'gpt-3.5-turbo',
            temperature DECIMAL(3,2) DEFAULT 0.7,
            max_tokens INT DEFAULT 2048,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_user_preferences_user_id (user_id),
            INDEX idx_user_preferences_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户偏好设置表'
    ";
    
    $pdo->exec($createUserPreferencesSQL);
    echo "✅ user_preferences 表创建成功\n";
    
    // 3. 验证表创建
    echo "\n=== 验证表创建 ===\n";
    $tables = ['logs', 'user_preferences'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM information_schema.tables 
            WHERE table_schema = ? AND table_name = ?
        ");
        $stmt->execute([$config['database'], $table]);
        $exists = $stmt->fetch()['count'] > 0;
        
        if ($exists) {
            echo "✅ {$table} 表验证成功\n";
        } else {
            echo "❌ {$table} 表创建失败\n";
        }
    }
    
    // 4. 插入默认用户偏好设置
    echo "\n=== 插入默认用户偏好设置 ===\n";
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO user_preferences (user_id, theme, language, timezone) 
        VALUES (1, 'dark', 'zh', 'Asia/Shanghai')
    ");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "✅ 为用户 ID 1 创建了默认偏好设置\n";
    } else {
        echo "ℹ️ 用户 ID 1 的偏好设置已存在\n";
    }
    
    // 5. 最终统计
    echo "\n=== 最终统计 ===\n";
    $allTables = [
        'users', 'chat_sessions', 'chat_messages', 'api_keys', 
        'system_settings', 'user_settings', 'logs', 'user_preferences'
    ];
    
    $existingTables = 0;
    foreach ($allTables as $table) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM information_schema.tables 
            WHERE table_schema = ? AND table_name = ?
        ");
        $stmt->execute([$config['database'], $table]);
        if ($stmt->fetch()['count'] > 0) {
            $existingTables++;
        }
    }
    
    echo "📊 数据库中共有 {$existingTables}/{" . count($allTables) . "} 个核心表\n";
    
    if ($existingTables === count($allTables)) {
        echo "🎉 所有数据库表创建完成！\n";
        echo "💡 现在可以重新运行安装程序进行最终验证了。\n";
    } else {
        echo "⚠️ 仍有表未创建，请检查。\n";
    }
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
