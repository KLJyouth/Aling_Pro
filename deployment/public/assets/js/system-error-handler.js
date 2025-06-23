// 系统错误处理和日志优化脚本
// 创建时间: 2025-05-30

console.log('🛠️ 加载系统错误处理和日志优化模块...');

// 全局错误处理器
class SystemErrorHandler {
    constructor() {
        this.errorLog = [];
        this.maxLogSize = 100;
        this.suppressedErrors = new Set();
        this.errorCounts = new Map();
        
        this.initializeErrorHandling();
        console.log('🔧 系统错误处理器初始化完成');
    }
    
    initializeErrorHandling() {
        // 捕获未处理的错误
        window.addEventListener('error', (event) => {
            this.handleError({
                type: 'javascript',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error,
                timestamp: new Date().toISOString()
            });
        });
        
        // 捕获未处理的Promise拒绝
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError({
                type: 'promise',
                message: event.reason?.message || 'Promise rejection',
                reason: event.reason,
                timestamp: new Date().toISOString()
            });
        });
        
        // 添加常见错误的抑制规则
        this.addSuppression('The AudioContext was not allowed to start');
        this.addSuppression('Could not load content for');
        this.addSuppression('DevTools failed to load source map');
        this.addSuppression('net::ERR_HTTP_RESPONSE_CODE_FAILURE');
    }
    
    handleError(errorInfo) {
        const errorKey = this.getErrorKey(errorInfo);
        
        // 检查是否应该抑制此错误
        if (this.shouldSuppressError(errorInfo)) {
            return;
        }
        
        // 更新错误计数
        const count = this.errorCounts.get(errorKey) || 0;
        this.errorCounts.set(errorKey, count + 1);
        
        // 只在前几次出现时记录
        if (count < 3) {
            this.logError(errorInfo);
            this.errorLog.push(errorInfo);
            
            // 维护日志大小
            if (this.errorLog.length > this.maxLogSize) {
                this.errorLog.shift();
            }
        }
    }
    
    getErrorKey(errorInfo) {
        return `${errorInfo.type}:${errorInfo.message}:${errorInfo.filename}:${errorInfo.lineno}`;
    }
    
    shouldSuppressError(errorInfo) {
        return Array.from(this.suppressedErrors).some(pattern => 
            errorInfo.message?.includes(pattern)
        );
    }
    
    addSuppression(pattern) {
        this.suppressedErrors.add(pattern);
    }
    
    logError(errorInfo) {
        const prefix = errorInfo.type === 'promise' ? '🔸' : '🔹';
        console.group(`${prefix} 系统错误 [${errorInfo.type}]`);
        console.error('消息:', errorInfo.message);
        if (errorInfo.filename) {
            console.error('文件:', errorInfo.filename, '行:', errorInfo.lineno);
        }
        if (errorInfo.error?.stack) {
            console.error('堆栈:', errorInfo.error.stack);
        }
        console.groupEnd();
    }
    
    getErrorSummary() {
        const summary = {
            totalErrors: this.errorLog.length,
            errorsByType: {},
            topErrors: []
        };
        
        // 按类型统计
        this.errorLog.forEach(error => {
            summary.errorsByType[error.type] = (summary.errorsByType[error.type] || 0) + 1;
        });
        
        // 获取最常见的错误
        const sortedErrors = Array.from(this.errorCounts.entries())
            .sort((a, b) => b[1] - a[1])
            .slice(0, 5);
            
        summary.topErrors = sortedErrors.map(([key, count]) => ({ key, count }));
        
        return summary;
    }
}

// 控制台输出优化器
class ConsoleOptimizer {
    constructor() {
        this.messageCache = new Map();
        this.rateLimits = new Map();
        this.messageCount = 0;
        
        this.initializeOptimization();
        console.log('📝 控制台输出优化器初始化完成');
    }
    
    initializeOptimization() {
        // 包装原生console方法
        this.originalConsole = {
            log: console.log,
            warn: console.warn,
            error: console.error,
            info: console.info
        };
        
        // 重写console方法
        console.log = this.createRateLimitedLogger('log');
        console.warn = this.createRateLimitedLogger('warn');
        console.error = this.createRateLimitedLogger('error');
        console.info = this.createRateLimitedLogger('info');
    }
    
    createRateLimitedLogger(level) {
        return (...args) => {
            const message = args.join(' ');
            const key = `${level}:${message}`;
            
            // 检查是否需要限制频率
            if (this.shouldRateLimit(key)) {
                return;
            }
            
            // 调用原始方法
            this.originalConsole[level](...args);
            this.messageCount++;
        };
    }
    
