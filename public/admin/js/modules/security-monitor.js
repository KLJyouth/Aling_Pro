/**
 * AlingAI Pro 安全监控模块
 * 提供实时安全监控、威胁分析和安全报告功能
 * @version 1.0.0
 * @author AlingAi Team
 */

class SecurityMonitor {
    constructor(options = {}) {
        this.options = Object.assign({
            apiEndpoint: '/admin/index.php',
            refreshInterval: 60000, // 默认60秒刷新一次
            alertThreshold: 'medium', // 默认警报阈值
            enableRealTimeAlerts: true,
            enableLogging: true
        }, options);
        
        this.securityData = {
            threats: [],
            vulnerabilities: [],
            securityScore: 100,
            lastScan: null,
            activeAlerts: []
        };
        
        this.eventListeners = {
            'threat-detected': [],
            'vulnerability-found': [],
            'security-score-changed': [],
            'data-updated': []
        };
        
        this.refreshTimer = null;
        this.isInitialized = false;
    }
    
    /**
     * 初始化安全监控
     */
    async initialize() {
        if (this.isInitialized) return;
        
        try {
            // 加载初始数据
            await this.refreshSecurityData();
            
            // 设置定时刷新
            if (this.options.refreshInterval > 0) {
                this.refreshTimer = setInterval(() => {
                    this.refreshSecurityData();
                }, this.options.refreshInterval);
            }
            
            // 设置实时警报
            if (this.options.enableRealTimeAlerts) {
                this.setupRealTimeAlerts();
            }
            
            this.isInitialized = true;
            this.log('安全监控初始化完成');
        } catch (error) {
            this.log('安全监控初始化失败: ' + error.message, 'error');
            throw error;
        }
    }
    
    /**
     * 刷新安全数据
     */
    async refreshSecurityData() {
        try {
            const response = await this.fetchData('security_monitoring');
            
            // 检查新威胁
            const newThreats = this.detectNewThreats(response.threats || []);
            if (newThreats.length > 0) {
                this.triggerEvent('threat-detected', newThreats);
            }
            
            // 检查新漏洞
            const newVulnerabilities = this.detectNewVulnerabilities(response.vulnerabilities || []);
            if (newVulnerabilities.length > 0) {
                this.triggerEvent('vulnerability-found', newVulnerabilities);
            }
            
            // 检查安全评分变化
            if (response.securityScore !== this.securityData.securityScore) {
                this.triggerEvent('security-score-changed', {
                    oldScore: this.securityData.securityScore,
                    newScore: response.securityScore
                });
            }
            
            // 更新数据
            this.securityData = {
                ...response,
                lastScan: new Date()
            };
            
            this.triggerEvent('data-updated', this.securityData);
            this.log('安全数据已更新');
            
            return this.securityData;
        } catch (error) {
            this.log('刷新安全数据失败: ' + error.message, 'error');
            throw error;
        }
    }
    
    /**
     * 检测新威胁
     * @param {Array} currentThreats - 当前威胁列表
     * @returns {Array} 新检测到的威胁
     */
    detectNewThreats(currentThreats) {
        if (!this.securityData.threats || this.securityData.threats.length === 0) {
            return currentThreats;
        }
        
        return currentThreats.filter(threat => {
            return !this.securityData.threats.some(existingThreat => 
                existingThreat.id === threat.id
            );
        });
    }
    
    /**
     * 检测新漏洞
     * @param {Array} currentVulnerabilities - 当前漏洞列表
     * @returns {Array} 新检测到的漏洞
     */
    detectNewVulnerabilities(currentVulnerabilities) {
        if (!this.securityData.vulnerabilities || this.securityData.vulnerabilities.length === 0) {
            return currentVulnerabilities;
        }
        
        return currentVulnerabilities.filter(vulnerability => {
            return !this.securityData.vulnerabilities.some(existingVulnerability => 
                existingVulnerability.id === vulnerability.id
            );
        });
    }
    
    /**
     * 设置实时警报
     */
    setupRealTimeAlerts() {
        // 这里可以设置WebSocket连接或其他实时通信方式
        this.log('实时安全警报已启用');
    }
    
    /**
     * 运行安全扫描
     * @param {string} scanType - 扫描类型，可选值：'quick', 'full', 'targeted'
     * @param {Object} options - 扫描选项
     */
    async runSecurityScan(scanType = 'quick', options = {}) {
        try {
            this.log(`开始${scanType}安全扫描`);
            
            const response = await this.fetchData('run_security_scan', {
                scan_type: scanType,
                ...options
            });
            
            this.log(`${scanType}安全扫描完成`);
            
            // 更新安全数据
            await this.refreshSecurityData();
            
            return response;
        } catch (error) {
            this.log(`安全扫描失败: ${error.message}`, 'error');
            throw error;
        }
    }
    
