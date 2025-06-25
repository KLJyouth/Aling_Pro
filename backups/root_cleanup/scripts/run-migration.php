<?php
/**
 * 数据库迁移执行脚本
 */

require_once __DIR__ . '/../vendor/autoload.php';

try {
    // 读取环境配置
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                [$key, $value] = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }

    // 数据库连接
    $host = $_ENV['DB_HOST'] ?? '111.180.205.70';
    $dbname = $_ENV['DB_DATABASE'] ?? 'alingai';
    $username = $_ENV['DB_USERNAME'] ?? 'AlingAi';
    $password = $_ENV['DB_PASSWORD'] ?? 'e5bjzeWCr7k38TrZ';

    echo "🔗 连接数据库: {$host}/{$dbname}\n";
    
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 执行迁移
    $migrationFile = __DIR__ . '/../database/migrations/2025_06_12_000001_create_enterprise_tables.sql';
    
    if (!file_exists($migrationFile)) {
        echo "❌ 迁移文件不存在: {$migrationFile}\n";
        exit(1);
    }

    echo "📋 读取迁移文件...\n";
    $sql = file_get_contents($migrationFile);
    
    // 分割SQL语句
    $statements = array_filter(
        preg_split('/;\s*$/m', $sql),
        function($stmt) {
            return trim($stmt) !== '' && !preg_match('/^\s*--/', $stmt);
        }
    );

    echo "🚀 开始执行迁移...\n";
    $pdo->beginTransaction();

    $executed = 0;
    foreach ($statements as $statement) {
        $trimmed = trim($statement);
        if (empty($trimmed) || str_starts_with($trimmed, '--')) {
            continue;
        }

        try {
            $pdo->exec($trimmed . ';');
            $executed++;
            
            // 提取表名用于显示
            if (preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?/i', $trimmed, $matches)) {
                echo "  ✅ 创建表: {$matches[1]}\n";
            } elseif (preg_match('/CREATE VIEW\s+`?(\w+)`?/i', $trimmed, $matches)) {
                echo "  ✅ 创建视图: {$matches[1]}\n";
            } elseif (preg_match('/CREATE\s+(?:DEFINER\s*=\s*[^@]+@[^@]+\s+)?PROCEDURE\s+`?(\w+)`?/i', $trimmed, $matches)) {
                echo "  ✅ 创建存储过程: {$matches[1]}\n";
            } elseif (preg_match('/CREATE\s+(?:DEFINER\s*=\s*[^@]+@[^@]+\s+)?TRIGGER\s+`?(\w+)`?/i', $trimmed, $matches)) {
                echo "  ✅ 创建触发器: {$matches[1]}\n";
            }
        } catch (PDOException $e) {
            // 如果是表已存在错误，继续执行
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "  ⚠️  对象已存在，跳过\n";
                continue;
            }
            throw $e;
        }
    }

    $pdo->commit();
    echo "\n🎉 迁移执行完成！\n";
    echo "📊 总计执行: {$executed} 条SQL语句\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "❌ 迁移失败: " . $e->getMessage() . "\n";
    exit(1);
}
