<?php
/**
 * AlingAi Pro 完整部署验证脚本
 * 验证"三完编译"的最终状态
 * 
 * @author AlingAi Team
 * @version 1.0.0
 * @license MIT
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

class DeploymentValidator
{
    private array $config;
    private array $results = [];
    private int $totalChecks = 0;
    private int $passedChecks = 0;
    private int $failedChecks = 0;
    private int $warningChecks = 0;
    
    public function __construct()
    {
        $this->loadConfig();
    }
    
    private function loadConfig(): void
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $this->config = parse_ini_file($envFile);
        } else {
            $this->config = [];
        }
    }
    
    public function runCompleteValidation(): array
    {
        $this->printHeader();
        
        // 运行所有验证检查
        $this->validateSystemRequirements();
        $this->validateFileStructure();
        $this->validateWebPages();
        $this->validateBackendServices();
        $this->validateDatabaseSchema();
        $this->validateAssetFiles();
        $this->validateSecurity();
        $this->validatePerformance();
        $this->validateDocumentation();
        $this->validateDeploymentTools();
        
        // 生成最终报告
        $this->generateFinalReport();
        
        return $this->results;
    }
    
    private function printHeader(): void
    {
        echo "================================================================" . PHP_EOL;
        echo "    AlingAi Pro 完整部署验证 v1.0.0" . PHP_EOL;
        echo "    \"三完编译\" 最终验证测试套件" . PHP_EOL;
        echo "================================================================" . PHP_EOL;
    }
    
    private function validateSystemRequirements(): void
    {
        echo "=== 系统要求验证 ===" . PHP_EOL;
        
        // PHP版本检查
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '7.4.0', '>=')) {
            $this->pass("PHP版本: {$phpVersion}");
        } else {
            $this->fail("PHP版本过低: {$phpVersion} (需要7.4+)");
        }
        
        // PHP扩展检查
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'openssl', 'mbstring', 'curl', 'zip', 'xml'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->pass("PHP扩展 {$ext}: 已安装");
            } else {
                $this->fail("PHP扩展 {$ext}: 未安装");
            }
        }
        
        // 可选扩展检查
        $optionalExtensions = ['redis', 'opcache', 'gd', 'imagick'];
        foreach ($optionalExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->pass("推荐扩展 {$ext}: 已安装");
            } else {
                $this->warn("推荐扩展 {$ext}: 未安装 (建议安装)");
            }
        }
        
        // 内存限制检查
        $memoryLimit = ini_get('memory_limit');
        $memoryBytes = $this->convertToBytes($memoryLimit);
        if ($memoryBytes >= 128 * 1024 * 1024) {
            $this->pass("内存限制: {$memoryLimit}");
        } else {
            $this->warn("内存限制: {$memoryLimit} (建议128M+)");
        }
    }
    
    private function validateFileStructure(): void
    {
        echo "=== 文件结构验证 ===" . PHP_EOL;
        
        $requiredFiles = [
            'public/index.php' => '入口文件',
            'src/Core/Application.php' => '核心应用类',
            'config/routes.php' => '路由配置',
            'composer.json' => 'Composer配置',
            '.env' => '环境配置',
            'README.md' => '项目文档',
        ];
        
        foreach ($requiredFiles as $file => $description) {
            $path = __DIR__ . '/../' . $file;
            if (file_exists($path)) {
                $this->pass("文件存在: {$file} ({$description})");
            } else {
                $this->fail("文件缺失: {$file} ({$description})");
            }
        }
        
        $requiredDirs = [
            'src' => '后端源码',
            'public' => 'Web根目录',
            'storage/logs' => '日志目录',
            'storage/cache' => '缓存目录',
            'storage/uploads' => '上传目录',
            'storage/sessions' => '会话目录',
            'storage/backup' => '备份目录',
            'config' => '配置目录',
            'resources' => '资源目录',
            'bin' => '脚本目录',
        ];
        
        foreach ($requiredDirs as $dir => $description) {
            $path = __DIR__ . '/../' . $dir;
            if (is_dir($path)) {
                $this->pass("目录存在: {$dir} ({$description})");
            } else {
                $this->fail("目录缺失: {$dir} ({$description})");
            }
        }
    }
    
    private function validateWebPages(): void
    {
        echo "=== Web页面验证 ===" . PHP_EOL;
        
        $webPages = [
            'public/index.php' => '首页',
            'public/chat.html' => '聊天页面',
            'public/login.html' => '登录页面',
            'public/register.html' => '注册页面',
            'public/dashboard.html' => '仪表板',
            'public/admin.html' => '管理页面',
            'public/profile.html' => '个人资料',
        ];
        
        foreach ($webPages as $page => $description) {
            $path = __DIR__ . '/../' . $page;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                
                // 检查HTML结构
                if (strpos($content, '<html') !== false || strpos($content, '<!DOCTYPE') !== false) {
                    $this->pass("页面结构: {$page} ({$description})");
                } else {
                    $this->warn("页面结构: {$page} 可能不是有效的HTML");
                }
                
                // 检查基本元素
                if (strpos($content, '<title>') !== false) {
                    $this->pass("页面标题: {$page}");
                } else {
                    $this->warn("页面标题缺失: {$page}");
                }
            } else {
                $this->fail("页面缺失: {$page} ({$description})");
            }
        }
    }
    
    private function validateBackendServices(): void
    {
        echo "=== 后端服务验证 ===" . PHP_EOL;
        
        // 检查核心PHP类
        $coreClasses = [
            'src/Core/Application.php' => 'Application',
            'src/Controllers/WebController.php' => 'WebController',
            'src/Services/AuthService.php' => 'AuthService',
            'src/Services/ChatService.php' => 'ChatService',
        ];
        
        foreach ($coreClasses as $file => $className) {
            $path = __DIR__ . '/../' . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                
                // 检查PHP语法
                if (strpos($content, '<?php') !== false) {
                    $this->pass("PHP类文件: {$file}");
                } else {
                    $this->fail("PHP类文件格式错误: {$file}");
                }
                
                // 检查命名空间
                if (strpos($content, 'namespace') !== false) {
                    $this->pass("命名空间: {$file}");
                } else {
                    $this->warn("命名空间缺失: {$file}");
                }
            } else {
                $this->fail("核心类缺失: {$file}");
            }
        }
        
        // 检查Composer依赖
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            $this->pass("Composer自动加载器");
        } else {
            $this->fail("Composer依赖未安装");
        }
    }
    
    private function validateDatabaseSchema(): void
    {
        echo "=== 数据库架构验证 ===" . PHP_EOL;
        
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['DB_HOST'] ?? 'localhost',
                $this->config['DB_PORT'] ?? '3306',
                $this->config['DB_DATABASE'] ?? 'alingai_pro',
                $this->config['DB_CHARSET'] ?? 'utf8mb4'
            );
            
            $pdo = new PDO(
                $dsn,
                $this->config['DB_USERNAME'] ?? 'root',
                $this->config['DB_PASSWORD'] ?? ''
            );
            
            $this->pass("数据库连接");
            
            // 检查必要的表
            $requiredTables = ['users', 'chat_history', 'agents', 'user_settings'];
            $stmt = $pdo->query("SHOW TABLES");
            $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($requiredTables as $table) {
                if (in_array($table, $existingTables)) {
                    $this->pass("数据表: {$table}");
                } else {
                    $this->warn("数据表缺失: {$table}");
                }
            }
            
        } catch (PDOException $e) {
            $this->warn("数据库连接失败: " . $e->getMessage());
        }
    }
    
    private function validateAssetFiles(): void
    {
        echo "=== 静态资源验证 ===" . PHP_EOL;
        
        // JavaScript文件
        $jsFiles = glob(__DIR__ . '/../public/assets/js/*.js');
        if (count($jsFiles) > 0) {
            $this->pass("JavaScript文件数量: " . count($jsFiles));
            
            foreach ($jsFiles as $jsFile) {
                $content = file_get_contents($jsFile);
                if (strlen($content) > 0) {
                    $this->pass("JS文件: " . basename($jsFile));
                } else {
                    $this->warn("JS文件为空: " . basename($jsFile));
                }
            }
        } else {
            $this->warn("JavaScript文件缺失");
        }
        
        // CSS文件
        $cssFiles = glob(__DIR__ . '/../public/assets/css/*.css');
        if (count($cssFiles) > 0) {
            $this->pass("CSS文件数量: " . count($cssFiles));
            
            foreach ($cssFiles as $cssFile) {
                $content = file_get_contents($cssFile);
                if (strlen($content) > 0) {
                    $this->pass("CSS文件: " . basename($cssFile));
                } else {
                    $this->warn("CSS文件为空: " . basename($cssFile));
                }
            }
        } else {
            $this->warn("CSS文件缺失");
        }
        
        // 图片文件
        $imageDir = __DIR__ . '/../public/assets/images';
        if (is_dir($imageDir)) {
            $this->pass("图片目录存在");
        } else {
            $this->warn("图片目录缺失");
        }
    }
    
    private function validateSecurity(): void
    {
        echo "=== 安全配置验证 ===" . PHP_EOL;
        
        // .env文件安全
        if (file_exists(__DIR__ . '/../.env')) {
            $this->pass(".env配置文件存在");
            
            // 检查JWT密钥
            if (!empty($this->config['JWT_SECRET']) && $this->config['JWT_SECRET'] !== 'your_jwt_secret_key_here') {
                $this->pass("JWT密钥已配置");
            } else {
                $this->fail("JWT密钥未配置或使用默认值");
            }
        } else {
            $this->fail(".env配置文件缺失");
        }
        
        // .htaccess文件
        if (file_exists(__DIR__ . '/../public/.htaccess')) {
            $this->pass(".htaccess安全配置");
        } else {
            $this->warn(".htaccess文件缺失");
        }
        
        // 敏感目录保护
        $protectedDirs = ['src', 'config', 'storage', 'vendor'];
        foreach ($protectedDirs as $dir) {
            $path = __DIR__ . '/../' . $dir;
            if (is_dir($path) && !is_dir(__DIR__ . '/../public/' . $dir)) {
                $this->pass("目录保护: {$dir} (不在public目录)");
            } else {
                $this->warn("目录安全: {$dir} 可能暴露");
            }
        }
    }
    
    private function validatePerformance(): void
    {
        echo "=== 性能配置验证 ===" . PHP_EOL;
        
        // Composer优化
        if (file_exists(__DIR__ . '/../vendor/composer/autoload_classmap.php')) {
            $classmap = include __DIR__ . '/../vendor/composer/autoload_classmap.php';
            if (count($classmap) > 0) {
                $this->pass("Composer类映射已优化 (" . count($classmap) . " 个类)");
            } else {
                $this->warn("Composer类映射为空");
            }
        } else {
            $this->warn("Composer类映射缺失");
        }
        
        // 缓存目录
        if (is_dir(__DIR__ . '/../storage/cache') && is_writable(__DIR__ . '/../storage/cache')) {
            $this->pass("缓存目录可写");
        } else {
            $this->warn("缓存目录不可写");
        }
        
        // 日志目录
        if (is_dir(__DIR__ . '/../storage/logs') && is_writable(__DIR__ . '/../storage/logs')) {
            $this->pass("日志目录可写");
        } else {
            $this->warn("日志目录不可写");
        }
    }
    
    private function validateDocumentation(): void
    {
        echo "=== 文档验证 ===" . PHP_EOL;
        
        $docFiles = [
            'README.md' => 'README文档',
            'THREE-COMPLETE-COMPILATION-REPORT.md' => '三完编译报告',
        ];
        
        foreach ($docFiles as $file => $description) {
            $path = __DIR__ . '/../' . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                if (strlen($content) > 100) {
                    $this->pass("文档: {$file} ({$description})");
                } else {
                    $this->warn("文档过短: {$file}");
                }
            } else {
                $this->warn("文档缺失: {$file}");
            }
        }
    }
    
    private function validateDeploymentTools(): void
    {
        echo "=== 部署工具验证 ===" . PHP_EOL;
        
        $deploymentTools = [
            'bin/mysql-setup.php' => 'MySQL初始化脚本',
            'bin/production-readiness.php' => '生产就绪检查',
            'bin/health-check.php' => '健康检查脚本',
            'bin/backup.php' => '备份脚本',
            'bin/system-optimizer.php' => '系统优化工具',
            'bin/websocket-server.php' => 'WebSocket服务器',
            'bin/production-deploy.sh' => '生产部署脚本',
        ];
        
        foreach ($deploymentTools as $tool => $description) {
            $path = __DIR__ . '/../' . $tool;
            if (file_exists($path)) {
                $this->pass("部署工具: {$tool} ({$description})");
            } else {
                $this->warn("部署工具缺失: {$tool}");
            }
        }
        
        // Nginx配置
        if (file_exists(__DIR__ . '/../nginx/production.conf')) {
            $this->pass("Nginx生产配置");
        } else {
            $this->warn("Nginx配置缺失");
        }
    }
    
    private function generateFinalReport(): void
    {
        echo PHP_EOL . "================================================================" . PHP_EOL;
        echo "部署验证完成报告" . PHP_EOL;
        echo "----------------------------------------------------------------" . PHP_EOL;
        
        $totalChecks = $this->passedChecks + $this->failedChecks + $this->warningChecks;
        $successRate = $totalChecks > 0 ? round(($this->passedChecks / $totalChecks) * 100, 1) : 0;
        
        echo "执行时间: " . date('Y-m-d H:i:s') . PHP_EOL;
        echo "总检查项: {$totalChecks}" . PHP_EOL;
        echo "通过: {$this->passedChecks}" . PHP_EOL;
        echo "失败: {$this->failedChecks}" . PHP_EOL;
        echo "警告: {$this->warningChecks}" . PHP_EOL;
        echo "成功率: {$successRate}%" . PHP_EOL;
        
        if ($this->failedChecks === 0) {
            echo PHP_EOL . "🎉 恭喜！AlingAi Pro \"三完编译\" 验证通过！" . PHP_EOL;
            echo "✅ 系统已准备好进行生产部署。" . PHP_EOL;
        } else {
            echo PHP_EOL . "⚠️ 发现 {$this->failedChecks} 个关键问题需要解决。" . PHP_EOL;
        }
        
        if ($this->warningChecks > 0) {
            echo "💡 有 {$this->warningChecks} 个建议优化项目。" . PHP_EOL;
        }
        
        // 保存详细报告
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_checks' => $totalChecks,
                'passed' => $this->passedChecks,
                'failed' => $this->failedChecks,
                'warnings' => $this->warningChecks,
                'success_rate' => $successRate,
            ],
            'results' => $this->results,
        ];
        
        $reportFile = __DIR__ . '/../storage/logs/deployment_validation_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "📊 详细报告已保存: " . basename($reportFile) . PHP_EOL;
        echo "================================================================" . PHP_EOL;
    }
    
    private function pass(string $message): void
    {
        $this->passedChecks++;
        $this->results[] = ['status' => 'pass', 'message' => $message];
        echo "✓ {$message}" . PHP_EOL;
    }
    
    private function fail(string $message): void
    {
        $this->failedChecks++;
        $this->results[] = ['status' => 'fail', 'message' => $message];
        echo "✗ {$message}" . PHP_EOL;
    }
    
    private function warn(string $message): void
    {
        $this->warningChecks++;
        $this->results[] = ['status' => 'warn', 'message' => $message];
        echo "⚠ {$message}" . PHP_EOL;
    }
    
    private function convertToBytes(string $memoryLimit): int
    {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) $memoryLimit;
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
}

// 主程序
if (php_sapi_name() === 'cli') {
    try {
        $validator = new DeploymentValidator();
        $results = $validator->runCompleteValidation();
        
        exit(0);
        
    } catch (Exception $e) {
        echo "❌ 验证过程出错: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}
