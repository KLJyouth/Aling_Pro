/**
 * 聊天组件
 * 提供智能AI聊天功能和用户交互界面
 */

class ChatComponent {
    constructor(uiManager) {
        this.uiManager = uiManager;
        this.isVisible = false;
        this.isMinimized = false;
        this.messages = [];
        this.isTyping = false;
        this.socket = null;
        this.container = null;
        this.messagesContainer = null;
        this.inputElement = null;
        this.toggleButton = null;
        this.isAuthenticated = false;
        this.currentSession = null;
        this.apiEndpoints = {
            chat: '/api/v1/chat/messages',
            sessions: '/api/v1/chat/sessions',
            auth: '/api/v1/auth/check'
        };
        this.init();
    }

    init() {
        this.createToggleButton();
        this.createChatInterface();
        this.setupEventListeners();
        this.loadChatHistory();
    }

    createToggleButton() {
        this.toggleButton = document.createElement('button');
        this.toggleButton.className = 'chat-toggle-btn';
        this.toggleButton.innerHTML = `
            <div class="chat-icon">
                <i class="fas fa-comments"></i>
                <div class="chat-notification-badge" style="display: none;">
                    <span class="badge-count">0</span>
                </div>
            </div>
            <div class="chat-status-indicator">
                <div class="status-dot online"></div>
                <span class="status-text">AI助手在线</span>
            </div>
        `;
        
        this.toggleButton.setAttribute('aria-label', '打开AI聊天窗口');
        this.toggleButton.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            cursor: pointer;
            z-index: 1500;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 0;
        `;

        // 悬停效果
        this.toggleButton.addEventListener('mouseenter', () => {
            this.toggleButton.style.transform = 'scale(1.1)';
            this.toggleButton.style.boxShadow = '0 12px 32px rgba(102, 126, 234, 0.6)';
        });

        this.toggleButton.addEventListener('mouseleave', () => {
            this.toggleButton.style.transform = 'scale(1)';
            this.toggleButton.style.boxShadow = '0 8px 24px rgba(102, 126, 234, 0.4)';
        });

        this.toggleButton.addEventListener('click', () => {
            this.toggle();
        });

        document.body.appendChild(this.toggleButton);
    }

    createChatInterface() {
        this.container = document.createElement('div');
        this.container.className = 'chat-container';
        this.container.style.cssText = `
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 380px;
            height: 500px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.1);
            z-index: 1400;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: translateY(100%) scale(0.8);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
        `;

        // 聊天头部
        const header = document.createElement('div');
        header.className = 'chat-header';
        header.innerHTML = `
            <div class="chat-avatar">
                <div class="avatar-image">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="status-indicator online"></div>
            </div>
            <div class="chat-info">
                <h3 class="chat-title">AI智能助手</h3>
                <p class="chat-subtitle">我是您的专属AI助手，有什么可以帮助您的吗？</p>
            </div>
            <div class="chat-actions">
                <button class="chat-minimize-btn" aria-label="最小化聊天窗口">
                    <i class="fas fa-minus"></i>
                </button>
                <button class="chat-close-btn" aria-label="关闭聊天窗口">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        header.style.cssText = `
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        `;

        // 消息容器
        this.messagesContainer = document.createElement('div');
        this.messagesContainer.className = 'chat-messages';
        this.messagesContainer.style.cssText = `
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 16px;
        `;

        // 输入区域
        const inputArea = document.createElement('div');
        inputArea.className = 'chat-input-area';
        inputArea.innerHTML = `
            <div class="chat-input-container">
                <input 
                    type="text" 
                    class="chat-input" 
                    placeholder="输入您的问题..."
                    maxlength="500"
                >
                <button class="chat-send-btn" aria-label="发送消息">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="chat-typing-indicator" style="display: none;">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="typing-text">AI正在思考...</span>
            </div>
        `;
        inputArea.style.cssText = `
            padding: 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
            flex-shrink: 0;
        `;

        this.inputElement = inputArea.querySelector('.chat-input');
        
        // 组装聊天界面
        this.container.appendChild(header);
        this.container.appendChild(this.messagesContainer);
        this.container.appendChild(inputArea);
        
        document.body.appendChild(this.container);
        
