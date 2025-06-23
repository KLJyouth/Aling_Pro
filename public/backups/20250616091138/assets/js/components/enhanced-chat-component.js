/**
 * AlingAi Pro WebSocket Chat Service
 * 实时聊天WebSocket服务组件
 * 
 * @package AlingAi\Components
 * @author AlingAi Team
 * @version 1.0.0
 */

class WebSocketChatService {
    constructor(options = {}) {
        this.options = {
            url: options.url || `ws://${window.location.host}/ws/chat`,
            reconnectInterval: options.reconnectInterval || 3000,
            maxReconnectAttempts: options.maxReconnectAttempts || 5,
            heartbeatInterval: options.heartbeatInterval || 30000,
            messageQueueSize: options.messageQueueSize || 100,
            ...options
        };
        
        this.ws = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.messageQueue = [];
        this.heartbeatTimer = null;
        this.eventListeners = new Map();
        
        this.init();
    }
    
    init() {
        this.connect();
    }
    
    connect() {
        try {
            this.ws = new WebSocket(this.options.url);
            this.setupEventHandlers();
        } catch (error) {
            console.error('WebSocket连接失败:', error);
            this.handleReconnect();
        }
    }
    
    setupEventHandlers() {
        this.ws.onopen = (event) => {
            console.log('WebSocket连接已建立');
            this.isConnected = true;
            this.reconnectAttempts = 0;
            this.startHeartbeat();
            this.processMessageQueue();
            this.emit('connected', event);
        };
        
        this.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            } catch (error) {
                console.error('消息解析失败:', error);
            }
        };
        
        this.ws.onclose = (event) => {
            console.log('WebSocket连接已关闭:', event.code, event.reason);
            this.isConnected = false;
            this.stopHeartbeat();
            this.emit('disconnected', event);
            
            if (!event.wasClean) {
                this.handleReconnect();
            }
        };
        
        this.ws.onerror = (error) => {
            console.error('WebSocket错误:', error);
            this.emit('error', error);
        };
    }
    
    handleMessage(data) {
        switch (data.type) {
            case 'chat_message':
                this.emit('message', data);
                break;
            case 'user_status':
                this.emit('userStatus', data);
                break;
            case 'typing':
                this.emit('typing', data);
                break;
            case 'pong':
                // 心跳响应
                break;
            default:
                console.warn('未知消息类型:', data.type);
        }
    }
    
    send(type, payload = {}) {
        const message = {
            type,
            payload,
            timestamp: Date.now(),
            id: this.generateMessageId()
        };
        
        if (this.isConnected) {
            try {
                this.ws.send(JSON.stringify(message));
                return true;
            } catch (error) {
                console.error('发送消息失败:', error);
                this.queueMessage(message);
                return false;
            }
        } else {
            this.queueMessage(message);
            return false;
        }
    }
    
    sendChatMessage(content, conversationId = null) {
        return this.send('chat_message', {
            content,
            conversationId,
            userId: this.getUserId()
        });
    }
    
    sendTypingIndicator(isTyping, conversationId = null) {
        return this.send('typing', {
            isTyping,
            conversationId,
            userId: this.getUserId()
        });
    }
    
    updateUserStatus(status) {
        return this.send('user_status', {
            status,
            userId: this.getUserId()
        });
    }
    
    queueMessage(message) {
        this.messageQueue.push(message);
        
        // 限制队列大小
        if (this.messageQueue.length > this.options.messageQueueSize) {
            this.messageQueue.shift();
        }
    }
    
    processMessageQueue() {
        while (this.messageQueue.length > 0 && this.isConnected) {
            const message = this.messageQueue.shift();
            try {
                this.ws.send(JSON.stringify(message));
            } catch (error) {
                console.error('处理队列消息失败:', error);
                this.messageQueue.unshift(message);
                break;
            }
        }
    }
    
    startHeartbeat() {
        this.heartbeatTimer = setInterval(() => {
            if (this.isConnected) {
                this.send('ping');
            }
        }, this.options.heartbeatInterval);
    }
    
    stopHeartbeat() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }
    }
    
    handleReconnect() {
        if (this.reconnectAttempts < this.options.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`尝试重连 (${this.reconnectAttempts}/${this.options.maxReconnectAttempts})`);
            
            setTimeout(() => {
                this.connect();
            }, this.options.reconnectInterval);
        } else {
            console.error('达到最大重连次数，停止重连');
            this.emit('maxReconnectAttemptsReached');
        }
    }
    
    disconnect() {
        this.stopHeartbeat();
        if (this.ws) {
            this.ws.close(1000, '主动断开连接');
        }
    }
    
    on(eventName, callback) {
        if (!this.eventListeners.has(eventName)) {
            this.eventListeners.set(eventName, []);
        }
        this.eventListeners.get(eventName).push(callback);
    }
    
    off(eventName, callback) {
        if (this.eventListeners.has(eventName)) {
            const listeners = this.eventListeners.get(eventName);
            const index = listeners.indexOf(callback);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }
    
    emit(eventName, data = null) {
        if (this.eventListeners.has(eventName)) {
            this.eventListeners.get(eventName).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`事件处理器错误 (${eventName}):`, error);
                }
            });
        }
    }
    
    generateMessageId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }
      async getUserId() {
        // 首先尝试从认证状态获取用户ID
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
                if (result.success && result.data?.authenticated && result.data.user?.id) {
                    return result.data.user.id;
                }
            }
        } catch (error) {
            console.warn('获取用户ID失败:', error);
        }
        
        // 降级到localStorage
        return localStorage.getItem('userId') || 'anonymous';
    }
    
    getConnectionStatus() {
        return {
            connected: this.isConnected,
            reconnectAttempts: this.reconnectAttempts,
            queuedMessages: this.messageQueue.length
        };
    }
}

