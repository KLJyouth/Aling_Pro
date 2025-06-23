/**
 * HTTP客户端服务
 * 提供统一的API请求接口
 */

class HttpClient {
    constructor(baseURL = '') {
        this.baseURL = baseURL;
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        this.interceptors = {
            request: [],
            response: []
        };
        
        this.setupInterceptors();
    }

    /**
     * 设置拦截器
     */
    setupInterceptors() {
        // 请求拦截器 - 添加认证token
        this.addRequestInterceptor((config) => {
            const token = localStorage.getItem('auth_token');
            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            return config;
        });

        // 响应拦截器 - 处理通用错误
        this.addResponseInterceptor(
            (response) => response,
            (error) => {
                if (error.status === 401) {
                    // Token过期，重定向到登录页
                    localStorage.removeItem('auth_token');
                    window.location.href = '/login';
                } else if (error.status === 403) {
                    window.app.getService('notifications').error('权限不足', '您没有权限执行此操作');
                } else if (error.status >= 500) {
                    window.app.getService('notifications').error('服务器错误', '服务器暂时不可用，请稍后重试');
                }
                return Promise.reject(error);
            }
        );
    }

    /**
     * 添加请求拦截器
     */
    addRequestInterceptor(fulfilled, rejected) {
        this.interceptors.request.push({ fulfilled, rejected });
    }

    /**
     * 添加响应拦截器
     */
    addResponseInterceptor(fulfilled, rejected) {
        this.interceptors.response.push({ fulfilled, rejected });
    }

    /**
     * 执行请求拦截器
     */
    async executeRequestInterceptors(config) {
        for (const interceptor of this.interceptors.request) {
            try {
                config = await interceptor.fulfilled(config);
            } catch (error) {
                if (interceptor.rejected) {
                    await interceptor.rejected(error);
                }
                throw error;
            }
        }
        return config;
    }

    /**
     * 执行响应拦截器
     */
    async executeResponseInterceptors(response, error = null) {
        for (const interceptor of this.interceptors.response) {
            try {
                if (error) {
                    if (interceptor.rejected) {
                        response = await interceptor.rejected(error);
                    }
                } else {
                    response = await interceptor.fulfilled(response);
                }
            } catch (err) {
                error = err;
            }
        }
        
        if (error) {
            throw error;
        }
        return response;
    }

    /**
     * 发送请求
     */
    async request(config) {
        try {
            // 合并配置
            const finalConfig = {
                method: 'GET',
                headers: { ...this.defaultHeaders },
                ...config,
                url: this.baseURL + config.url
            };

            // 执行请求拦截器
            const interceptedConfig = await this.executeRequestInterceptors(finalConfig);

            // 发送请求
            const response = await fetch(interceptedConfig.url, {
                method: interceptedConfig.method,
                headers: interceptedConfig.headers,
                body: interceptedConfig.data ? JSON.stringify(interceptedConfig.data) : undefined,
                credentials: 'same-origin'
            });

            // 解析响应
            let data;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }

            const result = {
                data,
                status: response.status,
                statusText: response.statusText,
                headers: response.headers,
                config: interceptedConfig
            };

            if (!response.ok) {
                const error = new Error(`HTTP ${response.status}: ${response.statusText}`);
                error.response = result;
                error.status = response.status;
                return await this.executeResponseInterceptors(null, error);
            }

            // 执行响应拦截器
            return await this.executeResponseInterceptors(result);

        } catch (error) {
            // 网络错误或其他错误
            if (!error.response) {
                error.message = '网络错误，请检查您的网络连接';
            }
            return await this.executeResponseInterceptors(null, error);
        }
    }

    /**
     * GET请求
     */
    async get(url, params = {}, config = {}) {
        const urlObj = new URL(this.baseURL + url, window.location.origin);
        Object.keys(params).forEach(key => {
            if (params[key] !== undefined && params[key] !== null) {
                urlObj.searchParams.append(key, params[key]);
            }
        });

        return this.request({
            ...config,
            method: 'GET',
            url: urlObj.pathname + urlObj.search
        });
    }

    /**
     * POST请求
     */
    async post(url, data = {}, config = {}) {
        return this.request({
            ...config,
            method: 'POST',
            url,
            data
        });
    }

    /**
     * PUT请求
     */
    async put(url, data = {}, config = {}) {
        return this.request({
            ...config,
            method: 'PUT',
            url,
            data
        });
    }

    /**
     * DELETE请求
     */
    async delete(url, config = {}) {
        return this.request({
            ...config,
            method: 'DELETE',
            url
        });
    }

    /**
     * PATCH请求
     */
    async patch(url, data = {}, config = {}) {
        return this.request({
            ...config,
            method: 'PATCH',
            url,
            data
        });
    }

    /**
     * 上传文件
     */
    async upload(url, file, options = {}) {
        const formData = new FormData();
        formData.append('file', file);
        
        // 添加额外的字段
        if (options.fields) {
            Object.keys(options.fields).forEach(key => {
                formData.append(key, options.fields[key]);
            });
        }

        const config = {
            method: 'POST',
            url,
            headers: {
                // 移除Content-Type，让浏览器自动设置multipart边界
                ...this.defaultHeaders,
                'Content-Type': undefined
            },
            body: formData
        };

        // 执行请求拦截器
        const interceptedConfig = await this.executeRequestInterceptors(config);

        try {
            const response = await fetch(this.baseURL + interceptedConfig.url, {
                method: interceptedConfig.method,
                headers: Object.fromEntries(
                    Object.entries(interceptedConfig.headers).filter(([key, value]) => value !== undefined)
                ),
                body: interceptedConfig.body,
                credentials: 'same-origin'
            });

            let data;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }

            const result = {
                data,
                status: response.status,
                statusText: response.statusText,
                headers: response.headers,
                config: interceptedConfig
            };

            if (!response.ok) {
                const error = new Error(`HTTP ${response.status}: ${response.statusText}`);
                error.response = result;
                error.status = response.status;
                return await this.executeResponseInterceptors(null, error);
            }

            return await this.executeResponseInterceptors(result);

        } catch (error) {
            if (!error.response) {
                error.message = '文件上传失败，请检查网络连接';
            }
            return await this.executeResponseInterceptors(null, error);
        }
    }

    /**
     * 并发请求
     */
    async all(requests) {
        try {
            const results = await Promise.allSettled(requests);
            return results.map(result => {
                if (result.status === 'fulfilled') {
                    return result.value;
                } else {
                    throw result.reason;
                }
            });
        } catch (error) {
            throw new Error('并发请求失败: ' + error.message);
        }
    }

    /**
     * 设置默认headers
     */
    setDefaultHeaders(headers) {
        this.defaultHeaders = { ...this.defaultHeaders, ...headers };
    }

    /**
     * 设置基础URL
     */
    setBaseURL(baseURL) {
        this.baseURL = baseURL;
    }

    /**
     * 创建新的实例
     */
    create(config = {}) {
        return new HttpClient(config.baseURL || this.baseURL);
    }
}

export { HttpClient };
