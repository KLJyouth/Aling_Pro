<?php

/**
 * 🚀 AlingAi Pro 5.0 系统性能测试和优化验证工具
 * 全面测试优化后系统的性能表现
 * 
 * @version 1.0
 * @author AlingAi Team
 * @created 2025-06-11
 */

class SystemPerformanceTester {
    private $basePath;
    private $results = [];
    private $startTime;
    
    public function __construct($basePath = null) {
        $this->basePath = $basePath ?: dirname(__DIR__);
        $this->startTime = microtime(true);
        $this->initializeReport();
    }
    
    private function initializeReport() {
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║            🚀 系统性能测试和优化验证工具                      ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  测试时间: " . date('Y-m-d H:i:s') . str_repeat(' ', 25) . "║\n";
        echo "║  项目路径: " . substr($this->basePath, 0, 40) . str_repeat(' ', 15) . "║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    }
    
    public function runComprehensiveTests() {
        echo "🔍 开始系统性能测试...\n\n";
        
        $this->testFileSystemPerformance();
        $this->testDatabasePerformance();
        $this->testConfigurationOptimizations();
        $this->testSecurityFeatures();
        $this->testCachePerformance();
        $this->testResourceOptimization();
        $this->generatePerformanceReport();
        
        $this->generateFinalReport();
    }
    
    private function testFileSystemPerformance() {
        echo "📁 测试文件系统性能...\n";
        
        $tests = [
            'file_read' => function() {
                $file = $this->basePath . '/README.md';
                $start = microtime(true);
                $content = file_get_contents($file);
                return microtime(true) - $start;
            },
            'file_write' => function() {
                $file = $this->basePath . '/tmp/test_write.txt';
                $dir = dirname($file);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                
                $start = microtime(true);
                file_put_contents($file, str_repeat('Test data ', 1000));
                $result = microtime(true) - $start;
                unlink($file);
                return $result;
            },
            'directory_scan' => function() {
                $start = microtime(true);
                $files = glob($this->basePath . '/*');
                return microtime(true) - $start;
            }
        ];
        
        foreach ($tests as $testName => $testFunc) {
            $time = $testFunc();
            $this->results['filesystem'][$testName] = $time;
            $status = $time < 0.1 ? "✅" : ($time < 0.5 ? "⚠️" : "❌");
            echo "   $status $testName: " . number_format($time * 1000, 2) . "ms\n";
        }
        
        echo "\n";
    }
    
    private function testDatabasePerformance() {
        echo "🗃️ 测试数据库性能...\n";
        
        $fileDbPath = $this->basePath . '/database/filedb';
        if (!is_dir($fileDbPath)) {
            echo "   ❌ 文件数据库目录不存在\n\n";
            return;
        }
        
        // 测试文件数据库操作
        $tests = [
            'read_users' => function() use ($fileDbPath) {
                $start = microtime(true);
                $data = json_decode(file_get_contents($fileDbPath . '/users.json'), true);
                return microtime(true) - $start;
            },
            'write_log' => function() use ($fileDbPath) {
                $logsFile = $fileDbPath . '/system_logs.json';
                $logs = json_decode(file_get_contents($logsFile), true) ?: [];
                
                $start = microtime(true);
                $logs[] = [
                    'id' => count($logs) + 1,
                    'level' => 'test',
                    'message' => 'Performance test log',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT));
                return microtime(true) - $start;
            },
            'search_records' => function() use ($fileDbPath) {
                $start = microtime(true);
                $users = json_decode(file_get_contents($fileDbPath . '/users.json'), true);
                $admins = array_filter($users, function($user) {
                    return isset($user['role']) && $user['role'] === 'admin';
                });
                return microtime(true) - $start;
            }
        ];
        
        foreach ($tests as $testName => $testFunc) {
            try {
                $time = $testFunc();
                $this->results['database'][$testName] = $time;
                $status = $time < 0.05 ? "✅" : ($time < 0.2 ? "⚠️" : "❌");
                echo "   $status $testName: " . number_format($time * 1000, 2) . "ms\n";
            } catch (Exception $e) {
                echo "   ❌ $testName: 失败 - " . $e->getMessage() . "\n";
                $this->results['database'][$testName] = 'error';
            }
        }
        
        echo "\n";
    }
    
    private function testConfigurationOptimizations() {
        echo "⚙️ 测试配置优化效果...\n";
        
        $configs = [
            'app.php' => 'app',
            'database.php' => 'database',
            'cache.php' => 'cache',
            'security.php' => 'security',
            'performance.php' => 'performance'
        ];
        
        foreach ($configs as $file => $type) {
            $configFile = $this->basePath . "/config/$file";
            if (file_exists($configFile)) {
                $start = microtime(true);
                $config = include $configFile;
                $time = microtime(true) - $start;
                
                $isValid = is_array($config) && !empty($config);
                $status = $isValid ? "✅" : "❌";
                $this->results['config'][$type] = ['time' => $time, 'valid' => $isValid];
                
                echo "   $status $type 配置: " . number_format($time * 1000, 2) . "ms\n";
            } else {
                echo "   ❌ $type 配置: 文件不存在\n";
                $this->results['config'][$type] = ['time' => 0, 'valid' => false];
            }
        }
        
        echo "\n";
    }
    
