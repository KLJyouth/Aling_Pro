<?php

/**
 * 🚀 AlingAi Pro 5.0 部署就绪检查工�?
 * 全面检查系统是否准备好部署到生产环�?
 * 
 * @version 1.0
 * @author AlingAi Team
 * @created 2025-06-11
 */

class DeploymentReadinessChecker {
    private $basePath;
    private $checks = [];
    private $criticalIssues = [];
    private $warnings = [];
    private $recommendations = [];
    
    public function __construct($basePath = null) {
        $this->basePath = $basePath ?: dirname(__DIR__];
        $this->initializeReport(];
    }
    
    private function initializeReport() {
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "�?             🚀 部署就绪检查工�?                           ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "�? 检查时�? " . date('Y-m-d H:i:s') . str_repeat(' ', 25) . "║\n";
        echo "�? 项目路径: " . substr($this->basePath, 0, 40) . str_repeat(' ', 15) . "║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    }
    
    public function runDeploymentChecks() {
        echo "🔍 开始部署就绪检�?..\n\n";
        
        $this->checkProjectStructure(];
        $this->checkDependencies(];
        $this->checkConfigurations(];
        $this->checkSecurity(];
        $this->checkPerformance(];
        $this->checkDocumentation(];
        $this->checkBackupAndRecovery(];
        $this->checkMonitoring(];
        
        $this->generateReadinessReport(];
    }
    
    private function checkProjectStructure() {
        echo "📁 检查项目结构完整�?..\n";
        
        $requiredDirs = [
            'config' => '配置文件目录',
            'public' => '公共访问目录',
            'src' => '源代码目�?,
            'storage' => '存储目录',
            'database' => '数据库目�?,
            'scripts' => '脚本目录',
            'cache' => '缓存目录'
        ];
        
        $requiredFiles = [
            'composer.json' => 'Composer配置',
            'README.md' => '项目说明文档',
            '.env' => '环境配置文件',
            'public/index.html' => '入口页面',
            'run_optimization.bat' => '优化脚本'
        ];
        
        foreach ($requiredDirs as $dir => $desc) {
            $path = $this->basePath . "/$dir";
            if (is_dir($path)) {
                echo "   �?$desc: $dir\n";
                $this->checks['structure']['dirs'][$dir] = true;
            } else {
                echo "   �?$desc 缺失: $dir\n";
                $this->checks['structure']['dirs'][$dir] = false;
                $this->criticalIssues[] = "缺失必要目录: $dir ($desc)";
            }
        }
        
        foreach ($requiredFiles as $file => $desc) {
            $path = $this->basePath . "/$file";
            if (file_exists($path)) {
                echo "   �?$desc: $file\n";
                $this->checks['structure']['files'][$file] = true;
            } else {
                echo "   ⚠️ $desc 缺失: $file\n";
                $this->checks['structure']['files'][$file] = false;
                $this->warnings[] = "建议创建文件: $file ($desc)";
            }
        }
        
        echo "\n";
    }
    
    private function checkDependencies() {
        echo "📦 检查依赖关�?..\n";
        
        // 检�?Composer 依赖
        $composerLock = $this->basePath . '/composer.lock';
        if (file_exists($composerLock)) {
            echo "   �?Composer 依赖已锁定\n";
            $this->checks['dependencies']['composer'] = true;
        } else {
            echo "   ⚠️ Composer 依赖未锁定\n";
            $this->checks['dependencies']['composer'] = false;
            $this->warnings[] = "建议运行 composer install 锁定依赖版本";
        }
        
        // 检查关�?PHP 扩展
        $requiredExtensions = [
            'json' => 'JSON处理',
            'mbstring' => '多字节字符串',
            'openssl' => 'SSL/TLS加密',
            'curl' => 'HTTP客户�?,
            'zip' => '压缩文件处理',
            'pdo' => '数据库抽象层'
        ];
        
        $optionalExtensions = [
            'gd' => '图像处理',
            'redis' => '高性能缓存',
            'opcache' => 'PHP操作码缓�?,
            'pdo_mysql' => 'MySQL数据库驱�?,
            'pdo_sqlite' => 'SQLite数据库驱�?
        ];
        
        foreach ($requiredExtensions as $ext => $desc) {
            if (extension_loaded($ext)) {
                echo "   �?必需扩展: $ext ($desc)\n";
                $this->checks['dependencies']['extensions'][$ext] = true;
            } else {
                echo "   �?必需扩展缺失: $ext ($desc)\n";
                $this->checks['dependencies']['extensions'][$ext] = false;
                $this->criticalIssues[] = "缺失必需扩展: $ext";
            }
        }
        
        foreach ($optionalExtensions as $ext => $desc) {
            if (extension_loaded($ext)) {
                echo "   �?可选扩�? $ext ($desc)\n";
                $this->checks['dependencies']['optional_extensions'][$ext] = true;
            } else {
                echo "   ⚠️ 可选扩展缺�? $ext ($desc)\n";
                $this->checks['dependencies']['optional_extensions'][$ext] = false;
                $this->recommendations[] = "建议安装扩展: $ext ($desc)";
            }
        }
        
        echo "\n";
    }
    
    private function checkConfigurations() {
        echo "⚙️ 检查配置文�?..\n";
        
        $configFiles = [
            'app.php' => '应用配置',
            'database.php' => '数据库配�?,
            'cache.php' => '缓存配置',
            'security.php' => '安全配置',
            'performance.php' => '性能配置',
            'logging.php' => '日志配置'
        ];
        
        foreach ($configFiles as $file => $desc) {
            $configPath = $this->basePath . "/config/$file";
            if (file_exists($configPath)) {
                try {
                    $config = include $configPath;
                    if (is_[$config) && !empty($config)) {
                        echo "   �?$desc: $file\n";
                        $this->checks['config'][$file] = true;
                    } else {
                        echo "   ⚠️ $desc 格式错误: $file\n";
                        $this->checks['config'][$file] = false;
                        $this->warnings[] = "配置文件格式错误: $file";
                    }
                } catch (Exception $e) {
                    echo "   �?$desc 加载失败: $file\n";
                    $this->checks['config'][$file] = false;
                    $this->criticalIssues[] = "配置文件错误: $file - " . $e->getMessage(];
                }
            } else {
                echo "   �?$desc 缺失: $file\n";
                $this->checks['config'][$file] = false;
                $this->criticalIssues[] = "缺失配置文件: $file";
            }
        }
        
        // 检查环境配�?
        $envFile = $this->basePath . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile];
            $hasApiKeys = strpos($envContent, 'API_KEY') !== false;
            $hasDbConfig = strpos($envContent, 'DB_') !== false;
            
            echo "   �?环境配置文件存在\n";
            if ($hasApiKeys) {
                echo "   �?包含API密钥配置\n";
            } else {
                echo "   ⚠️ 缺少API密钥配置\n";
                $this->warnings[] = "环境文件中缺少API密钥配置";
            }
            
            if ($hasDbConfig) {
                echo "   �?包含数据库配置\n";
            } else {
                echo "   ⚠️ 缺少数据库配置\n";
                $this->warnings[] = "环境文件中缺少数据库配置";
            }
            
            $this->checks['config']['.env'] = true;
        } else {
            echo "   �?环境配置文件缺失\n";
            $this->checks['config']['.env'] = false;
            $this->criticalIssues[] = "缺失环境配置文件 .env";
        }
        
