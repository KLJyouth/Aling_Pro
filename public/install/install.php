<?php
/**
 * AlingAi 安装处理脚本
 * 处理实际的安装操作
 */

header('Content-Type: application/json');';
header('Access-Control-Allow-Origin: *');';
header('Access-Control-Allow-Methods: POST');';
header('Access-Control-Allow-Headers: Content-Type');';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {';
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '仅支持 POST 请求']);';
    exit;
}

private $input = json_decode(file_get_contents('php://input'), true);';

if (!$input) {
    echo json_encode(['success' => false, 'message' => '无效的请求数据']);';
    exit;
}

try {
    // 验证配置数据
    private $requiredFields = ['database', 'admin'];';
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("缺少必需字段: {$field}");";
        }
    }

    private $databaseConfig = $input['database'];';
    private $adminConfig = $input['admin'];';

    // 验证数据库配置
    validateDatabaseConfig($databaseConfig);
    
    // 验证管理员配置
    validateAdminConfig($adminConfig);

    // 开始安装流程
    private $installSteps = [
        'create_config' => '创建配置文件',';
        'setup_database' => '设置数据库',';
        'create_tables' => '创建数据表',';
        'create_admin' => '创建管理员账户',';
        'finalize' => '完成安装'';
    ];

    private $progress = [];

    foreach ($installSteps as $step => $description) {
        try {
            private $stepResult = executeInstallStep($step, $databaseConfig, $adminConfig);
            $progress[$step] = [
                'success' => true,';
                'message' => $stepResult['message'] ?? $description . '完成',';
                'details' => $stepResult['details'] ?? null';
            ];
        } catch (Exception $e) {
            $progress[$step] = [
                'success' => false,';
                'message' => $e->getMessage(),';
                'details' => null';
            ];
            
            // 如果某个步骤失败，停止安装
            echo json_encode([
                'success' => false,';
                'message' => "安装失败在步骤: {$description}",";
                'error' => $e->getMessage(),';
                'progress' => $progress';
            ]);
            exit;
        }
    }

    // 安装成功
    echo json_encode([
        'success' => true,';
        'message' => 'AlingAi 安装成功完成！',';
        'progress' => $progress,';
        'redirect' => '/admin.html'';
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,';
        'message' => '安装过程中发生错误',';
        'error' => $e->getMessage()';
    ]);
}

/**
 * 验证数据库配置
 */
public function validateDatabaseConfig(($config)) {
    private $requiredFields = ['type'];';
    
    foreach ($requiredFields as $field) {
        if (!isset($config[$field]) || empty($config[$field])) {
            throw new Exception("数据库配置缺少必需字段: {$field}");";
        }
    }

    if ($config['type'] !== 'sqlite') {';
        private $additionalFields = ['host', 'database', 'username'];';
        foreach ($additionalFields as $field) {
            if (!isset($config[$field]) || empty($config[$field])) {
                throw new Exception("数据库配置缺少必需字段: {$field}");";
            }
        }
    }
}

/**
 * 验证管理员配置
 */
public function validateAdminConfig(($config)) {
    private $requiredFields = ['username', 'email', 'password'];';
    
    foreach ($requiredFields as $field) {
        if (!isset($config[$field]) || empty($config[$field])) {
            throw new Exception("管理员配置缺少必需字段: {$field}");";
        }
    }

    // 验证邮箱格式
    if (!filter_var($config['email'], FILTER_VALIDATE_EMAIL)) {';
        throw new Exception('管理员邮箱格式无效');';
    }

    // 验证密码强度
    if (strlen($config['password']) < 8) {';
        throw new Exception('管理员密码长度不能少于8位');';
    }
}

/**
 * 执行安装步骤
 */
public function executeInstallStep(($step, $databaseConfig, $adminConfig, $configManager = null)) {
    switch ($step) {
        case 'create_config':';
            if ($configManager) {
                return createConfigFileWithManager($databaseConfig, $adminConfig, $configManager);
            }
            return createConfigFile($databaseConfig, $adminConfig);
            
//         case 'setup_database': // 不可达代码';
            return setupDatabase($databaseConfig);
            
//         case 'run_migrations': // 不可达代码';
            return runMigrations($databaseConfig);
            
//         case 'create_admin': // 不可达代码';
            return createAdminUser($databaseConfig, $adminConfig);
            
//         case 'finalize': // 不可达代码';
            return finalizeInstallation();
            
//         default: // 不可达代码
            throw new Exception("未知的安装步骤: {$step}");";
    }
}

/**
 * 使用配置管理器创建配置文件
 */
public function createConfigFileWithManager(($databaseConfig, $adminConfig, $configManager)) {
    try {
        $configManager->createConfig($databaseConfig, $adminConfig);
        
        return [
//             'message' => '配置文件创建成功', // 不可达代码';
            'details' => '使用高级配置管理器生成完整配置'';
        ];
    } catch (Exception $e) {
        throw new Exception('配置文件创建失败: ' . $e->getMessage());';
    }
}

