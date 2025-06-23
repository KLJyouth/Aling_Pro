/**
 * 状态管理器
 * 基于观察者模式的状态管理
 */

class StateManager {
    constructor() {
        this.state = new Proxy({}, {
            set: (target, property, value) => {
                const oldValue = target[property];
                target[property] = value;
                
                // 通知订阅者
                this.notifySubscribers(property, value, oldValue);
                return true;
            }
        });
        
        this.subscribers = new Map();
        this.middlewares = [];
        this.history = [];
        this.maxHistorySize = 50;
    }

    /**
     * 获取状态
     */
    getState(key = null) {
        if (key === null) {
            return { ...this.state };
        }
        return this.getNestedValue(this.state, key);
    }

    /**
     * 设置状态
     */
    setState(key, value) {
        // 执行中间件
        const action = { type: 'SET_STATE', key, value, timestamp: Date.now() };
        const processedAction = this.executeMiddlewares(action);
        
        if (processedAction === null) {
            return; // 中间件阻止了这个操作
        }

        // 记录历史
        this.addToHistory(key, this.getNestedValue(this.state, key), value);

        // 设置值
        this.setNestedValue(this.state, key, value);
    }

    /**
     * 批量设置状态
     */
    setBatchState(updates) {
        const action = { type: 'SET_BATCH_STATE', updates, timestamp: Date.now() };
        const processedAction = this.executeMiddlewares(action);
        
        if (processedAction === null) {
            return;
        }

        Object.entries(updates).forEach(([key, value]) => {
            this.addToHistory(key, this.getNestedValue(this.state, key), value);
            this.setNestedValue(this.state, key, value);
        });
    }

    /**
     * 订阅状态变化
     */
    subscribe(key, callback) {
        if (!this.subscribers.has(key)) {
            this.subscribers.set(key, new Set());
        }
        this.subscribers.get(key).add(callback);

        // 返回取消订阅函数
        return () => {
            this.unsubscribe(key, callback);
        };
    }

    /**
     * 取消订阅
     */
    unsubscribe(key, callback) {
        if (this.subscribers.has(key)) {
            this.subscribers.get(key).delete(callback);
        }
    }

    /**
     * 通知订阅者
     */
    notifySubscribers(key, newValue, oldValue) {
        // 通知精确匹配的订阅者
        if (this.subscribers.has(key)) {
            this.subscribers.get(key).forEach(callback => {
                try {
                    callback(newValue, oldValue, key);
                } catch (error) {
                    console.error('状态订阅者回调错误:', error);
                }
            });
        }

        // 通知父级路径的订阅者
        const keyParts = key.split('.');
        for (let i = keyParts.length - 1; i > 0; i--) {
            const parentKey = keyParts.slice(0, i).join('.');
            if (this.subscribers.has(parentKey)) {
                const parentValue = this.getNestedValue(this.state, parentKey);
                this.subscribers.get(parentKey).forEach(callback => {
                    try {
                        callback(parentValue, undefined, parentKey);
                    } catch (error) {
                        console.error('状态订阅者回调错误:', error);
                    }
                });
            }
        }

        // 通知通配符订阅者
        if (this.subscribers.has('*')) {
            this.subscribers.get('*').forEach(callback => {
                try {
                    callback(newValue, oldValue, key);
                } catch (error) {
                    console.error('状态订阅者回调错误:', error);
                }
            });
        }
    }

    /**
     * 添加中间件
     */
    addMiddleware(middleware) {
        this.middlewares.push(middleware);
    }

    /**
     * 执行中间件
     */
    executeMiddlewares(action) {
        let currentAction = action;
        
        for (const middleware of this.middlewares) {
            try {
                currentAction = middleware(currentAction, this.state);
                if (currentAction === null || currentAction === false) {
                    return null; // 中间件阻止了操作
                }
            } catch (error) {
                console.error('中间件执行错误:', error);
                return null;
            }
        }
        
        return currentAction;
    }

    /**
     * 添加到历史记录
     */
    addToHistory(key, oldValue, newValue) {
        this.history.push({
            key,
            oldValue: this.deepClone(oldValue),
            newValue: this.deepClone(newValue),
            timestamp: Date.now()
        });

        // 限制历史记录大小
        if (this.history.length > this.maxHistorySize) {
            this.history.shift();
        }
    }

