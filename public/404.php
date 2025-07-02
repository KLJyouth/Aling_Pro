<?php
/**
 * 404页面 - 页面未找到
 * 
 * 当用户访问不存在的页面时显示
 */

// 设置HTTP状态码
http_response_code(404);

// 页面标题和描述
$pageTitle = "页面未找到 - AlingAi Pro";
$pageDescription = "您请求的页面不存在";

// 额外CSS和JS
$additionalCSS = ["/css/error.css"];
$additionalJS = [];

// 页面内容
ob_start();
?>

<div class="error-container">
    <div class="error-code">404</div>
    <h1 class="error-title">页面未找到</h1>
    <p class="error-message">很抱歉，您请求的页面不存在或已被移除。</p>
    
    <div class="error-actions">
        <a href="/" class="btn btn-primary">
            <i class="fas fa-home"></i> 返回首页
        </a>
        <a href="/contact.php" class="btn btn-secondary">
            <i class="fas fa-envelope"></i> 联系我们
        </a>
    </div>
    
    <div class="error-help">
        <h2>您可能想要：</h2>
        <ul>
            <li>检查您输入的URL是否正确</li>
            <li>返回上一页并尝试其他链接</li>
            <li>使用上方的导航菜单查找您需要的内容</li>
            <li>访问我们的<a href="/sitemap.php">网站地图</a>查看所有可用页面</li>
        </ul>
    </div>
</div>

<?php
$pageContent = ob_get_clean();

// 引入布局模板
require_once __DIR__ . '/templates/layout.php';
?> 