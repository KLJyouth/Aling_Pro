<?php
/**
 * AlingAi Pro - 重新发送验证邮件
 * 
 * 处理用户请求重新发送验证邮件
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
$status = 'error';
$message = '未授权的请求';

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    // 未登录，重定向到登录页面
    header('Location: /login');
    exit;
}

// 检查是否为POST请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    // 检查用户邮箱是否已验证
    if (isEmailVerified($userId)) {
        $status = 'info';
        $message = '您的邮箱已经验证过了';
    } else {
        // 检查是否在限制时间内（防止频繁请求）
        if (canSendVerificationEmail($userId)) {
            // 获取用户信息
            $userInfo = getUserInfo($userId);
            
            if ($userInfo) {
                // 生成新的验证令牌并发送邮件
                $token = generateVerificationToken($userId);
                sendVerificationEmail($userInfo['email'], $userInfo['name'], $userId, $token);
                
                // 记录发送时间
                recordEmailSent($userId);
                
                $status = 'success';
                $message = '验证邮件已发送，请查收您的邮箱';
            } else {
                $status = 'error';
                $message = '用户信息获取失败';
            }
        } else {
            $status = 'warning';
            $message = '请求过于频繁，请稍后再试';
        }
    }
}

// 返回JSON响应
header('Content-Type: application/json');
echo json_encode([
    'status' => $status,
    'message' => $message
]);
exit;

/**
 * 检查用户邮箱是否已验证
 * 
 * @param int $userId 用户ID
 * @return bool 是否已验证
 */
function isEmailVerified($userId) {
    $db = connectToDatabase();
    $stmt = $db->prepare('SELECT email_verified_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user && !empty($user['email_verified_at']);
}

/**
 * 检查是否可以发送验证邮件（限制频率）
 * 
 * @param int $userId 用户ID
 * @return bool 是否可以发送
 */
function canSendVerificationEmail($userId) {
    $db = connectToDatabase();
    $stmt = $db->prepare('
        SELECT created_at 
        FROM email_verification_requests 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    $lastRequest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 如果没有记录或者上次请求时间超过1分钟，则允许发送
    if (!$lastRequest) {
        return true;
    }
    
    $lastRequestTime = strtotime($lastRequest['created_at']);
    $currentTime = time();
    
    return ($currentTime - $lastRequestTime) > 60; // 1分钟限制
}

/**
 * 记录邮件发送请求
 * 
 * @param int $userId 用户ID
 */
function recordEmailSent($userId) {
    $db = connectToDatabase();
    $stmt = $db->prepare('
        INSERT INTO email_verification_requests (user_id, created_at) 
        VALUES (?, NOW())
    ');
    $stmt->execute([$userId]);
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
                <p>请点击下面的按钮验证您的邮箱地址：</p>
                <p style='text-align: center;'>
                    <a href='{$verificationUrl}' style='display: inline-block; background-color: #6B46C1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>验证邮箱</a>
                </p>
                <p>或者，您可以复制以下链接并粘贴到浏览器地址栏中：</p>
                <p>{$verificationUrl}</p>
                <p>此链接将在24小时后过期。</p>
                <p>如果您没有请求此验证邮件，请忽略此邮件。</p>
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