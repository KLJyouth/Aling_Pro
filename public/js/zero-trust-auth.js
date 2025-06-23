/**
 * 零信任认证系统
 * 实现多因素认证、设备指纹、行为分析等安全特性
 */

class ZeroTrustAuth {
    constructor() {
        this.config = {
            deviceFingerprintEnabled: true,
            behaviorAnalysisEnabled: true,
            multiFactorEnabled: true,
            riskBasedAuth: true,
            sessionMonitoring: true,
            maxFailedAttempts: 5,
            lockoutDuration: 30 * 60 * 1000, // 30分钟
            sessionTimeout: 24 * 60 * 60 * 1000, // 24小时
            riskThreshold: 0.7
        };
        
        this.authFactors = new Map();
        this.securityMetrics = {
            loginAttempts: 0,
            failedAttempts: 0,
            lastLoginTime: null,
            deviceChanges: 0,
            locationChanges: 0,
            behaviorScore: 1.0,
            riskScore: 0.0
        };
        
        this.deviceFingerprint = null;
        this.sessionData = null;
        this.behaviorPatterns = [];
        
        this.init();
    }

    // ==================== 初始化 ====================

    async init() {
        
        
        try {
            await this.generateDeviceFingerprint();
            this.loadSecurityMetrics();
            this.setupSessionMonitoring();
            this.setupBehaviorAnalysis();
            this.setupEventListeners();
            
            
        } catch (error) {
            console.error('❌ 零信任认证系统初始化失败:', error);
        }
    }

    setupEventListeners() {
        // 监听用户活动
        document.addEventListener('mousemove', (e) => this.trackBehavior('mousemove', e));
        document.addEventListener('keydown', (e) => this.trackBehavior('keydown', e));
        document.addEventListener('click', (e) => this.trackBehavior('click', e));
        
        // 监听页面可见性变化
        document.addEventListener('visibilitychange', () => {
            this.trackBehavior('visibility', { hidden: document.hidden });
        });
        
        // 监听网络状态变化
        window.addEventListener('online', () => this.trackBehavior('network', { online: true }));
        window.addEventListener('offline', () => this.trackBehavior('network', { online: false }));
    }

    // ==================== 设备指纹 ====================

    async generateDeviceFingerprint() {
        try {
            const fingerprint = {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                cookieEnabled: navigator.cookieEnabled,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                screen: {
                    width: screen.width,
                    height: screen.height,
                    colorDepth: screen.colorDepth,
                    pixelDepth: screen.pixelDepth
                },
                canvas: await this.generateCanvasFingerprint(),
                webgl: await this.generateWebGLFingerprint(),
                audio: await this.generateAudioFingerprint(),
                fonts: await this.getAvailableFonts(),
                plugins: this.getPluginInfo(),
                hardware: await this.getHardwareInfo(),
                timestamp: Date.now()
            };

            this.deviceFingerprint = await this.hashFingerprint(fingerprint);
            
            // 存储设备指纹
            this.storeDeviceFingerprint(this.deviceFingerprint);
            
            console.log('🔍 设备指纹生成完成:', this.deviceFingerprint.substring(0, 16) + '...');
            
            return this.deviceFingerprint;
        } catch (error) {
            console.error('设备指纹生成失败:', error);
            this.deviceFingerprint = 'fallback_' + Date.now();
            return this.deviceFingerprint;
        }
    }

    async generateCanvasFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = 200;
            canvas.height = 50;
            
