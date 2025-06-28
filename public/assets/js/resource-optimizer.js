/**
 * 资源优化器 - 优化网站资源加载
 * 实现延迟加载、预加载和资源优先级管理
 */

const ResourceOptimizer = (function() {
    'use strict';
    
    // 配置选项
    const config = {
        lazyLoadSelector: '[data-lazy]',
        lazyLoadThreshold: 200,
        preloadLinks: true,
        preloadThreshold: 0.2,
        cacheControl: true,
        cacheDuration: 7 * 24 * 60 * 60 * 1000, // 7天
        priorityAttr: 'data-priority',
        debug: false
    };
    
    // 缓存管理
    const cache = {
        store: {},
        
        /**
         * 存储资源到缓存
         * @param {string} url - 资源URL
         * @param {*} data - 资源数据
         * @param {number} expiry - 过期时间（毫秒）
         */
        set: function(url, data, expiry) {
            if (!config.cacheControl) return;
            
            try {
                const item = {
                    data: data,
                    expiry: Date.now() + (expiry || config.cacheDuration)
                };
                
                // 存储到内存缓存
                this.store[url] = item;
                
                // 存储到本地存储
                if (typeof localStorage !== 'undefined') {
                    localStorage.setItem('resource_' + url, JSON.stringify(item));
                }
                
                log(`缓存资源: ${url}`);
            } catch (e) {
                log(`缓存资源失败: ${url}`, e, true);
            }
        },
        
        /**
         * 从缓存获取资源
         * @param {string} url - 资源URL
         * @returns {*} 资源数据或null
         */
        get: function(url) {
            if (!config.cacheControl) return null;
            
            // 首先检查内存缓存
            let item = this.store[url];
            
            // 如果内存中没有，检查本地存储
            if (!item && typeof localStorage !== 'undefined') {
                const stored = localStorage.getItem('resource_' + url);
                if (stored) {
                    try {
                        item = JSON.parse(stored);
                        this.store[url] = item; // 更新内存缓存
                    } catch (e) {
                        log(`解析缓存失败: ${url}`, e, true);
                        return null;
                    }
                }
            }
            
            // 检查是否过期
            if (item && item.expiry > Date.now()) {
                log(`从缓存加载: ${url}`);
                return item.data;
            } else if (item) {
                // 删除过期缓存
                this.remove(url);
            }
            
            return null;
        },
        
        /**
         * 从缓存中删除资源
         * @param {string} url - 资源URL
         */
        remove: function(url) {
            delete this.store[url];
            
            if (typeof localStorage !== 'undefined') {
                localStorage.removeItem('resource_' + url);
            }
            
            log(`删除缓存: ${url}`);
        },
        
        /**
         * 清理所有过期缓存
         */
        cleanup: function() {
            const now = Date.now();
            
            // 清理内存缓存
            Object.keys(this.store).forEach(url => {
                if (this.store[url].expiry <= now) {
                    delete this.store[url];
                }
            });
            
            // 清理本地存储
            if (typeof localStorage !== 'undefined') {
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key.startsWith('resource_')) {
                        try {
                            const item = JSON.parse(localStorage.getItem(key));
                            if (item.expiry <= now) {
                                localStorage.removeItem(key);
                            }
                        } catch (e) {
                            // 忽略解析错误，直接删除
                            localStorage.removeItem(key);
                        }
                    }
                }
            }
            
            log('清理过期缓存完成');
        }
    };
    
    // 延迟加载处理
    const lazyLoad = {
        observer: null,
        elements: [],
        
        /**
         * 初始化延迟加载
         */
        init: function() {
            this.elements = document.querySelectorAll(config.lazyLoadSelector);
            
            if (!this.elements.length) return;
            
            if ('IntersectionObserver' in window) {
                this.observer = new IntersectionObserver(this.onIntersection, {
                    rootMargin: `${config.lazyLoadThreshold}px`,
                    threshold: 0.01
                });
                
                this.elements.forEach(element => {
                    this.observer.observe(element);
                });
            } else {
                // 降级处理：立即加载所有资源
                this.loadAll();
            }
            
            log(`初始化延迟加载: ${this.elements.length}个元素`);
        },
        
        /**
         * 交叉观察器回调
         * @param {IntersectionObserverEntry[]} entries - 观察条目
         */
        onIntersection: function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    lazyLoad.loadElement(entry.target);
                    lazyLoad.observer.unobserve(entry.target);
                }
            });
        },
        
        /**
         * 加载单个元素
         * @param {Element} element - 要加载的元素
         */
        loadElement: function(element) {
            const type = element.tagName.toLowerCase();
            
            switch (type) {
                case 'img':
                    if (element.dataset.src) {
                        element.src = element.dataset.src;
                        delete element.dataset.src;
                    }
                    break;
                    
                case 'video':
                    if (element.dataset.src) {
                        element.src = element.dataset.src;
                        delete element.dataset.src;
                    }
                    
                    Array.from(element.querySelectorAll('source')).forEach(source => {
                        if (source.dataset.src) {
                            source.src = source.dataset.src;
                            delete source.dataset.src;
                        }
                    });
                    
                    element.load();
                    break;
                    
                case 'iframe':
                    if (element.dataset.src) {
                        element.src = element.dataset.src;
                        delete element.dataset.src;
                    }
                    break;
                    
                default:
                    if (element.dataset.bg) {
                        element.style.backgroundImage = `url('${element.dataset.bg}')`;
                        delete element.dataset.bg;
                    }
            }
            
            element.classList.add('loaded');
            element.dispatchEvent(new Event('resourceLoaded'));
            
            log(`加载元素: ${type} ${element.dataset.src || element.dataset.bg || ''}`);
        },
        
        /**
         * 加载所有元素（降级方案）
         */
        loadAll: function() {
            this.elements.forEach(element => {
                this.loadElement(element);
            });
        }
    };
    
    // 链接预加载处理
    const linkPreload = {
        /**
         * 初始化链接预加载
         */
        init: function() {
            if (!config.preloadLinks) return;
            
            // 监听鼠标悬停事件
            document.addEventListener('mouseover', this.onMouseOver);
            
            // 监听触摸事件（移动设备）
            document.addEventListener('touchstart', this.onTouchStart, { passive: true });
            
            log('初始化链接预加载');
        },
        
        /**
         * 鼠标悬停事件处理
         * @param {MouseEvent} e - 鼠标事件
         */
        onMouseOver: function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            
            const url = link.getAttribute('href');
            if (!url || url.startsWith('#') || url.startsWith('javascript:') || url.startsWith('mailto:') || url.startsWith('tel:')) return;
            
            // 防止重复预加载
            if (link.dataset.preloaded === 'true') return;
            
            // 延迟预加载，避免用户快速滑过链接时触发不必要的预加载
            link._preloadTimer = setTimeout(() => {
                linkPreload.preload(url);
                link.dataset.preloaded = 'true';
            }, 100);
            
            // 如果鼠标移开，取消预加载
            link.addEventListener('mouseout', () => {
                if (link._preloadTimer) {
                    clearTimeout(link._preloadTimer);
                }
            }, { once: true });
        },
        
        /**
         * 触摸开始事件处理
         * @param {TouchEvent} e - 触摸事件
         */
        onTouchStart: function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            
            const url = link.getAttribute('href');
            if (!url || url.startsWith('#') || url.startsWith('javascript:') || url.startsWith('mailto:') || url.startsWith('tel:')) return;
            
            // 防止重复预加载
            if (link.dataset.preloaded === 'true') return;
            
            // 检查是否滚动到页面底部的阈值
            const scrollPosition = window.scrollY + window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollThreshold = documentHeight * config.preloadThreshold;
            
            if (scrollPosition > scrollThreshold) {
                linkPreload.preload(url);
                link.dataset.preloaded = 'true';
            }
        },
        
        /**
         * 预加载URL
         * @param {string} url - 要预加载的URL
         */
        preload: function(url) {
            // 如果是外部链接，不预加载
            if (url.startsWith('http') && !url.includes(window.location.hostname)) return;
            
            // 创建预加载链接
            const preloadLink = document.createElement('link');
            preloadLink.rel = 'prefetch';
            preloadLink.href = url;
            document.head.appendChild(preloadLink);
            
            log(`预加载链接: ${url}`);
        }
    };
    
    // 资源优先级处理
    const priorityLoader = {
        /**
         * 初始化资源优先级处理
         */
        init: function() {
            const elements = document.querySelectorAll(`[${config.priorityAttr}]`);
            if (!elements.length) return;
            
            // 按优先级排序
            const sorted = Array.from(elements).sort((a, b) => {
                const priorityA = parseInt(a.getAttribute(config.priorityAttr)) || 0;
                const priorityB = parseInt(b.getAttribute(config.priorityAttr)) || 0;
                return priorityA - priorityB;
            });
            
            // 按优先级加载
            sorted.forEach(element => {
                this.loadWithPriority(element);
            });
            
            log(`初始化资源优先级: ${elements.length}个元素`);
        },
        
        /**
         * 按优先级加载元素
         * @param {Element} element - 要加载的元素
         */
        loadWithPriority: function(element) {
            const priority = parseInt(element.getAttribute(config.priorityAttr)) || 0;
            
            // 高优先级资源立即加载
            if (priority <= 1) {
                this.loadResource(element);
                return;
            }
            
            // 中优先级资源在DOMContentLoaded后加载
            if (priority === 2) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        this.loadResource(element);
                    });
                } else {
                    this.loadResource(element);
                }
                return;
            }
            
            // 低优先级资源在页面加载完成后加载
            if (priority >= 3) {
                if (document.readyState === 'complete') {
                    this.loadResource(element);
                } else {
                    window.addEventListener('load', () => {
                        // 添加一些延迟，让更重要的资源先加载完
                        setTimeout(() => {
                            this.loadResource(element);
                        }, (priority - 2) * 200);
                    });
                }
                return;
            }
        },
        
        /**
         * 加载资源
         * @param {Element} element - 要加载的元素
         */
        loadResource: function(element) {
            const type = element.tagName.toLowerCase();
            
            switch (type) {
                case 'script':
                    if (element.dataset.src) {
                        element.src = element.dataset.src;
                        delete element.dataset.src;
                    }
                    break;
                    
                case 'link':
                    if (element.dataset.href) {
                        element.href = element.dataset.href;
                        delete element.dataset.href;
                    }
                    break;
                    
                default:
                    // 对于其他元素，使用延迟加载处理
                    if (element.dataset.src || element.dataset.bg) {
                        lazyLoad.loadElement(element);
                    }
            }
            
            log(`按优先级加载: ${type} (优先级 ${element.getAttribute(config.priorityAttr)})`);
        }
    };
    
    /**
     * 记录日志
     * @param {string} message - 日志消息
     * @param {Error} [error] - 错误对象（可选）
     * @param {boolean} [isError=false] - 是否为错误日志
     */
    function log(message, error, isError = false) {
        if (!config.debug) return;
        
        if (isError) {
            console.error(`[资源优化器] ${message}`, error);
        } else {
            console.log(`[资源优化器] ${message}`);
        }
    }
    
    /**
     * 初始化资源优化器
     * @param {Object} options - 配置选项
     */
    function init(options = {}) {
        // 合并配置
        Object.assign(config, options);
        
        // 清理过期缓存
        cache.cleanup();
        
        // 初始化各模块
        lazyLoad.init();
        linkPreload.init();
        priorityLoader.init();
        
        log('资源优化器初始化完成');
    }
    
    /**
     * 手动预加载资源
     * @param {string} url - 资源URL
     * @param {string} [type='fetch'] - 预加载类型（'fetch'|'image'|'script'|'style'）
     */
    function preload(url, type = 'fetch') {
        if (!url) return;
        
        switch (type) {
            case 'image':
                const img = new Image();
                img.src = url;
                break;
                
            case 'script':
                const script = document.createElement('script');
                script.src = url;
                script.async = true;
                document.head.appendChild(script);
                break;
                
            case 'style':
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = url;
                document.head.appendChild(link);
                break;
                
            default:
                // 使用fetch预加载
                fetch(url, { method: 'GET', mode: 'no-cors', cache: 'force-cache' })
                    .then(response => {
                        log(`预加载完成: ${url}`);
                        return response;
                    })
                    .catch(error => {
                        log(`预加载失败: ${url}`, error, true);
                    });
        }
        
        log(`手动预加载: ${url} (${type})`);
    }
    
    /**
     * 设置配置选项
     * @param {Object} options - 配置选项
     */
    function setConfig(options) {
        Object.assign(config, options);
        log('更新配置', config);
    }
    
    // 公开API
    return {
        init,
        preload,
        setConfig,
        cache
    };
})();

// 自动初始化
document.addEventListener('DOMContentLoaded', function() {
    ResourceOptimizer.init();
}); 