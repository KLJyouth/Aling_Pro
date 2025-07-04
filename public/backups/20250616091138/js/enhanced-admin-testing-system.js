/**
 * 增强综合测试系统 - 管理后端整合版
 * 整合所有测试功能和管理系统诊断
 * 创建时间: 2025年5月30日
 */

class EnhancedAdminTestingSystem {
    constructor() {
        this.testResults = {};
        this.diagnosticsData = {};
        this.isRunning = false;
        this.systemHealth = 'unknown';
        
        this.testCategories = {
            backend: '后端服务检测',
            admin: '管理系统检测',
            security: '安全系统检测',
            performance: '性能监控检测',
            database: '数据库系统检测',
            integrations: '系统集成检测',
            monitoring: '监控系统检测',
            backup: '备份系统检测'
        };

        this.init();
    }

    async init() {
        this.setupAdminUI();
        this.registerAdminTests();
        this.setupEventListeners();
        await this.loadAdminModules();
        this.startSystemMonitoring();
        this.log('✅ 增强管理测试系统初始化完成', 'success');
    }

    setupAdminUI() {
        // 创建管理员专用测试界面
        const adminTestContainer = document.getElementById('adminTestSystem');
        if (adminTestContainer) {
            adminTestContainer.innerHTML = `
                <div class="admin-test-dashboard">
                    <div class="test-control-panel">
                        <h3><i class="bi bi-gear-fill"></i> 系统管理测试控制台</h3>
                        <div class="control-buttons">
                            <button id="runComprehensiveTests" class="btn btn-primary">
                                <i class="bi bi-play-fill"></i> 运行综合测试
                            </button>
                            <button id="runDiagnostics" class="btn btn-info">
                                <i class="bi bi-search"></i> 系统诊断
                            </button>
                            <button id="runSecurityScan" class="btn btn-warning">
                                <i class="bi bi-shield-check"></i> 安全扫描
                            </button>
                            <button id="runHealthCheck" class="btn btn-success">
                                <i class="bi bi-heart-pulse"></i> 健康检查
                            </button>
                        </div>
                    </div>
                    
                    <div class="test-status-overview">
                        <div class="status-card" id="systemHealthCard">
                            <h4>系统健康状态</h4>
                            <div class="health-indicator" id="healthIndicator">
                                <span class="status-dot unknown"></span>
                                <span id="healthStatus">检查中...</span>
                            </div>
                        </div>
                        
                        <div class="status-card">
                            <h4>测试统计</h4>
                            <div class="test-stats">
                                <div class="stat-item">
                                    <span class="label">总测试数:</span>
                                    <span id="totalTestsAdmin">0</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">通过:</span>
                                    <span id="passedTestsAdmin" class="text-success">0</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">失败:</span>
                                    <span id="failedTestsAdmin" class="text-danger">0</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">警告:</span>
                                    <span id="warningTestsAdmin" class="text-warning">0</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="status-card">
                            <h4>系统监控</h4>
                            <div class="monitoring-data" id="monitoringData">
                                <div class="metric">
                                    <span>CPU:</span>
                                    <span id="cpuUsage">--%</span>
                                </div>
                                <div class="metric">
                                    <span>内存:</span>
                                    <span id="memoryUsage">--%</span>
                                </div>
                                <div class="metric">
                                    <span>磁盘:</span>
                                    <span id="diskUsage">--%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="test-results-container">
                        <div class="results-header">
                            <h4>测试结果详情</h4>
                            <div class="result-filters">
                                <button class="filter-btn active" data-filter="all">全部</button>
                                <button class="filter-btn" data-filter="passed">通过</button>
                                <button class="filter-btn" data-filter="failed">失败</button>
                                <button class="filter-btn" data-filter="warning">警告</button>
                            </div>
                        </div>
                        <div id="adminTestResults" class="test-results"></div>
                    </div>
                    
                    <div class="diagnostics-panel" id="diagnosticsPanel">
                        <h4>系统诊断信息</h4>
                        <div id="diagnosticsContent"></div>
                    </div>
                </div>
            `;
        }
    }