            // 绘制指纹图案
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.textBaseline = 'alphabetic';
            ctx.fillStyle = '#f60';
            ctx.fillRect(125, 1, 62, 20);
            ctx.fillStyle = '#069';
            ctx.fillText('AlingAi Zero Trust 🔐', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('Security Fingerprint', 4, 35);
            
            return canvas.toDataURL();
        } catch (error) {
            console.warn('Canvas指纹生成失败:', error);
            return 'canvas_error';
        }
    }

    async generateWebGLFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (!gl) return 'webgl_not_supported';
            
            const renderer = gl.getParameter(gl.RENDERER);
            const vendor = gl.getParameter(gl.VENDOR);
            const version = gl.getParameter(gl.VERSION);
            const shadingLanguageVersion = gl.getParameter(gl.SHADING_LANGUAGE_VERSION);
            
            return btoa(JSON.stringify({
                renderer,
                vendor,
                version,
                shadingLanguageVersion
            }));
        } catch (error) {
            console.warn('WebGL指纹生成失败:', error);
            return 'webgl_error';
        }
    }

    async generateAudioFingerprint() {
        return new Promise((resolve) => {
            try {
                const context = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = context.createOscillator();
                const analyser = context.createAnalyser();
                const gainNode = context.createGain();
                const scriptProcessor = context.createScriptProcessor(4096, 1, 1);
                
                oscillator.type = 'triangle';
                oscillator.frequency.setValueAtTime(10000, context.currentTime);
                
                gainNode.gain.setValueAtTime(0, context.currentTime);
                
                oscillator.connect(analyser);
                analyser.connect(scriptProcessor);
                scriptProcessor.connect(gainNode);
                gainNode.connect(context.destination);
                
                scriptProcessor.onaudioprocess = function(bins) {
                    const data = new Float32Array(analyser.frequencyBinCount);
                    analyser.getFloatFrequencyData(data);
                    
                    const fingerprint = Array.from(data.slice(0, 50))
                        .map(x => Math.round(x * 1000))
                        .join(',');
                    
                    oscillator.disconnect();
                    scriptProcessor.disconnect();
                    context.close();
                    
                    resolve(btoa(fingerprint));
                };
                
                oscillator.start();
                
                setTimeout(() => {
                    resolve('audio_timeout');
                }, 1000);
            } catch (error) {
                console.warn('音频指纹生成失败:', error);
                resolve('audio_error');
            }
        });
    }

    async getAvailableFonts() {
        const testFonts = [
            'Arial', 'Helvetica', 'Times New Roman', 'Courier New', 'Verdana',
            'Georgia', 'Palatino', 'Garamond', 'Bookman', 'Comic Sans MS',
            'Trebuchet MS', 'Arial Black', 'Impact', 'Microsoft Sans Serif',
            'Tahoma', 'Monaco', 'Courier', 'Lucida Console'
        ];
        
        const availableFonts = [];
        
        for (const font of testFonts) {
            if (await this.isFontAvailable(font)) {
                availableFonts.push(font);
            }
        }
        
        return availableFonts.join(',');
    }

    async isFontAvailable(fontName) {
        const testString = 'mmmmmmmmmmlli';
        const testSize = '72px';
        const baseFonts = ['monospace', 'sans-serif', 'serif'];
        
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        
        for (const baseFont of baseFonts) {
            context.font = `${testSize} ${baseFont}`;
            const baseWidth = context.measureText(testString).width;
            
            context.font = `${testSize} ${fontName}, ${baseFont}`;
            const testWidth = context.measureText(testString).width;
            
            if (baseWidth !== testWidth) {
                return true;
            }
        }
        
        return false;
    }

    getPluginInfo() {
        const plugins = [];
        for (let i = 0; i < navigator.plugins.length; i++) {
            const plugin = navigator.plugins[i];
            plugins.push({
                name: plugin.name,
                description: plugin.description,
                filename: plugin.filename
            });
        }
        return JSON.stringify(plugins);
    }

    async getHardwareInfo() {
        const info = {
            hardwareConcurrency: navigator.hardwareConcurrency || 0,
            deviceMemory: navigator.deviceMemory || 0,
            maxTouchPoints: navigator.maxTouchPoints || 0
        };
        
        // 获取电池信息（如果可用）
        if ('getBattery' in navigator) {
            try {
                const battery = await navigator.getBattery();
                info.battery = {
                    charging: battery.charging,
                    level: Math.round(battery.level * 100),
                    chargingTime: battery.chargingTime,
                    dischargingTime: battery.dischargingTime
                };
            } catch (error) {
                info.battery = 'unavailable';
            }
        }
        
        return info;
    }

    async hashFingerprint(fingerprint) {
        const encoder = new TextEncoder();
        const data = encoder.encode(JSON.stringify(fingerprint));
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    storeDeviceFingerprint(fingerprint) {
        try {
            localStorage.setItem('deviceFingerprint', fingerprint);
            localStorage.setItem('fingerprintTimestamp', Date.now().toString());
        } catch (error) {
            console.warn('设备指纹存储失败:', error);
        }
    }

    // ==================== 行为分析 ====================

    setupBehaviorAnalysis() {
        this.behaviorMetrics = {
            mouseMovements: [],
            keystrokes: [],
            clicks: [],
            scrollEvents: [],
            typingPattern: {
                averageSpeed: 0,
                rhythm: [],
                pauses: []
            },
            sessionDuration: 0,
            activityLevel: 0
        };
        
        // 启动行为分析定时器
        this.behaviorTimer = setInterval(() => {
            this.analyzeBehavior();
        }, 30000); // 每30秒分析一次
    }

    trackBehavior(type, data) {
        const timestamp = Date.now();
        
        switch (type) {
            case 'mousemove':
                this.behaviorMetrics.mouseMovements.push({
                    x: data.clientX,
                    y: data.clientY,
                    timestamp
                });
                // 保持最近100个移动记录
                if (this.behaviorMetrics.mouseMovements.length > 100) {
                    this.behaviorMetrics.mouseMovements.shift();
                }
                break;
                
            case 'keydown':
                this.behaviorMetrics.keystrokes.push({
                    key: data.key,
                    keyCode: data.keyCode,
                    timestamp
                });
                // 保持最近50个按键记录
                if (this.behaviorMetrics.keystrokes.length > 50) {
                    this.behaviorMetrics.keystrokes.shift();
                }
                this.updateTypingPattern();
                break;
                
            case 'click':
                this.behaviorMetrics.clicks.push({
                    x: data.clientX,
                    y: data.clientY,
                    button: data.button,
                    timestamp
                });
                // 保持最近20个点击记录
                if (this.behaviorMetrics.clicks.length > 20) {
                    this.behaviorMetrics.clicks.shift();
                }
                break;
                
            case 'scroll':
                this.behaviorMetrics.scrollEvents.push({
                    scrollX: window.scrollX,
                    scrollY: window.scrollY,
                    timestamp
                });
                // 保持最近30个滚动记录
                if (this.behaviorMetrics.scrollEvents.length > 30) {
                    this.behaviorMetrics.scrollEvents.shift();
                }
                break;
        }
        
        this.behaviorPatterns.push({ type, timestamp });
        // 保持最近200个行为记录
        if (this.behaviorPatterns.length > 200) {
            this.behaviorPatterns.shift();
        }
    }

    updateTypingPattern() {
        const keystrokes = this.behaviorMetrics.keystrokes;
        if (keystrokes.length < 2) return;
        
        const intervals = [];
        for (let i = 1; i < keystrokes.length; i++) {
            intervals.push(keystrokes[i].timestamp - keystrokes[i-1].timestamp);
        }
        
        if (intervals.length > 0) {
            const avgSpeed = intervals.reduce((a, b) => a + b, 0) / intervals.length;
            this.behaviorMetrics.typingPattern.averageSpeed = avgSpeed;
            this.behaviorMetrics.typingPattern.rhythm = intervals.slice(-10); // 最近10个间隔
        }
    }

    analyzeBehavior() {
        const now = Date.now();
        const timeWindow = 60000; // 1分钟窗口
        
        // 计算活动级别
        const recentActivities = this.behaviorPatterns.filter(
            pattern => now - pattern.timestamp < timeWindow
        );
        
        this.behaviorMetrics.activityLevel = recentActivities.length;
        
        // 计算行为分数
        this.calculateBehaviorScore();
        
        // 更新风险评分
        this.updateRiskScore();
    }

    calculateBehaviorScore() {
        let score = 1.0;
        
        // 活动级别分析
        if (this.behaviorMetrics.activityLevel === 0) {
            score -= 0.3; // 无活动降低分数
        } else if (this.behaviorMetrics.activityLevel > 100) {
            score -= 0.2; // 过度活动也可能是异常
        }
        
        // 鼠标移动模式分析
        if (this.behaviorMetrics.mouseMovements.length > 10) {
            const movements = this.behaviorMetrics.mouseMovements;
            const distances = [];
            
            for (let i = 1; i < movements.length; i++) {
                const dx = movements[i].x - movements[i-1].x;
                const dy = movements[i].y - movements[i-1].y;
                distances.push(Math.sqrt(dx*dx + dy*dy));
            }
            
            const avgDistance = distances.reduce((a, b) => a + b, 0) / distances.length;
            
            // 正常鼠标移动距离应该在一定范围内
            if (avgDistance < 5 || avgDistance > 200) {
                score -= 0.1;
            }
        }
        
        // 打字模式分析
        if (this.behaviorMetrics.typingPattern.averageSpeed > 0) {
            const speed = this.behaviorMetrics.typingPattern.averageSpeed;
            // 正常打字速度: 50-300ms
            if (speed < 50 || speed > 300) {
                score -= 0.1;
            }
        }
        
        this.securityMetrics.behaviorScore = Math.max(0, Math.min(1, score));
    }

    // ==================== 风险评估 ====================

    updateRiskScore() {
        let riskScore = 0;
        
        // 设备变化风险
        if (this.securityMetrics.deviceChanges > 0) {
            riskScore += this.securityMetrics.deviceChanges * 0.2;
        }
        
        // 位置变化风险
        if (this.securityMetrics.locationChanges > 2) {
            riskScore += (this.securityMetrics.locationChanges - 2) * 0.1;
        }
        
        // 失败登录次数
        if (this.securityMetrics.failedAttempts > 0) {
            riskScore += this.securityMetrics.failedAttempts * 0.15;
        }
        
        // 行为异常
        if (this.securityMetrics.behaviorScore < 0.7) {
            riskScore += (0.7 - this.securityMetrics.behaviorScore) * 0.5;
        }
        
        // 时间异常（非正常时间登录）
        const hour = new Date().getHours();
        if (hour < 6 || hour > 23) {
            riskScore += 0.1;
        }
        
        this.securityMetrics.riskScore = Math.min(1, riskScore);
        
        
    }

    // ==================== 多因素认证 ====================

    async requestMFA(loginData) {
        const riskScore = this.securityMetrics.riskScore;
        const isHighRisk = riskScore > this.config.riskThreshold;
        
        
        
        if (!isHighRisk && this.isTrustedDevice()) {
            return { required: false, factors: [] };
        }
        
        const factors = [];
        
        // 基于风险级别确定需要的认证因素
        if (riskScore > 0.8) {
            factors.push('sms', 'email', 'totp');
        } else if (riskScore > 0.5) {
            factors.push('email', 'totp');
        } else {
            factors.push('email');
        }
        
        return {
            required: true,
            factors,
            riskScore,
            challengeId: this.generateChallengeId()
        };
    }

    generateChallengeId() {
        return 'chg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    async verifyMFAFactor(challengeId, factor, value) {
        // 这里应该调用后端API验证
        console.log('验证MFA因素:', { challengeId, factor, value: value.substring(0, 3) + '...' });
        
        // 模拟验证过程
        return new Promise((resolve) => {
            setTimeout(() => {
                // 简单的模拟验证
                const isValid = value.length >= 4;
                resolve({
                    success: isValid,
                    factor,
                    challengeId
                });
            }, 1000);
        });
    }

    // ==================== 设备信任管理 ====================

    isTrustedDevice() {
        const storedFingerprint = localStorage.getItem('trustedDeviceFingerprint');
        const fingerprintTimestamp = localStorage.getItem('trustedDeviceTimestamp');
        
        if (!storedFingerprint || !fingerprintTimestamp) {
            return false;
        }
        
        // 检查设备信任是否过期（30天）
        const trustExpiry = 30 * 24 * 60 * 60 * 1000;
        if (Date.now() - parseInt(fingerprintTimestamp) > trustExpiry) {
            this.removeTrustedDevice();
            return false;
        }
        
        return storedFingerprint === this.deviceFingerprint;
    }

    markDeviceAsTrusted() {
        try {
            localStorage.setItem('trustedDeviceFingerprint', this.deviceFingerprint);
            localStorage.setItem('trustedDeviceTimestamp', Date.now().toString());
            
        } catch (error) {
            console.error('标记信任设备失败:', error);
        }
    }

    removeTrustedDevice() {
        try {
            localStorage.removeItem('trustedDeviceFingerprint');
            localStorage.removeItem('trustedDeviceTimestamp');
            
        } catch (error) {
            console.error('移除信任设备失败:', error);
        }
    }

    // ==================== 会话监控 ====================

    setupSessionMonitoring() {
        this.sessionData = {
            startTime: Date.now(),
            lastActivity: Date.now(),
            isActive: true,
            warnings: []
        };
        
        // 定期检查会话状态
        this.sessionMonitor = setInterval(() => {
            this.checkSessionSecurity();
        }, 60000); // 每分钟检查一次
        
        // 监听用户活动
        const updateActivity = () => {
            this.sessionData.lastActivity = Date.now();
        };
        
        document.addEventListener('mousemove', updateActivity);
        document.addEventListener('keydown', updateActivity);
        document.addEventListener('click', updateActivity);
    }

    checkSessionSecurity() {
        const now = Date.now();
        const inactiveTime = now - this.sessionData.lastActivity;
        
        // 检查会话超时
        if (inactiveTime > this.config.sessionTimeout) {
            this.handleSessionTimeout();
            return;
        }
        
        // 检查设备变化
        if (this.deviceFingerprint !== localStorage.getItem('deviceFingerprint')) {
            this.handleDeviceChange();
            return;
        }
        
        // 检查异常行为
        if (this.securityMetrics.riskScore > this.config.riskThreshold) {
            this.handleHighRiskBehavior();
        }
    }

    handleSessionTimeout() {
        console.warn('⚠️ 会话超时');
        this.sessionData.warnings.push({
            type: 'timeout',
            timestamp: Date.now(),
            message: '会话因长时间不活动而超时'
        });
        
        // 可以触发重新认证或自动登出
        this.triggerReAuthentication('session_timeout');
    }

    handleDeviceChange() {
        console.warn('⚠️ 检测到设备变化');
        this.securityMetrics.deviceChanges++;
        this.sessionData.warnings.push({
            type: 'device_change',
            timestamp: Date.now(),
            message: '检测到设备指纹变化'
        });
        
        this.triggerReAuthentication('device_change');
    }

    handleHighRiskBehavior() {
        console.warn('⚠️ 检测到高风险行为');
        this.sessionData.warnings.push({
            type: 'high_risk',
            timestamp: Date.now(),
            message: '检测到异常行为模式',
            riskScore: this.securityMetrics.riskScore
        });
        
        // 高风险行为可能需要额外验证
        if (this.securityMetrics.riskScore > 0.9) {
            this.triggerReAuthentication('high_risk');
        }
    }

    triggerReAuthentication(reason) {
        
        
        // 发送重新认证事件
        window.dispatchEvent(new CustomEvent('zeroTrustReAuth', {
            detail: {
                reason,
                riskScore: this.securityMetrics.riskScore,
                timestamp: Date.now()
            }
        }));
    }

    // ==================== 数据持久化 ====================

    loadSecurityMetrics() {
        try {
            const stored = localStorage.getItem('securityMetrics');
            if (stored) {
                this.securityMetrics = { ...this.securityMetrics, ...JSON.parse(stored) };
            }
        } catch (error) {
            console.warn('加载安全指标失败:', error);
        }
    }

    saveSecurityMetrics() {
        try {
            localStorage.setItem('securityMetrics', JSON.stringify(this.securityMetrics));
        } catch (error) {
            console.warn('保存安全指标失败:', error);
        }
    }

    // ==================== 公共接口 ====================

    async authenticate(credentials) {
        
        
        try {
            // 1. 更新安全指标
            this.securityMetrics.loginAttempts++;
            
            // 2. 检查账户锁定
            if (this.isAccountLocked()) {
                throw new Error('账户已被锁定，请稍后再试');
            }
            
            // 3. 生成或验证设备指纹
            if (!this.deviceFingerprint) {
                await this.generateDeviceFingerprint();
            }
            
            // 4. 检查是否需要MFA
            const mfaRequirement = await this.requestMFA(credentials);
            
            // 5. 构建认证请求
            const authRequest = {
                ...credentials,
                deviceFingerprint: this.deviceFingerprint,
                securityMetrics: this.securityMetrics,
                behaviorMetrics: this.behaviorMetrics,
                mfaRequired: mfaRequirement.required,
                riskScore: this.securityMetrics.riskScore,
                timestamp: Date.now()
            };
            
            return {
                authRequest,
                mfaRequirement,
                securityContext: {
                    riskScore: this.securityMetrics.riskScore,
                    deviceTrusted: this.isTrustedDevice(),
                    behaviorScore: this.securityMetrics.behaviorScore
                }
            };
            
        } catch (error) {
            this.securityMetrics.failedAttempts++;
            this.saveSecurityMetrics();
            throw error;
        }
    }

    isAccountLocked() {
        const now = Date.now();
        const lastFailedAttempt = this.securityMetrics.lastFailedAttempt || 0;
        
        if (this.securityMetrics.failedAttempts >= this.config.maxFailedAttempts) {
            if (now - lastFailedAttempt < this.config.lockoutDuration) {
                return true;
            } else {
                // 锁定期已过，重置失败次数
                this.securityMetrics.failedAttempts = 0;
                this.saveSecurityMetrics();
            }
        }
        
        return false;
    }

    onAuthSuccess(authResult) {
        
        
        // 重置失败计数
        this.securityMetrics.failedAttempts = 0;
        this.securityMetrics.lastLoginTime = Date.now();
        
        // 如果用户选择信任设备
        if (authResult.trustDevice) {
            this.markDeviceAsTrusted();
        }
        
        // 保存安全指标
        this.saveSecurityMetrics();
        
        // 启动会话监控
        this.sessionData.isActive = true;
    }

    onAuthFailure(error) {
        console.error('❌ 认证失败:', error);
        
        this.securityMetrics.failedAttempts++;
        this.securityMetrics.lastFailedAttempt = Date.now();
        
        // 更新风险评分
        this.updateRiskScore();
        
        // 保存安全指标
        this.saveSecurityMetrics();
    }

    // ==================== 安全报告 ====================

    generateSecurityReport() {
        return {
            timestamp: Date.now(),
            deviceFingerprint: this.deviceFingerprint?.substring(0, 16) + '...',
            securityMetrics: { ...this.securityMetrics },
            sessionData: { ...this.sessionData },
            config: { ...this.config },
            warnings: this.sessionData.warnings || [],
            recommendations: this.generateSecurityRecommendations()
        };
    }

    generateSecurityRecommendations() {
        const recommendations = [];
        
        if (this.securityMetrics.riskScore > 0.7) {
            recommendations.push('当前风险级别较高，建议启用额外的安全验证');
        }
        
        if (this.securityMetrics.failedAttempts > 2) {
            recommendations.push('检测到多次登录失败，建议检查账户安全');
        }
        
        if (!this.isTrustedDevice()) {
            recommendations.push('当前设备未被信任，建议完成设备验证');
        }
        
        if (this.securityMetrics.behaviorScore < 0.6) {
            recommendations.push('检测到异常行为模式，建议进行安全检查');
        }
        
        return recommendations;
    }

    // ==================== 清理方法 ====================

    destroy() {
        
        
        if (this.behaviorTimer) {
            clearInterval(this.behaviorTimer);
        }
        
        if (this.sessionMonitor) {
            clearInterval(this.sessionMonitor);
        }
        
        // 移除事件监听器
        document.removeEventListener('mousemove', this.trackBehavior);
        document.removeEventListener('keydown', this.trackBehavior);
        document.removeEventListener('click', this.trackBehavior);
        
        this.saveSecurityMetrics();
    }
}

// 全局实例
window.ZeroTrustAuth = ZeroTrustAuth;


