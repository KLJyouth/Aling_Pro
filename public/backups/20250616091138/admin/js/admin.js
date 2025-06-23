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
    }
    
    // 高亮选中的导航链接
    const selectedNavLink = document.querySelector(`[data-tab="${tabName}"]`);
    if (selectedNavLink) {
        selectedNavLink.classList.add('bg-white', 'bg-opacity-20');
    }
    
    // 更新页面标题
    const pageTitleElement = document.getElementById('page-title');
    if (pageTitleElement) {
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
            'business': '业务指标',
            'websocket': 'WebSocket监控',
            'chat': '聊天监控',
            'analytics': '分析报告',
            'cache': '缓存管理',
            'database-performance': '数据库性能',
            'api-analytics': 'API分析',
            'realtime': '实时监控'
        };
        pageTitleElement.textContent = titles[tabName] || '系统管理';
    }
    
    // 设置当前标签页
    currentTab = tabName;
    
    // 加载对应标签页的数据
    loadTabData(tabName);
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
        case 'websocket':
            loadWebSocketStatus();
            break;
        case 'chat':
            loadChatMonitoring();
            break;
        case 'cache':
            loadCacheManagement();
            break;
        case 'database-performance':
            loadDatabasePerformance();
            break;
        case 'api-analytics':
            loadAPIAnalytics();
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

// WebSocket状态监控
function loadWebSocketStatus() {
    fetchData('websocket_status')
        .then(data => {
            if (data.status === 'active') {
                updateWebSocketDisplay(data);
            } else {
                showError('WebSocket状态获取失败: ' + (data.message || '未知错误'));
            }
        })
        .catch(error => {
            console.error('WebSocket状态加载失败:', error);
            showError('WebSocket状态加载失败');
        });
}

