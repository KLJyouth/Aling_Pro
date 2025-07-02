<?php
/**
 * 通用错误页面
 * 
 * 显示各种服务器错误
 */

// 获取错误代码，默认为500
$errorCode = isset($_GET['code']) ? intval($_GET['code']) : 500;
$errorTitle = isset($_GET['title']) ? $_GET['title'] : '服务器错误';

// 根据错误代码设置适当的HTTP状态码
http_response_code($errorCode);

// 错误信息映射
$errorMessages = [
    400 => '请求无效，服务器无法理解您的请求',
    401 => '您需要登录后才能访问此页面',
    403 => '您没有权限访问此页面',
    500 => '服务器遇到了一个错误，无法完成您的请求',
    503 => '服务暂时不可用，请稍后再试'
];

// 获取错误信息
$errorMessage = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : '发生了一个错误';

// 页面标题和描述
$pageTitle = "$errorCode - $errorTitle - AlingAi Pro";
$pageDescription = "发生错误: $errorCode $errorTitle";

// 额外CSS和JS
$additionalCSS = ["/css/error.css"];
$additionalJS = [];

// 页面内容
ob_start();
?>

<div class="error-container">
    <div class="error-code"><?php echo $errorCode; ?></div>
    <h1 class="error-title"><?php echo htmlspecialchars($errorTitle); ?></h1>
    <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
    
    <div class="error-actions">
        <a href="/" class="btn btn-primary">
            <i class="fas fa-home"></i> 返回首页
        </a>
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 返回上一页
        </a>
        <a href="/contact.php" class="btn btn-outline">
            <i class="fas fa-envelope"></i> 联系支持
        </a>
    </div>
    
    <?php if ($errorCode == 500): ?>
    <div class="error-help">
        <h2>您可以尝试：</h2>
        <ul>
            <li>刷新页面</li>
            <li>清除浏览器缓存后重试</li>
            <li>稍后再试</li>
            <li>如果问题持续存在，请联系我们的技术支持</li>
        </ul>
    </div>
    <?php endif; ?>
</div>

<?php
$pageContent = ob_get_clean();

// 引入布局模板
require_once __DIR__ . '/templates/layout.php';
?> 