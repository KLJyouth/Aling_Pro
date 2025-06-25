<?php

/**
 * 🔧 AlingAi Pro 5.0 环境设置和问题修复工�?
 * 自动修复完整性检查中发现的问�?
 * 
 * @version 1.0
 * @author AlingAi Team
 * @created 2025-06-11
 */

class EnvironmentSetupAndFixes {
    private $basePath;
    private $issues = [];
    private $fixes = [];
    
    public function __construct($basePath = null) {
        $this->basePath = $basePath ?: dirname(__DIR__];
        $this->initializeReport(];
    }
    
    private function initializeReport() {
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "�?             🔧 环境设置和问题修复工�?                       ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "�? 执行时间: " . date('Y-m-d H:i:s') . str_repeat(' ', 25) . "║\n";
        echo "�? 项目路径: " . substr($this->basePath, 0, 40) . str_repeat(' ', 15) . "║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    }
    
    public function runDiagnosticAndFix() {
        echo "🔍 开始环境诊断和修复...\n\n";
        
        $this->fixFilePermissions(];
        $this->setupDatabaseEnvironment(];
        $this->optimizePHPConfiguration(];
        $this->setupSecurityEnhancements(];
        $this->createDevelopmentEnvironment(];
        $this->installMissingExtensions(];
        
        $this->generateReport(];
    }
    
    private function fixFilePermissions() {
        echo "🔐 修复文件权限问题...\n";
        
        $sensitiveFiles = [
            '.env',
            'config/database.php',
            'config/security.php',
            'config/app.php'
        ];
        
        foreach ($sensitiveFiles as $file) {
            $filePath = $this->basePath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($filePath)) {
                if (DIRECTORY_SEPARATOR === '\\') {
                    // Windows环境 - 移除所有用户的写权限，只保留管理员
                    $result = shell_exec("icacls \"$filePath\" /inheritance:r /grant:r \"%USERNAME%\":F"];
                } else {
                    // Linux/Unix环境
                    chmod($filePath, 0600];
                }
                echo "   �?权限已修�? $file\n";
                $this->fixes[] = "文件权限修复: $file";
            }
        }
        echo "\n";
    }
    
