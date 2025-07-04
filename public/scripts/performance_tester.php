<?php

/**
 * ๐ AlingAi Pro 5.0 ็ณป็ปๆง่ฝๆต่ฏๅไผๅ้ช่ฏๅทฅๅ?
 * ๅจ้ขๆต่ฏไผๅๅ็ณป็ป็ๆง่ฝ่กจ็ฐ
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
        $this->basePath = $basePath ?: dirname(__DIR__];
        $this->startTime = microtime(true];
        $this->initializeReport(];
    }
    
    private function initializeReport() {
        echo "โโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโ\n";
        echo "โ?           ๐ ็ณป็ปๆง่ฝๆต่ฏๅไผๅ้ช่ฏๅทฅๅ?                     โ\n";
        echo "โ โโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโฃ\n";
        echo "โ? ๆต่ฏๆถ้ด: " . date('Y-m-d H:i:s') . str_repeat(' ', 25) . "โ\n";
        echo "โ? ้กน็ฎ่ทฏๅพ: " . substr($this->basePath, 0, 40) . str_repeat(' ', 15) . "โ\n";
        echo "โโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโ\n\n";
    }
    
    public function runComprehensiveTests() {
        echo "๐ ๅผๅง็ณป็ปๆง่ฝๆต่ฏ...\n\n";
        
        $this->testFileSystemPerformance(];
        $this->testDatabasePerformance(];
        $this->testConfigurationOptimizations(];
        $this->testSecurityFeatures(];
        $this->testCachePerformance(];
        $this->testResourceOptimization(];
        $this->generatePerformanceReport(];
        
        $this->generateFinalReport(];
    }
    
    private function testFileSystemPerformance() {
        echo "๐ ๆต่ฏๆไปถ็ณป็ปๆง่ฝ...\n";
        
        $tests = [
            'file_read' => function() {
                $file = $this->basePath . '/README.md';
                $start = microtime(true];
                $content = file_get_contents($file];
                return microtime(true) - $start;
            },
            'file_write' => function() {
                $file = $this->basePath . '/tmp/test_write.txt';
                $dir = dirname($file];
                if (!is_dir($dir)) mkdir($dir, 0755, true];
                
                $start = microtime(true];
                file_put_contents($file, str_repeat('Test data ', 1000)];
                $result = microtime(true) - $start;
                unlink($file];
                return $result;
            },
            'directory_scan' => function() {
                $start = microtime(true];
                $files = glob($this->basePath . '/*'];
                return microtime(true) - $start;
            }
        ];
        
        foreach ($tests as $testName => $testFunc) {
            $time = $testFunc(];
            $this->results['filesystem'][$testName] = $time;
            $status = $time < 0.1 ? "โ? : ($time < 0.5 ? "โ ๏ธ" : "โ?];
            echo "   $status $testName: " . number_format($time * 1000, 2) . "ms\n";
        }
        
        echo "\n";
    }
    
    private function testDatabasePerformance() {
        echo "๐๏ธ?ๆต่ฏๆฐๆฎๅบๆง่ฝ...\n";
        
        $fileDbPath = $this->basePath . '/database/filedb';
        if (!is_dir($fileDbPath)) {
            echo "   โ?ๆไปถๆฐๆฎๅบ็ฎๅฝไธๅญๅจ\n\n";
            return;
        }
        
        // ๆต่ฏๆไปถๆฐๆฎๅบๆไฝ?
        $tests = [
            'read_users' => function() use ($fileDbPath) {
                $start = microtime(true];
                $data = json_decode(file_get_contents($fileDbPath . '/users.json'], true];
                return microtime(true) - $start;
            },
            'write_log' => function() use ($fileDbPath) {
                $logsFile = $fileDbPath . '/system_logs.json';
                $logs = json_decode(file_get_contents($logsFile], true) ?: [];
                
                $start = microtime(true];
                $logs[] = [
                    'id' => count($logs) + 1,
                    'level' => 'test',
                    'message' => 'Performance test log',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT)];
                return microtime(true) - $start;
            },
            'search_records' => function() use ($fileDbPath) {
                $start = microtime(true];
                $users = json_decode(file_get_contents($fileDbPath . '/users.json'], true];
                $admins = array_filter($users, function($user) {
                    return isset($user['role']) && $user['role'] === 'admin';
                }];
                return microtime(true) - $start;
            }
        ];
        
        foreach ($tests as $testName => $testFunc) {
            try {
                $time = $testFunc(];
                $this->results['database'][$testName] = $time;
                $status = $time < 0.05 ? "โ? : ($time < 0.2 ? "โ ๏ธ" : "โ?];
                echo "   $status $testName: " . number_format($time * 1000, 2) . "ms\n";
            } catch (Exception $e) {
                echo "   โ?$testName: ๅคฑ่ดฅ - " . $e->getMessage() . "\n";
                $this->results['database'][$testName] = 'error';
            }
        }
        
        echo "\n";
    }
    
    private function testConfigurationOptimizations() {
        echo "โ๏ธ ๆต่ฏ้็ฝฎไผๅๆๆ...\n";
        
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
                $start = microtime(true];
                $config = include $configFile;
                $time = microtime(true) - $start;
                
                $isValid = is_[$config) && !empty($config];
                $status = $isValid ? "โ? : "โ?;
                $this->results['config'][$type] = ['time' => $time, 'valid' => $isValid];
                
                echo "   $status $type ้็ฝฎ: " . number_format($time * 1000, 2) . "ms\n";
            } else {
                echo "   โ?$type ้็ฝฎ: ๆไปถไธๅญๅจ\n";
                $this->results['config'][$type] = ['time' => 0, 'valid' => false];
            }
        }
        
        echo "\n";
    }
    
    private function testSecurityFeatures() {
        echo "๐ก๏ธ?ๆต่ฏๅฎๅจๅ่ฝ...\n";
        
        $securityTests = [
            'csrf_token' => function() {
                $start = microtime(true];
                $token = bin2hex(random_bytes(32)];
                return microtime(true) - $start;
            },
            'password_hash' => function() {
                $start = microtime(true];
                $hash = password_hash('test_password', PASSWORD_DEFAULT];
                return microtime(true) - $start;
            },
            'session_id' => function() {
                $start = microtime(true];
                $sessionId = session_create_id(];
                return microtime(true) - $start;
            },
            'encryption' => function() {
                $key = random_bytes(32];
                $data = 'Sensitive data for testing';
                
                $start = microtime(true];
                $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, random_bytes(16)];
                return microtime(true) - $start;
            }
        ];
        
        foreach ($securityTests as $testName => $testFunc) {
            try {
                $time = $testFunc(];
                $this->results['security'][$testName] = $time;
                $status = $time < 0.01 ? "โ? : ($time < 0.05 ? "โ ๏ธ" : "โ?];
                echo "   $status $testName: " . number_format($time * 1000, 2) . "ms\n";
            } catch (Exception $e) {
                echo "   โ?$testName: ๅคฑ่ดฅ - " . $e->getMessage() . "\n";
                $this->results['security'][$testName] = 'error';
            }
        }
        
        echo "\n";
    }
    
    private function testCachePerformance() {
        echo "๐พ ๆต่ฏ็ผๅญๆง่ฝ...\n";
        
        $cacheDir = $this->basePath . '/storage/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true];
        }
        
        $cacheTests = [
            'file_cache_write' => function() use ($cacheDir) {
                $cacheFile = $cacheDir . '/test_cache.json';
                $data = ['test' => 'data', 'timestamp' => time()];
                
                $start = microtime(true];
                file_put_contents($cacheFile, json_encode($data)];
                return microtime(true) - $start;
            },
            'file_cache_read' => function() use ($cacheDir) {
                $cacheFile = $cacheDir . '/test_cache.json';
                
                $start = microtime(true];
                $data = json_decode(file_get_contents($cacheFile], true];
                return microtime(true) - $start;
            },
            'memory_cache' => function() {
                static $memoryCache = [];
                $key = 'test_key_' . mt_rand(];
                $value = ['data' => str_repeat('x', 1000)];
                
                $start = microtime(true];
                $memoryCache[$key] = $value;
                $retrieved = $memoryCache[$key];
                return microtime(true) - $start;
            }
        ];
        
        foreach ($cacheTests as $testName => $testFunc) {
            try {
                $time = $testFunc(];
                $this->results['cache'][$testName] = $time;
                $status = $time < 0.01 ? "โ? : ($time < 0.05 ? "โ ๏ธ" : "โ?];
                echo "   $status $testName: " . number_format($time * 1000, 2) . "ms\n";
            } catch (Exception $e) {
                echo "   โ?$testName: ๅคฑ่ดฅ - " . $e->getMessage() . "\n";
                $this->results['cache'][$testName] = 'error';
            }
        }
        
        // ๆธ็ๆต่ฏ็ผๅญ
        $testFile = $cacheDir . '/test_cache.json';
        if (file_exists($testFile)) {
            unlink($testFile];
        }
        
        echo "\n";
    }
    
    private function testResourceOptimization() {
        echo "๐ฆ ๆต่ฏ่ตๆบไผๅ...\n";
        
        $resourceTests = [
            'memory_usage' => function() {
                return memory_get_usage(true];
            },
            'peak_memory' => function() {
                return memory_get_peak_usage(true];
            },
            'cpu_time' => function() {
                $start = getrusage(];
                // ๆจกๆCPUๅฏ้ๆไฝ
                for ($i = 0; $i < 10000; $i++) {
                    hash('sha256', 'test_data_' . $i];
                }
                $end = getrusage(];
                return ($end['ru_utime.tv_sec'] - $start['ru_utime.tv_sec']) + 
                       (($end['ru_utime.tv_usec'] - $start['ru_utime.tv_usec']) / 1000000];
            }
        ];
        
        foreach ($resourceTests as $testName => $testFunc) {
            try {
                $value = $testFunc(];
                $this->results['resources'][$testName] = $value;
                
                if ($testName === 'memory_usage' || $testName === 'peak_memory') {
                    $mb = round($value / 1024 / 1024, 2];
                    $status = $mb < 32 ? "โ? : ($mb < 64 ? "โ ๏ธ" : "โ?];
                    echo "   $status $testName: {$mb}MB\n";
                } else {
                    $status = $value < 0.1 ? "โ? : ($value < 0.5 ? "โ ๏ธ" : "โ?];
                    echo "   $status $testName: " . number_format($value, 4) . "s\n";
                }
            } catch (Exception $e) {
                echo "   โ?$testName: ๅคฑ่ดฅ - " . $e->getMessage() . "\n";
                $this->results['resources'][$testName] = 'error';
            }
        }
        
        echo "\n";
    }
    
    private function generatePerformanceReport() {
        echo "๐ ็ๆๆง่ฝๆฅๅ...\n";
        
        $totalTime = microtime(true) - $this->startTime;
        
        // ่ฎก็ฎ็ปผๅๆง่ฝ่ฏๅ
        $scores = [];
        
        // ๆไปถ็ณป็ปๆง่ฝ่ฏๅ
        if (isset($this->results['filesystem'])) {
            $fsScore = 100;
            foreach ($this->results['filesystem'] as $time) {
                if (is_numeric($time)) {
                    $fsScore -= min($time * 1000, 50]; // ๆฏmsๅๅฐ1ๅ๏ผๆๅคๅ50ๅ?
                }
            }
            $scores['filesystem'] = max(0, $fsScore];
        }
        
        // ๆฐๆฎๅบๆง่ฝ่ฏๅ
        if (isset($this->results['database'])) {
            $dbScore = 100;
            foreach ($this->results['database'] as $result) {
                if (is_numeric($result)) {
                    $dbScore -= min($result * 2000, 40]; // ๆฐๆฎๅบๆไฝๆดไธฅๆ ผ
                } elseif ($result === 'error') {
                    $dbScore -= 30;
                }
            }
            $scores['database'] = max(0, $dbScore];
        }
        
        // ้็ฝฎไผๅ่ฏๅ
        if (isset($this->results['config'])) {
            $configScore = 0;
            $totalConfigs = count($this->results['config']];
            foreach ($this->results['config'] as $config) {
                if ($config['valid']) {
                    $configScore += 100 / $totalConfigs;
                }
            }
            $scores['config'] = $configScore;
        }
        
        // ๅฎๅจๅ่ฝ่ฏๅ
        if (isset($this->results['security'])) {
            $secScore = 100;
            foreach ($this->results['security'] as $result) {
                if ($result === 'error') {
                    $secScore -= 25;
                } elseif (is_numeric($result) && $result > 0.05) {
                    $secScore -= 10;
                }
            }
            $scores['security'] = max(0, $secScore];
        }
        
        // ็ปผๅ่ฏๅ
        $overallScore = !empty($scores) ? array_sum($scores) / count($scores) : 0;
        
        $this->results['summary'] = [
            'total_time' => $totalTime,
            'scores' => $scores,
            'overall_score' => $overallScore,
            'status' => $overallScore >= 80 ? 'excellent' : ($overallScore >= 60 ? 'good' : 'needs_improvement')
        ];
        
        echo "   ๐ ็ปผๅๆง่ฝ่ฏๅ: " . round($overallScore, 1) . "/100\n";
        echo "   โฑ๏ธ ๆปๆต่ฏๆถ้? " . number_format($totalTime, 2) . "s\n";
        echo "   ๐ฏ ็ณป็ป็ถๆ? " . $this->getStatusEmoji($overallScore) . " " . $this->getStatusText($overallScore) . "\n";
        
        echo "\n";
    }
    
    private function getStatusEmoji($score) {
        if ($score >= 80) return "๐ข";
        if ($score >= 60) return "๐ก";
        return "๐ด";
    }
    
    private function getStatusText($score) {
        if ($score >= 80) return "ไผ็ง";
        if ($score >= 60) return "่ฏๅฅฝ";
        return "้่ฆๆน่ฟ?;
    }
    
    private function generateFinalReport() {
        $timestamp = date('Y_m_d_H_i_s'];
        $reportFile = $this->basePath . "/PERFORMANCE_TEST_REPORT_$timestamp.json";
        
        $report = [
            'test_info' => [
                'timestamp' => date('Y-m-d H:i:s'],
                'version' => '1.0',
                'project' => 'AlingAi Pro 5.0',
                'tester' => 'SystemPerformanceTester'
            ], 
            'results' => $this->results,
            'recommendations' => $this->generateRecommendations()
        ];
        
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT)];
        
        echo "โโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโ\n";
        echo "โ?                   ๐ ๆง่ฝๆต่ฏๅฎๆ                          โ\n";
        echo "โ โโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโฃ\n";
        $score = isset($this->results['summary']['overall_score']) ? round($this->results['summary']['overall_score'],  1) : 0;
        echo "โ? ็ปผๅ่ฏๅ: $score/100                                     โ\n";
        echo "โ? ๆฅๅๆไปถ: " . basename($reportFile) . str_repeat(' ', 15) . "โ\n";
        echo "โโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโโ\n\n";
        
        $this->printRecommendations(];
    }
    
    private function generateRecommendations() {
        $recommendations = [];
        
        if (isset($this->results['summary']['overall_score'])) {
            $score = $this->results['summary']['overall_score'];
            
            if ($score < 80) {
                $recommendations[] = "่่ๅฏ็จ OPcache ไปฅๆๅ?PHP ๆง่ฝ";
                $recommendations[] = "ๅขๅ  PHP ๅๅญ้ๅถๅ?256MB ๆๆด้ซ?;
                $recommendations[] = "่่ไฝฟ็จ Redis ็ผๅญๆฟไปฃๆไปถ็ผๅญ";
            }
            
            if (isset($this->results['database']) && 
                array_sum(array_filter($this->results['database'],  'is_numeric')) > 0.5) {
                $recommendations[] = "่่ไผๅๆฐๆฎๅบๆไฝๆ่ฟ็งปๅ?MySQL/PostgreSQL";
            }
            
            if (isset($this->results['filesystem']['file_write']) && 
                $this->results['filesystem']['file_write'] > 0.1) {
                $recommendations[] = "่่ไฝฟ็จ SSD ๅญๅจไปฅๆๅๆไปถI/Oๆง่ฝ";
            }
        }
        
        return $recommendations;
    }
    
    private function printRecommendations() {
        $recommendations = $this->generateRecommendations(];
        
        if (!empty($recommendations)) {
            echo "๐ก ๆง่ฝไผๅๅปบ่ฎฎ:\n";
            foreach ($recommendations as $i => $rec) {
                echo "   " . ($i + 1) . ". $rec\n";
            }
            echo "\n";
        }
        
        echo "๐ ไธไธๆญฅๆไฝ?\n";
        echo "   1. ๆฅ็่ฏฆ็ปๆง่ฝๆฅๅๆไปถ\n";
        echo "   2. ๆ นๆฎๅปบ่ฎฎ่ฟ่ก็ณป็ปไผๅ\n";
        echo "   3. ๅฎๆ่ฟ่กๆง่ฝๆต่ฏ็ๆง็ณป็ป็ถๆ\n";
        echo "   4. ๅจ็ไบง็ฏๅข้จ็ฝฒๅ่ฟ่กๅๅๆต่ฏ\n\n";
    }
}

// ๆง่กๆง่ฝๆต่ฏ
echo "ๆญฃๅจๅฏๅจ AlingAi Pro 5.0 ็ณป็ปๆง่ฝๆต่ฏ...\n\n";
$tester = new SystemPerformanceTester(];
$tester->runComprehensiveTests(];

?>

