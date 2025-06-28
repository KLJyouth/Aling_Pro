/**
 * 安全监控系统
 * 用于检测和防止前端安全问题
 * 创建日期: 2025-06-10
 */

// 安全监控系统
class SecurityMonitor {
    constructor(options = {}) {
        this.options = {
            enableDomMonitoring: true,
            enableXssProtection: true,
            enableCspReporting: true,
            enableIntegrityChecks: true,
            ...options
        };
        
        this.domSnapshot = null;
        this.suspiciousActivities = [];
        this.maxLogSize = 100;
        
        this.initialize();
    }
    
    /**
     * 初始化安全监控系统
     */
    initialize() {
        console.log('🔒 安全监控系统已启动');
        
        if (this.options.enableDomMonitoring) {
            this.initializeDomMonitoring();
        }
        
        if (this.options.enableXssProtection) {
            this.setupXssProtection();
        }
        
        if (this.options.enableCspReporting) {
            this.setupCspReporting();
        }
        
        if (this.options.enableIntegrityChecks) {
            this.performIntegrityChecks();
            // 定期执行完整性检查
            setInterval(() => this.performIntegrityChecks(), 30000);
        }
    }
    
    /**
     * 初始化DOM监控
     */
    initializeDomMonitoring() {
        // 创建DOM快照
        this.createDomSnapshot();
        
        // 设置DOM变化监听器
        this.setupDomMutationObserver();
    }
    
    /**
     * 创建当前DOM的快照
     */
    createDomSnapshot() {
        // 简化版DOM快照，仅记录关键元素
        const keyElements = document.querySelectorAll('form, input, button, a[href], script');
        this.domSnapshot = Array.from(keyElements).map(element => ({
            tagName: element.tagName,
            id: element.id,
            className: element.className,
            attributes: this.getElementAttributes(element),
            path: this.getDomPath(element)
        }));
    }
    
    /**
     * 获取元素的所有属性
     * @param {HTMLElement} element - DOM元素
     * @returns {Object} 属性对象
     */
    getElementAttributes(element) {
        const attributes = {};
        Array.from(element.attributes).forEach(attr => {
            attributes[attr.name] = attr.value;
        });
        return attributes;
    }
    
    /**
     * 获取元素的DOM路径
     * @param {HTMLElement} element - DOM元素
     * @returns {string} DOM路径
     */
    getDomPath(element) {
        let path = [];
        while (element && element.nodeType === Node.ELEMENT_NODE) {
            let selector = element.nodeName.toLowerCase();
            if (element.id) {
                selector += '#' + element.id;
            } else {
                let sibling = element;
                let siblingIndex = 1;
                while (sibling = sibling.previousElementSibling) {
                    if (sibling.nodeName.toLowerCase() === selector) {
                        siblingIndex++;
                    }
                }
                if (siblingIndex > 1) {
                    selector += ':nth-of-type(' + siblingIndex + ')';
                }
            }
            path.unshift(selector);
            element = element.parentNode;
        }
        return path.join(' > ');
    }
    
