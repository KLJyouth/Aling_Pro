<?php
/**
 * 检查和修复 system_settings 表
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
    
    echo "=== AlingAi Pro - system_settings 表检查 ===\n";
    echo "连接数据库成功: {$config['database']}\n\n";
    
    // 1. 检查 system_settings 表是否存在
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM information_schema.tables 
        WHERE table_schema = ? AND table_name = 'system_settings'
    ");
    $stmt->execute([$config['database']]);
    $tableExists = $stmt->fetch()['count'] > 0;
    
    if ($tableExists) {
        echo "✅ system_settings 表存在\n";
        
        // 检查表结构
        echo "\n=== 当前表结构 ===\n";
        $stmt = $pdo->prepare("DESCRIBE system_settings");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        $hasSettingType = false;
        foreach ($columns as $column) {
            echo "字段: {$column['Field']} | 类型: {$column['Type']} | 默认值: " . ($column['Default'] ?? 'NULL') . "\n";
            if ($column['Field'] === 'setting_type') {
                $hasSettingType = true;
            }
        }
        
        if (!$hasSettingType) {
            echo "\n❌ 缺少 setting_type 字段，正在添加...\n";
            
            $alterSQL = "ALTER TABLE system_settings ADD COLUMN setting_type ENUM('string','integer','boolean','json') DEFAULT 'string' AFTER setting_value";
            $pdo->exec($alterSQL);
            
            echo "✅ setting_type 字段添加成功\n";
        } else {
            echo "\n✅ setting_type 字段已存在\n";
        }
        
    } else {
        echo "❌ system_settings 表不存在，正在创建...\n";
        
        $createTableSQL = "
            CREATE TABLE system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
                description TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($createTableSQL);
        echo "✅ system_settings 表创建成功\n";
    }
    
    // 2. 检查表中数据
    echo "\n=== 检查表中数据 ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM system_settings");
    $stmt->execute();
    $rowCount = $stmt->fetch()['count'];
    echo "📊 system_settings 表中有 {$rowCount} 条记录\n";
    
    if ($rowCount > 0) {
        echo "\n现有设置：\n";
        $stmt = $pdo->prepare("SELECT setting_key, setting_value, setting_type FROM system_settings LIMIT 10");
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            echo "- {$row['setting_key']}: {$row['setting_value']} ({$row['setting_type']})\n";
        }
    }
    
    // 3. 最终验证
    echo "\n=== 最终验证 ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = ? AND table_name = 'system_settings' AND column_name = 'setting_type'");
    $stmt->execute([$config['database']]);
    $settingTypeExists = $stmt->fetch()['count'] > 0;
    
    if ($settingTypeExists) {
        echo "✅ system_settings 表 setting_type 字段验证成功\n";
        echo "🎉 现在可以重新运行安装程序了！\n";
    } else {
        throw new Exception("setting_type 字段验证失败");
    }
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
