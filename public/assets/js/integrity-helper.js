/**
 * 完整性辅助工具
 * 用于管理和验证资源的完整性
 * 创建日期: 2025-06-30
 */

class IntegrityHelper {
    constructor() {
        // 已知资源的SRI哈希值
        this.knownHashes = {
            // CDN资源
            'https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js': 
                'sha384-eYESGCGbIIUVGzjQxA1lF17UlVJH9QQGAlXGgGZJYQxJf/Vz+WXPmj0rCe0OBQqN',
            
            // 本地资源
            '/assets/js/page-transitions.js': 
                'sha384-PLACEHOLDER_HASH_REPLACE_WITH_REAL_HASH',
            '/assets/js/cursor-effects.js': 
                'sha384-PLACEHOLDER_HASH_REPLACE_WITH_REAL_HASH',
            '/assets/js/scroll-animations.js': 
                'sha384-PLACEHOLDER_HASH_REPLACE_WITH_REAL_HASH',
            '/assets/js/resource-optimizer.js': 
                'sha384-PLACEHOLDER_HASH_REPLACE_WITH_REAL_HASH',
            '/assets/js/security-monitor.js': 
                'sha384-PLACEHOLDER_HASH_REPLACE_WITH_REAL_HASH'
        };
        
        // 初始化
        this.init();
    }
    
    /**
     * 初始化
     */
    init() {
        // 在页面加载完成后应用SRI
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.applySRI());
        } else {
            this.applySRI();
        }
        
        // 监听动态添加的脚本
        this.observeDynamicScripts();
    }
    
    /**
     * 应用子资源完整性
     */
    applySRI() {
        const scripts = document.querySelectorAll('script[src]');
        scripts.forEach(script => {
            const src = script.getAttribute('src');
            // 如果脚本没有完整性属性但在已知哈希列表中
            if (src && !script.hasAttribute('integrity') && this.knownHashes[src]) {
                script.setAttribute('integrity', this.knownHashes[src]);
                script.setAttribute('crossorigin', 'anonymous');
                console.info('已应用SRI:', src);
            }
        });
    }
    
    /**
     * 监听动态添加的脚本
     */
    observeDynamicScripts() {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.tagName === 'SCRIPT' && node.src) {
                            const src = node.getAttribute('src');
                            if (!node.hasAttribute('integrity') && this.knownHashes[src]) {
                                node.setAttribute('integrity', this.knownHashes[src]);
                                node.setAttribute('crossorigin', 'anonymous');
                                console.info('已应用SRI到动态脚本:', src);
                            }
                        }
                    });
                }
            });
        });
        
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });
    }
    
    /**
     * 生成资源的SRI哈希值
     * 注意：此方法仅在开发环境中使用，生产环境应预先计算哈希值
     * @param {string} url - 资源URL
     * @returns {Promise<string>} SRI哈希值
     */
    async generateHash(url) {
        try {
            const response = await fetch(url);
            const text = await response.text();
            
            // 使用SubtleCrypto API计算SHA-384哈希
            const encoder = new TextEncoder();
            const data = encoder.encode(text);
            const hashBuffer = await crypto.subtle.digest('SHA-384', data);
            
            // 转换为base64
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            const hashBase64 = btoa(String.fromCharCode(...hashArray));
            
            return `sha384-${hashBase64}`;
        } catch (error) {
            console.error('生成哈希失败:', error);
            return null;
        }
    }
}

// 初始化完整性辅助工具
const integrityHelper = new IntegrityHelper(); 