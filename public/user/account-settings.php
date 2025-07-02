<?php
/**
 * AlingAi Pro - 用户账号设置
 * 
 * 允许用户管理其账号设置，包括个人资料、密码、通知首选项等
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
if (!isset($_SESSION["user_id"])) {
    // 未登录，重定向到登录页面
    header("Location: /login");
    exit;
}

// 获取用户信息
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? '用户';
$userEmail = $_SESSION['user_email'] ?? '';

// 连接数据库
$db = connectToDatabase();

// 获取用户详细信息
$userInfo = getUserInfo($db, $userId);

// 处理表单提交
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'update_profile':
                $result = updateProfile($db, $userId);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                $userInfo = getUserInfo($db, $userId); // 刷新用户信息
                break;
                
            case 'change_password':
                $result = changePassword($db, $userId);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'update_notifications':
                $result = updateNotificationPreferences($db, $userId);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                $userInfo = getUserInfo($db, $userId); // 刷新用户信息
                break;
                
            case 'update_security':
                $result = updateSecuritySettings($db, $userId);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                $userInfo = getUserInfo($db, $userId); // 刷新用户信息
                break;
        }
    }
}

// 获取通知设置
$notificationPreferences = getNotificationPreferences($db, $userId);

// 获取安全设置
$securitySettings = getSecuritySettings($db, $userId);

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
 * 获取用户信息
 * 
 * @param PDO $db 数据库连接
 * @param int $userId 用户ID
 * @return array 用户信息
 */
function getUserInfo($db, $userId) {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}
