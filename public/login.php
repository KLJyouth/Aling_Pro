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
        // 模拟登录验证（实际项目中应连接数据库验证）
        if ($email === 'admin@example.com' && $password === 'password') {
            // 登录成功
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Admin User';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'admin';
            
            // 重定向到仪表盘
            header('Location: /dashboard');
            exit;
        } else {
            $error = '邮箱或密码错误';
        }
    }
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
        
        .verification-input {
            width: 3rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-family: 'JetBrains Mono', monospace;
            border-radius: 0.5rem;
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
        
        <!-- 常规登录表单 -->
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
            
            <!-- 分隔线 -->
            <div class="relative flex items-center my-8">
                <div class="flex-grow border-t border-gray-600"></div>
                <span class="flex-shrink mx-4 text-gray-400">或使用以下方式登录</span>
                <div class="flex-grow border-t border-gray-600"></div>
            </div>
            
            <!-- 社交登录按钮 -->
            <div class="grid grid-cols-2 gap-4">
                <a href="#" onclick="alert('Google登录功能尚未实现'); return false;" class="social-btn glassmorphism flex items-center justify-center py-3 px-4 rounded-lg text-white">
                    <i class="fab fa-google text-red-400 mr-2"></i> Google登录
                </a>
                <a href="#" onclick="alert('GitHub登录功能尚未实现'); return false;" class="social-btn glassmorphism flex items-center justify-center py-3 px-4 rounded-lg text-white">
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
        </form>
    </div>
    
    <script>
        // 切换密码显示/隐藏
        const togglePasswordBtn = document.getElementById('togglePassword');
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // 切换图标
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>

