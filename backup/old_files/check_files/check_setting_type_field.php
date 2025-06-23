<?php
/**
 * 检查和修复 user_settings 表中的 setting_type 字段
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
    
    echo "=== AlingAi Pro - user_settings 表字段检查 ===\n";
    echo "连接数据库成功: {$config['database']}\n\n";
    
    // 1. 检查 user_settings 表是否存在
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM information_schema.tables 
        WHERE table_schema = ? AND table_name = 'user_settings'
    ");
    $stmt->execute([$config['database']]);
    $tableExists = $stmt->fetch()['count'] > 0;
    
    if (!$tableExists) {
        echo "❌ user_settings 表不存在\n";
        echo "正在创建 user_settings 表...\n";
        
        // 创建 user_settings 表
        $createTableSQL = file_get_contents(__DIR__ . '/install/sql/enhancement_tables.sql');
        if ($createTableSQL) {
            $pdo->exec($createTableSQL);
            echo "✅ user_settings 表创建成功\n";
        } else {
            throw new Exception("无法读取创建表的SQL文件");
        }
    } else {
        echo "✅ user_settings 表存在\n";
    }
    
    // 2. 检查表结构
    echo "\n=== 检查表结构 ===\n";
    $stmt = $pdo->prepare("DESCRIBE user_settings");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    $hasSettingType = false;
    foreach ($columns as $column) {
        echo "字段: {$column['Field']} | 类型: {$column['Type']} | 默认值: {$column['Default']}\n";
        if ($column['Field'] === 'setting_type') {
            $hasSettingType = true;
        }
    }
    
    // 3. 如果缺少 setting_type 字段，则添加
    if (!$hasSettingType) {
        echo "\n❌ 缺少 setting_type 字段，正在添加...\n";
        
        $alterSQL = "ALTER TABLE user_settings ADD COLUMN setting_type ENUM('string','integer','boolean','json','array') NOT NULL DEFAULT 'string' AFTER setting_value";
        $pdo->exec($alterSQL);
        
        echo "✅ setting_type 字段添加成功\n";
    } else {
        echo "\n✅ setting_type 字段已存在\n";
    }
    
    // 4. 验证修复结果
    echo "\n=== 验证修复结果 ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as field_count FROM information_schema.columns WHERE table_schema = ? AND table_name = 'user_settings' AND column_name = 'setting_type'");
    $stmt->execute([$config['database']]);
    $fieldExists = $stmt->fetch()['field_count'] > 0;
    
    if ($fieldExists) {
        echo "✅ setting_type 字段验证成功\n";
        
        // 检查表中数据
        $stmt = $pdo->prepare("SELECT COUNT(*) as row_count FROM user_settings");
        $stmt->execute();
        $rowCount = $stmt->fetch()['row_count'];
        echo "📊 user_settings 表中有 {$rowCount} 条记录\n";
        
        // 如果有数据但setting_type为空，则更新默认值
        if ($rowCount > 0) {
            $stmt = $pdo->prepare("UPDATE user_settings SET setting_type = 'string' WHERE setting_type IS NULL OR setting_type = ''");
            $updated = $stmt->execute();
            $affectedRows = $stmt->rowCount();
            if ($affectedRows > 0) {
                echo "📝 更新了 {$affectedRows} 条记录的 setting_type 默认值\n";
            }
        }
        
    } else {
        throw new Exception("setting_type 字段添加失败");
    }
    
    echo "\n🎉 user_settings 表字段检查和修复完成！\n";
    echo "现在可以重新运行安装程序了。\n";
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
