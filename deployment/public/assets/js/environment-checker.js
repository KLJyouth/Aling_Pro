/**
 * 环境检测器 - 用户环境和系统兼容性检测
 */

class EnvironmentChecker {
    constructor() {
        this.checks = [];
        this.currentCheck = 0;
        this.errors = [];
        this.userInfo = {};
        this.startTime = Date.now();
    }

    // 添加检测项
    addCheck(name, description, checkFunction) {
        this.checks.push({
            name,
            description,
            checkFunction,
            status: 'pending', // pending, checking, success, error
            result: null,
            error: null,
            duration: 0
        });
    }

    // 初始化所有检测项
    initializeChecks() {
        this.addCheck('network', '网络连接检测', () => this.checkNetworkConnection());
        this.addCheck('browser', '浏览器兼容性检测', () => this.checkBrowserCompatibility());
        this.addCheck('javascript', 'JavaScript功能检测', () => this.checkJavaScriptFeatures());
        this.addCheck('websocket', 'WebSocket支持检测', () => this.checkWebSocketSupport());
        this.addCheck('storage', '本地存储检测', () => this.checkLocalStorage());
        this.addCheck('media', '媒体功能检测', () => this.checkMediaSupport());
        this.addCheck('security', '安全策略检测', () => this.checkSecurityFeatures());
        this.addCheck('api', 'API服务检测', () => this.checkAPIEndpoints());
        this.addCheck('database', '数据库连接检测', () => this.checkDatabaseConnection());
        this.addCheck('performance', '性能基准测试', () => this.checkPerformance());
    }

    // 更新UI
    updateUI() {
        const checkList = document.getElementById('checkList');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        // 清空检测列表
        checkList.innerHTML = '';
        
        // 创建检测项UI
        this.checks.forEach((check, index) => {
            const checkItem = document.createElement('div');
            checkItem.className = `check-item ${check.status}`;
            checkItem.id = `check-${index}`;
            
            let iconHtml = '';
            switch (check.status) {
                case 'checking':
                    iconHtml = '<div class="spinner-border spinner-border-sm" role="status"></div>';
                    break;
                case 'success':
                    iconHtml = '<i class="bi bi-check-lg"></i>';
                    break;
                case 'error':
                    iconHtml = '<i class="bi bi-x-lg"></i>';
                    break;
                default:
                    iconHtml = '<i class="bi bi-clock"></i>';
            }
            
            checkItem.innerHTML = `
                <div class="check-icon ${check.status}">
                    ${iconHtml}
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold">${check.description}</div>
                    <div class="detail-info">
                        ${check.status === 'success' ? '✓ 检测通过' : 
                          check.status === 'error' ? `✗ ${check.error || '检测失败'}` :
                          check.status === 'checking' ? '正在检测...' : '等待检测'}
                        ${check.duration > 0 ? ` (${check.duration}ms)` : ''}
                    </div>
                </div>
            `;
            
            checkList.appendChild(checkItem);
        });
        
        // 更新进度条
        const progress = (this.currentCheck / this.checks.length) * 100;
        progressBar.style.width = `${progress}%`;
        
        if (this.currentCheck < this.checks.length) {
            progressText.textContent = `正在进行检测 (${this.currentCheck + 1}/${this.checks.length})...`;
        } else {
            const errorCount = this.checks.filter(check => check.status === 'error').length;
            if (errorCount === 0) {
                progressText.textContent = '所有检测已完成！系统环境良好。';
            } else {
                progressText.textContent = `检测完成，发现 ${errorCount} 个问题需要处理。`;
            }
        }
    }

    // 获取用户信息
    getUserInfo() {
        return new Promise((resolve) => {
            // 获取用户IP和基本信息
            fetch('/api/user-info')
                .then(response => response.json())
                .then(data => {
                    this.userInfo = {
                        ip: data.ip || 'unknown',
                        userAgent: navigator.userAgent,
                        language: navigator.language,
                        platform: navigator.platform,
                        screen: `${screen.width}x${screen.height}`,
                        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                        timestamp: new Date().toISOString()
                    };
                    resolve(this.userInfo);
                })
                .catch(() => {
                    this.userInfo = {
                        ip: 'unknown',
                        userAgent: navigator.userAgent,
                        language: navigator.language,
                        platform: navigator.platform,
                        screen: `${screen.width}x${screen.height}`,
                        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                        timestamp: new Date().toISOString()
                    };
                    resolve(this.userInfo);
                });
        });
    }

