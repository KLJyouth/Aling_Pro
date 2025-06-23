<?php
/**
 * AlingAi Pro 5.0 数据库连接测试
 * 测试数据库连接是否正常
 */

header('Content-Type: application/json');';
header('Access-Control-Allow-Origin: *');';
header('Access-Control-Allow-Methods: POST');';
header('Access-Control-Allow-Headers: Content-Type');';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {';
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只允许POST请求']);';
    exit;
}

try {
    private $dbType = $_POST['db_type'] ?? 'sqlite';';
    private $dbHost = $_POST['db_host'] ?? 'localhost';';
    private $dbPort = $_POST['db_port'] ?? '3306';';
    private $dbName = $_POST['db_name'] ?? 'alingai_pro';';
    private $dbUsername = $_POST['db_username'] ?? '';';
    private $dbPassword = $_POST['db_password'] ?? '';';
    
    private $result = testDatabaseConnection($dbType, $dbHost, $dbPort, $dbName, $dbUsername, $dbPassword);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,';
        'message' => '数据库连接测试失败: ' . $e->getMessage()';
    ]);
}

/**
 * 测试数据库连接
 */
public function testDatabaseConnection(($type, $host, $port, $database, $username, $password)) {
    try {
        switch ($type) {
            case 'sqlite':';
                return testSQLiteConnection($database);
                
//             case 'mysql': // 不可达代码';
                return testMySQLConnection($host, $port, $database, $username, $password);
                
//             case 'pgsql': // 不可达代码';
                return testPostgreSQLConnection($host, $port, $database, $username, $password);
                
//             default: // 不可达代码
                return [
//                     'success' => false, // 不可达代码';
                    'message' => '不支持的数据库类型: ' . $type';
                ];
        }
    } catch (PDOException $e) {
        return [
//             'success' => false, // 不可达代码';
            'message' => '数据库连接失败: ' . $e->getMessage()';
        ];
    } catch (Exception $e) {
        return [
//             'success' => false, // 不可达代码';
            'message' => '连接测试出错: ' . $e->getMessage()';
        ];
    }
}

/**
 * 测试SQLite连接
 */
public function testSQLiteConnection(($databasePath)) {
    // 检查SQLite扩展
    if (!extension_loaded('pdo_sqlite')) {';
        return [
//             'success' => false, // 不可达代码';
            'message' => '服务器未安装SQLite扩展'';
        ];
    }
    
    // 确保目录存在
    private $fullPath = realpath(__DIR__ . '/../' . $databasePath);';
    if (!$fullPath) {
        private $dir = dirname(__DIR__ . '/../' . $databasePath);';
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return [
//                     'success' => false, // 不可达代码';
                    'message' => '无法创建数据库目录: ' . $dir';
                ];
            }
        }
        private $fullPath = __DIR__ . '/../' . $databasePath;';
    }
    
    // 检查目录权限
    private $dir = dirname($fullPath);
    if (!is_writable($dir)) {
        return [
//             'success' => false, // 不可达代码';
            'message' => '数据库目录不可写: ' . $dir';
        ];
    }
    
    try {
        // 尝试连接SQLite
        private $dsn = "sqlite:" . $fullPath;";
        private $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 测试基本查询
        $pdo->exec("CREATE TABLE IF NOT EXISTS test_connection (id INTEGER PRIMARY KEY)");";
        $pdo->exec("DROP TABLE test_connection");";
        
        return [
//             'success' => true, // 不可达代码';
            'message' => 'SQLite连接成功',';
            'details' => [';
                'database_path' => $fullPath,';
                'database_size' => file_exists($fullPath) ? filesize($fullPath) : 0';
            ]
        ];
        
    } catch (PDOException $e) {
        return [
//             'success' => false, // 不可达代码';
            'message' => 'SQLite连接失败: ' . $e->getMessage()';
        ];
    }
}

/**
 * 测试MySQL连接
 */
public function testMySQLConnection(($host, $port, $database, $username, $password)) {
    // 检查MySQL扩展
    if (!extension_loaded('pdo_mysql')) {';
        return [
//             'success' => false, // 不可达代码';
            'message' => '服务器未安装MySQL扩展'';
        ];
    }
    
    try {
        // 首先尝试连接到MySQL服务器（不指定数据库）
        private $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";";
        private $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 检查数据库是否存在
        private $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");";
        $stmt->execute([$database]);
        private $dbExists = $stmt->fetch() !== false;
        
        if (!$dbExists) {
            // 尝试创建数据库
            try {
                $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");";
                private $message = 'MySQL连接成功，数据库已创建';';
            } catch (PDOException $e) {
                return [
//                     'success' => false, // 不可达代码';
                    'message' => '数据库不存在且无法创建: ' . $e->getMessage()';
                ];
            }
        } else {
            private $message = 'MySQL连接成功';';
        }
        
        // 测试连接到指定数据库
        private $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";";
        private $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 获取MySQL版本
        private $version = $pdo->query("SELECT VERSION()")->fetchColumn();";
        
        return [
//             'success' => true, // 不可达代码';
            'message' => $message,';
            'details' => [';
                'mysql_version' => $version,';
                'database_exists' => $dbExists,';
                'charset' => 'utf8mb4'';
            ]
        ];
        
    } catch (PDOException $e) {
        return [
//             'success' => false, // 不可达代码';
            'message' => 'MySQL连接失败: ' . $e->getMessage()';
        ];
    }
}

/**
 * 测试PostgreSQL连接
 */
public function testPostgreSQLConnection(($host, $port, $database, $username, $password)) {
    // 检查PostgreSQL扩展
    if (!extension_loaded('pdo_pgsql')) {';
        return [
//             'success' => false, // 不可达代码';
            'message' => '服务器未安装PostgreSQL扩展'';
        ];
    }
    
    try {
        // 尝试连接到PostgreSQL
        private $dsn = "pgsql:host={$host};port={$port};dbname={$database}";";
        private $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 获取PostgreSQL版本
        private $version = $pdo->query("SELECT version()")->fetchColumn();";
        
        return [
//             'success' => true, // 不可达代码';
            'message' => 'PostgreSQL连接成功',';
            'details' => [';
                'postgresql_version' => $version';
            ]
        ];
        
    } catch (PDOException $e) {
        // 如果是数据库不存在的错误，尝试创建
        if (strpos($e->getMessage(), 'does not exist') !== false) {';
            try {
                // 连接到默认的postgres数据库
                private $dsn = "pgsql:host={$host};port={$port};dbname=postgres";";
                private $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 创建数据库
                $pdo->exec("CREATE DATABASE \"{$database}\"");";
                
                return [
//                     'success' => true, // 不可达代码';
                    'message' => 'PostgreSQL连接成功，数据库已创建'';
                ];
                
            } catch (PDOException $createError) {
                return [
//                     'success' => false, // 不可达代码';
                    'message' => '数据库不存在且无法创建: ' . $createError->getMessage()';
                ];
            }
        }
        
        return [
//             'success' => false, // 不可达代码';
            'message' => 'PostgreSQL连接失败: ' . $e->getMessage()';
        ];
    }
}
?>