    /**
     * 设置DOM变化监听器
     */
    setupDomMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                this.handleDomMutation(mutation);
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            attributes: true,
            characterData: true,
            subtree: true,
            attributeOldValue: true,
            characterDataOldValue: true
        });
    }
    
    /**
     * 处理DOM变化
     * @param {MutationRecord} mutation - 变化记录
     */
    handleDomMutation(mutation) {
        // 检查是否是可疑的DOM修改
        if (this.isSuspiciousMutation(mutation)) {
            const activity = {
                type: 'suspicious_dom_mutation',
                target: this.getDomPath(mutation.target),
                details: this.getMutationDetails(mutation),
                timestamp: new Date().toISOString()
            };
            
            this.logSuspiciousActivity(activity);
        }
    }
    
    /**
     * 判断是否是可疑的DOM变化
     * @param {MutationRecord} mutation - 变化记录
     * @returns {boolean} 是否可疑
     */
    isSuspiciousMutation(mutation) {
        // 检查新增的脚本元素
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            for (let i = 0; i < mutation.addedNodes.length; i++) {
                const node = mutation.addedNodes[i];
                if (node.nodeName === 'SCRIPT') {
                    return true;
                }
                
                // 检查内联事件处理程序
                if (node.nodeType === Node.ELEMENT_NODE) {
                    const attributes = node.attributes;
                    if (attributes) {
                        for (let j = 0; j < attributes.length; j++) {
                            const attr = attributes[j];
                            if (attr.name.startsWith('on') || 
                                (attr.name === 'href' && attr.value.startsWith('javascript:'))) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        
        // 检查属性变化
        if (mutation.type === 'attributes') {
            const attrName = mutation.attributeName;
            if (attrName.startsWith('on') || 
                (attrName === 'href' && mutation.target.getAttribute('href')?.startsWith('javascript:'))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取变化详情
     * @param {MutationRecord} mutation - 变化记录
     * @returns {Object} 变化详情
     */
    getMutationDetails(mutation) {
        const details = {
            type: mutation.type,
            targetElement: mutation.target.nodeName
        };
        
        if (mutation.type === 'attributes') {
            details.attribute = {
                name: mutation.attributeName,
                oldValue: mutation.oldValue,
                newValue: mutation.target.getAttribute(mutation.attributeName)
            };
        } else if (mutation.type === 'childList') {
            details.addedNodes = Array.from(mutation.addedNodes).map(node => node.nodeName);
            details.removedNodes = Array.from(mutation.removedNodes).map(node => node.nodeName);
        }
        
        return details;
    }
    
    /**
     * 设置XSS保护
     */
    setupXssProtection() {
        // 监控表单提交
        document.addEventListener('submit', this.checkFormSubmission.bind(this), true);
        
        // 监控动态内容插入
        this.monitorDynamicContent();
        
        // 净化URL参数
        this.sanitizeUrlParameters();
    }
    
    /**
     * 检查表单提交
     * @param {Event} event - 提交事件
     */
    checkFormSubmission(event) {
        const form = event.target;
        const inputs = form.querySelectorAll('input, textarea');
        
        for (let i = 0; i < inputs.length; i++) {
            const input = inputs[i];
            const value = input.value;
            
            // 检查可疑内容
            if (this.containsSuspiciousContent(value)) {
                event.preventDefault();
                this.logSuspiciousActivity({
                    type: 'suspicious_form_submission',
                    formAction: form.action,
                    inputName: input.name,
                    suspiciousValue: this.maskSensitiveData(value),
                    timestamp: new Date().toISOString()
                });
                
                // 可选：显示警告给用户
                alert('检测到可能的恶意内容，提交已被阻止。');
                break;
            }
        }
    }
    
    /**
     * 检查是否包含可疑内容
     * @param {string} value - 要检查的内容
     * @returns {boolean} 是否包含可疑内容
     */
    containsSuspiciousContent(value) {
        if (typeof value !== 'string') return false;
        
        const suspiciousPatterns = [
            /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
            /javascript:/gi,
            /onerror=/gi,
            /onload=/gi,
            /onclick=/gi,
            /onmouseover=/gi,
            /eval\s*\(/gi,
            /document\.cookie/gi,
            /document\.write/gi,
            /\blocation\s*=/gi,
            /\bwindow\s*\.\s*location\s*=/gi,
            /\bparent\s*\.\s*location\s*=/gi,
            /\btop\s*\.\s*location\s*=/gi
        ];
        
        return suspiciousPatterns.some(pattern => pattern.test(value));
    }
    
    /**
     * 掩盖敏感数据
     * @param {string} value - 敏感数据
     * @returns {string} 掩盖后的数据
     */
    maskSensitiveData(value) {
        if (value.length <= 10) {
            return '***' + value.substring(value.length - 3);
        }
        return value.substring(0, 3) + '***' + value.substring(value.length - 3);
    }
    
    /**
     * 监控动态内容插入
     */
    monitorDynamicContent() {
        // 重写危险的DOM方法
        const originalInnerHTML = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML');
        const originalOuterHTML = Object.getOwnPropertyDescriptor(Element.prototype, 'outerHTML');
        const originalInsertAdjacentHTML = Element.prototype.insertAdjacentHTML;
        
        // 重写innerHTML
        Object.defineProperty(Element.prototype, 'innerHTML', {
            set: function(value) {
                if (window.securityMonitor.containsSuspiciousContent(value)) {
                    window.securityMonitor.logSuspiciousActivity({
                        type: 'suspicious_innerHTML',
                        element: window.securityMonitor.getDomPath(this),
                        suspiciousValue: window.securityMonitor.maskSensitiveData(value),
                        timestamp: new Date().toISOString()
                    });
                    
                    // 可选：阻止设置或清理内容
                    const sanitized = window.securityMonitor.sanitizeHtml(value);
                    return originalInnerHTML.set.call(this, sanitized);
                }
                return originalInnerHTML.set.call(this, value);
            },
            get: originalInnerHTML.get
        });
        
        // 重写outerHTML
        Object.defineProperty(Element.prototype, 'outerHTML', {
            set: function(value) {
                if (window.securityMonitor.containsSuspiciousContent(value)) {
                    window.securityMonitor.logSuspiciousActivity({
                        type: 'suspicious_outerHTML',
                        element: window.securityMonitor.getDomPath(this),
                        suspiciousValue: window.securityMonitor.maskSensitiveData(value),
                        timestamp: new Date().toISOString()
                    });
                    
                    // 可选：阻止设置或清理内容
                    const sanitized = window.securityMonitor.sanitizeHtml(value);
                    return originalOuterHTML.set.call(this, sanitized);
                }
                return originalOuterHTML.set.call(this, value);
            },
            get: originalOuterHTML.get
        });
        
        // 重写insertAdjacentHTML
        Element.prototype.insertAdjacentHTML = function(position, text) {
            if (window.securityMonitor.containsSuspiciousContent(text)) {
                window.securityMonitor.logSuspiciousActivity({
                    type: 'suspicious_insertAdjacentHTML',
                    element: window.securityMonitor.getDomPath(this),
                    position: position,
                    suspiciousValue: window.securityMonitor.maskSensitiveData(text),
                    timestamp: new Date().toISOString()
                });
                
                // 可选：阻止设置或清理内容
                const sanitized = window.securityMonitor.sanitizeHtml(text);
                return originalInsertAdjacentHTML.call(this, position, sanitized);
            }
            return originalInsertAdjacentHTML.call(this, position, text);
        };
    }
    
    /**
     * 净化HTML内容
     * @param {string} html - 原始HTML
     * @returns {string} 净化后的HTML
     */
    sanitizeHtml(html) {
        // 简单的HTML净化，实际项目中可使用更完善的库如DOMPurify
        return html
            .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
            .replace(/javascript:/gi, 'removed:')
            .replace(/on\w+=/gi, 'data-removed-event=');
    }
    
    /**
     * 净化URL参数
     */
    sanitizeUrlParameters() {
        // 检查当前URL参数
        const urlParams = new URLSearchParams(window.location.search);
        let hasSuspiciousParams = false;
        
        for (const [key, value] of urlParams.entries()) {
            if (this.containsSuspiciousContent(value)) {
                hasSuspiciousParams = true;
                this.logSuspiciousActivity({
                    type: 'suspicious_url_parameter',
                    paramName: key,
                    suspiciousValue: this.maskSensitiveData(value),
                    timestamp: new Date().toISOString()
                });
            }
        }
        
        // 如果发现可疑参数，可以选择清理URL
        if (hasSuspiciousParams) {
            // 可选：重定向到清理后的URL
            // window.location.href = window.location.pathname;
        }
    }
    
    /**
     * 设置CSP报告
     */
    setupCspReporting() {
        // 监听CSP违规事件
        document.addEventListener('securitypolicyviolation', (e) => {
            this.logSuspiciousActivity({
                type: 'csp_violation',
                blockedURI: e.blockedURI,
                violatedDirective: e.violatedDirective,
                originalPolicy: e.originalPolicy,
                timestamp: new Date().toISOString()
            });
        });
        
        // 设置基本安全头部
        this.setupSecurityHeaders();
    }
    
    /**
     * 设置安全HTTP头部
     */
    setupSecurityHeaders() {
        // 注意：这些头部应该在服务器端设置
        // 这里只是为了记录和提醒
        
        const recommendedHeaders = {
            'Content-Security-Policy': "default-src 'self'; script-src 'self'; object-src 'none'; report-uri /csp-report",
            'X-XSS-Protection': '1; mode=block',
            'X-Content-Type-Options': 'nosniff',
            'X-Frame-Options': 'DENY',
            'Referrer-Policy': 'strict-origin-when-cross-origin'
        };
        
        // 检查当前页面是否设置了这些头部
        const missingHeaders = [];
        
        // 仅在支持Headers API的浏览器中执行
        if (typeof Headers !== 'undefined' && typeof Response !== 'undefined') {
            // 无法直接访问响应头，但可以记录建议
            console.info('建议在服务器端设置以下安全头部:', recommendedHeaders);
        }
    }
    
    /**
     * 执行完整性检查
     */
    performIntegrityChecks() {
        // 检查DOM完整性
        this.checkDomIntegrity();
        
        // 检查脚本完整性
        this.checkScriptIntegrity();
        
        // 检查存储完整性
        this.checkStorageIntegrity();
        
        // 检查可疑元素
        this.checkSuspiciousElements();
    }
    
    /**
     * 检查DOM完整性
     */
    checkDomIntegrity() {
        if (!this.domSnapshot) return;
        
        // 获取当前DOM状态
        const currentElements = document.querySelectorAll('form, input, button, a[href], script');
        const currentSnapshot = Array.from(currentElements).map(element => ({
            tagName: element.tagName,
            id: element.id,
            className: element.className,
            attributes: this.getElementAttributes(element),
            path: this.getDomPath(element)
        }));
        
        // 比较快照与当前状态
        const addedElements = currentSnapshot.filter(current => {
            return !this.domSnapshot.some(original => 
                original.path === current.path && 
                original.tagName === current.tagName
            );
        });
        
        const modifiedElements = currentSnapshot.filter(current => {
            const original = this.domSnapshot.find(orig => orig.path === current.path);
            if (!original) return false;
            
            // 检查属性是否被修改
            const originalAttrs = original.attributes;
            const currentAttrs = current.attributes;
            
            // 检查事件处理程序和href属性
            for (const attrName in currentAttrs) {
                if (attrName.startsWith('on') || attrName === 'href') {
                    if (originalAttrs[attrName] !== currentAttrs[attrName]) {
                        return true;
                    }
                }
            }
            
            return false;
        });
        
        // 记录可疑的DOM变化
        if (addedElements.length > 0) {
            this.logSuspiciousActivity({
                type: 'dom_integrity_violation',
                subType: 'added_elements',
                elements: addedElements.map(el => ({ path: el.path, tagName: el.tagName })),
                timestamp: new Date().toISOString()
            });
        }
        
        if (modifiedElements.length > 0) {
            this.logSuspiciousActivity({
                type: 'dom_integrity_violation',
                subType: 'modified_elements',
                elements: modifiedElements.map(el => ({ path: el.path, tagName: el.tagName })),
                timestamp: new Date().toISOString()
            });
        }
        
        // 更新DOM快照
        this.domSnapshot = currentSnapshot;
    }
    
    /**
     * 检查脚本完整性
     */
    checkScriptIntegrity() {
        const scripts = document.querySelectorAll('script');
        
        scripts.forEach(script => {
            // 检查内联脚本
            if (!script.src && script.textContent) {
                if (this.containsSuspiciousContent(script.textContent)) {
                    this.logSuspiciousActivity({
                        type: 'script_integrity_violation',
                        subType: 'suspicious_inline_script',
                        scriptPath: this.getDomPath(script),
                        timestamp: new Date().toISOString()
                    });
                }
            }
            
            // 检查外部脚本是否缺少完整性属性
            if (script.src && !script.integrity) {
                // 仅记录，不一定是安全问题，但最佳实践是使用SRI
                console.info('外部脚本缺少完整性属性:', script.src);
            }
        });
    }
    
    /**
     * 检查存储完整性
     */
    checkStorageIntegrity() {
        try {
            // 检查localStorage是否被篡改
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                const value = localStorage.getItem(key);
                
                if (this.containsSuspiciousContent(value)) {
                    this.logSuspiciousActivity({
                        type: 'storage_integrity_violation',
                        subType: 'suspicious_localStorage',
                        key: key,
                        suspiciousValue: this.maskSensitiveData(value),
                        timestamp: new Date().toISOString()
                    });
                }
            }
            
            // 检查sessionStorage是否被篡改
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                const value = sessionStorage.getItem(key);
                
                if (this.containsSuspiciousContent(value)) {
                    this.logSuspiciousActivity({
                        type: 'storage_integrity_violation',
                        subType: 'suspicious_sessionStorage',
                        key: key,
                        suspiciousValue: this.maskSensitiveData(value),
                        timestamp: new Date().toISOString()
                    });
                }
            }
            
            // 检查cookie是否被篡改
            const cookies = document.cookie.split(';');
            cookies.forEach(cookie => {
                const parts = cookie.split('=');
                if (parts.length >= 2) {
                    const value = decodeURIComponent(parts.slice(1).join('='));
                    if (this.containsSuspiciousContent(value)) {
                        this.logSuspiciousActivity({
                            type: 'storage_integrity_violation',
                            subType: 'suspicious_cookie',
                            cookieName: parts[0].trim(),
                            suspiciousValue: this.maskSensitiveData(value),
                            timestamp: new Date().toISOString()
                        });
                    }
                }
            });
        } catch (error) {
            console.error('检查存储完整性时出错:', error);
        }
    }
    
    /**
     * 检查可疑元素
     */
    checkSuspiciousElements() {
        // 检查隐藏的iframe
        const iframes = document.querySelectorAll('iframe');
        iframes.forEach(iframe => {
            const style = window.getComputedStyle(iframe);
            const isHidden = style.display === 'none' || 
                             style.visibility === 'hidden' || 
                             style.opacity === '0' ||
                             iframe.width === '0' ||
                             iframe.height === '0';
            
            if (isHidden) {
                this.logSuspiciousActivity({
                    type: 'suspicious_element',
                    subType: 'hidden_iframe',
                    elementPath: this.getDomPath(iframe),
                    src: iframe.src,
                    timestamp: new Date().toISOString()
                });
            }
        });
        
        // 检查可疑的表单
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            // 检查表单是否提交到外部域
            if (form.action && !form.action.startsWith(window.location.origin)) {
                this.logSuspiciousActivity({
                    type: 'suspicious_element',
                    subType: 'external_form_action',
                    elementPath: this.getDomPath(form),
                    action: form.action,
                    timestamp: new Date().toISOString()
                });
            }
            
            // 检查表单中的隐藏字段
            const hiddenInputs = form.querySelectorAll('input[type="hidden"]');
            hiddenInputs.forEach(input => {
                if (this.containsSuspiciousContent(input.value)) {
                    this.logSuspiciousActivity({
                        type: 'suspicious_element',
                        subType: 'suspicious_hidden_input',
                        elementPath: this.getDomPath(input),
                        inputName: input.name,
                        suspiciousValue: this.maskSensitiveData(input.value),
                        timestamp: new Date().toISOString()
                    });
                }
            });
        });
        
        // 检查可疑的链接
        const links = document.querySelectorAll('a[href]');
        links.forEach(link => {
            const href = link.getAttribute('href');
            
            // 检查javascript:协议
            if (href && href.toLowerCase().startsWith('javascript:')) {
                this.logSuspiciousActivity({
                    type: 'suspicious_element',
                    subType: 'javascript_protocol_link',
                    elementPath: this.getDomPath(link),
                    href: href,
                    timestamp: new Date().toISOString()
                });
            }
            
            // 检查data:协议
            if (href && href.toLowerCase().startsWith('data:')) {
                this.logSuspiciousActivity({
                    type: 'suspicious_element',
                    subType: 'data_protocol_link',
                    elementPath: this.getDomPath(link),
                    href: href,
                    timestamp: new Date().toISOString()
                });
            }
        });
    }
    
    /**
     * 记录可疑活动
     * @param {Object} activity - 可疑活动信息
     */
    logSuspiciousActivity(activity) {
        this.suspiciousActivities.push(activity);
        
        // 维护日志大小
        if (this.suspiciousActivities.length > this.maxLogSize) {
            this.suspiciousActivities.shift();
        }
        
        console.warn('🚨 检测到可疑活动:', activity);
    }
    
    /**
     * 获取安全报告
     * @returns {Object} 安全报告
     */
    getSecurityReport() {
        return {
            suspiciousActivities: this.suspiciousActivities,
            domSnapshot: this.domSnapshot ? this.domSnapshot.length : 0,
            timestamp: new Date().toISOString()
        };
    }
}

// 自动启动安全监控系统
if (typeof window !== 'undefined') {
    window.securityMonitor = new SecurityMonitor();
} 