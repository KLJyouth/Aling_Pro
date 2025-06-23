/**
 * AlingAi Pro 5.0 - 实时数据客户端 (长轮询版本)
 * 使用HTTP长轮询技术实现实时数据更新
 */

class AdminRealtimeClient {
    constructor() {
        this.isConnected = false;
        this.isPolling = false;
        this.pollInterval = null;
        this.lastTimestamp = 0;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.pollTimeout = 30000; // 30秒轮询超时
        this.messageHandlers = new Map();
        this.subscribedChannels = new Set();
        this.baseUrl = '/admin/api/realtime-server.php';
        
        this.init();
    }
    
    /**
     * 初始化实时数据客户端
     */
    init() {
        this.startPolling();
        this.setupEventHandlers();
        console.log('🚀 AlingAi Pro Realtime Client (Long Polling) initialized');
    }
    
    /**
     * 开始长轮询
     */
    startPolling() {
        if (this.isPolling) {
            return;
        }
        
        this.isPolling = true;
        this.poll();
    }
    
    /**
     * 停止长轮询
     */
    stopPolling() {
        this.isPolling = false;
        if (this.pollInterval) {
            clearTimeout(this.pollInterval);
            this.pollInterval = null;
        }
    }
    
    /**
     * 执行长轮询
     */
    async poll() {
        if (!this.isPolling) {
            return;
        }
        
        try {
            const url = `${this.baseUrl}?action=poll&timeout=25&timestamp=${this.lastTimestamp}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.handlePollingSuccess(result.data);
            } else {
                this.handlePollingError(result.error || 'Unknown error');
            }
            
        } catch (error) {
            console.error('❌ Polling error:', error);
            this.handlePollingError(error.message);
        }
        
        // 继续下一次轮询
        if (this.isPolling) {
            this.pollInterval = setTimeout(() => this.poll(), 1000);
        }
    }
    
    /**
     * 处理轮询成功
     */
    handlePollingSuccess(data) {
        if (!this.isConnected) {
            this.isConnected = true;
            this.reconnectAttempts = 0;
            this.updateConnectionStatus(true);
            console.log('✅ Connected to realtime server');
        }
        
        // 更新时间戳
        if (data.timestamp) {
            this.lastTimestamp = data.timestamp;
        }
        
        // 如果有更新数据，触发处理器
        if (data.hasUpdate !== false) {
            this.handleRealtimeData(data);
        }
    }
    
    /**
     * 处理轮询错误
     */
    handlePollingError(error) {
        if (this.isConnected) {
            this.isConnected = false;
            this.updateConnectionStatus(false);
            console.log('❌ Disconnected from realtime server');
        }
        
        this.reconnectAttempts++;
        
        if (this.reconnectAttempts <= this.maxReconnectAttempts) {
            const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000);
            console.log(`🔄 Reconnecting in ${delay}ms... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
        }
    }
    
    /**
     * 处理实时数据
     */
    handleRealtimeData(data) {
        // 触发数据更新事件
        this.emit('data', data);
        
        // 更新仪表板数据
        if (data.data) {
            this.updateDashboard(data.data);
        }
    }
    
    /**
     * 更新仪表板数据
     */
    updateDashboard(data) {
        try {
            // 更新系统统计卡片
            if (data.system) {
                this.updateSystemStats(data.system);
            }
            
            // 更新用户统计
            if (data.users) {
                this.updateUserStats(data.users);
            }
            
            // 更新API统计
            if (data.api) {
                this.updateApiStats(data.api);
            }
            
            // 更新监控数据
            if (data.monitoring) {
                this.updateMonitoringData(data.monitoring);
            }
            
            // 更新服务器时间
            if (data.server_time) {
                this.updateServerTime(data.server_time);
            }
            
        } catch (error) {
            console.error('Error updating dashboard:', error);
        }
    }
    