// 更新WebSocket显示
function updateWebSocketDisplay(data) {
    const container = document.getElementById('websocket-status-container');
    if (!container) return;

    const connections = data.connections;
    const channels = data.channels;
    const performance = data.performance;

    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">连接统计</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span>活跃连接:</span>
                        <span class="font-medium">${connections.active_connections}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>峰值连接:</span>
                        <span class="font-medium">${connections.peak_connections}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>消息发送:</span>
                        <span class="font-medium">${connections.total_messages_sent.toLocaleString()}</span>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-semibold text-green-800 mb-2">性能指标</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span>CPU使用率:</span>
                        <span class="font-medium">${performance.cpu_usage}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>内存使用:</span>
                        <span class="font-medium">${performance.memory_usage}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>网络吞吐:</span>
                        <span class="font-medium">${performance.network_throughput}</span>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h4 class="font-semibold text-purple-800 mb-2">频道状态</h4>
                <div class="space-y-1 text-sm">
                    ${Object.entries(channels).map(([channel, stats]) => `
                        <div class="flex justify-between">
                            <span>${channel}:</span>
                            <span class="font-medium">${stats.active}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
        <div class="text-xs text-gray-500">最后更新: ${data.last_updated}</div>
    `;
}

// 聊天系统监控
function loadChatMonitoring() {
    fetchData('chat_monitoring')
        .then(data => {
            updateChatMonitoringDisplay(data);
        })
        .catch(error => {
            console.error('聊天监控数据加载失败:', error);
            showError('聊天监控数据加载失败');
        });
}

// 更新聊天监控显示
function updateChatMonitoringDisplay(data) {
    const container = document.getElementById('chat-monitoring-container');
    if (!container) return;

    const chatStats = data.chat_statistics;
    const qualityMetrics = data.quality_metrics;
    const realTimeMetrics = data.real_time_metrics;

    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-2xl font-bold text-blue-600">${chatStats.total_conversations.toLocaleString()}</div>
                <div class="text-sm text-gray-600">总对话数</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-2xl font-bold text-green-600">${chatStats.active_conversations}</div>
                <div class="text-sm text-gray-600">活跃对话</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-2xl font-bold text-purple-600">${chatStats.ai_accuracy_rate}</div>
                <div class="text-sm text-gray-600">AI准确率</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-2xl font-bold text-yellow-600">${chatStats.user_satisfaction}</div>
                <div class="text-sm text-gray-600">用户满意度</div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-3">质量指标</h4>
                <div class="space-y-2">
                    ${Object.entries(qualityMetrics).map(([metric, value]) => `
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">${getMetricLabel(metric)}:</span>
                            <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: ${value}"></div>
                                </div>
                                <span class="text-sm font-medium">${value}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-3">实时指标</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>当前在线用户:</span>
                        <span class="font-medium">${realTimeMetrics.current_active_users}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>每分钟消息数:</span>
                        <span class="font-medium">${realTimeMetrics.messages_per_minute}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>AI处理队列:</span>
                        <span class="font-medium">${realTimeMetrics.ai_processing_queue}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-xs text-gray-500">最后更新: ${data.last_updated}</div>
    `;
}

// 分析报告生成
function generateAnalyticsReport(period = 'today') {
    showLoading();
    fetchData('analytics_report', { period })
        .then(data => {
            hideLoading();
            displayAnalyticsReport(data);
        })
        .catch(error => {
            hideLoading();
            console.error('分析报告生成失败:', error);
            showError('分析报告生成失败');
        });
}

// 显示分析报告
function displayAnalyticsReport(data) {
    const container = document.getElementById('analytics-report-container');
    if (!container) return;

    const summary = data.summary;
    const performance = data.performance_metrics;
    const aiPerformance = data.ai_performance;
    const trends = data.trends;

    container.innerHTML = `
        <div class="mb-6">
            <h3 class="text-xl font-bold mb-4">${data.period}分析报告</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg">
                    <div class="text-2xl font-bold">${summary.total_users.toLocaleString()}</div>
                    <div class="text-blue-100">总用户数</div>
                    <div class="text-sm text-blue-200">${trends.user_growth} 增长</div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg">
                    <div class="text-2xl font-bold">${summary.active_sessions.toLocaleString()}</div>
                    <div class="text-green-100">活跃会话</div>
                    <div class="text-sm text-green-200">${trends.engagement_change} 变化</div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg">
                    <div class="text-2xl font-bold">${performance.avg_response_time}</div>
                    <div class="text-purple-100">平均响应时间</div>
                    <div class="text-sm text-purple-200">${trends.performance_change} 优化</div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="font-semibold mb-3">性能指标</h4>
                    <div class="space-y-2">
                        ${Object.entries(performance).map(([key, value]) => `
                            <div class="flex justify-between">
                                <span class="text-gray-600">${getPerformanceLabel(key)}:</span>
                                <span class="font-medium">${value}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="font-semibold mb-3">AI性能</h4>
                    <div class="space-y-2">
                        ${Object.entries(aiPerformance).map(([key, value]) => `
                            <div class="flex justify-between">
                                <span class="text-gray-600">${getAIPerformanceLabel(key)}:</span>
                                <span class="font-medium">${value}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-xs text-gray-500">报告生成时间: ${data.generated_at}</div>
    `;
}

// 实时数据流
function startRealTimeDataStream() {
    // 每5秒获取一次实时数据
    setInterval(() => {
        if (currentTab === 'dashboard' || currentTab === 'performance') {
            fetchData('realtime_stream')
                .then(data => {
                    updateRealTimeChart(data);
                })
                .catch(error => {
                    console.error('实时数据获取失败:', error);
                });
        }
    }, 5000);
}

// 更新实时图表
function updateRealTimeChart(data) {
    const container = document.getElementById('realtime-chart-container');
    if (!container) return;

    // 这里可以集成Chart.js或其他图表库
    // 目前使用简单的数据显示
    const latestData = data.time_series[data.time_series.length - 1];
    const metrics = data.real_time_metrics;

    container.innerHTML = `
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">${latestData.users_online}</div>
                <div class="text-sm text-gray-600">在线用户</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">${latestData.cpu_usage}%</div>
                <div class="text-sm text-gray-600">CPU使用率</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">${latestData.response_time}ms</div>
                <div class="text-sm text-gray-600">响应时间</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">${latestData.requests_per_minute}</div>
                <div class="text-sm text-gray-600">每分钟请求</div>
            </div>
        </div>
        
        ${data.alerts.length > 0 ? `
            <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400">
                <h4 class="font-semibold text-yellow-800">系统警报</h4>
                <ul class="mt-2 space-y-1">
                    ${data.alerts.map(alert => `
                        <li class="text-sm text-yellow-700">
                            <span class="font-medium">${alert.timestamp}</span> - ${alert.message}
                        </li>
                    `).join('')}
                </ul>
            </div>
        ` : ''}
    `;
}

// 缓存管理
function loadCacheManagement() {
    fetchData('cache_management')
        .then(data => {
            updateCacheManagementDisplay(data);
        })
        .catch(error => {
            console.error('缓存管理数据加载失败:', error);
            showError('缓存管理数据加载失败');
        });
}

// 更新缓存管理显示
function updateCacheManagementDisplay(data) {
    const container = document.getElementById('cache-management-container');
    if (!container) return;

    const stats = data.cache_statistics;
    const types = data.cache_types;
    const performance = data.performance;
    const recommendations = data.recommendations;

    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">${stats.total_keys.toLocaleString()}</div>
                <div class="text-sm text-gray-600">总缓存键数</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">${stats.hit_ratio}</div>
                <div class="text-sm text-gray-600">命中率</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">${stats.memory_usage}</div>
                <div class="text-sm text-gray-600">内存使用</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">${performance.ops_per_second.toLocaleString()}</div>
                <div class="text-sm text-gray-600">操作/秒</div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-3">缓存类型统计</h4>
                <div class="space-y-3">
                    ${Object.entries(types).map(([type, typeStats]) => `
                        <div class="border-l-4 border-blue-400 pl-3">
                            <div class="font-medium">${getCacheTypeLabel(type)}</div>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>数量: ${typeStats.count.toLocaleString()}</div>
                                <div>命中率: ${typeStats.hit_rate}</div>
                                <div>平均大小: ${typeStats.avg_size}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-3">性能指标</h4>
                <div class="space-y-2">
                    ${Object.entries(performance).map(([key, value]) => `
                        <div class="flex justify-between">
                            <span class="text-gray-600">${getCachePerformanceLabel(key)}:</span>
                            <span class="font-medium">${value}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
        
        ${recommendations.length > 0 ? `
            <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-400">
                <h4 class="font-semibold text-yellow-800 mb-2">优化建议</h4>
                <ul class="space-y-1">
                    ${recommendations.map(rec => `
                        <li class="text-sm text-yellow-700">• ${rec}</li>
                    `).join('')}
                </ul>
            </div>
        ` : ''}
        
        <div class="text-xs text-gray-500 mt-4">最后更新: ${data.last_updated}</div>
    `;
}

// 数据库性能分析
function loadDatabasePerformance() {
    fetchData('database_performance')
        .then(data => {
            updateDatabasePerformanceDisplay(data);
        })
        .catch(error => {
            console.error('数据库性能分析加载失败:', error);
            showError('数据库性能分析加载失败');
        });
}

// 更新数据库性能显示
function updateDatabasePerformanceDisplay(data) {
    const container = document.getElementById('database-performance-container');
    if (!container) return;

    const stats = data.database_statistics;
    const slowQueries = data.query_analysis.top_slow_queries;
    const suggestions = data.optimization_suggestions;

    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">${stats.total_connections}</div>
                <div class="text-sm text-gray-600">总连接数</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">${stats.queries_per_second}</div>
                <div class="text-sm text-gray-600">查询/秒</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">${stats.avg_query_time}</div>
                <div class="text-sm text-gray-600">平均查询时间</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">${stats.buffer_hit_ratio}</div>
                <div class="text-sm text-gray-600">缓冲区命中率</div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-3">慢查询分析</h4>
                <div class="space-y-3">
                    ${slowQueries.map(query => `
                        <div class="border-l-4 border-red-400 pl-3 py-2">
                            <div class="font-mono text-sm bg-gray-100 p-2 rounded">${query.query.substring(0, 50)}...</div>
                            <div class="text-sm text-gray-600 mt-1">
                                <div>平均时间: ${query.avg_time}</div>
                                <div>执行次数: ${query.execution_count}</div>
                                <div class="text-blue-600">建议: ${query.suggestion}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-3">优化建议</h4>
                <ul class="space-y-2">
                    ${suggestions.map(suggestion => `
                        <li class="text-sm text-gray-700 flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-1"></i>
                            ${suggestion}
                        </li>
                    `).join('')}
                </ul>
            </div>
        </div>
        
        <div class="text-xs text-gray-500">最后更新: ${data.last_updated}</div>
    `;
}

// API使用分析
function loadAPIAnalytics() {
    fetchData('api_analytics')
        .then(data => {
            updateAPIAnalyticsDisplay(data);
        })
        .catch(error => {
            console.error('API分析数据加载失败:', error);
            showError('API分析数据加载失败');
        });
}

// 更新API分析显示
function updateAPIAnalyticsDisplay(data) {
    const container = document.getElementById('api-analytics-container');
    if (!container) return;

    const stats = data.api_statistics;
    const endpoints = data.endpoint_analysis;
    const errors = data.error_analysis;

    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">${stats.total_requests.toLocaleString()}</div>
                <div class="text-sm text-gray-600">总请求数</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">${stats.success_rate}</div>
                <div class="text-sm text-gray-600">成功率</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">${stats.avg_response_time}</div>
                <div class="text-sm text-gray-600">平均响应时间</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">${stats.unique_clients.toLocaleString()}</div>
                <div class="text-sm text-gray-600">独立客户端</div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-3">热门端点</h4>
                <div class="space-y-3">
                    ${Object.entries(endpoints).map(([endpoint, endpointStats]) => `
                        <div class="border-l-4 border-blue-400 pl-3">
                            <div class="font-mono text-sm">${endpoint}</div>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>请求数: ${endpointStats.requests.toLocaleString()}</div>
                                <div>平均时间: ${endpointStats.avg_time}</div>
                                <div>错误率: ${endpointStats.error_rate}</div>
                                <div>热度: ${endpointStats.popularity}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-3">错误分析</h4>
                <div class="space-y-2">
                    ${Object.entries(errors.common_errors).map(([errorType, count]) => `
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">${errorType}:</span>
                            <span class="font-medium text-red-600">${count}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
        
        <div class="text-xs text-gray-500">最后更新: ${data.last_updated}</div>
    `;
}

// 缓存优化功能
function optimizeCache() {
    showLoading();
    fetchData('optimize_system')
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess('缓存优化完成');
                loadCacheManagement(); // 刷新缓存状态
            } else {
                showError('缓存优化失败: ' + (data.message || '未知错误'));
            }
        })
        .catch(error => {
            hideLoading();
            console.error('缓存优化失败:', error);
            showError('缓存优化失败');
        });
}

// 慢查询分析
function analyzeSlowQueries() {
    showLoading();
    fetchData('database_performance')
        .then(data => {
            hideLoading();
            if (data.query_analysis && data.query_analysis.top_slow_queries) {
                displaySlowQueryAnalysis(data.query_analysis.top_slow_queries);
            } else {
                showError('慢查询分析数据获取失败');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('慢查询分析失败:', error);
            showError('慢查询分析失败');
        });
}

// 显示慢查询分析结果
function displaySlowQueryAnalysis(slowQueries) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-4xl max-h-96 overflow-y-auto">
            <h3 class="text-lg font-bold mb-4">慢查询分析报告</h3>
            <div class="space-y-4">
                ${slowQueries.map((query, index) => `
                    <div class="border-l-4 border-red-400 pl-4 py-2">
                        <div class="font-semibold text-red-600">查询 #${index + 1}</div>
                        <div class="font-mono text-sm bg-gray-100 p-2 rounded my-2">${query.query}</div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>平均执行时间: <span class="font-medium">${query.avg_time}</span></div>
                            <div>执行次数: <span class="font-medium">${query.execution_count}</span></div>
                        </div>
                        <div class="text-blue-600 text-sm mt-1">
                            <i class="fas fa-lightbulb mr-1"></i>优化建议: ${query.suggestion}
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="mt-6 text-right">
                <button onclick="this.closest('.fixed').remove()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    关闭
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// API报告导出
function exportAPIReport() {
    showLoading();
    fetchData('api_analytics')
        .then(data => {
            hideLoading();
            const reportData = {
                timestamp: new Date().toISOString(),
                api_statistics: data.api_statistics,
                endpoint_analysis: data.endpoint_analysis,
                error_analysis: data.error_analysis,
                performance_metrics: data.performance_metrics
            };
            
            const blob = new Blob([JSON.stringify(reportData, null, 2)], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `api_report_${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showSuccess('API报告已导出');
        })
        .catch(error => {
            hideLoading();
            console.error('API报告导出失败:', error);
            showError('API报告导出失败');
        });
}

// API性能优化
function optimizeAPIPerformance() {
    showLoading();
    fetchData('optimize_system')
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess('API性能优化完成');
                loadAPIAnalytics(); // 刷新API分析
            } else {
                showError('API性能优化失败: ' + (data.message || '未知错误'));
            }
        })
        .catch(error => {
            hideLoading();
            console.error('API性能优化失败:', error);
            showError('API性能优化失败');
        });
}

// 增强的标签页切换功能
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
    }
    
    // 高亮选中的导航链接
    const selectedNavLink = document.querySelector(`[data-tab="${tabName}"]`);
    if (selectedNavLink) {
        selectedNavLink.classList.add('bg-white', 'bg-opacity-20');
    }
    
    // 更新页面标题
    const pageTitleElement = document.getElementById('page-title');
    if (pageTitleElement) {
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
            'business': '业务指标',
            'websocket': 'WebSocket监控',
            'chat': '聊天监控',
            'analytics': '分析报告',
            'cache': '缓存管理',
            'database-performance': '数据库性能',
            'api-analytics': 'API分析',
            'realtime': '实时监控'
        };
        pageTitleElement.textContent = titles[tabName] || '系统管理';
    }
    
    // 设置当前标签页
    currentTab = tabName;
    
    // 加载对应标签页的数据
    loadTabData(tabName);
}

// 增强的数据获取函数
function fetchData(action, params = {}) {
    const queryParams = new URLSearchParams({
        action: action,
        ...params
    });
    
    return fetch(`?${queryParams}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .catch(error => {
        console.error('Fetch error:', error);
        throw error;
    });
}

// 显示成功消息
function showSuccess(message) {
    showNotification(message, 'success');
}

// 显示错误消息
function showError(message) {
    showNotification(message, 'error');
}

// 显示通知
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
    
    const colors = {
        'success': 'bg-green-500 text-white',
        'error': 'bg-red-500 text-white',
        'warning': 'bg-yellow-500 text-white',
        'info': 'bg-blue-500 text-white'
    };
    
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-times-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    notification.className += ` ${colors[type] || colors.info}`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="${icons[type] || icons.info} mr-2"></i>
            <span>${message}</span>
            <button onclick="this.closest('.fixed').remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // 动画显示
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // 自动移除
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// 显示/隐藏加载指示器
function showLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.remove('hidden');
    }
}

function hideLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.add('hidden');
    }
}

// 页面可见性变化处理
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
        // 页面重新获得焦点时刷新数据
        if (['dashboard', 'websocket', 'chat', 'realtime'].includes(currentTab)) {
            refreshData();
        }
    }
});

// 窗口大小变化处理
window.addEventListener('resize', function() {
    // 重新调整图表大小等
    if (typeof updateRealTimeChart === 'function') {
        // 重新渲染图表
        setTimeout(() => {
            if (currentTab === 'realtime') {
                fetchData('realtime_stream').then(updateRealTimeChart).catch(console.error);
            }
        }, 100);
    }
});

console.log('AlingAI Pro 5.0 管理后台已加载完成');
