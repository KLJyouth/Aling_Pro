/**
 * WebSocket管理器
 * 提供实时通信功能
 */

class WebSocketManager {
    constructor(url) {
        this.url = url;
        this.ws = null;
        this.connectionState = 'disconnected'; // disconnected, connecting, connected, reconnecting
        this.messageQueue = [];
        this.listeners = new Map();
        this.heartbeatInterval = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.heartbeatDelay = 30000;
        this.eventBus = new EventTarget();
        
        this.setupEventHandlers();
    }

    /**
     * 建立连接
     */
    async connect() {
        if (this.connectionState === 'connected' || this.connectionState === 'connecting') {
            return Promise.resolve();
        }

        return new Promise((resolve, reject) => {
            try {
                this.connectionState = 'connecting';
                this.emit('connecting');

                // 添加认证token到URL
                const token = localStorage.getItem('auth_token');
                const wsUrl = token ? `${this.url}?token=${token}` : this.url;

                this.ws = new WebSocket(wsUrl);
                
                // 设置超时
                const connectTimeout = setTimeout(() => {
                    if (this.connectionState === 'connecting') {
                        this.ws.close();
                        reject(new Error('WebSocket连接超时'));
                    }
                }, 10000);

                this.ws.onopen = () => {
                    clearTimeout(connectTimeout);
                    this.connectionState = 'connected';
                    this.reconnectAttempts = 0;
                    this.startHeartbeat();
                    this.sendQueuedMessages();
                    this.emit('connected');
                    resolve();
                };

                this.ws.onclose = (event) => {
                    clearTimeout(connectTimeout);
                    this.stopHeartbeat();
                    
                    if (this.connectionState === 'connected') {
                        this.connectionState = 'disconnected';
                        this.emit('disconnected', { code: event.code, reason: event.reason });
                        
                        // 自动重连
                        if (event.code !== 1000) { // 非正常关闭
                            this.handleReconnect();
                        }
                    } else if (this.connectionState === 'connecting') {
                        reject(new Error(`WebSocket连接失败: ${event.reason || event.code}`));
                    }
                };

                this.ws.onerror = (error) => {
                    clearTimeout(connectTimeout);
                    console.error('WebSocket错误:', error);
                    this.emit('error', error);
                    
                    if (this.connectionState === 'connecting') {
                        reject(error);
                    }
                };

                this.ws.onmessage = (event) => {
                    this.handleMessage(event.data);
                };

            } catch (error) {
                this.connectionState = 'disconnected';
                reject(error);
            }
        });
    }

    /**
     * 断开连接
     */
    disconnect() {
        if (this.ws) {
            this.connectionState = 'disconnected';
            this.stopHeartbeat();
            this.ws.close(1000, '用户主动断开连接');
            this.ws = null;
        }
    }

    /**
     * 发送消息
     */
    send(type, data = {}) {
        const message = {
            type,
            data,
            timestamp: Date.now(),
            id: this.generateMessageId()
        };

        if (this.connectionState === 'connected') {
            try {
                this.ws.send(JSON.stringify(message));
                return true;
            } catch (error) {
                console.error('发送消息失败:', error);
                this.messageQueue.push(message);
                return false;
            }
        } else {
            // 连接未建立，加入队列
            this.messageQueue.push(message);
            return false;
        }
    }

    /**
     * 监听消息
     */
    on(type, handler) {
        if (!this.listeners.has(type)) {
            this.listeners.set(type, new Set());
        }
        this.listeners.get(type).add(handler);
    }

    /**
     * 移除监听器
     */
    off(type, handler) {
        if (this.listeners.has(type)) {
            this.listeners.get(type).delete(handler);
        }
    }

    /**
     * 监听系统事件
     */
    onSystemEvent(eventName, handler) {
        this.eventBus.addEventListener(eventName, handler);
    }

    /**
     * 移除系统事件监听器
     */
    offSystemEvent(eventName, handler) {
        this.eventBus.removeEventListener(eventName, handler);
    }

    /**
     * 发送系统事件
     */
    emit(eventName, data = null) {
        this.eventBus.dispatchEvent(new CustomEvent(eventName, { detail: data }));
    }

    /**
     * 处理接收到的消息
     */
    handleMessage(rawMessage) {
        try {
            const message = JSON.parse(rawMessage);
            
            // 处理心跳响应
            if (message.type === 'pong') {
                return;
            }

            // 触发对应的监听器
            if (this.listeners.has(message.type)) {
                this.listeners.get(message.type).forEach(handler => {
                    try {
                        handler(message.data, message);
                    } catch (error) {
                        console.error('消息处理器错误:', error);
                    }
                });
            }

            // 触发通用消息事件
            this.emit('message', { type: message.type, data: message.data, message });

        } catch (error) {
            console.error('解析WebSocket消息失败:', error, rawMessage);
        }
    }

    /**
     * 发送队列中的消息
     */
    sendQueuedMessages() {
        while (this.messageQueue.length > 0 && this.connectionState === 'connected') {
            const message = this.messageQueue.shift();
            try {
                this.ws.send(JSON.stringify(message));
            } catch (error) {
                console.error('发送队列消息失败:', error);
                // 重新加入队列开头
                this.messageQueue.unshift(message);
                break;
            }
        }
    }

    /**
     * 开始心跳
     */
    startHeartbeat() {
        this.stopHeartbeat();
        this.heartbeatInterval = setInterval(() => {
            if (this.connectionState === 'connected') {
                this.send('ping');
            }
        }, this.heartbeatDelay);
    }

    /**
     * 停止心跳
     */
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }

    /**
     * 处理重连
     */
    handleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            this.emit('reconnectFailed');
            window.app.getService('notifications').error(
                '连接失败', 
                '无法连接到服务器，请刷新页面重试'
            );
            return;
        }

        this.connectionState = 'reconnecting';
        this.reconnectAttempts++;
        this.emit('reconnecting', { attempt: this.reconnectAttempts });

        const delay = Math.min(this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1), 30000);
        
        setTimeout(() => {
            if (this.connectionState === 'reconnecting') {
                this.connect().catch(() => {
                    // 重连失败，继续尝试
                    if (this.connectionState === 'reconnecting') {
                        this.handleReconnect();
                    }
                });
            }
        }, delay);
    }

    /**
     * 设置事件处理器
     */
    setupEventHandlers() {
        // 监听页面可见性变化
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.connectionState === 'disconnected') {
                // 页面变为可见且连接断开时，尝试重连
                this.connect().catch(console.error);
            }
        });

        // 监听网络状态变化
        window.addEventListener('online', () => {
            if (this.connectionState === 'disconnected') {
                this.connect().catch(console.error);
            }
        });

        window.addEventListener('offline', () => {
            if (this.connectionState === 'connected') {
                this.emit('networkOffline');
            }
        });
    }

    /**
     * 生成消息ID
     */
    generateMessageId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    /**
     * 获取连接状态
     */
    getConnectionState() {
        return this.connectionState;
    }

    /**
     * 是否已连接
     */
    isConnected() {
        return this.connectionState === 'connected';
    }

    /**
     * 获取连接统计信息
     */
    getStats() {
        return {
            connectionState: this.connectionState,
            reconnectAttempts: this.reconnectAttempts,
            queuedMessages: this.messageQueue.length,
            listeners: Array.from(this.listeners.keys())
        };
    }
}

export { WebSocketManager };