    registerAdminTests() {
        // 后端系统测试
        this.addTest('backend', '数据库连接测试', this.testDatabaseConnection);
        this.addTest('backend', 'API服务测试', this.testApiServices);
        this.addTest('backend', '缓存系统测试', this.testCacheSystem);
        this.addTest('backend', '邮件服务测试', this.testEmailService);
        
        // 管理系统测试
        this.addTest('admin', '管理员权限测试', this.testAdminPermissions);
        this.addTest('admin', '用户管理功能', this.testUserManagement);
        this.addTest('admin', '系统配置测试', this.testSystemConfiguration);
        this.addTest('admin', '日志系统测试', this.testLoggingSystem);
        
        // 安全系统测试
        this.addTest('security', '认证系统测试', this.testAuthentication);
        this.addTest('security', '授权检查测试', this.testAuthorization);
        this.addTest('security', '安全头检测', this.testSecurityHeaders);
        this.addTest('security', '文件权限检查', this.testFilePermissions);
        
        // 性能监控测试
        this.addTest('performance', '响应时间测试', this.testResponseTime);
        this.addTest('performance', '内存使用测试', this.testMemoryUsage);
        this.addTest('performance', '查询性能测试', this.testQueryPerformance);
        this.addTest('performance', '并发处理测试', this.testConcurrentHandling);
        
        // 数据库系统测试
        this.addTest('database', '连接池测试', this.testConnectionPool);
        this.addTest('database', '事务处理测试', this.testTransactionHandling);
        this.addTest('database', '数据完整性检查', this.testDataIntegrity);
        this.addTest('database', '备份系统测试', this.testBackupSystem);
        
        // 系统集成测试
        this.addTest('integrations', 'WebSocket连接测试', this.testWebSocketConnection);
        this.addTest('integrations', '第三方API集成', this.testThirdPartyAPIs);
        this.addTest('integrations', '支付系统集成', this.testPaymentIntegration);
        this.addTest('integrations', 'AI服务集成', this.testAIServiceIntegration);
        
        // 监控系统测试
        this.addTest('monitoring', '系统监控服务', this.testMonitoringService);
        this.addTest('monitoring', '警报系统测试', this.testAlertSystem);
        this.addTest('monitoring', '性能指标收集', this.testMetricsCollection);
        
        // 备份系统测试
        this.addTest('backup', '数据库备份测试', this.testDatabaseBackup);
        this.addTest('backup', '文件备份测试', this.testFileBackup);
        this.addTest('backup', '备份恢复测试', this.testBackupRestore);
    }

    addTest(category, name, testFunction) {
        if (!this.testQueue) this.testQueue = [];
        this.testQueue.push({
            category,
            name,
            function: testFunction.bind(this),
            id: `${category}_${name.replace(/\s+/g, '_')}`
        });
    }

    async loadAdminModules() {
        const adminModules = [
            '/api/unified-admin/dashboard',
            '/api/unified-admin/diagnostics',
            '/api/unified-admin/monitoring/current'
        ];

        for (const endpoint of adminModules) {
            try {
                const response = await this.apiCall('GET', endpoint);
                if (response.success) {
                    this.log(`✅ 加载管理模块: ${endpoint}`, 'success');
                } else {
                    this.log(`⚠️ 管理模块加载失败: ${endpoint}`, 'warning');
                }
            } catch (error) {
                this.log(`❌ 管理模块加载错误: ${endpoint} - ${error.message}`, 'error');
            }
        }
    }