        echo "\n";
    }
    
    private function checkSecurity() {
        echo "🛡�?检查安全配�?..\n";
        
        $securityChecks = [
            'file_permissions' => function() {
                $sensitiveFiles = ['.env', 'config/database.php', 'config/security.php'];
                $allSecure = true;
                
                foreach ($sensitiveFiles as $file) {
                    $path = $this->basePath . "/$file";
                    if (file_exists($path)) {
                        $perms = fileperms($path];
                        // 在Windows上权限检查不同，这里简化处�?
                        if (DIRECTORY_SEPARATOR === '\\') {
                            echo "      �?Windows环境下文件权�? $file\n";
                        } else {
                            $worldReadable = ($perms & 0004];
                            if ($worldReadable) {
                                echo "      �?权限过宽: $file\n";
                                $allSecure = false;
                            } else {
                                echo "      �?权限安全: $file\n";
                            }
                        }
                    }
                }
                
                return $allSecure;
            },
            
            'secret_keys' => function() {
                $envFile = $this->basePath . '/.env';
                if (file_exists($envFile)) {
                    $content = file_get_contents($envFile];
                    $hasAppKey = strpos($content, 'APP_KEY=') !== false && 
                                strpos($content, 'APP_KEY=your_') === false;
                    
                    if ($hasAppKey) {
                        echo "      �?应用密钥已配置\n";
                        return true;
                    } else {
                        echo "      ⚠️ 应用密钥未配置或使用默认值\n";
                        return false;
                    }
                }
                return false;
            },
            
            'security_headers' => function() {
                $htaccessFile = $this->basePath . '/public/.htaccess';
                if (file_exists($htaccessFile)) {
                    $content = file_get_contents($htaccessFile];
                    $hasSecurityHeaders = strpos($content, 'X-Content-Type-Options') !== false;
                    
                    if ($hasSecurityHeaders) {
                        echo "      �?安全头部配置已设置\n";
                        return true;
                    } else {
                        echo "      ⚠️ 缺少安全头部配置\n";
                        return false;
                    }
                } else {
                    echo "      ⚠️ .htaccess 文件不存在\n";
                    return false;
                }
            }
        ];
        
        foreach ($securityChecks as $checkName => $checkFunc) {
            $result = $checkFunc(];
            $this->checks['security'][$checkName] = $result;
            
            if (!$result) {
                if ($checkName === 'secret_keys') {
                    $this->criticalIssues[] = "安全检查失�? $checkName";
                } else {
                    $this->warnings[] = "安全建议: $checkName";
                }
            }
        }
        
        echo "\n";
    }
    
    private function checkPerformance() {
        echo "�?检查性能配置...\n";
        
        $performanceChecks = [
            'php_settings' => function() {
                $settings = [
                    'memory_limit' => ['current' => ini_get('memory_limit'], 'recommended' => '256M'], 
                    'max_execution_time' => ['current' => ini_get('max_execution_time'], 'recommended' => '300'], 
                    'opcache.enable' => ['current' => ini_get('opcache.enable'], 'recommended' => '1']
                ];
                
                $allOptimal = true;
                foreach ($settings as $setting => $info) {
                    $current = $info['current'];
                    $recommended = $info['recommended'];
                    
                    if ($setting === 'memory_limit') {
                        $currentBytes = $this->parseMemoryLimit($current];
                        $recommendedBytes = $this->parseMemoryLimit($recommended];
                        $optimal = $currentBytes >= $recommendedBytes;
                    } else {
                        $optimal = ($current == $recommended) || ($current === false && $setting === 'opcache.enable'];
                    }
                    
                    if ($optimal) {
                        echo "      �?$setting: $current\n";
                    } else {
                        echo "      ⚠️ $setting: $current (推荐: $recommended)\n";
                        $allOptimal = false;
                    }
                }
                
                return $allOptimal;
            },
            
            'cache_setup' => function() {
                $cacheDir = $this->basePath . '/storage/cache';
                $cacheWritable = is_dir($cacheDir) && is_writable($cacheDir];
                
                if ($cacheWritable) {
                    echo "      �?缓存目录可写\n";
                    return true;
                } else {
                    echo "      �?缓存目录不可写或不存在\n";
                    return false;
                }
            },
            
            'optimization_scripts' => function() {
                $scripts = [
                    'unified_optimizer.php',
                    'performance_tester.php',
                    'environment_setup_and_fixes.php'
                ];
                
                $allExist = true;
                foreach ($scripts as $script) {
                    $path = $this->basePath . "/scripts/$script";
                    if (file_exists($path)) {
                        echo "      �?优化脚本: $script\n";
                    } else {
                        echo "      ⚠️ 缺少优化脚本: $script\n";
                        $allExist = false;
                    }
                }
                
                return $allExist;
            }
        ];
        
        foreach ($performanceChecks as $checkName => $checkFunc) {
            $result = $checkFunc(];
            $this->checks['performance'][$checkName] = $result;
            
            if (!$result) {
                $this->recommendations[] = "性能优化建议: $checkName";
            }
        }
        
        echo "\n";
    }
    
    private function parseMemoryLimit($limit) {
        $limit = trim($limit];
        $last = strtolower($limit[strlen($limit)-1]];
        $limit = (int) $limit;
        
        switch($last) {
            case 'g': $limit *= 1024;
            case 'm': $limit *= 1024;
            case 'k': $limit *= 1024;
        }
        
        return $limit;
    }
    
    private function checkDocumentation() {
        echo "📚 检查文档完整�?..\n";
        
        $docFiles = [
            'README.md' => '项目说明',
            'COMPREHENSIVE_OPTIMIZATION_PLAN.md' => '优化计划',
            'PHP_EXTENSION_INSTALL_GUIDE.md' => '扩展安装指南',
            'ENVIRONMENT_FIX_REPORT_*.md' => '环境修复报告'
        ];
        
        foreach ($docFiles as $pattern => $desc) {
            if (strpos($pattern, '*') !== false) {
                $files = glob($this->basePath . '/' . $pattern];
                if (!empty($files)) {
                    echo "   �?$desc: " . count($files) . " 个文件\n";
                    $this->checks['documentation'][str_replace('*', 'files', $pattern)] = true;
                } else {
                    echo "   ⚠️ $desc: 未找到匹配文件\n";
                    $this->checks['documentation'][str_replace('*', 'files', $pattern)] = false;
                    $this->warnings[] = "缺少文档: $desc";
                }
            } else {
                $path = $this->basePath . "/$pattern";
                if (file_exists($path)) {
                    echo "   �?$desc: $pattern\n";
                    $this->checks['documentation'][$pattern] = true;
                } else {
                    echo "   ⚠️ $desc 缺失: $pattern\n";
                    $this->checks['documentation'][$pattern] = false;
                    $this->warnings[] = "缺少文档: $pattern";
                }
            }
        }
        
        echo "\n";
    }
    
    private function checkBackupAndRecovery() {
        echo "💾 检查备份和恢复机制...\n";
        
        $backupChecks = [
            'backup_directory' => function() {
                $backupDirs = ['backup', 'backups'];
                foreach ($backupDirs as $dir) {
                    $path = $this->basePath . "/$dir";
                    if (is_dir($path)) {
                        echo "      �?备份目录存在: $dir\n";
                        return true;
                    }
                }
                echo "      ⚠️ 未找到备份目录\n";
                return false;
            },
            
            'backup_scripts' => function() {
                $backupScript = $this->basePath . '/bin/backup.php';
                if (file_exists($backupScript)) {
                    echo "      �?备份脚本存在\n";
                    return true;
                } else {
                    echo "      ⚠️ 备份脚本不存在\n";
                    return false;
                }
            },
            
            'recovery_procedures' => function() {
                // 检查是否有恢复相关的文档或脚本
                $patterns = ['*recovery*', '*restore*', '*backup*'];
                $found = false;
                
                foreach ($patterns as $pattern) {
                    $files = glob($this->basePath . '/' . $pattern];
                    if (!empty($files)) {
                        $found = true;
                        break;
                    }
                }
                
                if ($found) {
                    echo "      �?找到恢复相关文档或脚本\n";
                    return true;
                } else {
                    echo "      ⚠️ 未找到恢复程序文档\n";
                    return false;
                }
            }
        ];
        
        foreach ($backupChecks as $checkName => $checkFunc) {
            $result = $checkFunc(];
            $this->checks['backup'][$checkName] = $result;
            
            if (!$result) {
                $this->recommendations[] = "备份建议: 设置 $checkName";
            }
        }
        
        echo "\n";
    }
    
    private function checkMonitoring() {
        echo "📊 检查监控和日志配置...\n";
        
        $monitoringChecks = [
            'log_directories' => function() {
                $logDir = $this->basePath . '/storage/logs';
                if (is_dir($logDir) && is_writable($logDir)) {
                    echo "      �?日志目录配置正确\n";
                    return true;
                } else {
                    echo "      ⚠️ 日志目录不存在或不可写\n";
                    return false;
                }
            },
            
            'health_check' => function() {
                $healthScript = $this->basePath . '/bin/health-check.php';
                if (file_exists($healthScript)) {
                    echo "      �?健康检查脚本存在\n";
                    return true;
                } else {
                    echo "      ⚠️ 健康检查脚本不存在\n";
                    return false;
                }
            },
            
            'performance_monitoring' => function() {
                $perfScript = $this->basePath . '/scripts/performance_tester.php';
                if (file_exists($perfScript)) {
                    echo "      �?性能监控脚本存在\n";
                    return true;
                } else {
                    echo "      ⚠️ 性能监控脚本不存在\n";
                    return false;
                }
            }
        ];
        
        foreach ($monitoringChecks as $checkName => $checkFunc) {
            $result = $checkFunc(];
            $this->checks['monitoring'][$checkName] = $result;
            
            if (!$result) {
                $this->recommendations[] = "监控建议: 配置 $checkName";
            }
        }
        
        echo "\n";
    }
    
    private function generateReadinessReport() {
        $timestamp = date('Y_m_d_H_i_s'];
        $reportFile = $this->basePath . "/DEPLOYMENT_READINESS_REPORT_$timestamp.json";
        
        // 计算就绪分数
        $totalChecks = 0;
        $passedChecks = 0;
        
        foreach ($this->checks as $category => $categoryChecks) {
            foreach ($categoryChecks as $check => $result) {
                if (is_[$result)) {
                    foreach ($result as $subResult) {
                        $totalChecks++;
                        if ($subResult) $passedChecks++;
                    }
                } else {
                    $totalChecks++;
                    if ($result) $passedChecks++;
                }
            }
        }
        
        $readinessScore = $totalChecks > 0 ? ($passedChecks / $totalChecks) * 100 : 0;
        
        // 确定部署状�?
        $deploymentStatus = 'not_ready';
        if (empty($this->criticalIssues)) {
            if ($readinessScore >= 80) {
                $deploymentStatus = 'ready';
            } elseif ($readinessScore >= 60) {
                $deploymentStatus = 'almost_ready';
            } else {
                $deploymentStatus = 'needs_work';
            }
        }
        
        $report = [
            'check_info' => [
                'timestamp' => date('Y-m-d H:i:s'],
                'version' => '1.0',
                'project' => 'AlingAi Pro 5.0'
            ], 
            'summary' => [
                'readiness_score' => $readinessScore,
                'deployment_status' => $deploymentStatus,
                'total_checks' => $totalChecks,
                'passed_checks' => $passedChecks,
                'critical_issues' => count($this->criticalIssues],
                'warnings' => count($this->warnings],
                'recommendations' => count($this->recommendations)
            ], 
            'detailed_checks' => $this->checks,
            'issues' => [
                'critical' => $this->criticalIssues,
                'warnings' => $this->warnings,
                'recommendations' => $this->recommendations
            ]
        ];
        
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT)];
        
        // 显示摘要
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "�?                   📊 部署就绪检查摘�?                       ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "�? 就绪分数: " . number_format($readinessScore, 1) . "%                                        ║\n";
        echo "�? 部署状�? " . $this->getStatusText($deploymentStatus) . str_repeat(' ', 30 - mb_strlen($this->getStatusText($deploymentStatus], 'UTF-8')) . "║\n";
        echo "�? 关键问题: " . count($this->criticalIssues) . " �?                                           ║\n";
        echo "�? 警告信息: " . count($this->warnings) . " �?                                           ║\n";
        echo "�? 建议项目: " . count($this->recommendations) . " �?                                           ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
        
        if (!empty($this->criticalIssues)) {
            echo "🚨 关键问题（必须修复）:\n";
            foreach ($this->criticalIssues as $i => $issue) {
                echo "   " . ($i + 1) . ". $issue\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "⚠️ 警告信息（建议修复）:\n";
            foreach ($this->warnings as $i => $warning) {
                echo "   " . ($i + 1) . ". $warning\n";
            }
            echo "\n";
        }
        
        if (!empty($this->recommendations)) {
            echo "💡 优化建议:\n";
            foreach ($this->recommendations as $i => $rec) {
                echo "   " . ($i + 1) . ". $rec\n";
            }
            echo "\n";
        }
        
        echo "📄 详细报告已保存到: " . basename($reportFile) . "\n\n";
        
        // 部署建议
        $this->printDeploymentAdvice($deploymentStatus];
    }
    
    private function getStatusText($status) {
        switch ($status) {
            case 'ready': return '�?准备就绪';
            case 'almost_ready': return '🟡 接近就绪';
            case 'needs_work': return '🟠 需要改�?;
            case 'not_ready': return '🔴 未准备好';
            default: return '�?未知状�?;
        }
    }
    
    private function printDeploymentAdvice($status) {
        echo "🚀 部署建议:\n";
        
        switch ($status) {
            case 'ready':
                echo "   🎉 系统已准备好部署到生产环境！\n";
                echo "   📝 建议在部署前:\n";
                echo "      1. 进行最终的备份\n";
                echo "      2. 设置监控和日志\n";
                echo "      3. 准备回滚计划\n";
                echo "      4. 进行负载测试\n";
                break;
                
            case 'almost_ready':
                echo "   🟡 系统基本就绪，建议先解决警告问题\n";
                echo "   📝 部署前清�?\n";
                echo "      1. 解决上述警告问题\n";
                echo "      2. 完善监控配置\n";
                echo "      3. 测试所有关键功能\n";
                break;
                
            case 'needs_work':
                echo "   🟠 系统需要进一步优化\n";
                echo "   📝 优化建议:\n";
                echo "      1. 按优先级解决上述问题\n";
                echo "      2. 完善缺失的配置\n";
                echo "      3. 再次运行就绪检查\n";
                break;
                
            case 'not_ready':
                echo "   🔴 系统存在关键问题，不建议部署\n";
                echo "   📝 紧急修�?\n";
                echo "      1. 立即解决所有关键问题\n";
                echo "      2. 完善基础配置\n";
                echo "      3. 重新进行就绪检查\n";
                break;
        }
        
        echo "\n";
    }
}

// 执行部署就绪检�?
echo "正在启动 AlingAi Pro 5.0 部署就绪检�?..\n\n";
$checker = new DeploymentReadinessChecker(];
$checker->runDeploymentChecks(];

?>

