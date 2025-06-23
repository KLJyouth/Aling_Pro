/**
 * AlingAi Pro 5.0 - Admin系统前端集成
 * 管理系统API调用和数据管理
 */

class AdminSystemManager {
    constructor() {
        this.baseURL = '/admin/api';
        this.authToken = this.getAuthToken();
        this.modules = {
            users: new UserManagementModule(this),
            thirdParty: new ThirdPartyModule(this),
            monitoring: new MonitoringModule(this),
            riskControl: new RiskControlModule(this),
            email: new EmailModule(this),
            chatMonitoring: new ChatMonitoringModule(this),
            documentation: new DocumentationModule(this),
            dashboard: new DashboardModule(this),
            tokens: new TokenModule(this),
            system: new SystemModule(this)
        };
        
        this.init();
    }
    
    init() {
        console.log('🚀 AlingAi Pro Admin System Manager 初始化');
        this.setupEventListeners();
        this.loadDashboard();
    }
    
    setupEventListeners() {
        // 页面路由
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-admin-action]')) {
                e.preventDefault();
                this.handleAction(e.target.dataset.adminAction, e.target);
            }
        });
        
        // 实时数据更新
        setInterval(() => {
            this.updateRealTimeData();
        }, 30000); // 30秒更新一次
    }
    
    async apiCall(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.authToken}`,
                ...options.headers
            },
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || '请求失败');
            }
            
            return data;
        } catch (error) {
            console.error('API调用失败:', error);
            this.showNotification('error', error.message);
            throw error;
        }
    }
    
    getAuthToken() {
        return localStorage.getItem('admin_token') || '';
    }
    
    async loadDashboard() {
        try {
            const data = await this.apiCall('/dashboard');
            this.renderDashboard(data.data);
        } catch (error) {
            console.error('仪表板加载失败:', error);
        }
    }
    
    renderDashboard(data) {
        const container = document.getElementById('admin-dashboard');
        if (!container) return;
        
        container.innerHTML = `
            <div class="admin-dashboard">
                <div class="dashboard-header">
                    <h1>AlingAi Pro 管理控制台</h1>
                    <div class="system-status ${data.overview.system_health}">
                        系统状态: ${this.getStatusText(data.overview.system_health)}
                    </div>
                </div>
                
                <div class="dashboard-overview">
                    <div class="overview-card">
                        <h3>用户统计</h3>
                        <div class="stat-number">${data.overview.total_users}</div>
                        <div class="stat-label">总用户数</div>
                        <div class="stat-detail">活跃: ${data.overview.active_users}</div>
                    </div>
                    
                    <div class="overview-card">
                        <h3>API调用</h3>
                        <div class="stat-number">${data.overview.api_calls_today}</div>
                        <div class="stat-label">今日调用</div>
                        <div class="stat-detail">总接口: ${data.overview.total_apis}</div>
                    </div>
                    
                    <div class="overview-card">
                        <h3>模块状态</h3>
                        <div class="modules-grid">
                            ${Object.entries(data.modules_status).map(([name, status]) => `
                                <div class="module-status ${status.status}">
                                    <span class="module-name">${this.getModuleName(name)}</span>
                                    <span class="module-endpoints">${status.endpoints}个端点</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-modules">
                    <div class="module-grid">
                        ${this.renderModuleCards()}
                    </div>
                </div>
                
                <div class="dashboard-activities">
                    <h2>最近活动</h2>
                    <div class="activities-list">
                        ${data.recent_activities.map(activity => `
                            <div class="activity-item">
                                <div class="activity-icon ${activity.type}"></div>
                                <div class="activity-content">
                                    <div class="activity-description">${activity.description}</div>
                                    <div class="activity-time">${this.formatTime(activity.timestamp)}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }
    
    renderModuleCards() {
        const moduleInfo = {
            users: { name: '用户管理', icon: '👥', description: '用户增删改查、权限管理' },
            thirdParty: { name: '第三方服务', icon: '🔗', description: '支付、登录、短信服务管理' },
            monitoring: { name: '系统监控', icon: '📊', description: '性能监控、健康检查' },
            riskControl: { name: '风险控制', icon: '🛡️', description: '安全风控、异常检测' },
            email: { name: '邮件系统', icon: '📧', description: '邮件模板、发送管理' },
            chatMonitoring: { name: '聊天监控', icon: '💬', description: '聊天内容监管、敏感词' },
            documentation: { name: 'API文档', icon: '📚', description: 'API文档生成、管理' },
            tokens: { name: 'Token管理', icon: '🎟️', description: 'JWT、API Key管理' }
        };
        
        return Object.entries(moduleInfo).map(([key, info]) => `
            <div class="module-card" data-admin-action="navigate:${key}">
                <div class="module-icon">${info.icon}</div>
                <div class="module-info">
                    <h3>${info.name}</h3>
                    <p>${info.description}</p>
                </div>
                <div class="module-actions">
                    <button class="btn-primary" data-admin-action="manage:${key}">管理</button>
                    <button class="btn-secondary" data-admin-action="stats:${key}">统计</button>
                </div>
            </div>
        `).join('');
    }
    
    async handleAction(action, element) {
        const [type, target] = action.split(':');
        
        switch (type) {
            case 'navigate':
                this.navigateToModule(target);
                break;
            case 'manage':
                this.openModuleManager(target);
                break;
            case 'stats':
                this.showModuleStats(target);
                break;
            case 'refresh':
                this.refreshData(target);
                break;
            default:
                console.warn('未知操作:', action);
        }
    }
    
    navigateToModule(moduleName) {
        const module = this.modules[moduleName];
        if (module) {
            module.activate();
        }
    }
    
    async openModuleManager(moduleName) {
        const module = this.modules[moduleName];
        if (module) {
            await module.showManager();
        }
    }
    
    async showModuleStats(moduleName) {
        const module = this.modules[moduleName];
        if (module) {
            await module.showStatistics();
        }
    }
    
    async updateRealTimeData() {
        try {
            // 更新系统健康状态
            const healthData = await this.apiCall('/health');
            this.updateSystemHealth(healthData.data);
            
            // 更新统计数据
            const statsData = await this.apiCall('/dashboard/stats');
            this.updateDashboardStats(statsData.data);
            
        } catch (error) {
            console.error('实时数据更新失败:', error);
        }
    }
    
    updateSystemHealth(healthData) {
        const statusElement = document.querySelector('.system-status');
        if (statusElement) {
            statusElement.className = `system-status ${healthData.overall_status}`;
            statusElement.textContent = `系统状态: ${this.getStatusText(healthData.overall_status)}`;
        }
    }
    
    updateDashboardStats(statsData) {
        // 更新用户统计
        const userStatElement = document.querySelector('.overview-card .stat-number');
        if (userStatElement) {
            userStatElement.textContent = statsData.user_stats.total;
        }
        
        // 更新API调用统计
        const apiStatElements = document.querySelectorAll('.overview-card .stat-number');
        if (apiStatElements[1]) {
            apiStatElements[1].textContent = statsData.api_stats.total_calls_today;
        }
    }
    
    getStatusText(status) {
        const statusMap = {
            'healthy': '健康',
            'warning': '警告',
            'critical': '严重',
            'error': '错误'
        };
        return statusMap[status] || '未知';
    }
    
    getModuleName(key) {
        const nameMap = {
            'users': '用户管理',
            'third_party': '第三方服务',
            'monitoring': '系统监控',
            'risk_control': '风险控制',
            'email': '邮件系统',
            'chat_monitoring': '聊天监控',
            'documentation': 'API文档'
        };
        return nameMap[key] || key;
    }
    
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return '刚刚';
        if (diff < 3600000) return `${Math.floor(diff / 60000)}分钟前`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)}小时前`;
        return date.toLocaleDateString();
    }
    
    showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// 用户管理模块
class UserManagementModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/users';
    }
    
    async activate() {
        console.log('激活用户管理模块');
        await this.loadUsers();
    }
    
    async loadUsers() {
        try {
            const data = await this.manager.apiCall(this.endpoint);
            this.renderUserList(data.data);
        } catch (error) {
            console.error('用户列表加载失败:', error);
        }
    }
    
    renderUserList(data) {
        const container = document.getElementById('module-content');
        if (!container) return;
        
        container.innerHTML = `
            <div class="user-management">
                <div class="module-header">
                    <h2>👥 用户管理</h2>
                    <div class="module-actions">
                        <button class="btn-primary" data-admin-action="user:create">添加用户</button>
                        <button class="btn-secondary" data-admin-action="user:export">导出数据</button>
                    </div>
                </div>
                
                <div class="user-stats">
                    <div class="stat-card">
                        <h3>总用户数</h3>
                        <div class="stat-value">${data.statistics.total_users}</div>
                    </div>
                    <div class="stat-card">
                        <h3>活跃用户</h3>
                        <div class="stat-value">${data.statistics.active_users}</div>
                    </div>
                    <div class="stat-card">
                        <h3>今日新增</h3>
                        <div class="stat-value">${data.statistics.new_users_today}</div>
                    </div>
                </div>
                
                <div class="user-filters">
                    <input type="text" placeholder="搜索用户..." class="search-input" data-filter="search">
                    <select class="filter-select" data-filter="status">
                        <option value="">所有状态</option>
                        <option value="active">活跃</option>
                        <option value="inactive">非活跃</option>
                        <option value="suspended">已暂停</option>
                    </select>
                    <select class="filter-select" data-filter="role">
                        <option value="">所有角色</option>
                        <option value="user">普通用户</option>
                        <option value="admin">管理员</option>
                        <option value="enterprise">企业用户</option>
                    </select>
                </div>
                
                <div class="user-table">
                    <table>
                        <thead>
                            <tr>
                                <th>用户ID</th>
                                <th>用户名</th>
                                <th>邮箱</th>
                                <th>角色</th>
                                <th>状态</th>
                                <th>余额</th>
                                <th>注册时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.users.map(user => `
                                <tr data-user-id="${user.id}">
                                    <td>${user.id}</td>
                                    <td>${user.username}</td>
                                    <td>${user.email}</td>
                                    <td><span class="role-badge ${user.role}">${user.role}</span></td>
                                    <td><span class="status-badge ${user.status}">${user.status}</span></td>
                                    <td>¥${user.balance}</td>
                                    <td>${user.created_at}</td>
                                    <td>
                                        <button class="btn-sm" data-admin-action="user:edit:${user.id}">编辑</button>
                                        <button class="btn-sm" data-admin-action="user:balance:${user.id}">余额</button>
                                        <button class="btn-sm" data-admin-action="user:chat:${user.id}">聊天</button>
                                        <button class="btn-sm danger" data-admin-action="user:delete:${user.id}">删除</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                
                <div class="pagination">
                    ${this.renderPagination(data.pagination)}
                </div>
            </div>
        `;
    }
    
    renderPagination(pagination) {
        const pages = [];
        const current = pagination.current_page;
        const total = pagination.total_pages;
        
        for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
            pages.push(`
                <button class="page-btn ${i === current ? 'active' : ''}" 
                        data-admin-action="user:page:${i}">
                    ${i}
                </button>
            `);
        }
        
        return `
            <button class="page-btn" data-admin-action="user:page:${Math.max(1, current - 1)}" 
                    ${current === 1 ? 'disabled' : ''}>上一页</button>
            ${pages.join('')}
            <button class="page-btn" data-admin-action="user:page:${Math.min(total, current + 1)}" 
                    ${current === total ? 'disabled' : ''}>下一页</button>
        `;
    }
    
    async showManager() {
        await this.activate();
    }
    
    async showStatistics() {
        try {
            const data = await this.manager.apiCall(`${this.endpoint}/stats`);
            this.showStatsModal(data.data);
        } catch (error) {
            console.error('用户统计获取失败:', error);
        }
    }
    
    showStatsModal(stats) {
        // 实现统计模态框
        console.log('用户统计:', stats);
    }
}

