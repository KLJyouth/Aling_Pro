/**
 * AlingAi Pro - 数据管理器
 * 现代化的数据状态管理系统，支持响应式数据、数据缓存和API集成
 * 
 * @version 2.0.0
 * @author AlingAi Team
 * @features
 * - 响应式数据绑定
 * - 数据缓存策略
 * - API集成管理
 * - 数据验证
 * - 离线数据同步
 * - 数据持久化
 */

class AlingDataManager {
    constructor(options = {}) {
        this.options = {
            apiBaseUrl: '/api',
            enableCache: true,
            cacheExpiration: 5 * 60 * 1000, // 5分钟
            enablePersistence: true,
            storagePrefix: 'aling_data_',
            enableOfflineSync: true,
            retryAttempts: 3,
            retryDelay: 1000,
            enableValidation: true,
            ...options
        };

        this.stores = new Map();
        this.cache = new Map();
        this.observers = new Map();
        this.validators = new Map();
        this.api = new APIManager(this.options);
        this.syncQueue = [];
        this.isOnline = navigator.onLine;
        
        this.init();
    }

    init() {
        this.setupNetworkListener();
        this.loadPersistedData();
        this.processSyncQueue();
        
        console.log('✅ AlingAi 数据管理器初始化完成');
    }

    /**
     * 创建数据存储
     * @param {string} name - 存储名称
     * @param {object} initialState - 初始状态
     * @param {object} options - 选项
     */
    createStore(name, initialState = {}, options = {}) {
        const store = new DataStore(name, initialState, {
            ...this.options,
            ...options,
            dataManager: this
        });

        this.stores.set(name, store);
        
        // 如果启用持久化，尝试恢复数据
        if (this.options.enablePersistence) {
            this.restoreStore(name);
        }

        return store;
    }

    /**
     * 获取数据存储
     * @param {string} name - 存储名称
     */
    getStore(name) {
        return this.stores.get(name);
    }

    /**
     * 删除数据存储
     * @param {string} name - 存储名称
     */
    removeStore(name) {
        const store = this.stores.get(name);
        if (store) {
            store.destroy();
            this.stores.delete(name);
            
            // 清除持久化数据
            if (this.options.enablePersistence) {
                this.clearPersistedStore(name);
            }
        }
    }

