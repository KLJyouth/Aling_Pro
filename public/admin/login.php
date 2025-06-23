<?php
/**
 * AlingAI Pro 5.1 系统管理后台登录页面
 * @version 2.1.0
 * @author AlingAi Team
 */

// 检查是否有登录错误
$loginError = $_GET['error'] ?? '';
$errorMessage = '';

if ($loginError === 'invalid') {
    $errorMessage = '用户名或密码无效';
} elseif ($loginError === 'expired') {
    $errorMessage = '会话已过期，请重新登录';
} elseif ($loginError === 'unauthorized') {
    $errorMessage = '您没有访问权限';
}

// CSRF保护
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

// 获取系统版本信息
$systemVersion = '5.1.0';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - AlingAI Pro <?php echo $systemVersion; ?> 管理后台</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            position: relative;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .form-input {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        
        .form-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .security-badge {
            animation: pulse 2s infinite;
        }
        
        .login-footer {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-white text-3xl font-bold mb-2">AlingAI Pro <?php echo $systemVersion; ?></h1>
            <p class="text-white text-opacity-80">系统管理后台</p>
        </div>
        
        <div class="login-container p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">安全登录</h2>
                <div class="security-badge flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                    <i class="fas fa-shield-alt mr-1"></i>
                    <span>安全连接</span>
                </div>
            </div>
            
            <?php if ($errorMessage): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">登录失败</p>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
            <?php endif; ?>
            
            <form method="post" action="index.php" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div>
                    <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" id="admin_username" name="admin_username" class="form-input block w-full pl-10 py-3 rounded-md" placeholder="管理员用户名" required autofocus>
                    </div>
                </div>
                
                <div>
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">密码</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="admin_password" name="admin_password" class="form-input block w-full pl-10 py-3 rounded-md" placeholder="管理员密码" required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" id="togglePassword" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700">记住我</label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">忘记密码?</a>
                    </div>
                </div>
                
                <div>
                    <button type="submit" class="btn-primary w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        登录管理后台
                    </button>
                </div>
                
                <div class="mt-4 text-center text-sm text-gray-600">
                    <p>登录即表示您同意遵守系统的<a href="#" class="text-indigo-600 hover:text-indigo-500">安全策略</a>和<a href="#" class="text-indigo-600 hover:text-indigo-500">使用条款</a></p>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-6 login-footer">
            <p>AlingAI Pro <?php echo $systemVersion; ?> &copy; <?php echo date('Y'); ?> AlingAi Team. 保留所有权利。</p>
            <p class="mt-1">安全增强型管理后台 | <a href="#" class="text-white hover:underline">报告问题</a></p>
        </div>
    </div>
    
    <script>
        // 切换密码可见性
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('admin_password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // 表单提交前验证
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('admin_username').value;
            const password = document.getElementById('admin_password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('请输入用户名和密码');
                return;
            }
        });
    </script>
</body>
</html>
