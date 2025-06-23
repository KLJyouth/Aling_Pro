/**
 * C++动画性能监控和自适应优化系统
 * 实时监控动画性能，根据设备能力动态调整效果
 */
class AnimationPerformanceMonitor {
    constructor() {
        this.frameCount = 0;
        this.lastTime = performance.now();
        this.fps = 60;
        this.performanceHistory = [];
        this.adaptiveSettings = {
            particleCount: 20,
            effectQuality: 'high',
            animationSpeed: 1.0,
            shadowQuality: 'high'
        };
        
        this.thresholds = {
            excellent: 55,  // > 55 FPS
            good: 40,       // 40-55 FPS  
            fair: 25,       // 25-40 FPS
            poor: 15        // < 25 FPS
        };
        
        this.monitoring = false;
        this.optimizationCallbacks = [];
        
        this.init();
    }
    
    init() {
        this.detectDeviceCapabilities();
        this.setupPerformanceObserver();
        console.log('🔬 动画性能监控器初始化完成');
    }
    
    // 检测设备性能能力
    detectDeviceCapabilities() {
        // GPU信息
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        
        if (gl) {
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            this.deviceInfo = {
                renderer: debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'Unknown',
                vendor: debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : 'Unknown',
                maxTextureSize: gl.getParameter(gl.MAX_TEXTURE_SIZE),
                supportedExtensions: gl.getSupportedExtensions()
            };
        }
        
        // 内存信息
        this.deviceInfo.memory = navigator.deviceMemory || 'Unknown';
        this.deviceInfo.cores = navigator.hardwareConcurrency || 'Unknown';
        
        // 初始性能等级
        this.performanceLevel = this.calculateInitialPerformanceLevel();
        
        console.log('📊 设备性能评估:', {
            level: this.performanceLevel,
            info: this.deviceInfo
        });
    }
    
    calculateInitialPerformanceLevel() {
        let score = 0;
        
        // 内存评分
        if (this.deviceInfo.memory >= 8) score += 30;
        else if (this.deviceInfo.memory >= 4) score += 20;
        else if (this.deviceInfo.memory >= 2) score += 10;
        
        // CPU核心评分
        if (this.deviceInfo.cores >= 8) score += 20;
        else if (this.deviceInfo.cores >= 4) score += 15;
        else if (this.deviceInfo.cores >= 2) score += 10;
        
        // GPU评分（基于已知型号关键词）
        const renderer = this.deviceInfo.renderer.toLowerCase();
        if (renderer.includes('nvidia') || renderer.includes('radeon')) {
            score += 25;
        } else if (renderer.includes('intel')) {
            score += 15;
        }
        
        // 移动设备检测
        if (/Mobile|Android|iPhone|iPad/.test(navigator.userAgent)) {
            score -= 20;
        }
        
        if (score >= 70) return 'excellent';
        if (score >= 50) return 'good';
        if (score >= 30) return 'fair';
        return 'poor';
    }
    
