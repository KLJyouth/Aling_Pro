/**
 * AlingAi Pro - 自定义鼠标效果
 * 提供动态鼠标跟随和交互效果
 * 
 * @version 1.1.0
 * @author AlingAi Team
 */

// 鼠标效果控制器
const CursorEffects = {
    // 配置选项
    options: {
        enabled: true, // 是否启用自定义光标
        showTrail: true, // 是否显示鼠标轨迹
        trailLength: 8, // 轨迹长度
        trailColor: 'rgba(10, 132, 255, 0.5)', // 轨迹颜色
        cursorSize: 20, // 光标大小（像素）
        cursorColor: '#0a84ff', // 光标颜色
        cursorBlendMode: 'normal', // 光标混合模式
        clickEffectEnabled: true, // 是否启用点击效果
        hoverEffectEnabled: true, // 是否启用悬停效果
        useCustomCursors: true, // 是否使用自定义光标图像
        disableOnMobile: true, // 是否在移动设备上禁用
        magneticButtons: true, // 是否启用磁性按钮效果
        cursorGlow: true, // 是否启用光标发光效果
        glowSize: 40, // 发光大小（像素）
        glowColor: 'rgba(10, 132, 255, 0.3)', // 发光颜色
        cursorImagePath: '/assets/images/cursors/', // 自定义光标图像路径
        cursorFriction: 0.15, // 光标跟随摩擦系数
        cursorEasing: 'cubic-bezier(0.23, 1, 0.32, 1)', // 光标缓动函数
        useGPUAcceleration: true, // 是否使用GPU加速
        useRAF: true // 是否使用requestAnimationFrame
    },
    
    // 当前状态
    state: {
        position: { x: 0, y: 0 }, // 当前鼠标位置
        target: { x: 0, y: 0 }, // 目标位置
        isVisible: false, // 是否可见
        isClicking: false, // 是否正在点击
        isHovering: false, // 是否正在悬停
        currentCursor: 'default', // 当前光标类型
        magnetElement: null, // 当前磁性元素
        isMobile: false, // 是否是移动设备
        trailPoints: [], // 轨迹点
        rafId: null, // requestAnimationFrame ID
        cursorElements: {}, // 光标元素
        eventsBound: false, // 是否已绑定事件
        lastUpdateTime: 0 // 上次更新时间
    },
    
    /**
     * 初始化鼠标效果
     * @param {Object} customOptions - 自定义配置选项
     */
    init(customOptions = {}) {
        // 合并自定义选项
        this.options = {...this.options, ...customOptions};
        
        // 检测是否是移动设备
        this.state.isMobile = this.isMobileDevice();
        
        // 如果是移动设备且设置为禁用，则不初始化
        if (this.state.isMobile && this.options.disableOnMobile) {
            console.log('移动设备上禁用自定义光标');
            return;
        }
        
        // 创建光标元素
        this.createCursorElements();
        
        // 绑定事件
        this.bindEvents();
        
        // 预加载光标图像
        if (this.options.useCustomCursors) {
            this.preloadCursorImages();
        }
        
        // 启动动画循环
        this.startAnimationLoop();
        
        console.log('自定义鼠标效果已初始化');
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
     * 创建光标元素
     */
    createCursorElements() {
        // 创建主光标容器
        const cursorContainer = document.createElement('div');
        cursorContainer.className = 'custom-cursor-container';
        
        // 创建主光标元素
        const cursor = document.createElement('div');
        cursor.className = 'custom-cursor';
        
        // 创建光标发光效果
        const cursorGlow = document.createElement('div');
        cursorGlow.className = 'cursor-glow';
        
        // 创建点击波纹效果
        const clickRipple = document.createElement('div');
        clickRipple.className = 'cursor-click-ripple';
        
        // 创建鼠标轨迹容器
        const trailContainer = document.createElement('div');
        trailContainer.className = 'cursor-trail-container';
        
        // 添加到容器
        cursorContainer.appendChild(cursorGlow);
        cursorContainer.appendChild(cursor);
        cursorContainer.appendChild(clickRipple);
        cursorContainer.appendChild(trailContainer);
        
        // 添加到文档
        document.body.appendChild(cursorContainer);
        
        // 保存引用
        this.state.cursorElements = {
            container: cursorContainer,
            cursor: cursor,
            glow: cursorGlow,
            clickRipple: clickRipple,
            trailContainer: trailContainer
        };
        
        // 添加样式
        this.addCursorStyles();
    },
    
    /**
     * 添加光标样式
     */
    addCursorStyles() {
        // 创建样式元素
        const style = document.createElement('style');
        
        // 定义样式
        style.textContent = `
            /* 隐藏原生光标 */
            body.custom-cursor-active {
                cursor: none !important;
            }
            
            body.custom-cursor-active * {
                cursor: none !important;
            }
            
            /* 光标容器 */
            .custom-cursor-container {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 9999;
                overflow: hidden;
                will-change: transform;
                ${this.options.useGPUAcceleration ? 'transform: translateZ(0);' : ''}
            }
            
            /* 主光标 */
            .custom-cursor {
                position: absolute;
                width: ${this.options.cursorSize}px;
                height: ${this.options.cursorSize}px;
                border-radius: 50%;
                background-color: ${this.options.cursorColor};
                mix-blend-mode: ${this.options.cursorBlendMode};
                transform: translate(-50%, -50%) scale(1);
                transition: transform 0.15s ${this.options.cursorEasing}, 
                            background-color 0.15s ease;
                will-change: transform, opacity;
                ${this.options.useGPUAcceleration ? 'transform: translateZ(0) translate(-50%, -50%);' : ''}
                opacity: 0;
            }
            
            /* 光标发光效果 */
            .cursor-glow {
                position: absolute;
                width: ${this.options.glowSize}px;
                height: ${this.options.glowSize}px;
                border-radius: 50%;
                background: radial-gradient(circle, ${this.options.glowColor} 0%, rgba(10, 132, 255, 0) 70%);
                transform: translate(-50%, -50%);
                opacity: ${this.options.cursorGlow ? 0.7 : 0};
                will-change: transform, opacity;
                ${this.options.useGPUAcceleration ? 'transform: translateZ(0) translate(-50%, -50%);' : ''}
            }
            
            /* 点击波纹效果 */
            .cursor-click-ripple {
                position: absolute;
                width: ${this.options.cursorSize * 2}px;
                height: ${this.options.cursorSize * 2}px;
                border-radius: 50%;
                background-color: ${this.options.cursorColor};
                transform: translate(-50%, -50%) scale(0);
                opacity: 0.5;
                will-change: transform, opacity;
                ${this.options.useGPUAcceleration ? 'transform: translateZ(0) translate(-50%, -50%) scale(0);' : ''}
            }
            
            /* 点击波纹动画 */
            .cursor-click-ripple.active {
                animation: cursor-ripple 0.6s ease-out forwards;
            }
            
            @keyframes cursor-ripple {
                0% {
                    transform: translate(-50%, -50%) scale(0);
                    opacity: 0.5;
                }
                100% {
                    transform: translate(-50%, -50%) scale(3);
                    opacity: 0;
                }
            }
            
            /* 鼠标轨迹 */
            .cursor-trail-container {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
            }
            
            .cursor-trail-point {
                position: absolute;
                width: 5px;
                height: 5px;
                border-radius: 50%;
                background-color: ${this.options.trailColor};
                transform: translate(-50%, -50%);
                opacity: 0.7;
                will-change: transform, opacity;
            }
            
            /* 悬停状态 */
            .custom-cursor.hovering {
                transform: translate(-50%, -50%) scale(1.5);
                background-color: rgba(255, 255, 255, 0.8);
                mix-blend-mode: difference;
            }
            
            /* 点击状态 */
            .custom-cursor.clicking {
                transform: translate(-50%, -50%) scale(0.8);
            }
            
            /* 磁性效果 */
            .magnetic-element {
                transition: transform 0.3s ${this.options.cursorEasing};
            }
        `;
        
        // 添加到文档
        document.head.appendChild(style);
    },
    
    /**
     * 绑定事件
     */
    bindEvents() {
        // 如果已经绑定，则不重复绑定
        if (this.state.eventsBound) return;
        
        // 鼠标移动事件
        document.addEventListener('mousemove', this.handleMouseMove.bind(this));
        
        // 鼠标进入/离开文档事件
        document.addEventListener('mouseenter', this.handleMouseEnter.bind(this));
        document.addEventListener('mouseleave', this.handleMouseLeave.bind(this));
        
        // 鼠标按下/释放事件
        document.addEventListener('mousedown', this.handleMouseDown.bind(this));
        document.addEventListener('mouseup', this.handleMouseUp.bind(this));
        
        // 元素悬停事件
        this.addHoverListeners();
        
        // 添加磁性效果
        if (this.options.magneticButtons) {
            this.addMagneticEffect();
        }
        
        // 窗口大小改变事件
        window.addEventListener('resize', this.handleResize.bind(this));
        
        // 标记为已绑定
        this.state.eventsBound = true;
        
        // 添加自定义光标类
        document.body.classList.add('custom-cursor-active');
    },
    
    /**
     * 启动动画循环
     */
    startAnimationLoop() {
        const animate = () => {
            // 平滑过渡到目标位置
            this.state.position.x += (this.state.target.x - this.state.position.x) * 0.2;
            this.state.position.y += (this.state.target.y - this.state.position.y) * 0.2;
            
            // 更新鼠标指针位置
            this.state.cursorElements.cursor.style.left = `${this.state.position.x}px`;
            this.state.cursorElements.cursor.style.top = `${this.state.position.y}px`;
            
            // 更新轨迹点
            if (this.options.showTrail) {
                this.updateTrailPoints();
            }
            
            // 继续动画循环
            requestAnimationFrame(animate);
        };
        
        animate();
    },
    
    /**
     * 更新轨迹点
     */
    updateTrailPoints() {
        // 更新第一个点为当前位置
        this.state.trailPoints.pop();
        this.state.trailPoints.unshift({ x: this.state.position.x, y: this.state.position.y, opacity: 1 });
        
        // 更新所有轨迹点的位置和透明度
        const trailElements = this.state.cursorElements.trailContainer.querySelectorAll('.cursor-trail-point');
        
        this.state.trailPoints.forEach((point, index) => {
            if (index > 0) {
                point.opacity -= 1 / this.options.trailLength;
            }
            
            if (trailElements[index]) {
                trailElements[index].style.left = `${point.x}px`;
                trailElements[index].style.top = `${point.y}px`;
                trailElements[index].style.opacity = Math.max(0, point.opacity);
                trailElements[index].style.transform = `translate(-50%, -50%) scale(${1 - index * (0.5 / this.options.trailLength)})`;
            }
        });
    },
    
    /**
     * 处理鼠标移动事件
     * @param {MouseEvent} event - 鼠标移动事件
     */
    handleMouseMove(event) {
        this.state.target.x = event.clientX;
        this.state.target.y = event.clientY;
    },
    
    /**
     * 处理鼠标进入事件
     * @param {MouseEvent} event - 鼠标进入事件
     */
    handleMouseEnter(event) {
        this.state.isHovering = true;
        this.state.cursorElements.cursor.classList.add('hovering');
        this.updateCursorState(event.target);
    },
    
    /**
     * 处理鼠标离开事件
     * @param {MouseEvent} event - 鼠标离开事件
     */
    handleMouseLeave(event) {
        this.state.isHovering = false;
        this.state.cursorElements.cursor.classList.remove('hovering');
        this.setState('default');
    },
    
    /**
     * 处理鼠标按下事件
     * @param {MouseEvent} event - 鼠标按下事件
     */
    handleMouseDown(event) {
        this.state.isClicking = true;
        this.state.cursorElements.cursor.classList.add('clicking');
        this.state.cursorElements.cursor.style.transform = `translate(-50%, -50%) scale(0.8)`;
    },
    
    /**
     * 处理鼠标释放事件
     * @param {MouseEvent} event - 鼠标释放事件
     */
    handleMouseUp(event) {
        this.state.isClicking = false;
        this.state.cursorElements.cursor.classList.remove('clicking');
        this.state.cursorElements.cursor.style.transform = `translate(-50%, -50%) scale(1)`;
    },
    
    /**
     * 更新鼠标状态
     * @param {HTMLElement} target - 鼠标悬停的元素
     */
    updateCursorState(target) {
        // 检查元素类型和属性
        if (target.tagName === 'A' || 
            target.tagName === 'BUTTON' || 
            target.closest('a') || 
            target.closest('button') || 
            target.classList.contains('clickable') || 
            target.closest('.clickable')) {
            this.setState('pointer');
        } else if (target.tagName === 'INPUT' || 
                  target.tagName === 'TEXTAREA' || 
                  target.isContentEditable) {
            this.setState('text');
        } else if (target.classList.contains('resizable') || 
                  target.closest('.resizable')) {
            this.setState('resize');
        } else if (target.classList.contains('draggable') || 
                  target.closest('.draggable')) {
            this.setState('move');
        } else if (target.classList.contains('loading') || 
                  target.closest('.loading')) {
            this.setState('wait');
        } else {
            this.setState('default');
        }
        
        // 检查自定义光标属性
        const customCursor = target.getAttribute('data-cursor') || 
                            target.closest('[data-cursor]')?.getAttribute('data-cursor');
        
        if (customCursor) {
            this.setState(customCursor);
        }
    },
    
    /**
     * 设置鼠标状态
     * @param {string} state - 鼠标状态
     */
    setState(state) {
        if (this.state.currentCursor === state) return;
        
        this.state.currentCursor = state;
        
        // 移除所有状态类
        this.state.cursorElements.cursor.classList.remove('default', 'pointer', 'text', 'wait', 'move', 'resize');
        
        // 添加当前状态类
        this.state.cursorElements.cursor.classList.add(state);
        
        // 如果是等待状态，添加脉冲动画
        if (state === 'wait' && this.options.clickEffectEnabled) {
            this.state.cursorElements.cursor.style.animation = `cursor-spin ${this.options.pulseDuration}ms linear infinite`;
        } else {
            this.state.cursorElements.cursor.style.animation = '';
        }
    },
    
    /**
     * 添加悬停事件监听器
     */
    addHoverListeners() {
        // 元素悬停事件
        document.addEventListener('mouseover', (e) => {
            this.state.isHovering = true;
            this.state.cursorElements.cursor.classList.add('hovering');
            this.updateCursorState(e.target);
        });
        
        // 页面可见性变化事件
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.state.isVisible = false;
                this.state.cursorElements.cursor.style.opacity = '0';
                this.state.cursorElements.trailContainer.style.opacity = '0';
            } else {
                this.state.isVisible = true;
                this.state.cursorElements.cursor.style.opacity = '1';
                this.state.cursorElements.trailContainer.style.opacity = '1';
            }
        });
    },
    
    /**
     * 添加磁性效果
     */
    addMagneticEffect() {
        // 查找所有可磁性元素
        const magneticElements = document.querySelectorAll('[data-magnetic]');
        
        magneticElements.forEach(element => {
            // 添加磁性类
            element.classList.add('magnetic-element');
            
            // 鼠标进入事件
            element.addEventListener('mouseenter', () => {
                this.state.magnetElement = element;
            });
            
            // 鼠标离开事件
            element.addEventListener('mouseleave', () => {
                this.state.magnetElement = null;
                
                // 重置元素位置
                element.style.transform = '';
            });
            
            // 鼠标移动事件
            element.addEventListener('mousemove', (e) => {
                if (!this.state.magnetElement) return;
                
                // 获取元素位置和大小
                const rect = element.getBoundingClientRect();
                
                // 计算鼠标相对元素中心的位置
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                
                // 计算鼠标距离中心的距离
                const distanceX = e.clientX - centerX;
                const distanceY = e.clientY - centerY;
                
                // 计算磁性强度（越靠近中心越弱）
                const strength = Math.min(rect.width, rect.height) * 0.2;
                
                // 计算位移
                const moveX = distanceX * (strength / rect.width);
                const moveY = distanceY * (strength / rect.height);
                
                // 应用位移
                element.style.transform = `translate(${moveX}px, ${moveY}px)`;
            });
        });
    },
    
    /**
     * 处理窗口大小改变事件
     */
    handleResize() {
        // 重新计算位置和大小
        this.state.position = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
        this.state.target = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
    },
    
    /**
     * 预加载光标图像
     */
    preloadCursorImages() {
        const cursorTypes = ['default', 'pointer', 'text', 'wait', 'move', 'resize'];
        
        cursorTypes.forEach(type => {
            const img = new Image();
            img.src = `${this.options.cursorImagePath}${type}.svg`;
            
            img.onload = () => {
                console.log(`光标图像 ${type}.svg 已加载`);
            };
            
            img.onerror = () => {
                console.warn(`光标图像 ${type}.svg 加载失败`);
            };
        });
    },
    
    /**
     * 创建点击波纹效果
     * @param {number} x - 点击位置X坐标
     * @param {number} y - 点击位置Y坐标
     */
    createClickRipple(x, y) {
        if (!this.options.clickEffectEnabled) return;
        
        // 获取波纹元素
        const ripple = this.state.cursorElements.clickRipple;
        
        // 设置位置
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        
        // 移除之前的动画类
        ripple.classList.remove('active');
        
        // 触发重绘
        void ripple.offsetWidth;
        
        // 添加动画类
        ripple.classList.add('active');
        
        // 动画结束后移除类
        setTimeout(() => {
            ripple.classList.remove('active');
        }, 600);
    },
    
    /**
     * 更新光标发光效果
     */
    updateCursorGlow() {
        if (!this.options.cursorGlow) return;
        
        // 获取发光元素
        const glow = this.state.cursorElements.glow;
        
        // 设置位置
        glow.style.left = `${this.state.position.x}px`;
        glow.style.top = `${this.state.position.y}px`;
        
        // 设置大小
        const scale = this.state.isHovering ? 1.5 : 1;
        glow.style.transform = `translate(-50%, -50%) scale(${scale})`;
    }
};

// 当DOM加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    // 初始化鼠标效果
    CursorEffects.init();
    
    // 导出到全局作用域，以便其他脚本可以使用
    window.initCursorEffects = () => CursorEffects.init();
}); 