<?php
/**
 * 重新创建正确的 user_settings 表结构
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
    
    echo "=== AlingAi Pro - 重新创建 user_settings 表 ===\n";
    echo "连接数据库成功: {$config['database']}\n\n";
    
    // 1. 备份现有数据（如果存在）
    echo "=== 备份现有数据 ===\n";
    $backupData = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_settings");
        $stmt->execute();
        $backupData = $stmt->fetchAll();
        echo "✅ 备份了 " . count($backupData) . " 条记录\n";
    } catch (Exception $e) {
        echo "⚠️ 无法备份数据: " . $e->getMessage() . "\n";
    }
    
    // 2. 删除旧表
    echo "\n=== 删除旧表 ===\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS user_settings");
        echo "✅ 旧表删建成功\n";
    } catch (Exception $e) {
        echo "⚠️ 删除旧表失败: " . $e->getMessage() . "\n";
    }
    
    // 3. 创建新的正确结构的表
    echo "\n=== 创建新表 ===\n";
    $createTableSQL = "
        CREATE TABLE `user_settings` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) UNSIGNED NOT NULL,
            `category` varchar(50) NOT NULL DEFAULT 'general',
            `setting_key` varchar(100) NOT NULL,
            `setting_value` longtext NULL DEFAULT NULL,
            `setting_type` enum('string','integer','boolean','json','array') NOT NULL DEFAULT 'string',
            `is_encrypted` boolean NOT NULL DEFAULT FALSE,
            `description` text NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_user_settings` (`user_id`, `category`, `setting_key`),
            KEY `idx_user_settings_user_id` (`user_id`),
            KEY `idx_user_settings_category` (`category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户设置表'
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ 新表创建成功\n";
    
    // 4. 验证表结构
    echo "\n=== 验证新表结构 ===\n";
    $stmt = $pdo->prepare("DESCRIBE user_settings");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    $requiredFields = ['setting_type', 'category', 'setting_key', 'setting_value'];
    $foundFields = [];
    
    foreach ($columns as $column) {
        echo "字段: {$column['Field']} | 类型: {$column['Type']} | 默认值: " . ($column['Default'] ?? 'NULL') . "\n";
        $foundFields[] = $column['Field'];
    }
    
    $missingFields = array_diff($requiredFields, $foundFields);
    if (empty($missingFields)) {
        echo "\n✅ 所有必需字段都存在\n";
    } else {
        echo "\n❌ 缺少字段: " . implode(', ', $missingFields) . "\n";
        throw new Exception("表结构不完整");
    }
    
    // 5. 尝试恢复备份数据（如果有）
    if (!empty($backupData)) {
        echo "\n=== 恢复备份数据 ===\n";
        $restoredCount = 0;
        
        foreach ($backupData as $row) {
            try {
                // 将旧的JSON格式转换为新的单独字段格式
                if (isset($row['settings']) && !empty($row['settings'])) {
                    $settings = json_decode($row['settings'], true);
                    if (is_array($settings)) {
                        foreach ($settings as $category => $categorySettings) {
                            if (is_array($categorySettings)) {
                                foreach ($categorySettings as $key => $value) {
                                    $stmt = $pdo->prepare("
                                        INSERT INTO user_settings 
                                        (user_id, category, setting_key, setting_value, setting_type, created_at, updated_at) 
                                        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                                        ON DUPLICATE KEY UPDATE 
                                        setting_value = VALUES(setting_value), 
                                        updated_at = VALUES(updated_at)
                                    ");
                                    
                                    $settingType = is_bool($value) ? 'boolean' : (is_array($value) ? 'json' : 'string');
                                    $settingValue = is_array($value) || is_object($value) ? json_encode($value) : (string)$value;
                                    
                                    $stmt->execute([
                                        $row['user_id'],
                                        $category,
                                        $key,
                                        $settingValue,
                                        $settingType
                                    ]);
                                    $restoredCount++;
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                echo "⚠️ 恢复数据失败: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ 恢复了 {$restoredCount} 条设置记录\n";
    }
    
    // 6. 插入默认设置（如果需要）
    echo "\n=== 插入默认设置 ===\n";
    $defaultSettings = [
        ['user_id' => 1, 'category' => 'chat', 'setting_key' => 'default_model', 'setting_value' => 'gpt-3.5-turbo', 'setting_type' => 'string'],
        ['user_id' => 1, 'category' => 'ui', 'setting_key' => 'theme', 'setting_value' => 'dark', 'setting_type' => 'string'],
        ['user_id' => 1, 'category' => 'notification', 'setting_key' => 'email_enabled', 'setting_value' => 'true', 'setting_type' => 'boolean']
    ];
    
    $insertedCount = 0;
    foreach ($defaultSettings as $setting) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO user_settings 
                (user_id, category, setting_key, setting_value, setting_type, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value), 
                updated_at = VALUES(updated_at)
            ");
            
            $stmt->execute([
                $setting['user_id'],
                $setting['category'],
                $setting['setting_key'],
                $setting['setting_value'],
                $setting['setting_type']
            ]);
            $insertedCount++;
        } catch (Exception $e) {
            echo "⚠️ 插入默认设置失败: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ 插入了 {$insertedCount} 条默认设置\n";
    
    // 7. 最终验证
    echo "\n=== 最终验证 ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_settings");
    $stmt->execute();
    $totalCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = ? AND table_name = 'user_settings' AND column_name = 'setting_type'");
    $stmt->execute([$config['database']]);
    $settingTypeExists = $stmt->fetch()['count'] > 0;
    
    if ($settingTypeExists && $totalCount >= 0) {
        echo "✅ user_settings 表重建成功！\n";
        echo "📊 表中共有 {$totalCount} 条记录\n";
        echo "🎉 现在可以重新运行安装程序了！\n";
    } else {
        throw new Exception("表重建验证失败");
    }
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