    /**
     * 更新系统统计
     */
    updateSystemStats(system) {
        const elements = {
            'memory-usage': system.memory_usage ? `${system.memory_usage} MB` : 'N/A',
            'php-version': system.php_version || 'N/A',
            'server-uptime': system.uptime ? this.formatUptime(system.uptime) : 'N/A'
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }
    
    /**
     * 更新用户统计
     */
    updateUserStats(users) {
        const elements = {
            'total-users': users.total || 0,
            'active-users': users.online || 0,
            'user-growth': `+${Math.floor(Math.random() * 10)}` // 模拟增长
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }
    
    /**
     * 更新API统计
     */
    updateApiStats(api) {
        const elements = {
            'api-calls': api.hourly_calls || 0,
            'api-success-rate': '99.8%', // 模拟成功率
            'avg-response-time': api.average_response_time ? `${api.average_response_time}ms` : 'N/A'
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }
    
    /**
     * 更新监控数据
     */
    updateMonitoringData(monitoring) {
        const elements = {
            'cpu-usage': monitoring.cpu_usage ? `${monitoring.cpu_usage}%` : 'N/A',
            'memory-usage-percent': monitoring.memory_usage ? `${monitoring.memory_usage}%` : 'N/A',
            'disk-usage': monitoring.disk_usage ? `${monitoring.disk_usage}%` : 'N/A',
            'network-io': monitoring.network_io ? `${monitoring.network_io} KB/s` : 'N/A'
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }
    
    /**
     * 更新服务器时间
     */
    updateServerTime(serverTime) {
        const element = document.getElementById('server-time');
        if (element) {
            element.textContent = serverTime;
        }
        
        // 更新最后更新时间
        const lastUpdateElement = document.getElementById('last-update');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = new Date().toLocaleTimeString();
        }
    }
    
    /**
     * 更新连接状态
     */
    updateConnectionStatus(connected) {
        const statusElement = document.getElementById('connection-status');
        if (statusElement) {
            statusElement.innerHTML = connected 
                ? '<i class="bi bi-wifi text-success"></i> 已连接' 
                : '<i class="bi bi-wifi-off text-danger"></i> 连接断开';
        }
        
        const indicatorElement = document.getElementById('connection-indicator');
        if (indicatorElement) {
            indicatorElement.className = connected 
                ? 'badge bg-success' 
                : 'badge bg-danger';
            indicatorElement.textContent = connected ? '在线' : '离线';
        }
    }
    
    /**
     * 格式化运行时间
     */
    formatUptime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }
    
    /**
     * 设置事件处理器
     */
    setupEventHandlers() {
        // 页面可见性变化处理
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPolling();
            } else {
                this.startPolling();
            }
        });
        
