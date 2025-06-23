/**
 * AlingAI Pro 5.0 管理后台 JavaScript
 */

// 全局变量
let currentTab = 'dashboard';
let refreshInterval;

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    initializeAdmin();
    setupEventListeners();
    startAutoRefresh();
});

// 初始化管理后台
function initializeAdmin() {
    // 显示当前选中的标签页
    showTab('dashboard');
    
    // 加载初始数据
    refreshData();
    
    // 设置当前时间
    updateServerTime();
    setInterval(updateServerTime, 1000);
}

// 设置事件监听器
function setupEventListeners() {
    // 导航链接点击事件
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tab = this.getAttribute('data-tab');
            showTab(tab);
        });
    });
    
    // 键盘快捷键
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            refreshData();
        }
    });
}

// 显示指定标签页
function showTab(tabName) {
    // 隐藏所有标签页内容
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // 移除所有导航链接的活跃状态
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('bg-white', 'bg-opacity-20');
    });
    
    // 显示选中的标签页
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
        
        // 设置对应导航链接为活跃状态
        const navLink = document.querySelector(`[data-tab="${tabName}"]`);
        if (navLink) {
            navLink.classList.add('bg-white', 'bg-opacity-20');
        }
        
        // 更新页面标题
        const titles = {
            'dashboard': '系统概览',
            'database': '数据库管理',
            'testing': '系统测试',
            'health': '健康检查',
            'debug': '调试工具',
            'optimization': '系统优化',
            'logs': '日志管理',
            'intelligent': '智能监控',
            'ai-services': 'AI服务监控',
            'security': '安全监控',
            'performance': '性能监控',
            'threats': '威胁情报',
            'business': '业务指标'
        };
        
        document.getElementById('page-title').textContent = titles[tabName] || '系统管理';
        currentTab = tabName;
        
        // 根据标签页加载相应数据
        loadTabData(tabName);
    }
}

// 加载标签页数据
function loadTabData(tabName) {
    switch(tabName) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'database':
            loadDatabaseData();
            break;
        case 'intelligent':
            loadIntelligentMonitoring();
            break;
        case 'ai-services':
            loadAIServicesData();
            break;
        case 'security':
            loadSecurityMonitoring();
            break;
        case 'performance':
            loadPerformanceMetrics();
            break;
        case 'threats':
            loadThreatIntelligence();
            break;
        case 'business':
            loadBusinessMetrics();
            break;
        default:
            // 其他标签页按需加载
            break;
    }
}

// 刷新数据
async function refreshData() {
    showLoading(true);
    
    try {
        // 获取系统状态
        const systemStatus = await fetchData('system_status');
        updateSystemStatus(systemStatus);
        
        // 根据当前标签页加载相应数据
        loadTabData(currentTab);
        
    } catch (error) {
        console.error('刷新数据失败:', error);
        showNotification('数据刷新失败', 'error');
    } finally {
        showLoading(false);
    }
}

// 加载仪表板数据
async function loadDashboardData() {
    try {
        const data = await fetchData('system_status');
        
        // 更新系统状态卡片
        document.getElementById('system-status').textContent = data.system_status || '正常';
        document.getElementById('database-status').textContent = data.database_status || '正常';
        document.getElementById('memory-usage').textContent = data.memory_usage || '未知';
        document.getElementById('uptime').textContent = data.uptime || '未知';
        
        // 更新系统信息
        document.getElementById('php-version').textContent = data.php_version || '未知';
        document.getElementById('system-version').textContent = 'AlingAI Pro 5.0';
        
    } catch (error) {
        console.error('加载仪表板数据失败:', error);
    }
}

// 加载数据库数据
async function loadDatabaseData() {
    try {
        const data = await fetchData('database_check');
        
        const dbInfo = document.getElementById('db-info');
        if (dbInfo) {
            dbInfo.innerHTML = `
                <div class="flex justify-between">
                    <span>连接状态:</span>
                    <span class="text-${data.connected ? 'green' : 'red'}-500">
                        ${data.connected ? '已连接' : '未连接'}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>数据库类型:</span>
                    <span>${data.type || '未知'}</span>
                </div>
                <div class="flex justify-between">
                    <span>表数量:</span>
                    <span>${data.table_count || 0}</span>
                </div>
            `;
        }
        
    } catch (error) {
        console.error('加载数据库数据失败:', error);
    }
}

