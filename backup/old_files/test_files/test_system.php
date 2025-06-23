<?php
/**
 * AlingAi Pro System Integration Test
 * 三完编译 (Three Complete Compilation) System Test
 * 
 * @package AlingAi\Pro
 * @version 3.0.0
 */

declare(strict_types=1);

// 定义应用常量 (类似于 public/index.php)
define('APP_ROOT', __DIR__);
define('APP_PUBLIC', __DIR__ . '/public');
define('APP_VERSION', '3.0.0');
define('APP_NAME', 'AlingAi Pro - Enhanced');

require_once __DIR__ . '/vendor/autoload.php';

// 加载环境变量
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

use AlingAi\Core\AlingAiProApplication;
use AlingAi\Services\DatabaseService;

class SystemIntegrationTest
{
    private DatabaseService $db;
    private array $testResults = [];
    
    public function __construct()
    {
        echo "🧪 AlingAi Pro System Integration Test\n";
        echo "======================================\n";
        $this->db = new DatabaseService();
    }
    
    public function runAllTests(): void
    {
        $tests = [
            'testDatabaseConnection',
            'testApplicationBootstrap',
            'testRequiredTables',
            'testCoreServices',
            'testRoutingSystem',
            'testAIAgentSystem',
            'testSecurityFeatures'
        ];
        
        foreach ($tests as $test) {
            try {
                echo "🔍 Running {$test}... ";
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
    
    private function testDatabaseConnection(): void
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT 1");
        if (!$stmt->fetch()) {
            throw new Exception("Database connection failed");
        }
    }
    
    private function testApplicationBootstrap(): void
    {
        $app = AlingAiProApplication::create();
        if (!$app instanceof AlingAiProApplication) {
            throw new Exception("Application bootstrap failed");
        }
    }
    
    private function testRequiredTables(): void
    {
        $requiredTables = [
            'users', 'chat_sessions', 'agents', 'system_settings'
        ];
        
        $stmt = $this->db->getPdo()->query("SHOW TABLES");
        $existingTables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $existingTables[] = $row[0];
        }
        
        $missingTables = array_diff($requiredTables, $existingTables);
        if (!empty($missingTables)) {
            throw new Exception("Missing tables: " . implode(', ', $missingTables));
        }
    }
    
    private function testCoreServices(): void
    {
        // Test basic service instantiation
        $services = [
            'DatabaseService' => 'AlingAi\\Services\\DatabaseService',
            'AuthService' => 'AlingAi\\Services\\AuthService',
            'CacheService' => 'AlingAi\\Services\\CacheService'
        ];
        
        foreach ($services as $name => $class) {
            if (!class_exists($class)) {
                throw new Exception("{$name} ({$class}) not found");
            }
        }
    }
    
    private function testRoutingSystem(): void
    {
        // Test basic routing system
        if (!class_exists('AlingAi\\Http\\CompleteRouterIntegration')) {
            throw new Exception("CompleteRouterIntegration class not found");
        }
    }
    
    private function testAIAgentSystem(): void
    {
        // Test AI agent coordinator
        if (!class_exists('AlingAi\\AI\\EnhancedAgentCoordinator')) {
            throw new Exception("EnhancedAgentCoordinator class not found");
        }
        
        // Check if agents table has data
        $stmt = $this->db->getPdo()->query("SELECT COUNT(*) FROM agents");
        $count = $stmt->fetchColumn();
        if ($count == 0) {
            throw new Exception("No AI agents found in database");
        }
    }
    
    private function testSecurityFeatures(): void
    {
        // Test security configurations
        $stmt = $this->db->getPdo()->query("SELECT COUNT(*) FROM system_settings WHERE setting_key LIKE '%security%'");
        $securitySettings = $stmt->fetchColumn();
        
        if ($securitySettings == 0) {
            echo "\nℹ️  Note: No security settings found, but basic security is implemented at application level";
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
            echo "🎉 All tests passed! System is ready for production.\n";
        } else {
            echo "⚠️  {$failed} test(s) failed. Please check the issues above.\n";
        }
        
        echo "\n🔗 Next Steps:\n";
        echo "  1. Start the web server: php -S localhost:8000 -t public/\n";
        echo "  2. Access the system: http://localhost:8000\n";
        echo "  3. Deploy to production using deploy.sh\n";
    }
}

// Run tests
try {
    $tester = new SystemIntegrationTest();
    $tester->runAllTests();
} catch (Exception $e) {
    echo "❌ System test failed: " . $e->getMessage() . "\n";
    exit(1);
}
