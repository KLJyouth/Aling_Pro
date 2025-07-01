/**
 * AlingAi Pro - IT运维中心脚本
 */

document.addEventListener('DOMContentLoaded', function() {
    // 侧边栏切换
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (sidebar.classList.contains('show') || window.getComputedStyle(sidebar).marginLeft === '0px') {
                sidebar.classList.remove('show');
                if (window.innerWidth > 768) {
                    mainContent.style.marginLeft = '0';
                }
            } else {
                sidebar.classList.add('show');
                if (window.innerWidth > 768) {
                    mainContent.style.marginLeft = '240px';
                }
            }
        });
    }
    
    // 自动关闭消息提示
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // 折叠菜单激活状态
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    navLinks.forEach(function(link) {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
            
            // 如果是子菜单，展开父菜单
            const parentCollapseEl = link.closest('.collapse');
            if (parentCollapseEl) {
                const parentNavItem = parentCollapseEl.previousElementSibling;
                if (parentNavItem && parentNavItem.getAttribute('data-bs-toggle') === 'collapse') {
                    parentNavItem.classList.add('active');
                    const collapse = new bootstrap.Collapse(parentCollapseEl);
                    collapse.show();
                }
            }
        }
    });
    
    // 数据表格排序
    const sortableTables = document.querySelectorAll('.table-sortable');
    sortableTables.forEach(function(table) {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(function(header) {
            header.addEventListener('click', function() {
                const sortKey = this.getAttribute('data-sort');
                const sortDirection = this.getAttribute('data-direction') || 'asc';
                
                // 清除所有表头的排序方向
                headers.forEach(function(h) {
                    h.removeAttribute('data-direction');
                    h.classList.remove('sort-asc', 'sort-desc');
                });
                
                // 设置当前表头的排序方向
                const newDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                this.setAttribute('data-direction', newDirection);
                this.classList.add('sort-' + newDirection);
                
                // 排序表格行
                const rows = Array.from(table.querySelectorAll('tbody tr'));
                rows.sort(function(a, b) {
                    const aValue = a.querySelector(`td:nth-child(${Array.from(headers).indexOf(header) + 1})`).textContent;
                    const bValue = b.querySelector(`td:nth-child(${Array.from(headers).indexOf(header) + 1})`).textContent;
                    
                    if (newDirection === 'asc') {
                        return aValue.localeCompare(bValue);
                    } else {
                        return bValue.localeCompare(aValue);
                    }
                });
                
                // 重新添加排序后的行
                const tbody = table.querySelector('tbody');
                rows.forEach(function(row) {
                    tbody.appendChild(row);
                });
            });
        });
    });
    
    // 确认删除对话框
    const confirmDeleteButtons = document.querySelectorAll('[data-confirm]');
    confirmDeleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // 初始化工具提示
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(function(tooltip) {
        new bootstrap.Tooltip(tooltip);
    });
}); 