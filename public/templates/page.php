<?php
/**
 * 统一页面模板
 * 用于所有前台页面
 * 
 * 使用方法:
 * 1. 包含此文件
 * 2. 设置 $pageTitle, $pageDescription 等变量
 * 3. 调用 renderPageHeader() 函数
 * 4. 输出页面内容
 * 5. 调用 renderPageFooter() 函数
 */

// 默认值
$pageTitle = $pageTitle ?? 'AlingAi Pro - 量子科技风格的AI助手平台';
$pageDescription = $pageDescription ?? '探索未来科技，体验智能交互的无限可能';
$pageKeywords = $pageKeywords ?? 'AI, 人工智能, 量子科技, 助手, 深度学习';
$pageAuthor = $pageAuthor ?? 'AlingAi Team';
$pageImage = $pageImage ?? '/assets/images/demo.jpg';
$pageUrl = $pageUrl ?? 'https://alingai.pro';
$pageThemeColor = $pageThemeColor ?? '#0a0e17';
$additionalCSS = $additionalCSS ?? [];
$additionalJS = $additionalJS ?? [];

/**
 * 渲染页面头部
 */
function renderPageHeader() {
    global $pageTitle, $pageDescription, $pageKeywords, $pageAuthor, $pageImage, $pageUrl, $pageThemeColor, $additionalCSS, $additionalJS;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
    <meta name="author" content="<?= htmlspecialchars($pageAuthor) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($pageImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($pageUrl) ?>">
    <meta name="theme-color" content="<?= htmlspecialchars($pageThemeColor) ?>">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- 网站图标 -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/assets/images/apple-touch-icon.png">
    
    <!-- 基础样式 -->
    <style>
        :root {
            --background-color: #0a0e17;
            --text-color: #e6f1ff;
            --accent-color: #0a84ff;
            --accent-glow: rgba(10, 132, 255, 0.5);
            --secondary-color: #5ac8fa;
            --tertiary-color: #bf5af2;
            --surface-color: rgba(30, 40, 60, 0.8);
            --surface-border: rgba(100, 130, 200, 0.3);
            --glass-background: rgba(15, 25, 40, 0.7);
            --glass-border: rgba(100, 130, 200, 0.2);
            --glass-highlight: rgba(255, 255, 255, 0.05);
            --shadow-color: rgba(0, 0, 0, 0.5);
            --success-color: #30d158;
            --warning-color: #ff9f0a;
            --error-color: #ff453a;
            --grid-line: rgba(100, 130, 200, 0.1);
            --font-main: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.5s ease;
            --border-radius-sm: 4px;
            --border-radius-md: 8px;
            --border-radius-lg: 16px;
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
            --spacing-xxl: 48px;
            --primary-color: #0a84ff;
            --text-color-light: rgba(230, 241, 255, 0.7);
            --font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        /* 基础重置 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            width: 100%;
            font-family: var(--font-main);
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        body {
            display: flex;
            flex-direction: column;
            position: relative;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(10, 132, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 80% 70%, rgba(191, 90, 242, 0.1) 0%, transparent 20%);
            background-attachment: fixed;
        }
        
        /* 网格背景 */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(to right, var(--grid-line) 1px, transparent 1px),
                linear-gradient(to bottom, var(--grid-line) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
            opacity: 0.4;
        }
        
        /* 容器样式 */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }
        
        /* 主要内容 */
        main {
            flex: 1;
            padding-top: 80px;
        }
    </style>
    
    <!-- 加载统一导航栏样式 -->
    <link rel="stylesheet" href="/css/unified-nav.css">
    
    <!-- 加载额外的CSS文件 -->
    <?php foreach ($additionalCSS as $cssFile): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($cssFile) ?>">
    <?php endforeach; ?>
    
    <!-- 加载额外的JS文件（头部） -->
    <?php foreach ($additionalJS as $jsFile): ?>
    <?php if (isset($jsFile['position']) && $jsFile['position'] === 'head'): ?>
    <script src="<?= htmlspecialchars($jsFile['src']) ?>" <?= isset($jsFile['async']) && $jsFile['async'] ? 'async' : '' ?> <?= isset($jsFile['defer']) && $jsFile['defer'] ? 'defer' : '' ?>></script>
    <?php endif; ?>
    <?php endforeach; ?>
</head>
<body>
    <?php include __DIR__ . '/nav.php'; ?>
    
    <main>
<?php
}