// 第三方服务模块
class ThirdPartyModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/third-party';
    }
    
    async activate() {
        console.log('激活第三方服务模块');
        await this.loadServices();
    }
    
    async loadServices() {
        try {
            const data = await this.manager.apiCall(this.endpoint);
            this.renderServiceList(data.data);
        } catch (error) {
            console.error('第三方服务列表加载失败:', error);
        }
    }
    
    renderServiceList(data) {
        const container = document.getElementById('module-content');
        if (!container) return;
        
        container.innerHTML = `
            <div class="third-party-management">
                <div class="module-header">
                    <h2>🔗 第三方服务管理</h2>
                    <div class="module-actions">
                        <button class="btn-primary" data-admin-action="service:add">添加服务</button>
                        <button class="btn-secondary" data-admin-action="service:test-all">测试所有</button>
                    </div>
                </div>
                
                <div class="service-categories">
                    <div class="category-tabs">
                        <button class="tab-btn active" data-category="all">全部</button>
                        <button class="tab-btn" data-category="payment">支付服务</button>
                        <button class="tab-btn" data-category="oauth">登录服务</button>
                        <button class="tab-btn" data-category="email">邮件服务</button>
                        <button class="tab-btn" data-category="sms">短信服务</button>
                    </div>
                </div>
                
                <div class="services-grid">
                    ${data.services.map(service => `
                        <div class="service-card ${service.status}" data-service-id="${service.id}">
                            <div class="service-header">
                                <div class="service-icon">${this.getServiceIcon(service.type)}</div>
                                <div class="service-info">
                                    <h3>${service.name}</h3>
                                    <span class="service-type">${service.type}</span>
                                </div>
                                <div class="service-status ${service.status}">
                                    ${this.getStatusText(service.status)}
                                </div>
                            </div>
                            
                            <div class="service-details">
                                <div class="detail-item">
                                    <label>最后测试:</label>
                                    <span>${service.last_test || '从未测试'}</span>
                                </div>
                                <div class="detail-item">
                                    <label>响应时间:</label>
                                    <span>${service.response_time || 'N/A'}</span>
                                </div>
                            </div>
                            
                            <div class="service-actions">
                                <button class="btn-sm" data-admin-action="service:config:${service.id}">配置</button>
                                <button class="btn-sm" data-admin-action="service:test:${service.id}">测试</button>
                                <button class="btn-sm" data-admin-action="service:logs:${service.id}">日志</button>
                                <button class="btn-sm danger" data-admin-action="service:delete:${service.id}">删除</button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    getServiceIcon(type) {
        const icons = {
            'payment': '💳',
            'oauth': '🔐',
            'email': '📧',
            'sms': '📱'
        };
        return icons[type] || '🔗';
    }
    
    getStatusText(status) {
        const statusMap = {
            'active': '正常',
            'inactive': '禁用',
            'error': '错误'
        };
        return statusMap[status] || status;
    }
    
    async showManager() {
        await this.activate();
    }
    
    async showStatistics() {
        try {
            const data = await this.manager.apiCall(`${this.endpoint}/stats`);
            this.showStatsModal(data.data);
        } catch (error) {
            console.error('第三方服务统计获取失败:', error);
        }
    }
}

