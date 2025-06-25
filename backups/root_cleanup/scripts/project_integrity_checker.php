<?php
/**
 * AlingAi Pro 5.0 - 项目完整性检查器
 * 
 * 功能：
 * 1. 检查项目结构完整性
 * 2. 验证配置文件有效性
 * 3. 测试核心功能可用性
 * 4. 生成健康状况报告
 */

class ProjectIntegrityChecker {
    private $rootPath;
    private $checkResults = [];
    private $criticalIssues = [];
    private $warnings = [];
    
    public function __construct($rootPath = null) {
        $this->rootPath = $rootPath ?: dirname(__DIR__);
        $this->displayHeader();
    }
    
    /**
     * 显示检查器头部信息
     */
    private function displayHeader() {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                🔍 AlingAi Pro 5.0 完整性检查器              ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  检查时间: " . date('Y-m-d H:i:s') . "                            ║\n";
        echo "║  项目路径: " . substr($this->rootPath, -40) . "           ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
    
    /**
     * 运行完整性检查
     */
    public function runCheck() {
        echo "🔍 开始项目完整性检查...\n\n";
        
        $this->checkProjectStructure();
        $this->checkConfigurationFiles();
        $this->checkDependencies();
        $this->checkSecuritySettings();
        $this->checkPerformanceSettings();
        $this->checkDatabaseConnectivity();
        $this->checkFilePermissions();
        $this->generateHealthReport();
        
        $this->displaySummary();
        
        return [
            'overall_health' => $this->calculateOverallHealth(),
            'critical_issues' => $this->criticalIssues,
            'warnings' => $this->warnings,
            'check_results' => $this->checkResults
        ];
    }
    
    /**
     * 检查项目结构
     */
    private function checkProjectStructure() {
        echo "📁 检查项目结构...\n";
        
        $requiredDirectories = [
            'config',
            'public',
            'src',
            'scripts',
            'vendor',
            'storage',
            'database'
        ];
        
        $requiredFiles = [
            'composer.json',
            'README.md',
            'public/index.html',
            'public/admin/login.php',
            'public/admin/tools_manager.php'
        ];
        
        $structureCheck = [
            'directories' => [],
            'files' => [],
            'missing_directories' => [],
            'missing_files' => []
        ];
        
        // 检查目录
        foreach ($requiredDirectories as $dir) {
            $fullPath = $this->rootPath . '/' . $dir;
            if (is_dir($fullPath)) {
                $structureCheck['directories'][] = $dir;
                echo "   ✅ 目录存在: {$dir}\n";
            } else {
                $structureCheck['missing_directories'][] = $dir;
                $this->criticalIssues[] = "缺失关键目录: {$dir}";
                echo "   ❌ 目录缺失: {$dir}\n";
            }
        }
        
        // 检查文件
        foreach ($requiredFiles as $file) {
            $fullPath = $this->rootPath . '/' . $file;
            if (file_exists($fullPath)) {
                $structureCheck['files'][] = $file;
                echo "   ✅ 文件存在: {$file}\n";
            } else {
                $structureCheck['missing_files'][] = $file;
                $this->criticalIssues[] = "缺失关键文件: {$file}";
                echo "   ❌ 文件缺失: {$file}\n";
            }
        }
        
        $this->checkResults['project_structure'] = $structureCheck;
    }
    
    /**
     * 检查配置文件
     */
    private function checkConfigurationFiles() {
        echo "\n⚙️ 检查配置文件...\n";
        
        $configFiles = [
            'app.php',
            'database.php',
            'cache.php',
            'security.php',
            'performance.php',
            'logging.php'
        ];
        
        $configCheck = [
            'valid_configs' => [],
            'invalid_configs' => [],
            'missing_configs' => []
        ];
        
        foreach ($configFiles as $configFile) {
            $configPath = $this->rootPath . '/config/' . $configFile;
            
            if (file_exists($configPath)) {
                try {
                    $config = include $configPath;
                    if (is_array($config)) {
                        $configCheck['valid_configs'][] = $configFile;
                        echo "   ✅ 配置有效: {$configFile}\n";
                    } else {
                        $configCheck['invalid_configs'][] = $configFile;
                        $this->warnings[] = "配置文件格式错误: {$configFile}";
                        echo "   ⚠️ 配置无效: {$configFile}\n";
                    }
                } catch (Exception $e) {
                    $configCheck['invalid_configs'][] = $configFile;
                    $this->criticalIssues[] = "配置文件加载失败: {$configFile} - " . $e->getMessage();
                    echo "   ❌ 加载失败: {$configFile}\n";
                }
            } else {
                $configCheck['missing_configs'][] = $configFile;
                $this->warnings[] = "配置文件缺失: {$configFile}";
                echo "   ⚠️ 配置缺失: {$configFile}\n";
            }
        }
        
        $this->checkResults['configuration_files'] = $configCheck;
    }
    
    /**
     * 检查依赖关系
     */
    private function checkDependencies() {
        echo "\n📦 检查依赖关系...\n";
        
        $dependencyCheck = [
            'composer_installed' => false,
            'vendor_directory' => false,
            'autoloader' => false,
            'required_extensions' => [],
            'missing_extensions' => []
        ];
        
        // 检查Composer
        if (file_exists($this->rootPath . '/vendor/autoload.php')) {
            $dependencyCheck['composer_installed'] = true;
            $dependencyCheck['vendor_directory'] = true;
            $dependencyCheck['autoloader'] = true;
            echo "   ✅ Composer依赖已安装\n";
        } else {
            $this->criticalIssues[] = "Composer依赖未安装";
            echo "   ❌ Composer依赖未安装\n";
        }
        
        // 检查PHP扩展
        $requiredExtensions = [
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'curl',
            'gd',
            'zip'
        ];
        
        foreach ($requiredExtensions as $extension) {
            if (extension_loaded($extension)) {
                $dependencyCheck['required_extensions'][] = $extension;
                echo "   ✅ 扩展可用: {$extension}\n";
            } else {
                $dependencyCheck['missing_extensions'][] = $extension;
                $this->warnings[] = "PHP扩展缺失: {$extension}";
                echo "   ⚠️ 扩展缺失: {$extension}\n";
            }
        }
        
        $this->checkResults['dependencies'] = $dependencyCheck;
    }
    
    /**
     * 检查安全设置
     */
    private function checkSecuritySettings() {
        echo "\n🛡️ 检查安全设置...\n";
        
        $securityCheck = [
            'https_enabled' => false,
            'secure_headers' => false,
            'session_security' => false,
            'file_permissions' => false,
            'debug_mode' => false
        ];
        
        // 检查HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $securityCheck['https_enabled'] = true;
            echo "   ✅ HTTPS已启用\n";
        } else {
            $this->warnings[] = "建议启用HTTPS";
            echo "   ⚠️ HTTPS未启用\n";
        }
        
        // 检查调试模式
        if (ini_get('display_errors') == '0') {
            echo "   ✅ 调试模式已关闭\n";
        } else {
            $securityCheck['debug_mode'] = true;
            $this->warnings[] = "生产环境应关闭调试模式";
            echo "   ⚠️ 调试模式开启\n";
        }
        
        // 检查敏感文件权限
        $sensitiveFiles = [
            '.env',
            'config/database.php',
            'config/security.php'
        ];
        
        $permissionIssues = 0;
        foreach ($sensitiveFiles as $file) {
            $filePath = $this->rootPath . '/' . $file;
            if (file_exists($filePath)) {
                $perms = fileperms($filePath);
                if (($perms & 0x0044) === 0) { // 检查是否对其他用户可读
                    echo "   ✅ 文件权限安全: {$file}\n";
                } else {
                    $permissionIssues++;
                    $this->warnings[] = "文件权限过宽: {$file}";
                    echo "   ⚠️ 权限过宽: {$file}\n";
                }
            }
        }
        
        $securityCheck['file_permissions'] = $permissionIssues === 0;
        $this->checkResults['security_settings'] = $securityCheck;
    }
    