    // 检测网络连接
    async checkNetworkConnection() {
        const start = Date.now();
        
        try {
            // 检测基本网络连接
            if (!navigator.onLine) {
                throw new Error('设备处于离线状态');
            }
            
            // 测试网络延迟
            const startPing = Date.now();
            const response = await fetch('/api/ping', { 
                method: 'GET',
                cache: 'no-cache'
            });
            const latency = Date.now() - startPing;
            
            if (!response.ok) {
                throw new Error(`网络请求失败: ${response.status}`);
            }
            
            if (latency > 5000) {
                throw new Error(`网络延迟过高: ${latency}ms`);
            }
            
            return { success: true, latency };
        } catch (error) {
            throw new Error(`网络连接异常: ${error.message}`);
        } finally {
            this.checks[0].duration = Date.now() - start;
        }
    }

    // 检测浏览器兼容性
    async checkBrowserCompatibility() {
        const start = Date.now();
        
        try {
            const issues = [];
            
            // 检测必需的API
            const requiredAPIs = [
                { name: 'fetch', check: () => typeof fetch !== 'undefined' },
                { name: 'Promise', check: () => typeof Promise !== 'undefined' },
                { name: 'localStorage', check: () => typeof Storage !== 'undefined' },
                { name: 'JSON', check: () => typeof JSON !== 'undefined' },
                { name: 'WebSocket', check: () => typeof WebSocket !== 'undefined' },
                { name: 'FormData', check: () => typeof FormData !== 'undefined' }
            ];
            
            requiredAPIs.forEach(api => {
                if (!api.check()) {
                    issues.push(`不支持 ${api.name} API`);
                }
            });
            
            // 检测浏览器版本
            const userAgent = navigator.userAgent;
            const isIE = /MSIE|Trident/.test(userAgent);
            const isOldChrome = /Chrome\/([0-9]+)/.test(userAgent) && 
                               parseInt(userAgent.match(/Chrome\/([0-9]+)/)[1]) < 60;
            const isOldFirefox = /Firefox\/([0-9]+)/.test(userAgent) && 
                                parseInt(userAgent.match(/Firefox\/([0-9]+)/)[1]) < 55;
            
            if (isIE) {
                issues.push('不支持Internet Explorer浏览器');
            }
            if (isOldChrome) {
                issues.push('Chrome浏览器版本过低，建议升级到60以上版本');
            }
            if (isOldFirefox) {
                issues.push('Firefox浏览器版本过低，建议升级到55以上版本');
            }
            
            if (issues.length > 0) {
                throw new Error(issues.join('; '));
            }
            
            return { success: true, userAgent };
        } catch (error) {
            throw error;
        } finally {
            this.checks[1].duration = Date.now() - start;
        }
    }

    // 检测JavaScript功能
    async checkJavaScriptFeatures() {
        const start = Date.now();
        
        try {
            const issues = [];
            
            // 检测ES6功能
            try {
                eval('const test = () => {}; class Test {}');
            } catch (e) {
                issues.push('不支持ES6语法');
            }
            
            // 检测异步功能
            try {
                eval('async function test() { await Promise.resolve(); }');
            } catch (e) {
                issues.push('不支持async/await语法');
            }
            
            // 检测模块支持
            const supportsModules = 'noModule' in document.createElement('script');
            if (!supportsModules) {
                issues.push('不支持ES6模块');
            }
            
            if (issues.length > 0) {
                throw new Error(issues.join('; '));
            }
            
            return { success: true };
        } catch (error) {
            throw error;
        } finally {
            this.checks[2].duration = Date.now() - start;
        }
    }

    // 检测WebSocket支持
    async checkWebSocketSupport() {
        const start = Date.now();
        
        return new Promise((resolve, reject) => {
            try {
                if (typeof WebSocket === 'undefined') {
                    throw new Error('浏览器不支持WebSocket');
                }
                
                // 尝试连接WebSocket
                const ws = new WebSocket(`ws://${location.host}/ws/test`);
                
                const timeout = setTimeout(() => {
                    ws.close();
                    resolve({ success: true, note: 'WebSocket支持但服务器未启用' });
                }, 3000);
                
                ws.onopen = () => {
                    clearTimeout(timeout);
                    ws.close();
                    resolve({ success: true });
                };
                
                ws.onerror = () => {
                    clearTimeout(timeout);
                    resolve({ success: true, note: 'WebSocket支持但连接失败' });
                };
                
            } catch (error) {
                reject(error);
            } finally {
                this.checks[3].duration = Date.now() - start;
            }
        });
    }

