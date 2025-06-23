<?php
require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 生产数据库连接和用户验证测试\n";
echo "====================================\n";

try {
    // 加载.env文件
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($envLines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '//') === 0) continue;
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!empty($key)) {
                    $_ENV[$key] = $value;
                }
            }
        }
        echo "✅ 环境配置加载成功\n";
    }
    
    // 使用生产数据库配置
    $host = $_ENV['DB_HOST'] ?? '111.180.205.70';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'alingai';
    $username = $_ENV['DB_USERNAME'] ?? 'AlingAi';
    $password = $_ENV['DB_PASSWORD'] ?? 'e5bjzeWCr7k38TrZ';
    
    echo "🗄️ 连接生产数据库: {$host}:{$port}/{$database}\n";
    echo "👤 用户名: {$username}\n";
    
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "✅ 生产数据库连接成功\n";
    
    // 检查用户表是否存在
    try {
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "📊 用户数量: $userCount\n";
        
        if ($userCount == 0) {
            echo "📝 创建测试用户...\n";
            $hashedPassword = password_hash('test123456', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (email, password, username, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([
                'test@example.com',
                $hashedPassword,
                'testuser',
                'user',
                'active'
            ]);
            
            if ($result) {
                echo "✅ 测试用户创建成功\n";
                echo "   邮箱: test@example.com\n";
                echo "   密码: test123456\n";
            } else {
                echo "❌ 测试用户创建失败\n";
            }
        } else {
            echo "📋 现有用户列表:\n";
            $users = $pdo->query("SELECT id, email, username, role, status, created_at FROM users LIMIT 5")->fetchAll();
            foreach ($users as $user) {
                echo "   ID:{$user['id']} | {$user['email']} | {$user['username']} | {$user['role']} | {$user['status']} | {$user['created_at']}\n";
            }
        }
        
        // 测试密码验证
        echo "\n🔐 测试密码验证:\n";
        $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch();
        
        if ($user) {
            $isValid = password_verify('test123456', $user['password']);
            echo "   用户ID: {$user['id']}\n";
            echo "   邮箱: {$user['email']}\n";
            echo "   密码验证结果: " . ($isValid ? "✅ 正确" : "❌ 错误") . "\n";
        } else {
            echo "   ⚠️ 找不到测试用户，尝试查找其他用户...\n";
            $stmt = $pdo->prepare("SELECT id, email FROM users LIMIT 1");
            $stmt->execute();
            $anyUser = $stmt->fetch();
            if ($anyUser) {
                echo "   找到用户: ID:{$anyUser['id']} | {$anyUser['email']}\n";
            }
        }
        
        // 测试表结构
        echo "\n📋 用户表结构:\n";
        $columns = $pdo->query("DESCRIBE users")->fetchAll();
        foreach ($columns as $column) {
            echo "   {$column['Field']} | {$column['Type']} | {$column['Null']} | {$column['Key']}\n";
        }
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            echo "⚠️ 用户表不存在，正在创建...\n";
            
            // 创建用户表
            $createTableSQL = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                username VARCHAR(255),
                name VARCHAR(255),
                role VARCHAR(50) DEFAULT 'user',
                status VARCHAR(20) DEFAULT 'active',
                permissions TEXT DEFAULT '[]',
                avatar VARCHAR(500),
                phone VARCHAR(20),
                last_login_at TIMESTAMP NULL,
                login_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_email (email),
                INDEX idx_status (status),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            $pdo->exec($createTableSQL);
            echo "✅ 用户表创建成功\n";
            
            // 创建测试用户
            echo "📝 创建测试用户...\n";
            $hashedPassword = password_hash('test123456', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (email, password, username, role, status) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                'test@example.com',
                $hashedPassword,
                'testuser',
                'user',
                'active'
            ]);
            
            if ($result) {
                echo "✅ 测试用户创建成功\n";
                echo "   邮箱: test@example.com\n";
                echo "   密码: test123456\n";
            }
        } else {
            throw $e;
        }
    }
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "   文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if ($e instanceof PDOException) {
        echo "   PDO错误代码: " . $e->getCode() . "\n";
        if (strpos($e->getMessage(), 'Connection refused') !== false) {
            echo "   💡 建议: 检查数据库服务是否运行，防火墙设置是否允许连接\n";
        }
    }
}