    private function setupDatabaseEnvironment() {
        echo "🗃�?设置数据库环�?..\n";
        
        // 创建SQLite数据库作为备用方�?
        $sqliteDbPath = $this->basePath . '/database/alingai_pro.sqlite';
        $databaseDir = dirname($sqliteDbPath];
        
        if (!is_dir($databaseDir)) {
            mkdir($databaseDir, 0755, true];
            echo "   �?创建数据库目�? $databaseDir\n";
        }
        
        // 创建SQLite数据库配�?
        $sqliteConfig = [
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => $sqliteDbPath,
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ], 
                'mysql' => [
                    'driver' => 'mysql',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'database' => 'alingai_pro',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'options' => [
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_STRINGIFY_FETCHES => false,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ], 
                ]
            ]
        ];
        
        $configFile = $this->basePath . '/config/database.php';
        $configContent = "<?php\n\n/**\n * AlingAi Pro 5.0 - Database Configuration\n * Updated with SQLite fallback\n * Modified: " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($sqliteConfig, true) . ";\n";
        
        file_put_contents($configFile, $configContent];
        echo "   �?数据库配置已更新（添加SQLite支持）\n";
        
        // 创建基础表结�?
        $this->createBasicDatabaseSchema($sqliteDbPath];
        
        echo "\n";
    }
    
    private function createBasicDatabaseSchema($dbPath) {
        try {
            $pdo = new PDO('sqlite:' . $dbPath];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            // 创建基础�?
            $schemas = [
                'users' => "
                    CREATE TABLE IF NOT EXISTS users (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        username VARCHAR(255) UNIQUE NOT NULL,
                        email VARCHAR(255) UNIQUE NOT NULL,
                        password_hash VARCHAR(255) NOT NULL,
                        role VARCHAR(50) DEFAULT 'user',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ",
                'sessions' => "
                    CREATE TABLE IF NOT EXISTS sessions (
                        id VARCHAR(255) PRIMARY KEY,
                        user_id INTEGER,
                        data TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        expires_at DATETIME,
                        FOREIGN KEY (user_id) REFERENCES users(id)
                    )
                ",
                'ai_conversations' => "
                    CREATE TABLE IF NOT EXISTS ai_conversations (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER,
                        title VARCHAR(255],
                        messages TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id)
                    )
                ",
                'system_logs' => "
                    CREATE TABLE IF NOT EXISTS system_logs (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        level VARCHAR(50],
                        message TEXT,
                        context TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                "
            ];
            
            foreach ($schemas as $table => $sql) {
                $pdo->exec($sql];
                echo "   �?创建�? $table\n";
            }
            
            // 创建默认管理员用�?
            $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(];
            if ($adminExists == 0) {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)"];
                $stmt->execute([
                    'admin',
                    'admin@alingai.pro',
                    password_hash('admin123456', PASSWORD_DEFAULT],
                    'admin'
                ]];
                echo "   �?创建默认管理员用�?(admin/admin123456)\n";
            }
            
            $this->fixes[] = "SQLite数据库创建完成，包含基础表结�?;
            
        } catch (Exception $e) {
            echo "   �?数据库创建失�? " . $e->getMessage() . "\n";
            $this->issues[] = "数据库创建失�? " . $e->getMessage(];
        }
    }
    
    private function optimizePHPConfiguration() {
        echo "�?优化PHP配置...\n";
        
        // 创建PHP配置推荐文件
        $phpRecommendations = [
            'memory_limit' => '256M',
            'max_execution_time' => '300',
            'upload_max_filesize' => '32M',
            'post_max_size' => '32M',
            'opcache.enable' => '1',
            'opcache.memory_consumption' => '128',
            'opcache.max_accelerated_files' => '4000',
            'opcache.revalidate_freq' => '2',
        ];
        
        $iniContent = "; AlingAi Pro 5.0 推荐的PHP配置\n";
        $iniContent .= "; 将这些设置添加到您的 php.ini 文件中\n\n";
        
        foreach ($phpRecommendations as $setting => $value) {
            $iniContent .= "$setting = $value\n";
            $currentValue = ini_get($setting];
            if ($currentValue !== false) {
                $status = ($currentValue == $value) ? "�? : "⚠️";
                echo "   $status $setting: 当前=$currentValue, 推荐=$value\n";
            } else {
                echo "   ⚠️ $setting: 未设�? 推荐=$value\n";
            }
        }
        
        file_put_contents($this->basePath . '/recommended_php.ini', $iniContent];
        echo "   📄 PHP配置推荐已保存到: recommended_php.ini\n";
        
        // 检查关键扩�?
        $requiredExtensions = ['pdo_sqlite', 'json', 'mbstring', 'openssl', 'curl'];
        $missingExtensions = [];
        
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo "   �?扩展已加�? $ext\n";
            } else {
                echo "   �?扩展缺失: $ext\n";
                $missingExtensions[] = $ext;
            }
        }
        
        if (!empty($missingExtensions)) {
            $this->issues[] = "缺失PHP扩展: " . implode(', ', $missingExtensions];
        }
        
        echo "\n";
    }
    
    private function setupSecurityEnhancements() {
        echo "🛡�?设置安全增强...\n";
        
        // 创建安全配置文件
        $securityConfig = [
            'encryption' => [
                'key' => bin2hex(random_bytes(32)],
                'cipher' => 'AES-256-CBC',
            ], 
            'session' => [
                'lifetime' => 7200,
                'secure' => false, // 开发环境设为false
                'httponly' => true,
                'samesite' => 'strict',
            ], 
            'csrf' => [
                'enabled' => true,
                'token_lifetime' => 3600,
            ], 
            'rate_limiting' => [
                'enabled' => true,
                'requests_per_minute' => 60,
            ], 
            'content_security_policy' => [
                'enabled' => true,
                'policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';",
            ], 
        ];
        
        $configFile = $this->basePath . '/config/security.php';
        $configContent = "<?php\n\n/**\n * AlingAi Pro 5.0 - Security Configuration\n * Enhanced security settings\n * Updated: " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($securityConfig, true) . ";\n";
        
        file_put_contents($configFile, $configContent];
        echo "   �?安全配置已更新\n";
        
        // 创建 .htaccess 文件（如果是Apache�?
        $htaccessContent = "# AlingAi Pro 5.0 Security Rules\n";
        $htaccessContent .= "RewriteEngine On\n\n";
        $htaccessContent .= "# Force HTTPS (uncomment in production)\n";
        $htaccessContent .= "# RewriteCond %{HTTPS} off\n";
        $htaccessContent .= "# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n\n";
        $htaccessContent .= "# Security Headers\n";
        $htaccessContent .= "Header always set X-Content-Type-Options nosniff\n";
        $htaccessContent .= "Header always set X-Frame-Options DENY\n";
        $htaccessContent .= "Header always set X-XSS-Protection \"1; mode=block\"\n\n";
        $htaccessContent .= "# Hide sensitive files\n";
        $htaccessContent .= "<Files \".env\">\n";
        $htaccessContent .= "    Order allow,deny\n";
        $htaccessContent .= "    Deny from all\n";
        $htaccessContent .= "</Files>\n";
        
        file_put_contents($this->basePath . '/public/.htaccess', $htaccessContent];
        echo "   �?Apache安全规则已创建\n";
        
        echo "\n";
    }
    
    private function createDevelopmentEnvironment() {
        echo "🔧 创建开发环境配�?..\n";
        
        // 创建开发环境的 .env 文件
        $envContent = "# AlingAi Pro 5.0 开发环境配置\n";
        $envContent .= "APP_ENV=development\n";
        $envContent .= "APP_DEBUG=true\n";
        $envContent .= "APP_URL=http://localhost\n";
        $envContent .= "APP_KEY=" . bin2hex(random_bytes(32)) . "\n\n";
        $envContent .= "# 数据库配置\n";
        $envContent .= "DB_CONNECTION=sqlite\n";
        $envContent .= "DB_DATABASE=database/alingai_pro.sqlite\n\n";
        $envContent .= "# 缓存配置\n";
        $envContent .= "CACHE_DRIVER=file\n";
        $envContent .= "SESSION_DRIVER=file\n\n";
        $envContent .= "# 邮件配置（开发环境）\n";
        $envContent .= "MAIL_DRIVER=log\n";
        $envContent .= "MAIL_FROM_ADDRESS=noreply@alingai.pro\n";
        $envContent .= "MAIL_FROM_NAME=AlingAi Pro\n\n";
        $envContent .= "# AI服务配置\n";
        $envContent .= "OPENAI_API_KEY=your_openai_api_key_here\n";
        $envContent .= "ANTHROPIC_API_KEY=your_anthropic_api_key_here\n";
        
        file_put_contents($this->basePath . '/.env', $envContent];
        echo "   �?开发环境配置文件已创建\n";
        
        // 创建生产环境模板
        $envProdContent = str_replace([
            'APP_ENV=development',
            'APP_DEBUG=true',
            'DB_CONNECTION=sqlite',
            'CACHE_DRIVER=file'
        ],  [
            'APP_ENV=production',
            'APP_DEBUG=false',
            'DB_CONNECTION=mysql',
            'CACHE_DRIVER=redis'
        ],  $envContent];
        
        file_put_contents($this->basePath . '/.env.production', $envProdContent];
        echo "   �?生产环境配置模板已创建\n";
        
        echo "\n";
    }
    
    private function installMissingExtensions() {
        echo "📦 扩展安装指南...\n";
        
        $extensionGuide = [
            'gd' => [
                'windows' => '取消注释 php.ini 中的 ;extension=gd',
                'linux' => 'sudo apt-get install php-gd (Ubuntu/Debian) �?yum install php-gd (CentOS)',
                'description' => '图像处理功能'
            ], 
            'redis' => [
                'windows' => '下载 php_redis.dll 并添加到 php.ini: extension=redis',
                'linux' => 'sudo apt-get install php-redis (Ubuntu/Debian)',
                'description' => '高性能缓存系统'
            ], 
            'opcache' => [
                'windows' => '取消注释 php.ini 中的 ;zend_extension=opcache',
                'linux' => '通常已包含，检�?php.ini 中的 opcache 设置',
                'description' => 'PHP操作码缓�?
            ]
        ];
        
        $guideContent = "# PHP扩展安装指南\n\n";
        foreach ($extensionGuide as $ext => $info) {
            $guideContent .= "## $ext - {$info['description']}\n";
            $guideContent .= "**Windows:** {$info['windows']}\n";
            $guideContent .= "**Linux:** {$info['linux']}\n\n";
            
            echo "   📋 $ext: {$info['description']}\n";
        }
        
        file_put_contents($this->basePath . '/PHP_EXTENSION_INSTALL_GUIDE.md', $guideContent];
        echo "   📄 扩展安装指南已保存到: PHP_EXTENSION_INSTALL_GUIDE.md\n";
        
        echo "\n";
    }
    
    private function generateReport() {
        $timestamp = date('Y_m_d_H_i_s'];
        $reportFile = $this->basePath . "/ENVIRONMENT_FIX_REPORT_$timestamp.md";
        
        $report = "# 🔧 环境修复报告\n\n";
        $report .= "**生成时间:** " . date('Y-m-d H:i:s') . "\n";
        $report .= "**项目路径:** {$this->basePath}\n\n";
        
        $report .= "## �?已完成的修复\n\n";
        foreach ($this->fixes as $fix) {
            $report .= "- $fix\n";
        }
        
        if (!empty($this->issues)) {
            $report .= "\n## ⚠️ 需要手动处理的问题\n\n";
            foreach ($this->issues as $issue) {
                $report .= "- $issue\n";
            }
        }
        
        $report .= "\n## 🚀 下一步操作\n\n";
        $report .= "1. 重启Web服务器以应用配置更改\n";
        $report .= "2. 安装缺失的PHP扩展（参�?PHP_EXTENSION_INSTALL_GUIDE.md）\n";
        $report .= "3. 在生产环境中启用HTTPS和关闭调试模式\n";
        $report .= "4. 运行 `php scripts/project_integrity_checker.php` 验证修复效果\n";
        $report .= "5. 运行 `php scripts/unified_optimizer.php` 进行全面优化\n\n";
        
        $report .= "## 📁 创建的文件\n\n";
        $report .= "- `.env` - 开发环境配置\n";
        $report .= "- `.env.production` - 生产环境配置模板\n";
        $report .= "- `database/alingai_pro.sqlite` - SQLite数据库\n";
        $report .= "- `recommended_php.ini` - 推荐的PHP配置\n";
        $report .= "- `public/.htaccess` - Apache安全规则\n";
        $report .= "- `PHP_EXTENSION_INSTALL_GUIDE.md` - 扩展安装指南\n";
        
        file_put_contents($reportFile, $report];
        
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "�?                   📊 环境修复完成摘要                        ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "�? 修复项目: " . count($this->fixes) . " �?                                           ║\n";
        echo "�? 遗留问题: " . count($this->issues) . " �?                                           ║\n";
        echo "�? 报告文件: " . basename($reportFile) . str_repeat(' ', 20) . "║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
        
        echo "🎉 环境修复完成！请查看报告文件了解详细信息。\n";
    }
}

// 执行修复
$fixer = new EnvironmentSetupAndFixes(];
$fixer->runDiagnosticAndFix(];

?>
