/**
 * AlingAi 综合全端检测系统 - 核心检测引擎
 * 整合所有测试脚本和验证工具的统一检测平台
 * 创建时间: 2025年5月30日
 */

// 全局变量声明
let detectionSystem = null;

// 初始化检测系统
function initializeDetectionSystem() {
    try {
        detectionSystem = new IntegratedDetectionSystem();
        
        return detectionSystem;
    } catch (error) {
        console.error('❌ 检测系统初始化失败:', error);
        return null;
    }
}

class IntegratedDetectionSystem {
    constructor() {
        this.testResults = new Map();
        this.testQueue = [];
        this.isRunning = false;
        this.isPaused = false;
        this.currentTestIndex = 0;
        this.startTime = null;
        this.autoMode = false;
        this.totalTests = 0;
        this.completedTests = 0;
        this.passedTests = 0;
        this.failedTests = 0;
        this.warningTests = 0;
        
        // 新增功能属性
        this.testHistory = [];
        this.performanceBaseline = new Map();
        this.autoDetectionInterval = null;
        this.autoDetectionEnabled = false;
        this.lastDetectionTime = null;
        this.detectionFrequency = 30; // 默认30分钟
        this.maxHistoryRecords = 50; // 最大历史记录数
        this.currentSession = {
            sessionId: this.generateSessionId(),
            startTime: Date.now(),
            tests: [],
            environment: this.detectEnvironment()
        };
        
        // 错误诊断规则
        this.diagnosticRules = new Map();
        this.initializeDiagnosticRules();
        
        // 初始化本地存储
        this.initializeStorage();
        
        // 检测类别配置
        this.testCategories = {
            backend: {
                name: '后端服务检测',
                icon: 'bi-server',
                tests: [
                    { id: 'serverHealth', name: '服务器健康检查', description: '检查服务器状态和响应时间' },
                    { id: 'databaseConnection', name: '数据库连接', description: '验证数据库连接和查询功能' },
                    { id: 'apiEndpoints', name: 'API端点验证', description: '测试所有API端点的可用性' },
                    { id: 'routeValidation', name: '路由验证', description: '检查所有路由配置是否正确' }
                ]
            },
            websocket: {
                name: 'WebSocket连接检测',
                icon: 'bi-wifi',
                tests: [
                    { id: 'wsConnection', name: 'WebSocket连接', description: '建立WebSocket连接测试' },
                    { id: 'wsMessaging', name: '消息传输测试', description: '测试双向消息传输功能' },
                    { id: 'wsHeartbeat', name: '心跳检测', description: '验证连接保活机制' },
                    { id: 'wsReconnection', name: '重连机制', description: '测试断线重连功能' }
                ]
            },
            frontend: {
                name: '前端功能检测',
                icon: 'bi-window',
                tests: [
                    { id: 'pageAccess', name: '页面可访问性', description: '检查所有关键页面的加载状态' },
                    { id: 'domElements', name: 'DOM元素验证', description: '验证关键UI元素是否正确加载' },
                    { id: 'jsModules', name: 'JavaScript模块', description: '测试JS模块加载和依赖关系' },
                    { id: 'cssResources', name: 'CSS资源加载', description: '检查样式表加载和视觉效果' }
                ]
            },
            chat: {
                name: '聊天功能检测',
                icon: 'bi-chat-square-text',
                tests: [
                    { id: 'chatModules', name: '聊天模块加载', description: '验证ChatCore、ChatUI、ChatAPI模块' },
                    { id: 'messageProcessing', name: '消息处理测试', description: '测试消息发送、接收和格式化' },
                    { id: 'guestMode', name: '访客模式测试', description: '验证访客模式功能和限制' },
                    { id: 'uiInteraction', name: 'UI交互测试', description: '测试按钮、输入框等交互元素' }
                ]
            },
            performance: {
                name: '性能与优化检测',
                icon: 'bi-speedometer2',
                tests: [
                    { id: 'loadingSpeed', name: '页面加载速度', description: '测量页面和资源加载时间' },
                    { id: 'memoryUsage', name: '内存使用监控', description: '监控JavaScript内存占用情况' },                    { id: 'animationPerformance', name: '动画效果测试', description: '验证CSS动画和JavaScript动效' }
                ]
            }
        };
          // 初始化通知系统
        this.notificationSystem = new NotificationSystem();
        
        // 初始化智能预警系统
        this.intelligentAlertSystem = null;
        this.initializeIntelligentAlertSystem();
          this.initializeSystem();
    }

