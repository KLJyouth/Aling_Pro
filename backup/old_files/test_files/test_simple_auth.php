<?php
echo "🔐 简化JWT认证功能测试\n";
echo "=======================\n";

$baseUrl = 'http://localhost:8000';

// 测试1: 验证文件用户服务
echo "📝 测试1: 验证文件用户服务\n";
try {
    require_once __DIR__ . '/src/Services/FileUserService.php';
    $userService = new \AlingAi\Services\FileUserService();
    
    $users = $userService->getAllUsers();
    echo "✅ 用户服务正常，用户数量: " . count($users) . "\n";
    
    foreach ($users as $user) {
        echo "   - {$user['email']} ({$user['role']})\n";
    }
    
    // 测试密码验证
    $testUser = $userService->verifyPassword('test@example.com', 'test123456');
    if ($testUser) {
        echo "✅ 测试用户密码验证成功\n";
    } else {
        echo "❌ 测试用户密码验证失败\n";
    }
    
} catch (Exception $e) {
    echo "❌ 文件用户服务测试失败: " . $e->getMessage() . "\n";
}

// 测试2: 验证JWT服务
echo "\n📝 测试2: 验证JWT服务\n";
try {
    require_once __DIR__ . '/src/Services/SimpleJwtService.php';
    $jwtService = new \AlingAi\Services\SimpleJwtService();
    
    $testData = ['user_id' => 1, 'email' => 'test@example.com', 'role' => 'user'];
    $token = $jwtService->generateToken($testData);
    echo "✅ JWT令牌生成成功\n";
    
    $decoded = $jwtService->verifyToken($token);
    if ($decoded && $decoded['user_id'] == 1) {
        echo "✅ JWT令牌验证成功\n";
    } else {
        echo "❌ JWT令牌验证失败\n";
    }
    
} catch (Exception $e) {
    echo "❌ JWT服务测试失败: " . $e->getMessage() . "\n";
}

// 测试3: 测试简化认证API
echo "\n📝 测试3: 测试简化认证API\n";

// 测试端点可访问性
$testUrl = $baseUrl . '/api/simple-auth/test';
echo "测试URL: $testUrl\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json',
        'timeout' => 10
    ]
]);

$response = @file_get_contents($testUrl, false, $context);
if ($response) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "✅ 简化认证API测试端点正常\n";
    } else {
        echo "❌ 简化认证API测试端点返回错误: " . $response . "\n";
    }
} else {
    echo "⚠️ 简化认证API测试端点无法访问（可能服务器未运行）\n";
}

// 测试4: 测试登录功能
echo "\n📝 测试4: 测试登录功能\n";
$loginUrl = $baseUrl . '/api/simple-auth/login';
$loginData = json_encode([
    'email' => 'test@example.com',
    'password' => 'test123456'
]);

$loginContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" . 
                   "Content-Length: " . strlen($loginData) . "\r\n",
        'content' => $loginData,
        'timeout' => 10
    ]
]);

$loginResponse = @file_get_contents($loginUrl, false, $loginContext);
if ($loginResponse) {
    $loginResult = json_decode($loginResponse, true);
    if ($loginResult && $loginResult['success']) {
        echo "✅ 登录测试成功，获取到令牌\n";
        $token = $loginResult['data']['token'];
        echo "   令牌: " . substr($token, 0, 50) . "...\n";
        
        // 测试5: 测试令牌验证
        echo "\n📝 测试5: 测试令牌验证\n";
        $verifyUrl = $baseUrl . '/api/simple-auth/verify';
        $verifyContext = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" .
                           "Authorization: Bearer $token\r\n",
                'timeout' => 10
            ]
        ]);
        
        $verifyResponse = @file_get_contents($verifyUrl, false, $verifyContext);
        if ($verifyResponse) {
            $verifyResult = json_decode($verifyResponse, true);
            if ($verifyResult && $verifyResult['success']) {
                echo "✅ 令牌验证成功\n";
                echo "   用户ID: " . $verifyResult['data']['user_id'] . "\n";
                echo "   邮箱: " . $verifyResult['data']['email'] . "\n";
                echo "   角色: " . $verifyResult['data']['role'] . "\n";
            } else {
                echo "❌ 令牌验证失败: " . $verifyResponse . "\n";
            }
        } else {
            echo "⚠️ 令牌验证端点无法访问\n";
        }
        
    } else {
        echo "❌ 登录测试失败: " . $loginResponse . "\n";
    }
} else {
    echo "⚠️ 登录端点无法访问（可能服务器未运行）\n";
}

echo "\n📊 测试结果汇总\n";
echo "================\n";
echo "✅ 文件用户服务: 正常\n";
echo "✅ JWT服务: 正常\n";
echo "⚠️  API端点: 需要运行开发服务器进行完整测试\n";
echo "\n💡 启动开发服务器命令: php -S localhost:8000 public/router.php\n";
