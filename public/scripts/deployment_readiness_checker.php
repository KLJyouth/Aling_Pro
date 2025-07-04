<?php

/**
 * ð AlingAi Pro 5.0 é¨ç½²å°±ç»ªæ£æ¥å·¥å?
 * å¨é¢æ£æ¥ç³»ç»æ¯å¦åå¤å¥½é¨ç½²å°çäº§ç¯å¢?
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
        echo "ââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ\n";
        echo "â?             ð é¨ç½²å°±ç»ªæ£æ¥å·¥å?                           â\n";
        echo "â âââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ£\n";
        echo "â? æ£æ¥æ¶é? " . date('Y-m-d H:i:s') . str_repeat(' ', 25) . "â\n";
        echo "â? é¡¹ç®è·¯å¾: " . substr($this->basePath, 0, 40) . str_repeat(' ', 15) . "â\n";
        echo "ââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ\n\n";
    }
    
    public function runDeploymentChecks() {
        echo "ð å¼å§é¨ç½²å°±ç»ªæ£æ?..\n\n";
        
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
        echo "ð æ£æ¥é¡¹ç®ç»æå®æ´æ?..\n";
        
        $requiredDirs = [
            'config' => 'éç½®æä»¶ç®å½',
            'public' => 'å¬å±è®¿é®ç®å½',
            'src' => 'æºä»£ç ç®å½?,
            'storage' => 'å­å¨ç®å½',
            'database' => 'æ°æ®åºç®å½?,
            'scripts' => 'èæ¬ç®å½',
            'cache' => 'ç¼å­ç®å½'
        ];
        
        $requiredFiles = [
            'composer.json' => 'Composeréç½®',
            'README.md' => 'é¡¹ç®è¯´æææ¡£',
            '.env' => 'ç¯å¢éç½®æä»¶',
            'public/index.html' => 'å¥å£é¡µé¢',
            'run_optimization.bat' => 'ä¼åèæ¬'
        ];
        
        foreach ($requiredDirs as $dir => $desc) {
            $path = $this->basePath . "/$dir";
            if (is_dir($path)) {
                echo "   â?$desc: $dir\n";
                $this->checks['structure']['dirs'][$dir] = true;
            } else {
                echo "   â?$desc ç¼ºå¤±: $dir\n";
                $this->checks['structure']['dirs'][$dir] = false;
                $this->criticalIssues[] = "ç¼ºå¤±å¿è¦ç®å½: $dir ($desc)";
            }
        }
        
        foreach ($requiredFiles as $file => $desc) {
            $path = $this->basePath . "/$file";
            if (file_exists($path)) {
                echo "   â?$desc: $file\n";
                $this->checks['structure']['files'][$file] = true;
            } else {
                echo "   â ï¸ $desc ç¼ºå¤±: $file\n";
                $this->checks['structure']['files'][$file] = false;
                $this->warnings[] = "å»ºè®®åå»ºæä»¶: $file ($desc)";
            }
        }
        
        echo "\n";
    }
    
    private function checkDependencies() {
        echo "ð¦ æ£æ¥ä¾èµå³ç³?..\n";
        
        // æ£æ?Composer ä¾èµ
        $composerLock = $this->basePath . '/composer.lock';
        if (file_exists($composerLock)) {
            echo "   â?Composer ä¾èµå·²éå®\n";
            $this->checks['dependencies']['composer'] = true;
        } else {
            echo "   â ï¸ Composer ä¾èµæªéå®\n";
            $this->checks['dependencies']['composer'] = false;
            $this->warnings[] = "å»ºè®®è¿è¡ composer install éå®ä¾èµçæ¬";
        }
        
        // æ£æ¥å³é?PHP æ©å±
        $requiredExtensions = [
            'json' => 'JSONå¤ç',
            'mbstring' => 'å¤å­èå­ç¬¦ä¸²',
            'openssl' => 'SSL/TLSå å¯',
            'curl' => 'HTTPå®¢æ·ç«?,
            'zip' => 'åç¼©æä»¶å¤ç',
            'pdo' => 'æ°æ®åºæ½è±¡å±'
        ];
        
        $optionalExtensions = [
            'gd' => 'å¾åå¤ç',
            'redis' => 'é«æ§è½ç¼å­',
            'opcache' => 'PHPæä½ç ç¼å­?,
            'pdo_mysql' => 'MySQLæ°æ®åºé©±å?,
            'pdo_sqlite' => 'SQLiteæ°æ®åºé©±å?
        ];
        
        foreach ($requiredExtensions as $ext => $desc) {
            if (extension_loaded($ext)) {
                echo "   â?å¿éæ©å±: $ext ($desc)\n";
                $this->checks['dependencies']['extensions'][$ext] = true;
            } else {
                echo "   â?å¿éæ©å±ç¼ºå¤±: $ext ($desc)\n";
                $this->checks['dependencies']['extensions'][$ext] = false;
                $this->criticalIssues[] = "ç¼ºå¤±å¿éæ©å±: $ext";
            }
        }
        
        foreach ($optionalExtensions as $ext => $desc) {
            if (extension_loaded($ext)) {
                echo "   â?å¯éæ©å±? $ext ($desc)\n";
                $this->checks['dependencies']['optional_extensions'][$ext] = true;
            } else {
                echo "   â ï¸ å¯éæ©å±ç¼ºå¤? $ext ($desc)\n";
                $this->checks['dependencies']['optional_extensions'][$ext] = false;
                $this->recommendations[] = "å»ºè®®å®è£æ©å±: $ext ($desc)";
            }
        }
        
        echo "\n";
    }
    
    private function checkConfigurations() {
        echo "âï¸ æ£æ¥éç½®æä»?..\n";
        
        $configFiles = [
            'app.php' => 'åºç¨éç½®',
            'database.php' => 'æ°æ®åºéç½?,
            'cache.php' => 'ç¼å­éç½®',
            'security.php' => 'å®å¨éç½®',
            'performance.php' => 'æ§è½éç½®',
            'logging.php' => 'æ¥å¿éç½®'
        ];
        
        foreach ($configFiles as $file => $desc) {
            $configPath = $this->basePath . "/config/$file";
            if (file_exists($configPath)) {
                try {
                    $config = include $configPath;
                    if (is_[$config) && !empty($config)) {
                        echo "   â?$desc: $file\n";
                        $this->checks['config'][$file] = true;
                    } else {
                        echo "   â ï¸ $desc æ ¼å¼éè¯¯: $file\n";
                        $this->checks['config'][$file] = false;
                        $this->warnings[] = "éç½®æä»¶æ ¼å¼éè¯¯: $file";
                    }
                } catch (Exception $e) {
                    echo "   â?$desc å è½½å¤±è´¥: $file\n";
                    $this->checks['config'][$file] = false;
                    $this->criticalIssues[] = "éç½®æä»¶éè¯¯: $file - " . $e->getMessage(];
                }
            } else {
                echo "   â?$desc ç¼ºå¤±: $file\n";
                $this->checks['config'][$file] = false;
                $this->criticalIssues[] = "ç¼ºå¤±éç½®æä»¶: $file";
            }
        }
        
        // æ£æ¥ç¯å¢éç½?
        $envFile = $this->basePath . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile];
            $hasApiKeys = strpos($envContent, 'API_KEY') !== false;
            $hasDbConfig = strpos($envContent, 'DB_') !== false;
            
            echo "   â?ç¯å¢éç½®æä»¶å­å¨\n";
            if ($hasApiKeys) {
                echo "   â?åå«APIå¯é¥éç½®\n";
            } else {
                echo "   â ï¸ ç¼ºå°APIå¯é¥éç½®\n";
                $this->warnings[] = "ç¯å¢æä»¶ä¸­ç¼ºå°APIå¯é¥éç½®";
            }
            
            if ($hasDbConfig) {
                echo "   â?åå«æ°æ®åºéç½®\n";
            } else {
                echo "   â ï¸ ç¼ºå°æ°æ®åºéç½®\n";
                $this->warnings[] = "ç¯å¢æä»¶ä¸­ç¼ºå°æ°æ®åºéç½®";
            }
            
            $this->checks['config']['.env'] = true;
        } else {
            echo "   â?ç¯å¢éç½®æä»¶ç¼ºå¤±\n";
            $this->checks['config']['.env'] = false;
            $this->criticalIssues[] = "ç¼ºå¤±ç¯å¢éç½®æä»¶ .env";
        }
        
        echo "\n";
    }
    
    private function checkSecurity() {
        echo "ð¡ï¸?æ£æ¥å®å¨éç½?..\n";
        
        $securityChecks = [
            'file_permissions' => function() {
                $sensitiveFiles = ['.env', 'config/database.php', 'config/security.php'];
                $allSecure = true;
                
                foreach ($sensitiveFiles as $file) {
                    $path = $this->basePath . "/$file";
                    if (file_exists($path)) {
                        $perms = fileperms($path];
                        // å¨Windowsä¸æéæ£æ¥ä¸åï¼è¿éç®åå¤ç?
                        if (DIRECTORY_SEPARATOR === '\\') {
                            echo "      â?Windowsç¯å¢ä¸æä»¶æé? $file\n";
                        } else {
                            $worldReadable = ($perms & 0004];
                            if ($worldReadable) {
                                echo "      â?æéè¿å®½: $file\n";
                                $allSecure = false;
                            } else {
                                echo "      â?æéå®å¨: $file\n";
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
                        echo "      â?åºç¨å¯é¥å·²éç½®\n";
                        return true;
                    } else {
                        echo "      â ï¸ åºç¨å¯é¥æªéç½®æä½¿ç¨é»è®¤å¼\n";
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
                        echo "      â?å®å¨å¤´é¨éç½®å·²è®¾ç½®\n";
                        return true;
                    } else {
                        echo "      â ï¸ ç¼ºå°å®å¨å¤´é¨éç½®\n";
                        return false;
                    }
                } else {
                    echo "      â ï¸ .htaccess æä»¶ä¸å­å¨\n";
                    return false;
                }
            }
        ];
        
        foreach ($securityChecks as $checkName => $checkFunc) {
            $result = $checkFunc(];
            $this->checks['security'][$checkName] = $result;
            
            if (!$result) {
                if ($checkName === 'secret_keys') {
                    $this->criticalIssues[] = "å®å¨æ£æ¥å¤±è´? $checkName";
                } else {
                    $this->warnings[] = "å®å¨å»ºè®®: $checkName";
                }
            }
        }
        
        echo "\n";
    }
    
    private function checkPerformance() {
        echo "â?æ£æ¥æ§è½éç½®...\n";
        
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
                        echo "      â?$setting: $current\n";
                    } else {
                        echo "      â ï¸ $setting: $current (æ¨è: $recommended)\n";
                        $allOptimal = false;
                    }
                }
                
                return $allOptimal;
            },
            
            'cache_setup' => function() {
                $cacheDir = $this->basePath . '/storage/cache';
                $cacheWritable = is_dir($cacheDir) && is_writable($cacheDir];
                
                if ($cacheWritable) {
                    echo "      â?ç¼å­ç®å½å¯å\n";
                    return true;
                } else {
                    echo "      â?ç¼å­ç®å½ä¸å¯åæä¸å­å¨\n";
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
                        echo "      â?ä¼åèæ¬: $script\n";
                    } else {
                        echo "      â ï¸ ç¼ºå°ä¼åèæ¬: $script\n";
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
                $this->recommendations[] = "æ§è½ä¼åå»ºè®®: $checkName";
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
        echo "ð æ£æ¥ææ¡£å®æ´æ?..\n";
        
        $docFiles = [
            'README.md' => 'é¡¹ç®è¯´æ',
            'COMPREHENSIVE_OPTIMIZATION_PLAN.md' => 'ä¼åè®¡å',
            'PHP_EXTENSION_INSTALL_GUIDE.md' => 'æ©å±å®è£æå',
            'ENVIRONMENT_FIX_REPORT_*.md' => 'ç¯å¢ä¿®å¤æ¥å'
        ];
        
        foreach ($docFiles as $pattern => $desc) {
            if (strpos($pattern, '*') !== false) {
                $files = glob($this->basePath . '/' . $pattern];
                if (!empty($files)) {
                    echo "   â?$desc: " . count($files) . " ä¸ªæä»¶\n";
                    $this->checks['documentation'][str_replace('*', 'files', $pattern)] = true;
                } else {
                    echo "   â ï¸ $desc: æªæ¾å°å¹éæä»¶\n";
                    $this->checks['documentation'][str_replace('*', 'files', $pattern)] = false;
                    $this->warnings[] = "ç¼ºå°ææ¡£: $desc";
                }
            } else {
                $path = $this->basePath . "/$pattern";
                if (file_exists($path)) {
                    echo "   â?$desc: $pattern\n";
                    $this->checks['documentation'][$pattern] = true;
                } else {
                    echo "   â ï¸ $desc ç¼ºå¤±: $pattern\n";
                    $this->checks['documentation'][$pattern] = false;
                    $this->warnings[] = "ç¼ºå°ææ¡£: $pattern";
                }
            }
        }
        
        echo "\n";
    }
    
    private function checkBackupAndRecovery() {
        echo "ð¾ æ£æ¥å¤ä»½åæ¢å¤æºå¶...\n";
        
        $backupChecks = [
            'backup_directory' => function() {
                $backupDirs = ['backup', 'backups'];
                foreach ($backupDirs as $dir) {
                    $path = $this->basePath . "/$dir";
                    if (is_dir($path)) {
                        echo "      â?å¤ä»½ç®å½å­å¨: $dir\n";
                        return true;
                    }
                }
                echo "      â ï¸ æªæ¾å°å¤ä»½ç®å½\n";
                return false;
            },
            
            'backup_scripts' => function() {
                $backupScript = $this->basePath . '/bin/backup.php';
                if (file_exists($backupScript)) {
                    echo "      â?å¤ä»½èæ¬å­å¨\n";
                    return true;
                } else {
                    echo "      â ï¸ å¤ä»½èæ¬ä¸å­å¨\n";
                    return false;
                }
            },
            
            'recovery_procedures' => function() {
                // æ£æ¥æ¯å¦ææ¢å¤ç¸å³çææ¡£æèæ¬
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
                    echo "      â?æ¾å°æ¢å¤ç¸å³ææ¡£æèæ¬\n";
                    return true;
                } else {
                    echo "      â ï¸ æªæ¾å°æ¢å¤ç¨åºææ¡£\n";
                    return false;
                }
            }
        ];
        
        foreach ($backupChecks as $checkName => $checkFunc) {
            $result = $checkFunc(];
            $this->checks['backup'][$checkName] = $result;
            
            if (!$result) {
                $this->recommendations[] = "å¤ä»½å»ºè®®: è®¾ç½® $checkName";
            }
        }
        
        echo "\n";
    }
    
    private function checkMonitoring() {
        echo "ð æ£æ¥çæ§åæ¥å¿éç½®...\n";
        
        $monitoringChecks = [
            'log_directories' => function() {
                $logDir = $this->basePath . '/storage/logs';
                if (is_dir($logDir) && is_writable($logDir)) {
                    echo "      â?æ¥å¿ç®å½éç½®æ­£ç¡®\n";
                    return true;
                } else {
                    echo "      â ï¸ æ¥å¿ç®å½ä¸å­å¨æä¸å¯å\n";
                    return false;
                }
            },
            
            'health_check' => function() {
                $healthScript = $this->basePath . '/bin/health-check.php';
                if (file_exists($healthScript)) {
                    echo "      â?å¥åº·æ£æ¥èæ¬å­å¨\n";
                    return true;
                } else {
                    echo "      â ï¸ å¥åº·æ£æ¥èæ¬ä¸å­å¨\n";
                    return false;
                }
            },
            
            'performance_monitoring' => function() {
                $perfScript = $this->basePath . '/scripts/performance_tester.php';
                if (file_exists($perfScript)) {
                    echo "      â?æ§è½çæ§èæ¬å­å¨\n";
                    return true;
                } else {
                    echo "      â ï¸ æ§è½çæ§èæ¬ä¸å­å¨\n";
                    return false;
                }
            }
        ];
        
        foreach ($monitoringChecks as $checkName => $checkFunc) {
            $result = $checkFunc(];
            $this->checks['monitoring'][$checkName] = $result;
            
            if (!$result) {
                $this->recommendations[] = "çæ§å»ºè®®: éç½® $checkName";
            }
        }
        
        echo "\n";
    }
    
    private function generateReadinessReport() {
        $timestamp = date('Y_m_d_H_i_s'];
        $reportFile = $this->basePath . "/DEPLOYMENT_READINESS_REPORT_$timestamp.json";
        
        // è®¡ç®å°±ç»ªåæ°
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
        
        // ç¡®å®é¨ç½²ç¶æ?
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
        
        // æ¾ç¤ºæè¦
        echo "ââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ\n";
        echo "â?                   ð é¨ç½²å°±ç»ªæ£æ¥æè¦?                       â\n";
        echo "â âââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ£\n";
        echo "â? å°±ç»ªåæ°: " . number_format($readinessScore, 1) . "%                                        â\n";
        echo "â? é¨ç½²ç¶æ? " . $this->getStatusText($deploymentStatus) . str_repeat(' ', 30 - mb_strlen($this->getStatusText($deploymentStatus], 'UTF-8')) . "â\n";
        echo "â? å³é®é®é¢: " . count($this->criticalIssues) . " ä¸?                                           â\n";
        echo "â? è­¦åä¿¡æ¯: " . count($this->warnings) . " ä¸?                                           â\n";
        echo "â? å»ºè®®é¡¹ç®: " . count($this->recommendations) . " ä¸?                                           â\n";
        echo "ââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ\n\n";
        
        if (!empty($this->criticalIssues)) {
            echo "ð¨ å³é®é®é¢ï¼å¿é¡»ä¿®å¤ï¼:\n";
            foreach ($this->criticalIssues as $i => $issue) {
                echo "   " . ($i + 1) . ". $issue\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "â ï¸ è­¦åä¿¡æ¯ï¼å»ºè®®ä¿®å¤ï¼:\n";
            foreach ($this->warnings as $i => $warning) {
                echo "   " . ($i + 1) . ". $warning\n";
            }
            echo "\n";
        }
        
        if (!empty($this->recommendations)) {
            echo "ð¡ ä¼åå»ºè®®:\n";
            foreach ($this->recommendations as $i => $rec) {
                echo "   " . ($i + 1) . ". $rec\n";
            }
            echo "\n";
        }
        
        echo "ð è¯¦ç»æ¥åå·²ä¿å­å°: " . basename($reportFile) . "\n\n";
        
        // é¨ç½²å»ºè®®
        $this->printDeploymentAdvice($deploymentStatus];
    }
    
    private function getStatusText($status) {
        switch ($status) {
            case 'ready': return 'â?åå¤å°±ç»ª';
            case 'almost_ready': return 'ð¡ æ¥è¿å°±ç»ª';
            case 'needs_work': return 'ð  éè¦æ¹è¿?;
            case 'not_ready': return 'ð´ æªåå¤å¥½';
            default: return 'â?æªç¥ç¶æ?;
        }
    }
    
    private function printDeploymentAdvice($status) {
        echo "ð é¨ç½²å»ºè®®:\n";
        
        switch ($status) {
            case 'ready':
                echo "   ð ç³»ç»å·²åå¤å¥½é¨ç½²å°çäº§ç¯å¢ï¼\n";
                echo "   ð å»ºè®®å¨é¨ç½²å:\n";
                echo "      1. è¿è¡æç»çå¤ä»½\n";
                echo "      2. è®¾ç½®çæ§åæ¥å¿\n";
                echo "      3. åå¤åæ»è®¡å\n";
                echo "      4. è¿è¡è´è½½æµè¯\n";
                break;
                
            case 'almost_ready':
                echo "   ð¡ ç³»ç»åºæ¬å°±ç»ªï¼å»ºè®®åè§£å³è­¦åé®é¢\n";
                echo "   ð é¨ç½²åæ¸å?\n";
                echo "      1. è§£å³ä¸è¿°è­¦åé®é¢\n";
                echo "      2. å®åçæ§éç½®\n";
                echo "      3. æµè¯ææå³é®åè½\n";
                break;
                
            case 'needs_work':
                echo "   ð  ç³»ç»éè¦è¿ä¸æ­¥ä¼å\n";
                echo "   ð ä¼åå»ºè®®:\n";
                echo "      1. æä¼åçº§è§£å³ä¸è¿°é®é¢\n";
                echo "      2. å®åç¼ºå¤±çéç½®\n";
                echo "      3. åæ¬¡è¿è¡å°±ç»ªæ£æ¥\n";
                break;
                
            case 'not_ready':
                echo "   ð´ ç³»ç»å­å¨å³é®é®é¢ï¼ä¸å»ºè®®é¨ç½²\n";
                echo "   ð ç´§æ¥ä¿®å¤?\n";
                echo "      1. ç«å³è§£å³ææå³é®é®é¢\n";
                echo "      2. å®ååºç¡éç½®\n";
                echo "      3. éæ°è¿è¡å°±ç»ªæ£æ¥\n";
                break;
        }
        
        echo "\n";
    }
}

// æ§è¡é¨ç½²å°±ç»ªæ£æ?
echo "æ­£å¨å¯å¨ AlingAi Pro 5.0 é¨ç½²å°±ç»ªæ£æ?..\n\n";
$checker = new DeploymentReadinessChecker(];
$checker->runDeploymentChecks(];

?>

