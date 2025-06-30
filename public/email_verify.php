<?php
/**
 * AlingAi Pro - 邮箱验证处理
 * 
 * 处理用户邮箱验证请求
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

// 初始化变量
$status = '';
$message = '';
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$token = $_GET['token'] ?? '';

// 验证参数
if (!$userId || empty($token)) {
    $status = 'error';
    $message = '无效的验证链接';
} else {
    // 验证令牌
    $result = verifyEmailToken($userId, $token);
    
    if ($result === true) {
        // 验证成功
        $status = 'success';
        $message = '邮箱验证成功！您的账户已激活。';
        
        // 如果用户已登录，更新会话
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $_SESSION['email_verified'] = true;
        }
    } elseif ($result === 'expired') {
        // 令牌已过期
        $status = 'warning';
        $message = '验证链接已过期。我们已向您发送了一个新的验证链接，请查收您的邮箱。';
        
        // 重新发送验证邮件
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $userInfo = getUserInfo($userId);
            if ($userInfo) {
                $newToken = generateVerificationToken($userId);
                sendVerificationEmail($userInfo['email'], $userInfo['name'], $userId, $newToken);
            }
        }
    } else {
        // 验证失败
        $status = 'error';
        $message = '验证失败，请确保您使用的是最新的验证链接。';
    }
}

/**
 * 验证邮箱令牌
 * 
 * @param int $userId 用户ID
 * @param string $token 验证令牌
 * @return bool|string 成功返回true，过期返回'expired'，失败返回false
 */
function verifyEmailToken($userId, $token) {
    $db = connectToDatabase();
    
    // 查询验证记录
    $stmt = $db->prepare('SELECT * FROM email_verifications WHERE user_id = ? AND token = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$userId, $token]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$verification) {
        return false;
    }
    
    // 检查是否已过期
    $expiresAt = strtotime($verification['expires_at']);
    if (time() > $expiresAt) {
        return 'expired';
    }
    
    // 标记用户邮箱为已验证
    $stmt = $db->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = ?');
    $stmt->execute([$userId]);
    
    // 标记验证记录为已使用
    $stmt = $db->prepare('UPDATE email_verifications SET used_at = NOW() WHERE id = ?');
    $stmt->execute([$verification['id']]);
    
    return true;
}

/**
 * 获取用户信息
 * 
 * @param int $userId 用户ID
 * @return array|false 用户信息
 */
function getUserInfo($userId) {
    $db = connectToDatabase();
    $stmt = $db->prepare('SELECT name, email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>邮箱验证 - AlingAi Pro</title>
    
    <!-- 核心资源 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind配置 -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'quantum-purple': '#6B46C1',
                        'quantum-blue': '#3B82F6'
                    },
                    fontFamily: {
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container px-4 py-8">
        <div class="max-w-md mx-auto glassmorphism rounded-3xl p-8 text-center">
            <?php if ($status === 'success'): ?>
                <div class="w-20 h-20 mx-auto mb-6 bg-green-500/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-400 text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-4">验证成功</h1>
                <p class="text-gray-300 mb-6"><?php echo $message; ?></p>
                <div class="flex flex-col space-y-3">
                    <a href="/dashboard" class="bg-gradient-to-r from-purple-600 to-blue-500 text-white py-3 px-6 rounded-lg font-medium hover:opacity-90 transition-opacity">
                        进入仪表盘
                    </a>
                    <a href="/" class="text-blue-400 hover:text-blue-300">返回首页</a>
                </div>
            <?php elseif ($status === 'warning'): ?>
                <div class="w-20 h-20 mx-auto mb-6 bg-yellow-500/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-4">链接已过期</h1>
                <p class="text-gray-300 mb-6"><?php echo $message; ?></p>
                <div class="flex flex-col space-y-3">
                    <a href="/dashboard" class="bg-gradient-to-r from-purple-600 to-blue-500 text-white py-3 px-6 rounded-lg font-medium hover:opacity-90 transition-opacity">
                        进入仪表盘
                    </a>
                    <a href="/" class="text-blue-400 hover:text-blue-300">返回首页</a>
                </div>
            <?php else: ?>
                <div class="w-20 h-20 mx-auto mb-6 bg-red-500/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-400 text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-4">验证失败</h1>
                <p class="text-gray-300 mb-6"><?php echo $message; ?></p>
                <div class="flex flex-col space-y-3">
                    <a href="/login" class="bg-gradient-to-r from-purple-600 to-blue-500 text-white py-3 px-6 rounded-lg font-medium hover:opacity-90 transition-opacity">
                        返回登录
                    </a>
                    <a href="/" class="text-blue-400 hover:text-blue-300">返回首页</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>