<?php
/**
 * JWT认证功能测试
 * 
 * 测试JWT令牌生成、验证和API端点保护
 */

require_once __DIR__ . '/vendor/autoload.php';

class JWTAuthenticationTest
{
    private $baseUrl;
    private $testUser = [
        'email' => 'test@alingai.com',
        'password' => 'test123456'
    ];
    
    public function __construct()
    {
        $this->baseUrl = 'http://localhost:8000';
    }
    
    public function runTests()
    {
        echo "🔐 JWT认证功能测试\n";
        echo "==================\n\n";
        
        $results = [];
        
        // 测试1: 登录并获取JWT令牌
        $results['login'] = $this->testLogin();
        
        if ($results['login']['success']) {
            $token = $results['login']['token'];
            
            // 测试2: 使用有效令牌访问受保护的端点
            $results['protected_access'] = $this->testProtectedAccess($token);
            
            // 测试3: 测试无令牌访问受保护的端点
            $results['no_token_access'] = $this->testNoTokenAccess();
            
            // 测试4: 测试无效令牌访问受保护的端点
            $results['invalid_token_access'] = $this->testInvalidTokenAccess();
            
            // 测试5: 测试用户信息获取
            $results['user_info'] = $this->testUserInfo($token);
        }
        
        // 输出测试结果
        $this->displayResults($results);
        
        return $results;
    }
    
    private function testLogin()
    {
        echo "📝 测试1: 用户登录获取JWT令牌\n";
        
        try {
            $response = $this->makeRequest('POST', '/api/auth/login', [
                'email' => $this->testUser['email'],
                'password' => $this->testUser['password']
            ]);
            
            if (isset($response['data']['token'])) {
                echo "✅ 登录成功，获取到JWT令牌\n";
                echo "   令牌: " . substr($response['data']['token'], 0, 20) . "...\n\n";
                
                return [
                    'success' => true,
                    'token' => $response['data']['token'],
                    'user' => $response['data']['user'] ?? null
                ];
            } else {
                echo "❌ 登录失败，未获取到令牌\n";
                echo "   响应: " . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n\n";
                
                return ['success' => false, 'error' => '未获取到令牌'];
            }
            
        } catch (Exception $e) {
            echo "❌ 登录测试出错: " . $e->getMessage() . "\n\n";
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testProtectedAccess($token)
    {
        echo "🔒 测试2: 使用有效令牌访问受保护端点\n";
        
        try {
            $response = $this->makeRequest('GET', '/api/history/sessions', [], $token);
            
            if ($response['success']) {
                echo "✅ 成功访问受保护端点\n";
                echo "   响应状态: " . $response['status'] . "\n\n";
                
                return ['success' => true, 'response' => $response];
            } else {
                echo "❌ 访问受保护端点失败\n";
                echo "   响应: " . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n\n";
                
                return ['success' => false, 'error' => $response['error'] ?? '未知错误'];
            }
            
        } catch (Exception $e) {
            echo "❌ 受保护端点访问测试出错: " . $e->getMessage() . "\n\n";
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testNoTokenAccess()
    {
        echo "🚫 测试3: 无令牌访问受保护端点\n";
        
        try {
            $response = $this->makeRequest('GET', '/api/history/sessions');
            
            if (!$response['success'] && $response['status'] == 401) {
                echo "✅ 正确拒绝无令牌访问\n";
                echo "   错误信息: " . ($response['error'] ?? 'Authentication required') . "\n\n";
                
                return ['success' => true, 'properly_blocked' => true];
            } else {
                echo "❌ 应该拒绝无令牌访问但没有\n";
                echo "   响应: " . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n\n";
                
                return ['success' => false, 'error' => '安全漏洞：未正确拒绝无令牌访问'];
            }
            
        } catch (Exception $e) {
            echo "❌ 无令牌访问测试出错: " . $e->getMessage() . "\n\n";
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testInvalidTokenAccess()
    {
        echo "🔓 测试4: 使用无效令牌访问受保护端点\n";
        
        $invalidToken = 'invalid.jwt.token.here';
        
        try {
            $response = $this->makeRequest('GET', '/api/history/sessions', [], $invalidToken);
            
            if (!$response['success'] && $response['status'] == 401) {
                echo "✅ 正确拒绝无效令牌访问\n";
                echo "   错误信息: " . ($response['error'] ?? 'Invalid token') . "\n\n";
                
                return ['success' => true, 'properly_blocked' => true];
            } else {
                echo "❌ 应该拒绝无效令牌访问但没有\n";
                echo "   响应: " . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n\n";
                
                return ['success' => false, 'error' => '安全漏洞：未正确拒绝无效令牌访问'];
            }
            
        } catch (Exception $e) {
            echo "❌ 无效令牌访问测试出错: " . $e->getMessage() . "\n\n";
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function testUserInfo($token)
    {
        echo "👤 测试5: 获取用户信息\n";
        
        try {
            $response = $this->makeRequest('GET', '/api/auth/me', [], $token);
            
            if ($response['success']) {
                echo "✅ 成功获取用户信息\n";
                if (isset($response['data']['user'])) {
                    echo "   用户邮箱: " . ($response['data']['user']['email'] ?? 'N/A') . "\n";
                    echo "   用户角色: " . ($response['data']['user']['role'] ?? 'N/A') . "\n";
                }
                echo "\n";
                
                return ['success' => true, 'user' => $response['data']['user'] ?? null];
            } else {
                echo "❌ 获取用户信息失败\n";
                echo "   响应: " . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n\n";
                
                return ['success' => false, 'error' => $response['error'] ?? '未知错误'];
            }
            
        } catch (Exception $e) {
            echo "❌ 用户信息获取测试出错: " . $e->getMessage() . "\n\n";
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function makeRequest($method, $endpoint, $data = [], $token = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception('cURL错误: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        if (!$decoded) {
            throw new Exception('无效的JSON响应: ' . $response);
        }
        
        return $decoded;
    }
    
    private function displayResults($results)
    {
        echo "📊 测试结果汇总\n";
        echo "================\n\n";
        
        $passed = 0;
        $total = 0;
        
        foreach ($results as $testName => $result) {
            $total++;
            if ($result['success']) {
                $passed++;
                echo "✅ {$testName}: 通过\n";
            } else {
                echo "❌ {$testName}: 失败 - " . ($result['error'] ?? '未知错误') . "\n";
            }
        }
        
        echo "\n总计: {$passed}/{$total} 项测试通过\n";
        
        if ($passed === $total) {
            echo "🎉 所有JWT认证测试通过！系统认证功能正常工作。\n\n";
        } else {
            echo "⚠️  有测试失败，需要检查JWT认证实现。\n\n";
        }
    }
}

// 运行测试
if (php_sapi_name() === 'cli') {
    $test = new JWTAuthenticationTest();
    $test->runTests();
}
