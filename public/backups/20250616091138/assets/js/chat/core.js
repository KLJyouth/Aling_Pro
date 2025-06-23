// 聊天核心模块
export class ChatCore {
    constructor() {
        this.messageHistory = [];
        this.currentState = 'idle';
        this.currentUser = null;
        this.messageQueue = [];
        this.currentSession = null;
        this.isInitialized = false;
        this.isAuthenticated = false;
        this.apiEndpoints = {
            chat: '/api/v1/chat/messages',
            sessions: '/api/v1/chat/sessions',
            conversations: '/api/v1/chat/conversations',
            auth: '/api/v1/auth/check'
        };
    }

    // 初始化方法
    async initialize() {
        try {
            // 检查用户认证状态
            await this.checkUserAuthentication();
            
            if (this.isAuthenticated) {
                // 认证用户从数据库加载历史记录
                console.log('🔐 用户已认证，从API加载聊天历史');
                await this.loadChatHistoryFromAPI();
            } else {
                // 未认证用户从本地存储加载
                console.log('👤 访客用户，从本地存储加载聊天历史');
                await this.loadChatHistoryFromLocal();
            }

            this.isInitialized = true;
            return true;
        } catch (error) {
            console.error('Failed to initialize ChatCore:', error);
            // 降级到本地存储模式
            await this.loadChatHistoryFromLocal();
            this.isInitialized = true;
            return false;
        }
    }