    // 检测本地存储
    async checkLocalStorage() {
        const start = Date.now();
        
        try {
            if (typeof Storage === 'undefined') {
                throw new Error('浏览器不支持本地存储');
            }
            
            // 测试localStorage
            const testKey = 'alingai_storage_test';
            const testValue = 'test_value_' + Date.now();
            
            localStorage.setItem(testKey, testValue);
            const retrieved = localStorage.getItem(testKey);
            localStorage.removeItem(testKey);
            
            if (retrieved !== testValue) {
                throw new Error('localStorage读写测试失败');
            }
            
            // 测试sessionStorage
            sessionStorage.setItem(testKey, testValue);
            const sessionRetrieved = sessionStorage.getItem(testKey);
            sessionStorage.removeItem(testKey);
            
            if (sessionRetrieved !== testValue) {
                throw new Error('sessionStorage读写测试失败');
            }
            
            return { success: true };
        } catch (error) {
            throw new Error(`本地存储检测失败: ${error.message}`);
        } finally {
            this.checks[4].duration = Date.now() - start;
        }
    }

    // 检测媒体功能
    async checkMediaSupport() {
        const start = Date.now();
        
        try {
            const support = {
                audio: false,
                video: false,
                microphone: false,
                camera: false
            };
            
            // 检测音频支持
            const audio = document.createElement('audio');
            support.audio = !!(audio.canPlayType && audio.canPlayType('audio/mpeg'));
            
            // 检测视频支持
            const video = document.createElement('video');
            support.video = !!(video.canPlayType && video.canPlayType('video/mp4'));
            
            // 检测媒体设备访问（可选）
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    support.microphone = true;
                    stream.getTracks().forEach(track => track.stop());
                } catch (e) {
                    // 麦克风权限被拒绝是正常的
                }
                
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    support.camera = true;
                    stream.getTracks().forEach(track => track.stop());
                } catch (e) {
                    // 摄像头权限被拒绝是正常的
                }
            }
            
            return { success: true, support };
        } catch (error) {
            throw new Error(`媒体功能检测失败: ${error.message}`);
        } finally {
            this.checks[5].duration = Date.now() - start;
        }
    }

    // 检测安全策略
    async checkSecurityFeatures() {
        const start = Date.now();
        
        try {
            const features = {
                https: location.protocol === 'https:',
                csp: document.querySelector('meta[http-equiv="Content-Security-Policy"]') !== null,
                cors: true // 假设支持，实际在API测试中验证
            };
            
            // 在生产环境中检查HTTPS
            if (location.hostname !== 'localhost' && location.hostname !== '127.0.0.1' && !features.https) {
                console.warn('建议使用HTTPS协议以确保安全性');
            }
            
            return { success: true, features };
        } catch (error) {
            throw new Error(`安全策略检测失败: ${error.message}`);
        } finally {
            this.checks[6].duration = Date.now() - start;
        }
    }

    // 检测API端点
    async checkAPIEndpoints() {
        const start = Date.now();
        
        try {
            const endpoints = [
                { url: '/api/status', desc: '状态检查' },
                { url: '/api/ping', desc: '连通性测试' }
            ];
            
            const results = [];
            
            for (const endpoint of endpoints) {
                try {
                    const response = await fetch(endpoint.url, {
                        method: 'GET',
                        cache: 'no-cache'
                    });
                    
                    if (response.ok) {
                        results.push({ ...endpoint, status: 'ok' });
                    } else {
                        results.push({ ...endpoint, status: 'error', error: response.status });
                    }
                } catch (error) {
                    results.push({ ...endpoint, status: 'error', error: error.message });
                }
            }
            
            const failedEndpoints = results.filter(r => r.status === 'error');
            if (failedEndpoints.length > 0) {
                throw new Error(`API端点检测失败: ${failedEndpoints.map(f => f.desc).join(', ')}`);
            }
            
            return { success: true, results };
        } catch (error) {
            throw error;
        } finally {
            this.checks[7].duration = Date.now() - start;
        }
    }

    // 检测数据库连接
    async checkDatabaseConnection() {
        const start = Date.now();
        
        try {
            const response = await fetch('/api/health/database', {
                method: 'GET',
                cache: 'no-cache'
            });
            
            if (!response.ok) {
                throw new Error(`数据库连接检查失败: ${response.status}`);
            }
            
            const data = await response.json();
            if (!data.connected) {
                throw new Error('数据库连接不可用');
            }
            
            return { success: true, data };
        } catch (error) {
            throw new Error(`数据库连接检测失败: ${error.message}`);
        } finally {
            this.checks[8].duration = Date.now() - start;
        }
    }

    // 性能基准测试
    async checkPerformance() {
        const start = Date.now();
        
        try {
            const results = {};
            
            // JavaScript执行性能测试
            const jsStart = performance.now();
            for (let i = 0; i < 100000; i++) {
                Math.random() * Math.random();
            }
            results.jsPerformance = performance.now() - jsStart;
            
            // DOM操作性能测试
            const domStart = performance.now();
            const testDiv = document.createElement('div');
            for (let i = 0; i < 1000; i++) {
                const child = document.createElement('span');
                child.textContent = 'test';
                testDiv.appendChild(child);
            }
            results.domPerformance = performance.now() - domStart;
            
            // 内存使用情况（如果支持）
            if ('memory' in performance) {
                results.memory = {
                    used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024),
                    total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024),
                    limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024)
                };
            }
            
            return { success: true, results };
        } catch (error) {
            throw new Error(`性能测试失败: ${error.message}`);
        } finally {
            this.checks[9].duration = Date.now() - start;
        }
    }

    // 运行单个检测
    async runCheck(index) {
        const check = this.checks[index];
        check.status = 'checking';
        this.updateUI();
        
        try {
            const result = await check.checkFunction();
            check.status = 'success';
            check.result = result;
        } catch (error) {
            check.status = 'error';
            check.error = error.message;
            this.errors.push({
                check: check.name,
                description: check.description,
                error: error.message,
                timestamp: new Date().toISOString()
            });
        }
        
        this.updateUI();
    }

    // 发送错误报告
    async sendErrorReport() {
        if (this.errors.length === 0) return;
        
        const report = {
            userInfo: this.userInfo,
            errors: this.errors,
            checkResults: this.checks,
            duration: Date.now() - this.startTime,
            timestamp: new Date().toISOString(),
            url: window.location.href
        };
        
        try {
            await fetch('/api/error-report', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(report)
            });
        } catch (error) {
            console.error('发送错误报告失败:', error);
        }
    }

    // 保存用户验证状态
    async saveUserVerification() {
        try {
            const response = await fetch('/api/user-verification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ip: this.userInfo.ip,
                    userAgent: this.userInfo.userAgent,
                    timestamp: new Date().toISOString(),
                    checksPassed: this.checks.filter(c => c.status === 'success').length,
                    totalChecks: this.checks.length
                })
            });
            
            if (!response.ok) {
                throw new Error('保存验证状态失败');
            }
            
            return await response.json();
        } catch (error) {
            console.error('保存用户验证状态失败:', error);
            throw error;
        }
    }

    // 显示错误详情
    showErrorDetails() {
        const errorContainer = document.getElementById('errorContainer');
        const errorDetails = document.getElementById('errorDetails');
        
        if (this.errors.length > 0) {
            const errorText = this.errors.map(error => 
                `[${error.check}] ${error.description}: ${error.error}`
            ).join('\n\n');
            
            errorDetails.textContent = errorText;
            errorContainer.style.display = 'block';
        }
    }

    // 运行所有检测
    async runAllChecks() {
        this.initializeChecks();
        this.updateUI();
        
        // 获取用户信息
        await this.getUserInfo();
        
        // 逐个运行检测
        for (let i = 0; i < this.checks.length; i++) {
            this.currentCheck = i;
            await this.runCheck(i);
            
            // 在检测之间添加小延迟，避免过快执行
            await new Promise(resolve => setTimeout(resolve, 300));
        }
        
        this.currentCheck = this.checks.length;
        this.updateUI();
        
        // 处理结果
        const errorCount = this.errors.length;
        const retryBtn = document.getElementById('retryBtn');
        const continueBtn = document.getElementById('continueBtn');
        
        if (errorCount > 0) {
            // 有错误，发送错误报告
            await this.sendErrorReport();
            this.showErrorDetails();
            retryBtn.style.display = 'inline-block';
        } else {
            // 无错误，保存验证状态并允许继续
            try {
                await this.saveUserVerification();
                continueBtn.style.display = 'inline-block';
            } catch (error) {
                console.error('保存验证状态失败:', error);
                retryBtn.style.display = 'inline-block';
            }
        }
    }
}

// 全局函数，供HTML调用
async function startInitialization() {
    const retryBtn = document.getElementById('retryBtn');
    const continueBtn = document.getElementById('continueBtn');
    const errorContainer = document.getElementById('errorContainer');
    
    // 隐藏按钮和错误信息
    retryBtn.style.display = 'none';
    continueBtn.style.display = 'none';
    errorContainer.style.display = 'none';
    
    // 开始检测
    const checker = new EnvironmentChecker();
    await checker.runAllChecks();
}
