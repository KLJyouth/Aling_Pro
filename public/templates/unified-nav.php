<?php
/**
 * 统一导航栏模板
 * 包含所有主要功能的导航链接
 */
?>
<!-- 导航栏 -->
<header class="header glass" id="header">
    <div class="container">
        <nav class="nav">
            <div class="logo">
                <a href="/">
                    <img src="/assets/images/logo.svg" alt="AlingAI Logo" width="120">
                </a>
            </div>
            
            <div class="nav-toggle" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item"><a href="/" class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>">首页</a></li>
                <li class="nav-item"><a href="/features" class="nav-link <?php echo ($current_page == 'features') ? 'active' : ''; ?>">功能</a></li>
                
                <!-- 新闻中心 - 对应后台新闻管理功能 -->
                <li class="nav-item"><a href="/news" class="nav-link <?php echo ($current_page == 'news') ? 'active' : ''; ?>">新闻中心</a></li>
                
                <!-- 会员等级 - 对应后台会员管理功能 -->
                <li class="nav-item"><a href="/membership" class="nav-link <?php echo ($current_page == 'membership') ? 'active' : ''; ?>">会员等级</a></li>
                
                <!-- 充值中心 - 对应后台充值管理功能 -->
                <li class="nav-item"><a href="/recharge" class="nav-link <?php echo ($current_page == 'recharge') ? 'active' : ''; ?>">充值中心</a></li>
                
                <!-- API文档 - 对应后台MCP管理功能 -->
                <li class="nav-item"><a href="/api-docs" class="nav-link <?php echo ($current_page == 'api-docs') ? 'active' : ''; ?>">API文档</a></li>
                
                <li class="nav-item"><a href="/about" class="nav-link <?php echo ($current_page == 'about') ? 'active' : ''; ?>">关于我们</a></li>
                <li class="nav-item"><a href="/contact" class="nav-link <?php echo ($current_page == 'contact') ? 'active' : ''; ?>">联系我们</a></li>
            </ul>
            
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <img src="<?php echo isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : '/assets/images/default-avatar.png'; ?>" alt="用户头像" class="user-avatar">
                            <span><?php echo $_SESSION['username']; ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="user-dropdown-content">
                            <a href="/dashboard">控制面板</a>
                            <a href="/profile">个人资料</a>
                            <a href="/conversations">对话历史</a>
                            <a href="/memories">长期记忆</a>
                            <a href="/wallet">我的钱包</a>
                            <a href="/settings">账号设置</a>
                            <hr>
                            <a href="/logout">退出登录</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline">登录</a>
                    <a href="/register" class="btn">注册</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<!-- 导航栏相关的JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 导航栏切换
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }
    
    // 用户下拉菜单
    const userDropdownBtn = document.querySelector('.user-dropdown-btn');
    const userDropdownContent = document.querySelector('.user-dropdown-content');
    
    if (userDropdownBtn && userDropdownContent) {
        userDropdownBtn.addEventListener('click', function() {
            userDropdownContent.classList.toggle('show');
        });
        
        // 点击其他地方关闭下拉菜单
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.user-dropdown-btn') && 
                !event.target.closest('.user-dropdown-btn')) {
                if (userDropdownContent.classList.contains('show')) {
                    userDropdownContent.classList.remove('show');
                }
            }
        });
    }
});
</script>

<!-- 导航栏相关的CSS -->
<style>
/* 用户下拉菜单样式 */
.user-dropdown {
    position: relative;
    display: inline-block;
}

.user-dropdown-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    color: var(--text-light);
    cursor: pointer;
    transition: all var(--transition-fast) ease;
}

.user-dropdown-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.user-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

.user-dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    min-width: 200px;
    background-color: var(--bg-medium);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    z-index: 1000;
    overflow: hidden;
    margin-top: 5px;
}

.user-dropdown-content.show {
    display: block;
}

.user-dropdown-content a {
    display: block;
    padding: 12px 16px;
    color: var(--text-light);
    text-decoration: none;
    transition: background-color var(--transition-fast) ease;
}

.user-dropdown-content a:hover {
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--primary-color);
}

.user-dropdown-content hr {
    margin: 5px 0;
    border: none;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* 响应式导航栏 */
@media (max-width: 992px) {
    .nav {
        position: relative;
    }
    
    .nav-toggle {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 30px;
        height: 21px;
        cursor: pointer;
    }
    
    .nav-toggle span {
        display: block;
        height: 3px;
        width: 100%;
        background-color: var(--text-light);
        border-radius: 3px;
        transition: all var(--transition-fast) ease;
    }
    
    .nav-toggle.active span:nth-child(1) {
        transform: translateY(9px) rotate(45deg);
    }
    
    .nav-toggle.active span:nth-child(2) {
        opacity: 0;
    }
    
    .nav-toggle.active span:nth-child(3) {
        transform: translateY(-9px) rotate(-45deg);
    }
    
    .nav-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: var(--bg-medium);
        flex-direction: column;
        padding: 20px;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
        box-shadow: var(--box-shadow);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all var(--transition-fast) ease;
        z-index: 100;
    }
    
    .nav-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .nav-item {
        margin: 10px 0;
    }
}
</style> 