// ==================== 智能监控功能 ====================

// 加载智能监控数据
async function loadIntelligentMonitoring() {
    try {
        const response = await fetch('?action=intelligent_monitoring');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // 更新系统健康评分
        const healthData = data.system_health;
        if (healthData) {
            updateElement('health-score', healthData.overall_score || '--');
            updateHealthBar(healthData.overall_score || 0);
        }
        
        // 更新活跃警报
        updateActiveAlerts(healthData?.alerts || []);
        
        // 更新组件状态
        updateComponentStatus(healthData?.components || {});
        
    } catch (error) {
        console.error('加载智能监控数据失败:', error);
        showNotification('加载智能监控数据失败: ' + error.message, 'error');
    }
}

// 更新健康评分条
function updateHealthBar(score) {
    const healthBar = document.getElementById('health-bar');
    if (healthBar) {
        healthBar.style.width = score + '%';
        
        // 根据分数设置颜色
        if (score >= 90) {
            healthBar.className = 'bg-green-500 h-2 rounded-full transition-all duration-500';
        } else if (score >= 70) {
            healthBar.className = 'bg-yellow-500 h-2 rounded-full transition-all duration-500';
        } else {
            healthBar.className = 'bg-red-500 h-2 rounded-full transition-all duration-500';
        }
    }
}

// 更新活跃警报
function updateActiveAlerts(alerts) {
    const container = document.getElementById('active-alerts');
    if (!container) return;
    
    if (alerts.length === 0) {
        container.innerHTML = '<div class="text-green-500"><i class="fas fa-check-circle mr-2"></i>无活跃警报</div>';
        return;
    }
    
    container.innerHTML = alerts.map(alert => `
        <div class="flex items-center p-2 border-l-4 border-${alert.type === 'critical' ? 'red' : 'yellow'}-500 bg-${alert.type === 'critical' ? 'red' : 'yellow'}-50 rounded mb-2">
            <i class="fas fa-exclamation-triangle text-${alert.type === 'critical' ? 'red' : 'yellow'}-500 mr-2"></i>
            <div>
                <div class="font-medium">${alert.component}</div>
                <div class="text-sm text-gray-600">${alert.message}</div>
            </div>
        </div>
    `).join('');
}

// 更新组件状态
function updateComponentStatus(components) {
    const container = document.getElementById('component-status');
    if (!container) return;
    
    container.innerHTML = Object.entries(components).map(([name, status]) => {
        return `
            <div class="bg-white p-4 rounded-lg border shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium">${getComponentDisplayName(name)}</span>
                    <span class="status-indicator status-${status.status === 'healthy' ? 'healthy' : status.status === 'warning' ? 'warning' : 'error'}"></span>
                </div>
                <div class="text-sm text-gray-600">${status.message || '运行正常'}</div>
            </div>
        `;
    }).join('');
}

// 获取组件显示名称
function getComponentDisplayName(name) {
    const names = {
        'database': '数据库',
        'cache': '缓存系统',
        'websocket': 'WebSocket',
        'ai_service': 'AI服务',
        'security_system': '安全系统',
        'file_system': '文件系统'
    };
    return names[name] || name;
}

// 加载AI服务数据
async function loadAIServicesData() {
    try {
        const response = await fetch('?action=ai_services_status');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // 更新AI服务概览
        updateAIOverview(data);
        
        // 更新性能指标
        updateAIPerformance(data.performance_metrics || {});
        
        // 更新服务详情
        updateAIServicesDetail(data.services || {});
        
    } catch (error) {
        console.error('加载AI服务数据失败:', error);
        showNotification('加载AI服务数据失败: ' + error.message, 'error');
    }
}