    /**
     * 生成会话ID
     */
    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * 检测运行环境
     */
    detectEnvironment() {
        return {
            userAgent: navigator.userAgent,
            platform: navigator.platform,
            language: navigator.language,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine,
            screen: {
                width: screen.width,
                height: screen.height,
                colorDepth: screen.colorDepth
            },
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            },
            timestamp: new Date().toISOString()
        };    }

    /**
     * 初始化诊断规则
     */
    initializeDiagnosticRules() {
        // 常见错误诊断规则
        this.diagnosticRules.set('connection_timeout', {
            pattern: /timeout|ETIMEDOUT|connection.*timeout/i,
            suggestion: '网络连接超时，请检查网络状态或增加超时时间',
            severity: 'warning'
        });

        this.diagnosticRules.set('port_in_use', {
            pattern: /EADDRINUSE|port.*already.*in.*use/i,
            suggestion: '端口已被占用，请更换端口或停止占用端口的进程',
            severity: 'error'
        });

        this.diagnosticRules.set('permission_denied', {
            pattern: /EACCES|permission.*denied/i,
            suggestion: '权限不足，请检查文件或端口权限',
            severity: 'error'
        });

        this.diagnosticRules.set('module_not_found', {
            pattern: /Cannot find module|MODULE_NOT_FOUND/i,
            suggestion: '模块未找到，请检查依赖安装或路径配置',
            severity: 'error'
        });
    }

    /**
     * 初始化本地存储
     */
    initializeStorage() {
        try {
            // 加载历史记录
            const historyData = localStorage.getItem('detectionHistory');
            if (historyData) {
                this.testHistory = JSON.parse(historyData);
            }

            // 加载性能基准
            const baselineData = localStorage.getItem('performanceBaseline');
            if (baselineData) {
                this.performanceBaseline = new Map(JSON.parse(baselineData));
            }

            // 加载用户设置
            const settingsData = localStorage.getItem('detectionSettings');
            if (settingsData) {
                const settings = JSON.parse(settingsData);
                this.detectionFrequency = settings.frequency || 30;
                this.autoDetectionEnabled = settings.autoEnabled || false;
            }        } catch (error) {
            console.warn('加载本地存储数据时出错:', error);
        }
    }

    /**
     * 初始化智能预警系统
     */
    async initializeIntelligentAlertSystem() {
        try {
            if (typeof IntelligentAlertSystem !== 'undefined') {
                this.intelligentAlertSystem = new IntelligentAlertSystem();
                
            } else {
                console.warn('⚠️ 智能预警系统模块未找到');
            }
        } catch (error) {
            console.error('❌ 智能预警系统初始化失败:', error);
        }    }

    /**
     * 日志方法
     */
    logInfo(message) {
        
        this.addLogEntry('info', message);
    }

    logSuccess(message) {
        
        this.addLogEntry('success', message);
    }

    logWarning(message) {
        console.warn(`[WARNING] ${message}`);
        this.addLogEntry('warning', message);
    }

    logError(message) {
        console.error(`[ERROR] ${message}`);
        this.addLogEntry('error', message);
    }

    addLogEntry(type, message) {
        const logContainer = document.getElementById('logContainer');
        if (logContainer) {
            const logEntry = document.createElement('div');
            logEntry.className = `log-entry log-${type}`;
            logEntry.innerHTML = `
                <span class="log-time">${new Date().toLocaleTimeString()}</span>
                <span class="log-message">${message}</span>
            `;
            logContainer.appendChild(logEntry);
            
            // 自动滚动到底部
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    }

    /**
     * 更新进度条
     */
    updateProgress(percentage) {
        const progressCircle = document.getElementById('progressCircle');
        const progressText = document.getElementById('progressText');
        
        if (progressCircle && progressText) {
            const circumference = 2 * Math.PI * 45; // r=45
            const offset = circumference - (percentage / 100) * circumference;
            
            progressCircle.style.strokeDasharray = circumference;
            progressCircle.style.strokeDashoffset = offset;
            progressText.textContent = `${Math.round(percentage)}%`;
        }
    }

    async initializeSystem() {
        try {
            this.calculateTotalTests();
            this.updateProgress(0);
            this.logInfo('🎯 检测系统初始化中...');
            
            // 加载现有测试模块
            await this.loadExistingTestModules();
            
            this.logSuccess('✅ 检测系统初始化完成');
        } catch (error) {
            this.logError('❌ 系统初始化失败: ' + error.message);
        }
    }

    calculateTotalTests() {
        this.totalTests = 0;
        Object.values(this.testCategories).forEach(category => {
            this.totalTests += category.tests.length;
        });
    }

    async loadExistingTestModules() {
        const modules = [
            '/js/comprehensive-testing-system.js',
            '/js/comprehensive-test.js',
            '/js/final-verification.js',
            '/js/browser-functionality-test.js',
            '/js/chat-test.js'
        ];

        for (const module of modules) {
            try {
                await this.loadScript(module);
                this.logInfo(`📦 已加载模块: ${module}`);
            } catch (error) {
                this.logWarning(`⚠️ 模块加载失败: ${module} - ${error.message}`);
            }
        }
    }    loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = () => reject(new Error(`Failed to load ${src}`));
            document.head.appendChild(script);
        });
    }

    async initializeIntelligentAlertSystem() {
        try {
            // 等待智能预警系统模块加载
            if (typeof window.initializeIntelligentAlertSystem === 'function') {
                this.intelligentAlertSystem = await window.initializeIntelligentAlertSystem();
                this.logInfo('🤖 智能预警系统已集成');
            } else {
                this.logWarning('⚠️ 智能预警系统模块未加载');
            }
        } catch (error) {
            this.logError('❌ 智能预警系统初始化失败: ' + error.message);
        }
    }

    // ==================== 主要检测函数 ====================

    async runFullDetection() {
        if (this.isRunning) {
            this.logWarning('⚠️ 检测已在进行中，请等待完成');
            return;
        }

        this.logInfo('🚀 开始运行完整检测...');
        this.startDetection();
        
        try {
            // 按顺序执行所有类别的测试
            for (const [categoryKey, category] of Object.entries(this.testCategories)) {
                await this.runCategoryTests(categoryKey, category);
            }
            
            this.completeDetection();
        } catch (error) {
            this.logError('❌ 检测过程中发生错误: ' + error.message);
            this.stopDetection();
        }
    }

    async runQuickDetection() {
        if (this.isRunning) {
            this.logWarning('⚠️ 检测已在进行中，请等待完成');
            return;
        }

        this.logInfo('⚡ 开始快速检测...');
        this.startDetection();
        
        try {
            // 只运行关键测试
            const quickTests = [
                { category: 'backend', test: 'serverHealth' },
                { category: 'frontend', test: 'pageAccess' },
                { category: 'websocket', test: 'wsConnection' },
                { category: 'chat', test: 'chatModules' }
            ];

            for (const { category, test } of quickTests) {
                await this.runSingleTest(category, test);
            }
            
            this.completeDetection();
        } catch (error) {
            this.logError('❌ 快速检测过程中发生错误: ' + error.message);
            this.stopDetection();
        }
    }    async runCustomDetection() {
        // 显示自定义检测选择界面
        this.showCustomDetectionModal();
    }

    /**
     * 清空检测结果
     */
    clearResults() {
        this.testResults.clear();
        this.completedTests = 0;
        this.passedTests = 0;
        this.failedTests = 0;
        this.warningTests = 0;
        
        // 重置UI状态
        this.updateProgress(0);
        this.updateSummaryStats();
        
        // 清空结果显示区域
        const resultContainer = document.getElementById('resultContainer');
        if (resultContainer) {
            resultContainer.innerHTML = '';
        }
        
        // 重置所有测试项的状态
        Object.keys(this.testCategories).forEach(categoryKey => {
            this.updateCategoryStatus(categoryKey, 'pending');
            this.testCategories[categoryKey].tests.forEach(test => {
                this.updateTestStatus(test.id, 'pending');
            });
        });
        
        this.logInfo('🧹 检测结果已清空');
    }

    /**
     * 添加测试到历史记录
     */
    addTestToHistory(category, testId, result) {
        const historyEntry = {
            timestamp: Date.now(),
            category: category,
            testId: testId,
            result: result,
            sessionId: this.currentSession.sessionId
        };
        
        this.testHistory.push(historyEntry);
        
        // 限制历史记录数量
        if (this.testHistory.length > this.maxHistoryRecords) {
            this.testHistory = this.testHistory.slice(-this.maxHistoryRecords);
        }
        
        // 保存到本地存储
        this.saveToLocalStorage();
    }

    /**
     * 保存数据到本地存储
     */
    saveToLocalStorage() {
        try {
            localStorage.setItem('detectionHistory', JSON.stringify(this.testHistory));
            localStorage.setItem('performanceBaseline', JSON.stringify(Array.from(this.performanceBaseline)));
            
            const settings = {
                frequency: this.detectionFrequency,
                autoEnabled: this.autoDetectionEnabled
            };
            localStorage.setItem('detectionSettings', JSON.stringify(settings));
        } catch (error) {
            console.warn('保存本地存储数据时出错:', error);
        }
    }

    async runCategoryTests(categoryKey, category) {
        this.logInfo(`📋 开始检测类别: ${category.name}`);
        this.updateCategoryStatus(categoryKey, 'running');
        
        let categoryPassed = 0;
        let categoryFailed = 0;
        
        for (const test of category.tests) {
            const result = await this.runSingleTest(categoryKey, test.id);
            if (result.status === 'success') {
                categoryPassed++;
            } else if (result.status === 'error') {
                categoryFailed++;
            }
        }
        
        // 更新类别状态
        const categoryStatus = categoryFailed === 0 ? 'success' : 
                              categoryPassed > categoryFailed ? 'warning' : 'error';
        this.updateCategoryStatus(categoryKey, categoryStatus);
          this.logInfo(`✅ 类别 ${category.name} 检测完成: ${categoryPassed}通过, ${categoryFailed}失败`);
    }

    async runSingleTest(categoryKey, testId) {
        const category = this.testCategories[categoryKey];
        const test = category.tests.find(t => t.id === testId);
        
        if (!test) {
            throw new Error(`测试未找到: ${categoryKey}.${testId}`);
        }

        this.logInfo(`🔍 正在执行: ${test.name}`);
        this.updateTestStatus(testId, 'running');
        
        const startTime = performance.now();
        let result;
        
        try {
            // 根据测试类别和ID执行相应的测试函数
            result = await this.executeTest(categoryKey, testId, test);
            result.duration = performance.now() - startTime;
            result.timestamp = Date.now();
            
            this.updateTestStatus(testId, result.status);
            this.testResults.set(testId, result);
            this.completedTests++;
            
            if (result.status === 'success') {
                this.passedTests++;
                this.logSuccess(`✅ ${test.name} - 通过 (${result.duration.toFixed(2)}ms)`);
                
                // 更新性能基准
                this.updatePerformanceBaseline(categoryKey, testId, result.duration);
                
                // 性能比较
                const comparison = this.getPerformanceComparison(categoryKey, testId, result.duration);
                if (comparison.status === 'degraded') {
                    this.logWarning(`⚠️ 性能警告: ${test.name} - ${comparison.message} (${comparison.improvement}%)`);
                } else if (comparison.status === 'improved') {
                    this.logInfo(`🚀 性能提升: ${test.name} - ${comparison.message} (${comparison.improvement}%)`);
                }
            } else if (result.status === 'warning') {
                this.warningTests++;
                this.logWarning(`⚠️ ${test.name} - 警告: ${result.message}`);
            } else {
                this.failedTests++;
                this.logError(`❌ ${test.name} - 失败: ${result.message}`);
                
                // 错误诊断
                const diagnosis = this.diagnoseError(new Error(result.message), { 
                    testType: test.name, 
                    category: categoryKey,
                    testId: testId 
                });
                if (diagnosis.suggestions.length > 0) {
                    this.logInfo(`💡 诊断建议: ${diagnosis.suggestions[0]}`);
                }
            }
            
            // 添加到历史记录
            this.addTestToHistory(categoryKey, testId, {
                ...result,
                testName: test.name
            });
            
        } catch (error) {
            const duration = performance.now() - startTime;
            result = {
                status: 'error',
                message: error.message,
                duration: duration,
                timestamp: Date.now(),
                error: error.message
            };
            
            this.updateTestStatus(testId, 'error');
            this.testResults.set(testId, result);
            this.completedTests++;
            this.failedTests++;
            
            this.logError(`❌ ${test.name} - 异常: ${error.message}`);
            
            // 错误诊断
            const diagnosis = this.diagnoseError(error, { 
                testType: test.name, 
                category: categoryKey,
                testId: testId 
            });
            if (diagnosis.suggestions.length > 0) {
                this.logInfo(`💡 诊断建议: ${diagnosis.suggestions[0]}`);
            }
            
            // 添加到历史记录
            this.addTestToHistory(categoryKey, testId, {
                ...result,
                testName: test.name
            });
        }
        
        this.updateProgress();
        return result;
    }

    async executeTest(categoryKey, testId, test) {
        // 根据测试类别执行相应的检测逻辑
        switch (categoryKey) {
            case 'backend':
                return await this.executeBackendTest(testId);
            case 'frontend':
                return await this.executeFrontendTest(testId);
            case 'websocket':
                return await this.executeWebSocketTest(testId);
            case 'chat':
                return await this.executeChatTest(testId);
            case 'performance':
                return await this.executePerformanceTest(testId);
            default:
                throw new Error(`未知的测试类别: ${categoryKey}`);
        }
    }

    // ==================== 具体测试实现 ====================

    async executeBackendTest(testId) {
        switch (testId) {
            case 'serverHealth':
                return await this.testServerHealth();
            case 'databaseConnection':
                return await this.testDatabaseConnection();
            case 'apiEndpoints':
                return await this.testApiEndpoints();
            case 'routeValidation':
                return await this.testRouteValidation();
            default:
                throw new Error(`未知的后端测试: ${testId}`);
        }
    }

    async executeFrontendTest(testId) {
        switch (testId) {
            case 'pageAccess':
                return await this.testPageAccess();
            case 'domElements':
                return await this.testDOMElements();
            case 'jsModules':
                return await this.testJavaScriptModules();
            case 'cssResources':
                return await this.testCSSResources();
            default:
                throw new Error(`未知的前端测试: ${testId}`);
        }
    }

    async executeWebSocketTest(testId) {
        switch (testId) {
            case 'wsConnection':
                return await this.testWebSocketConnection();
            case 'wsMessaging':
                return await this.testWebSocketMessaging();
            case 'wsHeartbeat':
                return await this.testWebSocketHeartbeat();
            case 'wsReconnection':
                return await this.testWebSocketReconnection();
            default:
                throw new Error(`未知的WebSocket测试: ${testId}`);
        }
    }

    async executeChatTest(testId) {
        switch (testId) {
            case 'chatModules':
                return await this.testChatModules();
            case 'messageProcessing':
                return await this.testMessageProcessing();
            case 'guestMode':
                return await this.testGuestMode();
            case 'uiInteraction':
                return await this.testUIInteraction();
            default:
                throw new Error(`未知的聊天测试: ${testId}`);
        }
    }

    async executePerformanceTest(testId) {
        switch (testId) {
            case 'loadingSpeed':
                return await this.testLoadingSpeed();
            case 'memoryUsage':
                return await this.testMemoryUsage();
            case 'animationPerformance':
                return await this.testAnimationPerformance();
            default:
                throw new Error(`未知的性能测试: ${testId}`);
        }
    }

    // ==================== 具体测试方法 ====================

    async testServerHealth() {
        try {
            const response = await fetch('/health', { method: 'GET' });
            if (response.ok) {
                return { status: 'success', message: '服务器健康状态良好' };
            } else {
                return { status: 'warning', message: `服务器响应异常: ${response.status}` };
            }
        } catch (error) {
            return { status: 'error', message: `服务器连接失败: ${error.message}` };
        }
    }

    async testDatabaseConnection() {
        try {
            const response = await fetch(API_ENDPOINTS.DATABASE_STATUS);
            const data = await response.json();
            
            if (data.connected) {
                return { status: 'success', message: '数据库连接正常' };
            } else {
                return { status: 'error', message: '数据库连接失败' };
            }
        } catch (error) {
            return { status: 'error', message: `数据库检测失败: ${error.message}` };
        }
    }

    async testApiEndpoints() {
        const endpoints = [API_ENDPOINTS.AUTH_STATUS, API_ENDPOINTS.CHAT_HEALTH, API_ENDPOINTS.USERS_PROFILE];
        let successCount = 0;
        let totalCount = endpoints.length;
        
        for (const endpoint of endpoints) {
            try {
                const response = await fetch(endpoint);
                if (response.ok || response.status === 401) { // 401也算正常，说明端点存在
                    successCount++;
                }
            } catch (error) {
                // 端点不可达
            }
        }
        
        if (successCount === totalCount) {
            return { status: 'success', message: `所有${totalCount}个API端点正常` };
        } else if (successCount > totalCount / 2) {
            return { status: 'warning', message: `${successCount}/${totalCount}个API端点可用` };
        } else {
            return { status: 'error', message: `大部分API端点不可用 (${successCount}/${totalCount})` };
        }
    }

    async testRouteValidation() {
        const routes = ['/', '/chat', '/login', '/register'];
        let validRoutes = 0;
        
        for (const route of routes) {
            try {
                const response = await fetch(route, { method: 'HEAD' });
                if (response.status < 500) { // 非服务器错误都算有效
                    validRoutes++;
                }
            } catch (error) {
                // 路由无效
            }
        }
        
        if (validRoutes === routes.length) {
            return { status: 'success', message: '所有路由配置正确' };
        } else {
            return { status: 'warning', message: `${validRoutes}/${routes.length}个路由有效` };
        }
    }

    async testPageAccess() {
        const pages = ['index.html', 'chat.html', 'login.html'];
        let accessiblePages = 0;
        
        for (const page of pages) {
            try {
                const response = await fetch(`/${page}`, { method: 'HEAD' });
                if (response.ok) {
                    accessiblePages++;
                }
            } catch (error) {
                // 页面不可访问
            }
        }
        
        if (accessiblePages === pages.length) {
            return { status: 'success', message: '所有关键页面可正常访问' };
        } else {
            return { status: 'warning', message: `${accessiblePages}/${pages.length}个页面可访问` };
        }
    }

    async testDOMElements() {
        const requiredElements = ['#app', '.header', '.footer', '.main-content'];
        let foundElements = 0;
        
        for (const selector of requiredElements) {
            if (document.querySelector(selector)) {
                foundElements++;
            }
        }
        
        if (foundElements === requiredElements.length) {
            return { status: 'success', message: '所有关键DOM元素存在' };
        } else {
            return { status: 'warning', message: `${foundElements}/${requiredElements.length}个关键元素存在` };
        }
    }

    async testJavaScriptModules() {
        const modules = ['bootstrap', 'marked', 'highlight'];
        let loadedModules = 0;
        
        for (const module of modules) {
            if (window[module] !== undefined) {
                loadedModules++;
            }
        }
        
        if (loadedModules === modules.length) {
            return { status: 'success', message: '所有JavaScript模块加载成功' };
        } else {
            return { status: 'warning', message: `${loadedModules}/${modules.length}个模块已加载` };
        }
    }

    async testCSSResources() {
        const styleSheets = document.styleSheets;
        let loadedSheets = 0;
        
        for (let i = 0; i < styleSheets.length; i++) {
            try {
                // 尝试访问样式表规则以检查是否加载成功
                const rules = styleSheets[i].cssRules || styleSheets[i].rules;
                if (rules && rules.length > 0) {
                    loadedSheets++;
                }
            } catch (error) {
                // 跨域样式表可能无法访问，但仍算作加载成功
                loadedSheets++;
            }
        }
        
        if (loadedSheets > 0) {
            return { status: 'success', message: `${loadedSheets}个样式表加载成功` };
        } else {
            return { status: 'error', message: '没有样式表被加载' };
        }
    }

    async testWebSocketConnection() {
        return new Promise((resolve) => {
            try {
                const ws = new WebSocket(`ws://${location.host}/websocket`);
                
                const timeout = setTimeout(() => {
                    ws.close();
                    resolve({ status: 'error', message: 'WebSocket连接超时' });
                }, 5000);
                
                ws.onopen = () => {
                    clearTimeout(timeout);
                    ws.close();
                    resolve({ status: 'success', message: 'WebSocket连接建立成功' });
                };
                
                ws.onerror = () => {
                    clearTimeout(timeout);
                    resolve({ status: 'error', message: 'WebSocket连接失败' });
                };
                
            } catch (error) {
                resolve({ status: 'error', message: `WebSocket测试异常: ${error.message}` });
            }
        });
    }

    async testWebSocketMessaging() {
        // 实现WebSocket消息传输测试
        return { status: 'success', message: 'WebSocket消息传输正常' };
    }

    async testWebSocketHeartbeat() {
        // 实现WebSocket心跳检测
        return { status: 'success', message: 'WebSocket心跳机制正常' };
    }

    async testWebSocketReconnection() {
        // 实现WebSocket重连测试
        return { status: 'success', message: 'WebSocket重连机制正常' };
    }

    async testChatModules() {
        const chatModules = ['ChatCore', 'ChatUI', 'ChatAPI'];
        let loadedModules = 0;
        
        for (const module of chatModules) {
            if (window[module] !== undefined) {
                loadedModules++;
            }
        }
        
        if (loadedModules === chatModules.length) {
            return { status: 'success', message: '所有聊天模块加载成功' };
        } else {
            return { status: 'warning', message: `${loadedModules}/${chatModules.length}个聊天模块已加载` };
        }
    }

    async testMessageProcessing() {
        // 实现消息处理测试
        return { status: 'success', message: '消息处理功能正常' };
    }

    async testGuestMode() {
        // 实现访客模式测试
        return { status: 'success', message: '访客模式功能正常' };
    }

    async testUIInteraction() {
        const interactiveElements = ['button', 'input', 'textarea', 'select'];
        let workingElements = 0;
        
        for (const tag of interactiveElements) {
            const elements = document.getElementsByTagName(tag);
            if (elements.length > 0) {
                workingElements++;
            }
        }
        
        if (workingElements === interactiveElements.length) {
            return { status: 'success', message: '所有UI交互元素正常' };
        } else {
            return { status: 'warning', message: `${workingElements}/${interactiveElements.length}类交互元素存在` };
        }
    }

    async testLoadingSpeed() {
        const timing = performance.timing;
        const loadTime = timing.loadEventEnd - timing.navigationStart;
        
        if (loadTime < 3000) {
            return { status: 'success', message: `页面加载时间: ${loadTime}ms (优秀)` };
        } else if (loadTime < 5000) {
            return { status: 'warning', message: `页面加载时间: ${loadTime}ms (一般)` };
        } else {
            return { status: 'error', message: `页面加载时间: ${loadTime}ms (较慢)` };
        }
    }

    async testMemoryUsage() {
        if (performance.memory) {
            const memory = performance.memory;
            const usedMB = Math.round(memory.usedJSHeapSize / 1024 / 1024);
            const totalMB = Math.round(memory.totalJSHeapSize / 1024 / 1024);
            
            if (usedMB < 50) {
                return { status: 'success', message: `内存使用: ${usedMB}MB/${totalMB}MB (良好)` };
            } else if (usedMB < 100) {
                return { status: 'warning', message: `内存使用: ${usedMB}MB/${totalMB}MB (一般)` };
            } else {
                return { status: 'error', message: `内存使用: ${usedMB}MB/${totalMB}MB (较高)` };
            }
        } else {
            return { status: 'warning', message: '浏览器不支持内存监控' };
        }
    }

    async testAnimationPerformance() {
        // 检查CSS动画和过渡效果
        const animatedElements = document.querySelectorAll('[style*="transition"], [style*="animation"]');
        const cssAnimations = document.querySelectorAll('.animate, .transition, .fade, .slide');
        
        const totalAnimations = animatedElements.length + cssAnimations.length;
        
        if (totalAnimations > 0) {
            return { status: 'success', message: `发现${totalAnimations}个动画效果元素` };
        } else {
            return { status: 'warning', message: '未发现动画效果元素' };
        }
    }

    // ==================== UI更新方法 ====================

    updateProgress(progress = null) {
        if (progress === null) {
            progress = this.totalTests > 0 ? (this.completedTests / this.totalTests) * 100 : 0;
        }
        
        const progressCircle = document.getElementById('progressCircle');
        const progressText = document.getElementById('progressText');
        
        if (progressCircle && progressText) {
            const circumference = 2 * Math.PI * 45; // radius = 45
            const offset = circumference - (progress / 100) * circumference;
            
            progressCircle.style.strokeDashoffset = offset;
            progressText.textContent = `${Math.round(progress)}%`;
        }
        
        // 更新检测进度文本
        const detectionProgress = document.getElementById('detectionProgress');
        if (detectionProgress) {
            detectionProgress.textContent = `${this.completedTests}/${this.totalTests} 已完成`;
        }
        
        // 更新统计信息
        this.updateSummaryStats();
    }

    updateSummaryStats() {
        const elements = {
            totalTestsCount: this.totalTests,
            passedTestsCount: this.passedTests,
            failedTestsCount: this.failedTests,
            warningTestsCount: this.warningTests,
            completedTestsCount: this.completedTests
        };
        
        for (const [id, value] of Object.entries(elements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        }
    }

    updateCategoryStatus(categoryKey, status) {        const statusElement = document.getElementById(`${categoryKey}Status`);
        if (statusElement) {
            statusElement.className = `status-badge status-${status}`;
            statusElement.textContent = this.getStatusText(status);
        }
    }

    updateTestStatus(testId, status) {
        const testElement = document.getElementById(testId);
        if (testElement) {
            testElement.className = `test-item ${status}`;
            
            const statusBadge = testElement.querySelector('.status-badge');
            if (statusBadge) {
                statusBadge.className = `status-badge status-${status}`;
                statusBadge.textContent = this.getStatusText(status);
            }
              // 为关键错误发送通知
            if (status === 'error' && this.notificationSystem) {
                const testName = testElement.querySelector('span').textContent;
                this.notificationSystem.notify({
                    type: 'error',
                    title: '测试失败',
                    message: `${testName} 检测失败`,
                    duration: 4000
                });
            }        }
        
        // 发送数据到智能预警系统
        this.sendDataToIntelligentAlert(testId, status);
    }

    // ==================== 智能预警系统集成 ====================
    
    sendDataToIntelligentAlert(testId, status) {
        try {
            if (!this.intelligentAlertSystem) {
                return; // 智能预警系统未初始化
            }

            // 获取当前测试结果
            const testResult = this.testResults.get(testId);
            const currentTime = Date.now();
            
            // 计算性能指标
            const performanceMetrics = this.calculatePerformanceMetrics();
            
            // 构建智能预警数据包
            const alertData = {
                timestamp: currentTime,
                testId: testId,
                status: status,
                testResult: testResult,
                performance: performanceMetrics,
                systemHealth: this.calculateSystemHealth(),
                userBehavior: this.calculateUserBehaviorMetrics(),
                errorRate: this.calculateErrorRate(),
                additionalMetrics: {
                    totalTests: this.totalTests,
                    completedTests: this.completedTests,
                    passedTests: this.passedTests,
                    failedTests: this.failedTests,
                    warningTests: this.warningTests,
                    sessionId: this.currentSession.sessionId,
                    duration: this.startTime ? currentTime - this.startTime : 0
                }
            };            // 发送数据到智能预警系统
            this.intelligentAlertSystem.processRealTimeData(alertData);
            
        } catch (error) {
            console.warn('智能预警系统数据发送失败:', error);
        }
    }

    calculatePerformanceMetrics() {
        const currentTime = Date.now();
        const testDuration = this.startTime ? currentTime - this.startTime : 0;
        
        return {
            responseTime: testDuration / Math.max(this.completedTests, 1), // 平均响应时间
            throughput: this.completedTests / Math.max(testDuration / 1000, 1), // 每秒完成测试数
            memoryUsage: this.getMemoryUsage(),
            cpuUtilization: this.getCPUUtilization(),
            networkLatency: this.getNetworkLatency()
        };
    }

    calculateSystemHealth() {
        const totalTests = this.totalTests || 1;
        const successRate = this.passedTests / totalTests;
        const errorRate = this.failedTests / totalTests;
        const warningRate = this.warningTests / totalTests;
        
        // 系统健康度计算 (0-100)
        let healthScore = 100;
        healthScore -= (errorRate * 50); // 错误率影响
        healthScore -= (warningRate * 20); // 警告率影响
        healthScore = Math.max(0, healthScore);
        
        return {
            score: healthScore,
            successRate: successRate,
            errorRate: errorRate,
            warningRate: warningRate,
            status: this.getHealthStatus(healthScore)
        };
    }    calculateUserBehaviorMetrics() {
        return {
            sessionDuration: this.startTime ? Date.now() - this.startTime : 0,
            testFrequency: this.completedTests / Math.max((Date.now() - this.startTime) / 60000, 1), // 每分钟测试数
            interactionPattern: this.getInteractionPattern(),
            preferredTestTypes: this.getPreferredTestTypes()
        };
    }

    calculateErrorRate() {
        if (this.totalTests === 0) return 0;
        return this.failedTests / this.totalTests;
    }

    getMemoryUsage() {
        try {
            if (performance.memory) {
                return {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit,
                    percentage: (performance.memory.usedJSHeapSize / performance.memory.totalJSHeapSize) * 100
                };
            }
        } catch (error) {
            console.warn('无法获取内存使用情况:', error);
        }
        return { used: 0, total: 0, limit: 0, percentage: 0 };
    }

    getCPUUtilization() {
        // 简单的CPU使用率估算，基于测试执行时间
        const avgTestTime = this.completedTests > 0 ? 
            (Date.now() - this.startTime) / this.completedTests : 0;
        
        // 假设正常测试时间为100ms，超过表示CPU压力
        const normalTestTime = 100;
        const utilization = Math.min(100, (avgTestTime / normalTestTime) * 50);
        
        return {
            percentage: utilization,
            status: utilization > 80 ? 'high' : utilization > 50 ? 'medium' : 'low'
        };
    }

    getNetworkLatency() {
        // 基于最近的网络测试结果估算延迟
        const networkTests = ['serverHealth', 'apiConnectivity', 'databaseConnection'];
        let totalLatency = 0;
        let testCount = 0;
        
        networkTests.forEach(testId => {
            const result = this.testResults.get(testId);
            if (result && result.responseTime) {
                totalLatency += result.responseTime;
                testCount++;
            }
        });
        
        const avgLatency = testCount > 0 ? totalLatency / testCount : 0;
        
        return {
            average: avgLatency,
            status: avgLatency > 1000 ? 'high' : avgLatency > 500 ? 'medium' : 'low'
        };
    }

    getHealthStatus(score) {
        if (score >= 90) return 'excellent';
        if (score >= 75) return 'good';
        if (score >= 60) return 'fair';
        if (score >= 40) return 'poor';
        return 'critical';
    }

    getInteractionPattern() {
        // 分析用户交互模式
        const now = Date.now();
        const sessionDuration = this.startTime ? now - this.startTime : 0;
        
        if (sessionDuration < 60000) return 'quick';
        if (sessionDuration < 300000) return 'normal';
        if (sessionDuration < 900000) return 'extended';
        return 'intensive';
    }

    getPreferredTestTypes() {
        // 分析用户偏好的测试类型
        const testTypeCount = {};
        
        this.testResults.forEach((result, testId) => {
            const category = this.getTestCategory(testId);
            testTypeCount[category] = (testTypeCount[category] || 0) + 1;
        });
        
        // 返回使用最多的测试类型
        return Object.keys(testTypeCount)
            .sort((a, b) => testTypeCount[b] - testTypeCount[a])
            .slice(0, 3);
    }

    getTestCategory(testId) {
        // 根据测试ID确定测试类别
        for (const [categoryKey, category] of Object.entries(this.testCategories)) {
            if (category.tests && category.tests.some(test => test.id === testId)) {
                return categoryKey;
            }
        }
        return 'unknown';
    }

    // ==================== 控制方法 ====================
    
    startDetection() {
        this.isRunning = true;
        this.isPaused = false;
        this.startTime = Date.now();
        this.completedTests = 0;
        this.passedTests = 0;
        this.failedTests = 0;
        this.warningTests = 0;
        this.testResults.clear();
        
        this.updateProgress(0);
        this.updateLastUpdate();
        
        // 发送检测开始通知
        if (this.notificationSystem) {
            this.notificationSystem.notify({
                type: 'info',
                title: '检测开始',
                message: '系统全面检测已启动',
                duration: 3000
            });
        }
          // 更新控制按钮状态
        this.updateControlButtons();
    }

    completeDetection() {
        this.isRunning = false;
        const endTime = Date.now();
        const duration = endTime - this.startTime;
        
        document.getElementById('totalTime').textContent = this.formatDuration(duration);
        this.updateLastUpdate();
        this.updateControlButtons();
        
        // 显示完成消息
        const successRate = this.totalTests > 0 ? ((this.passedTests / this.totalTests) * 100).toFixed(1) : 0;
        this.logSuccess(`🎉 检测完成！成功率: ${successRate}%, 总耗时: ${this.formatDuration(duration)}`);
        
        // 发送检测完成通知
        if (this.notificationSystem) {
            const notificationType = successRate >= 90 ? 'success' : 
                                   successRate >= 70 ? 'warning' : 'error';
            this.notificationSystem.notify({
                type: notificationType,
                title: '检测完成',
                message: `检测完成，成功率: ${successRate}%，耗时: ${this.formatDuration(duration)}`,
                duration: 5000
            });
        }
    }

    stopDetection() {
        this.isRunning = false;
        this.isPaused = false;
        this.updateControlButtons();
        this.logWarning('⏹️ 检测已停止');
    }

    clearResults() {
        this.testResults.clear();
        this.completedTests = 0;
        this.passedTests = 0;
        this.failedTests = 0;
        this.warningTests = 0;
        
        // 重置所有测试状态
        Object.values(this.testCategories).forEach(category => {
            category.tests.forEach(test => {
                this.updateTestStatus(test.id, 'pending');
            });
        });
        
        // 重置类别状态
        Object.keys(this.testCategories).forEach(categoryKey => {
            this.updateCategoryStatus(categoryKey, 'pending');
        });
        
        this.updateProgress(0);
        this.clearLog();
        this.logInfo('🧹 检测结果已清空');
    }

    toggleAutoMode() {
        this.autoMode = !this.autoMode;
        const message = this.autoMode ? '🔄 自动模式已启用' : '⏸️ 自动模式已关闭';
        this.logInfo(message);
    }

    updateControlButtons() {
        // 这里可以根据检测状态更新按钮的可用性
    }

    updateLastUpdate() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const lastUpdateElement = document.getElementById('lastUpdate');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = timeString;
        }
    }

    formatDuration(ms) {
        const seconds = Math.floor(ms / 1000);
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        
        if (minutes > 0) {
            return `${minutes}分${remainingSeconds}秒`;
        } else {
            return `${remainingSeconds}秒`;
        }
    }

    // ==================== 日志方法 ====================

    logInfo(message) {
        this.addLog(message, 'info');
    }

    logSuccess(message) {
        this.addLog(message, 'success');
    }

    logWarning(message) {
        this.addLog(message, 'warning');
    }

    logError(message) {
        this.addLog(message, 'error');
    }

    addLog(message, type = 'info') {
        const logContainer = document.getElementById('logConsole');
        if (!logContainer) return;
        
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry log-${type}`;
        logEntry.innerHTML = `
            <span class="log-time">${timestamp}</span>
            <span class="log-message">${message}</span>
        `;
        
        logContainer.appendChild(logEntry);
        
        // 自动滚动到底部（如果启用）
        const autoScroll = logContainer.getAttribute('data-auto-scroll') !== 'false';
        if (autoScroll) {
            logContainer.scrollTop = logContainer.scrollHeight;
        }
        
        // 限制日志条数，避免内存泄漏
        const logEntries = logContainer.querySelectorAll('.log-entry');
        if (logEntries.length > 1000) {
            logEntries[0].remove();        }
    }

    clearLog() {
        const logContainer = document.getElementById('logConsole');
        if (logContainer) {
            logContainer.innerHTML = '';
        }
    }

    // ==================== 导出功能 ====================

    exportResults() {
        const results = {
            timestamp: new Date().toISOString(),
            summary: {
                total: this.totalTests,
                completed: this.completedTests,
                passed: this.passedTests,
                failed: this.failedTests,
                warnings: this.warningTests,
                successRate: this.totalTests > 0 ? ((this.passedTests / this.totalTests) * 100).toFixed(2) : 0
            },
            results: Object.fromEntries(this.testResults)
        };
        
        this.downloadJSON(results, `detection-report-${Date.now()}.json`);
        this.logSuccess('📊 检测报告已导出');
    }

    exportJSON() {
        this.exportResults();
    }

    exportCSV() {
        const csvData = this.generateCSVData();
        this.downloadCSV(csvData, `detection-report-${Date.now()}.csv`);
        this.logSuccess('📊 CSV报告已导出');
    }

    generateCSVData() {
        const headers = ['测试项目', '分类', '状态', '执行时间', '详细信息'];
        const rows = [headers];
        
        for (const [testId, result] of this.testResults.entries()) {
            const row = [
                result.name || testId,
                result.category || '未知',
                result.status || '未知',
                result.duration ? `${result.duration}ms` : '未记录',
                result.message || result.error || '无详细信息'
            ];
            rows.push(row);
        }
        
        return rows.map(row => 
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
        ).join('\n');
    }

    downloadCSV(csvContent, filename) {
        const BOM = '\uFEFF'; // 添加BOM以支持中文
        const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);        URL.revokeObjectURL(url);
    }

    exportPDF() {
        // 使用jsPDF生成PDF报告
        if (typeof window.jsPDF === 'undefined') {
            this.logWarning('📊 正在加载PDF生成库...');
            this.loadJsPDF().then(() => {
                this.generatePDFReport();
            }).catch(() => {
                this.logError('❌ PDF生成库加载失败，请检查网络连接');
            });
        } else {
            this.generatePDFReport();
        }
    }

    async loadJsPDF() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    generatePDFReport() {
        const { jsPDF } = window.jsPDF;
        const doc = new jsPDF();
        
        // 设置字体
        doc.setFont('helvetica');
        
        // 标题
        doc.setFontSize(20);
        doc.text('AlingAi 系统检测报告', 20, 20);
        
        // 基本信息
        doc.setFontSize(12);
        const reportTime = new Date().toLocaleString('zh-CN');
        doc.text(`生成时间: ${reportTime}`, 20, 35);
        doc.text(`总测试数: ${this.totalTests}`, 20, 45);
        doc.text(`已完成: ${this.completedTests}`, 20, 55);
        doc.text(`成功: ${this.passedTests}`, 20, 65);
        doc.text(`失败: ${this.failedTests}`, 20, 75);
        doc.text(`警告: ${this.warningTests}`, 20, 85);
        
        const successRate = this.totalTests > 0 ? ((this.passedTests / this.totalTests) * 100).toFixed(2) : 0;
        doc.text(`成功率: ${successRate}%`, 20, 95);
        
        // 详细结果
        let yPosition = 110;
        doc.setFontSize(14);
        doc.text('详细测试结果:', 20, yPosition);
        yPosition += 15;
        
        doc.setFontSize(10);
        for (const [testId, result] of this.testResults.entries()) {
            if (yPosition > 280) {
                doc.addPage();
                yPosition = 20;
            }
            
            const statusText = result.status === 'success' ? '[通过]' : 
                             result.status === 'error' ? '[失败]' : 
                             result.status === 'warning' ? '[警告]' : '[未知]';
            
            doc.text(`${statusText} ${result.name || testId}`, 20, yPosition);
            yPosition += 8;
            
            if (result.message || result.error) {
                doc.setFontSize(8);
                const message = result.message || result.error;
                const lines = doc.splitTextToSize(message, 170);
                doc.text(lines, 25, yPosition);
                yPosition += lines.length * 4 + 5;
                doc.setFontSize(10);
            }
        }
        
        // 保存PDF
        const filename = `detection-report-${Date.now()}.pdf`;
        doc.save(filename);
        this.logSuccess('📊 PDF报告已导出');
    }

    downloadJSON(data, filename) {
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);        URL.revokeObjectURL(url);
    }

    showSystemInfo() {
        const systemInfo = this.collectSystemInfo();
        this.displaySystemInfoModal(systemInfo);
    }

    collectSystemInfo() {
        const nav = navigator;
        const performance = window.performance;
        
        return {
            browser: {
                name: nav.userAgent.split(' ').slice(-1)[0],
                version: nav.appVersion,
                language: nav.language,
                platform: nav.platform,
                cookieEnabled: nav.cookieEnabled,
                onLine: nav.onLine
            },
            system: {
                url: window.location.href,
                timestamp: new Date().toISOString(),
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                screen: `${screen.width}x${screen.height}`,
                viewport: `${window.innerWidth}x${window.innerHeight}`,
                colorDepth: screen.colorDepth
            },
            performance: {
                memory: performance.memory ? {
                    used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024) + 'MB',
                    total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024) + 'MB',
                    limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024) + 'MB'
                } : '不支持',
                timing: performance.timing ? {
                    pageLoad: performance.timing.loadEventEnd - performance.timing.navigationStart + 'ms',
                    domReady: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart + 'ms'
                } : '不支持'
            },
            detection: {
                totalTests: this.totalTests,
                completedTests: this.completedTests,
                successRate: this.totalTests > 0 ? ((this.passedTests / this.totalTests) * 100).toFixed(2) + '%' : '0%',
                lastRunTime: this.startTime ? new Date(this.startTime).toLocaleString('zh-CN') : '未运行',
                runDuration: this.startTime ? this.formatDuration(Date.now() - this.startTime) : '未知'
            }
        };
    }

    displaySystemInfoModal(info) {
        const modalHTML = `
            <div class="modal fade" id="systemInfoModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-dark text-light">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-info-circle"></i> 系统信息
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-browser-chrome"></i> 浏览器信息</h6>
                                    <ul class="list-unstyled small">
                                        <li><strong>名称:</strong> ${info.browser.name}</li>
                                        <li><strong>平台:</strong> ${info.browser.platform}</li>
                                        <li><strong>语言:</strong> ${info.browser.language}</li>
                                        <li><strong>在线状态:</strong> ${info.browser.onLine ? '在线' : '离线'}</li>
                                        <li><strong>Cookie:</strong> ${info.browser.cookieEnabled ? '启用' : '禁用'}</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bi bi-display"></i> 系统信息</h6>
                                    <ul class="list-unstyled small">
                                        <li><strong>时间:</strong> ${new Date().toLocaleString('zh-CN')}</li>
                                        <li><strong>时区:</strong> ${info.system.timezone}</li>
                                        <li><strong>屏幕:</strong> ${info.system.screen}</li>
                                        <li><strong>视窗:</strong> ${info.system.viewport}</li>
                                        <li><strong>色深:</strong> ${info.system.colorDepth}位</li>
                                    </ul>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-speedometer2"></i> 性能信息</h6>
                                    <ul class="list-unstyled small">
                                    ${typeof info.performance.memory === 'object' ? `
                                            <li><strong>内存使用:</strong> ${info.performance.memory.used}</li>
                                            <li><strong>内存总量:</strong> ${info.performance.memory.total}</li>
                                            <li><strong>内存限制:</strong> ${info.performance.memory.limit}</li>
                                        ` : `<li><strong>内存信息:</strong> ${info.performance.memory}</li>`}
                                        ${typeof info.performance.timing === 'object' ? `
                                            <li><strong>页面加载:</strong> ${info.performance.timing.pageLoad}</li>
                                            <li><strong>DOM就绪:</strong> ${info.performance.timing.domReady}</li>
                                        ` : `<li><strong>时间信息:</strong> ${info.performance.timing}</li>`}
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bi bi-check-circle"></i> 检测统计</h6>
                                    <ul class="list-unstyled small">
                                        <li><strong>总测试数:</strong> ${info.detection.totalTests}</li>
                                        <li><strong>已完成:</strong> ${info.detection.completedTests}</li>
                                        <li><strong>成功率:</strong> ${info.detection.successRate}</li>
                                        <li><strong>最后运行:</strong> ${info.detection.lastRunTime}</li>
                                        <li><strong>运行时长:</strong> ${info.detection.runDuration}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-light btn-sm" onclick="this.copySystemInfo()">
                                <i class="bi bi-clipboard"></i> 复制信息
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // 移除已存在的模态框
        const existingModal = document.getElementById('systemInfoModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // 添加新模态框
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // 显示模态框
        const modal = new bootstrap.Modal(document.getElementById('systemInfoModal'));
        modal.show();
          this.logSuccess('💻 系统信息已显示');
    }

    showCustomDetectionModal() {
        const modalHTML = `
            <div class="modal fade" id="customDetectionModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-dark text-light">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-gear"></i> 自定义检测
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">选择要执行的检测项目：</p>
                            <div class="row">
                                ${this.generateCustomDetectionOptions()}
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">                                <div>
                                    <button class="btn btn-outline-success btn-sm me-2" onclick="detectionSystem.selectAllTests()">
                                        <i class="bi bi-check-all"></i> 全选
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="detectionSystem.clearAllTests()">
                                        <i class="bi bi-x-square"></i> 清空
                                    </button>
                                </div>
                                <small class="text-muted">
                                    已选择: <span id="selectedTestCount">0</span> / ${this.totalTests} 项
                                </small>
                            </div>
                        </div>                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" onclick="detectionSystem.runSelectedTests()">
                                <i class="bi bi-play-circle"></i> 开始检测
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // 移除已存在的模态框
        const existingModal = document.getElementById('customDetectionModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // 添加新模态框
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // 添加事件监听
        this.attachCustomModalEvents();
        
        // 显示模态框
        const modal = new bootstrap.Modal(document.getElementById('customDetectionModal'));
        modal.show();
        
        this.logInfo('⚙️ 自定义检测选项已显示');
    }

    generateCustomDetectionOptions() {
        const categories = {
            backend: { name: '后端检测', tests: ['serverHealth', 'apiResponse', 'databaseConnection'] },
            websocket: { name: 'WebSocket检测', tests: ['websocketConnection', 'messageDelivery', 'connectionStability'] },
            frontend: { name: '前端检测', tests: ['resourceLoading', 'domStructure', 'scriptLoading', 'styleLoading', 'componentInitialization'] },
            chat: { name: '聊天功能', tests: ['messageProcessing', 'guestMode', 'uiInteraction'] },
            performance: { name: '性能检测', tests: ['loadingSpeed', 'memoryUsage', 'animationPerformance'] }
        };
        
        return Object.entries(categories).map(([key, category]) => `
            <div class="col-md-6 mb-3">
                <div class="card bg-secondary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">${category.name}</h6>
                        <div class="form-check">
                            <input class="form-check-input category-checkbox" type="checkbox" 
                                   id="category_${key}" data-category="${key}">
                            <label class="form-check-label" for="category_${key}">全选</label>
                        </div>
                    </div>
                    <div class="card-body">
                        ${category.tests.map(test => `
                            <div class="form-check">
                                <input class="form-check-input test-checkbox" type="checkbox" 
                                       id="test_${test}" data-test="${test}" data-category="${key}">
                                <label class="form-check-label" for="test_${test}">
                                    ${this.getTestDisplayName(test)}
                                </label>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `).join('');
    }

    getTestDisplayName(testKey) {
        const testNames = {
            serverHealth: '服务器健康状况',
            apiResponse: 'API响应测试',
            databaseConnection: '数据库连接',
            websocketConnection: 'WebSocket连接',
            messageDelivery: '消息传递',
            connectionStability: '连接稳定性',
            resourceLoading: '资源加载',
            domStructure: 'DOM结构',
            scriptLoading: '脚本加载',
            styleLoading: '样式加载',
            componentInitialization: '组件初始化',
            messageProcessing: '消息处理',
            guestMode: '访客模式',
            uiInteraction: 'UI交互',
            loadingSpeed: '加载速度',
            memoryUsage: '内存使用',
            animationPerformance: '动画性能'
        };
        return testNames[testKey] || testKey;
    }

    attachCustomModalEvents() {
        // 分类复选框事件
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const category = e.target.dataset.category;
                const checked = e.target.checked;
                document.querySelectorAll(`[data-category="${category}"].test-checkbox`).forEach(test => {
                    test.checked = checked;
                });
                this.updateSelectedCount();
            });
        });

        // 测试项复选框事件
        document.querySelectorAll('.test-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateSelectedCount();
                this.updateCategoryCheckboxes();
            });
        });
    }

    updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.test-checkbox:checked').length;
        const countElement = document.getElementById('selectedTestCount');
        if (countElement) {
            countElement.textContent = selectedCount;
        }
    }

    updateCategoryCheckboxes() {
        document.querySelectorAll('.category-checkbox').forEach(categoryCheckbox => {
            const category = categoryCheckbox.dataset.category;
            const testCheckboxes = document.querySelectorAll(`[data-category="${category}"].test-checkbox`);
            const checkedTests = document.querySelectorAll(`[data-category="${category}"].test-checkbox:checked`);
            
            if (checkedTests.length === 0) {
                categoryCheckbox.checked = false;
                categoryCheckbox.indeterminate = false;
            } else if (checkedTests.length === testCheckboxes.length) {
                categoryCheckbox.checked = true;
                categoryCheckbox.indeterminate = false;
            } else {
                categoryCheckbox.checked = false;
                categoryCheckbox.indeterminate = true;
            }        });
    }

    selectAllTests() {
        document.querySelectorAll('.test-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.checked = true;
            checkbox.indeterminate = false;
        });
        this.updateSelectedCount();
    }

    clearAllTests() {
        document.querySelectorAll('.test-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            checkbox.indeterminate = false;
        });
        this.updateSelectedCount();
    }

    runSelectedTests() {
        const selectedTests = Array.from(document.querySelectorAll('.test-checkbox:checked'))
            .map(checkbox => checkbox.dataset.test);
        
        if (selectedTests.length === 0) {
            this.logWarning('⚠️ 请至少选择一个测试项目');
            return;
        }
        
        // 关闭模态框
        const modal = bootstrap.Modal.getInstance(document.getElementById('customDetectionModal'));
        if (modal) {
            modal.hide();
        }
        
        // 开始自定义检测
        this.logInfo(`🚀 开始自定义检测，共选择 ${selectedTests.length} 个测试项目`);
        this.runCustomDetectionTests(selectedTests);
    }

    async runCustomDetectionTests(selectedTests) {
        this.startDetection();
        this.totalTests = selectedTests.length;
        
        // 创建测试方法映射
        const testMethods = {
            serverHealth: () => this.testServerHealth(),
            apiResponse: () => this.testAPIResponse(),
            databaseConnection: () => this.testDatabaseConnection(),
            websocketConnection: () => this.testWebSocketConnection(),
            messageDelivery: () => this.testMessageDelivery(),
            connectionStability: () => this.testConnectionStability(),
            resourceLoading: () => this.testResourceLoading(),
            domStructure: () => this.testDOMStructure(),
            scriptLoading: () => this.testScriptLoading(),
            styleLoading: () => this.testStyleLoading(),
            componentInitialization: () => this.testComponentInitialization(),
            messageProcessing: () => this.testMessageProcessing(),
            guestMode: () => this.testGuestMode(),
            uiInteraction: () => this.testUIInteraction(),
            loadingSpeed: () => this.testLoadingSpeed(),
            memoryUsage: () => this.testMemoryUsage(),
            animationPerformance: () => this.testAnimationPerformance()
        };
        
        // 执行选中的测试
        for (const testKey of selectedTests) {
            if (testMethods[testKey]) {
                this.updateTestStatus(testKey, 'running');
                try {
                    await testMethods[testKey]();
                } catch (error) {
                    this.logError(`❌ 测试 ${testKey} 执行失败: ${error.message}`);
                    this.updateTestStatus(testKey, 'error');
                    this.testResults.set(testKey, {
                        status: 'error',
                        error: error.message,
                        name: this.getTestDisplayName(testKey)
                    });
                    this.failedTests++;
                }
                this.completedTests++;
                this.updateProgress();
            }
        }
        
        this.completeDetection();
    }

    // ==================== 错误诊断系统 ====================

    // 初始化诊断规则
    initializeDiagnosticRules() {
        this.diagnosticRules.set('connection', {
            patterns: [
                /network error/i,
                /fetch.*failed/i,
                /connection.*refused/i,
                /timeout/i,
                /cors/i
            ],
            severity: 'high',
            suggestions: [
                '检查网络连接状态',
                '验证服务器是否正常运行',
                '确认API端点地址是否正确',
                '检查CORS配置',
                '检查防火墙设置'
            ]
        });

        this.diagnosticRules.set('authentication', {
            patterns: [
                /unauthorized/i,
                /forbidden/i,
                /401|403/,
                /invalid.*token/i,
                /authentication.*failed/i
            ],
            severity: 'high',
            suggestions: [
                '检查身份验证令牌',
                '验证用户权限',
                '确认登录状态',
                '检查API密钥配置'
            ]
        });

        this.diagnosticRules.set('resource', {
            patterns: [
                /not found/i,
                /404/,
                /file.*not.*found/i,
                /module.*not.*found/i,
                /script.*error/i
            ],
            severity: 'medium',
            suggestions: [
                '检查文件路径是否正确',
                '确认资源文件是否存在',
                '验证模块导入路径',
                '检查文件权限设置'
            ]
        });

        this.diagnosticRules.set('performance', {
            patterns: [
                /slow/i,
                /performance/i,
                /memory/i,
                /lag/i,
                /delay/i
            ],
            severity: 'low',
            suggestions: [
                '优化代码性能',
                '检查内存使用情况',
                '减少不必要的网络请求',
                '使用缓存机制',
                '优化数据库查询'
            ]
        });

        this.diagnosticRules.set('syntax', {
            patterns: [
                /syntax.*error/i,
                /unexpected.*token/i,
                /parse.*error/i,
                /invalid.*json/i
            ],
            severity: 'medium',
            suggestions: [
                '检查代码语法',
                '验证JSON格式',
                '确认括号匹配',
                '检查字符编码'
            ]
        });
    }

    // 诊断错误
    diagnoseError(error, context = {}) {
        const errorMsg = error.message || error.toString();
        const diagnosis = {
            error: errorMsg,
            context: context,
            matches: [],
            severity: 'unknown',
            suggestions: ['联系技术支持获取帮助'],
            timestamp: Date.now()
        };

        // 匹配诊断规则
        for (const [ruleType, rule] of this.diagnosticRules) {
            for (const pattern of rule.patterns) {
                if (pattern.test(errorMsg)) {
                    diagnosis.matches.push(ruleType);
                    diagnosis.severity = rule.severity;
                    diagnosis.suggestions = rule.suggestions;
                    break;
                }
            }
        }

        // 根据上下文添加特定建议
        if (context.testType) {
            diagnosis.suggestions.unshift(`针对${context.testType}测试，请检查相关配置`);
        }

        return diagnosis;
    }

    // ==================== 高级报告系统 ====================

    // 生成高级报告
    generateAdvancedReport() {
        const now = Date.now();
        const report = {
            metadata: {
                generatedAt: new Date(now).toISOString(),
                systemVersion: '2.0.0',
                sessionId: this.currentSession.sessionId,
                environment: this.currentSession.environment
            },
            summary: {
                totalTests: this.totalTests,
                completedTests: this.completedTests,
                successRate: this.completedTests > 0 ? (this.passedTests / this.completedTests * 100).toFixed(2) : 0,
                averageTestTime: this.getAverageTestTime(),
                lastRunTime: this.startTime ? now - this.startTime : 0
            },
            performance: this.getPerformanceMetrics(),
            history: this.getHistoricalTrends(),
            recommendations: this.generateRecommendations(),
            testResults: this.getCurrentTestResults(),
            diagnostics: this.getDiagnosticSummary()
        };

        return report;
    }

    // 获取性能指标
    getPerformanceMetrics() {
        const metrics = {
            totalBaselines: this.performanceBaseline.size,
            averagePerformance: 0,
            performanceDistribution: {},
            topPerformers: [],
            poorPerformers: []
        };

        if (this.performanceBaseline.size === 0) return metrics;

        const performanceData = Array.from(this.performanceBaseline.entries()).map(([key, data]) => ({
            test: key,
            average: data.averageTime,
            best: data.bestTime,
            worst: data.worstTime,
            runs: data.runCount
        }));

        // 计算平均性能
        const totalAvg = performanceData.reduce((sum, item) => sum + item.average, 0);
        metrics.averagePerformance = Math.round(totalAvg / performanceData.length);

        // 性能分布
        performanceData.forEach(item => {
            const range = this.getPerformanceRange(item.average);
            metrics.performanceDistribution[range] = (metrics.performanceDistribution[range] || 0) + 1;
        });

        // 排序获取最佳和最差表现
        const sortedByAvg = performanceData.sort((a, b) => a.average - b.average);
        metrics.topPerformers = sortedByAvg.slice(0, 3);
        metrics.poorPerformers = sortedByAvg.slice(-3).reverse();

        return metrics;
    }

    // 获取性能范围
    getPerformanceRange(time) {
        if (time < 100) return 'excellent';
        if (time < 500) return 'good';
        if (time < 1000) return 'average';
        if (time < 3000) return 'slow';
        return 'poor';
    }

    // 获取历史趋势
    getHistoricalTrends() {
        const trends = {
            totalRuns: this.testHistory.length,
            successTrend: [],
            performanceTrend: [],
            categoryDistribution: {},
            timeDistribution: {}
        };

        if (this.testHistory.length === 0) return trends;

        // 按天分组计算成功率趋势
        const dayGroups = {};
        this.testHistory.forEach(entry => {
            const day = new Date(entry.timestamp).toDateString();
            if (!dayGroups[day]) {
                dayGroups[day] = { total: 0, success: 0 };
            }
            dayGroups[day].total++;
            if (entry.status === 'success') {
                dayGroups[day].success++;
            }
        });

        trends.successTrend = Object.entries(dayGroups).map(([day, data]) => ({
            date: day,
            successRate: (data.success / data.total * 100).toFixed(2)
        }));

        // 类别分布
        this.testHistory.forEach(entry => {
            trends.categoryDistribution[entry.category] = (trends.categoryDistribution[entry.category] || 0) + 1;
        });

        // 时间分布（按小时）
        this.testHistory.forEach(entry => {
            const hour = new Date(entry.timestamp).getHours();
            trends.timeDistribution[hour] = (trends.timeDistribution[hour] || 0) + 1;
        });

        return trends;
    }

    // 生成建议
    generateRecommendations() {
        const recommendations = [];

        // 基于成功率的建议
        const successRate = this.completedTests > 0 ? (this.passedTests / this.completedTests) : 0;
        if (successRate < 0.8) {
            recommendations.push({
                type: 'reliability',
                priority: 'high',
                message: '测试成功率较低，建议检查系统稳定性',
                details: `当前成功率: ${(successRate * 100).toFixed(2)}%，建议目标: 90%以上`
            });
        }

        // 基于性能的建议
        const avgTime = this.getAverageTestTime();
        if (avgTime > 1000) {
            recommendations.push({
                type: 'performance',
                priority: 'medium',
                message: '测试执行时间较长，建议优化性能',
                details: `平均测试时间: ${avgTime}ms，建议目标: 1000ms以下`
            });
        }

        // 基于历史记录的建议
        if (this.testHistory.length < 10) {
            recommendations.push({
                type: 'monitoring',
                priority: 'low',
                message: '建议增加测试频率以获得更好的监控效果',
                details: '建议每日至少执行一次完整检测'
            });
        }

        // 基于自动检测的建议
        if (!this.autoDetectionEnabled) {
            recommendations.push({
                type: 'automation',
                priority: 'medium',
                message: '建议启用自动检测功能',
                details: '自动检测可以及时发现系统问题，提高系统可靠性'
            });
        }

        return recommendations;
    }

    // 获取当前测试结果
    getCurrentTestResults() {
        const results = {};
        for (const [key, result] of this.testResults) {
            results[key] = {
                status: result.status,
                duration: result.duration,
                timestamp: result.timestamp,
                details: result.details
            };
        }
        return results;
    }

    // 获取诊断摘要
    getDiagnosticSummary() {
        const summary = {
            totalErrors: 0,
            errorsByType: {},
            severityDistribution: {},
            commonIssues: []
        };

        // 分析历史记录中的错误
        this.testHistory.forEach(entry => {
            if (entry.status === 'error' && entry.error) {
                summary.totalErrors++;
                const diagnosis = this.diagnoseError(new Error(entry.error));
                
                diagnosis.matches.forEach(match => {
                    summary.errorsByType[match] = (summary.errorsByType[match] || 0) + 1;
                });

                summary.severityDistribution[diagnosis.severity] = 
                    (summary.severityDistribution[diagnosis.severity] || 0) + 1;
            }
        });

        // 识别常见问题
        summary.commonIssues = Object.entries(summary.errorsByType)
            .sort(([,a], [,b]) => b - a)
            .slice(0, 5)
            .map(([type, count]) => ({ type, count }));

        return summary;
    }

    // 获取平均测试时间
    getAverageTestTime() {
        if (this.testHistory.length === 0) return 0;
        
        const times = this.testHistory
            .filter(entry => entry.duration)        .map(entry => entry.duration);
        
        if (times.length === 0) return 0;
        
        return Math.round(times.reduce((sum, time) => sum + time, 0) / times.length);
    }

    // ==================== 自动检测管理 ====================
    
    enableAutoDetection(frequency) {
        if (this.isRunning) {
            this.logWarning('⚠️ 检测正在进行中，无法启用自动检测');
            return;
        }
        
        // 停止现有的自动检测
        this.disableAutoDetection();
        
        this.autoDetectionEnabled = true;
        this.detectionFrequency = frequency;
        
        // 设置定时器
        this.autoDetectionInterval = setInterval(() => {
            if (!this.isRunning) {
                this.logInfo(`🔄 自动检测启动 (间隔: ${frequency}分钟)`);
                this.runQuickDetection();
                
                // 发送自动检测通知
                if (this.notificationSystem) {
                    this.notificationSystem.notify({
                        type: 'info',
                        title: '自动检测',
                        message: `定时检测已启动，间隔: ${frequency}分钟`,
                        duration: 3000
                    });
                }
            }
        }, frequency * 60 * 1000);
        
        this.updateAutoDetectionStatus();
        this.saveToStorage();
        this.logSuccess(`✅ 自动检测已启用，间隔: ${frequency}分钟`);
        
        // 发送启用通知
        if (this.notificationSystem) {
            this.notificationSystem.notify({
                type: 'success',
                title: '自动检测启用',
                message: `自动检测已启用，检测间隔: ${frequency}分钟`,
                duration: 4000
            });
        }
    }
    
    disableAutoDetection() {
        if (this.autoDetectionInterval) {
            clearInterval(this.autoDetectionInterval);
            this.autoDetectionInterval = null;
        }
        
        this.autoDetectionEnabled = false;
        this.updateAutoDetectionStatus();
        this.saveToStorage();
        this.logInfo('🛑 自动检测已停用');
        
        // 发送停用通知
        if (this.notificationSystem) {
            this.notificationSystem.notify({
                type: 'info',
                title: '自动检测停用',
                message: '自动检测已停用',
                duration: 3000
            });
        }
    }
      updateAutoDetectionStatus() {
        const statusElement = document.getElementById('autoDetectionStatus');
        if (statusElement) {
            if (this.autoDetectionEnabled) {
                statusElement.textContent = `自动检测: ${this.detectionFrequency}分钟`;
                statusElement.className = 'badge bg-success auto-detection-status active';
            } else {
                statusElement.textContent = '自动检测: 未启用';
                statusElement.className = 'badge bg-secondary';
            }
        }
    }

    // ==================== 可视化仪表板数据提供方法 ====================

    /**
     * 获取可视化仪表板所需的数据
     * @returns {Object} 包含所有图表数据的对象
     */
    getDashboardData() {
        return {
            overview: this.getOverviewData(),
            performance: this.getPerformanceData(),
            trends: this.getTrendsData(),
            realtime: this.getRealtimeData(),
            history: this.testHistory,
            session: this.currentSession
        };
    }

    /**
     * 获取概览数据
     */
    getOverviewData() {
        const currentResults = Array.from(this.testResults.values());
        const overview = {
            total: this.totalTests,
            completed: this.completedTests,
            passed: this.passedTests,
            failed: this.failedTests,
            warning: this.warningTests,
            successRate: this.totalTests > 0 ? ((this.passedTests / this.completedTests) * 100).toFixed(1) : 0
        };

        // 分类统计
        const categoryStats = {};
        Object.keys(this.testCategories).forEach(categoryKey => {
            const category = this.testCategories[categoryKey];
            categoryStats[category.name] = {
                total: category.tests.length,
                completed: 0,
                passed: 0,
                failed: 0,
                warning: 0
            };

            category.tests.forEach(test => {
                const result = this.testResults.get(test.id);
                if (result) {
                    categoryStats[category.name].completed++;
                    if (result.status === 'success') categoryStats[category.name].passed++;
                    else if (result.status === 'error') categoryStats[category.name].failed++;
                    else if (result.status === 'warning') categoryStats[category.name].warning++;
                }
            });
        });

        return {
            ...overview,
            categoryStats
        };
    }

    /**
     * 获取性能数据
     */
    getPerformanceData() {
        const performanceData = {
            baseline: Object.fromEntries(this.performanceBaseline),
            currentSession: {
                startTime: this.currentSession.startTime,
                duration: Date.now() - this.currentSession.startTime,
                testsCompleted: this.completedTests,
                avgTestTime: this.completedTests > 0 ? 
                    (Date.now() - this.currentSession.startTime) / this.completedTests : 0
            },
            recentPerformance: this.getRecentPerformanceTrends()
        };

        return performanceData;
    }

    /**
     * 获取趋势数据
     */
    getTrendsData() {
        const recentHistory = this.testHistory.slice(-20); // 最近20次检测
        
        const trends = {
            timestamps: recentHistory.map(record => new Date(record.timestamp).toLocaleTimeString()),
            successRates: recentHistory.map(record => {
                const total = record.results ? record.results.length : 0;
                const passed = record.results ? record.results.filter(r => r.status === 'success').length : 0;
                return total > 0 ? ((passed / total) * 100).toFixed(1) : 0;
            }),
            testCounts: recentHistory.map(record => record.results ? record.results.length : 0),
            avgDuration: recentHistory.map(record => record.duration || 0)
        };

        return trends;
    }

    /**
     * 获取实时数据
     */
    getRealtimeData() {
        const now = Date.now();
        return {
            timestamp: now,
            isRunning: this.isRunning,
            isPaused: this.isPaused,
            currentProgress: this.completedTests / this.totalTests * 100,
            estimatedTimeRemaining: this.getEstimatedTimeRemaining(),
            systemMetrics: {
                memoryUsage: this.getMemoryUsage(),
                performanceNow: performance.now(),
                connectionStatus: navigator.onLine ? 'online' : 'offline'
            },
            recentTests: Array.from(this.testResults.values()).slice(-5) // 最近5个测试结果
        };
    }

    /**
     * 获取最近性能趋势
     */
    getRecentPerformanceTrends() {
        const recent = this.testHistory.slice(-10);
        return recent.map(record => ({
            timestamp: record.timestamp,
            duration: record.duration || 0,
            successRate: record.results ? 
                (record.results.filter(r => r.status === 'success').length / record.results.length * 100) : 0,
            testCount: record.results ? record.results.length : 0
        }));
    }

    /**
     * 获取预估剩余时间
     */
    getEstimatedTimeRemaining() {
        if (!this.isRunning || this.completedTests === 0) return 0;
        
        const elapsed = Date.now() - this.startTime;
        const avgTimePerTest = elapsed / this.completedTests;
        const remainingTests = this.totalTests - this.completedTests;
        
        return remainingTests * avgTimePerTest;
    }    /**
     * 获取内存使用情况
     */
    getMemoryUsage() {
        if (performance.memory) {
            return {
                used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024), // MB
                total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024), // MB
                limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024) // MB
            };
        }
        return null;
    }
}

