<?php
/**
 * 生产环境部署就绪检查工具
 * AlingAi Pro - "三完编译" 部署验证系统
 * 
 * 功能：
 * - 系统环境检查
 * - 服务状态验证
 * - 配置文件检查
 * - 性能基准测试
 * - 安全配置验证
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ProductionReadinessChecker {
    private $results = [];
    private $errors = [];
    private $warnings = [];
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
    }
    
    /**
     * 运行完整的就绪检查
     */
    public function run() {
        $this->printHeader();
        
        $this->checkSystemRequirements();
        $this->checkPHPConfiguration();
        $this->checkWebServerConfiguration();
        $this->checkDatabaseConnection();
        $this->checkFilePermissions();
        $this->checkSecurityConfiguration();
        $this->checkPerformanceSettings();
        $this->checkSSLConfiguration();
        $this->checkBackupStrategy();
        $this->checkMonitoringSetup();
        
        $this->generateReport();
    }
    
    /**
     * 系统要求检查
     */
    private function checkSystemRequirements() {
        $this->section("系统要求检查");
        
        // PHP版本检查
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '7.4.0', '>=')) {
            $this->pass("PHP版本: {$phpVersion} ✓");
        } else {
            $this->fail("PHP版本过低: {$phpVersion}，需要 7.4.0+");
        }
        
        // 必需的PHP扩展
        $requiredExtensions = [
            'pdo', 'pdo_mysql', 'json', 'openssl', 'mbstring', 
            'curl', 'gd', 'zip', 'xml', 'hash'
        ];
        
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->pass("PHP扩展 {$ext}: 已安装 ✓");
            } else {
                $this->fail("PHP扩展 {$ext}: 未安装");
            }
        }
        
        // 推荐的PHP扩展
        $recommendedExtensions = ['redis', 'opcache', 'imagick'];
        foreach ($recommendedExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->pass("推荐扩展 {$ext}: 已安装 ✓");
            } else {
                $this->warn("推荐扩展 {$ext}: 未安装（建议安装以提升性能）");
            }
        }
        
        // 内存限制检查
        $memoryLimit = ini_get('memory_limit');
        $memoryBytes = $this->parseMemoryLimit($memoryLimit);
        if ($memoryBytes >= 128 * 1024 * 1024) {
            $this->pass("内存限制: {$memoryLimit} ✓");
        } else {
            $this->warn("内存限制偏低: {$memoryLimit}，建议设置为128M或更高");
        }
        
        // 执行时间限制
        $maxExecutionTime = ini_get('max_execution_time');
        if ($maxExecutionTime == 0 || $maxExecutionTime >= 30) {
            $this->pass("执行时间限制: {$maxExecutionTime} ✓");
        } else {
            $this->warn("执行时间限制较短: {$maxExecutionTime}秒");
        }
    }
    
    /**
     * PHP配置检查
     */
    private function checkPHPConfiguration() {
        $this->section("PHP配置检查");
        
        // 错误报告设置
        $displayErrors = ini_get('display_errors');
        if ($displayErrors == '0' || strtolower($displayErrors) == 'off') {
            $this->pass("错误显示: 已关闭（生产环境推荐） ✓");
        } else {
            $this->fail("错误显示: 已开启（生产环境应关闭）");
        }
        
        // 日志设置
        $logErrors = ini_get('log_errors');
        if ($logErrors == '1' || strtolower($logErrors) == 'on') {
            $this->pass("错误日志: 已启用 ✓");
        } else {
            $this->fail("错误日志: 未启用");
        }
        
        // 文件上传设置
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $this->pass("文件上传限制: {$uploadMaxFilesize} ✓");
        $this->pass("POST数据限制: {$postMaxSize} ✓");
        
        // 时区设置
        $timezone = ini_get('date.timezone');
        if (!empty($timezone)) {
            $this->pass("时区设置: {$timezone} ✓");
        } else {
            $this->warn("时区未设置，建议在php.ini中设置date.timezone");
        }
        
        // OPcache检查
        if (extension_loaded('opcache')) {
            $opcacheEnabled = ini_get('opcache.enable');
            if ($opcacheEnabled) {
                $this->pass("OPcache: 已启用 ✓");
                
                $opcacheMemory = ini_get('opcache.memory_consumption');
                $this->pass("OPcache内存: {$opcacheMemory}MB ✓");
            } else {
                $this->warn("OPcache: 已安装但未启用");
            }
        }
    }
    
    /**
     * Web服务器配置检查
     */
    private function checkWebServerConfiguration() {
        $this->section("Web服务器配置检查");
        
        // 检查服务器软件
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        $this->pass("服务器软件: {$serverSoftware} ✓");
        
        // 检查.htaccess支持（Apache）
        if (stripos($serverSoftware, 'apache') !== false) {
            $htaccessFile = __DIR__ . '/../public/.htaccess';
            if (file_exists($htaccessFile)) {
                $this->pass(".htaccess文件: 存在 ✓");
            } else {
                $this->warn(".htaccess文件: 不存在，建议创建以增强安全性");
            }
        }
        
        // 检查HTTP头
        $headers = getallheaders();
        if (isset($headers['Host'])) {
            $this->pass("HTTP主机头: " . $headers['Host'] . " ✓");
        }
        
        // 检查HTTPS
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                   || $_SERVER['SERVER_PORT'] == 443;
        
        if ($isHttps) {
            $this->pass("HTTPS: 已启用 ✓");
        } else {
            $this->warn("HTTPS: 未启用（生产环境强烈建议启用SSL）");
        }
    }
    
    /**
     * 数据库连接检查
     */
    private function checkDatabaseConnection() {
        $this->section("数据库连接检查");
        
        try {
            $config = $this->loadEnvConfig();
            
            $host = $config['DB_HOST'] ?? 'localhost';
            $port = $config['DB_PORT'] ?? '3306';
            $database = $config['DB_DATABASE'] ?? 'alingai_pro';
            $username = $config['DB_USERNAME'] ?? 'root';
            $password = $config['DB_PASSWORD'] ?? '';
            $charset = $config['DB_CHARSET'] ?? 'utf8mb4';
            
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
            
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            
            $this->pass("数据库连接: 成功 ✓");
            
            // 检查MySQL版本
            $stmt = $pdo->query("SELECT VERSION() as version");
            $version = $stmt->fetch()['version'];
            $this->pass("MySQL版本: {$version} ✓");
            
            // 检查字符集
            $stmt = $pdo->query("SELECT @@character_set_database as charset");
            $dbCharset = $stmt->fetch()['charset'];
            $this->pass("数据库字符集: {$dbCharset} ✓");
            
            // 检查表数量
            $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = DATABASE()");
            $tableCount = $stmt->fetch()['table_count'];
            $this->pass("数据库表数量: {$tableCount} ✓");
            
            // 检查关键表
            $requiredTables = ['users', 'chat_sessions', 'chat_messages'];
            foreach ($requiredTables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
                if ($stmt->rowCount() > 0) {
                    $this->pass("关键表 {$table}: 存在 ✓");
                } else {
                    $this->warn("关键表 {$table}: 不存在");
                }
            }
            
        } catch (PDOException $e) {
            $this->fail("数据库连接失败: " . $e->getMessage());
        }
    }
    
    /**
     * 文件权限检查
     */
    private function checkFilePermissions() {
        $this->section("文件权限检查");
        
        $writableDirs = [
            'storage/logs',
            'storage/cache',
            'storage/uploads',
            'storage/sessions'
        ];
        
        foreach ($writableDirs as $dir) {
            $fullPath = __DIR__ . '/../' . $dir;
            if (is_dir($fullPath)) {
                if (is_writable($fullPath)) {
                    $this->pass("目录可写: {$dir} ✓");
                } else {
                    $this->fail("目录不可写: {$dir}");
                }
            } else {
                $this->warn("目录不存在: {$dir}");
            }
        }
        
        // 检查关键文件权限
        $criticalFiles = [
            '.env' => false,  // 不应该可写
            'config/app.php' => false,
            'composer.json' => false,
        ];
        
        foreach ($criticalFiles as $file => $shouldBeWritable) {
            $fullPath = __DIR__ . '/../' . $file;
            if (file_exists($fullPath)) {
                $isWritable = is_writable($fullPath);
                if ($shouldBeWritable === $isWritable) {
                    $permission = $shouldBeWritable ? '可写' : '只读';
                    $this->pass("文件权限 {$file}: {$permission} ✓");
                } else {
                    $expected = $shouldBeWritable ? '应该可写' : '应该只读';
                    $this->warn("文件权限 {$file}: {$expected}");
                }
            }
        }
    }
    
    /**
     * 安全配置检查
     */
    private function checkSecurityConfiguration() {
        $this->section("安全配置检查");
        
        // 检查.env文件安全性
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $this->pass(".env配置文件: 存在 ✓");
            
            // 检查是否在web根目录外
            $publicDir = realpath(__DIR__ . '/../public');
            $envDir = realpath(dirname($envFile));
            
            if (strpos($envFile, $publicDir) === false) {
                $this->pass(".env文件位置: 安全（不在public目录） ✓");
            } else {
                $this->fail(".env文件位置: 不安全（在public目录中）");
            }
        } else {
            $this->fail(".env配置文件: 不存在");
        }
        
        // 检查敏感目录保护
        $protectedDirs = ['vendor', 'src', 'config', 'storage'];
        foreach ($protectedDirs as $dir) {
            $dirPath = __DIR__ . '/../' . $dir;
            $publicDirPath = __DIR__ . '/../public/' . $dir;
            
            if (!is_dir($publicDirPath)) {
                $this->pass("目录保护 {$dir}: 不在public目录 ✓");
            } else {
                $this->fail("目录保护 {$dir}: 在public目录中（安全风险）");
            }
        }
        
        // 检查JWT密钥配置
        $config = $this->loadEnvConfig();
        $jwtSecret = $config['JWT_SECRET'] ?? '';
        
        if (!empty($jwtSecret) && $jwtSecret !== 'your_jwt_secret_key_here_change_in_production') {
            $this->pass("JWT密钥: 已配置 ✓");
        } else {
            $this->fail("JWT密钥: 未配置或使用默认值");
        }
        
        // 检查调试模式
        $appDebug = $config['APP_DEBUG'] ?? 'true';
        if (strtolower($appDebug) === 'false') {
            $this->pass("调试模式: 已关闭（生产环境推荐） ✓");
        } else {
            $this->warn("调试模式: 已开启（生产环境建议关闭）");
        }
    }
    
    /**
     * 性能设置检查
     */
    private function checkPerformanceSettings() {
        $this->section("性能设置检查");
        
        // 检查Composer优化
        $vendorDir = __DIR__ . '/../vendor';
        $autoloadFile = $vendorDir . '/composer/autoload_classmap.php';
        
        if (file_exists($autoloadFile)) {
            $classmap = include $autoloadFile;
            if (!empty($classmap)) {
                $this->pass("Composer类映射: 已优化 ✓");
            } else {
                $this->warn("Composer类映射: 未优化，运行 composer dump-autoload -o");
            }
        }
        
        // 检查缓存配置
        $config = $this->loadEnvConfig();
        
        // Redis检查
        $redisHost = $config['REDIS_HOST'] ?? '';
        if (!empty($redisHost)) {
            if (extension_loaded('redis')) {
                try {
                    $redis = new Redis();
                    $redis->connect($redisHost, $config['REDIS_PORT'] ?? 6379);
                    $this->pass("Redis缓存: 连接成功 ✓");
                    $redis->close();
                } catch (Exception $e) {
                    $this->warn("Redis缓存: 连接失败 - " . $e->getMessage());
                }
            } else {
                $this->warn("Redis缓存: PHP Redis扩展未安装");
            }
        } else {
            $this->warn("Redis缓存: 未配置");
        }
        
        // 检查日志配置
        $logChannel = $config['LOG_CHANNEL'] ?? '';
        if (!empty($logChannel)) {
            $this->pass("日志配置: {$logChannel} ✓");
        } else {
            $this->warn("日志配置: 未设置");
        }
    }
    
    /**
     * SSL配置检查
     */
    private function checkSSLConfiguration() {
        $this->section("SSL/TLS配置检查");
        
        // 检查OpenSSL扩展
        if (extension_loaded('openssl')) {
            $this->pass("OpenSSL扩展: 已安装 ✓");
            
            // 检查OpenSSL版本
            $opensslVersion = OPENSSL_VERSION_TEXT;
            $this->pass("OpenSSL版本: {$opensslVersion} ✓");
        } else {
            $this->fail("OpenSSL扩展: 未安装");
        }
        
        // 检查证书目录
        $certDirs = ['/etc/ssl/certs', '/etc/pki/tls/certs', 'C:\\OpenSSL\\certs'];
        foreach ($certDirs as $dir) {
            if (is_dir($dir)) {
                $this->pass("SSL证书目录: {$dir} 存在 ✓");
                break;
            }
        }
    }
    
    /**
     * 备份策略检查
     */
    private function checkBackupStrategy() {
        $this->section("备份策略检查");
        
        // 检查备份脚本
        $backupScript = __DIR__ . '/backup.php';
        if (file_exists($backupScript)) {
            $this->pass("备份脚本: 存在 ✓");
        } else {
            $this->warn("备份脚本: 不存在，建议创建自动备份脚本");
        }
        
        // 检查备份目录
        $backupDir = __DIR__ . '/../storage/backups';
        if (is_dir($backupDir)) {
            if (is_writable($backupDir)) {
                $this->pass("备份目录: 可写 ✓");
            } else {
                $this->warn("备份目录: 不可写");
            }
        } else {
            $this->warn("备份目录: 不存在");
        }
    }
    
    /**
     * 监控设置检查
     */
    private function checkMonitoringSetup() {
        $this->section("监控设置检查");
        
        // 检查错误日志
        $errorLogPath = ini_get('error_log');
        if (!empty($errorLogPath)) {
            $this->pass("错误日志路径: {$errorLogPath} ✓");
        } else {
            $this->warn("错误日志路径: 未设置");
        }
        
        // 检查应用日志目录
        $logDir = __DIR__ . '/../storage/logs';
        if (is_dir($logDir) && is_writable($logDir)) {
            $this->pass("应用日志目录: 可写 ✓");
        } else {
            $this->warn("应用日志目录: 不存在或不可写");
        }
        
        // 检查监控脚本
        $monitoringScript = __DIR__ . '/health-check.php';
        if (file_exists($monitoringScript)) {
            $this->pass("健康检查脚本: 存在 ✓");
        } else {
            $this->warn("健康检查脚本: 不存在，建议创建");
        }
    }
    
    /**
     * 生成最终报告
     */
    private function generateReport() {
        $endTime = microtime(true);
        $executionTime = round($endTime - $this->startTime, 2);
        
        $this->section("部署就绪报告");
        
        $totalChecks = count($this->results);
        $passedChecks = count(array_filter($this->results, function($result) {
            return $result['status'] === 'pass';
        }));
        $failedChecks = count($this->errors);
        $warningChecks = count($this->warnings);
        
        echo "执行时间: {$executionTime} 秒\n";
        echo "总检查项: {$totalChecks}\n";
        echo "通过: {$passedChecks}\n";
        echo "失败: {$failedChecks}\n";
        echo "警告: {$warningChecks}\n";
        
        $successRate = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100, 1) : 0;
        echo "成功率: {$successRate}%\n\n";
        
        if ($failedChecks === 0) {
            echo "🎉 系统已准备好部署到生产环境！\n";
        } else {
            echo "⚠️ 发现 {$failedChecks} 个严重问题需要解决：\n";
            foreach ($this->errors as $error) {
                echo "  ❌ {$error}\n";
            }
        }
        
        if ($warningChecks > 0) {
            echo "\n⚠️ 建议优化的项目：\n";
            foreach ($this->warnings as $warning) {
                echo "  ⚠️ {$warning}\n";
            }
        }
        
        // 生成详细报告文件
        $this->saveDetailedReport();
    }
    
    /**
     * 保存详细报告
     */
    private function saveDetailedReport() {
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'execution_time' => round(microtime(true) - $this->startTime, 2),
            'summary' => [
                'total_checks' => count($this->results),
                'passed' => count(array_filter($this->results, function($r) { return $r['status'] === 'pass'; })),
                'failed' => count($this->errors),
                'warnings' => count($this->warnings),
            ],
            'results' => $this->results,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
        
        $reportFile = __DIR__ . '/../storage/logs/production-readiness-' . time() . '.json';
        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "\n📊 详细报告已保存到: " . basename($reportFile) . "\n";
    }
    
    // 辅助方法
    private function loadEnvConfig() {
        $envFile = __DIR__ . '/../.env';
        $config = [];
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line[0] === '#') continue;
                
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $config[trim($key)] = trim($value);
                }
            }
        }
        
        return $config;
    }
    
    private function parseMemoryLimit($limit) {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit)-1]);
        $limit = (int) $limit;
        
        switch($last) {
            case 'g': $limit *= 1024;
            case 'm': $limit *= 1024;
            case 'k': $limit *= 1024;
        }
        
        return $limit;
    }
    
    private function printHeader() {
        echo "\n";
        echo "================================================================\n";
        echo "    AlingAi Pro 生产环境部署就绪检查 v1.0.0\n";
        echo "    \"三完编译\" 系统部署验证工具\n";
        echo "================================================================\n";
        echo "\n";
    }
    
    private function section($title) {
        echo "\n=== {$title} ===\n";
    }
    
    private function pass($message) {
        echo "✓ {$message}\n";
        $this->results[] = ['status' => 'pass', 'message' => $message];
    }
    
    private function fail($message) {
        echo "✗ {$message}\n";
        $this->results[] = ['status' => 'fail', 'message' => $message];
        $this->errors[] = $message;
    }
    
    private function warn($message) {
        echo "⚠ {$message}\n";
        $this->results[] = ['status' => 'warn', 'message' => $message];
        $this->warnings[] = $message;
    }
}

// 运行检查
if (php_sapi_name() === 'cli') {
    $checker = new ProductionReadinessChecker();
    $checker->run();
} else {
    echo "此脚本只能在命令行中运行。\n";
}
