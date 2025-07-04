<?php

/**
 * ð§ AlingAi Pro 5.0 ç¯å¢è®¾ç½®åé®é¢ä¿®å¤å·¥å?
 * èªå¨ä¿®å¤å®æ´æ§æ£æ¥ä¸­åç°çé®é¢?
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
        echo "ââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ\n";
        echo "â?             ð§ ç¯å¢è®¾ç½®åé®é¢ä¿®å¤å·¥å?                       â\n";
        echo "â âââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ£\n";
        echo "â? æ§è¡æ¶é´: " . date('Y-m-d H:i:s') . str_repeat(' ', 25) . "â\n";
        echo "â? é¡¹ç®è·¯å¾: " . substr($this->basePath, 0, 40) . str_repeat(' ', 15) . "â\n";
        echo "ââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ\n\n";
    }
    
    public function runDiagnosticAndFix() {
        echo "ð å¼å§ç¯å¢è¯æ­åä¿®å¤...\n\n";
        
        $this->fixFilePermissions(];
        $this->setupDatabaseEnvironment(];
        $this->optimizePHPConfiguration(];
        $this->setupSecurityEnhancements(];
        $this->createDevelopmentEnvironment(];
        $this->installMissingExtensions(];
        
        $this->generateReport(];
    }
    
    private function fixFilePermissions() {
        echo "ð ä¿®å¤æä»¶æéé®é¢...\n";
        
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
                    // Windowsç¯å¢ - ç§»é¤ææç¨æ·çåæéï¼åªä¿çç®¡çå
                    $result = shell_exec("icacls \"$filePath\" /inheritance:r /grant:r \"%USERNAME%\":F"];
                } else {
                    // Linux/Unixç¯å¢
                    chmod($filePath, 0600];
                }
                echo "   â?æéå·²ä¿®å¤? $file\n";
                $this->fixes[] = "æä»¶æéä¿®å¤: $file";
            }
        }
        echo "\n";
    }
    
    private function setupDatabaseEnvironment() {
        echo "ðï¸?è®¾ç½®æ°æ®åºç¯å¢?..\n";
        
        // åå»ºSQLiteæ°æ®åºä½ä¸ºå¤ç¨æ¹æ¡?
        $sqliteDbPath = $this->basePath . '/database/alingai_pro.sqlite';
        $databaseDir = dirname($sqliteDbPath];
        
        if (!is_dir($databaseDir)) {
            mkdir($databaseDir, 0755, true];
            echo "   â?åå»ºæ°æ®åºç®å½? $databaseDir\n";
        }
        
        // åå»ºSQLiteæ°æ®åºéç½?
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
        echo "   â?æ°æ®åºéç½®å·²æ´æ°ï¼æ·»å SQLiteæ¯æï¼\n";
        
        // åå»ºåºç¡è¡¨ç»æ?
        $this->createBasicDatabaseSchema($sqliteDbPath];
        
        echo "\n";
    }
    
    private function createBasicDatabaseSchema($dbPath) {
        try {
            $pdo = new PDO('sqlite:' . $dbPath];
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            
            // åå»ºåºç¡è¡?
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
                echo "   â?åå»ºè¡? $table\n";
            }
            
            // åå»ºé»è®¤ç®¡çåç¨æ?
            $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(];
            if ($adminExists == 0) {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)"];
                $stmt->execute([
                    'admin',
                    'admin@alingai.pro',
                    password_hash('admin123456', PASSWORD_DEFAULT],
                    'admin'
                ]];
                echo "   â?åå»ºé»è®¤ç®¡çåç¨æ?(admin/admin123456)\n";
            }
            
            $this->fixes[] = "SQLiteæ°æ®åºåå»ºå®æï¼åå«åºç¡è¡¨ç»æ?;
            
        } catch (Exception $e) {
            echo "   â?æ°æ®åºåå»ºå¤±è´? " . $e->getMessage() . "\n";
            $this->issues[] = "æ°æ®åºåå»ºå¤±è´? " . $e->getMessage(];
        }
    }
    
    private function optimizePHPConfiguration() {
        echo "â?ä¼åPHPéç½®...\n";
        
        // åå»ºPHPéç½®æ¨èæä»¶
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
        
        $iniContent = "; AlingAi Pro 5.0 æ¨èçPHPéç½®\n";
        $iniContent .= "; å°è¿äºè®¾ç½®æ·»å å°æ¨ç php.ini æä»¶ä¸­\n\n";
        
        foreach ($phpRecommendations as $setting => $value) {
            $iniContent .= "$setting = $value\n";
            $currentValue = ini_get($setting];
            if ($currentValue !== false) {
                $status = ($currentValue == $value) ? "â? : "â ï¸";
                echo "   $status $setting: å½å=$currentValue, æ¨è=$value\n";
            } else {
                echo "   â ï¸ $setting: æªè®¾ç½? æ¨è=$value\n";
            }
        }
        
        file_put_contents($this->basePath . '/recommended_php.ini', $iniContent];
        echo "   ð PHPéç½®æ¨èå·²ä¿å­å°: recommended_php.ini\n";
        
        // æ£æ¥å³é®æ©å±?
        $requiredExtensions = ['pdo_sqlite', 'json', 'mbstring', 'openssl', 'curl'];
        $missingExtensions = [];
        
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo "   â?æ©å±å·²å è½? $ext\n";
            } else {
                echo "   â?æ©å±ç¼ºå¤±: $ext\n";
                $missingExtensions[] = $ext;
            }
        }
        
        if (!empty($missingExtensions)) {
            $this->issues[] = "ç¼ºå¤±PHPæ©å±: " . implode(', ', $missingExtensions];
        }
        
        echo "\n";
    }
    
    private function setupSecurityEnhancements() {
        echo "ð¡ï¸?è®¾ç½®å®å¨å¢å¼º...\n";
        
        // åå»ºå®å¨éç½®æä»¶
        $securityConfig = [
            'encryption' => [
                'key' => bin2hex(random_bytes(32)],
                'cipher' => 'AES-256-CBC',
            ], 
            'session' => [
                'lifetime' => 7200,
                'secure' => false, // å¼åç¯å¢è®¾ä¸ºfalse
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
        echo "   â?å®å¨éç½®å·²æ´æ°\n";
        
        // åå»º .htaccess æä»¶ï¼å¦ææ¯Apacheï¼?
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
        echo "   â?Apacheå®å¨è§åå·²åå»º\n";
        
        echo "\n";
    }
    
    private function createDevelopmentEnvironment() {
        echo "ð§ åå»ºå¼åç¯å¢éç½?..\n";
        
        // åå»ºå¼åç¯å¢ç .env æä»¶
        $envContent = "# AlingAi Pro 5.0 å¼åç¯å¢éç½®\n";
        $envContent .= "APP_ENV=development\n";
        $envContent .= "APP_DEBUG=true\n";
        $envContent .= "APP_URL=http://localhost\n";
        $envContent .= "APP_KEY=" . bin2hex(random_bytes(32)) . "\n\n";
        $envContent .= "# æ°æ®åºéç½®\n";
        $envContent .= "DB_CONNECTION=sqlite\n";
        $envContent .= "DB_DATABASE=database/alingai_pro.sqlite\n\n";
        $envContent .= "# ç¼å­éç½®\n";
        $envContent .= "CACHE_DRIVER=file\n";
        $envContent .= "SESSION_DRIVER=file\n\n";
        $envContent .= "# é®ä»¶éç½®ï¼å¼åç¯å¢ï¼\n";
        $envContent .= "MAIL_DRIVER=log\n";
        $envContent .= "MAIL_FROM_ADDRESS=noreply@alingai.pro\n";
        $envContent .= "MAIL_FROM_NAME=AlingAi Pro\n\n";
        $envContent .= "# AIæå¡éç½®\n";
        $envContent .= "OPENAI_API_KEY=your_openai_api_key_here\n";
        $envContent .= "ANTHROPIC_API_KEY=your_anthropic_api_key_here\n";
        
        file_put_contents($this->basePath . '/.env', $envContent];
        echo "   â?å¼åç¯å¢éç½®æä»¶å·²åå»º\n";
        
        // åå»ºçäº§ç¯å¢æ¨¡æ¿
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
        echo "   â?çäº§ç¯å¢éç½®æ¨¡æ¿å·²åå»º\n";
        
        echo "\n";
    }
    
    private function installMissingExtensions() {
        echo "ð¦ æ©å±å®è£æå...\n";
        
        $extensionGuide = [
            'gd' => [
                'windows' => 'åæ¶æ³¨é php.ini ä¸­ç ;extension=gd',
                'linux' => 'sudo apt-get install php-gd (Ubuntu/Debian) æ?yum install php-gd (CentOS)',
                'description' => 'å¾åå¤çåè½'
            ], 
            'redis' => [
                'windows' => 'ä¸è½½ php_redis.dll å¹¶æ·»å å° php.ini: extension=redis',
                'linux' => 'sudo apt-get install php-redis (Ubuntu/Debian)',
                'description' => 'é«æ§è½ç¼å­ç³»ç»'
            ], 
            'opcache' => [
                'windows' => 'åæ¶æ³¨é php.ini ä¸­ç ;zend_extension=opcache',
                'linux' => 'éå¸¸å·²åå«ï¼æ£æ?php.ini ä¸­ç opcache è®¾ç½®',
                'description' => 'PHPæä½ç ç¼å­?
            ]
        ];
        
        $guideContent = "# PHPæ©å±å®è£æå\n\n";
        foreach ($extensionGuide as $ext => $info) {
            $guideContent .= "## $ext - {$info['description']}\n";
            $guideContent .= "**Windows:** {$info['windows']}\n";
            $guideContent .= "**Linux:** {$info['linux']}\n\n";
            
            echo "   ð $ext: {$info['description']}\n";
        }
        
        file_put_contents($this->basePath . '/PHP_EXTENSION_INSTALL_GUIDE.md', $guideContent];
        echo "   ð æ©å±å®è£æåå·²ä¿å­å°: PHP_EXTENSION_INSTALL_GUIDE.md\n";
        
        echo "\n";
    }
    
    private function generateReport() {
        $timestamp = date('Y_m_d_H_i_s'];
        $reportFile = $this->basePath . "/ENVIRONMENT_FIX_REPORT_$timestamp.md";
        
        $report = "# ð§ ç¯å¢ä¿®å¤æ¥å\n\n";
        $report .= "**çææ¶é´:** " . date('Y-m-d H:i:s') . "\n";
        $report .= "**é¡¹ç®è·¯å¾:** {$this->basePath}\n\n";
        
        $report .= "## â?å·²å®æçä¿®å¤\n\n";
        foreach ($this->fixes as $fix) {
            $report .= "- $fix\n";
        }
        
        if (!empty($this->issues)) {
            $report .= "\n## â ï¸ éè¦æå¨å¤ççé®é¢\n\n";
            foreach ($this->issues as $issue) {
                $report .= "- $issue\n";
            }
        }
        
        $report .= "\n## ð ä¸ä¸æ­¥æä½\n\n";
        $report .= "1. éå¯Webæå¡å¨ä»¥åºç¨éç½®æ´æ¹\n";
        $report .= "2. å®è£ç¼ºå¤±çPHPæ©å±ï¼åè?PHP_EXTENSION_INSTALL_GUIDE.mdï¼\n";
        $report .= "3. å¨çäº§ç¯å¢ä¸­å¯ç¨HTTPSåå³é­è°è¯æ¨¡å¼\n";
        $report .= "4. è¿è¡ `php scripts/project_integrity_checker.php` éªè¯ä¿®å¤ææ\n";
        $report .= "5. è¿è¡ `php scripts/unified_optimizer.php` è¿è¡å¨é¢ä¼å\n\n";
        
        $report .= "## ð åå»ºçæä»¶\n\n";
        $report .= "- `.env` - å¼åç¯å¢éç½®\n";
        $report .= "- `.env.production` - çäº§ç¯å¢éç½®æ¨¡æ¿\n";
        $report .= "- `database/alingai_pro.sqlite` - SQLiteæ°æ®åº\n";
        $report .= "- `recommended_php.ini` - æ¨èçPHPéç½®\n";
        $report .= "- `public/.htaccess` - Apacheå®å¨è§å\n";
        $report .= "- `PHP_EXTENSION_INSTALL_GUIDE.md` - æ©å±å®è£æå\n";
        
        file_put_contents($reportFile, $report];
        
        echo "ââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ\n";
        echo "â?                   ð ç¯å¢ä¿®å¤å®ææè¦                        â\n";
        echo "â âââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ£\n";
        echo "â? ä¿®å¤é¡¹ç®: " . count($this->fixes) . " ä¸?                                           â\n";
        echo "â? éçé®é¢: " . count($this->issues) . " ä¸?                                           â\n";
        echo "â? æ¥åæä»¶: " . basename($reportFile) . str_repeat(' ', 20) . "â\n";
        echo "ââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ\n\n";
        
        echo "ð ç¯å¢ä¿®å¤å®æï¼è¯·æ¥çæ¥åæä»¶äºè§£è¯¦ç»ä¿¡æ¯ã\n";
    }
}

// æ§è¡ä¿®å¤
$fixer = new EnvironmentSetupAndFixes(];
$fixer->runDiagnosticAndFix(];

?>
