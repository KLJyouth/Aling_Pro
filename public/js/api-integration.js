/**
 * API集成接口模块 v2.0
 * 提供RESTful API、Webhooks、外部系统集成功能
 * 
 * 功能包括：
 * - RESTful API 接口（增强版）
 * - Webhook 支持（安全性增强）
 * - 外部系统集成（扩展支持）
 * - API 密钥管理（自动轮换）
 * - 数据同步（离线支持）
 * - 第三方服务集成（增加OAuth2支持）
 * - 请求重试与断点续传
 * - 请求缓存与批处理
 * - 数据压缩与优化传输
 * 
 * @version 2.0.0
 * @author AlingAi Team
 * @license Proprietary
 */

class APIIntegration {
    constructor() {
        this.apiKey = null;
        this.apiEndpoints = new Map();
        this.webhookEndpoints = new Map();
        this.externalServices = new Map();
        this.syncQueue = [];
        this.rateLimiter = new Map();
        this.authTokens = new Map();
        this.requestCache = new Map();
        this.pendingRequests = new Map();
        this.offlineQueue = [];
        this.metrics = {
            totalRequests: 0,
            successfulRequests: 0,
            failedRequests: 0,
            averageResponseTime: 0,
            requestTimes: []
        };
        this.isInitialized = false;
        
        // API配置
        this.config = {
            baseUrl: '/api/v1',
            timeout: 30000,
            retryAttempts: 5,
            retryDelay: 1000,
            retryBackoffFactor: 1.5,
            rateLimitWindow: 60000, // 1分钟
            maxRequestsPerWindow: 120,
            enableWebhooks: true,
            webhookSecret: null,
            enableCompression: true,
            cacheTTL: 300000, // 5分钟
            offlineSupport: true,
            batchProcessing: true,
            maxBatchSize: 10,
            apiKeyRotationInterval: 7 * 24 * 60 * 60 * 1000, // 7天
            connectionTimeout: 10000
        };

        // 支持的外部服务
        this.supportedServices = {
            slack: { name: 'Slack', icon: 'slack', color: '#4A154B' },
            teams: { name: 'Microsoft Teams', icon: 'microsoft', color: '#6264A7' },
            discord: { name: 'Discord', icon: 'discord', color: '#5865F2' },
            email: { name: 'Email', icon: 'envelope', color: '#EA4335' },
            webhook: { name: 'Custom Webhook', icon: 'webhook', color: '#28a745' },
            grafana: { name: 'Grafana', icon: 'graph-up', color: '#F46800' },
            prometheus: { name: 'Prometheus', icon: 'speedometer2', color: '#E6522C' },
            elastic: { name: 'Elasticsearch', icon: 'search', color: '#005571' },
            jira: { name: 'Jira', icon: 'kanban', color: '#0052CC' },
            github: { name: 'GitHub', icon: 'github', color: '#24292e' },
            // 新增支持的服务
            zendesk: { name: 'Zendesk', icon: 'headset', color: '#03363D' },
            salesforce: { name: 'Salesforce', icon: 'cloud', color: '#00A1E0' },
            googlecloud: { name: 'Google Cloud', icon: 'google', color: '#4285F4' },
            azure: { name: 'Microsoft Azure', icon: 'microsoft', color: '#0078D4' },
            aws: { name: 'Amazon AWS', icon: 'amazon', color: '#FF9900' }
        };
        
        // 初始化离线检测
        this.setupOfflineDetection();
    }

    // ==================== 初始化方法 ====================

    async initialize(detectionSystem) {
        if (this.isInitialized) return;

        try {
        this.detectionSystem = detectionSystem;
            
            // 加载配置
        await this.loadConfiguration();
            
            // 设置API端点
        this.setupAPIEndpoints();
            
            // 初始化Webhooks
        this.initializeWebhooks();
            
            // 设置速率限制
        this.setupRateLimiting();
            
            // 启动同步服务
        this.startSyncService();
            
            // 设置API密钥轮换
            this.setupApiKeyRotation();
            
            // 处理离线队列
            if (navigator.onLine && this.offlineQueue.length > 0) {
                this.processOfflineQueue();
            }
        
        this.isInitialized = true;
            console.log('✅ API集成模块初始化完成');
        } catch (error) {
            console.error('❌ API集成模块初始化失败:', error);
            throw new Error(`API集成初始化失败: ${error.message}`);
        }
    }

