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

// 全局实例
let adminRealtimeClient = null;

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('/admin/')) {
        adminRealtimeClient = new AdminRealtimeClient();
        
        // 绑定手动刷新按钮
        const refreshBtn = document.getElementById('refresh-data-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                adminRealtimeClient.refreshData();
            });
        }
    }
});
