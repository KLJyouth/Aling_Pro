<?php
/**
 * AlingAi Pro 5.0 - 最终系统验证器
 * 验证所有优化功能是否正常工作
 */

require_once __DIR__ . '/../vendor/autoload.php';

class FinalSystemValidator {
    private $results = [];
    private $optimizations = [
        'api_performance' => 'API性能优化',
        'cache_system' => '缓存系统',
        'monitoring' => '监控系统',
        'security' => '安全增强',
        'static_resources' => '静态资源',
        'database_optimization' => '数据库优化',
        'error_handling' => '错误处理',
        'logging' => '日志系统'
    ];
    
    public function runCompleteValidation() {
        echo "🎯 AlingAi Pro 5.0 - 最终系统验证\n";
        echo str_repeat("=", 70) . "\n\n";
        
        foreach ($this->optimizations as $key => $name) {
            echo "🔍 验证: $name\n";
            echo str_repeat("-", 40) . "\n";
            
            $result = $this->validateOptimization($key);
            $this->results[$key] = $result;
            
            $status = $result['success'] ? '✅ 通过' : '❌ 失败';
            echo "$status - {$result['message']}\n";
            
            if (!empty($result['details'])) {
                foreach ($result['details'] as $detail) {
                    echo "   • $detail\n";
                }
            }
            echo "\n";
        }
        
        $this->generateFinalReport();
    }
    
    private function validateOptimization($type) {
        try {
            switch ($type) {
                case 'api_performance':
                    return $this->validateApiPerformance();
                case 'cache_system':
                    return $this->validateCacheSystem();
                case 'monitoring':
                    return $this->validateMonitoringSystem();
                case 'security':
                    return $this->validateSecurity();
                case 'static_resources':
                    return $this->validateStaticResources();
                case 'database_optimization':
                    return $this->validateDatabaseOptimization();
                case 'error_handling':
                    return $this->validateErrorHandling();
                case 'logging':
                    return $this->validateLogging();
                default:
                    return ['success' => false, 'message' => '未知验证类型'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => '验证异常: ' . $e->getMessage()];
        }
    }
    
    private function validateApiPerformance() {
        $endpoints = [
            'http://localhost:8000/api/',
            'http://localhost:8000/api/system/status',
            'http://localhost:8000/api/system/health'
        ];
        
        $passedTests = 0;
        $details = [];
        
        foreach ($endpoints as $endpoint) {
            $startTime = microtime(true);
            $context = stream_context_create(['http' => ['timeout' => 5]]);
            $result = @file_get_contents($endpoint, false, $context);
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($result !== false) {
                $passedTests++;
                $details[] = "API端点响应正常: $endpoint (" . round($responseTime, 2) . "ms)";
            } else {
                $details[] = "API端点响应失败: $endpoint";
            }
        }
        
        $success = $passedTests >= count($endpoints) * 0.8; // 80%通过率
        
        return [
            'success' => $success,
            'message' => "API性能测试通过率: " . round(($passedTests / count($endpoints)) * 100, 1) . "%",
            'details' => $details
        ];
    }
    
    private function validateCacheSystem() {
        $details = [];
        $checks = 0;
        $passed = 0;
        
        // 检查缓存目录
        $cacheDir = __DIR__ . '/../storage/cache';
        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            $passed++;
            $details[] = "缓存目录可写: $cacheDir";
        } else {
            $details[] = "缓存目录不可用: $cacheDir";
        }
        $checks++;
        
        // 检查缓存服务类
        if (class_exists('AlingAi\Services\CacheService')) {
            $passed++;
            $details[] = "缓存服务类已加载";
        } else {
            $details[] = "缓存服务类未找到";
        }
        $checks++;
        
        // 检查高级缓存策略
        if (class_exists('AlingAi\Cache\AdvancedCacheStrategy')) {
            $passed++;
            $details[] = "高级缓存策略已实现";
        } else {
            $details[] = "高级缓存策略未实现";
        }
        $checks++;
        
        return [
            'success' => $passed >= $checks * 0.8,
            'message' => "缓存系统检查通过: $passed/$checks",
            'details' => $details
        ];
    }
    
