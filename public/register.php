<?php
/**
 * AlingAi Pro - 注册处理
 * 
 * 处理用户注册请求，包括邮箱验证和推荐码功能
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
if (isset($_SESSION['user_id'])) {
    // 已登录，重定向到仪表盘
    header('Location: /dashboard');
    exit;
}

// 处理推荐码
$referralCode = '';
if (isset($_GET['ref'])) {
    $referralCode = $_GET['ref'];
    $_SESSION['referral_code'] = $referralCode;
} elseif (isset($_SESSION['referral_code'])) {
    $referralCode = $_SESSION['referral_code'];
}

// 如果有推荐码，验证其有效性
if (!empty($referralCode)) {
    $referrerExists = checkReferralCode($referralCode);
    if (!$referrerExists) {
        $referralCode = '';
        unset($_SESSION['referral_code']);
    }
}

// 处理表单提交
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证表单数据
    $name = trim($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';
    $terms = isset($_POST['terms']);
    $referralCode = $_POST['referral_code'] ?? '';
    
    // 验证数据
    if (empty($name)) {
        $error = '请输入您的姓名';
    } elseif (!$email) {
        $error = '请输入有效的电子邮件地址';
    } elseif (empty($password)) {
        $error = '请输入密码';
    } elseif (strlen($password) < 8) {
        $error = '密码至少需要8个字符';
    } elseif ($password !== $passwordConfirmation) {
        $error = '两次输入的密码不一致';
    } elseif (!$terms) {
        $error = '您必须同意服务条款和隐私政策';
    } elseif (!empty($referralCode) && !checkReferralCode($referralCode)) {
        $error = '无效的推荐码';
    } else {
        // 检查邮箱是否已存在
        if (emailExists($email)) {
            $error = '该邮箱已被注册';
        } else {
            // 创建用户
            $userId = createUser($name, $email, $password);
            
            if ($userId) {
                // 处理推荐码
                if (!empty($referralCode)) {
                    processReferral($userId, $referralCode);
                }
                
                // 生成验证令牌并发送验证邮件
                $verificationToken = generateVerificationToken($userId);
                sendVerificationEmail($email, $name, $userId, $verificationToken);
                
                // 自动登录
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'user';
                
                // 记录注册信息
                recordRegistration($userId, $_SERVER['REMOTE_ADDR']);
                
                // 重定向到仪表盘，并显示验证邮件提示
                header('Location: /dashboard?verified=0');
                exit;
            } else {
                $error = '注册失败，请稍后再试';
            }
        }
    }
}

/**
 * 检查推荐码是否有效
 * 
 * @param string $code 推荐码
 * @return bool 是否有效
 */
function checkReferralCode($code) {
    $db = connectToDatabase();
    $stmt = $db->prepare('SELECT id FROM users WHERE referral_code = ?');
    $stmt->execute([$code]);
    return $stmt->fetch() !== false;
}

/**
 * 检查邮箱是否已存在
 * 
 * @param string $email 邮箱
 * @return bool 是否存在
 */
