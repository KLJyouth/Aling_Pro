/**
 * AlingAi Pro - 滚动动画效果
 * 提供基于滚动的元素动画效果
 * 
 * @version 1.1.0
 * @author AlingAi Team
 */

// 滚动动画控制器
const ScrollAnimations = {
    // 配置选项
    options: {
        animationThreshold: 0.2, // 动画触发阈值
        animationDuration: 800, // 默认动画持续时间（毫秒）
        animationEasing: 'cubic-bezier(0.25, 0.1, 0.25, 1)', // 默认动画缓动函数
        defaultAnimation: 'fade-in', // 默认动画类型
        rootMargin: '0px 0px -100px 0px', // 观察器根元素边距
        disableOnMobile: false, // 是否在移动设备上禁用
        enableParallax: true, // 是否启用视差效果
        parallaxSpeed: 0.5, // 视差速度
        useGPUAcceleration: true, // 是否使用GPU加速
        enableRepeatAnimations: false, // 是否启用重复动画
        animationOffset: 100, // 动画偏移量（像素）
        staggerDelay: 100, // 交错延迟（毫秒）
        staggerItems: true, // 是否启用交错动画
        animateOnce: true, // 是否只动画一次
        observeChanges: true, // 是否观察DOM变化
        animationClasses: {
            'fade-in': {
                initial: { opacity: 0 },
                animate: { opacity: 1 }
            },
            'fade-in-up': {
                initial: { opacity: 0, transform: 'translateY(40px)' },
                animate: { opacity: 1, transform: 'translateY(0)' }
            },
            'fade-in-down': {
                initial: { opacity: 0, transform: 'translateY(-40px)' },
                animate: { opacity: 1, transform: 'translateY(0)' }
            },
            'fade-in-left': {
                initial: { opacity: 0, transform: 'translateX(40px)' },
                animate: { opacity: 1, transform: 'translateX(0)' }
            },
            'fade-in-right': {
                initial: { opacity: 0, transform: 'translateX(-40px)' },
                animate: { opacity: 1, transform: 'translateX(0)' }
            },
            'zoom-in': {
                initial: { opacity: 0, transform: 'scale(0.8)' },
                animate: { opacity: 1, transform: 'scale(1)' }
            },
            'zoom-out': {
                initial: { opacity: 0, transform: 'scale(1.2)' },
                animate: { opacity: 1, transform: 'scale(1)' }
            },
            'flip-up': {
                initial: { opacity: 0, transform: 'perspective(400px) rotateX(90deg)' },
                animate: { opacity: 1, transform: 'perspective(400px) rotateX(0)' }
            },
            'flip-down': {
                initial: { opacity: 0, transform: 'perspective(400px) rotateX(-90deg)' },
                animate: { opacity: 1, transform: 'perspective(400px) rotateX(0)' }
            },
            'flip-left': {
                initial: { opacity: 0, transform: 'perspective(400px) rotateY(-90deg)' },
                animate: { opacity: 1, transform: 'perspective(400px) rotateY(0)' }
            },
            'flip-right': {
                initial: { opacity: 0, transform: 'perspective(400px) rotateY(90deg)' },
                animate: { opacity: 1, transform: 'perspective(400px) rotateY(0)' }
            },
            'slide-up': {
                initial: { transform: 'translateY(100%)' },
                animate: { transform: 'translateY(0)' }
            },
            'slide-down': {
                initial: { transform: 'translateY(-100%)' },
                animate: { transform: 'translateY(0)' }
            },
            'slide-left': {
                initial: { transform: 'translateX(100%)' },
                animate: { transform: 'translateX(0)' }
            },
            'slide-right': {
                initial: { transform: 'translateX(-100%)' },
                animate: { transform: 'translateX(0)' }
            },
            'rotate': {
                initial: { opacity: 0, transform: 'rotate(45deg)' },
                animate: { opacity: 1, transform: 'rotate(0)' }
            },
            'scale': {
                initial: { transform: 'scale(0)' },
                animate: { transform: 'scale(1)' }
            },
            'bounce': {
                initial: { transform: 'translateY(40px)', opacity: 0 },
                animate: { transform: 'translateY(0)', opacity: 1 },
                easing: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)'
            },
            'swing': {
                initial: { transform: 'rotate(-10deg)', transformOrigin: 'top center', opacity: 0 },
                animate: { transform: 'rotate(0)', transformOrigin: 'top center', opacity: 1 },
                easing: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)'
            }
        }
    },
    
    // 状态
    state: {
        observer: null, // Intersection Observer实例
        mutationObserver: null, // Mutation Observer实例
        animatedElements: new Set(), // 已动画的元素集合
        isInitialized: false, // 是否已初始化
        isMobile: false, // 是否是移动设备
        scrollY: 0, // 当前滚动位置
        viewportHeight: 0, // 视口高度
        parallaxElements: [], // 视差元素
        resizeObserver: null // Resize Observer实例
    },
    
    /**
     * 初始化滚动动画
     * @param {Object} customOptions - 自定义配置选项
     */
    init(customOptions = {}) {
        // 合并自定义选项
        this.options = {...this.options, ...customOptions};
        
        // 检测是否是移动设备
        this.state.isMobile = this.isMobileDevice();
        
        // 如果是移动设备且设置为禁用，则不初始化
        if (this.state.isMobile && this.options.disableOnMobile) {
            console.log('移动设备上禁用滚动动画');
            return;
        }
        
        // 如果已经初始化，则不重复初始化
        if (this.state.isInitialized) return;
        
        // 获取视口高度
        this.state.viewportHeight = window.innerHeight;
        
        // 添加样式
        this.addStyles();
        
        // 创建观察器
        this.createObserver();
        
        // 观察所有动画元素
        this.observeAnimationElements();
        
        // 初始化视差效果
        if (this.options.enableParallax) {
            this.initParallax();
        }
        
        // 观察DOM变化
        if (this.options.observeChanges) {
            this.observeDOMChanges();
        }
        
        // 监听窗口大小变化
        this.state.resizeObserver = new ResizeObserver(this.debounce(() => {
            this.state.viewportHeight = window.innerHeight;
            
            // 重新初始化视差效果
            if (this.options.enableParallax) {
                this.updateParallaxElements();
            }
        }, 200));
        
        this.state.resizeObserver.observe(document.body);
        
        // 标记为已初始化
        this.state.isInitialized = true;
        
        console.log('滚动动画系统已初始化');
    },
    
    /**
     * 检查是否为移动设备
     * @returns {boolean} 是否为移动设备
     */
    isMobileDevice() {
        return (
            ('ontouchstart' in window) ||
            (navigator.maxTouchPoints > 0) ||
            (navigator.msMaxTouchPoints > 0) ||
            (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))
        );
    },
    
    /**
     * 添加样式
     */
    addStyles() {
        // 创建样式元素
        const style = document.createElement('style');
        
        // 定义样式
        style.textContent = `
            /* 动画元素 */
            [data-scroll] {
                opacity: 0;
                transition-property: transform, opacity;
                transition-duration: ${this.options.animationDuration}ms;
                transition-timing-function: ${this.options.animationEasing};
                ${this.options.useGPUAcceleration ? 'transform: translateZ(0);' : ''}
                will-change: transform, opacity;
            }
            
            /* 动画元素（已显示） */
            [data-scroll].animated {
                opacity: 1;
            }
            
            /* 视差元素 */
            [data-parallax] {
                position: relative;
                overflow: hidden;
                will-change: transform;
                ${this.options.useGPUAcceleration ? 'transform: translateZ(0);' : ''}
            }
            
            /* 视差内容 */
            [data-parallax] > * {
                position: relative;
                will-change: transform;
                ${this.options.useGPUAcceleration ? 'transform: translateZ(0);' : ''}
            }
        `;
        
        // 添加到文档
        document.head.appendChild(style);
    },
    
    /**
     * 创建观察器
     */
    createObserver() {
        // 创建Intersection Observer
        this.state.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // 获取元素
                    const element = entry.target;
                    
                    // 获取动画类型
                    const animationType = element.getAttribute('data-scroll') || this.options.defaultAnimation;
                    
                    // 获取动画延迟
                    const delay = parseInt(element.getAttribute('data-scroll-delay') || 0);
                    
                    // 获取动画持续时间
                    const duration = parseInt(element.getAttribute('data-scroll-duration') || this.options.animationDuration);
                    
                    // 获取动画缓动函数
                    const easing = element.getAttribute('data-scroll-easing') || 
                                  this.options.animationClasses[animationType]?.easing || 
                                  this.options.animationEasing;
                    
                    // 获取动画偏移量
                    const offset = parseInt(element.getAttribute('data-scroll-offset') || this.options.animationOffset);
                    
                    // 获取是否只动画一次
                    const once = element.hasAttribute('data-scroll-once') ? 
                                true : 
                                this.options.animateOnce;
                    
                    // 获取是否启用交错动画
                    const stagger = element.hasAttribute('data-scroll-stagger') ? 
                                   true : 
                                   this.options.staggerItems;
                    
                    // 获取交错延迟
                    const staggerDelay = parseInt(element.getAttribute('data-scroll-stagger-delay') || this.options.staggerDelay);
                    
                    // 应用动画
                    this.applyAnimation(element, animationType, delay, duration, easing, offset, once, stagger, staggerDelay);
                    
                    // 如果只动画一次，则停止观察
                    if (once) {
                        this.state.observer.unobserve(element);
                        this.state.animatedElements.add(element);
                    }
                } else if (!this.options.animateOnce && !element.hasAttribute('data-scroll-once')) {
                    // 如果不是只动画一次，则在元素离开视口时移除动画
                    const element = entry.target;
                    element.classList.remove('animated');
                    
                    // 重置样式
                    const animationType = element.getAttribute('data-scroll') || this.options.defaultAnimation;
                    const initialStyles = this.options.animationClasses[animationType]?.initial || {};
                    
                    Object.entries(initialStyles).forEach(([property, value]) => {
                        element.style[property] = value;
                    });
                }
            });
        }, {
            threshold: this.options.animationThreshold,
            rootMargin: this.options.rootMargin
        });
    },
    
    /**
     * 观察动画元素
     */
    observeAnimationElements() {
        // 获取所有动画元素
        const elements = document.querySelectorAll('[data-scroll]');
        
        // 观察每个元素
        elements.forEach(element => {
            // 如果已经动画过，则不再观察
            if (this.state.animatedElements.has(element)) return;
            
            // 获取动画类型
            const animationType = element.getAttribute('data-scroll') || this.options.defaultAnimation;
            
            // 获取初始样式
            const initialStyles = this.options.animationClasses[animationType]?.initial || {};
            
            // 应用初始样式
            Object.entries(initialStyles).forEach(([property, value]) => {
                element.style[property] = value;
            });
            
            // 开始观察
            this.state.observer.observe(element);
        });
    },
    
    /**
     * 应用动画
     * @param {HTMLElement} element - 要应用动画的元素
     * @param {string} animationType - 动画类型
     * @param {number} delay - 动画延迟（毫秒）
     * @param {number} duration - 动画持续时间（毫秒）
     * @param {string} easing - 动画缓动函数
     * @param {number} offset - 动画偏移量（像素）
     * @param {boolean} once - 是否只动画一次
     * @param {boolean} stagger - 是否启用交错动画
     * @param {number} staggerDelay - 交错延迟（毫秒）
     */
    applyAnimation(element, animationType, delay, duration, easing, offset, once, stagger, staggerDelay) {
        // 获取动画样式
        const animationClass = this.options.animationClasses[animationType];
        
        if (!animationClass) {
            console.warn(`未知的动画类型: ${animationType}`);
            return;
        }
        
        // 设置过渡属性
        element.style.transitionDuration = `${duration}ms`;
        element.style.transitionTimingFunction = easing;
        element.style.transitionDelay = `${delay}ms`;
        
        // 如果有子元素需要交错动画
        if (stagger && element.hasAttribute('data-scroll-stagger-children')) {
            const children = element.querySelectorAll(element.getAttribute('data-scroll-stagger-children') || '*');
            
            children.forEach((child, index) => {
                // 设置子元素样式
                child.style.transitionDuration = `${duration}ms`;
                child.style.transitionTimingFunction = easing;
                child.style.transitionDelay = `${delay + (index * staggerDelay)}ms`;
                
                // 应用初始样式
                Object.entries(animationClass.initial).forEach(([property, value]) => {
                    child.style[property] = value;
                });
                
                // 触发重排
                void child.offsetHeight;
                
                // 应用动画样式
                setTimeout(() => {
                    Object.entries(animationClass.animate).forEach(([property, value]) => {
                        child.style[property] = value;
                    });
                }, 10);
            });
        } else {
            // 应用动画样式
            setTimeout(() => {
                Object.entries(animationClass.animate).forEach(([property, value]) => {
                    element.style[property] = value;
                });
                
                // 添加动画类
                element.classList.add('animated');
            }, 10);
        }
        
        // 触发自定义事件
        const animationEvent = new CustomEvent('scrollAnimationStart', {
            detail: { 
                element,
                animationType,
                delay,
                duration
            }
        });
        
        document.dispatchEvent(animationEvent);
        
        // 动画结束后触发事件
        const transitionEndHandler = (e) => {
            if (e.target !== element) return;
            
            // 触发自定义事件
            const animationEndEvent = new CustomEvent('scrollAnimationEnd', {
                detail: { 
                    element,
                    animationType,
                    delay,
                    duration
                }
            });
            
            document.dispatchEvent(animationEndEvent);
            
            // 移除事件监听器
            element.removeEventListener('transitionend', transitionEndHandler);
        };
        
        // 添加过渡结束事件监听器
        element.addEventListener('transitionend', transitionEndHandler);
    },
    
    /**
     * 初始化视差效果
     */
    initParallax() {
        // 获取所有带有data-parallax属性的元素
        const elements = document.querySelectorAll('[data-parallax]');
        
        elements.forEach(element => {
            // 如果元素已经在视口中，立即应用视差效果
            if (this.isElementInViewport(element)) {
                this.applyParallax(element);
            } else {
                // 否则，观察元素
                this.state.observer.observe(element);
            }
        });
    },
    
    /**
     * 检查元素是否在视口中
     * @param {HTMLElement} element - 要检查的元素
     * @returns {boolean} 元素是否在视口中
     */
    isElementInViewport(element) {
        const rect = element.getBoundingClientRect();
        
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) * (1 - this.options.animationThreshold) &&
            rect.bottom >= 0 &&
            rect.left <= (window.innerWidth || document.documentElement.clientWidth) &&
            rect.right >= 0
        );
    },
    
    /**
     * 为元素应用视差效果
     * @param {HTMLElement} element - 要应用视差效果的元素
     */
    applyParallax(element) {
        // 获取视口高度
        const viewportHeight = window.innerHeight;
        
        // 获取元素高度
        const elementHeight = element.offsetHeight;
        
        // 计算视差速度
        const speed = this.options.parallaxSpeed;
        
        // 计算视差偏移量
        const offset = -((element.getBoundingClientRect().top - (viewportHeight - elementHeight) / 2) / elementHeight) * speed;
        
        // 应用视差效果
        element.style.transform = `translateY(${offset}px)`;
    },
    
    /**
     * 更新视差元素
     */
    updateParallaxElements() {
        // 获取所有带有data-parallax属性的元素
        const elements = document.querySelectorAll('[data-parallax]');
        
        elements.forEach(element => {
            // 应用视差效果
            this.applyParallax(element);
        });
    },
    
    /**
     * 观察DOM变化
     */
    observeDOMChanges() {
        // 创建Mutation Observer
        this.state.mutationObserver = new MutationObserver(() => {
            // 重新观察所有动画元素
            this.observeAnimationElements();
        });
        
        // 配置Mutation Observer
        this.state.mutationObserver.observe(document.body, {
            attributes: true,
            attributeFilter: ['data-scroll']
        });
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
    },
    
    /**
     * 手动触发元素动画
     * @param {HTMLElement|string} element - 元素或选择器
     */
    animate(element) {
        // 如果是选择器字符串，获取元素
        if (typeof element === 'string') {
            const elements = document.querySelectorAll(element);
            elements.forEach(el => this.applyAnimation(el));
            return;
        }
        
        // 如果是单个元素，应用动画
        this.applyAnimation(element);
    },
    
    /**
     * 手动重置元素动画
     * @param {HTMLElement|string} element - 元素或选择器
     */
    reset(element) {
        // 如果是选择器字符串，获取元素
        if (typeof element === 'string') {
            const elements = document.querySelectorAll(element);
            elements.forEach(el => this.resetElement(el));
            return;
        }
        
        // 如果是单个元素，重置动画
        this.resetElement(element);
    },
    
    /**
     * 重置元素动画
     * @param {HTMLElement} element - 要重置的元素
     */
    resetElement(element) {
        // 移除动画类
        element.classList.remove('animated');
        
        // 重置样式
        const animationType = element.getAttribute('data-scroll') || this.options.defaultAnimation;
        const initialStyles = this.options.animationClasses[animationType]?.initial || {};
        
        Object.entries(initialStyles).forEach(([property, value]) => {
            element.style[property] = value;
        });
        
        // 触发自定义事件
        const resetEvent = new CustomEvent('scrollAnimationReset', {
            detail: { element }
        });
        
        document.dispatchEvent(resetEvent);
    },
    
    /**
     * 添加动画
     * @param {string|HTMLElement} selector - 选择器或元素
     * @param {string} animationType - 动画类型
     * @param {Object} options - 动画选项
     */
    addAnimation(selector, animationType = this.options.defaultAnimation, options = {}) {
        // 获取元素
        const elements = typeof selector === 'string' ? 
                        document.querySelectorAll(selector) : 
                        [selector];
        
        // 为每个元素添加动画
        elements.forEach(element => {
            // 设置动画类型
            element.setAttribute('data-scroll', animationType);
            
            // 设置动画选项
            if (options.delay) {
                element.setAttribute('data-scroll-delay', options.delay);
            }
            
            if (options.duration) {
                element.setAttribute('data-scroll-duration', options.duration);
            }
            
            if (options.easing) {
                element.setAttribute('data-scroll-easing', options.easing);
            }
            
            if (options.once !== undefined) {
                if (options.once) {
                    element.setAttribute('data-scroll-once', '');
                } else {
                    element.removeAttribute('data-scroll-once');
                }
            }
            
            if (options.stagger) {
                element.setAttribute('data-scroll-stagger', '');
                
                if (options.staggerDelay) {
                    element.setAttribute('data-scroll-stagger-delay', options.staggerDelay);
                }
                
                if (options.staggerChildren) {
                    element.setAttribute('data-scroll-stagger-children', options.staggerChildren);
                }
            }
            
            // 观察元素
            this.state.observer.observe(element);
        });
    },
    
    /**
     * 移除动画
     * @param {string|HTMLElement} selector - 选择器或元素
     */
    removeAnimation(selector) {
        // 获取元素
        const elements = typeof selector === 'string' ? 
                        document.querySelectorAll(selector) : 
                        [selector];
        
        // 为每个元素移除动画
        elements.forEach(element => {
            // 移除动画属性
            element.removeAttribute('data-scroll');
            element.removeAttribute('data-scroll-delay');
            element.removeAttribute('data-scroll-duration');
            element.removeAttribute('data-scroll-easing');
            element.removeAttribute('data-scroll-once');
            element.removeAttribute('data-scroll-stagger');
            element.removeAttribute('data-scroll-stagger-delay');
            element.removeAttribute('data-scroll-stagger-children');
            
            // 移除动画类
            element.classList.remove('animated');
            
            // 重置样式
            element.style.opacity = '';
            element.style.transform = '';
            element.style.transition = '';
            
            // 停止观察
            this.state.observer.unobserve(element);
            
            // 从已动画元素集合中移除
            this.state.animatedElements.delete(element);
        });
    },
    
    /**
     * 刷新动画
     * 重新观察所有动画元素
     */
    refresh() {
        // 停止观察所有元素
        this.state.animatedElements.forEach(element => {
            this.state.observer.unobserve(element);
        });
        
        // 清空已动画元素集合
        this.state.animatedElements.clear();
        
        // 重新观察所有动画元素
        this.observeAnimationElements();
        
        // 如果启用视差效果，则更新视差元素
        if (this.options.enableParallax) {
            this.updateParallaxElements();
        }
    }
};

// 当DOM加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    // 初始化滚动动画
    ScrollAnimations.init();
    
    // 导出到全局作用域，以便其他脚本可以使用
    window.ScrollAnimations = ScrollAnimations;
}); 