    shouldRateLimit(key) {
        const now = Date.now();
        const limit = this.rateLimits.get(key);
        
        if (!limit) {
            this.rateLimits.set(key, { count: 1, lastTime: now });
            return false;
        }
        
        // 如果在1秒内超过3次相同消息，则限制
        if (now - limit.lastTime < 1000 && limit.count >= 3) {
            return true;
        }
        
        // 如果超过1秒，重置计数
        if (now - limit.lastTime > 1000) {
            limit.count = 1;
            limit.lastTime = now;
        } else {
            limit.count++;
        }
        
        return false;
    }
    
    getStats() {
        return {
            totalMessages: this.messageCount,
            rateLimitedMessages: this.rateLimits.size,
            cacheSize: this.messageCache.size
        };
    }
    
    reset() {
        this.messageCache.clear();
        this.rateLimits.clear();
        this.messageCount = 0;
        console.log('🧹 控制台优化器已重置');
    }
}

// 页面性能监控器
class PerformanceMonitor {
    constructor() {
        this.metrics = {};
        this.observers = [];
        
        this.initializeMonitoring();
        console.log('📊 页面性能监控器初始化完成');
    }
    
    initializeMonitoring() {
        // 监控页面加载性能
        if ('PerformanceObserver' in window) {
            // 监控导航时间
            const navObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    if (entry.entryType === 'navigation') {
                        this.metrics.navigation = {
                            domContentLoaded: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart,
                            loadComplete: entry.loadEventEnd - entry.loadEventStart,
                            firstPaint: entry.fetchStart,
                            timestamp: Date.now()
                        };
                    }
                });
            });
            
            try {
                navObserver.observe({ entryTypes: ['navigation'] });
                this.observers.push(navObserver);
            } catch (e) {
                console.warn('导航性能监控不可用');
            }
            
            // 监控资源加载时间
            const resourceObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const slowResources = entries.filter(entry => 
                    entry.duration > 1000 && entry.name.includes('.js')
                );
                
                if (slowResources.length > 0) {
                    console.warn('🐌 检测到加载缓慢的资源:', slowResources.map(r => ({
                        name: r.name.split('/').pop(),
                        duration: Math.round(r.duration) + 'ms'
                    })));
                }
            });
            
            try {
                resourceObserver.observe({ entryTypes: ['resource'] });
                this.observers.push(resourceObserver);
            } catch (e) {
                console.warn('资源性能监控不可用');
            }
        }
    }
    
    getPerformanceReport() {
        const report = {
            timestamp: new Date().toISOString(),
            metrics: this.metrics,
            memory: this.getMemoryUsage(),
            timing: this.getPageTiming()
        };
        
        return report;
    }
    
    getMemoryUsage() {
        if ('memory' in performance) {
            return {
                used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024) + 'MB',
                total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024) + 'MB',
                limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024) + 'MB'
            };
        }
        return null;
    }
    
    getPageTiming() {
        if ('timing' in performance) {
            const timing = performance.timing;
            return {
                pageLoad: timing.loadEventEnd - timing.navigationStart,
                domReady: timing.domContentLoadedEventEnd - timing.navigationStart,
                firstByte: timing.responseStart - timing.navigationStart
            };
        }
        return null;
    }
    
    cleanup() {
        this.observers.forEach(observer => observer.disconnect());
        this.observers = [];
    }
}

// 创建全局实例
window.systemErrorHandler = new SystemErrorHandler();
window.consoleOptimizer = new ConsoleOptimizer();
window.performanceMonitor = new PerformanceMonitor();

// 添加全局调试工具
window.debugUtils = {
    getErrorSummary: () => window.systemErrorHandler.getErrorSummary(),
    getConsoleStats: () => window.consoleOptimizer.getStats(),
    getPerformanceReport: () => window.performanceMonitor.getPerformanceReport(),
    
    resetConsole: () => window.consoleOptimizer.reset(),
    
    suppressError: (pattern) => window.systemErrorHandler.addSuppression(pattern),
    
    showSystemStatus: () => {
        console.group('🔍 系统状态报告');
        console.log('错误统计:', window.debugUtils.getErrorSummary());
        console.log('控制台统计:', window.debugUtils.getConsoleStats());
        console.log('性能报告:', window.debugUtils.getPerformanceReport());
        console.groupEnd();
    }
};

// 页面卸载时清理
window.addEventListener('beforeunload', () => {
    window.performanceMonitor.cleanup();
});

console.log('✅ 系统错误处理和日志优化模块加载完成');
console.log('🔧 可用工具: window.debugUtils.showSystemStatus()');
