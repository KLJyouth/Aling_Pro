/**
 * 安全指纹收集器
 * 用于收集浏览器和设备信息，生成设备指纹
 */
class SecurityFingerprint {
    /**
     * 构造函数
     */
    constructor() {
        this.fingerprint = null;
        this.deviceId = this.getDeviceId();
        this.components = {};
    }

    /**
     * 获取设备ID
     * 
     * @returns {string} 设备ID
     */
    getDeviceId() {
        // 尝试从localStorage获取设备ID
        let deviceId = localStorage.getItem('device_id');
        
        // 如果不存在，生成一个新的
        if (!deviceId) {
            deviceId = this.generateUUID();
            localStorage.setItem('device_id', deviceId);
        }
        
        return deviceId;
    }

    /**
     * 生成UUID
     * 
     * @returns {string} UUID
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /**
     * 收集浏览器信息
     * 
     * @returns {Object} 浏览器信息
     */
    collectBrowserInfo() {
        const navigator = window.navigator;
        const screen = window.screen;
        
        return {
            userAgent: navigator.userAgent,
            language: navigator.language,
            languages: JSON.stringify(navigator.languages),
            platform: navigator.platform,
            doNotTrack: navigator.doNotTrack,
            cookieEnabled: navigator.cookieEnabled,
            screenResolution: `${screen.width}x${screen.height}`,
            screenColorDepth: screen.colorDepth,
            timezone: new Date().getTimezoneOffset(),
            timezoneOffset: new Date().getTimezoneOffset(),
            sessionStorage: !!window.sessionStorage,
            localStorage: !!window.localStorage,
            indexedDb: !!window.indexedDB,
            cpuClass: navigator.cpuClass,
            deviceMemory: navigator.deviceMemory,
            hardwareConcurrency: navigator.hardwareConcurrency,
            plugins: this.getPlugins(),
            canvas: this.getCanvasFingerprint(),
            webgl: this.getWebglFingerprint(),
            adBlock: this.detectAdBlock(),
            fonts: this.detectFonts(),
            audio: this.getAudioFingerprint(),
            connection: this.getConnectionInfo()
        };
    }

    /**
     * 获取插件信息
     * 
     * @returns {string} 插件信息
     */
    getPlugins() {
        if (!navigator.plugins) return '';
        
        const pluginsArray = [];
        for (let i = 0; i < navigator.plugins.length; i++) {
            const plugin = navigator.plugins[i];
            pluginsArray.push(plugin.name);
        }
        
        return pluginsArray.join(',');
    }

    /**
     * 获取Canvas指纹
     * 
     * @returns {string} Canvas指纹
     */
    getCanvasFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = 200;
            canvas.height = 50;
            
            // 文本内容
            const text = 'AlingAi Security ';
            
            // 背景填充
            ctx.fillStyle = '#f8f8f8';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // 文本样式
            ctx.fillStyle = '#000';
            ctx.font = '18px Arial';
            ctx.fillText(text, 10, 25);
            
            // 添加一些图形
            ctx.strokeStyle = '#36c';
            ctx.beginPath();
            ctx.moveTo(150, 10);
            ctx.lineTo(180, 40);
            ctx.stroke();
            