/**
 * 渲染页面页脚
 */
function renderPageFooter() {
    global $additionalJS;
?>
    </main>
    
    <!-- 页脚 -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <a href="/" class="logo">
                        <img src="/assets/images/logo.svg" alt="AlingAi Pro Logo">
                        <span class="logo-text">AlingAi Pro</span>
                    </a>
                    <p style="margin-top: 15px; opacity: 0.8;">探索未来科技，体验智能交互的无限可能</p>
                </div>
                
                <div class="footer-links">
                    <div class="footer-links-column">
                        <h4>产品</h4>
                        <ul>
                            <li><a href="/products/ai-assistant">AI 助手</a></li>
                            <li><a href="/products/data-analysis">数据分析</a></li>
                            <li><a href="/products/automation">自动化工具</a></li>
                            <li><a href="/products/integration">系统集成</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-links-column">
                        <h4>解决方案</h4>
                        <ul>
                            <li><a href="/solutions/enterprise">企业应用</a></li>
                            <li><a href="/solutions/education">教育培训</a></li>
                            <li><a href="/solutions/healthcare">医疗健康</a></li>
                            <li><a href="/solutions/finance">金融科技</a></li>
                            <li><a href="/solutions/retail">零售商业</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-links-column">
                        <h4>资源</h4>
                        <ul>
                            <li><a href="/resources/blog">博客</a></li>
                            <li><a href="/resources/case-studies">案例研究</a></li>
                            <li><a href="/resources/webinars">网络研讨会</a></li>
                            <li><a href="/resources/white-papers">白皮书</a></li>
                            <li><a href="/docs">文档</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-links-column">
                        <h4>公司</h4>
                        <ul>
                            <li><a href="/about">关于我们</a></li>
                            <li><a href="/team">团队</a></li>
                            <li><a href="/careers">招贤纳士</a></li>
                            <li><a href="/contact">联系我们</a></li>
                            <li><a href="/press">新闻</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?= date('Y') ?> AlingAi Corporation. 保留所有权利。</p>
                <p style="margin-top: 5px;">
                    <a href="/privacy" style="color: inherit; margin-right: 15px;">隐私政策</a>
                    <a href="/terms" style="color: inherit; margin-right: 15px;">服务条款</a>
                    <a href="/security" style="color: inherit;">安全</a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- 页脚样式 -->
    <style>
        footer {
            background-color: var(--surface-color);
            border-top: 1px solid var(--surface-border);
            padding: var(--spacing-xl) 0;
            margin-top: var(--spacing-xxl);
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--spacing-xl);
        }
        
        .footer-logo {
            flex: 1;
            min-width: 250px;
        }
        
        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-xl);
        }
        
        .footer-links-column {
            min-width: 160px;
        }
        
        .footer-links-column h4 {
            margin-bottom: var(--spacing-md);
            color: var(--secondary-color);
        }
        
        .footer-links-column ul {
            list-style: none;
        }
        
        .footer-links-column ul li {
            margin-bottom: var(--spacing-sm);
        }
        
        .footer-links-column ul a {
            color: var(--text-color);
            text-decoration: none;
            opacity: 0.8;
            transition: opacity var(--transition-fast);
        }
        
        .footer-links-column ul a:hover {
            opacity: 1;
            color: var(--accent-color);
        }
        
        .copyright {
            margin-top: var(--spacing-xl);
            text-align: center;
            opacity: 0.6;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                gap: var(--spacing-lg);
            }
            
            .footer-links {
                flex-direction: column;
                gap: var(--spacing-lg);
            }
        }
    </style>
    
    <!-- 加载统一导航栏脚本 -->
    <script src="/js/unified-nav.js"></script>
    
    <!-- 加载额外的JS文件（底部） -->
    <?php if (isset($additionalJS) && is_array($additionalJS)): ?>
        <?php foreach ($additionalJS as $jsFile): ?>
            <?php if (!isset($jsFile['position']) || $jsFile['position'] !== 'head'): ?>
            <script src="<?= htmlspecialchars($jsFile['src']) ?>" <?= isset($jsFile['async']) && $jsFile['async'] ? 'async' : '' ?> <?= isset($jsFile['defer']) && $jsFile['defer'] ? 'defer' : '' ?>></script>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
<?php
}
?> 