/**
 * 统一背景管理器
 * 协调极简主义背景与量子动画系统
 */
class UnifiedBackgroundManager {
    constructor() {
        this.currentMode = 'minimalist'; // 默认极简模式
        this.quantumEnabled = false;
        this.performanceMode = this.detectPerformanceLevel();
        this.backgroundSystems = new Map();
        
        this.init();
    }
    
    init() {
        console.log('🎨 初始化统一背景管理器...');
        
        // 检测设备性能
        this.performanceMode = this.detectPerformanceLevel();
        console.log(`📊 设备性能等级: ${this.performanceMode}`);
        
        // 初始化背景系统
        this.initializeBackgroundSystems();
        
        // 设置事件监听器
        this.setupEventListeners();
        
        // 应用初始背景模式
        this.applyBackgroundMode(this.currentMode);
        
        console.log('✅ 统一背景管理器初始化完成');
    }
    
    detectPerformanceLevel() {
        // 基于设备特征检测性能等级
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        
        if (!gl) return 'low';
        
        const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
        const renderer = debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : '';
        
        // 检测内存和并发数
        const memory = navigator.deviceMemory || 4;
        const cores = navigator.hardwareConcurrency || 4;
        
        if (memory >= 8 && cores >= 8) return 'high';
        if (memory >= 4 && cores >= 4) return 'medium';
        return 'low';
    }
    
    initializeBackgroundSystems() {
        // 注册极简主义背景系统
        if (window.MinimalistBackground) {
            this.backgroundSystems.set('minimalist', window.MinimalistBackground);
            console.log('✅ 极简主义背景系统已注册');
        }
        
        // 注册量子粒子系统
        if (window.quantumParticleSystem) {
            this.backgroundSystems.set('quantum', window.quantumParticleSystem);
            console.log('✅ 量子粒子系统已注册');
        }
        
        // 注册量子动画系统
        if (window.quantumAnimation) {
            this.backgroundSystems.set('quantumAnimation', window.quantumAnimation);
            console.log('✅ 量子动画系统已注册');
        }
    }
    
    setupEventListeners() {
        // 监听性能变化
        if ('connection' in navigator) {
            navigator.connection.addEventListener('change', () => {
                this.handleConnectionChange();
            });
        }
        
        // 监听页面可见性变化
        document.addEventListener('visibilitychange', () => {
            this.handleVisibilityChange();
        });
        
        // 监听量子聊天事件
        if (window.quantumChatIntegrator) {
            window.quantumChatIntegrator.on('chatEvent', (data) => {
                this.handleQuantumEvent(data);
            });
        }
        
        // 添加切换按钮事件
        this.addBackgroundToggle();
    }
    
