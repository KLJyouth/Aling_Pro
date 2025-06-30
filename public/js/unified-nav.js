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
            link.addEventListener('click', function() {
                if (navMenu.classList.contains('active')) {
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
        }
    });
}); 