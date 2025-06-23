/**
 * 量子球-聊天系统实时集成模块
 * 连接3D量子球可视化与聊天功能，提供实时动画反馈
 */

class QuantumChatIntegrator {
    constructor() {
        this.wsConnection = null;
        this.quantumBallSystem = null;
        this.chatSystem = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectInterval = 3000;
        this.quantumSphereRef = null;
        this.isAuthenticated = false;
        
        // 动画状态管理
        this.currentAnimation = null;
        this.animationQueue = [];
        this.isAnimating = false;
        
        // 事件监听器
        this.eventListeners = new Map();
        
        this.init();
    }
    
    // 设置量子球系统引用（用于首页）
    setQuantumSphereReference(quantumSphereObjects) {
        this.quantumSphereRef = quantumSphereObjects;
        console.log('🎯 量子球系统引用已设置', Object.keys(quantumSphereObjects));
    }
    
    async init() {
        
        
        // 等待量子球系统就绪
        await this.waitForQuantumBallSystem();
        
        // 初始化WebSocket连接
        await this.initWebSocketConnection();
        
        // 设置聊天事件监听
        this.setupChatEventListeners();
        
        // 设置页面级事件监听
        this.setupPageEventListeners();
        
        
    }

    async waitForQuantumBallSystem() {
        return new Promise((resolve) => {
            const checkQuantumSystem = () => {
                // 检查量子球系统和动画系统是否存在
                const hasQuantumParticleSystem = window.quantumParticleSystem || 
                    document.getElementById('backgroundContainer') ||
                    window.QuantumParticleSystem;
                
                const hasQuantumAnimationSystem = window.quantumAnimation || 
                    window.QuantumAnimationSystem;
                
                if (hasQuantumParticleSystem && hasQuantumAnimationSystem) {
                    this.quantumBallSystem = window.quantumParticleSystem || 
                                           window.QuantumParticleSystem ||
                                           this.createQuantumBallProxy();
                    
                    
                    resolve();
                } else {
                    
                    setTimeout(checkQuantumSystem, 500);
                }
            };
            
            checkQuantumSystem();
        });
    }

    createQuantumBallProxy() {
        // 创建量子球系统代理，用于兼容性
        return {
            updateState: (state) => {
                
                this.triggerVisualFeedback(state);
            },
            triggerAnimation: (type, data) => {
                
                this.triggerChatEvent(type, data);
            }
        };
    }

    // 添加视觉反馈方法
    triggerVisualFeedback(state) {
        
        
        // 应用到背景容器
        const container = document.getElementById('backgroundContainer');
        if (container && state.mode) {
            container.className = `quantum-${state.mode}`;
            
            // 应用颜色变化
            if (state.colors && state.colors.length > 0) {
                this.applyColorAnimation(container, state.colors);
            }
            
            // 应用效果动画
            if (state.effects && state.effects.length > 0) {
                this.applyEffectAnimation(container, state.effects);
            }
        }
        
        // 应用到量子加载器
        const quantumLoader = document.getElementById('quantumLoader');
        if (quantumLoader && state.visible) {
            quantumLoader.style.display = 'flex';
            
            setTimeout(() => {
                quantumLoader.style.display = 'none';
            }, state.duration || 2000);
        }
    }
    
    async initWebSocketConnection() {
        try {
            const wsUrl = `ws://${window.location.host}/ws`;
            
            
            this.wsConnection = new WebSocket(wsUrl);
            
            this.wsConnection.onopen = () => {
                
                this.isConnected = true;
                this.reconnectAttempts = 0;
                
                // 发送初始化消息
                this.sendWebSocketMessage({
                    type: 'quantumBallSync',
                    action: 'init',
                    data: {
                        page: window.location.pathname,
                        timestamp: new Date().toISOString()
                    }
                });
                
                this.emit('connected');
            };

            this.wsConnection.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleWebSocketMessage(data);
                } catch (error) {
                    console.error('❌ WebSocket消息解析失败:', error);
                }
            };

            this.wsConnection.onclose = () => {
                
                this.isConnected = false;
                this.emit('disconnected');
                this.attemptReconnection();
            };

