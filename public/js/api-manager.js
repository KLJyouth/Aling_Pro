/**
 * AlingAi Pro - API 管理模块
 * 统一的API请求管理，支持认证、缓存、重试和错误处理
 * 
 * @version 1.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

class APIManager {
    constructor(options = {}) {
        this.options = {
            baseURL: '/api',
            timeout: 30000,
            maxRetries: 3,
            retryDelay: 1000,
            enableCache: true,
            cacheExpiry: 300000, // 5分钟
            enableOffline: true,
            ...options
        };

        this.cache = new Map();
        this.requestQueue = new Map();
        this.abortControllers = new Map();
        this.isOnline = navigator.onLine;
        this.offlineQueue = [];

        this.init();
    }

    /**
     * 初始化API管理器
     */
    init() {
        // 监听网络状态
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.processOfflineQueue();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
        });

        
    }

    /**
     * 发送GET请求
     */
    async get(endpoint, options = {}) {
        return this.request('GET', endpoint, null, options);
    }

    /**
     * 发送POST请求
     */
    async post(endpoint, data = null, options = {}) {
        return this.request('POST', endpoint, data, options);
    }

    /**
     * 发送PUT请求
     */
    async put(endpoint, data = null, options = {}) {
        return this.request('PUT', endpoint, data, options);
    }

    /**
     * 发送DELETE请求
     */
    async delete(endpoint, options = {}) {
        return this.request('DELETE', endpoint, null, options);
    }

    /**
     * 发送PATCH请求
     */
    async patch(endpoint, data = null, options = {}) {
        return this.request('PATCH', endpoint, data, options);
    }

    /**
     * 通用请求方法
     */
    async request(method, endpoint, data = null, options = {}) {
        const requestOptions = {
            cache: this.options.enableCache,
            timeout: this.options.timeout,
            maxRetries: this.options.maxRetries,
            retryDelay: this.options.retryDelay,
            ...options
        };

        const url = this.buildURL(endpoint);
        const cacheKey = this.getCacheKey(method, url, data);

        // 检查缓存
        if (method === 'GET' && requestOptions.cache) {
            const cached = this.getFromCache(cacheKey);
            if (cached) {
                return cached;
            }
        }

        // 检查是否有相同的请求正在进行
        if (this.requestQueue.has(cacheKey)) {
            return this.requestQueue.get(cacheKey);
        }

        // 创建请求Promise
        const requestPromise = this.executeRequest(method, url, data, requestOptions);
        
        // 添加到请求队列
        this.requestQueue.set(cacheKey, requestPromise);

        try {
            const response = await requestPromise;
            
            // 缓存GET请求的响应
            if (method === 'GET' && requestOptions.cache && response.success) {
                this.setCache(cacheKey, response);
            }

            return response;

        } finally {
            // 从请求队列中移除
            this.requestQueue.delete(cacheKey);
        }
    }

    /**
     * 执行HTTP请求
     */
    async executeRequest(method, url, data, options) {
        // 如果离线且启用离线模式，添加到离线队列
        if (!this.isOnline && this.options.enableOffline && method !== 'GET') {
            return this.queueOfflineRequest(method, url, data, options);
        }

        const abortController = new AbortController();
        const requestId = this.generateRequestId();
        
        this.abortControllers.set(requestId, abortController);

        // 设置超时
        const timeoutId = setTimeout(() => {
            abortController.abort();
        }, options.timeout);

        try {
            const response = await this.fetchWithRetry(method, url, data, {
                ...options,
                signal: abortController.signal
            });

            clearTimeout(timeoutId);
            return response;

        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new APIError('请求超时', 'TIMEOUT', { url, method });
            }
            
            throw error;

        } finally {
            this.abortControllers.delete(requestId);
        }
    }

    /**
     * 带重试的fetch请求
     */
    async fetchWithRetry(method, url, data, options, retryCount = 0) {
        try {
            const fetchOptions = this.buildFetchOptions(method, data, options);
            const response = await fetch(url, fetchOptions);

            if (!response.ok) {
                const errorData = await this.parseErrorResponse(response);
                throw new APIError(
                    errorData.message || `HTTP ${response.status}`,
                    errorData.code || 'HTTP_ERROR',
                    {
                        status: response.status,
                        statusText: response.statusText,
                        url,
                        method,
                        ...errorData
                    }
                );
            }

            const responseData = await this.parseResponse(response);
            return this.formatResponse(responseData, response);

        } catch (error) {
            // 网络错误或超时，尝试重试
            if (this.shouldRetry(error, retryCount, options.maxRetries)) {
                const delay = this.calculateRetryDelay(retryCount, options.retryDelay);
                await this.sleep(delay);
                return this.fetchWithRetry(method, url, data, options, retryCount + 1);
            }

            throw error;
        }
    }

    /**
     * 构建fetch选项
     */
    buildFetchOptions(method, data, options) {
        const fetchOptions = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...this.getDefaultHeaders(),
                ...options.headers
            },
            signal: options.signal
        };

        // 添加认证头
        const authHeader = this.getAuthHeader();
        if (authHeader) {
            fetchOptions.headers.Authorization = authHeader;
        }

        // 添加CSRF token
        const csrfToken = this.getCSRFToken();
        if (csrfToken) {
            fetchOptions.headers['X-CSRF-Token'] = csrfToken;
        }

        // 添加请求体
        if (data && !['GET', 'HEAD'].includes(method)) {
            if (data instanceof FormData) {
                delete fetchOptions.headers['Content-Type'];
                fetchOptions.body = data;
            } else {
                fetchOptions.body = JSON.stringify(data);
            }
        }

        return fetchOptions;
    }

    /**
     * 解析响应
     */
    async parseResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        
        if (contentType.includes('application/json')) {
            return response.json();
        } else if (contentType.includes('text/')) {
            return response.text();
        } else {
            return response.blob();
        }
    }

    /**
     * 解析错误响应
     */
    async parseErrorResponse(response) {
        try {
            const contentType = response.headers.get('content-type') || '';
            
            if (contentType.includes('application/json')) {
                return await response.json();
            } else {
                const text = await response.text();
                return { message: text };
            }
        } catch (error) {
            return {
                message: `HTTP ${response.status}: ${response.statusText}`,
                code: 'PARSE_ERROR'
            };
        }
    }

    /**
     * 格式化响应
     */
    formatResponse(data, response) {
        // 如果响应已经是标准格式，直接返回
        if (typeof data === 'object' && data !== null && 'success' in data) {
            return data;
        }

        // 否则包装成标准格式
        return {
            success: true,
            data: data,
            timestamp: new Date().toISOString(),
            status: response.status,
            headers: Object.fromEntries(response.headers.entries())
        };
    }

    /**
     * 判断是否应该重试
     */
    shouldRetry(error, retryCount, maxRetries) {
        if (retryCount >= maxRetries) {
            return false;
        }

        // 网络错误、超时错误或5xx服务器错误可以重试
        if (error.name === 'TypeError' || error.name === 'AbortError') {
            return true;
        }

        if (error instanceof APIError) {
            const status = error.details?.status;
            return status >= 500 || status === 429; // 服务器错误或限流
        }

        return false;
    }

    /**
     * 计算重试延迟
     */
    calculateRetryDelay(retryCount, baseDelay) {
        // 指数退避算法
        return baseDelay * Math.pow(2, retryCount) + Math.random() * 1000;
    }

    /**
     * 缓存管理
     */
    getCacheKey(method, url, data) {
        const key = `${method}:${url}`;
        if (data) {
            const dataStr = typeof data === 'string' ? data : JSON.stringify(data);
            return `${key}:${this.hashString(dataStr)}`;
        }
        return key;
    }

    getFromCache(key) {
        const cached = this.cache.get(key);
        if (cached && Date.now() - cached.timestamp < this.options.cacheExpiry) {
            return cached.data;
        }
        
        if (cached) {
            this.cache.delete(key);
        }
        
        return null;
    }

    setCache(key, data) {
        this.cache.set(key, {
            data: data,
            timestamp: Date.now()
        });

        // 清理过期缓存
        this.cleanExpiredCache();
    }

    cleanExpiredCache() {
        const now = Date.now();
        for (const [key, cached] of this.cache.entries()) {
            if (now - cached.timestamp >= this.options.cacheExpiry) {
                this.cache.delete(key);
            }
        }
    }

    clearCache() {
        this.cache.clear();
    }

    /**
     * 离线队列管理
     */
    queueOfflineRequest(method, url, data, options) {
        return new Promise((resolve, reject) => {
            this.offlineQueue.push({
                method,
                url,
                data,
                options,
                resolve,
                reject,
                timestamp: Date.now()
            });

            // 返回离线状态响应
            resolve({
                success: false,
                error: '当前离线，请求已加入队列',
                code: 'OFFLINE',
                queued: true
            });
        });
    }

    async processOfflineQueue() {
        

        const queue = [...this.offlineQueue];
        this.offlineQueue = [];

        for (const request of queue) {
            try {
                const response = await this.executeRequest(
                    request.method,
                    request.url,
                    request.data,
                    request.options
                );
                request.resolve(response);
            } catch (error) {
                request.reject(error);
            }
        }
    }

    /**
     * 工具方法
     */
    buildURL(endpoint) {
        if (endpoint.startsWith('http')) {
            return endpoint;
        }
        
        const baseURL = this.options.baseURL.replace(/\/$/, '');
        const path = endpoint.replace(/^\//, '');
        return `${baseURL}/${path}`;
    }

    getDefaultHeaders() {
        return {
            'X-Requested-With': 'XMLHttpRequest',
            'X-Client-Version': '1.0.0',
            'X-Client-Platform': navigator.platform,
            'X-Timestamp': Date.now().toString()
        };
    }

    getAuthHeader() {
        const token = this.getToken();
        return token ? `Bearer ${token}` : null;
    }

    getToken() {
        return localStorage.getItem('auth_token') || 
               sessionStorage.getItem('auth_token');
    }

    getCSRFToken() {
        return window.APP_CONFIG?.csrfToken || 
               document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    generateRequestId() {
        return `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    hashString(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // 转换为32位整数
        }
        return hash.toString(36);
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * 请求取消
     */
    cancelRequest(requestId) {
        const controller = this.abortControllers.get(requestId);
        if (controller) {
            controller.abort();
            this.abortControllers.delete(requestId);
            return true;
        }
        return false;
    }

    cancelAllRequests() {
        for (const [id, controller] of this.abortControllers.entries()) {
            controller.abort();
        }
        this.abortControllers.clear();
    }

    /**
     * 认证相关方法
     */
    setToken(token) {
        localStorage.setItem('auth_token', token);
    }

    removeToken() {
        localStorage.removeItem('auth_token');
        sessionStorage.removeItem('auth_token');
    }

    isAuthenticated() {
        return !!this.getToken();
    }

    /**
     * 统计信息
     */
    getStats() {
        return {
            cacheSize: this.cache.size,
            activeRequests: this.requestQueue.size,
            offlineQueueSize: this.offlineQueue.length,
            abortControllersCount: this.abortControllers.size,
            isOnline: this.isOnline
        };
    }
}

/**
 * API错误类
 */
class APIError extends Error {
    constructor(message, code = 'UNKNOWN_ERROR', details = {}) {
        super(message);
        this.name = 'APIError';
        this.code = code;
        this.details = details;
        this.timestamp = new Date().toISOString();
    }

    toJSON() {
        return {
            name: this.name,
            message: this.message,
            code: this.code,
            details: this.details,
            timestamp: this.timestamp,
            stack: this.stack
        };
    }
}

/**
 * 聊天API类 - 专门处理聊天相关的API调用
 */
class ChatAPI extends APIManager {
    constructor(options = {}) {
        super({
            baseURL: '/api/chat',
            ...options
        });

        this.conversationId = null;
        this.messageHistory = [];
    }

    /**
     * 发送聊天消息
     */
    async sendMessage(message, options = {}) {
        const requestData = {
            message,
            conversation_id: this.conversationId,
            context: this.getContext(),
            ...options
        };

        try {
            const response = await this.post('/send', requestData, {
                timeout: 60000, // 聊天请求可能需要更长时间
                cache: false    // 聊天消息不缓存
            });

            // 更新消息历史
            this.addToHistory('user', message);
            if (response.success && response.data?.content) {
                this.addToHistory('assistant', response.data.content);
            }

            return response;

        } catch (error) {
            console.error('发送消息失败:', error);
            throw error;
        }
    }

    /**
     * 获取对话历史
     */
    async getConversations(page = 1, limit = 20) {
        return this.get('/conversations', {
            params: { page, limit }
        });
    }

    /**
     * 获取特定对话
     */
    async getConversation(conversationId) {
        return this.get(`/conversations/${conversationId}`);
    }

    /**
     * 创建新对话
     */
    async createConversation(title = null) {
        const response = await this.post('/conversations', { title });
        
        if (response.success) {
            this.conversationId = response.data.id;
            this.messageHistory = [];
        }

        return response;
    }

    /**
     * 删除对话
     */
    async deleteConversation(conversationId) {
        return this.delete(`/conversations/${conversationId}`);
    }

    /**
     * 更新对话标题
     */
    async updateConversationTitle(conversationId, title) {
        return this.patch(`/conversations/${conversationId}`, { title });
    }

    /**
     * 获取上下文
     */
    getContext() {
        return {
            history: this.messageHistory.slice(-10), // 最近10条消息
            timestamp: new Date().toISOString(),
            user_agent: navigator.userAgent,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        };
    }

    /**
     * 添加到历史记录
     */
    addToHistory(role, content) {
        this.messageHistory.push({
            role,
            content,
            timestamp: new Date().toISOString()
        });

        // 限制历史记录长度
        if (this.messageHistory.length > 50) {
            this.messageHistory = this.messageHistory.slice(-40);
        }
    }

    /**
     * 清空历史记录
     */
    clearHistory() {
        this.messageHistory = [];
    }

    /**
     * 设置当前对话ID
     */
    setConversationId(id) {
        this.conversationId = id;
    }

    /**
     * 获取消息历史
     */
    getMessageHistory() {
        return [...this.messageHistory];
    }
}

/**
 * 用户API类 - 处理用户相关的API调用
 */
class UserAPI extends APIManager {
    constructor(options = {}) {
        super({
            baseURL: '/api/user',
            ...options
        });
    }

    /**
     * 用户登录
     */
    async login(email, password, remember = false) {
        const response = await this.post('/login', {
            email,
            password,
            remember
        }, { cache: false });

        if (response.success && response.data?.token) {
            this.setToken(response.data.token);
        }

        return response;
    }

    /**
     * 用户注册
     */
    async register(userData) {
        return this.post('/register', userData, { cache: false });
    }

    /**
     * 用户登出
     */
    async logout() {
        try {
            await this.post('/logout', {}, { cache: false });
        } finally {
            this.removeToken();
        }
    }

    /**
     * 获取用户信息
     */
    async getProfile() {
        return this.get('/profile');
    }

    /**
     * 更新用户信息
     */
    async updateProfile(userData) {
        return this.patch('/profile', userData, { cache: false });
    }

    /**
     * 修改密码
     */
    async changePassword(currentPassword, newPassword) {
        return this.post('/change-password', {
            current_password: currentPassword,
            new_password: newPassword
        }, { cache: false });
    }

    /**
     * 忘记密码
     */
    async forgotPassword(email) {
        return this.post('/forgot-password', { email }, { cache: false });
    }

    /**
     * 重置密码
     */
    async resetPassword(token, password) {
        return this.post('/reset-password', {
            token,
            password
        }, { cache: false });
    }

    /**
     * 验证邮箱
     */
    async verifyEmail(token) {
        return this.post('/verify-email', { token }, { cache: false });
    }

    /**
     * 获取用户设置
     */
    async getSettings() {
        return this.get('/settings');
    }

    /**
     * 更新用户设置
     */
    async updateSettings(settings) {
        return this.patch('/settings', settings, { cache: false });
    }
}

/**
 * 文档API类 - 处理文档相关的API调用
 */
class DocumentAPI extends APIManager {
    constructor(options = {}) {
        super({
            baseURL: '/api/documents',
            ...options
        });
    }

    /**
     * 获取文档列表
     */
    async getDocuments(filters = {}) {
        return this.get('/', { params: filters });
    }

    /**
     * 获取单个文档
     */
    async getDocument(documentId) {
        return this.get(`/${documentId}`);
    }

    /**
     * 上传文档
     */
    async uploadDocument(file, metadata = {}) {
        const formData = new FormData();
        formData.append('file', file);
        
        Object.keys(metadata).forEach(key => {
            formData.append(key, metadata[key]);
        });

        return this.post('/upload', formData, {
            timeout: 300000, // 5分钟上传超时
            cache: false
        });
    }

    /**
     * 删除文档
     */
    async deleteDocument(documentId) {
        return this.delete(`/${documentId}`);
    }

    /**
     * 更新文档信息
     */
    async updateDocument(documentId, data) {
        return this.patch(`/${documentId}`, data, { cache: false });
    }

    /**
     * 搜索文档
     */
    async searchDocuments(query, filters = {}) {
        return this.post('/search', {
            query,
            filters
        });
    }

    /**
     * 获取文档内容
     */
    async getDocumentContent(documentId) {
        return this.get(`/${documentId}/content`);
    }

    /**
     * 分析文档
     */
    async analyzeDocument(documentId, analysisType = 'summary') {
        return this.post(`/${documentId}/analyze`, {
            type: analysisType
        }, {
            timeout: 120000 // 2分钟分析超时
        });
    }
}

// 导出类
window.APIManager = APIManager;
window.APIError = APIError;
window.ChatAPI = ChatAPI;
window.UserAPI = UserAPI;
window.DocumentAPI = DocumentAPI;

// 创建全局API实例
window.api = {
    chat: new ChatAPI(),
    user: new UserAPI(),
    document: new DocumentAPI(),
    general: new APIManager()
};