    /**
     * 获取安全报告
     * @param {string} reportType - 报告类型，可选值：'summary', 'detailed', 'compliance'
     * @param {Object} options - 报告选项
     */
    async getSecurityReport(reportType = 'summary', options = {}) {
        try {
            const response = await this.fetchData('security_report', {
                report_type: reportType,
                ...options
            });
            
            this.log(`已生成${reportType}安全报告`);
            return response;
        } catch (error) {
            this.log(`生成安全报告失败: ${error.message}`, 'error');
            throw error;
        }
    }
    
    /**
     * 处理安全事件
     * @param {string} eventId - 事件ID
     * @param {string} action - 处理动作，可选值：'ignore', 'resolve', 'escalate'
     * @param {Object} details - 处理详情
     */
    async handleSecurityEvent(eventId, action, details = {}) {
        try {
            const response = await this.fetchData('handle_security_event', {
                event_id: eventId,
                action,
                details
            });
            
            this.log(`已处理安全事件 ${eventId}`);
            
            // 更新安全数据
            await this.refreshSecurityData();
            
            return response;
        } catch (error) {
            this.log(`处理安全事件失败: ${error.message}`, 'error');
            throw error;
        }
    }
    
    /**
     * 添加事件监听器
     * @param {string} event - 事件名称
     * @param {Function} callback - 回调函数
     */
    on(event, callback) {
        if (!this.eventListeners[event]) {
            this.eventListeners[event] = [];
        }
        
        this.eventListeners[event].push(callback);
        return this;
    }
    
    /**
     * 移除事件监听器
     * @param {string} event - 事件名称
     * @param {Function} callback - 回调函数
     */
    off(event, callback) {
        if (!this.eventListeners[event]) return this;
        
        this.eventListeners[event] = this.eventListeners[event].filter(
            listener => listener !== callback
        );
        
        return this;
    }
    
    /**
     * 触发事件
     * @param {string} event - 事件名称
     * @param {*} data - 事件数据
     */
    triggerEvent(event, data) {
        if (!this.eventListeners[event]) return;
        
        this.eventListeners[event].forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                this.log(`事件处理器错误: ${error.message}`, 'error');
            }
        });
    }
    
    /**
     * 获取当前安全数据
     */
    getSecurityData() {
        return { ...this.securityData };
    }
    
    /**
     * 获取安全评分
     */
    getSecurityScore() {
        return this.securityData.securityScore;
    }
    
    /**
     * 获取活跃威胁
     */
    getActiveThreats() {
        return [...(this.securityData.threats || [])];
    }
    
    /**
     * 获取已知漏洞
     */
    getVulnerabilities() {
        return [...(this.securityData.vulnerabilities || [])];
    }
    
    /**
     * 获取活跃警报
     */
    getActiveAlerts() {
        return [...(this.securityData.activeAlerts || [])];
    }
    
    /**
     * 获取上次扫描时间
     */
    getLastScanTime() {
        return this.securityData.lastScan;
    }
    
    /**
     * 销毁实例
     */
    destroy() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        this.eventListeners = {
            'threat-detected': [],
            'vulnerability-found': [],
            'security-score-changed': [],
            'data-updated': []
        };
        
        this.isInitialized = false;
        this.log('安全监控已销毁');
    }
    
    /**
     * 发送API请求
     * @param {string} action - API动作
     * @param {Object} params - 请求参数
     */
    async fetchData(action, params = {}) {
        const url = new URL(this.options.apiEndpoint, window.location.origin);
        url.searchParams.append('action', action);
        
        Object.entries(params).forEach(([key, value]) => {
            url.searchParams.append(key, value);
        });
        
        const response = await fetch(url.toString());
        
        if (!response.ok) {
            throw new Error(`API请求失败: ${response.status} ${response.statusText}`);
        }
        
        return await response.json();
    }
    
    /**
     * 记录日志
     * @param {string} message - 日志消息
     * @param {string} level - 日志级别
     */
    log(message, level = 'info') {
        if (!this.options.enableLogging) return;
        
        const timestamp = new Date().toISOString();
        
        switch (level) {
            case 'error':
                console.error(`[SecurityMonitor] ${timestamp} - ${message}`);
                break;
            case 'warn':
                console.warn(`[SecurityMonitor] ${timestamp} - ${message}`);
                break;
            case 'info':
            default:
                console.info(`[SecurityMonitor] ${timestamp} - ${message}`);
                break;
        }
    }
}

// 如果在浏览器环境中，将SecurityMonitor添加到全局对象
if (typeof window !== 'undefined') {
    window.SecurityMonitor = SecurityMonitor;
}

// 如果在Node.js环境中，导出模块
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SecurityMonitor;
} 