    setupEventListeners() {
        // 管理员测试按钮事件
        this.bindEvent('runComprehensiveTests', () => this.runComprehensiveTests());
        this.bindEvent('runDiagnostics', () => this.runSystemDiagnostics());
        this.bindEvent('runSecurityScan', () => this.runSecurityScan());
        this.bindEvent('runHealthCheck', () => this.runHealthCheck());
        
        // 结果过滤器事件
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.filterResults(e.target.dataset.filter);
            });
        });
    }

    bindEvent(elementId, handler) {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('click', handler);
        }
    }

    async runComprehensiveTests() {
        this.log('🚀 开始运行综合管理系统测试...', 'info');
        this.isRunning = true;
        this.updateButton('runComprehensiveTests', '测试中...', true);

        try {
            // 调用后端综合测试API
            const response = await this.apiCall('POST', '/api/unified-admin/tests/comprehensive');
            
            if (response.success) {
                this.testResults = response.data;
                this.displayTestResults(response.data);
                this.updateTestStatistics(response.data);
                this.log('✅ 综合测试完成', 'success');
            } else {
                throw new Error(response.error || '测试失败');
            }
        } catch (error) {
            this.log(`❌ 综合测试失败: ${error.message}`, 'error');
        } finally {
            this.isRunning = false;
            this.updateButton('runComprehensiveTests', '运行综合测试', false);
        }
    }

    async runSystemDiagnostics() {
        this.log('🔍 开始系统诊断...', 'info');
        this.updateButton('runDiagnostics', '诊断中...', true);

        try {
            const response = await this.apiCall('GET', '/api/unified-admin/diagnostics');
            
            if (response.success) {
                this.diagnosticsData = response.data;
                this.displayDiagnostics(response.data);
                this.log('✅ 系统诊断完成', 'success');
            } else {
                throw new Error(response.error || '诊断失败');
            }
        } catch (error) {
            this.log(`❌ 系统诊断失败: ${error.message}`, 'error');
        } finally {
            this.updateButton('runDiagnostics', '系统诊断', false);
        }
    }

    async runSecurityScan() {
        this.log('🛡️ 开始安全扫描...', 'info');
        this.updateButton('runSecurityScan', '扫描中...', true);

        try {
            const response = await this.apiCall('POST', '/api/unified-admin/security/scan');
            
            if (response.success) {
                this.displaySecurityResults(response.data);
                this.log('✅ 安全扫描完成', 'success');
            } else {
                throw new Error(response.error || '安全扫描失败');
            }
        } catch (error) {
            this.log(`❌ 安全扫描失败: ${error.message}`, 'error');
        } finally {
            this.updateButton('runSecurityScan', '安全扫描', false);
        }
    }

    async runHealthCheck() {
        this.log('💓 开始健康检查...', 'info');
        this.updateButton('runHealthCheck', '检查中...', true);

        try {
            const response = await this.apiCall('GET', '/api/unified-admin/health');
            
            if (response.success) {
                this.updateSystemHealth(response.data);
                this.log('✅ 健康检查完成', 'success');
            } else {
                throw new Error(response.error || '健康检查失败');
            }
        } catch (error) {
            this.log(`❌ 健康检查失败: ${error.message}`, 'error');
        } finally {
            this.updateButton('runHealthCheck', '健康检查', false);
        }
    }

    async startSystemMonitoring() {
        // 启动实时系统监控
        setInterval(async () => {
            try {
                const response = await this.apiCall('GET', '/api/unified-admin/monitoring/current');
                if (response.success) {
                    this.updateMonitoringData(response.data);
                }
            } catch (error) {
                // 静默处理监控错误
            }
        }, 30000); // 每30秒更新一次

        // 立即执行一次
        this.runHealthCheck();
    }

    displayTestResults(results) {
        const container = document.getElementById('adminTestResults');
        if (!container) return;

        let html = '';
        
        if (results.tests) {
            Object.entries(results.tests).forEach(([testName, result]) => {
                const statusClass = result.status === 'passed' ? 'success' : 
                                  result.status === 'failed' ? 'danger' : 'warning';
                
                html += `
                    <div class="test-result-item ${result.status}" data-status="${result.status}">
                        <div class="test-header">
                            <div class="test-name">
                                <i class="bi bi-${this.getStatusIcon(result.status)}"></i>
                                <strong>${result.name || testName}</strong>
                            </div>
                            <span class="badge bg-${statusClass}">${result.status}</span>
                        </div>
                        <div class="test-details">
                            <p>${result.message}</p>
                            ${result.execution_time ? `<small>执行时间: ${result.execution_time}</small>` : ''}
                            ${result.details ? this.formatTestDetails(result.details) : ''}
                        </div>
                    </div>
                `;
            });
        }

        container.innerHTML = html || '<p class="text-muted">暂无测试结果</p>';
    }

    formatTestDetails(details) {
        if (typeof details === 'object') {
            let html = '<div class="test-detail-list">';
            Object.entries(details).forEach(([key, value]) => {
                html += `<div class="detail-item"><strong>${key}:</strong> ${value}</div>`;
            });
            html += '</div>';
            return html;
        }
        return `<div class="test-detail">${details}</div>`;
    }

    updateTestStatistics(results) {
        if (results.summary) {
            this.updateElement('totalTestsAdmin', results.summary.total);
            this.updateElement('passedTestsAdmin', results.summary.passed);
            this.updateElement('failedTestsAdmin', results.summary.failed);
            this.updateElement('warningTestsAdmin', results.summary.warnings || 0);
        }
    }

    displayDiagnostics(data) {
        const container = document.getElementById('diagnosticsContent');
        if (!container) return;

        let html = '<div class="diagnostics-sections">';
        
        Object.entries(data).forEach(([section, info]) => {
            html += `
                <div class="diagnostic-section">
                    <h5>${this.formatSectionName(section)}</h5>
                    <div class="diagnostic-content">
                        ${this.formatDiagnosticData(info)}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    formatDiagnosticData(data) {
        if (typeof data === 'object' && data !== null) {
            let html = '<div class="diagnostic-items">';
            Object.entries(data).forEach(([key, value]) => {
                html += `
                    <div class="diagnostic-item">
                        <span class="key">${this.formatKey(key)}:</span>
                        <span class="value">${this.formatValue(value)}</span>
                    </div>
                `;
            });
            html += '</div>';
            return html;
        }
        return `<div class="diagnostic-value">${data}</div>`;
    }

    updateSystemHealth(healthData) {
        const indicator = document.getElementById('healthIndicator');
        const status = document.getElementById('healthStatus');
        
        if (indicator && status) {
            const overallStatus = healthData.overall_status || 'unknown';
            this.systemHealth = overallStatus;
            
            // 更新状态指示器
            const dot = indicator.querySelector('.status-dot');
            dot.className = `status-dot ${overallStatus}`;
            
            // 更新状态文本
            const statusText = {
                'healthy': '系统正常',
                'warning': '需要注意',
                'critical': '严重问题',
                'error': '系统错误',
                'unknown': '状态未知'
            };
            
            status.textContent = statusText[overallStatus] || overallStatus;
        }
    }

    updateMonitoringData(data) {
        if (data.cpu && data.cpu.load_1min !== undefined) {
            this.updateElement('cpuUsage', `${Math.round(data.cpu.load_1min * 100)}%`);
        }
        
        if (data.memory && data.memory.php_memory) {
            const usage = data.memory.php_memory;
            const percent = Math.round((usage.current / usage.peak) * 100);
            this.updateElement('memoryUsage', `${percent}%`);
        }
        
        if (data.disk && data.disk.usage_percent !== undefined) {
            this.updateElement('diskUsage', `${data.disk.usage_percent}%`);
        }
    }

    filterResults(filter) {
        const results = document.querySelectorAll('.test-result-item');
        results.forEach(item => {
            if (filter === 'all' || item.dataset.status === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    getStatusIcon(status) {
        const icons = {
            'passed': 'check-circle-fill',
            'failed': 'x-circle-fill',
            'warning': 'exclamation-triangle-fill',
            'running': 'arrow-clockwise'
        };
        return icons[status] || 'question-circle';
    }

    formatSectionName(section) {
        return section.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    formatKey(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    formatValue(value) {
        if (typeof value === 'boolean') {
            return value ? '✅ 是' : '❌ 否';
        }
        if (typeof value === 'object') {
            return JSON.stringify(value);
        }
        return value;
    }

    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    updateButton(id, text, disabled) {
        const button = document.getElementById(id);
        if (button) {
            button.textContent = text;
            button.disabled = disabled;
        }
    }

    async apiCall(method, endpoint, data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(endpoint, options);
        return await response.json();
    }

    log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        console.log(`[${timestamp}] ${message}`);
        
        // 可以添加到UI日志显示区域
        const logArea = document.getElementById('adminTestLog');
        if (logArea) {
            const logEntry = document.createElement('div');
            logEntry.className = `log-entry log-${type}`;
            logEntry.innerHTML = `<span class="timestamp">${timestamp}</span> ${message}`;
            logArea.appendChild(logEntry);
            logArea.scrollTop = logArea.scrollHeight;
        }
    }

    // 管理员专用测试方法
    async testDatabaseConnection() {
        return await this.apiCall('GET', '/api/public/health');
    }

    async testApiServices() {
        const endpoints = ['/api/public/status', '/api/public/version'];
        const results = [];
        
        for (const endpoint of endpoints) {
            try {
                const response = await this.apiCall('GET', endpoint);
                results.push({ endpoint, status: 'success', response });
            } catch (error) {
                results.push({ endpoint, status: 'error', error: error.message });
            }
        }
        
        return { results };
    }

    async testAdminPermissions() {
        try {
            const response = await this.apiCall('GET', '/api/unified-admin/dashboard');
            return { 
                status: response.success ? 'passed' : 'failed',
                message: response.success ? '管理员权限验证成功' : '管理员权限验证失败'
            };
        } catch (error) {
            return {
                status: 'failed',
                message: '管理员权限测试失败: ' + error.message
            };
        }
    }
}

// 初始化增强管理测试系统
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('adminTestSystem')) {
        window.enhancedAdminTestingSystem = new EnhancedAdminTestingSystem();
    }
});

// 导出给全局使用
window.EnhancedAdminTestingSystem = EnhancedAdminTestingSystem;