function emailExists($email) {
    $db = connectToDatabase();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

/**
 * 创建新用户
 * 
 * @param string $name 姓名
 * @param string $email 邮箱
 * @param string $password 密码
 * @return int|false 成功时返回用户ID，失败时返回false
 */
function createUser($name, $email, $password) {
    $db = connectToDatabase();
    
    // 生成密码哈希
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // 生成唯一推荐码
    $referralCode = generateReferralCode();
    
    try {
        $stmt = $db->prepare('INSERT INTO users (name, email, password, referral_code, role, status, created_at) VALUES (?, ?, ?, ?, "user", "active", NOW())');
        $stmt->execute([$name, $email, $passwordHash, $referralCode]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * 生成唯一推荐码
 * 
 * @return string 推荐码
 */
function generateReferralCode() {
    $db = connectToDatabase();
    
    do {
        // 生成8位随机字母数字组合
        $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);
        
        // 检查是否已存在
        $stmt = $db->prepare('SELECT id FROM users WHERE referral_code = ?');
        $stmt->execute([$code]);
        $exists = $stmt->fetch() !== false;
    } while ($exists);
    
    return $code;
}

/**
 * 处理推荐注册
 * 
 * @param int $userId 新用户ID
 * @param string $referralCode 推荐码
 */
function processReferral($userId, $referralCode) {
    $db = connectToDatabase();
    
    // 查找推荐人
    $stmt = $db->prepare('SELECT id FROM users WHERE referral_code = ?');
    $stmt->execute([$referralCode]);
    $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($referrer) {
        // 记录推荐关系
        $stmt = $db->prepare('INSERT INTO referrals (referrer_id, referred_id, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$referrer['id'], $userId]);
        
        // 这里可以添加奖励逻辑，如积分、优惠券等
    }
}

/**
 * 生成邮箱验证令牌
 * 
 * @param int $userId 用户ID
 * @return string 验证令牌
 */
function generateVerificationToken($userId) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 60*60*24); // 24小时有效期
    
    $db = connectToDatabase();
    $stmt = $db->prepare('INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $token, $expires]);
    
    return $token;
}

/**
 * 发送验证邮件
 * 
 * @param string $email 邮箱
 * @param string $name 姓名
 * @param int $userId 用户ID
 * @param string $token 验证令牌
 */
function sendVerificationEmail($email, $name, $userId, $token) {
    $verificationUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/email/verify?id=' . $userId . '&token=' . $token;
    
    $subject = 'AlingAi Pro - 验证您的邮箱';
    
    $message = "
    <html>
    <head>
        <title>验证您的邮箱</title>
    </head>
    <body>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
            <div style='background-color: #6B46C1; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;'>
                <h1>验证您的邮箱</h1>
            </div>
            <div style='background-color: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px;'>
                <p>尊敬的 {$name}，</p>
                <p>感谢您注册 AlingAi Pro！请点击下面的按钮验证您的邮箱地址：</p>
                <p style='text-align: center;'>
                    <a href='{$verificationUrl}' style='display: inline-block; background-color: #6B46C1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>验证邮箱</a>
                </p>
                <p>或者，您可以复制以下链接并粘贴到浏览器地址栏中：</p>
                <p>{$verificationUrl}</p>
                <p>此链接将在24小时后过期。</p>
                <p>如果您没有注册 AlingAi Pro，请忽略此邮件。</p>
                <p>谢谢！<br>AlingAi Pro 团队</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: AlingAi Pro <noreply@alingai.pro>\r\n";
    
    // 实际项目中，应该使用专业的邮件发送服务
    mail($email, $subject, $message, $headers);
}

/**
 * 记录用户注册
 * 
 * @param int $userId 用户ID
 * @param string $ip 用户IP地址
 */
function recordRegistration($userId, $ip) {
    $db = connectToDatabase();
    $stmt = $db->prepare('INSERT INTO registration_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $ip, $_SERVER['HTTP_USER_AGENT']]);
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

// 显示注册页面
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安全注册 - AlingAi Pro</title>
    <meta name="description" content="AlingAi Pro零信任安全注册系统">
    
    <!-- 核心资源 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- 密码强度检测 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    
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
        
        .password-strength-bar {
            height: 6px;
            border-radius: 3px;
            transition: all 0.3s ease;
            background: linear-gradient(90deg, #ef4444, #f97316, #eab308, #22c55e);
            background-size: 400% 100%;
        }
        
        .strength-weak { background-position: 0% 50%; width: 25%; }
        .strength-fair { background-position: 33% 50%; width: 50%; }
        .strength-good { background-position: 66% 50%; width: 75%; }
        .strength-strong { background-position: 100% 50%; width: 100%; }
        
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
    <!-- 注册容器 -->
    <div class="glassmorphism rounded-3xl p-8 w-full max-w-xl shadow-2xl z-10 relative">
        <!-- Logo和标题 -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto mb-4 glassmorphism rounded-full flex items-center justify-center">
                <i class="fas fa-user-plus text-3xl text-blue-400"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">创建安全账户</h1>
            <p class="text-gray-300">加入AlingAi Pro，体验最先进的AI服务</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-100 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <!-- 注册表单 -->
        <form method="POST" action="/register" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
            <div>
                    <label for="name" class="block text-gray-300 text-sm font-medium mb-2">
                        <i class="fas fa-user mr-2"></i>姓名
                </label>
                    <input type="text" id="name" name="name" required
                    class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        placeholder="请输入您的姓名"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>
            
            <div>
                    <label for="email" class="block text-gray-300 text-sm font-medium mb-2">
                        <i class="fas fa-envelope mr-2"></i>邮箱地址
                </label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        placeholder="请输入您的邮箱地址"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
            </div>
            
            <div>
                <label for="password" class="block text-gray-300 text-sm font-medium mb-2">
                    <i class="fas fa-lock mr-2"></i>密码
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        placeholder="请设置您的密码（至少8个字符）">
                    <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <!-- 密码强度指示器 -->
                <div class="mt-2">
                    <div class="w-full bg-gray-700 rounded-full h-1.5">
                        <div id="passwordStrength" class="password-strength-bar rounded-full"></div>
                    </div>
                    <p id="passwordFeedback" class="text-xs text-gray-400 mt-1">请输入至少8个字符的密码</p>
                </div>
            </div>
            
            <div>
                <label for="password_confirmation" class="block text-gray-300 text-sm font-medium mb-2">
                    <i class="fas fa-lock mr-2"></i>确认密码
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    placeholder="请再次输入密码">
            </div>
            
            <?php if (!empty($referralCode)): ?>
                <div>
                    <label for="referral_code" class="block text-gray-300 text-sm font-medium mb-2">
                        <i class="fas fa-user-friends mr-2"></i>推荐码
                    </label>
                    <input type="text" id="referral_code" name="referral_code" readonly
                        class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-700/50"
                        value="<?php echo htmlspecialchars($referralCode); ?>">
                    <p class="text-green-400 text-xs mt-1">
                        <i class="fas fa-check-circle mr-1"></i>您正在使用推荐码注册，注册成功后双方都将获得奖励！
                    </p>
                </div>
            <?php else: ?>
                <div>
                    <label for="referral_code" class="block text-gray-300 text-sm font-medium mb-2">
                        <i class="fas fa-user-friends mr-2"></i>推荐码（可选）
                    </label>
                    <input type="text" id="referral_code" name="referral_code"
                        class="w-full px-4 py-3 glassmorphism rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        placeholder="如果您有推荐码，请在此处输入"
                        value="<?php echo htmlspecialchars($_POST['referral_code'] ?? ''); ?>">
                </div>
            <?php endif; ?>
            
            <div class="flex items-center">
                <input id="terms" name="terms" type="checkbox" required
                    class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-blue-500 focus:ring-blue-500"
                    <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                <label for="terms" class="ml-2 block text-sm text-gray-300">
                    我已阅读并同意 <a href="/terms" class="text-blue-400 hover:text-blue-300">服务条款</a> 和 <a href="/privacy" class="text-blue-400 hover:text-blue-300">隐私政策</a>
                </label>
            </div>
            
            <div>
                <button type="submit" class="w-full btn-primary text-white py-3 px-4 rounded-lg font-medium">
                    <i class="fas fa-user-plus mr-2"></i>注册
                </button>
            </div>
        </form>
        
        <!-- 分隔线 -->
        <div class="relative flex items-center my-8">
            <div class="flex-grow border-t border-gray-600"></div>
            <span class="flex-shrink mx-4 text-gray-400">或使用以下方式注册</span>
            <div class="flex-grow border-t border-gray-600"></div>
        </div>
        
        <!-- 社交登录按钮 -->
        <div class="grid grid-cols-2 gap-4">
            <a href="/login/google" class="social-btn glassmorphism flex items-center justify-center py-3 px-4 rounded-lg text-white">
                <i class="fab fa-google text-red-400 mr-2"></i> Google注册
            </a>
            <a href="/login/github" class="social-btn glassmorphism flex items-center justify-center py-3 px-4 rounded-lg text-white">
                <i class="fab fa-github text-gray-300 mr-2"></i> GitHub注册
            </a>
        </div>
        
        <!-- 登录链接 -->
        <div class="mt-8 text-center">
            <p class="text-gray-400">
                已有账号？
                <a href="/login" class="text-blue-400 hover:text-blue-300">立即登录</a>
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
        
        // 密码强度检测
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const feedback = document.getElementById('passwordFeedback');
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength-bar rounded-full';
                strengthBar.style.width = '0';
                feedback.textContent = '请输入至少8个字符的密码';
                feedback.className = 'text-xs text-gray-400 mt-1';
                return;
            }
            
            // 使用zxcvbn检测密码强度
            const result = zxcvbn(password);
            const score = result.score; // 0-4
            
            // 更新强度条
            strengthBar.className = 'password-strength-bar rounded-full';
            
            if (score === 0) {
                strengthBar.classList.add('strength-weak');
                feedback.textContent = '密码强度：非常弱';
                feedback.className = 'text-xs text-red-400 mt-1';
            } else if (score === 1) {
                strengthBar.classList.add('strength-weak');
                feedback.textContent = '密码强度：弱';
                feedback.className = 'text-xs text-red-400 mt-1';
            } else if (score === 2) {
                strengthBar.classList.add('strength-fair');
                feedback.textContent = '密码强度：一般';
                feedback.className = 'text-xs text-orange-400 mt-1';
            } else if (score === 3) {
                strengthBar.classList.add('strength-good');
                feedback.textContent = '密码强度：良好';
                feedback.className = 'text-xs text-yellow-400 mt-1';
            } else {
                strengthBar.classList.add('strength-strong');
                feedback.textContent = '密码强度：极强';
                feedback.className = 'text-xs text-green-400 mt-1';
            }
            
            // 显示反馈建议
            if (result.feedback.warning) {
                feedback.textContent += ' - ' + result.feedback.warning;
            }
        });
    </script>
</body>
</html>

