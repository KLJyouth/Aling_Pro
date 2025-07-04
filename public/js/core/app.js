/**
 * AlingAi Pro - 核心应用程序
 * 使用现代ES6+特性和模块化架构
 * @version 2.1.0
 * @author AlingAi Team
 * @license Proprietary
 */

class AlingAiApp {
    constructor() {
        this.config = {
            apiBase: "/api/v1",
            wsUrl: this.getWebSocketUrl(),
            debug: true,
            version: "2.1.0",
            offlineMode: false,
            performanceMonitoring: true,
            analyticsEnabled: true,
            featureFlags: {
                darkMode: true,
                betaFeatures: false,
                advancedAnalytics: true,
                quantumFeatures: true
            }
        };
        
        this.modules = new Map();
        this.services = new Map();
        this.eventBus = new EventTarget();
        this.metrics = {
            startTime: performance.now(),
            loadTimes: {},
            errors: [],
            apiCalls: 0,
            apiResponseTimes: []
        };
        
        // 初始化性能监控
        if (this.config.performanceMonitoring) {
            this.initPerformanceMonitoring();
        }
        
        this.init();
    }

    /**
     * 应用程序初始化
     */
    async init() {
        try {
            this.logPerformance("init_start");
            
            // 检查网络连接
            await this.checkNetworkConnection();
            
            // 检查浏览器兼容性
            this.checkBrowserSupport();
            
            // 初始化错误处理
            this.initErrorHandling();
            
            // 初始化核心服务
            await this.initCoreServices();
            
            // 初始化UI组件
            await this.initUIComponents();
            
            // 初始化路由
            await this.initRouter();
            
            // 初始化Service Worker
            await this.initServiceWorker();
            
            // 启动应用
            await this.start();
            
            this.logPerformance("init_complete");
            
        } catch (error) {
            this.handleError("应用启动失败", error);
        }
    }

    /**
     * 检查网络连接
     */
    async checkNetworkConnection() {
        this.config.offlineMode = !navigator.onLine;
        
        if (this.config.offlineMode) {
            console.warn("应用处于离线模式");
            this.showOfflineNotification();
        }
        
        // 监听网络状态变化
        window.addEventListener("online", () => {
            this.config.offlineMode = false;
            this.eventBus.dispatchEvent(new CustomEvent("app:online"));
            this.showOnlineNotification();
            this.syncOfflineData();
        });
        
        window.addEventListener("offline", () => {
            this.config.offlineMode = true;
            this.eventBus.dispatchEvent(new CustomEvent("app:offline"));
            this.showOfflineNotification();
        });
    }