    // 检查用户认证状态
    async checkUserAuthentication() {
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
                const authenticated = result.success && result.data?.authenticated;
                this.isAuthenticated = authenticated;
                if (authenticated && result.data.user) {
                    this.currentUser = result.data.user;
                }
                return authenticated;
            }
        } catch (error) {
            console.warn('认证检查失败:', error);
        }
        this.isAuthenticated = false;
        return false;
    }

    // checkAuthentication别名方法，保持API一致性
    async checkAuthentication() {
        return await this.checkUserAuthentication();
    }

    // 从API加载聊天历史
    async loadChatHistoryFromAPI() {
        try {
            const response = await fetch(`${this.apiEndpoints.conversations}?limit=1`, {
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
                        this.messageHistory = conversation.messages.map(msg => ({
                            id: msg.id || Date.now().toString(),
                            type: msg.sender === 'user' ? 'user' : 'ai',
                            content: msg.content,
                            timestamp: new Date(msg.created_at || Date.now())
                        }));
                        this.currentSession = conversation.id;
                        localStorage.setItem('currentSessionId', conversation.id);
                        console.log('✅ 从API加载聊天历史');
                        return true;
                    }
                }
            }
        } catch (error) {
            console.warn('从API加载聊天历史失败:', error);
        }
        return false;
    }

    // 从本地存储加载聊天历史
    async loadChatHistoryFromLocal() {
        try {
            // 检查本地存储的用户信息
            const userInfo = localStorage.getItem('currentUser');
            if (userInfo) {
                try {
                    this.currentUser = JSON.parse(userInfo);
                } catch (e) {
                    console.warn('Invalid user data in localStorage');
                    localStorage.removeItem('currentUser');
                }
            }

            // 加载本地存储的历史记录
            const storedHistory = localStorage.getItem('chatHistory');
            if (storedHistory) {
                try {
                    this.messageHistory = JSON.parse(storedHistory);
                    console.log('📱 从本地存储加载聊天历史');
                } catch (e) {
                    console.warn('Invalid chat history in localStorage');
                    localStorage.removeItem('chatHistory');
                    this.messageHistory = [];
                }
            }

            // 加载当前会话信息
            const currentSessionId = localStorage.getItem('currentSessionId');
            if (currentSessionId) {
                this.currentSession = currentSessionId;
            }
        } catch (error) {
            console.error('Failed to load chat history from localStorage:', error);
            this.messageHistory = [];
        }
    }

    // 处理用户消息
    async processUserMessage(message) {
        const messageObj = {
            id: Date.now().toString(),
            type: 'user',
            content: message,
            timestamp: new Date()
        };
        
        this.messageHistory.push(messageObj);
        this.currentState = 'waitingForResponse';
        
        // 保存消息到数据库或本地存储
        await this.saveMessage(messageObj);
        
        return messageObj;
    }

    // 处理AI响应
    async processResponse(response) {
        const messageObj = {
            id: Date.now().toString(),
            type: 'ai',
            content: response.content,
            timestamp: new Date()
        };
        
        this.messageHistory.push(messageObj);
        this.currentState = 'idle';
        
        // 保存消息到数据库或本地存储
        await this.saveMessage(messageObj);
        
        return messageObj;
    }

    // 保存消息（智能选择存储方式）
    async saveMessage(messageObj) {
        if (this.isAuthenticated && this.currentUser) {
            try {
                await this.saveMessageToAPI(messageObj);
                console.log('💾 消息已保存到数据库');
            } catch (error) {
                console.warn('Failed to save to database, falling back to localStorage:', error);
                this._saveMessageToLocal();
            }
        } else {
            console.log('📱 消息已保存到本地存储');
            this._saveMessageToLocal();
        }
    }

    // 保存消息到API
    async saveMessageToAPI(messageObj) {
        try {
            // 如果没有会话ID，创建新会话
            if (!this.currentSession) {
                await this.createNewSession();
            }

            const response = await fetch(this.apiEndpoints.conversations, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    conversation_id: this.currentSession,
                    sender: messageObj.type === 'ai' ? 'assistant' : 'user',
                    content: messageObj.content,
                    metadata: {
                        timestamp: messageObj.timestamp,
                        id: messageObj.id
                    }
                })
            });

            if (!response.ok) {
                throw new Error(`API request failed: ${response.status}`);
            }

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to save message');
            }

            return result;
        } catch (error) {
            console.error('Error saving message to API:', error);
            throw error;
        }
    }

    // 创建新会话
    async createNewSession() {
        try {
            const response = await fetch(this.apiEndpoints.conversations, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    title: `Chat Session ${new Date().toLocaleString()}`,
                    model: 'deepseek-chat'
                })
            });

            if (!response.ok) {
                throw new Error(`Failed to create session: ${response.status}`);
            }

            const result = await response.json();
            if (result.success && result.data && result.data.id) {
                this.currentSession = result.data.id;
                localStorage.setItem('currentSessionId', result.data.id);
                return result.data.id;
            }

            throw new Error('Invalid session creation response');
        } catch (error) {
            console.error('Error creating new session:', error);
            throw error;
        }
    }

    // 显示历史消息
    displayHistory(messages) {
        if (!Array.isArray(messages)) return [];
        this.messageHistory = messages.map(msg => ({
            id: msg.id || Date.now().toString(),
            type: msg.role || msg.type,
            content: msg.content,
            timestamp: new Date(msg.timestamp || Date.now())
        }));
        return this.messageHistory;
    }

    // 获取当前状态
    getState() {
        return this.currentState;
    }

    // 处理历史消息
    async processHistoryMessage(history) {
        if (!Array.isArray(history)) return [];
        
        return history.map(msg => ({
            id: msg.id || Date.now().toString(),
            type: msg.role || msg.type,
            content: msg.content,
            timestamp: new Date(msg.timestamp || Date.now())
        }));
    }

    // 加载用户设置
    async loadUserSettings() {
        return {
            theme: localStorage.getItem('theme') || 'light',
            language: localStorage.getItem('language') || 'zh',
            enableVoice: localStorage.getItem('enableVoice') === 'true',
            enableTTS: localStorage.getItem('enableTTS') === 'true',
            model: localStorage.getItem('model') || 'deepseek-chat'
        };
    }

    // 保存用户设置
    async saveUserSettings(settings) {
        Object.entries(settings).forEach(([key, value]) => {
            localStorage.setItem(key, value);
        });
        
        // 应用设置
        if (settings.theme) {
            document.documentElement.setAttribute('data-bs-theme', settings.theme);
        }
        
        return settings;
    }

    // 设置当前用户
    setCurrentUser(user) {
        this.currentUser = user;
        localStorage.setItem('currentUser', JSON.stringify(user));
    }

    // 清空历史记录
    async clearHistory() {
        this.messageHistory = [];
        
        if (this.isAuthenticated && this.currentSession) {
            try {
                // 从数据库清除会话历史
                const response = await fetch(`${this.apiEndpoints.conversations}/${this.currentSession}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    this.currentSession = null;
                    localStorage.removeItem('currentSessionId');
                    console.log('🗑️ 已从数据库清除聊天历史');
                }
            } catch (error) {
                console.warn('Failed to clear history from database:', error);
            }
        }
        
        // 清除本地存储
        localStorage.removeItem('chatHistory');
        localStorage.removeItem('currentSessionId');
        console.log('🗑️ 已清除本地聊天历史');
    }

    // 重试上次失败的消息
    async retryLastMessage() {
        const lastUserMessage = this.messageHistory
            .filter(msg => msg.type === 'user')
            .pop();
            
        if (lastUserMessage) {
            return await this.processUserMessage(lastUserMessage.content);
        }
        
        throw new Error('没有找到可重试的消息');
    }
    
    // 清除错误状态
    clearErrors() {
        this.messageHistory = this.messageHistory.filter(msg => msg.type !== 'error');
        this._saveMessageToLocal();
    }
    
    // 获取会话统计信息
    getSessionStats() {
        const userMessages = this.messageHistory.filter(msg => msg.type === 'user').length;
        const aiMessages = this.messageHistory.filter(msg => msg.type === 'assistant' || msg.type === 'ai').length;
        const errors = this.messageHistory.filter(msg => msg.type === 'error').length;
        
        return {
            totalMessages: this.messageHistory.length,
            userMessages,
            aiMessages,
            errors,
            sessionDuration: this._getSessionDuration()
        };
    }
    
    // 获取会话持续时间
    _getSessionDuration() {
        if (this.messageHistory.length === 0) return 0;
        
        const firstMessage = this.messageHistory[0];
        const lastMessage = this.messageHistory[this.messageHistory.length - 1];
        
        return new Date(lastMessage.timestamp) - new Date(firstMessage.timestamp);
    }
    
    // 导出聊天历史
    exportHistory(format = 'json') {
        switch (format) {
            case 'json':
                return JSON.stringify(this.messageHistory, null, 2);
            case 'text':
                return this.messageHistory
                    .map(msg => `[${msg.timestamp}] ${msg.type}: ${msg.content}`)
                    .join('\n');
            case 'markdown':
                return this.messageHistory
                    .map(msg => {
                        const type = msg.type === 'user' ? '**用户**' : '**AI助手**';
                        return `${type}: ${msg.content}\n`;
                    })
                    .join('\n');
            default:
                throw new Error('不支持的导出格式');
        }
    }

    // 私有方法：保存消息到本地存储
    _saveMessageToLocal() {
        try {
            // 只保存最近的100条消息
            const recentHistory = this.messageHistory.slice(-100);
            localStorage.setItem('chatHistory', JSON.stringify(recentHistory));
            
            // 保存当前会话ID
            if (this.currentSession) {
                localStorage.setItem('currentSessionId', this.currentSession);
            }
        } catch (error) {
            console.error('Error saving chat history to localStorage:', error);
        }
    }

    // 获取聊天历史（从数据库或本地存储）
    async getChatHistory(sessionId = null) {
        if (this.isAuthenticated) {
            try {
                return await this.loadChatHistoryFromAPI();
            } catch (error) {
                console.warn('Failed to load from API, using local storage:', error);
            }
        }
        
        // 降级到本地存储
        return this.messageHistory;
    }

    // 重新同步到数据库（用于认证后迁移本地数据）
    async syncToDatabase() {
        if (!this.isAuthenticated || this.messageHistory.length === 0) {
            return false;
        }

        try {
            // 创建新会话
            await this.createNewSession();
            
            // 逐个上传消息
            for (const message of this.messageHistory) {
                await this.saveMessageToAPI(message);
            }
            
            // 清除本地存储（数据已同步到数据库）
            localStorage.removeItem('chatHistory');
            console.log('🔄 本地数据已同步到数据库');
            
            return true;
        } catch (error) {
            console.error('Failed to sync to database:', error);
            return false;
        }
    }
}

// 初始化函数
export function initChat() {
    // 创建新实例并初始化
    const instance = new ChatCore();
    instance.initialize().catch(err => {
        console.error('ChatCore 初始化失败:', err);
    });
    return instance;
}