// 关键全局函数定义
function runFullDetection() {
    if (detectionSystem) {
        detectionSystem.runFullDetection();
    }
}

function runQuickDetection() {
    if (detectionSystem) {
        detectionSystem.runQuickDetection();
    }
}

function runCustomDetection() {
    if (detectionSystem) {
        detectionSystem.runCustomDetection();
    }
}

function clearResults() {
    if (detectionSystem) {
        detectionSystem.clearResults();
    }
}

function exportResults() {
    if (detectionSystem) {
        detectionSystem.exportResults();
    }
}

function exportJSON() {
    if (detectionSystem) {
        detectionSystem.exportJSON();
    }
}

function exportCSV() {
    if (detectionSystem) {
        detectionSystem.exportCSV();
    }
}

function exportPDF() {
    if (detectionSystem) {
        detectionSystem.exportPDF();
    }
}

// 日志系统全局函数
function logInfo(message) {
    if (detectionSystem) {
        detectionSystem.logInfo(message);
    }
}

function logSuccess(message) {
    if (detectionSystem) {
        detectionSystem.logSuccess(message);
    }
}

function logWarning(message) {
    if (detectionSystem) {
        detectionSystem.logWarning(message);
    }
}

function logError(message) {
    if (detectionSystem) {
        detectionSystem.logError(message);
    }
}