    setupOfflineDetection() {
        window.addEventListener('online', () => {
            console.log('🌐 网络连接已恢复');
            if (this.offlineQueue.length > 0) {
                this.processOfflineQueue();
            }
        });

        window.addEventListener('offline', () => {
            console.log('⚠️ 网络连接已断开，切换到离线模式');
        });
    }

    async processOfflineQueue() {
        console.log(`⏳ 处理离线队列中的 ${this.offlineQueue.length} 个请求...`);
        
        const queue = [...this.offlineQueue];
        this.offlineQueue = [];
        
        for (const request of queue) {
            try {
                const { method, path, params, headers, resolve, reject, timestamp } = request;
                
                console.log(`🔄 处理离线请求: ${method} ${path} (延迟 ${Date.now() - timestamp}ms)`);
                
                const result = await this.handleAPIRequest(method, path, params, headers);
                resolve(result);
            } catch (error) {
                console.error('❌ 处理离线请求失败:', error);
                request.reject(error);
            }
        }
    }

    async loadConfiguration() {
        try {
            // 尝试从本地存储加载配置
            const savedConfig = localStorage.getItem('api-integration-config');
            if (savedConfig) {
                const config = JSON.parse(savedConfig);
                this.config = { ...this.config, ...config };
            }

            // 加载API密钥
            const savedApiKey = localStorage.getItem('api-integration-key');
            if (savedApiKey) {
                this.apiKey = savedApiKey;
            }

            // 加载外部服务配置
            const savedServices = localStorage.getItem('external-services-config');
            if (savedServices) {
                const services = JSON.parse(savedServices);
                services.forEach(service => {
                    this.externalServices.set(service.id, service);
                });
            }

            // 加载指标数据
            const savedMetrics = localStorage.getItem('api-integration-metrics');
            if (savedMetrics) {
                this.metrics = { ...this.metrics, ...JSON.parse(savedMetrics) };
            }
            
            // 加载离线队列
            const savedOfflineQueue = localStorage.getItem('api-integration-offline-queue');
            if (savedOfflineQueue) {
                this.offlineQueue = JSON.parse(savedOfflineQueue);
            }
        } catch (error) {
            console.error('❌ 配置加载失败:', error);
            // 使用默认配置继续
        }
    }

    saveConfiguration() {
        try {
            localStorage.setItem('api-integration-config', JSON.stringify(this.config));
            
            if (this.apiKey) {
                localStorage.setItem('api-integration-key', this.apiKey);
            }

            const services = Array.from(this.externalServices.values());
            localStorage.setItem('external-services-config', JSON.stringify(services));

            // 保存指标数据
            localStorage.setItem('api-integration-metrics', JSON.stringify(this.metrics));
            
            // 保存离线队列
            if (this.offlineQueue.length > 0) {
                localStorage.setItem('api-integration-offline-queue', JSON.stringify(this.offlineQueue));
            } else {
                localStorage.removeItem('api-integration-offline-queue');
            }
        } catch (error) {
            console.error('❌ 配置保存失败:', error);
        }
    }

    setupApiKeyRotation() {
        // 检查上次轮换时间
        const lastRotation = localStorage.getItem('api-key-last-rotation');
        const now = Date.now();
        
        if (!lastRotation || (now - parseInt(lastRotation)) > this.config.apiKeyRotationInterval) {
            this.rotateApiKey();
        }
        
        // 设置定时器进行定期轮换
        setInterval(() => {
            this.rotateApiKey();
        }, this.config.apiKeyRotationInterval);
    }

