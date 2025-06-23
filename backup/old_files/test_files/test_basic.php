<?php
/**
 * AlingAi Pro Basic System Test
 * 简化版本测试核心功能
 * 
 * @package AlingAi\Pro
 * @version 3.0.0
 */

declare(strict_types=1);

// 定义应用常量
define('APP_ROOT', __DIR__);
define('APP_VERSION', '3.0.0');

require_once __DIR__ . '/vendor/autoload.php';

// 加载环境变量
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

use AlingAi\Services\DatabaseService;

class BasicSystemTest
{
    private array $testResults = [];
    
    public function __construct()
    {
        echo "🧪 AlingAi Pro Basic System Test\n";
        echo "=================================\n";
    }
    
    public function runAllTests(): void
    {
        $tests = [
            'testEnvironmentSetup',
            'testDatabaseConnection',
            'testRequiredDirectories',
            'testCoreClasses',
            'testDatabaseTables',
            'testWebServerReady'
        ];
        
        foreach ($tests as $test) {
            try {
                echo "🔍 {$test}... ";
                $this->$test();
                echo "✅ PASSED\n";
                $this->testResults[$test] = 'PASSED';
            } catch (Exception $e) {
                echo "❌ FAILED: " . $e->getMessage() . "\n";
                $this->testResults[$test] = 'FAILED: ' . $e->getMessage();
            }
        }
        
        $this->printSummary();
    }
    
    private function testEnvironmentSetup(): void
    {
        $required = ['APP_ROOT', 'APP_VERSION'];
        foreach ($required as $constant) {
            if (!defined($constant)) {
                throw new Exception("Constant {$constant} not defined");
            }
        }        // 检查环境变量
        $envVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($envVars as $var) {
            $value = $_ENV[$var] ?? getenv($var);
            if (!$value) {
                throw new Exception("Environment variable {$var} not set");
            }
        }
    }
    
    private function testDatabaseConnection(): void
    {
        $db = new DatabaseService();
        $pdo = $db->getPdo();
        
        if (!$pdo instanceof PDO) {
            throw new Exception("Database connection failed");
        }
        
        $stmt = $pdo->query("SELECT 1");
        if (!$stmt->fetch()) {
            throw new Exception("Database query test failed");
        }
    }
    
    private function testRequiredDirectories(): void
    {
        $directories = [
            'src',
            'public',
            'database/migrations',
            'vendor'
        ];
        
        foreach ($directories as $dir) {
            $path = APP_ROOT . '/' . $dir;
            if (!is_dir($path)) {
                throw new Exception("Required directory not found: {$dir}");
            }
        }
    }
    
    private function testCoreClasses(): void
    {
        $classes = [
            'AlingAi\\Services\\DatabaseService',
            'AlingAi\\Services\\AuthService',
            'AlingAi\\Services\\CacheService'
        ];
        
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                throw new Exception("Core class not found: {$class}");
            }
        }
    }
    
    private function testDatabaseTables(): void
    {
        $db = new DatabaseService();
        $pdo = $db->getPdo();
        
        $stmt = $pdo->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $required = ['users', 'system_settings', 'agents', 'chat_sessions'];
        $missing = array_diff($required, $tables);
        
        if (!empty($missing)) {
            throw new Exception("Missing tables: " . implode(', ', $missing));
        }
        
        // 检查表数据
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM agents");
        $agentCount = $stmt->fetchColumn();
        
        echo "[{$userCount} users, {$agentCount} agents] ";
    }
    
    private function testWebServerReady(): void
    {
        $indexFile = APP_ROOT . '/public/index.php';
        if (!file_exists($indexFile)) {
            throw new Exception("Web server entry point not found: public/index.php");
        }
        
        $htaccessFile = APP_ROOT . '/public/.htaccess';
        if (!file_exists($htaccessFile)) {
            echo "[no .htaccess] ";
        }
    }
    
    private function printSummary(): void
    {
        echo "\n📊 Test Summary:\n";
        echo "================\n";
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->testResults as $test => $result) {
            $status = str_starts_with($result, 'PASSED') ? '✅' : '❌';
            echo "{$status} {$test}: {$result}\n";
            
            if (str_starts_with($result, 'PASSED')) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        $total = $passed + $failed;
        echo "\n📈 Results: {$passed}/{$total} tests passed\n";
        
        if ($failed === 0) {
            echo "\n🎉 All basic tests passed!\n";
            echo "✨ AlingAi Pro Enhanced System is ready!\n\n";
            
            echo "🚀 Quick Start Commands:\n";
            echo "========================\n";
            echo "1. Start development server:\n";
            echo "   php -S localhost:8000 -t public/\n\n";
            echo "2. Access the system:\n";
            echo "   http://localhost:8000\n\n";
            echo "3. Start background worker:\n";
            echo "   php worker.php\n\n";
            echo "4. Deploy to production:\n";
            echo "   chmod +x deploy.sh && ./deploy.sh\n\n";
            
            echo "🔧 System Features:\n";
            echo "==================\n";
            echo "✅ PHP 8.1+ Enhanced Architecture\n";
            echo "✅ Complete Frontend PHP System\n";
            echo "✅ AI Agent Coordination Platform\n";
            echo "✅ 3D Threat Visualization\n";
            echo "✅ Database Configuration Migration\n";
            echo "✅ Production Linux Deployment\n";
            echo "✅ Enhanced Security & Performance\n";
            
        } else {
            echo "\n⚠️  {$failed} test(s) failed. Please check the issues above.\n";
        }
    }
}

// Run tests
try {
    $tester = new BasicSystemTest();
    $tester->runAllTests();
} catch (Exception $e) {
    echo "❌ System test failed: " . $e->getMessage() . "\n";
    exit(1);
}
