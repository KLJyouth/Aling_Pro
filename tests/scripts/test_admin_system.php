<?php
/**
 * AlingAi Pro 5.0 - 管理员系统测试脚本
 * 用于测试管理员系统的各项功能
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Auth/AdminAuthService.php';

use AlingAi\Auth\AdminAuthService;

class AdminSystemTester
{
    private $authService;
    private $results = [];
    
    public function __construct()
    {
        $this->authService = new AdminAuthService();
    }
    
    public function runTests()
    {
        echo "=== AlingAi Pro 5.0 管理员系统测试 ===\n\n";
        
        // 1. 测试数据库连接
        $this->testDatabaseConnection();
        
        // 2. 测试默认管理员创建
        $this->testDefaultAdminCreation();
        
        // 3. 测试管理员登录
        $this->testAdminLogin();
        
        // 4. 测试Token验证
        $this->testTokenValidation();
        
        // 5. 测试API端点
        $this->testApiEndpoints();
        
        // 输出测试结果
        $this->outputResults();
    }
    
    private function testDatabaseConnection()
    {
        echo "📡 测试数据库连接...\n";
        
        try {
            // 尝试创建默认管理员来测试数据库连接
            $result = $this->authService->createDefaultAdmin();
            
            if ($result['success'] || strpos($result['message'], '已存在') !== false) {
                $this->results['database'] = ['status' => 'success', 'message' => '数据库连接正常'];
                echo "   ✅ 数据库连接成功\n";
            } else {
                $this->results['database'] = ['status' => 'error', 'message' => $result['message']];
                echo "   ❌ 数据库连接失败: " . $result['message'] . "\n";
            }
        } catch (Exception $e) {
            $this->results['database'] = ['status' => 'error', 'message' => $e->getMessage()];
            echo "   ❌ 数据库连接异常: " . $e->getMessage() . "\n";
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
                echo "   ✅ 登录成功\n";
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
                echo "   🏷️  权限: " . implode(', ', $user['permissions']) . "\n";
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
    
    private function testApiEndpoints()
    {
        echo "🌐 测试API端点...\n";
        
        $endpoints = [
            '/admin/api/auth/login',
            '/admin/api/dashboard/stats',
            '/admin/api/users',
            '/admin/api/monitoring/apis'
        ];
        
        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($endpoint);
        }
        
        echo "\n";
    }
    
    private function testEndpoint($endpoint)
    {
        $url = 'http://localhost:8000' . $endpoint;
        
        // 使用cURL测试端点
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        // 如果有token，添加认证头
        if (isset($this->results['tokens'])) {
            $headers = ['Authorization: Bearer ' . $this->results['tokens']['access_token']];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response !== false && $httpCode < 500) {
            echo "   ✅ {$endpoint} (HTTP {$httpCode})\n";
        } else {
            echo "   ❌ {$endpoint} (无法访问)\n";
        }
    }
    
    private function outputResults()
    {
        echo "=== 测试结果汇总 ===\n\n";
        
        $totalTests = count($this->results);
        $successCount = 0;
        
        foreach ($this->results as $test => $result) {
            $icon = $result['status'] === 'success' ? '✅' : 
                   ($result['status'] === 'info' ? 'ℹ️' : '❌');
            
            echo "{$icon} {$test}: {$result['message']}\n";
            
            if ($result['status'] === 'success') {
                $successCount++;
            }
        }
        
        echo "\n";
        echo "📊 测试完成: {$successCount}/{$totalTests} 通过\n";
        
        if ($successCount === $totalTests) {
            echo "🎉 所有测试通过！管理员系统运行正常。\n";
        } else {
            echo "⚠️  有测试失败，请检查系统配置。\n";
        }
        
        echo "\n=== 快速访问链接 ===\n";
        echo "🌐 管理员登录: http://localhost:8000/admin/login.html\n";
        echo "🎛️  管理控制台: http://localhost:8000/admin/\n";
        echo "📊 API文档: http://localhost:8000/admin/api/\n";
    }
}

// 运行测试
$tester = new AdminSystemTester();
$tester->runTests();
