/**
 * AlingAI JavaScript SDK
 * 量子安全API客户端库
 * 版本：2.0.0
 */

class AlingAIClient {
    constructor(apiKey, baseUrl = 'https://api.alingai.com', timeout = 30000) {
        this.apiKey = apiKey;
        this.baseUrl = baseUrl.replace(/\/$/, '');
        this.timeout = timeout;
    }

    /**
     * 量子加密文本
     * @param {string} text 待加密文本
     * @returns {Promise<Object>} 加密结果
     */
    async quantumEncrypt(text) {
        return this.makeRequest('POST', '/quantum/encrypt', { text });
    }

    /**
     * 量子解密文本
     * @param {string} encryptedData 加密数据
     * @returns {Promise<Object>} 解密结果
     */
    async quantumDecrypt(encryptedData) {
        return this.makeRequest('POST', '/quantum/decrypt', { data: encryptedData });
    }

    /**
     * AI智能对话
     * @param {string} message 用户消息
     * @param {Array} context 对话上下文
     * @returns {Promise<Object>} AI回复
     */
    async chat(message, context = []) {
        return this.makeRequest('POST', '/ai/chat', { message, context });
    }

    /**
     * 零信任身份验证
     * @param {string} token 身份令牌
     * @returns {Promise<Object>} 验证结果
     */
    async verifyIdentity(token) {
        return this.makeRequest('POST', '/auth/verify', { token });
    }

    /**
     * 发送HTTP请求
     * @param {string} method HTTP方法
     * @param {string} endpoint API端点
     * @param {Object} data 请求数据
     * @returns {Promise<Object>} 响应数据
     */
    async makeRequest(method, endpoint, data = {}) {
        const url = this.baseUrl + endpoint;
        
        const config = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.apiKey}`,
                'User-Agent': 'AlingAI-JS-SDK/2.0.0'
            },
            timeout: this.timeout
        };

        if (method === 'POST') {
            config.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '请求错误');
            }

            return await response.json();
        } catch (error) {
            throw new Error(`AlingAI API Error: ${error.message}`);
        }
    }
}

/**
 * 量子加密助手类
 */
class QuantumCrypto {
    /**
     * 生成量子密钥对
     * @returns {Object} 密钥对
     */
    static generateKeyPair() {
        // 这里是简化的示例，实际会调用量子密钥生成算法
        return {
            public_key: btoa(Array.from(crypto.getRandomValues(new Uint8Array(256))).map(b => String.fromCharCode(b)).join('')),
            private_key: btoa(Array.from(crypto.getRandomValues(new Uint8Array(256))).map(b => String.fromCharCode(b)).join('')),
            algorithm: 'quantum-rsa-4096'
        };
    }

    /**
     * 量子安全哈希
     * @param {string} data 待哈希数据
     * @returns {Promise<string>} 哈希值
     */
    static async quantumHash(data) {
        const encoder = new TextEncoder();
        const hashBuffer = await crypto.subtle.digest('SHA-512', encoder.encode(data + Date.now()));
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
}

// Node.js 环境支持
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { AlingAIClient, QuantumCrypto };
}

// Browser 环境支持
if (typeof window !== 'undefined') {
    window.AlingAIClient = AlingAIClient;
    window.QuantumCrypto = QuantumCrypto;
}
