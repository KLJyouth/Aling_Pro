<?php
/**
 * 联系我们页面
 * 
 * @version 1.0.0
 * @author AlingAi Team
 * @copyright 2024 AlingAi Corporation
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 启动会话
session_start();

// 处理表单提交
$formSubmitted = false;
$formError = false;
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证表单数据
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // 简单验证
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $formError = true;
        $errorMessage = '请填写所有必填字段';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = true;
        $errorMessage = '请输入有效的电子邮件地址';
    } else {
        // 在实际应用中，这里会发送电子邮件或保存到数据库
        // 这里仅模拟成功提交
        $formSubmitted = true;
        $successMessage = '感谢您的留言！我们会尽快回复您。';
        
        // 清空表单数据
        $name = $email = $subject = $message = '';
    }
}

// 页面信息设置
$pageTitle = '联系我们 - AlingAi Pro';
$pageDescription = '联系AlingAi Pro团队，获取产品信息、技术支持或合作机会';
$pageKeywords = '联系我们, 客户支持, 技术支持, 合作, AlingAi Pro';

// 包含页面模板
require_once __DIR__ . '/templates/page.php';

// 渲染页面头部
renderPageHeader();
?>

<!-- 联系我们页面内容 -->
<section class="contact-hero">
    <div class="container">
        <h1>联系我们</h1>
        <p>无论您有任何问题、建议或合作意向，我们都非常乐意听取您的声音</p>
    </div>
</section>

<section class="contact-content">
    <div class="container">
        <div class="contact-grid">
            <!-- 联系信息 -->
            <div class="contact-info">
                <h2>联系方式</h2>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>地址</h3>
                        <p>中国上海市浦东新区张江高科技园区科苑路88号</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>电话</h3>
                        <p>+86 21 5888 8888</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>电子邮件</h3>
                        <p>contact@alingai.pro</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h3>工作时间</h3>
                        <p>周一至周五: 9:00 - 18:00</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <h3>关注我们</h3>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-weixin"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-weibo"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- 联系表单 -->
            <div class="contact-form-container">
                <h2>发送消息</h2>
                
                <?php if ($formSubmitted && !$formError): ?>
                <div class="form-success">
                    <i class="fas fa-check-circle"></i>
                    <p><?= htmlspecialchars($successMessage) ?></p>
                </div>
                <?php else: ?>
                
                <?php if ($formError): ?>
                <div class="form-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <p><?= htmlspecialchars($errorMessage) ?></p>
                </div>
                <?php endif; ?>
                
                <form method="post" action="" class="contact-form">
                    <div class="form-group">
                        <label for="name">姓名 <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">电子邮件 <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">主题 <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($subject ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">消息 <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="6" required><?= htmlspecialchars($message ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">发送消息</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- 地图区域 -->
<section class="map-section">
    <div class="container">
        <h2>我们的位置</h2>
        <div class="map-container">
            <iframe src="https://maps.google.com/maps?q=上海市浦东新区张江高科技园区科苑路88号&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" style="border:0; width: 100%; height: 400px;" allowfullscreen></iframe>
        </div>
    </div>
</section>

<!-- 常见问题区域 -->
<section class="faq-section">
    <div class="container">
        <h2>常见问题</h2>
        
        <div class="faq-grid">
            <div class="faq-item">
                <h3>如何获取技术支持？</h3>
                <p>您可以通过上方的联系表单、电子邮件或电话联系我们的技术支持团队。我们的专业人员会尽快回复您的问题。</p>
            </div>
            
            <div class="faq-item">
                <h3>贵公司提供定制开发服务吗？</h3>
                <p>是的，我们提供定制开发服务，可以根据您的具体需求定制解决方案。请联系我们的销售团队了解更多信息。</p>
            </div>
            
            <div class="faq-item">
                <h3>如何申请产品演示？</h3>
                <p>您可以通过联系表单申请产品演示，或直接发送邮件至 demo@alingai.pro，我们会安排专业人员为您进行产品演示。</p>
            </div>
            
            <div class="faq-item">
                <h3>是否提供免费试用？</h3>
                <p>是的，我们为所有产品提供14天的免费试用期。您可以在官网注册账号，无需信用卡即可开始试用。</p>
            </div>
        </div>
    </div>
</section>

<!-- 页面样式 -->
<style>
    /* 联系我们页面样式 */
    .contact-hero {
        padding: var(--spacing-xxl) 0 var(--spacing-xl);
        text-align: center;
        background: linear-gradient(to right, rgba(10, 132, 255, 0.05), rgba(191, 90, 242, 0.05));
        border-radius: var(--border-radius-lg);
        margin: 0 var(--spacing-lg) var(--spacing-xl);
    }
    
    .contact-hero h1 {
        font-size: 3rem;
        margin-bottom: var(--spacing-md);
        background: linear-gradient(to right, var(--text-color), var(--secondary-color));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
    }
    
    .contact-hero p {
        font-size: 1.25rem;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }
    
    .contact-content {
        padding: var(--spacing-xl) 0;
    }
    
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: var(--spacing-xl);
    }
    
    /* 联系信息样式 */
    .contact-info {
        padding: var(--spacing-lg);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
    }
    
    .contact-info h2 {
        margin-bottom: var(--spacing-lg);
        font-size: 1.8rem;
        color: var(--secondary-color);
    }
    
    .info-item {
        display: flex;
        margin-bottom: var(--spacing-lg);
    }
    
    .info-icon {
        flex: 0 0 50px;
        height: 50px;
        background: rgba(10, 132, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--accent-color);
        margin-right: var(--spacing-md);
    }
    
    .info-content h3 {
        margin: 0 0 5px;
        font-size: 1.1rem;
    }
    
    .info-content p {
        margin: 0;
        opacity: 0.8;
    }
    
    .social-links h3 {
        margin-bottom: var(--spacing-sm);
        font-size: 1.1rem;
    }
    
    .social-icons {
        display: flex;
        gap: var(--spacing-md);
    }
    
    .social-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-color);
        font-size: 1.2rem;
        transition: all var(--transition-fast);
    }
    
    .social-icon:hover {
        background: var(--accent-color);
        transform: translateY(-3px);
    }
    
    /* 联系表单样式 */
    .contact-form-container {
        padding: var(--spacing-lg);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
    }
    
    .contact-form-container h2 {
        margin-bottom: var(--spacing-lg);
        font-size: 1.8rem;
        color: var(--secondary-color);
    }
    
    .contact-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-md);
    }
    
    .form-group {
        margin-bottom: var(--spacing-md);
    }
    
    .form-group:nth-child(3),
    .form-group:nth-child(4),
    .form-group:nth-child(5) {
        grid-column: 1 / -1;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    
    .required {
        color: var(--error-color);
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-sm);
        color: var(--text-color);
        font-family: var(--font-main);
        font-size: 1rem;
        transition: border-color var(--transition-fast);
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--accent-color);
    }
    
    .form-group button {
        padding: 12px 24px;
        font-size: 1rem;
    }
    
    .form-success,
    .form-error {
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        margin-bottom: var(--spacing-lg);
        display: flex;
        align-items: center;
    }
    
    .form-success {
        background: rgba(48, 209, 88, 0.1);
        border: 1px solid rgba(48, 209, 88, 0.3);
    }
    
    .form-error {
        background: rgba(255, 69, 58, 0.1);
        border: 1px solid rgba(255, 69, 58, 0.3);
    }
    
    .form-success i,
    .form-error i {
        font-size: 1.5rem;
        margin-right: var(--spacing-md);
    }
    
    .form-success i {
        color: var(--success-color);
    }
    
    .form-error i {
        color: var(--error-color);
    }
    
    /* 地图区域样式 */
    .map-section {
        padding: var(--spacing-xl) 0;
    }
    
    .map-section h2 {
        text-align: center;
        margin-bottom: var(--spacing-lg);
        font-size: 1.8rem;
        color: var(--secondary-color);
    }
    
    .map-container {
        border-radius: var(--border-radius-md);
        overflow: hidden;
        box-shadow: 0 10px 30px var(--shadow-color);
    }
    
    /* FAQ区域样式 */
    .faq-section {
        padding: var(--spacing-xl) 0 var(--spacing-xxl);
    }
    
    .faq-section h2 {
        text-align: center;
        margin-bottom: var(--spacing-xl);
        font-size: 1.8rem;
        color: var(--secondary-color);
    }
    
    .faq-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .faq-item {
        padding: var(--spacing-lg);
        background: var(--glass-background);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-md);
    }
    
    .faq-item h3 {
        margin-bottom: var(--spacing-sm);
        font-size: 1.2rem;
        color: var(--accent-color);
    }
    
    .faq-item p {
        opacity: 0.8;
    }
    
    /* 响应式设计 */
    @media (max-width: 992px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .contact-hero h1 {
            font-size: 2.5rem;
        }
        
        .contact-hero p {
            font-size: 1.1rem;
        }
        
        .contact-form {
            grid-template-columns: 1fr;
        }
        
        .faq-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
// 渲染页面页脚
renderPageFooter();
?> 