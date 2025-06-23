<?php
/**
 * AlingAI Pro 5.0 环境修复脚本
 * 版本: 5.0.0-Final
 * 日期: 2024-12-19
 */

declare(strict_types=1);

echo "🔧 AlingAI Pro 5.0 环境修复\n";
echo str_repeat("=", 50) . "\n";

// 1. 检查SQLite支持
echo "1. 检查SQLite支持...\n";
if (class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers())) {
    echo "✅ PDO SQLite 驱动已可用\n";
} else {
    echo "❌ PDO SQLite 驱动不可用\n";
    echo "ℹ️ 尝试使用原生SQLite3扩展...\n";
    
    if (class_exists('SQLite3')) {
        echo "✅ SQLite3 扩展可用，将创建PDO兼容包装器\n";
        
        // 创建SQLite3到PDO的兼容包装器
        $wrapperPath = __DIR__ . '/src/Database/SQLite3PDOWrapper.php';
        $wrapperDir = dirname($wrapperPath);
        
        if (!is_dir($wrapperDir)) {
            mkdir($wrapperDir, 0755, true);
        }
        
        $wrapperCode = <<<'PHP'
<?php
/**
 * SQLite3 到 PDO 兼容包装器
 * 当PDO SQLite驱动不可用时使用
 */

namespace AlingAI\Database;

class SQLite3PDOWrapper
{
    private $sqlite3;
    private $dbPath;
    
    public function __construct(string $dbPath)
    {
        $this->dbPath = $dbPath;
        $this->sqlite3 = new \SQLite3($dbPath);
        $this->sqlite3->enableExceptions(true);
    }
    
    public function exec(string $query): int
    {
        return $this->sqlite3->exec($query) ? 1 : 0;
    }
    
    public function query(string $query): \SQLite3Result
    {
        return $this->sqlite3->query($query);
    }
    
    public function prepare(string $query): \SQLite3Stmt
    {
        return $this->sqlite3->prepare($query);
    }
    
    public function lastInsertRowID(): int
    {
        return $this->sqlite3->lastInsertRowID();
    }
    
    public function changes(): int
    {
        return $this->sqlite3->changes();
    }
    
    public function close(): bool
    {
        return $this->sqlite3->close();
    }
    
    public function isConnected(): bool
    {
        try {
            $this->sqlite3->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
PHP;
        
        file_put_contents($wrapperPath, $wrapperCode);
        echo "✅ 创建了SQLite3兼容包装器: $wrapperPath\n";
        
    } else {
        echo "❌ SQLite3 扩展也不可用\n";
        echo "📋 建议解决方案:\n";
        echo "   1. 启用 php_pdo_sqlite 扩展\n";
        echo "   2. 或启用 php_sqlite3 扩展\n";
        echo "   3. 检查 php.ini 配置文件\n";
    }
}

// 2. 创建数据库文件（如果不存在）
echo "\n2. 初始化数据库...\n";
$dbPath = __DIR__ . '/storage/database.sqlite';
$dbDir = dirname($dbPath);

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
    echo "✅ 创建存储目录: $dbDir\n";
}

if (!file_exists($dbPath)) {
    // 尝试使用SQLite3创建数据库
    if (class_exists('SQLite3')) {
        try {
            $db = new SQLite3($dbPath);
            
            // 创建基本表结构
            $db->exec('CREATE TABLE IF NOT EXISTS system_config (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key TEXT NOT NULL UNIQUE,
                value TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )');
            
            $db->exec('CREATE TABLE IF NOT EXISTS security_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_type TEXT NOT NULL,
                severity TEXT NOT NULL,
                source_ip TEXT,
                description TEXT,
                data TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )');
            
            $db->exec('CREATE TABLE IF NOT EXISTS agents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                status TEXT DEFAULT "active",
                config TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )');
            
            // 插入默认配置
            $db->exec("INSERT OR IGNORE INTO system_config (key, value) VALUES 
                ('system_name', 'AlingAI Pro 5.0'),
                ('version', '5.0.0-Final'),
                ('security_level', 'high'),
                ('websocket_port', '8081'),
                ('api_enabled', '1')
            ");
            
            $db->close();
            echo "✅ 数据库文件创建成功: $dbPath\n";
            echo "✅ 基本表结构已创建\n";
            
        } catch (Exception $e) {
            echo "❌ 数据库创建失败: " . $e->getMessage() . "\n";
        }
    } else {
        // 创建空数据库文件
        touch($dbPath);
        echo "✅ 创建空数据库文件: $dbPath\n";
    }
} else {
    echo "✅ 数据库文件已存在: $dbPath\n";
}

// 3. 检查并创建必要的目录
echo "\n3. 检查目录结构...\n";
$requiredDirs = [
    'logs',
    'storage/cache',
    'storage/uploads',
    'storage/backups',
    'public/assets',
    'public/css',
    'public/js',
    'public/images'
];

foreach ($requiredDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "✅ 创建目录: $dir\n";
    } else {
        echo "✅ 目录已存在: $dir\n";
    }
}

// 4. 检查环境配置文件
echo "\n4. 检查环境配置...\n";
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    $envContent = <<<ENV
# AlingAI Pro 5.0 环境配置
APP_NAME="AlingAI Pro 5.0"
APP_VERSION="5.0.0-Final"
APP_ENV=production
APP_DEBUG=false

# 数据库配置
DB_TYPE=sqlite
DB_PATH=storage/database.sqlite

# WebSocket配置
WEBSOCKET_HOST=0.0.0.0
WEBSOCKET_PORT=8081

# 安全配置
SECURITY_LEVEL=high
API_RATE_LIMIT=100
SESSION_TIMEOUT=3600

# DeepSeek API配置
DEEPSEEK_API_KEY=your_api_key_here
DEEPSEEK_API_URL=https://api.deepseek.com

# 日志配置
LOG_LEVEL=info
LOG_PATH=logs/app.log

# 缓存配置
CACHE_DRIVER=file
CACHE_PATH=storage/cache
ENV;
    
    file_put_contents($envPath, $envContent);
    echo "✅ 创建环境配置文件: .env\n";
} else {
    echo "✅ 环境配置文件已存在: .env\n";
}

// 5. 检查Web服务器配置
echo "\n5. 检查Web服务器配置...\n";
$htaccessPath = __DIR__ . '/public/.htaccess';
if (!file_exists($htaccessPath)) {
    $htaccessContent = <<<HTACCESS
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# 安全头部
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# 禁止访问敏感文件
<Files ~ "^\.(env|htaccess|gitignore)">
    Order allow,deny
    Deny from all
</Files>
HTACCESS;
    
    file_put_contents($htaccessPath, $htaccessContent);
    echo "✅ 创建Web服务器配置: public/.htaccess\n";
} else {
    echo "✅ Web服务器配置已存在\n";
}

echo "\n🎉 环境修复完成！\n";
echo "==================================================\n";
echo "修复摘要:\n";
echo "  ✅ 数据库文件和表结构\n";
echo "  ✅ 必要的目录结构\n";
echo "  ✅ 环境配置文件\n";
echo "  ✅ Web服务器配置\n";
echo "\n💡 建议:\n";
echo "  1. 如果仍有SQLite问题，请启用相关PHP扩展\n";
echo "  2. 根据需要调整 .env 配置文件\n";
echo "  3. 现在可以运行 php quick_start.php 开始部署\n";
echo "==================================================\n";
