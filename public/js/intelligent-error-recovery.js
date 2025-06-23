/**
 * AlingAi Pro - 智能错误处理和恢复系统
 * 提供自动错误检测、报告和恢复功能
 */

class IntelligentErrorRecoverySystem {
    constructor() {
        this.errors = [];
        this.recoveryStrategies = new Map();
        this.autoRecoveryEnabled = true;
        this.errorThresholds = {
            javascript: 5,      // 5分钟内最大JavaScript错误数
            network: 10,        // 5分钟内最大网络错误数
            performance: 3,     // 5分钟内最大性能警告数
            animation: 5        // 5分钟内最大动画错误数
        };
        this.timeWindow = 5 * 60 * 1000; // 5分钟时间窗口
        this.recoveryHistory = [];
        this.isRecovering = false;
        this.healthCheckInterval = null;
        
        this.init();
    }
    
    init() {
        this.setupErrorInterception();
        this.setupRecoveryStrategies();
        this.startHealthCheck();
        this.createErrorReportingUI();
        
    }
    
    setupErrorInterception() {
        // JavaScript错误拦截
        window.addEventListener('error', (event) => {
            this.handleJavaScriptError({
                type: 'javascript',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error,
                timestamp: Date.now(),
                stack: event.error?.stack
            });
        });
        
        // Promise错误拦截
        window.addEventListener('unhandledrejection', (event) => {
            this.handlePromiseRejection({
                type: 'promise',
                reason: event.reason,
                timestamp: Date.now(),
                stack: event.reason?.stack
            });
        });
        
        // 网络错误拦截
        this.interceptNetworkErrors();
        
        // 性能监控
        this.setupPerformanceMonitoring();
        
        // 动画错误监控
        this.setupAnimationMonitoring();
    }
    
