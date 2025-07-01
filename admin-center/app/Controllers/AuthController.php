<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Core\View;
use App\Core\Security;
use App\Core\Config;

/**
 * 认证控制器
 * 负责处理用户登录、注销等认证相关请求
 */
class AuthController extends Controller
{
    /**
     * 显示登录表单
     * @return void
     */
    public function loginForm()
    {
        // 检查用户是否已登录
        if ($this->isLoggedIn()) {
            $this->redirect('/');
            return;
        }
        
        // 渲染登录视图
        $this->view('auth.login', [
            'pageTitle' => 'IT运维中心 - 安全登录'
        ]);
    }
    
    /**
     * 处理用户登录请求
     * @return void
     */
    public function login()
    {
        // 验证表单数据
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        // 简单验证
        if (empty($username) || empty($password)) {
            // 设置错误消息
            $_SESSION['login_error'] = '用户名和密码不能为空';
            header('Location: /admin/login');
            exit;
        }
        
        try {
            // 查询用户
            $stmt = Database::getInstance()->prepare("
                SELECT id, username, password, role, status
                FROM admin_users
                WHERE username = :username
            ");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // 验证用户存在且密码正确
            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['login_error'] = '用户名或密码错误';
                header('Location: /admin/login');
                exit;
            }
            
            // 验证用户状态
            if ($user['status'] !== 'active') {
                $_SESSION['login_error'] = '账户已被禁用，请联系管理员';
                header('Location: /admin/login');
                exit;
            }
            
            // 登录成功，设置会话
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_last_activity'] = time();
            
            // 记录登录日志
            Logger::info('管理员登录成功', [
                'username' => $user['username'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            // 如果选择了"记住我"
            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30天
                
                // 存储令牌到数据库
                $stmt = Database::getInstance()->prepare("
                    INSERT INTO admin_remember_tokens (user_id, token, expires_at)
                    VALUES (:user_id, :token, :expires_at)
                ");
                $stmt->execute([
                    'user_id' => $user['id'],
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires_at' => date('Y-m-d H:i:s', $expires)
                ]);
                
                // 设置Cookie
                setcookie('admin_remember', $user['id'] . ':' . $token, $expires, '/', '', true, true);
            }
            
            // 检查是否有登录后重定向地址
            if (isset($_SESSION['redirect_after_login'])) {
                $redirectUrl = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirectUrl);
                exit;
            }
            
            // 重定向到仪表盘
            header('Location: /admin/dashboard');
            exit;
            
        } catch (\Exception $e) {
            // 记录错误
            Logger::error('登录过程中发生错误: ' . $e->getMessage());
            
            // 设置错误消息
            $_SESSION['login_error'] = '登录过程中发生错误，请稍后再试';
            header('Location: /admin/login');
            exit;
        }
    }
    
    /**
     * 处理用户注销请求
     * @return void
     */
    public function logout()
    {
        // 记录注销日志
        if (isset($_SESSION['admin_user_id'])) {
            $this->logLogout($_SESSION['admin_user_id']);
        }
        
        // 清除会话数据
        session_unset();
        session_destroy();
        
        // 重定向到登录页面
        $this->redirect('/login');
    }
    
    /**
     * 验证用户凭据
     * @param string $username 用户名
     * @param string $password 密码
     * @return array|bool 成功时返回用户数组，失败时返回false
     */
    private function authenticate($username, $password)
    {
        // 连接数据库
        $db = $this->connectToDatabase();
        
        // 查询用户
        $stmt = $db->prepare('SELECT id, username, password, role, status FROM admin_users WHERE username = ? AND status = "active"');
        $stmt->execute([$username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // 验证密码
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * 连接到数据库
     * @return \PDO 数据库连接
     */
    private function connectToDatabase()
    {
        $host = 'localhost';
        $dbname = 'alingai_pro';
        $username = 'root';
        $password = '';
        
        try {
            $db = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $db;
        } catch (\PDOException $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 记录用户登录
     * @param int $userId 用户ID
     * @return void
     */
    private function logLogin($userId)
    {
        $db = $this->connectToDatabase();
        $stmt = $db->prepare('INSERT INTO admin_login_history (user_id, ip_address, user_agent, action) VALUES (?, ?, ?, "login")');
        $stmt->execute([$userId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
        
        // 更新最后登录时间
        $stmt = $db->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
        $stmt->execute([$userId]);
    }
    
    /**
     * 记录用户注销
     * @param int $userId 用户ID
     * @return void
     */
    private function logLogout($userId)
    {
        $db = $this->connectToDatabase();
        $stmt = $db->prepare('INSERT INTO admin_login_history (user_id, ip_address, user_agent, action) VALUES (?, ?, ?, "logout")');
        $stmt->execute([$userId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
    }
    
    /**
     * 检查用户是否已登录
     * @return bool 是否已登录
     */
    private function isLoggedIn()
    {
        return isset($_SESSION['admin_user_id']);
    }

    /**
     * 显示忘记密码表单
     * @return void
     */
    public function forgotPasswordForm()
    {
        // 渲染忘记密码页面
        View::display('auth.forgot-password', [
            'pageTitle' => '找回密码 - IT运维中心'
        ]);
    }

    /**
     * 处理发送密码重置链接请求
     * @return void
     */
    public function sendResetLink()
    {
        // 获取表单数据
        $email = $_POST['email'] ?? '';
        
        // 验证邮箱
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['forgot_password_error'] = '请提供有效的电子邮箱地址';
            header('Location: /admin/forgot-password');
            exit;
        }
        
        try {
            // 查询用户
            $stmt = Database::getInstance()->prepare("
                SELECT id, username, email, status
                FROM admin_users
                WHERE email = :email
            ");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // 即使用户不存在，也显示成功消息（安全考虑）
            if (!$user || $user['status'] !== 'active') {
                $_SESSION['forgot_password_success'] = '如果该邮箱地址存在于我们的系统中，您将收到密码重置链接';
                header('Location: /admin/forgot-password');
                exit;
            }
            
            // 生成重置令牌
            $token = bin2hex(random_bytes(32));
            $expires = time() + (60 * 60); // 1小时有效期
            
            // 存储令牌到数据库
            $stmt = Database::getInstance()->prepare("
                INSERT INTO admin_password_resets (user_id, token, expires_at)
                VALUES (:user_id, :token, :expires_at)
            ");
            $stmt->execute([
                'user_id' => $user['id'],
                'token' => password_hash($token, PASSWORD_DEFAULT),
                'expires_at' => date('Y-m-d H:i:s', $expires)
            ]);
            
            // 构建重置链接
            $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/admin/reset-password/' . $token;
            
            // 发送重置邮件
            $subject = 'AlingAi Pro IT运维中心 - 密码重置';
            $message = "
                <html>
                <head>
                    <title>密码重置</title>
                </head>
                <body>
                    <h2>密码重置请求</h2>
                    <p>您好，{$user['username']}：</p>
                    <p>我们收到了您的密码重置请求。请点击下面的链接重置您的密码：</p>
                    <p><a href=\"{$resetLink}\">{$resetLink}</a></p>
                    <p>此链接将在1小时后失效。</p>
                    <p>如果您没有请求重置密码，请忽略此邮件。</p>
                    <p>谢谢！</p>
                    <p>AlingAi Pro IT运维团队</p>
                </body>
                </html>
            ";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: noreply@alingai.com\r\n";
            
            // 尝试发送邮件
            if (mail($user['email'], $subject, $message, $headers)) {
                // 记录日志
                Logger::info('密码重置链接已发送', [
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
                
                $_SESSION['forgot_password_success'] = '密码重置链接已发送到您的邮箱';
            } else {
                // 邮件发送失败
                Logger::error('密码重置邮件发送失败', [
                    'username' => $user['username'],
                    'email' => $user['email']
                ]);
                
                $_SESSION['forgot_password_error'] = '邮件发送失败，请稍后再试';
            }
            
        } catch (\Exception $e) {
            // 记录错误
            Logger::error('发送密码重置链接时出错: ' . $e->getMessage());
            $_SESSION['forgot_password_error'] = '处理您的请求时出错，请稍后再试';
        }
        
        // 重定向回忘记密码页面
        header('Location: /admin/forgot-password');
        exit;
    }

    /**
     * 显示密码重置表单
     * @param string $token 重置令牌
     * @return void
     */
    public function resetPasswordForm($token)
    {
        // 验证令牌
        if (empty($token)) {
            $_SESSION['reset_password_error'] = '无效的密码重置令牌';
            header('Location: /admin/login');
            exit;
        }
        
        // 渲染密码重置页面
        View::display('auth.reset-password', [
            'pageTitle' => '重置密码 - IT运维中心',
            'token' => $token
        ]);
    }

    /**
     * 处理密码重置请求
     * @return void
     */
    public function resetPassword()
    {
        // 获取表单数据
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        // 验证输入
        if (empty($token)) {
            $_SESSION['reset_password_error'] = '无效的密码重置令牌';
            header('Location: /admin/login');
            exit;
        }
        
        if (empty($password) || strlen($password) < 8) {
            $_SESSION['reset_password_error'] = '密码必须至少包含8个字符';
            header('Location: /admin/reset-password/' . $token);
            exit;
        }
        
        if ($password !== $passwordConfirm) {
            $_SESSION['reset_password_error'] = '两次输入的密码不一致';
            header('Location: /admin/reset-password/' . $token);
            exit;
        }
        
        try {
            // 查询有效的重置令牌
            $stmt = Database::getInstance()->prepare("
                SELECT pr.user_id, pr.token, u.username
                FROM admin_password_resets pr
                JOIN admin_users u ON pr.user_id = u.id
                WHERE pr.expires_at > NOW()
                ORDER BY pr.created_at DESC
                LIMIT 1
            ");
            $stmt->execute();
            $reset = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$reset) {
                $_SESSION['reset_password_error'] = '密码重置令牌无效或已过期';
                header('Location: /admin/forgot-password');
                exit;
            }
            
            // 验证令牌
            if (!password_verify($token, $reset['token'])) {
                $_SESSION['reset_password_error'] = '密码重置令牌无效';
                header('Location: /admin/forgot-password');
                exit;
            }
            
            // 更新用户密码
            $stmt = Database::getInstance()->prepare("
                UPDATE admin_users
                SET password = :password, updated_at = NOW()
                WHERE id = :user_id
            ");
            $stmt->execute([
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'user_id' => $reset['user_id']
            ]);
            
            // 删除所有该用户的重置令牌
            $stmt = Database::getInstance()->prepare("
                DELETE FROM admin_password_resets
                WHERE user_id = :user_id
            ");
            $stmt->execute(['user_id' => $reset['user_id']]);
            
            // 记录日志
            Logger::info('用户密码已重置', [
                'user_id' => $reset['user_id'],
                'username' => $reset['username'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            // 设置成功消息
            $_SESSION['login_success'] = '密码重置成功，请使用新密码登录';
            header('Location: /admin/login');
            exit;
            
        } catch (\Exception $e) {
            // 记录错误
            Logger::error('密码重置过程中出错: ' . $e->getMessage());
            $_SESSION['reset_password_error'] = '处理您的请求时出错，请稍后再试';
            header('Location: /admin/reset-password/' . $token);
            exit;
        }
    }

    /**
     * API登录
     * @return void
     */
    public function apiLogin()
    {
        // 检查请求方法
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method Not Allowed'], 405);
            return;
        }
        
        // 获取JSON请求体
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // 验证必要字段
        if (!isset($data['username']) || !isset($data['password'])) {
            $this->jsonResponse(['error' => 'Bad Request', 'message' => '用户名和密码是必需的'], 400);
            return;
        }
        
        // 查询用户
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        $user = \App\Core\Database::fetchOne($sql, [$data['username']]);
        
        // 验证用户存在且密码正确
        if (!$user || !\App\Core\Security::verifyPassword($data['password'], $user['password'])) {
            $this->jsonResponse(['error' => 'Unauthorized', 'message' => '用户名或密码不正确'], 401);
            return;
        }
        
        // 生成API令牌
        $token = \App\Core\Security::generateApiToken($user['id']);
        
        // 返回令牌
        $this->jsonResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => \App\Core\Config::get('security.token.lifetime', 86400)
        ]);
    }

    /**
     * API刷新令牌
     * @return void
     */
    public function apiRefreshToken()
    {
        // 检查请求方法
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method Not Allowed'], 405);
            return;
        }
        
        // 获取Authorization请求头
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        // 检查Bearer令牌
        if (strpos($authHeader, 'Bearer ') !== 0) {
            $this->jsonResponse(['error' => 'Unauthorized', 'message' => '需要Bearer令牌'], 401);
            return;
        }
        
        // 提取令牌
        $token = substr($authHeader, 7);
        
        // 刷新令牌
        $newToken = \App\Core\Security::refreshApiToken($token);
        
        if ($newToken === false) {
            $this->jsonResponse(['error' => 'Unauthorized', 'message' => '无效的令牌'], 401);
            return;
        }
        
        // 返回新令牌
        $this->jsonResponse([
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => \App\Core\Config::get('security.token.lifetime', 86400)
        ]);
    }

    /**
     * API注销
     * @return void
     */
    public function apiLogout()
    {
        // API注销在无状态JWT认证中不需要服务器操作
        // 客户端只需丢弃令牌即可
        $this->jsonResponse(['message' => '注销成功']);
    }

    /**
     * 返回JSON响应
     * @param array $data 响应数据
     * @param int $statusCode HTTP状态码
     * @return void
     */
    protected function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
} 