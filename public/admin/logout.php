<?php
/**
 * AlingAI Pro 5.1 系统管理员登出脚本
 * @version 1.0.0
 * @author AlingAi Team
 */

// 启动会话
session_start();

// 记录登出日志
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && isset($_SESSION['admin_username'])) {
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
            $stmt->execute([$_SESSION['admin_username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // 记录安全审计日志
                $stmt = $pdo->prepare("INSERT INTO security_audit_log (user_id, action, description, ip_address, user_agent, severity, status) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user['id'],
                    'admin_logout',
                    '管理员退出登录',
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'info',
                    'success'
                ]);
                
                // 如果有记住我令牌，从数据库中删除
                if (isset($_COOKIE['admin_remember'])) {
                    $token = $_COOKIE['admin_remember'];
                    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND token = ?");
                    $stmt->execute([$user['id'], $token]);
                }
            }
        }
    } catch (Exception $e) {
        // 记录错误日志
        error_log('Admin logout error: ' . $e->getMessage());
    }
}

// 清除所有会话变量
$_SESSION = array();

// 如果使用了基于Cookie的会话，删除会话Cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 删除记住我cookie
setcookie('admin_remember', '', time() - 3600, '/', '', true, true);

// 销毁会话
session_destroy();

// 重定向到登录页面
header('Location: login.php?logout=1');
exit; 