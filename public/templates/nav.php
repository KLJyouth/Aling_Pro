<?php
/**
 * 统一导航栏组件
 * 用于所有前台页面
 */

// 检查是否有用户会话
$isLoggedIn = isset($_SESSION['user_id']);
$userInfo = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>

<!-- 导航栏 -->
<header id="header" class="header">
    <div class="container">
        <nav class="nav">
            <a href="/" class="logo">
                <img src="/assets/images/logo.svg" alt="AlingAi Pro Logo">
                <span class="logo-text">AlingAi Pro</span>
            </a>
            
            <ul id="navMenu" class="nav-menu">
                <li class="nav-item"><a href="/" class="nav-link">首页</a></li>
                
                <li class="nav-item nav-item-has-children">
                    <a href="/solutions" class="nav-link">解决方案</a>
                    <ul class="submenu">
                        <li><a href="/solutions/enterprise" class="submenu-item">企业应用</a></li>
                        <li><a href="/solutions/education" class="submenu-item">教育培训</a></li>
                        <li><a href="/solutions/healthcare" class="submenu-item">医疗健康</a></li>
                        <li><a href="/solutions/finance" class="submenu-item">金融科技</a></li>
                        <li><a href="/solutions/retail" class="submenu-item">零售商业</a></li>
                    </ul>
                </li>
                
                <li class="nav-item nav-item-has-children">
                    <a href="/products" class="nav-link">产品</a>
                    <ul class="submenu">
                        <li><a href="/products/ai-assistant" class="submenu-item">AI 助手</a></li>
                        <li><a href="/products/data-analysis" class="submenu-item">数据分析</a></li>
                        <li><a href="/products/automation" class="submenu-item">自动化工具</a></li>
                        <li><a href="/products/integration" class="submenu-item">系统集成</a></li>
                    </ul>
                </li>
                
                <li class="nav-item"><a href="/pricing" class="nav-link">价格</a></li>
                
                <li class="nav-item nav-item-has-children">
                    <a href="/resources" class="nav-link">资源</a>
                    <ul class="submenu">
                        <li><a href="/resources/blog" class="submenu-item">博客</a></li>
                        <li><a href="/resources/case-studies" class="submenu-item">案例研究</a></li>
                        <li><a href="/resources/webinars" class="submenu-item">网络研讨会</a></li>
                        <li><a href="/resources/white-papers" class="submenu-item">白皮书</a></li>
                    </ul>
                </li>
                
                <li class="nav-item"><a href="/docs" class="nav-link">文档</a></li>
                
                <li class="nav-item"><a href="/about" class="nav-link">关于我们</a></li>
                
                <li class="nav-item"><a href="/contact" class="nav-link">联系我们</a></li>
            </ul>
            
            <div class="nav-actions">
                <?php if ($isLoggedIn && $userInfo): ?>
                <!-- 已登录状态 -->
                <div class="user-dropdown">
                    <div class="user-dropdown-btn">
                        <img src="<?= htmlspecialchars($userInfo['avatar'] ?? '/assets/images/default-avatar.png') ?>" alt="用户头像" class="user-avatar">
                        <span><?= htmlspecialchars($userInfo['name'] ?? '用户') ?></span>
                    </div>
                    <div class="user-dropdown-content">
                        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> 控制台</a>
                        <a href="/profile"><i class="fas fa-user"></i> 个人资料</a>
                        <a href="/settings"><i class="fas fa-cog"></i> 设置</a>
                        <hr>
                        <a href="/logout"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
                    </div>
                </div>
                <?php else: ?>
                <!-- 未登录状态 -->
                <a href="/login" class="btn">登录</a>
                <a href="/register" class="btn btn-outline">注册</a>
                <?php endif; ?>
            </div>
            
            <!-- 移动端菜单按钮 -->
            <div id="navToggle" class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </div>
</header>

<!-- 加载字体图标 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- 加载导航栏样式和脚本 -->
<link rel="stylesheet" href="/css/unified-nav.css">
<script src="/js/unified-nav.js"></script> 