/**
 * 统一导航栏JavaScript
 * 实现导航栏的交互功能
 */

document.addEventListener('DOMContentLoaded', function() {
    // 导航栏滚动效果
    const header = document.getElementById('header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // 初始检查
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        }
    }
    
    // 导航栏切换（移动端）
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
        
        // 点击导航链接后关闭菜单
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // 如果是子菜单的父项，不要关闭菜单
                if (link.parentElement.classList.contains('nav-item-has-children') && 
                    window.innerWidth <= 992) {
                    e.preventDefault();
                    const parent = link.parentElement;
                    parent.classList.toggle('active');
                    return;
                }
                
                // 如果不是子菜单项，关闭菜单
                if (!link.closest('.submenu') && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                }
            });
        });
    }
    
    // 用户下拉菜单
    const userDropdownBtn = document.querySelector('.user-dropdown-btn');
    const userDropdownContent = document.querySelector('.user-dropdown-content');
    
    if (userDropdownBtn && userDropdownContent) {
        userDropdownBtn.addEventListener('click', function(event) {
            event.stopPropagation();
            userDropdownContent.classList.toggle('show');
        });
        
        // 点击其他地方关闭下拉菜单
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.user-dropdown')) {
                if (userDropdownContent.classList.contains('show')) {
                    userDropdownContent.classList.remove('show');
                }
            }
        });
    }
    
    // 当前页面高亮
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        
        // 首页特殊处理
        if (href === '/' && (currentPath === '/' || currentPath === '/index.php' || currentPath === '/index.html')) {
            link.classList.add('active');
        } 
        // 其他页面
        else if (href !== '/' && currentPath.startsWith(href)) {
            link.classList.add('active');
            
            // 如果是子菜单项，也高亮父菜单
            const submenu = link.closest('.submenu');
            if (submenu) {
                const parentItem = submenu.parentElement;
                if (parentItem) {
                    const parentLink = parentItem.querySelector('.nav-link');
                    if (parentLink) {
                        parentLink.classList.add('active');
                    }
                }
            }
        }
    });
    
    // 检查用户登录状态并更新导航栏
    checkLoginStatus();
});

/**
 * 检查用户登录状态并更新导航栏
 */
function checkLoginStatus() {
    // 尝试从localStorage或cookie获取登录状态
    const isLoggedIn = localStorage.getItem('user_logged_in') === 'true' || document.cookie.includes('user_logged_in=true');
    const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
    
    // 获取导航栏元素
    const navActions = document.querySelector('.nav-actions');
    
    if (!navActions) return;
    
    // 根据登录状态更新导航栏
    if (isLoggedIn && userInfo.name) {
        // 用户已登录，显示用户菜单
        navActions.innerHTML = `
            <div class="user-dropdown">
                <div class="user-dropdown-btn">
                    <img src="${userInfo.avatar || '/assets/images/default-avatar.png'}" alt="用户头像" class="user-avatar">
                    <span>${userInfo.name}</span>
                </div>
                <div class="user-dropdown-content">
                    <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> 控制台</a>
                    <a href="/profile"><i class="fas fa-user"></i> 个人资料</a>
                    <a href="/settings"><i class="fas fa-cog"></i> 设置</a>
                    <hr>
                    <a href="/logout" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
                </div>
            </div>
        `;
        
        // 添加退出登录功能
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // 清除登录状态
                localStorage.removeItem('user_logged_in');
                localStorage.removeItem('user_info');
                // 设置cookie过期
                document.cookie = 'user_logged_in=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                // 重定向到首页
                window.location.href = '/';
            });
        }
        
        // 重新绑定用户下拉菜单事件
        const userDropdownBtn = document.querySelector('.user-dropdown-btn');
        const userDropdownContent = document.querySelector('.user-dropdown-content');
        
        if (userDropdownBtn && userDropdownContent) {
            userDropdownBtn.addEventListener('click', function(event) {
                event.stopPropagation();
                userDropdownContent.classList.toggle('show');
            });
        }
    } else {
        // 用户未登录，显示登录和注册按钮
        navActions.innerHTML = `
            <a href="/login" class="btn">登录</a>
            <a href="/register" class="btn btn-outline">注册</a>
        `;
    }
} 