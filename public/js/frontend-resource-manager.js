/**
 * AlingAi Pro 前端资源整合管理器
 * 自动检查和整合所有前端资源文件
 */
class FrontendResourceManager {
    constructor() {
        this.resources = {
            css: [],
            js: [],
            missing: [],
            errors: []
        };
        this.init();
    }

    /**
     * 初始化资源管理器
     */
    init() {
        
        this.scanExistingResources();
        this.validateResourceReferences();
        this.generateResourceMap();
        this.setupAutoloader();
    }

    /**
     * 扫描现有资源
     */
    scanExistingResources() {
        // 扫描CSS资源
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"]');
        cssLinks.forEach(link => {
            this.resources.css.push({
                href: link.href,
                element: link,
                loaded: !link.sheet ? false : true
            });
        });

        // 扫描JS资源
        const jsScripts = document.querySelectorAll('script[src]');
        jsScripts.forEach(script => {
            this.resources.js.push({
                src: script.src,
                element: script,
                loaded: script.complete || script.readyState === 'complete'
            });
        });

        
    }

    /**
     * 验证资源引用
     */
    async validateResourceReferences() {
        

        // 检查CSS文件
        for (const css of this.resources.css) {
            try {
                const response = await fetch(css.href, { method: 'HEAD' });
                if (!response.ok) {
                    this.resources.missing.push({
                        type: 'css',
                        url: css.href,
                        error: `HTTP ${response.status}`
                    });
                }
            } catch (error) {
                this.resources.missing.push({
                    type: 'css',
                    url: css.href,
                    error: error.message
                });
            }
        }

        // 检查JS文件
        for (const js of this.resources.js) {
            try {
                const response = await fetch(js.src, { method: 'HEAD' });
                if (!response.ok) {
                    this.resources.missing.push({
                        type: 'js',
                        url: js.src,
                        error: `HTTP ${response.status}`
                    });
                }
            } catch (error) {
                this.resources.missing.push({
                    type: 'js',
                    url: js.src,
                    error: error.message
                });
            }
        }

        if (this.resources.missing.length > 0) {
            console.warn('⚠️ 发现缺失资源:', this.resources.missing);
        } else {
            
        }
    }

    /**
     * 生成资源映射
     */
    generateResourceMap() {
        this.resourceMap = {
            // 核心样式
            'bootstrap': '/assets/libs/bootstrap/css/bootstrap.min.css',
            'fontawesome': '/assets/libs/fontawesome/css/all.min.css',
            'animate': '/assets/libs/animate/animate.min.css',
            
            // 自定义样式
            'main-style': '/assets/css/style.css',
            'chat-style': '/assets/css/chat.css',
            'admin-style': '/assets/css/admin.css',
            'enhanced-style': '/assets/css/enhanced.css',
            
            // 核心脚本
            'jquery': '/assets/libs/jquery/jquery.min.js',
            'bootstrap-js': '/assets/libs/bootstrap/js/bootstrap.bundle.min.js',
            'threejs': '/assets/libs/three/three.min.js',
            
            // 功能脚本
            'chat-system': '/assets/js/chat-system.js',
            'quantum-particles': '/assets/js/quantum-particles.js',
            'admin-panel': '/assets/js/admin-panel.js',
            'system-health': '/assets/js/system-health-checker.js',
            'environment-checker': '/assets/js/environment-checker.js',
            'comprehensive-test': '/assets/js/comprehensive-test.js',
            
            // 增强脚本
            'localStorage-migrator': '/assets/js/migration/localStorage-migrator.js',
            'enhanced-admin': '/assets/js/enhanced-admin.js',
            'profile-enhanced': '/assets/js/profile-enhanced.js'
        };

        
    }

    /**
     * 动态加载资源
     */
    async loadResource(name, type = 'auto') {
        if (!this.resourceMap[name]) {
            console.error(`❌ 未知资源: ${name}`);
            return false;
        }

        const url = this.resourceMap[name];
        const fileType = type === 'auto' ? this.getFileType(url) : type;

        try {
            if (fileType === 'css') {
                return await this.loadCSS(url);
            } else if (fileType === 'js') {
                return await this.loadJS(url);
            }
        } catch (error) {
            console.error(`❌ 加载资源失败 ${name}:`, error);
            return false;
        }
    }

