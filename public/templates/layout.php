<?php
/**
 * 基础布局模板
 * 
 * 用于包装页面内容，提供通用的HTML结构
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 确保变量存在
$pageTitle = $pageTitle ?? "AlingAi Pro";
$pageDescription = $pageDescription ?? "AlingAi Pro - 先进的AI解决方案";
$additionalCSS = $additionalCSS ?? [];
$additionalJS = $additionalJS ?? [];
$pageContent = $pageContent ?? "";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    
    <!-- 基础样式 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- 额外CSS -->
    <?php foreach ($additionalCSS as $css): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
    <?php endforeach; ?>
    
    <!-- 网站图标 -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    
    <style>
        body {
            font-family: "Inter", sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .footer {
            background-color: #1f2937;
            color: #f3f4f6;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .footer-links {
            display: flex;
            gap: 1.5rem;
        }
        
        .footer-links a {
            color: #f3f4f6;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: #60a5fa;
        }

        /* 简单导航样式 */
        header {
            background-color: #1f2937;
            color: white;
            padding: 1rem 0;
        }

        .flex {
            display: flex;
        }

        .justify-between {
            justify-content: space-between;
        }

        .items-center {
            align-items: center;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .font-bold {
            font-weight: 700;
        }

        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin-left: 1.5rem;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: #60a5fa;
        }
    </style>
</head>
<body>
    <!-- 简化的导航栏 -->
    <header>
        <div class="container">
            <div class="flex justify-between items-center">
                <a href="/" class="text-xl font-bold">AlingAi Pro</a>
                <nav>
                    <ul>
                        <li><a href="/">首页</a></li>
                        <li><a href="/solutions">解决方案</a></li>
                        <li><a href="/docs.php">文档</a></li>
                        <li><a href="/contact.php">联系我们</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- 主要内容 -->
    <main class="main-content">
        <div class="container">
            <?php echo $pageContent; ?>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="footer">
        <div class="container footer-content">
            <div>
                <p>&copy; <?php echo date("Y"); ?> AlingAi Pro. 保留所有权利。</p>
            </div>
            <div class="footer-links">
                <a href="/privacy.php">隐私政策</a>
                <a href="/terms.php">服务条款</a>
                <a href="/security.php">安全说明</a>
            </div>
        </div>
    </footer>

    <!-- 额外JS -->
    <?php foreach ($additionalJS as $js): ?>
    <script src="<?php echo htmlspecialchars($js); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
