<?php
namespace App\Controllers;

use App\Core\Controller;

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
        $errors = $this->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->view('auth.login', [
                'errors' => $errors,
                'pageTitle' => 'IT运维中心 - 安全登录',
                'username' => $this->input('username')
            ]);
            return;
        }
        
        // 获取表单数据
        $username = $this->input('username');
        $password = $this->input('password');
        
        // 验证用户凭据
        $authenticated = $this->authenticate($username, $password);
        
        if ($authenticated) {
            // 设置会话
            $_SESSION['admin_user_id'] = $authenticated['id'];
            $_SESSION['admin_username'] = $authenticated['username'];
            $_SESSION['admin_role'] = $authenticated['role'];
            $_SESSION['admin_last_login'] = time();
            
            // 记录登录日志
            $this->logLogin($authenticated['id']);
            
            // 重定向到仪表盘
            $this->redirect('/');
        } else {
            // 登录失败
            $this->view('auth.login', [
                'error' => '用户名或密码错误',
                'pageTitle' => 'IT运维中心 - 安全登录',
                'username' => $username
            ]);
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
} 