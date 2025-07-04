<?php
/**
 * AlingAi Pro 安装向导 - 测试数据库连接
 */

// 设置响应头
header("Content-Type: application/json");

// 获取表单数据
$dbType = $_POST["db_type"] ?? "mysql";
$dbHost = $_POST["db_host"] ?? "localhost";
$dbPort = $_POST["db_port"] ?? "3306";
$dbName = $_POST["db_name"] ?? "";
$dbUser = $_POST["db_user"] ?? "";
$dbPassword = $_POST["db_password"] ?? "";

// 测试连接
try {
    if ($dbType === "mysql") {
        // 测试MySQL连接
        $dsn = "mysql:host={$dbHost};port={$dbPort}";
        $pdo = new PDO($dsn, $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 检查数据库是否存在
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = " . $pdo->quote($dbName));
        $dbExists = $stmt->fetchColumn();
        
        if (!$dbExists) {
            // 尝试创建数据库
            $pdo->exec("CREATE DATABASE " . $pdo->quote($dbName) . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        
        // 测试连接到指定数据库
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
        $pdo = new PDO($dsn, $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } else {
        // 测试SQLite连接
        $dbPath = __DIR__ . "/../../storage/database/alingai.sqlite";
        $dbDir = dirname($dbPath);
        
        // 确保目录存在
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        // 测试连接
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    // 连接成功
    echo json_encode([
        "success" => true,
        "message" => "数据库连接成功"
    ]);
} catch (PDOException $e) {
    // 连接失败
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