    interceptNetworkErrors() {
        const originalFetch = window.fetch;
        
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch.apply(window, args);
                
                if (!response.ok) {
                    this.handleNetworkError({
                        type: 'network',
                        url: args[0],
                        status: response.status,
                        statusText: response.statusText,
                        timestamp: Date.now()
                    });
                }
                
                return response;
            } catch (error) {
                this.handleNetworkError({
                    type: 'network',
                    url: args[0],
                    error: error.message,
                    timestamp: Date.now()
                });
                throw error;
            }
        };
        
        // XMLHttpRequest错误拦截
        const originalOpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function(...args) {
            this.addEventListener('error', (event) => {
                window.intelligentErrorRecovery.handleNetworkError({
                    type: 'network',
                    url: args[1],
                    error: 'XMLHttpRequest failed',
                    timestamp: Date.now()
                });
            });
            
            return originalOpen.apply(this, args);
        };
    }
    
    setupPerformanceMonitoring() {
        // 监控内存使用
        setInterval(() => {
            if (performance.memory) {
                const memoryUsage = performance.memory.usedJSHeapSize / performance.memory.jsHeapSizeLimit;
                
                if (memoryUsage > 0.9) {
                    this.handlePerformanceIssue({
                        type: 'performance',
                        issue: 'high_memory_usage',
                        value: memoryUsage,
                        timestamp: Date.now()
                    });
                }
            }
        }, 10000);
        
        // 监控帧率
        let frameCount = 0;
        let lastTime = performance.now();
        
        function checkFramerate() {
            frameCount++;
            const currentTime = performance.now();
            
            if (currentTime - lastTime >= 1000) {
                const fps = frameCount;
                frameCount = 0;
                lastTime = currentTime;
                
                if (fps < 30) {
                    window.intelligentErrorRecovery.handlePerformanceIssue({
                        type: 'performance',
                        issue: 'low_framerate',
                        value: fps,
                        timestamp: Date.now()
                    });
                }
            }
            
            requestAnimationFrame(checkFramerate);
        }
        
        requestAnimationFrame(checkFramerate);
    }
    
    setupAnimationMonitoring() {
        // 监控动画系统状态
        setInterval(() => {
            if (window.cppAnimationSystem) {
                try {
                    // 检查动画系统是否正常运行
                    if (!window.cppAnimationSystem.isRunning) {
                        this.handleAnimationError({
                            type: 'animation',
                            error: 'animation_system_stopped',
                            timestamp: Date.now()
                        });
                    }
                } catch (error) {
                    this.handleAnimationError({
                        type: 'animation',
                        error: error.message,
                        timestamp: Date.now()
                    });
                }
            }
        }, 5000);
    }
    
    setupRecoveryStrategies() {
        // JavaScript错误恢复策略
        this.recoveryStrategies.set('javascript', [
            {
                name: 'reload_script',
                description: '重新加载出错的脚本',
                execute: async (error) => {
                    if (error.filename) {
                        return this.reloadScript(error.filename);
                    }
                    return false;
                }
            },
            {
                name: 'fallback_mode',
                description: '启用降级模式',
                execute: async (error) => {
                    return this.enableFallbackMode();
                }
            }
        ]);
        
        // 网络错误恢复策略
        this.recoveryStrategies.set('network', [
            {
                name: 'retry_request',
                description: '重试网络请求',
                execute: async (error) => {
                    return this.retryNetworkRequest(error);
                }
            },
            {
                name: 'offline_mode',
                description: '启用离线模式',
                execute: async (error) => {
                    return this.enableOfflineMode();
                }
            }
        ]);
        
        // 性能问题恢复策略
        this.recoveryStrategies.set('performance', [
            {
                name: 'reduce_animations',
                description: '减少动画效果',
                execute: async (error) => {
                    return this.reduceAnimations();
                }
            },
            {
                name: 'force_gc',
                description: '强制垃圾回收',
                execute: async (error) => {
                    return this.forceGarbageCollection();
                }
            },
            {
                name: 'low_power_mode',
                description: '启用低功耗模式',
                execute: async (error) => {
                    return this.enableLowPowerMode();
                }
            }
        ]);
        
        // 动画错误恢复策略
        this.recoveryStrategies.set('animation', [
            {
                name: 'restart_animation',
                description: '重启动画系统',
                execute: async (error) => {
                    return this.restartAnimationSystem();
                }
            },
            {
                name: 'fallback_animation',
                description: '使用备用动画',
                execute: async (error) => {
                    return this.enableFallbackAnimation();
                }
            }
        ]);
    }
    
    handleJavaScriptError(error) {
        this.addError(error);
        console.error('JavaScript错误:', error);
        
        if (this.shouldAttemptRecovery('javascript')) {
            this.attemptRecovery('javascript', error);
        }
        
        this.updateErrorUI();
    }
    
    handlePromiseRejection(error) {
        this.addError(error);
        console.error('Promise拒绝:', error);
        
        if (this.shouldAttemptRecovery('javascript')) {
            this.attemptRecovery('javascript', error);
        }
        
        this.updateErrorUI();
    }
    
    handleNetworkError(error) {
        this.addError(error);
        console.error('网络错误:', error);
        
        if (this.shouldAttemptRecovery('network')) {
            this.attemptRecovery('network', error);
        }
        
        this.updateErrorUI();
    }
    
    handlePerformanceIssue(error) {
        this.addError(error);
        console.warn('性能问题:', error);
        
        if (this.shouldAttemptRecovery('performance')) {
            this.attemptRecovery('performance', error);
        }
        
        this.updateErrorUI();
    }
    
    handleAnimationError(error) {
        this.addError(error);
        console.error('动画错误:', error);
        
        if (this.shouldAttemptRecovery('animation')) {
            this.attemptRecovery('animation', error);
        }
        
        this.updateErrorUI();
    }
    
    addError(error) {
        error.id = Date.now() + Math.random();
        this.errors.unshift(error);
        
        // 保持最近的100个错误
        if (this.errors.length > 100) {
            this.errors.pop();
        }
        
        // 发送错误报告到服务器（如果需要）
        this.reportErrorToServer(error);
    }
    
    shouldAttemptRecovery(errorType) {
        if (!this.autoRecoveryEnabled || this.isRecovering) {
            return false;
        }
        
        const recentErrors = this.getRecentErrors(errorType);
        const threshold = this.errorThresholds[errorType];
        
        return recentErrors.length >= threshold;
    }
    
    getRecentErrors(errorType) {
        const cutoffTime = Date.now() - this.timeWindow;
        return this.errors.filter(error => 
            error.type === errorType && error.timestamp > cutoffTime
        );
    }
    
    async attemptRecovery(errorType, error) {
        if (this.isRecovering) {
            return;
        }
        
        this.isRecovering = true;
        
        
        const strategies = this.recoveryStrategies.get(errorType) || [];
        
        for (const strategy of strategies) {
            try {
                
                const success = await strategy.execute(error);
                
                if (success) {
                    
                    this.recordRecovery(errorType, strategy.name, true);
                    break;
                } else {
                    
                    this.recordRecovery(errorType, strategy.name, false);
                }
            } catch (recoveryError) {
                console.error(`恢复策略执行错误:`, recoveryError);
                this.recordRecovery(errorType, strategy.name, false, recoveryError.message);
            }
        }
        
        this.isRecovering = false;
        
        // 等待一段时间后清除相关错误
        setTimeout(() => {
            this.clearErrorsByType(errorType);
        }, 30000);
    }
    
    recordRecovery(errorType, strategyName, success, errorMessage = null) {
        this.recoveryHistory.unshift({
            errorType,
            strategyName,
            success,
            errorMessage,
            timestamp: Date.now()
        });
        
        if (this.recoveryHistory.length > 50) {
            this.recoveryHistory.pop();
        }
    }
    
    clearErrorsByType(errorType) {
        const cutoffTime = Date.now() - this.timeWindow;
        this.errors = this.errors.filter(error => 
            error.type !== errorType || error.timestamp < cutoffTime
        );
        this.updateErrorUI();
    }
    
    // 恢复策略实现
    async reloadScript(filename) {
        try {
            const script = document.querySelector(`script[src="${filename}"]`);
            if (script) {
                const newScript = document.createElement('script');
                newScript.src = filename + '?reload=' + Date.now();
                script.parentNode.replaceChild(newScript, script);
                return true;
            }
        } catch (error) {
            console.error('重新加载脚本失败:', error);
        }
        return false;
    }
    
    async enableFallbackMode() {
        try {
            // 禁用复杂动画
            document.body.classList.add('fallback-mode');
            
            // 简化UI
            const complexElements = document.querySelectorAll('.complex-animation');
            complexElements.forEach(el => el.style.display = 'none');
            
            return true;
        } catch (error) {
            console.error('启用降级模式失败:', error);
        }
        return false;
    }
    
    async retryNetworkRequest(error) {
        try {
            if (error.url) {
                const response = await fetch(error.url);
                return response.ok;
            }
        } catch (retryError) {
            console.error('重试网络请求失败:', retryError);
        }
        return false;
    }
    
    async enableOfflineMode() {
        try {
            document.body.classList.add('offline-mode');
            
            // 显示离线提示
            this.showOfflineNotification();
            
            return true;
        } catch (error) {
            console.error('启用离线模式失败:', error);
        }
        return false;
    }
    
    async reduceAnimations() {
        try {
            // 减少粒子数量
            const particles = document.querySelectorAll('.quantum-particle');
            for (let i = particles.length / 2; i < particles.length; i++) {
                particles[i].remove();
            }
            
            // 降低动画质量
            document.body.classList.add('reduced-animations');
            
            return true;
        } catch (error) {
            console.error('减少动画失败:', error);
        }
        return false;
    }
    
    async forceGarbageCollection() {
        try {
            if (window.gc) {
                window.gc();
            }
            
            // 清理缓存
            if (caches) {
                const keys = await caches.keys();
                for (const key of keys) {
                    await caches.delete(key);
                }
            }
            
            return true;
        } catch (error) {
            console.error('强制垃圾回收失败:', error);
        }
        return false;
    }
    
    async enableLowPowerMode() {
        try {
            document.body.classList.add('low-power-mode');
            
            // 暂停非关键动画
            const animations = document.querySelectorAll('.non-critical-animation');
            animations.forEach(anim => anim.style.animationPlayState = 'paused');
            
            return true;
        } catch (error) {
            console.error('启用低功耗模式失败:', error);
        }
        return false;
    }
    
    async restartAnimationSystem() {
        try {
            if (window.cppAnimationSystem && window.cppAnimationSystem.restart) {
                window.cppAnimationSystem.restart();
                return true;
            }
            
            // 尝试重新初始化动画系统
            if (window.initCppAnimation) {
                window.initCppAnimation();
                return true;
            }
            
        } catch (error) {
            console.error('重启动画系统失败:', error);
        }
        return false;
    }
    
    async enableFallbackAnimation() {
        try {
            // 使用CSS动画作为备用
            document.body.classList.add('fallback-animation');
            
            // 创建简单的CSS动画
            const style = document.createElement('style');
            style.textContent = `
                .fallback-animation .quantum-particle {
                    animation: simple-float 3s ease-in-out infinite;
                }
                @keyframes simple-float {
                    0%, 100% { transform: translateY(0px); }
                    50% { transform: translateY(-10px); }
                }
            `;
            document.head.appendChild(style);
            
            return true;
        } catch (error) {
            console.error('启用备用动画失败:', error);
        }
        return false;
    }
    
    createErrorReportingUI() {
        // 创建错误报告界面
        const errorPanel = document.createElement('div');
        errorPanel.id = 'error-recovery-panel';
        errorPanel.className = 'fixed bottom-4 left-4 z-50 bg-red-900/90 backdrop-blur-md text-white p-3 rounded-lg border border-red-700 shadow-2xl font-mono text-xs hidden max-w-sm';
        
        errorPanel.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-red-400 font-bold flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    错误监控
                </h4>
                <button id="close-error-panel" class="text-red-400 hover:text-red-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="error-summary" class="space-y-1 mb-3">
                <!-- 错误摘要 -->
            </div>
            
            <div class="flex space-x-2">
                <button id="view-error-details" class="bg-red-600 hover:bg-red-700 px-2 py-1 rounded text-xs">
                    查看详情
                </button>
                <button id="force-recovery" class="bg-yellow-600 hover:bg-yellow-700 px-2 py-1 rounded text-xs">
                    强制恢复
                </button>
            </div>
        `;
        
        document.body.appendChild(errorPanel);
        
        // 事件监听器
        document.getElementById('close-error-panel').addEventListener('click', () => {
            errorPanel.classList.add('hidden');
        });
        
        document.getElementById('view-error-details').addEventListener('click', () => {
            this.showErrorDetails();
        });
        
        document.getElementById('force-recovery').addEventListener('click', () => {
            this.forceRecovery();
        });
    }
    
    updateErrorUI() {
        const panel = document.getElementById('error-recovery-panel');
        const summary = document.getElementById('error-summary');
        
        const recentErrors = this.errors.slice(0, 5);
        
        if (recentErrors.length > 0) {
            panel.classList.remove('hidden');
            
            summary.innerHTML = recentErrors.map(error => {
                const timeAgo = Math.round((Date.now() - error.timestamp) / 1000);
                return `
                    <div class="text-xs text-red-300">
                        ${error.type}: ${error.message || error.error || '未知错误'} (${timeAgo}s前)
                    </div>
                `;
            }).join('');
        } else {
            panel.classList.add('hidden');
        }
    }
    
    showErrorDetails() {
        // 创建详细错误报告模态框
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm';
        
        modal.innerHTML = `
            <div class="bg-gray-800 text-white p-6 rounded-lg border border-gray-700 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                <h3 class="text-red-400 font-bold mb-4 flex items-center">
                    <i class="fas fa-bug mr-2"></i>错误详情报告
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <h4 class="text-yellow-400 font-semibold mb-2">最近错误</h4>
                        <div class="bg-gray-900 p-3 rounded font-mono text-xs max-h-60 overflow-y-auto">
                            ${this.errors.slice(0, 10).map(error => `
                                <div class="mb-2 p-2 border-l-2 border-red-500">
                                    <div class="text-red-400">${error.type}: ${error.message || error.error}</div>
                                    <div class="text-gray-400">${new Date(error.timestamp).toLocaleString()}</div>
                                    ${error.stack ? `<div class="text-xs text-gray-500 mt-1">${error.stack}</div>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-green-400 font-semibold mb-2">恢复历史</h4>
                        <div class="bg-gray-900 p-3 rounded font-mono text-xs max-h-40 overflow-y-auto">
                            ${this.recoveryHistory.slice(0, 10).map(recovery => `
                                <div class="mb-1 ${recovery.success ? 'text-green-400' : 'text-red-400'}">
                                    ${recovery.errorType} - ${recovery.strategyName}: ${recovery.success ? '成功' : '失败'}
                                    <span class="text-gray-400">(${new Date(recovery.timestamp).toLocaleString()})</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-6">
                    <button id="export-error-report" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm">
                        导出报告
                    </button>
                    <button id="close-error-details" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded text-sm">
                        关闭
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // 事件监听器
        modal.querySelector('#close-error-details').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.querySelector('#export-error-report').addEventListener('click', () => {
            this.exportErrorReport();
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }
    
    forceRecovery() {
        
        
        // 执行所有可用的恢复策略
        this.enableFallbackMode();
        this.reduceAnimations();
        this.forceGarbageCollection();
        this.enableLowPowerMode();
        
        // 清除所有错误
        this.errors = [];
        this.updateErrorUI();
    }
    
    exportErrorReport() {
        const report = {
            timestamp: new Date().toISOString(),
            errors: this.errors,
            recoveryHistory: this.recoveryHistory,
            systemInfo: {
                userAgent: navigator.userAgent,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                memory: performance.memory ? {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit
                } : null
            }
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `error-report-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    
    showOfflineNotification() {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-yellow-600 text-white p-3 rounded-lg shadow-lg';
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-wifi-slash mr-2"></i>
                系统已切换到离线模式
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 5000);
    }
    
    async reportErrorToServer(error) {
        try {
            if (navigator.onLine) {
                await fetch('/api/error-report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(error)
                });
            }
        } catch (reportError) {
            console.error('错误报告发送失败:', reportError);
        }
    }
    
    startHealthCheck() {
        this.healthCheckInterval = setInterval(() => {
            this.performHealthCheck();
        }, 30000); // 每30秒检查一次
    }
    
    performHealthCheck() {
        const checks = [
            this.checkJavaScriptHealth(),
            this.checkNetworkHealth(),
            this.checkPerformanceHealth(),
            this.checkAnimationHealth()
        ];
        
        const healthScore = checks.filter(check => check).length / checks.length;
        
        if (healthScore < 0.5) {
            console.warn('系统健康状况不佳，健康分数:', healthScore);
            this.forceRecovery();
        }
    }
    
    checkJavaScriptHealth() {
        const recentJSErrors = this.getRecentErrors('javascript');
        return recentJSErrors.length < this.errorThresholds.javascript;
    }
    
    checkNetworkHealth() {
        const recentNetworkErrors = this.getRecentErrors('network');
        return recentNetworkErrors.length < this.errorThresholds.network;
    }
    
    checkPerformanceHealth() {
        const recentPerformanceIssues = this.getRecentErrors('performance');
        return recentPerformanceIssues.length < this.errorThresholds.performance;
    }
    
    checkAnimationHealth() {
        const recentAnimationErrors = this.getRecentErrors('animation');
        return recentAnimationErrors.length < this.errorThresholds.animation;
    }
}

// 全局初始化
window.intelligentErrorRecovery = new IntelligentErrorRecoverySystem();


