<?php
/**
 * 修复版增强数据表迁移脚本
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/EnvLoader.php';

use AlingAi\Utils\EnvLoader;

// 加载环境配置
EnvLoader::load(__DIR__ . '/.env');

echo "=== AlingAi Pro 修复版增强数据表迁移 ===\n";
echo "开始时间: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 连接数据库
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'],
        $_ENV['DB_DATABASE']
    );
    
    echo "连接数据库: {$_ENV['DB_HOST']}:{$_ENV['DB_PORT']}/{$_ENV['DB_DATABASE']}\n";
    
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ 数据库连接成功\n\n";
    
    // 读取修复版增强数据表SQL文件
    $sqlFile = __DIR__ . '/install/sql/enhancement_tables_fixed.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("修复版增强数据表SQL文件不存在: {$sqlFile}");
    }
    
    echo "读取SQL文件: {$sqlFile}\n";
    $sql = file_get_contents($sqlFile);
    
    // 分割SQL语句
    $statements = preg_split('/;\s*$/m', $sql, -1, PREG_SPLIT_NO_EMPTY);
    
    echo "发现 " . count($statements) . " 个SQL语句\n\n";
    
    // 执行每个SQL语句
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        try {
            // 提取表名用于显示
            if (preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                echo "创建表: {$tableName}... ";
            } elseif (preg_match('/ALTER TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                echo "修改表: {$tableName}... ";
            } else {
                echo "执行语句 " . ($index + 1) . "... ";
            }
            
            $pdo->exec($statement);
            echo "✅ 成功\n";
            $success++;
            
        } catch (PDOException $e) {
            // 忽略已存在的索引错误
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "⚠️  索引已存在，跳过\n";
            } else {
                echo "❌ 失败: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
    }
    
    echo "\n=== 迁移完成 ===\n";
    echo "成功: {$success} 个语句\n";
    echo "失败: {$errors} 个语句\n";
    
    // 验证新表是否创建成功
    echo "\n=== 验证新表结构 ===\n";
    $newTables = [
        'user_settings',
        'system_monitoring', 
        'operations_tasks',
        'backup_records',
        'security_scans',
        'performance_tests',
        'system_notifications',
        'system_cache_status'
    ];
    
    foreach ($newTables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            $result = $stmt->fetch();
            
            if ($result) {
                // 获取表的列数
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$_ENV['DB_DATABASE']}' AND TABLE_NAME = '{$table}'");
                $columnCount = $stmt->fetch()['count'];
                echo "✅ {$table} - {$columnCount} 列\n";
            } else {
                echo "❌ {$table} - 表不存在\n";
            }
        } catch (Exception $e) {
            echo "❌ {$table} - 检查失败: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== 迁移报告 ===\n";
    echo "完成时间: " . date('Y-m-d H:i:s') . "\n";
    echo "总体状态: " . ($errors == 0 ? "✅ 成功" : "⚠️  部分成功") . "\n";
    
} catch (Exception $e) {
    echo "❌ 迁移失败: " . $e->getMessage() . "\n";
    exit(1);
}