            // 获取数据URL并截取一部分作为指纹
            const dataURL = canvas.toDataURL();
            return dataURL.substr(dataURL.length - 50);
        } catch (e) {
            return '';
        }
    }

    /**
     * 获取WebGL指纹
     * 
     * @returns {Object} WebGL信息
     */
    getWebglFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (!gl) {
                return {
                    vendor: '',
                    renderer: ''
                };
            }
            
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            
            if (!debugInfo) {
                return {
                    vendor: '',
                    renderer: ''
                };
            }
            
            return {
                vendor: gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL),
                renderer: gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL)
            };
        } catch (e) {
            return {
                vendor: '',
                renderer: ''
            };
        }
    }

    /**
     * 检测广告拦截器
     * 
     * @returns {boolean} 是否安装了广告拦截器
     */
    detectAdBlock() {
        const testAd = document.createElement('div');
        testAd.innerHTML = '&nbsp;';
        testAd.className = 'adsbox';
        document.body.appendChild(testAd);
        
        const isAdBlockEnabled = testAd.offsetHeight === 0;
        document.body.removeChild(testAd);
        
        return isAdBlockEnabled;
    }

    /**
     * 检测可用字体
     * 
     * @returns {Array} 可用字体列表
     */
    detectFonts() {
        const baseFonts = ['monospace', 'sans-serif', 'serif'];
        const fontList = [
            'Arial', 'Courier New', 'Georgia', 'Times New Roman', 
            'Verdana', 'Tahoma', 'Trebuchet MS', 'Palatino', 
            'Garamond', 'Bookman', 'Comic Sans MS', 'Impact'
        ];
        
        const testString = 'mmmmmmmmmmlli';
        const testSize = '72px';
        const h = document.getElementsByTagName('body')[0];
        
        const s = document.createElement('span');
        s.style.fontSize = testSize;
        s.innerHTML = testString;
        const defaultWidth = {};
        const defaultHeight = {};
        
        for (const index in baseFonts) {
            s.style.fontFamily = baseFonts[index];
            h.appendChild(s);
            defaultWidth[baseFonts[index]] = s.offsetWidth;
            defaultHeight[baseFonts[index]] = s.offsetHeight;
            h.removeChild(s);
        }
        
        const detected = [];
        for (const font of fontList) {
            let detected_count = 0;
            for (const baseFont of baseFonts) {
                s.style.fontFamily = font + ',' + baseFont;
                h.appendChild(s);
                const matched = (s.offsetWidth !== defaultWidth[baseFont] || s.offsetHeight !== defaultHeight[baseFont]);
                h.removeChild(s);
                if (matched) {
                    detected_count++;
                }
            }
            if (detected_count >= 2) {
                detected.push(font);
            }
        }
        
        return detected;
    }

    /**
     * 获取音频指纹
     * 
     * @returns {string} 音频指纹
     */
    getAudioFingerprint() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const analyser = audioContext.createAnalyser();
            analyser.fftSize = 256;
            
            const oscillator = audioContext.createOscillator();
            oscillator.type = 'triangle';
            oscillator.frequency.setValueAtTime(10000, audioContext.currentTime);
            
            const compressor = audioContext.createDynamicsCompressor();
            compressor.threshold.setValueAtTime(-50, audioContext.currentTime);
            compressor.knee.setValueAtTime(40, audioContext.currentTime);
            compressor.ratio.setValueAtTime(12, audioContext.currentTime);
            compressor.attack.setValueAtTime(0, audioContext.currentTime);
            compressor.release.setValueAtTime(0.25, audioContext.currentTime);
            
            oscillator.connect(compressor);
            compressor.connect(analyser);
            analyser.connect(audioContext.destination);
            
            oscillator.start(0);
            
            const dataArray = new Uint8Array(analyser.frequencyBinCount);
            analyser.getByteFrequencyData(dataArray);
            
            oscillator.stop();
            audioContext.close();
            
            return dataArray.slice(0, 10).join('');
        } catch (e) {
            return '';
        }
    }

    /**
     * 获取网络连接信息
     * 
     * @returns {Object} 网络连接信息
     */
    getConnectionInfo() {
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        
        if (!connection) {
            return {
                type: 'unknown',
                effectiveType: 'unknown',
                downlinkMax: -1,
                downlink: -1,
                rtt: -1,
                saveData: false
            };
        }
        
        return {
            type: connection.type || 'unknown',
            effectiveType: connection.effectiveType || 'unknown',
            downlinkMax: connection.downlinkMax || -1,
            downlink: connection.downlink || -1,
            rtt: connection.rtt || -1,
            saveData: connection.saveData || false
        };
    }

    /**
     * 生成指纹
     * 
     * @returns {Promise<string>} 指纹
     */
    async generateFingerprint() {
        if (this.fingerprint) {
            return this.fingerprint;
        }
        
        this.components = this.collectBrowserInfo();
        
        // 将组件转换为字符串并计算哈希
        const componentsStr = JSON.stringify(this.components);
        
        // 使用SHA-256计算哈希（如果可用）
        if (window.crypto && window.crypto.subtle && window.TextEncoder) {
            const encoder = new TextEncoder();
            const data = encoder.encode(componentsStr);
            const hashBuffer = await window.crypto.subtle.digest('SHA-256', data);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            this.fingerprint = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        } else {
            // 简单哈希函数
            let hash = 0;
            for (let i = 0; i < componentsStr.length; i++) {
                const char = componentsStr.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // 转换为32位整数
            }
            this.fingerprint = hash.toString(36);
        }
        
        return this.fingerprint;
    }

    /**
     * 添加安全头到请求
     * 
     * @param {XMLHttpRequest|Headers} target 请求对象或头对象
     */
    async addSecurityHeaders(target) {
        const fingerprint = await this.generateFingerprint();
        
        if (target instanceof XMLHttpRequest) {
            target.setRequestHeader('X-Device-ID', this.deviceId);
            target.setRequestHeader('X-Device-Fingerprint', fingerprint);
            target.setRequestHeader('X-JavaScript-Enabled', 'true');
            target.setRequestHeader('X-Cookies-Enabled', navigator.cookieEnabled.toString());
            target.setRequestHeader('X-Screen-Resolution', `${screen.width}x${screen.height}`);
            target.setRequestHeader('X-Color-Depth', screen.colorDepth.toString());
            target.setRequestHeader('X-Timezone-Offset', new Date().getTimezoneOffset().toString());
            
            if (this.components.webgl) {
                target.setRequestHeader('X-WebGL-Vendor', this.components.webgl.vendor);
                target.setRequestHeader('X-WebGL-Renderer', this.components.webgl.renderer);
            }
            
            if (this.components.connection) {
                target.setRequestHeader('X-Connection-Type', this.components.connection.effectiveType);
            }
        } else if (target instanceof Headers) {
            target.append('X-Device-ID', this.deviceId);
            target.append('X-Device-Fingerprint', fingerprint);
            target.append('X-JavaScript-Enabled', 'true');
            target.append('X-Cookies-Enabled', navigator.cookieEnabled.toString());
            target.append('X-Screen-Resolution', `${screen.width}x${screen.height}`);
            target.append('X-Color-Depth', screen.colorDepth.toString());
            target.append('X-Timezone-Offset', new Date().getTimezoneOffset().toString());
            
            if (this.components.webgl) {
                target.append('X-WebGL-Vendor', this.components.webgl.vendor);
                target.append('X-WebGL-Renderer', this.components.webgl.renderer);
            }
            
            if (this.components.connection) {
                target.append('X-Connection-Type', this.components.connection.effectiveType);
            }
        }
    }
}

