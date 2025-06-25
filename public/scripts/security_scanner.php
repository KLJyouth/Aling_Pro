<?php
/**
 * AlingAi Pro 安全扫描测试脚本
 * 基于当前配置进行全面安全检�?
 */

class SecurityScanner
{
    private $results = [];
    private $warnings = [];
    private $errors = [];
    
    public function __construct()
    {
        echo "🔍 AlingAi Pro 安全扫描开�?..\n";
        echo "扫描时间: " . date('Y-m-d H:i:s') . "\n\n";
    }
    
    public function runFullSecurityScan()
    {
        $this->checkEnvironmentSecurity(];
        $this->checkDatabaseSecurity(];
        $this->checkFileSecurity(];
        $this->checkNetworkSecurity(];
        $this->checkApplicationSecurity(];
        $this->checkCacheSecurity(];
        $this->generateSecurityReport(];
    }
    
    private function checkEnvironmentSecurity()
    {
        echo "📋 检查环境配置安全�?..\n";
        
        // 检�?.env 文件权限
        if (file_exists('.env')) {
            $perms = fileperms('.env'];
            if ($perms & 0x0004) {  // 其他用户可读
                $this->addError("ENV_PERMS", ".env 文件对其他用户可�?];
            } else {
                $this->addResult("ENV_PERMS", ".env 文件权限安全"];
            }
        }
        
        // 检查调试模�?
        $debug = $_ENV['APP_DEBUG'] ?? 'true';
        if ($debug === 'true') {
            $this->addWarning("DEBUG_MODE", "调试模式在生产环境中应该关闭"];
        } else {
            $this->addResult("DEBUG_MODE", "调试模式已关�?];
        }
        
        // 检�?HTTPS 强制
        $forceHttps = $_ENV['FORCE_HTTPS'] ?? 'false';
        if ($forceHttps !== 'true') {
            $this->addWarning("HTTPS_FORCE", "建议在生产环境中强制使用 HTTPS"];
        } else {
            $this->addResult("HTTPS_FORCE", "HTTPS 强制已启�?];
        }
        
        // 检查会话安�?
        $secureSession = $_ENV['SESSION_SECURE_COOKIE'] ?? 'false';
        if ($secureSession !== 'true') {
            $this->addWarning("SESSION_SECURITY", "会话 Cookie 应启�?Secure 标志"];
        } else {
            $this->addResult("SESSION_SECURITY", "会话 Cookie 安全配置正确"];
        }
        
        echo "  �?环境配置检查完成\n\n";
    }
    
    private function checkDatabaseSecurity()
    {
        echo "🗄�?检查数据库安全�?..\n";
        
        try {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $database = $_ENV['DB_DATABASE'] ?? '';
            
            // 检查数据库连接
            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]];
            
            $this->addResult("DB_CONNECTION", "数据库连接正�?];
            
            // 检查用户权�?
            if ($username === 'root') {
                $this->addWarning("DB_USER_PRIVILEGE", "建议使用专用数据库用户而非 root"];
            } else {
                $this->addResult("DB_USER_PRIVILEGE", "使用专用数据库用�?];
            }
            
            // 检查远程连接安全�?
            if ($host !== '127.0.0.1' && $host !== 'localhost') {
                $this->addWarning("DB_REMOTE_ACCESS", "远程数据库连接，确保网络安全"];
            }
            
            // 检查数据库版本
            $version = $pdo->query('SELECT VERSION()')->fetchColumn(];
            $this->addResult("DB_VERSION", "数据库版�? " . $version];
            
        } catch (Exception $e) {
            $this->addError("DB_CONNECTION", "数据库连接失�? " . $e->getMessage()];
        }
        
        echo "  �?数据库安全检查完成\n\n";
    }
    
    private function checkFileSecurity()
    {
        echo "📁 检查文件系统安全�?..\n";
        
        // 检查敏感文件是否存�?
        $sensitiveFiles = [
            '.env' => '环境配置文件',
            'composer.json' => 'Composer 配置',
            'config/' => '配置目录'
        ];
        
        foreach ($sensitiveFiles as $file => $desc) {
            if (file_exists($file)) {
                // 检查是否在 web 根目录下
                $webAccessible = $this->isWebAccessible($file];
                if ($webAccessible) {
                    $this->addError("WEB_ACCESSIBLE", "{$desc} 可通过 Web 访问"];
                } else {
                    $this->addResult("FILE_PROTECTION", "{$desc} 受保�?];
                }
            }
        }
        
        // 检查上传目录权�?
        $uploadPath = $_ENV['UPLOAD_PATH'] ?? 'storage/uploads';
        if (is_dir($uploadPath)) {
            $perms = fileperms($uploadPath];
            if ($perms & 0x0040) {  // 检查执行权�?
                $this->addWarning("UPLOAD_PERMS", "上传目录不应有执行权�?];
            } else {
                $this->addResult("UPLOAD_SECURITY", "上传目录权限安全"];
            }
        }
        
        // 检查日志文�?
        $logPath = $_ENV['LOG_FILE_PATH'] ?? './logs/app.log';
        $logDir = dirname($logPath];
        if (is_dir($logDir)) {
            $this->addResult("LOG_DIRECTORY", "日志目录存在"];
        } else {
            $this->addWarning("LOG_DIRECTORY", "日志目录不存在，可能影响错误追踪"];
        }
        
        echo "  �?文件系统安全检查完成\n\n";
    }
    
    private function checkNetworkSecurity()
    {
        echo "🌐 检查网络安全�?..\n";
        
        // 检�?Redis 安全配置
        $redisPassword = $_ENV['REDIS_PASSWORD'] ?? '';
        if (empty($redisPassword)) {
            $this->addError("REDIS_AUTH", "Redis 缺少密码保护"];
        } else {
            $this->addResult("REDIS_AUTH", "Redis 已配置密码保�?];
        }
        
        // 检�?Redis 端口
        $redisPort = $_ENV['REDIS_PORT'] ?? '6379';
        if ($redisPort === '6379') {
            $this->addWarning("REDIS_PORT", "建议修改 Redis 默认端口"];
        } else {
            $this->addResult("REDIS_PORT", "使用非默�?Redis 端口"];
        }
        
        // 检�?WebSocket 安全
        $wsSSL = $_ENV['WEBSOCKET_SSL'] ?? 'false';
        if ($wsSSL !== 'true') {
            $this->addWarning("WS_SSL", "WebSocket 应启�?SSL"];
        } else {
            $this->addResult("WS_SSL", "WebSocket SSL 已启�?];
        }
        
        echo "  �?网络安全检查完成\n\n";
    }
    
    private function checkApplicationSecurity()
    {
        echo "🔐 检查应用程序安全�?..\n";
        
        // 检�?JWT 密钥强度
        $jwtSecret = $_ENV['JWT_SECRET'] ?? '';
        if (strlen($jwtSecret) < 32) {
            $this->addError("JWT_STRENGTH", "JWT 密钥过短，应至少 32 字符"];
        } else {
            $this->addResult("JWT_STRENGTH", "JWT 密钥长度充足"];
        }
        
        // 检查速率限制
        $rateLimit = $_ENV['API_RATE_LIMIT_PER_MINUTE'] ?? '100';
        if ((int)$rateLimit > 100) {
            $this->addWarning("RATE_LIMIT", "API 速率限制较宽松，建议收紧"];
        } else {
            $this->addResult("RATE_LIMIT", "API 速率限制合理"];
        }
        
        // 检查文件上传限�?
        $uploadSize = $_ENV['UPLOAD_MAX_SIZE'] ?? '10485760';
        if ((int)$uploadSize > 10485760) {  // 10MB
            $this->addWarning("UPLOAD_SIZE", "文件上传大小限制较大"];
        } else {
            $this->addResult("UPLOAD_SIZE", "文件上传大小限制合理"];
        }
        
        // 检查允许的文件类型
        $allowedTypes = $_ENV['UPLOAD_ALLOWED_TYPES'] ?? '';
        $dangerousTypes = ['php', 'exe', 'sh', 'bat', 'js'];
        foreach ($dangerousTypes as $type) {
            if (strpos($allowedTypes, $type) !== false) {
                $this->addError("DANGEROUS_UPLOAD", "允许上传危险文件类型: {$type}"];
            }
        }
        
        echo "  �?应用程序安全检查完成\n\n";
    }
      private function checkCacheSecurity()
    {
        echo "💾 检查缓存安全�?..\n";
        
        // 检查Redis扩展是否已安�?
        if (!extension_loaded('redis')) {
            $this->addWarning("REDIS_EXTENSION", "Redis 扩展未安装，跳过Redis连接测试"];
            echo "  ⚠️ Redis 扩展未安装，跳过Redis连接测试\n";
            
            // 检查Redis配置是否存在
            $password = $_ENV['REDIS_PASSWORD'] ?? '';
            if (!empty($password)) {
                $this->addResult("REDIS_AUTH_CONFIG", "Redis 配置中设置了密码"];
            } else {
                $this->addWarning("REDIS_AUTH_CONFIG", "Redis 配置中未设置密码"];
            }
            
            echo "  �?缓存安全检查完成\n\n";
            return;
        }
        
        try {
            // 检�?Redis 连接
            $redis = new Redis(];
            $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
            $port = $_ENV['REDIS_PORT'] ?? '6379';
            $password = $_ENV['REDIS_PASSWORD'] ?? '';
            
            if ($redis->connect($host, $port)) {
                if (!empty($password)) {
                    $redis->auth($password];
                }
                
                $this->addResult("CACHE_CONNECTION", "Redis 缓存连接正常"];
                
                // 检�?Redis 配置
                $info = $redis->info(];
                if (isset($info['redis_version'])) {
                    $this->addResult("REDIS_VERSION", "Redis 版本: " . $info['redis_version']];
                }
                
                $redis->close(];
            } else {
                $this->addError("CACHE_CONNECTION", "Redis 缓存连接失败"];
            }
        } catch (Exception $e) {
            $this->addWarning("REDIS_EXTENSION", "Redis 扩展未安装或配置错误"];
        }
        
        echo "  �?缓存安全检查完成\n\n";
    }
    
    private function isWebAccessible($file)
    {
        // 简单检查文件是否在 public 目录�?web 根目录下
        $publicPaths = ['public/', 'www/', 'html/'];
        foreach ($publicPaths as $path) {
            if (strpos($file, $path) === 0) {
                return true;
            }
        }
        return false;
    }
    
    private function addResult($key, $message)
    {
        $this->results[$key] = $message;
    }
    
    private function addWarning($key, $message)
    {
        $this->warnings[$key] = $message;
    }
    
    private function addError($key, $message)
    {
        $this->errors[$key] = $message;
    }
    
    private function generateSecurityReport()
    {
        echo "📊 生成安全扫描报告...\n";
        echo str_repeat("=", 60) . "\n";
        echo "🛡�? AlingAi Pro 安全扫描报告\n";
        echo str_repeat("=", 60) . "\n";
        echo "扫描时间: " . date('Y-m-d H:i:s') . "\n\n";
        
        echo "📈 扫描统计:\n";
        echo "  �?通过检�? " . count($this->results) . "\n";
        echo "  ⚠️  警告项目: " . count($this->warnings) . "\n";
        echo "  �?错误项目: " . count($this->errors) . "\n\n";
        
        if (!empty($this->errors)) {
            echo "�?发现的安全问�?\n";
            echo str_repeat("-", 40) . "\n";
            foreach ($this->errors as $key => $message) {
                echo "  �?[{$key}] {$message}\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "⚠️  安全警告:\n";
            echo str_repeat("-", 40) . "\n";
            foreach ($this->warnings as $key => $message) {
                echo "  �?[{$key}] {$message}\n";
            }
            echo "\n";
        }
        
        if (!empty($this->results)) {
            echo "�?安全检查通过项目:\n";
            echo str_repeat("-", 40) . "\n";
            foreach ($this->results as $key => $message) {
                echo "  �?[{$key}] {$message}\n";
            }
            echo "\n";
        }
        
        // 安全评分
        $totalChecks = count($this->results) + count($this->warnings) + count($this->errors];
        $securityScore = $totalChecks > 0 ? 
            (count($this->results) + count($this->warnings) * 0.5) / $totalChecks * 100 : 0;
        
        echo "🎯 安全评分: " . round($securityScore, 2) . "%\n";
        
        if ($securityScore >= 90) {
            echo "🟢 安全状�? 优秀\n";
        } elseif ($securityScore >= 75) {
            echo "🟡 安全状�? 良好\n";
        } elseif ($securityScore >= 60) {
            echo "🟠 安全状�? 一般\n";
        } else {
            echo "🔴 安全状�? 需要改进\n";
        }
        
        echo "\n💡 建议优先处理错误项目，然后解决警告项目。\n";
        echo str_repeat("=", 60) . "\n";
        
        // 保存报告到文�?
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'],
            'results' => $this->results,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
            'security_score' => round($securityScore, 2)
        ];
        
        file_put_contents('security_scan_report_' . date('Y_m_d_H_i_s') . '.json', 
                         json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)];
        
        echo "📄 详细报告已保存到: security_scan_report_" . date('Y_m_d_H_i_s') . ".json\n";
    }
}

// 加载环境变量
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES];
    foreach ($lines as $line) {
        if (strpos(trim($line], '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2];
            $name = trim($name];
            $value = trim($value, "\" \t\n\r\0\x0B"];
            $_ENV[$name] = $value;
        }
    }
}

// 执行安全扫描
$scanner = new SecurityScanner(];
$scanner->runFullSecurityScan(];
