<?php
/**
 * AlingAi Pro 用户安全注销页面
 * 安全终止用户会话并清除登录状�?
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 引入用户安全�?
require_once __DIR__ . '/includes/UserSecurity.php';

use AlingAi\Security\UserSecurity;

// 记录注销日志
if (session_status() === PHP_SESSION_NONE) {
    session_start(];
}

// 获取当前用户ID
$userId = $_SESSION['user_id'] ?? 0;

if ($userId > 0) {
    // 记录注销事件
    UserSecurity::logSecurityEvent($userId, 'logout', '用户安全注销', 'info', 'success'];
    
    // 删除记住我令牌（如果存在�?
    if (isset($_COOKIE['remember_user'])) {
        try {
            // 加载配置文件
            $configFile = dirname(__DIR__) . '/config/config.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
                
                // 连接数据�?
                if ($config['database']['type'] === 'sqlite') {
                    $dbPath = dirname(__DIR__) . '/' . $config['database']['path'];
                    $pdo = new PDO("sqlite:{$dbPath}"];
                } else {
                    $host = $config['database']['host'];
                    $port = $config['database']['port'] ?? 3306;
                    $dbname = $config['database']['database'];
                    $dbuser = $config['database']['username'];
                    $dbpass = $config['database']['password'];
                    
                    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $dbuser, $dbpass];
                }
                
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
                
                // 删除令牌
                $token = $_COOKIE['remember_user'];
                $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND token = ?"];
                $stmt->execute([$userId, $token]];
            }
        } catch (Exception $e) {
            error_log('Logout error: ' . $e->getMessage()];
        }
    }
}

// 安全销毁会�?
UserSecurity::destroySession(];

// 删除记住我cookie
setcookie('remember_user', '', time() - 3600, '/', '', true, true];

// 设置HTTP头以防止缓存
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'];
header('Cache-Control: post-check=0, pre-check=0', false];
header('Pragma: no-cache'];

// 重定向到登录页面
header('Location: login.php?logout=1'];
exit;
?> 