            this.wsConnection.onerror = (error) => {
                console.error('❌ WebSocket连接错误:', error);
                this.emit('error', error);
            };

        } catch (error) {
            console.error('❌ WebSocket初始化失败:', error);
            this.emit('error', error);
        }
    }

    async attemptReconnection() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.error('❌ WebSocket重连次数已达上限');
            return;
        }

        this.reconnectAttempts++;
        console.log(`🔄 尝试重连WebSocket (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);

        setTimeout(() => {
            this.initWebSocketConnection();
        }, this.reconnectInterval);
    }

    handleWebSocketMessage(data) {
        
        
        switch (data.type) {
            case 'quantumBallAnimation':
                this.handleQuantumBallAnimation(data);
                break;
            case 'chatResponse':
                this.handleChatResponse(data);
                break;
            case 'systemNotification':
                this.handleSystemNotification(data);
                break;
            default:
                
        }
    }

    handleQuantumBallAnimation(data) {
        
        
        if (data.animation) {
            this.addAnimationToQueue(data.eventType, data.animation, data.data);
        }
        
        this.emit('quantumBallAnimation', data);
    }

    handleChatResponse(data) {
        
        
        // 触发AI响应动画
        this.triggerChatEvent('aiResponseReceived', data);
        
        this.emit('chatResponse', data);
    }

    handleSystemNotification(data) {
        
        this.emit('systemNotification', data);
    }

    // 事件发射器
    emit(event, data) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`❌ 事件监听器执行失败 (${event}):`, error);
                }
            });
        }
    }

    // 事件监听器管理
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }

    off(event, callback) {
        if (this.eventListeners.has(event)) {
            const listeners = this.eventListeners.get(event);
            const index = listeners.indexOf(callback);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    // 动画队列管理
    addAnimationToQueue(eventType, animation, chatData) {
        this.animationQueue.push({
            type: eventType,
            animation: animation,
            data: chatData,
            timestamp: Date.now()
        });
        
        this.processAnimationQueue();
        this.emit('quantumBallAnimation', { eventType, animation, chatData });
    }
    
    async processAnimationQueue() {
        if (this.isAnimating || this.animationQueue.length === 0) {
            return;
        }
        
        this.isAnimating = true;
        
        while (this.animationQueue.length > 0) {
            const animationItem = this.animationQueue.shift();
            await this.executeAnimation(animationItem);
        }
        
        this.isAnimating = false;
    }

    async executeAnimation(animationItem) {
        const { type, animation, data } = animationItem;
        
        
        
        try {
            // 应用动画到量子球系统
            if (this.quantumBallSystem && this.quantumBallSystem.triggerAnimation) {
                this.quantumBallSystem.triggerAnimation(type, animation);
            } else {
                await this.executeAnimationFallback(type, animation);
            }
            
            // 等待动画完成
            if (animation.duration) {
                await new Promise(resolve => setTimeout(resolve, animation.duration));
            }
            
        } catch (error) {
            console.error('❌ 执行动画失败:', error);
        }
    }

    async executeAnimationFallback(type, animation) {
        // 后备动画实现
        const container = document.getElementById('backgroundContainer');
        if (!container) return;
        
        const animationClass = `quantum-animation-${type}`;
        container.classList.add(animationClass);
        
        // 应用样式变化
        if (animation.colors) {
            this.applyColorAnimation(container, animation.colors);
        }
        
        if (animation.effects) {
            this.applyEffectAnimation(container, animation.effects);
        }
        
        // 清理动画类
        setTimeout(() => {
            container.classList.remove(animationClass);
        }, animation.duration || 2000);
    }

    applyColorAnimation(container, colors) {
        
        
        // 创建渐变背景
        const gradient = `linear-gradient(45deg, ${colors.join(', ')})`;
        container.style.background = gradient;
        
        // 重置背景
        setTimeout(() => {
            container.style.background = '';
        }, 3000);
    }

    applyEffectAnimation(container, effects) {
        
        
        effects.forEach(effect => {
            container.classList.add(`effect-${effect}`);
        });
        
        // 清理效果
        setTimeout(() => {
            effects.forEach(effect => {
                container.classList.remove(`effect-${effect}`);
            });
        }, 2000);
    }

    setupChatEventListeners() {
        
        
        // 监听消息发送事件
        document.addEventListener('chatMessageSent', (event) => {
            this.handleChatMessageSent(event.detail);
        });
        
        // 监听AI响应事件
        document.addEventListener('chatResponseReceived', (event) => {
            this.handleChatResponseReceived(event.detail);
        });
        
        // 监听聊天错误事件
        document.addEventListener('chatError', (event) => {
            this.handleChatError(event.detail);
        });
        
        // 监听聊天模块的直接调用
        if (window.chatInstance) {
            this.integrateChatInstance(window.chatInstance);
        }
        
        // 监听聊天实例创建
        document.addEventListener('chatInstanceCreated', (event) => {
            this.integrateChatInstance(event.detail);
        });
    }

    integrateChatInstance(chatInstance) {
        
        
        this.chatSystem = chatInstance;
        
        // 如果聊天实例有事件系统，则集成
        if (chatInstance.core && chatInstance.core.on) {
            chatInstance.core.on('messageSent', (data) => {
                this.triggerChatEvent('userMessageSent', data);
            });
            
            chatInstance.core.on('responseReceived', (data) => {
                this.triggerChatEvent('aiResponseReceived', data);
            });
            
            chatInstance.core.on('error', (data) => {
                this.triggerChatEvent('chatError', data);
            });
        }
        
        // 覆盖聊天API调用以注入量子球事件
        if (chatInstance.api && chatInstance.api.sendMessage) {
            const originalSendMessage = chatInstance.api.sendMessage.bind(chatInstance.api);
            
            chatInstance.api.sendMessage = async (message, options = {}) => {
                // 触发用户消息动画
                this.triggerChatEvent('userMessageSent', { message, options });
                
                try {
                    // 触发AI思考动画
                    this.triggerChatEvent('aiThinking', { message });
                    
                    const result = await originalSendMessage(message, options);
                    
                    // 触发AI响应动画
                    this.triggerChatEvent('aiResponseReceived', { 
                        message, 
                        response: result,
                        options 
                    });
                    
                    return result;
                } catch (error) {
                    // 触发错误动画
                    this.triggerChatEvent('chatError', { message, error, options });
                    throw error;
                }
            };
        }
    }
    
    handleChatMessageSent(data) {
        
        this.triggerChatEvent('userMessageSent', data);
    }
    
    handleChatResponseReceived(data) {
        
        this.triggerChatEvent('aiResponseReceived', data);
    }
    
    handleChatError(data) {
        
        this.triggerChatEvent('chatError', data);
    }

    triggerChatEvent(eventType, data) {
        
        
        // 首先尝试直接应用到首页量子球系统
        if (this.quantumSphereRef) {
            this.applyAnimationToQuantumSphere(eventType, data);
        }
        
        // 如果WebSocket连接可用，也通过WebSocket发送
        if (this.isConnected && this.wsConnection) {
            this.sendWebSocketMessage({
                type: 'chatEvent',
                eventType: eventType,
                data: data,
                timestamp: new Date().toISOString()
            });
        } else {
            console.log('🎨 使用本地动画效果 (WebSocket未连接)');
            // 使用本地动画效果作为fallback
            this.applyLocalAnimationEffect(eventType, data);
        }
        
        // 触发自定义事件，供其他模块监听
        document.dispatchEvent(new CustomEvent('quantumChatEvent', {
            detail: { eventType, data }
        }));
        
        // 根据事件类型调用相应的动画方法
        switch (eventType) {
            case 'userMessageSent':
                this.triggerUserMessageAnimation(data);
                break;
            case 'aiThinking':
                this.triggerAIThinkingAnimation();
                break;
            case 'aiResponseReceived':
                this.triggerAIResponseAnimation(data);
                break;
            case 'chatError':
                this.triggerErrorAnimation(data);
                break;
        }
        
        // 发送事件到WebSocket（如果连接）
        if (this.wsConnection && this.wsConnection.readyState === WebSocket.OPEN) {
            this.wsConnection.send(JSON.stringify({
                type: 'chat_event',
                eventType: eventType,
                data: data,
                timestamp: Date.now()
            }));
        }
        
        return true;
    }

    // 本地动画效果fallback
    applyLocalAnimationEffect(eventType, data) {
        
        
        const container = document.getElementById('backgroundContainer');
        if (container) {
            container.classList.add(`quantum-${eventType}`);
            
            setTimeout(() => {
                container.classList.remove(`quantum-${eventType}`);
            }, 2000);
        }
    }

    applyAnimationToQuantumSphere(eventType, data) {
        
        
        if (!this.quantumSphereRef) return;
        
        // 根据事件类型应用不同的动画
        switch (eventType) {
            case 'userMessageSent':
                this.applyUserMessageAnimation(this.quantumSphereRef, data);
                break;
            case 'aiThinking':
                this.applyAIThinkingAnimation(this.quantumSphereRef, data);
                break;
            case 'aiResponseReceived':
                this.applyAIResponseAnimation(this.quantumSphereRef, data);
                break;
            case 'chatError':
                this.applyErrorAnimation(this.quantumSphereRef, data);
                break;
        }
    }

    applyUserMessageAnimation(quantumSphere, data) {
        
        // 实现用户消息的量子球动画
    }

    applyAIThinkingAnimation(waveForm, connectionLines) {
        
        // 实现AI思考的量子球动画
    }

    applyAIResponseAnimation(quantumSphere, lightBeams) {
        
        // 实现AI响应的量子球动画
    }

    applyErrorAnimation(quantumSphere, particleCloud) {
        
        // 实现错误的量子球动画
    }

    setupPageEventListeners() {
        
        
        // 监听页面可见性变化
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.triggerQuantumBallMode('sleep');
            } else {
                this.triggerQuantumBallMode('active');
            }
        });
        
        // 监听窗口大小变化
        window.addEventListener('resize', () => {
            this.triggerQuantumBallMode('resize');
        });
    }

    triggerQuantumBallMode(mode) {
        
        
        this.sendWebSocketMessage({
            type: 'quantumBallMode',
            mode: mode,
            timestamp: new Date().toISOString()
        });
        
        this.emit('quantumBallModeChange', { mode });
    }

    sendWebSocketMessage(message) {
        if (this.wsConnection && this.wsConnection.readyState === WebSocket.OPEN) {
            this.wsConnection.send(JSON.stringify(message));
        } else {
            console.warn('⚠️ WebSocket未连接，无法发送消息:', message);
        }
    }

    // 动画触发方法（向后兼容）
    triggerUserMessageAnimation(data) {
        
        
        const animation = {
            type: 'userMessage',
            colors: ['#4CAF50', '#81C784'],
            effects: ['pulse', 'glow'],
            duration: 1500
        };
        
        this.addAnimationToQueue('userMessageSent', animation, data);
    }

    triggerAIThinkingAnimation() {
        
        
        const animation = {
            type: 'aiThinking',
            colors: ['#FF9800', '#FFB74D'],
            effects: ['wave', 'shimmer'],
            duration: 2000
        };
        
        this.addAnimationToQueue('aiThinking', animation, {});
    }

    triggerAIResponseAnimation(data) {
        
        
        const animation = {
            type: 'aiResponse',
            colors: ['#2196F3', '#64B5F6'],
            effects: ['burst', 'sparkle'],
            duration: 1800
        };
        
        this.addAnimationToQueue('aiResponseReceived', animation, data);
    }

    triggerErrorAnimation(data) {
        
        
        const animation = {
            type: 'error',
            colors: ['#F44336', '#EF5350'],
            effects: ['shake', 'fade'],
            duration: 1000
        };
        
        this.addAnimationToQueue('chatError', animation, data);
    }

    triggerQuantumAnimation(type, data = {}) {
        
        
        const defaultAnimations = {
            click: {
                colors: ['#9C27B0', '#BA68C8'],
                effects: ['ripple', 'zoom'],
                duration: 1000
            },
            hover: {
                colors: ['#673AB7', '#9575CD'],
                effects: ['glow'],
                duration: 500
            },
            idle: {
                colors: ['#3F51B5', '#7986CB'],
                effects: ['breathe'],
                duration: 3000
            }
        };
        
        const animation = { ...defaultAnimations[type], ...data };
        this.addAnimationToQueue(type, animation, data);
    }

    // 检查认证状态
    async checkAuthentication() {
        try {
            const response = await fetch('/api/v1/auth/check', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.isAuthenticated = result.success && result.data?.authenticated;
                return this.isAuthenticated;
            }
        } catch (error) {
            console.warn('认证检查失败:', error);
        }
        
        this.isAuthenticated = false;
        return false;
    }

    // 调试和测试方法
    testIntegration() {
        
        
        const testAnimations = [
            'userMessageSent',
            'aiThinking',
            'aiResponseReceived',
            'chatError'
        ];
        
        testAnimations.forEach((animation, index) => {
            setTimeout(() => {
                this.triggerChatEvent(animation, {
                    test: true,
                    message: `测试动画: ${animation}`,
                    timestamp: new Date().toISOString()
                });
            }, index * 3000);
        });
    }

    getSystemStatus() {
        return {
            isConnected: this.isConnected,
            isAuthenticated: this.isAuthenticated,
            reconnectAttempts: this.reconnectAttempts,
            hasQuantumBallSystem: !!this.quantumBallSystem,
            hasChatSystem: !!this.chatSystem,
            animationQueueLength: this.animationQueue.length,
            isAnimating: this.isAnimating,
            currentPage: window.location.pathname
        };
    }
    
    // 公共初始化方法 - 供外部调用
    async initialize() {
        
        try {
            // 如果已经初始化过，直接返回
            if (this.isConnected) {
                
                return Promise.resolve();
            }
            
            // 检查认证状态
            await this.checkAuthentication();
            
            // 调用内部初始化方法
            await this.init();
            
            
            return Promise.resolve();
        } catch (error) {
            console.error('❌ QuantumChatIntegrator 初始化失败:', error);
            throw error;
        }
    }
    
    // localStorage数据管理方法
    saveToLocalStorage(key, data) {
        try {
            localStorage.setItem(key, JSON.stringify(data));
            
        } catch (error) {
            console.warn('localStorage保存失败:', error);
        }
    }
    
    loadFromLocalStorage(key) {
        try {
            const data = localStorage.getItem(key);
            return data ? JSON.parse(data) : null;
        } catch (error) {
            console.warn('localStorage读取失败:', error);
            return null;
        }
    }
    
    removeFromLocalStorage(key) {
        try {
            localStorage.removeItem(key);
            
        } catch (error) {
            console.warn('localStorage删除失败:', error);
        }
    }
}

// 全局初始化
let quantumChatIntegrator = null;

// 页面加载时自动初始化
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        try {
            quantumChatIntegrator = new QuantumChatIntegrator();
            window.quantumChatIntegrator = quantumChatIntegrator;
            
            
            
            // 通知其他模块集成器已就绪
            document.dispatchEvent(new CustomEvent('quantumChatIntegratorReady', {
                detail: quantumChatIntegrator
            }));
        } catch (error) {
            console.error('❌ 量子球-聊天集成器初始化失败:', error);
        }
    }, 1000); // 延迟1秒确保其他系统初始化完成
});

// 添加全局初始化方法供外部调用
if (typeof window !== 'undefined') {
    window.initializeQuantumChatIntegrator = function() {
        if (window.quantumChatIntegrator) {
            
            return window.quantumChatIntegrator;
        } else {
            console.warn('⚠️ 量子球-聊天集成器尚未就绪，请稍后重试');
            return null;
        }
    };
}

// 导出给其他模块使用
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QuantumChatIntegrator;
}

// 确保类在全局作用域中可用
if (typeof window !== 'undefined') {
    window.QuantumChatIntegrator = QuantumChatIntegrator;
}