// 更新AI服务概览
function updateAIOverview(data) {
    const container = document.getElementById('ai-overview');
    if (!container) return;
    
    const totalServices = Object.keys(data.services || {}).length;
    const activeServices = Object.values(data.services || {}).filter(s => s.status === 'healthy').length;
    
    container.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-500">${totalServices}</div>
                <div class="text-sm text-gray-600">总服务数</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-500">${activeServices}</div>
                <div class="text-sm text-gray-600">活跃服务</div>
            </div>
        </div>
    `;
}

// 更新AI性能指标
function updateAIPerformance(metrics) {
    const container = document.getElementById('ai-performance');
    if (!container) return;
    
    container.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between">
                <span>平均响应时间</span>
                <span class="font-medium">${metrics.average_response_time_ms || 'N/A'}ms</span>
            </div>
            <div class="flex justify-between">
                <span>成功率</span>
                <span class="font-medium text-green-500">${metrics.success_rate || 'N/A'}</span>
            </div>
            <div class="flex justify-between">
                <span>今日请求数</span>
                <span class="font-medium">${metrics.total_requests_24h || 'N/A'}</span>
            </div>
            <div class="flex justify-between">
                <span>吞吐量</span>
                <span class="font-medium">${metrics.throughput_per_second || 'N/A'}/s</span>
            </div>
        </div>
    `;
}

// 更新AI服务详情
function updateAIServicesDetail(services) {
    const container = document.getElementById('ai-services-detail');
    if (!container) return;
    
    container.innerHTML = Object.entries(services).map(([name, service]) => {
        const statusColor = service.status === 'healthy' ? 'green' : 'red';
        return `
            <div class="bg-white p-4 rounded-lg border shadow-sm mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium">${getAIServiceDisplayName(name)}</span>
                    <span class="px-2 py-1 rounded text-sm bg-${statusColor}-100 text-${statusColor}-600">
                        ${service.status === 'healthy' ? '运行中' : '离线'}
                    </span>
                </div>
                <div class="text-sm text-gray-600">
                    响应时间: ${service.response_time_ms || 'N/A'}ms
                </div>
            </div>
        `;
    }).join('');
}

// 获取AI服务显示名称
function getAIServiceDisplayName(name) {
    const names = {
        'deepseek_api': 'DeepSeek API',
        'natural_language_processing': '自然语言处理',
        'computer_vision': '计算机视觉',
        'speech_processing': '语音处理',
        'knowledge_graph': '知识图谱',
        'recommendation_engine': '推荐引擎'
    };
    return names[name] || name;
}

// 加载安全监控数据
async function loadSecurityMonitoring() {
    try {
        const response = await fetch('?action=security_monitoring');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // 更新威胁等级
        updateElement('threat-level', data.threat_level || '--');
        
        // 更新安全评分
        updateElement('security-score', data.security_score || '--');
        
        // 更新活跃威胁
        updateActiveThreats(data.active_threats || []);
        
        // 更新零信任状态
        updateZeroTrustStatus(data.zero_trust_status || {});
        
    } catch (error) {
        console.error('加载安全监控数据失败:', error);
        showNotification('加载安全监控数据失败: ' + error.message, 'error');
    }
}

// 更新活跃威胁
function updateActiveThreats(threats) {
    const container = document.getElementById('active-threats');
    if (!container) return;
    
    if (threats.length === 0) {
        container.innerHTML = '<div class="text-green-500"><i class="fas fa-shield-alt mr-2"></i>无活跃威胁</div>';
        return;
    }
    
    container.innerHTML = threats.map(threat => `
        <div class="text-sm p-2 border-l-4 border-red-500 bg-red-50 rounded mb-2">
            <div class="font-medium">${threat.type}</div>
            <div class="text-gray-600">${threat.source_ip} - ${threat.severity}</div>
        </div>
    `).join('');
}

// 更新零信任状态
function updateZeroTrustStatus(status) {
    const container = document.getElementById('zero-trust-status');
    if (!container) return;
    
    container.innerHTML = `
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>启用状态</span>
                <span class="px-2 py-1 rounded text-sm ${status.enabled ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'}">
                    ${status.enabled ? '已启用' : '未启用'}
                </span>
            </div>
            <div class="flex justify-between">
                <span>覆盖率</span>
                <span class="font-medium">${status.coverage || 'N/A'}</span>
            </div>
            <div class="flex justify-between">
                <span>活跃策略</span>
                <span class="font-medium">${status.policies_active || 0}</span>
            </div>
        </div>
    `;
}