        // 添加欢迎消息
        this.addMessage('欢迎使用珑凌科技AI智能助手！我可以帮助您了解我们的产品和服务。请问有什么可以为您效劳的吗？', 'bot', true);
    }

    setupEventListeners() {
        // 最小化按钮
        const minimizeBtn = this.container.querySelector('.chat-minimize-btn');
        minimizeBtn.addEventListener('click', () => {
            this.minimize();
        });

        // 关闭按钮
        const closeBtn = this.container.querySelector('.chat-close-btn');
        closeBtn.addEventListener('click', () => {
            this.hide();
        });

        // 发送按钮
        const sendBtn = this.container.querySelector('.chat-send-btn');
        sendBtn.addEventListener('click', () => {
            this.sendMessage();
        });

        // 输入框事件
        this.inputElement.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        this.inputElement.addEventListener('input', () => {
            this.handleTyping();
        });

        // 点击外部关闭
        document.addEventListener('click', (e) => {
            if (this.isVisible && 
                !this.container.contains(e.target) && 
                !this.toggleButton.contains(e.target)) {
                this.hide();
            }
        });

        // 响应式处理
        window.addEventListener('resize', () => {
            this.handleResize();
        });
    }

    toggle() {
        if (this.isVisible) {
            this.hide();
        } else {
            this.show();
        }
    }

    show() {
        if (this.isVisible) return;
        
        this.isVisible = true;
        this.container.style.visibility = 'visible';
        
        requestAnimationFrame(() => {
            this.container.style.transform = 'translateY(0) scale(1)';
            this.container.style.opacity = '1';
        });

        // 聚焦输入框
        setTimeout(() => {
            this.inputElement.focus();
        }, 400);

        // 更新按钮状态
        this.toggleButton.innerHTML = `
            <i class="fas fa-times"></i>
        `;

        // 清除通知徽章
        this.clearNotificationBadge();

        // 无障碍支持
        this.uiManager.announceToScreenReader('AI聊天窗口已打开');
    }

    hide() {
        if (!this.isVisible) return;
        
        this.isVisible = false;
        this.container.style.transform = 'translateY(100%) scale(0.8)';
        this.container.style.opacity = '0';
        
        setTimeout(() => {
            this.container.style.visibility = 'hidden';
        }, 400);

        // 恢复按钮状态
        this.toggleButton.innerHTML = `
            <div class="chat-icon">
                <i class="fas fa-comments"></i>
                <div class="chat-notification-badge" style="display: none;">
                    <span class="badge-count">0</span>
                </div>
            </div>
            <div class="chat-status-indicator">
                <div class="status-dot online"></div>
                <span class="status-text">AI助手在线</span>
            </div>
        `;

        // 无障碍支持
        this.uiManager.announceToScreenReader('AI聊天窗口已关闭');
    }

    minimize() {
        this.isMinimized = !this.isMinimized;
        
        if (this.isMinimized) {
            this.container.style.height = '80px';
            this.messagesContainer.style.display = 'none';
            this.container.querySelector('.chat-input-area').style.display = 'none';
        } else {
            this.container.style.height = '500px';
            this.messagesContainer.style.display = 'flex';
            this.container.querySelector('.chat-input-area').style.display = 'block';
        }
    }

    async sendMessage() {
        const message = this.inputElement.value.trim();
        if (!message) return;

        // 添加用户消息
        this.addMessage(message, 'user');
        this.inputElement.value = '';

        // 显示打字指示器
        this.showTypingIndicator();

        try {
            // 发送到AI服务
            const response = await this.sendToAI(message);
            
            // 隐藏打字指示器
            this.hideTypingIndicator();
            
            // 添加AI回复
            this.addMessage(response, 'bot');
            
        } catch (error) {
            this.hideTypingIndicator();
            this.addMessage('抱歉，我现在遇到了一些技术问题。请稍后再试，或者联系我们的客服团队。', 'bot');
            console.error('Chat error:', error);
        }
    }    async sendToAI(message) {
        try {
            // 检查认证状态
            await this.checkAuthentication();
            
            const requestData = {
                message: message,
                conversation_id: this.currentSession?.id || null
            };

            const response = await fetch(this.apiEndpoints.chat, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.success) {
                // 更新当前会话信息
                if (result.data && result.data.conversation_id) {
                    this.currentSession = {
                        id: result.data.conversation_id,
                        updated_at: new Date().toISOString()
                    };
                }
                
                return result.data?.response || result.message || '收到回复';
            } else {
                throw new Error(result.message || 'AI服务暂时不可用');
            }
        } catch (error) {
            console.error('AI Service Error:', error);
            throw error;
        }
    }    addMessage(text, sender, isWelcome = false) {
        const messageElement = document.createElement('div');
        messageElement.className = `chat-message chat-message-${sender}`;
        
        const timestamp = new Date().toLocaleTimeString('zh-CN', {
            hour: '2-digit',
            minute: '2-digit'
        });

        if (sender === 'bot') {
            messageElement.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        <p class="message-text">${text}</p>
                        <span class="message-time">${timestamp}</span>
                    </div>
                </div>
            `;
        } else {
            messageElement.innerHTML = `
                <div class="message-content">
                    <div class="message-bubble">
                        <p class="message-text">${text}</p>
                        <span class="message-time">${timestamp}</span>
                    </div>
                </div>
                <div class="message-avatar">
                    <i class="fas fa-user"></i>
                </div>
            `;
        }

        messageElement.style.cssText = `
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 12px;
            ${sender === 'user' ? 'flex-direction: row-reverse;' : ''}
        `;

        this.messagesContainer.appendChild(messageElement);
        
        // 滚动到底部
        this.scrollToBottom();

        // 保存消息到内存
        const messageData = {
            text,
            sender,
            timestamp: new Date().toISOString()
        };
        this.messages.push(messageData);

        // 保存到存储（API或localStorage）
        if (!isWelcome) {
            this.saveChatHistory();
        }

        // 如果窗口未打开，显示通知徽章
        if (!this.isVisible && sender === 'bot' && !isWelcome) {
            this.showNotificationBadge();
        }

        // 动画效果
        requestAnimationFrame(() => {
            messageElement.style.opacity = '0';
            messageElement.style.transform = 'translateY(20px)';
            messageElement.style.transition = 'all 0.3s ease';
            
            requestAnimationFrame(() => {
                messageElement.style.opacity = '1';
                messageElement.style.transform = 'translateY(0)';
            });
        });
    }

    showTypingIndicator() {
        this.isTyping = true;
        const indicator = this.container.querySelector('.chat-typing-indicator');
        indicator.style.display = 'flex';
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        this.isTyping = false;
        const indicator = this.container.querySelector('.chat-typing-indicator');
        indicator.style.display = 'none';
    }

    showNotificationBadge() {
        const badge = this.toggleButton.querySelector('.chat-notification-badge');
        const count = this.toggleButton.querySelector('.badge-count');
        
        let currentCount = parseInt(count.textContent) + 1;
        count.textContent = currentCount > 9 ? '9+' : currentCount;
        badge.style.display = 'block';
    }

    clearNotificationBadge() {
        const badge = this.toggleButton.querySelector('.chat-notification-badge');
        const count = this.toggleButton.querySelector('.badge-count');
        
        count.textContent = '0';
        badge.style.display = 'none';
    }

    scrollToBottom() {
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 100);
    }

    handleTyping() {
        // 可以在这里添加用户正在输入的指示
    }

    handleResize() {
        const width = window.innerWidth;
        
        if (width < 768) {
            // 移动端适配
            this.container.style.cssText = `
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                top: 50%;
                width: 100%;
                height: 50%;
                border-radius: 16px 16px 0 0;
                ${this.container.style.cssText}
            `;
            
            this.toggleButton.style.bottom = '20px';
            this.toggleButton.style.right = '20px';
        } else {
            // 桌面端恢复
            this.container.style.cssText = `
                position: fixed;
                bottom: 100px;
                right: 30px;
                width: 380px;
                height: 500px;
                border-radius: 16px;
                ${this.container.style.cssText}
            `;
            
            this.toggleButton.style.bottom = '30px';
            this.toggleButton.style.right = '30px';
        }
    }    async checkAuthentication() {
        try {
            const response = await fetch(this.apiEndpoints.auth, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                this.isAuthenticated = result.success && result.data?.authenticated;
                if (this.isAuthenticated && result.data?.user) {
                    this.currentUser = result.data.user;
                }
            } else {
                this.isAuthenticated = false;
            }
        } catch (error) {
            console.warn('Authentication check failed:', error);
            this.isAuthenticated = false;
        }
        
        return this.isAuthenticated;
    }

    async loadChatHistory() {
        try {
            await this.checkAuthentication();
            
            if (this.isAuthenticated) {
                // 从API加载聊天历史
                const response = await fetch(this.apiEndpoints.sessions + '?limit=1', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data && result.data.length > 0) {
                        const latestSession = result.data[0];
                        this.currentSession = {
                            id: latestSession.id,
                            updated_at: latestSession.updated_at
                        };
                        
                        // 加载该会话的消息
                        await this.loadSessionMessages(latestSession.id);
                        return;
                    }
                }
            }
            
            // 如果未认证或无历史记录，尝试从localStorage加载
            this.loadLocalChatHistory();
            
        } catch (error) {
            console.warn('Failed to load chat history from API:', error);
            // 降级到localStorage
            this.loadLocalChatHistory();
        }
    }    async loadChatHistory() {
        try {
            const isAuthenticated = await this.checkAuthentication();
            
            if (isAuthenticated) {
                // 认证用户：优先从API加载
                const loaded = await this.loadServerChatHistory();
                if (loaded) return;
            }
            
            // 未认证用户或API失败：从本地存储加载
            this.loadLocalChatHistory();
        } catch (error) {
            console.warn('加载聊天历史失败，使用本地存储:', error);
            this.loadLocalChatHistory();
        }
    }

    async loadServerChatHistory() {
        try {
            const response = await fetch('/api/v1/chat/conversations?limit=1', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data && result.data.length > 0) {
                    const conversation = result.data[0];
                    if (conversation.messages && conversation.messages.length > 0) {
                        // 加载历史消息（最近10条）
                        conversation.messages.slice(-10).forEach(msg => {
                            if (msg.content !== '欢迎使用珑凌科技AI智能助手！我可以帮助您了解我们的产品和服务。请问有什么可以为您效劳的吗？') {
                                this.addMessage(msg.content, msg.sender === 'user' ? 'user' : 'bot', true);
                            }
                        });
                        console.log('✅ 从服务器加载聊天历史');
                        return true;
                    }
                }
            }
        } catch (error) {
            console.warn('从服务器加载聊天历史失败:', error);
        }
        return false;
    }

    loadLocalChatHistory() {
        try {
            const savedMessages = localStorage.getItem('alingai-chat-history');
            if (savedMessages) {
                const messages = JSON.parse(savedMessages);
                messages.slice(-10).forEach(msg => { // 只加载最近10条消息
                    if (msg.sender !== 'bot' || msg.text !== '欢迎使用珑凌科技AI智能助手！我可以帮助您了解我们的产品和服务。请问有什么可以为您效劳的吗？') {
                        this.addMessage(msg.text, msg.sender, true);
                    }
                });
                console.log('📱 从本地存储加载聊天历史');
            }
        } catch (error) {
            console.warn('Failed to load local chat history:', error);
        }
    }

    // 检查用户认证状态
    async checkAuthentication() {
        try {
            const response = await fetch('/api/v1/auth/check', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const result = await response.json();
                return result.success && result.data?.authenticated;
            }
        } catch (error) {
            console.warn('认证检查失败:', error);
        }
        return false;
    }

    async loadSessionMessages(sessionId) {
        try {
            const response = await fetch(`${this.apiEndpoints.chat}?conversation_id=${sessionId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data) {
                    // 清空当前消息显示
                    this.messagesContainer.innerHTML = '';
                    
                    // 添加历史消息
                    result.data.slice(-10).forEach(msg => {
                        this.addMessage(msg.content, msg.sender === 'user' ? 'user' : 'bot', true);
                    });
                }
            }
        } catch (error) {
            console.warn('Failed to load session messages:', error);
        }
    }    async saveChatHistory() {
        try {
            const isAuthenticated = await this.checkAuthentication();
            
            if (isAuthenticated && this.messages.length > 0) {
                // 认证用户：保存到API
                await this.saveChatToServer();
            } else {
                // 未认证用户：保存到localStorage
                this.saveChatToLocal();
            }
        } catch (error) {
            console.warn('保存聊天历史失败，使用本地存储:', error);
            // API失败时回退到本地存储
            this.saveChatToLocal();
        }
    }

    async saveChatToServer() {
        try {
            const response = await fetch('/api/v1/chat/conversations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    title: `聊天会话 - ${new Date().toLocaleString()}`,
                    messages: this.messages,
                    source: 'chat-component'
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log('✅ 聊天历史已保存到服务器');
                    return;
                }
            }
            throw new Error('保存到服务器失败');
        } catch (error) {
            console.error('保存聊天到服务器失败:', error);
            throw error;
        }
    }

    saveChatToLocal() {
        try {
            localStorage.setItem('alingai-chat-history', JSON.stringify(this.messages));
            console.log('📱 聊天历史已保存到本地存储');        } catch (error) {
            console.warn('Failed to save chat history to localStorage:', error);
        }
    }

    async clearHistory() {
        try {
            const isAuthenticated = await this.checkAuthentication();
            
            if (isAuthenticated && this.currentSession?.id) {
                // 为认证用户清除API中的会话历史
                const response = await fetch(`${this.apiEndpoints.sessions}/${this.currentSession.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    this.currentSession = null;
                    console.log('✅ 服务器端聊天历史已清空');
                }
            } else if (isAuthenticated) {
                // 没有特定会话ID，尝试清除所有会话
                await this.clearAllServerHistory();
            }
            
            // 清除localStorage（无论认证状态）
            localStorage.removeItem('alingai-chat-history');
            
            // 清除界面消息
            this.messages = [];
            this.messagesContainer.innerHTML = '';
            this.addMessage('聊天记录已清空。有什么可以帮助您的吗？', 'bot', true);
            
            console.log('✅ 聊天历史已清空');
        } catch (error) {
            console.warn('Failed to clear chat history:', error);
            // 即使API调用失败，也清除本地存储和界面
            localStorage.removeItem('alingai-chat-history');
            this.messages = [];
            this.messagesContainer.innerHTML = '';
            this.addMessage('聊天记录已清空。有什么可以帮助您的吗？', 'bot', true);
        }
    }

    async clearAllServerHistory() {
        try {
            const response = await fetch('/api/v1/chat/conversations', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log('✅ 所有服务器端聊天历史已清空');
                    return true;
                }
            }
        } catch (error) {
            console.warn('清空服务器端聊天历史失败:', error);        }
        return false;
    }

    async destroy() {
        if (this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
        if (this.toggleButton.parentNode) {
            this.toggleButton.parentNode.removeChild(this.toggleButton);
        }
        await this.saveChatHistory();
    }
}

// 添加聊天组件样式
const chatStyles = document.createElement('style');
chatStyles.textContent = `
    .chat-toggle-btn .chat-icon {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .chat-notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        border: 2px solid white;
    }

    .chat-status-indicator {
        position: absolute;
        bottom: -40px;
        right: 0;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .chat-toggle-btn:hover .chat-status-indicator {
        opacity: 1;
        transform: translateY(0);
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    .status-dot.online {
        background: #10b981;
    }

    .status-dot.offline {
        background: #6b7280;
    }

    .chat-header .chat-avatar {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .chat-header .status-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border: 2px solid white;
        border-radius: 50%;
    }

    .chat-info {
        flex: 1;
        min-width: 0;
    }

    .chat-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .chat-subtitle {
        margin: 2px 0 0 0;
        font-size: 12px;
        opacity: 0.8;
    }

    .chat-actions {
        display: flex;
        gap: 8px;
    }

    .chat-actions button {
        width: 32px;
        height: 32px;
        border: none;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    .chat-actions button:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .chat-messages::-webkit-scrollbar {
        width: 4px;
    }

    .chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    .chat-messages::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    .chat-message-bot .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .chat-message-user .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .message-content {
        flex: 1;
        min-width: 0;
    }

    .chat-message-bot .message-bubble {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px 16px 16px 4px;
        padding: 12px 16px;
        max-width: 280px;
    }

    .chat-message-user .message-bubble {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px 16px 4px 16px;
        padding: 12px 16px;
        max-width: 280px;
        margin-left: auto;
    }

    .message-text {
        margin: 0;
        font-size: 14px;
        line-height: 1.4;
        word-wrap: break-word;
    }

    .message-time {
        font-size: 11px;
        opacity: 0.6;
        margin-top: 4px;
        display: block;
    }

    .chat-input-container {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .chat-input {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .chat-input:focus {
        border-color: #667eea;
    }

    .chat-send-btn {
        width: 40px;
        height: 40px;
        border: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .chat-send-btn:hover {
        transform: scale(1.05);
    }

    .chat-typing-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        font-size: 12px;
        color: #64748b;
    }

    .typing-dots {
        display: flex;
        gap: 2px;
    }

    .typing-dots span {
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: #64748b;
        animation: typing-bounce 1.4s infinite ease-in-out;
    }

    .typing-dots span:nth-child(1) {
        animation-delay: -0.32s;
    }

    .typing-dots span:nth-child(2) {
        animation-delay: -0.16s;
    }

    @keyframes typing-bounce {
        0%, 80%, 100% {
            transform: scale(0);
        }
        40% {
            transform: scale(1);
        }
    }

    @media (max-width: 768px) {
        .chat-container {
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            top: 50% !important;
            width: 100% !important;
            height: 50% !important;
            border-radius: 16px 16px 0 0 !important;
        }
        
        .chat-toggle-btn {
            bottom: 20px !important;
            right: 20px !important;
        }
    }
`;

document.head.appendChild(chatStyles);

window.ChatComponent = ChatComponent;