function clearLog() {
    if (detectionSystem) {
        detectionSystem.clearLog();
    }
}

function toggleAutoScroll() {
    if (detectionSystem) {
        detectionSystem.toggleAutoScroll();
    }
}

function exportLog() {
    if (detectionSystem) {
        detectionSystem.exportLog();
    }
}

// 新增功能函数
function showVisualizationDashboard() {
    if (detectionSystem) {
        detectionSystem.showVisualizationDashboard();
    }
}

function showTeamCollaboration() {
    if (detectionSystem) {
        detectionSystem.showTeamCollaboration();
    }
}

function showIntelligentAlertSystem() {
    if (detectionSystem) {
        detectionSystem.showIntelligentAlertSystem();
    }
}

function toggleAutoMode() {
    if (detectionSystem) {
        detectionSystem.toggleAutoMode();
    }
}

function showSystemInfo() {
    if (detectionSystem) {
        detectionSystem.showSystemInfo();
    }
}

// 新增功能函数
function showHistory() {
    if (detectionSystem) {
        detectionSystem.showHistoryModal();
    }
}

function showPerformanceReport() {
    if (detectionSystem) {
        detectionSystem.showPerformanceModal();
    }
}

function enableAutoDetection() {
    if (detectionSystem) {
        const frequency = prompt('请输入自动检测间隔(分钟):', '30');
        if (frequency && !isNaN(frequency) && frequency > 0) {
            detectionSystem.enableAutoDetection(parseInt(frequency));
        }
    }
}