    /**
     * 获取历史记录
     */
    getHistory(key = null) {
        if (key === null) {
            return [...this.history];
        }
        return this.history.filter(record => record.key === key);
    }

    /**
     * 清除历史记录
     */
    clearHistory() {
        this.history = [];
    }

    /**
     * 撤销操作
     */
    undo(key = null) {
        if (this.history.length === 0) {
            return false;
        }

        let targetRecord;
        if (key === null) {
            targetRecord = this.history.pop();
        } else {
            const index = this.history.findLastIndex(record => record.key === key);
            if (index === -1) {
                return false;
            }
            targetRecord = this.history.splice(index, 1)[0];
        }

        // 恢复旧值
        this.setNestedValue(this.state, targetRecord.key, targetRecord.oldValue);
        return true;
    }

    /**
     * 获取嵌套值
     */
    getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => {
            return current && current[key] !== undefined ? current[key] : undefined;
        }, obj);
    }

    /**
     * 设置嵌套值
     */
    setNestedValue(obj, path, value) {
        const keys = path.split('.');
        const lastKey = keys.pop();
        const target = keys.reduce((current, key) => {
            if (current[key] === undefined || current[key] === null) {
                current[key] = {};
            }
            return current[key];
        }, obj);
        
        target[lastKey] = value;
    }

    /**
     * 深度克隆
     */
    deepClone(obj) {
        if (obj === null || typeof obj !== 'object') {
            return obj;
        }
        
        if (obj instanceof Date) {
            return new Date(obj);
        }
        
        if (Array.isArray(obj)) {
            return obj.map(item => this.deepClone(item));
        }
        
        const cloned = {};
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                cloned[key] = this.deepClone(obj[key]);
            }
        }
        return cloned;
    }

    /**
     * 重置状态
     */
    reset(key = null) {
        if (key === null) {
            // 重置全部状态
            Object.keys(this.state).forEach(k => {
                delete this.state[k];
            });
        } else {
            // 重置指定状态
            this.setNestedValue(this.state, key, undefined);
        }
    }

    /**
     * 计算属性
     */
    computed(key, computeFn, dependencies = []) {
        const compute = () => {
            try {
                const result = computeFn(this.state);
                this.setState(key, result);
            } catch (error) {
                console.error('计算属性错误:', error);
            }
        };

        // 初始计算
        compute();

        // 监听依赖变化
        dependencies.forEach(dep => {
            this.subscribe(dep, compute);
        });

        // 监听全局变化（如果没有指定依赖）
        if (dependencies.length === 0) {
            this.subscribe('*', compute);
        }
    }

    /**
     * 创建响应式对象
     */
    reactive(initialState = {}) {
        const reactiveObj = { ...initialState };
        
        Object.keys(reactiveObj).forEach(key => {
            this.setState(key, reactiveObj[key]);
        });

        return new Proxy(reactiveObj, {
            get: (target, property) => {
                return this.getState(property.toString());
            },
            set: (target, property, value) => {
                this.setState(property.toString(), value);
                return true;
            }
        });
    }

    /**
     * 持久化状态到localStorage
     */
    persist(key, storageKey = null) {
        const actualStorageKey = storageKey || `state_${key}`;
        
        // 从localStorage恢复状态
        try {
            const stored = localStorage.getItem(actualStorageKey);
            if (stored) {
                const value = JSON.parse(stored);
                this.setState(key, value);
            }
        } catch (error) {
            console.error('恢复持久化状态失败:', error);
        }

        // 监听状态变化并保存
        this.subscribe(key, (value) => {
            try {
                localStorage.setItem(actualStorageKey, JSON.stringify(value));
            } catch (error) {
                console.error('保存持久化状态失败:', error);
            }
        });
    }

    /**
     * 获取调试信息
     */
    getDebugInfo() {
        return {
            state: this.getState(),
            subscribers: Array.from(this.subscribers.keys()),
            middlewares: this.middlewares.length,
            historySize: this.history.length
        };
    }
}

// 创建默认的中间件
const loggingMiddleware = (action, state) => {
    if (window.app && window.app.config.debug) {
        
    }
    return action;
};

const validationMiddleware = (action, state) => {
    // 可以在这里添加状态验证逻辑
    return action;
};

export { StateManager, loggingMiddleware, validationMiddleware };