/**
 * Enhanced Chat Component with WebSocket Support
 */
class EnhancedChatComponent {
    constructor(options = {}) {
        this.options = {
            containerId: options.containerId || 'chat-container',
            apiEndpoint: options.apiEndpoint || '/api/chat',
            enableWebSocket: options.enableWebSocket !== false,
            autoSave: options.autoSave !== false,
            maxMessages: options.maxMessages || 1000,
            typingTimeout: options.typingTimeout || 3000,
            ...options
        };
        
        this.container = null;
        this.isVisible = false;
        this.isMinimized = false;
        this.messages = [];
        this.currentConversation = null;
        this.typingUsers = new Set();
        this.typingTimer = null;
        this.lastActivity = Date.now();
        
        if (this.options.enableWebSocket) {
            this.wsService = new WebSocketChatService();
            this.setupWebSocketHandlers();
        }
        
        this.init();
    }
    
    init() {
        this.createChatInterface();
        this.loadConversationHistory();
        this.bindEvents();
        
        if (this.options.autoSave) {
            this.startAutoSave();
        }
        
        console.log('Enhanced Chat Component 已初始化');
    }
    
    setupWebSocketHandlers() {
        this.wsService.on('connected', () => {
            this.updateConnectionStatus(true);
            this.showSystemMessage('已连接到聊天服务器');
        });
        
        this.wsService.on('disconnected', () => {
            this.updateConnectionStatus(false);
            this.showSystemMessage('与聊天服务器断开连接', 'warning');
        });
        
        this.wsService.on('message', (data) => {
            this.handleIncomingMessage(data);
        });
        
        this.wsService.on('typing', (data) => {
            this.handleTypingIndicator(data);
        });
        
        this.wsService.on('userStatus', (data) => {
            this.handleUserStatusUpdate(data);
        });
        
        this.wsService.on('error', (error) => {
            this.showSystemMessage('聊天服务连接错误', 'error');
        });
    }
    
    createChatInterface() {
        this.container = document.createElement('div');
        this.container.id = this.options.containerId;
        this.container.className = 'enhanced-chat-container';
        this.container.innerHTML = this.getChatHTML();
        
        document.body.appendChild(this.container);
        
        // 添加样式
        this.addChatStyles();
    }
    
