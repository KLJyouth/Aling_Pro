/**
 * AlingAi Pro - 首页功能修复
 * 修复联系表单、主题切换和AI助手聊天功能
 * 
 * @version 1.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

class HomepageFixes {
    constructor() {
        this.isInitialized = false;
        this.currentTheme = 'dark'; // 默认深色主题
        this.chatSystem = null;
        
        this.init();
    }

    async init() {
        
        
        try {
            await this.initThemeSystem();
            await this.initContactForm();
            await this.initAIAssistant();
            
            this.isInitialized = true;
            
        } catch (error) {
            console.error('❌ 首页功能修复失败:', error);
        }
    }

    /**
     * 初始化主题系统
     */
    async initThemeSystem() {
        
        
        // 从localStorage加载保存的主题
        const savedTheme = localStorage.getItem('alingai-theme') || 'dark';
        this.currentTheme = savedTheme;
        
        // 应用主题
        this.applyTheme(this.currentTheme);
        
        // 创建主题切换按钮
        this.createThemeToggle();
        
        // 监听系统主题变化
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('alingai-theme')) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }

    /**
     * 创建主题切换按钮
     */
    createThemeToggle() {
        // 查找导航栏或合适的位置
        const nav = document.querySelector('nav') || document.querySelector('header');
        if (!nav) return;

        // 创建主题切换容器
        const themeToggleContainer = document.createElement('div');
        themeToggleContainer.className = 'theme-toggle-container';
        themeToggleContainer.innerHTML = `
            <button id="themeToggle" class="theme-toggle-btn" title="切换主题">
                <i class="fas fa-sun theme-icon light-icon"></i>
                <i class="fas fa-moon theme-icon dark-icon"></i>
            </button>
        `;

        // 添加样式
        const style = document.createElement('style');
        style.textContent = `
            .theme-toggle-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
            }
            
            .theme-toggle-btn {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                border: 2px solid var(--border-color, rgba(255, 255, 255, 0.2));
                background: rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(10px);
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .theme-toggle-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
            }
            
            .theme-icon {
                position: absolute;
                font-size: 20px;
                transition: all 0.3s ease;
            }
            
            .light-icon {
                color: #fbbf24;
                opacity: 0;
                transform: rotate(180deg) scale(0);
            }
            
            .dark-icon {
                color: #e5e7eb;
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }
            
            /* 主题状态切换 */
            body.light-theme .light-icon {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }
            
            body.light-theme .dark-icon {
                opacity: 0;
                transform: rotate(-180deg) scale(0);
            }
            
            /* CSS变量定义 */
            :root {
                --bg-primary: #0a0016;
                --bg-secondary: rgba(10, 0, 22, 0.9);
                --text-primary: #ffffff;
                --text-secondary: #e5e7eb;
                --border-color: rgba(255, 255, 255, 0.2);
                --accent-color: #667eea;
            }
            
            body.light-theme {
                --bg-primary: #ffffff;
                --bg-secondary: rgba(255, 255, 255, 0.9);
                --text-primary: #1f2937;
                --text-secondary: #4b5563;
                --border-color: rgba(0, 0, 0, 0.1);
                --accent-color: #3b82f6;
            }
            
            /* 应用主题变量到元素 */
            body {
                background-color: var(--bg-primary);
                color: var(--text-primary);
                transition: background-color 0.3s ease, color 0.3s ease;
            }
            
            .glass-card {
                background: var(--bg-secondary);
                border-color: var(--border-color);
            }
            
            .text-gray-300 {
                color: var(--text-secondary);
            }
        `;
        document.head.appendChild(style);

        // 添加到页面
        document.body.appendChild(themeToggleContainer);

        // 绑定事件
        document.getElementById('themeToggle').addEventListener('click', () => {
            this.toggleTheme();
        });
    }

    /**
     * 切换主题
     */
    toggleTheme() {
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    /**
     * 设置主题
     */
    setTheme(theme) {
        this.currentTheme = theme;
        this.applyTheme(theme);
        localStorage.setItem('alingai-theme', theme);
    }

    /**
     * 应用主题
     */
    applyTheme(theme) {
        document.body.classList.remove('light-theme', 'dark-theme');
        document.body.classList.add(`${theme}-theme`);
        
        // 更新meta标签
        let themeColorMeta = document.querySelector('meta[name="theme-color"]');
        if (!themeColorMeta) {
            themeColorMeta = document.createElement('meta');
            themeColorMeta.name = 'theme-color';
            document.head.appendChild(themeColorMeta);
        }
        themeColorMeta.content = theme === 'dark' ? '#0a0016' : '#ffffff';
    }

    /**
     * 初始化联系表单
     */
    async initContactForm() {
        
        
        const contactForm = document.getElementById('contactForm');
        if (!contactForm) {
            console.warn('未找到联系表单');
            return;
        }

        // 移除原有的事件监听器
        contactForm.replaceWith(contactForm.cloneNode(true));
        const newContactForm = document.getElementById('contactForm');

        // 添加新的事件监听器
        newContactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleContactFormSubmit(e);
        });

        // 添加实时验证
        this.addFormValidation(newContactForm);
    }

    /**
     * 处理联系表单提交
     */
    async handleContactFormSubmit(event) {
        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        try {
            // 显示加载状态
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 发送中...';

            // 获取表单数据
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // 验证数据
            if (!this.validateContactForm(data)) {
                throw new Error('请填写所有必填字段');
            }

            // 发送到后端API
            const response = await fetch(API_ENDPOINTS.CONTACT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('success', result.message);
                form.reset();
                
                // 添加成功动画
                this.addSuccessAnimation(form);
            } else {
                throw new Error(result.message || '发送失败');
            }

        } catch (error) {
            console.error('联系表单提交错误:', error);
            this.showNotification('error', error.message || '发送失败，请稍后重试');
        } finally {
            // 恢复按钮状态
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    /**
     * 验证联系表单
     */
    validateContactForm(data) {
        const required = ['name', 'email', 'message'];
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        for (let field of required) {
            if (!data[field] || data[field].trim() === '') {
                this.showFieldError(field, '此字段为必填项');
                return false;
            }
        }

        if (!emailRegex.test(data.email)) {
            this.showFieldError('email', '请输入有效的邮箱地址');
            return false;
        }

        return true;
    }

    /**
     * 添加表单验证
     */
    addFormValidation(form) {
        const inputs = form.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });

            input.addEventListener('input', () => {
                this.clearFieldError(input.name);
            });
        });
    }

    /**
     * 验证单个字段
     */
    validateField(input) {
        const value = input.value.trim();
        const name = input.name;

        if (input.required && !value) {
            this.showFieldError(name, '此字段为必填项');
            return false;
        }

        if (name === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(name, '请输入有效的邮箱地址');
                return false;
            }
        }

        this.clearFieldError(name);
        return true;
    }

    /**
     * 显示字段错误
     */
    showFieldError(fieldName, message) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        // 移除旧的错误信息
        this.clearFieldError(fieldName);

        // 添加错误样式
        field.classList.add('error');
        
        // 创建错误信息元素
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        
        // 插入错误信息
        field.parentNode.appendChild(errorDiv);

        // 添加错误样式
        const style = document.createElement('style');
        style.textContent = `
            .field-error {
                color: #ef4444;
                font-size: 12px;
                margin-top: 4px;
                animation: slideDown 0.3s ease;
            }
            
            input.error, textarea.error {
                border-color: #ef4444 !important;
                box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        
        if (!document.querySelector('.field-error-styles')) {
            style.className = 'field-error-styles';
            document.head.appendChild(style);
        }
    }

    /**
     * 清除字段错误
     */
    clearFieldError(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        field.classList.remove('error');
        
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    /**
     * 添加成功动画
     */
    addSuccessAnimation(form) {
        form.style.transform = 'scale(0.95)';
        form.style.transition = 'transform 0.2s ease';
        
        setTimeout(() => {
            form.style.transform = 'scale(1)';
        }, 200);
    }

    /**
     * 初始化AI助手
     */
    async initAIAssistant() {
        
        
        try {
            // 检查是否已有聊天系统
            if (window.ChatSystem) {
                this.chatSystem = new window.ChatSystem({
                    apiEndpoint: API_ENDPOINTS.CHAT,
                    enableQuantumEffects: true,
                    autoSave: true
                });
                
                
            } else {
                console.warn('⚠️ ChatSystem类未找到，创建基础聊天功能');
                await this.createBasicChatSystem();
            }

            // 添加聊天按钮
            this.createChatButton();
            
        } catch (error) {
            console.error('❌ AI助手初始化失败:', error);
        }
    }

    /**
     * 创建基础聊天系统
     */
    async createBasicChatSystem() {
        this.chatSystem = {
            isOpen: false,
            
            toggle: () => {
                this.chatSystem.isOpen = !this.chatSystem.isOpen;
                const chatModal = document.getElementById('chatModal');
                if (chatModal) {
                    chatModal.style.display = this.chatSystem.isOpen ? 'flex' : 'none';
                }
            },
            
            sendMessage: async (message) => {
                try {
                    // 显示用户消息
                    this.addChatMessage('user', message);
                    
                    // 发送到API
                    const response = await fetch(API_ENDPOINTS.CHAT, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ message })
                    });
                    
                    const result = await response.json();
                    
                    // 显示AI回复
                    this.addChatMessage('ai', result.response || '抱歉，我现在无法回答您的问题。');
                    
                } catch (error) {
                    console.error('发送消息失败:', error);
                    this.addChatMessage('ai', '抱歉，发生了错误，请稍后重试。');
                }
            }
        };
    }

    /**
     * 创建聊天按钮
     */
    createChatButton() {
        // 创建聊天按钮
        const chatButton = document.createElement('div');
        chatButton.id = 'chatButton';
        chatButton.innerHTML = `
            <button class="chat-toggle-btn" title="AI智能助手">
                <i class="fas fa-robot"></i>
                <span class="chat-badge">AI</span>
            </button>
        `;

        // 创建聊天模态框
        const chatModal = document.createElement('div');
        chatModal.id = 'chatModal';
        chatModal.innerHTML = `
            <div class="chat-modal-content">
                <div class="chat-header">
                    <h3><i class="fas fa-robot"></i> AI智能助手</h3>
                    <button class="chat-close-btn">&times;</button>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-message ai-message">
                        <div class="message-content">
                            您好！我是AlingAi Pro的AI助手，有什么可以帮助您的吗？
                        </div>
                    </div>
                </div>
                <div class="chat-input-container">
                    <input type="text" id="chatInput" placeholder="输入您的问题..." />
                    <button id="chatSendBtn"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        `;

        // 添加样式
        const chatStyles = document.createElement('style');
        chatStyles.textContent = `
            #chatButton {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
            }
            
            .chat-toggle-btn {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                transition: all 0.3s ease;
            }
            
            .chat-toggle-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
            }
            
            .chat-toggle-btn i {
                color: white;
                font-size: 24px;
            }
            
            .chat-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #ef4444;
                color: white;
                border-radius: 10px;
                padding: 2px 6px;
                font-size: 10px;
                font-weight: bold;
            }
            
            #chatModal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(5px);
                z-index: 10000;
                display: none;
                align-items: center;
                justify-content: center;
            }
            
            .chat-modal-content {
                width: 400px;
                height: 500px;
                background: var(--bg-secondary, rgba(10, 0, 22, 0.95));
                border-radius: 15px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(20px);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            }
            
            .chat-header {
                padding: 15px 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .chat-header h3 {
                margin: 0;
                font-size: 16px;
            }
            
            .chat-close-btn {
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: background 0.3s ease;
            }
            
            .chat-close-btn:hover {
                background: rgba(255, 255, 255, 0.2);
            }
            
            .chat-messages {
                flex: 1;
                padding: 20px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .chat-message {
                max-width: 80%;
                word-wrap: break-word;
            }
            
            .user-message {
                align-self: flex-end;
            }
            
            .ai-message {
                align-self: flex-start;
            }
            
            .message-content {
                padding: 12px 16px;
                border-radius: 15px;
                line-height: 1.4;
            }
            
            .user-message .message-content {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-bottom-right-radius: 5px;
            }
            
            .ai-message .message-content {
                background: rgba(255, 255, 255, 0.1);
                color: var(--text-primary, white);
                border-bottom-left-radius: 5px;
            }
            
            .chat-input-container {
                padding: 15px 20px;
                background: rgba(255, 255, 255, 0.05);
                display: flex;
                gap: 10px;
                align-items: center;
            }
            
            #chatInput {
                flex: 1;
                padding: 12px 16px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 25px;
                background: rgba(255, 255, 255, 0.1);
                color: var(--text-primary, white);
                outline: none;
                transition: all 0.3s ease;
            }
            
            #chatInput:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            }
            
            #chatSendBtn {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }
            
            #chatSendBtn:hover {
                transform: scale(1.1);
            }
            
            /* 移动端适配 */
            @media (max-width: 480px) {
                .chat-modal-content {
                    width: 90%;
                    height: 80%;
                }
            }
        `;
        document.head.appendChild(chatStyles);

        // 添加到页面
        document.body.appendChild(chatButton);
        document.body.appendChild(chatModal);

        // 绑定事件
        document.querySelector('.chat-toggle-btn').addEventListener('click', () => {
            this.chatSystem.toggle();
        });

        document.querySelector('.chat-close-btn').addEventListener('click', () => {
            this.chatSystem.toggle();
        });

        // 点击模态框外部关闭
        chatModal.addEventListener('click', (e) => {
            if (e.target === chatModal) {
                this.chatSystem.toggle();
            }
        });

        // 输入框事件
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('chatSendBtn');

        sendBtn.addEventListener('click', () => {
            this.sendChatMessage();
        });

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendChatMessage();
            }
        });
    }

    /**
     * 发送聊天消息
     */
    async sendChatMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        input.value = '';
        
        if (this.chatSystem && this.chatSystem.sendMessage) {
            await this.chatSystem.sendMessage(message);
        }
    }

    /**
     * 添加聊天消息
     */
    addChatMessage(type, content) {
        const messagesContainer = document.getElementById('chatMessages');
        if (!messagesContainer) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${type}-message`;
        messageDiv.innerHTML = `
            <div class="message-content">
                ${content}
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    /**
     * 显示通知
     */
    showNotification(type, message) {
        // 移除现有通知
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        // 添加样式
        const notificationStyles = document.createElement('style');
        notificationStyles.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10001;
                min-width: 300px;
                max-width: 500px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(20px);
                animation: slideInRight 0.3s ease;
            }
            
            .notification-success {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                border: 1px solid rgba(16, 185, 129, 0.3);
            }
            
            .notification-error {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                border: 1px solid rgba(239, 68, 68, 0.3);
            }
            
            .notification-content {
                padding: 15px 20px;
                display: flex;
                align-items: center;
                gap: 10px;
                color: white;
            }
            
            .notification-content i {
                font-size: 18px;
                flex-shrink: 0;
            }
            
            .notification-content span {
                flex: 1;
                font-size: 14px;
                line-height: 1.4;
            }
            
            .notification-close {
                background: none;
                border: none;
                color: white;
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: background 0.3s ease;
                flex-shrink: 0;
            }
            
            .notification-close:hover {
                background: rgba(255, 255, 255, 0.2);
            }
            
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
        `;
        
        if (!document.querySelector('.notification-styles')) {
            notificationStyles.className = 'notification-styles';
            document.head.appendChild(notificationStyles);
        }

        // 添加到页面
        document.body.appendChild(notification);

        // 绑定关闭事件
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.hideNotification(notification);
        });

        // 自动消失
        setTimeout(() => {
            if (notification.parentNode) {
                this.hideNotification(notification);
            }
        }, 5000);
    }

    /**
     * 隐藏通知
     */
    hideNotification(notification) {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }
}

// 在页面加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    window.homepageFixes = new HomepageFixes();
});

// 确保在现有代码执行后也能初始化
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.homepageFixes) {
            window.homepageFixes = new HomepageFixes();
        }
    });
} else {
    if (!window.homepageFixes) {
        window.homepageFixes = new HomepageFixes();
    }
}