    private function testSecurityFeatures() {
        echo "🛡️ 测试安全功能...\n";
        
        $securityTests = [
            'csrf_token' => function() {
                $start = microtime(true);
                $token = bin2hex(random_bytes(32));
                return microtime(true) - $start;
            },
            'password_hash' => function() {
                $start = microtime(true);
                $hash = password_hash('test_password', PASSWORD_DEFAULT);
                return microtime(true) - $start;
            },
            'session_id' => function() {
                $start = microtime(true);
                $sessionId = session_create_id();
                return microtime(true) - $start;
            },
            'encryption' => function() {
                $key = random_bytes(32);
                $data = 'Sensitive data for testing';
                
                $start = microtime(true);
                $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, random_bytes(16));
                return microtime(true) - $start;
            }
        ];
        
        foreach ($securityTests as $testName => $testFunc) {
            try {
                $time = $testFunc();
                $this->results['security'][$testName] = $time;
                $status = $time < 0.01 ? "✅" : ($time < 0.05 ? "⚠️" : "❌");
                echo "   $status $testName: " . number_format($time * 1000, 2) . "ms\n";
            } catch (Exception $e) {
                echo "   ❌ $testName: 失败 - " . $e->getMessage() . "\n";
                $this->results['security'][$testName] = 'error';
            }
        }
        