    /**
     * 检查性能设置
     */
    private function checkPerformanceSettings() {
        echo "\n⚡ 检查性能设置...\n";
        
        $performanceCheck = [
            'opcache_enabled' => extension_loaded('opcache') && ini_get('opcache.enable'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'redis_available' => extension_loaded('redis'),
            'gzip_enabled' => false
        ];
        
        // 检查OPcache
        if ($performanceCheck['opcache_enabled']) {
            echo "   ✅ OPcache已启用\n";
        } else {
            $this->warnings[] = "建议启用OPcache提升性能";
            echo "   ⚠️ OPcache未启用\n";
        }
        
        // 检查内存限制
        $memoryLimit = ini_get('memory_limit');
        $memoryInBytes = $this->convertToBytes($memoryLimit);
        if ($memoryInBytes >= 256 * 1024 * 1024) { // 256MB
            echo "   ✅ 内存限制充足: {$memoryLimit}\n";
        } else {
            $this->warnings[] = "建议增加内存限制到256MB以上";
            echo "   ⚠️ 内存限制较低: {$memoryLimit}\n";
        }
        
        // 检查Redis
        if ($performanceCheck['redis_available']) {
            echo "   ✅ Redis扩展可用\n";
        } else {
            $this->warnings[] = "建议安装Redis扩展用于缓存";
            echo "   ⚠️ Redis扩展不可用\n";
        }
        
        $this->checkResults['performance_settings'] = $performanceCheck;
    }
      /**
     * 检查数据库连接
     */
    private function checkDatabaseConnectivity() {
        echo "\n🗃️ 检查数据库连接...\n";
        
        $databaseCheck = [
            'config_available' => false,
            'connection_successful' => false,
            'tables_exist' => false,
            'permissions_valid' => false
        ];
        
        // 检查数据库配置
        $dbConfigPath = $this->rootPath . '/config/database.php';
        if (file_exists($dbConfigPath)) {
            $databaseCheck['config_available'] = true;
            echo "   ✅ 数据库配置文件存在\n";
            
            try {
                $dbConfig = include $dbConfigPath;
                
                // 优先检查文件数据库
                if (isset($dbConfig['connections']['file'])) {
                    $fileDbPath = $this->rootPath . '/database/filedb';
                    
                    if (is_dir($fileDbPath)) {
                        $databaseCheck['connection_successful'] = true;
                        echo "   ✅ 文件数据库目录存在\n";
                        
                        // 检查数据表文件
                        $tableFiles = ['users.json', 'sessions.json', 'ai_conversations.json', 'system_logs.json'];
                        $existingTables = 0;
                        
                        foreach ($tableFiles as $tableFile) {
                            $tablePath = $fileDbPath . '/' . $tableFile;
                            if (file_exists($tablePath)) {
                                $existingTables++;
                            }
                        }
                        
                        if ($existingTables > 0) {
                            $databaseCheck['tables_exist'] = true;
                            echo "   ✅ 数据库表文件存在 ({$existingTables}/" . count($tableFiles) . " 个表)\n";
                        } else {
                            $this->warnings[] = "文件数据库表不存在，可能需要初始化";
                            echo "   ⚠️ 数据库表文件为空\n";
                        }
                        
                        // 检查文件权限
                        if (is_writable($fileDbPath)) {
                            $databaseCheck['permissions_valid'] = true;
                            echo "   ✅ 数据库目录可写\n";
                        } else {
                            $this->warnings[] = "文件数据库目录不可写";
                            echo "   ⚠️ 数据库目录不可写\n";
                        }
                        
                    } else {
                        $this->warnings[] = "文件数据库目录不存在，建议运行 init_file_database.php";
                        echo "   ⚠️ 文件数据库目录不存在\n";
                    }
                }
                // 检查MySQL数据库（作为备用）
                elseif (isset($dbConfig['connections']['mysql'])) {
                    $config = $dbConfig['connections']['mysql'];
                    
                    // 尝试连接数据库
                    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
                    $pdo = new PDO($dsn, $config['username'], $config['password'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 5
                    ]);
                    
                    $databaseCheck['connection_successful'] = true;
                    echo "   ✅ MySQL数据库连接成功\n";
                    
                    // 检查表是否存在
                    $stmt = $pdo->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($tables) > 0) {
                        $databaseCheck['tables_exist'] = true;
                        echo "   ✅ 数据库表存在 (" . count($tables) . " 个表)\n";
                    } else {
                        $this->warnings[] = "数据库中没有表，可能需要运行迁移";
                        echo "   ⚠️ 数据库为空\n";
                    }
                } else {
                    $this->criticalIssues[] = "数据库配置格式错误";
                    echo "   ❌ 数据库配置格式错误\n";
                }
                
            } catch (PDOException $e) {
                $this->warnings[] = "MySQL数据库连接失败，使用文件数据库: " . $e->getMessage();
                echo "   ⚠️ MySQL数据库连接失败，检查文件数据库\n";
                
                // 检查文件数据库作为备用
                $fileDbPath = $this->rootPath . '/database/filedb';
                if (is_dir($fileDbPath)) {
                    $databaseCheck['connection_successful'] = true;
                    echo "   ✅ 文件数据库可用\n";
                }
                
            } catch (Exception $e) {
                $this->warnings[] = "数据库检查失败: " . $e->getMessage();
                echo "   ⚠️ 数据库检查失败\n";
            }
        } else {
            $this->criticalIssues[] = "数据库配置文件不存在";
            echo "   ❌ 数据库配置文件不存在\n";
        }
        
        $this->checkResults['database_connectivity'] = $databaseCheck;
    }
    
