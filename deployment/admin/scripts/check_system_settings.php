<?php
/**
 * 检查系统设置表结构和数据
 */

require_once 'vendor/autoload.php';
require_once 'src/Utils/EnvLoader.php';

\AlingAi\Utils\EnvLoader::load(__DIR__ . '/.env');

try {
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'] . ';dbname=' . $_ENV['DB_DATABASE'] . ';charset=utf8mb4',
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== system_settings 表结构 ===\n";
    $stmt = $pdo->query('DESCRIBE system_settings');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "字段: " . $row['Field'] . " | 类型: " . $row['Type'] . " | 允许空: " . $row['Null'] . "\n";
    }
    
    echo "\n=== 现有数据 ===\n";
    $stmt = $pdo->query('SELECT * FROM system_settings LIMIT 10');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\n=== 检查字段名称 ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM system_settings LIKE '%key%'");
    echo "包含 'key' 的字段:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
} catch (Exception $e) {
    echo '错误: ' . $e->getMessage() . "\n";
}