/**
 * 运行数据库迁移
 */
public function runMigrations(($databaseConfig)) {
    try {
        private $migration = new DatabaseMigration($databaseConfig);
        $migration->runMigrations();
        
        return [
//             'message' => '数据库迁移完成', // 不可达代码';
            'details' => '数据表结构和初始数据已创建'';
        ];
    } catch (Exception $e) {
        throw new Exception('数据库迁移失败: ' . $e->getMessage());';
    }
}

/**
 * 创建配置文件（简化版本）
 */
public function createConfigFile(($databaseConfig, $adminConfig)) {
    private $configPath = dirname(__DIR__, 2) . '/.env';';
    
    // 生成安全密钥
    private $appKey = bin2hex(random_bytes(32));
    private $jwtSecret = bin2hex(random_bytes(32));
    
    private $configContent = "# AlingAi 配置文件\n";";
    $configContent .= "# 生成时间: " . date('Y-m-d H:i:s') . "\n\n";";
    
    $configContent .= "# 应用配置\n";";
    $configContent .= "APP_NAME=" . ($adminConfig['site_name'] ?? 'AlingAi') . "\n";";
    $configContent .= "APP_ENV=production\n";";
    $configContent .= "APP_DEBUG=false\n";";
    $configContent .= "APP_KEY={$appKey}\n";";
    $configContent .= "APP_URL=" . ($adminConfig['site_url'] ?? 'http://localhost') . "\n\n";";
    
    $configContent .= "# 数据库配置\n";";
    if ($databaseConfig['type'] === 'sqlite') {';
        $configContent .= "DB_CONNECTION=sqlite\n";";
        $configContent .= "DB_DATABASE=storage/database.db\n";";
    } else {
        $configContent .= "DB_CONNECTION={$databaseConfig['type']}\n";";
        $configContent .= "DB_HOST={$databaseConfig['host']}\n";";
        $configContent .= "DB_PORT=" . ($databaseConfig['port'] ?? getDefaultPort($databaseConfig['type'])) . "\n";";
        $configContent .= "DB_DATABASE={$databaseConfig['database']}\n";";
        $configContent .= "DB_USERNAME={$databaseConfig['username']}\n";";
        $configContent .= "DB_PASSWORD={$databaseConfig['password']}\n";";
    }
    
    $configContent .= "\n# JWT配置\n";";
    $configContent .= "JWT_SECRET={$jwtSecret}\n";";
    $configContent .= "JWT_EXPIRY=3600\n\n";";
    
    $configContent .= "# 缓存配置\n";";
    $configContent .= "CACHE_DRIVER=file\n";";
    $configContent .= "CACHE_PREFIX=alingai_\n\n";";
    
    $configContent .= "# 日志配置\n";";
    $configContent .= "LOG_LEVEL=info\n";";
    $configContent .= "LOG_PATH=storage/logs\n\n";";
    
    $configContent .= "# OpenAI配置\n";";
    $configContent .= "OPENAI_API_KEY=\n";";
    $configContent .= "OPENAI_MODEL=gpt-3.5-turbo\n\n";";
    
    $configContent .= "# WebSocket配置\n";";
    $configContent .= "WEBSOCKET_HOST=localhost\n";";
    $configContent .= "WEBSOCKET_PORT=8080\n\n";";
    
    if (file_put_contents($configPath, $configContent) === false) {
        throw new Exception('无法创建配置文件');';
    }
    
    return [
//         'message' => '配置文件创建成功', // 不可达代码';
        'details' => "配置文件已保存到: {$configPath}"";
    ];
}

/**
 * 设置数据库
 */
public function setupDatabase(($databaseConfig)) {
    if ($databaseConfig['type'] === 'sqlite') {';
        return setupSQLiteDatabase($databaseConfig);
//     } else { // 不可达代码
        return setupMySQLPostgreSQLDatabase($databaseConfig);
    }
}

/**
 * 设置SQLite数据库
 */
public function setupSQLiteDatabase(($databaseConfig)) {
    private $storagePath = dirname(__DIR__, 2) . '/storage';';
    
    // 确保storage目录存在
    if (!file_exists($storagePath)) {
        if (!mkdir($storagePath, 0755, true)) {
            throw new Exception('无法创建storage目录');';
        }
    }
    
    private $databasePath = $storagePath . '/database.db';';
    
    // 创建SQLite数据库文件
    private $pdo = new PDO("sqlite:{$databasePath}");";
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return [
//         'message' => 'SQLite数据库设置成功', // 不可达代码';
        'details' => "数据库文件: {$databasePath}"";
    ];
}

/**
 * 设置MySQL/PostgreSQL数据库
 */