    getChatHTML() {
        return `
            <div class="chat-toggle-btn" id="chatToggleBtn">
                <i class="bi bi-chat-dots"></i>
                <span class="chat-badge" id="chatBadge">0</span>
                <div class="connection-indicator" id="connectionIndicator"></div>
            </div>
            
            <div class="chat-window" id="chatWindow">
                <div class="chat-header">
                    <div class="chat-title">
                        <i class="bi bi-robot"></i>
                        <span>AlingAi Assistant</span>
                        <div class="connection-status" id="connectionStatus">
                            <span class="status-dot"></span>
                            <span class="status-text">连接中...</span>
                        </div>
                    </div>
                    <div class="chat-controls">
                        <button class="btn-minimize" id="btnMinimize" title="最小化">
                            <i class="bi bi-dash"></i>
                        </button>
                        <button class="btn-close" id="btnClose" title="关闭">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                
                <div class="chat-body">
                    <div class="chat-messages" id="chatMessages">
                        <div class="welcome-message">
                            <div class="ai-avatar">🤖</div>
                            <div class="message-content">
                                <p>您好！我是AlingAi助手，很高兴为您服务。</p>
                                <p>您可以向我询问任何问题，我会尽力帮助您。</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="typing-indicator" id="typingIndicator">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span class="typing-text">AI正在思考中...</span>
                    </div>
                </div>
                
                <div class="chat-footer">
                    <div class="input-container">
                        <textarea 
                            id="chatInput" 
                            placeholder="输入您的消息... (Ctrl+Enter发送)"
                            rows="1"
                        ></textarea>
                        <div class="input-actions">
                            <button class="btn-attachment" id="btnAttachment" title="添加附件">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            <button class="btn-send" id="btnSend" title="发送消息">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chat-info">
                        <span class="message-count">消息: <span id="messageCount">0</span></span>
                        <span class="last-active">最后活动: <span id="lastActive">刚刚</span></span>
                    </div>
                </div>
            </div>
        `;
    }
    
