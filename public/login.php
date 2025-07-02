<?php
/**
 * 登录页面
 * 
 * @version 1.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 启动会话
session_start();

// 如果用户已登录，重定向到控制台
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

// 处理表单提交
$formError = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);
    
    // 简单验证
    if (empty($email) || empty($password)) {
        $formError = true;
        $errorMessage = '请填写所有必填字段';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = true;
        $errorMessage = '请输入有效的电子邮件地址';
    } else {
        // 在实际应用中，这里会验证用户凭据并设置会话
        // 这里仅模拟登录成功
        if ($email === 'demo@alingai.pro' && $password === 'demo123') {
            // 设置会话
            $_SESSION['user_id'] = 1;
            $_SESSION['user'] = [
                'name' => '演示用户',
                'email' => $email,
                'avatar' => '/assets/images/default-avatar.png'
            ];
            
            // 如果选择"记住我"，设置cookie
            if ($rememberMe) {
                setcookie('user_logged_in', 'true', time() + 30 * 24 * 60 * 60, '/');
                setcookie('user_email', $email, time() + 30 * 24 * 60 * 60, '/');
            }
            
            // 重定向到控制台
            header('Location: /dashboard');
            exit;
        } else {
            $formError = true;
            $errorMessage = '邮箱或密码不正确';
        }
    }
}

// 页面信息设置
$pageTitle = '登录 - AlingAi Pro';
$pageDescription = '登录您的AlingAi Pro账户，访问智能AI助手和数据分析工具';
$pageKeywords = '登录, 账户, 用户, AlingAi Pro';

// 包含页面模板
require_once __DIR__ . '/templates/page.php';

// 渲染页面头部
renderPageHeader();
?>

<!-- 登录页面内容 -->
<section class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="/" class="auth-logo">
                <img src="/assets/images/logo.svg" alt="AlingAi Pro Logo">
            </a>
            <h1>登录</h1>
            <p>欢迎回来！请登录您的账户</p>
        </div>
        
        <?php if ($formError): ?>
        <div class="auth-error">
            <i class="fas fa-exclamation-circle"></i>
            <p><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="auth-form">
            <form method="post" action="">
                <div class="form-group">
                    <label for="email">电子邮件</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
            </div>
            
                <div class="form-group">
                    <div class="password-label">
                        <label for="password">密码</label>
                        <a href="/forgot-password" class="forgot-password">忘记密码？</a>
                    </div>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" aria-label="显示密码">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
                <div class="form-group remember-me">
                    <label class="checkbox">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span class="checkmark"></span>
                        记住我
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">登录</button>
                </div>
            </form>
        </div>
        
        <div class="auth-divider">
            <span>或</span>
            </div>
            
        <div class="social-login">
            <button class="btn btn-social btn-wechat">
                <i class="fab fa-weixin"></i>
                微信登录
            </button>
            <button class="btn btn-social btn-qq">
                <i class="fab fa-qq"></i>
                QQ登录
                </button>
            </div>
            
        <div class="auth-footer">
            <p>还没有账户？<a href="/register">立即注册</a></p>
            </div>
            
        <!-- 演示账户提示 -->
        <div class="demo-account">
            <div class="demo-header">
                <i class="fas fa-info-circle"></i>
                <span>演示账户</span>
            </div>
            <div class="demo-info">
                <p>电子邮件: demo@alingai.pro</p>
                <p>密码: demo123</p>
            </div>
        </div>
    </div>
</section>

<!-- 页面样式 -->
<style>
    /* 登录页面样式 */
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 200px);
        padding: var(--spacing-xl) var(--spacing-md);
    }
    
    .auth-card {
        width: 100%;
        max-width: 450px;
        padding: var(--spacing-xl);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-lg);
        box-shadow: 0 10px 30px var(--shadow-color);
    }
    
    .auth-header {
        text-align: center;
        margin-bottom: var(--spacing-lg);
    }
    
    .auth-logo {
        display: inline-block;
        margin-bottom: var(--spacing-md);
    }
    
    .auth-logo img {
        height: 40px;
    }
    
    .auth-header h1 {
        font-size: 1.8rem;
        margin-bottom: var(--spacing-sm);
        color: var(--text-color);
    }
    
    .auth-header p {
        opacity: 0.8;
    }
    
    .auth-error {
        display: flex;
        align-items: center;
        padding: var(--spacing-sm) var(--spacing-md);
        background-color: rgba(255, 69, 58, 0.1);
        border: 1px solid rgba(255, 69, 58, 0.3);
        border-radius: var(--border-radius-md);
        margin-bottom: var(--spacing-md);
    }
    
    .auth-error i {
        color: var(--error-color);
        font-size: 1.2rem;
        margin-right: var(--spacing-sm);
    }
    
    .auth-error p {
        margin: 0;
        color: var(--error-color);
    }
    
    .auth-form {
        margin-bottom: var(--spacing-lg);
    }
    
    .form-group {
        margin-bottom: var(--spacing-md);
    }
    
    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
    }
    
    .password-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .forgot-password {
        font-size: 0.9rem;
        color: var(--accent-color);
        text-decoration: none;
    }
    
    .forgot-password:hover {
        text-decoration: underline;
    }
    
    .input-icon {
        position: relative;
    }
    
    .input-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.5);
    }
    
    .input-icon input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
        color: var(--text-color);
        font-family: var(--font-main);
        font-size: 1rem;
        transition: border-color var(--transition-fast);
    }
    
    .input-icon input:focus {
        outline: none;
        border-color: var(--accent-color);
    }
    
    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        padding: 0;
    }
    
    .remember-me {
        display: flex;
        align-items: center;
    }
    
    .checkbox {
        display: flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }
    
    .checkbox input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }
    
    .checkmark {
        position: relative;
        height: 20px;
        width: 20px;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: 4px;
        margin-right: 10px;
    }
    
    .checkbox:hover input ~ .checkmark {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .checkbox input:checked ~ .checkmark {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
    }
    
    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }
    
    .checkbox input:checked ~ .checkmark:after {
        display: block;
    }
    
    .checkbox .checkmark:after {
        left: 7px;
        top: 3px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: var(--border-radius-md);
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    
    .btn-primary {
        background-color: var(--accent-color);
        color: var(--text-color);
    }
    
    .btn-primary:hover {
        background-color: rgba(10, 132, 255, 0.8);
    }
    
    .auth-divider {
        display: flex;
        align-items: center;
        margin: var(--spacing-lg) 0;
    }
    
    .auth-divider::before,
    .auth-divider::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid var(--glass-border);
    }
    
    .auth-divider span {
        padding: 0 var(--spacing-sm);
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.9rem;
    }
    
    .social-login {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }
    
    .btn-social {
        background-color: transparent;
        border: 1px solid var(--glass-border);
        color: var(--text-color);
    }
    
    .btn-social i {
        margin-right: 8px;
        font-size: 1.2rem;
    }
    
    .btn-wechat:hover {
        background-color: rgba(9, 187, 7, 0.2);
        border-color: rgba(9, 187, 7, 0.5);
    }
    
    .btn-qq:hover {
        background-color: rgba(0, 120, 213, 0.2);
        border-color: rgba(0, 120, 213, 0.5);
    }
    
    .auth-footer {
        text-align: center;
        margin-top: var(--spacing-lg);
    }
    
    .auth-footer p {
        margin: 0;
        font-size: 0.95rem;
    }
    
    .auth-footer a {
        color: var(--accent-color);
        text-decoration: none;
        font-weight: 500;
    }
    
    .auth-footer a:hover {
        text-decoration: underline;
    }
    
    /* 演示账户样式 */
    .demo-account {
        margin-top: var(--spacing-lg);
        padding: var(--spacing-sm);
        background-color: rgba(10, 132, 255, 0.1);
        border: 1px solid rgba(10, 132, 255, 0.3);
        border-radius: var(--border-radius-md);
    }
    
    .demo-header {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    
    .demo-header i {
        color: var(--accent-color);
        margin-right: 8px;
    }
    
    .demo-header span {
        font-weight: 500;
    }
    
    .demo-info {
        padding-left: 24px;
    }
    
    .demo-info p {
        margin: 5px 0;
        font-size: 0.9rem;
    }
</style>

<!-- 登录页面脚本 -->
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // 密码显示/隐藏功能
    const togglePassword = document.querySelector('.toggle-password');
                const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // 切换图标
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // 社交登录按钮
    const socialButtons = document.querySelectorAll('.btn-social');
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            alert('社交登录功能正在开发中，敬请期待！');
        });
    });
});
    </script>

<?php
// 渲染页面页脚
renderPageFooter();
?>

