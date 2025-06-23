/**
 * AlingAi Pro - 核心应用程序
 * 使用现代ES6+特性和模块化架构
 */

class AlingAiApp {
    constructor() {
        this.config = {
            apiBase: '/api/v1',
            wsUrl: this.getWebSocketUrl(),
            debug: true,
            version: '1.0.0'
        };
        
        this.modules = new Map();
        this.services = new Map();
        this.eventBus = new EventTarget();
        
        this.init();
    }

    /**
     * 应用程序初始化
     */
    async init() {
        try {
            
            
            // 检查浏览器兼容性
            this.checkBrowserSupport();
            
            // 初始化核心服务
            await this.initCoreServices();
            
            // 初始化UI组件
            await this.initUIComponents();
            
            // 初始化路由
            await this.initRouter();
            
            // 启动应用
            await this.start();
            
            
            
        } catch (error) {
            console.error('❌ 应用启动失败:', error);
            this.showErrorDialog('应用启动失败', error.message);
        }
    }

    /**
     * 检查浏览器支持
     */
    checkBrowserSupport() {
        const features = [
            'fetch',
            'Promise',
            'WebSocket',
            'localStorage',
            'sessionStorage'
        ];
        
        const unsupported = features.filter(feature => !(feature in window));
        
        if (unsupported.length > 0) {
            throw new Error(`浏览器不支持以下特性: ${unsupported.join(', ')}`);
        }
    }

    /**
     * 初始化核心服务
     */
    async initCoreServices() {
        // HTTP客户端
        const { HttpClient } = await import('./services/http-client.js');
        this.services.set('http', new HttpClient(this.config.apiBase));
        
        // WebSocket管理器
        const { WebSocketManager } = await import('./services/websocket-manager.js');
        this.services.set('ws', new WebSocketManager(this.config.wsUrl));
        
        // 状态管理
        const { StateManager } = await import('./services/state-manager.js');
        this.services.set('state', new StateManager());
        
        // 身份认证
        const { AuthService } = await import('./services/auth-service.js');
        this.services.set('auth', new AuthService(this.services.get('http')));
        
        // 通知系统
        const { NotificationService } = await import('./services/notification-service.js');
        this.services.set('notifications', new NotificationService());
        
        // 主题管理
        const { ThemeManager } = await import('./services/theme-manager.js');
        this.services.set('theme', new ThemeManager());
        
        // 国际化
        const { I18nService } = await import('./services/i18n-service.js');
        this.services.set('i18n', new I18nService());
    }

    /**
     * 初始化UI组件
     */
    async initUIComponents() {
        // 加载UI组件系统
        const { ComponentRegistry } = await import('./ui/component-registry.js');
        this.componentRegistry = new ComponentRegistry();
        
        // 注册核心组件
        await this.registerCoreComponents();
        
        // 初始化组件
        await this.componentRegistry.initializeComponents();
    }

    /**
     * 注册核心组件
     */
    async registerCoreComponents() {
        const components = [
            'header-component',
            'sidebar-component',
            'chat-component',
            'user-menu-component',
            'notification-component',
            'modal-component',
            'loading-component',
            'error-component'
        ];
        
        for (const componentName of components) {
            try {
                const module = await import(`./ui/components/${componentName}.js`);
                this.componentRegistry.register(componentName, module.default);
            } catch (error) {
                console.warn(`组件 ${componentName} 加载失败:`, error);
            }
        }
    }

    /**
     * 初始化路由
     */
    async initRouter() {
        const { Router } = await import('./router/router.js');
        this.router = new Router();
        
        // 配置路由
        this.router.config({
            mode: 'hash',
            base: '/',
            linkActiveClass: 'active'
        });
        
        // 注册路由
        await this.registerRoutes();
        
        // 启动路由
        this.router.start();
    }

    /**
     * 注册路由
     */
    async registerRoutes() {
        // 导入路由配置
        const { routes } = await import('./router/routes.js');
        
        // 注册所有路由
        routes.forEach(route => {
            this.router.register(route.path, route.handler, route.options);
        });
    }

    /**
     * 启动应用
     */
    async start() {
        // 检查用户认证状态
        const authService = this.services.get('auth');
        await authService.checkAuthStatus();
        
        // 初始化WebSocket连接
        const wsManager = this.services.get('ws');
        await wsManager.connect();
        
        // 绑定全局事件
        this.bindGlobalEvents();
        
        // 显示主界面
        this.showMainInterface();
        
        // 发送应用启动事件
        this.eventBus.dispatchEvent(new CustomEvent('app:started'));
    }

    /**
     * 绑定全局事件
     */
    bindGlobalEvents() {
        // 窗口大小改变
        window.addEventListener('resize', this.debounce(() => {
            this.eventBus.dispatchEvent(new CustomEvent('app:resize'));
        }, 250));
        
        // 网络状态变化
        window.addEventListener('online', () => {
            this.eventBus.dispatchEvent(new CustomEvent('app:online'));
        });
        
        window.addEventListener('offline', () => {
            this.eventBus.dispatchEvent(new CustomEvent('app:offline'));
        });
        
        // 页面可见性变化
        document.addEventListener('visibilitychange', () => {
            const event = document.hidden ? 'app:hidden' : 'app:visible';
            this.eventBus.dispatchEvent(new CustomEvent(event));
        });
        
        // 未处理的错误
        window.addEventListener('error', (event) => {
            console.error('未处理的错误:', event.error);
            this.services.get('notifications').error('发生了一个错误', event.error.message);
        });
        
        // 未处理的Promise拒绝
        window.addEventListener('unhandledrejection', (event) => {
            console.error('未处理的Promise拒绝:', event.reason);
            this.services.get('notifications').error('操作失败', event.reason.message || '未知错误');
        });
    }

    /**
     * 显示主界面
     */
    showMainInterface() {
        const loadingElement = document.querySelector('.app-loading');
        const mainElement = document.querySelector('.app-main');
        
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        if (mainElement) {
            mainElement.style.display = 'block';
        }
    }

    /**
     * 获取WebSocket URL
     */
    getWebSocketUrl() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.host;
        return `${protocol}//${host}/ws`;
    }

    /**
     * 防抖函数
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

    /**
     * 显示错误对话框
     */
    showErrorDialog(title, message) {
        const modal = document.createElement('div');
        modal.className = 'error-modal';
        modal.innerHTML = `
            <div class="error-modal-content">
                <h3>${title}</h3>
                <p>${message}</p>
                <button onclick="location.reload()">重新加载</button>
            </div>
        `;
        document.body.appendChild(modal);
    }

    /**
     * 获取服务
     */
    getService(name) {
        return this.services.get(name);
    }

    /**
     * 获取模块
     */
    getModule(name) {
        return this.modules.get(name);
    }

    /**
     * 注册模块
     */
    registerModule(name, module) {
        this.modules.set(name, module);
    }

    /**
     * 发送事件
     */
    emit(eventName, data = null) {
        this.eventBus.dispatchEvent(new CustomEvent(eventName, { detail: data }));
    }

    /**
     * 监听事件
     */
    on(eventName, handler) {
        this.eventBus.addEventListener(eventName, handler);
    }

    /**
     * 移除事件监听
     */
    off(eventName, handler) {
        this.eventBus.removeEventListener(eventName, handler);
    }
}

// 创建全局应用实例
window.app = new AlingAiApp();

// 导出应用类
export default AlingAiApp;
