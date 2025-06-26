<?php
/**
 * AlingAI Pro 5.1 系统管理后台登录页面
 * @version 2.1.0
 * @author AlingAi Team
 */

// 启动会话
session_start();

// 检查是否已登录
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // 已登录，重定向到管理后台
    header('Location: index.php');
    exit;
}

// 处理登录请求
$loginError = '';
$loginSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证CSRF令牌
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $loginError = '安全验证失败，请重新尝试登录';
    } else {
        // 清除CSRF令牌
        unset($_SESSION['csrf_token']);
        
        // 获取登录信息
        $username = $_POST['admin_username'] ?? '';
        $password = $_POST['admin_password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        // 验证登录信息
        if (empty($username) || empty($password)) {
            $loginError = '请输入用户名和密码';
        } else {
            // 验证登录凭据
            if (validateAdminLogin($username, $password)) {
                // 登录成功
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_last_activity'] = time();
                
                // 记录登录日志
                logAdminLogin($username, true);
                
                // 设置记住我cookie
                if ($rememberMe) {
                    $token = generateRememberToken($username);
                    setcookie('admin_remember', $token, time() + 30 * 24 * 60 * 60, '/', '', true, true);
                }
                
                // 重定向到管理后台
                header('Location: index.php');
                exit;
            } else {
                // 登录失败
                $loginError = '用户名或密码错误';
                logAdminLogin($username, false);
            }
        }
    }
}

// 生成CSRF令牌
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

// 获取系统版本信息
$systemVersion = '5.1.0';

/**
 * 验证管理员登录
 */
function validateAdminLogin($username, $password) {
    // 首先尝试文件数据库验证
    try {
        $usersFile = dirname(dirname(__DIR__)) . '/storage/users/users.json';
        if (file_exists($usersFile)) {
            $users = json_decode(file_get_contents($usersFile), true);
            
            foreach ($users as $user) {
                if ($user['username'] === $username && $user['role'] === 'admin' && $user['status'] === 'active') {
                    // 验证密码
                    if (password_verify($password, $user['password'])) {
                        // 登录成功
                        return true;
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log('文件数据库验证失败: ' . $e->getMessage());
    }
    
    // 尝试数据库验证
    try {
        // 尝试加载配置文件
        $configFile = dirname(dirname(__DIR__)) . '/config/config.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            
            // 连接数据库
            if ($config['database']['type'] === 'sqlite') {
                $dbPath = dirname(dirname(__DIR__)) . '/' . $config['database']['path'];
                $pdo = new PDO("sqlite:{$dbPath}");
            } else {
                $host = $config['database']['host'];
                $port = $config['database']['port'] ?? 3306;
                $dbname = $config['database']['database'];
                $dbuser = $config['database']['username'];
                $dbpass = $config['database']['password'];
                
                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $dbuser, $dbpass);
            }
            
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 查询用户
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' AND status = 'active' LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // 更新最后登录时间
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                return true;
            }
        }
        
        return false;
    } catch (Exception $e) {
        // 出错时记录日志但返回登录失败
        error_log('Admin login validation error: ' . $e->getMessage());
        return false;
    }
}

/**
 * 记录管理员登录日志
 */
function logAdminLogin($username, $success) {
    try {
        // 尝试加载配置文件
        $configFile = dirname(dirname(__DIR__)) . '/config/config.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            
            // 连接数据库
            if ($config['database']['type'] === 'sqlite') {
                $dbPath = dirname(dirname(__DIR__)) . '/' . $config['database']['path'];
                $pdo = new PDO("sqlite:{$dbPath}");
            } else {
                $host = $config['database']['host'];
                $port = $config['database']['port'] ?? 3306;
                $dbname = $config['database']['database'];
                $dbuser = $config['database']['username'];
                $dbpass = $config['database']['password'];
                
                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $dbuser, $dbpass);
            }
            
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 获取用户ID
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // 记录安全审计日志
                $stmt = $pdo->prepare("INSERT INTO security_audit_log (user_id, action, description, ip_address, user_agent, severity, status) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user['id'], 
                    'admin_login',
                    $success ? '管理员登录成功' : '管理员登录失败',
                    $_SERVER['REMOTE_ADDR'], 
                    $_SERVER['HTTP_USER_AGENT'] ?? '',
                    $success ? 'info' : 'warning',
                    $success ? 'success' : 'failed'
                ]);
            }
        }
    } catch (Exception $e) {
        // 记录日志错误
        error_log('Admin login log error: ' . $e->getMessage());
    }
}