// 监控模块（简化版）
class MonitoringModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/monitoring';
    }
    
    async activate() {
        console.log('激活监控模块');
        await this.loadMonitoringData();
    }
    
    async loadMonitoringData() {
        try {
            const data = await this.manager.apiCall(this.endpoint);
            this.renderMonitoringDashboard(data.data);
        } catch (error) {
            console.error('监控数据加载失败:', error);
        }
    }
    
    async showManager() {
        await this.activate();
    }
    
    async showStatistics() {
        // 实现监控统计
        console.log('显示监控统计');
    }
}

// 其他模块类似实现...
class RiskControlModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/risk-control';
    }
    
    async activate() { /* 实现 */ }
    async showManager() { /* 实现 */ }
    async showStatistics() { /* 实现 */ }
}

class EmailModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/email';
    }
    
    async activate() { /* 实现 */ }
    async showManager() { /* 实现 */ }
    async showStatistics() { /* 实现 */ }
}

class ChatMonitoringModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/chat-monitoring';
    }
    
    async activate() { /* 实现 */ }
    async showManager() { /* 实现 */ }
    async showStatistics() { /* 实现 */ }
}

class DocumentationModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/documentation';
    }
    
    async activate() { /* 实现 */ }
    async showManager() { /* 实现 */ }
    async showStatistics() { /* 实现 */ }
}

class DashboardModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/dashboard';
    }
    
    async activate() { /* 实现 */ }
    async showManager() { /* 实现 */ }
    async showStatistics() { /* 实现 */ }
}

class TokenModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/tokens';
    }
    
    async activate() { /* 实现 */ }
    async showManager() { /* 实现 */ }
    async showStatistics() { /* 实现 */ }
}

class SystemModule {
    constructor(manager) {
        this.manager = manager;
        this.endpoint = '/system';
    }
    
    async activate() { /* 实现 */ }
    async showManager() { /* 实现 */ }
    async showStatistics() { /* 实现 */ }
}

// 初始化管理系统
window.addEventListener('DOMContentLoaded', () => {
    window.adminSystem = new AdminSystemManager();
    console.log('✅ AlingAi Pro Admin System 初始化完成');
});

// 导出模块
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminSystemManager;
}