    // 设置性能观察器
    setupPerformanceObserver() {
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    if (entry.entryType === 'measure') {
                        this.recordPerformanceMetric(entry);
                    }
                });
            });
            
            observer.observe({ entryTypes: ['measure'] });
        }
    }
    
    // 开始监控
    startMonitoring() {
        if (this.monitoring) return;
        
        this.monitoring = true;
        this.frameCount = 0;
        this.lastTime = performance.now();
        
        this.monitorFrame();
        console.log('🔍 开始性能监控');
    }
    
    // 停止监控
    stopMonitoring() {
        this.monitoring = false;
        console.log('⏹️ 停止性能监控');
    }
    
    // 监控帧率
    monitorFrame() {
        if (!this.monitoring) return;
        
        const currentTime = performance.now();
        this.frameCount++;
        
        // 每秒计算一次FPS
        if (currentTime - this.lastTime >= 1000) {
            this.fps = Math.round((this.frameCount * 1000) / (currentTime - this.lastTime));
            this.frameCount = 0;
            this.lastTime = currentTime;
            
            this.recordFPS(this.fps);
            this.evaluatePerformance();
        }
        
        requestAnimationFrame(() => this.monitorFrame());
    }
    
    // 记录FPS数据
    recordFPS(fps) {
        this.performanceHistory.push({
            timestamp: Date.now(),
            fps: fps,
            level: this.getFPSLevel(fps)
        });
        
        // 保持最近100条记录
        if (this.performanceHistory.length > 100) {
            this.performanceHistory.shift();
        }
    }
    
    getFPSLevel(fps) {
        if (fps >= this.thresholds.excellent) return 'excellent';
        if (fps >= this.thresholds.good) return 'good';
        if (fps >= this.thresholds.fair) return 'fair';
        return 'poor';
    }
    
    // 评估性能并优化
    evaluatePerformance() {
        const recentHistory = this.performanceHistory.slice(-10); // 最近10秒
        if (recentHistory.length < 5) return;
        
        const avgFPS = recentHistory.reduce((sum, record) => sum + record.fps, 0) / recentHistory.length;
        const currentLevel = this.getFPSLevel(avgFPS);
        
        // 如果性能下降，触发优化
        if (currentLevel !== this.performanceLevel) {
            console.log(`📈 性能等级变化: ${this.performanceLevel} → ${currentLevel}`);
            this.performanceLevel = currentLevel;
            this.optimizeSettings();
        }
    }
    
    // 优化设置
    optimizeSettings() {
        const oldSettings = { ...this.adaptiveSettings };
        
        switch (this.performanceLevel) {
            case 'excellent':
                this.adaptiveSettings = {
                    particleCount: 30,
                    effectQuality: 'ultra',
                    animationSpeed: 1.0,
                    shadowQuality: 'high',
                    enableAdvancedEffects: true
                };
                break;
                
            case 'good':
                this.adaptiveSettings = {
                    particleCount: 20,
                    effectQuality: 'high',
                    animationSpeed: 1.0,
                    shadowQuality: 'medium',
                    enableAdvancedEffects: true
                };
                break;
                
            case 'fair':
                this.adaptiveSettings = {
                    particleCount: 15,
                    effectQuality: 'medium',
                    animationSpeed: 0.8,
                    shadowQuality: 'low',
                    enableAdvancedEffects: false
                };
                break;
                
            case 'poor':
                this.adaptiveSettings = {
                    particleCount: 8,
                    effectQuality: 'low',
                    animationSpeed: 0.6,
                    shadowQuality: 'none',
                    enableAdvancedEffects: false
                };
                break;
        }
        
        // 通知动画系统更新设置
        this.notifyOptimization(oldSettings, this.adaptiveSettings);
    }
    
    // 通知优化
    notifyOptimization(oldSettings, newSettings) {
        const optimizationData = {
            performanceLevel: this.performanceLevel,
            fps: this.fps,
            oldSettings,
            newSettings,
            timestamp: Date.now()
        };
        
        this.optimizationCallbacks.forEach(callback => {
            try {
                callback(optimizationData);
            } catch (error) {
                console.error('❌ 优化回调执行失败:', error);
            }
        });
        
        console.log('⚡ 性能优化应用:', optimizationData);
    }
    
    // 添加优化回调
    onOptimization(callback) {
        this.optimizationCallbacks.push(callback);
    }
    
    // 记录性能指标
    recordPerformanceMetric(entry) {
        if (entry.duration > 16.67) { // 超过60FPS的帧时间
            console.warn(`⚠️ 性能瓶颈检测: ${entry.name} 耗时 ${entry.duration.toFixed(2)}ms`);
        }
    }
    
    // 获取性能报告
    getPerformanceReport() {
        const recent = this.performanceHistory.slice(-30);
        const avgFPS = recent.length ? recent.reduce((sum, r) => sum + r.fps, 0) / recent.length : 0;
        
        return {
            currentFPS: this.fps,
            averageFPS: Math.round(avgFPS),
            performanceLevel: this.performanceLevel,
            deviceInfo: this.deviceInfo,
            adaptiveSettings: this.adaptiveSettings,
            history: this.performanceHistory.slice(-10)
        };
    }
    
    // 手动标记性能区间
    mark(name) {
        performance.mark(name);
    }
    
    measure(name, startMark, endMark) {
        performance.measure(name, startMark, endMark);
    }
    
    // 清理资源
    destroy() {
        this.stopMonitoring();
        this.optimizationCallbacks = [];
        this.performanceHistory = [];
        console.log('🗑️ 性能监控器已销毁');
    }
}

// 全局性能监控器
window.AnimationPerformanceMonitor = AnimationPerformanceMonitor;

// 导出供其他模块使用
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AnimationPerformanceMonitor;
}