    /**
     * 设置网络监听器
     */
    setupNetworkListener() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.processSyncQueue();
            this.emit('network:online');
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.emit('network:offline');
        });
    }

    /**
     * 加载持久化数据
     */
    loadPersistedData() {
        if (!this.options.enablePersistence) return;

        try {
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith(this.options.storagePrefix)) {
                    const storeName = key.replace(this.options.storagePrefix, '');
                    this.restoreStore(storeName);
                }
            });
        } catch (error) {
            console.warn('加载持久化数据失败:', error);
        }
    }

    /**
     * 恢复存储数据
     * @param {string} name - 存储名称
     */
    restoreStore(name) {
        try {
            const key = this.options.storagePrefix + name;
            const data = localStorage.getItem(key);
            
            if (data) {
                const parsed = JSON.parse(data);
                const store = this.stores.get(name);
                
                if (store && parsed.timestamp) {
                    // 检查数据是否过期
                    const now = Date.now();
                    if (now - parsed.timestamp < this.options.cacheExpiration) {
                        store.setState(parsed.state, { silent: true });
                    }
                }
            }
        } catch (error) {
            console.warn(`恢复存储 ${name} 失败:`, error);
        }
    }

    /**
     * 持久化存储数据
     * @param {string} name - 存储名称
     * @param {object} state - 状态数据
     */
    persistStore(name, state) {
        if (!this.options.enablePersistence) return;

        try {
            const key = this.options.storagePrefix + name;
            const data = {
                state,
                timestamp: Date.now()
            };
            
            localStorage.setItem(key, JSON.stringify(data));
        } catch (error) {
            console.warn(`持久化存储 ${name} 失败:`, error);
        }
    }

    /**
     * 清除持久化存储
     * @param {string} name - 存储名称
     */
    clearPersistedStore(name) {
        try {
            const key = this.options.storagePrefix + name;
            localStorage.removeItem(key);
        } catch (error) {
            console.warn(`清除持久化存储 ${name} 失败:`, error);
        }
    }

    /**
     * 处理同步队列
     */
    async processSyncQueue() {
        if (!this.isOnline || this.syncQueue.length === 0) return;

        const queue = [...this.syncQueue];
        this.syncQueue = [];

        for (const operation of queue) {
            try {
                await this.executeOperation(operation);
                this.emit('sync:success', { operation });
            } catch (error) {
                console.error('同步操作失败:', error);
                // 重新添加到队列
                this.syncQueue.push(operation);
                this.emit('sync:error', { operation, error });
            }
        }
    }

    /**
     * 执行操作
     * @param {object} operation - 操作对象
     */
    async executeOperation(operation) {
        const { type, endpoint, data, options = {} } = operation;
        
        switch (type) {
            case 'GET':
                return await this.api.get(endpoint, options);
            case 'POST':
                return await this.api.post(endpoint, data, options);
            case 'PUT':
                return await this.api.put(endpoint, data, options);
            case 'DELETE':
                return await this.api.delete(endpoint, options);
            default:
                throw new Error(`未知操作类型: ${type}`);
        }
    }

    /**
     * 添加到同步队列
     * @param {object} operation - 操作对象
     */
    addToSyncQueue(operation) {
        if (this.options.enableOfflineSync) {
            this.syncQueue.push({
                ...operation,
                timestamp: Date.now()
            });
        }
    }

    /**
     * 注册数据验证器
     * @param {string} name - 验证器名称
     * @param {function} validator - 验证函数
     */
    registerValidator(name, validator) {
        this.validators.set(name, validator);
    }

    /**
     * 验证数据
     * @param {string} validatorName - 验证器名称
     * @param {any} data - 要验证的数据
     */
    validateData(validatorName, data) {
        const validator = this.validators.get(validatorName);
        if (!validator) {
            throw new Error(`验证器 ${validatorName} 不存在`);
        }
        
        return validator(data);
    }

    /**
     * 设置缓存
     * @param {string} key - 缓存键
     * @param {any} data - 缓存数据
     * @param {number} ttl - 生存时间
     */
    setCache(key, data, ttl = this.options.cacheExpiration) {
        this.cache.set(key, {
            data,
            timestamp: Date.now(),
            ttl
        });
    }

    /**
     * 获取缓存
     * @param {string} key - 缓存键
     */
    getCache(key) {
        const cached = this.cache.get(key);
        if (!cached) return null;

        const now = Date.now();
        if (now - cached.timestamp > cached.ttl) {
            this.cache.delete(key);
            return null;
        }

        return cached.data;
    }

    /**
     * 清除缓存
     * @param {string} key - 缓存键，不提供则清除所有
     */
    clearCache(key = null) {
        if (key) {
            this.cache.delete(key);
        } else {
            this.cache.clear();
        }
    }

    /**
     * 事件发射器
     */
    emit(eventName, data) {
        const event = new CustomEvent(`data:${eventName}`, { detail: data });
        document.dispatchEvent(event);
    }

    /**
     * 销毁数据管理器
     */
    destroy() {
        this.stores.forEach(store => store.destroy());
        this.stores.clear();
        this.cache.clear();
        this.observers.clear();
        this.validators.clear();
        this.syncQueue = [];
    }
}

/**
 * 数据存储类
 */
class DataStore {
    constructor(name, initialState = {}, options = {}) {
        this.name = name;
        this.state = { ...initialState };
        this.options = options;
        this.observers = new Map();
        this.computed = new Map();
        this.mutations = new Map();
        this.actions = new Map();
        this.getters = new Map();
        this.isLoading = false;
        this.errors = new Map();
        
        this.setupReactivity();
    }

    /**
     * 设置响应式系统
     */
    setupReactivity() {
        this.proxy = new Proxy(this.state, {
            set: (target, property, value) => {
                const oldValue = target[property];
                target[property] = value;
                
                // 触发观察者
                this.notifyObservers(property, value, oldValue);
                
                // 持久化存储
                if (this.options.dataManager) {
                    this.options.dataManager.persistStore(this.name, this.state);
                }
                
                return true;
            },
            
            get: (target, property) => {
                // 如果是计算属性
                if (this.computed.has(property)) {
                    return this.computed.get(property)();
                }
                
                // 如果是getter
                if (this.getters.has(property)) {
                    return this.getters.get(property)(this.state);
                }
                
                return target[property];
            }
        });
    }

