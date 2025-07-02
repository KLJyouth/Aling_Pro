<?php
/**
 * 注册页面
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
$registrationSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $agreeTerms = isset($_POST['agree_terms']);
    
    // 简单验证
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $formError = true;
        $errorMessage = '请填写所有必填字段';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = true;
        $errorMessage = '请输入有效的电子邮件地址';
    } elseif (strlen($password) < 8) {
        $formError = true;
        $errorMessage = '密码长度必须至少为8个字符';
    } elseif ($password !== $confirmPassword) {
        $formError = true;
        $errorMessage = '两次输入的密码不一致';
    } elseif (!$agreeTerms) {
        $formError = true;
        $errorMessage = '请同意服务条款和隐私政策';
    } else {
        // 在实际应用中，这里会创建用户账户并设置会话
        // 这里仅模拟注册成功
        $registrationSuccess = true;
        
        // 清空表单数据
        $name = $email = '';
    }
}

// 获取选择的方案
$selectedPlan = $_GET['plan'] ?? '';

// 页面信息设置
$pageTitle = '注册 - AlingAi Pro';
$pageDescription = '注册AlingAi Pro账户，开始使用智能AI助手和数据分析工具';
$pageKeywords = '注册, 账户, 用户, AlingAi Pro';

// 包含页面模板
require_once __DIR__ . '/templates/page.php';

// 渲染页面头部
renderPageHeader();
?>

<!-- 注册页面内容 -->
<section class="auth-container">
    <div class="auth-card">
        <?php if ($registrationSuccess): ?>
        <!-- 注册成功信息 -->
        <div class="registration-success">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>注册成功！</h1>
            <p>您的账户已创建成功，我们已向您的邮箱发送了一封验证邮件。</p>
            <p>请点击邮件中的链接完成验证，然后开始使用AlingAi Pro的强大功能。</p>
            <div class="success-actions">
                <a href="/login" class="btn btn-primary">前往登录</a>
                <a href="/" class="btn btn-secondary">返回首页</a>
            </div>
        </div>
        <?php else: ?>
        <!-- 注册表单 -->
        <div class="auth-header">
            <a href="/" class="auth-logo">
                <img src="/assets/images/logo.svg" alt="AlingAi Pro Logo">
            </a>
            <h1>创建账户</h1>
            <p>加入AlingAi Pro，体验智能化的未来</p>
        </div>
        
        <?php if ($formError): ?>
        <div class="auth-error">
            <i class="fas fa-exclamation-circle"></i>
            <p><?= htmlspecialchars($errorMessage) ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($selectedPlan): ?>
        <div class="plan-selection">
            <div class="plan-badge">
                <?php if ($selectedPlan === 'pro'): ?>
                <span>专业版</span>
                <?php elseif ($selectedPlan === 'basic'): ?>
                <span>基础版</span>
                <?php else: ?>
                <span>自定义方案</span>
                <?php endif; ?>
            </div>
            <p>您选择了 <strong><?= $selectedPlan === 'pro' ? '专业版' : ($selectedPlan === 'basic' ? '基础版' : '自定义方案') ?></strong> 方案</p>
            <a href="/pricing" class="change-plan">更改方案</a>
        </div>
        <?php endif; ?>
        
        <div class="auth-form">
            <form method="post" action="">
                <div class="form-group">
                    <label for="name">姓名</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">电子邮件</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">密码</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" aria-label="显示密码">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-level" style="width: 0%;"></div>
                        </div>
                        <span class="strength-text">密码强度: 请输入密码</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">确认密码</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-group terms">
                    <label class="checkbox">
                        <input type="checkbox" name="agree_terms" id="agree_terms">
                        <span class="checkmark"></span>
                        我已阅读并同意 <a href="/terms" target="_blank">服务条款</a> 和 <a href="/privacy" target="_blank">隐私政策</a>
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">创建账户</button>
                </div>
            </form>
        </div>
        
        <div class="auth-divider">
            <span>或</span>
        </div>
        
        <div class="social-login">
            <button class="btn btn-social btn-wechat">
                <i class="fab fa-weixin"></i>
                微信注册
            </button>
            <button class="btn btn-social btn-qq">
                <i class="fab fa-qq"></i>
                QQ注册
            </button>
        </div>
        
        <div class="auth-footer">
            <p>已有账户？<a href="/login">立即登录</a></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- 页面样式 -->
<style>
    /* 注册页面样式 */
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
    
    .plan-selection {
        padding: var(--spacing-md);
        background-color: rgba(10, 132, 255, 0.1);
        border: 1px solid rgba(10, 132, 255, 0.3);
        border-radius: var(--border-radius-md);
        margin-bottom: var(--spacing-lg);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .plan-badge {
        background-color: var(--accent-color);
        color: var(--text-color);
        padding: 4px 12px;
        border-radius: var(--border-radius-sm);
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .change-plan {
        color: var(--accent-color);
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .change-plan:hover {
        text-decoration: underline;
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
    
    .password-strength {
        margin-top: 8px;
    }
    
    .strength-bar {
        height: 4px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .strength-level {
        height: 100%;
        width: 0;
        background-color: var(--error-color);
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    
    .strength-text {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
    }
    
    .terms {
        margin-top: var(--spacing-lg);
    }
    
    .terms a {
        color: var(--accent-color);
        text-decoration: none;
    }
    
    .terms a:hover {
        text-decoration: underline;
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
        flex-shrink: 0;
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
    
    .btn-secondary {
        background-color: transparent;
        border: 1px solid var(--accent-color);
        color: var(--text-color);
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
    
    /* 注册成功样式 */
    .registration-success {
        text-align: center;
    }
    
    .success-icon {
        font-size: 4rem;
        color: var(--success-color);
        margin-bottom: var(--spacing-md);
    }
    
    .registration-success h1 {
        font-size: 1.8rem;
        margin-bottom: var(--spacing-md);
        color: var(--text-color);
    }
    
    .registration-success p {
        margin-bottom: var(--spacing-md);
        opacity: 0.8;
    }
    
    .success-actions {
        display: flex;
        gap: var(--spacing-md);
        margin-top: var(--spacing-lg);
    }
</style>

<!-- 注册页面脚本 -->
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
    
    // 密码强度检查
    if (passwordInput) {
        const strengthBar = document.querySelector('.strength-level');
        const strengthText = document.querySelector('.strength-text');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let status = '';
            
            if (password.length === 0) {
                strength = 0;
                status = '请输入密码';
            } else if (password.length < 8) {
                strength = 25;
                status = '弱';
            } else {
                // 检查密码复杂度
                if (password.length >= 8) strength += 25;
                if (password.match(/[a-z]+/)) strength += 25;
                if (password.match(/[A-Z]+/)) strength += 25;
                if (password.match(/[0-9]+/)) strength += 25;
                if (password.match(/[$@#&!]+/)) strength += 25;
                
                if (strength > 100) strength = 100;
                
                if (strength < 50) status = '弱';
                else if (strength < 75) status = '中';
                else status = '强';
            }
            
            // 更新强度条
            strengthBar.style.width = strength + '%';
            
            // 更新颜色
            if (strength < 50) {
                strengthBar.style.backgroundColor = 'var(--error-color)';
            } else if (strength < 75) {
                strengthBar.style.backgroundColor = 'var(--warning-color)';
            } else {
                strengthBar.style.backgroundColor = 'var(--success-color)';
            }
            
            // 更新文本
            strengthText.textContent = '密码强度: ' + status;
        });
    }
    
    // 社交注册按钮
    const socialButtons = document.querySelectorAll('.btn-social');
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            alert('社交注册功能正在开发中，敬请期待！');
        });
    });
});
</script>

<?php
// 渲染页面页脚
renderPageFooter();
?>