    /**
     * 检查文件权限
     */
    private function checkFilePermissions() {
        echo "\n📝 检查文件权限...\n";
        
        $permissionCheck = [
            'writable_directories' => [],
            'non_writable_directories' => [],
            'executable_files' => [],
            'non_executable_files' => []
        ];
        
        // 需要写权限的目录
        $writableDirectories = [
            'storage',
            'storage/logs',
            'storage/cache',
            'public/uploads',
            'cache'
        ];
        
        foreach ($writableDirectories as $dir) {
            $dirPath = $this->rootPath . '/' . $dir;
            if (is_dir($dirPath) && is_writable($dirPath)) {
                $permissionCheck['writable_directories'][] = $dir;
                echo "   ✅ 目录可写: {$dir}\n";
            } else {
                $permissionCheck['non_writable_directories'][] = $dir;
                $this->criticalIssues[] = "目录不可写: {$dir}";
                echo "   ❌ 目录不可写: {$dir}\n";
            }
        }
        
        // 需要执行权限的文件
        $executableFiles = [
            'scripts/unified_optimizer.php',
            'run_optimization.bat'
        ];
        
        foreach ($executableFiles as $file) {
            $filePath = $this->rootPath . '/' . $file;
            if (file_exists($filePath) && is_readable($filePath)) {
                $permissionCheck['executable_files'][] = $file;
                echo "   ✅ 文件可执行: {$file}\n";
            } else {
                $permissionCheck['non_executable_files'][] = $file;
                $this->warnings[] = "文件不可执行: {$file}";
                echo "   ⚠️ 文件不可执行: {$file}\n";
            }
        }
        
        $this->checkResults['file_permissions'] = $permissionCheck;
    }
    
