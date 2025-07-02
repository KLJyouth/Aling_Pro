<?php
/**
 * 通用错误页面
 * 
 * 显示服务器错误和其他错误信息
 */

// 引入配置文件
require_once __DIR__ . '/config/config_loader.php';

// 获取错误代码
$errorCode = $_SERVER['REDIRECT_STATUS'] ?? 500;
$errorTitle = "服务器错误";
$errorMessage = "抱歉，服务器遇到了问题，请稍后再试。";

// 根据错误代码设置不同的消息
switch ($errorCode) {
    case 403:
        $errorTitle = "访问被拒绝";
        $errorMessage = "抱歉，您没有权限访问此页面。";
        break;
    case 404:
        $errorTitle = "页面未找到";
        $errorMessage = "抱歉，您访问的页面不存在或已被移动。";
        break;
    case 500:
        $errorTitle = "服务器错误";
        $errorMessage = "抱歉，服务器遇到了问题，请稍后再试。";
        break;
    default:
        $errorTitle = "发生错误";
        $errorMessage = "抱歉，发生了未知错误，请稍后再试。";
        break;
}

// 页面标题
$pageTitle = $errorTitle . " - AlingAi Pro";
$pageDescription = $errorMessage;

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
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h1 class="error-title"><?php echo $errorCode; ?></h1>
            <h2 class="error-subtitle"><?php echo $errorTitle; ?></h2>
            <p class="error-message"><?php echo $errorMessage; ?></p>
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