// 创建全局实例
window.securityFingerprint = new SecurityFingerprint();

// 重写XMLHttpRequest以添加安全头
(function() {
    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;
    
    XMLHttpRequest.prototype.open = function() {
        this._securityUrl = arguments[1];
        return originalOpen.apply(this, arguments);
    };
    
    XMLHttpRequest.prototype.send = function() {
        // 只对同源请求添加安全头
        const isSameOrigin = this._securityUrl && (
            this._securityUrl.indexOf('//') === -1 || 
            this._securityUrl.indexOf(window.location.origin) === 0
        );
        
        if (isSameOrigin) {
            window.securityFingerprint.addSecurityHeaders(this);
        }
        
        return originalSend.apply(this, arguments);
    };
})();

// 重写fetch以添加安全头
(function() {
    const originalFetch = window.fetch;
    
    window.fetch = function() {
        const args = Array.from(arguments);
        const url = args[0];
        let options = args[1] || {};
        
        // 只对同源请求添加安全头
        const isSameOrigin = typeof url === 'string' && (
            url.indexOf('//') === -1 || 
            url.indexOf(window.location.origin) === 0
        );
        
        if (isSameOrigin) {
            return window.securityFingerprint.generateFingerprint().then(fingerprint => {
                options = Object.assign({}, options);
                options.headers = options.headers || {};
                
                // 创建新的Headers对象
                const headers = new Headers(options.headers);
                window.securityFingerprint.addSecurityHeaders(headers);
                
                // 将Headers对象转换回普通对象
                const headersObj = {};
                for (const pair of headers.entries()) {
                    headersObj[pair[0]] = pair[1];
                }
                
                options.headers = headersObj;
                return originalFetch.apply(window, [url, options]);
            });
        }
        
        return originalFetch.apply(window, args);
    };
})();
