// 历史记录管理模块
export class HistoryManager {
    constructor() {
        this.sessions = [];
        this.currentSession = null;
        this.callbacks = {
            onSelectHistory: () => console.warn('onSelectHistory callback not set')
        };
    }

    // 设置回调函数
    setCallback(name, fn) {
        if (typeof fn === 'function') {
            this.callbacks[name] = fn;
        } else {
            console.error(`Invalid callback for ${name}`);
        }
    }

    // 安全调用回调
    callCallback(name, ...args) {
        try {
            return this.callbacks[name](...args);
        } catch (error) {
            console.error(`Error in ${name} callback:`, error);
        }
    }

    // 加载会话列表
    async loadSessions(userName) {
        try {
            // 先从本地存储加载
            this.loadFromLocalStorage();

            // 从服务器加载最新数据
            const response = await fetch('/api/chat/sessions');
            if (!response.ok) {
                throw new Error('加载会话失败');
            }
            
            const data = await response.json();
            if (data.success) {
                this.sessions = data.sessions;
                this.saveToLocalStorage();
            }
            
            return this.sessions;
        } catch (error) {
            console.error('加载会话失败:', error);
            // 如果服务器请求失败，使用本地数据
            return this.sessions;
        }
    }

    // 获取会话详情
    async getSessionDetails(sessionId) {
        try {
            // 先查找本地缓存
            const cachedSession = this.sessions.find(s => s.id === sessionId);
            if (cachedSession && cachedSession.messages) {
                return cachedSession;
            }

            // 从服务器加载完整会话数据
            const response = await fetch(`/api/chat/sessions/${sessionId}`);
            if (!response.ok) {
                throw new Error('加载会话详情失败');
            }

            const data = await response.json();
            if (data.success) {
                // 更新本地缓存
                const index = this.sessions.findIndex(s => s.id === sessionId);
                if (index !== -1) {
                    this.sessions[index] = { ...this.sessions[index], ...data.session };
                } else {
                    this.sessions.push(data.session);
                }
                this.saveToLocalStorage();
                return data.session;
            }
        } catch (error) {
            console.error('加载会话详情失败:', error);
            throw error;
        }
    }

    // 创建新会话
    async createSession() {
        try {
            const response = await fetch('/api/chat/sessions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('创建会话失败');
            }

            const data = await response.json();
            if (data.success) {
                const newSession = {
                    id: data.sessionId,
                    messages: [],
                    createdAt: new Date().toISOString(),
                    lastActive: new Date().toISOString()
                };
                
                this.sessions.unshift(newSession);
                this.currentSession = newSession;
                this.saveToLocalStorage();
                
                return newSession;
            }
        } catch (error) {
            console.error('创建会话失败:', error);
            throw error;
        }
    }

    // 删除会话
    async deleteSession(sessionId) {
        try {
            const response = await fetch(`/api/chat/sessions/${sessionId}`, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error('删除会话失败');
            }

            const data = await response.json();
            if (data.success) {
                // 从本地移除
                this.sessions = this.sessions.filter(s => s.id !== sessionId);
                if (this.currentSession?.id === sessionId) {
                    this.currentSession = null;
                }
                this.saveToLocalStorage();
                return true;
            }
        } catch (error) {
            console.error('删除会话失败:', error);
            throw error;
        }
    }

    // 添加消息到当前会话
    async addMessage(message) {
        if (!this.currentSession) {
            await this.createSession();
        }

        try {
            const response = await fetch(`/api/chat/sessions/${this.currentSession.id}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(message)
            });

            if (!response.ok) {
                throw new Error('添加消息失败');
            }

            const data = await response.json();
            if (data.success) {
                // 更新本地数据
                this.currentSession.messages.push(message);
                this.currentSession.lastActive = new Date().toISOString();
                this.saveToLocalStorage();

                // 触发回调
                this.callCallback('onHistoryChange', this.currentSession);
                return true;
            }
        } catch (error) {
            console.error('添加消息失败:', error);
            throw error;
        }
    }

    // 保存到本地存储
    saveToLocalStorage() {
        try {
            localStorage.setItem('chatSessions', JSON.stringify({
                sessions: this.sessions,
                currentSession: this.currentSession,
                timestamp: new Date().toISOString()
            }));
        } catch (error) {
            console.error('保存到本地存储失败:', error);
        }
    }

    // 从本地存储加载
    loadFromLocalStorage() {
        try {
            const data = JSON.parse(localStorage.getItem('chatSessions'));
            if (data && data.timestamp) {
                // 检查数据是否过期（24小时）
                const expired = new Date() - new Date(data.timestamp) > 24 * 60 * 60 * 1000;
                if (!expired) {
                    this.sessions = data.sessions || [];
                    this.currentSession = data.currentSession;
                }
            }
        } catch (error) {
            console.error('从本地存储加载失败:', error);
        }
    }
}

// 初始化函数
export function initHistoryManager() {
    return new HistoryManager();
}