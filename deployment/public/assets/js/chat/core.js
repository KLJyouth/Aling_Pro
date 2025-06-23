// 聊天核心模块
export class ChatCore {
    constructor() {
        this.messageHistory = [];
        this.currentState = 'idle';
        this.currentUser = null;
        this.messageQueue = [];
        this.currentSession = null;
        this.isInitialized = false;
    }

    // 初始化方法
    async initialize() {
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
                } catch (e) {
                    console.warn('Invalid chat history in localStorage');
                    localStorage.removeItem('chatHistory');
                }
            }

            // 加载当前会话信息
            const currentSessionId = localStorage.getItem('currentSessionId');
            if (currentSessionId) {
                this.currentSession = currentSessionId;
            }

            this.isInitialized = true;
            return true;
        } catch (error) {
            console.error('Failed to initialize ChatCore:', error);
            return false;
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
        this._saveMessageToLocal();
        
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
        this._saveMessageToLocal();
        
        return messageObj;
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
    clearHistory() {
        this.messageHistory = [];
        localStorage.removeItem('chatHistory');
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
        } catch (error) {
            console.error('Error saving chat history:', error);
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