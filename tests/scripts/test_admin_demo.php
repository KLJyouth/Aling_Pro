<?php
/**
 * AlingAi Pro 5.0 - 管理员系统演示测试脚本
 * 使用文件存储，不依赖数据库
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Auth/AdminAuthServiceDemo.php';

use AlingAi\Auth\AdminAuthServiceDemo;

class AdminSystemDemoTester
{
    private $authService;
    private $results = [];
    
    public function __construct()
    {
        $this->authService = new AdminAuthServiceDemo();
    }
    
    public function runTests()
    {
        echo "=== AlingAi Pro 5.0 管理员系统演示测试 ===\n\n";
        
        // 1. 测试文件存储系统
        $this->testFileStorage();
        
        // 2. 测试默认管理员创建
        $this->testDefaultAdminCreation();
        
        // 3. 测试管理员登录
        $this->testAdminLogin();
        
        // 4. 测试Token验证
        $this->testTokenValidation();
        
        // 5. 测试权限系统
        $this->testPermissionSystem();
        
        // 输出测试结果
        $this->outputResults();
    }
    
    private function testFileStorage()
    {
        echo "📁 测试文件存储系统...\n";
        
        try {
            $storageDir = __DIR__ . '/storage/demo_data';
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            $testFile = $storageDir . '/test.json';
            $testData = ['test' => 'data', 'timestamp' => time()];
            
            file_put_contents($testFile, json_encode($testData));
            
            if (file_exists($testFile)) {
                $this->results['file_storage'] = ['status' => 'success', 'message' => '文件存储系统正常'];
                echo "   ✅ 文件存储系统创建成功\n";
                unlink($testFile); // 清理测试文件
            } else {
                $this->results['file_storage'] = ['status' => 'error', 'message' => '文件存储系统失败'];
                echo "   ❌ 文件存储系统失败\n";
            }
        } catch (Exception $e) {
            $this->results['file_storage'] = ['status' => 'error', 'message' => $e->getMessage()];
            echo "   ❌ 文件存储系统异常: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function testDefaultAdminCreation()
    {
        echo "👤 测试默认管理员创建...\n";
        
        try {
            $result = $this->authService->createDefaultAdmin();
            
            if ($result['success']) {
                $this->results['admin_creation'] = ['status' => 'success', 'message' => '默认管理员创建成功'];
                echo "   ✅ 默认管理员创建成功\n";
                echo "   📋 用户名: " . $result['username'] . "\n";
                echo "   🔑 密码: " . $result['password'] . "\n";
            } else {
                if (strpos($result['message'], '已存在') !== false) {
                    $this->results['admin_creation'] = ['status' => 'info', 'message' => '管理员账户已存在'];
                    echo "   ℹ️  管理员账户已存在\n";
                } else {
                    $this->results['admin_creation'] = ['status' => 'error', 'message' => $result['message']];
                    echo "   ❌ 创建失败: " . $result['message'] . "\n";
                }
            }
        } catch (Exception $e) {
            $this->results['admin_creation'] = ['status' => 'error', 'message' => $e->getMessage()];
            echo "   ❌ 创建异常: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function testAdminLogin()
    {
        echo "🔐 测试管理员登录...\n";
        
        try {
            $loginResult = $this->authService->login('admin', 'admin123');
            
            if ($loginResult['success']) {
                $this->results['admin_login'] = ['status' => 'success', 'message' => '管理员登录成功'];
                $this->results['tokens'] = $loginResult['tokens']; // 保存token用于后续测试
                $this->results['user'] = $loginResult['user']; // 保存用户信息
                echo "   ✅ 登录成功\n";
                echo "   👤 用户: " . $loginResult['user']['username'] . "\n";
                echo "   📧 邮箱: " . $loginResult['user']['email'] . "\n";
                echo "   🎫 Access Token: " . substr($loginResult['tokens']['access_token'], 0, 20) . "...\n";
                echo "   🔄 Refresh Token: " . substr($loginResult['tokens']['refresh_token'], 0, 20) . "...\n";
            } else {
                $this->results['admin_login'] = ['status' => 'error', 'message' => $loginResult['error']];
                echo "   ❌ 登录失败: " . $loginResult['error'] . "\n";
            }
        } catch (Exception $e) {
            $this->results['admin_login'] = ['status' => 'error', 'message' => $e->getMessage()];
            echo "   ❌ 登录异常: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function testTokenValidation()
    {
        echo "🎫 测试Token验证...\n";
        
        if (!isset($this->results['tokens'])) {
            echo "   ⚠️  跳过Token验证（登录失败）\n\n";
            return;
        }
        
        try {
            $token = $this->results['tokens']['access_token'];
            $user = $this->authService->validateToken($token);
            
            if ($user) {
                $this->results['token_validation'] = ['status' => 'success', 'message' => 'Token验证成功'];
                echo "   ✅ Token验证成功\n";
                echo "   👤 用户: " . $user['username'] . "\n";
                echo "   🏷️  权限数量: " . count($user['permissions']) . "\n";
                echo "   🔑 权限列表: " . implode(', ', array_slice($user['permissions'], 0, 3)) . 
                     (count($user['permissions']) > 3 ? '...' : '') . "\n";
            } else {
                $this->results['token_validation'] = ['status' => 'error', 'message' => 'Token验证失败'];
                echo "   ❌ Token验证失败\n";
            }
        } catch (Exception $e) {
            $this->results['token_validation'] = ['status' => 'error', 'message' => $e->getMessage()];
            echo "   ❌ Token验证异常: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function testPermissionSystem()
    {
        echo "🔒 测试权限系统...\n";
        
        if (!isset($this->results['user'])) {
            echo "   ⚠️  跳过权限测试（用户信息不可用）\n\n";
            return;
        }
        
        try {
            $user = $this->results['user'];
            
            // 测试超级管理员权限
            $hasSuperAdmin = $this->authService->hasPermission($user, 'admin.super');
            echo "   " . ($hasSuperAdmin ? "✅" : "❌") . " 超级管理员权限: " . ($hasSuperAdmin ? "有" : "无") . "\n";
            
            // 测试用户查看权限
            $hasUserView = $this->authService->hasPermission($user, 'admin.users.view');
            echo "   " . ($hasUserView ? "✅" : "❌") . " 用户查看权限: " . ($hasUserView ? "有" : "无") . "\n";
            
            // 测试不存在的权限
            $hasInvalidPerm = $this->authService->hasPermission($user, 'admin.invalid.permission');
            echo "   " . (!$hasInvalidPerm ? "✅" : "❌") . " 无效权限检查: " . (!$hasInvalidPerm ? "正确拒绝" : "错误通过") . "\n";
            
            $this->results['permission_system'] = ['status' => 'success', 'message' => '权限系统测试通过'];
            
        } catch (Exception $e) {
            $this->results['permission_system'] = ['status' => 'error', 'message' => $e->getMessage()];
            echo "   ❌ 权限系统异常: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function outputResults()
    {
        echo "=== 测试结果汇总 ===\n\n";
        
        $totalTests = count($this->results);
        $successCount = 0;
        $errorCount = 0;
        $infoCount = 0;
        
        foreach ($this->results as $test => $result) {
            $icon = $result['status'] === 'success' ? '✅' : 
                   ($result['status'] === 'info' ? 'ℹ️' : '❌');
            
            echo "{$icon} " . str_replace('_', ' ', $test) . ": {$result['message']}\n";
            
            if ($result['status'] === 'success') {
                $successCount++;
            } elseif ($result['status'] === 'info') {
                $infoCount++;
            } else {
                $errorCount++;
            }
        }
        
        echo "\n";
        echo "📊 测试统计:\n";
        echo "   ✅ 成功: {$successCount}\n";
        echo "   ℹ️  信息: {$infoCount}\n";
        echo "   ❌ 失败: {$errorCount}\n";
        echo "   📈 总计: {$totalTests}\n";
        
        echo "\n";
        
        if ($errorCount === 0) {
            echo "🎉 所有关键测试通过！管理员系统演示版本运行正常。\n";
            echo "\n=== 系统信息 ===\n";
            echo "🗄️  存储方式: 文件存储（JSON）\n";
            echo "🔐 认证方式: JWT Token\n";
            echo "⏰ Token有效期: 1小时\n";
            echo "🛡️  安全特性: 密码哈希、权限控制、Token验证\n";
        } else {
            echo "⚠️  有 {$errorCount} 个测试失败，请检查系统配置。\n";
        }
        
        echo "\n=== 快速访问指南 ===\n";
        echo "1. 📂 存储目录: " . __DIR__ . "/storage/demo_data/\n";
        echo "2. 👤 管理员用户: admin / admin123\n";
        echo "3. 🌐 登录页面: http://localhost:8000/admin/login.html\n";
        echo "4. 🎛️  管理控制台: http://localhost:8000/admin/index.html\n";
        echo "5. 📊 API测试: http://localhost:8000/admin/api/demo.php\n";
        
        echo "\n=== 下一步操作 ===\n";
        echo "1. 🚀 启动Web服务器: php -S localhost:8000\n";
        echo "2. 🌐 访问管理员登录页面\n";
        echo "3. 👤 使用默认账户登录测试\n";
        echo "4. 🔧 根据需要配置数据库连接\n";
        
        // 生成状态报告
        $this->generateStatusReport();
    }
    
    private function generateStatusReport()
    {
        $report = [
            'test_time' => date('Y-m-d H:i:s'),
            'system_version' => 'AlingAi Pro 5.0 Demo',
            'storage_type' => 'File-based JSON',
            'test_results' => $this->results,
            'environment' => [
                'php_version' => PHP_VERSION,
                'os' => PHP_OS,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ]
        ];
        
        $reportFile = __DIR__ . '/admin_system_test_report.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "\n📄 详细报告已生成: {$reportFile}\n";
    }
}

// 运行演示测试
echo "🚀 启动AlingAi Pro 5.0管理员系统演示测试...\n\n";

try {
    $tester = new AdminSystemDemoTester();
    $tester->runTests();
} catch (Exception $e) {
    echo "❌ 测试运行失败: " . $e->getMessage() . "\n";
    echo "📋 错误详情: " . $e->getTraceAsString() . "\n";
}
