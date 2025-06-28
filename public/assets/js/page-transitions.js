/**
 * AlingAi Pro - 页面过渡动画效果
 * 提供平滑的页面切换动画
 * 
 * @version 1.1.0
 * @author AlingAi Team
 */

// 页面过渡效果控制器
const PageTransitions = {
    // 配置选项
    options: {
        animationDuration: 600, // 动画持续时间（毫秒）
        animationType: 'fade', // 默认动画类型
        cachePages: true, // 是否缓存页面
        prefetchLinks: true, // 是否预加载链接
        preventScrollReset: false, // 是否阻止滚动重置
        enableProgressBar: true, // 是否启用进度条
        progressBarColor: '#0a84ff', // 进度条颜色
        progressBarHeight: '3px', // 进度条高度
        enablePreloading: true, // 是否启用预加载动画
        historyManagement: true, // 是否管理浏览器历史
        smoothScrolling: true // 是否启用平滑滚动
    },
    
    // 缓存的页面
    cachedPages: new Map(),
    
    // 当前页面的URL
    currentUrl: window.location.href,
    
    // 是否正在进行动画
    isAnimating: false,
    
    // 进度条元素
    progressBar: null,
    
    // 预加载器元素
    preloader: null,
    
    /**
     * 初始化页面过渡效果
     * @param {Object} customOptions - 自定义配置选项
     */
    init(customOptions = {}) {
        // 合并自定义选项
        this.options = {...this.options, ...customOptions};
        
        // 创建过渡动画容器
        this.createTransitionContainer();
        
        // 创建进度条
        if (this.options.enableProgressBar) {
            this.createProgressBar();
        }
        
        // 创建预加载动画
        if (this.options.enablePreloading) {
            this.createPreloader();
        }
        
        // 绑定链接点击事件
        this.bindLinkClicks();
        
        // 绑定浏览器历史记录事件
        if (this.options.historyManagement) {
            this.bindHistoryEvents();
        }
        
        // 预加载链接
        if (this.options.prefetchLinks) {
            this.prefetchLinks();
        }
        
        // 启用平滑滚动
        if (this.options.smoothScrolling) {
            this.enableSmoothScrolling();
        }
        
        console.log('页面过渡动画系统已初始化');
    },
    
    /**
     * 创建过渡动画容器
     */
    createTransitionContainer() {
        // 创建过渡层元素
        const transitionLayer = document.createElement('div');
        transitionLayer.className = 'page-transition-layer';
        
        // 添加样式
        const style = document.createElement('style');
        style.textContent = `
            .page-transition-layer {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: var(--background-color, #0a0e17);
                z-index: 9999;
                pointer-events: none;
                opacity: 0;
                visibility: hidden;
                transition: opacity ${this.options.animationDuration}ms ease, visibility ${this.options.animationDuration}ms ease;
            }
            
            .page-transition-layer.fade-in {
                opacity: 1;
                visibility: visible;
            }
            
            .page-transition-layer.slide-in {
                transform: translateX(-100%);
                opacity: 1;
                visibility: visible;
                transition: transform ${this.options.animationDuration}ms ease;
            }
            
            .page-transition-layer.slide-out {
                transform: translateX(0);
                transition: transform ${this.options.animationDuration}ms ease;
            }
            
            .page-transition-layer.zoom-in {
                opacity: 1;
                visibility: visible;
                transform: scale(0);
                transition: opacity ${this.options.animationDuration}ms ease, transform ${this.options.animationDuration}ms ease;
            }
            
            .page-transition-layer.zoom-out {
                transform: scale(1);
                transition: opacity ${this.options.animationDuration}ms ease, transform ${this.options.animationDuration}ms ease;
            }
            
            .page-transition-layer.flip-in {
                opacity: 1;
                visibility: visible;
                transform: rotateY(90deg);
                transition: transform ${this.options.animationDuration}ms ease;
                transform-style: preserve-3d;
                perspective: 1000px;
            }
            
            .page-transition-layer.flip-out {
                transform: rotateY(0deg);
                transition: transform ${this.options.animationDuration}ms ease;
            }
            
            .page-transition-layer.push-in {
                opacity: 1;
                visibility: visible;
                transform: translateX(100%);
                transition: transform ${this.options.animationDuration}ms ease;
            }
            
            .page-transition-layer.push-out {
                transform: translateX(0);
                transition: transform ${this.options.animationDuration}ms ease;
            }
            
            body.page-transitioning {
                overflow: hidden;
            }
            
            .page-transition-progress {
                position: fixed;
                top: 0;
                left: 0;
                width: 0;
                height: ${this.options.progressBarHeight};
                background-color: ${this.options.progressBarColor};
                z-index: 10000;
                transition: width 0.2s ease;
            }
            
            .page-preloader {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 10001;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            
            .page-preloader.visible {
                opacity: 1;
                visibility: visible;
            }
            
            .page-preloader-spinner {
                width: 40px;
                height: 40px;
                border: 3px solid rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                border-top-color: ${this.options.progressBarColor};
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        
        // 添加到文档
        document.head.appendChild(style);
        document.body.appendChild(transitionLayer);
        
        // 保存引用
        this.transitionLayer = transitionLayer;
    },
    
    /**
     * 创建进度条
     */
    createProgressBar() {
        // 创建进度条元素
        const progressBar = document.createElement('div');
        progressBar.className = 'page-transition-progress';
        
        // 添加到文档
        document.body.appendChild(progressBar);
        
        // 保存引用
        this.progressBar = progressBar;
    },
    
    /**
     * 创建预加载动画
     */
    createPreloader() {
        // 创建预加载器容器
        const preloader = document.createElement('div');
        preloader.className = 'page-preloader';
        
        // 创建旋转动画
        const spinner = document.createElement('div');
        spinner.className = 'page-preloader-spinner';
        
        // 添加到预加载器
        preloader.appendChild(spinner);
        
        // 添加到文档
        document.body.appendChild(preloader);
        
        // 保存引用
        this.preloader = preloader;
    },
    
    /**
     * 绑定链接点击事件
     */
    bindLinkClicks() {
        // 获取所有内部链接
        document.addEventListener('click', (e) => {
            // 检查是否点击了链接
            const link = e.target.closest('a');
            
            if (!link) return;
            
            // 检查是否是内部链接
            const url = new URL(link.href, window.location.origin);
            const isInternalLink = url.origin === window.location.origin;
            
            // 检查是否有特殊属性阻止过渡动画
            const noTransition = link.hasAttribute('data-no-transition');
            
            // 检查是否是锚点链接
            const isAnchorLink = url.hash && url.pathname === window.location.pathname;
            
            if (isInternalLink && !noTransition && !isAnchorLink) {
                e.preventDefault();
                
                // 如果链接指向当前页面，则不执行过渡
                if (url.href === window.location.href) return;
                
                // 获取过渡类型
                const transitionType = link.getAttribute('data-transition-type') || 
                                      link.closest('[data-transition-type]')?.getAttribute('data-transition-type');
                
                // 执行页面过渡
                this.navigateTo(url.href, transitionType);
            } else if (isAnchorLink && this.options.smoothScrolling) {
                // 如果是锚点链接，执行平滑滚动
                e.preventDefault();
                
                const targetId = url.hash.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    this.smoothScrollTo(targetElement);
                }
            }
        });
    },
    
    /**
     * 绑定浏览器历史记录事件
     */
    bindHistoryEvents() {
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.url) {
                // 执行页面过渡（使用反向动画）
                this.navigateTo(e.state.url, e.state.transitionType, true);
            }
        });
        
        // 初始化当前页面的历史状态
        if (!window.history.state) {
            window.history.replaceState({
                url: window.location.href,
                transitionType: this.options.animationType,
                scrollPosition: window.scrollY
            }, '', window.location.href);
        }
    },
    
    /**
     * 导航到指定URL
     * @param {string} url - 目标URL
     * @param {string|null} transitionType - 过渡动画类型
     * @param {boolean} isBackButton - 是否是通过浏览器后退按钮触发
     */
    async navigateTo(url, transitionType = null, isBackButton = false) {
        // 如果正在动画中，则忽略
        if (this.isAnimating) return;
        
        this.isAnimating = true;
        
        // 使用指定的动画类型或默认类型
        const animationType = transitionType || this.options.animationType;
        
        // 添加页面过渡中的类名
        document.body.classList.add('page-transitioning');
        
        // 显示进度条
        if (this.progressBar) {
            this.updateProgressBar(0);
            this.progressBar.style.display = 'block';
        }
        
        // 显示预加载动画
        if (this.preloader) {
            this.preloader.classList.add('visible');
        }
        
        // 执行离开动画
        await this.playExitAnimation(animationType);
        
        try {
            // 加载新页面内容
            const newPageContent = await this.loadPage(url);
            
            // 保存当前滚动位置
            const scrollPosition = window.scrollY;
            
            // 更新浏览器历史记录
            if (!isBackButton) {
                window.history.pushState({
                    url,
                    transitionType: animationType,
                    scrollPosition
                }, '', url);
            }
            
            // 更新页面内容
            this.updatePageContent(newPageContent);
            
            // 更新当前URL
            this.currentUrl = url;
            
            // 恢复滚动位置或滚动到顶部
            if (isBackButton && window.history.state && window.history.state.scrollPosition) {
                if (!this.options.preventScrollReset) {
                    window.scrollTo(0, window.history.state.scrollPosition);
                }
            } else if (!this.options.preventScrollReset) {
                window.scrollTo(0, 0);
            }
            
            // 执行进入动画
            await this.playEnterAnimation(animationType);
            
            // 触发页面加载完成事件
            this.triggerPageLoadEvent();
        } catch (error) {
            console.error('页面加载失败:', error);
            
            // 如果加载失败，直接跳转
            window.location.href = url;
        } finally {
            // 隐藏进度条
            if (this.progressBar) {
                this.progressBar.style.display = 'none';
            }
            
            // 隐藏预加载动画
            if (this.preloader) {
                this.preloader.classList.remove('visible');
            }
            
            // 移除过渡中的类名
            document.body.classList.remove('page-transitioning');
            
            this.isAnimating = false;
        }
    },
    
    /**
     * 播放退出动画
     * @param {string} animationType - 动画类型
     * @returns {Promise} 动画完成的Promise
     */
    playExitAnimation(animationType) {
        return new Promise(resolve => {
            // 根据动画类型添加相应的类
            switch (animationType) {
                case 'slide':
                    this.transitionLayer.classList.add('slide-in');
                    break;
                case 'zoom':
                    this.transitionLayer.classList.add('zoom-in');
                    break;
                case 'flip':
                    this.transitionLayer.classList.add('flip-in');
                    break;
                case 'push':
                    this.transitionLayer.classList.add('push-in');
                    break;
                case 'fade':
                default:
                    this.transitionLayer.classList.add('fade-in');
                    break;
            }
            
            // 动画结束后解析Promise
            setTimeout(resolve, this.options.animationDuration);
        });
    },
    
    /**
     * 播放进入动画
     * @param {string} animationType - 动画类型
     * @returns {Promise} 动画完成的Promise
     */
    playEnterAnimation(animationType) {
        return new Promise(resolve => {
            // 根据动画类型移除相应的类
            switch (animationType) {
                case 'slide':
                    this.transitionLayer.classList.remove('slide-in');
                    this.transitionLayer.classList.add('slide-out');
                    break;
                case 'zoom':
                    this.transitionLayer.classList.remove('zoom-in');
                    this.transitionLayer.classList.add('zoom-out');
                    break;
                case 'flip':
                    this.transitionLayer.classList.remove('flip-in');
                    this.transitionLayer.classList.add('flip-out');
                    break;
                case 'push':
                    this.transitionLayer.classList.remove('push-in');
                    this.transitionLayer.classList.add('push-out');
                    break;
                case 'fade':
                default:
                    this.transitionLayer.classList.remove('fade-in');
                    break;
            }
            
            // 动画结束后解析Promise
            setTimeout(() => {
                // 重置所有动画类
                this.transitionLayer.classList.remove('slide-out', 'zoom-out', 'flip-out', 'push-out');
                resolve();
            }, this.options.animationDuration);
        });
    },
    
    /**
     * 加载页面
     * @param {string} url - 要加载的URL
     * @returns {Promise<Document>} 解析后的HTML文档
     */
    async loadPage(url) {
        // 检查是否已缓存
        if (this.options.cachePages && this.cachedPages.has(url)) {
            return this.cachedPages.get(url);
        }
        
        // 创建AbortController以便在需要时中止请求
        const controller = new AbortController();
        const signal = controller.signal;
        
        // 设置超时
        const timeoutId = setTimeout(() => controller.abort(), 10000);
        
        try {
            // 更新进度条
            if (this.progressBar) {
                this.updateProgressBar(10);
            }
            
            // 发起请求
            const response = await fetch(url, { signal });
            
            // 更新进度条
            if (this.progressBar) {
                this.updateProgressBar(50);
            }
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // 获取HTML文本
            const html = await response.text();
            
            // 更新进度条
            if (this.progressBar) {
                this.updateProgressBar(80);
            }
            
            // 解析HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // 更新进度条
            if (this.progressBar) {
                this.updateProgressBar(100);
            }
            
            // 缓存页面
            if (this.options.cachePages) {
                this.cachedPages.set(url, doc);
                
                // 限制缓存大小
                if (this.cachedPages.size > 10) {
                    const firstKey = this.cachedPages.keys().next().value;
                    this.cachedPages.delete(firstKey);
                }
            }
            
            return doc;
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('请求超时');
            }
            throw error;
        } finally {
            clearTimeout(timeoutId);
        }
    },
    
    /**
     * 更新页面内容
     * @param {Document} newDocument - 新页面的文档对象
     */
    updatePageContent(newDocument) {
        // 获取当前文档的主要内容容器
        const currentMain = document.querySelector('main') || document.body;
        
        // 获取新文档的主要内容容器
        const newMain = newDocument.querySelector('main') || newDocument.body;
        
        // 更新页面标题
        document.title = newDocument.title;
        
        // 更新头部元素
        this.updateHeadElements(newDocument);
        
        // 更新导航状态
        this.updateNavigationState(newDocument);
        
        // 更新主要内容
        currentMain.innerHTML = newMain.innerHTML;
        
        // 重新初始化脚本
        this.reinitializeScripts(newDocument);
    },
    
    /**
     * 更新头部元素
     * @param {Document} newDocument - 新页面的文档对象
     */
    updateHeadElements(newDocument) {
        // 获取当前和新的头部元素
        const currentHead = document.head;
        const newHead = newDocument.head;
        
        // 更新meta标签
        this.updateMetaTags(currentHead, newHead);
        
        // 更新样式表
        this.updateStylesheets(currentHead, newHead);
        
        // 更新规范链接
        this.updateCanonicalLink(currentHead, newHead);
    },
    
    /**
     * 更新Meta标签
     * @param {HTMLHeadElement} currentHead - 当前头部元素
     * @param {HTMLHeadElement} newHead - 新的头部元素
     */
    updateMetaTags(currentHead, newHead) {
        // 更新描述
        this.updateMetaTag(currentHead, newHead, 'description');
        
        // 更新关键词
        this.updateMetaTag(currentHead, newHead, 'keywords');
        
        // 更新Open Graph标签
        this.updateMetaTag(currentHead, newHead, 'og:title');
        this.updateMetaTag(currentHead, newHead, 'og:description');
        this.updateMetaTag(currentHead, newHead, 'og:image');
        this.updateMetaTag(currentHead, newHead, 'og:url');
    },
    
    /**
     * 更新特定的Meta标签
     * @param {HTMLHeadElement} currentHead - 当前头部元素
     * @param {HTMLHeadElement} newHead - 新的头部元素
     * @param {string} name - Meta标签的name或property属性值
     */
    updateMetaTag(currentHead, newHead, name) {
        // 查找当前和新的Meta标签
        const currentMeta = currentHead.querySelector(`meta[name="${name}"], meta[property="${name}"]`);
        const newMeta = newHead.querySelector(`meta[name="${name}"], meta[property="${name}"]`);
        
        if (newMeta) {
            if (currentMeta) {
                // 更新现有标签
                currentMeta.content = newMeta.content;
            } else {
                // 添加新标签
                currentHead.appendChild(newMeta.cloneNode(true));
            }
        } else if (currentMeta) {
            // 如果新页面没有此标签，但当前页面有，则删除
            currentMeta.remove();
        }
    },
    
    /**
     * 更新样式表
     * @param {HTMLHeadElement} currentHead - 当前头部元素
     * @param {HTMLHeadElement} newHead - 新的头部元素
     */
    updateStylesheets(currentHead, newHead) {
        // 获取当前和新的样式表
        const currentStylesheets = Array.from(currentHead.querySelectorAll('link[rel="stylesheet"]'));
        const newStylesheets = Array.from(newHead.querySelectorAll('link[rel="stylesheet"]'));
        
        // 检查新样式表
        newStylesheets.forEach(newStylesheet => {
            const href = newStylesheet.getAttribute('href');
            const exists = currentStylesheets.some(sheet => sheet.getAttribute('href') === href);
            
            if (!exists) {
                // 添加新样式表
                currentHead.appendChild(newStylesheet.cloneNode(true));
            }
        });
    },
    
    /**
     * 更新规范链接
     * @param {HTMLHeadElement} currentHead - 当前头部元素
     * @param {HTMLHeadElement} newHead - 新的头部元素
     */
    updateCanonicalLink(currentHead, newHead) {
        // 查找当前和新的规范链接
        const currentCanonical = currentHead.querySelector('link[rel="canonical"]');
        const newCanonical = newHead.querySelector('link[rel="canonical"]');
        
        if (newCanonical) {
            if (currentCanonical) {
                // 更新现有链接
                currentCanonical.href = newCanonical.href;
            } else {
                // 添加新链接
                currentHead.appendChild(newCanonical.cloneNode(true));
            }
        }
    },
    
    /**
     * 更新导航状态
     * @param {Document} newDocument - 新页面的文档对象
     */
    updateNavigationState(newDocument) {
        // 获取当前URL的路径
        const currentPath = window.location.pathname;
        
        // 更新导航链接的活动状态
        document.querySelectorAll('nav a').forEach(link => {
            const href = link.getAttribute('href');
            const isActive = href === currentPath || href === currentPath + '/' || currentPath.startsWith(href) && href !== '/';
            
            // 移除所有活动类
            link.classList.remove('active', 'current');
            
            // 添加活动类
            if (isActive) {
                link.classList.add('active');
            }
        });
    },
    
    /**
     * 重新初始化脚本
     * @param {Document} newDocument - 新页面的文档对象
     */
    reinitializeScripts(newDocument) {
        // 获取新页面中的内联脚本
        const scripts = newDocument.querySelectorAll('main script:not([src]), main script[src^="data:"]');
        
        // 执行内联脚本
        scripts.forEach(script => {
            try {
                const newScript = document.createElement('script');
                
                // 复制属性
                Array.from(script.attributes).forEach(attr => {
                    newScript.setAttribute(attr.name, attr.value);
                });
                
                // 复制内容
                newScript.textContent = script.textContent;
                
                // 添加到文档
                document.body.appendChild(newScript);
                
                // 执行后移除
                newScript.remove();
            } catch (error) {
                console.error('执行脚本时出错:', error);
            }
        });
        
        // 触发DOMContentLoaded事件
        const event = new Event('DOMContentLoaded');
        document.dispatchEvent(event);
    },
    
    /**
     * 更新进度条
     * @param {number} progress - 进度值（0-100）
     */
    updateProgressBar(progress) {
        if (!this.progressBar) return;
        
        // 设置进度条宽度
        this.progressBar.style.width = `${progress}%`;
        
        // 如果进度为100%，延迟隐藏进度条
        if (progress >= 100) {
            setTimeout(() => {
                this.progressBar.style.width = '0';
            }, 200);
        }
    },
    
    /**
     * 预加载链接
     */
    prefetchLinks() {
        // 延迟执行，让页面先加载完成
        setTimeout(() => {
            // 获取所有内部链接
            const links = document.querySelectorAll('a[href^="/"]:not([data-no-prefetch])');
            
            // 创建一个Set来存储已预加载的URL
            const prefetchedUrls = new Set();
            
            // 预加载可见链接
            this.prefetchVisibleLinks(links, prefetchedUrls);
            
            // 监听滚动事件，预加载新可见的链接
            window.addEventListener('scroll', this.debounce(() => {
                this.prefetchVisibleLinks(links, prefetchedUrls);
            }, 200));
        }, 2000);
    },
    
    /**
     * 预加载可见链接
     * @param {NodeList} links - 链接元素列表
     * @param {Set} prefetchedUrls - 已预加载的URL集合
     */
    prefetchVisibleLinks(links, prefetchedUrls) {
        // 创建交叉观察器
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const link = entry.target;
                    const url = link.href;
                    
                    // 如果链接已经预加载，则跳过
                    if (prefetchedUrls.has(url)) {
                        observer.unobserve(link);
                        return;
                    }
                    
                    // 标记为已预加载
                    prefetchedUrls.add(url);
                    
                    // 创建预加载链接
                    const prefetchLink = document.createElement('link');
                    prefetchLink.rel = 'prefetch';
                    prefetchLink.href = url;
                    
                    // 添加到文档
                    document.head.appendChild(prefetchLink);
                    
                    // 停止观察
                    observer.unobserve(link);
                }
            });
        }, { threshold: 0.1 });
        
        // 观察所有链接
        links.forEach(link => {
            observer.observe(link);
        });
    },
    
    /**
     * 启用平滑滚动
     */
    enableSmoothScrolling() {
        // 为所有锚点链接添加平滑滚动
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href^="#"]:not([data-no-smooth-scroll])');
            
            if (link) {
                const targetId = link.getAttribute('href').substring(1);
                
                if (targetId) {
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        e.preventDefault();
                        this.smoothScrollTo(targetElement);
                    }
                }
            }
        });
    },
    
    /**
     * 平滑滚动到元素
     * @param {HTMLElement} element - 目标元素
     */
    smoothScrollTo(element) {
        // 获取元素位置
        const rect = element.getBoundingClientRect();
        const offset = window.pageYOffset || document.documentElement.scrollTop;
        const top = rect.top + offset;
        
        // 计算滚动距离
        const startPosition = window.pageYOffset;
        const distance = top - startPosition;
        
        // 如果距离为0，则不需要滚动
        if (distance === 0) return;
        
        // 滚动持续时间（毫秒）
        const duration = 800;
        
        // 开始时间
        let startTime = null;
        
        // 缓动函数
        const easeInOutQuad = (t) => {
            return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
        };
        
        // 滚动动画
        const scroll = (currentTime) => {
            if (startTime === null) {
                startTime = currentTime;
            }
            
            // 计算经过的时间
            const timeElapsed = currentTime - startTime;
            
            // 计算滚动位置
            const progress = Math.min(timeElapsed / duration, 1);
            const eased = easeInOutQuad(progress);
            const position = startPosition + distance * eased;
            
            // 执行滚动
            window.scrollTo(0, position);
            
            // 如果动画未完成，继续滚动
            if (timeElapsed < duration) {
                requestAnimationFrame(scroll);
            }
        };
        
        // 开始滚动动画
        requestAnimationFrame(scroll);
    },
    
    /**
     * 触发页面加载完成事件
     */
    triggerPageLoadEvent() {
        // 创建自定义事件
        const event = new CustomEvent('pageTransitionComplete', {
            detail: {
                url: window.location.href,
                timestamp: Date.now()
            }
        });
        
        // 触发事件
        document.dispatchEvent(event);
        
        // 触发DOMContentLoaded事件
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializePageComponents();
            });
        } else {
            this.initializePageComponents();
        }
    },
    
    /**
     * 初始化页面组件
     */
    initializePageComponents() {
        // 初始化滚动动画
        if (window.ScrollAnimations && typeof ScrollAnimations.init === 'function') {
            ScrollAnimations.init();
        }
        
        // 初始化自定义光标
        if (window.CursorEffects && typeof CursorEffects.init === 'function') {
            CursorEffects.init();
        }
        
        // 初始化资源优化
        if (window.ResourceOptimizer && typeof ResourceOptimizer.init === 'function') {
            ResourceOptimizer.init();
        }
        
        // 初始化安全监控
        if (window.SecurityMonitor && typeof SecurityMonitor.init === 'function') {
            SecurityMonitor.init();
        }
    },
    
    /**
     * 防抖函数
     * @param {Function} func - 要执行的函数
     * @param {number} wait - 等待时间（毫秒）
     * @returns {Function} 防抖后的函数
     */
    debounce(func, wait) {
        let timeout;
        
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// 自动初始化页面过渡效果
document.addEventListener('DOMContentLoaded', () => {
    PageTransitions.init();
}); 