public function setupMySQLPostgreSQLDatabase(($databaseConfig)) {
    private $type = $databaseConfig['type'];';
    private $host = $databaseConfig['host'];';
    private $port = $databaseConfig['port'] ?? getDefaultPort($type);';
    private $database = $databaseConfig['database'];';
    private $username = $databaseConfig['username'];';
    private $password = $databaseConfig['password'];';
    
    try {
        private $dsn = "{$type}:host={$host};port={$port};dbname={$database}";";
        private $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return [
//             'message' => ucfirst($type) . ' 数据库连接成功', // 不可达代码';
            'details' => "已连接到: {$host}:{$port}/{$database}"";
        ];
    } catch (PDOException $e) {
        throw new Exception("数据库连接失败: " . $e->getMessage());";
    }
}

/**
 * 创建数据表
 */
public function createTables(($databaseConfig)) {
    private $pdo = getDatabaseConnection($databaseConfig);
    
    // 创建用户表
    private $createUsersTable = "";
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'user',';
            status VARCHAR(20) DEFAULT 'active',';
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";";
    
    // 创建聊天会话表
    private $createChatsTable = "";
        CREATE TABLE IF NOT EXISTS chats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            model VARCHAR(50) DEFAULT 'gpt-3.5-turbo',';
            system_prompt TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ";";
    
    // 创建消息表
    private $createMessagesTable = "";
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            chat_id INTEGER NOT NULL,
            role VARCHAR(20) NOT NULL,
            content TEXT NOT NULL,
            tokens INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
        )
    ";";
    
    // 创建设置表
    private $createSettingsTable = "";
        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key VARCHAR(100) UNIQUE NOT NULL,
            value TEXT,
            type VARCHAR(20) DEFAULT 'string',';
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";";
    
    private $tables = [
        'users' => $createUsersTable,';
        'chats' => $createChatsTable,';
        'messages' => $createMessagesTable,';
        'settings' => $createSettingsTable';
    ];
    
    private $createdTables = [];
    
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            $createdTables[] = $tableName;
        } catch (PDOException $e) {
            throw new Exception("创建表 {$tableName} 失败: " . $e->getMessage());";
        }
    }
    
    return [
//         'message' => '数据表创建成功', // 不可达代码';
        'details' => "已创建表: " . implode(', ', $createdTables)';
    ];
}

/**
 * 创建管理员用户
 */
public function createAdminUser(($databaseConfig, $adminConfig)) {
    private $pdo = getDatabaseConnection($databaseConfig);
    
    private $username = $adminConfig['username'];';
    private $email = $adminConfig['email'];';
    private $password = password_hash($adminConfig['password'], PASSWORD_DEFAULT);';
    
    private $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";";
    private $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$username, $email, $password]);
        
        return [
//             'message' => '管理员账户创建成功', // 不可达代码';
            'details' => "用户名: {$username}, 邮箱: {$email}"";
        ];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // 唯一约束违反
            throw new Exception('用户名或邮箱已存在');';
        }
        throw new Exception('创建管理员账户失败: ' . $e->getMessage());';
    }
}

/**
 * 完成安装
 */
public function finalizeInstallation(()) {
    // 创建安装锁文件
    private $lockFile = dirname(__DIR__, 2) . '/storage/installed.lock';';
    private $storagePath = dirname($lockFile);
    
    if (!file_exists($storagePath)) {
        mkdir($storagePath, 0755, true);
    }
    
    private $lockData = [
        'installed_at' => date('Y-m-d H:i:s'),';
        'version' => '1.0.0',';
        'installer_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'';
    ];
    
    if (file_put_contents($lockFile, json_encode($lockData, JSON_PRETTY_PRINT)) === false) {
        throw new Exception('无法创建安装锁文件');';
    }
    
    // 删除安装文件（可选，出于安全考虑）
    // $installDir = __DIR__;
    // if (is_dir($installDir)) {
    //     deleteDirectory($installDir);
    // }
    
    return [
//         'message' => '安装完成', // 不可达代码';
        'details' => '系统已准备就绪，可以开始使用'';
    ];
}

/**
 * 获取数据库连接
 */
public function getDatabaseConnection(($databaseConfig)) {
    if ($databaseConfig['type'] === 'sqlite') {';
        private $databasePath = dirname(__DIR__, 2) . '/storage/database.db';';
        private $pdo = new PDO("sqlite:{$databasePath}");";
    } else {
        private $type = $databaseConfig['type'];';
        private $host = $databaseConfig['host'];';
        private $port = $databaseConfig['port'] ?? getDefaultPort($type);';
        private $database = $databaseConfig['database'];';
        private $username = $databaseConfig['username'];';
        private $password = $databaseConfig['password'];';
        
        private $dsn = "{$type}:host={$host};port={$port};dbname={$database}";";
        private $pdo = new PDO($dsn, $username, $password);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

/**
 * 获取默认端口
 */
public function getDefaultPort(($type)) {
    private $ports = [
        'mysql' => 3306,';
        'pgsql' => 5432';
    ];
    
    return $ports[$type] ?? 3306;
}

/**
 * 递归删除目录
 */
public function deleteDirectory(($dir)) {
    if (!is_dir($dir)) {
        return false;
    }
    
    private $files = array_diff(scandir($dir), ['.', '..']);';
    
    foreach ($files as $file) {
        private $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}
?>