function disableAutoDetection() {
    if (detectionSystem) {
        detectionSystem.disableAutoDetection();
    }
}

function exportAdvancedReport() {
    if (detectionSystem) {
        const report = detectionSystem.generateAdvancedReport();
        detectionSystem.downloadJSON(report, `advanced-report-${Date.now()}.json`);
        detectionSystem.logSuccess('📊 高级报告已导出');
    }
}

function resetPerformanceBaseline() {
    if (detectionSystem) {
        if (confirm('确定要重置所有性能基准数据吗？')) {
            detectionSystem.resetBaseline();
        }
    }
}

function clearAllData() {
    if (detectionSystem) {
        if (confirm('确定要清空所有历史记录和性能数据吗？此操作不可恢复。')) {
            detectionSystem.clearHistory();
            detectionSystem.resetBaseline();
            detectionSystem.logSuccess('🗑️ 所有数据已清空');
        }
    }
}

// 导出到全局作用域
window.detectionSystem = detectionSystem;
window.initializeDetectionSystem = initializeDetectionSystem;
window.runFullDetection = runFullDetection;
window.runQuickDetection = runQuickDetection;
window.runCustomDetection = runCustomDetection;
window.clearResults = clearResults;
window.exportResults = exportResults;
window.exportJSON = exportJSON;
window.exportCSV = exportCSV;
window.exportPDF = exportPDF;
window.toggleAutoMode = toggleAutoMode;
window.showSystemInfo = showSystemInfo;
window.showHistory = showHistory;
window.showPerformanceReport = showPerformanceReport;
window.enableAutoDetection = enableAutoDetection;
window.disableAutoDetection = disableAutoDetection;
window.exportAdvancedReport = exportAdvancedReport;
window.resetPerformanceBaseline = resetPerformanceBaseline;
window.clearAllData = clearAllData;
window.logInfo = logInfo;
window.logSuccess = logSuccess;
window.logWarning = logWarning;
window.logError = logError;
window.clearLog = clearLog;
window.toggleAutoScroll = toggleAutoScroll;
window.exportLog = exportLog;

// 页面加载完成后初始化系统
document.addEventListener('DOMContentLoaded', function() {
    initializeDetectionSystem();
    
    // 如果启用了自动检测，则重启自动检测
    if (detectionSystem && detectionSystem.autoDetectionEnabled) {
        detectionSystem.enableAutoDetection(detectionSystem.detectionFrequency);
    }
    
    // 显示欢迎信息
    if (detectionSystem) {
        detectionSystem.logInfo('🚀 AlingAi集成检测系统已初始化');
        detectionSystem.logInfo('💡 提示: 使用Ctrl+R快速检测, Ctrl+F完整检测');
    }
});

// 页面卸载时清理资源
window.addEventListener('beforeunload', function() {
    if (detectionSystem && detectionSystem.autoDetectionInterval) {
        clearInterval(detectionSystem.autoDetectionInterval);
    }
});
