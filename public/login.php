<?php
/**
 * AlingAi Pro - 登录处理
 * 
 * 处理用户登录请求，包括常规登录和社交登录
 */

// 启动会话
session_start();

// 设置增强的安全头部
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// 检查是否已登录
if (isset($_SESSION['user_id']) && !isset($_GET['logout'])) {
    // 已登录，重定向到仪表盘
    header('Location: /dashboard');
    exit;
}

// 处理登出请求
if (isset($_GET['logout'])) {
    // 清除会话数据
    session_unset();
    session_destroy();
    
    // 重定向到首页
    header('Location: /');
    exit;
}

// 处理社交登录
if (isset($_GET['provider'])) {
    $provider = $_GET['provider'];
    
    // 验证提供商
    if (!in_array($provider, ['google', 'github'])) {
        $_SESSION['error'] = '不支持的登录方式';
        header('Location: /login');
        exit;
    }
    
    // 检查是否为回调
    if (isset($_GET['callback'])) {
        // 处理社交登录回调
        processSocialCallback($provider);
    } else {
        // 重定向到社交登录提供商
        redirectToProvider($provider);
    }
    
    exit;
}

// 处理表单提交
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证表单数据
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (!$email) {
        $error = '请输入有效的电子邮件地址';
    } elseif (empty($password)) {
        $error = '请输入密码';
    } else {
        // 尝试登录
        $user = authenticateUser($email, $password);
        
        if ($user) {
            // 登录成功，设置会话
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // 如果选择了"记住我"，设置cookie
            if ($remember) {
                $token = generateRememberToken($user['id']);
                setcookie('remember_token', $token, time() + 60*60*24*30, '/', '', true, true);
            }
            
            // 记录登录信息
            recordLogin($user['id'], $_SERVER['REMOTE_ADDR']);
            
            // 重定向到仪表盘
            header('Location: /dashboard');
            exit;
        } else {
            $error = '邮箱或密码错误';
        }
    }
}

/**
 * 验证用户凭据
 * 
 * @param string $email 用户邮箱
 * @param string $password 用户密码
 * @return array|false 成功时返回用户数据，失败时返回false
 */
function authenticateUser($email, $password) {
    // 连接数据库
    $db = connectToDatabase();
    
    // 查询用户
    $stmt = $db->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? AND status = "active"');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 验证密码
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

/**
 * 连接到数据库
 * 
 * @return PDO 数据库连接
 */
function connectToDatabase() {
    $host = 'localhost';
    $dbname = 'alingai_pro';
    $username = 'root';
    $password = '';
    
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die('数据库连接失败: ' . $e->getMessage());
    }
}

/**
 * 生成"记住我"令牌
 * 
 * @param int $userId 用户ID
 * @return string 生成的令牌
 */
function generateRememberToken($userId) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 60*60*24*30);
    
    $db = connectToDatabase();
    $stmt = $db->prepare('INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $token, $expires]);
    
    return $token;
}

/**
 * 记录用户登录
 * 
 * @param int $userId 用户ID
 * @param string $ip 用户IP地址
 */
