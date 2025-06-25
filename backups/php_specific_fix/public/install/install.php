<?php
/**
 * AlingAi 安装处理脚本
 * 处理实际的安装操�?
 */

header('Content-Type: application/json'];
header('Access-Control-Allow-Origin: *'];
header('Access-Control-Allow-Methods: POST'];
header('Access-Control-Allow-Headers: Content-Type'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST'] {
    http_response_code(405];
    echo json_encode(['success' => false, 'message' => '仅支�?POST 请求']];
    exit;
}

$input = json_decode(file_get_contents('php://input'], true];

if (!$input] {
    echo json_encode(['success' => false, 'message' => '无效的请求数�?]];
    exit;
}

try {
    // 验证配置数据
    $requiredFields = ['database', 'admin'];
    foreach ($requiredFields as $field] {
        if (!isset($input[$field]]] {
            throw new Exception("缺少必需字段: {$field}"];
        }
    }

    $databaseConfig = $input['database'];
    $adminConfig = $input['admin'];

    // 验证数据库配�?
    validateDatabaseConfig($databaseConfig];
    
    // 验证管理员配�?
    validateAdminConfig($adminConfig];

    // 开始安装流�?
    $installSteps = [
        'create_config' => '创建配置文件',
        'setup_database' => '设置数据�?,
        'create_tables' => '创建数据�?,
        'create_admin' => '创建管理员账�?,
        'finalize' => '完成安装'
    ];

    $progress = [];

    foreach ($installSteps as $step => $description] {
        try {
            $stepResult = executeInstallStep($step, $databaseConfig, $adminConfig];
            $progress[$step] = [
                'success' => true,
                'message' => $stepResult['message'] ?? $description . '完成',
                'details' => $stepResult['details'] ?? null
            ];
        } catch (Exception $e] {
            $progress[$step] = [
                'success' => false,
                'message' => $e->getMessage(],
                'details' => null
            ];
            
            // 如果某个步骤失败，停止安�?
            echo json_encode([
                'success' => false,
                'message' => "安装失败在步�? {$description}",
                'error' => $e->getMessage(],
                'progress' => $progress
            ]];
            exit;
        }
    }

    // 安装成功
    echo json_encode([
        'success' => true,
        'message' => 'AlingAi 安装成功完成�?,
        'progress' => $progress,
        'redirect' => '/admin/login.php'
    ]];

} catch (Exception $e] {
    echo json_encode([
        'success' => false,
        'message' => '安装过程中发生错�?,
        'error' => $e->getMessage(]
    ]];
}

/**
 * 验证数据库配�?
 */
function validateDatabaseConfig($config] {
    $requiredFields = ['type'];
    
    foreach ($requiredFields as $field] {
        if (!isset($config[$field]] || empty($config[$field]]] {
            throw new Exception("数据库配置缺少必需字段: {$field}"];
        }
    }

    if ($config['type'] !== 'sqlite'] {
        $additionalFields = ['host', 'database', 'username'];
        foreach ($additionalFields as $field] {
            if (!isset($config[$field]] || empty($config[$field]]] {
                throw new Exception("数据库配置缺少必需字段: {$field}"];
            }
        }
    }
}

/**
 * 验证管理员配�?
 */
function validateAdminConfig($config] {
    $requiredFields = ['username', 'email', 'password'];
    
    foreach ($requiredFields as $field] {
        if (!isset($config[$field]] || empty($config[$field]]] {
            throw new Exception("管理员配置缺少必需字段: {$field}"];
        }
    }

    // 验证邮箱格式
    if (!filter_var($config['email'], FILTER_VALIDATE_EMAIL]] {
        throw new Exception('管理员邮箱格式无�?];
    }

    // 验证密码强度
    if (strlen($config['password']] <8] {
        throw new Exception('管理员密码长度不能少�?�?];
    }
}

/**
 * 执行安装步骤
 */
function executeInstallStep($step, $databaseConfig, $adminConfig] {
    switch ($step] {
        case 'create_config':
            return createConfigFile($databaseConfig, $adminConfig];
            
        case 'setup_database':
            return setupDatabase($databaseConfig];
            
        case 'create_tables':
            return createDatabaseTables($databaseConfig];
            
        case 'create_admin':
            return createAdminUser($databaseConfig, $adminConfig];
            
        case 'finalize':
            return finalizeInstallation(];
            
        default:
            throw new Exception("未知的安装步�? {$step}"];
    }
}

/**
 * 创建配置文件
 */
function createConfigFile($databaseConfig, $adminConfig] {
    try {
        // 确保配置目录存在
        $configDir = '../../config';
        if (!is_dir($configDir]] {
            if (!mkdir($configDir, 0755, true]] {
                throw new Exception('无法创建配置目录'];
            }
        }
        
        // 生成配置文件内容
        $configContent = "<?php\n";
        $configContent .= "/**\n";
        $configContent .= " * AlingAi Pro 系统配置文件\n";
        $configContent .= " * 自动生成�?" . date('Y-m-d H:i:s'] . "\n";
        $configContent .= " */\n\n";
        $configContent .= "return [\n";
        
        // 数据库配�?
        $configContent .= "    'database' => [\n";
        $configContent .= "        'type' => '" . $databaseConfig['type'] . "',\n";
        
        if ($databaseConfig['type'] === 'sqlite'] {
            $configContent .= "        'path' => '" . ($databaseConfig['path'] ?? '../database/alingai.sqlite'] . "',\n";
        } else {
            $configContent .= "        'host' => '" . $databaseConfig['host'] . "',\n";
            $configContent .= "        'port' => " . ($databaseConfig['port'] ?? 3306] . ",\n";
            $configContent .= "        'database' => '" . $databaseConfig['database'] . "',\n";
            $configContent .= "        'username' => '" . $databaseConfig['username'] . "',\n";
            $configContent .= "        'password' => '" . $databaseConfig['password'] . "',\n";
            $configContent .= "        'charset' => 'utf8mb4',\n";
            $configContent .= "        'collation' => 'utf8mb4_general_ci',\n";
        }
        
        $configContent .= "    ],\n\n";
        
        // 系统配置
        $configContent .= "    'system' => [\n";
        $configContent .= "        'debug' => false,\n";
        $configContent .= "        'timezone' => 'Asia/Shanghai',\n";
        $configContent .= "        'language' => 'zh_CN',\n";
        $configContent .= "        'version' => '5.1.0',\n";
        $configContent .= "        'secret_key' => '" . bin2hex(random_bytes(32]] . "',\n";
        $configContent .= "        'session_lifetime' => 7200,\n";
        $configContent .= "    ],\n\n";
        
        // 安全配置
        $configContent .= "    'security' => [\n";
        $configContent .= "        'password_hash_algo' => PASSWORD_BCRYPT,\n";
        $configContent .= "        'password_hash_options' => ['cost' => 12],\n";
        $configContent .= "        'jwt_secret' => '" . bin2hex(random_bytes(32]] . "',\n";
        $configContent .= "        'jwt_expiration' => 3600,\n";
        $configContent .= "        'api_rate_limit' => 60,\n";
        $configContent .= "        'enable_csrf' => true,\n";
        $configContent .= "    ],\n\n";
        
        // 路径配置
        $configContent .= "    'paths' => [\n";
        $configContent .= "        'base' => dirname(__DIR__],\n";
        $configContent .= "        'public' => dirname(__DIR__] . '/public',\n";
        $configContent .= "        'storage' => dirname(__DIR__] . '/storage',\n";
        $configContent .= "        'logs' => dirname(__DIR__] . '/storage/logs',\n";
        $configContent .= "        'cache' => dirname(__DIR__] . '/storage/cache',\n";
        $configContent .= "        'uploads' => dirname(__DIR__] . '/public/uploads',\n";
        $configContent .= "    ],\n";
        
        $configContent .= "];\n";
        
        // 写入配置文件
        $configFile = $configDir . '/config.php';
        if (file_put_contents($configFile, $configContent] === false] {
            throw new Exception('无法写入配置文件'];
        }
        
        return [
            'message' => '配置文件创建成功',
            'details' => '配置文件已保存到: ' . $configFile
        ];
    } catch (Exception $e] {
        throw new Exception('配置文件创建失败: ' . $e->getMessage(]];
    }
}

/**
 * 设置数据�?
 */
function setupDatabase($config] {
    try {
        if ($config['type'] === 'sqlite'] {
            // SQLite数据�?
            $dbPath = $config['path'] ?? '../../database/alingai.sqlite';
            $dbDir = dirname($dbPath];
            
            if (!is_dir($dbDir]] {
                if (!mkdir($dbDir, 0755, true]] {
                    throw new Exception('无法创建数据库目�?];
                }
            }
            
            $pdo = new PDO("sqlite:{$dbPath}"];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            return [
                'message' => 'SQLite数据库设置成�?,
                'details' => "数据库文件位�? {$dbPath}"
            ];
        } else {
            // MySQL/MariaDB数据�?
            $host = $config['host'];
            $port = $config['port'] ?? 3306;
            $username = $config['username'];
            $password = $config['password'];
            $database = $config['database'];
            
            // 连接数据库服务器（不指定数据库名�?
            $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            // 检查数据库是否存在，如果不存在则创�?
            $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?"];
            $stmt->execute([$database]];
            
            if (!$stmt->fetch(]] {
                $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"];
            }
            
            // 连接到新创建的数据库
            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            return [
                'message' => '数据库设置成�?,
                'details' => "已连接到数据�? {$database}"
            ];
        }
    } catch (PDOException $e] {
        throw new Exception('数据库设置失�? ' . $e->getMessage(]];
    }
}

/**
 * 创建数据库表
 */
function createDatabaseTables($config] {
    try {
        // 读取SQL文件
        $schemaFile = '../database/schema.sql';
        if (!file_exists($schemaFile]] {
            throw new Exception('找不到数据库结构文件: ' . $schemaFile];
        }
        
        $sql = file_get_contents($schemaFile];
        
        if ($config['type'] === 'sqlite'] {
            // SQLite数据�?
            $dbPath = $config['path'] ?? '../../database/alingai.sqlite';
            $pdo = new PDO("sqlite:{$dbPath}"];
            
            // SQLite不支持一次执行多个语句，需要分割SQL
            $statements = explode(';', $sql];
            foreach ($statements as $statement] {
                $statement = trim($statement];
                if (!empty($statement]] {
                    // 替换MySQL特有的语�?
                    $statement = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $statement];
                    $statement = preg_replace('/ENGINE=InnoDB.*?;/i', ';', $statement];
                    $statement = preg_replace('/COLLATE.*?;/i', ';', $statement];
                    $statement = str_replace('CURRENT_TIMESTAMP', "datetime('now']", $statement];
                    $statement = str_replace('ON UPDATE CURRENT_TIMESTAMP', '', $statement];
                    
                    // 移除外键约束（SQLite处理方式不同�?
                    if (stripos($statement, 'FOREIGN KEY'] === false] {
                        $pdo->exec($statement];
                    }
                }
            }
        } else {
            // MySQL/MariaDB数据�?
            $host = $config['host'];
            $port = $config['port'] ?? 3306;
            $username = $config['username'];
            $password = $config['password'];
            $database = $config['database'];
            
            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            // 执行SQL语句
            $pdo->exec($sql];
        }
        
        return [
            'message' => '数据库表创建成功',
            'details' => '已创建所有必要的数据�?
        ];
    } catch (PDOException $e] {
        throw new Exception('创建数据库表失败: ' . $e->getMessage(]];
    }
}

/**
 * 创建管理员用�?
 */
function createAdminUser($databaseConfig, $adminConfig] {
    try {
        $username = $adminConfig['username'];
        $email = $adminConfig['email'];
        $password = $adminConfig['password'];
        
        // 密码哈希
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]];
        
        if ($databaseConfig['type'] === 'sqlite'] {
            // SQLite数据�?
            $dbPath = $databaseConfig['path'] ?? '../../database/alingai.sqlite';
            $pdo = new PDO("sqlite:{$dbPath}"];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            $sql = "INSERT INTO users (username, email, password, role, status, created_at] 
                    VALUES (:username, :email, :password, 'admin', 'active', datetime('now']]";
        } else {
            // MySQL/MariaDB数据�?
            $host = $databaseConfig['host'];
            $port = $databaseConfig['port'] ?? 3306;
            $dbUsername = $databaseConfig['username'];
            $dbPassword = $databaseConfig['password'];
            $database = $databaseConfig['database'];
            
            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $dbUsername, $dbPassword];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            $sql = "INSERT INTO users (username, email, password, role, status, created_at] 
                    VALUES (:username, :email, :password, 'admin', 'active', NOW(]]";
        }
        
        $stmt = $pdo->prepare($sql];
        $stmt->bindParam(':username', $username];
        $stmt->bindParam(':email', $email];
        $stmt->bindParam(':password', $passwordHash];
        $stmt->execute(];
        
        return [
            'message' => '管理员用户创建成�?,
            'details' => "管理员用户名: {$username}"
        ];
    } catch (PDOException $e] {
        throw new Exception('创建管理员用户失�? ' . $e->getMessage(]];
    }
}

/**
 * 完成安装
 */
function finalizeInstallation(] {
    try {
        // 创建安装锁文�?
        $installLockFile = '../../config/install.lock';
        $lockContent = 'Installation completed on ' . date('Y-m-d H:i:s'];
        
        if (file_put_contents($installLockFile, $lockContent] === false] {
            throw new Exception('无法创建安装锁文�?];
        }
        
        // 创建必要的目�?
        $directories = [
            '../../storage',
            '../../storage/logs',
            '../../storage/cache',
            '../../storage/uploads',
            '../../public/uploads',
            '../../public/uploads/images',
            '../../public/uploads/documents',
            '../../public/uploads/temp'
        ];
        
        foreach ($directories as $dir] {
            if (!is_dir($dir]] {
                if (!mkdir($dir, 0755, true]] {
                    throw new Exception('无法创建目录: ' . $dir];
                }
            }
        }
        
        // 创建.htaccess文件保护敏感目录
        $htaccessContent = "Order deny,allow\nDeny from all\n";
        file_put_contents('../../config/.htaccess', $htaccessContent];
        file_put_contents('../../storage/.htaccess', $htaccessContent];
        
        return [
            'message' => '安装完成',
            'details' => '系统已成功安装并准备就绪'
        ];
    } catch (Exception $e] {
        throw new Exception('完成安装失败: ' . $e->getMessage(]];
    }
}
?>

