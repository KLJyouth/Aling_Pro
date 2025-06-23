/**
 * 通知组件
 * 处理系统通知、消息提示和状态反馈
 */

class NotificationComponent {
    constructor(uiManager) {
        this.uiManager = uiManager;
        this.notifications = new Map();
        this.container = null;
        this.maxNotifications = 5;
        this.defaultDuration = 5000;
        this.init();
    }

    init() {
        this.createContainer();
        this.setupEventListeners();
    }

    createContainer() {
        this.container = document.createElement('div');
        this.container.className = 'notification-container';
        this.container.setAttribute('role', 'alert');
        this.container.setAttribute('aria-live', 'polite');
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
            pointer-events: none;
        `;
        document.body.appendChild(this.container);
    }

    setupEventListeners() {
        // 监听页面可见性变化
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseTimers();
            } else {
                this.resumeTimers();
            }
        });
    }

    show(message, type = 'info', duration = this.defaultDuration, options = {}) {
        const id = this.generateId();
        const notification = this.createNotification(id, message, type, duration, options);
        
        this.notifications.set(id, notification);
        this.container.appendChild(notification.element);
        
        // 限制通知数量
        this.enforceMaxNotifications();
        
        // 动画显示
        requestAnimationFrame(() => {
            notification.element.classList.add('notification-show');
        });

        // 设置自动关闭
        if (duration > 0) {
            notification.timer = setTimeout(() => {
                this.hide(id);
            }, duration);
        }

        // 无障碍支持
        this.announceToScreenReader(message);

        return id;
    }

    createNotification(id, message, type, duration, options) {
        const element = document.createElement('div');
        element.className = `notification notification-${type}`;
        element.setAttribute('data-notification-id', id);
        element.style.cssText = `
            background: var(--notification-bg, #ffffff);
            border: 1px solid var(--notification-border, #e1e5e9);
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            pointer-events: auto;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            min-height: 60px;
            display: flex;
            align-items: center;
            gap: 12px;
        `;

        // 类型样式
        this.applyTypeStyles(element, type);

        // 进度条（如果有持续时间）
        let progressBar = null;
        if (duration > 0) {
            progressBar = document.createElement('div');
            progressBar.className = 'notification-progress';
            progressBar.style.cssText = `
                position: absolute;
                bottom: 0;
                left: 0;
                height: 2px;
                background: currentColor;
                width: 100%;
                transform-origin: left;
                animation: progress-countdown ${duration}ms linear forwards;
            `;
            element.appendChild(progressBar);
        }

        // 图标
        const icon = document.createElement('div');
        icon.className = 'notification-icon';
        icon.innerHTML = this.getIcon(type);
        icon.style.cssText = `
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        `;

        // 内容
        const content = document.createElement('div');
        content.className = 'notification-content';
        content.style.cssText = `
            flex: 1;
            min-width: 0;
        `;

        // 标题（如果有）
        if (options.title) {
            const title = document.createElement('div');
            title.className = 'notification-title';
            title.textContent = options.title;
            title.style.cssText = `
                font-weight: 600;
                font-size: 14px;
                margin-bottom: 4px;
                color: var(--notification-title-color);
            `;
            content.appendChild(title);
        }

        // 消息
        const messageElement = document.createElement('div');
        messageElement.className = 'notification-message';
        messageElement.textContent = message;
        messageElement.style.cssText = `
            font-size: 14px;
            line-height: 1.4;
            color: var(--notification-text-color);
        `;
        content.appendChild(messageElement);

        // 操作按钮（如果有）
        if (options.actions && options.actions.length > 0) {
            const actions = document.createElement('div');
            actions.className = 'notification-actions';
            actions.style.cssText = `
                margin-top: 8px;
                display: flex;
                gap: 8px;
            `;

            options.actions.forEach(action => {
                const button = document.createElement('button');
                button.className = 'notification-action-btn';
                button.textContent = action.text;
                button.style.cssText = `
                    padding: 4px 12px;
                    border: 1px solid currentColor;
                    background: transparent;
                    border-radius: 6px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                `;

                button.addEventListener('click', () => {
                    if (action.handler) {
                        action.handler();
                    }
                    if (action.closeOnClick !== false) {
                        this.hide(id);
                    }
                });

                actions.appendChild(button);
            });

            content.appendChild(actions);
        }

        // 关闭按钮
        const closeButton = document.createElement('button');
        closeButton.className = 'notification-close';
        closeButton.innerHTML = '<i class="fas fa-times"></i>';
        closeButton.setAttribute('aria-label', '关闭通知');
        closeButton.style.cssText = `
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            opacity: 0.6;
            transition: opacity 0.2s ease;
            flex-shrink: 0;
        `;

        closeButton.addEventListener('click', () => {
            this.hide(id);
        });

        closeButton.addEventListener('mouseenter', () => {
            closeButton.style.opacity = '1';
        });

        closeButton.addEventListener('mouseleave', () => {
            closeButton.style.opacity = '0.6';
        });

        // 组装元素
        element.appendChild(icon);
        element.appendChild(content);
        element.appendChild(closeButton);

        // 悬停暂停
        element.addEventListener('mouseenter', () => {
            if (this.notifications.has(id)) {
                this.pauseTimer(id);
            }
        });

        element.addEventListener('mouseleave', () => {
            if (this.notifications.has(id)) {
                this.resumeTimer(id, duration);
            }
        });

        return {
            id,
            element,
            type,
            message,
            duration,
            timer: null,
            progressBar,
            isPaused: false,
            remainingTime: duration
        };
    }

    applyTypeStyles(element, type) {
        const styles = {
            success: {
                '--notification-bg': '#d1fae5',
                '--notification-border': '#34d399',
                '--notification-title-color': '#065f46',
                '--notification-text-color': '#047857',
                'color': '#10b981'
            },
            error: {
                '--notification-bg': '#fee2e2',
                '--notification-border': '#f87171',
                '--notification-title-color': '#991b1b',
                '--notification-text-color': '#dc2626',
                'color': '#ef4444'
            },
            warning: {
                '--notification-bg': '#fef3c7',
                '--notification-border': '#fbbf24',
                '--notification-title-color': '#92400e',
                '--notification-text-color': '#d97706',
                'color': '#f59e0b'
            },
            info: {
                '--notification-bg': '#dbeafe',
                '--notification-border': '#60a5fa',
                '--notification-title-color': '#1e40af',
                '--notification-text-color': '#2563eb',
                'color': '#3b82f6'
            }
        };

        const typeStyles = styles[type] || styles.info;
        Object.entries(typeStyles).forEach(([property, value]) => {
            element.style.setProperty(property, value);
        });
    }

    getIcon(type) {
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-exclamation-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-info-circle"></i>'
        };
        return icons[type] || icons.info;
    }

    hide(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;

        // 清除定时器
        if (notification.timer) {
            clearTimeout(notification.timer);
        }

        // 隐藏动画
        notification.element.classList.remove('notification-show');
        notification.element.style.transform = 'translateX(100%)';
        notification.element.style.opacity = '0';

        // 移除元素
        setTimeout(() => {
            if (notification.element.parentNode) {
                notification.element.parentNode.removeChild(notification.element);
            }
            this.notifications.delete(id);
        }, 300);
    }

    hideAll() {
        this.notifications.forEach((notification, id) => {
            this.hide(id);
        });
    }

    pauseTimer(id) {
        const notification = this.notifications.get(id);
        if (!notification || notification.isPaused) return;

        if (notification.timer) {
            clearTimeout(notification.timer);
            notification.isPaused = true;
        }

        // 暂停进度条动画
        if (notification.progressBar) {
            notification.progressBar.style.animationPlayState = 'paused';
        }
    }

    resumeTimer(id, originalDuration) {
        const notification = this.notifications.get(id);
        if (!notification || !notification.isPaused) return;

        notification.isPaused = false;
        
        // 恢复进度条动画
        if (notification.progressBar) {
            notification.progressBar.style.animationPlayState = 'running';
        }

        // 重新设置定时器（简化版本，实际应该计算剩余时间）
        if (notification.remainingTime > 0) {
            notification.timer = setTimeout(() => {
                this.hide(id);
            }, notification.remainingTime);
        }
    }

    pauseTimers() {
        this.notifications.forEach((notification, id) => {
            this.pauseTimer(id);
        });
    }

    resumeTimers() {
        this.notifications.forEach((notification, id) => {
            this.resumeTimer(id, notification.duration);
        });
    }

    enforceMaxNotifications() {
        if (this.notifications.size <= this.maxNotifications) return;

        // 找到最旧的通知并关闭
        const oldestId = [...this.notifications.keys()][0];
        this.hide(oldestId);
    }

    // 快捷方法
    success(message, duration, options) {
        return this.show(message, 'success', duration, options);
    }

    error(message, duration, options) {
        return this.show(message, 'error', duration, options);
    }

    warning(message, duration, options) {
        return this.show(message, 'warning', duration, options);
    }

    info(message, duration, options) {
        return this.show(message, 'info', duration, options);
    }

    // 特殊通知类型
    confirm(message, onConfirm, onCancel) {
        return this.show(message, 'warning', 0, {
            title: '确认操作',
            actions: [
                {
                    text: '确认',
                    handler: onConfirm,
                    closeOnClick: true
                },
                {
                    text: '取消',
                    handler: onCancel,
                    closeOnClick: true
                }
            ]
        });
    }

    loading(message) {
        const loadingIcon = '<i class="fas fa-spinner fa-spin"></i>';
        return this.show(message, 'info', 0, {
            title: '加载中...',
            customIcon: loadingIcon
        });
    }

    generateId() {
        return `notification-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }

    announceToScreenReader(message) {
        if (this.uiManager && this.uiManager.announceToScreenReader) {
            this.uiManager.announceToScreenReader(message);
        }
    }

    handleResize(width, height) {
        // 在移动设备上调整通知位置
        if (width < 768) {
            this.container.style.cssText = `
                position: fixed;
                top: 10px;
                left: 10px;
                right: 10px;
                z-index: 2000;
                display: flex;
                flex-direction: column;
                gap: 8px;
                max-width: none;
                pointer-events: none;
            `;
        } else {
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 2000;
                display: flex;
                flex-direction: column;
                gap: 12px;
                max-width: 400px;
                pointer-events: none;
            `;
        }
    }

    destroy() {
        this.hideAll();
        if (this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
    }
}

// 添加CSS动画
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification-show {
        transform: translateX(0) !important;
        opacity: 1 !important;
    }

    @keyframes progress-countdown {
        from {
            transform: scaleX(1);
        }
        to {
            transform: scaleX(0);
        }
    }

    .notification:hover .notification-progress {
        animation-play-state: paused;
    }

    .notification-action-btn:hover {
        background: currentColor !important;
        color: white !important;
    }

    @media (max-width: 768px) {
        .notification {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        .notification-container {
            left: 10px !important;
            right: 10px !important;
            top: 10px !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .notification {
            transition: none !important;
        }
        
        .notification-progress {
            animation: none !important;
        }
    }
`;

document.head.appendChild(notificationStyles);

window.NotificationComponent = NotificationComponent;