// 加载性能监控数据
async function loadPerformanceMetrics() {
    try {
        const response = await fetch('?action=performance_metrics');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // 更新响应时间
        updateResponseTimes(data.response_times || {});
        
        // 更新资源利用率
        updateResourceUtilization(data.resource_utilization || {});
        
        // 更新性能建议
        updatePerformanceSuggestions(data.bottlenecks || [], data.optimization_suggestions || []);
        
    } catch (error) {
        console.error('加载性能监控数据失败:', error);
        showNotification('加载性能监控数据失败: ' + error.message, 'error');
    }
}

// 更新响应时间
function updateResponseTimes(times) {
    const container = document.getElementById('response-times');
    if (!container) return;
    
    container.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between">
                <span>平均响应时间</span>
                <span class="font-medium">${times.average_ms || 'N/A'}ms</span>
            </div>
            <div class="flex justify-between">
                <span>95th 百分位</span>
                <span class="font-medium">${times.p95_ms || 'N/A'}ms</span>
            </div>
            <div class="flex justify-between">
                <span>99th 百分位</span>
                <span class="font-medium">${times.p99_ms || 'N/A'}ms</span>
            </div>
        </div>
    `;
}

// 更新资源利用率
function updateResourceUtilization(utilization) {
    const container = document.getElementById('resource-utilization');
    if (!container) return;
    
    container.innerHTML = Object.entries(utilization).map(([key, value]) => {
        const percentage = typeof value === 'number' ? value : parseFloat(value) || 0;
        const color = percentage > 80 ? 'red' : percentage > 60 ? 'yellow' : 'green';
        
        return `
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span>${getResourceDisplayName(key)}</span>
                    <span>${percentage}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-${color}-500 h-2 rounded-full" style="width: ${percentage}%"></div>
                </div>
            </div>
        `;
    }).join('');
}

// 获取资源显示名称
function getResourceDisplayName(key) {
    const names = {
        'cpu_percent': 'CPU使用率',
        'memory_percent': '内存使用率',
        'disk_io_percent': '磁盘IO',
        'network_utilization_percent': '网络利用率'
    };
    return names[key] || key;
}

// 更新性能建议
function updatePerformanceSuggestions(bottlenecks, suggestions) {
    const container = document.getElementById('performance-suggestions');
    if (!container) return;
    
    let html = '';
    
    if (bottlenecks.length > 0) {
        html += '<h4 class="font-medium mb-2 text-red-600">检测到的瓶颈:</h4>';
        html += bottlenecks.map(bottleneck => `
            <div class="p-3 bg-red-50 border-l-4 border-red-500 rounded mb-2">
                <div class="text-sm">${bottleneck}</div>
            </div>
        `).join('');
    }
    
    if (suggestions.length > 0) {
        html += '<h4 class="font-medium mb-2 mt-4 text-blue-600">优化建议:</h4>';
        html += suggestions.map(suggestion => `
            <div class="p-2 bg-blue-50 rounded mb-1">
                <div class="text-sm">${suggestion}</div>
            </div>
        `).join('');
    }
    
    if (!html) {
        html = '<div class="text-green-500"><i class="fas fa-check-circle mr-2"></i>系统性能良好，暂无优化建议</div>';
    }
    
    container.innerHTML = html;
}

// 加载威胁情报数据
async function loadThreatIntelligence() {
    try {
        const response = await fetch('?action=threat_intelligence');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // 更新全球威胁
        updateGlobalThreats(data.global_threats || {});
        
        // 更新本地威胁
        updateLocalThreats(data.local_threats || {});
        
        // 更新威胁分析
        updateThreatAnalysis(data.threat_patterns || {}, data.predictive_analysis || {});
        
    } catch (error) {
        console.error('加载威胁情报数据失败:', error);
        showNotification('加载威胁情报数据失败: ' + error.message, 'error');
    }
}

// 更新全球威胁
function updateGlobalThreats(threats) {
    const container = document.getElementById('global-threats');
    if (!container) return;
    
    container.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between">
                <span>活跃恶意软件</span>
                <span class="font-medium text-red-500">${threats.active_malware_families || 0}</span>
            </div>
            <div class="flex justify-between">
                <span>今日新漏洞</span>
                <span class="font-medium text-yellow-500">${threats.new_vulnerabilities_24h || 0}</span>
            </div>
            <div class="text-sm">
                <div class="text-gray-600 mb-1">攻击趋势:</div>
                ${Object.entries(threats.global_attack_trends || {}).map(([type, percent]) => `
                    <div class="flex justify-between">
                        <span>${type}</span>
                        <span>${percent}</span>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

// 更新本地威胁
function updateLocalThreats(threats) {
    const container = document.getElementById('local-threats');
    if (!container) return;
    
    container.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between">
                <span>今日阻止IP</span>
                <span class="font-medium text-green-500">${threats.blocked_ips_24h || 0}</span>
            </div>
            <div class="flex justify-between">
                <span>可疑活动</span>
                <span class="font-medium text-yellow-500">${threats.suspicious_activities || 0}</span>
            </div>
            <div class="flex justify-between">
                <span>登录失败</span>
                <span class="font-medium text-orange-500">${threats.failed_login_attempts || 0}</span>
            </div>
            <div class="flex justify-between">
                <span>恶意软件检测</span>
                <span class="font-medium text-red-500">${threats.malware_detections || 0}</span>
            </div>
        </div>
    `;
}

// 更新威胁分析
function updateThreatAnalysis(patterns, analysis) {
    const container = document.getElementById('threat-analysis');
    if (!container) return;
    
    let html = '';
    
    if (patterns.peak_attack_hours) {
        html += '<h4 class="font-medium mb-2">攻击模式:</h4>';
        html += `
            <div class="p-2 bg-yellow-50 rounded mb-2">
                <div class="text-sm">攻击高峰时段: ${patterns.peak_attack_hours.join(', ')}</div>
            </div>
            <div class="p-2 bg-yellow-50 rounded mb-2">
                <div class="text-sm">常见攻击向量: ${patterns.common_attack_vectors?.join(', ') || 'N/A'}</div>
            </div>
        `;
    }
    
    if (analysis.risk_score) {
        html += '<h4 class="font-medium mb-2 mt-4">预测性分析:</h4>';
        html += `
            <div class="p-3 bg-blue-50 rounded mb-2">
                <div class="flex justify-between mb-2">
                    <span>风险评分</span>
                    <span class="font-medium">${analysis.risk_score}/100</span>
                </div>
                <div class="flex justify-between">
                    <span>攻击概率</span>
                    <span class="font-medium">${analysis.predicted_attack_probability || 'N/A'}</span>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html || '<div class="text-gray-400">暂无威胁分析数据</div>';
}

// 加载业务指标数据
async function loadBusinessMetrics() {
    try {
        const response = await fetch('?action=business_metrics');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // 更新顶部指标卡片
        updateBusinessCards(data);
        
        // 更新用户活动
        updateUserActivity(data.user_activity || {});
        
        // 更新错误率
        updateErrorRates(data.error_rates || {});
        
    } catch (error) {
        console.error('加载业务指标数据失败:', error);
        showNotification('加载业务指标数据失败: ' + error.message, 'error');
    }
}

// 更新业务指标卡片
function updateBusinessCards(data) {
    const userActivity = data.user_activity || {};
    const apiUsage = data.api_usage || {};
    const conversations = data.conversation_analytics || {};
    const satisfaction = data.satisfaction_scores || {};
    
    updateElement('active-users', userActivity.active_users_24h || '--');
    updateElement('api-calls', apiUsage.total_api_calls_24h || '--');
    updateElement('conversations', conversations.total_conversations_24h || '--');
    updateElement('satisfaction', satisfaction.overall_satisfaction || '--');
}

// 更新用户活动
function updateUserActivity(activity) {
    const container = document.getElementById('user-activity');
    if (!container) return;
    
    container.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between">
                <span>24小时活跃用户</span>
                <span class="font-medium text-blue-500">${activity.active_users_24h || 0}</span>
            </div>
            <div class="flex justify-between">
                <span>今日新注册</span>
                <span class="font-medium text-green-500">${activity.new_registrations_24h || 0}</span>
            </div>
            <div class="flex justify-between">
                <span>平均会话时长</span>
                <span class="font-medium">${activity.session_duration_minutes || 'N/A'}分钟</span>
            </div>
            <div class="flex justify-between">
                <span>用户参与度</span>
                <span class="font-medium">${activity.user_engagement_score || 'N/A'}</span>
            </div>
        </div>
    `;
}

// 更新错误率
function updateErrorRates(errorData) {
    const container = document.getElementById('error-rates');
    if (!container) return;
    
    container.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between">
                <span>24小时错误数</span>
                <span class="font-medium ${errorData.total_errors_24h > 100 ? 'text-red-500' : 'text-green-500'}">${errorData.total_errors_24h || 0}</span>
            </div>
            <div class="flex justify-between">
                <span>错误率</span>
                <span class="font-medium">${errorData.error_rate_percent || 'N/A'}%</span>
            </div>
            <div class="flex justify-between">
                <span>严重错误</span>
                <span class="font-medium text-red-500">${errorData.critical_errors || 0}</span>
            </div>
            <div class="flex justify-between">
                <span>修复时间</span>
                <span class="font-medium">${errorData.mean_time_to_resolution_minutes || 'N/A'}分钟</span>
            </div>
        </div>
    `;
}

// ==================== 工具函数 ====================

// 更新元素内容
function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

// 更新系统状态指示器
function updateSystemStatus(data) {
    const indicator = document.getElementById('system-status-indicator');
    const text = document.getElementById('system-status-text');
    
    if (indicator && text) {
        if (data.overall_status === 'healthy') {
            indicator.className = 'status-indicator status-healthy';
            text.textContent = '系统正常';
        } else if (data.overall_status === 'warning') {
            indicator.className = 'status-indicator status-warning';
            text.textContent = '系统警告';
        } else {
            indicator.className = 'status-indicator status-error';
            text.textContent = '系统错误';
        }
    }
}

// 更新服务器时间
function updateServerTime() {
    const now = new Date();
    const timeStr = now.toLocaleString('zh-CN');
    const serverTimeElement = document.getElementById('server-time');
    if (serverTimeElement) {
        serverTimeElement.textContent = timeStr;
    }
}

// 获取数据
async function fetchData(action, params = {}) {
    const url = new URL('index.php', window.location.href);
    url.searchParams.set('action', action);
    
    Object.keys(params).forEach(key => {
        url.searchParams.set(key, params[key]);
    });
    
    const response = await fetch(url);
    
    if (!response.ok) {
        throw new Error('HTTP ' + response.status);
    }
    
    const data = await response.json();
    
    if (data.error) {
        throw new Error(data.error);
    }
    
    return data;
}

// 显示/隐藏加载指示器
function showLoading(show) {
    const loading = document.getElementById('loading');
    if (loading) {
        if (show) {
            loading.classList.remove('hidden');
        } else {
            loading.classList.add('hidden');
        }
    }
}

// 显示通知
function showNotification(message, type = 'info') {
    // 创建通知元素
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'error' ? 'bg-red-500' : 
        type === 'success' ? 'bg-green-500' : 
        'bg-blue-500'
    } text-white`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // 3秒后自动移除
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// 开始自动刷新
function startAutoRefresh() {
    // 每30秒自动刷新一次系统状态
    refreshInterval = setInterval(() => {
        if (currentTab === 'dashboard') {
            refreshData();
        } else if (['intelligent', 'ai-services', 'security', 'performance', 'threats', 'business'].includes(currentTab)) {
            loadTabData(currentTab);
        }
    }, 30000);
}

// 停止自动刷新
function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}
