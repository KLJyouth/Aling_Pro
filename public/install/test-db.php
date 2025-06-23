<?php
/**
 * AlingAi Pro 安装向导 - 数据库连接测试脚本
 * 测试数据库连接并返回结果
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 处理OPTIONS请求（CORS预检）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 只允许POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '仅支持POST请求']);
    exit;
}

// 获取请求数据
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => '无效的请求数据']);
    exit;
}

// 验证数据库配置
if (!isset($input['type'])) {
    echo json_encode(['success' => false, 'message' => '缺少数据库类型']);
    exit;
}

try {
    $dbType = $input['type'];
    
    if ($dbType === 'sqlite') {
        // 测试SQLite连接
        testSQLiteConnection();
    } else if ($dbType === 'mysql') {
        // 验证MySQL连接参数
        if (!isset($input['host']) || !isset($input['database']) || !isset($input['username'])) {
            echo json_encode(['success' => false, 'message' => '缺少必要的数据库连接参数']);
            exit;
        }
        
        // 测试MySQL连接
        testMySQLConnection($input);
    } else {
        echo json_encode(['success' => false, 'message' => '不支持的数据库类型: ' . $dbType]);
        exit;
    }
    
    // 如果没有抛出异常，则连接成功
    echo json_encode(['success' => true, 'message' => '数据库连接成功']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '数据库连接失败: ' . $e->getMessage()]);
}

/**
 * 测试SQLite连接
 */
function testSQLiteConnection() {
    $dbDir = dirname(dirname(__DIR__)) . '/database';
    $dbPath = $dbDir . '/alingai.sqlite';
    
    // 确保目录存在
    if (!is_dir($dbDir)) {
        if (!mkdir($dbDir, 0755, true)) {
            throw new Exception('无法创建数据库目录');
        }
    }
    
    // 连接数据库
    try {
        $pdo = new PDO("sqlite:{$dbPath}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建测试表
        $pdo->exec('CREATE TABLE IF NOT EXISTS test_connection (id INTEGER PRIMARY KEY, test_value TEXT)');
        
        // 插入测试数据
        $pdo->exec('INSERT INTO test_connection (test_value) VALUES ("测试连接成功")');
        
        // 查询测试数据
        $stmt = $pdo->query('SELECT test_value FROM test_connection LIMIT 1');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 删除测试表
        $pdo->exec('DROP TABLE test_connection');
        
        return true;
    } catch (PDOException $e) {
        throw new Exception('SQLite连接失败: ' . $e->getMessage());
    }
}

/**
 * 测试MySQL连接
 */
function testMySQLConnection($config) {
    $host = $config['host'];
    $port = $config['port'] ?? 3306;
    $username = $config['username'];
    $password = $config['password'] ?? '';
    $database = $config['database'];
    
    try {
        // 先尝试连接到数据库服务器
        $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 检查数据库是否存在
        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$database]);
        
        if (!$stmt->fetch()) {
            // 数据库不存在，则创建
            $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        }
        
        // 连接到指定的数据库
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database}", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建测试表
        $pdo->exec('CREATE TABLE IF NOT EXISTS test_connection (id INT AUTO_INCREMENT PRIMARY KEY, test_value VARCHAR(255))');
        
        // 插入测试数据
        $pdo->exec('INSERT INTO test_connection (test_value) VALUES ("测试连接成功")');
        
        // 查询测试数据
        $stmt = $pdo->query('SELECT test_value FROM test_connection LIMIT 1');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 删除测试表
        $pdo->exec('DROP TABLE test_connection');
        
        return true;
    } catch (PDOException $e) {
        throw new Exception('MySQL连接失败: ' . $e->getMessage());
    }
}
