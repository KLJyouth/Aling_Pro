try {
// 通知系统
const notifications = {
    show(message, type = 'info', details = '') {
        const container = document.createElement('div');
        container.className = `fixed top-4 right-4 z-[9999] max-w-md p-4 glass-card ${
            type === 'error' ? 'border-red-500' : 'border-tech-blue'
        }`;
        container.setAttribute('role', 'alert');
        const iconClass = type === 'success' ? 'fa-check-circle text-green-500' :
                      type === 'error' ? 'fa-exclamation-circle text-red-500' :
                      type === 'warning' ? 'fa-exclamation-triangle text-yellow-500' :
                        'fa-info-circle text-tech-blue';
        
        container.innerHTML = `
            <div class="flex items-start gap-3">
                <i class="fas ${iconClass} mt-1"></i>
                <div>
                    <h3 class="font-heading text-lg">${this.getTitle(type)}</h3>
                    <p class="text-gray-300 text-sm">${message}</p>
                    ${details ? `<p class="text-gray-400 text-xs mt-1">${details}</p>` : ''}
                </div>
                <button class="ml-auto mt-1" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
          document.body.appendChild(container);
        
        // 添加进入动画
        requestAnimationFrame(() => {
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        });
        
        // 3秒后自动消失
        setTimeout(() => {
            container.style.opacity = '0';
            container.style.transform = 'translateY(-20px)';
            container.style.transition = 'all 0.3s ease';
            setTimeout(() => container.remove(), 300);
        }, 3000);
    },

    getTitle(type) {
        switch(type) {
            case 'success': return '成功';
            case 'error': return '错误';
            case 'warning': return '警告';
            default: return '提示';
        }
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'error');
    },

    warning(message) {
        this.show(message, 'warning');
    },

    info(message) {
        this.show(message, 'info');
    }
};

// 全局通知函数
function createNotification(title, message, type = 'info') {
    const container = document.createElement('div');
    container.className = `fixed top-4 right-4 z-[9999] max-w-md glass-card ${
        type === 'error' ? 'border-red-500' : 'border-tech-blue'
    }`;
    container.setAttribute('role', 'alert');
    
    const iconClass = type === 'success' ? 'fa-check-circle text-green-500' :
                    type === 'error' ? 'fa-exclamation-circle text-red-500' :
                    type === 'warning' ? 'fa-exclamation-triangle text-yellow-500' :
                    'fa-info-circle text-tech-blue';
    
    container.innerHTML = `
        <div class="p-4">
            <div class="flex items-start gap-3">
                <i class="fas ${iconClass} mt-1"></i>
                <div>
                    <h3 class="font-heading text-lg">${title}</h3>
                    <p class="text-gray-300 text-sm">${message}</p>
                </div>
                <button class="ml-auto mt-1" onclick="this.parentElement.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(container);
    
    // 自动关闭通知
    setTimeout(() => {
        if (document.body.contains(container)) {
            container.classList.add('opacity-0');
            setTimeout(() => container.remove(), 300);
        }    }, 5000);
}

// 定义统一的全局通知函数
function showNotification(message, type = 'info', details = '') {
    notifications.show(message, type, details);
}

// 将通知函数添加到window对象使其全局可用
window.showNotification = showNotification;
window.notifications = notifications;

} catch (error) {
    console.error(error);
    // 处理错误
}
