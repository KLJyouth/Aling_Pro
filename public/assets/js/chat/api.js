// API交互模块
export class ChatAPI {
    constructor() {
        this.baseUrl = '/api';
        this.token = localStorage.getItem('token');        this.endpoints = {
            login: '/auth/login',
            sendMessage: '/chat/chat',
            loadHistory: '/history',
            getSessions: '/history/sessions',
            speechRecognize: '/speech/recognize',
            speechSynthesize: '/speech/synthesize',
            imageGenerate: '/image/generate',
            userSettings: '/settings/user'
        };
        this.isInitialized = false;
    }    // 初始化API
    async initialize() {
        try {
            // 检查服务器状态
            try {
                const response = await fetch('/api/v1/status');
                if (!response.ok) {
                    console.warn('服务器状态检查失败，但继续初始化');
                }
            } catch (error) {
                console.warn('无法连接到服务器，但继续初始化:', error.message);
            }
            
            // 验证token（如果存在）
            if (this.token) {
                try {
                    await this.validateToken();
                } catch (error) {
                    console.warn('Token验证失败，清除token:', error.message);
                    this.clearToken();
                }
            }
            
            this.isInitialized = true;
            return true;
        } catch (error) {
            console.error('API初始化失败:', error);
            // 即使初始化失败也设置为已初始化，允许继续使用
            this.isInitialized = true;
            return false;
        }
    }    // 验证token
    async validateToken() {
        try {
            return await this.request('/v1/auth/validate');
        } catch (error) {
            console.warn('Token验证失败:', error.message);
            throw error;
        }
    }

    // 清除token
    clearToken() {
        this.token = null;
        localStorage.removeItem('token');
    }

    // 设置认证token
    setToken(token) {
        this.token = token;
        localStorage.setItem('token', token);
    }

    // 获取请求头
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json'
        };
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        return headers;
    }

    // 通用请求方法
    async request(endpoint, options = {}) {
        if (!this.isInitialized) {
            throw new Error('API未初始化');
        }

        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                ...options,
                headers: {
                    ...this.getHeaders(),
                    ...(options.headers || {})
                }
            });

            // 处理401错误（未授权）
            if (response.status === 401) {
                this.clearToken();
                throw new Error('未登录或登录已过期');
            }

            // 处理其他错误
            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                throw new Error(errorData?.message || `请求失败: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API请求错误:', error);
            this._handleError(error);
            throw error;
        }
    }

    // 登录
    async login(credentials) {
        try {
            const data = await this.request(this.endpoints.login, {
                method: 'POST',
                body: JSON.stringify(credentials)
            });

            if (data.token) {
                this.setToken(data.token);
                return {
                    success: true,
                    token: data.token,
                    user: data.user
                };
            }

            return {
                success: false,
                error: data.error || '登录失败，未返回有效令牌'
            };
        } catch (error) {
            console.error('登录过程发生错误:', error);
            return {
                success: false,
                error: error.message || '登录请求失败'
            };
        }
    }    // 发送消息
    async sendMessage(message) {
        try {
            const requestBody = {
                text: message, // 匹配后端的参数名 "text"
                modelType: 'deepseek-chat', // 默认模型类型
                stream: false, // 默认非流式
                session_id: localStorage.getItem('currentSessionId') || null,
                timestamp: new Date().toISOString()
            };

            

            // 检查是否是访客模式
            const isGuestMode = localStorage.getItem('guestMode') === 'true';
            
            if (isGuestMode && !this.token) {
                // 访客模式下返回模拟响应
                return this._getGuestModeResponse(message);
            }

            const response = await this.request(this.endpoints.sendMessage, {
                method: 'POST',
                body: JSON.stringify(requestBody)
            });

            // 如果是新会话，保存会话ID
            if (response.session_id) {
                localStorage.setItem('currentSessionId', response.session_id);
            }

            // 验证响应格式
            if (!response.response && !response.content && !response.assistantText) {
                throw new Error('AI响应格式异常，请稍后重试');
            }

            return {
                content: response.response || response.assistantText || response.content || '',
                session_id: response.session_id,
                timestamp: response.timestamp || new Date().toISOString(),
                modelUsed: response.modelUsed || 'deepseek-chat'
            };
        } catch (error) {
            console.error('发送消息失败:', error);
            
            // 根据错误类型提供不同的提示
            let userFriendlyMessage = '发送消息失败';
            
            if (error.message.includes('401') || error.message.includes('未登录') || error.message.includes('jwt')) {
                userFriendlyMessage = '需要登录才能使用完整的聊天功能';
                this.clearToken();
            } else if (error.message.includes('网络')) {
                userFriendlyMessage = '网络连接异常，请检查网络后重试';
            } else if (error.message.includes('超时')) {
                userFriendlyMessage = 'AI正在思考中，请稍后重试';
            } else if (error.message.includes('API')) {
                userFriendlyMessage = 'AI服务暂时不可用，请稍后重试';
            }
            
            throw new Error(userFriendlyMessage);
        }
    }

    // 访客模式响应
    _getGuestModeResponse(message) {
        const responses = [
            '感谢您使用访客模式体验 AlingAi！这是一个演示回复。要获得完整的AI对话体验，请登录您的账户。',
            '您好！这是访客模式的示例回复。登录后可以享受更智能的AI对话服务。',
            '访客模式下的功能有限。要体验完整的AI功能，建议您注册并登录。',
            '这是一个演示回复。在访客模式下，您可以体验基本的界面操作，但AI回复功能需要登录后使用。'
        ];
        
        const randomResponse = responses[Math.floor(Math.random() * responses.length)];
        
        return Promise.resolve({
            content: randomResponse,
            session_id: 'guest-session',
            timestamp: new Date().toISOString(),
            modelUsed: 'guest-mode'
        });
    }

    // 加载历史消息
    async loadHistory(historyId) {
        return await this.request(`${this.endpoints.loadHistory}/${historyId}`);
    }

    // 获取会话列表
    async getSessions() {
        return await this.request(this.endpoints.getSessions);
    }

    // 语音识别
    async speechToText(audioBlob) {
        const formData = new FormData();
        formData.append('audio', audioBlob);

        return await this.request(this.endpoints.speechRecognize, {
            method: 'POST',
            body: formData,
            headers: {
                'Authorization': this.token ? `Bearer ${this.token}` : ''
            }
        });
    }

    // 生成图片
    async generateImage(prompt) {
        return await this.request(this.endpoints.imageGenerate, {
            method: 'POST',
            body: JSON.stringify({
                prompt,
                timestamp: new Date().toISOString()
            })
        });
    }

    // 获取用户设置
    async getUserSettings() {
        return await this.request(this.endpoints.userSettings);
    }

    // 更新用户设置
    async updateUserSettings(settings) {
        return await this.request(this.endpoints.userSettings, {
            method: 'PUT',
            body: JSON.stringify(settings)
        });
    }

    // 处理错误
    _handleError(error) {
        // 触发全局错误事件
        const event = new CustomEvent('api-error', {
            detail: {
                message: error.message,
                timestamp: new Date().toISOString()
            }
        });
        window.dispatchEvent(event);

        // 如果是认证错误，触发登出事件
        if (error.message.includes('未登录') || error.message.includes('过期')) {
            window.dispatchEvent(new Event('logout'));
        }
    }
}

// 初始化API
export function initAPI() {
    return new ChatAPI();
}