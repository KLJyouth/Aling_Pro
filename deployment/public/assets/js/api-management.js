/**
 * API管理模块
 * 提供统一的API调用和管理功能
 */

export class ApiManagement {
    constructor() {
        this.baseURL = window.location.origin;
        this.endpoints = {
            auth: '/api/auth',
            chat: '/api/chat',
            user: '/api/user',
            dashboard: '/api/dashboard'
        };
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        this.requestQueue = [];
        this.isInitialized = false;
    }

    /**
     * 初始化API管理器
     */
    async init() {
        try {
            this.setupInterceptors();
            this.loadAuthToken();
            this.isInitialized = true;
            console.log('🔌 API Management initialized successfully');
        } catch (error) {
            console.error('❌ API Management initialization failed:', error);
        }
    }

    /**
     * 设置请求拦截器
     */
    setupInterceptors() {
        // 设置默认的请求处理
        this.requestInterceptor = (config) => {
            // 添加认证token
            const token = this.getAuthToken();
            if (token) {
                config.headers = {
                    ...config.headers,
                    'Authorization': `Bearer ${token}`
                };
            }
            return config;
        };

        // 设置响应拦截器
        this.responseInterceptor = (response) => {
            // 处理响应数据
            return response;
        };
    }

    /**
     * 加载认证token
     */
    loadAuthToken() {
        try {
            const token = localStorage.getItem('authToken');
            if (token) {
                this.authToken = token;
            }
        } catch (error) {
            console.warn('⚠️ Failed to load auth token:', error);
        }
    }

    /**
     * 获取认证token
     */
    getAuthToken() {
        return this.authToken || localStorage.getItem('authToken');
    }

    /**
     * 设置认证token
     */
    setAuthToken(token) {
        this.authToken = token;
        try {
            localStorage.setItem('authToken', token);
        } catch (error) {
            console.warn('⚠️ Failed to save auth token:', error);
        }
    }

    /**
     * 通用请求方法
     */
    async request(url, options = {}) {
        try {
            const config = {
                method: 'GET',
                headers: { ...this.defaultHeaders },
                ...options
            };

            // 应用请求拦截器
            const processedConfig = this.requestInterceptor(config);

            const response = await fetch(url, processedConfig);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // 应用响应拦截器
            return this.responseInterceptor(data);

        } catch (error) {
            console.error('🔌 API request failed:', error);
            throw error;
        }
    }

    /**
     * GET请求
     */
    async get(endpoint, params = {}) {
        const url = new URL(endpoint, this.baseURL);
        Object.keys(params).forEach(key => {
            url.searchParams.append(key, params[key]);
        });

        return this.request(url.toString(), {
            method: 'GET'
        });
    }

    /**
     * POST请求
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT请求
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE请求
     */
    async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }

    /**
     * 认证相关API
     */
    auth = {
        login: async (credentials) => {
            const response = await this.post(this.endpoints.auth + '/login', credentials);
            if (response.token) {
                this.setAuthToken(response.token);
            }
            return response;
        },

        logout: async () => {
            try {
                await this.post(this.endpoints.auth + '/logout');
            } finally {
                this.authToken = null;
                localStorage.removeItem('authToken');
            }
        },

        register: async (userData) => {
            return this.post(this.endpoints.auth + '/register', userData);
        },

        forgotPassword: async (email) => {
            return this.post(this.endpoints.auth + '/forgot-password', { email });
        },

        resetPassword: async (token, newPassword) => {
            return this.post(this.endpoints.auth + '/reset-password', { token, newPassword });
        }
    };

    /**
     * 聊天相关API
     */
    chat = {
        sendMessage: async (message) => {
            return this.post(this.endpoints.chat + '/message', { message });
        },

        getHistory: async (limit = 50) => {
            return this.get(this.endpoints.chat + '/history', { limit });
        },

        clearHistory: async () => {
            return this.delete(this.endpoints.chat + '/history');
        }
    };

    /**
     * 用户相关API
     */
    user = {
        getProfile: async () => {
            return this.get(this.endpoints.user + '/profile');
        },

        updateProfile: async (profileData) => {
            return this.put(this.endpoints.user + '/profile', profileData);
        },

        getSettings: async () => {
            return this.get(this.endpoints.user + '/settings');
        },

        updateSettings: async (settings) => {
            return this.put(this.endpoints.user + '/settings', settings);
        }
    };

    /**
     * 仪表板相关API
     */
    dashboard = {
        getMetrics: async () => {
            return this.get(this.endpoints.dashboard + '/metrics');
        },

        getChartData: async (chartType) => {
            return this.get(this.endpoints.dashboard + '/charts/' + chartType);
        }
    };

    /**
     * 获取API状态
     */
    getStatus() {
        return {
            isInitialized: this.isInitialized,
            hasAuthToken: !!this.getAuthToken(),
            baseURL: this.baseURL,
            queueLength: this.requestQueue.length
        };
    }
}

// 创建全局实例
const apiManagement = new ApiManagement();

// 页面加载完成后初始化
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        apiManagement.init();
    });
} else {
    apiManagement.init();
}

// 将实例挂载到全局
window.apiManagement = apiManagement;

// 导出实例
export default apiManagement;
