<?php
/**
 * 数据库表结构检查脚本
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/EnvLoader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlingAi\Services\DatabaseService;

// 加载环境变量
EnvLoader::load();

echo "=== 数据库表结构检查 ===\n\n";

try {
    $logger = new Logger('db_check');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/storage/logs/db_check.log'));
    
    $dbService = new DatabaseService($logger);
    echo "✓ 数据库连接成功 (类型: " . $dbService->getConnectionType() . ")\n\n";
    
    if ($dbService->getConnectionType() === 'mysql') {
        $connection = $dbService->getConnection();
        
        // 1. 检查数据库中现有的表
        echo "1. 检查现有数据库表...\n";
        $tables = $connection->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            echo "✓ 表: {$table}\n";
        }
        echo "\n";
        
        // 2. 检查每个表的结构
        echo "2. 检查表结构...\n";
        foreach ($tables as $table) {
            echo "表 {$table} 的结构:\n";
            $columns = $connection->query("DESCRIBE {$table}")->fetchAll();
            
            foreach ($columns as $column) {
                echo "  - {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
            }
            echo "\n";
        }
        
        // 3. 测试基本的CRUD操作
        echo "3. 测试基本数据库操作...\n";
        
        // 测试 users 表
        if (in_array('users', $tables)) {
            echo "测试 users 表...\n";
            
            $testUser = [
                'username' => 'db_test_' . time(),
                'email' => 'dbtest@example.com',
                'password' => password_hash('test123', PASSWORD_DEFAULT),
                'level' => 1
            ];
            
            try {
                $result = $dbService->insert('users', $testUser);
                if ($result) {
                    echo "✓ users 表插入测试成功\n";
                    
                    // 查询测试
                    $users = $dbService->findAll('users', ['username' => $testUser['username']]);
                    if (!empty($users)) {
                        echo "✓ users 表查询测试成功\n";
                        
                        // 清理测试数据
                        $stmt = $connection->prepare("DELETE FROM users WHERE username = ?");
                        $stmt->execute([$testUser['username']]);
                        echo "✓ 测试数据清理完成\n";
                    } else {
                        echo "⚠ users 表查询测试失败\n";
                    }
                } else {
                    echo "⚠ users 表插入测试失败\n";
                }
            } catch (Exception $e) {
                echo "⚠ users 表测试失败: " . $e->getMessage() . "\n";
            }
        }
        
        // 4. 检查是否需要创建缺失的表
        echo "\n4. 检查缺失的表...\n";
        $requiredTables = [
            'users', 'chat_sessions', 'chat_messages', 'system_settings', 
            'system_metrics', 'ai_conversations', 'email_logs'
        ];
        
        $missingTables = array_diff($requiredTables, $tables);
        
        if (empty($missingTables)) {
            echo "✓ 所有必需的表都存在\n";
        } else {
            echo "需要创建的表:\n";
            foreach ($missingTables as $table) {
                echo "  - {$table}\n";
            }
            
            echo "\n是否要创建缺失的表？(y/n): ";
            $createTables = trim(fgets(STDIN));
            
            if (strtolower($createTables) === 'y') {
                echo "正在创建缺失的表...\n";
                include __DIR__ . '/check_database_migration.php';
            }
        }
        
    } else {
        echo "当前使用文件系统数据库，检查文件存储...\n";
        
        $dataDir = __DIR__ . '/storage/data';
        $dataFiles = glob($dataDir . '/*.json');
        
        foreach ($dataFiles as $file) {
            $fileName = basename($file);
            $fileSize = round(filesize($file) / 1024, 2);
            echo "✓ 数据文件: {$fileName} ({$fileSize}KB)\n";
        }
    }
    
    echo "\n=== 数据库表结构检查完成 ===\n";
    
} catch (Exception $e) {
    echo "✗ 数据库检查失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