    /**
     * 生成健康报告
     */
    private function generateHealthReport() {
        echo "\n📊 生成健康报告...\n";
        
        $overallHealth = $this->calculateOverallHealth();
        
        $healthReport = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_health_score' => $overallHealth,
            'health_status' => $this->getHealthStatus($overallHealth),
            'critical_issues_count' => count($this->criticalIssues),
            'warnings_count' => count($this->warnings),
            'critical_issues' => $this->criticalIssues,
            'warnings' => $this->warnings,
            'detailed_results' => $this->checkResults,
            'recommendations' => $this->generateRecommendations(),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'os' => php_uname(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ]
        ];
        
        // 保存健康报告
        $reportPath = $this->rootPath . '/PROJECT_HEALTH_REPORT_' . date('Y_m_d_H_i_s') . '.json';
        file_put_contents($reportPath, json_encode($healthReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "   📋 健康报告已保存到: " . basename($reportPath) . "\n";
        
        return $healthReport;
    }
    
    /**
     * 计算整体健康分数
     */
    private function calculateOverallHealth() {
        $totalChecks = 0;
        $passedChecks = 0;
        
        foreach ($this->checkResults as $category => $results) {
            if (is_array($results)) {
                foreach ($results as $key => $value) {
                    $totalChecks++;
                    if (is_bool($value) && $value) {
                        $passedChecks++;
                    } elseif (is_array($value) && !empty($value) && !in_array($key, ['missing_directories', 'missing_files', 'invalid_configs', 'missing_configs', 'missing_extensions', 'non_writable_directories', 'non_executable_files'])) {
                        $passedChecks++;
                    }
                }
            }
        }
        
        return $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100, 1) : 0;
    }
    