    /**
     * 加载CSS文件
     */
    loadCSS(url) {
        return new Promise((resolve, reject) => {
            // 检查是否已加载
            const existing = document.querySelector(`link[href="${url}"]`);
            if (existing) {
                resolve(true);
                return;
            }

            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            
            link.onload = () => {
                
                resolve(true);
            };
            
            link.onerror = () => {
                console.error(`❌ CSS加载失败: ${url}`);
                reject(new Error(`Failed to load CSS: ${url}`));
            };

            document.head.appendChild(link);
        });
    }

    /**
     * 加载JS文件
     */
    loadJS(url) {
        return new Promise((resolve, reject) => {
            // 检查是否已加载
            const existing = document.querySelector(`script[src="${url}"]`);
            if (existing) {
                resolve(true);
                return;
            }

            const script = document.createElement('script');
            script.src = url;
            script.async = true;
            
            script.onload = () => {
                
                resolve(true);
            };
            
            script.onerror = () => {
                console.error(`❌ JS加载失败: ${url}`);
                reject(new Error(`Failed to load JS: ${url}`));
            };

            document.head.appendChild(script);
        });
    }

    /**
     * 获取文件类型
     */
    getFileType(url) {
        const extension = url.split('.').pop().toLowerCase();
        if (extension === 'css') return 'css';
        if (extension === 'js') return 'js';
        return 'unknown';
    }

    /**
     * 设置自动加载器
     */
    setupAutoloader() {
        // 设置页面特定的资源自动加载
        const pathname = window.location.pathname;
        
        if (pathname.includes('admin')) {
            this.loadAdminResources();
        } else if (pathname.includes('profile')) {
            this.loadProfileResources();
        } else if (pathname.includes('chat')) {
            this.loadChatResources();
        }

        // 加载通用增强资源
        this.loadCommonResources();
    }

    /**
     * 加载管理员资源
     */
    async loadAdminResources() {
        
        
        const adminResources = [
            'admin-style',
            'enhanced-style', 
            'admin-panel',
            'system-health',
            'enhanced-admin'
        ];

        for (const resource of adminResources) {
            await this.loadResource(resource);
        }
    }

    /**
     * 加载个人中心资源
     */
    async loadProfileResources() {
        
        
        const profileResources = [
            'enhanced-style',
            'profile-enhanced',
            'localStorage-migrator'
        ];

        for (const resource of profileResources) {
            await this.loadResource(resource);
        }
    }

    /**
     * 加载聊天资源
     */
    async loadChatResources() {
        
        
        const chatResources = [
            'chat-style',
            'chat-system',
            'quantum-particles',
            'threejs'
        ];

        for (const resource of chatResources) {
            await this.loadResource(resource);
        }
    }

    /**
     * 加载通用资源
     */
    async loadCommonResources() {
        
        
        const commonResources = [
            'system-health',
            'environment-checker'
        ];

        for (const resource of commonResources) {
            await this.loadResource(resource);
        }
    }

    /**
     * 获取资源状态报告
     */
    getStatusReport() {
        return {
            total_css: this.resources.css.length,
            total_js: this.resources.js.length,
            missing_count: this.resources.missing.length,
            missing_resources: this.resources.missing,
            resource_map_size: Object.keys(this.resourceMap).length,
            timestamp: new Date().toISOString()
        };
    }

    /**
     * 修复缺失资源
     */
    async fixMissingResources() {
        
        
        for (const missing of this.resources.missing) {
            try {
                // 尝试从备用位置加载
                const alternativeUrl = this.getAlternativeUrl(missing.url);
                if (alternativeUrl) {
                    await this.loadResource(alternativeUrl, missing.type);
                    
                }
            } catch (error) {
                console.error(`❌ 修复失败: ${missing.url}`, error);
            }
        }
    }

    /**
     * 获取备用URL
     */
    getAlternativeUrl(originalUrl) {
        // CDN备用方案
        const cdnMapping = {
            'bootstrap': 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'fontawesome': 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            'jquery': 'https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js',
            'threejs': 'https://cdn.jsdelivr.net/npm/three@0.155.0/build/three.min.js'
        };

        // 简单的文件名匹配
        for (const [key, url] of Object.entries(cdnMapping)) {
            if (originalUrl.includes(key)) {
                return url;
            }
        }

        return null;
    }
}

// 自动启动资源管理器
if (typeof window !== 'undefined') {
    window.frontendResourceManager = new FrontendResourceManager();
    
    // 提供全局访问方法
    window.loadResource = (name, type) => window.frontendResourceManager.loadResource(name, type);
    window.getResourceStatus = () => window.frontendResourceManager.getStatusReport();
    window.fixResources = () => window.frontendResourceManager.fixMissingResources();
}
