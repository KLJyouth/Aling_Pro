<?php
/**
 * AlingAi Pro 安装向导 - 执行安装
 */

// 设置响应头
header("Content-Type: application/json");

// 设置执行时间限制
set_time_limit(300);

// 获取表单数据
$dbType = $_POST["db_type"] ?? "mysql";
$dbHost = $_POST["db_host"] ?? "localhost";
$dbPort = $_POST["db_port"] ?? "3306";
$dbName = $_POST["db_name"] ?? "";
$dbUser = $_POST["db_user"] ?? "";
$dbPassword = $_POST["db_password"] ?? "";

$appName = $_POST["app_name"] ?? "AlingAi Pro";
$appUrl = $_POST["app_url"] ?? "";
$adminEmail = $_POST["admin_email"] ?? "";
$adminPassword = $_POST["admin_password"] ?? "";
$timezone = $_POST["timezone"] ?? "Asia/Shanghai";
$locale = $_POST["locale"] ?? "zh_CN";

// 验证必填字段
if (empty($appName) || empty($appUrl) || empty($adminEmail) || empty($adminPassword)) {
    echo json_encode([
        "success" => false,
        "message" => "请填写所有必填字段"
    ]);
    exit;
}

// 生成应用密钥
$appKey = bin2hex(random_bytes(32));

try {
    // 创建.env文件
    $envContent = "APP_NAME=\"{$appName}\"\n";
    $envContent .= "APP_ENV=production\n";
    $envContent .= "APP_KEY={$appKey}\n";
    $envContent .= "APP_DEBUG=false\n";
    $envContent .= "APP_URL={$appUrl}\n";
    $envContent .= "APP_TIMEZONE={$timezone}\n";
    $envContent .= "APP_LOCALE={$locale}\n\n";
    
    if ($dbType === "mysql") {
        $envContent .= "DB_CONNECTION=mysql\n";
        $envContent .= "DB_HOST={$dbHost}\n";
        $envContent .= "DB_PORT={$dbPort}\n";
        $envContent .= "DB_DATABASE={$dbName}\n";
        $envContent .= "DB_USERNAME={$dbUser}\n";
        $envContent .= "DB_PASSWORD={$dbPassword}\n";
    } else {
        $envContent .= "DB_CONNECTION=sqlite\n";
        $envContent .= "DB_DATABASE=" . __DIR__ . "/../../storage/database/alingai.sqlite\n";
    }
    
    file_put_contents(__DIR__ . "/../../.env", $envContent);
    
    // 执行安装脚本
    require_once __DIR__ . "/../../install_all.php";
    
    // 创建管理员账户
    if ($dbType === "mysql") {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
        $pdo = new PDO($dsn, $dbUser, $dbPassword);
    } else {
        $dbPath = __DIR__ . "/../../storage/database/alingai.sqlite";
        $pdo = new PDO("sqlite:" . $dbPath);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 检查用户表是否存在
    try {
        $pdo->query("SELECT 1 FROM users LIMIT 1");
        $tableExists = true;
    } catch (PDOException $e) {
        $tableExists = false;
    }
    
    if ($tableExists) {
        // 创建管理员账户
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(["admin", $adminEmail, $hashedPassword, "admin", "active"]);
    }
    
    // 安装成功
    echo json_encode([
        "success" => true,
        "message" => "安装成功"
    ]);
} catch (Exception $e) {
    // 安装失败
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