        echo "\n";
    }
    
    private function testCachePerformance() {
        echo "💾 测试缓存性能...\n";
        
        $cacheDir = $this->basePath . '/storage/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheTests = [
            'file_cache_write' => function() use ($cacheDir) {
                $cacheFile = $cacheDir . '/test_cache.json';
                $data = ['test' => 'data', 'timestamp' => time()];
                
                $start = microtime(true);
                file_put_contents($cacheFile, json_encode($data));
                return microtime(true) - $start;
            },
            'file_cache_read' => function() use ($cacheDir) {
                $cacheFile = $cacheDir . '/test_cache.json';
                
                $start = microtime(true);
                $data = json_decode(file_get_contents($cacheFile), true);
                return microtime(true) - $start;
            },
            'memory_cache' => function() {
                static $memoryCache = [];
                $key = 'test_key_' . mt_rand();
                $value = ['data' => str_repeat('x', 1000)];
                
                $start = microtime(true);
                $memoryCache[$key] = $value;
                $retrieved = $memoryCache[$key];
                return microtime(true) - $start;
            }
        ];
        
        foreach ($cacheTests as $testName => $testFunc) {
            try {
                $time = $testFunc();
                $this->results['cache'][$testName] = $time;
                $status = $time < 0.01 ? "✅" : ($time < 0.05 ? "⚠️" : "❌");
                echo "   $status $testName: " . number_format($time * 1000, 2) . "ms\n";
            } catch (Exception $e) {
                echo "   ❌ $testName: 失败 - " . $e->getMessage() . "\n";
                $this->results['cache'][$testName] = 'error';
            }
        }
        
        // 清理测试缓存
        $testFile = $cacheDir . '/test_cache.json';
        if (file_exists($testFile)) {
            unlink($testFile);
        }
        
        echo "\n";
    }
    
    private function testResourceOptimization() {
        echo "📦 测试资源优化...\n";
        
        $resourceTests = [
            'memory_usage' => function() {
                return memory_get_usage(true);
            },
            'peak_memory' => function() {
                return memory_get_peak_usage(true);
            },
            'cpu_time' => function() {
                $start = getrusage();
                // 模拟CPU密集操作
                for ($i = 0; $i < 10000; $i++) {
                    hash('sha256', 'test_data_' . $i);
                }
                $end = getrusage();
                return ($end['ru_utime.tv_sec'] - $start['ru_utime.tv_sec']) + 
                       (($end['ru_utime.tv_usec'] - $start['ru_utime.tv_usec']) / 1000000);
            }
        ];
        
        foreach ($resourceTests as $testName => $testFunc) {
            try {
                $value = $testFunc();
                $this->results['resources'][$testName] = $value;
                
                if ($testName === 'memory_usage' || $testName === 'peak_memory') {
                    $mb = round($value / 1024 / 1024, 2);
                    $status = $mb < 32 ? "✅" : ($mb < 64 ? "⚠️" : "❌");
                    echo "   $status $testName: {$mb}MB\n";
                } else {
                    $status = $value < 0.1 ? "✅" : ($value < 0.5 ? "⚠️" : "❌");
                    echo "   $status $testName: " . number_format($value, 4) . "s\n";
                }
            } catch (Exception $e) {
                echo "   ❌ $testName: 失败 - " . $e->getMessage() . "\n";
                $this->results['resources'][$testName] = 'error';
            }
        }
        
        echo "\n";
    }
    
    private function generatePerformanceReport() {
        echo "📊 生成性能报告...\n";
        
        $totalTime = microtime(true) - $this->startTime;
        
        // 计算综合性能评分
        $scores = [];
        
        // 文件系统性能评分
        if (isset($this->results['filesystem'])) {
            $fsScore = 100;
            foreach ($this->results['filesystem'] as $time) {
                if (is_numeric($time)) {
                    $fsScore -= min($time * 1000, 50); // 每ms减少1分，最多减50分
                }
            }
            $scores['filesystem'] = max(0, $fsScore);
        }
        
        // 数据库性能评分
        if (isset($this->results['database'])) {
            $dbScore = 100;
            foreach ($this->results['database'] as $result) {
                if (is_numeric($result)) {
                    $dbScore -= min($result * 2000, 40); // 数据库操作更严格
                } elseif ($result === 'error') {
                    $dbScore -= 30;
                }
            }
            $scores['database'] = max(0, $dbScore);
        }
        
        // 配置优化评分
        if (isset($this->results['config'])) {
            $configScore = 0;
            $totalConfigs = count($this->results['config']);
            foreach ($this->results['config'] as $config) {
                if ($config['valid']) {
                    $configScore += 100 / $totalConfigs;
                }
            }
            $scores['config'] = $configScore;
        }
        
        // 安全功能评分
        if (isset($this->results['security'])) {
            $secScore = 100;
            foreach ($this->results['security'] as $result) {
                if ($result === 'error') {
                    $secScore -= 25;
                } elseif (is_numeric($result) && $result > 0.05) {
                    $secScore -= 10;
                }
            }
            $scores['security'] = max(0, $secScore);
        }
        
        // 综合评分
        $overallScore = !empty($scores) ? array_sum($scores) / count($scores) : 0;
        
        $this->results['summary'] = [
            'total_time' => $totalTime,
            'scores' => $scores,
            'overall_score' => $overallScore,
            'status' => $overallScore >= 80 ? 'excellent' : ($overallScore >= 60 ? 'good' : 'needs_improvement')
        ];
        
        echo "   📈 综合性能评分: " . round($overallScore, 1) . "/100\n";
        echo "   ⏱️ 总测试时间: " . number_format($totalTime, 2) . "s\n";
        echo "   🎯 系统状态: " . $this->getStatusEmoji($overallScore) . " " . $this->getStatusText($overallScore) . "\n";
        
        echo "\n";
    }
    
    private function getStatusEmoji($score) {
        if ($score >= 80) return "🟢";
        if ($score >= 60) return "🟡";
        return "🔴";
    }
    
    private function getStatusText($score) {
        if ($score >= 80) return "优秀";
        if ($score >= 60) return "良好";
        return "需要改进";
    }
    
    private function generateFinalReport() {
        $timestamp = date('Y_m_d_H_i_s');
        $reportFile = $this->basePath . "/PERFORMANCE_TEST_REPORT_$timestamp.json";
        
        $report = [
            'test_info' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'project' => 'AlingAi Pro 5.0',
                'tester' => 'SystemPerformanceTester'
            ],
            'results' => $this->results,
            'recommendations' => $this->generateRecommendations()
        ];
        
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    🎉 性能测试完成                          ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        $score = isset($this->results['summary']['overall_score']) ? round($this->results['summary']['overall_score'], 1) : 0;
        echo "║  综合评分: $score/100                                     ║\n";
        echo "║  报告文件: " . basename($reportFile) . str_repeat(' ', 15) . "║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
        
        $this->printRecommendations();
    }
    
    private function generateRecommendations() {
        $recommendations = [];
        
        if (isset($this->results['summary']['overall_score'])) {
            $score = $this->results['summary']['overall_score'];
            
            if ($score < 80) {
                $recommendations[] = "考虑启用 OPcache 以提升 PHP 性能";
                $recommendations[] = "增加 PHP 内存限制到 256MB 或更高";
                $recommendations[] = "考虑使用 Redis 缓存替代文件缓存";
            }
            
            if (isset($this->results['database']) && 
                array_sum(array_filter($this->results['database'], 'is_numeric')) > 0.5) {
                $recommendations[] = "考虑优化数据库操作或迁移到 MySQL/PostgreSQL";
            }
            
            if (isset($this->results['filesystem']['file_write']) && 
                $this->results['filesystem']['file_write'] > 0.1) {
                $recommendations[] = "考虑使用 SSD 存储以提升文件I/O性能";
            }
        }
        
        return $recommendations;
    }
    
    private function printRecommendations() {
        $recommendations = $this->generateRecommendations();
        
        if (!empty($recommendations)) {
            echo "💡 性能优化建议:\n";
            foreach ($recommendations as $i => $rec) {
                echo "   " . ($i + 1) . ". $rec\n";
            }
            echo "\n";
        }
        
        echo "🚀 下一步操作:\n";
        echo "   1. 查看详细性能报告文件\n";
        echo "   2. 根据建议进行系统优化\n";
        echo "   3. 定期运行性能测试监控系统状态\n";
        echo "   4. 在生产环境部署前进行压力测试\n\n";
    }
}

// 执行性能测试
echo "正在启动 AlingAi Pro 5.0 系统性能测试...\n\n";
$tester = new SystemPerformanceTester();
$tester->runComprehensiveTests();

?>