function recordLogin($userId, $ip) {
    $db = connectToDatabase();
    $stmt = $db->prepare('INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $ip, $_SERVER['HTTP_USER_AGENT']]);
}

/**
 * 重定向到社交登录提供商
 * 
 * @param string $provider 提供商名称
 */
function redirectToProvider($provider) {
    switch ($provider) {
        case 'google':
            $clientId = 'YOUR_GOOGLE_CLIENT_ID';
            $redirectUri = urlencode('http://' . $_SERVER['HTTP_HOST'] . '/login/google/callback');
            $url = "https://accounts.google.com/o/oauth2/auth?client_id=$clientId&redirect_uri=$redirectUri&response_type=code&scope=email%20profile";
            header("Location: $url");
            break;
            
        case 'github':
            $clientId = 'YOUR_GITHUB_CLIENT_ID';
            $redirectUri = urlencode('http://' . $_SERVER['HTTP_HOST'] . '/login/github/callback');
            $url = "https://github.com/login/oauth/authorize?client_id=$clientId&redirect_uri=$redirectUri&scope=user:email";
            header("Location: $url");
            break;
    }
}

/**
 * 处理社交登录回调
 * 
 * @param string $provider 提供商名称
 */
function processSocialCallback($provider) {
    // 这里应该处理OAuth回调，获取用户信息并登录
    // 实际项目中，应该使用适当的OAuth库来处理这些请求
    
    // 示例：模拟成功获取用户信息
    $socialUser = [
        'id' => 'social_' . rand(1000, 9999),
        'name' => '社交用户',
        'email' => 'social_user@example.com',
    ];
    
    // 查找或创建用户
    $db = connectToDatabase();
    $stmt = $db->prepare('SELECT id, name, email, role, status FROM users WHERE email = ?');
    $stmt->execute([$socialUser['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // 创建新用户
        $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, "user", "active")');
        $stmt->execute([$socialUser['name'], $socialUser['email'], $password]);
        
        $userId = $db->lastInsertId();
        $user = [
            'id' => $userId,
            'name' => $socialUser['name'],
            'email' => $socialUser['email'],
            'role' => 'user',
            'status' => 'active'
        ];
    }
    
    // 登录用户
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // 记录登录信息
    recordLogin($user['id'], $_SERVER['REMOTE_ADDR']);
    
    // 重定向到仪表盘
    header('Location: /dashboard');
    exit;
}

// 显示登录页面
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安全登录 - AlingAi Pro</title>
    <meta name="description" content="AlingAi Pro零信任安全登录系统">
    
    <!-- 核心资源 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind配置 -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'quantum-purple': '#6B46C1',
                        'quantum-blue': '#3B82F6',
                        'quantum-cyan': '#06B6D4',
                        'neon-green': '#10B981',
                        'cyber-orange': '#F59E0B'
                    },
                    fontFamily: {
                        'mono': ['JetBrains Mono', 'monospace'],
                        'sans': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            background: linear-gradient(135deg, #0F0F23 0%, #1A1A40 25%, #2D1B69 50%, #6B46C1 100%);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6B46C1, #3B82F6);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
        }
        
        .social-btn {
            transition: all 0.3s ease;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- 登录容器 -->
    <div class="glassmorphism rounded-3xl p-8 w-full max-w-lg shadow-2xl z-10 relative">
        <!-- Logo和标题 -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto mb-4 glassmorphism rounded-full flex items-center justify-center">
                <i class="fas fa-shield-halved text-3xl text-blue-400"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">安全登录</h1>
            <p class="text-gray-300">多重身份验证保护您的账户安全</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-100 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- 登录表单 -->
        <form method="POST" action="/login" class="space-y-6">
            <div>
                <label for="email" class="block text-gray-300 text-sm font-medium mb-2">
                    <i class="fas fa-envelope mr-2"></i>邮箱地址
                </label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    placeholder="请输入您的邮箱地址">
            </div>
            
            <div>
                <label for="password" class="block text-gray-300 text-sm font-medium mb-2">
                    <i class="fas fa-lock mr-2"></i>密码
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        placeholder="请输入您的密码">
                    <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-blue-500 focus:ring-blue-500">
                    <label for="remember" class="ml-2 block text-sm text-gray-300">记住我</label>
                </div>
                <a href="/forgot-password" class="text-sm text-blue-400 hover:text-blue-300">忘记密码？</a>
            </div>
            
            <div>
                <button type="submit" class="w-full btn-primary text-white py-3 px-4 rounded-lg font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>登录
                </button>
            </div>
        </form>
        
        <!-- 分隔线 -->
        <div class="relative flex items-center my-8">
            <div class="flex-grow border-t border-gray-600"></div>
            <span class="flex-shrink mx-4 text-gray-400">或使用以下方式登录</span>
            <div class="flex-grow border-t border-gray-600"></div>
        </div>
        
        <!-- 社交登录按钮 -->
        <div class="grid grid-cols-2 gap-4">
            <a href="/login/google" class="social-btn glassmorphism flex items-center justify-center py-3 px-4 rounded-lg text-white">
                <i class="fab fa-google text-red-400 mr-2"></i> Google登录
            </a>
            <a href="/login/github" class="social-btn glassmorphism flex items-center justify-center py-3 px-4 rounded-lg text-white">
                <i class="fab fa-github text-gray-300 mr-2"></i> GitHub登录
            </a>
        </div>
        
        <!-- 注册链接 -->
        <div class="mt-8 text-center">
            <p class="text-gray-400">
                还没有账号？
                <a href="/register" class="text-blue-400 hover:text-blue-300">立即注册</a>
            </p>
        </div>
    </div>
    
    <script>
        // 切换密码显示/隐藏
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // 切换图标
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>

