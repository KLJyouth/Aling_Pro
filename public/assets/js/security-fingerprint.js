/**
 * ��ȫָ���ռ���
 * �����ռ���������豸��Ϣ�������豸ָ��
 */
class SecurityFingerprint {
    /**
     * ���캯��
     */
    constructor() {
        this.fingerprint = null;
        this.deviceId = this.getDeviceId();
        this.components = {};
    }

    /**
     * ��ȡ�豸ID
     * 
     * @returns {string} �豸ID
     */
    getDeviceId() {
        // ���Դ�localStorage��ȡ�豸ID
        let deviceId = localStorage.getItem('device_id');
        
        // ��������ڣ�����һ���µ�
        if (!deviceId) {
            deviceId = this.generateUUID();
            localStorage.setItem('device_id', deviceId);
        }
        
        return deviceId;
    }

    /**
     * ����UUID
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
     * �ռ��������Ϣ
     * 
     * @returns {Object} �������Ϣ
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
     * ��ȡ�����Ϣ
     * 
     * @returns {string} �����Ϣ
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
     * ��ȡCanvasָ��
     * 
     * @returns {string} Canvasָ��
     */
    getCanvasFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = 200;
            canvas.height = 50;
            
            // �ı�����
            const text = 'AlingAi Security ';
            
            // �������
            ctx.fillStyle = '#f8f8f8';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // �ı���ʽ
            ctx.fillStyle = '#000';
            ctx.font = '18px Arial';
            ctx.fillText(text, 10, 25);
            
            // ���һЩͼ��
            ctx.strokeStyle = '#36c';
            ctx.beginPath();
            ctx.moveTo(150, 10);
            ctx.lineTo(180, 40);
            ctx.stroke();
            
            // ��ȡ����URL����ȡһ������Ϊָ��
            const dataURL = canvas.toDataURL();
            return dataURL.substr(dataURL.length - 50);
        } catch (e) {
            return '';
        }
    }

    /**
     * ��ȡWebGLָ��
     * 
     * @returns {Object} WebGL��Ϣ
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
     * �����������
     * 
     * @returns {boolean} �Ƿ�װ�˹��������
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
     * ����������
     * 
     * @returns {Array} ���������б�
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
     * ��ȡ��Ƶָ��
     * 
     * @returns {string} ��Ƶָ��
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
     * ��ȡ����������Ϣ
     * 
     * @returns {Object} ����������Ϣ
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
     * ����ָ��
     * 
     * @returns {Promise<string>} ָ��
     */
    async generateFingerprint() {
        if (this.fingerprint) {
            return this.fingerprint;
        }
        
        this.components = this.collectBrowserInfo();
        
        // �����ת��Ϊ�ַ����������ϣ
        const componentsStr = JSON.stringify(this.components);
        
        // ʹ��SHA-256�����ϣ��������ã�
        if (window.crypto && window.crypto.subtle && window.TextEncoder) {
            const encoder = new TextEncoder();
            const data = encoder.encode(componentsStr);
            const hashBuffer = await window.crypto.subtle.digest('SHA-256', data);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            this.fingerprint = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        } else {
            // �򵥹�ϣ����
            let hash = 0;
            for (let i = 0; i < componentsStr.length; i++) {
                const char = componentsStr.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // ת��Ϊ32λ����
            }
            this.fingerprint = hash.toString(36);
        }
        
        return this.fingerprint;
    }

    /**
     * ��Ӱ�ȫͷ������
     * 
     * @param {XMLHttpRequest|Headers} target ��������ͷ����
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

// ����ȫ��ʵ��
window.securityFingerprint = new SecurityFingerprint();

// ��дXMLHttpRequest����Ӱ�ȫͷ
(function() {
    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;
    
    XMLHttpRequest.prototype.open = function() {
        this._securityUrl = arguments[1];
        return originalOpen.apply(this, arguments);
    };
    
    XMLHttpRequest.prototype.send = function() {
        // ֻ��ͬԴ������Ӱ�ȫͷ
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

// ��дfetch����Ӱ�ȫͷ
(function() {
    const originalFetch = window.fetch;
    
    window.fetch = function() {
        const args = Array.from(arguments);
        const url = args[0];
        let options = args[1] || {};
        
        // ֻ��ͬԴ������Ӱ�ȫͷ
        const isSameOrigin = typeof url === 'string' && (
            url.indexOf('//') === -1 || 
            url.indexOf(window.location.origin) === 0
        );
        
        if (isSameOrigin) {
            return window.securityFingerprint.generateFingerprint().then(fingerprint => {
                options = Object.assign({}, options);
                options.headers = options.headers || {};
                
                // �����µ�Headers����
                const headers = new Headers(options.headers);
                window.securityFingerprint.addSecurityHeaders(headers);
                
                // ��Headers����ת������ͨ����
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