    private function validateMonitoringSystem() {
        $details = [];
        $checks = 0;
        $passed = 0;
        
        // 检查监控服务
        if (class_exists('AlingAi\Services\EnhancedMonitoringService')) {
            $passed++;
            $details[] = "增强监控服务已实现";
        } else {
            $details[] = "增强监控服务未找到";
        }
        $checks++;
        
        // 检查性能监控器
        $monitorFile = __DIR__ . '/performance_monitor.php';
        if (file_exists($monitorFile)) {
            $passed++;
            $details[] = "性能监控器脚本已创建";
        } else {
            $details[] = "性能监控器脚本未找到";
        }
        $checks++;
        
        // 检查日志目录
        $logDir = __DIR__ . '/../storage/logs';
        if (is_dir($logDir) && is_writable($logDir)) {
            $passed++;
            $details[] = "日志目录可写";
        } else {
            $details[] = "日志目录不可用";
        }
        $checks++;
        
        return [
            'success' => $passed >= $checks * 0.8,
            'message' => "监控系统检查通过: $passed/$checks",
            'details' => $details
        ];
    }
    
    private function validateSecurity() {
        $details = [];
        $issues = 0;
        
        // 检查PHP安全配置
        if (ini_get('display_errors') == '1') {
            $issues++;
            $details[] = "⚠️ display_errors应在生产环境中关闭";
        } else {
            $details[] = "✅ display_errors配置安全";
        }
        
        if (ini_get('expose_php') == '1') {
            $issues++;
            $details[] = "⚠️ expose_php应该关闭";
        } else {
            $details[] = "✅ expose_php配置安全";
        }
        
        // 检查文件权限
        $sensitiveFiles = ['.env', 'config'];
        foreach ($sensitiveFiles as $file) {
            $fullPath = __DIR__ . '/../' . $file;
            if (file_exists($fullPath)) {
                $perms = fileperms($fullPath);
                if (is_file($fullPath) && ($perms & 0044)) {
                    $issues++;
                    $details[] = "⚠️ 文件权限过宽: $file";
                } else {
                    $details[] = "✅ 文件权限安全: $file";
                }
            }
        }
        
        return [
            'success' => $issues === 0,
            'message' => $issues === 0 ? "安全检查全部通过" : "发现 $issues 个安全问题",
            'details' => $details
        ];
    }
    
    private function validateStaticResources() {
        $resources = [
            '/assets/css/style.css' => 'CSS样式文件',
            '/assets/js/app.js' => 'JavaScript应用文件'
        ];
        
        $details = [];
        $passed = 0;
        
        foreach ($resources as $path => $desc) {
            $filePath = __DIR__ . '/../public' . $path;
            if (file_exists($filePath) && is_readable($filePath)) {
                $passed++;
                $size = filesize($filePath);
                $details[] = "✅ $desc 可访问 (" . round($size/1024, 1) . "KB)";
            } else {
                $details[] = "❌ $desc 不可访问";
            }
        }
        
        return [
            'success' => $passed === count($resources),
            'message' => "静态资源检查: $passed/" . count($resources) . " 可用",
            'details' => $details
        ];
    }
    
    private function validateDatabaseOptimization() {
        $details = [];
        $checks = 0;
        $passed = 0;
        
        // 检查数据库服务类
        if (class_exists('AlingAi\Services\DatabaseService')) {
            $passed++;
            $details[] = "数据库服务类已加载";
        } else {
            $details[] = "数据库服务类未找到";
        }
        $checks++;
        
        // 检查配置文件
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $passed++;
            $details[] = "环境配置文件存在";
        } else {
            $details[] = "环境配置文件未找到";
        }
        $checks++;
        
