/**
 * 脚本加载器
 * 用于安全加载外部脚本
 * 创建日期: 2025-06-30
 */

class ScriptLoader {
    constructor() {
        // SRI哈希映射
        this.integrityMap = {
            // 外部CDN资源
            'https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js': 
                'sha384-eYESGCGbIIUVGzjQxA1lF17UlVJH9QQGAlXGgGZJYQxJf/Vz+WXPmj0rCe0OBQqN',
            
            // 本地资源
            '/assets/js/page-transitions.js': 
                'sha384-PLACEHOLDER_HASH',
            '/assets/js/cursor-effects.js': 
                'sha384-PLACEHOLDER_HASH',
            '/assets/js/scroll-animations.js': 
                'sha384-PLACEHOLDER_HASH',
            '/assets/js/resource-optimizer.js': 
                'sha384-PLACEHOLDER_HASH',
            '/assets/js/security-monitor.js': 
                'sha384-PLACEHOLDER_HASH'
        };
        
        // 已加载的脚本
        this.loadedScripts = new Set();
    }
    
    /**
     * 加载脚本
     * @param {string} src - 脚本源
     * @param {Object} options - 选项
     * @returns {Promise} 加载完成的Promise
     */
    loadScript(src, options = {}) {
        // 如果脚本已加载，返回已解决的Promise
        if (this.loadedScripts.has(src)) {
            return Promise.resolve();
        }
        
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            
            // 设置完整性属性（如果可用）
            if (this.integrityMap[src]) {
                script.integrity = this.integrityMap[src];
                script.crossOrigin = 'anonymous';
            }
            
            // 设置其他属性
            if (options.async !== false) script.async = true;
            if (options.defer) script.defer = true;
            if (options.type) script.type = options.type;
            
            // 监听加载事件
            script.onload = () => {
                this.loadedScripts.add(src);
                resolve();
            };
            
            script.onerror = (error) => {
                reject(new Error(`Failed to load script: ${src}`));
            };
            
            // 添加到文档
            document.head.appendChild(script);
        });
    }
    
    /**
     * 批量加载脚本
     * @param {Array<string|Object>} scripts - 脚本列表
     * @returns {Promise} 所有脚本加载完成的Promise
     */
    loadScripts(scripts) {
        const promises = scripts.map(script => {
            if (typeof script === 'string') {
                return this.loadScript(script);
            } else {
                return this.loadScript(script.src, script);
            }
        });
        
        return Promise.all(promises);
    }
}

// 创建全局实例
window.scriptLoader = new ScriptLoader(); 