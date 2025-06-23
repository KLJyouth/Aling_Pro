<?php
/**
 * AlingAi Pro 最终增强数据表迁移脚本
 * 修复所有兼容性问题并创建缺失的表
 * 
 * @package AlingAi\Pro
 */

require_once __DIR__ . '/install/classes/DatabaseHelper.php';

echo "=== AlingAi Pro 最终增强数据表迁移 ===\n";
echo "开始时间: " . date('Y-m-d H:i:s') . "\n";

try {    // 数据库配置
    $config = [
        'host' => '111.180.205.70',
        'port' => 3306,
        'database' => 'alingai',
        'username' => 'AlingAi',
        'password' => 'e5bjzeWCr7k38TrZ',
        'charset' => 'utf8mb4'
    ];
    
    // 获取数据库连接
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // 显示连接信息
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "连接数据库: " . $result['db_name'] . "\n";
    echo "✅ 数据库连接成功\n";
    
    // 读取SQL文件
    $sqlFile = __DIR__ . '/install/sql/enhancement_tables_final.sql';
    echo "读取SQL文件: " . realpath($sqlFile) . "\n";
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL文件不存在: " . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new Exception("无法读取SQL文件");
    }
    
    // 分割SQL语句，处理多语句
    $statements = [];
    $lines = explode("\n", $sql);
    $currentStatement = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // 跳过注释和空行
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        $currentStatement .= $line . "\n";
        
        // 检查语句结束
        if (substr(rtrim($line), -1) === ';') {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    echo "发现 " . count($statements) . " 个SQL语句\n";
    
    // 执行每个语句
    $successCount = 0;
    $failureCount = 0;
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        
        try {
            // 提取操作描述
            $operation = '';
            if (preg_match('/CREATE TABLE.*?(\w+)/i', $statement, $matches)) {
                $operation = "创建表: " . $matches[1];
            } elseif (preg_match('/ALTER TABLE\s+(\w+)/i', $statement, $matches)) {
                $operation = "修改表: " . $matches[1];
            } else {
                $operation = "执行语句 " . ($index + 1);
            }
            
            echo $operation . "...";
            
            $pdo->exec($statement);
            echo "✅ 成功\n";
            $successCount++;
            
        } catch (PDOException $e) {
            echo "❌ 失败: " . $e->getMessage() . "\n";
            $failureCount++;
            $errors[] = [
                'statement' => $operation,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }
    
    echo "=== 迁移完成 ===\n";
    echo "成功: {$successCount} 个语句\n";
    echo "失败: {$failureCount} 个语句\n";
    
    if (!empty($errors)) {
        echo "\n=== 错误详情 ===\n";
        foreach ($errors as $error) {
            echo "❌ {$error['statement']}: {$error['error']}\n";
        }
    }
    
    // 验证表创建结果
    echo "\n=== 验证新表结构 ===\n";
    $expectedTables = [
        'user_settings',
        'system_monitoring', 
        'operations_tasks',
        'backup_records',
        'security_scans',
        'performance_tests',
        'system_notifications',
        'system_cache_status'
    ];
    
    foreach ($expectedTables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as column_count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
            $stmt->execute([$table]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['column_count'] > 0) {
                echo "✅ {$table} - {$result['column_count']} 列\n";
            } else {
                echo "❌ {$table} - 表不存在\n";
            }
        } catch (PDOException $e) {
            echo "❌ {$table} - 检查失败: " . $e->getMessage() . "\n";
        }
    }
    
    // 检查索引创建情况
    echo "\n=== 验证索引创建 ===\n";
    $indexQueries = [
        "SELECT COUNT(*) as idx_count FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_settings' AND INDEX_NAME LIKE 'idx_%'",
        "SELECT COUNT(*) as idx_count FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_monitoring' AND INDEX_NAME LIKE 'idx_%'",
        "SELECT COUNT(*) as idx_count FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_cache_status' AND INDEX_NAME LIKE 'idx_%'"
    ];
    
    foreach ($indexQueries as $query) {
        try {
            $stmt = $pdo->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['idx_count'] > 0) {
                echo "✅ 索引创建成功: {$result['idx_count']} 个索引\n";
            }
        } catch (PDOException $e) {
            echo "❌ 索引检查失败: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== 迁移报告 ===\n";
    echo "完成时间: " . date('Y-m-d H:i:s') . "\n";
    
    if ($failureCount === 0) {
        echo "总体状态: ✅ 完全成功\n";
    } elseif ($successCount > 0) {
        echo "总体状态: ⚠️  部分成功\n";
    } else {
        echo "总体状态: ❌ 完全失败\n";
    }
    
} catch (Exception $e) {
    echo "❌ 迁移失败: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
