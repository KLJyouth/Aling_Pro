<?php
/**
 * AlingAi Pro 5.0 最终系统验证
 * 全面测试修复后的系统状态
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== AlingAi Pro 5.0 最终系统验证 ===\n";
echo "验证时间: " . date('Y-m-d H:i:s') . "\n\n";

class FinalSystemValidator
{
    private $results = [];
    private $logger;
    
    public function __construct()
    {
        $this->logger = new \Monolog\Logger('SystemValidator');
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::ERROR));
    }
    
    public function runCompleteValidation()
    {
        echo "🔍 开始全面系统验证...\n\n";
        
        $this->validateEnvironment();
        $this->validateAutoloader();
        $this->validateDatabaseSystem();
        $this->validateCoreApplication();
        $this->validateServices();
        $this->validateFileSystem();
        $this->generateFinalReport();
    }
    
    private function validateEnvironment()
    {
        echo "1. 🌍 环境配置验证\n";
        echo str_repeat("-", 30) . "\n";
        
        $this->test('PHP版本检查', function() {
            return version_compare(PHP_VERSION, '8.0.0', '>=');
        });
        
        $this->test('必需扩展检查', function() {
            $required = ['json', 'mbstring', 'curl'];
            foreach ($required as $ext) {
                if (!extension_loaded($ext)) {
                    return false;
                }
            }
            return true;
        });
        
        $this->test('环境变量加载', function() {
            if (file_exists(__DIR__ . '/.env')) {
                $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
                        continue;
                    }
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value, '"');
                }
                return isset($_ENV['APP_NAME']);
            }
            return false;
        });
        
        echo "\n";
    }
    
    private function validateAutoloader()
    {
        echo "2. 🔧 自动加载器验证\n";
        echo str_repeat("-", 30) . "\n";
        
        $this->test('Composer自动加载器', function() {
            return class_exists('Slim\\App');
        });
        
        $this->test('AlingAi核心类', function() {
            return class_exists('AlingAi\\Core\\Application');
        });
        
        $this->test('服务类加载', function() {
            $services = [
                'AlingAi\\Services\\DatabaseServiceInterface',
                'AlingAi\\Services\\FileSystemDatabaseService',
                'AlingAi\\Services\\UnifiedDatabaseServiceV3'
            ];
            
            foreach ($services as $service) {
                if (!class_exists($service) && !interface_exists($service)) {
                    return false;
                }
            }
            return true;
        });
        
        echo "\n";
    }
    
    private function validateDatabaseSystem()
    {
        echo "3. 🗄️ 数据库系统验证\n";
        echo str_repeat("-", 30) . "\n";
        
        $this->test('数据库服务创建', function() {
            try {
                $db = new \AlingAi\Services\UnifiedDatabaseServiceV3($this->logger);
                return $db->isConnected();
            } catch (Exception $e) {
                echo "   错误: " . $e->getMessage() . "\n";
                return false;
            }
        });
        
        $this->test('数据库查询测试', function() {
            try {
                $db = new \AlingAi\Services\UnifiedDatabaseServiceV3($this->logger);
                $result = $db->query("SELECT COUNT(*) as count FROM system_settings");
                return !empty($result) && isset($result[0]['count']);
            } catch (Exception $e) {
                return false;
            }
        });
        
        $this->test('文件系统数据库', function() {
            try {
                if (class_exists('AlingAi\\Services\\FileSystemDatabaseService')) {
                    $fileDb = new \AlingAi\Services\FileSystemDatabaseService($this->logger);
                    return $fileDb->isConnected();
                }
                return false;
            } catch (Exception $e) {
                return false;
            }
        });
        
        echo "\n";
    }
    
    private function validateCoreApplication()
    {
        echo "4. 🏗️ 核心应用程序验证\n";
        echo str_repeat("-", 30) . "\n";
        
        $this->test('应用程序创建', function() {
            try {
                $app = new \AlingAi\Core\Application();
                return $app instanceof \AlingAi\Core\Application;
            } catch (Exception $e) {
                echo "   错误: " . $e->getMessage() . "\n";
                return false;
            }
        });
        
        $this->test('静态工厂方法', function() {
            try {
                $app = \AlingAi\Core\Application::create();
                return $app instanceof \AlingAi\Core\Application;
            } catch (Exception $e) {
                return false;
            }
        });
        
        $this->test('PSR-7接口实现', function() {
            try {
                $app = new \AlingAi\Core\Application();
                return $app instanceof \Psr\Http\Server\RequestHandlerInterface;
            } catch (Exception $e) {
                return false;
            }
        });
        
        echo "\n";
    }
    
    private function validateServices()
    {
        echo "5. ⚙️ 服务层验证\n";
        echo str_repeat("-", 30) . "\n";
        
        $this->test('缓存服务', function() {
            return class_exists('AlingAi\\Services\\CacheService');
        });
        
        $this->test('认证服务', function() {
            return class_exists('AlingAi\\Services\\AuthService');
        });
        
        $this->test('配置服务', function() {
            return class_exists('AlingAi\\Services\\ConfigService');
        });
        
        echo "\n";
    }
    
    private function validateFileSystem()
    {
        echo "6. 📁 文件系统验证\n";
        echo str_repeat("-", 30) . "\n";
        
        $this->test('存储目录权限', function() {
            $dirs = ['storage', 'storage/logs', 'storage/cache', 'storage/database'];
            foreach ($dirs as $dir) {
                $fullPath = __DIR__ . '/' . $dir;
                if (!is_dir($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }
                if (!is_writable($fullPath)) {
                    return false;
                }
            }
            return true;
        });
        
        $this->test('日志文件写入', function() {
            $logFile = __DIR__ . '/storage/logs/test.log';
            return file_put_contents($logFile, 'test') !== false;
        });
        
        $this->test('配置文件存在', function() {
            $configFiles = ['.env', 'composer.json', 'public/index.php'];
            foreach ($configFiles as $file) {
                if (!file_exists(__DIR__ . '/' . $file)) {
                    return false;
                }
            }
            return true;
        });
        
        echo "\n";
    }
    
    private function test($name, $callback)
    {
        try {
            $result = $callback();
            $status = $result ? '✅' : '❌';
            echo "   {$status} {$name}\n";
            $this->results[$name] = $result;
            return $result;
        } catch (Exception $e) {
            echo "   ❌ {$name} (错误: {$e->getMessage()})\n";
            $this->results[$name] = false;
            return false;
        }
    }
    
    private function generateFinalReport()
    {
        echo "🎯 最终验证报告\n";
        echo str_repeat("=", 50) . "\n";
        
        $total = count($this->results);
        $passed = array_sum($this->results);
        $percentage = round(($passed / $total) * 100, 1);
        
        echo "总测试项目: {$total}\n";
        echo "通过测试: {$passed}\n";
        echo "失败测试: " . ($total - $passed) . "\n";
        echo "成功率: {$percentage}%\n\n";
        
        if ($percentage >= 90) {
            echo "🎉 系统状态: 优秀 - 所有核心功能正常运行\n";
        } elseif ($percentage >= 75) {
            echo "✅ 系统状态: 良好 - 主要功能可用，有少量非关键问题\n";
        } elseif ($percentage >= 60) {
            echo "⚠️ 系统状态: 可用 - 基本功能可用，需要进一步优化\n";
        } else {
            echo "❌ 系统状态: 需要修复 - 存在关键问题\n";
        }
        
        echo "\n详细结果:\n";
        foreach ($this->results as $test => $result) {
            $status = $result ? '✅' : '❌';
            echo "   {$status} {$test}\n";
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "验证完成时间: " . date('Y-m-d H:i:s') . "\n";
        
        // 创建验证报告文件
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_tests' => $total,
            'passed_tests' => $passed,
            'success_rate' => $percentage,
            'status' => $percentage >= 90 ? 'excellent' : ($percentage >= 75 ? 'good' : ($percentage >= 60 ? 'acceptable' : 'needs_fix')),
            'detailed_results' => $this->results
        ];
        
        file_put_contents(__DIR__ . '/storage/final_validation_report.json', json_encode($reportData, JSON_PRETTY_PRINT));
        echo "📄 详细报告已保存至: storage/final_validation_report.json\n";
    }
}

// 执行最终验证
try {
    $validator = new FinalSystemValidator();
    $validator->runCompleteValidation();
    
} catch (Exception $e) {
    echo "\n💥 验证过程出错: " . $e->getMessage() . "\n";
    echo "错误跟踪:\n" . $e->getTraceAsString() . "\n";
}
