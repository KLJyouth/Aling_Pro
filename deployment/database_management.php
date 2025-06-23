<?php
/**
 * AlingAi Pro 数据库管理脚本
 */

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/src/Utils/EnvLoader.php";

use AlingAi\Services\DatabaseService;
use AlingAi\Utils\EnvLoader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

EnvLoader::load();

echo "=== AlingAi Pro 数据库管理 ===\n\n";

$logger = new Logger("db_mgmt");
$logger->pushHandler(new StreamHandler(__DIR__ . "/storage/logs/db_mgmt.log"));

function askYesNo($question) {
    echo $question . " (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return trim(strtolower($line)) === 'y';
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

try {
    $dbService = new DatabaseService($logger);
    
    echo "数据库连接: ✓ 成功 (类型: " . $dbService->getConnectionType() . ")\n\n";
    
    while (true) {
        echo "选择操作:\n";
        echo "1. 查看数据库状态\n";
        echo "2. 查看表结构信息\n";
        echo "3. 备份数据库\n";
        echo "4. 清理系统日志\n";
        echo "5. 数据库优化\n";
        echo "6. 查看慢查询\n";
        echo "7. 重建文件系统索引\n";
        echo "8. 数据库迁移检查\n";
        echo "9. 返回主菜单\n";
        echo "0. 退出\n";
        echo "请选择 (0-9): ";
        
        $handle = fopen("php://stdin", "r");
        $choice = trim(fgets($handle));
        fclose($handle);
        
        switch ($choice) {
            case "1":
                echo "\n=== 数据库状态信息 ===\n";
                try {
                    $stats = $dbService->getStats();
                    echo "数据库统计信息:\n";
                    foreach ($stats as $key => $value) {
                        if (is_numeric($value)) {
                            $value = number_format($value);
                        }
                        echo "  {$key}: {$value}\n";
                    }
                    
                    // 显示表信息
                    if ($dbService->getConnectionType() === 'mysql') {
                        $pdo = $dbService->getConnection();
                        $stmt = $pdo->query("SHOW TABLE STATUS");
                        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        echo "\n表信息:\n";
                        printf("%-25s %-10s %-15s %-15s\n", "表名", "行数", "数据大小", "索引大小");
                        echo str_repeat("-", 70) . "\n";
                        
                        foreach ($tables as $table) {
                            $dataSize = formatBytes($table['Data_length']);
                            $indexSize = formatBytes($table['Index_length']);
                            printf("%-25s %-10s %-15s %-15s\n", 
                                $table['Name'], 
                                number_format($table['Rows']), 
                                $dataSize, 
                                $indexSize
                            );
                        }
                    }
                } catch (Exception $e) {
                    echo "获取数据库状态失败: " . $e->getMessage() . "\n";
                }
                break;
                
            case "2":
                echo "\n=== 表结构信息 ===\n";
                try {
                    if ($dbService->getConnectionType() === 'mysql') {
                        $pdo = $dbService->getConnection();
                        $stmt = $pdo->query("SHOW TABLES");
                        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        echo "数据库中的表:\n";
                        foreach ($tables as $table) {
                            echo "  - {$table}\n";
                        }
                        
                        echo "\n要查看哪个表的结构？输入表名 (或按回车跳过): ";
                        $handle = fopen("php://stdin", "r");
                        $tableName = trim(fgets($handle));
                        fclose($handle);
                        
                        if (!empty($tableName) && in_array($tableName, $tables)) {
                            $stmt = $pdo->query("DESCRIBE `{$tableName}`");
                            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            echo "\n表 '{$tableName}' 的结构:\n";
                            printf("%-20s %-20s %-10s %-10s %-10s %-20s\n", 
                                "字段名", "类型", "允许NULL", "键", "默认值", "扩展");
                            echo str_repeat("-", 100) . "\n";
                            
                            foreach ($columns as $column) {
                                printf("%-20s %-20s %-10s %-10s %-10s %-20s\n",
                                    $column['Field'],
                                    $column['Type'],
                                    $column['Null'],
                                    $column['Key'],
                                    $column['Default'] ?? 'NULL',
                                    $column['Extra']
                                );
                            }
                        }
                    } else {
                        echo "文件系统数据库模式，查看存储目录结构:\n";
                        $dataDir = __DIR__ . '/storage/data';
                        $files = glob($dataDir . '/*.json');
                        foreach ($files as $file) {
                            $tableName = basename($file, '.json');
                            $data = json_decode(file_get_contents($file), true);
                            $recordCount = count($data['data'] ?? []);
                            echo "  - {$tableName}: {$recordCount} 条记录\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "获取表结构失败: " . $e->getMessage() . "\n";
                }
                break;
                
            case "3":
                echo "\n=== 数据库备份 ===\n";
                try {
                    $backupDir = __DIR__ . '/storage/backups';
                    if (!is_dir($backupDir)) {
                        mkdir($backupDir, 0755, true);
                    }
                    
                    $timestamp = date('Y-m-d_H-i-s');
                    
                    if ($dbService->getConnectionType() === 'mysql') {
                        $backupFile = $backupDir . "/mysql_backup_{$timestamp}.sql";
                        echo "开始 MySQL 数据库备份...\n";
                        
                        $host = $_ENV['DB_HOST'];
                        $database = $_ENV['DB_DATABASE'];
                        $username = $_ENV['DB_USERNAME'];
                        $password = $_ENV['DB_PASSWORD'];
                        
                        $command = "mysqldump -h{$host} -u{$username} -p{$password} {$database} > \"{$backupFile}\"";
                        exec($command, $output, $returnCode);
                        
                        if ($returnCode === 0 && file_exists($backupFile)) {
                            $size = formatBytes(filesize($backupFile));
                            echo "✓ 备份完成: {$backupFile} ({$size})\n";
                        } else {
                            echo "✗ 备份失败\n";
                        }
                    } else {
                        $backupFile = $backupDir . "/filesystem_backup_{$timestamp}.tar.gz";
                        echo "开始文件系统数据库备份...\n";
                        
                        $dataDir = __DIR__ . '/storage/data';
                        $command = "tar -czf \"{$backupFile}\" -C \"" . dirname($dataDir) . "\" data";
                        exec($command, $output, $returnCode);
                        
                        if ($returnCode === 0 && file_exists($backupFile)) {
                            $size = formatBytes(filesize($backupFile));
                            echo "✓ 备份完成: {$backupFile} ({$size})\n";
                        } else {
                            echo "✗ 备份失败\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "备份失败: " . $e->getMessage() . "\n";
                }
                break;
                
            case "4":
                echo "\n=== 清理系统日志 ===\n";
                try {
                    $logDir = __DIR__ . '/storage/logs';
                    $logFiles = glob($logDir . '/*.log');
                    
                    echo "找到 " . count($logFiles) . " 个日志文件\n";
                    
                    if (askYesNo("是否清理超过7天的日志文件？")) {
                        $cleaned = 0;
                        $cutoff = time() - (7 * 24 * 3600);
                        
                        foreach ($logFiles as $logFile) {
                            if (filemtime($logFile) < $cutoff) {
                                unlink($logFile);
                                $cleaned++;
                            }
                        }
                        
                        echo "✓ 已清理 {$cleaned} 个过期日志文件\n";
                    }
                    
                    // 清理数据库中的系统日志
                    if ($dbService->getConnectionType() === 'mysql') {
                        if (askYesNo("是否清理数据库中超过30天的系统日志？")) {
                            $pdo = $dbService->getConnection();
                            $stmt = $pdo->prepare("DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
                            $stmt->execute();
                            $deleted = $stmt->rowCount();
                            echo "✓ 已清理 {$deleted} 条数据库日志记录\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "清理日志失败: " . $e->getMessage() . "\n";
                }
                break;
                
            case "5":
                echo "\n=== 数据库优化 ===\n";
                try {
                    if ($dbService->getConnectionType() === 'mysql') {
                        $pdo = $dbService->getConnection();
                        
                        echo "开始优化数据库表...\n";
                        $stmt = $pdo->query("SHOW TABLES");
                        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        foreach ($tables as $table) {
                            echo "优化表: {$table}...";
                            $pdo->exec("OPTIMIZE TABLE `{$table}`");
                            echo " ✓\n";
                        }
                        
                        echo "✓ 数据库优化完成\n";
                    } else {
                        echo "文件系统数据库优化...\n";
                        $dataDir = __DIR__ . '/storage/data';
                        $files = glob($dataDir . '/*.json');
                        
                        foreach ($files as $file) {
                            $data = json_decode(file_get_contents($file), true);
                            if ($data) {
                                // 重新写入文件以压缩格式
                                file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
                                echo "优化文件: " . basename($file) . " ✓\n";
                            }
                        }
                        
                        echo "✓ 文件系统数据库优化完成\n";
                    }
                } catch (Exception $e) {
                    echo "数据库优化失败: " . $e->getMessage() . "\n";
                }
                break;
                
            case "6":
                echo "\n=== 查看慢查询 ===\n";
                try {
                    if ($dbService->getConnectionType() === 'mysql') {
                        $pdo = $dbService->getConnection();
                        
                        // 检查慢查询日志是否启用
                        $stmt = $pdo->query("SHOW VARIABLES LIKE 'slow_query_log'");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result['Value'] === 'ON') {
                            echo "慢查询日志已启用\n";
                            
                            // 显示慢查询统计
                            $stmt = $pdo->query("SHOW STATUS LIKE 'Slow_queries'");
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo "慢查询数量: " . $result['Value'] . "\n";
                        } else {
                            echo "慢查询日志未启用\n";
                            if (askYesNo("是否启用慢查询日志？")) {
                                $pdo->exec("SET GLOBAL slow_query_log = 'ON'");
                                $pdo->exec("SET GLOBAL long_query_time = 2");
                                echo "✓ 慢查询日志已启用 (阈值: 2秒)\n";
                            }
                        }
                    } else {
                        echo "文件系统数据库模式，查看访问日志:\n";
                        $logFile = __DIR__ . '/storage/logs/database.log';
                        if (file_exists($logFile)) {
                            $lines = file($logFile);
                            $recentLines = array_slice($lines, -10);
                            foreach ($recentLines as $line) {
                                echo $line;
                            }
                        } else {
                            echo "暂无数据库访问日志\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "查看慢查询失败: " . $e->getMessage() . "\n";
                }
                break;
                
            case "7":
                echo "\n=== 重建文件系统索引 ===\n";
                try {
                    $dataDir = __DIR__ . '/storage/data';
                    $indexFile = $dataDir . '/index.json';
                    
                    echo "重建文件系统数据库索引...\n";
                    
                    $index = [];
                    $files = glob($dataDir . '/*.json');
                    
                    foreach ($files as $file) {
                        $tableName = basename($file, '.json');
                        if ($tableName === 'index') continue;
                        
                        $data = json_decode(file_get_contents($file), true);
                        $index[$tableName] = [
                            'file' => $file,
                            'records' => count($data['data'] ?? []),
                            'last_modified' => filemtime($file),
                            'schema' => $data['schema'] ?? []
                        ];
                        
                        echo "索引表: {$tableName} ({$index[$tableName]['records']} 条记录) ✓\n";
                    }
                    
                    file_put_contents($indexFile, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    echo "✓ 文件系统索引重建完成\n";
                    
                } catch (Exception $e) {
                    echo "重建索引失败: " . $e->getMessage() . "\n";
                }
                break;
                
            case "8":
                echo "\n=== 数据库迁移检查 ===\n";
                include __DIR__ . '/check_database_migration.php';
                break;
                
            case "9":
                echo "返回主菜单\n";
                return;
                
            case "0":
                echo "退出数据库管理\n";
                exit(0);
                
            default:
                echo "无效选择，请重新输入。\n";
        }
        
        echo "\n按回车键继续...";
        fgets(STDIN);
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "数据库管理错误: " . $e->getMessage() . "\n";
}
