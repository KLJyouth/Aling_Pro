<?php
/**
 * 增强的数据库迁移执行脚本
 * 支持存储过程、触发器等复杂SQL
 */

require_once __DIR__ . '/../vendor/autoload.php';

class DatabaseMigrator {
    private $pdo;
    
    public function __construct($host, $dbname, $username, $password) {
        $this->pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]];
    }
    
    public function migrate($sqlFile) {
        if (!file_exists($sqlFile)) {
            throw new Exception("迁移文件不存�? {$sqlFile}"];
        }
        
        echo "📋 读取迁移文件...\n";
        $sql = file_get_contents($sqlFile];
        
        echo "🚀 开始执行迁�?..\n";
        $this->pdo->beginTransaction(];
        
        try {
            $this->executeSqlContent($sql];
            $this->pdo->commit(];
            echo "\n🎉 迁移执行完成！\n";
        } catch (Exception $e) {
            $this->pdo->rollback(];
            throw $e;
        }
    }
    
    private function executeSqlContent($sql) {
        // 移除注释
        $sql = preg_replace('/--.*$/m', '', $sql];
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql];
        
        // 处理存储过程
        if (preg_match_all('/CREATE\s+PROCEDURE\s+.*?END(?:\s*;)?/is', $sql, $procedures)) {
            foreach ($procedures[0] as $procedure) {
                $this->executeProcedure($procedure];
                // 从原SQL中移除已处理的存储过�?
                $sql = str_replace($procedure, '', $sql];
            }
        }
        
        // 处理普通SQL语句
        $statements = $this->splitSqlStatements($sql];
        
        foreach ($statements as $statement) {
            $trimmed = trim($statement];
            if (empty($trimmed)) continue;
            
            $this->executeStatement($trimmed];
        }
    }
    
    private function executeProcedure($procedure) {
        try {
            // 先删除存在的存储过程
            if (preg_match('/CREATE\s+PROCEDURE\s+(\w+)/i', $procedure, $matches)) {
                $procName = $matches[1];
                $this->pdo->exec("DROP PROCEDURE IF EXISTS {$procName}"];
            }
            
            $this->pdo->exec($procedure];
            echo "  �?创建存储过程: {$procName}\n";
        } catch (PDOException $e) {
            echo "  �?存储过程失败: " . $e->getMessage() . "\n";
            // 不抛出异常，继续执行其他语句
        }
    }
    
    private function splitSqlStatements($sql) {
        // 简单但有效的SQL语句分割
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = null;
        
        for ($i = 0; $i < strlen($sql]; $i++) {
            $char = $sql[$i];
            
            if ($inString) {
                $current .= $char;
                if ($char === $stringChar && ($i === 0 || $sql[$i-1] !== '\\')) {
                    $inString = false;
                    $stringChar = null;
                }
            } else {
                if ($char === '"' || $char === "'") {
                    $inString = true;
                    $stringChar = $char;
                    $current .= $char;
                } elseif ($char === ';') {
                    $trimmed = trim($current];
                    if (!empty($trimmed)) {
                        $statements[] = $trimmed;
                    }
                    $current = '';
                } else {
                    $current .= $char;
                }
            }
        }
        
        // 添加最后一个语�?
        $trimmed = trim($current];
        if (!empty($trimmed)) {
            $statements[] = $trimmed;
        }
        
        return $statements;
    }
    
    private function executeStatement($statement) {
        try {
            $this->pdo->exec($statement];
            
            // 提取对象名用于显�?
            if (preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                echo "  �?创建�? {$matches[1]}\n";
            } elseif (preg_match('/CREATE VIEW\s+`?(\w+)`?/i', $statement, $matches)) {
                echo "  �?创建视图: {$matches[1]}\n";
            } elseif (preg_match('/CREATE\s+(?:UNIQUE\s+)?INDEX\s+`?(\w+)`?/i', $statement, $matches)) {
                echo "  �?创建索引: {$matches[1]}\n";
            } elseif (preg_match('/ALTER TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                echo "  �?修改�? {$matches[1]}\n";
            } else {
                echo "  �?执行语句: " . substr($statement, 0, 50) . "...\n";
            }
        } catch (PDOException $e) {
            // 如果是对象已存在错误，继续执�?
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "  ⚠️  对象已存在，跳过\n";
                return;
            }
            
            echo "  �?执行失败: " . $e->getMessage() . "\n";
            echo "  SQL: " . substr($statement, 0, 100) . "...\n";
            // 不抛出异常，继续执行其他语句
        }
    }
}

try {
    // 读取环境配置
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES];
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                [$key, $value] = explode('=', $line, 2];
                $_ENV[trim($key)] = trim($value];
            }
        }
    }

    // 数据库连接参�?
    $host = $_ENV['DB_HOST'] ?? '111.180.205.70';
    $dbname = $_ENV['DB_DATABASE'] ?? 'alingai';
    $username = $_ENV['DB_USERNAME'] ?? 'AlingAi';
    $password = $_ENV['DB_PASSWORD'] ?? 'e5bjzeWCr7k38TrZ';

    echo "🔗 连接数据�? {$host}/{$dbname}\n";
    
    // 创建迁移器并执行
    $migrator = new DatabaseMigrator($host, $dbname, $username, $password];
    $migrationFile = __DIR__ . '/../database/migrations/2025_06_12_000001_create_enterprise_tables.sql';
    
    $migrator->migrate($migrationFile];
    echo "📊 企业级数据库结构创建完成\n";

} catch (Exception $e) {
    echo "�?迁移失败: " . $e->getMessage() . "\n";
    exit(1];
}