    /**
     * 获取状态
     * @param {string} key - 状态键
     */
    getState(key = null) {
        if (key) {
            return this.proxy[key];
        }
        return { ...this.state };
    }

    /**
     * 设置状态
     * @param {object|string} keyOrState - 键或状态对象
     * @param {any} value - 值
     * @param {object} options - 选项
     */
    setState(keyOrState, value = null, options = {}) {
        if (typeof keyOrState === 'object') {
            // 批量设置
            Object.entries(keyOrState).forEach(([key, val]) => {
                this.proxy[key] = val;
            });
        } else {
            // 单个设置
            this.proxy[keyOrState] = value;
        }

        if (!options.silent) {
            this.emit('state:changed', { 
                store: this.name,
                state: this.getState() 
            });
        }
    }

    /**
     * 更新状态
     * @param {string} key - 状态键
     * @param {function} updater - 更新函数
     */
    updateState(key, updater) {
        const currentValue = this.proxy[key];
        const newValue = updater(currentValue);
        this.setState(key, newValue);
    }

    /**
     * 观察状态变化
     * @param {string|function} keyOrObserver - 键或观察者函数
     * @param {function} observer - 观察者函数
     */
    observe(keyOrObserver, observer = null) {
        if (typeof keyOrObserver === 'function') {
            // 观察整个状态
            const id = Symbol('observer');
            this.observers.set(id, {
                key: '*',
                callback: keyOrObserver
            });
            return id;
        } else {
            // 观察特定键
            const id = Symbol('observer');
            this.observers.set(id, {
                key: keyOrObserver,
                callback: observer
            });
            return id;
        }
    }

    /**
     * 取消观察
     * @param {symbol} observerId - 观察者ID
     */
    unobserve(observerId) {
        this.observers.delete(observerId);
    }

    /**
     * 通知观察者
     * @param {string} key - 变化的键
     * @param {any} newValue - 新值
     * @param {any} oldValue - 旧值
     */
    notifyObservers(key, newValue, oldValue) {
        this.observers.forEach(observer => {
            if (observer.key === '*' || observer.key === key) {
                try {
                    observer.callback(newValue, oldValue, key);
                } catch (error) {
                    console.error('观察者回调执行失败:', error);
                }
            }
        });
    }

    /**
     * 定义计算属性
     * @param {string} name - 属性名
     * @param {function} computed - 计算函数
     */
    defineComputed(name, computed) {
        this.computed.set(name, () => computed(this.state));
    }

    /**
     * 定义getter
     * @param {string} name - getter名
     * @param {function} getter - getter函数
     */
    defineGetter(name, getter) {
        this.getters.set(name, getter);
    }

    /**
     * 定义mutation
     * @param {string} name - mutation名
     * @param {function} mutation - mutation函数
     */
    defineMutation(name, mutation) {
        this.mutations.set(name, mutation);
    }

    /**
     * 提交mutation
     * @param {string} name - mutation名
     * @param {any} payload - 载荷
     */
    commit(name, payload) {
        const mutation = this.mutations.get(name);
        if (!mutation) {
            throw new Error(`Mutation ${name} 不存在`);
        }

        mutation(this.state, payload);
        this.emit('mutation:committed', { name, payload });
    }

    /**
     * 定义action
     * @param {string} name - action名
     * @param {function} action - action函数
     */
    defineAction(name, action) {
        this.actions.set(name, action);
    }

    /**
     * 分发action
     * @param {string} name - action名
     * @param {any} payload - 载荷
     */
    async dispatch(name, payload) {
        const action = this.actions.get(name);
        if (!action) {
            throw new Error(`Action ${name} 不存在`);
        }

        this.setLoading(name, true);
        this.clearError(name);

        try {
            const context = {
                state: this.state,
                commit: this.commit.bind(this),
                dispatch: this.dispatch.bind(this),
                getters: this.getters
            };

            const result = await action(context, payload);
            this.emit('action:dispatched', { name, payload, result });
            return result;

        } catch (error) {
            this.setError(name, error);
            this.emit('action:error', { name, payload, error });
            throw error;

        } finally {
            this.setLoading(name, false);
        }
    }