    addBackgroundToggle() {
        // 创建背景模式切换按钮
        const toggleButton = document.createElement('button');
        toggleButton.id = 'backgroundModeToggle';
        toggleButton.innerHTML = `
            <i class="fas fa-palette"></i>
            <span>背景模式</span>
        `;
        toggleButton.className = 'fixed top-4 left-4 z-50 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg px-3 py-2 text-white hover:bg-white/20 transition-all duration-300 text-sm';
        toggleButton.style.cssText = `
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        `;
        
        toggleButton.addEventListener('click', () => {
            this.toggleBackgroundMode();
        });
        
        // 添加到页面
        document.body.appendChild(toggleButton);
        
        // 添加快捷键支持 (Ctrl+B)
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                this.toggleBackgroundMode();
            }
        });
    }
    
    toggleBackgroundMode() {
        const modes = ['minimalist', 'quantum', 'hybrid'];
        const currentIndex = modes.indexOf(this.currentMode);
        const nextMode = modes[(currentIndex + 1) % modes.length];
        
        this.setBackgroundMode(nextMode);
        
        // 显示切换提示
        this.showModeNotification(nextMode);
    }
    
    setBackgroundMode(mode) {
        console.log(`🎨 切换背景模式: ${this.currentMode} → ${mode}`);
        
        // 禁用当前模式
        this.disableCurrentMode();
        
        // 启用新模式
        this.currentMode = mode;
        this.applyBackgroundMode(mode);
        
        // 保存用户偏好
        localStorage.setItem('backgroundMode', mode);
    }
    
    applyBackgroundMode(mode) {
        const body = document.body;
        
        // 清除所有背景模式类
        body.classList.remove('bg-minimalist', 'bg-quantum', 'bg-hybrid');
        
        switch (mode) {
            case 'minimalist':
                this.enableMinimalistMode();
                body.classList.add('bg-minimalist');
                break;
                
            case 'quantum':
                if (this.performanceMode !== 'low') {
                    this.enableQuantumMode();
                    body.classList.add('bg-quantum');
                } else {
                    // 低性能设备降级到极简模式
                    this.enableMinimalistMode();
                    body.classList.add('bg-minimalist');
                    console.warn('⚠️ 低性能设备，降级到极简模式');
                }
                break;
                
            case 'hybrid':
                this.enableHybridMode();
                body.classList.add('bg-hybrid');
                break;
        }
    }
    
    enableMinimalistMode() {
        console.log('🎨 启用极简主义背景模式');
        
        // 启用极简主义背景
        const minimalistBg = this.backgroundSystems.get('minimalist');
        if (minimalistBg && minimalistBg.enable) {
            minimalistBg.enable();
        }
        
        // 禁用量子系统
        this.disableQuantumSystems();
    }
    
    enableQuantumMode() {
        console.log('🌌 启用量子背景模式');
        
        // 禁用极简主义背景
        const minimalistBg = this.backgroundSystems.get('minimalist');
        if (minimalistBg && minimalistBg.disable) {
            minimalistBg.disable();
        }
        
        // 启用量子系统
        this.enableQuantumSystems();
    }
    
    enableHybridMode() {
        console.log('🔄 启用混合背景模式');
        
        // 同时启用两个系统，但降低量子系统强度
        const minimalistBg = this.backgroundSystems.get('minimalist');
        if (minimalistBg && minimalistBg.enable) {
            minimalistBg.enable();
            minimalistBg.setIntensity(0.5); // 降低强度
        }
        
        const quantumSystem = this.backgroundSystems.get('quantum');
        if (quantumSystem && quantumSystem.enable) {
            quantumSystem.enable();
            if (quantumSystem.setOpacity) quantumSystem.setOpacity(0.3);
        }
    }
    
    disableCurrentMode() {
        // 禁用所有背景系统
        this.backgroundSystems.forEach((system, name) => {
            if (system && system.disable) {
                system.disable();
            }
        });
    }
    
    enableQuantumSystems() {
        const quantumSystem = this.backgroundSystems.get('quantum');
        if (quantumSystem) {
            if (quantumSystem.enable) quantumSystem.enable();
            this.quantumEnabled = true;
        }
        
        const quantumAnimation = this.backgroundSystems.get('quantumAnimation');
        if (quantumAnimation && quantumAnimation.enable) {
            quantumAnimation.enable();
        }
    }
    
    disableQuantumSystems() {
        const quantumSystem = this.backgroundSystems.get('quantum');
        if (quantumSystem && quantumSystem.disable) {
            quantumSystem.disable();
        }
        
        const quantumAnimation = this.backgroundSystems.get('quantumAnimation');
        if (quantumAnimation && quantumAnimation.hide) {
            quantumAnimation.hide();
        }
        
        this.quantumEnabled = false;
    }
    
    handleQuantumEvent(data) {
        // 只在量子模式下处理事件
        if (this.currentMode === 'quantum' || this.currentMode === 'hybrid') {
            if (window.quantumChatIntegrator) {
                window.quantumChatIntegrator.triggerChatEvent(data.eventType, data);
            }
        }
    }
    
    handleConnectionChange() {
        const connection = navigator.connection;
        if (connection && connection.effectiveType) {
            const slowConnection = ['slow-2g', '2g'].includes(connection.effectiveType);
            
            if (slowConnection && this.currentMode === 'quantum') {
                console.log('📶 检测到慢速网络，切换到极简模式');
                this.setBackgroundMode('minimalist');
            }
        }
    }
    
    handleVisibilityChange() {
        if (document.hidden) {
            // 页面隐藏时暂停动画
            this.pauseAnimations();
        } else {
            // 页面可见时恢复动画
            this.resumeAnimations();
        }
    }
    
    pauseAnimations() {
        this.backgroundSystems.forEach((system) => {
            if (system && system.pause) {
                system.pause();
            }
        });
    }
    
    resumeAnimations() {
        this.backgroundSystems.forEach((system) => {
            if (system && system.resume) {
                system.resume();
            }
        });
    }
    
    showModeNotification(mode) {
        const modeNames = {
            minimalist: '极简主义',
            quantum: '量子动画',
            hybrid: '混合模式'
        };
        
        const notification = document.createElement('div');
        notification.className = 'fixed top-20 left-4 z-50 bg-blue-600/90 backdrop-blur-md border border-blue-400/30 rounded-lg px-4 py-2 text-white text-sm';
        notification.innerHTML = `🎨 已切换到 ${modeNames[mode]} 背景模式`;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }
    
    // 公共API
    getCurrentMode() {
        return this.currentMode;
    }
    
    getPerformanceMode() {
        return this.performanceMode;
    }
    
    isQuantumEnabled() {
        return this.quantumEnabled;
    }
    
    // 获取系统状态
    getSystemStatus() {
        return {
            currentMode: this.currentMode,
            performanceMode: this.performanceMode,
            quantumEnabled: this.quantumEnabled,
            availableSystems: Array.from(this.backgroundSystems.keys()),
            memoryUsage: performance.memory ? Math.round(performance.memory.usedJSHeapSize / 1024 / 1024) : 'Unknown'
        };
    }
}

// 初始化统一背景管理器
document.addEventListener('DOMContentLoaded', () => {
    // 确保在其他系统初始化后再创建管理器
    setTimeout(() => {
        if (!window.unifiedBackgroundManager) {
            window.unifiedBackgroundManager = new UnifiedBackgroundManager();
            
            // 从本地存储恢复用户偏好
            const savedMode = localStorage.getItem('backgroundMode');
            if (savedMode && ['minimalist', 'quantum', 'hybrid'].includes(savedMode)) {
                window.unifiedBackgroundManager.setBackgroundMode(savedMode);
            }
        }
    }, 1000);
});

console.log('📦 统一背景管理器模块已加载');
