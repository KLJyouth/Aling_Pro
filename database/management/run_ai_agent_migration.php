<?php
/**
 * AI智能体数据表迁移脚本
 * 三完编译 (Three Complete Compilation) - 企业级AI代理数据结构
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/EnvLoader.php';

use AlingAi\Utils\EnvLoader;

// 加载环境配置
EnvLoader::load(__DIR__ . '/.env');

echo "=== AlingAi Pro AI智能体数据表迁移 ===\n";
echo "开始时间: " . date('Y-m-d H:i:s') . "\n\n";

try {    // 连接数据库
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_PORT'] ?? '3306',
        $_ENV['DB_DATABASE'] ?? 'alingai'
    );
    
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "连接数据库: " . $_ENV['DB_HOST'] . ":" . $_ENV['DB_PORT'] . "/" . $_ENV['DB_DATABASE'] . "\n";
    echo "✅ 数据库连接成功\n\n";
    
    // 读取SQL文件
    $sqlFile = __DIR__ . '/install/sql/ai_agent_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL文件不存在: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "读取SQL文件: {$sqlFile}\n";
    
    // 分割SQL语句
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    echo "发现 " . count($statements) . " 个SQL语句\n\n";
    
    $success = 0;
    $failures = 0;
    
    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) continue;
        
        $statementNum = $index + 1;
        echo "执行语句 {$statementNum}...";
        
        try {
            $pdo->exec($statement);
            echo "✅ 成功\n";
            $success++;
            
            // 检查是否是创建表语句
            if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches)) {
                $tableName = $matches[1];
                echo "创建表: {$tableName}... ✅ 成功\n";
            }
        } catch (PDOException $e) {
            echo "❌ 失败: " . $e->getMessage() . "\n";
            $failures++;
        }
    }
    
    echo "\n=== 迁移完成 ===\n";
    echo "成功: {$success} 个语句\n";
    echo "失败: {$failures} 个语句\n";
    
    if ($failures > 0) {
        echo "⚠️  存在失败的语句，请检查日志\n";
    } else {
        echo "✅ 所有语句执行成功\n";
    }
    
    // 验证表结构
    echo "\n=== 验证AI智能体表结构 ===\n";
    $tables = ['ai_agents', 'ai_enhanced_tasks', 'ai_agent_performance_history', 'ai_task_execution_logs'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE `{$table}`");
            $columns = $stmt->fetchAll();
            echo "✅ {$table} - " . count($columns) . " 列\n";
        } catch (PDOException $e) {
            echo "❌ {$table} - 表不存在\n";
        }
    }
    
    // 检查默认数据
    echo "\n=== 检查默认智能体数据 ===\n";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM ai_agents");
        $result = $stmt->fetch();
        echo "✅ 智能体数量: " . $result['count'] . "\n";
        
        if ($result['count'] > 0) {
            $stmt = $pdo->query("SELECT agent_id, name, type, status FROM ai_agents LIMIT 5");
            $agents = $stmt->fetchAll();
            foreach ($agents as $agent) {
                echo "  - {$agent['agent_id']}: {$agent['name']} ({$agent['type']}) - {$agent['status']}\n";
            }
        }
    } catch (PDOException $e) {
        echo "❌ 无法检查智能体数据: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== 迁移报告 ===\n";
    echo "完成时间: " . date('Y-m-d H:i:s') . "\n";
    echo "总体状态: " . ($failures > 0 ? "⚠️  部分成功" : "✅ 完全成功") . "\n";
    
} catch (Exception $e) {
    echo "❌ 迁移失败: " . $e->getMessage() . "\n";
    exit(1);
}
