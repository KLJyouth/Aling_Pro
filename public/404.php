<?php
/**
 * 404错误页面
 * 
 * 当用户访问不存在的页面时显示
 */

// 引入配置文件
require_once __DIR__ . '/config/config_loader.php';

// 页面标题
$pageTitle = "页面未找到 - AlingAi Pro";
$pageDescription = "您访问的页面不存在或已被移动。";

// 添加页面特定的CSS
$additionalCSS = ['/css/error.css'];

// 开始输出缓冲
ob_start();
?>

<!-- 页面主要内容 -->
<main class="error-page">
    <div class="container">
        <div class="error-content glass-card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="error-title">404</h1>
            <h2 class="error-subtitle">页面未找到</h2>
            <p class="error-message">抱歉，您访问的页面不存在或已被移动。</p>
            <div class="error-actions">
                <a href="/" class="btn btn-primary">返回首页</a>
                <a href="/contact" class="btn btn-outline-primary">联系我们</a>
            </div>
        </div>
    </div>
</main>

<?php
// 获取缓冲内容
$pageContent = ob_get_clean();

// 使用页面模板
require_once __DIR__ . '/templates/page.php';
?> 