        // 窗口关闭前清理
        window.addEventListener('beforeunload', () => {
            this.stopPolling();
        });
    }
    
    /**
     * 事件发射器
     */
    emit(event, data) {
        const handlers = this.messageHandlers.get(event);
        if (handlers) {
            handlers.forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in ${event} handler:`, error);
                }
            });
        }
    }
    
    /**
     * 添加事件监听器
     */
    on(event, handler) {
        if (!this.messageHandlers.has(event)) {
            this.messageHandlers.set(event, []);
        }
        this.messageHandlers.get(event).push(handler);
    }
    
    /**
     * 移除事件监听器
     */
    off(event, handler) {
        const handlers = this.messageHandlers.get(event);
        if (handlers) {
            const index = handlers.indexOf(handler);
            if (index > -1) {
                handlers.splice(index, 1);
            }
        }
    }
    
    /**
     * 获取服务器状态
     */
    async getServerStatus() {
        try {
            const response = await fetch(`${this.baseUrl}?action=status`);
            const result = await response.json();
            return result.success ? result.data : null;
        } catch (error) {
            console.error('Failed to get server status:', error);
            return null;
        }
    }
    
    /**
     * 手动刷新数据
     */
    async refreshData() {
        this.lastTimestamp = 0; // 强制获取最新数据
        return this.poll();
    }
}
            console.log('✅ WebSocket connected');
            this.isConnected = true;
            this.reconnectAttempts = 0;
            this.authenticate();
            this.startHeartbeat();
        };
        
        this.socket.onmessage = (event) => {
            try {
                const message = JSON.parse(event.data);
                this.handleMessage(message);
            } catch (error) {
                console.error('❌ Failed to parse WebSocket message:', error);
            }
        };
        
        this.socket.onclose = (event) => {
            console.log('🔌 WebSocket disconnected:', event.code, event.reason);
            this.isConnected = false;
            this.isAuthenticated = false;
            this.stopHeartbeat();
            
            if (!event.wasClean) {
                this.scheduleReconnect();
            }
        };
        
        this.socket.onerror = (error) => {
            console.error('❌ WebSocket error:', error);
        };
    }
    
    /**
     * 处理WebSocket消息
     */
    handleMessage(message) {
        const { type, data } = message;
        
        switch (type) {
            case 'connection_established':
                console.log('🎉 WebSocket connection established');
                break;
                
            case 'auth_success':
                console.log('🔐 Authentication successful');
                this.isAuthenticated = true;
                this.onAuthenticationSuccess(data);
                break;
                
            case 'subscription_success':
                console.log('📡 Subscription successful:', data.channels);
                break;
                
            case 'system_metrics':
                this.handleSystemMetrics(data);
                break;
                
            case 'detailed_stats':
                this.handleDetailedStats(data);
                break;
                
            case 'data_response':
                this.handleDataResponse(message);
                break;
                
            case 'action_response':
                this.handleActionResponse(message);
                break;
                
            case 'admin_update':
                this.handleAdminUpdate(message);
                break;
                
            case 'error':
                console.error('❌ Server error:', message.message);
                this.onError(message);
                break;
                
            default:
                console.warn('⚠️ Unknown message type:', type);
        }
        
        // 调用自定义消息处理器
        if (this.messageHandlers.has(type)) {
            const handlers = this.messageHandlers.get(type);
            handlers.forEach(handler => handler(data || message));
        }
    }
    
    /**
     * 认证
     */
    authenticate() {
        const token = this.getAdminToken();
        if (token) {
            this.send({
                type: 'auth',
                token: token
            });
        } else {
            console.warn('⚠️ No admin token found for authentication');
        }
    }
    
    /**
     * 订阅频道
     */
    subscribe(channels) {
        if (!this.isAuthenticated) {
            console.warn('⚠️ Cannot subscribe: not authenticated');
            return;
        }
        
        const channelArray = Array.isArray(channels) ? channels : [channels];
        channelArray.forEach(channel => this.subscribedChannels.add(channel));
        
        this.send({
            type: 'subscribe',
            channels: Array.from(this.subscribedChannels)
        });
    }
    
    /**
     * 请求数据
     */
    requestData(requestType) {
        if (!this.isAuthenticated) {
            console.warn('⚠️ Cannot request data: not authenticated');
            return;
        }
        
        this.send({
            type: 'request_data',
            request: requestType
        });
    }
    
    /**
     * 执行管理员操作
     */
    adminAction(action, params = {}) {
        if (!this.isAuthenticated) {
            console.warn('⚠️ Cannot execute admin action: not authenticated');
            return;
        }
        
        this.send({
            type: 'admin_action',
            action: action,
            params: params
        });
    }
    
    /**
     * 发送消息
     */
    send(data) {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify(data));
        } else {
            console.warn('⚠️ WebSocket not connected');
        }
    }
    
    /**
     * 添加消息处理器
     */
    onMessage(type, handler) {
        if (!this.messageHandlers.has(type)) {
            this.messageHandlers.set(type, []);
        }
        this.messageHandlers.get(type).push(handler);
    }
    
    /**
     * 移除消息处理器
     */
    offMessage(type, handler) {
        if (this.messageHandlers.has(type)) {
            const handlers = this.messageHandlers.get(type);
            const index = handlers.indexOf(handler);
            if (index > -1) {
                handlers.splice(index, 1);
            }
        }
    }
    
    /**
     * 处理系统指标
     */
    handleSystemMetrics(data) {
        // 更新仪表板实时数据
        if (window.adminSystem && window.adminSystem.dashboard) {
            window.adminSystem.dashboard.updateMetrics(data);
        }
        
        // 触发自定义事件
        this.dispatchEvent('system-metrics-updated', data);
    }
    
    /**
     * 处理详细统计
     */
    handleDetailedStats(data) {
        // 更新统计数据
        if (window.adminSystem) {
            Object.keys(data).forEach(module => {
                if (window.adminSystem[module] && typeof window.adminSystem[module].updateStats === 'function') {
                    window.adminSystem[module].updateStats(data[module]);
                }
            });
        }
        
        this.dispatchEvent('detailed-stats-updated', data);
    }
    
    /**
     * 处理数据响应
     */
    handleDataResponse(message) {
        const { request, data } = message;
        this.dispatchEvent(`data-response-${request}`, data);
    }
    
    /**
     * 处理操作响应
     */
    handleActionResponse(message) {
        const { action, result } = message;
        this.dispatchEvent(`action-response-${action}`, result);
        
        // 显示操作结果
        if (result.success) {
            this.showNotification('操作成功', 'success');
        } else {
            this.showNotification(`操作失败: ${result.error}`, 'error');
        }
    }
    
    /**
     * 处理管理员更新
     */
    handleAdminUpdate(message) {
        const { action } = message;
        this.dispatchEvent('admin-update', message);
        
        // 刷新相关数据
        this.requestData('dashboard');
    }
    
    /**
     * 认证成功处理
     */
    onAuthenticationSuccess(data) {
        // 自动订阅默认频道
        this.subscribe(['system', 'users', 'security', 'performance']);
        
        // 请求初始数据
        this.requestData('dashboard');
        
        this.dispatchEvent('authenticated', data);
    }
    
    /**
     * 错误处理
     */
    onError(message) {
        this.dispatchEvent('error', message);
        this.showNotification(message.message, 'error');
    }
    
    /**
     * 设置事件处理器
     */
    setupEventHandlers() {
        // 页面隐藏时暂停心跳
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopHeartbeat();
            } else if (this.isConnected) {
                this.startHeartbeat();
            }
        });
        
        // 页面卸载时关闭连接
        window.addEventListener('beforeunload', () => {
            this.disconnect();
        });
    }
    
    /**
     * 开始心跳
     */
    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            if (this.isConnected) {
                this.send({ type: 'ping' });
            }
        }, 30000); // 30秒心跳
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
     * 计划重连
     */
    scheduleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
            
            console.log(`🔄 Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            setTimeout(() => {
                this.connect();
            }, delay);
        } else {
            console.error('❌ Max reconnection attempts reached');
            this.showNotification('WebSocket连接失败，请刷新页面重试', 'error');
        }
    }
    
    /**
     * 断开连接
     */
    disconnect() {
        if (this.socket) {
            this.socket.close(1000, 'Manual disconnect');
        }
        this.stopHeartbeat();
    }
    
    /**
     * 获取管理员Token
     */
    getAdminToken() {
        // 尝试从多个位置获取token
        return localStorage.getItem('admin_token') ||
               sessionStorage.getItem('admin_token') ||
               this.getCookie('admin_token');
    }
    
    /**
     * 获取Cookie
     */
    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    
    /**
     * 派发自定义事件
     */
    dispatchEvent(eventName, data) {
        const event = new CustomEvent(`admin-ws-${eventName}`, {
            detail: data
        });
        document.dispatchEvent(event);
    }
    
    /**
     * 显示通知
     */
    showNotification(message, type = 'info') {
        // 使用已存在的通知系统
        if (window.adminSystem && window.adminSystem.showNotification) {
            window.adminSystem.showNotification(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }
    
    /**
     * 获取连接状态
     */
    getStatus() {
        return {
            connected: this.isConnected,
            authenticated: this.isAuthenticated,
            reconnectAttempts: this.reconnectAttempts,
            subscribedChannels: Array.from(this.subscribedChannels)
        };
    }
}

// 实时数据管理器
class RealtimeDataManager {
    constructor(wsClient) {
        this.wsClient = wsClient;
        this.dataCache = new Map();
        this.subscribers = new Map();
        
        this.setupEventListeners();
    }
    
    /**
     * 设置事件监听器
     */
    setupEventListeners() {
        // 监听WebSocket事件
        document.addEventListener('admin-ws-system-metrics-updated', (e) => {
            this.updateData('system-metrics', e.detail);
        });
        
        document.addEventListener('admin-ws-detailed-stats-updated', (e) => {
            this.updateData('detailed-stats', e.detail);
        });
        
        document.addEventListener('admin-ws-data-response-dashboard', (e) => {
            this.updateData('dashboard', e.detail);
        });
    }
    
    /**
     * 订阅数据更新
     */
    subscribe(dataType, callback) {
        if (!this.subscribers.has(dataType)) {
            this.subscribers.set(dataType, new Set());
        }
        this.subscribers.get(dataType).add(callback);
        
        // 如果有缓存数据，立即调用回调
        if (this.dataCache.has(dataType)) {
            callback(this.dataCache.get(dataType));
        }
    }
    
    /**
     * 取消订阅
     */
    unsubscribe(dataType, callback) {
        if (this.subscribers.has(dataType)) {
            this.subscribers.get(dataType).delete(callback);
        }
    }
    
    /**
     * 更新数据
     */
    updateData(dataType, data) {
        this.dataCache.set(dataType, data);
        
        // 通知订阅者
        if (this.subscribers.has(dataType)) {
            this.subscribers.get(dataType).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('Error in data subscriber callback:', error);
                }
            });
        }
    }
    
    /**
     * 获取数据
     */
    getData(dataType) {
        return this.dataCache.get(dataType);
    }
    
    /**
     * 请求数据
     */
    requestData(dataType) {
        this.wsClient.requestData(dataType);
    }
    
    /**
     * 清除缓存
     */
    clearCache(dataType = null) {
        if (dataType) {
            this.dataCache.delete(dataType);
        } else {
            this.dataCache.clear();
        }
    }
}

// 全局初始化
let adminWebSocketClient = null;
let realtimeDataManager = null;

// DOM加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    // 检查是否在管理员页面
    if (window.location.pathname.includes('/admin')) {
        adminWebSocketClient = new AdminWebSocketClient();
        realtimeDataManager = new RealtimeDataManager(adminWebSocketClient);
        
        // 添加到全局对象
        if (window.adminSystem) {
            window.adminSystem.wsClient = adminWebSocketClient;
            window.adminSystem.realtimeData = realtimeDataManager;
        }
        
        console.log('🚀 Admin WebSocket Client initialized');
    }
});

// 导出到全局
window.AdminWebSocketClient = AdminWebSocketClient;
window.RealtimeDataManager = RealtimeDataManager;

// 提供便捷的全局函数
window.getAdminWebSocket = () => adminWebSocketClient;
window.getRealtimeDataManager = () => realtimeDataManager;