/**
 * 生成记住我令牌
 */
function generateRememberToken($username) {
    $token = bin2hex(random_bytes(32));
    
    try {
        // 尝试加载配置文件
        $configFile = dirname(dirname(__DIR__)) . '/config/config.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            
            // 连接数据库
            if ($config['database']['type'] === 'sqlite') {
                $dbPath = dirname(dirname(__DIR__)) . '/' . $config['database']['path'];
                $pdo = new PDO("sqlite:{$dbPath}");
            } else {
                $host = $config['database']['host'];
                $port = $config['database']['port'] ?? 3306;
                $dbname = $config['database']['database'];
                $dbuser = $config['database']['username'];
                $dbpass = $config['database']['password'];
                
                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $dbuser, $dbpass);
            }
            
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 获取用户ID
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // 保存令牌到数据库
                $expiresAt = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60);
                
                $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, token, ip_address, user_agent, expires_at) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user['id'], 
                    $token,
                    $_SERVER['REMOTE_ADDR'], 
                    $_SERVER['HTTP_USER_AGENT'] ?? '',
                    $expiresAt
                ]);
            }
        }
    } catch (Exception $e) {
        // 记录令牌生成错误
        error_log('Remember token generation error: ' . $e->getMessage());
    }
    
    return $token;
}
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

        /* 添加量子动画效果 */
        .quantum-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            opacity: 0;
            animation: float 4s infinite ease-in-out;
        }

        @keyframes float {
            0% {
                opacity: 0;
                transform: translateY(0) rotate(0deg);
            }
            50% {
                opacity: 0.5;
                transform: translateY(-20px) rotate(180deg);
            }
            100% {
                opacity: 0;
                transform: translateY(-40px) rotate(360deg);
            }
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <!-- 量子粒子背景 -->
    <div class="quantum-particles" id="particles"></div>
    
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-white text-3xl font-bold mb-2">AlingAI Pro <?php echo $systemVersion; ?></h1>
            <p class="text-white text-opacity-80">量子安全管理系统</p>
        </div>
        
        <div class="login-container p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">安全登录</h2>
                <div class="security-badge flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                    <i class="fas fa-shield-alt mr-1"></i>
                    <span>安全连接</span>
                </div>
            </div>
            
            <?php if ($loginError): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">登录失败</p>
                <p><?php echo htmlspecialchars($loginError); ?></p>
            </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-6">
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
                    <p>登录即表示您同意遵守系统<a href="#" class="text-indigo-600 hover:text-indigo-500">安全策略</a>和<a href="#" class="text-indigo-600 hover:text-indigo-500">使用条款</a></p>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-6 login-footer">
            <p>AlingAI Pro <?php echo $systemVersion; ?> &copy; <?php echo date('Y'); ?> AlingAi Team. 保留所有权利</p>
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

        // 创建量子粒子效果
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 15;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // 随机大小
                const size = Math.random() * 10 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // 随机位置
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                
                // 随机延迟
                const delay = Math.random() * 4;
                particle.style.animationDelay = `${delay}s`;
                
                // 随机颜色
                const hue = Math.random() * 60 + 200; // 蓝色到紫色范围
                particle.style.backgroundColor = `hsla(${hue}, 100%, 70%, 0.8)`;
                
                container.appendChild(particle);
            }
        }
        
        // 页面加载完成后创建粒子
        document.addEventListener('DOMContentLoaded', createParticles);
    </script>
</body>
</html>