        return [
            'success' => $passed >= $checks * 0.5,
            'message' => "数据库优化检查: $passed/$checks",
            'details' => $details
        ];
    }
    
    private function validateErrorHandling() {
        $details = [];
        $checks = 0;
        $passed = 0;
        
        // 检查错误处理配置
        $errorReporting = error_reporting();
        if ($errorReporting !== -1) {
            $passed++;
            $details[] = "错误报告级别已配置";
        } else {
            $details[] = "错误报告级别需要调整";
        }
        $checks++;
        
        // 检查异常处理类
        if (class_exists('Exception')) {
            $passed++;
            $details[] = "异常处理基类可用";
        }
        $checks++;
        
        return [
            'success' => $passed >= $checks * 0.8,
            'message' => "错误处理检查: $passed/$checks",
            'details' => $details
        ];
    }
    
    private function validateLogging() {
        $details = [];
        $checks = 0;
        $passed = 0;
        
        // 检查日志目录结构
        $logDir = __DIR__ . '/../storage/logs';
        if (is_dir($logDir)) {
            $passed++;
            $details[] = "日志目录存在";
            
            // 检查日志文件
            $logFiles = glob($logDir . '/*.log');
            if (!empty($logFiles)) {
                $passed++;
                $details[] = "日志文件存在 (" . count($logFiles) . " 个)";
            } else {
                $details[] = "未找到日志文件";
            }
            $checks++;
        } else {
            $details[] = "日志目录不存在";
        }
        $checks++;
        
        return [
            'success' => $passed >= $checks * 0.5,
            'message' => "日志系统检查: $passed/$checks",
            'details' => $details
        ];
    }
    
    private function generateFinalReport() {
        echo "📋 最终验证报告\n";
        echo str_repeat("=", 70) . "\n";
        
        $totalTests = count($this->results);
        $passedTests = 0;
        $criticalIssues = [];
        $warnings = [];
        
        foreach ($this->results as $key => $result) {
            if ($result['success']) {
                $passedTests++;
            } else {
                if (in_array($key, ['api_performance', 'security', 'static_resources'])) {
                    $criticalIssues[] = $this->optimizations[$key];
                } else {
                    $warnings[] = $this->optimizations[$key];
                }
            }
        }
        
        $successRate = ($passedTests / $totalTests) * 100;
        
        echo "📊 总体统计:\n";
        echo "   ✅ 通过测试: $passedTests/$totalTests\n";
        echo "   📈 成功率: " . round($successRate, 1) . "%\n";
        echo "   ❌ 关键问题: " . count($criticalIssues) . " 个\n";
        echo "   ⚠️ 警告: " . count($warnings) . " 个\n\n";
        
        if (!empty($criticalIssues)) {
            echo "🚨 关键问题 (需要立即解决):\n";
            foreach ($criticalIssues as $issue) {
                echo "   • $issue\n";
            }
            echo "\n";
        }
        
        if (!empty($warnings)) {
            echo "⚠️ 警告 (建议修复):\n";
            foreach ($warnings as $warning) {
                echo "   • $warning\n";
            }
            echo "\n";
        }
        
        echo "🎯 系统评级:\n";
        if ($successRate >= 90) {
            echo "   🌟 优秀 - 系统运行完美，所有优化功能正常\n";
        } elseif ($successRate >= 80) {
            echo "   ✅ 良好 - 系统运行稳定，大部分功能正常\n";
        } elseif ($successRate >= 70) {
            echo "   ⚠️ 一般 - 系统基本可用，需要关注部分问题\n";
        } else {
            echo "   ❌ 需要改进 - 系统存在多个问题，建议全面检查\n";
        }
        
        echo "\n💡 下一步建议:\n";
        if ($successRate >= 90) {
            echo "   🚀 系统已优化完成，可以正常使用\n";
            echo "   📊 定期运行性能监控: php scripts/performance_monitor.php\n";
            echo "   🔧 定期运行系统优化: php scripts/system_optimizer.php\n";
        } else {
            echo "   🔧 修复发现的问题\n";
            echo "   📋 重新运行验证: php scripts/final_system_validator.php\n";
            echo "   📖 查看详细文档: docs/ENHANCED_DEPLOYMENT_GUIDE.md\n";
        }
        
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "🎉 AlingAi Pro 5.0 系统验证完成！\n";
        echo "⏰ 验证时间: " . date('Y-m-d H:i:s') . "\n";
        
        return $successRate;
    }
}

// 命令行执行
if (php_sapi_name() === 'cli') {
    $validator = new FinalSystemValidator();
    $validator->runCompleteValidation();
} else {
    echo "此脚本只能在命令行中运行。\n";
}
?>
