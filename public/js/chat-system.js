/**
 * AlingAi Pro - 聊天系统模块
 * 高级聊天功能集成，支持实时通信、多模态交互和智能响应
 * 
 * @version 1.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

class ChatSystem {
    constructor(options = {}) {
        this.options = {
            apiEndpoint: '/api/chat',
            maxRetries: 3,
            retryDelay: 1000,
            enableTyping: true,
            enableVoice: false,
            enableQuantumEffects: true,
            autoSave: true,
            maxHistorySize: 1000,
            ...options
        };

        // API端点配置
        this.apiEndpoints = {
            chat: '/api/v1/chat/messages',
            sessions: '/api/v1/chat/sessions',
            auth: '/api/v1/auth/check',
            preferences: '/api/v1/user/preferences'
        };

        this.state = {
            isInitialized: false,
            isConnected: false,
            isTyping: false,
            currentConversationId: null,
            messageQueue: [],
            retryCount: 0,
            isAuthenticated: false,
            currentUser: null
        };

        this.conversations = new Map();
        this.currentMessages = [];
        this.eventListeners = new Map();
        this.wsConnection = null;
        this.quantumIntegrator = null;

        this.init();
    }

    /**
     * 初始化聊天系统
     */    async init() {
        try {
            
            
            await this.checkAuthentication();
            await this.setupDOM();
            await this.setupEventListeners();
            await this.loadSettings();
            await this.loadConversationHistory();
            await this.initWebSocket();
            await this.initQuantumIntegration();
            
            this.state.isInitialized = true;
            this.emit('initialized');
            
            
        } catch (error) {
            console.error('❌ 聊天系统初始化失败:', error);
            this.emit('error', { type: 'initialization', error });
        }
    }

    /**
     * 检查用户认证状态
     */
    async checkAuthentication() {
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
                this.state.isAuthenticated = result.success && result.data?.authenticated;
                if (this.state.isAuthenticated && result.data?.user) {
                    this.state.currentUser = result.data.user;
                }
            } else {
                this.state.isAuthenticated = false;
            }
        } catch (error) {
            console.warn('认证检查失败:', error);
            this.state.isAuthenticated = false;
        }
        
        return this.state.isAuthenticated;
    }

    /**
     * 设置DOM元素
     */
    async setupDOM() {
        // 确保聊天容器存在
        if (!document.getElementById('chat-container')) {
            const chatContainer = document.createElement('div');
            chatContainer.id = 'chat-container';
            chatContainer.className = 'chat-container';
            chatContainer.innerHTML = this.generateChatHTML();
            document.body.appendChild(chatContainer);
        }

        // 缓存DOM元素
        this.elements = {
            container: document.getElementById('chat-container'),
            messagesList: document.getElementById('messages-list'),
            messageInput: document.getElementById('message-input'),
            sendButton: document.getElementById('send-button'),
            voiceButton: document.getElementById('voice-button'),
            settingsButton: document.getElementById('settings-button'),
            newChatButton: document.getElementById('new-chat-button'),
            conversationsList: document.getElementById('conversations-list'),
            typingIndicator: document.getElementById('typing-indicator'),
            statusIndicator: document.getElementById('status-indicator')
        };

        // 设置初始状态
        this.updateConnectionStatus(false);
    }

    /**
     * 生成聊天界面HTML
     */
    generateChatHTML() {
        return `
            <div class="chat-header">
                <div class="chat-title">
                    <h3>AlingAi 智能助手</h3>
                    <div id="status-indicator" class="status-indicator offline">
                        <span class="status-dot"></span>
                        <span class="status-text">离线</span>
                    </div>
                </div>
                <div class="chat-controls">
                    <button id="new-chat-button" class="btn btn-sm btn-outline" title="新建对话">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button id="settings-button" class="btn btn-sm btn-outline" title="设置">
                        <i class="fas fa-cog"></i>
                    </button>
                    <button id="minimize-chat" class="btn btn-sm btn-outline" title="最小化">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            
            <div class="chat-body">
                <div class="chat-sidebar">
                    <div class="conversations-header">
                        <h4>对话历史</h4>
                        <div class="conversations-search">
                            <input type="text" id="conversations-search" placeholder="搜索对话..." />
                        </div>
                    </div>
                    <div id="conversations-list" class="conversations-list"></div>
                </div>
                
                <div class="chat-main">
                    <div id="messages-list" class="messages-list">
                        <div class="welcome-message">
                            <div class="quantum-avatar">
                                <div class="quantum-particles"></div>
                            </div>
                            <h4>欢迎使用 AlingAi Pro</h4>
                            <p>我是您的智能助手，可以帮助您处理各种任务。您可以：</p>
                            <ul>
                                <li>询问技术问题</li>
                                <li>请求代码帮助</li>
                                <li>获取实时信息</li>
                                <li>进行创意讨论</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div id="typing-indicator" class="typing-indicator" style="display: none;">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span class="typing-text">AI正在思考...</span>
                    </div>
                    
                    <div class="chat-input-container">
                        <div class="quick-actions">
                            <button class="quick-action" data-action="explain" title="解释概念">
                                <i class="fas fa-lightbulb"></i>
                            </button>
                            <button class="quick-action" data-action="code" title="编程帮助">
                                <i class="fas fa-code"></i>
                            </button>
                            <button class="quick-action" data-action="translate" title="翻译">
                                <i class="fas fa-language"></i>
                            </button>
                            <button class="quick-action" data-action="summarize" title="总结">
                                <i class="fas fa-compress-alt"></i>
                            </button>
                        </div>
                        
                        <div class="input-container">
                            <textarea 
                                id="message-input" 
                                class="message-input" 
                                placeholder="输入您的消息... (Shift+Enter 换行，Enter 发送)"
                                rows="1"
                                maxlength="4000"
                            ></textarea>
                            
                            <div class="input-actions">
                                <button id="voice-button" class="btn btn-sm btn-ghost" title="语音输入" disabled>
                                    <i class="fas fa-microphone"></i>
                                </button>
                                <button id="attach-button" class="btn btn-sm btn-ghost" title="附件">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button id="send-button" class="btn btn-sm btn-primary" title="发送" disabled>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="input-footer">
                            <div class="character-count">
                                <span id="char-count">0</span>/4000
                            </div>
                            <div class="input-hints">
                                支持 Markdown 格式 • 
                                <a href="#" id="shortcuts-help">快捷键</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * 设置事件监听器
     */
    async setupEventListeners() {
        // 消息输入事件
        this.elements.messageInput?.addEventListener('input', (e) => {
            this.handleInputChange(e);
        });

        this.elements.messageInput?.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });

        // 发送按钮
        this.elements.sendButton?.addEventListener('click', () => {
            this.sendMessage();
        });

        // 新建对话
        this.elements.newChatButton?.addEventListener('click', () => {
            this.createNewConversation();
        });

        // 语音按钮
        this.elements.voiceButton?.addEventListener('click', () => {
            this.toggleVoiceInput();
        });

        // 快捷操作
        document.querySelectorAll('.quick-action').forEach(button => {
            button.addEventListener('click', (e) => {
                const action = e.currentTarget.dataset.action;
                this.handleQuickAction(action);
            });
        });

        // 对话搜索
        const searchInput = document.getElementById('conversations-search');
        searchInput?.addEventListener('input', (e) => {
            this.filterConversations(e.target.value);
        });

        // 窗口事件
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });

        // 页面可见性变化
        document.addEventListener('visibilitychange', () => {
            this.handleVisibilityChange();
        });
    }

    /**
     * 处理输入变化
     */
    handleInputChange(event) {
        const input = event.target;
        const text = input.value;
        
        // 更新字符计数
        const charCount = document.getElementById('char-count');
        if (charCount) {
            charCount.textContent = text.length;
        }

        // 自动调整高度
        this.adjustTextareaHeight(input);

        // 更新发送按钮状态
        const sendButton = this.elements.sendButton;
        if (sendButton) {
            sendButton.disabled = !text.trim();
        }

        // 发送打字指示
        if (this.options.enableTyping && text.trim()) {
            this.sendTypingIndicator();
        }
    }

    /**
     * 处理键盘事件
     */
    handleKeydown(event) {
        if (event.key === 'Enter') {
            if (event.shiftKey) {
                // Shift+Enter 换行
                return;
            } else {
                // Enter 发送消息
                event.preventDefault();
                this.sendMessage();
            }
        } else if (event.key === 'Escape') {
            // ESC 清空输入
            this.clearInput();
        } else if (event.ctrlKey || event.metaKey) {
            // Ctrl/Cmd 快捷键
            this.handleCtrlShortcuts(event);
        }
    }

    /**
     * 处理快捷键
     */
    handleCtrlShortcuts(event) {
        switch (event.key) {
            case 'n':
                event.preventDefault();
                this.createNewConversation();
                break;
            case 'k':
                event.preventDefault();
                this.focusConversationSearch();
                break;
            case '/':
                event.preventDefault();
                this.showShortcutsHelp();
                break;
        }
    }

    /**
     * 发送消息
     */
    async sendMessage(text = null) {
        try {
            const messageText = text || this.elements.messageInput?.value?.trim();
            
            if (!messageText) {
                return;
            }

            // 添加用户消息到界面
            const userMessage = this.addMessage({
                id: this.generateMessageId(),
                type: 'user',
                content: messageText,
                timestamp: new Date().toISOString()
            });

            // 清空输入框
            if (!text) {
                this.clearInput();
            }

            // 显示打字指示器
            this.showTypingIndicator();

            // 触发量子效果
            this.triggerQuantumEffect('user_message', { message: messageText });

            // 发送到服务器
            const response = await this.sendToAPI(messageText);

            // 隐藏打字指示器
            this.hideTypingIndicator();

            // 添加AI响应
            if (response.success) {
                const aiMessage = this.addMessage({
                    id: this.generateMessageId(),
                    type: 'assistant',
                    content: response.content,
                    timestamp: new Date().toISOString(),
                    metadata: response.metadata
                });

                // 触发量子效果
                this.triggerQuantumEffect('ai_response', { 
                    message: response.content,
                    confidence: response.metadata?.confidence 
                });
            } else {
                // 显示错误消息
                this.addMessage({
                    id: this.generateMessageId(),
                    type: 'error',
                    content: response.error || '发送失败，请重试',
                    timestamp: new Date().toISOString()
                });

                // 触发错误效果
                this.triggerQuantumEffect('error', { error: response.error });
            }

            // 自动保存对话
            if (this.options.autoSave) {
                await this.saveConversation();
            }

        } catch (error) {
            console.error('发送消息失败:', error);
            this.hideTypingIndicator();
            
            this.addMessage({
                id: this.generateMessageId(),
                type: 'error',
                content: '网络错误，请检查连接后重试',
                timestamp: new Date().toISOString()
            });

            this.emit('error', { type: 'send_message', error });
        }
    }

    /**
     * 发送到API
     */
    async sendToAPI(message, options = {}) {
        const { signal, ...otherOptions } = options;
        
        try {
            const response = await fetch(`${this.options.apiEndpoint}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.APP_CONFIG?.csrfToken || '',
                    ...this.getAuthHeaders()
                },
                body: JSON.stringify({
                    message,
                    conversation_id: this.state.currentConversationId,
                    context: this.getConversationContext(),
                    ...otherOptions
                }),
                signal
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            return data;

        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('请求已取消');
            }
            
            // 重试逻辑
            if (this.state.retryCount < this.options.maxRetries) {
                this.state.retryCount++;
                
                
                await this.delay(this.options.retryDelay * this.state.retryCount);
                return this.sendToAPI(message, options);
            }
            
            this.state.retryCount = 0;
            throw error;
        }
    }

    /**
     * 添加消息到界面
     */
    addMessage(messageData) {
        const { id, type, content, timestamp, metadata = {} } = messageData;
        
        // 创建消息元素
        const messageElement = document.createElement('div');
        messageElement.className = `message message-${type}`;
        messageElement.dataset.messageId = id;
        messageElement.dataset.timestamp = timestamp;

        // 生成消息HTML
        messageElement.innerHTML = this.generateMessageHTML(messageData);

        // 添加到消息列表
        const messagesList = this.elements.messagesList;
        if (messagesList) {
            // 隐藏欢迎消息
            const welcomeMessage = messagesList.querySelector('.welcome-message');
            if (welcomeMessage) {
                welcomeMessage.style.display = 'none';
            }

            messagesList.appendChild(messageElement);
            this.scrollToBottom();
        }

        // 添加到当前消息列表
        this.currentMessages.push(messageData);

        // 设置消息交互
        this.setupMessageInteractions(messageElement, messageData);

        // 应用量子效果
        if (this.options.enableQuantumEffects) {
            this.applyQuantumMessageEffect(messageElement, type);
        }

        this.emit('message_added', messageData);
        return messageElement;
    }

    /**
     * 生成消息HTML
     */
    generateMessageHTML(messageData) {
        const { type, content, timestamp, metadata = {} } = messageData;
        
        const time = new Date(timestamp).toLocaleTimeString('zh-CN', {
            hour: '2-digit',
            minute: '2-digit'
        });

        switch (type) {
            case 'user':
                return `
                    <div class="message-avatar user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-author">您</span>
                            <span class="message-time">${time}</span>
                        </div>
                        <div class="message-text user-message">
                            ${this.processMessageContent(content)}
                        </div>
                    </div>
                    <div class="message-actions">
                        <button class="action-btn copy-btn" title="复制">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="action-btn edit-btn" title="编辑">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                `;

            case 'assistant':
                return `
                    <div class="message-avatar ai-avatar">
                        <div class="quantum-avatar">
                            <div class="quantum-core"></div>
                            <div class="quantum-ring"></div>
                        </div>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-author">AlingAi</span>
                            <span class="message-time">${time}</span>
                            ${metadata.confidence ? `<span class="confidence-badge">${Math.round(metadata.confidence * 100)}%</span>` : ''}
                        </div>
                        <div class="message-text ai-message">
                            ${this.processMessageContent(content)}
                        </div>
                    </div>
                    <div class="message-actions">
                        <button class="action-btn copy-btn" title="复制">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="action-btn regenerate-btn" title="重新生成">
                            <i class="fas fa-redo"></i>
                        </button>
                        <button class="action-btn speak-btn" title="朗读">
                            <i class="fas fa-volume-up"></i>
                        </button>
                        <button class="action-btn like-btn" title="点赞">
                            <i class="far fa-thumbs-up"></i>
                        </button>
                        <button class="action-btn dislike-btn" title="点踩">
                            <i class="far fa-thumbs-down"></i>
                        </button>
                    </div>
                `;

            case 'error':
                return `
                    <div class="message-avatar error-avatar">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-author">系统</span>
                            <span class="message-time">${time}</span>
                        </div>
                        <div class="message-text error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            ${content}
                        </div>
                    </div>
                    <div class="message-actions">
                        <button class="action-btn retry-btn" title="重试">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                `;

            default:
                return `
                    <div class="message-content">
                        <div class="message-text">
                            ${this.processMessageContent(content)}
                        </div>
                    </div>
                `;
        }
    }

    /**
     * 处理消息内容（Markdown、代码高亮等）
     */
    processMessageContent(content) {
        // 转义HTML特殊字符
        const escaped = content
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;');

        // 简单的Markdown支持
        return escaped
            // 代码块
            .replace(/```(\w+)?\n([\s\S]*?)\n```/g, (match, lang, code) => {
                return `<pre class="code-block ${lang || ''}"><code>${code}</code></pre>`;
            })
            // 行内代码
            .replace(/`([^`]+)`/g, '<code class="inline-code">$1</code>')
            // 粗体
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            // 斜体
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            // 链接
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>')
            // 换行
            .replace(/\n/g, '<br>');
    }

    /**
     * 设置消息交互
     */
    setupMessageInteractions(messageElement, messageData) {
        // 复制按钮
        const copyBtn = messageElement.querySelector('.copy-btn');
        copyBtn?.addEventListener('click', () => {
            this.copyMessage(messageData);
        });

        // 重新生成按钮
        const regenerateBtn = messageElement.querySelector('.regenerate-btn');
        regenerateBtn?.addEventListener('click', () => {
            this.regenerateMessage(messageData);
        });

        // 朗读按钮
        const speakBtn = messageElement.querySelector('.speak-btn');
        speakBtn?.addEventListener('click', () => {
            this.speakMessage(messageData);
        });

        // 点赞/点踩
        const likeBtn = messageElement.querySelector('.like-btn');
        const dislikeBtn = messageElement.querySelector('.dislike-btn');
        
        likeBtn?.addEventListener('click', () => {
            this.rateMessage(messageData.id, 'like');
        });
        
        dislikeBtn?.addEventListener('click', () => {
            this.rateMessage(messageData.id, 'dislike');
        });

        // 编辑按钮
        const editBtn = messageElement.querySelector('.edit-btn');
        editBtn?.addEventListener('click', () => {
            this.editMessage(messageData);
        });

        // 重试按钮
        const retryBtn = messageElement.querySelector('.retry-btn');
        retryBtn?.addEventListener('click', () => {
            this.retryLastMessage();
        });
    }

    /**
     * 复制消息
     */
    async copyMessage(messageData) {
        try {
            await navigator.clipboard.writeText(messageData.content);
            this.showToast('消息已复制到剪贴板', 'success');
        } catch (error) {
            console.error('复制失败:', error);
            this.showToast('复制失败', 'error');
        }
    }

    /**
     * WebSocket连接
     */
    async initWebSocket() {
        if (!window.WebSocket) {
            console.warn('浏览器不支持WebSocket');
            return;
        }        try {
            // 连接到独立的WebSocket服务器端口
            const wsUrl = `ws://127.0.0.1:8080/ws`;
            
            this.wsConnection = new WebSocket(wsUrl);
            
            this.wsConnection.onopen = () => {
                
                this.state.isConnected = true;
                this.updateConnectionStatus(true);
                this.emit('connected');
            };

            this.wsConnection.onmessage = (event) => {
                this.handleWebSocketMessage(event);
            };

            this.wsConnection.onclose = (event) => {
                
                this.state.isConnected = false;
                this.updateConnectionStatus(false);
                this.emit('disconnected');
                
                // 自动重连
                setTimeout(() => {
                    if (!this.state.isConnected) {
                        this.initWebSocket();
                    }
                }, 3000);
            };

            this.wsConnection.onerror = (error) => {
                console.error('WebSocket错误:', error);
                this.emit('error', { type: 'websocket', error });
            };

        } catch (error) {
            console.error('WebSocket初始化失败:', error);
        }
    }

    /**
     * 量子效果集成
     */
    async initQuantumIntegration() {
        if (window.QuantumParticles && this.options.enableQuantumEffects) {
            try {
                this.quantumIntegrator = new QuantumIntegrator({
                    container: this.elements.container,
                    particleCount: 50,
                    enableInteraction: true
                });
                
                await this.quantumIntegrator.init();
                
            } catch (error) {
                console.warn('量子效果集成失败:', error);
            }
        }
    }

    /**
     * 触发量子效果
     */
    triggerQuantumEffect(type, data = {}) {
        if (this.quantumIntegrator) {
            this.quantumIntegrator.trigger(type, data);
        }
    }

    /**
     * 应用消息量子效果
     */
    applyQuantumMessageEffect(messageElement, type) {
        if (!this.options.enableQuantumEffects) return;

        // 添加量子动画类
        messageElement.classList.add('quantum-message');
        
        // 根据消息类型应用不同效果
        switch (type) {
            case 'user':
                messageElement.classList.add('quantum-user');
                break;
            case 'assistant':
                messageElement.classList.add('quantum-ai');
                break;
            case 'error':
                messageElement.classList.add('quantum-error');
                break;
        }

        // 入场动画
        requestAnimationFrame(() => {
            messageElement.classList.add('quantum-enter');
        });
    }

    /**
     * 工具函数
     */
    generateMessageId() {
        return `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    adjustTextareaHeight(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    scrollToBottom() {
        const messagesList = this.elements.messagesList;
        if (messagesList) {
            messagesList.scrollTop = messagesList.scrollHeight;
        }
    }

    clearInput() {
        if (this.elements.messageInput) {
            this.elements.messageInput.value = '';
            this.elements.messageInput.style.height = 'auto';
            
            const charCount = document.getElementById('char-count');
            if (charCount) charCount.textContent = '0';
            
            if (this.elements.sendButton) {
                this.elements.sendButton.disabled = true;
            }
        }
    }

    showTypingIndicator() {
        const indicator = this.elements.typingIndicator;
        if (indicator) {
            indicator.style.display = 'flex';
            this.scrollToBottom();
        }
    }

    hideTypingIndicator() {
        const indicator = this.elements.typingIndicator;
        if (indicator) {
            indicator.style.display = 'none';
        }
    }

    updateConnectionStatus(isConnected) {
        const indicator = this.elements.statusIndicator;
        if (indicator) {
            indicator.className = `status-indicator ${isConnected ? 'online' : 'offline'}`;
            const statusText = indicator.querySelector('.status-text');
            if (statusText) {
                statusText.textContent = isConnected ? '在线' : '离线';
            }
        }
    }

    showToast(message, type = 'info') {
        // 使用全局通知系统或创建简单toast
        if (window.showNotification) {
            window.showNotification(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }    getAuthHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        
        const token = localStorage.getItem('auth_token');
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        
        return headers;
    }

    getConversationContext() {
        return {
            messages: this.currentMessages.slice(-10), // 最近10条消息
            conversation_id: this.state.currentConversationId,
            user_preferences: this.getUserPreferences()
        };
    }    async getUserPreferences() {
        if (this.state.isAuthenticated) {
            try {
                const response = await fetch(this.apiEndpoints.preferences, {
                    method: 'GET',
                    headers: this.getAuthHeaders()
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data) {
                        return result.data;
                    }
                }
            } catch (error) {
                console.warn('获取用户偏好设置失败:', error);
            }
        }
        
        // 降级到localStorage
        return JSON.parse(localStorage.getItem('chat_preferences') || '{}');
    }

    /**
     * 事件系统
     */
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }

    off(event, callback) {
        if (this.eventListeners.has(event)) {
            const listeners = this.eventListeners.get(event);
            const index = listeners.indexOf(callback);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    emit(event, data = null) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`事件回调错误 [${event}]:`, error);
                }
            });
        }
    }

    /**
     * 对话管理
     */
    async createNewConversation() {
        try {
            this.state.currentConversationId = this.generateConversationId();
            this.currentMessages = [];
            
            // 清空消息列表
            if (this.elements.messagesList) {
                this.elements.messagesList.innerHTML = '';
                // 重新添加欢迎消息
                const welcomeMessage = document.createElement('div');
                welcomeMessage.className = 'welcome-message';
                welcomeMessage.innerHTML = this.generateChatHTML().match(/<div class="welcome-message">[\s\S]*?<\/div>/)[0];
                this.elements.messagesList.appendChild(welcomeMessage);
            }

            this.emit('conversation_created', { id: this.state.currentConversationId });
            this.showToast('新对话已创建', 'success');

        } catch (error) {
            console.error('创建新对话失败:', error);
            this.showToast('创建对话失败', 'error');
        }
    }

    generateConversationId() {
        return `conv_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }    async saveConversation() {
        if (this.currentMessages.length === 0) return;

        try {
            const conversationData = {
                id: this.state.currentConversationId,
                messages: this.currentMessages,
                timestamp: new Date().toISOString(),
                title: this.generateConversationTitle()
            };

            if (this.state.isAuthenticated) {
                // 首先尝试API保存
                try {
                    await this.saveConversationToServer(conversationData);
                    
                    // 更新本地Map
                    this.conversations.set(conversationData.id, conversationData);
                    this.renderConversationsList();
                    return;
                } catch (apiError) {
                    console.warn('API保存失败，降级到localStorage:', apiError);
                    // 继续执行localStorage保存作为降级
                }
            }

            // localStorage保存（认证失败降级或未认证用户）
            await this.saveConversationLocally(conversationData);

        } catch (error) {
            console.error('保存对话失败:', error);
        }
    }

    async saveConversationToServer(conversationData) {
        const response = await fetch(this.apiEndpoints.sessions, {
            method: 'POST',
            headers: this.getAuthHeaders(),
            body: JSON.stringify({
                conversation_id: conversationData.id,
                title: conversationData.title,
                messages: conversationData.messages,
                timestamp: conversationData.timestamp
            })
        });

        if (!response.ok) {
            throw new Error(`保存会话API失败: ${response.status}`);
        }

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || '保存会话失败');
        }

        return result;
    }

    async saveConversationLocally(conversationData) {
        const conversations = JSON.parse(localStorage.getItem('chat_conversations') || '[]');
        const existingIndex = conversations.findIndex(conv => conv.id === conversationData.id);
        
        if (existingIndex > -1) {
            conversations[existingIndex] = conversationData;
        } else {
            conversations.unshift(conversationData);
        }

        // 限制存储数量
        if (conversations.length > this.options.maxHistorySize) {
            conversations.splice(this.options.maxHistorySize);
        }

        localStorage.setItem('chat_conversations', JSON.stringify(conversations));
        
        // 更新本地Map
        this.conversations.set(conversationData.id, conversationData);
        this.renderConversationsList();
    }

    generateConversationTitle() {
        if (this.currentMessages.length === 0) return '新对话';
        
        const firstUserMessage = this.currentMessages.find(msg => msg.type === 'user');
        if (firstUserMessage) {
            const title = firstUserMessage.content.slice(0, 30);
            return title.length < firstUserMessage.content.length ? title + '...' : title;
        }
        
        return '新对话';
    }    async loadConversationHistory() {
        try {
            if (this.state.isAuthenticated) {
                // 从API加载聊天历史
                const response = await fetch(this.apiEndpoints.sessions, {
                    method: 'GET',
                    headers: this.getAuthHeaders()
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data) {
                        this.conversations.clear();
                        result.data.forEach(conv => {
                            this.conversations.set(conv.id, {
                                id: conv.id,
                                title: conv.title || this.generateConversationTitle(conv.messages),
                                messages: conv.messages || [],
                                timestamp: conv.updated_at || conv.created_at
                            });
                        });
                        this.renderConversationsList();
                        return;
                    }
                }
            }
            
            // 降级到localStorage
            const conversations = JSON.parse(localStorage.getItem('chat_conversations') || '[]');
            this.conversations.clear();
            
            conversations.forEach(conv => {
                this.conversations.set(conv.id, conv);
            });

            this.renderConversationsList();

        } catch (error) {
            console.error('加载对话历史失败:', error);
            // 降级到localStorage
            const conversations = JSON.parse(localStorage.getItem('chat_conversations') || '[]');
            this.conversations.clear();
            
            conversations.forEach(conv => {
                this.conversations.set(conv.id, conv);
            });

            this.renderConversationsList();
        }
    }

    renderConversationsList() {
        const list = this.elements.conversationsList;
        if (!list) return;

        list.innerHTML = '';
        
        Array.from(this.conversations.values())
            .sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
            .forEach(conv => {
                const item = this.createConversationItem(conv);
                list.appendChild(item);
            });
    }

    createConversationItem(conversation) {
        const item = document.createElement('div');
        item.className = 'conversation-item';
        item.dataset.conversationId = conversation.id;
        
        const isActive = conversation.id === this.state.currentConversationId;
        if (isActive) {
            item.classList.add('active');
        }

        const time = new Date(conversation.timestamp).toLocaleDateString('zh-CN');
        const messageCount = conversation.messages.length;

        item.innerHTML = `
            <div class="conversation-info">
                <div class="conversation-title">${conversation.title}</div>
                <div class="conversation-meta">
                    <span class="conversation-time">${time}</span>
                    <span class="conversation-count">${messageCount} 条消息</span>
                </div>
            </div>
            <div class="conversation-actions">
                <button class="action-btn delete-btn" title="删除">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        // 点击加载对话
        item.addEventListener('click', (e) => {
            if (!e.target.closest('.conversation-actions')) {
                this.loadConversation(conversation.id);
            }
        });

        // 删除对话
        const deleteBtn = item.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.deleteConversation(conversation.id);
        });

        return item;
    }

    async loadConversation(conversationId) {
        try {
            const conversation = this.conversations.get(conversationId);
            if (!conversation) return;

            this.state.currentConversationId = conversationId;
            this.currentMessages = [...conversation.messages];

            // 清空并重新渲染消息
            if (this.elements.messagesList) {
                this.elements.messagesList.innerHTML = '';
                
                this.currentMessages.forEach(message => {
                    this.addMessage(message);
                });
            }

            // 更新对话列表状态
            this.updateConversationListState();

            this.emit('conversation_loaded', conversation);

        } catch (error) {
            console.error('加载对话失败:', error);
            this.showToast('加载对话失败', 'error');
        }
    }

    updateConversationListState() {
        const items = this.elements.conversationsList?.querySelectorAll('.conversation-item');
        items?.forEach(item => {
            const isActive = item.dataset.conversationId === this.state.currentConversationId;
            item.classList.toggle('active', isActive);
        });
    }    async deleteConversation(conversationId) {
        if (!confirm('确定要删除这个对话吗？')) return;

        try {
            if (this.state.isAuthenticated) {
                // 首先尝试API删除
                try {
                    await this.deleteConversationFromServer(conversationId);
                } catch (apiError) {
                    console.warn('API删除失败，继续本地删除:', apiError);
                    // 继续执行本地删除，即使API失败
                }
            }

            // 本地删除（无论API是否成功都执行）
            this.conversations.delete(conversationId);
            
            // 更新localStorage
            const conversations = Array.from(this.conversations.values());
            localStorage.setItem('chat_conversations', JSON.stringify(conversations));

            // 重新渲染列表
            this.renderConversationsList();

            // 如果删除的是当前对话，创建新对话
            if (conversationId === this.state.currentConversationId) {
                await this.createNewConversation();
            }

            this.showToast('对话已删除', 'success');

        } catch (error) {
            console.error('删除对话失败:', error);
            this.showToast('删除对话失败', 'error');
        }
    }

    async deleteConversationFromServer(conversationId) {
        const response = await fetch(`${this.apiEndpoints.sessions}/${conversationId}`, {
            method: 'DELETE',
            headers: this.getAuthHeaders()
        });

        if (!response.ok) {
            throw new Error(`删除会话API失败: ${response.status}`);
        }

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || '删除会话失败');
        }

        return result;
    }

    /**
     * 清理资源
     */
    cleanup() {
        // 关闭WebSocket连接
        if (this.wsConnection) {
            this.wsConnection.close();
        }

        // 清理量子效果
        if (this.quantumIntegrator) {
            this.quantumIntegrator.destroy();
        }

        // 保存最后状态
        this.saveConversation();

        
    }    /**
     * 加载设置
     */
    async loadSettings() {
        try {
            if (this.state.isAuthenticated) {
                // 尝试从API加载设置
                try {
                    const response = await fetch(`${this.apiEndpoints.preferences}/settings`, {
                        method: 'GET',
                        headers: this.getAuthHeaders()
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success && result.data) {
                            Object.assign(this.options, result.data);
                            return;
                        }
                    }
                } catch (apiError) {
                    console.warn('API加载设置失败，使用localStorage:', apiError);
                }
            }

            // 降级到localStorage
            const settings = JSON.parse(localStorage.getItem('chat_settings') || '{}');
            Object.assign(this.options, settings);
        } catch (error) {
            console.error('加载设置失败:', error);
        }
    }

    /**
     * 保存设置
     */
    async saveSettings(settings = {}) {
        try {
            // 更新本地选项
            Object.assign(this.options, settings);

            if (this.state.isAuthenticated) {
                // 尝试保存到API
                try {
                    const response = await fetch(`${this.apiEndpoints.preferences}/settings`, {
                        method: 'POST',
                        headers: this.getAuthHeaders(),
                        body: JSON.stringify(settings)
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success) {
                            // 同时保存到localStorage作为备份
                            localStorage.setItem('chat_settings', JSON.stringify(this.options));
                            return;
                        }
                    }
                } catch (apiError) {
                    console.warn('API保存设置失败，使用localStorage:', apiError);
                }
            }

            // 降级到localStorage
            localStorage.setItem('chat_settings', JSON.stringify(this.options));
        } catch (error) {
            console.error('保存设置失败:', error);
        }
    }

    /**
     * 其他功能方法占位符
     */
    async sendTypingIndicator() {
        // 发送打字指示器
    }

    handleVisibilityChange() {
        // 处理页面可见性变化
    }

    handleQuickAction(action) {
        // 处理快捷操作
        const prompts = {
            explain: '请解释一下：',
            code: '请帮我写代码：',
            translate: '请翻译：',
            summarize: '请总结：'
        };

        const prompt = prompts[action];
        if (prompt && this.elements.messageInput) {
            this.elements.messageInput.value = prompt;
            this.elements.messageInput.focus();
        }
    }

    async regenerateMessage(messageData) {
        // 重新生成消息
        this.showToast('重新生成功能开发中...', 'info');
    }

    async speakMessage(messageData) {
        // 朗读消息
        this.showToast('语音朗读功能开发中...', 'info');
    }

    async rateMessage(messageId, rating) {
        // 评价消息
        this.showToast(`已${rating === 'like' ? '点赞' : '点踩'}`, 'success');
    }

    async editMessage(messageData) {
        // 编辑消息
        this.showToast('编辑消息功能开发中...', 'info');
    }

    async retryLastMessage() {
        // 重试最后一条消息
        const lastUserMessage = this.currentMessages
            .slice()
            .reverse()
            .find(msg => msg.type === 'user');
        
        if (lastUserMessage) {
            await this.sendMessage(lastUserMessage.content);
        }
    }

    toggleVoiceInput() {
        // 切换语音输入
        this.showToast('语音输入功能开发中...', 'info');
    }

    filterConversations(searchTerm) {
        // 过滤对话
        const items = this.elements.conversationsList?.querySelectorAll('.conversation-item');
        items?.forEach(item => {
            const title = item.querySelector('.conversation-title')?.textContent || '';
            const visible = title.toLowerCase().includes(searchTerm.toLowerCase());
            item.style.display = visible ? 'block' : 'none';
        });
    }

    focusConversationSearch() {
        const searchInput = document.getElementById('conversations-search');
        searchInput?.focus();
    }

    showShortcutsHelp() {
        this.showToast('快捷键帮助功能开发中...', 'info');
    }

    handleWebSocketMessage(event) {
        try {
            const data = JSON.parse(event.data);
            this.emit('websocket_message', data);
        } catch (error) {
            console.error('处理WebSocket消息失败:', error);
        }
    }

    async saveConversationToServer(conversationData) {
        // 保存对话到服务器
        try {
            await fetch('/api/conversations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...this.getAuthHeaders()
                },
                body: JSON.stringify(conversationData)
            });
        } catch (error) {
            console.error('保存对话到服务器失败:', error);
        }
    }
}

/**
 * 量子集成器类
 */
class QuantumIntegrator {
    constructor(options = {}) {
        this.options = {
            container: null,
            particleCount: 30,
            enableInteraction: true,
            ...options
        };
        
        this.particles = null;
        this.isInitialized = false;
    }

    async init() {
        if (window.QuantumParticles) {
            this.particles = new window.QuantumParticles(this.options.container, {
                count: this.options.particleCount,
                interactive: this.options.enableInteraction
            });
            
            await this.particles.init();
            this.isInitialized = true;
        }
    }

    trigger(type, data = {}) {
        if (!this.isInitialized || !this.particles) return;

        switch (type) {
            case 'user_message':
                this.particles.pulse({ color: '#3b82f6', intensity: 0.7 });
                break;
            case 'ai_response':
                this.particles.wave({ color: '#10b981', intensity: 0.8 });
                break;
            case 'error':
                this.particles.shake({ color: '#ef4444', intensity: 0.6 });
                break;
            default:
                this.particles.glow({ intensity: 0.5 });
        }
    }

    destroy() {
        if (this.particles) {
            this.particles.destroy();
            this.particles = null;
            this.isInitialized = false;
        }
    }
}

// 导出
window.ChatSystem = ChatSystem;
window.QuantumIntegrator = QuantumIntegrator;


