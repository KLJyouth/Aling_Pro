/**
 * HTTP客户端服务
 */

export class HttpClient {
    constructor(baseUrl = '') {
        this.baseUrl = baseUrl;
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
    }

    async get(endpoint, params = {}, headers = {}) {
        const url = this.buildUrl(endpoint, params);
        return this.request(url, 'GET', null, headers);
    }

    async post(endpoint, data = {}, headers = {}) {
        const url = this.buildUrl(endpoint);
        return this.request(url, 'POST', data, headers);
    }

    async put(endpoint, data = {}, headers = {}) {
        const url = this.buildUrl(endpoint);
        return this.request(url, 'PUT', data, headers);
    }

    async delete(endpoint, headers = {}) {
        const url = this.buildUrl(endpoint);
        return this.request(url, 'DELETE', null, headers);
    }

    async request(url, method, data = null, headers = {}) {
        try {
            const options = {
                method,
                headers: { ...this.defaultHeaders, ...headers }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const json = await response.json();
                return { data: json, status: response.status, ok: response.ok };
            } else {
                const text = await response.text();
                return { data: text, status: response.status, ok: response.ok };
            }
        } catch (error) {
            console.error('HTTP请求错误:', error);
            throw error;
        }
    }

    buildUrl(endpoint, params = {}) {
        const url = new URL(this.baseUrl + endpoint, window.location.origin);
        
        Object.keys(params).forEach(key => {
            url.searchParams.append(key, params[key]);
        });
        
        return url.toString();
    }

    setAuthToken(token) {
        if (token) {
            this.defaultHeaders['Authorization'] = Bearer ;
        } else {
            delete this.defaultHeaders['Authorization'];
        }
    }
}