    /**
     * 获取健康状态描述
     */
    private function getHealthStatus($score) {
        if ($score >= 90) {
            return '优秀';
        } elseif ($score >= 80) {
            return '良好';
        } elseif ($score >= 70) {
            return '一般';
        } elseif ($score >= 60) {
            return '需要改进';
        } else {
            return '需要紧急修复';
        }
    }
    
    /**
     * 生成建议
     */
    private function generateRecommendations() {
        $recommendations = [];
        
        if (!empty($this->criticalIssues)) {
            $recommendations['critical'] = [
                '立即修复所有关键问题',
                '检查项目结构完整性',
                '确保数据库连接正常',
                '修复文件权限问题'
            ];
        }
        
        if (!empty($this->warnings)) {
            $recommendations['improvements'] = [
                '启用HTTPS加强安全性',
                '配置OPcache提升性能',
                '安装Redis用于缓存',
                '优化文件权限设置'
            ];
        }
        
        $recommendations['general'] = [
            '定期运行完整性检查',
            '保持依赖库更新',
            '监控系统性能指标',
            '实施自动化测试'
        ];
        
        return $recommendations;
    }
    
    /**
     * 显示检查摘要
     */
    private function displaySummary() {
        $overallHealth = $this->calculateOverallHealth();
        $healthStatus = $this->getHealthStatus($overallHealth);
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    📊 完整性检查摘要                        ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  整体健康分数: {$overallHealth}%  状态: {$healthStatus}" . str_repeat(" ", 28 - mb_strlen($healthStatus)) . "║\n";
        echo "║  关键问题: " . count($this->criticalIssues) . " 个" . str_repeat(" ", 47 - strlen(count($this->criticalIssues))) . "║\n";
        echo "║  警告信息: " . count($this->warnings) . " 个" . str_repeat(" ", 47 - strlen(count($this->warnings))) . "║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        
        if (!empty($this->criticalIssues)) {
            echo "\n🚨 关键问题:\n";
            foreach ($this->criticalIssues as $issue) {
                echo "   ❌ {$issue}\n";
            }
        }
        
        if (!empty($this->warnings)) {
            echo "\n⚠️ 警告信息:\n";
            foreach ($this->warnings as $warning) {
                echo "   ⚠️ {$warning}\n";
            }
        }
        
        if (empty($this->criticalIssues) && empty($this->warnings)) {
            echo "\n🎉 恭喜！项目通过了所有完整性检查\n";
        }
        
        echo "\n";
    }
    
    /**
     * 转换内存单位到字节
     */
    private function convertToBytes($value) {
        $unit = strtolower(substr($value, -1));
        $number = (int) $value;
        
        switch ($unit) {
            case 'g':
                return $number * 1024 * 1024 * 1024;
            case 'm':
                return $number * 1024 * 1024;
            case 'k':
                return $number * 1024;
            default:
                return $number;
        }
    }
}

// 执行完整性检查
if (php_sapi_name() === 'cli') {
    $checker = new ProjectIntegrityChecker();
    $results = $checker->runCheck();
    
    if (!empty($results['critical_issues'])) {
        exit(1);
    }
    exit(0);
} else {
    echo "此脚本需要在命令行环境中运行\n";
}
?>