    /**
     * 初始化性能监控
     */
    initPerformanceMonitoring() {
        // 记录页面加载性能
        window.addEventListener("load", () => {
            if (window.performance && window.performance.timing) {
                const timing = window.performance.timing;
                this.metrics.loadTimes = {
                    total: timing.loadEventEnd - timing.navigationStart,
                    domReady: timing.domComplete - timing.domLoading,
                    networkLatency: timing.responseEnd - timing.requestStart,
                    processingTime: timing.domComplete - timing.responseEnd,
                    renderTime: timing.loadEventEnd - timing.domComplete
                };
                
                // 发送性能指标
                if (this.config.analyticsEnabled) {
                    this.sendPerformanceMetrics();
                }
            }
        });
        
        // 监控长任务
        if ("PerformanceObserver" in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.duration > 50) { // 超过50ms的任务视为长任务
                            console.warn(`检测到长任务: ${entry.name || "未命名任务"}, 持续时间: ${entry.duration.toFixed(2)}ms`);
                        }
                    }
                });
                observer.observe({ entryTypes: ["longtask"] });
            } catch (e) {
                console.error("性能观察器初始化失败:", e);
            }
        }
    }

    /**
     * 记录性能标记
     */
    logPerformance(marker) {
        if (!this.config.performanceMonitoring) return;
        
        const time = performance.now();
        this.metrics.markers = this.metrics.markers || {};
        this.metrics.markers[marker] = time;
        
        if (this.config.debug) {
            console.debug(`性能标记: ${marker} - ${time.toFixed(2)}ms`);
        }
    }

    /**
     * 发送性能指标
     */
    async sendPerformanceMetrics() {
        try {
            if (!this.services.has("http")) return;
            
            const httpClient = this.services.get("http");
            await httpClient.post("/analytics/performance", {
                metrics: this.metrics,
                userAgent: navigator.userAgent,
                timestamp: Date.now()
            });
        } catch (error) {
            console.warn("发送性能指标失败:", error);
        }
    }

    /**
     * 初始化错误处理
     */
    initErrorHandling() {
        // 全局错误处理
        window.addEventListener("error", (event) => {
            this.handleError("未捕获的错误", event.error);
            return false;
        });
        
        // Promise 错误处理
        window.addEventListener("unhandledrejection", (event) => {
            this.handleError("未处理的Promise拒绝", event.reason);
            return false;
        });
        
        // 自定义错误处理器
        this.errorHandler = {
            captureError: (error, context = {}) => {
                this.handleError("捕获的错误", error, context);
            },
            logWarning: (message, data = {}) => {
                console.warn(` 警告: ${message}`, data);
                this.metrics.warnings = this.metrics.warnings || [];
                this.metrics.warnings.push({ message, data, timestamp: Date.now() });
            }
        };
    }

    /**
     * 处理错误
     */
    handleError(source, error, context = {}) {
        const errorInfo = {
            source,
            message: error?.message || String(error),
            stack: error?.stack,
            context,
            timestamp: Date.now(),
            url: window.location.href
        };
        
        // 记录错误
        console.error(` ${source}:`, error, context);
        this.metrics.errors.push(errorInfo);
        
        // 显示用户友好的错误消息
        if (this.services.has("notifications")) {
            this.services.get("notifications").error(
                "操作失败",
                this.getUserFriendlyErrorMessage(error)
            );
        } else {
            this.showErrorDialog("操作失败", this.getUserFriendlyErrorMessage(error));
        }
        
        // 发送错误报告
        this.sendErrorReport(errorInfo);
        
        // 触发错误事件
        this.eventBus.dispatchEvent(new CustomEvent("app:error", { detail: errorInfo }));
    }

    /**
     * 获取用户友好的错误消息
     */
    getUserFriendlyErrorMessage(error) {
        if (!error) return "发生未知错误";
        
        // 网络错误
        if (error.name === "NetworkError" || error.message?.includes("network")) {
            return "网络连接错误，请检查您的网络连接并重试";
        }
        
        // 认证错误
        if (error.name === "AuthError" || error.message?.includes("unauthorized") || error.status === 401) {
            return "您的登录已过期，请重新登录";
        }
        
        // 权限错误
        if (error.name === "PermissionError" || error.status === 403) {
            return "您没有执行此操作的权限";
        }
        
        // 服务器错误
        if (error.status >= 500) {
            return "服务器暂时不可用，请稍后重试";
        }
        
        // 默认错误消息
        return error.userMessage || error.message || "操作失败，请重试";
    }

    /**
     * 发送错误报告
     */
    async sendErrorReport(errorInfo) {
        try {
            if (!this.services.has("http") || this.config.offlineMode) return;
            
            const httpClient = this.services.get("http");
            await httpClient.post("/analytics/error", {
                error: errorInfo,
                app: {
                    version: this.config.version,
                    context: {
                        url: window.location.href,
                        userAgent: navigator.userAgent,
                        timestamp: Date.now()
                    }
                }
            });
        } catch (e) {
            console.warn("发送错误报告失败:", e);
        }
    }

    /**
     * 检查浏览器支持
     */
    checkBrowserSupport() {
        const features = [
            "fetch",
            "Promise",
            "WebSocket",
            "localStorage",
            "sessionStorage",
            "IntersectionObserver"
        ];
        
        const unsupported = features.filter(feature => !(feature in window));
        
        if (unsupported.length > 0) {
            throw new Error(`浏览器不支持以下特性: ${unsupported.join(", ")}`);
        }
    }

    /**
     * 初始化核心服务
     */
    async initCoreServices() {
        try {
            this.logPerformance("services_init_start");
            
            // HTTP客户端
            const { HttpClient } = await import("./services/http-client.js");
            this.services.set("http", new HttpClient(this.config.apiBase, this.errorHandler));
            
            // WebSocket管理器
            const { WebSocketManager } = await import("./services/websocket-manager.js");
            this.services.set("ws", new WebSocketManager(this.config.wsUrl, this.errorHandler));
            
            // 状态管理
            const { StateManager } = await import("./services/state-manager.js");
            this.services.set("state", new StateManager(this.errorHandler));
            
            // 身份认证
            const { AuthService } = await import("./services/auth-service.js");
            this.services.set("auth", new AuthService(this.services.get("http"), this.errorHandler));
            
            // 通知系统
            const { NotificationService } = await import("./services/notification-service.js");
            this.services.set("notifications", new NotificationService(this.errorHandler));
            
            // 主题管理
            const { ThemeManager } = await import("./services/theme-manager.js");
            this.services.set("theme", new ThemeManager(this.errorHandler));
            
            // 国际化
            const { I18nService } = await import("./services/i18n-service.js");
            this.services.set("i18n", new I18nService(this.errorHandler));
            
            // 离线数据管理
            const { OfflineManager } = await import("./services/offline-manager.js");
            this.services.set("offline", new OfflineManager(this.errorHandler));
            
            // 分析服务
            const { AnalyticsService } = await import("./services/analytics-service.js");
            this.services.set("analytics", new AnalyticsService(this.config.analyticsEnabled, this.errorHandler));
            
            this.logPerformance("services_init_complete");
        } catch (error) {
            this.handleError("初始化核心服务失败", error);
            throw error; // 重新抛出错误以中断初始化流程
        }
    }

    /**
     * 初始化UI组件
     */
    async initUIComponents() {
        try {
            this.logPerformance("ui_init_start");
            
            // 加载UI组件系统
            const { ComponentRegistry } = await import("./ui/component-registry.js");
            this.componentRegistry = new ComponentRegistry(this.errorHandler);
            
            // 注册核心组件
            await this.registerCoreComponents();
            
            // 初始化组件
            await this.componentRegistry.initializeComponents();
            
            this.logPerformance("ui_init_complete");
        } catch (error) {
            this.handleError("初始化UI组件失败", error);
            throw error;
        }
    }

    /**
     * 注册核心组件
     */
    async registerCoreComponents() {
        const components = [
            "header-component",
            "sidebar-component",
            "chat-component",
            "user-menu-component",
            "notification-component",
            "modal-component",
            "loading-component",
            "error-component",
            "offline-indicator-component",
            "performance-monitor-component"
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
        try {
            this.logPerformance("router_init_start");
            
            const { Router } = await import("./router/router.js");
            this.router = new Router(this.errorHandler);
            
            // 配置路由
            this.router.config({
                mode: "history",
                base: "/",
                linkActiveClass: "active",
                cacheRoutes: true,
                offlineFallback: "/offline.html"
            });
            
            // 注册路由
            await this.registerRoutes();
            
            // 启动路由
            this.router.start();
            
            this.logPerformance("router_init_complete");
        } catch (error) {
            this.handleError("初始化路由失败", error);
            throw error;
        }
    }

    /**
     * 注册路由
     */
    async registerRoutes() {
        // 导入路由配置
        const { routes } = await import("./router/routes.js");
        
        // 注册所有路由
        routes.forEach(route => {
            this.router.register(route.path, route.handler, {
                ...route.options,
                beforeEnter: async (to, from, next) => {
                    // 路由权限检查
                    if (route.options?.requiresAuth && !this.services.get("auth").isAuthenticated()) {
                        return next("/login");
                    }
                    
                    // 离线模式检查
                    if (this.config.offlineMode && !route.options?.availableOffline) {
                        return next("/offline");
                    }
                    
                    // 调用原始的beforeEnter
                    if (typeof route.options?.beforeEnter === "function") {
                        return route.options.beforeEnter(to, from, next);
                    }
                    
                    next();
                }
            });
        });
    }

    /**
     * 初始化Service Worker
     */
    async initServiceWorker() {
        if ("serviceWorker" in navigator) {
            try {
                const registration = await navigator.serviceWorker.register("/sw.js");
                console.log("Service Worker 注册成功:", registration.scope);
                
                // 监听Service Worker消息
                navigator.serviceWorker.addEventListener("message", (event) => {
                    if (event.data && event.data.type === "CACHE_UPDATED") {
                        this.showUpdateNotification();
                    }
                });
                
                // 检查更新
                if (registration.waiting) {
                    this.showUpdateNotification();
                }
            } catch (error) {
                console.warn("Service Worker 注册失败:", error);
            }
        }
    }

    /**
     * 启动应用
     */
    async start() {
        try {
            this.logPerformance("app_start");
            
            // 检查用户认证状态
            const authService = this.services.get("auth");
            await authService.checkAuthStatus();
            
            // 初始化WebSocket连接
            if (!this.config.offlineMode) {
                const wsManager = this.services.get("ws");
                await wsManager.connect();
            }
            
            // 绑定全局事件
            this.bindGlobalEvents();
            
            // 显示主界面
            this.showMainInterface();
            
            // 发送应用启动事件
            this.eventBus.dispatchEvent(new CustomEvent("app:started"));
            
            // 同步离线数据
            if (!this.config.offlineMode) {
                this.syncOfflineData();
            }
            
            this.logPerformance("app_ready");
        } catch (error) {
            this.handleError("启动应用失败", error);
            throw error;
        }
    }

    /**
     * 绑定全局事件
     */
    bindGlobalEvents() {
        // 窗口大小改变
        window.addEventListener("resize", this.debounce(() => {
            this.eventBus.dispatchEvent(new CustomEvent("app:resize"));
        }, 250));
        
        // 页面可见性变化
        document.addEventListener("visibilitychange", () => {
            const event = document.hidden ? "app:hidden" : "app:visible";
            this.eventBus.dispatchEvent(new CustomEvent(event));
            
            // 如果页面重新可见，检查更新
            if (!document.hidden) {
                this.checkForUpdates();
            }
        });
    }

    /**
     * 显示主界面
     */
    showMainInterface() {
        const loadingElement = document.querySelector(".app-loading");
        const mainElement = document.querySelector(".app-main");
        
        if (loadingElement) {
            loadingElement.classList.add("fade-out");
            setTimeout(() => {
                loadingElement.style.display = "none";
            }, 500);
        }
        
        if (mainElement) {
            mainElement.style.display = "block";
            setTimeout(() => {
                mainElement.classList.add("fade-in");
            }, 50);
        }
    }

    /**
     * 显示离线通知
     */
    showOfflineNotification() {
        if (this.services.has("notifications")) {
            this.services.get("notifications").warning(
                "您当前处于离线状态",
                "部分功能可能不可用，连接恢复后将自动同步数据",
                { autoClose: false, id: "offline-notification" }
            );
        }
        
        // 添加离线指示器
        const offlineIndicator = document.createElement("div");
        offlineIndicator.id = "offline-indicator";
        offlineIndicator.className = "offline-indicator";
        offlineIndicator.innerHTML = "<i class=\"fas fa-wifi-slash\"></i> 离线模式";
        document.body.appendChild(offlineIndicator);
    }

    /**
     * 显示在线通知
     */
    showOnlineNotification() {
        if (this.services.has("notifications")) {
            this.services.get("notifications").success(
                "网络连接已恢复",
                "正在同步数据...",
                { autoClose: true, duration: 3000, id: "online-notification" }
            );
            
            // 关闭离线通知
            this.services.get("notifications").close("offline-notification");
        }
        
        // 移除离线指示器
        const offlineIndicator = document.getElementById("offline-indicator");
        if (offlineIndicator) {
            offlineIndicator.classList.add("fade-out");
            setTimeout(() => {
                offlineIndicator.remove();
            }, 500);
        }
    }

    /**
     * 显示更新通知
     */
    showUpdateNotification() {
        if (this.services.has("notifications")) {
            this.services.get("notifications").info(
                "新版本可用",
                "点击此处更新应用",
                { 
                    autoClose: false, 
                    id: "update-notification",
                    onClick: () => this.updateApplication()
                }
            );
        }
    }

    /**
     * 更新应用程序
     */
    async updateApplication() {
        if ("serviceWorker" in navigator) {
            const registration = await navigator.serviceWorker.getRegistration();
            if (registration && registration.waiting) {
                registration.waiting.postMessage({ type: "SKIP_WAITING" });
            }
            
            // 刷新页面应用更新
            window.location.reload();
        }
    }

    /**
     * 检查更新
     */
    async checkForUpdates() {
        if ("serviceWorker" in navigator) {
            const registration = await navigator.serviceWorker.getRegistration();
            if (registration) {
                registration.update();
            }
        }
    }

    /**
     * 同步离线数据
     */
    async syncOfflineData() {
        if (this.services.has("offline")) {
            try {
                await this.services.get("offline").syncData();
            } catch (error) {
                console.warn("同步离线数据失败:", error);
            }
        }
    }

    /**
     * 获取WebSocket URL
     */
    getWebSocketUrl() {
        const protocol = window.location.protocol === "https:" ? "wss:" : "ws:";
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
        console.error(`${title}: ${message}`);
        
        const errorDialog = document.createElement("div");
        errorDialog.className = "error-dialog";
        errorDialog.innerHTML = `
            <div class="error-dialog-content">
                <h2>${title}</h2>
                <p>${message}</p>
                <button class="error-dialog-close">关闭</button>
            </div>
        `;
        
        document.body.appendChild(errorDialog);
        
        const closeButton = errorDialog.querySelector(".error-dialog-close");
        closeButton.addEventListener("click", () => {
            errorDialog.remove();
        });
    }

    /**
     * 获取服务
     */
    getService(name) {
        if (!this.services.has(name)) {
            throw new Error(`服务不存在: ${name}`);
        }
        return this.services.get(name);
    }

    /**
     * 获取模块
     */
    getModule(name) {
        if (!this.modules.has(name)) {
            throw new Error(`模块不存在: ${name}`);
        }
        return this.modules.get(name);
    }

    /**
     * 注册模块
     */
    registerModule(name, module) {
        if (this.modules.has(name)) {
            console.warn(`模块 ${name} 已存在，将被覆盖`);
        }
        this.modules.set(name, module);
    }

    /**
     * 发送事件
     */
    emit(eventName, data = null) {
        this.eventBus.dispatchEvent(
            new CustomEvent(eventName, { detail: data })
        );
    }

    /**
     * 监听事件
     */
    on(eventName, handler) {
        this.eventBus.addEventListener(eventName, (event) => {
            handler(event.detail);
        });
    }

    /**
     * 移除事件监听
     */
    off(eventName, handler) {
        this.eventBus.removeEventListener(eventName, handler);
    }
}

// 创建应用实例
window.app = new AlingAiApp();