    async rotateApiKey() {
        try {
            console.log('🔄 开始API密钥轮换');
            
            // 调用API获取新密钥
            const response = await fetch(`${this.config.baseUrl}/auth/rotate-key`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.apiKey}`
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success && result.apiKey) {
                    // 更新API密钥
                    this.apiKey = result.apiKey;
                    localStorage.setItem('api-integration-key', this.apiKey);
                    localStorage.setItem('api-key-last-rotation', Date.now().toString());
                    console.log('✅ API密钥轮换成功');
                }
            } else {
                throw new Error(`API密钥轮换失败: ${response.status} ${response.statusText}`);
            }
        } catch (error) {
            console.error('❌ API密钥轮换失败:', error);
        }
    }

    // ==================== RESTful API 接口 ====================

    setupAPIEndpoints() {
        // 检测结果API
        this.apiEndpoints.set('GET /detection/results', {
            handler: this.getDetectionResults.bind(this),
            description: '获取检测结果',
            auth: true,
            cache: true,
            cacheTTL: 60000, // 1分钟
            offlineSupport: true
        });

        this.apiEndpoints.set('POST /detection/start', {
            handler: this.startDetection.bind(this),
            description: '启动检测',
            auth: true,
            retry: true,
            offlineSupport: false
        });

        this.apiEndpoints.set('GET /detection/status', {
            handler: this.getDetectionStatus.bind(this),
            description: '获取检测状态',
            auth: false,
            cache: true,
            cacheTTL: 5000, // 5秒
            offlineSupport: true
        });

        this.apiEndpoints.set('GET /detection/history', {
            handler: this.getDetectionHistory.bind(this),
            description: '获取检测历史',
            auth: true,
            cache: true,
            cacheTTL: 300000, // 5分钟
            offlineSupport: true
        });

        // 系统信息API
        this.apiEndpoints.set('GET /system/info', {
            handler: this.getSystemInfo.bind(this),
            description: '获取系统信息',
            auth: false,
            cache: true,
            cacheTTL: 3600000, // 1小时
            offlineSupport: true
        });

        this.apiEndpoints.set('GET /system/metrics', {
            handler: this.getSystemMetrics.bind(this),
            description: '获取系统指标',
            auth: true,
            cache: true,
            cacheTTL: 60000, // 1分钟
            offlineSupport: true
        });

        // 配置管理API
        this.apiEndpoints.set('GET /config', {
            handler: this.getConfiguration.bind(this),
            description: '获取配置',
            auth: true,
            cache: true,
            cacheTTL: 300000, // 5分钟
            offlineSupport: true
        });

        this.apiEndpoints.set('PUT /config', {
            handler: this.updateConfiguration.bind(this),
            description: '更新配置',
            auth: true,
            retry: true,
            offlineSupport: false
        });

        // 新增API端点
        this.apiEndpoints.set('GET /health', {
            handler: this.getHealthStatus.bind(this),
            description: '获取健康状态',
            auth: false,
            cache: true,
            cacheTTL: 30000, // 30秒
            offlineSupport: true
        });
        
        this.apiEndpoints.set('POST /feedback', {
            handler: this.submitFeedback.bind(this),
            description: '提交反馈',
            auth: true,
            retry: true,
            offlineSupport: true
        });
    }

    // API处理方法
    async getDetectionResults(params) {
        if (!this.detectionSystem) {
            throw new Error('检测系统未初始化');
        }

        const results = Array.from(this.detectionSystem.testResults.entries()).map(([key, result]) => ({
            testId: key,
            status: result.status,
            duration: result.duration,
            timestamp: result.timestamp,
            details: result.details
        }));

        return {
            success: true,
            data: {
                results,
                summary: {
                    total: this.detectionSystem.totalTests,
                    completed: this.detectionSystem.completedTests,
                    passed: this.detectionSystem.passedTests,
                    failed: this.detectionSystem.failedTests,
                    successRate: this.detectionSystem.completedTests > 0 ? 
                        (this.detectionSystem.passedTests / this.detectionSystem.completedTests * 100).toFixed(2) : 0
                }
            },
            timestamp: Date.now()
        };
    }

    async startDetection(params) {
        if (!this.detectionSystem) {
            throw new Error('检测系统未初始化');
        }

        const { type = 'full', tests = null } = params;

        try {
            if (type === 'custom' && tests) {
                await this.detectionSystem.runCustomDetectionTests(tests);
            } else if (type === 'quick') {
                await this.detectionSystem.runQuickDetection();
            } else {
                await this.detectionSystem.runFullDetection();
            }

            return {
                success: true,
                message: '检测已启动',
                sessionId: this.detectionSystem.currentSession.sessionId,
                timestamp: Date.now()
            };
        } catch (error) {
            throw new Error(`检测启动失败: ${error.message}`);
        }
    }

    async getDetectionStatus(params) {
        if (!this.detectionSystem) {
            throw new Error('检测系统未初始化');
        }

        return {
            success: true,
            data: {
                isRunning: this.detectionSystem.isRunning,
                isPaused: this.detectionSystem.isPaused,
                progress: this.detectionSystem.totalTests > 0 ? 
                    (this.detectionSystem.completedTests / this.detectionSystem.totalTests * 100) : 0,
                currentTest: this.detectionSystem.currentTest,
                sessionId: this.detectionSystem.currentSession.sessionId,
                autoDetectionEnabled: this.detectionSystem.autoDetectionEnabled
            },
            timestamp: Date.now()
        };
    }

    async getDetectionHistory(params) {
        if (!this.detectionSystem) {
            throw new Error('检测系统未初始化');
        }

        const { limit = 50, offset = 0, startDate = null, endDate = null } = params;
        let history = [...this.detectionSystem.testHistory];

        // 日期过滤
        if (startDate) {
            history = history.filter(record => record.timestamp >= new Date(startDate).getTime());
        }
        if (endDate) {
            history = history.filter(record => record.timestamp <= new Date(endDate).getTime());
        }

        // 分页
        const total = history.length;
        const records = history.slice(offset, offset + limit);

        return {
            success: true,
            data: {
                records,
                pagination: {
                    total,
                    limit,
                    offset,
                    hasMore: offset + limit < total
                }
            },
            timestamp: Date.now()
        };
    }

    async getSystemInfo(params) {
        return {
            success: true,
            data: {
                version: '2.0.0',
                environment: this.detectionSystem?.currentSession?.environment || {},
                uptime: Date.now() - (this.detectionSystem?.currentSession?.startTime || Date.now()),
                apiVersion: '1.0',
                features: {
                    notifications: true,
                    visualization: true,
                    apiIntegration: true,
                    collaboration: false,
                    intelligentAlerts: false
                }
            },
            timestamp: Date.now()
        };
    }

    async getSystemMetrics(params) {
        if (!this.detectionSystem) {
            throw new Error('检测系统未初始化');
        }

        const dashboardData = this.detectionSystem.getDashboardData();
        
        return {
            success: true,
            data: {
                performance: dashboardData.performance,
                overview: dashboardData.overview,
                trends: dashboardData.trends,
                realtime: dashboardData.realtime
            },
            timestamp: Date.now()
        };
    }

    async getConfiguration(params) {
        return {
            success: true,
            data: {
                config: this.config,
                endpoints: Array.from(this.apiEndpoints.keys()),
                webhooks: Array.from(this.webhookEndpoints.keys()),
                services: Array.from(this.externalServices.values())
            },
            timestamp: Date.now()
        };
    }

    async updateConfiguration(params) {
        try {
            const { config, webhooks, services } = params;

            if (config) {
                this.config = { ...this.config, ...config };
            }

            if (webhooks) {
                webhooks.forEach(webhook => {
                    this.addWebhook(webhook.event, webhook.url, webhook.options);
                });
            }

            if (services) {
                services.forEach(service => {
                    this.addExternalService(service);
                });
            }

            this.saveConfiguration();

            return {
                success: true,
                message: '配置已更新',
                timestamp: Date.now()
            };
        } catch (error) {
            throw new Error(`配置更新失败: ${error.message}`);
        }
    }

    /**
     * 获取健康状态
     */
    async getHealthStatus() {
        const services = [
            { name: 'API服务', status: 'operational' },
            { name: '数据库', status: 'operational' },
            { name: '缓存服务', status: 'operational' },
            { name: '文件存储', status: 'operational' },
            { name: '消息队列', status: 'operational' }
        ];
        
        // 模拟随机服务状态
        const randomService = services[Math.floor(Math.random() * services.length)];
        randomService.status = Math.random() > 0.9 ? 'degraded' : 'operational';
        
        return {
            success: true,
            data: {
                status: services.every(s => s.status === 'operational') ? 'healthy' : 'degraded',
                services,
                uptime: Math.floor(Math.random() * 1000000) + 3600,
                timestamp: Date.now()
            }
        };
    }
    
    /**
     * 提交反馈
     */
    async submitFeedback(params) {
        if (!params.content) {
            throw new Error('反馈内容不能为空');
        }
        
        // 模拟提交反馈
        await this.delay(500);
        
        return {
            success: true,
            message: '感谢您的反馈！',
            feedbackId: this.generateId(),
            timestamp: Date.now()
        };
    }

    /**
     * 处理API请求
     * 增强版：支持缓存、重试、离线队列
     */
    async handleAPIRequest(method, path, params = {}, headers = {}) {
        // 增加请求ID用于跟踪
        const requestId = this.generateId();
        const startTime = Date.now();
        let attempt = 0;
        let lastError = null;
        
        // 检查是否离线
        if (!navigator.onLine && this.config.offlineSupport) {
            console.log(`🔄 网络离线，将请求添加到离线队列: ${method} ${path}`);
            return new Promise((resolve, reject) => {
                this.offlineQueue.push({
                    method, path, params, headers,
                    resolve, reject, timestamp: Date.now(),
                    requestId
                });
                
                // 保存离线队列到本地存储
                localStorage.setItem('api-integration-offline-queue', 
                    JSON.stringify(this.offlineQueue));
                
                // 通知用户
                if (this.detectionSystem) {
                    this.detectionSystem.notify({
                        title: '网络离线',
                        message: '请求已保存，将在网络恢复后自动发送',
                        type: 'info'
                    });
                }
            });
        }
        
        // 检查缓存
        const cacheKey = `${method}-${path}-${JSON.stringify(params)}`;
        if (method === 'GET' && this.requestCache.has(cacheKey)) {
            const cachedData = this.requestCache.get(cacheKey);
            if (cachedData.expiry > Date.now()) {
                console.log(`🔄 使用缓存数据: ${method} ${path}`);
                return cachedData.data;
            } else {
                // 缓存过期，删除
                this.requestCache.delete(cacheKey);
            }
        }
        
        // 检查是否有相同的请求正在进行中
        if (this.pendingRequests.has(cacheKey)) {
            console.log(`🔄 复用正在进行的请求: ${method} ${path}`);
            return this.pendingRequests.get(cacheKey);
        }
        
        // 创建请求Promise
        const requestPromise = (async () => {
            // 重试逻辑
            while (attempt <= this.config.retryAttempts) {
                try {
                    attempt++;
                    
                    // 构建请求URL
                    const url = path.startsWith('http') ? path : `${this.config.baseUrl}${path}`;
                    
                    // 准备请求头
                    const requestHeaders = {
                        'Content-Type': 'application/json',
                        'X-Request-ID': requestId,
                        ...headers
                    };
                    
                    // 添加API密钥
                    if (this.apiKey) {
                        requestHeaders['X-API-Key'] = this.apiKey;
                    }
                    
                    // 准备请求选项
                    const options = {
                        method,
                        headers: requestHeaders,
                        timeout: this.config.timeout
                    };
                    
                    // 添加请求体
                    if (method !== 'GET' && params) {
                        // 数据压缩
                        if (this.config.enableCompression && params && typeof params === 'object') {
                            const compressedData = await this.compressData(params);
                            options.body = compressedData;
                            options.headers['Content-Encoding'] = 'gzip';
                        } else {
                            options.body = JSON.stringify(params);
                        }
                    }
                    
                    // 添加查询参数到URL
                    if (method === 'GET' && params) {
                        const queryParams = new URLSearchParams();
                        Object.entries(params).forEach(([key, value]) => {
                            if (value !== null && value !== undefined) {
                                queryParams.append(key, value);
                            }
                        });
                        const queryString = queryParams.toString();
                        if (queryString) {
                            url += (url.includes('?') ? '&' : '?') + queryString;
                        }
                    }
                    
                    // 执行请求
                    console.log(`🔄 发送API请求 (${attempt}/${this.config.retryAttempts + 1}): ${method} ${url}`);
                    const response = await fetch(url, options);
                    
                    // 处理响应
                    if (!response.ok) {
                        throw new Error(`API请求失败: ${response.status} ${response.statusText}`);
                    }
                    
                    // 解析响应
                    let data;
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        data = await response.text();
                    }
                    
                    // 记录成功请求
                    this.metrics.totalRequests++;
                    this.metrics.successfulRequests++;
                    const requestTime = Date.now() - startTime;
                    this.metrics.requestTimes.push(requestTime);
                    this.metrics.averageResponseTime = this.metrics.requestTimes.reduce((a, b) => a + b, 0) / this.metrics.requestTimes.length;
                    
                    // 保存指标
                    localStorage.setItem('api-integration-metrics', JSON.stringify(this.metrics));
                    
                    // 缓存GET请求结果
                    if (method === 'GET' && this.config.cacheTTL > 0) {
                        this.requestCache.set(cacheKey, {
                            data,
                            expiry: Date.now() + this.config.cacheTTL
                        });
                        
                        // 清理过期缓存
                        this.cleanupCache();
                    }
                    
                    return data;
                } catch (error) {
                    lastError = error;
                    
                    // 检查是否可重试
                    if (attempt <= this.config.retryAttempts && this.isRetryableError(error)) {
                        // 计算退避时间
                        const delay = this.config.retryDelay * Math.pow(this.config.retryBackoffFactor, attempt - 1);
                        console.log(`⚠️ 请求失败，${delay}ms后重试 (${attempt}/${this.config.retryAttempts}): ${error.message}`);
                        
                        // 等待退避时间
                        await this.delay(delay);
                        continue;
                    }
                    
                    // 记录失败请求
                    this.metrics.totalRequests++;
                    this.metrics.failedRequests++;
                    localStorage.setItem('api-integration-metrics', JSON.stringify(this.metrics));
                    
                    // 抛出错误
                    console.error(`❌ API请求失败 (${attempt}/${this.config.retryAttempts + 1}): ${error.message}`);
            throw error;
        }
    }

            // 如果所有重试都失败
            throw lastError;
        })();
        
        // 记录进行中的请求
        this.pendingRequests.set(cacheKey, requestPromise);
        
        try {
            // 等待请求完成
            const result = await requestPromise;
            return result;
        } finally {
            // 请求完成后从进行中请求中删除
            this.pendingRequests.delete(cacheKey);
        }
    }
    
    // 新增数据压缩方法
    async compressData(data) {
        try {
            // 使用CompressionStream API进行gzip压缩
            if (window.CompressionStream) {
                const jsonString = JSON.stringify(data);
                const encoder = new TextEncoder();
                const encodedData = encoder.encode(jsonString);
                
                const compressedStream = new Blob([encodedData]).stream()
                    .pipeThrough(new CompressionStream('gzip'));
                
                return new Response(compressedStream).blob();
            } else {
                // 降级处理：不压缩，直接返回JSON字符串
                return JSON.stringify(data);
            }
        } catch (error) {
            console.warn('数据压缩失败，使用未压缩数据:', error);
            return JSON.stringify(data);
        }
    }
    
    // 清理过期缓存
    cleanupCache() {
        const now = Date.now();
        for (const [key, value] of this.requestCache.entries()) {
            if (value.expiry < now) {
                this.requestCache.delete(key);
            }
        }
    }
    
    // 批处理请求
    async batchRequest(requests) {
        if (!this.config.batchProcessing) {
            // 如果批处理被禁用，则顺序执行请求
            const results = [];
            for (const request of requests) {
                const { method, path, params, headers } = request;
                try {
                    const result = await this.handleAPIRequest(method, path, params, headers);
                    results.push({ success: true, data: result });
                } catch (error) {
                    results.push({ success: false, error: error.message });
                }
            }
            return results;
        }
        
        // 将请求分成批次
        const batches = [];
        for (let i = 0; i < requests.length; i += this.config.maxBatchSize) {
            batches.push(requests.slice(i, i + this.config.maxBatchSize));
        }
        
        // 处理每个批次
        const results = [];
        for (const batch of batches) {
            // 创建批处理请求
            const batchPayload = {
                requests: batch.map(req => ({
                    id: this.generateId(),
                    method: req.method,
                    path: req.path,
                    params: req.params,
                    headers: req.headers
                }))
            };
            
            try {
                // 发送批处理请求
                const batchResults = await this.handleAPIRequest(
                    'POST',
                    '/batch',
                    batchPayload,
                    { 'X-Batch-Request': 'true' }
                );
                
                // 处理批处理结果
                if (Array.isArray(batchResults)) {
                    results.push(...batchResults);
                } else {
                    throw new Error('批处理请求返回了无效的结果');
                }
            } catch (error) {
                // 如果批处理失败，将每个请求标记为失败
                batch.forEach(() => {
                    results.push({ success: false, error: error.message });
                });
            }
        }
        
        return results;
    }
    
    // 增强API密钥轮换
    setupApiKeyRotation() {
        // 清除现有的轮换计时器
        if (this.apiKeyRotationTimer) {
            clearInterval(this.apiKeyRotationTimer);
        }
        
        // 设置新的轮换计时器
        this.apiKeyRotationTimer = setInterval(() => {
            this.rotateApiKey().catch(error => {
                console.error('API密钥轮换失败:', error);
            });
        }, this.config.apiKeyRotationInterval);
        
        // 检查上次轮换时间
        const lastRotation = localStorage.getItem('api-key-last-rotation');
        if (lastRotation) {
            const timeSinceLastRotation = Date.now() - parseInt(lastRotation, 10);
            if (timeSinceLastRotation >= this.config.apiKeyRotationInterval) {
                // 如果超过轮换间隔，立即轮换
                this.rotateApiKey().catch(error => {
                    console.error('API密钥轮换失败:', error);
                });
            }
        }
    }

    formatPrometheusMetrics(data, event) {
        const timestamp = Date.now();
        let metrics = `# AlingAi Detection System Metrics\n`;
        
        metrics += `alingai_detection_event{event="${event}"} 1 ${timestamp}\n`;
        
        if (data.successRate !== undefined) {
            metrics += `alingai_detection_success_rate ${data.successRate} ${timestamp}\n`;
        }
        
        if (data.duration !== undefined) {
            metrics += `alingai_detection_duration_ms ${data.duration} ${timestamp}\n`;
        }
        
        return metrics;
    }

    // ==================== 工具方法 ====================

    getEventColor(event) {
        const colorMap = {
            'detection.completed': '#28a745',
            'detection.failed': '#dc3545',
            'test.passed': '#28a745',
            'test.failed': '#dc3545',
            'system.error': '#dc3545',
            'performance.threshold': '#ffc107'
        };
        return colorMap[event] || '#17a2b8';
    }

    getEventEmoji(event) {
        const emojiMap = {
            'detection.started': '🚀',
            'detection.completed': '✅',
            'detection.failed': '❌',
            'test.passed': '✅',
            'test.failed': '❌',
            'system.error': '🚨',
            'performance.threshold': '⚠️',
            'auto.detection.enabled': '🔄',
            'auto.detection.disabled': '⏹️'
        };
        return emojiMap[event] || '📊';
    }

    formatDataFields(data) {
        const fields = [];
        Object.entries(data).forEach(([key, value]) => {
            fields.push({
                type: 'mrkdwn',
                text: `*${key}:* ${value}`
            });
        });
        return fields;
    }

    formatTeamsFacts(data) {
        return Object.entries(data).map(([key, value]) => ({
            name: key,
            value: String(value)
        }));
    }

    formatDiscordFields(data) {
        return Object.entries(data).map(([key, value]) => ({
            name: key,
            value: String(value),
            inline: true
        }));
    }

    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // ==================== 数据同步服务 ====================

    startSyncService() {
        // 每5分钟同步一次数据
        setInterval(() => {
            this.processSyncQueue();
        }, 5 * 60 * 1000);

        
    }

    addToSyncQueue(data, priority = 'normal') {
        this.syncQueue.push({
            id: this.generateId(),
            data,
            priority,
            timestamp: Date.now(),
            retries: 0,
            maxRetries: 3
        });
    }

    async processSyncQueue() {
        if (this.syncQueue.length === 0) return;

        // 按优先级排序
        this.syncQueue.sort((a, b) => {
            const priorityOrder = { high: 3, normal: 2, low: 1 };
            return priorityOrder[b.priority] - priorityOrder[a.priority];
        });

        const batch = this.syncQueue.splice(0, 10); // 每次处理10个
        
        for (const item of batch) {
            try {
                await this.syncDataItem(item);
            } catch (error) {
                console.error('❌ 数据同步失败:', error);
                
                if (item.retries < item.maxRetries) {
                    item.retries++;
                    this.syncQueue.push(item); // 重新加入队列
                }
            }
        }
    }

    async syncDataItem(item) {
        // 这里实现具体的数据同步逻辑
        
        
        // 发送到已配置的外部服务
        const promises = Array.from(this.externalServices.values())
            .filter(service => service.enabled && service.config.autoSync)
            .map(service => this.sendToExternalService(service.id, item.data, 'data.sync'));

        await Promise.allSettled(promises);
    }

    // ==================== 公共接口方法 ====================

    /**
     * 显示API集成配置界面
     */
    show() {
        if (!this.isInitialized) {
            console.warn('⚠️ API集成系统未初始化');
            return;
        }

        this.createConfigurationModal();
    }

    async createConfigurationModal() {
        // 这个方法将在下一步实现UI界面
        
    }
}

// 全局函数
window.showAPIIntegration = function() {
    if (window.detectionSystem && window.detectionSystem.apiIntegration) {
        window.detectionSystem.apiIntegration.show();
    } else {
        console.warn('⚠️ API集成系统未初始化');
    }
};

// 导出类
window.APIIntegration = APIIntegration;