    addChatStyles() {
        if (document.getElementById('enhanced-chat-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'enhanced-chat-styles';
        style.textContent = `
            .enhanced-chat-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 10000;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .chat-toggle-btn {
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                transition: all 0.3s ease;
                position: relative;
                color: white;
                font-size: 24px;
            }
            
            .chat-toggle-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
            }
            
            .chat-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #ff4757;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                display: none;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: bold;
            }
            
            .chat-badge.show {
                display: flex;
            }
            
            .connection-indicator {
                position: absolute;
                bottom: 5px;
                right: 5px;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #ffc107;
                border: 2px solid white;
                animation: pulse 2s infinite;
            }
            
            .connection-indicator.connected {
                background: #28a745;
                animation: none;
            }
            
            .connection-indicator.disconnected {
                background: #dc3545;
                animation: blink 1s infinite;
            }
            
            @keyframes pulse {
                0% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.2); opacity: 0.7; }
                100% { transform: scale(1); opacity: 1; }
            }
            
            @keyframes blink {
                0%, 50% { opacity: 1; }
                51%, 100% { opacity: 0.3; }
            }
            
            .chat-window {
                position: absolute;
                bottom: 80px;
                right: 0;
                width: 380px;
                height: 500px;
                background: #ffffff;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                display: none;
                flex-direction: column;
                overflow: hidden;
                animation: slideUp 0.3s ease;
            }
            
            .chat-window.show {
                display: flex;
            }
            
            .chat-window.minimized {
                height: 60px;
            }
            
            .chat-window.minimized .chat-body,
            .chat-window.minimized .chat-footer {
                display: none;
            }
            
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .chat-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-shrink: 0;
            }
            
            .chat-title {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
            }
            
            .chat-title i {
                font-size: 20px;
            }
            
            .connection-status {
                display: flex;
                align-items: center;
                gap: 5px;
                font-size: 12px;
                opacity: 0.9;
                margin-left: 10px;
            }
            
            .status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #ffc107;
            }
            
            .status-dot.connected {
                background: #28a745;
            }
            
            .status-dot.disconnected {
                background: #dc3545;
            }
            
            .chat-controls {
                display: flex;
                gap: 10px;
            }
            
            .btn-minimize,
            .btn-close {
                background: none;
                border: none;
                color: white;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: background 0.2s;
            }
            
            .btn-minimize:hover,
            .btn-close:hover {
                background: rgba(255, 255, 255, 0.2);
            }
            
            .chat-body {
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            
            .chat-messages {
                flex: 1;
                padding: 20px;
                overflow-y: auto;
                scroll-behavior: smooth;
            }
            
            .welcome-message,
            .message {
                display: flex;
                margin-bottom: 20px;
                align-items: flex-start;
                gap: 12px;
            }
            
            .ai-avatar,
            .user-avatar {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 18px;
            }
            
            .ai-avatar {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .user-avatar {
                background: #f8f9fa;
                color: #495057;
                order: 2;
            }
            
            .message.user {
                flex-direction: row-reverse;
            }
            
            .message-content {
                flex: 1;
                background: #f8f9fa;
                padding: 12px 16px;
                border-radius: 15px;
                position: relative;
                word-break: break-word;
            }
            
            .message.user .message-content {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .message-content p {
                margin: 0 0 8px 0;
            }
            
            .message-content p:last-child {
                margin-bottom: 0;
            }
            
            .message-time {
                font-size: 11px;
                opacity: 0.7;
                margin-top: 8px;
            }
            
            .typing-indicator {
                display: none;
                padding: 0 20px 10px;
                align-items: center;
                gap: 10px;
            }
            
            .typing-indicator.show {
                display: flex;
            }
            
            .typing-dots {
                display: flex;
                gap: 4px;
            }
            
            .typing-dots span {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #667eea;
                animation: typing 1.4s infinite;
            }
            
            .typing-dots span:nth-child(2) {
                animation-delay: 0.2s;
            }
            
            .typing-dots span:nth-child(3) {
                animation-delay: 0.4s;
            }
            
            @keyframes typing {
                0%, 60%, 100% {
                    transform: scale(1);
                    opacity: 0.5;
                }
                30% {
                    transform: scale(1.2);
                    opacity: 1;
                }
            }
            
            .typing-text {
                font-size: 12px;
                color: #666;
                font-style: italic;
            }
            
            .chat-footer {
                border-top: 1px solid #e9ecef;
                padding: 15px 20px;
                flex-shrink: 0;
            }
            
            .input-container {
                display: flex;
                align-items: flex-end;
                gap: 10px;
                margin-bottom: 10px;
            }
            
            #chatInput {
                flex: 1;
                border: 1px solid #e9ecef;
                border-radius: 20px;
                padding: 10px 15px;
                resize: none;
                outline: none;
                font-family: inherit;
                font-size: 14px;
                line-height: 1.4;
                max-height: 100px;
                transition: border-color 0.2s;
            }
            
            #chatInput:focus {
                border-color: #667eea;
            }
            
            .input-actions {
                display: flex;
                gap: 5px;
            }
            
            .btn-attachment,
            .btn-send {
                width: 40px;
                height: 40px;
                border: none;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .btn-attachment {
                background: #f8f9fa;
                color: #6c757d;
            }
            
            .btn-attachment:hover {
                background: #e9ecef;
                color: #495057;
            }
            
            .btn-send {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .btn-send:hover {
                transform: scale(1.05);
                box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            }
            
            .btn-send:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }
            
            .chat-info {
                display: flex;
                justify-content: space-between;
                font-size: 11px;
                color: #6c757d;
            }
            
            .system-message {
                text-align: center;
                margin: 10px 0;
                padding: 8px;
                border-radius: 10px;
                font-size: 12px;
                background: #e3f2fd;
                color: #1976d2;
            }
            
            .system-message.warning {
                background: #fff3cd;
                color: #856404;
            }
            
            .system-message.error {
                background: #f8d7da;
                color: #721c24;
            }
            
            /* 移动端适配 */
            @media (max-width: 480px) {
                .enhanced-chat-container {
                    bottom: 10px;
                    right: 10px;
                    left: 10px;
                }
                
                .chat-window {
                    width: 100%;
                    height: 70vh;
                    max-height: 500px;
                    right: 0;
                    bottom: 80px;
                }
                
                .chat-toggle-btn {
                    width: 50px;
                    height: 50px;
                    font-size: 20px;
                }
            }
        `;
        
        document.head.appendChild(style);
    }
    
    bindEvents() {
        const toggleBtn = document.getElementById('chatToggleBtn');
        const chatWindow = document.getElementById('chatWindow');
        const btnMinimize = document.getElementById('btnMinimize');
        const btnClose = document.getElementById('btnClose');
        const chatInput = document.getElementById('chatInput');
        const btnSend = document.getElementById('btnSend');
        const btnAttachment = document.getElementById('btnAttachment');
        
        // 切换聊天窗口
        toggleBtn.addEventListener('click', () => {
            this.toggleChat();
        });
        
        // 最小化
        btnMinimize.addEventListener('click', () => {
            this.minimizeChat();
        });
        
        // 关闭
        btnClose.addEventListener('click', () => {
            this.closeChat();
        });
        
        // 发送消息
        btnSend.addEventListener('click', () => {
            this.sendMessage();
        });
        
        // 输入框事件
        chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                this.sendMessage();
            } else if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        chatInput.addEventListener('input', () => {
            this.handleTyping();
            this.autoResizeInput();
        });
        
        // 附件上传
        btnAttachment.addEventListener('click', () => {
            this.handleAttachment();
        });
        
        // 点击外部关闭
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                if (this.isVisible && !this.isMinimized) {
                    // 可选择是否在点击外部时关闭
                    // this.closeChat();
                }
            }
        });
    }
    
    toggleChat() {
        const chatWindow = document.getElementById('chatWindow');
        
        if (this.isVisible) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }
    
    openChat() {
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.classList.add('show');
        this.isVisible = true;
        this.isMinimized = false;
        
        // 聚焦输入框
        setTimeout(() => {
            document.getElementById('chatInput').focus();
        }, 100);
        
        // 滚动到底部
        this.scrollToBottom();
        
        // 清除未读徽章
        this.clearBadge();
    }
    
    closeChat() {
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.classList.remove('show');
        this.isVisible = false;
        this.isMinimized = false;
    }
    
    minimizeChat() {
        const chatWindow = document.getElementById('chatWindow');
        this.isMinimized = !this.isMinimized;
        
        if (this.isMinimized) {
            chatWindow.classList.add('minimized');
        } else {
            chatWindow.classList.remove('minimized');
            setTimeout(() => {
                document.getElementById('chatInput').focus();
            }, 100);
        }
    }
    
    async sendMessage() {
        const input = document.getElementById('chatInput');
        const content = input.value.trim();
        
        if (!content) return;
        
        // 添加用户消息到界面
        this.addMessage(content, 'user');
        
        // 清空输入框
        input.value = '';
        this.autoResizeInput();
        
        // 显示AI思考中
        this.showTyping();
        
        try {
            // WebSocket发送消息
            if (this.wsService && this.wsService.isConnected) {
                this.wsService.sendChatMessage(content, this.currentConversation);
            } else {
                // 降级到HTTP API
                await this.sendMessageViaAPI(content);
            }
            
            this.updateLastActivity();
        } catch (error) {
            console.error('发送消息失败:', error);
            this.hideTyping();
            this.addMessage('抱歉，发送消息时出现错误，请稍后重试。', 'ai', true);
        }
    }
    
    async sendMessageViaAPI(content) {
        try {
            const response = await fetch(this.options.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: content,
                    conversation_id: this.currentConversation,
                    timestamp: Date.now()
                })
            });
            
            if (!response.ok) {
                throw new Error('API请求失败');
            }
            
            const data = await response.json();
            
            this.hideTyping();
            this.addMessage(data.response || '收到您的消息，正在处理中...', 'ai');
            
        } catch (error) {
            console.error('API请求失败:', error);
            this.hideTyping();
            this.addMessage('连接服务器失败，请检查网络连接。', 'ai', true);
        }
    }
    
    handleIncomingMessage(data) {
        if (data.payload.conversationId === this.currentConversation) {
            this.hideTyping();
            this.addMessage(data.payload.content, 'ai');
            
            if (!this.isVisible) {
                this.incrementBadge();
            }
        }
    }
    
    handleTypingIndicator(data) {
        if (data.payload.conversationId === this.currentConversation) {
            if (data.payload.isTyping) {
                this.showTyping();
            } else {
                this.hideTyping();
            }
        }
    }
    
    handleUserStatusUpdate(data) {
        // 处理用户状态更新
        console.log('用户状态更新:', data);
    }
    
    addMessage(content, sender = 'ai', isError = false) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageElement = document.createElement('div');
        const timestamp = new Date().toLocaleTimeString();
        
        messageElement.className = `message ${sender}`;
        if (isError) messageElement.classList.add('error');
        
        const avatar = sender === 'user' ? '👤' : '🤖';
        const avatarClass = sender === 'user' ? 'user-avatar' : 'ai-avatar';
        
        messageElement.innerHTML = `
            <div class="${avatarClass}">${avatar}</div>
            <div class="message-content">
                <p>${this.formatMessage(content)}</p>
                <div class="message-time">${timestamp}</div>
            </div>
        `;
        
        messagesContainer.appendChild(messageElement);
        
        // 添加到消息数组
        this.messages.push({
            content,
            sender,
            timestamp: Date.now(),
            isError
        });
        
        // 限制消息数量
        if (this.messages.length > this.options.maxMessages) {
            this.messages.shift();
            messagesContainer.removeChild(messagesContainer.firstElementChild);
        }
        
        // 更新消息计数
        this.updateMessageCount();
        
        // 滚动到底部
        this.scrollToBottom();
        
        // 保存消息
        if (this.options.autoSave) {
            this.saveMessages();
        }
    }
    
    formatMessage(content) {
        // 简单的markdown支持
        return content
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code>$1</code>')
            .replace(/\n/g, '<br>');
    }
    
    showTyping() {
        const typingIndicator = document.getElementById('typingIndicator');
        typingIndicator.classList.add('show');
        this.scrollToBottom();
    }
    
    hideTyping() {
        const typingIndicator = document.getElementById('typingIndicator');
        typingIndicator.classList.remove('show');
    }
    
    handleTyping() {
        if (this.wsService && this.wsService.isConnected) {
            // 发送正在输入指示
            this.wsService.sendTypingIndicator(true, this.currentConversation);
            
            // 清除之前的计时器
            if (this.typingTimer) {
                clearTimeout(this.typingTimer);
            }
            
            // 设置新的计时器
            this.typingTimer = setTimeout(() => {
                this.wsService.sendTypingIndicator(false, this.currentConversation);
            }, this.options.typingTimeout);
        }
    }
    
    autoResizeInput() {
        const input = document.getElementById('chatInput');
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 100) + 'px';
    }
    
    scrollToBottom() {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    updateConnectionStatus(connected) {
        const indicator = document.getElementById('connectionIndicator');
        const statusDot = document.querySelector('.status-dot');
        const statusText = document.querySelector('.status-text');
        
        if (connected) {
            indicator.classList.remove('disconnected');
            indicator.classList.add('connected');
            statusDot.classList.add('connected');
            statusText.textContent = '已连接';
        } else {
            indicator.classList.remove('connected');
            indicator.classList.add('disconnected');
            statusDot.classList.add('disconnected');
            statusText.textContent = '连接中断';
        }
    }
    
    showSystemMessage(message, type = 'info') {
        const messagesContainer = document.getElementById('chatMessages');
        const messageElement = document.createElement('div');
        
        messageElement.className = `system-message ${type}`;
        messageElement.textContent = message;
        
        messagesContainer.appendChild(messageElement);
        this.scrollToBottom();
        
        // 自动移除系统消息
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.remove();
            }
        }, 5000);
    }
    
    incrementBadge() {
        const badge = document.getElementById('chatBadge');
        const currentCount = parseInt(badge.textContent) || 0;
        badge.textContent = currentCount + 1;
        badge.classList.add('show');
    }
    
    clearBadge() {
        const badge = document.getElementById('chatBadge');
        badge.textContent = '0';
        badge.classList.remove('show');
    }
    
    updateMessageCount() {
        const messageCount = document.getElementById('messageCount');
        messageCount.textContent = this.messages.length;
    }
    
    updateLastActivity() {
        this.lastActivity = Date.now();
        const lastActive = document.getElementById('lastActive');
        lastActive.textContent = '刚刚';
        
        // 定期更新时间显示
        this.updateLastActiveDisplay();
    }
    
    updateLastActiveDisplay() {
        const lastActive = document.getElementById('lastActive');
        const timeDiff = Date.now() - this.lastActivity;
        const minutes = Math.floor(timeDiff / 60000);
        
        if (minutes < 1) {
            lastActive.textContent = '刚刚';
        } else if (minutes < 60) {
            lastActive.textContent = `${minutes}分钟前`;
        } else {
            const hours = Math.floor(minutes / 60);
            lastActive.textContent = `${hours}小时前`;
        }
    }
    
    handleAttachment() {
        // 创建文件选择器
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*,text/*,.pdf,.doc,.docx';
        fileInput.multiple = false;
        
        fileInput.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                this.uploadFile(file);
            }
        };
        
        fileInput.click();
    }
    
    async uploadFile(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            this.showSystemMessage('文件大小不能超过10MB', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('conversation_id', this.currentConversation);
        
        try {
            this.addMessage(`正在上传文件: ${file.name}`, 'user');
            
            const response = await fetch('/api/upload', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('文件上传失败');
            }
            
            const data = await response.json();
            this.addMessage(`文件上传成功: ${file.name}`, 'ai');
            
        } catch (error) {
            console.error('文件上传失败:', error);
            this.addMessage('文件上传失败，请稍后重试', 'ai', true);
        }
    }
      async loadConversationHistory() {
        try {
            // 检查用户认证状态
            const isAuthenticated = await this.checkAuthentication();
            
            if (isAuthenticated) {
                // 从API加载历史记录
                const response = await fetch('/api/v1/chat/sessions?limit=1', {
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
                        
                        // 加载该会话的消息
                        const messagesResponse = await fetch(`/api/v1/chat/messages?conversation_id=${latestSession.id}`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (messagesResponse.ok) {
                            const messagesResult = await messagesResponse.json();
                            if (messagesResult.success && messagesResult.data) {
                                this.messages = messagesResult.data.map(msg => ({
                                    content: msg.content,
                                    sender: msg.sender === 'user' ? 'user' : 'ai',
                                    timestamp: msg.created_at,
                                    isError: false
                                }));
                                this.renderMessages();
                                return;
                            }
                        }
                    }
                }
            }
            
            // 降级到localStorage
            const saved = localStorage.getItem('alingai_chat_messages');
            if (saved) {
                this.messages = JSON.parse(saved);
                this.renderMessages();
            }
        } catch (error) {
            console.error('加载聊天记录失败:', error);
            // 降级到localStorage
            const saved = localStorage.getItem('alingai_chat_messages');
            if (saved) {
                this.messages = JSON.parse(saved);
                this.renderMessages();
            }
        }
    }

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
    
    renderMessages() {
        const messagesContainer = document.getElementById('chatMessages');
        
        // 清空欢迎消息
        messagesContainer.innerHTML = '';
        
        this.messages.forEach(message => {
            this.addMessageToDOM(message);
        });
        
        this.updateMessageCount();
        this.scrollToBottom();
    }
    
    addMessageToDOM(message) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageElement = document.createElement('div');
        const timestamp = new Date(message.timestamp).toLocaleTimeString();
        
        messageElement.className = `message ${message.sender}`;
        if (message.isError) messageElement.classList.add('error');
        
        const avatar = message.sender === 'user' ? '👤' : '🤖';
        const avatarClass = message.sender === 'user' ? 'user-avatar' : 'ai-avatar';
        
        messageElement.innerHTML = `
            <div class="${avatarClass}">${avatar}</div>
            <div class="message-content">
                <p>${this.formatMessage(message.content)}</p>
                <div class="message-time">${timestamp}</div>
            </div>
        `;
        
        messagesContainer.appendChild(messageElement);
    }    async saveMessages() {
        try {
            const isAuthenticated = await this.checkAuthentication();
            
            if (isAuthenticated && this.messages.length > 0) {
                // 认证用户：尝试保存到API
                await this.saveMessagesToServer();
            } else {
                // 未认证用户：保存到localStorage
                this.saveMessagesToLocal();
            }
        } catch (error) {
            console.error('保存聊天记录失败:', error);
            // 降级到localStorage
            this.saveMessagesToLocal();
        }
    }

    async saveMessagesToServer() {
        try {
            const response = await fetch('/api/v1/chat/conversations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    title: `增强聊天会话 - ${new Date().toLocaleString()}`,
                    messages: this.messages,
                    source: 'enhanced-chat-component'
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log('✅ 聊天记录已保存到服务器');
                    return;
                }
            }
            throw new Error('保存到服务器失败');
        } catch (error) {
            console.error('保存聊天到服务器失败:', error);
            throw error;
        }
    }

    saveMessagesToLocal() {
        try {
            localStorage.setItem('alingai_chat_messages', JSON.stringify(this.messages));
            console.log('📱 聊天记录已保存到本地存储');
        } catch (error) {
            console.error('保存聊天到本地存储失败:', error);
        }
    }
    
    startAutoSave() {
        setInterval(() => {
            this.saveMessages();
            this.updateLastActiveDisplay();
        }, 30000); // 每30秒保存一次
    }
    
    // 公共API
    sendTextMessage(text) {
        if (text && text.trim()) {
            document.getElementById('chatInput').value = text;
            this.sendMessage();
        }
    }
      async clearChat() {
        try {
            const isAuthenticated = await this.checkAuthentication();
            
            if (isAuthenticated) {
                // 认证用户：清除服务器端数据
                await this.clearChatFromServer();
            }
            
            // 清除本地数据
            this.messages = [];
            document.getElementById('chatMessages').innerHTML = `
                <div class="welcome-message">
                    <div class="ai-avatar">🤖</div>
                    <div class="message-content">
                        <p>聊天记录已清空。</p>
                    </div>
                </div>
            `;
            this.updateMessageCount();
            localStorage.removeItem('alingai_chat_messages');
            
            console.log('✅ 聊天记录已清空');
        } catch (error) {
            console.warn('清空聊天记录时出错:', error);
            // 确保本地存储被清理
            this.messages = [];
            localStorage.removeItem('alingai_chat_messages');
        }
    }

    async clearChatFromServer() {
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
                    console.log('✅ 服务器端聊天记录已清空');
                    return true;
                }
            }
        } catch (error) {
            console.warn('清空服务器端聊天记录失败:', error);
        }
        return false;
    }
    
    getMessages() {
        return [...this.messages];
    }
    
    destroy() {
        if (this.wsService) {
            this.wsService.disconnect();
        }
        
        if (this.typingTimer) {
            clearTimeout(this.typingTimer);
        }
        
        if (this.container) {
            this.container.remove();
        }
        
        const style = document.getElementById('enhanced-chat-styles');
        if (style) {
            style.remove();
        }
    }
}

// 导出供全局使用
window.EnhancedChatComponent = EnhancedChatComponent;
window.WebSocketChatService = WebSocketChatService;