    /**
     * 设置加载状态
     * @param {string} action - action名
     * @param {boolean} loading - 加载状态
     */
    setLoading(action, loading) {
        if (loading) {
            this.isLoading = true;
        } else {
            // 检查是否还有其他loading的action
            this.isLoading = Array.from(this.errors.keys()).some(key => 
                key.endsWith('_loading') && this.errors.get(key)
            );
        }
        
        this.errors.set(`${action}_loading`, loading);
        this.emit('loading:changed', { action, loading });
    }

    /**
     * 设置错误
     * @param {string} action - action名
     * @param {Error} error - 错误对象
     */
    setError(action, error) {
        this.errors.set(action, error);
        this.emit('error:set', { action, error });
    }

    /**
     * 清除错误
     * @param {string} action - action名
     */
    clearError(action) {
        this.errors.delete(action);
        this.emit('error:cleared', { action });
    }

    /**
     * 获取错误
     * @param {string} action - action名
     */
    getError(action) {
        return this.errors.get(action);
    }

    /**
     * 重置状态
     */
    reset() {
        Object.keys(this.state).forEach(key => {
            delete this.state[key];
        });
        this.errors.clear();
        this.isLoading = false;
        this.emit('state:reset');
    }

    /**
     * 事件发射器
     */
    emit(eventName, data) {
        const event = new CustomEvent(`store:${eventName}`, { 
            detail: { store: this.name, ...data } 
        });
        document.dispatchEvent(event);
    }

    /**
     * 销毁存储
     */
    destroy() {
        this.observers.clear();
        this.computed.clear();
        this.mutations.clear();
        this.actions.clear();
        this.getters.clear();
        this.errors.clear();
    }
}

/**
 * API管理器
 */
class APIManager {
    constructor(options = {}) {
        this.options = options;
        this.interceptors = {
            request: [],
            response: []
        };
    }

    /**
     * 添加请求拦截器
     * @param {function} interceptor - 拦截器函数
     */
    addRequestInterceptor(interceptor) {
        this.interceptors.request.push(interceptor);
    }

    /**
     * 添加响应拦截器
     * @param {function} interceptor - 拦截器函数
     */
    addResponseInterceptor(interceptor) {
        this.interceptors.response.push(interceptor);
    }

    /**
     * 处理请求
     * @param {string} url - 请求URL
     * @param {object} options - 请求选项
     */
    async request(url, options = {}) {
        // 应用请求拦截器
        let config = { url, ...options };
        for (const interceptor of this.interceptors.request) {
            config = await interceptor(config);
        }

        try {
            let response = await fetch(config.url, config);
            
            // 应用响应拦截器
            for (const interceptor of this.interceptors.response) {
                response = await interceptor(response);
            }

            return response;

        } catch (error) {
            // 如果离线，添加到同步队列
            if (!navigator.onLine && this.options.dataManager) {
                this.options.dataManager.addToSyncQueue({
                    type: options.method || 'GET',
                    endpoint: url,
                    data: options.body,
                    options
                });
            }
            throw error;
        }
    }

    /**
     * GET请求
     */
    async get(url, options = {}) {
        return this.request(url, { 
            method: 'GET', 
            ...options 
        });
    }

    /**
     * POST请求
     */
    async post(url, data, options = {}) {
        return this.request(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            body: JSON.stringify(data),
            ...options
        });
    }

    /**
     * PUT请求
     */
    async put(url, data, options = {}) {
        return this.request(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            body: JSON.stringify(data),
            ...options
        });
    }

    /**
     * DELETE请求
     */
    async delete(url, options = {}) {
        return this.request(url, {
            method: 'DELETE',
            ...options
        });
    }
}

// 导出
window.AlingDataManager = AlingDataManager;
window.DataStore = DataStore;
window.APIManager = APIManager;

// 创建全局数据管理器实例
window.dataManager = new AlingDataManager();

console.log('✅ AlingAi 数据管理器已加载');
